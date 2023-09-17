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
    <?php
        $username = $password = "";

        $valid = false;
        $admin = false;

        if ($_SERVER["REQUEST_METHOD"] == "POST"){
            $username = test_input($_POST["username"]);
            $password = test_input($_POST["password"]);
        }


        function test_input($input){
            $input = trim($input);
            $input = htmlspecialchars($input);
            return $input;
        }



        $connection = mysqli_connect("localhost", "root", "", "shop01");

        if (!$connection){
            die("Connection failed: ".mysqli_connect_error());
        }

        $tmp = $connection->query("SELECT * FROM users WHERE username='$username'");

        if (mysqli_num_rows($tmp) == 0){
            $valid = false;
        }else{
            $result = $tmp->fetch_assoc();
            if ($result["password"] == md5($password)){
                $valid = true;
                if ($result["role"] == 0){
                    $admin = true;
                }
            }else{
                $valid = false;
            }
        }
    ?>

    <h1>
        Ouput:
        <?php
        mysqli_close($connection); 
        if ($valid){
            if ($admin){
                echo "Welcome, Admin";
                header('Refresh:5; URL=/demo1/admincp/admin.php');
            }else{
                echo "Login successful";
            }
        }else{
            echo "User not found";
            header('Refresh:5; URL=/demo1/');
        }
        ?>
    </h1>
</body>
</html>