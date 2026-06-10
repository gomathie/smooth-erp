<?php

/**
 * Tenant-scoped query helper (centralised multi-tenancy enforcement).
 *
 * Instead of hand-writing `... AND idOrganization = Tenant::id()` in every model
 * method (easy to forget — see tests/tenant_scope_guard.php), the common scoped
 * reads/deletes go through Scope, which ALWAYS injects the current org filter by
 * construction. Table and column names are whitelisted/validated, so this also
 * removes the injection risk of interpolating a dynamic column into SQL.
 *
 * Custom multi-column INSERT/UPDATE and reporting joins stay as raw SQL in the
 * models (still org-scoped, and covered by the regression guard); Scope handles
 * the repetitive boilerplate.
 */
class Scope {

	/** Tenant-owned tables this helper may touch. */
	private const TABLES = [
		'sales', 'products', 'categories', 'customers', 'invoices', 'quotations',
		'expenses', 'payments_received', 'accounts', 'journal_entries',
		'journal_lines', 'stock_movements', 'invoice_activity_log',
		'organization_currencies',
	];

	private static function table(string $t): string {
		if (!in_array($t, self::TABLES, true)) {
			throw new InvalidArgumentException("Scope: unknown/!tenant table '{$t}'");
		}
		return $t;
	}

	private static function col(string $c): string {
		if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $c)) {
			throw new InvalidArgumentException("Scope: invalid column '{$c}'");
		}
		return $c;
	}

	/** Only allow a safe ORDER BY expression (code-supplied, never user input). */
	private static function orderBy(string $o): string {
		if ($o === '') { return ''; }
		if (!preg_match('/^[A-Za-z0-9_ ,.]+$/', $o)) {
			throw new InvalidArgumentException("Scope: invalid ORDER BY '{$o}'");
		}
		return ' ORDER BY ' . $o;
	}

	/** All rows for the current org. */
	public static function all(string $table, string $orderBy = ''): array {
		$sql = "SELECT * FROM `" . self::table($table) . "` WHERE idOrganization = :__org" . self::orderBy($orderBy);
		$st = Connection::connect()->prepare($sql);
		$st->bindValue(':__org', Tenant::id(), PDO::PARAM_INT);
		$st->execute();
		return $st->fetchAll() ?: [];
	}

	/** First row where column = value, scoped to the current org (or false). */
	public static function firstBy(string $table, string $column, $value, string $orderBy = '') {
		$sql = "SELECT * FROM `" . self::table($table) . "` WHERE `" . self::col($column) . "` = :val AND idOrganization = :__org" . self::orderBy($orderBy) . " LIMIT 1";
		$st = Connection::connect()->prepare($sql);
		$st->bindValue(':val', $value);
		$st->bindValue(':__org', Tenant::id(), PDO::PARAM_INT);
		$st->execute();
		return $st->fetch();
	}

	/** All rows where column = value, scoped to the current org. */
	public static function rowsBy(string $table, string $column, $value, string $orderBy = ''): array {
		$sql = "SELECT * FROM `" . self::table($table) . "` WHERE `" . self::col($column) . "` = :val AND idOrganization = :__org" . self::orderBy($orderBy);
		$st = Connection::connect()->prepare($sql);
		$st->bindValue(':val', $value);
		$st->bindValue(':__org', Tenant::id(), PDO::PARAM_INT);
		$st->execute();
		return $st->fetchAll() ?: [];
	}

	/** A single row by primary key id, scoped (or false). */
	public static function find(string $table, int $id) {
		return self::firstBy($table, 'id', $id);
	}

	/** Delete a row by id within the current org. Returns true on success. */
	public static function deleteById(string $table, int $id): bool {
		$st = Connection::connect()->prepare("DELETE FROM `" . self::table($table) . "` WHERE id = :id AND idOrganization = :__org");
		$st->bindValue(':id', $id, PDO::PARAM_INT);
		$st->bindValue(':__org', Tenant::id(), PDO::PARAM_INT);
		return $st->execute();
	}

	/** COUNT(*) for the current org, with an optional extra (parameterised) WHERE. */
	public static function count(string $table, string $extraWhere = '', array $params = []): int {
		$sql = "SELECT COUNT(*) AS n FROM `" . self::table($table) . "` WHERE idOrganization = :__org";
		if ($extraWhere !== '') { $sql .= " AND (" . $extraWhere . ")"; }
		$st = Connection::connect()->prepare($sql);
		$st->bindValue(':__org', Tenant::id(), PDO::PARAM_INT);
		foreach ($params as $k => $v) { $st->bindValue($k, $v); }
		$st->execute();
		return (int)($st->fetch()['n'] ?? 0);
	}
}
