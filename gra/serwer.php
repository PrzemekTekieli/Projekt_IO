<?php
require 'class.PHPWebSocket.php';
// Funkcja będzie wywoływana przy każdej przychodzącej wiadomości
function wsOnMessage($clientID, $message, $messageLength, $binary) {
    global $Server;
    // wypisujemy w konsoli to, co przyszło
    printf("Client %s sent: %s\n",$clientID,$message);
    
    $pieces = explode(" ", $message);
    if($pieces[0] == "log" && $pieces[1] != "" && file_exists("accounts/".$pieces[1]) && file_get_contents("accounts/".$pieces[1]."/password") == $pieces[2]) {
        $Server->wsSend($clientID, "true");
        printf("true\n");
    }
    else {
        $Server->wsSend($clientID, "false");
        printf("false\n");
    }
}
// Tworzymy klasę, podłączamy naszą funckję i uruchamiamy serwer 
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->wsStartServer('localhost', 8080);
?>