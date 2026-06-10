<?php

if (!Permission::has("currencies")) {
  echo '<script>window.location = "home";</script>';
  return;
}
if (!Currency::isEnabled()) {
  echo '<script>window.location = "home";</script>';
  return;
}

// Currencies are chosen for each organization by the platform (Super Admin),
// so this page is read-only for the org administrator.
$active = ControllerCurrencies::ctrOrgCurrencies();
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1><?php echo t('Currencies'); ?></h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> <?php echo t('Home'); ?></a></li>
      <li class="active"><?php echo t('Currencies'); ?></li>
    </ol>
  </section>

  <section class="content">

    <div class="callout callout-info">
      <?php echo t('These are the currencies enabled for your organization. They appear as options when you create invoices and quotations. To add or change currencies, please contact your provider.'); ?>
    </div>

    <div class="card card-success card-outline">
      <div class="card-header"><h3 class="card-title"><?php echo t('Enabled currencies'); ?></h3></div>
      <div class="card-body">
        <table class="table table-striped">
          <thead><tr><th><?php echo t('Code'); ?></th><th><?php echo t('Name'); ?></th><th><?php echo t('Symbol'); ?></th><th><?php echo t('Role'); ?></th></tr></thead>
          <tbody>
            <?php foreach ($active as $a) { ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($a["code"]); ?></strong></td>
                <td><?php echo htmlspecialchars($a["name"]); ?></td>
                <td><?php echo htmlspecialchars($a["symbol"]); ?></td>
                <td><?php echo (int)$a["isBase"] === 1 ? '<span class="badge text-bg-primary">' . t('Base') . '</span>' : ''; ?></td>
              </tr>
            <?php } if (!$active) { echo '<tr><td colspan="4" class="text-muted">' . t('No currencies enabled yet.') . '</td></tr>'; } ?>
          </tbody>
        </table>
      </div>
    </div>

  </section>
</div>
