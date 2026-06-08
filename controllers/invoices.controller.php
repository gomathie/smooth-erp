<?php

class ControllerInvoices {

	/*=============================================
	SHOW INVOICES
	=============================================*/

	/**
	 * @param string|null $item
	 * @param mixed       $value
	 * @return array|false
	 */
	public static function ctrShowInvoices($item, $value) {

		$table = "invoices";

		return ModelInvoices::mdlShowInvoices($table, $item, $value);

	}

	/*=============================================
	SHOW INVOICE ACTIVITY LOG
	=============================================*/

	public static function ctrShowActivity(int $idInvoice): array {

		return ModelInvoices::mdlShowActivity($idInvoice);

	}

	/*=============================================
	INVOICES FOR A CUSTOMER (for statements)
	=============================================*/

	public static function ctrInvoicesByCustomer(int $idCustomer): array {

		return ModelInvoices::mdlInvoicesByCustomer($idCustomer);

	}

	/*=============================================
	CREATE INVOICE
	=============================================*/

	public static function ctrCreateInvoice(): void {

		if (isset($_POST["newInvoice"])) {

			$table   = "invoices";
			$dueDate = ($_POST["dueDate"] ?? "") !== "" ? $_POST["dueDate"] : null;
			$notes   = $_POST["notes"] ?? "";

			$data = [
				"invoiceNumber"  => $_POST["newInvoice"],
				"orderReference" => $_POST["orderReference"] ?? "",
				"idCustomer"     => $_POST["selectCustomer"],
				"idSeller"       => $_POST["idSeller"],
				"items"          => $_POST["productsList"],
				"subtotal"       => $_POST["subtotal"]     ?? "0",
				"discount"       => $_POST["discount"]     ?? "0",
				"discountType"   => (($_POST["discountType"] ?? "amount") === "percent") ? "percent" : "amount",
				"discountValue"  => $_POST["discountValue"] ?? "0",
				"shipping"       => $_POST["shipping"]     ?? "0",
				"adjustments"    => $_POST["adjustments"]  ?? "0",
				"tax"            => $_POST["newTaxPrice"],
				"netPrice"       => $_POST["newNetPrice"],
				"totalPrice"     => $_POST["saleTotal"],
				"dueDate"        => $dueDate,
				"paymentTerms"   => $_POST["paymentTerms"] ?? "due_on_receipt",
				"status"         => $_POST["invoiceStatus"],
				"notes"          => $notes,
				"termsConditions"=> $_POST["termsConditions"] ?? "",
			];

			$answer = ModelInvoices::mdlAddInvoice($table, $data);

			if ($answer == "ok") {

				$userId  = (int)($_SESSION["id"] ?? 0);
				$created = ModelInvoices::mdlShowInvoices($table, "invoiceNumber", $data["invoiceNumber"]);

				if (is_array($created)) {

					$newId = (int)$created["id"];

					ModelInvoices::mdlLogActivity($newId, $userId, "created", "Invoice created with total $ " . number_format((float)$data["totalPrice"], 2));

					// Derive balance/status and post the receivable journal.
					self::ctrRecalcInvoice($newId, $userId, $data["status"]);

				}

				echo '<script>
				swal({
					  type: "success",
					  title: "Invoice created successfully",
					  showConfirmButton: true,
					  confirmButtonText: "Close"
					  }).then((result) => {
								if (result.value) { window.location = "invoices"; }
							})
				</script>';

			}

		}

	}

	/*=============================================
	EDIT INVOICE
	=============================================*/

	public static function ctrEditInvoice(): void {

		if (isset($_POST["editInvoice"])) {

			$table     = "invoices";
			$itemsList = ($_POST["productsList"] !== "")
				? $_POST["productsList"]
				: (ModelInvoices::mdlShowInvoices($table, "id", $_POST["editInvoice"])["items"] ?? "[]");

			$dueDate = ($_POST["dueDate"] ?? "") !== "" ? $_POST["dueDate"] : null;
			$notes   = $_POST["notes"] ?? "";

			$data = [
				"id"             => $_POST["editInvoice"],
				"idCustomer"     => $_POST["selectCustomer"],
				"idSeller"       => $_POST["idSeller"],
				"orderReference" => $_POST["orderReference"] ?? "",
				"items"          => $itemsList,
				"subtotal"       => $_POST["subtotal"]     ?? "0",
				"discount"       => $_POST["discount"]     ?? "0",
				"discountType"   => (($_POST["discountType"] ?? "amount") === "percent") ? "percent" : "amount",
				"discountValue"  => $_POST["discountValue"] ?? "0",
				"shipping"       => $_POST["shipping"]     ?? "0",
				"adjustments"    => $_POST["adjustments"]  ?? "0",
				"tax"            => $_POST["newTaxPrice"],
				"netPrice"       => $_POST["newNetPrice"],
				"totalPrice"     => $_POST["saleTotal"],
				"dueDate"        => $dueDate,
				"paymentTerms"   => $_POST["paymentTerms"] ?? "due_on_receipt",
				"status"         => $_POST["invoiceStatus"],
				"notes"          => $notes,
				"termsConditions"=> $_POST["termsConditions"] ?? "",
			];

			$answer = ModelInvoices::mdlEditInvoice($table, $data);

			if ($answer == "ok") {

				$userId = (int)($_SESSION["id"] ?? 0);

				ModelInvoices::mdlLogActivity((int)$data["id"], $userId, "edited", "Invoice details updated");

				// Totals may have changed — recalc balance/status and re-post the receivable journal.
				self::ctrRecalcInvoice((int)$data["id"], $userId, $data["status"]);

				echo '<script>
				swal({
					  type: "success",
					  title: "Invoice updated successfully",
					  showConfirmButton: true,
					  confirmButtonText: "Close"
					  }).then((result) => {
								if (result.value) { window.location = "invoices"; }
							})
				</script>';

			}

		}

	}

