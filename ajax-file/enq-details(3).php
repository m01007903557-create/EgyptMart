<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../lib/connect.php';

if (!isset($_SESSION['uid_indm'])) { echo "غير مصرح"; exit; }
$current_user = (int)$_SESSION['uid_indm'];

if (!isset($_POST['id']) || !isset($_POST['type'])) { echo "بيانات غير مكتملة"; exit; }

$msg_id = (int)$_POST['id'];


$sql = "SELECT * FROM message WHERE msg_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $msg_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Message not found");
}

// ✅ تعريف $rfq_id هنا (بعد جلب $row)
$rfq_id = 0;
if (isset($row->msg_entity_id) && $row->msg_entity_id > 0) {
    $rfq_id = (int)$row->msg_entity_id;
}
// ✅ الآن استخدم $rfq_id في الاستعلامات
$offers_sql = "SELECT * FROM offers WHERE rfq_id = $rfq_id ORDER BY created_at DESC";
// ...


mysqli_query($con, "UPDATE message SET msg_read = '1' WHERE msg_id = $msg_id");
?>

<?php
// ... جلب بيانات الرسالة ($row) ...

// ============================================
// جلب بيانات المرسل والمستلم
// ============================================
$from = null;
$to = null;

$from_res = mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_from}");
if ($from_res) {
    $from = mysqli_fetch_object($from_res);
}
if (!$from) {
    $from = new stdClass();
    $from->fname = 'غير معروف';
    $from->lname = '';
    $from->email = '';
}

$to_res = mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_to}");
if ($to_res) {
    $to = mysqli_fetch_object($to_res);
}
if (!$to) {
    $to = new stdClass();
    $to->fname = 'غير معروف';
    $to->lname = '';
    $to->email = '';
}

// ============================================
// تحديد الأدوار
// ============================================
$is_buyer = ($current_user == $row->msg_from);
$is_supplier = ($current_user == $row->msg_to);
$rfq_id = $row->msg_entity_id ?? 0;

// ... باقي الكود ...

// ============================================
// تصحيح: التأكد من أن المستخدم ليس مشترياً عند عرض زر "إرسال عرض سعر"
// ============================================
if ($current_user == $row->msg_from) {
    $is_supplier = false; // المشتري ليس مورداً
}
// ============================================
// 3. ✅ الآن استخدم $rfq_id في التصحيح (بعد تعريفه)
// ============================================
if ($rfq_id > 0) {
    $offer_check = mysqli_query($con, "SELECT buyer_id FROM offers WHERE rfq_id = $rfq_id AND status = 'pending' LIMIT 1");
    if ($offer_check) {
        $offer_data = mysqli_fetch_assoc($offer_check);
        if ($offer_data && $offer_data['buyer_id'] == $current_user) {
            $is_buyer = true;
            $is_supplier = false;
        }
    }
}

// سطر تشخيب
echo "<!-- Debug: is_buyer=" . ($is_buyer ? 'true' : 'false') . ", is_supplier=" . ($is_supplier ? 'true' : 'false') . " -->";


// ============================================
// 4. جلب عروض الأسعار (بعد تعريف $rfq_id)
// ============================================
$offers = [];
if ($rfq_id > 0) {
    $offers_res = mysqli_query($con, "SELECT * FROM offers WHERE rfq_id = $rfq_id ORDER BY created_at DESC");
    while ($offer = mysqli_fetch_assoc($offers_res)) {
        $offers[] = $offer;
    }
}
// ============================================
// 5. جلب رمز المحادثة
// ============================================
$chat_code = '';
if ($rfq_id > 0) {
    $chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
    if ($chat_check && mysqli_num_rows($chat_check) > 0) {
        $chat_data = mysqli_fetch_assoc($chat_check);
        $chat_code = $chat_data['chat_code'];
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>تفاصيل الرسالة</title>
    <style>
        body { font-family: Arial; direction: rtl; padding: 20px; }
        .info { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .btn { display: inline-block; padding: 8px 15px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; }
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
<!-- زر إرسال عرض سعر (للمورد فقط) -->
<!-- ============================================ -->
<?php if ($is_supplier && $rfq_id > 0): ?>
    <button class="btn btn-success" onclick="sendOffer(<?php echo $rfq_id; ?>)">
        <i class="fa fa-money"></i> إرسال عرض سعر
    </button>
<?php endif; ?>


<!-- ============================================ -->
<!-- أزرار المشتري (يظهر فقط للمشتري) -->
<!-- ============================================ -->
<?php if ($is_buyer && $rfq_id > 0): ?>
    <?php
    $offer_sql = "SELECT * FROM offers WHERE rfq_id = $rfq_id AND status = 'pending' ORDER BY created_at DESC LIMIT 1";
    $offer_res = mysqli_query($con, $offer_sql);
    if (mysqli_num_rows($offer_res) > 0):
        $offer = mysqli_fetch_assoc($offer_res);
    ?>
    <div style="margin:15px 0; padding:15px; background:#e8f4f8; border-radius:8px;">
        <h4>عرض السعر الحالي</h4>
        <p><strong>السعر:</strong> <?php echo $offer['price']; ?> USD</p>
        <p><strong>مدة التوصيل:</strong> <?php echo $offer['delivery_days']; ?> يوم</p>
        <p><strong>ملاحظات:</strong> <?php echo nl2br(htmlspecialchars($offer['notes'])); ?></p>
        
        <button class="btn btn-success" onclick="acceptOffer(<?php echo $offer['id']; ?>, <?php echo $rfq_id; ?>)">قبول العرض</button>
        <button class="btn btn-danger" onclick="rejectOffer(<?php echo $offer['id']; ?>, <?php echo $rfq_id; ?>)">رفض العرض</button>
        
        <?php if ($chat_code): ?>
            <a href="/chat/chat.php?chat_code=<?php echo $chat_code; ?>" class="btn btn-info" target="_blank">فتح المحادثة</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<script>
function sendOffer(rfqId) {
        console.log('1- بدء sendOffer');

    let price = prompt('السعر (USD):');
    console.log('2- السعر:', price);
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
        
        // ✅ تشخيب: اطبع قيمة whatsapp_url في Console
        console.log('whatsapp_url:', d.whatsapp_url);
        
        // ✅ فقط إذا كان هناك رابط واتساب
        if (d.whatsapp_url && d.whatsapp_url != '') {
            window.open(d.whatsapp_url, '_blank');
        }
        
        location.reload();
    })
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