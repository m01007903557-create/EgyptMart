<?php
/**
 * File: sendTenderEnquiry-form.php

 * Description: نموذج طلب عرض سعر (استفسار) من مورد
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header('Location: sign-in.php');
    exit;
}

// التحقق من وجود معرف المستخدم المستهدف
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$usr_hash = substr($_GET['id'], 4); // إزالة أول 4 أحرف
$current_user = (int)$_SESSION['uid_indm'];

global $con;

// جلب بيانات المستخدم المستهدف (المورد)
$sql = "SELECT u.* FROM user u WHERE MD5(u.usr_id) = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $usr_hash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header('Location: index.php');
    exit;
}

// جلب بيانات المستخدم الحالي (المرسل)
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
    header('Location: profile.php');
    exit;
}

// تنظيف البيانات للعرض
$target_user_id = (int)$row->usr_id;
$target_name = trim(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? ''));
$target_country = (int)($row->country ?? 0);
$target_email = htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8');
$target_mobile = htmlspecialchars($row->mobile1 ?? '', ENT_QUOTES, 'UTF-8');
$target_phone_code = htmlspecialchars($row->country_ph_code ?? '', ENT_QUOTES, 'UTF-8');

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
$subject_default = "Enquiry from " . $sender_name;

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

<link href="css/overlay-v2.css" type="text/css" rel="stylesheet">

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
    var valid = true;
    
    if (msg_message.value == '' || msg_message.value == null) {
        alert("من فضلك إرسل طلبك");
        valid = false;
    } else if (msg_message.value.length < 50) {
        alert("وصف الطلب لا ينبغى أن يقل عن 50 حرف");
        msg_message.focus();
        valid = false;
    }
    
    if (valid) {
        $("#msg_message").attr('readonly', 'readonly');
        $("#b_sub").css("display", "none");
        $("#loading").css("display", "block");
        
        $.post("ajax-file/sendMessage.php", {
            msg_from: msg_from.value,
            msg_to: msg_to.value,
            msg_subject: msg_subject.value,
            msg_message: msg_message.value
        }, function(data) {
            if (data == 1) {
                setTimeout(function() {
                    $("#loading").css("display", "none");
                    $("#succ_result").css("display", "block");
                }, 500);
            } else {
                setTimeout(function() {
                    $("#loading").css("display", "none");
                    $("#err_result").css("display", "block");
                }, 500);
            }
        });
    }
}
</script>

<div class="ov-base">
    <div class="neff2-nw">
        <p style="width:635px; text-align:center;">: إرسل إستفسارك<span class="co-name"></span></p>
    </div>
    
    <div class="bo k9 err-msg" id="errmsg" style="display:none;"></div>
    
    <form name="dataform" style="margin:0px; padding:0px;" method="post">
        <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $current_user; ?>" />
        <input type="hidden" id="msg_to" name="msg_to" value="<?php echo $target_user_id; ?>" />
        <input type="hidden" id="msg_subject" name="msg_subject" 
               value="<?php echo htmlspecialchars($subject_default, ENT_QUOTES, 'UTF-8'); ?>" />
        
        <div class="enn1-nw nef4-nw">
            <textarea id="msg_message" name="msg_message" style="resize:none;" 
                      class="nef10-nw" tabindex="1" maxlength="2000"></textarea>
            <div class="nef9-nw nef12-nw" style="text-align:right; width:99%;" id="Description-status">
                الحروف المتبقية:&nbsp;<b><strong id="charCount">2000</strong></b>
                <div class="m2"></div>
            </div>
        </div>
        
        <fieldset style="height:120px; border:1px solid #6500CA; margin-top:2px; width:178px;">
            <legend style="font-size:13px; color:#017BBC; text-align:center;">
                <strong>إوصف طلباتك</strong>
            </legend>
            <div class="f1-nw" style="color:#055985;">
                <ul>
                    <li class="li-1">متطلبات المنتج</li>
                    <li class="li-1">المواصفات المطلوبة</li>
                    <li class="li-1">التغليف والتسليم</li>
                    <li class="li-1">تفاصيل الاتصال بشركتك الخ</li>
                </ul>
            </div>
        </fieldset>
        
        <div class="clr-nw" style="margin-bottom:2px"></div>
        
        <?php if (($row_own->usr_mp_id ?? 0) > 4): ?>
        <!-- عرض بيانات المستلم (المورد) -->
        <div>
            <div class="w12" style="font-size:14px; padding:5px; border-bottom:1px solid #6500CA; margin:5px; width:658px; color:rgb(15,84,135); background-color:rgb(241,241,241); float:left; font-weight:700" align="center">
                <b>بيانات الراسل</b>
            </div>
            <div class="text" style="padding-top:5px; border:4px double #6500CA; padding-bottom:10px; padding-left:10px; background-color:rgb(241,241,241);" align="LEFT">
                <div style="clear:both"></div>
                <div id="yourcontactinfo">
                    <div class="text" style="padding-top:5px;" align="LEFT">
                        <?php echo htmlspecialchars($target_name, ENT_QUOTES, 'UTF-8'); ?><br>
                        <?php if ($target_country > 0): ?>
                            <?php echo htmlspecialchars(get_country_name($target_country), ENT_QUOTES, 'UTF-8'); ?><br>
                        <?php endif; ?>
                        Email: <?php echo $target_email; ?><br>
                        <?php if (!empty($target_mobile) && $target_mobile != '0'): ?>
                            Mobile / Cell Phone: +(<?php echo $target_phone_code; ?>)-<?php echo $target_mobile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div style="font-size:12px; margin-left:0px; padding:0px 0pt 10px 15px;" align="LEFT"><br></div>
        </div>
        <?php else: ?>
        <!-- عرض بيانات المرسل (المستخدم الحالي) -->
        <div>
            <div class="w12" style="font-size:14px; padding:5px; border-bottom:1px solid #6500CA; margin:5px; width:658px; color:rgb(15,84,135); background-color:rgb(241,241,241); float:left; font-weight:700" align="center">
                <b>بيانات الإتصال</b>
            </div>
            <div class="text" style="padding-top:5px; border:4px double #6500CA; padding-bottom:10px; padding-left:10px; background-color:rgb(241,241,241);" align="LEFT">
                <div style="clear:both"></div>
                <div id="yourcontactinfo">
                    <div class="text" style="padding-top:5px;" align="LEFT">
                        <?php echo htmlspecialchars($sender_name, ENT_QUOTES, 'UTF-8'); ?><br>
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
                        Email: <?php echo $sender_email; ?><br>
                        <?php if (!empty($sender_mobile) && $sender_mobile != '0'): ?>
                            Mobile / Cell Phone: +(<?php echo $sender_phone_code; ?>)-<?php echo $sender_mobile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div style="font-size:12px; margin-left:0px; padding:0px 0pt 10px 15px;" align="LEFT"><br></div>
        </div>
        <?php endif; ?>
        
        <div class="clr-nw"></div>
        
        <div id="nu_frm">
            <div class="nef4-nw" align="center">
                <div style="display:block;" id="b_sub">
                    <input name="submit_member" id="button" value="إرسل إستفسارك" class="snd-enq" 
                           style="box-shadow:0pt 1px 5px rgb(170,170,170); font-family:Arial,Helvetica,sans-serif; font-size:16px; font-weight:bold; text-align:center; color:rgb(255,255,255); border:1px solid #6500CA; border-radius:6px; padding:5px 20px; cursor:pointer;" 
                           type="button" onclick="sendEnquiry();" />
                </div>
                <div id="loading" style="display:none; padding-left:5px; color:#1045B0; padding-top:16px;" class="g9 bo off">
                    <img class="loading" src="images/loading-small.gif" alt="loading" height="16" width="16">
                    <b>... رجاء الانتظار</b>
                </div>
                <div id="succ_result" style="display:none; padding-left:5px; color:#009700; padding-top:16px;" class="g9 bo off">
                    ... تم إرسال رسالتك بنجاح
                </div>
                <div id="err_result" style="display:none; padding-left:5px; color:#F00; padding-top:16px;" class="g9 bo off">
                    حدث خطأ بالإرسال .. رجاء المحاولة لاحقا
                </div>
            </div>
        </div>
    </form>
    <div class="clr-nw"></div>
</div>