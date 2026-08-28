<?php 
$cid=rand(1000,9999).md5($row->bnsprof_id);
$message1 ='
<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 600px;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              <div style="height: 50px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMRAT" src="http://arabyos.com/images/logo.png"/>
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
			  <p>Kindly note that your membership as a JUNIOR Supplier will expire on '.$expiry_date.'.</p></div>

              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                              
                <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Your Membership Plan Details:</p>
              <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Date:</label>'.date("d-M-Y H:i T").'</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Order Id:</label>  '.$billing_detail->bh_id.'</p>              
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Annual Amount:</label>  '.$row->mp_amount.'</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Paid Amount:</label> '.$billing_detail->bh_amount.'</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Membership Plan:</label>  '.$plan_name.'</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Starting Period Time:</label> '.$start_date.'</p>
              <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Subscription End:</label> '.$expiry_date.'</p>     
			  
              </div>
              <div style="clear:both">
                                                            
              </div>
			  
			  
			  			  <div>
			  <a href="http://www.arabyos.com
/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.arabyos.com
/product-sel-cat.php " ><img src="http://arabyos.com/admin/images/RENEWAL_mail_Notification.png" alt="" height="100%" width="100%"></a>
			  </div>
			   <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                              
                <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Your Business Page <span style="color:red;">@'.$row->bnsprof_compname.'</span> is here to PREVIEW at :</p>
              <div style="width: 100%; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
			  	<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><a href="https://www.arabyos.com/company/products.php?c='.$cid.'">https://www.arabyos.com/company/products.php?c='.$cid.'</a></p>
			  </div>
			  </div>
			  <div>
			  <p style="color: #000;font-size: 18px;font-weight: 600;background-color: #eaeaea;padding: 10px;  margin-bottom: 5px;">      You may need to take this service right NOW  :        </p>
			  
			  <div style="margin-left:20px;font-size: 16px;">
			 
			  <table>
  <tr>
    <th style="vertical-align: top;"> <img src="http://arabyos.com/admin/images/sponser.png" alt="" height="" width=""></th>
	<th></th>
    <th style="padding-left: 15px;"><p style="text-align: left;"><span style="padding: 5px;font-size: 13px;">-</span>Unique Showcase as an Industry/ Trade leader</p>
<p style="text-align: left;"><span style="padding: 5px;font-size: 15px;">-</span>Get Top Priority Premium Listing</p>
<p style="text-align: left;"><span style="padding: 5px;font-size: 15px;">-</span>Exclusive access to Buy Leads/Tenders worth</p>
<p style="text-align: left;"><span style="padding: 5px;font-size: 15px;">-</span>Prestigious Sliders, Videos and Logo Image</p>
<p style="text-align: left;"><span style="padding: 5px;font-size: 15px;">-</span>Free Newsletters Email Marketing</p>
<p style="text-align: left;"><span style="padding: 5px;font-size: 15px;">-</span>Free Advertising Banners</p>
<p style="text-align: left;"><span style="padding: 5px;font-size: 15px;">-</span>Rank of Buyers to Find Your Products</p>
<p style="text-align:right;"><a href="http://www.arabyos.com" style="text-decoration: none;color:blue;">Learn More ></a></p>

	
</th>
    
  </tr>
 
</table>
			  </div>
			  </div>
			  
			  
             
                <br>
                <div style="clear:both">
                    <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em"><a href="http://www.arabyos.com/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.arabyos.com/why_egyptmart.php" style="font-size: 16px;font-weight: normal;">Click Here</a> to Unsubscribe  or Tell us your requirements  <a href="http://www.arabyos.com/sign-in.php?email='.stripslashes($user_detail->email).'&redirect=http://www.arabyos.com/membership_plans.php" style="text-decoration: none;"><strong style="color: #0f00d0;font-size: 19px;font-weight: 600;">NOW!</strong></a></p>
                </div>
                                                        <br>
  
              </div>
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;">

              </div>
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://arabyos.com/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | <a href="http://arabyos.com/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | <a href="http://arabyos.com/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | <a href="http://arabyos.com/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>
              </div>
              
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">For any assistance ,feel free to call us at 201030029097 or just reply to this mail.</p>
              </div><br/><br/>
              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;"><span style="    font-size: 17px;    font-weight: 600;">Warm Regards,</span> <br/><span><b><span style="color: blue;font-size: 16px;">EgyptMRAT <span style="color: blue;font-size: 16px;">Team</span></b></span><br/><span style="color: #da4e1e;font-size: 17px;    font-weight: 700;">We Promote Your Business !</span></p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';
?>
