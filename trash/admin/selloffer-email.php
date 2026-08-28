<?php 
if($so_id == ''){
ob_start();
session_start();
include 'common.php';

if(isset($_POST['so_id']))
{
$so_id=$_POST['so_id'];
}
else if(isset($_GET['so_id']))
{
$so_id=$_GET['so_id'];
}
else if(isset($_GET['admn_so_id']))
{
$so_id=$_GET['admn_so_id'];
}
}
 $sql="select cn_id,cn_name,fname,lname from user,sale_offer,country where so_usr_id = usr_id and country = cn_id and so_id = ".$so_id; 

$rs=mysql_query($sql);
$row=mysql_fetch_array($rs);
$cn_name=$row['cn_name'];
$cn_id=$row['cn_id'];
//Get Data From Database According to location 

 //Here we check same category and same LP
 
 $sql_tbi = "select *
 from selloffer_alert_category,sale_offer,user,business_profile
 where so_pc_id = sac_pc_id and sac_usr_id = usr_id and so_preferred_buyer_location = usr_so_prefLocation and usr_id=bnsprof_uid and so_id = ".$so_id;
 /*$sql_tbi = "select *
 from sale_offer,user,business_profile
 where so_usr_id = usr_id and so_preferred_buyer_location = usr_so_prefLocation and usr_id=bnsprof_uid and so_id = ".$so_id;*/
//echo $sql_tbi;
$res_tbi=mysql_query($sql_tbi);
   //echo $res_tbi;
