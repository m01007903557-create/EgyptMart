<?php
@ob_start();
include 'common.php';
require_once __DIR__ . '/whats360-config.php';

// منع أخطاء المتغيرات غير المعرفة في PHP 8.3
$msg = $msg ?? '';
$name_prefix = $name_prefix ?? '';
$fname = $fname ?? '';
$lname = $lname ?? '';
$email = $email ?? '';
$country = $country ?? '';
$ph_country = $ph_country ?? '';
$mobile1 = $mobile1 ?? '';
$website = $website ?? '';
$pass = $pass ?? '';
$accept = $accept ?? '';

if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '')
{
	header("location:index.php");
	exit;
}

if (!function_exists('egyptmart_account_otp_verified')) {
    function egyptmart_account_otp_verified(mysqli $con, string $mobile, string $code = ''): bool {
        $mobile = egyptmart_normalize_mobile($mobile);
        $code = preg_replace('/[^0-9]/', '', $code);
        $sessionMobile = egyptmart_normalize_mobile((string)($_SESSION['otp_mobile'] ?? ''));
        if (!empty($_SESSION['otp_verified']) && $mobile !== '' && $sessionMobile !== '' && $mobile === $sessionMobile) {
            return true;
        }
        if ($mobile === '') {
            return false;
        }
        egyptmart_otp_table($con);
        if ($code !== '') {
            $stmt = mysqli_prepare($con, "SELECT id FROM egyptmart_otp_requests WHERE mobile = ? AND otp_code = ? AND status = 'verified' AND verified_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY id DESC LIMIT 1");
            mysqli_stmt_bind_param($stmt, 'ss', $mobile, $code);
        } else {
            $stmt = mysqli_prepare($con, "SELECT id FROM egyptmart_otp_requests WHERE mobile = ? AND status = 'verified' AND verified_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY id DESC LIMIT 1");
            mysqli_stmt_bind_param($stmt, 's', $mobile);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $verifiedId);
        $verified = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($verified) {
            $_SESSION['otp_verified'] = true;
            $_SESSION['otp_mobile'] = $mobile;
        }
        return (bool)$verified;
    }
}

if (!empty($_GET) && (isset($_GET['mobile']) || isset($_GET['otp_code']) || isset($_GET['notify_optin']))) {
    $directMobile = egyptmart_normalize_mobile((string)($_GET['mobile'] ?? ''));
    $directCode = (string)($_GET['otp_code'] ?? '');
    if (!egyptmart_account_otp_verified($con, $directMobile, $directCode)) {
        $_SESSION['msg'] = 'Please verify your WhatsApp OTP before creating an account.';
        header("location:sign-in.php#signupform");
        exit;
    }
}

// تعريف دالة ip_info إذا لم تكن موجودة (لـ PHP 8.3)
if (!function_exists('ip_info')) {
    function ip_info($ip = null, $purpose = "location", $deep_detect = true) {
        $output = null;
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            $ip = $_SERVER["REMOTE_ADDR"] ?? '';
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        }
        
        $purpose = str_replace(array("name", "\n", "\t", " ", "-", "_"), "", strtolower(trim($purpose)));
        $support = array("country", "countrycode", "state", "statecode", "city", "location", "address");
        
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(serialize($ipdat)) >= 1) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city" => @$ipdat->geoplugin_city,
                            "state" => @$ipdat->geoplugin_regionName,
                            "country" => @$ipdat->geoplugin_countryName,
                            "country_code" => @$ipdat->geoplugin_countryCode,
                        );
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                }
            }
        }
        return $output;
    }
}

$user_country = ip_info("Visitor", "Country") ?? '';
$phone_code='';
$default_country = $user_country;
$default_country_id = 0;

// تحسين استعلام SQL لاستخدام mysqli_real_escape_string
$safe_user_country = mysqli_real_escape_string($con, $user_country);
$sql="select * from country where cn_status = '1' and cn_name LIKE '$safe_user_country%' order by cn_id asc";
$result = mysqli_query($con, $sql);
if($result)
{
	while($row=mysqli_fetch_object($result))
	{
		$phone_code= $row->cn_ph ?? '';
		$default_country_id = $row->cn_id ?? 0;
		$default_country = $row->cn_name ?? '';
	}
}

