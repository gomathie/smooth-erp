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
