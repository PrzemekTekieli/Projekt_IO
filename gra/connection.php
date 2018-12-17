<?php
class my_result{
    private $result;
    public $num_rows;

    public function __construct($result){
        $this->result = $result;
        $this->num_rows = pg_num_rows($this->result);
    }
    public function fetch_assoc(){
        return pg_fetch_assoc($this->result);
    }
};

//klasa tworzy połączenie z bazą danych, i udostępnia łatwy dostęp do jej operacji
class my_connection{
	private $conn;
	
	//w konstruktorze tworzymy połączenie z bazą danych
	public function __construct(){
		$this->conn = pg_connect("host=localhost dbname=test user=root password=root");
	}
	
	//używając naszego połączenia, wysyłamy otrzymane zapytanie do postgresa i zwracamy wynik
	public function query($query){
		//return pg_query($this->conn, $query);
        return new my_result(pg_query($this->conn, $query));
	}
	
	//z wejściowych danych (rezultatu powyższego zapytania tworzymy zwykłą tablicę (indeksy 0-n)
	public function fetchAll($result){
		$arr = array();
		while ($row = pg_fetch_row($result)) {
			$arr[] = $row;
		}
		return $arr;
	}
	
	//z wejściowych danych tworzymy obiekt (zamiast indeksów odwołujemy się do nazw kolumn)
	public function fetchAssoc($result){
		return pg_fetch_all($result);
	}
}

?>
