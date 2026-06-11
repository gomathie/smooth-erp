<?php

/**
 * PostgreSQL camelCase compatibility layer.
 *
 * PostgreSQL folds unquoted identifiers to lower case, so the app's camelCase
 * columns (idOrganization, sellingPrice, …) are created lower case in Postgres
 * and the app's unquoted SQL (`WHERE idOrganization = ?`, `INSERT ... (idCustomer)`)
 * matches them automatically. The only gap is SELECT results: Postgres returns
 * lower-cased keys, but the PHP reads `$row["idOrganization"]`.
 *
 * CamelCaseStatement (installed as the PDO statement class ONLY for the pgsql
 * driver) remaps each fetched row's keys back to camelCase via ColumnMap. The
 * MySQL driver does not use this class at all, so MySQL behaviour is unchanged.
 */

class ColumnMap
{
    /**
     * Canonical camelCase names (table columns + camelCase SELECT aliases used
     * in the code). Anything not listed is passed through unchanged — including
     * intentionally lower-case columns such as `date`, `code`, `name`, `status`.
     */
    private static array $camel = [
        // columns
        'amountPaid', 'balanceDue', 'baseCurrency', 'buyingPrice', 'createdBy',
        'createdDate', 'currencyCode', 'discountType', 'discountValue', 'dueDate',
        'entryDate', 'expenseDate', 'expenseNumber', 'expiryDate', 'idAccount',
        'idCategory', 'idCustomer', 'idDocument', 'idExpenseAccount', 'idInvoice',
        'idJournalEntry', 'idOrganization', 'idPaidThrough', 'idProduct', 'idSeller',
        'idUser', 'invoiceDate', 'invoiceNumber', 'isBase', 'isSystem', 'lastLogin',
        'lastPurchase', 'maxUsers', 'modifiedBy', 'modifiedDate', 'movementDate',
        'netPrice', 'orderReference', 'paymentDate', 'paymentMethod', 'paymentMode',
        'paymentNumber', 'paymentTerms', 'postalCode', 'qtyChange', 'quoteDate',
        'quoteNumber', 'registerDate', 'resetToken', 'resetTokenExpires',
        'sellingPrice', 'settingKey', 'settingValue', 'sourceId', 'sourceType',
        'termsConditions', 'themeColor', 'totalPrice', 'unitCost', 'Category',
        // camelCase SELECT aliases used in models
        'accountCode', 'accountName', 'categoryName', 'customerName', 'expenseCode',
        'expenseName', 'lastViewed', 'nextId', 'paidCode', 'paidName',
        'totalMovementQty', 'totalMovementValue',
    ];

    private static ?array $map = null;

    public static function toCamel(array $row): array
    {
        if (self::$map === null) {
            self::$map = [];
            foreach (self::$camel as $c) {
                self::$map[strtolower($c)] = $c;
            }
        }
        $out = [];
        foreach ($row as $k => $v) {
            if (is_string($k) && isset(self::$map[strtolower($k)])) {
                $out[self::$map[strtolower($k)]] = $v;
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}

class CamelCaseStatement extends PDOStatement
{
    protected function __construct() {}

    #[\ReturnTypeWillChange]
    public function fetch($mode = PDO::FETCH_DEFAULT, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        $row = parent::fetch($mode, $cursorOrientation, $cursorOffset);
        return is_array($row) ? ColumnMap::toCamel($row) : $row;
    }

    #[\ReturnTypeWillChange]
    public function fetchAll($mode = PDO::FETCH_DEFAULT, ...$args)
    {
        $rows = parent::fetchAll($mode, ...$args);
        if (is_array($rows)) {
            foreach ($rows as &$r) {
                if (is_array($r)) {
                    $r = ColumnMap::toCamel($r);
                }
            }
        }
        return $rows;
    }
}
