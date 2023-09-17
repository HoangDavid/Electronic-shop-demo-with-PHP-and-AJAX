<?php
include("config.php");
include("../config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add new product</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/m5rszbejd4a44c8yiaf9rjbr102doxy3drur4npytr2m4ien/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
        $connection = mysqli_connect("localhost", "root", "", "shop01");
        // check for connection status
        if (!$connection){
            die("Connection failed: ".mysqli_connect_error());
        }
        //Check for valid input, update to products table when click save
        $product = "";
        $valid_input = true;
        $main_index = 0;
        if (isset($_POST["save"])){
            
            $product = array(
                "name"=> test_input($_POST["name"]),
                "status"=> isset($_POST["active"]) ? 1:0,
                "visibility"=>isset($_POST["visible"]) ? 1:0,
                "brand"=>$_POST["brand"],
                "price"=>$_POST["price"],
                "demand"=>$_POST["demand"],
                "storage"=>$_POST["storage"],
                "feature"=>$_POST["feature"],
                "description"=>test_input($_POST["description"]),
                "rating"=>test_input($_POST["rating"])
            );

            // Check for empty or (todo) invalid input
            foreach ($product as $key=>$value){
                if ($value == ""){
                    echo ("<script>alert('Invalid value for $key')</script>");
                    $valid_input = false;
                    break;
                }else if($key == "price"){
                    if (!is_numeric($value)){
                        echo ("<script>alert('Price should be numeric')</script>");
                        $valid_input = false;
                        break;
                    }
                }
            }
            // only save after having valid inputs

            // save new record
            if ($valid_input && !isset($_GET["request"])){
                $sql = "INSERT INTO products (product_name, brand_id, price, demand_id, feature_id, storage_id, rating, visibility, status, description) 
                VALUES ('".$product["name"]."',".$product["brand"].",'".$product["price"]."',".$product["demand"].",".$product["feature"].",
                ".$product["storage"].",".$product["rating"].",".$product["visibility"].",".$product["status"].",'".$product["description"]."')";
                
                if ($connection->query($sql)){
                    $product_id = mysqli_insert_id($connection);
                    $target_folder =  $_SERVER["DOCUMENT_ROOT"]. IMAGE_PRODUCT_PATH .$product_id;
                    upload_product_image($connection,  $product_id, $target_folder);
                    echo "<script>alert('Saved')</script>";
                }
            // edit already existed record
            }else if($valid_input && isset($_GET["request"])){
                $product_id = $_GET["product_id"];
                $target_folder =  $_SERVER["DOCUMENT_ROOT"].IMAGE_PRODUCT_PATH .$product_id;

                upload_product_image($connection,  $product_id, $target_folder);
                delete_selected_image($connection);
                
                $sql = "UPDATE products SET brand_id=".$product["brand"].",price=".$product["price"].",demand_id=".$product["demand"]."
                ,feature_id=".$product["feature"].",storage_id=".$product["storage"].",rating=".$product["rating"].",status=".$product["status"].",description='".$product["description"]."' 
                WHERE id=$product_id";
                
                if ($connection->query($sql)){
                    echo "<script>alert('Updated')</script>";
                }
            }


        }else if (isset($_GET["request"])){
            // repopulate the data to display when editing
            $product_id = (is_numeric($_GET["product_id"])?test_input($_GET["product_id"]):exit());
            $sql = "SELECT * FROM products WHERE id=$product_id";
            $data = mysqli_fetch_assoc($connection->query($sql));
            $product = array(
                "name"=>$data["product_name"],
                "status"=>$data["status"],
                "visibility"=>$data["visibility"],
                "brand"=>$data["brand_id"],
                "price"=>$data["price"],
                "demand"=>$data["demand_id"],
                "storage"=>$data["storage_id"],
                "feature"=>$data["feature_id"],
                "description"=>$data["description"],
                "rating"=>$data["rating"]
            );
            
            
        }

        //upload files and save it designated folders
        function upload_product_image($connection, $product_id, $target_folder){
            if(!is_dir($target_folder)){
                mkdir($target_folder,0755);
            }

            // upload and edit images when edit record
            if (isset($_GET["request"])){
                $product_id = $_GET["product_id"];
                // reset all images to none main_image
                $reset_sql = "UPDATE images SET main_image = 0 WHERE product_id=$product_id";
                $connection->query($reset_sql);

                $sql = "SELECT * FROM images WHERE product_id=$product_id";
                $data = $connection->query($sql);
                $main_index = $_POST["main_index"];

                $file_numb = mysqli_num_rows($data);
                
                if ($main_index < $file_numb){
                    $tmp = 0;
                    while ($row=mysqli_fetch_assoc($data)){
                        if ($main_index == $tmp){
                            $update_sql = "UPDATE images SET main_image = 1 WHERE id=".$row["id"]."";
                            $connection->query($update_sql);
                            break;
                        }
                        $tmp++;
                    }

                    if (!empty($_FILES["file_to_upload"]["name"])){
                        $file_numb = count($_FILES["file_to_upload"]["name"]);
                        for ($i = 0; $i < $file_numb; $i++){
                            $timestamp = date("Y-m-d_H-i-s");
                            $file_name = $timestamp.str_replace(array('!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~', " "),
                            "-",trim(basename($_FILES["file_to_upload"]["name"][$i])));
                            $target_file = $target_folder."/".$file_name;

                            if (move_uploaded_file($_FILES["file_to_upload"]["tmp_name"][$i], $target_file)){
                                $image_path = IMAGE_PRODUCT_PATH.$product_id."/".$file_name;
                                $sql = "INSERT INTO images (product_id, image_path, main_image) VALUES ($product_id, '$image_path', 0);";
                                $connection->query($sql);
                            }
                        }
                    }
                }else{
                    if (!empty($_FILES["file_to_upload"]["name"])){
                        $main_index = $main_index - $file_numb;
                        $file_numb = count($_FILES["file_to_upload"]["name"]);
                        for ($i = 0; $i < $file_numb; $i++){
                            $timestamp = date("Y-m-d_H-i-s");
                            $file_name = $timestamp.str_replace(array('!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~', " "),
                            "-",trim(basename($_FILES["file_to_upload"]["name"][$i])));
                            $target_file = $target_folder."/".$file_name;

                            if (move_uploaded_file($_FILES["file_to_upload"]["tmp_name"][$i], $target_file)){
                                $image_path = IMAGE_PRODUCT_PATH.$product_id."/".$file_name;
                                $sql = "INSERT INTO images (product_id, image_path, main_image) VALUES ($product_id, '$image_path', ".($main_index == $i?1:0).");";
                                $connection->query($sql);
                            }
                        }
                    }
                }
            }

            // upload images when make new record
            if (!empty($_FILES["file_to_upload"]["name"])&&!isset($_GET["request"])){
                $file_numb = count($_FILES["file_to_upload"]["name"]);
                for ($i = 0; $i < $file_numb; $i++){
                    $timestamp = date("Y-m-d_H-i-s");
                    $file_name = $timestamp.str_replace(array('!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~', " "),
                    "-",trim(basename($_FILES["file_to_upload"]["name"][$i])));
                    $target_file = $target_folder."/".$file_name;

                    if (move_uploaded_file($_FILES["file_to_upload"]["tmp_name"][$i], $target_file)){
                        $image_path = IMAGE_PRODUCT_PATH.$product_id."/".$file_name;

                        //update the main index
                        $main_index = isset($_POST["main_index"])?($_POST["main_index"]==$i?1:0):0;

                        $sql = "INSERT INTO images (product_id, image_path, main_image) VALUES ($product_id, '$image_path', $main_index);";
                        $connection->query($sql);
                    }
                }
            }
        }

        // update and delete chosen images
        function delete_selected_image($connection){
            if (isset($_POST["delete-image"])){
                $deleted_id = $_POST["delete-image"];
                $sql = "SELECT * FROM images WHERE id=";
                if (!empty($deleted_id)){
                    for ($i = 0; $i < count($deleted_id); $i++){
                        $data = $connection->query($sql.$deleted_id[$i]);
                        $image_path =  mysqli_fetch_assoc($data)["image_path"];
                        deleteSpecificFile($_SERVER["DOCUMENT_ROOT"].$image_path);
                        $delete_query = "DELETE FROM images WHERE id=".$deleted_id[$i];
                        $connection->query($delete_query);
                    }
                }
            }
        }

        //delete all files in a folder (for testing only)
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

        //delete a file (for testing only)
        function deleteSpecificFile($filePath){
            if (!file_exists($filePath)){
            }else{
                unlink($filePath);
            }
        }

        //delete a folder (for testing only)
        function deleteSpecificFolder($folderPath){
            if (!is_dir($folderPath)){
                echo "folder doesn't exist";
            }else{
                rmdir($folderPath);
                
            }
        }


        // sanitise inputs
        function test_input($input){
            $input = htmlspecialchars($input);
            return $input;
        }
    ?>

    <?php
        // display all the options for brand, feature, demand, storage
        function display_options($connection, $table, $product){
            $sql = "SELECT * FROM $table";
            $data = $connection->query($sql);
            $output = "";

            while($row = mysqli_fetch_assoc($data)){
                $tmp = ((isset($_POST["save"]) || isset($_GET["request"])))?
                ($product[strtolower($table)]==$row["id"]?"selected='selected'":""):"";
                $output = $output."<option value=".$row["id"]." ".$tmp.">".$row["name"]."</option>";
            }

            return $output;
        }

        // display all images that is already uploaded
        function display_uploaded_images($connection){
            $product_id = $_GET["product_id"];
            $sql = "SELECT * FROM images WHERE product_id=$product_id";
            $data = $connection->query($sql);

            $output = "";
            $i = 0;
            while ($row = mysqli_fetch_assoc($data)){
                
                if($row['main_image'] == 1) {
                    $output .= '<div style="display:inline-block;position:relative;"><img src="'.$row["image_path"].'" width="100" height="auto"/><input type="radio" name="main_img" checked="checked" onclick="chooseMainIMG(this)"/>
                    <span class="remove_image"><input type="checkbox" value='.$row["id"].' name="delete-image[]" hidden></span></div>';
                } else {
                    $output .= '<div style="display:inline-block;position:relative;"><img src="'.$row["image_path"].'" width="100" height="auto"/><input type="radio" name="main_img" onclick="chooseMainIMG(this)"/>
                    <span class="remove_image"><input type="checkbox" value='.$row["id"].' name="delete-image[]" hidden></span></div>';
                }
            }

            return $output;
        }

        error_reporting(E_ALL);
        ini_set("display_errors", 1);
    ?>

    <form method="post" id="create-new-product" enctype="multipart/form-data">
        Name:
        <input id="product-name" type="text" name="name" placeholder="enter product name" 
        value='<?php echo ((isset($_POST["save"]) || isset($_GET["request"]))?$product["name"]:"")?>'
        <?php echo (isset($_GET["request"])?"readonly":"")?>><br><br>
        
        Status:
        <input id="active" type="radio" onclick="$('#inactive').prop('checked', false)" name="active" value="active" 
        <?= ((isset($_POST["save"]) || isset($_GET["request"]))?($product["status"] == 1?"checked":""):"checked") ?>>
        <label for="active">Active</label>

        <input id="inactive" type="radio" onclick="$('#active').prop('checked', false)" name="inactive" value="inactive"
        <?= ((isset($_POST["save"]) || isset($_GET["request"]))?($product["status"] == 0?"checked":""):"") ?>>
        <label for="inactive">Inactive</label><br><br>

        Visibility:
        <input id="visible" type="radio" onclick="$('#hidden').prop('checked', false)" name="visible" value="visible"
        <?= ((isset($_POST["save"]) || isset($_GET["request"]))?($product["visibility"] == 1?"checked":""):"checked")?>>
        <label for="visible">Visible</label>

        <input id="hidden" type="radio" onclick="$('#visible').prop('checked', false)" name="hidden" value="hidden"
        <?= ((isset($_POST["save"]) || isset($_GET["request"]))?($product["visibility"] == 0?"checked":""):"")?>>
        <label for="hidden">Hidden</label><br><br>

        Brand:
        <select name="brand">
            <?php echo display_options($connection, "Brand", $product)?>
        </select><br><br>

        Price:
        <input type="text" name="price" value='<?php echo ((isset($_POST["save"]) || isset($_GET["request"]))?$product["price"]:"")?>'> VNĐ<br><br>

        Demand:
        <select name="demand">
            <?php echo display_options($connection, "Demand", $product)?>
        </select><br><br>

        Storage:
        <select name="storage">
            <?php echo display_options($connection, "Storage", $product)?>
        </select><br><br>

        Feature:
        <select name="feature">
            <?php echo display_options($connection, "Feature", $product)?>
        </select><br><br>

        Description:
        <textarea name="description" style="resize:none;vertical-align: top;" placeholder="describe the product" cols="30" rows="5" ><?php echo ((isset($_POST["save"])|| isset($_GET["request"]))?$product["description"]:"")?></textarea>
        <br><br>

        Rating: none
        <input type="text" name="rating" value='<?php echo ((isset($_POST["save"]) || isset($_GET["request"]))?$product["rating"]:"")?>'> star<br><br>
        <br><br>
        <!--select * from image_cat order by orders -->
        <!--repeat item -->
         <!--element upload icon -->
        <!--Check type = 0:-->
        Image:
        <input type="file" multiple id="file_to_upload_tmp" value=""><br>
        <input type="file" name="file_to_upload[]" multiple id="file_to_upload" style="display:none" value="">
        <div id="preview">
            <?php echo (isset($_GET["request"])? display_uploaded_images($connection):"")?> 
        </div>
        <input type="hidden" name="main_index" id="main_index" value="<?=$main_index?>" />
        <!--else type = 1-->
        <!--texterea-->
    </form><br>
    <button onclick="window.location='products.php'">Back</button>
    <input type="submit" form="create-new-product" name="save" value="Save">
    <?php
      mysqli_close($connection);
    ?>

    <script>
         function imagePreview(fileInput) {
            const input = document.getElementById("file_to_upload");
            
            const dt = new DataTransfer()
            for (let i = 0; i < input.files.length; i++) {
                dt.items.add(input.files[i])
            }
            if (fileInput.files) {
                for(var i = 0; i < fileInput.files.length; i++){
                    var fileReader = new FileReader();
                    fileReader.onload = function (event) {
                        $('#preview').append('<div style="display:inline-block;position:relative;"><img src="'+event.target.result+'" width="100" height="auto"/><input type="radio" name="main_img" onclick="chooseMainIMG(this)"/><span class="remove_image_preview" onclick="remove_image(this);"></span></div>');
                    };
                    fileReader.readAsDataURL(fileInput.files[i]);
                    dt.items.add(fileInput.files[i]);
                }
            }
            input.files = dt.files;
        }
        function removeFileFromFileList(index) {
            console.log("delete index=" + index);
            const dt = new DataTransfer()
            const input = document.getElementById("file_to_upload")
            const { files } = input
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i]
                if (index !== i)
                dt.items.add(file) // here you exclude the file. thus removing it.
            }
            
            input.files = dt.files // Assign the updates list
        }
        function remove_image(obj){
            removeFileFromFileList($(obj).parent().index());
            $(obj).parent().remove();
            
            $("#main_index").val($("#preview div input:checked").parent().index());
        }
        function chooseMainIMG(obj){
            $("#main_index").val($(obj).parent().index());
        }
        $(document).ready(function(){
            $("#file_to_upload_tmp").change(function () {
                imagePreview(this);
            });
            
            $("#main_index").val($("#preview div input:checked").parent().index());

            $(".remove_image").each(function(){
                $(this).click(function(){
                    $(this).find("input[name='delete-image[]']").prop("checked", true)
                    $(this).parent().css("display","none")
                })
            })
        });

    </script>

<script>
    tinymce.init({
      selector: 'textarea',
      plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
      toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | align lineheight | tinycomments | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
      tinycomments_mode: 'embedded',
      tinycomments_author: 'Author name',
      mergetags_list: [
        { value: 'First.Name', title: 'First Name' },
        { value: 'Email', title: 'Email' },
      ],
      ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant"))
    });
  </script>
</body>
</html>
