<?php
/**
 * File: readcity.php

 * Description: البحث التلقائي عن المدن (AutoComplete)
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
$search_pattern = $search_term . '%';

// بناء شرط المدينة
$city_condition = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $city_condition = " AND ct_cn_id = ?";
}

// استعلام البحث عن المدن
$query = "SELECT ct_id, ct_name FROM city WHERE ct_name LIKE ? $city_condition ORDER BY ct_name ASC LIMIT 50";

$stmt = mysqli_prepare($con, $query);

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    mysqli_stmt_bind_param($stmt, 'si', $search_pattern, $loc_id);
} else {
    mysqli_stmt_bind_param($stmt, 's', $search_pattern);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0):
?>
<ul id="state-list" class="countrytwo">
    <?php while ($row = mysqli_fetch_assoc($result)): 
        $ct_name = htmlspecialchars($row['ct_name'] ?? '', ENT_QUOTES, 'UTF-8');
    ?>
    <li onClick="selectCity('<?php echo $ct_name; ?>');">
        <span style="color:red"><?php echo $ct_name; ?></span>
    </li>
    <?php endwhile; ?>
</ul>
<?php 
endif;

mysqli_stmt_close($stmt);
?>