<?php
// التحقق من وجود الصورة
$image = 'http://www.egyptmart.shop/server/php/files/thumbnail/' . ($row_usr->image ?? '');
$orig_image = $image;
$img = '';

if (!empty($row_usr->image) && @getimagesize($orig_image)) {
    $img = '<img src="http://www.egyptmart.shop/server/php/files/thumbnail/' . htmlspecialchars($row_usr->image) . '" alt="Company Image"/>';
}

// إنشاء عنوان الاستفسار إذا كان موجوداً
$headline = '';
if (!empty($lead_headline)) {
    $headline = '<div style="height:100px;width:100%;float:left;font-size: 16px;">
                    <h3 style="text-align: center; margin-top:0px; margin-bottom:0px;">
                        : إستفسار شراء عن  - ' . htmlspecialchars($lead_headline) . '
                    </h3>
                 </div>';
}

// التاريخ الحالي
$current_date = date("Y/m/d");

// تنظيف نص الرسالة
$clean_message = isset($msg_message) ? stripslashes($msg_message) : '';
$clean_message = htmlspecialchars($clean_message);

// معلومات المرسل
$sender_name = ($row_usr->name_prefix ?? '') . ' ' . ($row_usr->fname ?? '') . ' ' . ($row_usr->lname ?? '');
$sender_company = $row_usr->bnsprof_compname ?? '';
$sender_address = $row_usr->bnsprof_address1 ?? '';
$sender_city = isset($row_usr->bnsprof_city) ? get_city_name((int)$row_usr->bnsprof_city) : '';
$sender_country = isset($row_usr->country) ? get_country_name((int)$row_usr->country) : '';
$sender_phone = ($row_usr->country_ph_code ?? '') . '-' . ($row_usr->mobile1 ?? '');
$sender_email = $row_usr->email ?? '';

// معلومات المستلم
$recipient_name = '';
if (isset($row_to)) {
    $recipient_name = ($row_to->name_prefix ?? '') . ' ' . ($row_to->fname ?? '') . ' ' . ($row_to->lname ?? '');
}

// بريد المستخدم (للاستخدام في الروابط)
$user_email = isset($usr_email) ? urlencode($usr_email) : '';
?>
<?php
// ==============================================
// قالب البريد الإلكتروني للاستفسار
// ==============================================
$message1 = '<div class="b9_m2 b10_m2" id="detable">
  <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
    <tbody>
      <tr class="f5_m2">
        <td class="sh_m2">
          <span style="width:750px;word-wrap:break-word;" id="wbr">
            <div style="width: 90%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
              
              <!-- رأس البريد -->
              <div style="height: auto; width: 100%; float: left; ">
                  <div style="height: 100px; width: 30%; float: left;">
                      <img src="http://egyptmart.shop/images/Mlogo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART">
                  </div>
                  
                  <div style="height:100px;width:43%;float:left;">
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">
                          إستفسار شراء
                      </h2>
                  </div>
                  
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Notification</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . $current_date . '</span>
                  </div>
                  
                  ' . $headline . '
              </div>
              
              <!-- تحية المستلم -->
              <div style="width:100%;color:#000000;">
                  <div style="width:100%;color:#000000;">
                      <p style="font-size:16px;text-align:right;color:#000000">
                          <strong>الســادة ' . htmlspecialchars($recipient_name) . ' :</strong>
                      </p>
                  </div>
              </div>
              
              <!-- عنوان الاستفسار -->
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                  <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                      <b>إستفسار شراء من ' . htmlspecialchars($sender_company) . '</b>
                  </p>
              </div>
              
              <!-- تفاصيل الاستفسار -->
              <div style="max-width:100%;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                  <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center;background-color:#eaeaea;">
                      <b>تفاصيـل الإستفســار</b>
                  </p>
                  
                  <div style="width: 100%; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em;word-break: break-all;">
                      <span style="font-size:1.0em;font-weight:normal;word-break: break-all;">' . nl2br($clean_message) . '</span>
                      
                      <div style="width: 100%; float: left;">
                          <span style="font-size:1.0em;font-weight:normal"></span>
                      </div>
                  </div>
              </div>
              
              <!-- تفاصيل الاتصال بالمرسل -->
              <div style="max-width:100%;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                  <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center;background-color:#eaeaea;">
                      <b>تفاصيل الإتصال بالراسـل</b>
                  </p>
                  
                  <div style="width: 100%; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                      <div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                          ' . htmlspecialchars($sender_name) . '<br>
                          ' . htmlspecialchars($sender_address) . '<br>
                          ' . htmlspecialchars($sender_city) . ', ' . htmlspecialchars($sender_country) . '<br>
                          Mobile/ Cell Phone: ' . htmlspecialchars($sender_phone) . '<br>
                          E-mail: <a href="mailto:' . htmlspecialchars($sender_email) . '" target="_blank">' . htmlspecialchars($sender_email) . '</a><br>
                      </div>
                      
                      <div style="width: 100%; float: left;">
                          <span style="font-size:1.0em;font-weight:normal"></span>
                      </div>
                  </div>
              </div>
              
              <div style="clear:both"></div>
              <br>
              
              <!-- خط فاصل -->
              <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;"></div>
              
              <!-- روابط سريعة -->
              <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                  <a href="https://www.egyptmart.shop/sign-in.php?email=' . $user_email . '&redirect=https://www.egyptmart.shop/product-list.php" 
                     style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">
                      سجل منتجات جديدة
                  </a> | 
                  <a href="https://www.egyptmart.shop/sign-in.php?email=' . $user_email . '&redirect=https://www.egyptmart.shop/post-sell-offer.php" 
                     style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">
                      أنشر عروضك الخاصة
                  </a> | 
                  <a href="https://www.egyptmart.shop/sign-in.php?email=' . $user_email . '&redirect=https://www.egyptmart.shop/post-buy-req.php" 
                     style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">
                      أنشر طلب تسعير
                  </a> | 
                  <a href="https://www.egyptmart.shop/sign-in.php?email=' . $user_email . '&redirect=https://www.egyptmart.shop/post-tender.php" 
                     style="color:#00c118;text-decoration:none;font-size:18px;font-weight:bold;">
                      أنشر مناقصات مجانا
                  </a>
              </div>
              
              <br/>
              
              <!-- معلومات الاتصال -->
              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;">
                  <p style="margin:10px 0px 2px;font-size:12px;">
                      For any assistance, feel free to call us at +201030029097 or just reply to this mail.<br/>
                      <a href="mailto:info@egyptmart.shop">info@egyptmart.shop</a>
                  </p>
              </div>
              
              <br/><br/>
              
              <!-- توقيع -->
              <div style="width:100%;padding-left: 0px;text-align:center;color:#808080;">
                  <p style="margin:10px 0px 2px;font-size:12px;">
                      Thanks for choosing <b><span style="color: blue">EgyptMART</span></b> as your business platform,<br/>
                      Customer Care Helpdesk<br/>
                      EgyptMART.shop
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