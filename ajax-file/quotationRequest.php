<?php
/**
 * File: company/quotationRequest.php

 * Description: نموذج طلب عرض سعر (استفسار) لمنتج معين
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

/*********************/
$con_id = $_GET['conty'] ?? '';
$geo_id = $_GET['geo'] ?? '';

// بناء شرط الموقع
$sql_pd_ck = "";
if (!empty($con_id) && isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id)) 
        OR 
        (pd_preferred_buyer_location = 'any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id))
        OR
        (pd_preferred_buyer_location = 'my_city' AND pd_uid IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile 
            WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = $loc_id)
        ))
    )";
} elseif (!empty($geo_id)) {
    $location_geo_country = $location_geo_country ?? '';
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
}
/********************/

$c = $_GET['c'] ?? '';
$bnsprof_hash = substr($_GET['id'] ?? '', 4);
$product_id = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
$keywords = $_GET['keywords'] ?? '';
$vform = isset($_GET['vform']) ? (int)$_GET['vform'] : 0;

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: ../sign-in.php");
    exit;
}

$current_user = (int)$_SESSION['uid_indm'];

global $con;

// جلب بيانات المنتج
$p_sql = "SELECT pd_id, pd_uid, pd_title, pd_image, pd_min_order_qty, pd_unit 
          FROM products WHERE pd_id = ? LIMIT 1";
$stmt_p = mysqli_prepare($con, $p_sql);
mysqli_stmt_bind_param($stmt_p, 'i', $product_id);
mysqli_stmt_execute($stmt_p);
$result_p = mysqli_stmt_get_result($stmt_p);
$p_row = mysqli_fetch_object($result_p);
mysqli_stmt_close($stmt_p);

if (!$p_row) {
    die("Product not found");
}

// جلب بيانات الشركة المستهدفة
$sql = "SELECT bp.*, u.* 
        FROM business_profile bp
        INNER JOIN user u ON bp.bnsprof_uid = u.usr_id
        WHERE MD5(bp.bnsprof_id) = ? 
        LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $bnsprof_hash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Company not found");
}

// جلب بيانات المستخدم الحالي
$sql_own = "SELECT u.*, bp.* 
            FROM user u
            INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
            WHERE u.usr_id = ? 
            LIMIT 1";

$stmt_own = mysqli_prepare($con, $sql_own);
mysqli_stmt_bind_param($stmt_own, 'i', $current_user);
mysqli_stmt_execute($stmt_own);
$result_own = mysqli_stmt_get_result($stmt_own);
$row_own = mysqli_fetch_object($result_own);
mysqli_stmt_close($stmt_own);

if (!$row_own) {
    die("User profile not found");
}

// حفظ الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "company/products.php?c=" . $c;

// جلب المنتج التالي والسابق
$user_data = []; // سيتم ملؤها باستعلام لاحق

// استعلام لجلب بيانات المستخدمين (للتبسيط، يمكن تحسينه)
$view_product = "SELECT u.*, bp.*, ct.* 
                 FROM user u
                 INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                 INNER JOIN city ct ON ct.ct_id = bp.bnsprof_city";
$user_result = mysqli_query($con, $view_product);
$userArrayRow_Result = [];
while ($user_row = mysqli_fetch_assoc($user_result)) {
    $userArrayRow_Result[$user_row['usr_id']] = $user_row;
}

// المنتج التالي
$nxt_id = "";
if (!empty($keywords)) {
    $keywords_escaped = mysqli_real_escape_string($con, $keywords);
    $nxt_id = "SELECT p.*, mu.*, c.* 
               FROM products p
               INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
               INNER JOIN country c ON c.cn_id = p.pd_currency
               WHERE (p.pd_title LIKE '%$keywords_escaped%')
               $sql_pd_ck
               AND p.pd_status = '1'
               AND p.pd_image != ''
               AND p.pd_id < $product_id
               ORDER BY p.pd_id DESC 
               LIMIT 1";
} else {
    $nxt_id = "SELECT p.*, mu.*, c.* 
               FROM products p
               INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
               INNER JOIN country c ON c.cn_id = p.pd_currency
               $sql_pd_ck
               AND p.pd_status = '1'
               AND p.pd_image != ''";
    if ($vform == 1) {
        $nxt_id .= " AND p.pd_uid = " . $p_row->pd_uid;
    }
    $nxt_id .= " AND p.pd_id < $product_id 
                 ORDER BY p.pd_id DESC 
                 LIMIT 1";
}

