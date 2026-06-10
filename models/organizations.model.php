<?php

require_once 'connection.php';

/**
 * Organizations (tenants). Managed by Super Admins only — these methods are
 * NOT org-scoped (a Super Admin operates across all tenants).
 */
class ModelOrganizations {

	/*=============================================
	LIST / GET
	=============================================*/

	public static function mdlShowOrganizations(): array {
		$stmt = Connection::connect()->prepare("SELECT * FROM organizations ORDER BY id ASC");
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	public static function mdlGetOrganization(int $id) {
		$stmt = Connection::connect()->prepare("SELECT * FROM organizations WHERE id = :id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch();
	}

	public static function mdlCodeExists(string $code, int $excludeId = 0): bool {
		$stmt = Connection::connect()->prepare("SELECT COUNT(*) n FROM organizations WHERE code = :code AND id <> :ex");
		$stmt->bindParam(":code", $code, PDO::PARAM_STR);
		$stmt->bindParam(":ex", $excludeId, PDO::PARAM_INT);
		$stmt->execute();
		return (int)($stmt->fetch()["n"] ?? 0) > 0;
	}

	/*=============================================
	CREATE / UPDATE
	=============================================*/

	/**
	 * @return string new org id, or "error"
	 */
	public static function mdlAddOrganization(array $data): string {
		$link = Connection::connect();
		$stmt = $link->prepare(
			"INSERT INTO organizations (name, code, email, phone, address, baseCurrency, status)
			 VALUES (:name, :code, :email, :phone, :address, :baseCurrency, 1)"
		);
		$stmt->bindParam(":name",         $data["name"],         PDO::PARAM_STR);
		$stmt->bindParam(":code",         $data["code"],         PDO::PARAM_STR);
		$stmt->bindParam(":email",        $data["email"],        PDO::PARAM_STR);
		$stmt->bindParam(":phone",        $data["phone"],        PDO::PARAM_STR);
		$stmt->bindParam(":address",      $data["address"],      PDO::PARAM_STR);
		$stmt->bindParam(":baseCurrency", $data["baseCurrency"], PDO::PARAM_STR);

		if ($stmt->execute()) {
			return (string)$link->lastInsertId();
		}
		return "error";
	}

	public static function mdlEditOrganization(array $data): string {
		$stmt = Connection::connect()->prepare(
			"UPDATE organizations SET name = :name, email = :email, phone = :phone,
			    address = :address, baseCurrency = :baseCurrency, status = :status
			  WHERE id = :id"
		);
		$stmt->bindParam(":id",           $data["id"],           PDO::PARAM_INT);
		$stmt->bindParam(":name",         $data["name"],         PDO::PARAM_STR);
		$stmt->bindParam(":email",        $data["email"],        PDO::PARAM_STR);
		$stmt->bindParam(":phone",        $data["phone"],        PDO::PARAM_STR);
		$stmt->bindParam(":address",      $data["address"],      PDO::PARAM_STR);
		$stmt->bindParam(":baseCurrency", $data["baseCurrency"], PDO::PARAM_STR);
		$stmt->bindParam(":status",       $data["status"],       PDO::PARAM_INT);
		return $stmt->execute() ? "ok" : "error";
	}

	/*=============================================
	UPDATE COMPANY PROFILE / BRANDING (org admin, under Settings)
	=============================================*/

	public static function mdlUpdateProfile(int $idOrg, array $d): string {
		$stmt = Connection::connect()->prepare(
			"UPDATE organizations SET
			   name = :name, industry = :industry, email = :email, phone = :phone, fax = :fax,
			   website = :website, address = :address, city = :city, region = :region,
			   postalCode = :postalCode, country = :country, themeColor = :themeColor
			 WHERE id = :id"
		);
		$stmt->bindValue(":id",         $idOrg,            PDO::PARAM_INT);
		$stmt->bindValue(":name",       $d["name"]);
		$stmt->bindValue(":industry",   $d["industry"]);
		$stmt->bindValue(":email",      $d["email"]);
		$stmt->bindValue(":phone",      $d["phone"]);
		$stmt->bindValue(":fax",        $d["fax"]);
		$stmt->bindValue(":website",    $d["website"]);
		$stmt->bindValue(":address",    $d["address"]);
		$stmt->bindValue(":city",       $d["city"]);
		$stmt->bindValue(":region",     $d["region"]);
		$stmt->bindValue(":postalCode", $d["postalCode"]);
		$stmt->bindValue(":country",    $d["country"]);
		$stmt->bindValue(":themeColor", $d["themeColor"]);
		return $stmt->execute() ? "ok" : "error";
	}

	public static function mdlSetLogo(int $idOrg, string $path): string {
		$stmt = Connection::connect()->prepare("UPDATE organizations SET logo = :logo WHERE id = :id");
		$stmt->bindValue(":logo", $path, PDO::PARAM_STR);
		$stmt->bindValue(":id",   $idOrg, PDO::PARAM_INT);
		return $stmt->execute() ? "ok" : "error";
	}

	/*=============================================
	SEED A NEW ORG'S CHART OF ACCOUNTS (clone the default org's chart)
	=============================================*/

	public static function mdlSeedAccounts(int $idOrg): void {
		$stmt = Connection::connect()->prepare(
			"INSERT INTO accounts (code, name, type, isSystem, idOrganization)
			 SELECT code, name, type, isSystem, :idOrg FROM accounts WHERE idOrganization = 1"
		);
		$stmt->bindParam(":idOrg", $idOrg, PDO::PARAM_INT);
		$stmt->execute();
	}

	/*=============================================
	ACTIVATE THE BASE CURRENCY FOR A NEW ORG
	=============================================*/

	public static function mdlSeedBaseCurrency(int $idOrg, string $currency): void {
		$stmt = Connection::connect()->prepare(
			"INSERT IGNORE INTO organization_currencies (idOrganization, currencyCode, isBase) VALUES (:idOrg, :cur, 1)"
		);
		$stmt->bindParam(":idOrg", $idOrg,    PDO::PARAM_INT);
		$stmt->bindParam(":cur",   $currency, PDO::PARAM_STR);
		$stmt->execute();
	}

	/*=============================================
	CREATE AN ORG'S ADMIN USER (bypasses tenant scoping by design)
	=============================================*/

	public static function mdlCreateOrgAdmin(int $idOrg, array $data): string {
		$stmt = Connection::connect()->prepare(
			"INSERT INTO users (name, user, password, profile, photo, email, phone, status, lastLogin, idOrganization)
			 VALUES (:name, :user, :password, 'Administrator', '', :email, :phone, 1, NOW(), :idOrg)"
		);
		$stmt->bindParam(":name",     $data["name"],     PDO::PARAM_STR);
		$stmt->bindParam(":user",     $data["user"],     PDO::PARAM_STR);
		$stmt->bindParam(":password", $data["password"], PDO::PARAM_STR);
		$stmt->bindParam(":email",    $data["email"],    PDO::PARAM_STR);
		$stmt->bindParam(":phone",    $data["phone"],    PDO::PARAM_STR);
		$stmt->bindParam(":idOrg",    $idOrg,            PDO::PARAM_INT);
		return $stmt->execute() ? "ok" : "error";
	}

	/** Global username uniqueness check (usernames are unique across all orgs). */
	public static function mdlUsernameExists(string $user): bool {
		$stmt = Connection::connect()->prepare("SELECT COUNT(*) n FROM users WHERE user = :u");
		$stmt->bindParam(":u", $user, PDO::PARAM_STR);
		$stmt->execute();
		return (int)($stmt->fetch()["n"] ?? 0) > 0;
	}

	/*=============================================
	ORG CURRENCY ACTIVATION
	=============================================*/

	public static function mdlOrgCurrencies(int $idOrg): array {
		$stmt = Connection::connect()->prepare(
			"SELECT oc.currencyCode AS code, oc.isBase, c.name, c.symbol
			   FROM organization_currencies oc
			   JOIN currencies c ON c.code = oc.currencyCode
			  WHERE oc.idOrganization = :o ORDER BY oc.isBase DESC, oc.currencyCode ASC"
		);
		$stmt->bindParam(":o", $idOrg, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	public static function mdlActivateCurrency(int $idOrg, string $code): string {
		$stmt = Connection::connect()->prepare(
			"INSERT IGNORE INTO organization_currencies (idOrganization, currencyCode, isBase) VALUES (:o, :c, 0)"
		);
		$stmt->bindParam(":o", $idOrg, PDO::PARAM_INT);
		$stmt->bindParam(":c", $code, PDO::PARAM_STR);
		return $stmt->execute() ? "ok" : "error";
	}

	public static function mdlDeactivateCurrency(int $idOrg, string $code): string {
		// The base currency cannot be deactivated.
		$stmt = Connection::connect()->prepare(
			"DELETE FROM organization_currencies WHERE idOrganization = :o AND currencyCode = :c AND isBase = 0"
		);
		$stmt->bindParam(":o", $idOrg, PDO::PARAM_INT);
		$stmt->bindParam(":c", $code, PDO::PARAM_STR);
		return $stmt->execute() ? "ok" : "error";
	}

	public static function mdlSetBaseCurrency(int $idOrg, string $code): string {
		$link = Connection::connect();
		// Ensure it's activated, clear other base flags, set this as base, sync org row.
		$link->prepare("INSERT IGNORE INTO organization_currencies (idOrganization, currencyCode, isBase) VALUES (:o,:c,0)")
		     ->execute([":o"=>$idOrg, ":c"=>$code]);
		$link->prepare("UPDATE organization_currencies SET isBase = 0 WHERE idOrganization = :o")->execute([":o"=>$idOrg]);
		$link->prepare("UPDATE organization_currencies SET isBase = 1 WHERE idOrganization = :o AND currencyCode = :c")->execute([":o"=>$idOrg, ":c"=>$code]);
		$link->prepare("UPDATE organizations SET baseCurrency = :c WHERE id = :o")->execute([":c"=>$code, ":o"=>$idOrg]);
		return "ok";
	}

	/** Count of users / quick stats for an org (for the panel). */
	public static function mdlUserCount(int $idOrg): int {
		$stmt = Connection::connect()->prepare("SELECT COUNT(*) n FROM users WHERE idOrganization = :id");
		$stmt->bindParam(":id", $idOrg, PDO::PARAM_INT);
		$stmt->execute();
		return (int)($stmt->fetch()["n"] ?? 0);
	}

}
