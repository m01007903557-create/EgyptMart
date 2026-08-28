<?php
/**
 * File: email/contact_us.php
 * Version: PHP 8.3
 * Description: قوالب البريد الإلكتروني لصفحة الاتصال بنا
 * 
 * يحتوي هذا الملف على قالبين للبريد الإلكتروني:
 * 1. $message1 - قالب تأكيد استلام رسالة الاتصال (للمستخدم)
 * 2. $message2 - قالب إشعار الإدارة باستفسار جديد
 */

// التحقق من وجود المتغيرات الأساسية
$current_date = date("Y/m/d");
$user_email = isset($usr_email) ? urlencode($usr_email) : '';

// اسم المستخدم للتحية
$user_greeting = isset($suname) ? htmlspecialchars($suname) : 'المستخدم';
?>
<?php
// ==============================================
// القالب الأول: تأكيد استلام رسالة الاتصال (للمستخدم)
// ==============================================
$message1 = '<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:550px;word-wrap:break-word;" id="wbr">
            <div style="width: 60%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              
              <!-- رأس البريد -->
              <div style="height: 100px; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img src="http://egyptmart.shop/images/logo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART">
                  </div>
                  
                  <div style="height:100px;width:43%;float:left;">
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">
                          متابعة تواصل اليوم
                      </h2>
                  </div>
                  
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Enquiry</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . $current_date . '</span>
                  </div>
              </div>
              
              <!-- تحية المستخدم -->
              <div style="width:100%;color:#000000;">
                  <p style="font-size:16px;text-align:right;color:#000000">
                      <strong>' . $user_greeting . ' : الســادة</strong>
                  </p>
              </div>
              
              <!-- محتوى الرد -->
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                  شكرا لتواصلك مع منصة إيجيبت مارت أونلاين ، سوف نتواصل معك قريبا 
                </p>                                 
              </div>
              
              <!-- خط فاصل -->
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;"></div>
              
              <!-- روابط سريعة -->
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="http://www.egyptmart.shop/sign-in.php?email=' . $user_email . '&redirect=http://egyptmart.shop/dir.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Product &amp; Suppliers
                  </a> | 
                  <a href="http://www.egyptmart.shop/sign-in.php?email=' . $user_email . '&redirect=http://egyptmart.shop/sale-offers.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Sale Offers
                  </a> | 
                  <a href="http://www.egyptmart.shop/sign-in.php?email=' . $user_email . '&redirect=http://egyptmart.shop/buyleads.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Buy Requests
                  </a> | 
                  <a href="http://www.egyptmart.shop/sign-in.php?email=' . $user_email . '&redirect=http://egyptmart.shop/tenders.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Tenders
                  </a>
              </div>
              
              <!-- تذييل -->
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
                  <p style="margin:10px 0px 2px">
                      You have received this mail by virtue of your contact enquiry on <font style="color:blue;">EgyptMART</font>.
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
// القالب الثاني: إشعار الإدارة باستفسار جديد
// ==============================================
$message2 = '<div class="b9_m2 b10_m2" id="detable">
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
                          Today\'s Latest<br> Enquiry Reply
                      </h2>
                  </div>
                  
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Enquiry</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . $current_date . '</span>
                  </div>
              </div>
              
              <!-- تحية الإدارة -->
              <div style="width:100%;float:left;color:#000000;">
                  <p style="font-size:16px;color:#000000">
                      <strong>Dear Administrator,</strong>
                  </p>
              </div>
              
              <!-- عنوان الإشعار -->
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                  <b>Enquiry from <span style="color: blue">EgyptMART</span> user</b>
                </p>
                
                <!-- تفاصيل الاتصال -->
                <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                    Enquiry Details:
                </p>
                
                <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">';
                   
                // إضافة تفاصيل الاستفسار إذا كانت متوفرة (من الكلاس)
                if (isset($this) && is_object($this)) {
                    $message2 .= '<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Name: ' . htmlspecialchars(($this->cu_fname ?? '') . ' ' . ($this->cu_lname ?? '')) . '</p>';
                    $message2 .= '<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Contact Number: ' . htmlspecialchars($this->cu_contactnumber ?? '') . '</p>';
                    $message2 .= '<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Email: ' . htmlspecialchars($this->cu_email ?? '') . '</p>';
                    $message2 .= '<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Country: ' . htmlspecialchars($this->cu_country ?? '') . '</p>';
                    $message2 .= '<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">State: ' . htmlspecialchars($this->cu_state ?? '') . '</p>';
                    $message2 .= '<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">Comments: ' . htmlspecialchars($this->cu_comments ?? '') . '</p>';
                }
                
$message2 .= '<div style="width: 90%; float: left;">
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
                  <a href="http://egyptmart.shop/dir.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Product &amp; Suppliers
                  </a> | 
                  <a href="http://egyptmart.shop/sale-offers.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Sale Offers
                  </a> | 
                  <a href="http://egyptmart.shop/buyleads.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Buy Requests
                  </a> | 
                  <a href="http://egyptmart.shop/tenders.php" 
                     style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">
                      Tenders
                  </a>
              </div>
            </div>
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>';
?>