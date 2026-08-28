<?php
include 'common.php';

$uid = $_SESSION['uid_indm'];

class Editcat {
    var $name_prefix;
    var $fname;
    var $lname;
    var $userdesignation;
    var $ceo_prefix;
    var $ceo_fname;
    var $ceo_lname;
    var $company_name;
    var $address;
    var $address1;
    var $city;
    var $state;
    var $zipcode;
    var $telephone_areacode1;
    var $telephone1;
    var $telephone_areacode2;
    var $telephone2;
    var $telephone_areacode3;
    var $telephone3;
    var $telephone_areacode4;
    var $telephone4;
    var $mobile1;
    var $mobile2;
    var $mobile3;
    var $mobile4;
    var $fax_areacode1;
    var $fax1;
    var $fax_areacode2;
    var $fax2;
    var $email_alt1;
    var $email_alt2;
    var $email_alt3;
    var $website_alt;
    var $userd;
    var $businessdocument;
    var $profileimage;
    var $msg;
    private $db;
    private $cid;

    function __construct()
{
    global $con;
    $this->cid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

    function cmsdetailsObj(){
    if ($this->cid == 0) {
        return null;
    }
    // ✅ تعديل آمن للسطر 49 و 50
if(isset($this->cid) && $this->cid > 0) {
    $sql="select * from products where pd_id='".$this->cid."'";
    $res= mysqli_query($con,$sql);
} else {
    $res = null;
}
    return mysqli_fetch_object($res);
}

    function valid() {
        $valid = true;
        if ($this->fname == "") {
            $this->msg = '<font color="#CC0000">Please enter your First Name.</font>';
            $valid = false;
        } elseif ($this->city == "") {
            $this->msg = '<font color="#CC0000">Please enter your city name</font>';
            $valid = false;
        } elseif ($this->telephone_areacode1 == "" && $this->telephone1 != "") {
            $this->msg = '<font color="#CC0000">رجاء إدخال كود المنطقة</font>';
            $valid = false;
        } elseif ($this->telephone1 == "" && $this->telephone_areacode1 != "") {
            $this->msg = '<font color="#CC0000">رجاء إدخال رقم التليفون الأرضى</font>';
            $valid = false;
        } elseif ($this->telephone_areacode2 == "" && $this->telephone2 != "") {
            $this->msg = '<font color="#CC0000">رجاء إدخال كود المنطقة</font>';
            $valid = false;
        } elseif ($this->telephone2 == "" && $this->telephone_areacode2 != "") {
            $this->msg = '<font color="#CC0000">رجاء إدخال رقم التليفون الأرضى</font>';
            $valid = false;
        } elseif ($this->telephone_areacode3 == "" && $this->telephone3 != "") {
            $this->msg = '<font color="#CC0000">رجاء إدخال كود المنطقة</font>';
            $valid = false;
        } elseif ($this->telephone3 == "" && $this->telephone_areacode3 != "") {
            $this->msg = '<font color="#CC0000">رجاء إدخال رقم التليفون الأرضى</font>';
            $valid = false;
        } elseif ($this->telephone_areacode4 == "" && $this->telephone4 != "") {
            $this->msg = '<font color="#CC0000">رجاء إدخال كود المنطقة</font>';
            $valid = false;
        } elseif ($this->telephone4 == "" && $this->telephone_areacode4 != "") {
            $this->msg = '<font color="#CC0000">رجاء إدخال رقم التليفون الأرضى</font>';
            $valid = false;
        } elseif ($this->fax_areacode1 == "" && $this->fax1 != "") {
            $this->msg = '<font color="#CC0000">Please enter area code.</font>';
            $valid = false;
        } elseif ($this->fax1 == "" && $this->fax_areacode1 != "") {
            $this->msg = '<font color="#CC0000">Please enter fax number.</font>';
            $valid = false;
        } elseif ($this->fax_areacode2 == "" && $this->fax2 != "") {
            $this->msg = '<font color="#CC0000">Please enter area code.</font>';
            $valid = false;
        } elseif ($this->fax2 == "" && $this->fax_areacode2 != "") {
            $this->msg = '<font color="#CC0000">Please enter fax number.</font>';
            $valid = false;
        } elseif ($this->email_alt1 != '' && !validate::is_email($this->email_alt1)) {
            $this->msg = '<font color="#CC0000">Please Enter Valid email</font>';
            $valid = false;
        } elseif ($this->email_alt2 != '' && !validate::is_email($this->email_alt2)) {
            $this->msg = '<font color="#CC0000">Please Enter Valid email</font>';
            $valid = false;
        } elseif ($this->email_alt3 != '' && !validate::is_email($this->email_alt3)) {
            $this->msg = '<font color="#CC0000">Please Enter Valid email</font>';
            $valid = false;
        } elseif ($this->website_alt != '' && !validate::is_weblink($this->website_alt)) {
            $this->msg = '<font color="#CC0000">Please Enter a Valid Web Link</font>';
            $valid = false;
        }
        return $valid;
    }

    function update() {
        $filePath = dirname(__FILE__) . '/server/php/files/' . $this->profileimage;
        $thumbfilePath = dirname(__FILE__) . '/server/php/files/thumbnail/' . $this->profileimage;
        $image_data = '';
        if (file_exists($filePath)) {
            $image_data = addslashes(file_get_contents($filePath));
            unlink($filePath);
            unlink($thumbfilePath);
        }
        $sql = "UPDATE user SET
                name_prefix='{$this->name_prefix}',
                fname='{$this->fname}',
                lname='{$this->lname}',
                mobile1='{$this->mobile1}',
                image='{$this->profileimage}'";
        if ($image_data != '')
            $sql .= ", profileImage='{$image_data}'";
        $sql .= " WHERE usr_id={$this->userd}";
        mysqli_query($this->db, $sql);

        $sqlchk = "SELECT * FROM business_profile WHERE bnsprof_uid='{$this->userd}'";
        $reschk = mysqli_query($this->db, $sqlchk);
        $string = str_replace(' ', '', $this->company_name);
        if (mysqli_num_rows($reschk) > 0) {
            $sql = "UPDATE business_profile SET
                    bnsprof_designation='{$this->userdesignation}',
                    bnsprof_ceoprefix ='{$this->ceo_prefix}',
                    bnsprof_ceofname ='{$this->ceo_fname}',
                    bnsprof_ceolname='{$this->ceo_lname}',
                    bnsprof_compname='{$this->company_name}',
                    bnsprof_address1='{$this->address}',
                    bnsprof_address2='{$this->address1}',
                    bnsprof_city='{$this->city}',
                    bnsprof_state='{$this->state}',
                    bnsprof_zipcode='{$this->zipcode}',
                    bnsprof_phcode1='{$this->telephone_areacode1}',
                    bnsprof_ph1='{$this->telephone1}',
                    bnsprof_phcode2='{$this->telephone_areacode2}',
                    bnsprof_ph2='{$this->telephone2}',
                    bnsprof_phcode3='{$this->telephone_areacode3}',
                    bnsprof_ph3='{$this->telephone3}',
                    bnsprof_phcode4='{$this->telephone_areacode4}',
                    bnsprof_ph4='{$this->telephone4}',
                    bnsprof_mobile2='{$this->mobile2}',
                    bnsprof_mobile3='{$this->mobile3}',
                    bnsprof_mobile4='{$this->mobile4}',
                    bnsprof_faxcode1='{$this->fax_areacode1}',
                    bnsprof_fax1='{$this->fax1}',
                    bnsprof_faxcode2='{$this->fax_areacode2}',
                    bnsprof_fax2='{$this->fax2}',
                    bnsprof_emailalt1='{$this->email_alt1}',
                    bnsprof_emailalt2='{$this->email_alt2}',
                    bnsprof_emailalt3='{$this->email_alt3}',
                    bnsprof_website_alt='{$this->website_alt}',
                    bnsprof_comp_url='$string'
                    WHERE bnsprof_uid={$this->userd}";
            mysqli_query($this->db, $sql);
        } else {
            $sql = "INSERT INTO business_profile SET
                    bnsprof_uid='{$this->userd}',
                    bnsprof_designation='{$this->userdesignation}',
                    bnsprof_ceoprefix ='{$this->ceo_prefix}',
                    bnsprof_ceofname ='{$this->ceo_fname}',
                    bnsprof_ceolname='{$this->ceo_lname}',
                    bnsprof_compname='{$this->company_name}',
                    bnsprof_address1='{$this->address}',
                    bnsprof_address2='{$this->address1}',
                    bnsprof_city='{$this->city}',
                    bnsprof_state='{$this->state}',
                    bnsprof_zipcode='{$this->zipcode}',
                    bnsprof_phcode1='{$this->telephone_areacode1}',
                    bnsprof_ph1='{$this->telephone1}',
                    bnsprof_phcode2='{$this->telephone_areacode2}',
                    bnsprof_ph2='{$this->telephone2}',
                    bnsprof_phcode3='{$this->telephone_areacode3}',
                    bnsprof_ph3='{$this->telephone3}',
                    bnsprof_phcode4='{$this->telephone_areacode4}',
                    bnsprof_ph4='{$this->telephone4}',
                    bnsprof_mobile2='{$this->mobile2}',
                    bnsprof_mobile3='{$this->mobile3}',
                    bnsprof_mobile4='{$this->mobile4}',
                    bnsprof_faxcode1='{$this->fax_areacode1}',
                    bnsprof_fax1='{$this->fax1}',
                    bnsprof_faxcode2='{$this->fax_areacode2}',
                    bnsprof_fax2='{$this->fax2}',
                    bnsprof_emailalt1='{$this->email_alt1}',
                    bnsprof_emailalt2='{$this->email_alt2}',
                    bnsprof_emailalt3='{$this->email_alt3}',
                    bnsprof_website_alt='{$this->website_alt}',
                    bnsprof_comp_url='$string',
                    bnsprof_creation_date=NOW()";
            mysqli_query($this->db, $sql);
        }
    }
}

//$ecms = new Editcat();
//$row = $ecms->cmsdetailsObj();

// ... (باقي ملف my-contactdetails.php من هنا يبقى كما هو تمامًا، بما في ذلك الـ HTML و CSS و JavaScript) .....
	
	


if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg'];	unset($_SESSION['msg']); }else{ $msg=""; }
if(isset($_SESSION['name_prefix'])){ $name_prefix=$_SESSION['name_prefix'];	unset($_SESSION['name_prefix']); }else{ $name_prefix=""; }
if(isset($_SESSION['fname'])){	$fname=$_SESSION['fname'];	unset($_SESSION['fname']); }else{ $fname=""; }
if(isset($_SESSION['lname'])){	$lname=$_SESSION['lname'];	unset($_SESSION['lname']); }else{ $lname=""; }
if(isset($_SESSION['userdesignation'])){	$userdesignation=$_SESSION['userdesignation'];	unset($_SESSION['userdesignation']); }else{ $userdesignation=""; }
if(isset($_SESSION['ceo_prefix'])){ $ceo_prefix=$_SESSION['ceo_prefix']; unset($_SESSION['ceo_prefix']); }else{ $ceo_prefix=""; }
if(isset($_SESSION['ceo_fname'])){ $ceo_fname=$_SESSION['ceo_fname'];	unset($_SESSION['ceo_fname']); }else{ $ceo_fname=""; }
if(isset($_SESSION['ceo_lname'])){	$ceo_lname=$_SESSION['ceo_lname'];	unset($_SESSION['ceo_lname']); }else{ $ceo_lname=""; }
if(isset($_SESSION['company_name'])){	$company_name=$_SESSION['company_name'];	unset($_SESSION['company_name']); }else{ $company_name=""; }
if(isset($_SESSION['address'])){ $address=$_SESSION['address']; unset($_SESSION['address']); }else{ $address=""; }
if(isset($_SESSION['address1'])){ $address1=$_SESSION['address1'];	unset($_SESSION['address1']); }else{ $address1=""; }
if(isset($_SESSION['city'])){	$city=$_SESSION['city'];	unset($_SESSION['city']); }else{ $city=""; }
if(isset($_SESSION['state'])){	$state=$_SESSION['state'];	unset($_SESSION['state']); }else{ $state=""; }
if(isset($_SESSION['zipcode'])){	$zipcode=$_SESSION['zipcode'];	unset($_SESSION['zipcode']); }else{ $zipcode=""; }
if(isset($_SESSION['telephone_areacode1'])){ $telephone_areacode1=$_SESSION['telephone_areacode1'];	unset($_SESSION['telephone_areacode1']); }else{ $telephone_areacode1=""; }
if(isset($_SESSION['telephone1'])){ $telephone1=$_SESSION['telephone1'];	unset($_SESSION['telephone1']); }else{ $telephone1=""; }
if(isset($_SESSION['telephone_areacode2'])){ $telephone_areacode2=$_SESSION['telephone_areacode2'];	unset($_SESSION['telephone_areacode2']); }else{ $telephone_areacode2=""; }
if(isset($_SESSION['telephone2'])){ $telephone2=$_SESSION['telephone2'];	unset($_SESSION['telephone2']); }else{ $telephone2=""; }
if(isset($_SESSION['telephone_areacode3'])){ $telephone_areacode3=$_SESSION['telephone_areacode3'];	unset($_SESSION['telephone_areacode3']); }else{ $telephone_areacode3=""; }
if(isset($_SESSION['telephone3'])){ $telephone3=$_SESSION['telephone3'];	unset($_SESSION['telephone3']); }else{ $telephone3=""; }
if(isset($_SESSION['telephone_areacode4'])){ $telephone_areacode4=$_SESSION['telephone_areacode4'];	unset($_SESSION['telephone_areacode4']); }else{ $telephone_areacode4=""; }
if(isset($_SESSION['telephone4'])){ $telephone4=$_SESSION['telephone4'];	unset($_SESSION['telephone4']); }else{ $telephone4=""; }
if(isset($_SESSION['mobile1'])){ $mobile1=$_SESSION['mobile1'];	unset($_SESSION['mobile1']); }else{ $mobile1=""; }
if(isset($_SESSION['mobile2'])){ $mobile2=$_SESSION['mobile2'];	unset($_SESSION['mobile2']); }else{ $mobile2=""; }
if(isset($_SESSION['mobile3'])){ $mobile3=$_SESSION['mobile3'];	unset($_SESSION['mobile3']); }else{ $mobile3=""; }
if(isset($_SESSION['mobile4'])){ $mobile4=$_SESSION['mobile4'];	unset($_SESSION['mobile4']); }else{ $mobile4=""; }
if(isset($_SESSION['fax_areacode1'])){ $fax_areacode1=$_SESSION['fax_areacode1'];	unset($_SESSION['fax_areacode1']); }else{ $fax_areacode1=""; }
if(isset($_SESSION['fax1'])){ $fax1=$_SESSION['fax1'];	unset($_SESSION['fax1']); }else{ $fax1=""; }
if(isset($_SESSION['fax_areacode2'])){ $fax_areacode2=$_SESSION['fax_areacode2'];	unset($_SESSION['fax_areacode2']); }else{ $fax_areacode2=""; }
if(isset($_SESSION['fax2'])){ $fax2=$_SESSION['fax2'];	unset($_SESSION['fax2']); }else{ $fax2=""; }
if(isset($_SESSION['email_alt1'])){ $email_alt1=$_SESSION['email_alt1'];	unset($_SESSION['email_alt1']); }else{ $email_alt1=""; }
if(isset($_SESSION['email_alt2'])){ $email_alt2=$_SESSION['email_alt2'];	unset($_SESSION['email_alt2']); }else{ $email_alt2=""; }
if(isset($_SESSION['email_alt3'])){ $email_alt3=$_SESSION['email_alt3'];	unset($_SESSION['email_alt3']); }else{ $email_alt3=""; }
if(isset($_SESSION['website_alt'])){ $website_alt=$_SESSION['website_alt'];	unset($_SESSION['website_alt']); }else{ $website_alt=""; }

if(isset($_POST['btnUpdate'])){
    $uid = (int)($_SESSION['uid_indm'] ?? 0);
    
    // جلب البيانات من النموذج
    $name_prefix = mysqli_real_escape_string($con, $_POST['name_prefix'] ?? '');
    $fname = mysqli_real_escape_string($con, $_POST['fname'] ?? '');
    $lname = mysqli_real_escape_string($con, $_POST['lname'] ?? '');
    $mobile1 = mysqli_real_escape_string($con, $_POST['mobile1'] ?? '');
    $profile_photo = mysqli_real_escape_string($con, $_POST['profile_photo'] ?? '');
    
    // ✅ تحديث جدول user (بدون فاصلة زائدة)
    $sql_user = "UPDATE user SET 
        name_prefix = '$name_prefix',
        fname = '$fname',
        lname = '$lname',
        mobile1 = '$mobile1',
        image = '$profile_photo'
        WHERE usr_id = $uid";
    mysqli_query($con, $sql_user) or die("خطأ في تحديث user: " . mysqli_error($con));
    
    // تحديث جدول business_profile
    $userdesignation = mysqli_real_escape_string($con, $_POST['userdesignation'] ?? '');
    $ceo_prefix = mysqli_real_escape_string($con, $_POST['ceo_prefix'] ?? '');
    $ceo_fname = mysqli_real_escape_string($con, $_POST['ceo_fname'] ?? '');
    $ceo_lname = mysqli_real_escape_string($con, $_POST['ceo_lname'] ?? '');
    $company_name = mysqli_real_escape_string($con, $_POST['company_name'] ?? '');
    $address = mysqli_real_escape_string($con, $_POST['address'] ?? '');
    $address1 = mysqli_real_escape_string($con, $_POST['address1'] ?? '');
    $city = (int)($_POST['city'] ?? 0);
    $state = (int)($_POST['state'] ?? 0);
    $zipcode = mysqli_real_escape_string($con, $_POST['zipcode'] ?? '');
    $telephone_areacode1 = mysqli_real_escape_string($con, $_POST['telephone_areacode1'] ?? '');
    $telephone1 = mysqli_real_escape_string($con, $_POST['telephone1'] ?? '');
    $fax_areacode1 = mysqli_real_escape_string($con, $_POST['fax_areacode1'] ?? '');
    $fax1 = mysqli_real_escape_string($con, $_POST['fax1'] ?? '');
    $email_alt1 = mysqli_real_escape_string($con, $_POST['email_alt1'] ?? '');
    $website_alt = mysqli_real_escape_string($con, $_POST['website_alt'] ?? '');
    
    // ✅ تحديث business_profile
    $check = mysqli_query($con, "SELECT * FROM business_profile WHERE bnsprof_uid = $uid");
    if(mysqli_num_rows($check) > 0){
        $sql_profile = "UPDATE business_profile SET
            bnsprof_designation = '$userdesignation',
            bnsprof_ceoprefix = '$ceo_prefix',
            bnsprof_ceofname = '$ceo_fname',
            bnsprof_ceolname = '$ceo_lname',
            bnsprof_compname = '$company_name',
            bnsprof_address1 = '$address',
            bnsprof_address2 = '$address1',
            bnsprof_city = $city,
            bnsprof_state = $state,
            bnsprof_zipcode = '$zipcode',
            bnsprof_phcode1 = '$telephone_areacode1',
            bnsprof_ph1 = '$telephone1',
            bnsprof_faxcode1 = '$fax_areacode1',
            bnsprof_fax1 = '$fax1',
            bnsprof_emailalt1 = '$email_alt1',
            bnsprof_website_alt = '$website_alt'
            WHERE bnsprof_uid = $uid";
        mysqli_query($con, $sql_profile) or die("خطأ في تحديث business_profile: " . mysqli_error($con));
    } else {
        $sql_profile = "INSERT INTO business_profile SET
            bnsprof_uid = $uid,
            bnsprof_designation = '$userdesignation',
            bnsprof_ceoprefix = '$ceo_prefix',
            bnsprof_ceofname = '$ceo_fname',
            bnsprof_ceolname = '$ceo_lname',
            bnsprof_compname = '$company_name',
            bnsprof_address1 = '$address',
            bnsprof_address2 = '$address1',
            bnsprof_city = $city,
            bnsprof_state = $state,
            bnsprof_zipcode = '$zipcode',
            bnsprof_phcode1 = '$telephone_areacode1',
            bnsprof_ph1 = '$telephone1',
            bnsprof_faxcode1 = '$fax_areacode1',
            bnsprof_fax1 = '$fax1',
            bnsprof_emailalt1 = '$email_alt1',
            bnsprof_website_alt = '$website_alt',
            bnsprof_creation_date = NOW()";
        mysqli_query($con, $sql_profile) or die("خطأ في إدراج business_profile: " . mysqli_error($con));
    }
    
    header("Location: my-contactdetails.php");
    exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/c.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
	<style>
	.newAddimage{display: block;background: -moz-linear-gradient(center top , #FFFFFF, #F0F0F0) repeat scroll 0 0 transparent; background:-webkit-linear-gradient(top, #ffffff, #f0f0f0);background:-ms-linear-gradient(top, #ffffff, #f0f0f0);background:-o-linear-gradient(top, #ffffff, #f0f0f0);background-color:#f0f0f0;background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#ffffff), to(#f0f0f0)); color: #444444;cursor:pointer;position: absolute; width:125px;padding-bottom:1px;margin-left: 0px;filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f0f0f0'); BACKGROUND-COLOR: #f0f0f0; COLOR: #444444; CURSOR: pointer;}
	.file_button{position:relative;width:125px;height:123px;overflow:hidden;}
	.file_button .hiddenMask{position:absolute;top:-5px;right:-5px;z-index:2;filter:alpha(opacity=0);opacity:0;font-size:106px!important;cursor:pointer}
	.file_button .fadeButton{position:absolute;top:2px;left:0;z-index:1;}
	.label{ color: #000 !important;    font-size: 100%!important;line-height: 4!important;}
	input, button, select, textarea{height:40px;}
	.edit{font-size: 18px;}
	</style>
 <script type="text/javascript">

function list_photo()
{
	$.get("companylogo-list.php", {'uid' : <?php echo $uid; ?>}, 
	function(data){ 
	$('#list_photo').html(data); 
	});
}

function DelTempImage(imid)
{
   var cnf = confirm("Remove the Company Document?");
   if(cnf==true)
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
    </script>
    
<script type="text/javascript">
function updatecontactdet()
{
	var fname=document.getElementById('fname');
	var city=document.getElementById('city');
	var telephone_areacode1=document.getElementById('telephone_areacode1');
	var telephone1=document.getElementById('telephone1');
	var telephone_areacode2=document.getElementById('telephone_areacode2');
	var telephone2=document.getElementById('telephone2');
	var telephone_areacode3=document.getElementById('telephone_areacode3');
	var telephone3=document.getElementById('telephone3');
	var telephone_areacode4=document.getElementById('telephone_areacode4');
	var telephone4=document.getElementById('telephone4');
	var mobile1=document.getElementById('mobile1');
	var mobile2=document.getElementById('mobile2');
	var mobile3=document.getElementById('mobile3');
	var mobile4=document.getElementById('mobile4');
	var fax_areacode1=document.getElementById('fax_areacode1');
	var fax1=document.getElementById('fax1');
	var fax_areacode2=document.getElementById('fax_areacode2');
	var fax2=document.getElementById('fax2');
	var email_alt1=document.getElementById('email_alt1');
	var email_alt2=document.getElementById('email_alt2');
	var email_alt3=document.getElementById('email_alt3');
	var website_alt=document.getElementById('website_alt');
	var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/; 
	
    var message="";
    var valid=true;
   	if(fname.value=='')
	{
		message="Please enter first name";
		fname.focus();
		valid=false;
	}
	else if(city.value=='')
	{
		message="Please choose city";
		city.focus();
		valid=false;
	}
	else if(isNaN(telephone_areacode1.value))
	{
		message="Area code must be numeric";
		telephone_areacode1.focus();
		valid=false;	
	}
	else if(isNaN(telephone1.value))
	{
		message="Your telephone Number must be numeric";
		telephone1.focus();
		valid=false;	
	}
	else if(telephone_areacode1.value=='' && telephone1.value!='')
	{
		message="Please enter area code";
		telephone_areacode1.focus();
		valid=false;
	}
	else if(telephone1.value=='' && telephone_areacode1.value!='')
	{
		message="Please enter Telephone number";
		telephone1.focus();
		valid=false;
	}
	else if(isNaN(telephone_areacode2.value))
	{
		message="Area code must be numeric";
		telephone_areacode2.focus();
		valid=false;	
	}
	else if(isNaN(telephone2.value))
	{
		message="Your telephone Number must be numeric";
		telephone2.focus();
		valid=false;	
	}
	else if(telephone_areacode2.value=='' && telephone2.value!='')
	{
		message="Please enter area code";
		telephone_areacode2.focus();
		valid=false;
	}
	else if(telephone2.value=='' && telephone_areacode2.value!='')
	{
		message="Please enter Telephone number";
		telephone2.focus();
		valid=false;
	}
	else if(isNaN(telephone_areacode3.value))
	{
		message="Area code must be numeric";
		telephone_areacode3.focus();
		valid=false;	
	}
	else if(isNaN(telephone3.value))
	{
		message="Your telephone Number must be numeric";
		telephone3.focus();
		valid=false;	
	}
	else if(telephone_areacode3.value=='' && telephone3.value!='')
	{
		message="Please enter area code";
		telephone_areacode3.focus();
		valid=false;
	}
	else if(telephone3.value=='' && telephone_areacode3.value!='')
	{
		message="Please enter Telephone number";
		telephone3.focus();
		valid=false;
	}
	else if(isNaN(telephone_areacode4.value))
	{
		message="Area code must be numeric";
		telephone_areacode4.focus();
		valid=false;	
	}
	else if(isNaN(telephone4.value))
	{
		message="Your telephone Number must be numeric";
		telephone4.focus();
		valid=false;	
	}
	else if(telephone_areacode4.value=='' && telephone4.value!='')
	{
		message="Please enter area code";
		telephone_areacode4.focus();
		valid=false;
	}
	else if(telephone4.value=='' && telephone_areacode4.value!='')
	{
		message="Please enter Telephone number";
		telephone4.focus();
		valid=false;
	}
	else if(isNaN(mobile1.value))
	{
		message="Please enter only numbers in mobile field.";
		telephone4.focus();
		valid=false;	
	}
	else if((mobile1.value.length > 10 && mobile1.value!='') || (mobile1.value.length < 10 && mobile1.value!=''))
	{
		message="Your Mobile Number should be 10 digits";
		mobile1.focus();
		valid=false;
	}
	else if(isNaN(mobile2.value))
	{
		message="Please enter only numbers in mobile field.";
		mobile2.focus();
		valid=false;	
	}
	else if((mobile2.value.length > 10 && mobile2.value!='') || (mobile2.value.length < 10 && mobile2.value!=''))
	{
		message="Your Mobile Number should be 10 digits";
		mobile2.focus();
		valid=false;
	}
	else if(isNaN(mobile3.value))
	{
		message="Please enter only numbers in mobile field.";
		mobile3.focus();
		valid=false;	
	}
	else if((mobile3.value.length > 10 && mobile3.value!='') || (mobile3.value.length < 10 && mobile3.value!=''))
	{
		message="Your Mobile Number should be 10 digits";
		mobile3.focus();
		valid=false;
	}
	else if(isNaN(mobile4.value))
	{
		message="Please enter only numbers in mobile field.";
		mobile4.focus();
		valid=false;	
	}
	else if((mobile4.value.length > 10 && mobile4.value!='') || (mobile4.value.length < 10 && mobile4.value!=''))
	{
		message="Your Mobile Number should be 10 digits";
		mobile4.focus();
		valid=false;
	}
	else if(isNaN(fax_areacode1.value))
	{
		message="Area code should be number.";
		fax_areacode1.focus();
		valid=false;	
	}
	else if(isNaN(fax1.value))
	{
		message="Fax (Number) should be number.";
		fax1.focus();
		valid=false;	
	}
	else if(fax_areacode1.value=='' && fax1.value!='')
	{
		message="Please enter area code";
		fax_areacode1.focus();
		valid=false;
	}
	else if(fax1.value=='' && fax_areacode1.value!='')
	{
		message="Please enter fax number";
		fax1.focus();
		valid=false;
	}
	else if(isNaN(fax_areacode2.value))
	{
		message="Area code should be number.";
		fax_areacode2.focus();
		valid=false;	
	}
	else if(isNaN(fax2.value))
	{
		message="Fax (Number) should be number.";
		fax2.focus();
		valid=false;	
	}
	else if(fax_areacode2.value=='' && fax2.value!='')
	{
		message="Please enter area code";
		fax_areacode2.focus();
		valid=false;
	}
	else if(fax2.value=='' && fax_areacode2.value!='')
	{
		message="Please enter fax number";
		fax2.focus();
		valid=false;
	}
	else if (email_alt1.value!="" && !email_alt1.value.match(is_email))
    {
		message="Please enter valid email";
        email_alt1.focus();
        valid = false;		
    }
	else if (email_alt2.value!="" && !email_alt2.value.match(is_email))
    {
		message="Please enter valid email";
        email_alt2.focus();
        valid = false;		
    }
	else if (email_alt3.value!="" && !email_alt3.value.match(is_email))
    {
		message="Please enter valid email";
        email_alt3.focus();
        valid = false;		
    }
	else if(website_alt.value != '' && !website_alt.value.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/))
	{
		message='Please Enter a Valid Web Link';
		website_alt.focus();
		valid=false;
	}
	if(!valid)
	{
		document.getElementById('updatemessage').style.color = "red";
		document.getElementById('updatemessage').innerHTML = message;	
	}
	return valid;
}


$(function () {
    
    // Change this to the location of your server-side upload handler:
    var url ='/server/php/';
    jQuery('#profileupload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
                jQuery('#profile_photo').val(file.name);
			    jQuery('#profilephoto1').attr('src',file.thumbnailUrl);
            });
        }
    })
});


$(function () {
    
    // Change this to the location of your server-side upload handler:
    var url ='http://egyptmart.shop/server/php/';
    jQuery('#fileupload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
               jQuery('#business_documents').val(file.name);
			   jQuery('#business_doc').attr('src',file.thumbnailUrl);
                       list_photo();	

            });
        }
       
    })
});

