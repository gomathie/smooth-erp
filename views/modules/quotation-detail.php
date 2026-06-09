<?php

if ($_SESSION["profile"] == "Special") {
  echo '<script>window.location = "home";</script>';
  return;
}

$quote = ControllerQuotations::ctrShowQuotations("id", (int)($_GET["idQuotation"] ?? 0));

if (!is_array($quote)) {
  echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger" style="margin:20px;">Quotation not found.</div></section></div>';
  return;
}

$customer = ControllerCustomers::ctrShowCustomers("id", (int)$quote["idCustomer"]);
$seller   = ControllerUsers::ctrShowUsers("id", (int)$quote["idSeller"]);
$items    = json_decode((string)$quote["items"], true) ?: [];
$sym      = Currency::symbol($quote["currency"] ?? Currency::base());
$curCode  = $quote["currency"] ?? Currency::base();

$today     = date("Y-m-d");
$isExpired = ($quote["status"] !== "invoiced" && $quote["status"] !== "declined"
              && !empty($quote["expiryDate"]) && $quote["expiryDate"] < $today);
$displayStatus = $isExpired ? "expired" : $quote["status"];

$statusLabels = ["draft"=>"Draft","sent"=>"Sent","accepted"=>"Accepted","declined"=>"Declined","expired"=>"Expired","invoiced"=>"Invoiced"];
$statusLabel  = $statusLabels[$displayStatus] ?? ucfirst($displayStatus);
$statusClass  = match($displayStatus) {
  "accepted" => "label-success",
  "invoiced" => "label-primary",
  "sent"     => "label-warning",
  "declined" => "label-danger",
  "expired"  => "label-danger",
  default    => "label-default",
};

$discountLabel = $quote["discountType"] === "percent"
  ? number_format((float)$quote["discountValue"], 2) . "%"
  : "$ " . number_format((float)$quote["discount"], 2);
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>Quotation #<?php echo htmlspecialchars($quote["quoteNumber"]); ?>
      <span class="label <?php echo $statusClass; ?>" style="font-size:14px; vertical-align:middle;"><?php echo $statusLabel; ?></span>
    </h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="quotations">Quotations</a></li>
      <li class="active">#<?php echo htmlspecialchars($quote["quoteNumber"]); ?></li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-xs-12" style="margin-bottom:15px;">
        <a class="btn btn-warning" href="extensions/tcpdf/pdf/quotation-pdf.php?id=<?php echo $quote["id"]; ?>" target="_blank"><i class="fa fa-print"></i> Print PDF</a>
        <?php if ($quote["status"] !== "invoiced") { ?>
          <a class="btn btn-success" href="index.php?route=quotations&convertQuote=<?php echo $quote["id"]; ?>" onclick="return confirm('Convert this quotation to a draft invoice?');"><i class="fa fa-exchange"></i> Convert to Invoice</a>
          <a class="btn btn-primary" href="index.php?route=edit-quotation&idQuotation=<?php echo $quote["id"]; ?>"><i class="fa fa-pencil"></i> Edit</a>
        <?php } else if (!empty($quote["idInvoice"])) { ?>
          <a class="btn btn-primary" href="index.php?route=invoice-detail&idInvoice=<?php echo $quote["idInvoice"]; ?>"><i class="fa fa-file-text-o"></i> View Invoice</a>
        <?php } ?>
        <a class="btn btn-default" href="quotations"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <div class="box box-primary">
          <div class="box-body">

            <div class="row">
              <div class="col-xs-6">
                <strong style="color:#888;">Prepared For</strong>
                <p style="font-size:15px; margin:4px 0;"><?php echo htmlspecialchars($customer["name"] ?? "—"); ?></p>
                <?php if (!empty($customer["email"])) { echo '<p style="margin:0; color:#777;">'.htmlspecialchars($customer["email"]).'</p>'; } ?>
                <?php if (!empty($customer["phone"])) { echo '<p style="margin:0; color:#777;">'.htmlspecialchars($customer["phone"]).'</p>'; } ?>
              </div>
              <div class="col-xs-6 text-right">
                <p style="margin:2px 0;"><strong>Date:</strong> <?php echo substr((string)$quote["quoteDate"], 0, 10); ?></p>
                <p style="margin:2px 0;"><strong>Valid Until:</strong> <?php echo $quote["expiryDate"] ?: "—"; ?></p>
                <p style="margin:2px 0;"><strong>Prepared By:</strong> <?php echo htmlspecialchars($seller["name"] ?? "—"); ?></p>
                <p style="margin:2px 0;"><strong>Currency:</strong> <?php echo htmlspecialchars($curCode); ?></p>
                <?php if (!empty($quote["orderReference"])) { echo '<p style="margin:2px 0;"><strong>Ref:</strong> '.htmlspecialchars($quote["orderReference"]).'</p>'; } ?>
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
                <?php $n = 1; foreach ($items as $it) {
                  $isService = empty($it["id"]);
                ?>
                  <tr>
                    <td><?php echo $n++; ?></td>
                    <td><?php echo htmlspecialchars($it["description"] ?? ""); ?><?php echo $isService ? ' <span class="label label-default">service</span>' : ''; ?></td>
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
                  <tr><td style="color:#888;">Subtotal</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$quote["subtotal"], 2); ?></td></tr>
                  <?php if ((float)$quote["discount"] > 0) { ?><tr><td style="color:#888;">Discount (<?php echo $discountLabel; ?>)</td><td class="text-right" style="color:#e74c3c;">- <?php echo $sym; ?> <?php echo number_format((float)$quote["discount"], 2); ?></td></tr><?php } ?>
                  <?php if ((float)$quote["shipping"] > 0) { ?><tr><td style="color:#888;">Shipping</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$quote["shipping"], 2); ?></td></tr><?php } ?>
                  <?php if ((float)$quote["adjustments"] != 0) { ?><tr><td style="color:#888;">Adjustment</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$quote["adjustments"], 2); ?></td></tr><?php } ?>
                  <tr><td style="color:#888;">Tax</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$quote["tax"], 2); ?></td></tr>
                  <tr style="border-top:2px solid #1e3a5f;"><td><strong>Total</strong></td><td class="text-right"><strong><?php echo $sym; ?> <?php echo number_format((float)$quote["totalPrice"], 2); ?></strong></td></tr>
                </table>
              </div>
            </div>

            <?php if (!empty($quote["notes"])) { ?>
              <div class="callout callout-info" style="margin-top:15px;"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($quote["notes"])); ?></div>
            <?php } ?>
            <?php if (!empty($quote["termsConditions"])) { ?>
              <div style="margin-top:10px; font-size:12px; color:#777;"><strong>Terms &amp; Conditions:</strong> <?php echo nl2br(htmlspecialchars($quote["termsConditions"])); ?></div>
            <?php } ?>

          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="box box-widget">
          <div class="box-body text-center">
            <p style="color:#888; margin-bottom:4px;">Quotation Total</p>
            <p style="font-size:32px; font-weight:bold; color:#1e3a5f; margin:0;"><?php echo $sym; ?> <?php echo number_format((float)$quote["totalPrice"], 2); ?></p>
            <p class="text-muted" style="margin-top:6px;"><?php echo $statusLabel; ?><?php echo $quote["expiryDate"] ? ' &middot; valid until '.$quote["expiryDate"] : ''; ?></p>
          </div>
        </div>
      </div>
    </div>

  </section>

</div>
