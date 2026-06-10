<?php

if (!Permission::has("settings")) {
  echo '<script>window.location = "home";</script>';
  return;
}

// Handle the toggle post
ControllerSettings::ctrToggleAccounting();

$accountingEnabled = ControllerSettings::ctrAccountingEnabled();
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1><?php echo t('Settings'); ?></h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> <?php echo t('Home'); ?></a></li>
      <li class="active"><?php echo t('Settings'); ?></li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-md-8">

        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-id-card-o"></i> <?php echo t('Company Profile & Branding'); ?></h3>
          </div>
          <div class="card-body">
            <p><?php echo t("Set your organization's logo, brand color, and company details (address, contact, website). This information appears on your printed documents — invoices, quotations, and receipts."); ?></p>
            <a href="company-profile" class="btn btn-primary"><i class="fa fa-pencil"></i> <?php echo t('Edit Company Profile'); ?></a>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-paint-brush"></i> <?php echo t('Appearance'); ?></h3>
          </div>
          <div class="card-body">
            <p><?php echo t('Choose your interface theme color. The change applies immediately and is saved for your session.'); ?></p>
            <!-- Swatches are built by JS (template.js) from themes.config.js -->
            <div id="theme-swatches" style="display:flex; flex-wrap:wrap; gap:10px;"></div>
          </div>
        </div>

        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-balance-scale"></i> <?php echo t('Accounting Module'); ?></h3>
          </div>
          <div class="card-body">

            <p>
              <?php echo t('The accounting module adds the <strong>Accounting</strong> dashboard, <strong>Expenses</strong>, and <strong>Chart of Accounts</strong> to the menu. Invoice and payment bookkeeping always runs in the background, so turning this on later shows a complete, consistent ledger.'); ?>
            </p>

            <p>
              <?php echo t('Status'); ?>:
              <?php if ($accountingEnabled) { ?>
                <span class="badge text-bg-success"><?php echo t('Enabled'); ?></span>
              <?php } else { ?>
                <span class="badge text-bg-secondary"><?php echo t('Disabled'); ?></span>
              <?php } ?>
            </p>

            <form method="post" role="form">
              <input type="hidden" name="toggleAccounting" value="<?php echo $accountingEnabled ? '0' : '1'; ?>">
              <?php if ($accountingEnabled) { ?>
                <button type="submit" class="btn btn-default"><i class="fa fa-toggle-off"></i> <?php echo t('Disable accounting module'); ?></button>
              <?php } else { ?>
                <button type="submit" class="btn btn-success"><i class="fa fa-toggle-on"></i> <?php echo t('Enable accounting module'); ?></button>
              <?php } ?>
            </form>

          </div>
        </div>

      </div>
    </div>

  </section>

</div>
