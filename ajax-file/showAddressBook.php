<?php
declare(strict_types=1);

ob_start();

// استخدام المسار المطلق
require_once $_SERVER['DOCUMENT_ROOT'] . '/common.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lib/connect.php';

// بدء الجلسة فقط إذا لم تكن قد بدأت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    echo '<div style="padding:20px; text-align:center;">الرجاء تسجيل الدخول أولاً</div>';
    exit();
}

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من وجود رقم الصفحة
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
if ($page < 1) $page = 1;

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

// دالة الحصول على قائمة المستخدمين المحظورين
function getBlockedUserList(int $user_id, mysqli $con): string {
    $sql = "SELECT 
                CASE 
                    WHEN bu_blockBy = ? THEN bu_blocked
                    WHEN bu_blocked = ? THEN bu_blockBy
                END as blocked_user
            FROM blocked_user 
            WHERE bu_blockBy = ? OR bu_blocked = ?";
    
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) return '';
    
    mysqli_stmt_bind_param($stmt, 'iiii', $user_id, $user_id, $user_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $blocked_users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        if (!empty($row['blocked_user'])) {
            $blocked_users[] = (int)$row['blocked_user'];
        }
    }
    mysqli_stmt_close($stmt);
    
    return !empty($blocked_users) ? implode(',', $blocked_users) : '';
}

$blockedUser = getBlockedUserList($current_user, $con);
$blockedCondition = !empty($blockedUser) ? " AND u.usr_id NOT IN ($blockedUser)" : "";

// استعلام جلب جهات الاتصال (آخر رسالة مع كل مستخدم)
$sql = "SELECT m.*, u.*, bp.bnsprof_compname,
               (SELECT MAX(msg_id) FROM message 
                WHERE (msg_to = ? OR msg_from = ?) 
                AND (msg_to = u.usr_id OR msg_from = u.usr_id)) as last_msg_id
        FROM message m
        INNER JOIN user u ON (
            (m.msg_to = ? AND m.msg_from = u.usr_id) OR 
            (m.msg_from = ? AND m.msg_to = u.usr_id)
        )
        LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        WHERE (m.msg_to = ? OR m.msg_from = ?)
        AND u.usr_id != ?
        AND m.msg_id = (
            SELECT MAX(msg_id) FROM message 
            WHERE (msg_to = ? OR msg_from = ?) 
            AND (msg_to = u.usr_id OR msg_from = u.usr_id)
        )
        $blockedCondition
        ORDER BY m.msg_date DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql);
if (!$stmt) {
    echo '<div style="padding:20px; text-align:center; color:red;">خطأ في تجهيز الاستعلام</div>';
    exit();
}

mysqli_stmt_bind_param($stmt, 'iiiiiiiiiii', 
    $current_user, $current_user,  // for first subquery
    $current_user, $current_user,  // for JOIN conditions
    $current_user, $current_user,  // for WHERE conditions
    $current_user,                 // u.usr_id != ?
    $current_user, $current_user,  // for main subquery
    $start, $per_page              // LIMIT
);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(DISTINCT 
                    CASE 
                        WHEN msg_to = ? THEN msg_from
                        WHEN msg_from = ? THEN msg_to
                    END
                ) as count
                FROM message 
                WHERE msg_to = ? OR msg_from = ?
                AND msg_id IN (
                    SELECT MAX(msg_id) FROM message 
                    WHERE msg_to = ? OR msg_from = ? 
                    GROUP BY CASE 
                        WHEN msg_to = ? THEN msg_from
                        WHEN msg_from = ? THEN msg_to
                    END
                )";

$stmt_count = mysqli_prepare($con, $query_pag_num);
if ($stmt_count) {
    mysqli_stmt_bind_param($stmt_count, 'iiiiiiii', 
        $current_user, $current_user,
        $current_user, $current_user,
        $current_user, $current_user,
        $current_user, $current_user
    );
    mysqli_stmt_execute($stmt_count);
    $result_count = mysqli_stmt_get_result($stmt_count);
    $row = mysqli_fetch_assoc($result_count);
    $count = (int)($row['count'] ?? 0);
    mysqli_stmt_close($stmt_count);
} else {
    $count = 0;
}

$no_of_paginations = (int)ceil($count / $per_page);
if ($no_of_paginations < 1) $no_of_paginations = 1;
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

