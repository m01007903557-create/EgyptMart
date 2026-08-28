<?php
/**
 * File: admin/ajax-file/cus_support.php
 * Version: PHP 8.3
 * Description: تحميل وعرض الأسئلة الشائعة حسب التصنيف مع نافذة منبثقة للعرض
 * 
 * هذا الملف يستقبل طلب AJAX لعرض قائمة الأسئلة الشائعة التابعة لتصنيف محدد
 * مع إمكانية عرض المحتوى الكامل في نافذة منبثقة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// تعيين نوع المحتوى إلى HTML
header('Content-Type: text/html; charset=UTF-8');

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    echo '<div class="alert alert-danger">خطأ في الاتصال بقاعدة البيانات</div>';
    exit();
}

// التحقق من وجود معرف التصنيف
if (!isset($_GET['hid']) || empty($_GET['hid']) || $_GET['hid'] == 'undefined') {
    echo '<div class="alert alert-warning">معرف التصنيف مطلوب</div>';
    exit();
}

// تنظيف المدخلات
$hid = (int)$_GET['hid'];

if ($hid <= 0) {
    echo '<div class="alert alert-warning">معرف التصنيف غير صالح</div>';
    exit();
}

// جلب الأسئلة الشائعة للتصنيف المحدد
$recObjsql = "SELECT * FROM custom_faq WHERE cf_fc_id = " . $hid . " ORDER BY cf_heading";
$recObj = mysqli_query($con, $recObjsql);

if (!$recObj) {
    error_log("خطأ في جلب الأسئلة: " . mysqli_error($con));
    echo '<div class="alert alert-danger">خطأ في جلب البيانات</div>';
    exit();
}
?>

<div class="table-responsive">
    <table id="sample-table-2" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th><strong>Heading</strong></th>
                <th><strong>Content</strong></th>
                <th><strong>Action</strong></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $count = mysqli_num_rows($recObj);
            if ($count > 0):
                while ($row = mysqli_fetch_array($recObj)):
                    $content = substr(stripslashes($row['cf_content'] ?? ''), 0, 150);
                    $content_id = (int)$row['cf_id'];
            ?>
                    <tr>
                        <td style="text-align:center;"><?php echo htmlspecialchars(ucwords($row['cf_heading'] ?? '')); ?></td>
                        <td style="text-align:center;">
                            <?php echo htmlspecialchars($content); ?>&nbsp;... 
                            <a id="id-btn-job<?php echo $content_id; ?>" style="cursor:pointer; color:#0066cc; text-decoration:underline;">more..</a>
                        </td>
                        <td style="text-align:center">
                            <a href="support_change.php?pid=<?php echo $content_id; ?>&mode=edit" title="Edit">
                                <img alt="edit" src="images/edit.jpg">
                            </a>
                            <a onClick="DelPageList(<?php echo $content_id; ?>)" style="cursor:pointer;">
                                <img alt="delete" src="images/delete.jpg" border="0">
                            </a>
                        </td>
                    </tr>
                    
                    <!-- نافذة عرض المحتوى الكامل -->
                    <div id="job_form<?php echo $content_id; ?>" class="backLayer" style="left: 15%; top: 5%; display: none;">
                        <h3>Content</h3>
                        <div class="">
                            <?php echo nl2br(htmlspecialchars(stripslashes($row['cf_content'] ?? ''))); ?>
                        </div>
                        <div class="space-6"></div>
                        <div style="float: right; padding-bottom:10px">
                            <button class="btn btn-xs btn-default" type="button" id="clse_job<?php echo $content_id; ?>">Close</button>
                        </div>
                    </div>
                    
                    <script type="text/javascript">
                    $(document).ready(function() {
                        // فتح النافذة
                        $("#id-btn-job<?php echo $content_id; ?>").click(function() {
                            $("#job_form<?php echo $content_id; ?>").fadeIn(1000);
                            $(".background_overlay").fadeIn(500);
                            positionCookiePopup(<?php echo $content_id; ?>);
                        });
                        
                        // إغلاق النافذة
                        $("#clse_job<?php echo $content_id; ?>, .background_overlay").click(function() {
                            $("#job_form<?php echo $content_id; ?>").fadeOut(500);
                            $(".background_overlay").fadeOut(500);
                        });
                    });
                    
                    function positionCookiePopup(id) {
                        if (!$("#job_form" + id).is(':visible')) {
                            return;
                        }
                        $("#job_form" + id).css({
                            left: ($(window).width() - $('#job_form' + id).width()) / 2,
                            top: ($(window).height() - $('#job_form' + id).height()) / 5,
                            position: 'fixed'
                        });
                    }
                    
                    $(window).bind('resize', function() {
                        <?php
                        // إعادة ضبط الموضع لجميع النوافذ المفتوحة
                        mysqli_data_seek($recObj, 0);
                        while ($row = mysqli_fetch_array($recObj)):
                            $content_id = (int)$row['cf_id'];
                        ?>
                        if ($("#job_form<?php echo $content_id; ?>").is(':visible')) {
                            positionCookiePopup(<?php echo $content_id; ?>);
                        }
                        <?php endwhile; ?>
                    });
                    </script>
                    
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="3" align="center" style="padding: 20px; color: #666;">
                        لا توجد أسئلة شائعة في هذا التصنيف
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- طبقة التعتيم الخلفية -->
<div class="background_overlay" style="display: none;"></div>

<style>
.backLayer {
    position: fixed;
    border: 5px solid lightblue;
    padding: 0px 20px;
    background: white;
    width: 70%;
    height: auto;
    max-height: 80%;
    z-index: 9999999999999;
    overflow: auto;
    overflow-x: hidden;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
}

.backLayer h3 {
    color: #333;
    border-bottom: 2px solid lightblue;
    padding-bottom: 10px;
}

.background_overlay {
    position: fixed;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    z-index: 99999999;
    background: black;
    opacity: 0.4;
}

.btn-xs {
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 3px;
    cursor: pointer;
}

.btn-default {
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    color: #333;
}

.btn-default:hover {
    background-color: #e2e6ea;
}

.table-hover tbody tr:hover {
    background-color: #f5f5f5;
}
</style>

<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>