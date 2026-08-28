<?php
/**
 * File: ajax/getSubCategories.php
 * Description: جلب الفئات الفرعية للتنبيهات (شراء/بيع/مناقصات/مزادات)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    die("Please login to continue");
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Invalid category ID");
}

if (!isset($_POST['type']) || empty($_POST['type'])) {
    die("Invalid alert type");
}

$pc_parent_id = (int)$_POST['id'];
$type = trim($_POST['type']);
$user_id = (int)$_SESSION['uid_indm'];

// تحديد أسماء الجداول بناءً على النوع
$tableConfig = [
    'buy' => [
        'main_table' => 'buylead_alert_category',
        'main_field' => 'bac_pc_id',
        'temp_table' => 'temp_buylead_alert_cat',
        'temp_field' => 'tbac_pc_id'
    ],
    'sell' => [
        'main_table' => 'selloffer_alert_category',
        'main_field' => 'sac_pc_id',
        'temp_table' => 'temp_selloffer_alert_cat',
        'temp_field' => 'tsac_pc_id'
    ],
    'tender' => [
        'main_table' => 'tender_alert_category',
        'main_field' => 'tac_pc_id',
        'temp_table' => 'temp_tender_alert_cat',
        'temp_field' => 'ttac_pc_id'
    ],
    'auction' => [
        'main_table' => 'auction_alert_category',
        'main_field' => 'aac_pc_id',
        'temp_table' => 'temp_auction_alert_cat',
        'temp_field' => 'taac_pc_id'
    ]
];

if (!array_key_exists($type, $tableConfig)) {
    die("Invalid alert type");
}

$config = $tableConfig[$type];

global $con;

// جلب الفئات الفرعية غير المشترك فيها
$sql = "SELECT pc_id, pc_name 
        FROM product_category 
        WHERE pc_parent_id = ? 
        AND pc_id NOT IN (
            SELECT {$config['main_field']} 
            FROM {$config['main_table']} 
            WHERE {$config['main_table']}.{$config['main_field']} = product_category.pc_id 
            AND {$config['main_table']}.{$config['main_field']} IS NOT NULL
        )
        AND pc_status = 1
        ORDER BY pc_order, pc_name ASC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $pc_parent_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// عرض الفئات
while ($row = mysqli_fetch_object($result)) {
    // التحقق مما إذا كانت الفئة في الجدول المؤقت
    $sql_chk = "SELECT COUNT(*) as count 
                FROM {$config['temp_table']} 
                WHERE {$config['temp_field']} = ? 
                AND {$config['temp_table']}.usr_id = ?";
    
    $stmt_chk = mysqli_prepare($con, $sql_chk);
    mysqli_stmt_bind_param($stmt_chk, 'ii', $row->pc_id, $user_id);
    mysqli_stmt_execute($stmt_chk);
    $result_chk = mysqli_stmt_get_result($stmt_chk);
    $row_chk = mysqli_fetch_assoc($result_chk);
    $is_checked = ($row_chk['count'] > 0);
    
    mysqli_stmt_close($stmt_chk);
    
    // عرض checkbox
    $pc_id = (int)$row->pc_id;
    $pc_name = htmlspecialchars(ucwords($row->pc_name ?? ''), ENT_QUOTES, 'UTF-8');
    $checked_attr = $is_checked ? 'checked="checked"' : '';
    
    echo "<input type=\"checkbox\" 
                 name=\"scat_{$pc_id}\" 
                 id=\"scat_{$pc_id}\" 
                 value=\"{$pc_id}\" 
                 onclick=\"scatAddDel('{$pc_id}')\" 
                 {$checked_attr}>" . $pc_name . "<br>";
}

mysqli_stmt_close($stmt);
?>