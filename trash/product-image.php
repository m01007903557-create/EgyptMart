<?php 
include "common.php";

//check_user_login();
$pdid=$_POST['pdid']; 
$targetFolder = 'upload/myproduct'; // Relative to the root

if (!empty($_FILES)) {
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	//$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	//$targetFile = $uploadDir . $_FILES['Filedata']['name'];
	$targetPath = $targetFolder;
	// Set the allowed file extensions
	$fileTypes = array('jpg', 'jpeg', 'gif', 'png'); // Allowed file extensions

	// Validate the filetype
	$fileParts = pathinfo($_FILES['Filedata']['name']);

	if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
		
	$sqlk="SELECT * FROM products WHERE pd_id ='".$pdid."'";
    $resk=mysqli_query($con, $sqlk);
	if(mysqli_num_rows($resk)<2)
	  {
        $vid=$uid.''.date("YmdHis");
        $imagename=''.$vid.'.'.$fileParts['extension'].'';
	    $targetFile = rtrim($targetPath,'/') . '/' . $vid.'.'.$fileParts['extension'];
		move_uploaded_file($tempFile,$targetFile);
		$sql="update products set pd_image ='".$imagename."' where pd_id='".$pdid."'";	
		  mysqli_query($con, $sql) or die(mysql_error());
	  }
	} else {

		// The file type wasn't allowed
		echo 'Invalid file type.';

	}

} else {

	echo 'No file!';

}
?>