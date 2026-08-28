<?php
/**
 * File: validation.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: فئة التحقق من صحة البيانات وإرسال البريد الإلكتروني
 * Validation and email sending class
 * 
 * Features:
 * - التحقق من صحة الأسماء
 * - التحقق من صحة البريد الإلكتروني
 * - التحقق من صحة أرقام الهواتف
 * - إرسال البريد الإلكتروني العادي
 * - إرسال البريد الإلكتروني مع مرفقات
 * 
 * @package Validation
 * @subpackage Email
 * @license GPL
 */

declare(strict_types=1);

// Prevent direct access
//if (!defined('IN_EGYPTMART') && !defined('STDIN')) {
    //exit('Direct access not allowed');
//}

/**
 * Class validate
 * 
 * Validation and email utilities
 */
class validate
{
    /**
     * Validate name (letters, spaces, and underscore only)
     * 
     * @param string $str Name to validate
     * @return bool True if valid
     */
    public static function is_name(string $str): bool
    {
        $pattern = "/^([A-Za-z_\ ]*)$/";
        return (bool)preg_match($pattern, $str);
    }
    
    /**
     * Validate email address
     * 
     * @param string $str Email to validate
     * @return bool True if valid
     */
    public static function is_email(string $str): bool
    {
        // Modern email validation using filter_var
        return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (10-18 digits, plus, dot, space allowed)
     * 
     * @param string $str Phone number to validate
     * @return bool True if valid
     */
    public static function is_phone(string $str): bool
    {
        $pattern = "/^([0-9_\ \.\+]{10,18})$/";
        return (bool)preg_match($pattern, $str);
    }
    
    /**
     * Validate username (letters, numbers, underscore)
     * 
     * @param string $str Username to validate
     * @return bool True if valid
     */
    public static function is_username(string $str): bool
    {
        $pattern = "/^([A-Za-z0-9_]*)$/";
        return (bool)preg_match($pattern, $str);
    }
    
    /**
     * Send plain email
     * 
     * @param string $mailto Recipient email
     * @param string $from_mail Sender email
     * @param string $from_name Sender name
     * @param string $replyto Reply-to email
     * @param string $cc CC email
     * @param string $subject Email subject
     * @param string $message Email message (HTML)
     * @param string|null $filename Attachment filename (optional)
     * @param string|null $path Attachment path (optional)
     * @return bool True if sent successfully
     */
    public static function send_mail(
        string $mailto,
        string $from_mail,
        string $from_name,
        string $replyto,
        string $cc,
        string $subject,
        string $message,
        ?string $filename = null,
        ?string $path = null
    ): bool {
        // If attachment is provided, use attachment method
        if ($filename !== null && $path !== null) {
            return self::mail_attachment($mailto, $from_mail, $from_name, $replyto, $subject, $message, $filename, $path);
        }
        
        // Generate unique boundary
        $uid = md5(uniqid((string)time(), true));
        
        // Build headers
        $headers = [];
        $headers[] = "From: {$from_name} <{$from_mail}>";
        $headers[] = "Reply-To: {$replyto}";
        
        if (!empty($cc)) {
            $headers[] = "CC: {$cc}";
        }
        
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: multipart/mixed; boundary=\"{$uid}\"";
        $headers[] = "";
        $headers[] = "--{$uid}";
        $headers[] = "Content-Type: text/html; charset=utf-8";
        $headers[] = "Content-Transfer-Encoding: 7bit";
        $headers[] = "";
        $headers[] = $message;
        $headers[] = "";
        $headers[] = "--{$uid}--";
        
        // Send email
        return mail($mailto, $subject, "", implode("\r\n", $headers));
    }
    
    /**
     * Send email with attachment
     * 
     * @param string $mailto Recipient email
     * @param string $from_mail Sender email
     * @param string $from_name Sender name
     * @param string $replyto Reply-to email
     * @param string $subject Email subject
     * @param string $message Email message
     * @param string $filename Attachment filename
     * @param string $path Attachment path
     * @return bool True if sent successfully
     */
    public static function mail_attachment(
        string $mailto,
        string $from_mail,
        string $from_name,
        string $replyto,
        string $subject,
        string $message,
        string $filename,
        string $path
    ): bool {
        // Build full file path
        $file = rtrim($path, '/') . '/' . ltrim($filename, '/');
        
        // Check if file exists
        if (!file_exists($file)) {
            error_log("Attachment file not found: {$file}");
            return false;
        }
        
        // Read file
        $file_size = filesize($file);
        $handle = fopen($file, "r");
        if ($handle === false) {
            error_log("Cannot open attachment file: {$file}");
            return false;
        }
        
        $content = fread($handle, $file_size);
        fclose($handle);
        
        // Encode file content
        $content = chunk_split(base64_encode($content));
        
        // Generate unique boundary
        $uid = md5(uniqid((string)time(), true));
        $name = basename($file);
        
        // Build HTML message
        $htmlMessage = $this->buildHtmlMessage($subject, $message, $from_mail);
        
        // Build headers
        $headers = [];
        $headers[] = "From: {$from_name} <{$from_mail}>";
        $headers[] = "Reply-To: {$replyto}";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: multipart/mixed; boundary=\"{$uid}\"";
        $headers[] = "";
        $headers[] = "This is a multi-part message in MIME format.";
        $headers[] = "--{$uid}";
        $headers[] = "Content-type: text/html; charset=utf-8";
        $headers[] = "Content-Transfer-Encoding: 7bit";
        $headers[] = "";
        $headers[] = $htmlMessage;
        $headers[] = "";
        $headers[] = "--{$uid}";
        $headers[] = "Content-Type: application/octet-stream; name=\"{$name}\"";
        $headers[] = "Content-Transfer-Encoding: base64";
        $headers[] = "Content-Disposition: attachment; filename=\"{$name}\"";
        $headers[] = "";
        $headers[] = $content;
        $headers[] = "";
        $headers[] = "--{$uid}--";
        
        // Send email
        $sent = mail($mailto, $subject, "", implode("\r\n", $headers));
        
        // Delete file after sending if successful
        if ($sent) {
            @unlink($file);
        }
        
        return $sent;
    }
    
    /**
     * Build HTML email template
     * 
     * @param string $subject Email subject
     * @param string $message Email content
     * @param string $fromEmail Sender email
     * @return string HTML email
     */
    private static function buildHtmlMessage(string $subject, string $message, string $fromEmail): string
    {
        $siteLogo = 'http://hashlive.com/images/logo.gif';
        
        return '
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <title>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</title>
            </head>
            <body>
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:0 auto; font-family:Arial,sans-serif;">
                    <tr>
                        <td style="background:#fff6e7; padding:20px;">
                            <table width="100%" cellpadding="10" cellspacing="0" style="background:#ffffff; border:1px solid #e0e0e0;">
                                <tr>
                                    <td align="center" style="padding:20px;">
                                        <img src="' . htmlspecialchars($siteLogo, ENT_QUOTES, 'UTF-8') . '" alt="Logo" style="max-width:200px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background:#CCFFFF; padding:10px; text-align:center;">
                                        <strong>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:20px;">
                                        ' . $message . '
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px; border-top:1px solid #e0e0e0;">
                                        <strong>Email:</strong> ' . htmlspecialchars($fromEmail, ENT_QUOTES, 'UTF-8') . '
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
        </html>';
    }
    
    /**
     * Validate URL
     * 
     * @param string $url URL to validate
     * @return bool True if valid
     */
    public static function is_url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Validate integer
     * 
     * @param mixed $value Value to validate
     * @return bool True if valid integer
     */
    public static function is_int($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Validate float
     * 
     * @param mixed $value Value to validate
     * @return bool True if valid float
     */
    public static function is_float($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }
    
    /**
     * Sanitize input string
     * 
     * @param string $str Input string
     * @return string Sanitized string
     */
    public static function sanitize(string $str): string
    {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }
}
?>