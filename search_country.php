<?php
/**
 * File: search_country.php

 * Description: البحث عن دولة وعرضها مع إمكانية اختيارها
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/lib/connect.php';

// استخراج المتغيرات من REQUEST
$cname = isset($_REQUEST['cname']) ? trim($_REQUEST['cname']) : '';

if (empty($cname)) {
    echo "<center><h4>Search results</h4><br/>Please enter a country name<br/><br/></center>";
    exit;
}

global $con;

// البحث عن الدولة
$get_country = "SELECT cn_id, cn_name, cn_flag FROM country WHERE cn_name = ? LIMIT 1";
$stmt = mysqli_prepare($con, $get_country);
mysqli_stmt_bind_param($stmt, 's', $cname);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$count = mysqli_num_rows($result);

if ($count > 0) {
    $row = mysqli_fetch_assoc($result);
    $cn_id = (int)$row['cn_id'];
    $cn_name = htmlspecialchars($row['cn_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $cn_flag = htmlspecialchars($row['cn_flag'] ?? '', ENT_QUOTES, 'UTF-8');
    
    echo "<center>
            <h4>Search results</h4><br/>
            <a href='#' onclick='setCountryLocation($cn_id);'>
                <img src='images/country_flag/$cn_flag' alt='$cn_name'/> &nbsp;$cn_name
            </a><br/><br/>
          </center>";
} else {
    echo "<center>
            <h4>Search results</h4><br/>
            Not found in the country list<br/><br/>
          </center>";
}

mysqli_stmt_close($stmt);
?>