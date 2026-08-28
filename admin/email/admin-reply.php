<?php
/**
 * File: admin-reply.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: قالب البريد الإلكتروني للرد على استفسارات العملاء
 * Template for sending reply emails to customer enquiries
 * 
 * Variables Expected:
 * - $row_to: Object containing recipient details (name_prefix, fname, lname)
 * - $enq_details: String containing enquiry details
 * - $this->reply_subject: String reply subject
 * - $this->reply_content: String reply message content
 * - $plans: String indicating plan type
 * - $is_contact: Integer indicating if contact page
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

$enquiryDetails = $enq_details ?? 'No details provided';
$replySubject = htmlspecialchars($this->reply_subject ?? 'No Subject', ENT_QUOTES, 'UTF-8');
$replyContent = html_entity_decode($this->reply_content ?? 'No content', ENT_QUOTES, 'UTF-8');
$currentDate = date('Y/m/d');

// Determine reply link based on context
$replyLink = 'https://egyptmart.shop/membership_plans.php';
$replyText = 'يمكنك الرد الآن';

if (isset($plans) && $plans === 'Advertisements Requirements') {
    $replyLink = 'https://egyptmart.shop/advertise-with-us.php';
} elseif (isset($is_contact) && $is_contact == 1) {
    $replyLink = 'https://egyptmart.shop/contact_us.php';
}

// Build email template
$message1 = <<<HTML
<div class="b9_m2 b10_m2" id="detable" style="direction: rtl; font-family: Arial, Helvetica, sans-serif;">
    <table class="lh2_m2" border="0" width="100%" cellpadding="0" cellspacing="0" style="max-width: 1000px; margin: 0 auto;">
        <tbody>
            <tr class="f5_m2">
                <td class="sh_m2">
                    <span style="width:750px;word-wrap:break-word;" id="wbr">
                        <div style="width: 90%; height: auto; border: 10px solid #92AED2; float: left; padding: 10px; margin-top:10px; background-color: #ffffff;">
                            
                            <!-- Header Section -->
                            <div style="height: 100px; width: 100%; float: left; margin-bottom: 20px;">
                                <div style="height: 100px; width: 30%; float: left;">
                                    <img style="width: 100%; color: #00F; font-size: 22px; font-weight: bold;" 
                                         alt="EgyptMART" 
                                         src="https://egyptmart.shop/images/Mlogo.png" />
                                </div>
                                <div style="height:100px; width:43%; float:left;">
                                    <h2 style="font-size: 20px; color:#466da0; text-align: center; margin-top:10px; margin-bottom:0px;">
                                        رد على إستفسار <br> من إدارة المنصة
                                    </h2>
                                </div>
                                <div style="min-height: 100px; width: 27%; float: right; padding-top: 3px;">
                                    <span style="font-size: 15px; float: right; padding-bottom: 0px; clear: both; font-weight: bold; color:#000000;">
                                        Enquiry
                                    </span>
                                    <span style="float: right; font-size: 13px; padding-top: 0px; clear: both; color:#000000;">
                                        {$currentDate}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Recipient Name -->
                            <div style="width:100%; color:#000000; margin-bottom: 15px;">
                                <p style="font-size:16px; text-align:right; color:#000000; margin: 5px 0;">
                                    <strong>{$recipientName} : الســادة</strong>
                                </p>
                            </div>
                            
                            <!-- Enquiry Details Section -->
                            <div style="max-width:575px; line-height:18px; font-size:12px; font-family:Arial,Helvetica,sans-serif;">
                                <p style="font-size:1.4em; margin:0; padding:.5em 0 0.5em; line-height:1.4em; text-align:center;">
                                    <b>رد على إستفسـار <span style="color: blue">EgyptMART</span> من إدارة المنصة</b>
                                </p>               
                                
                                <p style="line-height:1.5em; text-align:center; font-size:1.2em; background-color:#eaeaea; 
                                          margin:0; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:.4em .4em .4em">
                                    تفاصيل الإستفسـار
                                </p>
                                
                                <div style="width: 100%; line-height:1.5em; font-size:12px; font-family:Arial,Helvetica,sans-serif; 
                                            padding:0.5em 0 0.9em 1em; background-color: #f9f9f9; margin: 5px 0;">
                                    {$enquiryDetails}
                                </div>
                                
                                <div style="clear:both;"></div>
                                <br>
                                
                                <!-- Admin Reply Section -->
                                <div style="clear:both;">
                                    <p style="line-height:1.5em; text-align:center; font-size:1.2em; background-color:#eaeaea; 
                                              margin:0; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:.4em .4em .4em">
                                        رد من أدمن المنصة 
                                    </p>
                                </div>
                                <br/>
                                
                                <span style="font-size:1.0em; font-weight:normal;">
                                    <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; 
                                              font-family: HelveticaNeue, sans-serif; text-align: left;" align="left">
                                        <strong>Subject:</strong> {$replySubject}
                                    </p>
                                    <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; 
                                              font-family: HelveticaNeue, sans-serif; text-align: left;" align="left">
                                        <strong>Message:</strong><br>
                                        {$replyContent}
                                    </p>
                                </span>
                                
                                <br><br>
HTML;

// Add reply link section
$message1 .= <<<HTML
                                <div style="clear:both;">
                                    <p style="line-height:1.5em; text-align:center; font-size:1.2em; background-color:#eaeaea; 
                                              margin:0; font-family:Arial,Helvetica,sans-serif; font-weight:bold; padding:.4em .4em .4em">
                                        عند رغبتك الرد علينا  
                                        <a style="color: #466da0; text-decoration: none; font-weight: bold; margin-right: 5px;" 
                                           href="{$replyLink}">
                                            {$replyText}
                                        </a>
                                    </p>
                                </div>
HTML;

// Footer section
$message1 .= <<<HTML
                                <br>
                                
                                <!-- Support Info -->
                                <table style="font-family:Arial,Helvetica,sans-serif; font-size:13px; width: 100%; margin-top: 20px;" 
                                       cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="line-height:20px" valign="top">
                                                <span style="color:blue">EgyptMART</span> الدعم الفنى<br>
                                                Call us on +201030029097
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <span style="color:rgb(171,172,172); font-size:11px; display: block; margin-top: 10px;">
                                    You are receiving this mailer as a registered member of <span style="color:blue">EgyptMART</span>.
                                </span>
                            </div>
                            
                            <!-- Divider -->
                            <div style="height:2px; width:100%; float:left; border-bottom: 3px dotted #D8AED8; margin: 15px 0;">
                            </div>
                            
                            <!-- Navigation Links -->
                            <div style="width:100%; float:left; text-align:center; padding: 10px 0;">
                                <a href="https://egyptmart.shop/dir.php" 
                                   style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold; margin: 0 5px;">
                                    Product & Suppliers
                                </a> | 
                                <a href="https://egyptmart.shop/sale-offers.php" 
                                   style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold; margin: 0 5px;">
                                    Sale Offers
                                </a> | 
                                <a href="https://egyptmart.shop/buyleads.php" 
                                   style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold; margin: 0 5px;">
                                    Buy Requests
                                </a> | 
                                <a href="https://egyptmart.shop/tenders.php" 
                                   style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold; margin: 0 5px;">
                                    Tenders
                                </a>
                            </div>
                            
                            <!-- Footer Note -->
                            <div style="width:100%; padding-left: 0px; float:left; color:#808080;">
                                <p style="margin:10px 0px 2px; font-size: 12px;">
                                    You have received this mail virtue of your opt-in subscription for Enquiry on 
                                    <font style="color:blue;">EgyptMART</font>.
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

// Note: This template should be included in email sending scripts
// The $message1 variable is now ready to be used in mail() or SMTP functions
?>