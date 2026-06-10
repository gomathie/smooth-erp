<?php

if (!Permission::has("sales")) {
  echo '<script>window.location = "home";</script>';
  return;
}

/*=============================================
HANDLE PAYMENT ACTIONS (inline, like the rest of the app)
=============================================*/
ControllerPayments::ctrAddPayment();
ControllerPayments::ctrEditPayment();
ControllerPayments::ctrDeletePayment();

/*=============================================
LOAD THE INVOICE
=============================================*/
$idInvoice = (int)($_GET["idInvoice"] ?? 0);
$invoice   = ControllerInvoices::ctrShowInvoices("id", $idInvoice);

if (!is_array($invoice)) {
  echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger" style="margin:20px;">Invoice not found.</div></section></div>';
  return;
}

$customer = ControllerCustomers::ctrShowCustomers("id", (int)$invoice["idCustomer"]);
$seller   = ControllerUsers::ctrShowUsers("id", (int)$invoice["idSeller"]);
$payments = ControllerPayments::ctrShowInvoicePayments($idInvoice);
$activity = ControllerInvoices::ctrShowActivity($idInvoice);
$items    = json_decode((string)$invoice["items"], true) ?: [];

$total      = (float)$invoice["totalPrice"];
$paid       = (float)$invoice["amountPaid"];
$balance    = (float)$invoice["balanceDue"];
$sym        = Currency::symbol($invoice["currency"] ?? Currency::base());
$curCode    = $invoice["currency"] ?? Currency::base();

/*=============================================
DERIVE DISPLAY STATUS (overdue is computed, never stored)
=============================================*/
$today     = date("Y-m-d");
$isOverdue = ($invoice["status"] !== "paid" && $invoice["status"] !== "draft"
              && $balance > 0 && !empty($invoice["dueDate"]) && $invoice["dueDate"] < $today);

$displayStatus = $isOverdue ? "overdue" : $invoice["status"];

$statusLabels = [
  "draft"          => "Draft",
  "sent"           => "Sent",
  "partially_paid" => "Partially Paid",
  "paid"           => "Paid",
  "overdue"        => "Overdue",
];
$statusLabel = $statusLabels[$displayStatus] ?? ucfirst($displayStatus);

$statusClass = match($displayStatus) {
  "paid"           => "label-success",
  "partially_paid" => "label-primary",
  "sent"           => "label-warning",
  "overdue"        => "label-danger",
  default          => "label-default",
};

$paymentTermsMap = [
  "due_on_receipt" => "Due on Receipt",
  "net_15" => "Net 15", "net_30" => "Net 30", "net_45" => "Net 45",
  "net_60" => "Net 60", "end_of_month" => "End of Month",
];
$paymentTermsLabel = $paymentTermsMap[$invoice["paymentTerms"] ?? "due_on_receipt"] ?? "Due on Receipt";

