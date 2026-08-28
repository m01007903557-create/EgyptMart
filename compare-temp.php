<?php
/**
 * File: compare-temp.php

 * Version: PHP 8.3
 * Description: جلب وعرض معلومات المنتجات المحددة للمقارنة
 * 
 * هذا الملف يستقبل معرفات المنتجات ويعرض معلوماتها
 * يمكن استخدامه لعرض المنتجات المختارة للمقارنة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
require_once 'common.php';

// تعيين نوع المحتوى إلى HTML
header('Content-Type: text/html; charset=UTF-8');

// التحقق من وجود معرفات المنتجات في طلب GET
if (!isset($_GET['products']) || empty($_GET['products'])) {
    echo '<div style="color:red; padding:20px; text-align:center;">لا توجد منتجات محددة للمقارنة</div>';
    exit();
}

// تنظيف المدخلات
$product_ids = $_GET['products'];

// إزالة أي أحرف غير رقمية أو فواصل
$product_ids = preg_replace('/[^0-9,]/', '', $product_ids);

// التحقق من صحة القيمة بعد التنظيف
if (empty($product_ids)) {
    echo '<div style="color:red; padding:20px; text-align:center;">معرفات المنتجات غير صالحة</div>';
    exit();
}

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    echo '<div style="color:red; padding:20px; text-align:center;">خطأ في الاتصال بقاعدة البيانات</div>';
    error_log("خطأ في الاتصال بقاعدة البيانات في get-compare-products.php");
    exit();
}

// تحويل المعرفات إلى أعداد صحيحة للتأكد من سلامتها
$ids_array = array_map('intval', explode(',', $product_ids));
$ids_array = array_filter($ids_array, function($id) {
    return $id > 0;
});

if (empty($ids_array)) {
    echo '<div style="color:red; padding:20px; text-align:center;">معرفات المنتجات غير صالحة</div>';
    exit();
}

$clean_ids = implode(',', $ids_array);

// استعلام جلب المنتجات
$view_product = "SELECT * FROM products WHERE pd_id IN ({$clean_ids})";
$run_query = mysqli_query($con, $view_product);

if (!$run_query) {
    echo '<div style="color:red; padding:20px; text-align:center;">خطأ في جلب المنتجات: ' . mysqli_error($con) . '</div>';
    error_log("خطأ في استعلام المنتجات: " . mysqli_error($con));
    exit();
}

$product_count = mysqli_num_rows($run_query);

if ($product_count == 0) {
    echo '<div style="color:orange; padding:20px; text-align:center;">لا توجد منتجات مطابقة</div>';
    exit();
}

// عرض المنتجات
echo '<div style="padding:10px; background-color:#f9f9f9; border:1px solid #ddd; border-radius:5px;">';
echo '<h3 style="margin-top:0; color:#333; border-bottom:1px solid #ddd; padding-bottom:10px;">المنتجات المحددة للمقارنة (' . $product_count . ')</h3>';

while ($row = mysqli_fetch_array($run_query, MYSQLI_ASSOC)) {
    // تنظيف وعرض بيانات المنتج
    $product_id = isset($row['pd_id']) ? (int)$row['pd_id'] : 0;
    $product_title = isset($row['pd_title']) ? htmlspecialchars($row['pd_title']) : 'غير محدد';
    $product_price = isset($row['pd_fob_price']) ? htmlspecialchars($row['pd_fob_price']) : 'غير محدد';
    
    echo '<div style="margin-bottom:15px; padding:10px; background-color:#fff; border:1px solid #eee; border-radius:3px;">';
    echo '<strong>معرف المنتج:</strong> ' . $product_id . '<br>';
    echo '<strong>اسم المنتج:</strong> ' . $product_title . '<br>';
    echo '<strong>السعر:</strong> ' . $product_price . '<br>';
    echo '</div>';
}

echo '</div>';

// إنهاء المخزن المؤقت
ob_end_flush();
?>