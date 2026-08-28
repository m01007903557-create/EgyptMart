<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>🔍 تشخيص validate-admin.php</h1>";

// 1. تضمين common.php
require_once dirname(__DIR__) . "/common.php";
echo "✅ تم تضمين common.php<br><br>";

// 2. محاكاة بيانات POST (للاختبار فقط)
$_POST['username'] = 'ARABYOS'; // ضع اسم المستخدم الصحيح هنا
$_POST['password'] = 'ANAehab@64'; 

echo "<strong>بيانات الاختبار:</strong><br>";
echo "Username: " . $_POST['username'] . "<br>";
echo "Password: [مخفية]<br><br>";

// 3. محاولة تنفيذ نفس خطوات validate-admin.php
try {
    global $con;
    
    // البحث عن المستخدم
    $sql = "SELECT id, username, password, status FROM admin_user WHERE username = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        throw new Exception("فشل تحضير الاستعلام: " . mysqli_error($con));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $_POST['username']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        echo "❌ المستخدم غير موجود في قاعدة البيانات<br>";
    } else {
        $user = mysqli_fetch_assoc($result);
        echo "✅ تم العثور على المستخدم:<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Status: " . $user['status'] . "<br>";
        
        // التحقق من كلمة المرور
        $encrypted = md5($_POST['password']);
        echo "كلمة المرور المدخلة (مشفرة): " . $encrypted . "<br>";
        echo "كلمة المرور في قاعدة البيانات: " . $user['password'] . "<br>";
        
        if ($user['password'] === $encrypted) {
            echo "✅ كلمة المرور صحيحة<br>";
        } else {
            echo "❌ كلمة المرور غير صحيحة<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "<br>";
}
?>