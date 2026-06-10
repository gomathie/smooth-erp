<?php

if (!Permission::has("sales")) {
  echo '<script>window.location = "home";</script>';
  return;
}
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>Quotations</h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="quotations">Quotations</a></li>
      <li class="active">Create Quotation</li>
    </ol>
  </section>

  <section class="content">

    <div class="row">

      <!-- THE FORM -->
      <div class="col-lg-5 col-xs-12">
        <div class="box box-default">
          <div class="box-header with-border"></div>

          <form role="form" method="post" class="quotationForm">
            <div class="box-body">
              <div class="box">

                <!-- SELLER -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control" value="<?php echo $_SESSION["name"]; ?>" readonly>
                    <input type="hidden" name="idSeller" value="<?php echo $_SESSION["id"]; ?>">
                  </div>
                </div>

                <!-- NUMBER -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <?php
                      $next = ModelQuotations::mdlNextQuoteNumber();
                      echo '<input type="text" class="form-control" name="newQuotation" id="newQuotation" value="'.$next.'" readonly>';
                    ?>
                  </div>
                </div>

                <!-- ORDER REFERENCE -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                    <input type="text" class="form-control" name="orderReference" id="orderReference" placeholder="Reference (optional)">
                  </div>
                </div>

                <!-- CURRENCY -->
                <?php if (Currency::isEnabled()) { ?>
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-money"></i></span>
                    <select class="form-control" name="currency">
                      <?php foreach (Currency::activeForOrg() as $c) { echo '<option value="'.$c["code"].'">'.htmlspecialchars($c["code"]." — ".$c["name"]." (".$c["symbol"].")").'</option>'; } ?>
                    </select>
                  </div>
                </div>
                <?php } else { ?>
                  <input type="hidden" name="currency" value="<?php echo htmlspecialchars(Currency::base()); ?>">
                <?php } ?>

                <!-- CUSTOMER -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-users"></i></span>
                    <select class="form-control" name="selectCustomer" id="selectCustomer" required>
                      <option value="">Select Customer</option>
                      <?php
                        $customers = ControllerCustomers::ctrShowCustomers(null, null);
                        foreach ($customers as $c) { echo '<option value="'.$c["id"].'">'.htmlspecialchars($c["name"]).'</option>'; }
                      ?>
                    </select>
                    <span class="input-group-addon"><button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modalAddCustomer">Add</button></span>
                  </div>
                </div>

                <!-- LINE ITEMS -->
                <div class="form-group row newProduct"></div>

                <input type="hidden" name="productsList" id="productsList">

                <button type="button" class="btn btn-default btn-sm btnAddProductQuote hidden-lg">Add Product</button>
                <button type="button" class="btn btn-info btn-sm btnAddServiceQuote"><i class="fa fa-wrench"></i> Add Service / Custom Line</button>

                <hr>

                <!-- TOTALS -->
                <div class="row">
                  <div class="col-xs-12 col-sm-8 col-sm-offset-4">
                    <table class="table table-condensed" style="font-size:13px;">
                      <tbody>
                        <tr>
                          <td class="text-right" style="color:#888; width:50%;">Subtotal</td>
                          <td class="text-right">$ <span id="subtotalDisplay">0.00</span>
                            <input type="hidden" name="subtotal" id="quoteSubtotal" value="0">
                          </td>
                        </tr>
                        <tr>
                          <td class="text-right" style="color:#888;">Discount</td>
                          <td>
                            <div class="input-group input-group-sm">
                              <input type="number" class="form-control" id="quoteDiscountValue" name="discountValue" min="0" step="0.01" value="0">
                              <span class="input-group-btn">
                                <select class="form-control" id="quoteDiscountType" name="discountType" style="height:30px;">
                                  <option value="amount">$</option>
                                  <option value="percent">%</option>
                                </select>
                              </span>
                            </div>
                            <small class="text-muted">= $ <span id="discountAmountDisplay">0.00</span></small>
                            <input type="hidden" name="discount" id="quoteDiscountAmount" value="0">
                          </td>
                        </tr>
                        <tr>
                          <td class="text-right" style="color:#888;">Shipping ($)</td>
                          <td><input type="number" class="form-control input-sm" id="quoteShipping" name="shipping" min="0" step="0.01" value="0"></td>
                        </tr>
                        <tr>
                          <td class="text-right" style="color:#888;">Adjustment ($)</td>
                          <td><input type="number" class="form-control input-sm" id="quoteAdjustments" name="adjustments" step="0.01" value="0"></td>
                        </tr>
                        <tr>
                          <td class="text-right" style="color:#888;">Tax (%)</td>
                          <td>
                            <div class="input-group input-group-sm">
                              <input type="number" class="form-control" name="newTaxSale" id="newTaxSale" placeholder="0" min="0">
                              <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                            </div>
                            <input type="hidden" name="newTaxPrice" id="newTaxPrice" value="0">
                            <input type="hidden" name="newNetPrice" id="newNetPrice" value="0">
                          </td>
                        </tr>
                        <tr style="background:#f5f5f5;">
                          <td class="text-right"><strong>Total</strong></td>
                          <td class="text-right">
                            <strong>$ <span id="grandTotalDisplay">0.00</span></strong>
                            <input type="hidden" name="saleTotal" id="saleTotal" value="0">
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <hr>

                <!-- EXPIRY -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control" name="expiryDate" id="expiryDate" placeholder="Expiry date">
                  </div>
                </div>

                <!-- STATUS -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-info"></i></span>
                    <select class="form-control" name="quoteStatus" id="quoteStatus" required>
                      <option value="draft">Draft</option>
                      <option value="sent">Sent</option>
                      <option value="accepted">Accepted</option>
                      <option value="declined">Declined</option>
                    </select>
                  </div>
                </div>

                <!-- NOTES -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
                    <textarea class="form-control" name="notes" id="notes" placeholder="Notes"></textarea>
                  </div>
                </div>

                <!-- TERMS -->
                <div class="form-group">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-legal"></i></span>
                    <textarea class="form-control" name="termsConditions" id="termsConditions" placeholder="Terms &amp; Conditions" rows="3"></textarea>
                  </div>
                </div>

              </div>
            </div>

            <div class="box-footer">
              <button type="submit" class="btn btn-success pull-right">Save Quotation</button>
            </div>
          </form>

          <?php
            $saveQuotation = new ControllerQuotations();
            $saveQuotation->ctrCreateQuotation();
          ?>

        </div>
      </div>

      <!-- PRODUCTS TABLE -->
      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">
        <div class="box box-default">
          <div class="box-header with-border"></div>
          <div class="box-body">
            <table class="table table-bordered table-hover table-striped dt-responsive quotationsProductsTable">
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

