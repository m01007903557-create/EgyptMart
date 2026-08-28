<?php
/**
 * File: ajax/enq-details.php
 * Description: عرض تفاصيل رسالة مع إمكانية الرد عليها وعرض عروض الأسعار
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../lib/connect.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    die("Unauthorized");
}

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من وجود معرف الرسالة والنوع
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !isset($_POST['type'])) {
    http_response_code(400);
    die("Invalid request parameters");
}

$msg_id = (int)$_POST['id'];
$type = trim($_POST['type']);

global $con;

// جلب بيانات الرسالة
$sql = "SELECT * FROM message WHERE msg_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $msg_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Message not found");
}

// ============================================
// تحديد دور المستخدم في هذا الطلب
// ============================================
$is_buyer = ($current_user == $row->msg_from);   // مرسل الطلب = مشتري
$is_supplier = ($current_user == $row->msg_to);  // مستقبل الطلب = مورد

// تحديث حالة القراءة
$sql_upd = "UPDATE message SET msg_read = '1' WHERE msg_id = ?";
$stmt_upd = mysqli_prepare($con, $sql_upd);
mysqli_stmt_bind_param($stmt_upd, 'i', $msg_id);
mysqli_stmt_execute($stmt_upd);
mysqli_stmt_close($stmt_upd);

// جلب بيانات المستلم
$sql_to = "SELECT fname, lname, email FROM user WHERE usr_id = ? LIMIT 1";
$stmt_to = mysqli_prepare($con, $sql_to);
mysqli_stmt_bind_param($stmt_to, 'i', $row->msg_to);
mysqli_stmt_execute($stmt_to);
$result_to = mysqli_stmt_get_result($stmt_to);
$row_to = mysqli_fetch_object($result_to);
mysqli_stmt_close($stmt_to);

// جلب بيانات المرسل
$sql_from = "SELECT fname, lname, email FROM user WHERE usr_id = ? LIMIT 1";
$stmt_from = mysqli_prepare($con, $sql_from);
mysqli_stmt_bind_param($stmt_from, 'i', $row->msg_from);
mysqli_stmt_execute($stmt_from);
$result_from = mysqli_stmt_get_result($stmt_from);
$row_from = mysqli_fetch_object($result_from);
mysqli_stmt_close($stmt_from);

$to_name = htmlspecialchars(trim(($row_to->fname ?? '') . ' ' . ($row_to->lname ?? '')), ENT_QUOTES, 'UTF-8');
$to_email = htmlspecialchars($row_to->email ?? '', ENT_QUOTES, 'UTF-8');
$from_name = htmlspecialchars(trim(($row_from->fname ?? '') . ' ' . ($row_from->lname ?? '')), ENT_QUOTES, 'UTF-8');
$from_email = htmlspecialchars($row_from->email ?? '', ENT_QUOTES, 'UTF-8');
$msg_subject = !empty($row->msg_subject) ? htmlspecialchars($row->msg_subject, ENT_QUOTES, 'UTF-8') : 'No Subject';
$msg_message = $row->msg_message ?? '';
$msg_date = !empty($row->msg_date) ? date("d-M-Y H:i:s A", strtotime($row->msg_date)) . ' ' . date('T') : 'N/A';

// جلب عروض الأسعار
$rfq_id = $row->msg_entity_id ?? 0;
$offers = [];
if ($rfq_id > 0) {
    $offers_sql = "SELECT o.*, s.fname as supplier_fname, s.lname as supplier_lname, bp.bnsprof_compname as supplier_company 
                   FROM offers o 
                   LEFT JOIN user s ON o.supplier_id = s.usr_id 
                   LEFT JOIN business_profile bp ON s.usr_id = bp.bnsprof_uid 
                   WHERE o.rfq_id = $rfq_id 
                   ORDER BY o.created_at DESC";
    $offers_res = mysqli_query($con, $offers_sql);
    while ($offer_row = mysqli_fetch_assoc($offers_res)) {
        $offers[] = $offer_row;
    }
}

// جلب الشات
$chat_code = '';
$chat_exists = false;
$chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if ($chat_check && mysqli_num_rows($chat_check) > 0) {
    $chat_data = mysqli_fetch_assoc($chat_check);
    $chat_code = $chat_data['chat_code'];
    $chat_exists = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        .offers-section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .offer-card { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px; background: #fff; }
        .btn { padding: 8px 15px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-info { background: #17a2b8; color: #fff; }
        .label { padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .label-success { background: #28a745; color: #fff; }
        .label-danger { background: #dc3545; color: #fff; }
    </style>
</head>
<body>

<div style="margin-bottom: 15px;">
    <button class="btn btn-default" onclick="window.history.back()">« رجوع</button>
</div>

<div style="background:#f5f5f5; padding:15px; margin-bottom:20px;">
    <p><strong>إلى:</strong> <?php echo $to_name; ?> &lt;<?php echo $to_email; ?>&gt;</p>
    <p><strong>من:</strong> <?php echo $from_name; ?> &lt;<?php echo $from_email; ?>&gt;</p>
    <p><strong>التاريخ:</strong> <?php echo $msg_date; ?></p>
    <p><strong>الموضوع:</strong> <?php echo $msg_subject; ?></p>
</div>

<div style="background:#f9f9f9; padding:15px; margin-bottom:20px;">
    <p><strong>الرسالة:</strong></p>
    <p><?php echo nl2br(htmlspecialchars($msg_message)); ?></p>
</div>

<!-- ============================================= -->
<!-- عروض الأسعار -->
<!-- ============================================= -->
<?php if (!empty($offers)): ?>
<div class="offers-section">
    <h3>عروض الأسعار</h3>
    <?php foreach ($offers as $offer): 
        $offer_id = $offer['id'];
        $update_count = (int)($offer['update_count'] ?? 0);
        $status = $offer['status'];
        $supplier_name = $offer['supplier_company'] ?? ($offer['supplier_fname'] . ' ' . $offer['supplier_lname']);
    ?>
    <div class="offer-card">
        <p><strong>المورد:</strong> <?php echo htmlspecialchars($supplier_name); ?></p>
        <p><strong>السعر:</strong> <?php echo $offer['price'] . ' ' . $offer['currency']; ?></p>
        <p><strong>مدة التوصيل:</strong> <?php echo $offer['delivery_days']; ?> يوم</p>
        <p><strong>الملاحظات:</strong> <?php echo nl2br(htmlspecialchars($offer['notes'] ?? '')); ?></p>
        
        <?php if ($status == 'accepted'): ?>
            <span class="label label-success">✓ تم قبول هذا العرض</span>
        <?php elseif ($status == 'rejected'): ?>
            <span class="label label-danger">✗ تم رفض هذا العرض</span>
        <?php elseif ($is_supplier): ?>
            <div class="offer-actions">
                <?php if ($update_count == 0): ?>
                    <button class="btn btn-success" onclick="sendQuoteDirect(<?php echo $rfq_id; ?>, <?php echo $offer_id; ?>)">إرسال عرض سعر</button>
                <?php elseif ($update_count == 1): ?>
                    <button class="btn btn-warning" onclick="sendQuoteDirect(<?php echo $rfq_id; ?>, <?php echo $offer_id; ?>)">تعديل عرض السعر (مرة أخيرة)</button>
                <?php else: ?>
                    <span class="label">تم تحديث السعر مرتين</span>
                <?php endif; ?>
                <?php if ($chat_exists && $chat_code): ?>
                    <button class="btn btn-info" onclick="openChat('<?php echo $chat_code; ?>')">فتح المحادثة</button>
                <?php endif; ?>
            </div>
        <?php elseif ($is_buyer): ?>
            <div class="offer-actions">
                <button class="btn btn-success" onclick="acceptOffer(<?php echo $offer_id; ?>, <?php echo $rfq_id; ?>)">قبول العرض</button>
                <button class="btn btn-danger" onclick="rejectOffer(<?php echo $offer_id; ?>, <?php echo $rfq_id; ?>)">رفض العرض</button>
                <?php if ($chat_exists && $chat_code): ?>
                    <button class="btn btn-info" onclick="openChat('<?php echo $chat_code; ?>')">فتح المحادثة</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
// ============================================
// دوال المورد (إرسال / تعديل عرض السعر)
// ============================================
function sendQuoteDirect(rfqId, offerId) {
    let price = prompt('السعر (USD):');
    if (!price) return;
    let delivery = prompt('مدة التوصيل (أيام):');
    if (!delivery) return;
    let notes = prompt('ملاحظات (اختياري):', '');
    
    fetch('/lib/update_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&price=' + price + '&delivery_days=' + delivery + '&notes=' + encodeURIComponent(notes || '') + '&offer_id=' + offerId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.warning) alert('⚠️ ' + data.warning);
            alert('✅ ' + data.message);
            if (data.whatsapp_url) window.open(data.whatsapp_url, '_blank');
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => alert('خطأ في الاتصال: ' + error.message));
}

// ============================================
// دوال المشتري (قبول / رفض العرض)
// ============================================
function acceptOffer(offerId, rfqId) {
    if (!confirm('قبول هذا العرض؟ سيتم كشف بيانات المورد.')) return;
    
    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId + '&rfq_id=' + rfqId + '&action=accept'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            if (data.supplier_data) {
                alert('بيانات المورد:\n' + data.supplier_data.company_name + '\n' + data.supplier_data.phone);
            }
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => alert('خطأ: ' + error.message));
}

function rejectOffer(offerId, rfqId) {
    if (!confirm('رفض هذا العرض؟')) return;
    
    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId + '&rfq_id=' + rfqId + '&action=reject'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ تم رفض العرض');
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => alert('خطأ: ' + error.message));
}

function openChat(chatCode) {
    if (chatCode) {
        window.open('/chat/chat.php?chat_code=' + chatCode, '_blank');
    } else {
        alert('لا توجد محادثة مفتوحة');
    }
}
</script>

</body>
</html>

<?php
if (isset($stmt_to) && $stmt_to !== null && is_object($stmt_to)) {
    mysqli_stmt_close($stmt_to);
}
?>