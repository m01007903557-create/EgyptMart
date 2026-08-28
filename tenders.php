<?php
/**
 * File: tenders.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض قائمة المناقصات مع تصفية حسب الموقع والتصنيف
 * Display tenders list with location and category filtering
 * 
 * Features:
 * - عرض المناقصات النشطة
 * - تصفية حسب موقع المستخدم (Cookie)
 * - تصنيفات جانبية
 * - مناقصات ذات صلة
 * - إعلانات
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "common.php";

// Get location from cookie or geo IP
$location_geo_country = '';
$sql_tnd_ck = '';

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $locId = (int)$_COOKIE['loc_id'];
    
    $sql_tnd_ck = " AND (
        (tnd_preferred_location = 'domestic' AND tnd_usr_id IN (
            SELECT DISTINCT usr_id FROM user WHERE country = {$locId}
        ))
        OR 
        (tnd_preferred_location = 'any' AND tnd_usr_id IN (
            SELECT DISTINCT usr_id FROM user WHERE country = {$locId}
        ))
        OR
        (tnd_preferred_location = 'my_city' AND tnd_usr_id IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile 
            WHERE bnsprof_city IN (
                SELECT ct_id FROM city WHERE ct_cn_id = {$locId}
            )
        ))
    )";
} else {
    // Get country from geo IP (simplified)
    $location_geo_country = $_SERVER['GEOIP_COUNTRY_CODE'] ?? '';
    
    $sql_tnd_ck = " AND (
        (tnd_preferred_location = 'any')
        OR
        (tnd_preferred_location = 'abroad' AND tnd_usr_id NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country IN (
                SELECT cn_id FROM country WHERE cn_code = '{$location_geo_country}'
            )
        ))
    )";
}

// Determine category ordering
$sql_order = (get_page_settings('25') === 'manual') ? " ORDER BY pc_order, pc_name" : " ORDER BY pc_name";
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle()); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
    <title><?php echo htmlspecialchars(getSiteTitle()); ?></title>
    
    <!-- CSS Files -->
    <link href="css/eto-index-buy-1.css" rel="stylesheet" type="text/css">
    <link href="css/menu_styles.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- jQuery -->
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
        .tender-right-side {
            float: right;
            width: 100%;
        }
        .tender__banner-background {
            width: 100%;
            height: 150px;
            background-size: cover;
            background-position: center;
            border-radius: 5px;
        }
        .latest-upcoming {
            width: 100%;
            float: left;
        }
        .tbl {
            width: 20%;
            float: left;
        }
        #sidebarTabs {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #sidebarTabs li {
            padding: 10px;
            margin-bottom: 5px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        #sidebarTabs li:hover {
            background: #e0e0e0;
        }
        #sidebarTabs li.ho {
            background: #466da0;
            color: white;
        }
        #sidebarTabs li.ho a {
            color: white;
        }
        #res {
            float: left;
            width: 80%;
            padding-right: 10px;
        }
        .bx {
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
            padding: 10px;
            margin-bottom: 20px;
        }
        .bxh {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #c30000;
        }
        .color_coding {
            color: #c30000;
        }
        .c3 {
            clear: both;
        }
        .lnh1 {
            line-height: 1.6;
        }
        .cor a {
            color: #E84000;
            text-decoration: none;
        }
        .cor a:hover {
            text-decoration: underline;
        }
        .rm a {
            color: #466da0;
            text-decoration: none;
            font-size: 12px;
        }
        .rm a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 640px) {
            .tender-img {
                width: 106%;
            }
            .c3 {
                margin-left: 0;
                margin-top: 10px;
            }
            .banner_img {
                width: 282px;
                height: 158px;
            }
            .page2-header2-col1-row2 {
                margin-left: 0;
            }
            .page2-header2-col2 {
                width: 100% !important;
            }
            .page-header-col2-intro-texts {
                margin-left: 6px;
            }
            .tbl {
                width: 100%;
                margin-bottom: 20px;
            }
            #res {
                width: 100%;
            }
        }
    </style>
    
    <script type="text/javascript">
        function showLead(page, id) {
            $('ul#sidebarTabs li').removeClass('ho');
            $('#tabbb' + id).addClass('ho');
            
            $('.xx').removeClass('on').addClass('off');
            $('#aaa' + id).addClass('on').removeClass('off');
            
            $('#res').html('<div style="width:100%; padding-top:8%;" align="center">' +
                          '<img src="images/horizontal_loading.gif" alt="Loading"/></div>');
            
            $.post("ajax-file/tenders.php", {page: page, id: id}, function(data) {
                $('#res').html(data);
            }).fail(function() {
                $('#res').html('<div class="alert alert-danger">Failed to load tenders</div>');
            });
        }
        
        function showLeadMain(page, id) {
            $('ul#sidebarTabs li').removeClass('ho');
            $('#tabbb' + id).addClass('ho');
            
            $('.xx').removeClass('on').addClass('off');
            $('#aaa' + id).addClass('on').removeClass('off');
            
            $('#res').html('<div style="width:100%; padding-top:8%;" align="center">' +
                          '<img src="images/horizontal_loading.gif" alt="Loading"/></div>');
            
            $.post("ajax-file/tenders.php", {page: page, id: id}, function(data) {
                showsidecate();
                $('#res').html(data);
            }).fail(function() {
                $('#res').html('<div class="alert alert-danger">Failed to load tenders</div>');
            });
        }
        
        function showAuction(page, id) {
            $('ul#sidebarTabs li').removeClass('ho');
            $('#tabbb' + id).addClass('ho');
            
            $('.xx').removeClass('on').addClass('off');
            $('#aaa' + id).addClass('on').removeClass('off');
            
            $('#res1').html('<div style="width:100%; padding-top:8%;" align="center">' +
                           '<img src="images/horizontal_loading.gif" alt="Loading"/></div>');
            
            $.post("ajax-file/auctions.php", {page: page, id: id}, function(data) {
                $('#res1').html(data);
            }).fail(function() {
                $('#res1').html('<div class="alert alert-danger">Failed to load auctions</div>');
            });
        }
        
        function showsidecate() {
            $('#showsideleft').html('<img src="http://egyptmart.shop/images/horizontal_loading.gif">');
            
            $.post("showidecate.php", {}, function(data) {
                $('#showsideleft').html(data);
            }).fail(function() {
                $('#showsideleft').html('<div class="alert alert-danger">Failed to load categories</div>');
            });
        }
        
        $(document).ready(function() {
            // Initialize first category
            <?php if (!empty($firstCategoryId)): ?>
            showLeadMain(1, <?php echo $firstCategoryId; ?>);
            <?php endif; ?>
        });
    </script>
</head>
<body class="search-show-box-buyleads tranders">
<div class="q_hm1">
    
    <!-- Header -->
    <?php include "includes/header_new.php"; ?>
    
    <div class="inner_wrapper" style="margin-top:40px;">
        <p class="q_c3"></p>
        
        <!-- Left Sidebar -->
        <div class="lft1 lfl fl col-md-3 col-sm-3 col-xs-12">
            <span class="mobile-menu"><i class="fa fa-bars" aria-hidden="true"></i></span>
            <p class="bg bp1 fl d1 a6 bo col-md-3 col-sm-3 col-xs-12">
                <img alt="" src="css/img/my-marketA.png">
            </p>
            
            <!-- Dynamic Menu -->
            <div id='cssmenu' style="width:100% !important; margin:0 !important; padding:0 !important;">
                <div id="showsideleft"></div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="tender-right-side">
            
            <!-- Top Navigation -->
            <div class="q_bt1 w1 fl w3 col-md-9 col-sm-9 col-xs-12">
                <a class="cb2" href="manage-sell-offer.php" rel="nofollow">عروضى التجارية</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                <a class="cb2" href="manage-tenders.php" rel="nofollow">مناقصاتى</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                <a class="cb2" href="manage-tender-alert.php" rel="nofollow">إدارة إشعارات مناقصات</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                <a class="q_r" href="post-tender.php" rel="nofollow">
                    <img src="images/zero.gif" alt="">أنشر مناقصة
                </a>&nbsp;&nbsp;|&nbsp;&nbsp;
                <a class="q_r" href="post-auction.php" rel="nofollow">
                    <img src="images/zero.gif" alt="">أنشر مزايدة
                </a>
            </div>
            
            <!-- Banner -->
            <div class="tender-banner-wrap">
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <a href="http://www.egyptmart.shop/post-tender.php" target="_blank">
                        <div class="tender__banner-background" 
                             style="background-image:url('http://www.egyptmart.shop/images/tender_banner.jpg'); background-size:cover;">
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="mid fl col-md-12 col-sm-12 col-xs-12">
                <div class="bx fl" style="width:67%;">
                    
                    <!-- Section Header -->
                    <p class="c4 f4 g5 lbl">
                        <span>آخر المناقصات</span>
                        <a href="http://egyptmart.shop/auctions.php" class="Up_coming_Auction">المزايدات القادمة</a>
                    </p>
                    
                    <div class="latest-upcoming">
                        
                        <!-- Category Tabs -->
                        <div class="tbl fl">
                            <ul id="sidebarTabs">
                                <?php
                                $firstCategoryId = 0;
                                $sql_cat = "SELECT pc.*, COUNT(t.tnd_id) as tender_count 
                                           FROM product_category pc 
                                           JOIN product_category pc1 ON pc1.pc_parent_id = pc.pc_id 
                                           JOIN product_category pc2 ON pc2.pc_parent_id = pc1.pc_id 
                                           JOIN tender t ON t.tnd_pc_id = pc2.pc_id 
                                           WHERE t.tnd_approval_status = '1' 
                                             AND pc.pc_status = '1' 
                                             AND TO_DAYS(t.tnd_due_date) >= TO_DAYS(NOW()) 
                                             AND t.tnd_status = '1' 
                                             {$sql_tnd_ck}
                                           GROUP BY pc.pc_id 
                                           ORDER BY tender_count DESC";
                                
                                $res_cat = mysqli_query($con, $sql_cat);
                                $i = 1;
                                
                                while ($row_cat = mysqli_fetch_object($res_cat)) {
                                    if ($i == 1) {
                                        $firstCategoryId = $row_cat->pc_id;
                                    }
                                    ?>
                                    <li onclick="showLead('1', '<?php echo (int)$row_cat->pc_id; ?>');" 
                                        <?php echo ($i == 1) ? 'class="ho"' : ''; ?> 
                                        id="tabbb<?php echo $i; ?>">
                                        <a class="bgf cm1 cp" style="background:url('upload/category/<?php echo htmlspecialchars($row_cat->pc_image ?? '', ENT_QUOTES, 'UTF-8'); ?>') no-repeat scroll 0% 0% transparent;"></a>
                                        <?php echo htmlspecialchars($row_cat->pc_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        <span class="badge">(<?php echo (int)($row_cat->tender_count ?? 0); ?>)</span>
                                    </li>
                                    <?php
                                    $i++;
                                }
                                ?>
                            </ul>
                        </div>
                        
                        <!-- Tenders List Container -->
                        <div id="res"></div>
                        
                    </div>
                </div>
                
                <!-- Right Sidebar - Related Tenders -->
                <div class="ryt fl" style="padding:7px; width:28%; float:right;">
                    
                    <?php
                    $sql_t = "SELECT t.*, pc.pc_name, u.*, bf.* 
                             FROM tender t
                             JOIN product_category pc ON t.tnd_pc_id = pc.pc_id
                             JOIN user u ON t.tnd_usr_id = u.usr_id
                             JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
                             WHERE t.tnd_approval_status = '1' 
                               AND pc.pc_status = '1' 
                               AND TO_DAYS(t.tnd_due_date) >= TO_DAYS(NOW()) 
                               {$sql_tnd_ck}
                               AND t.tnd_status = '1' 
                             ORDER BY RAND() 
                             LIMIT 5";
                    
                    $res_t = mysqli_query($con, $sql_t);
                    
                    if (mysqli_num_rows($res_t) > 0):
                    ?>
                    <div class="mb1 c3" style="border:1px solid #ccc; border-radius:5px; background:#fff;">
                        <p class="bg bxt"><img alt="" src="images/zero.gif" height="1" width="1"></p>
                        
                        <div class="bbx cln">
                            <p class="bxh f2 color_coding" style="color:#c30000;">المناقصات ذات الصلة</p>
                            
                            <div style="display:block;" id="d2">
                                <?php
                                $n = 1;
                                while ($row_t = mysqli_fetch_object($res_t)):
                                    $details_length = strlen($row_t->tnd_details ?? '');
                                    $tender_token = rand(1000, 9999) . md5((string)$row_t->tnd_id);
                                ?>
                                    <?php if ($n > 1): ?>
                                        <br class="c3">
                                    <?php endif; ?>
                                    
                                    <p class="lnh1">
                                        <b class="cor lnh1">
                                            <a href="tender-details.php?id=<?php echo $tender_token; ?>" 
                                               style="text-decoration:none; color:#E84000">
                                                <?php echo htmlspecialchars($row_t->tnd_heading ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                            <br>
                                            <span class="cb2"><?php echo htmlspecialchars(get_country_name((int)($row_t->country ?? 0)), ENT_QUOTES, 'UTF-8'); ?></span>
                                        </b>
                                        <br>
                                        <?php echo htmlspecialchars(substr(strip_tags($row_t->tnd_details ?? ''), 0, 120), ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if ($details_length > 120): ?>
                                            ...
                                        <?php endif; ?>
                                    </p>
                                    
                                    <?php if ($details_length > 120): ?>
                                        <p class="c3 pa1 rm tr">
                                            <a href="tender-details.php?id=<?php echo $tender_token; ?>" target="_blank">Read More...</a>
                                        </p>
                                    <?php endif; ?>
                                    
                                <?php
                                    $n++;
                                endwhile;
                                ?>
                            </div>
                        </div>
                        
                        <p class="bg bxb"><img alt="" src="images/zero.gif" height="1" width="1"></p>
                    </div>
                    <?php endif; ?>
                    
                </div>
                
                <p class="q_c3"><br></p>
                
                <!-- Advertisement -->
                <div class="c3 tenders-arab">
                    <?php
                    $sql_adv = "SELECT * FROM advertisement 
                               WHERE adv_imagewidth = '239' 
                                 AND adv_imageheight = '186' 
                                 AND adv_status = '1' 
                               ORDER BY RAND() 
                               LIMIT 1";
                    $res_adv = mysqli_query($con, $sql_adv);
                    
                    if (mysqli_num_rows($res_adv) > 0):
                        $row_adv = mysqli_fetch_object($res_adv);
                        ?>
                        <a href="//<?php echo htmlspecialchars($row_adv->adv_link ?? '', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                            <img src="upload/advertisement/<?php echo htmlspecialchars($row_adv->adv_img ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                 class="banner_img" alt="Advertisement"/>
                        </a>
                    <?php else: ?>
                        <img src="upload/advertisement/239-186-advertisement.png" width="239" height="186" alt="Advertisement"/>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </div>
    
    <p class="q_c3"><br><br></p>
</div>

<!-- Overlay Layer -->
<div id="bl_overlay_layer" class="layer" style="display:none">
    <div class="bl_overlay"></div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script>
    // Initialize side categories on page load
    $(document).ready(function() {
        showsidecate();
    });
</script>

</body>
</html>
<?php ob_end_flush(); ?>