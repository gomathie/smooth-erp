<?php

/**
 * Reporting layer. Pulls from the existing models and filters by an optional
 * date range (from / to, both Y-m-d). Empty bounds mean "no limit", so a report
 * with no range applied shows everything.
 */
class ControllerReports {

	/*=============================================
	CURRENT DATE RANGE FROM THE QUERY STRING
	=============================================*/

	public static function ctrRange(): array {
		$from = self::cleanDate($_GET["from"] ?? "");
		$to   = self::cleanDate($_GET["to"]   ?? "");
		return ["from" => $from, "to" => $to];
	}

	private static function cleanDate(string $d): string {
		return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) ? $d : "";
	}

	/**
	 * Is $date within [from, to]? Compares on the Y-m-d prefix.
	 */
	public static function inRange($date, string $from, string $to): bool {
		$d = substr((string)$date, 0, 10);
		if ($d === "" || $d === "0000-00-00") { return ($from === "" && $to === ""); }
		if ($from !== "" && $d < $from) { return false; }
		if ($to   !== "" && $d > $to)   { return false; }
		return true;
	}

	/*=============================================
	FILTERED DATA SETS
	=============================================*/

	public static function ctrSales(string $from, string $to): array {
		$all = ModelSales::mdlShowSales("sales", null, null) ?: [];
		return array_values(array_filter($all, fn($s) => self::inRange($s["saledate"], $from, $to)));
	}

	public static function ctrInvoices(string $from, string $to): array {
		$all = ModelInvoices::mdlShowInvoices("invoices", null, null) ?: [];
		return array_values(array_filter($all, fn($i) => self::inRange($i["invoiceDate"], $from, $to)));
	}

	public static function ctrPayments(string $from, string $to): array {
		$all = ModelPayments::mdlShowAllPayments();
		return array_values(array_filter($all, fn($p) => self::inRange($p["paymentDate"], $from, $to)));
	}

	public static function ctrExpenses(string $from, string $to): array {
		$all = ModelExpenses::mdlShowExpenses();
		return array_values(array_filter($all, fn($e) => self::inRange($e["expenseDate"], $from, $to)));
	}

	public static function ctrMovements(string $from, string $to): array {
		$all = ModelInventory::mdlAllMovements();
		return array_values(array_filter($all, fn($m) => self::inRange($m["movementDate"], $from, $to)));
	}

	public static function ctrActivity(string $from, string $to): array {
		$all = ModelInvoices::mdlAllActivity();
		return array_values(array_filter($all, fn($a) => self::inRange($a["createdDate"], $from, $to)));
	}

}
