<?php

/**
 * Read-only accounting reports built from the double-entry journal.
 */
class ControllerAccounting {

	/*=============================================
	TRIAL BALANCE (per-account debit/credit/balance)
	=============================================*/

	public static function ctrTrialBalance(): array {
		return ModelAccounting::mdlTrialBalance();
	}

	/*=============================================
	RECENT JOURNAL ENTRIES (with lines)
	=============================================*/

	public static function ctrRecentEntries(int $limit = 50): array {
		return ModelAccounting::mdlRecentEntries($limit);
	}

	/*=============================================
	CHART OF ACCOUNTS — list
	=============================================*/

	public static function ctrShowAccounts(): array {
		return ModelAccounting::mdlShowAccounts();
	}

	/**
	 * Accounts of a given type, for dropdowns (expense form etc.).
	 */
	public static function ctrAccountsByType(string $type): array {
		return ModelAccounting::mdlAccountsByType($type);
	}

	private const ACCOUNT_TYPES = ["asset", "liability", "equity", "income", "expense"];

	/*=============================================
	ADD ACCOUNT
	=============================================*/

	public static function ctrAddAccount(): void {

		if (!isset($_POST["newAccountCode"])) {
			return;
		}

		$code = trim($_POST["newAccountCode"]);
		$name = trim($_POST["newAccountName"] ?? "");
		$type = $_POST["newAccountType"] ?? "";

		if ($code === "" || $name === "" || !in_array($type, self::ACCOUNT_TYPES, true)) {
			self::accountError("Code, name and a valid type are required.");
			return;
		}

		if (ModelAccounting::mdlCodeExists($code)) {
			self::accountError("An account with code {$code} already exists.");
			return;
		}

		$answer = ModelAccounting::mdlAddAccount(["code" => $code, "name" => $name, "type" => $type]);

		if ($answer === "ok") {
			self::accountOk("Account added successfully");
		}

	}

	/*=============================================
	EDIT ACCOUNT (name + type; code is immutable)
	=============================================*/

	public static function ctrEditAccount(): void {

		if (!isset($_POST["editAccount"])) {
			return;
		}

		$id   = (int)$_POST["editAccount"];
		$name = trim($_POST["editAccountName"] ?? "");
		$type = $_POST["editAccountType"] ?? "";

		$account = ModelAccounting::mdlGetAccount($id);
		if (!is_array($account) || $name === "" || !in_array($type, self::ACCOUNT_TYPES, true)) {
			return;
		}

		// System accounts keep their type (it's relied on by posting logic).
		if ((int)$account["isSystem"] === 1) {
			$type = $account["type"];
		}

		$answer = ModelAccounting::mdlEditAccount(["id" => $id, "name" => $name, "type" => $type]);

		if ($answer === "ok") {
			self::accountOk("Account updated successfully");
		}

	}

	/*=============================================
	DELETE ACCOUNT (blocked for system / in-use accounts)
	=============================================*/

	public static function ctrDeleteAccount(): void {

		if (!isset($_GET["deleteAccount"])) {
			return;
		}

		$id = (int)$_GET["deleteAccount"];
		$account = ModelAccounting::mdlGetAccount($id);

		if (!is_array($account)) {
			return;
		}

		if ((int)$account["isSystem"] === 1) {
			self::accountError("System accounts cannot be deleted.");
			return;
		}

		if (ModelAccounting::mdlAccountInUse($id)) {
			self::accountError("This account has journal entries and cannot be deleted.");
			return;
		}

		if (ModelAccounting::mdlDeleteAccount($id) === "ok") {
			self::accountOk("Account deleted successfully");
		}

	}

	private static function accountOk(string $title): void {
		echo '<script>swal({type:"success",title:"' . $title . '",confirmButtonText:"Close"}).then((r)=>{if(r.value){window.location="chart-of-accounts";}})</script>';
	}

	private static function accountError(string $title): void {
		echo '<script>swal({type:"error",title:"' . addslashes($title) . '",confirmButtonText:"Close"})</script>';
	}

}
