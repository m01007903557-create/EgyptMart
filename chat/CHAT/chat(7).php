<?php
/**
 * chat/chat.php - صفحة محادثة متكاملة (النسخة النهائية)
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
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المحادثة - طلب شراء #<?php echo $rfq_id; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f0f2f5; 
            padding: 15px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container { 
            max-width: 900px; 
            width: 100%;
            margin: 0 auto; 
            background: #fff; 
            border-radius: 16px; 
            box-shadow: 0 8px 30px rgba(0,0,0,0.12); 
            overflow: hidden;
        }
        
        /* ============================================ */
        /* تذكرة طلب الشراء - بيانات كاملة بالترتيب */
        /* ============================================ */
        .rfq-ticket {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff;
            padding: 14px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px 20px;
            align-items: center;
            font-size: 13px;
            border-bottom: 3px solid #ffd700;
        }
        .rfq-ticket .item {
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }
        .rfq-ticket .label { opacity: 0.7; font-weight: 400; }
        .rfq-ticket .value { font-weight: 600; }
        .rfq-ticket .badge {
            background: rgba(255,215,0,0.2);
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            border: 1px solid rgba(255,215,0,0.3);
        }
        .rfq-ticket .highlight { color: #ffd700; }
        
        /* ============================================ */
        /* نافذة المحادثة - RTL و LTR */
        /* ============================================ */
        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        /* المورد: يكتب من اليسار إلى اليمين (LTR) مع خلفية خضراء فاتحة */
        .message.supplier {
            align-self: flex-start;
            background: #e8f5e9;
            color: #1b5e20;
            border-bottom-left-radius: 5px;
            max-width: 75%;
            padding: 10px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            direction: ltr;
            text-align: left;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        /* المشتري: يكتب من اليمين إلى اليسار (RTL) مع خلفية خضراء داكنة */
        .message.buyer {
            align-self: flex-end;
            background: #25D366;
            color: #fff;
            border-bottom-right-radius: 5px;
            max-width: 75%;
            padding: 10px 16px;
            border-radius: 18px;
            word-wrap: break-word;
            direction: rtl;
            text-align: right;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .message .time {
            font-size: 10px;
            opacity: 0.7;
            margin-top: 5px;
            display: block;
        }
        .message .sender-name {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 4px;
            display: block;
        }
        .buyer .sender-name { color: #fff; }
        .supplier .sender-name { color: #1b5e20; }
        
        /* ============================================ */
        /* تذكرة عرض السعر */
        /* ============================================ */
        .offer-ticket {
            align-self: center;
            background: #fff;
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 12px 18px;
            max-width: 90%;
            box-shadow: 0 2px 10px rgba(255,193,7,0.15);
            margin: 6px 0;
            width: 100%;
        }
        .offer-ticket .title {
            color: #856404;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 8px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .offer-ticket .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
            border-bottom: 1px solid #f5f5f5;
        }
        .offer-ticket .row:last-child { border-bottom: none; }
        .offer-ticket .label { color: #777; font-weight: 400; }
        .offer-ticket .value { font-weight: 600; color: #333; }
        .offer-ticket .price-value { color: #28a745; font-size: 16px; font-weight: 700; }
        .offer-ticket .update-badge {
            background: #ffc107;
            color: #856404;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .offer-ticket .update-limit {
            background: #dc3545;
            color: #fff;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        /* ============================================ */
        /* منطقة الإدخال - متجاوبة */
        /* ============================================ */
        .input-area {
            padding: 12px 15px;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 8px;
            background: #fff;
            align-items: center;
            flex-wrap: wrap;
        }
        .input-area input {
            flex: 1;
            min-width: 120px;
            padding: 10px 16px;
            border: 1.5px solid #e0e0e0;
            border-radius: 30px;
            outline: none;
            font-size: 14px;
            transition: border 0.3s;
        }
        .input-area input:focus { border-color: #25D366; }
        .input-area button {
            background: #25D366;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: background 0.3s;
        }
        .input-area button:hover { background: #1da851; }
        
        .emoji-btn {
            background: none;
            border: none;
            font-size: 26px;
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
            border: 1px solid #e0e0e0;
            border-radius: 14px;
            padding: 12px;
            box-shadow: 0 8px 35px rgba(0,0,0,0.15);
            z-index: 999;
            width: 290px;
            flex-wrap: wrap;
            gap: 4px;
        }
        .emoji-popup.active { display: flex; }
        .emoji-popup span {
            font-size: 30px;
            cursor: pointer;
            padding: 4px 6px;
            border-radius: 8px;
            transition: background 0.15s;
        }
        .emoji-popup span:hover { background: #f0f0f0; }
        
        .no-messages { 
            text-align: center; 
            color: #aaa; 
            padding: 30px; 
            align-self: center;
            font-size: 14px;
        }
        
        /* ============================================ */
        /* متجاوب (Mobile Responsive) */
        /* ============================================ */
        @media (max-width: 700px) {
            body { padding: 10px; }
            .rfq-ticket { 
                font-size: 12px; 
                padding: 10px 14px; 
                gap: 4px 12px;
            }
            .rfq-ticket .item { white-space: normal; }
            .chat-box { height: 350px; padding: 12px 14px; }
            .message { max-width: 85%; padding: 8px 14px; font-size: 14px; }
            .input-area { padding: 10px 12px; gap: 6px; }
            .input-area input { min-width: 80px; padding: 8px 14px; font-size: 13px; }
            .input-area button { padding: 8px 16px; font-size: 13px; }
            .offer-ticket { padding: 10px 14px; max-width: 95%; }
            .offer-ticket .row { font-size: 12px; }
            .offer-ticket .price-value { font-size: 14px; }
            .emoji-popup { width: 240px; padding: 10px; }
            .emoji-popup span { font-size: 26px; }
        }
        
        @media (max-width: 420px) {
            .rfq-ticket { font-size: 11px; padding: 8px 10px; gap: 3px 8px; }
            .chat-box { height: 280px; padding: 10px; }
            .message { max-width: 90%; padding: 6px 12px; font-size: 13px; }
            .input-area { flex-wrap: wrap; justify-content: center; }
            .input-area input { min-width: 100%; }
            .offer-ticket .row { flex-wrap: wrap; gap: 2px; }
            .offer-ticket .label { min-width: 70px; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- ============================================ -->
    <!-- تذكرة طلب الشراء (بالترتيب المطلوب) -->
    <!-- ============================================ -->
    <div class="rfq-ticket">
        <span class="item"><span class="label">📦 طلب:</span><span class="value highlight">#<?php echo $rfq_id; ?></span></span>
        <span class="item"><span class="label">📦 المنتج:</span><span class="value"><?php echo htmlspecialchars($chat['product_name']); ?></span></span>
        <span class="item"><span class="label">📊 الكمية:</span><span class="value"><?php echo $chat['br_estimate_qty'] . ' ' . $chat['br_estimate_qty_unit']; ?></span></span>
        <span class="item"><span class="label">🏢 الشركة:</span><span class="value"><?php echo htmlspecialchars($chat['buyer_company'] ?? 'غير محدد'); ?></span></span>
        <span class="item"><span class="label">👤 المشتري:</span><span class="value"><?php echo $chat['buyer_fname'] . ' ' . $chat['buyer_lname']; ?></span></span>
        <span class="item"><span class="label">📞 الهاتف:</span><span class="value"><?php echo $chat['buyer_phone']; ?></span></span>
        <span class="item"><span class="label">🌍 البلد:</span><span class="value"><?php echo $chat['country_name'] ?? 'غير محدد'; ?></span></span>
        <span class="item"><span class="label">📧 البريد:</span><span class="value"><?php echo $chat['buyer_email']; ?></span></span>
        <span class="item"><span class="label">📝 التفاصيل:</span><span class="value"><?php echo mb_substr($chat['br_requirement'], 0, 35) . (strlen($chat['br_requirement']) > 35 ? '...' : ''); ?></span></span>
        <span class="item badge">📅 <?php echo date('Y-m-d', strtotime($chat['br_posting_date'])); ?></span>
    </div>

    <!-- ============================================ -->
    <!-- نافذة المحادثة -->
    <!-- ============================================ -->
    <div class="chat-box" id="chatMessages">
        <p class="no-messages">جارٍ تحميل الرسائل...</p>
    </div>

    <!-- ============================================ -->
    <!-- منطقة الإدخال -->
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
var senderName = (senderType == 'supplier') ? '<?php echo addslashes($chat['supplier_fname']); ?>' : '<?php echo addslashes($chat['buyer_fname']); ?>';

// عروض الأسعار
var offers = <?php echo json_encode($offers); ?>;

// ============================================================
// عرض تذكرة عرض السعر (مع عدد التحديثات)
// ============================================================
function displayOfferTicket(offer) {
    var chatBox = document.getElementById('chatMessages');
    
    // إزالة تذاكر العروض القديمة
    var oldTickets = chatBox.querySelectorAll('.offer-ticket');
    oldTickets.forEach(function(el) { el.remove(); });
    
    if (!offer || offer.price <= 0) return;
    
    var div = document.createElement('div');
    div.className = 'offer-ticket';
    
    var updateCount = offer.update_count || 0;
    var updateText = '';
    var badgeClass = 'update-badge';
    
    if (updateCount == 0) {
        updateText = 'عرض سعر أولي';
    } else if (updateCount == 1) {
        updateText = 'تحديث السعر (المرة الأولى)';
    } else if (updateCount >= 2) {
        updateText = '⚠️ آخر تحديث (لا يمكن التعديل مرة أخرى)';
        badgeClass = 'update-limit';
    }
    
    div.innerHTML = `
        <div class="title">
            💰 عرض السعر
            <span class="${badgeClass}">${updateText}</span>
        </div>
        <div class="row">
            <span class="label">السعر:</span>
            <span class="value price-value">${offer.price} ${offer.currency || 'USD'}</span>
        </div>
        <div class="row">
            <span class="label">مدة التوصيل:</span>
            <span class="value">${offer.delivery_days} يوم</span>
        </div>
        <div class="row">
            <span class="label">الملاحظات:</span>
            <span class="value">${offer.notes || 'لا توجد ملاحظات'}</span>
        </div>
    `;
    
    // إضافة التذكرة في أعلى المحادثة (بعد معلومات الطلب)
    var noMsg = chatBox.querySelector('.no-messages');
    if (noMsg) {
        chatBox.insertBefore(div, noMsg);
    } else {
        chatBox.insertBefore(div, chatBox.firstChild);
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
            var chatBox = document.getElementById('chatMessages');
            
            // إزالة جميع الرسائل القديمة (مع الاحتفاظ بتذكرة العرض)
            var children = chatBox.children;
            var toRemove = [];
            for (var i = 0; i < children.length; i++) {
                if (!children[i].classList.contains('offer-ticket')) {
                    toRemove.push(children[i]);
                }
            }
            toRemove.forEach(function(el) { el.remove(); });
            
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
                if (html) {
                    chatBox.insertAdjacentHTML('beforeend', html);
                } else {
                    var noMsg = chatBox.querySelector('.no-messages');
                    if (!noMsg) {
                        var p = document.createElement('p');
                        p.className = 'no-messages';
                        p.textContent = 'لا توجد رسائل بعد';
                        chatBox.appendChild(p);
                    }
                }
            }
            
            // عرض أحدث عرض سعر
            if (offers.length > 0) {
                displayOfferTicket(offers[0]);
            }
            
            chatBox.scrollTop = chatBox.scrollHeight;
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
    
    var rect = this.getBoundingClientRect();
    var popupWidth = 290;
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