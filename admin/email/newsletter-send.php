<?php
/**
 * File: newsletter-send.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: قالب البريد الإلكتروني للنشرات الإخبارية والتواصل مع الأعضاء
 * Email template for newsletters and member communications
 * 
 * Variables Expected:
 * - $this->nc_content: String newsletter content
 * - $_SERVER['HTTP_HOST']: Server host for links
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('IN_EGYPTMART') && !isset($this)) {
    exit('Direct access not allowed');
}

// Get website name and host
$websiteName = getWebSiteName() ?: 'EgyptMART';
$host = $_SERVER['HTTP_HOST'] ?? 'egyptmart.shop';
$currentYear = date('Y');
$content = $this->nc_content ?? '';

// Build main email template
$message = <<<HTML
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ececec; direction: ltr;">
    <tbody>
        <tr>
            <td align="center" bgcolor="#ececec">
                <table style="margin:0 10px;" border="0" cellpadding="0" cellspacing="0" width="640">
                    <tbody>
                        <!-- Header Space -->
                        <tr>
                            <td height="20" width="640"></td>
                        </tr>
                        
                        <!-- Header Section -->
                        <tr>
                            <td class="w640" align="center" bgcolor="" width="640">
                                <table class="" border="0" cellpadding="0" cellspacing="0" width="640">
                                    <tbody>
                                        <tr>
                                            <td class="w30" width="30"></td>
                                            <td class="" height="30" width="580"></td>
                                            <td class="" width="30"></td>
                                        </tr>
                                        <tr>
                                            <td class="" width="30"></td>
                                            <td class="" width="580">
                                                <div align="center">
                                                    <p style="font-size: 30px !important; color: #edf7f7; font-family: HelveticaNeue, sans-serif; font-size: 36px; text-align: left; margin-top:0px; margin-bottom:30px;">
                                                        <strong>
                                                            <a style="color: #edf7f7; text-decoration: none;" 
                                                               href="https://{$host}" 
                                                               target="_blank">
                                                                {$websiteName}
                                                            </a>
                                                        </strong>
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="w30" width="30"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        
                        <!-- Content Background -->
                        <tr>
                            <td class="" bgcolor="#ffffff" height="30" width="640"></td>
                        </tr>
                        
                        <!-- Main Content -->
                        <tr id="simple-content-row">
                            <td class="" bgcolor="#ffffff" width="640">
                                <table class="" align="left" border="0" cellpadding="0" cellspacing="0" width="640">
                                    <tbody>
                                        <tr>
                                            <td class="" width="30"></td>
                                            <td class="" width="580">
                                                <repeater>
                                                    <layout label="Text only">
                                                        <table class="" border="0" cellpadding="0" cellspacing="0" width="580">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="" width="580">
                                                                        <div style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:18px; font-family: HelveticaNeue, sans-serif;" align="left">
                                                                            {$content}
                                                                        </div>
                                                                        <p style="font-size: 14px; line-height:24px; margin-top:0px; margin-bottom:8px; font-family: HelveticaNeue, sans-serif;" align="left">
                                                                            {$websiteName} Team.
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="w580" height="10" width="580"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </layout>
                                                </repeater>
                                            </td>
                                            <td class="w30" width="30"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        
                        <!-- Bottom Space -->
                        <tr>
                            <td class="w640" bgcolor="#ffffff" height="30" width="640">&nbsp;</td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td class="w640" align="center" bgcolor="" width="640">
                                <table class="" border="0" cellpadding="0" cellspacing="0" width="640">
                                    <tbody>
                                        <tr>
                                            <td class="" width="30"></td>
                                            <td class="" width="580">
                                                <p style="text-align: center;">
                                                    <span style="color:#FFF; font-size: 13px;">
                                                        Copyright &copy; {$currentYear} {$websiteName}. All rights reserved.
                                                    </span>
                                                </p>
                                            </td>
                                            <td class="w30" width="30"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        
                        <!-- Final Space -->
                        <tr>
                            <td class="w640" height="60" width="640">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
HTML;

// Alternative simple footer version
$message1 = <<<HTML
<div style="font-family: HelveticaNeue, sans-serif; line-height: 1.6; padding: 20px;">
    {$content}
    <p style="text-align: center; margin-top: 30px;">
        <span style="color:#000; font-size: 13px;">
            Copyright &copy; {$currentYear} {$websiteName}. All rights reserved.
        </span>
    </p>
</div>
HTML;
?>