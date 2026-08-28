<?php
/**
 * File: process-payment.php
 * Description: معالجة طلب شراء الكريديت وحفظه في قاعدة البيانات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

// بدء الجلسة
session_start();

// بيانات الاتصال بقاعدة البيانات
$server = "localhost";
$user = "u397968200_arabuser";
$db_name = "u397968200_egmart";
$pass = "ANAehab@64";

// إنشاء الاتصال بقاعدة البيانات
$conn = mysqli_connect($server, $user, $pass, $db_name);
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Connection failed. Please try again later.");
}
mysqli_set_charset($conn, 'utf8mb4');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || !is_numeric($_SESSION['uid_indm']) || (int)$_SESSION['uid_indm'] <= 0) {
    header("Location: sign-in.php?redirect=payment-option.php");
    exit();
}

$user_id = (int)$_SESSION['uid_indm'];
$user_name = isset($_SESSION['member_name']) ? htmlspecialchars($_SESSION['member_name'], ENT_QUOTES, 'UTF-8') : 'مستخدم';

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_payment'])) {
    header("Location: payment-option.php");
    exit();
}

try {
    // التحقق من صحة البيانات
    if (!isset($_POST['credit_amount']) || !is_numeric($_POST['credit_amount']) || (int)$_POST['credit_amount'] <= 0) {
        throw new Exception("Invalid credit amount");
    }

    $credit_amount = (int)$_POST['credit_amount'];
    $payment_type = isset($_POST['payment_type']) ? trim($_POST['payment_type']) : 'manual';
    $transaction_id = isset($_POST['transaction_id']) ? trim($_POST['transaction_id']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // التحقق من صحة طريقة الدفع
    $allowed_payment_types = ['manual', 'card', 'bank_transfer', 'vodafone_cash'];
    if (!in_array($payment_type, $allowed_payment_types, true)) {
        $payment_type = 'manual';
    }

    // معالجة رفع الصورة
    $receipt_path = '';
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        // التحقق من نوع الملف
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $_FILES['receipt']['tmp_name']);
        finfo_close($file_info);

        if (!in_array($mime_type, $allowed_types, true)) {
            throw new Exception("Invalid file type. Only JPG, PNG, GIF and PDF are allowed.");
        }

        // التحقق من حجم الملف (5MB كحد أقصى)
        if ($_FILES['receipt']['size'] > 5 * 1024 * 1024) {
            throw new Exception("File size too large. Maximum size is 5MB.");
        }

        // إنشاء المجلد إذا لم يكن موجوداً
        $target_dir = __DIR__ . "/uploads/receipts/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        // التحقق من صلاحيات الكتابة
        if (!is_writable($target_dir)) {
            throw new Exception("Upload directory is not writable");
        }

        // إنشاء اسم ملف آمن
        $file_extension = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
        $safe_filename = sprintf(
            '%d_%d_%s.%s',
            $user_id,
            time(),
            bin2hex(random_bytes(8)),
            $file_extension
        );
        
        $receipt_path = "uploads/receipts/" . $safe_filename;
        $full_path = $target_dir . $safe_filename;

        if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $full_path)) {
            throw new Exception("Failed to upload file");
        }

        // تغيير صلاحيات الملف
        chmod($full_path, 0644);
    }

    // بدء المعاملة
    mysqli_begin_transaction($conn);

    // حفظ طلب الشراء في قاعدة البيانات
    $sql = "INSERT INTO credit_purchases 
            (user_id, amount, payment_type, transaction_id, receipt_path, notes, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "iissss", 
        $user_id, 
        $credit_amount, 
        $payment_type, 
        $transaction_id, 
        $receipt_path, 
        $notes
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to insert record: " . mysqli_stmt_error($stmt));
    }

    $purchase_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // تأكيد المعاملة
    mysqli_commit($conn);

    // إرسال إيميل إشعار (اختياري)
    try {
        $to = "jornet2000@gmail.com";
        $subject = "طلب شحن كريديت جديد - مستخدم: " . $user_name;
        
        $message = "تفاصيل الطلب:\n";
        $message .= "رقم الطلب: " . $purchase_id . "\n";
        $message .= "المستخدم: " . $user_name . " (ID: " . $user_id . ")\n";
        $message .= "المبلغ: " . $credit_amount . " كريديت\n";
        $message .= "طريقة الدفع: " . $payment_type . "\n";
        $message .= "رقم العملية: " . $transaction_id . "\n";
        $message .= "ملاحظات: " . $notes . "\n";
        $message .= "صورة الإيصال: " . ($receipt_path ?: "لا يوجد") . "\n";
        $message .= "تاريخ الطلب: " . date('Y-m-d H:i:s');

        $headers = "From: info@egyptmart.shop\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        // تسجيل الخطأ ولكن لا نوقف العملية
        error_log("Email sending failed: " . $e->getMessage());
    }

    // تخزين رسالة نجاح
    $_SESSION['payment_success'] = "تم استلام طلبك بنجاح. سيتم إضافة الكريديت إلى حسابك خلال 24 ساعة.";
    
    // التوجيه لصفحة النجاح
    header("Location: payment-confirm.php");
    exit();

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    if (isset($conn)) {
        mysqli_rollback($conn);
    }

    // حذف الملف المرفوع إذا فشلت العملية
    if (!empty($receipt_path) && file_exists(__DIR__ . "/" . $receipt_path)) {
        unlink(__DIR__ . "/" . $receipt_path);
    }

    // تسجيل الخطأ
    error_log("Payment processing error: " . $e->getMessage() . " | User ID: " . $user_id);

    // تخزين رسالة الخطأ
    $_SESSION['payment_error'] = "حدث خطأ أثناء حفظ الطلب. الرجاء المحاولة مرة أخرى.";
    
    // التوجيه لصفحة الدفع
    header("Location: payment-option.php");
    exit();

} finally {
    // إغلاق الاتصال بقاعدة البيانات
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>