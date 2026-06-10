<?php

if (($_SESSION["profile"] ?? "") !== "Administrator") {
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
    <h1>Currencies</h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Currencies</li>
    </ol>
  </section>

  <section class="content">

    <div class="callout callout-info">
      These are the currencies enabled for your organization. They appear as options when you
      create invoices and quotations. To add or change currencies, please contact your provider.
    </div>

    <div class="box box-success">
      <div class="box-header with-border"><h3 class="box-title">Enabled currencies</h3></div>
      <div class="box-body">
        <table class="table table-striped">
          <thead><tr><th>Code</th><th>Name</th><th>Symbol</th><th>Role</th></tr></thead>
          <tbody>
            <?php foreach ($active as $a) { ?>
              <tr>
                <td><strong><?php echo htmlspecialchars($a["code"]); ?></strong></td>
                <td><?php echo htmlspecialchars($a["name"]); ?></td>
                <td><?php echo htmlspecialchars($a["symbol"]); ?></td>
                <td><?php echo (int)$a["isBase"] === 1 ? '<span class="label label-primary">Base</span>' : ''; ?></td>
              </tr>
            <?php } if (!$active) { echo '<tr><td colspan="4" class="text-muted">No currencies enabled yet.</td></tr>'; } ?>
          </tbody>
        </table>
      </div>
    </div>

  </section>
</div>
