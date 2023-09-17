<?php
include("config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
<body>
    <div class="content-header">
        <?php include("header.php")?>
    </div>

    <div class="content-body">
        <div class="left-col"> 
            <?php include("left_menu.php")?>
        </div>
        <div class="right-col">
            <div class="search">
                <form method="post">
                    <input type="text" name="search_user">
                    <input type="submit" value="Search" name="search">
                </form>
            </div>

            <?php
                $connection = mysqli_connect("localhost", "root", "", "shop01");
                // check for connection status
                if (!$connection){
                    die("Connection failed: ".mysqli_connect_error());
                }
                
                //Delete a user
                if (isset($_POST["delete_user"])){
                    $user_id = $_POST["user_id"];
                    $tmp = $connection->query("SELECT * FROM users WHERE id=$user_id");
                    $row = $tmp->fetch_assoc();
                    if ($row["role"] == 0){
                        if (mysqli_num_rows($connection->query("SELECT * FROM users WHERE role=0")) == 1){
                            echo "<script>alert('Deletion failed. Need at least one admin account!')</script>";
                        }else{
                            if ($connection->query("DELETE FROM users WHERE id=$user_id")){
                                echo "<script>alert('Deletion success')</script>";
                            }else{
                                echo "<script>alert('Deletion failed. Something went wrong=(')</script>";
                            }
                        }
                    }else{
                        if ($connection->query("DELETE FROM users WHERE id=$user_id")){
                            echo "<script>alert('Deletion success')</script>";
                        }else{
                            echo "<script>alert('Deletion failed. Something went wrong=(')</script>";
                        }
                    }
                }

                if (isset($_POST["delete-selected"])){
                    $selected = $_POST["select"];
                    if (empty($selected)){
                        echo "<br>Nothing has been selected";
                    }else {
                        $delete_sql = "DELETE FROM users WHERE id in(".implode(",", $selected).")";
                        $connection->query($delete_sql);
                    }

                }



                // show all users or by filter
                $sql = "SELECT * FROM users";
                $where = "";
                $limit = "";
                $order_by = isset($_GET["order"])?$_GET["order"]:"id";
                $order_type = isset($_GET["order_type"])?$_GET["order_type"]:"ASC";
                $page_from = 1;
                $max_visible_rows = isset($_GET["number-to-display"])?$_GET["number-to-display"]:20;
                $href_field = "users.php?";
                $href_page = "users.php?".(isset($_GET["order"])?("&order=".$_GET["order"]):"").(isset($_GET["order_type"])?("&order_type=".$_GET["order_type"]):"");


                $href_page .= "&number-to-display=".$max_visible_rows."&";
                $href_field .= "&number-to-display=".$max_visible_rows."&";

                if (isset($_GET["page"])){
                    $page_from = (int) $_GET["page"];
                    $page_from = ($page_from - 1) * $max_visible_rows;
                    $limit = " LIMIT $max_visible_rows OFFSET $page_from";
                    
                }else{
                    $limit = " LIMIT $max_visible_rows OFFSET 0";
                }
                $href_field .= "page=".(isset($_GET["page"])?$_GET["page"]:"1");
                
                // For searching and filtering
                if (isset($_POST["search"])){

                    $search_user = htmlspecialchars($_POST["search_user"]);
                    $search_user = trim($search_user);
                    $href_field .= "&keyword=".$search_user;
                    $href_page .= "&keyword=".$search_user;
                    if ($search_user != ""){
                        $where = " WHERE username LIKE '$search_user%'OR username='$search_user'";
                    }
                }else if (isset($_GET["keyword"])){
                    $tmp = $_GET["keyword"];
                    $href_field .= "&keyword=".$tmp;
                    $href_page .= "&keyword=".$tmp;
                    $where = " WHERE username LIKE'$tmp%'OR username='$tmp'";
                }

                $order = " ORDER BY ".$order_by . " ".$order_type;

                $total_rows = mysqli_num_rows($connection->query($sql.$where.$order));
                $total_page = floor($total_rows / $max_visible_rows) + ($total_rows % 20 == 0 ? 0 : 1);
                $current_page = isset($_GET["page"])?$_GET["page"]:1;
                if($current_page > $total_page) {
                    $current_page = $total_page;
                    $limit = " LIMIT $max_visible_rows OFFSET ".($current_page - 1)*$max_visible_rows;
                }
                $sql = $sql.$where.$order.$limit;
                
                $data = $connection->query($sql);

                $status_arr = array(
                    "0" => "Inactive",
                    "1" => "Active"
                );
                $role_arr = array(
                    "0"=>"Admin", 
                    "1"=>"Member"
                );
                
                $output = '
                <table>
                <tbody>
                    <tr>
                        <th><a href="'. $href_field .'&order=id&order_type='.(($order_by=="id"&&$order_type=="asc")?"desc":"asc").'">id</a></th>
                        <th><a href="'. $href_field .'&order=username&order_type='.(($order_by=="username"&&$order_type=="asc")?"desc":"asc").'">username</a></th>
                        <th><a href="'. $href_field .'&order=password&order_type='.(($order_by=="password"&&$order_type=="asc")?"desc":"asc").'">password</a></th>
                        <th><a href="'. $href_field .'&order=status&order_type='.(($order_by=="status"&&$order_type=="asc")?"desc":"asc").'">status</a></th>
                        <th><a href="'. $href_field .'&order=role&order_type='.(($order_by=="role"&&$order_type=="asc")?"desc":"asc").'">role</a></th>
                        <th>action</th>
                    </tr>
                
                ';

                $edit_link = "
                    <form method='post' action='edit.php'>
                        <input type='text' id='user-id' name='user_id' value=?>
                        <input type='submit' id='edit-link' name='edit_user' value='Edit'>
                    </form> /
                    <form method='post' target='_blank'>
                        <input type='text' id='user-id' name='user_id' value=?>
                        <input type='submit' id='edit-link' name='delete_user' value='Del'>
                    </form>
                ";
                if (mysqli_num_rows($data) == 0){
                    echo "<h3>Nothing here =(<h3>";
                }else{
                    while($row = $data->fetch_assoc()){
                        $tmp = "
                        <tr>
                            <td> <span style='position:relative; left: 0;'><input type='checkbox' form='delete-form' class='select-box' name='select[]' value=".$row["id"]."></span>".$row["id"]."</td>
                            <td>".$row["username"]."</td>
                            <td>".$row["password"]."</td>
                            <td>".$status_arr[$row["status"]]."</td>
                            <td>".$role_arr[$row["role"]]."</td>
                            <td>".str_replace("?", $row["id"], $edit_link)."</td>
                        </tr>";

                        $output = $output.$tmp;
                    }
                    echo $output."</tbody></table>";
                }
        
                mysqli_close($connection); 
            ?>

            <div class="navigation">
                <?php
                    
                    $last_page = $total_page;
                    $nav_html = "";
                    if ($last_page > 5){
                        if ($current_page >=4 && $current_page <= $last_page - 3){
                        $nav_html = "<span class='page_numb'><a href='users.php?page=1'>1</a></span>...";
                        for ($i = $current_page - 1; $i <= $current_page + 1; $i++){
                            $nav_html = $nav_html."<span class='page_numb'><a href='$href_page&page=$i'>$i</a></span>";
                        }

                        $nav_html = $nav_html."...<span class='page_numb'><a href='$href_page&page=$last_page'>$last_page</a></span>";
                        }else if($current_page < 4){
                            for ($i = 1; $i <= 4; $i++){
                                $nav_html = $nav_html."<span class='page_numb'><a href='$href_page&page=$i'>$i</a></span>";
                            }
                            $nav_html = $nav_html."...<span class='page_numb'><a href='$href_page&page=$last_page'>$last_page</a></span>";
                        }else if($current_page > $last_page - 3){
                            $nav_html = "<span class='page_numb'><a href='$href_page&page=1'>1</a></span> ...";
                            for ($i = $last_page - 3; $i <= $last_page; $i++){
                                $nav_html = $nav_html."<span class='page_numb'><a href='$href_page&page=$i'>$i</a></span>";
                            }
                        }
                    }else{
                        for ($i = 1; $i <= $last_page; $i++){
                            $nav_html = $nav_html."<span class='page_numb'><a href='$href_page&page=$i'>$i</a></span>";
                        }
                    }
                ?>

                <a href="<?=$href_page?>&page=1"><<</a>
                <a href="<?=$href_page?>&page=<?=($current_page>1?($current_page - 1):1) ?>"><</a>
                <?php echo $nav_html;?>
                <a href="<?=$href_page?>&page=<?=($current_page < $last_page?($current_page + 1):$last_page) ?>">></a>
                <a href="<?=$href_page?>&page=<?php echo $last_page?>">>></a>
            </div>
            
            <div style="margin-top:20px; display:inline-block">
                <input type="checkbox" name="Check-all" id="Check-all">
                <label for="Check-all">Check all</label>

                <form method="post" id="delete-form">
                        <input type="submit" value="Delete" name="delete-selected">
                </form>
            </div>

            <div style="display:inline-block">
                <form method="get">
                    <label for="number-to-display">Number of rows: </label>
                    <select name="number-to-display" id="number-to-display" onchange="this.form.submit()">
                        <option value="20" <?=($max_visible_rows==20?'selected="selected"':"") ?>>20</option>
                        <option value="50" <?=($max_visible_rows==50?'selected="selected"':"") ?>>50</option>
                        <option value="100" <?=($max_visible_rows==100?'selected="selected"':"") ?>>100</option>
                        <option value="200" <?=($max_visible_rows==200?'selected="selected"':"") ?>>200</option>
                    </select>
                </form>
            </div>

            


            <script>
                $("#Check-all").on("change", function(){
                    if ($(this).prop('checked')){
                        $(".select-box").each(function(){
                            $(this).prop("checked", true);
                        }) 
                    }else{
                        $(".select-box").each(function(){
                            $(this).prop("checked", false);
                        }) 
                    }
                })
            </script>
        </div>
    </div>

</body>
</html>