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

        <span class="fa fa-envelope form-control-feedback"></span>

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

        <span class="fa fa-lock form-control-feedback"></span>

      </div>

      <div class="form-group has-feedback">

        <input type="password" class="form-control" placeholder="Confirm password" name="confirmPassword" minlength="6" maxlength="72" required>

        <span class="fa fa-lock form-control-feedback"></span>

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

    <p class="login-box-msg"><?php echo htmlspecialchars(t('Please log in to start your session')); ?></p>

    <form method="post">

      <input type="hidden" name="_csrf" value="<?php echo h($_SESSION['csrf_token'] ?? ''); ?>">

      <div class="form-group has-feedback">

        <input type="text" class="form-control" placeholder="<?php echo htmlspecialchars(t('Username')); ?>" name="loginUser" required>

        <span class="fa fa-user form-control-feedback"></span>

      </div>

      <div class="form-group has-feedback">

        <input type="password" class="form-control" placeholder="<?php echo htmlspecialchars(t('Password')); ?>" name="loginPass" required>

        <span class="fa fa-lock form-control-feedback"></span>

      </div>

      <div class="row">

        <div class="col-xs-8">

          <a href="forgot-password"><?php echo htmlspecialchars(t('Forgot password?')); ?></a>

        </div>

        <div class="col-xs-4">

          <button type="submit" class="btn btn-success btn-block btn-flat"><?php echo htmlspecialchars(t('Log In')); ?></button>

        </div>
       
      </div>

      <?php

        $login = new ControllerUsers();
        $login -> ctrUserLogin();

      ?>

    </form>

    <?php endif; ?>

    <?php if (class_exists('I18n')) { ?>
      <div class="text-center" style="margin-top:14px; font-size:12px;">
        <?php
          $curLang = I18n::current();
          $parts = [];
          foreach (I18n::available() as $code => $label) {
            $style = $code === $curLang ? ' style="font-weight:700; text-decoration:none;"' : '';
            $parts[] = '<a href="index.php?route=' . htmlspecialchars($authRoute) . '&setlang=' . htmlspecialchars($code) . '"' . $style . '>' . htmlspecialchars($label) . '</a>';
          }
          echo '<i class="fa fa-globe"></i> ' . implode(' &middot; ', $parts);
        ?>
      </div>
    <?php } ?>

  </div>

</div>
<!--  -->

