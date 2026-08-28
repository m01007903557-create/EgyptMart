<?php
require_once dirname(__DIR__) . '/lib/connect.php';
session_start();

$current_user = $_SESSION['uid_indm'] ?? 0;
if (!$current_user) {
    echo "error: not logged in";
    exit;
}

$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
$per_page = 10;

// استعلام يجلب جميع الرسائل مع تحديد المصدر
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

// العدد الإجمالي
$sql_count = "SELECT COUNT(*) as total FROM message WHERE msg_to = ? AND msg_to_status = '1'";
$stmt_count = mysqli_prepare($con, $sql_count);
mysqli_stmt_bind_param($stmt_count, 'i', $current_user);
mysqli_stmt_execute($stmt_count);
$count_result = mysqli_stmt_get_result($stmt_count);
$total_row = mysqli_fetch_assoc($count_result);
$total_messages = $total_row['total'];
?>

<div class="inbox-table">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="30"><input type="checkbox" id="checkAll"></th>
                <th>Sender</th>
                <th width="80">Type</th>
                <th>Subject</th>
                <th width="120">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_object($result)): 
                $sender_name = '';
                if (!empty($row->bnsprof_compname)) {
                    $sender_name = htmlspecialchars($row->bnsprof_compname);
                } elseif (!empty($row->username)) {
                    $sender_name = htmlspecialchars($row->username);
                } else {
                    $sender_name = htmlspecialchars(trim(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? '')));
                }
                
                $type_label = ($row->source == 'whatsapp_rfq') ? '<span class="label label-success"><i class="fa fa-whatsapp"></i> WhatsApp</span>' : '<span class="label label-info"><i class="fa fa-envelope"></i> Email</span>';
                
                $bg_color = ($row->msg_read == 0) ? '#F5ECFF' : '';
            ?>
            <tr class="inbox-row" style="cursor:pointer; <?php echo $bg_color; ?>" data-id="<?php echo $row->msg_id; ?>">
                <td class="text-center">
                    <input type="checkbox" class="msg_checkbox" value="<?php echo $row->msg_id; ?>">
                </td>
                <td class="sender"><?php echo $sender_name; ?></td>
                <td class="type"><?php echo $type_label; ?></td>
                <td class="subject"><?php echo htmlspecialchars($row->msg_subject); ?></td>
                <td class="date"><?php echo date('Y-m-d H:i', strtotime($row->msg_date)); ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($result) == 0): ?>
            <tr>
                <td colspan="5" class="text-center">لا توجد رسائل</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// تحديد الكل
$('#checkAll').click(function() {
    $('.msg_checkbox').prop('checked', this.checked);
});

// فتح الرسالة عند النقر على الصف
$('.inbox-row').click(function(e) {
    if (e.target.type !== 'checkbox') {
        var msgId = $(this).data('id');
        if (msgId) {
            openmail(msgId, 'inbox');
        }
    }
});
</script>