<?php
require 'class.PHPWebSocket.php';
// Funkcja będzie wywoływana przy każdej przychodzącej wiadomości
$conn = new mysqli("localhost", "root", "", "projekt");
$logged = false;
$player = "";
$postac = "";
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);
    
function wsOnMessage($clientID, $message, $messageLength, $binary) {
    global $Server;
    global $conn;
    global $logged;
    global $player;
    global $postac;
	global $x;
	global $y;
    // wypisujemy w konsoli to, co przyszło
    printf("Client %s sent: %s\n",$clientID,$message);
    
    $pieces = explode(" ", $message);
    $sql2 = "SELECT haslo FROM Gracze where login = '$pieces[1]'";
    $result = $conn->query($sql2);
    if($pieces[0] == "log" && $pieces[1] != "" && $result->num_rows == 1) { // zaloguj
        $row = $result->fetch_assoc();
        if($row["haslo"] == $pieces[2]) {
            $Server->wsSend($clientID, "true");
            printf("true\n");
            $player = $pieces[1];
			$sql = "select nazwa from Postacie where login = '$pieces[1]'";
			$result = $conn->query($sql);
			if($result->num_rows > 0) {
				$Server->wsSend($clientID, "Twoje postacie to:");
				while($row = $result->fetch_assoc()) {
					$Server->wsSend($clientID, "".$row['nazwa']."");
				}
			}
			else{
				$Server->wsSend($clientID, "Nie posiadasz żadnych postaci.");
			}
        } else {
            $Server->wsSend($clientID, "false");
            printf("false\n");
        }
    }
    else if($pieces[0] == "log") {
        $Server->wsSend($clientID, "false");
        printf("false\n");
    }
    else if($pieces[0] == "postac" && !isset($pieces[1])) { // wybierz postac
        $Server->wsSend($clientID, "Po poleceniu 'postac' wpisz nazwę postaci, którą chcesz stworzyć lub wybierz już istniejącą");
        printf("Po poleceniu 'postac' wpisz nazwę postaci, którą chcesz stworzyć lub wybierz już istniejącą\n");
    }
    else if($pieces[0] == "postac") {
        $sql3 = "select login, nazwa from Postacie";
        $result = $conn->query($sql3);
        $jest = false;
        $gracz = "";
        if($result != false)
            while($row = $result->fetch_assoc()) {
                if($row["nazwa"] == $pieces[1]) {
                    $jest = true;
                    $gracz = $row["login"];
                    break;
                }
            }
        if($jest && $gracz == $player) {
            $postac = $pieces[1];
			$sql = "select id_lokacji from Postacie where nazwa = '$postac'";
			$result = $conn->query($sql)->fetch_assoc();
			$id_lok = $result['id_lokacji'];
			$sql = "select x,y,opis from Lokacje where id_lokacji = '$id_lok'";
			$result = $conn->query($sql)->fetch_assoc();
			$x = $result['x'];
			$y = $result['y'];
            $Server->wsSend($clientID, "Wybrałeś postać ".$pieces[1]);
			$Server->wsSend($clientID, "Jesteś w ".$result['opis']."");
        }
        else if($jest)
            $Server->wsSend($clientID, "Podana nazwa już istnieje.");
        else {
            $postac = $pieces[1];
			$x = 0;
			$y = 0;
            $sql = "insert into Postacie (login, id_lokacji, id_statystyki, nazwa) values ('$player', '1', '1', '$postac')";
			$sql = "select id_postaci from Postacie where nazwa = '$postac'";
			$result = $conn->query($sql)->fetch_assoc();
			$id_pos = $result['id_postaci'];
			$sql = "insert into Ekwipunek (id_postaci, pieniadze) values ('$id_pos', '10')";
			$sql = "select opis from Lokacje where id_lokacji = '1'";
			$result = $conn->query($sql)->fetch_assoc();
            $Server->wsSend($clientID, "Stworzyłeś i wybrałeś postać $postac");
			$Server->wsSend($clientID, "Jesteś w ".$result['opis']."");
        }
    }
    else if($pieces[0] == "n") {
        if($postac != "") {
			$y = $y + 1;
			$sql = "select * from Lokacje where x = '$x' and y = '$y'";
			$result = $conn->query($sql)->fetch_assoc();
			if(!$result){
				$Server->wsSend($clientID, "Nie możesz tam pójść");
				$y = $y - 1;
			}
			else{
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				if($id_potw != 0){
					$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
				}
			}
        }
        else
            $Server->wsSend($clientID, "Najpierw wybierz postać poleceniem: postac ...");
    }
    else if($pieces[0] == "e") {
        if($postac != "") {
			$x = $x + 1;
			$sql = "select * from Lokacje where x = '$x' and y = '$y'";
			$result = $conn->query($sql)->fetch_assoc();
			if(!$result){
				$Server->wsSend($clientID, "Nie możesz tam pójść");
				$x = $x - 1;
			}
			else{
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				if($id_potw != 0){
					$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
				}
			}
        }
        else
            $Server->wsSend($clientID, "Najpierw wybierz postać poleceniem: postac ...");
    }
    else if($pieces[0] == "s") {
        if($postac != "") {
			$y = $y - 1;
			$sql = "select * from Lokacje where x = '$x' and y = '$y'";
			$result = $conn->query($sql)->fetch_assoc();
			if(!$result){
				$Server->wsSend($clientID, "Nie możesz tam pójść");
				$y = $y + 1;
			}
			else{
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				if($id_potw != 0){
					$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
				}
			}
        }
        else
            $Server->wsSend($clientID, "Najpierw wybierz postać poleceniem: postac ...");
    }
    else if($pieces[0] == "w") {
        if($postac != "") {
			$x = $x - 1;
			$sql = "select * from Lokacje where x = '$x' and y = '$y'";
			$result = $conn->query($sql)->fetch_assoc();
			if(!$result){
				$Server->wsSend($clientID, "Nie możesz tam pójść");
				$x = $x + 1;
			}
			else{
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				if($id_potw != 0){
					$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
				}
			}
        }
        else
            $Server->wsSend($clientID, "Najpierw wybierz postać poleceniem: postac ...");
    }
}

function wejscieDoLokacji($conn, $x, $y, $postac) {
	$sql = "select id_lokacji, opis from Lokacje where x = '$x' and y = '$y'";
	$result = $conn->query($sql)->fetch_assoc();
	$id_lok = $result['id_lokacji'];
	$opis = $result['opis'];
	$sql = "update Postacie set id_lokacji = '$id_lok' where nazwa = '$postac'";
	$conn->query($sql);
	$sql = "select id_potwora, procent_odrodzenia from Wystapienia where id_lokacji = '$id_lok'";
	$result = $conn->query($sql);
	$id_potw = 0;
	$nazwa = "";
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			if(rand(0,99) < $row['procent_odrodzenia']){
				$id_potw = $row['id_potwora'];
				break;
			}
		}
	}
	if($id_potw != 0){
		$sql = "select nazwa from Potwory where id_potwora = '$id_potw'";
		$result = $conn->query($sql)->fetch_assoc();
		$nazwa = $result['nazwa'];
	}
	return array($opis, $id_potw, $nazwa);
}

// Tworzymy klasę, podłączamy naszą funckję i uruchamiamy serwer 
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->wsStartServer('localhost', 8080);
?>
