<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: text/html; charset=utf-8');

require 'connection.php';
require 'functions.php';


if(isset($_POST['class'])){
	$class = $_POST['class'];
}
else{
	echo(json_encode('Nie podano nazwy klasy'));
	return;
}


if(isset($_POST['action'])){
	$action = $_POST['action'];
}
else{
	echo(json_encode('Nie podano akcji'));
	return;
}


//dla każdej przysłanej komendy $class, powinna być tak nazwana klasa
if(class_exists($class)){
	$class = new $class;
	
	if(isset($_POST['value'])){
		$value = $_POST['value'];
	}
	else{
		$value = null;
	}
	if(isset($_POST['where'])){
		$where = $_POST['where'];
	}
	else{
		//do testu żeby dało się update-ować, TODO wysyłać ajaxem dodatkowy parametr $where
		$where ='id_gracza = 1';
		//$where = null;
	}
	
	switch($action){
		case 'select':
			echo($class->select());
			break;
		case 'insert':
			if(!is_null($value)){
				echo($class->insert($value));
			}
			else{
				echo(json_encode('Nie podano wartości do \'INSERT\''));
			}
			break;
		case 'update':
			if(!is_null($value)){
				echo($class->update($value));
			}
			else{
				echo(json_encode('Nie podano wartości do \'UPDATE\''));
			}
			break;
		case 'delete':
			if(!is_null($where)){
				echo($class->delete($where));
			}
			else{
				echo(json_encode('Nie podano warunku do \'DELETE\''));
			}
			break;
	}
}
else{
	echo(json_encode('Podana klasa: \'' . $class .'\' nie istnieje'));
}

?>