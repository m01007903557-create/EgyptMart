<?php
/**
 * File: admin_selloffer_approve.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: قالب البريد الإلكتروني لإشعار الموافقة على نشر عرض بيع خاص
 * Email template for notifying users about sell offer approval
 * 
 * Variables Expected:
 * - $row_to: Object containing recipient details (name_prefix, fname, lname)
 * - $product_img: String product image HTML
 * - $product_title: String product title
 * - $contact_details: String contact information HTML
 * - $usr_email: String user email for login links
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_EGYPTMART') && !isset($row_to)) {
    exit('Direct access not allowed');
}

// Sanitize and prepare variables
$recipientName = trim(
    ($row_to->name_prefix ?? '') . ' ' . 
    ($row_to->fname ?? '') . ' ' . 
    ($row_to->lname ?? '')
);

$productImage = $product_img ?? '<img src="https://egyptmart.shop/images/noimage.jpg" width="100" alt="Product Image" />';
$productTitle = htmlspecialchars($product_title ?? 'عرض البيع', ENT_QUOTES, 'UTF-8');
$contactInfo = $contact_details ?? 'لا توجد تفاصيل اتصال متاحة';
$userEmail = rawurlencode($usr_email ?? '');
$currentDate = date('Y/m/d');

// Base URL
$baseUrl = 'https://egyptmart.shop';
$loginRedirect = "{$baseUrl}/sign-in.php?email={$userEmail}&redirect=";

// Build email template
$message1 = <<<HTML
<div class="b9_m2 b10_m2" id="detable" style="direction: rtl; font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0" style="max-width: 700px; margin: 0 auto;">
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
                                <div style="width: 43%; float: left;">
                                    <h2 style="font-size: 20px; color: #466da0; font-weight: 600; text-align: center; margin:15px 0 0;">
                                        موافقة المنصة على <br> نشر عرض بيع خاص
                                    </h2>
                                </div>
                                <div style="width: 27%; float: right; text-align: left; padding-top: 10px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; color: #333; margin-bottom: 5px;">
                                        Notification
                                    </span>
                                    <span style="display: block; font-size: 13px; color: #666;">
                                        {$currentDate}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Recipient Name -->
                            <div style="width:100%; margin-bottom: 20px;">
                                <p style="font-size:18px; color:#333; margin:0; padding:10px 0; border-bottom: 1px solid #eaeaea; text-align: right;">
                                    <strong>{$recipientName} : الســادة</strong>
                                </p>
                            </div>
                            
                            <!-- Main Content -->
                            <div style="max-width:100%; line-height:1.6; font-size:14px; font-family:Arial,Helvetica,sans-serif;">
                                
                                <!-- Title -->
                                <p style="font-size:1.4em; margin:0 0 20px 0; padding:.5em 0; line-height:1.4em; text-align:center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
                                    <b>تم نشر عرض البيع الخاص بك بنجاح على <span style="color: #FFD700"> EgyptMART</span></b>
                                </p>
                                
                                <!-- Sell Offer Details -->
                                <p style="line-height:1.5em; text-align:center; font-size:1.4em; background-color:#eaeaea; margin:20px 0 10px; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:15px; color: #002757; border-radius: 5px;">
                                    تفاصيل عرض البيع الخاص
                                </p>
                                
                                <!-- Product Info -->
                                <div style="width: 100%; background-color: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 20px; min-height: 120px;">
                                    <div style="float: left; width: 120px; margin-right: 20px; text-align: center;">
                                        {$productImage}
                                    </div>
                                    <div style="float: right; width: calc(100% - 150px); padding-right: 10px;">
                                        <span style="display: block; font-size: 18px; color: #333; font-weight: 600; word-wrap: break-word; margin-bottom: 10px;">
                                            {$productTitle}
                                        </span>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                                
                                <!-- Contact Details -->
                                <p style="line-height:1.5em; text-align:center; font-size:1.4em; background-color:#eaeaea; margin:20px 0 10px; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:15px; color: #002757; border-radius: 5px;">
                                    تفاصيل اتصال شركتك
                                </p>
                                
                                <div style="width: 100%; background-color: #f0f8ff; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #92AED2;">
                                    {$contactInfo}
                                </div>
                                
                                <!-- Login Prompt -->
                                <div style="margin-bottom: 20px;">
                                    <p style="line-height:1.5em; text-align:center; font-size:1.4em; background-color:#eaeaea; margin:20px 0 10px; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:15px; color: #002757; border-radius: 5px;">
                                        لم تقم بتسجيل دخول للمنصة من قبل ؟
                                    </p>
                                    <div style="background-color: #e8f4f8; padding: 15px; border-radius: 10px; text-align: center; font-size: 16px; color: #333;">
                                        إستخدم بريدك الحالى وكلمة مرور 123456 للدخول
                                        <a href="{$baseUrl}/sign-in.php?email={$userEmail}" 
                                           style="display: inline-block; margin-right: 20px; padding: 8px 25px; background-color: #466da0; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                                            الدخول الآن
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Free Posting Options -->
                                <div style="margin-bottom: 20px;">
                                    <p style="color: #000000; font-size: 20px; text-align:center; font-weight: 900; background-color: #eaeaea; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                                        ربما تريد إدخال التالى أيضا  
                                        <strong style="color:#da4e1e; font-size: 20px;">مجانا</strong>
                                    </p>
                                    
                                    <table align="center" style="width: 100%; max-width: 500px; margin: 0 auto; border-collapse: separate; border-spacing: 10px;">
                                        <tr>
                                            <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 8px;">
                                                <a href="{$loginRedirect}{$baseUrl}/product-sel-cat.php" 
                                                   style="color: #466da0; font-size: 18px; font-weight: 600; text-decoration: none; display: block;">
                                                    منتجات وخدمات
                                                </a>
                                            </td>
                                            <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 8px;">
                                                <a href="{$loginRedirect}{$baseUrl}/post-buy-req.php" 
                                                   style="color: #466da0; font-size: 18px; font-weight: 600; text-decoration: none; display: block;">
                                                    طلبات شراء
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 8px;">
                                                <a href="{$loginRedirect}{$baseUrl}/post-sell-offer.php" 
                                                   style="color: #466da0; font-size: 18px; font-weight: 600; text-decoration: none; display: block;">
                                                    عروض بيع خاصة
                                                </a>
                                            </td>
                                            <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 8px;">
                                                <a href="{$loginRedirect}{$baseUrl}/post-tender.php" 
                                                   style="color: #466da0; font-size: 18px; font-weight: 600; text-decoration: none; display: block;">
                                                    مناقصات ومزايدات
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <!-- Contact Link -->
                                <div style="clear:both; margin-bottom: 20px;">
                                    <p style="line-height:1.5em; text-align:center; font-size:1.2em; background-color:#eaeaea; margin:20px 0 0; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:15px; border-radius: 5px;">
                                        <a href="{$loginRedirect}{$baseUrl}/why_egyptmart.php" 
                                           style="font-size: 16px; color: #466da0; text-decoration: none; font-weight: normal;">
                                            << إضغط هنا
                                        </a> 
                                        تواصل معنا وأبلغنا ماتريد
                                        <a href="{$loginRedirect}{$baseUrl}/membership_plans.php">
                                            <strong style="color: #466da0; font-size: 16px;">! الآن</strong>
                                        </a>
                                    </p>
                                </div>
                                
                                <br>
                            </div>
                            
                            <!-- Divider -->
                            <div style="height:2px; width:100%; float:left; border-bottom: 3px dotted #D8AED8; margin: 20px 0;">
                            </div>
                            
                            <!-- Navigation Links -->
                            <div style="width:100%; float:left; text-align:center; padding: 15px 0; background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); border-radius: 10px; margin-bottom: 15px;">
                                <a href="{$baseUrl}/dir.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block;">
                                    منتجات وخدمات
                                </a> | 
                                <a href="{$baseUrl}/sale-offers.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block;">
                                    عروض بيع خاصة
                                </a> | 
                                <a href="{$baseUrl}/buyleads.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block;">
                                    طلبات شراء
                                </a> | 
                                <a href="{$baseUrl}/tenders.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block;">
                                    مناقصات ومزايدات
                                </a>
                            </div>
                            
                            <!-- Support Info -->
                            <div style="width:100%; text-align:center; color:#666; margin: 10px 0;">
                                <p style="margin:5px 0; font-size:14px;">
                                    للمساعدة لاتتردد فى الإتصال برقم - 01030029097
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