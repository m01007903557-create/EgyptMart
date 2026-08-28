<?php 

/********************  Video  ****************************/

function show_youtube($text)
{
	$VID_WID = 300;
	$VID_HEI = 200;

	for ($k=0; $k<9; $k++)
	{
		$text .= ' ';
		$find = 'youtube.com/watch?v=';
		$pos = strpos($text, $find);
		if ($pos === false)
		{
			break;
		}
		$len = strlen($text);
		for ($i=$pos; $i>=0; $i--)
		{
			if (substr($text, $i, 6) == 'http:/')
			{
				$pos1 = $i;
				break;
			}
		}
		for ($i=$pos; $i<$len; $i++)
		{
			if (in_array($text[$i], array('&', ' ', "\r", "\n", ',', "\t")))
			{
				$pos2 = $i;
				break;
			}
		}
		$link1 = substr($text, $pos1, $pos2 - $pos1);
		$link2 = str_replace('/watch?v=', '/v/', $link1);

		$embed =	'<object width="' . $VID_WID . '" height="' . $VID_HEI . '">'.
					'<param name="movie" value="' . $link2 . '"></param>'.
					'<param name="allowFullScreen" value="true"></param>'.
					'<param name="allowscriptaccess" value="always"></param>'.
					'<embed src="' . $link2 . '" type="application/x-shockwave-flash" '.
					'allowscriptaccess="always" allowfullscreen="true" '.
					'width="' . $VID_WID . '" height="' . $VID_HEI . '"></embed></object>';

		$text = str_replace($link1, $embed, $text);
	}
	return $text;
}


/********************  End Video  ****************************/

