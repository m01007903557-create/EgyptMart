<?php
/**
 * File: manage-purchased-auctions.php
 * Version: PHP 8.3
 * Description: عرض وإدارة المزادات المشتراه
 */

include "common.php";

$_SESSION['last_page'] = "manage-purchased-auctions.php";

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

<!-- meta start -->
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

<!-- ColorBox -->
<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">

<script>
    $(document).ready(function() {
        // استخدام on بدلاً من live (live مهمل في الإصدارات الحديثة من jQuery)
        $(document).on('click', '.ajax', function() {
            $.colorbox({
                href: $(this).attr('href'), 
                open: true, 
                width: "800px", 
                height: "500px"
            });
            return false;
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function()
    {
        showPurAuctions(1);
    });
    
    /**
     * عرض المزادات المشتراه (التبويب النشط)
     * @param {number} page - رقم الصفحة
     */
    function showPurAuctions(page)
    {
        $("#opnReq_tab").removeClass("npo").addClass("ap2 tabTextColor");
        $("#clsReq_tab").removeClass("ap2 tabTextColor").addClass("npo");
        purchasedAuctions(page);
    }
    
    /**
     * عرض الطلبات المغلقة (التبويب المغلق)
     * @param {number} page - رقم الصفحة
     */
    function showClosedReq(page)
    {
        $("#clsReq_tab").removeClass("npo").addClass("ap2 tabTextColor");
        $("#opnReq_tab").removeClass("ap2 tabTextColor").addClass("npo");
        closedRequirement(page);
    }
    
    /**
     * جلب المزادات المشتراه
     * @param {number} page - رقم الصفحة
     */
    function purchasedAuctions(page)
    {
        $('#res').html('<div align="center" style="margin-top:50px;margin-bottom:50px;"><img src="images/loading-hor-red.gif" alt="جاري التحميل..." /></div>');
        $.post("ajax-file/purchased-auctions.php", {page: page}, function(data) { 
            $('#res').html(data); 
        }).fail(function() {
            $('#res').html('<div class="error-message" style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل المزادات المشتراه</div>');
        });
    }
    
    /**
     * جلب الطلبات المغلقة
     * @param {number} page - رقم الصفحة
     */
    function closedRequirement(page)
    {
        $('#res').html('<div align="center" style="margin-top:50px;margin-bottom:50px;"><img src="images/loading-hor-red.gif" alt="جاري التحميل..." /></div>');
        $.post("ajax-file/closedRequirement.php", {page: page}, function(data) { 
            $('#res').html(data); 
        }).fail(function() {
            $('#res').html('<div class="error-message" style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل الطلبات المغلقة</div>');
        });
    }
    
    /**
     * عرض تفاصيل المزاد المشترى
     * @param {number} id - معرف المزاد
     */
    function detailPurAuctions(id)
    {
        $('#res').html('<div align="center" style="margin-top:50px;margin-bottom:50px;"><img src="images/loading-hor-red.gif" alt="جاري التحميل..." /></div>');
        $.post("ajax-file/detailPurAuctions.php", {id: id}, function(data) { 
            $('#detail_req').html(data);
            $('#req_listing').css("display", "none");
            $('#detail_req').css("display", "block");
        }).fail(function() {
            $('#res').html('<div class="error-message" style="color:red; text-align:center; padding:20px;">حدث خطأ في تحميل تفاصيل المزاد</div>');
        });
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
     * حذف مزاد مشترى
     * @param {number} id - معرف المزاد
     * @param {string} v - نوع العرض (op - مفتوحة / أخرى - مغلقة)
     */
    function delAuction(id, v)
    {
        if (confirm("Are you sure to delete this?"))
        {
            $.post("ajax-file/delAuction.php", {id: id}, function(data) {
                if (v == 'op')
                {
                    purchasedAuctions(1);
                }
                else
                {
                    closedRequirement(1);
                }
            }).fail(function() {
                alert("حدث خطأ في حذف المزاد");
            });
        }
    }
</script>

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

<!--main div:start-->
<div class="hm1 bbc" id="res-mob1">
    <!-- Header start Here::-->
    <?php 
    if (file_exists("includes/header_new.php")) {
        include "includes/header_new.php"; 
    }
    ?>
    
    <br><br>
    <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1"></div>
    <!-- Header End Here::-->

    <?php 
    if (file_exists("includes/header_menu.php")) {
        include "includes/header_menu.php"; 
    }
    ?>
    
    <!--myzone drop elements:ends--> 
    
    <!--left navigation:start-->
    <div class="f1 w61n tb lh ml br" id="lnav">
        <ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
            <li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Auction Tools</h3></li>
            <li class="np npnew"><a href="post-auction.php">»&nbsp;Post an Auction</a></li>
            <li class="np npnew"><a href="manage-auctions.php">»&nbsp;Manage Auctions</a></li>
            <li class="np npnew"><a href="manage-auction-alert.php">»&nbsp;Manage Auction Alerts</a></li>
            <li style="border-bottom:none"><h3>Auction Purchases</h3></li>
            <li class="np npnew"><a href="subscription.php">Purchase Credits</a></li>
            <li class="np npnew"><a href="auctions.php">View Latest Auctions</a></li>
            <li class="np npnew"><a class="leftindi txtcol" href="manage-purchased-auctions.php">View Purchased Auctions</a></li>
            <li class="np npnew"><a href="transaction_history.php">Transaction History</a></li>
            <li class="np npnew"><a href="manage-tender-alert.php">Manage Tender Alerts</a></li>
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
                            <table align="CENTER" border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td valign="TOP" width="100%">
                                        
                                            <?php
                                            // الحصول على معلومات المستخدم
                                            $sql = "SELECT * FROM user WHERE usr_id = " . $uid;
                                            $res = mysqli_query($db, $sql);
                                            $row = $res ? mysqli_fetch_object($res) : null;
                                            ?>
                                            
                                            <?php if($row && $row->usr_mp_id > 3): ?>
                                                <div class="mfr mf14 md1 mc3"> 
                                                    <span class="mf12">[ 
                                                        <a href="membership_plans.php">Pay Annual Subscription</a>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <div class="mfr mf14 md1 mc3">
                                                    Available Credits: <font class="mc6"> <span id="current_balance">0</span> Credits</font>
                                                    <span class="mf12">[ 
                                                        <a href="subscription.php">Purchase More Credits</a>
                                                    ]</span>
                                                    <div class="mta mpb8 mpt5 mf11">
                                                        <a href="transaction_history.php">View Your Transaction History</a>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="wd mf18 mc5 mta2 mpb10">Purchased Auctions</div>
                                            
                                            <div id="masterdiv">
                                                <div id="sub1" style="display:inline;">
                                                    <div class="ap1">
                                                        <?php
                                                        // حساب عدد المزادات المشتراه
                                                        $sql_pur = "SELECT count(*) AS cnt 
                                                                   FROM purchased_auction, auction 
                                                                   WHERE pauc_auc_id = auc_id 
                                                                   AND pauc_usr_id = " . $uid . "
                                                                   AND pauc_status = '1'";
                                                        $res_pur = mysqli_query($db, $sql_pur);
                                                        $row_pur = $res_pur ? mysqli_fetch_object($res_pur) : null;
                                                        $pur_count = $row_pur ? $row_pur->cnt : 0;
                                                        ?>
                                                        
                                                        <a class="f1 ap2 tabTextColor" onclick="showPurAuctions(1);" style="cursor:pointer;" id="opnReq_tab">
                                                            Purchased Auctions (<?php echo (int)$pur_count; ?>)
                                                        </a>
                                                        <div class="c3"></div>
                                                    </div>

                                                    <div id="res"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td valign="TOP"><img src="images/zero.gif" height="1" width="10" alt=""></td>
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