<?php 
include "../common.php";
ini_set("display_errors", 1);
global $con;
// initilize all variable
	$params = $columns = $totalRecords = $data = array();

	$params = $_REQUEST;

	//define index of column
	$columns = array( 
		1 =>'auc_due_date',
		2 =>'auc_heading', 
		3=> 'pc_name',
		4 => 'auc_value',
		5 => 'bnsprof_compname',
		6 => 'cn_name',
		7 => 'mst_name',
		8 => 'expiry_date'
	);

	$where = $sqlTot = $sqlRec = "";

	// check search value exist
	if( !empty($params['search']['value']) ) {   
		$where .=" AND  ";
		$where .=" ( auc_heading LIKE '%".$params['search']['value']."%' ";    
		$where .=" OR pc_name LIKE '%".$params['search']['value']."%' ";
		$where .=" OR bnsprof_compname LIKE '%".$params['search']['value']."%' ";
		$where .=" OR cn_name LIKE '%".$params['search']['value']."%' ";
		$where .=" OR auc_due_date LIKE '%".$params['search']['value']."%' ";
		$where .=" OR auc_value LIKE '%".$params['search']['value']."%' )";
		
	}

	// getting total number records without any search
	//$sql = "select pd_id, p.pd_image,p.pd_date,p.pd_image, p.pd_imagelogo,p.pd_title, p.pd_status, pc.pc_name,p.pd_fob_price,bf.bnsprof_compname,c.cn_name, sip.mst_name from products p JOIN product_category pc ON p.pd_subcat_id=pc.pc_id JOIN user u ON u.usr_id = p.pd_uid LEFT JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id LEFT JOIN country c ON c.cn_id = u.country ";
	
	$sql = "select auc_id,auc_usr_id,auc_heading,auc_publish_date,auc_due_date,auc_approval_status,bnsprof_compname,cn_name from auction a JOIN product_category pc ON a.auc_pc_id=pc.pc_id JOIN user u ON u.usr_id = a.auc_usr_id JOIN business_profile bf ON bf.bnsprof_uid = a.auc_usr_id JOIN country c ON c.cn_id = u.country where auc_status='1' AND auc_due_date >= curdate()";
	
	$sqlTot .= "select count(*) as count from auction a JOIN product_category pc ON a.auc_pc_id=pc.pc_id JOIN user u ON u.usr_id = a.auc_usr_id JOIN business_profile bf ON bf.bnsprof_uid = a.auc_usr_id JOIN country c ON c.cn_id = u.country where auc_status='1' AND auc_due_date >= curdate()";
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
	//echo $sqlRec;

	$queryTot = mysqli_query($con, $sqlTot) or die("database error:". mysqli_error($con));
	while( $queryTotObj = mysqli_fetch_object($queryTot) ) {	
	$totalRecords = $queryTotObj->count;
	}
	//echo $totalRecords;exit;
	//echo $sqlRec;exit;
	$queryRecords = mysqli_query($con, $sqlRec) or die(mysqli_error($con));
	
	//iterate on results row and create new index array of data
	while( $row = mysqli_fetch_object($queryRecords) ) {			
			$res = array();
			$res[0] ='<input name="cb[]"  class="ace" type="checkbox" value="'.$row->auc_id.'" /><span class="lbl"></span>';
			$res[1] = ucwords(stripslashes($row->auc_heading));
			$res[2] = ucwords(stripslashes($row->bnsprof_compname));
			$res[3] = ucwords(stripslashes($row->cn_name));
			$res[4] = date("d-M,Y",strtotime($row->auc_publish_date));
			$res[5] = date("d-M,Y",strtotime($row->auc_due_date));
			$res[6] = '<a href="auction-details.php?token='.rand(1000,9000).md5($row->auc_id).'"><img src="images/details.png" /></a>';
			$val = '';
			if($_SERVER['QUERY_STRING']==""){
				$plink="?action=appr&id=".$row->auc_id;
			}
			else
			{
				$plink="auction-view.php?".$_SERVER['QUERY_STRING']."&action=appr&id=".$row->auc_id;
			}
		
			if($_SERVER['QUERY_STRING']==""){
				$dlink="?action=disappr&id=".$row->auc_id;
			}
			else
			{
				$dlink="auction-view.php?".$_SERVER['QUERY_STRING']."&action=disappr&id=".$row->auc_id;
			}
			
			
			if($_SERVER['QUERY_STRING']==""){
				$dellink="?action=del&ad-id=".$row->auc_id;
			}
			else{
				$dellink=$_SERVER['QUERY_STRING']."&action=del&ad-id=".$row->auc_id;
			}
			if($row->auc_approval_status=='0'){
				$val ='<a href="'.$plink.'" onclick="return confirm(\'Are you sure to approve the Tender?\')" title="Approve"><img alt="Approve" src="images/active.jpg"></a>&nbsp;<a href="'. $dlink.'" onclick="return confirm(\'Are you sure to dissapprove the Tender?\')" title="Disapprove"><img alt="Disapprove" src="images/reject.png" width="19" height="19"  border="0"></a>';            
			}
			elseif($row->auc_approval_status=='1'){	
				$val .= '<font color="#009933" weight="800">Approved</font>';
			}elseif($row->auc_approval_status=='2'){	
				$val .= '<font color="#CC0000" weight="800">Rejected</font>';
			}	
			
			$res[7] = $val;
			$res[8]='<a href="auction-edit.php?token='.md5($row->auc_id).'" title="Edit"><img src="images/edit.jpg" alt="Edit" /></a>';
			
			
		$data[] = $res;
	}	
	
	$json_data = array(
			"draw"            => intval( $params['draw'] ),   
			"recordsTotal"    => intval( $totalRecords ),  
			"recordsFiltered" => intval($totalRecords),
			"data"            => $data   // total data array
			);

	echo json_encode($json_data);  // send data as json format