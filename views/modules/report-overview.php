<?php
if (!Permission::has("reports")) { echo '<script>window.location="home";</script>'; return; }
if (!ControllerSettings::ctrAccountingEnabled()) { echo '<script>window.location="home";</script>'; return; }
$reportRoute = "report-overview";

$range = ControllerReports::ctrRange();
$from = $range["from"]; $to = $range["to"];

$sales    = ControllerReports::ctrSales($from, $to);
$invoices = ControllerReports::ctrInvoices($from, $to);
$payments = ControllerReports::ctrPayments($from, $to);
$expenses = ControllerReports::ctrExpenses($from, $to);
$allInvoices = ControllerInvoices::ctrShowInvoices(null, null) ?: [];

$posSales = array_sum(array_map(fn($s) => (float)$s["totalPrice"], $sales));
$invoiced = 0.0; $invTax = 0.0;
foreach ($invoices as $i) { if ($i["status"] !== "draft") { $invoiced += (float)$i["totalPrice"]; $invTax += (float)$i["tax"]; } }
$received = array_sum(array_map(fn($p) => (float)$p["amount"], $payments));
$expensesTotal = array_sum(array_map(fn($e) => (float)$e["amount"], $expenses));
$salesTax = array_sum(array_map(fn($s) => (float)$s["tax"], $sales));

// Point-in-time figures (not range filtered)
$outstanding = 0.0;
foreach ($allInvoices as $i) { if ($i["status"] !== "draft") { $outstanding += (float)$i["balanceDue"]; } }
$inventoryValue = ModelInventory::mdlInventoryValue();

$income = $posSales + $invoiced;
$net = $income - $expensesTotal;
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?php echo t('Business Overview'); ?> <small><?php echo t('Report'); ?></small></h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Business Overview</li>
    </ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="row">
      <div class="col-md-3 col-sm-6"><div class="small-box bg-aqua"><div class="inner"><h3>$ <?php echo number_format($posSales,2); ?></h3><p>POS Sales</p></div><div class="icon"><i class="fa fa-shopping-cart"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-blue"><div class="inner"><h3>$ <?php echo number_format($invoiced,2); ?></h3><p>Invoiced</p></div><div class="icon"><i class="fa fa-file-text"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-green"><div class="inner"><h3>$ <?php echo number_format($received,2); ?></h3><p>Payments Received</p></div><div class="icon"><i class="fa fa-money"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-maroon"><div class="inner"><h3>$ <?php echo number_format($expensesTotal,2); ?></h3><p>Expenses</p></div><div class="icon"><i class="fa fa-credit-card"></i></div></div></div>
    </div>
    <div class="row">
      <div class="col-md-3 col-sm-6"><div class="small-box bg-yellow"><div class="inner"><h3>$ <?php echo number_format($outstanding,2); ?></h3><p>Outstanding A/R <small>(current)</small></p></div><div class="icon"><i class="fa fa-hourglass-half"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-purple"><div class="inner"><h3>$ <?php echo number_format($inventoryValue,2); ?></h3><p>Inventory Value <small>(current)</small></p></div><div class="icon"><i class="fa fa-cubes"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-navy"><div class="inner"><h3>$ <?php echo number_format($income,2); ?></h3><p>Total Income</p></div><div class="icon"><i class="fa fa-line-chart"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box <?php echo $net>=0?'bg-green':'bg-red'; ?>"><div class="inner"><h3>$ <?php echo number_format($net,2); ?></h3><p>Income &minus; Expenses</p></div><div class="icon"><i class="fa fa-balance-scale"></i></div></div></div>
    </div>

    <div class="card">
      <div class="card-header"><h3 class="card-title">Summary for the selected period</h3></div>
      <div class="card-body">
        <table class="table table-bordered">
          <tr><td>POS sales (count)</td><td class="text-right"><?php echo count($sales); ?></td><td class="text-right">$ <?php echo number_format($posSales,2); ?></td></tr>
          <tr><td>Invoices issued (non-draft)</td><td class="text-right"><?php echo count(array_filter($invoices, fn($i)=>$i["status"]!=="draft")); ?></td><td class="text-right">$ <?php echo number_format($invoiced,2); ?></td></tr>
          <tr><td>Payments received (count)</td><td class="text-right"><?php echo count($payments); ?></td><td class="text-right">$ <?php echo number_format($received,2); ?></td></tr>
          <tr><td>Expenses (count)</td><td class="text-right"><?php echo count($expenses); ?></td><td class="text-right">$ <?php echo number_format($expensesTotal,2); ?></td></tr>
          <tr><td>Tax collected (invoices + sales)</td><td class="text-right"></td><td class="text-right">$ <?php echo number_format($invTax + $salesTax,2); ?></td></tr>
        </table>
      </div>
    </div>
  </section>
</div>
