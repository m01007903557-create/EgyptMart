<?php
/**
 * اسم الملف: debug-search.php
 * الوصف: ملف تصحيح الأخطاء والبحث - متوافق مع PHP 8.3
 * الإصدار: 2.0.0
 * تاريخ التحديث: 2024-01-25
 * متطلبات PHP: 8.3
 */

// تم تعطيل عرض الأخطاء في الإنتاج - يمكن تفعيلها للتطوير فقط
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    // في وضع الإنتاج، لا نعرض الأخطاء
    error_reporting(0);
    ini_set('display_errors', 0);
}

// بدء تشغيل المخزن المؤقت وجلسة العمل
ob_start();
session_start();

// تضمين ملف الإعدادات المشتركة
require_once "../common.php";

/**
 * دالة تنفيذ الاستعلام بشكل آمن مع prepared statements
 */
function executeSecureQuery($con, $sql, $params = [], $types = "") {
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        error_log("خطأ في تحضير الاستعلام: " . mysqli_error($con));
        return false;
    }
    
    if (!empty($params) && !empty($types)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("خطأ في تنفيذ الاستعلام: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * دالة البحث حسب ID الدولة
 */
function searchCountryById($con, $pd_id) {
    // تنظيف المدخلات - التأكد من أنها أحرف آمنة للبحث
    $search_term = mysqli_real_escape_string($con, $pd_id) . '%';
    
    $sql = "SELECT * FROM country 
            WHERE cn_status = '1' 
            AND cn_name LIKE ? 
            ORDER BY cn_id ASC";
    
    return executeSecureQuery($con, $sql, [$search_term], "s");
}

/**
 * دالة البحث عن الولايات حسب الكلمات المفتاحية
 */
function searchStatesByKeywords($con, $keywords, $location_geo_country) {
    // تنظيف المدخلات
    $search_keywords = '%' . mysqli_real_escape_string($con, $keywords) . '%';
    $country_code = isset($location_geo_country[0]) ? mysqli_real_escape_string($con, $location_geo_country[0]) : '';
    
    $sql = "SELECT bp.bnsprof_state 
            FROM products p
            INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
            WHERE bp.bnsprof_compname LIKE ? 
            AND (
                (p.pd_preferred_buyer_location = 'domestic' AND p.pd_currency = ?) 
                OR (p.pd_preferred_buyer_location = 'any' AND p.pd_currency = ?) 
                OR (p.pd_preferred_buyer_location = 'my_city' AND p.pd_currency = ?)
            )
            GROUP BY p.pd_uid 
            HAVING bp.bnsprof_state > 0";
    
    return executeSecureQuery($con, $sql, [$search_keywords, $country_code, $country_code, $country_code], "ssss");
}

// تهيئة المتغيرات
$pd_id = isset($_GET['id']) ? trim($_GET['id']) : '';
$keywords = '';

// معالجة الكلمات المفتاحية
if (isset($_GET['keywords']) && !empty($_GET['keywords'])) {
    $raw_keywords = trim($_GET['keywords']);
    
    // إذا كان النص محاطاً بعلامات اقتباس
    if (substr($raw_keywords, 0, 1) === '"') {
        $keywords = substr($raw_keywords, 1, -1);
    } else {
        $keywords = $raw_keywords;
    }
}

$country_buy = [];

try {
    if (!empty($pd_id)) {
        // البحث حسب ID الدولة
        $result = searchCountryById($con, $pd_id);
        
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<h3>نتائج البحث عن الدولة: " . htmlspecialchars($pd_id) . "</h3>";
            echo "<pre>";
            while ($row = mysqli_fetch_assoc($result)) {
                print_r($row);
                $country_buy[] = $row;
            }
            echo "</pre>";
        } else {
            echo "<p>لا توجد نتائج للبحث عن: " . htmlspecialchars($pd_id) . "</p>";
        }
    } elseif (!empty($keywords)) {
        // البحث حسب الكلمات المفتاحية
        $result = searchStatesByKeywords($con, $keywords, $location_geo_country ?? []);
        
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<h3>نتائج البحث عن: " . htmlspecialchars($keywords) . "</h3>";
            echo "<pre>";
            while ($row = mysqli_fetch_assoc($result)) {
                print_r($row);
                $country_buy[] = $row;
            }
            echo "</pre>";
        } else {
            echo "<p>لا توجد نتائج للبحث عن: " . htmlspecialchars($keywords) . "</p>";
        }
    } else {
        echo "<p>يرجى توفير معاملات بحث صالحة (id أو keywords)</p>";
    }
    
    // عرض معلومات التصحيح
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
        echo "<h3>معلومات التصحيح:</h3>";
        echo "<pre>";
        echo "location_geo_country: ";
        var_dump($location_geo_country ?? null);
        echo "عدد النتائج: " . count($country_buy) . "\n";
        echo "</pre>";
    }
    
} catch (Exception $e) {
    error_log("خطأ في ملف debug-search.php: " . $e->getMessage());
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE === true) {
        echo "<h3>حدث خطأ:</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    } else {
        echo "<p>عذراً، حدث خطأ في معالجة طلبك.</p>";
    }
}

// إنهاء المخزن المؤقت وإرسال المحتوى
ob_end_flush();
?>