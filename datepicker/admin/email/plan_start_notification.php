<?php 
$cid=rand(1000,9999).md5($user_detail->bnsprof_id);
 $message1 ='<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 600px;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              <div style="height: 50px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART" src="http://egyptmart.online/images/logo.png"/>
                  </div>
                  
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Notification</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . date("Y/m/d") . '</span>
                  </div>
              </div>
<div style="width:100%;text-align:center;">
                      <h2 style="font-size: 20px; color:#2923ae; text-align: center; margin-top:0px; margin-bottom:0px;">Today\'s Latest<br> Membership Plan Status
                      </h2>
			  
               </div>
              <div style="width:100%;color:#000000;">
                  <p style="font-size:16px;color:#000000"><strong>Dear ' . $fullname . ',</strong>
              </p>
			  <p>We are pleased to promote your company business for a promotional period. NOW, You could list, update and manage your Products / Services / Tenders to display your business to Domestic Egypt Cities, ARABS and Global markets  as a  <strong style="color:#2923ae;" >'.$bus_detail->mst_name.'</strong></p></div>

              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                              
                <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Your Membership Plan Details:</p>
              <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Date:</label> ' . date("d-M-Y H:i T") . '</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Order Id:</label> ' . $billing_detail->bh_id . '</p>              
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Annual Amount:</label> ' . $bus_detail->mp_amount . '</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Paid Amount:</label> ' . $billing_detail->bh_amount . '</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Membership Plan:</label> ' . $bus_detail->mst_name . '</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Starting Period Time:</label> ' . $start_date . '</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Subscription End:</label> ' . $expiry_date . '</p>                                                       			 
              </div>
              <div style="clear:both">
                                                            
              </div>
			
			  
			  			  <div>
			  <a href="http://www.egyptmart.online/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.egyptmart.online/product-list.php" ><img src="http://egyptmart.online/images/plan.png" alt="" height="100%" width="100%"></a>
			  </div>
			  <div>
			  <p style="color: #002757;font-size: 18px;font-weight: 600;background-color: #eaeaea;padding: 10px;  margin-bottom: 5px;"> Contact Detail</p>
			  
			  <div style="margin-left:20px;font-size: 16px;">
			  <span><strong style="color: #000000;">'.$user_detail->bnsprof_compname.'</strong></span><br>';
			  if(trim($user_detail->bnsprof_address1) != '')
			  $message1 .='<span>'.$user_detail->bnsprof_address1.'</span><br>';
			  if(trim($user_detail->bnsprof_address2) != '')
			  $message1 .='<span>'.$user_detail->bnsprof_address2.'</span><br>';
			 
			  $city_state_string = '';
			  if(trim($user_detail->bnsprof_city) != '')
				  $city_state_string .= get_city_name(user_info($user_detail->usr_id,'bnsprof_city')).',';
			  
			  if(trim($user_detail->bnsprof_state) != '')
				  $city_state_string .= get_state_name(user_info($user_detail->usr_id,'bnsprof_state')).',';
			  
			  if(trim($user_detail->bnsprof_zipcode) != '')
				  $city_state_string .= user_info($user_detail->usr_id,'bnsprof_zipcode');
			  
			  
			  $message1 .='<span>'.$city_state_string.'</span><br>			  
			  <span>Mobile/Cell phone:'.$user_detail->bnsprof_ph1.'</span><br>
			  <span>E-mail:'.$user_detail->email.'</span>

			  
			  </div>
			  </div>
			   <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                              
                <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Your Business Page <span style="color:red;">@'.$user_detail->bnsprof_compname.'</span> is here to PREVIEW at :</p>
              <div style="width: 100%; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
			  	<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><a href="https://www.egyptmart.online/company/products.php?c='.$cid.'">https://www.egyptmart.online/company/products.php?c='.$cid.'</a></p>
			  </div>
			  </div>
			  
			  <div>
			  <p style="color: #000000;font-size: 19px;font-weight: 900;background-color: #eaeaea;padding: 10px;  margin-bottom: 5px;"> You may need to post <strong style="color:#da4e1e;font-size: 19px;font-weight: 900">FREE</strong>:</p>
			  <table align="center">
			  <tr>
			  <td style="padding: 5px;"><a href="http://www.egyptmart.online/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.egyptmart.online/product-sel-cat.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Products/Services</a></td>
			  <td style="padding: 5px;"><a href="http://www.egyptmart.online/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.egyptmart.online/post-buy-req.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Buy Requirments</a></td>			  
			  </tr>
			  <tr>
			  <td style="padding: 5px;"><a href="http://www.egyptmart.online/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.egyptmart.online/post-sell-offer.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Temporary Sale Offer</a></td>
			  <td style="padding: 5px;"><a href="http://www.egyptmart.online/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.egyptmart.online/post-tender.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">Tenders / Auctions</a></td>			  
			  </tr>
			 
			  </table>
			  </div>
             
                <br>
                <div style="clear:both">
                    <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em"><a href="http://www.egyptmart.online/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.egyptmart.online/why_egyptmart.php" style="font-size: 16px;font-weight: normal;">Click Here</a> to Unsubscribe  or Tell us your requirements  <a href="http://www.egyptmart.online/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.egyptmart.online/membership_plans.php"><strong style="color: #0f00d0;font-size: 15px;font-weight: 600;">NOW!</strong></a></p>
                </div>
                                                        <br>
  
              </div>
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;">

              </div>
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://egyptmart.online/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | <a href="http://egyptmart.online/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | <a href="http://egyptmart.online/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | <a href="http://egyptmart.online/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>
              </div>
              
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">Be noted that hereby you can delete / update  your displayed  business on EgyptMART instantly once you login to https://www.egyptmart.online/product-list.php and delete it, else, you are satisfy that  EgyptMRAT team will do their best to promote your business successfully in domestic and global. For more information,feel free to call us at +201030029097.
</p>
              </div><br/><br/>
              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;"><span style="    font-size: 17px;    font-weight: 600;">Warm Regards,</span> <br/><span><b><span style="color: blue;font-size: 16px;">EgyptMART <span style="color: blue;font-size: 16px;">Team</span></b></span><br/><span style="color: #da4e1e;font-size: 17px;    font-weight: 700;">We Promote Your Business !</span></p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';
?>