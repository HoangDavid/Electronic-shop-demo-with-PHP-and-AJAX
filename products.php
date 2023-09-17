<?php
include("config.php");
include("../config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Products_data</title>
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
            <?php

                $connection = mysqli_connect("localhost", "root", "", "shop01");

                // check for connection status
                if (!$connection){
                    die("Connection failed: ".mysqli_connect_error());
                }
                
                //delete link for the product
                if (isset($_POST["delete"])){
                    $sql = "DELETE FROM products WHERE id=".$_POST["user_id"]."";
                    delete_products_images($connection, $_POST["user_id"]);
                    if ($connection->query($sql)){
                        echo "<script>alert('deleted successfully')</script>";
                    }
                }

                //delete selected rows or all rows
                if (isset($_POST["delete-select"])){
                    $selected = isset($_POST["select-box"])?$_POST["select-box"]:"";
                    if (!empty($selected)){
                        for ($i = 0; $i < count($selected); $i++){
                            delete_products_images($connection, $selected[$i]);
                        }
                        $sql = "DELETE FROM products WHERE id IN (".implode(",", $selected).")";
                        $connection->query($sql);
                    }
                }

                // set by default
                $max_rows_display = isset($_GET["limit"])?$_GET["limit"]:20;

                //display products info
                function display_table($connection){
                    $output = "";
                    $sql = update_query();
                    $data = $connection->query($sql);
                    $status = array(
                        "1"=>"Active",
                        "0"=>"Inactive"
                    );
                
                    $visibility = array(
                        "1"=>"Visible",
                        "0"=>"Hidden"
                    );

                    while($row = mysqli_fetch_assoc($data)){
                        $output .=  "
                        <tr>
                            <td><input type='checkbox' class='select-box' name='select-box[]' form='delete-select' value=".$row["id"].">".$row["id"]."</td>
                            <td><img src='".get_main_image_path($connection, $row["id"])."'width='60'></td>
                            <td>".$row["product_name"]."</td>
                            <td>".$status[$row["status"]]."</td>
                            <td>".$row["brand_name"]."</td>
                            <td>".$row["price"]."</td>
                            <td>".$row["demand_name"]."</td>
                            <td>".$row["feature_name"]."</td>
                            <td>".$row["storage_name"]."</td>
                            <td>".$row["rating"]."</td>
                            <td>".$visibility[$row["visibility"]]."</td>
                            <td>".$row["description"]."</td>
                            <td>".edit_del_link($row["id"])."</td>
                        </tr>
                        ";
                    }

                    return $output;
                }

                //update the query for displaying products info for the query (helper function)
                function update_query(){
                    $sql = "SELECT * FROM (
                        SELECT p.id, p.product_name, p.price, p.description, p.rating, p.visibility,p.status,
                        b.name AS brand_name,d.name AS demand_name,f.name AS feature_name,s.name AS storage_name 
                        FROM products AS p 
                        JOIN Brand b ON p.brand_id = b.id
                        JOIN Demand d ON p.demand_id = d.id
                        JOIN Feature f ON p.feature_id = f.id
                        JOIN Storage s ON p.storage_id = s.id
                    ) AS subquery";

                    $where = $order_by = $limit = "";
                    //udpate the where parameter
                    if (isset($_GET["keywords"])){
                        $clean_input = trim(htmlspecialchars($_GET["keywords"]));
                        $clean_input = str_replace(array('!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~'), 
                        "",$clean_input);
                        if ($clean_input != ""){
                            $where = " WHERE product_name='$clean_input'";
                        }
                    }

                    //update the order parameter
                    if (isset($_GET["order_by"])){
                        $order = isset($_GET["order"])?$_GET["order"]:"";
                        $order = trim(htmlspecialchars($order));
                        if(strtolower($order) != "asc" && strtolower($order) != "desc"){
                            $order = "ASC";
                        }

                        $tmp = trim(htmlspecialchars($_GET["order_by"]));
                        if ($tmp =="storage_name"){
                            $order_by = " ORDER BY CAST($tmp AS SIGNED) $order";
                        }else{
                            $order_by = " ORDER BY $tmp $order";
                        }
                    }

                    // update the limit parameter
                    global $max_rows_display;
                    if (isset($_GET["limit"]) && is_numeric($_GET["limit"])){
                        $current_page = isset($_GET["page"])? ($_GET["page"] > 0 ? $_GET["page"]:1) : 1;
                        $limit = " LIMIT $max_rows_display OFFSET ".(($current_page - 1) * $max_rows_display);
                    }else{
                        $current_page = isset($_GET["page"])? ($_GET["page"] > 0 ? $_GET["page"]:1) : 1;
                        $limit = " LIMIT $max_rows_display OFFSET ".(($current_page - 1) * $max_rows_display);
                    }

                    
                    $sql .= $where.$order_by.$limit;
                    return $sql;
                }

                //delete all images of that product (helped fuction)
                function delete_products_images($connection,$product_id){
                    $folder_path = $_SERVER["DOCUMENT_ROOT"].IMAGE_PRODUCT_PATH.$product_id;
                    if (is_dir($folder_path)){
                        if (DeleteFilesInFolder($folder_path)){
                            rmdir($folder_path);
                            $delete_query = "DELETE FROM images WHERE product_id=$product_id";
                            $connection->query($delete_query);
                        }
                    }
                    return false;
                }

                function deleteFilesInFolder($folderPath){
                    if (!is_dir($folderPath)){
                        return false;
                    }

                    $dirHandle = opendir($folderPath);
                    while (($file = readdir($dirHandle))!=false){
                        $filePath = $folderPath.'/'.$file;

                        if (is_file($filePath)){
                            unlink($filePath);
                        }
                    }

                    return true;
                }

                // return main image path
                function get_main_image_path($connection, $product_id){
                    $sql = "SELECT * FROM images WHERE product_id=$product_id AND main_image=1";

                    $data = $connection->query("$sql");
                    if (mysqli_num_rows($data)==0){
                        // return path to no image available
                        return "/demo1/images/no_image.jpg";
                    }else if(mysqli_num_rows($data) > 0){
                        $row = mysqli_fetch_assoc($data);
                        return $row["image_path"];
                    }
                }

                //update the edit and del link (helper function)
                function edit_del_link($id){
                    $output = "
                    <a id='edit-link' href='add_product.php?request=edit&product_id=$id'>Edit</a> / 
                    <form method='post'>
                        <input type='text' name='user_id' value='$id' hidden>
                        <input type='submit' value='Del' name='delete' id='edit-link'>
                    </form>
                    ";
                    return $output;
                }

                // display arrow if order by asc or desc
                function display_arrow($order_by){
                    $output = "";
                    if(isset($_GET["order_by"])){
                        if ($_GET["order_by"]==$order_by){
                            $output .= isset($_GET["order"])?($_GET["order"]=="desc"?"↓":"↑"):"↑";
                        }
                    }

                    return $output;
                }

                // update the url
                $page_link = array(
                    "keywords"=>"keywords=".str_replace(' ','+',(isset($_GET["keywords"])?$_GET["keywords"]:"")),
                    "order_by"=>"&order_by=".(isset($_GET["order_by"])?$_GET["order_by"]:"id"),
                    "order"=>"&order=".(isset($_GET["order"])?($_GET["order"]=="desc"?"asc":"desc"):""),
                    "limit"=>"&limit=".((isset($_GET["limit"]))?(is_numeric($_GET["limit"])?$_GET["limit"]:"20"):"20"),
                    "page"=>"&page=".((isset($_GET["page"]))?(is_numeric($_GET["page"])?$_GET["page"]:"1"):"1")
                );

                // Todo: display page navigation
                function display_page_navigation($connection){
                    $total_rows = mysqli_num_rows($connection->query("SELECT * FROM products"));
                    $current_page = 1;
                    global $max_rows_display;
                    global $page_link;
                    $page_href = "products.php?".$page_link["keywords"].$page_link["order_by"]."&order=".(isset($_GET["order"])?($_GET["order"]=="desc"?"desc":"asc"):"").$page_link["limit"]."&page=";

                    $output = "";
                    //sanitize input
                    if (isset($_GET["page"])){
                        if (is_numeric($_GET["page"])){
                            $current_page = trim(htmlspecialchars($_GET["page"]));
                        }
                    }

                    $total_page = floor($total_rows / $max_rows_display) + ($total_rows % $max_rows_display == 0 ? 0 : 1);
                    if ($total_page <= 5){
                        for ($i = 1; $i <= $total_page; $i++){
                            $output .= "<span class='page_numb'><a href='".$page_href.$i."'>$i</a></span>";
                        }
                    }else{
                        if ($current_page < 4){
                            for ($i = 1; $i <= 4; $i++){
                                $output .= "<span class='page_numb'><a href='".$page_href.$i."'>$i</a></span>";
                            }
                            $output .= "...<span class='page_numb'><a href='".$page_href.$total_page."'>$total_page</a></span>";
                        }else if ($current_page >= 4 && $current_page <= $total_page - 3){
                            $output = "<span class='page_numb'><a href='".$page_href."1'>1</a></span>...";
                            for ($i = $current_page - 1; $i <= $current_page + 1; $i++){
                                $output .= $output."<span class='page_numb'><a href='".$page_href.$i."'>$i</a></span>";
                            }

                            $output = "...<span class='page_numb'><a href='".$page_href.$total_page."'>$total_page</a></span>";
                        } else if ($current_page > $total_page - 3){
                            $output = "<span class='page_numb'>1</span>...";
                            for($i=$total_page - 3; $i <= $total_page; $i++){
                                $output .= $output."<span class='page_numb'><a href='".$page_href.$i."'>$i</a></span>";
                            }
                        }
                    }

                    return $output;
                }
            ?>

                <div class="search">
                    <form method="get">
                        <input type="text" placeholder="Search" name="keywords" id="search-keywords"
                        value='<?php echo (isset($_GET["keywords"])?$_GET["keywords"]:"")?>'>
                    </form>
                </div>
                <table>
                    <tbody>
                        <tr>
                            <th><a href=<?="products.php?".$page_link["keywords"]."&order_by=id".$page_link["order"].$page_link["limit"].$page_link["page"]?>>id</a>
                            <?php echo display_arrow("id") ?></th>
                            <th>Image</th>
                            <th><a href=<?="products.php?".$page_link["keywords"]."&order_by=product_name".$page_link["order"].$page_link["limit"].$page_link["page"]?>>product_name</a>
                            <?php echo display_arrow("product_name") ?></th>
                            <th>status</th>
                            <th><a href=<?="products.php?".$page_link["keywords"]."&order_by=brand_name".$page_link["order"].$page_link["limit"].$page_link["page"]?>>brand</a>
                            <?php echo display_arrow("brand_name")?></th>
                            <th><a href=<?="products.php?".$page_link["keywords"]."&order_by=price".$page_link["order"].$page_link["limit"].$page_link["page"]?>>price</a>
                            <?php echo display_arrow("price")?></th>
                            <th><a href=<?="products.php?".$page_link["keywords"]."&order_by=demand_name".$page_link["order"].$page_link["limit"].$page_link["page"]?>>demand</a>
                            <?php echo display_arrow("demand_name")?></th>
                            <th><a href=<?="products.php?".$page_link["keywords"]."&order_by=feature_name".$page_link["order"].$page_link["limit"]?>>feature</a>
                            <?php echo display_arrow("feature_name")?></th>
                            <th><a href=<?="products.php?".$page_link["keywords"]."&order_by=storage_name".$page_link["order"].$page_link["limit"].$page_link["page"]?>>storage</a>
                            <?php echo display_arrow("storage_name")?></th>
                            <th>rating</th>
                            <th>visibility</th>
                            <th>description</th>
                            <th>action</th>
                        </tr>
                        <?php echo display_table($connection)?>
                    </tbody>
                </table>

                <!--Todo: Select all, Delete, display options-->
                <div class="edit-options">
                    <button onclick="window.location='add_product.php'">Add new record</button>
                    <label style="margin-left: 10px;" for="">Select All:</label>
                    <form method="post" id="delete-select">
                        <input type="checkbox" id="select-all" name="select">
                        <input style="margin-left: 10px;" type="submit" value="Delete" name="delete-select">
                    </form>
                    <label style="margin-left:10px;"for="">Rows:</label>
                    <form method="get">
                        <select id="limit" name="limit" onchange="this.form.submit()">
                            <option value="20" <?=($max_rows_display==20?'selected="selected"':"") ?>>20</option>
                            <option value="50" <?=($max_rows_display==50?'selected="selected"':"") ?>>50</option>
                            <option value="100" <?=($max_rows_display==100?'selected="selected"':"") ?>>100</option>
                        </select>
                        <input name="keywords" value='<?php echo (isset($_GET["keywords"])?$_GET["keywords"]:"")?>' hidden>
                    </form>
                </div>

                <!--Todo: display page navigation-->
                <div class="navigation">
                    <?php echo display_page_navigation($connection)?>
                </div>
                <script>
                    $(document).ready(function(){
                        $("#search-keywords").keypress(function(e){
                            if(e.which == 13){
                                $(this).form.submit();
                            }
                        })
                    })

                    $("#select-all").click(function(){
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