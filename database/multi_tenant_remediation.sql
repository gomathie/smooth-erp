-- ================================================================
-- MULTI-TENANT ARCHITECTURE REMEDIATION
-- ================================================================
-- Phase 1-9: Complete migration scripts
-- ================================================================

-- ================================================================
-- PHASE 1: DATABASE AUDIT - Table Classification
-- ================================================================
/*
GROUP A: SYSTEM TABLES (should be global, no idOrganization)
=================================================================
1. currencies            - Global currency definitions
2. report_categories     - Report category definitions (currently org-scoped ❌)
3. reports               - Report definitions (currently org-scoped ❌)

GROUP B: HYBRID TABLES (need restructuring for system vs tenant split)
=================================================================
4. accounts              - Chart of Accounts (system accounts should be global;
                           user-defined accounts per org)
5. settings              - System settings incorrectly duplicated per org
6. categories            - Product categories (could be org-specific, OK)
7. users                 - SuperAdmin has NULL org, org users scoped (OK pattern)

GROUP C: TENANT TABLES (correctly org-scoped)
=================================================================
8.  organizations        - The tenant table itself
9.  customers            - Business data per org
10. expenses             - Business data per org
11. invoice_activity_log - Business data per org
12. invoices             - Business data per org
13. journal_entries      - Business data per org
14. journal_lines        - Business data per org
15. organization_currencies - Org-currency mapping (correct: hybrid)
16. payments_received    - Business data per org
17. products             - Business data per org
18. quotations           - Business data per org
19. sales                - Business data per org
20. stock_movements      - Business data per org
21. report_favorites     - User preferences per org (OK)
22. report_history       - User activity per org (OK)
23. report_schedules     - User schedules per org (OK)
24. saved_report_filters - User saved filters per org (OK)
*/

-- ================================================================
-- PHASE 2: TABLES REQUIRING REMEDIATION
-- ================================================================
/*
TABLE: report_categories
PROBLEM: idOrganization column + UNIQUE(slug, idOrganization) forces
         category duplication per org
CORRECTION: Remove idOrganization, make slug globally unique

TABLE: reports
PROBLEM: idOrganization column + UNIQUE(slug, idOrganization) forces
         report duplication per org
CORRECTION: Remove idOrganization, make slug globally unique

TABLE: accounts
PROBLEM: idOrganization column + UNIQUE(code, idOrganization) forces
         chart of accounts duplication per org
CORRECTION: System accounts (isSystem=1) should be global with idOrganization=NULL
            User accounts (isSystem=0) remain org-scoped

TABLE: settings
PROBLEM: Primary key is (idOrganization, settingKey) - forces per-org duplication
CORRECTION: Implement 3-tier settings hierarchy (system -> org -> user)
*/

-- ================================================================
-- PHASE 3: REPORT CENTER FIX
-- ================================================================

-- STEP 1: Make report_categories global
ALTER TABLE report_categories
  DROP INDEX uniq_rptcat_slug_org,
  ADD UNIQUE INDEX uniq_rptcat_slug (slug);

-- STEP 2: Make reports global
ALTER TABLE reports
  DROP INDEX uniq_report_slug_org,
  ADD UNIQUE INDEX uniq_report_slug (slug);

-- STEP 3: Consolidate duplicate categories (keep one, reassign reports)
DROP PROCEDURE IF EXISTS ConsolidateReportCategories;
DELIMITER $$
CREATE PROCEDURE ConsolidateReportCategories()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE catSlug VARCHAR(100);
  DECLARE keepId INT;
  DECLARE dupId INT;
  DECLARE cur CURSOR FOR
    SELECT slug FROM report_categories GROUP BY slug HAVING COUNT(*) > 1;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN cur;
  read_loop: LOOP
    FETCH cur INTO catSlug;
    IF done THEN LEAVE read_loop; END IF;

    -- Find the lowest ID for this slug (keep it)
    SELECT MIN(id) INTO keepId FROM report_categories WHERE slug = catSlug;
    -- Find duplicate IDs to merge
    SET dupId = 0;

    -- Update reports to point to kept category
    UPDATE reports SET idCategory = keepId
      WHERE idCategory IN (SELECT id FROM report_categories WHERE slug = catSlug AND id <> keepId);

    -- Delete duplicate categories
    DELETE FROM report_categories WHERE slug = catSlug AND id <> keepId;
  END LOOP;
  CLOSE cur;
