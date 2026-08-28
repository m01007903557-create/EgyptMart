<?php
if($new_br_id == ''){
ob_start();
session_start();
include 'common.php';
if(isset($_POST['br_id']))
{
	$new_br_id=$_POST['br_id'];
}
else
if(isset($_GET['br_id']))
{
	$new_br_id=$_GET['br_id'];
}
else
if(isset($_GET['admn_br_id']))
{
	$new_br_id=$_GET['admn_br_id'];
}
else
if(isset($_SESSION['new_br_id']))
{
	$new_br_id=$_SESSION['new_br_id'];
}
}
else{
	$new_br_id=$new_br_id;
}
$sql="select cn_id,cn_name from user,buy_requirement,country where br_u_id = usr_id and country = cn_id and br_id = ".$new_br_id; 
$rs=mysql_query($sql);
$row=mysql_fetch_array($rs);
$cn_name=$row['cn_name'];
$cn_id=$row['cn_id'];
//echo $cn_name;
//Get Data From Database According to location 
$sql_tbi="select * from buy_requirement,user,business_profile
		   where br_preferred_supplier_location = usr_br_prefLocation 
           and br_u_id = usr_id  and usr_id = bnsprof_uid and br_id = ".$new_br_id;
	//echo $sql_tbi;		 
	/*$sql_tbi="select br_id,br_pc_id,br_u_id,br_pd_name,br_requirement,br_estimate_qty,br_preferred_supplier_location,mu_name,pc_name,usr_id,email,usr_br_prefLocation from
           buylead_alert_category,buy_requirement,product_category_arabyos,user,measurement_unit
		   where br_pc_id = bac_pc_id and pc_id = bac_pc_id 
           and bac_usr_id = usr_id and br_preferred_supplier_location = usr_br_prefLocation 
             and br_estimate_qty_unit = mu_id and br_id = ".$new_br_id;		*/ 

$res_tbi=mysql_query($sql_tbi);
     
//Send Email To All Sellers according to location and product	 
        $from_mail=get_adminemail();
		$from_name = get_page_settings(4);
	    $subj= "Latest Buy-lead Trade Alerts On EgyptMART

