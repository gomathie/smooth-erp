<?php

if (!Permission::has("sales")) {
  echo '<script>window.location = "home";</script>';
  return;
}

$idCustomer = (int)($_GET["idCustomer"] ?? 0);
$customer   = ControllerCustomers::ctrShowCustomers("id", $idCustomer);

if (!is_array($customer)) {
  echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger" style="margin:20px;">Customer not found.</div></section></div>';
  return;
}

$invoices = ControllerInvoices::ctrInvoicesByCustomer($idCustomer);
$payments = ControllerPayments::ctrShowCustomerPayments($idCustomer);

/*=============================================
BUILD A UNIFIED, DATE-ORDERED LEDGER WITH RUNNING BALANCE
=============================================
Each non-draft invoice raises what the customer owes (debit); each payment
reduces it (credit). The running balance is the customer's A/R balance.
*/
$rows = [];

foreach ($invoices as $inv) {
  if ($inv["status"] === "draft") { continue; }
  $rows[] = [
    "date"    => substr((string)$inv["invoiceDate"], 0, 10),
    "type"    => "invoice",
    "ref"     => "Invoice #" . $inv["invoiceNumber"],
    "debit"   => (float)$inv["totalPrice"],
    "credit"  => 0.0,
    "link"    => "index.php?route=invoice-detail&idInvoice=" . $inv["id"],
  ];
}

foreach ($payments as $p) {
  $rows[] = [
    "date"    => $p["paymentDate"],
    "type"    => "payment",
    "ref"     => "Payment " . $p["paymentNumber"] . " (" . $p["paymentMode"] . ")",
    "debit"   => 0.0,
    "credit"  => (float)$p["amount"],
    "link"    => "index.php?route=invoice-detail&idInvoice=" . $p["idInvoice"],
  ];
}

usort($rows, function ($a, $b) {
  return [$a["date"], $a["type"]] <=> [$b["date"], $b["type"]];
});

$running       = 0.0;
$totalInvoiced = 0.0;
$totalPaid     = 0.0;
foreach ($rows as &$r) {
  $running += $r["debit"] - $r["credit"];
  $r["balance"]   = $running;
  $totalInvoiced += $r["debit"];
  $totalPaid     += $r["credit"];
}
unset($r);

$closingBalance = $running;
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>Customer Statement</h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="customers">Customers</a></li>
      <li class="active"><?php echo htmlspecialchars($customer["name"]); ?></li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-md-8">
        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-user"></i> <?php echo htmlspecialchars($customer["name"]); ?></h3>
          </div>
          <div class="card-body">
            <?php if (!empty($customer["email"])) { echo '<p style="margin:2px 0; color:#777;"><i class="fa fa-envelope"></i> '.htmlspecialchars($customer["email"]).'</p>'; } ?>
            <?php if (!empty($customer["phone"])) { echo '<p style="margin:2px 0; color:#777;"><i class="fa fa-phone"></i> '.htmlspecialchars($customer["phone"]).'</p>'; } ?>
            <?php if (!empty($customer["address"])) { echo '<p style="margin:2px 0; color:#777;"><i class="fa fa-map-marker"></i> '.htmlspecialchars($customer["address"]).'</p>'; } ?>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <p style="color:#888; margin-bottom:4px;">Closing Balance Due</p>
            <p style="font-size:30px; font-weight:bold; color:<?php echo $closingBalance > 0 ? '#e74c3c' : '#27ae60'; ?>; margin:0;">
              $ <?php echo number_format($closingBalance, 2); ?>
            </p>
            <p class="text-muted" style="margin-top:6px;">
              Invoiced $ <?php echo number_format($totalInvoiced, 2); ?> &middot; Paid $ <?php echo number_format($totalPaid, 2); ?>
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3 class="card-title"><i class="fa fa-list"></i> Account Activity</h3></div>
      <div class="card-body">
        <?php if (!$rows) { ?>
          <p class="text-muted">No invoices or payments on record for this customer yet.</p>
        <?php } else { ?>
          <table class="table table-bordered table-striped">
            <thead>
              <tr style="background:#f5f5f5;">
                <th>Date</th>
                <th>Detail</th>
                <th class="text-right">Invoiced</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Balance</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r) { ?>
                <tr>
                  <td><?php echo $r["date"]; ?></td>
                  <td><a href="<?php echo $r["link"]; ?>"><?php echo htmlspecialchars($r["ref"]); ?></a></td>
                  <td class="text-right"><?php echo $r["debit"]  > 0 ? '$ ' . number_format($r["debit"], 2)  : ''; ?></td>
                  <td class="text-right" style="color:#27ae60;"><?php echo $r["credit"] > 0 ? '$ ' . number_format($r["credit"], 2) : ''; ?></td>
                  <td class="text-right"><strong>$ <?php echo number_format($r["balance"], 2); ?></strong></td>
                </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr style="font-weight:bold; background:#f9f9f9;">
                <td colspan="2" class="text-right">Closing Balance</td>
                <td class="text-right">$ <?php echo number_format($totalInvoiced, 2); ?></td>
                <td class="text-right">$ <?php echo number_format($totalPaid, 2); ?></td>
                <td class="text-right">$ <?php echo number_format($closingBalance, 2); ?></td>
              </tr>
            </tfoot>
          </table>
        <?php } ?>
      </div>
    </div>

  </section>

</div>
