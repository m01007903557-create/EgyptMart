<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
require_once dirname(__DIR__) . "/common.php";

global $con;

echo "<h2>اختبار بسيط للدخول</h2>";

// اختبار الاتصال بقاعدة البيانات
if (!$con) {
    die("❌ فشل الاتصال بقاعدة البيانات");
} else {
    echo "✅ الاتصال بقاعدة البيانات ناجح<br>";
}

// محاولة جلب المستخدم ARABYOS
$username = 'ARABYOS';
$sql = "SELECT id, username, password, status FROM admin_user WHERE username = '$username'";
$result = mysqli_query($con, $sql);

if (!$result) {
    die("❌ خطأ في الاستعلام: " . mysqli_error($con));
}

if (mysqli_num_rows($result) == 0) {
    die("❌ المستخدم ARABYOS غير موجود في قاعدة البيانات");
}

$user = mysqli_fetch_assoc($result);
echo "✅ تم العثور على المستخدم:<br>";
echo "- ID: " . $user['id'] . "<br>";
echo "- Username: " . $user['username'] . "<br>";
echo "- Status: " . $user['status'] . "<br>";
echo "- Password hash in DB: " . $user['password'] . "<br>";

// اختبار كلمة المرور admin123
$test_password = 'admin123';
$md5_test = md5($test_password);
echo "- MD5 of 'admin123': " . $md5_test . "<br>";

if ($user['password'] === $md5_test) {
    echo "✅ كلمة المرور 'admin123' صحيحة!<br>";
} else {
    echo "❌ كلمة المرور 'admin123' غير صحيحة!<br>";
}

// اختبار إنشاء جلسة وهمية
$_SESSION['ad_id_indm'] = $user['id'];
$_SESSION['ad_username_indm'] = $user['username'];
$_SESSION['admin_logged_in'] = true;

echo "✅ تم تعيين الجلسة وهمياً<br>";
echo "<a href='welcome.php'>الذهاب إلى welcome.php</a>";
?>