<?php
session_start();
require_once "../lib/connect.php";

if (empty($_SESSION['ad_id_indm'])) {
    echo "غير مصرح";
    exit;
}

$sql = "SELECT * FROM offers ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);

echo "<h2>عروض الأسعار</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>ID</th><th>RFQ ID</th><th>السعر</th><th>الحالة</th><th>التاريخ</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['rfq_id'] . "</td>";
    echo "<td>" . $row['price'] . " " . $row['currency'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?>