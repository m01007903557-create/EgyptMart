<?php 
include "../common.php";
ini_set("display_errors", 1);
global $con;
// initilize all variable
	$params = $columns = $totalRecords = $data = array();

	$params = $_REQUEST;

	//define index of column
	$columns = array( 
		1 =>'so_posting_date',
		2 =>'so_service', 
		3 => 'bnsprof_compname',
		4 => 'cn_name',
		5 => 'mst_name',
	);

	$where = $sqlTot = $sqlRec = "";

	// check search value exist
	if( !empty($params['search']['value']) ) {   
		$where .=" WHERE ";
		$where .=" ( so_service LIKE '".$params['search']['value']."%' ";    
		$where .=" OR so_posting_date LIKE '".$params['search']['value']."%' ";
		$where .=" OR bnsprof_compname LIKE '".$params['search']['value']."%' ";
		$where .=" OR cn_name LIKE '".$params['search']['value']."%' ";
		$where .=" OR sp.mst_name LIKE '".$params['search']['value']."%' )";
	}

	// getting total number records without any search
	//$sql = "select pd_id, p.pd_image,p.pd_date,p.pd_image, p.pd_imagelogo,p.pd_title, p.pd_status, pc.pc_name,p.pd_fob_price,bf.bnsprof_compname,c.cn_name, sip.mst_name from products p JOIN product_category pc ON p.pd_subcat_id=pc.pc_id JOIN user u ON u.usr_id = p.pd_uid LEFT JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id LEFT JOIN country c ON c.cn_id = u.country ";
	
	$sql = "select so_id,s.so_usr_id, s.so_pic,s.so_posting_date,s.so_service, s.so_approval_status, pc.pc_name,c.cn_name,bf.bnsprof_compname, pm.expiry_date, sp.mst_name from sale_offer s JOIN product_category pc ON s.so_pc_id=pc.pc_id JOIN user u ON u.usr_id = s.so_usr_id JOIN business_profile bf ON bf.bnsprof_uid = s.so_usr_id JOIN country c ON c.cn_id = u.country LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";
	
	$sqlTot .= "select count(*) as count from sale_offer s JOIN product_category pc ON s.so_pc_id=pc.pc_id JOIN user u ON u.usr_id = s.so_usr_id JOIN business_profile bf ON bf.bnsprof_uid = s.so_usr_id JOIN country c ON c.cn_id = u.country LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";
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
			$res[0] ='<input name="cb[]"  class="ace" type="checkbox" value="'.$row->so_id.'" /><span class="lbl"></span>';
		
			if($row->so_pic!=''){ 
           	 $res[1]='<img src="../upload/sale_offer/'.$row->so_pic.'" width="70px;" height="62px;" />';
			}else{	
             $res[1]='<img src="../upload/sale_offer/no-image.png" width="70px;" height="62px;" />';
          	}
			$res[2] = ucwords(stripslashes($row->so_service));
			
			$res[3] = ucwords(stripslashes($row->bnsprof_compname));
			$res[4] = $row->cn_name;
			$res[5] = $row->mst_name;
			$res[6] = date_format(date_create($row->so_posting_date),"jS F Y");
		
			$val = '';
					if($_SERVER['QUERY_STRING']==""){
						$plink="?action=appr&id=".$row->so_id;
					}
					else
					{
						$plink="selloffer-view.php?".$_SERVER['QUERY_STRING']."&action=appr&id=".$row->so_id;
					}
				
					if($_SERVER['QUERY_STRING']==""){
						$dlink="?action=disappr&id=".$row->so_id;
					}
					else
					{
						$dlink="selloffer-view.php?".$_SERVER['QUERY_STRING']."&action=disappr&id=".$row->so_id;
					}
					
					
					if($_SERVER['QUERY_STRING']==""){
						$dellink="?action=del&ad-id=".$row->so_id;
					}
					else{
						$dellink=$_SERVER['QUERY_STRING']."&action=del&ad-id=".$row->so_id;
					}
				
					if($row->so_approval_status=='0'){
					$val ='<a href="'.$plink.'" onclick="return confirm(\'Are you sure to approve the Product?\')" title="Approve"><img alt="Approve" src="images/active.jpg"></a>&nbsp;<a href="'. $dlink.'" onclick="return confirm(Are you sure to dissapprove the Product?)" title="Disapprove"><img alt="Disapprove" src="images/reject.png" width="19" height="19"  border="0"></a>';            
					}
					elseif($row->so_approval_status=='1'){	
					$val .= '<font color="#009933" weight="800">Approved</font>';
					}elseif($row->so_approval_status=='2'){	
					$val .= '<font color="#CC0000" weight="800">Rejected</font>';
					}	
		
			$res[7] = $val;
			$res[8] = '<a href="selloffer-details.php?token='.rand(1000,9000).md5($row->so_id).'"><img src="images/details.png" /></a>
            <a href="selloffer-edit.php?token='.md5($row->so_id).'"><img src="images/edit.jpg" /></a>';
			
			
			
		$data[] = $res;
	}	
	
	$json_data = array(
			"draw"            => intval( $params['draw'] ),   
			"recordsTotal"    => intval( $totalRecords ),  
			"recordsFiltered" => intval($totalRecords),
			"data"            => $data   // total data array
			);

	echo json_encode($json_data);  // send data as json format