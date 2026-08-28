<?php
/**
 * File: login-process.php
 * Version: PHP 8.3
 * Description: معالجة تسجيل الدخول والتحقق من بيانات المستخدم
 */

include "common.php";
error_reporting(1);

// تحديد طريقة استلام البيانات (GET أو POST)
if (isset($_GET['changepass'])) {
    $email = trim($_GET['email'] ?? '');
    $password = md5(trim($_GET['pass'] ?? ''));
} else {
    $email = trim($_POST['email'] ?? '');
    $password = md5(trim($_POST['pass'] ?? ''));
}

if (isset($_REQUEST['login'])) {
    
    $_SESSION['last_page'] = $_SESSION['last_page'] ?? "my-dashboard.php";
    
    // التحقق من صحة الإدخالات
    if ($email == "") {
        $msg = "من فضلك أدخل عنوان بريدى أو رقم محمول صحيح";
        $ERR = 1;
        $_SESSION['errr_msg'] = $msg;
        header("location: sign-in.php");
        exit();
    } else if ($password == "") {
        $msg = "من فضلك أدخل كلمة مرور";
        $ERR = 1;
        $_SESSION['errr_msg'] = $msg;
        header("location: sign-in.php");
        exit();
    } else {
        // معالجة رقم الهاتف (إزالة الصفر الأول إن وجد)
        if (substr($email, 0, 1) == '0') {
            $phone = ltrim($email, '0');
        } else {
            $phone = $email;
        }

        // البحث عن المستخدم في قاعدة البيانات
        $sql = "SELECT * FROM `user` 
                WHERE (`email` = '" . mysqli_real_escape_string($con, $email) . "' 
                OR `mobile1` = '" . mysqli_real_escape_string($con, $phone) . "') 
                AND `pass` = '" . mysqli_real_escape_string($con, $password) . "'";
        
        $qry = mysqli_query($con, $sql) or die("Database error: " . mysqli_error($con));
        $arr = mysqli_fetch_assoc($qry);
        
        if (mysqli_num_rows($qry) != 1) {
            $msg = "العنوان البريدى أو كلمة المرور غير صحيحة";
            $ERR = 1;
        }
        
        if (isset($ERR) && $ERR == 1) {
            $_SESSION['errr_msg'] = $msg;
            header("location: sign-in.php");
            exit();
        }
        
        // تحديث حالة المستخدم إذا تم تسجيل الدخول بنجاح
        if (!empty($arr['usr_id'])) {
            $update_sql = "UPDATE user SET password_email = '0', password_link = '0' 
                           WHERE usr_id = '" . (int)$arr['usr_id'] . "'";
            mysqli_query($con, $update_sql) or die("Database error: " . mysqli_error($con));
        }

        // حفظ معرف المستخدم في الجلسة
        $_SESSION['uid_indm'] = (int)$arr['usr_id'];
        
        // حفظ معرف المستخدم في كوكي (لمدة 300 يوم)
        setcookie('cook_usr_id', (string)$arr['usr_id'], time() + (86400 * 300), "/");
        
        // التحقق من البريد الإلكتروني إذا كان مطلوباً
        if (isset($_SESSION['email_verify_for']) && $_SESSION['email_verify_for'] == $_SESSION['uid_indm']) {
            $sql = "UPDATE user SET `usr_emailVerify` = '1' 
                    WHERE `usr_id` = '" . (int)$_SESSION['email_verify_for'] . "'";
            mysqli_query($con, $sql);
        }
        
        // إزالة رسائل الخطأ
        unset($_SESSION['errr_msg']);
        
        // التوجيه إلى الصفحة المناسبة
        if ($_SESSION['last_page'] == 'compare.php') {
            header("location: compare.php");
            unset($_SESSION['last_page']);
            // إزالة كوكي productids
            setcookie("productids", '', time() - 1000);
            setcookie("productids", '', time() - 1000, '/');
            exit();
        } else if (isset($_GET['redirect']) && $_GET['redirect'] != '') {
            setcookie("productids", '', time() - 1000);
            setcookie("productids", '', time() - 1000, '/');
            header("location: " . $_GET['redirect']);
            exit();
        } else {
            // التوجيه إلى لوحة التحكم
            header("location: my-dashboard.php");
            exit();
        }
    }
} else {
    // إذا لم يتم استدعاء الصفحة بشكل صحيح
    header("location: sign-in.php");
    exit();
}
?>