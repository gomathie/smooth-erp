<div class="content-wrapper">

  <section class="content-header">

    <h1>

      <?php echo t('Dashboard'); ?>

      <small><?php echo t('Control panel'); ?></small>

    </h1>

    <ol class="breadcrumb">

      <li><a href="#"><i class="fa fa-dashboard"></i> <?php echo t('Home'); ?></a></li>

      <li class="active"><?php echo t('Dashboard'); ?></li>

    </ol>

  </section>

  <section class="content">

    <div class="row">
      
      <?php

        if($_SESSION["profile"] =="Administrator"){

          include "home/top-boxes.php";

        }

      ?>
    
    </div><!--  -->
    
    <div class="row">

      <div class="col-lg-12">

      <?php

        if($_SESSION["profile"] =="Administrator"){

          include "reports/sales-graph.php";

        }

      ?>
      
      </div>

      <div class="col-lg-6">
        
        <?php

          if($_SESSION["profile"] =="Administrator"){

            include "reports/bestseller-products.php";

          }

        ?>

      </div><!--  -->

       <div class="col-lg-6">
        
        <?php

          if($_SESSION["profile"] =="Administrator"){

            include "home/recent-products.php";

          }

        ?>

      </div>

      <div class="col-lg-6">

        <?php

          if($_SESSION["profile"] =="Administrator"){

            include "home/top-stock.php";

          }

        ?>

      </div>

      <div class="col-lg-6">

        <?php

          if($_SESSION["profile"] =="Administrator"){

            include "home/low-stock.php";

          }

        ?>

      </div>

      <div class="col-lg-12">
           
        <?php

        if($_SESSION["profile"] =="Special" || $_SESSION["profile"] =="Seller"){

           echo '<div class="card">

           <div class="card-header">

           <h1>' . t('Welcome') . ' ' .$_SESSION["name"].'</h1>

           </div>

           </div>';

        }

        ?>

      </div>

    </div>

  </section>

</div>
<!--  -->

