<?php
/**
 * File: manage-sell-offer.php
 * Description: إدارة عروض البيع - عرض العروض النشطة والمعلقة والمنتهية مع خيارات التعديل والحذف
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "manage-sell-offer.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

global $con;

// حساب عدد العروض النشطة
$sql_active_cnt = "SELECT COUNT(*) as cnt 
                   FROM sale_offer 
                   WHERE so_usr_id = ? 
                   AND so_approval_status = '1' 
                   AND so_status = '1' 
                   AND DATE_ADD(so_approval_date, INTERVAL so_validity DAY) >= NOW()";

$stmt_active = mysqli_prepare($con, $sql_active_cnt);
mysqli_stmt_bind_param($stmt_active, 'i', $uid);
mysqli_stmt_execute($stmt_active);
$result_active = mysqli_stmt_get_result($stmt_active);
$row_active = mysqli_fetch_assoc($result_active);
$active_count = (int)($row_active['cnt'] ?? 0);
mysqli_stmt_close($stmt_active);

// حساب عدد العروض المعلقة (تحت الموافقة)
$sql_pending_cnt = "SELECT COUNT(*) as cnt 
                    FROM sale_offer 
                    WHERE so_usr_id = ? 
                    AND so_approval_status = '0' 
                    AND so_status = '1'";

$stmt_pending = mysqli_prepare($con, $sql_pending_cnt);
mysqli_stmt_bind_param($stmt_pending, 'i', $uid);
mysqli_stmt_execute($stmt_pending);
$result_pending = mysqli_stmt_get_result($stmt_pending);
$row_pending = mysqli_fetch_assoc($result_pending);
$pending_count = (int)($row_pending['cnt'] ?? 0);
mysqli_stmt_close($stmt_pending);

// حساب عدد العروض المنتهية
$sql_expired_cnt = "SELECT COUNT(*) as cnt 
                    FROM sale_offer 
                    WHERE so_usr_id = ? 
                    AND so_approval_status = '1' 
                    AND so_status = '1' 
                    AND DATE_ADD(so_approval_date, INTERVAL so_validity DAY) < NOW()";

$stmt_expired = mysqli_prepare($con, $sql_expired_cnt);
mysqli_stmt_bind_param($stmt_expired, 'i', $uid);
mysqli_stmt_execute($stmt_expired);
$result_expired = mysqli_stmt_get_result($stmt_expired);
$row_expired = mysqli_fetch_assoc($result_expired);
$expired_count = (int)($row_expired['cnt'] ?? 0);
mysqli_stmt_close($stmt_expired);
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- CSS -->
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/dir-new.css" type="text/css" rel="stylesheet">
    
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /><![endif]-->
    <!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
    
    <style>
        @media screen and (max-width: 1400px) and (min-width: 990px) {
            .n-hdrn li {
                padding: 10px !important;
                font-size: 13px !important;
            }
        }
        .sub { display: none; }
        .tbq { display: none; }
        .active_tab { background-color: #FF8080; }
        .tab { background-color: #ACACAC; }
        #updSO {
            border: 1px solid #6F0000;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            padding: 5px;
            text-align: center;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            background-color: #DF0000;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#DF0000');
            background: -webkit-gradient(linear, left top, left bottom, from(#DF0000), to(#DF0000));
            background: -moz-linear-gradient(top, #DF0000, #DF0000);
            cursor: pointer;
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    
    <script>
    function showActOffer() {
        $("#active_offer_tab").removeClass("tab").addClass("active_tab");
        $("#approval_pending_tab").addClass("tab").removeClass("active_tab");
        $("#expired_offer_tab").addClass("tab").removeClass("active_tab");
        
        $("#active_offer").css("display", "block");
        $("#pending_approval").css("display", "none");
        $("#expired_offer").css("display", "none");
        
        showActive(1);
    }
    
    function showApprPending() {
        $("#approval_pending_tab").removeClass("tab").addClass("active_tab");
        $("#active_offer_tab").addClass("tab").removeClass("active_tab");
        $("#expired_offer_tab").addClass("tab").removeClass("active_tab");
        
        $("#active_offer").css("display", "none");
        $("#pending_approval").css("display", "block");
        $("#expired_offer").css("display", "none");
        
        showPending(1);
    }
    
    function showExpiredOffer() {
        $("#approval_pending_tab").addClass("tab").removeClass("active_tab");
        $("#active_offer_tab").addClass("tab").removeClass("active_tab");
        $("#expired_offer_tab").removeClass("tab").addClass("active_tab");
        
        $("#active_offer").css("display", "none");
        $("#pending_approval").css("display", "none");
        $("#expired_offer").css("display", "block");
        
        showExpired(1);
    }
    
    function viewSODetails(id) {
        $("#listing").css("display", "none");
        $.post("ajax-file/sale-offer-details.php", {id: id}, function(data) {
            $("#details").css("display", "block");
            $('#details').html(data);
        });
    }
    
    function backToListing() {
        $("#details").css("display", "none");
        $("#listing").css("display", "block");
    }
    
    function editSaleOffer(id) {
        $.post("ajax-file/sale-offer-edit.php", {id: id}, function(data) {
            $('#details').html(data);
        });
    }
    
    function delSaleOffer(id) {
        if (confirm("Are you sure to delete this Offer?")) {
            $.post("ajax-file/delSaleOffer.php", {id: id}, function(data) {
                window.location.reload();
            });
        }
    }
    
    function showActive(page) {
        $.post("ajax-file/active-selloffer.php", {page: page}, function(data) {
            $('#res').html(data);
        });
    }
    
    function showPending(page) {
        $.post("ajax-file/pending-approval-selloffer.php", {page: page}, function(data) {
            $('#res').html(data);
        });
    }
    
    function showExpired(page) {
        $.post("ajax-file/expired-selloffer.php", {page: page}, function(data) {
            $('#res').html(data);
        });
    }
    </script>
</head>
<body>
    <div id="imgtrailer" style="position:absolute; z-index:4; visibility:hidden;">
        <img src="images/loading.gif" height="32" width="32" alt="Loading">
    </div>
    
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
        
        <!-- Menu -->
        <?php include __DIR__ . "/includes/header_menu.php"; ?>
        
        <!-- القائمة الجانبية اليسرى -->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="ulid" class="nln1" style="margin:0px; padding:0px;" title="أدوات البائع">
                <li>
                    <h3 style="font-size:16px; font-weight:bold; color:#000; margin:0; padding:18px 5px 18px 5px; background-color:#FFFFFF;" title="Seller Tools">
                        أدوات البائع
                    </h3>
                </li>
                
                <li style="border-bottom:none" title="Products / Services">
                    <h3>المنتجات والخدمات والأعمال التجارية</h3>
                </li>
                <li class="np npnew"><a href="product-add.php" title="Add New Products">»&nbsp;أضف منتج جديد للنشر</a></li>
                <li class="np npnew"><a href="product-list.php" class="" title="Manage Products">»&nbsp;إدارة المنتجات المنشورة</a></li>
                
                <li style="border-bottom:medium none;" title="Buy Leads">
                    <h3>طلبات الشراء</h3>
                </li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php" title="Purchased Buy Leads">»&nbsp;شاهد طلبات الشراء المشتراه</a></li>
                
                <li style="border-bottom:medium none;" title="Sell Offers">
                    <h3>عروض البيع</h3>
                </li>
                <li class="np npnew"><a href="post-sell-offer.php" title="Post a Sell Offer">»&nbsp;إرسل عروض بيع جديدة للنشر</a></li>
                <li class="np npnew"><a class="txtcol leftindi" href="manage-sell-offer.php" title="Manage Sell Offer">»&nbsp;إدارة عروض البيع المنشورة</a></li>
                
                <li style="border-bottom:medium none;" title="Subscriptions">
                    <h3>الإشتراكات بالمنصة</h3>
                </li>
                <li class="np npnew"><a href="membership_plans.php" title="View Latest Membership Plans">»&nbsp;شاهد خطط إشتراك المنصة</a></li>
                <li class="np npnew"><a href="why_egyptmart.php" title="Read Good Reasons To Subscribe">»&nbsp;إقرأ أسباب جيدة للإشتراك بخدمات المنصة</a></li>
                <li class="np npnew"><a href="advertise-with-us.php" title="Read Ads Prices Subscription">»&nbsp;أسعار الإعلانات بالمنصة</a></li>
                
                <li style="border-bottom:medium none; margin-top:40px;" title="You may also like to :">
                    <h2>روابط هامة لأعمالك التجارية</h2>
                </li>
                <li class="np npnew"><a href="buyleads.php" title="View Latest Buy Leads">»&nbsp;شاهد طلبات الشراء المنشورة</a></li>
                <li class="np npnew"><a href="post-buy-req.php" title="Post a New Buy Requirement">»&nbsp;إرسل طلبات شراء جديدة للنشر</a></li>
                <li class="np npnew"><a href="my-enquiries.php" title="Reply Enquiries from Your Website">»&nbsp;إستجيب لإستفسارات المشتريين</a></li>
                <li class="np npnew"><a href="my-contactdetails.php" title="Update Contact Details">»&nbsp;حدث بيانات الإتصال بشركتك</a></li>
                <li class="np npnew"><a href="business-details.php" title="Update Business Information">»&nbsp;حدث بيانات أعمال شركتك</a></li>
            </ul>
            
            <?php include __DIR__ . "/includes/seller-tools-panel.php"; ?>
        </div>
        <!-- نهاية القائمة الجانبية -->
        
        <!-- منطقة تفاصيل العرض (تظهر عند النقر على عرض معين) -->
        <div id="details" style="display:none;"></div>
        
        <!-- المحتوى الرئيسي -->
        <div class="mctr mfl" id="listing">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td valign="TOP" width="100%">
                            <form style="margin:0px;" action="" name="form1">
                                <table align="CENTER" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td valign="TOP" width="100%">
                                                <div class="wd1 mf18 mc5 mta2 mpb10">
                                                    تعديل وإضافة عروض البيع
                                                </div>
                                                
                                                <div id="masterdiv">
                                                    <div id="sub2" style="display:inline;">
                                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tbody>
                                                                <td align="LEFT" class="tab" valign="TOP" id="active_offer_tab">
                                                                    <div class="mpl5 mpr5 mf13 mc1 mpt10" style="vertical-align:middle">
                                                                        <nobr>
                                                                            <a class="hover mtd mb" onClick="showActOffer();" style="cursor:pointer;" title="Live /Active Offer">
                                                                                عروض بيع منشورة
                                                                            </a> (<?php echo $active_count; ?>)
                                                                        </nobr>
                                                                    </div>
                                                                </td>
                                                                
                                                                <td align="LEFT" class="active_tab" valign="TOP" id="approval_pending_tab">
                                                                    <div class="mpl5 mpr5 mf13 mc1 mpt10" style="vertical-align:middle">
                                                                        <nobr>
                                                                            <a class="hover mtd mb" onClick="showApprPending();" style="cursor:pointer;" title="Approval Pending">
                                                                                عروض تحت الموافقة
                                                                            </a> (<?php echo $pending_count; ?>)
                                                                        </nobr>
                                                                    </div>
                                                                </td>
                                                                
                                                                <td align="LEFT" class="tab" valign="TOP" id="expired_offer_tab">
                                                                    <div class="mpl5 mpr5 mf13 mc1 mpt10" style="vertical-align:middle">
                                                                        <nobr>
                                                                            <a class="hover mtd mb" onClick="showExpiredOffer();" style="cursor:pointer;" title="Expired Offer">
                                                                                عروض منتهية
                                                                            </a> (<?php echo $expired_count; ?>)
                                                                        </nobr>
                                                                    </div>
                                                                </td>
                                                                
                                                                <td align="LEFT" valign="TOP"><img src="images/zero.gif" height="1" width="5"></td>
                                                                
                                                                <td align="RIGHT" background="images/topline-bg.gif" width="100%">
                                                                    <div id="post-new">
                                                                        <p id="sellb" class="sellb mf13 fw mb3" style="display:block" onmouseover="hsb()" onmouseout="hsb1()">
                                                                            <a href="post-buy-req.php" title="Post Buy Offer">أنشر طلب شراء</a>&nbsp;|&nbsp;
                                                                            <a href="post-sell-offer.php" title="Post Sell Offer">أنشر عرض بيع</a>
                                                                        </p>
                                                                    </div>
                                                                </td>
                                                            </tbody>
                                                        </table>
                                                        
                                                        <script>
                                                        $(document).ready(function() {
                                                            <?php if ($active_count > $pending_count && $active_count > $expired_count): ?>
                                                            showActOffer();
                                                            <?php elseif ($expired_count > $pending_count && $expired_count > $active_count): ?>
                                                            showExpiredOffer();
                                                            <?php else: ?>
                                                            showActOffer();
                                                            <?php endif; ?>
                                                        });
                                                        </script>
                                                        
                                                        <div id="res"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td valign="TOP"><img src="images/zero.gif" height="1" width="10"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td height="30"></td>
                                        <td><div class="liv" style="margin-right:20px;" align="RIGHT"><b></b></div></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div style="clear:both"><br></div>
                            <div align="CENTER"><br></div>
                            <div align="CENTER"><br><br></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
mysqli_stmt_close($stmt_active);
mysqli_stmt_close($stmt_pending);
mysqli_stmt_close($stmt_expired);
?>