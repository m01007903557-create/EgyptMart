<?php
/**
 * chat/chat.php - صفحة محادثة متكاملة
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
            bp.bnsprof_compname as buyer_company,
            sp.fname as supplier_fname, 
            sp.lname as supplier_lname
        FROM chat_rooms cr
        LEFT JOIN buy_requirement br ON cr.rfq_id = br.br_id
        LEFT JOIN user u ON cr.buyer_id = u.usr_id
        LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        LEFT JOIN user sp ON cr.supplier_id = sp.usr_id
        WHERE cr.chat_code = '$chat_code'";

$result = mysqli_query($con, $sql);
$chat = mysqli_fetch_assoc($result);

if (!$chat) {
    die('خطأ: الشات غير موجود');
}

$rfq_id = $chat['rfq_id'];

// جلب عروض الأسعار للتحديثات
$offers = [];
$offers_sql = "SELECT * FROM offers WHERE rfq_id = $rfq_id ORDER BY created_at DESC";
$offers_res = mysqli_query($con, $offers_sql);
while ($offer = mysqli_fetch_assoc($offers_res)) {
    $offers[] = $offer;
}
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
        
        .container { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
        
        /* ============================================ */
        /* تذكرة طلب الشراء (أفقية) */
        /* ============================================ */
        .rfq-ticket {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 12px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 30px;
            align-items: center;
            font-size: 13px;
        }
        .rfq-ticket .item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .rfq-ticket .label { opacity: 0.7; font-weight: normal; }
        .rfq-ticket .value { font-weight: 600; }
        .rfq-ticket .badge {
            background: rgba(255,255,255,0.2);
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
        }
        
        /* ============================================ */
        /* نافذة المحادثة */
        /* ============================================ */
        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .message {
            max-width: 75%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
        }
        .message.supplier {
            align-self: flex-start;
            background: #e8f5e9;
            color: #333;
            border-bottom-left-radius: 5px;
        }
        .message.buyer {
            align-self: flex-end;
            background: #25D366;
            color: #fff;
            border-bottom-right-radius: 5px;
        }
        .message .time {
            font-size: 10px;
            opacity: 0.7;
            margin-top: 4px;
            display: block;
        }
        .message .sender-name {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 3px;
            display: block;
        }
        .buyer .sender-name { color: #fff; }
        .supplier .sender-name { color: #2e7d32; }
        
        /* تحديثات السعر في المحادثة */
        .price-update {
            align-self: center;
            background: #fff3cd;
            color: #856404;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            border: 1px solid #ffc107;
            max-width: 90%;
            text-align: center;
        }
        
        /* ============================================ */
        /* منطقة الإدخال */
        /* ============================================ */
        .input-area {
            padding: 12px 15px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 8px;
            background: #fff;
            align-items: center;
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
            white-space: nowrap;
        }
        .input-area button:hover { background: #1da851; }
        
        .emoji-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 0 8px;
        }
        
        /* ============================================ */
        /* نافذة الإيموجي المنبثقة (Popup) */
        /* ============================================ */
        .emoji-popup {
            display: none;
            position: fixed;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            z-index: 999;
            width: 280px;
            flex-wrap: wrap;
            gap: 6px;
        }
        .emoji-popup.active { display: flex; }
        .emoji-popup span {
            font-size: 28px;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .emoji-popup span:hover { background: #f0f0f0; }
        
        /* ============================================ */
        /* رسائل النظام */
        /* ============================================ */
        .system-msg {
            align-self: center;
            color: #999;
            font-size: 12px;
            padding: 4px 12px;
            background: #f0f0f0;
            border-radius: 12px;
        }
        .no-messages { text-align: center; color: #999; padding: 20px; align-self: center; }
        
        /* ============================================ */
        /* تصميم متجاوب */
        /* ============================================ */
        @media (max-width: 600px) {
            .rfq-ticket { font-size: 11px; padding: 8px 12px; gap: 5px 15px; }
            .message { max-width: 85%; }
            .input-area { flex-wrap: wrap; }
            .input-area input { min-width: 120px; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- ============================================ -->
    <!-- تذكرة طلب الشراء (أفقية) -->
    <!-- ============================================ -->
    <div class="rfq-ticket">
        <span class="item"><span class="label">📦 طلب:</span><span class="value">#<?php echo $rfq_id; ?></span></span>
        <span class="item"><span class="label">🏢 الشركة:</span><span class="value"><?php echo htmlspecialchars($chat['buyer_company'] ?? 'غير محدد'); ?></span></span>
        <span class="item"><span class="label">👤 المشتري:</span><span class="value"><?php echo $chat['buyer_fname'] . ' ' . $chat['buyer_lname']; ?></span></span>
        <span class="item"><span class="label">📞 الهاتف:</span><span class="value"><?php echo $chat['buyer_phone']; ?></span></span>
        <span class="item"><span class="label">📧 البريد:</span><span class="value"><?php echo $chat['buyer_email']; ?></span></span>
        <span class="item"><span class="label">📦 المنتج:</span><span class="value"><?php echo htmlspecialchars($chat['product_name']); ?></span></span>
        <span class="item"><span class="label">📊 الكمية:</span><span class="value"><?php echo $chat['br_estimate_qty'] . ' ' . $chat['br_estimate_qty_unit']; ?></span></span>
        <span class="item badge">📅 <?php echo date('Y-m-d', strtotime($chat['br_posting_date'])); ?></span>
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
    
    <!-- ============================================ -->
    <!-- نافذة الإيموجي المنبثقة -->
    <!-- ============================================ -->
    <div class="emoji-popup" id="emojiPopup">
        <span onclick="addEmoji('😊')">😊</span>
        <span onclick="addEmoji('😂')">😂</span>
        <span onclick="addEmoji('❤️')">❤️</span>
        <span onclick="addEmoji('👍')">👍</span>
        <span onclick="addEmoji('🎉')">🎉</span>
        <span onclick="addEmoji('😢')">😢</span>
        <span onclick="addEmoji('🔥')">🔥</span>
        <span onclick="addEmoji('✅')">✅</span>
        <span onclick="addEmoji('⭐')">⭐</span>
        <span onclick="addEmoji('🙏')">🙏</span>
        <span onclick="addEmoji('💪')">💪</span>
        <span onclick="addEmoji('🤝')">🤝</span>
        <span onclick="addEmoji('👋')">👋</span>
        <span onclick="addEmoji('😎')">😎</span>
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
var senderName = (senderType == 'supplier') ? '<?php echo addslashes($chat['supplier_fname'] . ' ' . $chat['supplier_lname']); ?>' : '<?php echo addslashes($chat['buyer_fname'] . ' ' . $chat['buyer_lname']); ?>';

// عروض الأسعار للتحديثات
var offers = <?php echo json_encode($offers); ?>;

// ============================================================
// عرض تحديثات السعر في المحادثة
// ============================================================
function displayPriceUpdates() {
    var chatBox = document.getElementById('chatMessages');
    var existingUpdates = chatBox.querySelectorAll('.price-update');
    existingUpdates.forEach(function(el) { el.remove(); });
    
    if (offers.length > 1) {
        for (var i = 0; i < offers.length - 1; i++) {
            var offer = offers[i];
            if (i === 0 && offer.update_count > 0) {
                // التحديث الأول
                var div = document.createElement('div');
                div.className = 'price-update';
                div.textContent = '💰 تم تحديث السعر إلى ' + offer.price + ' ' + offer.currency + ' (التحديث الأول)';
                chatBox.appendChild(div);
            } else if (offer.update_count > 1) {
                var div2 = document.createElement('div');
                div2.className = 'price-update';
                div2.textContent = '💰 تم تحديث السعر إلى ' + offer.price + ' ' + offer.currency + ' (التحديث الأخير)';
                chatBox.appendChild(div2);
            }
        }
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

// ============================================================
// تحميل الرسائل
// ============================================================
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
                    var name = msg.sender_type === 'supplier' ? '<?php echo addslashes($chat['supplier_fname']); ?>' : '<?php echo addslashes($chat['buyer_fname']); ?>';
                    var time = msg.time || '';
                    html += '<div class="message ' + cls + '">';
                    html += '<span class="sender-name">' + name + '</span>';
                    html += msg.message;
                    html += '<span class="time">' + time + '</span>';
                    html += '</div>';
                }
                var chatBox = document.getElementById('chatMessages');
                // إزالة رسالة "لا توجد رسائل"
                var noMsg = chatBox.querySelector('.no-messages');
                if (noMsg) noMsg.remove();
                
                // إضافة الرسائل
                var existingMessages = chatBox.querySelectorAll('.message, .price-update, .system-msg');
                existingMessages.forEach(function(el) { el.remove(); });
                
                if (html) {
                    chatBox.innerHTML = html;
                } else {
                    chatBox.innerHTML = '<p class="no-messages">لا توجد رسائل بعد</p>';
                }
                displayPriceUpdates();
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        })
        .catch(err => console.log('Error:', err));
}

// ============================================================
// إرسال رسالة
// ============================================================
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

// ============================================================
// إضافة إيموجي
// ============================================================
function addEmoji(emoji) {
    var input = document.getElementById('messageInput');
    if (input) {
        input.value += emoji;
        input.focus();
        document.getElementById('emojiPopup').classList.remove('active');
    }
}

// ============================================================
// التحكم في نافذة الإيموجي المنبثقة
// ============================================================
document.getElementById('emojiBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    var popup = document.getElementById('emojiPopup');
    popup.classList.toggle('active');
    
    // تحديد موقع النافذة بجانب الزر
    var rect = this.getBoundingClientRect();
    popup.style.bottom = (window.innerHeight - rect.top + 10) + 'px';
    popup.style.left = (rect.left - 120) + 'px';
});

// إغلاق نافذة الإيموجي عند النقر خارجها
document.addEventListener('click', function(e) {
    var popup = document.getElementById('emojiPopup');
    var btn = document.getElementById('emojiBtn');
    if (!popup.contains(e.target) && e.target !== btn) {
        popup.classList.remove('active');
    }
});

// ============================================================
// إرسال بالضغط على Enter
// ============================================================
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
});

// ============================================================
// تحميل الرسائل عند فتح الصفحة
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    loadMessages();
    setInterval(loadMessages, 5000);
});
</script>

</body>
</html>