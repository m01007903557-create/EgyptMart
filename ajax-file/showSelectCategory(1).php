<?php
// عرض الأخطاء مباشرة
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// تضمين الملف الأساسي
require_once '../common.php';

// التحقق من أن الطريقة هي POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("يجب استخدام POST");
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    die("Invalid ID: " . $id);
}

// جلب التصنيفات الفرعية
$sql = "SELECT * FROM product_category WHERE pc_status='1' AND pc_parent_id='$id' ORDER BY pc_name";
$res = mysqli_query($con, $sql);

if (!$res) {
    die("SQL Error: " . mysqli_error($con));
}

$disp = '';
if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_object($res)) {
        $disp .= '<option value="' . $row->pc_id . '">' . ucfirst($row->pc_name) . '</option>';
    }
} else {
    $disp = '<option value="">لا توجد تصنيفات فرعية</option>';
}

// جلب اسم التصنيف الحالي
$sql_c = "SELECT * FROM product_category WHERE pc_status='1' AND pc_id='$id'";
$res_c = mysqli_query($con, $sql_c);
$category_name = '';
if ($res_c && mysqli_num_rows($res_c) > 0) {
    $row_c = mysqli_fetch_object($res_c);
    $category_name = ucfirst($row_c->pc_name);
}

// إرجاع النتيجة
echo $disp . '|' . $category_name;






/**
 * showSelectCategory.php - التصنيفات المتتالية
 * تم الترقية لـ PHP 8.3
 */

// تضمين الملف الأساسي
include "../common.php";

// التحقق من وجود POST id
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo '|';
    exit;
}

// جلب التصنيفات الفرعية
$sql = "SELECT * FROM product_category WHERE pc_status='1' AND pc_parent_id='" . mysqli_real_escape_string($con, $id) . "' ORDER BY pc_name";
$res = mysqli_query($con, $sql);

$disp = '';
if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_object($res)) {
        $disp .= '<option value="' . $row->pc_id . '">' . ucfirst($row->pc_name) . '</option>';
    }
} else {
    $disp = '<option value="">لا توجد تصنيفات فرعية</option>';
}

// جلب اسم التصنيف الحالي
$sql_c = "SELECT * FROM product_category WHERE pc_status='1' AND pc_id='" . mysqli_real_escape_string($con, $id) . "'";
$res_c = mysqli_query($con, $sql_c);

$category_name = '';
if (mysqli_num_rows($res_c) > 0) {
    $row_c = mysqli_fetch_object($res_c);
    $category_name = ucfirst($row_c->pc_name);
}

// إرجاع النتيجة بنفس التنسيق القديم (خيارات|الاسم)
echo $disp . '|' . $category_name;
?>