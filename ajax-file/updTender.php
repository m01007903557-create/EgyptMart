<?php
ob_start();
session_start();
include "../common.php";

$tnd_id=$_POST['tnd_id'];
$tender_id = $tnd_id;
$tnd_pc_id=addslashes(trim($_POST['tnd_pc_id']));
$tnd_heading=addslashes(trim($_POST['tnd_heading']));
$tnd_value = trim($_POST['tnd_value']);
$tnd_currency = $_POST['tnd_currency'];

$tnd_notice_type = $_POST['tnd_notice_type'];
$tnd_qty = $_POST['tnd_qty'];
$tnd_qty_mu_id = $_POST['tnd_qty_mu_id'];
$tnd_emd = $_POST['tnd_emd'];
$tnd_document_fees = $_POST['tnd_document_fees'];
$tnd_document_fees_currency = $_POST['tnd_document_fees_currency'];
$tnd_project_period = $_POST['tnd_project_period'];
$tnd_products = $_POST['tnd_products'];

$tnd_publish_date = $_POST['tnd_publish_date'];
$tnd_docSaleStart_date = $_POST['tnd_docSaleStart_date'];
$tnd_docSaleEnd_date = $_POST['tnd_docSaleEnd_date'];
$tnd_docSubmitBefore_date = $_POST['tnd_docSubmitBefore_date'];
$tnd_due_date = $_POST['tnd_due_date'];


$tnd_prequalification_criteria = $_POST['tnd_prequalification_criteria'];
$tnd_details = $_POST['tnd_details'];
$tnd_preferred_location=$_POST['tnd_preferred_location'];

$valid=true;
$data=array();

$sqlrpl = "select bd_word from bad_word";
$resrpl = mysqli_query($con, $sqlrpl);
while($rowrpl = mysqli_fetch_object($resrpl))
{		
	$letters1[] = strtoupper($rowrpl->bd_word);
	$letters2[] = strtoupper($rowrpl->bd_word);
}

$data=array();

if($tnd_pc_id=="")
{
	$data[0]="0";
	$data[1]='Kindly select Sub-Category.';
	$valid=false;
}
else if($tnd_heading=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Tender Heading.';
	$valid=false;
}
else if($tnd_heading!="" && checkBadWord(strtoupper($tnd_heading))==false)
{
	$data[0]="0";
	$data[1]="You can't post this Tender Heading. It contains some Bad words.";
	$valid=false;
}
else if($tnd_notice_type=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Notice Type.ajax';
	$valid=false;
}
else if($tnd_document_fees=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Document Fees.';
	$valid=false;
}
else if($tnd_document_fees_currency=="")
{
	$data[0]="0";
	$data[1]='Kindly select currency for Document Fees.';
	$valid=false;
}
else if($tnd_prequalification_criteria == "")
{
	$data[0]="0";
	$data[1]='Kindly describe Pre-qualification Criteria.';
	$valid=false;
}
else if($tnd_prequalification_criteria!="" && checkBadWord(strtoupper($tnd_prequalification_criteria))==false)
{
	$data[0]="0";
	$data[1]="You can't post this Pre-qualification Criteria. It contains some Bad words.";
	$valid=false;
}
else if($tnd_details == "")
{
	$data[0]="0";
	$data[1]='Kindly describe Tender details.';
	$valid=false;
}
else
{
	$publish_date = str_replace('/', '-', $tnd_publish_date);
	$docSaleStart_date = str_replace('/', '-', $tnd_docSaleStart_date);
	$docSaleEnd_date = str_replace('/', '-', $tnd_docSaleEnd_date);
	$docSubmitBefore_date = str_replace('/', '-', $tnd_docSubmitBefore_date);
	$due_date = str_replace('/', '-', $tnd_due_date);
	
	$sql="update tender
			set
				tnd_pc_id='".$tnd_pc_id."',
				tnd_heading ='".$tnd_heading."',
				tnd_value ='".$tnd_value."',
				tnd_currency ='".$tnd_currency."',
				tnd_notice_type = '".$tnd_notice_type."',
				tnd_qty = '".$tnd_qty."',
				tnd_qty_mu_id = '".$tnd_qty_mu_id."',
				tnd_emd = '".$tnd_emd."',
				tnd_document_fees = '".$tnd_document_fees."',
				tnd_document_fees_currency = '".$tnd_document_fees_currency."',
				tnd_project_period = '".$tnd_project_period."',
				tnd_products = '".$tnd_products."',
				tnd_prequalification_criteria ='".$tnd_prequalification_criteria."',
				tnd_details ='".$tnd_details."',
				tnd_preferred_location ='".$tnd_preferred_location."',
				tnd_publish_date = '".date('Y-m-d', strtotime($publish_date))."',
				tnd_docSaleStart_date = '".date('Y-m-d', strtotime($docSaleStart_date))."',
				tnd_docSaleEnd_date = '".date('Y-m-d', strtotime($docSaleEnd_date))."',
				tnd_docSubmitBefore_date = '".date('Y-m-d', strtotime($docSubmitBefore_date))."',
				tnd_due_date = '".date('Y-m-d', strtotime($due_date))."',
				tnd_updated_date=now()
			where
				tnd_id='".$tnd_id."'";
		
	mysqli_query($con, $sql);
	
	$data[0]="1";
	$data[1]='Tender updated successfully.';
	include "../tender-email.php";
}
echo $data[0]."|".$data[1];

function checkBadWord($param)
{
	$valid=true;
	$sqlrpl = "select bd_word from bad_word";
	$resrpl = mysqli_query($con, $sqlrpl);
	while($rowrpl = mysqli_fetch_object($resrpl))
	{		
		$letters[] = strtoupper($rowrpl->bd_word);
	}
	foreach($letters as $val)
	{
		$pos = strpos($param, $val);
		if ($pos !== false)
		{
			$valid=false;
		} 
	}
	return $valid;
}
?>