<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'p:localhost');
define('DB_USER', 'root'); // اسم المستخدم الصحيح
define('DB_PASS', '....');   // ضع كلمة المرور هنا
define('DB_NAME', 'u397968200_egmart');   // اسم قاعدة البيانات



// إنشاء اتصال MySQLi
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// التحقق من الاتصال
if (mysqli_connect_errno()) {
    // سجل الخطأ في ملف log
    error_log("Database connection failed: " . mysqli_connect_error());
    
    // رسالة للمستخدم (يمكن تخصيصها)
    die("عذراً، حدث خطأ في الاتصال بقاعدة البيانات. الرجاء المحاولة لاحقاً.");
}

// تعيين الترميز
if (!mysqli_set_charset($con, 'utf8mb4')) {
    error_log("Error setting charset: " . mysqli_error($con));
}

// تعيين وضع الإبلاغ عن الأخطاء (للتطوير)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>





