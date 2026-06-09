<?php

if (($_SESSION["profile"] ?? "") !== "Administrator") {
  echo '<script>window.location = "home";</script>';
  return;
}
if (!Currency::isEnabled()) {
  echo '<script>window.location = "home";</script>';
  return;
}

ControllerCurrencies::ctrHandle();

$all    = ControllerCurrencies::ctrAllCurrencies();
$active = ControllerCurrencies::ctrOrgCurrencies();
$activeCodes = [];
$baseCode = "";
foreach ($active as $a) { $activeCodes[$a["code"]] = $a; if ((int)$a["isBase"] === 1) { $baseCode = $a["code"]; } }
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
      Activate the currencies your organization transacts in. The <strong>base</strong> currency
      (<strong><?php echo htmlspecialchars($baseCode); ?></strong>) is used by default on new documents and can't be deactivated.
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="box box-success">
          <div class="box-header with-border"><h3 class="box-title">Activated for this organization</h3></div>
          <div class="box-body">
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
                        <a class="btn btn-xs btn-default" href="index.php?route=currencies&setBase=<?php echo $a["code"]; ?>">Set base</a>
                        <a class="btn btn-xs btn-danger" href="index.php?route=currencies&deactivate=<?php echo $a["code"]; ?>">Remove</a>
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
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title">Available currencies</h3></div>
          <div class="box-body">
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
                    <td class="text-right"><a class="btn btn-xs btn-success" href="index.php?route=currencies&activate=<?php echo $c["code"]; ?>"><i class="fa fa-plus"></i> Activate</a></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </section>
</div>
