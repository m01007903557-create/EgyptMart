<?php
ob_start();
require_once __DIR__ . '/common.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// تعريف $db إذا لم يكن معرفاً
global $db;
if (!isset($db) || $db === null) {
    global $con;
    $db = $con;
}

if (!$db) {
    die("خطأ: لا يوجد اتصال بقاعدة البيانات");
}

$_SESSION['last_page'] = "my-buylead-locationpref.php";
if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') {
    header("Location: sign-in.php");
    exit;
}
$uid = (int)$_SESSION['uid_indm'];

// تعريف الاتصال بقاعدة البيانات
global $con, $db;
if (!isset($db) || $db === null) {
    $db = $con;
}

class editPreference
{
    public $msg;
    public $usr_id;
    public $usr_br_prefLocation;
    
    function __construct($usr_id)
    {
        $this->usr_id = (int)$usr_id;
    }
    
    function detailsObj()
    {
        global $db;
        $sql = "SELECT * FROM user WHERE usr_id = " . $this->usr_id;
        $res = mysqli_query($db, $sql);
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_object($res);
        }
        return null;
    }
    
    function updatePref()
    {
        global $db;
        $sql = "UPDATE user 
                SET usr_br_prefLocation = '" . mysqli_real_escape_string($db, $this->usr_br_prefLocation) . "' 
                WHERE usr_id = " . $this->usr_id;
        mysqli_query($db, $sql) or die(mysqli_error($db));
    }
}

$obj_pref = new editPreference($uid);
$row_usr_lpref = $obj_pref->detailsObj();