$run_nxt = null;
if (!empty($nxt_id)) {
    $result_nxt = mysqli_query($con, $nxt_id);
    if ($result_nxt) {
        $run_nxt = mysqli_fetch_object($result_nxt);
    }
}
$data = $run_nxt ? ($userArrayRow_Result[$run_nxt->pd_uid] ?? []) : [];

// المنتج السابق
$pre_id = "";
if (!empty($keywords)) {
    $keywords_escaped = mysqli_real_escape_string($con, $keywords);
    $pre_id = "SELECT p.*, mu.*, c.* 
               FROM products p
               INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
               INNER JOIN country c ON c.cn_id = p.pd_currency
               WHERE (p.pd_title LIKE '%$keywords_escaped%')
               $sql_pd_ck
               AND p.pd_status = '1'
               AND p.pd_image != ''
               AND p.pd_id > $product_id
               ORDER BY p.pd_id ASC 
               LIMIT 1";
} else {
    $pre_id = "SELECT p.*, mu.*, c.* 
               FROM products p
               INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
               INNER JOIN country c ON c.cn_id = p.pd_currency
               $sql_pd_ck
               AND p.pd_status = '1'
               AND p.pd_image != ''";
    if ($vform == 1) {
        $pre_id .= " AND p.pd_uid = " . $p_row->pd_uid;
    }
    $pre_id .= " AND p.pd_id > $product_id 
                 ORDER BY p.pd_id ASC 
                 LIMIT 1";
}

$run_pre = null;
if (!empty($pre_id)) {
    $result_pre = mysqli_query($con, $pre_id);
    if ($result_pre) {
        $run_pre = mysqli_fetch_object($result_pre);
    }
}
$data1 = $run_pre ? ($userArrayRow_Result[$run_pre->pd_uid] ?? []) : [];

// دوال مساعدة
function get_product_detail(int $product_id, string $field): string {
    global $con;
    $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    $sql = "SELECT $field FROM products WHERE pd_id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return htmlspecialchars($row[$field] ?? '', ENT_QUOTES, 'UTF-8');
}

// تنظيف البيانات للعرض
$company_name = htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
$target_user_id = (int)$row->usr_id;
$product_title = htmlspecialchars(get_product_detail($product_id, 'pd_title'), ENT_QUOTES, 'UTF-8');
$product_image = htmlspecialchars($p_row->pd_image ?? '', ENT_QUOTES, 'UTF-8');
$product_moq = (int)($p_row->pd_min_order_qty ?? 0);
$product_unit = (int)($p_row->pd_unit ?? 0);

