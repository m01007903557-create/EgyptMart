<?php
/**
 * File: my-blocklist.php
 * Version: PHP 8.3
 * Description: عرض وإدارة قائمة المستخدمين المحظورين
 */

include "common.php";

$_SESSION['last_page'] = "my-blocklist.php";

if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '')
{
    header("Location: sign-in.php");
    exit();
}

$uid = (int)$_SESSION['uid_indm'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<!-- meta start -->
<title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        
<!-- css start -->
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/my2.css" type="text/css" rel="stylesheet">
<link href="css/add.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->
<style>
.starRating .starActive{background-image:url(images/sth.gif);}
.starRating .starDactive{background:url(images/nst.gif);}
</style>
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function()
{
    showBlockList(1);
});

/**
 * عرض قائمة المحظورين
 * @param {number} page - رقم الصفحة
 */
function showBlockList(page)
{
    $.post("ajax-file/showBlockList.php", {page: page}, function(data) { 
        $('#res_list').html(data); 
    }).fail(function() {
        alert("حدث خطأ في تحميل القائمة");
    });
}

/**
 * عرض تفاصيل المستخدم المحظور
 * @param {number} uid - معرف المستخدم
 * @param {number} mid - معرف الرسالة
 * @param {number} pg - رقم الصفحة
 */
function detailContact(uid, mid, pg)
{
    $("#loading").css("display", "block");
    
    setTimeout(function () {
        $.post("ajax-file/blockUserDetails.php", {uid: uid, mid: mid, pg: pg}, function(data) {    
            $("#loading").css("display", "none");
            $("#res_list").css("display", "none");
            $("#res_detail").css("display", "block");
            $('#res_detail').html(data);
        }).fail(function() {
            $("#loading").css("display", "none");
            alert("حدث خطأ في تحميل التفاصيل");
        });
    }, 500);
}

/**
 * العودة إلى القائمة الرئيسية
 * @param {number} pg - رقم الصفحة
 */
function back_to_list(pg)
{
    $("#res_detail").css("display", "none");
    $('#res_list').css("display", "block");
    showBlockList(pg);
}

/**
 * إلغاء حظر مستخدم
 * @param {number} blockBy - معرف المستخدم الذي قام بالحظر
 * @param {number} blocked - معرف المستخدم المحظور
 */
function unBlockUser(blockBy, blocked)
{
    if (confirm("Are you sure to Unblock this user?"))
    {
        $.post("ajax-file/delBlockUser.php", {blockBy: blockBy, blocked: blocked}, function(data) {
            $("#res_detail").css("display", "none");
            $('#res_list').css("display", "block");
            showBlockList(1);
        }).fail(function() {
            alert("حدث خطأ في إلغاء الحظر");
        });
    }
}

/**
 * إظهار نموذج إضافة مجلد جديد
 */
function showfolders() {
    // يمكن إضافة دالة لعرض المجلدات هنا
}

/**
 * إنشاء مجلد جديد
 */
function newfol() {
    $('#m2_nf').toggle();
}

/**
 * إضافة مجلد جديد
 */
function addfolder() {
    var folderName = $('#m2_nfn').val();
    if (folderName.trim() == '') {
        alert("الرجاء إدخال اسم المجلد");
        return;
    }
    // يمكن إضافة كود AJAX لحفظ المجلد هنا
}
</script>
</head>
<body>

    <!--main div:start-->
    <div class="hm1 bbc" id="res-mob1">
<!-- Header start Here::-->
        <?php 
        if (file_exists("includes/header_new.php")) {
            include "includes/header_new.php";
        }
        ?>
        
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1"></div>
<!-- Header End Here::-->

    <?php 
    if (file_exists("includes/header_menu.php")) {
        include "includes/header_menu.php"; 
    }
    ?>
            
        <!--left navigation:start-->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="enqulid" class="nln1" style="margin: 0px; padding: 0px;">
                <li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;text-align:center; padding: 18px 5px 18px 5px;background-color: #FFFFFF;">مراسلاتى</h3></li>
                <li class="np"><a href="my-enquiries.php" class="me inbox bnr">الوارد</a></li>
                <li class="np"><a href="my-enquiries.php" class="me sent bnr">الصادر</a></li>
                <li style="border-bottom: medium none;">
                    <h3 style="height:18px;">
                        <a href="javascript:showfolders();" id="folimg" class="mf_h me bnr f1">حافظات</a>
                        <a href="javascript:newfol();" id="m2_w2nf" class=""></a>
                    </h3>
                </li>
            </ul>
            
            <span id="m2_nf" style="display:none;">
                <li style="border-bottom:0;">
                    <table border="0" cellpadding="0" cellspacing="3" width="100%">
                        <tbody>
                            <tr>
                                <td><input class="mu11" style="width: 128px;font-size:10px;" id="m2_nfn" name="m2_nfn" type="text" placeholder="اسم المجلد"></td>
                                <td width="45"><input value="Add" onclick="addfolder();" class="fadb me bnr" type="button"></td>
                                <td width="10"><input value="" onclick="newfol();" class="me ffc bnr" type="button"></td>
                            </tr>
                        </tbody>
                    </table>
                </li>
            </span>
            
            <span id="allfol" style="display:block;"></span>
            
            <ul id="m2_sep">&nbsp;</ul>
            
            <ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
                <li style="border-bottom: medium none;"><h3>بيانات عملاء</h3></li>
                <li class="np npnew"><a href="my-addressbook.php">»&nbsp;بيانات اتصال عملائى</a></li>
                <li class="np npnew"><a class="leftindi txtcol" href="my-blocklist.php">»&nbsp;قائمة المحظورين</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php">»&nbsp;بيانات مشتريين</a></li>
            
                <li style="border-bottom: medium none; margin-top: 40px;"><h2>: ربما ايضا تريد</h2></li>
                <li class="np npnew"><a href="buyleads.php">أحدث طلبات شراء</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php">إدارة طلبات شراء</a></li>
                <li class="np npnew"><a href="manage-buylead-alert.php">إدارة إشعارات طلبات شراء</a></li>
                <li class="np npnew"><a href="transaction_history.php">شحن رصيد شراء طلبات شراء</a></li>
            </ul>
        </div>
        <!--left navigation:ends-->
        
        <div id="loading" style="display:none; text-align:center; padding:20px;">
            <img src="images/loading.gif" alt="Loading..." border="0" width="32" height="32">
            <br>جاري التحميل...
        </div>
        
        <div id="res_list"></div>
        <div id="res_detail" style="display:none;"></div>
        
        <div class="c3">&nbsp;</div>
    </div>
    <!--footer:start-->
    <?php 
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    }
    ?>
</body>
</html>