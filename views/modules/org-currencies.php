<?php

if (!Tenant::isSuperAdmin()) {
  echo '<script>window.location = "home";</script>';
  return;
}

ControllerSuperAdmin::ctrManageCurrencies();

$idOrg = (int)($_GET["org"] ?? 0);
$org   = ModelOrganizations::mdlGetOrganization($idOrg);

if (!is_array($org)) {
  echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger" style="margin:20px;">Organization not found.</div></section></div>';
  return;
}

$all    = ControllerSuperAdmin::ctrAllCurrencies();
$active = ControllerSuperAdmin::ctrOrgCurrencyList($idOrg);
$activeCodes = [];
foreach ($active as $a) { $activeCodes[$a["code"]] = $a; }
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>Currencies <small><?php echo htmlspecialchars($org["name"]); ?></small></h1>
    <ol class="breadcrumb">
      <li><i class="fa fa-building"></i> Super Admin</li>
      <li><a href="organizations">Organizations</a></li>
      <li class="active">Currencies</li>
    </ol>
  </section>

  <section class="content">

    <div class="callout callout-info">
      Choose which currencies <strong><?php echo htmlspecialchars($org["name"]); ?></strong> can use. The
      <strong>base</strong> currency is used by default on new documents and can't be removed.
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card card-success card-outline">
          <div class="card-header"><h3 class="card-title">Activated</h3></div>
          <div class="card-body">
            <table class="table table-striped">
              <thead><tr><th>Code</th><th>Name</th><th>Symbol</th><th>Role</th><th class="text-right">Actions</th></tr></thead>
              <tbody>
                <?php foreach ($active as $a) { ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($a["code"]); ?></strong></td>
                    <td><?php echo htmlspecialchars($a["name"]); ?></td>
                    <td><?php echo htmlspecialchars($a["symbol"]); ?></td>
                    <td><?php echo (int)$a["isBase"] === 1 ? '<span class="label label-primary">Base</span>' : ''; ?></td>
                    <td class="text-right">
                      <?php if ((int)$a["isBase"] !== 1) { ?>
                        <a class="btn btn-xs btn-default" href="index.php?route=org-currencies&org=<?php echo $idOrg; ?>&setBase=<?php echo $a["code"]; ?>">Set base</a>
                        <a class="btn btn-xs btn-danger" href="index.php?route=org-currencies&org=<?php echo $idOrg; ?>&deactivate=<?php echo $a["code"]; ?>">Remove</a>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Available currencies</h3></div>
          <div class="card-body">
            <table class="table table-striped">
              <thead><tr><th>Code</th><th>Name</th><th>Symbol</th><th class="text-right">Action</th></tr></thead>
              <tbody>
                <?php foreach ($all as $c) {
                  if (isset($activeCodes[$c["code"]])) { continue; }
                ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($c["code"]); ?></strong></td>
                    <td><?php echo htmlspecialchars($c["name"]); ?></td>
                    <td><?php echo htmlspecialchars($c["symbol"]); ?></td>
                    <td class="text-right"><a class="btn btn-xs btn-success" href="index.php?route=org-currencies&org=<?php echo $idOrg; ?>&activate=<?php echo $c["code"]; ?>"><i class="fa fa-plus"></i> Activate</a></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <a href="organizations" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to organizations</a>

  </section>
</div>
