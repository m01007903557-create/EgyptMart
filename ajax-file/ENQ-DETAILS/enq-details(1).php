<?php
/**
 * File: ajax/enq-details.php

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
    unset($stmt_to);  // استخدام unset بدلاً من null
}

// في نهاية الملف - لا تضع أي إغلاق لـ $stmt_to

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
        <!--div class="bc f11">Enquiries &#187;</div-->
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
            <img src="images/my2-loading.gif" class="loading_m2">&nbsp;Loading...&nbsp;
        </div>
        <div id="noselect" class="c2_m2 bo_m2 lh_m2" style="display:none; width:25%">
            &nbsp;No Enquiry Selected.&nbsp;
        </div>
        <div id="fc_m2" class="c2_m2 bo_m2 lh_m2" style="display:none; width:25%">
            &nbsp;Folder has been created.&nbsp;
        </div>
        <div id="fr_m2" class="c2_m2 bo_m2 lh_m2" style="display:none; width:25%">
            &nbsp;Folder has been renamed.&nbsp;
        </div>
        <div id="fd_m2" class="c2_m2 bo_m2 lh_m2" style="display:none; width:25%">
            &nbsp;Folder has been deleted.&nbsp;
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
                                &nbsp;<span style="cursor:pointer;" title="Back" class="sd_m2" onclick="closeDetail();">« Back</span>&nbsp;&nbsp;
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
                        
                        <div class="fl_m2" id="derdiv" style="border-right:1px solid #85B3D5; height:24px">&nbsp;&nbsp;</div>
                        
                        <div id="showmandiv" style="height:22px; padding-top:2px; display:none;" class="fl_m2 b17_m2" align="center">
                            &nbsp;&nbsp;<span id="showmanage"></span>&nbsp;&nbsp;
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
                <div class="fl_m2" style="border-right:1px solid #85B3D5; height:24px"></div>
            </div>
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
                                        <td width="150"><b>&nbsp;To:</b></td>
                                        <td><?php echo $to_name; ?>&nbsp;&lt;<?php echo $to_email; ?>&gt;</td>
                                    </tr>
                                    <tr>
                                        <td><b>&nbsp;From:</b></td>
                                        <td>
                                            <?php if ($from_admin == 1): ?>
                                                <?php echo $from_name; ?>&nbsp;&lt;<?php echo $from_email; ?>&gt;
                                            <?php else: ?>
                                                <?php echo $from_name; ?>&nbsp;&lt;<?php echo $from_email; ?>&gt;
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>&nbsp;Date:</b></td>
                                        <td><?php echo $msg_date; ?></td>
                                    </tr>
                                    <tr>
                                        <td><b>&nbsp;Subject:</b></td>
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
$allowed_tags = '<div><span> <br> <p> <b> <strong> <i> <u> <h1> <h2> <h3> <h4> <table> <tr> <td> <tbody> <img> <hr> <ul> <ol> <li>';
echo strip_tags(html_entity_decode($msg_message), $allowed_tags);
?>
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

<?php
if (isset($stmt_to) && $stmt_to !== null && is_object($stmt_to)) {
    mysqli_stmt_close($stmt_to);
}
?>