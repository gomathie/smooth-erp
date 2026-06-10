<?php
  ini_set('session.use_strict_mode', '1');
  $secureSessionCookie = (
      (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
  );
  session_set_cookie_params([
      'lifetime' => 0,
      'path'     => '/',
      'secure'   => $secureSessionCookie,
      'httponly' => true,
      'samesite' => 'Strict',
  ]);
  session_start();

  if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }

  // Resolve the active UI language (handles ?setlang=, session, cookie).
  if (class_exists('I18n')) { I18n::init(); }

  $validThemeKeys = [
    'blue','blue-light','black','black-light',
    'purple','purple-light','red','red-light',
    'green','green-light','yellow','yellow-light',
  ];
  $currentThemeKey = 'red-light';

  // Read stored key from session or cookie
  $stored = $_SESSION['pos_theme'] ?? $_COOKIE['pos_theme'] ?? '';
  // Migrate old skin-* format (strip the prefix)
  if (strpos($stored, 'skin-') === 0) { $stored = substr($stored, 5); }
  if (in_array($stored, $validThemeKeys, true)) {
      $currentThemeKey = $stored;
  }
  $_SESSION['pos_theme'] = $currentThemeKey;
?>

<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <title>Smooth ERP</title>

  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

  <link rel="icon" href="views/img/template/icono-negro.png">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="pos-theme"  content="<?php echo htmlspecialchars($currentThemeKey, ENT_QUOTES, 'UTF-8'); ?>">

  <!--=================================
  =            Plugins CSS            =
  ==================================-->
  <!-- Bootstrap 4.6 (migrated from Bootstrap 3.3.7) -->
  <link rel="stylesheet" href="views/vendor/bootstrap4/css/bootstrap.min.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="views/bower_components/font-awesome/css/font-awesome.min.css">

  <!-- Ionicons -->
  <link rel="stylesheet" href="views/bower_components/Ionicons/css/ionicons.min.css">
  
  <!-- AdminLTE 3 theme (migrated from AdminLTE 2.4) -->
  <link rel="stylesheet" href="views/vendor/adminlte3/css/adminlte.min.css">
  <!-- AdminLTE 2 box -> AdminLTE 3 card compatibility (until the box->card sweep) -->
  <link rel="stylesheet" href="views/dist/css/alte2-box-compat.css">

  <!-- POS custom theme engine for AdminLTE 3 (CSS variables — edit themes.config.js) -->
  <link rel="stylesheet" href="views/dist/css/pos-themes-alte3.css">

  <!-- themes.config.js must run in <head> to set CSS vars before first paint -->
  <script src="views/js/themes.config.js"></script>

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic"> 

   <!-- DataTables -->
  <link rel="stylesheet" href="views/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" href="views/bower_components/datatables.net-bs/css/responsive.bootstrap.min.css">

  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="views/plugins/iCheck/all.css">

  <!-- Daterange picker -->
  <link rel="stylesheet" href="views/bower_components/bootstrap-daterangepicker/daterangepicker.css">

  <!-- Morris chart -->
  <link rel="stylesheet" href="views/bower_components/morris.js/morris.css">
  
  <!--====  End of Plugins CSS  ====-->
  
  <!--========================================
  =            plugins javascript            =
  =========================================-->
  <!-- jQuery 3 -->
  <script src="views/bower_components/jquery/dist/jquery.min.js"></script>

  <!-- Bootstrap 4.6 bundle (includes Popper) -->
  <script src="views/vendor/bootstrap4/js/bootstrap.bundle.min.js"></script>

  <!-- FastClick -->
  <script src="views/bower_components/fastclick/lib/fastclick.js"></script>

  <!-- AdminLTE 3 App -->
  <script src="views/vendor/adminlte3/js/adminlte.min.js"></script>

   <!-- DataTables -->
  <script src="views/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
  <script src="views/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
  <script src="views/bower_components/datatables.net-bs/js/dataTables.responsive.min.js"></script>
  <script src="views/bower_components/datatables.net-bs/js/responsive.bootstrap.min.js"></script>

  <!-- sweet alert -->
  <script src="views/plugins/sweetalert2/sweetalert2.all.js"></script>

  <!-- By default sweetalert2 doesn't support IE. To enable IE 11 support, include Promise polyfill -->
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>

  <!-- iCheck 1.0.1 -->
  <script src="views/plugins/iCheck/icheck.min.js"></script>
  <!-- InputMask -->
  <script src="views/plugins/input-mask/jquery.inputmask.js"></script>
  <script src="views/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
  <script src="views/plugins/input-mask/jquery.inputmask.extensions.js"></script>
  <!-- jQuery Number -->
  <script src="views/plugins/jqueryNumber/jquerynumber.min.js"></script>

  <!-- daterangepicker http://www.daterangepicker.com/-->
  <script src="views/bower_components/moment/min/moment.min.js"></script>
  <script src="views/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>


  <!-- Morris.js charts http://morrisjs.github.io/morris.js/-->
  <script src="views/bower_components/raphael/raphael.min.js"></script>
  <script src="views/bower_components/morris.js/morris.min.js"></script>

  <!-- ChartJS http://www.chartjs.org/-->
  <script src="views/bower_components/Chart.js/Chart.js"></script>
  
