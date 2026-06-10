<?php
/**
 * Expense Breakdown Report
 * Shows expenses by category/account with totals.
 * SQL: Queries expenses table joined with accounts.
 */
require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$from = $_GET["from"] ?? date("Y-01-01");
$to   = $_GET["to"]   ?? date("Y-m-d");

$expenses = AccountingReports::getExpenses($from, $to);
$totalExpenses = 0.0;
$byCategory = array();

foreach ($expenses as $ex) {
	$amount = (float)$ex["amount"];
	$totalExpenses += $amount;
	$code = $ex["accountCode"];
	if (!isset($byCategory[$code])) {
		$byCategory[$code] = array("code"=>$code,"name"=>$ex["accountName"],"total"=>0.0,"count"=>0);
	}
	$byCategory[$code]["total"] += $amount;
	$byCategory[$code]["count"]++;
}
uasort($byCategory, function($a,$b){ return $b["total"] <=> $a["total"]; });

AccountingReports::renderHeader("Expense Breakdown","Expense Breakdown","accounting-expense-breakdown",true,$from,$to);
AccountingReports::renderPrintButton();
AccountingReports::renderKpiCards(array(
	array("label"=>"Total Expenses","value"=>AccountingReports::fmtMoney($totalExpenses),"icon"=>"fa-credit-card","color"=>"bg-red"),
	array("label"=>"Expense Categories","value"=>count($byCategory),"icon"=>"fa-list-alt","color"=>"bg-maroon"),
	array("label"=>"Total Entries","value"=>count($expenses),"icon"=>"fa-file-text-o","color"=>"bg-yellow"),
	array("label"=>"Period","value"=>$from." to ".$to,"icon"=>"fa-calendar","color"=>"bg-aqua"),
));

// Category breakdown
echo '<div class="card card-danger card-outline"><div class="card-header"><h3 class="card-title"><i class="fa fa-pie-chart"></i> Expenses by Category</h3></div><div class="card-body">';
$catCols = array(array("key"=>"code","label"=>"Account","align"=>"left","format"=>"text"),array("key"=>"name","label"=>"Category","align"=>"left","format"=>"text"),array("key"=>"count","label"=>"Entries","align"=>"right","format"=>"number"),array("key"=>"total","label"=>"Amount","align"=>"right","format"=>"money"),array("key"=>"pct","label"=>"% of Total","align"=>"right","format"=>"text"));
$catRows = array();
foreach ($byCategory as $cat) {
	$pct = $totalExpenses > 0 ? number_format(($cat["total"]/$totalExpenses)*100, 1).'%' : '0.0%';
	$catRows[] = array("code"=>htmlspecialchars($cat["code"]),"name"=>htmlspecialchars($cat["name"]),"count"=>$cat["count"],"total"=>$cat["total"],"pct"=>$pct);
}
AccountingReports::renderTable($catCols,$catRows,array("count"=>count($expenses),"total"=>$totalExpenses));
echo '</div></div>';

// Detail list
echo '<div class="card card-outline"><div class="card-header"><h3 class="card-title"><i class="fa fa-list"></i> All Expenses</h3></div><div class="card-body">';
$detailCols = array(array("key"=>"date","label"=>"Date","align"=>"left","format"=>"text"),array("key"=>"number","label"=>"Expense #","align"=>"left","format"=>"text"),array("key"=>"account","label"=>"Account","align"=>"left","format"=>"text"),array("key"=>"payee","label"=>"Payee","align"=>"left","format"=>"text"),array("key"=>"amount","label"=>"Amount","align"=>"right","format"=>"money"));
$detailRows = array();
foreach ($expenses as $ex) {
	$detailRows[] = array("date"=>$ex["expenseDate"],"number"=>htmlspecialchars($ex["expenseNumber"]),"account"=>htmlspecialchars($ex["accountCode"]." - ".$ex["accountName"]),"payee"=>htmlspecialchars($ex["payee"]??""),"amount"=>(float)$ex["amount"]);
}
AccountingReports::renderTable($detailCols,$detailRows,array("amount"=>$totalExpenses));
echo '</div></div>';

$sql = "SELECT e.*, a.name AS accountName, a.code AS accountCode FROM expenses e JOIN accounts a ON a.id = e.idExpenseAccount WHERE e.idOrganization = :org AND e.expenseDate >= :fromDate AND e.expenseDate <= :toDate ORDER BY e.expenseDate ASC";
AccountingReports::renderSqlBlock("SQL: Expenses with Account Info",$sql);
AccountingReports::renderExplanation("The Expense Breakdown groups all recorded expenses by their expense account (Rent, Utilities, Salaries, etc.). Each expense is linked to an account via idExpenseAccount. The percentage of total is computed for each category. All data from the expenses table joined with accounts.");
AccountingReports::renderFooter();