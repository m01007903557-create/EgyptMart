<?php
/**
 * File: company-video.php
 * Version: PHP 8.3
 * Description: إدارة فيديوهات الشركة - إضافة وعرض وحذف فيديوهات يوتيوب
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include 'common.php';

// التحقق من وجود مستخدم مسجل دخوله
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    $_SESSION['last_page'] = "company-video.php";
    header("Location: sign-in.php");
    exit();
}

// تنظيف معرف المستخدم
$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// جلب بيانات ملف الشركة
$sql_bf = "SELECT * FROM business_profile WHERE bnsprof_uid = {$uid} LIMIT 1";
$res_bf = mysqli_query($con, $sql_bf);

if (!$res_bf) {
    die('خطأ في جلب بيانات الشركة: ' . mysqli_error($con));
}

$row_bf = mysqli_fetch_object($res_bf);

// إذا لم يكن هناك ملف شركة، إنشاء واحد
if (!$row_bf) {
    // إنشاء سجل جديد في business_profile
    $insert_sql = "INSERT INTO business_profile (bnsprof_uid, bnsprof_creation_date) VALUES ({$uid}, NOW())";
    mysqli_query($con, $insert_sql);
    
    // إعادة جلب البيانات
    $res_bf = mysqli_query($con, $sql_bf);
    $row_bf = mysqli_fetch_object($res_bf);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>فيديو الشركة | <?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/about-us.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">

    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    
    <script>
    $(document).ready(function() {
        <?php if (isset($row_bf->bnsprof_id) && $row_bf->bnsprof_id > 0): ?>
            showVideoList(<?php echo (int)$row_bf->bnsprof_id; ?>, 1);
        <?php endif; ?>
    });
    
    /**
     * فتح منطقة رفع الفيديو
     */
    function openUploadArea() {
        $("#upload_area").show();
        $("#add_new_video").hide();
    }
    
    /**
     * إغلاق منطقة رفع الفيديو
     */
    function closeUploadArea() {
        $("#upload_area").hide();
        $("#add_new_video").show();
        $("#cv_video_link").val('');
    }
    
    /**
     * حفظ الفيديو
     */
    function saveVideo() {
        var cv_bnsprof_id = $("#cv_bnsprof_id").val();
        var vlink = $("#cv_video_link").val().trim();
        
        if (vlink == '') {
            alert('الرجاء إدخال رابط فيديو يوتيوب صحيح.');
            return;
        }
        
        // التحقق من أن الرابط من يوتيوب
        if (vlink.indexOf('youtube.com') == -1 && vlink.indexOf('youtu.be') == -1) {
            if (!confirm('الرابط ليس من يوتيوب. هل تريد المتابعة؟')) {
                return;
            }
        }
        
        $.ajax({
            type: "POST",
            url: "ajax-file/addCompanyVideo.php",
            data: {cv_bnsprof_id: cv_bnsprof_id, vlink: vlink},
            beforeSend: function() {
                $("#video_disp").html('<div style="text-align:center; padding:20px;"><img src="images/animated_loading.gif" alt="جاري التحميل..." /></div>');
            },
            success: function(data) {
                $("#cv_video_link").val('');
                closeUploadArea();
                alert('تم إضافة الفيديو بنجاح.');
                showVideoList(cv_bnsprof_id, 1);
            },
            error: function() {
                alert('حدث خطأ في إضافة الفيديو. الرجاء المحاولة مرة أخرى.');
            }
        });
    }
    
    /**
     * حذف فيديو
     * @param {number} id - معرف الفيديو
     * @param {number} cv_bnsprof_id - معرف ملف الشركة
     */
    function delVideo(id, cv_bnsprof_id) {
        if (confirm("هل أنت متأكد من حذف هذا الفيديو؟")) {
            $.ajax({
                type: "POST",
                url: "ajax-file/delCompanyVideo.php",
                data: {cv_id: id},
                success: function(data) {
                    showVideoList(cv_bnsprof_id, 1);
                },
                error: function() {
                    alert('حدث خطأ في حذف الفيديو.');
                }
            });
        }
    }
    
    /**
     * عرض قائمة الفيديوهات
     * @param {number} cv_bnsprof_id - معرف ملف الشركة
     * @param {number} page - رقم الصفحة
     */
    function showVideoList(cv_bnsprof_id, page) {
        $.ajax({
            type: "POST",
            url: "ajax-file/showCompanyVideoList.php",
            data: {cv_bnsprof_id: cv_bnsprof_id, page: page},
            beforeSend: function() {
                $("#video_disp").html('<div style="text-align:center; padding:20px;"><img src="images/animated_loading.gif" alt="جاري التحميل..." /></div>');
            },
            success: function(data) {
                $("#video_disp").html(data);
            },
            error: function() {
                $("#video_disp").html('<div style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل قائمة الفيديوهات.</div>');
            }
        });
    }
    </script>
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
    <?php 
    if (file_exists("includes/header_new.php")) {
        include "includes/header_new.php"; 
    }
    ?>
    
    <div class="bt">
        <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" width="1" height="1">
    </div>

    <?php 
    if (file_exists('includes/header_menu.php')) {
        include 'includes/header_menu.php';
    }
    ?>
    
    <!--left navigation:start-->
    <?php 
    if (file_exists('includes/left_menu.php')) {
        include 'includes/left_menu.php';
    }
    ?>
    <!--left navigation:ends-->
    
    <div class="w56 f1 p2b p14 blr" style="width:80%; height:100%;">
        <div></div>
        <div class="c3"></div>
        
        <div>
            <div id="chg_name" class="f1 chng_a">
                <h1 class="f1" id="cpf_name" title="Company Video">حمل - فيديو الشركة - والمنتجات</h1>
            </div>
            <p id="pf_change" style="display:none; float:left; margin-top:0px;"></p>
            <p class="f2 mt11 cnt_1" id="prof_cnt"></p>
            <div class="c3"></div>
        </div>
        
        <div class="clb px"></div>
        
        <div class="" style="margin-top:4px;">
            <p class="aml"></p>
            <div id="re_link" class="utab">
                <span style="font-size: 15px; *float:left;" title="Add Video to your Company Profile">
                    حمل فيديو الشركة والمنتجات لأفضل عرض لأعمالك التجارية
                </span>
                
                <a style="display: block;" class="f2 fw apr1" id="add_new_video" onclick="openUploadArea();" title="Add New Video">
                    أضف فيديو جديد
                </a>
            </div>
            
            <div id="upload_area" class="utab" style="background-color:#F5ECFF; display:none; height:auto; padding:15px; margin-top:10px;">
                <input type="hidden" id="cv_bnsprof_id" name="cv_bnsprof_id" value="<?php echo isset($row_bf->bnsprof_id) ? (int)$row_bf->bnsprof_id : 0; ?>" />
                
                <span style="font-size: 12px; display:block; margin-bottom:10px;">
                    رابط فيديو يوتيوب المضمن (الحد الأقصى 640x360):
                </span>
                
                <textarea id="cv_video_link" name="cv_video_link" style="width:100%; height:80px; padding:5px; margin-bottom:10px;" placeholder="أدخل رابط فيديو يوتيوب هنا..."></textarea>
                
                <div style="text-align:left; margin-top:10px;">
                    <a style="display: inline-block; margin-right:10px; padding:5px 15px; background-color:#ccc; color:#333; text-decoration:none; border-radius:3px; cursor:pointer;" onclick="closeUploadArea();">إلغاء</a>
                    <a style="display: inline-block; padding:5px 15px; background-color:#4CAF50; color:#fff; text-decoration:none; border-radius:3px; cursor:pointer;" onclick="saveVideo();">إضافة فيديو</a>
                </div>
            </div>
            
            <div class="c3"></div>
            <div class="c3"></div>
            
            <div id="video_disp" style="text-align:center; padding-top:10px;"></div>
        </div>
    </div>
    
    <div class="c3">&nbsp;</div>
</div>

<!--footer:start-->
<?php 
if (file_exists('includes/footer.php')) {
    include 'includes/footer.php';
}
?>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>
</body>
</html>