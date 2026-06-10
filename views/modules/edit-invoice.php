<?php
if (!Permission::has("sales")) { echo '<script>window.location = "home";</script>'; return; }
?>
<div class="content-wrapper">

  <section class="content-header">

    <h1>

      Edit Invoice

    </h1>

    <ol class="breadcrumb">

      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>

      <li class="active">Edit Invoice</li>

    </ol>

  </section>

  <section class="content">

    <div class="row">

      <!--=============================================
      THE FORM
      =============================================-->
      <div class="col-lg-5 col-12">

        <div class="card">

          <div class="card-header"></div>

          <form role="form" method="post" class="invoiceForm">

            <div class="card-body">

                <div class="card">

                  <?php

                    $item = "id";
                    $value = $_GET["idInvoice"];

                    $invoice = ControllerInvoices::ctrShowInvoices($item, $value);

                    $itemUser = "id";
                    $valueUser = $invoice["idSeller"];

                    $seller = ControllerUsers::ctrShowUsers($itemUser, $valueUser);

                    $itemCustomers = "id";
                    $valueCustomers = $invoice["idCustomer"];

                    $customers = ControllerCustomers::ctrShowCustomers($itemCustomers, $valueCustomers);

                    $taxPercentage = $invoice["netPrice"] ? round($invoice["tax"] * 100 / $invoice["netPrice"]) : 0;
                ?>

                    <!--=====================================
                    =            SELLER INPUT           =
                    ======================================-->

                    <div class="form-group">

                      <div class="input-group">

                        <span class="input-group-text"><i class="fa fa-user"></i></span>

                        <input type="text" class="form-control" name="newSeller" id="newSeller" value="<?php echo $seller["name"]; ?>" readonly>

                        <input type="hidden" name="idSeller" value="<?php echo $seller["id"]; ?>">

                      </div>

                    </div>


                    <!--=====================================
                    CODE INPUT
                    ======================================-->


                    <div class="form-group">

                      <div class="input-group">

                        <span class="input-group-text"><i class="fa fa-key"></i></span>

                        <input type="text" class="form-control" value="<?php echo $invoice["invoiceNumber"]; ?>" readonly>
                        <input type="hidden" name="editInvoice" value="<?php echo $invoice["id"]; ?>">

                      </div>


                    </div>

                    <!-- ORDER REFERENCE -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-tag"></i></span>
                        <input type="text" class="form-control" name="orderReference" id="orderReference" placeholder="Order reference (optional)" value="<?php echo htmlspecialchars($invoice['orderReference'] ?? ''); ?>">
                      </div>
                    </div>

                    <!-- CURRENCY -->
                    <?php $invCur = $invoice['currency'] ?? Currency::base(); if (Currency::isEnabled()) { ?>
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-money"></i></span>
                        <select class="form-control" name="currency">
                          <?php foreach (Currency::activeForOrg() as $c) { $sel = $c["code"]===$invCur?' selected':''; echo '<option value="'.$c["code"].'"'.$sel.'>'.htmlspecialchars($c["code"]." — ".$c["name"]." (".$c["symbol"].")").'</option>'; } ?>
                        </select>
                      </div>
                    </div>
                    <?php } else { ?>
                      <input type="hidden" name="currency" value="<?php echo htmlspecialchars($invCur); ?>">
                    <?php } ?>


                    <!--=====================================
                    =            CUSTOMER INPUT           =
                    ======================================-->

                    <!--  -->
                    <div class="form-group">

                      <div class="input-group">

                        <span class="input-group-text"><i class="fa fa-users"></i></span>

                        <select class="form-control" name="selectCustomer" id="selectCustomer" required>

                            <option value="<?php echo $customers["id"]; ?>"><?php echo $customers["name"]; ?></option>

                            <?php

                            $item = null;
                            $value = null;

                            $allCustomers = ControllerCustomers::ctrShowCustomers($item, $value);

                            foreach ($allCustomers as $key => $val) {
                              echo '<option value="'.$val["id"].'">'.$val["name"].'</option>';
                            }


                            ?>

                        </select>

                        <span class="input-group-text"><button type="button" class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#modalAddCustomer" data-bs-dismiss="modal">Add Customer</button></span>

                      </div>

                    </div>

                    <!--=====================================
                    =            PRODUCT INPUT           =
                    ======================================-->


                    <div class="form-group row newProduct">
                      <?php

                        $productList = json_decode($invoice["items"], true);

                        if ($productList) {
                          foreach ($productList as $key => $value) {

                            $isService = empty($value["id"]);

                            if ($isService) {

                              // Free-text service / custom line — fully editable
                              $desc = htmlspecialchars($value["description"] ?? "", ENT_QUOTES);
                              $qty  = (int)($value["quantity"] ?? 1);
                              $unit = (float)($value["price"] ?? 0);
                              $line = (float)($value["totalPrice"] ?? 0);

                              echo '<div class="row" style="padding:5px 15px">'
                                .'<div class="col-6" style="padding-right:0px"><div class="input-group">'
                                  .'<span class="input-group-text"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct=""><i class="fa fa-trash"></i></button></span>'
                                  .'<input type="text" class="form-control newProductDescription" idProduct="" name="addServiceLine" value="'.$desc.'" required>'
                                .'</div></div>'
                                .'<div class="col-3"><input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="'.$qty.'" stock="999999" newStock="999999" required></div>'
                                .'<div class="col-3 enterPrice" style="padding-left:0px"><div class="input-group">'
                                  .'<span class="input-group-text"><i class="ion ion-social-usd"></i></span>'
                                  .'<input type="number" step="0.01" min="0" class="form-control newProductPrice serviceRate" realPrice="'.$unit.'" name="newProductPrice" value="'.$line.'" required>'
                                .'</div></div>'
                              .'</div>';

                            } else {

                              $item = "id";
                              $valueProduct = $value["id"];
                              $order = "id";

                              $answer = ControllerProducts::ctrShowproducts($item, $valueProduct, $order);

                              $lastStock = isset($answer["stock"]) ? $answer["stock"] + $value["quantity"] : $value["quantity"];

                              echo '<div class="row" style="padding:5px 15px">'
                                .'<div class="col-6" style="padding-right:0px"><div class="input-group">'
                                  .'<span class="input-group-text"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct="'.$value["id"].'"><i class="fa fa-trash"></i></button></span>'
                                  .'<input type="text" class="form-control newProductDescription" idProduct="'.$value["id"].'" name="addProduct" value="'.htmlspecialchars($value["description"], ENT_QUOTES).'" readonly required>'
                                .'</div></div>'
                                .'<div class="col-3"><input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="'.$value["quantity"].'" stock="'.$lastStock.'" newStock="'.$value["stock"].'" required></div>'
                                .'<div class="col-3 enterPrice" style="padding-left:0px"><div class="input-group">'
                                  .'<span class="input-group-text"><i class="ion ion-social-usd"></i></span>'
                                  .'<input type="text" class="form-control newProductPrice" realPrice="'.($answer["sellingPrice"] ?? 0).'" name="newProductPrice" value="'.$value["totalPrice"].'" readonly required>'
                                .'</div></div>'
                              .'</div>';

                            }
                          }
                        }

                        ?>

                    </div>

                    <input type="hidden" name="productsList" id="productsList">

                    <!--=====================================
                    =            ADD PRODUCT BUTTON          =
                    ======================================-->

                    <button type="button" class="btn btn-default btn-sm hidden-lg btnAddProductInvoice">Add Product</button>
                    <button type="button" class="btn btn-info btn-sm btnAddServiceInvoice"><i class="fa fa-wrench"></i> Add Service / Custom Line</button>

                    <hr>

                    <div class="row">

                      <!--=====================================
                        TAXES AND TOTAL INPUT
                      ======================================-->

                      <div class="col-12 col-sm-8 col-sm-offset-4">
                        <table class="table table-condensed" style="font-size:13px;">
                          <tbody>
                            <tr>
                              <td class="text-right" style="color:#888; width:55%;">Subtotal</td>
                              <td class="text-right">$ <span id="subtotalDisplay"><?php echo number_format((float)$invoice["subtotal"], 2); ?></span>
                                <input type="hidden" name="subtotal" id="invoiceSubtotal" value="<?php echo $invoice['subtotal']; ?>">
                              </td>
                            </tr>
                            <tr>
                              <td class="text-right" style="color:#888;">Discount</td>
                              <td>
                                <?php $dType = $invoice['discountType'] ?? 'amount'; ?>
                                <div class="input-group input-group-sm">
                                  <input type="number" class="form-control" id="invoiceDiscountValue" name="discountValue" min="0" step="0.01" value="<?php echo $invoice['discountValue'] ?? $invoice['discount']; ?>">
                                  <span class="input-group-btn">
                                    <select class="form-control" id="invoiceDiscountType" name="discountType" style="height:30px;">
                                      <option value="amount"  <?php echo $dType==='amount'?'selected':''; ?>>$</option>
                                      <option value="percent" <?php echo $dType==='percent'?'selected':''; ?>>%</option>
                                    </select>
                                  </span>
                                </div>
                                <small class="text-muted">= $ <span id="invoiceDiscountAmountDisplay"><?php echo number_format((float)$invoice['discount'], 2); ?></span></small>
                                <input type="hidden" name="discount" id="invoiceDiscount" value="<?php echo $invoice['discount']; ?>">
                              </td>
                            </tr>
                            <tr>
                              <td class="text-right" style="color:#888;">Shipping ($)</td>
                              <td><input type="number" class="form-control input-sm" id="invoiceShipping" name="shipping" min="0" step="0.01" value="<?php echo $invoice['shipping']; ?>"></td>
                            </tr>
                            <tr>
                              <td class="text-right" style="color:#888;">Adjustment ($)</td>
                              <td><input type="number" class="form-control input-sm" id="invoiceAdjustments" name="adjustments" step="0.01" value="<?php echo $invoice['adjustments']; ?>"></td>
                            </tr>
                            <tr>
                              <td class="text-right" style="color:#888;">Tax (%)</td>
                              <td>
                                <div class="input-group input-group-sm">
                                  <input type="number" class="form-control" name="newTaxSale" id="newTaxSale" value="<?php echo $taxPercentage; ?>" min="0">
                                  <span class="input-group-text"><i class="fa fa-percent"></i></span>
                                </div>
                                <input type="hidden" name="newTaxPrice" id="newTaxPrice" value="<?php echo $invoice['tax']; ?>">
                                <input type="hidden" name="newNetPrice" id="newNetPrice" value="<?php echo $invoice['netPrice']; ?>">
                              </td>
                            </tr>
                            <tr style="background:#f5f5f5;">
                              <td class="text-right"><strong>Total</strong></td>
                              <td class="text-right">
                                <strong>$ <span id="grandTotalDisplay"><?php echo number_format((float)$invoice['totalPrice'], 2); ?></span></strong>
                                <input type="hidden" name="saleTotal" id="saleTotal" value="<?php echo $invoice['totalPrice']; ?>">
                                <input type="hidden" name="newSaleTotal" id="newSaleTotal" totalSale="<?php echo $invoice['netPrice']; ?>" value="<?php echo $invoice['totalPrice']; ?>">
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                    </div>

                    <hr>

                    <!-- DUE DATE -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        <input type="date" class="form-control" name="dueDate" id="dueDate" value="<?php echo $invoice["dueDate"]; ?>">
                      </div>
                    </div>

                    <!-- PAYMENT TERMS -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                        <select class="form-control" name="paymentTerms" id="paymentTerms">
                          <?php $pt = $invoice['paymentTerms'] ?? 'due_on_receipt'; ?>
                          <option value="due_on_receipt" <?php echo $pt==='due_on_receipt'?'selected':''; ?>>Due on Receipt</option>
                          <option value="net_15"         <?php echo $pt==='net_15'        ?'selected':''; ?>>Net 15</option>
                          <option value="net_30"         <?php echo $pt==='net_30'        ?'selected':''; ?>>Net 30</option>
                          <option value="net_45"         <?php echo $pt==='net_45'        ?'selected':''; ?>>Net 45</option>
                          <option value="net_60"         <?php echo $pt==='net_60'        ?'selected':''; ?>>Net 60</option>
                          <option value="end_of_month"   <?php echo $pt==='end_of_month'  ?'selected':''; ?>>End of Month</option>
                        </select>
                      </div>
                    </div>

                    <!-- STATUS -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-info"></i></span>
                        <?php $baseStatus = in_array($invoice["status"], ["draft","sent"], true) ? $invoice["status"] : "sent"; ?>
                        <select class="form-control" name="invoiceStatus" id="invoiceStatus" required>
                          <option value="draft" <?php echo ($baseStatus=="draft")?"selected":""; ?>>Draft</option>
                          <option value="sent" <?php echo ($baseStatus=="sent")?"selected":""; ?>>Sent</option>
                        </select>
                      </div>
                      <p class="help-block" style="margin:5px 0 0;">Current: <strong><?php echo htmlspecialchars(ucwords(str_replace("_", " ", $invoice["status"]))); ?></strong>. Paid / Partially Paid are driven by recorded payments.</p>
                    </div>

                    <!-- NOTES -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-file-text-o"></i></span>
                        <textarea class="form-control" name="notes" id="notes" placeholder="Notes"><?php echo htmlspecialchars($invoice["notes"]); ?></textarea>
                      </div>
                    </div>

                    <!-- TERMS & CONDITIONS -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-legal"></i></span>
                        <textarea class="form-control" name="termsConditions" id="termsConditions" placeholder="Terms &amp; Conditions" rows="3"><?php echo htmlspecialchars($invoice['termsConditions'] ?? ''); ?></textarea>
                      </div>
                    </div>

                </div>

            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-success float-end">Save Changes</button>
            </div>
          </form>

          <?php

            $editInvoice = new ControllerInvoices();
            $editInvoice -> ctrEditInvoice();

          ?>

        </div>

      </div>


      <!--=============================================
      =            PRODUCTS TABLE                   =
      =============================================-->


      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">

          <div class="card">

            <div class="card-header"></div>

            <div class="card-body">

              <table class="table table-bordered table-hover table-striped dt-responsive invoicesProductsTable">

                <thead>

                   <tr>

                     <th style="width:10px">#</th>
                     <th>Image</th>
                     <th style="width:30px">Code</th>
                     <th>Description</th>
                     <th>Stock</th>
                     <th>Actions</th>

                   </tr>

                </thead>

              </table>

            </div>

          </div>


      </div>

    </div>

  </section>

</div>


<!-- Modal Add Customer reused from create-sale -->
<div id="modalAddCustomer" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <div class="modal-content">
      <form role="form" method="POST">
        <div class="modal-header" style="background: #DD4B39; color: #fff">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add Customer</h4>
        </div>
        <div class="modal-body">
          <div class="card-body">

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                <input class="form-control input-lg" type="text" name="newCustomer" placeholder="Write name" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-key"></i></span>
                <input class="form-control input-lg" type="number" min="0" name="newIdDocument" placeholder="Write your ID" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                <input class="form-control input-lg" type="text" name="newEmail" placeholder="Email" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                <input class="form-control input-lg" type="text" name="newPhone" placeholder="phone" data-inputmask="'mask':'(999) 999-9999'" data-mask required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-map-marker"></i></span>
                <input class="form-control input-lg" type="text" name="newAddress" placeholder="Address" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                <input class="form-control input-lg" type="text" name="newBirthdate" placeholder="Birthdate" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Customer</button>
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Close</button>
        </div>

        <?php

          $createCustomer = new ControllerCustomers();
          $createCustomer -> ctrCreateCustomer();

        ?>

      </form>
    </div>

  </div>
</div>
