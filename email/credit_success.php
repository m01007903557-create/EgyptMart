<?php
/**
 * File: email/credit_success.php

 * Version: PHP 8.3
 * Description: قالب البريد الإلكتروني لتأكيد شراء الرصيد (Credits)
 * 
 * هذا الملف يحتوي على قالب البريد الإلكتروني الذي يتم إرساله عند شراء رصيد
 */

// التحقق من وجود المتغيرات الأساسية
$current_date = date("Y/m/d");
$current_datetime = date("d-M-Y H:i T");

// اسم المستخدم
$user_name = '';
if (isset($row_u)) {
    $user_name = ($row_u->name_prefix ?? '') . ' ' . 
                 ($row_u->fname ?? '') . ' ' . 
                 ($row_u->lname ?? '');
}

// بيانات المعاملة
$order_id_display = isset($order_id) ? htmlspecialchars($order_id) : 'غير متوفر';
$amount_display = isset($amount) ? htmlspecialchars($amount) : 'غير متوفر';
$credits_display = (isset($row) && isset($row->mp_credits)) ? htmlspecialchars($row->mp_credits) : 'غير متوفر';
?>
<?php
// ==============================================
// قالب البريد الإلكتروني لتأكيد شراء الرصيد
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
                      <img style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART" src="http://egyptmart.shop/images/logo.png"/>
                  </div>
                  
                  <div style="height:100px;width:43%;float:left;">
                      <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">
                          Today\'s Latest<br> Credits Purchase Status
                      </h2>
                  </div>
                  
                  <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                      <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Notification</span>
                      <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . $current_date . '</span>
                  </div>
              </div>
              
              <!-- تحية المستخدم -->
              <div style="width:100%;float:left;color:#000000;">
                  <p style="font-size:16px;color:#000000">
                      <strong>Dear ' . htmlspecialchars($user_name) . ',</strong>
                  </p>
              </div>
              
              <!-- محتوى الإشعار -->
              <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                <p style="font-size:12px;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:left">
                    <strong>Thanks!</strong>
                    Your online payment has been processed.
                </p>
                
                <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                  <b><span style="color: blue">Please Check <a href="http://egyptmart.shop/transaction_history.php">Transaction History</a></span></b>
                </p>
                
                <!-- تفاصيل المعاملة -->
                <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                    Your Transaction Details:
                </p>
                
                <div style="width: 1000px; line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                    <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        <label style="font-weight:bold;">Transaction Status:</label> PROCESSED
                    </p>
                    
                    <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        <label style="font-weight:bold;">Date:</label> ' . $current_datetime . '
                    </p>
                    
                    <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        <label style="font-weight:bold;">Order Id:</label> ' . $order_id_display . '
                    </p>
                    
                    <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        <label style="font-weight:bold;">Amount:</label> ' . $amount_display . '
                    </p>
                    
                    <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        <label style="font-weight:bold;">Credits:</label> ' . $credits_display . '
                    </p>
                </div>
                
                <div style="clear:both"></div>
                
                <br>
                
                <!-- رابط الرد -->
                <div style="clear:both">
                    <p style="line-height:1.5em;text-align:left;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                        You can Reply :<a href="http://egyptmart.shop/my-enquiries.php" style="float: right">Reply NOW</a>
                    </p>
                </div>
                
                <br>
                
                <span style="color:rgb(171,172,172);font-size:11px">
                    You are receiving this mailer as a registered member of <span style="color:blue">EgyptMART</span>.
                </span>
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
              
              <!-- معلومات الاشتراك -->
              <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
                  <p style="margin:10px 0px 2px">
                      You have received this mail by virtue of your opt-in subscription for Membership Plan on <font style="color:blue;">EgyptMART</font>.
                  </p>
              </div>
              
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