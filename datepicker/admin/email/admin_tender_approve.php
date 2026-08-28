<?php 
$comment="<div style='width: 628px;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;'>";
		$comment.="<div style='height: 100px; width: 100%; float: left; '><div style='height: 100px; width: 30%; float: left;'>";
		$comment.="<img src='http://egyptmart.online/images/logo.png' style='width: 100%;color: #00F;font-size: 22px;font-weight: bold;' alt='EgyptMART'>";
      $comment.="</div><div style='height:100px;width:43%;float:left;'><h2 style='font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;'>Today's Latest<br> Tenders Approval</h2></div>";
        $comment.="<div style='min-height: 100px; width: 27%; float: right; padding-top: 3px;'><span style='font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;'> Notification</span><span style='float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;'>".date('M d, Y')."</span></div></div>";
		  $comment.="<div style='width:100%;float:left;color:#000000;'><p style='font-size:16px;color:#000000'><strong>Dear ".$suname."</strong>,<br><br><b>Your below Tender was approved to be active on <span style='color:blue'>EgyptMART.online</span></p></div>";
		$comment.="<div  style='height:auto;width:100%;float:left; margin-top:10px;'>";
		$comment.="<div style='height:auto;width:100%;float:left;'>";
		$comment.="<div style='width:100%;height:auto;float:right;'><div style='width:100%;float:left;font-size:18px;font-weight:bold;color:#466da0;padding-left:0px;text-align: center;text-transform: uppercase;background-color: #F2F241;'>".$sproduct->tnd_heading."</div><div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left: 0px;padding-top: 10px;color:#000000;'><div style='padding-left:0px;width:31%; float:left;'>Location<span style='text-align:right; float:right;'>:</span></div><div style='color:#000;padding-left:5px;width:64%; float:left;'>".$suser->cn_name." / ".$suser->state_name." / ".$suser->ct_name."</div></div>
		<div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left: 0px;padding-top: 10px;color:#000000;'>
		<div style='padding-left:0px;width:31%; float:left;'>Due Date<span style='text-align:right; float:right;'>:</span></div>
		<div style='color:#e9582c; line-height:15px;font-size:15px;font-weight:bold;padding-left:5px;width:24%; float:left;'>".date('d M, Y',strtotime($sproduct->tnd_due_date))."</div>
		<div style='color:#000;padding-left:5px;width:40%; float:left;'></div>
		</div>
		<div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left: 0px;padding-top: 10px;color:#000000;'><div style='padding-left:0px;width:31%; float:left;'>Tender Value<span style='text-align:right; float:right;'>:</span></div><div style='color:#e9582c; line-height:15px;font-size:15px;font-weight:bold;padding-left:5px;width:24%; float:left;'>".$sproduct->tnd_value."</div><div style='color:#000;padding-left:5px;width:40%; float:left;'>".getCurrency($sproduct->tnd_currency)."</div></div><div style='height:auto;width:100%;float:left;font-size:14px;line-height: 14px;text-align:left;padding-left:0px;padding-top: 10px;color:#000000;'><div style='padding-left:0px;width:31%; float:left;'>Quantity<span style='text-align:right; float:right;'>:</span></div><div style='color:#e9582c;font-weight:bold;padding-left:5px;width:24%; float:left;font-size:15px;line-height:15px;'>".$sproduct->tnd_qty."</div><div style='color:#000;padding-left:5px;width:40%; float:left;'>".measurement_unit($sproduct->tnd_qty_mu_id)."</div></div><div style='height:auto;width:100%;float:left;font-size:14px;line-height: 20px;text-align:left;padding-left:0px;padding-top: 10px;color:#000000;'><div style='padding-left:0px;width:31%; float:left;'>Project Period<span style='text-align:right; float:right;'>:</span></div><div style='color:#e9582c;font-weight:bold;padding-left:5px;width:auto; float:left;font-size:15px;line-height:15px;'>".$sproduct->tnd_project_period."</div><div style='color:#000;padding-left:5px;width:40%; float:left;'></div></div><div style='height:16%;width:97%;float:left;font-size:12px;font-weight:bold;text-align:right;padding-top:15px;padding-right:0px;'><a href='http://egyptmart.online/tender-details.php?id=".rand(1000,9999).md5($sproduct->tnd_id)."' style='text-decoration:none;color:#466da0;padding-right: 2px;'>Learn More >> </a></div></div></div>";
		$comment.="</div>";
			$comment.='<div style="width:100%;height:auto;float:right;"><p style="line-height:1.5em;text-align:left;font-size:1.4em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em;color: #002757;">Contact Details :</p></div>';
		$comment.='<div style="width: 100%; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                '.$contact_details.'
