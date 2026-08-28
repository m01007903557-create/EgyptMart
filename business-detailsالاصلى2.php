<?php
ob_start();
session_start();

// عرض جميع الأخطاء للمساعدة في التصحيح (يمكنك تعطيلها لاحقاً)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'common.php';

if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') {
    header("Location: sign-in.php");
    exit();
}
$uid = $_SESSION['uid_indm'];

class Editcat
{    
    var $company_name;
    var $website_alt;
    var $bnsprof_businesstype;
    var $bnsprof_yoe;
    var $bnsprof_comemp;
    var $bnsprof_turnover;
    var $bnsprof_owntype;
    var $userd;
    var $msg;
    
    function __construct()
    {
    }

    function valid()
    {
        $valid = true;
        if($this->website_alt != '' && !validate::is_weblink($this->website_alt))
        {
            $this->msg = '<font color="#CC0000">Please Enter a Valid Web Link</font>';
            $valid = false;
        }
        else if($this->bnsprof_yoe != "" && !is_numeric($this->bnsprof_yoe))
        {
            $this->msg = '<font color="#CC0000">Kindly enter a numeric value for the Year of Establishment.</font>';
            $valid = false;
        }
        return $valid;
    }
    
    function update($con)
    {
        $btype1 = "";
        if(is_array($this->bnsprof_businesstype)) {
            foreach($this->bnsprof_businesstype as $val)
            {
                $btype1 = $val . "," . $btype1;    
            }
            $btype = substr($btype1, 0, -1); 
        } else {
            $btype = '';
        }
        
        $userd_safe = mysqli_real_escape_string($con, $this->userd);
        $sqlchk = "select * from business_profile where bnsprof_uid='" . $userd_safe . "'";
        $reschk = mysqli_query($con, $sqlchk);
        
        if(mysqli_num_rows($reschk) > 0)
        {
            $sql = "update business_profile set        
                bnsprof_businesstype='" . mysqli_real_escape_string($con, $btype) . "',
                bnsprof_website_alt='" . mysqli_real_escape_string($con, $this->website_alt) . "',
                bnsprof_compname='" . mysqli_real_escape_string($con, $this->company_name) . "',
                bnsprof_yoe='" . mysqli_real_escape_string($con, $this->bnsprof_yoe) . "',
                bnsprof_comemp='" . mysqli_real_escape_string($con, $this->bnsprof_comemp) . "',
                bnsprof_turnover='" . mysqli_real_escape_string($con, $this->bnsprof_turnover) . "',
                bnsprof_owntype='" . mysqli_real_escape_string($con, $this->bnsprof_owntype) . "'
            where bnsprof_uid=" . $userd_safe;
            mysqli_query($con, $sql) or die(mysqli_error($con));
            $this->msg = '<div class="save bnr mt12" id="savemsg"><strong> Business Profile saved successfully ! </strong></div>';
        }
        else
        {
            $sql = "insert into business_profile
                set
                    bnsprof_uid='" . $userd_safe . "',
                    bnsprof_businesstype='" . mysqli_real_escape_string($con, $btype) . "',
                    bnsprof_website_alt='" . mysqli_real_escape_string($con, $this->website_alt) . "',
                    bnsprof_compname='" . mysqli_real_escape_string($con, $this->company_name) . "',
                    bnsprof_yoe='" . mysqli_real_escape_string($con, $this->bnsprof_yoe) . "',
                    bnsprof_comemp='" . mysqli_real_escape_string($con, $this->bnsprof_comemp) . "',
                    bnsprof_turnover='" . mysqli_real_escape_string($con, $this->bnsprof_turnover) . "',
                    bnsprof_owntype='" . mysqli_real_escape_string($con, $this->bnsprof_owntype) . "',
                    bnsprof_creation_date=now()";                    
            
            mysqli_query($con, $sql) or die(mysqli_error($con));
            $this->msg = '<div class="save bnr mt12" id="savemsg"><strong> Business Profile saved successfully ! </strong></div>';            
        }        
    }
}
                
$ecms = new Editcat();