if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']); }else{ $msg=""; }
if(isset($_SESSION['name_prefix'])){ $name_prefix=$_SESSION['name_prefix'];	unset($_SESSION['name_prefix']); }else{ $name_prefix=""; }
if(isset($_SESSION['fname'])){	$fname=$_SESSION['fname'];	unset($_SESSION['fname']); }else{ $fname=""; }
if(isset($_SESSION['lname'])){	$lname=$_SESSION['lname'];	unset($_SESSION['lname']); }else{ $lname=""; }
if(isset($_SESSION['email'])){	$email=$_SESSION['email'];	unset($_SESSION['email']); }else{ $email=""; }
if(isset($_SESSION['country'])){ $country=$_SESSION['country']; unset($_SESSION['country']); }else{ $country=""; }
if(isset($_SESSION['ph_country'])){ $ph_country=$_SESSION['ph_country']; unset($_SESSION['ph_country']); }else{ $ph_country=""; }
if(isset($_SESSION['mobile1'])){ $mobile1=$_SESSION['mobile1'];	unset($_SESSION['mobile1']); }else{ $mobile1=""; }
if(isset($_SESSION['website'])){ $website=$_SESSION['website'];	unset($_SESSION['website']); }else{ $website=""; }
if(isset($_SESSION['pass'])){	$pass=$_SESSION['pass']; unset($_SESSION['pass']); }else{ $pass=""; }
if(isset($_SESSION['accept'])){ $accept=$_SESSION['accept']; unset($_SESSION['accept']); }else{ $accept=""; }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" href="../../favicon.ico">
<title>index</title>
<!-- Bootstrap core CSS -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<!--    <link href="assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">-->

<!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
<!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
<!--   <script src="assets/js/ie-emulation-modes-warning.js"></script>-->

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

