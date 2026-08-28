<?php
/**
 * File: subscription.php
 * Description: عرض خطط الاشتراك (الكريديت) المتاحة للمستخدم
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "subscription.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

global $con;

// جلب خطط العضوية المتاحة
$plans = [];
$sql_mp = "SELECT mp_id, mp_name, mp_credits, mp_amount FROM membership_plan WHERE mp_status = '1' ORDER BY mp_amount ASC";
$result_mp = mysqli_query($con, $sql_mp);
while ($row = mysqli_fetch_assoc($result_mp)) {
    $plans[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/credit_subs01.css" type="text/css" rel="stylesheet">
    
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /><![endif]-->
    <!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
    
    <style>
        .redirect {
            color: #0000ff;
            text-decoration: none;
        }
        .redirect:hover {
            color: #ff0000;
            text-decoration: underline;
            cursor: pointer;
        }
        .m_pkgdes {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .m_pricebtn {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .m_pricebtn:hover {
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
    </style>
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    
    <script>
    function choosePackage(id) {
        window.location.href = "payment-option.php?id=" + id;
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
        
        <!-- خطوات الاشتراك -->
        <div class="m_ca">
            <form style="margin:0px;" method="post" name="showPlan" action="/cgi/eto-subscription-checkout.mp">
                <div class="m_crtop">
                    <div class="m_fst1s cpa1 m_crtophds">
                        <p class="m_spimg m_1s cpa1"></p>
                        Select Subscription Plan
                    </div>
                    <p class="m_spimg m_arwmiddle cpa1"></p>
                    <div class="m_fst2s cpa1 m_crtophd">
                        <p class="m_spimg m_2nons cpa1"></p>
                        Choose Payment Option
                    </div>
                </div>
                
                <div class="m_innerpart">
                    <h1><b>Available Plans</b></h1>
                    
                    <div class="cpm2"><!-- clear:both --></div>
                    <div class="cpm2"><!-- clear:both --></div>
                    <div class="cpm2"><!-- clear:both --></div>
                    
                    <?php foreach ($plans as $plan): 
                        $plan_id = (int)$plan['mp_id'];
                        $plan_name = htmlspecialchars($plan['mp_name'] ?? '', ENT_QUOTES, 'UTF-8');
                        $plan_credits = (int)$plan['mp_credits'];
                        $plan_amount = number_format((float)$plan['mp_amount'], 2);
                        $leads_count = (int)($plan_credits / 20);
                        $token = rand(10000, 99999) . md5((string)$plan_id);
                    ?>
                    <div class="m_pkgdes m_btbdr">
                        <div class="m_basicpkg cpa1">
                            <p class="m_spimg m_bsicimg cpa1"></p>
                            <?php echo $plan_name; ?> - <?php echo $plan_credits; ?> Credits<br>
                            <span>You can purchase <?php echo $leads_count; ?> Leads.</span>
                        </div>
                        
                        <div class="m_pricebtn cpa1" id="plan_<?php echo $plan_id; ?>" 
                             onclick="choosePackage('<?php echo $token; ?>');">
                            <input class="cpmb2" value="<?php echo $plan_credits; ?>" 
                                   name="plan" style="display:none" type="radio">
                            <b>
                                <span class=""><?php echo htmlspecialchars(getCurrencySymbol(), ENT_QUOTES, 'UTF-8'); ?>&nbsp;</span> 
                                <?php echo $plan_amount; ?><br>Pay Now
                            </b>
                        </div>
                        <div style="clear:both"></div>
                    </div>
                    <?php endforeach; ?>
                    
                </div>
            </form>
            <img src="images/z_002.gif" class="cpm2" height="2" width="1" alt="">
        </div>
        
        <img src="images/z_002.gif" height="2" width="1" alt="">
        <img src="images/z_002.gif" height="2" width="1" alt="">
        
        <style>
            .redirect {
                color: #0000ff;
                text-decoration: none;
            }
            .redirect:hover {
                color: #ff0000;
                text-decoration: underline;
                cursor: pointer;
            }
        </style>
        
        <div id="main_helparea" style="position:absolute; left:0; top:0; width:100%; display:none;">
            <div id="inside_helpmsg" style="z-index:11; width:600px; border-radius:4px; webkit-border-radius:4px; font-family:arial; moz-border-radius:4px; background-color:#FEF9EE; border-color:#FFDA8B #EDB153 #EDB153 #FFDA8B; border-style:solid; border-width:3px 5px 5px 3px; margin:0px auto; position:relative; box-shadow:0px 4px 10px 5px rgba(221,221,221,1);">
                <span style="position:absolute; top:-17px; right:-13px; cursor:pointer;" onclick="cnfirmmsg_close()">
                    <img src="images/q_clbtn.png" align="right" height="28" width="29" alt="Close">
                </span>
                <div style="font-size:25px; text-align:center; padding:25px 8px 25px 10px; line-height:24px; color:#000000;">
                    <p style="padding:0px; margin:0px; line-height:40px;">
                        <b style="color:#e57100; font-size:30px;">Want to know more about the service?</b> 
                        <br><b>Call us @ 08030178025</b>
                    </p>
                    <br>
                    <p style="padding:0px; margin:0px; font-size:18px; font-family:arial;" onclick="r_direct();">
                        Still you want to Leave this page 
                        <span class="redirect" onclick="r_direct();">click here</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>