if(isset($_SESSION['msg'])){ 
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']); 
} else { 
    $msg = ""; 
}
if(isset($_SESSION['company_name'])){ 
    $company_name = $_SESSION['company_name'];
    unset($_SESSION['company_name']); 
} else { 
    $company_name = ""; 
}
if(isset($_SESSION['website_alt'])){ 
    $website_alt = $_SESSION['website_alt'];
    unset($_SESSION['website_alt']); 
} else { 
    $website_alt = ""; 
}
if(isset($_SESSION['bnsprof_yoe'])){ 
    $bnsprof_yoe = $_SESSION['bnsprof_yoe'];
    unset($_SESSION['bnsprof_yoe']); 
} else { 
    $bnsprof_yoe = ""; 
}
if(isset($_SESSION['bnsprof_comemp'])){    
    $bnsprof_comemp = $_SESSION['bnsprof_comemp']; 
    unset($_SESSION['bnsprof_comemp']); 
} else { 
    $bnsprof_comemp = ""; 
}
if(isset($_SESSION['bnsprof_turnover'])){ 
    $bnsprof_turnover = $_SESSION['bnsprof_turnover']; 
    unset($_SESSION['bnsprof_turnover']); 
} else { 
    $bnsprof_turnover = ""; 
}
if(isset($_SESSION['bnsprof_owntype'])){ 
    $bnsprof_owntype = $_SESSION['bnsprof_owntype'];
    unset($_SESSION['bnsprof_owntype']); 
} else { 
    $bnsprof_owntype = ""; 
}

if(isset($_POST['btnUpdate'])){
    
    $ecms->company_name = trim(mysqli_real_escape_string($con, $_POST['company_name']));
    $ecms->website_alt = trim(mysqli_real_escape_string($con, $_POST['website_alt'])); 
    $ecms->bnsprof_businesstype = isset($_POST['bnsprof_businesstype']) ? $_POST['bnsprof_businesstype'] : array();
    $ecms->bnsprof_yoe = trim(mysqli_real_escape_string($con, $_POST['bnsprof_yoe']));
    $ecms->bnsprof_comemp = trim(mysqli_real_escape_string($con, $_POST['bnsprof_comemp']));
    $ecms->bnsprof_turnover = trim(mysqli_real_escape_string($con, $_POST['bnsprof_turnover']));
    $ecms->bnsprof_owntype = trim(mysqli_real_escape_string($con, $_POST['bnsprof_owntype']));
    $ecms->userd = trim(mysqli_real_escape_string($con, $_POST['userd']));

    if($ecms->valid()){
        $ecms->update($con);
    }
    
    $_SESSION['msg'] = $ecms->msg;
    header("Location: statutory-details.php");
    exit();
}

$sql = "select * from business_profile where bnsprof_uid='" . mysqli_real_escape_string($con, $uid) . "'"; 
$res = mysqli_query($con, $sql);
$row = mysqli_fetch_object($res);

$bstyp = array();
if($row && isset($row->bnsprof_businesstype)) {
    $bstyp = explode(",", $row->bnsprof_businesstype);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo htmlspecialchars(getSiteTitle()); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle()); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/b-v-7.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/jquery.fileupload.css">
<script language="javascript" type="text/javascript" src="js/jquery-1.7.min.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
<script type="text/javascript">
function chkalldetails()
{
    var website_alt = document.getElementById('website_alt');
    var bnsprof_yoe = document.getElementById('bnsprof_yoe');
    var message = "";
    var valid = true;
    
    if(website_alt.value != '' && !website_alt.value.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/))
    {
        message = 'Please Enter a Valid Web Link';
        website_alt.focus();
        valid = false;
    }
    else if(bnsprof_yoe.value != "" && isNaN(bnsprof_yoe.value))
    {
        message = "Kindly enter a numeric value for the Year of Establishment.";
        bnsprof_yoe.focus();
        valid = false;    
    }
    if(!valid)
    {
        document.getElementById('updatemessage').style.color = "red";
        document.getElementById('updatemessage').innerHTML = message;    
    }
    return valid;
}

</script>

</head>

