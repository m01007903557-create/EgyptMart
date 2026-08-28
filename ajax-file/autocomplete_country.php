<?php
/**
 * File: ajax/autocomplete_country.php
 * Description: البحث عن الدول (AutoComplete) مع عرض العلم
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود نص البحث
$q = $_GET['q'] ?? '';

if (empty($q)) {
    // لا يوجد نتائج إذا كانت المدخلات غير صالحة
    exit;
}

global $con;

// تنظيف نص البحث
$search_term = trim($q);
$search_term = mysqli_real_escape_string($con, $search_term);
$search_pattern = $search_term . '%';

// البحث عن الدول
$sql = "SELECT cn_id, cn_name, cn_ph, cn_flag 
        FROM country 
        WHERE cn_status = '1' 
        AND cn_name LIKE ? 
        ORDER BY cn_name ASC 
        LIMIT 50"; // تحديد حد أقصى للنتائج

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $search_pattern);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_object($result)) {
        $country_id = (int)$row->cn_id;
        $country_name = ucfirst($row->cn_name ?? '');
        $country_ph = htmlspecialchars($row->cn_ph ?? '', ENT_QUOTES, 'UTF-8');
        $country_flag = htmlspecialchars($row->cn_flag ?? '', ENT_QUOTES, 'UTF-8');
        
        // تنسيق النتيجة مع صورة العلم: <img src="..."> CountryName|CountryID|CountryPhoneCode
        echo '<img src="images/country_flag/' . $country_flag . '" alt="' . $country_name . '"> ' . 
             htmlspecialchars($country_name, ENT_QUOTES, 'UTF-8') . '|' . 
             $country_id . '|' . 
             $country_ph . "\n";
    }
}

mysqli_stmt_close($stmt);
?>
<style>
#country_name {  
    background-image: url('images/country_flag/<?php echo $country_flag ?? ''; ?>');   
    background-position: right;   
    background-repeat: no-repeat;   
    padding-left: 17px;
}
</style>