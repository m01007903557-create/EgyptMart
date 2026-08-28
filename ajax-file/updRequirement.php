<?php
/**
 * File: ajax/updateBuyRequirement.php
 * Description: تحديث بيانات طلب شراء
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['br_id']) || !is_numeric($_POST['br_id'])) {
    die("0|Invalid requirement ID");
}

// تنظيف وتحضير البيانات
$br_id = (int)$_POST['br_id'];
$br_pc_id = trim($_POST['br_pc_id'] ?? '');
$br_pd_name = trim($_POST['br_pd_name'] ?? '');
$br_requirement = trim($_POST['br_requirement'] ?? '');
$br_estimate_qty = trim($_POST['br_estimate_qty'] ?? '');
$br_estimate_qty_unit = trim($_POST['br_estimate_qty_unit'] ?? '');
$br_preferred_supplier_location = trim($_POST['br_preferred_supplier_location'] ?? '');
$br_apprx_order_value = trim($_POST['br_apprx_order_value'] ?? '');
$br_apprx_order_currency = trim($_POST['br_apprx_order_currency'] ?? '');
$br_description = trim($_POST['br_description'] ?? '');
$br_website = trim($_POST['br_website'] ?? '');
$br_need_quote_for = trim($_POST['br_need_quote_for'] ?? '');
$br_purchase_time = trim($_POST['br_purchase_time'] ?? '');
$br_need_for = trim($_POST['br_need_for'] ?? '');
$br_requirement_frequency = trim($_POST['br_requirement_frequency'] ?? '');

// التحقق من صحة البيانات الأساسية
if (empty($br_pd_name)) {
    die("0|Product/Service name is required");
}

// جلب قائمة الكلمات الممنوعة
$letters = [];
$sqlrpl = "SELECT bd_word FROM bad_word WHERE bd_status = 1";
$resrpl = mysqli_query($GLOBALS['con'], $sqlrpl);

if ($resrpl) {
    while ($rowrpl = mysqli_fetch_object($resrpl)) {
        $letters[] = strtoupper($rowrpl->bd_word);
    }
}

// التحقق من الكلمات الممنوعة
$br_name = strtoupper($br_pd_name);
$requirement = strtoupper($br_requirement);
$valid = true;
$data = [];

// التحقق من اسم المنتج/الخدمة
if (!empty($br_pd_name) && $valid) {
    foreach ($letters as $val) {
        if (str_contains($br_name, $val)) {
            $data[0] = "0";
            $data[1] = "You can't post words like '" . htmlspecialchars($val) . "' in Products / Services Name.";
            $valid = false;
            break;
        }
    }
}

// التحقق من تفاصيل الطلب
if (!empty($br_requirement) && $valid) {
    foreach ($letters as $val) {
        if (str_contains($requirement, $val)) {
            $data[0] = "0";
            $data[1] = "You can't post words like '" . htmlspecialchars($val) . "' in Buying Requirements in detail.";
            $valid = false;
            break;
        }
    }
}

// تحديث البيانات إذا كانت صالحة
if ($valid) {
    global $con;
    
    $sql = "UPDATE buy_requirement SET
                br_pc_id = ?,
                br_pd_name = ?,
                br_requirement = ?,
                br_estimate_qty = ?,
                br_estimate_qty_unit = ?,
                br_preferred_supplier_location = ?,
                br_apprx_order_value = ?,
                br_apprx_order_currency = ?,
                br_description = ?,
                br_website = ?,
                br_need_quote_for = ?,
                br_purchase_time = ?,
                br_need_for = ?,
                br_requirement_frequency = ?,
                br_updated_date = NOW()
            WHERE br_id = ?";
    
    $stmt = mysqli_prepare($con, $sql);
    
    mysqli_stmt_bind_param($stmt, 'ssssssssssssssi', 
        $br_pc_id,
        $br_pd_name,
        $br_requirement,
        $br_estimate_qty,
        $br_estimate_qty_unit,
        $br_preferred_supplier_location,
        $br_apprx_order_value,
        $br_apprx_order_currency,
        $br_description,
        $br_website,
        $br_need_quote_for,
        $br_purchase_time,
        $br_need_for,
        $br_requirement_frequency,
        $br_id
    );
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        
        // تضمين ملف إرسال الإيميل
        require_once __DIR__ . '/../post-buy-req-email.php';
        
        $data[0] = "1";
        $data[1] = "Buy Requirement updated successfully.";
    } else {
        error_log("Update Buy Requirement Error: " . mysqli_error($con) . " | Requirement ID: $br_id");
        $data[0] = "0";
        $data[1] = "Failed to update buy requirement. Please try again.";
    }
}

echo $data[0] . "|" . $data[1];
?>