END$$
DELIMITER ;

CALL ConsolidateReportCategories();

-- STEP 4: Consolidate duplicate reports (keep one, merge favorites/history)
DROP PROCEDURE IF EXISTS ConsolidateReports;
DELIMITER $$
CREATE PROCEDURE ConsolidateReports()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE rptSlug VARCHAR(150);
  DECLARE keepId INT;
  DECLARE cur CURSOR FOR
    SELECT slug FROM reports GROUP BY slug HAVING COUNT(*) > 1;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN cur;
  read_loop: LOOP
    FETCH cur INTO rptSlug;
    IF done THEN LEAVE read_loop; END IF;

    SELECT MIN(id) INTO keepId FROM reports WHERE slug = rptSlug;

    -- Update favorites to point to kept report
    UPDATE report_favorites SET idReport = keepId
      WHERE idReport IN (SELECT id FROM reports WHERE slug = rptSlug AND id <> keepId);
    -- Update history
    UPDATE report_history SET idReport = keepId
      WHERE idReport IN (SELECT id FROM reports WHERE slug = rptSlug AND id <> keepId);
    -- Update schedules
    UPDATE report_schedules SET idReport = keepId
      WHERE idReport IN (SELECT id FROM reports WHERE slug = rptSlug AND id <> keepId);
    -- Update saved filters
    UPDATE saved_report_filters SET idReport = keepId
      WHERE idReport IN (SELECT id FROM reports WHERE slug = rptSlug AND id <> keepId);

    -- Delete duplicates
    DELETE FROM reports WHERE slug = rptSlug AND id <> keepId;
  END LOOP;
  CLOSE cur;
END$$
DELIMITER ;

CALL ConsolidateReports();

-- STEP 5: Drop idOrganization from report_categories (after consolidation)
ALTER TABLE report_categories
  DROP KEY idx_rptcat_org,
  DROP COLUMN idOrganization;

-- STEP 6: Drop idOrganization from reports
ALTER TABLE reports
  DROP KEY idx_report_org,
  DROP KEY idx_report_cat,
  ADD KEY idx_report_cat (idCategory),
  DROP COLUMN idOrganization;

-- ================================================================
-- PHASE 4: MENU SYSTEM FIX
-- ================================================================

