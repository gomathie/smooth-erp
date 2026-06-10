<?php

$item = null;
$value = null;
$order = "id";

$products = ControllerProducts::ctrShowProducts($item, $value, $order);

 ?>

<!--  -->
<div class="card">

  <div class="card-header">

    <h3 class="card-title">Recently Added Products</h3>

    <div class="card-tools">

      <button type="button" class="btn btn-box-tool" data-widget="collapse">

        <i class="fa fa-minus"></i>

      </button>

      <button type="button" class="btn btn-box-tool" data-widget="remove">

        <i class="fa fa-times"></i>

      </button>

    </div>

  </div>

  <div class="card-body">

    <ul class="products-list product-list-in-box">

    <?php

    for($i = 0; $i < 7; $i++){

      echo '<li class="item">

        <div class="product-img">

          <img src="'.$products[$i]["image"].'" alt="Product Image">

        </div>

        <div class="product-info">

          <a href="" class="product-title">

            '.$products[$i]["description"].'

            <span class="badge text-bg-warning">$'.$products[$i]["sellingPrice"].'</span>

          </a>
    
       </div>

      </li>';

    }

    ?>

    </ul>

  </div>

  <div class="card-footer text-center">

    <a href="products">View All Products</a>
  
  </div>

</div>

