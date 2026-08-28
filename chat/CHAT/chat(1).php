<?php
require_once dirname(__DIR__) . '/lib/connect.php';
session_start();

$user_id = $_SESSION['uid_indm'] ?? 0;
if (!$user_id) {
    header('Location: /sign-in.php');
    exit;
}

$chat_code = isset($_GET['chat_code']) ? mysqli_real_escape_string($con, $_GET['chat_code']) : '';
if (!$chat_code) {
    die('معرف الشات غير صحيح');
}

// جلب بيانات الشات
$sql = "SELECT c.*, 
               p.pd_title as product_name,
               sup.bnsprof_comp_url as supplier_name,
               sup.bnsprof_uid as supplier_id,
               u.usr_id as buyer_id
        FROM chat_rooms c
        LEFT JOIN buy_requirement br ON c.rfq_id = br.br_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN business_profile sup ON c.supplier_id = sup.bnsprof_uid
        LEFT JOIN user u ON c.buyer_id = u.usr_id
        WHERE c.chat_code = '$chat_code'";
$res = mysqli_query($con, $sql);
$chat = mysqli_fetch_assoc($res);

if (!$chat) {
    die('الشات غير موجود');
}

// جلب عرض السعر
$quote_sql = "SELECT * FROM quotes WHERE quote_id = {$chat['quote_id']}";
$quote_res = mysqli_query($con, $quote_sql);
$quote = mysqli_fetch_assoc($quote_res);
?>
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>محادثة - <?php echo $chat['chat_code']; ?></title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/font-awesome.min.css" rel="stylesheet">
    <style>
    
    
    /* زر فتح مربع الإيموجي */
