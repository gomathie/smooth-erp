<?php

if (!Permission::has("accounting")) {
  echo '<script>window.location = "home";</script>';
  return;
}

if (!ControllerSettings::ctrAccountingEnabled()) {
  echo '<script>window.location = "home";</script>';
  return;
}

$invoices     = ControllerInvoices::ctrShowInvoices(null, null) ?: [];
$trialBalance = ControllerAccounting::ctrTrialBalance();
$entries      = ControllerAccounting::ctrRecentEntries(40);
$expenses     = ControllerExpenses::ctrShowExpenses();

$totalExpenses = 0.0;
foreach ($expenses as $ex) { $totalExpenses += (float)$ex["amount"]; }

// Profit & loss from the ledger: income less all expenses (incl. COGS).
$incomeTotal  = 0.0;
$expenseTotal = 0.0;
foreach ($trialBalance as $accRow) {
  if ($accRow["type"] === "income")  { $incomeTotal  += (float)$accRow["balance"]; }
  if ($accRow["type"] === "expense") { $expenseTotal += (float)$accRow["balance"]; }
}
$netIncome = $incomeTotal - $expenseTotal;

$today = date("Y-m-d");

$totalInvoiced = 0.0;
$totalPaid     = 0.0;
$totalOutstanding = 0.0;
$totalOverdue  = 0.0;
$totalTax      = 0.0;
$outstanding   = [];

foreach ($invoices as $inv) {
  if ($inv["status"] === "draft") { continue; } // drafts aren't recognised in the books
  $bal = (float)$inv["balanceDue"];
  $totalInvoiced += (float)$inv["totalPrice"];
  $totalPaid     += (float)$inv["amountPaid"];
  $totalTax      += (float)$inv["tax"];
  if ($bal > 0) {
    $totalOutstanding += $bal;
    $isOverdue = (!empty($inv["dueDate"]) && $inv["dueDate"] < $today);
    if ($isOverdue) { $totalOverdue += $bal; }
    $outstanding[] = $inv + ["overdue" => $isOverdue];
  }
}

