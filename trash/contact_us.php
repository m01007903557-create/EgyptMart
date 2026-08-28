<?php
include 'common.php';

$_SESSION['last_page']="contact_us.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
  $sql = "SELECT * FROM `user` WHERE `usr_id` = '".$_SESSION['uid_indm']."'  LIMIT 1";
  $qry = mysqli_query($con, $sql) or die(mysql_error());
  $user_detail = mysqli_fetch_array( $qry);
$usqlcountry = "select cn_name from country where cn_id='". $user_detail['country']."'";
	   $urscountry = mysqli_query($con,$usqlcountry);
	   if(mysqli_num_rows($urscountry) > 0)
	   {
		  $urowcountrty = mysqli_fetch_object($urscountry);
		  $user_cn_name = $urowcountrty->cn_name;
	   }

if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']); }
if(isset($_SESSION['cu_fname'])){	$cu_fname=$_SESSION['cu_fname'];	unset($_SESSION['cu_fname']);	}else { $cu_fname=$user_detail['fname']; }
if(isset($_SESSION['cu_lname'])){	$cu_lname=$_SESSION['cu_lname'];	unset($_SESSION['cu_lname']);	}else { $cu_lname=$user_detail['lname']; }
if(isset($_SESSION['cu_contactnumber'])){	$cu_contactnumber=$_SESSION['cu_contactnumber'];	unset($_SESSION['cu_contactnumber']);	}else { $cu_contactnumber=$user_detail['mobile1']; }
if(isset($_SESSION['cu_state'])){	$cu_state=$_SESSION['cu_state'];	unset($_SESSION['cu_state']);	}else { $cu_state=$user_detail['state']; }
if(isset($_SESSION['cu_country'])){	$cu_country=$_SESSION['cu_country'];	unset($_SESSION['cu_country']);	}else { $cu_country=$user_cn_name; }
if(isset($_SESSION['cu_email'])){	$cu_email=$_SESSION['cu_email'];	unset($_SESSION['cu_email']);	}else { $cu_email=$user_detail['email']; }
if(isset($_SESSION['cu_comments'])){	$cu_comments=$_SESSION['cu_comments'];	unset($_SESSION['cu_comments']);	}

class addContact{
	
	var $cu_fname;
	var $cu_lname;
	var $cu_email;	
	var $cu_contactnumber;
        var $cu_country;	
		var $cu_state;
	var $cu_comments;
		
	function __construct($cu_fname, $cu_lname, $cu_email, $cu_contactnumber, $cu_country,$cu_state, $cu_comments)
	{

		$this->cu_fname=$cu_fname;
		$this->cu_lname=$cu_lname;
		$this->cu_email=$cu_email;
		$this->cu_contactnumber=$cu_contactnumber;
		$this->cu_country=$cu_country;
		$this->cu_state=$cu_state;
		$this->cu_comments=$cu_comments;
	}
	
	function valid(){	
	
		$valid=true;	
									
		if($this->cu_fname=="")
		{
			$this->msg='<font color="#CC0000">Please enter first name.</font>';
			$valid=false;
		}
		else if (!validate::is_name($this->cu_fname))
		{
			$this->msg= '<font color="#CC0000">Please enter correct first name.</font>';
			$valid=false;
		}
		else if($this->cu_lname=="")
		{
			$this->msg= '<font color="#CC0000">Please enter last name.</font>';
			$valid=false;
		}
		else if (!validate::is_name($this->cu_lname))
		{
			$this->msg= '<font color="#CC0000">Please enter correct last name.</font>';
			$valid=false;
		}
		else if($this->cu_email=="")
		{
			$this->msg= '<font color="#CC0000">Please enter your email address</font>';
			$valid=false;
		}
		else if (!validate::is_email($this->cu_email))
		{
			$this->msg= '<font color="#CC0000">Please enter valid email address</font>';
			$valid=false;
		}	
		else if($this->cu_contactnumber=="")
		{
			$this->msg= '<font color="#CC0000">Please enter your contact number</font>';
			$valid=false;
		}
                else if($this->cu_country =="")
		{
			$this->msg= '<font color="#CC0000">Please enter your country</font>';
			$valid=false;
		}
		       else if($this->cu_state =="")
		{
			$this->msg= '<font color="#CC0000">Please enter your state</font>';
			$valid=false;
		}
		else if($this->cu_comments=="")
		{
			$this->msg= '<font color="#CC0000">Please enter comments</font>';
			$valid=false;
		}
					
		return $valid;
	}
	
