<?php
include 'common.php';
if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '')
{
	header("location:index.php");
}

$user_country = ip_info("Visitor", "Country");
$phone_code='';
$default_country = $user_country;
$default_country_id = 0;
$sql="select * from country where cn_status = '1' and cn_name LIKE '$user_country%' order by cn_id asc";
	$result = mysqli_query($con, $sql);
	if($result)
	{
		while($row=mysqli_fetch_object($result))
		{
			$phone_code= $row->cn_ph;
			$default_country_id = $row->cn_id;
			$default_country = $row->cn_name;
			
			
			
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

/*if(isset($_POST['fname']))
{
echo "<pre>"; print_r($_POST); echo "</pre>";exit;	
    
    
	$name_prefix= addslashes(trim($_POST['name_prefix']));
	$fname= addslashes(trim($_POST['fname']));	
	$lname= addslashes(trim($_POST['lname']));	
	$email= addslashes(trim($_POST['email']));
	$country= addslashes(trim($_POST['country']));
	$ph_country = $_POST['ph_country'];
	$mobile1= addslashes(trim($_POST['mobile1']));
	$website= addslashes(trim($_POST['website']));
	$pass= addslashes(trim($_POST['pass']));
	$npass= md5($pass);
	$accept=$_POST['accept'];
	
	$_SESSION['name_prefix']=$name_prefix;
	$_SESSION['fname']=$fname;
	$_SESSION['lname']=$lname;
	$_SESSION['email']=$email;
	$_SESSION['country']=$country;
	$_SESSION['ph_country']=$ph_country;
	$_SESSION['mobile1']=$mobile1;
	$_SESSION['website']=$website;
	$_SESSION['pass']=$pass;
	$_SESSION['accept']=$accept;
	
	$msg="";	
	$valid=true;
	$em=1;
	
	$sql_chk="select * from user where email='".$email."' and status=1";
	$res_chk=mysql_query($sql_chk);
	if(mysql_num_rows($res_chk)>0)
	{	
		$em=0;		
	}
	if($fname == '')
	{
		$msg="<font color=red>Please enter first name</font>";
		$valid=false;	
	}
	else if($email=="")
	{
		$msg='<font color="#CC0000">Please enter email</font>';
		$valid=false;	
	}
	else if(!validate::is_email($email))
	{
		$msg='<font color="#CC0000">Please enter valid email</font>';
		$valid=false;		
	}
	else if($em==0)
	{	
		$msg="<font color=red>Please enter another Email Id. User already exist with this ID.</font>";
		$valid=false;				
	}
	else if($country=="")
	{
		$msg="<font color=red>Please select country.</font>";
		$valid=false;	
	}
	else if($ph_country =="")
	{
		$msg="<font color=red>Country ISD Code Must Not Blank.</font>";
		$valid=false;	
	}
	else if($mobile1 =="")
	{
		$msg="<font color=red>Please Enter Mobile.</font>";
		$valid=false;	
	}
	else if($website != '' && !(validate::is_weblink($website)))
	{
		$msg="<font color=red>Please Enter a Valid Web Link</font>";
		$valid=false;
	}
	else if($pass=="")
	{
		$msg="<font color=red>Please enter password</font>";
		$valid=false;	
	}
	else if($accept=="")
	{
		$msg="<font color=red>You must agree to the Terms of Use for your registration.</font>";
		$valid=false;	
	}
	else
	{
		$valid=true;	
	}
	
	if($valid==true)
	{	
		$insert1="insert into user 
				set
				   email='".$email."',
				   name_prefix='".$name_prefix."',
				   fname='".$fname."', 
				   lname='".$lname."',
				   country_ph_code = '".$ph_country."',
				   country='".$country."',
				   mobile1='".$mobile1."',
				   pass='".$npass."',
				   website='".$website."',
				   date=now()";
				   
		mysql_query($insert1);
		$id=mysql_insert_id();
		
		if(getEmailVerificationStatus()==0)
		{
			$sql_veify_upd="update user
				set
					usr_emailVerify='1'
				where
					usr_id='".$id."'";
			mysql_query($sql_veify_upd);
		}
		
		$sql_bpf="insert into business_profile
			set
				bnsprof_uid='".$id."',
				bnsprof_creation_date=now()";
		mysql_query($sql_bpf);
		
		$sql_webst="insert into website_content
			set
				wc_usr_id='".$id."',
				wc_updated_date=now()";
		mysql_query($sql_webst);
		
		
		$_SESSION['uid_indm']=$id;
		$_SESSION['eml_indm']= $email;
		$_SESSION['msg']=$msg;
		
		if(getEmailVerificationStatus()==1)
		{
		
			$link = "<a href=http://".$_SERVER['SERVER_NAME']."/verifyUser.php?token=".rand(1000,9999).md5($_SESSION['uid_indm']).">Verify</a>";
			$to = stripslashes(user_info($_SESSION['uid_indm'],'email'));  /*Put Your Email Adress Here
			$subject = "Email Verification from ".get_page_settings(4);
			$from_name = get_page_settings(4);
			$from_email = get_adminemail();
			
			include "email/emailVerification.php"; //email design with content included
			
			/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
			$message .= "We are happy you joined. Please click on folowing link to verify your email with us : ".$link;
			$message .= "<br /><br />".get_page_settings(4)." Team";
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";

			if(mail($to, $subject, $message1, $headers)){	header("location:home.php?verifylinksend=1");	}
		
		}
		else
		{
			$to = stripslashes(user_info($_SESSION['uid_indm'],'email'));  /*Put Your Email Adress Here
			$subject = "Welcome to ".get_page_settings(4);
			$from_name = get_page_settings(4);
			$from_email = get_adminemail();
			
			include "email/emailVerification.php"; //email design with content included
			
			/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
			$message .= "We are happy you joined.";
			$message .= "<br /><br />".get_page_settings(4)." Team";
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";

			if(mail($to, $subject, $message2, $headers)){	header("location:home.php?verifylinksend=1");	)
		
		
		/********** Email Notification to Admin on User Creation **********
			$sql_cn="select * from country where cn_id='".$country."'";
			$res_cn=mysql_query($sql_cn);
			$row_cn=mysql_fetch_object($res_cn);
			
			$to = get_adminemail();
			$subject = "User Creation Notification";
			$from_name = get_page_settings(4);
			$from_email = get_adminemail();
			
			include "email/emailVerification.php"; //email design with content included
			
			/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
			$message .= "We are happy you joined.";
			$message .= "<br /><br />".get_page_settings(4)." Team";
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";

			if(mail($to, $subject, $message_admin, $headers)){	header("location:home.php?verifylinksend=1");	}
		/*********************
		
		
		unset($_SESSION['name_prefix']);
		unset($_SESSION['email']);
		unset($_SESSION['fname']);
		unset($_SESSION['lname']);
		unset($_SESSION['website']);
		unset($_SESSION['pass']);
		unset($_SESSION['ph_country']);
		unset($_SESSION['mobile1']);
		unset($_SESSION['country']);
		unset($_SESSION['accept']);
		
		header("location:index.php");
	}
}*/
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
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
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
		extraParams: {country: $('#nncountrynn').val()} 
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

	
	
	
	/*$(*"#designation").autocomplete("ajax-file/showdesignation.php", {
		selectFirst: true
	})
	.result(function(event, data, formatted) {
 	  $("#userdesignation").val(data[1]);
	});*/
	
});

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
	else if(email.value == "" || email.value == null)
	{
		message="Please enter Email";
		email.focus();
		valid=false;
	}
	else if (!email.value.match(is_email))
    {
		message="Please enter valid email";
		email.value="";
        email.focus();
        valid = false;		
    }
	else if(document.getElementById('email_exists').value =='1')
	{
		message="Email already exists.";
		email.focus();
		valid=false;
	}
	else if(country.value=='')
	{
		message="Please select country";
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
		message="Please enter city";
		city.focus();
		valid=false;
	}
	else if(state.value=='')
	{
		message="Please enter State";
		state.focus();
		valid=false;
	}	
	else if(postal_code.value=='')
	{
		message="Please enter postal Code";
		postal_code.focus();
		valid=false;
	}
	else if(fname.value=='')
	{
		message="Please enter First Name";
		fname.focus();
		valid=false;
	}
   	else if(!isNaN(fname.value))
	{
		message="Please enter valid First Name";
		fname.value='';
		fname.focus();
		valid=false;
	}
   	else if(lname.value=='')
	{
		message="Please enter Last Name";
		lname.focus();
		valid=false;
	}
   	else if(!isNaN(lname.value))
	{
		message="Please enter valid Last Name";
		lname.value='';
		lname.focus();
		valid=false;
	}
   else if(designation.value=='')
	{
		message="Please enter Job title.";
		designation.focus();
		valid=false;
	}
	else if(businessname.value=='')
	{
		message="Please enter Business Name";
		businessname.focus();
		valid=false;
	}
	/*else if($("#email_exist").val()==1)
	{
		message="Please enter another Email Id. User already exist with this ID.";
		email.focus();
		valid=false;
	}*/
	else if(authority.value=='')
	{
		message="Please enter Registration Authority No.";
		authority.focus();
		valid=false;
	}
	else if(authority1.value=='')
	{
		message="Please enter Service Tax No.";
		authority1.focus();
		valid=false;
	}
	else if(comapnyimage.value=='')
	{
		message="Please Add Business Document";
		valid=false;
	}
   	
    /*else if(profileimage.value=='')
	{
		message="Please Add Contact Person Photo";
		perposition.focus();
		valid=false;
	}
	else if(website.value=='')
	{
		message="Please Enter Valid website Link";
		website.focus();
		valid=false;
	}*/
   
    else if(website.value != '' && !website.value.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/))
	{
		message='Please Enter Valid website Link';
		website.focus();
		valid=false;
	}
	else if(pass.value=='')
	{
		message="Please enter password";
		pass.focus();
		valid=false;
	}
	else if(pass.value.length <6)
	{
		message="Password must be 6 characters long";
		pass.value="";
		pass.focus();
		valid=false;
	}	
	else if(accept.checked==false)
	{
		message="You must agree to the Terms of Use for your registration.";
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
    var url ='http://arabyos.com/server/php/';
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
    var url ='http://arabyos.com/server/php/';
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
       <?php include "includes/header_new.php"; ?>
        
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

</header>
<div id="middle">
  <div class="container">
  <div class="row">
  <div class="top-btn">
  <div class="col-sm-4">
  <div class="first-btn active"><span>1</span>Register Your Business Profile </div>
  </div>
  <div class="col-sm-4">
  <div class="first-btn"><span>2</span>Select Membership Type </div>
  </div>
  <div class="col-sm-4">
  <div class="first-btn"><span>3</span>Create Your Account on EgyptMART </div>
  </div>
  <div class="clr"></div>
  </div>
  
    <div class="clr"></div>
  
  </div>
  <div class="row">
  
  <div class="mid-left col-sm-5">
 <div class="arobeb">
 <h3>The EgyptMART Advantage </h3>
 <ul>
 <li>Create Your FREE Catalog</li>
  <li>Got listing in relevant products categories </li>
   <li>Find new markets by promoting your products 24×7</li>
    <li>Get business enquiries from buyers across the world</li>
 </ul>
  <div class="clr"></div>
 </div>
 
 <div class="mid-image"><center><img src="images/left-image.jpg"/></center></div>
 
 <div class="aloud">
 <div class="col-sm-6 s1"><span>1</span> All Over the World</div>
  <div class="col-sm-6 s1"><span>2</span> In Arab Countries</div>
   <div class="col-sm-6 s1"><span>3</span> In Your Country</div>
    <div class="col-sm-6 s1"><span>4</span> In Your City Only</div>
      <div class="clr"></div>
 </div>
  </div>
  
  <div class="mid-right col-sm-7">
  <div class="warning-new">
  <img  style="float:left; padding-right:10px;" src="images/warning.jpg"/>
  <div>EgyptMART hereby requests the members authentic information
towards their business statutory details.</div>
  </div>
  
  <div class="account-from">
  <h2><strong>Create Your Account on EgyptMART </strong></h2>
  <div class="warning"> Authenticated company info will help EgyptMART to promote your Products / Services successfully!
</div>
  <form class="form-horizontal"  action="" method="post" name="ModReg">
   <div id="message" class="sbox nt bnr fw" style="text-align:left;width:389px;padding:1% 1% 1% 5%;display:none;margin-left:87px;"></div>
    <?php if(isset($msg) && $msg!=""){ ?>
	<div style="text-align:left;width:389px;padding:1% 1% 1% 5%;display:block;margin-left:87px;" class="sbox nt bnr fw" id="message"><?php echo $msg; ?></div>
    <?php } ?>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Contact mobile/ Cell Phone</label>
    <div class="col-sm-7">
    <input type="text" class="form-control pull-left" maxlength="6" name="ph_country" id="ph_country"  placeholder="" style="width:20%; background:white" value="<?php echo $phone_code;?>">
     <input type="text" class="form-control  pull-right"  name="mobile1" id="mobile1" value="<?php echo $mobile1?>" placeholder="" style="width:80%;">
    </div>
  </div>
  
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Your Business Mail</label>
    <div class="col-sm-7">
    
      <input type="text" class="form-control" name="email" id="email" value="<?php echo $email; ?>" placeholder="">
	  <input type="hidden" class="form-control" name="email_exists" id="email_exists" value="0" />
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Country </label>
    <div class="col-sm-7">
     <div class="">
     <script>
	 function thisisnowcode()
	 	{
			var necc = document.getElementById("nncountrynn").value;
			document.getElementById("country").value = necc;
			document.getElementById("ph_country").value = "+"+$("#nncountrynn").find(':selected').data("zipcode");
 
		}
		</script>
        <input name="country" value="<?php print $default_country_id; ?>" id="country" type="text">	
        
     <select name="nncountrynn" id="nncountrynn" style="width:100%;" onChange="thisisnowcode()">
     <?php 
	 $sql="select * from country where cn_status = '1'  order by cn_id asc";
	$result = mysqli_query($con, $sql);
 	while($row=mysqli_fetch_object($result)) { ?>
  <option value='<?php echo $row->cn_id; ?>' data-zipcode="<?php echo ucfirst($row->cn_ph); ?>" <?php if($default_country_id==$row->cn_id) { echo 'selected'; } ?> data-image="images/country_flag/<?php echo $row->cn_flag; ?>" data-imagecss="flag ad" data-title="<?php echo ucfirst($row->cn_name); ?>"><?php echo ucfirst($row->cn_name); ?></option>
 <?php } ?>
</select>
     


</div>
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* State / City</label>
    <div class="col-sm-7">
	<input name="city" value="" id="city" type="hidden">
      <input type="text" class="form-control" id="city_others" name="city_others" placeholder="City"  style="width:30%; display:inline-block;">
	<input name="b_state" value="" id="b_state" type="hidden">
      <input type="text" class="form-control" name="state" id="state"    placeholder="State" style="width:34%; display:inline-block;">
      <input type="text" class="form-control" style="width:34%; display:inline-block;" name="postal_code" id="postal_code" placeholder="Postal/Zip Code">
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label"> *Contact Person</label>
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
										$arr=array("Mr.","Ms.","Mrs.","Dr.","Eng.");
foreach($arr as $val)
										{
										?>
<option value="<?php echo $val;?>" <?php if($val==$name_prefix) { ?> selected="selected" <?php } ?> ><?php echo $val;?> </option>
<?php } ?>

</select>
 
          <input type="text" class="form-control" name="fname" id="fname" value="<?php echo $fname; ?>" placeholder="First Name" style="width:34%; display:inline-block;">
              <input type="text" class="form-control" name="lname" id="lname" value="<?php echo $lname; ?>" placeholder="Last Name" style="width:34%; display:inline-block;">
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Designation / Job Title</label>
    <div class="col-sm-7">
      <input name="designation" id="designation" autocomplete="on" aria-haspopup="true" aria-autocomplete="list" role="textbox" placeholder="Type Designation / Job Title" class="form-control" type="text" onClick="blankdesignation()">
      <input type="hidden" name="userdesignation" id="userdesignation">
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Business Name</label>
    <div class="col-sm-7">
      <input type="text" class="form-control" placeholder="" name="business_name" value="" id="business_name">
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Registration Authority No.</label>
    <div class="col-sm-7">
      <input type="text" class="form-control" value="" size="30" name="authority" id="authority" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Service Tax. No.</label>
    <div class="col-sm-7">
      <input type="text" class="form-control"  value="" size="30" name="authority1" id="authority1" placeholder="">
    </div>
  </div>
  
  
  
  
  
  
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Company Documents</label>
    <div class="col-sm-7 upload_div">
    <input type="hidden" name="business_documents" id="business_documents">
    <img  style="float:left; margin-right:10px;" src="<?php echo BASE_URL ?>/images/CompanyImage.jpg" id="business_doc"/>
    <input id="fileupload" type="file" name="files"  style="cursor:pointer;" >
	<span class="file_input">Add File</span>
                     
       e.g Registration Authority , Service Tax and 
   Business Card Documents, etc ..	
    </div>
  </div>
  <div class="form-group">

<label for="inputEmail3" class="col-sm-4 control-label"> Contact Person</label>
    <div class="col-sm-7 upload_div">
<input type="hidden" name="profile_photo" id="profile_photo">
    <img  style="float:left; margin-right:10px;" src="<?php echo BASE_URL ?>/images/upload.png" id="profilephoto"/>
     <input id="profileupload" type="file" name="files" style="cursor:pointer;" />
	<span class="file_input">Add image</span>
        
                     
        Add contact person photo to enhance your business visual impact.	
    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label"> Website Url</label>
    
    <div class="col-sm-7">
      <input type="text" class="form-control" value="<?php echo $website;?>" id="website" name="website" placeholder="">
      <span id="helpBlock" class="help-block">http://example.com	</span>    </div>
  </div>
  <div class="form-group">
    <label for="inputEmail3" class="col-sm-4 control-label">* Create Password</label>
    <div class="col-sm-7">
      <input name="pass" id="pass" type="password" value="<?php echo $pass;?>"  class="form-control" placeholder="">
    </div>
    </div>
  <div class="form-group">
    <div class="col-sm-offset-4 col-sm-7">
      <div class="new_checkbox">
        <label>
          <input value="yes" name="accept" id="accept" type="checkbox">Yes I Agree <a href="terms.php" target="_new">Terms of Use</a>
        </label>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-4 col-sm-7">
      <input type="button" name="register" value="Select Membership Type >>" class="btn btn-danger"  onclick="checkvalid();" /> <span class="pull-right text-center"> Already Member ?<br> <a href="http://arabyos.com/sign-in.php" style="font-weight:bold; color:#00F; font-size:20px;">Sign in</a></span>
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