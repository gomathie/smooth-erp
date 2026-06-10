<?php

if (!Tenant::isSuperAdmin()) {
  echo '<script>window.location = "home";</script>';
  return;
}

// Inline action handlers
ControllerSuperAdmin::ctrSetMaxOrganizations();
ControllerSuperAdmin::ctrCreateOrganization();
ControllerSuperAdmin::ctrEditOrganization();
ControllerSuperAdmin::ctrToggleFeature();
ControllerSuperAdmin::ctrEnterOrg();

$orgs    = ControllerSuperAdmin::ctrShowOrganizations();
$maxOrgs = ControllerSuperAdmin::ctrMaxOrganizations();
$orgsUsed = count($orgs);
$atOrgCap = $orgsUsed >= $maxOrgs;

$curStmt = Connection::connect()->query("SELECT code, name, symbol FROM currencies ORDER BY code ASC");
$currencies = $curStmt ? $curStmt->fetchAll() : [];

function currencyOptions(array $currencies, string $selected = "USD"): string {
  $h = "";
  foreach ($currencies as $c) {
    $sel = $c["code"] === $selected ? " selected" : "";
    $h .= '<option value="' . $c["code"] . '"' . $sel . '>' . htmlspecialchars($c["code"] . " — " . $c["name"]) . '</option>';
  }
  return $h;
}
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>Organizations <small>Super Admin</small></h1>
    <ol class="breadcrumb">
      <li><i class="fa fa-building"></i> Super Admin</li>
      <li class="active">Organizations</li>
    </ol>
  </section>

  <section class="content">

    <div class="box box-solid">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-sliders"></i> Platform Limits</h3>
      </div>
      <div class="box-body">
        <form method="post" role="form" class="form-inline">
          <div class="form-group">
            <label>Maximum organizations allowed&nbsp;</label>
            <input type="number" min="1" class="form-control" name="maxOrganizations" value="<?php echo (int)$maxOrgs; ?>" style="width:90px;">
          </div>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
          <span style="padding-left:12px; color:#888;">
            Currently <strong><?php echo $orgsUsed; ?></strong> of <strong><?php echo (int)$maxOrgs; ?></strong> used.
          </span>
        </form>
        <p class="help-block" style="margin-top:8px;">The default cap is 3 organizations. Each organization also has its own maximum number of users (set when onboarding or editing it).</p>
      </div>
    </div>

    <div class="box">
      <div class="box-header with-border">
        <?php if ($atOrgCap) { ?>
          <button class="btn btn-success" disabled title="Organization limit reached"><i class="fa fa-plus"></i> Onboard Organization</button>
          <span class="text-danger" style="padding-left:8px;"><i class="fa fa-exclamation-triangle"></i> Limit reached — raise the cap above to add more.</span>
        <?php } else { ?>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAddOrg"><i class="fa fa-plus"></i> Onboard Organization</button>
        <?php } ?>
        <span class="pull-right" style="padding-top:6px;"><?php echo $orgsUsed; ?> organization(s)</span>
      </div>
      <div class="box-body">
        <table class="table table-bordered table-hover table-striped">
          <thead>
            <tr>
              <th>#</th><th>Code</th><th>Name</th><th>Base Cur.</th><th>Users</th>
              <th class="text-center">Accounting</th><th class="text-center">Multi-currency</th>
              <th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orgs as $o) {
              $id = (int)$o["id"];
              $acc = ControllerSuperAdmin::ctrOrgFeature($id, "accounting_enabled");
              $mc  = ControllerSuperAdmin::ctrOrgFeature($id, "multicurrency_enabled");
            ?>
              <tr>
                <td><?php echo $id; ?></td>
                <td><strong><?php echo htmlspecialchars($o["code"]); ?></strong></td>
                <td><?php echo htmlspecialchars($o["name"]); ?></td>
                <td><?php echo htmlspecialchars($o["baseCurrency"]); ?></td>
                <td><?php echo ControllerSuperAdmin::ctrUserCount($id); ?> / <?php echo (int)($o["maxUsers"] ?? 3); ?></td>
                <td class="text-center">
                  <a href="index.php?route=organizations&toggleFeature=accounting_enabled&org=<?php echo $id; ?>"
                     class="btn btn-xs <?php echo $acc ? 'btn-success' : 'btn-default'; ?>">
                     <?php echo $acc ? 'On' : 'Off'; ?>
                  </a>
                </td>
                <td class="text-center">
                  <a href="index.php?route=organizations&toggleFeature=multicurrency_enabled&org=<?php echo $id; ?>"
                     class="btn btn-xs <?php echo $mc ? 'btn-success' : 'btn-default'; ?>">
                     <?php echo $mc ? 'On' : 'Off'; ?>
                  </a>
                </td>
                <td><?php echo ((int)$o["status"] === 1) ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Suspended</span>'; ?></td>
                <td>
                  <div class="btn-group">
                    <a class="btn btn-primary btn-xs" href="index.php?route=organizations&enterOrg=<?php echo $id; ?>" title="Enter / operate"><i class="fa fa-sign-in"></i> Enter</a>
                    <a class="btn btn-info btn-xs" href="index.php?route=org-currencies&org=<?php echo $id; ?>" title="Currencies"><i class="fa fa-money"></i></a>
                    <button class="btn btn-default btn-xs btnEditOrg"
                            data-id="<?php echo $id; ?>"
                            data-name="<?php echo htmlspecialchars($o["name"], ENT_QUOTES); ?>"
                            data-email="<?php echo htmlspecialchars($o["email"] ?? "", ENT_QUOTES); ?>"
                            data-phone="<?php echo htmlspecialchars($o["phone"] ?? "", ENT_QUOTES); ?>"
                            data-address="<?php echo htmlspecialchars($o["address"] ?? "", ENT_QUOTES); ?>"
                            data-currency="<?php echo htmlspecialchars($o["baseCurrency"], ENT_QUOTES); ?>"
                            data-maxusers="<?php echo (int)($o["maxUsers"] ?? 3); ?>"
                            data-status="<?php echo (int)$o["status"]; ?>">
                      <i class="fa fa-pencil"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

  </section>
</div>

<!-- ADD ORG MODAL -->
<div id="modalAddOrg" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" role="form">
        <div class="modal-header" style="background:#00a65a; color:#fff;">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Onboard Organization</h4>
        </div>
        <div class="modal-body">
          <h5 style="font-weight:bold; color:#888;">ORGANIZATION</h5>
          <div class="form-group"><label>Name</label><input type="text" class="form-control" name="newOrgName" required></div>
          <div class="form-group"><label>Code (unique)</label><input type="text" class="form-control" name="newOrgCode" placeholder="e.g. ACME" required></div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>Base Currency</label><select class="form-control" name="newOrgBaseCurrency"><?php echo currencyOptions($currencies, "USD"); ?></select></div></div>
            <div class="col-xs-6"><div class="form-group"><label>Max Users</label><input type="number" min="1" class="form-control" name="newOrgMaxUsers" value="3"></div></div>
          </div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>Phone</label><input type="text" class="form-control" name="newOrgPhone"></div></div>
            <div class="col-xs-6"><div class="form-group"><label>Email</label><input type="text" class="form-control" name="newOrgEmail"></div></div>
          </div>
          <div class="form-group"><label>Address</label><input type="text" class="form-control" name="newOrgAddress"></div>

          <h5 style="font-weight:bold; color:#888; margin-top:15px;">FIRST ADMINISTRATOR</h5>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>Full name</label><input type="text" class="form-control" name="adminName"></div></div>
            <div class="col-xs-6"><div class="form-group"><label>Username</label><input type="text" class="form-control" name="adminUser" required></div></div>
          </div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>Password</label><input type="text" class="form-control" name="adminPass" required></div></div>
            <div class="col-xs-6"><div class="form-group"><label>Email</label><input type="text" class="form-control" name="adminEmail"></div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Create</button>
          <button type="button" class="btn btn-default pull-left" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT ORG MODAL -->
<div id="modalEditOrg" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" role="form">
        <div class="modal-header" style="background:#3c8dbc; color:#fff;">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Organization</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="editOrg" id="editOrgId">
          <div class="form-group"><label>Name</label><input type="text" class="form-control" name="editOrgName" id="editOrgName" required></div>
          <div class="row">
            <div class="col-xs-6"><div class="form-group"><label>Base Currency</label><select class="form-control" name="editOrgBaseCurrency" id="editOrgBaseCurrency"><?php echo currencyOptions($currencies, "USD"); ?></select></div></div>
            <div class="col-xs-6"><div class="form-group"><label>Status</label><select class="form-control" name="editOrgStatus" id="editOrgStatus"><option value="1">Active</option><option value="0">Suspended</option></select></div></div>
          </div>
          <div class="form-group"><label>Max Users</label><input type="number" min="1" class="form-control" name="editOrgMaxUsers" id="editOrgMaxUsers" value="3"></div>
          <div class="form-group"><label>Email</label><input type="text" class="form-control" name="editOrgEmail" id="editOrgEmail"></div>
          <div class="form-group"><label>Phone</label><input type="text" class="form-control" name="editOrgPhone" id="editOrgPhone"></div>
          <div class="form-group"><label>Address</label><input type="text" class="form-control" name="editOrgAddress" id="editOrgAddress"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-default pull-left" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).on("click", ".btnEditOrg", function(){
  $("#editOrgId").val($(this).data("id"));
  $("#editOrgName").val($(this).data("name"));
  $("#editOrgEmail").val($(this).data("email"));
  $("#editOrgPhone").val($(this).data("phone"));
  $("#editOrgAddress").val($(this).data("address"));
  $("#editOrgBaseCurrency").val($(this).data("currency"));
  $("#editOrgMaxUsers").val($(this).data("maxusers"));
  $("#editOrgStatus").val(String($(this).data("status")));
  bootstrap.Modal.getOrCreateInstance(document.getElementById("modalEditOrg")).show();
});
</script>
