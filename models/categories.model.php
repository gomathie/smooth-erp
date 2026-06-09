<?php


require_once "connection.php";

class CategoriesModel{
	/*  */
	/*=============================================
	CREATE CATEGORY
	=============================================*/

	static public function mdlAddCategory($table, $data){

		$stmt = Connection::connect()->prepare("INSERT INTO $table(Category, idOrganization) VALUES (:category, :__org)");

		$stmt -> bindParam(":category", $data, PDO::PARAM_STR);
		$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

		if ($stmt->execute()) {

			return 'ok';

		} else {

			return 'error';

		}
		
		$stmt -> close();

		$stmt = null;
	}
	/*  */
	/*=============================================
	SHOW CATEGORY 
	=============================================*/
	
	static public function mdlShowCategories($table, $item, $value){

		if($item != null){

			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE $item = :$item AND idOrganization = :__org");

			$stmt -> bindParam(":".$item, $value, PDO::PARAM_STR);
			$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

			$stmt -> execute();

			return $stmt -> fetch();

		}
		else{
			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE idOrganization = :__org");

			$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}
	/*  */
	/*=============================================
	EDIT CATEGORY
	=============================================*/

	static public function mdlEditCategory($table, $data){

		$stmt = Connection::connect()->prepare("UPDATE $table SET Category = :Category WHERE id = :id AND idOrganization = :__org");

		$stmt -> bindParam(":Category", $data["Category"], PDO::PARAM_STR);
		$stmt -> bindParam(":id", $data["id"], PDO::PARAM_INT);
		$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

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
	DELETE CATEGORY
	=============================================*/

	static public function mdlDeleteCategory($table, $data){

		$stmt = Connection::connect()->prepare("DELETE FROM $table WHERE id = :id AND idOrganization = :__org");

		$stmt -> bindParam(":id", $data, PDO::PARAM_INT);
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
