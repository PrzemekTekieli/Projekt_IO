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

function load() {
    document.getElementById('t1').value='';
    socket = new WebSocket('ws://localhost:8080');
    socket.addEventListener('message', function(event) {
        document.getElementById('t1').value += event.data + '\n';
    }); 
}