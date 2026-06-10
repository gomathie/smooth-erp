<?php

require_once 'connection.php';

/**
 * Double-entry accounting store.
 *
 * Every business event (invoice issued, payment received) is recorded as a
 * journal ENTRY made of two or more LINES whose debits equal credits.
 * Balances are always derived by summing lines per account — never stored.
 */
class ModelAccounting {

	/*=============================================
	GET ACCOUNT ID BY CODE (e.g. '1100' = Accounts Receivable)
	=============================================*/

	/**
	 * @param string $code
	 * @return int|null
	 */
	public static function mdlAccountIdByCode(string $code): ?int {

		$stmt = Connection::connect()->prepare("SELECT id FROM accounts WHERE code = :code AND idOrganization = " . (int)Tenant::id() . " LIMIT 1");
		$stmt->bindParam(":code", $code, PDO::PARAM_STR);
		$stmt->execute();

		$row = $stmt->fetch();

		return $row ? (int)$row["id"] : null;

	}

	/*=============================================
	CHART OF ACCOUNTS — list / get / add / edit / delete
	=============================================*/

	public static function mdlShowAccounts(): array {

		return Scope::all('accounts', 'code ASC');

	}

	/**
	 * @param int $id
	 * @return array|false
	 */
	public static function mdlGetAccount(int $id) {

		return Scope::find('accounts', $id);

	}

	/**
	 * Accounts of one type (asset|liability|equity|income|expense).
	 * @param string $type
	 * @return array
	 */
	public static function mdlAccountsByType(string $type): array {

		return Scope::rowsBy('accounts', 'type', $type, 'code ASC');

	}

