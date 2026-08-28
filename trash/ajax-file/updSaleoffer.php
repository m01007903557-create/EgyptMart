<?php
ob_start();
session_start();
include "../common.php";


$so_id=$_POST['so_id'];
$so_pc_id=addslashes(trim($_POST['so_pc_id']));
$so_service=addslashes(trim($_POST['so_service']));
$so_description=addslashes(trim($_POST['so_description']));

$so_preferred_buyer_location=addslashes(trim($_POST['so_preferred_buyer_location']));

$change_validity=addslashes(trim($_POST['change_validity']));
$so_validity=addslashes(trim($_POST['so_validity']));


$valid=true;
$data=array();

$sqlrpl = "select bd_word from bad_word";
$resrpl = mysqli_query($con, $sqlrpl);
while($rowrpl = mysqli_fetch_object($resrpl))
{		
	$letters[] = strtoupper($rowrpl->bd_word);
}
$service=strtoupper($so_service);
$description=strtoupper($so_description);


if($so_service != "" && $valid==true)
{		
	foreach($letters as $val)
	{
		$pos = strpos($service, $val);
		if ($pos !== false)
		{
			$data[0]="0";
			$data[1]="You can't post words like '".$val."' in Products / Services you want to sell.";
			$valid=false;
		}
	}
}
if($so_description != "" && $valid==true)
{
	foreach($letters as $val)
	{
		$pos = strpos($description, $val);
		if ($pos !== false)
		{
			$data[0]="0";
			$data[1]="You can't post words like '".$val."' in Products / Services in detail.";
			$valid=false;
		}
	}
}
if($valid==true)
{
	if($change_validity=='yes')
	{
		$sql="update sale_offer
			set
				so_pc_id='".$so_pc_id."',
				so_service='".$so_service."',
				so_description='".$so_description."',
				so_preferred_buyer_location='".$so_preferred_buyer_location."',
				so_validity='".$so_validity."'
			where
				so_id='".$so_id."'";
		mysqli_query($con, $sql);	
	}
	else
	{
		$sql="update sale_offer
			set
				so_pc_id='".$so_pc_id."',
				so_service='".$so_service."',
				so_description='".$so_description."',
				so_preferred_buyer_location='".$so_preferred_buyer_location."'
			where
				so_id='".$so_id."'";

		mysqli_query($con, $sql);
	}
	include "../selloffer-email.php";
	$data[0]="1";
	$data[1]='Sale Offer updated successfully.';
}
echo $data[0]."|".$data[1];
?>