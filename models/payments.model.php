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

		return Scope::rowsBy('payments_received', 'idInvoice', $idInvoice, 'paymentDate ASC, id ASC');

	}

	/*=============================================
	PAYMENTS FOR A CUSTOMER (across all their invoices)
	=============================================*/

	/**
	 * @param int $idCustomer
	 * @return array
	 */
	public static function mdlShowCustomerPayments(int $idCustomer): array {

		return Scope::rowsBy('payments_received', 'idCustomer', $idCustomer, 'paymentDate ASC, id ASC');

	}

	/*=============================================
	ALL PAYMENTS (for reports)
	=============================================*/

	public static function mdlShowAllPayments(): array {

		return Scope::all('payments_received', 'paymentDate DESC, id DESC');

	}

	/*=============================================
	GET A SINGLE PAYMENT
	=============================================*/

	/**
	 * @param int $id
	 * @return array|false
	 */
	public static function mdlGetPayment(int $id) {

		return Scope::find('payments_received', $id);

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
			"SELECT COALESCE(SUM(amount), 0) AS paid FROM payments_received WHERE idInvoice = :idInvoice AND idOrganization = " . (int)Tenant::id() . ""
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
			   (paymentNumber, idInvoice, idCustomer, amount, paymentDate, paymentMode, reference, notes, createdBy, idOrganization)
			 VALUES
			   (:paymentNumber, :idInvoice, :idCustomer, :amount, :paymentDate, :paymentMode, :reference, :notes, :createdBy, " . (int)Tenant::id() . ")"
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
			  WHERE id = :id AND idOrganization = " . (int)Tenant::id() . ""
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

		return Scope::deleteById('payments_received', $id) ? "ok" : "error";

	}

	/*=============================================
	DELETE ALL PAYMENTS FOR AN INVOICE (cascade on invoice delete)
	=============================================*/

	/**
	 * @param int $idInvoice
	 * @return string
	 */
	public static function mdlDeleteInvoicePayments(int $idInvoice): string {

		$stmt = Connection::connect()->prepare("DELETE FROM payments_received WHERE idInvoice = :idInvoice AND idOrganization = " . (int)Tenant::id() . "");
		$stmt->bindParam(":idInvoice", $idInvoice, PDO::PARAM_INT);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	NEXT PAYMENT NUMBER (simple sequential PAY-n)
	=============================================*/

	public static function mdlNextPaymentNumber(): string {

		$stmt = Connection::connect()->prepare("SELECT COALESCE(MAX(id), 0) + 1 AS nextId FROM payments_received WHERE idOrganization = " . (int)Tenant::id() . "");
		$stmt->execute();
		$row = $stmt->fetch();

		return "PAY-" . (int)($row["nextId"] ?? 1);

	}

}
