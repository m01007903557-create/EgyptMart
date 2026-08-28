<?php
session_start();
require_once __DIR__ . '/../lib/connect.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm'])) {
    echo "غير مصرح";
    exit;
}

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من البيانات
if (!isset($_POST['id']) || !isset($_POST['type'])) {
    echo "بيانات غير مكتملة";
    exit;
}

$msg_id = (int)$_POST['id'];
$type = $_POST['type'];

ini_set('session.gc_maxlifetime', 28800); // 8 ساعات
session_set_cookie_params(28800);
session_start();


// جلب بيانات الرسالة
$sql = "SELECT * FROM message WHERE msg_id = $msg_id";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_object($result);

if (!$row) {
    echo "الرسالة غير موجودة";
    exit;
}

// تحديث حالة القراءة
mysqli_query($con, "UPDATE message SET msg_read = '1' WHERE msg_id = $msg_id");

// جلب بيانات المرسل والمستلم
$from = mysqli_fetch_object(mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_from}"));
$to = mysqli_fetch_object(mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_to}"));

// تحديد دور المستخدم
$is_buyer = ($current_user == $row->msg_from);
$is_supplier = ($current_user == $row->msg_to);




// جلب rfq_id
$rfq_id = $row->msg_entity_id;
?>

<!DOCTYPE html>
<html>
<head>
    <title>تفاصيل الرسالة</title>
    <style>
  
        body { font-family: Arial; direction: rtl; padding: 20px; }
        .info { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .btn { padding: 8px 15px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-success { background: #28a745; color: white; }
    </style>
</head>
<body>
    <div class="info">
        <p><strong>إلى:</strong> <?php echo $to->fname . ' ' . $to->lname . ' <' . $to->email . '>'; ?></p>
        <p><strong>من:</strong> <?php echo $from->fname . ' ' . $from->lname . ' <' . $from->email . '>'; ?></p>
        <p><strong>التاريخ:</strong> <?php echo $row->msg_date; ?></p>
        <p><strong>الموضوع:</strong> <?php echo htmlspecialchars($row->msg_subject); ?></p>
        <p><strong>الرسالة:</strong> <?php echo nl2br(htmlspecialchars($row->msg_message)); ?></p>
    </div>
    
    
    <?php if ($is_buyer && $rfq_id > 0): ?>
    <div class="offers-section">
        <h4>عروض الأسعار</h4>
        <?php
        // جلب العروض
        $offers_sql = "SELECT o.*, u.fname, u.lname, bp.bnsprof_compname as company_name 
                       FROM offers o
                       LEFT JOIN user u ON o.supplier_id = u.usr_id
                       LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                       WHERE o.rfq_id = $rfq_id AND o.status = 'pending'
                       ORDER BY o.created_at DESC";
        $offers_res = mysqli_query($con, $offers_sql);
        while ($offer = mysqli_fetch_assoc($offers_res)): 
        ?>
            <div class="offer-card">
                <p><strong>المورد:</strong> <?php echo $offer['company_name'] ?? $offer['fname'] . ' ' . $offer['lname']; ?></p>
                <p><strong>السعر:</strong> <?php echo $offer['price'] . ' ' . $offer['currency']; ?></p>
                <p><strong>مدة التوصيل:</strong> <?php echo $offer['delivery_days']; ?> يوم</p>
                <p><strong>ملاحظات:</strong> <?php echo nl2br(htmlspecialchars($offer['notes'])); ?></p>
                
                <button class="btn btn-success" onclick="acceptOffer(<?php echo $offer['id']; ?>, <?php echo $rfq_id; ?>)">
    قبول العرض
</button>
<button class="btn btn-danger" onclick="rejectOffer(<?php echo $rfq_id; ?>)">
    رفض العرض
</button>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>
    
    
    
    
<!-- ============================================ -->
<!-- زر إرسال عرض السعر (للمورد فقط) -->
<!-- ============================================ -->
<?php if ($is_supplier && $rfq_id > 0): ?>
    <!-- Debug: is_supplier = true -->
    <button ...>إرسال عرض سعر</button>
<?php else: ?>
    <!-- Debug: is_supplier = false, user: <?php echo $current_user; ?>, msg_to: <?php echo $row->msg_to; ?> -->
<?php endif; ?>
   
    
    
    
</body>
</html>

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
        if (d.success) {
            alert(d.message);
            if (d.whatsapp_url) window.open(d.whatsapp_url, '_blank');
            location.reload();
        } else {
            alert('خطأ: ' + d.error);
        }
    })
    .catch(e => alert('خطأ: ' + e.message));
}



