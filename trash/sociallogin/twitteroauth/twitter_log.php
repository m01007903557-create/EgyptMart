<?php
include '../../common.php';
include '../function.php';
require_once('twitteroauth.php');

$CONSUMER_KEY = social_login_settings(16);
$CONSUMER_SECRET= social_login_settings(17);
$OAUTH_CALLBACK= social_login_settings(18);

	$connection = new TwitterOAuth($CONSUMER_KEY, $CONSUMER_SECRET);
	$request_token = $connection->getRequestToken($OAUTH_CALLBACK); //get Request Token

	if(	$request_token)
	{
		$token = $request_token['oauth_token'];
		$_SESSION['request_token'] = $token ;
		$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];
		
		switch ($connection->http_code) 
		{
			case 200:
				$url = $connection->getAuthorizeURL($token);
				//redirect to Twitter .
		    	header('Location: ' . $url); 
			    break;
			default:
			    echo "Coonection with twitter Failed";
		    	break;
		}

	}
	else //error receiving request token
	{
		echo "Error Receiving Request Token";
	}

?>