<?php
/**
 * File Name: my-tender-locationpref.php

 * PHP Version: 8.3
 * Description: صفحة إدارة تفضيلات موقع المناقصات - نسخة مطورة ومتوافقة مع PHP 8.3
 */

declare(strict_types=1);

ob_start();
require_once "common.php";

// التحقق من الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['last_page'] = "my-tender-locationpref.php";

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

/**
 * Class EditPreference
 * كلاس إدارة تفضيلات موقع المناقصات
 */
class EditPreference
{
    private string $msg = '';
    private int $usr_id;
    public string $usr_tnd_prefLocation = '';
    private mysqli $db;

    public function __construct(int $usr_id, mysqli $db)
    {
        $this->usr_id = $usr_id;
        $this->db = $db;
    }

    /**
     * الحصول على تفاصيل المستخدم
     */
    public function getDetails(): ?object
    {
        $sql = "SELECT * FROM user WHERE usr_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $this->usr_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_object();
        $stmt->close();
        return $row;
    }

    /**
     * تحديث تفضيل الموقع
     */
    public function updatePref(): bool
    {
        $sql = "UPDATE user SET usr_tnd_prefLocation = ? WHERE usr_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('si', $this->usr_tnd_prefLocation, $this->usr_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getMessage(): string
    {
        return $this->msg;
    }
}

// إنشاء كائن التفضيلات
$obj_pref = new EditPreference($uid, $con);
$row_usr_lpref = $obj_pref->getDetails();

// معالجة تحديث التفضيلات
if (isset($_POST['btnUpdate'])) {
    $obj_pref->usr_tnd_prefLocation = trim($_POST['usr_tnd_prefLocation'] ?? '');
    
    if (!empty($obj_pref->usr_tnd_prefLocation)) {
        $obj_pref->updatePref();
    }
    
    header("Location: my-tender-locationpref.php");
    exit;
}

// جلب إعلان عشوائي
$sql_adv = "SELECT adv_link, adv_img FROM advertisement 
            WHERE adv_imagewidth = '200' AND adv_imageheight = '154' 
            AND adv_status = '1' 
            ORDER BY RAND() 
            LIMIT 1";
$result_adv = $con->query($sql_adv);
$advertisement = null;
if ($result_adv && $result_adv->num_rows > 0) {
    $advertisement = $result_adv->fetch_object();
}

// دالة مساعدة للحصول على اسم الدولة (معلقة حالياً)
function get_country_name($country_id) {
    // يمكن إضافة منطق جلب اسم الدولة هنا إذا لزم الأمر
    return "";
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? 'تفضيلات موقع المناقصات'); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <!-- CSS -->
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my02.css" type="text/css" rel="stylesheet">
    <link href="css/mng-trde-alrt.css" type="text/css" rel="stylesheet">
    <link href="css/colorbox.css" type="text/css" rel="stylesheet">

