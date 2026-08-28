<?php
/**
 * File: showidecate.php
 * Description: إنشاء القائمة الجانبية للتصنيفات الرئيسية مع عدد المنتجات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/lib/connect.php';

global $con;

// =============================================
// بناء شروط الموقع (Location Conditions)
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
            SELECT DISTINCT bnsprof_uid FROM business_profile bf 
            INNER JOIN city c ON bf.bnsprof_city = c.ct_id 
            WHERE c.ct_cn_id = $loc_id
        ))
    )";
} else {
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country IN (SELECT cn_id FROM country WHERE cn_code = 'EG')
        ))
    )";
}

// =============================================
// جلب التصنيفات الرئيسية
// =============================================
$current_time = time();

// جلب التصنيفات الفرعية للمنتجات النشطة
$sql_cmt_cnt1 = "SELECT DISTINCT p.pd_subcat_id 
                 FROM products p
                 INNER JOIN product_category pc ON p.pd_subcat_id = pc.pc_id
                 INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
                 INNER JOIN country c ON c.cn_id = p.pd_currency
                 INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                 INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                 INNER JOIN smembership_plan sp ON sp.mp_id = pm.p_id
                 WHERE p.pd_status = '1'
                 AND p.pd_image != ''
                 AND pm.expiry_date > $current_time
                 $sql_pd_ck
                 ORDER BY pc.pc_order ASC";

$result_dd_mnu = mysqli_query($con, $sql_cmt_cnt1);
$pc_id_arr = array();

if ($result_dd_mnu && mysqli_num_rows($result_dd_mnu) > 0) {
    while ($row = mysqli_fetch_assoc($result_dd_mnu)) {
        $pc_id_arr[] = (int)$row['pd_subcat_id'];
    }
}

// إذا لم توجد تصنيفات، الخروج
if (empty($pc_id_arr)) {
    echo '<li class="ptag text-danger" style="padding:5px;">لا توجد تصنيفات حالياً</li>';
    exit;
}

$ids = "'" . implode("','", $pc_id_arr) . "'";

// جلب التصنيفات الأصلية
$sql_parent = "SELECT DISTINCT pc_parent_id 
               FROM product_category 
               WHERE pc_id IN ($ids) 
               ORDER BY pc_order ASC";

$result_parent = mysqli_query($con, $sql_parent);
$parent_ids = array();

if ($result_parent) {
    while ($row = mysqli_fetch_assoc($result_parent)) {
        $parent_ids[] = (int)$row['pc_parent_id'];
    }
}

if (empty($parent_ids)) {
    exit;
}

$parent_ids_str = "'" . implode("','", $parent_ids) . "'";

// جلب التصنيفات الرئيسية الكبرى
$sql_master = "SELECT DISTINCT pc_parent_id 
               FROM product_category 
               WHERE pc_id IN ($parent_ids_str) 
               ORDER BY pc_order ASC";

$result_master = mysqli_query($con, $sql_master);
$master_ids = array();

if ($result_master) {
    while ($row = mysqli_fetch_assoc($result_master)) {
        $master_ids[] = (int)$row['pc_parent_id'];
    }
}

if (empty($master_ids)) {
    exit;
}

$master_ids_str = "'" . implode("','", $master_ids) . "'";

// جلب التصنيفات الرئيسية للعرض
$sql_master_cats = "SELECT pc_id, pc_name 
                    FROM product_category 
                    WHERE pc_id IN ($master_ids_str) 
                    ORDER BY pc_order ASC";

$result_master_cats = mysqli_query($con, $sql_master_cats);
?>

<ul>
    <?php while ($master_cat = mysqli_fetch_assoc($result_master_cats)): 
        $master_id = (int)$master_cat['pc_id'];
        $master_name = htmlspecialchars($master_cat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $master_token = rand(10, 9999) . md5((string)$master_id);
    ?>
    <li class='has-sub'>
        <a href="category.php?token=<?php echo $master_token; ?>">
            <span><?php echo $master_name; ?></span>
        </a>
        
        <!-- التصنيفات الفرعية من المستوى الأول -->
        <ul>
            <?php
            $sql_sub = "SELECT pc_id, pc_name 
                        FROM product_category 
                        WHERE pc_parent_id = ? 
                        AND pc_id IN ($parent_ids_str) 
                        ORDER BY pc_order ASC";
            
            $stmt_sub = mysqli_prepare($con, $sql_sub);
            if ($stmt_sub) {
                mysqli_stmt_bind_param($stmt_sub, 'i', $master_id);
                mysqli_stmt_execute($stmt_sub);
                $result_sub = mysqli_stmt_get_result($stmt_sub);
                
                while ($sub_cat = mysqli_fetch_assoc($result_sub)):
                    $sub_id = (int)$sub_cat['pc_id'];
                    $sub_name = htmlspecialchars($sub_cat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8');
                    
                    // جلب التصنيفات الفرعية من المستوى الثاني لحساب عدد المنتجات
                    $sql_sub_sub = "SELECT pc_id 
                                   FROM product_category 
                                   WHERE pc_parent_id = ? 
                                   AND pc_id IN ($ids) 
                                   ORDER BY pc_order ASC";
                    
                    $stmt_sub_sub = mysqli_prepare($con, $sql_sub_sub);
                    $child_ids = array();
                    
                    if ($stmt_sub_sub) {
                        mysqli_stmt_bind_param($stmt_sub_sub, 'i', $sub_id);
                        mysqli_stmt_execute($stmt_sub_sub);
                        $result_sub_sub = mysqli_stmt_get_result($stmt_sub_sub);
                        
                        while ($child = mysqli_fetch_assoc($result_sub_sub)) {
                            $child_ids[] = (int)$child['pc_id'];
                        }
                        mysqli_stmt_close($stmt_sub_sub);
                    }
                    
                    // حساب عدد المنتجات في هذه التصنيفات
                    $product_count = 0;
                    if (!empty($child_ids)) {
                        $child_ids_str = "'" . implode("','", $child_ids) . "'";
                        
                        $count_sql = "SELECT COUNT(*) as count 
                                      FROM products p
                                      INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
                                      INNER JOIN country c ON c.cn_id = p.pd_currency
                                      INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                                      INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                                      INNER JOIN smembership_plan sp ON sp.mp_id = pm.p_id
                                      WHERE p.pd_status = '1'
                                      AND p.pd_image != ''
                                      AND pm.expiry_date > $current_time
                                      $sql_pd_ck
                                      AND p.pd_subcat_id IN ($child_ids_str)";
                        
                        $count_result = mysqli_query($con, $count_sql);
                        if ($count_result) {
                            $count_row = mysqli_fetch_assoc($count_result);
                            $product_count = (int)($count_row['count'] ?? 0);
                        }
                    }
                    
                    if ($product_count > 0):
            ?>
            <li>
                <a href="products.php?c=<?php echo md5((string)$sub_id); ?>">
                    <span><?php echo $sub_name; ?> (<?php echo $product_count; ?>)</span>
                </a>
            </li>
            <?php 
                    endif;
                endwhile;
                mysqli_stmt_close($stmt_sub);
            }
            ?>
        </ul>
    </li>
    <?php endwhile; ?>
    
    <!-- رابط عرض جميع التصنيفات -->
    <li><a href="dir.php">عرض كل التصنيفات</a></li>
</ul>
<?php
exit;
?>