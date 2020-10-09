<?php

// GET LIST OF DEPARTMENTS


if ($_POST["search"]) {
	$search = $_POST["search"];
} else {
	$search = $_GET["search"];
}


require_once("easy_groceries.class.php");

$oEasyGroceries = new EasyGroceries();

$data = $oEasyGroceries->productBySearch($search);

header("Content-Type: application/json");

echo $data;

?>
