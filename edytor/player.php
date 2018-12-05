<?php
//nazwa klasy musi być zgodna z command w pliku index.html
//fajnie by było gdyby to jakoś jeszcze w klasę abstrakcyjną zmienić, bo kopiuj wklej nowych klas jest głupie
class player extends handler{
	private $tab_name = 'gracze';
	
	public function select(){
		$result = $this->db->query("SELECT * FROM $this->tab_name ORDER BY login");
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