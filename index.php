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
<form method="post" action="validation.php">
    Username: <input type="text" name="username"><br>
    Password: <input type="password" name="password"> <br>
<input type="submit" value="Sign in">
<a href="new_user.php">
    <input type="button" value="Sign up">
</a>
</form>

</body>
</html>