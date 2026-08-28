<?php
// validate.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * كلاس التحقق من صحة البيانات
 */
class Validate {
    
    // ==================== دوال التحقق ====================
    
    /**
     * التحقق من صحة الاسم (حروف ومسافات فقط)
     * 
     * @param string $str النص المراد التحقق منه
     * @return bool صحيح إذا كان الاسم صحيحاً
     */
    public static function is_name(string $str): bool {
        $str = trim($str);
        if (empty($str)) return false;
        
        // يسمح بالحروف العربية والإنجليزية والمسافات
        $pattern = '/^[\p{Arabic}A-Za-z\s]+$/u';
        return preg_match($pattern, $str) === 1;
    }
    
    /**
     * التحقق من صحة البريد الإلكتروني
     * 
     * @param string $str البريد الإلكتروني
     * @return bool صحيح إذا كان البريد صحيحاً
     */
    public static function is_email(string $str): bool {
        $str = trim($str);
        if (empty($str)) return false;
        
        // استخدام فلتر PHP المدمج للتحقق من البريد
        return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * التحقق من صحة رقم الهاتف
     * 
     * @param string $str رقم الهاتف
     * @return bool صحيح إذا كان الرقم صحيحاً
     */
    public static function is_phone(string $str): bool {
        $str = trim($str);
        if (empty($str)) return false;
        
        // يسمح بالأرقام والمسافات والنقاط والشرطات وعلامة +
        $pattern = '/^[0-9\s\.\+\-\(\)]{8,20}$/';
        return preg_match($pattern, $str) === 1;
    }
    
    /**
     * التحقق من صحة الموقع الإلكتروني
     * 
     * @param string $str رابط الموقع
     * @return bool صحيح إذا كان الرابط صحيحاً
     */
    public static function is_website(string $str): bool {
        $str = trim($str);
        if (empty($str)) return false;
        
        // إضافة http:// إذا لم يكن موجوداً
        if (!preg_match('/^https?:\/\//i', $str)) {
            $str = 'http://' . $str;
        }
        
        return filter_var($str, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * التحقق من صحة اسم المستخدم
     * 
     * @param string $str اسم المستخدم
     * @return bool صحيح إذا كان اسم المستخدم صحيحاً
     */
    public static function is_username(string $str): bool {
        $str = trim($str);
        if (empty($str)) return false;
        
        // حروف وأرقام وشرطة سفلية فقط، طول 3-20 حرف
        $pattern = '/^[A-Za-z0-9_]{3,20}$/';
        return preg_match($pattern, $str) === 1;
    }
    
    /**
     * التحقق من صحة رابط الويب (نفس is_website)
     * 
     * @param string $str رابط الويب
     * @return bool صحيح إذا كان الرابط صحيحاً
     */
    public static function is_weblink(string $str): bool {
        return self::is_website($str);
    }
    
    /**
     * التحقق من صحة الرقم
     * 
     * @param mixed $value القيمة المراد التحقق منها
     * @param bool $positive فقط أرقام موجبة
     * @return bool صحيح إذا كان الرقم صحيحاً
     */
    public static function is_number($value, bool $positive = true): bool {
        if (!is_numeric($value)) return false;
        if ($positive && $value <= 0) return false;
        return true;
    }
    
    /**
     * التحقق من صحة التاريخ
     * 
     * @param string $date التاريخ
     * @param string $format الصيغة المتوقعة
     * @return bool صحيح إذا كان التاريخ صحيحاً
     */
    public static function is_date(string $date, string $format = 'Y-m-d'): bool {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * التحقق من صحة الرمز البريدي
     * 
     * @param string $zip الرمز البريدي
     * @return bool صحيح إذا كان الرمز صحيحاً
     */
    public static function is_zip(string $zip): bool {
        $zip = trim($zip);
        // أرقام فقط، طول 3-10 أحرف
        return preg_match('/^[0-9]{3,10}$/', $zip) === 1;
    }
    
    /**
     * التحقق من أن النص فارغ
     * 
     * @param string $str النص المراد التحقق منه
     * @return bool صحيح إذا كان النص فارغاً
     */
    public static function is_empty(string $str): bool {
        return trim($str) === '';
    }
    
    /**
     * التحقق من طول النص
     * 
     * @param string $str النص
     * @param int $min الحد الأدنى
     * @param int $max الحد الأقصى
     * @return bool صحيح إذا كان الطول ضمن النطاق
     */
    public static function length(string $str, int $min, int $max): bool {
        $len = mb_strlen(trim($str), 'UTF-8');
        return $len >= $min && $len <= $max;
    }
    
    /**
     * التحقق من تطابق النص مع نمط معين
     * 
     * @param string $str النص
     * @param string $pattern النمط
     * @return bool صحيح إذا تطابق
     */
    public static function matches(string $str, string $pattern): bool {
        return preg_match($pattern, $str) === 1;
    }
    
    // ==================== دوال التنسيق ====================
    
    /**
     * تنسيق المدخلات للتخزين الآمن
     * 
     * @param string $ip النص المدخل
     * @return string النص بعد التنسيق
     */
    public static function formatInput(string $ip): string {
        return trim(htmlspecialchars($ip, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * تنسيق المخرجات للعرض
     * 
     * @param string $op النص المراد عرضه
     * @return string النص بعد التنسيق
     */
    public static function formatOutput(string $op): string {
        return html_entity_decode($op, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * تنظيف المدخلات من الأحرف الخطيرة
     * 
     * @param string $str النص
     * @return string النص المنظف
     */
    public static function sanitize(string $str): string {
        $str = strip_tags($str);
        $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        return trim($str);
    }
    
    /**
     * تحويل النص إلى عنوان URL صديق (slug)
     * 
     * @param string $str النص
     * @return string النص المحول
     */
    public static function slugify(string $str): string {
        $str = mb_strtolower($str, 'UTF-8');
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/[\s-]+/', '-', $str);
        return trim($str, '-');
    }
    
    // ==================== دوال إرسال البريد ====================
    
    /**
     * إرسال بريد إلكتروني
     * 
     * @param string $mailto البريد المستلم
     * @param string $from_mail بريد المرسل
     * @param string $from_name اسم المرسل
     * @param string $replyto بريد الرد
     * @param string $cc نسخة كربونية
     * @param string $subject عنوان الرسالة
     * @param string $message نص الرسالة
     * @param string|null $filename اسم الملف المرفق
     * @param string|null $path مسار الملف المرفق
     * @return bool نجاح أو فشل الإرسال
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
        
        // التحقق من صحة البريد
        if (!self::is_email($mailto) || !self::is_email($from_mail)) {
            return false;
        }
        
        $uid = md5(uniqid((string)time(), true));
        
        // بناء الرؤوس
        $headers = "From: " . self::formatInput($from_name) . " <" . $from_mail . ">\r\n";
        $headers .= "Reply-To: " . $replyto . "\r\n";
        if (!empty($cc)) {
            $headers .= "CC: " . $cc . "\r\n";
        }
        $headers .= "MIME-Version: 1.0\r\n";
        
        // إذا كان هناك مرفق
        if ($filename && $path && file_exists($path)) {
            $content = file_get_contents($path);
            $content = chunk_split(base64_encode($content));
            
            $headers .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"\r\n\r\n";
            $headers .= "--" . $uid . "\r\n";
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            $headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $headers .= $message . "\r\n\r\n";
            $headers .= "--" . $uid . "\r\n";
            $headers .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"\r\n";
            $headers .= "Content-Transfer-Encoding: base64\r\n";
            $headers .= "Content-Disposition: attachment; filename=\"" . $filename . "\"\r\n\r\n";
            $headers .= $content . "\r\n";
            $headers .= "--" . $uid . "--";
            
            $body = "";
        } else {
            // بريد عادي بدون مرفقات
            $headers .= "Content-Type: text/html; charset=utf-8\r\n";
            $headers .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $body = $message;
        }
        
        // إرسال البريد
        return mail($mailto, $subject, $body, $headers);
    }
    
    /**
     * إرسال بريد إلكتروني بسيط (بدون مرفقات)
     * 
     * @param string $mailto البريد المستلم
     * @param string $from_mail بريد المرسل
     * @param string $from_name اسم المرسل
     * @param string $subject عنوان الرسالة
     * @param string $message نص الرسالة
     * @return bool نجاح أو فشل الإرسال
     */
    public static function send_simple_mail(
        string $mailto,
        string $from_mail,
        string $from_name,
        string $subject,
        string $message
    ): bool {
        return self::send_mail($mailto, $from_mail, $from_name, $from_mail, '', $subject, $message);
    }
}
?>