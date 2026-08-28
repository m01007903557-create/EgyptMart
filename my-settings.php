<?php
/**
 * File Name: my-settings.php
 * PHP Version: 8.3
 * Description: صفحة إعدادات الخصوصية والتنبيهات - نسخة مطورة ومتوافقة مع PHP 8.3
 */

declare(strict_types=1);

require_once "common.php";

// التحقق من الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['last_page'] = "my-settings.php";

// التحقق من وجود المستخدم في الجلسة
if (empty($_SESSION['uid_indm'] ?? null)) {
    header('Location: sign-in.php');
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
if (!isset($con) || !$con) {
    die('Database connection error');
}

// الحصول على إعدادات الموقع من قاعدة البيانات
$website_name = getWebsiteName() ?? 'الموقع';
$page_settings_4 = get_page_settings(4) ?? $website_name;

// دوال مساعدة لتحديث الإعدادات (يمكن تطويرها لاحقاً)
function updatePrivacySetting(int $uid, string $setting, bool $value, mysqli $con): bool {
    // يمكن إضافة منطق حفظ الإعدادات في قاعدة البيانات هنا
    return true;
}

function getPrivacySetting(int $uid, string $setting, mysqli $con): bool {
    // يمكن إضافة منطق قراءة الإعدادات من قاعدة البيانات هنا
    return true;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? 'إعدادات الخصوصية'); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <!-- CSS -->
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/ps-v-11.css" type="text/css" rel="stylesheet">

    <!-- JavaScript -->
    <script src="js/jquery-1.2.1.min.js"></script>
    
    <style>
        /* تحسينات إضافية للصفحة */
        .ps1 {
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #fff;
            position: relative;
            min-height: 60px;
        }
        .ps1 strong {
            color: #333;
            font-size: 14px;
        }
        .ps1 .ps2 {
            position: absolute;
            left: 15px;
            top: 15px;
        }
        .mp3w {
            font-weight: bold;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .enb {
            color: #28a745;
            font-weight: bold;
        }
        .dis {
            color: #dc3545;
            font-weight: bold;
        }
        .lod img {
            vertical-align: middle;
        }
        .fr {
            float: left;
        }
        .clb {
            clear: both;
        }
        input[type="checkbox"] {
            cursor: pointer;
            width: 16px;
            height: 16px;
        }
        table td {
            padding: 2px 5px;
        }
        .brm {
            margin-top: 15px;
        }
        .mp1 {
            margin-bottom: 20px;
        }
        .mp10 {
            margin-top: 10px;
        }
        .f12 {
            font-size: 12px;
        }
    </style>
    
    <script>
    /**
     * دوال JavaScript - متوافقة مع PHP 8.3
     */
    
    /**
     * تحديث إعداد الخصوصية
     */
    function myPrivacyUpdate(checkbox, loadId, enableId) {
        var value = checkbox.checked ? 1 : 0;
        var settingId = checkbox.value;
        
        // إظهار مؤشر التحميل
        document.getElementById(loadId).style.display = 'block';
        
        // تحديث النص
        var enableLabel = document.getElementById(enableId);
        if (checkbox.checked) {
            enableLabel.innerHTML = 'مفعل';
            enableLabel.className = 'enb';
        } else {
            enableLabel.innerHTML = 'غير مفعل';
            enableLabel.className = 'dis';
        }
        
        // إرسال الطلب (يمكن تفعيل هذا الجزء لاحقاً)
        /*
        $.get("ajax-file/update-privacy.php", {
            id: settingId,
            value: value,
            uid: <?php echo $uid; ?>
        }, function(data) {
            // إخفاء مؤشر التحميل بعد الاستجابة
            document.getElementById(loadId).style.display = 'none';
            
            if (data === 'success') {
                // تم التحديث بنجاح
            } else {
                alert('حدث خطأ أثناء تحديث الإعداد');
            }
        }).fail(function() {
            document.getElementById(loadId).style.display = 'none';
            alert('حدث خطأ في الاتصال');
        });
        */
        
        // إخفاء مؤشر التحميل بعد فترة (للعرض فقط)
        setTimeout(function() {
            document.getElementById(loadId).style.display = 'none';
        }, 500);
    }
    
    /**
     * تحديث إعداد تنبيهات طلبات الشراء (البريد الإلكتروني)
     */
    function showbuyleademailalert(checkbox, loadId, enableId) {
        myPrivacyUpdate(checkbox, loadId, enableId);
    }
    
    /**
     * تحديث إعداد تنبيهات طلبات الشراء (SMS)
     */
    function showbuyleadsmsalert(checkbox, loadId, enableId) {
        myPrivacyUpdate(checkbox, loadId, enableId);
    }
    
    /**
     * تحديث إعداد التنبيهات لعنصر معين
     */
    function updateAlert(settingId, type, checkbox) {
        var loadId = 'load_' + settingId + '_' + type;
        var enableId = 'enable_' + settingId + '_' + type;
        myPrivacyUpdate(checkbox, loadId, enableId);
    }
    
    $(document).ready(function() {
        // إضافة مستمعات للأحداث للخيارات
        $('input[type="checkbox"]').change(function() {
            // يمكن إضافة منطق إضافي هنا
        });
    });
    </script>
</head>
<body>

<!-- القسم الرئيسي -->
<div class="hm1 bbc" id="res-mob1">
    
    <!-- رأس الصفحة -->
    <?php include "includes/header_new.php"; ?>
    <br><br>
    
    <div class="bt">
        <img src="images/z.gif" alt="<?php echo htmlspecialchars($website_name); ?>" height="1" width="1">
    </div>

    <!-- قائمة التنقل العلوية -->
    <?php include "includes/header_menu.php"; ?>
    
    <!-- القائمة الجانبية -->
    <?php include 'includes/left_menu.php'; ?>
    
    <!-- المحتوى الرئيسي -->
    <div class="w56b f1 p2b p14 bl ps-ie7">
        
        <!-- عنوان الصفحة -->
        <div>
            <h1>إعدادات الخصوصية</h1>
        </div>

        <div>&nbsp;</div>
        
        <div class="mt5">
            <p class="f12">قم بتخصيص إعدادات حسابك لاستخدام <?php echo htmlspecialchars($website_name); ?></p>
            <div class="brm">
                
                <!-- قسم التنبيهات -->
                <div class="mp1">
                    <a name="Ale"></a>
                    <div class="mp2">
                        <div class="mp3w mp4">التنبيهات</div>
                    </div>

                    <div class="mp10" id="cd">
                        
                        <!-- رسائل الخدمة الهامة -->
                        <div class="ps1 ism f12" style="background-image:url(images/email_settings/service-message.png); background-repeat:no-repeat; background-position:10px 8px;">
                            <strong>رسائل الخدمة الهامة</strong><br>
                            إشعارات عضويتك في <?php echo htmlspecialchars($page_settings_4); ?> تساعدك في إدارة عملك بشكل أفضل. 
                            هذه الرسائل الحصرية إلزامية لإعلامك بحالة ترويج أعمالك على منصة <?php echo htmlspecialchars($page_settings_4); ?>.
                            <div class="ps2">
                                <table align="right" border="0" cellpadding="2" cellspacing="0" width="350">
                                    <tr>
                                        <td align="right" width="63"><span>البريد الإلكتروني</span>&nbsp;</td>
                                        <td align="left" width="98">إلزامي</td>
                                        <td align="right" width="47"><span>رسالة نصية</span></td>
                                        <td width="20">
                                            <input id="important_service_messages_sms" value="5" 
                                                   onclick="myPrivacyUpdate(this,'load_5','enable_5')" 
                                                   checked="checked" type="checkbox">
                                        </td>
                                        <td align="left" width="102">
                                            <div class="fr lod" id="load_5" style="display:none">
                                                <img src="images/loading.gif" alt="جاري التحميل" height="11" width="16">
                                            </div>
                                            <label class="enb" id="enable_5">مفعل</label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="clb"></div>
                        </div>

                        <!-- تنبيهات طلبات الشراء -->
                        <a name="buy_lead"></a><a name="leads"></a>
                        <div class="ps1 bla f12" style="background-image:url(images/email_settings/bulead_alerts.png); background-repeat:no-repeat; background-position:10px 8px;">
                            <strong>تنبيهات طلبات الشراء</strong><br>
                            تحكم في تنبيهات البريد الإلكتروني والرسائل النصية لطلبات الشراء المفضلة من جميع أنحاء العالم.
                            <div class="ps2">
                                <table align="right" border="0" cellpadding="1" cellspacing="0">
                                    <tr>
                                        <td align="right" width="38"><span>البريد الإلكتروني</span></td>
                                        <td align="left" width="23">
                                            <input id="buy_leads_alerts_email" value="20" 
                                                   onclick="showbuyleademailalert(this,'load_20','enable_20')" 
                                                   checked="checked" type="checkbox">
                                        </td>
                                        <td align="left" width="76">
                                            <div class="fr lod" id="load_20" style="display:none">
                                                <img src="images/loading.gif" alt="جاري التحميل" height="11" width="16">
                                            </div>
                                            <label class="enb" id="enable_20">مفعل</label>
                                        </td>
                                        <td align="right" width="51"><span>رسالة نصية</span></td>
                                        <td width="23">
                                            <input id="buy_leads_alerts_sms" value="19" 
                                                   onclick="showbuyleadsmsalert(this,'load_19','enable_19')" 
                                                   checked="checked" type="checkbox">
                                        </td>
                                        <td align="left" width="102">
                                            <div class="fr lod" id="load_19" style="display:none">
                                                <img src="images/loading.gif" alt="جاري التحميل" height="11" width="16">
                                            </div>
                                            <label class="enb" id="enable_19">مفعل</label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="clb"></div>
                        </div>

                        <!-- تنبيهات عروض البيع -->
                        <a name="selloffer"></a>
                        <div class="ps1 tl f12" style="background-image:url(images/email_settings/selloffer_alerts.png); background-repeat:no-repeat; background-position:10px 8px;">
                            <strong>تنبيهات عروض البيع</strong><br>
                            تتضمن تنبيهات البريد الإلكتروني لأحدث العروض التجارية في فئاتك المفضلة. يمكنك تفعيل/تعطيل تنبيهاتك أو إدارة الفئات المشترك فيها.
                            <div class="ps2">
                                <table align="right" border="0" cellpadding="1" cellspacing="0" width="460">
                                    <tr>
                                        <td align="right" width="38"><span>البريد الإلكتروني</span>&nbsp;</td>
                                        <td align="left" width="23">
                                            <input id="trade_lead_alerts_email" value="14" 
                                                   onclick="myPrivacyUpdate(this,'load_14','enable_14')" 
                                                   checked="checked" type="checkbox">
                                        </td>
                                        <td align="left" width="76">
                                            <div class="fr lod" id="load_14" style="display:none">
                                                <img src="images/loading.gif" alt="جاري التحميل" height="11" width="16">
                                            </div>
                                            <label class="enb" id="enable_14">مفعل</label>
                                        </td>
                                        <td align="right" width="51">&nbsp;</td>
                                        <td width="22">&nbsp;</td>
                                        <td align="left" width="102">&nbsp;</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="clb"></div>
                        </div>

                        <!-- النشرات الإخبارية -->
                        <a name="newsletters"></a>
                        <div class="ps1 ind f12" style="border-bottom:0px;">
                            <strong>النشرة الإخبارية للصناعة</strong><br>
                            يمكنك الاختيار من بين مجموعة من النشرات الإخبارية المتخصصة التي تغطي جميع الاتجاهات والفرص والأخبار الجديدة. 
                            يتم إرسال هذه النشرات إليك كل أسبوعين.
                            <div class="ps2">
                                <table align="right" border="0" cellpadding="1" cellspacing="0" width="460">
                                    <tr>
                                        <td align="right" width="38"><span>البريد الإلكتروني</span>&nbsp;</td>
                                        <td align="left" width="23">
                                            <input id="industry_newsletter_email" value="18" 
                                                   onclick="myPrivacyUpdate(this,'load_18','enable_18')" 
                                                   checked="checked" type="checkbox">
                                        </td>
                                        <td align="left" width="76">
                                            <div class="fr lod" id="load_18" style="display:none">
                                                <img src="images/loading.gif" alt="جاري التحميل" height="11" width="16">
                                            </div>
                                            <label class="enb" id="enable_18">مفعل</label>
                                        </td>
                                        <td align="right" width="51">&nbsp;</td>
                                        <td width="22">&nbsp;</td>
                                        <td align="left" width="102">&nbsp;</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="clb"></div>
                        </div>

                    </div>
                    <div class="clb"></div>
                </div>

                <!-- الاتصالات الترويجية -->
                <div class="mp1">
                    <div class="mp2">
                        <a name="Cmn"></a>
                        <div class="mp3w mp4">الاتصالات الترويجية</div>
                    </div>

                    <div class="mp10" id="cd">
                        <div class="ps1 usa f12">
                            <strong>أخبار وإعلانات خدمات <?php echo htmlspecialchars($website_name); ?></strong><br>
                            تتضمن تنبيهات حول عروض الخدمات المجانية أو المدفوعة الجديدة من <?php echo htmlspecialchars($page_settings_4); ?> 
                            والتي يوصى بها بشدة لنمو أعمالك.
                            <div class="ps2">
                                <table align="right" border="0" cellpadding="2" cellspacing="0" width="350">
                                    <tr>
                                        <td align="right" width="63"><span>البريد الإلكتروني</span>&nbsp;</td>
                                        <td align="left" width="98">إلزامي</td>
                                        <td align="right" width="47"><span>رسالة نصية</span></td>
                                        <td width="20">
                                            <input id="iupdates_service_announcements_sms" value="7" 
                                                   onclick="myPrivacyUpdate(this,'load_7','enable_7')" 
                                                   checked="checked" type="checkbox">
                                        </td>
                                        <td align="left" width="102">
                                            <div class="fr lod" id="load_7" style="display:none">
                                                <img src="images/loading.gif" alt="جاري التحميل" height="11" width="16">
                                            </div>
                                            <label class="enb" id="enable_7">مفعل</label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="clb"></div>
                        </div>
                    </div>
                    <div class="clb"></div>
                </div>

            </div>
        </div>
    </div>
    
    <div class="c3">&nbsp;</div>
</div>

<!-- تذييل الصفحة -->
<?php include 'includes/footer.php'; ?>

</body>
</html>