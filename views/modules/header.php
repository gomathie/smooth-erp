<?php
  $curLang  = class_exists('I18n') ? I18n::current() : 'en';
  $langs    = class_exists('I18n') ? I18n::available() : ['en' => 'English'];
  $curRoute = isset($_GET['route']) ? preg_replace('/[^a-z0-9\-]/', '', $_GET['route']) : 'home';
?>
<!-- AdminLTE 3 navbar -->
<nav class="main-header navbar navbar-expand navbar-dark">

  <!-- Left: sidebar toggle -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fa fa-bars"></i></a>
    </li>
  </ul>

  <!-- Right: language / theme / user menus -->
  <ul class="navbar-nav ml-auto">

    <!-- Language -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="<?php echo htmlspecialchars(t('Language')); ?>">
        <i class="fa fa-globe"></i> <span class="d-none d-sm-inline" style="text-transform:uppercase; font-size:12px;"><?php echo htmlspecialchars($curLang); ?></span>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <h6 class="dropdown-header"><?php echo htmlspecialchars(t('Language')); ?></h6>
        <?php foreach ($langs as $code => $label) {
          $active = $code === $curLang ? ' active' : '';
          echo '<a class="dropdown-item' . $active . '" href="index.php?route=' . htmlspecialchars($curRoute) . '&setlang=' . htmlspecialchars($code) . '">'
             . ($code === $curLang ? '<i class="fa fa-check"></i> ' : '<i class="fa fa-fw"></i> ')
             . htmlspecialchars($label) . '</a>';
        } ?>
      </div>
    </li>

    <!-- Theme picker -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Change Theme">
        <i class="fa fa-paint-brush"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-right" style="padding:15px; min-width:265px;">
        <p style="margin:0 0 10px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#999;">Theme Color</p>
        <!-- Swatches are built by JS from themes.config.js -->
        <div id="theme-swatches" style="display:flex; flex-wrap:wrap; gap:8px;"></div>
      </div>
    </li>

    <!-- User menu -->
    <li class="nav-item dropdown user-menu">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <?php if ($_SESSION["photo"] != "") { ?>
          <img src="<?php echo htmlspecialchars($_SESSION["photo"], ENT_QUOTES, 'UTF-8'); ?>" class="user-image img-circle">
        <?php } else { ?>
          <img class="user-image img-circle" src="views/img/users/default/anonymous.png">
        <?php } ?>
        <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION["name"], ENT_QUOTES, 'UTF-8'); ?></span>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <div class="dropdown-item text-right">
          <a class="btn btn-default btn-flat btn-sm" href="logout"><?php echo htmlspecialchars(t('Log out')); ?></a>
        </div>
      </div>
    </li>

  </ul>
</nav>
