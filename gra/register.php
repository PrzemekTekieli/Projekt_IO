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
                echo "Login może składać się tylko z liter i cyfr";
            if($_POST["haslo1"] != $_POST["haslo2"])
                echo "Podane hasła nie są takie same";
        }
        else
        {
            if(!file_exists("accounts/" . $_POST["login"]))
            {
                mkdir("accounts/" . $_POST["login"]);
                $file = fopen("accounts/".$_POST["login"]."/password", "w");
                fwrite($file, $_POST["haslo1"]);
                fclose($file);
                echo "Założono konto";
            }
            else
                echo "Konto już istnieje";
        }
        
    ?>
    </body>
</html>