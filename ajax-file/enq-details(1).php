<?php
/**
 * File: ajax/enq-details.php
 * Description: عرض تفاصيل رسالة مع إمكانية الرد عليها وعرض عروض الأسعار
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    die("Unauthorized");
}

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من وجود معرف الرسالة والنوع
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !isset($_POST['type'])) {
    http_response_code(400);
    die("Invalid request parameters");
}

$msg_id = (int)$_POST['id'];
$type = trim($_POST['type']);

global $con;

// جلب بيانات الرسالة
$sql = "SELECT * FROM message WHERE msg_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $msg_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Message not found");
}

// ============================================
// تحديد دور المستخدم
// ============================================
$is_buyer = ($current_user == $row->msg_from);
$is_supplier = ($current_user == $row->msg_to);

// تحديث حالة القراءة
$sql_upd = "UPDATE message SET msg_read = '1' WHERE msg_id = ?";
$stmt_upd = mysqli_prepare($con, $sql_upd);
mysqli_stmt_bind_param($stmt_upd, 'i', $msg_id);
mysqli_stmt_execute($stmt_upd);
mysqli_stmt_close($stmt_upd);

// جلب بيانات المستلم
$sql_to = "SELECT fname, lname, email FROM user WHERE usr_id = ? LIMIT 1";
$stmt_to = mysqli_prepare($con, $sql_to);
mysqli_stmt_bind_param($stmt_to, 'i', $row->msg_to);
mysqli_stmt_execute($stmt_to);
$result_to = mysqli_stmt_get_result($stmt_to);
$row_to = mysqli_fetch_object($result_to);
mysqli_stmt_close($stmt_to);

// جلب بيانات المرسل
$sql_from = "SELECT fname, lname, email FROM user WHERE usr_id = ? LIMIT 1";
$stmt_from = mysqli_prepare($con, $sql_from);
mysqli_stmt_bind_param($stmt_from, 'i', $row->msg_from);
mysqli_stmt_execute($stmt_from);
$result_from = mysqli_stmt_get_result($stmt_from);
$row_from = mysqli_fetch_object($result_from);
mysqli_stmt_close($stmt_from);

// تنظيف البيانات للعرض
$to_name = htmlspecialchars(trim(($row_to->fname ?? '') . ' ' . ($row_to->lname ?? '')), ENT_QUOTES, 'UTF-8');
$to_email = htmlspecialchars($row_to->email ?? '', ENT_QUOTES, 'UTF-8');
$from_name = htmlspecialchars(trim(($row_from->fname ?? '') . ' ' . ($row_from->lname ?? '')), ENT_QUOTES, 'UTF-8');
$from_email = htmlspecialchars($row_from->email ?? '', ENT_QUOTES, 'UTF-8');
$msg_subject = !empty($row->msg_subject) ? htmlspecialchars($row->msg_subject, ENT_QUOTES, 'UTF-8') : 'No Subject';
$msg_message = $row->msg_message ?? '';
$msg_date = !empty($row->msg_date) ? date("d-M-Y H:i:s A", strtotime($row->msg_date)) . ' ' . date('T') : 'N/A';

// ============================================
// جلب بيانات طلب الشراء (RFQ)
// ============================================
$rfq_id = $row->msg_entity_id ?? 0;
$rfq_data = null;
if ($rfq_id > 0) {
    $rfq_sql = "SELECT br.*, u.fname, u.lname, u.mobile1, u.email 
                FROM buy_requirement br
                LEFT JOIN user u ON br.br_u_id = u.usr_id
                WHERE br.br_id = $rfq_id";
    $rfq_result = mysqli_query($con, $rfq_sql);
    $rfq_data = mysqli_fetch_assoc($rfq_result);
}

// ============================================
// جلب عروض الأسعار
// ============================================
$offers = [];
if ($rfq_id > 0) {
    $offers_sql = "SELECT o.*, 
                          s.fname as supplier_fname, s.lname as supplier_lname,
                          bp.bnsprof_compname as supplier_company
                   FROM offers o
                   LEFT JOIN user s ON o.supplier_id = s.usr_id
                   LEFT JOIN business_profile bp ON s.usr_id = bp.bnsprof_uid
                   WHERE o.rfq_id = $rfq_id
                   ORDER BY o.created_at DESC";
    $offers_res = mysqli_query($con, $offers_sql);
    while ($offer_row = mysqli_fetch_assoc($offers_res)) {
        $offers[] = $offer_row;
    }
}

// ============================================
// جلب الشات
// ============================================
$chat_code = '';
$chat_exists = false;
$chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = $rfq_id LIMIT 1");
if ($chat_check && mysqli_num_rows($chat_check) > 0) {
    $chat_data = mysqli_fetch_assoc($chat_check);
    $chat_code = $chat_data['chat_code'];
    $chat_exists = true;
}
?>

<!-- CSS -->
<style type="text/css">
    #wbr>div>div:last-child>div>div:last-child { position: relative; left: 10px; background: transparent; }
    #wbr>div>div:last-child>div>div:last-child>div { background: transparent; }
</style>
<style>
.message-content-modern {
    font-size: 13px;
    line-height: 1.5;
    white-space: pre-wrap;
    word-wrap: break-word;
    background: #f9f9f9;
    padding: 12px;
    border-radius: 10px;
    border-right: 3px solid #25D366;
    margin: 15px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
    direction: rtl;
    text-align: right;
}
.offers-section { margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 8px; }
.offer-card { border: 1px solid #ddd; padding: 10px; margin: 8px 0; border-radius: 8px; background: #fff; }
.offer-card div { margin-bottom: 5px; font-size: 13px; }
.btn { padding: 4px 10px; margin: 3px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
.btn-success { background: #28a745; color: #fff; }
.btn-danger { background: #dc3545; color: #fff; }
.btn-warning { background: #ffc107; color: #000; }
.btn-info { background: #17a2b8; color: #fff; }
.buyer-details { margin: 15px 0; padding: 10px; background: #e8f4f8; border-radius: 8px; border-right: 3px solid #17a2b8; }
.buyer-details table { width: 100%; }
.buyer-details td { padding: 4px; font-size: 13px; }
.buyer-details td:first-child { width: 120px; font-weight: bold; }
</style>

<!-- JavaScript -->
<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="fl_m2 my_m2">
    <div class="fl_m2"></div>
</div>

<span id="fol_m2">
    <h1><?php echo ($type == 'inbox') ? 'Inbox' : 'Sent Box'; ?></h1>
</span>

<div class="fl_m2 my_m2" id="det" align="left">
    <br>
    <div id="yeartabs"><div style="height:28px; border-bottom:1px solid #FFD9D9;"></div></div>

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

            <?php if ($type == 'inbox'): ?>
            
            <!-- ============================================= -->
            <!-- 1. معلومات الرسالة الأساسية -->
            <!-- ============================================= -->
            <div class="b11_m2" id="detable">
                <table class="lh2_m2" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size:13px;">
                    <tbody>
                        <tr class="bo_m2 bg_m2 ff_m2 c1_m2 f3_m2">
                            <td class="p_m2"><b>Enquiry Information</b></td>
                        </tr>
                        <tr>
                            <td class="sh_m2">
                                <table cellpadding="0" cellspacing="0" style="width:100%;">
                                    <tr><td width="80"><b>To:</b></td><td><?php echo $to_name; ?> &lt;<?php echo $to_email; ?>&gt;</td></tr>
                                    <tr><td width="80"><b>From:</b></td><td><?php echo $from_name; ?> &lt;<?php echo $from_email; ?>&gt;</td></tr>
                                    <tr><td width="80"><b>Date:</b></td><td><?php echo $msg_date; ?></td></tr>
                                    <tr><td width="80"><b>Subject:</b></td><td><?php echo $msg_subject; ?></td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="bo_m2 bg_m2 ff_m2 c1_m2 f3_m2">
                            <td class="p_m2"><b>Message</b></td>
                        </tr>
                        <tr class="f5_m2">
                            <td class="sh_m2">
                                <div class="message-content-modern">
                                    <?php echo nl2br(htmlspecialchars($msg_message)); ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ============================================= -->
            <!-- 2. بيانات طلب الشراء (للمورد فقط - بدون تكرار) -->
            <!-- ============================================= -->
            <?php if ($rfq_data && $is_supplier): ?>
            <div class="buyer-details">
                <h4 style="margin:0 0 8px 0; font-size:14px;"><i class="fa fa-shopping-cart"></i> تفاصيل طلب الشراء</h4>
                <table>
                    <tr><td>المنتج:</td><td><?php echo htmlspecialchars($rfq_data['br_pd_name']); ?></td></tr>
                    <tr><td>الكمية:</td><td><?php echo $rfq_data['br_estimate_qty'] . ' ' . $rfq_data['br_estimate_qty_unit']; ?></td></tr>
                    <tr><td>التفاصيل:</td><td><?php echo nl2br(htmlspecialchars($rfq_data['br_requirement'])); ?></td></tr>
                    <tr><td>المشتري:</td><td><?php echo $rfq_data['fname'] . ' ' . $rfq_data['lname']; ?></td></tr>
                    <tr><td>هاتف المشتري:</td><td><?php echo $rfq_data['mobile1']; ?></td></tr>
                    <tr><td>بريد المشتري:</td><td><?php echo $rfq_data['email']; ?></td></tr>
                </table>
            </div>
            <?php endif; ?>

            <!-- ============================================= -->
            <!-- 3. عروض الأسعار (زر واحد ذكي) -->
            <!-- ============================================= -->
            <?php if (!empty($offers)): ?>
            <div class="offers-section">
                <h4 style="margin:0 0 8px 0; font-size:14px;"><i class="fa fa-tag"></i> عروض الأسعار المقدمة</h4>
                <?php foreach ($offers as $offer): 
                    $offer_id = $offer['id'];
                    $update_count = (int)($offer['update_count'] ?? 0);
                    $status = $offer['status'];
                    $supplier_name = $offer['supplier_company'] ?? ($offer['supplier_fname'] . ' ' . $offer['supplier_lname']);
                ?>
                <div class="offer-card">
                    <div><strong>المورد:</strong> <?php echo htmlspecialchars($supplier_name); ?></div>
                    <div><strong>السعر:</strong> <?php echo $offer['price'] . ' ' . $offer['currency']; ?></div>
                    <div><strong>مدة التوصيل:</strong> <?php echo $offer['delivery_days']; ?> يوم</div>
                    <div><strong>الملاحظات:</strong> <?php echo nl2br(htmlspecialchars($offer['notes'] ?? '')); ?></div>

                    <?php if ($status == 'accepted'): ?>
                        <div class="label label-success" style="color:green; margin-top:8px; font-size:12px;">✓ تم قبول هذا العرض</div>
                    <?php elseif ($status == 'rejected'): ?>
                        <div class="label label-danger" style="color:red; margin-top:8px; font-size:12px;">✗ تم رفض هذا العرض</div>
                    <?php elseif ($is_supplier): ?>
                        <div style="margin-top:10px;">
                            <?php if ($update_count == 0): ?>
                                <button class="btn btn-warning" onclick="updateOffer(<?php echo $rfq_id; ?>, <?php echo $offer_id; ?>, 1)">
                                    <i class="fa fa-edit"></i> تعديل عرض السعر (الفرصة الأولى)
                                </button>
                            <?php elseif ($update_count == 1): ?>
                                <button class="btn btn-danger" onclick="updateOffer(<?php echo $rfq_id; ?>, <?php echo $offer_id; ?>, 2)">
                                    <i class="fa fa-exclamation-triangle"></i> تعديل عرض السعر (الفرصة الأخيرة)
                                </button>
                            <?php else: ?>
                                <span class="label label-default" style="font-size:11px;">⚠️ تم تعديل السعر مرتين - لا يمكن التعديل مرة أخرى</span>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($is_buyer): ?>
                        <div style="margin-top:10px;">
                            <button class="btn btn-success accept-offer" data-offer-id="<?php echo $offer_id; ?>" data-rfq-id="<?php echo $rfq_id; ?>">
                                <i class="fa fa-check"></i> قبول العرض
                            </button>
                            <button class="btn btn-danger reject-offer" data-offer-id="<?php echo $offer_id; ?>" data-rfq-id="<?php echo $rfq_id; ?>">
                                <i class="fa fa-times"></i> رفض العرض
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($is_supplier && $rfq_id > 0): ?>
           
            <!-- ============================================ -->
            <!-- أزرار المورد (يظهر فقط للمورد) -->
            <!-- ============================================ -->
<?php if ($is_supplier && $rfq_id > 0): ?>
    <button class="btn btn-success" onclick="sendOffer(<?php echo $rfq_id; ?>)">
        <i class="fa fa-money"></i> إرسال عرض سعر
    </button>
<?php endif; ?>

            <!-- ============================================= -->
            <!-- 4. زر فتح المحادثة -->
            <!-- ============================================= -->
            <?php if ($chat_exists && $chat_code): ?>
            <div class="fl_m2" style="margin-top:10px;">
                <button class="btn btn-info" onclick="window.open('/chat/chat.php?chat_code=<?php echo $chat_code; ?>', '_blank')">
                    <i class="fa fa-comments"></i> فتح المحادثة
                </button>
            </div>
            <?php endif; ?>

            <div class="fl_m2" style="border-right:1px solid #85B3D5; height:24px"></div>
            <?php endif; ?>
        </div>
        <div class="b11_m2" id="ci_m2"><br></div>
    </div>
</div>

<script>
// ============================================
// إنشاء عرض جديد (مرة واحدة فقط)
// ============================================
function createNewOffer(rfqId) {
    let price = prompt('💰 أدخل السعر (USD):');
    if (!price) return;
    let delivery = prompt('🚚 أدخل مدة التوصيل (أيام):');
    if (!delivery) return;
    let notes = prompt('📝 ملاحظات إضافية (اختياري):', '');

    let confirmMsg = `تأكيد عرض السعر الجديد:\n\n`;
    confirmMsg += `💰 السعر: ${price} USD\n`;
    confirmMsg += `🚚 مدة التوصيل: ${delivery} يوم\n`;
    confirmMsg += `📝 الملاحظات: ${notes || 'لا توجد'}\n\n`;
    confirmMsg += `⚠️ ملاحظة: هذا هو عرضك الأول. سيكون لديك فرصتين فقط للتعديل بعد هذا.`;

    if (!confirm(confirmMsg)) return;

    fetch('/lib/update_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&price=' + price + '&delivery_days=' + delivery + '&notes=' + encodeURIComponent(notes || '') + '&offer_id=0'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            if (data.whatsapp_url) window.open(data.whatsapp_url, '_blank');
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => alert('خطأ في الاتصال: ' + error.message));
}

// ============================================
// تعديل عرض موجود (الفرصة الأولى أو الأخيرة)
// ============================================
function updateOffer(rfqId, offerId, chanceNumber) {
    let warningMsg = '';
    if (chanceNumber == 1) {
        warningMsg = '⚠️ هذا هو تعديلك الأول. لا يزال لديك فرصة واحدة أخرى للتعديل بعد هذا.\n\n';
    } else if (chanceNumber == 2) {
        warningMsg = '🔴 هذا هو التعديل الأخير المسموح به! لن تتمكن من تعديل السعر مرة أخرى.\n\n';
    }
    
    let price = prompt(warningMsg + '💰 السعر الجديد (USD):');
    if (!price) return;
    let delivery = prompt('🚚 مدة التوصيل الجديدة (أيام):');
    if (!delivery) return;
    let notes = prompt('📝 ملاحظات إضافية (اختياري):', '');

    fetch('/lib/update_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&price=' + price + '&delivery_days=' + delivery + '&notes=' + encodeURIComponent(notes || '') + '&offer_id=' + offerId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            if (data.whatsapp_url) window.open(data.whatsapp_url, '_blank');
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => alert('خطأ في الاتصال: ' + error.message));
}

// ============================================
// دوال المشتري (قبول / رفض العرض)
// ============================================
$(document).ready(function() {
    $('.accept-offer').click(function() {
        let offerId = $(this).data('offer-id');
        let rfqId = $(this).data('rfq-id');
        if (confirm('✅ قبول هذا العرض؟ سيتم كشف بيانات المورد.')) {
            $.post('/lib/accept_offer.php', {offer_id: offerId, rfq_id: rfqId, action: 'accept'}, function(response) {
                if (response.success) {
                    alert('✅ ' + response.message);
                    if (response.supplier_data) {
                        alert('📋 بيانات المورد:\n🏢 ' + response.supplier_data.company_name + '\n📞 ' + response.supplier_data.phone);
                    }
                    location.reload();
                } else {
                    alert('❌ ' + response.error);
                }
            }, 'json');
        }
    });

    $('.reject-offer').click(function() {
        let offerId = $(this).data('offer-id');
        let rfqId = $(this).data('rfq-id');
        if (confirm('❌ رفض هذا العرض؟')) {
            $.post('/lib/accept_offer.php', {offer_id: offerId, rfq_id: rfqId, action: 'reject'}, function(response) {
                if (response.success) {
                    alert('✅ تم رفض العرض');
                    location.reload();
                } else {
                    alert('❌ ' + response.error);
                }
            }, 'json');
        }
    });
});

function closeDetail() {
    $('#mail-details').html('');
    $('#mail-details').hide();
    $('#mail').show();
}
</script>

<?php
// لا داعي لإغلاق stmt_to مرة أخرى - تم إغلاقه مسبقاً
?>