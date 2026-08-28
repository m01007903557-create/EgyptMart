<?php
/**
 * File: ajax/aprodutcustomcategory.php

 * Description: إضافة تصنيف مؤقت للتنبيهات (بيع/شراء/مناقصات/مزادات)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

error_reporting(0);
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!session_id()) {
    session_start();
}

// التحقق من وجود كلمات البحث
if (!isset($_POST['keywordsFilter']) || empty($_POST['keywordsFilter'])) {
    echo '<script>alert("Enter keyword first");</script>';
    exit;
}

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    echo '<script>alert("Please login first");</script>';
    exit;
}

// التحقق من وجود المنتجات في الجلسة
$searchedproducts = $_SESSION['searchedproducts'] ?? [];

if (empty($searchedproducts) || !is_array($searchedproducts)) {
    echo '<script>alert("No products found. Please search again.");</script>';
    exit;
}

// معالجة كلمات البحث
$keywordsFilters = $_POST['keywordsFilter'];
$keywords_parts = explode(">>", $keywordsFilters);
$last_part = end($keywords_parts);
$_POST['keywordsFilter'] = $last_part;

// التحقق من وجود التصنيف في المصفوفة
if (!array_key_exists($_POST['keywordsFilter'], $searchedproducts)) {
    echo '<script>alert("No product found with that name");</script>';
    exit;
}

$id = $searchedproducts[$_POST['keywordsFilter']];

if (empty($id)) {
    echo '<script>alert("Invalid category ID");</script>';
    exit;
}

require_once __DIR__ . '/../common.php';

global $con;

// تحديد نوع التنبيه
$alert_type = $_POST['type'] ?? '';
$user_id = (int)$_SESSION['uid_indm'];
$category_id = (int)$id;
$current_time = date('Y-m-d H:i:s');

// تحديد الجدول المناسب حسب النوع
$table = '';
$field_prefix = '';

switch ($alert_type) {
    case 'addTempAuctionAlertCat':
        $table = 'temp_auction_alert_cat';
        $field_prefix = 'taac';
        break;
    case 'addTempBuyleadAlertCat':
        $table = 'temp_buylead_alert_cat';
        $field_prefix = 'tbac';
        break;
    case 'addTempTenderAlertCat':
        $table = 'temp_tender_alert_cat';
        $field_prefix = 'ttac';
        break;
    default:
        $table = 'temp_selloffer_alert_cat';
        $field_prefix = 'tsac';
        break;
}

// التحقق من عدم وجود التصنيف مسبقاً
$check_sql = "SELECT {$field_prefix}_id FROM $table 
               WHERE {$field_prefix}_usr_id = ? AND {$field_prefix}_pc_id = ? 
               LIMIT 1";

$stmt_check = mysqli_prepare($con, $check_sql);
mysqli_stmt_bind_param($stmt_check, 'ii', $user_id, $category_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) == 0) {
    mysqli_stmt_close($stmt_check);
    
    // إدراج التصنيف
    $insert_sql = "INSERT INTO $table ({$field_prefix}_usr_id, {$field_prefix}_pc_id, {$field_prefix}_updated_date) 
                   VALUES (?, ?, ?)";
    
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'iis', $user_id, $category_id, $current_time);
    
    if (!mysqli_stmt_execute($stmt_insert)) {
        error_log("Add Temp Alert Error: " . mysqli_error($con) . " | Type: $alert_type, User: $user_id, Cat: $category_id");
        echo '<script>alert("Failed to add category");</script>';
        mysqli_stmt_close($stmt_insert);
        mysqli_close($con);
        exit;
    }
    
    mysqli_stmt_close($stmt_insert);
} else {
    mysqli_stmt_close($stmt_check);
}
?>

<div class="setcat" id="<?php echo $category_id; ?>" style="display:block;">
    &bull;&nbsp;<?php echo htmlspecialchars($keywordsFilters, ENT_QUOTES, 'UTF-8'); ?>
    <a href="javascript:remove(<?php echo $category_id; ?>)">
        <img src="images/remove.gif" height="10" hspace="6" width="44" alt="Remove">
    </a>
</div>

<?php
mysqli_close($con);
?>