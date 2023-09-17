<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="homepage.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
<body>
<?php
    $connection = mysqli_connect("localhost","root", "", "shop01");

    // check for connection status
    if (!$connection){
        die("Connection failed: ".mysqli_connect_error());
    }

    function display_category($connection){
        $product_id = $_GET["product"];
        $sql = "SELECT * FROM image_cat AS i_a 
        JOIN images i ON i_a.icon_id = i.id WHERE product_id=$product_id";
        $output = "";
        $data = $connection->query($sql);

        while($row=mysqli_fetch_assoc($data)){
            $output .= '
            <div class="option-box">
                    <div class="image-border">
                        <img src="'.$row["image_path"].'" width="35">
                    </div>
                    <p>'.$row["name"].'</p>
            </div>
            ';
        }

        echo $output;
    }

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

        echo $output;
    }


?>
<section class="header">
        <div class="header-top">
            <div class="header-logo"></div>
            <div style="display:inline-block; margin-left:10px;vertical-align:top">
                <a class="header-address">
                    Xem giá, tồn kho tại
                    <span style='font-weight: 700;font-size:12px; display:block'> Hà Nội </span>
                    <span class="arrow-down"></span>
                </a>
            </div>
            <div class="header-search">
                <input type="text" placeholder="Bạn tìm gì..."> 
                <i class="icon-search"></i>
            </div>
            <div style="display:inline-block; margin-left: 10px;vertical-align:top">
                <a class="header-address" style="font-size:14px;width:90px">
                    Tài khoản & Đơn hàng
                </a>
            </div>
            <div style="display:inline-block; margin-left: 10px;vertical-align:top">
                <a class="header-address" style="font-size:14px;width:90px">
                    <i class="cart-logo"></i>
                    Giỏ hàng
                </a>
            </div>
            <div class="option-list">
                <div class="item" style="padding-right: 5px">
                    24h <br>Công nghệ
                </div>
                <div class="item" style="padding:9px 0;">
                    Hỏi đáp
                </div>
                <div class="item" style="padding:9px 0; border-right:none">
                    Game App
                </div>
            </div>
        </div>
        <div class="header-bottom">
            <div class="box"><i class="box-icon" style="background-image:url('iphone_icon.png')"></i>Điện thoại</div>
            <div class="box"><i class="box-icon" style="background-image:url('laptop_icon.png')"></i>Laptop</div>
            <div class="box"><i class="box-icon" style="background-image:url('tablet_icon.png')"></i>Tablet</div>
            <div class="box"><i class="box-icon" style="background-image:url('phu_kien.png')"></i>Phụ kiện</div>
            <div class="box"><i class="box-icon" style="background-image:url('smart_watch.png')"></i>Smartwatch</div>
            <div class="box"><i class="box-icon" style="background-image:url('smart_watch.png')"></i>Đồng hồ</div>
            <div class="box"><i class="box-icon" style="background-image:url('iphone_icon.png')"></i>Máy cũ giá rẻ</div>
            <div class="box"><i class="box-icon" style="background-image:url('pc_printer.png')"></i>PC, Máy in</div>
            <div class="box"><i></i>Sim, Thẻ cào</div>
            <div class="box"><i></i>Dịch vụ, tiện ích</div>
        </div>
    </section>

    <section class="display-product-detail">
        <div class="nav">
            <p class="last-page">Điện thoại</p>
            <p class="current-page"><!--TODO: get the current brand-->Điện thoại Samsung</p>
        </div>

        <div class="caption">
            <span class="title">
                <?php //echo $product["product_name"]?>
            </span>
            <span style="display: inline-block;"><i class="brand_icon"></i></span>
            <span class="rating">
                <?php //display_rating($product["rating"])?>
            </span>

            <span style="display:inline-block"><p class="compare"> So sánh</p></span>
        </div>

        <div class="main-box">

            <div class="image-slider">
                <img src='<?php echo $product["other_images_path"][3]?>' width="710">
            </div>

            <div class="image-options">
                <?php display_category($connection)?>
            </div>

        </div>
    </section> 

    <script>
        $(".option-box").each(function(){
            $(this).click(function(){
                $(".option-box").each(function(){
                    $(this).attr("class", "option-box")
                })
                $(this).addClass("choice")
            })
        })
    </script>

    <?php mysqli_close($connection);?>
</body>
</html>