<?php

if (!Permission::has("sales")) {

  echo '<script>

    window.location = "home";

  </script>';

  return;

}

?>
<div class="content-wrapper">

  <section class="content-header">

    <h1>

      <?php echo t('Invoices'); ?>

    </h1>

    <ol class="breadcrumb">

      <li><a href="home"><i class="fa fa-dashboard"></i> <?php echo t('Home'); ?></a></li>

      <li class="active"><?php echo t('Invoices'); ?></li>

    </ol>

  </section>

  <section class="content">

    <div class="card">

      <div class="card-header">

        <a href="create-invoice">
          <button class="btn btn-success">
            <i class="fa fa-plus"></i> <?php echo t('New Invoice'); ?>
          </button>
        </a>

      </div>

      <div class="card-body">

        <table class="table table-bordered table-hover table-striped dt-responsive invoicesTable" width="100%">

          <thead>

            <tr>
              <th style="width:10px">#</th>
              <th><?php echo t('Invoice #'); ?></th>
              <th><?php echo t('Customer'); ?></th>
              <th><?php echo t('Total'); ?></th>
              <th><?php echo t('Paid'); ?></th>
              <th><?php echo t('Balance'); ?></th>
              <th><?php echo t('Status'); ?></th>
              <th><?php echo t('Due Date'); ?></th>
              <th><?php echo t('Date'); ?></th>
              <th><?php echo t('Actions'); ?></th>
            </tr>

          </thead>

          <tbody>

            <?php

            $invoices = ControllerInvoices::ctrShowInvoices(null, null);

            if ($invoices) {

              foreach ($invoices as $key => $invoice) {

                $customer = ControllerCustomers::ctrShowCustomers("id", $invoice["idCustomer"]);

                $balance   = (float)$invoice["balanceDue"];
                $today     = date("Y-m-d");
                $isOverdue = ($invoice["status"] !== "paid" && $invoice["status"] !== "draft"
                              && $balance > 0
                              && !empty($invoice["dueDate"])
                              && $invoice["dueDate"] < $today);

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
                    "paid"           => "btn-success",
                    "partially_paid" => "btn-primary",
                    "sent"           => "btn-warning",
                    "overdue"        => "btn-danger",
                    default          => "btn-default",
                };

                echo '<tr>';
                echo '<td>' . ($key + 1) . '</td>';
                echo '<td><a href="index.php?route=invoice-detail&idInvoice=' . $invoice["id"] . '">' . $invoice["invoiceNumber"] . '</a></td>';
                $cs = Currency::symbol($invoice["currency"] ?? Currency::base());
                echo '<td>' . htmlspecialchars($customer["name"]) . '</td>';
                echo '<td>' . $cs . ' ' . number_format($invoice["totalPrice"], 2) . '</td>';
                echo '<td>' . $cs . ' ' . number_format($invoice["amountPaid"], 2) . '</td>';
                echo '<td>' . $cs . ' ' . number_format($balance,               2) . '</td>';
                echo '<td><span class="btn btn-xs ' . $statusClass . '">' . $statusLabel . '</span></td>';
                echo '<td>' . ($invoice["dueDate"] ? $invoice["dueDate"] : '-') . '</td>';
                echo '<td>' . substr($invoice["invoiceDate"], 0, 10) . '</td>';
                echo '<td><div class="btn-group">';

                echo '<a class="btn btn-default" href="index.php?route=invoice-detail&idInvoice=' . $invoice["id"] . '">
                        <i class="fa fa-eye"></i>
                      </a>';

                echo '<button class="btn btn-warning btnPrintInvoice" invoiceId="' . $invoice["id"] . '">
                        <i class="fa fa-print"></i>
                      </button>';

                if ($_SESSION["profile"] == "Administrator") {
                  echo '<button class="btn btn-primary btnEditInvoice" idInvoice="' . $invoice["id"] . '">
                          <i class="fa fa-pencil"></i>
                        </button>';
                  echo '<button class="btn btn-danger btnDeleteInvoice" idInvoice="' . $invoice["id"] . '">
                          <i class="fa fa-trash"></i>
                        </button>';
                }

                echo '</div></td>';
                echo '</tr>';

              }

            }

            ?>

          </tbody>

        </table>

        <?php

          $deleteInvoice = new ControllerInvoices();
          $deleteInvoice->ctrDeleteInvoice();

        ?>

      </div>

    </div>

  </section>

</div>
