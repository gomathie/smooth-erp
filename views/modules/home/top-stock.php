<?php

$item = null;
$value = null;
$order = "stock";

$products = ControllerProducts::ctrShowProducts($item, $value, $order);

?>

<!--=====================================
Top 10 Highest Stock Available
======================================-->
<div class="card">

  <div class="card-header">

    <h3 class="card-title"><i class="fa fa-cubes"></i> Top 10 Highest Stock Available</h3>

  </div>

  <div class="card-body">

    <ul class="products-list product-list-in-box">

    <?php

    $top = array_slice($products, 0, 10);

    foreach ($top as $p){

      $stockVal = (int)($p["stock"] ?? 0);

      echo '<li class="item">

        <div class="product-img">

          <img src="'.$p["image"].'" alt="'.$p["description"].'">

        </div>

        <div class="product-info">

          <a href="" class="product-title">

            '.$p["description"].'

            <span class="badge text-bg-primary">'.number_format($stockVal).' in stock</span>

          </a>

          <span class="product-description">Code: '.htmlspecialchars($p["code"] ?? "").' &mdash; Price: $'.number_format($p["sellingPrice"] ?? 0, 2).'</span>
    
       </div>

      </li>';

    }

    ?>

    </ul>

  </div>

</div>