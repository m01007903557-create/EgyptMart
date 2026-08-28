<?php
/**
 * File: create-free-website.php
 * Version: PHP 8.3
 * Description: إنشاء موقع ويب مجاني للشركة - صفحة إدخال بيانات الشركة الأساسية
 */

// بدء المخزن المؤقت والجلسة
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// تعيين الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "create-free-website.php";

// التحقق من وجود مستخدم مسجل دخوله
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit();
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// جلب بيانات المستخدم والشركة
$sql = "SELECT * FROM user, business_profile WHERE bnsprof_uid = usr_id AND usr_id = {$uid}";
$res = mysqli_query($con, $sql);
$row = $res ? mysqli_fetch_object($res) : null;

/***********************************/
class CreateSupplierWebsite
{
    public $msg;
    public $usr_id;
    public $bnsprof_id;
    public $name_prefix;
    public $fname;
    public $lname;
    public $bnsprof_compname;
    public $city;    
    public $bnsprof_city;
    public $bnsprof_address1;
    public $con; // إضافة متصل قاعدة البيانات
    
    public function __construct($usr_id, $bnsprof_id, $name_prefix, $fname, $lname, $bnsprof_compname, $city, $bnsprof_city, $bnsprof_address1)
    {    
        global $con;
        $this->con = $con;
        $this->usr_id = (int)$usr_id;
        $this->bnsprof_id = (int)$bnsprof_id;
        $this->name_prefix = $this->sanitize($name_prefix);
        $this->fname = $this->sanitize($fname);
        $this->lname = $this->sanitize($lname);
        $this->bnsprof_compname = $this->sanitize($bnsprof_compname);
        $this->city = $this->sanitize($city);
        $this->bnsprof_city = (int)$bnsprof_city;
        $this->bnsprof_address1 = $this->sanitize($bnsprof_address1);
    }
    
    /**
     * تنظيف المدخلات
     */
    private function sanitize($data) {
        if ($data === null) return '';
        return trim($data);
    }

    /**
     * التحقق من صحة البيانات
     */
    public function valid()
    {
        $valid = true;
        
        if ($this->fname == "") {
            $this->msg = '<font color="#FF0000">Kindly enter your first name.</font>';
            $valid = false;
        } else if ($this->lname == "") {
            $this->msg = '<font color="#FF0000">Kindly enter your last name.</font>';
            $valid = false;
        } else if ($this->bnsprof_compname == "") {
            $this->msg = '<font color="#FF0000">Kindly enter your Company Name.</font>';
            $valid = false;
        } else if ($this->bnsprof_city <= 0 || $this->city == "" || $this->city == "Select City") {
            $this->msg = '<font color="#FF0000">Kindly enter City name.</font>';
            $valid = false;
        } else if ($this->bnsprof_address1 == "") {
            $this->msg = '<font color="#FF0000">Kindly enter Address.</font>';
            $valid = false;
        }

        return $valid;
    }
    
    /**
     * إضافة/تحديث بيانات المستخدم والشركة
     */
    public function add()
    {    
        // تحديث بيانات المستخدم
        $sql_u = "UPDATE user SET
                    name_prefix = '" . mysqli_real_escape_string($this->con, $this->name_prefix) . "',
                    fname = '" . mysqli_real_escape_string($this->con, $this->fname) . "',
                    lname = '" . mysqli_real_escape_string($this->con, $this->lname) . "',
                    date = NOW()
                  WHERE usr_id = {$this->usr_id}";

        $result_u = mysqli_query($this->con, $sql_u);
        
        if (!$result_u) {
            error_log("خطأ في تحديث بيانات المستخدم: " . mysqli_error($this->con));
        }
        
        // تحديث بيانات الشركة
        $sql_bp = "UPDATE business_profile SET
                    bnsprof_compname = '" . mysqli_real_escape_string($this->con, $this->bnsprof_compname) . "',
                    bnsprof_city = {$this->bnsprof_city},
                    bnsprof_address1 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_address1) . "'
                  WHERE bnsprof_id = {$this->bnsprof_id}";
                
