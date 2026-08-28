<?php
declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

global $con;

// =============================================
// بناء شروط الموقع (Location Conditions)
// =============================================
$sql_pd_ck = ""; // سيتم استخدامه في الاستعلامات
$sql_br_ck = "";
$sql_so_ck = "";

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    
    $sql_br_ck = " AND (
        (br_preferred_supplier_location = 'domestic' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id)) 
        OR 
        (br_preferred_supplier_location = 'any' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id))
        OR
        (br_preferred_supplier_location = 'my_city' AND br_u_id IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile 
            WHERE bnsprof_city = (SELECT ct_id FROM city WHERE ct_cn_id = $loc_id LIMIT 1)
        ))
    )";
    
    $sql_so_ck = " AND (
        (so_preferred_buyer_location = 'domestic' AND so_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id)) 
        OR 
        (so_preferred_buyer_location = 'any' AND so_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id))
        OR
        (so_preferred_buyer_location = 'my_city' AND so_usr_id IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile 
            WHERE bnsprof_city = (SELECT ct_id FROM city WHERE ct_cn_id = $loc_id LIMIT 1)
        ))
    )";
} else {
    $location_geo_country = $location_geo_country ?? '';
    
    $sql_br_ck = " AND (
        (br_preferred_supplier_location = 'any')
        OR
        (br_preferred_supplier_location = 'abroad' AND br_u_id NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
    
    $sql_so_ck = " AND (
        (so_preferred_buyer_location = 'any')
        OR
        (so_preferred_buyer_location = 'abroad' AND so_usr_id NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
}

// =============================================
// تحديد ترتيب التصنيفات
// =============================================
if (get_page_settings('25') == 'manual') {
    $sql_order = " ORDER BY pc_order, pc_name";
} else {
    $sql_order = " ORDER BY pc_order";
}

// =============================================
// جلب التصنيفات للقائمة الجانبية
// =============================================
$sql_cmt_cnt1 = "SELECT DISTINCT pd_subcat_id 
                 FROM products p
                 INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
                 INNER JOIN country c ON c.cn_id = p.pd_currency
                 INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                 INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                 INNER JOIN smembership_plan sp ON sp.mp_id = pm.p_id
                 WHERE p.pd_status = '1'
                 AND p.pd_image != ''
                 AND pm.expiry_date > " . time() . "
                 $sql_pd_ck
                 ORDER BY FIELD(sp.mp_id, '5', '4', '3', '15')";

$result_dd_mnu = mysqli_query($con, $sql_cmt_cnt1);
$pc_id_arr = [];

if ($result_dd_mnu) {
    while ($row = mysqli_fetch_assoc($result_dd_mnu)) {
        $pc_id_arr[] = (int)$row['pd_subcat_id'];
    }
}

$parent_category_arr = [];
$master_category_arr = [];

if (!empty($pc_id_arr)) {
    $ids = implode("','", $pc_id_arr);
    
    // جلب التصنيفات الأصلية
    $sql_parent = "SELECT DISTINCT pc_parent_id 
                   FROM product_category 
                   WHERE pc_id IN ('$ids') AND pc_status = '1'";
    $result_parent = mysqli_query($con, $sql_parent);
    
    while ($row = mysqli_fetch_assoc($result_parent)) {
        $parent_category_arr[] = (int)$row['pc_parent_id'];
    }
    
    if (!empty($parent_category_arr)) {
        $parent_ids = implode("','", $parent_category_arr);
        
        // جلب التصنيفات الرئيسية
        $sql_master = "SELECT DISTINCT pc_parent_id 
                       FROM product_category 
                       WHERE pc_id IN ('$parent_ids') AND pc_status = '1'";
        $result_master = mysqli_query($con, $sql_master);
        
        while ($row = mysqli_fetch_assoc($result_master)) {
            $master_category_arr[] = (int)$row['pc_parent_id'];
        }
    }
}

// جلب التصنيفات الرئيسية للقائمة
$master_categories = [];
if (!empty($master_category_arr)) {
    $master_ids = implode("','", $master_category_arr);
    $sql_master_cats = "SELECT pc_id, pc_image, pc_name 
                        FROM product_category 
                        WHERE pc_id IN ('$master_ids') AND pc_status = '1'
                        ORDER BY pc_order ASC";
    $result_master_cats = mysqli_query($con, $sql_master_cats);
    
    while ($row = mysqli_fetch_assoc($result_master_cats)) {
        $master_categories[] = $row;
    }
}

// جلب الإعلان
$adv_sql = "SELECT adv_link, adv_img FROM advertisement 
            WHERE adv_imagewidth = '468' AND adv_imageheight = '60' 
            AND adv_status = '1' 
            ORDER BY RAND() 
            LIMIT 1";
$adv_result = mysqli_query($con, $adv_sql);
$adv_row = mysqli_fetch_assoc($adv_result);
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link href="css/eto-index-2.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <script type="text/javascript" src="js/leftdd-1.js"></script>
    
    <script>
    $(document).ready(function() {
        showSaleoffers(1);
        showBuyleads(1);
        
        $('.mobile-menu').click(function() {
            $('.search-show-box-buyleads .left-side-bar-sale-offer #cssmenu').toggleClass('menu-active');
        });
    });
    
    function showSaleoffers(page) {
        $.post("ajax-file/showLatestSelloffers.php", {page: page}, function(data) {
            $('#so').html(data);
        });
    }
    
    function showBuyleads(page) {
        $.post("ajax-file/showLatestBuyleads.php", {page: page}, function(data) {
            $('#bl').html(data);
        });
    }
    </script>
</head>
<body class="search-show-box-buyleads sale-offers search-page-now">
    <div class="q_hm1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        
        <div class="inner_wrapper">
            <div class="bt" style="display:none;"><img src="images/z.gif" alt="" height="1" width="1"></div>
            
            <p class="q_c3"></p>
            
            <!-- القائمة الجانبية اليسرى -->
            <div class="left-side-bar-sale-offer" style="float:left">
                <span class="mobile-menu desktop-mobile-menu"><i class="fa fa-bars" aria-hidden="true"></i></span>
                
                <div id='cssmenu' style="float:left; width:200px;">
                    <ul>
                        <?php if (!empty($master_categories)): ?>
                            <?php foreach ($master_categories as $cat): 
                                $pc_id = (int)$cat['pc_id'];
                                $pc_name = htmlspecialchars($cat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8');
                                $token = rand(10, 9999) . md5((string)$pc_id);
                            ?>
                            <li class='has-sub'>
                                <a href="category.php?token=<?php echo $token; ?>">
                                    <span><?php echo $pc_name; ?></span>
                                </a>
                                
                                <?php if (!empty($parent_category_arr)): 
                                    $parent_ids = implode("','", $parent_category_arr);
                                    $sql_sub = "SELECT pc_id, pc_name 
                                                FROM product_category 
                                                WHERE pc_parent_id = ? 
                                                AND pc_status = '1' 
                                                AND pc_id IN ('$parent_ids')
                                                $sql_order";
                                    $stmt_sub = mysqli_prepare($con, $sql_sub);
                                    mysqli_stmt_bind_param($stmt_sub, 'i', $pc_id);
                                    mysqli_stmt_execute($stmt_sub);
                                    $result_sub = mysqli_stmt_get_result($stmt_sub);
                                    
                                    if (mysqli_num_rows($result_sub) > 0):
                                ?>
                                <ul>
                                    <?php while ($sub = mysqli_fetch_assoc($result_sub)): 
                                        $sub_id = (int)$sub['pc_id'];
                                        $sub_name = htmlspecialchars(ucwords($sub['pc_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <li>
                                        <a href="products.php?c=<?php echo md5((string)$sub_id); ?>">
                                            <span><?php echo $sub_name; ?></span>
                                        </a>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                                <?php 
                                    endif;
                                    mysqli_stmt_close($stmt_sub);
                                endif; ?>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="ptag text-danger" style="padding:5px;">
                                Currently, this country has no businesses to display!
                            </li>
                        <?php endif; ?>
                        
                        <li><a href="dir.php">شاهد كل التصنيفات</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- القسم الأيمن (المحتوى الرئيسي) -->
            <div class="right-side-bar-sale-offer" style="float:left">
                <div class="so_q_bt11 so_w3">
                    <a class="cb2" href="myproduct-sell.php">تلقى إشعارات طلبات شراء</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="cb2" href="buyleads.php">صفحة طلبات الشراء</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="cb2" href="post-buy-req.php" rel="nofollow">أنشر طلب شراء</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="cb2" href="tenders.php" rel="nofollow">مناقصات</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="cb2" href="manage-sell-offer.php" rel="nofollow">إدارة عروض البيع</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="cb2" href="manage-buy-requirement.php" rel="nofollow">إدارة طلبات الشراء</a>&nbsp;&nbsp;|&nbsp;&nbsp;
                    <a class="cb2" href="manage-selloffer-alert.php" rel="nofollow">تلقى إشعارات عروض بيع</a>
                </div>
                
                <div class="bxc fd">
                    <!-- الإعلان العلوي -->
                    <div class="m1 bxt fd min-container-saleoffer" style="text-align:right; margin-top:1px">
                        <div id="m4t">
                            <?php if ($adv_row): ?>
                                <a href="//<?php echo htmlspecialchars($adv_row['adv_link'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                    <img src="upload/advertisement/<?php echo htmlspecialchars($adv_row['adv_img'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                         alt="Advertisement" border="0" height="60" hspace="0" vspace="0" width="468">
                                </a>
                            <?php else: ?>
                                <img src="upload/advertisement/468-60-advertisement.png" alt="" 
                                     border="0" height="60" hspace="0" vspace="0" width="468">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <p class="c3"><br></p>
                    
                    <!-- عروض البيع -->
                    <div id="so"></div>
                    
                    <!-- طلبات الشراء -->
                    <div id="bl"></div>
                    
                    <p class="c3 p1"></p>
                </div>
            </div>
        </div>
        
        <p class="c3"><br></p>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>