$(function () {
    
    // Change this to the location of your server-side upload handler:
    var url ='http://egyptmart.shop/server/php/';
    jQuery('#file_upload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
				jQuery.post("companylogo-update.php", {'uid' :'<?php echo $uid; ?>', 'file' : file.name }, function(data) {
						list_photo();	
				});

            });
        }
       
    })
});


</script>

<script>
function showEditForm() {
    document.getElementById('eform').style.display = 'block';
    document.getElementById('cview').style.display = 'none';
    document.getElementById('savecd').style.display = 'none';
    document.getElementById('edi1').style.display = 'none';
}

function headOfficesv()
{
$('#savecd').show();
$('#eform').show();
$('#cview').hide();
$('#edi1').hide();
}

function shownewblk()
{
$('#adnewb').show();
$('.hideup').hide();
$('.shedfrm').hide(); 
$('.hdedfrm').show();
$('.abc').show();
}

function hidenewblk()
{
$('#adnewb').hide();	
}

function stedit(id)
{
$('#cnctedit'+id).show();
$('#cnctlist'+id).hide();
$('#seditbt'+id).hide();
$('#sedsvbt'+id).show();
$('#sdel'+id).hide();
$('.abtListdv').hide();
}

function cnctdiscard(id)
{
$('#sedsvbt'+id).hide();
$('#sdel'+id).show();
$('#seditbt'+id).show();
$('#cnctlist'+id).show();
$('#cnctedit'+id).hide();
}
</script>

