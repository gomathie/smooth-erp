<?php
/**
 * Tenant-scope guard (regression test for multi-tenancy data isolation).
 * -----------------------------------------------------------------------------
 * Every query against a business (tenant-owned) table MUST be scoped by
 * idOrganization, otherwise one organization could read/modify another's data.
 * This guard scans models/ for SQL string literals that reference a business
 * table in a FROM / JOIN / INTO / UPDATE / DELETE FROM position and fails if the
 * same statement does not mention `idOrganization`.
 *
 * Run:   php tests/tenant_scope_guard.php
 * Exit:  0 = all scoped, 1 = violation(s) found.
 *
 * Limitations: it inspects literal table names only (queries built with a
 * `FROM $table` variable are not analysed). It is a cheap regression net, not a
 * full SQL parser. Add genuine, reviewed exceptions to $ALLOW below.
 */

$ROOT = dirname(__DIR__);
$MODELS = $ROOT . '/models';

// Tenant-owned tables that must always be org-scoped.
$BUSINESS = [
    'sales', 'products', 'categories', 'customers', 'invoices', 'quotations',
    'expenses', 'payments_received', 'accounts', 'journal_entries',
    'journal_lines', 'stock_movements', 'invoice_activity_log',
];

// Reviewed, intentional exceptions: [file basename, substring that identifies the query].
// e.g. the chart-of-accounts seed clones the template org (idOrganization = 1) on purpose.
$ALLOW = [
    // (none currently — the seed clone already contains "idOrganization")
];

$tablesAlt = implode('|', array_map('preg_quote', $BUSINESS));
$violations = [];

foreach (glob($MODELS . '/*.php') as $file) {
    $code = file_get_contents($file);
    $base = basename($file);

    // Extract plain string literals via the PHP tokenizer (so comments and
    // apostrophes inside comments can't be mistaken for SQL).
    $strings = [];
    foreach (token_get_all($code) as $tok) {
        if (is_array($tok) && $tok[0] === T_CONSTANT_ENCAPSED_STRING) {
            $strings[] = substr($tok[1], 1, -1); // strip surrounding quotes
        }
    }

    foreach ($strings as $sql) {
        if ($sql === '') { continue; }

        // Does this string reference a business table in a real table position?
        if (!preg_match('/\b(?:FROM|JOIN|INTO|UPDATE)\s+`?(' . $tablesAlt . ')`?\b/i', $sql, $tm)) {
            continue;
        }

        // Scoped? (the literal "idOrganization" appears in the same statement)
        if (stripos($sql, 'idOrganization') !== false) { continue; }

        // Allowlisted?
        $allowed = false;
        foreach ($ALLOW as [$af, $asub]) {
            if ($af === $base && strpos($sql, $asub) !== false) { $allowed = true; break; }
        }
        if ($allowed) { continue; }

        $violations[] = [$base, $tm[1], trim(preg_replace('/\s+/', ' ', $sql))];
    }
}

if (empty($violations)) {
    echo "PASS: every business-table query in models/ is scoped by idOrganization.\n";
    exit(0);
}

echo "FAIL: " . count($violations) . " business-table query(ies) NOT scoped by idOrganization:\n\n";
foreach ($violations as [$f, $t, $sql]) {
    echo "  [$f] table '$t':\n    " . substr($sql, 0, 160) . "\n\n";
}
echo "Fix: add `AND idOrganization = \" . (int)Tenant::id() . \"` (or :__org) to the query,\n";
echo "or, if genuinely cross-tenant, add a reviewed entry to \$ALLOW in this guard.\n";
exit(1);
