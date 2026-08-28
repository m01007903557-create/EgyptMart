<?php
// عرض طلبات الواتساب فقط
$sql = "SELECT * FROM br_buyer_requirements 
        WHERE communication_type = 'whatsapp' 
        ORDER BY br_posting_date DESC";
$result = mysqli_query($conn, $sql);

echo "<h2>طلبات WhatsApp RFQ</h2>";
echo "<table border='1'>";
echo "<tr><th>RFQ ID</th><th>المنتج</th><th>المستخدم</th><th>التفاصيل</th><th>تاريخ الإرسال</th><th>حالة واتساب</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['br_pc_id']}</td>";
    echo "<td>{$row['br_pd_name']}</td>";
    echo "<td>{$row['br_u_id']}</td>";
    echo "<td>{$row['br_requirement']}</td>";
    echo "<td>{$row['br_posting_date']}</td>";
    echo "<td>" . ($row['whatsapp_sent'] ? 'تم الإرسال' : 'قيد الانتظار') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>