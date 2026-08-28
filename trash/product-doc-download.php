<?php
include "common.php";
$uid=$_SESSION['uid_indm'];
$pid=$_GET['pid']; 

$sql="SELECT * FROM products WHERE pd_id ='".$pid."' "; 
$recObj=mysqli_query($con, $sql) or die(mysql_error());
$timage_num=mysqli_num_rows($recObj);
$rowk=mysqli_fetch_object($recObj);
$file = "upload/productdoc/".$rowk->pd_pdf_attach;

if (file_exists($file)) 
{
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);	
}
?>