<?php

// GET LIST OF DEPARTMENTS



require_once("easy_groceries.class.php");

$oEasyGroceries = new EasyGroceries();

$data = $oEasyGroceries->price();

header("Content-Type: application/json");

echo $data;

?>
