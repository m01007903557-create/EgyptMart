<?php 
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../' . PATH_SEPARATOR . dirname(__FILE__) . '/../lib' . PATH_SEPARATOR . dirname(__FILE__) . '/../includes:/home/customer/www/egyptmart.online/public_html/:/home/customer/www/egyptmart.online/public_html/lib:/home/customer/www/egyptmart.online/public_html/includes');
include "../common.php";
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
include_once '../lib/function.php';

global $con;

if (isset($_POST['verifyId'])) {
	//error_reporting(E_ALL);
	echo ($_POST['verifyId']);
	
$_SESSION['uid_indm'] = $_POST['verifyId'];
	
$link = "<a href=http://".$_SERVER['SERVER_NAME']."/verifyUser.php?token=".rand(1000,9999).md5($_SESSION['uid_indm']).">Verify</a>";
$to = stripslashes(user_info($_SESSION['uid_indm'],'email')); 
$subject = "Email Verification from ".get_page_settings(4);
$from_name = get_page_settings(4);
$from_email = get_adminemail();

//echo get_include_path();
include "email/emailVerification.php"; //email design with content included
//$_SESSION['email_verify_for']=$_SESSION['uid_indm'];
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= "From: $from_name < $from_email >";

if(mail($to, $subject, $message1, $headers)){
	echo 'Sent!';
	//header("location:my-dashboard.php?verifylinksend=1");
}
	
	
	exit;
}
// initilize all variable
	$params = $columns = $totalRecords = $data = array();

	$params = $_REQUEST;

	//define index of column
	$columns = array( 
		1 =>'usr_id',
		2 =>'date',
		3 =>'fname', 
		4 => 'lname',
		5 => 'email',
		6 => 'bnsprof_compname',
		7 => 'cn_name'
		
	);

	$where = $sqlTot = $sqlRec = "";

	// check search value exist
	if( !empty($params['search']['value']) ) {   
		$where .=" AND ";
		$where .=" ( fname LIKE '%".$params['search']['value']."%' ";    
		$where .=" OR lname LIKE '%".$params['search']['value']."%' ";
		$where .=" OR bnsprof_compname LIKE '%".$params['search']['value']."%' ";
		$where .=" OR cn_name LIKE '%".$params['search']['value']."%' ";
		$where .=" OR date LIKE '%".$params['search']['value']."%' ";
		$where .=" OR email LIKE '%".$params['search']['value']."%' )";
	}

	// getting total number records without any search
	//$sql = "select pd_id, p.pd_image,p.pd_date,p.pd_image, p.pd_imagelogo,p.pd_title, p.pd_status, pc.pc_name,p.pd_fob_price,bf.bnsprof_compname,c.cn_name, sip.mst_name from products p JOIN product_category pc ON p.pd_subcat_id=pc.pc_id JOIN user u ON u.usr_id = p.pd_uid LEFT JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id LEFT JOIN country c ON c.cn_id = u.country ";
	
	$sql = "select * from country,business_profile bf JOIN user u ON u.usr_id = bf.bnsprof_uid LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id LEFT JOIN states s ON bnsprof_state=s.state_id LEFT JOIN city c on bnsprof_city=ct_id where country=cn_id and bnsprof_uid=usr_id and status='1' ";
	
	$sqlTot .= "select count(*) as count from country,business_profile bf JOIN user u ON u.usr_id = bf.bnsprof_uid LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where country=cn_id and bnsprof_uid=usr_id and status='1' ";
	$sqlRec .= $sql;
	//concatenate search sql if value exist
	if(isset($where) && $where != '') {

		$sqlTot .= $where;
		$sqlRec .= $where;
	}
	
	if($params['order'][0]['column'] == 0) {
		$params['order'][0]['column'] = 1;
		$params['order'][0]['dir'] = 'desc';
	}
	
	$sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir']."  LIMIT ".$params['start']." ,".$params['length']." ";
	

	$queryTot = mysqli_query($con, $sqlTot) or die("database error:". mysqli_error($con));
	while( $queryTotObj = mysqli_fetch_object($queryTot) ) {	
	$totalRecords = $queryTotObj->count;
	}
	//echo $totalRecords;exit;
	//echo $sqlRec;exit;
	$queryRecords = mysqli_query($con, $sqlRec) or die("error to fetch employees data");
	
	//iterate on results row and create new index array of data
	while( $row = mysqli_fetch_object($queryRecords) ) {			
			$res = array();
			$res[0] ='<input name="cb[]"  class="ace" type="checkbox" value="'.$row->usr_id.'" /><span class="lbl"></span>';
			$res[1] = date('d/M / Y',strtotime($row->bnsprof_creation_date));
			$res[2] = ucwords($row->bnsprof_compname);
			$res[3] = '<a href="'.$row->website.'" target="_blank">'.str_replace(".","<br>.",$row->website).'</a>';
			$res[4] = '<a href="user-details.php?token='.rand(1000,9999).md5($row->usr_id).'" target="_blank">'.str_replace(".",'<br>.',ucwords($row->name_prefix." ".$row->lname." ".$row->fname)).'</a>';
			$res[5] =  '+'.$row->country_ph_code.' '.$row->mobile1;
			$res[6] =   str_replace("@","<br>@",$row->email);
			$res[7] = $row->cn_name;
			$res[8] = $row->state_name;
			$res[9] = $row->ct_name;
			$res[10] = (date('d F Y',$row->expiry_date) == '09 September 9999')?'PROMO Plan':$row->mst_name;

			$res[11] = (($row->expiry_date != '' && date('d F Y',$row->expiry_date) == '09 September 9999')?'Permanent':((($row->expiry_date != '')?(date("d F Y", $row->expiry_date)):'')).' '. ((date("Y-m-d", $row->expiry_date) > date("Y-m-d"))?'Active':'Inactive'));
			//$res[11] = (($row->expiry_date != '')?(date("d F Y", $row->expiry_date)):'').' '. ((date("Y-m-d", $row->expiry_date) > date("Y-m-d"))?'Active':'Inactive');
		
			if($row->usr_emailVerify == '0'){ $stat= '<font color=red>Email not verified</font><div id="verify-link-'.$row->usr_id.'"><a href="javascript:void(0);" onclick="verifyNow('.$row->usr_id.', this)" style="color: #F00">Verify Now</a></div>';}elseif($row->usr_emailVerify == '1'){ $stat= '<font color=green>Email verified</font>';}
			$res[12] = $stat;
			
			//$res[11] = '123';
			$res[13] = '<a href="company-details.php?token='.md5($row->bnsprof_id).'"><img src="images/details.png" /></a>';
			
			//$res[12] ='test';
			//$res[13] ='test2';
			$res[14] = '';
			
		$data[] = $res;
		
	}	
	
	$json_data = array(
			"draw"            => intval( $params['draw'] ),   
			"recordsTotal"    => intval( $totalRecords ),  
			"recordsFiltered" => intval($totalRecords),
			"data"            => $data   // total data array
			
			);

	echo json_encode($json_data);  // send data as json format