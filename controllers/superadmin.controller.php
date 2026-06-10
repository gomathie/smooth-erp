<?php

/**
 * Super Admin operations: onboard/manage organizations, toggle their features,
 * and "enter" an org to operate it as its administrator. All actions require a
 * platform-level Super Admin (Tenant::isSuperAdmin()).
 */
class ControllerSuperAdmin {

	private const FEATURES = ['accounting_enabled', 'multicurrency_enabled'];

	private static function guard(): bool {
		return Tenant::isSuperAdmin();
	}

	/*=============================================
	LIST
	=============================================*/

	public static function ctrShowOrganizations(): array {
		return ModelOrganizations::mdlShowOrganizations();
	}

	public static function ctrOrgFeature(int $idOrg, string $key): bool {
		return ModelSettings::mdlGetForOrg($idOrg, $key, $key === 'accounting_enabled' ? '1' : '0') === '1';
	}

	public static function ctrUserCount(int $idOrg): int {
		return ModelOrganizations::mdlUserCount($idOrg);
	}

	public static function ctrOrgCount(): int {
		return ModelOrganizations::mdlOrgCount();
	}

	/*=============================================
	PLATFORM LIMITS (global cap on number of organizations)
	=============================================*/

	/** Max number of organizations allowed on the platform (default 3). */
	public static function ctrMaxOrganizations(): int {
		return (int) ModelSettings::mdlGetForOrg(0, "max_organizations", "3");
	}

	public static function ctrSetMaxOrganizations(): void {
		if (!isset($_POST["maxOrganizations"]) || !self::guard()) {
			return;
		}
		$max = max(1, (int)$_POST["maxOrganizations"]);
		ModelSettings::mdlSetForOrg(0, "max_organizations", (string)$max);
		self::alert("success", "Maximum organizations set to {$max}.");
	}

	/*=============================================
	CREATE ORGANIZATION (+ chart of accounts, base currency, first admin)
	=============================================*/

	public static function ctrCreateOrganization(): void {

		if (!isset($_POST["newOrgName"]) || !self::guard()) {
			return;
		}

		$code = strtoupper(trim($_POST["newOrgCode"] ?? ""));
		$name = trim($_POST["newOrgName"]);
		$adminUser = trim($_POST["adminUser"] ?? "");
		$adminPass = $_POST["adminPass"] ?? "";

		if ($name === "" || $code === "" || $adminUser === "" || strlen($adminPass) < 4) {
			self::alert("error", "Name, code, admin username and a password (min 4 chars) are required.");
			return;
		}
		if (ModelOrganizations::mdlCodeExists($code)) {
			self::alert("error", "An organization with code {$code} already exists.");
			return;
		}
		if (ModelOrganizations::mdlUsernameExists($adminUser)) {
			self::alert("error", "Username '{$adminUser}' is already taken.");
			return;
		}

		// Enforce the platform-wide cap on number of organizations.
		$maxOrgs = self::ctrMaxOrganizations();
		if (ModelOrganizations::mdlOrgCount() >= $maxOrgs) {
			self::alert("error", "Organization limit reached ({$maxOrgs}). Increase the limit in Platform Limits to add more.");
			return;
		}

		$baseCurrency = strtoupper($_POST["newOrgBaseCurrency"] ?? "USD");
		$maxUsers     = max(1, (int)($_POST["newOrgMaxUsers"] ?? 3));

		Connection::begin();
		try {
			$orgId = ModelOrganizations::mdlAddOrganization([
				"name"         => $name,
				"code"         => $code,
				"email"        => $_POST["newOrgEmail"]   ?? "",
				"phone"        => $_POST["newOrgPhone"]   ?? "",
				"address"      => $_POST["newOrgAddress"] ?? "",
				"baseCurrency" => $baseCurrency,
				"maxUsers"     => $maxUsers,
			]);

			if ($orgId === "error") { Connection::rollBack(); return; }
			$orgId = (int)$orgId;

			ModelOrganizations::mdlSeedAccounts($orgId);
			ModelOrganizations::mdlSeedBaseCurrency($orgId, $baseCurrency);

			ModelOrganizations::mdlCreateOrgAdmin($orgId, [
				"name"     => $_POST["adminName"] ?? $adminUser,
				"user"     => $adminUser,
				"password" => password_hash($adminPass, PASSWORD_DEFAULT),
				"email"    => $_POST["adminEmail"] ?? "",
				"phone"    => $_POST["adminPhone"] ?? "",
			]);

			// Default feature flags for the new org.
			ModelSettings::mdlSetForOrg($orgId, "accounting_enabled", "1");
			ModelSettings::mdlSetForOrg($orgId, "multicurrency_enabled", "0");

			Connection::commit();
		} catch (Exception $e) {
			Connection::rollBack();
			return;
		}

		self::alert("success", "Organization '{$name}' created with admin '{$adminUser}'.");
	}

