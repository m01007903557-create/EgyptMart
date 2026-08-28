<?php
include 'common.php';

if(isset($_GET['from'])){
	if($_GET['from'] != 2) {
		header("location:index.php");
	}
}
else {
	header("location:index.php");
}
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

<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
</head>

<body>
<header>
     
<div id="res-mob1">
       <?php include "includes/header_new.php"; ?>
        
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>
    </div>
</header>
<div id="middle">
  <div class="container">
  <div class="row">
  <div class="top-btn">
  <div class="col-sm-4">
  <div class="first-btn"><span>1</span>Register Your Business Profile </div>
  </div>
  <div class="col-sm-4">
  <div class="first-btn"><span>2</span>Select Membership Type </div>
  </div>
  <div class="col-sm-4">
  <div class="first-btn active"><span>3</span>Create Your Account on ARABYOS </div>
  </div>
  <div class="clr"></div>
  </div>
  
    <div class="clr"></div>
  
  </div>
  <div class="row">
    <dic class="col-md-8 col-md-offset-2">
              <div class="thankyou_row">  
          <div class="thankyou text-center">
             
              <div>
                  <span style="font-size: 54px;color:#1cb505;text-align:center;">Thanks !</span> <br>
                  <span style="color:#0003ff;ext-align:center;">Right now we are going to review your business details and make sure its ready for ARABYOS.</span><br>
                  <span style="color:#0003ff;ext-align:center;">Once its approved, you will get an email to create and launch your account.</span><br><br>
                  <span style="background:#1cb505;color:#fff;padding:10px;text-align:center;">Once Your Account is launched, you can list, edit and delete your products / Services.</span>
              </div>
          </div>
      </div>
    </dic>
  </div>
  </div>
  </div>
  
    
<!--footer:start-->
		<?php include 'includes/footer.php'; ?>