<?php
if (!Permission::has("reports")) { echo '<script>window.location="home";</script>'; return; }
$reportRoute = "report-inventory";
$range = ControllerReports::ctrRange();
$movements = ControllerReports::ctrMovements($range["from"], $range["to"]);

$products = ControllerProducts::ctrShowProducts(null, null, "id") ?: [];
$invValue = 0.0; $units = 0; $lowStock = 0;
foreach ($products as $p) {
  if (($p["type"] ?? "good") === "service") { continue; }
  $invValue += (float)$p["stock"] * (float)$p["buyingPrice"];
  $units += (int)$p["stock"];
  if ((int)$p["stock"] <= 10) { $lowStock++; }
}

$srcLabels = ["opening"=>"Opening","sale"=>"Sale","sale_reversal"=>"Sale reversal","sale_edit"=>"Sale edit","invoice"=>"Invoice","adjustment"=>"Adjustment"];
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Inventory <small>Report</small></h1>
    <ol class="breadcrumb"><li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Inventory</li></ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="row">
      <div class="col-md-4"><div class="small-box bg-purple"><div class="inner"><h3>$ <?php echo number_format($invValue,2); ?></h3><p>Inventory Value <small>(at cost)</small></p></div><div class="icon"><i class="fa fa-cubes"></i></div></div></div>
      <div class="col-md-4"><div class="small-box bg-aqua"><div class="inner"><h3><?php echo $units; ?></h3><p>Units in Stock</p></div><div class="icon"><i class="fa fa-archive"></i></div></div></div>
      <div class="col-md-4"><div class="small-box bg-yellow"><div class="inner"><h3><?php echo $lowStock; ?></h3><p>Low Stock (&le;10)</p></div><div class="icon"><i class="fa fa-exclamation-triangle"></i></div></div></div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Stock Valuation <small>(current)</small></h3></div>
          <div class="card-body">
            <table class="table table-bordered table-striped dt-responsive" width="100%">
              <thead><tr><th>Code</th><th>Product</th><th class="text-right">Stock</th><th class="text-right">Unit Cost</th><th class="text-right">Value</th></tr></thead>
              <tbody>
                <?php foreach ($products as $p) { if (($p["type"] ?? "good")==="service") continue; ?>
                  <tr>
                    <td><?php echo htmlspecialchars($p["code"]); ?></td>
                    <td><?php echo htmlspecialchars($p["description"]); ?></td>
                    <td class="text-right"><?php echo (int)$p["stock"]; ?></td>
                    <td class="text-right">$ <?php echo number_format($p["buyingPrice"],2); ?></td>
                    <td class="text-right">$ <?php echo number_format((float)$p["stock"]*(float)$p["buyingPrice"],2); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
              <tfoot><tr style="font-weight:bold;background:#f9f9f9;"><td colspan="4" class="text-right">Total</td><td class="text-right">$ <?php echo number_format($invValue,2); ?></td></tr></tfoot>
            </table>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Stock Movements <small>(period)</small></h3></div>
          <div class="card-body">
            <table class="table table-bordered table-striped dt-responsive" width="100%">
              <thead><tr><th>Date</th><th>Product</th><th>Source</th><th class="text-right">Qty</th><th class="text-right">Unit Cost</th></tr></thead>
              <tbody>
                <?php foreach ($movements as $m) { ?>
                  <tr>
                    <td><?php echo $m["movementDate"]; ?></td>
                    <td><?php echo htmlspecialchars(($m["code"] ?? "")." ".($m["description"] ?? "")); ?></td>
                    <td><?php echo $srcLabels[$m["sourceType"]] ?? htmlspecialchars($m["sourceType"]); ?></td>
                    <td class="text-right" style="color:<?php echo $m["qtyChange"]<0?'#dd4b39':'#00a65a'; ?>;"><?php echo (int)$m["qtyChange"] > 0 ? '+' : ''; ?><?php echo (int)$m["qtyChange"]; ?></td>
                    <td class="text-right">$ <?php echo number_format($m["unitCost"],2); ?></td>
                  </tr>
                <?php } if (!$movements) { echo '<tr><td colspan="5" class="text-muted">No movements in range</td></tr>'; } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
