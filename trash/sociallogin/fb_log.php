<?php 

if (array_key_exists("login", $_GET))
{
    $oauth_provider = $_GET['oauth_provider'];
    if ($oauth_provider == 'facebook')
    {
        header("Location: sociallogin/loginwithfb/login-facebook.php");
    }
}

 
?>    
    
       <a href="?login&oauth_provider=facebook">
			<img style="padding-left: 3px" src="social_media_images/facebook_signin_small.png"/>
        </a>
