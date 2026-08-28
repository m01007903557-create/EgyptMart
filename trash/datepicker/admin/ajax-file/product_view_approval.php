<?php 
include "../../common.php";
global $con;
$pid = $_GET['id'];
$pd_id=$_GET['id'];
mysqli_query($con, "update products set pd_status='1' where pd_id='".$pid."'");
		
		$get_user = "SELECT * FROM user u LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid WHERE u.usr_id = (SELECT pd_uid FROM products WHERE pd_id = '$pid')";
		$res_user = $con->query($get_user);
		$suser = $res_user->fetch_object();
		$cid=rand(1000,9999).md5($suser->bnsprof_id);
		$contact_details = '<strong>'.$suser->bnsprof_compname.'</strong><br/>'.$suser->bnsprof_address1.'<br/>Mobile/Cell Phone: '.$suser->mobile1.'<br/>Email: '.$suser->email;
		
		//echo $contact_details;
		//echo "<pre>"; print_r($suser);
		$suname = $suser->name_prefix." ".$suser->fname." ".$suser->lname;
		
		$to = $suser->email;
		
		$get_product = "SELECT * FROM products WHERE pd_id = '$pid'";
		$res_product = $con->query($get_product);
		$sproduct = $res_product->fetch_object();
		if($sproduct->pd_image!=''){
			$product_img='<img src="http://arabyos.com/upload/myproduct/'. $sproduct->pd_image.'" width="100" />';
		}
		else{
			$product_img='<img src="http://arabyos.com/upload/myproduct/noimage.jpg" width="100" />';
		}

        $makearr=explode(',', $product_img);
		$product_img = $makearr[0];
		$cid=rand(0001,9999).md5($suser->bnsprof_id);
		//echo $product_img;
		$product_moq = $sproduct->pd_min_order_qty;
		$product_price = $sproduct->pd_fob_price.' ~ '.$sproduct->pd_fob_price2;
		$unitsql=mysqli_query($con, "select * from measurement_unit where mu_status='1' AND mu_id = ".$sproduct->pd_unit);
		while($unitrow=mysqli_fetch_object($unitsql)){
			$product_type = $unitrow->mu_name;
		}
		//echo "<pre>"; print_r($sproduct); exit;
		$product_title = $spname = $sproduct->pd_title;
		/*Put Your Email Adress Here*/
			$subject = "Product Approve from ".get_page_settings(4);
			$from_name = get_page_settings(4);
			$from_email = get_adminemail();
			$message = $suname."<br /><br />"."Your Product <b>".$spname."</b> is approved";
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";
			$usr_email = $to;
		
			//echo $to.'</br/>'.$message; exit;
			include "../email/admin_product_approve.php";
			//echo $message1;
			//exit;
			//$to = 'josyaprabu@gmail.com';
				
			if(mail($to, $subject, $message1, $headers)){
				//exit;
			//header("location:product-view.php");	
				
			include('../../product-email-notification.php');	
				}
			echo true;
?>