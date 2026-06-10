<?php

$item = null;
$value = null;
$order = "sales";

$products = ControllerProducts::ctrShowProducts($item, $value, $order);

$colours = array("red","green","yellow","aqua","purple","blue","cyan","magenta","orange","gold");

$salesTotal = ControllerProducts::ctrShowAddingOfTheSales();

$chartColours = ["#ef4444","#10b981","#f59e0b","#06b6d4","#8b5cf6","#3b82f6","#06b6d4","#ec4899","#f97316","#eab308"];

?>

<!--=====================================
Bestseller Products
======================================-->
<div class="card bestseller-card">

	<div class="card-header with-bvalue">

      <h3 class="card-title">Bestseller Products</h3>

    </div>

	<div class="card-body">

      	<div class="row">

	        <div class="col-md-12">

	 			<div class="chart-responsive" id="barChartContainer">
	 				<div class="bar-chart">
	 				<?php
	 				$maxVal = 0;
	 				for($i = 0; $i < 5; $i++){
	 				    $pct = ceil($products[$i]["sales"]*100/$salesTotal["total"]);
	 				    if($pct > $maxVal) $maxVal = $pct;
	 				}
	 				$scale = $maxVal > 0 ? 200 / $maxVal : 1;

	 				for($i = 0; $i < 5; $i++):
	 				    $pct = ceil($products[$i]["sales"]*100/$salesTotal["total"]);
	 				    $barH = round($pct * $scale);
	 				    $colour = $chartColours[$i % count($chartColours)];
	 				?>
	 				<div class="bar-column">
	 					<div class="bar-value" style="color:<?php echo $colour; ?>"><?php echo $pct; ?>%</div>
	 					<div class="bar-track">
	 						<div class="bar-fill" style="height:<?php echo $barH; ?>px; background:<?php echo $colour; ?>;"></div>
	 					</div>
	 					<div class="bar-label"><?php echo $products[$i]["description"]; ?></div>
	 				</div>
	 				<?php endfor; ?>
	 				</div>

	          	</div>

	        </div>

		</div>

    </div>

    <div class="card-footer">

		<ul class="nav">

			 <?php

          	for($i = 0; $i < 5; $i++){

          		$pct = ceil($products[$i]["sales"]*100/$salesTotal["total"]);
          		$colour = $colours[$i % count($colours)];

          		echo '<li>

						 <a href="#">

						 <img src="'.$products[$i]["image"].'" class="img-thumbnail" alt="'.$products[$i]["description"].'">

						 <span class="product-label">'.$products[$i]["description"].'</span>

						 <span class="percentage-badge text-'.$colour.'">
						 '.$pct.'%
						 </span>

						 <div class="progress-tile">
						   <div class="progress-bar-tile bg-'.$colour.'" style="width: '.$pct.'%"></div>
						 </div>

						 </a>

      				</li>';

			}

			?>

		</ul>

    </div>

</div>