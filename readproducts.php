<?php
/**
 * File: readproducts.php

 * Description: البحث التلقائي عن المنتجات والشركات (AutoComplete)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

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

// استعلام البحث عن المنتجات والشركات
$query = "SELECT DISTINCT 
            s.pc_id, 
            p.pd_title, 
            s.pc_name AS subcat, 
            s.pc_sort_name, 
            c.pc_name AS cat, 
            m.pc_name AS maincat 
          FROM product_category s
          INNER JOIN product_category c ON s.pc_parent_id = c.pc_id
          INNER JOIN product_category m ON c.pc_parent_id = m.pc_id
          INNER JOIN products p ON p.pd_subcat_id = s.pc_id
          INNER JOIN user u ON p.pd_uid = u.usr_id
          INNER JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id
          LEFT JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
          WHERE m.pc_parent_id = '0'
            AND m.pc_status = '1'
            AND c.pc_status = '1'
            AND s.pc_status = '1'
            AND p.pd_subcat_id = s.pc_id
            AND bp.bnsprof_status = '1'
            AND p.pd_status = '1'
            AND (p.pd_title LIKE ? OR bp.bnsprof_compname LIKE ?)
            AND pm.expiry_date > ?
            $country_condition
          ORDER BY s.pc_name ASC
          LIMIT 50";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'ssi', $search_pattern, $search_pattern, $current_time);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0):
?>
<ul id="country-list" class="countrytwo">
    <?php while ($row = mysqli_fetch_assoc($result)): 
        $pd_title = htmlspecialchars($row['pd_title'] ?? '', ENT_QUOTES, 'UTF-8');
        $subcat = htmlspecialchars($row['subcat'] ?? '', ENT_QUOTES, 'UTF-8');
        $search_url = "https://www.egyptmart.shop/search.php?rctyp=Products&keywords=" . urlencode($pd_title);
    ?>
    <li onClick="selectCountry('<?php echo $pd_title; ?>');">
        <a href="<?php echo $search_url; ?>" class="search_pro_class">
            <?php echo $subcat; ?> >> <span style="color:red"><?php echo $pd_title; ?></span>
        </a>
    </li>
    <?php endwhile; ?>
</ul>
<?php 
endif;

mysqli_stmt_close($stmt);
?>