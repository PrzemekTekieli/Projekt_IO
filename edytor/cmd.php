<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/html; charset=utf-8');

require 'connection.php';
require 'functions.php';

if(isset($_GET['action'])){
	$action = $_GET['action'];
}
else{
	$action = null;
}
if(isset($_GET['value'])){
	$value = $_GET['value'];
}
else{
	$value = null;
}


if(function_exists($action)){
	echo($action($value));
}
else{
	echo(json_encode('Nieznana komenda: \'' . $action .'\''));
}


?>