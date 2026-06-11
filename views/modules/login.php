<?php
/*=============================================================================
  AUTHENTICATION PAGE (login / forgot-password / reset-password)
  -----------------------------------------------------------------------------
  Self-contained document rendered by template.php for unauthenticated visitors.
  It loads ONLY what it needs — Bootstrap 5, Font Awesome and login.css — not the
  full AdminLTE app shell, so the styling is clean and needs no !important hacks.
=============================================================================*/

$authRoute = $_GET['route'] ?? 'login';
$csrfToken = $_SESSION['csrf_token'] ?? '';
$lang      = class_exists('I18n') ? I18n::current() : 'en';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars(t('Log In')); ?> &middot; Smooth ERP</title>

  <link rel="icon" href="views/img/template/icono-negro.png">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

  <link rel="stylesheet" href="views/vendor/bootstrap5/css/bootstrap.min.css">
  <link rel="stylesheet" href="views/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="views/dist/css/login.css">
</head>
<body class="auth-page">

  <main class="auth-card">

    <header class="auth-brand">
      <img src="views/img/template/logo-negro-bloque.png" alt="Smooth ERP">
    </header>

    <?php if ($authRoute === 'forgot-password'): ?>

      <h1 class="auth-title"><?php echo htmlspecialchars(t('Reset your password')); ?></h1>
      <p class="auth-subtitle"><?php echo htmlspecialchars(t('Enter your username or email to reset your password')); ?></p>

      <form method="post" class="auth-form">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken); ?>">

        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-envelope"></i></span>
          <input type="text" class="form-control" name="resetIdentifier" placeholder="<?php echo htmlspecialchars(t('Username or email')); ?>" required autofocus>
        </div>

        <button type="submit" class="btn btn-auth"><?php echo htmlspecialchars(t('Send reset link')); ?></button>

        <?php ControllerUsers::ctrForgotPassword(); ?>

        <p class="auth-alt"><a href="login"><?php echo htmlspecialchars(t('Back to login')); ?></a></p>
      </form>

    <?php elseif ($authRoute === 'reset-password'): ?>

      <h1 class="auth-title"><?php echo htmlspecialchars(t('Choose a new password')); ?></h1>

      <form method="post" class="auth-form">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken); ?>">
        <input type="hidden" name="resetToken" value="<?php echo h($_GET['token'] ?? ''); ?>">

        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-lock"></i></span>
          <input type="password" class="form-control" name="newPassword" placeholder="<?php echo htmlspecialchars(t('New password')); ?>" minlength="6" maxlength="72" required autofocus>
        </div>

        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-lock"></i></span>
          <input type="password" class="form-control" name="confirmPassword" placeholder="<?php echo htmlspecialchars(t('Confirm password')); ?>" minlength="6" maxlength="72" required>
        </div>

        <button type="submit" class="btn btn-auth"><?php echo htmlspecialchars(t('Save')); ?></button>

        <?php ControllerUsers::ctrResetPassword(); ?>

        <p class="auth-alt"><a href="login"><?php echo htmlspecialchars(t('Back to login')); ?></a></p>
      </form>

    <?php else: ?>

      <p class="auth-subtitle"><?php echo htmlspecialchars(t('Please log in to start your session')); ?></p>

      <form method="post" class="auth-form">
        <input type="hidden" name="_csrf" value="<?php echo h($csrfToken); ?>">

        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-user"></i></span>
          <input type="text" class="form-control" name="loginUser" placeholder="<?php echo htmlspecialchars(t('Username')); ?>" required autofocus>
        </div>

        <div class="input-group">
          <span class="input-group-text"><i class="fa fa-lock"></i></span>
          <input type="password" class="form-control" name="loginPass" placeholder="<?php echo htmlspecialchars(t('Password')); ?>" required>
        </div>

        <button type="submit" class="btn btn-auth"><?php echo htmlspecialchars(t('Log In')); ?></button>

        <?php
          $login = new ControllerUsers();
          $login->ctrUserLogin();
        ?>

        <p class="auth-alt"><a href="forgot-password"><?php echo htmlspecialchars(t('Forgot password?')); ?></a></p>
      </form>

    <?php endif; ?>

    <?php if (class_exists('I18n')): ?>
      <div class="auth-lang">
        <i class="fa fa-globe"></i>
        <?php
          $current = I18n::current();
          $links = [];
          foreach (I18n::available() as $code => $label) {
            $active = $code === $current ? ' class="active"' : '';
            $links[] = '<a href="index.php?route=' . htmlspecialchars($authRoute) . '&setlang=' . htmlspecialchars($code) . '"' . $active . '>' . htmlspecialchars($label) . '</a>';
          }
          echo implode(' <span class="sep">&middot;</span> ', $links);
        ?>
      </div>
    <?php endif; ?>

  </main>

  <script src="views/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
</body>
</html>
