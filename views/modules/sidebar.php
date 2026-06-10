<aside class="main-sidebar">

	<section class="sidebar">

		<ul class="sidebar-menu">

			<?php

			/*=============================================
			SUPER ADMIN (not inside an org) — platform menu only
			=============================================*/
			if (Tenant::isSuperAdmin() && Tenant::enteredOrg() === 0) {

				echo '
					<li class="header">SUPER ADMIN</li>
					<li class="active">
						<a href="organizations"><i class="fa fa-building"></i> <span>'.t('Organizations').'</span></a>
					</li>
					<li>
						<a href="sa-profile"><i class="fa fa-user-circle"></i> <span>'.t('My Profile').'</span></a>
					</li>
					<li>
						<a href="logout"><i class="fa fa-sign-out"></i> <span>'.t('Log out').'</span></a>
					</li>
				';

			} else {

			if (Permission::has("dashboard")) {

				echo '
					<li class="active">
						<a href="home">
							<i class="fa fa-home"></i>
							<span>'.t('Home').'</span>
						</a>
					</li>
				';
			}

			if (Permission::has("products")) {

				echo '
					<li>
						<a href="categories">
							<i class="fa fa-th"></i>
							<span>'.t('Categories').'</span>
						</a>
					</li>
					<li>
						<a href="products">
							<i class="fa fa-product-hunt"></i>
							<span>'.t('Products').'</span>
						</a>
					</li>
				';
			}

			if (Permission::has("customers")) {
				echo '
					<li>
						<a href="customers">
							<i class="fa fa-users"></i>
							<span>'.t('Customers').'</span>
						</a>
					</li>
				';
			}

			/*=============================================
			SALES
			=============================================*/
			if (Permission::has("sales")) {

				echo '
					<li class="treeview">
						<a href="#">
							<i class="fa fa-usd"></i>
							<span>'.t('Sales').'</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">
							<li><a href="sales"><i class="fa fa-circle"></i> <span>'.t('Manage Sales').'</span></a></li>
							<li><a href="create-sale"><i class="fa fa-circle"></i> <span>'.t('Create Sale').'</span></a></li>
							<li><a href="quotations"><i class="fa fa-circle"></i> <span>'.t('Quotations').'</span></a></li>
							<li><a href="invoices"><i class="fa fa-circle"></i> <span>'.t('Invoices').'</span></a></li>';

				if (Permission::has("reports")) {
					echo '<li><a href="reports"><i class="fa fa-circle"></i> <span>'.t('Overview').'</span></a></li>';
				}

				echo '
						</ul>
					</li>';
			}

			/*=============================================
			REPORTS, ACCOUNTING, EXPENSES, CURRENCIES, USERS, SETTINGS
			(each gated by its own permission)
			=============================================*/
			if (Permission::has("reports")) {

				// Financial reports are hidden when the accounting module is off.
				$acct = ControllerSettings::ctrAccountingEnabled();
				$reportItems  = "";
				if ($acct) { $reportItems .= '<li><a href="report-overview"><i class="fa fa-circle"></i> <span>'.t('Business Overview').'</span></a></li>'; }
				$reportItems .= '<li><a href="report-sales"><i class="fa fa-circle"></i> <span>'.t('Sales').'</span></a></li>';
				$reportItems .= '<li><a href="report-inventory"><i class="fa fa-circle"></i> <span>'.t('Inventory').'</span></a></li>';
				if ($acct) {
					$reportItems .= '<li><a href="report-payables"><i class="fa fa-circle"></i> <span>'.t('Payables').'</span></a></li>';
					$reportItems .= '<li><a href="report-receivables"><i class="fa fa-circle"></i> <span>'.t('Receivables').'</span></a></li>';
					$reportItems .= '<li><a href="report-payments"><i class="fa fa-circle"></i> <span>'.t('Payments Received').'</span></a></li>';
				}
				$reportItems .= '<li><a href="report-activity"><i class="fa fa-circle"></i> <span>'.t('Activity').'</span></a></li>';
				if ($acct) { $reportItems .= '<li><a href="report-tax"><i class="fa fa-circle"></i> <span>'.t('Tax Summary').'</span></a></li>'; }

				echo '
					<li class="treeview">
						<a href="#">
							<i class="fa fa-bar-chart"></i>
							<span>'.t('Reports').'</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">' . $reportItems . '</ul>
					</li>';
			}

			// Expenses (standalone, independent of the accounting toggle)
			if (Permission::has("expenses")) {
				echo '
					<li>
						<a href="expenses">
							<i class="fa fa-credit-card"></i>
							<span>'.t('Expenses').'</span>
						</a>
					</li>';
			}

			if (Permission::has("accounting") && ControllerSettings::ctrAccountingEnabled()) {
				echo '
					<li class="treeview">
						<a href="#">
							<i class="fa fa-balance-scale"></i>
							<span>'.t('Accounting').'</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">
							<li><a href="accounting"><i class="fa fa-circle"></i> <span>'.t('Overview').'</span></a></li>
							<li><a href="chart-of-accounts"><i class="fa fa-circle"></i> <span>'.t('Chart of Accounts').'</span></a></li>
						</ul>
					</li>';
			}

			if (Permission::has("currencies") && Currency::isEnabled()) {
				echo '<li><a href="currencies"><i class="fa fa-money"></i> <span>'.t('Currencies').'</span></a></li>';
			}

			if (Permission::has("users")) {
				echo '
					<li>
						<a href="users">
							<i class="fa fa-user"></i>
							<span>'.t('User Management').'</span>
						</a>
					</li>';
			}

			if (Permission::has("settings")) {
				echo '
					<li>
						<a href="settings">
							<i class="fa fa-cog"></i>
							<span>'.t('Settings').'</span>
						</a>
					</li>';
			}

			} // end else (org user / entered super admin)

			?>

		</ul>

	</section>

</aside>
