<?php
/**
 * File: ajax/delBuyleadAlertCat.php

 * Description: حذف تصنيف من تنبيهات الشراء مع حذف المنتجات المرتبطة به
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "0|Unauthorized";
    exit;
}

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من وجود المعرفات المطلوبة
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid alert ID";
    exit;
}

if (!isset($_POST['cat_id']) || !is_numeric($_POST['cat_id'])) {
    http_response_code(400);
    echo "0|Invalid category ID";
    exit;
}

$bac_id = (int)$_POST['id'];
$cat_id = (int)$_POST['cat_id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // جلب اسم التصنيف
    $category_sql = "SELECT pc_name FROM product_category WHERE pc_id = ? LIMIT 1";
    $stmt_cat = mysqli_prepare($con, $category_sql);
    mysqli_stmt_bind_param($stmt_cat, 'i', $cat_id);
    mysqli_stmt_execute($stmt_cat);
    $result_cat = mysqli_stmt_get_result($stmt_cat);
    $row_cat = mysqli_fetch_object($result_cat);
    mysqli_stmt_close($stmt_cat);

    if (!$row_cat) {
        throw new Exception("Category not found");
    }

    $key_cat_name = $row_cat->pc_name ?? '';

    // حذف المنتجات المرتبطة بهذا التصنيف للمستخدم
    if (!empty($key_cat_name)) {
        $delete_products_sql = "DELETE FROM product_buy WHERE pdby_title = ? AND pdby_uid = ?";
        $stmt_products = mysqli_prepare($con, $delete_products_sql);
        mysqli_stmt_bind_param($stmt_products, 'si', $key_cat_name, $current_user);
        
        if (!mysqli_stmt_execute($stmt_products)) {
            throw new Exception("Failed to delete related products: " . mysqli_error($con));
        }
        
        mysqli_stmt_close($stmt_products);
    }

    // حذف التصنيف من تنبيهات الشراء
    $delete_alert_sql = "DELETE FROM buylead_alert_category WHERE bac_id = ? AND bac_usr_id = ?";
    $stmt_alert = mysqli_prepare($con, $delete_alert_sql);
    mysqli_stmt_bind_param($stmt_alert, 'ii', $bac_id, $current_user);

    if (!mysqli_stmt_execute($stmt_alert)) {
        throw new Exception("Failed to delete alert category: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt_alert) == 0) {
        throw new Exception("Alert category not found or already deleted");
    }

    mysqli_stmt_close($stmt_alert);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|Category and related products deleted successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete Buy Alert Category Error: " . $e->getMessage() . " | Alert ID: $bac_id, Cat ID: $cat_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>