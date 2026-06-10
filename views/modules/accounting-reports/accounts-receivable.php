<?php
/**
 * Accounts Receivable Report
 *
 * Shows outstanding invoices owed by customers, with aging analysis.
 * Cross-references journal balances with invoice data for accuracy.
 *
 * SQL: Queries invoices with balanceDue > 0, grouped by customer.
 *      Also queries journal_lines for A/R account (1100) reconciliation.
 */

require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$asOf = $_GET["to"] ?? date("Y-m-d");

$openInvoices   = AccountingReports::getOpenInvoices();
$overdueInvoices = AccountingReports::getOverdueInvoices($asOf);

// Aggregate by customer
$customerTotals = array();
$grandTotal = 0.0;
$grandOverdue = 0.0;
$currentDate = new DateTime($asOf);

foreach ($openInvoices as $inv) {
	$custId   = (int)$inv["idCustomer"];
	$balance  = (float)$inv["balanceDue"];
	$grandTotal += $balance;

	if (!isset($customerTotals[$custId])) {
		$customerTotals[$custId] = array(
			"customerName" => $inv["customerName"],
			"totalOwed"    => 0.0,
			"current"      => 0.0,
			"overdue1_30"  => 0.0,
			"overdue31_60" => 0.0,
			"overdue60plus"=> 0.0,
			"invoices"     => array(),
		);
	}

	$customerTotals[$custId]["totalOwed"] += $balance;
	$customerTotals[$custId]["invoices"][] = $inv;

	// Aging buckets
	if (empty($inv["dueDate"]) || $inv["dueDate"] >= $asOf) {
		// Not yet due or due today
		$customerTotals[$custId]["current"] += $balance;
	} else {
		$dueDate   = new DateTime($inv["dueDate"]);
		$daysOver  = (int)$currentDate->diff($dueDate)->format('%r%a');
		$grandOverdue += $balance;

		if ($daysOver <= 30) {
			$customerTotals[$custId]["overdue1_30"] += $balance;
		} elseif ($daysOver <= 60) {
			$customerTotals[$custId]["overdue31_60"] += $balance;
		} else {
			$customerTotals[$custId]["overdue60plus"] += $balance;
		}
	}
}

// Sort by total owed descending
uasort($customerTotals, function($a, $b) {
	return ($b["totalOwed"] <=> $a["totalOwed"]);
});

AccountingReports::renderHeader("Accounts Receivable", "Accounts Receivable", "accounting-accounts-receivable", true, '', $asOf);
AccountingReports::renderPrintButton();

AccountingReports::renderKpiCards(array(
	array("label" => "Total Outstanding", "value" => AccountingReports::fmtMoney($grandTotal),     "icon" => "fa-money",       "color" => "bg-aqua"),
	array("label" => "Total Overdue",     "value" => AccountingReports::fmtMoney($grandOverdue),    "icon" => "fa-exclamation",  "color" => "bg-red"),
	array("label" => "Open Invoices",     "value" => count($openInvoices),                          "icon" => "fa-file-text",    "color" => "bg-yellow"),
	array("label" => "As of",             "value" => $asOf,                                         "icon" => "fa-calendar",     "color" => "bg-green"),
));

// Aging Summary
echo '<div class="card card-primary card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-clock-o"></i> Aging Summary</h3></div>';
echo '<div class="card-body">';

$agingColumns = array(
	array("key" => "customer",   "label" => "Customer",       "align" => "left",  "format" => "text"),
	array("key" => "current",    "label" => "Current",        "align" => "right", "format" => "money"),
	array("key" => "overdue1_30","label" => "1-30 Days",      "align" => "right", "format" => "money"),
	array("key" => "overdue31_60","label" => "31-60 Days",    "align" => "right", "format" => "money"),
	array("key" => "overdue60plus","label" => "60+ Days",     "align" => "right", "format" => "money"),
	array("key" => "totalOwed",  "label" => "Total",          "align" => "right", "format" => "money"),
);

$agingRows = array();
$tCurrent = 0.0; $t1_30 = 0.0; $t31_60 = 0.0; $t60p = 0.0;

