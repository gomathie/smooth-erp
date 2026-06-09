<?php

require_once 'connection.php';

/**
 * Perpetual inventory ledger.
 *
 * Every change in stock is recorded as a movement (negative = out, positive
 * = in). The authoritative quantity on hand for a product is SUM(qtyChange);
 * products.stock is kept as a synchronized cache so the rest of the app
 * (sales screen, product list) keeps working unchanged.
 *
 * All writes are expected to run inside a Connection transaction owned by the
 * calling controller.
 */
class ModelInventory {

	/*=============================================
	ADD ONE MOVEMENT (and adjust the stock cache)
	=============================================*/

	/**
	 * @param array $data idProduct, sourceType, sourceId, qtyChange, unitCost, movementDate, note
	 * @return void
	 */
	public static function mdlAddMovement(array $data): void {

		$link = Connection::connect();

		$stmt = $link->prepare(
			"INSERT INTO stock_movements
			   (idProduct, sourceType, sourceId, qtyChange, unitCost, movementDate, note)
			 VALUES
			   (:idProduct, :sourceType, :sourceId, :qtyChange, :unitCost, :movementDate, :note)"
		);

		$unitCost = number_format((float)($data["unitCost"] ?? 0), 2, '.', '');

		$stmt->bindValue(":idProduct",    (int)$data["idProduct"],   PDO::PARAM_INT);
		$stmt->bindValue(":sourceType",   $data["sourceType"],       PDO::PARAM_STR);
		$stmt->bindValue(":sourceId",     (int)($data["sourceId"] ?? 0), PDO::PARAM_INT);
		$stmt->bindValue(":qtyChange",    (int)$data["qtyChange"],   PDO::PARAM_INT);
		$stmt->bindValue(":unitCost",     $unitCost,                 PDO::PARAM_STR);
		$stmt->bindValue(":movementDate", $data["movementDate"],     PDO::PARAM_STR);
		$stmt->bindValue(":note",         $data["note"] ?? null,     PDO::PARAM_STR);
		$stmt->execute();

		self::adjustStockCache((int)$data["idProduct"], (int)$data["qtyChange"]);

	}

	/*=============================================
	DELETE ALL MOVEMENTS FOR A SOURCE (reversing the cache)
	=============================================*/

	/**
	 * @param string $sourceType
	 * @param int    $sourceId
	 * @return void
	 */
	public static function mdlDeleteMovementsBySource(string $sourceType, int $sourceId): void {

		$link = Connection::connect();

		// Reverse each movement's effect on the cache before deleting it.
		$find = $link->prepare(
			"SELECT idProduct, qtyChange FROM stock_movements
			  WHERE sourceType = :sourceType AND sourceId = :sourceId"
		);
		$find->bindValue(":sourceType", $sourceType, PDO::PARAM_STR);
		$find->bindValue(":sourceId",   $sourceId,   PDO::PARAM_INT);
		$find->execute();

		foreach ($find->fetchAll() as $row) {
			self::adjustStockCache((int)$row["idProduct"], -(int)$row["qtyChange"]);
		}

		$del = $link->prepare(
			"DELETE FROM stock_movements WHERE sourceType = :sourceType AND sourceId = :sourceId"
		);
		$del->bindValue(":sourceType", $sourceType, PDO::PARAM_STR);
		$del->bindValue(":sourceId",   $sourceId,   PDO::PARAM_INT);
		$del->execute();

	}

	/*=============================================
	RE-SYNC A SOURCE'S MOVEMENTS (delete old, add new)
	=============================================
	Idempotent reposting: call with the full current set of lines whenever a
	document (invoice / sale) changes. Mirrors how the accounting journal is
	re-posted, so stock never drifts.
	*/

	/**
	 * @param string $sourceType
	 * @param int    $sourceId
	 * @param array  $lines  each: idProduct, qtyChange, unitCost, movementDate, note
	 * @return void
	 */
	public static function mdlSyncSourceMovements(string $sourceType, int $sourceId, array $lines): void {

		self::mdlDeleteMovementsBySource($sourceType, $sourceId);

		foreach ($lines as $line) {
			$line["sourceType"] = $sourceType;
			$line["sourceId"]   = $sourceId;
			self::mdlAddMovement($line);
		}

	}

	/*=============================================
	MOVEMENT HISTORY FOR A PRODUCT
	=============================================*/

	public static function mdlProductMovements(int $idProduct): array {

		$stmt = Connection::connect()->prepare(
			"SELECT * FROM stock_movements WHERE idProduct = :idProduct ORDER BY movementDate ASC, id ASC"
		);
		$stmt->bindValue(":idProduct", $idProduct, PDO::PARAM_INT);
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	ALL MOVEMENTS WITH PRODUCT INFO (for the Inventory report)
	=============================================*/

	public static function mdlAllMovements(): array {

		$stmt = Connection::connect()->prepare(
			"SELECT m.*, p.code, p.description
			   FROM stock_movements m
			   LEFT JOIN products p ON p.id = m.idProduct
			  ORDER BY m.movementDate DESC, m.id DESC"
		);
		$stmt->execute();

		return $stmt->fetchAll() ?: [];

	}

	/*=============================================
	TOTAL INVENTORY VALUE (qty on hand * unit cost, by latest cost)
	=============================================*/

	public static function mdlInventoryValue(): float {

		// Value at the product's current buying price (moving-average proxy).
		$stmt = Connection::connect()->prepare(
			"SELECT COALESCE(SUM(p.stock * p.buyingPrice), 0) AS val
			   FROM products p WHERE p.type = 'good'"
		);
		$stmt->execute();
		$row = $stmt->fetch();

		return (float)($row["val"] ?? 0);

	}

	/*=============================================
	RECONCILE CACHE FROM LEDGER (stock = SUM(qtyChange))
	=============================================*/

	public static function mdlReconcile(int $idProduct): void {

		$link = Connection::connect();
		$stmt = $link->prepare(
			"UPDATE products
			    SET stock = (SELECT COALESCE(SUM(qtyChange), 0) FROM stock_movements WHERE idProduct = :p1)
			  WHERE id = :p2"
		);
		$stmt->bindValue(":p1", $idProduct, PDO::PARAM_INT);
		$stmt->bindValue(":p2", $idProduct, PDO::PARAM_INT);
		$stmt->execute();

	}

	/*=============================================
	INTERNAL — adjust products.stock cache by a delta
	=============================================*/

	private static function adjustStockCache(int $idProduct, int $delta): void {

		$stmt = Connection::connect()->prepare(
			"UPDATE products SET stock = stock + :delta WHERE id = :id"
		);
		$stmt->bindValue(":delta", $delta,     PDO::PARAM_INT);
		$stmt->bindValue(":id",    $idProduct, PDO::PARAM_INT);
		$stmt->execute();

	}

}
