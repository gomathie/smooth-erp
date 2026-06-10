<?php
/**
 * Accounts Payable Report
 *
 * Shows outstanding liabilities (vendor payables, credit card balances, etc.).
 * Derived from journal_lines for liability accounts.
 *
 * SQL: Aggregates liability account balances from journal_lines.
 */

require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$asOf = $_GET["to"] ?? date("Y-m-d");

$liabilityAccounts = AccountingReports::getAccountsByType("liability");

$totalPayable = 0.0;
$payableRows = array();

foreach ($liabilityAccounts as $acc) {
	$balance = AccountingReports::netBalance($acc);
	if ($balance > 0) {
		$totalPayable += $balance;
		$payableRows[] = array(
			"code"   => htmlspecialchars($acc["code"]),
			"name"   => htmlspecialchars($acc["name"]),
			"balance"=> $balance,
		);
	}
}

// Get expense-linked liabilities (expenses not yet paid via journal)
$expenseAccounts = AccountingReports::getAccountsByType("expense");
$totalExpenses = 0.0;
$expenseRows = array();
foreach ($expenseAccounts as $acc) {
	$balance = AccountingReports::netBalance($acc);
	if ($balance != 0) {
		$totalExpenses += abs($balance);
		$expenseRows[] = array(
			"code"   => htmlspecialchars($acc["code"]),
			"name"   => htmlspecialchars($acc["name"]),
			"amount" => abs($balance),
		);
	}
}

AccountingReports::renderHeader("Accounts Payable", "Accounts Payable", "accounting-accounts-payable", true, '', $asOf);
AccountingReports::renderPrintButton();

AccountingReports::renderKpiCards(array(
	array("label" => "Total Payable",  "value" => AccountingReports::fmtMoney($totalPayable),  "icon" => "fa-credit-card", "color" => "bg-red"),
	array("label" => "Total Expenses", "value" => AccountingReports::fmtMoney($totalExpenses), "icon" => "fa-credit-card", "color" => "bg-maroon"),
	array("label" => "Liability Accts", "value" => count($payableRows),                        "icon" => "fa-list-alt",    "color" => "bg-yellow"),
	array("label" => "As of",          "value" => $asOf,                                       "icon" => "fa-calendar",    "color" => "bg-aqua"),
));

// Liability Balances
echo '<div class="card card-warning card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-credit-card"></i> Liability Account Balances</h3></div>';
echo '<div class="card-body">';

$columns = array(
	array("key" => "code",    "label" => "Account Code", "align" => "left",  "format" => "text"),
	array("key" => "name",    "label" => "Account Name", "align" => "left",  "format" => "text"),
	array("key" => "balance", "label" => "Balance",      "align" => "right", "format" => "money"),
);
$totals = array("balance" => $totalPayable);
AccountingReports::renderTable($columns, $payableRows, $totals, 'table table-bordered table-striped', 'No outstanding liabilities.');
echo '</div></div>';

// Expense Summary
echo '<div class="card card-danger card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-credit-card"></i> Expense Account Totals</h3></div>';
echo '<div class="card-body">';
$expenseColumns = array(
	array("key" => "code",   "label" => "Account Code", "align" => "left",  "format" => "text"),
	array("key" => "name",   "label" => "Account Name", "align" => "left",  "format" => "text"),
	array("key" => "amount", "label" => "Amount",       "align" => "right", "format" => "money"),
);
$totals = array("amount" => $totalExpenses);
AccountingReports::renderTable($expenseColumns, $expenseRows, $totals, 'table table-bordered table-striped', 'No expenses recorded.');
echo '</div></div>';

// SQL
$sql = "SELECT a.code, a.name, a.type,
               COALESCE(SUM(jl.debit), 0)  AS debit,
               COALESCE(SUM(jl.credit), 0) AS credit,
               COALESCE(SUM(jl.credit), 0) - COALESCE(SUM(jl.debit), 0) AS balance
          FROM accounts a
          LEFT JOIN journal_lines jl ON jl.idAccount = a.id
               AND jl.idOrganization = :org
         WHERE a.idOrganization = :org AND a.type = 'liability'
         GROUP BY a.id, a.code, a.name, a.type
         HAVING balance > 0
         ORDER BY a.code ASC";

AccountingReports::renderSqlBlock("SQL: Liability Balances (Accounts Payable)", $sql);

AccountingReports::renderExplanation(
	"The Accounts Payable report shows money the business owes. Liability accounts "
	. "(2000-2999 range: Accounts Payable, Tax Payable, Credit Card, Accrued Expenses, etc.) "
	. "are queried from journal_lines. The balance is computed as credit - debit (credit-normal). "
	. "Only accounts with a positive balance (amounts owed) are shown. Expenses incurred but "
	. "not yet paid are also summarized. All data derives from the double-entry journal."
);

AccountingReports::renderFooter();