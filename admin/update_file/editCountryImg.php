<?php
/**
 * File: admin/editCountryImg.php
 * Version: PHP 8.3
 * Description: رفع وتغيير حجم علم البلد
 * 
 * هذا الملف يستقبل طلب AJAX لرفع علم البلد، وتغيير حجمه إلى 30x20 بكسل،
 * وتحديث اسم الملف في قاعدة البيانات
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// تعيين نوع المحتوى إلى نص عادي
header('Content-Type: text/plain; charset=UTF-8');

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    error_log("خطأ في الاتصال بقاعدة البيانات في upload-country-flag.php");
    echo "0";
    exit();
}

// التحقق من وجود معرف البلد في طلب POST
if (!isset($_POST['cn_id']) || empty($_POST['cn_id'])) {
    echo "0";
    exit();
}

// التحقق من وجود ملف مرفوع
if (empty($_FILES) || !isset($_FILES['Filedata']) || $_FILES['Filedata']['error'] != UPLOAD_ERR_OK) {
    echo "0";
    error_log("خطأ في رفع ملف علم البلد: " . ($_FILES['Filedata']['error'] ?? 'No file'));
    exit();
}

// تنظيف المدخلات
$cn_id = (int)$_POST['cn_id'];

if ($cn_id <= 0) {
    echo "0";
    exit();
}

// جلب معلومات البلد الحالية
$sql_cn = "SELECT * FROM country WHERE cn_id = " . $cn_id . " LIMIT 1";
$res_cn = mysqli_query($con, $sql_cn);

if (!$res_cn || mysqli_num_rows($res_cn) == 0) {
    echo "0";
    error_log("البلد غير موجود: " . $cn_id);
    exit();
}

$row_cn = mysqli_fetch_object($res_cn);

// إنشاء اسم جديد للملف
$file_info = pathinfo($_FILES['Filedata']['name']);
$file_extension = strtolower($file_info['extension'] ?? '');
$safe_name = preg_replace('/[^a-zA-Z0-9]/', '_', $row_cn->cn_name ?? '') . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $row_cn->cn_currency ?? '');
$newFileName = $safe_name . '_' . uniqid() . '.' . $file_extension;

// التحقق من صحة امتداد الملف
$allowed_extensions = array('png');

if (!in_array($file_extension, $allowed_extensions)) {
    echo '0';
    error_log("امتداد غير مسموح: " . $file_extension);
    exit();
}

// محاولة نقل الملف المرفوع
$upload_path = "../images/country_flag/" . $newFileName;
$temp_file = $_FILES["Filedata"]["tmp_name"];

if (!move_uploaded_file($temp_file, $upload_path)) {
    echo '0';
    error_log("فشل في نقل الملف: " . $temp_file . " إلى " . $upload_path);
    exit();
}

// معالجة الصورة وتغيير حجمها
try {
    $imgSImage = new SimpleImage();
    $imgSImage->load($upload_path);
    $imgSImage->resize(30, 20); // تغيير الحجم إلى 30x20 بكسل
    $imgSImage->save($upload_path);

    // حذف الصورة القديمة إذا كانت موجودة
    if (!empty($row_cn->cn_flag)) {
        $old_path = "../images/country_flag/" . $row_cn->cn_flag;
        if (file_exists($old_path) && is_file($old_path)) {
            unlink($old_path);
        }
    }

    // تحديث قاعدة البيانات
    $sql = "UPDATE country
            SET cn_flag = '" . mysqli_real_escape_string($con, $newFileName) . "'
            WHERE cn_id = " . (int)$row_cn->cn_id;

    $result = mysqli_query($con, $sql);

    if (!$result) {
        error_log("خطأ في تحديث قاعدة البيانات: " . mysqli_error($con));
        echo '0';
        exit();
    }

    echo '1'; // نجاح

} catch (Exception $e) {
    error_log("استثناء في معالجة الصورة: " . $e->getMessage());
    echo '0';
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>