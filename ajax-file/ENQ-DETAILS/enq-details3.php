<?php
/**
 * File: ajax/enq-details.php
 *
 * Description: عرض تفاصيل رسالة مع إمكانية الرد عليها
 * Version: 2.0.0 (PHP 8.3 Compatible)
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

// تحديث حالة القراءة
$sql_upd = "UPDATE message SET msg_read = '1' WHERE msg_id = ?";
$stmt_upd = mysqli_prepare($con, $sql_upd);
mysqli_stmt_bind_param($stmt_upd, 'i', $msg_id);
mysqli_stmt_execute($stmt_upd);
mysqli_stmt_close($stmt_upd);

// جلب بيانات الشركة للمرسل
$row_comp = null;
if (!empty($row->msg_from)) {
    $sql_comp = "SELECT * FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
    $stmt_comp = mysqli_prepare($con, $sql_comp);
    mysqli_stmt_bind_param($stmt_comp, 'i', $row->msg_from);
    mysqli_stmt_execute($stmt_comp);
    $result_comp = mysqli_stmt_get_result($stmt_comp);
    $row_comp = mysqli_fetch_object($result_comp);
    mysqli_stmt_close($stmt_comp);
}

// جلب بيانات المستلم
$sql_to = "SELECT fname, lname, email FROM user WHERE usr_id = ? LIMIT 1";
$stmt_to = mysqli_prepare($con, $sql_to);

if ($stmt_to) {
    mysqli_stmt_bind_param($stmt_to, 'i', $row->msg_to);
    mysqli_stmt_execute($stmt_to);
    $result_to = mysqli_stmt_get_result($stmt_to);
    $row_to = mysqli_fetch_object($result_to);
    mysqli_stmt_close($stmt_to);
    unset($stmt_to);
}

// جلب بيانات المرسل
$from_admin = 0;
$row_from = null;
$admin_username = '';
$admin_email = '';

$sql_from_user = "SELECT fname, lname, email FROM user WHERE usr_id = ? LIMIT 1";
$stmt_from_user = mysqli_prepare($con, $sql_from_user);
mysqli_stmt_bind_param($stmt_from_user, 'i', $row->msg_from);
mysqli_stmt_execute($stmt_from_user);
$result_from_user = mysqli_stmt_get_result($stmt_from_user);

if (mysqli_num_rows($result_from_user) > 0) {
    $row_from = mysqli_fetch_object($result_from_user);
} else {
    $from_admin = 1;
    $sql_from_admin = "SELECT username, email FROM admin_user WHERE id = ? LIMIT 1";
    $stmt_from_admin = mysqli_prepare($con, $sql_from_admin);
    mysqli_stmt_bind_param($stmt_from_admin, 'i', $row->msg_from);
    mysqli_stmt_execute($stmt_from_admin);
    $result_from_admin = mysqli_stmt_get_result($stmt_from_admin);
    $admin_data = mysqli_fetch_object($result_from_admin);
    if ($admin_data) {
        $admin_username = $admin_data->username ?? '';
        $admin_email = $admin_data->email ?? '';
    }
    mysqli_stmt_close($stmt_from_admin);
}
mysqli_stmt_close($stmt_from_user);

// جلب المرفقات
$sql_ma = "SELECT ma_file FROM message_attachment WHERE ma_msg_id = ?";
$stmt_ma = mysqli_prepare($con, $sql_ma);
mysqli_stmt_bind_param($stmt_ma, 'i', $msg_id);
mysqli_stmt_execute($stmt_ma);
$result_ma = mysqli_stmt_get_result($stmt_ma);
$attachments = [];
while ($row_ma = mysqli_fetch_object($result_ma)) {
    $attachments[] = $row_ma;
}
mysqli_stmt_close($stmt_ma);

// تنظيف البيانات للعرض
$to_name = htmlspecialchars(trim(($row_to->fname ?? '') . ' ' . ($row_to->lname ?? '')), ENT_QUOTES, 'UTF-8');
$to_email = htmlspecialchars($row_to->email ?? '', ENT_QUOTES, 'UTF-8');

$from_name = '';
$from_email = '';
if ($from_admin == 1) {
    $from_name = htmlspecialchars($admin_username, ENT_QUOTES, 'UTF-8');
    $from_email = htmlspecialchars($admin_email, ENT_QUOTES, 'UTF-8');
} elseif ($row_from) {
    $from_name = htmlspecialchars(trim(($row_from->fname ?? '') . ' ' . ($row_from->lname ?? '')), ENT_QUOTES, 'UTF-8');
    $from_email = htmlspecialchars($row_from->email ?? '', ENT_QUOTES, 'UTF-8');
}

$msg_subject = !empty($row->msg_subject) 
    ? htmlspecialchars($row->msg_subject, ENT_QUOTES, 'UTF-8') 
    : 'No Subject';

$msg_message = $row->msg_message ?? '';
if ($from_admin == 1) {
    $msg_message = html_entity_decode($msg_message);
}
$msg_message = stripslashes($msg_message);
$msg_date = !empty($row->msg_date) 
    ? date("d-M-Y H:i:s A", strtotime($row->msg_date)) . ' ' . date('T') 
    : 'N/A';
?>

<!-- CSS -->
<style type="text/css">
    #wbr>div>div:last-child>div>div:last-child {
        position: relative;
        left: 10px;
        background: transparent;
    }
    #wbr>div>div:last-child>div>div:last-child>div {
        background: transparent;
    }
</style>
<style>
.message-content {
    font-size: 18px;
    line-height: 1.8;
    white-space: pre-wrap;
    word-wrap: break-word;
    background: #f9f9f9;
    padding: 15px;
    border-radius: 10px;
    border-right: 4px solid #25D366;
    margin: 10px 0;
}
.message-content p {
    margin: 0 0 10px 0;
}
.message-content strong {
    color: #25D366;
}
</style>
<style>
.message-content-modern {
    font-size: 14px;
    line-height: .8;
    white-space: pre-wrap;
    word-wrap: break-word;
    background: #f9f9f9;
    padding: 20px;
    border-radius: 15px;
    border-right: 5px solid #25D366;
    border-left: none;
    margin: 20px 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    color: #2c3e50;
    direction: rtl;
    text-align: right;
}
.message-content-modern p {
    margin: 0 0 12px 0;
}
.message-content-modern strong {
    color: #25D366;
}
.message-content-modern ul, 
.message-content-modern ol {
    padding-right: 20px;
    margin: 10px 0;
}
.message-content-modern a {
    color: #25D366;
    text-decoration: none;
}
.message-content-modern a:hover {
    text-decoration: underline;
}
</style>

<!-- JavaScript -->
<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">

<script>
$(document).ready(function() {
    $('body').on('click', '.ajax', function(event) {
        $.colorbox({
            href: $(this).attr('href'),
            open: true, 
            width: '750px'
        });
        return false;
    });
});
</script>

<div class="fl_m2 my_m2">
    <div class="fl_m2">
        <!--div class="bc f11">Enquiries »</div-->
    </div>
</div>

<span id="fol_m2">
    <h1><?php echo ($type == 'inbox') ? 'Inbox' : 'Sent Box'; ?></h1>
</span>

<div class="fl_m2 my_m2" id="det" align="left">
    <br>

    <div id="yeartabs">
        <div style="height:28px; border-bottom:1px solid #FFD9D9;"></div>
    </div>

    <div class="b11_m2 tmsg_m2" id="dymesg" align="center">
        <div id="loading_det" class="c2_m2 bo_m2 lh_m2" style="width:15%; display:none;">
            <img src="images/my2-loading.gif" class="loading_m2"> Loading... 
        </div>
        <div id="noselect" class="c2_m2 bo_m2 lh_m2" style="display:none; width:25%">
             No Enquiry Selected. 
        </div>
        <div id="fc_m2" class="c2_m2 bo_m2 lh_m2" style="display:none; width:25%">
             Folder has been created. 
        </div>
        <div id="fr_m2" class="c2_m2 bo_m2 lh_m2" style="display:none; width:25%">
             Folder has been renamed. 
        </div>
        <div id="fd_m2" class="c2_m2 bo_m2 lh_m2" style="display:none; width:25%">
             Folder has been deleted. 
        </div>
    </div>

    <div id="pnavsec">
        <div class="b11_m2 b7_m2">
            <span class="pagenavigation"></span>
            <div id="new_m2" class="fl_m2 p9_m2" style="padding-left:40px"></div>
            <div style="clear:both;"></div>
        </div>
    </div>

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

                    <div class="fl_m2 lh5_m2" id="mailoption">
                        <span id="reply_m2"></span>
                        <div class="fl_m2 hh_m2 b17_m2" style="" id="muldiv"></div>

                        <div id="delete" class="horizontalcssmenu_delete_m2 horizontalcssmenu_m2 fl_m2 lh4_m2">
                            <ul id="delete_m2" style="margin-top:0px; padding-top:0pt;">
                                <li style="z-index:0; margin:0px; padding:0pt;"> 
                                    <span id="deleteall_m2">
                                        <a title="Delete" iscontextmenu="true" onclick="delMessage(<?php echo $msg_id; ?>);"></a>
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div class="fl_m2" id="derdiv" style="border-right:1px solid #85B3D5; height:24px">  </div>

                        <div id="showmandiv" style="height:22px; padding-top:2px; display:none;" class="fl_m2 b17_m2" align="center">
                              <span id="showmanage"></span>  
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($type == 'inbox'): ?>
            <div id="movetocombox">
                <div id="movetoptions" title="Move To">
                    <div id="movedropdown" class="horizontalcssmenu_move_m2 horizontalcssmenu_m2 fl_m2">
                        <ul id="my2cssmenu" style="margin-top:0px; padding-top:0;">
                            <li style="z-index:0; margin:0px; padding:0;" align="center"> 
                                <span id="moveto_m2">
                                    <?php if ($row->msg_entity == 'membership_plan' || $row->msg_entity == 'membership_requirement'): ?>
                                        <a href="membership_plans.php" style="text-decoration:none; color:#666">
                                            <span class=""><div title="WEB" class="td_m2 m2_sym m2_sym3">Reply</div></span>
                                        </a>
                                    <?php elseif ($row->msg_entity == 'advertisement_requirement'): ?>
                                        <a href="advertise-with-us.php" style="text-decoration:none; color:#666">
                                            <span class=""><div title="WEB" class="td_m2 m2_sym m2_sym3">Reply</div></span>
                                        </a>
                                    <?php else: ?>
                                        <a href="sendmessage-form.php?id=<?php echo (int)$row->msg_from; ?>" 
                                           style="text-decoration:none; color:#666" 
                                           iscontextmenu="true" class="ajax" rel="nofollow">
                                            <span class=""><div title="WEB" class="td_m2 m2_sym m2_sym3">Reply</div></span>
                                        </a>
                                    <?php endif; ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- عروض الأسعار (للمورد والمشتري حسب الدور) -->
            <?php
            $rfq_id = $row->msg_entity_id ?? 0;
            $current_user_id = $_SESSION['uid_indm'] ?? 0;
            $is_supplier = ($current_user_id == $row->msg_to);

            if ($rfq_id > 0) {
                $offers_sql = "SELECT o.*, 
                                      s.fname as supplier_fname, s.lname as supplier_lname,
                                      bp.bnsprof_compname as supplier_company
                               FROM offers o
                               LEFT JOIN user s ON o.supplier_id = s.usr_id
                               LEFT JOIN business_profile bp ON s.usr_id = bp.bnsprof_uid
                               WHERE o.rfq_id = $rfq_id AND o.status IN ('notified', 'pending', 'negotiation')
                               ORDER BY o.created_at DESC";
                $offers_res = mysqli_query($con, $offers_sql);
                $offers = mysqli_fetch_all($offers_res, MYSQLI_ASSOC);

                if (!empty($offers)): ?>
                    <div class="offers-section" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;">
                        <h4><i class="fa fa-tag"></i> عروض الأسعار المقدمة</h4>
                        <?php foreach ($offers as $offer): 
                            $update_count = $offer['update_count'] ?? 0;
                            $offer_id = $offer['id'];
                        ?>
                            <div class="offer-card" style="border:1px solid #ddd; padding:15px; margin:10px 0; border-radius:8px; background:#fff;">
                                <div style="margin-bottom:10px;">
                                    <strong><i class="fa fa-building"></i> المورد:</strong> 
                                    <?php echo htmlspecialchars($offer['supplier_company'] ?? $offer['supplier_fname'] . ' ' . $offer['supplier_lname']); ?>
                                </div>
                                <div style="margin-bottom:10px;">
                                    <strong><i class="fa fa-dollar"></i> السعر:</strong> 
                                    <?php echo $offer['price'] . ' ' . $offer['currency']; ?>
                                </div>
                                <div style="margin-bottom:10px;">
                                    <strong><i class="fa fa-truck"></i> مدة التوصيل:</strong> 
                                    <?php echo $offer['delivery_days']; ?> يوم
                                </div>
                                <div style="margin-bottom:10px;">
                                    <strong><i class="fa fa-comment"></i> ملاحظات المورد:</strong> 
                                    <?php echo nl2br(htmlspecialchars($offer['notes'])); ?>
                                </div>

                                <?php if ($offer['status'] == 'accepted'): ?>
                                    <div class="label label-success" style="padding:5px 10px;">
                                        <i class="fa fa-check"></i> تم قبول هذا العرض
                                    </div>
                                <?php elseif ($offer['status'] == 'rejected'): ?>
                                    <div class="label label-danger" style="padding:5px 10px;">
                                        <i class="fa fa-times"></i> تم رفض هذا العرض
                                    </div>
                                <?php elseif ($is_supplier): ?>
                                    <div class="offer-actions" style="margin-top:15px;">
                                        <?php if ($update_count == 0): ?>
                                            <button class="btn btn-success btn-sm" onclick="openOfferPopup(<?php echo $rfq_id; ?>, 'send', <?php echo $offer_id; ?>)">
                                                <i class="fa fa-money"></i> إرسال عرض سعر
                                            </button>
                                        <?php elseif ($update_count == 1): ?>
                                            <button class="btn btn-warning btn-sm" onclick="openOfferPopup(<?php echo $rfq_id; ?>, 'update', <?php echo $offer_id; ?>)">
                                                <i class="fa fa-edit"></i> تعديل عرض السعر (مرة أخيرة)
                                            </button>
                                        <?php else: ?>
                                            <span class="label label-default">تم تحديث السعر مرتين. لا يمكن التعديل مرة أخرى.</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="offer-actions" style="margin-top:15px;">
                                        <button class="btn btn-success btn-sm accept-offer" 
                                                data-offer-id="<?php echo $offer['id']; ?>" 
                                                data-rfq-id="<?php echo $rfq_id; ?>">
                                            <i class="fa fa-check"></i> قبول العرض
                                        </button>
                                        <button class="btn btn-danger btn-sm reject-offer" 
                                                data-offer-id="<?php echo $offer['id']; ?>" 
                                                data-rfq-id="<?php echo $rfq_id; ?>">
                                            <i class="fa fa-times"></i> رفض العرض
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; 
            } 
            ?>

            <!-- التحقق من وجود شات لهذا الطلب -->
            <?php
            $chat_code = '';
            $chat_exists = false;

            if (isset($row->msg_entity) && ($row->msg_entity == 'whatsapp_rfq' || $row->msg_entity == 'supplier_quote')) {
                $chat_check = mysqli_query($con, "SELECT chat_code FROM chat_rooms WHERE rfq_id = {$row->msg_entity_id} LIMIT 1");
                if ($chat_check && mysqli_num_rows($chat_check) > 0) {
                    $chat_data = mysqli_fetch_assoc($chat_check);
                    $chat_code = $chat_data['chat_code'];
                    $chat_exists = true;
                }
            }
            ?>

            <!-- الأزرار الجديدة -->
            <div class="fl_m2" style="margin-right: 15px; margin-top: 5px;">
                <?php if (isset($row->msg_entity) && $row->msg_entity == 'whatsapp_rfq'): ?>
                    <button class="btn btn-success" onclick="sendQuoteDirect(<?php echo $row->msg_entity_id; ?>)">
                        <i class="fa fa-money"></i> إرسال عرض سعر
                    </button>
                <?php endif; ?>

                <?php if ($chat_exists && $chat_code): ?>
                    <a href="/chat/chat.php?chat_code=<?php echo $chat_code; ?>" class="btn btn-info" target="_blank">
                        <i class="fa fa-comments"></i> فتح المحادثة
                    </a>
                <?php endif; ?>
            </div>

            <div class="fl_m2" style="border-right:1px solid #85B3D5; height:24px"></div>
            <?php endif; ?>
        </div>

        <div class="b11_m2" id="ci_m2"><br></div>
    </div>

    <div class="" id="repseq"></div>

    <span class="mailbox">
        <div id="qidtype"></div>

        <div class="b9_m2 b10_m2" id="reptable" style="display:none"></div>

        <div class="b9_m2 b10_m2" id="detable">
            <table class="lh2_m2" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr class="bo_m2 bg_m2 ff_m2 c1_m2 f3_m2">
                        <td class="p_m2"><b>Enquiry Information</b></td>
                    </tr>

                    <tr>
                        <td class="sh_m2">
                            <table cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td width="150"><b> To:</b></td>
                                        <td><?php echo $to_name; ?> <<?php echo $to_email; ?>></td>
                                    </tr>
                                    <tr>
                                        <td><b> From:</b></td>
                                        <td>
                                            <?php if ($from_admin == 1): ?>
                                                <?php echo $from_name; ?> <<?php echo $from_email; ?>>
                                            <?php else: ?>
                                                <?php echo $from_name; ?> <<?php echo $from_email; ?>>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b> Date:</b></td>
                                        <td><?php echo $msg_date; ?></td>
                                    </tr>
                                    <tr>
                                        <td><b> Subject:</b></td>
                                        <td><?php echo $msg_subject; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <tr class="bo_m2 bg_m2 ff_m2 c1_m2 f3_m2">
                        <td class="p_m2"><b>Message</b></td>
                    </tr>

                    <tr class="f5_m2">
                        <td class="sh_m2">
                            <span style="width:750px; word-wrap:break-word;" id="wbr">
                                <?php 
                                $allowed_tags = '<div><span> <br> <p> <b> <strong> <i> <u> <h1> <h2> <h3> <h4> <tr> <td> <tbody> <img> <hr> <ul> <ol> <li>';
                                $msg_message_clean = strip_tags(html_entity_decode($msg_message), $allowed_tags);
                                ?>
                                <div class="message-content-modern">
                                    <?php echo nl2br(htmlspecialchars($msg_message_clean)); ?>
                                </div>
                            </span>
                        </td>
                    </tr>

                    <?php if (!empty($attachments)): ?>
                    <tr class="f5_m2">
                        <td class="sh_m2">
                            <span style="width:750px; word-wrap:break-word;" id="wbr">
                                <b>Attachments:</b>
                            </span>
                            <?php foreach ($attachments as $att): 
                                $file_name = htmlspecialchars($att->ma_file ?? '', ENT_QUOTES, 'UTF-8');
                                $file_path = "upload/message_attachment/" . $file_name;
                            ?>
                            <div>
                                <a href="<?php echo $file_path; ?>" target="_blank">
                                    <?php echo $file_name; ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </span>
</div>

<!-- Popup إرسال عرض السعر -->
<div id="quotePopup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; width:400px; margin:100px auto; padding:25px; border-radius:10px; direction:rtl;">
        <span onclick="closeQuotePopup()" style="float:left; cursor:pointer; font-size:20px;">×</span>
        <h3 style="color:#25D366;"><i class="fa fa-whatsapp"></i> إرسال عرض سعر</h3>
        <form id="quoteForm">
            <input type="hidden" name="rfq_id" id="quote_rfq_id">
            <div style="margin-bottom:15px;">
                <label>Unit Price (USD)</label>
                <input type="number" name="unit_price" id="unit_price" step="0.01" required style="width:100%; padding:8px;">
            </div>
            <div style="margin-bottom:15px;">
                <label>MOQ (Minimum Order Quantity)</label>
                <input type="number" name="moq" id="moq" style="width:100%; padding:8px;">
            </div>
            <div style="margin-bottom:15px;">
                <label>Delivery Time</label>
                <input type="text" name="delivery_time" id="delivery_time" style="width:100%; padding:8px;" placeholder="مثال: 15 يوم">
            </div>
            <div style="margin-bottom:15px;">
                <label>Supplier Message</label>
                <textarea name="supplier_message" id="supplier_message" rows="3" style="width:100%; padding:8px;"></textarea>
            </div>
            <button type="submit" style="background:#25D366; color:#fff; border:none; padding:10px; width:100%; border-radius:5px;">إرسال العرض</button>
        </form>
    </div>
</div>

<script>
function openQuotePopup(rfqId) {
    document.getElementById('quote_rfq_id').value = rfqId;
    document.getElementById('quotePopup').style.display = 'block';
}

function closeQuotePopup() {
    document.getElementById('quotePopup').style.display = 'none';
}

document.getElementById('quoteForm').onsubmit = async function(e) {
    e.preventDefault();
    let btn = this.querySelector('button');
    let originalText = btn.innerText;
    btn.innerText = 'جاري الإرسال...';
    btn.disabled = true;
    let formData = new FormData(this);
    try {
        let response = await fetch('/ajax-file/submit_quote_handler.php', {
            method: 'POST',
            body: formData
        });
        let text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch(e) {
            alert('خطأ في استجابة الخادم: ' + text.substring(0, 200));
            btn.innerText = originalText;
            btn.disabled = false;
            return;
        }
        if (data.success) {
            alert('✓ ' + (data.message || 'تم إرسال عرض السعر بنجاح'));
            if (data.chat_code) {
                window.open('/chat/chat.php?chat_code=' + data.chat_code, '_blank');
            }
            location.reload();
        } else {
            alert('❌ ' + (data.error || 'حدث خطأ غير معروف'));
        }
    } catch(error) {
        alert('❌ خطأ في الاتصال: ' + error.message);
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
};

function sendQuoteDirect(rfqId) {
    if (!rfqId) {
        alert('خطأ: رقم الطلب غير صالح');
        return;
    }
    let price = prompt("أدخل السعر (USD):", "10");
    if (!price) return;
    let moq = prompt("أدخل أقل كمية (MOQ):", "100");
    if (!moq) return;
    let delivery = prompt("أدخل مدة التوصيل:", "15 يوم");
    if (!delivery) return;
    let msg = prompt("أدخل رسالتك للمشتري:", "هذا هو عرضنا");
    let formData = new FormData();
    formData.append('rfq_id', rfqId);
    formData.append('unit_price', price);
    formData.append('moq', moq);
    formData.append('delivery_time', delivery);
    formData.append('supplier_message', msg || "");
    fetch('/ajax-file/submit_quote_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ ' + data.message);
            if (data.chat_code) {
                if (confirm('فتح صفحة المحادثة؟')) {
                    window.open('/chat/chat.php?chat_code=' + data.chat_code, '_blank');
                }
            }
            location.reload();
        } else {
            alert('❌ ' + (data.error || 'حدث خطأ غير معروف'));
        }
    })
    .catch(error => {
        alert('❌ خطأ في الاتصال: ' + error.message);
    });
}

$(document).ready(function() {
    $('.accept-offer').click(function() {
        let offerId = $(this).data('offer-id');
        let rfqId = $(this).data('rfq-id');
        if (confirm('هل أنت متأكد من قبول هذا العرض؟')) {
            $.post('/buyer/respond_offer.php', {offer_id: offerId, rfq_id: rfqId, action: 'accept'}, function(response) {
                if (response.success) {
                    alert('تم قبول العرض بنجاح');
                    location.reload();
                } else {
                    alert('خطأ: ' + response.error);
                }
            }, 'json');
        }
    });
    $('.reject-offer').click(function() {
        let offerId = $(this).data('offer-id');
        let rfqId = $(this).data('rfq-id');
        if (confirm('هل أنت متأكد من رفض هذا العرض؟')) {
            $.post('/buyer/respond_offer.php', {offer_id: offerId, rfq_id: rfqId, action: 'reject'}, function(response) {
                if (response.success) {
                    alert('تم رفض العرض');
                    location.reload();
                } else {
                    alert('خطأ: ' + response.error);
                }
            }, 'json');
        }
    });
});

function openOfferPopup(rfqId, action, offerId) {
    let price = prompt((action == 'send' ? 'إرسال عرض سعر جديد' : 'تعديل عرض السعر') + '\nالسعر (USD):');
    if (!price) return;
    let delivery = prompt('مدة التوصيل (أيام):');
    if (!delivery) return;
    let notes = prompt('ملاحظات (اختياري):');
    fetch('/supplier/update_offer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&price=' + price + '&delivery_days=' + delivery + '¬es=' + encodeURIComponent(notes || '') + '&offer_id=' + offerId
    }).then(response => response.json()).then(data => {
        if (data.success) {
            alert('✓ ' + data.message);
            if (data.whatsapp_url) window.open(data.whatsapp_url, '_blank');
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    }).catch(error => alert('خطأ في الاتصال'));
}
</script>

<?php
if (isset($stmt_to) && $stmt_to !== null && is_object($stmt_to)) {
    mysqli_stmt_close($stmt_to);
}
?>