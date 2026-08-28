<?php
session_start();
require_once __DIR__ . '/../lib/connect.php';

if (!isset($_SESSION['uid_indm'])) { echo "غير مصرح"; exit; }
$current_user = (int)$_SESSION['uid_indm'];

if (!isset($_POST['id']) || !isset($_POST['type'])) { echo "بيانات غير مكتملة"; exit; }

$msg_id = (int)$_POST['id'];
$type = $_POST['type'];

$sql = "SELECT * FROM message WHERE msg_id = $msg_id";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_object($result);
if (!$row) { echo "الرسالة غير موجودة"; exit; }

mysqli_query($con, "UPDATE message SET msg_read = '1' WHERE msg_id = $msg_id");

$from = mysqli_fetch_object(mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_from}"));
$to = mysqli_fetch_object(mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_to}"));

$rfq_id = $row->msg_entity_id;

// جلب عروض الأسعار
$offers_res = mysqli_query($con, "SELECT * FROM offers WHERE rfq_id = $rfq_id ORDER BY created_at DESC");
$offers = [];
while ($off = mysqli_fetch_assoc($offers_res)) {
    $offers[] = $off;
}

// جلب الشات
$chat_code = '';
$chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if ($chat_check && mysqli_num_rows($chat_check) > 0) {
    $chat_data = mysqli_fetch_assoc($chat_check);
    $chat_code = $chat_data['chat_code'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>تفاصيل الرسالة</title>
    <style>
        body { font-family: Arial; direction: rtl; padding: 20px; }
        .info { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .btn { display: inline-block; padding: 8px 15px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-info { background: #17a2b8; color: white; }
        .offer-card { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px; background: #f9f9f9; }
        .status-accepted { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
    </style>
</head>
<body>
<div class="info">
    <p><strong>إلى:</strong> <?php echo $to->fname . ' ' . $to->lname; ?></p>
    <p><strong>من:</strong> <?php echo $from->fname . ' ' . $from->lname; ?></p>
    <p><strong>التاريخ:</strong> <?php echo $row->msg_date; ?></p>
    <p><strong>الموضوع:</strong> <?php echo htmlspecialchars($row->msg_subject); ?></p>
    <p><strong>الرسالة:</strong> <?php echo nl2br(htmlspecialchars($row->msg_message)); ?></p>
</div>

<!-- ============================================ -->
<!-- عروض الأسعار والأزرار -->
<!-- ============================================ -->
<?php if (!empty($offers)): ?>
    <h3>عروض الأسعار</h3>
    <?php foreach ($offers as $offer): 
        $offer_id = $offer['id'];
        $update_count = (int)$offer['update_count'];
        $status = $offer['status'];
    ?>
    <div class="offer-card">
        <p><strong>السعر:</strong> <?php echo $offer['price']; ?> USD</p>
        <p><strong>مدة التوصيل:</strong> <?php echo $offer['delivery_days']; ?> يوم</p>
        <p><strong>ملاحظات:</strong> <?php echo nl2br(htmlspecialchars($offer['notes'])); ?></p>
        <p><strong>الحالة:</strong> <span class="status-<?php echo $status; ?>"><?php echo $status; ?></span></p>
        
        <!-- أزرار المورد (صاحب العرض) -->
        <?php if ($current_user == $row->msg_to && $status == 'pending'): ?>
            <?php if ($update_count == 0): ?>
                <button class="btn btn-success" onclick="sendOffer(<?php echo $rfq_id; ?>, <?php echo $offer_id; ?>)">إرسال عرض سعر</button>
            <?php elseif ($update_count == 1): ?>
                <button class="btn btn-warning" onclick="updateOffer(<?php echo $rfq_id; ?>, <?php echo $offer_id; ?>)">تعديل عرض السعر (مرة أخيرة)</button>
            <?php else: ?>
                <span>تم التعديل مرتين - لا يمكن التعديل مرة أخرى</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- أزرار المشتري (صاحب الطلب) -->
        <?php if ($current_user == $row->msg_from && $status == 'pending'): ?>
            <button class="btn btn-success" onclick="acceptOffer(<?php echo $offer_id; ?>, <?php echo $rfq_id; ?>)">قبول العرض</button>
            <button class="btn btn-danger" onclick="rejectOffer(<?php echo $offer_id; ?>, <?php echo $rfq_id; ?>)">رفض العرض</button>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- زر فتح المحادثة -->
<?php if ($chat_code): ?>
    <div style="margin-top:20px;">
        <a href="/chat/chat.php?chat_code=<?php echo $chat_code; ?>" class="btn btn-info" target="_blank">💬 فتح المحادثة</a>
    </div>
<?php endif; ?>

<script>
// ============================================
// دوال المورد
// ============================================
function sendOffer(rfqId, offerId) {
    let price = prompt('السعر (USD):');
    if (!price) return;
    let delivery = prompt('مدة التوصيل (أيام):');
    if (!delivery) return;
    let notes = prompt('ملاحظات:', '');
    
    fetch('/ajax-file/supplier_offer_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&price=' + price + '&delivery_days=' + delivery + '&notes=' + encodeURIComponent(notes) + '&offer_id=' + offerId
    })
    .then(r => r.json())
    .then(d => { alert(d.message); location.reload(); })
    .catch(e => alert('خطأ: ' + e.message));
}

function updateOffer(rfqId, offerId) {
    sendOffer(rfqId, offerId);
}

// ============================================
// دوال المشتري
// ============================================
function acceptOffer(offerId, rfqId) {
    if (!confirm('قبول هذا العرض؟')) return;
    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId + '&rfq_id=' + rfqId + '&action=accept'
    })
    .then(r => r.json())
    .then(d => { alert(d.message); if (d.success) location.reload(); })
    .catch(e => alert('خطأ: ' + e.message));
}

function rejectOffer(offerId, rfqId) {
    if (!confirm('رفض هذا العرض؟')) return;
    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId + '&rfq_id=' + rfqId + '&action=reject'
    })
    .then(r => r.json())
    .then(d => { alert(d.message); if (d.success) location.reload(); })
    .catch(e => alert('خطأ: ' + e.message));
}
</script>
</body>
</html>