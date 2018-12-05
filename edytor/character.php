<?php
//require('handler.php');
//przerzucony handel do functions.php, a może warto require if not decladed czy coś takiego?

//nazwa klasy musi być zgodna z command w pliku index.html
//nieco bardziej skomplikowana klasa niż player
class character extends handler{
	private $tab_name = 'postacie p';
	private $foreign_tab = 'gracze g';
	private $foreign_key = 'id_gracza';
	
	public function select(){//fajnie by też było to bardziej sparametryzować też, zamiast na stałe
		$result = $this->db->query("SELECT p.*, g.login AS gracz FROM $this->tab_name INNER JOIN $this->foreign_tab USING($this->foreign_key) ORDER BY p.nazwa");
		//$result = $db->fetchAll($result);
		$result = $this->db->fetchAssoc($result);

		echo(json_encode($result));
	}
	
	public function insert($val){
		$val = json_decode($val);
		$arr =  (array) $val;
		
		$query = "";//ignorujemy pierwszą kolumnę, czyli id
		$first = true;
		foreach($arr as $key => $value){
			if($first == true){
				$first = false;
			}
			else{
				$query .= "'$value',";
			}
		}
		$query = substr($query, 0, -1);
				
		$this->_insert($this->tab_name,$query);
		
		echo(json_encode("Zapisano zmiany"));
	}
	
	public function update($val){
		$val = json_decode($val);
		$arr =  (array) $val;
		
		$where = '';
		$query = '';
		foreach($arr as $key => $value){
			if($where == ''){
				$where = "$key = '$value'";
			}
			else{
				$query .= "$key = '$value',";
			}
		}
		$query = substr($query, 0, -1);
				
		$this->_update($this->tab_name,$query,$where);
		
		echo(json_encode("Zapisano zmiany"));
	}
	
	public function delete($where){
		$this->_delete($this->tab_name,$where);
		
		echo(json_encode("Zapisano zmiany"));
	}
}

?>