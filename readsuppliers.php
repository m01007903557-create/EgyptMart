<?php
/**
 * File: readsuppliers.php

 * Description: البحث التلقائي عن الموردين (Suppliers) للـ AutoComplete
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/lib/connect.php';

// التحقق من وجود كلمة البحث
if (empty($_POST['keyword'])) {
    exit;
}

global $con;

$search_term = trim($_POST['keyword']);
$search_pattern = '%' . $search_term . '%';
$current_time = time();

// بناء شرط الدولة
$country_condition = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $country_condition = " AND u.country = $loc_id";
}

// استعلام البحث عن الموردين
$query = "SELECT bp.bnsprof_compname 
          FROM business_profile bp
          INNER JOIN user u ON u.usr_id = bp.bnsprof_uid
          INNER JOIN products p ON p.pd_uid = u.usr_id AND p.pd_status = '1'
          LEFT JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
          WHERE u.usr_mp_id IN (3, 4, 5, 15)
            $country_condition
            AND bp.bnsprof_status = '1'
            AND bp.bnsprof_compname LIKE ?
            AND pm.expiry_date > ?
          GROUP BY u.usr_id
          HAVING COUNT(p.pd_id) > 0
          ORDER BY bp.bnsprof_compname ASC
          LIMIT 50";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'si', $search_pattern, $current_time);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0):
?>
<ul id="country-list" class="countrytwo">
    <?php while ($row = mysqli_fetch_assoc($result)): 
        $company_name = htmlspecialchars($row['bnsprof_compname'] ?? '', ENT_QUOTES, 'UTF-8');
        $search_url = "http://egyptmart.shop/search.php?rctyp=Suppliers&keywords=" . urlencode($company_name);
    ?>
    <li onClick="selectCountry('<?php echo $company_name; ?>');">
        <a href="<?php echo $search_url; ?>">
            <span style="color:red"><?php echo $company_name; ?></span>
        </a>
    </li>
    <?php endwhile; ?>
</ul>
<?php 
endif;

mysqli_stmt_close($stmt);
?>