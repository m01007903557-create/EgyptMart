<?php
/**
 * File: del_pldtmp-news.php

 * Version: PHP 8.3
 * Description: حذف صورة أخبار مؤقتة (صغيرة أو كبيرة)
 * 
 * هذا الملف يقوم بحذف صورة أخبار مؤقتة من المجلد المناسب
 * بناءً على نوع الصورة (1 = صورة صغيرة، 2 = صورة كبيرة)
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

// التحقق من وجود نوع الصورة في طلب GET
if (!isset($_GET['type']) || empty($_GET['type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'نوع الصورة مطلوب'
    ]);
    exit();
}

// تنظيف المدخلات
$image_id = (int)$_GET['imid'];
$type = (int)$_GET['type'];

// التحقق من صحة القيم
if ($image_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف الصورة غير صالح'
    ]);
    exit();
}

if (!in_array($type, [1, 2])) {
    echo json_encode([
        'success' => false,
        'message' => 'نوع الصورة غير صالح. يجب أن يكون 1 أو 2'
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

// تحديد المسار المناسب حسب نوع الصورة
$folder_path = ($type == 1) ? "upload/mynews/small/" : "upload/mynews/large/";
$image_type_text = ($type == 1) ? "صورة صغيرة" : "صورة كبيرة";

// جلب معلومات الصورة قبل الحذف
$select_sql = "SELECT * FROM temp_newsimage 
               WHERE tmpns_id = {$image_id} 
               AND tmpns_status = {$type} 
               LIMIT 1";

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
        'message' => 'الصورة غير موجودة في قاعدة البيانات'
    ]);
    exit();
}

$rowk = mysqli_fetch_object($select_result);
$image_path = "";

// حذف الملف الفعلي من السيرفر
if (!empty($rowk->tmpns_image)) {
    $image_path = $folder_path . $rowk->tmpns_image;
    
    if (file_exists($image_path)) {
        if (!unlink($image_path)) {
            // فشل في حذف الملف - لكن نستمر في حذف سجل قاعدة البيانات
            error_log("فشل في حذف {$image_type_text}: " . $image_path);
        }
    } else {
        error_log("ملف {$image_type_text} غير موجود: " . $image_path);
    }
} else {
    // لا يوجد اسم ملف في قاعدة البيانات
    echo json_encode([
        'success' => true,
        'message' => 'لا يوجد ملف مرتبط بهذه الصورة',
        'action' => 'no_file'
    ]);
    // نستمر في حذف السجل
}

// حذف السجل من قاعدة البيانات
$delete_sql = "DELETE FROM temp_newsimage WHERE tmpns_id = {$image_id} AND tmpns_status = {$type}";
$delete_result = mysqli_query($con, $delete_sql);

if ($delete_result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        echo json_encode([
            'success' => true,
            'message' => "تم حذف {$image_type_text} بنجاح",
            'action' => 'deleted',
            'type' => $type,
            'image_type' => $image_type_text,
            'image_path' => $image_path,
            'affected_rows' => mysqli_affected_rows($con)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'لم يتم العثور على الصورة في قاعدة البيانات',
            'action' => 'not_found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'فشل في حذف الصورة: ' . mysqli_error($con),
        'error' => mysqli_error($con)
    ]);
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>