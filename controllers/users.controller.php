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

							// Multi-tenant context: bind the session to the user's organization.
							// Super Admin (idOrganization NULL) has no org until they "enter" one.
							$_SESSION["idOrganization"] = isset($answer["idOrganization"]) && $answer["idOrganization"] !== null
								? (int)$answer["idOrganization"] : 0;
							$_SESSION["isSuperAdmin"] = ($answer["profile"] === "SuperAdmin");

							// Load the org's base currency for display/defaults.
							if ($_SESSION["idOrganization"] > 0 && class_exists("ModelOrganizations")) {
								$org = ModelOrganizations::mdlGetOrganization($_SESSION["idOrganization"]);
								$_SESSION["baseCurrency"] = is_array($org) ? $org["baseCurrency"] : "USD";
							}

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
	REQUEST PASSWORD RESET
	=============================================*/

	static public function ctrForgotPassword(){

		if (isset($_POST["resetIdentifier"])) {

			csrf_verify();

			$identifier = trim($_POST["resetIdentifier"]);
			$answer = false;

			if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
				$answer = UsersModel::MdlShowUsers('users', 'email', $identifier);
			} elseif (preg_match('/^[a-zA-Z0-9]+$/', $identifier)) {
				$answer = UsersModel::MdlShowUsers('users', 'user', $identifier);
			}

			$notice = '<br><div class="alert alert-info">If that account exists and has an email address, a reset link has been sent.</div>';

			if ($answer && !empty($answer["id"])) {
				$token = bin2hex(random_bytes(32));
				$tokenHash = hash('sha256', $token);
				$expiresAt = date('Y-m-d H:i:s', time() + 3600);

				if (UsersModel::mdlSetPasswordReset((int)$answer["id"], $tokenHash, $expiresAt) == 'ok') {
					$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
					$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
					$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
					$resetLink = $scheme . '://' . $host . $basePath . '/index.php?route=reset-password&token=' . urlencode($token);

					try {
						send_password_reset_email($answer, $resetLink);
					} catch (Throwable $exception) {
						if (filter_var(env_value('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN)) {
							$notice .= '<div class="alert alert-warning">SMTP is not ready yet: ' . h($exception->getMessage()) . '</div>';
						}
					}
				}
			}

			echo $notice;
		}
	}

	/*=============================================
	RESET PASSWORD
	=============================================*/

	static public function ctrResetPassword(){

		if (isset($_POST["resetToken"], $_POST["newPassword"], $_POST["confirmPassword"])) {

			csrf_verify();

			$token = trim($_POST["resetToken"]);
			$newPassword = $_POST["newPassword"];
			$confirmPassword = $_POST["confirmPassword"];

			if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
				echo '<br><div class="alert alert-danger">Invalid reset link.</div>';
				return;
			}

			if ($newPassword !== $confirmPassword) {
				echo '<br><div class="alert alert-danger">Passwords do not match.</div>';
				return;
			}

			if (strlen($newPassword) < 6 || strlen($newPassword) > 72) {
				echo '<br><div class="alert alert-danger">Password must be between 6 and 72 characters.</div>';
				return;
			}

			$tokenHash = hash('sha256', $token);
			$answer = UsersModel::MdlShowUsers('users', 'resetToken', $tokenHash);

			if (!$answer || empty($answer["resetTokenExpires"]) || strtotime($answer["resetTokenExpires"]) < time()) {
				echo '<br><div class="alert alert-danger">This reset link is invalid or expired.</div>';
				return;
			}

			$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
			$updated = UsersModel::mdlUpdateUser('users', 'password', $passwordHash, 'id', $answer["id"]);

			if ($updated == 'ok') {
				UsersModel::mdlClearPasswordReset((int)$answer["id"]);
				echo '<br><div class="alert alert-success">Password updated. You can now log in.</div>
					  <script>setTimeout(function(){ window.location = "login"; }, 1800);</script>';
			} else {
				echo '<br><div class="alert alert-danger">Password could not be updated.</div>';
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
