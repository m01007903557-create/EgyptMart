<?php
/**
 * File: email/buylead_notification.php

 * Version: PHP 8.3
 * Description: قالب البريد الإلكتروني لإشعار المشتري بموردين مهتمين بطلبه
 * 
 * هذا الملف يحتوي على قالب البريد الإلكتروني الذي يتم إرساله للمشتري
 * عند وجود موردين مهتمين بطلب الشراء الخاص به
 */

// التحقق من وجود المتغيرات الأساسية
$current_date = date("Y/m/d");

// اسم المشتري
$buyer_name = '';
if (isset($buyer_details)) {
    $buyer_name = ($buyer_details->name_prefix ?? '') . ' ' . 
                  ($buyer_details->fname ?? '') . ' ' . 
                  ($buyer_details->lname ?? '');
}

// رابط طلب الشراء
$buy_request_link = '';
if (isset($buyer_details) && isset($buyer_details->br_id) && isset($buyer_details->br_pd_name)) {
    $buy_request_link = 'http://egyptmart.shop/buyleads-details.php?id=' . 
                        rand(1000, 9999) . 
                        md5((string)$buyer_details->br_id);
}

// معلومات المورد
$supplier_name = $row_usr->bnsprof_compname ?? '';
$supplier_contact = ($row_usr->fname ?? '') . ' ' . ($row_usr->lname ?? '');
$supplier_email = $row_usr->email ?? '';

// معالجة رقم الهاتف
$phone = '';
if (!empty($row_usr->bnsprof_phcode1)) {
    if (strpos($row_usr->bnsprof_phcode1, '+') === false) {
        $phone = '+' . $row_usr->bnsprof_phcode1;
    } else {
        $phone = $row_usr->bnsprof_phcode1;
    }
}
$phone .= $row_usr->mobile1 ?? '';

// صورة المورد
$supplier_image_html = '';
if (!empty($row_usr->image)) {
    $image_url = 'http://www.egyptmart.shop/server/php/files/thumbnail/' . $row_usr->image;
    
    // محاولة التحقق من وجود الصورة (مع تجاهل الأخطاء)
    $headers = @get_headers($image_url);
    if ($headers && strpos($headers[0], '200') !== false) {
        $supplier_image_html = '<img src="' . htmlspecialchars($image_url) . '" alt="Supplier Image" style="max-width:100px; max-height:100px;"/>';
    }
}

// الموقع الجغرافي
$user_country_display = isset($user_country) ? htmlspecialchars($user_country) : '';
$user_state_display = isset($user_state) ? htmlspecialchars($user_state) : '';
?>
<?php
// ==============================================
// قالب البريد الإلكتروني لإشعار المشتري
// ==============================================
$message1 = '<div class="b9_m2 b10_m2" id="detable">
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
                          Today\'s Latest<br> Buy Request Enquiry
                      </h2>
                  </div>
                  
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Notification</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . $current_date . '</span>
                  </div>
              </div>
              
              <!-- تحية المشتري -->
              <div style="width:100%;color:#000000;">
                  <p style="font-size:14px;color:#000000">
                      <strong>Dear ' . htmlspecialchars($buyer_name) . ',</strong>
                  </p>
              </div>
              
              <!-- محتوى الإشعار -->
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:12px;margin:0;padding:.5em 0 0.5em;line-height:1.4em;">
                 <b>We&nbsp;are pleased</b> to send the latest suppliers interested in your Buy request :<br/>
                 <b>Titled:</b> <a href="' . htmlspecialchars($buy_request_link) . '">' . htmlspecialchars($buyer_details->br_pd_name ?? '') . '</a>
                </p>  
              </div>
              
              <!-- تفاصيل المورد -->
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center;background-color:#eaeaea;">
                  <b>Supplier Contact Details</b>
                </p>
                
                <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                   
                   <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                       <label style="font-weight:bold;">Supplier Name:</label> ' . htmlspecialchars($supplier_name) . '
                   </p>
                   
                   <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                       <label style="font-weight:bold;">Contact Person:</label> ' . $supplier_image_html . ' ' . htmlspecialchars($supplier_contact) . '
                   </p>
                   
                   <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                       <label style="font-weight:bold;">Email:</label> ' . htmlspecialchars($supplier_email) . '
                   </p>
                   
                   <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                       <label style="font-weight:bold;">Mobile Number:</label> ' . htmlspecialchars($phone) . '
                   </p>
                   
                   <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                       <label style="font-weight:bold;">Country/State:</label> ' . $user_country_display . ' / ' . $user_state_display . '
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
              
              <br/>
              
              <!-- معلومات الاتصال -->
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
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
                      Customers Helpdesk<br/>
                      egyptmart.shop
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