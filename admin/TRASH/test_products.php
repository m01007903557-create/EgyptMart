<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
require_once dirname(__DIR__) . "/common.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    echo "❌ غير مسجل دخول";
    exit;
}

global $con;

echo "<h2>اختبار جلب المنتجات</h2>";

// استعلام بسيط
$sql = "SELECT COUNT(*) as total FROM products";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);
echo "عدد المنتجات في قاعدة البيانات: " . $row['total'] . "<br><br>";

// جلب أول 5 منتجات
$sql2 = "SELECT pd_id, pd_title FROM products LIMIT 5";
$result2 = mysqli_query($con, $sql2);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Title</th></tr>";
while ($row2 = mysqli_fetch_assoc($result2)) {
    echo "<tr><td>" . $row2['pd_id'] . "</td><td>" . htmlspecialchars($row2['pd_title']) . "</td></tr>";
}
echo "</table>";

// اختبار الاتصال
echo "<br>✅ الاتصال بقاعدة البيانات يعمل";
?>