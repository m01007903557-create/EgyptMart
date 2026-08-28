<?php
/**
 * File: includes/category_menu.php
 * Description: عرض قائمة التصنيفات المتعددة المستويات للمنتجات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود متغيرات التصفية
$sql_order = $sql_order ?? "ORDER BY pc_order, pc_name ASC";
$sql_pd_ck = $sql_pd_ck ?? "";

global $con;

// استعلام جلب التصنيفات الرئيسية
$sql_dd_mnu = "SELECT pc_id, pc_name, pc_image 
               FROM product_category 
               WHERE pc_parent_id = 0 
               AND pc_status = 1 
               $sql_order";

$res_dd_mnu = mysqli_query($con, $sql_dd_mnu);

if (!$res_dd_mnu) {
    error_log("Category Menu Error: " . mysqli_error($con));
    echo "<!-- Error loading categories -->";
    exit;
}
?>

<ul>
<?php while ($row_dd_mnu = mysqli_fetch_object($res_dd_mnu)): 
    $parent_pc_id = (int)$row_dd_mnu->pc_id;
    $parent_pc_name = htmlspecialchars($row_dd_mnu->pc_name ?? '', ENT_QUOTES, 'UTF-8');
    
    // جلب التصنيفات الفرعية
    $sql_dd_cmnu = "SELECT pc_id, pc_sort_name 
                    FROM product_category 
                    WHERE pc_parent_id = ? 
                    AND pc_status = 1 
                    $sql_order";
    
    $stmt_cmnu = mysqli_prepare($con, $sql_dd_cmnu);
    mysqli_stmt_bind_param($stmt_cmnu, 'i', $parent_pc_id);
    mysqli_stmt_execute($stmt_cmnu);
    $res_dd_cmnu = mysqli_stmt_get_result($stmt_cmnu);
    
    $total_products = 0;
    $sub_categories = [];
    
    // حساب إجمالي المنتجات في جميع التصنيفات الفرعية
    while ($row_dd_cmnu = mysqli_fetch_object($res_dd_cmnu)) {
        $sub_pc_id = (int)$row_dd_cmnu->pc_id;
        
        // جلب التصنيفات الفرعية الأعمق
        $sql_check2 = "SELECT pc_id 
                       FROM product_category 
                       WHERE pc_parent_id = ? 
                       AND pc_status = 1";
        
        $stmt_check2 = mysqli_prepare($con, $sql_check2);
        mysqli_stmt_bind_param($stmt_check2, 'i', $sub_pc_id);
        mysqli_stmt_execute($stmt_check2);
        $res_check2 = mysqli_stmt_get_result($stmt_check2);
        
        $child_ids = [];
        while ($data1 = mysqli_fetch_assoc($res_check2)) {
            $child_ids[] = (int)$data1['pc_id'];
        }
        mysqli_stmt_close($stmt_check2);
        
        $ids1 = !empty($child_ids) ? implode(',', $child_ids) : '0';
        
        // حساب عدد المنتجات في هذه التصنيفات
        $sql_cmt_cnt1 = "SELECT COUNT(*) as count2 
                        FROM products p
                        JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
                        JOIN country c ON c.cn_id = p.pd_currency
                        JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                        JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                        JOIN smembership_plan sp ON sp.mp_id = pm.p_id
                        WHERE p.pd_status = '1' 
                        AND p.pd_image != '' 
                        AND pm.expiry_date > ? 
                        AND p.pd_subcat_id IN ($ids1)
                        $sql_pd_ck";
        
        $current_time = time();
        $stmt_cnt = mysqli_prepare($con, $sql_cmt_cnt1);
        mysqli_stmt_bind_param($stmt_cnt, 'i', $current_time);
        mysqli_stmt_execute($stmt_cnt);
        $result_cnt = mysqli_stmt_get_result($stmt_cnt);
        $row_cnt = mysqli_fetch_assoc($result_cnt);
        $category_count = (int)($row_cnt['count2'] ?? 0);
        
        $total_products += $category_count;
        
        // تخزين بيانات التصنيف الفرعي للاستخدام لاحقاً
        $sub_categories[] = [
            'id' => $sub_pc_id,
            'name' => $row_dd_cmnu->pc_sort_name ?? '',
            'count' => $category_count
        ];
        
        mysqli_stmt_close($stmt_cnt);
    }
    
    mysqli_stmt_close($stmt_cmnu);
    
    // عرض التصنيف الرئيسي فقط إذا كان يحتوي على منتجات
    if ($total_products > 0): 
        $token = rand(10, 9999) . md5((string)$parent_pc_id);
?>
    <li class='has-sub'>
        <a href="category.php?token=<?php echo $token; ?>">
            <span><?php echo $parent_pc_name; ?></span>
        </a>
        <ul>
        <?php foreach ($sub_categories as $sub_cat): 
            if ($sub_cat['count'] > 0): 
                $sub_cat_name = htmlspecialchars(ucwords($sub_cat['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $sub_cat_token = md5((string)$sub_cat['id']);
        ?>
            <li>
                <a href="products.php?c=<?php echo $sub_cat_token; ?>">
                    <span><?php echo $sub_cat_name; ?> (<?php echo $sub_cat['count']; ?>)</span>
                </a>
            </li>
        <?php 
            endif;
        endforeach; ?>
        </ul>
    </li>
<?php 
    endif;
endwhile; 
mysqli_close($con); 
?>
</ul>