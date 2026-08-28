<?php
session_start();
// سطر تشخيب بسيط
error_log("=== enq-details.php loaded ===");

require_once __DIR__ . '/../lib/connect.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm'])) {
    echo "غير مصرح";
    exit;
}

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من البيانات
if (!isset($_POST['id']) || !isset($_POST['type'])) {
    echo "بيانات غير مكتملة";
    exit;
}

$msg_id = (int)$_POST['id'];
$type = $_POST['type'];

// جلب بيانات الرسالة
$sql = "SELECT * FROM message WHERE msg_id = $msg_id";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_object($result);

if (!$row) {
    echo "الرسالة غير موجودة";
    exit;
}


// ============================================
// جلب بيانات طلب الشراء مباشرة
// ============================================
$rfq_id = $row->msg_entity_id ?? 0;
$buyer_data = null;
$rfq_data = null;

if ($rfq_id > 0) {
    // جلب بيانات الطلب والمشتري
    $sql_buyer = "SELECT br.*, u.fname, u.lname, u.mobile1, u.email, u.usr_id as buyer_id
                  FROM buy_requirement br
                  LEFT JOIN user u ON br.br_u_id = u.usr_id
                  WHERE br.br_id = $rfq_id";
    $result_buyer = mysqli_query($con, $sql_buyer);
    $rfq_data = mysqli_fetch_assoc($result_buyer);
    
    if ($rfq_data) {
        $buyer_data = $rfq_data;
    }
}

// للتشخيب - سطر مخفي في HTML
echo "<!-- RFQ ID: $rfq_id, Buyer found: " . ($buyer_data ? 'yes' : 'no') . " -->";








// تحديث حالة القراءة
mysqli_query($con, "UPDATE message SET msg_read = '1' WHERE msg_id = $msg_id");

// جلب بيانات المرسل والمستلم
$from = mysqli_fetch_object(mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_from}"));
$to = mysqli_fetch_object(mysqli_query($con, "SELECT fname, lname, email FROM user WHERE usr_id = {$row->msg_to}"));

// تحديد دور المستخدم
$is_buyer = ($current_user == $row->msg_from);
$is_supplier = ($current_user == $row->msg_to);


// جلب عروض الأسعار
$rfq_id = $row->msg_entity_id;
$offers = [];
$offers_res = mysqli_query($con, "SELECT * FROM offers WHERE rfq_id = $rfq_id ORDER BY created_at DESC");
if ($offers_res) {
    while ($offer = mysqli_fetch_assoc($offers_res)) {
        $offers[] = $offer;
    }
}

// جلب الشات
$chat_code = '';
$chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if ($chat_check && mysqli_num_rows($chat_check) > 0) {
    $chat_data = mysqli_fetch_assoc($chat_check);
    $chat_code = $chat_data['chat_code'];
}
?>

<div class="fl_m2 my_m2">
    <div class="fl_m2"></div>
</div>

<span id="fol_m2">
    <h1><?php echo ($type == 'inbox') ? 'Inbox' : 'Sent Box'; ?></h1>
</span>

