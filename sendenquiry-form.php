<?php
/**
 * File: sendenquiry-form.php
 * Description: نموذج إرسال استفسار إلى شركة/مورد
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

// التحقق من وجود معرف الشركة
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$bnsprof_hash = substr($_GET['id'], 4); // إزالة أول 4 أحرف
$current_user = (int)$_SESSION['uid_indm'];

global $con;

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
    header('Location: index.php');
    exit;
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
    header('Location: profile.php');
    exit;
}

// تنظيف البيانات للعرض
$company_name = htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
$target_user_id = (int)$row->usr_id;
$subject_default = "الإستفسار من : " . trim(($row_own->name_prefix ?? '') . ' ' . ($row_own->fname ?? '') . ' ' . ($row_own->lname ?? ''));

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
<link type="text/css" rel="stylesheet" href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? '', ENT_QUOTES, 'UTF-8'); ?>/css/main-v2.css">        
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? '', ENT_QUOTES, 'UTF-8'); ?>/css/dir-style-8.css" type="text/css" rel="stylesheet">
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? '', ENT_QUOTES, 'UTF-8'); ?>/css/overlay-v2.css" type="text/css" rel="stylesheet">
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? '', ENT_QUOTES, 'UTF-8'); ?>/css/bl_form_temp5.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="js/jquery-1.11.1.min.js"></script>

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
    
    // عرض المرفقات الموجودة
    showTempAttachments(<?php echo $current_user; ?>);
});

function showTempAttachments(usr) {
    $.post("ajax-file/showTempAttachments.php", {usr: usr}, function(data) { 
        $("#file_list").html(data); 
    });
}

function delAttachment(id, usr) {
    $.post("ajax-file/delTempAttachments.php", {id: id}, function(data) { 
        showTempAttachments(usr); 
    });
}

function sendEnquiry() {
    var msg_from = document.getElementById('msg_from');
    var msg_to = document.getElementById('msg_to');
    var msg_subject = document.getElementById('msg_subject');
    var msg_message = document.getElementById('msg_message');
    var valid = true;
    
    if (msg_message.value == '' || msg_message.value == null) {
        alert("رجاء وصف متطلباتك");
        msg_message.focus();
        valid = false;
    } else if (msg_message.value.length < 50) {
        alert("وصف المتطلبات لابد أن لايقل عن 50 حرف");
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
                showTempAttachments(msg_from.value);
                document.getElementById('msg_message').value = '';
                $("#charCount").html('2000');
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
        <p style="width: 635px;">إرسل إستفسار الى : <span class="co-name"><?php echo $company_name; ?></span></p>
    </div>
    
    <div class="bo k9 err-msg" id="errmsg" style="display: none;"></div>
    
    <form name="dataform" class="mp0-nw" method="post">
        <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $current_user; ?>" />
        <input type="hidden" id="msg_to" name="msg_to" value="<?php echo $target_user_id; ?>" />
        <input type="hidden" id="msg_subject" name="msg_subject" value="<?php echo htmlspecialchars($subject_default, ENT_QUOTES, 'UTF-8'); ?>" />
        
        <div class="enn1-nw nef4-nw">
            <textarea id="msg_message" name="msg_message" style="resize: none;" class="nef10-nw" tabindex="1" maxlength="2000"></textarea>
            <div class="nef9-nw nef12-nw">
                <!-- Send me a copy of this Enquiry-->
            </div>
            <div class="nef9-nw nef12-nw" style="text-align:right; width:99%;" id="Description-status">
                الحروف المتبقية:&nbsp;<b><strong id="charCount">2000</strong></b>
                <div class="m2"></div>
            </div>
        </div>
        
        <fieldset style="height: 125px; border: 1px solid rgb(134,182,217); margin-top: 2px; width:178px;">
            <legend style="font-size: 13px; color:#017BBC; text-align: center;"><strong>إوصف متطلباتك</strong></legend>
            <div class="f1-nw" style="color:#055985;">
                <ul>
                    <li class="li-1">تفاصيل المنتجات أو الخدمات</li>
                    <li class="li-1">المواصفات المطلوبة</li>
                    <li class="li-1">الحد الأدنى للطلب</li>
                    <li class="li-1">وقت وشروط التسليم .. الخ</li>
                </ul>
            </div>
        </fieldset>
        
        <div style="padding:5px;">
            <script src="company/uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
            <link rel="stylesheet" type="text/css" href="company/uploadifive/uploadifive.css">
            <script type="text/javascript">
                jQuery(function() {
                    jQuery('#file_upload').uploadifive({
                        'auto': true,
                        'formData': {'id': '<?php echo $current_user; ?>'},
                        'queueID': 'queue',
                        'debug': false,
                        'method': 'post',
                        'uploadScript': 'ajax-file/addTempEnquiryFile.php',
                        'onUploadComplete': function(file, data) {
                            showTempAttachments('<?php echo $current_user; ?>');
                        }
                    });
                });
            </script>
            <div id="drop" style="padding-left:10px;">
                <input type="file" id="file_upload" name="file_upload" />
            </div>
            <div id="queue"></div>
            <div id="file_list" style="margin:2px;"></div>
        </div>
        
        <div style="padding:5px; font-size:14px; float:right">
            إذا لم يتواصل معى هذا المورد خلال فترة قصرة .. رجاء إرسال موردين مماثلين الى بريدى
            <input type="checkbox" id="notify_me" name="notify_me" />
        </div>
        
        <div class="clr-nw" style="margin-bottom:2px"></div>
        
        <div>
            <div class="w12" style="font-size:14px; padding:5px; border-bottom:1px solid rgb(134,182,217); margin:5px; width:658px; color:rgb(15,84,135); background-color:rgb(241,241,241); float:left; font-weight:700" align="right">
                <b>تفاصيل الإتصال الخاصة بشركتك</b>
            </div>
            <div class="text" style="padding-top:5px; border:4px double rgb(134,182,217); padding-bottom:10px; padding-left:10px; background-color:rgb(241,241,241);" align="LEFT">
                <div style="clear:both"></div>
                <div id="yourcontactinfo">
                    <div class="text" style="padding-top:5px;" align="LEFT">
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
                        Email: <?php echo $sender_email; ?>
                        <?php if (!empty($sender_mobile) && $sender_mobile != '0'): ?>
                            <br>Mobile / Cell Phone: +(<?php echo $sender_phone_code; ?>)-<?php echo $sender_mobile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div style="font-size:12px; margin-left:0px; padding:0px 0pt 10px 15px;" align="LEFT">
                <br>
            </div>
        </div>
        
        <div class="clr-nw"></div>
        
        <div id="nu_frm">
            <div class="nef4-nw" align="center">
                <div style="display: block;" id="b_sub">
                    <input name="submit_member" id="button" value="إرسل إستفسارك" class="snd-enq" 
                           style="box-shadow:0pt 1px 5px rgb(170,170,170); font-family:Arial,Helvetica,sans-serif; font-size:16px; font-weight:bold; text-align:center; color:rgb(255,255,255); border:1px solid rgb(24,143,205); border-radius:6px; padding:5px 20px; cursor:pointer;" 
                           type="button" onclick="sendEnquiry();" />
                </div>
                <div id="loading" style="display:none; padding-left:5px; color:#1045B0; padding-top:16px;" class="g9 bo off">
                    <img class="loading" src="images/loading-small.gif" alt="loading" height="16" width="16">
                    <b>... رجاء الإنتظار</b>
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