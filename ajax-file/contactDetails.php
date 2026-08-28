<?php
/**
 * File: ajax/contactDetails.php

 * Description: عرض ملف جهة الاتصال مع إمكانية التقييم وإضافة ملاحظات
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

// جلب بيانات التقييم
$sql_rr = "SELECT rr_id, rr_rating, rr_review 
           FROM review_rating 
           WHERE rr_from_usr = ? AND rr_to_usr = ? 
           LIMIT 1";

$stmt_rr = mysqli_prepare($con, $sql_rr);
mysqli_stmt_bind_param($stmt_rr, 'ii', $current_user, $usr_id);
mysqli_stmt_execute($stmt_rr);
$result_rr = mysqli_stmt_get_result($stmt_rr);
$row_rr = mysqli_fetch_object($result_rr);
mysqli_stmt_close($stmt_rr);

$activeStar = (int)($row_rr->rr_rating ?? 0);
$rr_id = (int)($row_rr->rr_id ?? 0);
$rr_review = htmlspecialchars($row_rr->rr_review ?? '', ENT_QUOTES, 'UTF-8');

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

// تحديد نص زر التقييم
$review_button_text = !empty($rr_review) ? 'UPDATE' : 'ADD';
?>

<div class="f1 p2b p14 add_b" style="width:80%">
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
                <div class="ab2 ab3 ab7">
                    <a href="javascript:back_to_list(<?php echo $page; ?>)" class="ab0 ab6 bnr f1">Back</a>
                </div>
                
                <div class="ab8">
                    <span>Member Profile</span>
                    <h2><strong></strong> <?php echo $user_name; ?></h2>
                    <p><?php echo $company_name; ?>, <?php echo $country_name; ?></p><br>
                    <p><span>(Member since: <?php echo $member_since; ?>)</span></p><br>
                    <div class="ab0 abem bnr"><?php echo $email; ?></div>
                    <div class="ab0 mobile bnr mt12"><?php echo $phone; ?></div>
                    <div style="padding-top:5px;">
                        <input class="adr f11 fw" id="rev_submit" value="Block" name="rev_submit" 
                               onclick="blockUser('<?php echo $current_user; ?>', '<?php echo $usr_id; ?>')" 
                               type="button">
                    </div>
                </div>
                <div class="c3"></div>
            </div>

            <div class="lc1 f1">
                <div class="lc2">Last Contacted : <span><?php echo $last_contacted; ?></span></div>
                
                <div class="ab8">
                    <table border="0" cellpadding="3" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <script type="text/javascript">
                                function giveRating(r) {
                                    $("#rr_rating").val(r);
                                    for (var i = 1; i <= r; i++) {
                                        $("#rating" + i).removeClass("starDactive").addClass("starActive");
                                    }
                                    for (; i <= 5; i++) {
                                        $("#rating" + i).removeClass("starActive").addClass("starDactive");
                                    }
                                }
                                
                                function update_review() {
                                    var id = $("#rr_id").val();
                                    var r = $("#rr_rating").val();
                                    var rv = $("#rr_review").val();
                                    
                                    if (rv == '') {
                                        alert('Please write your remarks first');
                                        $("#rr_review").focus();
                                    } else {
                                        $.post("ajax-file/addReview.php", {
                                            id: id,
                                            r: r,
                                            rv: rv
                                        }, function(data) {
                                            alert('Remarks Posted Successfully');
                                            $("#rev_submit").val("UPDATE");
                                        });
                                    }
                                }
                                </script>
                                
                                <td width="29%"><span>Rank:</span></td>
                                <td width="71%">
                                    <ul id="ulmy_text2" class="starRating webwidget_rating_simple">
                                        <?php for ($star = 1; $star <= $activeStar; $star++): ?>
                                        <li class="starActive" id="rating<?php echo $star; ?>" 
                                            onclick="giveRating('<?php echo $star; ?>')">
                                            <span><?php echo $star; ?></span>
                                        </li>
                                        <?php endfor; ?>
                                        <?php for (; $star <= 5; $star++): ?>
                                        <li class="starDactive" id="rating<?php echo $star; ?>" 
                                            onclick="giveRating('<?php echo $star; ?>')">
                                            <span><?php echo $star; ?></span>
                                        </li>
                                        <?php endfor; ?>
                                    </ul>
                                    <input type="hidden" id="rr_id" name="rr_id" value="<?php echo $rr_id; ?>">
                                    <input type="hidden" id="rr_rating" name="rr_rating" value="<?php echo $activeStar; ?>">
                                    <div style="clear:both; display:none"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <table border="0" cellpadding="3" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td width="29%"><span>Remarks:</span></td>
                                <td width="71%">You can add remarks here</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <table border="0" cellpadding="3" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td width="29%">&nbsp;</td>
                                <td width="71%">
                                    <textarea class="mu11" style="resize:none;" rows="5" cols="17" 
                                              maxlength="1000" id="rr_review" name="rr_review"><?php echo $rr_review; ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td width="29%">&nbsp;</td>
                                <td width="71%">
                                    <input class="adr f11 fw" id="rev_submit" value="<?php echo $review_button_text; ?>" 
                                           name="rev_submit" onclick="update_review()" type="button">
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
mysqli_stmt_close($stmt_rr);
?>