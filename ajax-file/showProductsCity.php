<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';

$keywords = $_REQUEST['keywords'] ?? '';
$rctype   = $_REQUEST['rctype'] ?? '';
$q        = $_GET['q'] ?? '';
$rctyp    = $_GET['rctyp'] ?? '';
$idd      = $_GET['idd'] ?? '';

if (empty($q)) exit;

global $con, $link, $sql_pd_ck;

// Use same connection variable as old code
if (!isset($link) && isset($con)) $link = $con;

$my_data      = mysqli_real_escape_string($link, $q);
$current_time = time();
$today        = date('Y-m-d');

// =============================================
// Keep same function signatures as old code
// (no extra $con parameter)
// =============================================
if (!function_exists('generateProdSearchString')) {
    function generateProdSearchString($keywords) {
        global $link;
        $conditions = [];
        foreach (explode(" ", $keywords) as $v) {
            $v = trim($v);
            if (!empty($v)) {
                $conditions[] = "pd_title LIKE '%" . mysqli_real_escape_string($link, $v) . "%'";
            }
        }
        return implode(" OR ", $conditions);
    }
}

if (!function_exists('generateSupplierSearchString')) {
    function generateSupplierSearchString($keywords) {
        global $link;
        $conditions = [];
        foreach (explode(" ", $keywords) as $v) {
            $v = trim($v);
            if (!empty($v)) {
                $conditions[] = "bnsprof_compname LIKE '%" . mysqli_real_escape_string($link, $v) . "%'";
            }
        }
        return implode(" OR ", $conditions);
    }
}

if (!function_exists('generateBuyleadSearchString')) {
    function generateBuyleadSearchString($keywords) {
        global $link;
        $conditions = [];
        foreach (explode(" ", $keywords) as $v) {
            $v = trim($v);
            if (!empty($v)) {
                $v = mysqli_real_escape_string($link, $v);
                $conditions[] = "(br_pd_name LIKE '%$v%' OR br_requirement LIKE '%$v%')";
            }
        }
        return implode(" OR ", $conditions);
    }
}

if (!function_exists('generateTenderSearchString')) {
    function generateTenderSearchString($keywords) {
        global $link;
        $conditions = [];
        foreach (explode(" ", $keywords) as $v) {
            $v = trim($v);
            if (!empty($v)) {
                $v = mysqli_real_escape_string($link, $v);
                $conditions[] = "(tnd_heading LIKE '%$v%' OR tnd_details LIKE '%$v%')";
            }
        }
        return implode(" OR ", $conditions);
    }
}

if (!function_exists('generateAuctionSearchString')) {
    function generateAuctionSearchString($keywords) {
        global $link;
        $conditions = [];
        foreach (explode(" ", $keywords) as $v) {
            $v = trim($v);
            if (!empty($v)) {
                $v = mysqli_real_escape_string($link, $v);
                $conditions[] = "(auc_heading LIKE '%$v%' OR auc_details LIKE '%$v%')";
            }
        }
        return implode(" OR ", $conditions);
    }
}

// =============================================
// Location conditions - same as old code
// =============================================
$loc_id = isset($_COOKIE['loc_id']) ? (int)$_COOKIE['loc_id'] : 0;

if ($loc_id > 0) {
    $tenderCondition  = " AND ((tnd_preferred_location='domestic' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='$loc_id')) OR (tnd_preferred_location='any' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='$loc_id')) OR (tnd_preferred_location='my_city' AND tnd_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='$loc_id'))))";
    $auctionCondition = " AND ((auc_preferred_location='domestic' AND auc_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='$loc_id')) OR (auc_preferred_location='any' AND auc_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='$loc_id')) OR (auc_preferred_location='my_city' AND auc_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='$loc_id'))))";
} else {
    $tenderCondition  = " AND ((tnd_preferred_location='domestic') OR (tnd_preferred_location='any') OR (tnd_preferred_location='my_city'))";
    $auctionCondition = " AND ((auc_preferred_location='domestic') OR (auc_preferred_location='any') OR (auc_preferred_location='my_city'))";
}

// =============================================
// Build queries - same structure as old code
// =============================================
$view_country = "";
$view_country_auction = "";

