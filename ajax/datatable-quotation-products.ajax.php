<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }


require_once "../controllers/products.controller.php";
require_once "../models/products.model.php";

class quotationProductsTable {

/*=============================================
  SHOW PRODUCTS TABLE FOR QUOTATION
 =============================================*/
public function showQuotationProductsTable() {

header('Content-Type: application/json; charset=utf-8');

$products = ControllerProducts::ctrShowProducts(null, null, "id");

$data = [];

for ($i = 0; $i < count($products); $i++) {

$image = "<img src='" . $products[$i]["image"] . "' width='40px'>";

if (($products[$i]["type"] ?? "good") === "service") {
	$stock = "<button class='btn btn-default'><i class='fa fa-wrench'></i> Service</button>";
} else if ($products[$i]["stock"] <= 10) {
	$stock = "<button class='btn btn-danger'>" . $products[$i]["stock"] . "</button>";
} else if ($products[$i]["stock"] > 11 && $products[$i]["stock"] <= 15) {
	$stock = "<button class='btn btn-warning'>" . $products[$i]["stock"] . "</button>";
} else {
	$stock = "<button class='btn btn-success'>" . $products[$i]["stock"] . "</button>";
}

$buttons = "<div class='btn-group'><button class='btn btn-primary addProductQuote recoverButtonQuote' idProduct='" . $products[$i]["id"] . "'><i class='fa fa-plus'></i></button></div>";

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

$activate = new quotationProductsTable();
$activate->showQuotationProductsTable();
