<?php
/**
 * chat/chat.php - صفحة محادثة نظيفة (بدون هيدر/فوتر)
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/connect.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm'])) {
    die('يرجى تسجيل الدخول أولاً');
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
$sql = "SELECT 
            cr.*,
            br.br_id as rfq_id,
            br.br_pd_name as product_name,
            br.br_estimate_qty,
            br.br_estimate_qty_unit,
            br.br_requirement,
            br.br_posting_date,
            u.fname as buyer_fname, 
            u.lname as buyer_lname, 
            u.mobile1 as buyer_phone, 
            u.email as buyer_email,
            sp.fname as supplier_fname, 
            sp.lname as supplier_lname
        FROM chat_rooms cr
        LEFT JOIN buy_requirement br ON cr.rfq_id = br.br_id
        LEFT JOIN user u ON cr.buyer_id = u.usr_id
        LEFT JOIN user sp ON cr.supplier_id = sp.usr_id
        WHERE cr.chat_code = '$chat_code'";

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المحادثة - طلب شراء #<?php echo $rfq_id; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; direction: rtl; background: #f0f2f5; padding: 20px; }
        
        .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        
        /* تذكرة طلب الشراء */
        .rfq-ticket {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px;
            border-bottom: 3px solid #5a67d8;
        }
        .rfq-ticket h2 { font-size: 18px; margin-bottom: 10px; }
        .rfq-ticket table { width: 100%; font-size: 14px; }
        .rfq-ticket td { padding: 4px 0; }
        .rfq-ticket .label { opacity: 0.8; width: 100px; }
        
        /* نافذة المحادثة */
        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .message { margin-bottom: 15px; display: flex; }
        .message.supplier { justify-content: flex-start; }
        .message.buyer { justify-content: flex-end; }
        
        .bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        .supplier .bubble { background: #e8f5e9; color: #333; }
        .buyer .bubble { background: #25D366; color: #fff; }
        .time { font-size: 11px; color: #999; margin-top: 5px; display: block; }
        
        /* منطقة الإدخال */
        .input-area {
            padding: 15px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 10px;
            background: #fff;
        }
        .input-area input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
        }
        .input-area input:focus { border-color: #25D366; }
        .input-area button {
            background: #25D366;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
        }
        .input-area button:hover { background: #1da851; }
        
        .emoji-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 0 10px;
        }
        .emoji-picker {
            display: none;
            padding: 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-top: 10px;
        }
        .emoji-picker span {
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
        }
        .emoji-picker span:hover { background: #f0f0f0; border-radius: 5px; }
        
        /* رسالة عدم وجود رسائل */
        .no-messages { text-align: center; color: #999; padding: 20px; }
    </style>
</head>
<body>

<div class="container">
    <!-- ============================================ -->
    <!-- تذكرة طلب الشراء (بيانات كاملة) -->
    <!-- ============================================ -->
    <div class="rfq-ticket">
        <h2>📦 طلب شراء #<?php echo $rfq_id; ?></h2>
        <table>
            <tr><td class="label">المنتج:</td><td><?php echo htmlspecialchars($chat['product_name']); ?></td></tr>
            <tr><td class="label">الكمية:</td><td><?php echo $chat['br_estimate_qty'] . ' ' . $chat['br_estimate_qty_unit']; ?></td></tr>
            <tr><td class="label">التفاصيل:</td><td><?php echo nl2br(htmlspecialchars($chat['br_requirement'])); ?></td></tr>
            <tr><td class="label">التاريخ:</td><td><?php echo date('Y-m-d', strtotime($chat['br_posting_date'])); ?></td></tr>
            <tr><td class="label">المشتري:</td><td><?php echo $chat['buyer_fname'] . ' ' . $chat['buyer_lname']; ?></td></tr>
            <tr><td class="label">هاتف المشتري:</td><td><?php echo $chat['buyer_phone']; ?></td></tr>
            <tr><td class="label">بريد المشتري:</td><td><?php echo $chat['buyer_email']; ?></td></tr>
            <tr><td class="label">المورد:</td><td><?php echo $chat['supplier_fname'] . ' ' . $chat['supplier_lname']; ?></td></tr>
        </table>
    </div>

    <!-- ============================================ -->
    <!-- نافذة المحادثة -->
    <!-- ============================================ -->
    <div class="chat-box" id="chatMessages">
        <p class="no-messages">جارٍ تحميل الرسائل...</p>
    </div>

    <!-- ============================================ -->
    <!-- منطقة الإدخال + الإيموجي -->
    <!-- ============================================ -->
    <div class="input-area">
        <button class="emoji-btn" id="emojiBtn">😊</button>
        <input type="text" id="messageInput" placeholder="اكتب رسالتك...">
        <button onclick="sendMessage()">📤 إرسال</button>
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
                    var color = msg.sender_type === 'supplier' ? '#333' : '#fff';
                    html += '<div class="message ' + cls + '">';
                    html += '<div class="bubble" style="background:' + bg + '; color:' + color + ';">';
                    html += msg.message;
                    html += '<span class="time">' + (msg.time || '') + '</span>';
                    html += '</div></div>';
                }
                document.getElementById('chatMessages').innerHTML = html || '<p class="no-messages">لا توجد رسائل بعد</p>';
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
        } else {
            alert('فشل الإرسال: ' + (data.error || 'خطأ غير معروف'));
        }
    })
    .catch(err => {
        console.log('Error:', err);
        alert('خطأ في الاتصال');
    });
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