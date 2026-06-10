<?php
if (!Permission::has("reports")) { echo '<script>window.location="home";</script>'; return; }
if (!ControllerSettings::ctrAccountingEnabled()) { echo '<script>window.location="home";</script>'; return; }
$reportRoute = "report-payments";
$range = ControllerReports::ctrRange();
$payments = ControllerReports::ctrPayments($range["from"], $range["to"]);

$modeLabels = ["cash"=>"Cash","card"=>"Card","bank_transfer"=>"Bank Transfer","cheque"=>"Cheque","online"=>"Online"];
$total = 0.0; $byMode = [];
foreach ($payments as $p) {
  $total += (float)$p["amount"];
  $m = $p["paymentMode"];
  $byMode[$m] = ($byMode[$m] ?? 0) + (float)$p["amount"];
}
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Payments Received <small>Report</small></h1>
    <ol class="breadcrumb"><li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Payments Received</li></ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="row">
      <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-money"></i></span><div class="info-box-content"><span class="info-box-text">Total Received</span><span class="info-box-number">$ <?php echo number_format($total,2); ?></span></div></div></div>
      <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-list"></i></span><div class="info-box-content"><span class="info-box-text">Payments</span><span class="info-box-number"><?php echo count($payments); ?></span></div></div></div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">By Payment Mode</h3></div>
          <div class="box-body">
            <table class="table">
              <?php foreach ($byMode as $m => $amt) { ?>
                <tr><td><?php echo $modeLabels[$m] ?? ucfirst($m); ?></td><td class="text-right">$ <?php echo number_format($amt,2); ?></td></tr>
              <?php } if (!$byMode) { echo '<tr><td class="text-muted">No payments in range</td></tr>'; } ?>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Payments</h3></div>
          <div class="box-body">
            <table class="table table-bordered table-striped dt-responsive" width="100%">
              <thead><tr><th>Payment #</th><th>Date</th><th>Invoice</th><th>Customer</th><th>Mode</th><th class="text-right">Amount</th></tr></thead>
              <tbody>
                <?php foreach ($payments as $p) {
                  $cust = ControllerCustomers::ctrShowCustomers("id", $p["idCustomer"]);
                  $inv  = ControllerInvoices::ctrShowInvoices("id", $p["idInvoice"]);
                ?>
                  <tr>
                    <td><?php echo htmlspecialchars($p["paymentNumber"]); ?></td>
                    <td><?php echo $p["paymentDate"]; ?></td>
                    <td><?php echo is_array($inv) ? '<a href="index.php?route=invoice-detail&idInvoice='.$p["idInvoice"].'">#'.htmlspecialchars($inv["invoiceNumber"]).'</a>' : '-'; ?></td>
                    <td><?php echo htmlspecialchars($cust["name"] ?? "-"); ?></td>
                    <td><?php echo $modeLabels[$p["paymentMode"]] ?? ucfirst($p["paymentMode"]); ?></td>
                    <td class="text-right">$ <?php echo number_format($p["amount"],2); ?></td>
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