<div class="f1 p2b p14 add_b" style="width:80%">
    <!-- address book listing:start -->
    <div id="dymesg" class="load_contacts" align="center">
        <div style="display: none; width: 15%;" class="c2_m2 bo_m2 lh_m2" id="loading">
            <img class="loading_m2" src="/images/my2-loading.gif">&nbsp;Loading...&nbsp;
        </div>
    </div>

    <span style="display: block;" class="pagenav" id="pagenav">
        <span class="pagenav" id="pagenav">
            <span class="f1"><h1>My Contacts</h1></span>
            <div id="PageNavMaster">
                <?php if ($count > 0): ?>
                <div class="f1_m2 rf_m2 p9_m2">
                    <!-- My PageNavigation start -->
                    <?php echo htmlspecialchars($pagi_string); ?>
                    
                    <?php
                    // زر الصفحة الأولى
                    if ($first_btn && $cur_page > 1) {
                        echo '<a href="javascript:showAddressBook(\'1\')"><img id="firstmail" src="/images/firsten.gif"></a>';
                    } elseif ($first_btn) {
                        echo '<img id="firstmail" src="/images/first.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة السابقة
                    if ($previous_btn && $cur_page > 1) {
                        $pre = $cur_page - 1;
                        echo '<a href="javascript:showAddressBook(\'' . $pre . '\')"><img id="prevmail" src="/images/prven.gif"></a>';
                    } elseif ($previous_btn) {
                        echo '<img id="prevmail" src="/images/prevmail.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة التالية
                    if ($next_btn && $cur_page < $no_of_paginations) {
                        $nex = $cur_page + 1;
                        echo '<a href="javascript:showAddressBook(\'' . $nex . '\')"><img id="nextmail" src="/images/nxten.gif"></a>';
                    } elseif ($next_btn) {
                        echo '<img id="nextmail" src="/images/nextmail.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة الأخيرة
                    if ($last_btn && $cur_page < $no_of_paginations) {
                        echo '<a href="javascript:showAddressBook(\'' . $no_of_paginations . '\')"><img id="lastmail" src="/images/lastenv.gif"></a>';
                    } elseif ($last_btn) {
                        echo '<img id="lastmail" src="/images/last.gif">';
                    }
                    ?>
                    <!-- My PageNavigation end -->
                </div>
                <?php endif; ?>
            </div>
            <div class="c3"></div>  
            <div class="mt5 ab1" id="addBook">
                <span class="addressbook">
                    <div class="ab2">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" dir="ltr">
                            <tbody>
                                <tr>
                                    <td class="ab3" width="390"><span>Contact Name</span></td>
                                    <td class="ab3" width="80"><span>Country</span></td>
                                    <td class="ab3" width="80"><span>Rank</span></td>
                                    <td class="ab3" width="120"><span>Last Contacted</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($count > 0): ?>
                        <?php while ($row = mysqli_fetch_object($result)): 
                            $contact_id = (int)$row->usr_id;
                            $msg_id = (int)$row->msg_id;
                            $name_prefix = htmlspecialchars($row->name_prefix ?? '', ENT_QUOTES, 'UTF-8');
                            $fname = htmlspecialchars($row->fname ?? '', ENT_QUOTES, 'UTF-8');
                            $lname = htmlspecialchars($row->lname ?? '', ENT_QUOTES, 'UTF-8');
                            $email = htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8');
                            $compname = htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
                            $country_name = htmlspecialchars(get_country_name((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8');
                            $last_contacted = !empty($row->msg_date) ? date("d M Y", strtotime($row->msg_date)) : 'N/A';
                            
                            // جلب التقييم
                            $sql_r = "SELECT rr_rating FROM review_rating 
                                      WHERE rr_from_usr = ? AND rr_to_usr = ? LIMIT 1";
                            $stmt_r = mysqli_prepare($con, $sql_r);
                            if ($stmt_r) {
                                mysqli_stmt_bind_param($stmt_r, 'ii', $current_user, $contact_id);
                                mysqli_stmt_execute($stmt_r);
                                $result_r = mysqli_stmt_get_result($stmt_r);
                                $row_r = mysqli_fetch_object($result_r);
                                $activeStar = (int)($row_r->rr_rating ?? 0);
                                mysqli_stmt_close($stmt_r);
                            } else {
                                $activeStar = 0;
                            }
                        ?>
                        <div class="ab4">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" dir="ltr">
                                <tbody>
                                    <tr>
                                        <td width="414">
                                            <a onclick="detailContact(<?php echo $contact_id; ?>, <?php echo $msg_id; ?>, <?php echo $cur_page; ?>)" style="cursor:pointer;">
                                                <strong><?php echo trim($name_prefix . ' ' . $fname . ' ' . $lname); ?></strong> 
                                                (<?php echo $email; ?>)<br>
                                                <span><?php echo $compname; ?> &nbsp;</span>
                                            </a>
                                        </td>
                                        <td width="80">
                                            <?php echo $country_name; ?>
                                        </td>
                                        <td width="100">
                                            <ul id="ulmy_text_input1" class="starRating webwidget_rating_simple" style="list-style:none; margin:0; padding:0; display:flex; direction:ltr;">
                                                <?php for ($star = 1; $star <= $activeStar; $star++): ?>
                                                    <li class="starActive" style="display:inline-block;"><span><?php echo $star; ?></span></li>
                                                <?php endfor; ?>
                                                <?php for (; $star <= 5; $star++): ?>
                                                    <li class="starDactive" style="display:inline-block;"><span><?php echo $star; ?></span></li>
                                                <?php endfor; ?>
                                            </ul>
                                            <div style="clear:both; display:none"></div>
                                        </td>
                                        <td width="120"><?php echo $last_contacted; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="ab4">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td align="center">
                                            <font color="#FF0000">لا توجد جهات اتصال. قم بإرسال رسائل إلى أعضاء آخرين لتظهر هنا.</font>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </span>
            </div>
            <div class="mt12 ab1" id="addprof" style="display:none"></div>
        </span>
    </span>
</div>

<?php
if (isset($stmt) && $stmt) mysqli_stmt_close($stmt);
ob_end_flush();
?>