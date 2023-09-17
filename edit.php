<?php
include("config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
        $user_id = $username = $status = $role = $password = "";

        $connection = mysqli_connect("localhost", "root", "", "shop01");
            // check for connection status
        if (!$connection){
            die("Connection failed: ".mysqli_connect_error());
        }



        if (isset($_POST["edit_user"])){
            $user_id = htmlspecialchars($_POST["user_id"]);

            $sql = "SELECT * FROM users WHERE id = $user_id";
            $data = $connection->query($sql);

            $tmp = $data->fetch_assoc();

            $username = $tmp["username"];
            $status = $tmp["status"];
            $role = $tmp["role"];
            $password = $tmp["password"];

        }else if (isset($_POST["update"])){

            $username = $_POST["username"];
            $status = $_POST["status"];
            $role = $_POST["role"];
            $password = md5($_POST["password"]);

            $sql = "UPDATE users SET password='$password',status=$status, role=$role where username='$username'";
            if($connection->query($sql)){
                echo "<script type='text/javascript'>alert('Updated successfully!');</script>";
            }else{
                echo "<script type='text/javascript'>alert('Something went wrong. Can't update to database');</script>";
            }
        }


        mysqli_close($connection); 
    ?>

    <div class="edit-section">
        <form method="post">
            Username: <input type=text value=<?php echo $username?> name="username" readonly><br>
            Status: 
            <input type="radio" id="radio-1" onclick="document.querySelector('#radio-2').checked=false" label="Active" name="status" value="1">
            <label for="Active">Active</label>
            <input type="radio" id="radio-2" onclick="document.querySelector('#radio-1').checked=false" label="Deactivate" name="status" value="0">
            <label for="Deactivate">Inactive</label><br>

            Role:
            <select name="role" id="role_list">
                <option value="0">Admin</option>
                <option value="1">Member</option>
            </select><br>
            Password:
            <input type="button" value="Reset Pass" id="reset-password">
            <input type="hidden" name="password" value="<?php echo $password?>" id="new-password"><br><br>
            <a href="users.php" style="display:hidden">
                <input type="button" href="users.php" value="Back">
            </a>
            <input type="submit" value="Update" name="update">
        </form>

    </div>

    <?php
        echo "
        <script> 
            if ($status == 1){
                document.getElementById('radio-1').checked = true;
            }else if ($status == 0){
                document.getElementById('radio-2').checked = true;
            }

            if ($role == 1){
                document.getElementById('role_list').getElementsByTagName('option')[1].selected = 'selected'
            }else if ($role == 0){
                document.getElementById('role_list').getElementsByTagName('option')[0].selected = 'selected'
            }
        </script>";
    ?>
    <script>
        $(document).ready(function(){
            $("#reset-password").click(function(){
                min_num = 3;
                max_num = 7;
                
                new_length = Math.floor(Math.random() * (max_num - min_num)) + min_num;
                new_pass = "";

                for (i = 0; i < new_length; i++){
                    tmp = Math.floor(Math.random() * 10);
                    new_pass += tmp;
                }

                console.log(new_pass);
                $('#new-password').val(new_pass);
            })
        })
    </script>

</body>
</html>