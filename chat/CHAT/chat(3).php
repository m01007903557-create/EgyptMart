<?php
// ============================================================
// هذا الملف هو chat.php الموجود في مجلد chat/
// ============================================================

// عرض الأخطاء للتصحيح (قم بإزالتها بعد التأكد من العمل)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/function.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/common.php';

$user_id = $_SESSION['uid_indm'] ?? 0;
if (!$user_id) {
    header('Location: /sign-in.php');
    exit;
}

// ============================================================
// جلب rfq_id من الرابط
// ============================================================
$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if ($rfq_id == 0) {
    die("رقم الطلب غير صحيح");
}

// ============================================
// إنشاء غرفة محادثة تلقائياً إذا لم تكن موجودة
// ============================================
$chat_check = mysqli_query($con, "SELECT * FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if (!$chat_check || mysqli_num_rows($chat_check) == 0) {
    // جلب supplier_id و buyer_id من offers
    $offer_check = mysqli_query($con, "SELECT supplier_id, buyer_id FROM offers WHERE rfq_id = $rfq_id LIMIT 1");
    if ($offer_check && mysqli_num_rows($offer_check) > 0) {
        $offer_data = mysqli_fetch_assoc($offer_check);
        $supplier_id = $offer_data['supplier_id'];
        $buyer_id = $offer_data['buyer_id'];
        
        $chat_code = 'CHAT_' . time() . '_' . $rfq_id;
        $insert_chat = "INSERT INTO chat_rooms (rfq_id, supplier_id, buyer_id, chat_code, created_at, status, expiry_date) 
                        VALUES ($rfq_id, $supplier_id, $buyer_id, '$chat_code', NOW(), 'active', DATE_ADD(NOW(), INTERVAL 7 DAY))";
        mysqli_query($con, $insert_chat);
    }
}

// جلب chat_code من قاعدة البيانات
$chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if ($chat_check && mysqli_num_rows($chat_check) > 0) {
    $chat_data = mysqli_fetch_assoc($chat_check);
    $chat_code = $chat_data['chat_code'];
} else {
    die("خطأ: لا توجد غرفة محادثة لهذا الطلب");
}

// ============================================================
// الحصول على كود الشات (جاهز للاستخدام)
// ============================================================
if (empty($chat_code)) {
    die('خطأ: لم يتم توفير كود الشات في الرابط');
}

// تخزين الكود في متغير للاستخدام لاحقاً
$_SESSION['debug_chat_code'] = $chat_code;

// ============================================================
// تضمين الهيدر والفوتر (نفس طريقة chat-wrapper.php)
// ============================================================
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header_new.php';
include $_SERVER['DOCUMENT_ROOT'] . '/includes/header_menu.php';
?>

<!-- إضافة CSS لإصلاح أي مشاكل في التنسيق -->
<style>
    .chat-content-area {
        min-height: 500px;
        background: #f5f5f5;
        padding: 20px;
    }
    
    body {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .hm1, .bbc, #res-mob1 {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    .alert {
        padding: 15px;
        margin: 20px;
        border-radius: 5px;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .chat-container {
        max-width: 800px;
        margin: 20px auto;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .chat-header {
        background: #25D366;
        color: white;
        padding: 15px;
        text-align: center;
    }
    
    .quote-info {
        background: #f9f9f9;
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }
    
    .messages {
        padding: 20px;
        height: 400px;
        overflow-y: auto;
        background: #f5f5f5;
    }
    
    .message {
        margin-bottom: 15px;
        display: flex;
    }
    
    .message.supplier {
        justify-content: flex-start;
    }
    
    .message.buyer {
        justify-content: flex-end;
    }
    
    .bubble {
        max-width: 70%;
        padding: 10px 15px;
        border-radius: 18px;
    }
   
    .supplier .bubble {
        background: #e8f5e9;
        color: #333;
    }
    
    .buyer .bubble {
        background: #25D366;
        color: white;
    }
    
    .time {
        font-size: 11px;
        color: #999;
        margin-top: 5px;
    }
    
    .chat-input {
        padding: 15px;
        border-top: 1px solid #ddd;
        display: flex;
        gap: 10px;
        background: white;
    }
    
    .chat-input input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 25px;
    }
    
    .chat-input button {
        background: #25D366;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 25px;
        cursor: pointer;
    }
    
    #emojiPickerBtn {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        padding: 8px 12px;
    }
</style>