	/*=============================================
	DELETE INVOICE
	=============================================*/

	public static function ctrDeleteInvoice(): void {

		if (isset($_GET["idInvoice"])) {

			$table     = "invoices";
			$idInvoice = (int)$_GET["idInvoice"];

			// Cascade as one unit of work: payments, accounting entries, COGS,
			// stock movements (which restores inventory) and the activity trail,
			// then the header itself.
			Connection::begin();

			try {

				$payments = ModelPayments::mdlShowInvoicePayments($idInvoice);
				foreach ($payments as $payment) {
					ModelAccounting::mdlDeleteEntriesBySource("payment", (int)$payment["id"]);
				}
				ModelPayments::mdlDeleteInvoicePayments($idInvoice);
				ModelAccounting::mdlDeleteEntriesBySource("invoice", $idInvoice);
				ModelAccounting::mdlDeleteEntriesBySource("invoice_cogs", $idInvoice);
				ModelInventory::mdlDeleteMovementsBySource("invoice", $idInvoice);
				ModelInvoices::mdlDeleteActivity($idInvoice);

				$answer = ModelInvoices::mdlDeleteInvoice($table, $idInvoice);

				Connection::commit();

			} catch (Exception $e) {

				Connection::rollBack();
				$answer = "error";

			}

			if ($answer == "ok") {

				echo '<script>
				swal({
					  type: "success",
					  title: "Invoice deleted successfully",
					  showConfirmButton: true,
					  confirmButtonText: "Close",
					  closeOnConfirm: false
					  }).then((result) => {
								if (result.value) { window.location = "invoices"; }
							})
				</script>';

			}

		}

	}

	/*=============================================
	RECALCULATE BALANCE + STATUS  (single source of truth)
	=============================================
	balanceDue = totalPrice - SUM(payments). Status is derived, never typed:
	  - no payments        -> the manual base status (draft / sent)
	  - 0 < paid < total   -> partially_paid
	  - paid >= total      -> paid
	Then the invoice's accounting journal is re-synced.
	"overdue" is intentionally NOT stored here — it depends on today's date and
	is derived at read time from dueDate + balanceDue.
	*/

	/**
	 * @param int         $idInvoice
	 * @param int|null    $userId
	 * @param string|null $manualStatus  draft|sent from the create/edit form (optional)
	 * @return void
	 */
	public static function ctrRecalcInvoice(int $idInvoice, ?int $userId = null, ?string $manualStatus = null): void {

		$invoice = ModelInvoices::mdlShowInvoices("invoices", "id", $idInvoice);

		if (!is_array($invoice)) {
			return;
		}

		// Everything below commits as one unit of work: the cached balance,
		// the receivable journal, the stock movements and the COGS journal
		// either all land together or none do.
		Connection::begin();

		try {

			$total = (float)$invoice["totalPrice"];
			$paid  = ModelPayments::mdlSumInvoicePayments($idInvoice);
			$balance = round($total - $paid, 2);

			// Base status: only draft/sent are user-selectable. Fall back to the
			// invoice's current base when no manual status was supplied.
			$current = $invoice["status"];
			$base    = $manualStatus ?? $current;
			if (!in_array($base, ["draft", "sent"], true)) {
				$base = "sent";
			}

			if ($paid <= 0) {
				$status = $base;
			} elseif ($balance > 0) {
				$status = "partially_paid";
			} else {
				$status = "paid";
			}

			ModelInvoices::mdlUpdateBalance($idInvoice, $paid, $balance, $status, $userId);

			self::ctrSyncInvoiceJournal($invoice, $status, $userId);

			// Business rule: goods leave inventory (and COGS is recognised) only
			// once money has been received against the invoice.
			$released = ($paid > 0 && $status !== "draft");
			self::ctrSyncInventory($invoice, $released, $userId);

			Connection::commit();

		} catch (Exception $e) {

			Connection::rollBack();

		}

	}

