/*=============================================
UPLOADING USER PICTURE
=============================================*/

$(".newPics").change(function(){

	var newImage = this.files[0];

	/*===============================================
	=            validating image format            =
	===============================================*/
	
	if (newImage["type"] != "image/jpeg" && newImage["type"] != "image/png"){

		$(".newPics").val("");

		swal({
			type: "error",
			title: "Error uploading image",
			text: "¡Image has to be JPEG or PNG!",
			showConfirmButton: true,
			confirmButtonText: "Close"
		});

	}else if(newImage["size"] > 2000000){

		$(".newPics").val("");

		swal({
			type: "error",
			title: "Error uploading image",
			text: "¡Image too big. It has to be less than 2Mb!",
			showConfirmButton: true,
			confirmButtonText: "Close"
		});

	}else{

		var imgData = new FileReader;
		imgData.readAsDataURL(newImage);

		$(imgData).on("load", function(event){
			
			var routeImg = event.target.result;

			$(".preview").attr("src", routeImg);

		});

	}
		
	/*=====  End of validating image format  ======*/
	
})


/*=============================================
EDITING USER PICTURE
=============================================*/
$(document).on("click", ".btnEditUser", function(){

 	var idUser = $(this).attr("idUser");

 	var data = new FormData();
 	data.append("idUser", idUser);

 	$.ajax({

 		url: "ajax/users.ajax.php",
 		method: "POST",
 		data: data,
 		cache: false,
 		contentType: false,
 		processData: false,
 		dataType: "json",
 		success: function(answer){
 			
 			// console.log("answer", answer);

 			$("#EditName").val(answer["name"]);

 			$("#EditUser").val(answer["user"]);

				$("#EditEmail").val(answer["email"] || '');

				$("#EditPhone").val(answer["phone"] || '');

 			$("#currentPicture").val(answer["photo"]);

 			if(answer["photo"] != ''){

 				$('.preview').attr('src', answer["photo"]);

 			}

 			// RBAC: set role + permission checkboxes for the edit modal.
 			var role = answer["role"] || 'staff';
 			var perms = [];
 			try { perms = answer["permissions"] ? JSON.parse(answer["permissions"]) : []; } catch(e){ perms = []; }
 			permSetExplicit('Edit', role, perms);

 		}

 	});

 });


/*=============================================
RBAC: ROLE -> PERMISSION CHECKBOXES
=============================================*/

// Apply a role's default permissions to its modal's checkboxes.
function permApplyRole(prefix){
	var role = $('.roleSelect[data-prefix="'+prefix+'"]').val();
	var $boxes = $('.perm-grid[data-prefix="'+prefix+'"] .perm-check');
	if(role === 'administrator'){
		$boxes.prop('checked', true).prop('disabled', true);
	} else {
		var defs = (window.ROLE_DEFAULTS && window.ROLE_DEFAULTS[role]) || ['dashboard'];
		$boxes.prop('disabled', false);
		$boxes.each(function(){ $(this).prop('checked', defs.indexOf($(this).val()) !== -1); });
	}
}

// Set an explicit role + permission list (used when editing an existing user).
function permSetExplicit(prefix, role, perms){
	$('.roleSelect[data-prefix="'+prefix+'"]').val(role);
	var $boxes = $('.perm-grid[data-prefix="'+prefix+'"] .perm-check');
	if(role === 'administrator'){
		$boxes.prop('checked', true).prop('disabled', true);
	} else if (perms && perms.length){
		$boxes.prop('disabled', false);
		$boxes.each(function(){ $(this).prop('checked', perms.indexOf($(this).val()) !== -1); });
	} else {
		permApplyRole(prefix);
	}
}

$(document).on('change', '.roleSelect', function(){
	permApplyRole($(this).data('prefix'));
});

$(function(){ permApplyRole('new'); });


/*=============================================
ACTIVATE USER
=============================================*/
$(document).on("click", ".btnActivate", function(){

	var userId = $(this).attr("userId");
	var userStatus = $(this).attr("userStatus");

	var datum = new FormData();
 	datum.append("activateId", userId);
  	datum.append("activateUser", userStatus);

  	$.ajax({

	  url:"ajax/users.ajax.php",
	  method: "POST",
	  data: datum,
	  cache: false,
      contentType: false,
      processData: false,
      success: function(answer){
      	
      	// console.log("answer", answer);

      	if(window.matchMedia("(max-width:767px)").matches){
		
			swal({
				title: "The user status has been updated",
				type: "success",
				confirmButtonText: "Close"	
			}).then(function(result) {

				if (result.value) {
					window.location = "users";
				}

			})

		}
		
      }

  	})

  	if(userStatus == 0){

  		$(this).removeClass('btn-success');
  		$(this).addClass('btn-danger');
  		$(this).html('Deactivated');
  		$(this).attr('userStatus',1);

  	}else{

  		$(this).addClass('btn-success');
  		$(this).removeClass('btn-danger');
  		$(this).html('Activated');
  		$(this).attr('userStatus',0);

  	}

});


/*=============================================
VALIDATE IF USER ALREADY EXISTS
=============================================*/

$("#newUser").change(function(){

	$(".alert").remove();

	var user = $(this).val();

	var data = new FormData();
 	data.append("validateUser", user);

  	$.ajax({

	  url:"ajax/users.ajax.php",
	  method: "POST",
	  data: data,
	  cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function(answer){ 

      	// console.log("answer", answer);

      	if(answer){

      		$("#newUser").parent().after('<div class="alert alert-warning">This user is already taken</div>');
      		
      		$("#newUser").val('');
      	}

      }

    });

});

/*=============================================
DELETE USER
=============================================*/

$(document).on("click", ".btnDeleteUser", function(){

	var userId = $(this).attr("userId");
	var userPhoto = $(this).attr("userPhoto");
	var username = $(this).attr("username");

	swal({
		title: '¿Are you sure you want to delete the user?',
		text: "¡if you're not sure you can cancel!",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  cancelButtonText: 'Cancel',
		  confirmButtonText: 'Yes, delete user!'
		}).then(function(result){

		if(result.value){

		  var csrf = $('meta[name="csrf-token"]').attr('content');
		  window.location = "index.php?route=users&userId="+userId+"&username="+username+"&userPhoto="+userPhoto+"&_csrf="+encodeURIComponent(csrf);

		}

	})

});



