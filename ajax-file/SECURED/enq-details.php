<?php
session_start();
require_once __DIR__ . '/../lib/connect.php';
require_once __DIR__ . '/../includes/rfq_helpers.php';

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

$from_res = mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_from}");
$from = mysqli_fetch_object($from_res);
if (!$from) { $from = new stdClass(); $from->fname = 'غير معروف'; $from->lname = ''; $from->email = ''; }

$to_res = mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_to}");
$to = mysqli_fetch_object($to_res);
if (!$to) { $to = new stdClass(); $to->fname = 'غير معروف'; $to->lname = ''; $to->email = ''; }


// ============================================
// تحديد الأدوار
// ============================================
$rfq_id = $row->msg_entity_id ?? 0;
$is_buyer = ($current_user == $row->msg_from);
$is_supplier = ($current_user == $row->msg_to);
if ($is_buyer) $is_supplier = false;

// 1. جلب المشتري من جدول offers
if ($rfq_id > 0) {
    $offer_check = mysqli_query($con, "SELECT buyer_id, supplier_id FROM offers WHERE rfq_id = $rfq_id LIMIT 1");
    if ($offer_check) {
        $offer_data = mysqli_fetch_assoc($offer_check);
        if ($offer_data) {
            // ✅ إذا كان المستخدم الحالي هو المشتري في offers
            if ($current_user == $offer_data['buyer_id']) {
                $is_buyer = true;
                $is_supplier = false;
            }
            // ✅ إذا كان المستخدم الحالي هو المورد في offers
            if ($current_user == $offer_data['supplier_id']) {
                $is_supplier = true;
                $is_buyer = false;
            }
        }
    }
}

// 2. إذا لم يتم العثور على الأدوار من offers، استخدم msg_from و msg_to
if (!$is_buyer && !$is_supplier) {
    if ($current_user == $row->msg_from) {
        $is_buyer = true;
        $is_supplier = false;
    }
    if ($current_user == $row->msg_to) {
        $is_supplier = true;
        $is_buyer = false;
    }
}

// 3. ✅ تصحيح نهائي: المشتري ليس مورداً والمورد ليس مشترياً
if ($is_buyer) {
    $is_supplier = false;
}
if ($is_supplier) {
    $is_buyer = false;
}

// سطر تشخيب
echo "<!-- Debug: current_user=$current_user, is_buyer=" . ($is_buyer ? 'true' : 'false') . ", is_supplier=" . ($is_supplier ? 'true' : 'false') . " -->";

// جلب عروض الأسعار
$offers = [];
$offers_res = mysqli_query($con, "SELECT * FROM offers WHERE rfq_id = $rfq_id ORDER BY created_at DESC");
while ($offer = mysqli_fetch_assoc($offers_res)) { $offers[] = $offer; }

// جلب رمز المحادثة
$chat_code = '';
$chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if ($chat_check && mysqli_num_rows($chat_check) > 0) {
    $chat_data = mysqli_fetch_assoc($chat_check);
    $chat_code = $chat_data['chat_code'];
}

echo "<!-- Debug: chat_code = " . ($chat_code ? $chat_code : 'غير موجود') . " -->";
?>
<!DOCTYPE html>
<html>
<head>
    <title>تفاصيل الرسالة</title>
    <style>
        body { font-family: Arial; direction: rtl; padding: 20px; }
        .info { background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 6px 12px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 13px; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .offer-card { border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px; }
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
<!-- أزرار بسيطة -->
<!-- ============================================ -->
<?php
// تحديد الأزرار يدوياً
$show_send = ($is_supplier && $rfq_id > 0);
$show_accept = ($is_buyer && $rfq_id > 0 && !empty($offers));

// جلب chat_code
$chat_code = '';
$chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if ($chat_check && mysqli_num_rows($chat_check) > 0) {
    $chat_data = mysqli_fetch_assoc($chat_check);
    $chat_code = $chat_data['chat_code'];
}
?>

<?php if ($show_send): ?>
    <button class="btn btn-success" onclick="sendOffer(<?php echo $rfq_id; ?>)">إرسال عرض سعر</button>
<?php endif; ?>

<?php if ($show_accept): ?>
    <?php $offer = $offers[0]; ?>
    <div style="margin:15px 0; padding:15px; background:#e8f4f8; border-radius:8px;">
        <h4>عرض السعر الحالي</h4>
        <p><strong>السعر:</strong> <?php echo $offer['price']; ?> USD</p>
        <p><strong>مدة التوصيل:</strong> <?php echo $offer['delivery_days']; ?> يوم</p>
        <p><strong>ملاحظات:</strong> <?php echo nl2br(htmlspecialchars($offer['notes'])); ?></p>
        <button class="btn btn-success" onclick="acceptOffer(<?php echo $offer['id']; ?>, <?php echo $rfq_id; ?>)">قبول العرض</button>
        <button class="btn btn-danger" onclick="rejectOffer(<?php echo $offer['id']; ?>, <?php echo $rfq_id; ?>)">رفض العرض</button>
    </div>
<?php endif; ?>

<!-- زر المحادثة -->
<?php if ($chat_code): ?>
    <a href="/chat/chat.php?chat_code=<?php echo $chat_code; ?>" class="btn btn-info" target="_blank">
        <i class="fa fa-comments"></i> بدء المحادثة
    </a>
<?php endif; ?>

<script>
function sendOffer(rfqId) {
    let price = prompt('السعر (USD):');
    if (!price) return;
    let delivery = prompt('مدة التوصيل (أيام):');
    if (!delivery) return;
    let notes = prompt('ملاحظات:', '');
    
    fetch('/ajax-file/supplier_offer_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&price=' + price + '&delivery_days=' + delivery + '&notes=' + encodeURIComponent(notes)
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        
        // ✅ فتح واتساب إذا كان موجوداً
        if (d.whatsapp_url && d.whatsapp_url != '') {
            window.open(d.whatsapp_url, '_blank');
        }
        
        // ✅ فتح صفحة المحادثة مباشرة (بدون سؤال)
        if (d.chat_code) {
            window.open('/chat/chat.php?chat_code=' + d.chat_code, '_blank');
        }
        
        location.reload();
    })
    .catch(e => alert('خطأ: ' + e.message));
}

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