<?php
/**
 * File: ajax/openRequirement.php

 * Description: تحميل وعرض طلبات الشراء المفتوحة للمستخدم مع التصفح (Pagination)
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

// استعلام جلب طلبات الشراء المفتوحة
$sql_inbox = "SELECT br.*, u.*, bp.* 
              FROM buy_requirement br
              INNER JOIN user u ON br.br_u_id = u.usr_id
              INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
              WHERE br.br_u_id = ? 
              AND br.br_display_status = '1' 
              AND br.br_status = '1' 
              ORDER BY br.br_updated_date DESC 
              LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_inbox);
mysqli_stmt_bind_param($stmt, 'iii', $current_user, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM buy_requirement 
                  WHERE br_u_id = ? 
                  AND br_display_status = '1' 
                  AND br_status = '1'";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_bind_param($stmt_count, 'i', $current_user);
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

<?php if ($count > 0): ?>
<div class="pbl_top_borderBuy">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td height="33" width="100%">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td height="33" width="100%">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td height="30">
                                                    <div class="pbl_liv" style="font-family:arial; color:#474747; font-size:12px">
                                                        <b><!--Displaying 1 - 1 of total 1 Open Buy Requirements--></b>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="pbl_liv" align="RIGHT">
                                                        <b>
                                                            <span class="pagenavigation">
                                                                <div class="f1_m2 rf_m2 p9_m2">
                                                                    <!-- My PageNavigation start -->
                                                                    <?php echo htmlspecialchars($pagi_string); ?>&nbsp;&nbsp;
                                                                    
                                                                    <?php
                                                                    // زر الصفحة الأولى
                                                                    if ($first_btn && $cur_page > 1) {
                                                                        echo '<a href="javascript:openRequirement(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
                                                                    } elseif ($first_btn) {
                                                                        echo '<img id="firstmail" src="images/first.gif">';
                                                                    }
                                                                    echo '&nbsp;';
                                                                    
                                                                    // زر الصفحة السابقة
                                                                    if ($previous_btn && $cur_page > 1) {
                                                                        $pre = $cur_page - 1;
                                                                        echo '<a href="javascript:openRequirement(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                                                                    } elseif ($previous_btn) {
                                                                        echo '<img id="prevmail" src="images/prevmail.gif">';
                                                                    }
                                                                    echo '&nbsp;';
                                                                    
                                                                    // زر الصفحة التالية
                                                                    if ($next_btn && $cur_page < $no_of_paginations) {
                                                                        $nex = $cur_page + 1;
                                                                        echo '<a href="javascript:openRequirement(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                                                                    } elseif ($next_btn) {
                                                                        echo '<img id="nextmail" src="images/nextmail.gif">';
                                                                    }
                                                                    echo '&nbsp;';
                                                                    
                                                                    // زر الصفحة الأخيرة
                                                                    if ($last_btn && $cur_page < $no_of_paginations) {
                                                                        echo '<a href="javascript:openRequirement(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
                                                                    } elseif ($last_btn) {
                                                                        echo '<img id="lastmail" src="images/last.gif">';
                                                                    }
                                                                    ?>
                                                                    &nbsp;
                                                                    <!-- My PageNavigation end -->
                                                                </div>
                                                            </span>
                                                        </b>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <table class="pbl_bg_topBuy" id="toptitle" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td class="pbl_top_mBuy" height="24">Buy Requirement Details</td>
                                <td class="pbl_top_mBuy" height="24" width="208">Choose Action</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="bllistinmain">
    <div id="Listing1">
        <table class="selectsp1" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <?php while ($row = mysqli_fetch_object($result)): 
                    $br_id = (int)$row->br_id;
                    $br_pd_name = htmlspecialchars($row->br_pd_name ?? '', ENT_QUOTES, 'UTF-8');
                    $br_requirement = htmlspecialchars(stripslashes($row->br_requirement ?? ''), ENT_QUOTES, 'UTF-8');
                    $br_pic = !empty($row->br_pic) ? htmlspecialchars($row->br_pic, ENT_QUOTES, 'UTF-8') : '';
                    $br_approval_status = $row->br_approval_status ?? '0';
                    $updated_date = !empty($row->br_updated_date) ? date("d M, Y", strtotime($row->br_updated_date)) : 'N/A';
                    
                    // التحقق من وجود الصورة
                    $image_path = "upload/buy_requirement/" . (!empty($br_pic) ? $br_pic : "no-image.png");
                    $full_path = __DIR__ . "/../" . $image_path;
                    $image_exists = file_exists($full_path) && is_file($full_path);
                ?>
                <tr>
                    <td>
                        <?php if ($image_exists): ?>
                            <img src="<?php echo $image_path; ?>" id="6390059595_1" 
                                 border="0" height="100" hspace="0" vspace="0" width="125"
                                 alt="<?php echo $br_pd_name; ?>">
                        <?php else: ?>
                            <div style="width:125px; height:100px; border:1px solid #ccc; text-align:center; line-height:100px;">
                                No Image
                            </div>
                        <?php endif; ?>
                    </td>
                    
                    <td valign="TOP">
                        <div class="mp5 blhd" style="border-right: 1px solid #E7EAEE;">
                            <p>
                                <a onclick="detailRequirement(<?php echo $br_id; ?>)" style="cursor:pointer;">
                                    <?php echo $br_pd_name; ?>
                                </a>
                                <?php if ($br_approval_status == '0'): ?>
                                    <img src="images/waiting_ico1.png" 
                                         title="&lt;b&gt;Your Buy Requirement is under review by our system&lt;/b&gt;" 
                                         id="imgWaiting" name="imgWaiting" class="imgWaiting" align="absmiddle">
                                <?php endif; ?>
                            </p>
                            <div style="padding:0 0 5px 0; font-size:11px; color:#727272;">
                                <b>Posted on:</b> <?php echo $updated_date; ?>
                            </div>
                            <?php echo $br_requirement; ?>
                            <div class="blvd">
                                <a onclick="detailRequirement(<?php echo $br_id; ?>)" style="cursor:pointer;">
                                    View complete details
                                </a>
                            </div>
                        </div>
                    </td>
                    
                    <td class="mp5" valign="TOP" width="208">
                        <div class="bulletImage">
                            <a id="apprvDel1" class="apprvDel" style="cursor:pointer;" 
                               onClick="closeRequirement(<?php echo $br_id; ?>, 'op');">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
    <div style="font-size:12px; color:#b60000; padding:10px 0 10px 0;" align="center">
        There are no Open Requirements to manage.
    </div>
<?php endif; ?>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>