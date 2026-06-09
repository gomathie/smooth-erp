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

		$baseCurrency = strtoupper($_POST["newOrgBaseCurrency"] ?? "USD");

		Connection::begin();
		try {
			$orgId = ModelOrganizations::mdlAddOrganization([
				"name"         => $name,
				"code"         => $code,
				"email"        => $_POST["newOrgEmail"]   ?? "",
				"phone"        => $_POST["newOrgPhone"]   ?? "",
				"address"      => $_POST["newOrgAddress"] ?? "",
				"baseCurrency" => $baseCurrency,
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
		$_SESSION["profile"] = "Administrator";

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
	SWAL HELPER
	=============================================*/

	private static function alert(string $type, string $title): void {
		echo '<script>
		swal({ type: "' . $type . '", title: "' . addslashes($title) . '", confirmButtonText: "Close" })
		  .then((r) => { if (r.value && "' . $type . '" === "success") { window.location = "organizations"; } })
		</script>';
	}

}
