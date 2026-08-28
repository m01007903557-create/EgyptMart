<?php
session_start();
include "common.php";
unset($_SESSION['eml_indm']);
unset($_SESSION['uid_indm']);
unset($_SESSION['pass']);

/*########  Google Logout  #########*/
unset($_SESSION['token']);
unset($_SESSION['popup']);
unset($_SESSION['last_page']);
//webcast
for($i=0;$i<20;$i++){
unset($_SESSION['id'.$i.'']);
}
header("location:index.php");
?>
