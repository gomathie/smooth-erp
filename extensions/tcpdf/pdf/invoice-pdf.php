<?php
ob_start();

require_once "../../../controllers/invoices.controller.php";
require_once "../../../models/invoices.model.php";

require_once "../../../controllers/customers.controller.php";
require_once "../../../models/customers.model.php";

require_once "../../../controllers/users.controller.php";
require_once "../../../models/users.model.php";

require_once('tcpdf_include.php');

class PrintInvoice {

    public $invoiceId;

    public function generate() {

        $invoice = ControllerInvoices::ctrShowInvoices("id", $this->invoiceId);

        if (!is_array($invoice)) {
            die('Invoice not found.');
        }

        $invoiceNumber  = (string)$invoice["invoiceNumber"];
        $orderReference = htmlspecialchars((string)($invoice["orderReference"] ?? ""));
        $invoiceDate    = substr((string)$invoice["invoiceDate"], 0, 10);
        $dueDate        = ($invoice["dueDate"] && $invoice["dueDate"] !== "0000-00-00") ? (string)$invoice["dueDate"] : "";
        $dueDateDisplay = $dueDate ?: "—";

        $paymentTermsMap = [
            "due_on_receipt" => "Due on Receipt",
            "net_15"         => "Net 15",
            "net_30"         => "Net 30",
            "net_45"         => "Net 45",
            "net_60"         => "Net 60",
            "end_of_month"   => "End of Month",
        ];
        $paymentTerms = $paymentTermsMap[$invoice["paymentTerms"] ?? "due_on_receipt"] ?? "Due on Receipt";

        $amountPaid = (float)($invoice["amountPaid"] ?? 0);
        $balanceDue = (float)($invoice["balanceDue"] ?? $invoice["totalPrice"]);
        $cur        = $invoice["currency"] ?? "USD";  // currency code shown on the PDF

        // Overdue: computed, never stored
        $today     = date("Y-m-d");
        $isOverdue = ($invoice["status"] !== "paid" && $invoice["status"] !== "draft"
                      && $balanceDue > 0 && $dueDate && $dueDate < $today);

        $statusLabels = [
            "draft" => "Draft", "sent" => "Sent", "partially_paid" => "Partially Paid", "paid" => "Paid",
        ];
        $displayStatus = $isOverdue ? "Overdue" : ($statusLabels[$invoice["status"]] ?? ucfirst((string)$invoice["status"]));
        $statusColor   = "#888888";
        if ($invoice["status"] === "paid")           { $statusColor = "#27ae60"; }
        if ($invoice["status"] === "sent")           { $statusColor = "#e67e22"; }
        if ($invoice["status"] === "partially_paid") { $statusColor = "#3c8dbc"; }
        if ($isOverdue)                              { $statusColor = "#e74c3c"; }

        $amountPaidFmt = number_format($amountPaid, 2);
        $balanceDueFmt = number_format($balanceDue, 2);

        $items          = json_decode((string)$invoice["items"], true) ?? [];
        $subtotal       = number_format((float)($invoice["subtotal"]    ?? 0), 2);
        $discount       = number_format((float)($invoice["discount"]    ?? 0), 2);
        $shipping       = number_format((float)($invoice["shipping"]    ?? 0), 2);
        $adjustments    = number_format((float)($invoice["adjustments"] ?? 0), 2);
        $netPrice       = number_format((float)$invoice["netPrice"],   2);
        $taxAmount      = number_format((float)$invoice["tax"],        2);
        $totalPrice     = number_format((float)$invoice["totalPrice"], 2);
        $notes          = htmlspecialchars((string)($invoice["notes"]          ?? ""));
        $termsConditions= htmlspecialchars((string)($invoice["termsConditions"] ?? ""));

        $customer     = ControllerCustomers::ctrShowCustomers("id", (int)$invoice["idCustomer"]);
        $seller       = ControllerUsers::ctrShowUsers("id",         (int)$invoice["idSeller"]);
        $customerName = htmlspecialchars($customer["name"] ?? "");
        $sellerName   = htmlspecialchars($seller["name"]   ?? "");

        $companyName    = "Smooth ERP";
        $companyAddress = "86 Bel Meadow Drive";
        $companyPhone   = "300 786 52 49";
        $companyEmail   = "info@smoothpos.com";

        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage('P', 'A4');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetFont('helvetica', '', 10);

        // HEADER BLOCK
        $refLine  = $orderReference ? "<br/><span style=\"font-size:9px; color:#777;\">Ref: {$orderReference}</span>" : "";
        $header = <<<HTML
<table cellpadding="3" cellspacing="0" style="width:100%">
  <tr>
    <td width="55%" style="font-size:18px; font-weight:bold; color:#1e3a5f;">
      {$companyName}
      <br/><span style="font-size:9px; font-weight:normal; color:#555;">{$companyAddress}</span>
      <br/><span style="font-size:9px; font-weight:normal; color:#555;">Tel: {$companyPhone} | {$companyEmail}</span>
    </td>
    <td width="45%" style="text-align:right;">
      <span style="font-size:26px; font-weight:bold; color:#1e3a5f; letter-spacing:2px;">INVOICE</span>
      <br/><span style="font-size:11px; color:#333;"># {$invoiceNumber}</span>{$refLine}
      <br/><span style="font-size:9px; color:#777;">Date: {$invoiceDate}</span>
      <br/><span style="font-size:9px; color:#777;">Due: {$dueDateDisplay} &nbsp;|&nbsp; {$paymentTerms}</span>
      <br/><span style="font-size:9px; font-weight:bold; color:{$statusColor};">{$displayStatus}</span>
    </td>
  </tr>
</table>
HTML;

        $pdf->writeHTML($header, true, false, true, false, '');
        $pdf->SetDrawColor(30, 58, 95);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(15, $pdf->GetY() + 3, 195, $pdf->GetY() + 3);
        $pdf->Ln(6);

        // BILL TO / SELLER
        $billBlock = <<<HTML
<table cellpadding="5" cellspacing="0" style="width:100%; background-color:#f4f7fb; border:1px solid #dde3ef;">
  <tr>
    <td width="50%" style="font-size:9px;">
      <strong style="font-size:10px; color:#1e3a5f;">Bill To</strong><br/>{$customerName}
    </td>
    <td width="50%" style="font-size:9px; text-align:right;">
      <strong style="font-size:10px; color:#1e3a5f;">Handled By</strong><br/>{$sellerName}
    </td>
  </tr>
</table>
HTML;

        $pdf->writeHTML($billBlock, true, false, true, false, '');
        $pdf->Ln(5);

        // ITEMS TABLE
        $itemsTable = '<table cellpadding="5" cellspacing="0" style="width:100%; font-size:9px;">'
            . '<tr style="background-color:#1e3a5f; color:#ffffff;">'
            . '<td width="5%"  align="center"><strong>#</strong></td>'
            . '<td width="49%"><strong>Description</strong></td>'
            . '<td width="12%" align="center"><strong>Qty</strong></td>'
            . '<td width="17%" align="right"><strong>Unit Price</strong></td>'
            . '<td width="17%" align="right"><strong>Line Total</strong></td>'
            . '</tr>';

        $rowNum = 1;
        foreach ($items as $item) {
            $desc      = htmlspecialchars($item["description"] ?? "");
            $qty       = (int)($item["quantity"] ?? 0);
            $unitPrice = number_format((float)($item["price"] ?? 0), 2);
            $lineTotal = number_format((float)($item["totalPrice"] ?? 0), 2);
            $bg        = ($rowNum % 2 === 0) ? "#f5f5f5" : "#ffffff";

            $itemsTable .= '<tr style="background-color:'.$bg.';">'
                . '<td width="5%"  align="center" style="color:#888;">'.$rowNum.'</td>'
                . '<td width="49%">'.$desc.'</td>'
                . '<td width="12%" align="center">'.$qty.'</td>'
                . '<td width="17%" align="right">' . $cur . ' '.$unitPrice.'</td>'
                . '<td width="17%" align="right">' . $cur . ' '.$lineTotal.'</td>'
                . '</tr>';
            $rowNum++;
        }

        $itemsTable .= '</table>';
        $pdf->writeHTML($itemsTable, true, false, true, false, '');
        $pdf->Ln(4);

        // TOTALS — full breakdown
        $discountRow    = ((float)($invoice["discount"]    ?? 0) > 0) ? "<tr><td width=\"60%\"></td><td width=\"20%\" align=\"right\" style=\"color:#555;\">Discount:</td><td width=\"20%\" align=\"right\" style=\"color:#e74c3c;\">- {$cur} {$discount}</td></tr>" : "";
        $shippingRow    = ((float)($invoice["shipping"]    ?? 0) > 0) ? "<tr><td width=\"60%\"></td><td width=\"20%\" align=\"right\" style=\"color:#555;\">Shipping:</td><td width=\"20%\" align=\"right\">{$cur} {$shipping}</td></tr>" : "";
        $adjustmentsRow = ((float)($invoice["adjustments"] ?? 0) != 0) ? "<tr><td width=\"60%\"></td><td width=\"20%\" align=\"right\" style=\"color:#555;\">Adjustment:</td><td width=\"20%\" align=\"right\">{$cur} {$adjustments}</td></tr>" : "";

        $totals = <<<HTML
<table cellpadding="4" cellspacing="0" style="width:100%; font-size:9px;">
  <tr>
    <td width="60%"></td>
    <td width="20%" align="right" style="color:#555;">Subtotal:</td>
    <td width="20%" align="right">{$cur} {$subtotal}</td>
  </tr>
  {$discountRow}
  {$shippingRow}
  {$adjustmentsRow}
  <tr>
    <td width="60%"></td>
    <td width="20%" align="right" style="color:#555;">Net:</td>
    <td width="20%" align="right">{$cur} {$netPrice}</td>
  </tr>
  <tr>
    <td width="60%"></td>
    <td width="20%" align="right" style="color:#555;">Tax:</td>
    <td width="20%" align="right">{$cur} {$taxAmount}</td>
  </tr>
  <tr>
    <td width="60%"></td>
    <td colspan="2" style="border-top:1px solid #1e3a5f; padding:1px;"></td>
  </tr>
  <tr>
    <td width="60%"></td>
    <td width="20%" align="right" style="font-size:11px; font-weight:bold; color:#1e3a5f;">TOTAL:</td>
    <td width="20%" align="right" style="font-size:11px; font-weight:bold; color:#1e3a5f;">{$cur} {$totalPrice}</td>
  </tr>
  <tr>
    <td width="60%"></td>
    <td width="20%" align="right" style="color:#27ae60;">Amount Paid:</td>
    <td width="20%" align="right" style="color:#27ae60;">{$cur} {$amountPaidFmt}</td>
  </tr>
  <tr>
    <td width="60%"></td>
    <td width="20%" align="right" style="font-size:11px; font-weight:bold; color:#1e3a5f;">BALANCE DUE:</td>
    <td width="20%" align="right" style="font-size:11px; font-weight:bold; color:#1e3a5f;">{$cur} {$balanceDueFmt}</td>
  </tr>
</table>
HTML;

        $pdf->writeHTML($totals, true, false, true, false, '');

        // NOTES
        if ($notes) {
            $notesBlock = <<<HTML
<table cellpadding="5" cellspacing="0" style="width:100%; margin-top:6px; background-color:#fafafa; border:1px solid #e0e0e0;">
  <tr>
    <td style="font-size:9px;"><strong>Notes:</strong><br/>{$notes}</td>
  </tr>
</table>
HTML;
            $pdf->writeHTML($notesBlock, true, false, true, false, '');
        }

        // TERMS & CONDITIONS
        if ($termsConditions) {
            $termsBlock = <<<HTML
<table cellpadding="5" cellspacing="0" style="width:100%; margin-top:6px; background-color:#fafafa; border:1px solid #e0e0e0;">
  <tr>
    <td style="font-size:9px;"><strong>Terms &amp; Conditions:</strong><br/>{$termsConditions}</td>
  </tr>
</table>
HTML;
            $pdf->writeHTML($termsBlock, true, false, true, false, '');
        }

        // FOOTER
        $pdf->Ln(8);
        $pdf->writeHTML('<p style="text-align:center; font-size:8px; color:#aaa;">Thank you for your business! &mdash; ' . $companyName . '</p>', true, false, true, false, '');

        ob_end_clean();
        $pdf->Output('invoice-' . $invoiceNumber . '.pdf');
    }
}

$print = new PrintInvoice();
$print->invoiceId = (int)($_GET["id"] ?? 0);
$print->generate();
?>
