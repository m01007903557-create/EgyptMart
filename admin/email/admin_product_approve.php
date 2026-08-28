<?php
/**
 * File: admin_product_approve.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: قالب البريد الإلكتروني للتسويق للموردين - عرض خدمات المنصة
 * Email template for marketing to suppliers - promoting platform services
 * 
 * Variables Expected:
 * - $suname: String recipient full name
 * - $usr_email: String user email for login links
 * - $user_detail: Object containing user details (optional)
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_EGYPTMART') && !isset($suname)) {
    exit('Direct access not allowed');
}

// Sanitize and prepare variables
$recipientName = htmlspecialchars($suname ?? 'عميلنا العزيز', ENT_QUOTES, 'UTF-8');
$userEmail = rawurlencode($usr_email ?? '');
$currentDate = date('Y/m/d');

// Get user email from different sources if needed
if (empty($userEmail) && isset($user_detail) && isset($user_detail->email)) {
    $userEmail = rawurlencode($user_detail->email);
}

// Base URL
$baseUrl = 'https://egyptmart.shop';
$loginRedirect = "{$baseUrl}/sign-in.php?email={$userEmail}&redirect=";

// Build email template
$message1 = <<<HTML
<div class="b9_m2 b10_m2" id="detable" style="direction: rtl; font-family: Arial, Helvetica, sans-serif; background-color: #f9f9f9; padding: 20px;">
    <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0" style="max-width: 650px; margin: 0 auto;">
        <tbody>
            <tr class="f5_m2">
                <td class="sh_m2">
                    <span style="width:100%; word-wrap:break-word;" id="wbr">
                        <div style="width: 100%; height: auto; border: 10px solid #92AED2; float: left; padding: 20px; margin-top:10px; background-color: #ffffff; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            
                            <!-- Header Section -->
                            <div style="width: 100%; float: left; margin-bottom: 20px; border-bottom: 2px solid #eaeaea; padding-bottom: 20px;">
                                <div style="width: 30%; float: left;">
                                    <img style="width: 100%; max-width: 180px; height: auto;" 
                                         alt="EgyptMART" 
                                         src="{$baseUrl}/images/Mlogo.png" />
                                </div>
                                <div style="width: 43%; float: left; text-align: center;">
                                    <!-- Empty space for balance -->
                                </div>
                                <div style="width: 27%; float: right; text-align: left; padding-top: 10px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; color: #333; margin-bottom: 5px;">
                                        عرض الموردين
                                    </span>
                                    <span style="display: block; font-size: 13px; color: #666;">
                                        {$currentDate}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Recipient Name -->
                            <div style="width:100%; margin-bottom: 20px;">
                                <p style="font-size:18px; color:#333; margin:0; padding:10px 0; border-bottom: 1px solid #eaeaea;">
                                    <strong>{$recipientName} : الســادة</strong>
                                </p>
                            </div>
                            
                            <!-- Main Call to Action -->
                            <div style="width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                                <p style="color: white; font-size: 22px; font-weight: bold; text-align: center; margin:0;">
                                    <a href="{$loginRedirect}{$baseUrl}/product-list.php" 
                                       style="color: white; text-decoration: none; display: block;">
                                        ! أنشر منتجاتك وخدماتك للجملة والتصدير - نجلب لك موردين
                                    </a>
                                </p>
                            </div>
                            
                            <!-- Banner Image -->
                            <div style="width: 100%; margin-bottom: 20px;">
                                <a href="{$loginRedirect}{$baseUrl}/product-list.php">
                                    <img src="{$baseUrl}/images/plan.png" 
                                         alt="خطط العضوية" 
                                         style="width: 100%; height: auto; border-radius: 10px; box-shadow: 0 3px 10px rgba(0,0,0,0.1);" />
                                </a>
                            </div>
                            
                            <!-- Benefits Section -->
                            <div style="width: 100%; background-color: #eaeaea; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                                <p style="margin:0; text-align:center; color:#333;">
                                    <span style="font-size: 20px; font-weight: bold; color: #00c118; display: block; margin-bottom: 10px;">
                                        ✦ مزايا حصرية ✦
                                    </span>
                                    <span style="font-size: 16px; display: block; margin-bottom: 5px;">
                                        نعرض لك حتى 6 منتجات للتسويق ديجيتال بمنصات إيجبت مارت وأرابيوس دوت كوم
                                    </span>
                                    <span style="font-size: 16px; display: block;">
                                        نجلب لك طلبات شراء من داخل وخارج مصر ومستوردين وجملة
                                    </span>
                                </p>
                            </div>
                            
                            <!-- Email Marketing Image -->
                            <div style="width: 100%; margin-bottom: 20px; text-align: left;">
                                <a href="{$baseUrl}">
                                    <img src="{$baseUrl}/images/mail.png" 
                                         alt="التسويق الإلكتروني" 
                                         style="max-width: 100%; height: auto; border-radius: 5px;" />
                                </a>
                            </div>
                            
                            <!-- Registration CTA -->
                            <div style="width: 100%; background-color: #e8f4f8; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                                <p style="margin:0; text-align:center;">
                                    <a href="{$loginRedirect}{$baseUrl}/product-list.php" 
                                       style="color: #1600cc; font-size: 22px; font-weight: bold; text-decoration: none;">
                                        سجل الآن صور منتجاتك التى تريد تسويقها مجانا وأستمتع بالتجربة
                                    </a>
                                </p>
                            </div>
                            
                            <!-- Decorative Divider -->
                            <div style="height:3px; width:100%; float:left; border-bottom: 3px dotted #D8AED8; margin: 20px 0;">
                            </div>
                            
                            <!-- Navigation Links -->
                            <div style="width:100%; float:left; text-align:center; padding: 15px 0; background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); border-radius: 10px; margin-bottom: 20px;">
                                <a href="{$loginRedirect}{$baseUrl}/product-list.php" 
                                   style="color:#00c118; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block; padding: 5px 10px;">
                                    سجل منتجات جديدة
                                </a> 
                                <span style="color:#00c118;">|</span>
                                <a href="{$loginRedirect}{$baseUrl}/post-sell-offer.php" 
                                   style="color:#00c118; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block; padding: 5px 10px;">
                                    أنشر عروضك الخاصة
                                </a> 
                                <span style="color:#00c118;">|</span>
                                <a href="{$loginRedirect}{$baseUrl}/post-buy-req.php" 
                                   style="color:#00c118; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block; padding: 5px 10px;">
                                    أنشر طلب تسعير
                                </a> 
                                <span style="color:#00c118;">|</span>
                                <a href="{$loginRedirect}{$baseUrl}/post-tender.php" 
                                   style="color:#00c118; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block; padding: 5px 10px;">
                                    أنشر مناقصات مجانا
                                </a>
                            </div>
                            
                            <!-- Disclaimer -->
                            <div style="width:100%; margin-bottom: 15px;">
                                <p style="margin:5px 0; font-size:12px; color:#666; text-align:right; line-height:1.6;">
                                    نحيط سيادتكم أنه يمكنكم وحدك تقرير إذا ماكنت تريد إستمرار عرض مضمون شركتك وتجارتك 
                                    على المنصة أو تعديله أو حذفه طبقا لسياساتك المعمول بها داخل شركتك
                                </p>
                            </div>
                            
                            <!-- Signature -->
                            <div style="width:100%; text-align:center; color:#666; border-top: 2px solid #eaeaea; padding-top: 20px;">
                                <p style="margin:5px 0;">
                                    <span style="font-size: 18px; font-weight: 600; color: #333;">مع تحيات</span>
                                </p>
                                <p style="margin:5px 0;">
                                    <span style="color: #466da0; font-size: 18px; font-weight: bold;">Egypt MART Team</span>
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