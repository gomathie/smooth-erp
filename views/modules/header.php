<header class="main-header">
	<!--==========================
	=            logo            =
	===========================-->
	<a href="home" class="logo">
		
		<!-- mini logo -->

		<span class="logo-mini">

			<img class="img-responsive" src="views/img/template/icono-blanco.png" style="padding: 10px" >

		</span>

		<!-- logo -->

		<span class="logo-lg">

			<img class="img-responsive" src="views/img/template/logo-blanco-lineal.png" style="padding: 10px 0" >

		</span>

	</a>
	<!--  -->
	<!--=====================================
	=            navigation         =
	======================================-->
	
	<nav class="navbar navbar-static-top" role="navigation">
		
		<!-- Navigation button -->

		<a class="sidebar-toggle" data-toggle="push-menu" role="button" href="#">

			<span class="sr-only">Toggle Navigation</span>

		</a>

		<!-- User Profile -->

		<div class="navbar-custom-menu">

			<ul class="nav navbar-nav">

				<!-- Theme Picker -->
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#" title="Change Theme" style="font-size:16px; padding: 15px 14px;">
						<i class="fa fa-paint-brush"></i>
					</a>
					<ul class="dropdown-menu" style="padding:15px 15px 10px; min-width:265px; right:0; left:auto;">
						<li>
							<p style="margin:0 0 10px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#999;">Theme Color</p>
							<!-- Swatches are built by JS from themes.config.js -->
							<div id="theme-swatches" style="display:flex; flex-wrap:wrap; gap:8px;"></div>
						</li>
					</ul>
				</li>
				<!-- /Theme Picker -->

				<li class="dropdown user user-menu">

					<a class="dropdown-toggle" data-toggle="dropdown" href="#">

						<?php 

							if ($_SESSION["photo"] != "") {

								echo '<img src="'.htmlspecialchars($_SESSION["photo"], ENT_QUOTES, 'UTF-8').'" class="user-image">';

							}else{

								echo '<img class="user-image" src="views/img/users/default/anonymous.png">';
							}

						?>

						<span class="hidden-xs"><?php echo htmlspecialchars($_SESSION["name"], ENT_QUOTES, 'UTF-8'); ?></span>

					</a>

					<!-- dropdown toggle -->

					<ul class="dropdown-menu">

						<li class="user-body">

							<div class="pull-right">

								<a class="btn btn-default btn-flat" href="logout">Logout</a>

							</div><!--  -->

						</li>

					</ul>

				</li>

			</ul>
			
		</div>
		
	</nav>
	
</header>
