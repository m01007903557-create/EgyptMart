<?php
$message2='<div class="b9_m2 b10_m2" id="detable">
  
</div>';
$userMessage = user_info($_SESSION['uid_indm'],'name_prefix')."&nbsp;".user_info($_SESSION['uid_indm'],'fname') ."&nbsp;".user_info($_SESSION['uid_indm'],'lname');
$email_link="https://".$_SERVER['SERVER_NAME']."/verifyUser.php?token=".rand(1000,9999).md5($_SESSION['uid_indm']);
$message1 ='<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 600px;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              <div style="height: 100px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 40%; float: left;">
                      <img style="width: 100%;color: #00F;font-size: 22px;" alt="EgyptMART" src="http://egyptmart.online/images/Mlogo.png"/>
                  </div>
                  
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">إشعار تحقق</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . date("Y/m/d") . '</span>
                  </div>
              </div>
			  <div style="width:100%;text-align:center;">
  <h2 style="font-size: 22px; color:#0101e4; text-align: center; margin-top:0px; margin-bottom:0px;" > التجارة هنا مع الشركات والمصانع فى مصر والعالم</h2><span style="color:#c13406; font-size: 18px;">مصانع وشركات - تصدير وإستيراد - جملة وتجزئة - خدمات 
 </span>
               </div>
              <div style="width:100%;color:#000000;">
                  <p style="font-size:16px;text-align: right;color:#000000"><strong>    ' . $userMessage. ' : السـادة </strong>
              </p>
<p style="line-height: 1.5em;text-align:center;font-size: 22px;color:#ffffff;font-weight: bold;background-color: #fe6430;margin: 0;font-family: Arial,Helvetica,sans-serif;  padding: .4em .4em .4em;"> تمكنك المنصة من البيع والشراء داخل وخارج مصر جملة وتجزئة وإستيراد وتصدير - مصانع وشركات وخدمات - أهم 50,000 منتج و 15,000 تجارة  
</p>

	   
<p style="line-height: 1.5em;text-align: center;font-size: 20px;font-weight: bold; margin: 0;font-family: Arial,Helvetica,sans-serif;  padding: .4em .4em .4em;"> إضغط على الرابط لتبدأ البيع والشراء <a href=http://'.$_SERVER['SERVER_NAME'].'/verifyUser.php?token='.rand(1000,9999).md5($_SESSION['uid_indm']).'> إبدأ الآن </strong></a></p>
							   
			  

              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                              
               
              <div style="clear:both">
                                                            
              </div>
			 <div>
			  <a href='.$email_link.' ><img src="http://egyptmart.online/images/mail.png" alt="" height="100%" width="100%"></
			
			  </div>
			  </div>
			  
			  <div>
			  <p style="color: #0101e4;font-size: 22px;text-align: center;font-weight: 900;background-color: #eaeaea;padding: 10px;  margin-bottom: 5px;"> ربمـا تحتـاج أيضا لنشـر   <strong style="color:#da4e1e;font-size: 15px;font-weight: 900"></strong> </p>
			  <table align="center">
			  <tr>
			  <td style="padding: 5px;"><a href="http://egyptmart.online/sign-in.php?email='.stripslashes(user_info($_SESSION['uid_indm'],'email')).'&redirect=http://egyptmart.online/product-sel-cat.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">منتجات وخدمات تجارية</a></td>
			  <td style="padding: 5px;"><a href="http://egyptmart.online/sign-in.php?email='.stripslashes(user_info($_SESSION['uid_indm'],'email')).'&redirect=http://egyptmart.online/post-buy-req.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">طلبات تسعير أو شراء</a></td>			  
			  </tr>
			  <tr>
			  <td style="padding: 5px;"><a href="http://egyptmart.online/sign-in.php?email='.stripslashes(user_info($_SESSION['uid_indm'],'email')).'&redirect=http://egyptmart.online/post-sell-offer.php" style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">عـــروض بيـــــع خاصــة</a></td>
			  <td style="padding: 5px;"><a href="http://egyptmart.online/sign-in.php?email='.stripslashes(user_info($_SESSION['uid_indm'],'email')).'&redirect=http://egyptmart.online/post-tender.php " style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">مناقصــات ومزايــدات</a></td>			  
			  </tr>
			 
			  </table>
			  
			  <div style="width: 100%;text-align: center;font-size: 15px;color: #000000;line-height: 1.5; font-weight: 400;">
				<a href="" style="color: #000000;"> <strong> تلقى إستفسارات شراء من الداخل والخارج للبيع التجارى  </strong></a><br>
				<a href="" style="color: #000000;"> <strong> تلقى إشعارات بيع وشراء من مصدرين وشركات ومصانع  <strong></a><br>
				</div>
			  </div>
             
                <br>
                <div style="clear:both">
                    <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em"><a href="http://egyptmart.online/sign-in.php?email='.stripslashes(user_info($_SESSION['uid_indm'],'email')).'&redirect=http://egyptmart.online/contact_us.php" style="font-size: 18px;font-weight: bold;">    أكتب إقتراحاتك وأحصل على أكبر عضوية    </a>   <a href="http://egyptmart.online/sign-in.php?email='.stripslashes(user_info($_SESSION['uid_indm'],'email')).'&redirect=http://egyptmart.online/contact_us.php"><strong style="color: #0f00d0;font-size: 15px;font-weight: 600;"></strong></a></p>
                </div>
                                                        <br>
  
              </div>
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;">

              </div>
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://egyptmart.online/sign-in.php?email='.$usr_email.'&redirect=http://egyptmart.online/product-sel-cat.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | <a href="http://egyptmart.online/sign-in.php?email='.$usr_email.'&redirect=http://egyptmart.online/post-sell-offer.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | <a href="http://egyptmart.online/sign-in.php?email='.$usr_email.'&redirect=http://egyptmart.online/post-buy-req.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | <a href="http://egyptmart.online/sign-in.php?email='.$usr_email.'&redirect=http://egyptmart.online/post-tender.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>

              </div>
              
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;">Be noted that hereby you are free to update / delete your  company details once you login to https://egyptmart.online/my-dashboard.php, else, it will be considered as an authorization by you to EgyptMART.online & ARABYOS.com admins to display your business details. For more information, feel free to call us at 201030029097.</p>
              </div><br/><br/>


              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;"><span style="    font-size: 17px;    font-weight: 600;">Warm Regards,</span> <br/><span><b><span style="color: blue;font-size: 13px;">EgyptMART<span style="color: blue;font-size: 13px;">Team</span></b></span><br/><span style="color: #da4e1e;font-size: 16px;    font-weight: 700;">We Promote Your Business !</span></p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';
