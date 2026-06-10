<?php
if (!Permission::has("reports")) { echo '<script>window.location="home";</script>'; return; }
$reportRoute = "report-activity";
$range = ControllerReports::ctrRange();
$activity = ControllerReports::ctrActivity($range["from"], $range["to"]);

$actionLabels = [
  "created"=>"Created","edited"=>"Edited","payment_added"=>"Payment added",
  "payment_edited"=>"Payment edited","payment_deleted"=>"Payment deleted",
];
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Activity <small>Report</small></h1>
    <ol class="breadcrumb"><li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Activity</li></ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Invoice &amp; Payment Activity <span class="label label-default"><?php echo count($activity); ?></span></h3></div>
      <div class="box-body">
        <table class="table table-bordered table-striped dt-responsive" width="100%">
          <thead><tr><th>Date / Time</th><th>User</th><th>Invoice</th><th>Action</th><th>Detail</th></tr></thead>
          <tbody>
            <?php foreach ($activity as $a) {
              $user = ControllerUsers::ctrShowUsers("id", (int)$a["idUser"]);
            ?>
              <tr>
                <td><?php echo $a["createdDate"]; ?></td>
                <td><?php echo htmlspecialchars(is_array($user) ? $user["name"] : "System"); ?></td>
                <td><?php echo $a["invoiceNumber"] ? '<a href="index.php?route=invoice-detail&idInvoice='.$a["idInvoice"].'">#'.htmlspecialchars($a["invoiceNumber"]).'</a>' : '-'; ?></td>
                <td><span class="label label-info"><?php echo $actionLabels[$a["action"]] ?? htmlspecialchars($a["action"]); ?></span></td>
                <td><?php echo htmlspecialchars($a["description"] ?? ""); ?></td>
              </tr>
            <?php } if (!$activity) { echo '<tr><td colspan="5" class="text-muted">No activity in range</td></tr>'; } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
