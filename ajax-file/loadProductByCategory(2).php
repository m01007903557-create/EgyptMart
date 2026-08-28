<?php
/**
 * File: ajax/loadProductByCategory.php
 * Description: تحميل وعرض منتجات التصنيف مع القوائم الجانبية والتصفية
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
// session_start(); // تم تعطيلها مؤقتاً

require_once __DIR__ . '/../common.php';

// التحقق من وجود الصفحة ومعرف التصنيف
if (!isset($_POST['page']) || !isset($_POST['id'])) {
    http_response_code(400);
    die("Invalid request parameters");
}

$page = (int)$_POST['page'];
$pc_id = mysqli_real_escape_string($GLOBALS['con'], $_POST['id']);
$is_sub = isset($_POST['is_sub']) ? $_POST['is_sub'] : false;

// إعدادات التصفح
$cur_page = $page;
$page -= 1;
$per_page = 40;
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

global $con;

// =============================================
// 1. بناء شروط الموقع (Location Conditions)
// =============================================
$sql_pd_ck = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id)) 
        OR 
        (pd_preferred_buyer_location = 'any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id))
        OR
        (pd_preferred_buyer_location = 'my_city' AND pd_uid IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile 
            WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = $loc_id)
        ))
    )";
} else {
    $location_geo_country = $location_geo_country ?? '';
    
       $geo_code = isset($location_geo_country) ? $location_geo_country : '';
 
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = '$geo_code')
        ))
    )";
}

// =============================================
// 2. جلب معلومات التصنيف
// =============================================
$sql_pcat = "SELECT m.pc_id as mcat_id, m.pc_name as mcat_name, 
                    c.pc_id as cat_id, c.pc_name as cat_name 
             FROM product_category m
             INNER JOIN product_category c ON m.pc_id = c.pc_parent_id
             WHERE MD5(c.pc_id) = ? 
             ORDER BY m.pc_order ASC 
             LIMIT 1";

$stmt_pcat = mysqli_prepare($con, $sql_pcat);
mysqli_stmt_bind_param($stmt_pcat, 's', $pc_id);
mysqli_stmt_execute($stmt_pcat);
$result_pcat = mysqli_stmt_get_result($stmt_pcat);
$row_pcat = mysqli_fetch_array($result_pcat);
mysqli_stmt_close($stmt_pcat);

// =============================================
// 3. جلب التصنيفات الفرعية
// =============================================
$parent_prefix = $is_sub ? '' : 'parent_';
$sql_check1 = "SELECT pc_id FROM product_category 
               WHERE MD5(pc_{$parent_prefix}id) = ? 
               ORDER BY pc_order ASC";

$stmt_check = mysqli_prepare($con, $sql_check1);
mysqli_stmt_bind_param($stmt_check, 's', $pc_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

$pc_id_arr = [];
while ($data = mysqli_fetch_assoc($result_check)) {
    $pc_id_arr[] = (int)$data['pc_id'];
}
mysqli_stmt_close($stmt_check);

$ids = !empty($pc_id_arr) ? implode("','", $pc_id_arr) : '0';

// =============================================
// 4. استعلام جلب المنتجات
// =============================================
$sql_prd = "SELECT p.*, mu.*, c.*, bp.*, pm.*, sp.* 
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
            AND p.pd_subcat_id IN ('$ids')
            ORDER BY FIELD(sp.mp_id,'5','4','3','15')
            LIMIT ?, ?";

$stmt_prd = mysqli_prepare($con, $sql_prd);
mysqli_stmt_bind_param($stmt_prd, 'ii', $start, $per_page);
mysqli_stmt_execute($stmt_prd);
$result_prd = mysqli_stmt_get_result($stmt_prd);

// حساب إجمالي المنتجات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM products p
                  INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
                  INNER JOIN country c ON c.cn_id = p.pd_currency
                  INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                  INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                  WHERE p.pd_status = '1' 
                  AND p.pd_image != '' 
                  AND pm.expiry_date > " . time() . "
                  $sql_pd_ck
                  AND p.pd_subcat_id IN ('$ids')";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row = mysqli_fetch_assoc($result_count);
$count = (int)($row['count'] ?? 0);

$no_of_paginations = (int)ceil($count / $per_page);

// =============================================
// 5. جلب بيانات القائمة الجانبية (من اليسار)
// =============================================
// جلب التصنيفات الرئيسية
$sql_order = " ORDER BY pc_order ASC";
$sql_dd_mnu = "SELECT pc_id, pc_name, pc_image 
               FROM product_category 
               WHERE pc_parent_id = 0 AND pc_status = 1 
               $sql_order";

$res_dd_mnu = mysqli_query($con, $sql_dd_mnu);

// جلب التصنيفات الفرعية للقائمة
$sql_subcat = "SELECT p.pc_id, p.pc_name, 
                      (SELECT COUNT(*) FROM products p2
                       INNER JOIN measurement_unit mu ON mu.mu_id = p2.pd_unit
                       INNER JOIN country c ON c.cn_id = p2.pd_currency
                       INNER JOIN business_profile bp ON bp.bnsprof_uid = p2.pd_uid
                       INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                       WHERE p2.pd_status = '1' 
                       AND p2.pd_image != '' 
                       AND pm.expiry_date > " . time() . "
                       $sql_pd_ck
                       AND p2.pd_subcat_id = p.pc_id) as tot_prod 
               FROM product_category p 
               WHERE MD5(p.pc_{$parent_prefix}id) = ? 
               ORDER BY p.pc_order ASC";

$stmt_subcat = mysqli_prepare($con, $sql_subcat);
mysqli_stmt_bind_param($stmt_subcat, 's', $pc_id);
mysqli_stmt_execute($stmt_subcat);
$result_subcat = mysqli_stmt_get_result($stmt_subcat);

// =============================================
// 6. جلب الدول والمدن للتصفية
// =============================================
$countries_list = [];
$states_list = [];

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $country_sql = "SELECT DISTINCT s.state_id, s.state_name 
                    FROM products p
                    INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
                    INNER JOIN country c ON c.cn_id = p.pd_currency
                    INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                    INNER JOIN states s ON s.state_id = bp.bnsprof_state
                    INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                    INNER JOIN smembership_plan sp ON sp.mp_id = pm.p_id
                    WHERE s.state_cn_id = $loc_id
                    AND p.pd_status = '1'
                    AND p.pd_image != ''
                    AND pm.expiry_date > " . time() . "
                    $sql_pd_ck
                    AND p.pd_subcat_id IN ('$ids')
                    ORDER BY s.state_name ASC";
    
    $states_result = mysqli_query($con, $country_sql);
    while ($state = mysqli_fetch_assoc($states_result)) {
        $states_list[] = $state;
    }
} else {
    $country_sql = "SELECT DISTINCT cn_id, cn_name 
                    FROM country 
                    WHERE cn_status = 1 
                    ORDER BY cn_name ASC 
                    LIMIT 50";
    $countries_result = mysqli_query($con, $country_sql);
    while ($country = mysqli_fetch_assoc($countries_result)) {
        $countries_list[] = $country;
    }
}

// =============================================
// 7. جلب أنواع العضوية للتصفية
// =============================================
$mtqury = "SELECT sp.mp_id, sip.mst_icon, sp.mst_name 
           FROM smembership_plan sp 
           INNER JOIN smembership_icon_plan sip ON sp.mp_id = sip.mp_id 
           WHERE sp.mp_status != '0'";
$membership_result = mysqli_query($con, $mtqury);
$membership_types = [];
while ($mtrow = mysqli_fetch_assoc($membership_result)) {
    $membership_types[] = $mtrow;
}
?>

<!-- تضمين ملفات CSS و JS -->
<link href="https://egyptmart.shop/company/css/colorbox.css" type="text/css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.3/jquery.colorbox.js"></script>
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/js/jquery.autocomplete.js"></script>

<style>
    .bt {
        background-position: 0 -240px;
        margin: 18px 0 30px 0;
        clear: left;
        height: 0px !Important;
        background-color: transparent;
        color: transparent;
    }
    .prc-right-side {
        padding-left: 4px !important;
    }
    .middle-control-part {
        padding: 10px 0;
        background: #fff;
        width: 100%;
    }
    button.btn.btn-default.border-radius-0.txt-bold.bold-xs.btn-white.text-capitalize {
        border-radius: 0px;
        background: #fff !important;
        color: #000;
        padding: 10px 23px;
    }
    .cnt_supplier {
        width: 83%;
        margin-left: 9%;
        margin-top: 9px;
        margin-bottom: 9px;
    }
    .cnt-phone {
        width: 83%;
        margin-left: 9%;
    }
    .checkbox-inline {
        padding-left: 0px !important;
    }
    .rightside_bar_slider {
        width: 19% !important;
    }
    @media only screen and (max-width: 767px) {
        .rightside_bar_slider {
            width: 100% !important;
        }
    }
    .pagination ul {
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    .pagination li {
        display: inline;
    }
    .pagination li a, li span {
        text-decoration: none;
    }
    .pagination li.active a {
        background-color: blue;
        color: white;
    }
    .pagination li a:hover:not(.active) {
        background-color: #ddd;
    }
</style>

<div class="col-md-3 col-sm-3 col-xs-12 col-lg-2 prc-left-side">
    <h3 class="togle_style" style="font-size:19px; cursor:pointer; font-weight:700;" onclick="toggle_menu();">
        <span class="fa fa-list"></span>&nbsp; أسـواق تجارتى &nbsp;
        <i id="uparrow" class="fa fa-angle-up" style="font-size:18px; display:none;"></i>
        <i id="downarrow" class="fa fa-angle-down" style="font-size:18px;"></i>
    </h3>
    
    <div style="padding-left:0px; padding-right:10px;" class="left-side-bar-sale-offer">
        <link rel="stylesheet" href="../css/menu_styles.css" type="text/css" />
        
        <div id='cssmenu' class="" style="float:left; width:200px; display:none">
            <ul>
                <?php while ($row_dd_mnu = mysqli_fetch_object($res_dd_mnu)): ?>
                <li class='has-sub'>
                    <a href="category.php?token=<?php echo rand(10, 9999) . md5((string)$row_dd_mnu->pc_id); ?>">
                        <span><?php echo htmlspecialchars($row_dd_mnu->pc_name ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                    <ul>
                        <?php
                        $sql_dd_cmnu = "SELECT pc_id, pc_name FROM product_category 
                                        WHERE pc_parent_id = ? AND pc_status = 1 
                                        $sql_order";
                        $stmt_cmnu = mysqli_prepare($con, $sql_dd_cmnu);
                        mysqli_stmt_bind_param($stmt_cmnu, 'i', $row_dd_mnu->pc_id);
                        mysqli_stmt_execute($stmt_cmnu);
                        $res_dd_cmnu = mysqli_stmt_get_result($stmt_cmnu);
                        
                        while ($row_dd_cmnu = mysqli_fetch_object($res_dd_cmnu)):
                        ?>
                        <li>
                            <a href="products.php?c=<?php echo md5((string)$row_dd_cmnu->pc_id); ?>">
                                <span><?php echo htmlspecialchars(ucwords($row_dd_cmnu->pc_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            </a>
                        </li>
                        <?php 
                        endwhile;
                        mysqli_stmt_close($stmt_cmnu);
                        ?>
                    </ul>
                </li>
                <?php endwhile; ?>
                <li><a href="dir.php">شاهد كل التصنيفات</a></li>
            </ul>
        </div>
        
        <h4><?php echo htmlspecialchars($row_pcat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h4>
        
        <div id="leftCats">
            <?php while ($row_subcat = mysqli_fetch_object($result_subcat)): 
                if ($row_subcat->tot_prod > 0):
            ?>
            <div class="item-list">
                <a style="cursor:pointer;" onclick="loadProductByCategory(1, '<?php echo md5((string)$row_subcat->pc_id); ?>', true);">
                    <?php echo htmlspecialchars(ucwords($row_subcat->pc_name ?? ''), ENT_QUOTES, 'UTF-8') . " (" . (int)$row_subcat->tot_prod . ")"; ?>
                </a>
                <?php if ($is_sub && $is_sub != 'false'): ?>
                <button class="btn btn-xs btn-default border-radius-0" onclick="loadProductByCategory(1, main_cat);" style="padding:0 5px 0 5px">
                    الغاء
                </button>
                <?php endif; ?>
            </div>
            <?php 
                endif;
            endwhile;
            mysqli_stmt_close($stmt_subcat);
            ?>
        </div>
    </div>
</div>

<div class="col-md-9 col-sm-9 col-lg-10 col-xs-12 prc-right-side">
    <div id="final_result" class="col-md-9 col-sm-9 col-xs-12 col-lg-9 new_final">
        
        <!-- أزرار المنتجات/الموردين -->
        <div class="middle-part">
            <button type="button" class="btn btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize" 
                    style="border-top:2px solid #ff7519 !important; border:0px; font-weight:600;">
                منتجـات
            </button>
            <button type="button" class="btn btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize <?php echo $pc_id; ?>" 
                    style="background-color: #F5F7FA !important;">
                <a target="_blank" href="https://egyptmart.shop/catcompany.php?token=<?php echo rand(1000, 9999) . $pc_id; ?>" 
                   style="color:#000; font-weight:600;">موردون</a>
            </button>
        </div>
        
        <!-- شريط التحكم والتصفية -->
        <div class="middle-control-part row" style="margin-left:0px;">
            <div class="col-md-2 col-xs-12">عضوية المورد :</div>
            
            <div class="col-md-10 col-xs-12">
                <?php foreach ($membership_types as $mtrow): ?>
                <label class="checkbox-inline">
                    <input type="checkbox" class="search_filter" name="mst_type" value="<?php echo (int)$mtrow['mp_id']; ?>">
                    <img src="admin/images/<?php echo htmlspecialchars($mtrow['mst_icon'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                         width="20px" height="20px;" style="margin-right:5px;">
                    <span class="txt-gray text-uppercase"><?php echo htmlspecialchars($mtrow['mst_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </label>
                <?php endforeach; ?>
                
                <button class="btn btn-xs btn-default border-radius-0" onclick="filter_member()" style="padding:0 5px 0 5px; margin-left:13px;">OK</button>
                <button class="btn btn-xs btn-default border-radius-0" onclick="$('input[name=mst_type]').each(function(){this.checked = false;}); filter_member()" style="padding:0 5px 0 5px">الغاء</button>
            </div>
            
            <div class="col-md-5" style="margin-top:8px">
                <label class="checkbox-inline min_order1" style="padding-left:0px; padding-right:15px;">بـلـد المورديـن :</label>
                <span id="showcnt" style="">البلاد والمحافظات العارضة &nbsp;<i class="fa fa fa-sort-desc"></i></span>
            </div>
            
            <div class="col-md-3 min_quan" style="display:inline-block;">
                <label for="min_order" class="checkbox-inline min_order">أقل طلب 
                    <input id="min_order" type="text" value="" style="width:50px"/>
                </label>
                <button class="btn btn-xs btn-default border-radius-0 min_btn" style="padding:0 5px 0 5px" onclick="filter_member()">OK</button>
            </div>
            
            <div class="col-md-4" style="display:inline-block;">
                <form name="getcitydata" id="getcitydata" method="post" action="search.php?<?php 
                    echo isset($_GET['rctyp']) ? '&rctyp=' . urlencode($_GET['rctyp']) : ''; 
                    echo isset($_GET['keywords']) ? '&keywords=' . urlencode($_GET['keywords']) : ''; 
                ?>">
                    <input type="hidden" id="srchbustype" name="srchbustype" value="srchbustype" />
                    <input type="search" class="" name="scity" id="scity" value="<?php echo htmlspecialchars($_POST['scity'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="إبحث بالمدينـة" />
                    <button type="submit" onclick="filter_member(); return false;" class="scity_btn">
                        <i class="fa fa-search fa_search"></i>
                    </button>
                    <div id="city_suggesstion_box"></div>
                </form>
            </div>
        </div>
        
        <!-- قائمة الدول والمدن -->
        <div class="countries" style="display:none">
            <div class="countries_inner">
                <?php if (isset($_COOKIE['loc_id'])): ?>
                    <?php foreach ($states_list as $state): ?>
                    <span class="outer_c">
                        <input type="checkbox" name="state_sel" value="<?php echo (int)$state['state_id']; ?>">
                        <span style="color:blue;"><?php echo htmlspecialchars($state['state_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        <i class="fa fa-angle-down cnt_state" id="<?php echo (int)$state['state_id']; ?>" style="font-size:15px; margin-left:5px; cursor:pointer;"></i>
                    </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($countries_list as $country): ?>
                    <span class="outer_c">
                        <input type="checkbox" name="country_sel" value="<?php echo (int)$country['cn_id']; ?>">
                        <span style="color:blue;"><?php echo htmlspecialchars($country['cn_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        <i class="fa fa-angle-down cnt_state" id="<?php echo (int)$country['cn_id']; ?>" style="font-size:15px; margin-left:5px; cursor:pointer;"></i>
                    </span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="state_section"></div>
            <button class="btn btn-xs btn-default border-radius-0" style="padding:0 5px 0 5px" onclick="filter_member()">OK</button>
        </div>
        
        <!-- مؤشر التحميل -->
        <div id="header_load_sub" style="text-align:center; display:none">
            <img src="http://egyptmart.shop/loadingp.gif" style="height:125px"/>
        </div>
        
        <!-- عرض المنتجات -->
        <div class="als-container" id="product_slider" style="border-radius:5px;">
            <div class="als-viewport" align="center">
                <ul class="als-wrapper" style="width:101.1%">
                    <?php if (mysqli_num_rows($result_prd) > 0): ?>
                        <?php while ($row_prd = mysqli_fetch_object($result_prd)): 
                            $product_name = $row['pd_title'] ?? 'Product';
                            $product_id = (int)$row_prd->pd_id;
                            $product_title = htmlspecialchars($row_prd->pd_title ?? '', ENT_QUOTES, 'UTF-8');
                            $product_title_short = htmlspecialchars(substr($product_title, 0, 30), ENT_QUOTES, 'UTF-8');
                            $product_image = htmlspecialchars($row_prd->pd_image ?? '', ENT_QUOTES, 'UTF-8');
                            $bnsprof_id = (int)($row_prd->bnsprof_id ?? 0);
                            $pd_subcat_id = (int)($row_prd->pd_subcat_id ?? 0);
                            $pd_uid = (int)($row_prd->pd_uid ?? 0);
                            $bnsprof_city = (int)($row_prd->bnsprof_city ?? 0);
                            $bnsprof_state = (int)($row_prd->bnsprof_state ?? 0);
                            $pd_min_order_qty = (int)($row_prd->pd_min_order_qty ?? 0);
                            $mu_name = htmlspecialchars($row_prd->mu_name ?? '', ENT_QUOTES, 'UTF-8');
                            $cn_currency = htmlspecialchars($row_prd->cn_currency ?? '', ENT_QUOTES, 'UTF-8');
                            $pd_fob_price = htmlspecialchars($row_prd->pd_fob_price ?? '', ENT_QUOTES, 'UTF-8');
                            $mst_name = htmlspecialchars($row_prd->mst_name ?? '', ENT_QUOTES, 'UTF-8');
                            $cn_name = htmlspecialchars($row_prd->cn_name ?? '', ENT_QUOTES, 'UTF-8');
                            
                            // جلب معلومات الموقع
                            $location_name = $cn_name;
                            if ($bnsprof_city > 0) {
                                if (isset($_COOKIE['loc_id'])) {
                                    $city_query = "SELECT ct_name FROM city WHERE ct_id = ? LIMIT 1";
                                    $stmt_city = mysqli_prepare($con, $city_query);
                                    mysqli_stmt_bind_param($stmt_city, 'i', $bnsprof_city);
                                    mysqli_stmt_execute($stmt_city);
                                    $city_result = mysqli_stmt_get_result($stmt_city);
                                    $city_row = mysqli_fetch_object($city_result);
                                    $cn_city = $city_row ? htmlspecialchars($city_row->ct_name ?? '', ENT_QUOTES, 'UTF-8') : '';
                                    mysqli_stmt_close($stmt_city);
                                    
                                    $state_query = "SELECT state_name FROM states WHERE state_id = ? LIMIT 1";
                                    $stmt_state = mysqli_prepare($con, $state_query);
                                    mysqli_stmt_bind_param($stmt_state, 'i', $bnsprof_state);
                                    mysqli_stmt_execute($stmt_state);
                                    $state_result = mysqli_stmt_get_result($stmt_state);
                                    $state_row = mysqli_fetch_object($state_result);
                                    $cn_state = $state_row ? htmlspecialchars($state_row->state_name ?? '', ENT_QUOTES, 'UTF-8') : '';
                                    mysqli_stmt_close($stmt_state);
                                    
                                    $location_name = $cn_city . " - " . $cn_state;
                                } else {
                                    $city_query = "SELECT ct_cn_id FROM city WHERE ct_id = ? LIMIT 1";
                                    $stmt_city = mysqli_prepare($con, $city_query);
                                    mysqli_stmt_bind_param($stmt_city, 'i', $bnsprof_city);
                                    mysqli_stmt_execute($stmt_city);
                                    $city_result = mysqli_stmt_get_result($stmt_city);
                                    $city_row = mysqli_fetch_object($city_result);
                                    $ct_cn_id = $city_row ? (int)$city_row->ct_cn_id : 0;
                                    mysqli_stmt_close($stmt_city);
                                    
                                    if ($ct_cn_id > 0) {
                                        $country_query = "SELECT cn_name FROM country WHERE cn_id = ? LIMIT 1";
                                        $stmt_country = mysqli_prepare($con, $country_query);
                                        mysqli_stmt_bind_param($stmt_country, 'i', $ct_cn_id);
                                        mysqli_stmt_execute($stmt_country);
                                        $country_result = mysqli_stmt_get_result($stmt_country);
                                        $country_row = mysqli_fetch_object($country_result);
                                        $location_name = $country_row ? htmlspecialchars($country_row->cn_name ?? '', ENT_QUOTES, 'UTF-8') : $cn_name;
                                        mysqli_stmt_close($stmt_country);
                                    }
                                }
                            }
                            
                            // جلب معلومات الاتصال
                            $row_contact = null;
                            $contact_query = "SELECT mobile1, country_ph_code FROM user WHERE usr_id = ? LIMIT 1";
                            $stmt_contact = mysqli_prepare($con, $contact_query);
                            mysqli_stmt_bind_param($stmt_contact, 'i', $pd_uid);
                            mysqli_stmt_execute($stmt_contact);
                            $contact_result = mysqli_stmt_get_result($stmt_contact);
                            $row_contact = mysqli_fetch_object($contact_result);
                            mysqli_stmt_close($stmt_contact);
                            
                            $mobile = $row_contact ? htmlspecialchars($row_contact->mobile1 ?? '', ENT_QUOTES, 'UTF-8') : '';
                            $phone_code = $row_contact ? htmlspecialchars($row_contact->country_ph_code ?? '', ENT_QUOTES, 'UTF-8') : '';
                        ?>
                        <li class="als-item">
                            <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>&sc=<?php echo rand(10000, 99999) . $pd_subcat_id; ?>#<?php echo $product_id; ?>" 
                               style="text-decoration:none; color:#000" target="_blank">
                                <div style="height:150px;" id="img-div">
                                    <span class="img-helper" style="display:inline-block; height:100%; vertical-align:middle;"></span>
                                    <img src="upload/myproduct/<?php echo $product_image; ?>" 
                                         alt="<?php echo htmlspecialchars(ucwords(substr($product_title, 0, 28)), ENT_QUOTES, 'UTF-8'); ?>"
                                         title="<?php echo $product_title; ?>" />
                                </div>
                                <div style="">
                                    <div class="utext" style=""><b><?php echo htmlspecialchars(substr($product_title_short, 0, 36), ENT_QUOTES, 'UTF-8'); ?></b></div>
                                </div>
                                <div style="text-align:center; margin-top:-8px;">
                                    <span class="span_red"><?php echo $location_name; ?></span>
                                    <div style="height:10%; margin-top:1%; font-size:10px;">
                                        أقل طلب : <span style="color:red; font-weight:600; font-size:13px !important;"><?php echo $pd_min_order_qty; ?>&nbsp;</span>
                                        <span style="color:#9ca1ac;"><?php echo $mu_name; ?></span>
                                    </div>
                                    <div style="line-height:0.8; margin-top:1%; font-size:10px;">
                                        <?php echo $cn_currency; ?>&nbsp;
                                        <span style="color:red; font-weight:600; font-size:13px !important;"><?php echo $pd_fob_price; ?></span> / 
                                        <span style="color:#9ca1ac;"><?php echo $mu_name; ?></span>
                                    </div>
                                    <div style="font-weight:bold; font-size:10px; padding:4px 0;"><?php echo $mst_name; ?></div>
                                </div>
                            </a>
                            
                          
                          
                          
                          
                            <p class="cnt-phone">
    <span class="cnt-phone-inner">
        <img src="images/mobile.png" width="25px">&nbsp;&nbsp;
        <?php if (!empty($_SESSION['uid_indm'])): ?>
        <a href="javascript:void(0)" onclick="
            var pid = <?php echo $product_id; ?>;
            var pname = '<?php echo addslashes($product_name); ?>';
            var qty_from = prompt('الكمية التقريبية (من):');
            if(!qty_from) return;
            var qty_to = prompt('إلى:');
            if(!qty_to) return;
            var details = prompt('التفاصيل:');
            var formData = new FormData();
            formData.append('product_id', pid);
            formData.append('product_name', pname);
            formData.append('qty_from', qty_from);
            formData.append('qty_to', qty_to);
            formData.append('requirement_details', details);
            fetch('/whatsapp_rfq_handler.php', {method:'POST', body:formData})
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('✅ Your RFQ has been noted, suppliers will contact you soon.');
                    window.open(data.whatsapp_url, '_blank');
                } else {
                    alert('❌ ' + data.error);
                }
            });
        " style="color:#25D366; text-decoration:none; font-weight:bold;">
            طلب سعر واتساب
        </a>
        <?php else: ?>
        <a href="/sign-in.php#loginform" style="color:#25D366; text-decoration:none; font-weight:bold;">
            سجل دخول لطلب السعر
        </a>
        <?php endif; ?>
    </span>
</p>
                           
                           
                           
                           
                           
                            <p class="cnt_supplier">
                                <span class="cnt_supplier_inner">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;&nbsp;
                                    <a href="ajax-file/quotationRequest.php?id=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>&pid=<?php echo $product_id; ?>&c=<?php echo $pd_uid; ?>&vform=1"
                                       id="btn_ajax<?php echo $product_id; ?>"
                                       rel="product-send-inquiry" class="inquiry_but" style="color:#fff;">تواصل مع المورد</a>
                                </span>
                            </p>
                            
                            <div class="link pt10px">
                                <script>
                                    $(document).ready(function() {
                                        var uid_ind = '<?php echo $_SESSION['uid_indm'] ?? ''; ?>';
                                        $("#btn_ajax<?php echo $product_id; ?>").click(function(event) {
                                            if (uid_ind == '') {
                                                window.location.href = "https://www.egyptmart.shop/sign-in.php";
                                            } else {
                                                event.preventDefault();
                                                $("#btn_ajax<?php echo $product_id; ?>").colorbox({
                                                    width: "62%",
                                                    height: "89%"
                                                });
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="als-item" style="border:1px solid #484891; margin-top:1%; margin-left:1.5%; border-radius:4px; width:97%; height:350px; color:#F00;">
                            لايوجد منتجات مسجلة لهذا التصنيف
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- روابط التصفح (Pagination) -->
            <?php if ($count > 0): ?>
            <p class="cl"><br></p>
            <p align="center" style="margin-bottom:10px;">
                <nav>
                    <div class="text-center">
                        <ul class="pagination">
                            <?php
                            $totalpages = $no_of_paginations;
                            if ($totalpages > 1):
                                $currentpage = $cur_page;
                                if ($currentpage < 1) $currentpage = 1;
                                if ($currentpage > $totalpages) $currentpage = $totalpages;
                                
                                $range = 3;
                                $link = "javascript:refineProductBySubCategory('{P}','$pc_id')";
                                
                                // رابط الصفحة السابقة
                                if ($currentpage > 1):
                                    $prevpage = $currentpage - 1;
                                    echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', $prevpage, $link) . '"><</a></li>';
                                    
                                    if ($currentpage - $range >= 2):
                                        echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', 1, $link) . '">1</a></li>';
                                        if ($currentpage - $range > 2):
                                            echo '<li class="disabled"><span>...</span></li>';
                                        endif;
                                    endif;
                                endif;
                                
                                // أرقام الصفحات
                                for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++):
                                    if (($x > 0) && ($x <= $totalpages)):
                                        if ($x == $currentpage):
                                        echo '<li class="page-item active"><a class="page-link" href="' . str_replace('{P}', (string)$x, $link) . '">' . $x . '</a></li>';                                        else:
                                            echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', (string)$x, $link) . '">' . $x . '</a></li>';
                                        endif;
                                    endif;
                                endfor;
                                
                                // رابط الصفحة التالية
                                if ($x <= $totalpages):
                                    if ($x < $totalpages):
                                        echo '<li class="disabled"><span>...</span></li>';
                                    endif;
                                    echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', (string)$totalpages, $link) . '">' . $totalpages . '</a></li>';
                                endif;
                                
                                if ($currentpage != $totalpages):
                                    $nextpage = $currentpage + 1;
                                    echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', (string)$nextpage, $link) . '">></a></li>';
                                endif;
                            endif;
                            ?>
                            <li class="page-item">
                                <span style="color:black; background:none; border:0;">
                                    إذهب الى صفحة 
                                    <input id="goToPageNum" type="text" value="" style="width:50px"/>
                                    <button class="btn btn-xs btn-default border-radius-0" onclick="goToPage()" style="padding:0 5px 0 5px">إذهب</button>
                                </span>
                            </li>
                        </ul>
                    </div>
                </nav>
            </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- الشريط الجانبي الأيمن -->
    <div class="col-md-3 col-sm-12 col-xs-12 col-lg-3 rightside_bar_slider" style="margin-top:19px;">
       <?php include_once __DIR__ . '/../index_rightsidebar_back.php'; ?>
    </div>
</div>

<script>
function goToPage() {
    refineProductBySubCategory($('#goToPageNum').val(), '<?php echo $pc_id; ?>');
}

$(document).ready(function() {
    $("#scity").autocomplete("ajax-file/showProductsCity.php", {
        selectFirst: true,
        extraParams: {
            keywords: '<?php echo isset($_GET['keywords']) ? addslashes($_GET['keywords']) : ''; ?>', 
            rctype: '<?php echo isset($_GET['rctyp']) ? addslashes($_GET['rctyp']) : ''; ?>'
        }
    });
});
</script>

<?php
// إغلاق الـ statements
mysqli_stmt_close($stmt_prd);
mysqli_stmt_close($stmt_count);
?>