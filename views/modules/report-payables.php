<?php
if (!Permission::has("reports")) { echo '<script>window.location="home";</script>'; return; }
if (!ControllerSettings::ctrAccountingEnabled()) { echo '<script>window.location="home";</script>'; return; }
$reportRoute = "report-payables";
$range = ControllerReports::ctrRange();
$expenses = ControllerReports::ctrExpenses($range["from"], $range["to"]);

$total = 0.0; $byAccount = [];
foreach ($expenses as $e) {
  $total += (float)$e["amount"];
  $k = ($e["expenseCode"] ?? "") . " · " . ($e["expenseName"] ?? "—");
  $byAccount[$k] = ($byAccount[$k] ?? 0) + (float)$e["amount"];
}

// Accounts Payable balance (point-in-time) from the trial balance
$apBalance = 0.0;
foreach (ControllerAccounting::ctrTrialBalance() as $a) {
  if ($a["code"] === "2000") { $apBalance = (float)$a["balance"]; }
}
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Payables <small>Report</small></h1>
    <ol class="breadcrumb"><li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Payables</li></ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="row">
      <div class="col-md-4"><div class="small-box bg-maroon"><div class="inner"><h3>$ <?php echo number_format($total,2); ?></h3><p>Expenses (period)</p></div><div class="icon"><i class="fa fa-credit-card"></i></div></div></div>
      <div class="col-md-4"><div class="small-box bg-red"><div class="inner"><h3>$ <?php echo number_format($apBalance,2); ?></h3><p>Accounts Payable <small>(current)</small></p></div><div class="icon"><i class="fa fa-balance-scale"></i></div></div></div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">By Expense Account</h3></div>
          <div class="box-body">
            <table class="table">
              <?php foreach ($byAccount as $k => $amt) { ?>
                <tr><td><?php echo htmlspecialchars($k); ?></td><td class="text-right">$ <?php echo number_format($amt,2); ?></td></tr>
              <?php } if (!$byAccount) { echo '<tr><td class="text-muted">No expenses in range</td></tr>'; } ?>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Expenses</h3></div>
          <div class="box-body">
            <table class="table table-bordered table-striped dt-responsive" width="100%">
              <thead><tr><th>#</th><th>Date</th><th>Account</th><th>Paid Through</th><th>Payee</th><th class="text-right">Amount</th></tr></thead>
              <tbody>
                <?php foreach ($expenses as $e) { ?>
                  <tr>
                    <td><?php echo htmlspecialchars($e["expenseNumber"]); ?></td>
                    <td><?php echo $e["expenseDate"]; ?></td>
                    <td><?php echo htmlspecialchars(($e["expenseCode"] ?? "")." · ".($e["expenseName"] ?? "-")); ?></td>
                    <td><?php echo htmlspecialchars($e["paidName"] ?? "-"); ?></td>
                    <td><?php echo htmlspecialchars($e["payee"] ?? ""); ?></td>
                    <td class="text-right">$ <?php echo number_format($e["amount"],2); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
              <tfoot><tr style="font-weight:bold;background:#f9f9f9;"><td colspan="5" class="text-right">Total</td><td class="text-right">$ <?php echo number_format($total,2); ?></td></tr></tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