function acceptOffer(offerId, rfqId) {
  
    if (!confirm('قبول هذا العرض؟ سيتم إرسال رسالة تأكيد للطرفين.')) return;
    
    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&action=accept'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('🎉 مبروك! تم قبول العرض. يمكنك الآن التواصل مع المورد.');
            location.reload();
        } else {
            alert('❌ ' + d.error);
        }
    })
    .catch(e => alert('خطأ: ' + e.message));
}


function rejectOffer(rfqId) {
    if (!confirm('رفض هذا العرض؟')) return;
    
    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&action=reject'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('تم رفض العرض');
            location.reload();
        } else {
            alert('❌ ' + d.error);
        }
    })
    .catch(e => alert('خطأ: ' + e.message));
}





// دوال المشتري
$(document).ready(function() {
    $('.accept-offer').click(function() {
        let offerId = $(this).data('offer-id');
        let rfqId = $(this).data('rfq-id');
        if (confirm('قبول هذا العرض؟')) {
            $.post('/lib/accept_offer.php', {offer_id: offerId, rfq_id: rfqId, action: 'accept'}, function(response) {
                if (response.success) {
                    alert('✅ تم قبول العرض');
                    location.reload();
                } else {
                    alert('❌ ' + response.error);
                }
            }, 'json');
        }
    });
    
    $('.reject-offer').click(function() {
        let offerId = $(this).data('offer-id');
        let rfqId = $(this).data('rfq-id');
        if (confirm('رفض هذا العرض؟')) {
            $.post('/lib/accept_offer.php', {offer_id: offerId, rfq_id: rfqId, action: 'reject'}, function(response) {
                if (response.success) {
                    alert('✅ تم رفض العرض');
                    location.reload();
                } else {
                    alert('❌ ' + response.error);
                }
            }, 'json');
        }
    });
});

<!-- ============================================= -->
<!-- أزرار قبول/رفض للمشتري (إضافة فقط) -->
<!-- ============================================= -->
<?php if ($is_buyer && isset($offers) && !empty($offers)): ?>
<div style="margin-top:20px; padding:15px; background:#f0f0f0; border-radius:8px;">
    <h4>عروض الأسعار</h4>
    <?php foreach ($offers as $offer): ?>
        <div style="border:1px solid #ddd; padding:10px; margin:10px 0; border-radius:5px;">
            <p><strong>السعر:</strong> <?php echo $offer['price']; ?> USD</p>
            <p><strong>مدة التوصيل:</strong> <?php echo $offer['delivery_days']; ?> يوم</p>
            <button class="btn-accept" data-offer-id="<?php echo $offer['id']; ?>">قبول العرض</button>
            <button class="btn-reject" data-offer-id="<?php echo $offer['id']; ?>">رفض العرض</button>
        </div>
    <?php endforeach; ?>
</div>

<script>
$(document).ready(function() {
    $('.btn-accept').click(function() {
        let offerId = $(this).data('offer-id');
        if (confirm('قبول هذا العرض؟')) {
            $.post('/lib/accept_offer.php', {offer_id: offerId, action: 'accept'}, function(res) {
                alert(res.message);
                if(res.success) location.reload();
            }, 'json');
        }
    });
    $('.btn-reject').click(function() {
        let offerId = $(this).data('offer-id');
        if (confirm('رفض هذا العرض؟')) {
            $.post('/lib/accept_offer.php', {offer_id: offerId, action: 'reject'}, function(res) {
                alert(res.message);
                if(res.success) location.reload();
            }, 'json');
        }
    });
});
</script>
<?php endif; ?>