	/*=============================================
	EDIT ORGANIZATION
	=============================================*/

	public static function ctrEditOrganization(): void {

		if (!isset($_POST["editOrg"]) || !self::guard()) {
			return;
		}

		$id = (int)$_POST["editOrg"];
		$answer = ModelOrganizations::mdlEditOrganization([
			"id"           => $id,
			"name"         => trim($_POST["editOrgName"] ?? ""),
			"email"        => $_POST["editOrgEmail"]   ?? "",
			"phone"        => $_POST["editOrgPhone"]   ?? "",
			"address"      => $_POST["editOrgAddress"] ?? "",
			"baseCurrency" => strtoupper($_POST["editOrgBaseCurrency"] ?? "USD"),
			"status"       => ($_POST["editOrgStatus"] ?? "1") === "1" ? 1 : 0,
			"maxUsers"     => max(1, (int)($_POST["editOrgMaxUsers"] ?? 3)),
		]);

		if ($answer === "ok") {
			self::alert("success", "Organization updated.");
		}
	}

	/*=============================================
	TOGGLE A FEATURE FOR AN ORG
	=============================================*/

	public static function ctrToggleFeature(): void {

		if (!isset($_GET["toggleFeature"], $_GET["org"]) || !self::guard()) {
			return;
		}

		$feature = $_GET["toggleFeature"];
		if (!in_array($feature, self::FEATURES, true)) {
			return;
		}

		$idOrg = (int)$_GET["org"];
		$current = ModelSettings::mdlGetForOrg($idOrg, $feature, "0");
		ModelSettings::mdlSetForOrg($idOrg, $feature, $current === "1" ? "0" : "1");

		echo '<script>window.location = "organizations";</script>';
	}

	/*=============================================
	PER-ORG CURRENCY CONTROL (Super Admin chooses an org's currencies)
	=============================================*/

	public static function ctrAllCurrencies(): array {
		$stmt = Connection::connect()->query("SELECT code, name, symbol FROM currencies ORDER BY code ASC");
		return $stmt ? $stmt->fetchAll() : [];
	}

	public static function ctrOrgCurrencyList(int $idOrg): array {
		return ModelOrganizations::mdlOrgCurrencies($idOrg);
	}

	public static function ctrManageCurrencies(): void {

		if (!self::guard() || !isset($_GET["org"])) {
			return;
		}

		$idOrg = (int)$_GET["org"];

		if (isset($_GET["activate"])) {
			ModelOrganizations::mdlActivateCurrency($idOrg, strtoupper($_GET["activate"]));
			self::redirectCur($idOrg);
		}
		if (isset($_GET["deactivate"])) {
			ModelOrganizations::mdlDeactivateCurrency($idOrg, strtoupper($_GET["deactivate"]));
			self::redirectCur($idOrg);
		}
		if (isset($_GET["setBase"])) {
			ModelOrganizations::mdlSetBaseCurrency($idOrg, strtoupper($_GET["setBase"]));
			self::redirectCur($idOrg);
		}
	}

	private static function redirectCur(int $idOrg): void {
		echo '<script>window.location = "index.php?route=org-currencies&org=' . $idOrg . '";</script>';
	}

	/*=============================================
	ENTER / EXIT AN ORG (operate as its administrator)
	=============================================*/

	public static function ctrEnterOrg(): void {

		if (!isset($_GET["enterOrg"]) || !self::guard()) {
			return;
		}

		$org = ModelOrganizations::mdlGetOrganization((int)$_GET["enterOrg"]);
		if (!is_array($org)) {
			return;
		}

		$_SESSION["enteredOrg"]     = (int)$org["id"];
		$_SESSION["enteredOrgName"] = $org["name"];
		$_SESSION["idOrganization"] = (int)$org["id"];
		$_SESSION["baseCurrency"]   = $org["baseCurrency"];
		// Act as an administrator within the org; isSuperAdmin stays true in session.
		$_SESSION["profile"]     = "Administrator";
		$_SESSION["role"]        = "administrator";
		$_SESSION["permissions"] = Permission::KEYS;

		echo '<script>window.location = "home";</script>';
	}

