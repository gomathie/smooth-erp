<?php

require_once "connection.php";

class UsersModel{

	/*=============================================
	SHOW USER
	=============================================*/

	static public function MdlShowUsers($tableUsers, $item, $value){

		if ($tableUsers !== 'users') return false;

		$org = Tenant::id();

		if ($item != null) {

			// Whitelist allowed column names to prevent SQL injection
			if (!in_array($item, ['id', 'user', 'email', 'resetToken'], true)) return false;

			// Auth lookups (login / password reset) run before an org context
			// exists, so they must NOT be org-scoped. Everything else is.
			$authLookup = in_array($item, ['user', 'email', 'resetToken'], true);

			$sql = "SELECT * FROM users WHERE " . Connection::quoteIdent($item) . " = :val";
			if (!$authLookup && $org > 0) { $sql .= " AND idOrganization = " . (int)$org; }

			$stmt = Connection::connect()->prepare($sql);
			$stmt->bindParam(':val', $value, PDO::PARAM_STR);
			$stmt->execute();
			return $stmt->fetch();

		} else {

			$sql = "SELECT * FROM users";
			if ($org > 0) { $sql .= " WHERE idOrganization = " . (int)$org; }

			$stmt = Connection::connect()->prepare($sql);
			$stmt->execute();
			return $stmt->fetchAll();
		}
	}


	/*=============================================
	ADD USER
	=============================================*/

	static public function mdlAddUser($table, $data){

		$stmt = Connection::connect()->prepare(
			"INSERT INTO users(name, " . Connection::quoteIdent('user') . ", password, profile, role, permissions, photo, email, phone, idOrganization) VALUES (:name, :user, :password, :profile, :role, :permissions, :photo, :email, :phone, " . (int)Tenant::id() . ")"
		);

		$stmt->bindParam(":name",     $data["name"],     PDO::PARAM_STR);
		$stmt->bindParam(":user",     $data["user"],     PDO::PARAM_STR);
		$stmt->bindParam(":password", $data["password"], PDO::PARAM_STR);
		$stmt->bindParam(":profile",  $data["profile"],  PDO::PARAM_STR);
		$stmt->bindValue(":role",        $data["role"]        ?? 'staff', PDO::PARAM_STR);
		$stmt->bindValue(":permissions", $data["permissions"] ?? null,    $data["permissions"] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
		$stmt->bindParam(":photo",    $data["photo"],    PDO::PARAM_STR);
		$stmt->bindParam(":email",    $data["email"],    PDO::PARAM_STR);
		$stmt->bindParam(":phone",    $data["phone"],    PDO::PARAM_STR);

		return $stmt->execute() ? 'ok' : 'error';
	}


	/*=============================================
	EDIT USER
	=============================================*/

	static public function mdlEditUser($table, $data){

		$stmt = Connection::connect()->prepare(
			"UPDATE users SET name = :name, password = :password, profile = :profile, role = :role, permissions = :permissions, photo = :photo, email = :email, phone = :phone WHERE " . Connection::quoteIdent('user') . " = :user AND idOrganization = " . (int)Tenant::id() . ""
		);

		$stmt->bindParam(":name",     $data["name"],     PDO::PARAM_STR);
		$stmt->bindParam(":user",     $data["user"],     PDO::PARAM_STR);
		$stmt->bindParam(":password", $data["password"], PDO::PARAM_STR);
		$stmt->bindParam(":profile",  $data["profile"],  PDO::PARAM_STR);
		$stmt->bindValue(":role",        $data["role"]        ?? 'staff', PDO::PARAM_STR);
		$stmt->bindValue(":permissions", $data["permissions"] ?? null,    $data["permissions"] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
		$stmt->bindParam(":photo",    $data["photo"],    PDO::PARAM_STR);
		$stmt->bindParam(":email",    $data["email"],    PDO::PARAM_STR);
		$stmt->bindParam(":phone",    $data["phone"],    PDO::PARAM_STR);

		return $stmt->execute() ? 'ok' : 'error';
	}


	/*=============================================
	UPDATE USER (used for lastLogin, status, password)
	=============================================*/

	static public function mdlUpdateUser($table, $item1, $value1, $item2, $value2){

		// Whitelist columns to prevent SQL injection via dynamic column names
		$allowed = ['id', 'user', 'status', 'lastLogin', 'password', 'resetToken', 'resetTokenExpires'];

		if (!in_array($item1, $allowed, true) || !in_array($item2, $allowed, true)) {
			return 'error';
		}

		$stmt = Connection::connect()->prepare(
			"UPDATE users SET " . Connection::quoteIdent($item1) . " = :v1 WHERE " . Connection::quoteIdent($item2) . " = :v2"
		);

		$stmt->bindParam(':v1', $value1, PDO::PARAM_STR);
		$stmt->bindParam(':v2', $value2, PDO::PARAM_STR);

		return $stmt->execute() ? 'ok' : 'error';
	}

	/*=============================================
	SET PASSWORD RESET TOKEN
	=============================================*/

	static public function mdlSetPasswordReset($userId, $tokenHash, $expiresAt){

		$stmt = Connection::connect()->prepare(
			"UPDATE users SET resetToken = :token, resetTokenExpires = :expires WHERE id = :id"
		);

		$stmt->bindParam(':token', $tokenHash, PDO::PARAM_STR);
		$stmt->bindParam(':expires', $expiresAt, PDO::PARAM_STR);
		$stmt->bindParam(':id', $userId, PDO::PARAM_INT);

		return $stmt->execute() ? 'ok' : 'error';
	}

	/*=============================================
	CLEAR PASSWORD RESET TOKEN
	=============================================*/

	static public function mdlClearPasswordReset($userId){

		$empty = null;

		$stmt = Connection::connect()->prepare(
			"UPDATE users SET resetToken = :token, resetTokenExpires = :expires WHERE id = :id"
		);

		$stmt->bindParam(':token', $empty, PDO::PARAM_NULL);
		$stmt->bindParam(':expires', $empty, PDO::PARAM_NULL);
		$stmt->bindParam(':id', $userId, PDO::PARAM_INT);

		return $stmt->execute() ? 'ok' : 'error';
	}


	/*=============================================
	DELETE USER
	=============================================*/

	static public function mdlDeleteUser($table, $data){

		$stmt = Connection::connect()->prepare("DELETE FROM users WHERE id = :id AND idOrganization = " . (int)Tenant::id() . "");
		$stmt->bindParam(":id", $data, PDO::PARAM_STR);
		return $stmt->execute() ? 'ok' : 'error';
	}

}
