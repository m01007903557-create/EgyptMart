<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/common.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['uid_indm'] ?? 0;
if (!$user_id) {
    die('الرجاء تسجيل الدخول أولاً');
}

// جلب جميع أكواد الشات للمستخدم
$sql = "SELECT chat_id, chat_code, buyer_id, supplier_id 
        FROM chat_rooms 
        WHERE buyer_id = '$user_id' OR supplier_id = '$user_id'";

$res = mysqli_query($con, $sql);

echo "<h2>أكواد الشات المتاحة لك:</h2>";
echo "<ul>";
while ($row = mysqli_fetch_assoc($res)) {
    echo "<li>كود الشات: <strong>" . htmlspecialchars($row['chat_code']) . "</strong> - رابط: <a href='/chat-wrapper.php?chat_code=" . urlencode($row['chat_code']) . "'>فتح الشات</a></li>";
}
echo "</ul>";
?>