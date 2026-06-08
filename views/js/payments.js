/*=============================================
PAYMENTS — invoice detail page interactions
=============================================*/

/*=============================================
OPEN EDIT PAYMENT MODAL (prefill from data attributes)
=============================================*/
$(document).on("click", ".btnEditPayment", function () {

    $("#editPaymentId").val($(this).data("id"));
    $("#editPaymentAmount").val($(this).data("amount"));
    $("#editPaymentDate").val($(this).data("date"));
    $("#editPaymentMode").val($(this).data("mode"));
    $("#editPaymentReference").val($(this).data("reference"));
    $("#editPaymentNotes").val($(this).data("notes"));

    $("#modalEditPayment").modal("show");

});

/*=============================================
DELETE PAYMENT (confirm, then navigate)
=============================================*/
$(document).on("click", ".btnDeletePayment", function () {

    var paymentId = $(this).data("id");
    var idInvoice = $(this).data("invoice");

    swal({
        title: "Delete this payment?",
        text: "The invoice balance and status will be recalculated.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonText: "Cancel",
        confirmButtonText: "Yes, delete it!"
    }).then(function (result) {
        if (result.value) {
            window.location = "index.php?route=invoice-detail&idInvoice=" + idInvoice + "&deletePayment=" + paymentId;
        }
    });

});
