<?php
ob_start();
session_start();
include "common.php";
   error_reporting(1);
function checkBadWord($param)
{
	$valid=true;
	$sqlrpl = "select bd_word from bad_word";
	$resrpl = mysql_query($sqlrpl);
	while($rowrpl = mysql_fetch_object($resrpl))
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
$valid = true;
$tnd_usr_id = $_GET['tnd_usr_id'];
$main_cat = $_GET['main_cat'];
$pc_id = $_GET['pc_id'];
$tnd_pc_id = $_GET['tnd_pc_id'];
$tnd_heading = $_GET['tnd_heading'];
$tnd_value = $_GET['tnd_value'];
$tnd_currency = $_GET['tnd_currency'];


$tnd_notice_type = $_GET['tnd_notice_type'];
$tnd_qty = $_GET['tnd_qty'];
$tnd_qty_mu_id = $_GET['tnd_qty_mu_id'];
$tnd_emd = $_GET['tnd_emd'];
$tnd_document_fees = $_GET['tnd_document_fees'];
$tnd_document_fees_currency = $_GET['tnd_document_fees_currency'];
$tnd_project_period = $_GET['tnd_project_period'];
$tnd_products = $_GET['tnd_products'];

$tnd_publish_date = $_GET['tnd_publish_date'];
$tnd_docSaleStart_date = $_GET['tnd_docSaleStart_date'];
$tnd_docSaleEnd_date = $_GET['tnd_docSaleEnd_date'];
$tnd_docSubmitBefore_date = $_GET['tnd_docSubmitBefore_date'];
$tnd_due_date = $_GET['tnd_due_date'];


$tnd_prequalification_criteria = $_GET['tnd_prequalification_criteria'];
$tnd_details = $_GET['tnd_details'];
$tnd_preferred_location=$_GET['tnd_preferred_location'];

$af_id=$_GET['af_id'];
$afv_val=$_GET['afv_val'];
 $typeofselection = $_GET['typeofselection'];

 $keywordsFilter = $_GET['keywordsFilter'];
$data=array();
if(!$typeofselection){
if($main_cat=="")
{
	$data[0]="0";
	$data[1]='Kindly select Main Category.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
else if($pc_id=="")
{
	$data[0]="0";
	$data[1]='Kindly select Category.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
else if($tnd_pc_id=="")
{
	$data[0]="0";
	$data[1]='Kindly select Sub-Category.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
}
elseif($typeofselection){
 if($keywordsFilter=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Keyword.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}


 $searchedproducts = $_SESSION['searchedproducts'];

if(!$searchedproducts && !array_key_exists($keywordsFilter,$searchedproducts))  {
	$data[0]="0";
	$data[1]='No category found with given keywords';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
     $keywordsFilter =  explode(">>",$keywordsFilter)   ;

$keywordsFilter1 = end($keywordsFilter);
$tnd_pc_id = $searchedproducts[$keywordsFilter1];
$pc_id = $searchedproducts[$keywordsFilter[1]];
$main_cat = $searchedproducts[$keywordsFilter[0]];
if(!$tnd_pc_id){
 	$data[0]="0";
	$data[1]='No category found with given keywords';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}

}
else if($tnd_heading=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Tender Heading.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
else if($tnd_heading!="" && checkBadWord(strtoupper($tnd_heading))==false)
{
	$data[0]="0";
	$data[1]="You can't post this Tender Heading. It contains some Bad words.";
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
else if($tnd_notice_type=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Notice Type.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
else if($tnd_document_fees=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Document Fees.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
else if($tnd_document_fees_currency=="")
{
	$data[0]="0";
	$data[1]='Kindly select currency for Document Fees.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
else if($tnd_prequalification_criteria == "")
{
	$data[0]="0";
	$data[1]='Kindly describe Pre-qualification Criteria.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
}
else if($tnd_prequalification_criteria!="" && checkBadWord(strtoupper($tnd_prequalification_criteria))==false)
{
	$data[0]="0";
	$data[1]="You can't post this Pre-qualification Criteria. It contains some Bad words.";
	$valid=false;
    echo $data[0]."|".$data[1];
    exit;
}
else if($tnd_details == "")
{
	$data[0]="0";
	$data[1]='Kindly describe Tender details.';
	$valid=false;
     echo $data[0]."|".$data[1];
    exit;
   
}

if($valid)
{

	$sql1="insert into temp_tender_alert_cat
	set
		ttac_usr_id='".$_SESSION['uid_indm']."',
		ttac_pc_id='".$tnd_pc_id."',
		ttac_updated_date=now()";
	mysqli_query($con, $sql1);
	$sql2="select * from temp_tender_alert_cat where ttac_usr_id='".$_SESSION['uid_indm']."'";

	$res2=mysqli_query($con, $sql2);
	while($row=mysqli_fetch_object($res2)){
		$sql_exist="select * from tender_alert_category where tac_usr_id='".$_SESSION['uid_indm']."' AND tac_pc_id='".$row->ttac_pc_id."'";
		$res12=mysqli_query($con, $sql_exist);
		if($res12->num_rows==0){
			$sql_ins="insert into tender_alert_category
				set
					tac_usr_id='".$_SESSION['uid_indm']."',
					tac_pc_id='".$row->ttac_pc_id."',
					tac_updated_date=now()";

			mysqli_query($con, $sql_ins);
		}
	}
	mysqli_query($con, "delete from temp_tender_alert_cat where ttac_usr_id='".$_SESSION['uid_indm']."'");
	$sql_exist="select * from tender_alert_category where tac_usr_id='".$_SESSION['uid_indm']."' AND tac_pc_id='".$tnd_pc_id."'";
		$res12=mysqli_query($con, $sql_exist);
		if($res12->num_rows==0){
			$sql_ins="insert into tender_alert_category
				set
					tac_usr_id='".$_SESSION['uid_indm']."',
					tac_pc_id='".$row->ttac_pc_id."',
					tac_updated_date=now()";

			mysqli_query($con, $sql_ins);
		}

	$publish_date = str_replace('/', '-', $tnd_publish_date);
	$docSaleStart_date = str_replace('/', '-', $tnd_docSaleStart_date);
	$docSaleEnd_date = str_replace('/', '-', $tnd_docSaleEnd_date);
	$docSubmitBefore_date = str_replace('/', '-', $tnd_docSubmitBefore_date);
	$due_date = str_replace('/', '-', $tnd_due_date);

	$sql="insert into tender
		set
			tnd_usr_id='".$tnd_usr_id."',
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
			tnd_updated_date=now()";
	 // echo $sql;
	mysql_query($sql)or die(mysql_error());
	
	$tnd=mysql_insert_id();
		
	if($af_id!='' && $afv_val!='')	//add additional information
	{
		$tav_af_id=explode("|",$af_id);
		$tav_value=explode("|", $afv_val);
			
		for($i=0;$i<count($tav_af_id);$i++)
		{
			$val=explode("-",$tav_value[$i]);
			for($c=0;$c<count($val);$c++)
			{
				$sql_sav="insert into tender_additional_value
					set
						tav_tnd_id='".$tnd."',
						tav_af_id='".$tav_af_id[$i]."',
						tav_value='".$val[$c]."',
						tav_status='1'";
				mysql_query($sql_sav) or die(mysql_error());
			}
		}
	}
	$data[0]="1";
	$data[1]='Tender posted successfully.';
}
   echo $data[0]."|".$data[1];


?>