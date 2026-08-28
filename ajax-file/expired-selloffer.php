<?php
/**
 * File: ajax/expired-selloffer.php

 * Description: تحميل وعرض عروض البيع المنتهية مع التصفح (Pagination)
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

// استعلام جلب عروض البيع المنتهية
$sql_expired = "SELECT so_id, so_service, so_description, so_pic, so_updated_date 
                FROM sale_offer 
                WHERE so_usr_id = ? 
                AND so_approval_status = '1' 
                AND so_status = '1' 
                AND DATE_ADD(so_approval_date, INTERVAL so_validity DAY) < NOW()
                ORDER BY so_updated_date DESC 
                LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_expired);
mysqli_stmt_bind_param($stmt, 'iii', $current_user, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM sale_offer 
                  WHERE so_usr_id = ? 
                  AND so_approval_status = '1' 
                  AND so_status = '1' 
                  AND DATE_ADD(so_approval_date, INTERVAL so_validity DAY) < NOW()";

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

<div id="expired_offer">
    <div class="pbl_top_border1">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td background="images/gray-line.gif"><img src="images/gray-line.gif" height="2" width="1"></td>
                    <td height="33" width="100%">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td height="30">
                                        <div class="pbl_liv"><b></b></div>
                                    </td>
                                    <td>
                                        <div class="pbl_liv" align="RIGHT">
                                            <?php if ($count > 0): ?>
                                            <span class="pagenavigation">
                                                <div class="f1_m2 rf_m2 p9_m2">
                                                    <!-- My PageNavigation start -->
                                                    <b><?php echo htmlspecialchars($pagi_string); ?></b>&nbsp;&nbsp;
                                                    
                                                    <?php
                                                    // زر الصفحة الأولى
                                                    if ($first_btn && $cur_page > 1) {
                                                        echo '<a href="javascript:showExpired(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
                                                    } elseif ($first_btn) {
                                                        echo '<img id="firstmail" src="images/first.gif">';
                                                    }
                                                    echo '&nbsp;';
                                                    
                                                    // زر الصفحة السابقة
                                                    if ($previous_btn && $cur_page > 1) {
                                                        $pre = $cur_page - 1;
                                                        echo '<a href="javascript:showExpired(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                                                    } elseif ($previous_btn) {
                                                        echo '<img id="prevmail" src="images/prevmail.gif">';
                                                    }
                                                    echo '&nbsp;';
                                                    
                                                    // زر الصفحة التالية
                                                    if ($next_btn && $cur_page < $no_of_paginations) {
                                                        $nex = $cur_page + 1;
                                                        echo '<a href="javascript:showExpired(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                                                    } elseif ($next_btn) {
                                                        echo '<img id="nextmail" src="images/nextmail.gif">';
                                                    }
                                                    echo '&nbsp;';
                                                    
                                                    // زر الصفحة الأخيرة
                                                    if ($last_btn && $cur_page < $no_of_paginations) {
                                                        echo '<a href="javascript:showExpired(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
                                                    } elseif ($last_btn) {
                                                        echo '<img id="lastmail" src="images/last.gif">';
                                                    }
                                                    ?>
                                                    &nbsp;
                                                    <!-- My PageNavigation end -->
                                                </div>
                                            </span>
                                            <?php endif; ?>
                                            <b></b>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="pbl_bg_top1" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td class="pbl_top_m" height="24" width="115">Photos</td>
                    <td class="pbl_top_m" height="24">Offer Details</td>
                    <td class="pbl_top_m" height="24" width="162">Offer Stats</td>
                    <td class="pbl_top_m" height="24" width="122">Choose Action</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <table class="select_sp" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <?php if ($count > 0): ?>
                <?php while ($row_so = mysqli_fetch_object($result)): 
                    $so_id = (int)$row_so->so_id;
                    $so_service = htmlspecialchars($row_so->so_service ?? '', ENT_QUOTES, 'UTF-8');
                    $so_description = htmlspecialchars($row_so->so_description ?? '', ENT_QUOTES, 'UTF-8');
                    $so_pic = !empty($row_so->so_pic) ? htmlspecialchars($row_so->so_pic, ENT_QUOTES, 'UTF-8') : '';
                    $updated_date = !empty($row_so->so_updated_date) ? date("d M, Y", strtotime($row_so->so_updated_date)) : 'N/A';
                ?>
                <tr>
                    <td align="CENTER" valign="top" width="122">
                        <div style="border:1px solid #D5E4E9; width:100px; line-height:100px; margin:10px auto;">
                            <?php if (!empty($so_pic)): ?>
                                <?php 
                                $image_path = "upload/sale_offer/" . $so_pic;
                                $full_path = __DIR__ . "/../" . $image_path;
                                if (file_exists($full_path) && is_file($full_path)):
                                ?>
                                <img src="<?php echo $image_path; ?>" id="6363630246_1" 
                                     style="margin-right:5px; cursor:pointer;" 
                                     border="0" height="74" hspace="0" vspace="0" width="100"
                                     alt="<?php echo $so_service; ?>">
                                <?php endif; ?>
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                            <div id="6363630246_1_H" vspace="0" hspace="0" 
                                 style="display:none; position:absolute; top:0; left:0; width:0; height:0; background:#FFFFFF;" 
                                 height="90"></div>
                        </div>
                    </td>

                    <td valign="TOP">
                        <div class="mp5" style="word-wrap: break-word; width:325px;">
                            <b><a onclick="viewSODetails(<?php echo $so_id; ?>);" style="cursor:pointer;">
                                <?php echo $so_service; ?>
                            </a></b>&nbsp;&nbsp;
                            <img src="images/sell1-img.gif" alt="Your Sell Requirement" 
                                 align="ABSMIDDLE" height="17" hspace="5" vspace="2" width="38">
                            <br><?php echo $so_description; ?><br>
                            <a onclick="viewSODetails(<?php echo $so_id; ?>);" style="cursor:pointer;">
                                View complete details
                            </a><br>
                        </div>
                    </td>
                    
                    <td class="mp5" valign="TOP" width="160">
                        <b>Posted On:</b> <?php echo $updated_date; ?><br>
                    </td>
                    
                    <td class="mp5" valign="TOP" width="120">
                        <img src="images/rmv.gif" alt="Remove" align="ABSMIDDLE" 
                             height="10" hspace="5" vspace="5" width="9">
                        <a onclick="delSaleOffer(<?php echo $so_id; ?>);" style="cursor:pointer;">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" align="center" 
                        style="vertical-align:middle; color:#F00; padding-top:10px; padding-bottom:10px;">
                        No expired Sale Offer till date
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>