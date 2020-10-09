<?php

// GET LIST OF DEPARTMENTS


if ($_POST["category_id"]) {
	$category_id = $_POST["category_id"];
} else {
	$category_id = $_GET["category_id"];
}


require_once("easy_groceries.class.php");

$oEasyGroceries = new EasyGroceries();

$data = $oEasyGroceries->productByDepartment($category_id);

header("Content-Type: application/json");

echo $data;

?>
