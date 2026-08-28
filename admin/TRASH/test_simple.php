<?php
session_start();

// تحقق بسيط
echo "<h1>اختبار بسيط</h1>";
echo "Session ad_id_indm: " . ($_SESSION['ad_id_indm'] ?? 'غير موجود') . "<br>";

// اتصال بقاعدة البيانات
require_once dirname(__DIR__) . "/common.php";

global $con;
if (!$con) {
    die("اتصال قاعدة البيانات فشل");
}
echo "اتصال قاعدة البيانات ناجح<br>";

// استعلام بسيط
$sql = "SELECT COUNT(*) as total FROM business_profile";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);
echo "عدد الشركات: " . $row['total'] . "<br>";

// عرض أول 5 شركات
$sql2 = "SELECT bnsprof_id, bnsprof_compname FROM business_profile LIMIT 5";
$result2 = mysqli_query($con, $sql2);

echo "<h2>أول 5 شركات:</h2>";
echo "<ul>";
while ($row2 = mysqli_fetch_assoc($result2)) {
    echo "<li>ID: " . $row2['bnsprof_id'] . " - " . $row2['bnsprof_compname'] . "</li>";
}
echo "</ul>";

echo "<h2>جدول بسيط:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Company Name</th></tr>";
$result3 = mysqli_query($con, "SELECT bnsprof_id, bnsprof_compname FROM business_profile LIMIT 10");
while ($row3 = mysqli_fetch_assoc($result3)) {
    echo "<tr><td>" . $row3['bnsprof_id'] . "</td><td>" . $row3['bnsprof_compname'] . "</td></tr>";
}
echo "</table>";
?>