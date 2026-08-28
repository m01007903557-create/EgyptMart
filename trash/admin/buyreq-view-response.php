<?php 
include "../common.php";
ini_set("display_errors", 1);
global $con;
// initilize all variable
	$params = $columns = $totalRecords = $data = array();

	$params = $_REQUEST;

	//define index of column
	$columns = array( 
		1 =>'br_posting_date',
		2 =>'br_pd_name', 
		3 => 'br_requirement',
		4 => 'bnsprof_compname',
		5 => 'cn_name',
		6 => 'mst_name',
		7 => 'expiry_date',
		
	);

	$where = $sqlTot = $sqlRec = "";

	// check search value exist
	if( !empty($params['search']['value']) ) {   
		$where .=" WHERE ";
		$where .=" ( br_pd_name LIKE '".$params['search']['value']."%' ";    
		$where .=" OR br_requirement LIKE '".$params['search']['value']."%' ";
		$where .=" OR bnsprof_compname LIKE '".$params['search']['value']."%' ";
		$where .=" OR cn_name LIKE '".$params['search']['value']."%' ";
		$where .=" OR br_updated_date LIKE '".$params['search']['value']."%' ";
		$where .=" OR sp.mst_name LIKE '".$params['search']['value']."%' )";
		
	}
	
	$sql = "select br_id,br.br_u_id, br.br_pic,br.br_posting_date,br_requirement,br.br_pd_name,br.br_approval_status,c.cn_name,bf.bnsprof_compname, sp.mst_name from buy_requirement br JOIN measurement_unit mu ON br.br_estimate_qty_unit=mu.mu_id JOIN user u ON u.usr_id = br.br_u_id LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id LEFT JOIN country c ON c.cn_id = u.country LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id  LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";
	
	$sqlTot .= "select count(*) as count from buy_requirement br JOIN measurement_unit mu ON br.br_estimate_qty_unit=mu.mu_id JOIN user u ON u.usr_id = br.br_u_id LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id LEFT JOIN country c ON c.cn_id = u.country LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id  LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";
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
	$queryRecords = mysqli_query($con, $sqlRec) or die(mysqli_error($con));
	
	//iterate on results row and create new index array of data
	while( $row = mysqli_fetch_object($queryRecords) ) {			
			$res = array();
			$res[0] ='<input name="cb[]"  class="ace" type="checkbox" value="'.$row->br_id.'" /><span class="lbl"></span>';
		if($row->br_pic !=''){
			$img='<img src="../upload/buy_requirement/thumb/'.$row->br_pic.'" border="0" hspace="0" vspace="0">';
		}else{
			$img='<img src="../upload/buy_requirement/thumb/no-image.png" border="0" hspace="0" vspace="0">';
		}
			$res[1] = date_format(date_create($row->br_posting_date),"jS F Y");
			$res[2] = $img;
			$res[3] = $row->br_pd_name;
			$res[4] = ucwords(stripslashes($row->br_requirement));
			$res[5] = ucwords(stripslashes($row->bnsprof_compname));
			$res[6] = $row->cn_name;
			$res[7] = $row->mst_name;
			$res[8] ='';
			$res[9] = '';
		
			$val = '';
			if($_SERVER['QUERY_STRING']==""){
				$plink="?action=appr&id=".$row->br_id;
				$ballink = "?action=appr&id=".$row->br_id;
			}
			else
			{
				$plink="buyreq-view.php?".$_SERVER['QUERY_STRING']."&action=appr&id=".$row->br_id;
				$ballink = "?action=appr&id=".$row->br_id;
			}
		
			if($_SERVER['QUERY_STRING']==""){
				$dlink="?action=disappr&id=".$row->br_id;
			}
			else
			{
				$dlink="buyreq-view.php?".$_SERVER['QUERY_STRING']."&action=disappr&id=".$row->br_id;
			}
			
			
			if($_SERVER['QUERY_STRING']==""){
				$dellink="?action=del&ad-id=".$row->br_id;
			}
			else{
				$dellink=$_SERVER['QUERY_STRING']."&action=del&ad-id=".$row->br_id;
			}
			 if($row->br_approval_status=='0'){	
             	$status='<a href="'.$plink.'"  title="Approve"><img alt="Approve" src="images/active.jpg"></a>&nbsp;<a href="'.$dlink.'" title="Disapprove"><img alt="Disapprove" src="images/reject.png" width="19" height="19"  border="0"></a>';
            	}else if($row->br_approval_status=='1'){	
					$status ='<font color="#009933" weight="800">Approved</font>';
					$status .= '<br><a href="'.$ballink.'" class="approve_product" title="Approve">Re-send</a>';
				 } else if($row->br_approval_status=='2'){
					$status ='<font color="#CC0000" weight="800">Rejected</font>';
				 } 
				$res[8] = $status;
		
			$res[9] = '<a href="buyreq-details.php?token='.rand(1000
,9000).md5($row->br_id).'"><img src="images/details.png" /> <a href="buyreq-edit.php?token='.md5($row->br_id).'" title="Edit"><img src="images/edit.jpg" alt="Edit" />';
						
		$data[] = $res;
	}	
	$json_data = array(
			"draw"            => intval( $params['draw'] ),   
			"recordsTotal"    => intval( $totalRecords ),  
			"recordsFiltered" => intval($totalRecords),
			"data"            => $data   // total data array
			);

	echo json_encode($json_data);  // send data as json format