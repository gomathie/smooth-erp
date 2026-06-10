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
    <h1><?php echo t('Activity'); ?> <small><?php echo t('Report'); ?></small></h1>
    <ol class="breadcrumb"><li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li><li class="active">Activity</li></ol>
  </section>
  <section class="content">
    <?php include "reports/filter.php"; ?>

    <div class="card">
      <div class="card-header"><h3 class="card-title">Invoice &amp; Payment Activity <span class="badge text-bg-secondary"><?php echo count($activity); ?></span></h3></div>
      <div class="card-body">
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
                <td><span class="badge text-bg-info"><?php echo $actionLabels[$a["action"]] ?? htmlspecialchars($a["action"]); ?></span></td>
                <td><?php echo htmlspecialchars($a["description"] ?? ""); ?></td>
              </tr>
            <?php } if (!$activity) { echo '<tr><td colspan="5" class="text-muted">No activity in range</td></tr>'; } ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
