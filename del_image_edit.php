<?php
/**
 * File: del_image_edit.php
 * Version: PHP 8.3
 * Description: حذف صورة من صفحة "من نحن"
 * 
 * هذا الملف يقوم بحذف صورة من مجلد upload/myprofile
 * وتحديث حقل abtus_image في قاعدة البيانات ليكون فارغاً
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// تعيين نوع المحتوى إلى JSON (للاستجابة)
header('Content-Type: application/json');

// التحقق من وجود معرف الصورة في طلب GET
if (!isset($_GET['imid']) || empty($_GET['imid'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف الصورة مطلوب'
    ]);
    exit();
}

// تنظيف المدخلات
$image_id = (int)$_GET['imid'];

// التحقق من صحة القيمة
if ($image_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف الصورة غير صالح'
    ]);
    exit();
}

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في الاتصال بقاعدة البيانات'
    ]);
    exit();
}

// التحقق من صلاحيات المستخدم (اختياري - إذا كانت الصفحة تتطلب تسجيل دخول)
// يمكن إضافة التحقق من أن المستخدم هو مدير الموقع أو لديه الصلاحية المناسبة
/*
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    echo json_encode([
        'success' => false,
        'message' => 'يجب تسجيل الدخول أولاً'
    ]);
    exit();
}

// يمكن إضافة التحقق من صلاحية المستخدم (مثلاً: user_type = 'admin')
$user_id = (int)$_SESSION['uid_indm'];
$check_admin_sql = "SELECT user_type FROM user WHERE usr_id = {$user_id} LIMIT 1";
$check_admin_result = mysqli_query($con, $check_admin_sql);
if ($check_admin_result && mysqli_num_rows($check_admin_result) > 0) {
    $admin_row = mysqli_fetch_assoc($check_admin_result);
    if ($admin_row['user_type'] != 'admin') {
        echo json_encode([
            'success' => false,
            'message' => 'ليس لديك صلاحية لحذف هذه الصورة'
        ]);
        exit();
    }
}
*/

// جلب معلومات الصورة قبل الحذف
$select_sql = "SELECT abtus_image FROM about_us WHERE abtus_id = {$image_id} LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في جلب بيانات الصورة: ' . mysqli_error($con)
    ]);
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'السجل غير موجود في قاعدة البيانات'
    ]);
    exit();
}

$rowk = mysqli_fetch_object($select_result);
$image_path = "";

// حذف الملف الفعلي من السيرفر إذا كان موجوداً
if (!empty($rowk->abtus_image)) {
    $image_path = "upload/myprofile/" . $rowk->abtus_image;
    
    if (file_exists($image_path)) {
        if (!unlink($image_path)) {
            // فشل في حذف الملف - لكن نستمر في تحديث قاعدة البيانات
            error_log("فشل في حذف ملف الصورة: " . $image_path);
        }
    } else {
        error_log("ملف الصورة غير موجود: " . $image_path);
    }
} else {
    // لا يوجد ملف لحذفه
    echo json_encode([
        'success' => true,
        'message' => 'لا توجد صورة مرتبطة بهذا السجل',
        'action' => 'no_image'
    ]);
    // نستمر في تحديث قاعدة البيانات (لكن لن يكون هناك تغيير)
}

// تحديث حقل الصورة في قاعدة البيانات ليكون فارغاً
$update_sql = "UPDATE about_us SET abtus_image = '' WHERE abtus_id = {$image_id}";
$update_result = mysqli_query($con, $update_sql);

if ($update_result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الصورة بنجاح',
            'action' => 'deleted',
            'image_path' => $image_path,
            'affected_rows' => mysqli_affected_rows($con)
        ]);
    } else {
        // لم يتم تحديث أي صف (ربما الصورة كانت فارغة أصلاً)
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الملف ولكن لم يتم تحديث قاعدة البيانات (ربما كانت الصورة فارغة)',
            'action' => 'file_deleted_db_not_updated',
            'image_path' => $image_path
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'فشل في تحديث قاعدة البيانات: ' . mysqli_error($con),
        'error' => mysqli_error($con)
    ]);
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>