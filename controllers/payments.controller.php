<?php

/**
 * Payments received against invoices.
 *
 * Every add / edit / delete:
 *   1. writes the payment record,
 *   2. (re)posts its double-entry journal (Dr Cash, Cr Accounts Receivable),
 *   3. logs the action to the invoice activity trail,
 *   4. asks ControllerInvoices to recalculate the invoice balance + status.
 *
 * The invoice's amountPaid / balanceDue / status are NEVER set here directly —
 * they are always derived in ControllerInvoices::ctrRecalcInvoice.
 */
class ControllerPayments {

	/*=============================================
	SHOW PAYMENTS FOR AN INVOICE
	=============================================*/

	public static function ctrShowInvoicePayments(int $idInvoice): array {
		return ModelPayments::mdlShowInvoicePayments($idInvoice);
	}

	/*=============================================
	SHOW ALL PAYMENTS FOR A CUSTOMER (for statements)
	=============================================*/

	public static function ctrShowCustomerPayments(int $idCustomer): array {
		return ModelPayments::mdlShowCustomerPayments($idCustomer);
	}

	/*=============================================
	RECORD A PAYMENT
	=============================================*/

	public static function ctrAddPayment(): void {

		if (!isset($_POST["recordPaymentInvoice"])) {
			return;
		}

		$idInvoice = (int)$_POST["recordPaymentInvoice"];
		$amount    = (float)($_POST["paymentAmount"] ?? 0);

		$invoice = ModelInvoices::mdlShowInvoices("invoices", "id", $idInvoice);

		if (!is_array($invoice) || $amount <= 0) {
			return;
		}

		$userId = (int)($_SESSION["id"] ?? 0);

		$data = [
			"paymentNumber" => ModelPayments::mdlNextPaymentNumber(),
			"idInvoice"     => $idInvoice,
			"idCustomer"    => (int)$invoice["idCustomer"],
			"amount"        => number_format($amount, 2, '.', ''),
			"paymentDate"   => ($_POST["paymentDate"] ?? "") !== "" ? $_POST["paymentDate"] : date("Y-m-d"),
			"paymentMode"   => $_POST["paymentMode"] ?? "cash",
			"reference"     => $_POST["paymentReference"] ?? "",
			"notes"         => $_POST["paymentNotes"] ?? "",
			"createdBy"     => $userId,
		];

		$paymentId = ModelPayments::mdlAddPayment($data);

		if ($paymentId === "error") {
			return;
		}

		// Accounting: money in. Debit Cash/Bank, credit Accounts Receivable.
		ModelAccounting::mdlPostEntry(
			[
				"entryDate"   => $data["paymentDate"],
				"reference"   => $data["paymentNumber"],
				"sourceType"  => "payment",
				"sourceId"    => (int)$paymentId,
				"description" => "Payment for invoice #" . $invoice["invoiceNumber"],
				"createdBy"   => $userId,
			],
			[
				["code" => "1000", "debit"  => $amount, "credit" => 0],
				["code" => "1100", "debit"  => 0,       "credit" => $amount],
			]
		);

		ModelInvoices::mdlLogActivity(
			$idInvoice,
			$userId,
			"payment_added",
			"Recorded payment of $ " . number_format($amount, 2) . " (" . $data["paymentMode"] . ")"
		);

		ControllerInvoices::ctrRecalcInvoice($idInvoice, $userId);

		self::redirect($idInvoice, "Payment recorded successfully");

	}

	/*=============================================
	EDIT A PAYMENT
	=============================================*/

	public static function ctrEditPayment(): void {

		if (!isset($_POST["editPayment"])) {
			return;
		}

		$paymentId = (int)$_POST["editPayment"];
		$amount    = (float)($_POST["paymentAmount"] ?? 0);

		$payment = ModelPayments::mdlGetPayment($paymentId);

		if (!is_array($payment) || $amount <= 0) {
			return;
		}

		$idInvoice = (int)$payment["idInvoice"];
		$userId    = (int)($_SESSION["id"] ?? 0);

		$data = [
			"id"          => $paymentId,
			"amount"      => number_format($amount, 2, '.', ''),
			"paymentDate" => ($_POST["paymentDate"] ?? "") !== "" ? $_POST["paymentDate"] : $payment["paymentDate"],
			"paymentMode" => $_POST["paymentMode"] ?? $payment["paymentMode"],
			"reference"   => $_POST["paymentReference"] ?? "",
			"notes"       => $_POST["paymentNotes"] ?? "",
		];

		if (ModelPayments::mdlEditPayment($data) !== "ok") {
			return;
		}

		// Re-post the payment journal cleanly with the new amount/date.
		ModelAccounting::mdlDeleteEntriesBySource("payment", $paymentId);
		ModelAccounting::mdlPostEntry(
			[
				"entryDate"   => $data["paymentDate"],
				"reference"   => $payment["paymentNumber"],
				"sourceType"  => "payment",
				"sourceId"    => $paymentId,
				"description" => "Payment (edited) for invoice id " . $idInvoice,
				"createdBy"   => $userId,
			],
			[
				["code" => "1000", "debit"  => $amount, "credit" => 0],
				["code" => "1100", "debit"  => 0,       "credit" => $amount],
			]
		);

		ModelInvoices::mdlLogActivity(
			$idInvoice,
			$userId,
			"payment_edited",
			"Edited payment " . $payment["paymentNumber"] . " to $ " . number_format($amount, 2)
		);

		ControllerInvoices::ctrRecalcInvoice($idInvoice, $userId);

		self::redirect($idInvoice, "Payment updated successfully");

	}

	/*=============================================
	DELETE A PAYMENT
	=============================================*/

	public static function ctrDeletePayment(): void {

		if (!isset($_GET["deletePayment"])) {
			return;
		}

		$paymentId = (int)$_GET["deletePayment"];
		$payment   = ModelPayments::mdlGetPayment($paymentId);

		if (!is_array($payment)) {
			return;
		}

		$idInvoice = (int)$payment["idInvoice"];
		$userId    = (int)($_SESSION["id"] ?? 0);

		if (ModelPayments::mdlDeletePayment($paymentId) !== "ok") {
			return;
		}

		ModelAccounting::mdlDeleteEntriesBySource("payment", $paymentId);

		ModelInvoices::mdlLogActivity(
			$idInvoice,
			$userId,
			"payment_deleted",
			"Deleted payment " . $payment["paymentNumber"] . " of $ " . number_format((float)$payment["amount"], 2)
		);

		ControllerInvoices::ctrRecalcInvoice($idInvoice, $userId);

		self::redirect($idInvoice, "Payment deleted successfully");

	}

	/*=============================================
	REDIRECT HELPER (mirrors the swal pattern used across the app)
	=============================================*/

	private static function redirect(int $idInvoice, string $title): void {

		echo '<script>
		swal({
			  type: "success",
			  title: "' . $title . '",
			  showConfirmButton: true,
			  confirmButtonText: "Close"
			  }).then((result) => {
						if (result.value) { window.location = "index.php?route=invoice-detail&idInvoice=' . $idInvoice . '"; }
					})
		</script>';

	}

}
