<?php

// Tenant context is needed wherever models run (web requests and standalone
// AJAX endpoints), so load it alongside the DB layer.
require_once __DIR__ . '/../helpers/tenant.php';
require_once __DIR__ . '/../helpers/permission.php';
require_once __DIR__ . '/../helpers/tenant_query.php';
require_once __DIR__ . '/../helpers/db_camelcase.php';

class Connection{

	/** @var PDO|null Single shared connection for the request (unit of work). */
	static private $instance = null;

	/** @var int Nesting depth so nested begin/commit behave like one transaction. */
	static private $txDepth = 0;

	/** @var bool Set when an inner scope rolls back so the outer commit aborts. */
	static private $txRolledBack = false;

	/** @var string|null Active PDO driver: 'mysql' | 'pgsql'. */
	static private $driver = null;

	static public function connect(){

		// Reuse one PDO per request so transactions can span multiple model
		// calls. Real accounting systems use exactly one unit-of-work here.
		if (self::$instance instanceof PDO) {
			return self::$instance;
		}

		$env = self::loadEnv();

		// 'mysql' (default, local/XAMPP) or 'pgsql' (Supabase/Postgres production).
		self::$driver = strtolower($env['DB_CONNECTION'] ?? 'mysql');

		// Accept both the canonical keys (DB_NAME/DB_USER/DB_PASS) and the
		// Laravel-style aliases (DB_DATABASE/DB_USERNAME/DB_PASSWORD) so a .env
		// written either way connects instead of silently falling back to local.
		$host    = $env['DB_HOST'] ?? 'localhost';
		$db      = $env['DB_NAME'] ?? $env['DB_DATABASE'] ?? 'smootherp';
		$user    = $env['DB_USER'] ?? $env['DB_USERNAME'] ?? 'root';
		$pass    = $env['DB_PASS'] ?? $env['DB_PASSWORD'] ?? '';
		$charset = $env['DB_CHARSET'] ?? 'utf8';
		$port    = $env['DB_PORT'] ?? (self::$driver === 'pgsql' ? '5432' : '3306');

		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];

		if (self::$driver === 'pgsql') {
			// Supabase requires TLS. Result keys are remapped to camelCase by
			// CamelCaseStatement (Postgres lower-cases identifiers).
			$sslmode = $env['DB_SSLMODE'] ?? 'require';
			$dsn = "pgsql:host={$host};port={$port};dbname={$db};sslmode={$sslmode}";
			$options[PDO::ATTR_STATEMENT_CLASS] = [CamelCaseStatement::class, []];
		} else {
			$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$charset}";
		}

		$link = new PDO($dsn, $user, $pass, $options);

		self::$instance = $link;

		return $link;
	}

	/** Active driver for the current connection ('mysql' | 'pgsql'). */
	static public function driver(): string
	{
		if (self::$driver === null) { self::connect(); }
		return self::$driver ?? 'mysql';
	}

	/*=============================================
	CROSS-DRIVER SQL HELPERS (MySQL <-> PostgreSQL)
	=============================================*/

	/** "INSERT IGNORE INTO" on MySQL; plain "INSERT INTO" on Postgres (pair with onConflictDoNothing()). */
	static public function insertIgnoreInto(): string
	{
		return self::driver() === 'pgsql' ? 'INSERT INTO' : 'INSERT IGNORE INTO';
	}

	/** Trailing " ON CONFLICT DO NOTHING" on Postgres; empty on MySQL. */
	static public function onConflictDoNothing(): string
	{
		return self::driver() === 'pgsql' ? ' ON CONFLICT DO NOTHING' : '';
	}

	/** Integer cast type for "CAST(x AS ...)": UNSIGNED on MySQL, BIGINT on Postgres. */
	static public function intCast(): string
	{
		return self::driver() === 'pgsql' ? 'BIGINT' : 'UNSIGNED';
	}

	/**
	 * Quote a table/column identifier for the active driver: backticks on MySQL,
	 * double quotes on Postgres. Needed for reserved words such as the `user`
	 * column (MySQL backticks are a syntax error on Postgres, and unquoted
	 * `user` on Postgres resolves to the session-user function, not the column).
	 */
	static public function quoteIdent(string $name): string
	{
		// Postgres folds unquoted identifiers to lower-case, and the schema was
		// migrated with pgloader's "downcase identifiers" (so every column is
		// lower-case). Lower-case the name before quoting so a reserved or
		// mixed-case identifier (e.g. `user`, `lastLogin`) still matches the
		// actual column instead of becoming a case-sensitive miss.
		return self::driver() === 'pgsql'
			? '"' . str_replace('"', '""', strtolower($name)) . '"'
			: '`' . str_replace('`', '``', $name) . '`';
	}

	/*=============================================
	UNIT-OF-WORK TRANSACTION HELPERS
	=============================================
	MySQL/PDO has no true nested transactions, so we emulate them with a
	depth counter: only the outermost begin/commit actually hits the DB.
	This lets a high-level operation (e.g. recalc) wrap several model calls
	that may each also open a transaction, and still commit atomically.
	*/

	static public function begin(): void {
		$link = self::connect();
		if (self::$txDepth === 0) {
			$link->beginTransaction();
			self::$txRolledBack = false;
		}
		self::$txDepth++;
	}

	static public function commit(): void {
		$link = self::connect();
		if (self::$txDepth <= 0) {
			return;
		}
		self::$txDepth--;
		if (self::$txDepth === 0 && $link->inTransaction()) {
			if (self::$txRolledBack) {
				$link->rollBack();
			} else {
				$link->commit();
			}
		}
	}

	static public function rollBack(): void {
		$link = self::connect();
		// Mark so the outer commit aborts; only physically roll back at depth 1.
		self::$txRolledBack = true;
		if (self::$txDepth <= 1 && $link->inTransaction()) {
			$link->rollBack();
			self::$txDepth = 0;
			self::$txRolledBack = false;
		} elseif (self::$txDepth > 1) {
			self::$txDepth--;
		}
	}

	static private function loadEnv(){
		// .env lives in config/ (moved for security); fall back to project root.
		$root = dirname(__DIR__);
		$path = is_file($root . '/config/.env') ? $root . '/config/.env' : $root . '/.env';
		$vars = [];

		if (!is_file($path)) {
			return $vars;
		}

		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '' || str_starts_with($line, '#')) {
				continue;
			}

			if (!str_contains($line, '=')) {
				continue;
			}

			[$key, $value] = explode('=', $line, 2);
			$key = trim($key);
			$value = trim($value);

			if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
				$value = $matches[2];
			}

			$vars[$key] = $value;
		}

		return $vars;
	}

}