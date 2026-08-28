<?php
include "../common.php";

$msg_from=$_POST['msg_from'];
$msg_to=$_POST['msg_to'];

$sql_to = "select * from user,business_profile where usr_id='" .$_POST['msg_to']. "' and bnsprof_uid=usr_id limit 1";
$res_to = mysql_query($sql_to);
$row_to = mysql_fetch_object($res_to);

$msg_subject=trim(addslashes($_POST['msg_subject']));
$msg=trim(addslashes($_POST['msg_message']));

$sql_usr="select * from user,business_profile where usr_id=bnsprof_uid and usr_id='".$msg_from."'";
$res_usr=mysql_query($sql_usr);
$row_usr=mysql_fetch_object($res_usr);

$msg_message = wordwrap($msg, 90, "<br />\n");

$sql="insert into message
	set	
		msg_from ='".$msg_from."',
		msg_to ='".$msg_to."',
		msg_subject ='".$msg_subject."',
		msg_message ='".$msg_message."',
		msg_date =now()";
		
if(mysql_query($sql))
{
	/** Code for message attachment start **/
	
	
	
	/**** START -- Mail sending code ****/
	
	$comment='<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              <div style="height: 100px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img src="http://www.arabyos.com/images/Mlogo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="ARABYOS">
                  </div>
                  <div style="height:100px;width:43%;float:left;">
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">Today\'s Latest<br> Business Enquiry
                      </h2>
                  </div>
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;"> Enquiry</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">'.date("Y/m/d").'</span>
                  </div>
              </div>
              <div style="width:100%;color:#000000;">
                  <p style="font-size:16px;color:#000000"><strong>Dear '.$row_to->name_prefix.''.$row_to->fname.' '.$row_to->lname.'</strong>
              </div>
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
  <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center"> <b>'.$row_usr->bnsprof_compname.' Business Enquiry through <span style="color: blue">ARABYOS</span></b></p>
  <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">      Sender\'s Contact Details:</p>
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
  <br>
              <div style="clear:both">
                                                            <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Kindly Reply Your Buyer Enquiry:<a href="http://arabyos.com/my-enquiries.php" style="float: right">Reply NOW</a></p>
                                                        </div>
                                                        <br>
  <div style="clear:both"></div>
  <br>
  <div style="clear:both"></div>
  <table style="font-family:Arial,Helvetica,sans-serif;font-size:13px" cellpadding="0" cellspacing="0">
    <tbody><tr>
    <td style="line-height:20px" valign="top">
       <span style="color: blue;">ARABYOS</span> Customer Support Team
      <br>Call us on '.get_page_settings(21).'
    </td>
    </tr></tbody>
  </table>
  <span style="color:rgb(171,172,172);font-size:11px">You are receiving this mailer as a registered member of <span style="color: blue;"></span>.</span>
</div>
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;">

              </div>
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://arabyos.com/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | <a href="http://arabyos.com/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | <a href="http://arabyos.com/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | <a href="http://arabyos.com/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>
              </div>
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px">You have recived this mail virtue of your opt-in subscription for product Enquiry on <font style="color:blue;">ARABYOS</font>.</p><p style="color:#808080; margin:0px 0px 20px;"><a href="http://www.arabyos.com/manage-buylead-alert.php" style="text-decoration:none;color:blue;">Click here</a> if you wish to modify to your buy requirement alert categories.</p>
              </div>
            </div>
          </span>
        </td>   
      </tr>
    </tbody>
  </table>
</div>';

		$from_mail=get_adminemail();
	        $to=user_info($msg_to,'email');
		$from_name = get_page_settings(4);
	        $subj=$row_usr->bnsprof_compname.' Business Enquiry through ARABYOS';
	        $headers  = "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\n";
	 	$headers .= "From: ".$from_name." <".$from_mail.">";	
		mail($to,$subj,$comment,$headers);
	
	/**** END -- Mail sending code ****/
	
	
	echo 1;	
}
else
{
	echo 0;	
}
?>