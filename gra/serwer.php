<?php
require 'class.PHPWebSocket.php';
// Funkcja będzie wywoływana przy każdej przychodzącej wiadomości
$conn = new mysqli("localhost", "root", "", "projekt");
$logged = false;
$player = "";
$postac = "";
$nazwa_potwora = "";
$potwor_do_zabicia = "";
$ilosc_potworow = 0;
$pieniadze = 0;
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
	global $nazwa_potwora;
	global $potwor_do_zabicia;
	global $ilosc_potworow;
	global $pieniadze;
	global $opis;
	global $kierunki;
	
    // wypisujemy w konsoli to, co przyszło
    printf("Client %s sent: %s\n",$clientID,$message);
    
    $pieces = explode(" ", $message);
    $sql = "SELECT haslo FROM Gracze where login = '$pieces[1]'";
    $result = $conn->query($sql);
    if($pieces[0] == "log" && $pieces[1] != "" && $result->num_rows == 1) { // zaloguj
        $row = $result->fetch_assoc();
        if($row["haslo"] == $pieces[2]) {
            $Server->wsSend($clientID, "true");
            $player = $pieces[1];
			$sql = "select nazwa from Postacie where login = '$pieces[1]'";
			$result = $conn->query($sql);
			if($result->num_rows > 0) {
				$Server->wsSend($clientID, "Najpierw wybierz postać poleceniem 'postac ...'\n\nTwoje postacie to:");
				while($row = $result->fetch_assoc()) {
					$Server->wsSend($clientID, "".$row['nazwa']."");
				}
			}
			else{
				$Server->wsSend($clientID, "Nie posiadasz żadnych postaci. Stwórz nową poleceniem 'postac ...'");
			}
        }
        else {
            $Server->wsSend($clientID, "false");
        }
    }
    else if($pieces[0] == "log") {
        $Server->wsSend($clientID, "false");
    }
    else if($pieces[0] == "postac" && !isset($pieces[1])) { // wybierz postac
        $Server->wsSend($clientID, "Po poleceniu 'postac' wpisz nazwę postaci, którą chcesz stworzyć lub wybierz już istniejącą");
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
			$sql = "select opis, x, y from Lokacje where id_lokacji = '$id_lok'";
			$result = $conn->query($sql)->fetch_assoc();
			$x = $result['x'];
			$y = $result['y'];
			$kierunki = mozliweKierunki($conn, $x, $y);
            $Server->wsSend($clientID, "Wybrałeś postać ".$pieces[1]);
			$Server->wsSend($clientID, "Jesteś w ".$result['opis']."");
			$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
        }
        else if($jest)
            $Server->wsSend($clientID, "Podana nazwa już istnieje.");
        else {
            $postac = $pieces[1];
            $x = 0;
            $y = 0;
			$sql = "select max(id_statystyki) as max_stat from Postacie";
			$result = $conn->query($sql)->fetch_assoc();
			$id_stat = $result["max_stat"] + 1;
            $sql = "insert into Postacie (login, id_lokacji, id_statystyki, nazwa) values ('$player', 1, $id_stat, '$postac')";
            $conn->query($sql);
			$sql = "select id_postaci from Postacie where nazwa = '$postac'";
			$result = $conn->query($sql)->fetch_assoc();
			$id_pos = $result['id_postaci'];
			$sql = "insert into Ekwipunek (id_postaci, pieniadze) values ('$id_pos', '10')";
			$conn->query($sql);
			$sql = "insert into Statystyka (id_statystyki, atak, obrona, hp) values ($id_stat, 30, 30, 100)";
			$conn->query($sql);
			$sql = "select opis from Lokacje where id_lokacji = '1'";
			$result = $conn->query($sql)->fetch_assoc();
			$kierunki = mozliweKierunki($conn, $x, $y);
            $Server->wsSend($clientID, "Stworzyłeś i wybrałeś postać $postac");
			$Server->wsSend($clientID, "Jesteś w ".$result['opis']."");
			$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
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
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
				$kierunki = mozliweKierunki($conn, $x, $y);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				if($id_potw != 0){
					$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
					$nazwa_potwora = $nazwa;
				}
				else
				    $nazwa_potwora = "";
				$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
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
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
				$kierunki = mozliweKierunki($conn, $x, $y);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				if($id_potw != 0){
					$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
					$nazwa_potwora = $nazwa;
				}
				else
				    $nazwa_potwora = "";
				$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
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
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
				$kierunki = mozliweKierunki($conn, $x, $y);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				if($id_potw != 0){
					$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
					$nazwa_potwora = $nazwa;
				}
				else
				    $nazwa_potwora = "";
				$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
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
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
				$kierunki = mozliweKierunki($conn, $x, $y);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				if($id_potw != 0){
					$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
					$nazwa_potwora = $nazwa;
				}
				else
				    $nazwa_potwora = "";
				$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
			}
        }
        else
            $Server->wsSend($clientID, "Najpierw wybierz postać poleceniem: postac ...");
    }
    else if($pieces[0] == "walcz") {
        if($nazwa_potwora == "")
            $Server->wsSend($clientID, "W tym miejscu nie ma żadnego potwora");
        else if($nazwa_potwora == $pieces[1]) {
            $Server->wsSend($clientID, "Teraz walczysz");
            $sql = "select atak, obrona, hp from Statystyka join Postacie using(id_statystyki) where nazwa = '$postac'";
            $result = $conn->query($sql)->fetch_assoc();
            $sql = "select atak, obrona, hp from Statystyka join Potwory using(id_statystyki) where nazwa = '$nazwa_potwora'";
            $result2 = $conn->query($sql)->fetch_assoc();
            if($result["atak"] - $result2["obrona"] > 0)
                $moja_sila = $result["atak"] - $result2["obrona"];
            else
                $moja_sila = 1;
            if($result2["atak"] - $result["obrona"] > 0)
                $sila = $result2["atak"] - $result["obrona"];
            else
                $sila = 1;
            $moje_hp = $result["hp"];
            $hp = $result2["hp"];
            while($moje_hp > 0 && $hp > 0) {
                $hp -= $moja_sila;
                if($hp > 0)
                    $moje_hp -= $sila;
            }
            if($moje_hp > 0) {
                $Server->wsSend($clientID, "Pokonałeś $nazwa_potwora");
                $Server->wsSend($clientID, "Twoje hp wynosi $moje_hp");
                $sql = "update Ekwipunek set pieniadze = pieniadze + (select pieniadze from Potwory where nazwa = '$nazwa_potwora') where id_postaci = (select id_postaci from Postacie where nazwa = '$postac')";
                $conn->query($sql);
                $sql = "update Statystyka set hp = $moje_hp where id_statystyki= (select id_statystyki from Postacie where nazwa='$postac')";
                $conn->query($sql);
                $sql = "update Statystyka set atak = atak+5, obrona = obrona+5 where id_statystyki = (select id_statystyki from Postacie where nazwa='$postac')";
                $conn->query($sql);
                if($potwor_do_zabicia == $nazwa_potwora) {
                    $ilosc_potworow--;
                    echo "Pomniejszam";
                    if($ilosc_potworow == 0) {
                        echo "Wygrałeś";
                        $potwor_do_zabicia = "";
                        $Server->wsSend($clientID, "Ukończyłeś misję. Dostajesz pieniądze: $pieniadze");
                        $sql = "update Postacie set pieniadze=pieniadze+$pieniadze where id_postaci= (select id_postaci from Postacie where nazwa='$postac')";
                        $conn->query($sql);
                    }
                }
				$nazwa_potwora = "";
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
            }
            else {
                $Server->wsSend($clientID, "Przegrałeś. Wracasz do lokacji startowej");
                $x = 0;
                $y = 0;
                $sql = "update Postacie set id_lokacji = 1 where nazwa = '$postac'";
                $conn->query($sql);
                $sql = "update Statystyka set hp=100 where id_statystyki = (select id_statystyki from Postacie where nazwa='$postac'";
                $conn->query($sql);
                $nazwa_potwora = "";
				list($opis, $id_potw, $nazwa) = wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
				$kierunki = mozliweKierunki($conn, $x, $y);
				$Server->wsSend($clientID, "Jesteś w ".$opis."");
				$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
            }
        }
        else if(!isset($pieces[1]))
            $Server->wsSend($clientID, "Po poleceniu 'walcz' podaj nazwę potwora, z którym chcesz walczyć");
        else
            $Server->wsSend($clientID, "Tutaj nie ma takiego potwora");
    }
}

