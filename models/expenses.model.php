<?php

require_once 'connection.php';

/**
 * Expenses. Each expense posts a double-entry journal:
 *   Dr <expense account>   Cr <paid-through account, e.g. Cash/Bank or A/P>
 */
class ModelExpenses {

	/*=============================================
	SHOW EXPENSES (with account names joined)
	=============================================*/

	public static function mdlShowExpenses(): array {

		$stmt = Connection::connect()->prepare(
			"SELECT e.*,
			        ea.code AS expenseCode, ea.name AS expenseName,
			        pa.code AS paidCode,    pa.name AS paidName
			   FROM expenses e
			   LEFT JOIN accounts ea ON ea.id = e.idExpenseAccount
			   LEFT JOIN accounts pa ON pa.id = e.idPaidThrough
			  WHERE e.idOrganization = " . (int)Tenant::id() . " ORDER BY e.expenseDate DESC, e.id DESC"
		);
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	GET A SINGLE EXPENSE
	=============================================*/

	public static function mdlGetExpense(int $id) {

		return Scope::find('expenses', $id);

	}

	/*=============================================
	ADD EXPENSE
	=============================================*/

	/**
	 * @param array $data
	 * @return string new expense id, or "error"
	 */
	public static function mdlAddExpense(array $data): string {

		$link = Connection::connect();

		$stmt = $link->prepare(
			"INSERT INTO expenses
			   (expenseNumber, idExpenseAccount, idPaidThrough, amount, expenseDate, payee, reference, notes, createdBy, idOrganization)
			 VALUES
			   (:expenseNumber, :idExpenseAccount, :idPaidThrough, :amount, :expenseDate, :payee, :reference, :notes, :createdBy, " . (int)Tenant::id() . ")"
		);

		$stmt->bindParam(":expenseNumber",    $data["expenseNumber"],    PDO::PARAM_STR);
		$stmt->bindParam(":idExpenseAccount", $data["idExpenseAccount"], PDO::PARAM_INT);
		$stmt->bindParam(":idPaidThrough",    $data["idPaidThrough"],    PDO::PARAM_INT);
		$stmt->bindParam(":amount",           $data["amount"],           PDO::PARAM_STR);
		$stmt->bindParam(":expenseDate",      $data["expenseDate"],      PDO::PARAM_STR);
		$stmt->bindParam(":payee",            $data["payee"],            PDO::PARAM_STR);
		$stmt->bindParam(":reference",        $data["reference"],        PDO::PARAM_STR);
		$stmt->bindParam(":notes",            $data["notes"],            PDO::PARAM_STR);
		$stmt->bindParam(":createdBy",        $data["createdBy"],        PDO::PARAM_INT);

		if ($stmt->execute()) {
			return (string)$link->lastInsertId();
		}

		return "error";

	}

	/*=============================================
	EDIT EXPENSE
	=============================================*/

	public static function mdlEditExpense(array $data): string {

		$stmt = Connection::connect()->prepare(
			"UPDATE expenses
			    SET idExpenseAccount = :idExpenseAccount, idPaidThrough = :idPaidThrough,
			        amount = :amount, expenseDate = :expenseDate, payee = :payee,
			        reference = :reference, notes = :notes
			  WHERE id = :id AND idOrganization = " . (int)Tenant::id() . ""
		);

		$stmt->bindParam(":id",               $data["id"],               PDO::PARAM_INT);
		$stmt->bindParam(":idExpenseAccount", $data["idExpenseAccount"], PDO::PARAM_INT);
		$stmt->bindParam(":idPaidThrough",    $data["idPaidThrough"],    PDO::PARAM_INT);
		$stmt->bindParam(":amount",           $data["amount"],           PDO::PARAM_STR);
		$stmt->bindParam(":expenseDate",      $data["expenseDate"],      PDO::PARAM_STR);
		$stmt->bindParam(":payee",            $data["payee"],            PDO::PARAM_STR);
		$stmt->bindParam(":reference",        $data["reference"],        PDO::PARAM_STR);
		$stmt->bindParam(":notes",            $data["notes"],            PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	DELETE EXPENSE
	=============================================*/

	public static function mdlDeleteExpense(int $id): string {

		return Scope::deleteById('expenses', $id) ? "ok" : "error";

	}

	/*=============================================
	NEXT EXPENSE NUMBER (EXP-n)
	=============================================*/

	public static function mdlNextExpenseNumber(): string {

		$stmt = Connection::connect()->prepare("SELECT COALESCE(MAX(id), 0) + 1 AS nextId FROM expenses WHERE idOrganization = " . (int)Tenant::id() . "");
		$stmt->execute();
		$row = $stmt->fetch();

		return "EXP-" . (int)($row["nextId"] ?? 1);

	}

}
