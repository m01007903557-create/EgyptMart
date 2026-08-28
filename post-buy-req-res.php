<?php
/**
 * File: post-buy-req-res.php
 * Description: صفحة تأكيد نشر طلب الشراء مع رسالة شكر وتعليمات للمستخدم
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

// التحقق من تسجيل الدخول
if ($uid == 0) {
    header("Location: sign-in.php");
    exit;
}

// الحصول على اسم المستخدم
$user_name = user_info($uid, 'name_prefix') . ' ' . user_info($uid, 'fname') . ' ' . user_info($uid, 'lname');
$user_name = htmlspecialchars(trim($user_name), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/tradeleads05.css" type="text/css" rel="stylesheet">
    
    <style>
        .thanksmsg {
            color: #333333;
            font-family: ms sans serif, arial;
            font-size: 13px;
            padding: 10px 0 0 5px !important;
            text-align: left;
        }
        .thanksmsg ul li { 
            padding-bottom: 0 0 5px 0 !important; 
            margin-left: 16px !important; 
            list-style-image: none !important;
        }
        .lf { text-align: left; }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
</head>
<body>
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" width="1" height="1"></div>
        
        <!-- Menu -->
        <?php include __DIR__ . "/includes/header_menu.php"; ?>
        
        <!-- القائمة الجانبية اليسرى -->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="ulid" class="nln1" style="margin:0px; padding:0px;">
                <li>
                    <h3 style="font-size:16px; font-weight:bold; color:#000; margin:0; padding:18px 5px 18px 5px; background-color:#FFFFFF;">
                        أدوات الشراء
                    </h3>
                </li>
                
                <li class="np npnew"><a href="post-buy-req.php">»&nbsp;أنشر أمر تسعيير</a></li>
                <li class="np npnew"><a href="manage-buy-requirement.php">»&nbsp;إدارة تسعييرات الشراء</a></li>
                <li class="np npnew"><a href="manage-selloffer-alert.php">»&nbsp;إدارة إشعارات عروض البيع</a></li>
                
                <li style="border-bottom:medium none; margin-top:40px;"><h2>ربما تحتاج أيضا</h2></li>
                <li class="np npnew"><a href="buyleads.php">رؤية أحدث طلبات الشراء</a></li>
                <li class="np npnew"><a href="manage-purchased-buyleads.php">طلبات الشراء المشتراه</a></li>
                <li class="np npnew"><a href="manage-buylead-alert.php">إدارة إشعارات طلبات شرائك</a></li>
            </ul>
        </div>
        
        <!-- المحتوى الرئيسي -->
        <div class="mctr1 mfl">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td valign="TOP" width="100%">
                            <div>
                                <div><img src="post-buy-req-res_files/zero.gif" height="5" width="1"><br></div>
                                
                                <table>
                                    <tbody>
                                        <tr>
                                            <td valign="TOP"><img src="post-buy-req-res_files/zero.gif" height="1" width="1"></td>
                                            <td valign="TOP" width="100%">
                                                <div><img src="post-buy-req-res_files/zero.gif" height="15" width="1"><br></div>
                                                
                                                <table class="lf mpl10" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                    <tbody>
                                                        <tr>
                                                            <td style="border-right:0px;" valign="top">
                                                                <div>
                                                                    <table style="border-collapse:collapse; border:1px solid #86CDFD;" align="CENTER" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td bgcolor="#E1F0FF">
                                                                                    <div class="thankscathead"><b>Dear <?php echo $user_name; ?></b></div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    
                                                                    <table class="lf" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="thanksmsg" width="100%">
                                                                                    <ul style="margin:0px; padding:0px;">
                                                                                        <img src="post-buy-req-res_files/zero.gif" height="2" width="1">
                                                                                        <li class="thanksmsg">شكرا لنشرك طلب شراء على إيجيبت مارت وأرابيوس دوت كوم</li>
                                                                                        <li class="thanksmsg">طلب شرائك سوف يتم نشره قريبا جدا وسوف تستلم تأكيد بالنشر على الميل الخاص بك</li>
                                                                                        <li class="thanksmsg">للحصول على إستجابة سريعة عليك تأكيد لينك التحقق المرسل على الايميل الخاص بك</li>
                                                                                        <li class="thanksmsg">سوف تستلم إستجابات لطلب تسعيرك على الميل الخاص بك وعلى موبايلك وعليك فحص بريدك بشكل منتظم</li>
                                                                                    </ul>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    
                                                                    <br>
                                                                    
                                                                    <table align="CENTER" border="0" cellpadding="2" cellspacing="0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td align="CENTER" height="35" valign="BOTTOM">
                                                                                    <div class="thanksadlink" style="background:#f4faff; border:1px solid #c2e6fe; border-radius:6px; padding:4px 8px;" align="CENTER">
                                                                                        <a href="post-buy-req.php"><b>قم بنشر المزيد من طلبات التسعير</b></a>
                                                                                    </div>
                                                                                </td>
                                                                                <td width="10px"></td>
                                                                                <td align="CENTER" valign="BOTTOM">
                                                                                    <div class="thanksadlink" style="background:#f4faff; border:1px solid #c2e6fe; border-radius:6px; padding:4px 8px;" align="CENTER">
                                                                                        <a href="manage-buy-requirement.php"><b>صفحة إدارة طلبات التسعير</b></a>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                
                                                <div></div>
                                                <div align="center"><br></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br><br>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>