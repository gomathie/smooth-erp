<!-- AdminLTE 4 sidebar -->
<aside class="app-sidebar shadow">

  <!-- Brand / logo -->
  <div class="sidebar-brand">
    <a href="<?php echo Tenant::isSuperAdmin() && Tenant::enteredOrg() === 0 ? 'organizations' : 'home'; ?>" class="brand-link">
      <img src="views/img/template/icono-blanco.png" class="brand-image opacity-75 shadow" style="max-height:33px;">
      <span class="brand-text fw-light">Smooth ERP</span>
    </a>
  </div>

  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

        <?php
        /*=============================================
        SUPER ADMIN (not inside an org) — platform menu only
        =============================================*/
        if (Tenant::isSuperAdmin() && Tenant::enteredOrg() === 0) {

          echo '
            <li class="nav-header">SUPER ADMIN</li>
            <li class="nav-item">
              <a href="organizations" class="nav-link active"><i class="nav-icon fa fa-building"></i><p>' . t('Organizations') . '</p></a>
            </li>
            <li class="nav-item">
              <a href="sa-profile" class="nav-link"><i class="nav-icon fa fa-user-circle"></i><p>' . t('My Profile') . '</p></a>
            </li>
            <li class="nav-item">
              <a href="logout" class="nav-link"><i class="nav-icon fa fa-sign-out"></i><p>' . t('Log out') . '</p></a>
            </li>
          ';

        } else {

          if (Permission::has("dashboard")) {
            echo '
              <li class="nav-item">
                <a href="home" class="nav-link active"><i class="nav-icon fa fa-home"></i><p>' . t('Home') . '</p></a>
              </li>';
          }

          if (Permission::has("products")) {
            echo '
              <li class="nav-item">
                <a href="categories" class="nav-link"><i class="nav-icon fa fa-th"></i><p>' . t('Categories') . '</p></a>
              </li>
              <li class="nav-item">
                <a href="products" class="nav-link"><i class="nav-icon fa fa-product-hunt"></i><p>' . t('Products') . '</p></a>
              </li>';
          }

          if (Permission::has("customers")) {
            echo '
              <li class="nav-item">
                <a href="customers" class="nav-link"><i class="nav-icon fa fa-users"></i><p>' . t('Customers') . '</p></a>
              </li>';
          }

         /* SALES (treeview) */
if (Permission::has("sales")) {

  echo '
    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="nav-icon fa fa-usd"></i>
        <p>' . t('Sales') . '<i class="nav-arrow fa fa-angle-right"></i></p>
      </a>
      <ul class="nav nav-treeview">';

  // Overview first
  if (Permission::has("reports")) {
    echo '
      <li class="nav-item">
        <a href="reports" class="nav-link">
          <i class="nav-icon fa fa-circle-o"></i>
          <p>' . t('Overview') . '</p>
        </a>
      </li>';
  }

  echo '
      <li class="nav-item"><a href="sales" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Manage Sales') . '</p></a></li>
      <li class="nav-item"><a href="create-sale" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Create Sale') . '</p></a></li>
      <li class="nav-item"><a href="quotations" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Quotations') . '</p></a></li>
      <li class="nav-item"><a href="invoices" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Invoices') . '</p></a></li>
      </ul>
    </li>';
}

          /* REPORTS (treeview) */
          if (Permission::has("reports")) {
            $acct = ControllerSettings::ctrAccountingEnabled();
            $reportItems  = "";
            if ($acct) { $reportItems .= '<li class="nav-item"><a href="report-overview" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Business Overview') . '</p></a></li>'; }
            $reportItems .= '<li class="nav-item"><a href="report-sales" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Sales') . '</p></a></li>';
            $reportItems .= '<li class="nav-item"><a href="report-inventory" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Inventory') . '</p></a></li>';
            if ($acct) {
              $reportItems .= '<li class="nav-item"><a href="report-payables" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Payables') . '</p></a></li>';
              $reportItems .= '<li class="nav-item"><a href="report-receivables" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Receivables') . '</p></a></li>';
              $reportItems .= '<li class="nav-item"><a href="report-payments" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Payments Received') . '</p></a></li>';
            }
            $reportItems .= '<li class="nav-item"><a href="report-activity" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Activity') . '</p></a></li>';
            if ($acct) { $reportItems .= '<li class="nav-item"><a href="report-tax" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Tax Summary') . '</p></a></li>'; }

            echo '
              <li class="nav-item">
                <a href="#" class="nav-link"><i class="nav-icon fa fa-bar-chart"></i><p>' . t('Reports') . '<i class="nav-arrow fa fa-angle-right"></i></p></a>
                <ul class="nav nav-treeview">' . $reportItems . '</ul>
              </li>';
          }

          /* EXPENSES */
          if (Permission::has("expenses")) {
            echo '
              <li class="nav-item">
                <a href="expenses" class="nav-link"><i class="nav-icon fa fa-credit-card"></i><p>' . t('Expenses') . '</p></a>
              </li>';
          }

          /* ACCOUNTING (treeview) */
          if (Permission::has("accounting") && ControllerSettings::ctrAccountingEnabled()) {
            echo '
              <li class="nav-item">
                <a href="#" class="nav-link"><i class="nav-icon fa fa-balance-scale"></i><p>' . t('Accounting') . '<i class="nav-arrow fa fa-angle-right"></i></p></a>
                <ul class="nav nav-treeview">
                  <li class="nav-item"><a href="accounting" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Overview') . '</p></a></li>
                  <li class="nav-item"><a href="chart-of-accounts" class="nav-link"><i class="nav-icon fa fa-circle-o"></i><p>' . t('Chart of Accounts') . '</p></a></li>
                </ul>
              </li>';
          }

          /* CURRENCIES */
          if (Permission::has("currencies") && Currency::isEnabled()) {
            echo '
              <li class="nav-item">
                <a href="currencies" class="nav-link"><i class="nav-icon fa fa-money"></i><p>' . t('Currencies') . '</p></a>
              </li>';
          }

          /* USERS */
          if (Permission::has("users")) {
            echo '
              <li class="nav-item">
                <a href="users" class="nav-link"><i class="nav-icon fa fa-user"></i><p>' . t('User Management') . '</p></a>
              </li>';
          }

          /* SETTINGS */
          if (Permission::has("settings")) {
            echo '
              <li class="nav-item">
                <a href="settings" class="nav-link"><i class="nav-icon fa fa-cog"></i><p>' . t('Settings') . '</p></a>
              </li>';
          }

        } // end else
        ?>

      </ul>
    </nav>
  </div>
</aside>
