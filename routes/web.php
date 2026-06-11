<?php

/**
 * Web route map — the canonical list of page routes the front controller serves
 * and the permission each requires.
 *
 * The legacy front controller (index.php -> ControllerTemplate -> views/template.php)
 * currently whitelists these routes inline. This file is the central source of
 * truth to migrate toward; it can be consumed to drive routing/guards (see
 * AuthMiddleware) without changing behaviour today.
 *
 * Format:  'route' => ['permission' => <key|null>, 'super' => bool]
 */
return [
    // Dashboard
    'home'              => ['permission' => 'dashboard'],

    // Products
    'products'          => ['permission' => 'products'],
    'categories'        => ['permission' => 'products'],

    // Customers
    'customers'         => ['permission' => 'customers'],
    'customer-statement'=> ['permission' => 'sales'],

    // Sales / Quotations / Invoices
    'sales'             => ['permission' => 'sales'],
    'create-sale'       => ['permission' => 'sales'],
    'edit-sale'         => ['permission' => 'sales'],
    'sale-detail'       => ['permission' => 'sales'],
    'quotations'        => ['permission' => 'sales'],
    'create-quotation'  => ['permission' => 'sales'],
    'edit-quotation'    => ['permission' => 'sales'],
    'quotation-detail'  => ['permission' => 'sales'],
    'invoices'          => ['permission' => 'sales'],
    'create-invoice'    => ['permission' => 'sales'],
    'edit-invoice'      => ['permission' => 'sales'],
    'invoice-detail'    => ['permission' => 'sales'],

    // Reports Center + reports
    'reports-center'    => ['permission' => 'reports'],
    'reports'           => ['permission' => 'reports'],
    'report-overview'   => ['permission' => 'reports'],
    'report-sales'      => ['permission' => 'reports'],
    'report-inventory'  => ['permission' => 'reports'],
    'report-payables'   => ['permission' => 'reports'],
    'report-receivables'=> ['permission' => 'reports'],
    'report-payments'   => ['permission' => 'reports'],
    'report-activity'   => ['permission' => 'reports'],
    'report-tax'        => ['permission' => 'reports'],

    // Accounting
    'accounting'        => ['permission' => 'accounting'],
    'chart-of-accounts' => ['permission' => 'accounting'],

    // Expenses / Currencies
    'expenses'          => ['permission' => 'expenses'],
    'currencies'        => ['permission' => 'currencies'],

    // Administration
    'users'             => ['permission' => 'users'],
    'settings'          => ['permission' => 'settings'],
    'company-profile'   => ['permission' => 'settings'],

    // Super Admin (platform)
    'organizations'     => ['permission' => null, 'super' => true],
    'org-currencies'    => ['permission' => null, 'super' => true],
    'sa-profile'        => ['permission' => null, 'super' => true],

    // Auth (no permission gate)
    'login'             => ['permission' => null],
    'logout'            => ['permission' => null],
];
