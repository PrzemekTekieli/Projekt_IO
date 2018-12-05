<html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
    <?php
        function test_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
        
        $name = test_input($_POST["login"]);
        if(!preg_match("/^[a-zA-Z0-9]*$/",$name) || $_POST["haslo1"] != $_POST["haslo2"])
        {
            if(!preg_match("/^[a-zA-Z0-9]*$/",$name))
                echo 'Login może składać się tylko z liter i cyfr<br><a href="register_form.html">Powróć</a>';
            if($_POST["haslo1"] != $_POST["haslo2"])
                echo 'Podane hasła nie są takie same<br><a href="register_form.html">Powróć</a>';
        }
        else
        {
            $conn = new mysqli("localhost", "root", "", "projekt");
            if ($conn->connect_error)
                die("Connection failed: " . $conn->connect_error);
            if(mysqli_num_rows($conn->query("select login from Gracze where login='".$_POST["login"]."'")) == 0)
            {
                $sql  = "INSERT INTO Gracze VALUES ('".$_POST["login"]."', '".$_POST["haslo1"]."')";
                $conn->query($sql);
                echo 'Założono konto<br><a href="index.html">Zaloguj</a>';
            }
            else
                echo 'Konto już istnieje<br><a href="register_form.html">Powróć</a>';
        }
        
    ?>
    </body>
</html>