#emojiPickerBtn {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    padding: 8px 12px;
    transition: 0.2s;
}
#emojiPickerBtn:hover {
    transform: scale(1.1);
}

        .chat-container { max-width: 800px; margin: 20px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .chat-header { background: #25D366; color: white; padding: 15px; text-align: center; }
        .quote-info { background: #f9f9f9; padding: 15px; border-bottom: 1px solid #ddd; }
        .messages { padding: 20px; height: 400px; overflow-y: auto; background: #f5f5f5; }
        .message { margin-bottom: 15px; display: flex; }
        .message.supplier { justify-content: flex-start; }
        .message.buyer { justify-content: flex-end; }
        .bubble { max-width: 70%; padding: 10px 15px; border-radius: 18px; }
        .supplier .bubble { background: #e8f5e9; color: #333; }
        .buyer .bubble { background: #25D366; color: white; }
        .time { font-size: 11px; color: #999; margin-top: 5px; }
        .chat-input { padding: 15px; border-top: 1px solid #ddd; display: flex; gap: 10px; background: white; }
        .chat-input input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 25px; }
        .chat-input button { background: #25D366; color: white; border: none; padding: 10px 20px; border-radius: 25px; }
    
 
  
    
   </style>

</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        <i class="fa fa-whatsapp"></i> محادثة - <?php echo $chat['chat_code']; ?>
    </div>
    
    <div class="quote-info">
        <strong>المنتج:</strong> <?php echo htmlspecialchars($chat['product_name']); ?><br>
        <strong>RFQ #:</strong> <?php echo $chat['rfq_id']; ?><br>
        <?php if ($quote): ?>
        <strong>عرض السعر:</strong><br>
        السعر: <?php echo $quote['unit_price']; ?> USD<br>
        أقل كمية: <?php echo $quote['moq']; ?><br>
        مدة التوصيل: <?php echo htmlspecialchars($quote['delivery_time']); ?><br>
        رسالة المورد: <?php echo nl2br(htmlspecialchars($quote['supplier_message'])); ?>
        <?php endif; ?>
    </div>
    
    <div class="messages" id="messages"></div>
    
    <div class="chat-input">
        
    
    
        <button type="button" id="emojiPickerBtn" style="background:none; border:none; font-size:28px; cursor:pointer;">😊</button>
    <div id="emojiPicker" style="display:none; position:absolute; bottom:70px; left:20px; background:white; border:1px solid #ddd; border-radius:12px; padding:12px; width:280px; flex-wrap:wrap; gap:8px; z-index:1000;">
        <span style="font-size:24px; cursor:pointer; padding:6px;" onclick="addEmoji('😊')">😊</span>
        <span style="font-size:24px; cursor:pointer; padding:6px;" onclick="addEmoji('😂')">😂</span>
        <span style="font-size:24px; cursor:pointer; padding:6px;" onclick="addEmoji('❤️')">❤️</span>
        <span style="font-size:24px; cursor:pointer; padding:6px;" onclick="addEmoji('👍')">👍</span>
        <span style="font-size:24px; cursor:pointer; padding:6px;" onclick="addEmoji('🎉')">🎉</span>
        <span style="font-size:24px; cursor:pointer; padding:6px;" onclick="addEmoji('😢')">😢</span>
    </div>
    <input type="text" id="messageInput" placeholder="اكتب رسالتك..." style="flex:1; padding:10px;">
    <button onclick="sendMessage()">إرسال</button>
</div>
  
<script>
// انتظر تحميل الصفحة بالكامل
document.addEventListener('DOMContentLoaded', function() {
    // العناصر
    var emojiBtn = document.getElementById('emojiPickerBtn');
    var emojiPicker = document.getElementById('emojiPicker');
    var messageInput = document.getElementById('messageInput');
    
   // التأكد من وجود العناصر في الصفحة
    if (!emojiBtn || !emojiPicker) {
        console.log("Elements not found!");
        return;
    }
    
    // فتح وإغلاق مربع الإيموجي عند الضغط على الزر
    emojiBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (emojiPicker.style.display === 'flex') {
            emojiPicker.style.display = 'none';
        } else {
            emojiPicker.style.display = 'flex';
        }
    });
    
    // إغلاق المربع عند الضغط خارج الزر أو المربع
    document.addEventListener('click', function(e) {
        if (!emojiPicker.contains(e.target) && e.target !== emojiBtn) {
            emojiPicker.style.display = 'none';
        }
    });
});

// دالة إضافة الإيموجي إلى حقل النص
function addEmoji(emoji) {
    var input = document.getElementById('messageInput');
    if (input) {
        input.value += emoji;
        input.focus();
        // إغلاق المربع بعد الاختيار
        var picker = document.getElementById('emojiPicker');
        if (picker) picker.style.display = 'none';
    }
}
</script>



<script>
    
 
    
var chatId = <?php echo $chat['chat_id']; ?>;
var senderType = <?php echo ($user_id == $chat['supplier_id']) ? "'supplier'" : "'buyer'"; ?>;

function loadMessages() {
    fetch('/chat/ajax_chat.php?action=get&chat_id=' + chatId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                var html = '';
                data.messages.forEach(msg => {
                    html += '<div class="message ' + msg.sender_type + '">';
                    html += '<div class="bubble">' + msg.message.replace(/\n/g, '<br>');
                    html += '<div class="time">' + msg.time + '</div></div></div>';
                });
                document.getElementById('messages').innerHTML = html;
                document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
            }
        });
}

function sendMessage() {
    var msg = document.getElementById('messageInput').value;
    if (!msg.trim()) return;
    
    fetch('/chat/ajax_chat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=send&chat_id=' + chatId + '&message=' + encodeURIComponent(msg) + '&sender_type=' + senderType
    }).then(res => res.json()).then(data => {
        if (data.success) {
            document.getElementById('messageInput').value = '';
            loadMessages();
        }
    });
}


document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
});



setInterval(loadMessages, 5000);
loadMessages();
</script>

<script>
var chatId = <?php echo $chat['chat_id']; ?>;
var currentUserId = <?php echo $user_id; ?>;
var supplierId = <?php echo $chat['supplier_id']; ?>;
var buyerId = <?php echo $chat['buyer_id']; ?>;

var senderType = (currentUserId == supplierId) ? 'supplier' : 'buyer';
</script>


</body>
</html>