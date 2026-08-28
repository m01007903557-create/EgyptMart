<?php
/**
 * File: plan_start_notification.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: قالب البريد الإلكتروني للترحيب بالعضوية الجديدة (نسخة عربية)
 * Email template for welcoming new membership (Arabic version)
 * 
 * Variables Expected:
 * - $fullname: String recipient full name
 * - $plan_detail: Object membership plan details (mst_name)
 * - $bus_detail: Object business details (mp_amount, s_key)
 * - $start_date: String subscription start date
 * - $expiry_date: String subscription expiry date
 * - $user_detail: Object user details (email, bnsprof_compname, bnsprof_address1, bnsprof_address2, 
 *                 bnsprof_city, bnsprof_state, bnsprof_zipcode, bnsprof_ph1, usr_id)
 * - $usr_email: String user email for links
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_EGYPTMART') && !isset($fullname)) {
    exit('Direct access not allowed');
}

// Helper functions if not already defined
if (!function_exists('get_city_name')) {
    function get_city_name($cityId) { return is_numeric($cityId) ? "City $cityId" : (string)$cityId; }
}
if (!function_exists('get_state_name')) {
    function get_state_name($stateId) { return is_numeric($stateId) ? "State $stateId" : (string)$stateId; }
}
if (!function_exists('user_info')) {
    function user_info($userId, $field) { return ''; } // Placeholder - implement as needed
}

// Sanitize and prepare variables
$recipientName = htmlspecialchars($fullname ?? 'عميلنا العزيز', ENT_QUOTES, 'UTF-8');
$currentDate = date('Y/m/d');
$currentDateTime = date('d-M-Y H:i T');

// Plan details
$planName = htmlspecialchars($plan_detail->mst_name ?? 'JUNIOR', ENT_QUOTES, 'UTF-8');
$annualAmount = number_format((float)($bus_detail->mp_amount ?? 0), 2);
$paidAmount = (isset($bus_detail->s_key) && ($bus_detail->s_key == 1 || $bus_detail->s_key == 0)) 
    ? '0.00' 
    : number_format((float)($bus_detail->mp_amount ?? 0), 2);

$startDate = htmlspecialchars($start_date ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8');
$expiryDate = htmlspecialchars($expiry_date ?? 'N/A', ENT_QUOTES, 'UTF-8');

// User details for contact info
$companyName = htmlspecialchars($user_detail->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8');
$address1 = htmlspecialchars($user_detail->bnsprof_address1 ?? '', ENT_QUOTES, 'UTF-8');
$address2 = htmlspecialchars($user_detail->bnsprof_address2 ?? '', ENT_QUOTES, 'UTF-8');
$cityId = $user_detail->bnsprof_city ?? 0;
$stateId = $user_detail->bnsprof_state ?? 0;
$zipcode = htmlspecialchars($user_detail->bnsprof_zipcode ?? '', ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($user_detail->bnsprof_ph1 ?? '', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($user_detail->email ?? '', ENT_QUOTES, 'UTF-8');
$userId = $user_detail->usr_id ?? 0;

// Build city/state string
$cityStateParts = [];
if (!empty($cityId)) {
    $cityName = get_city_name($cityId);
    if (!empty($cityName)) $cityStateParts[] = $cityName;
}
if (!empty($stateId)) {
    $stateName = get_state_name($stateId);
    if (!empty($stateName)) $cityStateParts[] = $stateName;
}
if (!empty($zipcode)) {
    $cityStateParts[] = $zipcode;
}
$cityStateString = implode(', ', $cityStateParts);

// User email for links
$userEmail = rawurlencode($usr_email ?? $email ?? '');

// Base URL
$baseUrl = 'https://egyptmart.shop';
$loginRedirect = "{$baseUrl}/sign-in.php?email={$userEmail}&redirect=";

// Start building the email
$message1 = <<<HTML
<div class="b9_m2 b10_m2" id="detable" style="direction: rtl; font-family: 'Arial', 'Helvetica', sans-serif; background-color: #f5f5f5; padding: 20px;">
    <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0" style="max-width: 650px; margin: 0 auto;">
        <tbody>
            <tr class="f5_m2">
                <td class="sh_m2">
                    <span style="width:100%; word-wrap:break-word;" id="wbr">
                        <div style="width: 100%; height: auto; border: 10px solid #92AED2; float: left; padding: 20px; margin-top:10px; background-color: #ffffff; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            
                            <!-- Header Section -->
                            <div style="width: 100%; float: left; margin-bottom: 20px; border-bottom: 2px solid #eaeaea; padding-bottom: 15px;">
                                <div style="width: 30%; float: left;">
                                    <img style="width: 100%; max-width: 160px; height: auto;" 
                                         alt="EgyptMART" 
                                         src="{$baseUrl}/images/Mlogo.png" />
                                </div>
                                <div style="width: 43%; float: left; text-align: center;">
                                    <h2 style="font-size: 20px; color: #466da0; font-weight: 600; margin:10px 0 0;">
                                        حالة عضويتك اليوم <br> على إيجبت مارت
                                    </h2>
                                </div>
                                <div style="width: 27%; float: right; text-align: left; padding-top: 10px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; color: #333; margin-bottom: 5px;">
                                        إشعار هام
                                    </span>
                                    <span style="display: block; font-size: 13px; color: #666;">
                                        {$currentDate}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Greeting -->
                            <div style="width:100%; margin-bottom: 15px; text-align: right;">
                                <p style="font-size:18px; color:#333; margin:5px 0;">
                                    <strong>{$recipientName} : السادة</strong>
                                </p>
                                <p style="font-size:15px; color:#1366b1; margin:10px 0; line-height:1.6;">
                                    <strong style="color:#ff0000;">{$planName}</strong> نحن سعداء بإنضمامك الى منصة إيجبت مارت لمساعدتك على البيع والشراء كعضو
                                </p>
                            </div>
                            
                            <!-- Membership Details -->
                            <div style="width:100%; margin-bottom: 20px;">
                                <p style="line-height:1.5em; text-align:center; font-size:1.2em; background-color:#eaeaea; margin:0 0 10px; font-weight:bold; padding:10px; border-radius: 5px;">
                                    : تفاصيل العضوية
                                </p>
                                
                                <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px;">
                                    <table style="width: 100%; font-size: 14px; line-height: 24px; direction: ltr; text-align: left;">
                                        <tr>
                                            <td style="padding: 3px 0; width: 120px;"><strong>Date:</strong></td>
                                            <td style="padding: 3px 0;">{$currentDateTime}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 3px 0;"><strong>Annual Amount:</strong></td>
                                            <td style="padding: 3px 0;">{$annualAmount}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 3px 0;"><strong>Paid Amount:</strong></td>
                                            <td style="padding: 3px 0;">{$paidAmount}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 3px 0;"><strong>Membership Plan:</strong></td>
                                            <td style="padding: 3px 0;">{$planName}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 3px 0;"><strong>Starting Period Time:</strong></td>
                                            <td style="padding: 3px 0;">{$startDate}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 3px 0;"><strong>Subscription End:</strong></td>
                                            <td style="padding: 3px 0;">{$expiryDate}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- CTA Button -->
                            <div style="clear:both; margin-bottom: 20px;">
                                <p style="line-height:1.5em; text-align:center; font-size:1.2em; background-color:#eaeaea; margin:0 0 15px; padding:15px; border-radius: 5px;">
                                    <a href="{$loginRedirect}{$baseUrl}/why_egyptmart.php" 
                                       style="font-size: 18px; color: #466da0; text-decoration: underline; font-weight: normal; margin-left: 5px;">
                                        إضغط هنا
                                    </a> 
                                    لكى تبدأ
                                    <a href="{$loginRedirect}{$baseUrl}/product-list.php">
                                        <strong style="color: #466da0; font-size: 18px;"> الآن </strong>
                                    </a>
                                </p>
                            </div>
                            
                            <br>
                            
                            <!-- Plan Banner -->
                            <div style="width: 100%; margin-bottom: 20px;">
                                <a href="{$loginRedirect}{$baseUrl}/product-list.php">
                                    <img src="{$baseUrl}/images/plan.png" 
                                         alt="خطط العضوية" 
                                         style="width: 100%; height: auto; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);" />
                                </a>
                            </div>
HTML;

// Contact Details Section
$message1 .= <<<HTML
                            <!-- Contact Details -->
                            <div style="width:100%; margin-bottom: 20px;">
                                <p style="color: #002757; font-size: 16px; text-align:center; font-weight:bold; background-color: #eaeaea; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                                    : تفاصيل الإتصال
                                </p>
                                
                                <div style="background-color: #f0f8ff; padding: 15px; border-radius: 5px; border: 1px solid #92AED2; text-align: right;">
                                    <p style="margin:5px 0;"><strong style="color: #000;">{$companyName}</strong></p>
HTML;

if (!empty($address1)) {
    $message1 .= "<p style='margin:5px 0;'>{$address1}</p>";
}
if (!empty($address2)) {
    $message1 .= "<p style='margin:5px 0;'>{$address2}</p>";
}
if (!empty($cityStateString)) {
    $message1 .= "<p style='margin:5px 0;'>{$cityStateString}</p>";
}
if (!empty($phone)) {
    $message1 .= "<p style='margin:5px 0;'><strong>Mobile/Cell phone:</strong> {$phone}</p>";
}
if (!empty($email)) {
    $message1 .= "<p style='margin:5px 0;'><strong>E-mail:</strong> {$email}</p>";
}

$message1 .= <<<HTML
                                </div>
                            </div>
                            
                            <!-- Free Posting Options -->
                            <div style="width:100%; margin-bottom: 20px;">
                                <p style="color: #002757; font-size: 16px; text-align:center; font-weight:bold; background-color: #eaeaea; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                                    : ربما تريد أيضا نشر
                                </p>
                                
                                <table align="center" style="width: 100%; max-width: 500px; margin: 0 auto; border-collapse: separate; border-spacing: 10px;">
                                    <tr>
                                        <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 8px;">
                                            <a href="{$loginRedirect}{$baseUrl}/product-sel-cat.php" 
                                               style="color: #466da0; font-size: 16px; font-weight: 600; text-decoration: none; display: block;">
                                                منتجات / خدمات
                                            </a>
                                        </td>
                                        <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 8px;">
                                            <a href="{$loginRedirect}{$baseUrl}/post-buy-req.php" 
                                               style="color: #466da0; font-size: 16px; font-weight: 600; text-decoration: none; display: block;">
                                                متطلبات شراء
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 8px;">
                                            <a href="{$loginRedirect}{$baseUrl}/post-sell-offer.php" 
                                               style="color: #466da0; font-size: 15px; font-weight: 600; text-decoration: none; display: block;">
                                                عروض بيع خاصة
                                            </a>
                                        </td>
                                        <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 8px;">
                                            <a href="{$loginRedirect}{$baseUrl}/post-tender.php" 
                                               style="color: #466da0; font-size: 16px; font-weight: 600; text-decoration: none; display: block;">
                                                مناقصات ومزايدات
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <br>
                            
                            <!-- Feedback Link -->
                            <div style="clear:both; margin-bottom: 20px;">
                                <p style="line-height:1.5em; text-align:center; font-size:1.2em; background-color:#eaeaea; margin:20px 0 0; padding:15px; border-radius: 5px;">
                                    <a href="{$loginRedirect}{$baseUrl}/why_egyptmart.php" 
                                       style="font-size: 16px; color: #466da0; text-decoration: underline; font-weight: normal; margin-left: 5px;">
                                        أكتب الآن
                                    </a> 
                                    أكتب لنا طلباتك ومقترحاتك
                                    <a href="{$loginRedirect}{$baseUrl}/membership_plans.php">
                                        <strong style="color: #466da0; font-size: 16px;">الآن</strong>
                                    </a>
                                </p>
                            </div>
                            
                            <br>
                            
                            <!-- Divider -->
                            <div style="height:2px; width:100%; float:left; border-bottom: 3px dotted #D8AED8; margin: 20px 0;">
                            </div>
                            
                            <!-- Navigation Links -->
                            <div style="width:100%; float:left; text-align:center; padding: 15px 0; background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); border-radius: 10px; margin-bottom: 15px;">
                                <a href="{$loginRedirect}{$baseUrl}/product-sel-cat.php" 
                                   style="color:#466da0; text-decoration:none; font-size:15px; font-weight:600; margin: 0 8px; display: inline-block;">
                                    Products & Suppliers
                                </a> | 
                                <a href="{$loginRedirect}{$baseUrl}/post-sell-offer.php" 
                                   style="color:#466da0; text-decoration:none; font-size:15px; font-weight:600; margin: 0 8px; display: inline-block;">
                                    Sale Offers
                                </a> | 
                                <a href="{$loginRedirect}{$baseUrl}/post-buy-req.php" 
                                   style="color:#466da0; text-decoration:none; font-size:15px; font-weight:600; margin: 0 8px; display: inline-block;">
                                    Buy Requests
                                </a> | 
                                <a href="{$loginRedirect}{$baseUrl}/post-tender.php" 
                                   style="color:#466da0; text-decoration:none; font-size:15px; font-weight:600; margin: 0 8px; display: inline-block;">
                                    Tenders
                                </a>
                            </div>
                            
                            <!-- Disclaimer -->
                            <div style="width:100%; color:#666; margin: 15px 0;">
                                <p style="margin:5px 0; font-size:12px; line-height:1.5; text-align: justify;">
                                    Be noted that hereby you can delete / update your displayed business on EgyptMART 
                                    instantly once you login to <a href="{$loginRedirect}{$baseUrl}/product-list.php" style="color:#466da0;">{$baseUrl}/product-list.php</a> 
                                    and delete it. Otherwise, you are satisfied that EgyptMART team will do their best 
                                    to promote your business in domestic and global markets. For more information.
                                </p>
                            </div>
                            
                            <br/><br/>
                            
                            <!-- Signature -->
                            <div style="width:100%; text-align:center; color:#666; border-top: 2px solid #eaeaea; padding-top: 20px;">
                                <p style="margin:5px 0;">
                                    <span style="font-size: 18px; font-weight: 600; color: #333;">Warm Regards,</span>
                                </p>
                                <p style="margin:5px 0;">
                                    <span style="color: #466da0; font-size: 18px; font-weight: bold;">EgyptMART Team</span>
                                </p>
                                <p style="margin:5px 0;">
                                    <span style="color: #da4e1e; font-size: 20px; font-weight: bold;">We Promote Your Business !</span>
                                </p>
                            </div>
                        </div>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
HTML;
?>