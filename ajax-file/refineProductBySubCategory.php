<?php
/**
 * File: ajax/refineProductBySubCategory.php

 * Description: تحميل وعرض المنتجات حسب التصنيف الفرعي (نسخة مبسطة)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود رقم الصفحة ومعرف التصنيف
if (!isset($_POST['page']) || !is_numeric($_POST['page']) || !isset($_POST['id'])) {
    http_response_code(400);
    die("Invalid request parameters");
}

$page = (int)$_POST['page'];
$pc_id = mysqli_real_escape_string($GLOBALS['con'], $_POST['id']);

// إعدادات التصفح
$cur_page = $page;
$page -= 1;
$per_page = 20;
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

global $con;

// بناء شرط الموقع
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
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
}

// جلب معلومات التصنيف
$sql_pcat = "SELECT m.pc_id as mcat_id, m.pc_name as mcat_name, 
                    c.pc_id as cat_id, c.pc_name as cat_name, 
                    s.pc_name as subcat_name
             FROM product_category m
             INNER JOIN product_category c ON m.pc_id = c.pc_parent_id
             INNER JOIN product_category s ON c.pc_id = s.pc_parent_id
             WHERE MD5(s.pc_id) = ? 
             LIMIT 1";

$stmt_pcat = mysqli_prepare($con, $sql_pcat);
mysqli_stmt_bind_param($stmt_pcat, 's', $pc_id);
mysqli_stmt_execute($stmt_pcat);
$result_pcat = mysqli_stmt_get_result($stmt_pcat);
$row_pcat = mysqli_fetch_array($result_pcat);
mysqli_stmt_close($stmt_pcat);

// استعلام جلب المنتجات
$sql_prd = "SELECT p.*, mu.*, c.*, bp.*, pm.* 
            FROM products p
            INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
            INNER JOIN country c ON c.cn_id = p.pd_currency
            INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
            INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
            WHERE p.pd_status = '1' 
            AND p.pd_image != '' 
            AND pm.expiry_date > " . time() . "
            $sql_pd_ck
            AND MD5(p.pd_subcat_id) = ?
            ORDER BY FIELD(pm.p_id,'5','4','3','15')
            LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_prd);

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    mysqli_stmt_bind_param($stmt, 'sii', $pc_id, $start, $per_page);
} else {
    // مع معامل location_geo_country
    // سيتم التعامل معها حسب الحاجة
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
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
                  AND MD5(p.pd_subcat_id) = ?";

$stmt_count = mysqli_prepare($con, $query_pag_num);

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    mysqli_stmt_bind_param($stmt_count, 's', $pc_id);
} else {
    // مع معامل location_geo_country
}

mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row = mysqli_fetch_assoc($result_count);
$count = (int)($row['count'] ?? 0);

$no_of_paginations = (int)ceil($count / $per_page);
$pagi_string = "Page " . ($cur_page) . " of " . $no_of_paginations;

// حساب نطاق أزرار التصفح
$start_loop = 1;
$end_loop = $no_of_paginations;

if ($cur_page >= 7) {
    $start_loop = $cur_page - 3;
    if ($no_of_paginations > $cur_page + 3) {
        $end_loop = $cur_page + 3;
    } elseif ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    }
} else {
    $start_loop = 1;
    $end_loop = $no_of_paginations > 7 ? 7 : $no_of_paginations;
}
?>

<style>
.bt {
    background-position: 0 -240px;
    margin: 18px 0 30px 0;
    clear: left;
    height: 0px !Important;
    background-color: transparent;
    color: transparent;
}
</style>

<div style="border:1px solid #F5ECFF; border-radius:5px; padding-left:10px;">
    <h2 style="color:#36006C"><?php echo htmlspecialchars(ucwords($row_pcat[3] ?? ''), ENT_QUOTES, 'UTF-8'); ?></h2>
</div>

<div class="als-container" id="product_slider" style="border:1px solid #F5ECFF; border-radius:5px;">
    <div class="als-viewport" align="center">
        <ul class="als-wrapper">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row_prd = mysqli_fetch_object($result)): 
                    $product_title = htmlspecialchars($row_prd->pd_title ?? '', ENT_QUOTES, 'UTF-8');
                    $product_title_short = htmlspecialchars(substr($product_title, 0, 12), ENT_QUOTES, 'UTF-8');
                    $product_image = htmlspecialchars($row_prd->pd_image ?? '', ENT_QUOTES, 'UTF-8');
                    $bnsprof_id = (int)($row_prd->bnsprof_id ?? 0);
                    $pd_subcat_id = (int)($row_prd->pd_subcat_id ?? 0);
                    $pd_id = (int)($row_prd->pd_id ?? 0);
                    $bnsprof_city = (int)($row_prd->bnsprof_city ?? 0);
                    $bnsprof_state = (int)($row_prd->bnsprof_state ?? 0);
                    $pd_min_order_qty = (int)($row_prd->pd_min_order_qty ?? 0);
                    $mu_name = htmlspecialchars($row_prd->mu_name ?? '', ENT_QUOTES, 'UTF-8');
                    $cn_currency = htmlspecialchars($row_prd->cn_currency ?? '', ENT_QUOTES, 'UTF-8');
                    $pd_fob_price = htmlspecialchars($row_prd->pd_fob_price ?? '', ENT_QUOTES, 'UTF-8');
                    
                    // جلب معلومات الموقع
                    $location_name = '';
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
                        
                        $location_name = $cn_city . "&nbsp;-&nbsp;" . $cn_state;
                    } else {
                        $city_query = "SELECT ct_cn_id FROM city WHERE ct_id = ? LIMIT 1";
                        $stmt_city = mysqli_prepare($con, $city_query);
                        mysqli_stmt_bind_param($stmt_city, 'i', $bnsprof_city);
                        mysqli_stmt_execute($stmt_city);
                        $city_result = mysqli_stmt_get_result($stmt_city);
                        $city_row = mysqli_fetch_object($city_result);
                        $ct_cn_id = $city_row ? (int)$city_row->ct_cn_id : 0;
                        mysqli_stmt_close($stmt_city);
                        
                        $country_query = "SELECT cn_name FROM country WHERE cn_id = ? LIMIT 1";
                        $stmt_country = mysqli_prepare($con, $country_query);
                        mysqli_stmt_bind_param($stmt_country, 'i', $ct_cn_id);
                        mysqli_stmt_execute($stmt_country);
                        $country_result = mysqli_stmt_get_result($stmt_country);
                        $country_row = mysqli_fetch_object($country_result);
                        $location_name = $country_row ? htmlspecialchars($country_row->cn_name ?? '', ENT_QUOTES, 'UTF-8') : '';
                        mysqli_stmt_close($stmt_country);
                    }
                ?>
                <li class="als-item" style="border:1px solid #ccc; margin-top:1%; margin-left:1%; margin-bottom:1%; border-radius:4px; float:left; height:190px; background-color:rgba(251, 251, 251, 0.96);">
                    <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>&sc=<?php echo rand(10000, 99999) . $pd_subcat_id; ?>#<?php echo $pd_id; ?>" style="text-decoration:none; color:#000" target="_blank">
                        <img src="upload/myproduct/thumb/<?php echo $product_image; ?>" 
                             alt="<?php echo htmlspecialchars(ucwords(substr($product_title, 0, 28)), ENT_QUOTES, 'UTF-8'); ?>" 
                             title="<?php echo $product_title; ?>" />
                        <div style="height:0%; margin-top:3%; padding-top:5%;">
                            <span style="color:blue;"><b><?php echo ucwords($product_title_short); ?>....</b></span><br />
                            <span style="color:red;"><?php echo $location_name; ?></span>
                        </div>
                        <div style="height:10%; margin-top:21%; font-size:11px;">
                            MOQ: <span style="color:red; font-weight:600; font-size:15px;"><?php echo $pd_min_order_qty; ?>&nbsp;</span><?php echo $mu_name; ?>
                        </div>
                        <div style="height:10%; margin-top:1%; font-size:11px;">
                            <?php echo $cn_currency; ?>&nbsp;
                            <span style="color:red; font-weight:600; font-size:15px;"><?php echo $pd_fob_price; ?></span>/<?php echo $mu_name; ?>
                        </div>
                    </a>
                </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="als-item" style="border:1px solid #484891; margin-top:1%; margin-left:1.5%; border-radius:4px; width:97%; height:20px; color:#F00">
                    No products listed for this category.
                </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <?php if ($count > 0): ?>
        <p class="cl"><br></p>
        <p align="center" style="margin-bottom:10px;">
            <?php
            // زر الصفحة الأولى
            if ($first_btn && $cur_page > 1) {
                echo '<a href="javascript:refineProductBySubCategory(\'1\',\'' . $pc_id . '\')"><img id="firstmail" src="images/firsten.gif"></a>';
            } elseif ($first_btn) {
                echo '<img id="firstmail" src="images/first.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة السابقة
            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                echo '<a href="javascript:refineProductBySubCategory(\'' . $pre . '\',\'' . $pc_id . '\')"><img id="prevmail" src="images/prven.gif"></a>';
            } elseif ($previous_btn) {
                echo '<img id="prevmail" src="images/prevmail.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة التالية
            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                echo '<a href="javascript:refineProductBySubCategory(\'' . $nex . '\',\'' . $pc_id . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
            } elseif ($next_btn) {
                echo '<img id="nextmail" src="images/nextmail.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة الأخيرة
            if ($last_btn && $cur_page < $no_of_paginations) {
                echo '<a href="javascript:refineProductBySubCategory(\'' . $no_of_paginations . '\',\'' . $pc_id . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
            } elseif ($last_btn) {
                echo '<img id="lastmail" src="images/last.gif">';
            }
            ?>
        </p>
    <?php endif; ?>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>