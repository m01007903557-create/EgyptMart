<?php
session_start();
include "../common.php";

//print_r($_SESSION['HTTP_HOST']);
//die;

//print_r($_POST['c']);
//die;

$sql_own = "select * from user,business_profile where usr_id='" . $_SESSION['uid_indm'] . "' and bnsprof_uid=usr_id limit 1";
$res_own = mysql_query($sql_own);
$row_own = mysql_fetch_object($res_own);



//echo "<pre>";
//print_r($row_so);
//die;
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
        
	/*New Email Design start form heare*/
        /***********IMORTANTE CHANGE NEED FOR LIVE SITE********/
        /* 1. src="http://'.$_SERVER['HTTP_HOST'].'/sitelogo/'.getSiteLogo().'
         * 2. 
         */
	$comment='<table align="center" border="4" cellpadding="0" cellspacing="0" width="680">
<tbody><tr>
  <td>
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tbody>
  <tr>
  <td style="padding-bottom:5px" valign="middle" width="32%">
  <a rel="nofollow" href="index.php" target="_blank"><img alt='. getWebSiteName().' src="https://'.$_SERVER['HTTP_HOST'].'/arabyos/sitelogo/'.getSiteLogo().'"style="width: 300px; margin: 0px 0px 0px 5px;"  border:0"></a></td>

  <td style="font-family:Trebuchet MS";font-size:13px;text-align:center" valign="middle" width="50%">
  
  <p style="color: rgb(45, 104, 162); font-weight: bold; font-family:Trebuchet MS; font-size:18px; text-align:center">Todays Latest<br>
  <span style="font-size:18px">Wholesale Order Enquiry</span> </p>
  </td>
  
  <td style="padding:7px 5px 10px 0;font-size:13px" align="right" valign="middle" width="32%"><b><?php echo date("l, F d, Y"); ?></b>
  
  </td>
  </tr>
  </tbody>
  </table>
  </td>
  </tr>
					   
  <tr>
  <td style="color:#7e7e7f;padding:15px 5px 15px 0;line-height:16px"><b>Dear '.$row_own->name_prefix.''.$row_own->fname.''.$row_own->lname.'</b><br><br>
  Latest sell offers relevant to your subscribed categories are listed below:</td>
  </tr> 
<tr><td>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="680">
<tbody><tr>



<td style="vertical-align:top" width="680">		 
		<div style="width:95%;overflow:hidden;background-color:rgb(243,243,243);border-top:1px solid rgb(225,36,0);padding:2px 2px 12px;min-height:175px;line-height:normal">
		<div style="margin:0 0 5px 0;padding:0;min-height:26px">
				<table border="0" cellpadding="0" cellspacing="0">
				<tbody><tr><td style="width:210px;text-align:left" align="left">
				<a href="'.$_SESSION['HTTP_HOST'].'"/saleoffer-details.php?id=".rand(1000,9999).md5('.$row_so->so_id.');" style="color:#0000ff;font-family:Arial;font-size:13px;line-height:15px;word-wrap:break-word" target="_blank"><b>'.$row_so->so_service.'</b></a>
				</td>

				<td style="text-align:right;width:100px" align="right">

				<div style="margin-left:3px">
                                </div>
				</td></tr>
				</tbody></table>
		</div>
<table>
<tbody><tr>
<td style="list-style:none outside none;line-height:normal;vertical-align:top;width:47%">
<div style="line-height:normal;border:4px solid rgb(170,170,170);vertical-align:middle;min-height:125px!important;width:auto;background-color:rgb(255,255,255)">
<a href="'.$_SESSION['HTTP_HOST'].'"/saleoffer-details.php?id=".rand(1000,9999).md5('.$row_so->so_id.');" style="text-decoration:none;line-height:normal" target="_blank"><table style="line-height:normal">
<tbody><tr><td style="vertical-align:middle;width:125px;word-wrap:break-word;height:125px;background-color:#ffffff;line-height:normal" align="center"><img alt="'. $row_so->so_service.'" style="line-height:normal;margin:0px;padding:0px" src="'.$_SESSION['HTTP_HOST'].'"/upload/sale_offer/"'.$row_so->so_pic.'" border="0"></td>
</tr></tbody></table></a></div>

			</td>
			<td style="list-style:none outside none;width:53%;line-height:normal;vertical-align:top"><div style="line-height:14px;font-size:13px;font-family:Arial;word-wrap:break-word;font-weight:700;padding:5px 0px 0px 2px;margin:0px"><?php echo user_info($row_so->so_usr_id,'.bnsprof_compname.'); ?></div>

			<div style="line-height:14px;font-size:12px;color:#3b3b3b;font-weight:700;margin:0;padding:5px 0 0 2px;font-family:Arial">Location:&nbsp;<span style="font-weight:normal;word-wrap:break-word"><?php echo get_city_name($row_so->so_usr_id,'.bnsprof_city.'); ?><br>[<?php echo get_country_name($row_so->country); ?>]</span></div>
			

			<br>	
			<div style="line-height:normal;margin:0;padding:0;background:#f75b16;border:1px solid #bf5305;background:-moz-linear-gradient(top,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);background:-webkit-linear-gradient(top,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);background:-o-linear-gradient(top,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);background:-ms-linear-gradient(top,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);background:linear-gradient(to bottom,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);width:122px;min-height:32px;text-align:center">
                            
			<a href="<?php echo $_SESSION['.HTTP_HOST.']."/saleoffer-details.php?id=".rand(1000,9999).md5($row_so->so_id); ?>" style="color:#fff;padding:8px 0px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;line-height:normal;text-decoration:none;text-align:center" target="_blank">Send Enquiry</a>
		</div>
			</td>
	</tr></tbody></table>
		</div>
		
		</td></tr>
</tbody></table>
</td></tr>



  <tr> <td>
<table align="left" width="668">
<tbody><tr>
  <td style="padding:10px 5px;font-size:10px;color:#888888;background-color:#ebebeb">You have received this email by virtue of your opt-in subscription for sell offers alert on <span style="color:#4163a2;text-decoration:underline"><a href="<?php echo $_SESSION['.HTTP_HOST.']; ?>" target="_blank"><?php echo getWebSiteName(); ?></a></span> 
  <br>
  <a href="<?php echo $_SESSION['.HTTP_HOST.']."/manage-selloffer-alert.php"; ?>" target="_blank">Click here</a> if you wish to modify your sell offers alert categories.<br>

  <br>
   </td>
  </tr>
</tbody></table>
</td>
  </tr>
</tbody></table>';

		$from_mail=get_adminemail();
	    $to=user_info($msg_to,'email');
		$from_name = get_page_settings(4);
	    $subj=$row_own->bnsprof_compname.' Business Enquiry Through '.getWebSiteName();
	    $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";	
                echo "<pre>";
                print_r($comment);
                die;
		mail('zakariya.omar.naseef@gmail.com',$subj,$comment,$headers);
	
	/**** END -- Mail sending code ****/
        session_destroy();
header("Location: https://localhost/arabyos/company/products.php?c=$c");
}
else
{
	echo 0;	
}

