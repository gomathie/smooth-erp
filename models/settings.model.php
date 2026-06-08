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
			$stmt = Connection::connect()->prepare("SELECT settingValue FROM settings WHERE settingKey = :k LIMIT 1");
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
	SET A SETTING (upsert)
	=============================================*/

	/**
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
	public static function mdlSet(string $key, string $value): string {

		$stmt = Connection::connect()->prepare(
			"INSERT INTO settings (settingKey, settingValue) VALUES (:k, :v)
			 ON DUPLICATE KEY UPDATE settingValue = :v2"
		);
		$stmt->bindParam(":k",  $key,   PDO::PARAM_STR);
		$stmt->bindParam(":v",  $value, PDO::PARAM_STR);
		$stmt->bindParam(":v2", $value, PDO::PARAM_STR);

		return $stmt->execute() ? "ok" : "error";

	}

}
