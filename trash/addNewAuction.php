<?php
ob_start();
session_start();
include "common.php";


$auc_usr_id = $_GET['auc_usr_id'];
$main_cat = $_GET['main_cat'];
$pc_id = $_GET['pc_id'];
$auc_pc_id = $_GET['auc_pc_id'];
$auc_heading = $_GET['auc_heading'];
$auc_value = $_GET['auc_value'];
$auc_currency = $_GET['auc_currency'];


$auc_notice_type = $_GET['auc_notice_type'];
$auc_qty = $_GET['auc_qty'];
$auc_qty_mu_id = $_GET['auc_qty_mu_id'];
$auc_emd = $_GET['auc_emd'];
$auc_document_fees = $_GET['auc_document_fees'];
$auc_document_fees_currency = $_GET['auc_document_fees_currency'];
$auc_project_period = $_GET['auc_project_period'];
$auc_products = $_GET['auc_products'];

$auc_publish_date = $_GET['auc_publish_date'];
$auc_docSaleStart_date = $_GET['auc_docSaleStart_date'];
$auc_docSaleEnd_date = $_GET['auc_docSaleEnd_date'];
$auc_docSubmitBefore_date = $_GET['auc_docSubmitBefore_date'];
$auc_due_date = $_GET['auc_due_date'];


$auc_prequalification_criteria = $_GET['auc_prequalification_criteria'];
$auc_details = $_GET['auc_details'];
$auc_preferred_location=$_GET['auc_preferred_location'];

$af_id=$_GET['af_id'];
$afv_val=$_GET['afv_val'];

 $typeofselection = $_GET['typeofselection'];
 $keywordsFilter = $_GET['keywordsFilter'];
$data=array();
$valid = true;
if(!$typeofselection){
if($main_cat=="")
{
	$data[0]="0";
	$data[1]='Kindly select Main Category.';
	$valid=false;
}
else if($pc_id=="")
{
	$data[0]="0";
	$data[1]='Kindly select Category.';
	$valid=false;
}
else if($pc_id=="")
{
	$data[0]="0";
	$data[1]='Kindly select Sub-Category.';
	$valid=false;
}
}
elseif($typeofselection){
 if($keywordsFilter=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Keyword.';
	$valid=false;
}


 $searchedproducts = $_SESSION['searchedproducts'];

if(!$searchedproducts && !array_key_exists($keywordsFilter,$searchedproducts))  {
	$data[0]="0";
	$data[1]='No category found with given keywords';
	$valid=false;
}
     $keywordsFilter =  explode(">>",$keywordsFilter)   ;

$keywordsFilter1 = end($keywordsFilter);
$auc_pc_id = $searchedproducts[$keywordsFilter1];
$pc_id = $searchedproducts[$keywordsFilter[1]];
$main_cat = $searchedproducts[$keywordsFilter[0]];
if(!$auc_pc_id){
 	$data[0]="0";
	$data[1]='No category found with given keywords';
	$valid=false;
}

}
else if($auc_heading=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Auction Heading.';
	$valid=false;
}
else if($auc_heading!="" && checkBadWord(strtoupper($auc_heading))==false)
{
	$data[0]="0";
	$data[1]="You can't post this Auction Heading. It contains some Bad words.";
	$valid=false;
}
else if($auc_notice_type=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Notice Type.';
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
	$data[1]='Kindly describe Auction details.';
	$valid=false;
}
if($valid)
{

	$sql1="insert into temp_auction_alert_cat
		set
			taac_usr_id='".$_SESSION['uid_indm']."',
			taac_pc_id='".$auc_pc_id."',
			taac_updated_date=now()";

	mysqli_query($con, $sql1);
	$sql2="select * from temp_auction_alert_cat where taac_usr_id='".$_SESSION['uid_indm']."'";

	$res2=mysqli_query($con, $sql2);
	while($row=mysqli_fetch_object($res2)){
		$sql_exist="select * from auction_alert_category where aac_usr_id='".$_SESSION['uid_indm']."' AND aac_pc_id='".$row->taac_pc_id."'";
		$res12=mysqli_query($con, $sql_exist);
		if($res12->num_rows==0){
		$sql_ins="insert into auction_alert_category
			set
				aac_usr_id='".$_SESSION['uid_indm']."',
				aac_pc_id='".$row->taac_pc_id."',
				aac_updated_date=now()";

		mysqli_query($con, $sql_ins);
		}
	}
	
	mysqli_query($con, "delete from temp_auction_alert_cat where taac_usr_id='".$_SESSION['uid_indm']."'");
	
	$sql_exist="select * from auction_alert_category where aac_usr_id='".$_SESSION['uid_indm']."' AND aac_pc_id='".$auc_pc_id."'";
	$res12=mysqli_query($con, $sql_exist);
	if($res12->num_rows==0){
	$sql_ins="insert into auction_alert_category
		set
			aac_usr_id='".$_SESSION['uid_indm']."',
			aac_pc_id='".$auc_pc_id."',
			aac_updated_date=now()";

	//mysqli_query($con, $sql_ins);
	}
	
	$publish_date = str_replace('/', '-', $auc_publish_date);
	$docSaleStart_date = str_replace('/', '-', $auc_docSaleStart_date);
	$docSaleEnd_date = str_replace('/', '-', $auc_docSaleEnd_date);
	$docSubmitBefore_date = str_replace('/', '-', $auc_docSubmitBefore_date);
	$due_date = str_replace('/', '-', $auc_due_date);
		
	$sql="insert into auction
		set
			auc_usr_id='".$auc_usr_id."',
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
			auc_updated_date=now()";
	mysql_query($sql);
	
	$tnd=mysql_insert_id();
		
	if($af_id!='' && $afv_val!='')	//add additional information
	{
		$tav_af_id=explode("|",$af_id);
		$aav_value=explode("|", $afv_val);
			
		for($i=0;$i<count($tav_af_id);$i++)
		{
			$val=explode("-",$aav_value[$i]);
			for($c=0;$c<count($val);$c++)
			{
				$sql_sav="insert into auction_additional_value
					set
						aav_auc_id='".$tnd."',
						aav_af_id='".$tav_af_id[$i]."',
						aav_value='".$val[$c]."',
						aav_status='1'";
				mysql_query($sql_sav);
			}
		}
	}
	$data[0]="1";
	$data[1]='Auction posted successfully.';
}
echo $data[0]."|".$data[1];

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
?>