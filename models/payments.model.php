<?php

require_once 'connection.php';

/**
 * Payments received against invoices.
 *
 * A payment is always its own record — the invoice never
 * stores payment details directly. amountPaid / balanceDue on the invoice
 * are recalculated from SUM(amount) here. See ControllerInvoices::ctrRecalcInvoice.
 */
class ModelPayments {

	/*=============================================
	SHOW PAYMENTS FOR AN INVOICE
	=============================================*/

	/**
	 * @param int $idInvoice
	 * @return array
	 */
	public static function mdlShowInvoicePayments(int $idInvoice): array {

		$stmt = Connection::connect()->prepare(
			"SELECT * FROM payments_received WHERE idInvoice = :idInvoice ORDER BY paymentDate ASC, id ASC"
		);

		$stmt->bindParam(":idInvoice", $idInvoice, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	PAYMENTS FOR A CUSTOMER (across all their invoices)
	=============================================*/

	/**
	 * @param int $idCustomer
	 * @return array
	 */
	public static function mdlShowCustomerPayments(int $idCustomer): array {

		$stmt = Connection::connect()->prepare(
			"SELECT * FROM payments_received WHERE idCustomer = :idCustomer ORDER BY paymentDate ASC, id ASC"
		);
		$stmt->bindParam(":idCustomer", $idCustomer, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	ALL PAYMENTS (for reports)
	=============================================*/

	public static function mdlShowAllPayments(): array {

		$stmt = Connection::connect()->prepare("SELECT * FROM payments_received ORDER BY paymentDate DESC, id DESC");
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	GET A SINGLE PAYMENT
	=============================================*/

	/**
	 * @param int $id
	 * @return array|false
	 */
	public static function mdlGetPayment(int $id) {

		$stmt = Connection::connect()->prepare("SELECT * FROM payments_received WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetch();

	}

	/*=============================================
	SUM OF PAYMENTS FOR AN INVOICE
	=============================================*/

	/**
	 * @param int $idInvoice
	 * @return float
	 */
	public static function mdlSumInvoicePayments(int $idInvoice): float {

		$stmt = Connection::connect()->prepare(
			"SELECT COALESCE(SUM(amount), 0) AS paid FROM payments_received WHERE idInvoice = :idInvoice"
		);

		$stmt->bindParam(":idInvoice", $idInvoice, PDO::PARAM_INT);
		$stmt->execute();

		$row = $stmt->fetch();

		return (float)($row["paid"] ?? 0);

	}

	/*=============================================
	ADD PAYMENT
	=============================================*/

	/**
	 * @param array $data
	 * @return string returns the new payment id on success, "error" otherwise
	 */
	public static function mdlAddPayment(array $data): string {

		// Keep one connection so lastInsertId() reflects this INSERT.
		$link = Connection::connect();

		$stmt = $link->prepare(
			"INSERT INTO payments_received
			   (paymentNumber, idInvoice, idCustomer, amount, paymentDate, paymentMode, reference, notes, createdBy)
			 VALUES
			   (:paymentNumber, :idInvoice, :idCustomer, :amount, :paymentDate, :paymentMode, :reference, :notes, :createdBy)"
		);

		$stmt->bindParam(":paymentNumber", $data["paymentNumber"], PDO::PARAM_STR);
		$stmt->bindParam(":idInvoice",     $data["idInvoice"],     PDO::PARAM_INT);
		$stmt->bindParam(":idCustomer",    $data["idCustomer"],    PDO::PARAM_INT);
		$stmt->bindParam(":amount",        $data["amount"],        PDO::PARAM_STR);
		$stmt->bindParam(":paymentDate",   $data["paymentDate"],   PDO::PARAM_STR);
		$stmt->bindParam(":paymentMode",   $data["paymentMode"],   PDO::PARAM_STR);
		$stmt->bindParam(":reference",     $data["reference"],     PDO::PARAM_STR);
		$stmt->bindParam(":notes",         $data["notes"],         PDO::PARAM_STR);
		$stmt->bindParam(":createdBy",     $data["createdBy"],     PDO::PARAM_INT);

		if ($stmt->execute()) {
			return (string)$link->lastInsertId();
		}

		return "error";

	}

	/*=============================================
	EDIT PAYMENT
	=============================================*/

	/**
	 * @param array $data
	 * @return string
	 */
	public static function mdlEditPayment(array $data): string {

		$stmt = Connection::connect()->prepare(
			"UPDATE payments_received
			    SET amount = :amount, paymentDate = :paymentDate, paymentMode = :paymentMode,
			        reference = :reference, notes = :notes
			  WHERE id = :id"
		);

		$stmt->bindParam(":id",          $data["id"],          PDO::PARAM_INT);
		$stmt->bindParam(":amount",      $data["amount"],      PDO::PARAM_STR);
		$stmt->bindParam(":paymentDate", $data["paymentDate"], PDO::PARAM_STR);
		$stmt->bindParam(":paymentMode", $data["paymentMode"], PDO::PARAM_STR);
		$stmt->bindParam(":reference",   $data["reference"],   PDO::PARAM_STR);
		$stmt->bindParam(":notes",       $data["notes"],       PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	DELETE PAYMENT
	=============================================*/

	/**
	 * @param int $id
	 * @return string
	 */
	public static function mdlDeletePayment(int $id): string {

		$stmt = Connection::connect()->prepare("DELETE FROM payments_received WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	DELETE ALL PAYMENTS FOR AN INVOICE (cascade on invoice delete)
	=============================================*/

	/**
	 * @param int $idInvoice
	 * @return string
	 */
	public static function mdlDeleteInvoicePayments(int $idInvoice): string {

		$stmt = Connection::connect()->prepare("DELETE FROM payments_received WHERE idInvoice = :idInvoice");
		$stmt->bindParam(":idInvoice", $idInvoice, PDO::PARAM_INT);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	NEXT PAYMENT NUMBER (simple sequential PAY-n)
	=============================================*/

	public static function mdlNextPaymentNumber(): string {

		$stmt = Connection::connect()->prepare("SELECT COALESCE(MAX(id), 0) + 1 AS nextId FROM payments_received");
		$stmt->execute();
		$row = $stmt->fetch();

		return "PAY-" . (int)($row["nextId"] ?? 1);

	}

}