$message_admin ='<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              <div style="height: 100px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img src="http://egyptmart.online/images/logo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART">
                  </div>
                  <div style="height:100px;width:43%;float:left;">
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">Today\'s Latest<br> Registration notification
                      </h2>
                  </div>
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Notification</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">'. date("Y/m/d").'</span>
                  </div>
              </div>
              <div style="width:100%;float:left;color:#000000;">
                  <p style="font-size:16px;color:#000000"><strong>Dear Administrator,</strong>
              </p></div>
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                  <b>Notification From <span style="color: blue">EgyptMART</span></b>
                </p>               
              <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                    <singleline label="Title">A new user has registered on your website. Please check the details as below:</singleline>
							   </p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Name: <b>'.user_info($_SESSION['uid_indm'],'name_prefix')."&nbsp;".user_info($_SESSION['uid_indm'],'fname') ."&nbsp;".user_info($_SESSION['uid_indm'],'lname').'</b></singleline>
							   </p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Email: <b>'.user_info($_SESSION['uid_indm'],'email').'</b></singleline>
							   </p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Country: <b>'.get_country_name(user_info($_SESSION['uid_indm'],'country')).'</b></singleline>
							   </p>
							   <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:8px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
							   <singleline label="Title">Mobile / Cell Phone: <b>'.user_info($_SESSION['uid_indm'],'country_ph_code').'-'.user_info($_SESSION['uid_indm'],'mobile1').'</b></singleline>
							   </p>
                                                            
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
                  <a href="https://www.egyptmart.online/sign-in.php?email='.$usr_email.'&redirect=https://www.egyptmart.online/product-list.php" style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">سجل منتجات جديدة </a> | <a href="https://www.egyptmart.online/sign-in.php?email='.$usr_email.'&redirect=https://www.egyptmart.online/post-sell-offer.php" style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر عروضك الخاصة</a> | <a href="https://www.egyptmart.online/sign-in.php?email='.$usr_email.'&redirect=https://www.egyptmart.online/post-buy-req.php" style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر طلب تسعير</a> | <a href="https://www.egyptmart.online/sign-in.php?email='.$usr_email.'&redirect=https://www.egyptmart.online/post-tender.php" style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">أنشر مناقصات مجانا</a>
              </div>
              
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;text-align: right"> نحيط سيادكم أنه يمكنكم وحدكم تقرير إذا ماكنتم تريدون الإستمرار فى عرض مضمون شركتكم وتجارتكم على المنصة أو تعديله أو حذفه طبقا لسياساتكم المعمول بها داخل شركتكم للإستفسار يمكنكم الاتصال على محمول </p>
              </div><br/><br/>
              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;"><p style="margin:10px 0px 2px;font-size:12px;"><span style="    font-size: 17px;    font-weight: 600;">Warm Regards,</span> <br/><span><b><span style="color: blue;font-size: 14px;">Egypt MART<span style="color: blue;font-size: 14px;">  Team</span></b></span><br/><span style="color: #da4e1e;font-size: 17px;    font-weight: 700;">We Promote Your Business !</span></p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';
?> 