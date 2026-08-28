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
    
    // تحديث جدول user
    mysqli_query($con, "UPDATE user SET 
        name_prefix = '" . mysqli_real_escape_string($con, $_POST['name_prefix'] ?? '') . "',
        fname = '" . mysqli_real_escape_string($con, $_POST['fname'] ?? '') . "',
        lname = '" . mysqli_real_escape_string($con, $_POST['lname'] ?? '') . "',
        mobile1 = '" . mysqli_real_escape_string($con, $_POST['mobile1'] ?? '') . "',
        image = '" . mysqli_real_escape_string($con, $_POST['profile_photo'] ?? '') . "'
        WHERE usr_id = $uid");
    
    // تحديث جدول business_profile
    $city_id = (int)($_POST['city'] ?? 0);
    $state_id = (int)($_POST['state'] ?? 0);
    
    $check = mysqli_query($con, "SELECT * FROM business_profile WHERE bnsprof_uid = $uid");
    if(mysqli_num_rows($check) > 0){
        mysqli_query($con, "UPDATE business_profile SET
            bnsprof_designation = '" . mysqli_real_escape_string($con, $_POST['userdesignation'] ?? '') . "',
            bnsprof_ceoprefix = '" . mysqli_real_escape_string($con, $_POST['ceo_prefix'] ?? '') . "',
            bnsprof_ceofname = '" . mysqli_real_escape_string($con, $_POST['ceo_fname'] ?? '') . "',
            bnsprof_ceolname = '" . mysqli_real_escape_string($con, $_POST['ceo_lname'] ?? '') . "',
            bnsprof_compname = '" . mysqli_real_escape_string($con, $_POST['company_name'] ?? '') . "',
            bnsprof_address1 = '" . mysqli_real_escape_string($con, $_POST['address'] ?? '') . "',
            bnsprof_address2 = '" . mysqli_real_escape_string($con, $_POST['address1'] ?? '') . "',
            bnsprof_city = $city_id,
            bnsprof_state = $state_id,
            bnsprof_zipcode = '" . mysqli_real_escape_string($con, $_POST['zipcode'] ?? '') . "',
            bnsprof_phcode1 = '" . mysqli_real_escape_string($con, $_POST['telephone_areacode1'] ?? '') . "',
            bnsprof_ph1 = '" . mysqli_real_escape_string($con, $_POST['telephone1'] ?? '') . "',
            bnsprof_phcode2 = '" . mysqli_real_escape_string($con, $_POST['telephone_areacode2'] ?? '') . "',
            bnsprof_ph2 = '" . mysqli_real_escape_string($con, $_POST['telephone2'] ?? '') . "',
            bnsprof_phcode3 = '" . mysqli_real_escape_string($con, $_POST['telephone_areacode3'] ?? '') . "',
            bnsprof_ph3 = '" . mysqli_real_escape_string($con, $_POST['telephone3'] ?? '') . "',
            bnsprof_phcode4 = '" . mysqli_real_escape_string($con, $_POST['telephone_areacode4'] ?? '') . "',
            bnsprof_ph4 = '" . mysqli_real_escape_string($con, $_POST['telephone4'] ?? '') . "',
            bnsprof_mobile2 = '" . mysqli_real_escape_string($con, $_POST['mobile2'] ?? '') . "',
            bnsprof_mobile3 = '" . mysqli_real_escape_string($con, $_POST['mobile3'] ?? '') . "',
            bnsprof_mobile4 = '" . mysqli_real_escape_string($con, $_POST['mobile4'] ?? '') . "',
            bnsprof_faxcode1 = '" . mysqli_real_escape_string($con, $_POST['fax_areacode1'] ?? '') . "',
            bnsprof_fax1 = '" . mysqli_real_escape_string($con, $_POST['fax1'] ?? '') . "',
            bnsprof_faxcode2 = '" . mysqli_real_escape_string($con, $_POST['fax_areacode2'] ?? '') . "',
            bnsprof_fax2 = '" . mysqli_real_escape_string($con, $_POST['fax2'] ?? '') . "',
            bnsprof_emailalt1 = '" . mysqli_real_escape_string($con, $_POST['email_alt1'] ?? '') . "',
            bnsprof_emailalt2 = '" . mysqli_real_escape_string($con, $_POST['email_alt2'] ?? '') . "',
            bnsprof_emailalt3 = '" . mysqli_real_escape_string($con, $_POST['email_alt3'] ?? '') . "',
            bnsprof_website_alt = '" . mysqli_real_escape_string($con, $_POST['website_alt'] ?? '') . "'
            WHERE bnsprof_uid = $uid");
    } else {
        mysqli_query($con, "INSERT INTO business_profile SET
            bnsprof_uid = $uid,
            bnsprof_designation = '" . mysqli_real_escape_string($con, $_POST['userdesignation'] ?? '') . "',
            bnsprof_ceoprefix = '" . mysqli_real_escape_string($con, $_POST['ceo_prefix'] ?? '') . "',
            bnsprof_ceofname = '" . mysqli_real_escape_string($con, $_POST['ceo_fname'] ?? '') . "',
            bnsprof_ceolname = '" . mysqli_real_escape_string($con, $_POST['ceo_lname'] ?? '') . "',
            bnsprof_compname = '" . mysqli_real_escape_string($con, $_POST['company_name'] ?? '') . "',
            bnsprof_address1 = '" . mysqli_real_escape_string($con, $_POST['address'] ?? '') . "',
            bnsprof_address2 = '" . mysqli_real_escape_string($con, $_POST['address1'] ?? '') . "',
            bnsprof_city = $city_id,
            bnsprof_state = $state_id,
            bnsprof_zipcode = '" . mysqli_real_escape_string($con, $_POST['zipcode'] ?? '') . "',
            bnsprof_phcode1 = '" . mysqli_real_escape_string($con, $_POST['telephone_areacode1'] ?? '') . "',
            bnsprof_ph1 = '" . mysqli_real_escape_string($con, $_POST['telephone1'] ?? '') . "',
            bnsprof_phcode2 = '" . mysqli_real_escape_string($con, $_POST['telephone_areacode2'] ?? '') . "',
            bnsprof_ph2 = '" . mysqli_real_escape_string($con, $_POST['telephone2'] ?? '') . "',
            bnsprof_phcode3 = '" . mysqli_real_escape_string($con, $_POST['telephone_areacode3'] ?? '') . "',
            bnsprof_ph3 = '" . mysqli_real_escape_string($con, $_POST['telephone3'] ?? '') . "',
            bnsprof_phcode4 = '" . mysqli_real_escape_string($con, $_POST['telephone_areacode4'] ?? '') . "',
            bnsprof_ph4 = '" . mysqli_real_escape_string($con, $_POST['telephone4'] ?? '') . "',
            bnsprof_mobile2 = '" . mysqli_real_escape_string($con, $_POST['mobile2'] ?? '') . "',
            bnsprof_mobile3 = '" . mysqli_real_escape_string($con, $_POST['mobile3'] ?? '') . "',
            bnsprof_mobile4 = '" . mysqli_real_escape_string($con, $_POST['mobile4'] ?? '') . "',
            bnsprof_faxcode1 = '" . mysqli_real_escape_string($con, $_POST['fax_areacode1'] ?? '') . "',
            bnsprof_fax1 = '" . mysqli_real_escape_string($con, $_POST['fax1'] ?? '') . "',
            bnsprof_faxcode2 = '" . mysqli_real_escape_string($con, $_POST['fax_areacode2'] ?? '') . "',
            bnsprof_fax2 = '" . mysqli_real_escape_string($con, $_POST['fax2'] ?? '') . "',
            bnsprof_emailalt1 = '" . mysqli_real_escape_string($con, $_POST['email_alt1'] ?? '') . "',
            bnsprof_emailalt2 = '" . mysqli_real_escape_string($con, $_POST['email_alt2'] ?? '') . "',
            bnsprof_emailalt3 = '" . mysqli_real_escape_string($con, $_POST['email_alt3'] ?? '') . "',
            bnsprof_website_alt = '" . mysqli_real_escape_string($con, $_POST['website_alt'] ?? '') . "',
            bnsprof_creation_date = NOW()");
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

    <div class="mp10 shedfrm"  style="display:  <?php if($msg!=''){?>block<?php }else{?>none<?php }?>;"  id="eform">
    <form name="frmHeadOffice" action="" method="post" style="margin:0; padding:0;" onSubmit="return updatecontactdet();" enctype="multipart/form-data">
<div>
    <?php //if(isset($msg) && $msg!=""){ ?>
	<div style="text-align:left;width:389px;padding:1% 1% 1% 5%;display:block;margin-left:87px;" class="" id="updatemessage"><?php echo $msg; ?></div>
    <?php //} ?>
<table align="left" border="0" cellpadding="4" cellspacing="0" width="490">
<tbody><tr>
<td class="label" width="160"title="Contact Person"><span>*</span>&nbsp;مسئول الاتصال المعين من الشركة</td>
<td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="53">         
        <select class="s_s a_f" gtbfieldid="9" name="name_prefix" id="name_prefix" style="width: 59px;" tabindex="1">
        <?php
        $arr=array("Mr.","Ms.","Mrs.","Dr.");
		foreach($arr as $val)
		{
		?>
        <option value="<?php echo $val;?>" <?php if($val==user_info($uid,'name_prefix')) { ?> selected="selected" <?php } ?> ><?php echo $val;?> </option>
        <?php } ?>
        </select>
         </td>
         
        <td width="125"><div id="a1" class="tbp tbm8" style="display: none;"><div class="t1a" align="left">Please Enter your <strong>First Name</strong></div></div><input gtbfieldid="10" maxlength="20" size="10" value="<?php echo user_info($uid,'fname');?>" name="fname" id="fname" tabindex="2" onfocus="javascript:ntt('q1','a1');" onblur="nhid('a1');f_name();" class="a_f f_n_wid ml8"></td>
        
        <td width="125"><div id="a2" class="tbp tbm9" style="display:none"><div class="t1a" align="left">Please Enter your <strong>Last Name</strong></div></div><input gtbfieldid="11" maxlength="20" size="11" value="<?php echo user_info($uid,'lname');?>" name="lname" id="lname" tabindex="3" onfocus="javascript:ntt('q2','a2');" onblur="nhid('a2');l_name();" class="a_f f_n_wid ml8"></td>
        </tr>
        </tbody></table>
	<span id="e1" class="em mt5" style="display:none"></span>
    <span id="e1a" class="em" style="display:none"></span>    
    </td>
    </tr>
    
<tr>
<td class="label" width="160"title="Designation / Job Title">المسمى الوظيفى </td>
<td><div id="a3" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please Enter your <strong>Job Title</strong></div></div>

<!--<input value="<?php //echo user_designation_name(user_info($uid,'bnsprof_designation')); ?>" name="designation" id="designation" maxlength="30" autocomplete="off" aria-haspopup="true" aria-autocomplete="list" role="textbox" placeholder="Select Designation" tabindex="4" onfocus="javascript:ntt('q3','a3');" onblur="nhid('a3');desig();" class="a_f rf" type="text" onClick="blankdesignation()" style="">-->

<input type="text" name="userdesignation" id="userdesignation" value="<?php echo user_info($uid,'bnsprof_designation');?>">
<span id="d1" class="em mt5" style="display:none"></span>
<span id="d1a" class="em mt5" style="display:none"></span>
</td>
</tr>

        <tr>
        <td class="label" width="160"title="CEO Name" > إسم  رئيس الشركة </td>
        <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="53">        
        <select class="s_s a_f" gtbfieldid="9" name="ceo_prefix" id="ceo_prefix" style="width: 59px;" tabindex="1">
        <?php
        $arr=array("Mr.","Ms.","Mrs.","Dr.");
		foreach($arr as $val)
		{
		?>
        <option value="<?php echo $val;?>" <?php if($val==user_info($uid,'bnsprof_ceoprefix')) { ?> selected="selected" <?php } ?> ><?php echo $val;?> </option>
        <?php } ?>
        </select>
        </td>
        <td width="125"><div id="a31" class="tbp tbm8" style="display:none"><div class="t1a" align="left">Please Enter your <strong>First Name</strong></div></div><input gtbfieldid="31" maxlength="20" size="10" name="ceo_fname" id="ceo_fname" tabindex="6" onfocus="javascript:ntt('q31','a31');" onblur="nhid('a1');c_fname();" class="a_f f_n_wid ml8" value="<?php echo user_info($uid,'bnsprof_ceofname');?>"></td>
        <td width="125"><div id="a32" class="tbp tbm9" style="display:none"><div class="t1a" align="left">Please Enter your <strong>Last Name</strong></div></div><input gtbfieldid="32" maxlength="20" size="11" name="ceo_lname" id="ceo_lname" tabindex="7" onfocus="javascript:ntt('q32','a32');" onblur="nhid('a32');c_lname();" class="a_f f_n_wid ml8" value="<?php echo user_info($uid,'bnsprof_ceolname');?>"></td>
        </tr>
        </tbody></table>
	    <span id="ceo" class="em mt5" style="display:none"></span><span id="ceo1" class="em" style="display:none"></span>
        </td>
        </tr>

    <tr>
    <td class="label" width="160"title="Company Name">إسم الشركة </td>
    <td><div id="a4" class="tbp tbm10" style="display: none;"><div class="t1a" align="left">We suggest that you use a genuine and complete company name such as <b>'Perfect Systems Ltd.'</b> Buyers and Suppliers prefer to interact with companies with complete and accurate company name.</div></div>
    <input name="company_name" id="company_name" maxlength="100" onfocus="javascript:ntt('q4','a4');" onblur="nhid('a4');company();" tabindex="8" class="a_f rf" type="text" value="<?php echo user_info($uid,'bnsprof_compname');?>">
<span id="co1" class="em mt5" style="display:none"></span><span id="co1a" class="em" style="display:none"></span>
</td>
    </tr>
    
    <tr>
    <td class="label" width="160"title="Address">عنوان الشركة</td>
    <td><div id="a5" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please enter your full postal address. Include <b>plot no./ building no./ street name, landmark etc</b>.</div></div>
<input name="address" id="address" onfocus="javascript:ntt('q5','a5');" onblur="nhid('a5');address();" tabindex="9" maxlength="100" class="a_f rf" type="text" value="<?php echo user_info($uid,'bnsprof_address1');?>">
	</td>
    </tr>
    
    <tr>
    <td class="label" width="160">&nbsp;</td>
    <td><div id="a6" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please enter your full postal address. Include <b>plot no./ building no./ street name, landmark etc.</b></div></div><input name="address1" id="address1" tabindex="10" maxlength="100" onfocus="javascript:ntt('q6','a6');" onblur="nhid('a6');address2();" class="a_f rf" type="text" value="<?php echo user_info($uid,'bnsprof_address2');?>">
<span id="ad" class="em mt5" style="display:none"></span><span id="ad1a" class="em" style="display:none"></span>
</td>
    </tr>
     
    <tr>
    <td class="label" width="230">&nbsp;</td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr class="csp">
        
        
        <td width="300" title="المدينة - المحافظة - الكود البريدى"><span>*</span> ( المحافظة >> المدينة )</td>
<td style="width:103px;text-align:left">Postal / ZIP Code</td>
</tr>
<tr>
<td colspan="4">
    <input autocomplete="off" id="city_others" name="city_others" placeholder="Search city..." class="a_f rf" maxlength="50" style="width:220px; margin-bottom:5px;" type="text" value="<?php echo htmlspecialchars(user_info($uid,'bnsprof_city')); ?>">
    <input name="city" id="city" value="<?php echo htmlspecialchars(user_info($uid,'bnsprof_city')); ?>" type="hidden">
    <input name="stateid" id="stateid" maxlength="30" style="width:80px; margin-bottom:5px;" type="text" value="<?php echo htmlspecialchars(user_info($uid,'bnsprof_state')); ?>">
    <input name="state" id="state" value="<?php echo htmlspecialchars(user_info($uid,'bnsprof_state')); ?>" type="hidden">
    <input name="zipcode" id="zipcode" maxlength="10" style="width:100px;" class="a_f ci" type="text" value="<?php echo htmlspecialchars(user_info($uid,'bnsprof_zipcode')); ?>">
</td>


        </tr>
        </tbody></table>
	<span id="c1a" class="em" style="display:none"></span>
    <span id="z1" class="em" style="display:none"></span> 
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title="Country "><span>*</span>&nbsp;البــلد </td>
    <td><input name="country_iso" value="IN" id="country_iso" type="hidden">
       <input name="country" value="IN" id="country" type="hidden">
<input name="country_name" value="<?php echo get_country_name(user_info($uid,'country'));?>" id="country_name1" style="" tabindex="14" class="a_f rf" readonly="readonly" type="text">
</td>
    </tr>
    <tr>
    <td class="label" width="160"title="Telephone 1 "><span>*</span>&nbsp;  1 تليفـون أرضى </td>
    <td><input name="im_gsm" id="im_gsm" value="" type="hidden">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="ph_country" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="ph_country" style="background-color:#eaeaea;" tabindex="15" class="ron c_c"></td>
        <td width="60"><div id="a10" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777.</strong></div></div>
        <input gtbfieldid="16" name="telephone_areacode1" id="telephone_areacode1" size="5" maxlength="6" onfocus="javascript:ntt('q10','a10');" onblur="nhid('a10');a_code('ph_area','pa','pno');" tabindex="16" class="a_f a_c ml8" value="<?php echo user_info($uid,'bnsprof_phcode1');?>"  >
        </td>
        <td><div id="a11" class="tbp tbm12" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777.</strong></div></div><input gtbfieldid="17" size="15" name="telephone1" id="telephone1" maxlength="35" onfocus="javascript:ntt('q11','a11');" onblur="nhid('a11');ph_nu('ph_area','ph_no','pa','pno','im_gsm');" tabindex="17" class="a_f ml8 ph_n" style="width:180px" type="text" value="<?php echo user_info($uid,'bnsprof_ph1');?>"></td>
        </tr>
        </tbody></table><span id="pa" class="em" style="display:none"></span><span id="pno" class="em" style="display:none"></span> 
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title=" Telephone 2 ">&nbsp; 2 تليفــون أرضى </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="ph_country2" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="ph_country2" style="background-color:#eaeaea;" tabindex="18" class="ron c_c"></td>
        <td width="60"><div id="a12" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777.</strong></div></div><input gtbfieldid="16" name="telephone_areacode2" id="telephone_areacode2" size="5" maxlength="6" onfocus="javascript:ntt('q12','a12');" onblur="nhid('a12');a_code('ph_area2','pa2','pno2');" tabindex="19" class="a_f a_c ml8" value="<?php echo user_info($uid,'bnsprof_phcode2');?>"></td>
        <td><div id="a13" class="tbp tbm12" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777.</strong></div></div><input gtbfieldid="17" size="15" name="telephone2" id="telephone2" maxlength="35" onfocus="javascript:ntt('q13','a13');" class="a_f ml8 ph_n" onblur="nhid('a13');ph_nu('ph_area2','ph_no2','pa2','pno2','im_gsm');" tabindex="20" style="width:180px" type="text" value="<?php echo user_info($uid,'bnsprof_ph2');?>"></td>
        </tr>
        </tbody></table><span id="pa2" class="em" style="display:none"></span><span id="pno2" class="em" style="display:none"></span>
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title="Telephone 3 ">&nbsp; 3 تليفـون أرضى </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="ph_country3" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="ph_country3" style="background-color:#eaeaea;" tabindex="21" class="ron c_c"></td>
        <td width="60"><div id="a14" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777.</strong></div></div><input gtbfieldid="16" name="telephone_areacode3" id="telephone_areacode3" size="5" maxlength="6" onfocus="javascript:ntt('q14','a14');" onblur="nhid('a14');a_code('ph_area3','pa3','pno3');" tabindex="22" class="a_f a_c ml8" value="<?php echo user_info($uid,'bnsprof_phcode3');?>"></td>
        <td><div id="a15" class="tbp tbm12" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777.</strong></div></div><input gtbfieldid="17" size="15" name="telephone3" id="telephone3" maxlength="35" onfocus="javascript:ntt('q15','a15');" class="a_f ml8 ph_n" onblur="nhid('a15');ph_nu('ph_area3','ph_no3','pa3','pno3','im_gsm');" tabindex="23" type="text" value="<?php echo user_info($uid,'bnsprof_ph3');?>"></td>
        </tr>
        </tbody></table><span id="pa3" class="em" style="display:none"></span><span id="pno3" class="em" style="display:none"></span>
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title=" Telephone 4 ">&nbsp;4 تليفــون أرضى </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="ph_country4" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="ph_country4" style="background-color:#eaeaea;" tabindex="24" class="ron c_c"></td>
        <td width="60"><div id="a16" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777.</strong></div></div><input gtbfieldid="16" name="telephone_areacode4" id="telephone_areacode4" size="5" maxlength="6" onfocus="javascript:ntt('q16','a16');" onblur="nhid('a16');a_code('ph_area4','pa4','pno4');" tabindex="25" class="a_f a_c ml8" value="<?php echo user_info($uid,'bnsprof_phcode4');?>"></td>
        <td><div id="a17" class="tbp tbm12" style="display:none"><div class="t1a" align="left">Please enter your office phone (land line or fixed phone) number here along with area code.<br><strong>Example: +91-120-6777777.</strong></div></div><input gtbfieldid="17" size="15" name="telephone4" id="telephone4" maxlength="35" onfocus="javascript:ntt('q17','a17');" class="a_f ml8 ph_n" tabindex="26" onblur="nhid('a17');ph_nu('ph_area4','ph_no4','pa4','pno4','im_gsm');" type="text" value="<?php echo user_info($uid,'bnsprof_ph4');?>"></td>
        </tr>
        </tbody></table><span id="pa4" class="em" style="display:none"></span><span id="pno4" class="em" style="display:none"></span>
     <span id="pm" class="em" style="display:none"></span>
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title="Mobile/Cell Phone 1"><span>*</span>&nbsp;التليفون المحمول الأساسى </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="mob_country" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="mob_country" style="background-color:#eaeaea;" tabindex="27" class="ron c_c"></td>
        <td><div id="a18" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your mobile / cell phone number here. <br><strong>Example: +91-9696969696</strong>.</div></div><input gtbfieldid="17" size="15" name="mobile1" id="mobile1" value="<?php echo user_info($uid,'mobile1');?>" maxlength="40" onfocus="javascript:ntt('q18','a18');" onblur="nhid('a18');mobi('mobile','mo','mob','im_gsm');" tabindex="28" class="a_f ml8 mo_n" style="width:250px" type="text"></td>
        </tr>
        </tbody></table><span id="mo" class="em" style="display:none"></span><span id="mob" class="em" style="display:none"></span><span id="mob_exist" class="em" style="display:none"></span><input id="mob_flag" name="mob_flag" value="0" type="hidden">
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title=" Mobile/Cell Phone 2< ">&nbsp;  2 تليفـون محمول </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="mob_country2" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="mob_country2" style="background-color:#eaeaea;" tabindex="29" class="ron c_c"></td>
        <td><div id="a19" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your mobile / cell phone number here. <br><strong>Example: +91-9696969696</strong>.</div></div><input gtbfieldid="17" size="15" name="mobile2" id="mobile2" value="<?php echo user_info($uid,'bnsprof_mobile2');?>" maxlength="40" onfocus="javascript:ntt('q19','a19');" onblur="nhid('a19');mobi('mobile2','mo2','mob2','im_gsm');" tabindex="30" class="a_f ml8 mo_n" style="width:250px" type="text"></td>
        </tr>
        </tbody></table><span id="mo2" class="em" style="display:none"></span><span id="mob2" class="em" style="display:none"></span>
    </td>
    </tr>
   <tr>
    <td class="label" width="160"title=" Mobile/Cell Phone 3 ">&nbsp;3 تليفــون محمول </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="mob_country3" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="mob_country3" style="background-color:#eaeaea;" tabindex="31" class="ron c_c"></td>
        <td><div id="a20" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your mobile / cell phone number here. <br><strong>Example: +91-9696969696</strong>.</div></div><input gtbfieldid="17" size="15" name="mobile3" id="mobile3" value="<?php echo user_info($uid,'bnsprof_mobile3');?>" maxlength="40" onfocus="javascript:ntt('q20','a20');" onblur="nhid('a20');mobi('mobile3','mo3','mob3','im_gsm');" tabindex="32" class="a_f ml8 mo_n" type="text"></td>
        </tr>
        </tbody></table><span id="mo3" class="em" style="display:none"></span><span id="mob3" class="em" style="display:none"></span>
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title=" Mobile/Cell Phone 4 ">&nbsp;4 تليفــون محمول </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="mob_country4" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="mob_country4" style="background-color:#eaeaea;" tabindex="33" class="ron c_c"></td>
        <td><div id="a21" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your mobile / cell phone number here.<br><strong>Example: +91-9696969696</strong>.</div></div><input gtbfieldid="17" size="15" name="mobile4" id="mobile4" value="<?php echo user_info($uid,'bnsprof_mobile4');?>" maxlength="40" onfocus="javascript:ntt('q21','a21');" onblur="nhid('a21');mobi('mobile4','mo4','mob4','im_gsm');mend_ph();" tabindex="34" class="a_f ml8 mo_n" type="text"></td>
        </tr>
        </tbody></table><span id="mo4" class="em" style="display:none"></span><span id="mob4" class="em" style="display:none"></span>
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title=" Fax 1"> 1 فاكس  </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="fax_country" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="fax_country" style="background-color:#eaeaea;" tabindex="35" class="ron c_c"></td>
        <td width="60"><div id="a22" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your official fax (Facsimile) number here along with area code.<br><strong>Example:+91-120-3911010</strong>.
</div></div><input gtbfieldid="16" name="fax_areacode1" id="fax_areacode1" size="5" maxlength="6" onfocus="javascript:ntt('q22','a22');" onblur="nhid('a22');faxarea_valid('fax_area','fa','fax');" tabindex="36" class="a_f ml8 a_c" value="<?php echo user_info($uid,'bnsprof_faxcode1');?>">
</td>
        <td><div id="a23" class="tbp tbm12" style="display:none"><div class="t1a" align="left">Please enter your official fax (Facsimile) number here along with area code.<br><strong>Example:+91-120-3911010</strong>.</div></div><input gtbfieldid="17" size="15" name="fax1" id="fax1" maxlength="35" onfocus="javascript:ntt('q23','a23');" onblur="nhid('a23');faxno_valid('fax_area','fax_no','fa','fax','im_gsm');" tabindex="37" class="a_f ml8 ph_n" type="text" value="<?php echo user_info($uid,'bnsprof_fax1');?>"></td>
        </tr>
        </tbody></table><span id="fa" class="em" style="display:none"></span><span id="fax" class="em" style="display:none"></span>
    </td>
    </tr>
    <tr>
    <td class="label" width="160"title=" Fax 2 "> 2 فاكس </td>
    <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody><tr>
        <td width="50"><input gtbfieldid="15" maxlength="6" readonly="readonly" name="fax_country2" size="3" value="<?php echo user_info($uid,'country_ph_code');?>" id="fax_country2" style="background-color:#eaeaea;" tabindex="38" class="ron c_c"></td>
        <td width="60"><div id="a24" class="tbp tbm11" style="display:none"><div class="t1a" align="left">Please enter your official fax (Facsimile) number here along with area code.<br><strong>Example:+91-120-3911010</strong>.</div></div>
   <input gtbfieldid="16" name="fax_areacode2" id="fax_areacode2" size="5" maxlength="6" onfocus="javascript:ntt('q24','a24');" onblur="nhid('a24');faxarea_valid('fax_area2','fa2','fax2');" tabindex="39" class="a_f ml8 a_c" value="<?php echo user_info($uid,'bnsprof_faxcode2');?>"></td>
        <td><div id="a25" class="tbp tbm12" style="display:none"><div class="t1a" align="left">Please enter your official fax (Facsimile) number here along with area code.<br><strong>Example:+91-120-3911010</strong>.</div></div><input gtbfieldid="17" size="15" name="fax2" id="fax2" maxlength="35" onfocus="javascript:ntt('q25','a25');" onblur="nhid('a25');faxno_valid('fax_area2','fax_no2','fa2','fax2','im_gsm');" tabindex="40" class="a_f ml8 ph_n" type="text" value="<?php echo user_info($uid,'bnsprof_fax2');?>"></td>
        </tr>
        </tbody></table>
        <span id="fa2" class="em" style="display:none"></span>
        <span id="fax2" class="em" style="display:none"></span>
    </td>
    </tr>
    
    <input type="hidden" id='usr_oauth_reg' name="usr_oauth_reg" value="<?php echo user_info($uid,'usr_oauth_reg');?>"/>
    <?php if(user_info($uid,'usr_oauth_reg') == '0'){?>
    <tr>
    <td class="label" width="160"title="Primary E-mail"><span>*</span>&nbsp;البريد الألكترونى الأساسى</td>
    <td><div id="a26" class="tbp tbm10" style="display:none"><div class="t1a" align="left">A
 valid email address is required in order to receive all important 
communication including enquiries. Your email address will also be your <strong>login id</strong>.</div></div><input readonly="readonly" style="background-color:#eaeaea;" tabindex="41" value="<?php echo user_info($uid,'email');?>" name="email" id="email" maxlength="100" onfocus="javascript:ntt('q26','a26');" onblur="nhid('a26');email_valid('email','eml')" class="a_f rf" type="text"><span id="eml" class="em" style="display:none"></span></td>
    </tr>
    
     <?php }elseif(user_info($uid,'usr_oauth_reg') == '1'){?>
     <tr>
    <td class="label" width="160"><span>*</span>&nbsp;Logged In With</td>
    <td><img src="social_media_images/facebook_logo.jpg" /></td>
    </tr> 
        
     <?php }elseif(user_info($uid,'usr_oauth_reg') == '2'){?>
     <tr>
    <td class="label" width="160"><span>*</span>&nbsp;Logged In With</td>
    <td><img src="social_media_images/gmail_.png" /></td>
    </tr> 
        
     <?php }elseif(user_info($uid,'usr_oauth_reg') == '3'){?>
     <tr>
    <td class="label" width="160"><span>*</span>&nbsp;Logged In With</td>
    <td><img src="social_media_images/twtBrd.jpg" /></td>
    </tr> 
        
     <?php }elseif(user_info($uid,'usr_oauth_reg') == '4'){?>
     <tr>
    <td class="label" width="160"><span>*</span>&nbsp;Logged In With</td>
    <td><img src="social_media_images/linkedinLog.png" /></td>
    </tr>     
     <?php }?>
    
    
    <?php if(user_info($uid,'usr_oauth_reg') == '0'){?>
    <tr>
    <td width="160">&nbsp;</td>
    <td><a style="" class="ajax" href="change-email.php" tiptitle="Change your primary Email" id="changepe1"title="Change Login E-Mail id">غـير إيميل الدخول الرئيسى</a></td>
    </tr>
    <?php }?>
    <tr>
    <td class="label" width="160"title=" Alternate E-mail 1 ">1 بريد الكترونى بديل </td>
    <td><div id="a27" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please provide your alternate email ID so that you can be contacted in case your Primary e-mail is not working.</div></div><input tabindex="42" name="email_alt1" id="email_alt1" maxlength="100" onfocus="javascript:ntt('q27','a27');" onblur="nhid('a27');email_valid('email2','eml2');" class="a_f rf" type="text" value="<?php echo user_info($uid,'bnsprof_emailalt1');?>"><span id="eml2" class="em" style="display:none"></span></td>
    </tr>
    <tr>
    <td class="label" width="160"title="  Alternate E-mail 2 "> بريد الكترونى بديل</td>
    <td><div id="a28" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please provide your alternate email ID so that you can be contacted in case your Primary e-mail is not working.</div></div><input tabindex="43" name="email_alt2" id="email_alt2" maxlength="100" onfocus="javascript:ntt('q28','a28');" onblur="nhid('a28');email_valid('email3','eml3');" class="a_f rf" type="text" value="<?php echo user_info($uid,'bnsprof_emailalt2');?>"><span id="eml3" class="em" style="display:none"></span></td>
    </tr>
    <tr>
    <td class="label" width="160"title="Alternate E-mail 3 ">3 بريد الكترونى بديل  </td>
    <td><div id="a29" class="tbp tbm10" style="display:none"><div class="t1a" align="left">Please provide your alternate email ID so that you can be contacted in case your Primary e-mail is not working.</div></div><input tabindex="44" name="email_alt3" id="email_alt3" maxlength="100" onfocus="javascript:ntt('q29','a29');" onblur="nhid('a29');email_valid('email4','eml4');" class="a_f rf" type="text" value="<?php echo user_info($uid,'bnsprof_emailalt3');?>"><span id="eml4" class="em" style="display:none"></span></td>
    </tr> 

	
    <tr>
    <td class="label" width="160"title="Alternate Website">الموقع الألكترونى الأصلى  </td>
    <td><div id="a31" class="tbp tbm10" style="display:none"><div class="t1a" align="left">If you have a website other than <?php echo getWebSiteName(); ?> catalog, please enter its URL here. <br><strong>Example: www.yourwebsite.com</strong>.</div></div>
    <input value="<?php echo user_info($uid,'bnsprof_website_alt');?>" onkeypress="http_val(this.value);" onkeyup="http_val(this.value);" name="website_alt" id="website_alt" onfocus="javascript:ntt('q31','a31');" onblur="nhid('a31');webs();" tabindex="47" maxlength="100" class="a_f rf" type="text"><span id="wb" class="em" style="display:none"></span><span id="wb1" class="em" style="display:none"></span></td>
    </tr>
	
    <tr class="hide">
		<td>
			Business Documents
		</td>
		<td>
			 <div class="upload_div">
							<input type="hidden" name="business_documents" id="business_documents" value="<?php echo user_info($uid,'attachment');?>">
							<img src="<?php echo BASE_URL ?>/server/php/files/thumbnail/<?php echo user_info($uid,'attachment');?>" width="110" id="business_doc" height="130">
							 <input id="fileupload" type="file" name="files" >
							 <span class="file_input">Add image</span>
			</div></td></tr><tr><td>صورة مسئول الإنصال</td>
		<td>
			 <div class="upload_div">
							<input type="hidden" name="profile_photo" id="profile_photo" value="<?php echo user_info($uid,'image');?>">
							<?php if(user_info($uid,'image')!=""){ ?>
							<img src="<?php echo 'data:image/jpg;base64,'.base64_encode( getUserInfo($uid,'profileImage'));?>" width="110" id="profilephoto1" height="130">
							<?php } else{ ?>
							<img src="http://egyptmart.shop/images/uploadd.png" width="110" id="profilephoto1" height="100">	
							<?php } ?>
							 <input id="profileupload" type="file" name="files" >
							 <span class="file_input"title="Add image"> أضف صورة </span>
			</div>
					
                                            
			   
		</td>
	</tr>
	
    <tr>
    <td width="160">&nbsp;</td>
    <td align="left">
    <input name="userd" id="userd" value="<?php echo $uid;?>" type="hidden">
    <input id="btnUpdate" class="saps" value=" إحفـظ التغييـرات " name="btnUpdate" tabindex="48" type="submit">&nbsp;
    <span id="save" style="display:none;">
    <img src="images/loading.gif" alt="" border="0" width="16" height="11">
       </span></td>
    </tr>                                                                
    </tbody></table>
    </div>
    </form>
    </div>
<div class="mp10" id="cview">
    <!-- مسئول الاتصالات -->
    <div class="mp8"><strong>مسئول الاتصالات:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'name_prefix') . ' ' . getUserInfo($uid, 'fname') . ' ' . getUserInfo($uid, 'lname')); ?></div>

    <!-- المسمى الوظيفى -->
    <div class="mp8"><strong>المسمى الوظيفى:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'bnsprof_designation')); ?></div>

    <!-- إسم رئيس مجلس الادارة -->
    <div class="mp8"><strong>إسم رئيس مجلس الادارة:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'bnsprof_ceoprefix') . ' ' . getUserInfo($uid, 'bnsprof_ceofname') . ' ' . getUserInfo($uid, 'bnsprof_ceolname')); ?></div>

    <!-- إسم الشركة (مضاف) -->
    <div class="mp8"><strong>إسم الشركة:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'bnsprof_compname')); ?></div>

    <!-- العنوان -->
    <div class="mp8"><strong>العنوان:</strong></div>
    <div class="mp7"><?php echo nl2br(htmlspecialchars(getUserInfo($uid, 'bnsprof_address1') . "\n" . getUserInfo($uid, 'bnsprof_address2'))); ?></div>

    <!-- المدينة، المحافظة، الرمز البريدى -->
    <div class="mp8"><strong>المدينة، المحافظة، الرمز البريدى:</strong></div>
    <div class="mp7">
        <?php 
        $city_id = (int)getUserInfo($uid, 'bnsprof_city');
        $state_id = (int)getUserInfo($uid, 'bnsprof_state');
        $city_name = ($city_id > 0) ? get_city_name($city_id) : '';
        $state_name = ($state_id > 0) ? get_state_name($state_id) : '';
        echo htmlspecialchars($city_name . '، ' . $state_name . '، ' . getUserInfo($uid, 'bnsprof_zipcode'));
        ?>
    </div>

    <!-- البلد -->
    <div class="mp8"><strong>البــلد:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(get_country_name(getUserInfo($uid, 'country'))); ?></div>

    <!-- تليفون أرضى 1 -->
    <div class="mp8"><strong>تليفــون أرضى 1:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'country_ph_code') . '-' . getUserInfo($uid, 'bnsprof_phcode1') . '-' . getUserInfo($uid, 'bnsprof_ph1')); ?></div>

    <!-- تليفون أرضى 2 -->
    <div class="mp8"><strong>تليفــون أرضى 2:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'country_ph_code') . '-' . getUserInfo($uid, 'bnsprof_phcode2') . '-' . getUserInfo($uid, 'bnsprof_ph2')); ?></div>

    <!-- تليفون محمول أساسى -->
    <div class="mp8"><strong>تليفــون محمول أساسى:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'country_ph_code') . '-' . getUserInfo($uid, 'mobile1')); ?></div>

    <!-- تليفون محمول 2 -->
    <div class="mp8"><strong>تليفــون محمول 2:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'country_ph_code') . '-' . getUserInfo($uid, 'bnsprof_mobile2')); ?></div>

    <!-- فاكس -->
    <div class="mp8"><strong>فاكس:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'country_ph_code') . '-' . getUserInfo($uid, 'bnsprof_faxcode1') . '-' . getUserInfo($uid, 'bnsprof_fax1')); ?></div>

    <!-- البريد الألكترونى الأساسى -->
    <div class="mp8"><strong>البريد الألكترونى الأساسى:</strong></div>
    <div class="mp7"><?php echo htmlspecialchars(getUserInfo($uid, 'email')); ?></div>

    <!-- صورة مسئول الاتصال -->
    <div class="mp8"><strong>صورة مسئول الاتصال:</strong></div>
    <div class="mp7">
        <?php 
        $profile_image = getUserInfo($uid, 'image');
        if ($profile_image) {
            echo '<img src="data:image/jpg;base64,' . base64_encode(getUserInfo($uid, 'profileImage')) . '" width="110" height="130" id="profilephoto1">';
        } else {
            echo '<img src="http://egyptmart.shop/images/uploadd.png" width="110" height="130" id="profilephoto1">';
        }
        ?>
    </div>
</div>
	
	
	
	
	
	
	
	
	
	

	
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
</div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>