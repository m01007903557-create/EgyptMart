<?php
session_start();
function sendToDebug($content) {
$file = 'debug.txt';
file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
}
/*$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value)
{
	$value = urlencode(stripslashes($value));
	$req .= '&' . $key . '=' . $value;
}
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
// If testing on Sandbox use: 
$header .= "Host: www.sandbox.paypal.com:443\r\n";
//$header .= "Host: www.paypal.com:443\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

//$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);*/

$payment_status = $_POST['payment_status'];
$amount = $_POST['mc_gross'];
$pay_currency = $_POST['mc_currency'];
$txn_id = $_POST['txn_id'];
$scustom = explode('||',$_POST['custom']);

$uid=$scustom[0];
$_SESSION['uid_indm'] = $uid;
$mp_id=$scustom[1];
$debug_txt = 'Date: '.date('Y-m-d').'<br/>';
$debug_txt .= 'payment_status: '.$payment_status.'<br/>';
$debug_txt .= 'amount: '.$amount.'<br/>';
$debug_txt .= 'txn_id: '.$txn_id.'<br/>';
$debug_txt .= 'scustom: '.$_POST['custom'].'<br/>';
sendToDebug($debug_txt);
/*if (!$fp)
{
	$checkpay="no";
	$error_output = $errstr . ' (' . $errno . ')';
}
else
{
	fputs ($fp, $header . $req);
	while (!feof($fp))
	{
		$res = fgets ($fp, 1024);
		if (strcmp ($res, "VERIFIED") == 0)
		{
			$checkpay="yes";
		}
	}
	fclose ($fp);
}*/



$checkpay="yes";
require '../common.php';

$sql="select * from membership_plan where mp_id='".$mp_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$sql_u="select * from user where usr_id='".$uid."'";
$res_u=mysqli_query($con, $sql_u);
$row_u=mysqli_fetch_object($res_u);

if($checkpay=="yes")
{
	
	$new_credits=$row_u->usr_credit+$row->mp_credits;
	
	$sql_upd="update user
		set
			usr_credit='".$new_credits."',
			usr_mp_id='".$row->mp_id."'
		where
			usr_id='".$uid."'";
			sendToDebug($sql_upd);
	mysqli_query($con, $sql_upd);
	
	$sql_bh="insert into billing_history
		set 
			bh_type = '1',
			bh_usr_id = '".$uid."',
			bh_from = 'Paypal',
			bh_txn_id = '".$txn_id."',
			bh_amount = '".$amount."',
			bh_currency_code = '".$pay_currency."',
			bh_credit_purchased = '".$row->mp_credits."',
			bh_user_balance = '".$new_credits."',
			bh_updated_date = now()";
	sendToDebug($sql_bh);
	mysqli_query($con, $sql_bh);
	$order_id = mysqli_insert_id($con);
	/**** START -- Mail sending code ****/
	require '../email/credit_success.php';	
		$from_mail=get_adminemail();
	    $to=stripslashes($row_u->email);  
		$from_name = get_page_settings(4);
	    $subj="Credit purchase notification";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";
sendToDebug($message1);		
		mail($to,$subj,$message1,$headers);
	
	/**** END -- Mail sending code ****/
}
else {
	/**** START -- Mail sending code ****/
	require '../email/credit_failure.php';	
		$from_mail=get_adminemail();
	    $to=stripslashes($row_u->email);  
		$from_name = get_page_settings(4);
	    $subj="Credit purchase notification";
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";	
		mail($to,$subj,$message1,$headers);
	
}

header("Location: http://".$_SERVER['HTTP_HOST']);
?>