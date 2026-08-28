<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>🔍 اختبار اتصال قاعدة البيانات مباشرة</h1>";

// بيانات الاتصال التي أكدتها
$db_host = 'localhost';
$db_user = 'u397968200_arabuser';
$db_pass = 'ANAehab@64';
$db_name = 'u397968200_egmart';

echo "محاولة الاتصال بـ: $db_user@$db_host / $db_name <br>";

// اتصال مباشر
$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$con) {
    die("❌ فشل الاتصال: " . mysqli_connect_error());
}
echo "✅ الاتصال ناجح!<br>";

// اختبار استعلام بسيط
$result = mysqli_query($con, "SELECT COUNT(*) as count FROM admin_user");
$row = mysqli_fetch_assoc($result);
echo "عدد المستخدمين في admin_user: " . $row['count'] . "<br>";

// اختبار جلب المستخدم ARABYOS
$sql = "SELECT id, username, password, status FROM admin_user WHERE username = 'ARABYOS'";
$result = mysqli_query($con, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "❌ المستخدم ARABYOS غير موجود!<br>";
} else {
    $user = mysqli_fetch_assoc($result);
    echo "✅ المستخدم ARABYOS موجود:<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Password hash: " . $user['password'] . "<br>";
    
    // اختبار كلمة المرور admin123
    $test_pass = 'admin123';
    $md5_test = md5($test_pass);
    echo "MD5('admin123'): " . $md5_test . "<br>";
    
    if ($user['password'] === $md5_test) {
        echo "✅ كلمة المرور admin123 صحيحة!<br>";
    } else {
        echo "❌ كلمة المرور admin123 غير صحيحة!<br>";
    }
}

mysqli_close($con);
?>