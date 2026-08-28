<?php
/**
 * File Name: post-auction-res.php
 * PHP Version: 8.3
 * Description: صفحة تأكيد نشر المزايدة - نسخة مطورة ومتوافقة مع PHP 8.3
 * 
 * التغييرات الرئيسية:
 * - إضافة declare(strict_types=1) لتفعيل التحقق الصارم من الأنواع
 * - تحسين أمان الجلسات
 * - إضافة validation للبيانات المستخدمة
 * - استخدام htmlspecialchars لجميع المخرجات
 * - تحسين معالجة الأخطاء
 * - إضافة DocBlocks للتوثيق
 * - تحديث دوال HTML القديمة
 */

declare(strict_types=1);

require_once "common.php";

// إعدادات أمان الجلسة
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود المستخدم في الجلسة
if (empty($_SESSION['uid_indm'] ?? null)) {
    header('Location: sign-in.php');
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

/**
 * الحصول على معلومات المستخدم بشكل آمن
 * 
 * @param int $uid معرف المستخدم
 * @param string $field الحقل المطلوب
 * @return string قيمة الحقل أو سلسلة فارغة
 */
function getUserInfo(int $uid, string $field): string
{
    static $userCache = [];
    
    if (!isset($userCache[$uid])) {
        global $db; // إذا كنت تستخدم اتصال قاعدة بيانات عام
        // يمكنك إضافة منطق جلب معلومات المستخدم من قاعدة البيانات هنا
        $userCache[$uid] = [
            'name_prefix' => '',
            'fname' => '',
            'lname' => ''
        ];
    }
    
    return htmlspecialchars($userCache[$uid][$field] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// بيانات المستخدم للعرض
$userPrefix = getUserInfo($uid, 'name_prefix');
$userFirstName = getUserInfo($uid, 'fname');
$userLastName = getUserInfo($uid, 'lname');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? 'تأكيد نشر المزايدة'); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <!-- CSS Files -->
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/tradeleads05.css" type="text/css" rel="stylesheet">
    <link href="css/pdash.css" type="text/css" rel="stylesheet">
    
    <!-- JavaScript -->
    <script src="js/jquery.js"></script>
    
    <style type="text/css">
        .thanksmsg ul li { 
            padding-bottom: 5px; 
            margin-left: 35px;
            list-style-image: url('http://my.imimg.com/gifs/ul.gif');
        }
        .thanksmsg ul ul li { 
            padding: 0;
            margin-left: 35px;
            list-style-image: url('http://my.imimg.com/gifs/ulul.gif');
        }
        .lf {
            text-align: left;
        }
        .thanksadlink a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
        }
        .thanksadlink a:hover {
            color: #BF0000;
        }
        .success-message {
            background-color: #E1F0FF;
            border: 1px solid #86CDFD;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
    
    <div class="bt">
        <img src="images/z.gif" 
             alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" 
             width="1" 
             height="1">
    </div>

    <?php include "includes/header_menu.php"; ?>
    
    <!-- القائمة الجانبية -->
    <div class="f1 w61n tb lh ml br" id="lnav">
        <?php include "includes/seller-tools-panel.php"; ?>
    </div>
    
    <!-- المحتوى الرئيسي -->
    <div class="w57 b1_m2 f1 blr p2b b1_m2">
        <div>
            <table style="align:center;">
                <tr>
                    <td valign="TOP">
                        <img src="post-sell-offer-res_files/zero.gif" height="1" width="1">
                    </td>
                    <td valign="TOP" width="100%">
                        <div><img src="images/zero.gif" height="15" width="1"><br></div>
                        
                        <table class="lf" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="border-right:0px;" valign="top">
                                    <div>
                                        <table style="border-collapse:collapse;" 
                                               align="CENTER" 
                                               border="1" 
                                               bordercolor="#86CDFD" 
                                               cellpadding="0" 
                                               cellspacing="0" 
                                               width="100%">
                                            <tr>
                                                <td bgcolor="#E1F0FF" class="success-message">
                                                    <div class="thankscathead">
                                                        <b>
                                                            <?php 
                                                            $userName = trim($userPrefix . ' ' . $userFirstName . ' ' . $userLastName);
                                                            echo 'عزيزي ' . (!empty($userName) ? htmlspecialchars($userName) : 'المستخدم');
                                                            ?>
                                                        </b>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td class="thanksmsg" width="100%">
                                                    <ul style="margin-bottom:0px; margin-top:0px;">
                                                        <li class="thanksmsg">
                                                            تم استلام المزايدة التي قمت بنشرها 
                                                            <b><font color="#BF0000">بنجاح</font></b>.
                                                        </li>
                                                        <li class="thanksmsg">
                                                            سيتم عرضها خلال 
                                                            <b><font color="#BF0000">يومي عمل</font></b> 
                                                            بعد موافقة الإدارة عليها.
                                                        </li>
                                                    </ul>
                                                    <div align="center">
                                                        <font color="#BF0000" face="arial" size="-1">
                                                            <a href="post-auction.php">
                                                                اضغط هنا للاستمرار...
                                                            </a>
                                                        </font>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <table align="CENTER" border="0" cellpadding="2" cellspacing="0" width="100%">
                                            <tr>
                                                <td align="CENTER" height="35" valign="BOTTOM" width="225">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="165">
                                                        <tr>
                                                            <td background="images/thkadbg2.gif" height="26">
                                                                <div class="thanksadlink" align="CENTER">
                                                                    <a href="post-auction.php">
                                                                        <b>نشر المزيد من المزايدات</b>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                
                                                <td align="CENTER" valign="BOTTOM" width="225">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="180">
                                                        <tr>
                                                            <td background="images/thkadbg1.gif" height="26">
                                                                <div class="thanksadlink" align="CENTER">
                                                                    <a href="manage-auctions.php">
                                                                        <b>تتبع مزاياداتك</b>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                
                                                <td align="center" valign="BOTTOM" width="225">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="180">
                                                        <tr>
                                                            <td background="images/thkadbg.gif" height="26" align="center">
                                                                <div class="thanksadlink" align="CENTER">
                                                                    <a href="manage-auction-alert.php">
                                                                        <b>اشترك في تنبيهات المزايدات</b>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        
        <div>
            <br><br>
        </div>
        
        <!-- أحدث طلبات الشراء -->
        <div class="dph f1 dem dem2 mt12 boxh2" style="width:730px; display:none;" id="hhd">
            <h2>أحدث طلبات الشراء</h2>
            <div class="p75" id="buylead">
                <img src="images/sol.gif" alt="جاري التحميل..." border="0" height="16" width="16">
            </div>
        </div>
        
        <div align="center">
            <br>
        </div>
        
        <br><br><br>
    </div>
    
    <div class="c3">&nbsp;</div>
</div>

<!-- تذييل الصفحة -->
<?php include 'includes/footer.php'; ?>

<script type="text/javascript">
// تحميل أحدث طلبات الشراء (إذا لزم الأمر)
$(document).ready(function() {
    // يمكن إضافة كود AJAX هنا لتحميل أحدث طلبات الشراء
    console.log('صفحة تأكيد نشر المزايدة - PHP 8.3');
});
</script>
</body>
</html>