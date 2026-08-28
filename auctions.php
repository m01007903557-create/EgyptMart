<?php
/**
 * File: auctions.php
 * Version: PHP 8.3
 * Description: صفحة عرض المزادات - تعرض المزادات حسب تفضيلات موقع المستخدم
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// ==============================================
// شروط التصفية حسب بلد المستخدم للمزادات
// ==============================================

// تحديد معرف البلد من الكوكيز
$loc_id = isset($_COOKIE['loc_id']) ? (int)$_COOKIE['loc_id'] : 0;

// الحصول على معلومات الموقع الجغرافي (للاستخدام الاحتياطي)
$location_geo_country = '';
$location = getLocationInfoByIp1();
if (isset($location['countryCode']) && !empty($location['countryCode'])) {
    $location_geo_country = $location['countryCode'];
}

// تعريف الدالة المفقودة getLocationInfoByIp
if (!function_exists('getLocationInfoByIp')) {
    function getLocationInfoByIp() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        // يمكن استخدام خدمة خارجية للحصول على معلومات الموقع
        // أو إرجاع قيمة افتراضية
        return array('country' => 'EG', 'city' => 'Cairo');
    }
}

// بناء شروط SQL للمزادات حسب بلد المستخدم
if ($loc_id > 0) {
    // مستخدم لديه بلد محدد في الكوكيز
    $sql_auc_ck = " AND (
        (auc_preferred_location = 'domestic' AND auc_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = {$loc_id})) 
        OR 
        (auc_preferred_location = 'any' AND auc_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = {$loc_id}))
        OR
        (auc_preferred_location = 'my_city' AND auc_usr_id IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city = (SELECT ct_id FROM city WHERE ct_cn_id = {$loc_id})))
    )";
} else {
    // مستخدم بدون بلد محدد (Global)
    $country = '';
    if (!empty($location_geo_country)) {
        $country = mysqli_real_escape_string($con, $location_geo_country);
    }
    
    $sql_auc_ck = " AND (
        (auc_preferred_location = 'any')
        OR
        (auc_preferred_location = 'abroad' AND auc_usr_id NOT IN (SELECT DISTINCT usr_id FROM user WHERE country = (SELECT cn_id FROM country WHERE cn_code = '{$country}')))
    )";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <link href="css/eto-index-buy-1.css" rel="stylesheet" type="text/css">
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    
    <style>
        .maincontainer { 
            margin-bottom: 40px !important; 
        }
        .bp1 { 
            height: 40px;
        }
        .tender-img {
            width: 100%;
        }
        .page2-header2-col1-row2 {
            margin-left: 28px;
        }
        .banner_img {
            width: 257px; 
            height: 251px;
        }
        .page-header-col2-intro {
            width: 100% !important;
        }
        .page2-header2-col2 {
            width: 20.5% !important;
        }
        
        @media(max-width:640px) {
            .tender-img {
                width: 101%;
            }
            .c3 {
                margin-left: 0px;
                margin-top: 10px;
            }
            .banner_img {
                width: 301px; 
                height: 158px;
            }
            .page2-header2-col1-row2 {
                margin-left: 0px;
            }
            .page2-header2-col2 {
                width: 100% !important;
            }
            .page-header-col2-intro-texts {
                margin-left: 6px;
            }
        }
        
        .color_coding {
            background-color: #006bb1;
            color: #fff;
            padding: 5px 10px;
            border-radius: 3px;
        }
        
        .sidebar-toggle-acution ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-toggle-acution ul li {
            padding: 8px 10px;
            margin-bottom: 2px;
            background-color: #f5f5f5;
            cursor: pointer;
            border: 1px solid #ddd;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle-acution ul li:hover {
            background-color: #e0e0e0;
        }
        
        .sidebar-toggle-acution ul li.ho {
            background-color: #006bb1;
            color: white;
            border-color: #0055a0;
        }
        
        .sidebar-toggle-acution ul li.ho a {
            color: white;
        }
        
        .sidebar-toggle-acution ul li a {
            text-decoration: none;
            color: #333;
            display: block;
        }
        
        .aution-banner-ad {
            width: 100%;
            height: 200px;
            background-size: cover;
            background-repeat: no-repeat;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .cln {
            padding: 10px;
        }
        
        .bxh {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="search-show-box-buyleads auction">
    <div class="q_hm1">
        <!-- Header start Here::-->
        <?php 
        if (file_exists("includes/header_new.php")) {
            include "includes/header_new.php"; 
        }
        ?>
        <br>
        <p class="q_c3"></p>
        
        <!--New Header3 End -->
        <p class="c3"><img alt="" src="images/zero.gif" height="1" width="1"></p>
        
        <div class="acution-page inner_wrapper">
            <div class="lft1 lfl fl" style="width:16%;">
                <span class="mobile-menu"><i class="fa fa-bars" aria-hidden="true"></i></span>
                <p class="bg bp1 fl d1 a6 bo col-md-3 col-sm-3 col-xs-12">
                    <img alt="" src="css/img/my-market.png">
                </p>
                
                <?php
                // تحديد ترتيب عرض الفئات
                if (get_page_settings('25') == 'manual') {
                    $sql_order = " ORDER BY pc_order, pc_name";
                } else {
                    $sql_order = " ORDER BY pc_name";
                }
                ?>
                
                <link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
                <div id='cssmenu' style="width:100% !important; margin:0 !important; padding:0 !important;">
                    <div id="showsideleft"></div>
                </div>
            </div>
            
            <div class="right-acution-side">
                <div class="q_bt1 w1 fl w3 col-md-5 col-sm-5 col-xs-12">
                    <a class="cb2" href="manage-sell-offer.php" rel="nofollow">My Trade Offers</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="cb2" href="manage-auctions.php" rel="nofollow">My Auctions</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="cb2" href="manage-auction-alert.php" rel="nofollow">Manage Auction Alerts</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="q_r" href="post-auction.php" rel="nofollow"><img src="images/zero.gif" alt="">Post Auction</a>
                </div>

                <div class="" style="float:left; width:100%;">
                    <div class="acution-img-banner col-md-9 col-sm-9 col-xs-9">
                        <a href="http://www.egyptmart.shop/post-tender.php" target="_blank">
                            <div class="aution-banner-ad" style="background:url('http://www.egyptmart.shop/images/tender_banner.jpg'); background-size: cover; background-repeat: no-repeat;"></div>
                        </a>
                    </div>
                    
                    <div class="second-acution-img-banner col-md-3 col-sm-3 col-xs-12">
                        <div class="c3">
                            <?php
                            $sql_adv = "SELECT * FROM advertisement 
                                        WHERE adv_imagewidth = '239' 
                                          AND adv_imageheight = '186' 
                                          AND adv_status = '1' 
                                        ORDER BY RAND() 
                                        LIMIT 1";
                            $res_adv = mysqli_query($con, $sql_adv);
                            
                            if (mysqli_num_rows($res_adv) > 0) {
                                $row_adv = mysqli_fetch_object($res_adv);
                                $adv_img = htmlspecialchars($row_adv->adv_img ?? '');
                                ?>
                                <a href="http://www.egyptmart.shop/advertise-with-us.php" target="_blank">
                                    <img src="upload/advertisement/<?php echo $adv_img; ?>" class="banner_img" alt="Advertisement"/>
                                </a>
                                <?php
                            } else {
                                ?>
                                <img src="upload/advertisement/239-186-advertisement.png" width="239" height="186" alt="Advertisement"/>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="mid fl col-md-12 col-sm-12 col-xs-12">
                    <center>
                        <div id="m4t1"></div>
                    </center>
                    
                    <p><br></p>
                    
                    <script type="text/javascript">
                    function showAuction(page, id) {
                        $('ul#sidebarTabs li').removeClass('ho');
                        $('#tabbb' + id).addClass('ho');
                        
                        $(".xx").removeClass("on").addClass("off");
                        $("#aaa" + id).addClass("on").removeClass("off");
                        
                        $('#res').html('<div style="width:100%; padding-top:8%;" align="center"><img src="images/horizontal_loading.gif" alt="Loading"/></div>');
                        
                        $.ajax({
                            type: "POST",
                            url: "ajax-file/auctions.php",
                            data: {page: page, id: id},
                            success: function(data) {
                                $('#res').html(data);
                            },
                            error: function() {
                                $('#res').html('<div style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل المزادات</div>');
                            }
                        });
                    }
                    </script>
                    
                    <div class="bx fl col-md-9 col-sm-9 col-xs-12">
                        <p class="c4 f4 g5 lbl color_coding">
                            آخر المزايدات 
                            <a href="http://egyptmart.shop/tenders.php" class="Up_coming_Auction" style="color:white; margin-right:20px;">
                                ......................................................................... آخر المناقصات
                            </a>
                        </p>
                        
                        <div class="result-latest">
                            <div class="sidebar-toggle-acution col-sm-4">
                                <ul id="sidebarTabs">
                                    <?php
                                    $pc = 0;
                                    $i = 1;
                                    
                                    // استعلام جلب الفئات الرئيسية للمزادات النشطة
                                    $sql_cat = "SELECT pc.*, a.* 
                                                FROM product_category pc 
                                                JOIN product_category pc1 ON pc1.pc_parent_id = pc.pc_id 
                                                JOIN product_category pc2 ON pc2.pc_parent_id = pc1.pc_id 
                                                JOIN auction a ON a.auc_pc_id = pc2.pc_id 
                                                WHERE a.auc_approval_status = '1' 
                                                  AND TO_DAYS(a.auc_due_date) >= TO_DAYS(NOW()) 
                                                  {$sql_auc_ck} 
                                                  AND a.auc_status = '1' 
                                                GROUP BY pc.pc_id";
                                    
                                    $res_cat = mysqli_query($con, $sql_cat);
                                    
                                    while ($row_cat = mysqli_fetch_object($res_cat)) {
                                        if ($i == 1) {
                                            $pc = $row_cat->pc_id;
                                        }
                                        ?>
                                        <li onClick="showAuction('1', '<?php echo (int)$row_cat->pc_id; ?>');" 
                                            <?php echo ($i == 1) ? 'class="ho"' : ''; ?> 
                                            id="tabbb<?php echo $i; ?>">
                                            <a class="bgf cm1 cp" id="kk1" style="background: url('upload/category/<?php echo htmlspecialchars($row_cat->pc_image ?? ''); ?>') no-repeat scroll 0% 0% transparent; padding-left:30px;">
                                                <?php echo htmlspecialchars($row_cat->pc_name ?? ''); ?>
                                            </a>
                                        </li>
                                        <?php
                                        $i++;
                                    }
                                    ?>
                                </ul>
                            </div>
                            
                            <script type="text/javascript">
                            $(document).ready(function() {
                                showAuction(1, <?php echo (int)$pc; ?>);
                            });
                            </script>
                            
                            <div id="res" style="" class="col-sm-8"></div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-3 col-xs-12" style="float:left; margin-top:39px; background:#fff; padding:5px;">
                        <?php
                        // استعلام جلب مزادات عشوائية ذات صلة
                        $sql_t = "SELECT auction.*, product_category.pc_name, user.*, business_profile.* 
                                 FROM auction 
                                 JOIN product_category ON auction.auc_pc_id = product_category.pc_id 
                                 JOIN user ON auction.auc_usr_id = user.usr_id 
                                 JOIN business_profile ON user.usr_id = business_profile.bnsprof_uid 
                                 WHERE auction.auc_approval_status = '1' 
                                   AND TO_DAYS(auction.auc_due_date) >= TO_DAYS(NOW()) 
                                   {$sql_auc_ck} 
                                   AND auction.auc_status = '1' 
                                 ORDER BY RAND() 
                                 LIMIT 5";
                        
                        $res_t = mysqli_query($con, $sql_t);
                        
                        if (mysqli_num_rows($res_t) > 0) {
                            ?>
                            <div class="mb1 c3" style="border:1px solid #ccc; border-radius:5px;">
                                <p class="bg bxt"><img alt="" src="images/zero.gif" height="1" width="1"></p>
                                <div class="bbx cln">
                                    <p class="bxh f2 color_coding">مزادات ذات صلة</p>
                                    
                                    <div style="display: block;" id="d2">
                                        <?php
                                        $n = 1;
                                        while ($row_t = mysqli_fetch_object($res_t)) {
                                            $len = strlen($row_t->auc_details ?? '');
                                            $auc_id_enc = rand(1000, 9999) . md5((string)$row_t->auc_id);
                                            ?>
                                            <?php if ($n > 1): ?>
                                                <br class="c3">
                                            <?php endif; ?>
                                            
                                            <p class="lnh1">
                                                <b class="cor lnh1">
                                                    <a href="auction-details.php?id=<?php echo $auc_id_enc; ?>" style="text-decoration:none; color:#E84000;">
                                                        <?php echo htmlspecialchars($row_t->auc_heading ?? ''); ?>
                                                    </a>
                                                    <br>
                                                    <span class="cb2"><?php echo htmlspecialchars(get_country_name($row_t->country ?? 0)); ?></span>
                                                </b>
                                                <br>
                                                <?php echo htmlspecialchars(substr($row_t->auc_details ?? '', 0, 120)); ?>
                                            </p>
                                            
                                            <?php if ($len > 120): ?>
                                                <p class="c3 pa1 rm tr">
                                                    <a href="auction-details.php?id=<?php echo $auc_id_enc; ?>" target="_blank">المزيد...</a>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php
                                            $n++;
                                        }
                                        ?>
                                    </div>
                                    
                                    <p class="c3"></p>
                                </div>
                                <p class="bg bxb"><img alt="" src="images/zero.gif" height="1" width="1"></p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <p class="q_c3"><br></p>
                    
                    <div class="c3 tenders-arab">
                        <?php
                        // إعلان إضافي
                        $sql_adv2 = "SELECT * FROM advertisement 
                                     WHERE adv_imagewidth = '239' 
                                       AND adv_imageheight = '186' 
                                       AND adv_status = '1' 
                                     ORDER BY RAND() 
                                     LIMIT 1";
                        $res_adv2 = mysqli_query($con, $sql_adv2);
                        
                        if (mysqli_num_rows($res_adv2) > 0) {
                            $row_adv2 = mysqli_fetch_object($res_adv2);
                            $adv_link = htmlspecialchars($row_adv2->adv_link ?? '');
                            $adv_img2 = htmlspecialchars($row_adv2->adv_img ?? '');
                            ?>
                            <a href="//<?php echo $adv_link; ?>" target="_blank">
                                <img src="upload/advertisement/<?php echo $adv_img2; ?>" class="banner_img" alt="Advertisement"/>
                            </a>
                            <?php
                        } else {
                            ?>
                            <img src="upload/advertisement/239-186-advertisement.png" width="239" height="186" alt="Advertisement"/>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="q_c3"><br><br></p>
    </div>
    
    <div id="bl_overlay_layer" class="layer" style="display:none">
        <div class="bl_overlay"></div>
    </div>
    
    <!--Footer starts here-->
    <?php 
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    }
    ?>
    
    <script>       
    function showsidecate() { 
        $('#showsideleft').html('<img src="http://egyptmart.shop/images/horizontal_loading.gif">');
        
        $.ajax({
            type: "POST",
            url: "showidecate.php",
            data: {},
            success: function(data) {
                $('#showsideleft').html(data);
            },
            error: function() {
                $('#showsideleft').html('<div style="color:red;">خطأ في تحميل القائمة</div>');
            }
        });
    }
    
    showsidecate(); 
    </script>
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>