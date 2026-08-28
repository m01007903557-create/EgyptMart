<?php
/**
 * File: email/emailVerification.php
 * Version: PHP 8.3
 * Description: قوالب البريد الإلكتروني لصفحات التحقق والتسجيل
 * 
 * يحتوي هذا الملف على ثلاثة قوالب بريد إلكتروني:
 * 1. $message1 - قالب بريد التحقق للمستخدم
 * 2. $message2 - قالب فارغ (احتياطي)
 * 3. $message_admin - قالب إشعار التسجيل للإدارة
 */

// التحقق من وجود الجلسة ومعرف المستخدم
$user_id = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
$user_email = '';

if ($user_id > 0) {
    $user_email = stripslashes(user_info($user_id, 'email') ?? '');
}

// إنشاء رابط التحقق الفريد
$verification_token = rand(1000, 9999) . md5((string)$user_id);
$email_link = "https://" . ($_SERVER['SERVER_NAME'] ?? 'egyptmart.shop') . "/verifyUser.php?token=" . $verification_token;

// اسم المستخدم للتتريب
$userMessage = user_info($user_id, 'name_prefix') . "&nbsp;" . 
               user_info($user_id, 'fname') . "&nbsp;" . 
               user_info($user_id, 'lname');

// التاريخ الحالي
$current_date = date("Y/m/d");

// ==============================================
// القالب الفارغ (احتياطي)
// ==============================================
$message2 = '<div class="b9_m2 b10_m2" id="detable"></div>';

// ==============================================
// قالب بريد التحقق للمستخدم
// ==============================================
$message1 = '<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 600px;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              
              <!-- رأس البريد -->
              <div style="height: 100px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 40%; float: left;">
                      <img style="width: 100%;color: #00F;font-size: 22px;" alt="EgyptMART" src="http://egyptmart.shop/images/Mlogo.png"/>
                  </div>
                  
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">إشعار تحقق</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . $current_date . '</span>
                  </div>
              </div>
			  
              <!-- شعار -->
			  <div style="width:100%;text-align:center;">
                  <h2 style="font-size: 22px; color:#0101e4; text-align: center; margin-top:0px; margin-bottom:0px;">التجارة هنا مع الشركات والمصانع فى مصر والعالم</h2>
                  <span style="color:#c13406; font-size: 18px;">مصانع وشركات - تصدير وإستيراد - جملة وتجزئة - خدمات</span>
              </div>
              
              <!-- تحية المستخدم -->
              <div style="width:100%;color:#000000;">
                  <p style="font-size:16px;text-align: right;color:#000000">
                      <strong>السادة ' . $userMessage . ' :</strong>
                  </p>
                  <p style="line-height: 1.5em;text-align:center;font-size: 22px;color:#ffffff;font-weight: bold;background-color: #fe6430;margin: 0;font-family: Arial,Helvetica,sans-serif;padding: .4em .4em .4em;">
                      تمكنك المنصة من البيع والشراء داخل وخارج مصر جملة وتجزئة وإستيراد وتصدير - مصانع وشركات وخدمات - أهم 50,000 منتج و 15,000 تجارة
                  </p>
                  
                  <!-- رابط التحقق -->
                  <p style="line-height: 1.5em;text-align: center;font-size: 20px;font-weight: bold; margin: 0;font-family: Arial,Helvetica,sans-serif;padding: .4em .4em .4em;">
                      إضغط على الرابط لتبدأ البيع والشراء 
                      <a href="http://egyptmart.shop/verifyUser.php?token=' . $verification_token . '"><strong>إبدأ الآن</strong></a>
                  </p>
              </div>

              <!-- صورة البريد -->
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                  <div style="clear:both"></div>
                  <div>
                      <a href="' . $email_link . '">
                          <img src="http://egyptmart.shop/images/mail.png" alt="" height="100%" width="100%">
                      </a>
                  </div>
              </div>
			  
              <!-- روابط سريعة -->
              <div>
                  <p style="color: #0101e4;font-size: 22px;text-align: center;font-weight: 900;background-color: #eaeaea;padding: 10px;margin-bottom: 5px;">
                      ربمـا تحتـاج أيضا لنشـر
                  </p>
                  
                  <table align="center" cellpadding="5" cellspacing="0" border="0">
                      <tr>
                          <td style="padding: 5px;">
                              <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/product-sel-cat.php" 
                                 style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">
                                  منتجات وخدمات تجارية
                              </a>
                          </td>
                          <td style="padding: 5px;">
                              <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/post-buy-req.php" 
                                 style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">
                                  طلبات تسعير أو شراء
                              </a>
                          </td>			  
                      </tr>
                      <tr>
                          <td style="padding: 5px;">
                              <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/post-sell-offer.php" 
                                 style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">
                                  عـــروض بيـــــع خاصــة
                              </a>
                          </td>
                          <td style="padding: 5px;">
                              <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/post-tender.php" 
                                 style="padding: 5px;color: #0f00d0;font-size: 18px;font-weight: 600;text-decoration:none;">
                                  مناقصــات ومزايــدات
                              </a>
                          </td>			  
                      </tr>
                  </table>
			  
                  <div style="width: 100%;text-align: center;font-size: 15px;color: #000000;line-height: 1.5; font-weight: 400;">
                      <a href="" style="color: #000000; text-decoration:none;">
                          <strong>تلقى إستفسارات شراء من الداخل والخارج للبيع التجارى</strong>
                      </a><br>
                      <a href="" style="color: #000000; text-decoration:none;">
                          <strong>تلقى إشعارات بيع وشراء من مصدرين وشركات ومصانع</strong>
                      </a><br>
                  </div>
              </div>
             
              <!-- رابط الاتصال -->
              <br>
              <div style="clear:both">
                  <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                      <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/contact_us.php" 
                         style="font-size: 18px;font-weight: bold;">
                          أكتب إقتراحاتك وأحصل على أكبر عضوية
                      </a>
                  </p>
              </div>
              <br>
  
              <!-- فوتر -->
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;"></div>
              
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/product-sel-cat.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Product &amp; Suppliers
                  </a> | 
                  <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/post-sell-offer.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Sale Offers
                  </a> | 
                  <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/post-buy-req.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Buy Requests
                  </a> | 
                  <a href="http://egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=http://egyptmart.shop/post-tender.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Tenders
                  </a>
              </div>
              
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
                  <p style="margin:10px 0px 2px;font-size:12px;">
                      Be noted that hereby you are free to update / delete your company details once you login to https://egyptmart.shop/my-dashboard.php, else, it will be considered as an authorization by you to EgyptMART.shop & ARABYOS.com admins to display your business details.
                  </p>
              </div>
              <br/><br/>

              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;">
                  <p style="margin:10px 0px 2px;font-size:12px;">
                      <span style="font-size: 17px;font-weight: 600;">Warm Regards,</span> <br/>
                      <span>
                          <b>
                              <span style="color: blue;font-size: 13px;">EgyptMART Team</span>
                          </b>
                      </span><br/>
                      <span style="color: #da4e1e;font-size: 16px;font-weight: 700;">We Promote Your Business !</span>
                  </p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';

