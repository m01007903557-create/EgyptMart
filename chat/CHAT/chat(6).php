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
        /* تذكرة طلب الشراء (أفقية) - بيانات كاملة */
        /* ============================================ */
        .rfq-ticket {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff;
            padding: 12px 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px 25px;
            align-items: center;
            font-size: 13px;
        }
        .rfq-ticket .item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .rfq-ticket .label { opacity: 0.7; font-weight: normal; }
        .rfq-ticket .value { font-weight: 600; }
        .rfq-ticket .badge {
            background: rgba(255,255,255,0.15);
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
        }
        .rfq-ticket .highlight { color: #ffd700; }
        
        /* ============================================ */
        /* نافذة المحادثة - توزيع صحيح */
        /* ============================================ */
        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        /* رسائل المورد (تظهر على اليسار) */
        .message.supplier {
            align-self: flex-start;
            background: #e8f5e9;
            color: #333;
            border-bottom-left-radius: 5px;
            max-width: 75%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
        }
        /* رسائل المشتري (تظهر على اليمين) */
        .message.buyer {
            align-self: flex-end;
            background: #25D366;
            color: #fff;
            border-bottom-right-radius: 5px;
            max-width: 75%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
            position: relative;
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
        
        /* ============================================ */
        /* تذكرة عرض السعر (تظهر في المحادثة) */
        /* ============================================ */
        .offer-ticket {
            align-self: center;
            background: #fff;
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 12px 18px;
            max-width: 85%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin: 5px 0;
        }
        .offer-ticket .title {
            color: #856404;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 6px;
            text-align: center;
        }
        .offer-ticket .row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 13px;
            border-bottom: 1px solid #f0f0f0;
        }
        .offer-ticket .row:last-child { border-bottom: none; }
        .offer-ticket .label { color: #666; }
        .offer-ticket .value { font-weight: 600; color: #333; }
        .offer-ticket .price-value { color: #28a745; font-size: 16px; }
        .offer-ticket .update-badge {
            background: #ffc107;
            color: #856404;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 11px;
            display: inline-block;
            margin-right: 8px;
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
        /* نافذة الإيموجي المنبثقة */
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
        
        .no-messages { text-align: center; color: #999; padding: 20px; align-self: center; }
        
        @media (max-width: 600px) {
            .rfq-ticket { font-size: 11px; padding: 8px 12px; gap: 4px 12px; }
            .message { max-width: 85%; }
            .input-area { flex-wrap: wrap; }
            .offer-ticket { max-width: 95%; padding: 10px; }
            .offer-ticket .row { font-size: 12px; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- ============================================ -->
    <!-- تذكرة طلب الشراء (أفقية) - بيانات كاملة -->
    <!-- ============================================ -->
    <div class="rfq-ticket">
        <span class="item"><span class="label">📦 طلب:</span><span class="value highlight">#<?php echo $rfq_id; ?></span></span>
        <span class="item"><span class="label">🏢 الشركة:</span><span class="value"><?php echo htmlspecialchars($chat['buyer_company'] ?? 'غير محدد'); ?></span></span>
        <span class="item"><span class="label">👤 المشتري:</span><span class="value"><?php echo $chat['buyer_fname'] . ' ' . $chat['buyer_lname']; ?></span></span>
        <span class="item"><span class="label">📞 هاتف:</span><span class="value"><?php echo $chat['buyer_phone']; ?></span></span>
        <span class="item"><span class="label">📧 البريد:</span><span class="value"><?php echo $chat['buyer_email']; ?></span></span>
        <span class="item"><span class="label">📦 المنتج:</span><span class="value"><?php echo htmlspecialchars($chat['product_name']); ?></span></span>
        <span class="item"><span class="label">📊 الكمية:</span><span class="value"><?php echo $chat['br_estimate_qty'] . ' ' . $chat['br_estimate_qty_unit']; ?></span></span>
        <span class="item"><span class="label">📝 التفاصيل:</span><span class="value"><?php echo mb_substr($chat['br_requirement'], 0, 30) . (strlen($chat['br_requirement']) > 30 ? '...' : ''); ?></span></span>
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
var senderName = (senderType == 'supplier') ? '<?php echo addslashes($chat['supplier_fname']); ?>' : '<?php echo addslashes($chat['buyer_fname']); ?>';

// عروض الأسعار
var offers = <?php echo json_encode($offers); ?>;

// ============================================================
// عرض تذكرة عرض السعر في المحادثة
// ============================================================
function displayOfferTicket(offer) {
    var chatBox = document.getElementById('chatMessages');
    var div = document.createElement('div');
    div.className = 'offer-ticket';
    
    var updateText = '';
    if (offer.update_count == 0) {
        updateText = 'عرض سعر أولي';
    } else if (offer.update_count == 1) {
        updateText = 'تحديث السعر (المرة الأولى)';
    } else if (offer.update_count >= 2) {
        updateText = 'تحديث السعر (المرة الأخيرة)';
    }
    
    div.innerHTML = `
        <div class="title">
            💰 عرض السعر ${updateText ? '<span class="update-badge">' + updateText + '</span>' : ''}
        </div>
        <div class="row">
            <span class="label">السعر:</span>
            <span class="value price-value">${offer.price} ${offer.currency}</span>
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
    
    // إضافة قبل رسائل "جارٍ التحميل"
    var noMsg = chatBox.querySelector('.no-messages');
    if (noMsg) {
        chatBox.insertBefore(div, noMsg);
    } else {
        chatBox.appendChild(div);
    }
    chatBox.scrollTop = chatBox.scrollHeight;
}

// ============================================================
// عرض تحديثات السعر في المحادثة
// ============================================================
function displayPriceUpdates() {
    // إزالة تذاكر العروض القديمة (مع الاحتفاظ بتذكرة العرض الأخير)
    var chatBox = document.getElementById('chatMessages');
    var oldTickets = chatBox.querySelectorAll('.offer-ticket');
    oldTickets.forEach(function(el) { el.remove(); });
    
    // عرض آخر عرض سعر فقط (الأحدث)
    if (offers.length > 0) {
        var latestOffer = offers[0];
        if (latestOffer.price > 0) {
            displayOfferTicket(latestOffer);
        }
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
            
            // إزالة جميع العناصر عدا تذاكر العروض
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
                    // إضافة الرسائل قبل تذاكر العروض
                    var offerTicket = chatBox.querySelector('.offer-ticket');
                    if (offerTicket) {
                        chatBox.insertAdjacentHTML('beforeend', html);
                    } else {
                        chatBox.innerHTML = html;
                    }
                } else {
                    var noMsg = chatBox.querySelector('.no-messages');
                    if (!noMsg) {
                        var p = document.createElement('p');
                        p.className = 'no-messages';
                        p.textContent = 'لا توجد رسائل بعد';
                        chatBox.appendChild(p);
                    }
                }
                
                // إعادة عرض تذكرة العرض بعد الرسائل
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
    
    var rect = this.getBoundingClientRect();
    popup.style.bottom = (window.innerHeight - rect.top + 10) + 'px';
    popup.style.left = (rect.left - 120) + 'px';
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