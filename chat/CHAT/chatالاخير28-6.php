<?php
/**
 * chat/chat.php - صفحة محادثة (نسخة مبسطة)
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/connect.php';

if (!isset($_SESSION['uid_indm'])) {
    die('يرجى تسجيل الدخول أولاً');
}

$current_user_id = (int)$_SESSION['uid_indm'];

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
// جلب بيانات الشات
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
            sp.lname as supplier_lname,
            sp.mobile1 as supplier_phone
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
$supplier_id = (int)$chat['supplier_id'];
$buyer_id = (int)$chat['buyer_id'];

// جلب عروض الأسعار
$offers = [];
$offers_sql = "SELECT * FROM offers WHERE rfq_id = $rfq_id ORDER BY created_at DESC";
$offers_res = mysqli_query($con, $offers_sql);
while ($offer = mysqli_fetch_assoc($offers_res)) {
    $offers[] = $offer;
}

// ============================================================
// جلب الرسائل - ✅ استخدم sender_id
// ============================================================
$messages = [];
$msg_sql = "SELECT sender_id, message, 
            DATE_FORMAT(created_at, '%H:%i') as time 
            FROM chat_messages 
            WHERE chat_id = {$chat['chat_id']} 
            ORDER BY created_at ASC";
$msg_res = mysqli_query($con, $msg_sql);
while ($msg = mysqli_fetch_assoc($msg_res)) {
    $messages[] = $msg;
}

$buyer_fullname = $chat['buyer_fname'] . ' ' . $chat['buyer_lname'];
$supplier_fullname = $chat['supplier_fname'] . ' ' . $chat['supplier_lname'];
?>
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>المحادثة - طلب شراء #<?php echo $rfq_id; ?></title>
    <style>
        /* ... نفس الـ Style السابق ... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; background: #e5ddd5; display: flex; flex-direction: column; direction: rtl; }
        .container { max-width: 800px; width: 100%; margin: 0 auto; background: #fff; height: 100vh; max-height: 100vh; display: flex; flex-direction: column; box-shadow: 0 0 20px rgba(0,0,0,0.1); overflow: hidden; }
        .chat-header { background: #075e54; color: #fff; padding: 8px 14px; display: flex; flex-wrap: wrap; align-items: center; gap: 3px 10px; font-size: 11px; flex-shrink: 0; border-bottom: 1px solid #054740; min-height: 50px; }
        .chat-header .item { display: flex; align-items: center; gap: 3px; }
        .chat-header .label { opacity: 0.7; }
        .chat-header .value { font-weight: 600; }
        .chat-header .badge { background: rgba(255,255,255,0.12); padding: 1px 10px; border-radius: 12px; font-size: 10px; }
        .offer-ticket { background: #dcf8c6; border-radius: 12px; padding: 6px 12px; margin: 4px 10px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 3px 10px; border: 1px solid #b9e6a8; flex-shrink: 0; min-height: 40px; }
        .offer-ticket .title { color: #075e54; font-weight: 700; font-size: 12px; display: flex; align-items: center; gap: 5px; }
        .offer-ticket .details { display: flex; flex-wrap: wrap; gap: 3px 10px; font-size: 11px; }
        .offer-ticket .details .label { color: #555; }
        .offer-ticket .details .value { font-weight: 600; color: #333; }
        .offer-ticket .price-value { color: #075e54; font-size: 13px; font-weight: 700; }
        .offer-ticket .update-badge { background: #25d366; color: #fff; padding: 1px 10px; border-radius: 20px; font-size: 10px; font-weight: 600; }
        .offer-ticket .update-limit { background: #dc3545; color: #fff; padding: 1px 10px; border-radius: 20px; font-size: 10px; font-weight: 600; }
        .chat-messages { flex: 1 1 auto; overflow-y: auto; padding: 10px 12px; background: #e5ddd5; display: flex; flex-direction: column; gap: 4px; min-height: 0; }
        .message.outgoing { align-self: flex-end; max-width: 80%; background: #dcf8c6; color: #303030; padding: 8px 14px; border-radius: 12px; border-top-right-radius: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.08); word-wrap: break-word; direction: rtl; text-align: right; }
        .message.incoming { align-self: flex-start; max-width: 80%; background: #ffffff; color: #303030; padding: 8px 14px; border-radius: 12px; border-top-left-radius: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.08); word-wrap: break-word; direction: ltr; text-align: left; }
        .message .sender-name { font-size: 11px; font-weight: 700; margin-bottom: 3px; display: block; opacity: 0.8; }
        .outgoing .sender-name { color: #075e54; }
        .incoming .sender-name { color: #555; }
        .message .time { font-size: 10px; opacity: 0.6; margin-top: 3px; display: block; }
        .outgoing .time { color: #555; }
        .incoming .time { color: #888; }
        .no-messages { text-align: center; color: #888; padding: 30px; align-self: center; }
        .input-area { padding: 8px 12px; background: #f0f0f0; border-top: 1px solid #ddd; display: flex; gap: 6px; align-items: center; flex-shrink: 0; min-height: 60px; }
        .input-area input { flex: 1; padding: 10px 14px; border: none; border-radius: 25px; outline: none; font-size: 14px; background: #fff; direction: rtl; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .input-area input:focus { box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .input-area button { background: #25d366; color: #fff; border: none; padding: 10px 18px; border-radius: 25px; cursor: pointer; font-size: 14px; font-weight: 600; white-space: nowrap; transition: background 0.2s; }
        .input-area button:hover { background: #1da851; }
        .emoji-btn { background: none; border: none; font-size: 24px; cursor: pointer; padding: 0 6px; line-height: 1; }
        .emoji-popup { display: none; position: fixed; background: #fff; border-radius: 12px; padding: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 999; width: 270px; flex-wrap: wrap; gap: 4px; }
        .emoji-popup.active { display: flex; }
        .emoji-popup span { font-size: 28px; cursor: pointer; padding: 4px 6px; border-radius: 6px; transition: background 0.15s; }
        .emoji-popup span:hover { background: #f0f0f0; }
        @media (max-width: 600px) { .chat-header { font-size: 10px; padding: 6px 10px; gap: 2px 6px; } .chat-messages { padding: 8px 10px; } .message { max-width: 85%; padding: 6px 12px; font-size: 13px; } .offer-ticket { padding: 5px 8px; flex-direction: column; align-items: stretch; gap: 3px; } .input-area { padding: 6px 10px; } .input-area input { padding: 8px 12px; font-size: 13px; } .input-area button { padding: 8px 14px; font-size: 13px; } .emoji-popup { width: 220px; } .emoji-popup span { font-size: 24px; } }
        @media (max-width: 400px) { .chat-header { font-size: 9px; padding: 4px 6px; gap: 2px 4px; } .chat-messages { padding: 4px; } .message { max-width: 90%; padding: 4px 10px; font-size: 12px; } .offer-ticket { font-size: 10px; padding: 4px 6px; } .input-area { flex-wrap: wrap; } .input-area input { min-width: 100%; } }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="chat-header">
        <span class="item"><span class="label">📦 طلب:</span><span class="value">#<?php echo $rfq_id; ?></span></span>
        <span class="item"><span class="label">🏢 الشركة:</span><span class="value"><?php echo htmlspecialchars($chat['buyer_company'] ?? 'غير محدد'); ?></span></span>
        <span class="item"><span class="label">👤 المشتري:</span><span class="value"><?php echo htmlspecialchars($buyer_fullname); ?></span></span>
        <span class="item"><span class="label">📞 هاتف:</span><span class="value"><?php echo $chat['buyer_phone']; ?></span></span>
        <span class="item"><span class="label">📦 منتج:</span><span class="value"><?php echo htmlspecialchars($chat['product_name']); ?></span></span>
        <span class="item"><span class="label">📊 كمية:</span><span class="value"><?php echo $chat['br_estimate_qty'] . ' ' . $chat['br_estimate_qty_unit']; ?></span></span>
        <span class="item badge">📅 <?php echo date('Y-m-d', strtotime($chat['br_posting_date'])); ?></span>
    </div>

    <!-- تذكرة عرض السعر -->
    <div class="offer-ticket" id="offerTicket" style="display:none;">
        <div class="title">💰 عرض السعر <span class="update-badge" id="offerBadge">عرض سعر أولي</span></div>
        <div class="details">
            <span><span class="label">السعر:</span> <span class="price-value" id="offerPrice">0 USD</span></span>
            <span><span class="label">مدة التوصيل:</span> <span class="value" id="offerDelivery">0 يوم</span></span>
            <span><span class="label">الملاحظات:</span> <span class="value" id="offerNotes">لا توجد</span></span>
        </div>
    </div>

    <!-- نافذة المحادثة -->
    <div class="chat-messages" id="messages">
        <?php if (empty($messages)): ?>
            <p class="no-messages">لا توجد رسائل بعد</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): 
                $isOutgoing = ((int)$msg['sender_id'] === $current_user_id);
                $containerClass = $isOutgoing ? 'message outgoing' : 'message incoming';
                
                if ($isOutgoing) {
                    $senderName = 'أنت';
                } else {
                    if ((int)$msg['sender_id'] === $supplier_id) {
                        $senderName = $supplier_fullname;
                    } else {
                        $senderName = $buyer_fullname;
                    }
                }
            ?>
                <div class="<?php echo $containerClass; ?>">
                    <span class="sender-name"><?php echo htmlspecialchars($senderName); ?></span>
                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    <span class="time"><?php echo $msg['time']; ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- منطقة الإدخال -->
    <div class="input-area">
        <button class="emoji-btn" id="emojiBtn">😊</button>
        <input type="text" id="messageInput" placeholder="اكتب رسالتك...">
        <button onclick="sendMessage()">📤 إرسال</button>
    </div>

    <!-- نافذة الإيموجي -->
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
var chatId = <?php echo (int)$chat['chat_id']; ?>;
var currentUserId = <?php echo (int)$current_user_id; ?>;
var supplierId = <?php echo (int)$supplier_id; ?>;
var buyerId = <?php echo (int)$buyer_id; ?>;
var buyerFullname = '<?php echo addslashes($buyer_fullname); ?>';
var supplierFullname = '<?php echo addslashes($supplier_fullname); ?>';
var offers = <?php echo json_encode($offers); ?>;

function updateOfferTicket(offer) {
    var ticket = document.getElementById('offerTicket');
    if (!offer || offer.price <= 0) { ticket.style.display = 'none'; return; }
    ticket.style.display = 'flex';
    document.getElementById('offerPrice').textContent = offer.price + ' ' + (offer.currency || 'USD');
    document.getElementById('offerDelivery').textContent = offer.delivery_days + ' يوم';
    document.getElementById('offerNotes').textContent = offer.notes || 'لا توجد';
    var badge = document.getElementById('offerBadge');
    var count = offer.update_count || 0;
    if (count == 0) { badge.textContent = 'عرض سعر أولي'; badge.className = 'update-badge'; }
    else if (count == 1) { badge.textContent = 'تحديث (مرة أولى)'; badge.className = 'update-badge'; }
    else if (count >= 2) { badge.textContent = '⚠️ آخر تحديث'; badge.className = 'update-limit'; }
}

function displayMessages(messages) {
    var box = document.getElementById('messages');
    box.innerHTML = '';
    if (!messages || messages.length === 0) {
        box.innerHTML = '<p class="no-messages">لا توجد رسائل بعد</p>';
        return;
    }
    for (var i = 0; i < messages.length; i++) {
        var msg = messages[i];
        var isOutgoing = (parseInt(msg.sender_id) === currentUserId);
        var containerClass = isOutgoing ? 'message outgoing' : 'message incoming';
        var senderName;
        if (isOutgoing) {
            senderName = 'أنت';
        } else {
            if (parseInt(msg.sender_id) === supplierId) {
                senderName = supplierFullname;
            } else {
                senderName = buyerFullname;
            }
        }
        var div = document.createElement('div');
        div.className = containerClass;
        div.innerHTML = '<span class="sender-name">' + senderName + '</span>' + msg.message + '<span class="time">' + msg.time + '</span>';
        box.appendChild(div);
    }
    box.scrollTop = box.scrollHeight;
}

function addNewMessage(msg, senderId, time) {
    var box = document.getElementById('messages');
    var noMsg = box.querySelector('.no-messages');
    if (noMsg) noMsg.remove();
    var isOutgoing = (parseInt(senderId) === currentUserId);
    var containerClass = isOutgoing ? 'message outgoing' : 'message incoming';
    var senderName;
    if (isOutgoing) {
        senderName = 'أنت';
    } else {
        if (parseInt(senderId) === supplierId) {
            senderName = supplierFullname;
        } else {
            senderName = buyerFullname;
        }
    }
    var div = document.createElement('div');
    div.className = containerClass;
    div.innerHTML = '<span class="sender-name">' + senderName + '</span>' + msg + '<span class="time">' + time + '</span>';
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}

function sendMessage() {
    var input = document.getElementById('messageInput');
    var msg = input ? input.value : '';
    if (!msg.trim()) return;
    fetch('/chat/ajax_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=send&chat_id=' + chatId + '&message=' + encodeURIComponent(msg)
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            input.value = '';
            var now = new Date();
            var time = now.toLocaleTimeString('ar-EG', {hour: '2-digit', minute: '2-digit'});
            addNewMessage(msg, currentUserId, time);
            if (offers.length > 0) updateOfferTicket(offers[0]);
        } else {
            alert('فشل الإرسال: ' + (data.error || 'خطأ غير معروف'));
        }
    })
    .catch(function(err) {
        console.error('Error:', err);
        alert('خطأ في الاتصال: ' + err.message);
    });
}

function loadMessages() {
    if (!chatId) return;
    fetch('/chat/ajax_chat.php?action=get&chat_id=' + chatId)
    .then(function(response) {
        if (!response.ok) {
            throw new Error('HTTP error ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            displayMessages(data.messages);
        }
        if (offers.length > 0) updateOfferTicket(offers[0]);
    })
    .catch(function(err) {
        console.error('Error loading messages:', err);
        // محاولة إعادة التحميل بعد 5 ثواني
        setTimeout(loadMessages, 5000);
    });
}

function addEmoji(emoji) {
    var input = document.getElementById('messageInput');
    if (input) {
        input.value += emoji;
        input.focus();
        document.getElementById('emojiPopup').classList.remove('active');
    }
}

document.getElementById('emojiBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    var popup = document.getElementById('emojiPopup');
    popup.classList.toggle('active');
    var rect = this.getBoundingClientRect();
    var w = 270;
    var left = rect.left - (w / 2) + 20;
    if (left < 10) left = 10;
    if (left + w > window.innerWidth - 10) left = window.innerWidth - w - 10;
    popup.style.bottom = (window.innerHeight - rect.top + 10) + 'px';
    popup.style.left = left + 'px';
});

document.addEventListener('click', function(e) {
    var popup = document.getElementById('emojiPopup');
    var btn = document.getElementById('emojiBtn');
    if (!popup.contains(e.target) && e.target !== btn) {
        popup.classList.remove('active');
    }
});

document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    loadMessages();
});
</script>

</body>
</html>