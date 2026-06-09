<?php

require_once 'connection.php';

class ModelInvoices {

	/*=============================================
	SHOW INVOICES
	=============================================*/

	/**
	 * @param string      $table
	 * @param string|null $item
	 * @param mixed       $value
	 * @return array|false
	 */
	public static function mdlShowInvoices(string $table, $item, $value) {

		if ($item != null) {

			$stmt = Connection::connect()->prepare("SELECT * FROM {$table} WHERE {$item} = :{$item} ORDER BY id ASC");

			$stmt->bindParam(":{$item}", $value, PDO::PARAM_STR);

			$stmt->execute();

			return $stmt->fetch();

		} else {

			$stmt = Connection::connect()->prepare("SELECT * FROM {$table} ORDER BY id ASC");

			$stmt->execute();

			return $stmt->fetchAll();

		}

	}

	/*=============================================
	ADD INVOICE
	=============================================*/

	/**
	 * @param string $table
	 * @param array  $data
	 * @return string
	 */
	public static function mdlAddInvoice(string $table, array $data): string {

		// Default discount metadata so callers that don't set it (e.g. quote
		// conversion of older data) still insert cleanly.
		$discountType  = $data["discountType"]  ?? "amount";
		$discountValue = $data["discountValue"] ?? "0";

		$stmt = Connection::connect()->prepare(
			"INSERT INTO {$table}
			   (invoiceNumber, orderReference, idCustomer, idSeller, items,
			    subtotal, discount, discountType, discountValue, shipping, adjustments, tax, netPrice, totalPrice,
			    dueDate, paymentTerms, status, notes, termsConditions)
			 VALUES
			   (:invoiceNumber, :orderReference, :idCustomer, :idSeller, :items,
			    :subtotal, :discount, :discountType, :discountValue, :shipping, :adjustments, :tax, :netPrice, :totalPrice,
			    :dueDate, :paymentTerms, :status, :notes, :termsConditions)"
		);

		$stmt->bindParam(":invoiceNumber",  $data["invoiceNumber"],  PDO::PARAM_STR);
		$stmt->bindParam(":orderReference", $data["orderReference"], PDO::PARAM_STR);
		$stmt->bindParam(":idCustomer",     $data["idCustomer"],     PDO::PARAM_INT);
		$stmt->bindParam(":idSeller",       $data["idSeller"],       PDO::PARAM_INT);
		$stmt->bindParam(":items",          $data["items"],          PDO::PARAM_STR);
		$stmt->bindParam(":subtotal",       $data["subtotal"],       PDO::PARAM_STR);
		$stmt->bindParam(":discount",       $data["discount"],       PDO::PARAM_STR);
		$stmt->bindParam(":discountType",   $discountType,           PDO::PARAM_STR);
		$stmt->bindParam(":discountValue",  $discountValue,          PDO::PARAM_STR);
		$stmt->bindParam(":shipping",       $data["shipping"],       PDO::PARAM_STR);
		$stmt->bindParam(":adjustments",    $data["adjustments"],    PDO::PARAM_STR);
		$stmt->bindParam(":tax",            $data["tax"],            PDO::PARAM_STR);
		$stmt->bindParam(":netPrice",       $data["netPrice"],       PDO::PARAM_STR);
		$stmt->bindParam(":totalPrice",     $data["totalPrice"],     PDO::PARAM_STR);
		$stmt->bindParam(":dueDate",        $data["dueDate"],        PDO::PARAM_STR);
		$stmt->bindParam(":paymentTerms",   $data["paymentTerms"],   PDO::PARAM_STR);
		$stmt->bindParam(":status",         $data["status"],         PDO::PARAM_STR);
		$stmt->bindParam(":notes",          $data["notes"],          PDO::PARAM_STR);
		$stmt->bindParam(":termsConditions",$data["termsConditions"],PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	EDIT INVOICE
	=============================================*/

	/**
	 * @param string $table
	 * @param array  $data
	 * @return string
	 */
	public static function mdlEditInvoice(string $table, array $data): string {

		$stmt = Connection::connect()->prepare(
			"UPDATE {$table}
			 SET idCustomer = :idCustomer, idSeller = :idSeller, orderReference = :orderReference,
			     items = :items, subtotal = :subtotal, discount = :discount,
			     discountType = :discountType, discountValue = :discountValue,
			     shipping = :shipping, adjustments = :adjustments,
			     tax = :tax, netPrice = :netPrice, totalPrice = :totalPrice,
			     dueDate = :dueDate, paymentTerms = :paymentTerms, status = :status,
			     notes = :notes, termsConditions = :termsConditions
			 WHERE id = :id"
		);

		$discountType  = $data["discountType"]  ?? "amount";
		$discountValue = $data["discountValue"] ?? "0";

		$stmt->bindParam(":id",            $data["id"],            PDO::PARAM_INT);
		$stmt->bindParam(":idCustomer",    $data["idCustomer"],    PDO::PARAM_INT);
		$stmt->bindParam(":idSeller",      $data["idSeller"],      PDO::PARAM_INT);
		$stmt->bindParam(":orderReference",$data["orderReference"],PDO::PARAM_STR);
		$stmt->bindParam(":items",         $data["items"],         PDO::PARAM_STR);
		$stmt->bindParam(":subtotal",      $data["subtotal"],      PDO::PARAM_STR);
		$stmt->bindParam(":discount",      $data["discount"],      PDO::PARAM_STR);
		$stmt->bindParam(":discountType",  $discountType,          PDO::PARAM_STR);
		$stmt->bindParam(":discountValue", $discountValue,         PDO::PARAM_STR);
		$stmt->bindParam(":shipping",      $data["shipping"],      PDO::PARAM_STR);
		$stmt->bindParam(":adjustments",   $data["adjustments"],   PDO::PARAM_STR);
		$stmt->bindParam(":tax",           $data["tax"],           PDO::PARAM_STR);
		$stmt->bindParam(":netPrice",      $data["netPrice"],      PDO::PARAM_STR);
		$stmt->bindParam(":totalPrice",    $data["totalPrice"],    PDO::PARAM_STR);
		$stmt->bindParam(":dueDate",       $data["dueDate"],       PDO::PARAM_STR);
		$stmt->bindParam(":paymentTerms",  $data["paymentTerms"],  PDO::PARAM_STR);
		$stmt->bindParam(":status",        $data["status"],        PDO::PARAM_STR);
		$stmt->bindParam(":notes",         $data["notes"],         PDO::PARAM_STR);
		$stmt->bindParam(":termsConditions",$data["termsConditions"],PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	DELETE INVOICE
	=============================================*/

	/**
	 * @param string $table
	 * @param int    $id
	 * @return string
	 */
	public static function mdlDeleteInvoice(string $table, int $id): string {

		$stmt = Connection::connect()->prepare("DELETE FROM {$table} WHERE id = :id");

		$stmt->bindParam(":id", $id, PDO::PARAM_INT);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	INVOICES FOR A CUSTOMER (list, newest activity last)
	=============================================*/

	/**
	 * @param int $idCustomer
	 * @return array
	 */
	public static function mdlInvoicesByCustomer(int $idCustomer): array {

		$stmt = Connection::connect()->prepare(
			"SELECT * FROM invoices WHERE idCustomer = :idCustomer ORDER BY invoiceDate ASC, id ASC"
		);
		$stmt->bindParam(":idCustomer", $idCustomer, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	UPDATE BALANCE + STATUS (central recalc writer)
	Never call directly — go through ControllerInvoices::ctrRecalcInvoice
	so payments, accounting and the activity log stay in sync.
	=============================================*/

	/**
	 * @param int    $id
	 * @param float  $amountPaid
	 * @param float  $balanceDue
	 * @param string $status
	 * @param int|null $modifiedBy
	 * @return string
	 */
	public static function mdlUpdateBalance(int $id, float $amountPaid, float $balanceDue, string $status, ?int $modifiedBy): string {

		$paid    = number_format($amountPaid, 2, '.', '');
		$balance = number_format($balanceDue, 2, '.', '');

		$stmt = Connection::connect()->prepare(
			"UPDATE invoices
			    SET amountPaid = :amountPaid, balanceDue = :balanceDue, status = :status,
			        modifiedBy = :modifiedBy, modifiedDate = NOW()
			  WHERE id = :id"
		);

		$stmt->bindParam(":id",         $id,         PDO::PARAM_INT);
		$stmt->bindParam(":amountPaid", $paid,       PDO::PARAM_STR);
		$stmt->bindParam(":balanceDue", $balance,    PDO::PARAM_STR);
		$stmt->bindParam(":status",     $status,     PDO::PARAM_STR);
		$stmt->bindParam(":modifiedBy", $modifiedBy, PDO::PARAM_INT);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	ACTIVITY LOG — record an action against an invoice
	=============================================*/

	/**
	 * @param int      $idInvoice
	 * @param int|null $idUser
	 * @param string   $action
	 * @param string   $description
	 * @return void
	 */
	public static function mdlLogActivity(int $idInvoice, ?int $idUser, string $action, string $description): void {

		$stmt = Connection::connect()->prepare(
			"INSERT INTO invoice_activity_log (idInvoice, idUser, action, description)
			 VALUES (:idInvoice, :idUser, :action, :description)"
		);

		$stmt->bindParam(":idInvoice",   $idInvoice,   PDO::PARAM_INT);
		$stmt->bindParam(":idUser",      $idUser,      PDO::PARAM_INT);
		$stmt->bindParam(":action",      $action,      PDO::PARAM_STR);
		$stmt->bindParam(":description", $description, PDO::PARAM_STR);
		$stmt->execute();

	}

	/*=============================================
	ACTIVITY LOG — list actions for an invoice
	=============================================*/

	/**
	 * @param int $idInvoice
	 * @return array
	 */
	public static function mdlShowActivity(int $idInvoice): array {

		$stmt = Connection::connect()->prepare(
			"SELECT * FROM invoice_activity_log WHERE idInvoice = :idInvoice ORDER BY createdDate DESC, id DESC"
		);
		$stmt->bindParam(":idInvoice", $idInvoice, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	ALL ACTIVITY (for the Activity report)
	=============================================*/

	public static function mdlAllActivity(): array {

		$stmt = Connection::connect()->prepare(
			"SELECT a.*, i.invoiceNumber
			   FROM invoice_activity_log a
			   LEFT JOIN invoices i ON i.id = a.idInvoice
			  ORDER BY a.createdDate DESC, a.id DESC"
		);
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	DELETE ACTIVITY LOG FOR AN INVOICE (cascade on delete)
	=============================================*/

	public static function mdlDeleteActivity(int $idInvoice): void {

		$stmt = Connection::connect()->prepare("DELETE FROM invoice_activity_log WHERE idInvoice = :idInvoice");
		$stmt->bindParam(":idInvoice", $idInvoice, PDO::PARAM_INT);
		$stmt->execute();

	}

}
