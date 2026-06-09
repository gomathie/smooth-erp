<?php

/**
 * Tenant context for the current request.
 *
 * Multi-tenancy is row-level: every business table carries an idOrganization
 * column, and queries must be scoped to Tenant::id(). The current org is set
 * in the session at login (from the user's row); a Super Admin has no fixed
 * org and may "enter" one, which sets the session org for that session.
 */
class Tenant {

	/** Current organization id (0 = none / platform-level Super Admin not inside an org). */
	public static function id(): int {
		return (int)($_SESSION["idOrganization"] ?? 0);
	}

	/** True for platform-level Super Admin accounts (set at login; stays true
	 *  even while the SA has "entered" an org and is acting as its admin). */
	public static function isSuperAdmin(): bool {
		return ($_SESSION["isSuperAdmin"] ?? false) === true;
	}

	/** When a Super Admin has entered an org to operate it, returns that org id (else 0). */
	public static function enteredOrg(): int {
		return (int)($_SESSION["enteredOrg"] ?? 0);
	}

	/** True once a tenant context is established (an org user, or an SA who entered an org). */
	public static function hasOrg(): bool {
		return self::id() > 0;
	}

	/** Base currency for the current org (defaults to USD until currency wiring sets it). */
	public static function baseCurrency(): string {
		return $_SESSION["baseCurrency"] ?? "USD";
	}

}
