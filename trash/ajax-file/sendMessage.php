<?php
include "../common.php";

$lead_headline=$_POST['lead_headline'];
$msg_from=$_POST['msg_from'];
$msg_to=$_POST['msg_to'];
$msg_subject=trim(addslashes($_POST['msg_subject']));
$msg_message=trim(addslashes($_POST['msg_message']));

$sql_usr="select * from user,business_profile where usr_id=bnsprof_uid and usr_id='".$msg_from."'";
$res_usr=mysqli_query($con, $sql_usr);
$row_usr=mysqli_fetch_object($res_usr);

$sql="insert into message
	set	
		msg_from ='".$msg_from."',
		msg_to ='".$msg_to."',
		msg_subject ='".$msg_subject."',
		msg_message ='".$msg_message."',
		msg_date =now()";
	
if(mysqli_query($con, $sql))
{
	
	/** Code for message attachment start **/
	$msg_id=mysql_insert_id();
	
	$sql_tma="select * from temp_msg_attachment where tma_usr_id='".$msg_from."'";
	$res_tma=mysqli_query($con, $sql_tma);
	
	while($row_tma=mysqli_fetch_object($res_tma))
	{
			
		$sql_ma="insert into message_attachment
			set	
				ma_msg_id ='".$msg_id."',
				ma_file ='".$row_tma->tma_file."',
				ma_updated_date =now()";
				
		mysqli_query($con, $sql_ma);

		mysqli_query($con, "delete from temp_msg_attachment where tma_id='".$row_tma->tma_id."'");
	}
	
	/** Code for message attachment end **/
	
	$sql_chk="select * from review_rating where rr_from_usr='".$msg_from."' and rr_to_usr='".$msg_to."'";
	$res_chk=mysqli_query($con, $sql_chk);
	if(mysqli_num_rows($res_chk)<=0)
	{
		$sql_rr1="insert into review_rating
			set
				rr_from_usr='".$msg_from."',
				rr_to_usr='".$msg_to."'";
		mysqli_query($con, $sql_rr1);
		
		$sql_rr2="insert into review_rating
			set
				rr_from_usr='".$msg_to."',
				rr_to_usr='".$msg_from."'";
		mysqli_query($con, $sql_rr2);
	}
	
	
	/**** START -- Mail sending code ****/
	
	/*$comment='<div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
	<p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center"> <b>'.$row_usr->bnsprof_compname.' Business Enquiry Through '.getWebSiteName().'</b></p>
	<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">			Sender\'s Contact Details:</p>
	<div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">'.$row_usr->name_prefix.' '.$row_usr->fname.' '.$row_usr->lname.'<br>
		'.$row_usr->bnsprof_address1.'<br>
		'.get_city_name($row_usr->bnsprof_city).', '.get_country_name($row_usr->country).'<br>
		Mobile/ Cell Phone: '.$row_usr->country_ph_code.'-'.$row_usr->mobile1.'<br>
		E-mail: <a href="'.$row_usr->email.'" target="_blank">'.$row_usr->email.'</a><br>
	</div>
	<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Enquiry Details:</p>
	<div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:0.5em 0 0.9em 1em">
    	<span style="font-size:1.0em;font-weight:normal">'.stripslashes($msg_message).'</span>
	</div>
	<div style="clear:both"></div>
	<br>
	<div style="clear:both"></div>
	<table style="font-family:Arial,Helvetica,sans-serif;font-size:13px" cellpadding="0" cellspacing="0">
		<tbody><tr>
		<td style="line-height:20px" valign="top">
			'.getWebSiteName().' Customer Support Team
			<br>Call us on '.get_page_settings(21).'
		</td>
		</tr></tbody>
	</table>
	<span style="color:rgb(171,172,172);font-size:11px">You are receiving this mailer as a registered member of '.getWebSiteName().'.</span>
</div>';*/
		include '../email/sendenquiry_notification.php';
		$from_mail=get_adminemail();
	    $to=user_info($msg_to,'email');
	   //$to ='patelpinku20@gmail.com';
		$from_name = get_page_settings(4);
	    $subj=$row_usr->bnsprof_compname.' Business Enquiry Through '.getWebSiteName();
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";
		mail($to,$subj,$message1,$headers);
	//echo $message1;exit;
	/**** END -- Mail sending code ****/
	
	
	echo 1;	
}
else
{
	echo 0;	
}
?>