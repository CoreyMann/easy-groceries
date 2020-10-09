<?php

//require_once("inc/connect_pdo.php");
date_default_timezone_set('America/Toronto');


class EasyGroceries {

	public $dbo = "";



	// define constructor
	public function __construct ()
	{
		require_once("./inc/connect_pdo.php");
		$this->dbo = $dbo;

	}


	//productBySearch

	public function getCategory ($upc) {

		try {
			$query = "SELECT id, file
			FROM ea_images
			WHERE upc = '$upc' ";
			foreach($this->dbo->query($query) as $row) {
				$id = stripslashes($row[0]);
				$file = stripslashes($row[1]);
			}

			if ($id) {
				$upc = mb_substr($upc, 0, 4);
				$image = "./product_images/$upc/$file";
			}


		} catch (PDOException $e) {
			$this->errorCode = 1;
			$errorCode = -1;
			$errorMessage = "PDOException for getGameCode.";
		}

		return $image;
	}

	public function makeRandomPrice ($product_id) {
		srand(time()+$product_id);
		$pool = "0123456789";
		for($index = 0; $index < 3; $index++) {
			$sid .= substr($pool,(rand()%(strlen($pool))), 1);
		}

		$sid = $sid/100;
		$sid = number_format((float)$sid,2,".","");
		return($sid);
	}



	public function updatePrice ($product_id, $price) {

		$query = "UPDATE ea_product
		SET avg_price = '$price'
		WHERE id = '$product_id' ";
		$this->dbo->query($query);
		print("$query<br>");

	}



	public function price () {

		$query = "SELECT id, avg_price
		FROM ea_product
        WHERE avg_price < 0.99 ";
		foreach($this->dbo->query($query) as $row) {

			$product_id = stripslashes($row[0]);
			$price = $this->makeRandomPrice($product_id);
			$this->updatePrice($product_id, $price);
		}
	}



	public function saveCart ($name,$cart,$quantity) {

		$cart_arr = explode("|", $cart);
		$quantity_arr = explode("|", $quantity);
		$customer_name = $name;
		//var_dump($cart_arr);

		$errorCode = 0;
		$errorMessage = "";

		$sub_total = 0.00;

		try {

			if (!empty($cart)) {

				// inset into order_list
				$query = "INSERT INTO ea_cart
				SET date_me = now(),
				name = '$name'
				";


				$this->dbo->query($query);

				$order_id = $this->dbo->lastInsertId();



				// insert into item

				$query = "SELECT id, avg_price
				FROM ea_product ";
				foreach($this->dbo->query($query) as $row) {

					$product_id = stripslashes($row[0]);
					$price = stripslashes($row[1]);

					$price_arr[$product_id] = $price;
				}

				//print("cart: $cart quantity: $quantity");


				foreach ($cart_arr as $key=>$value) {
					$query = "INSERT INTO ea_item
					SET cart_id = '$order_id',
					order_me = '$x',
					product_id = '$value',
					quantity = '$quantity_arr[$key]',
					price = '$price_arr[$key]'
					";
					$this->dbo->query($query);
				}



				$x = 1;
				$query = "SELECT ea_product.id, ea_product.product_name, ea_product.product_description, ea_product.avg_price, ea_product.upc,
				ea_category.name, ea_item.order_me, ea_item.quantity
				FROM ea_product, ea_category, ea_item
				WHERE ea_product.category_id = ea_category.id
				AND ea_product.id = ea_item.product_id
				AND ea_item.cart_id = '$order_id'
				ORDER BY ea_item.order_me ";
				//print("$query");

				foreach($this->dbo->query($query) as $row) {

					$product_id = stripslashes($row[0]);
					$name = stripslashes($row[1]);
					$description = stripslashes($row[2]);
					$price = stripslashes($row[3]);
					$upc = stripslashes($row[4]);
					$category_name = stripslashes($row[5]);
					$item = stripslashes($row[6]);
					$quantity = stripslashes($row[7]);

					$extended_price = $price * $quantity;


					$product["item"] = "$item";
					$product["product_id"] = $product_id;
					$product["name"] = $name;
					$product["description"] = $description;
					$product["quantity"] = "$quantity";
					$product["price"] = number_format((float)$price,2,".","");
					$product["extended_price"] = number_format((float)$extended_price,2,".","");
					$product["upc"] = "$upc";
					$product["category_name"] = $category_name;
					++$x;
					$products[] = $product;
					$sub_total = $sub_total + $extended_price;
				}

			}


		} catch (PDOException $e) {
			$this->errorCode = 1;
			$errorCode = -1;
			$errorMessage = "PDOException for getDepartments.";
		}

		$hst = $sub_total * 0.13;
		$hst = round($hst*100)/100;
		$total = $sub_total + $hst;

		$sub_total = number_format((float)$sub_total,2,".","");
		$hst = number_format((float)$hst,2,".","");
		$total = number_format((float)$total,2,".","");

		$error["id"] = $errorCode;
		$error["message"] = $errorMessage;

		$data["error"] = $error;
		$data["order_id"] = $order_id;
		$data["name"] = $customer_name;
		$data["sub_total"] = $sub_total;
		$data["hst"] = $hst;
		$data["total"] = $total;

		$data["products"] = $products;

		$data["query"] = $query;


		return $data = json_encode($data);
	}




