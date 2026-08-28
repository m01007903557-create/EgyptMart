<?php
/**
 * File: admin_buylead_approve.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: قالب البريد الإلكتروني لإشعار الموافقة على نشر طلب شراء
 * Email template for notifying users about buy request approval
 * 
 * Variables Expected:
 * - $row_to: Object containing recipient details (name_prefix, fname, lname)
 * - $product_img: String product image URL
 * - $product_title: String product title
 * - $product_moq: String minimum order quantity
 * - $product_type: String product type/unit
 * - $product_price: String product price
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

$productImage = $product_img ?? '';
$productTitle = htmlspecialchars($product_title ?? 'Product', ENT_QUOTES, 'UTF-8');
$productMoq = htmlspecialchars($product_moq ?? 'N/A', ENT_QUOTES, 'UTF-8');
$productType = htmlspecialchars($product_type ?? 'Unit', ENT_QUOTES, 'UTF-8');
$productPrice = htmlspecialchars($product_price ?? 'N/A', ENT_QUOTES, 'UTF-8');
$contactInfo = $contact_details ?? 'No contact details available';
$userEmail = rawurlencode($usr_email ?? '');
$currentDate = date('Y/m/d');

// Build email template
$message2 = <<<HTML
<div class="b9_m2 b10_m2" id="detable" style="direction: rtl; font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0" style="max-width: 800px; margin: 0 auto;">
        <tbody>
            <tr class="f5_m2">
                <td class="sh_m2">
                    <span style="width:750px;word-wrap:break-word;" id="wbr">
                        <div style="width: 100%; height: auto; border: 10px solid #92AED2; float: left; padding: 20px; margin-top:10px; background-color: #ffffff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                            
                            <!-- Header Section -->
                            <div style="height: 120px; width: 100%; float: left; margin-bottom: 20px; border-bottom: 2px solid #eaeaea; padding-bottom: 20px;">
                                <div style="height: 100px; width: 30%; float: left;">
                                    <img style="width: 100%; max-width: 200px; height: auto; color: #00F; font-size: 22px; font-weight: bold;" 
                                         alt="EgyptMART" 
                                         src="https://egyptmart.shop/images/Mlogo.png" />
                                </div>
                                <div style="height:100px; width:43%; float:left;">
                                    <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:20px; margin-bottom:0px; font-weight: 600;">
                                        موافقة المنصة على <br>نشر طلـب شـراء جـديـد
                                    </h2>
                                </div>
                                <div style="min-height: 100px; width: 27%; float: right; padding-top: 10px;">
                                    <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold; color:#000000;">
                                        Notification
                                    </span>
                                    <span style="float: right; font-size: 13px; padding-top: 0px; clear: both; color:#000000;">
                                        {$currentDate}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Recipient Name -->
                            <div style="width:100%; color:#000000; margin-bottom: 20px; text-align: right;">
                                <p style="font-size:18px; color:#000000; margin: 5px 0; border-bottom: 1px solid #eaeaea; padding-bottom: 10px;">
                                    <strong>{$recipientName} : الســادة</strong>
                                </p>
                            </div>
                            
                            <!-- Main Content -->
                            <div style="max-width:100%; line-height:1.6; font-size:14px; font-family:Arial,Helvetica,sans-serif;">
                                
                                <!-- Title -->
                                <p style="font-size:1.4em; margin:0 0 20px 0; padding:.5em 0; line-height:1.4em; text-align:center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 5px;">
                                    <b>تم نشر طلب شرائك على <span style="color: #FFD700"> EgyptMART</span></b>
                                </p>
                                
                                <!-- Buy Request Details -->
                                <p style="line-height:1.5em; text-align:center; font-size:1.4em; background-color:#eaeaea; margin:20px 0 10px; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:15px; color: #002757; border-radius: 5px;">
                                    تفاصيل طلب الشراء
                                </p>
                                
                                <!-- Product Info -->
                                <div style="width: 100%; background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                                    <div style="float: left; width: 120px; margin-right: 20px; text-align: center;">
                                        <img src="{$productImage}" width="100" height="100" style="border: 1px solid #ddd; border-radius: 5px; padding: 5px; background: white;" alt="{$productTitle}" />
                                    </div>
                                    <div style="margin-left: 150px; padding: 10px;">
                                        <span style="font-size:22px; color: #333; display: block; margin-bottom: 10px;">
                                            <strong>{$productTitle}</strong>
                                        </span>
                                        <span style="display: block; margin-bottom: 5px; font-size: 16px;">
                                            <strong>{$productMoq} <span style="color: #466da0;">{$productType}</span> : الكمية المطلوبة</strong>
                                        </span>
                                        <span style="display: block; font-size: 16px;">
                                            <strong style="color: #002757;">{$productPrice} : القيمة التقريبية</strong>
                                        </span>
                                    </div>
                                    <div style="clear:both;"></div>
                                </div>
                                
                                <!-- Contact Details -->
                                <p style="line-height:1.5em; text-align:center; font-size:1.4em; background-color:#eaeaea; margin:20px 0 10px; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:15px; color: #002757; border-radius: 5px;">
                                    تفاصيل الإتصال
                                </p>
                                
                                <div style="width: 100%; background-color: #f0f8ff; padding: 20px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #92AED2;">
                                    {$contactInfo}
                                </div>
                                
                                <!-- Login Prompt -->
                                <div style="margin-bottom: 20px;">
                                    <p style="line-height:1.5em; text-align:center; font-size:1.4em; background-color:#eaeaea; margin:20px 0 10px; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:15px; color: #002757; border-radius: 5px;">
                                        هل دخلت الى حسابك من قبل ؟
                                    </p>
                                    <div style="background-color: #e8f4f8; padding: 15px; border-radius: 5px; text-align: center; font-size: 16px; color: #333;">
                                        إستخدم عنوانك البريدى الحالى وكلمة عبور 123456 للدخول الى حسابك  
                                        <a href="https://egyptmart.shop/sign-in.php?email={$userEmail}" 
                                           style="display: inline-block; margin-right: 20px; padding: 8px 20px; background-color: #466da0; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                                            سجل دخول الآن
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Free Posting Options -->
                                <div style="margin-bottom: 20px;">
                                    <p style="color: #000000; font-size: 20px; text-align:center; font-weight: 900; background-color: #eaeaea; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                                        ربما تريد أيضا نشر التالى  
                                        <strong style="color:#da4e1e; font-size: 20px;">مجانا</strong>
                                    </p>
                                    
                                    <table align="center" style="width: 100%; max-width: 600px; margin: 0 auto; border-collapse: separate; border-spacing: 10px;">
                                        <tr>
                                            <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 5px;">
                                                <a href="https://egyptmart.shop/sign-in.php?email={$userEmail}&redirect=https://egyptmart.shop/product-sel-cat.php" 
                                                   style="color: #466da0; font-size: 18px; font-weight: 600; text-decoration: none; display: block;">
                                                    منتجات أو خدمات
                                                </a>
                                            </td>
                                            <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 5px;">
                                                <a href="https://egyptmart.shop/sign-in.php?email={$userEmail}&redirect=https://egyptmart.shop/post-buy-req.php" 
                                                   style="color: #466da0; font-size: 18px; font-weight: 600; text-decoration: none; display: block;">
                                                    طلبــات شــــراء
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 5px;">
                                                <a href="https://egyptmart.shop/sign-in.php?email={$userEmail}&redirect=https://egyptmart.shop/post-sell-offer.php" 
                                                   style="color: #466da0; font-size: 18px; font-weight: 600; text-decoration: none; display: block;">
                                                    عروض بيع خاصة
                                                </a>
                                            </td>
                                            <td style="padding: 10px; text-align: center; background-color: #f0f8ff; border-radius: 5px;">
                                                <a href="https://egyptmart.shop/sign-in.php?email={$userEmail}&redirect=https://egyptmart.shop/post-tender.php" 
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
                                        <a href="https://egyptmart.shop/sign-in.php?email={$userEmail}&redirect=https://egyptmart.shop/why_egyptmart.php" 
                                           style="font-size: 16px; color: #466da0; text-decoration: none; font-weight: normal;">
                                            إضغط هنا 
                                        </a> 
                                        لتتواصل معنا وتخبرنا بما تريد 
                                        <a href="https://egyptmart.shop/sign-in.php?email={$userEmail}&redirect=https://egyptmart.shop/membership_plans.php">
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
                            <div style="width:100%; float:left; text-align:center; padding: 15px 0; background-color: #f5f5f5; border-radius: 5px;">
                                <a href="https://egyptmart.shop/dir.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 10px;">
                                    منتجات أو خدمات
                                </a> | 
                                <a href="https://egyptmart.shop/sale-offers.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 10px;">
                                    عروض بيع خاصة
                                </a> | 
                                <a href="https://egyptmart.shop/buyleads.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 10px;">
                                    طلبات شراء
                                </a> | 
                                <a href="https://egyptmart.shop/tenders.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 10px;">
                                    مناقصات ومزايدات
                                </a>
                            </div>
                            
                            <!-- Support Info -->
                            <div style="width:100%; padding-left: 0px; float:left; color:#666; margin-top: 20px; text-align: center;">
                                <p style="margin:10px 0px 2px; font-size:14px;">
                                    لمزيد من المساعدة رجاء إرسال ميل الى info@egyptmart.shop أو الاتصال على رقم 01030029097
                                </p>
                            </div>
                            
                            <br/><br/>
                            
                            <!-- Signature -->
                            <div style="width:100%; padding-left: 0px; text-align:center; color:#666; border-top: 2px solid #eaeaea; padding-top: 20px;">
                                <p style="margin:10px 0px 2px; font-size:14px;">
                                    <span style="font-size: 18px; font-weight: 600;">مع تحيات</span> 
                                    <br/>
                                    <span><b><span style="color: #466da0; font-size: 16px;">فريق الدعم</span></b></span>
                                    <br/>
                                    <span style="color: #da4e1e; font-size: 18px; font-weight: 700;">We Promote Your Business !</span>
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