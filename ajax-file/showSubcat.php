<?php
/**
 * File: ajax/getSubCategorySelect.php
 * Description: جلب التصنيفات الفرعية لعنصر select مع خيار افتراضي
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف التصنيف
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo '<option value="">-- Invalid Request --</option>';
    exit;
}

$pc_id = (int)$_POST['id'];

global $con;

// جلب التصنيفات الفرعية من قاعدة البيانات
$sql = "SELECT pc_id, pc_name 
        FROM product_category 
        WHERE pc_parent_id = ? 
        AND pc_parent_id != 0 
        AND pc_status = 1 
        ORDER BY pc_order, pc_name ASC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $pc_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!-- خيار افتراضي -->
<option value="">-- Select Category --</option>

<?php
$has_options = false;

while ($row = mysqli_fetch_object($result)) {
    $has_options = true;
    $sub_pc_id = (int)$row->pc_id;
    $sub_pc_name = htmlspecialchars($row->pc_name ?? '', ENT_QUOTES, 'UTF-8');
?>
    <option value="<?php echo $sub_pc_id; ?>"><?php echo $sub_pc_name; ?></option>
<?php 
}

mysqli_stmt_close($stmt);

// إذا لم توجد تصنيفات فرعية، إبقاء الخيار الافتراضي فقط
if (!$has_options) {
    // يمكن إضافة تعليق أو رسالة إذا أردت
    echo '<!-- No subcategories found -->';
}
?>