if (isset($_POST['btnUpdate'])) {
    $obj_pref->usr_br_prefLocation = trim($_POST['usr_br_prefLocation'] ?? '');
    $obj_pref->updatePref();
    header("Location: my-buylead-locationpref.php");
    exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

<title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- css start -->
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my02.css" type="text/css" rel="stylesheet">
<link href="css/mng-trde-alrt.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->

<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
<script>
$(document).ready(function(){
    $(".ajax").colorbox();
    $(".inline").colorbox({inline:true, width:"50%"});
    $("#click").click(function(){ 
        $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
        return false;
    });
});
</script>
<script type="text/javascript">
function addAlertCategory()
{
    $.post("ajax-file/addBuyleadAlertCat.php",{}, function(data){ window.location.reload(); });
}
function delAlertCat(id)
{
    if(confirm("Are you sure to delete this Category?")){
        $.post("ajax-file/delBuyleadAlertCat.php",{id:id}, function(data){ window.location.reload(); });
    }
}
</script>
</head>
<body>

<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
    <br>
    <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
    <?php include "includes/header_menu.php"; ?>
    
    <div class="f1 w61n tb lh ml br" id="lnav">
        <ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
            <li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">طلبات وإستفسارات الشراء</h3></li> 
            <li style="border-bottom:none"><h3>الحصول على طلبات شراء</h3></li>
            <li class="np npnew"><a href="myproduct-sell.php">»&nbsp;سجل منتجات شركتك المعتادة</a></li>
            <li class="np npnew"><a href="manage-buylead-alert.php">»&nbsp;سجل أصناف شراء شركتك</a></li>
            <li class="np npnew"><a class="leftindi txtcol" href="my-buylead-locationpref.php">»&nbsp;حدد أماكن وروود إستفسارات الشراء</a></li>
            <li class="np npnew"><a href="manage-purchased-buyleads.php">»&nbsp;شاهد استفسارات شراء واردة</a></li>
            <li style="border-bottom: medium none;"><h3>الأسئلة المتكررة عن طلبات الشراء</h3></li>
            <li class="np npnew"><a href="help.php">»&nbsp;المساعدة لشرح طلبات الشراء</a></li>
            <li class="ug-banner">
            <?php
            global $db;
            $sql_adv = "SELECT * FROM advertisement WHERE adv_imagewidth='200' AND adv_imageheight='154' AND adv_status='1' ORDER BY RAND() LIMIT 1";
            $res_adv = mysqli_query($db, $sql_adv);
            if ($res_adv && mysqli_num_rows($res_adv) > 0) {
                $row_adv = mysqli_fetch_object($res_adv);
                echo '<a href="//' . htmlspecialchars($row_adv->adv_link, ENT_QUOTES, 'UTF-8') . '" target="_blank"><img src="upload/advertisement/' . htmlspecialchars($row_adv->adv_img, ENT_QUOTES, 'UTF-8') . '" width="200" height="154"/></a>';
            } else {
                echo '<img src="upload/advertisement/200-154-advertisement.png" alt="" border="0" height="154" width="200">';
            }
            ?>
            </li>
        </ul>
    </div>
    
    <div class="mctr mfl">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
        <tr><td id="id_attribute_value" valign="TOP" width="100%">
            <div style="position:absolute; background-image:url('images/bg_popup.png'); left:0px; margin-top:0px; top:0px; right:0px; width:100%; z-index:2000;" class="win-close" id="div_info" align="CENTER">
                <div id="divheight"></div>
                <table id="tableheight" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody><tr><td align="CENTER">
                    <div id="dynamicheight"></div>
                    <div class="bg_border_new" style="height:675px" id="dvh1">
                        <div style="background-color:#FFFFFF; height:670px" id="dvh2">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                            <tr>
                                <td bgcolor="#E6E6E6"><div class="myta">Manage Your Buy Lead Product Preference</div></td>
                                <td style="padding-right:7px;" align="RIGHT" bgcolor="#E6E6E6"><img style="cursor:pointer" src="images/q_clbtn.png" onclick="win_open_buy();" height="16" width="16"></td>
                            </tr>
                            </tbody>
                            </table>
                            <img src="images/zero.gif" height="10" width="1"><br>
                        </div>
                    </div>
                </td></tr>
                </tbody>
                </table>
            
            
            
            </div>
<div dir="rtl">
            <table style="table-layout:fixed;width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td style="border-right:0px; padding-right:10px" valign="top" width="100%">
                    <table style="table-layout:fixed;width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                    <tr>
                        <td align="LEFT" height="38" valign="TOP" width="325">
                            <img src="images/zero.gif" height="4" width="1"><br>
                            <nobr><div class="mf18 mc5 mta2 mpr8 mpt10" title="Location Preferences">حدد أماكن ورود إشعارات إستفسارات الشراء منها</div></nobr>
                        </td>
                        <td valign="bottom" style="text-align: left;><div class="manage_country mb" title="Manage Your Buylead Preferences"><a href="manage-buylead-alert.php">سجل مزيد من أصناف طلبات وإستفسارات الشراء</a></div></td>
                    </tr>
                    </tbody>
                    </table>
                    <div style="border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
                    
                    <div class="mclb mpr5">
                        <form name="savelocpref" id="savelocpref" method="post">
                            <div id="location">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                <tr>
                                <td id="prf_main" style="margin-right: 0px; *padding-top:0px" valign="top">
                                    <div style="font-size:24px; font-weight:bold;font-family:arial; width:98%;color:#fff; background:#024ca7; padding:10px 17px 5px 5px;margin-bottom:15px;text-transform:uppercase;border:solid 1px #024ca7; margin-right:0px;" class="boxbg bgi1" title="I want Buyers from :">أريد طلبات وإستفسارات شراء من الأماكن التالية</div>
                                    
                                    <div onmouseover="change_bg(1)" onmouseout="remove_bg(1)" style="margin-right:0px;" id="box_1" class="boxbg boxbg1 <?php if($row_usr_lpref && $row_usr_lpref->usr_br_prefLocation == 'any'){ ?>bgi1<?php } ?>" onclick="location_prf(1)">
                                        <label for="locationid_1" id="label_1">
                                            <input name="usr_br_prefLocation" value="any" id="usr_br_prefLocation_1" <?php if($row_usr_lpref && $row_usr_lpref->usr_br_prefLocation == 'any'){ ?>checked="checked"<?php } ?> type="radio">
                                            <span class="fs18 lc4 fwb" title="All Over the World (My City + My Country">من جميع أنحاء العالم من الداخل والخارج</span>
                                            <div class="pdl20 mrgn lc5" style="line-height:17px;padding-top:2px;" title="Selecting this option would mean you will receive buyers from all over the world including Your Country">
                                                : هذا الإختيار يعنى
                                                <br>
                                                <div class="mpt3 mf12 mc2 mlh16">هذا الإختيار يعنى أنك سوف تستقبل إستفسارات شراء من جميع العالم من داخل وخارج بلدك</div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div onmouseover="change_bg(2)" onmouseout="remove_bg(2)" onclick="location_prf(2)" id="box_2" class="boxbg1 <?php if($row_usr_lpref && $row_usr_lpref->usr_br_prefLocation == 'abroad'){ ?>bgi1<?php } ?>" style="float:left; width:32%; margin-right:12px;height:120px">
                                        <label for="locationid_2" id="label_2">
                                            <input name="usr_br_prefLocation" value="abroad" id="usr_br_prefLocation_2" <?php if($row_usr_lpref && $row_usr_lpref->usr_br_prefLocation == 'abroad'){ ?>checked="checked"<?php } ?> type="radio">
                                            <span class="fs18 lc4 fwb" title="Foreign Only">من الخارج فقط للتصدير<br><span style="padding-left:30px">(إستفسارات من الخارج فقط للتصدير)</span></span>
                                            <div class="pdl20 mrgn lc5" style="line-height:17px;padding-top:4px;">
                                                : هذا الإختيار يعنى
                                                <br>
                                                <div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
                                                    &bull; لن تستقبل إستفسارات شراء من بلدك<br><img src="images/zero.gif" height="6" width="1"><br>
                                                    &bull; لن تستقبل إستفسارات شراء من مدينتك
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div onmouseover="change_bg(3)" onmouseout="remove_bg(3)" id="box_3" style="float:left; width:32%; margin-right:12px;height:120px" class="boxbg1 <?php if($row_usr_lpref && $row_usr_lpref->usr_br_prefLocation == 'domestic'){ ?>bgi1<?php } ?>" onclick="location_prf(3)">
                                        <label for="locationid_3" id="label_3">
                                            <input name="usr_br_prefLocation" id="usr_br_prefLocation_3" value="domestic" <?php if($row_usr_lpref && $row_usr_lpref->usr_br_prefLocation == 'domestic'){ ?>checked="checked"<?php } ?> type="radio">
                                            <span class="fs18 lc4 fwb" title="My Country Only">من داخل بلدى فقط<br><span style="padding-left:30px">(من بلدى محليا فقط)</span></span>
                                            <div class="pdl20 mrgn lc5" style="line-height:17px;padding-top:4px;">
                                                : هذا الإختيار يعنى
                                                <br>
                                                <div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
                                                    &bull; لن تستقبل إستفسارات شراء من خارج بلدك<br><img src="images/zero.gif" height="6" width="1"><br>
                                                    &bull; لن تستقبل إستفسارات تصدير
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div onmouseover="change_bg(4)" onmouseout="remove_bg(4)" id="box_4" style="float:left; width:32%; margin-right:0px;height:120px" class="boxbg1 <?php if($row_usr_lpref && $row_usr_lpref->usr_br_prefLocation == 'my_city'){ ?>bgi1<?php } ?>" onclick="location_prf(4)">
                                        <label for="locationid_4" id="label_4">
                                            <input name="usr_br_prefLocation" id="usr_br_prefLocation_4" value="my_city" <?php if($row_usr_lpref && $row_usr_lpref->usr_br_prefLocation == 'my_city'){ ?>checked="checked"<?php } ?> type="radio">
                                            <span class="fs18 lc4 fwb" title="Local Area Only">من داخل مدينتى والمدن القريبة فقط<br><span style="padding-left:30px">(من مدينتى ومائتان كيلومتر من المدن القريبة)</span></span>
                                            <div class="pdl20 mrgn lc5" style="line-height:17px;padding-top:4px;">
                                                : هذا الإختيار يعنى:<br>
                                                <div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
                                                    &bull; لن تستقبل إستفسارات شراء لأكثر من مائتان كيلومتر حولك<br>
                                                    <img src="images/zero.gif" height="6" width="1"><br>
                                                    &bull; لن تستقبل إستفسارات شراء من خارج بلدك
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div style="clear:both"></div>
                                    
                                    <table style="border-collapse: collapse; margin-top: 10px; clear:both" border="1" bordercolor="#007af4" cellpadding="5" cellspacing="0" align="center">
                                    <tbody>
                                    <tr>
                                        <td style="padding: 8px;" bgcolor="#9fcfff" title="إحفظ - تعديلات - أماكن طلب طلبات الشراء الجاهزة"><input name="btnUpdate" value="إحفظ التعديلات" style="padding: 3px 8px; font-size: 18px;" type="submit"></td>
                                    </tr>
                                    </tbody>
                                    </table>
                                </td>
                                </tr>
                                </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div style="border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
                    <span id="subs_cats"></span> <span id="procssing"></span>
                    <input name="catid" id="catid" value="" type="hidden">
                    <div><br><br><br><br></div>
                </td>
                <td style="border-right:0px;" valign="top"><img src="images/gray-line.gif" height="1" width="1"></td>
            </tr>
            </tbody>
            </table>
            <div style="clear:both"><br></div>
        </td></tr>
        </tbody>
        </table>
    </div>
    <div class="c3">&nbsp;</div>
</div>
<?php include 'includes/footer.php'; ?>