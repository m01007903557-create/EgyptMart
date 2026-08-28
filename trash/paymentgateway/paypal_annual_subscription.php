<?php
$payment_status = $_POST['payment_status'];
$amount = $_POST['mc_gross'];
$pay_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$scustom = explode('||',$_POST['custom']);

$uid=$scustom[0];
$mp_id=$scustom[1];
require '../common.php';

$sql="select * from smembership_plan where mp_id='".$mp_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$sql_u="select * from user join business_profile on usr_id = bnsprof_uid where usr_id='".$uid."'";
$res_u=mysqli_query($con, $sql_u);
$row_u=mysqli_fetch_object($res_u);

if($txn_id != ''){
$sql_bh="insert into billing_history
	set 
		bh_type = '5',
		bh_usr_id = '".$uid."',
		bh_from = 'Paypal',
		bh_txn_id = '".$txn_id."',
		bh_amount = '".$amount."',
		bh_currency_code = '".$pay_currency."',
		bh_updated_date = now()";

mysqli_query($con, $sql_bh);
$order_id = mysqli_insert_id($con);

/**** START -- Mail sending code ****/
require '../email/annual_subscription.php';

	$from_mail=get_adminemail();
	$to=stripslashes($row_usr->email);  
	$from_name = get_page_settings(4);
	$subj="Annual Subscription Payment Success";
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "From: ".$from_name." <".$from_mail.">";	
	mail($to,$subj,$message1,$headers);

	$from_mail=get_adminemail();
	$to=get_adminemail();  
	$from_name = get_page_settings(4);
	$subj="Annual Subscription notification";
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "From: ".$from_name." <".$from_mail.">";	
	mail($to,$subj,$message2,$headers);
}
else {
	/**** START -- Mail sending code ****/
require '../email/annual_subscription_failure.php';

	$from_mail=get_adminemail();
	$to=stripslashes($row_usr->email);  
	$from_name = get_page_settings(4);
	$subj="Annual Subscription Payment Failure";
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	$headers .= "From: ".$from_name." <".$from_mail.">";	
	mail($to,$subj,$message1,$headers);
}

/**** END -- Mail sending code ****/
header("Location: http://www.www.arabyos.com/index.php");
?>