    <!-- JavaScript -->
    <script src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <style>
        .bgi1 {
            background-color: #024ca7 !important;
            color: #fff !important;
        }
        .bgi1 .lc4, .bgi1 .lc5, .bgi1 .mc2 {
            color: #fff !important;
        }
        .boxbg1 {
            padding: 15px;
            border: 1px solid #024ca7;
            border-radius: 5px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .boxbg1:hover {
            background-color: #f0f7ff;
        }
        .fs18 {
            font-size: 18px;
        }
        .fwb {
            font-weight: bold;
        }
        .lc4 {
            color: #024ca7;
        }
        .lc5 {
            color: #333;
        }
        .mc2 {
            color: #666;
        }
        .pdl20 {
            padding-left: 20px;
        }
        .mrgn {
            margin-top: 5px;
        }
        .mpt3 {
            margin-top: 3px;
        }
        .mf12 {
            font-size: 12px;
        }
        .mlh16 {
            line-height: 16px;
        }
        input[type="radio"] {
            margin-left: 10px;
        }
    </style>
    
    <script>
    $(document).ready(function(){
        $(".ajax").colorbox();
        $(".inline").colorbox({inline:true, width:"50%"});
        $("#click").click(function(){ 
            $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
            return false;
        });
    });

    function change_bg(boxid) {
        var element = document.getElementById('box_' + boxid);
        if (element && !element.classList.contains('bgi1')) {
            element.style.backgroundColor = '#f0f7ff';
        }
    }

    function remove_bg(boxid) {
        var element = document.getElementById('box_' + boxid);
        if (element && !element.classList.contains('bgi1')) {
            element.style.backgroundColor = '';
        }
    }

    function location_prf(boxid) {
        // إزالة التحديد من جميع الصناديق
        for (var i = 1; i <= 4; i++) {
            var box = document.getElementById('box_' + i);
            if (box) {
                box.classList.remove('bgi1');
            }
        }
        
        // تحديد الصندوق المختار
        var selectedBox = document.getElementById('box_' + boxid);
        if (selectedBox) {
            selectedBox.classList.add('bgi1');
        }
        
        // تحديد الراديو المقابل
        var radio = document.getElementById('usr_tnd_prefLocation_' + boxid);
        if (radio) {
            radio.checked = true;
        }
    }

    function win_open_buy() {
        document.getElementById('div_info').style.display = 'none';
    }
    </script>
</head>
<body>

<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
    
    <br><br>
    <div class="bt">
        <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1">
    </div>

    <?php include "includes/header_menu.php"; ?>
    
    <!-- القائمة الجانبية -->
    <div class="f1 w61n tb lh ml br" id="lnav">
        <ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
            <li><h3 style="font-size: 16px; font-weight: bold; color:#000; margin:0; padding: 18px 5px 18px 5px; background-color: #FFFFFF;">المناقصات</h3></li> 
            <li style="border-bottom:none"><h3>مشتريات المناقصات</h3></li>
            <li class="np npnew"><a href="manage-purchased-tenders.php">»&nbsp;المناقصات المشتراة</a></li>
            <li class="np npnew"><a href="manage-tender-alert.php">»&nbsp;تنبيهات المناقصات</a></li>
            <li class="np npnew"><a class="leftindi txtcol" href="my-tender-locationpref.php">»&nbsp;تفضيلات الموقع</a></li>
            <li class="np npnew"><a href="transaction_history.php">»&nbsp;سجل المعاملات</a></li>
            <li style="border-bottom: medium none;"><h3>مساعدة / أسئلة شائعة</h3></li>
            <li class="np npnew"><a href="help.php">»&nbsp;مساعدة المناقصات / أسئلة شائعة</a></li>
            <li class="ug-banner">
                <?php if ($advertisement): ?>
                <a href="//<?php echo htmlspecialchars($advertisement->adv_link ?? ''); ?>" target="_blank" rel="noopener noreferrer">
                    <img src="upload/advertisement/<?php echo htmlspecialchars($advertisement->adv_img ?? ''); ?>" 
                         width="200" height="154" alt="إعلان">
                </a>
                <?php else: ?>
                <img src="upload/advertisement/200-154-advertisement.png" alt="" border="0" height="154" width="200">
                <?php endif; ?>
            </li>
        </ul>
    </div>
    
    <!-- المحتوى الرئيسي -->
    <div class="mctr mfl">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td id="id_attribute_value" valign="TOP" width="100%">
                    
                    <!-- نافذة منبثقة (مخفية) -->
                    <div style="position:absolute; background-image:url('images/bg_popup.png'); left:0px; margin-top:0px; top:0px; right:0px; width:100%; z-index:2000; display:none;" 
                         class="win-close" id="div_info" align="CENTER">
                        <div id="divheight"></div>
                        <table id="tableheight" border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="CENTER">
                                    <div id="dynamicheight"></div>
                                    <div class="bg_border_new" style="height:675px" id="dvh1">
                                        <div style="background-color:#FFFFFF; height:670px" id="dvh2">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td bgcolor="#E6E6E6">
                                                        <div class="myta">إدارة تفضيلات المناقصات</div>
                                                    </td>
                                                    <td style="padding-right:7px;" align="RIGHT" bgcolor="#E6E6E6">
                                                        <img style="cursor:pointer" src="images/q_clbtn.png" 
                                                             onclick="win_open_buy();" height="16" width="16">
                                                    </td>
                                                </tr>
                                            </table>
                                            <img src="images/zero.gif" height="10" width="1"><br>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table style="table-layout:fixed; width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="border-right:0px; padding-right:10px" valign="top" width="100%">
                                <table style="table-layout:fixed; width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="LEFT" height="38" valign="TOP" width="325">
                                            <img src="images/zero.gif" height="4" width="1"><br>
                                            <nobr>
                                                <div class="mf18 mc5 mta2 mpr8 mpt10">
                                                    تفضيلات الموقع
                                                </div>
                                            </nobr>
                                        </td>
                                        <td valign="bottom"></td>
                                        <td valign="bottom">
                                            <div class="manage_country mb">
                                                <a href="manage-tender-alert.php">إدارة تفضيلات المناقصات</a>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                
                                <div style="border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
                                
                                <!-- نموذج تفضيلات الموقع -->
                                <div class="mclb mpr5">
                                    <form name="savelocpref" id="savelocpref" method="post">
                                        <div id="location">
                                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <tr>
                                                    <td id="prf_main" style="margin-right: 0px; *padding-top:0px" valign="top">
                                                        
                                                        <div style="font-size:24px; font-weight:bold; font-family:arial; width:98%; color:#fff; background:#024ca7; padding:10px 17px 5px 5px; margin-bottom:15px; text-transform:uppercase; border:solid 1px #024ca7; margin-right:0px;" 
                                                             class="boxbg bgi1">
                                                            أريد مناقصات من
                                                        </div>
                                                        
                                                        <!-- الخيار 1: جميع أنحاء العالم -->
                                                        <div onmouseover="change_bg(1)" onmouseout="remove_bg(1)" 
                                                             style="margin-right:0px;" id="box_1" 
                                                             class="boxbg1 <?php echo ($row_usr_lpref->usr_tnd_prefLocation ?? '') == 'any' ? 'bgi1' : ''; ?>" 
                                                             onclick="location_prf(1)">
                                                            <label for="locationid_1" id="label_1">
                                                                <input name="usr_tnd_prefLocation" value="any" 
                                                                       id="usr_tnd_prefLocation_1" 
                                                                       <?php echo ($row_usr_lpref->usr_tnd_prefLocation ?? '') == 'any' ? 'checked="checked"' : ''; ?> 
                                                                       type="radio">
                                                                <span class="fs18 lc4 fwb">جميع أنحاء العالم (مدينتي + بلدي + التصدير)</span>
                                                                <div class="pdl20 mrgn lc5" style="line-height:17px; padding-top:2px;">
                                                                    اختيار هذا الخيار يعني أنك ستتلقى مناقصات من جميع أنحاء العالم بما في ذلك بلدك والخارج.
                                                                    <br>
                                                                    <div class="mpt3 mf12 mc2 mlh16">
                                                                        هذا يعني أنك تمارس عملك على مستوى العالم محلياً ودولياً.
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <!-- الخيار 2: الخارج فقط -->
                                                        <div onmouseover="change_bg(2)" onmouseout="remove_bg(2)" 
                                                             onclick="location_prf(2)" id="box_2" 
                                                             class="boxbg1 <?php echo ($row_usr_lpref->usr_tnd_prefLocation ?? '') == 'abroad' ? 'bgi1' : ''; ?>" 
                                                             style="float:left; width:32%; margin-right:12px; height:120px">
                                                            <label for="locationid_2" id="label_2">
                                                                <input name="usr_tnd_prefLocation" value="abroad" 
                                                                       id="usr_tnd_prefLocation_2" 
                                                                       <?php echo ($row_usr_lpref->usr_tnd_prefLocation ?? '') == 'abroad' ? 'checked="checked"' : ''; ?> 
                                                                       type="radio">
                                                                <span class="fs18 lc4 fwb">الخارج فقط<br><span style="padding-left:30px">(تصدير فقط)</span></span>
                                                                <div class="pdl20 mrgn lc5" style="line-height:17px; padding-top:4px;">
                                                                    اختيار هذا الخيار يعني:
                                                                    <br>
                                                                    <div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
                                                                        &bull; لا توجد مناقصات من بلدك<br><img src="images/zero.gif" height="6" width="1"><br>
                                                                        &bull; لا توجد مناقصات من مدينتك
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <!-- الخيار 3: بلدي فقط -->
                                                        <div onmouseover="change_bg(3)" onmouseout="remove_bg(3)" 
                                                             id="box_3" style="float:left; width:32%; margin-right:12px; height:120px" 
                                                             class="boxbg1 <?php echo ($row_usr_lpref->usr_tnd_prefLocation ?? '') == 'domestic' ? 'bgi1' : ''; ?>" 
                                                             onclick="location_prf(3)">
                                                            <label for="locationid_3" id="label_3">
                                                                <input name="usr_tnd_prefLocation" id="usr_tnd_prefLocation_3" 
                                                                       value="domestic" 
                                                                       <?php echo ($row_usr_lpref->usr_tnd_prefLocation ?? '') == 'domestic' ? 'checked="checked"' : ''; ?> 
                                                                       type="radio">
                                                                <span class="fs18 lc4 fwb">بلدي فقط<br><span style="padding-left:30px">(محلي فقط)</span></span>
                                                                <div class="pdl20 mrgn lc5" style="line-height:17px; padding-top:4px;">
                                                                    اختيار هذا الخيار يعني:
                                                                    <br>
                                                                    <div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
                                                                        &bull; لا توجد مناقصات من خارج بلدك<br><img src="images/zero.gif" height="6" width="1"><br>
                                                                        &bull; لا توجد استفسارات تصدير
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <!-- الخيار 4: المنطقة المحلية فقط -->
                                                        <div onmouseover="change_bg(4)" onmouseout="remove_bg(4)" 
                                                             id="box_4" style="float:left; width:32%; margin-right:0px; height:120px" 
                                                             class="boxbg1 <?php echo ($row_usr_lpref->usr_tnd_prefLocation ?? '') == 'my_city' ? 'bgi1' : ''; ?>" 
                                                             onclick="location_prf(4)">
                                                            <label for="locationid_4" id="label_4">
                                                                <input name="usr_tnd_prefLocation" id="usr_tnd_prefLocation_4" 
                                                                       value="my_city" 
                                                                       <?php echo ($row_usr_lpref->usr_tnd_prefLocation ?? '') == 'my_city' ? 'checked="checked"' : ''; ?> 
                                                                       type="radio">
                                                                <span class="fs18 lc4 fwb">المنطقة المحلية فقط<br><span style="padding-left:30px">(مدينتي و 250 كم حولها)</span></span>
                                                                <div class="pdl20 mrgn lc5" style="line-height:17px; padding-top:4px;">
                                                                    اختيار هذا الخيار يعني:<br>
                                                                    <div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
                                                                        &bull; لا توجد مناقصات خارج 250 كم من مدينتك<br>
                                                                        <img src="images/zero.gif" height="6" width="1"><br>
                                                                        &bull; لا توجد مناقصات من خارج بلدك
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>

                                                        <div style="clear:both"></div>

                                                        <table style="border-collapse: collapse; margin-top: 10px; clear:both" 
                                                               border="1" bordercolor="#007af4" cellpadding="5" cellspacing="0" align="center">
                                                            <tr>
                                                                <td style="padding: 8px;" bgcolor="#9fcfff">
                                                                    <input name="btnUpdate" value="حفظ التغييرات" 
                                                                           style="padding: 3px 8px; font-size: 18px; cursor: pointer;" 
                                                                           type="submit">
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </form>
                                </div>
                                
                                <div style="border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
                                
                                <span id="subs_cats"></span>
                                <span id="procssing"></span>
                            </td>
                            <td><img src="images/zero.gif" height="2" width="2"></td>
                        </tr>
                        <input name="catid" id="catid" value="" type="hidden">
                    </table>
                    
                    <div><br><br><br><br></div>
                </td>
                <td style="border-right:0px;" valign="top"><img src="images/gray-line.gif" height="1" width="1"></td>
            </tr>
        </table>
        
        <div style="clear:both"><br></div>
    </div>
    
    <div class="c3">&nbsp;</div>
</div>

<!-- تذييل الصفحة -->
<?php include 'includes/footer.php'; ?>
</body>
</html>