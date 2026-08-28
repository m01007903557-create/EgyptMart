<?php
/**
 * File: manage-buylead-alert.php
 * Description: إدارة تنبيهات طلبات الشراء - عرض التصنيفات المشترك بها وإضافتها وحذفها
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "manage-buylead-alert.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

global $con;

// معالجة إضافة تصنيف عبر POST
if (isset($_POST['sub_cat_id']) && !empty($_POST['sub_cat_id']) && is_numeric($_POST['sub_cat_id'])) {
    $sub_cat_id = (int)$_POST['sub_cat_id'];
    
    // التحقق من عدم وجود التصنيف مسبقاً
    $check_query = "SELECT bac_id FROM buylead_alert_category WHERE bac_pc_id = ? AND bac_usr_id = ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt_check, 'ii', $sub_cat_id, $uid);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_sql = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) VALUES (?, ?, NOW())";
        $stmt_insert = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'ii', $uid, $sub_cat_id);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
    }
    mysqli_stmt_close($stmt_check);
}
// معالجة إضافة تصنيف عبر GET
elseif (isset($_GET['sub_cat_id']) && !empty($_GET['sub_cat_id']) && is_numeric($_GET['sub_cat_id'])) {
    $sub_cat_id = (int)$_GET['sub_cat_id'];
    
    // التحقق من عدم وجود التصنيف مسبقاً
    $check_query = "SELECT bac_id FROM buylead_alert_category WHERE bac_pc_id = ? AND bac_usr_id = ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt_check, 'ii', $sub_cat_id, $uid);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($check_result) == 0) {
        $insert_sql = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) VALUES (?, ?, NOW())";
        $stmt_insert = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'ii', $uid, $sub_cat_id);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
    }
    mysqli_stmt_close($stmt_check);
}
// معالجة البحث عن تصنيف
elseif (isset($_GET['keywords']) && isset($_GET['rctyp']) && $_GET['rctyp'] == 'buy_lead' && !empty($_GET['keywords'])) {
    $keywords = trim($_GET['keywords']);
    
    $sql_key = "SELECT pc.pc_id 
                FROM buy_requirement br
                INNER JOIN product_category pc ON pc.pc_id = br.br_pc_id
                WHERE br.br_pd_name = ? 
                AND pc.pc_status = '1' 
                ORDER BY pc.pc_order ASC 
                LIMIT 1";
    
    $stmt_key = mysqli_prepare($con, $sql_key);
    mysqli_stmt_bind_param($stmt_key, 's', $keywords);
    mysqli_stmt_execute($stmt_key);
    $result_key = mysqli_stmt_get_result($stmt_key);
    
    if (mysqli_num_rows($result_key) > 0) {
        $row_key = mysqli_fetch_object($result_key);
        $key_cat_id = (int)$row_key->pc_id;
        
        // التحقق من عدم وجود التصنيف مسبقاً
        $check_query = "SELECT bac_id FROM buylead_alert_category WHERE bac_pc_id = ? AND bac_usr_id = ? LIMIT 1";
        $stmt_check = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($stmt_check, 'ii', $key_cat_id, $uid);
        mysqli_stmt_execute($stmt_check);
        $check_result = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($check_result) == 0) {
            $insert_sql = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) VALUES (?, ?, NOW())";
            $stmt_insert = mysqli_prepare($con, $insert_sql);
            mysqli_stmt_bind_param($stmt_insert, 'ii', $uid, $key_cat_id);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt_check);
    }
    mysqli_stmt_close($stmt_key);
}

// جلب التصنيفات المشترك بها
$sql = "SELECT pc.*, bac.bac_id 
        FROM product_category pc
        INNER JOIN buylead_alert_category bac ON pc.pc_id = bac.bac_pc_id
        WHERE bac.bac_usr_id = ?
        ORDER BY pc.pc_order ASC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$subscriptions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $subscriptions[] = $row;
}
$subscription_count = count($subscriptions);
mysqli_stmt_close($stmt);

// جلب الإعلان الجانبي
$adv_sql = "SELECT adv_link, adv_img FROM advertisement 
            WHERE adv_imagewidth = '200' AND adv_imageheight = '154' 
            AND adv_status = '1' 
            ORDER BY RAND() 
            LIMIT 1";
$adv_result = mysqli_query($con, $adv_sql);
$adv_row = mysqli_fetch_assoc($adv_result);
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
    <link href="css/mng-trde-alrt.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="css/colorbox.css" />
    <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
    <link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>
    
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /><![endif]-->
    <!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
    
    <style>
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
        }
        .select_sp tr td {
            padding: 10px;
        }
    </style>
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
    
    <script>
    $(document).ready(function() {
        $('.ajax').live('click', function() {
            $.colorbox({
                href: $(this).attr('href'),
                open: true
            });
            return false;
        });
        
        $(".inline").colorbox({inline: true, width: "50%"});
        
        $("#keywordsFilter").unbind().live('keyup', function() {
            var type11 = 'Products';
            $("#keywordsFilter").autocomplete("autocomplete.php", {
                selectFirst: true,
                extraParams: {type: type11},
                width: 407
            }).result(function(event, data, formatted) {
                $("input#keywordsFilter").val(data);
            });
        });
    });
    
    function addAlertCategory() {
        $.post("ajax-file/addBuyleadAlertCat.php", {}, function(data) {
            window.location.reload();
        });
    }
    
    function delAlertCat(id) {
        if (confirm("هل متأكد من رغبتك فى الغاء صنف الشراء من الإشعارات ؟")) {
            $.post("ajax-file/delBuyleadAlertCat.php", {id: id}, function(data) {
                window.location.reload();
            });
        }
    }
    </script>
</head>
<body class="search-show-box">
    <div class="hm1 bbc" id="res-mob1">
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
                    <h3 style="font-size:16px; font-weight:bold; color:#000; margin:0; padding:18px 5px 18px 5px; background-color:#f2f2f2;" title="Buy Leads">
                        طـلبـات الـشــراء
                    </h3>
                </li>
                
                <li class="np npnew"><a href="post-buy-req.php" title="Post a Buy Requirement">»&nbsp;إرسل طلب شراء للنشر</a></li>
                <li class="np npnew"><a href="myproduct-buy.php" title="myproduct-buy.php">»&nbsp;سجل طلبات شراء معتادة</a></li>
                <li class="np npnew"><a href="manage-buy-requirement.php" title="Manage Buy Requirements">»&nbsp;إدارة طلبات شرائك المنشورة</a></li>
                <li class="np npnew"><a href="manage-selloffer-alert.php" title="Manage Sell Offer Alerts">»&nbsp;سجل منتجات إشعارات فرص بيع</a></li>
                <li class="np npnew"><a href="buyleads.php" title="View Latest Buy Leads">»&nbsp;شاهد أخر طلبات الشراء</a></li>
                <li class="np npnew"><a href="manage-purchased-tenders.php" title="View Purchased Tenders">»&nbsp;شاهد بيانات المناقصات المشتراه</a></li>
                <li class="np npnew"><a href="manage-buylead-alert.php" title="Manage Buy Lead Alerts">»&nbsp;سجل منتجات إشعارات طلبات شراء</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php" title="Manage Purchased Buyleads">»&nbsp;بيانات طلبات الشراء المشتراه</a></li>
                <li class="np npnew"><a href="my-buylead-locationpref.php" title="Buy Location Preferences">»&nbsp;أماكن إشعارات طلبات الشراء</a></li>
                
                <li style="border-bottom:medium none;" title="Help / FAQs?">
                    <h3>أسئلة متكررة ومساعـدة</h3>
                </li>
                <li class="np npnew"><a href="help.php" title="Buy Leads Help / FAQs?">»&nbsp;الأسئلة المتكررة حول طلبات الشراء</a></li>
                
                <li class="ug-banner">
                    <?php if ($adv_row): ?>
                    <a href="//<?php echo htmlspecialchars($adv_row['adv_link'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                        <img src="upload/advertisement/<?php echo htmlspecialchars($adv_row['adv_img'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                             width="200" height="154" alt="Advertisement">
                    </a>
                    <?php else: ?>
                    <img src="upload/advertisement/emicosteel.jpg" alt="" border="0" height="154" width="200">
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <!-- نهاية القائمة الجانبية -->
        
        <!-- المحتوى الرئيسي -->
        <div class="mctr mfl">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td id="id_attribute_value" valign="TOP" width="100%">
                            <form style="margin:0px; padding:10px;" id="postForm" name="postForm" method="post" action="/cgi/eto-alert-subs-new.mp">
                                <table style="table-layout:fixed; width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="border-right:0px; padding-right:10px" valign="top" width="100%">
                                                <table style="table-layout:fixed; width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td align="LEFT" height="38" valign="TOP" width="325">
                                                                <img src="images/zero.gif" height="4" width="1"><br>
                                                                <nobr>
                                                                    <div class="mf18 mc5 mta2 mpr8 mpt10" title="Manage Buy-leads Categories To Get Alerts Via Your Mailbox">
                                                                        تلقى طلبات شراء للمنتجات التى تسجلها هنا
                                                                    </div>
                                                                </nobr>
                                                            </td>
                                                            <td valign="bottom"></td>
                                                            <td valign="bottom">
                                                                <div class="manage_country mb" style="text-align:left;">
                                                                    <a href="browse-cat-for-buylead-alert.php" class="ajax" title="Add More Categories">
                                                                        أضف مزيد من أصناف إستقبال طلبات شراء
                                                                    </a>
                                                                </div>
                                                            </td>
                                                            <td valign="bottom">
                                                                <div class="manage_country mb">
                                                                    <a href="my-buylead-locationpref.php" title="Manage Your Location Preferences">
                                                                        أختار مكان تلقى طلبات شراء
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                                <div style="border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
                                                
                                                <div style="font-family:arial; font-size:12px; margin-top:9px; color:#000000; padding-left:5px;">
                                                    <div style="width:218px; float:left;" title="No. of Product Categories Subscribed">
                                                        عدد الاصناف المشترك بها لتلقى إشعارات شراء
                                                    </div> : <?php echo $subscription_count; ?><br>
                                                </div>
                                                
                                                <div style="border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
                                                
                                                <table style="table-layout:fixed; width:100%" class="mgoffer" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="2" align="LEFT">
                                                                <b style="font-size:14px;" title="Your Existing Buy-lead Products Subscription">
                                                                    هذه هى أصناف منتجاتك المسجله
                                                                </b>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                                <table class="select_sp" style="border-top:1px solid #C8DDEC" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td class="tdoffer" style="padding-left:5px;" align="LEFT" bgcolor="#F1F9FE" width="80%"></td>
                                                            <td class="tdoffer" align="right" bgcolor="#F1F9FE">
                                                                <img src="images/zero.gif" height="1" width="85"><br>Remove
                                                            </td>
                                                        </tr>
                                                        
                                                        <?php if ($subscription_count > 0): ?>
                                                            <?php foreach ($subscriptions as $sub): ?>
                                                            <tr id="map1">
                                                                <td class="mgoffer" align="LEFT">
                                                                    <?php echo htmlspecialchars($sub['pc_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                                </td>
                                                                <td style="cursor:pointer;" align="CENTER">
                                                                    <a onclick="delAlertCat(<?php echo (int)$sub['bac_id']; ?>)" style="cursor:pointer;">
                                                                        <img src="images/del_img.gif" hspace="6" alt="Delete">
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                        <tr>
                                                            <td colspan="5" height="60">
                                                                <div style="font-family:arial; font-size:16px; color:#FF0000;" align="center">
                                                                    <b>You do not have any Buy Lead Product Alerts</b>
                                                                </div>
                                                                <div style="font-family:arial; font-size:16px; color:#FF0000;" align="center">
                                                                    <a href="browse-cat-for-buylead-alert.php" class="ajax">
                                                                        إضغط هنا لإضافة مزيد من إشعارات طلبات الشراء
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                                
                                                <span id="subs_cats"></span>
                                                <span id="procssing"></span>
                                            </td>
                                            <td><img src="images/zero.gif" height="2" width="2"></td>
                                        </tr>
                                        <input name="catid" id="catid" value="" type="hidden">
                                    </tbody>
                                </table>
                            </form>
                            <div><br><br><br><br></div>
                        </td>
                        <td style="border-right:0px;" valign="top"><img src="images/gray-line.gif" height="1" width="1"></td>
                    </tr>
                </tbody>
            </table>
            <div style="clear:both"><br></div>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>