<?php

require_once __DIR__ . '/../models/connection.php';

/**
 * Multi-currency (Lite) helper. Symbols come from the global `currencies`
 * reference table; activated currencies and the base are per organization.
 * No FX conversion — amounts are stored and shown in the currency entered.
 */
class Currency {

	/** @var array<string,string>|null code => symbol cache */
	private static $symbols = null;

	/** Symbol for a currency code (e.g. "USD" => "$"). Falls back to "CODE ". */
	public static function symbol(string $code): string {
		if (self::$symbols === null) {
			self::$symbols = [];
			try {
				$rows = Connection::connect()->query("SELECT code, symbol FROM currencies")->fetchAll();
				foreach ($rows as $r) { self::$symbols[$r["code"]] = $r["symbol"]; }
			} catch (Exception) { /* table missing — fall through */ }
		}
		$code = $code !== "" ? $code : self::base();
		return self::$symbols[$code] ?? ($code . " ");
	}

	/** Format an amount with its currency symbol, e.g. "$ 1,200.00". */
	public static function format($amount, string $code): string {
		return self::symbol($code) . " " . number_format((float)$amount, 2);
	}

	/** Base currency for the current org (loaded into the session at login). */
	public static function base(): string {
		return $_SESSION["baseCurrency"] ?? "USD";
	}

	/** Is the multi-currency feature enabled for the current org? */
	public static function isEnabled(): bool {
		return class_exists("ModelSettings") && ModelSettings::mdlGet("multicurrency_enabled", "0") === "1";
	}

	/** Currencies activated for the current org (base first). */
	public static function activeForOrg(): array {
		try {
			$stmt = Connection::connect()->prepare(
				"SELECT oc.currencyCode AS code, oc.isBase, c.name, c.symbol
				   FROM organization_currencies oc
				   JOIN currencies c ON c.code = oc.currencyCode
				  WHERE oc.idOrganization = :o
				  ORDER BY oc.isBase DESC, oc.currencyCode ASC"
			);
			$stmt->bindValue(":o", Tenant::id(), PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll() ?: [];
		} catch (Exception) {
			return [];
		}
	}

}
