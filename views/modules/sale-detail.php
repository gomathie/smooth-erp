<?php

if (!Permission::has("sales")) {
  echo '<script>window.location = "home";</script>';
  return;
}

$sale = ControllerSales::ctrShowSales("id", (int)($_GET["idSale"] ?? 0));

if (!is_array($sale)) {
  echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger" style="margin:20px;">Sale not found.</div></section></div>';
  return;
}

$customer = ControllerCustomers::ctrShowCustomers("id", (int)$sale["idCustomer"]);
$seller   = ControllerUsers::ctrShowUsers("id", (int)$sale["idSeller"]);
$items    = json_decode((string)$sale["products"], true) ?: [];

$net   = (float)$sale["netPrice"];
$tax   = (float)$sale["tax"];
$total = (float)$sale["totalPrice"];
$sym   = Currency::symbol($sale["currency"] ?? Currency::base());
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>Sale #<?php echo htmlspecialchars($sale["code"]); ?></h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="sales">Sales</a></li>
      <li class="active">#<?php echo htmlspecialchars($sale["code"]); ?></li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-xs-12" style="margin-bottom:15px;">
        <a class="btn btn-warning" href="extensions/tcpdf/pdf/bill.php?code=<?php echo urlencode($sale["code"]); ?>" target="_blank"><i class="fa fa-print"></i> Print Bill</a>
        <?php if ($_SESSION["profile"] == "Administrator") { ?>
          <a class="btn btn-primary" href="index.php?route=edit-sale&idSale=<?php echo $sale["id"]; ?>"><i class="fa fa-pencil"></i> Edit</a>
        <?php } ?>
        <a class="btn btn-default" href="sales"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="card card-primary card-outline">
          <div class="card-body">

            <div class="row">
              <div class="col-xs-6">
                <strong style="color:#888;">Customer</strong>
                <p style="font-size:15px; margin:4px 0;"><?php echo htmlspecialchars($customer["name"] ?? "—"); ?></p>
                <?php if (!empty($customer["email"])) { echo '<p style="margin:0; color:#777;">'.htmlspecialchars($customer["email"]).'</p>'; } ?>
                <?php if (!empty($customer["phone"])) { echo '<p style="margin:0; color:#777;">'.htmlspecialchars($customer["phone"]).'</p>'; } ?>
              </div>
              <div class="col-xs-6 text-right">
                <p style="margin:2px 0;"><strong>Date:</strong> <?php echo substr((string)$sale["saledate"], 0, 10); ?></p>
                <p style="margin:2px 0;"><strong>Seller:</strong> <?php echo htmlspecialchars($seller["name"] ?? "—"); ?></p>
                <p style="margin:2px 0;"><strong>Payment:</strong> <?php echo htmlspecialchars($sale["paymentMethod"]); ?></p>
              </div>
            </div>

            <hr>

            <table class="table table-bordered">
              <thead>
                <tr style="background:#f5f5f5;">
                  <th style="width:40px;">#</th>
                  <th>Description</th>
                  <th class="text-center">Qty</th>
                  <th class="text-right">Unit Price</th>
                  <th class="text-right">Line Total</th>
                </tr>
              </thead>
              <tbody>
                <?php $n = 1; foreach ($items as $it) { ?>
                  <tr>
                    <td><?php echo $n++; ?></td>
                    <td><?php echo htmlspecialchars($it["description"] ?? ""); ?></td>
                    <td class="text-center"><?php echo (int)($it["quantity"] ?? 0); ?></td>
                    <td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)($it["price"] ?? 0), 2); ?></td>
                    <td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)($it["totalPrice"] ?? 0), 2); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>

            <div class="row">
              <div class="col-xs-6 col-xs-offset-6">
                <table class="table" style="margin-bottom:0;">
                  <tr><td style="color:#888;">Net</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format($net, 2); ?></td></tr>
                  <tr><td style="color:#888;">Tax</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format($tax, 2); ?></td></tr>
                  <tr style="border-top:2px solid #1e3a5f;"><td><strong>Total</strong></td><td class="text-right"><strong><?php echo $sym; ?> <?php echo number_format($total, 2); ?></strong></td></tr>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <p style="color:#888; margin-bottom:4px;">Total</p>
            <p style="font-size:32px; font-weight:bold; color:#00a65a; margin:0;"><?php echo $sym; ?> <?php echo number_format($total, 2); ?></p>
            <p class="text-muted" style="margin-top:6px;"><?php echo count($items); ?> item(s) &middot; <?php echo htmlspecialchars($sale["paymentMethod"]); ?></p>
          </div>
        </div>
      </div>
    </div>

  </section>

</div>