	function set_session()
	{
		$_SESSION['cu_fname']=$this->cu_fname;
		$_SESSION['cu_lname']=$this->cu_lname;
		$_SESSION['cu_country ']=$this->cu_country ;
		$_SESSION['cu_state ']=$this->cu_state ;
		$_SESSION['cu_contactnumber']=$this->cu_contactnumber;
		$_SESSION['cu_email']=$this->cu_email;
		$_SESSION['cu_comments']=$this->cu_comments;	
	}
	
	function add()
	{	
                global $con;						
		$sql="insert into contact_us 
				set 
					cu_fname='".ucwords($this->cu_fname)."',
					cu_lname='".ucwords($this->cu_lname)."',
					cu_contactnumber='".$this->cu_contactnumber."',
                                        cu_country ='".$this->cu_country ."',
				        cu_state ='".$this->cu_state ."',
					cu_email='".$this->cu_email."',		
                                        cu_user_id=".$_SESSION['uid_indm'].",		                                        
					cu_comments='".ucwords($this->cu_comments)."',					 
					cu_updated_date=now()";		
		mysqli_query($con, $sql) or die(mysql_error());
		$this->msg='<font color="#e80d0d">Thank you, your enquiry has been sent and we will contact you shortly.</font>';

		

		/********************* Email sending code start here **********************/
		
		$to = $this->cu_email;  /*Put Your Email Adress Here*/
		$subject = "Contact form submission on ".get_page_settings(4);
		$from_name = get_page_settings(4);
		$from_email = get_adminemail();
			
		include "email/contact_us.php"; //email design with content included
			
		/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
		$message .= "We are happy you joined. Please click on folowing link to verify your email with us : ".$link;
		$message .= "<br /><br />".get_page_settings(4)." Team";*/
		$headers  = "MIME-Version: 1.0\r\n";
	    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	$headers .= "From: $from_name < $from_email >\r\n";
    	$headers .= "Reply-To: $from_email";

		mail($to, $subject, $message1, $headers);
		
		/********************* Email sending code end here **********************/
		
		/********************* Email sending code to admin start here **********************/
		
		$to = get_adminemail();  /*Put Your Email Adress Here*/
		$subject = "Contact form submission on ".get_page_settings(4);
		$from_name = get_page_settings(4);
		$from_email = get_adminemail();
			
		include "email/contact_us.php"; //email design with content included
			
		/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
		$message .= "We are happy you joined. Please click on folowing link to verify your email with us : ".$link;
		$message .= "<br /><br />".get_page_settings(4)." Team";*/
		$headers  = "MIME-Version: 1.0\r\n";
	    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	$headers .= "From: $from_name < $from_email >\r\n";

		mail($to, $subject, $message2, $headers);
		
		/********************* Email sending code to admin end here **********************/
		
	}
}
if(isset($_POST['contactSubmit']))
{		

	$adn=new addContact(addslashes(trim($_POST['cu_fname'])),addslashes(trim($_POST['cu_lname'])), $_POST['cu_email'], addslashes(trim($_POST['cu_contactnumber'])),  addslashes(trim($_POST['cu_country'])),  addslashes(trim($_POST['cu_state'])),
		 addslashes(trim($_POST['cu_comments'])));

		
	if($adn->valid())
	{	
	
		$adn->add();			
	}
	else
	{ 			
		$adn->set_session();			
	}		
	$_SESSION['msg']=$adn->msg;								
	header("location:contact_us.php");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">


<link rel="shortcut icon" type="/x-icon" href="images/favicon.ico">
<link href="css/style.css" rel="stylesheet" type="text/css">
<!--include-->
<link rel="stylesheet" type="text/css" href="css/header-style.css">
<!--include end-->
<!--navigation-->
<link rel="stylesheet" type="text/css" href="css/ddsmoothmenu1.css">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>

<!--navigation-->
<style type="text/css">
<!--
.style2 {font-weight: bold}
-->
</style>
<style> 
 .inputs  { 
-webkit-border-radius: 3px; 
-moz-border-radius: 3px; 
-ms-border-radius: 3px; 
-o-border-radius: 3px; 
border-radius: 3px; 
-webkit-box-shadow: 0 1px 0 #FFF, 0 -2px 5px #F0E1FF inset; 
-moz-box-shadow: 0 1px 0 #fff, 0 -2px 5px #F0E1FF inset; 
-ms-box-shadow: 0 1px 0 #fff, 0 -2px 5px #F0E1FF inset; 
-o-box-shadow: 0 1px 0 #fff, 0 -2px 5px #F0E1FF inset; 
box-shadow: 0 1px 0 #FFF, 0 -2px 5px #F0E1FF inset; 
-webkit-transition: all 0.5s ease; 
-moz-transition: all 0.5s ease; 
-ms-transition: all 0.5s ease; 
-o-transition: all 0.5s ease; 
transition: all 0.5s ease; 
background: #E6E6E6 ; 
border: 1px solid #C488FF; 
color: #000; 
font: 13px Helvetica, Arial, sans-serif;
margin: 0 0 10px; 
padding: 10px 10px 10px 10px; 
width:80%; 
} 
 
.inputs:focus { 
-webkit-box-shadow: 0 0 2px #F0E1FF inset; 
-moz-box-shadow: 0 0 2px #F0E1FF inset; 
-ms-box-shadow: 0 0 2px #F0E1FF inset; 
-o-box-shadow: 0 0 2px #F0E1FF inset; 
box-shadow: 0 0 2px #F0E1FF inset; 
background-color: #FFF; 
border: 1px solid #C488FF; 
outline: none; 
} 
.contBtn {
	-moz-box-shadow:inset 0px 0px 0px 0px #ffffff;
	-webkit-box-shadow:inset 0px 0px 0px 0px #ffffff;
	box-shadow:inset 0px 0px 0px 0px #ffffff;
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #999), color-stop(1, #999));
	background:-moz-linear-gradient(top, #999 5%, #999 100%);
	background:-webkit-linear-gradient(top, #999 5%, #999 100%);
	background:-o-linear-gradient(top, #999 5%, #999 100%);
	background:-ms-linear-gradient(top, #999 5%, #999 100%);
	background:linear-gradient(to bottom, #999 5%, #999 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf',GradientType=0);
	background-color:#999;
	-moz-border-radius:4px;
	-webkit-border-radius:4px;
	border-radius:4px;
	border:1px solid #dcdcdc;
	display:inline-block;
	cursor:pointer;
	color:#333;
	font-family:arial;
	font-size:16px;
	font-weight:bold;
	padding:8px 14px;
	text-decoration:none;
	/*text-shadow:0px 1px 0px #ffffff;*/
}
.contBtn:hover {
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #DF0000), color-stop(1, #B30000));
	background:-moz-linear-gradient(top, #DF0000 5%, #B30000 100%);
	background:-webkit-linear-gradient(top, #DF0000 5%, #B30000 100%);
	background:-o-linear-gradient(top, #DF0000 5%, #B30000 100%);
	background:-ms-linear-gradient(top, #DF0000 5%, #B30000 100%);
	background:linear-gradient(to bottom, #DF0000 5%, #B30000 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#B30000',GradientType=0);
	background-color:#DF0000;
	color:#FFF;
	font-weight:bold;
}
.contBtn:active {
	position:relative;
	top:1px;
}

</style> 
<script type="text/javascript">
function validContactForm2()
{
    var cu_fname = document.getElementById('cu_fname');
    var cu_lname = document.getElementById('cu_lname');
	var cu_email = document.getElementById('cu_email');	
	var cu_contactnumber = document.getElementById('cu_contactnumber');	
	var cu_comments = document.getElementById('cu_comments');	
	var cu_country = document.getElementById('cu_country');	
	var cu_state = document.getElementById('cu_state');	
	
	var stripped = cu_contactnumber.value.search(/^([0-9_\ \-\/\(\)\.\+]{10,18})$/); 
	
	var at = "@";
	var dot = ".";
	var lat = cu_email.value.indexOf(at);
	var lstr = cu_email.value.length;
	var ldot = cu_email.value.indexOf(dot);
	
	var msgContact="";
	var valid=true;
	
	if (cu_fname.value == "" || cu_fname.value == null)
    {
		msgContact='Please enter your first name';
		cu_fname.value="";
        cu_fname.focus();
        valid = false;		
    }
	else if (!isNaN(cu_fname.value))
    {
		msgContact='Please enter your valid first name';
		cu_fname.value="";
        cu_fname.focus();
        valid = false;		
    }
	else if (cu_lname.value == "" || cu_lname.value == null)
    {
		msgContact='Please enter your last name';
		cu_lname.value="";
        cu_lname.focus();
        valid = false;		
    }
	else if (!isNaN(cu_lname.value))
    {
		msgContact='Please enter your valid last name';
		cu_lname.value="";
        cu_lname.focus();
        valid = false;		
    }
	else if (cu_email.value == "" || cu_email.value == null)
    {
		msgContact="Please enter your email address";
		cu_email.value="";
        cu_email.focus();
        valid = false;
    }  	
	// check if '@' is at the first position or at last position or absent in given cu_email 
	else if (cu_email.value.indexOf(at) == -1 || cu_email.value.indexOf(at) == 0 || cu_email.value.indexOf(at) == lstr)
	{	
		msgContact="Please enter valid email address";
		cu_email.value="";
        cu_email.focus();
        valid = false;	
			
	}
	// check if '.' is at the first position or at last position or absent in given cu_email
	else if (cu_email.value.indexOf(dot) == -1 || cu_email.value.indexOf(dot) == 0 || cu_email.value.indexOf(dot) == lstr)
	{
	    msgContact="Please enter valid email address";
		cu_email.value="";
        cu_email.focus();
        valid = false;
		
	}
    // check if '@' is used more than one times in given cu_email
	else if (cu_email.value.indexOf(at,(lat+1)) != -1)
	{
	    msgContact="Please enter valid email address";
		cu_email.value="";
        cu_email.focus();
        valid = false;	
	}  
    // check for the position of '.'
	else if (cu_email.value.substring(lat-1,lat) == dot || cu_email.value.substring(lat+1,lat+2) == dot)
	{
	    msgContact="Please enter valid email address";
		cu_email.value="";
        cu_email.focus();
    	valid = false;	
	}
    // check if '.' is present after two characters from location of '@'
	else if (cu_email.value.indexOf(dot,(lat+2)) == -1)
	{
	    msgContact="Please enter valid email address";
		cu_email.value="";
        cu_email.focus();
    	valid = false;	
	}	
	// check for blank spaces in given cu_email
	else if (cu_email.value.indexOf(" ") != -1)
	{	
		msgContact="Please enter valid email address";
		cu_email.value="";
      	cu_email.focus();
       	valid = false;	
	}
	else if (cu_contactnumber.value == "" || cu_contactnumber.value == null)
    {
		msgContact='Please enter your contact number';
		cu_contactnumber.value="";
        cu_contactnumber.focus();
        valid = false;		
    }  
	else if (stripped == -1)//isNaN(parseInt(stripped))) 
	{	
        msgContact = "Please enter correct contact number";
        cu_contactnumber.value="";
        cu_contactnumber.focus();
        valid = false;
    }
    else if (cu_country.value == "" || cu_country.value == null)
    {
		msgContact="Please enter country";
		cu_country.value="";
                cu_country.focus();
                valid = false;
    }
	else if (cu_state.value == "" || cu_state.value == null)
    {
		msgContact="Please enter state";
		cu_state.value="";
                cu_state.focus();
                valid = false;
    }
	else if (cu_comments.value == "" || cu_comments.value == null)
    {
		msgContact="Please enter comments";
		cu_comments.value="";
        cu_comments.focus();
        valid = false;
    }
	else
	{
		valid=true;
	}
	
	
	if(!valid)
	{
		document.getElementById("msg").style.color = "red";
		document.getElementById('msg').innerHTML = msgContact;	
	}
	
    return valid;
}
</script>
</head>
<body>

<div style="left: 1180px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 1042px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 904px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 749px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 611px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 473px; top: 330px;" class="ddshadow toplevelshadow"><div style="left: 170px; top: 125px;" class="ddshadow"></div></div>
<div id="main_container">


<div class="hm1 bbc">
<!-- Header start Here::-->

<?php include 'includes/header_new.php';?>

<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>
<!-- Header End Here::-->

</div>
<div class="clr"></div>

  <div id="middle_container">
      <div id="banner-contact-us">
       <img src="images/banner-contact-us.jpg" width="100%" />
        <!--navigation start-->
      <span class="left-cor"></span>
        <span class="right-cor"></span>
      
<?php include 'includes/contact_head_menu.php';?>
</div>
<!--navigation close-->
<div class="clr"></div>
<div id="content_area">
	<?php include 'includes/contact_left_menu.php';?>
    
    	<?php 
		 
		 $latitude = '';
		 $longitude = '';
		 $iframe_width = '479px';
		 $iframe_height = '296px';//'296px';
         $address = get_page_settings(20);
		 $address = urlencode($address);
		 $geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
 
       	 $output= json_decode($geocode);
 
      	 $latitude = $output->results[0]->geometry->location->lat;
       	 $longitude = $output->results[0]->geometry->location->lng;
        
		 ?>
  
    
    
    <div class="right-side">
   
   
   
  <div style=" float:right; border:1px solid #999;-webkit-box-shadow: 8px -4px 10px 0px rgba(50, 50, 50, 0.42);-moz-box-shadow:    8px -4px 10px 0px rgba(50, 50, 50, 0.42);box-shadow:8px -4px 10px 0px rgba(50, 50, 50, 0.42);">
    
    <iframe width="<?php echo $iframe_width; ?>" height="<?php echo $iframe_height; ?>" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.co.in/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=<?php echo $address; ?>&amp;aq=&amp;sll=<?php echo $latitude; ?>,<?php echo $longitude; ?>&amp;ie=UTF8&amp;hq=&amp;hnear=<?php echo $address; ?>&amp;ll=<?php echo $latitude; ?>,<?php echo $longitude; ?>&amp;t=m&amp;z=13&amp;output=embed"></iframe>
    
    </div>
<div style=" float:left; border:1px solid #999;-webkit-box-shadow: 8px -4px 10px 0px rgba(50, 50, 50, 0.42);-moz-box-shadow:    8px -4px 10px 0px rgba(50, 50, 50, 0.42);box-shadow:8px -4px 10px 0px rgba(50, 50, 50, 0.42);">

</div>
<div id="contact-form" style="width:38%">
<form action="" method="post" onsubmit="return validContactForm2();">
	<div id="msg" style="width:28%;"><?php echo $msg; ?></div>
		<input class="inputs" placeholder="First Name" name="cu_fname" id="cu_fname" type="text" value="<?php echo $cu_fname; ?>"/>
        <input class="inputs" placeholder="Last Name" name="cu_lname" id="cu_lname" type="text" value="<?php echo $cu_lname; ?>"/>
		<input class="inputs" placeholder="Email Address" name="cu_email" id="cu_email" type="text" value="<?php echo $cu_email; ?>"/>
		<input class="inputs" placeholder="Contact Number" name="cu_contactnumber" id="cu_contactnumber" type="text" value="<?php echo $cu_contactnumber; ?>"/>
         <input class="inputs" placeholder="Country" name="cu_country" id="cu_country" type="text" value="<?php echo $cu_country; ?>"/>
		 <input class="inputs" placeholder="State" name="cu_state" id="cu_state" type="text" value="<?php echo $cu_state; ?>"/>
		<textarea class="inputs" placeholder="Comments" id="cu_comments" name="cu_comments"><?php echo $cu_comments; ?></textarea>
        <input type="submit" class="contBtn" value="Submit" id="contactSubmit" name="contactSubmit"/></form>















</div>

 <div style="margin-top:1%">
<strong>Head/ Work Office</strong><br>
<div class="right" align="center"></div>
<?php echo get_page_settings(16);?>
<br>
Mobile & WhatsApp: <?php echo get_page_settings(17);?><br>
Phone: <?php echo get_page_settings(18);?><br>

<br>

<strong>For Leader Suppliers FREE Banner Ads & FREE Events News,<br />
 Please Contact Us Here or emailto : <?php echo get_adminemail();?>.</strong>
</div>

        
          </div>
</div>


        
  </div>
     
     
</div>


<!--footer start-->
<!--media4trade add starts-->    




<!--footer close-->

<!--footer close1-->
<!--footer new-->
<?php include 'includes/footer.php';?>