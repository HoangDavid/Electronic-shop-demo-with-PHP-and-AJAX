<?php
include("config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post">
        New username: <input type="text" name="new_username"><br>
        New password: <input type="password" name="new_password"> <br>
        Re-enter password: <input type="password" name="reenter_password"> <br>

        <input type="submit" name="Signup" value="Sign up">
    </form>


<?php

    if (isset($_POST["Signup"])){
        $new_user = $_POST["new_username"];
        $new_pass = md5($_POST["new_password"]);

        if ($new_user == ""){
            echo "please enter a valid username";
            exit;
        }else if (preg_match("/^[A-Z a-z 0-9]{2,}$/", $new_user) == 0){
            echo "username format is not valid. Username must begin with a letter and must not contain any puctuations";
            exit;
        }

        if ($new_pass == ""){
            echo "please enter a valid password";
            exit;
        }else if ($new_pass != md5($_POST["reenter_password"])){
            echo "passwords are not matched";
            exit;
        }

        $connection = mysqli_connect("localhost", "root", "", "shop01");

        // check for connection status
        if (!$connection){
            die("Connection failed: ".mysqli_connect_error());
        }

        $tmp = mysqli_num_rows($connection->query("SELECT * FROM users WHERE username = '$new_user';"));
        if ($tmp == 0){
            $sql = "INSERT INTO users (username, password, status) VALUES ('$new_user', '$new_pass', 1)";
            $connection->query($sql);
            mysqli_close($connection);
    
            echo "You have succesfully signed up. Heading back to login page...";
            header('Refresh:5; URL=/demo1/');
        }else{
            mysqli_close($connection);
            echo "Username already existed. Please use another one";
        }

    }
?>
</form>
</body>
</html>