<?php
/**
 * chat/chat.php - صفحة محادثة Two-Sided Chat
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
            c.cn_name as country_name,
            sp.fname as supplier_fname, 
            sp.lname as supplier_lname,
            sp.mobile1 as supplier_phone
        FROM chat_rooms cr
        LEFT JOIN buy_requirement br ON cr.rfq_id = br.br_id
        LEFT JOIN user u ON cr.buyer_id = u.usr_id
        LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        LEFT JOIN country c ON u.country = c.cn_id
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
// جلب الرسائل مع تحديد sender_type
// ============================================================
$messages = [];
$msg_sql = "SELECT * FROM chat_messages WHERE chat_id = {$chat['chat_id']} ORDER BY created_at ASC";
$msg_res = mysqli_query($con, $msg_sql);
while ($msg = mysqli_fetch_assoc($msg_res)) {
    $messages[] = $msg;
}
?>
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>المحادثة - طلب شراء #<?php echo $rfq_id; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #e5ddd5;
            display: flex;
            flex-direction: column;
            direction: rtl;
        }

        .container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            background: #fff;
            height: 100vh;
            max-height: 100vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* ============================================ */
        /* Header */
        /* ============================================ */
        .chat-header {
            background: #075e54;
            color: #fff;
            padding: 8px 14px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 3px 10px;
            font-size: 11px;
            flex-shrink: 0;
            border-bottom: 1px solid #054740;
            min-height: 50px;
        }
        .chat-header .item { display: flex; align-items: center; gap: 3px; }
        .chat-header .label { opacity: 0.7; }
        .chat-header .value { font-weight: 600; }
        .chat-header .badge {
            background: rgba(255,255,255,0.12);
            padding: 1px 10px;
            border-radius: 12px;
            font-size: 10px;
        }

        /* ============================================ */
        /* تذكرة عرض السعر */
        /* ============================================ */
        .offer-ticket {
            background: #dcf8c6;
            border-radius: 12px;
            padding: 6px 12px;
            margin: 4px 10px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 3px 10px;
            border: 1px solid #b9e6a8;
            flex-shrink: 0;
            min-height: 40px;
        }
        .offer-ticket .title {
            color: #075e54;
            font-weight: 700;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .offer-ticket .details {
            display: flex;
            flex-wrap: wrap;
            gap: 3px 10px;
            font-size: 11px;
        }
        .offer-ticket .details .label { color: #555; }
        .offer-ticket .details .value { font-weight: 600; color: #333; }
        .offer-ticket .price-value { color: #075e54; font-size: 13px; font-weight: 700; }
        .offer-ticket .update-badge {
            background: #25d366;
            color: #fff;
            padding: 1px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }
        .offer-ticket .update-limit {
            background: #dc3545;
            color: #fff;
            padding: 1px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
        }

        /* ============================================ */
        /* نافذة المحادثة - Two-Sided Chat */
        /* ============================================ */
        .chat-messages {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 10px 12px;
            background: #e5ddd5;
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-height: 0;
        }

       /* ============================================ */
/* OUTGOING - رسائل المستخدم الحالي (على اليمين) */
/* ============================================ */
.message.outgoing {
    align-self: flex-end;
    max-width: 80%;
    background: #dcf8c6;
    color: #303030;
    padding: 8px 14px;
    border-radius: 12px;
    border-top-right-radius: 0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    word-wrap: break-word;
    direction: rtl;
    text-align: right;
}

/* ============================================ */
/* INCOMING - رسائل الطرف الآخر (على اليسار) */
/* ============================================ */
.message.incoming {
    align-self: flex-start;
    max-width: 80%;
    background: #ffffff;
    color: #303030;
    padding: 8px 14px;
    border-radius: 12px;
    border-top-left-radius: 0;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
    word-wrap: break-word;
    direction: ltr;
    text-align: left;
}
        /* اسم المرسل */
        .message .sender-name {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 3px;
            display: block;
            opacity: 0.8;
        }
        .outgoing .sender-name { color: #075e54; }
        .incoming .sender-name { color: #555; }

        /* وقت الرسالة */
        .message .time {
            font-size: 10px;
            opacity: 0.6;
            margin-top: 3px;
            display: block;
        }
        .outgoing .time { color: #555; }
        .incoming .time { color: #888; }

        .no-messages {
            text-align: center;
            color: #888;
            padding: 30px;
            align-self: center;
        }

        /* ============================================ */
        /* منطقة الإدخال */
        /* ============================================ */
        .input-area {
            padding: 8px 12px;
            background: #f0f0f0;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 6px;
            align-items: center;
            flex-shrink: 0;
            min-height: 60px;
        }
        .input-area input {
            flex: 1;
            padding: 10px 14px;
            border: none;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            background: #fff;
            direction: rtl;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .input-area input:focus { box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
        .input-area button {
            background: #25d366;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .input-area button:hover { background: #1da851; }

        .emoji-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 0 6px;
            line-height: 1;
        }

        /* ============================================ */
        /* نافذة الإيموجي المنبثقة */
        /* ============================================ */
        .emoji-popup {
            display: none;
            position: fixed;
            background: #fff;
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 999;
            width: 270px;
            flex-wrap: wrap;
            gap: 4px;
        }
        .emoji-popup.active { display: flex; }
        .emoji-popup span {
            font-size: 28px;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 6px;
            transition: background 0.15s;
        }
        .emoji-popup span:hover { background: #f0f0f0; }

        /* ============================================ */
        /* متجاوب */
        /* ============================================ */
        @media (max-width: 600px) {
            .chat-header { font-size: 10px; padding: 6px 10px; gap: 2px 6px; }
            .chat-messages { padding: 8px 10px; }
            .message { max-width: 85%; padding: 6px 12px; font-size: 13px; }
            .offer-ticket { padding: 5px 8px; flex-direction: column; align-items: stretch; gap: 3px; }
            .input-area { padding: 6px 10px; }
            .input-area input { padding: 8px 12px; font-size: 13px; }
            .input-area button { padding: 8px 14px; font-size: 13px; }
            .emoji-popup { width: 220px; }
            .emoji-popup span { font-size: 24px; }
        }

        @media (max-width: 400px) {
            .chat-header { font-size: 9px; padding: 4px 6px; gap: 2px 4px; }
            .chat-messages { padding: 4px; }
            .message { max-width: 90%; padding: 4px 10px; font-size: 12px; }
            .offer-ticket { font-size: 10px; padding: 4px 6px; }
            .input-area { flex-wrap: wrap; }
            .input-area input { min-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="chat-header">
        <span class="item"><span class="label">📦 طلب:</span><span class="value">#<?php echo $rfq_id; ?></span></span>
        <span class="item"><span class="label">🏢 الشركة:</span><span class="value"><?php echo htmlspecialchars($chat['buyer_company'] ?? 'غير محدد'); ?></span></span>
        <span class="item"><span class="label">👤 المشتري:</span><span class="value"><?php echo $chat['buyer_fname'] . ' ' . $chat['buyer_lname']; ?></span></span>
        <span class="item"><span class="label">📞 هاتف:</span><span class="value"><?php echo $chat['buyer_phone']; ?></span></span>
        <span class="item"><span class="label">📧 بريد:</span><span class="value"><?php echo $chat['buyer_email']; ?></span></span>
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

    <!-- ============================================ -->
    <!-- نافذة المحادثة - Two-Sided Chat -->
    <!-- ============================================ -->
    <div class="chat-messages" id="messages">
        <?php if (empty($messages)): ?>
            <p class="no-messages">لا توجد رسائل بعد</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): 
                // ✅ الشرط الأساسي: مقارنة sender_id مع current_user_id
                $isOutgoing = ((int)$msg['sender_id'] === (int)$_SESSION['uid_indm']);

                // ✅ تحديد الكلاسات حسب نوع الرسالة
                $containerClass = $isOutgoing ? 'message outgoing' : 'message incoming';

                // ✅ اسم المرسل
                if ($isOutgoing) {
                    $senderName = 'أنت';
                } else {
                    // تحديد اسم الطرف الآخر من بيانات الشات
                    if ($msg['sender_type'] === 'supplier') {
                        $senderName = $chat['supplier_fname'] ?? 'المورد';
                    } else {
                        $senderName = $chat['buyer_fname'] ?? 'المشتري';
                    }
                }
            ?>
                <div class="<?php echo $containerClass; ?>">
                    <span class="sender-name"><?php echo htmlspecialchars($senderName); ?></span>
                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    <span class="time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
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
// ============================================================
// دوال المحادثة - Two-Sided Chat
// ============================================================
var chatId = <?php echo (int)$chat['chat_id']; ?>;
var currentUserId = <?php echo (int)$current_user_id; ?>;
var supplierId = <?php echo (int)$supplier_id; ?>;
var buyerId = <?php echo (int)$buyer_id; ?>;
var senderType = (currentUserId == supplierId) ? 'supplier' : 'buyer';

var offers = <?php echo json_encode($offers); ?>;

// تحديث تذكرة عرض السعر
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

// ============================================================
// تحميل الرسائل - Two-Sided Chat (مع تحديث تدريجي)
// ============================================================
// ============================================================
// تحميل الرسائل - Two-Sided Chat (ثابت)
// ============================================================
function loadMessages() {
    if (!chatId) return;
    fetch('/chat/ajax_chat.php?action=get&chat_id=' + chatId)
        .then(res => res.json())
        .then(data => {
            var box = document.getElementById('messages');

            // إذا كانت الصفحة فارغة (أول تحميل)
            if (box.children.length === 0 || box.querySelector('.no-messages')) {
                box.innerHTML = '';
                if (data.success && data.messages) {
                    // ✅ استخدام appendMessages لضمان التنسيق الصحيح
                    appendMessages(data.messages, box);
                } else {
                    box.innerHTML = '<p class="no-messages">لا توجد رسائل بعد</p>';
                }
                box.scrollTop = box.scrollHeight;
                return;
            }

            // ✅ تحديث تدريجي: إضافة الرسائل الجديدة فقط
            if (data.success && data.messages) {
                var existingMsgs = box.querySelectorAll('.message');
                var existingCount = existingMsgs.length;

                if (data.messages.length > existingCount) {
                    // إضافة الرسائل الجديدة فقط
                    var newMessages = data.messages.slice(existingCount);
                    // ✅ استخدام appendMessages لضمان التنسيق الصحيح
                    appendMessages(newMessages, box);
                    box.scrollTop = box.scrollHeight;
                }
            }

            // تحديث تذكرة العرض
            if (offers.length > 0) updateOfferTicket(offers[0]);
        })
        .catch(err => console.log('Error:', err));
}

// ============================================================
// دالة مساعدة لإضافة الرسائل مع التنسيق الصحيح (Two-Sided)
// ============================================================
function appendMessages(messages, box) {
    var currentUserId = <?php echo (int)$_SESSION['uid_indm']; ?>;
    var supplierName = '<?php echo addslashes($chat['supplier_fname']); ?>';
    var buyerName = '<?php echo addslashes($chat['buyer_fname']); ?>';

    for (var i = 0; i < messages.length; i++) {
        var msg = messages[i];
        
        // ✅ تحديد نوع الرسالة بناءً على sender_id
        var isOutgoing = (parseInt(msg.sender_id) === currentUserId);
        
        // ✅ تحديد الكلاس المناسب
        var cls = isOutgoing ? 'outgoing' : 'incoming';
        
        // ✅ تحديد اسم المرسل
        var senderName;
        if (isOutgoing) {
            senderName = 'أنت';
        } else {
            if (msg.sender_type === 'supplier') {
                senderName = supplierName;
            } else {
                senderName = buyerName;
            }
        }
        
        var time = msg.time || '';

        var div = document.createElement('div');
        div.className = 'message ' + cls;
        div.innerHTML = '<span class="sender-name">' + senderName + '</span>' +
                       msg.message +
                       '<span class="time">' + time + '</span>';
        box.appendChild(div);
    }
}
// ============================================================
// دالة مساعدة لإضافة الرسائل مع التنسيق الصحيح
// ============================================================
function appendMessages(messages, box) {
    var currentUserId = <?php echo (int)$_SESSION['uid_indm']; ?>;
    var supplierName = '<?php echo addslashes($chat['supplier_fname']); ?>';
    var buyerName = '<?php echo addslashes($chat['buyer_fname']); ?>';

    for (var i = 0; i < messages.length; i++) {
        var msg = messages[i];
        
        // ✅ تحديد نوع الرسالة بناءً على sender_id
        var isOutgoing = (parseInt(msg.sender_id) === currentUserId);
        
        // ✅ تحديد الكلاس المناسب
        var cls = isOutgoing ? 'outgoing' : 'incoming';
        
        // ✅ تحديد اسم المرسل
        var senderName;
        if (isOutgoing) {
            senderName = 'أنت';
        } else {
            if (msg.sender_type === 'supplier') {
                senderName = supplierName;
            } else {
                senderName = buyerName;
            }
        }
        
        var time = msg.time || '';

        var div = document.createElement('div');
        div.className = 'message ' + cls;
        div.innerHTML = '<span class="sender-name">' + senderName + '</span>' +
                       msg.message +
                       '<span class="time">' + time + '</span>';
        box.appendChild(div);
    }
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

function addEmoji(emoji) {
    var input = document.getElementById('messageInput');
    if (input) {
        input.value += emoji;
        input.focus();
        document.getElementById('emojiPopup').classList.remove('active');
    }
}

// ============================================================
// التحكم في نافذة الإيموجي
// ============================================================
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
    setInterval(loadMessages, 5000);
});
</script>

</body>
</html>