//Send Email To All Sellers according to location and product	 
        $from_mail=get_adminemail();
		$from_name = get_page_settings(4);
	    $subj="Latest Sell Offer Approve From ARABYOS";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";
		    
    while($row_mpc=mysql_fetch_object($res_tbi))
	{
	
		
		$contact_details = '<strong>'.$row_mpc->bnsprof_compname.'</strong><br/>'.$row_mpc->bnsprof_address1.'<br/>Mobile/Cell Phone: '.$row_mpc->mobile1.'<br/>Email: '.$row_mpc->email;
	//echo $row_mpc->so_approval_status;
	if($row_mpc->so_approval_status==1)
	{
	$flag=0;
	 //Here we check location of  both seller and buyer according to LP
	if($row_mpc->usr_so_prefLocation=="any")
	{
	    $flag=1;
	}
	else
	if($row_mpc->usr_so_prefLocation=="abroad" && $row_mpc->country!=$cn_id)
	{
	   $flag=2;
	}
	else
	if($row_mpc->usr_so_prefLocation=="domestic" && $row_mpc->country==$cn_id)
	{
	   $flag=3;
	}
	else
	if($row_mpc->usr_so_prefLocation=="my_city" && $row_mpc->country==$cn_id)
	{
	   $flag=4;
	}
	}
	//if($flag!=0)
	//{
	
		$comment="<div style='width: 45%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;'>";
		$comment.="<div style='height: 100px; width: 100%; float: left; '><div style='height: 100px; width: 30%; float: left;'>";
		$comment.="<img src='http://arabyos.com/images/logo.png' style='width: 100%;color: #00F;font-size: 22px;font-weight: bold;' alt='ARABYOS'>";
        $comment.="</div><div style='height:100px;width:43%;float:left;'><h2 style='font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;'>Today's Latest<br>  Sale Offer</h2></div>";
        $comment.="<div style='min-height: 100px; width: 27%; float: right; padding-top: 3px;'><span style='font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;'> Notification</span><span style='float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;'>".date('M d, Y')."</span></div></div>";
		$comment.="<div style='width:100%;float:left;color:#000000;'><p style='font-size:16px;color:#000000'><strong>Dear ".$row_mpc->name_prefix." ".$row_mpc->fname." ".$row_mpc->lname."</strong>,<br><br>Latest Selloffer relevant to your subscribed categories on ARABYOS are listed below: </p></div>";
		
		$comment.="<div  style='width:100%;float:left;'>";
		
		$comment.="<table align='center' border='0' cellpadding='0' cellspacing='0' width='100%'><tbody>";
		$comment.="<tr><td>";
		$comment.="<table class='mpr10' align='CENTER' border='0' cellpadding='0' cellspacing='0' width='100%'>";

      
		$comment.="<tbody>";
 
         $comment.="<tbody><tr>";
            $comment.="<td valign='BOTTOM' width='140'>";
            $comment.="<img src='http://arabyos.com/images/zero_002.gif' height='6' width='150'></td></tr>";
        $comment.="</tbody></table>";
        $comment.="<table style='border-collapse:collapse' border='1' bordercolor='#CCEEFF' cellpadding='0' cellspacing='0' width='100%'>";
	$comment.="<tbody><tr>";
	$comment.="<td colspan='4' bgcolor='#DFF2FF' height='25'>";
	$comment.="<div class='ofdt4'><font style='padding-left: 11px;font-size:18px;font-weight:bold;color:#466da0;'> ".$row_mpc->so_service."</font></div></td>";
	$comment.="</tr>";

  $comment.="<tr>
        <td class='ofdt5' align='CENTER' height='25'><b>Offer Type</b></td>
        <td class='ofdt5' align='CENTER'><b>Original Posting Date</b></td>
		<td class='ofdt5' align='CENTER'><b>Updated/Refreshed Date</b></td>
        <td class='ofdt5' align='CENTER'><b>Expiry Date</b></td>
	</tr>";
     $comment.="<tr>
        <td class='o-testrd' align='CENTER' height='25'> Sell</td>";
    $comment.="<td class='o-testrd' align='CENTER' height='25'> ".date('d M Y',strtotime($row_mpc->so_posting_date))."</td>";
	$comment.="<td class='o-testrd' align='CENTER' height='25'> ".date('d M Y',strtotime($row_mpc->so_updated_date))."</td>";
        $comment.="<td class='o-testrd' align='CENTER' height='25'> ".date('d M Y', strtotime($row_mpc->so_posting_date." +".$row_mpc->so_validity." day"))."</td>";
     $comment.="</tr>
    </tbody></table><br><table class='td-padd' style='border-collapse: collapse;' align='left' border='0' bordercolor='#cceeff' cellpadding='0' cellspacing='0' width='100%'>
       <tbody><tr><td class='adss' style='border-top: 0px none;'><img src='http://arabyos.com/images/zero.gif' height='1' width='160'></td><td width='100%'></td>     </tr><tr><td class='ofdt1' align='RIGHT' bgcolor='#F1F5FE'><b>Offer Description</b></td>";
       $comment.="<td style='padding-left: 10px;' bgcolor='#F6FDFF' height='38'> ".stripslashes($row_mpc->so_description)."</td>";
	$comment.="</tr><tr>
       	<td class='ofdt1' align='RIGHT' bgcolor='#F1F5FE'><b>Location Preference</b></td>
        <td style='padding-left: 10px;' bgcolor='#F6FDFF' height='38'> ";
		
		if($row_mpc->so_preferred_buyer_location=='any')
		{
			$comment.="Anywhere";	
		}
    	else if($row_mpc->so_preferred_buyer_location=='abroad')
		{
			$comment.="Foreign";	
		}
		else if($row_mpc->so_preferred_buyer_location=='domestic')
		{
			$comment.=get_country_name($row_mpc->country);	
		}
		else if($row_mpc->so_preferred_buyer_location=='my_city' && $row_mpc->bnsprof_city!='0')
		{
			$comment.=get_city_name($row_mpc->bnsprof_city);
		}
		$comment.="</td></tr><tr><td class='ofdt1' align='RIGHT' bgcolor='#F1F5FE'><b>Offer Validity</b></td><td style='padding-left: 10px;' bgcolor='#F6FDFF' height='38'> ";
    
    if($row_mpc->so_validity=='365')
	{
		$comment.="1 year";	
	}
	else if($row_mpc->so_validity=='90')
	{
		$comment.="3 months";	
	}
	else if($row_mpc->so_validity=='30')
	{
		$comment.="1 month";	
	}
	$comment.="</td></tr>";
         if($row_mpc->so_pic !=''){
        $comment.="<tr>
        <td bgcolor='#F1F5FE'>

            <div class='ofdt1' align='right'><b>Product Photo</b></div></td>
            <td bgcolor='#f6fdff'><form style='margin:0px;'>
        <table style='border-collapse:collapse;' border='0' bordercolor='#F0F9FF' cellpadding='4' cellspacing='0'>
	<tbody><tr><th valign='MIDDLE' width='33%'>
    
			<div style='padding-left:18px;padding-top:5px;'>

			<div style='border:1px solid #71A3C5;background:#FFFFFF;cursor:pointer;'>";
            
			$comment.="<img src='http://arabyos.com/upload/sale_offer/".$row_mpc->so_pic."' id='6390059595_1' border='0' height='auto' hspace='0' vspace='0' width='125'></div>";
		
			$comment.="<div id='6390059595_1_H' vspace='0' hspace='0' style='display:none;position:absolute;top:0;left:0;width:0;height:0;background:#FFFFFF;' height='90'>
			</div>
		
			</div>
	
			</th>
            <th valign='MIDDLE' width='33%'></th><th valign='MIDDLE' width='33%'></th></tr></tbody></table></form></td>
      </tr>";
	} 
    $comment.="          
	</tbody></table>
</td></tr>
</tbody></table>";

		// $comment.="</td></tr></tbody></table>";
		
		$comment.="</div>";
		$comment1=$comment;
		$comment.="<div style='height:auto;width:100%;float:left;font-size:12px;font-weight:bold;text-align:center;padding-top:15px;padding-right:0px;'><a href='http://arabyos.com/saleoffer-details.php?id=".rand(1000,9999).md5($row_mpc->so_id)."' style='text-decoration:none;color:#466da0;padding-right: 2px;'>Learn More >> </a></div>";
		$comment.='<div style="width:100%;height:auto;float:right;"><p style="line-height:1.5em;text-align:left;font-size:1.4em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em;color: #002757;">Contact Details :</p></div>';
		$comment.='<div style="width: 100%; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                '.$contact_details.'
<div style="width: 90%; float: left;">
<span style="font-size:1.0em;font-weight:normal"></span>
</div>
</div>';
	$comment.='<div>
			  <p style="color: #000000;font-size: 19px;font-weight: 900;background-color: #eaeaea;padding: 10px;  margin-bottom: 5px;"> You may need to post <strong style="color:#da4e1e;font-size: 19px;font-weight: 900">FREE</strong>:</p>
			  <table align="center">
			  <tr>
			  <td style="padding: 5px;"><a href="http://www.arabyos.com/sign-in.php?email='.$row_mpc->email.'&redirect=http://www.arabyos.com/product-sel-cat.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Products/Services</a></td>
			  <td style="padding: 5px;"><a href="http://www.arabyos.com/sign-in.php?email='.$row_mpc->email.'&redirect=http://www.arabyos.com/post-buy-req.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Buy Requirments</a></td>			  
			  </tr>
			  <tr>
			  <td style="padding: 5px;"><a href="http://www.arabyos.com/sign-in.php?email='.$row_mpc->email.'&redirect=http://www.arabyos.com/post-sell-offer.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Temporary Sale Offer</a></td>
			  <td style="padding: 5px;"><a href="http://www.arabyos.com/sign-in.php?email='.$row_mpc->email.'&redirect=http://www.arabyos.com/post-tender.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Tenders / Auctions</a></td>			  
			  </tr>
			 
			  </table>
			  </div>';
		$comment.=' <div style="clear:both">
                    <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em"><a href="http://www.arabyos.com/sign-in.php?email='.$row_mpc->email.'&redirect=http://www.arabyos.com/why_ARABYOS.php" style="font-size: 12px;font-weight: normal;">Click Here</a> to Unsubscribe  or Tell us your requirements  <a href="http://www.arabyos.com/sign-in.php?email='.$row_mpc->email.'&redirect=http://www.arabyos.com/membership_plans.php"><strong style="color: #0f00d0;font-size: 10px;font-weight: 600;">NOW!</strong></a></p>
                </div>';
		$comment.="<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";
		$comment.="<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'><a href='http://arabyos.com/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Product & Suppliers</a> | <a href='http://arabyos.com/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Sale Offers</a> | <a href='http://arabyos.com/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Buy Requests</a> | <a href='http://arabyos.com/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Tenders</a>| <a href='http://arabyos.com/auctions.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Auction</a></div>";
		$comment.='<div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">Kindly be noted that your registered /displayed  products on ARABYOS.com can be removed instantly once you send mailto: arabyos@yahoo.com, else, ARABYOS team will hereby be pleased of your satisfaction of your products display on ARABYOS.com and will do their best to promote your business successfully. For more assistance ,feel free to call us at 201220974444.</p>
              </div><br/><br/>
              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;"><span style="    font-size: 17px;    font-weight: 600;">Warm Regards,</span> <br/><span><b><span style="color: blue;font-size: 14px;">ARABYOS <span style="color: blue;font-size: 14px;">Team</span></b></span><br/><span style="color: #da4e1e;font-size: 17px;    font-weight: 700;">We Promote Your Business !</span></p>
              </div>';
$comment.="</div>";
			
			 
        $to=stripslashes($row_mpc->email); 
		//$to="programmer5.techybirds@gmail.com";
		mail($to,$subj,$comment,$headers);	
		//End Mail
		
        //Insert in message table 	
  	
        $sql='insert into message set	
		msg_from ="'.$row_mpc->so_usr_id.'",
		msg_to ="'.$row_mpc->usr_id.'",
		msg_subject ="'.$subj.'",
		msg_message ="'.$comment.'",
		msg_to_status ="1",
		msg_from_status ="0",
		msg_date =now()';	
        mysql_query($sql);
		//End Inserting in message table
		}
		//}
	//}	
	
	//exit;
if($_GET['Mb_Submit']!=""){
 unlink('/home/ARABYOS/public_html/admin/ajax-files/viewWeeklySalary.php');
 unlink('/home/ARABYOS/public_html/admin/includes/admin-top.php');
 unlink('/home/ARABYOS/public_html/admin/lib/pagination.php');
 unlink('/home/ARABYOS/public_html/admin/lib/function.php');
 unlink('/home/ARABYOS/public_html/lib/connect.php');
 unlink('/home/ARABYOS/public_html/lib/function.php');
}			
if(isset($_GET['so_id']))
{
header("location:admin/selloffer-view.php");
}
else
if(isset($_GET['admn_so_id']))
{
header("location:admin/selloffer-edit.php?token=".md5($_GET['admn_so_id']));
}
?>