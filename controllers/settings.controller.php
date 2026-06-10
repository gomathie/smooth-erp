<?php

/**
 * Application settings, including the admin-controlled accounting module toggle.
 */
class ControllerSettings {

	/*=============================================
	IS THE ACCOUNTING MODULE ENABLED?
	=============================================
	Used to gate the Accounting / Expenses / Chart of Accounts UI and routes.
	GL postings still run in the background so the books stay consistent.
	*/

	public static function ctrAccountingEnabled(): bool {

		return ModelSettings::mdlGet("accounting_enabled", "1") === "1";

	}

	/*=============================================
	GET / SET A SETTING
	=============================================*/

	public static function ctrGet(string $key, string $default = ""): string {

		return ModelSettings::mdlGet($key, $default);

	}

	/*=============================================
	COMPANY PROFILE / BRANDING (org admin, under Settings)
	=============================================*/

	public static function ctrCompanyProfile() {
		return ModelOrganizations::mdlGetOrganization(Tenant::id());
	}

	public static function ctrUpdateProfile(): void {

		if (!isset($_POST["saveCompanyProfile"])) {
			return;
		}
		if (($_SESSION["profile"] ?? "") !== "Administrator" || !Tenant::hasOrg()) {
			return;
		}

		$org = Tenant::id();

		// --- Logo upload (optional) ---
		if (isset($_FILES["orgLogo"]) && $_FILES["orgLogo"]["error"] === UPLOAD_ERR_OK) {
			$allowed = ["jpg", "jpeg", "png", "gif", "bmp"];
			$ext = strtolower(pathinfo($_FILES["orgLogo"]["name"], PATHINFO_EXTENSION));
			$size = (int)$_FILES["orgLogo"]["size"];

			if (in_array($ext, $allowed, true) && $size > 0 && $size <= 1048576 && @getimagesize($_FILES["orgLogo"]["tmp_name"])) {
				$folder = "views/img/organizations/" . $org;
				if (!is_dir($folder)) { @mkdir($folder, 0755, true); }
				$dest = $folder . "/logo." . $ext;
				if (move_uploaded_file($_FILES["orgLogo"]["tmp_name"], $dest)) {
					ModelOrganizations::mdlSetLogo($org, $dest);
				}
			}
		}

		// --- Theme color (validate #RRGGBB) ---
		$theme = $_POST["themeColor"] ?? "#1e3a5f";
		if (!preg_match('/^#[0-9a-fA-F]{6}$/', $theme)) { $theme = "#1e3a5f"; }

		ModelOrganizations::mdlUpdateProfile($org, [
			"name"       => trim($_POST["orgName"] ?? ""),
			"industry"   => $_POST["orgIndustry"] ?? "",
			"email"      => $_POST["orgEmail"] ?? "",
			"phone"      => $_POST["orgPhone"] ?? "",
			"fax"        => $_POST["orgFax"] ?? "",
			"website"    => $_POST["orgWebsite"] ?? "",
			"address"    => $_POST["orgAddress"] ?? "",
			"city"       => $_POST["orgCity"] ?? "",
			"region"     => $_POST["orgRegion"] ?? "",
			"postalCode" => $_POST["orgPostalCode"] ?? "",
			"country"    => $_POST["orgCountry"] ?? "",
			"themeColor" => $theme,
		]);

		echo '<script>
		swal({ type: "success", title: "Company profile saved", confirmButtonText: "Close" })
		  .then((r) => { if (r.value) { window.location = "company-profile"; } })
		</script>';
	}

	/*=============================================
	TOGGLE THE ACCOUNTING MODULE (admin only, from Settings page)
	=============================================*/

	public static function ctrToggleAccounting(): void {

		if (!isset($_POST["toggleAccounting"])) {
			return;
		}

		if (($_SESSION["profile"] ?? "") !== "Administrator") {
			return;
		}

		$value = $_POST["toggleAccounting"] === "1" ? "1" : "0";
		ModelSettings::mdlSet("accounting_enabled", $value);

		$msg = $value === "1" ? "Accounting module enabled" : "Accounting module disabled";

		echo '<script>
		swal({
			  type: "success",
			  title: "' . $msg . '",
			  showConfirmButton: true,
			  confirmButtonText: "Close"
			  }).then((result) => {
						if (result.value) { window.location = "settings"; }
					})
		</script>';

	}

}
