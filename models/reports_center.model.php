<?php
/**
 * Reports Center Model - Multi-Tenant Refactored
 *
 * ARCHITECTURE:
 * - report_categories  → GLOBAL (no idOrganization)
 * - reports            → GLOBAL (no idOrganization)
 * - report_favorites   → TENANT-SCOPED (idOrganization, idUser)
 * - report_history     → TENANT-SCOPED (idOrganization, idUser)
 * - report_schedules   → TENANT-SCOPED (idOrganization, idUser)
 * - saved_report_filters → TENANT-SCOPED (idOrganization, idUser)
 */

require_once __DIR__ . '/connection.php';

class ReportsCenterModel {

    /* ============================================
       AUTO-CREATE TABLES IF MISSING
       ============================================ */
    public static function mdlEnsureTables() {
        $link = Connection::connect();

        // GLOBAL: report_categories (no idOrganization)
        $link->exec("CREATE TABLE IF NOT EXISTS report_categories (
            id int NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            slug varchar(100) NOT NULL,
            icon varchar(50) DEFAULT 'fa-folder',
            sort_order int NOT NULL DEFAULT 0,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_rptcat_slug (slug)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // GLOBAL: reports (no idOrganization)
        $link->exec("CREATE TABLE IF NOT EXISTS reports (
            id int NOT NULL AUTO_INCREMENT,
            idCategory int NOT NULL,
            name varchar(150) NOT NULL,
            slug varchar(150) NOT NULL,
            description text,
            route varchar(255) NOT NULL,
            module varchar(50) NOT NULL DEFAULT 'system',
            permission_key varchar(50) DEFAULT NULL,
            icon varchar(50) DEFAULT 'fa-file-text-o',
            supports_date_filter tinyint(1) NOT NULL DEFAULT 1,
            supports_branch_filter tinyint(1) NOT NULL DEFAULT 0,
            supports_dept_filter tinyint(1) NOT NULL DEFAULT 0,
            supports_pdf_export tinyint(1) NOT NULL DEFAULT 1,
            supports_excel_export tinyint(1) NOT NULL DEFAULT 1,
            is_system tinyint(1) NOT NULL DEFAULT 1,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_report_slug (slug),
            KEY idx_report_cat (idCategory),
            KEY idx_report_module (module)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // TENANT-SCOPED: report_favorites
        $link->exec("CREATE TABLE IF NOT EXISTS report_favorites (
            id int NOT NULL AUTO_INCREMENT,
            idUser int NOT NULL,
            idReport int NOT NULL,
            createdDate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            idOrganization int NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_fav_user_report (idUser, idReport),
            KEY idx_fav_report (idReport),
            KEY idx_fav_org (idOrganization)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // TENANT-SCOPED: report_history
        $link->exec("CREATE TABLE IF NOT EXISTS report_history (
            id int NOT NULL AUTO_INCREMENT,
            idUser int NOT NULL,
            idReport int NOT NULL,
            viewedDate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            filters_used text,
            idOrganization int NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY idx_history_user (idUser, idOrganization),
            KEY idx_history_report (idReport),
            KEY idx_history_date (viewedDate)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // TENANT-SCOPED: report_schedules
        $link->exec("CREATE TABLE IF NOT EXISTS report_schedules (
            id int NOT NULL AUTO_INCREMENT,
            idReport int NOT NULL,
            idUser int NOT NULL,
            frequency varchar(20) NOT NULL DEFAULT 'daily',
            day_of_week tinyint DEFAULT NULL,
            day_of_month tinyint DEFAULT NULL,
            time_of_day time NOT NULL DEFAULT '09:00:00',
            recipients text,
            format varchar(10) NOT NULL DEFAULT 'pdf',
            is_active tinyint(1) NOT NULL DEFAULT 1,
            last_run datetime DEFAULT NULL,
            next_run datetime DEFAULT NULL,
            createdDate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            idOrganization int NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY idx_sched_report (idReport),
            KEY idx_sched_org (idOrganization)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // TENANT-SCOPED: saved_report_filters
        $link->exec("CREATE TABLE IF NOT EXISTS saved_report_filters (
            id int NOT NULL AUTO_INCREMENT,
            idReport int NOT NULL,
            idUser int NOT NULL,
            name varchar(100) NOT NULL,
            filter_params text NOT NULL,
            is_default tinyint(1) NOT NULL DEFAULT 0,
            createdDate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            idOrganization int NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY idx_srf_report (idReport),
            KEY idx_srf_org (idOrganization)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3");

        // Ensure icon column exists on reports table (may be missing from older installs)
        try { $link->exec("ALTER TABLE reports ADD COLUMN icon varchar(50) DEFAULT 'fa-file-text-o' AFTER permission_key"); } catch (Exception $e) {}
        // Legacy compatibility: remove idOrganization if still present on reports
        try { $link->exec("ALTER TABLE reports DROP COLUMN idOrganization"); } catch (Exception $e) {}
        try { $link->exec("ALTER TABLE report_categories DROP COLUMN idOrganization"); } catch (Exception $e) {}
    }

    /* ============================================
       CATEGORIES — GLOBAL (no tenant scope)
       ============================================ */
    public static function mdlGetCategories() {
        $stmt = Connection::connect()->prepare(
            "SELECT rc.*, COUNT(r.id) AS report_count
               FROM report_categories rc
               LEFT JOIN reports r ON r.idCategory = rc.id AND r.is_active = 1
              WHERE rc.is_active = 1
              GROUP BY rc.id
              ORDER BY rc.sort_order ASC, rc.name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public static function mdlGetCategoryBySlug($slug) {
        $stmt = Connection::connect()->prepare(
            "SELECT * FROM report_categories WHERE slug = :slug LIMIT 1"
        );
        $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch() ?: false;
    }

    /* ============================================
       REPORTS — GLOBAL (no tenant scope)
       ============================================ */
    public static function mdlGetReportsByCategory($categoryId) {
        $stmt = Connection::connect()->prepare(
            "SELECT r.*, rc.name AS categoryName
               FROM reports r
               JOIN report_categories rc ON rc.id = r.idCategory
              WHERE r.idCategory = :catId AND r.is_active = 1
              ORDER BY r.name ASC"
        );
        $stmt->bindParam(":catId", $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public static function mdlSearchReports($query) {
        $like = "%" . $query . "%";
        $stmt = Connection::connect()->prepare(
            "SELECT r.*, rc.name AS categoryName
               FROM reports r
               JOIN report_categories rc ON rc.id = r.idCategory
              WHERE r.is_active = 1
                AND (r.name LIKE :q OR r.description LIKE :q2 OR rc.name LIKE :q3)
              ORDER BY r.name ASC"
        );
        $stmt->bindParam(":q", $like, PDO::PARAM_STR);
        $stmt->bindParam(":q2", $like, PDO::PARAM_STR);
        $stmt->bindParam(":q3", $like, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public static function mdlGetAllReports() {
        $stmt = Connection::connect()->prepare(
            "SELECT r.*, rc.name AS categoryName
               FROM reports r
               JOIN report_categories rc ON rc.id = r.idCategory
              WHERE r.is_active = 1
              ORDER BY rc.sort_order ASC, r.name ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public static function mdlGetReportBySlug($slug) {
        $stmt = Connection::connect()->prepare(
            "SELECT r.*, rc.name AS categoryName
               FROM reports r
               JOIN report_categories rc ON rc.id = r.idCategory
              WHERE r.slug = :slug LIMIT 1"
        );
        $stmt->bindParam(":slug", $slug, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch() ?: false;
    }

    /* ============================================
       REGISTER — GLOBAL (no tenant scope)
       Reports and categories are registered once globally.
       All organizations see the same reports automatically.
       ============================================ */
    public static function mdlRegisterReport($catSlug, $name, $slug, $route, $module, $desc, $icon = 'fa-file-text-o', $perm = null) {
        $link = Connection::connect();

        // Get category id (global lookup — no org scope)
        $cstmt = $link->prepare("SELECT id FROM report_categories WHERE slug = :slug");
        $cstmt->bindParam(":slug", $catSlug, PDO::PARAM_STR);
        $cstmt->execute();
        $catRow = $cstmt->fetch();
        if (!$catRow) return;
        $catId = (int)$catRow["id"];

        // Check if report already exists (global slug)
        $check = $link->prepare("SELECT id FROM reports WHERE slug = :slug");
        $check->bindParam(":slug", $slug, PDO::PARAM_STR);
        $check->execute();
        if ($check->fetch()) return; // already registered globally

        $ins = $link->prepare(
            "INSERT INTO reports (idCategory, name, slug, description, route, module, icon, permission_key)
             VALUES (:catId, :name, :slug, :desc, :route, :mod, :icon, :perm)"
        );
        $ins->bindParam(":catId", $catId, PDO::PARAM_INT);
        $ins->bindParam(":name", $name, PDO::PARAM_STR);
        $ins->bindParam(":slug", $slug, PDO::PARAM_STR);
        $ins->bindParam(":desc", $desc, PDO::PARAM_STR);
        $ins->bindParam(":route", $route, PDO::PARAM_STR);
        $ins->bindParam(":mod", $module, PDO::PARAM_STR);
        $ins->bindParam(":icon", $icon, PDO::PARAM_STR);
        $ins->bindParam(":perm", $perm, ($perm !== null) ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $ins->execute();
    }

    /* ============================================
       FAVORITES — TENANT-SCOPED
       ============================================ */
    public static function mdlGetFavoriteIds($userId) {
        $stmt = Connection::connect()->prepare(
            "SELECT idReport FROM report_favorites WHERE idUser = :uid AND idOrganization = :org"
        );
        $org = Tenant::id();
        $stmt->bindParam(":uid", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":org", $org, PDO::PARAM_INT);
        $stmt->execute();
        $ids = [];
        foreach ($stmt->fetchAll() as $row) { $ids[] = (int)$row["idReport"]; }
        return $ids;
    }

    public static function mdlToggleFavorite($userId, $reportId) {
        $link = Connection::connect();
        $org = Tenant::id();
        $check = $link->prepare("SELECT id FROM report_favorites WHERE idUser = :uid AND idReport = :rid AND idOrganization = :org");
        $check->bindParam(":uid", $userId, PDO::PARAM_INT);
        $check->bindParam(":rid", $reportId, PDO::PARAM_INT);
        $check->bindParam(":org", $org, PDO::PARAM_INT);
        $check->execute();
        if ($check->fetch()) {
            $del = $link->prepare("DELETE FROM report_favorites WHERE idUser = :uid AND idReport = :rid AND idOrganization = :org");
            $del->bindParam(":uid", $userId, PDO::PARAM_INT);
            $del->bindParam(":rid", $reportId, PDO::PARAM_INT);
            $del->bindParam(":org", $org, PDO::PARAM_INT);
            $del->execute();
            return false;
        }
        $ins = $link->prepare("INSERT INTO report_favorites (idUser, idReport, idOrganization) VALUES (:uid, :rid, :org)");
        $ins->bindParam(":uid", $userId, PDO::PARAM_INT);
        $ins->bindParam(":rid", $reportId, PDO::PARAM_INT);
        $ins->bindParam(":org", $org, PDO::PARAM_INT);
        $ins->execute();
        return true;
    }

    /* ============================================
       HISTORY — TENANT-SCOPED
       ============================================ */
    public static function mdlRecordView($userId, $reportId) {
        $stmt = Connection::connect()->prepare(
            "INSERT INTO report_history (idUser, idReport, idOrganization) VALUES (:uid, :rid, :org)"
        );
        $org = Tenant::id();
        $stmt->bindParam(":uid", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":rid", $reportId, PDO::PARAM_INT);
        $stmt->bindParam(":org", $org, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function mdlGetRecentReports($userId, $limit = 10) {
        $stmt = Connection::connect()->prepare(
            "SELECT r.id, r.name, r.slug, r.route, rc.name AS categoryName, MAX(h.viewedDate) AS lastViewed
               FROM report_history h
               JOIN reports r ON r.id = h.idReport
               JOIN report_categories rc ON rc.id = r.idCategory
              WHERE h.idUser = :uid AND h.idOrganization = :org
              GROUP BY r.id
              ORDER BY MAX(h.viewedDate) DESC
              LIMIT :lim"
        );
        $org = Tenant::id();
        $stmt->bindParam(":uid", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":org", $org, PDO::PARAM_INT);
        $stmt->bindValue(":lim", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public static function mdlGetLastViewedByReport($reportId, $userId) {
        $stmt = Connection::connect()->prepare(
            "SELECT viewedDate FROM report_history
              WHERE idReport = :rid AND idUser = :uid AND idOrganization = :org
              ORDER BY viewedDate DESC LIMIT 1"
        );
        $org = Tenant::id();
        $stmt->bindParam(":rid", $reportId, PDO::PARAM_INT);
        $stmt->bindParam(":uid", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":org", $org, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? $row["viewedDate"] : null;
    }

    /* ============================================
       SEED: Register all categories and reports
       Categories and reports are seeded ONCE globally.
       ============================================ */
    public static function mdlSeedReports() {
        self::mdlEnsureTables();
        $link = Connection::connect();

        // Track if seeding was already done (check if categories exist)
        $check = $link->query("SELECT COUNT(*) n FROM report_categories WHERE is_active = 1")->fetch();
        if ((int)$check["n"] > 0) return; // Already seeded globally

        $categories = array(
            array("name"=>"Business Overview","slug"=>"business-overview","icon"=>"fa-dashboard","sort_order"=>1),
            array("name"=>"Sales","slug"=>"sales","icon"=>"fa-usd","sort_order"=>2),
            array("name"=>"Receivables","slug"=>"receivables","icon"=>"fa-arrow-circle-down","sort_order"=>3),
            array("name"=>"Payments Received","slug"=>"payments-received","icon"=>"fa-money","sort_order"=>4),
            array("name"=>"Payables","slug"=>"payables","icon"=>"fa-arrow-circle-up","sort_order"=>5),
            array("name"=>"Purchases and Expenses","slug"=>"purchases-expenses","icon"=>"fa-shopping-cart","sort_order"=>6),
            array("name"=>"Inventory","slug"=>"inventory","icon"=>"fa-cubes","sort_order"=>7),
            array("name"=>"Banking","slug"=>"banking","icon"=>"fa-university","sort_order"=>8),
            array("name"=>"Taxes","slug"=>"taxes","icon"=>"fa-percent","sort_order"=>9),
            array("name"=>"Accounting","slug"=>"accounting","icon"=>"fa-balance-scale","sort_order"=>10),
            array("name"=>"Audit and Compliance","slug"=>"audit-compliance","icon"=>"fa-shield","sort_order"=>11),
        );

        $catIds = array();
        foreach ($categories as $cat) {
            $ins = $link->prepare(Connection::insertIgnoreInto() . " report_categories (name, slug, icon, sort_order) VALUES (:name, :slug, :icon, :so)" . Connection::onConflictDoNothing());
            $ins->bindParam(":name", $cat["name"], PDO::PARAM_STR);
            $ins->bindParam(":slug", $cat["slug"], PDO::PARAM_STR);
            $ins->bindParam(":icon", $cat["icon"], PDO::PARAM_STR);
            $ins->bindParam(":so", $cat["sort_order"], PDO::PARAM_INT);
            $ins->execute();
            // Fetch the ID (either existing or newly inserted)
            $fetch = $link->prepare("SELECT id FROM report_categories WHERE slug = :slug");
            $fetch->bindParam(":slug", $cat["slug"], PDO::PARAM_STR);
            $fetch->execute();
            $row = $fetch->fetch();
            if ($row) $catIds[$cat["slug"]] = (int)$row["id"];
        }

        // Define all reports
        $reportDefs = array(
            array("cat"=>"business-overview","name"=>"Business Overview","slug"=>"business-overview","route"=>"report-overview","module"=>"accounting","desc"=>"KPIs and business performance summary.","icon"=>"fa-dashboard"),
            array("cat"=>"sales","name"=>"Sales Summary","slug"=>"sales-summary","route"=>"report-sales","module"=>"sales","desc"=>"Filters, totals and breakdown of all sales.","icon"=>"fa-shopping-cart"),
            array("cat"=>"sales","name"=>"Bestseller Products","slug"=>"bestseller-products","route"=>"report-overview","module"=>"sales","desc"=>"Top 5 selling products.","icon"=>"fa-trophy"),
            array("cat"=>"sales","name"=>"Sales by Customer","slug"=>"sales-by-customer","route"=>"report-sales","module"=>"sales","desc"=>"Revenue by customer.","icon"=>"fa-users"),
            array("cat"=>"sales","name"=>"Sales by Product","slug"=>"sales-by-product","route"=>"report-sales","module"=>"sales","desc"=>"Revenue by product line.","icon"=>"fa-product-hunt"),
            array("cat"=>"sales","name"=>"Cash Flow Statement","slug"=>"cash-flow-statement","route"=>"accounting-cash-flow","module"=>"accounting","desc"=>"Operating, investing and financing cash movements.","icon"=>"fa-money"),
            array("cat"=>"sales","name"=>"Sales Trends","slug"=>"sales-trends","route"=>"report-sales","module"=>"sales","desc"=>"Sales trends over time.","icon"=>"fa-line-chart"),
            array("cat"=>"sales","name"=>"Quotations Report","slug"=>"quotations-report","route"=>"quotations","module"=>"sales","desc"=>"Quotation activity report.","icon"=>"fa-file-text-o"),
            array("cat"=>"sales","name"=>"Invoice Report","slug"=>"invoice-report","route"=>"invoices","module"=>"sales","desc"=>"Invoice activity and values.","icon"=>"fa-file-text"),
            array("cat"=>"receivables","name"=>"Outstanding Invoices","slug"=>"outstanding-invoices","route"=>"report-receivables","module"=>"sales","desc"=>"Invoices with outstanding balances.","icon"=>"fa-hourglass-half"),
            array("cat"=>"receivables","name"=>"Accounts Receivable Detail","slug"=>"ar-detail","route"=>"accounting-accounts-receivable","module"=>"accounting","desc"=>"Aging analysis and customer balance details.","icon"=>"fa-clock-o"),
            array("cat"=>"payments-received","name"=>"Payments Summary","slug"=>"payments-summary","route"=>"report-payments","module"=>"sales","desc"=>"Summary of all payments received.","icon"=>"fa-money"),
            array("cat"=>"payables","name"=>"Payables Overview","slug"=>"payables-overview","route"=>"report-payables","module"=>"accounting","desc"=>"Outstanding liabilities and vendor balances.","icon"=>"fa-credit-card"),
            array("cat"=>"purchases-expenses","name"=>"Expense Summary","slug"=>"expense-summary","route"=>"accounting-expense-breakdown","module"=>"accounting","desc"=>"All expenses by category.","icon"=>"fa-credit-card"),
            array("cat"=>"purchases-expenses","name"=>"Expense by Category","slug"=>"expense-by-category","route"=>"accounting-expense-breakdown","module"=>"accounting","desc"=>"Expense distribution across accounts.","icon"=>"fa-pie-chart"),
            array("cat"=>"inventory","name"=>"Inventory Report","slug"=>"inventory-report","route"=>"report-inventory","module"=>"inventory","desc"=>"Stock levels and movement summary.","icon"=>"fa-cubes"),
            array("cat"=>"inventory","name"=>"Stock Valuation","slug"=>"stock-valuation","route"=>"accounting-inventory-valuation","module"=>"inventory","desc"=>"Inventory value at cost price.","icon"=>"fa-cubes"),
            array("cat"=>"taxes","name"=>"Tax Summary","slug"=>"tax-summary","route"=>"report-tax","module"=>"accounting","desc"=>"Tax collected and payable summary.","icon"=>"fa-percent"),
            array("cat"=>"accounting","name"=>"General Ledger","slug"=>"general-ledger","route"=>"accounting-general-ledger","module"=>"accounting","desc"=>"Detailed journal entries per account with running balances.","icon"=>"fa-book"),
            array("cat"=>"accounting","name"=>"Profit & Loss","slug"=>"profit-loss-detail","route"=>"accounting-profit-loss","module"=>"accounting","desc"=>"Revenue and expenses summary with KPIs.","icon"=>"fa-line-chart"),
            array("cat"=>"accounting","name"=>"Balance Sheet","slug"=>"balance-sheet-detail","route"=>"accounting-balance-sheet","module"=>"accounting","desc"=>"Assets, liabilities and equity with balance check.","icon"=>"fa-balance-scale"),
            array("cat"=>"accounting","name"=>"Trial Balance","slug"=>"trial-balance-detail","route"=>"accounting-trial-balance","module"=>"accounting","desc"=>"All accounts with debit and credit totals.","icon"=>"fa-calculator"),
            array("cat"=>"accounting","name"=>"Accounts Receivable","slug"=>"ar-detail","route"=>"accounting-accounts-receivable","module"=>"accounting","desc"=>"AR aging with customer breakdown.","icon"=>"fa-user"),
            array("cat"=>"accounting","name"=>"Accounts Payable","slug"=>"ap-detail","route"=>"accounting-accounts-payable","module"=>"accounting","desc"=>"Outstanding liabilities and expense summary.","icon"=>"fa-credit-card"),
            array("cat"=>"accounting","name"=>"Cash Flow Summary","slug"=>"cash-flow-summary","route"=>"accounting-cash-flow","module"=>"accounting","desc"=>"Operating, investing and financing cash movements.","icon"=>"fa-money"),
            array("cat"=>"accounting","name"=>"Expense Breakdown","slug"=>"expense-breakdown-detail","route"=>"accounting-expense-breakdown","module"=>"accounting","desc"=>"Expenses by category with percentage of total.","icon"=>"fa-pie-chart"),
            array("cat"=>"accounting","name"=>"Inventory Valuation","slug"=>"inventory-valuation-detail","route"=>"accounting-inventory-valuation","module"=>"accounting","desc"=>"Stock levels and cost value with potential revenue.","icon"=>"fa-cubes"),
            array("cat"=>"audit-compliance","name"=>"Activity Report","slug"=>"activity-report","route"=>"report-activity","module"=>"system","desc"=>"User login and activity history.","icon"=>"fa-history"),
            array("cat"=>"audit-compliance","name"=>"Customer Statement","slug"=>"customer-statement","route"=>"customer-statement","module"=>"system","desc"=>"Customer account statement report.","icon"=>"fa-user"),
        );

        // Register each report globally
        foreach ($reportDefs as $rpt) {
            $catId = $catIds[$rpt["cat"]] ?? 0;
            if ($catId == 0) continue;

            $check = $link->prepare("SELECT id FROM reports WHERE slug = :slug");
            $check->bindParam(":slug", $rpt["slug"], PDO::PARAM_STR);
            $check->execute();
            if ($check->fetch()) continue; // Already exists globally

            $ins = $link->prepare(
                "INSERT INTO reports (idCategory, name, slug, description, route, module, icon, permission_key)
                 VALUES (:catId, :name, :slug, :desc, :route, :mod, :icon, :perm)"
            );
            $perm = null;
            $ins->bindParam(":catId", $catId, PDO::PARAM_INT);
            $ins->bindParam(":name", $rpt["name"], PDO::PARAM_STR);
            $ins->bindParam(":slug", $rpt["slug"], PDO::PARAM_STR);
            $ins->bindParam(":desc", $rpt["desc"], PDO::PARAM_STR);
            $ins->bindParam(":route", $rpt["route"], PDO::PARAM_STR);
            $ins->bindParam(":mod", $rpt["module"], PDO::PARAM_STR);
            $ins->bindParam(":icon", $rpt["icon"], PDO::PARAM_STR);
            $ins->bindParam(":perm", $perm, PDO::PARAM_NULL);
            $ins->execute();
        }
    }
}