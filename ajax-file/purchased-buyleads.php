<?php
/**
 * File: ajax/purchased-buyleads.php

 * Description: تحميل وعرض طلبات الشراء المشتراة مع التصفح (Pagination)
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

// استعلام جلب طلبات الشراء المشتراة
$sql = "SELECT br.*, pbr.* 
        FROM buy_requirement br
        INNER JOIN purchased_buy_requirement pbr ON pbr.pbr_br_id = br.br_id
        WHERE br.br_display_status = '1' 
        AND br.br_status = '1' 
        AND pbr.pbr_usr_id = ? 
        ORDER BY pbr.pbr_purchase_date DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $current_user, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM buy_requirement br
                  INNER JOIN purchased_buy_requirement pbr ON pbr.pbr_br_id = br.br_id
                  WHERE br.br_display_status = '1' 
                  AND br.br_status = '1' 
                  AND pbr.pbr_usr_id = ?";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_bind_param($stmt_count, 'i', $current_user);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row_c = mysqli_fetch_assoc($result_count);
$count = (int)($row_c['count'] ?? 0);

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
<!-- تضمين مكتبة ColorBox -->
<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">

<script>
    $(document).ready(function() {
        $(".ajax").colorbox({ width: "61%" });
    });
</script>

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
                                                                        echo '<a href="javascript:purchasedBuyleads(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
                                                                    } elseif ($first_btn) {
                                                                        echo '<img id="firstmail" src="images/first.gif">';
                                                                    }
                                                                    echo '&nbsp;';
                                                                    
                                                                    // زر الصفحة السابقة
                                                                    if ($previous_btn && $cur_page > 1) {
                                                                        $pre = $cur_page - 1;
                                                                        echo '<a href="javascript:purchasedBuyleads(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                                                                    } elseif ($previous_btn) {
                                                                        echo '<img id="prevmail" src="images/prevmail.gif">';
                                                                    }
                                                                    echo '&nbsp;';
                                                                    
                                                                    // زر الصفحة التالية
                                                                    if ($next_btn && $cur_page < $no_of_paginations) {
                                                                        $nex = $cur_page + 1;
                                                                        echo '<a href="javascript:purchasedBuyleads(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                                                                    } elseif ($next_btn) {
                                                                        echo '<img id="nextmail" src="images/nextmail.gif">';
                                                                    }
                                                                    echo '&nbsp;';
                                                                    
                                                                    // زر الصفحة الأخيرة
                                                                    if ($last_btn && $cur_page < $no_of_paginations) {
                                                                        echo '<a href="javascript:purchasedBuyleads(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
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
                                <td class="pbl_top_mBuy" height="24">تفاصيل طلب الشراء</td>
                                <td class="pbl_top_mBuy" height="24" width="208"></td>
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
                    $pbr_id = (int)$row->pbr_id;
                    $br_id = (int)$row->br_id;
                    $br_u_id = (int)$row->br_u_id;
                    $br_pd_name = htmlspecialchars($row->br_pd_name ?? '', ENT_QUOTES, 'UTF-8');
                    $br_requirement = htmlspecialchars(stripslashes($row->br_requirement ?? ''), ENT_QUOTES, 'UTF-8');
                    $br_approval_status = $row->br_approval_status ?? '0';
                    $purchase_date = !empty($row->pbr_purchase_date) ? date("d M, Y", strtotime($row->pbr_purchase_date)) : 'N/A';
                    
                    // إنشاء رابط الاستفسار
                    $enquiry_link = "sendLeadEnquiry-form.php?id=" . rand(1000, 9999) . md5((string)$br_u_id) . "&headline=" . urlencode($br_pd_name);
                ?>
                <tr>
                    <td valign="TOP">
                        <div class="mp5 blhd" style="border-right: 1px solid #E7EAEE; word-wrap: break-word; width: 800px;">
                            <p>
                                <a onclick="detailPurBuyleads(<?php echo $pbr_id; ?>)" style="cursor:pointer;">
                                    <?php echo $br_pd_name; ?>
                                </a>
                                <?php if ($br_approval_status == '0'): ?>
                                    <img src="images/waiting_ico1.png" 
                                         title="&lt;b&gt;Your Buy Requirement is under review by our system&lt;/b&gt;" 
                                         id="imgWaiting" name="imgWaiting" class="imgWaiting" align="absmiddle">
                                <?php endif; ?>
                            </p>
                            <div style="padding:0 0 5px 0; font-size:11px; color:#727272;">
                                <b>Purchased On :</b> <?php echo $purchase_date; ?>
                            </div>
                            <?php echo $br_requirement; ?>
                            
                            <div class="blvd">
                                <a onclick="detailPurBuyleads(<?php echo $pbr_id; ?>)" style="cursor:pointer;">
                                    شاهد التفاصيل بالكامل
                                </a>
                            </div>
                            <div class="blvd"></div>
                        </div>
                    </td>
                    <td class="mp5" valign="TOP" width="208">
                        <div class="bulletImage fleft">
                            <a id="apprvDel1" class="apprvDel" style="cursor:pointer;" 
                               onClick="delRequirement(<?php echo $br_id; ?>, 'op');"></a>
                        </div>
                        <div class="bulletImage fleft">
                            <a class="ajax" rel="nofollow" href="<?php echo $enquiry_link; ?>">
                                إرسل إستفسارك
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
    <div style="clear:both;">
        <div class="pbl-sr" style="background-position:0px -35px; min-height:90px; color:#F00;" align="center">
            <br><b style="line-height:50px;">You do not have any Purchased Buy Leads.</b><br><br>
        </div>
        <div id="switchDiv" style="display:none;"></div>
    </div>
<?php endif; ?>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>