<!-- Modal Add Customer -->
<div id="modalAddCustomer" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form role="form" method="POST">
        <div class="modal-header" style="background: #DD4B39; color: #fff">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add Customer</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">
            <div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="fa fa-user"></i></span><input class="form-control input-lg" type="text" name="newCustomer" placeholder="Write name" required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="fa fa-key"></i></span><input class="form-control input-lg" type="number" min="0" name="newIdDocument" placeholder="Write your ID" required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="fa fa-envelope"></i></span><input class="form-control input-lg" type="text" name="newEmail" placeholder="Email" required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="fa fa-phone"></i></span><input class="form-control input-lg" type="text" name="newPhone" placeholder="phone" data-inputmask="'mask':'(999) 999-9999'" data-mask required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="fa fa-map-marker"></i></span><input class="form-control input-lg" type="text" name="newAddress" placeholder="Address" required></div></div>
            <div class="form-group"><div class="input-group"><span class="input-group-addon"><i class="fa fa-calendar"></i></span><input class="form-control input-lg" type="text" name="newBirthdate" placeholder="Birthdate" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required></div></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Customer</button>
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
        </div>
        <?php
          $createCustomer = new ControllerCustomers();
          $createCustomer->ctrCreateCustomer();
        ?>
      </form>
    </div>
  </div>
</div>
