<?php

require_once 'connection.php';

/**
 * Simple key/value application settings.
 */
class ModelSettings {

	/*=============================================
	GET A SETTING (with default)
	=============================================*/

	/**
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public static function mdlGet(string $key, string $default = ""): string {

		try {
			$stmt = Connection::connect()->prepare("SELECT settingValue FROM settings WHERE settingKey = :k AND idOrganization = " . (int)Tenant::id() . " LIMIT 1");
			$stmt->bindParam(":k", $key, PDO::PARAM_STR);
			$stmt->execute();
			$row = $stmt->fetch();
		} catch (Exception) {
			// settings table not migrated yet — fall back to default
			return $default;
		}

		return $row ? (string)$row["settingValue"] : $default;

	}

	/*=============================================
	GET / SET A SETTING FOR A SPECIFIC ORG (Super Admin use)
	=============================================*/

	public static function mdlGetForOrg(int $idOrg, string $key, string $default = ""): string {
		try {
			$stmt = Connection::connect()->prepare("SELECT settingValue FROM settings WHERE settingKey = :k AND idOrganization = :o LIMIT 1");
			$stmt->bindParam(":k", $key, PDO::PARAM_STR);
			$stmt->bindParam(":o", $idOrg, PDO::PARAM_INT);
			$stmt->execute();
			$row = $stmt->fetch();
		} catch (Exception) {
			return $default;
		}
		return $row ? (string)$row["settingValue"] : $default;
	}

	public static function mdlSetForOrg(int $idOrg, string $key, string $value): string {
		$upsert = Connection::driver() === 'pgsql'
			? "ON CONFLICT (idOrganization, settingKey) DO UPDATE SET settingValue = :v2"
			: "ON DUPLICATE KEY UPDATE settingValue = :v2";
		$stmt = Connection::connect()->prepare(
			"INSERT INTO settings (settingKey, settingValue, idOrganization) VALUES (:k, :v, :o) " . $upsert
		);
		$stmt->bindParam(":k",  $key,   PDO::PARAM_STR);
		$stmt->bindParam(":v",  $value, PDO::PARAM_STR);
		$stmt->bindParam(":o",  $idOrg, PDO::PARAM_INT);
		$stmt->bindParam(":v2", $value, PDO::PARAM_STR);
		return $stmt->execute() ? "ok" : "error";
	}

	/*=============================================
	SET A SETTING (upsert)
	=============================================*/

	/**
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
	public static function mdlSet(string $key, string $value): string {

		$upsert = Connection::driver() === 'pgsql'
			? "ON CONFLICT (idOrganization, settingKey) DO UPDATE SET settingValue = :v2"
			: "ON DUPLICATE KEY UPDATE settingValue = :v2";
		$stmt = Connection::connect()->prepare(
			"INSERT INTO settings (settingKey, settingValue, idOrganization) VALUES (:k, :v, " . (int)Tenant::id() . ") " . $upsert
		);
		$stmt->bindParam(":k",  $key,   PDO::PARAM_STR);
		$stmt->bindParam(":v",  $value, PDO::PARAM_STR);
		$stmt->bindParam(":v2", $value, PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

}