        $result_bp = mysqli_query($this->con, $sql_bp);
        
        if ($result_bp) {
            $this->msg = '<font color="#009900">Company created successfully.</font>';
        } else {
            $this->msg = '<font color="#FF0000">Error: ' . mysqli_error($this->con) . '</font>';
            error_log("خطأ في تحديث بيانات الشركة: " . mysqli_error($this->con));
        }
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : "";
unset($_SESSION['msg']);

if (isset($_POST['btnSubmit'])) {
    
    $adn = new CreateSupplierWebsite(
        (int)$_POST['usr_id'],
        (int)$_POST['bnsprof_id'],
        $_POST['name_prefix'] ?? '',
        $_POST['fname'] ?? '',
        $_POST['lname'] ?? '',
        $_POST['bnsprof_compname'] ?? '',
        $_POST['city'] ?? '',
        $_POST['bnsprof_city'] ?? 0,
        $_POST['bnsprof_address1'] ?? ''
    );

    if ($adn->valid()) {
        $adn->add();
        header("Location: my-dashboard.php");
        exit();
    } else {
        $_SESSION['msg'] = $adn->msg;
        header("Location: create-free-website.php");
        exit();
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <link href="css/free-web.css" rel="stylesheet" type="text/css">
    <link href="css/main-v1.css" rel="stylesheet" type="text/css">
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />
    <script type="text/javascript" src="js/jquery.autocomplete2.js"></script>
    
    <script type="text/javascript">
    $(document).ready(function(){
        $("#city").autocomplete("ajax-file/showcity.php", {
            selectFirst: true
        })
        .result(function(event, data, formatted) {
            var dm = data[0].split(">>");
            $("#city").val(dm[0]);
            $("#state").val(dm[1]);
            $("#bnsprof_city").val(data[1]);
            $("#bnsprof_state").val(data[2]);
        });
    });
    
    function validWebsite() {
        var fname = document.getElementById('fname');
        var lname = document.getElementById('lname');
        var bnsprof_compname = document.getElementById('bnsprof_compname');
        var bnsprof_city = document.getElementById('bnsprof_city');
        var city = document.getElementById('city');
        var bnsprof_address1 = document.getElementById('bnsprof_address1');
        
        var message = "";
        var valid = true;
        
        if (!fname.value || fname.value.trim() == "") {
            message = "Kindly enter your first name.";
            fname.focus();
            valid = false;
        } else if (!lname.value || lname.value.trim() == "") {
            message = "Kindly enter your last name.";
            lname.focus();
            valid = false;
        } else if (!bnsprof_compname.value || bnsprof_compname.value.trim() == "") {
            message = "Kindly enter your Company Name.";
            bnsprof_compname.focus();
            valid = false;
        } else if (!bnsprof_city.value || bnsprof_city.value == "" || !city.value || city.value.trim() == "" || city.value == "Select City") {
            message = "Kindly enter City name.";
            city.focus();
            valid = false;
        } else if (!bnsprof_address1.value || bnsprof_address1.value.trim() == "") {
            message = "Kindly enter Address.";
            bnsprof_address1.focus();
            valid = false;
        }
        
        if (!valid) {
            alert(message);
        }
        return valid;
    }
    </script>
</head>
<body bgcolor="#ffffff" marginheight="0" marginwidth="0" class="search-show-box">
    
    <div class="hm1 bbc">
        <!-- Header start Here::-->
        <?php 
        if (file_exists("includes/header_new.php")) {
            include "includes/header_new.php"; 
        }
        ?>
        
        <div class="help_script_error" style="display:none" id="error" align="center">
            <b><?php echo htmlspecialchars($msg); ?></b><br><br>
        </div>
        
        <!--form html start--> 
        <form style="margin:0px;" action="" method="POST" name="ModReg" onsubmit="return validWebsite();">
            <input type="hidden" id="usr_id" name="usr_id" value="<?php echo (int)$uid; ?>" />
            <input type="hidden" id="bnsprof_id" name="bnsprof_id" value="<?php echo isset($row->bnsprof_id) ? (int)$row->bnsprof_id : 0; ?>" />
            
            <div class="cfw_wrap" style="width:966px; padding:0px 20px; margin:0 auto; overflow:hidden;">
                <div style="position:relative; width:20px; height:24px; top:62px; left: 381px;">
                    <img src="images/arrow.png" alt="Arrow">
                </div>
                
                <div id="con_right" style="float:left; background-color:#F9F0FF; margin-top:25px; background-repeat:no-repeat; padding:8px 8px 8px 15px; width:400px; overflow:hidden;">
                    <h1 style="font-size:25px; color:#000099; margin-top:0; line-height:30px; border-bottom:1px solid #e7e5f2; padding-bottom:9px;">
                        Create your <span style="font-size:32px; color:#890101;">Free Website</span> in single easy step!
                    </h1>
                    <h1 style="font-weight:bold; font-size:14px; color:#222222; padding-top:12px; padding-bottom:5px; margin-top:0;">Benefits:</h1>
                    <div id="bnfts">
                        <ul>
                            <li><span style="color:#666666;">Showcase your products</span></li>
                            <li><span style="color:#666666;">Get listing in relevant product categories</span></li>
                            <li><span style="color:#666666;">Expand your business</span></li>
                        </ul>
                    </div>
                </div>
                
                <div id="con_left" class="f1" style="border:1px solid #F9F0FF; border-radius:8px; padding:10px 15px; width:515px;">
                    <!-- Your Name -->
                    <div style="width:100%; overflow:hidden;" class="pt10">
                        <div class="label f1"><font color="#FF0000">* </font>Your Name:</div>
                        <div class="f2" style="width:75%">
                            <select tabindex="1" class="f1 p6" style="width:62px; border:1px solid #d7d8dd; border-radius:3px; padding:5px;" id="name_prefix" name="name_prefix">
                                <option <?php echo (isset($row->name_prefix) && $row->name_prefix == "Mr.") ? 'selected="selected"' : ''; ?> value="Mr.">Mr.</option>
                                <option <?php echo (isset($row->name_prefix) && $row->name_prefix == "Ms.") ? 'selected="selected"' : ''; ?> value="Ms.">Ms.</option>
                                <option <?php echo (isset($row->name_prefix) && $row->name_prefix == "Mrs.") ? 'selected="selected"' : ''; ?> value="Mrs.">Mrs.</option>
                                <option <?php echo (isset($row->name_prefix) && $row->name_prefix == "Dr.") ? 'selected="selected"' : ''; ?> value="Dr.">Dr.</option>
                            </select>
                            <input class="txtlname f1 p6" id="fname" name="fname" value="<?php echo htmlspecialchars($row->fname ?? ''); ?>" placeholder="" style="border:1px solid #d7d8dd; border-radius:3px; width:133px; margin-left:5px;" type="text"/>
                            <input class="txtlname f2 p6" placeholder="" name="lname" value="<?php echo htmlspecialchars($row->lname ?? ''); ?>" id="lname" style="border:1px solid #d7d8dd; border-radius:3px; width:133px;" type="text"/>
                        </div>
                    </div>
                    
                    <!-- Email ID -->
                    <div style="width:100%; overflow:hidden;" class="pt10">
                        <div class="label f1"><font color="#FF0000">* </font>Your Email ID:</div>
                        <div class="f2" id="my" style="width:75%">
                            <input name="email" id="email" value="<?php echo htmlspecialchars($row->email ?? ''); ?>" class="txtlname f2 p6" style="border:1px solid #d7d8dd; border-radius:3px; width:361px; background-color:#EDEDED;" readonly="readonly" type="text">
                        </div>
                    </div>
                    
                    <!-- Company Name -->
                    <div style="width:100%; overflow:hidden;" class="pt10">
                        <div class="label f1"><font color="#FF0000">* </font>Company Name:</div>
                        <div class="f2" style="width:75%">
                            <input class="txtlname f2 p6" id="bnsprof_compname" value="<?php echo htmlspecialchars(stripslashes($row->bnsprof_compname ?? '')); ?>" name="bnsprof_compname" placeholder="" style="border:1px solid #d7d8dd; border-radius:3px; width:361px;" type="text">
                        </div>
                    </div>
                    
                    <!-- Country -->
                    <div style="width:100%; overflow:hidden;" class="pt10">
                        <div class="label f1"><font color="#FF0000">* </font>Country:</div>
                        <div class="f2" id="xyz" style="width:75%">
                            <input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" name="country" id="country" value="<?php echo htmlspecialchars(get_country_name($row->country ?? 0)); ?>" class="txtlname f2 p6 ui-autocomplete-input" style="border:1px solid #d7d8dd; border-radius:3px; width:361px; background-color:#EDEDED;" readonly="readonly" type="text">
                        </div>
                    </div>
                    
                    <!-- City/State -->
                    <div style="width:100%; overflow:hidden;" id="ban" class="pt10">
                        <div class="label f1"><font color="#FF0000">* </font>City/State:</div>
                        <div class="f2" style="width:75%">
                            <div id="xyq">
                                <input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" name="city" value="<?php echo ($row->bnsprof_city ?? 0) != 0 ? htmlspecialchars(get_city_name((int)$row->bnsprof_city)) : ''; ?>" placeholder="Select City" id="city" style="border:1px solid #d7d8dd; border-radius:3px; margin-right:4px;" class="prd1 p6 f1 city_o ui-autocomplete-input" type="text">
                                <input class="ui-autocomplete-input" name="bnsprof_city" id="bnsprof_city" autocomplete="off" value="<?php echo (int)($row->bnsprof_city ?? 0); ?>" type="hidden">
                            </div>
                            <div id="xyl">
                                <input placeholder="State" name="state" id="state" value="<?php echo ($row->bnsprof_state ?? 0) != 0 ? htmlspecialchars(get_state_name((int)$row->bnsprof_state)) : ''; ?>" style="border:1px solid #d7d8dd; border-radius:3px;" class="prd2 p6 f2" readonly="readonly" type="text">
                                <input class="ui-autocomplete-input" name="bnsprof_state" id="bnsprof_state" autocomplete="off" value="<?php echo (int)($row->bnsprof_state ?? 0); ?>" type="hidden">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mobile No. -->
                    <div class="clr pt10" style="overflow:hidden;">
                        <div class="label f1"><font color="#FF0000">* </font>Mobile No.:</div>
                        <div id="nano">
                            <input class="f1 p6" name="country_ph_code" id="country_ph_code" value="<?php echo htmlspecialchars($row->country_ph_code ?? ''); ?>" style="border:1px solid #d7d8dd; border-radius:3px; margin-left:1px; float:left; width:65px; background-color:#EDEDED;" readonly="readonly" type="text">
                        </div>
                        <div id="nano1">
                            <input name="mobile1" value="<?php echo htmlspecialchars($row->mobile1 ?? ''); ?>" id="mobile1" style="border:1px solid #d7d8dd; border-radius:3px; width:285px; background-color:#EDEDED;" placeholder="" class="txtlname f2 p6" readonly="readonly" type="text">
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div style="width:100%; overflow:hidden;" class="pt10">
                        <div class="label f1"><font color="#FF0000">* </font>Address:</div>
                        <div class="f2" style="width:75%">
                            <input name="bnsprof_address1" value="<?php echo htmlspecialchars($row->bnsprof_address1 ?? ''); ?>" id="bnsprof_address1" class="txtlname f2 p6" placeholder="" style="border:1px solid #d7d8dd; min-height:32px; border-radius:3px; width:361px;" type="text">
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="pt10" style="width:100%; overflow:hidden;">
                        <div class="label f1">&nbsp;</div>
                        <div class="f2" style="width:75%">
                            <div id="but" align="center">
                                <input class="clr btnbg" style="font-size:16px; width:180px;" value="Create Website Now" name="btnSubmit" id="save_button" type="submit">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <br>
        <!--form end-->
        <div><br><br></div>
    </div>
    
    <!-- Footer Start Here::-->
    <?php 
    if (file_exists('includes/footer.php')) {
        include 'includes/footer.php';
    }
    ?>
</body>
</html>