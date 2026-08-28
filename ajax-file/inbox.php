<?php
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

// التحقق من وجود رقم الصفحة
if (!isset($_POST['page']) || !is_numeric($_POST['page'])) {
    http_response_code(400);
    die("Invalid page number");
}

$page = (int)$_POST['page'];

// إعدادات التصفح
$cur_page = $page;
$page -= 1;
$per_page = 10;
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

global $con;

// استعلام جلب رسائل صندوق الوارد مع إضافة عمود source
$sql_inbox = "SELECT m.*, 
                     u.name_prefix, u.fname, u.lname, u.email,
                     bp.bnsprof_compname,
                     au.username,
                     -- تحديد مصدر الرسالة (WhatsApp أم إيميل)
                     CASE WHEN m.msg_entity = 'whatsapp_rfq' THEN 'whatsapp_rfq' ELSE 'email' END as source
              FROM message m
              LEFT JOIN user u ON m.msg_from = u.usr_id
              LEFT JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id
              LEFT JOIN admin_user au ON m.msg_from = au.id
              WHERE m.msg_to = ? 
              AND m.msg_to_status = '1' 
              ORDER BY m.msg_date DESC 
              LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_inbox);
mysqli_stmt_bind_param($stmt, 'iii', $current_user, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM message 
                  WHERE msg_to = ? 
                  AND msg_to_status = '1'";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_bind_param($stmt_count, 'i', $current_user);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row_count = mysqli_fetch_assoc($result_count);
$count = (int)($row_count['count'] ?? 0);

$no_of_paginations = (int)ceil($count / $per_page);
$pagi_string = "Page " . ($cur_page) . " of " . $no_of_paginations;

// حساب نطاق أزرار التصفح
$start_loop = 1;
$end_loop = $no_of_paginations;

if ($cur_page >= 7) {
    $start_loop = $cur_page - 3;
    if ($no_of_paginations > $cur_page + 3) {
        $end_loop = $cur_page + 3;
    } elseif ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    }
} else {
    $start_loop = 1;
    $end_loop = $no_of_paginations > 7 ? 7 : $no_of_paginations;
}
?>

<div class="fl_m2 my_m2">
    <div class="fl_m2">
        <!--div class="bc f11">Enquiries »</div-->
    </div>
</div>
<span id="fol_m2"><h1>Inbox</h1></span><!-- My Mailbox start -->