<body>
<div class="hm1 bbc" id="res-mob1">

    <?php include "includes/header_new.php"; ?>
    <br>
    <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName()); ?>" width="1" height="1"></div>
   
        <?php include 'includes/header_menu.php'; ?>
        <?php include 'includes/left_menu.php'; ?>        
        <div class="w56b f1 p2b p14 blr">
            <style type="text/css">
                .max{color:#fa5901; font-size:11px; margin-top:5px} 
                .s_u{width:144px} 
                .frm_a{width:95%; border:1px solid #e0f0fd; padding:10px}
                .label {color: #000 !important; }
            </style>
            <div> 
                <h1 style="font-size: 22px; font-weight: bold; direction: rtl; text-align: right;" title="Business Profile">
                    إملأ بيانات بروفايل الشركة
                </h1>
            </div>
            <?php include 'includes/business-panel.php'; ?>
            
            <div id="re_link" class="utab" style="height:39px;">
                <span style="font-size: 14px;" class="f1" title="Complete your Business Details to attract genuine buyers.">
                    أكمل معلومات الشركة حتى تجتذب مشتريين حقيقيين 
                </span>
            </div>

            <div class="clb px"></div>
            <div class="clb"></div>
            <div class="mt5">
                <div style="text-align:left;width:389px;padding:1% 1% 1% 5%;display:block;margin-left:87px;" class="" id="updatemessage">
                    <?php echo $msg; ?>
                </div>
                <form style="margin:0px;" action="" method="POST" name="ModReg" onSubmit="return chkalldetails();">
                    <div class="frm_a clb" style="background-color:#FAF4FF">
                        <table align="left" border="0" cellpadding="4" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td class="label" style="text-align:right" width="135" title="Company Name">
                                        الإسم التجارى للشركة
                                    </td>
                                    <td>
                                        <div id="a1" class="tbp cona" style="display:none">
                                            <div class="t1a">We suggest that you use a genuine and complete company name such as <b>'Perfect Systems Ltd.'</b> Buyers and Suppliers prefer to interact with companies with complete and accurate company name.</div>
                                        </div>
                                        <input maxlength="60" name="company_name" id="company_name" value="<?php echo htmlspecialchars(ucwords(user_info($uid, 'bnsprof_compname'))); ?>" class="a_f rf" onFocus="javascript:ntt('comp_name','a1');" onBlur="nhid('a1')" tabindex="1">
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label pt7" valign="top" title="Company Logo">
                                        لوجو الشركة
                                    </td>
                                    <td>
                                        <script type="text/javascript">
                                        function list_photo()
                                        {
                                            $.get("companylogo-list.php", {'uid' : <?php echo (int)$uid; ?>}, 
                                            function(data){ 
                                                $('#list_photo').html(data); 
                                            });
                                        }

                                        function DelTempImage(imid)
                                        {
                                            var cnf = confirm("Remove logo?");
                                            if(cnf == true)
                                            {
                                                $.get("del_companylogo.php", {imid:imid},
                                                function(data){
                                                    list_photo();
                                                });	 
                                            }
                                        }
                                        function showImgButt()
                                        {
                                            $("#add_image1").show()
                                        }
                                        function hideImgButt()
                                        {
                                            $("#add_image1").hide()
                                        }
                                        jQuery(document).ready(function(){
                                            jQuery('#file_upload').fileupload({
                                                url: '/server/php/',
                                                maxNumberOfFiles: 1,
                                                dataType: 'json',
                                                done: function (e, data) {
                                                    jQuery.each(data.result.files, function (index, file)
                                                    {
                                                        jQuery.post("companylogo-update.php", {'uid' : '<?php echo (int)$uid; ?>', 'file' : file.name }, function(data) {
                                                            list_photo();	
                                                        });
                                                    });
                                                }
                                            });  
                                        });
                                        </script>
                                        
                                        <div class="file_button" style="margin:0px;" id="addbut">
                                            <script type="text/javascript">list_photo()</script>
                                            <div id="queue">
                                                <div align="left" id="list_photo" class="line clearfix"></div>
                                            </div>
                                            <div class="upload_div" title=" حمل لوجو - صورة الشعار التجارى - للشركة ">
                                                <img style="float:left; margin-right:10px;" src="<?php echo BASE_URL; ?>/images/newaddlogo.jpg"/>
                                                <input id="file_upload" type="file" name="files" style="cursor:pointer;" />
                                                <span class="file_input" title="Add image"> حمل لوجو الشركة </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" title="Alternate Website">
                                        أكتب الموقع الألكترونى الأصلى للشركة
                                    </td>
                                    <td>
                                        <div id="a3" class="tbp cona" style="display:none">
                                            <div class="t1a">If you have a website other than <?php echo htmlspecialchars(getWebSiteName()); ?> catalog, please enter its URL here.<br><b>Example: www.yourwebsite.com</b></div>
                                        </div> 
                                        <input class="a_f rf" maxlength="80" name="website_alt" id="website_alt" value="<?php echo htmlspecialchars(user_info($uid, 'bnsprof_website_alt')); ?>" tabindex="3">
                                        <span id="wb" class="em" style="display:none"></span><span id="wb1" class="em" style="display:none"></span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label pt7" valign="top" title="Business Type">
                                        إختار أنوع نشاط الشركة التى تعمل بها
                                    </td>
                                    <td>
                                        <table style="margin:0px;" cellpadding="0" cellspacing="0" width="70%">
                                            <tbody>
                                            <?php
                                            $c = 1;
                                            $bstypsql = mysqli_query($con, "select * from business_type where bsntyp_status='1'");
                                            while($bstyprow = mysqli_fetch_object($bstypsql)){
                                                if($c == 1) {
                                                    echo "<tr>";    
                                                }
                                            ?>
                                                <td class="fom3" style="text-align:left" valign="TOP" width="35%" height="20">
                                                    <input name="bnsprof_businesstype[]" id="bnsprof_businesstype" value="<?php echo $bstyprow->bsntyp_id; ?>" tabindex="10" type="CHECKBOX" <?php if(in_array($bstyprow->bsntyp_id, $bstyp)){ ?> checked="checked" <?php } ?> >
                                                    <?php echo htmlspecialchars($bstyprow->bsntyp_title); ?>
                                                </td>            
                                            <?php 
                                                if($c == 3) { 
                                                    echo "</tr>"; 
                                                    $c = 0;
                                                } 
                                                $c++;
                                            } ?>                                                          
                                            </tbody>
                                        </table>
                                        <img src="images/zero1.gif" width="1" height="7"><br>
                                    </td>
                                </tr>    
                                
                                <tr>
                                    <td class="label" width="138" title="Year of Establishment">
                                        أكتب سنة إنشاء الشركة
                                    </td>
                                    <td>
                                        <div id="a11" class="tbp cona" style="display:none">
                                            <div class="t1a">Please enter year in (YYYY format), <b>Example: 1998</b></div>
                                        </div>
                                        <input maxlength="60" class="a_f rf" name="bnsprof_yoe" id="bnsprof_yoe" value="<?php echo htmlspecialchars(user_info($uid, 'bnsprof_yoe')); ?>">
                                        <span id="yr" class="em" style="display:none"></span>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="label" title="No. of Employees">
                                        إختار العدد التقريبى للموظفين
                                    </td>
                                    <td>
                                        <div id="a111" style="display:none"></div>
                                        <select class="a_f em_p" size="1" name="bnsprof_comemp" id="bnsprof_comemp">
                                            <option value="">--- إختار واحدة ---</option>
                                            <?php
                                            $noempsql = mysqli_query($con, "select * from employee_range where emprange_status='1'");
                                            while($noemprow = mysqli_fetch_object($noempsql)) {
                                            ?>
                                                <option value="<?php echo $noemprow->emprange_id; ?>" <?php if(user_info($uid, 'bnsprof_comemp') == $noemprow->emprange_id){ ?> selected="selected" <?php } ?> >
                                                    <?php echo htmlspecialchars($noemprow->emprange_type); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            
                                <tr>
                                    <td class="label" title="Revenue Sales Turnover">
                                        إختار العائد السنوى التقريبى
                                    </td>
                                    <td>
                                        <div id="a112" style="display:none"></div>
                                        <select class="a_f em_p" size="1" name="bnsprof_turnover" id="bnsprof_turnover">
                                            <option value="">--- إختار واحدة ---</option>
                                            <?php
                                            $turnoversql = mysqli_query($con, "select * from revenue_turnover where revturnover_status='1'");
                                            while($turnoverow = mysqli_fetch_object($turnoversql)) {
                                            ?>
                                                <option value="<?php echo $turnoverow->revturnover_id; ?>" <?php if(user_info($uid, 'bnsprof_turnover') == $turnoverow->revturnover_id){ ?> selected="selected" <?php } ?>>
                                                    <?php echo htmlspecialchars($turnoverow->revturnover_title); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            
                                <tr>
                                    <td class="label" title="Ownership Type">
                                        إختار نوع ملكيـة الشركة
                                    </td>
                                    <td>
                                        <div id="a113" style="display:none"></div> 
                                        <select class="a_f em_p" size="1" name="bnsprof_owntype" id="bnsprof_owntype">
                                            <option value="">--- إختار واحدة ---</option>
                                            <?php
                                            $owntypesql = mysqli_query($con, "select * from ownership_type where owntyp_status='1'");
                                            while($owntyperow = mysqli_fetch_object($owntypesql)) {
                                            ?>
                                                <option value="<?php echo $owntyperow->owntyp_id; ?>" <?php if(user_info($uid, 'bnsprof_owntype') == $owntyperow->owntyp_id){ ?> selected="selected" <?php } ?>>
                                                    <?php echo htmlspecialchars($owntyperow->owntyp_title); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>&nbsp;</td>
                                    <td align="left">
                                        <table><tbody><tr>
                                            <td width="118px;" title="إحفظ - التغييرات - للنشر - مع بيانات الشركة ">
                                                <input name="userd" id="userd" value="<?php echo (int)$uid; ?>" tabindex="31" type="hidden">
                                                <input name="btnUpdate" id="btnUpdate" value="إحفظ التغييرات" class="saps mt5" tabindex="31" type="submit">
                                            </td>
                                            <td> 
                                                <span id="pf_save" style="display:none;margin-left:15px;margin-top:6px;">
                                                    <img src="images/loading.gif" alt="" border="0" width="16" height="11">
                                                </span>
                                            </td>
                                        </tr></tbody></table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="clb">&nbsp;</div>
                    </div>
                </form>
            </div>
            <div><br></div>
            <div><br></div>
        </div>
        <div class="c3">&nbsp;</div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>