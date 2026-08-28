<?php
session_start();
include "../common.php";
$c = $_POST['c'];
$uid_indm = $_SESSION['uid_indm'];

$country = '';

if($_POST['country'] == ''){
    $country = '98';
}
else{
    $country = $_POST['country'];
}
$sn_email = $_POST['email'];
$company = $_POST['company'];
$msg_from = $_POST['name'];
$msg_to = $_POST['msg_to'];
$country_code = $_POST['country_code'];
$mobile = $_POST['mobile'];
$msg = $_POST['description'];

$description = wordwrap($msg, 150, "<br />\n");

$msg_message ='Company Name : '.$msg_from.'<br>
               Country : '.get_country_name($country).'<br>
               Mobile/ Cell Phone: '.$country_code.'-'.$mobile.'<br>
               E-mail: <a href="'.$sn_email.'" target="_blank">'.$sn_email.'</a><br>
               Description : '.$description.''; 


$sql="insert into message
	set	
		msg_from ='".$company."',
		msg_to ='".$msg_to."',
		msg_subject ='SMS Enquiry',
		msg_message ='".$msg_message."',
		msg_date =now()";



if(mysql_query($sql))
{
	$comment='<div class="b9_m2 b10_m2" id="detable">
	<table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
		<tbody>
			<tr class="f5_m2">
				<td class="sh_m2">
					<span style="width:750px;word-wrap:break-word;" id="wbr">
						<div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
							<div style="height: 100px; width: 100%; float: left; ">
							    <div style="height: 100px; width: 30%; float: left;">
							        <img src="http://arabyos.com/images/logo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="ARABYOS">
							    </div>
							    <div style="height:100px;width:43%;float:left;">
							        <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">Todays Latest<br> Business Enquiry
							        </h2>
							    </div>
							    <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
							        <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;"> Notification</span>
							        <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">'.date("Y/m/d").'</span>
							    </div>
							</div>  
							<div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
								<p style="line-height:1.5em;text-align:left;font-size:1.2em;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Sender\'s Contact Details:</p>
                                                                    <div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                                                                        Company Name : '.$msg_from.'<br>
                                                                        Country : '.get_country_name($country).'<br>
                                                                        Mobile/ Cell Phone: '.$country_code.'-'.$mobile.'<br>
                                                                        E-mail: <a href="'.$sn_email.'" target="_blank">'.$sn_email.'</a><br>
                                                                        Description : '.$description.'
                                                                    </div>
							<div style="clear:both">
								
							</div>
							<br>
							<div style="clear:both">
                                                            <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Kindly Replay Your Buyer Enquiry:<button href="javascript:void(0);" style="float: right">Reply NOW</button></p>
                                                        </div>
							<table style="font-family:Arial,Helvetica,sans-serif;font-size:13px" cellpadding="0" cellspacing="0">
								<tbody>
									<tr>
										<td style="line-height:20px" valign="top">
											'.getWebSiteName().' Customer Support Team
											<br>
											Call us on '.get_page_settings(21).'
										</td>
									</tr>
								</tbody>
							</table>
	<span style="color:rgb(171,172,172);font-size:11px">You are receiving this mailer as a registered member of '.getWebSiteName().'.</span>
							</div>
							<div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;">

							</div>
							<div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
							    <a href="http://arabyos.com/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | <a href="http://arabyos.com/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | <a href="http://arabyos.com/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | <a href="http://arabyos.com/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>| <a href="http://arabyos.com/auctions.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Auction</a>
							</div>
							<div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px">You have recived this mail virtue of your opt-in subscription for product alert on <font style="color:blue;">ARABYOS</font>.</p><p style="color:#808080; margin:0px 0px 20px;"><a href="http://www.arabyos.com/manage-buylead-alert.php" style="text-decoration:none;color:blue;">Click here</a> if you wish to modify to your buy requirement alert categories.</p>
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
	    $subj=$row_own->bnsprof_compname.' Business Enquiry Through '.getWebSiteName();
	    $headers  = "MIME-Version: 1.0\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\n";
            $headers .= "From: ".$from_name." <".$from_mail.">";	
            mail($to,$subj,$comment,$headers);
	/**** END -- Mail sending code ****/

header("Location: http://arabyos.com/company/products.php?c=$c");
        session_unset();
        $_SESSION = $c;
        $_SESSION = $uid_indm;
}
else
{
	echo "OPS SOMTHING WENT WRONG";	
}

