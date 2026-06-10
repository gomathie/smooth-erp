<?php
if (!Permission::has("users")) {
  echo '<script>window.location = "home";</script>';
  return;
}

/**
 * Render the permission checkboxes for a user form.
 * $prefix is "new" or "Edit" so field names become newPerms[] / EditPerms[].
 */
function permissionCheckboxes(string $prefix): void {
  echo '<div class="row perm-grid" data-prefix="' . $prefix . '">';
  foreach (Permission::KEYS as $key) {
    $label = Permission::LABELS[$key] ?? $key;
    echo '<div class="col-6" style="margin-bottom:6px;">
            <label style="font-weight:normal; cursor:pointer;">
              <input type="checkbox" name="' . $prefix . 'Perms[]" value="' . $key . '" class="perm-check"> ' . htmlspecialchars($label) . '
            </label>
          </div>';
  }
  echo '</div>';
}
?>
<script>
// RBAC role -> default permissions, exposed for the role/permission UI.
window.ROLE_DEFAULTS = <?php echo json_encode(Permission::ROLE_DEFAULTS); ?>;
window.PERM_KEYS     = <?php echo json_encode(Permission::KEYS); ?>;
</script>

<div class="content-wrapper">
	<!--  -->
  <section class="content-header">

    <h1>

      User Management

    </h1>

    <ol class="breadcrumb">

      <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>

      <li class="active">Dashboard</li>

    </ol>

  </section>

  <section class="content">

    <div class="card">

      <div class="card-header">

        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUser">

         <i class="fa fa-plus"></i> Add User

        </button>

      </div>

      <div class="card-body">

        <table class="table table-bordered table-hover table-striped dt-responsive tables" width="100%">
       
          <thead>
           
           <tr>
             
             <th style="width:10px">#</th>
             <th>Name</th>
             <th>Username</th>
             <th>Email</th>
             <th>Phone</th>
             <th>Photo</th>
             <th>Role</th>
             <th>Status</th>
             <th>Last Login</th>
             <th>Actions</th>

           </tr> 

          </thead>
			<!--  -->
          <tbody>

            <?php

              $item = null; 
              $value = null;

              $users = ControllerUsers::ctrShowUsers($item, $value);

              // var_dump($users);

              foreach ($users as $key => $value) {

                echo '

                  <tr>
                    <td>'.($key+1).'</td>
                    <td>'.$value["name"].'</td>
                    <td>'.$value["user"].'</td>
                    <td>'.(!empty($value["email"]) ? $value["email"] : '').'</td>
                    <td>'.(!empty($value["phone"]) ? $value["phone"] : '').'</td>';

                    if ($value["photo"] != ""){

                      echo '<td><img src="'.$value["photo"].'" class="img-thumbnail" width="40px"></td>';

                    }else{

                      echo '<td><img src="views/img/users/default/anonymous.png" class="img-thumbnail" width="40px"></td>';
                    
                    }

                    echo '<td>'.ucfirst($value["role"] ?? Permission::roleFromProfile($value["profile"] ?? '')).'</td>';

                    if($value["status"] != 0){

                      echo '<td><button class="btn btn-success btnActivate btn-xs" userId="'.$value["id"].'" userStatus="0">Activated</button></td>';

                    }else{

                      echo '<td><button class="btn btn-danger btnActivate btn-xs" userId="'.$value["id"].'" userStatus="1">Deactivated</button></td>';
                    }
                    
                    echo '<td>'.$value["lastLogin"].'</td>

                    <td>

                      <div class="btn-group">
                          
                        <button class="btn btn-primary btnEditUser" idUser="'.$value["id"].'" data-bs-toggle="modal" data-bs-target="#editUser"><i class="fa fa-pencil"></i></button>

                        <button class="btn btn-danger btnDeleteUser" userId="'.$value["id"].'" username="'.$value["user"].'" userPhoto="'.$value["photo"].'"><i class="fa fa-trash"></i></button>

                      </div>  

                    </td>

                  </tr>';
              }

            ?>

          </tbody>

        </table>

      </div>
    
    </div>

  </section>

</div>

<!--=====================================
=            module add user            =
======================================-->

