<?php
declare(strict_types=1);

ob_start();

require_once __DIR__ . '/../common.php';
require_once dirname(__DIR__) . '/lib/connect.php';

$current_user = $_SESSION['uid_indm'] ?? 0;
if (!$current_user) {
    echo "error: not logged in";
    exit;
}

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



//$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
//$per_page = 10;

// استعلام يجلب جميع الرسائل مع تحديد المصدر
$sql_inbox = "SELECT m.*, 
                     u.name_prefix, u.fname, u.lname, u.email,
                     bp.bnsprof_compname,
                     au.username,
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

// العدد الإجمالي
$sql_count = "SELECT COUNT(*) as total FROM message WHERE msg_to = ? AND msg_to_status = '1'";
$stmt_count = mysqli_prepare($con, $sql_count);
mysqli_stmt_bind_param($stmt_count, 'i', $current_user);
mysqli_stmt_execute($stmt_count);
$count_result = mysqli_stmt_get_result($stmt_count);
$total_row = mysqli_fetch_assoc($count_result);
$total_messages = $total_row['total'];
?>



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
        <!--div class="bc f11">Enquiries &#187;</div-->
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
            <?php if ($count > 0): ?>
            <span class="pagenavigation">
                <div class="f1_m2 rf_m2 p9_m2">
                    <!-- My PageNavigation start -->
                    <?php echo htmlspecialchars($pagi_string); ?>&nbsp;&nbsp;
                    
                    <?php
                    // زر الصفحة الأولى
                    if ($first_btn && $cur_page > 1) {
                        echo '<a href="javascript:showInbox(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
                    } elseif ($first_btn) {
                        echo '<img id="firstmail" src="images/first.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة السابقة
                    if ($previous_btn && $cur_page > 1) {
                        $pre = $cur_page - 1;
                        echo '<a href="javascript:showInbox(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                    } elseif ($previous_btn) {
                        echo '<img id="prevmail" src="images/prevmail.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة التالية
                    if ($next_btn && $cur_page < $no_of_paginations) {
                        $nex = $cur_page + 1;
                        echo '<a href="javascript:showInbox(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                    } elseif ($next_btn) {
                        echo '<img id="nextmail" src="images/nextmail.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة الأخيرة
                    if ($last_btn && $cur_page < $no_of_paginations) {
                        echo '<a href="javascript:showInbox(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
                    } elseif ($last_btn) {
                        echo '<img id="lastmail" src="images/last.gif">';
                    }
                    ?>
                    &nbsp;
                    <!-- My PageNavigation end -->
                </div>
            </span>
            <?php endif; ?>
            <div style="clear:both;"></div>
        </div>




<div class="inbox-table">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="30"><input type="checkbox" id="checkAll"></th>
                <th>Sender</th>
                <th width="80">Type</th>
                <th>Subject</th>
                <th width="120">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_object($result)): 
                $sender_name = '';
                if (!empty($row->bnsprof_compname)) {
                    $sender_name = htmlspecialchars($row->bnsprof_compname);
                } elseif (!empty($row->username)) {
                    $sender_name = htmlspecialchars($row->username);
                } else {
                    $sender_name = htmlspecialchars(trim(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? '')));
                }
                
                $type_label = ($row->source == 'whatsapp_rfq') ? '<span class="label label-success"><i class="fa fa-whatsapp"></i> WhatsApp</span>' : '<span class="label label-info"><i class="fa fa-envelope"></i> Email</span>';
                
                $bg_color = ($row->msg_read == 0) ? '#F5ECFF' : '';
            ?>
            <tr class="inbox-row" style="cursor:pointer; <?php echo $bg_color; ?>" data-id="<?php echo $row->msg_id; ?>">
                <td class="text-center">
                    <input type="checkbox" class="msg_checkbox" value="<?php echo $row->msg_id; ?>">
                </td>
                <td class="sender"><?php echo $sender_name; ?></td>
                <td class="type"><?php echo $type_label; ?></td>
                <td class="subject"><?php echo htmlspecialchars($row->msg_subject); ?></td>
                <td class="date"><?php echo date('Y-m-d H:i', strtotime($row->msg_date)); ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if (mysqli_num_rows($result) == 0): ?>
            <tr>
                <td colspan="5" class="text-center">لا توجد رسائل</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// تحديد الكل
$('#checkAll').click(function() {
    $('.msg_checkbox').prop('checked', this.checked);
});

// فتح الرسالة عند النقر على الصف
$('.inbox-row').click(function(e) {
    if (e.target.type !== 'checkbox') {
        var msgId = $(this).data('id');
        if (msgId) {
            openmail(msgId, 'inbox');
        }
    }
});
</script>