<?php
	include_once "linkconfig.php";
	include_once "linkedin.php";
   
    
    # First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
    $linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url'] );
    //$linkedin->debug = true;

   if (isset($_REQUEST['oauth_verifier'])){
        $_SESSION['oauth_verifier']     = $_REQUEST['oauth_verifier'];

        $linkedin->request_token    =   unserialize($_SESSION['requestToken']);
        $linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
        $linkedin->getAccessToken($_REQUEST['oauth_verifier']);

        $_SESSION['oauth_access_token'] = serialize($linkedin->access_token);
        header("Location: " . $config['callback_url']);
        //exit;
   }
   else{
        $linkedin->request_token    =   unserialize($_SESSION['requestToken']);
        $linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
        $linkedin->access_token     =   unserialize($_SESSION['oauth_access_token']);
   }


    # You now have a $linkedin->access_token and can make calls on behalf of the current member  
	
	 $id = $linkedin->getProfile("~:(id)");
    $fname = $linkedin->getProfile("~:(first-name)");
    $lname = $linkedin->getProfile("~:(last-name)");
    $email = $linkedin->getProfile("~:(email-address)");


	$id=trim(strip_tags($id));
	$fname=trim(strip_tags($fname));
	$lname=trim(strip_tags($lname));
	$email=trim(strip_tags($email));

include '../linkedin_sql_session.php';
?>
