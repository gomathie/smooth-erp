<?php

if (!Permission::has("settings")) {
  echo '<script>window.location = "home";</script>';
  return;
}

ControllerSettings::ctrUpdateProfile();

$o = ControllerSettings::ctrCompanyProfile();
if (!is_array($o)) {
  echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger" style="margin:20px;">Organization not found.</div></section></div>';
  return;
}
$logo = $o["logo"] ?? "";
$theme = $o["themeColor"] ?? "#1e3a5f";
function v($a, $k) { return htmlspecialchars($a[$k] ?? "", ENT_QUOTES); }
?>
<div class="content-wrapper">

  <section class="content-header">
    <h1>Company Profile</h1>
    <ol class="breadcrumb">
      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="settings">Settings</a></li>
      <li class="active">Company Profile</li>
    </ol>
  </section>

  <section class="content">
    <form method="post" enctype="multipart/form-data" role="form">
      <input type="hidden" name="saveCompanyProfile" value="1">

      <div class="row">
        <!-- LEFT: logo + branding -->
        <div class="col-md-4">
          <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Organization Logo</h3></div>
            <div class="box-body text-center">
              <?php if ($logo && is_file($logo)) { ?>
                <img src="<?php echo htmlspecialchars($logo); ?>" style="max-width:180px; max-height:180px; border:1px solid #eee; padding:4px;">
              <?php } else { ?>
                <div style="width:180px;height:180px;border:2px dashed #ddd;line-height:180px;color:#bbb;margin:0 auto;">No logo</div>
              <?php } ?>
              <div class="form-group" style="margin-top:12px; text-align:left;">
                <input type="file" name="orgLogo" accept=".jpg,.jpeg,.png,.gif,.bmp">
                <p class="help-block" style="font-size:11px;">
                  Displayed on transaction PDFs and email notifications.<br>
                  Preferred: 240 × 240 px @ 72 DPI. Files: jpg, jpeg, png, gif, bmp. Max 1MB.
                </p>
              </div>
            </div>
          </div>

          <div class="box box-default">
            <div class="box-header with-border"><h3 class="box-title">Brand Color</h3></div>
            <div class="box-body">
              <div class="form-group">
                <label>Theme / accent color</label>
                <input type="color" class="form-control" name="themeColor" value="<?php echo htmlspecialchars($theme); ?>" style="height:42px; padding:4px;">
                <p class="help-block" style="font-size:11px;">Used as the accent color on your printed documents.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: company details -->
        <div class="col-md-8">
          <div class="box box-default">
            <div class="box-header with-border"><h3 class="box-title">Company Details</h3></div>
            <div class="box-body">
              <div class="row">
                <div class="col-sm-7"><div class="form-group"><label>Organization Name</label><input type="text" class="form-control" name="orgName" value="<?php echo v($o,'name'); ?>" required></div></div>
                <div class="col-sm-5"><div class="form-group"><label>Industry</label><input type="text" class="form-control" name="orgIndustry" value="<?php echo v($o,'industry'); ?>" placeholder="e.g. Technology"></div></div>
              </div>

              <div class="form-group"><label>Address</label><textarea class="form-control" name="orgAddress" rows="2" placeholder="Street / building"><?php echo htmlspecialchars($o['address'] ?? ''); ?></textarea></div>

              <div class="row">
                <div class="col-sm-4"><div class="form-group"><label>City / Town</label><input type="text" class="form-control" name="orgCity" value="<?php echo v($o,'city'); ?>"></div></div>
                <div class="col-sm-4"><div class="form-group"><label>Region / State</label><input type="text" class="form-control" name="orgRegion" value="<?php echo v($o,'region'); ?>"></div></div>
                <div class="col-sm-4"><div class="form-group"><label>Postal Code</label><input type="text" class="form-control" name="orgPostalCode" value="<?php echo v($o,'postalCode'); ?>"></div></div>
              </div>

              <div class="row">
                <div class="col-sm-6"><div class="form-group"><label>Country</label><input type="text" class="form-control" name="orgCountry" value="<?php echo v($o,'country'); ?>"></div></div>
                <div class="col-sm-6"><div class="form-group"><label>Website</label><input type="text" class="form-control" name="orgWebsite" value="<?php echo v($o,'website'); ?>" placeholder="example.com"></div></div>
              </div>

              <div class="row">
                <div class="col-sm-4"><div class="form-group"><label>Phone</label><input type="text" class="form-control" name="orgPhone" value="<?php echo v($o,'phone'); ?>"></div></div>
                <div class="col-sm-4"><div class="form-group"><label>Fax</label><input type="text" class="form-control" name="orgFax" value="<?php echo v($o,'fax'); ?>"></div></div>
                <div class="col-sm-4"><div class="form-group"><label>Email</label><input type="text" class="form-control" name="orgEmail" value="<?php echo v($o,'email'); ?>"></div></div>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> Save Profile</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </section>
</div>
