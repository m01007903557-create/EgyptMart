<?php
/**
 * File: ajax/detailPurBuyleads.php

 * Description: عرض تفاصيل طلب الشراء المشترى
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

// التحقق من وجود معرف طلب الشراء المشترى
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    die("Invalid purchased requirement ID");
}

$pbr_id = (int)$_POST['id'];

global $con;

// جلب بيانات طلب الشراء المشترى مع التحقق من ملكية المستخدم
$sql = "SELECT pbr.*, br.*, u.* 
        FROM purchased_buy_requirement pbr
        INNER JOIN buy_requirement br ON pbr.pbr_br_id = br.br_id
        INNER JOIN user u ON br.br_u_id = u.usr_id
        WHERE pbr.pbr_id = ? AND pbr.pbr_usr_id = ? 
        LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $pbr_id, $current_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Purchased buy lead not found or access denied");
}

// تنظيف البيانات للعرض
$br_pd_name = htmlspecialchars($row->br_pd_name ?? '', ENT_QUOTES, 'UTF-8');
$br_requirement = htmlspecialchars(stripslashes($row->br_requirement ?? ''), ENT_QUOTES, 'UTF-8');
$br_estimate_qty = (isset($row->br_estimate_qty) && $row->br_estimate_qty != '0' && $row->br_estimate_qty != 0) ? htmlspecialchars((string)$row->br_estimate_qty, ENT_QUOTES, 'UTF-8') : '';
$br_estimate_qty_unit = (int)($row->br_estimate_qty_unit ?? 0);
$br_u_id = (int)($row->br_u_id ?? 0);
$name_prefix = htmlspecialchars($row->name_prefix ?? '', ENT_QUOTES, 'UTF-8');
$fname = htmlspecialchars($row->fname ?? '', ENT_QUOTES, 'UTF-8');
$lname = htmlspecialchars($row->lname ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8');
$country = (int)($row->country ?? 0);
$mobile1 = htmlspecialchars($row->mobile1 ?? '', ENT_QUOTES, 'UTF-8');
$country_ph_code = htmlspecialchars($row->country_ph_code ?? '', ENT_QUOTES, 'UTF-8');
$purchase_date = !empty($row->pbr_purchase_date) ? date("d M, Y", strtotime($row->pbr_purchase_date)) : 'N/A';
?>

<!-- تضمين مكتبة ColorBox -->
<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">

<style>
    .btn-send-offer {
        display: inline-block;
        padding: 6px 12px;
        margin: 5px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        font-size: 13px;
        background: #28a745;
        color: white;
    }
    .btn-send-offer:hover {
        background: #218838;
    }
</style>


<script>
// ============================================
// دالة إرسال عرض سعر (من enq-details.php)
// ============================================
function sendOfferFromBuylead(rfqId, element) {
    let price = prompt('السعر (USD):');
    if (!price) return;
    let delivery = prompt('مدة التوصيل (أيام):');
    if (!delivery) return;
    let notes = prompt('ملاحظات:', '');

    var originalHtml = element.innerHTML;
    element.innerHTML = '⏳ جاري...';
    element.disabled = true;

console.log('✅ زر إرسال عرض سعر تم الضغط عليه، RFQ ID:', rfqId);

    fetch('/ajax-file/supplier_offer_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId + '&price=' + price + '&delivery_days=' + delivery + '&notes=' + encodeURIComponent(notes)
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        if (d.whatsapp_url && d.whatsapp_url != '' && !d.is_first) {
            window.open(d.whatsapp_url, '_blank');
        }
        if (d.rfq_id) {
            if (confirm('✅ تم إرسال العرض. هل تريد فتح المحادثة مع الطرف الآخر؟')) {
                window.open('/chat/chat.php?rfq_id=' + d.rfq_id, '_blank');
            }
        }
        location.reload();
    })
    .catch(e => {
        alert('خطأ: ' + e.message);
        element.innerHTML = originalHtml;
        element.disabled = false;
    });
}
</script>

<script>
$(document).ready(function() {
    // تفعيل ColorBox للروابط التي تحمل كلاس ajax
    $(".ajax").colorbox();
    $(".inline").colorbox({inline: true, width: "50%"});
});
</script>

<div class="mctr_buyreq mfl">
    <!-- رأس الصفحة -->
    <div class="mf18 mc5 mta2 mpb10">
        <div class="mf11 bc mbl mbn"></div>
        <a class="mctr_manage">Purchased Buy Leads</a>
        <span style="float:right; color:#929292; font-size:16px; padding-right:87px">
            « <a href="javascript:goback()" style="font-size:12px; padding-top:4px; font-weight:bold;">back</a>
        </span>
    </div>
    
    <!-- تفاصيل طلب الشراء -->
    <div class="to_bd">Buylead Details</div>
    
    <div class="to_ct">
        <div class="to_lp" style="min-height:53px;">
            <b style="color:#1973B9; font-size:14px;"><?php echo $br_pd_name; ?></b>
            <br><?php echo nl2br($br_requirement); ?>
            
            <?php if (!empty($br_estimate_qty)): ?>
            <br><b>Quantity: </b><?php echo $br_estimate_qty; ?>&nbsp;<?php echo measurement_unit($br_estimate_qty_unit); ?>
            <?php endif; ?>
            
            <br><br>
            <span class="bdd bo">
                <span class="artb sbg"></span>
                <strong>Buyer Information :</strong>
            </span>
            <br>
            
            <?php 
            $buyer_name = trim($name_prefix . ' ' . $fname . ' ' . $lname);
            echo htmlspecialchars($buyer_name, ENT_QUOTES, 'UTF-8');
            ?>
            
            <?php if ($country > 0): ?>
            <br><?php echo htmlspecialchars(get_country_name($country), ENT_QUOTES, 'UTF-8'); ?>
            <?php endif; ?>
            
            <br>Email: <?php echo $email; ?>
            
            <?php if (!empty($mobile1) && $mobile1 != '0'): ?>
            <br>Mobile / Cell Phone: +(<?php echo $country_ph_code; ?>)-<?php echo $mobile1; ?>
            <?php endif; ?>
            
            <br><br>
            <!-- ✅ زر "Send Enquiry" (الموجود) -->
<a class="ajax" rel="nofollow" 
   href="sendLeadEnquiry-form.php?id=<?php echo rand(1000, 9999) . md5((string)$br_u_id); ?>&headline=<?php echo urlencode($br_pd_name); ?>"
   style="display:inline-block; padding:6px 12px; margin:5px; background:#17a2b8; color:white; border-radius:4px; text-decoration:none; font-size:13px;">
    <i class="fa fa-envelope"></i> Send Enquiry
</a>

<!-- ✅ زر "إرسال عرض سعر" (الجديد) -->
<button class="btn-send-offer" onclick="sendOfferFromBuylead(<?php echo $row->pbr_br_id; ?>, this)"
        style="display:inline-block; padding:6px 12px; margin:5px; border:none; border-radius:4px; cursor:pointer; font-size:13px; background:#28a745; color:white;">
    <i class="fa fa-dollar"></i> إرسال عرض سعر
</button>
        </div>
        
        <p class="to_rp1">
            <b>Purchased on:</b> <?php echo $purchase_date; ?><br>
        </p>
        <div style="clear:both;"></div>
    </div>
</div>

<?php
// إغلاق الاتصال
// mysqli_close($con);
?>