<!-- Modal -->
<div id="addUser" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">

      <form role="form" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <!--=====================================
        HEADER
        ======================================-->

        <div class="modal-header" style="background: #DD4B39; color: #fff">

          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

          <h4 class="modal-title">Add User</h4>

        </div>

        <!--=====================================
        BODY
        ======================================-->

        <div class="modal-body">

          <div class="card-body">

            <!--Input name -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-user"></i></span>

                <input class="form-control input-lg" type="text" name="newName" placeholder="Enter fullname" required>

              </div>

            </div>

            <!-- input username -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-key"></i></span>

                <input class="form-control input-lg" type="text" id="newUser" name="newUser" placeholder="Enter username" required>

              </div>

            </div>

            <!-- input email -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-envelope"></i></span>

                <input class="form-control input-lg" type="email" name="newEmail" placeholder="Email (optional)">

              </div>

            </div>

            <!-- input phone -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-phone"></i></span>

                <input class="form-control input-lg" type="text" name="newPhone" placeholder="Phone (optional)">

              </div>

            </div>

            <!-- input password -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-lock"></i></span>

                <input class="form-control input-lg" type="password" name="newPasswd" placeholder="Enter password" required>

              </div>

            </div>

            <!-- Role -->
            <div class="form-group">
              <label>Role</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                <select class="form-control input-lg roleSelect" name="newRole" data-prefix="new">
                  <option value="administrator">Administrator</option>
                  <option value="manager">Manager</option>
                  <option value="accountant">Accountant</option>
                  <option value="staff" selected>Staff</option>
                  <option value="viewer">Viewer</option>
                </select>
              </div>
            </div>

            <!-- Permissions (what this user can see) -->
            <div class="form-group permWrap" data-prefix="new">
              <label>Access permissions <small class="text-muted">(administrators get everything)</small></label>
              <?php permissionCheckboxes("new"); ?>
            </div>

            <!-- Uploading image -->
            <div class="form-group">

              <div class="panel">Upload image</div>

              <input class="newPics" type="file" name="newPhoto">

              <p class="help-block">Maximum size 2Mb</p>

              <img class="thumbnail preview" src="views/img/users/default/prfplaceholder.png" width="100px">

            </div>

          </div>

        </div>

        <!--=====================================
        FOOTER
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-danger float-start" data-bs-dismiss="modal">Close</button>

          <button type="submit" class="btn btn-success">Save</button>
          
        </div>

          <?php
            $createUser = new ControllerUsers();
            $createUser -> ctrCreateUser();
          ?>

      </form>

    </div>

  </div>

</div>
<!--====  End of module add user  ====-->

<!--=====================================
=            module edit user            =
======================================-->

<!-- Modal -->
<div id="editUser" class="modal fade" role="dialog">

  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">

      <form role="form" method="POST" enctype="multipart/form-data">

        <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

        <!--=====================================
        HEADER
        ======================================-->

        <div class="modal-header" style="background: #DD4B39; color: #fff">

          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>

          <h4 class="modal-title">Edit User</h4>

        </div>

        <!--=====================================
        BODY
        ======================================-->

        <div class="modal-body">

          <div class="card-body">

            <!--Input name -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-user"></i></span>

                <input class="form-control input-lg" type="text" id="EditName" name="EditName" placeholder="Edit name" required>

              </div>

            </div>

            <!-- input username -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-key"></i></span>

                <input class="form-control input-lg" type="text" id="EditUser" name="EditUser" placeholder="Edit username" readonly>

              </div>

            </div>

            <!-- input email -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-envelope"></i></span>

                <input class="form-control input-lg" type="email" id="EditEmail" name="EditEmail" placeholder="Email (optional)">

              </div>

            </div>

            <!-- input phone -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-phone"></i></span>

                <input class="form-control input-lg" type="text" id="EditPhone" name="EditPhone" placeholder="Phone (optional)">

              </div>

            </div>

            <!-- input password -->
            <div class="form-group">

              <div class="input-group">

                <span class="input-group-text"><i class="fa fa-lock"></i></span>

                <input class="form-control input-lg" type="password" name="EditPasswd" placeholder="Enter new password">

                <input type="hidden" name="currentPasswd" id="currentPasswd">

              </div>

            </div>

            <!-- Role -->
            <div class="form-group">
              <label>Role</label>
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                <select class="form-control input-lg roleSelect" id="EditRole" name="EditRole" data-prefix="Edit">
                  <option value="administrator">Administrator</option>
                  <option value="manager">Manager</option>
                  <option value="accountant">Accountant</option>
                  <option value="staff">Staff</option>
                  <option value="viewer">Viewer</option>
                </select>
              </div>
            </div>

            <!-- Permissions (what this user can see) -->
            <div class="form-group permWrap" data-prefix="Edit">
              <label>Access permissions <small class="text-muted">(administrators get everything)</small></label>
              <?php permissionCheckboxes("Edit"); ?>
            </div>

            <!-- Uploading image -->
            <div class="form-group">

              <div class="panel">Upload image</div>

              <input class="newPics" type="file" name="editPhoto">

              <p class="help-block">Maximum size 2Mb</p>

              <img class="thumbnail preview" src="views/img/users/default/anonymous.png" alt="" width="100px">

              <input type="hidden" name="currentPicture" id="currentPicture">

            </div>

          </div>

        </div>

        <!--=====================================
        FOOTER
        ======================================-->

        <div class="modal-footer">

          <button type="button" class="btn btn-danger float-start" data-bs-dismiss="modal">Close</button>

          <button type="submit" class="btn btn-success">Edit User</button>
          
        </div>

          <?php
            $editUser = new ControllerUsers();
            $editUser -> ctrEditUser();
          ?>

      </form>

    </div>

  </div>
	<!--  -->
</div>

<?php

  $deleteUser = new ControllerUsers();
  $deleteUser -> ctrDeleteUser();

?> 
