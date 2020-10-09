<?php

// GET LIST OF DEPARTMENTS

if ($_POST["customer_name"]) {
	$customer_name = $_POST["customer_name"];
} else {
	$customer_name = $_GET["customer_name"];
}

if ($_POST["cart"]) {
	$cart = $_POST["cart"];
} else {
	$cart = $_GET["cart"];
}

if ($_POST["quantity"]) {
	$quantity = $_POST["quantity"];
} else {
	$quantity = $_GET["quantity"];
}


require_once("easy_groceries.class.php");

$oEasyGroceries = new EasyGroceries();

$data = $oEasyGroceries->saveCart($customer_name,$cart,$quantity);

header("Content-Type: application/json");

echo $data;

?>
