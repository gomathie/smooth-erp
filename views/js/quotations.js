/*=============================================
PRODUCTS TABLE FOR QUOTATION (create/edit pages)
=============================================*/
$('.quotationsProductsTable').DataTable({
    "ajax": "ajax/datatable-quotation-products.ajax.php",
    "deferRender": true,
    "retrieve": true,
    "processing": true
});

/*=============================================
CALCULATE ALL QUOTATION TOTALS (discount can be $ or %)
=============================================*/
function quoteCalculateTotals() {

    var priceItems = $(".quotationForm .newProductPrice");
    var subtotal   = 0;
    for (var i = 0; i < priceItems.length; i++) {
        subtotal += parseFloat($(priceItems[i]).val()) || 0;
    }

    var discountType  = $("#quoteDiscountType").val();
    var discountInput = parseFloat($("#quoteDiscountValue").val()) || 0;
    var discountAmount = (discountType === "percent")
        ? subtotal * discountInput / 100
        : discountInput;
    if (discountAmount > subtotal) { discountAmount = subtotal; }

    var shipping    = parseFloat($("#quoteShipping").val())    || 0;
    var adjustments = parseFloat($("#quoteAdjustments").val()) || 0;
    var taxPercent  = parseFloat($("#newTaxSale").val())       || 0;

    var net = subtotal - discountAmount + shipping + adjustments;
    if (net < 0) { net = 0; }

    var taxAmount  = parseFloat((net * taxPercent / 100).toFixed(2));
    var grandTotal = parseFloat((net + taxAmount).toFixed(2));

    $("#subtotalDisplay").text(subtotal.toFixed(2));
    $("#discountAmountDisplay").text(discountAmount.toFixed(2));
    $("#grandTotalDisplay").text(grandTotal.toFixed(2));

    $("#quoteSubtotal").val(subtotal.toFixed(2));
    $("#quoteDiscountAmount").val(discountAmount.toFixed(2));
    $("#newNetPrice").val(net.toFixed(2));
    $("#newTaxPrice").val(taxAmount.toFixed(2));
    $("#saleTotal").val(grandTotal.toFixed(2));

    quoteListProducts();
}

/*=============================================
ADD PRODUCT FROM TABLE (desktop)
=============================================*/
$(".quotationsProductsTable tbody").on("click", "button.addProductQuote", function(){

    var idProduct = $(this).attr("idProduct");
    $(this).removeClass("btn-primary addProductQuote").addClass("btn-default");

    var datum = new FormData();
    datum.append("idProduct", idProduct);

    $.ajax({
        url: "ajax/products.ajax.php",
        method: "POST", data: datum, cache: false, contentType: false, processData: false, dataType: "json",
        success: function(answer){
            var description = answer["description"];
            var stock       = answer["stock"];
            var price       = answer["sellingPrice"];
            var isService   = (answer["type"] || "good") === "service";

            if (!isService && stock == 0) {
                swal({ title: "No stock available", type: "error", confirmButtonText: "Close!" });
                $("button[idProduct='"+idProduct+"']").addClass("btn-primary addProductQuote");
                return;
            }
            var stockAttr   = isService ? 999999 : stock;
            var newStockAttr = isService ? 999999 : (Number(stock) - 1);

            $(".quotationForm .newProduct").append(
                '<div class="row" style="padding:5px 15px">'
                + '<div class="col-xs-6" style="padding-right:0px">'
                    + '<div class="input-group">'
                        + '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct="'+idProduct+'"><i class="fa fa-times"></i></button></span>'
                        + '<input type="text" class="form-control newProductDescription" idProduct="'+idProduct+'" name="addProductQuote" value="'+description+'" readonly required>'
                    + '</div>'
                + '</div>'
                + '<div class="col-xs-3">'
                    + '<input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="1" stock="'+stockAttr+'" newStock="'+newStockAttr+'" required>'
                + '</div>'
                + '<div class="col-xs-3 enterPrice" style="padding-left:0px">'
                    + '<div class="input-group">'
                        + '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'
                        + '<input type="text" class="form-control newProductPrice" realPrice="'+price+'" name="newProductPrice" value="'+price+'" readonly required>'
                    + '</div>'
                + '</div>'
                + '</div>'
            );
            quoteCalculateTotals();
        }
    });
});

