<?php 
include "common.php";
$pid=$_POST['pid']; 
$targetFolder = 'upload/productdoc'; // Relative to the root

if (!empty($_FILES)) 
{
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	//$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	//$targetFile = $uploadDir . $_FILES['Filedata']['name'];
	$targetPath = $targetFolder;
	// Set the allowed file extensions
	$fileTypes = array('pdf'); // Allowed file extensions

	// Validate the filetype
	$fileParts = pathinfo($_FILES['Filedata']['name']);

	if(in_array(strtolower($fileParts['extension']), $fileTypes)) 
	{	
	$sqlk="SELECT * FROM products WHERE pd_id ='".$pid."'";
    $resk=mysqli_query($con, $sqlk);
	$rowk=mysqli_fetch_object($resk);
	if($rowk->pd_pdf_attach=="")
	{
		$pdtitle = str_replace(" ", "-", $rowk->pd_title).''.date("YmdHis");
        $imagename=''.$pdtitle.'.'.$fileParts['extension'].'';
	    $targetFile = rtrim($targetPath,'/') . '/' . $pdtitle.'.'.$fileParts['extension'];
		move_uploaded_file($tempFile,$targetFile);
		$sql="update products set pd_pdf_attach ='".$imagename."' where pd_id ='".$pid."'";
		mysqli_query($con, $sql) or die(mysql_error());
	 }
	 echo 'You have been attached successfully .pdf file.';
   } 
   else 
   {
		echo 'Please attach .pdf files only.';
   }
} 
else 
{
	echo 'No file!';
}
?>