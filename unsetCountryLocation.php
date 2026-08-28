<?php
/**
 * File: unsetCountryLocation.php
 * Description: إلغاء تعيين موقع المستخدم (الدولة) من الكوكيز
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();

require_once __DIR__ . '/common.php';

// حذف كوكيز الموقع
setcookie("loc_id", "", time() - 3600, "/");
setcookie("loc_id", "", time() - 3600, "/ajax-file");

// تعيين كوكيز الوضع العام (صالحة لمدة ساعة)
setcookie("is_global", "1", time() + 3600, "/");

// إعادة التوجيه إلى الصفحة الرئيسية
header("Location: index.php");
exit;
?>