	public function productBySearch ($search) {

		$errorCode = 0;
		$errorMessage = "";

		try {


			if (!empty($search)) {
				$query = "SELECT ea_product.id, upc, brand, product_name, product_description, avg_price, category_id, ea_category.name
				FROM ea_product, ea_category
				WHERE ea_product.category_id = ea_category.id
				AND (upc LIKE '%$search%'
				OR brand LIKE '%$search%'
				OR product_name LIKE '%$search%'
				OR ea_category.name LIKE '%$search%' )
				ORDER BY product_name ";
				foreach($this->dbo->query($query) as $row) {

					$id = stripslashes($row[0]);
					$upc = stripslashes($row[1]);
					$brand = stripslashes($row[2]);
					$product_name = stripslashes($row[3]);
					$product_description = stripslashes($row[4]);
					$avg_price = stripslashes($row[5]);
					$category_id = stripslashes($row[6]);
					$category_name = stripslashes($row[7]);

					$product["id"] = $id;
					$product["upc"] = "$upc";
					$product["brand"] = $brand;
					$product["product_name"] = $product_name;
					$product["product_description"] = $product_description;
					$product["avg_price"] = $avg_price;
					$product["image"] = $this->getImage($upc);
					$product["category_id"] = $category_id;
					$product["category_name"] = $category_name;

					$products[] = $product;
				}

			}



		} catch (PDOException $e) {
			$this->errorCode = 1;
			$errorCode = -1;
			$errorMessage = "PDOException for getDepartments.";
		}

		$error["id"] = $errorCode;
		$error["message"] = $errorMessage;

		$data["error"] = $error;

		$data["search"] = $search;
		$data["products"] = $products;

		$data["query"] = $query;


		return $data = json_encode($data);
	}


	public function getImage ($upc) {

		try {
			$query = "SELECT id, file
			FROM ea_images
			WHERE upc = '$upc' ";
			foreach($this->dbo->query($query) as $row) {
				$id = stripslashes($row[0]);
				$file = stripslashes($row[1]);
			}

			if ($id) {
				$upc = mb_substr($upc, 0, 4);
				$image = "./product_images/$upc/$file";
			}


		} catch (PDOException $e) {
			$this->errorCode = 1;
			$errorCode = -1;
			$errorMessage = "PDOException for getGameCode.";
		}

		return $image;
	}


