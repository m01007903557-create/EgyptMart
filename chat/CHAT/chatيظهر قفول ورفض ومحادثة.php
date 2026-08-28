<?php
session_start();
require_once __DIR__ . '/../lib/connect.php';

// جلب rfq_id من الرابط
$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;
$chat_code = isset($_GET['chat_code']) ? trim($_GET['chat_code']) : '';

if ($rfq_id == 0 && empty($chat_code)) {
    die("رقم الطلب أو كود الشات غير صحيح");
}

// إذا كان هناك chat_code، جلب rfq_id
if (!empty($chat_code)) {
    $chat_check = mysqli_query($con, "SELECT rfq_id FROM chat_rooms WHERE chat_code = '$chat_code' LIMIT 1");
    if ($chat_check && mysqli_num_rows($chat_check) > 0) {
        $chat_data = mysqli_fetch_assoc($chat_check);
        $rfq_id = $chat_data['rfq_id'];
    }
}

if ($rfq_id == 0) {
    die("رقم الطلب غير صحيح");
}

// إنشاء غرفة محادثة إذا لم تكن موجودة
$chat_check = mysqli_query($con, "SELECT * FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if (!$chat_check || mysqli_num_rows($chat_check) == 0) {
    $offer_check = mysqli_query($con, "SELECT supplier_id, buyer_id FROM offers WHERE rfq_id = $rfq_id LIMIT 1");
    if ($offer_check && mysqli_num_rows($offer_check) > 0) {
        $offer_data = mysqli_fetch_assoc($offer_check);
        $supplier_id = $offer_data['supplier_id'];
        $buyer_id = $offer_data['buyer_id'];
        
        $chat_code = 'CHAT_' . time() . '_' . $rfq_id;
        $insert_chat = "INSERT INTO chat_rooms (rfq_id, supplier_id, buyer_id, chat_code, created_at, status) 
                        VALUES ($rfq_id, $supplier_id, $buyer_id, '$chat_code', NOW(), 'active')";
        mysqli_query($con, $insert_chat);
    }
}

// جلب بيانات الطلب
$sql = "SELECT br.*, 
               u.fname, u.lname, u.mobile1, u.email,
               p.pd_title
        FROM buy_requirement br
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        WHERE br.br_id = $rfq_id";
$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    die("الطلب غير موجود");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>المحادثة - طلب شراء #<?php echo $rfq_id; ?></title>
    <style>
        body { font-family: Arial; direction: rtl; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .rfq-box { background: #e8f4f8; padding: 20px; border-bottom: 3px solid #17a2b8; }
        .chat-area { padding: 20px; min-height: 300px; max-height: 500px; overflow-y: auto; }
        .input-area { padding: 15px; border-top: 1px solid #ddd; background: #fafafa; }
        .input-area textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .input-area button { margin-top: 10px; padding: 10px 20px; background: #25D366; color: #fff; border: none; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <div class="rfq-box">
        <h2>📦 طلب شراء #<?php echo $rfq_id; ?></h2>
        <p><strong>المنتج:</strong> <?php echo htmlspecialchars($rfq['pd_title'] ?? 'غير محدد'); ?></p>
        <p><strong>الكمية:</strong> <?php echo $rfq['br_estimate_qty'] . ' ' . $rfq['br_estimate_qty_unit']; ?></p>
        <p><strong>المشتري:</strong> <?php echo $rfq['fname'] . ' ' . $rfq['lname']; ?></p>
        <p><strong>هاتف المشتري:</strong> <?php echo $rfq['mobile1']; ?></p>
    </div>
    <div class="chat-area" id="chatMessages">
        <p style="text-align:center; color:#999;">المحادثة ستبدأ هنا...</p>
    </div>
    <div class="input-area">
        <textarea id="chatInput" rows="3" placeholder="اكتب رسالتك..."></textarea>
        <button onclick="sendMessage()">📤 إرسال</button>
    </div>
</div>

<script>
function sendMessage() {
    let input = document.getElementById('chatInput');
    let msg = input.value.trim();
    if (!msg) return alert('الرجاء كتابة رسالة');

    fetch('/chat/send_message_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=<?php echo $rfq_id; ?>&message=' + encodeURIComponent(msg)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            let chat = document.getElementById('chatMessages');
            let div = document.createElement('div');
            div.style.cssText = 'margin-bottom:10px; padding:10px; background:#dcf8c6; border-radius:10px;';
            div.innerHTML = '<strong>أنت:</strong><p>' + msg + '</p>';
            chat.appendChild(div);
            input.value = '';
            chat.scrollTop = chat.scrollHeight;
        } else {
            alert('فشل الإرسال: ' + d.error);
        }
    })
    .catch(e => alert('خطأ في الاتصال'));
}
</script>
</body>
</html>