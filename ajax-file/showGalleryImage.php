<?php
/**
 * File: ajax/loadGalleryImages.php
 * Description: تحميل وعرض صور معرض المستخدم مع التصفح (Pagination)
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

// التحقق من وجود رقم الصفحة
if (!isset($_POST['page']) || !is_numeric($_POST['page'])) {
    http_response_code(400);
    die("Invalid page number");
}

$page = (int)$_POST['page'];
$user_id = (int)$_SESSION['uid_indm'];

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

// استعلام جلب البيانات
$sql_active = "SELECT ph_id, ph_fileName, ph_updated_date 
               FROM photo 
               WHERE ph_status = '1' 
               AND ph_u_id = ? 
               ORDER BY ph_id DESC 
               LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_active);
mysqli_stmt_bind_param($stmt, 'iii', $user_id, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM photo 
                  WHERE ph_status = '1' 
                  AND ph_u_id = ?";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_bind_param($stmt_count, 'i', $user_id);
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
<script type="text/javascript" src="../js/jquery-1.2.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
<script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>

<div id="active_offer">
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
                                        <div class="pbl_liv">
                                            <b>
                                                <script type="text/javascript">
                                                    jQuery(function() {
                                                        jQuery('#file_upload').uploadifive({
                                                            'auto': true,
                                                            'formData': {'id': '<?php echo $user_id; ?>'},
                                                            'queueID': 'queue',
                                                            'debug': false, // تعطيل التصحيح في الإنتاج
                                                            'method': 'post',
                                                            'uploadScript': 'ajax-file/uploadGalleryImage.php',
                                                            'onUploadComplete': function(file, data) {
                                                                showGalleryImage(1);
                                                            }
                                                        });
                                                    });
                                                </script>
                                                <div id="drop" style="padding-left:10px;">
                                                    <input type="file" id="file_upload" name="file_upload" />
                                                </div>
                                                <div id="queue"></div>
                                            </b>
                                        </div>
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
                                                        echo '<a href="javascript:showGalleryImage(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
                                                    } elseif ($first_btn) {
                                                        echo '<img id="firstmail" src="images/first.gif">';
                                                    }
                                                    echo '&nbsp;';
                                                    
                                                    // زر الصفحة السابقة
                                                    if ($previous_btn && $cur_page > 1) {
                                                        $pre = $cur_page - 1;
                                                        echo '<a href="javascript:showGalleryImage(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                                                    } elseif ($previous_btn) {
                                                        echo '<img id="prevmail" src="images/prevmail.gif">';
                                                    }
                                                    echo '&nbsp;';
                                                    
                                                    // زر الصفحة التالية
                                                    if ($next_btn && $cur_page < $no_of_paginations) {
                                                        $nex = $cur_page + 1;
                                                        echo '<a href="javascript:showGalleryImage(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                                                    } elseif ($next_btn) {
                                                        echo '<img id="nextmail" src="images/nextmail.gif">';
                                                    }
                                                    echo '&nbsp;';
                                                    
                                                    // زر الصفحة الأخيرة
                                                    if ($last_btn && $cur_page < $no_of_paginations) {
                                                        echo '<a href="javascript:showGalleryImage(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lasten.gif"></a>';
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
                    <td class="pbl_top_m" height="24" width="115">Image</td>
                    <td class="pbl_top_m" height="24" width="162">Posting Date</td>
                    <td class="pbl_top_m" height="24" width="122">Choose Action</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="select_sp" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <?php if ($count > 0): ?>
                <?php while ($row_ph = mysqli_fetch_object($result)): 
                    $ph_id = (int)$row_ph->ph_id;
                    $file_name = htmlspecialchars($row_ph->ph_fileName ?? '', ENT_QUOTES, 'UTF-8');
                    $updated_date = !empty($row_ph->ph_updated_date) ? date("d M, Y", strtotime($row_ph->ph_updated_date)) : 'N/A';
                ?>
                <tr>
                    <td align="CENTER" valign="top" width="122">
                        <div style="border:1px solid #FFCACA; width:100px; line-height:100px; margin:10px auto;">
                            <?php if (!empty($file_name)): ?>
                                <?php 
                                $image_path = "upload/image_gallery/" . $file_name;
                                $full_path = __DIR__ . "/../" . $image_path;
                                if (file_exists($full_path) && is_file($full_path)):
                                ?>
                                <img src="<?php echo $image_path; ?>" id="6363630246_1" 
                                     style="margin-right:5px;" border="0" 
                                     height="74" hspace="0" vspace="0" width="100" 
                                     alt="<?php echo $file_name; ?>">
                                <?php endif; ?>
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                            <div id="6363630246_1_H" vspace="0" hspace="0" 
                                 style="display:none; position:absolute; top:0; left:0; width:0; height:0; background:#FFFFFF;" 
                                 height="90"></div>
                        </div>
                    </td>

                    <td class="mp5" valign="TOP" width="160"><?php echo $updated_date; ?><br></td>
                    <td class="mp5" valign="TOP" width="120">
                        <img src="images/rmv.gif" alt="Remove" align="ABSMIDDLE" 
                             height="10" hspace="5" vspace="5" width="9">
                        <a onclick="delGalleryImage(<?php echo $ph_id; ?>);" style="cursor:pointer;">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" align="center" 
                        style="vertical-align:middle; color:#F00; padding-top:10px; padding-bottom:10px;">
                        No Image
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