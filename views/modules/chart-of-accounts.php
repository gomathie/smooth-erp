<?php

if (!Permission::has("accounting")) {
  echo '<script>window.location = "home";</script>';
  return;
}

if (!ControllerSettings::ctrAccountingEnabled()) {
  echo '<script>window.location = "home";</script>';
  return;
}

// Inline action handlers
ControllerAccounting::ctrAddAccount();
ControllerAccounting::ctrEditAccount();
ControllerAccounting::ctrDeleteAccount();

$accounts = ControllerAccounting::ctrShowAccounts();

$typeLabels = [
  "asset" => "Asset", "liability" => "Liability", "equity" => "Equity",
  "income" => "Income", "expense" => "Expense",
];
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1><?php echo t('Chart of Accounts'); ?></h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="accounting">Accounting</a></li>
      <li class="active">Chart of Accounts</li>
    </ol>
  </section>

  <section class="content">

    <div class="card">
      <div class="card-header">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAddAccount">
          <i class="fa fa-plus"></i> New Account
        </button>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-hover table-striped dt-responsive accountsTable" width="100%">
          <thead>
            <tr>
              <th style="width:90px;">Code</th>
              <th>Name</th>
              <th>Type</th>
              <th style="width:120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($accounts as $a) {
              $isSystem = (int)($a["isSystem"] ?? 0) === 1;
            ?>
              <tr>
                <td><?php echo htmlspecialchars($a["code"]); ?></td>
                <td>
                  <?php echo htmlspecialchars($a["name"]); ?>
                  <?php if ($isSystem) { echo ' <span class="badge text-bg-secondary">system</span>'; } ?>
                </td>
                <td><?php echo $typeLabels[$a["type"]] ?? ucfirst($a["type"]); ?></td>
                <td>
                  <div class="btn-group">
                    <button class="btn btn-primary btn-xs btnEditAccount"
                            data-id="<?php echo $a["id"]; ?>"
                            data-code="<?php echo htmlspecialchars($a["code"], ENT_QUOTES); ?>"
                            data-name="<?php echo htmlspecialchars($a["name"], ENT_QUOTES); ?>"
                            data-type="<?php echo $a["type"]; ?>"
                            data-system="<?php echo $isSystem ? '1' : '0'; ?>">
                      <i class="fa fa-pencil"></i>
                    </button>
                    <?php if (!$isSystem) { ?>
                      <button class="btn btn-danger btn-xs btnDeleteAccount" data-id="<?php echo $a["id"]; ?>">
                        <i class="fa fa-trash"></i>
                      </button>
                    <?php } ?>
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

<!-- ADD ACCOUNT MODAL -->
<div id="modalAddAccount" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" role="form">
        <div class="modal-header" style="background:#00a65a; color:#fff;">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">New Account</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Account Code</label>
            <input type="text" class="form-control" name="newAccountCode" placeholder="e.g. 5500" required>
          </div>
          <div class="form-group">
            <label>Account Name</label>
            <input type="text" class="form-control" name="newAccountName" placeholder="e.g. Marketing" required>
          </div>
          <div class="form-group">
            <label>Type</label>
            <select class="form-control" name="newAccountType" required>
              <option value="asset">Asset</option>
              <option value="liability">Liability</option>
              <option value="equity">Equity</option>
              <option value="income">Income</option>
              <option value="expense" selected>Expense</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Account</button>
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT ACCOUNT MODAL -->
<div id="modalEditAccount" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" role="form">
        <div class="modal-header" style="background:#3c8dbc; color:#fff;">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Account</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="editAccount" id="editAccountId">
          <div class="form-group">
            <label>Account Code</label>
            <input type="text" class="form-control" id="editAccountCode" readonly>
            <p class="help-block" style="margin:4px 0 0;">Codes are fixed once created.</p>
          </div>
          <div class="form-group">
            <label>Account Name</label>
            <input type="text" class="form-control" name="editAccountName" id="editAccountName" required>
          </div>
          <div class="form-group">
            <label>Type</label>
            <select class="form-control" name="editAccountType" id="editAccountType" required>
              <option value="asset">Asset</option>
              <option value="liability">Liability</option>
              <option value="equity">Equity</option>
              <option value="income">Income</option>
              <option value="expense">Expense</option>
            </select>
            <p class="help-block" id="editTypeLocked" style="margin:4px 0 0; display:none;">System account type is locked.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Account</button>
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