// ==============================================
// قالب إشعار التسجيل للإدارة
// ==============================================
$message_admin = '<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              
              <!-- رأس البريد -->
              <div style="height: 100px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img src="http://egyptmart.shop/images/logo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART">
                  </div>
                  <div style="height:100px;width:43%;float:left;">
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">
                          Today\'s Latest<br> Registration notification
                      </h2>
                  </div>
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Notification</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . $current_date . '</span>
                  </div>
              </div>
              
              <!-- تحية الإدارة -->
              <div style="width:100%;float:left;color:#000000;">
                  <p style="font-size:16px;color:#000000"><strong>Dear Administrator,</strong>
              </p></div>
              
              <!-- محتوى الإشعار -->
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                  <b>Notification From <span style="color: blue">EgyptMART</span></b>
                </p>               
                
                <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                    <p>A new user has registered on your website. Please check the details as below:</p>
                    
                    <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Name: <b>' . $userMessage . '</b>
                    </p>
                    
                    <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Email: <b>' . htmlspecialchars(user_info($user_id, 'email') ?? '') . '</b>
                    </p>
                    
                    <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:4px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Country: <b>' . htmlspecialchars(get_country_name(user_info($user_id, 'country') ?? 0)) . '</b>
                    </p>
                    
                    <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:8px;margin-left:10px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Mobile / Cell Phone: <b>' . 
                            htmlspecialchars(user_info($user_id, 'country_ph_code') ?? '') . '-' . 
                            htmlspecialchars(user_info($user_id, 'mobile1') ?? '') . '
                        </b>
                    </p>
                                                            
                    <div style="width: 90%; float: left;">
                        <span style="font-size:1.0em;font-weight:normal"></span>
                    </div>
                </div>
                
                <div style="clear:both"></div>
                <br>
              </div>
              
              <!-- خط فاصل -->
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;"></div>
              
              <!-- روابط سريعة -->
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=https://www.egyptmart.shop/product-list.php" 
                     style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">
                      سجل منتجات جديدة
                  </a> | 
                  <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=https://www.egyptmart.shop/post-sell-offer.php" 
                     style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">
                      أنشر عروضك الخاصة
                  </a> | 
                  <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=https://www.egyptmart.shop/post-buy-req.php" 
                     style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">
                      أنشر طلب تسعير
                  </a> | 
                  <a href="https://www.egyptmart.shop/sign-in.php?email=' . urlencode($user_email) . '&redirect=https://www.egyptmart.shop/post-tender.php" 
                     style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">
                      أنشر مناقصات مجانا
                  </a>
              </div>
              
              <!-- تذييل -->
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
                  <p style="margin:10px 0px 2px;font-size:12px;text-align: right">
                      نحيط سيادكم أنه يمكنكم وحدكم تقرير إذا ماكنتم تريدون الإستمرار فى عرض مضمون شركتكم وتجارتكم على المنصة أو تعديله أو حذفه طبقا لسياساتكم المعمول بها داخل شركتكم
                  </p>
              </div>
              <br/><br/>
              
              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;">
                  <p style="margin:10px 0px 2px;font-size:12px;">
                      <span style="font-size: 17px; font-weight: 600;">Warm Regards,</span> <br/>
                      <span>
                          <b>
                              <span style="color: blue;font-size: 14px;">Egypt MART Team</span>
                          </b>
                      </span><br/>
                      <span style="color: #da4e1e;font-size: 17px; font-weight: 700;">We Promote Your Business !</span>
                  </p>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';
?>