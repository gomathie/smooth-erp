<?php

if (!Permission::has("sales")) {
  echo '<script>window.location = "home";</script>';
  return;
}

$quote = ControllerQuotations::ctrShowQuotations("id", (int)($_GET["idQuotation"] ?? 0));

if (!is_array($quote)) {
  echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger" style="margin:20px;">Quotation not found.</div></section></div>';
  return;
}

if ($quote["status"] === "invoiced") {
  echo '<script>window.location = "quotations";</script>';
  return;
}

$seller        = ControllerUsers::ctrShowUsers("id", $quote["idSeller"]);
$thisCustomer  = ControllerCustomers::ctrShowCustomers("id", $quote["idCustomer"]);
$allCustomers  = ControllerCustomers::ctrShowCustomers(null, null);
$taxPercentage = $quote["netPrice"] ? round($quote["tax"] * 100 / $quote["netPrice"]) : 0;
$items         = json_decode($quote["items"], true) ?: [];
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>Edit Quotation</h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="quotations">Quotations</a></li>
      <li class="active">Edit</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">

      <div class="col-lg-5 col-12">
        <div class="card">
          <div class="card-header"></div>

          <form role="form" method="post" class="quotationForm">
            <div class="card-body">
              <div class="card">

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($seller["name"]); ?>" readonly>
                    <input type="hidden" name="idSeller" value="<?php echo $seller["id"]; ?>">
                  </div>
                </div>

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($quote["quoteNumber"]); ?>" readonly>
                    <input type="hidden" name="editQuotation" value="<?php echo $quote["id"]; ?>">
                  </div>
                </div>

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-tag"></i></span>
                    <input type="text" class="form-control" name="orderReference" value="<?php echo htmlspecialchars($quote["orderReference"] ?? ""); ?>" placeholder="Reference (optional)">
                  </div>
                </div>

                <!-- CURRENCY -->
                <?php $qCur = $quote["currency"] ?? Currency::base(); if (Currency::isEnabled()) { ?>
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-money"></i></span>
                    <select class="form-control" name="currency">
                      <?php foreach (Currency::activeForOrg() as $c) { $sel = $c["code"]===$qCur?' selected':''; echo '<option value="'.$c["code"].'"'.$sel.'>'.htmlspecialchars($c["code"]." — ".$c["name"]." (".$c["symbol"].")").'</option>'; } ?>
                    </select>
                  </div>
                </div>
                <?php } else { ?>
                  <input type="hidden" name="currency" value="<?php echo htmlspecialchars($qCur); ?>">
                <?php } ?>

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-users"></i></span>
                    <select class="form-control" name="selectCustomer" id="selectCustomer" required>
                      <option value="<?php echo $thisCustomer["id"]; ?>"><?php echo htmlspecialchars($thisCustomer["name"]); ?></option>
                      <?php foreach ($allCustomers as $c) { echo '<option value="'.$c["id"].'">'.htmlspecialchars($c["name"]).'</option>'; } ?>
                    </select>
                    <span class="input-group-text"><button type="button" class="btn btn-primary btn-xs" data-bs-toggle="modal" data-bs-target="#modalAddCustomer">Add</button></span>
                  </div>
                </div>

                <!-- LINE ITEMS (prefilled) -->
                <div class="form-group row newProduct">
                  <?php
                  foreach ($items as $it) {
                    $isService = empty($it["id"]);
                    $desc = htmlspecialchars($it["description"] ?? "", ENT_QUOTES);
                    $qty  = (int)($it["quantity"] ?? 1);
                    $unit = (float)($it["price"] ?? 0);
                    $line = (float)($it["totalPrice"] ?? 0);

                    echo '<div class="row" style="padding:5px 15px">'
                      . '<div class="col-6" style="padding-right:0px"><div class="input-group">'
                        . '<span class="input-group-text"><button type="button" class="btn btn-danger btn-xs removeProduct" idProduct="'.htmlspecialchars($it["id"] ?? "", ENT_QUOTES).'"><i class="fa fa-times"></i></button></span>';

                    if ($isService) {
                      echo '<input type="text" class="form-control newProductDescription" idProduct="" name="addServiceLine" value="'.$desc.'" required>';
                    } else {
                      echo '<input type="text" class="form-control newProductDescription" idProduct="'.htmlspecialchars($it["id"], ENT_QUOTES).'" name="addProductQuote" value="'.$desc.'" readonly required>';
                    }

                    echo '</div></div>'
                      . '<div class="col-3"><input type="number" class="form-control newProductQuantity" name="newProductQuantity" min="1" value="'.$qty.'" stock="999999" newStock="'.$qty.'" required></div>'
                      . '<div class="col-3 enterPrice" style="padding-left:0px"><div class="input-group">'
                        . '<span class="input-group-text"><i class="ion ion-social-usd"></i></span>'
                        . '<input type="'.($isService ? 'number' : 'text').'" class="form-control newProductPrice'.($isService ? ' serviceRate' : '').'" realPrice="'.$unit.'" name="newProductPrice" value="'.$line.'" '.($isService ? '' : 'readonly').' required>'
                      . '</div></div>'
                    . '</div>';
                  }
                  ?>
                </div>

                <input type="hidden" name="productsList" id="productsList">

                <button type="button" class="btn btn-default btn-sm btnAddProductQuote hidden-lg">Add Product</button>
                <button type="button" class="btn btn-info btn-sm btnAddServiceQuote"><i class="fa fa-wrench"></i> Add Service / Custom Line</button>

                <hr>

                <div class="row">
                  <div class="col-12 col-sm-8 col-sm-offset-4">
                    <table class="table table-condensed" style="font-size:13px;">
                      <tbody>
                        <tr>
                          <td class="text-right" style="color:#888; width:50%;">Subtotal</td>
                          <td class="text-right">$ <span id="subtotalDisplay"><?php echo number_format((float)$quote["subtotal"], 2); ?></span>
                            <input type="hidden" name="subtotal" id="quoteSubtotal" value="<?php echo $quote["subtotal"]; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td class="text-right" style="color:#888;">Discount</td>
                          <td>
                            <div class="input-group input-group-sm">
                              <input type="number" class="form-control" id="quoteDiscountValue" name="discountValue" min="0" step="0.01" value="<?php echo $quote["discountValue"]; ?>">
                              <span class="input-group-btn">
                                <select class="form-control" id="quoteDiscountType" name="discountType" style="height:30px;">
                                  <option value="amount"  <?php echo $quote["discountType"]==="amount"?"selected":""; ?>>$</option>
                                  <option value="percent" <?php echo $quote["discountType"]==="percent"?"selected":""; ?>>%</option>
                                </select>
                              </span>
                            </div>
                            <small class="text-muted">= $ <span id="discountAmountDisplay"><?php echo number_format((float)$quote["discount"], 2); ?></span></small>
                            <input type="hidden" name="discount" id="quoteDiscountAmount" value="<?php echo $quote["discount"]; ?>">
                          </td>
                        </tr>
                        <tr>
                          <td class="text-right" style="color:#888;">Shipping ($)</td>
                          <td><input type="number" class="form-control input-sm" id="quoteShipping" name="shipping" min="0" step="0.01" value="<?php echo $quote["shipping"]; ?>"></td>
                        </tr>
                        <tr>
                          <td class="text-right" style="color:#888;">Adjustment ($)</td>
                          <td><input type="number" class="form-control input-sm" id="quoteAdjustments" name="adjustments" step="0.01" value="<?php echo $quote["adjustments"]; ?>"></td>
                        </tr>
                        <tr>
                          <td class="text-right" style="color:#888;">Tax (%)</td>
                          <td>
                            <div class="input-group input-group-sm">
                              <input type="number" class="form-control" name="newTaxSale" id="newTaxSale" value="<?php echo $taxPercentage; ?>" min="0">
                              <span class="input-group-text"><i class="fa fa-percent"></i></span>
                            </div>
                            <input type="hidden" name="newTaxPrice" id="newTaxPrice" value="<?php echo $quote["tax"]; ?>">
                            <input type="hidden" name="newNetPrice" id="newNetPrice" value="<?php echo $quote["netPrice"]; ?>">
                          </td>
                        </tr>
                        <tr style="background:#f5f5f5;">
                          <td class="text-right"><strong>Total</strong></td>
                          <td class="text-right">
                            <strong>$ <span id="grandTotalDisplay"><?php echo number_format((float)$quote["totalPrice"], 2); ?></span></strong>
                            <input type="hidden" name="saleTotal" id="saleTotal" value="<?php echo $quote["totalPrice"]; ?>">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <hr>

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control" name="expiryDate" value="<?php echo $quote["expiryDate"]; ?>">
                  </div>
                </div>

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-info"></i></span>
                    <?php $st = $quote["status"]; ?>
                    <select class="form-control" name="quoteStatus" required>
                      <option value="draft"    <?php echo $st==="draft"?"selected":""; ?>>Draft</option>
                      <option value="sent"     <?php echo $st==="sent"?"selected":""; ?>>Sent</option>
                      <option value="accepted" <?php echo $st==="accepted"?"selected":""; ?>>Accepted</option>
                      <option value="declined" <?php echo $st==="declined"?"selected":""; ?>>Declined</option>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-file-text-o"></i></span>
                    <textarea class="form-control" name="notes" placeholder="Notes"><?php echo htmlspecialchars($quote["notes"] ?? ""); ?></textarea>
                  </div>
                </div>

                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-legal"></i></span>
                    <textarea class="form-control" name="termsConditions" rows="3" placeholder="Terms &amp; Conditions"><?php echo htmlspecialchars($quote["termsConditions"] ?? ""); ?></textarea>
                  </div>
                </div>

              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-success float-end">Save Changes</button>
            </div>
          </form>

          <?php
            $editQuotation = new ControllerQuotations();
            $editQuotation->ctrEditQuotation();
          ?>

        </div>
      </div>

      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
        <div class="card">
          <div class="card-header"></div>
          <div class="card-body">
            <table class="table table-bordered table-hover table-striped dt-responsive quotationsProductsTable">
              <thead>
                <tr><th style="width:10px">#</th><th>Image</th><th style="width:30px">Code</th><th>Description</th><th>Stock</th><th>Actions</th></tr>
              </thead>
            </table>
          </div>
        </div>
      </div>

    </div>
  </section>

