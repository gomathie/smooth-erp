<?php

class Connection{

	static public function connect(){

		$env = self::loadEnv();

		$host = $env['DB_HOST'] ?? 'localhost';
		$db = $env['DB_NAME'] ?? 'posystem';
		$user = $env['DB_USER'] ?? 'root';
		$pass = $env['DB_PASS'] ?? '';
		$charset = $env['DB_CHARSET'] ?? 'utf8';

		$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

		$link = new PDO($dsn, $user, $pass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
		]);

		return $link;
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