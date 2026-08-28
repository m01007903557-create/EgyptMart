<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>🔍 اختبار validate-admin.php</h1>";

// 1. تضمين common.php
require_once dirname(__DIR__) . "/common.php";
echo "✅ common.php تم تضمينه<br>";

// 2. محاكاة بيانات POST
$_POST['username'] = 'ARABYOS';
$_POST['password'] = 'كلمة_المرور_الصحيحة'; // ضع كلمة المرور الصحيحة هنا

echo "بيانات الاختبار:<br>";
echo "Username: " . $_POST['username'] . "<br>";
echo "Password: [مخفية]<br>";

// 3. تنفيذ خطوات التحقق
global $con;

// البحث عن المستخدم
$sql = "SELECT id, username, password, status FROM admin_user WHERE username = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "s", $_POST['username']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo "✅ المستخدم موجود:<br>";
    echo "ID: " . $row['id'] . "<br>";
    echo "Username: " . $row['username'] . "<br>";
    echo "Status: " . $row['status'] . "<br>";
    
    // التحقق من كلمة المرور
    $encrypted = md5($_POST['password']);
    echo "كلمة المرور المدخلة (مشفرة): " . $encrypted . "<br>";
    echo "كلمة المرور في قاعدة البيانات: " . $row['password'] . "<br>";
    
    if ($row['password'] === $encrypted) {
        echo "✅ كلمة المرور صحيحة<br>";
        
        // محاكاة تعيين الجلسة
        session_start();
        $_SESSION['ad_id_indm'] = $row['id'];
        $_SESSION['ad_username_indm'] = $row['username'];
        $_SESSION['admin_logged_in'] = true;
        echo "✅ تم تعيين الجلسة<br>";
        echo "الانتقال إلى: welcome.php<br>";
        
    } else {
        echo "❌ كلمة المرور غير صحيحة<br>";
    }
} else {
    echo "❌ المستخدم غير موجود<br>";
}
?>