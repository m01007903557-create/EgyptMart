<?php
ob_start();
session_start();
include "../common.php";

$auc_id=$_POST['auc_id'];
$auc_pc_id=addslashes(trim($_POST['auc_pc_id']));
$auc_heading=addslashes(trim($_POST['auc_heading']));
$auc_value = trim($_POST['auc_value']);
$auc_currency = $_POST['auc_currency'];

$auc_notice_type = $_POST['auc_notice_type'];
$auc_qty = $_POST['auc_qty'];
$auc_qty_mu_id = $_POST['auc_qty_mu_id'];
$auc_emd = $_POST['auc_emd'];
$auc_document_fees = $_POST['auc_document_fees'];
$auc_document_fees_currency = $_POST['auc_document_fees_currency'];
$auc_project_period = $_POST['auc_project_period'];
$auc_products = $_POST['auc_products'];

$auc_publish_date = $_POST['auc_publish_date'];
$auc_docSaleStart_date = $_POST['auc_docSaleStart_date'];
$auc_docSaleEnd_date = $_POST['auc_docSaleEnd_date'];
$auc_docSubmitBefore_date = $_POST['auc_docSubmitBefore_date'];
$auc_due_date = $_POST['auc_due_date'];


$auc_prequalification_criteria = $_POST['auc_prequalification_criteria'];
$auc_details = $_POST['auc_details'];
$auc_preferred_location=$_POST['auc_preferred_location'];

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

if($auc_pc_id=="")
{
	$data[0]="0";
	$data[1]='Kindly select Sub-Category.';
	$valid=false;
}
else if($auc_heading=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Tender Heading.';
	$valid=false;
}
else if($auc_heading!="" && checkBadWord(strtoupper($auc_heading))==false)
{
	$data[0]="0";
	$data[1]="You can't post this Tender Heading. It contains some Bad words.";
	$valid=false;
}
else if($auc_notice_type=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Notice Type.ajax';
	$valid=false;
}
else if($auc_document_fees=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Document Fees.';
	$valid=false;
}
else if($auc_document_fees_currency=="")
{
	$data[0]="0";
	$data[1]='Kindly select currency for Document Fees.';
	$valid=false;
}
else if($auc_prequalification_criteria == "")
{
	$data[0]="0";
	$data[1]='Kindly describe Pre-qualification Criteria.';
	$valid=false;
}
else if($auc_prequalification_criteria!="" && checkBadWord(strtoupper($auc_prequalification_criteria))==false)
{
	$data[0]="0";
	$data[1]="You can't post this Pre-qualification Criteria. It contains some Bad words.";
	$valid=false;
}
else if($auc_details == "")
{
	$data[0]="0";
	$data[1]='Kindly describe Tender details.';
	$valid=false;
}
else
{
	$publish_date = str_replace('/', '-', $auc_publish_date);
	$docSaleStart_date = str_replace('/', '-', $auc_docSaleStart_date);
	$docSaleEnd_date = str_replace('/', '-', $auc_docSaleEnd_date);
	$docSubmitBefore_date = str_replace('/', '-', $auc_docSubmitBefore_date);
	$due_date = str_replace('/', '-', $auc_due_date);
	
	$sql="update auction
			set
				auc_pc_id='".$auc_pc_id."',
				auc_heading ='".$auc_heading."',
				auc_value ='".$auc_value."',
				auc_currency ='".$auc_currency."',
				auc_notice_type = '".$auc_notice_type."',
				auc_qty = '".$auc_qty."',
				auc_qty_mu_id = '".$auc_qty_mu_id."',
				auc_emd = '".$auc_emd."',
				auc_document_fees = '".$auc_document_fees."',
				auc_document_fees_currency = '".$auc_document_fees_currency."',
				auc_project_period = '".$auc_project_period."',
				auc_products = '".$auc_products."',
				auc_prequalification_criteria ='".$auc_prequalification_criteria."',
				auc_details ='".$auc_details."',
				auc_preferred_location ='".$auc_preferred_location."',
				auc_publish_date = '".date('Y-m-d', strtotime($publish_date))."',
				auc_docSaleStart_date = '".date('Y-m-d', strtotime($docSaleStart_date))."',
				auc_docSaleEnd_date = '".date('Y-m-d', strtotime($docSaleEnd_date))."',
				auc_docSubmitBefore_date = '".date('Y-m-d', strtotime($docSubmitBefore_date))."',
				auc_due_date = '".date('Y-m-d', strtotime($due_date))."',
				auc_updated_date=now()
			where
				auc_id='".$auc_id."'";
		
	mysqli_query($con, $sql);
	
	$data[0]="1";
	$data[1]='Tender updated successfully.';
	include "../auction-email.php";
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