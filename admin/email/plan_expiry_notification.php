<?php
/**
 * File: plan_expiry_notification.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: قالب البريد الإلكتروني لإشعار انتهاء صلاحية العضوية
 * Email template for membership expiry notification
 * 
 * Variables Expected:
 * - $fullname: String recipient full name
 * - $expiry_date: String membership expiry date
 * - $billing_detail: Object billing details (bh_id, bh_amount)
 * - $row: Object membership plan details (mp_amount, bnsprof_compname)
 * - $plan_name: String membership plan name
 * - $start_date: String subscription start date
 * - $user_detail: Object user details (email)
 * - $cid: String company profile link ID
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_EGYPTMART') && !isset($fullname)) {
    exit('Direct access not allowed');
}

// Generate company ID if not provided
$cid = $cid ?? (rand(1000, 9999) . md5($row->bnsprof_id ?? ''));

// Sanitize and prepare variables
$recipientName = htmlspecialchars($fullname ?? 'Valued Member', ENT_QUOTES, 'UTF-8');
$expiryDate = htmlspecialchars($expiry_date ?? 'N/A', ENT_QUOTES, 'UTF-8');
$currentDate = date('Y/m/d');
$currentDateTime = date('d-M-Y H:i T');

// Billing details
$orderId = $billing_detail->bh_id ?? 'N/A';
$paidAmount = $billing_detail->bh_amount ?? '0.00';
$annualAmount = $row->mp_amount ?? '0.00';
$companyName = $row->bnsprof_compname ?? 'Your Company';
$planName = htmlspecialchars($plan_name ?? 'JUNIOR Supplier', ENT_QUOTES, 'UTF-8');
$startDate = htmlspecialchars($start_date ?? 'N/A', ENT_QUOTES, 'UTF-8');

// User email for links
$userEmail = rawurlencode($user_detail->email ?? '');

// Base URL
$baseUrl = 'https://egyptmart.shop';
$loginRedirect = "{$baseUrl}/sign-in.php?email={$userEmail}&redirect=";

// Build email template
$message1 = <<<HTML
<div class="b9_m2 b10_m2" id="detable" style="direction: ltr; font-family: Arial, Helvetica, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0" style="max-width: 700px; margin: 0 auto;">
        <tbody>
            <tr class="f5_m2">
                <td class="sh_m2">
                    <span style="width:100%; word-wrap:break-word;" id="wbr">
                        <div style="width: 100%; height: auto; border: 10px solid #92AED2; float: left; padding: 20px; margin-top:10px; background-color: #ffffff; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                            
                            <!-- Header Section -->
                            <div style="width: 100%; float: left; margin-bottom: 10px; border-bottom: 2px solid #eaeaea; padding-bottom: 15px;">
                                <div style="width: 30%; float: left;">
                                    <img style="width: 100%; max-width: 180px; height: auto;" 
                                         alt="EgyptMART" 
                                         src="{$baseUrl}/images/logo.png" />
                                </div>
                                <div style="width: 43%; float: left; text-align: center;">
                                    <h2 style="font-size: 22px; color: #2923ae; margin:10px 0 0;">
                                        Today's Latest<br> Membership Plan Status
                                    </h2>
                                </div>
                                <div style="width: 27%; float: right; text-align: right; padding-top: 10px;">
                                    <span style="display: block; font-size: 16px; font-weight: bold; color: #333; margin-bottom: 5px;">
                                        Notification
                                    </span>
                                    <span style="display: block; font-size: 13px; color: #666;">
                                        {$currentDate}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Greeting -->
                            <div style="width:100%; margin-bottom: 15px;">
                                <p style="font-size:18px; color:#333; margin:5px 0;">
                                    <strong>Dear {$recipientName},</strong>
                                </p>
                                <p style="font-size:16px; color:#333; margin:5px 0;">
                                    Kindly note that your membership as a JUNIOR Supplier will expire on <strong style="color: #d9534f;">{$expiryDate}</strong>.
                                </p>
                            </div>
                            
                            <!-- Membership Details -->
                            <div style="width:100%; margin-bottom: 20px;">
                                <p style="line-height:1.5em; font-size:1.2em; background-color:#eaeaea; margin:0 0 10px; font-weight:bold; padding:10px; border-radius: 5px;">
                                    Your Membership Plan Details:
                                </p>
                                
                                <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px;">
                                    <table style="width: 100%; font-size: 14px; line-height: 24px;">
                                        <tr>
                                            <td style="padding: 3px 0;"><strong>Date:</strong></td>
                                            <td style="padding: 3px 0;">{$currentDateTime}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 3px 0;"><strong>Order Id:</strong></td>
                                            <td style="padding: 3px 0;">{$orderId}</td>
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
                            
                            <!-- Renewal Banner -->
                            <div style="width: 100%; margin-bottom: 20px;">
                                <a href="{$loginRedirect}{$baseUrl}/product-sel-cat.php">
                                    <img src="{$baseUrl}/admin/images/RENEWAL_mail_Notification.png" 
                                         alt="Renew Your Membership" 
                                         style="width: 100%; height: auto; border-radius: 5px;" />
                                </a>
                            </div>
                            
                            <!-- Business Page Preview -->
                            <div style="width:100%; margin-bottom: 20px;">
                                <p style="line-height:1.5em; font-size:1.2em; background-color:#eaeaea; margin:0 0 10px; font-weight:bold; padding:10px; border-radius: 5px;">
                                    Your Business Page <span style="color:red;">@{$companyName}</span> is here to PREVIEW at :
                                </p>
                                
                                <div style="background-color: #f0f8ff; padding: 15px; border-radius: 5px; border: 1px solid #92AED2;">
                                    <p style="margin:0; word-break: break-all;">
                                        <a href="{$baseUrl}/company/products.php?c={$cid}" 
                                           style="color: #466da0; text-decoration: none; font-size: 14px;">
                                            {$baseUrl}/company/products.php?c={$cid}
                                        </a>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Upgrade Benefits -->
                            <div style="width:100%; margin-bottom: 20px;">
                                <p style="color: #000; font-size: 18px; font-weight: 600; background-color: #eaeaea; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                                    You may need to take this service right NOW:
                                </p>
                                
                                <div style="margin-left: 10px;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="vertical-align: top; width: 60px; padding: 5px;">
                                                <img src="{$baseUrl}/admin/images/sponser.png" alt="Sponsor Benefits" style="max-width: 50px;">
                                            </td>
                                            <td style="padding: 5px 15px;">
                                                <ul style="margin:0; padding-left: 20px; font-size: 14px; line-height: 1.8;">
                                                    <li>Unique Showcase as an Industry/Trade leader</li>
                                                    <li>Get Top Priority Premium Listing</li>
                                                    <li>Exclusive access to Buy Leads/Tenders</li>
                                                    <li>Prestigious Sliders, Videos and Logo Image</li>
                                                    <li>Free Newsletters Email Marketing</li>
                                                    <li>Free Advertising Banners</li>
                                                    <li>Rank of Buyers to Find Your Products</li>
                                                </ul>
                                                <p style="text-align:right; margin:10px 0 0;">
                                                    <a href="{$baseUrl}" style="text-decoration: none; color: #466da0; font-weight: bold;">
                                                        Learn More >
                                                    </a>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Footer Links -->
                            <div style="clear:both; margin-bottom: 20px;">
                                <p style="line-height:1.5em; text-align:center; font-size:1.2em; background-color:#eaeaea; margin:20px 0 0; padding:15px; border-radius: 5px;">
                                    <a href="{$loginRedirect}{$baseUrl}/why_egyptmart.php" 
                                       style="font-size: 16px; color: #466da0; text-decoration: none; font-weight: normal;">
                                        Click Here
                                    </a> 
                                    to Unsubscribe or Tell us your requirements
                                    <a href="{$loginRedirect}{$baseUrl}/membership_plans.php" 
                                       style="text-decoration: none;">
                                        <strong style="color: #466da0; font-size: 19px;">NOW!</strong>
                                    </a>
                                </p>
                            </div>
                            
                            <br>
                            
                            <!-- Divider -->
                            <div style="height:2px; width:100%; float:left; border-bottom: 3px dotted #D8AED8; margin: 15px 0;">
                            </div>
                            
                            <!-- Navigation Links -->
                            <div style="width:100%; float:left; text-align:center; padding: 15px 0; background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); border-radius: 10px; margin-bottom: 15px;">
                                <a href="{$baseUrl}/dir.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block;">
                                    Products & Suppliers
                                </a> | 
                                <a href="{$baseUrl}/sale-offers.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block;">
                                    Sale Offers
                                </a> | 
                                <a href="{$baseUrl}/buyleads.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block;">
                                    Buy Requests
                                </a> | 
                                <a href="{$baseUrl}/tenders.php" 
                                   style="color:#466da0; text-decoration:none; font-size:16px; font-weight:bold; margin: 0 8px; display: inline-block;">
                                    Tenders
                                </a>
                            </div>
                            
                            <!-- Contact Info -->
                            <div style="width:100%; color:#808080; margin: 10px 0;">
                                <p style="margin:5px 0; font-size:12px; text-align:center;">
                                    For any assistance, feel free to call us at 201030029097 or just reply to this mail.
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