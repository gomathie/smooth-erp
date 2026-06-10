<?php

if (!Permission::has("sales")) {
  echo '<script>window.location = "home";</script>';
  return;
}

// Inline action handlers
$ctrl = new ControllerQuotations();
$ctrl->ctrConvertToInvoice();
$ctrl->ctrDeleteQuotation();
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1><?php echo t('Quotations'); ?></h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> <?php echo t('Home'); ?></a></li>
      <li class="active"><?php echo t('Quotations'); ?></li>
    </ol>
  </section>

  <section class="content">
    <div class="card">
      <div class="card-header">
        <a href="create-quotation"><button class="btn btn-success"><i class="fa fa-plus"></i> <?php echo t('New Quotation'); ?></button></a>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-hover table-striped dt-responsive quotationsTable" width="100%">
          <thead>
            <tr>
              <th style="width:10px">#</th>
              <th><?php echo t('Quote #'); ?></th>
              <th><?php echo t('Customer'); ?></th>
              <th><?php echo t('Total'); ?></th>
              <th><?php echo t('Status'); ?></th>
              <th><?php echo t('Expiry'); ?></th>
              <th><?php echo t('Date'); ?></th>
              <th><?php echo t('Actions'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
            $quotations = ControllerQuotations::ctrShowQuotations(null, null);

            if ($quotations) {

              $today = date("Y-m-d");

              foreach ($quotations as $key => $q) {

                $customer = ControllerCustomers::ctrShowCustomers("id", $q["idCustomer"]);

                $isExpired = ($q["status"] !== "invoiced" && $q["status"] !== "declined"
                              && !empty($q["expiryDate"]) && $q["expiryDate"] < $today);
                $displayStatus = $isExpired ? "expired" : $q["status"];

                $statusLabels = [
                  "draft"    => "Draft", "sent" => "Sent", "accepted" => "Accepted",
                  "declined" => "Declined", "expired" => "Expired", "invoiced" => "Invoiced",
                ];
                $label = $statusLabels[$displayStatus] ?? ucfirst($displayStatus);

                $statusClass = match($displayStatus) {
                  "accepted" => "btn-success",
                  "invoiced" => "btn-primary",
                  "sent"     => "btn-warning",
                  "declined" => "btn-danger",
                  "expired"  => "btn-danger",
                  default    => "btn-default",
                };

                echo '<tr>';
                echo '<td>' . ($key + 1) . '</td>';
                echo '<td>' . htmlspecialchars($q["quoteNumber"]) . '</td>';
                echo '<td>' . htmlspecialchars($customer["name"] ?? "-") . '</td>';
                echo '<td>' . Currency::symbol($q["currency"] ?? Currency::base()) . ' ' . number_format($q["totalPrice"], 2) . '</td>';
                echo '<td><span class="btn btn-xs ' . $statusClass . '">' . $label . '</span></td>';
                echo '<td>' . ($q["expiryDate"] ?: '-') . '</td>';
                echo '<td>' . substr($q["quoteDate"], 0, 10) . '</td>';
                echo '<td><div class="btn-group">';

                echo '<a class="btn btn-default" href="index.php?route=quotation-detail&idQuotation=' . $q["id"] . '" title="View"><i class="fa fa-eye"></i></a>';

                echo '<button class="btn btn-warning btnPrintQuotation" idQuotation="' . $q["id"] . '"><i class="fa fa-print"></i></button>';

                if ($q["status"] !== "invoiced") {
                  echo '<button class="btn btn-success btnConvertQuotation" idQuotation="' . $q["id"] . '" title="Convert to invoice"><i class="fa fa-exchange"></i></button>';
                  echo '<button class="btn btn-primary btnEditQuotation" idQuotation="' . $q["id"] . '"><i class="fa fa-pencil"></i></button>';
                } else if (!empty($q["idInvoice"])) {
                  echo '<a class="btn btn-default" href="index.php?route=invoice-detail&idInvoice=' . $q["idInvoice"] . '" title="View invoice"><i class="fa fa-file-text-o"></i></a>';
                }

                if ($_SESSION["profile"] == "Administrator") {
                  echo '<button class="btn btn-danger btnDeleteQuotation" idQuotation="' . $q["id"] . '"><i class="fa fa-trash"></i></button>';
                }

                echo '</div></td>';
                echo '</tr>';
              }
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

</div>
