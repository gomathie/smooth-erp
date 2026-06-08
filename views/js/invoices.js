/*=============================================
PRODUCTS TABLE FOR INVOICE (create/edit pages)
=============================================*/

$('.invoicesProductsTable').DataTable({
    "ajax": "ajax/datatable-invoice-products.ajax.php",
    "deferRender": true,
    "retrieve": true,
    "processing": true
});

/*=============================================
CALCULATE ALL INVOICE TOTALS
Replaces addingTotalPrices() + addTax() for
invoice pages, factoring in discount/shipping/adjustments.
=============================================*/

function invoiceCalculateTotals() {

    var priceItems = $(".invoiceForm .newProductPrice");
    var subtotal   = 0;

    for (var i = 0; i < priceItems.length; i++) {
        subtotal += parseFloat($(priceItems[i]).val()) || 0;
    }

    var discountType  = $("#invoiceDiscountType").val();
    var discountInput = parseFloat($("#invoiceDiscountValue").val()) || 0;
    var discount = (discountType === "percent")
        ? subtotal * discountInput / 100
        : discountInput;
    if (discount > subtotal) { discount = subtotal; }

    var shipping    = parseFloat($("#invoiceShipping").val())    || 0;
    var adjustments = parseFloat($("#invoiceAdjustments").val()) || 0;
    var taxPercent  = parseFloat($("#newTaxSale").val())         || 0;

    var net = subtotal - discount + shipping + adjustments;
    if (net < 0) { net = 0; }

    var taxAmount  = parseFloat((net * taxPercent / 100).toFixed(2));
    var grandTotal = parseFloat((net + taxAmount).toFixed(2));

    // Update visible displays
    $("#subtotalDisplay").text(subtotal.toFixed(2));
    $("#invoiceDiscountAmountDisplay").text(discount.toFixed(2));
    $("#grandTotalDisplay").text(grandTotal.toFixed(2));

    // Update hidden POST fields
    $("#invoiceSubtotal").val(subtotal.toFixed(2));
    $("#invoiceDiscount").val(discount.toFixed(2));
    $("#newNetPrice").val(net.toFixed(2));
    $("#newTaxPrice").val(taxAmount.toFixed(2));
    $("#saleTotal").val(grandTotal.toFixed(2));

    // Keep #newSaleTotal in sync — totalSale attr = net so sales.js addTax() stays harmless
    $("#newSaleTotal").attr("totalSale", net.toFixed(2));
    $("#newSaleTotal").val(grandTotal.toFixed(2));

    // Keep items JSON in sync
    invoiceListProducts();
}

/*=============================================
ADD PRODUCT FROM TABLE (desktop)
=============================================*/

