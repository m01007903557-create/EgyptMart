<?php
/**
 * chat/chat.php - صفحة المحادثة بين المورد والمشتري
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm'])) {
    header('Location: /sign-in.php');
    exit;
}

$user_id = (int)$_SESSION['uid_indm'];

// ============================================================
// جلب chat_code أو rfq_id من الرابط
// ============================================================
$chat_code = isset($_GET['chat_code']) ? trim($_GET['chat_code']) : '';
$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if (empty($chat_code) && $rfq_id > 0) {
    $chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
    if ($chat_check && mysqli_num_rows($chat_check) > 0) {
        $chat_data = mysqli_fetch_assoc($chat_check);
        $chat_code = $chat_data['chat_code'];
    }
}

if (empty($chat_code)) {
    die('خطأ: لم يتم توفير كود الشات في الرابط');
}

// ============================================================
// جلب بيانات الشات وطلب الشراء
// ============================================================
$sql = "SELECT c.*, 
               br.br_id as rfq_id,
               br.br_pd_name as product_name,
               br.br_estimate_qty,
               br.br_estimate_qty_unit,
               br.br_requirement,
               u.fname as buyer_fname, u.lname as buyer_lname, u.mobile1 as buyer_phone,
               s.fname as supplier_fname, s.lname as supplier_lname
        FROM chat_rooms c
        LEFT JOIN buy_requirement br ON c.rfq_id = br.br_id
        LEFT JOIN user u ON c.buyer_id = u.usr_id
        LEFT JOIN user s ON c.supplier_id = s.usr_id
        WHERE c.chat_code = '$chat_code'";

$result = mysqli_query($con, $sql);
$chat = mysqli_fetch_assoc($result);

if (!$chat) {
    die('خطأ: الشات غير موجود');
}

$rfq_id = $chat['rfq_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>المحادثة - طلب شراء #<?php echo $rfq_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; direction: rtl; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        .rfq-box { background: #e8f4f8; padding: 20px; border-bottom: 3px solid #17a2b8; }
        .chat-box { padding: 20px; height: 400px; overflow-y: auto; background: #f5f5f5; }
        .message { margin-bottom: 15px; }
        .message.supplier { text-align: right; }
        .message.buyer { text-align: left; }
        .bubble { display: inline-block; padding: 10px 15px; border-radius: 18px; max-width: 70%; }
        .supplier .bubble { background: #e8f5e9; }
        .buyer .bubble { background: #25D366; color: white; }
        .input-area { padding: 15px; border-top: 1px solid #ddd; display: flex; gap: 10px; background: white; }
        .input-area input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 25px; }
        .input-area button { background: #25D366; color: white; border: none; padding: 10px 20px; border-radius: 25px; cursor: pointer; }
        #emojiBtn { background: none; border: none; font-size: 24px; cursor: pointer; padding: 0 10px; }
        .emoji-picker { display: none; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 8px; margin-top: 10px; }
        .emoji-picker span { font-size: 24px; cursor: pointer; padding: 5px; }
    </style>
</head>
<body>

<div class="container">
    <!-- تذكرة طلب الشراء -->
    <div class="rfq-box">
        <h3><i class="fa fa-shopping-cart"></i> طلب شراء #<?php echo $rfq_id; ?></h3>
        <p><strong>المنتج:</strong> <?php echo htmlspecialchars($chat['product_name']); ?></p>
        <p><strong>الكمية:</strong> <?php echo $chat['br_estimate_qty'] . ' ' . $chat['br_estimate_qty_unit']; ?></p>
        <p><strong>التفاصيل:</strong> <?php echo nl2br(htmlspecialchars($chat['br_requirement'])); ?></p>
        <p><strong>المشتري:</strong> <?php echo $chat['buyer_fname'] . ' ' . $chat['buyer_lname']; ?></p>
        <p><strong>المورد:</strong> <?php echo $chat['supplier_fname'] . ' ' . $chat['supplier_lname']; ?></p>
    </div>

    <!-- نافذة المحادثة -->
    <div class="chat-box" id="chatMessages">
        <p class="text-center text-muted">جارٍ تحميل الرسائل...</p>
    </div>

    <!-- منطقة الإدخال -->
    <div class="input-area">
        <button id="emojiBtn">😊</button>
        <input type="text" id="messageInput" placeholder="اكتب رسالتك...">
        <button onclick="sendMessage()"><i class="fa fa-send"></i> إرسال</button>
    </div>
    <div class="emoji-picker" id="emojiPicker">
        <span onclick="addEmoji('😊')">😊</span>
        <span onclick="addEmoji('😂')">😂</span>
        <span onclick="addEmoji('❤️')">❤️</span>
        <span onclick="addEmoji('👍')">👍</span>
        <span onclick="addEmoji('🎉')">🎉</span>
        <span onclick="addEmoji('😢')">😢</span>
        <span onclick="addEmoji('🔥')">🔥</span>
        <span onclick="addEmoji('✅')">✅</span>
    </div>
</div>

<script>
// ============================================================
// دوال المحادثة
// ============================================================
var chatId = <?php echo (int)$chat['chat_id']; ?>;
var currentUserId = <?php echo (int)$user_id; ?>;
var supplierId = <?php echo (int)$chat['supplier_id']; ?>;
var buyerId = <?php echo (int)$chat['buyer_id']; ?>;
var senderType = (currentUserId == supplierId) ? 'supplier' : 'buyer';

function loadMessages() {
    if (!chatId) return;
    fetch('/chat/ajax_chat.php?action=get&chat_id=' + chatId)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.messages) {
                var html = '';
                for (var i = 0; i < data.messages.length; i++) {
                    var msg = data.messages[i];
                    var cls = msg.sender_type === 'supplier' ? 'supplier' : 'buyer';
                    var bg = msg.sender_type === 'supplier' ? '#e8f5e9' : '#dcf8c6';
                    html += '<div class="message ' + cls + '">';
                    html += '<div class="bubble" style="background:' + bg + ';">' + msg.message + '</div>';
                    html += '</div>';
                }
                document.getElementById('chatMessages').innerHTML = html || '<p class="text-center text-muted">لا توجد رسائل بعد</p>';
                document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
            }
        })
        .catch(err => console.log('Error:', err));
}

function sendMessage() {
    var input = document.getElementById('messageInput');
    var msg = input ? input.value : '';
    if (!msg.trim()) return;
    fetch('/chat/ajax_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=send&chat_id=' + chatId + '&message=' + encodeURIComponent(msg) + '&sender_type=' + senderType
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages();
        }
    })
    .catch(err => console.log('Error:', err));
}

function addEmoji(emoji) {
    var input = document.getElementById('messageInput');
    if (input) {
        input.value += emoji;
        input.focus();
        document.getElementById('emojiPicker').style.display = 'none';
    }
}

document.getElementById('emojiBtn').addEventListener('click', function() {
    var picker = document.getElementById('emojiPicker');
    picker.style.display = picker.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    loadMessages();
    setInterval(loadMessages, 5000);
});
</script>

</body>
</html>