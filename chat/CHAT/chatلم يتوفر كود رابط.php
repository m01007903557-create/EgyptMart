<?php
session_start();
require_once __DIR__ . '/../lib/connect.php';

$chat_code = isset($_GET['chat_code']) ? trim($_GET['chat_code']) : '';
if (empty($chat_code)) {
    die('خطأ: لم يتم توفير كود الشات في الرابط');
}

$sql = "SELECT c.*, 
               br.br_id as rfq_id,
               p.pd_title as product_name,
               u.fname as buyer_fname, u.lname as buyer_lname,
               s.fname as supplier_fname, s.lname as supplier_lname
        FROM chat_rooms c
        LEFT JOIN buy_requirement br ON c.rfq_id = br.br_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON c.buyer_id = u.usr_id
        LEFT JOIN user s ON c.supplier_id = s.usr_id
        WHERE c.chat_code = '$chat_code'";
$result = mysqli_query($con, $sql);
$chat = mysqli_fetch_assoc($result);

if (!$chat) {
    die('خطأ: الشات غير موجود');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>المحادثة - طلب شراء #<?php echo $chat['rfq_id']; ?></title>
    <style>
        body { font-family: Arial; direction: rtl; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: #25D366; color: white; padding: 15px; text-align: center; }
        .rfq-info { background: #f9f9f9; padding: 15px; border-bottom: 1px solid #ddd; }
        .messages { padding: 20px; height: 400px; overflow-y: auto; background: #f5f5f5; }
        .message { margin-bottom: 15px; }
        .message.supplier { text-align: right; }
        .message.buyer { text-align: left; }
        .bubble { display: inline-block; padding: 10px 15px; border-radius: 18px; max-width: 70%; }
        .supplier .bubble { background: #e8f5e9; }
        .buyer .bubble { background: #25D366; color: white; }
        .input-area { padding: 15px; border-top: 1px solid #ddd; display: flex; gap: 10px; background: white; }
        .input-area input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 25px; }
        .input-area button { background: #25D366; color: white; border: none; padding: 10px 20px; border-radius: 25px; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <div class="header"><h2>💬 المحادثة</h2></div>
    <div class="rfq-info">
        <strong>المنتج:</strong> <?php echo htmlspecialchars($chat['product_name']); ?><br>
        <strong>RFQ #:</strong> <?php echo $chat['rfq_id']; ?><br>
        <strong>المشتري:</strong> <?php echo $chat['buyer_fname'] . ' ' . $chat['buyer_lname']; ?><br>
        <strong>المورد:</strong> <?php echo $chat['supplier_fname'] . ' ' . $chat['supplier_lname']; ?>
    </div>
    <div class="messages" id="messages"></div>
    <div class="input-area">
        <input type="text" id="messageInput" placeholder="اكتب رسالتك...">
        <button onclick="sendMessage()">إرسال</button>
    </div>
</div>

<script>
var chatCode = '<?php echo $chat_code; ?>';
var currentUserId = <?php echo (int)$_SESSION['uid_indm']; ?>;
var supplierId = <?php echo (int)$chat['supplier_id']; ?>;
var buyerId = <?php echo (int)$chat['buyer_id']; ?>;
var senderType = (currentUserId == supplierId) ? 'supplier' : 'buyer';

function loadMessages() {
    fetch('/chat/ajax_chat.php?action=get&chat_code=' + chatCode)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.messages) {
                var html = '';
                for (var i = 0; i < data.messages.length; i++) {
                    var msg = data.messages[i];
                    var cls = msg.sender_type;
                    html += '<div class="message ' + cls + '">';
                    html += '<div class="bubble">' + msg.message + '</div>';
                    html += '</div>';
                }
                document.getElementById('messages').innerHTML = html || '<p style="text-align:center;color:#999;">لا توجد رسائل بعد</p>';
                document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
            }
        })
        .catch(err => console.log('Error:', err));
}

function sendMessage() {
    var input = document.getElementById('messageInput');
    var msg = input.value.trim();
    if (!msg) return;
    fetch('/chat/ajax_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=send&chat_code=' + chatCode + '&message=' + encodeURIComponent(msg) + '&sender_type=' + senderType
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) { input.value = ''; loadMessages(); }
    })
    .catch(err => console.log('Error:', err));
}

document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('messageInput');
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
    loadMessages();
    setInterval(loadMessages, 5000);
});
</script>
</body>
</html>