</head>

<?php
  $bodyClass = (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok")
    ? 'hold-transition sidebar-mini layout-fixed sidebar-collapse'
    : 'hold-transition login-page';
?>

<body class="<?php echo $bodyClass; ?>">

<!-- Site wrapper -->

  <?php

    if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok"){

      echo '<div class="wrapper">';

      /*=============================================
      =     Super Admin context handling           =
      =============================================*/
      // The "Exit org" banner link can be triggered from any page.
      if (Tenant::isSuperAdmin()) { ControllerSuperAdmin::ctrExitOrg(); }
      // A Super Admin who hasn't entered an org is limited to the platform pages.
      if (Tenant::isSuperAdmin() && Tenant::enteredOrg() === 0) {
        $saRoutes = ["organizations", "org-currencies", "sa-profile", "logout"];
        if (!isset($_GET["route"]) || !in_array($_GET["route"], $saRoutes, true)) {
          $_GET["route"] = "organizations";
        }
      }

      /*=============================================
      =            header          =
      =============================================*/

      include "modules/header.php";

      /*=============================================
      =            sidebar          =
      =============================================*/ 

      include "modules/sidebar.php";

      /*=============================================
      =     Entered-org banner (Super Admin)       =
      =============================================*/
      if (Tenant::isSuperAdmin() && Tenant::enteredOrg() > 0) {
        echo '<div style="background:#dd4b39; color:#fff; padding:8px 15px; margin-left:0;" class="content-wrapper-banner">
                <i class="fa fa-eye"></i> Operating as admin of <strong>' . htmlspecialchars($_SESSION["enteredOrgName"] ?? "") . '</strong>
                <a href="index.php?route=organizations&exitOrg=1" class="btn btn-xs btn-default pull-right"><i class="fa fa-sign-out"></i> Exit to Super Admin</a>
              </div>';
      }

      /*=============================================
      =            Content          =
      =============================================*/

      if(isset($_GET["route"])){

        if ($_GET["route"] == 'home' || 
            $_GET["route"] == 'users' ||
            $_GET["route"] == 'categories' ||
            $_GET["route"] == 'products' ||
            $_GET["route"] == 'customers' ||
            $_GET["route"] == 'sales' ||
            $_GET["route"] == 'create-sale' ||
            $_GET["route"] == 'edit-sale' ||
            $_GET["route"] == 'sale-detail' ||
            $_GET["route"] == 'invoices' ||
            $_GET["route"] == 'create-invoice' ||
            $_GET["route"] == 'edit-invoice' ||
            $_GET["route"] == 'quotations' ||
            $_GET["route"] == 'create-quotation' ||
            $_GET["route"] == 'edit-quotation' ||
            $_GET["route"] == 'quotation-detail' ||
            $_GET["route"] == 'invoice-detail' ||
            $_GET["route"] == 'customer-statement' ||
            $_GET["route"] == 'accounting' ||
            $_GET["route"] == 'expenses' ||
            $_GET["route"] == 'chart-of-accounts' ||
            $_GET["route"] == 'settings' ||
            $_GET["route"] == 'reports' ||
            $_GET["route"] == 'report-overview' ||
            $_GET["route"] == 'report-sales' ||
            $_GET["route"] == 'report-inventory' ||
            $_GET["route"] == 'report-payables' ||
            $_GET["route"] == 'report-receivables' ||
            $_GET["route"] == 'report-payments' ||
            $_GET["route"] == 'report-activity' ||
            $_GET["route"] == 'report-tax' ||
            $_GET["route"] == 'organizations' ||
            $_GET["route"] == 'org-currencies' ||
            $_GET["route"] == 'sa-profile' ||
            $_GET["route"] == 'company-profile' ||
            $_GET["route"] == 'currencies' ||
            $_GET["route"] == 'logout'){

          include "modules/".$_GET["route"].".php";

        }else{

           include "modules/404.php";
        
        }

      }else{

        include "modules/home.php";
      
      }
 
      /*=============================================
      =            Footer          =
      =============================================*/ 

      include "modules/footer.php";

      echo '</div>';

    }else{
       /*=============================================
      =            login          =
      =============================================*/ 

      include "modules/login.php";
    }
        
  ?>

  
<!-- ./wrapper -->

<script src="views/js/template.js"></script>
<script src="views/js/users.js"></script>
<script src="views/js/categories.js"></script>
<script src="views/js/products.js"></script>
<script src="views/js/customers.js"></script>
<script src="views/js/sales.js"></script>
<script src="views/js/invoices.js"></script>
<script src="views/js/quotations.js"></script>
<script src="views/js/payments.js"></script>
<script src="views/js/accounting.js"></script>
<script src="views/js/reports.js"></script>
<script>
$(function () {
  var csrf = $('meta[name="csrf-token"]').attr('content') || '';
  if (!csrf) { return; }
  $(document).on('submit', 'form[method="post"], form[method="POST"]', function () {
    var form = $(this);
    if (form.find('input[name="_csrf"]').length === 0) {
      $('<input>', { type: 'hidden', name: '_csrf', value: csrf }).appendTo(form);
    }
  });
});
</script>

</body>
</html>
