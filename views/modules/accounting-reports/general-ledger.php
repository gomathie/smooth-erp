<?php
/**
 * General Ledger Report
 *
 * Shows all journal line activity for each account, with running balances.
 * Derived from journal_entries + journal_lines (the single source of truth).
 *
 * SQL queries used:
 *   1. Get all accounts with balances (via AccountingReports::getAccountBalances)
 *   2. Get journal lines per account within date range (via AccountingReports::getJournalLinesForAccount)
 */

require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$from = $_GET["from"] ?? date("Y-01-01");
$to   = $_GET["to"]   ?? date("Y-m-d");

// Get all accounts that have activity
$accounts = AccountingReports::getAccountBalances();

// Filter to only accounts with activity
$activeAccounts = array_filter($accounts, function($a) {
	return (float)$a["debit"] > 0 || (float)$a["credit"] > 0;
});

$accountTypeLabels = array(
	"asset" => "Asset", "liability" => "Liability", "equity" => "Equity",
	"income" => "Income", "expense" => "Expense",
);

// Calculate grand totals
$grandDebit  = 0.0;
$grandCredit = 0.0;
foreach ($activeAccounts as $acc) {
	$grandDebit  += (float)$acc["debit"];
	$grandCredit += (float)$acc["credit"];
}

// Render header
AccountingReports::renderHeader("General Ledger", "General Ledger", "accounting-general-ledger", true, $from, $to);
AccountingReports::renderPrintButton();

// KPI cards
AccountingReports::renderKpiCards(array(
	array("label" => "Total Accounts", "value" => count($activeAccounts), "icon" => "fa-list-alt", "color" => "bg-aqua"),
	array("label" => "Total Debits", "value" => AccountingReports::fmtMoney($grandDebit), "icon" => "fa-arrow-down", "color" => "bg-green"),
	array("label" => "Total Credits", "value" => AccountingReports::fmtMoney($grandCredit), "icon" => "fa-arrow-up", "color" => "bg-red"),
	array("label" => "Period", "value" => $from . " to " . $to, "icon" => "fa-calendar", "color" => "bg-yellow"),
));

// Render each account's ledger
foreach ($activeAccounts as $acc) {
	$lines = AccountingReports::getJournalLinesForAccount((int)$acc["id"], $from, $to);
	$runningBalance = 0.0;
	$normalDebit = in_array($acc["type"], array("asset", "expense"), true);

	echo '<div class="card card-primary card-outline">';
	echo '<div class="card-header">';
	echo '<h3 class="card-title"><i class="fa fa-book"></i> ' . htmlspecialchars($acc["code"] . " - " . $acc["name"]) . '</h3>';
	echo '<span class="badge float-end" style="background:' . ($normalDebit ? '#00a65a' : '#00c0ef') . '; color:#fff;">' . ($accountTypeLabels[$acc["type"]] ?? ucfirst($acc["type"])) . '</span>';
	echo '</div>';
	echo '<div class="card-body">';

	$columns = array(
		array("key" => "entryDate",    "label" => "Date",        "align" => "left",   "format" => "text"),
		array("key" => "reference",    "label" => "Reference",   "align" => "left",   "format" => "text"),
		array("key" => "description",  "label" => "Description", "align" => "left",   "format" => "text"),
		array("key" => "debit",        "label" => "Debit",       "align" => "right",  "format" => "money"),
		array("key" => "credit",       "label" => "Credit",      "align" => "right",  "format" => "money"),
		array("key" => "balance",      "label" => "Balance",     "align" => "right",  "format" => "money"),
	);

	// Build rows with running balance
	$tableRows = array();
	$totalDebit  = 0.0;
	$totalCredit = 0.0;

	foreach ($lines as $line) {
		$debit  = (float)$line["debit"];
		$credit = (float)$line["credit"];
		$totalDebit  += $debit;
		$totalCredit += $credit;

		if ($normalDebit) {
			$runningBalance += $debit - $credit;
		} else {
			$runningBalance += $credit - $debit;
		}

		$tableRows[] = array(
			"entryDate"   => $line["entryDate"],
			"reference"   => htmlspecialchars($line["reference"] ?? ''),
			"description" => htmlspecialchars($line["description"] ?? ''),
			"debit"       => $debit > 0 ? $debit : '',
			"credit"      => $credit > 0 ? $credit : '',
			"balance"     => $runningBalance,
		);
	}

	$totals = array(
		"debit"  => $totalDebit,
		"credit" => $totalCredit,
	);

	AccountingReports::renderTable($columns, $tableRows, $totals);

	echo '</div>';
	echo '</div>';
}

// SQL documentation
$mainSql = "SELECT a.id, a.code, a.name, a.type,
                   COALESCE(SUM(jl.debit), 0)  AS debit,
                   COALESCE(SUM(jl.credit), 0) AS credit
              FROM accounts a
              LEFT JOIN journal_lines jl ON jl.idAccount = a.id
                   AND jl.idOrganization = :org
             WHERE a.idOrganization = :org
             GROUP BY a.id, a.code, a.name, a.type
             ORDER BY a.code ASC";

$detailSql = "SELECT je.entryDate, je.reference, je.sourceType, je.description,
                     jl.debit, jl.credit
                FROM journal_lines jl
                JOIN journal_entries je ON je.id = jl.idJournalEntry
               WHERE jl.idAccount = :accountId
                 AND jl.idOrganization = :org
                 AND je.entryDate >= :fromDate
                 AND je.entryDate <= :toDate
               ORDER BY je.entryDate ASC, je.id ASC";

AccountingReports::renderSqlBlock("SQL: Account Balances", $mainSql);
AccountingReports::renderSqlBlock("SQL: Account Detail Lines (per account)", $detailSql);

AccountingReports::renderExplanation(
	"The General Ledger lists every journal entry line for each account. "
	. "A running balance is computed per account: for asset/expense accounts, "
	. "balance = cumulative debits - cumulative credits. For liability/equity/income accounts, "
	. "balance = cumulative credits - cumulative debits. All data derives from journal_lines joined "
	. "with journal_entries, filtered by the date range. No hardcoded values are used."
);

AccountingReports::renderFooter();