$(".invoicesProductsTable tbody").on("click", "button.addProductInvoice", function(){

    var idProduct = $(this).attr("idProduct");

    $(this).removeClass("btn-primary addProductInvoice");
    $(this).addClass("btn-default");

    var datum = new FormData();
    datum.append("idProduct", idProduct);

    $.ajax({
        url: "ajax/products.ajax.php",
        method: "POST",
        data: datum,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(answer){

            var description = answer["description"];
            var stock       = answer["stock"];
            var price       = answer["sellingPrice"];
            var type        = answer["type"] || "good";
            var isService   = (type === "service");

            // Services aren't stock-tracked, so an empty/zero stock is fine.
            if (!isService && stock == 0) {
                swal({
                    title: "No stock available",
                    type: "error",
                    confirmButtonText: "Close!"
                });
                $("button[idProduct='"+idProduct+"']").addClass("btn-primary addProductInvoice");
                return;
            }

            var stockAttr    = isService ? 999999 : stock;
            var newStockAttr  = isService ? 999999 : (Number(stock) - 1);

            $(".newProduct").append(
                '<div class="row" style="padding:5px 15px">'
                + '<div class="col-xs-6" style="padding-right:0px">'
                    + '<div class="input-group">'
                        + '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct="'+idProduct+'"><i class="fa fa-times"></i></button></span>'
                        + '<input type="text" class="form-control newProductDescription" idProduct="'+idProduct+'" name="addProductSale" value="'+description+'" readonly required>'
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

            invoiceCalculateTotals();
            $(".newProductPrice").number(true, 2);
        }
    });

});

/*=============================================
RECOVER BUTTON STATE WHEN TABLE REDRAWS
=============================================*/

$(".invoicesProductsTable").on("draw.dt", function(){

    if (localStorage.getItem("removeInvoiceProduct") != null) {

        var listIdProducts = JSON.parse(localStorage.getItem("removeInvoiceProduct"));

        for (var i = 0; i < listIdProducts.length; i++) {
            $("button.recoverButtonInvoice[idProduct='"+listIdProducts[i]["idProduct"]+"']").removeClass("btn-default");
            $("button.recoverButtonInvoice[idProduct='"+listIdProducts[i]["idProduct"]+"']").addClass("btn-primary addProductInvoice");
        }

    }

});

/*=============================================
REMOVE PRODUCT FROM INVOICE
=============================================*/

var idRemoveInvoiceProduct = [];

localStorage.removeItem("removeInvoiceProduct");

$(".invoiceForm").on("click", "button.removeProduct", function(){

    $(this).parent().parent().parent().parent().remove();

    var idProduct = $(this).attr("idProduct");

    if (localStorage.getItem("removeInvoiceProduct") == null) {
        idRemoveInvoiceProduct = [];
    } else {
        idRemoveInvoiceProduct.concat(localStorage.getItem("removeInvoiceProduct"));
    }

    idRemoveInvoiceProduct.push({"idProduct": idProduct});
    localStorage.setItem("removeInvoiceProduct", JSON.stringify(idRemoveInvoiceProduct));

    $("button.recoverButtonInvoice[idProduct='"+idProduct+"']").removeClass("btn-default");
    $("button.recoverButtonInvoice[idProduct='"+idProduct+"']").addClass("btn-primary addProductInvoice");

    invoiceCalculateTotals();

});

/*=============================================
ADD PRODUCT FROM MOBILE (btnAddProductInvoice)
=============================================*/

var numInvoiceProduct = 0;

$(".btnAddProductInvoice").click(function(){

    numInvoiceProduct++;

    var datum = new FormData();
    datum.append("getProducts", "ok");

    $.ajax({
        url: "ajax/products.ajax.php",
        method: "POST",
        data: datum,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(answer){

            $(".newProduct").append(
                '<div class="row" style="padding:5px 15px">'
                + '<div class="col-xs-6" style="padding-right:0px">'
                    + '<div class="input-group">'
                        + '<span class="input-group-addon"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct><i class="fa fa-times"></i></button></span>'
                        + '<select class="form-control newProductDescription" id="invProduct'+numInvoiceProduct+'" idProduct name="newProductDescription" required>'
                            + '<option>Select product</option>'
                        + '</select>'
                    + '</div>'
                + '</div>'
                + '<div class="col-xs-3 enterQuantity">'
                    + '<input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="1" stock newStock required>'
                + '</div>'
                + '<div class="col-xs-3 enterPrice" style="padding-left:0px">'
                    + '<div class="input-group">'
                        + '<span class="input-group-addon"><i class="ion ion-social-usd"></i></span>'
                        + '<input type="text" class="form-control newProductPrice" realPrice="" name="newProductPrice" readonly required>'
                    + '</div>'
                + '</div>'
                + '</div>'
            );

            answer.forEach(function(item){
                if (item.stock != 0) {
                    $("#invProduct"+numInvoiceProduct).append(
                        '<option idProduct="'+item.id+'" value="'+item.description+'">'+item.description+'</option>'
                    );
                }
            });

            invoiceCalculateTotals();
            $(".newProductPrice").number(true, 2);
        }
    });

});

/*=============================================
SELECT PRODUCT — mobile dropdown
=============================================*/

$(".invoiceForm").on("change", "select.newProductDescription", function(){

    var productName           = $(this).val();
    var newProductDescription = $(this).parent().parent().parent().children().children().children(".newProductDescription");
    var newProductPrice       = $(this).parent().parent().parent().children(".enterPrice").children().children(".newProductPrice");
    var newProductQuantity    = $(this).parent().parent().parent().children(".enterQuantity").children(".newProductQuantity");

    var datum = new FormData();
    datum.append("productName", productName);

    $.ajax({
        url: "ajax/products.ajax.php",
        method: "POST",
        data: datum,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(answer){
            $(newProductDescription).attr("idProduct", answer["id"]);
            $(newProductQuantity).attr("stock", answer["stock"]);
            $(newProductQuantity).attr("newStock", Number(answer["stock"]) - 1);
            $(newProductPrice).val(answer["sellingPrice"]);
            $(newProductPrice).attr("realPrice", answer["sellingPrice"]);
            invoiceCalculateTotals();
        }
    });

});

/*=============================================
QUANTITY CHANGE
=============================================*/

$(".invoiceForm").on("change", "input.newProductQuantity", function(){

    var price      = $(this).parent().parent().children(".enterPrice").children().children(".newProductPrice");
    var finalPrice = $(this).val() * price.attr("realPrice");
    price.val(finalPrice);

    var newStock = Number($(this).attr("stock")) - $(this).val();
    $(this).attr("newStock", newStock);

    if (Number($(this).val()) > Number($(this).attr("stock"))) {

        $(this).val(1);
        price.val($(this).val() * price.attr("realPrice"));
        invoiceCalculateTotals();

        swal({
            title: "Quantity exceeds stock",
            text: "Only " + $(this).attr("stock") + " units available!",
            type: "error",
            confirmButtonText: "Close!"
        });

        return;
    }

    invoiceCalculateTotals();

});

/*=============================================
DISCOUNT / SHIPPING / ADJUSTMENT / TAX CHANGE
=============================================*/

$(".invoiceForm").on("input change", "#invoiceDiscountValue, #invoiceDiscountType, #invoiceShipping, #invoiceAdjustments, #newTaxSale", function(){
    invoiceCalculateTotals();
});

/*=============================================
ADD A FREE-TEXT SERVICE / CUSTOM LINE (universal)
=============================================*/

$(".invoiceForm").on("click", ".btnAddServiceInvoice", function(){
    $(".invoiceForm .newProduct").append(
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

$(".invoiceForm").on("input", ".newProductPrice.serviceRate", function(){
    var qty  = parseFloat($(this).closest(".row").find(".newProductQuantity").val()) || 1;
    var rate = (parseFloat($(this).val()) || 0) / qty;
    $(this).attr("realPrice", rate);
    invoiceCalculateTotals();
});

/*=============================================
LIST INVOICE ITEMS AS JSON (per-row; supports products + services)
=============================================*/

function invoiceListProducts(){

    var productsList = [];

    $(".invoiceForm .newProduct > .row").each(function(){
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
EDIT INVOICE
=============================================*/

$(".invoicesTable").on("click", ".btnEditInvoice", function(){

    var idInvoice = $(this).attr("idInvoice");
    window.location = "index.php?route=edit-invoice&idInvoice=" + idInvoice;

});

/*=============================================
DELETE INVOICE
=============================================*/

$(".invoicesTable").on("click", ".btnDeleteInvoice", function(){

    var idInvoice = $(this).attr("idInvoice");

    swal({
        title: "Are you sure?",
        text: "This invoice will be permanently deleted!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "Cancel",
        confirmButtonText: "Yes, delete it!"
    }).then(function(result){
        if (result.value) {
            window.location = "index.php?route=invoices&idInvoice=" + idInvoice;
        }
    });

});

/*=============================================
PRINT INVOICE PDF
=============================================*/

$(".invoicesTable").on("click", ".btnPrintInvoice", function(){

    var invoiceId = $(this).attr("invoiceId");
    window.open("extensions/tcpdf/pdf/invoice-pdf.php?id=" + invoiceId, "_blank");

});
