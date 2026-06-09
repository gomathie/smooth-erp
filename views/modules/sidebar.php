<aside class="main-sidebar">

	<section class="sidebar">

		<ul class="sidebar-menu">

			<?php

			if ($_SESSION["profile"] == "Administrator") {

				echo '
					<li class="active">
						<a href="home">
							<i class="fa fa-home"></i>
							<span>Home</span>
						</a>
					</li>
				';
			}

			if ($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Special") {

				echo '
					<li>
						<a href="categories">
							<i class="fa fa-th"></i>
							<span>Categories</span>
						</a>
					</li>
					<li>
						<a href="products">
							<i class="fa fa-product-hunt"></i>
							<span>Products</span>
						</a>
					</li>
				';
			}

			if ($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Seller") {
				echo '
					<li>
						<a href="customers">
							<i class="fa fa-users"></i>
							<span>Customers</span>
						</a>
					</li>
				';
			}

			/*=============================================
			SALES
			=============================================*/
			if ($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Seller") {

				echo '
					<li class="treeview">
						<a href="#">
							<i class="fa fa-usd"></i>
							<span>Sales</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">
							<li><a href="sales"><i class="fa fa-circle"></i> <span>Manage Sales</span></a></li>
							<li><a href="create-sale"><i class="fa fa-circle"></i> <span>Create Sale</span></a></li>
							<li><a href="quotations"><i class="fa fa-circle"></i> <span>Quotations</span></a></li>
							<li><a href="invoices"><i class="fa fa-circle"></i> <span>Invoices</span></a></li>';

				if ($_SESSION["profile"] == "Administrator") {
					echo '<li><a href="reports"><i class="fa fa-circle"></i> <span>Overview</span></a></li>';
				}

				echo '
						</ul>
					</li>';
			}

			/*=============================================
			ADMIN-ONLY: REPORTS, ACCOUNTING, USERS, SETTINGS
			=============================================*/
			if ($_SESSION["profile"] == "Administrator") {

				// Financial reports are hidden when the accounting module is off.
				$acct = ControllerSettings::ctrAccountingEnabled();
				$reportItems  = "";
				if ($acct) { $reportItems .= '<li><a href="report-overview"><i class="fa fa-circle"></i> <span>Business Overview</span></a></li>'; }
				$reportItems .= '<li><a href="report-sales"><i class="fa fa-circle"></i> <span>Sales</span></a></li>';
				$reportItems .= '<li><a href="report-inventory"><i class="fa fa-circle"></i> <span>Inventory</span></a></li>';
				if ($acct) {
					$reportItems .= '<li><a href="report-payables"><i class="fa fa-circle"></i> <span>Payables</span></a></li>';
					$reportItems .= '<li><a href="report-receivables"><i class="fa fa-circle"></i> <span>Receivables</span></a></li>';
					$reportItems .= '<li><a href="report-payments"><i class="fa fa-circle"></i> <span>Payments Received</span></a></li>';
				}
				$reportItems .= '<li><a href="report-activity"><i class="fa fa-circle"></i> <span>Activity</span></a></li>';
				if ($acct) { $reportItems .= '<li><a href="report-tax"><i class="fa fa-circle"></i> <span>Tax Summary</span></a></li>'; }

				echo '
					<li class="treeview">
						<a href="#">
							<i class="fa fa-bar-chart"></i>
							<span>Reports</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">' . $reportItems . '</ul>
					</li>';

				// Expenses always available (standalone), independent of the accounting toggle
				echo '
					<li>
						<a href="expenses">
							<i class="fa fa-credit-card"></i>
							<span>Expenses</span>
						</a>
					</li>';

				if (ControllerSettings::ctrAccountingEnabled()) {
					echo '
					<li class="treeview">
						<a href="#">
							<i class="fa fa-balance-scale"></i>
							<span>Accounting</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">
							<li><a href="accounting"><i class="fa fa-circle"></i> <span>Overview</span></a></li>
							<li><a href="chart-of-accounts"><i class="fa fa-circle"></i> <span>Chart of Accounts</span></a></li>
						</ul>
					</li>';
				}

				echo '
					<li>
						<a href="users">
							<i class="fa fa-user"></i>
							<span>User Management</span>
						</a>
					</li>
					<li>
						<a href="settings">
							<i class="fa fa-cog"></i>
							<span>Settings</span>
						</a>
					</li>';
			}

			?>

		</ul>

	</section>

</aside>
