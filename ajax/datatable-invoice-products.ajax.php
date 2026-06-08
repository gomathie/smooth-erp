<?php

require_once "../controllers/products.controller.php";
require_once "../models/products.model.php";

class invoiceProductsTable {

/*=============================================
  SHOW PRODUCTS TABLE FOR INVOICE
 =============================================*/
public function showInvoiceProductsTable() {

header('Content-Type: application/json; charset=utf-8');

$item = null;
$value = null;
$order = "id";

$products = ControllerProducts::ctrShowProducts($item, $value, $order);

$data = [];

for ($i = 0; $i < count($products); $i++) {

/*=============================================
Product image
=============================================*/
$image = "<img src='" . $products[$i]["image"] . "' width='40px'>";

/*=============================================
Stock badge (services aren't stock-tracked)
=============================================*/
if (($products[$i]["type"] ?? "good") === "service") {
	$stock = "<button class='btn btn-default'><i class='fa fa-wrench'></i> Service</button>";
} else if ($products[$i]["stock"] <= 10) {
	$stock = "<button class='btn btn-danger'>" . $products[$i]["stock"] . "</button>";
} else if ($products[$i]["stock"] > 11 && $products[$i]["stock"] <= 15) {
	$stock = "<button class='btn btn-warning'>" . $products[$i]["stock"] . "</button>";
} else {
	$stock = "<button class='btn btn-success'>" . $products[$i]["stock"] . "</button>";
}

/*=============================================
Add button — uses invoiceProduct-specific classes
=============================================*/
$buttons = "<div class='btn-group'><button class='btn btn-primary addProductInvoice recoverButtonInvoice' idProduct='" . $products[$i]["id"] . "'><i class='fa fa-plus'></i></button></div>";

$data[] = [
	($i + 1),
	$image,
	$products[$i]["code"],
	$products[$i]["description"],
	$stock,
	$buttons
];
}

echo json_encode(["data" => $data]);
}
}

/*=============================================
ACTIVATE TABLE
=============================================*/
$activateInvoiceProducts = new invoiceProductsTable();
$activateInvoiceProducts->showInvoiceProductsTable();
