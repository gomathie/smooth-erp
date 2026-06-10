<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }


require_once "../../../controllers/quotations.controller.php";
require_once "../../../models/quotations.model.php";
require_once "../../../controllers/customers.controller.php";
require_once "../../../models/customers.model.php";
require_once "../../../controllers/users.controller.php";
require_once "../../../models/users.model.php";

require_once "../../../models/organizations.model.php";
require_once "../../../helpers/branding.php";

require_once('tcpdf_include.php');

class PrintQuotation {

    public $quoteId;

    public function generate() {

        $q = ControllerQuotations::ctrShowQuotations("id", $this->quoteId);

        if (!is_array($q)) {
            die('Quotation not found.');
        }

        $quoteNumber    = (string)$q["quoteNumber"];
        $orderReference = htmlspecialchars((string)($q["orderReference"] ?? ""));
        $quoteDate      = substr((string)$q["quoteDate"], 0, 10);
        $expiry         = ($q["expiryDate"] && $q["expiryDate"] !== "0000-00-00") ? (string)$q["expiryDate"] : "";
        $expiryDisplay  = $expiry ?: "—";

        $today     = date("Y-m-d");
        $isExpired = ($q["status"] !== "invoiced" && $q["status"] !== "declined" && $expiry && $expiry < $today);

        $statusLabels = ["draft"=>"Draft","sent"=>"Sent","accepted"=>"Accepted","declined"=>"Declined","invoiced"=>"Invoiced"];
        $displayStatus = $isExpired ? "Expired" : ($statusLabels[$q["status"]] ?? ucfirst((string)$q["status"]));
        $statusColor   = "#888888";
        if ($q["status"] === "accepted") { $statusColor = "#27ae60"; }
        if ($q["status"] === "sent")     { $statusColor = "#e67e22"; }
        if ($q["status"] === "invoiced") { $statusColor = "#3c8dbc"; }
        if ($isExpired || $q["status"] === "declined") { $statusColor = "#e74c3c"; }

        $items       = json_decode((string)$q["items"], true) ?? [];
        $subtotal    = number_format((float)($q["subtotal"]    ?? 0), 2);
        $discount    = number_format((float)($q["discount"]    ?? 0), 2);
        $shipping    = number_format((float)($q["shipping"]    ?? 0), 2);
        $adjustments = number_format((float)($q["adjustments"] ?? 0), 2);
        $netPrice    = number_format((float)$q["netPrice"], 2);
        $taxAmount   = number_format((float)$q["tax"], 2);
        $totalPrice  = number_format((float)$q["totalPrice"], 2);
        $cur         = $q["currency"] ?? "USD";  // currency code shown on the PDF
        $notes       = htmlspecialchars((string)($q["notes"] ?? ""));
        $terms       = htmlspecialchars((string)($q["termsConditions"] ?? ""));

        $customer     = ControllerCustomers::ctrShowCustomers("id", (int)$q["idCustomer"]);
        $seller       = ControllerUsers::ctrShowUsers("id", (int)$q["idSeller"]);
        $customerName = htmlspecialchars($customer["name"] ?? "");
        $sellerName   = htmlspecialchars($seller["name"] ?? "");

        $brand   = org_branding(ModelOrganizations::mdlGetOrganization((int)($q["idOrganization"] ?? 1)));
        $theme   = $brand["hex"];
        $companyName    = $brand["name"];
        $companyAddress = $brand["address"];
        $companyContact = $brand["contact"];
        $logoHtml = $brand["logo"] ? '<img src="' . $brand["logo"] . '" height="42"><br/>' : '';

        $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage('P', 'A4');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetFont('helvetica', '', 10);

        $refLine = $orderReference ? "<br/><span style=\"font-size:9px; color:#777;\">Ref: {$orderReference}</span>" : "";
        $header = <<<HTML
<table cellpadding="3" cellspacing="0" style="width:100%">
  <tr>
    <td width="55%" style="font-size:18px; font-weight:bold; color:{$theme};">
      {$logoHtml}{$companyName}
      <br/><span style="font-size:9px; font-weight:normal; color:#555;">{$companyAddress}</span>
      <br/><span style="font-size:9px; font-weight:normal; color:#555;">{$companyContact}</span>
    </td>
    <td width="45%" style="text-align:right;">
      <span style="font-size:24px; font-weight:bold; color:{$theme}; letter-spacing:2px;">QUOTATION</span>
      <br/><span style="font-size:11px; color:#333;"># {$quoteNumber}</span>{$refLine}
      <br/><span style="font-size:9px; color:#777;">Date: {$quoteDate}</span>
      <br/><span style="font-size:9px; color:#777;">Valid until: {$expiryDisplay}</span>
      <br/><span style="font-size:9px; font-weight:bold; color:{$statusColor};">{$displayStatus}</span>
    </td>
  </tr>
</table>
HTML;
        $pdf->writeHTML($header, true, false, true, false, '');
        $pdf->SetDrawColor($brand["r"], $brand["g"], $brand["b"]);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(15, $pdf->GetY() + 3, 195, $pdf->GetY() + 3);
        $pdf->Ln(6);

        $billBlock = <<<HTML
<table cellpadding="5" cellspacing="0" style="width:100%; background-color:#f4f7fb; border:1px solid #dde3ef;">
  <tr>
    <td width="50%" style="font-size:9px;"><strong style="font-size:10px; color:{$theme};">Prepared For</strong><br/>{$customerName}</td>
    <td width="50%" style="font-size:9px; text-align:right;"><strong style="font-size:10px; color:{$theme};">Prepared By</strong><br/>{$sellerName}</td>
  </tr>
</table>
HTML;
        $pdf->writeHTML($billBlock, true, false, true, false, '');
        $pdf->Ln(5);

        $itemsTable = '<table cellpadding="5" cellspacing="0" style="width:100%; font-size:9px;">'
            . '<tr style="background-color:' . $theme . '; color:#ffffff;">'
            . '<td width="5%" align="center"><strong>#</strong></td>'
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
                . '<td width="5%" align="center" style="color:#888;">'.$rowNum.'</td>'
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

        $discountRow    = ((float)($q["discount"]    ?? 0) > 0) ? "<tr><td width=\"60%\"></td><td width=\"20%\" align=\"right\" style=\"color:#555;\">Discount:</td><td width=\"20%\" align=\"right\" style=\"color:#e74c3c;\">- {$cur} {$discount}</td></tr>" : "";
        $shippingRow    = ((float)($q["shipping"]    ?? 0) > 0) ? "<tr><td width=\"60%\"></td><td width=\"20%\" align=\"right\" style=\"color:#555;\">Shipping:</td><td width=\"20%\" align=\"right\">{$cur} {$shipping}</td></tr>" : "";
        $adjustmentsRow = ((float)($q["adjustments"] ?? 0) != 0) ? "<tr><td width=\"60%\"></td><td width=\"20%\" align=\"right\" style=\"color:#555;\">Adjustment:</td><td width=\"20%\" align=\"right\">{$cur} {$adjustments}</td></tr>" : "";

        $totals = <<<HTML
<table cellpadding="4" cellspacing="0" style="width:100%; font-size:9px;">
  <tr><td width="60%"></td><td width="20%" align="right" style="color:#555;">Subtotal:</td><td width="20%" align="right">{$cur} {$subtotal}</td></tr>
  {$discountRow}
  {$shippingRow}
  {$adjustmentsRow}
  <tr><td width="60%"></td><td width="20%" align="right" style="color:#555;">Net:</td><td width="20%" align="right">{$cur} {$netPrice}</td></tr>
  <tr><td width="60%"></td><td width="20%" align="right" style="color:#555;">Tax:</td><td width="20%" align="right">{$cur} {$taxAmount}</td></tr>
  <tr><td width="60%"></td><td colspan="2" style="border-top:1px solid {$theme}; padding:1px;"></td></tr>
  <tr><td width="60%"></td><td width="20%" align="right" style="font-size:11px; font-weight:bold; color:{$theme};">TOTAL:</td><td width="20%" align="right" style="font-size:11px; font-weight:bold; color:{$theme};">{$cur} {$totalPrice}</td></tr>
</table>
HTML;
        $pdf->writeHTML($totals, true, false, true, false, '');

        if ($notes) {
            $pdf->writeHTML('<table cellpadding="5" cellspacing="0" style="width:100%; margin-top:6px; background-color:#fafafa; border:1px solid #e0e0e0;"><tr><td style="font-size:9px;"><strong>Notes:</strong><br/>'.$notes.'</td></tr></table>', true, false, true, false, '');
        }
        if ($terms) {
            $pdf->writeHTML('<table cellpadding="5" cellspacing="0" style="width:100%; margin-top:6px; background-color:#fafafa; border:1px solid #e0e0e0;"><tr><td style="font-size:9px;"><strong>Terms &amp; Conditions:</strong><br/>'.$terms.'</td></tr></table>', true, false, true, false, '');
        }

        $pdf->Ln(8);
        $pdf->writeHTML('<p style="text-align:center; font-size:8px; color:#aaa;">This is a quotation, not a tax invoice. &mdash; ' . $companyName . '</p>', true, false, true, false, '');

        ob_end_clean();
        $pdf->Output('quotation-' . $quoteNumber . '.pdf');
    }
}

$print = new PrintQuotation();
$print->quoteId = (int)($_GET["id"] ?? 0);
$print->generate();
?>
