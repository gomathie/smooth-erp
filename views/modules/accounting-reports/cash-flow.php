<?php
/**
 * Cash Flow Summary
 * Shows cash movements: Operating, Investing, and Financing activities.
 * SQL: Joins journal_lines to cash/bank accounts and categorizes by source type.
 */
require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$from = $_GET["from"] ?? date("Y-01-01");
$to   = $_GET["to"]   ?? date("Y-m-d");

$link = Connection::connect();
$org = Tenant::id();
$cashCodes = array("1000", "1010", "1150");
$placeholders = implode(",", array_fill(0, count($cashCodes), "?"));

// Cash inflows (credits to cash = money received)
$inflowSql = "SELECT je.entryDate, je.reference, je.sourceType, je.description,
                     jl.credit AS amount
                FROM journal_lines jl
                JOIN journal_entries je ON je.id = jl.idJournalEntry
                JOIN accounts a ON a.id = jl.idAccount
               WHERE jl.idOrganization = ?
                 AND a.code IN ($placeholders)
                 AND jl.credit > 0
                 AND je.entryDate >= ? AND je.entryDate <= ?
               ORDER BY je.entryDate ASC";
$stmt = $link->prepare($inflowSql);
$params = array_merge(array($org), $cashCodes, array($from, $to));
foreach ($params as $i => $v) {
	$stmt->bindValue($i + 1, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$inflows = $stmt->fetchAll() ?: [];

// Cash outflows (debits to cash = money spent)
$outflowSql = "SELECT je.entryDate, je.reference, je.sourceType, je.description,
                      jl.debit AS amount
                 FROM journal_lines jl
                 JOIN journal_entries je ON je.id = jl.idJournalEntry
                 JOIN accounts a ON a.id = jl.idAccount
                WHERE jl.idOrganization = ?
                  AND a.code IN ($placeholders)
                  AND jl.debit > 0
                  AND je.entryDate >= ? AND je.entryDate <= ?
                ORDER BY je.entryDate ASC";
$stmt2 = $link->prepare($outflowSql);
$params2 = array_merge(array($org), $cashCodes, array($from, $to));
foreach ($params2 as $i => $v) {
	$stmt2->bindValue($i + 1, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt2->execute();
$outflows = $stmt2->fetchAll() ?: [];

// Categorize
$operatingIn = 0.0; $operatingOut = 0.0;
$investingIn = 0.0; $investingOut = 0.0;
$financingIn = 0.0; $financingOut = 0.0;

function cfCategorize($src, $amt, &$oI, &$oO, &$iI, &$iO, &$fI, &$fO) {
	$s = strtolower($src);
	if (in_array($s, array("invoice","payment","expense","sale"))) {
		if ($amt > 0) $oI += $amt; else $oO += abs($amt);
	} elseif (in_array($s, array("asset","fixed_asset","investment"))) {
		if ($amt > 0) $iI += $amt; else $iO += abs($amt);
	} else {
		if ($amt > 0) $fI += $amt; else $fO += abs($amt);
	}
}

$totalInflow = 0.0; $totalOutflow = 0.0;
$inflowRows = array();
foreach ($inflows as $r) {
	$amt = (float)$r["amount"];
	$totalInflow += $amt;
	cfCategorize($r["sourceType"], $amt, $operatingIn, $operatingOut, $investingIn, $investingOut, $financingIn, $financingOut);
	$inflowRows[] = array("date"=>$r["entryDate"],"reference"=>htmlspecialchars($r["reference"]??""),"type"=>htmlspecialchars($r["sourceType"]),"description"=>htmlspecialchars($r["description"]??""),"amount"=>$amt);
}
$outflowRows = array();
foreach ($outflows as $r) {
	$amt = (float)$r["amount"];
	$totalOutflow += $amt;
	cfCategorize($r["sourceType"], -$amt, $operatingIn, $operatingOut, $investingIn, $investingOut, $financingIn, $financingOut);
	$outflowRows[] = array("date"=>$r["entryDate"],"reference"=>htmlspecialchars($r["reference"]??""),"type"=>htmlspecialchars($r["sourceType"]),"description"=>htmlspecialchars($r["description"]??""),"amount"=>$amt);
}
$netCashFlow = $totalInflow - $totalOutflow;

AccountingReports::renderHeader("Cash Flow Summary","Cash Flow","accounting-cash-flow",true,$from,$to);
AccountingReports::renderPrintButton();
AccountingReports::renderKpiCards(array(
	array("label"=>"Cash Inflows","value"=>AccountingReports::fmtMoney($totalInflow),"icon"=>"fa-arrow-down","color"=>"bg-green"),
	array("label"=>"Cash Outflows","value"=>AccountingReports::fmtMoney($totalOutflow),"icon"=>"fa-arrow-up","color"=>"bg-red"),
	array("label"=>"Net Cash Flow","value"=>AccountingReports::fmtMoney($netCashFlow),"icon"=>"fa-exchange","color"=>($netCashFlow>=0?"bg-green":"bg-red")),
	array("label"=>"Period","value"=>$from." to ".$to,"icon"=>"fa-calendar","color"=>"bg-aqua"),
));

// Category Summary
echo '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fa fa-sitemap"></i> Cash Flow by Category</h3></div><div class="card-body">';
$catCols = array(array("key"=>"category","label"=>"Category","align"=>"left","format"=>"text"),array("key"=>"inflow","label"=>"Inflows","align"=>"right","format"=>"money"),array("key"=>"outflow","label"=>"Outflows","align"=>"right","format"=>"money"),array("key"=>"net","label"=>"Net","align"=>"right","format"=>"money"));
$catRows = array(array("category"=>"Operating Activities","inflow"=>$operatingIn,"outflow"=>$operatingOut,"net"=>$operatingIn-$operatingOut),array("category"=>"Investing Activities","inflow"=>$investingIn,"outflow"=>$investingOut,"net"=>$investingIn-$investingOut),array("category"=>"Financing Activities","inflow"=>$financingIn,"outflow"=>$financingOut,"net"=>$financingIn-$financingOut));
AccountingReports::renderTable($catCols,$catRows,array("inflow"=>$totalInflow,"outflow"=>$totalOutflow,"net"=>$netCashFlow));
echo '</div></div>';

// Cash Inflows Detail
$detailCols = array(array("key"=>"date","label"=>"Date","align"=>"left","format"=>"text"),array("key"=>"reference","label"=>"Reference","align"=>"left","format"=>"text"),array("key"=>"type","label"=>"Type","align"=>"left","format"=>"text"),array("key"=>"description","label"=>"Description","align"=>"left","format"=>"text"),array("key"=>"amount","label"=>"Amount","align"=>"right","format"=>"money"));
echo '<div class="card card-success card-outline"><div class="card-header"><h3 class="card-title"><i class="fa fa-arrow-down"></i> Cash Inflows</h3></div><div class="card-body">';
AccountingReports::renderTable($detailCols, $inflowRows, array("amount"=>$totalInflow));
echo '</div></div>';
echo '<div class="card card-danger card-outline"><div class="card-header"><h3 class="card-title"><i class="fa fa-arrow-up"></i> Cash Outflows</h3></div><div class="card-body">';
AccountingReports::renderTable($detailCols, $outflowRows, array("amount"=>$totalOutflow));
echo '</div></div>';
$sqlCash = "SELECT je.entryDate, je.reference, je.sourceType, jl.debit, jl.credit FROM journal_lines jl JOIN journal_entries je ON je.id = jl.idJournalEntry JOIN accounts a ON a.id = jl.idAccount WHERE jl.idOrganization = :org AND a.code IN ('1000','1010','1150') AND je.entryDate >= :fromDate AND je.entryDate <= :toDate ORDER BY je.entryDate ASC";
AccountingReports::renderSqlBlock("SQL: Cash Account Journal Lines", $sqlCash);
AccountingReports::renderExplanation("The Cash Flow Summary tracks movements through cash/bank accounts (1000,1010,1150). Inflows are credits, outflows are debits. Categorized into Operating, Investing, and Financing activities. All data from journal_lines.");
AccountingReports::renderFooter();