<div style="width: 90%; float: left;">
<span style="font-size:1.0em;font-weight:normal"></span>
</div>
</div>';
			$comment.='<div>
<p style="line-height:1.5em;text-align:left;font-size:1.4em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em;color: #002757;">Not Signed in Before Yet ? .. </p>
<div style="color: #000;">Use your current mail address + default password: 123456  <a href="http://www.egyptmart.online/sign-in.php?email='.$suser->email.'" style="text-decoration:none;margin-left: 50px;font-size: 14px;">Sign in NOW</a></div>';
		$comment.='<div>
			  <p style="color: #000000;font-size: 19px;font-weight: 900;background-color: #eaeaea;padding: 10px;  margin-bottom: 5px;"> You may need to post <strong style="color:#da4e1e;font-size: 19px;font-weight: 900">FREE</strong>:</p>
			  <table align="center">
			  <tr>
			  <td style="padding: 5px;"><a href="http://www.egyptmart.online/sign-in.php?email='.$suser->email.'&redirect=http://www.egyptmart.online/product-sel-cat.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Products/Services</a></td>
			  <td style="padding: 5px;"><a href="http://www.egyptmart.online/sign-in.php?email='.$suser->email.'&redirect=http://www.egyptmart.online/post-buy-req.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Buy Requirments</a></td>			  
			  </tr>
			  <tr>
			  <td style="padding: 5px;"><a href="http://www.egyptmart.online/sign-in.php?email='.$suser->email.'&redirect=http://www.egyptmart.online/post-sell-offer.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Temporary Sale Offer</a></td>
			  <td style="padding: 5px;"><a href="http://www.egyptmart.online/sign-in.php?email='.$row_mpc->email.'&redirect=http://www.egyptmart.online/post-tender.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Tenders / Auctions</a></td>			  
			  </tr>
			 
			  </table>
			  </div>';
		$comment.=' <div style="clear:both">
                    <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em"><a href="http://www.egyptmart.online/sign-in.php?email='.$suser->email.'&redirect=http://www.egyptmart.online/why_egyptmart
.php" style="font-size: 12px;font-weight: normal;">Click Here</a> to unsubscribe  or tell us your requirements  <a href="http://www.egyptmart.online/sign-in.php?email='.$suser->email.'&redirect=http://www.egyptmart.online/membership_plans.php"><strong style="color: #0f00d0;font-size: 10px;font-weight: 600;">NOW!</strong></a></p>
                </div>';
		$comment.="<div style='height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;'></div>";
		
		$comment.="<div style='width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;'><a href='http://egyptmart.online/dir.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Product & Suppliers</a> | <a href='http://egyptmart.online/sale-offers.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Sale Offers</a> | <a href='http://egyptmart.online/buyleads.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Buy Requests</a> | <a href='http://egyptmart.online/tenders.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Tenders</a>| <a href='http://egyptmart.online/auctions.php' style='color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;'>Auction</a></div>";
		$comment.='<div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">For more assistance ,feel free to call us at 201030029097.</p>
              </div><br/><br/>
              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;"><span style="    font-size: 17px;    font-weight: 600;">Warm Regards,</span> <br/><span><b><span style="color: blue;font-size: 14px;">EgyptMART <span style="color: blue;font-size: 14px;">Team</span></b></span><br/><span style="color: #da4e1e;font-size: 17px;    font-weight: 700;">We Promote Your Business !</span></p>
              </div>';
		$comment.="</div>";
	
	?>