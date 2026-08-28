<?php
include "../common.php";

$usr_id=$_SESSION['uid_indm'];
$br_id=$_POST['id'];


$sql="insert into purchased_buy_requirement
	set
		pbr_usr_id='".$usr_id."',
		pbr_br_id='".$br_id."',
		pbr_purchase_date=now()";

if(mysqli_query($con, $sql))
{
	$sql_usr="select * from user join business_profile on usr_id=bnsprof_uid where usr_id='".$usr_id."'";
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
					bh_type = '2',
					bh_usr_id = '".$usr_id."',
					bh_from = '".$br_id."',
					bh_credit_used = '20',
					bh_user_balance = '".($row_usr->usr_credit-20)."',
					bh_updated_date = now()";
	mysqli_query($con, $sql_bh);
$user_country ='';
$user_state = '';
if($row_usr->country != '') {
$sql_country="select * from country where cn_id='".$row_usr->country."' and cn_status =1";
$res_country=mysqli_query($con, $sql_country);
$row_country=mysqli_fetch_object($res_country);
$user_country = $row_country->cn_name;
}
if($row_usr->bnsprof_state!= '') {
$sql_state="select * from states where state_id='".$row_usr->bnsprof_state."' and state_status = 1";
$res_state=mysqli_query($con, $sql_state);
$row_state=mysqli_fetch_object($res_state);
$user_state = $row_state->state_name;
}
$sql_br = "select * from buy_requirement join user on br_u_id=usr_id where br_id='".$br_id."'";
$res_br = mysqli_query($con, $sql_br);
$buyer_details = mysqli_fetch_object($res_br);
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
  Thank You for Purchasing the Buy Lead!<br>
  This Buy Lead is saved in your "<a href="//'.$_SERVER['HTTP_HOST'].'/indiamart/manage-purchased-buyleads.php">Purchased Buy Leads</a>" section.<br>
  You can submit your response to this Buyer from "<a href="//'.$_SERVER['HTTP_HOST'].'/indiamart/manage-purchased-buyleads.php">Purchased Buy Leads</a>".<br>
  This purchase will reflect in your "<a href="//'.$_SERVER['HTTP_HOST'].'/indiamart/transaction_history.php">Transaction History</a>" as well.<br><br>
  <b>'.getWebSiteName().' Customer Support Team</b>
  </td>
  </tr>
</tbody></table>';

		$from_mail=get_adminemail();
	    $to=stripslashes($row_usr->email);  
		$from_name = get_page_settings(4);
	    $subj="Buylead purchase notification";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";	
		mail($to,$subj,$comment,$headers);*/
		include '../email/buylead_notification.php';
        $from_mail=get_adminemail();
	    $to=stripslashes($buyer_details->email);  
	    $from_name = get_page_settings(4);
	    $subj="Buylead purchase notification";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";	
		//echo $message1;exit;
		mail($to,$subj,$message1,$headers);
	/**** END -- Mail sending code ****/
	
}
?>