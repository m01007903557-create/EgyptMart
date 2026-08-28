<?php
include "../common.php";

$usr_id=$_SESSION['uid_indm'];
$auc_id=$_POST['id'];

$sql="insert into purchased_auction
	set
		pauc_usr_id='".$usr_id."',
		pauc_auc_id='".$auc_id."',
		pauc_purchase_date=now()";

if(mysqli_query($con, $sql))
{
	$sql_usr="select * from user where usr_id='".$usr_id."'";
	$res_usr=mysqli_query($con, $sql_usr);
	$row_usr=mysqli_fetch_object($res_usr);
	
	$sql_upd="update user
		set
			usr_credit='".($row_usr->usr_credit-20)."'
		where
			usr_id='".$row_usr->usr_id."'";
	mysqli_query($con, $sql_upd);
	
	$sql_bh="insert into billing_history
				set 
					bh_type = '4',
					bh_usr_id = '".$usr_id."',
					bh_from = '".$auc_id."',
					bh_credit_used = '20',
					bh_user_balance = '".($row_usr->usr_credit-20)."',
					bh_updated_date = now()";
	mysqli_query($con, $sql_bh);
	
	/**** START -- Mail sending code ****/
	
	/*$comment='<table align="center" border="0" cellpadding="0" cellspacing="0" width="680">
<tbody><tr>
  <td style="padding-top:10px;border-bottom:1px solid #bdd0f2">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tbody>
  <tr>
  <td style="padding-bottom:5px" valign="middle" width="32%">
  <a rel="nofollow" href="//'.$_SERVER['HTTP_HOST'].'/indiamart/index.php" target="_blank"><h2>'.getWebSiteName().'</h2></a></td>
  <td style="font-family:\'Trebuchet MS\';font-size:13px;text-align:center" valign="middle" width="36%">
  <b></b></td>
  <td style="padding:7px 5px 10px 0;font-size:13px" align="right" valign="middle" width="32%"><b>'.date("l, F d, Y").'</b></td>
  </tr>
  </tbody>
  </table>
  </td>
  </tr><tr>
  <td style="color:#7e7e7f;padding:15px 5px 15px 0;line-height:16px"><b>Dear '.$row_usr->name_prefix.' '.$row_usr->fname.' '.$row_usr->lname.',</b><br><br>
  Thank You for Purchasing Auction Information!<br>
  This Auction is saved in your "<a href="//'.$_SERVER['HTTP_HOST'].'/manage-purchased-auctions.php">Purchased Auctions</a>" section.<br>
  You can submit your response from "<a href="//'.$_SERVER['HTTP_HOST'].'/manage-purchased-auctions.php">Purchased Auctions</a>".<br>
  This purchase will reflect in your "<a href="//'.$_SERVER['HTTP_HOST'].'/transaction_history.php">Transaction History</a>" as well.<br><br>
  <b>'.getWebSiteName().' Customer Support Team</b>
  </td>
  </tr>
</tbody></table>';

		$from_mail=get_adminemail();
	    $to=stripslashes($row_usr->email);  
		$from_name = get_page_settings(4);
	    $subj="Auction Purchase Notification";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";	
		mail($to,$subj,$comment,$headers);*/
		include '../email/auction_notification.php';
		$from_mail=get_adminemail();
	    $to=stripslashes($row_usr->email);  
		$from_name = get_page_settings(4);
	    $subj="Auction Purchase Notification";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";	
		mail($to,$subj,$message1,$headers);
		
	
	/**** END -- Mail sending code ****/
	
}
?>