<?php
/**
 * File: compare_add_product.php
 * Version: PHP 8.3
 * Description: إضافة منتج إلى قائمة المقارنة باستخدام الكوكيز
 * 
 * هذا الملف يستقبل طلب AJAX لإضافة منتج إلى قائمة المقارنة
 * ويعيد عدد المنتجات في القائمة بعد الإضافة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تعيين نوع المحتوى إلى نص عادي (للاستجابة)
header('Content-Type: text/plain');

// التحقق من وجود معرف المنتج في طلب POST
if (empty($_POST['product_id'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$product_id = (int)$_POST['product_id'];

// التحقق من صحة القيمة
if ($product_id <= 0) {
    echo "0";
    exit();
}

// تهيئة مصفوفة معرفات المنتجات
$product_ids = [];

// قراءة الكوكيز الموجودة
if (isset($_COOKIE['productids']) && !empty($_COOKIE['productids'])) {
    $existing_ids = $_COOKIE['productids'];
    
    // إذا كانت الكوكيز سلسلة نصية، حولها إلى مصفوفة
    if (is_string($existing_ids)) {
        $product_ids = explode(',', $existing_ids);
    } elseif (is_array($existing_ids)) {
        $product_ids = $existing_ids;
    }
}

// تنظيف المصفوفة من القيم الفارغة
$product_ids = array_filter($product_ids, function($value) {
    return !empty($value) && is_numeric($value);
});

// التحقق مما إذا كان المنتج موجوداً بالفعل
if (!in_array($product_id, $product_ids)) {
    // إضافة المنتج الجديد
    $product_ids[] = $product_id;
}

// تنظيف المصفوفة مرة أخرى
$product_ids = array_filter($product_ids, function($value) {
    return !empty($value) && is_numeric($value);
});

// تحويل المصفوفة إلى سلسلة نصية
$product_ids_string = implode(',', $product_ids);

// تخزين في كوكي لمدة 30 يوماً
setcookie('productids', $product_ids_string, time() + (86400 * 30), '/');

// إرجاع عدد المنتجات
echo count($product_ids);

// إنهاء المخزن المؤقت
ob_end_flush();
?>