</div>

<!-- Modal Add Customer -->
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
            <div class="form-group"><div class="input-group"><span class="input-group-text"><i class="fa fa-user"></i></span><input class="form-control input-lg" type="text" name="newCustomer" placeholder="Write name" required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-text"><i class="fa fa-key"></i></span><input class="form-control input-lg" type="number" min="0" name="newIdDocument" placeholder="Write your ID" required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-text"><i class="fa fa-envelope"></i></span><input class="form-control input-lg" type="text" name="newEmail" placeholder="Email" required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-text"><i class="fa fa-phone"></i></span><input class="form-control input-lg" type="text" name="newPhone" placeholder="phone" data-inputmask="'mask':'(999) 999-9999'" data-mask required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-text"><i class="fa fa-map-marker"></i></span><input class="form-control input-lg" type="text" name="newAddress" placeholder="Address" required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-text"><i class="fa fa-calendar"></i></span><input class="form-control input-lg" type="text" name="newBirthdate" placeholder="Birthdate" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required></div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Customer</button>
          <button type="button" class="btn btn-default float-start" data-bs-dismiss="modal">Close</button>
        </div>
        <?php
          $createCustomer = new ControllerCustomers();
          $createCustomer->ctrCreateCustomer();
        ?>
      </form>
    </div>
  </div>
</div>