	public function getProducts ($cart,$quantity) {

		$data["stuff"] = $cart;
        $data["quantity"] = $quantity;

        //print("CART: $cart");

        $cart_arr = explode("|", $cart);
		$quantity_arr = explode("|", $quantity);


		$cart_string = "";

        foreach ($cart_arr as $key=>$value) {

            if ($cart_string == "") {
				$cart_string = "$value";
			} else {
				$cart_string = "$cart_string, $value";
			}

            //$data = $quantity_arr[$key];
			$quantity_array[$value] = $quantity_arr[$key];

		}


        //var_dump($quantity_array);

		//$data["stuff2"] = $cart_string;

		$errorCode = 0;
		$errorMessage = "";

		try {

			$query = "SELECT name
			FROM ea_category
			WHERE id = '$category_id' ";
			foreach($this->dbo->query($query) as $row) {

				$name = stripslashes($row[0]);

			}

            //var_dump($quantity_array);

            //ea_product.id, ea_product.product_name, ea_product.product_description, ea_product.avg_price, ea_product.upc, ea_category.name, ea_item.order_me, ea_item.quantity


			$item = 1;
			$query = "SELECT ea_product.id, ea_product.upc, ea_product.brand, ea_product.product_name, ea_product.product_description, ea_product.avg_price, ea_category.name
			FROM ea_product, ea_category
			WHERE ea_product.category_id = ea_category.id
			AND ea_product.id IN ($cart_string)
			ORDER BY ea_category.name, ea_product.product_name ";
			foreach($this->dbo->query($query) as $row) {

				$product_id = stripslashes($row[0]);
                $upc = stripslashes($row[1]);
                $brand = stripslashes($row[2]);
                $name = stripslashes($row[3]);
                $description = stripslashes($row[4]);
                $price = stripslashes($row[5]);
                $category_name = stripslashes($row[6]);

                $quantity = $quantity_array[$product_id];

                $extended_price = $price * $quantity;
                $sub_total = $sub_total + $extended_price;

                $product["item"] = "$item";
                $product["product_id"] = $product_id;
                $product["brand"] = $brand;
                $product["name"] = $name;
                $product["description"] = $description;
                $product["quantity"] = "$quantity";
                $product["price"] = number_format((float)$price,2,".","");
                $product["extended_price"] = number_format((float)$extended_price,2,".","");
                $product["upc"] = "$upc";
                $product["category_name"] = $category_name;
                ++$x;
                $products[] = $product;

                ++$item;

                /*
                $id = stripslashes($row[0]);
				$upc = stripslashes($row[1]);
				$brand = stripslashes($row[2]);
				$product_name = stripslashes($row[3]);
				$product_description = stripslashes($row[4]);
				$avg_price = stripslashes($row[5]);
				$category_name = stripslashes($row[6]);

				$product["id"] = $id;
				$product["upc"] = "$upc";
				$product["category_name"] = $category_name;
				$product["brand"] = $brand;
				$product["product_name"] = $product_name;
				$product["product_description"] = $product_description;
				$product["quantity"] = $quantity_array[$id];
				$product["avg_price"] = $avg_price;
				$product["extended_price"] = number_format((float)$avg_price * (float)$quantity_array[$id],2,".","");
				$product["image"] = $this->getImage($upc);

				$products[] = $product;
                */

			}



		} catch (PDOException $e) {
			$this->errorCode = 1;
			$errorCode = -1;
			$errorMessage = "PDOException for getDepartments.";
		}

		$hst = $sub_total * 0.13;
		$hst = round($hst*100)/100;
		$total = $sub_total + $hst;

		$sub_total = number_format((float)$sub_total,2,".","");
		$hst = number_format((float)$hst,2,".","");
		$total = number_format((float)$total,2,".","");

		$error["id"] = $errorCode;
		$error["message"] = $errorMessage;

		$data["error"] = $error;
		$data["sub_total"] = $sub_total;
		$data["hst"] = $hst;
		$data["total"] = $total;

		$data["category_id"] = $category_id;
		$data["category_name"] = $name;
		$data["products"] = $products;

		$data["query"] = $query;


		return $data = json_encode($data);
	}



	public function productByDepartment ($category_id) {

		$errorCode = 0;
		$errorMessage = "";

		try {

			$query = "SELECT name
			FROM ea_category
			WHERE id = '$category_id' ";
			foreach($this->dbo->query($query) as $row) {

				$name = stripslashes($row[0]);

			}


			$query = "SELECT id, upc, brand, product_name, product_description, avg_price
			FROM ea_product
			WHERE category_id = '$category_id'
			ORDER BY product_name ";
			foreach($this->dbo->query($query) as $row) {

				$id = stripslashes($row[0]);
				$upc = stripslashes($row[1]);
				$brand = stripslashes($row[2]);
				$product_name = stripslashes($row[3]);
				$product_description = stripslashes($row[4]);
				$avg_price = stripslashes($row[5]);

				$product["id"] = $id;
				$product["upc"] = "$upc";
				$product["brand"] = $brand;
				$product["product_name"] = $product_name;
				$product["product_description"] = $product_description;
				$product["avg_price"] = $avg_price;
				$product["image"] = $this->getImage($upc);

				$products[] = $product;
			}



		} catch (PDOException $e) {
			$this->errorCode = 1;
			$errorCode = -1;
			$errorMessage = "PDOException for getDepartments.";
		}

		$error["id"] = $errorCode;
		$error["message"] = $errorMessage;

		$data["error"] = $error;

		$data["category_id"] = $category_id;
		$data["category_name"] = $name;
		$data["products"] = $products;

		$data["query"] = $query;


		return $data = json_encode($data);
	}



	public function getDepartments () {

		$errorCode = 0;
		$errorMessage = "";

		try {

			$query = "SELECT id, name
			FROM ea_category
			ORDER BY name ";
			foreach($this->dbo->query($query) as $row) {

				$id = stripslashes($row[0]);
				$name = stripslashes($row[1]);

				$department["id"] = $id;
				$department["name"] = $name;

				$departments[] = $department;
			}



		} catch (PDOException $e) {
			$this->errorCode = 1;
			$errorCode = -1;
			$errorMessage = "PDOException for getDepartments.";
		}

		$error["id"] = $errorCode;
		$error["message"] = $errorMessage;

		$data["error"] = $error;

		$data["departments"] = $departments;
		$data["query"] = $query;


		return $data = json_encode($data);

	}




}

?>
