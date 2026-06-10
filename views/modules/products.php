<?php

if(!Permission::has("products")){

  echo '<script>

    window.location = "home";

  </script>';

  return;

}

?>
<div class="content-wrapper">

  <section class="content-header">

    <h1>

      <?php echo t('Product Management'); ?>

    </h1>

    <ol class="breadcrumb">
		<!--  -->
      <li><a href="home"><i class="fa fa-dashboard"></i> <?php echo t('Home'); ?></a></li>

      <li class="active"><?php echo t('Dashboard'); ?></li>

    </ol>

  </section>

  <section class="content">

    <div class="card">

      <div class="card-header">

        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProduct"> <i class="fa fa-plus"></i> <?php echo t('Add Product'); ?></button>

      </div>

      <div class="card-body">

        <table class="table table-bordered table-hover table-striped dt-responsive productsTable" width="100%">
       
          <thead>
			<!--  -->
           <tr>
             
             <th style="width:10px">#</th>
             <th><?php echo t('Image'); ?></th>
             <th><?php echo t('Code'); ?></th>
             <th><?php echo t('Description'); ?></th>
             <th><?php echo t('Category'); ?></th>
             <th><?php echo t('Stock'); ?></th>
             <th><?php echo t('Buying Price'); ?></th>
             <th><?php echo t('Selling Price'); ?></th>
             <th><?php echo t('Date added'); ?></th>
             <th><?php echo t('Actions'); ?></th>

           </tr> 

          </thead>

        </table>

        <input type="hidden" value="<?php echo $_SESSION['profile']; ?>" id="hiddenProfile">

      </div>
    
    </div>

  </section>

</div>

<!--=====================================
=            module add Product            =
======================================-->

<!-- Modal -->
<div id="addProduct" class="modal fade" role="dialog">
	<!--  -->
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <!--=====================================
        HEADER
        ======================================-->

        <div class="modal-header" style="background: #DD4B39; color: #fff">

          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

          <h4 class="modal-title">Add Product</h4>

        </div>

        <!--=====================================
        BODY
        ======================================-->

        <div class="modal-body">

          <div class="card-body">

            <!-- input category -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-th"></i></span>

                <select class="form-control input-lg" id="newCategory" name="newCategory">

                  <option value="">Select Category</option>

                   <?php

                    $item = null;
                    $value1 = null;

                    $categories = controllerCategories::ctrShowCategories($item, $value1);

                    foreach ($categories as $key => $value) {
                      
                      echo '<option value="'.$value["id"].'">'.$value["Category"].'</option>';
                    }

                  ?>

                </select>

              </div>

            </div>

            <!-- input type (Goods vs Service) -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-cube"></i></span>

                <select class="form-control input-lg" id="newProductType" name="newProductType">
                  <option value="good">Goods (stock-tracked)</option>
                  <option value="service">Service (no stock)</option>
                </select>

              </div>

            </div>

            <!--Input Code -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-code"></i></span>

                <input class="form-control input-lg" type="text" id="newCode" name="newCode" placeholder="Add Product Code" required>

              </div>

            </div>

            <!-- input description -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-product-hunt"></i></span>

                <input class="form-control input-lg" type="text" id="newDescription" name="newDescription" placeholder="Add Description/Product Name" required>

              </div>

            </div>

             <!-- input Stock -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-check"></i></span>

                <input class="form-control input-lg" type="number" id="newStock" name="newStock" placeholder="Add Stock" min="0" required>

              </div>

            </div>

            <!-- INPUT BUYING PRICE -->
            <div class="form-group row">

              <div class="col-12 col-sm-6">

                <div class="input-group"> 

                  <span class="input-group-text"><i class="fa fa-arrow-up"></i></span> 

                  <input type="number" class="form-control input-lg" id="newBuyingPrice" name="newBuyingPrice" step="any" min="0" placeholder="Buying Price" required>

                </div>

              </div>
			  <!--  -->

              <!-- INPUT SELLING PRICE -->
              <div class="col-12 col-sm-6">  

                <div class="input-group"> 

                  <span class="input-group-text"><i class="fa fa-arrow-down"></i></span> 

                  <input type="number" class="form-control input-lg" id="newSellingPrice" name="newSellingPrice" step="any" min="0" placeholder="Selling Price" required>

                </div> 

                <br>

                <!-- CHECKBOX PERCENTAGE -->
                <div class="col-6"> 

                  <div class="form-group">   

                    <label>     

                      <input type="checkbox" class="minimal percentage" checked>

                      Use Percentage

                    </label>

                  </div>

                </div>

                <!-- INPUT PERCENTAGE -->
                <div class="col-6" style="padding:0">

                  <div class="input-group"> 

                    <input type="number" class="form-control input-lg newPercentage" min="0" value="40" required>

                    <span class="input-group-text"><i class="fa fa-percent"></i></span>

                  </div>

                </div>

              </div>

            </div>

            <!-- input image -->
            <div class="form-group">

              <div class="panel">Upload image</div>

              <input id="newProdPhoto" type="file" class="newImage" name="newProdPhoto">

              <p class="help-block">Maximum size 2Mb</p>

              <img src="views/img/products/default/anonymous.png" class="img-thumbnail preview" alt="" width="100px">

            </div> 

          </div>

        </div>

        <!--=====================================
        FOOTER
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-danger float-start" data-bs-dismiss="modal">Close</button>

          <button type="submit" class="btn btn-success">Save Product</button>

        </div>

      </form>
	  <!--  -->

      <?php

          $createProduct = new ControllerProducts();
          $createProduct -> ctrCreateProducts();

        ?> 
    </div>

  </div>

