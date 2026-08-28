<?php
include "../common.php";

$keywords =  $_REQUEST['keywords'];
$rctype =  $_REQUEST['rctype'];

$q=$_GET['q'];
$my_data=mysql_real_escape_string($q);

if($_GET['rctyp'] == 'buy_lead'){ 
			 $view_country = "select  DISTINCT city.ct_name  from buy_requirement,measurement_unit,city,country where br_estimate_qty_unit=mu_id ".$keywords_string."  and business_profile.bnsprof_city = city.ct_id and city.ct_name LIKE '".$my_data."%' and br_display_status='1' and br_approval_status='1' group by br_u_id";
	//echo $view_country; 	
		} 
		else if($_GET['rctyp'] == 'tender'){
			$tender_keywords_string = generateTenderSearchString($keywords);
			$auction_keywords_string = generateAuctionSearchString($keywords);
			
			if(isset($_COOKIE['loc_id'])){
				$tenderCondition = " AND ((tnd_preferred_location='domestic' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='".$_COOKIE['loc_id']."')) OR (tnd_preferred_location='any' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='".$_COOKIE['loc_id']."')) OR (tnd_preferred_location='my_city' AND tnd_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='".$_COOKIE['loc_id']."'))))";
			}else{
				$tenderCondition = " AND ((tnd_preferred_location='domestic') OR (tnd_preferred_location='any') OR (tnd_preferred_location='my_city'))";
			}
			
			if(isset($_COOKIE['loc_id'])){
				$auctionCondition = " AND ((auc_preferred_location='domestic' AND auc_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='".$_COOKIE['loc_id']."')) OR (auc_preferred_location='any' AND auc_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='".$_COOKIE['loc_id']."')) OR (auc_preferred_location='my_city' AND auc_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='".$_COOKIE['loc_id']."'))))";
			}else{
				$auctionCondition = " AND ((auc_preferred_location='domestic') OR (auc_preferred_location='any') OR (auc_preferred_location='my_city'))";
			}
			
			$view_country = "select DISTINCT  city.ct_name from tender,city,product_category,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id  and business_profile.bnsprof_city = city.ct_id and city.ct_name LIKE '".$my_data."%' and usr_id=bnsprof_uid AND (".$tender_keywords_string.") and tnd_due_date>='".date('Y-m-d')."' AND tnd_approval_status = '1'  ".$tenderCondition." group by tender.tnd_currency ORDER BY tnd_id desc ";//and TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) 
			
			$view_country_auction = "select * from auction,product_category,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid AND (".$auction_keywords_string.") and auc_due_date>='".date('Y-m-d')."' AND auc_approval_status = '1'  ".$auctionCondition." group by auction.auc_currency ORDER BY auc_id desc ";//and TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) 
			
		}else if($_GET['rctyp'] == 'Suppliers'){
			$keywords_string=generateSupplierSearchString($_GET['keywords']);
			if(isset($_COOKIE['loc_id'])){
				$view_country = "select  DISTINCT city.ct_name  from products,measurement_unit,country,business_profile,city,user,plan_member_id where usr_id = pd_uid AND bnsprof_uid = pd_uid AND b_id =business_profile.bnsprof_id  and business_profile.bnsprof_city = city.ct_id and city.ct_name LIKE '".$my_data."%' AND mu_id=pd_unit AND (bnsprof_compname LIKE ".$keywords_string.") and pd_currency=cn_id and ((pd_preferred_buyer_location =  'domestic' AND user.country =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'any' AND user.country =  '".$_COOKIE['loc_id']."') OR (pd_preferred_buyer_location =  'my_city' AND user.country =  '".$_COOKIE['loc_id']."')) AND expiry_date > ". time() ." and pd_status='1' and pd_image!='' group by pd_currency";
			}else{
				$view_country = "select DISTINCT  city.ct_name   from products,measurement_unit,country,business_profile,city,user,plan_member_id where usr_id = pd_uid AND bnsprof_uid = pd_uid AND b_id =business_profile.bnsprof_id  and business_profile.bnsprof_city = city.ct_id and city.ct_name LIKE '".$my_data."%' AND mu_id=pd_unit AND (bnsprof_compname LIKE ".$keywords_string.") and pd_currency=cn_id and ((pd_preferred_buyer_location =  'domestic') OR (pd_preferred_buyer_location =  'any') OR (pd_preferred_buyer_location =  'my_city')) AND expiry_date > ". time() ."  and pd_status='1' and pd_image!='' group by pd_currency";
			}
	  }else { 
		  $newkw=generateProdSearchString($_GET['keywords']);	
		  if($_GET['idd']!=""){
			  $view_country="select DISTINCT city.ct_name   from products,measurement_unit,country,city,business_profile, plan_member_id where mu_id=pd_unit  and business_profile.bnsprof_uid = products.pd_uid and business_profile.bnsprof_city = city.ct_id and city.ct_name LIKE '".$my_data."%'  and b_id = bnsprof_id AND plan_member_id.expiry_date > ". time() ." and pd_subcat_id = ".$_GET['idd']." and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' group by pd_currency ";
		 }else{
			 $view_country = "select DISTINCT  city.ct_name    from products,measurement_unit,country,city,business_profile, plan_member_id where mu_id=pd_unit and business_profile.bnsprof_uid = products.pd_uid and business_profile.bnsprof_city = city.ct_id and city.ct_name LIKE '".$my_data."%' and b_id = bnsprof_id AND plan_member_id.expiry_date > ". time() ." and (pd_title LIKE ".$newkw.") and pd_currency=cn_id ".$sql_pd_ck." and pd_status='1' and pd_image!='' group by pd_id";
			//echo $view_country;
				
		}
	}
			//echo $view_country;exit;
			//echo $data['country']; die;
		 $run_sql= mysql_query($view_country, $link);
		 
		
   while( $row11=mysql_fetch_array($run_sql, MYSQL_ASSOC))
		{
			
			echo $row11['ct_name'];	
				
	}

function generateProdSearchString($keywords)
{
	//echo 'product searching';
	
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or pd_title LIKE ";
		}
		$keywords_string=$keywords_string."'%".$v."%'";
		$i++;
	}
	return $keywords_string;
}
function generateSupplierSearchString($keywords)
{
	//echo 'supllier searching';

	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or bnsprof_compname LIKE ";
		}
		$keywords_string=$keywords_string."'%".$v."%'";
		$i++;
	}
	return $keywords_string;
}
function generateBuyleadSearchString($keywords)
{
	//echo 'lead searching';
	
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or br_pd_name LIKE '%".$v."%' or br_requirement LIKE '%".$v."%'";
		}
		else
		{
			$keywords_string=$keywords_string."br_pd_name LIKE '%".$v."%' or br_requirement LIKE '%".$v."%'";
		}
		$i++;
	}
	return $keywords_string;
}
function generateTenderSearchString($keywords)
{
	//echo 'tender searching';
	
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or tnd_heading LIKE '%".$v."%' or tnd_details LIKE '%".$v."%'";
		}
		else
		{
			$keywords_string=$keywords_string."tnd_heading LIKE '%".$v."%' or tnd_details LIKE '%".$v."%'";
		}
		$i++;
	}
	return $keywords_string;
}

function generateAuctionSearchString($keywords)
{
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or auc_heading LIKE '%".$v."%' or auc_details LIKE '%".$v."%'";
		}
		else
		{
			$keywords_string=$keywords_string."auc_heading LIKE '%".$v."%' or auc_details LIKE '%".$v."%'";
		}
		$i++;
	}
	return $keywords_string;
}