/*=============================================
ADD A FREE-TEXT SERVICE / CUSTOM LINE (universal)
=============================================*/
$(".quotationForm").on("click", ".btnAddServiceQuote", function(){
    $(".quotationForm .newProduct").append(
        '<div class="row" style="padding:5px 15px">'
        + '<div class="col-xs-6" style="padding-right:0px">'
            + '<div class="input-group">'
                + '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct=""><i class="fa fa-times"></i></button></span>'
                + '<input type="text" class="form-control newProductDescription" idProduct="" name="addServiceLine" placeholder="Service / description" required>'
            + '</div>'
        + '</div>'
        + '<div class="col-xs-3">'
            + '<input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="1" stock="999999" newStock="999999" required>'
        + '</div>'
        + '<div class="col-xs-3 enterPrice" style="padding-left:0px">'
            + '<div class="input-group">'
                + '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'
                + '<input type="number" step="0.01" min="0" class="form-control newProductPrice serviceRate" realPrice="0" name="newProductPrice" value="" placeholder="Amount" required>'
            + '</div>'
        + '</div>'
        + '</div>'
    );
});

/*=============================================
SERVICE RATE TYPED — keep realPrice (unit) in sync
=============================================*/
$(".quotationForm").on("input", ".newProductPrice.serviceRate", function(){
    var qty  = parseFloat($(this).closest(".row").find(".newProductQuantity").val()) || 1;
    var rate = (parseFloat($(this).val()) || 0) / qty;
    $(this).attr("realPrice", rate);
    quoteCalculateTotals();
});

/*=============================================
REMOVE A LINE
=============================================*/
$(".quotationForm").on("click", "button.removeProduct", function(){
    var idProduct = $(this).attr("idProduct");
    $(this).closest(".row").remove();
    if (idProduct) {
        $("button.recoverButtonQuote[idProduct='"+idProduct+"']").removeClass("btn-default").addClass("btn-primary addProductQuote");
    }
    quoteCalculateTotals();
});

/*=============================================
QUANTITY CHANGE
=============================================*/
$(".quotationForm").on("change", "input.newProductQuantity", function(){
    var price      = $(this).closest(".row").find(".newProductPrice");
    var finalPrice = $(this).val() * price.attr("realPrice");
    price.val(finalPrice);

    var newStock = Number($(this).attr("stock")) - $(this).val();
    $(this).attr("newStock", newStock);

    if (Number($(this).val()) > Number($(this).attr("stock"))) {
        $(this).val(1);
        price.val($(this).val() * price.attr("realPrice"));
        quoteCalculateTotals();
        swal({ title: "Quantity exceeds stock", text: "Only " + $(this).attr("stock") + " units available!", type: "error", confirmButtonText: "Close!" });
        return;
    }
    quoteCalculateTotals();
});

/*=============================================
DISCOUNT / SHIPPING / ADJUSTMENT / TAX CHANGE
=============================================*/
$(".quotationForm").on("input change", "#quoteDiscountValue, #quoteDiscountType, #quoteShipping, #quoteAdjustments, #newTaxSale", function(){
    quoteCalculateTotals();
});

/*=============================================
LIST QUOTATION ITEMS AS JSON (per-row, supports services)
=============================================*/
function quoteListProducts(){
    var productsList = [];
    $(".quotationForm .newProduct > .row").each(function(){
        var desc  = $(this).find(".newProductDescription");
        var qty   = $(this).find(".newProductQuantity");
        var price = $(this).find(".newProductPrice");
        if (desc.length === 0) { return; }
        productsList.push({
            "id":          desc.attr("idProduct") || "",
            "description": desc.val(),
            "quantity":    qty.val(),
            "stock":       qty.attr("newStock"),
            "price":       price.attr("realPrice"),
            "totalPrice":  price.val()
        });
    });
    $("#productsList").val(JSON.stringify(productsList));
}

/*=============================================
LIST ACTIONS
=============================================*/
$(".quotationsTable").on("click", ".btnEditQuotation", function(){
    window.location = "index.php?route=edit-quotation&idQuotation=" + $(this).attr("idQuotation");
});

$(".quotationsTable").on("click", ".btnPrintQuotation", function(){
    window.open("extensions/tcpdf/pdf/quotation-pdf.php?id=" + $(this).attr("idQuotation"), "_blank");
});

$(".quotationsTable").on("click", ".btnConvertQuotation", function(){
    var id = $(this).attr("idQuotation");
    swal({
        title: "Convert to invoice?",
        text: "A draft invoice will be created from this quotation.",
        type: "question", showCancelButton: true, confirmButtonColor: "#3085d6",
        cancelButtonText: "Cancel", confirmButtonText: "Yes, convert"
    }).then(function(result){
        if (result.value) { window.location = "index.php?route=quotations&convertQuote=" + id; }
    });
});

$(".quotationsTable").on("click", ".btnDeleteQuotation", function(){
    var id = $(this).attr("idQuotation");
    swal({
        title: "Are you sure?", text: "This quotation will be permanently deleted!",
        type: "warning", showCancelButton: true, confirmButtonColor: "#3085d6", cancelButtonColor: "#d33",
        cancelButtonText: "Cancel", confirmButtonText: "Yes, delete it!"
    }).then(function(result){
        if (result.value) { window.location = "index.php?route=quotations&idQuotation=" + id; }
    });
});
