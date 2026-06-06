<?php

class ControllerUsers{

	/*=============================================
	USER LOGIN
	=============================================*/

	static public function ctrUserLogin(){

		if (isset($_POST["loginUser"])) {

			$loginUser = $_POST["loginUser"];
			$loginPass = $_POST["loginPass"];

			if (preg_match('/^[a-zA-Z0-9]+$/', $loginUser) &&
				strlen($loginPass) > 0 && strlen($loginPass) <= 72) {

				$table = 'users';
				$item  = 'user';
				$answer = UsersModel::MdlShowUsers($table, $item, $loginUser);

				if ($answer && $answer["user"] === $loginUser) {

					$verified = false;

					// Try modern password_hash format first
					if (password_verify($loginPass, $answer["password"])) {
						$verified = true;
					}
					// Fall back to legacy crypt and rehash on success
					elseif ($answer["password"] === crypt($loginPass, '$2a$07$asxx54ahjppf45sd87a5a4dDDGsystemdev$')) {
						$verified = true;
						$newHash = password_hash($loginPass, PASSWORD_DEFAULT);
						UsersModel::mdlUpdateUser($table, 'password', $newHash, 'id', $answer["id"]);
					}

					if ($verified) {

						if ($answer["status"] == 1) {

							$_SESSION["loggedIn"] = "ok";
							$_SESSION["id"]      = $answer["id"];
							$_SESSION["name"]    = $answer["name"];
							$_SESSION["user"]    = $answer["user"];
							$_SESSION["photo"]   = $answer["photo"];
							$_SESSION["profile"] = $answer["profile"];

							date_default_timezone_set("America/Bogota");
							$actualDate = date('Y-m-d H:i:s');

							$lastLogin = UsersModel::mdlUpdateUser($table, 'lastLogin', $actualDate, 'id', $answer["id"]);

							if ($lastLogin == "ok") {
								echo '<script>window.location = "home";</script>';
							}

						} else {
							echo '<br><div class="alert alert-danger">User is deactivated</div>';
						}

					} else {
						echo '<br><div class="alert alert-danger">User or password incorrect</div>';
					}

				} else {
					echo '<br><div class="alert alert-danger">User or password incorrect</div>';
				}

			}

		}

	}


	/*=============================================
	CREATE USER
	=============================================*/

