  <?php
	include '../../common.php';
include '../function.php';
  	$config['base_url'] = 'http://'.$_SERVER['SERVER_NAME'].social_login_settings(23);
    $config['callback_url'] = social_login_settings(22);
    $config['linkedin_access'] = social_login_settings(20);
    $config['linkedin_secret'] = social_login_settings(21);
	
	
	?>