<?php
/**
 * File: ajax/productAdd.php

 * Description: حفظ منتج جديد مع التحقق من الكلمات الممنوعة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "0|<font color='#CC0000'>Please login first</font>";
    exit;
}

// تنظيف وتحضير البيانات
$uid = (int)$_SESSION['uid_indm'];
$pd_subcat_id = (int)trim($_POST['pd_subcat_id'] ?? 0);
$pd_title = trim($_POST['pd_title'] ?? '');
$pd_code = trim($_POST['pd_code'] ?? '');
$pd_desc = trim($_POST['pd_desc'] ?? '');
$pd_min_order_qty = (float)trim($_POST['pd_min_order_qty'] ?? 0);
$pd_unit = (int)trim($_POST['pd_unit'] ?? 0);
$pd_fob_price = (float)trim($_POST['pd_fob_price'] ?? 0);
$pd_fob_price2 = (float)trim($_POST['pd_fob_price2'] ?? 0);
$pd_currency = (int)trim($_POST['pd_currency'] ?? 0);
$pd_preferred_buyer_location = trim($_POST['pd_preferred_buyer_location'] ?? '');
$pd_status = (int)($_POST['pd_status'] ?? 0);
$pd_brand_name = trim($_POST['pd_brand'] ?? '');
$pd_payment = trim($_POST['pd_payment'] ?? '');
$pd_pod = trim($_POST['pd_pod'] ?? '');
$pd_pn_capct = trim($_POST['pd_pn_capct'] ?? '');
$pd_dlv_time = trim($_POST['pd_dlv_time'] ?? '');
$pd_pck_dets = trim($_POST['pd_pck_dets'] ?? '');

global $con;

// جلب قائمة الكلمات الممنوعة
$letters = [];
$sqlrpl = "SELECT bd_word FROM bad_word WHERE bd_status = 1";
$resrpl = mysqli_query($con, $sqlrpl);
if ($resrpl) {
    while ($rowrpl = mysqli_fetch_object($resrpl)) {
        $letters[] = strtoupper($rowrpl->bd_word);
    }
}

$title = strtoupper($pd_title);
$desc = strtoupper($pd_desc);
$valid = true;
$data = [];

// التحقق من صحة البيانات
if ($pd_subcat_id <= 0) {
    $data[0] = "0";
    $data[1] = '<font color="#CC0000">Please choose the Product subcategory here.</font>';
    $valid = false;
} elseif (empty($pd_title)) {
    $data[0] = "0";
    $data[1] = '<font color="#CC0000">Please enter the Product Name.</font>';
    $valid = false;
} elseif (!empty($pd_title)) {
    foreach ($letters as $val) {
        if (str_contains($title, $val)) {
            $data[0] = "0";
            $data[1] = "<font color='#CC0000'>You can't post words like '" . htmlspecialchars($val) . "' in Product Name.</font>";
            $valid = false;
            break;
        }
    }
} elseif (strlen($pd_desc) > 4000) {
    $data[0] = "0";
    $data[1] = '<font color="#CC0000">Please check that Product Description cannot have more than 4000 characters.</font>';
    $valid = false;
} elseif (!empty($pd_desc)) {
    foreach ($letters as $val) {
        if (str_contains($desc, $val)) {
            $data[0] = "0";
            $data[1] = '<font color="#CC0000">You can\'t post words like ' . htmlspecialchars($val) . ' in Product Description.</font>';
            $valid = false;
            break;
        }
    }
} elseif ($pd_min_order_qty <= 0) {
    $data[0] = "0";
    $data[1] = '<font color="#CC0000">Please enter valid Minimum Order Quantity.</font>';
    $valid = false;
} elseif ($pd_unit <= 0) {
    $data[0] = "0";
    $data[1] = '<font color="#CC0000">Please choose Measurement Unit for Minimum Order Quantity.</font>';
    $valid = false;
} elseif ($pd_fob_price <= 0) {
    $data[0] = "0";
    $data[1] = '<font color="#CC0000">Please enter valid FOB Price.</font>';
    $valid = false;
} elseif ($pd_fob_price2 <= 0) {
    $data[0] = "0";
    $data[1] = '<font color="#CC0000">Please enter valid FOB Price (Range).</font>';
    $valid = false;
} elseif ($pd_currency <= 0) {
    $data[0] = "0";
    $data[1] = '<font color="#CC0000">Please choose Currency.</font>';
    $valid = false;
} else {
    $valid = true;
}

if ($valid) {
    // جلب الصور المؤقتة
    $tmp_sql = "SELECT tpi_image, tpi_logo FROM temp_product_image WHERE tpi_usr_id = ? LIMIT 1";
    $stmt_tmp = mysqli_prepare($con, $tmp_sql);
    mysqli_stmt_bind_param($stmt_tmp, 'i', $uid);
    mysqli_stmt_execute($stmt_tmp);
    $result_tmp = mysqli_stmt_get_result($stmt_tmp);
    $tmpimagerow = mysqli_fetch_object($result_tmp);
    mysqli_stmt_close($stmt_tmp);
    
    $pd_image = $tmpimagerow ? ($tmpimagerow->tpi_image ?? '') : '';
    $pd_logo = $tmpimagerow ? ($tmpimagerow->tpi_logo ?? '') : '';
    
    // إدراج المنتج
    $insert_sql = "INSERT INTO products SET
                    pd_subcat_id = ?,
                    pd_uid = ?,
                    pd_title = ?,
                    pd_code = ?,
                    pd_min_order_qty = ?,
                    pd_unit = ?,
                    pd_fob_price = ?,
                    pd_fob_price2 = ?,
                    pd_currency = ?,
                    pd_desc = ?,
                    pd_image = ?,
                    pd_imagelogo = ?,
                    pd_date = NOW(),
                    pd_preferred_buyer_location = ?,
                    pd_hot = ?,
                    brand_name = ?,
                    pd_payment = ?,
                    pd_pod = ?,
                    pd_pn_capct = ?,
                    pd_dlv_time = ?,
                    pd_pck_dets = ?";
    
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'iissiiddissssissssss', 
        $pd_subcat_id,
        $uid,
        $pd_title,
        $pd_code,
        $pd_min_order_qty,
        $pd_unit,
        $pd_fob_price,
        $pd_fob_price2,
        $pd_currency,
        $pd_desc,
        $pd_image,
        $pd_logo,
        $pd_preferred_buyer_location,
        $pd_status,
        $pd_brand_name,
        $pd_payment,
        $pd_pod,
        $pd_pn_capct,
        $pd_dlv_time,
        $pd_pck_dets
    );
    
    if (mysqli_stmt_execute($stmt_insert)) {
        mysqli_stmt_close($stmt_insert);
        
        // حذف الصور المؤقتة
        $delete_sql = "DELETE FROM temp_product_image WHERE tpi_usr_id = ?";
        $stmt_delete = mysqli_prepare($con, $delete_sql);
        mysqli_stmt_bind_param($stmt_delete, 'i', $uid);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
        
        // إضافة التصنيف إلى تنبيهات الشراء إذا لم يكن موجوداً
        $check_sql = "SELECT bac_id FROM buylead_alert_category 
                      WHERE bac_usr_id = ? AND bac_pc_id = ? LIMIT 1";
        $stmt_check = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'ii', $uid, $pd_subcat_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) == 0) {
            mysqli_stmt_close($stmt_check);
            
            $insert_alert_sql = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) 
                                 VALUES (?, ?, NOW())";
            $stmt_alert = mysqli_prepare($con, $insert_alert_sql);
            mysqli_stmt_bind_param($stmt_alert, 'ii', $uid, $pd_subcat_id);
            mysqli_stmt_execute($stmt_alert);
            mysqli_stmt_close($stmt_alert);
        } else {
            mysqli_stmt_close($stmt_check);
        }
        
        $data[0] = "1";
        $data[1] = 'Product added successfully! Please wait for Admin Approval.';
    } else {
        error_log("Save Product Error: " . mysqli_error($con) . " | User: $uid");
        $data[0] = "0";
        $data[1] = '<font color="#CC0000">Failed to add product. Please try again.</font>';
    }
}

echo $data[0] . "|" . $data[1];
?>