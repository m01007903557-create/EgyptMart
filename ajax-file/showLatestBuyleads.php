<?php
/**
 * File: ajax/showLatestBuyleads.php

 * Description: تحميل طلبات الشراء مع التصفح (Pagination)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود رقم الصفحة
if (!isset($_POST['page']) || !is_numeric($_POST['page'])) {
    http_response_code(400);
    die("Invalid page number");
}

$page = (int)$_POST['page'];

// إعدادات التصفح
$cur_page = $page;
$page -= 1;
$per_page = 10;
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

global $con;

// بناء شرط الموقع
$sql_br_ck = "";
$params = [];
$param_types = "";

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_br_ck = " AND (
        (br_preferred_supplier_location = 'domestic' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country = ?)) 
        OR 
        (br_preferred_supplier_location = 'any' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country = ?))
        OR
        (br_preferred_supplier_location = 'my_city' AND br_u_id IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile 
            WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = ?)
        ))
    )";
    $params = [$loc_id, $loc_id, $loc_id];
    $param_types = "iii";
} else {
    $location_geo_country = $location_geo_country ?? '';
    $sql_br_ck = " AND (
        (br_preferred_supplier_location = 'any')
        OR
        (br_preferred_supplier_location = 'abroad' AND br_u_id NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
    $params = [$location_geo_country];
    $param_types = "s";
}

// استعلام جلب البيانات
$sql_bl = "SELECT br.*, u.*, bp.* 
           FROM buy_requirement br
           INNER JOIN user u ON br.br_u_id = u.usr_id
           INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
           WHERE br.br_approval_status = '1' 
           $sql_br_ck
           AND br.br_status = '1' 
           ORDER BY br.br_updated_date DESC 
           LIMIT ?, ?";

// إعداد المعاملات للاستعلام
$stmt_params = array_merge($params, [$start, $per_page]);
$stmt_types = $param_types . "ii";

$stmt = mysqli_prepare($con, $sql_bl);
mysqli_stmt_bind_param($stmt, $stmt_types, ...$stmt_params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM buy_requirement br
                  INNER JOIN user u ON br.br_u_id = u.usr_id
                  WHERE br.br_approval_status = '1' 
                  $sql_br_ck
                  AND br.br_status = '1'";

$stmt_count = mysqli_prepare($con, $query_pag_num);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_count, $param_types, ...$params);
}

mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row = mysqli_fetch_assoc($result_count);
$count = (int)($row['count'] ?? 0);

$no_of_paginations = (int)ceil($count / $per_page);
$pagi_string = "Page " . ($page + 1) . " of " . $no_of_paginations;

// حساب نطاق أزرار التصفح
$start_loop = 1;
$end_loop = $no_of_paginations;

if ($cur_page >= 7) {
    $start_loop = $cur_page - 3;
    if ($no_of_paginations > $cur_page + 3) {
        $end_loop = $cur_page + 3;
    } elseif ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    }
} else {
    $start_loop = 1;
    $end_loop = $no_of_paginations > 7 ? 7 : $no_of_paginations;
}
?>

<div class="bx flr">
    <div class="bc1 f3 m12" style="padding-left:5px;">
        <p class="flr fz2 ttc">إجمالى العدد المنشور - <span class="c4 bo"><?php echo $count; ?></span></p>
        آخر طلبات الشراء والتسعير المنشورة
    </div>
    
    <p class="flr p1 p2 p3 bg bpr"><!--<a href="#" class="tdn" target="_BLANK">FAQ - Buy Leads</a>--></p>
    <p class="c3"></p>
    
    <div class="brl">
        <ul class="lst">
            <?php while ($row_br = mysqli_fetch_object($result)): 
                $country_name = get_country_name((int)($row_br->country ?? 0));
                $country_flag = get_country_flag((int)($row_br->country ?? 0));
                $city_name = get_city_name((int)($row_br->bnsprof_city ?? 0));
            ?>
            <li>
                <a href="buyleads-details.php?id=<?php echo rand(1000, 9999) . md5((string)$row_br->br_id); ?>" 
                   style="font-size:15px; font-weight:700;">
                    <?php echo htmlspecialchars(ucwords($row_br->br_pd_name ?? '')); ?>
                </a>
                
                <span class="vlogo g9 bo d1" onMouseOver="show1('tp0');" onMouseOut="hide('tp0');">Verified</span>
                <span id="tp0" class="off"></span>
                
                <p class="p1 lnh"><?php echo htmlspecialchars($row_br->br_requirement ?? ''); ?></p>
                
                <p class="p1">
                    <?php if (!empty($row_br->br_preferred_supplier_location)): ?>
                        <?php if ($row_br->br_preferred_supplier_location == 'any'): ?>
                            <span class="c7">البلــد :</span> <?php echo htmlspecialchars($country_name); ?>
                            &nbsp;&nbsp;
                            <?php if (!empty($country_flag)): ?>
                            <img src="images/country_flag/<?php echo htmlspecialchars($country_flag); ?>" 
                                 alt="" class="w4" align="top" height="15" width="23">
                            <?php endif; ?>
                            <span style="float:right"><span class="c7">أماكن البيع :</span> (تصدير ومحلى)</span>
                            
                        <?php elseif ($row_br->br_preferred_supplier_location == 'abroad'): ?>
                            <span class="c7">البلــد :</span> <?php echo htmlspecialchars($country_name); ?>
                            &nbsp;&nbsp;
                            <?php if (!empty($country_flag)): ?>
                            <img src="images/country_flag/<?php echo htmlspecialchars($country_flag); ?>" 
                                 alt="" class="w4" align="top" height="15" width="23">
                            <?php endif; ?>
                            <span style="float:right"><span class="c7">أماكن البيع :</span> (تصدير فقط)</span>
                            
                        <?php elseif ($row_br->br_preferred_supplier_location == 'domestic'): ?>
                            <span class="c7">البلــد :</span> <?php echo htmlspecialchars($country_name); ?>
                            &nbsp;&nbsp;
                            <?php if (!empty($country_flag)): ?>
                            <img src="images/country_flag/<?php echo htmlspecialchars($country_flag); ?>" 
                                 alt="" class="w4" align="top" height="15" width="23">
                            <?php endif; ?>
                            <span style="float:right"><span class="c7">أماكن البيع :</span> (محلى فقط)</span>
                            
                        <?php elseif ($row_br->br_preferred_supplier_location == 'my_city' && !empty($row_br->bnsprof_city) && $row_br->bnsprof_city != '0'): ?>
                            <span class="c7">البلــد :</span> <?php echo htmlspecialchars($city_name); ?>
                            <span style="float:right"><span class="c7">أماكن البيع :</span> (مسافة 250 كيلومتر)</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($country_name); ?>
                        &nbsp;&nbsp;
                        <?php if (!empty($country_flag)): ?>
                        <img src="images/country_flag/<?php echo htmlspecialchars($country_flag); ?>" 
                             alt="" class="w4" align="top" height="15" width="23"/>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
                
                <p class="c3"></p>
            </li>
            <?php endwhile; ?>
        </ul>
        
        <?php if ($count > $per_page): ?>
        <span class="pagenavigation" style="text-align:center">
            <div class="f1_m2 rf_m2 p9_m2"><!-- My PageNavigation start -->&nbsp;&nbsp;
                
                <?php
                // زر الصفحة الأولى
                if ($first_btn && $cur_page > 1) {
                    echo '<a href="javascript:showBuyleads(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
                } elseif ($first_btn) {
                    echo '<img id="firstmail" src="images/first.gif">';
                }
                echo '&nbsp;';
                
                // زر الصفحة السابقة
                if ($previous_btn && $cur_page > 1) {
                    $pre = $cur_page - 1;
                    echo '<a href="javascript:showBuyleads(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                } elseif ($previous_btn) {
                    echo '<img id="prevmail" src="images/prevmail.gif">';
                }
                echo '&nbsp;';
                
                // زر الصفحة التالية
                if ($next_btn && $cur_page < $no_of_paginations) {
                    $nex = $cur_page + 1;
                    echo '<a href="javascript:showBuyleads(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                } elseif ($next_btn) {
                    echo '<img id="nextmail" src="images/nextmail.gif">';
                }
                echo '&nbsp;';
                
                // زر الصفحة الأخيرة
                if ($last_btn && $cur_page < $no_of_paginations) {
                    echo '<a href="javascript:showBuyleads(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lasten.gif"></a>';
                } elseif ($last_btn) {
                    echo '<img id="lastmail" src="images/last.gif">';
                }
                ?>
                &nbsp;
                <!-- My PageNavigation end -->
            </div>
        </span>
        <?php endif; ?>
    </div>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>