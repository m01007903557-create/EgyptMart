<?php
if($auc_id == ''){
ob_start();
session_start();
include 'common.php';
}
if(isset($_GET['auc_id']))
{
$auc_id=$_GET['auc_id'];
}
else
if(isset($_GET['admn_auc_id']))
{
$auc_id=$_GET['admn_auc_id'];
}
else
if(isset($_POST['auc_id']))
{
$auc_id=$_POST['auc_id'];
}

$sql="select cn_id,cn_name,fname,lname,state_name,ct_name from user,auction,country,states,city where auc_usr_id = usr_id and country = cn_id and state_id = ct_state and state_cn_id = cn_id  and auc_id = ".$auc_id; 
$rs=mysql_query($sql);
$row=mysql_fetch_array($rs);
$cn_name=$row['cn_name'];
$cn_id=$row['cn_id'];
$ct_name=$row['ct_name'];
$state_name=$row['state_name'];
$auct_id=rand(0001,9999).md5($auc_id);
//Get Data From Database According to location 
$sql_tbi = "select * 
from auction_alert_category,auction,user,business_profile
 where auc_pc_id = aac_pc_id and aac_usr_id = usr_id 
  and usr_auc_prefLocation = auc_preferred_location and usr_id=bnsprof_uid and auc_id = ".$auc_id;


$res_tbi=mysql_query($sql_tbi);
     
