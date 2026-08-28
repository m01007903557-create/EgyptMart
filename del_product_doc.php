<?php
/**
 * File: del_product_pdf.php
 * Version: PHP 8.3
 * Description: حذف ملف PDF مرفق مع منتج
 * 
 * هذا الملف يقوم بحذف ملف PDF من مجلد upload/productdoc
 * وتحديث حقل pd_pdf_attach في قاعدة البيانات ليكون فارغاً
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

// التحقق من وجود معرف المنتج في طلب GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المنتج مطلوب'
    ]);
    exit();
}

// تنظيف المدخلات
$product_id = (int)$_GET['id'];

// التحقق من صحة القيمة
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المنتج غير صالح'
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

// التحقق من صلاحيات المستخدم (اختياري - حسب احتياجات النظام)
// يمكن إضافة التحقق من أن المستخدم الحالي هو مالك المنتج
if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    $user_id = (int)$_SESSION['uid_indm'];
    
    // التحقق من أن المنتج يخص المستخدم الحالي
    $check_owner_sql = "SELECT pd_uid FROM products WHERE pd_id = {$product_id} LIMIT 1";
    $check_owner_result = mysqli_query($con, $check_owner_sql);
    
    if (!$check_owner_result) {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ في التحقق من صلاحيات المستخدم: ' . mysqli_error($con)
        ]);
        exit();
    }
    
    if (mysqli_num_rows($check_owner_result) > 0) {
        $owner_row = mysqli_fetch_assoc($check_owner_result);
        if ($owner_row['pd_uid'] != $user_id) {
            echo json_encode([
                'success' => false,
                'message' => 'ليس لديك صلاحية لحذف هذا الملف'
            ]);
            exit();
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'المنتج غير موجود'
        ]);
        exit();
    }
}

// جلب معلومات الملف قبل الحذف
$select_sql = "SELECT pd_pdf_attach FROM products WHERE pd_id = {$product_id} LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في جلب بيانات المنتج: ' . mysqli_error($con)
    ]);
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'المنتج غير موجود في قاعدة البيانات'
    ]);
    exit();
}

$rowk = mysqli_fetch_object($select_result);
$file_path = "";

// حذف الملف الفعلي من السيرفر إذا كان موجوداً
if (!empty($rowk->pd_pdf_attach)) {
    $file_path = "upload/productdoc/" . $rowk->pd_pdf_attach;
    
    if (file_exists($file_path)) {
        if (!unlink($file_path)) {
            // فشل في حذف الملف - لكن نستمر في تحديث قاعدة البيانات
            error_log("فشل في حذف ملف PDF: " . $file_path);
        }
    } else {
        error_log("ملف PDF غير موجود: " . $file_path);
    }
} else {
    // لا يوجد ملف لحذفه
    echo json_encode([
        'success' => true,
        'message' => 'لا يوجد ملف PDF مرفق مع هذا المنتج',
        'action' => 'no_file'
    ]);
    exit();
}

// تحديث حقل الملف في قاعدة البيانات ليكون فارغاً
$update_sql = "UPDATE products SET pd_pdf_attach = '' WHERE pd_id = {$product_id}";
$update_result = mysqli_query($con, $update_sql);

if ($update_result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف ملف PDF بنجاح',
            'action' => 'deleted',
            'file_path' => $file_path,
            'affected_rows' => mysqli_affected_rows($con)
        ]);
    } else {
        // لم يتم تحديث أي صف (ربما الملف تم حذفه بالفعل)
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الملف ولكن لم يتم تحديث قاعدة البيانات',
            'action' => 'file_deleted_db_not_updated',
            'file_path' => $file_path
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'فشل في تحديث بيانات المنتج: ' . mysqli_error($con),
        'error' => mysqli_error($con)
    ]);
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>