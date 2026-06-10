<?php
/**
 * Balance Sheet
 *
 * Shows Assets = Liabilities + Equity at a point in time.
 * All figures derived from journal_lines (no hardcoded values).
 *
 * SQL: Aggregates debit/credit from journal_lines grouped by account type.
 */

require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$asOf = $_GET["to"] ?? date("Y-m-d");

$assetAccounts     = AccountingReports::getAccountsByType("asset");
$liabilityAccounts = AccountingReports::getAccountsByType("liability");
$equityAccounts    = AccountingReports::getAccountsByType("equity");

$totalAssets     = 0.0;
$totalLiabilities = 0.0;
$totalEquity     = 0.0;

$assetRows = array();
foreach ($assetAccounts as $acc) {
	$balance = AccountingReports::netBalance($acc);
	if ($balance != 0) {
		$totalAssets += $balance;
		$assetRows[] = array(
			"code"   => htmlspecialchars($acc["code"]),
			"name"   => htmlspecialchars($acc["name"]),
			"amount" => $balance,
		);
	}
}

$liabilityRows = array();
foreach ($liabilityAccounts as $acc) {
	$balance = AccountingReports::netBalance($acc);
	if ($balance != 0) {
		$totalLiabilities += $balance;
		$liabilityRows[] = array(
			"code"   => htmlspecialchars($acc["code"]),
			"name"   => htmlspecialchars($acc["name"]),
			"amount" => $balance,
		);
	}
}

$equityRows = array();
foreach ($equityAccounts as $acc) {
	$balance = AccountingReports::netBalance($acc);
	if ($balance != 0) {
		$totalEquity += $balance;
		$equityRows[] = array(
			"code"   => htmlspecialchars($acc["code"]),
			"name"   => htmlspecialchars($acc["name"]),
			"amount" => $balance,
		);
	}
}

$totalLiabEq = $totalLiabilities + $totalEquity;

AccountingReports::renderHeader("Balance Sheet", "Balance Sheet", "accounting-balance-sheet", true, '', $asOf);
AccountingReports::renderPrintButton();

AccountingReports::renderKpiCards(array(
	array("label" => "Total Assets",       "value" => AccountingReports::fmtMoney($totalAssets),     "icon" => "fa-inbox",           "color" => "bg-aqua"),
	array("label" => "Total Liabilities",  "value" => AccountingReports::fmtMoney($totalLiabilities), "icon" => "fa-credit-card",     "color" => "bg-red"),
	array("label" => "Total Equity",       "value" => AccountingReports::fmtMoney($totalEquity),      "icon" => "fa-balance-scale",   "color" => "bg-green"),
	array("label" => "As of",              "value" => $asOf,                                          "icon" => "fa-calendar",        "color" => "bg-yellow"),
));

// Assets
echo '<div class="card card-info card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-inbox"></i> Assets</h3></div>';
echo '<div class="card-body">';
$columns = array(
	array("key" => "code",   "label" => "Account Code", "align" => "left",  "format" => "text"),
	array("key" => "name",   "label" => "Account Name", "align" => "left",  "format" => "text"),
	array("key" => "amount", "label" => "Balance",      "align" => "right", "format" => "money"),
);
$totals = array("amount" => $totalAssets);
AccountingReports::renderTable($columns, $assetRows, $totals, 'table table-bordered table-striped', 'No asset accounts with balances.');
echo '</div></div>';

// Liabilities
echo '<div class="card card-warning card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-credit-card"></i> Liabilities</h3></div>';
echo '<div class="card-body">';
$totals = array("amount" => $totalLiabilities);
AccountingReports::renderTable($columns, $liabilityRows, $totals, 'table table-bordered table-striped', 'No liability accounts with balances.');
echo '</div></div>';

// Equity
echo '<div class="card card-success card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-balance-scale"></i> Equity</h3></div>';
echo '<div class="card-body">';
$totals = array("amount" => $totalEquity);
AccountingReports::renderTable($columns, $equityRows, $totals, 'table table-bordered table-striped', 'No equity accounts with balances.');
echo '</div></div>';

// Balance Check
$diff = abs($totalAssets - $totalLiabEq);
$balanceOk = $diff < 0.01;
$checkColor = $balanceOk ? '#00a65a' : '#dd4b39';
$checkIcon  = $balanceOk ? 'fa-check-circle' : 'fa-exclamation-triangle';
$checkText  = $balanceOk ? 'Balance Sheet is balanced!' : 'WARNING: Balance Sheet does not balance (difference: ' . AccountingReports::fmtMoney($diff) . ')';

echo '<div class="card card-outline" style="border-color:' . $checkColor . ';">';
echo '<div class="card-body text-center" style="padding:20px;">';
echo '<h2 style="color:' . $checkColor . ';"><i class="fa ' . $checkIcon . '"></i> ' . $checkText . '</h2>';
echo '<p style="margin:5px 0;">Assets: ' . AccountingReports::fmtMoney($totalAssets) . ' = Liabilities: ' . AccountingReports::fmtMoney($totalLiabilities) . ' + Equity: ' . AccountingReports::fmtMoney($totalEquity) . '</p>';
echo '</div></div>';

// SQL Documentation
$balanceSql = "SELECT a.type,
                      a.code, a.name,
                      COALESCE(SUM(jl.debit), 0)  AS debit,
                      COALESCE(SUM(jl.credit), 0) AS credit
                 FROM accounts a
                 LEFT JOIN journal_lines jl ON jl.idAccount = a.id
                      AND jl.idOrganization = :org
                WHERE a.idOrganization = :org
                  AND a.type IN ('asset','liability','equity')
                GROUP BY a.id, a.code, a.name, a.type
                ORDER BY FIELD(a.type,'asset','liability','equity'), a.code ASC";

AccountingReports::renderSqlBlock("SQL: Balance Sheet Accounts", $balanceSql);

AccountingReports::renderExplanation(
	"The Balance Sheet shows the financial position at a point in time. "
	. "Assets (debit-normal: Cash, Receivables, Inventory, etc.) must equal "
	. "Liabilities + Equity (credit-normal: Payables, Capital, Retained Earnings). "
	. "All balances are derived from journal_lines. The accounting equation "
	. "Assets = Liabilities + Equity is verified automatically. Net Income from "
	. "the current period flows into Retained Earnings (equity)."
);

AccountingReports::renderFooter();