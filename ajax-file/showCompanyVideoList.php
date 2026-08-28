<?php
/**
 * File: ajax/showCompanyVideoList.php

 * Description: تحميل وعرض فيديوهات الشركة مع التصفح (Pagination)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف الشركة
if (!isset($_POST['cv_bnsprof_id']) || !is_numeric($_POST['cv_bnsprof_id'])) {
    http_response_code(400);
    die("Invalid company ID");
}

$cv_bnsprof_id = (int)trim($_POST['cv_bnsprof_id']);

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
$sql = "SELECT cv_id, cv_video_link, cv_bnsprof_id 
        FROM company_video 
        WHERE cv_bnsprof_id = ? 
        AND cv_status = '1' 
        ORDER BY cv_updated_date DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $cv_bnsprof_id, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM company_video 
                  WHERE cv_bnsprof_id = ? 
                  AND cv_status = '1'";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_bind_param($stmt_count, 'i', $cv_bnsprof_id);
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
function renderPagination($cv_bnsprof_id, $cur_page, $no_of_paginations, $first_btn, $previous_btn, $next_btn, $last_btn, $pagi_string): void {
    if ($no_of_paginations <= 1) return;
    ?>
    <div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="margin-bottom:15px; margin-top:15px; text-align:right">
        <div class="f1_m2 rf_m2 p9_m2">
            <!-- My PageNavigation start --><?php echo htmlspecialchars($pagi_string); ?>
            
            <?php
            // زر الصفحة الأولى
            if ($first_btn && $cur_page > 1) {
                echo '<a href="javascript:showVideoList(' . $cv_bnsprof_id . ', \'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
            } elseif ($first_btn) {
                echo '<img id="firstmail" src="images/first.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة السابقة
            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                echo '<a href="javascript:showVideoList(' . $cv_bnsprof_id . ', \'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
            } elseif ($previous_btn) {
                echo '<img id="prevmail" src="images/prevmail.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة التالية
            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                echo '<a href="javascript:showVideoList(' . $cv_bnsprof_id . ', \'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
            } elseif ($next_btn) {
                echo '<img id="nextmail" src="images/nextmail.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة الأخيرة
            if ($last_btn && $cur_page < $no_of_paginations) {
                echo '<a href="javascript:showVideoList(' . $cv_bnsprof_id . ', \'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lasten.gif"></a>';
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
    renderPagination($cv_bnsprof_id, $cur_page, $no_of_paginations, $first_btn, $previous_btn, $next_btn, $last_btn, $pagi_string);
}

// عرض الفيديوهات
if ($count > 0) {
    while ($row = mysqli_fetch_object($result)) {
        $cv_id = (int)$row->cv_id;
        $video_link = htmlspecialchars($row->cv_video_link ?? '', ENT_QUOTES, 'UTF-8');
        $bnsprof_id = (int)$row->cv_bnsprof_id;
        ?>
        <div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="margin-bottom:10px;" 
             onmouseover="showDelButt(<?php echo $cv_id; ?>);" 
             onmouseout="hideDelButt(<?php echo $cv_id; ?>);">
            
            <span style="float:right; margin-top:-18px; margin-right:-28px; display:none; width:28px; position:static" 
                  id="butt_area_<?php echo $cv_id; ?>">
                <a style="cursor:pointer;" title="Delete Video" 
                   onclick="delVideo(<?php echo $cv_id; ?>, <?php echo $bnsprof_id; ?>);">
                    <img src="images/close_m2.png" alt="Delete">
                </a>
            </span>
            
            <div class="c3"></div>
            
            <?php 
            // محاولة استخراج YouTube video ID وتحويله إلى iframe
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_link, $matches)) {
                $video_id = $matches[1];
                echo '<iframe width="100%" height="315" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allowfullscreen></iframe>';
            } else {
                echo '<p>' . $video_link . '</p>';
            }
            ?>
            
            <div class="c3"></div>
        </div>
        <?php
    }
} else {
    ?>
    <div id="list_abt" class="mt_7 ap4 p8 s mse abtListdv" style="color:#F00; text-align:center">
        <div class="c3"></div>
        No Video listed by you.
        <div class="c3"></div>
    </div> 
    <?php
}

// عرض أزرار التصفح في الأسفل (إذا كان هناك نتائج)
if ($count > 0) {
    renderPagination($cv_bnsprof_id, $cur_page, $no_of_paginations, $first_btn, $previous_btn, $next_btn, $last_btn, $pagi_string);
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