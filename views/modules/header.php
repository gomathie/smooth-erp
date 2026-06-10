<?php
  $curLang  = class_exists('I18n') ? I18n::current() : 'en';
  $langs    = class_exists('I18n') ? I18n::available() : ['en' => 'English'];
  $curRoute = isset($_GET['route']) ? preg_replace('/[^a-z0-9\-]/', '', $_GET['route']) : 'home';
?>
<!-- AdminLTE 4 header -->
<nav class="app-header navbar navbar-expand">
  <div class="container-fluid">

    <!-- Left: sidebar toggle -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="fa fa-bars"></i></a>
      </li>
    </ul>

    <!-- Right: language / theme / user menus -->
    <ul class="navbar-nav ms-auto">

      <!-- Language -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-bs-toggle="dropdown" href="#" title="<?php echo htmlspecialchars(t('Language')); ?>">
          <i class="fa fa-globe"></i> <span class="d-none d-sm-inline" style="text-transform:uppercase; font-size:12px;"><?php echo htmlspecialchars($curLang); ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
          <h6 class="dropdown-header"><?php echo htmlspecialchars(t('Language')); ?></h6>
          <?php foreach ($langs as $code => $label) {
            $active = $code === $curLang ? ' active' : '';
            echo '<a class="dropdown-item' . $active . '" href="index.php?route=' . htmlspecialchars($curRoute) . '&setlang=' . htmlspecialchars($code) . '">'
               . ($code === $curLang ? '<i class="fa fa-check"></i> ' : '<i class="fa fa-fw"></i> ')
               . htmlspecialchars($label) . '</a>';
          } ?>
        </div>
      </li>

      <!-- User menu -->
      <li class="nav-item dropdown user-menu">
        <a class="nav-link" data-bs-toggle="dropdown" href="#">
          <?php if ($_SESSION["photo"] != "") { ?>
            <img src="<?php echo htmlspecialchars($_SESSION["photo"], ENT_QUOTES, 'UTF-8'); ?>" class="user-image rounded-circle" style="width:25px; height:25px;">
          <?php } else { ?>
            <img class="user-image rounded-circle" src="views/img/users/default/anonymous.png" style="width:25px; height:25px;">
          <?php } ?>
          <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION["name"], ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
          <div class="dropdown-item text-end">
            <a class="btn btn-default btn-sm" href="logout"><?php echo htmlspecialchars(t('Log out')); ?></a>
          </div>
        </div>
      </li>

    </ul>
  </div>
</nav>
