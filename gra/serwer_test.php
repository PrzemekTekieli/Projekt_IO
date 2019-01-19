<?php
require 'class.PHPWebSocket.php';
require 'connection.php';

$conn = new my_connection();
$logged = false;
$player = "";
$postac = "";
$nazwa_potwora = "";
$potwor_do_zabicia = "";
$ilosc_potworow = 0;
$pieniadze_nagr = 0;
$docelowa_lokacja = 0;
    
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
	global $docelowa_lokacja;
	global $pieniadze_nagr;
	global $opis;
	global $kierunki;
	global $npc;
	global $zleceniodawca;
	global $id_misji;
	
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
			$sql = "select nazwa from Postacie where id_gracza = (select id_gracza from Gracze where login = '$pieces[1]')";
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
        $sql3 = "select g.login, p.nazwa from Postacie p join Gracze g using (id_gracza)";
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
            $Server->wsSend($clientID, "Wybrałeś postać ".$pieces[1]);
			wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
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
			$sql = "insert into Statystyka (id_statystyki, atak, obrona, hp) values ($id_stat, 10, 10, 100)";
			$conn->query($sql);
            $sql = "insert into Postacie (id_gracza, id_lokacji, id_statystyki, nazwa) values ((select id_gracza from Gracze where login = '$player'), 1, $id_stat, '$postac')";
            $conn->query($sql);
			$sql = "select id_postaci from Postacie where nazwa = '$postac'";
			$result = $conn->query($sql)->fetch_assoc();
			$id_pos = $result['id_postaci'];
			$sql = "insert into Ekwipunek (id_postaci, pieniadze) values ('$id_pos', '10')";
			$conn->query($sql);
			wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
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
				wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
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
				wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
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
				wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
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
				wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
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
                if($potwor_do_zabicia == $nazwa_potwora && $id_misji != 0) {
                    $ilosc_potworow--;
                    echo "Pomniejszam\n\n";
                    if($ilosc_potworow == 0) {
                        echo "Wygrałeś\n\n";
                        $potwor_do_zabicia = "";
                        $Server->wsSend($clientID, "Ukończyłeś misję. Dostajesz pieniądze: $pieniadze_nagr");
                        $sql = "update Ekwipunek set pieniadze=pieniadze+$pieniadze_nagr where id_postaci= (select id_postaci from Postacie where nazwa='$postac')";
                        $conn->query($sql);
						$id_misji = 0;
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
				wejscieDoLokacji($conn, $x, $y, $postac, $clientID);
            }
        }
        else if(!isset($pieces[1]))
            $Server->wsSend($clientID, "Po poleceniu 'walcz' podaj nazwę potwora, z którym chcesz walczyć");
        else
            $Server->wsSend($clientID, "Tutaj nie ma takiego potwora");
    }
    else if($pieces[0] == "wyloguj") {
		$sql = "select nazwa from Postacie where id_gracza = (select id_gracza from Gracze where login = '$player')";
		$result = $conn->query($sql);
		$Server->wsSend($clientID, "Twoje postacie to:");
		while($row = $result->fetch_assoc()) {
			$Server->wsSend($clientID, "".$row['nazwa']."");
		}
	}
	else if($pieces[0] == "info") {
		$Server->wsSend($clientID, "Postać: ".$postac."");
		$sql = "select * from Statystyka where id_statystyki = (select id_statystyki from Postacie where nazwa = '$postac')";
		$result = $conn->query($sql)->fetch_assoc();
		$Server->wsSend($clientID, "Atak: ".$result['atak']."");
		$Server->wsSend($clientID, "Obrona: ".$result['obrona']."");
		$Server->wsSend($clientID, "Punkty zdrowia: ".$result['hp']."");
		$sql = "select pieniadze from Ekwipunek where id_postaci = (select id_postaci from Postacie where nazwa = '$postac')";
		$result = $conn->query($sql)->fetch_assoc();
		$Server->wsSend($clientID, "Pieniądze: ".$result['pieniadze']."");
	}
	else if($pieces[0] == "wez" && $pieces[1] == "misje") {
		if($zleceniodawca) {
			$docelowa_lokacja = 0;
			$potwor_do_zabicia = "";
			$sql = "select max(id_misji) as max_id from Misje";
			$result = $conn->query($sql)->fetch_assoc();
			$id_misji = rand(1, $result['max_id']);
			$sql = "select * from Misje where id_misji = '$id_misji'";
			$result = $conn->query($sql)->fetch_assoc();
			if($result['id_potwora_do_zabicia']) {
				$potw_id = $result["id_potwora_do_zabicia"];
				$sql2 = "select nazwa from Potwory where id_potwora = '$potw_id'";
				$result2 = $conn->query($sql2)->fetch_assoc();
				$Server->wsSend($clientID, "Masz misję: ".$result["opis"].", pokonaj ".$result["ilosc_do_zabicia"]." potworów o nazwie ".$result2["nazwa"].", pieniadze ".$result["pieniadze"]."");
				$potwor_do_zabicia = $result2["nazwa"];
				$ilosc_potworow = $result["ilosc_do_zabicia"];
				$pieniadze_nagr = $result["pieniadze"];
			}
			else {
				$docelowa_lokacja = $result['id_docelowej_lokacji'];
				$sql2 = "select x,y from Lokacje where id_lokacji = '$docelowa_lokacja'";
				$result2 = $conn->query($sql2)->fetch_assoc();
				$Server->wsSend($clientID, "Masz misję: ".$result["opis"]." o współrzędnych x: ".$result2["x"].", y: ".$result2["y"].", pieniadze ".$result["pieniadze"]."");
				$pieniadze_nagr = $result["pieniadze"];
			}
		}
		else {
			$Server->wsSend($clientID, "W tej lokacji nie ma zleceniodawcy.");
		}
	}
	else if($pieces[0] == "trenuj") {
		if($npc != "") {
			$sql = "select pieniadze from Ekwipunek where id_postaci = (select id_postaci from Postacie where nazwa = '$postac')";
			$result = $conn->query($sql)->fetch_assoc();
			$moje_pieniadze = $result['pieniadze'];
			if($moje_pieniadze >= 100) {
				if($pieces[1] == "atak") {
					$sql = "update Statystyka set atak = atak+1 where id_statystyki = (select id_statystyki from Postacie where nazwa='$postac')";
					$conn->query($sql);
					$sql = "update Ekwipunek set pieniadze=pieniadze-100 where id_postaci = (select id_postaci from Postacie where nazwa='$postac')";
                    $conn->query($sql);
					$Server->wsSend($clientID, "".$npc." pokazał ci nowe ofensywne techniki walki. Zdobyłeś +1 do ataku.");
				}
				else if($pieces[1] == "obrona") {
					$sql = "update Statystyka set obrona = obrona+1 where id_statystyki = (select id_statystyki from Postacie where nazwa='$postac')";
					$conn->query($sql);
					$sql = "update Ekwipunek set pieniadze=pieniadze-100 where id_postaci = (select id_postaci from Postacie where nazwa='$postac')";
                    $conn->query($sql);
					$Server->wsSend($clientID, "".$npc." pokazał ci nowe defensywne techniki walki. Zdobyłeś +1 do obrony.");
				}
				else {
					$Server->wsSend($clientID, "Możesz trenować jedynie atak lub obronę.");
				}
			}
			else {
				$Server->wsSend($clientID, "Nie posiadasz wystarczającej ilości pieniędzy. Potrzebujesz 100 monet, by moć trenować.");
			}
		}
		else {
			$Server->wsSend($clientID, "W tej lokacji nie ma cię kto trenować.");
		}
	}
	else if($pieces[0] == "wylecz") {
		if($npc != "") {
			if(ctype_digit(strval($pieces[1]))) {
				$nowe_zycie = $pieces[1];
				if($nowe_zycie <= 100) {
					$sql = "select hp from Statystyka where id_statystyki = (select id_statystyki from Postacie where nazwa = '$postac')";
					$result = $conn->query($sql)->fetch_assoc();
					$moje_zycie = $result['hp'];
					if ($moje_zycie < $nowe_zycie) {
						$cena = ($nowe_zycie - $moje_zycie)*2;
						$sql = "select pieniadze from Ekwipunek where id_postaci = (select id_postaci from Postacie where nazwa = '$postac')";
						$result = $conn->query($sql)->fetch_assoc();
						if($result >= $cena) {
							$sql = "update Statystyka set hp = '$nowe_zycie' where id_statystyki = (select id_statystyki from Postacie where nazwa='$postac')";
							$conn->query($sql);
							$sql = "update Ekwipunek set pieniadze=pieniadze-'$cena' where id_postaci = (select id_postaci from Postacie where nazwa='$postac')";
							$conn->query($sql);
							$Server->wsSend($clientID, "Zostałeś wyleczony. Posiadasz ".$nowe_zycie." punktów zdrowia.");
						}
						else {
							$Server->wsSend($clientID, "Nie posiadasz wystarczającej ilości pieniędzy. Potrzebujesz 2 monety, na każdy wyleczony punkt zdrowia.");
						}
					}
					else {
						$Server->wsSend($clientID, "Już masz tyle zdrowia.");
					}
				}
				else {
					$Server->wsSend($clientID, "Możesz zostać wyleczony jedynie do 100 punktów zdrowia.");
				}
			}
			else {
				$Server->wsSend($clientID, "Po komendzie wylecz musisz podać liczbę naturalną nie większą niż 100, do której chcesz być wyleczony.");
			}
		}
		else {
			$Server->wsSend($clientID, "W tej lokacji nie ma cię kto wyleczyć.");
		}
	}
	else if($pieces[0] == "misja") {
		if($id_misji != 0) {
			$sql = "select * from Misje where id_misji = '$id_misji'";
			$result = $conn->query($sql)->fetch_assoc();
			if($result['id_potwora_do_zabicia']) {
				$Server->wsSend($clientID, "Masz misję: ".$result["opis"].", pokonaj ".$ilosc_potworow." potworów o nazwie ".$potwor_do_zabicia.", pieniadze ".$pieniadze_nagr."");
			}
			else {
				$sql2 = "select x,y from Lokacje where id_lokacji = '$docelowa_lokacja'";
				$result2 = $conn->query($sql2)->fetch_assoc();
				$Server->wsSend($clientID, "Masz misję: ".$result["opis"]." o współrzędnych x: ".$result2["x"].", y: ".$result2["y"].", pieniadze ".$result["pieniadze"]."");
			}
		}
		else {
			$Server->wsSend($clientID, "Nie masz obecnie żadnej misji.");
		}
	}
	else if($pieces[0] == "komendy") {
		$Server->wsSend($clientID, "postac ... - loguje na daną postać bądź tworzy nową, jeśli dana postać nie istnieje");
		$Server->wsSend($clientID, "n, e, s, w - poruszanie się postaci");
		$Server->wsSend($clientID, "walcz ... - rozpoczyna walkę z danym potworem");
		$Server->wsSend($clientID, "info - wyświetla informację o twojej postaci");
		$Server->wsSend($clientID, "wez misje - rozpoczyna nową misję, jeśli w lokacji jest zleceniodawca");
		$Server->wsSend($clientID, "trenuj ... - trenuje atak lub obronę, jeśli w lokacji jest NPC");
		$Server->wsSend($clientID, "misja - wyświetla obecną misję, jeśli istnieje");
		$Server->wsSend($clientID, "opis - wyświetla informacje o obecnej lokacji");
		$Server->wsSend($clientID, "wyloguj - wylogowuje obecną postać");
	}
	else if($pieces[0] == "opis") {
		$sql = "select opis from Lokacje where x = '$x' and y = '$y'";
		$result = $conn->query($sql)->fetch_assoc();
		$kierunki = mozliweKierunki($conn, $x, $y);
		$Server->wsSend($clientID, "Jesteś w ".$opis."");
		if($id_potw != 0){
			$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
		}
		if(npc != "") {
			if($zleceniodawca) {
				$Server->wsSend($clientID, "Znajduje się tu również NPC: $npc (zleceniodawca)");
			}
			else {
				$Server->wsSend($clientID, "Znajduje się tu również NPC: $npc");
			}
		}
		$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
	}
	else{
		$Server->wsSend($clientID, "Nie ma takiej komendy");
	}
}
function wejscieDoLokacji($conn, $x, $y, $postac, $clientID): void {
    global $Server;
	global $nazwa_potwora;
    global $potwor_do_zabicia;
    global $ilosc_potworow;
    global $pieniadze_nagr;
	global $opis;
	global $kierunki;
	global $npc;
	global $zleceniodawca;
	global $docelowa_lokacja;
	global $id_potw;
    
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
	$kierunki = mozliweKierunki($conn, $x, $y);
	if($id_lok == $docelowa_lokacja) {
        echo "Wygrałeś\n\n";
        $docelowa_lokacja = 0;
		$id_misji = 0;
        $Server->wsSend($clientID, "Wypełniłeś misję. Dostajesz pieniądze: $pieniadze_nagr");
        $sql = "update Ekwipunek set pieniadze=pieniadze+$pieniadze_nagr where id_postaci= (select id_postaci from Postacie where nazwa='$postac')";
        $conn->query($sql);
    }
	$Server->wsSend($clientID, "Jesteś w ".$opis."");
	if($id_potw != 0){
		$Server->wsSend($clientID, "Znajduje się tu również: ".$nazwa."");
		$nazwa_potwora = $nazwa;
	}
	else {
		$nazwa_potwora = "";
	}
	$sql = "select nazwa, zleceniodawca from NPC n join Lokacje l using(id_lokacji) where l.x=$x and l.y=$y";
	$result = $conn->query($sql);
	if($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$npc = $row["nazwa"];
	    if($row["zleceniodawca"]) {
			$zleceniodawca = true;
			$Server->wsSend($clientID, "Znajduje się tu również NPC: $npc (zleceniodawca)");
        }
		else {
			$zleceniodawca = false;
			$Server->wsSend($clientID, "Znajduje się tu również NPC: $npc");
		}
    }
	else {
		$npc = "";
	}
	$Server->wsSend($clientID, "Możliwe kierunki: ".$kierunki."");
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
