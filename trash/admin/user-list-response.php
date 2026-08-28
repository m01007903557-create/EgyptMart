<?php 
include "../common.php";
ini_set("display_errors", 1);
global $con;
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
		$where .=" OR mst_name LIKE '%".$params['search']['value']."%' ";
		$where .=" OR email LIKE '%".$params['search']['value']."%' )";
	}

	// getting total number records without any search
	//$sql = "select pd_id, p.pd_image,p.pd_date,p.pd_image, p.pd_imagelogo,p.pd_title, p.pd_status, pc.pc_name,p.pd_fob_price,bf.bnsprof_compname,c.cn_name, sip.mst_name from products p JOIN product_category pc ON p.pd_subcat_id=pc.pc_id JOIN user u ON u.usr_id = p.pd_uid LEFT JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id LEFT JOIN country c ON c.cn_id = u.country ";
	
	$sql = "select * from user,country,business_profile,smembership_plan where bnsprof_uid=usr_id and country=cn_id and mp_id=usr_mp_id and status='1' ";
	
	$sqlTot .= "select count(*) as count from user,country,business_profile,smembership_plan where bnsprof_uid=usr_id and mp_id=usr_mp_id and country=cn_id and status='1'";
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
			$res[1] = ucwords($row->name_prefix." ".$row->fname." ".$row->lname);
			$res[2] = $row->email;
			$res[3] =  ucwords($row->usr_credit);
			$res[4] = $row->cn_name;
			$res[5] = $row->mst_name;
			$res[6] = '<a href="user-details.php?token='.rand(1000,9999).md5($row->usr_id).'"><img src="images/details.png" /></a>';
			if($_SERVER['QUERY_STRING']==""){
				$dellink="?action=del&ad-id=".$row->usr_id;
			}
			else{
				$dellink=$_SERVER['QUERY_STRING']."&action=del&ad-id=".$row->usr_id;
			}
			$res[7] = '	<a href="user-edit.php?token='.rand(1000,9999).$row->usr_id.'"><img src="images/edit.jpg" /></a><a href="'.$dellink.'" onclick="return confirm(\'Are you sure to Delete the Product?\')" title="Delete"><img src="images/delete.jpg"/></a>';
		$data[] = $res;
	}	
	
	$json_data = array(
			"draw"            => intval( $params['draw'] ),   
			"recordsTotal"    => intval( $totalRecords ),  
			"recordsFiltered" => intval($totalRecords),
			"data"            => $data   // total data array
			);

	echo json_encode($json_data);  // send data as json format