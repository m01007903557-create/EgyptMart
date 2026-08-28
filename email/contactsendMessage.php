<?php
/**
 * File: contactsendMessage.php
 * Version: PHP 8.3
 * Description: إرسال رسالة استفسار بين المستخدمين مع إشعار عبر البريد الإلكتروني
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية (تعديل المسار للوصول إلى common.php)
include dirname(__DIR__) . "/common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    echo "0";
    error_log("خطأ في الاتصال بقاعدة البيانات في sendMessage.php");
    exit();
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['msg_from']) || !isset($_POST['msg_to']) || !isset($_POST['msg_subject']) || !isset($_POST['msg_message'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$msg_from = (int)$_POST['msg_from'];
$msg_to = (int)$_POST['msg_to'];
$msg_subject = trim($_POST['msg_subject'] ?? '');
$msg_message_raw = trim($_POST['msg_message'] ?? '');

// التحقق من صحة القيم
if ($msg_from <= 0 || $msg_to <= 0 || empty($msg_subject) || empty($msg_message_raw)) {
    echo "0";
    exit();
}

// تنظيف البيانات للاستخدام في قاعدة البيانات
$msg_subject_escaped = mysqli_real_escape_string($con, $msg_subject);
$msg_message_escaped = mysqli_real_escape_string($con, $msg_message_raw);

// تنسيق الرسالة للعرض (مع فواصل الأسطر)
$msg_message_formatted = wordwrap($msg_message_raw, 90, "<br />\n");

// جلب بيانات المستلم
$sql_to = "SELECT * FROM user, business_profile 
           WHERE usr_id = {$msg_to} 
           AND bnsprof_uid = usr_id 
           LIMIT 1";
$res_to = mysqli_query($con, $sql_to);

if (!$res_to) {
    echo "0";
    error_log("خطأ في جلب بيانات المستلم: " . mysqli_error($con));
    exit();
}
$row_to = mysqli_fetch_object($res_to);

// جلب بيانات المرسل
$sql_usr = "SELECT * FROM user, business_profile 
            WHERE usr_id = bnsprof_uid 
            AND usr_id = {$msg_from} 
            LIMIT 1";
$res_usr = mysqli_query($con, $sql_usr);

if (!$res_usr) {
    echo "0";
    error_log("خطأ في جلب بيانات المرسل: " . mysqli_error($con));
    exit();
}
$row_usr = mysqli_fetch_object($res_usr);

// إدراج الرسالة في قاعدة البيانات
$sql = "INSERT INTO message
        SET
            msg_from = {$msg_from},
            msg_to = {$msg_to},
            msg_subject = '" . $msg_subject_escaped . "',
            msg_message = '" . $msg_message_escaped . "',
            msg_date = NOW()";

$result = mysqli_query($con, $sql);

if ($result) {
    
    // ==============================================
    // إنشاء قالب البريد الإلكتروني
    // ==============================================
    $current_date = date("Y/m/d");
    
    $comment = '<div class="b9_m2 b10_m2" id="detable">
      <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0">
        <tbody>
          <tr class="f5_m2">
            <td class="sh_m2">
              <span style="width:550px;word-wrap:break-word;" id="wbr">
                <div style="width: 60%;height: auto;border: 10px solid #92AED2;float: left;padding: 10px;margin-top:10px;">
                  
                  <!-- رأس البريد -->
                  <div style="height: 100px; width: 100%; float: left; ">
                      <div style="height: 100px; width: 30%; float: left;">
                          <img src="http://www.egyptmart.shop/images/Mlogo.png" style="width: 100%;color: #00F;font-size: 22px;font-weight: bold;" alt="EgyptMART">
                      </div>
                      
                      <div style="height:100px;width:43%;float:left;">
                          <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:0px; margin-bottom:0px;">
                              إستفسار اليوم <br> طلب شراء
                          </h2>
                      </div>
                      
                      <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                          <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold;color:#000000;">Enquiry</span>
                          <span style="float: right; font-size: 13px; padding-top: 0px; clear: both;color:#000000;">' . $current_date . '</span>
                      </div>
                  </div>
                  
                  <!-- تحية المستلم -->
                  <div style="width:100%;color:#000000;">
                      <p style="font-size:16px;text-align:right;color:#000000">
                          <strong>' . htmlspecialchars($row_to->name_prefix ?? '') . ' ' . htmlspecialchars($row_to->fname ?? '') . ' ' . htmlspecialchars($row_to->lname ?? '') . ' : الســادة</strong>
                      </p>
                  </div>
                  
                  <!-- معلومات المرسل -->
                  <div style="max-width:575px;line-height:18px;font-size:12px;font-family:Arial,Helvetica,sans-serif">
                      <p style="font-size:1.4em;margin:0;padding:.5em 0 0.5em;line-height:1.4em;text-align:center">
                          <b>' . htmlspecialchars($row_usr->bnsprof_compname ?? '') . ' : إستفسار شراء من</b>
                      </p>
                      
                      <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                          بيانات إتصال الراسل
                      </p>
                      
                      <div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;padding:0.5em 0 0.9em 1em">
                          ' . htmlspecialchars($row_usr->name_prefix ?? '') . ' ' . htmlspecialchars($row_usr->fname ?? '') . ' ' . htmlspecialchars($row_usr->lname ?? '') . '<br>
                          ' . htmlspecialchars($row_usr->bnsprof_address1 ?? '') . '<br>
                          ' . htmlspecialchars(get_city_name($row_usr->bnsprof_city ?? 0)) . ', ' . htmlspecialchars(get_country_name($row_usr->country ?? 0)) . '<br>
                          Mobile/ Cell Phone: ' . htmlspecialchars(($row_usr->country_ph_code ?? '') . '-' . ($row_usr->mobile1 ?? '')) . '<br>
                          E-mail: <a href="mailto:' . htmlspecialchars($row_usr->email ?? '') . '" target="_blank">' . htmlspecialchars($row_usr->email ?? '') . '</a><br>
                      </div>
                      
                      <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                          تفاصيـل الإستفسـار
                      </p>
                      
                      <div style="line-height:1.5em;font-size:12px;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:0.5em 0 0.9em 1em">
                          <span style="font-size:1.0em;font-weight:normal">' . $msg_message_formatted . '</span>
                      </div>
                      
                      <br>
                      
                      <div style="clear:both">
                          <p style="line-height:1.5em;text-align:center;font-size:1.2em;background-color:#eaeaea;margin:0;font-family:Arial,Helvetica,sans-serif;font-weight:bold;padding:.4em .4em .4em">
                              يمكنك الرد على الإستفسار من هنا 
                              <a href="http://egyptmart.shop/my-enquiries.php" style="float: center">يمكنك الرد الآن</a>
                          </p>
                      </div>
                      
                      <br>
                      
                      <div style="clear:both"></div>
                      <br>
                      
                      <!-- معلومات الدعم الفني -->
                      <table style="font-family:Arial,Helvetica,sans-serif;font-size:13px" cellpadding="0" cellspacing="0">
                        <tbody>
                          <tr>
                            <td style="line-height:20px" valign="top">
                               <span style="color: blue;">EgyptMART</span> الدعم الفنى
                              <br>Call us on ' . htmlspecialchars(get_page_settings(21) ?? '') . '
                            </td>
                          </tr>
                        </tbody>
                      </table>
                      
                      <span style="color:rgb(171,172,172);font-size:11px">
                          You are receiving this mailer as a registered member of <span style="color: blue;"></span>.
                      </span>
                  </div>
                  
                  <!-- خط فاصل -->
                  <div style="height:2px;width:100%;float:left;border-bottom: 3px dotted #D8AED8;"></div>
                  
                  <!-- روابط سريعة -->
                  <div style="width:100%;float:left;text-align:center;padding-top: 10px;padding-bottom: 10px;">
                      <a href="http://egyptmart.shop/dir.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Product &amp; Suppliers</a> | 
                      <a href="http://egyptmart.shop/sale-offers.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Sale Offers</a> | 
                      <a href="http://egyptmart.shop/buyleads.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Buy Requests</a> | 
                      <a href="http://egyptmart.shop/tenders.php" style="color:#466da0;text-decoration:none;font-size:18px;font-weight:bold;">Tenders</a>
                  </div>
                  
                  <!-- تذييل -->
                  <div style="width:100%;padding-left: 0px;float:left;color:#808080;">
                      <p style="margin:10px 0px 2px">
                          You have received this mail by virtue of your opt-in subscription for product Enquiry on <font style="color:blue;">EgyptMART</font>.
                      </p>
                      <p style="color:#808080; margin:0px 0px 20px;">
                          <a href="http://www.egyptmart.shop/manage-buylead-alert.php" style="text-decoration:none;color:blue;">Click here</a> if you wish to modify your buy requirement alert categories.
                      </p>
                  </div>
                </div>
              </span>
            </td>   
          </tr>
        </tbody>
      </table>
    </div>';
    
    // إعداد وإرسال البريد الإلكتروني
    $from_mail = get_adminemail();
    $to_email = user_info($msg_to, 'email');
    $from_name = get_page_settings(4);
    $subject = htmlspecialchars($row_usr->bnsprof_compname ?? '') . ': إستفسار شراء من ';
    
    $headers = "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $from_name . " <" . $from_mail . ">\r\n";
    
    // إرسال البريد
    if (!mail($to_email, $subject, $comment, $headers)) {
        error_log("فشل إرسال البريد الإلكتروني للاستفسار من {$msg_from} إلى {$msg_to}");
    }
    
    echo "1";
    
} else {
    error_log("فشل إدراج الرسالة: " . mysqli_error($con));
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>