// بيانات المرسل للعرض
$sender_name = trim(($row_own->name_prefix ?? '') . ' ' . ($row_own->fname ?? '') . ' ' . ($row_own->lname ?? ''));
$sender_company = htmlspecialchars($row_own->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
$sender_address1 = htmlspecialchars($row_own->bnsprof_address1 ?? '', ENT_QUOTES, 'UTF-8');
$sender_address2 = htmlspecialchars($row_own->bnsprof_address2 ?? '', ENT_QUOTES, 'UTF-8');
$sender_city = (int)($row_own->bnsprof_city ?? 0);
$sender_state = (int)($row_own->bnsprof_state ?? 0);
$sender_country = (int)($row_own->country ?? 0);
$sender_email = htmlspecialchars($row_own->email ?? '', ENT_QUOTES, 'UTF-8');
$sender_phone_code = htmlspecialchars($row_own->country_ph_code ?? '', ENT_QUOTES, 'UTF-8');
$sender_mobile = htmlspecialchars($row_own->mobile1 ?? '', ENT_QUOTES, 'UTF-8');

// دوال مساعدة للعرض
function get_city_name_safe($city_id): string {
    return $city_id > 0 ? htmlspecialchars(get_city_name($city_id), ENT_QUOTES, 'UTF-8') : '';
}
function get_state_name_safe($state_id): string {
    return $state_id > 0 ? htmlspecialchars(get_state_name($state_id), ENT_QUOTES, 'UTF-8') : '';
}
function get_country_name_safe($country_id): string {
    return $country_id > 0 ? htmlspecialchars(get_country_name($country_id), ENT_QUOTES, 'UTF-8') : '';
}
?>

<link type="text/css" rel="stylesheet" href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>/css/main-v2.css">        
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>/css/dir-style-8.css" type="text/css" rel="stylesheet">
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>/css/overlay-v2.css" type="text/css" rel="stylesheet">
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>/css/bl_form_temp5.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script src="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>/js/jquery.colorbox.js"></script>
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>/css/colorbox.css" type="text/css" rel="stylesheet">

<?php if ($_GET['search'] ?? 0 == 1): ?>
<script>
    $(document).ready(function() {
        $('body').on('click', '.ajax', function(event) {
            parent.$.colorbox({
                href: $(this).attr('href'),
                open: true,
                iframe: true, 
                width: '750px', 
                height: '600px'
            });
            parent.$.colorbox.close(); 
            return false;
        });
    });
</script>
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function() {
    $(document).on('keyup', '#msg_message', function(e) {
        var maxLength = 2000;
        var text = $(this).val();
        var length = $(this).val().length;
        if (length > maxLength) {
            $(this).val(text.substring(0, maxLength));
        }
        var remaining = maxLength - $(this).val().length;
        $("#charCount").empty().html(remaining);
    });
});

function sendEnquiry() {
    var msg_from = document.getElementById('msg_from');
    var msg_to = document.getElementById('msg_to');
    var msg_subject = document.getElementById('msg_subject');
    var msg_message = document.getElementById('msg_message');
    var prd_name = document.getElementById('prd_name');
    var prd_image = document.getElementById('prd_image');
    var prd_moq = document.getElementById('prd_moq');
    var prd_unit = document.getElementById('prd_unit');
    var valid = true;
    
    if (msg_message.value == '' || msg_message.value == null) {
        alert("من فضلك إوصف متطلباتك");
        valid = false;
    } else if (msg_message.value.length < 50) {
        alert("... وصف المتطلبات يحتاج الى كتابة 50 حرف على الأقل");
        msg_message.focus();
        valid = false;
    }
    
    if (valid) {
        $("#msg_message").attr('readonly', 'readonly');
        $("#b_sub").css("display", "none");
        $("#loadings").css("display", "block");
        
        var messageText = msg_message.value;
        $.post("../ajax-file/sendQuotationMessage.php", {
            msg_pro_unit: prd_unit.value,
            msg_pro_moq: prd_moq.value,
            msg_pro_name: prd_name.value,
            msg_from: msg_from.value,
            msg_img: prd_image.value,
            msg_to: msg_to.value,
            msg_subject: msg_subject.value,
            msg_message: messageText
        }, function(data) {
            if (data == 1) {
                setTimeout(function() {
                    $("#loadings").css("display", "none");
                    $("#succ_results").css("display", "block");
                }, 500);
            } else {
                setTimeout(function() {
                    $("#loadings").css("display", "none");
                    $("#err_results").css("display", "block");
                }, 500);
            }
        });
    }
}
</script>

<style>
/* =========================================================
   NEW RESPONSIVE CSS - UPDATED 27 AUG 2026
   quotationRequest2.php
   ========================================================= */
.enn1-nw {
    color: #000000;
    float: none;
    width: auto;
}

