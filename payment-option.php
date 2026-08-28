<?php
// payment-options.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "common.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: membership_plans.php");
    exit;
}

$_SESSION['last_page'] = "payment-option.php?id=" . urlencode($_GET['id']);

if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];
$id = substr($_GET['id'], 5);
$id = mysqli_real_escape_string($con, $id);

$sql = "SELECT * FROM membership_plan WHERE md5(mp_id) = '{$id}'";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    header("Location: membership_plans.php");
    exit;
}

$row = mysqli_fetch_object($res);
$amount = (float)($row->mp_amount ?? 0);
$plan_id = (int)($row->mp_id ?? 0);
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/credit_subs01.css" type="text/css" rel="stylesheet">
    
    <script src="js/jquery-1.2.1.min.js"></script>
    
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
    
    <script>
    function choosePackage(mp, pg) {
        window.location.href = "payment-confirm.php?m=" + mp + "&p=" + pg;
    }
    
    function cnfirmmsg_close() {
        $('#main_helparea').hide();
    }
    
    function r_direct() {
        window.location.href = "membership_plans.php";
    }
    
    function getGAEventTrackingJS(plan) {
        // يمكن إضافة كود Google Analytics هنا
        return true;
    }
    
    function frmsubmit(plan) {
        // تنفيذ عملية الدفع
        return true;
    }
    </script>
</head>

<body>
    <div id="imgtrailer" style="position:absolute; z-index:4;visibility:hidden;">
        <img src="images/loading.gif" height="32" width="32">
    </div>

    <div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
        
        <br><br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1"></div>

        <div class="m_ca">
            <form style="margin:0px;" method="post" name="showPlan" action="/cgi/eto-subscription-checkout.mp">
                <div class="m_crtop">
                    <div class="m_fst1s cpa1 m_crtophd">
                        <p class="m_spimg m_1nons cpa1"></p>
                        اختر خطة الاشتراك
                    </div>
                    <p class="m_spimg m_arwmiddle cpa1"></p>
                    <div class="m_fst2s cpa1 m_crtophds">
                        <p class="m_spimg m_2s cpa1"></p>
                        اختر طريقة الدفع
                    </div>
                </div>

                <div class="m_innerpart">
                    <div class="cpm2"></div>
                    <div class="cpm2"></div>
                    <div class="cpm2"></div>
                    <div></div>

                    <?php
                    $sql_pg = "SELECT * FROM payment_gateway WHERE pg_status = '1'";
                    $res_pg = mysqli_query($con, $sql_pg);
                    
                    if (mysqli_num_rows($res_pg) > 0):
                        while ($row_pg = mysqli_fetch_object($res_pg)):
                            $pg_logo = !empty($row_pg->pg_logo) ? htmlspecialchars($row_pg->pg_logo) : '';
                            $pg_id = (int)($row_pg->id ?? 0);
                    ?>
                        <div class="m_pkgdes m_btbdr">
                            <div class="m_basicpkg cpa1">
                                <p class="m_bsicimg cpa1">
                                    <?php if (!empty($pg_logo)): ?>
                                        <img src="images/payment-gateway/<?php echo $pg_logo; ?>" style="max-height:50px;" alt=""/>
                                    <?php endif; ?>
                                </p>
                                <span><?php echo htmlspecialchars($row_pg->pg_name ?? ''); ?></span>
                            </div>
                            
                            <div class="m_pricebtn cpa1" id="<?php echo $pg_id; ?>" 
                                 onclick="choosePackage('<?php echo htmlspecialchars(substr($_GET['id'] ?? '', 0, 5) . md5((string)$plan_id)); ?>', 
                                 '<?php echo rand(10000, 99999) . md5((string)$pg_id); ?>');">
                                <input class="cpmb2" value="<?php echo $pg_id; ?>" name="plan" style="display:none" type="radio">
                                <b>
                                    <span class=""><?php echo htmlspecialchars(getCurrencySymbol() ?? ''); ?>&nbsp;</span>
                                    <?php echo number_format($amount, 2); ?><br>
                                    ادفع الآن
                                </b>
                            </div>
                            <div style="clear:both"></div>
                        </div>
                        <div></div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <div class="m_pkgdes m_btbdr">
                            <div class="m_basicpkg cpa1">
                                <p class="m_bsicimg cpa1"></p>
                                <span>لا توجد بوابات دفع متاحة حالياً</span>
                            </div>
                            <div style="clear:both"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
            <img src="images/z_002.gif" class="cpm2" height="2" width="1">
        </div>
        <img src="images/z_002.gif" height="2" width="1">
        <img src="images/z_002.gif" height="2" width="1">
    </div>

    <div id="main_helparea" style="position:absolute; left:0;top:0;width:100%;display:none">
        <div id="inside_helpmsg" style="z-index:11;width:600px; border-radius:4px; font-family:arial; background-color: #FEF9EE; border-color: #FFDA8B #EDB153 #EDB153 #FFDA8B; border-style: solid; border-width: 3px 5px 5px 3px; margin:0px auto; position:relative; box-shadow: 0px 4px 10px 5px rgba(221,221,221,1);">
            <span style="position:absolute; top:-17px;right:-13px; cursor:pointer;" onclick="cnfirmmsg_close()">
                <img src="images/q_clbtn.png" align="right" height="28" width="29">
            </span>
            <div style="font-size:25px; text-align:center; padding:25px 8px 25px 10px; line-height:24px; color:#000000;">
                <p style="padding:0px; margin:0px; line-height:40px;">
                    <b style="color:#e57100; font-size:30px;">تريد معرفة المزيد عن الخدمة؟</b>
                    <br>
                    <b>اتصل بنا على 08030178025</b>
                </p>
                <br>
                <p style="padding:0px; margin:0px; font-size:18px; font-family:arial;" onclick="r_direct();">
                    مازلت تريد مغادرة هذه الصفحة 
                    <span class="redirect" onclick="r_direct();">اضغط هنا</span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="c3">&nbsp;</div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>