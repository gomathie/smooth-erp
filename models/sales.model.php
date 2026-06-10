<?php

require_once 'connection.php';


class ModelSales{
	/*=============================================
	SHOWING SALES
	=============================================*/
	/*  */

	static public function mdlShowSales($table, $item, $value){

		if($item != null){

			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE $item = :$item AND idOrganization = :__org ORDER BY id ASC");

			$stmt -> bindParam(":".$item, $value, PDO::PARAM_STR);
			$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

			$stmt -> execute();

			return $stmt -> fetch();

		}else{

			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE idOrganization = :__org ORDER BY id ASC");

			$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

		$stmt -> close();

		$stmt = null;

	}

	/*=============================================
	REGISTERING SALE
	=============================================*/
	/*  */
	static public function mdlAddSale($table, $data){

		$stmt = Connection::connect()->prepare("INSERT INTO $table(code, idCustomer, idSeller, products, tax, netPrice, totalPrice, paymentMethod, idOrganization) VALUES (:code, :idCustomer, :idSeller, :products, :tax, :netPrice, :totalPrice, :paymentMethod, :__org)");

		$stmt->bindValue(":__org", Tenant::id(), PDO::PARAM_INT);
		$stmt->bindParam(":code", $data["code"], PDO::PARAM_INT);
		$stmt->bindParam(":idCustomer", $data["idCustomer"], PDO::PARAM_INT);
		$stmt->bindParam(":idSeller", $data["idSeller"], PDO::PARAM_INT);
		$stmt->bindParam(":products", $data["products"], PDO::PARAM_STR);
		$stmt->bindParam(":tax", $data["tax"], PDO::PARAM_STR);
		$stmt->bindParam(":netPrice", $data["netPrice"], PDO::PARAM_STR);
		$stmt->bindParam(":totalPrice", $data["totalPrice"], PDO::PARAM_STR);
		$stmt->bindParam(":paymentMethod", $data["paymentMethod"], PDO::PARAM_STR);

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
	EDIT SALE
	=============================================*/
	
	static public function mdlEditSale($table, $data){

		$stmt = Connection::connect()->prepare("UPDATE $table SET  idCustomer = :idCustomer, idSeller = :idSeller, products = :products, tax = :tax, netPrice = :netPrice, totalPrice= :totalPrice, paymentMethod = :paymentMethod WHERE code = :code AND idOrganization = :__org");

		$stmt->bindValue(":__org", Tenant::id(), PDO::PARAM_INT);
		$stmt->bindParam(":code", $data["code"], PDO::PARAM_INT);
		$stmt->bindParam(":idCustomer", $data["idCustomer"], PDO::PARAM_INT);
		$stmt->bindParam(":idSeller", $data["idSeller"], PDO::PARAM_INT);
		$stmt->bindParam(":products", $data["products"], PDO::PARAM_STR);
		$stmt->bindParam(":tax", $data["tax"], PDO::PARAM_STR);
		$stmt->bindParam(":netPrice", $data["netPrice"], PDO::PARAM_STR);
		$stmt->bindParam(":totalPrice", $data["totalPrice"], PDO::PARAM_STR);
		$stmt->bindParam(":paymentMethod", $data["paymentMethod"], PDO::PARAM_STR);

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
	DELETE SALE
	=============================================*/

	static public function mdlDeleteSale($table, $data){

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
	/*  */
	/*=============================================
	DATES RANGE
	=============================================*/	

	static public function mdlSalesDatesRange($table, $initialDate, $finalDate){

		if($initialDate == null){

			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE idOrganization = :__org ORDER BY id ASC");

			$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

			$stmt -> execute();

			return $stmt -> fetchAll();


		}else if($initialDate == $finalDate){

			$searchDate = '%' . $finalDate . '%';
			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE saledate LIKE :saledate AND idOrganization = :__org");

			$stmt -> bindParam(":saledate", $searchDate, PDO::PARAM_STR);
			$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}else{

			$actualDate = new DateTime();
			$actualDate ->add(new DateInterval("P1D"));
			$actualDatePlusOne = $actualDate->format("Y-m-d");

			$finalDate2 = new DateTime($finalDate);
			$finalDate2 ->add(new DateInterval("P1D"));
			$finalDatePlusOne = $finalDate2->format("Y-m-d");

			// Use the +1-day end bound when the range ends today (to include today's sales).
			$endDate = ($finalDatePlusOne == $actualDatePlusOne) ? $finalDatePlusOne : $finalDate;

			// Dates come from user input ($_GET) — bind as parameters (no string interpolation).
			$stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE saledate BETWEEN :d1 AND :d2 AND idOrganization = :__org");
			$stmt -> bindValue(":d1", $initialDate, PDO::PARAM_STR);
			$stmt -> bindValue(":d2", $endDate, PDO::PARAM_STR);
			$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

			$stmt -> execute();

			return $stmt -> fetchAll();

		}

	}

	/*  */
	/*=============================================
	Adding TOTAL sales
	=============================================*/

	static public function mdlAddingTotalSales($table){	

		$stmt = Connection::connect()->prepare("SELECT SUM(netPrice) as total FROM $table WHERE idOrganization = :__org");

		$stmt -> bindValue(":__org", Tenant::id(), PDO::PARAM_INT);

		$stmt -> execute();

		return $stmt -> fetch();

		$stmt -> close();

		$stmt = null;

	}
}
