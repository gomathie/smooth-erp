<?php
// Super Admin only.
if (!Tenant::isSuperAdmin()) {
  echo '<script>window.location = "home";</script>';
  return;
}

ControllerSuperAdmin::ctrUpdateOwnProfile();

$me = ControllerSuperAdmin::ctrOwnProfile();
if (!is_array($me)) {
  echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger" style="margin:20px;">Profile not found.</div></section></div>';
  return;
}
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>My Profile</h1>
    <ol class="breadcrumb">
      <li><a href="organizations"><i class="fa fa-dashboard"></i> Platform</a></li>
      <li class="active">My Profile</li>
    </ol>
  </section>

  <section class="content">

    <div class="row">
      <div class="col-md-6">

        <div class="card card-primary card-outline">
          <div class="card-header">
            <h3 class="card-title"><i class="fa fa-user-circle"></i> Super Admin Account</h3>
          </div>

          <form method="post" role="form" enctype="multipart/form-data">
            <div class="card-body">

              <div class="form-group text-center">
                <?php
                  $saPhoto = !empty($me["photo"]) ? $me["photo"] : "views/img/users/default/anonymous.png";
                ?>
                <img class="preview img-thumbnail" src="<?php echo htmlspecialchars($saPhoto); ?>"
                     style="width:120px; height:120px; object-fit:cover; border-radius:50%;">
                <div class="mt-2">
                  <input class="newPics" type="file" name="saPhoto" accept="image/jpeg,image/png">
                  <p class="help-block">JPEG or PNG, max 2MB. Leave empty to keep your current photo.</p>
                </div>
              </div>

              <div class="form-group">
                <label>Username</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($me["user"]); ?>" readonly>
                <p class="help-block">Your login username cannot be changed.</p>
              </div>

              <div class="form-group">
                <label>Full Name</label>
                <input type="text" class="form-control" name="saName" value="<?php echo htmlspecialchars($me["name"]); ?>" required>
              </div>

              <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="saEmail" value="<?php echo htmlspecialchars($me["email"] ?? ""); ?>">
              </div>

              <div class="form-group">
                <label>New Password</label>
                <input type="password" class="form-control" name="saPassword" placeholder="Leave blank to keep current password" autocomplete="new-password">
                <p class="help-block">Only enter a value if you want to change your password (min 4 characters).</p>
              </div>

            </div>
            <div class="card-footer">
              <button type="submit" name="saProfileSave" value="1" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
            </div>
          </form>
        </div>

      </div>
    </div>

  </section>

</div>
