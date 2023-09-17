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
    <!--server side-->
    <?php
        $connection = mysqli_connect("localhost","root", "", "shop01");

        // check for connection status
        if (!$connection){
            die("Connection failed: ".mysqli_connect_error());
        }

        $total_rows = mysqli_num_rows($connection->query("SELECT * FROM products"));

        function display_product($connection){
            $sql = "SELECT p.product_name, p.price, p.description, p.rating, p.id, i.image_path AS image_path
            FROM products AS p JOIN images i ON p.id = i.product_id WHERE i.main_image=1 LIMIT 10;";
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
        }

        // helper function
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
    <!--page html part-->
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
    <!--filter section start -->
    <section class="product-filter">
        <div class="filter-top">
            <!--TODO: main filter--> 

            <div class="filter-option">
                <p>Hãng</p>
                <span class="arrow-down filter-arrow"></span>
            </div>
            <div class="filter-option">
                <p>Giá</p>
                <span class="arrow-down filter-arrow"></span>
            </div>
            <div class="filter-option">
                <p>Loại điện thoại</p>
                <span class="arrow-down filter-arrow"></span>
            </div>
            <div class="filter-option">
                <p>Nhu cầu</p>
                <span class="arrow-down filter-arrow"></span>
            </div>
            <div class="filter-option">
                <p>RAM</p>
                <span class="arrow-down filter-arrow"></span>
            </div>
            <div class="filter-option">
                <p>Dung lượng lưu trữ</p>
                <span class="arrow-down filter-arrow"></span>
            </div>
            <div class="filter-option">
                <p>Tính năng sạc</p>
                <span class="arrow-down filter-arrow"></span>
            </div>
            <div class="filter-option">
                <p>Tính năng đặc biệt
                </p>
                <span class="arrow-down filter-arrow"></span>
            </div>
        </div>

        <div class="filter-bottom">
            <input id="fast-delivery" type="checkbox" class="filter-checkbox">
            <label for="fast-delivery"> GIAO NHANH</label> 
            <input id="discount-1" type="checkbox" class="filter-checkbox">
            <label for="discount-1" >Giảm giá</label>
            <input id="discount-2" type="checkbox" class="filter-checkbox">
            <label for="discount-2"> Góp 0% - 1% </label>
            <input id="special-product" type="checkbox" class="filter-checkbox">
            <label for="special-product"> Đặc biệt tại thế giới di động</label>
            <input id ="new-product" type="checkbox" class="filter-checkbox">
            <label for="new-product">Mới</label>
            <!--TODO: filter for popularity, price range, etc-->

        </div>
    </section>
    <!--filter section end -->

    <!--display product section start-->
    <section class="product-display">
        <div class="product-gallery">
            <?php display_product($connection)?>
        </div>
        <div class="view-more-product">
            <a id="load_next_page">
                Xem thêm
                <span id="remain"></span>
                điện thoại
            </a>
        </div>
    </section>

    <section class="page-footer">
        <div class="all-links-display">
            <ul>
                <li>Tích điểm Quà tặng</li>
                <li>Lịch sử mua hàng</li>
                <li>Tìm hiểu về mua trả góp</li>
                <li>Chính sách bảo hành</li>
                <li> Xem thêm</li>
            </ul>
            <ul>
                <li>Giới thiệu công ty (MWG.vn)</li>
                <li>Tuyển dụng</li>
                <li>Gửi góp ý, khiếu nại</li>
                <li>Tìm siêu thị (3.361 shop)</li>
                <li>Xem bản mobile</li>
            </ul>
            <ul>
                <li><b>Tổng đài hỗ trợ</b> (Miễn phí gọi)</li>
                <li>Gọi mua: <span style="color:#288ad6">1800.1060</span> (7:30 - 22:00)</li>
                <li>Khiếu nại: <span style="color:#288ad6">1800.1062</span> (8:00 - 21:30)</li>
                <li>Bảo hành: <span style="color:#288ad6">1800.1064</span> (8:00 - 21:30)</li>
            </ul>

            <ul style="margin-left: 30px;">
                <li style="margin-left: 20px;">
                    <span class="f-social"> 
                        3911.8k Fan
                        <i class="icon" style="background-position: -225px 0;"></i>
                    </span>
                    <span class="f-social">
                        860k Đăng ký
                        <i class="icon" style="background-position: -200px 0;"></i>
                    </span>
                    <span class="f-social">
                        Zalo TGDĐ
                    </span>
                </li>

                <li style="margin-left: 10px;">
                    <span class="f-social">
                        <i class="icon" style="background-position: -200px -30px; height: 24px; width:79px;"> </i>
                    </span>

                    <span class="f-social">
                        <i class="icon" style="background-position: -250px 0px; height: 25px; width:25px; left: 35px"> </i>
                    </span>

                    <span class="f-social">
                        <i class="icon"  style="background-position: -80px -60px; height: 24px; width:122px; left: 35px"> </i>
                    </span>

                    <span class="f-social" style="display: inline-block;left: 145px;position:relative;">
                        <i style="background-position: center; height: 35px; width:85px; left: 35px;
                        background-image:url('extra_brand.png');background-position: center;display:block;background-size:cover;"> </i>
                    </span>
                </li>
                <li style="position: relative; margin-top: 17px;">
                    Website cùng tập đoàn 
                </li>

                <li>
                    <span class="brand-logo" style="background-position: 0 -58px;"> </span>
                    <span class="brand-logo" style="background-position: -85px 0;"> </span>
                    <span class="brand-logo" style="background-position: -170px 0;"> </span>
                    <span class="brand-logo" style="background-position: -85px -90px;"> </span>
                </li>
                <li>
                    <span class="brand-logo" style="background-position: -85px -120px;"> </span>
                    <span class="brand-logo" style="background-position: -170px -90px;"> </span>
                    <span class="brand-logo" style="background-position: 0 -90px;"> </span>
                    <span class="brand-logo" style="background-position: 0 -120px;"> </span>
                </li>
            </ul>
        </div>
    </section>

    <section class="copy-right">
        <div class="container">
            <p style="font-size: 16px;">
                © 2018. Công ty cổ phần Thế Giới Di Động. GPDKKD: 0303217354 do sở KH &amp; ĐT TP.HCM cấp ngày 02/01/2007. GPMXH: 238/GP-BTTTT do Bộ Thông Tin và Truyền Thông cấp ngày 04/06/2020.
                Địa chỉ: 128 Trần Quang Khải, P.Tân Định, Q.1, TP.Hồ Chí Minh. Địa chỉ liên hệ và gửi chứng từ: Lô T2-1.2, Đường D1, Đ. D1, P.Tân Phú, TP.Thủ Đức, TP.Hồ Chí Minh. Điện thoại: 028 38125960. Email: cskh@thegioididong.com. Chịu trách nhiệm nội dung: Huỳnh Văn Tốt. Email: Tot.huynhvan@thegioididong.com.
                <a rel="nofollow" href="/thoa-thuan-su-dung-trang-mxh">Xem chính sách sử dụng</a>
                
            </p>
        </div>
    </section>

    <script>
        var total_rows = <?php echo $total_rows ?>;
        var total_page = Math.ceil(total_rows / 10);
        var current_page = 1;

        function display_next_page() {
            
            current_page += 1;
            if(current_page >= total_page){
                $("#load_next_page").hide();
            }

            $.ajax({
                url: "load_next_page.php?page=" + current_page,
                context: document.body
            }).done(function(res) {
                $(".product-gallery").append(res);
                var tmp = total_rows - (current_page - 1) * 10 - 10;
                $("#remain").text(tmp);
            });
        }
        $(document).ready(function(){
            $("#load_next_page").click(function(){
                display_next_page();
            })

            var tmp = total_rows - (current_page - 1) * 10 - 10;
            $("#remain").text(tmp);
        })
    </script>

    <?php
        mysqli_close($connection);
    ?>
</body>
</html>