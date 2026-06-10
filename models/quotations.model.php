<?php

require_once 'connection.php';

/**
 * Quotations (estimates). A quotation is a proposal only — it never posts to
 * the General Ledger and never moves stock. When accepted it can be converted
 * into an invoice, which is what actually books revenue.
 */
class ModelQuotations {

	/*=============================================
	SHOW QUOTATIONS
	=============================================*/

	/**
	 * @param string|null $item
	 * @param mixed       $value
	 * @return array|false
	 */
	public static function mdlShowQuotations($item, $value) {

		// Scoped by org automatically; column name is validated by Scope::col()
		// (the previous {$item} interpolation was a latent injection point).
		if ($item != null) {
			return Scope::firstBy('quotations', $item, $value, 'id ASC');
		}

		return Scope::all('quotations', 'id ASC');

	}

	/*=============================================
	ADD QUOTATION
	=============================================*/

	public static function mdlAddQuotation(array $data): string {

		$stmt = Connection::connect()->prepare(
			"INSERT INTO quotations
			   (quoteNumber, orderReference, idCustomer, idSeller, items,
			    subtotal, discount, discountType, discountValue, shipping, adjustments,
			    tax, netPrice, totalPrice, expiryDate, status, notes, termsConditions, createdBy, currency, idOrganization)
			 VALUES
			   (:quoteNumber, :orderReference, :idCustomer, :idSeller, :items,
			    :subtotal, :discount, :discountType, :discountValue, :shipping, :adjustments,
			    :tax, :netPrice, :totalPrice, :expiryDate, :status, :notes, :termsConditions, :createdBy, :currency, " . (int)Tenant::id() . ")"
		);

		self::bindShared($stmt, $data);
		$stmt->bindParam(":quoteNumber", $data["quoteNumber"], PDO::PARAM_STR);
		$stmt->bindParam(":createdBy",   $data["createdBy"],   PDO::PARAM_INT);
		$stmt->bindValue(":currency",    $data["currency"] ?? Currency::base(), PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	EDIT QUOTATION
	=============================================*/

	public static function mdlEditQuotation(array $data): string {

		$stmt = Connection::connect()->prepare(
			"UPDATE quotations
			    SET idCustomer = :idCustomer, idSeller = :idSeller, orderReference = :orderReference,
			        items = :items, subtotal = :subtotal, discount = :discount,
			        discountType = :discountType, discountValue = :discountValue,
			        shipping = :shipping, adjustments = :adjustments, tax = :tax,
			        netPrice = :netPrice, totalPrice = :totalPrice, expiryDate = :expiryDate,
			        status = :status, notes = :notes, termsConditions = :termsConditions,
			        currency = :currency, modifiedBy = :modifiedBy, modifiedDate = NOW()
			  WHERE id = :id AND idOrganization = " . (int)Tenant::id() . ""
		);

		self::bindShared($stmt, $data);
		$stmt->bindParam(":id",         $data["id"],         PDO::PARAM_INT);
		$stmt->bindParam(":modifiedBy", $data["modifiedBy"], PDO::PARAM_INT);
		$stmt->bindValue(":currency",   $data["currency"] ?? Currency::base(), PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	DELETE QUOTATION
	=============================================*/

	public static function mdlDeleteQuotation(int $id): string {

		return Scope::deleteById('quotations', $id) ? "ok" : "error";

	}

	/*=============================================
	MARK A QUOTATION AS CONVERTED TO AN INVOICE
	=============================================*/

	public static function mdlMarkConverted(int $id, int $idInvoice): string {

		$stmt = Connection::connect()->prepare(
			"UPDATE quotations SET status = 'invoiced', idInvoice = :idInvoice WHERE id = :id AND idOrganization = " . (int)Tenant::id() . ""
		);
		$stmt->bindParam(":id",        $id,        PDO::PARAM_INT);
		$stmt->bindParam(":idInvoice", $idInvoice, PDO::PARAM_INT);

		return $stmt->execute() ? "ok" : "error";

	}

	/*=============================================
	NEXT QUOTATION NUMBER (sequential, starts at 1001)
	=============================================*/

	public static function mdlNextQuoteNumber(): string {

		$stmt = Connection::connect()->prepare("SELECT COALESCE(MAX(CAST(quoteNumber AS UNSIGNED)), 1000) + 1 AS n FROM quotations WHERE idOrganization = " . (int)Tenant::id() . "");
		$stmt->execute();
		$row = $stmt->fetch();

		return (string)(int)($row["n"] ?? 1001);

	}

	/*=============================================
	SHARED COLUMN BINDINGS
	=============================================*/

	private static function bindShared(PDOStatement $stmt, array $data): void {
		$stmt->bindParam(":orderReference", $data["orderReference"], PDO::PARAM_STR);
		$stmt->bindParam(":idCustomer",     $data["idCustomer"],     PDO::PARAM_INT);
		$stmt->bindParam(":idSeller",       $data["idSeller"],       PDO::PARAM_INT);
		$stmt->bindParam(":items",          $data["items"],          PDO::PARAM_STR);
		$stmt->bindParam(":subtotal",       $data["subtotal"],       PDO::PARAM_STR);
		$stmt->bindParam(":discount",       $data["discount"],       PDO::PARAM_STR);
		$stmt->bindParam(":discountType",   $data["discountType"],   PDO::PARAM_STR);
		$stmt->bindParam(":discountValue",  $data["discountValue"],  PDO::PARAM_STR);
		$stmt->bindParam(":shipping",       $data["shipping"],       PDO::PARAM_STR);
		$stmt->bindParam(":adjustments",    $data["adjustments"],    PDO::PARAM_STR);
		$stmt->bindParam(":tax",            $data["tax"],            PDO::PARAM_STR);
		$stmt->bindParam(":netPrice",       $data["netPrice"],       PDO::PARAM_STR);
		$stmt->bindParam(":totalPrice",     $data["totalPrice"],     PDO::PARAM_STR);
		$stmt->bindParam(":expiryDate",     $data["expiryDate"],     PDO::PARAM_STR);
		$stmt->bindParam(":status",         $data["status"],         PDO::PARAM_STR);
		$stmt->bindParam(":notes",          $data["notes"],          PDO::PARAM_STR);
		$stmt->bindParam(":termsConditions",$data["termsConditions"],PDO::PARAM_STR);
	}

}
