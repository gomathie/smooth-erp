<?php

/**
 * Quotations. CRUD plus "convert to invoice". Quotations themselves never post
 * to the ledger; conversion creates a draft invoice which then follows the
 * normal invoice posting rules.
 */
class ControllerQuotations {

	/*=============================================
	SHOW QUOTATIONS
	=============================================*/

	public static function ctrShowQuotations($item, $value) {
		return ModelQuotations::mdlShowQuotations($item, $value);
	}

	/*=============================================
	CREATE QUOTATION
	=============================================*/

	public static function ctrCreateQuotation(): void {

		if (!isset($_POST["newQuotation"])) {
			return;
		}

		$data = self::collectPost();
		$data["quoteNumber"] = $_POST["newQuotation"];
		$data["createdBy"]   = (int)($_SESSION["id"] ?? 0);

		if (ModelQuotations::mdlAddQuotation($data) === "ok") {
			self::redirect("quotations", "Quotation created successfully");
		}

	}

	/*=============================================
	EDIT QUOTATION
	=============================================*/

	public static function ctrEditQuotation(): void {

		if (!isset($_POST["editQuotation"])) {
			return;
		}

		$id       = (int)$_POST["editQuotation"];
		$existing = ModelQuotations::mdlShowQuotations("id", $id);

		$data = self::collectPost();
		$data["id"]         = $id;
		$data["modifiedBy"] = (int)($_SESSION["id"] ?? 0);

		// Preserve items if the form posted an empty list (no edits made to lines)
		if (($_POST["productsList"] ?? "") === "") {
			$data["items"] = $existing["items"] ?? "[]";
		}

		if (ModelQuotations::mdlEditQuotation($data) === "ok") {
			self::redirect("quotations", "Quotation updated successfully");
		}

	}

	/*=============================================
	DELETE QUOTATION
	=============================================*/

	public static function ctrDeleteQuotation(): void {

		if (!isset($_GET["idQuotation"])) {
			return;
		}

		if (ModelQuotations::mdlDeleteQuotation((int)$_GET["idQuotation"]) === "ok") {
			self::redirect("quotations", "Quotation deleted successfully");
		}

	}

	/*=============================================
	CONVERT QUOTATION -> DRAFT INVOICE
	=============================================*/

	public static function ctrConvertToInvoice(): void {

		if (!isset($_GET["convertQuote"])) {
			return;
		}

		$id    = (int)$_GET["convertQuote"];
		$quote = ModelQuotations::mdlShowQuotations("id", $id);

		if (!is_array($quote) || $quote["status"] === "invoiced") {
			return;
		}

		$userId = (int)($_SESSION["id"] ?? 0);

		Connection::begin();

		try {

			$invoiceNumber = self::nextInvoiceNumber();

			$invoiceData = [
				"invoiceNumber"  => $invoiceNumber,
				"orderReference" => $quote["orderReference"] ?? "",
				"idCustomer"     => $quote["idCustomer"],
				"idSeller"       => $quote["idSeller"],
				"items"          => $quote["items"],
				"subtotal"       => $quote["subtotal"],
				"discount"       => $quote["discount"],
				"discountType"   => $quote["discountType"] ?? "amount",
				"discountValue"  => $quote["discountValue"] ?? "0",
				"shipping"       => $quote["shipping"],
				"adjustments"    => $quote["adjustments"],
				"tax"            => $quote["tax"],
				"netPrice"       => $quote["netPrice"],
				"totalPrice"     => $quote["totalPrice"],
				"dueDate"        => null,
				"paymentTerms"   => "due_on_receipt",
				"status"         => "draft",
				"notes"          => $quote["notes"] ?? "",
				"termsConditions"=> $quote["termsConditions"] ?? "",
			];

			if (ModelInvoices::mdlAddInvoice("invoices", $invoiceData) !== "ok") {
				Connection::rollBack();
				return;
			}

			$newInvoice = ModelInvoices::mdlShowInvoices("invoices", "invoiceNumber", $invoiceNumber);
			$newId      = (int)$newInvoice["id"];

			ModelInvoices::mdlLogActivity($newId, $userId, "created", "Created from quotation #" . $quote["quoteNumber"]);
			ControllerInvoices::ctrRecalcInvoice($newId, $userId, "draft");
			ModelQuotations::mdlMarkConverted($id, $newId);

			Connection::commit();

		} catch (Exception $e) {
			Connection::rollBack();
			return;
		}

		echo '<script>
		swal({
			  type: "success",
			  title: "Quotation converted to invoice",
			  showConfirmButton: true,
			  confirmButtonText: "Open invoice"
			  }).then((result) => {
						if (result.value) { window.location = "index.php?route=invoice-detail&idInvoice=' . $newId . '"; }
					})
		</script>';

	}

	/*=============================================
	HELPERS
	=============================================*/

	private static function collectPost(): array {

		$expiry = ($_POST["expiryDate"] ?? "") !== "" ? $_POST["expiryDate"] : null;

		return [
			"orderReference" => $_POST["orderReference"] ?? "",
			"idCustomer"     => $_POST["selectCustomer"],
			"idSeller"       => $_POST["idSeller"],
			"items"          => $_POST["productsList"],
			"subtotal"       => $_POST["subtotal"]      ?? "0",
			"discount"       => $_POST["discount"]      ?? "0",
			"discountType"   => (($_POST["discountType"] ?? "amount") === "percent") ? "percent" : "amount",
			"discountValue"  => $_POST["discountValue"] ?? "0",
			"shipping"       => $_POST["shipping"]      ?? "0",
			"adjustments"    => $_POST["adjustments"]   ?? "0",
			"tax"            => $_POST["newTaxPrice"]   ?? "0",
			"netPrice"       => $_POST["newNetPrice"]   ?? "0",
			"totalPrice"     => $_POST["saleTotal"]     ?? "0",
			"expiryDate"     => $expiry,
			"status"         => in_array($_POST["quoteStatus"] ?? "draft", ["draft", "sent", "accepted", "declined"], true) ? $_POST["quoteStatus"] : "draft",
			"notes"          => $_POST["notes"] ?? "",
			"termsConditions"=> $_POST["termsConditions"] ?? "",
		];

	}

	private static function nextInvoiceNumber(): string {

		$stmt = Connection::connect()->prepare("SELECT COALESCE(MAX(CAST(invoiceNumber AS UNSIGNED)), 10000) + 1 AS n FROM invoices");
		$stmt->execute();
		$row = $stmt->fetch();

		return (string)(int)($row["n"] ?? 10001);

	}

	private static function redirect(string $route, string $title): void {

		echo '<script>
		swal({
			  type: "success",
			  title: "' . $title . '",
			  showConfirmButton: true,
			  confirmButtonText: "Close"
			  }).then((result) => {
						if (result.value) { window.location = "' . $route . '"; }
					})
		</script>';

	}

}