<style>
    #eform { display: none; }
    #cview { display: block; }
    .mp8, .mp7, .mp10 { margin-bottom: 10px; }
    input, select, textarea { width: 100%; padding: 8px; margin: 5px 0; }
    .saps { background: #25D366; color: white; border: none; padding: 10px 20px; cursor: pointer; }
</style>

</head>

<body>
<div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
        <br>
	<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

    <?php include 'includes/header_menu.php';?>
		<!--left navigation:start-->
<?php include 'includes/left_menu.php';?>		
		<!--left navigation:ends-->
        <div class="w56 f1 p2b p14 blr">			
		<form name="MarketListing" id="MarketListing" method="post" action="">
        <input id="mktlist" name="mktlist" type="hidden"></form><a name="cnt"></a>
<!--body:start-->

     
<!--<div style="float:right; width:300px;><SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">showBackRef();</SCRIPT></div>-->
	<div>
	<!--<div class="bc f11>Company Profile &raquo;</div>-->
    <h1>معلومات الإتصال بالشركــة</h1>
	</div>
<div style="display: block;" id="msgsave"> <p class="aml"></p><div id="re_link" class="utab" style="height:36px;"><span style="font-size: 15px;*float:left;" title=" Complete &amp; Updated contact information gives your buyers more options to contact you." > حدث معلومات الإتصال بشركتك حتى تعطى المشتريين خيارات متعدده للإتصال بالشركة</span></div> </div>
	<div class="mt5">

<noscript><div class="ebox" >Please check that  either your browser does not support JavaScript, or it might be disabled. You need to enable your browser's javascript in order to work with this form.</div></noscript>
<div class="ma sbox bnr fw lh" id="div_succ" style="display:none;"></div>
    <!--contact details:start-->
    <div class="mp1 tl bx" title="حدث معلومات الإتصال بشركتك حتى تعطى المشتريين خيارات متعدده للإتصال بالشركة ">
<div class="mp2 bnr">
    <a href="javascript:void(0);" onclick="showEditForm();" class="sl f2 edit mt c bnr">تغـيير</a>
</div>
	</div>
	<div class="mp10">
	
    <div class="clb"></div>
<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
<script>
$(document).ready(function(){ 
	$("#city_others").autocomplete("ajax-file/showcity.php", {
		selectFirst: true
	})
	.result(function(event, data, formatted) {
					
	  var dm =	data[0].split(">>");
 	  $("#city_others").val(dm[0]);
	  $("#stateid").val(dm[1]);
      $("#city").val(data[1]);
	  $("#state").val(data[2]);
	  $("#reset").show();
	  //$("input#country_name").attr('disabled','disabled');
	});
});
		
$(document).ready(function(){
	/*$("#designation").autocomplete("ajax-file/showdesignation.php", {
		selectFirst: true
	})
	.result(function(event, data, formatted) {
 	  $("#userdesignation").val(data[1]);
	});*/
});		


function blankcity()
{
 $("#city_others").val('');	
 $("#city").val('');
  $("#stateid").val('');	
 $("#state").val('');
}

function blankdesignation()
{
 $("#designation").val('');	
  $("#userdesignation").val('');	
}
</script>

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
		</script>

    
    
    
    
    
    
    <!-- ============================================= -->
<!-- EFORM (نموذج التعديل) -->
<!-- ============================================= -->
<div class="mp10 shedfrm" id="eform" style="display: none;">
    <form name="frmHeadOffice" action="" method="post" enctype="multipart/form-data">
        <div style="text-align:left; padding:1%; margin-left:87px;" id="updatemessage"></div>
        
        <?php
        // جلب جميع بيانات business_profile مرة واحدة
        $sql_profile = mysqli_query($con, "SELECT * FROM business_profile WHERE bnsprof_uid = $uid");
        $profile_data = mysqli_fetch_assoc($sql_profile);
        
        // جلب اسم الصورة من جدول user
        $img_sql = mysqli_query($con, "SELECT image FROM user WHERE usr_id = $uid");
        $img_row = mysqli_fetch_assoc($img_sql);
        $image_name = $img_row['image'] ?? '';
        $image_url = '';
        if (!empty($image_name) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/server/php/files/thumbnail/' . $image_name)) {
            $image_url = 'https://egyptmart.shop/server/php/files/thumbnail/' . $image_name;
        }
        ?>
        
        <table align="left" border="0" cellpadding="4" cellspacing="0" width="490">
            <tbody>
                <!-- مسئول الاتصال -->
                <tr>
                    <td class="label" width="160"><span>*</span> مسئول الاتصال المعين من الشركة</td>
                    <td>
                        <select name="name_prefix" id="name_prefix" style="width: 59px;">
                            <?php foreach(['Mr.','Ms.','Mrs.','Dr.'] as $val): ?>
                                <option value="<?php echo $val; ?>" <?php echo ($val == user_info($uid, 'name_prefix')) ? 'selected' : ''; ?>><?php echo $val; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="fname" id="fname" value="<?php echo htmlspecialchars(user_info($uid, 'fname')); ?>" style="width:120px;">
                        <input type="text" name="lname" id="lname" value="<?php echo htmlspecialchars(user_info($uid, 'lname')); ?>" style="width:120px;">
                     </td>
                 </tr>
                
                <!-- المسمى الوظيفى -->
                <tr>
                    <td class="label">المسمى الوظيفى</td>
                    <td><input type="text" name="userdesignation" id="userdesignation" value="<?php echo htmlspecialchars($profile_data['bnsprof_designation'] ?? ''); ?>"></td>
                 </tr>
                
                <!-- إسم رئيس الشركة -->
                <tr>
                    <td class="label">إسم رئيس الشركة</td>
                    <td>
                        <select name="ceo_prefix" id="ceo_prefix" style="width: 59px;">
                            <?php foreach(['Mr.','Ms.','Mrs.','Dr.'] as $val): ?>
                                <option value="<?php echo $val; ?>" <?php echo ($val == ($profile_data['bnsprof_ceoprefix'] ?? '')) ? 'selected' : ''; ?>><?php echo $val; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="ceo_fname" id="ceo_fname" value="<?php echo htmlspecialchars($profile_data['bnsprof_ceofname'] ?? ''); ?>" style="width:120px;">
                        <input type="text" name="ceo_lname" id="ceo_lname" value="<?php echo htmlspecialchars($profile_data['bnsprof_ceolname'] ?? ''); ?>" style="width:120px;">
                     </td>
                 </tr>
                
                <!-- إسم الشركة -->
                <tr>
                    <td class="label">إسم الشركة</td>
                    <td><input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($profile_data['bnsprof_compname'] ?? ''); ?>"></td>
                 </tr>
                
                <!-- العنوان -->
                <tr>
                    <td class="label">عنوان الشركة</td>
                    <td>
                        <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($profile_data['bnsprof_address1'] ?? ''); ?>"><br>
                        <input type="text" name="address1" id="address1" value="<?php echo htmlspecialchars($profile_data['bnsprof_address2'] ?? ''); ?>">
                     </td>
                 </tr>
                
                <!-- المدينة، المحافظة، الرمز البريدى -->
                <tr>
                    <td class="label">المدينة، المحافظة، الرمز البريدى</td>
                    <td>
                        <input type="text" id="city_others" name="city_others" placeholder="Search city..." value="<?php echo htmlspecialchars(get_city_name((int)($profile_data['bnsprof_city'] ?? 0))); ?>" style="width:220px;">
                        <input type="hidden" name="city" id="city" value="<?php echo (int)($profile_data['bnsprof_city'] ?? 0); ?>">
                        <input type="text" name="stateid" id="stateid" placeholder="State" value="<?php echo htmlspecialchars(get_state_name((int)($profile_data['bnsprof_state'] ?? 0))); ?>" style="width:100px;">
                        <input type="hidden" name="state" id="state" value="<?php echo (int)($profile_data['bnsprof_state'] ?? 0); ?>">
                        <input type="text" name="zipcode" id="zipcode" placeholder="ZIP Code" value="<?php echo htmlspecialchars($profile_data['bnsprof_zipcode'] ?? ''); ?>" style="width:100px;">
                     </td>
                 </tr>
                
                <!-- البلد -->
                <tr>
                    <td class="label">البــلد</td>
                    <td>
                        <input type="text" name="country_name" value="<?php echo htmlspecialchars(get_country_name(user_info($uid, 'country'))); ?>" readonly style="background:#eaeaea;">
                        <input type="hidden" name="country" value="IN">
                     </td>
                 </tr>
                
                <!-- تليفون أرضى 1 -->
                <!-- تليفون أرضى 1 مع كود البلد -->
<tr>
    <td class="label">تليفـون أرضى 1</td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <!-- كود البلد -->
                    <td width="50">
                        <input type="text" readonly 
                               value="<?php echo htmlspecialchars(user_info($uid, 'country_ph_code')); ?>" 
                               style="background-color:#eaeaea; width:60px;" 
                               id="ph_country" name="ph_country">
                    </td>
                    <!-- كود المنطقة -->
                    <td width="60">
                        <input type="text" name="telephone_areacode1" id="telephone_areacode1" 
                               value="<?php echo htmlspecialchars($profile_data['bnsprof_phcode1'] ?? ''); ?>" 
                               placeholder="كود المنطقة" style="width:80px;">
                    </td>
                    <!-- رقم التليفون -->
                    <td>
                        <input type="text" name="telephone1" id="telephone1" 
                               value="<?php echo htmlspecialchars($profile_data['bnsprof_ph1'] ?? ''); ?>" 
                               placeholder="رقم التليفون" style="width:180px;">
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
                
                <!-- تليفون محمول أساسى -->
                <!-- كود البلد + تليفون محمول أساسى (بنفس نمط التليفون الأرضى) -->
<tr>
    <td class="label">كود البلد / تليفـون محمول أساسى</td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td width="50">
                        <input type="text" readonly 
                               value="<?php echo htmlspecialchars(user_info($uid, 'country_ph_code')); ?>" 
                               style="background-color:#eaeaea; width:60px;" 
                               id="mob_country" name="mob_country">
                    </td>
                    <td width="60">
                        <div id="a18" class="tbp tbm11" style="display:none">
                            <div class="t1a" align="left">Please enter your mobile / cell phone number here.<br>
                            <strong>Example: +91-9696969696</strong>.</div>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="mobile1" id="mobile1" 
                               value="<?php echo htmlspecialchars(user_info($uid, 'mobile1')); ?>" 
                               maxlength="40" class="a_f ml8 mo_n" style="width:180px" 
                               onfocus="javascript:ntt('q18','a18');" onblur="nhid('a18');mobi('mobile','mo','mob','im_gsm');">
                    </td>
                </tr>
            </tbody>
        </table>
        <span id="mo" class="em" style="display:none"></span>
        <span id="mob" class="em" style="display:none"></span>
        <span id="mob_exist" class="em" style="display:none"></span>
        <input id="mob_flag" name="mob_flag" value="0" type="hidden">
    </td>
</tr>
                <!-- فاكس -->
               <!-- فاكس مع كود البلد -->
<tr>
    <td class="label">فاكس</td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <!-- كود البلد -->
                    <td width="50">
                        <input type="text" readonly 
                               value="<?php echo htmlspecialchars(user_info($uid, 'country_ph_code')); ?>" 
                               style="background-color:#eaeaea; width:60px;" 
                               id="fax_country" name="fax_country">
                    </td>
                    <!-- كود المنطقة -->
                    <td width="60">
                        <input type="text" name="fax_areacode1" id="fax_areacode1" 
                               value="<?php echo htmlspecialchars($profile_data['bnsprof_faxcode1'] ?? ''); ?>" 
                               placeholder="كود المنطقة" style="width:80px;">
                    </td>
                    <!-- رقم الفاكس -->
                    <td>
                        <input type="text" name="fax1" id="fax1" 
                               value="<?php echo htmlspecialchars($profile_data['bnsprof_fax1'] ?? ''); ?>" 
                               placeholder="رقم الفاكس" style="width:180px;">
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
                
                <!-- البريد الألكترونى الأساسى (يظهر فقط إذا لم يكن OAuth) -->
               <!-- البريد الألكترونى الأساسى -->
<?php if(user_info($uid, 'usr_oauth_reg') == '0'): ?>
<tr>
    <td class="label">البريد الألكترونى الأساسى</td>
    <td>
        <input type="text" name="email" id="email" 
               value="<?php echo htmlspecialchars(user_info($uid, 'email')); ?>" 
               readonly style="background:#eaeaea; width:250px;">
    </td>
</tr>
<!-- رابط تغيير البريد الإلكترونى (يظهر فقط للحسابات العادية) -->
<tr>
    <td width="160">&nbsp;</td>
    <td>
        <a style="color:#25D366; text-decoration:none;" class="ajax" 
           href="change-email.php" tiptitle="Change your primary Email" 
           id="changepe1">
            <i class="fa fa-envelope"></i> تغيير البريد الإلكترونى الأساسى
        </a>
    </td>
</tr>
<?php else: ?>
<!-- للحسابات المسجلة عبر OAuth (فيسبوك، جوجل، إلخ) -->
<tr>
    <td class="label">البريد الألكترونى الأساسى</td>
    <td>
        <label id="lbl_email"><?php echo htmlspecialchars(user_info($uid, 'email')); ?></label>
        <div class="mp8">Logged In With</div>
        <div class="mp7">
            <?php if(user_info($uid, 'usr_oauth_reg') == '1'): ?>
                <img src="social_media_images/facebook_logo.jpg" />
            <?php elseif(user_info($uid, 'usr_oauth_reg') == '2'): ?>
                <img src="social_media_images/gmail_.png" />
            <?php elseif(user_info($uid, 'usr_oauth_reg') == '3'): ?>
                <img src="social_media_images/twtBrd.jpg" />
            <?php elseif(user_info($uid, 'usr_oauth_reg') == '4'): ?>
                <img src="social_media_images/linkedinLog.png" />
            <?php endif; ?>
        </div>
    </td>
</tr>
<?php endif; ?>
                
                <!-- صورة مسئول الاتصال (مرة واحدة فقط) -->
                <tr>
                    <td class="label">صورة مسئول الاتصال</td>
                    <td>
                        <div class="upload_div">
                            <input type="hidden" name="profile_photo" id="profile_photo" value="<?php echo htmlspecialchars($image_name); ?>">
                            <img src="<?php echo !empty($image_url) ? $image_url : 'http://egyptmart.shop/images/uploadd.png'; ?>" 
                                 width="110" id="profilephoto1" height="130" 
                                 style="margin-bottom:10px; display:block;">
                            <input id="profileupload" type="file" name="files">
                            <span class="file_input">أضف صورة</span>
                        </div>
                     </td>
                 </tr>
                
                <!-- زر الحفظ -->
                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <input type="hidden" name="userd" value="<?php echo $uid; ?>">
                        <input type="submit" class="saps" value="إحفـظ التغييـرات" name="btnUpdate">
                     </td>
                 </tr>
            </tbody>
         </table>
    </form>
</div>
<!-- ============================================= -->
<!-- JavaScript للتبديل بين CVIEW و EFORM -->
<!-- ============================================= -->
<script>
function showEditForm() {
    document.getElementById('eform').style.display = 'block';
    document.getElementById('cview').style.display = 'none';
}
</script>


<!-- ============================================= -->
<!-- CVIEW (وضع العرض) -->
<!-- ============================================= -->
<div class="mp10" id="cview" style="background:#f5f5f5; padding:20px; border-radius:8px; overflow:auto; min-height:400px;">
    <!-- كل محتوى cview هنا -->

    <!-- مسئول الاتصال (مضاف) -->
    <div class="mp8"><strong>مسئول الاتصال:</strong></div>
    <div class="mp7">
        <?php 
        $sql_contact = mysqli_query($con, "SELECT name_prefix, fname, lname FROM user WHERE usr_id = $uid");
        $contact = mysqli_fetch_assoc($sql_contact);
        echo htmlspecialchars(($contact['name_prefix'] ?? '') . ' ' . ($contact['fname'] ?? '') . ' ' . ($contact['lname'] ?? ''));
        ?>
    </div>

    <!-- المسمى الوظيفى -->
    <div class="mp8"><strong>المسمى الوظيفى:</strong></div>
    <div class="mp7">
        <?php 
        $sql1 = mysqli_query($con, "SELECT bnsprof_designation FROM business_profile WHERE bnsprof_uid = $uid");
        $row1 = mysqli_fetch_assoc($sql1);
        echo htmlspecialchars($row1['bnsprof_designation'] ?? '');
        ?>
    </div>

    <!-- إسم رئيس مجلس الادارة -->
    <div class="mp8"><strong>إسم رئيس مجلس الادارة:</strong></div>
    <div class="mp7">
        <?php 
        $sql2 = mysqli_query($con, "SELECT bnsprof_ceoprefix, bnsprof_ceofname, bnsprof_ceolname FROM business_profile WHERE bnsprof_uid = $uid");
        $row2 = mysqli_fetch_assoc($sql2);
        echo htmlspecialchars(($row2['bnsprof_ceoprefix'] ?? '') . ' ' . ($row2['bnsprof_ceofname'] ?? '') . ' ' . ($row2['bnsprof_ceolname'] ?? ''));
        ?>
    </div>

    <!-- إسم الشركة -->
    <div class="mp8"><strong>إسم الشركة:</strong></div>
    <div class="mp7">
        <?php 
        $sql_comp = mysqli_query($con, "SELECT bnsprof_compname FROM business_profile WHERE bnsprof_uid = $uid");
        $row_comp = mysqli_fetch_assoc($sql_comp);
        echo htmlspecialchars($row_comp['bnsprof_compname'] ?? '');
        ?>
    </div>

    <!-- العنوان -->
    <div class="mp8"><strong>العنوان:</strong></div>
    <div class="mp7">
        <?php 
        $sql4 = mysqli_query($con, "SELECT bnsprof_address1, bnsprof_address2 FROM business_profile WHERE bnsprof_uid = $uid");
        $row4 = mysqli_fetch_assoc($sql4);
        echo htmlspecialchars(($row4['bnsprof_address1'] ?? '') . ' ' . ($row4['bnsprof_address2'] ?? ''));
        ?>
    </div>

    <!-- المدينة، المحافظة، الرمز البريدى -->
    <div class="mp8"><strong>المدينة، المحافظة، الرمز البريدى:</strong></div>
    <div class="mp7">
        <?php 
        $sql5 = mysqli_query($con, "SELECT bnsprof_city, bnsprof_state, bnsprof_zipcode FROM business_profile WHERE bnsprof_uid = $uid");
        $row5 = mysqli_fetch_assoc($sql5);
        $city_name = get_city_name((int)($row5['bnsprof_city'] ?? 0));
        $state_name = get_state_name((int)($row5['bnsprof_state'] ?? 0));
        echo htmlspecialchars($city_name . '، ' . $state_name . '، ' . ($row5['bnsprof_zipcode'] ?? ''));
        ?>
    </div>

    <!-- البلد -->
    <div class="mp8"><strong>البــلد:</strong></div>
    <div class="mp7"><?php echo get_country_name(user_info($uid, 'country')); ?></div>

    <!-- تليفون أرضى 1 -->
<div class="mp8"><strong>تليفــون أرضى 1:</strong></div>
<div class="mp7">
    <?php 
    $tel_sql = mysqli_query($con, "SELECT bnsprof_phcode1, bnsprof_ph1 FROM business_profile WHERE bnsprof_uid = $uid");
    $tel_row = mysqli_fetch_assoc($tel_sql);
    $tel_code = $tel_row['bnsprof_phcode1'] ?? '';
    $tel_number = $tel_row['bnsprof_ph1'] ?? '';
    if (!empty($tel_code) || !empty($tel_number)) {
        echo htmlspecialchars(user_info($uid, 'country_ph_code') . '-' . $tel_code . '-' . $tel_number);
    } else {
        echo 'غير مسجل';
    }
    ?>
</div>

    <!-- تليفون محمول أساسى -->
    <div class="mp8"><strong>تليفــون محمول أساسى:</strong></div>
    <div class="mp7"><?php echo user_info($uid, 'country_ph_code') . '-' . user_info($uid, 'mobile1'); ?></div>

   <!-- فاكس -->
<div class="mp8"><strong>فاكس:</strong></div>
<div class="mp7">
    <?php 
    $fax_sql = mysqli_query($con, "SELECT bnsprof_faxcode1, bnsprof_fax1 FROM business_profile WHERE bnsprof_uid = $uid");
    $fax_row = mysqli_fetch_assoc($fax_sql);
    $fax_code = $fax_row['bnsprof_faxcode1'] ?? '';
    $fax_number = $fax_row['bnsprof_fax1'] ?? '';
    if (!empty($fax_code) || !empty($fax_number)) {
        echo htmlspecialchars(user_info($uid, 'country_ph_code') . '-' . $fax_code . '-' . $fax_number);
    } else {
        echo 'غير مسجل';
    }
    ?>
</div>
    <!-- البريد الألكترونى الأساسى -->
    <div class="mp8"><strong>البريد الألكترونى الأساسى:</strong></div>
    <div class="mp7">
        <?php if(user_info($uid, 'usr_oauth_reg') == '0'): ?>
            <?php echo user_info($uid, 'email'); ?>
        <?php else: ?>
            <label id="lbl_email"><?php echo user_info($uid, 'email'); ?></label>&nbsp;&nbsp;&nbsp;
            <div class="mp8">Logged In With</div>
            <div class="mp7">
                <?php if(user_info($uid, 'usr_oauth_reg') == '1'): ?>
                    <img src="social_media_images/facebook_logo.jpg" />
                <?php elseif(user_info($uid, 'usr_oauth_reg') == '2'): ?>
                    <img src="social_media_images/gmail_.png" />
                <?php elseif(user_info($uid, 'usr_oauth_reg') == '3'): ?>
                    <img src="social_media_images/twtBrd.jpg" />
                <?php elseif(user_info($uid, 'usr_oauth_reg') == '4'): ?>
                    <img src="social_media_images/linkedinLog.png" />
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    
<!-- صورة مسئول الاتصال -->
<tr>
   
    <td>
        <div class="upload_div">
            <input type="hidden" name="profile_photo" id="profile_photo" value="<?php echo htmlspecialchars(user_info($uid, 'image')); ?>">
            
            <?php 
            // جلب اسم ملف الصورة من قاعدة البيانات
            $img_sql = mysqli_query($con, "SELECT image FROM user WHERE usr_id = $uid");
            $img_row = mysqli_fetch_assoc($img_sql);
            $image_name = $img_row['image'] ?? '';
            $image_url = '';
            
            // بناء المسار الكامل للصورة
            if (!empty($image_name) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/server/php/files/thumbnail/' . $image_name)) {
                $image_url = 'https://egyptmart.shop/server/php/files/thumbnail/' . $image_name;
            }
            ?>
            
            <!-- عرض الصورة الحالية (إذا وجدت) أو الصورة الافتراضية -->
            <img src="<?php echo !empty($image_url) ? $image_url : 'http://egyptmart.shop/images/uploadd.png'; ?>" 
                 width="110" id="profilephoto1" height="130" 
                 style="margin-bottom:10px; display:block;">
            
            <input id="profileupload" type="file" name="files">
            <span class="file_input">أضف صورة</span>
            <small style="display:block; margin-top:5px;">(سيتم استبدال الصورة القديمة عند رفع صورة جديدة)</small>
        </div>
    </td>
</tr>
	
	
	
	
	
	
	

	
	<!--<div class="mz fr"><div class="nw6 f11 fr wi1" style="border-bottom: medium none;"><div id="complete_width_text">Contact Completed 30%&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="javascript:headOfficesv();" id="edit2" class="sl ct">Edit</a></div><div class="pc" id="complete_width">
    <img src="images/pm2.png" alt="" border="0" width="30%" height="10">
    </div></div>
          </div>-->

    </div>
    <div class="clb"></div>
    </div>

	
    </div>
    <div class="clb">&nbsp;</div>
    </div>
    <!--contact details:ends-->
    
    <script type="text/javascript">
    function allcnct_list(){
	$.get("ajax-file/allcontact-list.php", 
	 function(data){ 
	$('#allcnctlist').html(data);
	//setTimeout("allcnct_list()",5000);
	 });
	}
	
	function delete_contact(id)
	{
	var r=confirm("Are you sure you want to delete this contact");
	if (r==true)
	  {
	 $.get("ajax-file/delete-contact.php", {id:id},
	 function(data){ 
	//allcnct_list();
	 });
	  }
	}
</script>
    
    
    <div id="all_contact">
    <!--<script type="text/javascript">allcnct_list();</script>-->
	<div id="allcnctlist"></div>
	<!--add new contact:start-->

    <div class="mp1 tl bx abtListdv" style="display:none;" id="adnewb">
    <div class="mp10" id="cd">
    <div class="mz">
    <div class="f1 ac1"><h2>Add New Contact</h2></div>

    <a href="javascript:hidenewblk();" class="sl f2 bnr c close mt">Discard</a>
	<a href="javascript:addContact();" class="sl f2 bnr c sav mt"title="Save">إحفظ التغييرات</a>


    <div class="clb"></div>
    </div>
<script>
$(document).ready(function(){
	$("#country_nm").autocomplete("ajax-file/autocomplete_country.php", {
		selectFirst: true
	})
	.result(function(event, data, formatted) {
      $("#comp_cnt_country").val(data[1]);
	  $("#phcntode1").val(data[2]);
	  $("#phcntode2").val(data[2]);
	  $("#phcntode3").val(data[2]);
	  $('#comp_cnt_phcntode').val(data[2])
	});
});

function addContact() 
{
var division=$('#comp_cnt_division').val();
var prefix=$('#comp_cnt_prefix').val();
var fname=$('#comp_cnt_fname').val();
var lname=$('#comp_cnt_lname').val();
var address=$('#comp_cnt_address').val();
var address1=$('#comp_cnt_address1').val();
var country=$('#comp_cnt_country').val();
var phareacode=$('#comp_cnt_phareacode').val();
var telephone=$('#comp_cnt_telephone').val();
var mobile=$('#comp_cnt_mobile').val();
var faxareacode=$('#comp_cnt_faxareacode').val();
var fax=$('#comp_cnt_fax').val();
var email=$('#comp_cnt_email').val();
var phcode=$('#comp_cnt_phcntode').val();
var country_nm =$('#country_nm').val();
var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

if(country_nm=="")
{
alert("Kindly select the Country.");
}
else if(isNaN(phareacode))
{
alert("Phone area code must be numeric");
}
else if(isNaN(telephone))
{
alert("Telephone number must be numeric");
}
else if(phareacode=="" && telephone!="")
{
alert("Kindly enter phone area code");
}
else if(telephone=="" && phareacode!="")
{
alert("Please enter telephone Number");
}
else if(isNaN(mobile))
{
alert("Mobile number must be numeric");
}
else if(isNaN(faxareacode))
{
alert("Fax area code must be numeric");
}
else if(isNaN(fax))
{
alert("Fax number must be numeric");
}
else if(faxareacode=="" && fax!="")
{
alert("Kindly enter fax area code");
}
else if(fax=="" && faxareacode!="")
{
alert("Please enter fax Number");
}
else if(email!="" && !email.match(is_email))
{
alert("Kindly enter valid email");
}
else
{
	$.get("ajax-file/addContact.php", {division:division,
	prefix:prefix,fname:fname,lname:lname,address:address,address1:address1,country:country,phareacode:phareacode,telephone:telephone,mobile:mobile,faxareacode:faxareacode,fax:fax,email:email,phcode:phcode},
	function(data){
	$('#comp_cnt_division').val(' ');
	$('#comp_cnt_fname').val(' ');
	$('#comp_cnt_lname').val(' ');
	$('#comp_cnt_address').val(' ');
	$('#comp_cnt_address1').val(' ');
	$('#comp_cnt_country').val(' ');
	$('#comp_cnt_phareacode').val(' ');
	$('#comp_cnt_telephone').val(' ');
	$('#comp_cnt_mobile').val(' ');
	$('#comp_cnt_faxareacode').val(' ');
	$('#comp_cnt_fax').val(' ');
	$('#comp_cnt_email').val(' ');
	$('#comp_cnt_phcntode').val(' ');
	$('#country_nm').val(' ');
	//allcnct_list();
	});	
}
}

function editContact(id)
{
var division=$('#comp_cnt_division1'+id).val();
var prefix=$('#comp_cnt_prefix1'+id).val();
var fname=$('#comp_cnt_fname1'+id).val();
var lname=$('#comp_cnt_lname1'+id).val();
var address=$('#comp_cnt_address1'+id).val();
var address1=$('#comp_cnt_address2'+id).val();
var phareacode=$('#comp_cnt_phareacode1'+id).val();
var telephone=$('#comp_cnt_telephone1'+id).val();
var mobile=$('#comp_cnt_mobile1'+id).val();
var faxareacode=$('#comp_cnt_faxareacode1'+id).val();
var fax=$('#comp_cnt_fax1'+id).val();
var email=$('#comp_cnt_email1'+id).val();
var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

if(isNaN(phareacode))
{
alert("Phone area code must be numeric");
}
else if(isNaN(telephone))
{
alert("Telephone number must be numeric");
}
else if(phareacode=="" && telephone!="")
{
alert("Kindly enter phone area code");
}
else if(telephone=="" && phareacode!="")
{
alert("Please enter telephone Number");
}
else if(isNaN(mobile))
{
alert("Mobile number must be numeric");
}
else if(isNaN(faxareacode))
{
alert("Fax area code must be numeric");
}
else if(isNaN(fax))
{
alert("Fax number must be numeric");
}
else if(faxareacode=="" && fax!="")
{
alert("Kindly enter fax area code");
}
else if(fax=="" && faxareacode!="")
{
alert("Please enter fax Number");
}
else if(email!="" && !email.match(is_email))
{
alert("Kindly enter valid email");
}
else
{
$.get("ajax-file/editContact.php", {id:id,division:division,
	prefix:prefix,fname:fname,lname:lname,address:address,address1:address1,phareacode:phareacode,telephone:telephone,mobile:mobile,faxareacode:faxareacode,fax:fax,email:email},
	function(data){
	//allcnct_list();
	});	
}
}
</script>

<script>
function showEditForm() {
    document.getElementById('eform').style.display = 'block';
    document.getElementById('cview').style.display = 'none';
}
</script>







    <div class="mp10">
	<form action="" name="frmNewContact" method="post">
	<div>
	<table align="left" border="0" cellpadding="4" cellspacing="0" width="490">
	<tbody><tr>
		<td class="label" width="160">Division</td>
		<td>
		<select name="comp_cnt_division" id="comp_cnt_division" class="a_f" tabindex="1">
		<option value="">Select a Division</option>
		<?php
        $divisionres=mysqli_query($con,   "select * from division where dvtn_status='1' ");	
        while($divisionrow=mysqli_fetch_object($divisionres))
		{
        ?>
        <option value="<?php echo $divisionrow->dvtn_id; ?>"><?php echo $divisionrow->dvtn_title; ?></option>
        <?php } ?>
		</select>
    </td>
	</tr>
	<tr>
		<td class="label" width="160">Contact Person</td>
		<td>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody><tr>
			<td width="53">
			<select gtbfieldid="9" name="comp_cnt_prefix" id="comp_cnt_prefix" class="s_s a_f" style="width: 59px;" tabindex="2">
			<?php
            $arr=array("Mr.","Ms.","Mrs.","Dr.");
            foreach($arr as $val)
            {
            ?>
            <option value="<?php echo $val;?>" <?php if($val==user_info($uid,'name_prefix')) { ?> selected="selected" <?php } ?> ><?php echo $val;?> </option>
            <?php } ?>
			</select>
			</td>
			<td width="125"><div id="a32" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please Enter your <strong>First Name</strong> and <strong>Last Name</strong>.</div></div>
			<input gtbfieldid="10" maxlength="20" name="comp_cnt_fname" id="comp_cnt_fname" tabindex="3" class="a_f f_n_wid ml8"></td>
			<td width="125"><div id="a33" class="tbp tbm13" style="display:none"><div class="t1a" align="left">Please Enter your <strong>First Name</strong> and <strong>Last Name</strong>.</div></div>
			<input gtbfieldid="11" maxlength="20" size="11" name="comp_cnt_lname" id="comp_cnt_lname" tabindex="4" class="a_f f_n_wid ml8"></td>
		</tr>
		</tbody></table>
		</td>
	</tr>
	<tr>
		<td class="label" width="160">Address</td>
		<td><div id="a34" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please enter your full postal address. Include plot no./ building no./ street name, landmark etc.</div></div><input maxlength="190" name="comp_cnt_address" id="comp_cnt_address" class="a_f rf" tabindex="5" type="text"></td>
	</tr>
	<tr>
		<td class="label" width="160">&nbsp;</td>
		<td><div id="a35" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please enter your full postal address. Include plot no./ building no./ street name, landmark etc.</div></div><input maxlength="200" name="comp_cnt_address1" id="comp_cnt_address1" class="a_f rf" tabindex="6" type="text"></td>
	</tr>
	<tr>
		<td class="label" width="160"><span>*</span>&nbsp;Country</td>
		<td><input name="country_iso" value="" id="country_iso_add" type="Hidden">
        <input name="comp_cnt_country" value="" id="comp_cnt_country" type="hidden"><div id="a36" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please select your country.</div></div><input id="country_nm" name="country_nm" autocomplete="off" class="a_f rf" tabindex="7" maxlength="100" type="text"></td>
	</tr>
	<tr>
		<td class="label" width="160">&nbsp;Telephone</td>
		<td>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody><tr>
			<td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" id="phcntode1" name="phcntode1" class="ron c_c" tabindex="8">   </td>
			<td width="60"><div id="a37" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777</strong>.</div></div><input gtbfieldid="16" class="a_f ml8 a_c" maxlength="6" name="comp_cnt_phareacode" id="comp_cnt_phareacode" tabindex="9"></td>
			<td><div id="a38" class="tbp tbm12" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777</strong>.</div></div><input gtbfieldid="17" maxlength="35" name="comp_cnt_telephone" id="comp_cnt_telephone" class="a_f ml8 ph_n" tabindex="10" type="text"></td>
		</tr>
		</tbody></table>
		</td>
	</tr>
	<tr>
		<td class="label" width="160">Mobile/Cell Phone</td>
		<td>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody><tr>
			<td width="50"><input gtbfieldid="15" maxlength="6" id="phcntode2" name="phcntode2" readonly="readonly" class="ron c_c" tabindex="11"></td>
			<td><div id="a39" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your mobile / cell phone number here.<br><strong> Example: +91-9696969696</strong>.</div></div><input gtbfieldid="17" maxlength="40" name="comp_cnt_mobile" id="comp_cnt_mobile" class="a_f ml8 mo_n" tabindex="12" type="text"></td>
		</tr>
		</tbody></table>
		</td>
	</tr>
	<tr>
		<td class="label" width="160">Fax</td>
		<td>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody><tr>
			<td width="50"><input gtbfieldid="15" readonly="readonly" id="phcntode3" name="phcntode3" maxlength="6" class="ron c_c" tabindex="13"></td>
			<td width="60"><div id="a40" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your official fax (Facsimile) number here along with area code.<br><strong> Example:+91-120-3911010</strong>.</div></div><input gtbfieldid="16" class="a_f ml8 a_c" name="comp_cnt_faxareacode" id="comp_cnt_faxareacode" maxlength="6" tabindex="14"></td>
			<td><div id="a41" class="tbp tbm12" style="display:none"><div class="t1a" align="left">Please enter your official fax (Facsimile) number here along with area code.<br><strong> Example:+91-120-3911010</strong>. </div></div><input gtbfieldid="17" maxlength="35" name="comp_cnt_fax" id="comp_cnt_fax" class="a_f ml8 ph_n" tabindex="15" type="text"></td>
		</tr>
		</tbody></table>
		</td>
	</tr>
	<tr>
		<td class="label" width="160"> E-mail</td>
		<td><div id="a42" class="tbp tbm10" style="display:none"><div class="t1a" align="left">A
 valid email address is required in order to receive all important 
communication including enquiries. Your email address will also be your <b>login id</b>.</div></div><input name="comp_cnt_email" id="comp_cnt_email" class="a_f rf" maxlength="200" tabindex="16" type="text"></td>
	</tr>
	<tr>
		<td width="160">&nbsp;</td>
		<td align="left"><input name="comp_cnt_phcntode" id="comp_cnt_phcntode" value="" type="hidden">
        <input value="Save" name="save" class="saps" onclick="addContact();" id="button1" tabindex="17" type="button"title="بوب اب ">&nbsp; <span id="new_save" style="display: none;"><img src="images/loading.gif" alt="" border="0" width="16" height="11"></span></td>
	</tr>
	</tbody></table>
    </div>
    </form>

    </div>
    </div>
    <div class="clb">&nbsp;</div>
    </div>
    <!--add new contact:ends-->
    
    <div class="clb">&nbsp;</div>
<!--<a style="display: block;" href="javascript:shownewblk();" class="c am bnr fr" id="add_new"></a> -->  
</div>
    <div>&nbsp;</div>
	</div>
        <!--body part:ends-->

	</div><!--right navigation:start-->
		<div class="f2 leftnv">
                <!--<div style="position:relative" class="">
		<div class="nw6 fw">Profile Completed 12%<div class="pc"><img src="images/pm.png" alt="" border="0" width="12%" height="10"></div></div>
		<div style="display:none;" id="spi" class="spiral"></div>

                <div class="nw7 mt5"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td width="50%">Logo</td><td width="50%"><div class="nw9 c s2 f1 bnr">Incomplete</div></td></tr></tbody></table></div>
		<div class="nw7"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td width="50%">Contact</td><td width="50%"><div class="nw9 c s2 f1 bnr">Incomplete</div></td></tr></tbody></table></div>
		<div class="nw7"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td width="50%">Business Info.</td><td width="50%"><div class="nw9 c s3 f1 bnr"><a href="javascript:void(0);">Add Now</a></div></td></tr></tbody></table></div>
		<div class="nw7"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td width="50%">Fact Sheet</td><td width="50%"><div class="nw9 c s3 f1 bnr"><a href="javascript:void(0);">Add Now</a></div></td></tr></tbody></table></div>
                <div class="nw7"><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td width="50%">Registrations</td><td width="50%"><div class="nw9 c s3 f1 bnr"><a href="javascript:void(0);">Add Now</a></div></td></tr></tbody></table></div>
		
		
                </div>--> 
		
			<!--do you know widget:start--><div>&nbsp;</div> <div class="dyk1" id="do_you_know"> <div class="dyk2"><h2>    هل تعــلــم ؟ </h2></div> <div class="dyk3">  <strong><span></span></strong> <strong>  </strong> <strong><span> أنك تستطيع تحميل وعرض منتجاتك وخدماتك التجارية والإعلان عنها بكل المزايا</span></strong><div><a href="product-add.php">إعرض منتجات الآن</a></div> </div><div class="dyk4">&nbsp;</div></div> <!-- do you know widget:end -->
			
			<div class="c3">&nbsp;</div><div class="c3">&nbsp;</div></div><!--right navigation:ends-->
		<div class="c3">&nbsp;</div></div>
		
		<style>
#cview {
    background: #f5f5f5 !important;
    padding: 20px !important;
    border-radius: 8px !important;
    margin-bottom: 20px !important;
}
#cview .mp7, #cview .mp8 {
    background: transparent !important;
}
</style>
</div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>