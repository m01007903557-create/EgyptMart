<?php
/**
 * File: manage-auctions.php
 * Version: PHP 8.3
 * Description: عرض وإدارة المزايدات (Auctions)
 */

include "common.php";

$_SESSION['last_page'] = "manage-auctions.php";

if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '')
{
    header("Location: sign-in.php");
    exit();
}

$uid = (int)$_SESSION['uid_indm'];

// الحصول على اتصال قاعدة البيانات
global $db;
if (!isset($db)) {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- css start -->
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/eto-buyreq.css" type="text/css" rel="stylesheet">

<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->

<script type="text/javascript">
    $(document).ready(function()
    {
        showAuctions(1);
    });

    /**
     * عرض قائمة المزايدات
     * @param {number} page - رقم الصفحة
     */
    function showAuctions(page)
    {
        $('#res').html('<div align="center" style="margin-top:50px;margin-bottom:50px;"><img src="images/loading-hor-red.gif" alt="جاري التحميل..." /></div>');
        $.post("ajax-file/showAuctions.php", {page: page}, function(data) { 
            $('#res').html(data); 
        }).fail(function() {
            $('#res').html('<div class="error-message" style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل المزايدات</div>');
        });
    }

    /**
     * عرض المزايدات المغلقة
     * @param {number} page - رقم الصفحة
     */
    function closedRequirement(page)
    {
        $('#res').html('<div align="center" style="margin-top:50px;margin-bottom:50px;"><img src="images/loading-hor-red.gif" alt="جاري التحميل..." /></div>');
        $.post("ajax-file/closedRequirement.php", {page: page}, function(data) { 
            $('#res').html(data); 
        }).fail(function() {
            $('#res').html('<div class="error-message" style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل المزايدات المغلقة</div>');
        });
    }

    /**
     * عرض تفاصيل المزايدة
     * @param {number} id - معرف المزايدة
     */
    function detailAuction(id)
    {
        $('#res').html('<div align="center" style="margin-top:50px;margin-bottom:50px;"><img src="images/loading-hor-red.gif" alt="جاري التحميل..." /></div>');
        $.post("ajax-file/detailAuction.php", {id: id}, function(data) { 
            $('#detail_req').html(data);
            $('#req_listing').css("display", "none");
            $('#detail_req').css("display", "block");
        }).fail(function() {
            $('#res').html('<div class="error-message" style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل تفاصيل المزايدة</div>');
        });
    }

    /**
     * تعديل المزايدة
     * @param {number} id - معرف المزايدة
     */
    function editAuction(id)
    {
        $.post("ajax-file/editAuction.php", {id: id}, function(data) { 
            $('#detail_req').html(data);
        }).fail(function() {
            alert("حدث خطأ في تحميل بيانات التعديل");
        });
    }

    /**
     * حذف المزايدة
     * @param {number} id - معرف المزايدة
     */
    function delAuction(id)
    {
        if (confirm("Are you sure to delete this Auction?"))
        {
            $.post("ajax-file/delAuction.php", {id: id}, function(data) {
                showAuctions(1);
            }).fail(function() {
                alert("حدث خطأ في حذف المزايدة");
            });
        }
    }

    /**
     * إغلاق المزايدة
     * @param {number} id - معرف المزايدة
     * @param {string} v - نوع العرض (op - مفتوحة / أخرى - مغلقة)
     */
    function closeRequirement(id, v)
    {
        if (confirm("Are you sure to close this requirement?"))
        {
            $.post("ajax-file/closeRequirement.php", {id: id}, function(data) {
                if (v == 'op')
                {
                    openRequirement(1);
                }
                else
                {
                    closedRequirement(1);
                }
            }).fail(function() {
                alert("حدث خطأ في إغلاق المزايدة");
            });
        }
    }

    /**
     * إعادة نشر المزايدة
     * @param {number} id - معرف المزايدة
     * @param {string} v - نوع العرض (op - مفتوحة / أخرى - مغلقة)
     */
    function repostRequirement(id, v)
    {
        if (confirm("Are you sure to Repost this requirement?"))
        {
            $.post("ajax-file/repostRequirement.php", {id: id}, function(data) {
                if (v == 'op')
                {
                    openRequirement(1);
                }
                else
                {
                    closedRequirement(1);
                }
            }).fail(function() {
                alert("حدث خطأ في إعادة نشر المزايدة");
            });
        }
    }

    /**
     * العودة إلى القائمة الرئيسية
     */
    function goback()
    {
        $('#detail_req').css("display", "none");
        $('#req_listing').css("display", "block");
    }

    /**
     * عرض المزايدات المفتوحة
     * @param {number} page - رقم الصفحة
     */
    function openRequirement(page)
    {
        showAuctions(page);
    }
</script>

<!-- DatePicker Scripts -->
<script type="text/javascript" src="datepicker/date.js"></script>
<script type="text/javascript" src="datepicker/jquery.datePicker.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="datepicker/datePicker.css">
<link rel="stylesheet" type="text/css" media="screen" href="datepicker/demo.css">

<style id="poshytip-css-tip-yellowsimple" type="text/css">
    div.tip-yellowsimple{visibility:hidden;position:absolute;top:0;left:0;}
    div.tip-yellowsimple table, div.tip-yellowsimple td{margin:0;font-family:inherit;font-size:inherit;font-weight:inherit;font-style:inherit;font-variant:inherit;}
    div.tip-yellowsimple td.tip-bg-image span{display:block;font:1px/1px sans-serif;height:10px;width:10px;overflow:hidden;}
    div.tip-yellowsimple td.tip-right{background-position:100% 0;}
    div.tip-yellowsimple td.tip-bottom{background-position:100% 100%;}
    div.tip-yellowsimple td.tip-left{background-position:0 100%;}
    div.tip-yellowsimple div.tip-inner{background-position:-10px -10px;}
    div.tip-yellowsimple div.tip-arrow{visibility:hidden;position:absolute;overflow:hidden;font:1px/1px sans-serif;}
</style>

</head>
<body>
<div id="imgtrailer" style="position:absolute; z-index:4;visibility:hidden;">
    <img src="images/loading.gif" height="32" width="32" alt="Loading...">
</div>

<!-- Validate logged in user code ends HERE-->

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

    <!-- autosuggest ends -->

    <!--iil header:ends-->

    <?php 
    if (file_exists("includes/header_menu.php")) {
        include "includes/header_menu.php"; 
    }
    ?>

    <!--left navigation:start-->
    <div class="f1 w61n tb lh ml br" id="lnav">
        <ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
            <li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Auction Tools</h3></li>
            <li class="np npnew"><a href="post-auction.php">»&nbsp;أنشر مزايدة جديدة</a></li>
            <li class="np npnew"><a class="leftindi txtcol" href="manage-auctions.php">»&nbsp;إدارة المزايدات</a></li>
            <li class="np npnew"><a href="manage-auction-alert.php">»&nbsp;إدارة إشهارات المزايدات</a></li>
            <li style="border-bottom: medium none; margin-top: 40px;"><h2>ربما تريد أيضا</h2></li>
            <li class="np npnew"><a href="auctions.php">آخر المزايدات</a></li>
            <li class="np npnew"><a href="manage-purchased-auctions.php">بيانات المزايدات المشتراة</a></li>
        </ul>
    </div>
    <!--left navigation:ends-->
    
    <div class="mctr_buyreq mfl" id="req_listing">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td valign="TOP" width="100%">
                        <style type="text/css">
                            .sub{display: none;}
                            .tbq{display: none;}
                            .tabTextColor{color:#fff;}
                        </style>

                        <form style="margin:0px;" action="" name="form1">
                            <table align="CENTER" border="0" cellpadding="0" cellspacing="0" style="min-width:100%">
                                <tbody>
                                    <tr>
                                        <td valign="TOP" width="100%">
                                            <div class="wd1 mf18 mc5 mta2 mpb10">
                                                <div class="mf11 bc mbl mbn"></div>
                                                إدارة المزايدات
                                            </div>
                                            
                                            <div id="masterdiv">
                                                <div id="sub1" style="display:inline;">
                                                    <div class="ap1">
                                                        <?php
                                                        // ملاحظة: هذه الاستعلامات تستخدم جدول buy_requirement 
                                                        // قد تحتاج إلى تعديلها لاستخدام جدول auctions المناسب
                                                        $sql_opn = "SELECT count(*) AS cnt FROM buy_requirement, measurement_unit 
                                                                   WHERE br_estimate_qty_unit = mu_id 
                                                                   AND br_approval_status != '2' 
                                                                   AND br_display_status = '1' 
                                                                   AND br_status = '1' 
                                                                   AND br_u_id = " . $uid;
                                                        $res_opn = mysqli_query($db, $sql_opn);
                                                        $row_opn = $res_opn ? mysqli_fetch_object($res_opn) : null;
                                                        
                                                        $sql_cls = "SELECT count(*) AS cnt FROM buy_requirement, measurement_unit 
                                                                   WHERE br_estimate_qty_unit = mu_id 
                                                                   AND br_approval_status != '2' 
                                                                   AND br_display_status = '0' 
                                                                   AND br_status = '1' 
                                                                   AND br_u_id = " . $uid;
                                                        $res_cls = mysqli_query($db, $sql_cls);
                                                        $row_cls = $res_cls ? mysqli_fetch_object($res_cls) : null;
                                                        ?>
                                                        
                                                        <div id="post-new" style="float: right; margin:5px 5px" class="postNewReq">
                                                            <a href="post-auction.php" class="bo_m2 active">أنشر مزايدة جديدة</a>
                                                        </div>
                                                        <div class="c3"></div>
                                                    </div>

                                                    <div id="res"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                        <div style="clear:both"><br></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="detail_req" style="display:none;"></div>
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