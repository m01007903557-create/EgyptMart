<?php
include "../common.php";
ini_set("display_errors", 1);
$data = mysql_query("select pm.*, u.*, sp.* from plan_member_id pm JOIN business_profile bf ON pm.b_id = bf.bnsprof_id JOIN user u ON u.usr_id = bf.bnsprof_uid JOIN smembership_plan sp ON sp.mp_id = pm.p_id Where notification_email_week3_status =0") or die(mysql_error());
 while($row = mysql_fetch_object($data)) {
	 if(date('Y-m-d', strtotime("+14 days"))==date('Y-m-d',$row->expiry_date)){
	 //echo "<pre>test"; print_r($row); echo "</pre>";
    $to = $row->email;  /*Put Your Email Adress Here*/
    $fullname = $row->name_prefix.$row->fname." ".$row->lname;
    $expiry_date = date("Y-m-d", $row->expiry_date);
    $start_date = date("Y-m-d", $row->start_date);
    $plan_name = $row->mst_name;

   $billing_sql = "SELECT * from billing_history WHERE bh_type = 5 AND bh_usr_id='".$row->usr_id."' ORDER BY bh_updated_date DESC LIMIT 1";
   $billing_query = mysql_query($billing_sql) or die(mysql_error()); 
   $billing_detail = mysql_fetch_object($billing_query);

	$subject = "Your ".$plan_name." plan will expire on ".get_page_settings(4);	
		
	require "email/plan_expiry_notification.php"; //email design with content included
$from_name = get_page_settings(4);
	$from_email = get_adminemail();
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: $from_name < $from_email >\r\n";
	$headers .= "Reply-To: $from_email";
	mail($to, $subject, $message1, $headers);
    mysql_query("UPDATE plan_member_id SET notification_email_week3_status = 1 WHERE b_id =".$row->b_id);

$message = 'Your '.str_replace(array("Plan","plan"),array("",""),$plan_name). ' plan will expire on '.$expiry_date.'. Please renew your membership.';
$sql="insert into message 
				set 
					msg_from=".getAdminUserId().",
					msg_to='".$row->usr_id."',
					msg_subject='". $subject. "',
					msg_message='".$message ."',
					msg_entity='membership_plan',
					msg_entity_id='".$row->b_id."',
					msg_date=now()";
					//echo $sql;exit;
			mysql_query($sql);
	 }
 }
 ?>