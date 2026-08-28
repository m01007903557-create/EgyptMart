<?php
/**
 * File: admin/check-exists.php

 * Version: PHP 8.3
 * Description: التحقق من وجود ملف مرفوع مسبقاً باستخدام UploadiFive
 * 
 * هذا الملف يتأكد من وجود ملف في مجلد التحميلات
 * ويعيد 1 إذا كان الملف موجوداً، 0 إذا لم يكن موجوداً
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل (اختياري)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تعيين نوع المحتوى إلى نص عادي
header('Content-Type: text/plain; charset=UTF-8');

// التحقق من وجود اسم الملف في طلب POST
if (!isset($_POST['filename']) || empty($_POST['filename'])) {
    echo 0;
    exit();
}

// تنظيف اسم الملف - إزالة أي مسارات أو أحرف خطرة
$filename = trim($_POST['filename']);
$filename = basename($filename); // التأكد من أننا نأخذ اسم الملف فقط
$filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename); // إزالة الأحرف غير المسموح بها

if (empty($filename)) {
    echo 0;
    exit();
}

// تعريف المجلد المستهدف
$targetFolder = '/uploads'; // يجب أن يتطابق مع المجلد في سكريبت الرفع

// بناء المسار الكامل
$fullPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder . '/' . $filename;

// التحقق من وجود الملف
if (file_exists($fullPath)) {
    echo 1;
} else {
    echo 0;
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>