	/*=============================================
	SYNC STOCK MOVEMENTS + COGS FOR THE INVOICE'S GOODS
	=============================================
	When released, each goods line leaves inventory at its buying price and
	posts Dr Cost of Goods Sold / Cr Inventory. Services are skipped. Always
	delete-and-repost so edits and payment reversals stay correct.
	*/

	/**
	 * @param array    $invoice
	 * @param bool     $released
	 * @param int|null $userId
	 * @return void
	 */
	private static function ctrSyncInventory(array $invoice, bool $released, ?int $userId): void {

		$idInvoice = (int)$invoice["id"];

		// Clear any prior COGS journal for this invoice; movements are handled
		// by the sync call below.
		ModelAccounting::mdlDeleteEntriesBySource("invoice_cogs", $idInvoice);

		if (!$released) {
			ModelInventory::mdlDeleteMovementsBySource("invoice", $idInvoice);
			return;
		}

		$items = json_decode((string)$invoice["items"], true) ?: [];
		$movementDate = date("Y-m-d");

		$lines = [];
		$cogsTotal = 0.0;

		foreach ($items as $it) {

			$idProduct = (int)($it["id"] ?? 0);
			$qty       = (int)($it["quantity"] ?? 0);
			if ($idProduct <= 0 || $qty <= 0) {
				continue;
			}

			$product = ProductsModel::mdlShowProducts("products", "id", $idProduct, "id");
			if (!is_array($product) || ($product["type"] ?? "good") !== "good") {
				continue; // services don't move stock or post COGS
			}

			$unitCost   = (float)$product["buyingPrice"];
			$cogsTotal += $unitCost * $qty;

			$lines[] = [
				"idProduct"    => $idProduct,
				"qtyChange"    => -$qty,
				"unitCost"     => $unitCost,
				"movementDate" => $movementDate,
				"note"         => "Invoice #" . $invoice["invoiceNumber"],
			];

		}

		ModelInventory::mdlSyncSourceMovements("invoice", $idInvoice, $lines);

		if ($cogsTotal > 0) {
			ModelAccounting::mdlPostEntry(
				[
					"entryDate"   => $movementDate,
					"reference"   => "COGS-" . $invoice["invoiceNumber"],
					"sourceType"  => "invoice_cogs",
					"sourceId"    => $idInvoice,
					"description" => "Cost of goods sold for invoice #" . $invoice["invoiceNumber"],
					"createdBy"   => $userId,
				],
				[
					["code" => "5000", "debit" => $cogsTotal, "credit" => 0],          // Cost of Goods Sold
					["code" => "1200", "debit" => 0,          "credit" => $cogsTotal], // Inventory Asset
				]
			);
		}

	}

	/*=============================================
	SYNC INVOICE ACCOUNTING ENTRY
	=============================================
	A draft invoice has no accounting impact. Once it is sent (or beyond),
	it owes: Dr Accounts Receivable (total), Cr Sales Revenue (net), Cr Tax Payable (tax).
	We delete-and-repost so edits to totals stay correct.
	*/

	/**
	 * @param array    $invoice  the invoice row (pre-recalc totals are unchanged)
	 * @param string   $status
	 * @param int|null $userId
	 * @return void
	 */
	private static function ctrSyncInvoiceJournal(array $invoice, string $status, ?int $userId): void {

		$idInvoice = (int)$invoice["id"];

		ModelAccounting::mdlDeleteEntriesBySource("invoice", $idInvoice);

		if ($status === "draft") {
			return; // drafts are not yet recognised in the books
		}

		$total = (float)$invoice["totalPrice"];
		$tax   = (float)$invoice["tax"];
		$net   = round($total - $tax, 2); // revenue portion

		$entryDate = !empty($invoice["invoiceDate"]) ? substr((string)$invoice["invoiceDate"], 0, 10) : date("Y-m-d");

		ModelAccounting::mdlPostEntry(
			[
				"entryDate"   => $entryDate,
				"reference"   => "INV-" . $invoice["invoiceNumber"],
				"sourceType"  => "invoice",
				"sourceId"    => $idInvoice,
				"description" => "Invoice #" . $invoice["invoiceNumber"],
				"createdBy"   => $userId,
			],
			[
				["code" => "1100", "debit" => $total, "credit" => 0],     // Accounts Receivable
				["code" => "4000", "debit" => 0,      "credit" => $net],  // Sales Revenue
				["code" => "2200", "debit" => 0,      "credit" => $tax],  // Tax Payable
			]
		);

	}

}