//Send Email To All Sellers according to location and product	 

        $from_mail=get_adminemail();
		$from_name = get_page_settings(4);
	    $subj="Latest Auction Alert From EgyptMART";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";
		    
    while($row_mpc=mysql_fetch_object($res_tbi)){
	if($row_mpc->auc_approval_status==1)
	{
	$flag=0;
	if($row_mpc->usr_auc_prefLocation=="any")
	{
	    $flag=1;
	}
	else
	if($row_mpc->usr_auc_prefLocation=="abroad" && $row_mpc->country!=$cn_id)
	{
	   $flag=2;
	}
	else
	if($row_mpc->usr_auc_prefLocation=="domestic" && $row_mpc->country==$cn_id)
	{
	   $flag=3;
	}
	else
	if($row_mpc->usr_auc_prefLocation=="my_city" && $row_mpc->country==$cn_id)
	{
	   $flag=4;
	}
	/*echo $flag;
	echo $row_mpc['usr_auc_prefLocation']."-------".$row_mpc['country']."=======".$cn_id;*/
	if($flag!=0)
	{
	$contact_details = '<strong>'.$row_mpc->bnsprof_compname.'</strong><br/>'.$row_mpc->bnsprof_address1.'<br/>Mobile/Cell Phone: '.$row_mpc->mobile1.'<br/>Email: '.$row_mpc->email;
		
	  $sql_pc="select m.pc_name,c.pc_name,s.pc_name,m.pc_id,c.pc_id,s.pc_id from product_category m,product_category c,product_category s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and s.pc_id='".$row_mpc->auc_pc_id."'";
			$res_pc=mysql_query($sql_pc);
			$row_pc=mysql_fetch_array($res_pc);
		
	 
	  $comment="<div style='width: 680px;height: auto;border: 9px solid #92AED2;float: left;padding: 10px;margin-top:10px;'>";
		$comment.="<div style='height: 100px; width: 100%; float: left; '><div style='height: 100px; width: 30%; float: left;'>";
		$comment.="<img src='http://arabyos.com/images/logo.png' style='width:100%;' alt='EgyptMART'>";
        $comment.="</div><div style='height:100px;width:43%;float:left;'><h2 style='font-size: 20px; color:#466da0; text-align:center; margin-top:0px; margin-bottom:0px;'>Today's Latest<br> Auction</h2> Alert</div>";
       $comment.="<div style='min-height: 100px; width: 27%; float: right; padding-top: 3px;'><span style='font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;'> Notification</span><span style='float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;'>".date('M d, Y')."</span></div></div>";
		$comment.="<div style='width:100%;float:left;color:#000000;'><p style='font-size:16px;color:#000000'><strong>Dear ".$row_mpc->name_prefix." ".$row_mpc->fname." ".$row_mpc->lname."</strong>,<br><br>Latest Products relevant to your subscribed categories on EgyptMART are listed below:</p></div>";
		 $comment.="<div style='height:auto;width:100%;float:left;margin-top:10px;'>";
		$comment.="<div style='height:auto;width:100%;float:left;'>";
		$comment.="<div style='width:100%'>
		<div style='width:100%'><h3 style='font-size:18px;'>".$row_mpc->auc_heading."</h3></div>
		<div style='width:100%;margin-top:10px'> <div style='display:inline-block;padding-left:0px;width:30%'>Quantity<span style='padding-left:20px;'>:</span></div>
					 <div style='color:#e9582c;display:inline-block'>".$row_mpc->auc_qty."</div>
					 <div style='color:#000;display:inline-block'>".measurement_unit($row_mpc->auc_qty_mu_id)."</div>
					 </div>
					 <div style='width:100%;margin-top:10px'>
					 <div style='padding-left:0px;display:inline-block;width:30%'>Price<span style='padding-left:20px;'>:</span></div>
					 <div style='color:#e9582c;font-weight:bold;font-size:15px;line-height:15px;display:inline-block;'>".$row_mpc->auc_value."</div>
					 <div style='color:#000;padding-left:5px;display:inline-block;'>".getCurrency($row_mpc->auc_currency)."</div>
					 </div>
					 <div style='width:100%;margin-top:10px'>
					 <div style='padding-left:0px;display:inline-block;width:30%'>Due Date<span style='padding-left:20px;'>:</span></div>
					 <div style='color:#e9582c;font-weight:bold;font-size:15px;line-height:15px;display:inline-block;'>".date('d M, Y',strtotime($row_mpc->auc_due_date))."</div>
					 <div style='color:#000;padding-left:5px;display:inline-block;'></div>
					 </div>
					 <div style='width:100%;margin-top:10px'>
					 <div style='padding-left:0px;display:inline-block;width:30%'>Project Period<span style='padding-left:20px;'>:</span></div>
					 <div style='color:#e9582c;font-weight:bold;font-size:15px;line-height:15px;display:inline-block;'>".$row_mpc->auc_project_period."</div>
					 <div style='color:#000;padding-left:5px;display:inline-block;'></div>
					 </div>
		</div>
			
		</div>";
		$comment.="<div style='width:100%;font-size:12px;font-weight:bold;text-align:center;padding-top:15px;'>
					 <a href='https://www.arabyos.com/auction-details.php?id=$auct_id' style='text-decoration:none;color:#466da0;'>Learn More >> </a>
				 </div>";
		$comment.="<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";
		$comment.="<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'><a href='http://arabyos.com/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Product & Suppliers</a> | <a href='http://arabyos.com/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Sale Offers</a> | <a href='http://arabyos.com/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Buy Requests</a> | <a href='http://arabyos.com/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Tenders</a></div>";
		$comment.="<div style='width:100%;padding-left: 0px;float:left;color:#808080;text-align: center;'><p style='margin:10px 0px 2px'>You have recived this mail virtue of your opt-in subscription for product alert on <font style='color:blue;'>EgyptMART</font>.</p><p style='color:#808080; margin:0px 0px 20px;'><a href='http://arabyos.com/manage-auction-alert.php' style='text-decoration:none;color:blue;'>Click here</a> if you wish to modify to your product alert categories.</p></div>";
			$comment.="</div>";
		
		/* for inbox store */
		$inbox="<div style='width: 680px;height: auto;border: 9px solid #92AED2;float: left;padding: 10px;margin-top:10px;'>";
		$inbox.="<div style='height: 100px; width: 100%; float: left; '><div style='height: 100px; width: 30%; float: left;'>";
		$inbox.="<img src='http://arabyos.com/images/logo.png' style='width:100%;' alt='EgyptMART'>";
        $inbox.="</div><div style='height:100px;width:43%;float:left;'><h2 style='font-size: 20px; color:#466da0; text-align:center; margin-top:0px; margin-bottom:0px;'>Today's Latest<br> Auction</h2> Alert</div>";
       $inbox.="<div style='min-height: 100px; width: 27%; float: right; padding-top: 3px;'><span style='font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;'> Notification</span><span style='float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;'>".date('M d, Y')."</span></div></div>";
		$inbox.="<div style='width:100%;float:left;color:#000000;'><p style='font-size:16px;color:#000000'><strong>Dear ".$row_mpc->name_prefix." ".$row_mpc->fname." ".$row_mpc->lname."</strong>,<br><br>Latest Products relevant to your subscribed categories on EgyptMART are listed below:</p></div>";
		$inbox.="<div style='height:auto;width:100%;float:left;margin-top:10px;'>";
		$inbox.="<div style='height:auto;width:100%;float:left;'>";$inbox.="<div style='width:100%'><div style='width:100%'><h3 style='font-size:18px;'>".$row_mpc->auc_heading."</h3></div><div style='width:100%;margin-top:10px'> <div style='display:inline-block;padding-left:0px;width:30%'>Quantity<span style='padding-left:20px;'>:</span></div><div style='color:#e9582c;display:inline-block'>".$row_mpc->auc_qty."</div><div style='color:#000;display:inline-block'>".measurement_unit($row_mpc->auc_qty_mu_id)."</div></div><div style='width:100%;margin-top:10px'><div style='padding-left:0px;display:inline-block;width:30%'>Price<span style='padding-left:20px;'>:</span></div><div style='color:#e9582c;font-weight:bold;font-size:15px;line-height:15px;display:inline-block;'>".$row_mpc->auc_value."</div><div style='color:#000;padding-left:5px;display:inline-block;'>".getCurrency($row_mpc->auc_currency)."</div></div><div style='width:100%;margin-top:10px'><div style='padding-left:0px;display:inline-block;width:30%'>Due Date<span style='padding-left:20px;'>:</span></div><div style='color:#e9582c;font-weight:bold;font-size:15px;line-height:15px;display:inline-block;'>".date('d M, Y',strtotime($row_mpc->auc_due_date))."</div><div style='color:#000;padding-left:5px;display:inline-block;'></div></div><div style='width:100%;margin-top:10px'><div style='padding-left:0px;display:inline-block;width:30%'>Project Period<span style='padding-left:20px;'>:</span></div><div style='color:#e9582c;font-weight:bold;font-size:15px;line-height:15px;display:inline-block;'>".$row_mpc->auc_project_period."</div><div style='color:#000;padding-left:5px;display:inline-block;'></div></div></div></div>";
		$inbox.="<div style='width:100%;font-size:12px;font-weight:bold;text-align:center;padding-top:15px;'><a href='https://www.arabyos.com/auction-details.php?id=$auct_id' style='text-decoration:none;color:#466da0;'>Learn More >> </a></div>";
		$inbox.="<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";
		$inbox.="<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'><a href='http://arabyos.com/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Product & Suppliers</a> | <a href='http://arabyos.com/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Sale Offers</a> | <a href='http://arabyos.com/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Buy Requests</a> | <a href='http://arabyos.com/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Tenders</a></div>";
		$inbox.="<div style='width:100%;padding-left: 0px;float:left;color:#808080;text-align: center;'><p style='margin:10px 0px 2px'>You have recived this mail virtue of your opt-in subscription for product alert on <font style='color:blue;'>EgyptMART</font>.</p><p style='color:#808080; margin:0px 0px 20px;'><a href='http://arabyos.com/manage-auction-alert.php' style='text-decoration:none;color:blue;'>Click here</a> if you wish to modify to your product alert categories.</p></div>";
			$inbox.="</div>";
		/* for inbox store */
       $to=stripslashes($row_mpc->email);  
		//$to='programmer5.techybirds@gmail.com';
		if(mail($to,$subj,$comment,$headers)){
		//End Mail
		
        //Insert in message table 	
  		
       $sql='insert into message set	
		msg_from ="'.$row_mpc->auc_usr_id.'",
		msg_to ="'.$row_mpc->usr_id.'",
		msg_subject ="'.$subj.'",
		msg_message ="'.$inbox.'",
		msg_to_status ="1",
		msg_from_status ="0",
		msg_date =now()';	
        mysql_query($sql) or die(mysql_error());
		//End Inserting in message table
		}
		}
		}
	}	
	if(isset($_GET['auc_id']))
	{
	//header("Location:post-auction.php");
	header("location:admin/auction-view.php");
	}
	else
 	if(isset($_GET['admn_auc_id']))
	{
		header("location:admin/auction-edit.php?token=".md5($_GET['admn_auc_id']));
	}
?>