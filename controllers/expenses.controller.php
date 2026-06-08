<?php

/**
 * Expenses. Each add/edit/delete keeps a balanced journal in sync:
 *   Dr <expense account>   Cr <paid-through account>
 * Everything commits as one unit of work.
 */
class ControllerExpenses {

	/*=============================================
	SHOW EXPENSES
	=============================================*/

	public static function ctrShowExpenses(): array {
		return ModelExpenses::mdlShowExpenses();
	}

	/*=============================================
	ADD EXPENSE
	=============================================*/

	public static function ctrAddExpense(): void {

		if (!isset($_POST["newExpense"])) {
			return;
		}

		$amount           = (float)($_POST["expenseAmount"] ?? 0);
		$idExpenseAccount = (int)($_POST["idExpenseAccount"] ?? 0);
		$idPaidThrough    = (int)($_POST["idPaidThrough"] ?? 0);

		if ($amount <= 0 || $idExpenseAccount <= 0 || $idPaidThrough <= 0) {
			return;
		}

		$userId  = (int)($_SESSION["id"] ?? 0);
		$expDate = ($_POST["expenseDate"] ?? "") !== "" ? $_POST["expenseDate"] : date("Y-m-d");

		Connection::begin();

		try {

			$data = [
				"expenseNumber"    => ModelExpenses::mdlNextExpenseNumber(),
				"idExpenseAccount" => $idExpenseAccount,
				"idPaidThrough"    => $idPaidThrough,
				"amount"           => number_format($amount, 2, '.', ''),
				"expenseDate"      => $expDate,
				"payee"            => $_POST["payee"] ?? "",
				"reference"        => $_POST["expenseReference"] ?? "",
				"notes"            => $_POST["expenseNotes"] ?? "",
				"createdBy"        => $userId,
			];

			$expenseId = ModelExpenses::mdlAddExpense($data);

			if ($expenseId === "error") {
				Connection::rollBack();
				return;
			}

			self::postExpenseJournal((int)$expenseId, $idExpenseAccount, $idPaidThrough, $amount, $expDate, $data["expenseNumber"], $userId);

			Connection::commit();

		} catch (Exception $e) {
			Connection::rollBack();
			return;
		}

		self::redirect("Expense recorded successfully");

	}

	/*=============================================
	EDIT EXPENSE
	=============================================*/

	public static function ctrEditExpense(): void {

		if (!isset($_POST["editExpense"])) {
			return;
		}

		$id               = (int)$_POST["editExpense"];
		$amount           = (float)($_POST["expenseAmount"] ?? 0);
		$idExpenseAccount = (int)($_POST["idExpenseAccount"] ?? 0);
		$idPaidThrough    = (int)($_POST["idPaidThrough"] ?? 0);

		$existing = ModelExpenses::mdlGetExpense($id);
		if (!is_array($existing) || $amount <= 0 || $idExpenseAccount <= 0 || $idPaidThrough <= 0) {
			return;
		}

		$userId  = (int)($_SESSION["id"] ?? 0);
		$expDate = ($_POST["expenseDate"] ?? "") !== "" ? $_POST["expenseDate"] : $existing["expenseDate"];

		Connection::begin();

		try {

			$data = [
				"id"               => $id,
				"idExpenseAccount" => $idExpenseAccount,
				"idPaidThrough"    => $idPaidThrough,
				"amount"           => number_format($amount, 2, '.', ''),
				"expenseDate"      => $expDate,
				"payee"            => $_POST["payee"] ?? "",
				"reference"        => $_POST["expenseReference"] ?? "",
				"notes"            => $_POST["expenseNotes"] ?? "",
			];

			if (ModelExpenses::mdlEditExpense($data) !== "ok") {
				Connection::rollBack();
				return;
			}

			// Re-post the journal cleanly.
			ModelAccounting::mdlDeleteEntriesBySource("expense", $id);
			self::postExpenseJournal($id, $idExpenseAccount, $idPaidThrough, $amount, $expDate, $existing["expenseNumber"], $userId);

			Connection::commit();

		} catch (Exception $e) {
			Connection::rollBack();
			return;
		}

		self::redirect("Expense updated successfully");

	}

	/*=============================================
	DELETE EXPENSE
	=============================================*/

	public static function ctrDeleteExpense(): void {

		if (!isset($_GET["deleteExpense"])) {
			return;
		}

		$id = (int)$_GET["deleteExpense"];
		if (!is_array(ModelExpenses::mdlGetExpense($id))) {
			return;
		}

		Connection::begin();

		try {
			ModelExpenses::mdlDeleteExpense($id);
			ModelAccounting::mdlDeleteEntriesBySource("expense", $id);
			Connection::commit();
		} catch (Exception $e) {
			Connection::rollBack();
			return;
		}

		self::redirect("Expense deleted successfully");

	}

	/*=============================================
	POST THE EXPENSE JOURNAL (Dr expense / Cr paid-through)
	=============================================*/

	private static function postExpenseJournal(int $expenseId, int $idExpenseAccount, int $idPaidThrough, float $amount, string $date, string $ref, int $userId): void {

		$expenseAcc = ModelAccounting::mdlGetAccount($idExpenseAccount);
		$paidAcc    = ModelAccounting::mdlGetAccount($idPaidThrough);

		if (!is_array($expenseAcc) || !is_array($paidAcc)) {
			return;
		}

		ModelAccounting::mdlPostEntry(
			[
				"entryDate"   => $date,
				"reference"   => $ref,
				"sourceType"  => "expense",
				"sourceId"    => $expenseId,
				"description" => "Expense " . $ref . " — " . $expenseAcc["name"],
				"createdBy"   => $userId,
			],
			[
				["code" => $expenseAcc["code"], "debit" => $amount, "credit" => 0],
				["code" => $paidAcc["code"],    "debit" => 0,       "credit" => $amount],
			]
		);

	}

	/*=============================================
	REDIRECT HELPER
	=============================================*/

	private static function redirect(string $title): void {

		echo '<script>
		swal({
			  type: "success",
			  title: "' . $title . '",
			  showConfirmButton: true,
			  confirmButtonText: "Close"
			  }).then((result) => {
						if (result.value) { window.location = "expenses"; }
					})
		</script>';

	}

}
