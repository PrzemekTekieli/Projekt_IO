<?php

function player($val){
	//gdy $val jest ustawione, to znaczy że chcemy zapisać to w bazie danych
	//jeśli nie jest, to znaczy że chcemy odczytać
	$db = new my_connection();
	if(is_null($val)){
		//get
		$result = $db->query("SELECT * FROM gracze ORDER BY id_gracza");
		//$result = $db->fetchAll($result);
		$result = $db->fetchAssoc($result);

		echo(json_encode($result));
	}
	else{
		//set
		$val = json_decode($val);
		$arr =  (array) $val;
		
		$query = "UPDATE gracze SET ";
		foreach($arr as $key => $value){
			$query .= "$key = '$value',";
		}
		$query = substr($query, 0, -1) . " WHERE id_gracza = 1";
		$db->query($query);
		
		echo(json_encode("Zapisano zmiany"));
	}
}

function character($val){
	$db = new my_connection();
	if(is_null($val)){
		//get
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
	$db = new my_connection();
	if(is_null($val)){
		//get
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