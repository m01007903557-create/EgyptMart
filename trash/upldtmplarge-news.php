<?php 
include "common.php";
$uid=$_POST['uid']; 
$targetFolder = 'upload/mynews/large'; // Relative to the root

if (!empty($_FILES)) {
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	$targetPath = $targetFolder;
	$fileTypes = array('jpg', 'jpeg', 'gif', 'png'); // Allowed file extensions
	// Validate the filetype
	$fileParts = pathinfo($_FILES['Filedata']['name']);

	if (in_array(strtolower($fileParts['extension']), $fileTypes)) 
	{	
	$sqlk="SELECT * FROM temp_newsimage WHERE tmpns_uid ='".$uid."' and tmpns_status ='2'";
    $resk=mysqli_query($con, $sqlk);
	if(mysqli_num_rows($resk)<1)
	  {
        $vid=$uid.''.date("YmdHis");
        $imagename=''.$vid.'.'.$fileParts['extension'].'';
		
		$imgSImage = new SimpleImage();			
	    $imgSImage->load($tempFile);		
		$pimage=$imagename;		
		$imgSImage->resize(570,550);
		$imgSImage->save("upload/mynews/large/".$pimage);
		
	  $sql="insert into temp_newsimage set		
			tmpns_uid ='".$uid."',
			tmpns_image ='".$pimage."',
			tmpns_status ='2' ";
		mysqli_query($con, $sql) or die(mysql_error());
	  }
	} 
	else 
	{
		echo 'Invalid file type.';
	}

} else {

	echo 'No file!';

}
?>