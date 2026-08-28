<?php
/**
 * File: company-banner.php
 * Version: PHP 8.3
 * Description: إدارة صور بانر الشركة - رفع وعرض وحذف صور الشركة
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
    $_SESSION['last_page'] = "company-banner.php";
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

// الحصول على معرف خطة العضوية
$user_mp_id = (int)getUserInfo($uid, 'usr_mp_id');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>صور الشركة | <?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
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
            showBannerList(<?php echo (int)$row_bf->bnsprof_id; ?>, 1);
        <?php endif; ?>
    });
    
    /**
     * فتح منطقة رفع الصور
     */
    function openUploadArea() {
        var mpId = $('#mpId').val();
        
        if (mpId < 3) {
            alert("يجب الاشتراك في عضوية بريميوم لإضافة صور الشركة");
            window.location.href = "membership_plans.php";
        } else {
            $("#upload_area").show();
            $("#add_new_banner").hide();
            $("#can_upload").show();
        }
    }
    
    /**
     * إغلاق منطقة رفع الصور
     */
    function closeUploadArea() {
        $("#upload_area").hide();
        $("#add_new_banner").show();
        $("#can_upload").hide();
    }
    
    /**
     * حذف صورة بانر
     * @param {number} id - معرف الصورة
     * @param {number} cb_bnsprof_id - معرف ملف الشركة
     */
    function delBanner(id, cb_bnsprof_id) {
        if (confirm("هل أنت متأكد من حذف هذه الصورة؟")) {
            $.ajax({
                type: "POST",
                url: "ajax-file/delCompanyBanner.php",
                data: {cb_id: id},
                success: function(data) {
                    showBannerList(cb_bnsprof_id, 1);
                },
                error: function() {
                    alert('حدث خطأ في حذف الصورة.');
                }
            });
        }
    }
    
    /**
     * عرض قائمة صور البانر
     * @param {number} cb_bnsprof_id - معرف ملف الشركة
     * @param {number} page - رقم الصفحة
     */
    function showBannerList(cb_bnsprof_id, page) {
        $.ajax({
            type: "POST",
            url: "ajax-file/showCompanyBannerList.php",
            data: {cb_bnsprof_id: cb_bnsprof_id, page: page},
            beforeSend: function() {
                $("#banner_disp").html('<div style="text-align:center; padding:20px;"><img src="images/animated_loading.gif" alt="جاري التحميل..." /></div>');
            },
            success: function(data) {
                $("#banner_disp").html(data);
            },
            error: function() {
                $("#banner_disp").html('<div style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل قائمة الصور.</div>');
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
                <h1 class="f1" id="cpf_name">أضف صور للشركة من الداخل والخارج</h1>
            </div>
            <p id="pf_change" style="display:none; float:left; margin-top:0px;"></p>
            <p class="f2 mt11 cnt_1" id="prof_cnt"></p>
            <div class="c3"></div>
        </div>
        
        <div class="clb px"></div>
        
        <div class="" style="margin-top:4px;">
            <p class="aml"></p>
            <div id="re_link" class="utab">
                <span style="font-size: 12px; *float:left;" title="Add Company Images to your Company Profile">
                    أضف صور للشركة والمكاتب للنشر فى بروفايل الشركة
                </span>
                
                <input type="hidden" value="<?php echo $user_mp_id; ?>" id="mpId" />
                
                <a style="display: block;" class="f2 fw apr1" id="add_new_banner" onclick="openUploadArea();" title="Add Image">
                    أضف صور جديدة
                </a>
                
                <a style="display: none;" class="f2 fw apr1" id="can_upload" onclick="closeUploadArea();">إلغاء</a>
            </div>
            
            <div id="upload_area" class="utab" style="background-color:#FAF4FF; display:none; height:auto; padding:15px; margin-top:10px;">
                <input type="hidden" id="cv_bnsprof_id" name="cb_bnsprof_id" value="<?php echo isset($row_bf->bnsprof_id) ? (int)$row_bf->bnsprof_id : 0; ?>" />
                
                <span style="font-size: 12px; display:block; margin-bottom:10px;">
                    أبعاد الصورة المفضلة: 200px عرض × 200px ارتفاع
                </span>
                
                <b>
                    <!-- Uploadifive Scripts -->
                    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
                    <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
                    
                    <script type="text/javascript">
                    jQuery(function() {
                        jQuery('#banner_upload').uploadifive({
                            'auto': true,
                            'formData': {'id': '<?php echo isset($row_bf->bnsprof_id) ? (int)$row_bf->bnsprof_id : 0; ?>'},
                            'queueID': 'queue',
                            'debug': false,
                            'method': 'post',
                            'uploadScript': 'ajax-file/uploadCompanyBanner.php',
                            'onUploadComplete': function(file, data) {
                                <?php if (isset($row_bf->bnsprof_id) && $row_bf->bnsprof_id > 0): ?>
                                    showBannerList(<?php echo (int)$row_bf->bnsprof_id; ?>, 1);
                                <?php endif; ?>
                                // تفريغ حقل الملف بعد الرفع
                                jQuery('#banner_upload').val('');
                            },
                            'onError': function(errorType, error) {
                                alert('حدث خطأ في رفع الملف: ' + error);
                            }
                        });
                    });
                    </script>
                    
                    <div id="drop" style="padding-left:10px; margin-bottom:10px;">
                        <input type="file" id="banner_upload" name="file_upload" />
                    </div>
                    
                    <div id="queue" style="margin-top:10px;"></div>
                </b>
                
                <div style="text-align:left; margin-top:10px;">
                    <span style="font-size:11px; color:#666;">يتم رفع الصور تلقائياً بعد الاختيار</span>
                </div>
            </div>
            
            <div class="c3"></div>
            <div class="c3"></div>
            
            <div id="banner_disp" style="text-align:center; padding-top:10px;"></div>
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