	static public function ctrCreateUser(){

		if (isset($_POST["newUser"])) {

			csrf_verify();

			$newEmail = trim($_POST["newEmail"] ?? '');
			$newPhone = trim($_POST["newPhone"] ?? '');

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["newName"]) &&
				preg_match('/^[a-zA-Z0-9]+$/', $_POST["newUser"]) &&
				strlen($_POST["newPasswd"]) > 0 &&
				(empty($newEmail) || filter_var($newEmail, FILTER_VALIDATE_EMAIL)) &&
				(empty($newPhone) || preg_match('/^[0-9()+\- ]+$/', $newPhone))) {

				$photo = "";

				if (isset($_FILES["newPhoto"]["tmp_name"]) && !empty($_FILES["newPhoto"]["tmp_name"])) {

					list($width, $height) = getimagesize($_FILES["newPhoto"]["tmp_name"]);

					$newWidth  = 500;
					$newHeight = 500;

					// Strip everything except alphanumeric to prevent path traversal
					$safeUser = preg_replace('/[^a-zA-Z0-9]/', '', $_POST["newUser"]);
					$folder   = "views/img/users/" . $safeUser;

					mkdir($folder, 0755);

					if ($_FILES["newPhoto"]["type"] == "image/jpeg") {

						$randomNumber = mt_rand(100, 999);
						$photo = "views/img/users/" . $safeUser . "/" . $randomNumber . ".jpg";
						$srcImage = imagecreatefromjpeg($_FILES["newPhoto"]["tmp_name"]);
						$destination = imagecreatetruecolor($newWidth, $newHeight);
						imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagejpeg($destination, $photo);

					}

					if ($_FILES["newPhoto"]["type"] == "image/png") {

						$randomNumber = mt_rand(100, 999);
						$photo = "views/img/users/" . $safeUser . "/" . $randomNumber . ".png";
						$srcImage = imagecreatefrompng($_FILES["newPhoto"]["tmp_name"]);
						$destination = imagecreatetruecolor($newWidth, $newHeight);
						imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($destination, $photo);
					}

				}

				$table      = 'users';
				$encryptpass = password_hash($_POST["newPasswd"], PASSWORD_DEFAULT);

				$data = array(
					'name'     => $_POST["newName"],
					'user'     => $_POST["newUser"],
					'password' => $encryptpass,
					'profile'  => $_POST["newProfile"],
					'photo'    => $photo,
					'email'    => $newEmail,
					'phone'    => $newPhone,
				);

				$answer = UsersModel::mdlAddUser($table, $data);

				if ($answer == 'ok') {

					echo '<script>
					swal({
						type: "success",
						title: "User added succesfully!",
						showConfirmButton: true,
						confirmButtonText: "Close"
					}).then(function(result){
						if (result.value) { window.location = "users"; }
					});
					</script>';

				}

			} else {

				echo '<script>
					swal({
						type: "error",
						title: "No special characters or blank fields",
						showConfirmButton: true,
						confirmButtonText: "Close"
					}).then(function(result){
						if (result.value) { window.location = "users"; }
					});
				</script>';
			}

		}
	}

	/*=============================================
	SHOW USER
	=============================================*/

	static public function ctrShowUsers($item, $value){

		$table  = "users";
		$answer = UsersModel::MdlShowUsers($table, $item, $value);
		return $answer;
	}

	/*=============================================
	EDIT USER
	=============================================*/

	static public function ctrEditUser(){

		if (isset($_POST["EditUser"])) {

			csrf_verify();

			$editEmail = trim($_POST["EditEmail"] ?? '');
			$editPhone = trim($_POST["EditPhone"] ?? '');

			if (preg_match('/^[a-zA-Z0-9ñÑáéíóúÁÉÍÓÚ ]+$/', $_POST["EditName"]) &&
				(empty($editEmail) || filter_var($editEmail, FILTER_VALIDATE_EMAIL)) &&
				(empty($editPhone) || preg_match('/^[0-9()+\- ]+$/', $editPhone))) {

				$photo = $_POST["currentPicture"];

				if (isset($_FILES["editPhoto"]["tmp_name"]) && !empty($_FILES["editPhoto"]["tmp_name"])) {

					list($width, $height) = getimagesize($_FILES["editPhoto"]["tmp_name"]);

					$newWidth  = 500;
					$newHeight = 500;

					$safeUser = preg_replace('/[^a-zA-Z0-9]/', '', $_POST["EditUser"]);
					$folder   = "views/img/users/" . $safeUser;

					if (!empty($_POST["currentPicture"]) &&
						strpos($_POST["currentPicture"], 'views/img/users/') === 0) {
						unlink($_POST["currentPicture"]);
					} else {
						mkdir($folder, 0755);
					}

					if ($_FILES["editPhoto"]["type"] == "image/jpeg") {

						$randomNumber = mt_rand(100, 999);
						$photo = "views/img/users/" . $safeUser . "/" . $randomNumber . ".jpg";
						$srcImage = imagecreatefromjpeg($_FILES["editPhoto"]["tmp_name"]);
						$destination = imagecreatetruecolor($newWidth, $newHeight);
						imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagejpeg($destination, $photo);

					}

					if ($_FILES["editPhoto"]["type"] == "image/png") {

						$randomNumber = mt_rand(100, 999);
						$photo = "views/img/users/" . $safeUser . "/" . $randomNumber . ".png";
						$srcImage = imagecreatefrompng($_FILES["editPhoto"]["tmp_name"]);
						$destination = imagecreatetruecolor($newWidth, $newHeight);
						imagecopyresized($destination, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
						imagepng($destination, $photo);
					}

				}

				$table = 'users';

				if ($_POST["EditPasswd"] != "") {

					$encryptpass = password_hash($_POST["EditPasswd"], PASSWORD_DEFAULT);

				} else {

					$encryptpass = $_POST["currentPasswd"];

				}

				$data = array(
					'name'     => $_POST["EditName"],
					'user'     => $_POST["EditUser"],
					'password' => $encryptpass,
					'profile'  => $_POST["EditProfile"],
					'photo'    => $photo,
					'email'    => $editEmail,
					'phone'    => $editPhone,
				);

				$answer = UsersModel::mdlEditUser($table, $data);

				if ($answer == 'ok') {

					echo '<script>
					swal({
						type: "success",
						title: "User edited succesfully!",
						showConfirmButton: true,
						confirmButtonText: "Close"
					}).then(function(result){
						if (result.value) { window.location = "users"; }
					});
					</script>';

				} else {

					echo '<script>
					swal({
						type: "error",
						title: "No special characters in the name or blank field",
						showConfirmButton: true,
						confirmButtonText: "Close"
					}).then(function(result){
						if (result.value) { window.location = "users"; }
					});
					</script>';
				}

			}

		}

	}

	/*=============================================
	DELETE USER
	=============================================*/

	static public function ctrDeleteUser(){

		if (isset($_GET["userId"])) {

			csrf_verify();

			$table = "users";
			$data  = $_GET["userId"];

			// Validate paths before filesystem operations
			if (!empty($_GET["userPhoto"]) &&
				strpos($_GET["userPhoto"], 'views/img/users/') === 0 &&
				file_exists($_GET["userPhoto"])) {
				unlink($_GET["userPhoto"]);
			}

			if (!empty($_GET["username"]) &&
				preg_match('/^[a-zA-Z0-9]+$/', $_GET["username"])) {
				@rmdir('views/img/users/' . $_GET["username"]);
			}

			$answer = UsersModel::mdlDeleteUser($table, $data);

			if ($answer == "ok") {

				echo '<script>
				swal({
					type: "success",
					title: "The user has been succesfully deleted",
					showConfirmButton: true,
					confirmButtonText: "Close"
				}).then(function(result){
					if (result.value) { window.location = "users"; }
				})
				</script>';

			}

		}

	}

}
