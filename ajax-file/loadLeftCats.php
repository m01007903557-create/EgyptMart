<?php
/**
 * File: ajax/loadLeftCats.php

 * Description: تحميل وعرض التصنيفات الفرعية مع عدد المنتجات للتصفية
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود الصفحة ومعرف التصنيف
if (!isset($_POST['page']) || !isset($_POST['id'])) {
    http_response_code(400);
    die("Invalid request parameters");
}

$page = (int)$_POST['page'];
$pc_id = mysqli_real_escape_string($GLOBALS['con'], $_POST['id']);
$is_sub = isset($_POST['is_sub']) && $_POST['is_sub'] === "true";
$mst_type = isset($_POST['mst_type']) ? $_POST['mst_type'] : '';
$minorder = isset($_POST['min_order']) ? (float)$_POST['min_order'] : 0;
$country = isset($_POST['country']) ? mysqli_real_escape_string($GLOBALS['con'], $_POST['country']) : '';
$city = isset($_POST['city']) ? mysqli_real_escape_string($GLOBALS['con'], $_POST['city']) : '';
$state = isset($_POST['state']) ? mysqli_real_escape_string($GLOBALS['con'], $_POST['state']) : '';

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
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
}

// =============================================
// 2. بناء شرط التصنيف الفرعي
// =============================================
$sql_sub_cat = "";
if ($is_sub) {
    $sql_sub_cat = " AND MD5(pd_subcat_id) = ?";
} else {
    // جلب معلومات التصنيف الرئيسي
    $sql_pcat = "SELECT m.pc_id, m.pc_name, c.pc_id, c.pc_name 
                 FROM product_category m
                 INNER JOIN product_category c ON m.pc_id = c.pc_parent_id
                 WHERE MD5(c.pc_id) = ? 
                 ORDER BY pc_order ASC 
                 LIMIT 1";
    
    $stmt_pcat = mysqli_prepare($con, $sql_pcat);
    mysqli_stmt_bind_param($stmt_pcat, 's', $pc_id);
    mysqli_stmt_execute($stmt_pcat);
    $result_pcat = mysqli_stmt_get_result($stmt_pcat);
    $row_pcat = mysqli_fetch_array($result_pcat);
    mysqli_stmt_close($stmt_pcat);
    
    // جلب جميع التصنيفات الفرعية تحت هذا التصنيف
    $sql_check1 = "SELECT pc_id FROM product_category WHERE MD5(pc_parent_id) = ? ORDER BY pc_order ASC";
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
    $sql_sub_cat = " AND pd_subcat_id IN ('$ids')";
}

// =============================================
// 3. بناء شروط التصفية الإضافية
// =============================================
$member_condition = "";
if (!empty($mst_type)) {
    $member_ids = implode(',', array_map('intval', explode(',', $mst_type)));
    $member_condition = " AND sp.mp_id IN ($member_ids)";
}

$minorder_condition = "";
if ($minorder > 0) {
    $minorder_condition = " AND pd_min_order_qty <= $minorder";
}

$country_condition = "";
if (!empty($country)) {
    $country_ids = implode(',', array_map('intval', explode(',', $country)));
    $country_condition = " AND cn_id IN ($country_ids)";
}

$sql_pd_city = "";
if (!empty($city)) {
    $city_escaped = mysqli_real_escape_string($con, $city);
    $sql_pd_city = " AND bnsprof_city IN (SELECT ct_id FROM city WHERE ct_name LIKE '%$city_escaped%')";
}

$sql_pd_state = "";
if (!empty($state)) {
    $state_ids = implode(',', array_map('intval', explode(',', $state)));
    $sql_pd_state = " AND bnsprof_state IN ($state_ids)";
    
    if (!empty($country_condition)) {
        $sql_pd_state = str_replace(' AND bnsprof_state', ' OR bnsprof_state', $sql_pd_state);
        $country_condition = str_replace(' AND cn_id', ' AND (cn_id', $country_condition) . $sql_pd_state . ')';
        $sql_pd_state = '';
    }
}

// =============================================
// 4. استعلام جلب التصنيفات الفرعية مع عدد المنتجات
// =============================================
$sql_prd = "SELECT pc.pc_id, pc.pc_name, COUNT(p.pd_id) as tot_prod
            FROM product_category pc
            LEFT JOIN products p ON p.pd_subcat_id = pc.pc_id 
                AND p.pd_status = '1' 
                AND p.pd_image != '' 
                AND p.pd_uid IN (
                    SELECT DISTINCT bnsprof_uid FROM business_profile
                )
            LEFT JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
            LEFT JOIN country c ON c.cn_id = p.pd_currency
            LEFT JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
            LEFT JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
            LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id
            LEFT JOIN city ct ON ct.ct_id = bp.bnsprof_city
            LEFT JOIN country c2 ON c2.cn_id = ct.ct_cn_id
            WHERE 1=1
            $sql_sub_cat
            $sql_pd_ck
            $member_condition
            $minorder_condition
            $country_condition
            $sql_pd_city
            $sql_pd_state
            AND (p.pd_id IS NULL OR (pm.expiry_date > " . time() . "))
            GROUP BY pc.pc_id, pc.pc_name
            HAVING tot_prod > 0
            ORDER BY pc.pc_order ASC";

$result_subcat = mysqli_query($con, $sql_prd);

// عرض التصنيفات الفرعية
while ($row_subcat = mysqli_fetch_object($result_subcat)):
    if ($row_subcat->tot_prod > 0):
        $pc_id_md5 = md5((string)$row_subcat->pc_id);
        $pc_name = htmlspecialchars(ucwords($row_subcat->pc_name ?? ''), ENT_QUOTES, 'UTF-8');
        $tot_prod = (int)$row_subcat->tot_prod;
?>
    <div class="item-list">
        <a style="cursor:pointer;" onclick="loadProductByCategory(1, '<?php echo $pc_id_md5; ?>', true);">
            <?php echo $pc_name . " (" . $tot_prod . ")"; ?>
        </a>
        <?php if ($is_sub): ?>
        <button class="btn btn-xs btn-default border-radius-0" 
                onclick="loadProductByCategory(1, main_cat);" 
                style="padding:0 5px 0 5px">
            Cancel
        </button>
        <?php endif; ?>
    </div>
<?php 
    endif;
endwhile;

// إغلاق الاتصال
// mysqli_close($con);
?>