<?php
ob_start();
include "common.php";

setcookie("loc_id", ''/*$cn_id*/, 1/*time()-60*/, '/'); //added path fixes /ajax-files/ directory deletion fix - by webxtor
setcookie("loc_id", ''/*$cn_id*/, 1/*time()-60*/, '/ajax-file');
setcookie("is_global",1, time()+3600, '/'); 
?>