<div class="chat-content-area">
    <?php
    // ============================================================
    // جلب بيانات الشات (نفس الكود الأصلي)
    // ============================================================
    
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
    
    if (!$res) {
        echo '<div class="alert alert-danger">خطأ في الاستعلام: ' . mysqli_error($con) . '</div>';
    } else {
        $chat = mysqli_fetch_assoc($res);
        
        if (!$chat) {
            echo '<div class="alert alert-danger">';
            echo '<strong>الشات غير موجود</strong><br>';
            echo 'الكود المطلوب: ' . htmlspecialchars($chat_code) . '<br>';
            echo 'تأكد من أن هذا الكود موجود في جدول chat_rooms';
            echo '</div>';
        } else {
            // جلب عرض السعر
            $quote = null;
            if (!empty($chat['quote_id'])) {
                $quote_sql = "SELECT * FROM quotes WHERE quote_id = {$chat['quote_id']}";
                $quote_res = mysqli_query($con, $quote_sql);
                $quote = mysqli_fetch_assoc($quote_res);
            }
            ?>
            
            <!-- عرض الشات -->
            <div class="chat-container">
                <div class="chat-header">
                    <i class="fa fa-whatsapp"></i> محادثة - <?php echo htmlspecialchars($chat['chat_code']); ?>
                </div>
                
                <div class="quote-info">
                    <strong>المنتج:</strong> <?php echo htmlspecialchars($chat['product_name']); ?><br>
                    <strong>RFQ #:</strong> <?php echo htmlspecialchars($chat['rfq_id']); ?><br>
                    <?php if ($quote): ?>
                        <strong>عرض السعر:</strong><br>
                        السعر: <?php echo htmlspecialchars($quote['unit_price']); ?> USD<br>
                        أقل كمية: <?php echo htmlspecialchars($quote['moq']); ?><br>
                        مدة التوصيل: <?php echo htmlspecialchars($quote['delivery_time']); ?><br>
                        رسالة المورد: <?php echo nl2br(htmlspecialchars($quote['supplier_message'])); ?>
                    <?php endif; ?>
                </div>
                
                <div class="messages" id="messages"></div>
                
                <div class="chat-input">
                    <button type="button" id="emojiPickerBtn">😊</button>
                    <div id="emojiPicker" style="display:none; position:fixed; background:#fff; border:1px solid #ddd; border-radius:12px; padding:12px; width:280px; flex-wrap:wrap; gap:8px; z-index:9999; box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                        <span onclick="addEmoji('😊')" style="font-size:24px; cursor:pointer; padding:6px;">😊</span>
                        <span onclick="addEmoji('😂')" style="font-size:24px; cursor:pointer; padding:6px;">😂</span>
                        <span onclick="addEmoji('❤️')" style="font-size:24px; cursor:pointer; padding:6px;">❤️</span>
                        <span onclick="addEmoji('👍')" style="font-size:24px; cursor:pointer; padding:6px;">👍</span>
                        <span onclick="addEmoji('🎉')" style="font-size:24px; cursor:pointer; padding:6px;">🎉</span>
                        <span onclick="addEmoji('😢')" style="font-size:24px; cursor:pointer; padding:6px;">😢</span>
                        <span onclick="addEmoji('🔥')" style="font-size:24px; cursor:pointer; padding:6px;">🔥</span>
                        <span onclick="addEmoji('✅')" style="font-size:24px; cursor:pointer; padding:6px;">✅</span>
                    </div>
                    <input type="text" id="messageInput" placeholder="اكتب رسالتك...">
                    <button onclick="sendMessage()">إرسال</button>
                </div>
            </div>
            
            <script>
                var chatId = <?php echo (int)$chat['chat_id']; ?>;
                var currentUserId = <?php echo (int)$user_id; ?>;
                var supplierId = <?php echo (int)$chat['supplier_id']; ?>;
                var buyerId = <?php echo (int)$chat['buyer_id']; ?>;
                var senderType = (currentUserId == supplierId) ? 'supplier' : 'buyer';
                
                function addEmoji(emoji) {
                    var input = document.getElementById('messageInput');
                    if (input) {
                        input.value += emoji;
                        input.focus();
                        var picker = document.getElementById('emojiPicker');
                        if (picker) picker.style.display = 'none';
                    }
                }
                
                function loadMessages() {
                    if (!chatId) return;
                    fetch('/chat/ajax_chat.php?action=get&chat_id=' + chatId)
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data.success && data.messages) {
                                var html = '';
                                for (var i = 0; i < data.messages.length; i++) {
                                    var msg = data.messages[i];
                                    var safeMessage = (msg.message || '').replace(/[&<>]/g, function(m) {
                                        if (m === '&') return '&amp;';
                                        if (m === '<') return '&lt;';
                                        if (m === '>') return '&gt;';
                                        return m;
                                    }).replace(/\n/g, '<br>');
                                    var bubbleStyle = (msg.sender_type === 'supplier') ? 'background: #e8f5e9; color: #333;' : 'background: #25D366; color: white;';
                                    html += '<div class="message ' + msg.sender_type + '" style="margin-bottom: 15px; display: flex; ' + (msg.sender_type === 'supplier' ? 'justify-content: flex-start;' : 'justify-content: flex-end;') + '">';
                                    html += '<div class="bubble" style="max-width: 70%; padding: 10px 15px; border-radius: 18px; ' + bubbleStyle + '">' + safeMessage;
                                    html += '<div class="time" style="font-size: 11px; color: #999; margin-top: 5px;">' + (msg.time || '') + '</div></div></div>';
                                }
                                var messagesDiv = document.getElementById('messages');
                                if (messagesDiv) {
                                    messagesDiv.innerHTML = html;
                                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                                }
                            }
                        })
                        .catch(function(err) { console.log('Error:', err); });
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
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.success) {
                            if (input) input.value = '';
                            loadMessages();
                        }
                    })
                    .catch(function(err) { console.log('Error:', err); });
                }
                
                document.addEventListener('DOMContentLoaded', function() {
                    var emojiBtn = document.getElementById('emojiPickerBtn');
                    var emojiPicker = document.getElementById('emojiPicker');
                    if (emojiBtn && emojiPicker) {
                        emojiBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            var rect = emojiBtn.getBoundingClientRect();
                            emojiPicker.style.bottom = (window.innerHeight - rect.top + 10) + 'px';
                            emojiPicker.style.left = (rect.left - 280 + 40) + 'px';
                            emojiPicker.style.display = 'flex';
                        });
                        document.addEventListener('click', function(e) {
                            if (!emojiPicker.contains(e.target) && e.target !== emojiBtn) {
                                emojiPicker.style.display = 'none';
                            }
                        });
                    }
                    
                    var messageInput = document.getElementById('messageInput');
                    if (messageInput) {
                        messageInput.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                sendMessage();
                            }
                        });
                    }
                    
                    if (chatId) {
                        loadMessages();
                        setInterval(loadMessages, 5000);
                    }
                });
            </script>
            <?php
        }
    }
    ?>
</div>

<?php
// تضمين الفوتر
include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
?>