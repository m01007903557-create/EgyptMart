<?php
/**
 * File: ajax/showBlockList.php

 * Description: تحميل وعرض قائمة المستخدمين المحظورين مع التصفح (Pagination)
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

// استعلام جلب المستخدمين المحظورين
$sql = "SELECT DISTINCT u.*, b.*, bp.bnsprof_compname 
        FROM user u
        INNER JOIN blocked_user b ON (
            (b.bu_blockBy = ? AND b.bu_blocked = u.usr_id)
            OR 
            (b.bu_blocked = ? AND b.bu_blockBy = u.usr_id)
        )
        LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        ORDER BY b.bu_updated_date DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iiii', $current_user, $current_user, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(DISTINCT u.usr_id) as count 
                  FROM user u
                  INNER JOIN blocked_user b ON (
                      (b.bu_blockBy = ? AND b.bu_blocked = u.usr_id)
                      OR 
                      (b.bu_blocked = ? AND b.bu_blockBy = u.usr_id)
                  )";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_bind_param($stmt_count, 'ii', $current_user, $current_user);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row = mysqli_fetch_assoc($result_count);
$count = (int)($row['count'] ?? 0);

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

<div class="f1 p2b p14 add_b" style="width:80%">
    <!-- address book listing:start-->
    <div id="dymesg" class="load_contacts" align="center">
        <div style="display: none; width: 15%;" class="c2_m2 bo_m2 lh_m2" id="loading">
            <img class="loading_m2" src="images/my2-loading.gif">&nbsp;Loading...&nbsp;
        </div>
    </div>

    <span style="display: block;" class="pagenav" id="pagenav">
        <span class="pagenav" id="pagenav">
            <span class="f1"><h1>My Block List</h1></span>
            <div id="PageNavMaster">
                <?php if ($count > 0): ?>
                <div class="f1_m2 rf_m2 p9_m2">
                    <!-- My PageNavigation start -->
                    <?php echo htmlspecialchars($pagi_string); ?>
                    
                    <?php
                    // زر الصفحة الأولى
                    if ($first_btn && $cur_page > 1) {
                        echo '<a href="javascript:showBlockList(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
                    } elseif ($first_btn) {
                        echo '<img id="firstmail" src="images/first.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة السابقة
                    if ($previous_btn && $cur_page > 1) {
                        $pre = $cur_page - 1;
                        echo '<a href="javascript:showBlockList(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                    } elseif ($previous_btn) {
                        echo '<img id="prevmail" src="images/prevmail.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة التالية
                    if ($next_btn && $cur_page < $no_of_paginations) {
                        $nex = $cur_page + 1;
                        echo '<a href="javascript:showBlockList(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                    } elseif ($next_btn) {
                        echo '<img id="nextmail" src="images/nextmail.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة الأخيرة
                    if ($last_btn && $cur_page < $no_of_paginations) {
                        echo '<a href="javascript:showBlockList(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
                    } elseif ($last_btn) {
                        echo '<img id="lastmail" src="images/last.gif">';
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
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td class="ab3" width="410"><span>Contact Name</span></td>
                                    <td class="ab3" width="100"><span>Country</span></td>
                                    <td class="ab3" width="120"><span>Blocked On</span></td>
                                    <td class="ab3" width="80"><span>&nbsp;</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($count > 0): ?>
                        <?php while ($row = mysqli_fetch_object($result)): 
                            $name_prefix = htmlspecialchars($row->name_prefix ?? '', ENT_QUOTES, 'UTF-8');
                            $fname = htmlspecialchars($row->fname ?? '', ENT_QUOTES, 'UTF-8');
                            $lname = htmlspecialchars($row->lname ?? '', ENT_QUOTES, 'UTF-8');
                            $email = htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8');
                            $compname = htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
                            $country_name = htmlspecialchars(get_country_name((int)($row->country ?? 0)), ENT_QUOTES, 'UTF-8');
                            $blocked_date = !empty($row->bu_updated_date) ? date("d M Y", strtotime($row->bu_updated_date)) : 'N/A';
                            $blocked_user_id = (int)$row->usr_id;
                        ?>
                        <div class="ab4">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td width="410">
                                            <strong><?php echo trim($name_prefix . ' ' . $fname . ' ' . $lname); ?></strong> 
                                            (<?php echo $email; ?>)<br>
                                            <span><?php echo $compname; ?> &nbsp;</span>
                                        </td>
                                        <td width="100">
                                            <?php echo $country_name; ?>
                                        </td>
                                        <td width="120"><?php echo $blocked_date; ?></td>
                                        <td width="80">
                                            <input class="adr f11 fw" style="cursor:pointer;" type="button" 
                                                   title="UnBlock" value="UnBlock" 
                                                   onclick="unBlockUser('<?php echo $current_user; ?>', '<?php echo $blocked_user_id; ?>')"/>
                                        </td>
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
                                            <font color="#FF0000">No Blocked Contacts listed.</font>
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
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>