-- Create menu registry table (menus defined globally)
CREATE TABLE IF NOT EXISTS menu_registry (
  id int NOT NULL AUTO_INCREMENT,
  parent_id int DEFAULT NULL,
  label_key varchar(100) NOT NULL,
  label_en varchar(100) NOT NULL,
  icon varchar(50) DEFAULT NULL,
  route varchar(255) DEFAULT NULL,
  permission_key varchar(50) DEFAULT NULL,
  module varchar(50) DEFAULT NULL,
  sort_order int NOT NULL DEFAULT 0,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  is_system tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_menu_parent (parent_id),
  KEY idx_menu_module (module),
  KEY idx_menu_permission (permission_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Seed menu registry (mirrors sidebar.php structure)
INSERT INTO menu_registry (parent_id, label_key, label_en, icon, route, permission_key, module, sort_order) VALUES
  (NULL, 'Home', 'Home', 'fa-home', 'home', 'dashboard', 'dashboard', 10),
  (NULL, 'Products', 'Products', 'fa-product-hunt', NULL, 'products', 'products', 20),
  (1, 'Products', 'Products', 'fa-circle-o', 'products', 'products', 'products', 21),
  (1, 'Categories', 'Categories', 'fa-circle-o', 'categories', 'products', 'products', 22),
  (NULL, 'Customers', 'Customers', 'fa-users', 'customers', 'customers', 'sales', 30),
  (NULL, 'Sales', 'Sales', 'fa-usd', NULL, 'sales', 'sales', 40),
  (5, 'Overview', 'Overview', 'fa-circle-o', 'reports', 'reports', 'sales', 41),
  (5, 'Manage Sales', 'Manage Sales', 'fa-circle-o', 'sales', 'sales', 'sales', 42),
  (5, 'Create Sale', 'Create Sale', 'fa-circle-o', 'create-sale', 'sales', 'sales', 43),
  (5, 'Quotations', 'Quotations', 'fa-circle-o', 'quotations', 'sales', 'sales', 44),
  (5, 'Invoices', 'Invoices', 'fa-circle-o', 'invoices', 'sales', 'sales', 45),
  (NULL, 'Reports Center', 'Reports Center', 'fa-bar-chart', 'index.php?route=reports-center', 'reports', 'reports', 50),
  (NULL, 'Reports', 'Reports', 'fa-bar-chart', NULL, 'reports', 'reports', 60),
  (12, 'Business Overview', 'Business Overview', 'fa-circle-o', 'report-overview', 'reports', 'accounting', 61),
  (12, 'Sales', 'Sales', 'fa-circle-o', 'report-sales', 'reports', 'sales', 62),
  (12, 'Inventory', 'Inventory', 'fa-circle-o', 'report-inventory', 'reports', 'inventory', 63),
  (12, 'Payables', 'Payables', 'fa-circle-o', 'report-payables', 'reports', 'accounting', 64),
  (12, 'Receivables', 'Receivables', 'fa-circle-o', 'report-receivables', 'reports', 'accounting', 65),
  (12, 'Payments Received', 'Payments Received', 'fa-circle-o', 'report-payments', 'reports', 'accounting', 66),
  (12, 'Activity', 'Activity', 'fa-circle-o', 'report-activity', 'reports', 'system', 67),
  (12, 'Tax Summary', 'Tax Summary', 'fa-circle-o', 'report-tax', 'reports', 'accounting', 68),
  (NULL, 'Expenses', 'Expenses', 'fa-credit-card', 'expenses', 'expenses', 'expenses', 70),
  (NULL, 'Accounting', 'Accounting', 'fa-balance-scale', NULL, 'accounting', 'accounting', 80),
  (23, 'Overview', 'Overview', 'fa-circle-o', 'accounting', 'accounting', 'accounting', 81),
  (23, 'Chart of Accounts', 'Chart of Accounts', 'fa-circle-o', 'chart-of-accounts', 'accounting', 'accounting', 82),
  (23, 'Currencies', 'Currencies', 'fa-money', 'currencies', 'currencies', 'settings', 83),
  (NULL, 'User Management', 'User Management', 'fa-user', 'users', 'users', 'system', 90),
  (NULL, 'Settings', 'Settings', 'fa-cog', 'settings', 'settings', 'settings', 100);

-- ================================================================
-- PHASE 5: DASHBOARD FIX
-- ================================================================

-- Create widget registry (widgets defined globally)
CREATE TABLE IF NOT EXISTS widget_registry (
  id int NOT NULL AUTO_INCREMENT,
  widget_key varchar(50) NOT NULL,
  name varchar(100) NOT NULL,
  description text,
  icon varchar(50) DEFAULT NULL,
  module varchar(50) NOT NULL,
  component_path varchar(255) NOT NULL,
  permission_key varchar(50) DEFAULT NULL,
  default_columns int NOT NULL DEFAULT 6,
  default_order int NOT NULL DEFAULT 0,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  supports_date_range tinyint(1) NOT NULL DEFAULT 0,
  cache_ttl int NOT NULL DEFAULT 300,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_widget_key (widget_key),
  KEY idx_widget_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Seed initial widget definitions
INSERT INTO widget_registry (widget_key, name, description, icon, module, component_path, permission_key, default_columns, default_order) VALUES
  ('recent-products', 'Recent Products', 'Recently added products', 'fa-cube', 'products', 'views/modules/home/recent-products.php', 'products', 6, 10),
  ('top-stock', 'Top Stock', 'Products with highest stock levels', 'fa-cubes', 'inventory', 'views/modules/home/top-stock.php', 'products', 6, 20),
  ('low-stock', 'Low Stock', 'Products running low on stock', 'fa-exclamation-triangle', 'inventory', 'views/modules/home/low-stock.php', 'products', 6, 30);

-- User widget preferences (tenant-specific, stores which widgets + layout)
CREATE TABLE IF NOT EXISTS user_widget_preferences (
  id int NOT NULL AUTO_INCREMENT,
  idUser int NOT NULL,
  idWidget int NOT NULL,
  idOrganization int NOT NULL,
  is_visible tinyint(1) NOT NULL DEFAULT 1,
  sort_order int NOT NULL DEFAULT 0,
  columns int DEFAULT NULL,
  config_json text,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_user_widget (idUser, idWidget, idOrganization),
  KEY idx_uwp_org (idOrganization)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ================================================================
-- PHASE 6: SETTINGS ARCHITECTURE
-- ================================================================

-- New system_settings table (global settings, no idOrganization)
CREATE TABLE IF NOT EXISTS system_settings (
  settingKey varchar(50) NOT NULL,
  settingValue varchar(255) DEFAULT NULL,
  settingType varchar(20) NOT NULL DEFAULT 'string',
  description text,
  is_editable tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (settingKey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Migrate org-independent settings from settings table to system_settings
INSERT IGNORE INTO system_settings (settingKey, settingValue, settingType, description, is_editable)
SELECT DISTINCT s.settingKey, s.settingValue, 'string', NULL, 1
FROM settings s
ORDER BY s.settingKey;

-- Keep settings table for org-level overrides (already structured correctly)
ALTER TABLE settings
  MODIFY COLUMN settingValue varchar(255) DEFAULT NULL,
  ADD COLUMN override_level varchar(10) NOT NULL DEFAULT 'organization'
    COMMENT 'organization or user';

-- New user_settings table for user-level overrides
CREATE TABLE IF NOT EXISTS user_settings (
  settingKey varchar(50) NOT NULL,
  settingValue varchar(255) DEFAULT NULL,
  idUser int NOT NULL,
  idOrganization int NOT NULL,
  PRIMARY KEY (idUser, idOrganization, settingKey),
  KEY idx_us_org (idOrganization)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- ================================================================
-- PHASE 7: CHART OF ACCOUNTS GLOBALIZATION
-- ================================================================

-- System accounts (isSystem=1) should be global
-- After migration, system accounts have idOrganization = NULL
ALTER TABLE accounts
  MODIFY COLUMN idOrganization int DEFAULT NULL;

-- Consolidate system accounts to global
DROP PROCEDURE IF EXISTS ConsolidateSystemAccounts;
DELIMITER $$
CREATE PROCEDURE ConsolidateSystemAccounts()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE accCode VARCHAR(20);
  DECLARE accName VARCHAR(100);
  DECLARE accType VARCHAR(30);
  DECLARE keepId INT;
  DECLARE cur CURSOR FOR
    SELECT code, name, type FROM accounts WHERE isSystem = 1 GROUP BY code HAVING COUNT(*) > 1;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN cur;
  read_loop: LOOP
    FETCH cur INTO accCode, accName, accType;
    IF done THEN LEAVE read_loop; END IF;

    -- Keep the account with idOrganization = 1 (first org)
    SELECT MIN(id) INTO keepId FROM accounts WHERE code = accCode AND isSystem = 1;

    -- Update journal lines referencing duplicate system accounts
    UPDATE journal_lines SET idAccount = keepId
      WHERE idAccount IN (SELECT id FROM accounts WHERE code = accCode AND isSystem = 1 AND id <> keepId);

    -- Delete duplicate system accounts
    DELETE FROM accounts WHERE code = accCode AND isSystem = 1 AND id <> keepId;
  END LOOP;
  CLOSE cur;

  -- Set idOrganization = NULL for system accounts (truly global)
  UPDATE accounts SET idOrganization = NULL WHERE isSystem = 1;
END$$
DELIMITER ;

CALL ConsolidateSystemAccounts();

-- Update unique constraint for accounts to allow null org
ALTER TABLE accounts
  DROP INDEX uniq_account_org_code,
  DROP INDEX idx_acc_org;

-- Add new indexes for global + per-org accounts
CREATE UNIQUE INDEX uniq_system_account_code ON accounts (code) WHERE idOrganization IS NULL AND isSystem = 1;
CREATE UNIQUE INDEX uniq_org_account_code ON accounts (idOrganization, code) WHERE idOrganization IS NOT NULL;
CREATE INDEX idx_account_org ON accounts (idOrganization);

-- ================================================================
-- PHASE 8: ORGANIZATION ONBOARDING (NEW REFACTORED)
-- ================================================================

DROP PROCEDURE IF EXISTS CreateOrganization;

DELIMITER $$
CREATE PROCEDURE CreateOrganization(
  IN p_name VARCHAR(150),
  IN p_code VARCHAR(30),
  IN p_email VARCHAR(150),
  IN p_phone VARCHAR(50),
  IN p_address VARCHAR(255),
  IN p_baseCurrency VARCHAR(3),
  IN p_maxUsers INT,
  IN p_adminName VARCHAR(150),
  IN p_adminUser VARCHAR(150),
  IN p_adminPassword VARCHAR(255),
  IN p_adminEmail VARCHAR(150)
)
BEGIN
  DECLARE newOrgId INT;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Organization creation failed';
  END;

  START TRANSACTION;

  -- 1. Create organization record
  INSERT INTO organizations (name, code, email, phone, address, baseCurrency, status, maxUsers)
  VALUES (p_name, p_code, p_email, p_phone, p_address, p_baseCurrency, 1, p_maxUsers);

  SET newOrgId = LAST_INSERT_ID();

  -- 2. Create admin user for org
  INSERT INTO users (name, user, password, profile, photo, email, phone, status, lastLogin, idOrganization)
  VALUES (p_adminName, p_adminUser, p_adminPassword, 'Administrator', '', p_adminEmail, p_adminPhone, 1, NOW(), newOrgId);

  -- 3. Create org-level settings overrides (if needed)
  -- Settings are inherited from system_settings by default
  INSERT INTO settings (settingKey, settingValue, idOrganization)
  VALUES ('accounting_enabled', '1', newOrgId),
         ('multicurrency_enabled', '1', newOrgId);

  -- 4. Add base currency
  INSERT INTO organization_currencies (idOrganization, currencyCode, isBase)
  VALUES (newOrgId, p_baseCurrency, 1);

  -- 5. Clone non-system user-defined accounts only (system accounts are global)
  INSERT INTO accounts (code, name, type, isSystem, idOrganization)
  SELECT code, name, type, isSystem, newOrgId
  FROM accounts
  WHERE idOrganization = 1 AND isSystem = 0;

  -- NOTE: Reports, report_categories, menus, widgets, permissions
  -- are ALL GLOBAL - no need to copy them.

  COMMIT;
  SELECT newOrgId AS orgId;
END$$
DELIMITER ;

-- ================================================================
-- PHASE 9: ROLLBACK SCRIPTS
-- ================================================================

/*
ROLLBACK PROCEDURE:
--------------------
To revert to the previous architecture, run the following steps:

1. Restore idOrganization on reports + report_categories:
   ALTER TABLE report_categories
     ADD COLUMN idOrganization int NOT NULL DEFAULT 1 AFTER is_active,
     ADD KEY idx_rptcat_org (idOrganization),
     ADD UNIQUE KEY uniq_rptcat_slug_org (slug, idOrganization);

   ALTER TABLE reports
     ADD COLUMN idOrganization int NOT NULL DEFAULT 1 AFTER is_active,
     ADD KEY idx_report_org (idOrganization),
     ADD UNIQUE KEY uniq_report_slug_org (slug, idOrganization);

2. Re-seed reports per org (copy global reports to each org):
   INSERT INTO reports (idCategory, name, slug, description, route, module, icon, permission_key, idOrganization)
   SELECT r.idCategory, r.name, r.slug, r.description, r.route, r.module, r.icon, r.permission_key, o.id
   FROM reports r CROSS JOIN organizations o
   WHERE r.idOrganization IS NULL;

3. Drop new tables:
   DROP TABLE IF EXISTS menu_registry;
   DROP TABLE IF EXISTS widget_registry;
   DROP TABLE IF EXISTS user_widget_preferences;
   DROP TABLE IF EXISTS system_settings;
   DROP TABLE IF EXISTS user_settings;

4. Restore unique constraint on accounts:
   DROP INDEX uniq_system_account_code ON accounts;
   DROP INDEX uniq_org_account_code ON accounts;
   CREATE UNIQUE INDEX uniq_account_org_code ON accounts (idOrganization, code);
*/

-- ================================================================
-- CLEANUP: Drop consolidation procedures
-- ================================================================
DROP PROCEDURE IF EXISTS ConsolidateReportCategories;
DROP PROCEDURE IF EXISTS ConsolidateReports;
DROP PROCEDURE IF EXISTS ConsolidateSystemAccounts;