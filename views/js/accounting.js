/*=============================================
ACCOUNTING MODULE — expenses + chart of accounts
=============================================*/

/* DataTables for the expense / accounts lists */
$(".expensesTable").DataTable({ "order": [], "retrieve": true });
$(".accountsTable").DataTable({ "order": [], "retrieve": true });

/*=============================================
EDIT EXPENSE — prefill modal
=============================================*/
$(document).on("click", ".btnEditExpense", function () {
    $("#editExpenseId").val($(this).data("id"));
    $("#editExpenseAccount").val($(this).data("expacc"));
    $("#editPaidThrough").val($(this).data("paid"));
    $("#editExpenseAmount").val($(this).data("amount"));
    $("#editExpenseDate").val($(this).data("date"));
    $("#editExpensePayee").val($(this).data("payee"));
    $("#editExpenseReference").val($(this).data("reference"));
    $("#editExpenseNotes").val($(this).data("notes"));
    $("#modalEditExpense").modal("show");
});

/*=============================================
DELETE EXPENSE
=============================================*/
$(document).on("click", ".btnDeleteExpense", function () {
    var id = $(this).data("id");
    swal({
        title: "Delete this expense?",
        text: "Its accounting entry will be reversed.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonText: "Cancel",
        confirmButtonText: "Yes, delete it!"
    }).then(function (result) {
        if (result.value) {
            window.location = "index.php?route=expenses&deleteExpense=" + id;
        }
    });
});

/*=============================================
EDIT ACCOUNT — prefill modal (lock type for system accounts)
=============================================*/
$(document).on("click", ".btnEditAccount", function () {
    $("#editAccountId").val($(this).data("id"));
    $("#editAccountCode").val($(this).data("code"));
    $("#editAccountName").val($(this).data("name"));
    $("#editAccountType").val($(this).data("type"));

    var isSystem = String($(this).data("system")) === "1";
    $("#editAccountType").prop("disabled", isSystem);
    $("#editTypeLocked").toggle(isSystem);

    $("#modalEditAccount").modal("show");
});

/*=============================================
DELETE ACCOUNT
=============================================*/
$(document).on("click", ".btnDeleteAccount", function () {
    var id = $(this).data("id");
    swal({
        title: "Delete this account?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonText: "Cancel",
        confirmButtonText: "Yes, delete it!"
    }).then(function (result) {
        if (result.value) {
            window.location = "index.php?route=chart-of-accounts&deleteAccount=" + id;
        }
    });
});
