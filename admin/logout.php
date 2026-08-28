<?php
/**
 * ملف تسجيل الخروج (Logout)
 * 
 * @filename    logout.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن تسجيل خروج المستخدم وإنهاء الجلسة بشكل آمن
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * تسجيل خروج المستخدم وإنهاء الجلسة بشكل آمن
 */
function secureLogout() {
    
    // تسجيل وقت الخروج في ملف السجل (اختياري)
    if (isset($_SESSION['ad_username_indm'])) {
        error_log("User logged out: " . $_SESSION['ad_username_indm'] . " at " . date('Y-m-d H:i:s'));
    }
    
    // إزالة جميع متغيرات الجلسة الخاصة بالمستخدم
    $sessionVars = [
        'ad_username_indm',
        'ad_email_indm', 
        'ad_id_indm',
        'ad_role_indm',
        'ad_last_activity'
    ];
    
    foreach ($sessionVars as $var) {
        if (isset($_SESSION[$var])) {
            unset($_SESSION[$var]);
        }
    }
    
    // إعادة تعيين مصفوفة الجلسة بالكامل (اختياري)
    // $_SESSION = array();
    
    // إذا كنت تستخدم session cookies، قم بحذفها
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // تدمير الجلسة بالكامل
    session_destroy();
    
    // منع التخزين المؤقت للصفحات بعد تسجيل الخروج
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

// تنفيذ تسجيل الخروج الآمن
secureLogout();

// إعادة التوجيه إلى صفحة تسجيل الدخول
header("Location: index.php");
exit();
?>