	public static function ctrExitOrg(): void {

		if (!isset($_GET["exitOrg"]) || !self::guard()) {
			return;
		}

		unset($_SESSION["enteredOrg"], $_SESSION["enteredOrgName"], $_SESSION["baseCurrency"]);
		$_SESSION["idOrganization"] = 0;
		$_SESSION["profile"] = "SuperAdmin";

		echo '<script>window.location = "organizations";</script>';
	}

	/*=============================================
	SUPER ADMIN OWN PROFILE
	=============================================*/

	/** The current Super Admin's own user row. */
	public static function ctrOwnProfile() {
		if (!self::guard()) { return null; }
		return ModelOrganizations::mdlGetUserById((int)($_SESSION["id"] ?? 0));
	}

	public static function ctrUpdateOwnProfile(): void {

		if (!isset($_POST["saProfileSave"]) || !self::guard()) {
			return;
		}

		$id    = (int)($_SESSION["id"] ?? 0);
		$name  = trim($_POST["saName"]  ?? "");
		$email = trim($_POST["saEmail"] ?? "");
		$pass  = $_POST["saPassword"]   ?? "";

		if ($name === "") {
			self::alertStay("error", "Name is required.");
			return;
		}
		if ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			self::alertStay("error", "Please enter a valid email address.");
			return;
		}
		if ($pass !== "" && strlen($pass) < 4) {
			self::alertStay("error", "Password must be at least 4 characters.");
			return;
		}

		// Profile photo: keep the current one unless a new JPEG/PNG is uploaded.
		$photo = $_SESSION["photo"] ?? "";
		if (isset($_FILES["saPhoto"]["tmp_name"]) && !empty($_FILES["saPhoto"]["tmp_name"])) {
			$type = $_FILES["saPhoto"]["type"] ?? "";
			if ($type === "image/jpeg" || $type === "image/png") {
				list($width, $height) = getimagesize($_FILES["saPhoto"]["tmp_name"]);
				$newWidth = 500; $newHeight = 500;
				$safeUser = preg_replace('/[^a-zA-Z0-9]/', '', $_SESSION["user"] ?? ("sa" . $id));
				$folder   = "views/img/users/" . $safeUser;
				if (!is_dir($folder)) { mkdir($folder, 0755, true); }
				$rand = mt_rand(100, 999);
				$destination = imagecreatetruecolor($newWidth, $newHeight);
				if ($type === "image/jpeg") {
					$photo = $folder . "/" . $rand . ".jpg";
					imagecopyresized($destination, imagecreatefromjpeg($_FILES["saPhoto"]["tmp_name"]), 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
					imagejpeg($destination, $photo);
				} else {
					$photo = $folder . "/" . $rand . ".png";
					imagecopyresized($destination, imagecreatefrompng($_FILES["saPhoto"]["tmp_name"]), 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
					imagepng($destination, $photo);
				}
			}
		}

		$data = ["name" => $name, "email" => $email, "photo" => $photo];
		if ($pass !== "") { $data["password"] = password_hash($pass, PASSWORD_DEFAULT); }

		if (ModelOrganizations::mdlUpdateOwnProfile($id, $data) === "ok") {
			$_SESSION["name"]  = $name;
			$_SESSION["photo"] = $photo;
			self::alertStay("success", "Your profile has been updated.");
		} else {
			self::alertStay("error", "Could not update your profile.");
		}
	}

	/*=============================================
	SWAL HELPER
	=============================================*/

	/** Like alert() but returns to the Super Admin profile page on success. */
	private static function alertStay(string $type, string $title): void {
		echo '<script>
		swal({ type: "' . $type . '", title: "' . addslashes($title) . '", confirmButtonText: "Close" })
		  .then((r) => { if (r.value && "' . $type . '" === "success") { window.location = "sa-profile"; } })
		</script>';
	}

	private static function alert(string $type, string $title): void {
		echo '<script>
		swal({ type: "' . $type . '", title: "' . addslashes($title) . '", confirmButtonText: "Close" })
		  .then((r) => { if (r.value && "' . $type . '" === "success") { window.location = "organizations"; } })
		</script>';
	}

}