.quotation-popup,
.quotation-popup * {
    box-sizing: border-box;
}

.quotation-popup {
    width: 100%;
    margin: 0 auto;
    padding: 12px;
    overflow-x: hidden;
    color: #333;
}

.quotation-popup img {
    max-width: 100%;
    height: auto;
}

.quotation-popup .neff2-nw,
.quotation-popup .neff2-nw p {
    width: 100%;
    max-width: 635px;
    margin-left: auto;
    margin-right: auto;
}

.quotation-popup .neff2-nw p {
    overflow-wrap: anywhere;
}

.quotation-popup .is-hidden,
.quotation-popup #errmsg,
.quotation-popup #loadings,
.quotation-popup #succ_results,
.quotation-popup #err_results {
    display: none;
}

.quotation-popup .product-navigation {
    width: 100%;
    min-height: 163px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    text-align: center;
}

.quotation-popup .product-navigation .ajax {
    flex: 0 0 auto;
}

.quotation-popup .product-image {
    display: block;
    width: min(250px, 70%);
    height: auto;
    max-height: 163px;
    object-fit: contain;
    margin: 0 auto;
}

.quotation-popup .navigation-arrow {
    display: block;
    width: 42px;
    height: auto;
    max-height: 61px;
    cursor: pointer;
}

.quotation-popup .enquiry-message {
    width: 100%;
    min-height: 125px;
    resize: none;
}

.quotation-popup .character-status {
    width: 99%;
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
    text-align: right;
}

.quotation-popup .request-details {
    width: 100%;
    max-width: 178px;
    min-height: 125px;
    margin-top: 2px;
    border: 1px solid rgb(134, 182, 217);
}

.quotation-popup .request-details legend {
    width: 89%;
    margin-bottom: 2px;
    color: #017BBC;
    font-size: 13px;
    text-align: center;
}

.quotation-popup .request-details .f1-nw {
    color: #055985;
}

.quotation-popup .details-clear {
    margin-bottom: 2px;
}

.quotation-popup .contact-section {
    width: 100%;
    overflow: hidden;
}

.quotation-popup .contact-heading {
    width: calc(100% - 10px);
    margin: 5px;
    padding: 5px;
    border-bottom: 1px solid rgb(134, 182, 217);
    background-color: rgb(241, 241, 241);
    color: rgb(15, 84, 135);
    float: left;
    font-size: 14px;
    font-weight: 700;
}

.quotation-popup .contact-panel {
    clear: both;
    width: 100%;
    padding: 5px 10px 10px;
    border: 4px double rgb(134, 182, 217);
    background-color: rgb(241, 241, 241);
    overflow-wrap: anywhere;
}

.quotation-popup .contact-info {
    padding-top: 5px;
}

.quotation-popup .contact-spacer {
    min-height: 12px;
    margin-left: 0;
    padding: 0 0 10px 15px;
    font-size: 12px;
}

.quotation-popup .submission-status {
    padding: 16px 5px 0;
}

.quotation-popup .loading-status {
    color: #1045B0;
}

.quotation-popup .success-status {
    color: #009700;
}

.quotation-popup .error-status {
    color: #F00;
}

.quotation-popup .snd-enq {
    box-shadow: 0pt 1px 5px rgb(170, 170, 170);
    max-width: 100%;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    border: 1px solid rgb(24, 143, 205);
    border-radius: 6px;
    padding: 5px 20px;
    cursor: pointer;
}

@media (max-width: 600px) {
    .quotation-popup {
        padding: 8px;
    }

    .quotation-popup .product-navigation {
        min-height: 110px;
        gap: 4px;
    }

    .quotation-popup .product-image {
        width: min(250px, 72%);
        max-height: 120px;
    }

    .quotation-popup .navigation-arrow {
        width: 32px;
    }

    .quotation-popup .request-details {
        max-width: none;
    }
}

