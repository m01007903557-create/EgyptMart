<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>اختبار بسيط</h1>";

$common_path = dirname(__DIR__) . "/common.php";
echo "المسار: " . $common_path . "<br>";

if (file_exists($common_path)) {
    echo "✅ common.php موجود<br>";
    require_once $common_path;
    echo "✅ تم التضمين<br>";
    
    if (function_exists('GettingSite_Setting')) {
        echo "✅ الدالة موجودة<br>";
        $result = GettingSite_Setting('logo');
        echo "النتيجة: " . ($result ?: 'فارغة') . "<br>";
    } else {
        echo "❌ الدالة غير موجودة<br>";
    }
} else {
    echo "❌ common.php غير موجود<br>";
}
?>