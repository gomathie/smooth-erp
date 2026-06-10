<?php

/**
 * Role-based access control (RBAC).
 *
 * Each organization user has a ROLE (administrator, manager, accountant, staff,
 * viewer) and an optional per-user permission override. A permission is a coarse
 * module key (see KEYS) that controls whether that module is visible in the
 * sidebar and reachable as a page. The org administrator decides "who sees what"
 * by picking a role and, optionally, ticking individual permissions per user.
 *
 * Effective permissions are computed at login into $_SESSION["permissions"].
 *   - administrator        -> every permission, always (the org owner).
 *   - Super Admin in an org -> every permission (Tenant::isSuperAdmin()).
 *   - other roles          -> the user's stored override if set, else the
 *                             role's default set.
 */
class Permission {

	/** All module permission keys. */
	const KEYS = [
		'dashboard', 'products', 'customers', 'sales', 'reports',
		'accounting', 'expenses', 'currencies', 'users', 'settings',
	];

	/** Human labels for the management UI. */
	const LABELS = [
		'dashboard'  => 'Dashboard',
		'products'   => 'Products & Categories',
		'customers'  => 'Customers',
		'sales'      => 'Sales, Quotations & Invoices',
		'reports'    => 'Reports',
		'accounting' => 'Accounting & Chart of Accounts',
		'expenses'   => 'Expenses',
		'currencies' => 'Currencies',
		'users'      => 'User Management',
		'settings'   => 'Settings & Company Profile',
	];

	/** Selectable roles (administrator is implicitly all-access). */
	const ROLES = ['administrator', 'manager', 'accountant', 'staff', 'viewer'];

	/** Default permission set per non-admin role. */
	const ROLE_DEFAULTS = [
		'manager'    => ['dashboard', 'products', 'customers', 'sales', 'reports', 'expenses'],
		'accountant' => ['dashboard', 'customers', 'reports', 'accounting', 'expenses'],
		'staff'      => ['dashboard', 'products', 'customers', 'sales'],
		'viewer'     => ['dashboard', 'reports'],
	];

	/** Default permissions for a role (administrator = everything). */
	public static function defaultsFor(string $role): array {
		if ($role === 'administrator') {
			return self::KEYS;
		}
		return self::ROLE_DEFAULTS[$role] ?? ['dashboard'];
	}

	/**
	 * Effective permission list for a user, given role and stored override JSON.
	 * A null/empty override falls back to the role defaults.
	 */
	public static function effective(string $role, ?string $override): array {
		if ($role === 'administrator') {
			return self::KEYS;
		}
		if ($override !== null && $override !== '') {
			$arr = json_decode($override, true);
			if (is_array($arr)) {
				return array_values(array_intersect($arr, self::KEYS));
			}
		}
		return self::defaultsFor($role);
	}

	/** Map a legacy profile to a role (for users created before RBAC). */
	public static function roleFromProfile(string $profile): string {
		switch ($profile) {
			case 'Administrator':
			case 'SuperAdmin':
				return 'administrator';
			default:               // Seller, Special, anything else
				return 'staff';
		}
	}

	/** The legacy profile value a role maps to (kept in sync for older code paths). */
	public static function profileForRole(string $role): string {
		return $role === 'administrator' ? 'Administrator' : 'Seller';
	}

	/** Does the current session hold a given permission? */
	public static function has(string $key): bool {
		// A Super Admin operating inside an org has full access.
		if (Tenant::isSuperAdmin()) {
			return true;
		}
		if (($_SESSION['role'] ?? '') === 'administrator') {
			return true;
		}
		// Legacy sessions without a role: treat an Administrator profile as admin.
		if (!isset($_SESSION['role']) && ($_SESSION['profile'] ?? '') === 'Administrator') {
			return true;
		}
		$perms = $_SESSION['permissions'] ?? [];
		return is_array($perms) && in_array($key, $perms, true);
	}

	/**
	 * Page guard. Returns true if allowed; otherwise emits a redirect to home
	 * and returns false. Use at the top of a module:  if (!Permission::guard('sales')) return;
	 */
	public static function guard(string $key): bool {
		if (self::has($key)) {
			return true;
		}
		echo '<script>window.location = "home";</script>';
		return false;
	}

}
