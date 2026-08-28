    <?php               
      
      ########## Google Settings.. Client ID, Client Secret #############

include 'sociallogin/function.php';

$google_client_id 		= social_login_settings(2);
$google_client_secret 	= social_login_settings(3);
$google_redirect_url 	= social_login_settings(4);
$google_developer_key 	= social_login_settings(5);
//echo $_GET['code'];
require_once 'sociallogin/google_login_api/Google_Client.php';
require_once 'sociallogin/google_login_api/contrib/Google_Oauth2Service.php';


$gClient = new Google_Client();
$gClient->setApplicationName('Demonstrationfb');
$gClient->setClientId($google_client_id);
$gClient->setClientSecret($google_client_secret);
$gClient->setRedirectUri($google_redirect_url);
$gClient->setDeveloperKey($google_developer_key);

$google_oauthV2 = new Google_Oauth2Service($gClient);

//If user wish to log out, we just unset Session variable
if (isset($_REQUEST['reset'])) 
{
  unset($_SESSION['token']);
  $gClient->revokeToken();
  header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
}

//Redirect user to google authentication page for code, if code is empty.
//Code is required to aquire Access Token from google
//Once we have access token, assign token to session variable
//and we can redirect user back to page and login.
if (isset($_GET['code'])) 
{ 
	$gClient->authenticate($_GET['code']);
	$_SESSION['token'] = $gClient->getAccessToken();
	header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
	return;
}


if (isset($_SESSION['token'])) 
{ 
		$gClient->setAccessToken($_SESSION['token']);
}

if ($gClient->getAccessToken()) 
{
	  //Get user details if user is logged in
	  $user 				= $google_oauthV2->userinfo->get();
	  $user_id 				= $user['id'];
	  $user_name 			= filter_var($user['name'], FILTER_SANITIZE_SPECIAL_CHARS);
	  $google_email 		= filter_var($user['email'], FILTER_SANITIZE_EMAIL);
	  $profile_url 			= filter_var($user['link'], FILTER_VALIDATE_URL);
	  $profile_image_url 	= filter_var($user['picture'], FILTER_VALIDATE_URL);
	  $personMarkup 		= "$email<div><img src='$profile_image_url?sz=50'></div>";
	 // $_SESSION['token'] 	= $gClient->getAccessToken();exit();
}
else 
{
	//get google login url
	$authUrl = $gClient->createAuthUrl();
}
?>

<?php
if(isset($authUrl)) //user is not logged in, show login button
{
	?>
	<a href="<?php echo $authUrl;?>">
<img src="social_media_images/google_signin_small.png" />
     </a>
<?php
}
else
{
	include 'sociallogin/google_sql_session.php';
		
}
	?>     
     
     
     
     
     
     