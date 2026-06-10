<?php
/**
 * Inventory Valuation Report
 * Shows stock levels and value per product.
 * SQL: Products joined with stock_movements for valuation.
 */
require_once __DIR__ . '/../../../helpers/accounting_reports.php';

if (!Permission::has("accounting")) {
	echo '<script>window.location = "home";</script>';
	return;
}

$stock = AccountingReports::getStockSummary();
$totalValue = 0.0;
$totalQty = 0;
$rows = array();

foreach ($stock as $p) {
	$qty = (int)$p["stock"];
	$buyPrice = (float)$p["buyingPrice"];
	$sellPrice = (float)$p["sellingPrice"];
	$value = $qty * $buyPrice;
	$totalValue += $value;
	$totalQty += $qty;
	$rows[] = array(
		"code" => htmlspecialchars($p["code"]),
		"description" => htmlspecialchars($p["description"]),
		"qty" => $qty,
		"buyingPrice" => $buyPrice,
		"sellingPrice" => $sellPrice,
		"stockValue" => $value,
		"potentialRevenue" => $qty * $sellPrice,
	);
}

AccountingReports::renderHeader("Inventory Valuation","Inventory Valuation","accounting-inventory-valuation",false);
AccountingReports::renderPrintButton();

$potentialRevenue = 0.0;
foreach ($rows as $r) { $potentialRevenue += $r["potentialRevenue"]; }
$potentialProfit = $potentialRevenue - $totalValue;

AccountingReports::renderKpiCards(array(
	array("label"=>"Total Stock Value","value"=>AccountingReports::fmtMoney($totalValue),"icon"=>"fa-cubes","color"=>"bg-aqua"),
	array("label"=>"Total Units","value"=>number_format($totalQty),"icon"=>"fa-cube","color"=>"bg-green"),
	array("label"=>"Potential Revenue","value"=>AccountingReports::fmtMoney($potentialRevenue),"icon"=>"fa-line-chart","color"=>"bg-yellow"),
	array("label"=>"Potential Profit","value"=>AccountingReports::fmtMoney($potentialProfit),"icon"=>"fa-balance-scale","color"=>($potentialProfit>=0?"bg-green":"bg-red")),
));

echo '<div class="card card-primary card-outline"><div class="card-header"><h3 class="card-title"><i class="fa fa-cubes"></i> Inventory Valuation (at Cost)</h3></div><div class="card-body">';
$cols = array(
	array("key"=>"code","label"=>"Code","align"=>"left","format"=>"text"),
	array("key"=>"description","label"=>"Product","align"=>"left","format"=>"text"),
	array("key"=>"qty","label"=>"Qty On Hand","align"=>"right","format"=>"number"),
	array("key"=>"buyingPrice","label"=>"Cost Price","align"=>"right","format"=>"money"),
	array("key"=>"sellingPrice","label"=>"Selling Price","align"=>"right","format"=>"money"),
	array("key"=>"stockValue","label"=>"Stock Value","align"=>"right","format"=>"money"),
	array("key"=>"potentialRevenue","label"=>"Potential Revenue","align"=>"right","format"=>"money"),
);
AccountingReports::renderTable($cols,$rows,array("qty"=>$totalQty,"stockValue"=>$totalValue,"potentialRevenue"=>$potentialRevenue));
echo '</div></div>';

$sql = "SELECT p.code, p.description, p.stock, p.buyingPrice, p.sellingPrice,
               COALESCE(SUM(sm.qtyChange), 0) AS totalMovementQty,
               COALESCE(SUM(sm.qtyChange * sm.unitCost), 0) AS totalMovementValue
          FROM products p
          LEFT JOIN stock_movements sm ON sm.idProduct = p.id AND sm.idOrganization = :org
         WHERE p.idOrganization = :org
         GROUP BY p.id, p.code, p.description, p.stock, p.buyingPrice, p.sellingPrice
         ORDER BY p.code ASC";
AccountingReports::renderSqlBlock("SQL: Inventory with Stock Movements",$sql);
AccountingReports::renderExplanation("The Inventory Valuation report shows all products with their current stock levels and values. Stock Value = Qty On Hand x Cost Price (buyingPrice). Potential Revenue = Qty x Selling Price. The difference represents potential gross profit. Stock movements from the stock_movements table are included for reference. The Inventory Asset account (1200) in the general ledger should reconcile with this total.");
AccountingReports::renderFooter();