<div class="fl_m2 my_m2" id="det" align="left">
    <div id="mhmdd">
        <div class="f4_m2 b5_m2 b11_m2 b10_m2 b7_m2 bg2_m2 ac_m2 p6_m2 hh_m2 b18_m2" id="mainheader">
            <div id="mailboxheader">
                <div id="selectfolder" class="box_m2">
                    <div id="mailboxoptions" class="fl_m2 lh5_m2">
                        <span id="backdrop">
                            <div class="fl_m2 p_m2" id="backto" style="width:250px">
                                <span style="cursor:pointer;" title="Back" class="sd_m2" onclick="closeDetail();">« Back</span>
                            </div>
                        </span>
                    </div>
                </div>
            </div>

            <div class="b11_m2" id="detable">
                <table class="lh2_m2" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr class="bo_m2 bg_m2 ff_m2 c1_m2 f3_m2">
                            <td class="p_m2"><b>Enquiry Information</b></td>
                        </tr>
                        <tr>
                            <td class="sh_m2">
                                <table cellpadding="0" cellspacing="0">
                                    <tr><td width="150"><b>To:</b></td><td><?php echo $to->fname . ' ' . $to->lname . ' <' . $to->email . '>'; ?></td></tr>
                                    <tr><td><b>From:</b></td><td><?php echo $from->fname . ' ' . $from->lname . ' <' . $from->email . '>'; ?></td></tr>
                                    <tr><td><b>Date:</b></td><td><?php echo $row->msg_date; ?></td></tr>
                                    <tr><td><b>Subject:</b></td><td><?php echo htmlspecialchars($row->msg_subject); ?></td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="bo_m2 bg_m2 ff_m2 c1_m2 f3_m2">
                            <td class="p_m2"><b>Message</b></td>
                        </tr>
                        <tr>
                            <td class="sh_m2">
                                <div class="message-content-modern">
                                    <?php echo nl2br(htmlspecialchars($row->msg_message)); ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>




<!-- ============================================= -->
<!-- عرض بيانات طلب الشراء (للمورد) -->
<!-- ============================================= -->
<?php if ($buyer_data && $is_supplier): ?>
<div class="buyer-details" style="margin: 20px 0; padding: 15px; background: #e8f4f8; border-radius: 8px; border-right: 4px solid #17a2b8;">
    <h4><i class="fa fa-user"></i> بيانات المشتري (صاحب الطلب)</h4>
    <table width="100%">
        <tr><td width="150"><strong>الاسم:</strong></td><td><?php echo $buyer_data['fname'] . ' ' . $buyer_data['lname']; ?></td></tr>
        <tr><td width="150"><strong>الهاتف:</strong></td><td><?php echo $buyer_data['mobile1']; ?></td></tr>
        <tr><td width="150"><strong>البريد الإلكتروني:</strong></td><td><?php echo $buyer_data['email']; ?></td></tr>
        <tr><td width="150"><strong>المنتج المطلوب:</strong></td><td><?php echo htmlspecialchars($buyer_data['br_pd_name']); ?></td></tr>
        <tr><td width="150"><strong>الكمية:</strong></td><td><?php echo $buyer_data['br_estimate_qty'] . ' ' . $buyer_data['br_estimate_qty_unit']; ?></td></tr>
        <tr><td width="150"><strong>التفاصيل:</strong></td><td><?php echo nl2br(htmlspecialchars($buyer_data['br_requirement'])); ?></td></tr>
    </table>
