<?php
// showSelectCategory.php - مبسط ومخصص للتصنيفات المتتالية فقط
require_once '../common.php';

// التحقق من وجود POST id
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo '|';
    exit;
}

// جلب التصنيفات الفرعية (للعرض في الـ select box)
$sql = "SELECT pc_id, pc_name FROM product_category 
        WHERE pc_status = '1' AND pc_parent_id = '$id' 
        ORDER BY pc_name";
$res = mysqli_query($con, $sql);

$options = '';
if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_object($res)) {
        $options .= '<option value="' . $row->pc_id . '">' . ucfirst($row->pc_name) . '</option>';
    }
} else {
    $options = '<option value="">لا توجد تصنيفات فرعية</option>';
}

// جلب اسم التصنيف الحالي (للسطر الأفقي)
$sql_c = "SELECT pc_name FROM product_category WHERE pc_id = '$id'";
$res_c = mysqli_query($con, $sql_c);
$category_name = '';
if ($res_c && mysqli_num_rows($res_c) > 0) {
    $row_c = mysqli_fetch_object($res_c);
    $category_name = ucfirst($row_c->pc_name);
}

// إرجاع النتيجة فقط (بدون أي محتوى إضافي)
echo $options . '|' . $category_name;
?>