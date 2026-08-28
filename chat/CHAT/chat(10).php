<?php
/**
 * chat/chat.php - صفحة محادثة متكاملة (باستخدام التنسيق القديم الناجح)
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

// جلب عروض الأسعار للتحديثات
$offers = [];
$offers_sql = "SELECT * FROM offers WHERE rfq_id = $rfq_id ORDER BY created_at DESC";
$offers_res = mysqli_query($con, $offers_sql);
while ($offer = mysqli_fetch_assoc($offers_res)) {
    $offers[] = $offer;
}
?>
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المحادثة - طلب شراء #<?php echo $rfq_id; ?></title>
    <style>
        /* ============================================ */
        /* التنسيق القديم الناجح (من الملف الأصلي) */
        /* ============================================ */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2f7;
            padding: 15px;
            margin: 0;
            direction: rtl;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
        
        /* ============================================ */
        /* تذكرة طلب الشراء */
        /* ============================================ */
        .rfq-ticket {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff;
            padding: 10px 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 3px 14px;
            align-items: center;
            font-size: 12px;
            border-bottom: 3px solid #ffd700;
        }
        .rfq-ticket .item {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .rfq-ticket .label { opacity: 0.7; font-weight: 400; }
        .rfq-ticket .value { font-weight: 600; }
        .rfq-ticket .badge {
            background: rgba(255,215,0,0.12);
            padding: 1px 10px;
            border-radius: 12px;
            font-size: 11px;
            border: 1px solid rgba(255,215,0,0.15);
        }
        .rfq-ticket .highlight { color: #ffd700; }
        
        /* ============================================ */
        /* تذكرة عرض السعر (Floating) */
        /* ============================================ */
        .offer-ticket {
            background: #fff8e1;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 8px 14px;
            margin: 4px 12px;
            box-shadow: 0 3px 12px rgba(255,193,7,0.15);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 4px 16px;
        }
        .offer-ticket .title {
            color: #856404;
            font-weight: 700;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .offer-ticket .details {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 14px;
            font-size: 12px;
        }
        .offer-ticket .details .label { color: #888; font-weight: 400; }
        .offer-ticket .details .value { font-weight: 600; color: #333; }
        .offer-ticket .price-value { color: #28a745; font-size: 14px; font-weight: 700; }
        .offer-ticket .update-badge {
            background: #ffc107;
            color: #856404;
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
        /* نافذة المحادثة - التنسيق القديم الناجح */
        /* ============================================ */
        .chat-container {
            background: #fff;
            overflow: hidden;
        }
        
        .chat-header {
            background: #25D366;
            color: white;
            padding: 12px 15px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
        }
        
        .quote-info {
            background: #f9f9f9;
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }
        .quote-info strong { color: #333; }
        
        .messages {
            padding: 15px 18px;
            height: 380px;
            overflow-y: auto;
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        /* ============================================ */
        /* تنسيق الرسائل - ثنائي الجوانب */
        /* ============================================ */
        .message {
            display: flex;
            margin-bottom: 8px;
            max-width: 80%;
        }
        
        .message.supplier {
            align-self: flex-start;
            justify-content: flex-start;
        }
        
        .message.buyer {
            align-self: flex-end;
            justify-content: flex-end;
        }
        
        .bubble {
            padding: 10px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .supplier .bubble {
            background: #e8f5e9;
            color: #1b5e20;
            border-bottom-left-radius: 4px;
        }
        
        .buyer .bubble {
            background: #25D366;
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }
        
        .message .sender-name {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 3px;
            display: block;
        }
        .buyer .sender-name { color: #e8f5e9; }
        .supplier .sender-name { color: #1b5e20; }
        
        .message .time {
            font-size: 10px;
            opacity: 0.6;
            margin-top: 3px;
            display: block;
        }
        .buyer .time { color: #e8f5e9; }
        .supplier .time { color: #888; }
        
        .no-messages {
            text-align: center;
            color: #aaa;
            padding: 30px;
            align-self: center;
            font-size: 14px;
        }
        
        /* ============================================ */
        /* منطقة الإدخال */
        /* ============================================ */
        .chat-input {
            padding: 12px 15px;
            border-top: 1px solid #ddd;
            display: flex;
            gap: 8px;
            background: white;
            align-items: center;
        }
        .chat-input input {
            flex: 1;
            padding: 10px 16px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
            font-size: 14px;
            direction: rtl;
        }
        .chat-input input:focus { border-color: #25D366; }
        .chat-input button {
            background: #25D366;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: background 0.3s;
        }
        .chat-input button:hover { background: #1da851; }
        
        #emojiPickerBtn {
            background: none;
            border: none;
            font-size: 26px;
            cursor: pointer;
            padding: 0 8px;
            line-height: 1;
        }
        
        /* ============================================ */
        /* نافذة الإيموجي المنبثقة */
        /* ============================================ */
        .emoji-popup {
            display: none;
            position: fixed;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
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
        @media (max-width: 700px) {
            body { padding: 10px; }
            .rfq-ticket { font-size: 11px; padding: 8px 12px; gap: 3px 8px; }
            .messages { height: 320px; padding: 10px 12px; }
            .message { max-width: 85%; }
            .bubble { padding: 8px 14px; font-size: 13px; }
            .offer-ticket { padding: 6px 10px; flex-direction: column; align-items: stretch; gap: 4px; }
            .offer-ticket .details { gap: 3px 10px; font-size: 11px; }
            .chat-input { padding: 8px 10px; flex-wrap: wrap; }
            .chat-input input { min-width: 70px; padding: 8px 12px; font-size: 13px; }
            .chat-input button { padding: 8px 14px; font-size: 13px; }
            .emoji-popup { width: 220px; }
            .emoji-popup span { font-size: 24px; }
        }
        
        @media (max-width: 420px) {
            .rfq-ticket { font-size: 10px; padding: 6px 8px; gap: 2px 6px; }
            .messages { height: 250px; padding: 6px; }
            .message { max-width: 90%; }
            .bubble { padding: 6px 10px; font-size: 12px; }
            .offer-ticket { font-size: 11px; padding: 5px 8px; }
            .chat-input { flex-wrap: wrap; justify-content: center; }
            .chat-input input { min-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- ============================================ -->
    <!-- تذكرة طلب الشراء -->
    <!-- ============================================ -->
    <div class="rfq-ticket">
        <span class="item"><span class="label">📦 طلب:</span><span class="value highlight">#<?php echo $rfq_id; ?></span></span>
        <span class="item"><span class="label">🏢 الشركة:</span><span class="value"><?php echo htmlspecialchars($chat['buyer_company'] ?? 'غير محدد'); ?></span></span>
        <span class="item"><span class="label">👤 المشتري:</span><span class="value"><?php echo $chat['buyer_fname'] . ' ' . $chat['buyer_lname']; ?></span></span>
        <span class="item"><span class="label">📞 الهاتف:</span><span class="value"><?php echo $chat['buyer_phone']; ?></span></span>
        <span class="item"><span class="label">📧 البريد:</span><span class="value"><?php echo $chat['buyer_email']; ?></span></span>
        <span class="item"><span class="label">📦 المنتج:</span><span class="value"><?php echo htmlspecialchars($chat['product_name']); ?></span></span>
        <span class="item"><span class="label">📊 الكمية:</span><span class="value"><?php echo $chat['br_estimate_qty'] . ' ' . $chat['br_estimate_qty_unit']; ?></span></span>
        <span class="item badge">📅 <?php echo date('Y-m-d', strtotime($chat['br_posting_date'])); ?></span>
    </div>

    <!-- ============================================ -->
    <!-- تذكرة عرض السعر (Floating) -->
    <!-- ============================================ -->
    <div class="offer-ticket" id="offerTicket" style="display:none;">
        <div class="title">
            💰 عرض السعر
            <span class="update-badge" id="offerBadge">عرض سعر أولي</span>
        </div>
        <div class="details">
            <span><span class="label">السعر:</span> <span class="price-value" id="offerPrice">0 USD</span></span>
            <span><span class="label">مدة التوصيل:</span> <span class="value" id="offerDelivery">0 يوم</span></span>
            <span><span class="label">الملاحظات:</span> <span class="value" id="offerNotes">لا توجد</span></span>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- نافذة المحادثة -->
    <!-- ============================================ -->
    <div class="chat-container">
        <div class="chat-header">
            <i class="fa fa-whatsapp"></i> 💬 المحادثة
        </div>
        
        <div class="quote-info">
            <strong>المنتج:</strong> <?php echo htmlspecialchars($chat['product_name']); ?> &nbsp;|&nbsp;
            <strong>RFQ #:</strong> <?php echo $rfq_id; ?>
        </div>
        
        <div class="messages" id="messages">
            <p class="no-messages">جارٍ تحميل الرسائل...</p>
        </div>
        
        <div class="chat-input">
            <button type="button" id="emojiPickerBtn">😊</button>
            <input type="text" id="messageInput" placeholder="اكتب رسالتك...">
            <button onclick="sendMessage()">إرسال</button>
        </div>
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
var senderName = (senderType == 'supplier') ? '<?php echo addslashes($chat['supplier_fname']); ?>' : '<?php echo addslashes($chat['buyer_fname']); ?>';

// عروض الأسعار
var offers = <?php echo json_encode($offers); ?>;

// ============================================================
// تحديث تذكرة عرض السعر العائمة
// ============================================================
function updateOfferTicket(offer) {
    var ticket = document.getElementById('offerTicket');
    if (!offer || offer.price <= 0) {
        ticket.style.display = 'none';
        return;
    }
    
    ticket.style.display = 'flex';
    document.getElementById('offerPrice').textContent = offer.price + ' ' + (offer.currency || 'USD');
    document.getElementById('offerDelivery').textContent = offer.delivery_days + ' يوم';
    document.getElementById('offerNotes').textContent = offer.notes || 'لا توجد';
    
    var badge = document.getElementById('offerBadge');
    var updateCount = offer.update_count || 0;
    
    if (updateCount == 0) {
        badge.textContent = 'عرض سعر أولي';
        badge.className = 'update-badge';
    } else if (updateCount == 1) {
        badge.textContent = 'تحديث السعر (المرة الأولى)';
        badge.className = 'update-badge';
    } else if (updateCount >= 2) {
        badge.textContent = '⚠️ آخر تحديث (لا يمكن التعديل)';
        badge.className = 'update-limit';
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
            var messagesDiv = document.getElementById('messages');
            messagesDiv.innerHTML = '';
            
            if (data.success && data.messages) {
                var html = '';
                for (var i = 0; i < data.messages.length; i++) {
                    var msg = data.messages[i];
                    var cls = msg.sender_type === 'supplier' ? 'supplier' : 'buyer';
                    var name = msg.sender_type === 'supplier' ? '<?php echo addslashes($chat['supplier_fname']); ?>' : '<?php echo addslashes($chat['buyer_fname']); ?>';
                    var time = msg.time || '';
                    
                    html += '<div class="message ' + cls + '">';
                    html += '<div class="bubble">';
                    html += '<span class="sender-name">' + name + '</span>';
                    html += msg.message;
                    html += '<span class="time">' + time + '</span>';
                    html += '</div></div>';
                }
                messagesDiv.innerHTML = html;
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            } else {
                messagesDiv.innerHTML = '<p class="no-messages">لا توجد رسائل بعد</p>';
            }
            
            // تحديث تذكرة العرض
            if (offers.length > 0) {
                updateOfferTicket(offers[0]);
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
document.getElementById('emojiPickerBtn').addEventListener('click', function(e) {
    e.stopPropagation();
    var popup = document.getElementById('emojiPopup');
    popup.classList.toggle('active');
    
    var rect = this.getBoundingClientRect();
    var popupWidth = 270;
    var leftPos = rect.left - (popupWidth / 2) + 20;
    if (leftPos < 10) leftPos = 10;
    if (leftPos + popupWidth > window.innerWidth - 10) {
        leftPos = window.innerWidth - popupWidth - 10;
    }
    popup.style.bottom = (window.innerHeight - rect.top + 10) + 'px';
    popup.style.left = leftPos + 'px';
});

document.addEventListener('click', function(e) {
    var popup = document.getElementById('emojiPopup');
    var btn = document.getElementById('emojiPickerBtn');
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