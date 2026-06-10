<?php

/**
 * Accounting Reports - Shared Helper Functions.
 *
 * All financial reports derive from journal_entries / journal_lines (the
 * single source of truth). This helper provides reusable functions for
 * querying balances, formatting output, and building HTML tables.
 */

require_once __DIR__ . '/../models/connection.php';

class AccountingReports {

	/**
	 * Get all accounts with their aggregated debit/credit balances.
	 * @return array
	 */
	public static function getAccountBalances() {
		$stmt = Connection::connect()->prepare(
			"SELECT a.id, a.code, a.name, a.type,
			        COALESCE(SUM(jl.debit), 0)  AS debit,
			        COALESCE(SUM(jl.credit), 0) AS credit
			   FROM accounts a
			   LEFT JOIN journal_lines jl ON jl.idAccount = a.id
			        AND jl.idOrganization = :org
			  WHERE a.idOrganization = :org
			  GROUP BY a.id, a.code, a.name, a.type
			  ORDER BY a.code ASC"
		);
		$org = Tenant::id();
		$stmt->bindParam(":org", $org, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	/**
	 * Get accounts filtered by type with aggregated balances.
	 * @param string $type  asset|liability|equity|income|expense
	 * @return array
	 */
	public static function getAccountsByType($type) {
		$stmt = Connection::connect()->prepare(
			"SELECT a.id, a.code, a.name, a.type,
			        COALESCE(SUM(jl.debit), 0)  AS debit,
			        COALESCE(SUM(jl.credit), 0) AS credit
			   FROM accounts a
			   LEFT JOIN journal_lines jl ON jl.idAccount = a.id
			        AND jl.idOrganization = :org
			  WHERE a.idOrganization = :org AND a.type = :type
			  GROUP BY a.id, a.code, a.name, a.type
			  ORDER BY a.code ASC"
		);
		$org = Tenant::id();
		$stmt->bindParam(":org", $org, PDO::PARAM_INT);
		$stmt->bindParam(":type", $type, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	/**
	 * Get journal lines for a specific account within a date range.
	 * @param int    $accountId
	 * @param string $fromDate  YYYY-MM-DD
	 * @param string $toDate    YYYY-MM-DD
	 * @return array
	 */
	public static function getJournalLinesForAccount($accountId, $fromDate = '', $toDate = '') {
		$link = Connection::connect();
		$org = Tenant::id();
		$sql = "SELECT je.entryDate, je.reference, je.sourceType, je.description,
				       jl.debit, jl.credit
				  FROM journal_lines jl
				  JOIN journal_entries je ON je.id = jl.idJournalEntry
				 WHERE jl.idAccount = :accountId
				   AND jl.idOrganization = :org";
		$params = array(":accountId" => $accountId, ":org" => $org);
		if ($fromDate !== '') {
			$sql .= " AND je.entryDate >= :fromDate";
			$params[":fromDate"] = $fromDate;
		}
		if ($toDate !== '') {
			$sql .= " AND je.entryDate <= :toDate";
			$params[":toDate"] = $toDate;
		}
		$sql .= " ORDER BY je.entryDate ASC, je.id ASC";
		$stmt = $link->prepare($sql);
		foreach ($params as $k => $v) {
			$stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	/**
	 * Get all journal entries with their lines within a date range.
	 * @param string $fromDate
	 * @param string $toDate
	 * @return array
	 */
	public static function getJournalEntries($fromDate = '', $toDate = '') {
		$link = Connection::connect();
		$org = Tenant::id();
		$sql = "SELECT je.* FROM journal_entries je WHERE je.idOrganization = :org";
		$params = array(":org" => $org);
		if ($fromDate !== '') {
			$sql .= " AND je.entryDate >= :fromDate";
			$params[":fromDate"] = $fromDate;
		}
		if ($toDate !== '') {
			$sql .= " AND je.entryDate <= :toDate";
			$params[":toDate"] = $toDate;
		}
		$sql .= " ORDER BY je.entryDate ASC, je.id ASC";
		$stmt = $link->prepare($sql);
		foreach ($params as $k => $v) {
			$stmt->bindValue($k, $v, PDO::PARAM_INT);
		}
		$stmt->execute();
		$entries = $stmt->fetchAll() ?: [];
		$lineStmt = $link->prepare(
			"SELECT jl.debit, jl.credit, a.code, a.name, a.type
			   FROM journal_lines jl
			   JOIN accounts a ON a.id = jl.idAccount
			  WHERE jl.idJournalEntry = :id AND jl.idOrganization = :org
			  ORDER BY jl.debit DESC"
		);
		foreach ($entries as &$entry) {
			$lineStmt->bindValue(":id", (int)$entry["id"], PDO::PARAM_INT);
			$lineStmt->bindValue(":org", $org, PDO::PARAM_INT);
			$lineStmt->execute();
			$entry["lines"] = $lineStmt->fetchAll() ?: [];
		}
		return $entries;
	}

	/**
	 * Get total balance for a list of account types.
	 * @param array $types  e.g. ['income'] or ['asset']
	 * @return float
	 */
	public static function getTotalByTypes($types) {
		$total = 0.0;
		$balances = self::getAccountBalances();
		$normalDebit = array("asset", "expense");
		foreach ($balances as $row) {
			if (in_array($row["type"], $types, true)) {
				$debit  = (float)$row["debit"];
				$credit = (float)$row["credit"];
				$total += in_array($row["type"], $normalDebit, true)
					? ($debit - $credit)
					: ($credit - $debit);
			}
		}
		return $total;
	}

	/**
	 * Get overdue invoices (for A/R aging).
	 * @param string $asOfDate  YYYY-MM-DD
	 * @return array
	 */
	public static function getOverdueInvoices($asOfDate = '') {
		if ($asOfDate === '') { $asOfDate = date("Y-m-d"); }
		$stmt = Connection::connect()->prepare(
			"SELECT i.id, i.invoiceNumber, i.idCustomer, i.totalPrice,
			        i.amountPaid, i.balanceDue, i.dueDate, i.invoiceDate,
			        i.status, i.currency, c.name AS customerName
			   FROM invoices i
			   JOIN customers c ON c.id = i.idCustomer
			  WHERE i.idOrganization = :org
			    AND i.status != 'draft'
			    AND i.balanceDue > 0
			    AND i.dueDate < :asOf
			  ORDER BY i.dueDate ASC"
		);
		$org = Tenant::id();
		$stmt->bindParam(":org", $org, PDO::PARAM_INT);
		$stmt->bindParam(":asOf", $asOfDate, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	/**
	 * Get all open invoices (not fully paid, not draft).
	 * @return array
	 */
	public static function getOpenInvoices() {
		$stmt = Connection::connect()->prepare(
			"SELECT i.id, i.invoiceNumber, i.idCustomer, i.totalPrice,
			        i.amountPaid, i.balanceDue, i.dueDate, i.invoiceDate,
			        i.status, i.currency, c.name AS customerName
			   FROM invoices i
			   JOIN customers c ON c.id = i.idCustomer
			  WHERE i.idOrganization = :org
			    AND i.status != 'draft'
			    AND i.balanceDue > 0
			  ORDER BY i.dueDate ASC"
		);
		$org = Tenant::id();
		$stmt->bindParam(":org", $org, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	/**
	 * Get expenses within a date range.
	 * @param string $fromDate
	 * @param string $toDate
	 * @return array
	 */
	public static function getExpenses($fromDate = '', $toDate = '') {
		$link = Connection::connect();
		$org = Tenant::id();
		$sql = "SELECT e.*, a.name AS accountName, a.code AS accountCode
				  FROM expenses e
				  JOIN accounts a ON a.id = e.idExpenseAccount
				 WHERE e.idOrganization = :org";
		$params = array(":org" => $org);
		if ($fromDate !== '') {
			$sql .= " AND e.expenseDate >= :fromDate";
			$params[":fromDate"] = $fromDate;
		}
		if ($toDate !== '') {
			$sql .= " AND e.expenseDate <= :toDate";
			$params[":toDate"] = $toDate;
		}
		$sql .= " ORDER BY e.expenseDate ASC";
		$stmt = $link->prepare($sql);
		foreach ($params as $k => $v) {
			$stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	/**
	 * Get stock movement summary per product.
	 * @return array
	 */
	public static function getStockSummary() {
		$stmt = Connection::connect()->prepare(
			"SELECT p.id, p.code, p.description, p.stock, p.buyingPrice, p.sellingPrice,
			        COALESCE(SUM(sm.qtyChange), 0) AS totalMovementQty,
			        COALESCE(SUM(sm.qtyChange * sm.unitCost), 0) AS totalMovementValue
			   FROM products p
			   LEFT JOIN stock_movements sm ON sm.idProduct = p.id
			        AND sm.idOrganization = :org
			  WHERE p.idOrganization = :org
			  GROUP BY p.id, p.code, p.description, p.stock, p.buyingPrice, p.sellingPrice
			  ORDER BY p.code ASC"
		);
		$org = Tenant::id();
		$stmt->bindParam(":org", $org, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	/**
	 * Get payments received within a date range.
	 * @param string $fromDate
	 * @param string $toDate
	 * @return array
	 */
	public static function getPaymentsReceived($fromDate = '', $toDate = '') {
		$link = Connection::connect();
		$org = Tenant::id();
		$sql = "SELECT pr.*, c.name AS customerName, i.invoiceNumber
				  FROM payments_received pr
				  JOIN customers c ON c.id = pr.idCustomer
				  JOIN invoices i ON i.id = pr.idInvoice
				 WHERE pr.idOrganization = :org";
		$params = array(":org" => $org);
		if ($fromDate !== '') {
			$sql .= " AND pr.paymentDate >= :fromDate";
			$params[":fromDate"] = $fromDate;
		}
		if ($toDate !== '') {
			$sql .= " AND pr.paymentDate <= :toDate";
			$params[":toDate"] = $toDate;
		}
		$sql .= " ORDER BY pr.paymentDate ASC";
		$stmt = $link->prepare($sql);
		foreach ($params as $k => $v) {
			$stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
		}
		$stmt->execute();
		return $stmt->fetchAll() ?: [];
	}

	/* =========================================
	   CALCULATION HELPERS
	   ========================================= */

	/**
	 * Compute net balance for an account row.
	 * Asset/expense = debit - credit; others = credit - debit.
	 * @param array $row
	 * @return float
	 */
	public static function netBalance($row) {
		$debit  = (float)($row["debit"] ?? 0);
		$credit = (float)($row["credit"] ?? 0);
		$type   = $row["type"] ?? "asset";
		return in_array($type, array("asset", "expense"), true)
			? $debit - $credit
			: $credit - $debit;
	}

	/* =========================================
	   HTML / FORMATTING HELPERS
	   ========================================= */

	/**
	 * Format a number as currency string.
	 * @param float  $amount
	 * @param string $symbol
	 * @return string
	 */
	public static function fmtMoney($amount, $symbol = '$') {
		return $symbol . ' ' . number_format((float)$amount, 2);
	}

	/**
	 * Format a number with commas.
	 * @param float|int $number
	 * @return string
	 */
	public static function fmtNumber($number) {
		return number_format((float)$number, 2);
	}

	/**
	 * Render a standard report header with title, breadcrumb, and date filter.
	 * @param string $title
	 * @param string $breadcrumb
	 * @param string $currentRoute
	 * @param bool   $showDateFilter
	 * @param string $defaultFrom
	 * @param string $defaultTo
	 */
	public static function renderHeader($title, $breadcrumb, $currentRoute, $showDateFilter = true, $defaultFrom = '', $defaultTo = '') {
		if ($defaultFrom === '') { $defaultFrom = date("Y-01-01"); }
		if ($defaultTo === '')   { $defaultTo   = date("Y-m-d"); }
		$from = $_GET["from"] ?? $defaultFrom;
		$to   = $_GET["to"]   ?? $defaultTo;

		echo '<div class="content-wrapper">';
		echo '<section class="content-header">';
		echo '<h1>' . htmlspecialchars($title) . '</h1>';
		echo '<ol class="breadcrumb">';
		echo '<li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>';
		echo '<li><a href="accounting">Accounting</a></li>';
		echo '<li class="active">' . htmlspecialchars($breadcrumb) . '</li>';
		echo '</ol>';
		echo '</section>';
		echo '<section class="content">';
		echo '<div style="margin-bottom:12px;">';
		echo '<a class="btn btn-default" href="accounting"><i class="fa fa-arrow-left"></i> Back to Accounting</a>';
		echo '</div>';

		if ($showDateFilter) {
			echo '<div class="card card-outline card-primary" style="margin-bottom:15px;">';
			echo '<div class="card-body">';
			echo '<form method="GET" class="form-inline">';
			echo '<input type="hidden" name="route" value="' . htmlspecialchars($currentRoute) . '">';
			echo '<label style="margin-right:5px;" for="date-from">From:</label>';
			echo '<input type="date" id="date-from" name="from" value="' . htmlspecialchars($from) . '" class="form-control form-control-sm" style="margin-right:15px;">';
			echo '<label style="margin-right:5px;" for="date-to">To:</label>';
			echo '<input type="date" id="date-to" name="to" value="' . htmlspecialchars($to) . '" class="form-control form-control-sm" style="margin-right:15px;">';
			echo '<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>';
			echo '</form>';
			echo '</div>';
			echo '</div>';
		}

		echo '<input type="hidden" id="report-from" value="' . htmlspecialchars($from) . '">';
		echo '<input type="hidden" id="report-to" value="' . htmlspecialchars($to) . '">';
	}

	/** Close the report content section. */
	public static function renderFooter() {
		echo '</section>';
		echo '</div>';
	}

	/**
	 * Render a standard HTML table with header and optional footer totals.
	 * @param array  $columns  [['key'=>'', 'label'=>'', 'align'=>'left', 'format'=>'money'], ...]
	 * @param array  $rows
	 * @param array  $totals   Optional totals row
	 * @param string $tableClass
	 * @param string $emptyMessage
	 */
	public static function renderTable($columns, $rows, $totals = array(), $tableClass = 'table table-bordered table-striped', $emptyMessage = 'No data found for the selected period.') {
		if (empty($rows)) {
			echo '<p class="text-muted"><em>' . $emptyMessage . '</em></p>';
			return;
		}
		echo '<div class="table-responsive">';
		echo '<table class="' . $tableClass . '">';

		// Header
		echo '<thead><tr style="background:#f5f5f5;">';
		foreach ($columns as $col) {
			$align = (isset($col["align"]) && $col["align"] === "right") ? ' class="text-right"' : '';
			echo '<th' . $align . '>' . htmlspecialchars($col["label"]) . '</th>';
		}
		echo '</tr></thead>';

		// Body
		echo '<tbody>';
		foreach ($rows as $row) {
			echo '<tr>';
			foreach ($columns as $col) {
				$key    = $col["key"];
				$align  = (isset($col["align"]) && $col["align"] === "right") ? ' class="text-right"' : '';
				$format = $col["format"] ?? "text";
				$value  = $row[$key] ?? "";
				if ($format === "money") {
					$value = self::fmtMoney((float)$value);
				} elseif ($format === "number") {
					$value = self::fmtNumber($value);
				}
				echo '<td' . $align . '>' . $value . '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody>';

		// Footer totals
		if (!empty($totals)) {
			echo '<tfoot><tr style="font-weight:bold; background:#f9f9f9;">';
			$first = true;
			foreach ($columns as $col) {
				$key    = $col["key"];
				$align  = (isset($col["align"]) && $col["align"] === "right") ? ' class="text-right"' : '';
				$format = $col["format"] ?? "text";
				if ($first) {
					echo '<td' . $align . '><strong>Total</strong></td>';
					$first = false;
				} else {
					$value = $totals[$key] ?? "";
					if ($format === "money") {
						$value = self::fmtMoney((float)$value);
					} elseif ($format === "number") {
						$value = self::fmtNumber($value);
					}
					echo '<td' . $align . '>' . $value . '</td>';
				}
			}
			echo '</tr></tfoot>';
		}

		echo '</table>';
		echo '</div>';
	}

	/**
	 * Render a collapsible SQL query block.
	 * @param string $description
	 * @param string $sql
	 */
	public static function renderSqlBlock($description, $sql) {
		echo '<div class="callout callout-info" style="margin-top:15px;">';
		echo '<h4><i class="fa fa-database"></i> ' . htmlspecialchars($description) . '</h4>';
		echo '<pre style="margin:5px 0 0; white-space:pre-wrap; font-size:12px;">' . htmlspecialchars(trim($sql)) . '</pre>';
		echo '</div>';
	}

	/**
	 * Render the logic explanation block.
	 * @param string $text
	 */
	public static function renderExplanation($text) {
		echo '<div class="callout callout-success" style="margin-top:10px;">';
		echo '<h4><i class="fa fa-info-circle"></i> Logic Explanation</h4>';
		echo '<p>' . $text . '</p>';
		echo '</div>';
	}

	/**
	 * Render summary KPI cards.
	 * @param array $cards  [['label'=>'', 'value'=>'', 'icon'=>'', 'color'=>''], ...]
	 */
	public static function renderKpiCards($cards) {
		echo '<div class="row" style="margin-bottom:15px;">';
		foreach ($cards as $card) {
			$color = $card["color"] ?? "bg-aqua";
			$icon  = $card["icon"] ?? "fa-chart-bar";
			echo '<div class="col-md-3 col-sm-6">';
			echo '<div class="small-box ' . $color . '">';
			echo '<div class="inner"><h3>' . htmlspecialchars($card["value"]) . '</h3><p>' . htmlspecialchars($card["label"]) . '</p></div>';
			echo '<div class="icon"><i class="fa ' . htmlspecialchars($icon) . '"></i></div>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	/** Render a print button. */
	public static function renderPrintButton() {
		echo '<button onclick="window.print();" class="btn btn-default" style="margin-bottom:10px;"><i class="fa fa-print"></i> Print Report</button>';
	}

}