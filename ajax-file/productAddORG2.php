<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    echo "0|Please login first";
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// استقبال البيانات
$pd_subcat_id = isset($_POST['pd_subcat_id']) ? (int)$_POST['pd_subcat_id'] : 0;
$pd_title = isset($_POST['pd_title']) ? trim($_POST['pd_title']) : '';
$pd_code = isset($_POST['pd_code']) ? trim($_POST['pd_code']) : '';
$pd_desc = isset($_POST['pd_desc']) ? trim($_POST['pd_desc']) : '';
$pd_min_order_qty = isset($_POST['pd_min_order_qty']) ? (float)$_POST['pd_min_order_qty'] : 0;
$pd_unit = isset($_POST['pd_unit']) ? (int)$_POST['pd_unit'] : 0;
$pd_fob_price = isset($_POST['pd_fob_price']) ? (float)$_POST['pd_fob_price'] : 0;
$pd_fob_price2 = isset($_POST['pd_fob_price2']) ? (float)$_POST['pd_fob_price2'] : 0;
$pd_currency = isset($_POST['pd_currency']) ? (int)$_POST['pd_currency'] : 0;
$pd_preferred_buyer_location = isset($_POST['pd_preferred_buyer_location']) ? $_POST['pd_preferred_buyer_location'] : 'any';

// التحقق من صحة البيانات
if ($pd_subcat_id <= 0) {
    echo "0|Please choose product category";
    exit;
}

if (empty($pd_title)) {
    echo "0|Please enter product name";
    exit;
}

if ($pd_min_order_qty <= 0) {
    echo "0|Please enter valid minimum order quantity";
    exit;
}

if ($pd_unit <= 0) {
    echo "0|Please choose measurement unit";
    exit;
}

if ($pd_fob_price <= 0) {
    echo "0|Please enter valid FOB price";
    exit;
}

if ($pd_currency <= 0) {
    echo "0|Please choose currency";
    exit;
}

// جلب الصور المؤقتة
$pd_image = '';
$pd_logo = '';

$sql_img = "SELECT tpi_image, tpi_type FROM temp_product_image WHERE tpi_usr_id = $uid";
$res_img = mysqli_query($con, $sql_img);
if ($res_img) {
    while ($row = mysqli_fetch_assoc($res_img)) {
        if ($row['tpi_type'] == 'product') {
            $pd_image = $row['tpi_image'];
        } elseif ($row['tpi_type'] == 'logo') {
            $pd_logo = $row['tpi_image'];
        }
    }
}

// إدراج المنتج
$sql = "INSERT INTO products (
    pd_subcat_id, pd_uid, pd_title, pd_code, pd_desc,
    pd_min_order_qty, pd_unit, pd_fob_price, pd_fob_price2,
    pd_currency, pd_preferred_buyer_location, pd_image, pd_imagelogo,
    pd_status, pd_date
) VALUES (
    $pd_subcat_id, $uid, '$pd_title', '$pd_code', '$pd_desc',
    $pd_min_order_qty, $pd_unit, $pd_fob_price, $pd_fob_price2,
    $pd_currency, '$pd_preferred_buyer_location', '$pd_image', '$pd_logo',
    0, NOW()
)";

if (mysqli_query($con, $sql)) {
    $product_id = mysqli_insert_id($con);
    
    // حذف الصور المؤقتة
    mysqli_query($con, "DELETE FROM temp_product_image WHERE tpi_usr_id = $uid");
    
    echo "1|Product added successfully! Product ID: $product_id - Waiting for admin approval.";
} else {
    echo "0|Database error: " . mysqli_error($con);
}
?>