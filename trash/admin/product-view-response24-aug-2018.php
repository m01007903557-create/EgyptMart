<?php 
include "../common.php";
ini_set("display_errors", 1);
global $con;
// initilize all variable
	$params = $columns = $totalRecords = $data = array();

	$params = $_REQUEST;

	//define index of column
	$columns = array( 
		1 =>'pd_date',
		3 =>'pd_title', 
		4 => 'pc_name',
		5 => 'pd_fob_price',
		6 => 'bnsprof_compname',
		7 => 'cn_name',
		8 => 'mst_name',
		9 => 'expiry_date'
		
	);

	$where = $sqlTot = $sqlRec = "";

	// check search value exist
	if( !empty($params['search']['value']) ) {   
		$where .=" WHERE ";
		$where .=" ( pd_title LIKE '".$params['search']['value']."%' ";    
		$where .=" OR pc_name LIKE '".$params['search']['value']."%' ";
		$where .=" OR bnsprof_compname LIKE '".$params['search']['value']."%' ";
		$where .=" OR cn_name LIKE '".$params['search']['value']."%' ";
		$where .=" OR pd_date LIKE '".$params['search']['value']."%' ";
		$where .=" OR pd_fob_price LIKE '".$params['search']['value']."%' ";
		$where .=" OR sp.mst_name LIKE '".$params['search']['value']."%' )";
		if(strtolower($params['search']['value']) == 'active')
		$where .=' OR pm.expiry_date > UNIX_TIMESTAMP()';
		else if(strtolower($params['search']['value']) == 'inactive')
		$where .=' OR pm.expiry_date < UNIX_TIMESTAMP()';
	}

	// getting total number records without any search
	//$sql = "select pd_id, p.pd_image,p.pd_date,p.pd_image, p.pd_imagelogo,p.pd_title, p.pd_status, pc.pc_name,p.pd_fob_price,bf.bnsprof_compname,c.cn_name, sip.mst_name from products p JOIN product_category pc ON p.pd_subcat_id=pc.pc_id JOIN user u ON u.usr_id = p.pd_uid LEFT JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id LEFT JOIN country c ON c.cn_id = u.country ";
	
	$sql = "select pd_id,p.pd_uid, p.pd_image,p.pd_date,p.pd_image, p.pd_imagelogo,p.pd_title, p.pd_status, pc.pc_name,p.pd_fob_price,c.cn_name,bf.bnsprof_compname, pm.expiry_date, sp.mst_name from products p JOIN product_category pc ON p.pd_subcat_id=pc.pc_id JOIN user u ON u.usr_id = p.pd_uid JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid JOIN country c ON c.cn_id = u.country LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";
	
	$sqlTot .= "select count(*) as count,pd_id,p.pd_uid, p.pd_image,p.pd_date,p.pd_image, p.pd_imagelogo,p.pd_title, p.pd_status, pc.pc_name,p.pd_fob_price,c.cn_name,bf.bnsprof_compname, pm.expiry_date, sp.mst_name from products p JOIN product_category pc ON p.pd_subcat_id=pc.pc_id JOIN user u ON u.usr_id = p.pd_uid  JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid JOIN country c ON c.cn_id = u.country LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";
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
			$res[0] ='<input name="cb[]"  class="ace" type="checkbox" value="'.$row->pd_id.'" /><span class="lbl"></span>';
			$res[1] = date('d M, y',strtotime($row->pd_date));
			$res[2] = '  <div style=" position: relative;">';
			$img=$row->pd_image;
            $makearr=explode(',', $img);
			 if($row->pd_imagelogo !=''){
			 $logoarr = explode(',',$row->pd_imagelogo); 
				$res[2] .='<a href="#"> <img src="../upload/myproduct/'. $logoarr[0].'" style="position: absolute; top:48px; width:30px;height:29px;" /> </a>';
			} 
            $res[2] .='<a href="#"> <img src="../upload/myproduct/'. (($row->pd_image!='')?$makearr[0]:'noimage.jpg').'" style="width:80px; height:78px;"/></a></div>';
			$res[3] = ucwords(stripslashes($row->pd_title));
			$res[4] = ucwords(stripslashes($row->pc_name));
			$res[5] = get_product_detail($row->pd_id,'pd_currency')." ".$row->pd_fob_price;
			$res[6] = $row->bnsprof_compname;
			$res[7] = $row->cn_name;
			$res[8] = $row->mst_name;
			$res[9] = (($row->expiry_date != '')?(date("d F Y", $row->expiry_date)):'').' '. ((date("Y-m-d", $row->expiry_date) > date("Y-m-d"))?'Active':'Inactive');
			$res[10] = '<a href="product-details.php?token='.rand(1000,9999).md5($row->pd_id).'"><img src="images/details.png" /></a>';
			$val = '';
			if($_SERVER['QUERY_STRING']==""){
				$plink="?action=appr&id=".$row->pd_id;
			}
			else
			{
				$plink="product-view.php?".$_SERVER['QUERY_STRING']."&action=appr&id=".$row->pd_id;
			}
		
			if($_SERVER['QUERY_STRING']==""){
				$dlink="?action=disappr&id=".$row->pd_id;
			}
			else
			{
				$dlink="product-view.php?".$_SERVER['QUERY_STRING']."&action=disappr&id=".$row->pd_id;
			}
			
			
			if($_SERVER['QUERY_STRING']==""){
				$dellink="?action=del&ad-id=".$row->pd_id;
			}
			else{
				$dellink=$_SERVER['QUERY_STRING']."&action=del&ad-id=".$row->pd_id;
			}
		
			if($row->pd_status=='0'){
            $val ='<a data-id="'.$row->pd_id.'"  class="approve_product" title="Approve"><img alt="Approve" src="images/active.jpg"></a>&nbsp;<a  data-id="'.$row->pd_id.'"  class="disapprove_product" title="Disapprove"><img alt="Disapprove" src="images/reject.png" width="19" height="19"  border="0"></a>';            
            }
			elseif($row->pd_status=='1'){	
            $val .= '<font color="#009933" weight="800">Approved</font>';
            }elseif($row->pd_status=='2'){	
            $val .= '<font color="#CC0000" weight="800">Rejected</font>';
            }		
			$res[11] = $val;
			$res[12] = '<a href="product-edit.php?fid='. $row->pd_id.'" title="Edit"><img src="images/edit.jpg"/></a>
            <a href="'.$dellink.'" onclick="return confirm(\'Are you sure to Delete the Product?\')" title="Delete"><img src="images/delete.jpg"/></a>';
			
			
		$data[] = $res;
	}	
	
	$json_data = array(
			"draw"            => intval( $params['draw'] ),   
			"recordsTotal"    => intval( $totalRecords ),  
			"recordsFiltered" => intval($totalRecords),
			"data"            => $data   // total data array
			);

	echo json_encode($json_data);  // send data as json format