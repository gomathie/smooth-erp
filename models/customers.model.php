<?php

require_once "connection.php";

class ModelCustomers{

	/*=============================================
	CREATE CUSTOMERS
	=============================================*/
	/*  */
	static public function mdlAddCustomer($table, $data){

		$stmt = Connection::connect()->prepare("INSERT INTO $table(name, idDocument, email, phone, address, birthdate, idOrganization) VALUES (:name, :idDocument, :email, :phone, :address, :birthdate, :__org)");

		$stmt->bindParam(":name", $data["name"], PDO::PARAM_STR);
		$stmt->bindParam(":idDocument", $data["idDocument"], PDO::PARAM_INT);
		$stmt->bindParam(":email", $data["email"], PDO::PARAM_STR);
		$stmt->bindParam(":phone", $data["phone"], PDO::PARAM_STR);
		$stmt->bindParam(":address", $data["address"], PDO::PARAM_STR);
		$stmt->bindParam(":birthdate", $data["birthdate"], PDO::PARAM_STR);
		$stmt->bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}
	/*  */
	/*=============================================
	SHOW CUSTOMERS
	=============================================*/

	static public function mdlShowCustomers($table, $item, $value){

		if($item != null){
			return Scope::firstBy($table, $item, $value);
		}

		return Scope::all($table);

	}
	/*  */
	/*=============================================
	EDIT CUSTOMER
	=============================================*/

	static public function mdlEditCustomer($table, $data){

		$stmt = Connection::connect()->prepare("UPDATE $table SET name = :name, idDocument = :idDocument, email = :email, phone = :phone, address = :address, birthdate = :birthdate WHERE id = :id AND idOrganization = :__org");

		$stmt->bindParam(":id", $data["id"], PDO::PARAM_INT);
		$stmt->bindParam(":name", $data["name"], PDO::PARAM_STR);
		$stmt->bindParam(":idDocument", $data["idDocument"], PDO::PARAM_INT);
		$stmt->bindParam(":email", $data["email"], PDO::PARAM_STR);
		$stmt->bindParam(":phone", $data["phone"], PDO::PARAM_STR);
		$stmt->bindParam(":address", $data["address"], PDO::PARAM_STR);
		$stmt->bindParam(":birthdate", $data["birthdate"], PDO::PARAM_STR);
		$stmt->bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

		if($stmt->execute()){

			return "ok";

		}else{

			return "error";
		
		}

		$stmt->close();
		$stmt = null;

	}
	/*  */
	/*=============================================
	DELETE CUSTOMER
	=============================================*/

	static public function mdlDeleteCustomer($table, $data){

		return Scope::deleteById($table, (int)$data) ? "ok" : "error";

	}
	/*  */
	/*=============================================
	UPDATE CUSTOMER
	=============================================*/

	static public function mdlUpdateCustomer($table, $item1, $value1, $value){

		$stmt = Connection::connect()->prepare("UPDATE $table SET $item1 = :$item1 WHERE id = :id AND idOrganization = :__org");

		$stmt -> bindParam(":".$item1, $value1, PDO::PARAM_STR);
		$stmt -> bindParam(":id", $value, PDO::PARAM_STR);
		$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

		if($stmt -> execute()){

			return "ok";
		
		}else{

			return "error";	

		}

		$stmt -> close();

		$stmt = null;

	}

}
