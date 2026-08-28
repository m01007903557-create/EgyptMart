<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>🔍 تشخيص شامل لملف common.php</h1>";

// 1. التحقق من وجود الملف
$common_path = dirname(__DIR__) . "/common.php";
echo "<strong>1. التحقق من المسار:</strong> " . $common_path . "<br>";

if (!file_exists($common_path)) {
    die("❌ خطأ: ملف common.php غير موجود!");
}
echo "✅ ملف common.php موجود<br><br>";

// 2. قراءة أول 50 سطر من الملف للتأكد من عدم وجود أخطاء في البداية
echo "<strong>2. أول 10 أسطر من common.php:</strong><br>";
echo "<pre>";
$lines = file($common_path);
for ($i = 0; $i < min(10, count($lines)); $i++) {
    echo htmlspecialchars($lines[$i]);
}
echo "</pre><br>";

// 3. محاولة تضمين الملف
echo "<strong>3. محاولة تضمين common.php:</strong><br>";
try {
    require_once $common_path;
    echo "✅ تم تضمين common.php بنجاح<br><br>";
} catch (Throwable $e) {
    die("❌ خطأ أثناء التضمين: " . $e->getMessage() . "<br>في ملف: " . $e->getFile() . ":" . $e->getLine());
}

// 4. التحقق من وجود الدوال الأساسية
echo "<strong>4. التحقق من الدوال الأساسية:</strong><br>";
$functions = [
    'executePreparedQuery',
    'fetchOne',
    'fetchAll',
    'get_page_settings',
    'get_state_name',
    'get_country_name',
    'get_city_name',
    'check_user_login',
    'checkUserLogin',
    'getAdminUserId'
];

foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "✅ $func موجودة<br>";
    } else {
        echo "❌ $func غير موجودة<br>";
    }
}
echo "<br>";

// 5. اختبار الاتصال بقاعدة البيانات
echo "<strong>5. اختبار الاتصال بقاعدة البيانات:</strong><br>";
global $con;
if (isset($con) && $con instanceof mysqli) {
    echo "✅ اتصال قاعدة البيانات موجود<br>";
    
    // اختبار استعلام بسيط
    $test = mysqli_query($con, "SELECT 1");
    if ($test) {
        echo "✅ يمكن تنفيذ الاستعلامات<br>";
    } else {
        echo "❌ فشل تنفيذ الاستعلام: " . mysqli_error($con) . "<br>";
    }
} else {
    echo "❌ اتصال قاعدة البيانات غير موجود<br>";
}
echo "<br>";

// 6. اختبار دالة get_page_settings
echo "<strong>6. اختبار get_page_settings:</strong><br>";
try {
    $result = get_page_settings(1);
    echo "النتيجة: " . ($result ?: 'قيمة فارغة') . "<br>";
    echo "✅ الدالة تعمل<br>";
} catch (Throwable $e) {
    echo "❌ خطأ: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 7. اختبار دالة get_state_name
echo "<strong>7. اختبار get_state_name:</strong><br>";
try {
    $result = get_state_name(1);
    echo "النتيجة: " . ($result ?: 'قيمة فارغة') . "<br>";
    echo "✅ الدالة تعمل<br>";
} catch (Throwable $e) {
    echo "❌ خطأ: " . $e->getMessage() . "<br>";
}
echo "<br>";

// 8. اختبار دالة get_country_name
echo "<strong>8. اختبار get_country_name:</strong><br>";
try {
    $result = get_country_name(1);
    echo "النتيجة: " . ($result ?: 'قيمة فارغة') . "<br>";
    echo "✅ الدالة تعمل<br>";
} catch (Throwable $e) {
    echo "❌ خطأ: " . $e->getMessage() . "<br>";
}
echo "<br>";

echo "<h2>✅ اكتمل التشخيص</h2>";
?>