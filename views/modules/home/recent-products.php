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

    // Only render as many items as there actually are (was a fixed 7, which
    // produced blank/dummy rows with empty images for orgs with fewer products).
    $count = is_array($products) ? count($products) : 0;
    $topN  = min(7, $count);

    for($i = 0; $i < $topN; $i++){

      $img = $products[$i]["image"] !== "" ? $products[$i]["image"] : "views/img/products/default/anonymous.png";

      echo '<li class="item">

        <div class="product-img">

          <img src="'.$img.'" alt="Product Image">

        </div>

        <div class="product-info">

          <a href="" class="product-title">

            '.$products[$i]["description"].'

            <span class="badge text-bg-warning">$'.$products[$i]["sellingPrice"].'</span>

          </a>

       </div>

      </li>';

    }

    if ($topN === 0) {
      echo '<li class="item"><div class="product-info text-muted">' . t('No products yet') . '</div></li>';
    }

    ?>

    </ul>

  </div>

  <div class="card-footer text-center">

    <a href="products">View All Products</a>
  
  </div>

</div>

