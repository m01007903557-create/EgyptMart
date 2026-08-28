<?php
/**
 * File: ajax/getLocationFilters.php
 * Description: جلب قائمة الدول والمدن لتصفية المنتجات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود المعاملات المطلوبة
if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
    die("Invalid country ID");
}

$id = (int)$_REQUEST['id'];
$pc_id = $_REQUEST['cid'] ?? '';
$is_sub = isset($_REQUEST['is_sub']) ? (bool)$_REQUEST['is_sub'] : false;

global $con;

// جلب معلومات الدولة
$country_sql = "SELECT cn_id, cn_name FROM country WHERE cn_id = ?";
$stmt_country = mysqli_prepare($con, $country_sql);
mysqli_stmt_bind_param($stmt_country, 'i', $id);
mysqli_stmt_execute($stmt_country);
$country_result = mysqli_stmt_get_result($stmt_country);
$country_data = mysqli_fetch_assoc($country_result);
mysqli_stmt_close($stmt_country);

if (!$country_data) {
    die("Country not found");
}
?>

<div>
    <p>
        <span class="outer_c">
            <input type="checkbox" name="country_sel" value="<?php echo $country_data['cn_id']; ?>">
            <span style="font-weight:bold;"><?php echo htmlspecialchars($country_data['cn_name']); ?></span>
            <i class="fa fa-angle-down close_state" id="<?php echo $country_data['cn_id']; ?>" style="font-size: 15px;margin-left: 5px;cursor: pointer;"></i>
        </span>
    </p>

    <?php
    // تحديد شرط التصفية بناءً على موقع المستخدم
    $sql_pd_ck = "";
    if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
        $loc_id = (int)$_COOKIE['loc_id'];
        $sql_pd_ck = " AND (
            (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = ?)) 
            OR 
            (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = ?))
            OR
            (pd_preferred_buyer_location='my_city' AND pd_uid IN (
                SELECT DISTINCT bnsprof_uid FROM business_profile 
                WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = ?)
            ))
        )";
    } else {
        $location_geo_country = $location_geo_country ?? '';
        $sql_pd_ck = " AND (
            (pd_preferred_buyer_location='any')
            OR
            (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (
                SELECT DISTINCT usr_id FROM user 
                WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
            ))
        )";
    }

    // جلب التصنيفات الفرعية
    $parent_prefix = $is_sub ? '' : 'parent_';
    $pc_id_arr = [];
    
    if (!empty($pc_id)) {
        $sql_check1 = "SELECT pc_id FROM product_category WHERE MD5(pc_{$parent_prefix}id) = ?";
        $stmt_check1 = mysqli_prepare($con, $sql_check1);
        mysqli_stmt_bind_param($stmt_check1, 's', $pc_id);
        mysqli_stmt_execute($stmt_check1);
        $result_check1 = mysqli_stmt_get_result($stmt_check1);
        
        while ($data = mysqli_fetch_assoc($result_check1)) {
            $pc_id_arr[] = (int)$data['pc_id'];
        }
        mysqli_stmt_close($stmt_check1);
    }
    
    $ids = !empty($pc_id_arr) ? implode(',', $pc_id_arr) : '0';

    // جلب الولايات/المدن المرتبطة
    $current_time = time();
    
    $state_sql = "SELECT DISTINCT s.state_id, s.state_name 
                  FROM products p
                  JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                  JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                  JOIN smembership_plan sp ON sp.mp_id = pm.p_id
                  JOIN states s ON s.state_id = bp.bnsprof_state
                  WHERE p.pd_image != '' 
                  AND p.pd_status = '1' 
                  AND pm.expiry_date > ? 
                  AND s.state_cn_id = ?";
    
    // إضافة شرط التصنيفات
    if (!empty($ids) && $ids !== '0') {
        $state_sql .= " AND p.pd_subcat_id IN ($ids)";
    }
    
    // إضافة شرط موقع المستخدم
    if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
        $loc_id = (int)$_COOKIE['loc_id'];
        $state_sql = str_replace('?', "'$loc_id'", $state_sql); // استبدال مؤقت للمعاملات المتعددة
        
        $stmt_state = mysqli_prepare($con, $state_sql);
        mysqli_stmt_bind_param($stmt_state, 'ii', $current_time, $id);
    } else {
        $loc_country = $location_geo_country ?? '';
        $state_sql = str_replace('?', "'$loc_country'", $state_sql);
        
        $stmt_state = mysqli_prepare($con, $state_sql);
        mysqli_stmt_bind_param($stmt_state, 'i', $current_time);
    }
    
    mysqli_stmt_execute($stmt_state);
    $state_result = mysqli_stmt_get_result($stmt_state);
    
    // عرض الولايات
    while ($data1 = mysqli_fetch_assoc($state_result)) {
        $state_id = (int)$data1['state_id'];
        $state_name = htmlspecialchars($data1['state_name'] ?? '', ENT_QUOTES, 'UTF-8');
        ?>
        <span class="outer_c">
            <input type="checkbox" name="state_sel" value="<?php echo $state_id; ?>">
            <span><?php echo $state_name; ?></span>
        </span>
        <?php
    }
    
    mysqli_stmt_close($stmt_state);
    ?>
</div>