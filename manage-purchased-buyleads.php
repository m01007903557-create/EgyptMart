<?php
/**
 * File: manage-purchased-buyleads.php
 * Description: إدارة طلبات الشراء المشتراه - عرض وتفاصيل الطلبات التي تم شراؤها
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "manage-purchased-buyleads.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

global $con;

// جلب بيانات المستخدم
$sql = "SELECT usr_mp_id FROM user WHERE usr_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$membership_id = (int)($row['usr_mp_id'] ?? 0);
mysqli_stmt_close($stmt);

// حساب عدد طلبات الشراء المشتراه
$sql_pur = "SELECT COUNT(*) as cnt FROM purchased_buy_requirement WHERE pbr_usr_id = ? AND pbr_status = '1'";
$stmt_pur = mysqli_prepare($con, $sql_pur);
mysqli_stmt_bind_param($stmt_pur, 'i', $uid);
mysqli_stmt_execute($stmt_pur);
$result_pur = mysqli_stmt_get_result($stmt_pur);
$row_pur = mysqli_fetch_assoc($result_pur);
$purchased_count = (int)($row_pur['cnt'] ?? 0);
mysqli_stmt_close($stmt_pur);
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
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/eto-buyreq.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="css/colorbox.css" />
    
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /><![endif]-->
    <!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
    
    <style>
        .sub { display: none; }
        .tbq { display: none; }
        .tabTextColor { color: #fff; }
        .npo { background-color: #f0f0f0; }
        .ap2 { background-color: #2362a5; color: white; }
    </style>
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <script>
    $(document).ready(function() {
        $('.ajax').live('click', function() {
            $.colorbox({
                href: $(this).attr('href'),
                open: true,
                width: "800px",
                height: "500px"
            });
            return false;
        });
        
        showPurBuyleads(1);
    });
    
    function showPurBuyleads(page) {
        $("#opnReq_tab").removeClass("npo").addClass("ap2 tabTextColor");
        $("#clsReq_tab").removeClass("ap2 tabTextColor").addClass("npo");
        purchasedBuyleads(page);
    }
    
    function showClosedReq(page) {
        $("#clsReq_tab").removeClass("npo").addClass("ap2 tabTextColor");
        $("#opnReq_tab").removeClass("ap2 tabTextColor").addClass("npo");
        closedRequirement(page);
    }
    
    function purchasedBuyleads(page) {
        $.post("ajax-file/purchased-buyleads.php", {page: page}, function(data) {
            $('#res').html(data);
        });
    }
    
    function closedRequirement(page) {
        $.post("ajax-file/closedRequirement.php", {page: page}, function(data) {
            $('#res').html(data);
        });
    }
    
    function detailPurBuyleads(id) {
        $.post("ajax-file/detailPurBuyleads.php", {id: id}, function(data) {
            $('#detail_req').html(data);
            $('#req_listing').css("display", "none");
            $('#detail_req').css("display", "block");
        });
    }
    
    function delRequirement(id, v) {
        if (confirm("! لايمكنك الغاء طلبات الشراء المشتراه لإستخداماتك المستقبلية")) {
            $.post("ajax-file/delRequirement.php", {id: id}, function(data) {
                if (v == 'op') {
                    purchasedBuyleads(1);
                } else {
                    closedRequirement(1);
                }
            });
        }
    }
    
    function goback() {
        $('#detail_req').css("display", "none");
        $('#req_listing').css("display", "block");
    }
    </script>
</head>
<body>
    <div class="hm1 bbc manage-purchase-leads" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
        
        <!-- Menu -->
        <?php include __DIR__ . "/includes/header_menu.php"; ?>
        
        <!-- القائمة الجانبية اليسرى -->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="ulid" class="nln1" style="margin:0px; padding:0px;">
                <li>
                    <h3 style="font-size:16px; font-weight:bold; text-align:center; color:#000; margin:0; padding:18px 5px 18px 5px; background-color:#FFFFFF;" title="Buyer Tools">
                        أدوات المشترى
                    </h3>
                </li>
                
                <li class="np npnew">
                    <a href="post-buy-req.php" title="Post a Buy Requirement">»&nbsp;إرسل طلب شراء للنشر</a>
                </li>
                <li class="np npnew">
                    <a href="post-buy-req.php" title="myproduct-buy.php">»&nbsp;إنشر طلبات شراء معتادة</a>
                </li>
                <li class="np npnew">
                    <a href="manage-buy-requirement.php" title="Manage Buy Requirements">»&nbsp;تحكم فى طلبات شرائك المنشورة</a>
                </li>
                <li class="np npnew">
                    <a href="manage-selloffer-alert.php" title="Manage Sell Offer Alerts">»&nbsp;سجل منتجات إشعارات فرص بيع</a>
                </li>
                <li class="np npnew">
                    <a href="buyleads.php" title="View Latest Buy Leads">»&nbsp;شاهد أخر طلبات الشراء</a>
                </li>
                <li class="np npnew">
                    <a href="manage-purchased-tenders.php" title="View Purchased Tenders">»&nbsp;شاهد بيانات المناقصات المشتراه</a>
                </li>
                <li class="np npnew">
                    <a href="manage-buylead-alert.php" title="Manage Buy Lead Alerts">»&nbsp;سجل منتجات إشعارات فرص شراء</a>
                </li>
                <li class="np npnew">
                    <a href="manage-purchased-buyleads.php" title="Manage Purchased Buyleads">»&nbsp;طلبات الشراء الجاهزة المشتراه</a>
                </li>
            </ul>
        </div>
        <!-- نهاية القائمة الجانبية -->
        
        <!-- المحتوى الرئيسي -->
        <div class="mctr_buyreq mfl" id="req_listing">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td valign="TOP" width="100%">
                            <form style="margin:0px;" action="" name="form1">
                                <table align="CENTER" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td valign="TOP" width="100%">
                                                <!-- معلومات الرصيد والعضوية -->
                                                <?php if ($membership_id > 3): ?>
                                                <div class="mfr mf14 md1 mc3">
                                                    <span class="mf12">[ 
                                                        <a href="membership_plans.php" title="Pay Annual Subscription to get FREE buy leads !">
                                                            خطط الإشتراك بالمنصة
                                                        </a>
                                                    ]</span>
                                                    <div class="mta mpb8 mpt5 mf11">
                                                        <a href="transaction_history.php" title="شاهد بيانات نقاط شراء نقاط الكريديت الخاصة بك"></a>
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <div class="mfr mf14 md1 mc3">
                                                    Available Credits: <font class="mc6"> <span id="current_balance">0</span> Credits</font>
                                                    <span class="mf12">[ 
                                                        <a href="subscription.php">Purchase More Credits</a>
                                                    ]</span>
                                                    <div class="mta mpb8 mpt5 mf11">
                                                        <a href="transaction_history.php" title="شاهد بيانات نقاط شراء نقاط الكريديت الخاصة بك"></a>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <div class="wd mf18 mc5 mta2 mpb10"></div>
                                                
                                                <div id="masterdiv">
                                                    <div id="sub1" style="display:inline;">
                                                        <div class="ap1">
                                                            <a class="f1 ap2 tabTextColor" onclick="showPurBuyleads(1);" style="cursor:pointer;" id="opnReq_tab" title="Purchased Buy Leads">
                                                                طلبات الشراء الجاهزة المشتراه (<?php echo $purchased_count; ?>)
                                                            </a>
                                                            <div class="c3"></div>
                                                        </div>
                                                        
                                                        <div id="res"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td valign="TOP"><img src="images/zero.gif" height="1" width="10"></td>
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
        
        <!-- منطقة تفاصيل الطلب (تظهر عند النقر على طلب معين) -->
        <div id="detail_req" style="display:none;"></div>
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_pur);
?>