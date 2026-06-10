<?php
/**
 * Trial Balance
 *
 * Lists all accounts with their total debits and credits.
 * Must balance: total debits = total credits.
 * All figures derived from journal_lines (no hardcoded values).
 *
 * SQL: COALESCE(SUM(debit)), COALESCE(SUM(credit)) per account from journal_lines.
 */

require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$from = $_GET["from"] ?? date("Y-01-01");
$to   = $_GET["to"]   ?? date("Y-m-d");

$balances = AccountingReports::getAccountBalances();

$accountTypeLabels = array(
	"asset" => "Asset", "liability" => "Liability", "equity" => "Equity",
	"income" => "Income", "expense" => "Expense",
);

// Group by type
$grouped = array(
	"asset"     => array("rows" => array(), "debit" => 0.0, "credit" => 0.0),
	"liability" => array("rows" => array(), "debit" => 0.0, "credit" => 0.0),
	"equity"    => array("rows" => array(), "debit" => 0.0, "credit" => 0.0),
	"income"    => array("rows" => array(), "debit" => 0.0, "credit" => 0.0),
	"expense"   => array("rows" => array(), "debit" => 0.0, "credit" => 0.0),
);

$grandDebit  = 0.0;
$grandCredit = 0.0;

foreach ($balances as $acc) {
	$type   = $acc["type"];
	$debit  = (float)$acc["debit"];
	$credit = (float)$acc["credit"];

	if (!isset($grouped[$type])) { continue; }
	if ($debit == 0 && $credit == 0) { continue; }

	$grouped[$type]["rows"][] = array(
		"code"    => htmlspecialchars($acc["code"]),
		"name"    => htmlspecialchars($acc["name"]),
		"debit"   => $debit,
		"credit"  => $credit,
		"balance" => AccountingReports::netBalance($acc),
	);
	$grouped[$type]["debit"]  += $debit;
	$grouped[$type]["credit"] += $credit;
	$grandDebit  += $debit;
	$grandCredit += $credit;
}

AccountingReports::renderHeader("Trial Balance", "Trial Balance", "accounting-trial-balance", true, $from, $to);
AccountingReports::renderPrintButton();

$balanceDiff = abs($grandDebit - $grandCredit);
$isOk = $balanceDiff < 0.01;

AccountingReports::renderKpiCards(array(
	array("label" => "Total Debits",  "value" => AccountingReports::fmtMoney($grandDebit),  "icon" => "fa-arrow-down", "color" => "bg-green"),
	array("label" => "Total Credits", "value" => AccountingReports::fmtMoney($grandCredit), "icon" => "fa-arrow-up",   "color" => "bg-red"),
	array("label" => "Difference",    "value" => AccountingReports::fmtMoney($balanceDiff),  "icon" => "fa-exchange",    "color" => ($isOk ? "bg-green" : "bg-red")),
	array("label" => "Status",        "value" => $isOk ? "BALANCED" : "UNBALANCED",         "icon" => ($isOk ? "fa-check" : "fa-warning"), "color" => ($isOk ? "bg-green" : "bg-red")),
));

$columns = array(
	array("key" => "code",    "label" => "Code",   "align" => "left",  "format" => "text"),
	array("key" => "name",    "label" => "Name",   "align" => "left",  "format" => "text"),
	array("key" => "debit",   "label" => "Debit",  "align" => "right", "format" => "money"),
	array("key" => "credit",  "label" => "Credit", "align" => "right", "format" => "money"),
);

// Render each section
$sectionOrder = array("asset", "liability", "equity", "income", "expense");
foreach ($sectionOrder as $type) {
	$section = $grouped[$type];
	if (empty($section["rows"])) { continue; }

	$colors = array(
		"asset" => "card-info", "liability" => "card-warning", "equity" => "card-success",
		"income" => "card-success", "expense" => "card-danger",
	);
	$icons = array(
		"asset" => "fa-inbox", "liability" => "fa-credit-card", "equity" => "fa-balance-scale",
		"income" => "fa-line-chart", "expense" => "fa-credit-card",
	);

	echo '<div class="card ' . ($colors[$type] ?? 'card-primary') . ' card-outline">';
	echo '<div class="card-header"><h3 class="card-title"><i class="fa ' . ($icons[$type] ?? 'fa-book') . '"></i> ' . $accountTypeLabels[$type] . ' Accounts</h3></div>';
	echo '<div class="card-body">';
	$totals = array("debit" => $section["debit"], "credit" => $section["credit"]);
	AccountingReports::renderTable($columns, $section["rows"], $totals);
	echo '</div></div>';
}

// Grand Total
echo '<div class="card card-primary card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-calculator"></i> Grand Total</h3></div>';
echo '<div class="card-body">';
echo '<table class="table table-bordered" style="margin-bottom:0;">';
echo '<thead><tr style="background:#f5f5f5;"><th>Debit Total</th><th class="text-right">Credit Total</th><th class="text-right">Difference</th></tr></thead>';
echo '<tbody><tr style="font-weight:bold;">';
echo '<td>' . AccountingReports::fmtMoney($grandDebit) . '</td>';
echo '<td class="text-right">' . AccountingReports::fmtMoney($grandCredit) . '</td>';
echo '<td class="text-right" style="color:' . ($isOk ? '#00a65a' : '#dd4b39') . ';">' . AccountingReports::fmtMoney($balanceDiff) . '</td>';
echo '</tr></tbody></table>';
echo '</div></div>';

// SQL
$sql = "SELECT a.code, a.name, a.type,
               COALESCE(SUM(jl.debit), 0)  AS debit,
               COALESCE(SUM(jl.credit), 0) AS credit
          FROM accounts a
          LEFT JOIN journal_lines jl ON jl.idAccount = a.id
               AND jl.idOrganization = :org
         WHERE a.idOrganization = :org
         GROUP BY a.id, a.code, a.name, a.type
         HAVING debit > 0 OR credit > 0
         ORDER BY a.code ASC";

AccountingReports::renderSqlBlock("SQL: Trial Balance Query", $sql);

AccountingReports::renderExplanation(
	"The Trial Balance lists every account and its total debits and credits "
	. "from the general ledger. In a valid double-entry system, total debits "
	. "must equal total credits (difference should be $0.00). The query uses "
	. "COALESCE(SUM(debit),0) and COALESCE(SUM(credit),0) with a LEFT JOIN "
	. "from accounts to journal_lines. Accounts with zero activity are excluded "
	. "via HAVING clause. All data derives from journal_lines - no hardcoded values."
);

AccountingReports::renderFooter();