$paymentModeMap = [
  "cash" => "Cash", "card" => "Card", "bank_transfer" => "Bank Transfer",
  "cheque" => "Cheque", "online" => "Online",
];
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>Invoice #<?php echo htmlspecialchars($invoice["invoiceNumber"]); ?>
      <span class="label <?php echo $statusClass; ?>" style="font-size:14px; vertical-align:middle;"><?php echo $statusLabel; ?></span>
    </h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="invoices">Invoices</a></li>
      <li class="active">#<?php echo htmlspecialchars($invoice["invoiceNumber"]); ?></li>
    </ol>
  </section>

  <section class="content">

    <!-- ACTION BAR -->
    <div class="row">
      <div class="col-xs-12" style="margin-bottom:15px;">
        <?php if ($balance > 0) { ?>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRecordPayment">
            <i class="fa fa-money"></i> Record Payment
          </button>
        <?php } ?>
        <a class="btn btn-warning" href="extensions/tcpdf/pdf/invoice-pdf.php?id=<?php echo $idInvoice; ?>" target="_blank">
          <i class="fa fa-print"></i> Print PDF
        </a>
        <?php if ($_SESSION["profile"] == "Administrator") { ?>
          <a class="btn btn-primary" href="index.php?route=edit-invoice&idInvoice=<?php echo $idInvoice; ?>">
            <i class="fa fa-pencil"></i> Edit
          </a>
        <?php } ?>
        <a class="btn btn-default" href="invoices"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>

    <div class="row">

      <!-- LEFT: INVOICE DOCUMENT -->
      <div class="col-md-8">
        <div class="card card-primary card-outline">
          <div class="card-body">

            <div class="row">
              <div class="col-xs-6">
                <strong style="color:#888;">Bill To</strong>
                <p style="font-size:15px; margin:4px 0;"><?php echo htmlspecialchars($customer["name"] ?? "—"); ?></p>
                <?php if (!empty($customer["email"])) { echo '<p style="margin:0; color:#777;">'.htmlspecialchars($customer["email"]).'</p>'; } ?>
                <?php if (!empty($customer["phone"])) { echo '<p style="margin:0; color:#777;">'.htmlspecialchars($customer["phone"]).'</p>'; } ?>
              </div>
              <div class="col-xs-6 text-right">
                <p style="margin:2px 0;"><strong>Invoice Date:</strong> <?php echo substr((string)$invoice["invoiceDate"], 0, 10); ?></p>
                <p style="margin:2px 0;"><strong>Due Date:</strong> <?php echo $invoice["dueDate"] ?: "—"; ?></p>
                <p style="margin:2px 0;"><strong>Terms:</strong> <?php echo $paymentTermsLabel; ?></p>
                <p style="margin:2px 0;"><strong>Handled By:</strong> <?php echo htmlspecialchars($seller["name"] ?? "—"); ?></p>
                <p style="margin:2px 0;"><strong>Currency:</strong> <?php echo htmlspecialchars($curCode); ?></p>
                <?php if (!empty($invoice["orderReference"])) { echo '<p style="margin:2px 0;"><strong>Ref:</strong> '.htmlspecialchars($invoice["orderReference"]).'</p>'; } ?>
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
                  <tr><td style="color:#888;">Subtotal</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$invoice["subtotal"], 2); ?></td></tr>
                  <?php if ((float)$invoice["discount"] > 0) { ?><tr><td style="color:#888;">Discount</td><td class="text-right" style="color:#e74c3c;">- <?php echo $sym; ?> <?php echo number_format((float)$invoice["discount"], 2); ?></td></tr><?php } ?>
                  <?php if ((float)$invoice["shipping"] > 0) { ?><tr><td style="color:#888;">Shipping</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$invoice["shipping"], 2); ?></td></tr><?php } ?>
                  <?php if ((float)$invoice["adjustments"] != 0) { ?><tr><td style="color:#888;">Adjustment</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$invoice["adjustments"], 2); ?></td></tr><?php } ?>
                  <tr><td style="color:#888;">Tax</td><td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$invoice["tax"], 2); ?></td></tr>
                  <tr style="border-top:2px solid #1e3a5f;"><td><strong>Total</strong></td><td class="text-right"><strong><?php echo $sym; ?> <?php echo number_format($total, 2); ?></strong></td></tr>
                  <tr><td style="color:#27ae60;">Amount Paid</td><td class="text-right" style="color:#27ae60;"><?php echo $sym; ?> <?php echo number_format($paid, 2); ?></td></tr>
                  <tr style="background:#fcf8e3;"><td><strong>Balance Due</strong></td><td class="text-right"><strong><?php echo $sym; ?> <?php echo number_format($balance, 2); ?></strong></td></tr>
                </table>
              </div>
            </div>

            <?php if (!empty($invoice["notes"])) { ?>
              <div class="callout callout-info" style="margin-top:15px;"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($invoice["notes"])); ?></div>
            <?php } ?>
            <?php if (!empty($invoice["termsConditions"])) { ?>
              <div style="margin-top:10px; font-size:12px; color:#777;"><strong>Terms &amp; Conditions:</strong> <?php echo nl2br(htmlspecialchars($invoice["termsConditions"])); ?></div>
            <?php } ?>

          </div>
        </div>

        <!-- PAYMENTS RECEIVED -->
        <div class="card card-success card-outline">
          <div class="card-header"><h3 class="card-title"><i class="fa fa-money"></i> Payments Received</h3></div>
          <div class="card-body">
            <?php if (!$payments) { ?>
              <p class="text-muted">No payments recorded yet.</p>
            <?php } else { ?>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Payment #</th><th>Date</th><th>Mode</th><th>Reference</th>
                    <th class="text-right">Amount</th>
                    <?php if ($_SESSION["profile"] == "Administrator") { echo '<th class="text-right">Actions</th>'; } ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($payments as $p) {
                    $modeLabel = $paymentModeMap[$p["paymentMode"]] ?? ucfirst($p["paymentMode"]);
                  ?>
                    <tr>
                      <td><?php echo htmlspecialchars($p["paymentNumber"]); ?></td>
                      <td><?php echo $p["paymentDate"]; ?></td>
                      <td><?php echo $modeLabel; ?></td>
                      <td><?php echo htmlspecialchars($p["reference"] ?? ""); ?></td>
                      <td class="text-right"><?php echo $sym; ?> <?php echo number_format((float)$p["amount"], 2); ?></td>
                      <?php if ($_SESSION["profile"] == "Administrator") { ?>
                        <td class="text-right">
                          <button class="btn btn-xs btn-primary btnEditPayment"
                                  data-id="<?php echo $p["id"]; ?>"
                                  data-amount="<?php echo $p["amount"]; ?>"
                                  data-date="<?php echo $p["paymentDate"]; ?>"
                                  data-mode="<?php echo $p["paymentMode"]; ?>"
                                  data-reference="<?php echo htmlspecialchars($p["reference"] ?? "", ENT_QUOTES); ?>"
                                  data-notes="<?php echo htmlspecialchars($p["notes"] ?? "", ENT_QUOTES); ?>">
                            <i class="fa fa-pencil"></i>
                          </button>
                          <button class="btn btn-xs btn-danger btnDeletePayment"
                                  data-id="<?php echo $p["id"]; ?>"
                                  data-invoice="<?php echo $idInvoice; ?>">
                            <i class="fa fa-trash"></i>
                          </button>
                        </td>
                      <?php } ?>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            <?php } ?>
          </div>
        </div>

      </div>

      <!-- RIGHT: SUMMARY + ACTIVITY -->
      <div class="col-md-4">

        <div class="card">
          <div class="card-body text-center">
            <p style="color:#888; margin-bottom:4px;">Balance Due</p>
            <p style="font-size:32px; font-weight:bold; color:<?php echo $balance > 0 ? '#e74c3c' : '#27ae60'; ?>; margin:0;">
              <?php echo $sym; ?> <?php echo number_format($balance, 2); ?>
            </p>
            <p class="text-muted" style="margin-top:6px;">of <?php echo $sym; ?> <?php echo number_format($total, 2); ?> total</p>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="card-title"><i class="fa fa-history"></i> Activity Log</h3></div>
          <div class="card-body">
            <?php if (!$activity) { ?>
              <p class="text-muted">No activity yet.</p>
            <?php } else { ?>
              <ul class="list-unstyled" style="font-size:13px;">
                <?php foreach ($activity as $a) {
                  $actor = ControllerUsers::ctrShowUsers("id", (int)$a["idUser"]);
                  $actorName = is_array($actor) ? $actor["name"] : "System";
                ?>
                  <li style="border-left:2px solid #ddd; padding:4px 0 8px 10px; margin-left:4px;">
                    <div><?php echo htmlspecialchars($a["description"]); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($actorName); ?> &middot; <?php echo $a["createdDate"]; ?></small>
                  </li>
                <?php } ?>
              </ul>
            <?php } ?>
          </div>
        </div>

      </div>

    </div>

  </section>

</div>

<!-- ============ RECORD PAYMENT MODAL ============ -->
<div id="modalRecordPayment" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <div class="modal-header" style="background:#00a65a; color:#fff;">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Record Payment</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="recordPaymentInvoice" value="<?php echo $idInvoice; ?>">

          <div class="form-group">
            <label>Amount</label>
            <div class="input-group">
              <span class="input-group-addon">$</span>
              <input type="number" step="0.01" min="0.01" class="form-control" name="paymentAmount" value="<?php echo number_format($balance, 2, '.', ''); ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label>Payment Date</label>
            <input type="date" class="form-control" name="paymentDate" value="<?php echo date('Y-m-d'); ?>" required>
          </div>

          <div class="form-group">
            <label>Payment Mode</label>
            <select class="form-control" name="paymentMode">
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
              <option value="online">Online</option>
            </select>
          </div>

          <div class="form-group">
            <label>Reference (optional)</label>
            <input type="text" class="form-control" name="paymentReference" placeholder="Txn / cheque no.">
          </div>

          <div class="form-group">
            <label>Notes (optional)</label>
            <textarea class="form-control" name="paymentNotes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Payment</button>
          <button type="button" class="btn btn-default pull-left" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============ EDIT PAYMENT MODAL ============ -->
<div id="modalEditPayment" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="post">
        <div class="modal-header" style="background:#3c8dbc; color:#fff;">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Payment</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="editPayment" id="editPaymentId">

          <div class="form-group">
            <label>Amount</label>
            <div class="input-group">
              <span class="input-group-addon">$</span>
              <input type="number" step="0.01" min="0.01" class="form-control" name="paymentAmount" id="editPaymentAmount" required>
            </div>
          </div>

          <div class="form-group">
            <label>Payment Date</label>
            <input type="date" class="form-control" name="paymentDate" id="editPaymentDate" required>
          </div>

          <div class="form-group">
            <label>Payment Mode</label>
            <select class="form-control" name="paymentMode" id="editPaymentMode">
              <option value="cash">Cash</option>
              <option value="card">Card</option>
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
              <option value="online">Online</option>
            </select>
          </div>

          <div class="form-group">
            <label>Reference (optional)</label>
            <input type="text" class="form-control" name="paymentReference" id="editPaymentReference">
          </div>

          <div class="form-group">
            <label>Notes (optional)</label>
            <textarea class="form-control" name="paymentNotes" id="editPaymentNotes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Payment</button>
          <button type="button" class="btn btn-default pull-left" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
