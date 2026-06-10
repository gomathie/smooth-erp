<?php

$item = null;
$value = null;
$order = "stock";

$products = ControllerProducts::ctrShowProducts($item, $value, $order);

?>

<!--=====================================
5 Lowest Stock Levels
======================================-->
<div class="card">

  <div class="card-header">

    <h3 class="card-title"><i class="fa fa-exclamation-triangle"></i> Lowest Stock Levels</h3>

  </div>

  <div class="card-body">

    <ul class="products-list product-list-in-box">

    <?php

    // Products are sorted stock DESC; reverse to get lowest first
    $reversed = array_reverse($products);
    $lowest = array_slice($reversed, 0, 5);

    foreach ($lowest as $p){

      $stockVal = max(0, (int)($p["stock"] ?? 0));
      $badgeClass = $stockVal === 0 ? 'text-bg-danger' : ($stockVal < 10 ? 'text-bg-warning' : 'text-bg-primary');

      echo '<li class="item">

        <div class="product-img">

          <img src="'.$p["image"].'" alt="'.$p["description"].'">

        </div>

        <div class="product-info">

          <a href="" class="product-title">

            '.$p["description"].'

            <span class="badge '.$badgeClass.'">'.number_format($stockVal).' in stock</span>

          </a>

          <span class="product-description">Code: '.htmlspecialchars($p["code"] ?? "").' &mdash; Price: $'.number_format($p["sellingPrice"] ?? 0, 2).'</span>
    
       </div>

      </li>';

    }

    ?>

    </ul>

  </div>

</div>