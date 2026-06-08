<?php

if($_SESSION["profile"] == "Special"){

  echo '<script>

    window.location = "home";

  </script>';

  return;

}

?>
<!--  -->
<div class="content-wrapper">

  <section class="content-header">

    <h1>

      Invoices

    </h1>

    <ol class="breadcrumb">

      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>

      <li class="active">Create Invoice</li>

    </ol>

  </section>

  <section class="content">

    <div class="row">

      <!--=============================================
      THE FORM
      =============================================-->
      <div class="col-lg-5 col-xs-12">

        <div class="box box-default">

          <div class="box-header with-border"></div>

          <form role="form" method="post" class="invoiceForm">

            <div class="box-body">

                <div class="box">

                    <!--=====================================
                    =            SELLER INPUT           =
                    ======================================-->


                    <div class="form-group">

                      <div class="input-group">

                        <span class="input-group-addon"><i class="fa fa-user"></i></span>

                        <input type="text" class="form-control" name="newSeller" id="newSeller" value="<?php echo $_SESSION["name"]; ?>" readonly>

                        <input type="hidden" name="idSeller" value="<?php echo $_SESSION["id"]; ?>">

                      </div>

                    </div>


                    <!--=====================================
                    CODE INPUT
                    ======================================-->


                    <div class="form-group">

                      <div class="input-group">

                        <span class="input-group-addon"><i class="fa fa-key"></i></span>


                        <?php
                          $item = null;
                          $value = null;

                          $invoices = ControllerInvoices::ctrShowInvoices($item, $value);

                          if(!$invoices){

                            echo '<input type="text" class="form-control" name="newInvoice" id="newInvoice" value="10001" readonly>';
                          }
                          else{

                            foreach ($invoices as $key => $value) {

                            }

                            $code = $value["invoiceNumber"] +1;

                            echo '<input type="text" class="form-control" name="newInvoice" id="newInvoice" value="'.$code.'" readonly>';

                          }

                        ?>

                      </div>


                    </div>

                    <!-- ORDER REFERENCE -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-tag"></i></span>
                        <input type="text" class="form-control" name="orderReference" id="orderReference" placeholder="Order reference (optional)">
                      </div>
                    </div>


                    <!--=====================================
                    =            CUSTOMER INPUT           =
                    ======================================-->

                    <!--  -->
                    <div class="form-group">

                      <div class="input-group">

                        <span class="input-group-addon"><i class="fa fa-users"></i></span>
                        <select class="form-control" name="selectCustomer" id="selectCustomer" required>

                            <option value="">Select Customer</option>

                            <?php

                            $item = null;
                            $value = null;

                            $customers = ControllerCustomers::ctrShowCustomers($item, $value);

                            foreach ($customers as $key => $value) {
                              echo '<option value="'.$value["id"].'">'.$value["name"].'</option>';
                            }


                            ?>

                        </select>

                        <span class="input-group-addon"><button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#modalAddCustomer" data-dismiss="modal">Add Customer</button></span>

                      </div>

                    </div>

                    <!--=====================================
                    =            PRODUCT INPUT           =
                    ======================================-->


                    <div class="form-group row newProduct">


                    </div>

                    <input type="hidden" name="productsList" id="productsList">

                    <!--=====================================
                    =            ADD PRODUCT BUTTON          =
                    ======================================-->

                    <button type="button" class="btn btn-default btn-sm hidden-lg btnAddProductInvoice">Add Product</button>
                    <button type="button" class="btn btn-info btn-sm btnAddServiceInvoice"><i class="fa fa-wrench"></i> Add Service / Custom Line</button>

                    <hr>

                    <div class="row">
                      <div class="col-xs-12 col-sm-8 col-sm-offset-4">
                        <table class="table table-condensed" style="font-size:13px;">
                          <tbody>
                            <tr>
                              <td class="text-right" style="color:#888; width:55%;">Subtotal</td>
                              <td class="text-right">$ <span id="subtotalDisplay">0.00</span>
                                <input type="hidden" name="subtotal" id="invoiceSubtotal" value="0">
                              </td>
                            </tr>
                            <tr>
                              <td class="text-right" style="color:#888;">Discount</td>
                              <td>
                                <div class="input-group input-group-sm">
                                  <input type="number" class="form-control" id="invoiceDiscountValue" name="discountValue" min="0" step="0.01" value="0">
                                  <span class="input-group-btn">
                                    <select class="form-control" id="invoiceDiscountType" name="discountType" style="height:30px;">
                                      <option value="amount">$</option>
                                      <option value="percent">%</option>
                                    </select>
                                  </span>
                                </div>
                                <small class="text-muted">= $ <span id="invoiceDiscountAmountDisplay">0.00</span></small>
                                <input type="hidden" name="discount" id="invoiceDiscount" value="0">
                              </td>
                            </tr>
                            <tr>
                              <td class="text-right" style="color:#888;">Shipping ($)</td>
                              <td><input type="number" class="form-control input-sm" id="invoiceShipping" name="shipping" min="0" step="0.01" value="0"></td>
                            </tr>
                            <tr>
                              <td class="text-right" style="color:#888;">Adjustment ($)</td>
                              <td><input type="number" class="form-control input-sm" id="invoiceAdjustments" name="adjustments" step="0.01" value="0"></td>
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
                                <input type="hidden" name="newSaleTotal" id="newSaleTotal" totalSale="0" value="0">
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
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        <input type="date" class="form-control" name="dueDate" id="dueDate">
                      </div>
                    </div>

                    <!-- PAYMENT TERMS -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                        <select class="form-control" name="paymentTerms" id="paymentTerms">
                          <option value="due_on_receipt">Due on Receipt</option>
                          <option value="net_15">Net 15</option>
                          <option value="net_30">Net 30</option>
                          <option value="net_45">Net 45</option>
                          <option value="net_60">Net 60</option>
                          <option value="end_of_month">End of Month</option>
                        </select>
                      </div>
                    </div>

                    <!-- STATUS -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-info"></i></span>
                        <select class="form-control" name="invoiceStatus" id="invoiceStatus" required>
                          <option value="draft">Draft</option>
                          <option value="sent">Sent</option>
                        </select>
                      </div>
                      <p class="help-block" style="margin:5px 0 0;">Paid / Partially Paid are set automatically when you record payments.</p>
                    </div>

                    <!-- NOTES -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
                        <textarea class="form-control" name="notes" id="notes" placeholder="Notes"></textarea>
                      </div>
                    </div>

                    <!-- TERMS & CONDITIONS -->
                    <div class="form-group">
                      <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-legal"></i></span>
                        <textarea class="form-control" name="termsConditions" id="termsConditions" placeholder="Terms &amp; Conditions" rows="3"></textarea>
                      </div>
                    </div>

                </div>

            </div>

            <div class="box-footer">
              <button type="submit" class="btn btn-success pull-right">Save Invoice</button>
            </div>
          </form>

          <?php

            $saveInvoice = new ControllerInvoices();
            $saveInvoice -> ctrCreateInvoice();

          ?>

        </div>

      </div>


      <!--=============================================
      =            PRODUCTS TABLE                   =
      =============================================-->

		<!--  -->
      <div class="col-lg-7 hidden-md hidden-sm hidden-xs">

          <div class="box box-default">

            <div class="box-header with-border"></div>

            <div class="box-body">

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
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Add Customer</h4>
        </div>
        <div class="modal-body">
          <div class="box-body">

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input class="form-control input-lg" type="text" name="newCustomer" placeholder="Write name" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key"></i></span>
                <input class="form-control input-lg" type="number" min="0" name="newIdDocument" placeholder="Write your ID" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input class="form-control input-lg" type="text" name="newEmail" placeholder="Email" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                <input class="form-control input-lg" type="text" name="newPhone" placeholder="phone" data-inputmask="'mask':'(999) 999-9999'" data-mask required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                <input class="form-control input-lg" type="text" name="newAddress" placeholder="Address" required>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input class="form-control input-lg" type="text" name="newBirthdate" placeholder="Birthdate" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask required>
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Customer</button>
          <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
        </div>

        <?php

          $createCustomer = new ControllerCustomers();
          $createCustomer -> ctrCreateCustomer();

        ?>

      </form>
    </div>

  </div>
</div>