</div>

<!--====  End of module add Product  ====-->

<!--=====================================
EDIT PRODUCT
======================================-->

<div id="modalEditProduct" class="modal fade" role="dialog">
  
  <div class="modal-dialog">

    <div class="modal-content">

      <form role="form" method="post" enctype="multipart/form-data">

        <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <!--=====================================
        HEADER
        ======================================-->

        <div class="modal-header" style="background:#DD4B39; color:white">

          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

          <h4 class="modal-title">Edit product</h4>

        </div>

        <!--=====================================
         BODY
        ======================================-->
		<!--  -->
        <div class="modal-body">

          <div class="card-body">

            <!-- Select Category -->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-text"><i class="fa fa-th"></i></span> 

                <select class="form-control input-lg" name="editCategory" readonly required>

                  <option id="editCategory"></option>

                </select>

              </div>

            </div>

            <!-- type (Goods vs Service) -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-cube"></i></span>

                <select class="form-control input-lg" id="editProductType" name="editProductType">
                  <option value="good">Goods (stock-tracked)</option>
                  <option value="service">Service (no stock)</option>
                </select>

              </div>

            </div>

            <!-- INPUT FOR THE CODE -->
            <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-text"><i class="fa fa-code"></i></span> 

                <input type="text" class="form-control input-lg" id="editCode" name="editCode" readonly required>

              </div>

            </div>

            <!-- INPUT FOR THE DESCRIPTION -->
             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-text"><i class="fa fa-product-hunt"></i></span> 

                <input type="text" class="form-control input-lg" id="editDescription" name="editDescription" required>

              </div>

            </div>

             <!-- INPUT FOR THE STOCK -->
             <div class="form-group">
              
              <div class="input-group">
              
                <span class="input-group-text"><i class="fa fa-check"></i></span> 

                <input type="number" class="form-control input-lg" id="editStock" name="editStock" min="0" required>

              </div>

            </div>

             <!-- INPUT FOR BUYING PRICE -->
             <div class="form-group row">

                <div class="col-12 col-sm-6">
                
                  <div class="input-group">
                  
                    <span class="input-group-text"><i class="fa fa-arrow-up"></i></span> 

                    <input type="number" class="form-control input-lg" id="editBuyingPrice" name="editBuyingPrice" step="any" min="0" required>

                  </div>

                </div><!--  -->

                <!-- INPUT FOR SELLING PRICE -->
                <div class="col-12 col-sm-6">
                
                  <div class="input-group">
                  
                    <span class="input-group-text"><i class="fa fa-arrow-down"></i></span> 

                    <input type="number" class="form-control input-lg" id="editSellingPrice" name="editSellingPrice" step="any" min="0" readonly required>

                  </div>
                
                  <br>

                  <!-- PERCENTAGE CHECKBOX -->
                  <div class="col-6">
                    
                    <div class="form-group">
                      
                      <label>
                        
                        <input type="checkbox" class="minimal percentage" checked>
                        
                        Use Percentage

                      </label>

                    </div>

                  </div>

                  <!-- INPUT FOR PORCENTAJE -->
                  <div class="col-6" style="padding:0">
                    
                    <div class="input-group">
                      
                      <input type="number" class="form-control input-lg newPercentage" min="0" value="40" required>

                      <span class="input-group-text"><i class="fa fa-percent"></i></span>

                    </div>

                  </div>

                </div>

            </div>

            <!-- INPUT TO UPLOAD IMAGE -->
             <div class="form-group">
              
              <div class="panel">Upload Image</div>

              <input type="file" class="newImage" name="editImage">

              <p class="help-block">2MB max</p>

              <img src="views/img/products/default/anonymous.png" class="img-thumbnail preview" width="100px">

              <input type="hidden" name="currentImage" id="currentImage">

            </div>

          </div>

        </div>

        <!--=====================================
        FOOTER
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-danger float-start" data-bs-dismiss="modal">Close</button>

          <button type="submit" class="btn btn-success">Save Changes</button>

        </div>

      </form>

        <?php

          $editProduct = new controllerProducts();
          $editProduct -> ctrEditProduct();

        ?>      

    </div>

  </div>

</div><!--  -->

<?php

  $deleteProduct = new controllerProducts();
  $deleteProduct -> ctrDeleteProduct();

?>