<!-- Custom styles for this template -->
<link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800,300' rel='stylesheet' type='text/css'>
<link href="css/template.css" rel="stylesheet">
<link href="css/font-awesome.css" rel="stylesheet">
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo htmlspecialchars(getSiteTitle() ?? '', ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />
<link rel="stylesheet" type="text/css" href="css/jquery.fileupload.css">
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
<script>

$(document).ready(function(){
	
$("#country_name").autocomplete("ajax-file/autocomplete_country.php", {
		selectFirst: true
	})
	.result(function(event, data, formatted) {

      $("#country").val(data[1]);
	  $("#ph_country").val(data[2]);
	  $("#reset").show();
	  $("input#country_name").attr('disabled','disabled');
	  
     $("#state").autocomplete("ajax-file/autocomplete_state.php?country="+$("#country").val(), {
		selectFirst: true
	})
	.result(function(event, data, formatted) {

      $("#b_state").val(data[1]);
	  $("#reset").show();
	  $("input#state").attr('disabled','disabled');
	});
	$("#city_others").autocomplete("ajax-file/showregisterUsercity.php", {
		selectFirst: true,
		extraParams: {country: $('#country').val()} 
	})
	.result(function(event, data, formatted) {
					
	  var dm =	data[0].split(">>");
 	  $("#city_others").val(dm[0]);
	  $("#state").val(dm[1]);
      $("#city").val(data[1]);
	  $("#b_state").val(data[2]);
	  $("#reset").show();
	  //$("input#country_name").attr('disabled','disabled');
	});
	
	});

});



	
 function CheckIfChanged() {		
	$("#state").autocomplete("ajax-file/autocomplete_state.php?country="+$("#country").val(), {
		selectFirst: true
	})
	.result(function(event, data, formatted) {

      $("#b_state").val(data[1]);
	  $("#reset").show();
	  $("input#state").attr('disabled','disabled');
	});
	
	$("#city_others").autocomplete("ajax-file/showregisterUsercity.php", {
		selectFirst: true,
		extraParams: { country :document.getElementById("country").value  } 
	})
	.result(function(event, data, formatted) {
					
	  var dm =	data[0].split(">>");
 	  $("#city_others").val(dm[0]);
	  $("#state").val(dm[1]);
      $("#city").val(data[1]);
	  $("#b_state").val(data[2]);
	  $("#reset").show();
	  //$("input#country_name").attr('disabled','disabled');
	});

	
	
	
	/*$(*"#designation").autocomplete("ajax-file/showdesignation.php", {
		selectFirst: true
	})
	.result(function(event, data, formatted) {
 	  $("#userdesignation").val(data[1]);
	});*/
	
}

setInterval(function(){ CheckIfChanged(); }, 1000);
 

function mable(){
		$("input#country_name").removeAttr('disabled');
		$("input#country_name").val('');
		 $("#ph_country").val('');
		$("input#country").val('');
		$("#reset").hide();
		}
</script>

<script type="text/javascript">
function checkExistingEmail(eml)
{
	$.post("ajax-file/isEmailExist.php", {eml:eml},	function(data){	
		$('#email_exists').val($.trim(data));
	});	
}
function checkvalid()
{
    
	var businessname = document.getElementById('business_name');
	var email=document.getElementById('email');
	var authority=document.getElementById('authority');
	var authority1=document.getElementById('authority1');
	var comapnyimage = document.getElementById('business_documents');
	var name_prefix=document.getElementById('name_prefix');	
	var fname=document.getElementById('fname');
	var lname=document.getElementById('lname');   
	var perposition =document.getElementById('userdesignation');
	var designation =document.getElementById('designation');
	var profileimage =document.getElementById('profile_photo');	
	var ph_country=document.getElementById('ph_country');
	var country=document.getElementById('country');
    var mobile1=document.getElementById('mobile1');
	var state=document.getElementById('b_state');
	var city=document.getElementById('city');
	var postal_code=document.getElementById('postal_code');	
    var website=document.getElementById('website');
	var pass=document.getElementById('pass');
	var accept = document.getElementById('accept');   
        
        
	var message="";
    var valid=true;	
	var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
 	if (email.value != '')
    {
		checkExistingEmail(email.value);		
    }
		
	
//	alert($("#email_exist").val());
	if(mobile1.value=='')
	{
		message="Please enter Mobile Number";
		mobile1.focus();
		valid=false;
	}
	else if(isNaN(mobile1.value))
	{
		message="Mobile number must be numeric";
		mobile1.focus();
		valid=false;
	}
	else if(mobile1.value.length != 10) {
		message="Mobile number must be 10 digit only.";
		mobile1.focus();
		valid=false;
	}
	else if(email.value == "" || email.value == null)
	{
		message="Please enter Email";
		email.focus();
		valid=false;
	}
	else if (!email.value.match(is_email))
    {
		message="من فضلك أدخل إيميل صالح ";
		email.value="";
        email.focus();
        valid = false;		
    }
	else if(document.getElementById('email_exists').value =='1')
	{
		message="هذا الميل مسجل من قبل ";
		email.focus();
		valid=false;
	}
	else if(country.value=='')
	{
		message="من فضلك إختار البلد";
		country.focus();
		valid=false;
	}
	else if(ph_country.value=='')
	{
		message="Country ISD Code Must Not Blank";
		ph_country.focus();
		valid=false;
	}
	else if(city.value=='')
	{
		message="من فضلك إختار المدينة ";
		city.focus();
		valid=false;
	}
	else if(state.value=='')
	{
		message="من فضلك أدخل المحافظة  ";
		state.focus();
		valid=false;
	}	
	else if(postal_code.value=='')
	{
		message="من فضلك أدخل الرمز البريدى";
		postal_code.focus();
		valid=false;
	}
	else if(fname.value=='')
	{
		message="من فضلك أدخل الإسم الأول";
		fname.focus();
		valid=false;
	}
   	else if(!isNaN(fname.value))
	{
		message="من فضلك أدخل إسم أول صالح";
		fname.value='';
		fname.focus();
		valid=false;
	}
   	else if(lname.value=='')
	{
		message="من فضلك أدخل الإسم الأخير";
		lname.focus();
		valid=false;
	}
   	else if(!isNaN(lname.value))
	{
		message="من فضلك أدخل إسم أخير صالح";
		lname.value='';
		lname.focus();
		valid=false;
	}
   else if(designation.value=='')
	{
		message=" من فضلك أدخل المسمى الوظيفى ";
		designation.focus();
		valid=false;
	}
	else if(businessname.value=='')
	{
		message=" من فضلك أدخل الإسم التجارى للشركة ";
		businessname.focus();
		valid=false;
	}
	/*else if($("#email_exist").val()==1)
	{
		message=" من فضلك أدخل عنوان بريد أخر هذا العنوان البريدى موجود سلفا ";
		email.focus();
		valid=false;
	}*/
	else if(authority.value=='')
	{
		message="من فضلك أدخل رقم السجل التجارى";
		authority.focus();
		valid=false;
	}
	else if(authority1.value=='')
	{
		message="من فضلك أدخل رقم البطاقة الضريبية ";
		authority1.focus();
		valid=false;
	}
	else if(comapnyimage.value=='')
	{
		message="من فضلك أضف أواق الشركة مثل كارت الشركة";
		valid=false;
	}
   	
    /*else if(profileimage.value=='')
	{
		message="من فضلك أضف صورة شخص الإتصال";
		perposition.focus();
		valid=false;
	}
	else if(website.value=='')
	{
		message=" من فضلك أدخل عنوان ويب ";
		website.focus();
		valid=false;
	}*/
   
    else if(website.value != '' && !website.value.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/))
	{
		message='من فضلك أدخل عنوان ويب صالح';
		website.focus();
		valid=false;
	}
	else if(pass.value=='')
	{
		message="من فصلك أكتب كلمة مرور";
		pass.focus();
		valid=false;
	}
	else if(pass.value.length <6)
	{
		message="كلمة المرور لاينبغى أن تقل عن 6 حروف";
		pass.value="";
		pass.focus();
		valid=false;
	}	
	else if(accept.checked==false)
	{
		message="يجب الموافقة على شرط الإستخدم";
		accept.focus();
		valid=false;
	}
	else
	{
		//valid = true;
		$.post("createAccount.php", {name_prefix:name_prefix.value,fname:fname.value,lname:lname.value,email:email.value,ph_country:ph_country.value,country:country.value,mobile1:mobile1.value,website:website.value,pass:pass.value,city:city.value,city_others:document.getElementById('city_others').value,state:state.value,state_others:document.getElementById('state').value,postal_code:postal_code.value,businessname:businessname.value,authority:authority.value,perposition:designation.value, profileimage:profileimage.value,comapnyimage:comapnyimage.value,authority1:authority1.value},	function(data){	
			console.log(data)
                        data=data.trim();
			dt=data.split("|")
			if(dt[0]=='0')
			{
				document.getElementById('message').style.display="block";
				document.getElementById('message').style.color = "red";
				document.getElementById('message').innerHTML = dt[1];
				
			}
			else
			{
				alert(dt[1]);
				window.location="membership_plans.php?from=1";
			}																  
		});
	}
	if(!valid)
	{
		document.getElementById('message').style.display="block";
		document.getElementById('message').style.color = "red";
		document.getElementById('message').innerHTML = message;	
	}
	return valid;
}

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


