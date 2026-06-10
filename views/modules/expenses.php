<?php

if (!Permission::has("expenses")) {
  echo '<script>window.location = "home";</script>';
  return;
}

// Expenses are available regardless of the accounting module toggle.
// (Journal postings still run in the background so the books stay consistent.)

// Inline action handlers
ControllerExpenses::ctrAddExpense();
ControllerExpenses::ctrEditExpense();
ControllerExpenses::ctrDeleteExpense();

$expenses        = ControllerExpenses::ctrShowExpenses();
$expenseAccounts = ControllerAccounting::ctrAccountsByType("expense");
$assetAccounts   = ControllerAccounting::ctrAccountsByType("asset");
$liabilityAccts  = ControllerAccounting::ctrAccountsByType("liability");

// Paid-through = where the money came from (cash/bank) or what you now owe (A/P)
$paidThrough = array_merge($assetAccounts, $liabilityAccts);

$totalExpenses = 0.0;
foreach ($expenses as $e) { $totalExpenses += (float)$e["amount"]; }

if (!function_exists('expenseAccountOptions')) {
  function expenseAccountOptions(array $accounts, $selected = null): string {
    $html = "";
    foreach ($accounts as $a) {
      $sel = ((int)$a["id"] === (int)$selected) ? " selected" : "";
      $html .= '<option value="' . $a["id"] . '"' . $sel . '>' . htmlspecialchars($a["code"] . " · " . $a["name"]) . '</option>';
    }
    return $html;
  }
}
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>Expenses</h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Expenses</li>
    </ol>
  </section>

  <section class="content">

    <div class="card">
      <div class="card-header">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAddExpense">
          <i class="fa fa-plus"></i> Record Expense
        </button>
        <span class="float-end" style="font-size:16px; padding-top:6px;">
          Total recorded: <strong>$ <?php echo number_format($totalExpenses, 2); ?></strong>
        </span>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-hover table-striped dt-responsive expensesTable" width="100%">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Expense Account</th>
              <th>Paid Through</th>
              <th>Payee</th>
              <th class="text-right">Amount</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($expenses as $e) { ?>
              <tr>
                <td><?php echo htmlspecialchars($e["expenseNumber"]); ?></td>
                <td><?php echo $e["expenseDate"]; ?></td>
                <td><?php echo htmlspecialchars(($e["expenseCode"] ?? "") . " · " . ($e["expenseName"] ?? "")); ?></td>
                <td><?php echo htmlspecialchars(($e["paidName"] ?? "")); ?></td>
                <td><?php echo htmlspecialchars($e["payee"] ?? ""); ?></td>
                <td class="text-right">$ <?php echo number_format((float)$e["amount"], 2); ?></td>
                <td>
                  <div class="btn-group">
                    <button class="btn btn-primary btn-xs btnEditExpense"
                            data-id="<?php echo $e["id"]; ?>"
                            data-expacc="<?php echo $e["idExpenseAccount"]; ?>"
                            data-paid="<?php echo $e["idPaidThrough"]; ?>"
                            data-amount="<?php echo $e["amount"]; ?>"
                            data-date="<?php echo $e["expenseDate"]; ?>"
                            data-payee="<?php echo htmlspecialchars($e["payee"] ?? "", ENT_QUOTES); ?>"
                            data-reference="<?php echo htmlspecialchars($e["reference"] ?? "", ENT_QUOTES); ?>"
                            data-notes="<?php echo htmlspecialchars($e["notes"] ?? "", ENT_QUOTES); ?>">
                      <i class="fa fa-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-xs btnDeleteExpense" data-id="<?php echo $e["id"]; ?>">
                      <i class="fa fa-trash"></i>
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

<!-- ADD EXPENSE MODAL -->
<div id="modalAddExpense" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" role="form">
        <div class="modal-header" style="background:#00a65a; color:#fff;">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Record Expense</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="newExpense" value="1">

          <div class="form-group">
            <label>Expense Account</label>
            <select class="form-control" name="idExpenseAccount" required>
              <option value="">Select expense account</option>
              <?php echo expenseAccountOptions($expenseAccounts); ?>
            </select>
          </div>

          <div class="form-group">
            <label>Paid Through</label>
            <select class="form-control" name="idPaidThrough" required>
              <option value="">Select account</option>
              <?php echo expenseAccountOptions($paidThrough); ?>
            </select>
          </div>

          <div class="form-group">
            <label>Amount</label>
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input type="number" step="0.01" min="0.01" class="form-control" name="expenseAmount" required>
            </div>
          </div>

          <div class="form-group">
            <label>Date</label>
            <input type="date" class="form-control" name="expenseDate" value="<?php echo date('Y-m-d'); ?>" required>
          </div>

          <div class="form-group">
            <label>Payee / Vendor (optional)</label>
            <input type="text" class="form-control" name="payee">
          </div>

          <div class="form-group">
            <label>Reference (optional)</label>
            <input type="text" class="form-control" name="expenseReference">
          </div>

          <div class="form-group">
            <label>Notes (optional)</label>
            <textarea class="form-control" name="expenseNotes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save Expense</button>
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT EXPENSE MODAL -->
<div id="modalEditExpense" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" role="form">
        <div class="modal-header" style="background:#3c8dbc; color:#fff;">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Expense</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" name="editExpense" id="editExpenseId">

          <div class="form-group">
            <label>Expense Account</label>
            <select class="form-control" name="idExpenseAccount" id="editExpenseAccount" required>
              <?php echo expenseAccountOptions($expenseAccounts); ?>
            </select>
          </div>

          <div class="form-group">
            <label>Paid Through</label>
            <select class="form-control" name="idPaidThrough" id="editPaidThrough" required>
              <?php echo expenseAccountOptions($paidThrough); ?>
            </select>
          </div>

          <div class="form-group">
            <label>Amount</label>
            <div class="input-group">
              <span class="input-group-text">$</span>
              <input type="number" step="0.01" min="0.01" class="form-control" name="expenseAmount" id="editExpenseAmount" required>
            </div>
          </div>

          <div class="form-group">
            <label>Date</label>
            <input type="date" class="form-control" name="expenseDate" id="editExpenseDate" required>
          </div>

          <div class="form-group">
            <label>Payee / Vendor (optional)</label>
            <input type="text" class="form-control" name="payee" id="editExpensePayee">
          </div>

          <div class="form-group">
            <label>Reference (optional)</label>
            <input type="text" class="form-control" name="expenseReference" id="editExpenseReference">
          </div>

          <div class="form-group">
            <label>Notes (optional)</label>
            <textarea class="form-control" name="expenseNotes" id="editExpenseNotes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Expense</button>
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
