<?php

// Logging + global error handling first, so failures anywhere below are captured.
require_once "app/Helpers/Logger.php";
require_once "app/Exceptions/Handler.php";
Handler::register();

require_once "helpers/security.php";
require_once "helpers/mailer.php";
require_once "helpers/tenant.php";
require_once "helpers/currency.php";
require_once "helpers/i18n.php";
require_once "controllers/template.controller.php";
require_once "controllers/users.controller.php";
require_once "controllers/categories.controller.php";
require_once "controllers/products.controller.php";
require_once "controllers/customers.controller.php";
require_once "controllers/sales.controller.php";
require_once "controllers/invoices.controller.php";
require_once "controllers/payments.controller.php";
require_once "controllers/accounting.controller.php";
require_once "controllers/expenses.controller.php";
require_once "controllers/settings.controller.php";
require_once "controllers/quotations.controller.php";
require_once "controllers/reports.controller.php";
require_once "controllers/superadmin.controller.php";
require_once "controllers/currencies.controller.php";

require_once "models/users.model.php";
require_once "models/categories.model.php";
require_once "models/products.model.php";
require_once "models/customers.model.php";
require_once "models/sales.model.php";
require_once "models/invoices.model.php";
require_once "models/payments.model.php";
require_once "models/accounting.model.php";
require_once "models/inventory.model.php";
require_once "models/expenses.model.php";
require_once "models/settings.model.php";
require_once "models/quotations.model.php";
require_once "models/organizations.model.php";

require_once "extensions/vendor/autoload.php";

$template = new ControllerTemplate();
$template -> ctrTemplate();
