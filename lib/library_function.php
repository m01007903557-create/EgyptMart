<?php
// library_function.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// منع الوصول المباشر
//if (!defined('IN_SITE')) {
    //exit('الوصول المباشر غير مسموح');
//}

/**
 * كلاس الدوال المكتبية
 */
class LibraryFunction {
    
    /**
     * الحصول على محتوى الصفحة حسب العنوان واللغة
     * 
     * @param string $title عنوان الصفحة
     * @param string|null $lang رمز اللغة (اختياري، 'hi' للهندية، غيره للإنجليزية)
     * @return string محتوى الصفحة
     */
    public static function entry_structure_content(string $title, ?string $lang = null): string {
        global $con;
        
        if (empty($title)) {
            return '';
        }
        
        $title = mysqli_real_escape_string($con, $title);
        $sql = "SELECT * FROM cms WHERE title = '{$title}' LIMIT 1";
        $result = mysqli_query($con, $sql);
        
        if (!$result || mysqli_num_rows($result) == 0) {
            return '';
        }
        
        $row = mysqli_fetch_assoc($result);
        
        // إذا كانت اللغة الهندية مطلوبة وتوجد، وإلا استخدم الإنجليزية
        if ($lang === 'hi' && isset($row['ln_hi'])) {
            return $row['ln_hi'];
        }
        
        return $row['ln_en'] ?? '';
    }
}

/**
 * توليد سلسلة عشوائية من الحروف
 * 
 * @param int $digits عدد الأحرف المطلوبة
 * @return string سلسلة عشوائية
 */
function Rand_String(int $digits): string {
    if ($digits <= 0) {
        return '';
    }
    
    $alphanum = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    // استخدام random_int للأمان العشوائي
    $result = '';
    $max = strlen($alphanum) - 1;
    
    for ($i = 0; $i < $digits; $i++) {
        $result .= $alphanum[random_int(0, $max)];
    }
    
    return $result;
}

/**
 * توليد سلسلة عشوائية من الحروف والأرقام (إضافة مفيدة)
 * 
 * @param int $digits عدد الأحرف المطلوبة
 * @param bool $include_numbers تضمين الأرقام
 * @return string سلسلة عشوائية
 */
function Rand_String_Extended(int $digits, bool $include_numbers = true): string {
    if ($digits <= 0) {
        return '';
    }
    
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($include_numbers) {
        $characters .= '0123456789';
    }
    
    $result = '';
    $max = strlen($characters) - 1;
    
    for ($i = 0; $i < $digits; $i++) {
        $result .= $characters[random_int(0, $max)];
    }
    
    return $result;
}

/**
 * الحصول على محتوى CMS بشكل مباشر (دالة مساعدة)
 * 
 * @param string $title عنوان الصفحة
 * @param string $lang اللغة (en أو hi)
 * @return string محتوى الصفحة
 */
function get_cms_content(string $title, string $lang = 'en'): string {
    return LibraryFunction::entry_structure_content($title, $lang);
}