if ($rctyp == 'buy_lead') {
    // Same as old code - city join through business_profile
    $view_country = "SELECT DISTINCT city.ct_name 
        FROM buy_requirement, measurement_unit, city, business_profile
        WHERE br_estimate_qty_unit = mu_id 
        AND business_profile.bnsprof_uid = buy_requirement.br_u_id
        AND business_profile.bnsprof_city = city.ct_id 
        AND city.ct_name LIKE '$my_data%' 
        AND br_display_status = '1' 
        AND br_approval_status = '1'
        GROUP BY br_u_id";

} elseif ($rctyp == 'tender') {
    $tender_keywords_string  = generateTenderSearchString($keywords);
    $auction_keywords_string = generateAuctionSearchString($keywords);

    // Exact same structure as old code
    $view_country = "SELECT DISTINCT city.ct_name 
        FROM tender, city, product_category, user, business_profile 
        WHERE tnd_pc_id = pc_id 
        AND tnd_usr_id = usr_id 
        AND business_profile.bnsprof_city = city.ct_id 
        AND city.ct_name LIKE '$my_data%' 
        AND usr_id = bnsprof_uid 
        AND ($tender_keywords_string) 
        AND tnd_due_date >= '$today' 
        AND tnd_approval_status = '1' 
        $tenderCondition 
        GROUP BY tender.tnd_currency 
        ORDER BY tnd_id DESC";

    $view_country_auction = "SELECT DISTINCT city.ct_name 
        FROM auction, product_category, user, business_profile, city
        WHERE auc_pc_id = pc_id 
        AND auc_usr_id = usr_id 
        AND usr_id = bnsprof_uid 
        AND business_profile.bnsprof_city = city.ct_id
        AND city.ct_name LIKE '$my_data%'
        AND ($auction_keywords_string) 
        AND auc_due_date >= '$today' 
        AND auc_approval_status = '1' 
        $auctionCondition 
        GROUP BY auction.auc_currency 
        ORDER BY auc_id DESC";

} elseif ($rctyp == 'Suppliers') {
    $keywords_string = generateSupplierSearchString($keywords);

    if ($loc_id > 0) {
        $view_country = "SELECT DISTINCT city.ct_name 
            FROM products, measurement_unit, country, business_profile, city, user, plan_member_id 
            WHERE usr_id = pd_uid 
            AND bnsprof_uid = pd_uid 
            AND b_id = business_profile.bnsprof_id 
            AND business_profile.bnsprof_city = city.ct_id 
            AND city.ct_name LIKE '$my_data%' 
            AND mu_id = pd_unit 
            AND (bnsprof_compname LIKE $keywords_string) 
            AND pd_currency = cn_id 
            AND ((pd_preferred_buyer_location = 'domestic' AND user.country = '$loc_id') 
                OR (pd_preferred_buyer_location = 'any' AND user.country = '$loc_id') 
                OR (pd_preferred_buyer_location = 'my_city' AND user.country = '$loc_id')) 
            AND expiry_date > $current_time 
            AND pd_status = '1' 
            AND pd_image != '' 
            GROUP BY pd_currency";
    } else {
        $view_country = "SELECT DISTINCT city.ct_name 
            FROM products, measurement_unit, country, business_profile, city, user, plan_member_id 
            WHERE usr_id = pd_uid 
            AND bnsprof_uid = pd_uid 
            AND b_id = business_profile.bnsprof_id 
            AND business_profile.bnsprof_city = city.ct_id 
            AND city.ct_name LIKE '$my_data%' 
            AND mu_id = pd_unit 
            AND (bnsprof_compname LIKE $keywords_string) 
            AND pd_currency = cn_id 
            AND ((pd_preferred_buyer_location = 'domestic') 
                OR (pd_preferred_buyer_location = 'any') 
                OR (pd_preferred_buyer_location = 'my_city')) 
            AND expiry_date > $current_time 
            AND pd_status = '1' 
            AND pd_image != '' 
            GROUP BY pd_currency";
    }

} else {
    // Products - exact same as old code
    $newkw      = generateProdSearchString($keywords);
    $sql_pd_ck  = $sql_pd_ck ?? '';

    if (!empty($idd)) {
        $idd = (int)$idd;
        $view_country = "SELECT DISTINCT city.ct_name 
            FROM products, measurement_unit, country, city, business_profile, plan_member_id 
            WHERE mu_id = pd_unit 
            AND business_profile.bnsprof_uid = products.pd_uid 
            AND business_profile.bnsprof_city = city.ct_id 
            AND city.ct_name LIKE '$my_data%' 
            AND b_id = bnsprof_id 
            AND plan_member_id.expiry_date > $current_time 
            AND pd_subcat_id = $idd 
            AND pd_currency = cn_id 
            $sql_pd_ck 
            AND pd_status = '1' 
            AND pd_image != '' 
            GROUP BY pd_currency";
    } else {
        $view_country = "SELECT DISTINCT city.ct_name 
            FROM products, measurement_unit, country, city, business_profile, plan_member_id 
            WHERE mu_id = pd_unit 
            AND business_profile.bnsprof_uid = products.pd_uid 
            AND business_profile.bnsprof_city = city.ct_id 
            AND city.ct_name LIKE '$my_data%' 
            AND b_id = bnsprof_id 
            AND plan_member_id.expiry_date > $current_time 
            AND ($newkw) 
            AND pd_currency = cn_id 
            $sql_pd_ck 
            AND pd_status = '1' 
            AND pd_image != '' 
            GROUP BY pd_id";
    }
}

// =============================================
// Execute and output - same as old code
// =============================================
if (!empty($view_country)) {
    $run_sql = mysqli_query($link, $view_country);
    if (!$run_sql) {
        error_log("City search query error: " . mysqli_error($link));
    } else {
        while ($row11 = mysqli_fetch_assoc($run_sql)) {
            if (!empty($row11['ct_name'])) {
                echo $row11['ct_name'];
            }
        }
    }
}

if ($rctyp == 'tender' && !empty($view_country_auction)) {
    $run_sql_auction = mysqli_query($link, $view_country_auction);
    if ($run_sql_auction) {
        while ($row11 = mysqli_fetch_assoc($run_sql_auction)) {
            if (!empty($row11['ct_name'])) {
                echo $row11['ct_name'];
            }
        }
    }
}