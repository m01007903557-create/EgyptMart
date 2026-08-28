<?php
include '../../lib/connect.php';

function createRandomPassword($t)
{
	if($t==1)
	{
    	$chars = "abcdefghijkmnopqrstuvwxyz023456789";
	}
	if($t==2)
	{
    	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ023456789";
	}
	if($t==3)
	{
   	 $chars = "abcdefghijkmnopqrstuvwxyz023456789";
	}
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;
 	while ($i <= 7) 
	{
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}
class User {

    function checkUser($uid, $oauth_provider, $username, $email) 
	{
        include '../fb_sql.php';
        return $result;
    }

}
?>