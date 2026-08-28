<?php
$message1='<div class="b9_m2 b10_m2" id="detable">
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
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">Today\'s Latest<br> Buy Request Enquiry
                      </h2>
                  </div>
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Notification</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">'.date("Y/m/d").'</span>
                  </div>
              </div>
              <div style="width:100%;color:#000000;">
                  <p style="font-size:14px;color:#000000"><strong>Dear '.$buyer_details->name_prefix.$buyer_details->fname.' '.$buyer_details->lname.',</strong>
              </p></div>
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:12px;margin:0;padding:.5em 0 0.5em;line-height:1.4em;">
                 <b>We&nbsp;are pleased</b> to send the latest suppliers interested in your Buy request :<br/><b>Titled:</b><a href="http://arabyos.com/buyleads-details.php?id='. rand(1000,9999).md5($buyer_details->br_id).'">'.$buyer_details->br_pd_name.'</a>
                </p>  
              </div>
			  <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center;background-color:#eaeaea;">
                  <b>Supplier Contact Details</b>
                </p>
              <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                   <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Supplier Name:</label> '.$row_usr->bnsprof_compname.'</p>
					<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Contact Person:</label> ';
					$image = 'http://www.arabyos.com/server/php/files/thumbnail/'.$row_usr->image;
					
					if (@getimagesize($image)) {
						$message1 .='<img src="'.$image.'"/>';
					}
					$phone = '';
					if($row_usr->bnsprof_phcode1 != '') {
						if(strpos($row_usr->bnsprof_phcode1, '+') === false){
							$phone = '+'.$row_usr->bnsprof_phcode1;
						}					
					}
					$phone .= $row_usr->mobile1;
					
					$message1 .=$row_usr->fname.' '.$row_usr->lname.'</p>
					<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Email:</label> '.$row_usr->email.'</p>
					<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Mobile Number:</label> '.$phone.'</p>
					<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left"><label style="font-weight:bold;">Country/State:</label> '.$user_country.' / '.$user_state.'</p>
																				
                                                        <div style="width: 90%; float: left;">
                                                            <span style="font-size:1.0em;font-weight:normal"></span>
                                                        </div>
              </div>
              <div style="clear:both">
                                                            
              </div>
              <br>
              </div>
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;">

              </div>
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://arabyos.com/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | <a href="http://arabyos.com/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | <a href="http://arabyos.com/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | <a href="http://arabyos.com/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>
              </div><br/>
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">For any assistance, feel free to call us at +201030029097 or just reply to this mail.<br/><a href="mailto:info@arabyos.com">mailto:info@arabyos.com</a></p>
              </div><br/><br/>
			  <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">Thanks for choosing <b><span style="color: blue">ARABYOS</span></b> as your business platform,<br/>Customers Helpdesk<br/>arabyos.com</p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';
?>