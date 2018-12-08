var socket;

function handle(e) {
    if (e.keyCode === 13) { // jeżeli enter
        e.preventDefault(); // zeby nie dodawalo entera
        document.getElementById('t1').value += '>' + document.getElementById('t2').value + '\n';
        socket.send(document.getElementById('t2').value);
        document.getElementById('t2').value = '';
        document.getElementById('t1').scrollTop =  document.getElementById('t1').scrollHeight; // przewiń
        return false; // żeby formularz nie został wysłany
    }
    else {
        return false;
    }
}

function logHandle(e) {
    if(e.keyCode === 13) {
        log();
    }
}

function load() {
    document.getElementById('t1').value='';
     
}

function log() {
    socket = new WebSocket('ws://localhost:8080');
    socket.addEventListener('open', function (event) { // trzeba poczekac az polaczenie bedzie otwarte
        socket.send('log ' + document.getElementById('login').value + " " + document.getElementById('haslo').value);
    });
    socket.addEventListener('message', function(event) {
        if(event.data == "true") {
            document.getElementById('body').innerHTML = '<div id="div1"><textarea readonly id="t1"></textarea><textarea rows="1" id="t2" placeholder="Tutaj wpisz polecenie..." onkeypress="handle(event);"></textarea></div>';
        }
        else if(event.data == "false")
            document.getElementById('body').innerHTML = '<table><tr><td>Login:</td><td><input type="text" id="login"></td></tr><tr><td>Hasło:</td><td><input type="password" id="haslo"> <input type="button" value="Zaloguj" onclick="log()"> <a href="register_form.html">Zarejestruj się</a></td></tr></table>Nieprawidłowe login i/lub hasło';
        else {
            document.getElementById('t1').value += event.data+"\n";
            document.getElementById('t1').scrollTop =  document.getElementById('t1').scrollHeight; // przewiń
        }
    });
}