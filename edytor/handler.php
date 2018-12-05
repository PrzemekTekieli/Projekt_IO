<?php

//abstrakcyjna klasa po której powinny dziedzić wszystkie następne
abstract class handler{
	
	protected $db;
	
	//w konstruktorze ustawiamy połączenie do bazy danych
	public function __construct(){
		$this->db = new my_connection();
	}
	
	//wstawia nowy rekord do tabeli
	protected function _insert($table, $values){
		$this->db->query("INSERT INTO $table VALUES(default,$values)");
	}
	
	//aktualizuje rekord
	protected function _update($table, $values ,$where){
		$this->db->query("UPDATE $table SET $values WHERE $where");
	}
	
	//usuwa rekord
	protected function _delete($table, $where){
		$this->db->query("DELETE FROM $table WHERE $where");
	}
	
	//każda klasa powinna mieć funkcje pobierającą rekordy z bazy, i modyfikującą bazę
	abstract public function select();
	abstract public function insert($val);
	abstract public function update($val);
	abstract public function delete($where);
}

?>