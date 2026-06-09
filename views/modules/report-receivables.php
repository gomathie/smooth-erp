<?php
if ($_SESSION["profile"] != "Administrator") { echo '<script>window.location="home";</script>'; return; }
if (!ControllerSettings::ctrAccountingEnabled()) { echo '<script>window.location="home";</script>'; return; }
$reportRoute = "report-receivables";
$range = ControllerReports::ctrRange();
$invoices = ControllerReports::ctrInvoices($range["from"], $range["to"]);

$today = date("Y-m-d");
$totalInvoiced = 0.0; $totalPaid = 0.0; $totalOutstanding = 0.0; $totalOverdue = 0.0;
$rows = [];
foreach ($invoices as $i) {
  if ($i["status"] === "draft") { continue; }
  $bal = (float)$i["balanceDue"];
  $totalInvoiced += (float)$i["totalPrice"];
  $totalPaid     += (float)$i["amountPaid"];
  $overdue = ($bal > 0 && !empty($i["dueDate"]) && $i["dueDate"] < $today);
  if ($bal > 0) { $totalOutstanding += $bal; if ($overdue) { $totalOverdue += $bal; } }
  $rows[] = $i + ["overdue" => $overdue];
}
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Receivables <small>Report</small></h1>
    <ol class="breadcrumb"><li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Receivables</li></ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="row">
      <div class="col-md-3 col-sm-6"><div class="small-box bg-blue"><div class="inner"><h3>$ <?php echo number_format($totalInvoiced,2); ?></h3><p>Invoiced</p></div><div class="icon"><i class="fa fa-file-text"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-green"><div class="inner"><h3>$ <?php echo number_format($totalPaid,2); ?></h3><p>Collected</p></div><div class="icon"><i class="fa fa-money"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-yellow"><div class="inner"><h3>$ <?php echo number_format($totalOutstanding,2); ?></h3><p>Outstanding</p></div><div class="icon"><i class="fa fa-hourglass-half"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-red"><div class="inner"><h3>$ <?php echo number_format($totalOverdue,2); ?></h3><p>Overdue</p></div><div class="icon"><i class="fa fa-exclamation-triangle"></i></div></div></div>
    </div>

    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Invoices</h3></div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive" width="100%">
          <thead><tr><th>Invoice #</th><th>Customer</th><th>Date</th><th>Due</th><th class="text-right">Total</th><th class="text-right">Paid</th><th class="text-right">Balance</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $i) {
              $cust = ControllerCustomers::ctrShowCustomers("id", $i["idCustomer"]);
              $style = $i["overdue"] ? ' style="color:#dd4b39;"' : '';
            ?>
              <tr<?php echo $style; ?>>
                <td><a href="index.php?route=invoice-detail&idInvoice=<?php echo $i["id"]; ?>">#<?php echo htmlspecialchars($i["invoiceNumber"]); ?></a><?php echo $i["overdue"] ? ' <span class="label label-danger">Overdue</span>' : ''; ?></td>
                <td><?php echo htmlspecialchars($cust["name"] ?? "-"); ?></td>
                <td><?php echo substr($i["invoiceDate"],0,10); ?></td>
                <td><?php echo $i["dueDate"] ?: "-"; ?></td>
                <td class="text-right">$ <?php echo number_format($i["totalPrice"],2); ?></td>
                <td class="text-right">$ <?php echo number_format($i["amountPaid"],2); ?></td>
                <td class="text-right">$ <?php echo number_format($i["balanceDue"],2); ?></td>
              </tr>
            <?php } ?>
          </tbody>
          <tfoot><tr style="font-weight:bold;background:#f9f9f9;"><td colspan="4" class="text-right">Totals</td><td class="text-right">$ <?php echo number_format($totalInvoiced,2); ?></td><td class="text-right">$ <?php echo number_format($totalPaid,2); ?></td><td class="text-right">$ <?php echo number_format($totalOutstanding,2); ?></td></tr></tfoot>
        </table>
      </div>
    </div>
  </section>
</div>
