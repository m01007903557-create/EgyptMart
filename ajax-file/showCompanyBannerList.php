<?php
/**
 * File: ajax/showCompanyBannerList.php

 * Description: تحميل وعرض بانرات الشركة مع التصفح (Pagination)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف الشركة
if (!isset($_POST['cb_bnsprof_id']) || !is_numeric($_POST['cb_bnsprof_id'])) {
    http_response_code(400);
    die("Invalid company ID");
}

$cb_bnsprof_id = (int)trim($_POST['cb_bnsprof_id']);

// التحقق من وجود رقم الصفحة
if (!isset($_POST['page']) || !is_numeric($_POST['page'])) {
    http_response_code(400);
    die("Invalid page number");
}

$page = (int)$_POST['page'];

// إعدادات التصفح
$cur_page = $page;
$page -= 1;
$per_page = 5;
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

global $con;

// استعلام جلب البيانات
$sql = "SELECT cb_id, cb_image, cb_bnsprof_id 
        FROM company_banner 
        WHERE cb_bnsprof_id = ? 
        AND cb_status = '1' 
        ORDER BY cb_id DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $cb_bnsprof_id, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM company_banner 
                  WHERE cb_bnsprof_id = ? 
                  AND cb_status = '1'";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_bind_param($stmt_count, 'i', $cb_bnsprof_id);
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

// دالة عرض أزرار التصفح
function renderPagination($cb_bnsprof_id, $cur_page, $no_of_paginations, $first_btn, $previous_btn, $next_btn, $last_btn, $pagi_string): void {
    if ($no_of_paginations <= 1) return;
    ?>
    <div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="margin-bottom:15px; margin-top:15px; text-align:right">
        <div class="f1_m2 rf_m2 p9_m2">
            <!-- My PageNavigation start --><?php echo htmlspecialchars($pagi_string); ?>
            
            <?php
            // زر الصفحة الأولى
            if ($first_btn && $cur_page > 1) {
                echo '<a href="javascript:showBannerList(' . $cb_bnsprof_id . ', \'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
            } elseif ($first_btn) {
                echo '<img id="firstmail" src="images/first.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة السابقة
            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                echo '<a href="javascript:showBannerList(' . $cb_bnsprof_id . ', \'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
            } elseif ($previous_btn) {
                echo '<img id="prevmail" src="images/prevmail.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة التالية
            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                echo '<a href="javascript:showBannerList(' . $cb_bnsprof_id . ', \'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
            } elseif ($next_btn) {
                echo '<img id="nextmail" src="images/nextmail.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة الأخيرة
            if ($last_btn && $cur_page < $no_of_paginations) {
                echo '<a href="javascript:showBannerList(' . $cb_bnsprof_id . ', \'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lasten.gif"></a>';
            } elseif ($last_btn) {
                echo '<img id="lastmail" src="images/last.gif">';
            }
            ?>
            <!-- My PageNavigation end -->
        </div>
    </div>
    <?php
}

// عرض أزرار التصفح في الأعلى (إذا كان هناك نتائج)
if ($count > 0) {
    renderPagination($cb_bnsprof_id, $cur_page, $no_of_paginations, $first_btn, $previous_btn, $next_btn, $last_btn, $pagi_string);
}

// عرض البانرات
if ($count > 0) {
    while ($row = mysqli_fetch_object($result)) {
        $cb_id = (int)$row->cb_id;
        $cb_image = htmlspecialchars($row->cb_image ?? '', ENT_QUOTES, 'UTF-8');
        $bnsprof_id = (int)$row->cb_bnsprof_id;
        
        // تنظيف اسم الصورة
        $cb_image = basename($cb_image);
        $cb_image = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $cb_image);
        
        // التحقق من وجود الملف
        $image_path = "upload/company_banner/" . $cb_image;
        $full_path = __DIR__ . "/../" . $image_path;
        $image_exists = !empty($cb_image) && file_exists($full_path) && is_file($full_path);
        ?>
        <div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="margin-bottom:10px;" 
             onmouseover="showDelButt(<?php echo $cb_id; ?>);" 
             onmouseout="hideDelButt(<?php echo $cb_id; ?>);">
            
            <span style="float:right; margin-top:-18px; margin-right:-28px; display:none; width:28px; position:static" 
                  id="butt_area_<?php echo $cb_id; ?>">
                <a style="cursor:pointer;" title="Delete Banner" 
                   onclick="delBanner(<?php echo $cb_id; ?>, <?php echo $bnsprof_id; ?>);">
                    <img src="images/close_m2.png" alt="Delete">
                </a>
            </span>
            
            <div class="c3"></div>
            
            <?php if ($image_exists): ?>
                <img src="<?php echo $image_path; ?>" width="100" height="100" 
                     alt="Company Banner" style="border:1px solid #ddd; padding:5px;">
            <?php else: ?>
                <div style="width:100px; height:100px; border:1px solid #ddd; padding:5px; text-align:center; color:#999;">
                    No Image
                </div>
            <?php endif; ?>
            
            <div class="c3"></div>
        </div>
        <?php
    }
} else {
    ?>
    <div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="color:#F00; text-align:center">
        <div class="c3"></div>
        No banners listed by you.
        <div class="c3"></div>
    </div> 
    <?php
}

// عرض أزرار التصفح في الأسفل (إذا كان هناك نتائج)
if ($count > 0) {
    renderPagination($cb_bnsprof_id, $cur_page, $no_of_paginations, $first_btn, $previous_btn, $next_btn, $last_btn, $pagi_string);
}

mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>

<script type="text/javascript">
function showDelButt(id) {
    $("#butt_area_" + id).show();
}
function hideDelButt(id) {
    $("#butt_area_" + id).hide();
}
</script>