function get_category_name($id)     /* $id = Category id in MD5*/
{
	global $con;
	$sql = mysqli_query($con, "select * from product_category where md5(pc_id) = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return $row->pc_name;
}
function get_category_id($id)     /* $id = Category id in MD5*/
{
	global $con;
	$sql = mysqli_query($con, "select * from product_category where md5(pc_id) = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return $row->pc_id;
}

function seller_info($id,$field)    
{
	global $con;
	$sql = mysqli_query($con, "select * from seller where sllr_usr_id = '".$id."' and sllr_status = '1'");
	$row = mysqli_fetch_array( $sql);
	return $row[$field];
}

function prev_cat($id,$type)     /* $type = name | id */
{
	global $con;
    $sql = mysqli_query($con, "select * from product_category where pc_id = (select pc_parent_id from product_category where md5(pc_id) = '".$id."') and pc_status = '1'");
	$row = mysqli_fetch_object($sql);
            if($type == 'name')
            {
                 return $row->pc_name;
            }
            elseif($type == 'id')
            {
                return $row->pc_id;
            }
}

function get_cntname($id,$type)    
{
	global $con;
	if($type=="state")
	{
	$sql = mysqli_query($con, "select cn_name from states,country where state_cn_id=cn_id and state_id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	}
	return ucwords($row->cn_name);
}

function get_country_name($id)    
{
	global $con;
	$sql = mysqli_query($con, "select cn_name from country where cn_id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return ucwords($row->cn_name);
}
function get_country_flag($id)    //pranab
{
	global $con;
	$sql = mysqli_query($con, "select cn_flag from country where cn_id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return $row->cn_flag;
}

function city_to_country($id)    /*  City id  */
{
	global $con;
	$sql = mysqli_query($con, "select cn_name from country,city where ct_cn_id=cn_id and ct_id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return ucwords($row->cn_name);
}

function get_country_phn_code($id)
{
	global $con;
	$sql = mysqli_query($con, "select cn_ph from country where cn_id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return $row->cn_ph;
}

function get_measurement_unit($id)
{
	global $con;
	$sql = mysqli_query($con, "select mu_name from measurement_unit where mu_id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return $row->mu_name;
}


function get_gateway_name($id)
{
	global $con;
	$sql = mysqli_query($con, "select pg_name  from payment_gateway where id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return $row->pg_name ;
}

function get_state_name($id)    
{
	global $con;
	$sql = mysqli_query($con, "select state_name from states where state_id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return ucwords($row->state_name);
}

function get_city_name($id)    
{
	global $con;
	$sql = mysqli_query($con, "select ct_name from city where ct_id = '".$id."'");
	$row = mysqli_fetch_object($sql);
	return ucwords($row->ct_name);
}

function user_designation_name($id)    
{
	global $con;
	$sql = mysqli_query($con, "select * from designation where desig_id = '".$id."' and desig_status = '1'");
	$row = mysqli_fetch_array( $sql);
	return $row['desig_title'];
}

function get_catname($id,$level,$field)    
{
	global $con;
	$sql = mysqli_query($con, "select * from product_category where md5(pc_id) = '".$id."'");
	$row = mysqli_fetch_array( $sql);
	if($level==0)
	{	
		$sqlh = mysqli_query($con, "select * from product_category where pc_id = '".$row['pc_parent_id']."'");
		$rowh = mysqli_fetch_array( $sqlh);
		$pcname=ucwords($rowh[$field]);
	}
	if($level==1)
	{
		$pcname=ucwords($row[$field]);
	}
	return $pcname;
}

function get_payment_terms($id)    
{
	global $con;
	$sqlchk="select * from products where pd_id='".$id."'";
	$reschk=mysqli_query($con, $sqlchk);
	$rowchk=mysqli_fetch_array( $reschk);
	$pdm=$rowchk['pd_payment'];
	
	$sid="(".$pdm.")";
    $sql = "select * from payment_gateway where id in $sid and pg_status = '1'";
	$res = mysqli_query($con, $sql);
	$s="";
	while($row = mysqli_fetch_array( $res))
	{
		$s= $row['pg_name'].",".$s;
	}
	return substr($s,0,-1);
}

function get_product_detail($id,$field)  
{
	global $con;
	global $con;
	$sqlchk="select * from products where pd_id='".$id."'";
	$reschk=mysqli_query($con, $sqlchk);
	$rowchk=mysqli_fetch_array( $reschk);
	
	if($field=='pd_currency')
	{
	$sql = "select * from country where cn_id = '".$rowchk['pd_currency']."'";
	$res = mysqli_query($con, $sql);	
	$row = mysqli_fetch_array( $res);
	$pdm=$row['cn_currency'];
	}
	else if($field=='pd_unit')
	{
	$sql = "select * from measurement_unit where mu_id = '".$rowchk['pd_unit']."'";
	$res = mysqli_query($con, $sql);	
	$row = mysqli_fetch_array( $res);
	$pdm=$row['mu_name'];
	}
	else
	{
	$pdm=$rowchk[$field];	
	}
	return $pdm;
}

function user_info($id,$field)    
{
	global $con;
	$sql="select * from user,business_profile where usr_id=bnsprof_uid and usr_id = '".$id."' and status = '1'";
	$res = mysqli_query($con, $sql);
	$row = mysqli_fetch_array( $res);
	return $row[$field];
}

function get_adminemail()    
{
	global $con;
	$sql = mysqli_query($con, "select * from admin_user where status = '1'");
	$row = mysqli_fetch_array( $sql);
	return $row['email'];
}

function chkloggedin($id)
{
	global $con;
	$sql = mysqli_query($con, "select * from user where usr_id = '".$id."'");
	if(mysqli_num_rows(sql)<1)
	{
		header("location:index.php");
	}
}

function ordinal($num)
{
    // Special case "teenth"
    if ( ($num / 10) % 10 != 1 )
    {
        // Handle 1st, 2nd, 3rd
        switch( $num % 10 )
        {
            case 1: return $num . 'st';
            case 2: return $num . 'nd';
            case 3: return $num . 'rd'; 
        }
    }
    // Everything else is "nth"
    return $num . 'th';
}
function getActiveCountryList()
{
	global $con;
	$sql="select distinct cn_id from country where cn_id in(select distinct country from products,user,business_profile,plan_member_id where pd_uid=usr_id and usr_id=bnsprof_uid and  b_id =bnsprof_id  and expiry_date > " . time() . " and pd_status='1') or cn_id in(select distinct country from buy_requirement,user,business_profile,plan_member_id where br_u_id=usr_id and usr_id=bnsprof_uid and  b_id =bnsprof_id and br_approval_status='1' and br_display_status='1' and expiry_date > " . time() . " and br_status='1') or cn_id in(select distinct country from sale_offer,user,business_profile,plan_member_id where so_usr_id=usr_id and usr_id=bnsprof_uid and b_id =bnsprof_id and so_approval_status='1' and  expiry_date > " . time() . " and DATE_ADD(so_approval_date,INTERVAL so_validity DAY)>=now() and so_status='1')";
	$res=mysqli_query($con, $sql);
	$val="";
	$i=0;
	while($row=mysqli_fetch_object($res))
	{
		if($i>0){	$val.=",";		}
		$val.=$row->cn_id;
		$i++;
	}
	return $val;
}
## function for Home page Banner
function GetHomeBanner($pos,$strconutnry="")
{
	global $con;
	$sqlban = "select * from advertisementhome where adv_position = '".$pos."' and adv_status = '1' $strconutnry order by adv_id DESC";
	$rsquery = mysqli_query($con,$sqlban);
	if(mysqli_num_rows($rsquery) > 0)
	{
		$row = mysqli_fetch_object($rsquery);
	   $adv_img = $row->adv_img;
		$width = $row->adv_imagewidth;
		$height = $row->adv_imageheight;
		$logopath = "upload/advertisementhome/".$adv_img;
		$adv_link = $row->adv_link;
		$banner2ret = '<a href="'.$adv_link.'"><img src="'.$logopath.'" width="100%"></a>';
	}
	else
	{
		$banner2ret = "";
	}
	return $banner2ret;
}
## function for Getting site_settings values
function GettingSite_Setting($st_field)
{
	global $con;
	$sqlban = "select st_value from site_settings where st_field = '".$st_field."' and st_status = '1'";
	$rsquery = mysqli_query($con,$sqlban);
	if(mysqli_num_rows($rsquery) > 0)
	{
		$row = mysqli_fetch_object($rsquery);
	    $st_value = $row->st_value;
	}
	else
	{
		$st_value = "";
	}
	return $st_value;
}
function categoryAdsBanner($strconutnry="",$categoryid="",$supplierid="",$position="")
{
	global $con;
	//echo 'Country=>'.$strconutnry.' CatId=>'.$categoryid.' Sup=>'.$supplierid;exit;
	if($strconutnry != ''){
		$countryCond = " AND (adv_country LIKE '%".$strconutnry."%' OR adv_country LIKE '%,".$strconutnry."%' OR adv_country LIKE '%".$strconutnry.",%')";
	}else{
		$countryCond = "";
	}
	
	if($position != ''){
		$positionCond = " AND adv_position ='".$position."'";
	}else{
		$positionCond = "";
	}
	if($supplierid != ''){
		$sqlban = "SELECT * FROM advertisementcathome WHERE adv_supplier_id = '".$supplierid."' AND adv_status = '1'".$countryCond." ".$positionCond." ORDER BY adv_id DESC";
	}else if($categoryid != ''){
		$sqlban = "SELECT * FROM advertisementcathome WHERE adv_status = '1' AND (adv_cat_id = '".$categoryid."' OR adv_subcat_id = '".$categoryid."' OR adv_subsub_cat_id = '".$categoryid."')".$countryCond."  ".$positionCond." ORDER BY adv_id DESC";
		
	}
	$rsquery = mysqli_query($con,$sqlban);
	if(mysqli_num_rows($rsquery) > 0)
	{
		$row = mysqli_fetch_object($rsquery);
	   	$adv_img = $row->adv_img;
		$width = $row->adv_imagewidth;
		$height = $row->adv_imageheight;
		$logopath = "upload/advertisementcathome/".$adv_img;
		$adv_link = $row->adv_link;
		$banner2ret =  $row->adv_position.'~~<a href="'.$adv_link.'"><img src="'.$logopath.'" width="100%"></a>';
	}
	else
	{
		$banner2ret = "";
	}
	return $banner2ret;
}

function staticAdsBanner($position="")
{
	global $con;
	if($position != ''){
		$positionCond = " AND adv_position ='".$position."'";
	}else{
		$positionCond = "";
	}
		
	$sqlban = "SELECT * FROM advertisement WHERE adv_status = '1'".$positionCond." ORDER BY adv_id DESC";
		
	$rsquery = mysqli_query($con,$sqlban);
	if(mysqli_num_rows($rsquery) > 0)
	{
		$row = mysqli_fetch_object($rsquery);
	   	$adv_img = $row->adv_img;
		$width = $row->adv_imagewidth;
		$height = $row->adv_imageheight;
		$logopath = "http://egyptmart.online/upload/advertisement/".$adv_img;
		$adv_link = $row->adv_link;
		if($position == 'left'){
			$data = getimagesize($logopath);
			$width = $data[0];
			$height = 'height="'.$data[1].'"';
		}else{
			$height = '';
		}
		$banner2ret = '<a href="'.$adv_link.'"><img src="'.$logopath.'" width="100%" '.$height.' style="display:block !important;"></a>';
	}
	else
	{
		$banner2ret = "";
	}
	return $banner2ret;
}
?>