<?php

function player($val){
	//gdy $val jest ustawione, to znaczy że chcemy zapisać to w bazie danych
	//jeśli nie jest, to znaczy że chcemy odczytać
	if(is_null($val)){
		//get
		$db = new my_connection();
		$result = $db->query("SELECT * FROM gracze");
		//$result = $db->fetchAll($result);
		$result = $db->fetchAssoc($result);

		echo(json_encode($result));
	}
	else{
		//set
		echo("set");
	}
}

function character($val){
	if(is_null($val)){
		//get
		$db = new my_connection();
		$result = $db->query("SELECT * FROM postacie");
		$result = $db->fetchAssoc($result);

		echo(json_encode($result));
	}
	else{
		//set
		echo("set");
	}
}

function location($val){
	if(is_null($val)){
		//get
		$db = new my_connection();
		$result = $db->query("SELECT * FROM lokacje");
		$result = $db->fetchAssoc($result);

		echo(json_encode($result));
	}
	else{
		//set
		echo("set");
	}
}

?>