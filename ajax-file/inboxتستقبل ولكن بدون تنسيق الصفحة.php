<?php
// ملف inbox.php - نسخة نظيفة تعمل مع WhatsApp RFQ
require_once dirname(__DIR__) . '/lib/connect.php';
session_start();

$current_user = $_SESSION['uid_indm'] ?? 0;
if (!$current_user) {
    echo "error: not logged in";
    exit;
}

$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$per_page = 10;

// الاستعلام لجلب جميع الرسائل (سواء email أو whatsapp_rfq)
$sql_inbox = "SELECT m.*, 
                     u.name_prefix, u.fname, u.lname, u.email,
                     bp.bnsprof_compname,
                     au.username,
                     CASE WHEN m.msg_entity = 'whatsapp_rfq' THEN 'whatsapp_rfq' ELSE 'email' END as source
              FROM message m
              LEFT JOIN user u ON m.msg_from = u.usr_id
              LEFT JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id
              LEFT JOIN admin_user au ON m.msg_from = au.id
              WHERE m.msg_to = ? 
              AND m.msg_to_status = '1' 
              ORDER BY m.msg_date DESC 
              LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_inbox);
mysqli_stmt_bind_param($stmt, 'iii', $current_user, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_object($result)) {
    $messages[] = $row;
}

// العدد الإجمالي للرسائل (لصفحات العرض)
$sql_count = "SELECT COUNT(*) as total FROM message WHERE msg_to = ? AND msg_to_status = '1'";
$stmt_count = mysqli_prepare($con, $sql_count);
mysqli_stmt_bind_param($stmt_count, 'i', $current_user);
mysqli_stmt_execute($stmt_count);
$count_result = mysqli_stmt_get_result($stmt_count);
$total_row = mysqli_fetch_assoc($count_result);
$total_messages = $total_row['total'];

// عرض الرسائل بنفس تنسيق النظام الأصلي
// (هذا الجزء يجب أن يتطابق مع طريقة عرض الملف الأصلي)
?>
<div class="inbox-container">
    <div class="inbox-header">
        <span class="sender">المرسل</span>
        <span class="type">النوع</span>
        <span class="subject">الموضوع</span>
        <span class="date">التاريخ</span>
    </div>
    <?php foreach ($messages as $msg): ?>
    <div class="inbox-row" onclick="openMessage(<?php echo $msg->msg_id; ?>)">
        <span class="sender">
            <?php echo htmlspecialchars($msg->bnsprof_compname ?? $msg->fname ?? $msg->username ?? 'النظام'); ?>
        </span>
        <span class="type">
            <?php if ($msg->source == 'whatsapp_rfq'): ?>
                <span class="whatsapp-badge"><i class="fa fa-whatsapp"></i> WhatsApp</span>
            <?php else: ?>
                <span class="email-badge"><i class="fa fa-envelope"></i> Email</span>
            <?php endif; ?>
        </span>
        <span class="subject"><?php echo htmlspecialchars($msg->msg_subject); ?></span>
        <span class="date"><?php echo date('Y-m-d H:i', strtotime($msg->msg_date)); ?></span>
    </div>
    <?php endforeach; ?>
</div>