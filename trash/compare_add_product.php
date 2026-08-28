<?php
session_start();
if(!empty($_POST['product_id'])){
$_COOKIE['productids'][]=$_POST['product_id'];
return count($_COOKIE['productids']);
}
?>