</div>
<?php endif; ?>








            <!-- ============================================= -->
            <!-- عروض الأسعار -->
            <!-- ============================================= -->
            <?php if (!empty($offers)): ?>
            <div class="offers-section" style="margin:20px 0; padding:15px; background:#f9f9f9; border-radius:8px;">
                <h4><i class="fa fa-tag"></i> عروض الأسعار المقدمة</h4>
                <?php foreach ($offers as $offer): 
                    $offer_id = $offer['id'];
                    $update_count = (int)($offer['update_count'] ?? 0);
                    $status = $offer['status'];
                ?>
                <div class="offer-card" style="border:1px solid #ddd; padding:15px; margin:10px 0; border-radius:8px; background:#fff;">
                    <div><strong><i class="fa fa-dollar"></i> السعر:</strong> <?php echo $offer['price'] . ' ' . $offer['currency']; ?></div>
                    <div><strong><i class="fa fa-truck"></i> مدة التوصيل:</strong> <?php echo $offer['delivery_days']; ?> يوم</div>
                    <div><strong><i class="fa fa-comment"></i> الملاحظات:</strong> <?php echo nl2br(htmlspecialchars($offer['notes'] ?? '')); ?></div>
                    
                    <?php if ($status == 'accepted'): ?>
                        <div class="label label-success" style="color:green;">✓ تم قبول هذا العرض</div>
                    <?php elseif ($status == 'rejected'): ?>
                        <div class="label label-danger" style="color:red;">✗ تم رفض هذا العرض</div>
                    
                  
                    
                  <?php elseif ($is_supplier): ?>
    <div class="offer-actions" style="margin-top:15px;">
        <?php if ($update_count == 0): ?>
            <button class="btn btn-success btn-sm" onclick="showSendOfferForm(<?php echo $rfq_id; ?>, <?php echo $offer_id; ?>)">
                <i class="fa fa-money"></i> إرسال عرض سعر
            </button>
        <?php elseif ($update_count == 1): ?>
            <button class="btn btn-warning btn-sm" onclick="showSendOfferForm(<?php echo $rfq_id; ?>, <?php echo $offer_id; ?>)">
                <i class="fa fa-edit"></i> تعديل عرض السعر (مرة أخيرة)
            </button>
        <?php else: ?>
            <span class="label label-default">تم تحديث السعر مرتين. لا يمكن التعديل مرة أخرى.</span>
        <?php endif; ?>
        
        <?php if ($chat_exists && $chat_code): ?>
            <button class="btn btn-info btn-sm" onclick="openChat('<?php echo $chat_code; ?>')">
                <i class="fa fa-comments"></i> فتح المحادثة
            </button>
        <?php endif; ?>
    </div>
                            
                            
                            
                            <?php if ($chat_code): ?>
                                <button class="btn btn-info" onclick="window.open('/chat/chat.php?chat_code=<?php echo $chat_code; ?>', '_blank')">
                                    <i class="fa fa-comments"></i> فتح المحادثة
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($is_buyer): ?>
                        <div style="margin-top:15px;">
                            <button class="btn btn-success" onclick="acceptOffer(<?php echo $offer_id; ?>, <?php echo $rfq_id; ?>)">
                                <i class="fa fa-check"></i> قبول العرض
                            </button>
                            <button class="btn btn-danger" onclick="rejectOffer(<?php echo $offer_id; ?>, <?php echo $rfq_id; ?>)">
                                <i class="fa fa-times"></i> رفض العرض
                            </button>
                            <?php if ($chat_code): ?>
                                <button class="btn btn-info" onclick="window.open('/chat/chat.php?chat_code=<?php echo $chat_code; ?>', '_blank')">
                                    <i class="fa fa-comments"></i> فتح المحادثة
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function sendOffer(rfqId, offerId) {
    let price = prompt('السعر (USD):');
    if (!price) return;
    let delivery = prompt('مدة التوصيل (أيام):');
    if (!delivery) return;
    let notes = prompt('ملاحظات:', '');
    
    fetch('/lib/update_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&price=' + price + '&delivery_days=' + delivery + '&notes=' + encodeURIComponent(notes) + '&offer_id=' + offerId
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert(d.message);
            if (d.whatsapp_url) window.open(d.whatsapp_url, '_blank');
            location.reload();
        } else alert('خطأ: ' + d.error);
    })
    .catch(e => alert('خطأ: ' + e.message));
}

// ============================================
// دوال المشتري (قبول / رفض العرض) - نسخة مبسطة وآمنة
// ============================================
function acceptOffer(offerId, rfqId) {
    if (!confirm('✅ هل أنت متأكد من قبول هذا العرض؟\n\nبعد القبول سيتم كشف بيانات المورد (الهاتف، البريد الإلكتروني، اسم الشركة).')) {
        return;
    }

    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId + '&rfq_id=' + rfqId + '&action=accept'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('خطأ في الاتصال بالخادم: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            if (data.supplier_data) {
                let info = '📋 بيانات المورد:\n\n';
                info += '🏢 الشركة: ' + (data.supplier_data.company_name || 'غير متوفر') + '\n';
                info += '📞 الهاتف: ' + (data.supplier_data.phone || 'غير متوفر') + '\n';
                info += '📧 البريد: ' + (data.supplier_data.email || 'غير متوفر');
                alert(info);
            }
            // إعادة تحميل الصفحة لعرض البيانات الجديدة
            location.reload();
        } else {
            alert('❌ فشل قبول العرض: ' + (data.error || 'حدث خطأ غير معروف'));
        }
    })
    .catch(error => {
        console.error('Error in acceptOffer:', error);
        alert('❌ خطأ في الاتصال بالخادم: ' + error.message);
    });
}