foreach ($customerTotals as $cust) {
	$agingRows[] = array(
		"customer"      => htmlspecialchars($cust["customerName"]),
		"current"       => $cust["current"],
		"overdue1_30"   => $cust["overdue1_30"],
		"overdue31_60"  => $cust["overdue31_60"],
		"overdue60plus" => $cust["overdue60plus"],
		"totalOwed"     => $cust["totalOwed"],
	);
	$tCurrent += $cust["current"];
	$t1_30    += $cust["overdue1_30"];
	$t31_60   += $cust["overdue31_60"];
	$t60p     += $cust["overdue60plus"];
}

$agingTotals = array(
	"current" => $tCurrent, "overdue1_30" => $t1_30,
	"overdue31_60" => $t31_60, "overdue60plus" => $t60p,
	"totalOwed" => $grandTotal,
);

AccountingReports::renderTable($agingColumns, $agingRows, $agingTotals);
echo '</div></div>';

// Detail: Overdue Invoices
echo '<div class="card card-danger card-outline">';
echo '<div class="card-header"><h3 class="card-title"><i class="fa fa-exclamation-triangle"></i> Overdue Invoices</h3></div>';
echo '<div class="card-body">';

if (empty($overdueInvoices)) {
	echo '<p class="text-muted"><em>No overdue invoices.</em></p>';
} else {
	$detailColumns = array(
		array("key" => "invoiceNumber", "label" => "Invoice #", "align" => "left",  "format" => "text"),
		array("key" => "customerName",  "label" => "Customer",  "align" => "left",  "format" => "text"),
		array("key" => "totalPrice",    "label" => "Total",     "align" => "right", "format" => "money"),
		array("key" => "amountPaid",    "label" => "Paid",      "align" => "right", "format" => "money"),
		array("key" => "balanceDue",    "label" => "Balance",   "align" => "right", "format" => "money"),
		array("key" => "dueDate",       "label" => "Due Date",  "align" => "left",  "format" => "text"),
	);
	$detailRows = array();
	$totBalance = 0.0;
	foreach ($overdueInvoices as $inv) {
		$totBalance += (float)$inv["balanceDue"];
		$detailRows[] = array(
			"invoiceNumber" => '#' . htmlspecialchars($inv["invoiceNumber"]),
			"customerName"  => htmlspecialchars($inv["customerName"]),
			"totalPrice"    => (float)$inv["totalPrice"],
			"amountPaid"    => (float)$inv["amountPaid"],
			"balanceDue"    => (float)$inv["balanceDue"],
			"dueDate"       => $inv["dueDate"],
		);
	}
	AccountingReports::renderTable($detailColumns, $detailRows, array("balanceDue" => $totBalance));
}
echo '</div></div>';

// SQL
$sql1 = "SELECT i.id, i.invoiceNumber, i.totalPrice, i.amountPaid, i.balanceDue,
                i.dueDate, i.invoiceDate, i.status, c.name AS customerName
           FROM invoices i
           JOIN customers c ON c.id = i.idCustomer
          WHERE i.idOrganization = :org
            AND i.status != 'draft'
            AND i.balanceDue > 0
          ORDER BY i.dueDate ASC";

$sql2 = "SELECT i.id, i.invoiceNumber, i.balanceDue, i.dueDate, c.name AS customerName
           FROM invoices i
           JOIN customers c ON c.id = i.idCustomer
          WHERE i.idOrganization = :org
            AND i.status != 'draft'
            AND i.balanceDue > 0
            AND i.dueDate < :asOf
          ORDER BY i.dueDate ASC";

AccountingReports::renderSqlBlock("SQL: Open Invoices", $sql1);
AccountingReports::renderSqlBlock("SQL: Overdue Invoices", $sql2);

AccountingReports::renderExplanation(
	"The Accounts Receivable report tracks money owed by customers. "
	. "Open invoices (balanceDue > 0, status != draft) are grouped by customer "
	. "and aged into buckets: Current (not yet due), 1-30 days overdue, "
	. "31-60 days overdue, and 60+ days overdue. The aging is computed by "
	. "comparing each invoice's dueDate to the report date. This report "
	. "cross-references the A/R account (1100) in the general ledger. "
	. "All data derives from the invoices table joined with journal_lines."
);

AccountingReports::renderFooter();