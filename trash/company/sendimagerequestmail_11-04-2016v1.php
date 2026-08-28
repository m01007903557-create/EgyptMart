<?php
session_start();
include "../common.php";

$data=unserialize($_POST['sel_product_ser']);
$uid_indm = $_SESSION['uid_indm'];

$sql_own = "select * from user,business_profile where usr_id='" . $_SESSION['uid_indm'] . "' and bnsprof_uid=usr_id limit 1";
$res_own = mysql_query($sql_own);
$row_own = mysql_fetch_object($res_own);




$image1 = $_SESSION['image1'];
$image2 = $_SESSION['image2'];
$image3 = $_SESSION['image3'];
$image4 = $_SESSION['image4'];
$image5 = $_SESSION['image5'];
$image6 = $_SESSION['image6'];
$image7 = $_SESSION['image7'];
$image8 = $_SESSION['image8'];
$image9 = $_SESSION['image9'];
$image10 = $_SESSION['image10'];
$image11 = $_SESSION['image11'];
$image12 = $_SESSION['image12'];
$image13 = $_SESSION['image13'];
$image14 = $_SESSION['image14'];
$image15 = $_SESSION['image15'];
$image16 = $_SESSION['image16'];
$image17 = $_SESSION['image17'];
$image18 = $_SESSION['image18'];
$image19 = $_SESSION['image19'];
$image20 = $_SESSION['image20'];

$id1 = $_SESSION['id1'];
$id2 = $_SESSION['id2'];
$id3 = $_SESSION['id3'];
$id4 = $_SESSION['id4'];
$id5 = $_SESSION['id5'];
$id6 = $_SESSION['id6'];
$id7 = $_SESSION['id7'];
$id8 = $_SESSION['id8'];
$id9 = $_SESSION['id9'];
$id10 = $_SESSION['id10'];
$id11 = $_SESSION['id11'];
$id12 = $_SESSION['id12'];
$id13 = $_SESSION['id13'];
$id14 = $_SESSION['id14'];
$id15 = $_SESSION['id15'];
$id16 = $_SESSION['id16'];
$id17 = $_SESSION['id17'];
$id18 = $_SESSION['id18'];
$id19 = $_SESSION['id19'];
$id20 = $_SESSION['id20'];

if ($id1 == '') {
    $id1 = 0;
}
if ($id2 == '') {
    $id2 = 0;
}
if ($id3 == '') {
    $id3 = 0;
}
if ($id4 == '') {
    $id4 = 0;
}
if ($id5 == '') {
    $id5 = 0;
}
if ($id6 == '') {
    $id6 = 0;
}
if ($id7 == '') {
    $id7 = 0;
}
if ($id8 == '') {
    $id8 = 0;
}
if ($id9 == '') {
    $id9 = 0;
}
if ($id10 == '') {
    $id10 = 0;
}
if ($id11 == '') {
    $id11 = 0;
}
if ($id12 == '') {
    $id12 = 0;
}
if ($id13 == '') {
    $id13 = 0;
}
if ($id14 == '') {
    $id14 = 0;
}
if ($id15 == '') {
    $id15 = 0;
}
if ($id16 == '') {
    $id16 = 0;
}
if ($id17 == '') {
    $id17 = 0;
}
if ($id18 == '') {
    $id18 = 0;
}
if ($id19 == '') {
    $id19 = 0;
}
if ($id20 == '') {
    $id20 = 0;
}


$sel_product = array();

$sel_pro = "select * from products where pd_id IN ($id1,$id2,$id3,$id4,$id5,$id6,$id7,$id8,$id9,$id10,$id11,$id12,$id13,$id14,$id15,$id16,$id17,$id18,$id19,$id20)";

$s_prod = mysql_query($sel_pro);
while ($select_product = mysql_fetch_object($s_prod)) {
    $sel_product[] = $select_product;
    $sel_product_image[] = $select_product->pd_image;
}

