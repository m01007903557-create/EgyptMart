<?php
/**
 * File: del_companylogo.php
 * Version: PHP 8.3
 * Description: حذف شعار الشركة
 * 
 * هذا الملف يقوم بحذف شعار الشركة من مجلد server/php/files/
 * وتحديث حقل bnsprof_complogo في قاعدة البيانات ليكون فارغاً
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

// التحقق من وجود معرف المستخدم في طلب GET
if (!isset($_GET['imid']) || empty($_GET['imid'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المستخدم مطلوب'
    ]);
    exit();
}

// تنظيف المدخلات
$user_id = (int)$_GET['imid'];

// التحقق من صحة القيمة
if ($user_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المستخدم غير صالح'
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

// التحقق من صلاحيات المستخدم (التأكد من أن المستخدم الحالي هو مالك الشعار)
if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    $current_user_id = (int)$_SESSION['uid_indm'];
    
    // التحقق من أن المستخدم الحالي هو نفسه صاحب الشعار
    if ($current_user_id != $user_id) {
        echo json_encode([
            'success' => false,
            'message' => 'ليس لديك صلاحية لحذف هذا الشعار'
        ]);
        exit();
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'يجب تسجيل الدخول أولاً'
    ]);
    exit();
}

// جلب معلومات الشعار قبل الحذف
$select_sql = "SELECT bnsprof_complogo FROM business_profile WHERE bnsprof_uid = {$user_id} LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في جلب بيانات الشركة: ' . mysqli_error($con)
    ]);
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ملف الشركة غير موجود في قاعدة البيانات'
    ]);
    exit();
}

$rowk = mysqli_fetch_object($select_result);
$logo_path = "";

// حذف الملف الفعلي من السيرفر إذا كان موجوداً
if (!empty($rowk->bnsprof_complogo)) {
    $logo_path = "server/php/files/" . $rowk->bnsprof_complogo;
    
    if (file_exists($logo_path)) {
        if (!unlink($logo_path)) {
            // فشل في حذف الملف - لكن نستمر في تحديث قاعدة البيانات
            error_log("فشل في حذف ملف الشعار: " . $logo_path);
        } else {
            // تم حذف الملف بنجاح
            error_log("تم حذف ملف الشعار: " . $logo_path);
        }
    } else {
        error_log("ملف الشعار غير موجود: " . $logo_path);
    }
} else {
    // لا يوجد شعار لحذفه
    echo json_encode([
        'success' => true,
        'message' => 'لا يوجد شعار مرتبط بهذه الشركة',
        'action' => 'no_logo'
    ]);
    exit();
}

// تحديث حقل الشعار في قاعدة البيانات ليكون فارغاً
$update_sql = "UPDATE business_profile SET bnsprof_complogo = '' WHERE bnsprof_uid = {$user_id}";
$update_result = mysqli_query($con, $update_sql);

if ($update_result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الشعار بنجاح',
            'action' => 'deleted',
            'logo_path' => $logo_path,
            'affected_rows' => mysqli_affected_rows($con)
        ]);
    } else {
        // لم يتم تحديث أي صف (ربما الشعار كان فارغاً أصلاً)
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الملف ولكن لم يتم تحديث قاعدة البيانات',
            'action' => 'file_deleted_db_not_updated',
            'logo_path' => $logo_path
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