$(function () {
    
    // Change this to the location of your server-side upload handler:
    var url ='https://egyptmart.shop/server/php/';
    jQuery('#profileupload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
                jQuery('#profile_photo').val(file.name);
			    jQuery('#profilephoto').attr('src',file.thumbnailUrl);
            });
        }
    })
});


$(function () {
    
    // Change this to the location of your server-side upload handler:
    var url ='https://egyptmart.shop/server/php/';
    jQuery('#fileupload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
               jQuery('#business_documents').val(file.name);
			   jQuery('#business_doc').attr('src',file.thumbnailUrl);
            });
        }
       
    })
});
</script>
<script src="js/msdropdown/jquery.dd.min.js"></script>
<script>
$(document).ready(function() {
	$("#nncountrynn").msDropdown();
})
</script>
</head>

<body>
<header>
     
<div id="res-mob1">
       <?php include "includes/header_login.php"; ?>
        

</header>
<div id="middle">
  <div class="container" id="signupform">
  <div class="row">
  <div class="top-btn">
  <div class="col-sm-4">
  <div class="first-btn active"title="  Register Your Business Profile " ><span>1</span>   سجل مجانا الآن فى ثلاث خطوات</div>
  </div>
  <div class="col-sm-4">
  <div class="first-btn"><span>2</span>إختار خطة مجانية للبدء </div>
  </div>
  <div class="col-sm-4">
  <div class="first-btn"><span>3</span> إبدأ العمل والتجارة </div>
  </div>
  <div class="clr"></div>
  </div>
  
    <div class="clr"></div>
  
  </div>
  <div class="row">
  
  <div class="mid-left col-sm-5"title=" The EgyptMART Advantage  " >
 <div class="arobeb">
 <h3>مزايا الإنضمام الى بوابة التجارة  </h3> 
 <ul>
 <li>إنشىء كتالوج منتجاتك التجارية</li>
  <li>تلقى طلبات تسعير وشراء </li>
 <li>إبحث عن إحتياجات أعمالك </li>
   <li> تلقى إشعارات تجارية فى بريدك  </li>
    
 </ul>
  <div class="clr"></div>
 </div>
 
 <div class="mid-image"><center><img src="images/left-image.jpg"/></center></div>
 
 <div class="aloud">
 <div class="col-sm-6 s1">تجارة داخل بلدى فقط <span>1</span> </div>
  <div class="col-sm-6 s1">تجارة داخل وخارج بلدى <span>2</span>  </div>
    <div class="col-sm-6 s1">تجارة حول مدينتى فقط <span>3</span> </div>
   <div class="col-sm-6 s1">تجارة خارج بلدى فقط <span>4</span> </div>
      <div class="clr"></div>
 </div>
  </div>
  
  <div class="mid-right col-sm-7">
  <div class="warning-new">
  <img  style="float:right; padding-right:10px;" title=" hereby requests the members authentic information