function wejscieDoLokacji($conn, $x, $y, $postac, $clientID) {
    global $Server;
    global $potwor_do_zabicia;
    global $ilosc_potworow;
    global $pieniadze;
    
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
	$sql = "select nazwa, zleceniodawca from NPC join Lokacje using(id_lokacji) where x=$x and y=$y";
	$result = $conn->query($sql);
	if($result->num_rows > 0) {
	    $npc = $row["nazwa"];
	    $row = $result->fetch_assoc();
	    $Server->wsSend($clientID, "Znajduje się tu również NPC: $npc");
	    if($row["zleceniodawca"]) {
	        $sql = "select * from Misje join Potwory on id_potwora_do_zabicia=id_potwora";
	        $result = $conn->query($sql)->fetch_assoc();
	        $Server->wsSend($clientID, "Masz misję: ".$result["opis"].", ".$result["iloscDoZabicia"]." potworów o nazwie ".$result["nazwa"].", pieniadze ".$result["pieniadze"]);
	        $potwor_do_zabicia = $result["nazwa"];
	        $ilosc_potworow = $result["iloscDoZabicia"];
	        $pieniadze = $result["pieniadze"];
        }
    }
	return array($opis, $id_potw, $nazwa);
}

function mozliweKierunki($conn, $x, $y) {
	$kierunki = "";
	if($conn->query("select * from Lokacje where x = $x and y = $y + 1")->fetch_assoc()){
		$kierunki .= "n, ";
	}
	if($conn->query("select * from Lokacje where x = $x + 1 and y = $y")->fetch_assoc()){
		$kierunki .= "e, ";
	}
	if($conn->query("select * from Lokacje where x = $x and y = $y - 1")->fetch_assoc()){
		$kierunki .= "s, ";
	}
	if($conn->query("select * from Lokacje where x = $x - 1 and y = $y")->fetch_assoc()){
		$kierunki .= "w, ";
	}
	$kierunki = substr($kierunki, 0, -2);
	return $kierunki;
}
// Tworzymy klasę, podłączamy naszą funckję i uruchamiamy serwer 
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->wsStartServer('localhost', 8080);
?>