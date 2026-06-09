<?php
/**
 * Shared date-range filter for report pages.
 * The including page must set $reportRoute before including this file.
 * Exposes $range (['from'=>, 'to'=>]) to the including page.
 */
$range = ControllerReports::ctrRange();
?>
<form method="get" class="form-inline" style="margin-bottom:15px;">
  <input type="hidden" name="route" value="<?php echo htmlspecialchars($reportRoute, ENT_QUOTES); ?>">
  <div class="form-group">
    <label>From</label>
    <input type="date" name="from" class="form-control input-sm" value="<?php echo htmlspecialchars($range['from']); ?>">
  </div>
  <div class="form-group">
    <label>To</label>
    <input type="date" name="to" class="form-control input-sm" value="<?php echo htmlspecialchars($range['to']); ?>">
  </div>
  <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply</button>
  <a href="index.php?route=<?php echo htmlspecialchars($reportRoute, ENT_QUOTES); ?>" class="btn btn-default btn-sm">Clear</a>
  <?php if ($range['from'] !== '' || $range['to'] !== '') { ?>
    <span class="text-muted" style="margin-left:8px;">
      Showing <?php echo $range['from'] ?: '…'; ?> &rarr; <?php echo $range['to'] ?: '…'; ?>
    </span>
  <?php } else { ?>
    <span class="text-muted" style="margin-left:8px;">Showing all dates</span>
  <?php } ?>
</form>
