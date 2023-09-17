<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["file_to_upload"])){
        $response = array(
            'image'=>$_FILES["file_to_upload"]
        );


        echo json_encode($response);
    }
?>