<?php
include 'common.php';
require_once __DIR__ . '/lib/function.php';
/*$_SESSION['last_page']="conatct_us.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}*/

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
</head>
<body>

<div style="left: 1180px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 1042px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 904px; top: 330px;" class="ddshadow toplevelshadow"></div>
<div style="left: 749px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 611px; top: 330px;" class="ddshadow toplevelshadow"></div><div style="left: 473px; top: 330px;" class="ddshadow toplevelshadow"><div style="left: 170px; top: 125px;" class="ddshadow"></div></div>
<div id="main_container">


<div class="hm1 bbc">
<!-- Header start Here::-->

<?php include 'includes/header_new.php';?>

<br>
<!-- Header End Here::-->

</div>
<div class="clr"></div>

  <div id="middle_container">
      <div id="banner-contact-us">
       
        <!--navigation start-->
      <span class="left-cor"></span>
        <span class="right-cor"></span>
      
<?php include 'includes/contact_head_menu.php';?>
</div>
<!--navigation close-->
<div class="clr"></div>
<div id="content_area">
	<?php include 'includes/contact_left_menu.php';?>
    
  
    <div class="right-side2" style="80%">
    
    	<h2>Terms & <span>Conditions</span></h2> 
    	
        <p align="justify">
        <?php echo get_page_content(3,'cms_content');?>
        
         </p>
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