@media (max-width: 360px) {
    .quotation-popup {
        padding: 5px;
    }

    .quotation-popup .product-navigation {
        min-height: 90px;
    }

    .quotation-popup .product-image {
        width: 68%;
        max-height: 95px;
    }

    .quotation-popup .navigation-arrow {
        width: 26px;
    }

    .quotation-popup .snd-enq {
        width: 100%;
        padding-left: 10px;
        padding-right: 10px;
    }
}
</style>

<div class="ov-base quotation-popup">
    <div class="neff2-nw">
        <p>إرسل الى :<span class="co-name">&nbsp;<?php echo $company_name; ?></span></p>
    </div>
    <div class="neff2-nw">
        <p>إستفسار عن :<span class="co-name"><?php echo $product_title; ?></span></p>
    </div>
    
    <div class="bo k9 err-msg" id="errmsg"></div>
    
    <form name="dataform" class="mp0-nw" method="post" action="">
        <input type="hidden" id="prd_image" name="prd_image" value="<?php echo $product_image; ?>">
        <input type="hidden" id="prd_moq" name="prd_moq" value="<?php echo $product_moq; ?>">
        <input type="hidden" id="prd_unit" name="prd_unit" value="<?php echo $product_unit; ?>">
        <input type="hidden" id="prd_name" name="prd_name" value="<?php echo $product_title; ?>">
        <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $current_user; ?>">
        <input type="hidden" id="msg_to" name="msg_to" value="<?php echo $target_user_id; ?>">
        <input type="hidden" id="msg_subject" name="msg_subject" value="Product Enquiry">
        <br>
        
        <div class="product-navigation">
            <!-- السهم الأيسر (المنتج السابق) -->
            <?php if ($run_pre): 
                $prev_url = "";
                if (!empty($keywords)) {
                    $prev_url = "https://" . $_SERVER['HTTP_HOST'] . "/company/quotationRequest.php?id=" . rand(1000, 9999) . md5((string)($data1['bnsprof_id'] ?? '')) . "&pid=" . $run_pre->pd_id . "&keywords=" . urlencode($keywords) . "&geo=" . urlencode($geo_id) . "&conty=" . urlencode($con_id);
                } else {
                    $url_id = ($vform == 1) ? $_GET['id'] : (rand(1000, 9999) . md5((string)($data1['bnsprof_id'] ?? '')));
                    $prev_url = "https://" . $_SERVER['HTTP_HOST'] . "/company/quotationRequest.php?id=" . $url_id . "&pid=" . $run_pre->pd_id . "&geo=" . urlencode($geo_id) . "&conty=" . urlencode($con_id) . (($vform == 1) ? '&vform=1' : '');
                }
            ?>
                <a data-prev="" class="ajax<?php echo ($vform == 1) ? ' is-hidden' : ''; ?>" href="<?php echo $prev_url; ?>">
                    <img class="navigation-arrow" src="https://egyptmart.shop/company/images/2.png" alt="Previous product">
                </a>
            <?php endif; ?>
            
            <!-- صورة المنتج -->
            <img class="product-image" src="https://egyptmart.shop/upload/myproduct/<?php echo $product_image; ?>" alt="<?php echo $product_title; ?>">
            
            <!-- السهم الأيمن (المنتج التالي) -->
            <?php if ($run_nxt): 
                $next_url = "";
                if (!empty($keywords)) {
                    $next_url = "https://" . $_SERVER['HTTP_HOST'] . "/company/quotationRequest.php?id=" . rand(1000, 9999) . md5((string)($data['bnsprof_id'] ?? '')) . "&pid=" . $run_nxt->pd_id . "&keywords=" . urlencode($keywords) . "&geo=" . urlencode($geo_id) . "&conty=" . urlencode($con_id);
                } else {
                    $url_id = ($vform == 1) ? $_GET['id'] : (rand(1000, 9999) . md5((string)($data['bnsprof_id'] ?? '')));
                    $next_url = "https://" . $_SERVER['HTTP_HOST'] . "/company/quotationRequest.php?id=" . $url_id . "&pid=" . $run_nxt->pd_id . "&geo=" . urlencode($geo_id) . "&conty=" . urlencode($con_id) . (($vform == 1) ? '&vform=1' : '');
                }
            ?>
                <a data-next="" class="ajax<?php echo ($vform == 1) ? ' is-hidden' : ''; ?>" href="<?php echo $next_url; ?>">
                    <img class="navigation-arrow" src="https://egyptmart.shop/company/images/play.png" alt="Next product">
                </a>
            <?php endif; ?>
        </div>
        
        <br>
        
        <div class="enn1-nw nef4-nw">
            <textarea id="msg_message" name="msg_message" class="nef10-nw enquiry-message" tabindex="1" maxlength="2000"></textarea>
            <div class="nef9-nw nef12-nw">
                <!-- Send me a copy of this Enquiry-->
            </div>
            <div class="nef9-nw nef12-nw character-status" id="Description-status">
                الحروف المتبقية :&nbsp;<b><strong id="charCount">2000</strong></b>
                <div class="m2"></div>
            </div>
        </div>
        
        <fieldset class="request-details">
            <legend>
                <strong>إوصف طلباتك</strong>
            </legend>
            <div class="f1-nw">
                <ul>
                    <li class="li-1">متطلبات المنتج</li>
                    <li class="li-1">المواصفات المطلوبة</li>
                    <li class="li-1">التغليف والتسليم</li>
                    <li class="li-1">تفاصيل شركتك الخ</li>
                </ul>
            </div>
        </fieldset>
        
        <div class="clr-nw details-clear"></div>
        
        <div class="contact-section">
            <div class="w12 contact-heading" align="LEFT">
                <b>: معلومات إتصالك</b>
            </div>
            <div class="text contact-panel" align="LEFT">
                <div class="contact-clear"></div>
                <div id="yourcontactinfo">
                    <div class="text contact-info" align="LEFT">
                        <?php echo $sender_name; ?><br>
                        <?php echo $sender_company; ?><br>
                        <?php if (!empty($sender_address1)): ?>
                            <?php echo $sender_address1; ?><br>
                        <?php endif; ?>
                        <?php if (!empty($sender_address2)): ?>
                            <?php echo $sender_address2; ?><br>
                        <?php endif; ?>
                        <?php 
                        $location_parts = [];
                        if ($sender_city > 0) $location_parts[] = get_city_name_safe($sender_city);
                        if ($sender_state > 0) $location_parts[] = get_state_name_safe($sender_state);
                        if ($sender_country > 0) $location_parts[] = get_country_name_safe($sender_country);
                        echo implode(', ', array_filter($location_parts));
                        ?><br>
                        الإيميل: <?php echo $sender_email; ?>
                        <?php if (!empty($sender_mobile) && $sender_mobile != '0'): ?>
                            <br>الموبايل / التليفون: +(<?php echo $sender_phone_code; ?>)-<?php echo $sender_mobile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="contact-spacer" align="LEFT">
                <br>
            </div>
        </div>
        
        <div class="clr-nw"></div>
        
        <div id="nu_frm">
            <div class="nef4-nw" align="center">
                <div id="b_sub">
                    <input name="submit_member" id="button" value="إرسل إستفسارك" class="snd-enq" 
                           type="button" onclick="sendEnquiry();" />
                </div>
                <div id="loadings" class="g9 bo off submission-status loading-status">
                    <img class="loading" src="../images/loading-small.gif" alt="loading">
                    <b>... رجاء الانتظار</b>
                </div>
                <div id="succ_results" class="g9 bo off submission-status success-status">
                    ... تم إرسال رسالتك بنجاح
                </div>
                <div id="err_results" class="g9 bo off submission-status error-status">
                    ... حدث خطأ بالإرسال .. رجاء المحاولة لاحقا
                </div>
            </div>
        </div>
    </form>
    <div class="clr-nw"></div>
</div>