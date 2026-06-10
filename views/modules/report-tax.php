<?php
if (!Permission::has("reports")) { echo '<script>window.location="home";</script>'; return; }
if (!ControllerSettings::ctrAccountingEnabled()) { echo '<script>window.location="home";</script>'; return; }
$reportRoute = "report-tax";
$range = ControllerReports::ctrRange();
$invoices = ControllerReports::ctrInvoices($range["from"], $range["to"]);
$sales    = ControllerReports::ctrSales($range["from"], $range["to"]);

$invoiceTax = 0.0; $invoiceNet = 0.0; $taxedInvoices = [];
foreach ($invoices as $i) {
  if ($i["status"] === "draft") { continue; }
  $invoiceTax += (float)$i["tax"];
  $invoiceNet += (float)$i["netPrice"];
  if ((float)$i["tax"] > 0) { $taxedInvoices[] = $i; }
}
$salesTax = array_sum(array_map(fn($s) => (float)$s["tax"], $sales));
$salesNet = array_sum(array_map(fn($s) => (float)$s["netPrice"], $sales));
$totalTax = $invoiceTax + $salesTax;

// Tax Payable balance (point-in-time)
$taxPayable = 0.0;
foreach (ControllerAccounting::ctrTrialBalance() as $a) {
  if ($a["code"] === "2200") { $taxPayable = (float)$a["balance"]; }
}
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Tax Summary <small>Report</small></h1>
    <ol class="breadcrumb"><li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Tax Summary</li></ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="row">
      <div class="col-md-3 col-sm-6"><div class="small-box bg-blue"><div class="inner"><h3>$ <?php echo number_format($invoiceTax,2); ?></h3><p>Tax on Invoices</p></div><div class="icon"><i class="fa fa-file-text"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-aqua"><div class="inner"><h3>$ <?php echo number_format($salesTax,2); ?></h3><p>Tax on POS Sales</p></div><div class="icon"><i class="fa fa-shopping-cart"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-green"><div class="inner"><h3>$ <?php echo number_format($totalTax,2); ?></h3><p>Total Tax Collected</p></div><div class="icon"><i class="fa fa-percent"></i></div></div></div>
      <div class="col-md-3 col-sm-6"><div class="small-box bg-red"><div class="inner"><h3>$ <?php echo number_format($taxPayable,2); ?></h3><p>Tax Payable <small>(current)</small></p></div><div class="icon"><i class="fa fa-balance-scale"></i></div></div></div>
    </div>

    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Tax basis (period)</h3></div>
      <div class="box-body">
        <table class="table table-bordered">
          <thead><tr><th>Source</th><th class="text-right">Net (taxable)</th><th class="text-right">Tax</th></tr></thead>
          <tbody>
            <tr><td>Invoices (non-draft)</td><td class="text-right">$ <?php echo number_format($invoiceNet,2); ?></td><td class="text-right">$ <?php echo number_format($invoiceTax,2); ?></td></tr>
            <tr><td>POS Sales</td><td class="text-right">$ <?php echo number_format($salesNet,2); ?></td><td class="text-right">$ <?php echo number_format($salesTax,2); ?></td></tr>
          </tbody>
          <tfoot><tr style="font-weight:bold;background:#f9f9f9;"><td class="text-right">Total</td><td class="text-right">$ <?php echo number_format($invoiceNet+$salesNet,2); ?></td><td class="text-right">$ <?php echo number_format($totalTax,2); ?></td></tr></tfoot>
        </table>
      </div>
    </div>

    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Taxed Invoices</h3></div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive" width="100%">
          <thead><tr><th>Invoice #</th><th>Date</th><th class="text-right">Net</th><th class="text-right">Tax</th><th class="text-right">Total</th></tr></thead>
          <tbody>
            <?php foreach ($taxedInvoices as $i) { ?>
              <tr>
                <td><a href="index.php?route=invoice-detail&idInvoice=<?php echo $i["id"]; ?>">#<?php echo htmlspecialchars($i["invoiceNumber"]); ?></a></td>
                <td><?php echo substr($i["invoiceDate"],0,10); ?></td>
                <td class="text-right">$ <?php echo number_format($i["netPrice"],2); ?></td>
                <td class="text-right">$ <?php echo number_format($i["tax"],2); ?></td>
                <td class="text-right">$ <?php echo number_format($i["totalPrice"],2); ?></td>
              </tr>
            <?php } if (!$taxedInvoices) { echo '<tr><td colspan="5" class="text-muted">No taxed invoices in range</td></tr>'; } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
