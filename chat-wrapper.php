<?php
/**
 * File: chat-wrapper.php
 * Description: صفحة عرض المحادثات (الشات) بنفس هيكل my-enquiries.php
 * Version: 2.0.0
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "chat-wrapper.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];
$current_user = $uid;

// الحصول على كود الشات من الرابط
$chat_code = isset($_GET['chat_code']) ? trim($_GET['chat_code']) : '';

// جلب بيانات الشات من قاعدة البيانات
require_once __DIR__ . '/lib/connect.php';

$chat_data = null;
$quote_data = null;

if (!empty($chat_code)) {
    $clean_chat_code = mysqli_real_escape_string($con, $chat_code);
    
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
            WHERE c.chat_code = '$clean_chat_code'";
    
    $res = mysqli_query($con, $sql);
    $chat_data = mysqli_fetch_assoc($res);
    
    if ($chat_data && !empty($chat_data['quote_id'])) {
        $quote_sql = "SELECT * FROM quotes WHERE quote_id = {$chat_data['quote_id']}";
        $quote_res = mysqli_query($con, $quote_sql);
        $quote_data = mysqli_fetch_assoc($quote_res);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محادثة - <?php echo htmlspecialchars($chat_code, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- CSS (نفس ملفات my-enquiries.php) -->
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/my2.css" type="text/css" rel="stylesheet">
    <link href="css/colorbox.css" type="text/css" rel="stylesheet">
    
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /><![endif]-->
    <!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
    
    <!-- CSS إضافية للشات -->
    <style>
        /* تنسيقات الشات */
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
            font-size: 18px;
            font-weight: bold;
        }
        .quote-info {
            background: #f9f9f9;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
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
            word-wrap: break-word;
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
            outline: none;
        }
        .chat-input input:focus {
            border-color: #25D366;
        }
        .chat-input button {
            background: #25D366;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: 0.3s;
        }
        .chat-input button:hover {
            background: #128C7E;
        }
        #emojiPickerBtn {
            background: #f0f0f0;
            border: 1px solid #ddd;
            font-size: 24px;
            cursor: pointer;
            padding: 5px 12px;
            border-radius: 25px;
            transition: 0.3s;
        }
        #emojiPickerBtn:hover {
            background: #e0e0e0;
        }
        
        /* منطقة عرض محتوى الشات */
        .chat-content-wrapper {
            overflow: hidden;
            padding: 0 20px;
        }
        
        /* استجابة للموبايل */
        @media (max-width: 768px) {
            .chat-container {
                margin: 10px;
            }
        }
    </style>
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <script type="text/javascript">
    $(document).ready(function() {
        $('body').on('click', '.ajax', function(event) {
            $.colorbox({
                href: $(this).attr('href'),
                open: true,
                width: '750px'
            });
            return false;
        });
        
        // بدء تحميل الرسائل إذا كان الشات موجوداً
        <?php if (!empty($chat_data)): ?>
        loadMessages();
        setInterval(loadMessages, 5000);
        <?php endif; ?>
    });
    
    // دوال القائمة الجانبية (مطابقة لـ my-enquiries.php)
    function showInbox(page) {
        window.location.href = 'my-enquiries.php?inbox=1&page=' + (page || 1);
    }
    
    function showSent(page) {
        window.location.href = 'my-enquiries.php?sent=1&page=' + (page || 1);
    }
    
    function showfolders() {
        var elem = document.getElementById('allfol');
        if (elem) {
            if (elem.style.display == 'none' || elem.style.display == '') {
                elem.style.display = 'block';
            } else {
                elem.style.display = 'none';
            }
        }
    }
    
    function newfol() {
        var elem = document.getElementById('m2_nf');
        if (elem) {
            if (elem.style.display == 'none' || elem.style.display == '') {
                elem.style.display = 'block';
            } else {
                elem.style.display = 'none';
            }
        }
    }
    
    function addfolder() {
        var folderName = document.getElementById('m2_nfn');
        if (folderName && folderName.value.trim() != '') {
            window.location.href = 'add-folder.php?name=' + encodeURIComponent(folderName.value.trim());
        } else {
            alert('الرجاء إدخال اسم المجلد');
        }
    }
    
    // دوال الشات
    <?php if (!empty($chat_data)): ?>
    var chatId = <?php echo (int)$chat_data['chat_id']; ?>;
    var currentUserId = <?php echo (int)$uid; ?>;
    var supplierId = <?php echo (int)($chat_data['supplier_id'] ?? 0); ?>;
    var buyerId = <?php echo (int)($chat_data['buyer_id'] ?? 0); ?>;
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
        fetch('chat/ajax_chat.php?action=get&chat_id=' + chatId)
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
            .catch(function(err) { console.log('خطأ في تحميل الرسائل:', err); });
    }
    
    function sendMessage() {
        var input = document.getElementById('messageInput');
        var msg = input ? input.value : '';
        if (!msg.trim()) return;
        fetch('chat/ajax_chat.php', {
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
        .catch(function(err) { console.log('خطأ في إرسال الرسالة:', err); });
    }
    
    $(document).ready(function() {
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
    });
    <?php endif; ?>
    </script>
</head>
<body>
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header (نفس my-enquiries.php) -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
        
        <!-- Menu (نفس my-enquiries.php) -->
        <?php include __DIR__ . "/includes/header_menu.php"; ?>
        
        <!-- القائمة الجانبية اليسرى (مطابقة تماماً لـ my-enquiries.php) -->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="enqulid" class="nln1" style="margin:0px; padding:0px;">
                <li>
                    <h3 style="font-size:16px; font-weight:bold; text-align:center; color:#000; margin:0; padding:18px 5px 18px 5px; background-color:#f2f2f2;">
                        مراسلات تجارتى
                    </h3>
                </li>
                
                <li class="np">
                    <a class="txtcol me inbox bnr" onclick="showInbox(1);" style="cursor:pointer;">
                        البريد المرسل الى شركتى
                    </a>
                </li>
                
                <li class="np">
                    <a class="me sent bnr" onclick="showSent(1);" style="cursor:pointer;" title="Sent Box">
                        صندوق البريد المرسل
                    </a>
                </li>
                
                <li style="border-bottom: medium none;">
                    <h3 style="height:18px;">
                        <a href="javascript:showfolders();" id="folimg" class="mf_h me bnr f1">My Folders</a>
                        <a href="javascript:newfol();" id="m2_w2nf" class=""></a>
                    </h3>
                </li>
                
                <span id="m2_nf" style="display:none;">
                    <li style="border-bottom:0;">
                        <table border="0" cellpadding="0" cellspacing="3" width="100%">
                            <tbody>
                                <tr>
                                    <td>
                                        <input class="mu11" style="width:128px; font-size:10px;" id="m2_nfn" name="m2_nfn" type="text">
                                    </td>
                                    <td width="45">
                                        <input value="Add" onclick="addfolder();" class="fadb me bnr" type="button">
                                    </td>
                                    <td width="10">
                                        <input value="" onclick="newfol();" class="me ffc bnr" type="button">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </li>
                </span>
                
                <span id="allfol" style="display:block;"></span>
            </ul>
            
            <ul id="m2_sep">&nbsp;</ul>
            
            <ul id="ulid" class="nln1" style="margin:0px; padding:0px;">
                <li style="border-bottom: medium none;" title="Address Book">
                    <h3>دفتر عناوين مراسلاتى البريدية</h3>
                </li>
                <li class="np npnew"><a href="my-addressbook.php" title="Contacts List">»&nbsp;قائمة عملائى المتصلين</a></li>
                <li class="np npnew"><a href="my-blocklist.php">»&nbsp;قائمة البلوك</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php">»&nbsp;بيانات طلبات شرائى</a></li>
                
                <li style="border-bottom: medium none; margin-top:40px;"><h2>روابط هامة</h2></li>
                <li class="np npnew"><a href="buyleads.php">شاهد طلبات شراء</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php">إدارة بيانات طلبات شرائى</a></li>
                <li class="np npnew"><a href="manage-buylead-alert.php">إدارة إشعارات طلبات شراء</a></li>
            </ul>
        </div>
        <!-- نهاية القائمة الجانبية -->
        
        <!-- محتوى الشات -->
        <div class="chat-content-wrapper">
            <?php if (empty($chat_code)): ?>
                <div style="padding:20px; text-align:center; color:red;">خطأ: لم يتم توفير كود الشات في الرابط</div>
            <?php elseif (empty($chat_data)): ?>
                <div style="padding:20px; text-align:center; color:red;">الشات غير موجود للكود: <?php echo htmlspecialchars($chat_code); ?></div>
            <?php else: ?>
                <div class="chat-container">
                    <div class="chat-header">
                        <i class="fa fa-whatsapp"></i> محادثة - <?php echo htmlspecialchars($chat_data['chat_code']); ?>
                    </div>
                    
                    <div class="quote-info">
                        <strong>المنتج:</strong> <?php echo htmlspecialchars($chat_data['product_name'] ?? 'غير محدد'); ?><br>
                        <strong>RFQ #:</strong> <?php echo htmlspecialchars($chat_data['rfq_id'] ?? 'غير محدد'); ?><br>
                        <?php if ($quote_data): ?>
                            <strong>عرض السعر:</strong><br>
                            السعر: <?php echo htmlspecialchars($quote_data['unit_price']); ?> USD<br>
                            أقل كمية: <?php echo htmlspecialchars($quote_data['moq']); ?><br>
                            مدة التوصيل: <?php echo htmlspecialchars($quote_data['delivery_time']); ?><br>
                            رسالة المورد: <?php echo nl2br(htmlspecialchars($quote_data['supplier_message'])); ?>
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
            <?php endif; ?>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- footer (نفس my-enquiries.php) -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>