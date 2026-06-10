-- =============================================
-- Reports Center Database Schema
-- MULTI-TENANT REFACTORED
-- =============================================
--
-- ARCHITECTURE (after remediation):
--   report_categories     → GLOBAL  (no idOrganization)
--   reports               → GLOBAL  (no idOrganization)
--   report_favorites      → TENANT  (has idOrganization)
--   report_history        → TENANT  (has idOrganization)
--   report_schedules      → TENANT  (has idOrganization)
--   saved_report_filters  → TENANT  (has idOrganization)
-- =============================================

-- GLOBAL: Report Categories (same for all organizations)
CREATE TABLE IF NOT EXISTS `report_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fa-folder',
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_rptcat_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- GLOBAL: Reports Registry (same for all organizations)
CREATE TABLE IF NOT EXISTS `reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idCategory` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text,
  `route` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL DEFAULT 'system',
  `permission_key` varchar(50) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fa-file-text-o',
  `supports_date_filter` tinyint(1) NOT NULL DEFAULT 1,
  `supports_branch_filter` tinyint(1) NOT NULL DEFAULT 0,
  `supports_dept_filter` tinyint(1) NOT NULL DEFAULT 0,
  `supports_pdf_export` tinyint(1) NOT NULL DEFAULT 1,
  `supports_excel_export` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_report_slug` (`slug`),
  KEY `idx_report_cat` (`idCategory`),
  KEY `idx_report_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TENANT: User Favorites (per-organization user preferences)
CREATE TABLE IF NOT EXISTS `report_favorites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idUser` int NOT NULL,
  `idReport` int NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_fav_user_report` (`idUser`,`idReport`),
  KEY `idx_fav_report` (`idReport`),
  KEY `idx_fav_org` (`idOrganization`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TENANT: Report View History (per-organization user activity)
CREATE TABLE IF NOT EXISTS `report_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idUser` int NOT NULL,
  `idReport` int NOT NULL,
  `viewedDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `filters_used` text,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_history_user` (`idUser`,`idOrganization`),
  KEY `idx_history_report` (`idReport`),
  KEY `idx_history_date` (`viewedDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TENANT: Report Schedules (per-organization user schedules)
CREATE TABLE IF NOT EXISTS `report_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idReport` int NOT NULL,
  `idUser` int NOT NULL,
  `frequency` varchar(20) NOT NULL DEFAULT 'daily',
  `day_of_week` tinyint DEFAULT NULL,
  `day_of_month` tinyint DEFAULT NULL,
  `time_of_day` time NOT NULL DEFAULT '09:00:00',
  `recipients` text,
  `format` varchar(10) NOT NULL DEFAULT 'pdf',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_run` datetime DEFAULT NULL,
  `next_run` datetime DEFAULT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_sched_report` (`idReport`),
  KEY `idx_sched_org` (`idOrganization`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- TENANT: Saved Report Filters (per-organization user filter presets)
CREATE TABLE IF NOT EXISTS `saved_report_filters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idReport` int NOT NULL,
  `idUser` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `filter_params` text NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idOrganization` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_srf_report` (`idReport`),
  KEY `idx_srf_org` (`idOrganization`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;