";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";
		    
    while($row_mpc=mysql_fetch_object($res_tbi)){
	if($row_mpc->br_approval_status==1)
	{
		//print_r($row_mpc);
	$flag=0;
	if($row_mpc->usr_br_prefLocation=="any")
	{
	    $flag=1;
	}
	else
	if($row_mpc->usr_br_prefLocation=="abroad" && $row_mpc->country!=$cn_id)
	{
	   $flag=2;
	}
	else
	if($row_mpc->usr_br_prefLocation=="domestic" && $row_mpc->country==$cn_id)
	{
	   $flag=3;
	}
	else
	if($row_mpc->usr_br_prefLocation=="my_city" && $row_mpc->country==$cn_id)
	{
	   $flag=4;
	}
	/*echo $row_mpc->usr_br_prefLocation."---".$row_mpc->country."-----".$cn_id;
	echo "<br/>".$flag;*/
	if($flag!=0)
	{
	
	
       	$sql_pc="select m.pc_name,c.pc_name,s.pc_name,m.pc_id,c.pc_id,s.pc_id from product_category_arabyos m,product_category_arabyos c,product_category_arabyos s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and s.pc_id='".$row_mpc->br_pc_id."'";
		//echo $sql_pc;
		$res_pc=mysql_query($sql_pc);
		$row_pc=mysql_fetch_array($res_pc);
		
		$comment="<div style='width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;'>";
		$comment.="<div style='height: 100px; width: 100%; float: left; '><div style='height: 100px; width: 30%; float: left;'>";
		$comment.="<img src='http://arabyos.com/images/logo.png' style='width: 100%;color: #00F;font-size: 22px;font-weight: bold;' alt='EgyptMART'>";
        $comment.="</div><div style='height:100px;width:43%;float:left;'><h2 style='font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;'>Today's Latest<br> Buy Requirements</h2></div>";
        $comment.="<div style='min-height: 100px; width: 27%; float: right; padding-top: 3px;'><span style='font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;'> Notification</span><span style='float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;'>".date('M d, Y')."</span></div></div>";
		$comment.="<div style='width:100%;float:left;color:#000000;'><p style='font-size:16px;color:#000000'><strong>Dear ".$row_mpc->name_prefix." ".$row_mpc->fname." ".$row_mpc->lname."</strong>,<br><br>Latest Buy-leads relevant to your subscribed categories on EgyptMART are listed below:</p></div>";
		$comment.="<div  style='height:150px;width:100%;float:left; margin-top:10px;'>";
		if($row_mpc->br_pic =='')
		{
		$comment.="<div style='width:20%;height:100%'><img src='https://www.arabyos.com/upload/myproduct/noimage.jpg' style='height:auto;width:100%;'></div>";
		}
		else
		{
		$comment.="<div style='width:20%;height:100%;display:inline-block;float:left;'><img src='https://www.arabyos.com/upload/buy_requirement/".$row_mpc->br_pic."' style='width:100%;height:auto;'></div>";
		}
		$comment.="<div style='width:70%;height:100%;display:inline-block;margin-left:10px;'>";
		$comment.="<div style='width:90%;font-size:18px;font-weight:bold;color:#466da0;float:left;margin:5px 0;'>".$row_mpc->br_pd_name."</div>";
		$comment.="<div style='width:90%;font-size:14px;float:left;;margin:5px 0;'>Order Value : ".$row_mpc->br_apprx_order_value." ".$row_mpc->br_apprx_order_currency." </div>";
		$comment.="<div style='width:90%;font-size:14px;float:left;;margin:5px 0;'>Quantity : ".$row_mpc->br_estimate_qty." ".measurement_unit($row_mpc->br_estimate_qty_unit)."</div>";
		$comment.="</div></div>";
		
		$comment.="<div style='width:100%;text-align:center;'><a href='http://arabyos.com/buyleads-details.php?id=".rand(1000,9999).md5($row_mpc->br_id)."'>Learn More >></a></div>";
				$comment .= "<div style='float:left;width:100%;'>
<p style='line-height:1.5em;text-align:left;font-size:1.4em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em;color: #002757;'>Not Signed in Before Yet ? .. </p>
<div style='color: #000;'>Use your current mail address + default password: 123456  <a href='http://www.arabyos.com/sign-in.php?email='".stripslashes($row_mpc->email)."' style='text-decoration:none;margin-left: 50px;font-size: 18px;'>Sign in NOW</a></div>
<p style='text-align:center;margin-bottom: 0;font-size: 19px;'> <u>Post to <b>Domestic</b> or <b>Global</b> markets <b style='color:#da4e1e;'>FREE</b> :</u></p>";
$comment .= "<table align='center'>";
 $comment .= "<tr>";
  $comment .= "<th style='padding-top: 5px;'><a href='http://www.arabyos.com/dir.php' style='text-decoration:none;'>- Products / Services</a> </th>";
    $comment .= "<th  style='padding-top: 5px;'><a href='http://www.arabyos.com/post-buy-req.php' style='padding-left: 40px;text-decoration:none;'>- Buy Requirements</a></th>";
    
   $comment .= "</tr>";
   $comment .= "<tr>";
    $comment .= "<th  style='padding-top: 5px;'><a href='http://www.arabyos.com/post-sell-offer.php' style='text-decoration:none;padding-left: 13px;'> - Temporary Sale Offer</a></th>";
     $comment .= "<th  style='padding-top: 5px;'> <a href='http://www.arabyos.com/tenders.php' style='padding-left: 40px;text-decoration:none;'>-Tenders /</a><a href='http://www.arabyos.com/auctions.php' style='text-decoration:none;'> Auctions</a></th>";
   $comment .= "</tr>";
 $comment .= "</table>";
 $comment .= '<br><br>';
 $comment .= '<table>';
  $comment .= '<tr>';
     $comment .= "<th><a href='http://www.arabyos.com/product-sel-cat.php' style='font-size: 10px;color: #000;'>Get Globel & Domestic Enquuries</a></th>";
     $comment .= "<th><a href='#' style='font-size: 10px;color: #000;'>Get timely products updates to inbox </a></th>";
     $comment .= "<th><a href='http://www.arabyos.com/product-list.php' style='font-size: 10px;color: #000;'>Manage your listed products / services </a></th>";	
   $comment .= "</tr>";
 $comment .= "</table>";
 $comment .= '</div>';
               $comment .= "<div style='clear:both'>";
                                                            
               $comment .= '</div><br>';
              
              
  
              $comment .= "<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";
               $comment .= "<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'>";
                  $comment .= "<a href='http://arabyos.com/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Product &amp; Suppliers</a> | <a href='http://arabyos.com/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Sale Offers</a> | <a href='http://arabyos.com/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Buy Requests</a> | <a href='http://arabyos.com/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Tenders</a>| <a href='http://arabyos.comauctions.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Auction</a>";
               $comment .= '</div>';
					 $comment .= '<span>For any assistance , feel free to call us at +201220974444 or just reply to this mail.</span></br>';
					 $comment .= "<span><a href='mailto:membership@arabyos.com'>mailto:membership@arabyos.com</a></span>";
					 $comment .= "<table align='center'><tr>";
     $comment .= "<th><span style='color:#808080'>Warm Regards,</span></th>";
    
   $comment .= '</tr>';
  $comment .= '<tr>';
     $comment .= "<th><span><a href='#' style='text-decoration: none;font-size: 15px;'>EgyptMART Team</a></span></th>";
    
   $comment .= '</tr>';
   $comment .= '<tr>';
     $comment .= "<th><span style='color: #000;font-size: 18px;'><u>We Promote Your Business!</u></span></th>";
   
   $comment .= '</tr>';
  
 $comment .= '</table></div>';
		/*$comment.="<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";
		
		$comment.="<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'><a href='http://arabyos.com/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Product & Suppliers</a> | <a href='http://arabyos.com/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Sale Offers</a> | <a href='http://arabyos.com/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Buy Requests</a> | <a href='http://arabyos.com/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Tenders</a>| <a href='http://arabyos.com/auctions.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Auction</a></div>";
		$comment.="<div style='width:100%;padding-left: 0px;float:left;color:#808080;'><p style='margin:10px 0px 2px'>You have recived this mail virtue of your opt-in subscription for product alert on <font style='color:blue;'>EgyptMART</font>.</p><p style='color:#808080; margin:0px 0px 20px;'><a href='http://www.arabyos.com/manage-buylead-alert.php' style='text-decoration:none;color:blue;'>Click here</a> if you wish to modify to your buy requirement alert categories.</p></div>";*/
		
			$comment.="</div>";
		
       	$to=stripslashes($row_mpc->email);  
		//$to="programmer6techy@gmail.com";  
		mail($to,$subj,$comment,$headers);	
		//End Mail
		
        //Insert in message table 	
  	
        $sql='insert into message set	
		msg_from ="'.$row_mpc->br_u_id.'",
		msg_to ="'.$row_mpc->usr_id.'",
		msg_subject ="'.$subj.'",
		msg_message ="'.$comment.'",
		msg_to_status ="1",
		msg_from_status ="0",
		msg_date =now()';	
        mysql_query($sql);
		//End Inserting in message table
		}
		}
	}
	if(isset($_GET['br_id']))
	{
	header("location:admin/buyreq-view.php");
	}
	else
	if(isset($_GET['admn_br_id']))
	{
	header("location:admin/buyreq-edit.php?token=".md5($_GET['admn_br_id']));
	}
	else
	{
	if(isset($_SESSION['new_br_id']))
{	
	header("Location:post-buy-req-res.php");
	}
	}
?>