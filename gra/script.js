function handle(e) {
    if (e.keyCode === 13) { // jeżeli enter
        document.getElementById('t1').value = document.getElementById('t1').value + '>' + document.getElementById('t2').value + '\n';
        document.getElementById('t2').value = '';
        document.getElementById('t1').scrollTop =  document.getElementById('t1').scrollHeight; // przewiń
        return false; // żeby formularz nie został wysłany
    }
    else {
        return false;
    }
}