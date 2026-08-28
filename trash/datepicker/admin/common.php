<?php

/*if($_SERVER['SERVER_NAME'] == 'phpserver')
	define(SRNAME, 'http://'.$_SERVER['SERVER_NAME'].'/cupcake/');
else if($_SERVER['SERVER_NAME'] == '64.191.66.18')
	define(SRNAME, 'http://'.$_SERVER['SERVER_NAME'].'/cupcake/');
else
	define(SRNAME, 'http://'.$_SERVER['SERVER_NAME'].'/');*/
	
//error_reporting(E_All);
include 'lib/connect.php';
//include 'lib/name-var.php';
include 'lib/function.php';
include 'lib/validation.php';
include 'lib/pagination.php';
//include 'common-js.php';
//include "includes/common-menu.php";

$c_form_scode = $_SESSION['security_code_contact_form'];

?>

