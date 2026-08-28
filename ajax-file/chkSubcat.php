<?php
/**
 * File: ajax/chkSubcat.php

 * Description: جلب التصنيفات الفرعية لمنتج معين (لعرضها في قائمة منسدلة)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف التصنيف
if (!isset($_POST['cid']) || !is_numeric($_POST['cid'])) {
    http_response_code(400);
    // يمكن إرجاع رسالة خطأ أو عنصر فارغ حسب الحاجة
    exit;
}

$category_id = (int)$_POST['cid'];

global $con;

// استعلام جلب التصنيفات الفرعية
$sql = "SELECT pc_id, pc_name 
        FROM product_category 
        WHERE pc_parent_id = ? AND pc_status = '1' 
        ORDER BY pc_order, pc_name ASC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$count = mysqli_num_rows($result);

// عرض التصنيفات الفرعية فقط إذا كان هناك أكثر من تصنيف واحد
if ($count > 1): 
?>
<div class="fs1 f1">
    <p><span style="line-height: 12px;">*</span>Sub Category</p>
    <select name="pd_subcat_id" id="pd_subcat_id" class="a_f pf1" style="width:280px;">
        <option value="">Select</option>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <option value="<?php echo (int)$row['pc_id']; ?>">
            <?php echo htmlspecialchars($row['pc_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
        </option>
        <?php endwhile; ?>
    </select>
    <br>
    <span>Please choose the Product subcategory here.</span>
</div>
<?php 
endif;

mysqli_stmt_close($stmt);
?>


