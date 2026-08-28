<?php
session_start();
unset($_SESSION['ad_username_indm']);
unset($_SESSION['ad_email_indm']);
unset($_SESSION['ad_id_indm']);
header("location:index.php");
?>
