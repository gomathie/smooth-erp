<?php

// Tenant context is needed wherever models run (web requests and standalone
// AJAX endpoints), so load it alongside the DB layer.
require_once __DIR__ . '/../helpers/tenant.php';
require_once __DIR__ . '/../helpers/permission.php';

class Connection{

	/** @var PDO|null Single shared connection for the request (unit of work). */
	static private $instance = null;

	/** @var int Nesting depth so nested begin/commit behave like one transaction. */
	static private $txDepth = 0;

	/** @var bool Set when an inner scope rolls back so the outer commit aborts. */
	static private $txRolledBack = false;

	static public function connect(){

		// Reuse one PDO per request so transactions can span multiple model
		// calls. Real accounting systems use exactly one unit-of-work here.
		if (self::$instance instanceof PDO) {
			return self::$instance;
		}

		$env = self::loadEnv();

		$host = $env['DB_HOST'] ?? 'localhost';
		$db = $env['DB_NAME'] ?? 'smootherp';
		$user = $env['DB_USER'] ?? 'root';
		$pass = $env['DB_PASS'] ?? '';
		$charset = $env['DB_CHARSET'] ?? 'utf8';

		$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

		$link = new PDO($dsn, $user, $pass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
		]);

		self::$instance = $link;

		return $link;
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
		$path = dirname(__DIR__) . '/.env';
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