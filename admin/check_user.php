<?php
// check_user.php - تشخيص معرف المستخدم

session_start();

echo "<h2>🔍 تشخيص المستخدم</h2>";

// عرض معرف المستخدم من الجلسة
$user_id = $_SESSION['uid_indm'] ?? $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
echo "<p><strong>معرف المستخدم من الجلسة:</strong> " . $user_id . "</p>";

// عرض جميع متغيرات الجلسة
echo "<h3>📋 جميع متغيرات الجلسة:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// الاتصال بقاعدة البيانات
require_once __DIR__ . '/../lib/connect.php';

if (isset($con) && $con) {
    echo "<p style='color:green;'>✅ الاتصال بقاعدة البيانات ناجح</p>";
    
    // البحث عن المستخدم في جدول user
    $sql = "SELECT * FROM user WHERE usr_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row) {
            echo "<p style='color:green;'>✅ تم العثور على المستخدم في جدول user</p>";
            echo "<pre>";
            print_r($row);
            echo "</pre>";
            
            // عرض أسماء الأعمدة في جدول user
            echo "<h3>📊 أسماء الأعمدة في جدول user:</h3>";
            echo "<pre>";
            $columns = array_keys($row);
            print_r($columns);
            echo "</pre>";
        } else {
            echo "<p style='color:red;'>❌ لم يتم العثور على المستخدم في جدول user</p>";
            
            // عرض جميع المستخدمين
            echo "<h3>📋 جميع المستخدمين في جدول user:</h3>";
            $all_sql = "SELECT usr_id, email, mobile1 FROM user LIMIT 10";
            $all_result = mysqli_query($con, $all_sql);
            if ($all_result) {
                echo "<pre>";
                while ($all_row = mysqli_fetch_assoc($all_result)) {
                    echo "usr_id: " . $all_row['usr_id'] . " - email: " . $all_row['email'] . " - mobile: " . $all_row['mobile1'] . "\n";
                }
                echo "</pre>";
            }
        }
        mysqli_stmt_close($stmt);
    }
} else {
    echo "<p style='color:red;'>❌ فشل الاتصال بقاعدة البيانات</p>";
}
?>