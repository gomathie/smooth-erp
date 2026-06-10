<?php

if(!Permission::has("products")){

  echo '<script>

    window.location = "home";

  </script>';

  return;

}

?>
<!--  -->
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?php echo t('Category Management'); ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="home"><i class="fa fa-dashboard"></i> <?php echo t('Home'); ?></a></li>
        <li class="active"><?php echo t('Dashboard'); ?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCategories"> <i class="fa fa-plus"></i> <?php echo t('Add Categories'); ?></button>

        </div>
        <div class="card-body">
          <table class="table table-bordered table-hover table-striped dt-responsive tables" width="100%">
         
            <thead>
             
             <tr>
               
               <th style="width:10px">#</th>
               <th><?php echo t('Category'); ?></th>
               <th><?php echo t('Actions'); ?></th>

             </tr> 

            </thead>

            <tbody>
              <?php

                $item = null; 
                $value = null;

                $categories = ControllerCategories::ctrShowCategories($item, $value);

                // var_dump($categories);

                foreach ($categories as $key => $value) {

                  echo '<tr>
                          <td>'.($key+1).'</td>
                          <td class="text-uppercase">'.$value['Category'].'</td>
                          <td>

                            <div class="btn-group">
                                
                              <button class="btn btn-primary btnEditCategory" idCategory="'.$value["id"].'" data-bs-toggle="modal" data-bs-target="#editCategories"><i class="fa fa-pencil"></i></button>

                              <button class="btn btn-danger btnDeleteCategory" idCategory="'.$value["id"].'"><i class="fa fa-trash"></i></button>

                            </div>  

                          </td>

                        </tr>';
                }

              ?>
              
            </tbody>

          </table>

		<!--  -->

        </div>
      
      </div>
      <!-- /.box -->

    </section>
    <!-- /.content -->
  </div>


<!--=====================================
=            module add Categories            =
======================================-->
<!--  -->
<!-- Modal -->
<div id="addCategories" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <form role="form" method="POST">
        <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-header" style="background: #DD4B39; color: #fff">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title"><?php echo t('Add Categories'); ?></h4>
        </div>
        <div class="modal-body">
          <div class="card-body">

            <!--Input name -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-th"></i></span>
                <input class="form-control input-lg" type="text" name="newCategory" placeholder="<?php echo htmlspecialchars(t('Add Category')); ?>" required>
              </div>
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger float-start" data-bs-dismiss="modal"><?php echo t('Close'); ?></button>
          <button type="submit" class="btn btn-success"><?php echo t('Save Category'); ?></button>
        </div>
      </form>
    </div>

  </div>
</div>

<?php
  
  $createCategory = new ControllerCategories();
  $createCategory -> ctrCreateCategory();
?>

<!--  -->
<!--=====================================
=            module edit Categories            =
======================================-->

<!-- Modal -->
<div id="editCategories" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <form role="form" method="POST">
        <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-header" style="background: #DD4B39; color: #fff">
          <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
          <h4 class="modal-title"><?php echo t('Edit Categories'); ?></h4>
        </div>
        <div class="modal-body">
          <div class="card-body">

            <!--Input name -->
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-th"></i></span>
                <input class="form-control input-lg" type="text" id="editCategory" name="editCategory" required>
                <input type="hidden" name="idCategory" id="idCategory" required>
              </div>
            </div>
			<!--  -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger float-start" data-bs-dismiss="modal"><?php echo t('Close'); ?></button>
          <button type="submit" class="btn btn-success"><?php echo t('Save Changes'); ?></button>
        </div>

        <?php
  
          $editCategory = new ControllerCategories();
          $editCategory -> ctrEditCategory();
        ?>
      </form>
    </div>

  </div>
</div>
<!--  -->
<?php
  
  $deleteCategory = new ControllerCategories();
  $deleteCategory -> ctrDeleteCategory();
?>
