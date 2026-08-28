<?php
require_once dirname(__DIR__) . "/common.php";

echo "<h1>اختبار وجود الدوال</h1>";

if (function_exists('GettingSite_Setting')) {
    echo "✅ الدالة GettingSite_Setting موجودة<br>";
    $result = GettingSite_Setting('logo');
    echo "نتيجة الاختبار: " . ($result ?: 'قيمة فارغة') . "<br>";
} else {
    echo "❌ الدالة GettingSite_Setting غير موجودة<br>";
}

echo "<br>قائمة الدوال الموجودة:<br>";
$all_functions = get_defined_functions()['user'];
$admin_functions = array_filter($all_functions, function($f) {
    return strpos($f, 'get') === 0 || strpos($f, 'Getting') === 0;
});
print_r($admin_functions);
?>