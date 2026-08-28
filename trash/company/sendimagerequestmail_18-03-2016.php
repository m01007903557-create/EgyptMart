<?php
session_start();
include "../common.php";

//print_r($_POST['c']);
//die;

$sql_own = "select * from user,business_profile where usr_id='" . $_SESSION['uid_indm'] . "' and bnsprof_uid=usr_id limit 1";
$res_own = mysql_query($sql_own);
$row_own = mysql_fetch_object($res_own);

//echo"<pre>";
//print_r($row_own);
//die;

//echo "<pre>";
//print_r($_POST['msg_from']);
//echo "_";
//print_r($_POST['msg_to']);
//echo "_";
//print_r($_POST['msg_subject']);
//echo "_";
//print_r($_POST['email']);
//echo "_";
//print_r($_POST['comment']);
//echo "_";
//print_r($_SESSION['selimage']);
//die;

$msg_from=$_POST['msg_from'];
$msg_to=$_POST['msg_to'];
$msg_subject=$_POST['msg_subject'];
$msg_message=$_POST['comment'];
$image=$_SESSION['selimage'];
$c=$_POST['c'];
//echo "<pre>";
//print_r($image);
//die;

$sql="insert into message
	set	
		msg_from ='".$msg_from."',
		msg_to ='".$msg_to."',
		msg_subject ='".$msg_subject."',
		msg_message ='".$msg_message[0]."',
		msg_date =now()";

if(mysql_query($sql))
{
    $msg_id=mysql_insert_id();
    foreach($image as $key=>$value){
        $sql_ma="insert into message_attachment
			set	
				ma_msg_id ='".$msg_id."',
				ma_file ='".$value."',
				ma_updated_date =now()";
				
		mysql_query($sql_ma);
    }
    
	/** Code for message attachment start **/
//	$msg_id=mysql_insert_id();
//	
//	$sql_tma="select * from temp_msg_attachment where tma_usr_id='".$msg_from."'";
//	$res_tma=mysql_query($sql_tma);
//	while($row_tma=mysql_fetch_object($res_tma))
//	{
//            
//		$sql_ma="insert into message_attachment
//			set	
//				ma_msg_id ='".$msg_id."',
//				ma_file ='".$row_tma->tma_file."',
//				ma_updated_date =now()";
//				
//		mysql_query($sql_ma);
//
//		mysql_query("delete from temp_msg_attachment where tma_id='".$row_tma->tma_id."'");
//	}
	/** Code for message attachment end **/
	
	$sql_chk="select * from review_rating where rr_from_usr='".$msg_from."' and rr_to_usr='".$msg_to."'";
	$res_chk=mysql_query($sql_chk);
	if(mysql_num_rows($res_chk)<=0)
	{
		$sql_rr1="insert into review_rating
			set
				rr_from_usr='".$msg_from."',
				rr_to_usr='".$msg_to."'";
		mysql_query($sql_rr1);
		
		$sql_rr2="insert into review_rating
			set
				rr_from_usr='".$msg_to."',
				rr_to_usr='".$msg_from."'";
		mysql_query($sql_rr2);
	}
	
	
	/**** START -- Mail sending code ****/
        
	$comment='<div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
	<p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center"> <b>'.$row_own->bnsprof_compname.' Business Enquiry Through '.getWebSiteName().'</b></p>
	<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">			Sender\'s Contact Details:</p>
	<div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">'.$row_own->name_prefix.' '.$row_own->fname.' '.$row_own->lname.'<br>
		'.$row_own->bnsprof_address1.'<br>
		'.get_city_name($row_own->bnsprof_city).', '.get_country_name($row_own->country).'<br>
		Mobile/ Cell Phone: '.$row_own->country_ph_code.'-'.$row_own->mobile1.'<br>
		E-mail: <a href="'.$row_own->email.'" target="_blank">'.$row_own->email.'</a><br>
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
</div>';

		$from_mail=get_adminemail();
	    $to=user_info($msg_to,'email');
		$from_name = get_page_settings(4);
	    $subj=$row_own->bnsprof_compname.' Business Enquiry Through '.getWebSiteName();
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";	
		mail($to,$subj,$comment,$headers);
	
	/**** END -- Mail sending code ****/
        session_destroy();
header("Location: https://localhost/arabyos/company/products.php?c=$c");
}
else
{
	echo 0;	
}

