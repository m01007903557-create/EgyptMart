<?php
include "common.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="stylesheet" type="text/css" href="css/testimonial.css" />

</head>

<body>
    <div id="wrapper">
    	<div class="top-part">
        <div class="logo-part"><a href="index.php"><img src="sitelogo/<?php echo getSiteLogo(); ?>" alt="<?php echo getWebSiteName(); ?>" width="227" height="68" border="0"  /></a><a href="index.php"><img src="images/easybuying-logo.gif" alt="Easy Buying" width="423" height="68" border="0" /></a></div>
        <div class="clearer"></div>
      </div>
        <div class="container-blue">
        <div class="hh"><a href="index.php"><span  class="icons_m hpi"></span><span  class="hht">Home </span></a></div>
       <div class="inner_container">
       <h1 class="page-heading">Testimonials</h1>
<?php
$sql_testi="select * from testimonials WHERE testi_status='1' order by testi_updated_date desc";
$res_testi=mysqli_query($con, $sql_testi);
$n=1;
while($row_testi=mysqli_fetch_object($res_testi))
{
?>
    <div class="t-part ft"> <img src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>" width="76" height="76" />&#8220;<?php echo stripslashes($row_testi->testi_details); ?>&#8221;<br />
<b class="ts"><?php echo $row_testi->testi_name; ?>,<br />
<?php echo get_country_name($row_testi->testi_cn_id); ?></b>
<div class="clearer"></div>
</div>
<?php 
	$n++;
} ?>
     

       <div class="clearer"></div>
       <br />
  </div>
  <div class="clearer"></div>    
</div>
    <div class="clearer"></div>
    <br />
        </div>
         <!-- Footer Start Here::-->
<?php include 'includes/footer.php';?>