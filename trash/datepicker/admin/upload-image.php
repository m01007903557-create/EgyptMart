<?php 
include "../common.php";

$pid=$_POST['pid']; 
$targetFolder = '../upload/feature'; // Relative to the root

if (!empty($_FILES)) {
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	//$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	//$targetFile = $uploadDir . $_FILES['Filedata']['name'];
	$targetPath = $targetFolder;
	// Set the allowed file extensions
	
	/**/$fileTypes = array('jpg', 'jpeg', 'gif', 'png'); // Allowed file extensions

	// Validate the filetype
	$fileParts = pathinfo($_FILES['Filedata']['name']);

	if (in_array(strtolower($fileParts['extension']), $fileTypes)) {/**/
        $vid=$pid.''.date("YmdHis");
        $imagename=''.$vid.'.'.$fileParts['extension'].'';
	    $targetFile = rtrim($targetPath,'/') . '/' . $vid.'.'.$fileParts['extension'];
		move_uploaded_file($tempFile,$targetFile);
		
	 $sql="insert into feature_images
			set				
				fi_f_id =".$pid.",
				fi_image ='".$imagename."',
				fi_updated_date = now()";
		mysqli_query($con, $sql) or die(mysql_error());

	} else {

		// The file type wasn't allowed
		echo 'Invalid file type.';

	}
/**/
} else {

	echo 'No file!';

}
?>