<?php
ob_start();
session_start();
include "../common.php";

$br_id=$_POST['br_id'];
$new_br_id = $br_id;
$br_pc_id=addslashes(trim($_POST['br_pc_id']));
$br_pd_name=addslashes(trim($_POST['br_pd_name']));
$br_requirement=addslashes(trim($_POST['br_requirement']));
$br_estimate_qty=addslashes(trim($_POST['br_estimate_qty']));
$br_estimate_qty_unit=addslashes(trim($_POST['br_estimate_qty_unit']));
$br_preferred_supplier_location=addslashes(trim($_POST['br_preferred_supplier_location']));

$br_apprx_order_value=addslashes(trim($_POST['br_apprx_order_value']));
$br_apprx_order_currency=addslashes(trim($_POST['br_apprx_order_currency']));
$br_description=addslashes(trim($_POST['br_description']));
$br_website=addslashes(trim($_POST['br_website']));
$br_need_quote_for=addslashes(trim($_POST['br_need_quote_for']));
$br_purchase_time=addslashes(trim($_POST['br_purchase_time']));
$br_need_for=addslashes(trim($_POST['br_need_for']));
$br_requirement_frequency=addslashes(trim($_POST['br_requirement_frequency']));

$valid=true;
$data=array();

$sqlrpl = "select bd_word from bad_word";
$resrpl = mysqli_query($con, $sqlrpl);
while($rowrpl = mysqli_fetch_object($resrpl))
{		
	$letters1[] = strtoupper($rowrpl->bd_word);
	$letters2[] = strtoupper($rowrpl->bd_word);
}
$br_name=strtoupper($br_pd_name);
$requirement=strtoupper($br_requirement);


if($br_pd_name != "" && $valid==true)
{
	$val="";
	$pos="";
	foreach($letters1 as $val)
	{
		$pos = strpos($br_name, $val);
		if ($pos !== false)
		{
			$data[0]="0";
			$data[1]="You can't post words like '".$val."' in Products / Services Name.";
			$valid=false;
		}
	}
}
if($br_requirement != "" && $valid==true)
{
	$val="";
	$pos="";
	foreach($letters2 as $val)
	{
		$pos = strpos($requirement, $val);
		if ($pos !== false)
		{
			$data[0]="0";
			$data[1]="You can't post words like '".$val."' in Buying Requirements in detail.";
			$valid=false;
		}
	}
}

if($valid==true)
{
	$sql="update buy_requirement
			set
				br_pc_id='".$br_pc_id."',
				br_pd_name='".$br_pd_name."',
				br_requirement='".$br_requirement."',
				br_estimate_qty='".$br_estimate_qty."',
				br_estimate_qty_unit='".$br_estimate_qty_unit."',
				br_preferred_supplier_location='".$br_preferred_supplier_location."',
				br_apprx_order_value='".$br_apprx_order_value."',
				br_apprx_order_currency='".$br_apprx_order_currency."',
				br_description='".$br_description."',
				br_website='".$br_website."',
				br_need_quote_for='".$br_need_quote_for."',
				br_purchase_time='".$br_purchase_time."',

				br_need_for='".$br_need_for."',
				br_requirement_frequency='".$br_requirement_frequency."',
				br_updated_date=now()
			where
				br_id='".$br_id."'";
	mysqli_query($con, $sql);
	
	$data[0]="1";
	$data[1]='Buy Requirement updated successfully.';
	require_once '../post-buy-req-email.php';
}
echo $data[0]."|".$data[1];
?>