<?php
session_start();
require_once "lib/connect.php";

$user_id = $_SESSION['uid_indm'] ?? 0;
$user_type = isset($_SESSION['uid_indm']) ? 'buyer' : 'supplier';

if (!$user_id) {
    header('Location: sign-in.php');
    exit;
}

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;
if (!$rfq_id) {
    die('RFQ ID مطلوب');
}

// جلب بيانات الطلب والمحادثة
$sql = "SELECT w.*, 
               u.fname, u.lname, u.mobile1, u.email,
               bp.bnsprof_comp_url, bp.bnsprof_mobile1, bp.bnsprof_email
        FROM whatsapp_rfq_messages w
        LEFT JOIN user u ON w.buyer_id = u.usr_id
        LEFT JOIN business_profile bp ON w.supplier_id = bp.bnsprof_uid
        WHERE w.rfq_id = $rfq_id";
$res = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($res);

if (!$rfq) {
    die('الطلب غير موجود');
}

// جلب عرض السعر المقبول
$quote_sql = "SELECT * FROM whatsapp_quotes WHERE rfq_id = $rfq_id AND status = 'accepted' LIMIT 1";
$quote_res = mysqli_query($con, $quote_sql);
$quote = mysqli_fetch_assoc($quote_res);

// جلب رسائل المحادثة
$chat_sql = "SELECT * FROM whatsapp_chat WHERE rfq_id = $rfq_id ORDER BY created_date ASC";
$chat_res = mysqli_query($con, $chat_sql);
?>
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>محادثة - RFQ #<?php echo $rfq_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { font-family: Arial; background: #f5f5f5; margin: 0; padding: 20px; }
        .chat-container { max-width: 800px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; }
        .chat-header { background: #25D366; color: white; padding: 15px; text-align: center; }
        .contact-info { background: #f9f9f9; padding: 15px; border-bottom: 1px solid #ddd; font-size: 14px; }
        .messages { padding: 20px; height: 400px; overflow-y: auto; }
        .message { margin-bottom: 15px; }
        .message.buyer { text-align: left; }
        .message.supplier { text-align: right; }
        .message .bubble { display: inline-block; padding: 10px 15px; border-radius: 18px; max-width: 70%; }
        .message.buyer .bubble { background: #e8f5e9; color: #333; }
        .message.supplier .bubble { background: #25D366; color: white; }
        .message .time { font-size: 11px; color: #999; margin-top: 5px; }
        .chat-input { padding: 15px; border-top: 1px solid #ddd; display: flex; gap: 10px; }
        .chat-input input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 25px; }
        .chat-input button { background: #25D366; color: white; border: none; padding: 10px 20px; border-radius: 25px; cursor: pointer; }
        .quote-info { background: #fff3cd; padding: 15px; margin: 10px; border-radius: 8px; text-align: center; }
        .btn-accept { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        <i class="fa fa-whatsapp"></i> محادثة - RFQ #<?php echo $rfq_id; ?>
    </div>
    
    <div class="contact-info">
        <strong>المنتج:</strong> <?php echo htmlspecialchars($rfq['product_name'] ?? ''); ?><br>
        <strong>الكمية:</strong> <?php echo htmlspecialchars($rfq['quantity_required']); ?>
    </div>
    
    <?php if ($quote && $rfq['status'] == 'accepted'): ?>
    <div class="contact-info" style="background:#e8f5e9;">
        <strong>📞 بيانات التواصل</strong><br>
        <?php if ($user_type == 'buyer'): ?>
        <strong>المورد:</strong> <?php echo htmlspecialchars($rfq['bnsprof_comp_url'] ?? ''); ?><br>
        <strong>جوال المورد:</strong> <?php echo $rfq['bnsprof_mobile1']; ?><br>
        <strong>إيميل المورد:</strong> <?php echo $rfq['bnsprof_email']; ?>
        <?php else: ?>
        <strong>المشتري:</strong> <?php echo htmlspecialchars($rfq['fname'] . ' ' . $rfq['lname']); ?><br>
        <strong>جوال المشتري:</strong> <?php echo $rfq['mobile1']; ?><br>
        <strong>إيميل المشتري:</strong> <?php echo $rfq['email']; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($quote && $rfq['status'] != 'accepted' && $user_type == 'buyer'): ?>
    <div class="quote-info">
        <strong>عرض السعر:</strong><br>
        السعر: <?php echo $quote['unit_price']; ?> USD<br>
        أقل كمية: <?php echo $quote['moq']; ?><br>
        مدة التوصيل: <?php echo htmlspecialchars($quote['delivery_time']); ?><br>
        رسالة المورد: <?php echo nl2br(htmlspecialchars($quote['supplier_message'])); ?><br>
        <button class="btn-accept" onclick="acceptQuote(<?php echo $quote['wq_id']; ?>, <?php echo $rfq_id; ?>)">قبول عرض السعر</button>
    </div>
    <?php endif; ?>
    
    <div class="messages" id="messages">
        <?php while($msg = mysqli_fetch_assoc($chat_res)): ?>
        <div class="message <?php echo $msg['sender_type']; ?>">
            <div class="bubble">
                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                <div class="time"><?php echo date('H:i', strtotime($msg['created_date'])); ?></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div class="chat-input">
        <input type="text" id="chatMessage" placeholder="اكتب رسالتك...">
        <button onclick="sendMessage()"><i class="fa fa-paper-plane"></i> إرسال</button>
    </div>
</div>

<script>
function sendMessage() {
    var message = document.getElementById('chatMessage').value;
    if(!message) return;
    
    fetch('/ajax/whatsapp_chat_handler.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=send&rfq_id=<?php echo $rfq_id; ?>&message=' + encodeURIComponent(message)
    }).then(function(res) { return res.json(); }).then(function(data) {
        if(data.success) {
            document.getElementById('chatMessage').value = '';
            loadMessages();
        }
    });
}

function loadMessages() {
    fetch('/ajax/whatsapp_chat_handler.php?action=get&rfq_id=<?php echo $rfq_id; ?>')
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if(data.success) {
                var html = '';
                data.messages.forEach(function(msg) {
                    var cls = msg.sender_type == 'buyer' ? 'buyer' : 'supplier';
                    html += '<div class="message ' + cls + '">';
                    html += '<div class="bubble">' + msg.message.replace(/\n/g, '<br>');
                    html += '<div class="time">' + msg.time + '</div></div></div>';
                });
                document.getElementById('messages').innerHTML = html;
                document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
            }
        });
}

function acceptQuote(quoteId, rfqId) {
    if(confirm('هل أنت متأكد من قبول هذا العرض؟ سيتم كشف بيانات التواصل مع المورد.')) {
        fetch('/buyer/accept_quote_handler.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'quote_id=' + quoteId + '&rfq_id=' + rfqId
        }).then(function(res) { return res.json(); }).then(function(data) {
            if(data.success) {
                alert('تم قبول العرض بنجاح. يمكنك الآن التواصل مع المورد.');
                location.reload();
            } else {
                alert('خطأ: ' + data.error);
            }
        });
    }
}

// تحديث الرسائل كل 5 ثوان
setInterval(loadMessages, 5000);
loadMessages();
</script>
</body>
</html>