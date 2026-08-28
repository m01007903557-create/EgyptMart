<?php 
include '../../common.php';
include '../function.php';
include 'facebook.php';
include 'fbfunctions.php';

$APP_ID = social_login_settings(8);
$APP_SECRET = social_login_settings(7);

define('APP_ID', $APP_ID);
define('APP_SECRET', $APP_SECRET);

$facebook = new Facebook(array(
            'appId' => APP_ID,
            'secret' => APP_SECRET
        ));

    $user1 = $facebook->getUser();

if ($user1) 
{
    # Active session, let's try getting the user id (getUser()) and user info (api->('/me')) 
try {
 
        $user = $facebook->api('/me');
    } 
    catch (FacebookApiException $e) {
        error_log($e);
        $user1 = null;

    }

    if (!empty($user)) 
    {

        $username = $user['name'];
        $email = $user['email'];
        $user = new User();
        
        $userdata = $user->checkUser($uid, 'facebook', $username, $email);
        
        if(!empty($userdata))
        {
          
            include '../fb_session.php';
        }
    } 
    else
    {

        # For testing purposes, if there was an error, let's kill the script
//        die("There was an error.");
    }
}
 else 
 {
    # There's no active session, let's generate one
   $login_url = $facebook->getLoginUrl(array(
    'scope' => 'email'
));
     header("Location: " . $login_url);
//echo "ff";
}
?>