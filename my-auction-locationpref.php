<?php
ob_start();
include "common.php";

$_SESSION['last_page'] = "my-auction-locationpref.php";

if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '')
{
    header("Location: sign-in.php");
    exit();
}

$uid = (int)$_SESSION['uid_indm'];

class editPreference
{
    public $msg;
    public $usr_id;
    public $usr_auc_prefLocation;
    private $con;
    
    public function __construct($usr_id)
    {
        global $con;
        $this->con = $con;
        $this->usr_id = (int)$usr_id;
    }
    
    public function detailsObj()
    {
        $sql = "SELECT * FROM user WHERE usr_id = " . $this->usr_id;
        $result = mysqli_query($this->con, $sql);
        
        if (!$result) {
            die("خطأ في الاستعلام: " . mysqli_error($this->con));
        }
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_object($result);
        }
        return null;
    }
    
    public function updatePref()
    {
        $sql = "UPDATE user
                SET usr_auc_prefLocation = '" . mysqli_real_escape_string($this->con, $this->usr_auc_prefLocation) . "'
                WHERE usr_id = " . $this->usr_id;
        
        mysqli_query($this->con, $sql) or die("Database error: " . mysqli_error($this->con));
    }
}

$obj_pref = new editPreference($uid);
$row_usr_lpref = $obj_pref->detailsObj();

if (isset($_POST['btnUpdate']))
{
    $obj_pref->usr_auc_prefLocation = trim($_POST['usr_auc_prefLocation'] ?? '');
    $obj_pref->updatePref();
    
    header("Location: my-auction-locationpref.php");
    exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- css start -->
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my02.css" type="text/css" rel="stylesheet">
<link href="css/mng-trde-alrt.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->

<!-- inline script/js start -->
    <!-- Validate logged in user code ends HERE-->
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
<script>
    $(document).ready(function(){
        //Examples of how to assign the ColorBox event to elements
                    
        $(".ajax").colorbox();
        $(".inline").colorbox({inline:true, width:"50%"});
        //Example of preserving a JavaScript event for inline calls.
        $("#click").click(function(){ 
            $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
            return false;
        });
    });
    
    function change_bg(boxId) {
        $('#box_' + boxId).addClass('bgi1');
    }
    
    function remove_bg(boxId) {
        <?php
        $selectedLocation = $row_usr_lpref->usr_auc_prefLocation ?? '';
        ?>
        var selectedLocation = '<?php echo $selectedLocation; ?>';
        var boxMap = {
            1: 'any',
            2: 'abroad',
            3: 'domestic',
            4: 'my_city'
        };
        
        if (boxMap[boxId] != selectedLocation) {
            $('#box_' + boxId).removeClass('bgi1');
        }
    }
    
    function location_prf(boxId) {
        $('#usr_auc_prefLocation_' + boxId).prop('checked', true);
        
        // Remove highlight from all boxes
        $('[id^="box_"]').removeClass('bgi1');
        
        // Add highlight to selected box
        $('#box_' + boxId).addClass('bgi1');
    }
    
    function win_open_buy() {
        $('#div_info').hide();
    }
</script>
</head>
<body>

    <!--main div:start-->
    <div class="hm1 bbc" id="res-mob1">
    <?php 
    if (file_exists("includes/header_new.php")) {
        include "includes/header_new.php"; 
    }
    ?>
        <br><br>
<!-- Header start Here::-->
        
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1"></div>

        <?php 
        if (file_exists("includes/header_menu.php")) {
            include "includes/header_menu.php"; 
        }
        ?>
    
        <!--left navigation:start-->
        <div class="f1 w61n tb lh ml br" id="lnav">
            <ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
                <li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Auctions</h3></li> 
                <li style="border-bottom:none"><h3>Auction Purchases</h3></li>
                <li class="np npnew"><a href="manage-purchased-auctions.php">»&nbsp;Purchased Auctions</a></li>
                <li class="np npnew"><a href="manage-auction-alert.php">»&nbsp;Auction Alerts</a></li>
                <li class="np npnew"><a class="leftindi txtcol" href="my-auction-locationpref.php">»&nbsp;Location Preference</a></li>
                <li class="np npnew"><a href="transaction_history.php">»&nbsp;Transaction History</a></li>
                <li style="border-bottom: medium none;"><h3>Help / FAQs?</h3></li>
                <li class="np npnew"><a href="help.php">»&nbsp;Auction Help / FAQs?</a></li>
                <li class="ug-banner">
                
                <?php
                global $db;
$sql_adv = "SELECT * FROM advertisement WHERE adv_imagewidth = '200' AND adv_imageheight = '154' AND adv_status = '1' ORDER BY RAND() LIMIT 1";
$res_adv = mysqli_query($con, $sql_adv);
                
                if (mysqli_num_rows($res_adv) > 0)
                {
                    $row_adv = mysqli_fetch_object($res_adv);
                    $adv_link = htmlspecialchars($row_adv->adv_link ?? '');
                    $adv_img = htmlspecialchars($row_adv->adv_img ?? '');
                    ?>
                    <a href="//<?php echo $adv_link; ?>" target="_blank">
                        <img src="upload/advertisement/<?php echo $adv_img; ?>" width="200" height="154" alt="Advertisement">
                    </a>
                    <?php
                }
                else
                {
                ?>
                    <img src="upload/advertisement/200-154-advertisement.png" alt="Advertisement" border="0" height="154" width="200">
                <?php } ?>
                
                </li>
            </ul>
        </div>
        <!--left navigation:ends-->
     
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
                                                        <div class="myta">إدارة تفضيلات المزايدات</div>
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
                                                <a href="manage-tender-alert.php">إدارة تفضيلات المزايدات</a>
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
                                                            أريد مزايدات من
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
                                                                    اختيار هذا الخيار يعني أنك ستتلقى مزايدات من جميع أنحاء العالم بما في ذلك بلدك والخارج.
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
                                                                        &bull; لا توجد مزايدات من بلدك<br><img src="images/zero.gif" height="6" width="1"><br>
                                                                        &bull; لا توجد مزايدات من مدينتك
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
                                                                        &bull; لا توجد مزايدات من خارج بلدك<br><img src="images/zero.gif" height="6" width="1"><br>
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
                                                                        &bull; لا توجد مزايدات خارج 250 كم من مدينتك<br>
                                                                        <img src="images/zero.gif" height="6" width="1"><br>
                                                                        &bull; لا توجد مزايدات من خارج بلدك
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
   
                                            <!-- test end -->
                            
                                            <div style="border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
        
                                            <span id="subs_cats"></span> 
                                            <span id="procssing"></span> 
                                        </td> 
                                        <td><img src="images/zero.gif" height="2" width="2" alt=""></td> 
                                    </tr>
                                    <input name="catid" id="catid" value="" type="hidden"> 
                                </tbody>
                            </table> 
                            <div><br><br><br><br></div>
                        </td> 
                        <td style="border-right:0px;" valign="top"><img src="images/gray-line.gif" height="1" width="1" alt=""></td> 
                    </tr>
                </tbody>
            </table> 
            <div style="clear:both"><br></div>
        </div>
        <div class="c3">&nbsp;</div>
    </div>
    <!--footer:start-->
    <?php 
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    }
    ?>
</body>
</html>