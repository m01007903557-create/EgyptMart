<?php
/**
 * File: companylogo-update.php
 * Version: PHP 8.3
 * Description: تحديث شعار الشركة - إضافة أو تحديث شعار الشركة في قاعدة البيانات وحذف القديم
 * 
 * هذا الملف يستقبل طلب AJAX لتحديث شعار الشركة
 * يتم استدعاؤه بعد رفع صورة الشعار
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// تعيين نوع المحتوى إلى نص عادي (للاستجابة)
header('Content-Type: text/plain; charset=UTF-8');

// التحقق من وجود معرف المستخدم في طلب POST
if (!isset($_POST['uid']) || empty($_POST['uid'])) {
    echo "0: معرف المستخدم مطلوب";
    error_log("خطأ في update-company-logo.php: معرف المستخدم مطلوب");
    exit();
}

// التحقق من وجود اسم الملف في طلب POST
if (!isset($_POST['file']) || empty($_POST['file'])) {
    echo "0: اسم الملف مطلوب";
    error_log("خطأ في update-company-logo.php: اسم الملف مطلوب");
    exit();
}

// تنظيف المدخلات
$uid = (int)$_POST['uid'];
$file_name = trim($_POST['file']);

// التحقق من صحة القيم
if ($uid <= 0) {
    echo "0: معرف المستخدم غير صالح";
    exit();
}

if (empty($file_name)) {
    echo "0: اسم الملف غير صالح";
    exit();
}

// التحقق من امتداد الملف (اختياري - أمان إضافي)
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    echo "0: امتداد الملف غير مسموح به";
    error_log("خطأ في update-company-logo.php: امتداد غير مسموح - " . $file_extension);
    exit();
}

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    echo "0: خطأ في الاتصال بقاعدة البيانات";
    error_log("خطأ في update-company-logo.php: اتصال قاعدة البيانات غير موجود");
    exit();
}

// التحقق من صلاحيات المستخدم (التأكد من أن المستخدم الحالي هو نفسه)
if (isset($_SESSION['uid_indm']) && (int)$_SESSION['uid_indm'] != $uid) {
    echo "0: ليس لديك صلاحية لتحديث هذا الشعار";
    error_log("خطأ في update-company-logo.php: محاولة تحديث شعار مستخدم آخر");
    exit();
}

// تحديد مسار المجلد
$targetFolder = 'server/php/files';

// التحقق من وجود ملف الشركة
$sqlk = "SELECT * FROM business_profile WHERE bnsprof_uid = {$uid} LIMIT 1";
$resk = mysqli_query($con, $sqlk);

if (!$resk) {
    echo "0: خطأ في قاعدة البيانات: " . mysqli_error($con);
    error_log("خطأ في update-company-logo.php: " . mysqli_error($con));
    exit();
}

if (mysqli_num_rows($resk) > 0) {
    // تحديث سجل موجود
    $rowk = mysqli_fetch_object($resk);
    
    // تحديث الشعار في قاعدة البيانات
    $sql = "UPDATE business_profile SET 
            bnsprof_complogo = '" . mysqli_real_escape_string($con, $file_name) . "' 
            WHERE bnsprof_uid = {$uid}";
    
    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        echo "0: فشل في تحديث قاعدة البيانات: " . mysqli_error($con);
        error_log("خطأ في update-company-logo.php: " . mysqli_error($con));
        exit();
    }
    
    // حذف الشعار القديم إذا كان موجوداً
    if (!empty($rowk->bnsprof_complogo)) {
        $old_logo = $rowk->bnsprof_complogo;
        
        // حذف الملف الكبير
        $pathLrg = $targetFolder . "/" . $old_logo;
        if (file_exists($pathLrg)) {
            if (!unlink($pathLrg)) {
                error_log("تحذير في update-company-logo.php: فشل في حذف الملف القديم - " . $pathLrg);
            }
        }
        
        // حذف الصورة المصغرة (إذا كانت موجودة في مجلد مختلف)
        $pathThumb = $targetFolder . "/thumbnail/" . $old_logo;
        if (file_exists($pathThumb)) {
            if (!unlink($pathThumb)) {
                error_log("تحذير في update-company-logo.php: فشل في حذف الصورة المصغرة - " . $pathThumb);
            }
        }
    }
    
    echo "1: تم تحديث الشعار بنجاح";
    
} else {
    // إدراج سجل جديد
    $sql = "INSERT INTO business_profile 
            SET bnsprof_uid = {$uid}, 
                bnsprof_complogo = '" . mysqli_real_escape_string($con, $file_name) . "', 
                bnsprof_creation_date = NOW()";
    
    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        echo "0: فشل في إدراج قاعدة البيانات: " . mysqli_error($con);
        error_log("خطأ في update-company-logo.php: " . mysqli_error($con));
        exit();
    }
    
    echo "1: تم إضافة الشعار بنجاح";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>