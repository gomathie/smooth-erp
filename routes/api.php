<?php

/**
 * API / AJAX endpoint map.
 *
 * These PHP endpoints under /ajax are called directly by the browser (DataTables
 * server-side data, CRUD actions, theme switch). Listed here as the central
 * inventory of API surface; all require an authenticated session and send the
 * CSRF token via the X-CSRF-Token header (see views/js/template.js).
 *
 * Format:  'name' => ['file' => <path>, 'auth' => bool]
 */
return [
    'users'                     => ['file' => 'ajax/users.ajax.php',                     'auth' => true],
    'categories'                => ['file' => 'ajax/categories.ajax.php',                'auth' => true],
    'products'                  => ['file' => 'ajax/products.ajax.php',                  'auth' => true],
    'customers'                 => ['file' => 'ajax/customers.ajax.php',                 'auth' => true],
    'theme'                     => ['file' => 'ajax/theme.ajax.php',                     'auth' => true],

    // DataTables server-side feeds
    'datatable-products'        => ['file' => 'ajax/datatable-products.ajax.php',        'auth' => true],
    'datatable-sales'           => ['file' => 'ajax/datatable-sales.ajax.php',           'auth' => true],
    'datatable-invoice-products'=> ['file' => 'ajax/datatable-invoice-products.ajax.php','auth' => true],
    'datatable-quotation-products' => ['file' => 'ajax/datatable-quotation-products.ajax.php', 'auth' => true],
];
