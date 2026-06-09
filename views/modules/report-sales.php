<?php
if ($_SESSION["profile"] != "Administrator") { echo '<script>window.location="home";</script>'; return; }
$reportRoute = "report-sales";
$range = ControllerReports::ctrRange();
$sales = ControllerReports::ctrSales($range["from"], $range["to"]);

$total = 0.0; $tax = 0.0; $net = 0.0;
foreach ($sales as $s) { $total += (float)$s["totalPrice"]; $tax += (float)$s["tax"]; $net += (float)$s["netPrice"]; }
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Sales <small>Report</small></h1>
    <ol class="breadcrumb"><li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Sales</li></ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="row">
      <div class="col-md-4 col-sm-4"><div class="info-box"><span class="info-box-icon bg-aqua"><i class="fa fa-shopping-cart"></i></span><div class="info-box-content"><span class="info-box-text">Sales Count</span><span class="info-box-number"><?php echo count($sales); ?></span></div></div></div>
      <div class="col-md-4 col-sm-4"><div class="info-box"><span class="info-box-icon bg-green"><i class="fa fa-money"></i></span><div class="info-box-content"><span class="info-box-text">Total Sales</span><span class="info-box-number">$ <?php echo number_format($total,2); ?></span></div></div></div>
      <div class="col-md-4 col-sm-4"><div class="info-box"><span class="info-box-icon bg-yellow"><i class="fa fa-percent"></i></span><div class="info-box-content"><span class="info-box-text">Tax Collected</span><span class="info-box-number">$ <?php echo number_format($tax,2); ?></span></div></div></div>
    </div>

    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Sales</h3></div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive" width="100%">
          <thead><tr><th>Code</th><th>Date</th><th>Customer</th><th>Seller</th><th>Payment</th><th class="text-right">Net</th><th class="text-right">Tax</th><th class="text-right">Total</th></tr></thead>
          <tbody>
            <?php foreach ($sales as $s) {
              $cust = ControllerCustomers::ctrShowCustomers("id", $s["idCustomer"]);
              $sell = ControllerUsers::ctrShowUsers("id", $s["idSeller"]);
            ?>
              <tr>
                <td><?php echo htmlspecialchars($s["code"]); ?></td>
                <td><?php echo substr($s["saledate"],0,10); ?></td>
                <td><?php echo htmlspecialchars($cust["name"] ?? "-"); ?></td>
                <td><?php echo htmlspecialchars($sell["name"] ?? "-"); ?></td>
                <td><?php echo htmlspecialchars($s["paymentMethod"]); ?></td>
                <td class="text-right">$ <?php echo number_format($s["netPrice"],2); ?></td>
                <td class="text-right">$ <?php echo number_format($s["tax"],2); ?></td>
                <td class="text-right">$ <?php echo number_format($s["totalPrice"],2); ?></td>
              </tr>
            <?php } ?>
          </tbody>
          <tfoot><tr style="font-weight:bold;background:#f9f9f9;"><td colspan="5" class="text-right">Totals</td><td class="text-right">$ <?php echo number_format($net,2); ?></td><td class="text-right">$ <?php echo number_format($tax,2); ?></td><td class="text-right">$ <?php echo number_format($total,2); ?></td></tr></tfoot>
        </table>
      </div>
    </div>
  </section>
</div>