towards their business statutory details.  " src="images/warning.jpg"/> يساعد الأدمن المستخدم فى تسجيل حسابه عند تواصله  
  <div>   </div>
 <script>
	 function thisisnowcode()
	 	{
			var necc = document.getElementById("nncountrynn").value;
			document.getElementById("country").value = necc;
			document.getElementById("ph_country").value = "+"+$("#nncountrynn").find(':selected').data("zipcode");
 
		}
		</script>
        <input name="country" value="<?php print (int)$default_country_id; ?>" id="country" type="hidden">	
  </div>
  
  <div class="account-from"title="  Create Your Business Account on EgyptMART " >
  <h2><strong>إنشىء حسابك على بوابة التجارة </strong></h2>
  <div class="warning" >إنشىء حسابك فى ثلاث خطوات سهلة 
</div>
  <form class="form-horizontal"  action="" method="post" name="ModReg">
   <div id="message" class="sbox nt bnr fw" style="text-align:left;width:389px;padding:1% 1% 1% 5%;display:none;margin-left:87px;"></div>
    <?php if(isset($msg) && $msg!=""){ ?>
	<div style="text-align:left;width:389px;padding:1% 1% 1% 5%;display:block;margin-left:87px;" class="sbox nt bnr fw" id="message"><?php echo htmlspecialchars($msg ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
    <?php } ?>
  <div class="form-group"title="Contact Mobile/ Cell Phone">
    <label for="inputEmail3" class="col-sm-4 control-label" >رقم المحمـول أو الهاتف الجوال  * </label>
    <div class="col-sm-7">
    <input type="text" class="form-control pull-left" maxlength="6" name="ph_country" id="ph_country"  placeholder="" style="width:20%; background:white" value="<?php echo htmlspecialchars($phone_code ?? '', ENT_QUOTES, 'UTF-8'); ?>">
     <input type="text" class="form-control  pull-right"  name="mobile1" id="mobile1" value="<?php echo htmlspecialchars($mobile1 ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="" style="width:80%;">
    </div>
  </div>
  
  <div class="form-group"title="Your Business Mail">
    <label for="inputEmail3" class="col-sm-4 control-label"> البريد الألكترونى للشركة * </label>
    <div class="col-sm-7">
    
      <input type="text" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="">
	  <input type="hidden" class="form-control" name="email_exists" id="email_exists" value="0" />
    </div>
  </div>
  <div class="form-group"title="Country">
    <label for="inputEmail3" class="col-sm-4 control-label">إختار البـلد *  </label>
    <div class="col-sm-7">
     <div class="">
    
        
     <select name="nncountrynn" id="nncountrynn" style="width:100%;" onChange="thisisnowcode(); CheckIfChanged();">
     <?php 
	 $sql="select * from country where cn_status = '1'  order by cn_id asc";
	$result = mysqli_query($con, $sql);
 	while($row=mysqli_fetch_object($result)) { ?>
  <option value='<?php echo (int)$row->cn_id; ?>' data-zipcode="<?php echo htmlspecialchars(ucfirst($row->cn_ph ?? ''), ENT_QUOTES, 'UTF-8'); ?>" <?php if($default_country_id==$row->cn_id) { echo 'selected'; } ?> data-image="images/country_flag/<?php echo htmlspecialchars($row->cn_flag ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-imagecss="flag ad" data-title="<?php echo htmlspecialchars(ucfirst($row->cn_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(ucfirst($row->cn_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></option>
 <?php } ?>
</select>
     


</div>
    </div>
  </div>
  <div class="form-group"title="إختار مدينتك من القائمة">
    <label for="inputEmail3" class="col-sm-4 control-label">إكتب المدينة باللغة الإنجليزية * </label>
    <div class="col-sm-7">
	<input name="city" value="" id="city" type="hidden">
      <input type="text" class="form-control" id="city_others" name="city_others" placeholder="المدينة"  style="width:30%; display:inline-block;">
	<input name="b_state" value="" id="b_state" type="hidden">
      <input type="text" class="form-control" name="state" id="state"    placeholder="المحافظة" style="width:34%; display:inline-block;">
      <input type="text" class="form-control" style="width:34%; display:inline-block;" name="الرمز البريدى" id="postal_code" placeholder="الرمز البريدى">
    </div>
  </div>
  <div class="form-group"title="Contact Person">
    <label for="inputEmail3" class="col-sm-4 control-label">شخص الإتصال المفوض  *</label>
    <div class="col-sm-7">
    <!--<div class="row">
  <div class="col-xs-3">
    <input type="text" class="form-control" placeholder=".col-xs-2">
  </div>
  <div class="col-xs-4">
    <input type="text" class="form-control" placeholder="">
  </div>
  <div class="col-xs-5">
    <input type="text" class="form-control" placeholder="">
  </div>
</div>-->
    
    <select class="form-control" style="width:30%; display:inline-block;" name="name_prefix" id="name_prefix">
  <?php
										$arr=array("السيد","الأنسة","السيدة","دكتور","مهندس");
foreach($arr as $val)
										{
										?>
<option value="<?php echo htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>" <?php if($val==$name_prefix) { ?> selected="selected" <?php } ?> ><?php echo htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?> </option>
<?php } ?>

</select>
 
          <input type="text" class="form-control" name="fname" id="fname" value="<?php echo htmlspecialchars($fname ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="الإسم الثانى" style="width:34%; display:inline-block;">
              <input type="text" class="form-control" name="lname" id="lname" value="<?php echo htmlspecialchars($lname ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="الإسم الأول" style="width:34%; display:inline-block;">
    </div>
  </div>
  <div class="form-group"title="المسمى الوظيفى">
    <label for="inputEmail3" class="col-sm-4 control-label"> وظيفة مسئول الإتصال * </label>
    <div class="col-sm-7">
      <input name="designation" id="designation" autocomplete="on" aria-haspopup="true" aria-autocomplete="list" role="textbox" placeholder="أكتب المسمى الوظيفى" class="form-control" type="text" onClick="blankdesignation()">
      <input type="hidden" name="userdesignation" id="userdesignation">
    </div>
  </div>
  <div class="form-group"title="Company Business Name">
    <label for="inputEmail3" class="col-sm-4 control-label"> الإسم التجارى للشركة * </label>
    <div class="col-sm-7">
      <input type="text" class="form-control" placeholder="" name="business_name" value="" id="business_name">
    </div>
  </div>
  <div class="form-group"title="Registration Authority No.">
    <label for="inputEmail3" class="col-sm-4 control-label">رقم السجل التجارى * </label>
    <div class="col-sm-7">
      <input type="text" class="form-control" value="" size="30" name="authority" id="authority" placeholder="">
    </div>
  </div>
  <div class="form-group"title=" Service Tax. No.">
    <label for="inputEmail3" class="col-sm-4 control-label" > رقم التسجيل الضريبى  *</label>
    <div class="col-sm-7">
      <input type="text" class="form-control"  value="" size="30" name="authority1" id="authority1" placeholder="">
    </div>
  </div>
  
  
  
  
  
  
  <div class="form-group"title="One Company Documents">
    <label for="inputEmail3" class="col-sm-4 control-label">حمل صورة مستند صحة الشركة * </label>
    <div class="col-sm-7 upload_div">
    <input type="hidden" name="business_documents" id="business_documents">
    <img  style="float:left; margin-right:10px;" src="<?php echo htmlspecialchars(BASE_URL ?? '', ENT_QUOTES, 'UTF-8'); ?>/images/CompanyImage.jpg" id="business_doc"/>
    <input id="fileupload" type="file" name="files"  style="cursor:pointer;">
	<span class="file_input">حمل المستند</span>
                     
       مثال صورة السجل التجارى أو البطاقة الضريبية أو كارت الشركة أو أى مستند دال الخ
    </div>
  </div>
  <div class="form-group"title="Contact Person Image">

<label for="inputEmail3" class="col-sm-4 control-label">  صورة مسئول إتصال الشركة </label>
    <div class="col-sm-7 upload_div">
<input type="hidden" name="profile_photo" id="profile_photo">
    <img  style="float:left; margin-right:10px;" src="<?php echo htmlspecialchars(BASE_URL ?? '', ENT_QUOTES, 'UTF-8'); ?>/images/uploadd.png" id="profilephoto"/>
     <input id="profileupload" type="file" name="files" style="cursor:pointer;" />
	<span class="file_input">Add image</span>
        
        ( إختيارى )             
        صورة مسئول اتصال الشركة 	
    </div>
  </div>
  <div class="form-group"title="Website Url">
    <label for="inputEmail3" class="col-sm-4 control-label"> الموقع الألكترونى للشركة</label>
    
    <div class="col-sm-7">
      <input type="text" class="form-control" value="<?php echo htmlspecialchars($website ?? '', ENT_QUOTES, 'UTF-8'); ?>" id="website" name="website" placeholder="">
      <span id="helpBlock" class="help-block">http://example.com	 مثـال </span>    </div>
  </div>
  <div class="form-group" title="Create Password">
    <label for="inputEmail3" class="col-sm-4 control-label">رجاء إنشاء كلمة مرور خاصة بك * </label>
    <div class="col-sm-7">
      <input name="pass" id="pass" type="password" value="<?php echo htmlspecialchars($pass ?? '', ENT_QUOTES, 'UTF-8'); ?>"  class="form-control" placeholder="">
    </div>
    </div>
  <div class="form-group">
    <div class="col-sm-offset-4 col-sm-7">
      <div class="new_checkbox">
        <label>
          <input value="yes" name="accept" id="accept" type="checkbox">    <a href="terms.php" target="_new">  نعـم أوافـق عـلى  شـروط الإستخـدام</a>
        </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-4 col-sm-7">
      <input type="button" name="register"title="إختار خطة العضوية " value=" << إختار نوع عضويتك المجانية على المنصة " class="btn btn-danger"  onclick="checkvalid();" /> <br><br> <span class="pull-right text-center"title="  Already Member ? ">  هل أنت عضو من قبل ؟ <br> <a href="http://egyptmart.shop/sign-in.php" style="font-weight:bold; color:#00F; font-size:18px;"title=" Sign in  ">سجل دخول</a></span>
    </div>
  </div>
</form>

   <div class="clr"></div>
  </div>
   <div class="clr"></div>
  </div>
  <div class="clr"></div>
  
  </div>
  
  
  
  
  
    <div class="clr"></div>
  </div>
  
  <div class="clr"></div>
</div>
<!--footer:start-->
		<?php include 'includes/footer.php'; ?>
<!--      <script src="http://www.marghoobsuleman.com/misc/jquery.js"></script>-->
<link rel="stylesheet" type="text/css" href="css/msdropdown/dd.css" />

<link rel="stylesheet" type="text/css" href="css/msdropdown/flags.css" />

<style>
.divider { display:none; }
.ddTitleText  {     background: white; }

.dd .ddTitle .ddTitleText {
    padding: 8px 45px 7px 6px;
    border: 1px solid #cccccc42;
    border-radius: 4px;
    height: 32px;
}
</style>
<style>

.ddChild { overflow: scroll!important; }
</style>
		
</div>
