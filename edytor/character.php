<?php
//require('handler.php');
//przerzucony handel do functions.php, a może warto require if not decladed czy coś takiego?

//nazwa klasy musi być zgodna z command w pliku index.html
//nieco bardziej skomplikowana klasa niż player
class character extends handler{
	private $tab_name = 'postacie';
	private $f_tabs = ['gracze g','lokacje l'];
	private $f_keys = ['id_gracza','id_lokacji'];
	private $f_names = [['g.login'],['l.x','l.y']];
	private $f_visible = ['gracz','lokacja'];
	
	public function select(){//fajnie by też było to bardziej sparametryzować też, zamiast na stałe
		//$result = $this->db->query("SELECT * FROM $this->tab_name ORDER BY p.nazwa");
		$result = $this->db->query("SELECT id_postaci, id_gracza, id_lokacji, nazwa FROM $this->tab_name ORDER BY nazwa");
		//$result = $db->fetchAll($result);
		$result = $this->db->fetchAssoc($result);
		
		//tab - ponieważ te bardziej skomplikowane selecty będą miały atrybuty do selecta
		$tab['result'] = $result;
		
		$options = $this->select_foreign();
		$tab['options'] = $options;
		
		echo(json_encode($tab));
	}
	
	private function select_foreign(){
		$options = array();
		for($i = 0; $i < sizeof($this->f_tabs); $i++){
			$name = "(" . implode(",",$this->f_names[$i]) . ")";
			$visible = $this->f_visible[$i];
			$table = $this->f_tabs[$i];
			$key = $this->f_keys[$i];
			$result = $this->db->query("SELECT $key, $name FROM $table ORDER BY $name");
			$index = $this->db->fetchColumn($result);
			$res = $this->db->fetchColumn($result,1);
			//na ostatnim indeksie mamy nazwy
			$res[] = $visible;
			$arr['index'] = $index;
			$arr['name'] = $res;
			$options[] = $arr;
		}
		return $options;
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
				//TODO usunąć, i zrobić id_statystyki normalne
				if($key == 'nazwa'){
					$query .= "'1',";
				}
				
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