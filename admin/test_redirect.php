<?php
// test_redirect.php - تشخيص سبب التوجيه إلى index.php

session_start();

echo "<h2>🔍 تشخيص مسار التنفيذ</h2>";

// عرض جميع متغيرات الجلسة
echo "<h3>📋 متغيرات الجلسة:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// التحقق من وجود الملفات المضمنة
echo "<h3>📁 التحقق من الملفات المضمنة:</h3>";

$files = [
    'includes/admin-top.php',
    'includes/admin-left-con.php',
    'includes/footer.php',
    '../lib/connect.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "$file: " . (file_exists($path) ? '✅ موجود' : '❌ غير موجود') . "<br>";
}

// محاكاة تنفيذ videoslider-add.php
echo "<h3>🧪 محاكاة تنفيذ videoslider-add.php:</h3>";

// 1. التحقق من reseller_id
$reseller_id = $_SESSION['uid_indm'] ?? $_SESSION['reseller_id'] ?? 0;
echo "reseller_id المستخدم: " . $reseller_id . "<br>";

if ($reseller_id <= 0) {
    echo "<p style='color:red;'>❌ reseller_id غير صالح (0 أو أقل)</p>";
} else {
    echo "<p style='color:green;'>✅ reseller_id صالح: $reseller_id</p>";
}

// 2. التحقق من اتصال قاعدة البيانات
require_once __DIR__ . '/../lib/connect.php';
if (isset($con) && $con) {
    echo "<p style='color:green;'>✅ الاتصال بقاعدة البيانات ناجح</p>";
    
    // 3. التحقق من وجود المستخدم في جدول reseller
    $sql = "SELECT * FROM reseller WHERE reseller_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $reseller_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row) {
            echo "<p style='color:green;'>✅ المستخدم موجود في جدول reseller</p>";
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        } else {
            echo "<p style='color:red;'>❌ المستخدم غير موجود في جدول reseller</p>";
        }
        mysqli_stmt_close($stmt);
    }
}

echo "<hr>";
echo "<a href='videoslider-add.php'>📹 محاولة فتح videoslider-add.php</a>";
?>