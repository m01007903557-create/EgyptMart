<?php
/**
 * File: ajax/updateSaleOffer.php
 * Description: تحديث بيانات عرض البيع
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['so_id']) || !is_numeric($_POST['so_id'])) {
    die("0|Invalid offer ID");
}

// تنظيف وتحضير البيانات
$so_id = (int)$_POST['so_id'];
$so_pc_id = trim($_POST['so_pc_id'] ?? '');
$so_service = trim($_POST['so_service'] ?? '');
$so_description = trim($_POST['so_description'] ?? '');
$so_preferred_buyer_location = trim($_POST['so_preferred_buyer_location'] ?? '');
$change_validity = trim($_POST['change_validity'] ?? 'no');
$so_validity = trim($_POST['so_validity'] ?? '');

// التحقق من صحة البيانات
if (empty($so_service)) {
    die("0|Service/Product name is required");
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
$service = strtoupper($so_service);
$description = strtoupper($so_description);
$valid = true;

// التحقق من اسم الخدمة/المنتج
if (!empty($so_service) && $valid) {
    foreach ($letters as $val) {
        if (str_contains($service, $val)) {
            echo "0|You can't post words like '" . htmlspecialchars($val) . "' in Products / Services you want to sell.";
            $valid = false;
            break;
        }
    }
}

// التحقق من الوصف
if (!empty($so_description) && $valid) {
    foreach ($letters as $val) {
        if (str_contains($description, $val)) {
            echo "0|You can't post words like '" . htmlspecialchars($val) . "' in Products / Services in detail.";
            $valid = false;
            break;
        }
    }
}

// تحديث البيانات إذا كانت صالحة
if ($valid) {
    global $con;
    
    if ($change_validity === 'yes' && !empty($so_validity)) {
        $sql = "UPDATE sale_offer SET
                    so_pc_id = ?,
                    so_service = ?,
                    so_description = ?,
                    so_preferred_buyer_location = ?,
                    so_validity = ?
                WHERE so_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssi', 
            $so_pc_id, 
            $so_service, 
            $so_description, 
            $so_preferred_buyer_location,
            $so_validity,
            $so_id
        );
    } else {
        $sql = "UPDATE sale_offer SET
                    so_pc_id = ?,
                    so_service = ?,
                    so_description = ?,
                    so_preferred_buyer_location = ?
                WHERE so_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssi', 
            $so_pc_id, 
            $so_service, 
            $so_description, 
            $so_preferred_buyer_location,
            $so_id
        );
    }
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        
        // تضمين ملف إرسال الإيميل
        require_once __DIR__ . '/../selloffer-email.php';
        
        echo "1|Sale Offer updated successfully.";
    } else {
        error_log("Update Sale Offer Error: " . mysqli_error($con) . " | Offer ID: $so_id");
        echo "0|Failed to update sale offer. Please try again.";
    }
}
?>