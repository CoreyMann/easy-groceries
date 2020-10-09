<?php

// GET LIST OF DEPARTMENTS


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

$data = $oEasyGroceries->getProducts($cart,$quantity);

header("Content-Type: application/json");

echo $data;

?>
