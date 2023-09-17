<?php
    $page = $_REQUEST['page'];
    //select db
    $connection = mysqli_connect("localhost","root", "", "shop01");

    // check for connection status
    if (!$connection){
        die("Connection failed: ".mysqli_connect_error());
    }
    $offset_val = 10 * ($page -1);
    $sql = "SELECT p.product_name, p.price, p.description, p.rating, p.id, i.image_path AS image_path
    FROM products AS p JOIN images i ON p.id = i.product_id WHERE i.main_image=1 LIMIT 10 OFFSET $offset_val;";

    $data = $connection->query($sql);

    $output = "";
    while ($row = mysqli_fetch_assoc($data)){
        $output .= ' 
        <a href="product_detail.php?product='.$row["id"].'">
        <div class="product-box">
        <img src="'.$row["image_path"].'" class="product-image" width="200">
        <div class="info">
            <p class="product-caption">'.$row["product_name"].'</p>
            <p class="product-price">'.str_replace(",",".",number_format($row["price"])).' đ</p>
            <p class="product-rating"> 
                '.display_rating($row["rating"]).'
            </p>
            <p class="product-compare">So sánh</p>
        </div>
        </div>
        </a>';
    }

    echo $output;

    function display_rating($rating){
        $output = "";
        $full_star_numb = (int) $rating;
        $float_point = $rating - $full_star_numb;
        $rest_star = 5;

        for ($i = 0; $i < $full_star_numb; $i++){
            $output .= "<i class='full-star'></i>";
            $rest_star--;
        }

        if ($float_point == 0.5){
            $output .= "<i class='half-star'></i>";
            $rest_star--;
        }

        for ($i = 0; $i < $rest_star; $i++){
            $output .= "<i class='empty-star'></i>";
        }

        return $output;
    }
    
?>