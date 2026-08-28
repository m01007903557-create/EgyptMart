<?php
/**
 * File: ajax/blockUserDetails.php

 * Description: عرض ملف جهة اتصال محظورة مع خيار إلغاء الحظر
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

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['uid']) || !is_numeric($_POST['uid'])) {
    http_response_code(400);
    die("Invalid user ID");
}

if (!isset($_POST['mid']) || !is_numeric($_POST['mid'])) {
    http_response_code(400);
    die("Invalid message ID");
}

$usr_id = (int)$_POST['uid'];
$msg_id = (int)$_POST['mid'];
$page = (int)($_POST['pg'] ?? 1);

global $con;

// جلب بيانات المستخدم والشركة
$sql = "SELECT u.*, bp.* 
        FROM user u
        INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        WHERE u.usr_id = ? 
        LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $usr_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("User not found");
}

// جلب بيانات الرسالة
$sql_m = "SELECT msg_date FROM message WHERE msg_id = ? LIMIT 1";
$stmt_m = mysqli_prepare($con, $sql_m);
mysqli_stmt_bind_param($stmt_m, 'i', $msg_id);
mysqli_stmt_execute($stmt_m);
$result_m = mysqli_stmt_get_result($stmt_m);
$row_m = mysqli_fetch_object($result_m);
mysqli_stmt_close($stmt_m);

// تنظيف البيانات للعرض
$user_name = htmlspecialchars(
    trim(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? '')),
    ENT_QUOTES, 
    'UTF-8'
);
$company_name = htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
$country_name = htmlspecialchars(get_country_name((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8');
$member_since = !empty($row->bnsprof_creation_date) 
    ? date("M d Y", strtotime($row->bnsprof_creation_date)) 
    : 'N/A';
$email = htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars(($row->country_ph_code ?? '') . '-' . ($row->mobile1 ?? ''), ENT_QUOTES, 'UTF-8');
$last_contacted = !empty($row_m->msg_date) 
    ? date("d M Y", strtotime($row_m->msg_date)) 
    : 'N/A';
?>

<div class="f1 p2b p14 add_b">
    <div id="dymesg" class="load_contacts" align="center">
        <div style="display: none; width: 15%;" class="c2_m2 bo_m2 lh_m2" id="loading">
            <img class="loading_m2" src="images/my2-loading.gif">&nbsp;Loading...&nbsp;
        </div>
    </div>
    
    <span class="pagenav" id="pagenav">
        <span class="f1"><h1>My Contacts</h1></span>
        
        <div class="c3"></div>
        
        <div class="mt12 ab1" id="addprof" style="display: block;">
            <!-- member profile:start -->
            <div class="ab3w f1">
                <div class="ab2 ab3 ab7" style="border-right: 1px solid #B0D4EE;">
                    <a href="javascript:back_to_list(<?php echo $page; ?>)" class="ab0 ab6 bnr f1">Back</a>
                </div>
                
                <div class="ab8" style="border-right: 1px solid #B0D4EE;">
                    <span>Member Profile</span>
                    <h2><strong></strong> <?php echo $user_name; ?></h2>
                    <p><?php echo $company_name; ?>, <?php echo $country_name; ?></p><br>
                    <p><span>(Member since: <?php echo $member_since; ?>)</span></p><br>
                    <div class="ab0 abem bnr"><?php echo $email; ?></div>
                    <div class="ab0 mobile bnr mt12"><?php echo $phone; ?></div>
                    <div style="padding-top:5px;"></div>
                </div>
                <div class="c3"></div>
            </div>

            <div class="lc1 f1" style="border-left:none;">
                <div class="lc2">Last Contacted : <span><?php echo $last_contacted; ?></span></div>
                
                <div class="ab8">
                    <table border="0" cellpadding="3" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td width="29%">&nbsp;</td>
                                <td width="71%">
                                    <input class="adr f11 fw" id="rev_submit" value="UnBlock" name="rev_submit" 
                                           onclick="unBlockUser('<?php echo $current_user; ?>', '<?php echo $usr_id; ?>')" 
                                           type="button"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="c3"></div>
            </div>
        </div>
    </span>
</div>

<?php
mysqli_stmt_close($stmt_m);
?>