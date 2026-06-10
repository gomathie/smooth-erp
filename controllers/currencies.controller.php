<?php

/**
 * Per-organization currency activation (multi-currency Lite).
 * Available to an organization Administrator (or a Super Admin who has entered
 * the org) only when the multi-currency feature is enabled for that org.
 */
class ControllerCurrencies {

	private static function allowed(): bool {
		return Tenant::hasOrg()
			&& Permission::has("currencies")
			&& Currency::isEnabled();
	}

	/** All reference currencies (the master list). */
	public static function ctrAllCurrencies(): array {
		$stmt = Connection::connect()->query("SELECT code, name, symbol FROM currencies ORDER BY code ASC");
		return $stmt ? $stmt->fetchAll() : [];
	}

	/** Currencies activated for the current org. */
	public static function ctrOrgCurrencies(): array {
		return ModelOrganizations::mdlOrgCurrencies(Tenant::id());
	}

	/*=============================================
	ACTIONS (GET-driven from the management page)
	=============================================*/

	public static function ctrHandle(): void {

		if (!self::allowed()) {
			return;
		}

		$org = Tenant::id();

		if (isset($_GET["activate"])) {
			ModelOrganizations::mdlActivateCurrency($org, strtoupper($_GET["activate"]));
			self::redirect();
		}

		if (isset($_GET["deactivate"])) {
			ModelOrganizations::mdlDeactivateCurrency($org, strtoupper($_GET["deactivate"]));
			self::redirect();
		}

		if (isset($_GET["setBase"])) {
			ModelOrganizations::mdlSetBaseCurrency($org, strtoupper($_GET["setBase"]));
			$_SESSION["baseCurrency"] = strtoupper($_GET["setBase"]); // refresh session base
			self::redirect();
		}
	}

	private static function redirect(): void {
		echo '<script>window.location = "currencies";</script>';
	}

}