	public static function mdlAddAccount(array $data): string {

		$stmt = Connection::connect()->prepare(
			"INSERT INTO accounts (code, name, type, isSystem, idOrganization) VALUES (:code, :name, :type, 0, " . (int)Tenant::id() . ")"
		);
		$stmt->bindParam(":code", $data["code"], PDO::PARAM_STR);
		$stmt->bindParam(":name", $data["name"], PDO::PARAM_STR);
		$stmt->bindParam(":type", $data["type"], PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

	public static function mdlEditAccount(array $data): string {

		// Code is immutable for system accounts; only name/type are updated here.
		$stmt = Connection::connect()->prepare(
			"UPDATE accounts SET name = :name, type = :type WHERE id = :id AND idOrganization = " . (int)Tenant::id() . ""
		);
		$stmt->bindParam(":id",   $data["id"],   PDO::PARAM_INT);
		$stmt->bindParam(":name", $data["name"], PDO::PARAM_STR);
		$stmt->bindParam(":type", $data["type"], PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

	public static function mdlDeleteAccount(int $id): string {

		return Scope::deleteById('accounts', $id) ? "ok" : "error";

	}

	/**
	 * Is the account referenced by any posted journal line?
	 * @param int $id
	 * @return bool
	 */
	public static function mdlAccountInUse(int $id): bool {

		$stmt = Connection::connect()->prepare("SELECT COUNT(*) AS n FROM journal_lines WHERE idAccount = :id AND idOrganization = " . (int)Tenant::id() . "");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
		$row = $stmt->fetch();

		return (int)($row["n"] ?? 0) > 0;

	}

	/**
	 * Does an account with this code already exist (for uniqueness checks)?
	 * @param string $code
	 * @return bool
	 */
	public static function mdlCodeExists(string $code): bool {

		$stmt = Connection::connect()->prepare("SELECT COUNT(*) AS n FROM accounts WHERE code = :code AND idOrganization = " . (int)Tenant::id() . "");
		$stmt->bindParam(":code", $code, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();

		return (int)($row["n"] ?? 0) > 0;

	}

	/*=============================================
	POST A JOURNAL ENTRY (header + balanced lines)
	=============================================*/

	/**
	 * @param array $entry  entryDate, reference, sourceType, sourceId, description, createdBy
	 * @param array $lines  list of ['code' => '1100', 'debit' => 0.00, 'credit' => 0.00]
	 * @return string new journal entry id, or "error"
	 */
	public static function mdlPostEntry(array $entry, array $lines): string {

		$link = Connection::connect();

		try {

			// Depth-guarded: nests safely inside an outer recalc transaction.
			Connection::begin();

			$stmt = $link->prepare(
				"INSERT INTO journal_entries
				   (entryDate, reference, sourceType, sourceId, description, createdBy, idOrganization)
				 VALUES
				   (:entryDate, :reference, :sourceType, :sourceId, :description, :createdBy, " . (int)Tenant::id() . ")"
			);

			$stmt->bindParam(":entryDate",   $entry["entryDate"],   PDO::PARAM_STR);
			$stmt->bindParam(":reference",   $entry["reference"],   PDO::PARAM_STR);
			$stmt->bindParam(":sourceType",  $entry["sourceType"],  PDO::PARAM_STR);
			$stmt->bindParam(":sourceId",    $entry["sourceId"],    PDO::PARAM_INT);
			$stmt->bindParam(":description", $entry["description"], PDO::PARAM_STR);
			$stmt->bindParam(":createdBy",   $entry["createdBy"],   PDO::PARAM_INT);
			$stmt->execute();

			$entryId = (int)$link->lastInsertId();

			$lineStmt = $link->prepare(
				"INSERT INTO journal_lines (idJournalEntry, idAccount, debit, credit, idOrganization)
				 VALUES (:idJournalEntry, :idAccount, :debit, :credit, " . (int)Tenant::id() . ")"
			);

			foreach ($lines as $line) {

				$accountId = self::mdlAccountIdByCode($line["code"]);
				if ($accountId === null) {
					continue; // account not seeded; skip rather than crash
				}

				$debit  = number_format((float)($line["debit"]  ?? 0), 2, '.', '');
				$credit = number_format((float)($line["credit"] ?? 0), 2, '.', '');

				$lineStmt->bindParam(":idJournalEntry", $entryId,   PDO::PARAM_INT);
				$lineStmt->bindParam(":idAccount",      $accountId, PDO::PARAM_INT);
				$lineStmt->bindParam(":debit",          $debit,     PDO::PARAM_STR);
				$lineStmt->bindParam(":credit",         $credit,    PDO::PARAM_STR);
				$lineStmt->execute();

			}

			Connection::commit();

			return (string)$entryId;

		} catch (Exception) {

			Connection::rollBack();

			return "error";

		}

	}

	/*=============================================
	DELETE ALL ENTRIES FOR A SOURCE (e.g. an invoice or a payment)
	Used to re-post cleanly when totals/amounts change.
	=============================================*/

	/**
	 * @param string $sourceType  invoice | payment
	 * @param int    $sourceId
	 * @return void
	 */
	public static function mdlDeleteEntriesBySource(string $sourceType, int $sourceId): void {

		$link = Connection::connect();

		// Collect entry ids first so we can clear their lines.
		$find = $link->prepare(
			"SELECT id FROM journal_entries WHERE sourceType = :sourceType AND sourceId = :sourceId AND idOrganization = " . (int)Tenant::id() . ""
		);
		$find->bindParam(":sourceType", $sourceType, PDO::PARAM_STR);
		$find->bindParam(":sourceId",   $sourceId,   PDO::PARAM_INT);
		$find->execute();
		$entries = $find->fetchAll();

		foreach ($entries as $row) {
			$entryId = (int)$row["id"];
			$delLines = $link->prepare("DELETE FROM journal_lines WHERE idJournalEntry = :id AND idOrganization = " . (int)Tenant::id() . "");
			$delLines->bindParam(":id", $entryId, PDO::PARAM_INT);
			$delLines->execute();
		}

		$delEntry = $link->prepare(
			"DELETE FROM journal_entries WHERE sourceType = :sourceType AND sourceId = :sourceId AND idOrganization = " . (int)Tenant::id() . ""
		);
		$delEntry->bindParam(":sourceType", $sourceType, PDO::PARAM_STR);
		$delEntry->bindParam(":sourceId",   $sourceId,   PDO::PARAM_INT);
		$delEntry->execute();

	}

	/*=============================================
	TRIAL BALANCE — debit/credit totals per account
	=============================================*/

	/**
	 * @return array list of ['code','name','type','debit','credit','balance']
	 */
	public static function mdlTrialBalance(): array {

		$stmt = Connection::connect()->prepare(
			"SELECT a.code, a.name, a.type,
			        COALESCE(SUM(l.debit), 0)  AS debit,
			        COALESCE(SUM(l.credit), 0) AS credit
			   FROM accounts a
			   LEFT JOIN journal_lines l ON l.idAccount = a.id
			  WHERE a.idOrganization = " . (int)Tenant::id() . " GROUP BY a.id, a.code, a.name, a.type
			  ORDER BY a.code ASC"
		);
		$stmt->execute();

		$rows = $stmt->fetchAll() ?: [];

		foreach ($rows as &$row) {
			// Asset/expense accounts carry a debit balance; others a credit balance.
			$debit  = (float)$row["debit"];
			$credit = (float)$row["credit"];
			$row["balance"] = in_array($row["type"], ["asset", "expense"], true)
				? $debit - $credit
				: $credit - $debit;
		}

		return $rows;

	}

	/*=============================================
	RECENT JOURNAL ENTRIES (with their lines) — for the ledger view
	=============================================*/

	/**
	 * @param int $limit
	 * @return array
	 */
	public static function mdlRecentEntries(int $limit = 50): array {

		$link = Connection::connect();

		$stmt = $link->prepare(
			"SELECT * FROM journal_entries WHERE idOrganization = " . (int)Tenant::id() . " ORDER BY entryDate DESC, id DESC LIMIT :limit"
		);
		$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
		$stmt->execute();
		$entries = $stmt->fetchAll() ?: [];

		$lineStmt = $link->prepare(
			"SELECT l.debit, l.credit, a.code, a.name
			   FROM journal_lines l
			   JOIN accounts a ON a.id = l.idAccount
			  WHERE l.idJournalEntry = :id AND l.idOrganization = " . (int)Tenant::id() . "
			  ORDER BY l.debit DESC"
		);

		foreach ($entries as &$entry) {
			$lineStmt->bindValue(":id", (int)$entry["id"], PDO::PARAM_INT);
			$lineStmt->execute();
			$entry["lines"] = $lineStmt->fetchAll() ?: [];
		}

		return $entries;

	}

}
