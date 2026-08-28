<?php
/**
 * File: post-sell-offer-res.php
 * Description: صفحة تأكيد نشر عرض البيع مع رسالة شكر وتعليمات للمستخدم
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
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/tradeleads05.css" type="text/css" rel="stylesheet">
    <link href="css/pdash.css" type="text/css" rel="stylesheet">
    
    <style>
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
            <?php include __DIR__ . "/includes/seller-tools-panel.php"; ?>
        </div>
        
        <!-- المحتوى الرئيسي -->
        <div class="w57 b1_m2 f1 blr p2b b1_m2">
            <div>
                <table style="align:center;">
                    <tbody>
                        <tr>
                            <td valign="TOP"><img src="post-sell-offer-res_files/zero.gif" height="1" width="1"></td>
                            <td valign="TOP" width="100%">
                                <div><img src="images/zero.gif" height="15" width="1"><br></div>
                                
                                <table class="lf" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tbody>
                                        <tr>
                                            <td style="border-right:0px;" valign="top">
                                                <div>
                                                    <table style="border-collapse:collapse;" align="CENTER" border="1" bordercolor="#86CDFD" cellpadding="0" cellspacing="0" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td bgcolor="#E1F0FF">
                                                                    <div class="thankscathead">
                                                                        <b>السيد / السيدة : <?php echo $user_name; ?></b>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td class="thanksmsg" width="100%">
                                                                    <ul style="margin-bottom:0px; margin-top:0px;">
                                                                        <li class="thanksmsg">
                                                                            تم إرسال عرض البيع المقدم منك للموافقة عن طريق الأدمن 
                                                                            <b><font color="#BF0000">بنجاح</font></b>.
                                                                        </li>
                                                                        <li class="thanksmsg">
                                                                            سوف يتم نشر عرض البيع المقدم منك  
                                                                            <b><font color="#BF0000">خلال يومين عمل</font></b> بعد مراجعة الأدمن للعرض
                                                                        </li>
                                                                        <div align="center">
                                                                            <font color="#BF0000" face="arial" size="-2">
                                                                                <a href="post-sell-offer.php">... إضغط هنا للإستمرار</a>
                                                                            </font>
                                                                        </div>
                                                                    </ul>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    
                                                    <table align="CENTER" border="0" cellpadding="2" cellspacing="0" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td align="CENTER" height="35" valign="BOTTOM" width="225">
                                                                    <table border="0" cellpadding="0" cellspacing="0" width="165">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td background="images/thkadbg2.gif" height="26">
                                                                                    <div class="thanksadlink" align="CENTER">
                                                                                        <a href="post-sell-offer.php"><b>أنشر المزيد من عروض البيع</b></a>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                                
                                                                <td align="CENTER" valign="BOTTOM" width="225">
                                                                    <table border="0" cellpadding="0" cellspacing="0" width="180">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td background="images/thkadbg1.gif" height="26">
                                                                                    <div class="thanksadlink" align="CENTER">
                                                                                        <a href="manage-sell-offer.php"><b>شاهد كل عروض بيعك المقدمة</b></a>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                                
                                                                <td align="center" valign="BOTTOM" width="225">
                                                                    <table border="0" cellpadding="0" cellspacing="0" width="180">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td background="images/thkadbg.gif" height="26" align="center">
                                                                                    <div class="thanksadlink" align="CENTER">
                                                                                        <a href="manage-buylead-alert.php"><b>أكتب أصناف إشعارت طلبات شراء</b></a>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <div><br><br></div>
                                
                                <!-- أحدث طلبات الشراء (مخفية) -->
                                <div class="dph f1 dem dem2 mt12 boxh2" style="width:730px; display:none;" id="hhd">
                                    <h2>Latest Buy Leads</h2>
                                    <div class="p75" id="buylead">
                                        <img src="images/sol.gif" alt="Loading..." border="0" height="16" width="16">
                                    </div>
                                </div>
                                
                                <div align="center"><br></div>
                                <br><br><br>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>