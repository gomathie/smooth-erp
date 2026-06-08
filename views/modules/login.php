<div id="back"></div>
<!--  -->
<div class="login-box">

  <div class="login-logo">

    <img class="img-responsive" src="views/img/template/logo-blanco-bloque.png" style="padding: 30px 100px 0 100px">

  </div>

  <div class="login-box-body">

    <?php $authRoute = $_GET["route"] ?? "login"; ?>

    <?php if ($authRoute === "forgot-password"): ?>

    <p class="login-box-msg">Enter your username or email to reset your password</p>

    <form method="post">

      <input type="hidden" name="_csrf" value="<?php echo h($_SESSION['csrf_token'] ?? ''); ?>">

      <div class="form-group has-feedback">

        <input type="text" class="form-control" placeholder="Username or email" name="resetIdentifier" required>

        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>

      </div>

      <div class="row">

        <div class="col-xs-7">
          <a href="login">Back to login</a>
        </div>

        <div class="col-xs-5">
          <button type="submit" class="btn btn-success btn-block btn-flat">Reset</button>
        </div>

      </div>

      <?php ControllerUsers::ctrForgotPassword(); ?>

    </form>

    <?php elseif ($authRoute === "reset-password"): ?>

    <p class="login-box-msg">Choose a new password</p>

    <form method="post">

      <input type="hidden" name="_csrf" value="<?php echo h($_SESSION['csrf_token'] ?? ''); ?>">
      <input type="hidden" name="resetToken" value="<?php echo h($_GET['token'] ?? ''); ?>">

      <div class="form-group has-feedback">

        <input type="password" class="form-control" placeholder="New password" name="newPassword" minlength="6" maxlength="72" required>

        <span class="glyphicon glyphicon-lock form-control-feedback"></span>

      </div>

      <div class="form-group has-feedback">

        <input type="password" class="form-control" placeholder="Confirm password" name="confirmPassword" minlength="6" maxlength="72" required>

        <span class="glyphicon glyphicon-lock form-control-feedback"></span>

      </div>

      <div class="row">

        <div class="col-xs-7">
          <a href="login">Back to login</a>
        </div>

        <div class="col-xs-5">
          <button type="submit" class="btn btn-success btn-block btn-flat">Save</button>
        </div>

      </div>

      <?php ControllerUsers::ctrResetPassword(); ?>

    </form>

    <?php else: ?>

    <p class="login-box-msg">Please log in to start your session</p>

    <form method="post">

      <input type="hidden" name="_csrf" value="<?php echo h($_SESSION['csrf_token'] ?? ''); ?>">

      <div class="form-group has-feedback">

        <input type="text" class="form-control" placeholder="Username" name="loginUser" required>

        <span class="glyphicon glyphicon-user form-control-feedback"></span>

      </div>

      <div class="form-group has-feedback">

        <input type="password" class="form-control" placeholder="Password" name="loginPass" required>

        <span class="glyphicon glyphicon-lock form-control-feedback"></span>

      </div>

      <div class="row">

        <div class="col-xs-8">

          <a href="forgot-password">Forgot password?</a>

        </div>

        <div class="col-xs-4">

          <button type="submit" class="btn btn-success btn-block btn-flat">Log In</button>

        </div>
       
      </div>

      <?php

        $login = new ControllerUsers();
        $login -> ctrUserLogin();

      ?>

    </form>

    <?php endif; ?>

  </div>

</div>
<!--  -->

