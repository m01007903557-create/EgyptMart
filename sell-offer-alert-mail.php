<?php
/**
 * File: email/sell-offer-alert-mail.php

 * Description: قالب البريد الإلكتروني لتنبيهات عروض البيع
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

global $con;

// التحقق من وجود المعاملات المطلوبة
if (!isset($_GET['so']) || !isset($_GET['u'])) {
    die("Invalid request parameters");
}

$so_id = (int)$_GET['so'];
$user_id = (int)$_GET['u'];

// جلب بيانات عرض البيع
$sql_so = "SELECT so.*, u.* 
           FROM sale_offer so
           INNER JOIN user u ON so.so_usr_id = u.usr_id
           WHERE so.so_id = ? 
           LIMIT 1";

$stmt_so = mysqli_prepare($con, $sql_so);
mysqli_stmt_bind_param($stmt_so, 'i', $so_id);
mysqli_stmt_execute($stmt_so);
$result_so = mysqli_stmt_get_result($stmt_so);
$row_so = mysqli_fetch_object($result_so);
mysqli_stmt_close($stmt_so);

if (!$row_so) {
    die("Sale offer not found");
}

// جلب بيانات المستخدم المستهدف
$sql_user = "SELECT * FROM user WHERE usr_id = ? LIMIT 1";
$stmt_user = mysqli_prepare($con, $sql_user);
mysqli_stmt_bind_param($stmt_user, 'i', $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$row_user = mysqli_fetch_object($result_user);
mysqli_stmt_close($stmt_user);

if (!$row_user) {
    die("User not found");
}

// تنظيف البيانات للعرض
$so_service = htmlspecialchars($row_so->so_service ?? '', ENT_QUOTES, 'UTF-8');
$so_pic = !empty($row_so->so_pic) ? htmlspecialchars($row_so->so_pic, ENT_QUOTES, 'UTF-8') : 'no-image.png';
$so_id_enc = rand(1000, 9999) . md5((string)$row_so->so_id);

$user_name = htmlspecialchars(
    trim(($row_user->name_prefix ?? '') . ' ' . ($row_user->fname ?? '') . ' ' . ($row_user->lname ?? '')),
    ENT_QUOTES, 
    'UTF-8'
);

$company_name = htmlspecialchars(user_info($row_so->so_usr_id, 'bnsprof_compname'), ENT_QUOTES, 'UTF-8');
$city_name = htmlspecialchars(user_info($row_so->so_usr_id, 'bnsprof_city'), ENT_QUOTES, 'UTF-8');
$country_name = htmlspecialchars(get_country_name((int)($row_so->country ?? 0)), ENT_QUOTES, 'UTF-8');

$site_name = htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8');
$site_logo = isset($_SESSION['HTTP_HOST']) ? $_SESSION['HTTP_HOST'] . "/sitelogo/" . getSiteLogo() : '';
$site_url = $_SESSION['HTTP_HOST'] ?? '';
$details_url = $site_url . "/saleoffer-details.php?id=" . $so_id_enc;
$manage_url = $site_url . "/manage-selloffer-alert.php";
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض بيع جديد</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, Helvetica, sans-serif;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="680" style="border-collapse:collapse;">
        <!-- Header -->
        <tr>
            <td style="padding-top:10px; border-bottom:1px solid #bdd0f2;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                    <tr>
                        <td style="padding-bottom:5px;" valign="middle" width="32%">
                            <a rel="nofollow" href="index.php" target="_blank">
                                <img src="<?php echo htmlspecialchars($site_logo, ENT_QUOTES, 'UTF-8'); ?>" 
                                     alt="<?php echo $site_name; ?>" style="margin:0 20px 0px 0;" border="0">
                            </a>
                        </td>
                        <td style="font-family:'Trebuchet MS'; font-size:13px; text-align:center;" valign="middle" width="36%">
                            <b>أحدث عرض بيع<br>
                            <span style="font-size:18px;">طبقا لإهتمامك</span></b>
                        </td>
                        <td style="padding:7px 5px 10px 0; font-size:13px;" align="right" valign="middle" width="32%">
                            <b><?php echo date("l, F d, Y"); ?></b>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <!-- التحية -->
        <tr>
            <td style="color:#7e7e7f; padding:15px 5px 15px 0; line-height:16px;">
                <b>Dear <?php echo $user_name; ?>,</b><br><br>
                : أحدث عرض بيع خاص طبقا لإهتمامك
            </td>
        </tr>
        
        <!-- محتوى عرض البيع -->
        <tr>
            <td>
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="680" style="border-collapse:collapse;">
                    <tr>
                        <td style="vertical-align:top;" width="680">
                            <div style="width:95%; overflow:hidden; background-color:#f3f3f3; border-top:1px solid #e12400; padding:2px 2px 12px; min-height:175px; line-height:normal;">
                                
                                <!-- عنوان العرض -->
                                <div style="margin:0 0 5px 0; padding:0; min-height:26px;">
                                    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                        <tr>
                                            <td style="width:210px; text-align:left;" align="left">
                                                <a href="<?php echo htmlspecialchars($details_url, ENT_QUOTES, 'UTF-8'); ?>" 
                                                   style="color:#0000ff; font-family:Arial; font-size:13px; line-height:15px; word-wrap:break-word;" 
                                                   target="_blank">
                                                    <b><?php echo $so_service; ?></b>
                                                </a>
                                            </td>
                                            <td style="text-align:right; width:100px;" align="right">
                                                <div style="margin-left:3px;"></div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <!-- صورة العرض وتفاصيله -->
                                <table style="border-collapse:collapse;">
                                    <tr>
                                        <td style="list-style:none; line-height:normal; vertical-align:top; width:47%;">
                                            <div style="line-height:normal; border:4px solid #aaa; vertical-align:middle; min-height:125px; width:auto; background-color:#fff;">
                                                <a href="<?php echo htmlspecialchars($details_url, ENT_QUOTES, 'UTF-8'); ?>" 
                                                   style="text-decoration:none; line-height:normal;" target="_blank">
                                                    <table style="line-height:normal; border-collapse:collapse;">
                                                        <tr>
                                                            <td style="vertical-align:middle; width:125px; word-wrap:break-word; height:125px; background-color:#fff; line-height:normal;" align="center">
                                                                <img src="<?php echo htmlspecialchars($site_url . "/upload/sale_offer/" . $so_pic, ENT_QUOTES, 'UTF-8'); ?>" 
                                                                     alt="<?php echo $so_service; ?>" style="line-height:normal; margin:0; padding:0;" border="0">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </a>
                                            </div>
                                        </td>
                                        
                                        <td style="list-style:none; width:53%; line-height:normal; vertical-align:top;">
                                            <div style="line-height:14px; font-size:13px; font-family:Arial; word-wrap:break-word; font-weight:700; padding:5px 0px 0px 2px; margin:0px;">
                                                <?php echo $company_name; ?>
                                            </div>
                                            
                                            <div style="line-height:14px; font-size:12px; color:#3b3b3b; font-weight:700; margin:0; padding:5px 0 0 2px; font-family:Arial;">
                                                Location:&nbsp;
                                                <span style="font-weight:normal; word-wrap:break-word;">
                                                    <?php echo $city_name; ?><br>[<?php echo $country_name; ?>]
                                                </span>
                                            </div>
                                            
                                            <br>
                                            
                                            <!-- زر إرسال الاستفسار -->
                                            <div style="line-height:normal; margin:0; padding:0; 
                                                        background:#f75b16; border:1px solid #bf5305;
                                                        background:-moz-linear-gradient(top, #f77219 1%, #fec6a7 3%, #f77219 7%, #f75b16 100%);
                                                        background:-webkit-linear-gradient(top, #f77219 1%, #fec6a7 3%, #f77219 7%, #f75b16 100%);
                                                        background:-o-linear-gradient(top, #f77219 1%, #fec6a7 3%, #f77219 7%, #f75b16 100%);
                                                        background:-ms-linear-gradient(top, #f77219 1%, #fec6a7 3%, #f77219 7%, #f75b16 100%);
                                                        background:linear-gradient(to bottom, #f77219 1%, #fec6a7 3%, #f77219 7%, #f75b16 100%);
                                                        width:122px; min-height:32px; text-align:center;">
                                                <a href="<?php echo htmlspecialchars($details_url, ENT_QUOTES, 'UTF-8'); ?>" 
                                                   style="color:#fff; padding:8px 0px; display:block; font-family:Arial,Helvetica,sans-serif; font-size:14px; font-weight:bold; line-height:normal; text-decoration:none; text-align:center;" 
                                                   target="_blank">Send Enquiry</a>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <!-- Footer -->
        <tr>
            <td>
                <table align="left" width="668" style="border-collapse:collapse;">
                    <tr>
                        <td style="padding:10px 5px; font-size:10px; color:#888888; background-color:#ebebeb;">
                            You have received this email by virtue of your opt-in subscription for sell offers alert on 
                            <span style="color:#4163a2; text-decoration:underline;">
                                <a href="<?php echo htmlspecialchars($site_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                    <?php echo $site_name; ?>
                                </a>
                            </span> 
                            <br>
                            <a href="<?php echo htmlspecialchars($manage_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                إضغط هنا
                            </a> عند رغبتك تغيير إشعارات عروض البيع الخاصة بإهتمامك
                            <br><br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>