<div class="fl_m2 my_m2" id="inbox" align="left">
    <br>
    <div id="yeartabs">
        <div class="tabs d">
            <ul id="date-list" class="tabs clearfix"></ul>
        </div>
    </div>

    <div class="b11_m2 tmsg_m2" id="dymesg" align="center" style="height:2px;">
        <div id="loading" class="c2_m2 bo_m2 lh_m2" style="width:15%; display:none;">
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
            <?php if ($count > 0): ?>
            <span class="pagenavigation">
                <div class="f1_m2 rf_m2 p9_m2">
                    <!-- My PageNavigation start -->
                    <?php echo htmlspecialchars($pagi_string); ?>  

                    <?php
                    // زر الصفحة الأولى
                    if ($first_btn && $cur_page > 1) {
                        echo '<a href="javascript:showInbox(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
                    } elseif ($first_btn) {
                        echo '<img id="firstmail" src="images/first.gif">';
                    }
                    echo ' ';

                    // زر الصفحة السابقة
                    if ($previous_btn && $cur_page > 1) {
                        $pre = $cur_page - 1;
                        echo '<a href="javascript:showInbox(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                    } elseif ($previous_btn) {
                        echo '<img id="prevmail" src="images/prevmail.gif">';
                    }
                    echo ' ';

                    // زر الصفحة التالية
                    if ($next_btn && $cur_page < $no_of_paginations) {
                        $nex = $cur_page + 1;
                        echo '<a href="javascript:showInbox(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                    } elseif ($next_btn) {
                        echo '<img id="nextmail" src="images/nextmail.gif">';
                    }
                    echo ' ';

                    // زر الصفحة الأخيرة
                    if ($last_btn && $cur_page < $no_of_paginations) {
                        echo '<a href="javascript:showInbox(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
                    } elseif ($last_btn) {
                        echo '<img id="lastmail" src="images/last.gif">';
                    }
                    ?>

                    <!-- My PageNavigation end -->
                </div>
            </span>
            <?php endif; ?>
            <div style="clear:both;"></div>
        </div>
    </div>

    <div id="mhmdd">
        <div class="f4_m2 b5_m2 b11_m2 b10_m2 b7_m2 bg2_m2 ac_m2 p6_m2 hh_m2 b18_m2" id="mainheader">
            <div id="mailboxheader">
                <div id="selectfolder" class="box_m2">
                    <div id="mailboxoptions" class="fl_m2 lh5_m2">
                        <span id="backdrop"></span>
                    </div>
                    <div class="fl_m2 lh5_m2" id="mailoption">
                        <span id="reply_m2"></span>
                        <div class="fl_m2 hh_m2 b17_m2" style="display:none;" id="muldiv"></div>
                        <div class="fl_m2 hh_m2 b17_m2" id="deldiv"></div>
                        <div id="delete" class="horizontalcssmenu_delete_m2 horizontalcssmenu_m2 fl_m2 lh4_m2">
                            <ul id="delete_m2" style="margin-top:0px; padding-top:0pt;">
                                <li style="z-index:0; margin:0px; padding:0pt;"> 
                                    <span id="deleteall_m2">
                                        <a title="Delete" iscontextmenu="true" onclick="deleteInbox()"></a>
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <div class="fl_m2" id="derdiv" style="border-right:1px solid #ead5ff; height:24px">  </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="b11_m2" id="ci_m2"><br></div>
    </div>

    <div class="b11_m2" id="repseq"></div>

    <span class="mailbox">
        <form name="m_inbox" id="m_inbox" method='post'>
            <?php if ($count > 0): ?>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr class="f1_m2">
                        <td class="lh_m2 msg_m2 b9_m2 b10_m2">
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr class="c1_m2 bo_m2 bg_m2 ff_m2 f3_m2">
                                        <td class="p2_m2 sb_m2 b_m2" width="3%">
                                            <input class="select-all" name="check_all" value="yes" id="check_all" type="checkbox" onClick="return checkedAll_inbox();">
                                        </td>
                                        <td class="b_m2 sb_m2 p1_m2 dp_m2" width="40%">Sender </td> <!-- Reduced width slightly -->
                                        <td class="b_m2 sb_m2 p1_m2 dp_m2" width="10%">Type </td> <!-- New Type Column -->
                                        <td class="sb_m2 p_m2" width="36%">Subject </td> <!-- Adjusted width -->
                                        <td class="sb_m2 p_m2" width="11%">Date </td>
                                    </tr>

                                    <?php while ($row = mysqli_fetch_object($result)): 
                                        $msg_id = (int)$row->msg_id;
                                        $msg_read = (int)($row->msg_read ?? 0);
                                        $bg_color = ($msg_read == 0) ? '#F5ECFF' : '';

                                        // تحديد اسم المرسل
                                        $sender_name = '';
                                        if (!empty($row->bnsprof_compname)) {
                                            $sender_name = htmlspecialchars($row->bnsprof_compname, ENT_QUOTES, 'UTF-8');
                                        } elseif (!empty($row->username)) {
                                            $sender_name = htmlspecialchars($row->username, ENT_QUOTES, 'UTF-8');
                                        } else {
                                            $sender_name = htmlspecialchars(
                                                trim(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? '')),
                                                ENT_QUOTES, 
                                                'UTF-8'
                                            );
                                        }

                                        // تحديد نوع الرسالة وأيقونتها
                                        $type_html = '';
                                        if ($row->source == 'whatsapp_rfq') {
                                            $type_html = '<span style="color:#25D366;"><i class="fa fa-whatsapp"></i> WhatsApp</span>';
                                        } else {
                                            $type_html = '<span style="color:#2196F3;"><i class="fa fa-envelope"></i> Email</span>';
                                        }

                                        $msg_subject = !empty($row->msg_subject) 
                                            ? htmlspecialchars(stripslashes($row->msg_subject), ENT_QUOTES, 'UTF-8') 
                                            : 'No Subject';

                                        $msg_date = !empty($row->msg_date) 
                                            ? date("d M Y", strtotime($row->msg_date)) 
                                            : 'N/A';
                                    ?>
                                    <tr class="f1_m2" id="mail<?php echo $msg_id; ?>" 
                                        style="cursor:pointer; <?php echo $bg_color; ?>" 
                                        onmouseover="document.getElementById(this.id).className='f1_m2 mailbc_m2';" 
                                        onmouseout="document.getElementById(this.id).className='f1_m2';">

                                        <td class="p2_m2">
                                            <div class="td_m2">
                                                <input id="cbI" name="cbI" type="checkbox" value="<?php echo $msg_id; ?>">
                                            </div>
                                        </td>

                                        <td class="p1_m2" onclick="openmail('<?php echo $msg_id; ?>','inbox')">
                                            <div class="td_m2" style="width:200px"><?php echo $sender_name; ?></div>
                                        </td>

                                        <!-- New Type Column -->
                                        <td class="p1_m2" onclick="openmail('<?php echo $msg_id; ?>','inbox')">
                                            <div class="td_m2" style="width:80px"><?php echo $type_html; ?></div>
                                        </td>

                                        <td class="p_m2" onclick="openmail('<?php echo $msg_id; ?>','inbox')">
                                            <div class="td_m2" style="width:320px" title="Click to open this Enquiry">
                                                <?php echo $msg_subject; ?>
                                            </div>
                                        </td>

                                        <td class="p_m2" onclick="openmail('<?php echo $msg_id; ?>','inbox')">
                                            <div class="td_m2"><?php echo $msg_date; ?></div>
                                        </td>
                                     </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php else: ?>
            <table cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr class="f1_m2">
                        <td>
                            <div class="bo_m2 f3_m2 b9_m2 b12_m2 lh1_m2" id="nor_m2" align="center">
                                There are no messages in your Inbox.
                            </div>
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr class="co_m2 bo_m2">
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </form>
    </span>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>