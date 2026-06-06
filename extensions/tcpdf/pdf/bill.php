<?php
ob_start();

require_once "../../../controllers/sales.controller.php";
require_once "../../../models/sales.model.php";

require_once "../../../controllers/customers.controller.php";
require_once "../../../models/customers.model.php";

require_once "../../../controllers/users.controller.php";
require_once "../../../models/users.model.php";

require_once "../../../controllers/products.controller.php";
require_once "../../../models/products.model.php";

class printBill{

public $code;

public function getBillPrinting(){

//WE BRING THE INFORMATION OF THE SALE

$itemSale = "code";
$valueSale = $this->code;

$answerSale = ControllerSales::ctrShowSales($itemSale, $valueSale);

$saledate = substr($answerSale["saledate"],0,-8);
$products = json_decode($answerSale["products"], true);
$netPrice = number_format($answerSale["netPrice"],2);
$tax = number_format($answerSale["tax"],2);
$totalPrice = number_format($answerSale["totalPrice"],2);
// Recompute total from raw values to ensure accuracy
$computedTotal = number_format(floatval($answerSale["netPrice"]) + floatval($answerSale["tax"]), 2);

// FETCH CUSTOMER INFORMATION

$itemCustomer = "id";
$valueCustomer = $answerSale["idCustomer"];

$answerCustomer = ControllerCustomers::ctrShowCustomers($itemCustomer, $valueCustomer);

// FETCH SELLER INFORMATION

$itemSeller = "id";
$valueSeller = $answerSale["idSeller"];

$answerSeller = ControllerUsers::ctrShowUsers($itemSeller, $valueSeller);

// REQUIRE TCPDF

require_once('tcpdf_include.php');

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Page size kept compact for receipt-style output
$pdf->AddPage('P', 'A7');

// Styling: tighten layout to keep everything on one page
$pdf->SetAutoPageBreak(true, 2);
$pdf->SetMargins(2, 1, 2);
$pdf->SetFont('helvetica', '', 8);

// Company / header block
$company = 'Smooth POS System';
$address = '86 Bel Meadow Drive';
$contact = '300 786 52 49';


$header = <<<EOF
<table cellpadding="1">
<tr>
	<td width="60%" style="font-size:10px"><strong>$company</strong><br/><span style="font-size:7px">$address<br/>Contact: $contact</span></td>
	<td width="40%" style="font-size:8px;text-align:right"><strong>Invoice: $valueSale</strong><br/>$saledate</td>
</tr>
</table>
<hr/>
EOF;

$pdf->writeHTML($header, true, false, true, false, '');

// Customer / seller info box
$customerName = isset($answerCustomer["name"]) ? $answerCustomer["name"] : '';
$sellerName = isset($answerSeller["name"]) ? $answerSeller["name"] : '';

$info = <<<EOF
<table cellpadding="2" style="border:1px solid #ddd; background-color:#f7f7f7;">
<tr>
	<td style="font-size:8px"><strong>Customer:</strong> $customerName</td>
</tr>
<tr>
	<td style="font-size:8px"><strong>Seller:</strong> $sellerName</td>
</tr>
</table>
EOF;

$pdf->Ln(2);
$pdf->writeHTML($info, true, false, true, false, '');

// Products table header
// Build products table as a single compact table to avoid extra spacing
$productsTable = '<table cellpadding="2" style="font-size:8px;">';
$productsTable .= '<tr style="background-color:#eeeeee">'
	. '<td width="55%"><strong>Description</strong></td>'
	. '<td width="15%" align="right"><strong>Qty</strong></td>'
	. '<td width="15%" align="right"><strong>Unit</strong></td>'
	. '<td width="15%" align="right"><strong>Total</strong></td>'
	. '</tr>';

foreach ($products as $key => $item) {
	$desc = htmlspecialchars($item["description"]);
	$qty = (int)$item["quantity"];
	$unit = number_format($item["price"], 2);
	$lineTotal = number_format($item["totalPrice"], 2);

	$productsTable .= '<tr>'
		. "<td width=\"55%\">$desc</td>"
		. "<td width=\"15%\" align=\"right\">$qty</td>"
		. "<td width=\"15%\" align=\"right\">$unit</td>"
		. "<td width=\"15%\" align=\"right\">$lineTotal</td>"
		. '</tr>';
}

$productsTable .= '</table>';
$pdf->writeHTML($productsTable, true, false, true, false, '');

// Totals block: match font/size to product detail (helvetica, 8pt)
$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 8);
$totals = <<<EOF
<table cellpadding="3" style="font-size:6px;">
<tr>
	<td width="60%"></td>
	<td width="20%" align="right">NET:</td>
	<td width="20%" align="right">$ $netPrice</td>
</tr>
<tr>
	<td></td>
	<td align="right">TAX:</td>
	<td align="right">$ $tax</td>
</tr>
<tr>
	<td></td>
	<td align="right">----------</td>
	<td align="right"></td>
</tr>
<tr>
	<td></td>
	<td align="right">TOTAL:</td>
	<td align="right">$ $computedTotal</td>
</tr>
</table>
EOF;

$pdf->writeHTML($totals, true, false, true, false, '');

$pdf->Ln(4);
$pdf->SetFont('helvetica', '', 8);
$thanks = '<div style="text-align:center; font-size:8px;">Thank you for your purchase!</div>';
$pdf->writeHTML($thanks, true, false, true, false, '');

// OUTPUT PDF FILE
// $pdf->Output('bill.pdf', 'D');

ob_end_clean();
$pdf->Output('bill.pdf');

}

}

$bill = new printBill();
$bill -> code = $_GET["code"];
$bill -> getBillPrinting();

?>