function rejectOffer(offerId, rfqId) {
    if (!confirm('❌ هل أنت متأكد من رفض هذا العرض؟')) {
        return;
    }

    fetch('/lib/accept_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId + '&rfq_id=' + rfqId + '&action=reject'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('خطأ في الاتصال بالخادم: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('✅ تم رفض العرض بنجاح');
            location.reload();
        } else {
            alert('❌ فشل رفض العرض: ' + (data.error || 'حدث خطأ غير معروف'));
        }
    })
    .catch(error => {
        console.error('Error in rejectOffer:', error);
        alert('❌ خطأ في الاتصال بالخادم: ' + error.message);
    });
}

function closeDetail() {
    $('#mail-details').html('');
    $('#mail-details').hide();
    $('#mail').show();
}


// ============================================
// دالة إرسال عرض السعر (خطوة بخطوة)
// ============================================
function showSendOfferForm(rfqId, offerId) {
    // 1. إدخال السعر
    let price = prompt('💰 أدخل السعر (USD):');
    if (!price) return;
    
    // 2. إدخال أقل كمية (MOQ)
    let moq = prompt('📦 أدخل أقل كمية (MOQ):', '1');
    if (!moq) return;
    
    // 3. إدخال مدة التوصيل
    let delivery = prompt('🚚 أدخل مدة التوصيل (أيام):');
    if (!delivery) return;
    
    // 4. إدخال ملاحظات إضافية
    let notes = prompt('📝 ملاحظات إضافية (اختياري):', '');
    
    // تأكيد الإرسال
    let confirmMsg = `تأكيد بيانات عرض السعر:\n\n`;
    confirmMsg += `💰 السعر: ${price} USD\n`;
    confirmMsg += `📦 أقل كمية: ${moq}\n`;
    confirmMsg += `🚚 مدة التوصيل: ${delivery} يوم\n`;
    confirmMsg += `📝 الملاحظات: ${notes || 'لا توجد'}\n\n`;
    confirmMsg += `هل أنت متأكد من إرسال هذا العرض؟`;
    
    if (!confirm(confirmMsg)) return;
    
    // إرسال البيانات إلى الخادم
    fetch('/lib/update_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + 
              '&price=' + encodeURIComponent(price) + 
              '&moq=' + encodeURIComponent(moq) + 
              '&delivery_days=' + encodeURIComponent(delivery) + 
              '&notes=' + encodeURIComponent(notes || '') + 
              '&offer_id=' + offerId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 1. عرض رسالة نجاح
            alert('✅ ' + data.message);
            
            // 2. فتح زر المحادثة (سيظهر بعد إعادة التحميل)
            if (data.chat_code) {
                alert('💬 تم فتح المحادثة مع المشتري');
            }
            
            // 3. فتح واتساب المشتري (للإرسال اليدوي فقط)
            if (data.whatsapp_url) {
                let openWhatsApp = confirm('📱 هل تريد فتح واتساب لإرسال عرض السعر يدوياً للمشتري؟');
                if (openWhatsApp) {
                    window.open(data.whatsapp_url, '_blank');
                }
            }
            
            // إعادة تحميل الصفحة لعرض زر المحادثة
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ خطأ في الاتصال: ' + error.message);
    });
}
</script>

<style>
.message-content-modern {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 10px;
    margin: 10px 0;
}
.btn { padding: 5px 12px; margin: 3px; border: none; border-radius: 4px; cursor: pointer; }
.btn-success { background: #28a745; color: white; }
.btn-danger { background: #dc3545; color: white; }
.btn-warning { background: #ffc107; color: black; }
.btn-info { background: #17a2b8; color: white; }
.offers-section { margin: 20px 0; }
.offer-card { margin: 10px 0; }
</style>

<?php
if (isset($stmt_to) && $stmt_to !== null && is_object($stmt_to)) {
    mysqli_stmt_close($stmt_to);
}
?>