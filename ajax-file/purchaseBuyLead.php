<?php
/**
 * File: ajax/purchaseBuyLead.php

 * Description: شراء طلب شراء (Buy Lead) باستخدام الرصيد
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

// التحقق من وجود معرف طلب الشراء
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid requirement ID";
    exit;
}

$usr_id = (int)$_SESSION['uid_indm'];
$br_id = (int)$_POST['id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // التحقق من وجود طلب الشراء
    $check_sql = "SELECT br_id FROM buy_requirement WHERE br_id = ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'i', $br_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        throw new Exception("Buy requirement not found");
    }
    mysqli_stmt_close($stmt_check);
    
    // التحقق من عدم شراء نفس الطلب مسبقاً
    $dup_check = "SELECT pbr_id FROM purchased_buy_requirement 
                   WHERE pbr_usr_id = ? AND pbr_br_id = ? LIMIT 1";
    $stmt_dup = mysqli_prepare($con, $dup_check);
    mysqli_stmt_bind_param($stmt_dup, 'ii', $usr_id, $br_id);
    mysqli_stmt_execute($stmt_dup);
    $result_dup = mysqli_stmt_get_result($stmt_dup);
    
    if (mysqli_num_rows($result_dup) > 0) {
        throw new Exception("You have already purchased this buy lead");
    }
    mysqli_stmt_close($stmt_dup);
    
    // جلب بيانات المستخدم
    $sql_usr = "SELECT u.*, bp.* 
                FROM user u
                INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                WHERE u.usr_id = ? 
                LIMIT 1";
    
    $stmt_usr = mysqli_prepare($con, $sql_usr);
    mysqli_stmt_bind_param($stmt_usr, 'i', $usr_id);
    mysqli_stmt_execute($stmt_usr);
    $result_usr = mysqli_stmt_get_result($stmt_usr);
    $row_usr = mysqli_fetch_object($result_usr);
    mysqli_stmt_close($stmt_usr);
    
    if (!$row_usr) {
        throw new Exception("User not found");
    }
    
    // التحقق من كفاية الرصيد
    $credit_cost = 20;
    $current_credit = (int)($row_usr->usr_credit ?? 0);
    
    if ($current_credit < $credit_cost) {
        throw new Exception("Insufficient credit balance");
    }
    
    $new_balance = $current_credit - $credit_cost;
    
    // إدراج سجل الشراء
    $sql_insert = "INSERT INTO purchased_buy_requirement (pbr_usr_id, pbr_br_id, pbr_purchase_date) 
                   VALUES (?, ?, NOW())";
    
    $stmt_insert = mysqli_prepare($con, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, 'ii', $usr_id, $br_id);
    
    if (!mysqli_stmt_execute($stmt_insert)) {
        throw new Exception("Failed to insert purchase record");
    }
    mysqli_stmt_close($stmt_insert);
    
    // تحديث رصيد المستخدم
    $sql_upd = "UPDATE user SET usr_credit = ? WHERE usr_id = ?";
    $stmt_upd = mysqli_prepare($con, $sql_upd);
    mysqli_stmt_bind_param($stmt_upd, 'ii', $new_balance, $usr_id);
    
    if (!mysqli_stmt_execute($stmt_upd)) {
        throw new Exception("Failed to update user credit");
    }
    mysqli_stmt_close($stmt_upd);
    
    // إدراج سجل في billing_history
    $sql_bh = "INSERT INTO billing_history (bh_type, bh_usr_id, bh_from, bh_credit_used, bh_user_balance, bh_updated_date) 
               VALUES ('2', ?, ?, ?, ?, NOW())";
    
    $stmt_bh = mysqli_prepare($con, $sql_bh);
    mysqli_stmt_bind_param($stmt_bh, 'iiii', $usr_id, $br_id, $credit_cost, $new_balance);
    
    if (!mysqli_stmt_execute($stmt_bh)) {
        throw new Exception("Failed to insert billing history");
    }
    mysqli_stmt_close($stmt_bh);
    
    // جلب معلومات الدولة والولاية للمستخدم (للبريد الإلكتروني)
    $user_country = '';
    $user_state = '';
    
    if (!empty($row_usr->country)) {
        $sql_country = "SELECT cn_name FROM country WHERE cn_id = ? AND cn_status = 1 LIMIT 1";
        $stmt_country = mysqli_prepare($con, $sql_country);
        mysqli_stmt_bind_param($stmt_country, 'i', $row_usr->country);
        mysqli_stmt_execute($stmt_country);
        $result_country = mysqli_stmt_get_result($stmt_country);
        $row_country = mysqli_fetch_object($result_country);
        $user_country = $row_country ? $row_country->cn_name : '';
        mysqli_stmt_close($stmt_country);
    }
    
    if (!empty($row_usr->bnsprof_state)) {
        $sql_state = "SELECT state_name FROM states WHERE state_id = ? AND state_status = 1 LIMIT 1";
        $stmt_state = mysqli_prepare($con, $sql_state);
        mysqli_stmt_bind_param($stmt_state, 'i', $row_usr->bnsprof_state);
        mysqli_stmt_execute($stmt_state);
        $result_state = mysqli_stmt_get_result($stmt_state);
        $row_state = mysqli_fetch_object($result_state);
        $user_state = $row_state ? $row_state->state_name : '';
        mysqli_stmt_close($stmt_state);
    }
    
    // جلب بيانات المشتري (صاحب طلب الشراء)
    $sql_br = "SELECT br.*, u.* 
               FROM buy_requirement br
               INNER JOIN user u ON br.br_u_id = u.usr_id
               WHERE br.br_id = ? 
               LIMIT 1";
    
    $stmt_br = mysqli_prepare($con, $sql_br);
    mysqli_stmt_bind_param($stmt_br, 'i', $br_id);
    mysqli_stmt_execute($stmt_br);
    $result_br = mysqli_stmt_get_result($stmt_br);
    $buyer_details = mysqli_fetch_object($result_br);
    mysqli_stmt_close($stmt_br);
    
    // تأكيد المعاملة
    mysqli_commit($con);
    
    // إرسال إشعار البريد الإلكتروني للمشتري (صاحب الطلب)
    if ($buyer_details && !empty($buyer_details->email)) {
        ob_start();
        include __DIR__ . '/../email/buylead_notification.php';
        $message_content = ob_get_clean();
        
        if (!empty($message_content)) {
            $from_mail = get_adminemail();
            $to_email = stripslashes($buyer_details->email);
            $from_name = get_page_settings(4);
            $subject = "Buy lead purchase notification";
            
            $headers = "MIME-Version: 1.0\n";
            $headers .= "Content-type: text/html; charset=UTF-8\n";
            $headers .= "From: " . $from_name . " <" . $from_mail . ">\n";
            
            mail($to_email, $subject, $message_content, $headers);
        }
    }
    
    echo "1|Buy lead purchased successfully";
    
} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Purchase Buy Lead Error: " . $e->getMessage() . " | User: $usr_id, Lead: $br_id");
    echo "0|" . $e->getMessage();
}
?>