$accountTypeLabels = [
  "asset" => "Asset", "liability" => "Liability", "equity" => "Equity",
  "income" => "Income", "expense" => "Expense",
];
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1><?php echo t('Accounting'); ?></h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Accounting</li>
    </ol>
  </section>

  <section class="content">

    <!-- ACTION BAR -->
    <div class="row">
      <div class="col-12" style="margin-bottom:12px;">
        <a class="btn btn-success" href="expenses"><i class="fa fa-credit-card"></i> Expenses</a>
        <a class="btn btn-primary" href="chart-of-accounts"><i class="fa fa-list-alt"></i> Chart of Accounts</a>
      </div>
    </div>

    <!-- KPI CARDS -->
    <div class="row">
      <div class="col-md-3 col-sm-6">
        <div class="small-box bg-aqua">
          <div class="inner"><h3>$ <?php echo number_format($totalInvoiced, 2); ?></h3><p>Total Invoiced</p></div>
          <div class="icon"><i class="fa fa-file-text"></i></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="small-box bg-green">
          <div class="inner"><h3>$ <?php echo number_format($totalPaid, 2); ?></h3><p>Total Received</p></div>
          <div class="icon"><i class="fa fa-money"></i></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="small-box bg-yellow">
          <div class="inner"><h3>$ <?php echo number_format($totalOutstanding, 2); ?></h3><p>Outstanding (A/R)</p></div>
          <div class="icon"><i class="fa fa-hourglass-half"></i></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="small-box bg-red">
          <div class="inner"><h3>$ <?php echo number_format($totalOverdue, 2); ?></h3><p>Overdue</p></div>
          <div class="icon"><i class="fa fa-exclamation-triangle"></i></div>
        </div>
      </div>
    </div>

    <!-- PROFIT & LOSS SNAPSHOT -->
    <div class="row">
      <div class="col-md-3 col-sm-6">
        <div class="small-box bg-light-blue">
          <div class="inner"><h3>$ <?php echo number_format($incomeTotal, 2); ?></h3><p>Income (recognised)</p></div>
          <div class="icon"><i class="fa fa-line-chart"></i></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="small-box bg-maroon">
          <div class="inner"><h3>$ <?php echo number_format($totalExpenses, 2); ?></h3><p>Expenses Recorded</p></div>
          <div class="icon"><i class="fa fa-credit-card"></i></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="small-box bg-purple">
          <div class="inner"><h3>$ <?php echo number_format($expenseTotal, 2); ?></h3><p>Total Costs (incl. COGS)</p></div>
          <div class="icon"><i class="fa fa-cubes"></i></div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="small-box <?php echo $netIncome >= 0 ? 'bg-green' : 'bg-red'; ?>">
          <div class="inner"><h3>$ <?php echo number_format($netIncome, 2); ?></h3><p>Net Profit / Loss</p></div>
          <div class="icon"><i class="fa fa-balance-scale"></i></div>
        </div>
      </div>
    </div>

    <div class="row">

      <!-- TRIAL BALANCE -->
      <div class="col-md-6">
        <div class="card card-primary card-outline">
          <div class="card-header"><h3 class="card-title"><i class="fa fa-balance-scale"></i> Trial Balance</h3></div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead>
                <tr style="background:#f5f5f5;">
                  <th>Account</th><th>Type</th>
                  <th class="text-right">Debit</th><th class="text-right">Credit</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $sumDebit = 0.0; $sumCredit = 0.0;
                foreach ($trialBalance as $acc) {
                  $sumDebit  += (float)$acc["debit"];
                  $sumCredit += (float)$acc["credit"];
                  echo '<tr>';
                  echo '<td>' . htmlspecialchars($acc["code"] . " · " . $acc["name"]) . '</td>';
                  echo '<td>' . ($accountTypeLabels[$acc["type"]] ?? ucfirst($acc["type"])) . '</td>';
                  echo '<td class="text-right">$ ' . number_format((float)$acc["debit"], 2) . '</td>';
                  echo '<td class="text-right">$ ' . number_format((float)$acc["credit"], 2) . '</td>';
                  echo '</tr>';
                }
                ?>
              </tbody>
              <tfoot>
                <tr style="font-weight:bold; background:#f9f9f9;">
                  <td colspan="2" class="text-right">Totals</td>
                  <td class="text-right">$ <?php echo number_format($sumDebit, 2); ?></td>
                  <td class="text-right">$ <?php echo number_format($sumCredit, 2); ?></td>
                </tr>
              </tfoot>
            </table>
            <p class="text-muted" style="margin:0;">Tax collected (payable): $ <?php echo number_format($totalTax, 2); ?></p>
          </div>
        </div>
      </div>

      <!-- OUTSTANDING INVOICES -->
      <div class="col-md-6">
        <div class="card card-warning card-outline">
          <div class="card-header"><h3 class="card-title"><i class="fa fa-hourglass-half"></i> Outstanding Invoices</h3></div>
          <div class="card-body">
            <?php if (!$outstanding) { ?>
              <p class="text-muted">Nothing outstanding — all invoices are settled. 🎉</p>
            <?php } else { ?>
              <table class="table table-striped">
                <thead>
                  <tr><th>Invoice #</th><th>Due</th><th class="text-right">Balance</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($outstanding as $inv) {
                    $rowStyle = $inv["overdue"] ? ' style="color:#dd4b39;"' : '';
                  ?>
                    <tr<?php echo $rowStyle; ?>>
                      <td><a href="index.php?route=invoice-detail&idInvoice=<?php echo $inv["id"]; ?>">#<?php echo htmlspecialchars($inv["invoiceNumber"]); ?></a>
                          <?php echo $inv["overdue"] ? ' <span class="badge text-bg-danger">Overdue</span>' : ''; ?>
                      </td>
                      <td><?php echo $inv["dueDate"] ?: "—"; ?></td>
                      <td class="text-right">$ <?php echo number_format((float)$inv["balanceDue"], 2); ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            <?php } ?>
          </div>
        </div>
      </div>

    </div>

    <!-- JOURNAL / GENERAL LEDGER -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header"><h3 class="card-title"><i class="fa fa-book"></i> Recent Journal Entries</h3></div>
          <div class="card-body">
            <?php if (!$entries) { ?>
              <p class="text-muted">No journal entries yet. They are posted automatically when invoices are sent and payments recorded.</p>
            <?php } else { ?>
              <table class="table table-bordered">
                <thead>
                  <tr style="background:#f5f5f5;">
                    <th>Date</th><th>Reference</th><th>Account</th>
                    <th class="text-right">Debit</th><th class="text-right">Credit</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($entries as $entry) {
                    $lines = $entry["lines"] ?? [];
                    $first = true;
                    foreach ($lines as $line) {
                  ?>
                      <tr>
                        <td><?php echo $first ? $entry["entryDate"] : ''; ?></td>
                        <td><?php echo $first ? htmlspecialchars($entry["reference"]) : ''; ?></td>
                        <td><?php echo htmlspecialchars($line["code"] . " · " . $line["name"]); ?></td>
                        <td class="text-right"><?php echo ((float)$line["debit"] > 0) ? '$ ' . number_format((float)$line["debit"], 2) : ''; ?></td>
                        <td class="text-right"><?php echo ((float)$line["credit"] > 0) ? '$ ' . number_format((float)$line["credit"], 2) : ''; ?></td>
                      </tr>
                  <?php $first = false; } } ?>
                </tbody>
              </table>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>

  </section>

</div>
