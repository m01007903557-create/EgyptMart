<?php
$message1 ='<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              <div style="height: 100px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt=EgyptMART" src="http://egyptmart.online/images/logo.png" />
                  </div>
                  <div style="height:100px;width:43%;float:left;">
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">Today\'s Latest<br> Enquiry Reply
                      </h2>
                  </div>
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Enquiry</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">2016/07/29</span>
                  </div>
              </div>
              <div style="width:100%;color:#000000;">
                  <p style="font-size:16px;color:#000000"><strong>Dear '.$this->mem_name.',</strong>
              </p></div>
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                  <b>Enquiry Reply from <span style="color: blue">EgyptMART</span> Admin </b>
                </p>               
                <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Enquiry Details:</p>
              <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                    '.$enq_details.'    
                                                            
                                                        <div style="width: 90%; float: left;">
                                                            <span style="font-size:1.0em;font-weight:normal"></span>
                                                        </div>
              </div>
              <div style="clear:both">
                                                            
              </div>
              <br>
              <div style="clear:both">
				<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">Reply From Admin:
                                                        </p></div><br/>
			   <span style="font-size:1.0em;font-weight:normal">
			     <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Subject: '.$this->reply_subject.'</singleline>
							   </p>
<p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Message: '.html_entity_decode($this->reply_content).'</singleline>
							   </p>
							   </span>
				<br>
				<br>
				<div style="clear:both">';
				if($plans == 'Advertisements Requirements'){
					$message1 .= '<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">You can Reply :<a style="float: right" href="http://egyptmart.online/advertise-with-us.php">Reply NOW</a></p>';
				}
				else if($is_contact == 1){
					$message1 .= '<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">You can Reply :<a style="float: right" href="http://egyptmart.online/contact_us.php">Reply NOW</a></p>';
				}
				else { 
				
					$message1 .= '<p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">You can Reply :<a style="float: right" href="http://egyptmart.online/membership_plans.php">Reply NOW</a></p>';
					}
					
				$message1 .=  '</div>
                                                        <br>
              <table style="font-family:Arial,Helvetica,sans-serif;font-size:13px" cellpadding="0" cellspacing="0">
                <tbody>
                  <tr>
                    <td style="line-height:20px" valign="top">
                      <span style="color:blue">EgyptMART</span> Customer Support Team
                      <br>
                      Call us on +201030029097
                    </td>
                  </tr>
                </tbody>
              </table>
  <span style="color:rgb(171,172,172);font-size:11px">You are receiving this mailer as a registered member of <span style="color:blue">EgyptMART</span>.</span>
              </div>
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;">

              </div>
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://egyptmart.online/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | <a href="http://egyptmart.online/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | <a href="http://egyptmart.online/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | <a href="http://egyptmart.online/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>
              </div>
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px">You have received this mail virtue of your opt-in subscription for  Enquiry on <font style="color:blue;">EgyptMART</font>.</p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';