$msg_from=$_POST['msg_from'];
$msg_to=$_POST['msg_to'];
$msg_subject=$_POST['msg_subject'];
$msg_message=$_POST['comment'];
$image=$_SESSION['selimage'];
$c=$_POST['c'];

//echo "<pre>";
//print_r($selpro);
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
    foreach($data as $value){
        $sql_ma="insert into message_attachment
			set	
				ma_msg_id ='".$msg_id."',
				ma_file ='".$value->pd_image."',
                                ma_file_name = '".$value->pd_title."',
                                ma_file_quentity = '".$value->pd_min_order_qty."',
                                ma_file_unit = '".$value->pd_unit."',
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
        /* 1. src="http://'.$_SERVER['HTTP_HOST']..........
         * 2. 
         */
//        $imgList = "";
//        foreach($image as $key=>$value){
//             $imgList.='<><img src="http://'.$_SERVER['HTTP_HOST'].'/upload/myproduct/'.$value.'"></img>';  
//        }
        $product = "";
        foreach ($sel_product as $selpro) {
            $product.='<div style="width:35%; overflow: hidden; float:left; margin-bottom: 20px;">
	
		<div style="width: 50%; float: left; overflow: hidden;">
			<img height="100" width="150"src="http://'.$_SERVER['HTTP_HOST'].'/upload/myproduct/'.rawurlencode($selpro->pd_image).'">
		</div>
		<div style="width:50%; float: left; overflow: hidden; font-size: 1.2em;">
			<div>
				<div style="color:rgb(70, 109, 160);">
					'.$selpro->pd_title.'
				</div>
                                <br>
				<div>
					MOQ &nbsp;:&nbsp;'.$selpro->pd_min_order_qty.' &nbsp; &nbsp; &nbsp;'.get_measurement_unit($selpro->pd_unit).'
				</div>
			</div>
		</div>
	
</div>';  
        }
//        die($imgList);
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
							        <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">Todays Latest<br> Wholesale Business Enquiry
							        </h2>
							    </div>
							    <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
							        <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;"> Notification</span>
							        <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">'.date("Y/m/d").'</span>
							    </div>
							</div>
							<div style="width:100%;float:left;color:#000000;">
							    <p style="font-size:16px;color:#000000"><strong>Dear '.$row_own->name_prefix.''.$row_own->fname.''.$row_own->lname.'</strong>
							</div>
							<div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
								<p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
									<b>'.$row_own->bnsprof_compname.' '.$row->msg_subject.' Through '.getWebSiteName().'</b>
								</p>
								<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Sender\'s Contact Details:
								</p>
								<div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">'.$row_own->name_prefix.' '.$row_own->fname.' '.$row_own->lname.'<br>
									'.$row_own->bnsprof_address1.'<br>
									'.get_city_name($row_own->bnsprof_city).', '.get_country_name($row_own->country).'<br>
									Mobile/ Cell Phone: '.$row_own->country_ph_code.'-'.$row_own->mobile1.'<br>
									E-mail: <a href="'.$row_own->email.'" target="_blank">'.$row_own->email.'</a><br>
								</div>
								<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Enquiry Details:</p>
							<div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:0.5em 0 0.9em 1em">
                                                        
                                                            '.$product.'
                                                        <div style="width: 100%; float: left;">
                                                            <span style="font-size:1.0em;font-weight:normal">'.stripslashes($msg_message[0]).'</span>
                                                        </div>
							</div>
							<div style="clear:both">
                                                            
							</div>
							<br>
							<div style="clear:both">
                                                            <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Kindly Replay Your Buyer Enquiry:<button href="javascript:void(0);" style="float: right">Reply NOW</button></p>
                                                        </div>
                                                        <br>
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
//          echo "<pre>";
//          print_r($comment);
//          die;
            mail($to,$subj,$comment,$headers);
	
	/**** END -- Mail sending code ****/

header("Location: http://arabyos.com/company/products.php?c=$c");
        session_unset();
        $_SESSION = $c;
        $_SESSION = $uid_indm;
}
else
{
	echo 0;	
}

