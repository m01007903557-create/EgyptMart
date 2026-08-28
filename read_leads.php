<?php
/**
 * File: read_leads.php

 * Description: البحث التلقائي عن طلبات الشراء (Buy Leads) للـ AutoComplete
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

// بناء شرط الدولة
$country_condition = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $country_condition = " AND u.country = $loc_id";
}

// استعلام البحث عن طلبات الشراء
$query = "SELECT br.br_pd_name 
          FROM buy_requirement br
          INNER JOIN measurement_unit mu ON br.br_estimate_qty_unit = mu.mu_id
          INNER JOIN user u ON u.usr_id = br.br_u_id
          LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id
          LEFT JOIN country c ON c.cn_id = u.country
          LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id
          WHERE br.br_pd_name LIKE ?
            AND br.br_display_status = '1'
            AND br.br_status = '1'
            AND br.br_approval_status = '1'
            $country_condition
          GROUP BY br.br_pd_name
          ORDER BY br.br_pd_name ASC
          LIMIT 50";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 's', $search_pattern);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0):
?>
<ul id="country-list" class="countrytwo">
    <?php while ($row = mysqli_fetch_assoc($result)): 
        $br_pd_name = htmlspecialchars($row['br_pd_name'] ?? '', ENT_QUOTES, 'UTF-8');
        $search_url = "https://www.egyptmart.online/search.php?rctyp=buy_lead&keywords=" . urlencode($br_pd_name);
    ?>
    <li onClick="selectCountry('<?php echo $br_pd_name; ?>');">
        <a href="<?php echo $search_url; ?>">
            <span style="color:red"><?php echo $br_pd_name; ?></span>
        </a>
    </li>
    <?php endwhile; ?>
</ul>
<?php 
endif;

mysqli_stmt_close($stmt);
?>