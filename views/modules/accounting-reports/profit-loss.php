<?php
/**
 * Profit & Loss Statement
 *
 * Shows revenue minus expenses for a given period.
 * All figures derived from journal_lines (no hardcoded values).
 *
 * SQL: Aggregates debit/credit from journal_lines grouped by account type.
 */

require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$from = $_GET["from"] ?? date("Y-01-01");
$to   = $_GET["to"]   ?? date("Y-m-d");

$incomeAccounts  = AccountingReports::getAccountsByType("income");
$expenseAccounts = AccountingReports::getAccountsByType("expense");

$totalIncome  = 0.0;
$totalExpense = 0.0;

$incomeRows = array();
foreach ($incomeAccounts as $acc) {
	$balance = AccountingReports::netBalance($acc);
	if ($balance != 0) {
		$totalIncome += $balance;
		$incomeRows[] = array(
			"code"   => htmlspecialchars($acc["code"]),
			"name"   => htmlspecialchars($acc["name"]),
			"amount" => $balance,
		);
	}
}

$expenseRows = array();
foreach ($expenseAccounts as $acc) {
	$balance = AccountingReports::netBalance($acc);
	if ($balance != 0) {
		$totalExpense += $balance;
		$expenseRows[] = array(
			"code"   => htmlspecialchars($acc["code"]),
			"name"   => htmlspecialchars($acc["name"]),
			"amount" => abs($balance),
		);
	}
}

$netIncome = $totalIncome - $totalExpense;

AccountingReports::renderHeader("Profit & Loss Statement", "Profit & Loss", "accounting-profit-loss", true, $from, $to);
AccountingReports::renderPrintButton();

AccountingReports::renderKpiCards(array(
	array("label" => "Total Income",     "value" => AccountingReports::fmtMoney($totalIncome),  "icon" => "fa-arrow-up",   "color" => "bg-green"),
	array("label" => "Total Expenses",   "value" => AccountingReports::fmtMoney($totalExpense), "icon" => "fa-arrow-down", "color" => "bg-red"),
	array("label" => "Net Profit/Loss",  "value" => AccountingReports::fmtMoney($netIncome),    "icon" => "fa-balance-scale", "color" => ($netIncome >= 0 ? "bg-green" : "bg-red")),
	array("label" => "Period",           "value" => $from . " to " . $to,                       "icon" => "fa-calendar",   "color" => "bg-aqua"),
));

// Income Section
echo '<div class="card card-success card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-line-chart"></i> Income (Revenue)</h3></div>';
echo '<div class="card-body">';

$columns = array(
	array("key" => "code",   "label" => "Account Code", "align" => "left",  "format" => "text"),
	array("key" => "name",   "label" => "Account Name", "align" => "left",  "format" => "text"),
	array("key" => "amount", "label" => "Amount",       "align" => "right", "format" => "money"),
);
$totals = array("amount" => $totalIncome);
AccountingReports::renderTable($columns, $incomeRows, $totals, 'table table-bordered table-striped', 'No income recorded for this period.');
echo '</div></div>';

// Expense Section
echo '<div class="card card-danger card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-credit-card"></i> Expenses</h3></div>';
echo '<div class="card-body">';
$totals = array("amount" => $totalExpense);
AccountingReports::renderTable($columns, $expenseRows, $totals, 'table table-bordered table-striped', 'No expenses recorded for this period.');
echo '</div></div>';

// Net Income Summary
$netColor = $netIncome >= 0 ? '#00a65a' : '#dd4b39';
echo '<div class="card card-outline" style="border-color:' . $netColor . ';">';
echo '<div class="card-body text-center" style="padding:20px;">';
echo '<h2 style="color:' . $netColor . ';">Net ' . ($netIncome >= 0 ? 'Profit' : 'Loss') . ': ' . AccountingReports::fmtMoney($netIncome) . '</h2>';
echo '</div></div>';

// SQL Documentation
$incomeSql = "SELECT a.code, a.name, a.type,
                     COALESCE(SUM(jl.debit), 0)  AS debit,
                     COALESCE(SUM(jl.credit), 0) AS credit
                FROM accounts a
                LEFT JOIN journal_lines jl ON jl.idAccount = a.id
                     AND jl.idOrganization = :org
               WHERE a.idOrganization = :org AND a.type = 'income'
               GROUP BY a.id, a.code, a.name, a.type
               ORDER BY a.code ASC";

$expenseSql = "SELECT a.code, a.name, a.type,
                      COALESCE(SUM(jl.debit), 0)  AS debit,
                      COALESCE(SUM(jl.credit), 0) AS credit
                 FROM accounts a
                 LEFT JOIN journal_lines jl ON jl.idAccount = a.id
                      AND jl.idOrganization = :org
                WHERE a.idOrganization = :org AND a.type = 'expense'
                GROUP BY a.id, a.code, a.name, a.type
                ORDER BY a.code ASC";

AccountingReports::renderSqlBlock("SQL: Income Accounts", $incomeSql);
AccountingReports::renderSqlBlock("SQL: Expense Accounts", $expenseSql);

AccountingReports::renderExplanation(
	"The Profit & Loss statement summarizes all income and expense accounts. "
	. "Income balances (credit-normal) and expense balances (debit-normal) are derived "
	. "from journal_lines by computing COALESCE(SUM(debit),0) and COALESCE(SUM(credit),0) "
	. "per account. Net Profit = Total Income - Total Expenses. All figures come directly "
	. "from the double-entry ledger with no hardcoded values."
);

AccountingReports::renderFooter();