<?php
/**
 * File: ajax/buyLeads.php

 * Description: تحميل وعرض طلبات الشراء حسب التصنيف مع التصفح (Pagination)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود رقم الصفحة ومعرف التصنيف
if (!isset($_POST['page']) || !isset($_POST['id'])) {
    http_response_code(400);
    die("Invalid request parameters");
}

$page = (int)$_POST['page'];
$pc_id = mysqli_real_escape_string($GLOBALS['con'], $_POST['id']);

// إعدادات التصفح
$cur_page = $page;
$page -= 1;
$per_page = 20;
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

global $con;

// =============================================
// 1. بناء شروط الموقع (Location Conditions)
// =============================================
$sql_br_ck = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_br_ck = " AND (
        (br_preferred_supplier_location = 'domestic' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id)) 
        OR 
        (br_preferred_supplier_location = 'any' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id))
        OR
        (br_preferred_supplier_location = 'my_city' AND br_u_id IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile 
            WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = $loc_id)
        ))
    )";
} else {
    $location_geo_country = $location_geo_country ?? '';
    $sql_br_ck = " AND (
        (br_preferred_supplier_location = 'any')
        OR
        (br_preferred_supplier_location = 'abroad' AND br_u_id NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country IN (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
}

// =============================================
// 2. استعلام جلب طلبات الشراء
// =============================================
$sql_bl = "SELECT br.*, u.*, pc.* 
           FROM buy_requirement br
           INNER JOIN user u ON br.br_u_id = u.usr_id
           INNER JOIN product_category pc ON br.br_pc_id = pc.pc_id
           WHERE br.br_approval_status = '1' 
           AND br.br_status = '1' 
           AND br.br_display_status = '1' 
           AND pc.pc_parent_id IN (
               SELECT DISTINCT pc_id FROM product_category WHERE pc_parent_id = ?
           )
           $sql_br_ck
           ORDER BY br.br_updated_date DESC 
           LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_bl);
mysqli_stmt_bind_param($stmt, 'siii', $pc_id, $start, $per_page, $location_geo_country);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// =============================================
// 3. حساب إجمالي السجلات
// =============================================
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM buy_requirement br
                  INNER JOIN user u ON br.br_u_id = u.usr_id
                  WHERE br.br_approval_status = '1' 
                  AND br.br_status = '1' 
                  AND br.br_display_status = '1'
                  $sql_br_ck";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row = mysqli_fetch_assoc($result_count);
$count = (int)($row['count'] ?? 0);

$no_of_paginations = (int)ceil($count / $per_page);

// =============================================
// 4. جلب اسم التصنيف
// =============================================
$pc_query = "SELECT pc_name FROM product_category WHERE pc_id = ? LIMIT 1";
$stmt_pc = mysqli_prepare($con, $pc_query);
mysqli_stmt_bind_param($stmt_pc, 's', $pc_id);
mysqli_stmt_execute($stmt_pc);
$pc_result = mysqli_stmt_get_result($stmt_pc);
$row_pc = mysqli_fetch_object($pc_result);
mysqli_stmt_close($stmt_pc);

$category_name = $row_pc ? htmlspecialchars($row_pc->pc_name ?? '', ENT_QUOTES, 'UTF-8') : '';
?>

<div class="xx fl on" id="aaa">
    <p class="cnb" style="font-size:15px; font-weight:700">
        <b class="cnr" style="font-size:15px;"><?php echo $category_name; ?></b>
    </p>
    
    <div class="sl1">
        <ul class="lst">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row_br = mysqli_fetch_object($result)): 
                    $br_id = (int)$row_br->br_id;
                    $br_pd_name = htmlspecialchars(ucwords($row_br->br_pd_name ?? ''), ENT_QUOTES, 'UTF-8');
                    $br_requirement = htmlspecialchars($row_br->br_requirement ?? '', ENT_QUOTES, 'UTF-8');
                    $country_id = (int)($row_br->country ?? 0);
                    $country_name = htmlspecialchars(get_country_name($country_id), ENT_QUOTES, 'UTF-8');
                    $country_flag = htmlspecialchars(get_country_flag($country_id), ENT_QUOTES, 'UTF-8');
                    $bnsprof_city = (int)($row_br->bnsprof_city ?? 0);
                    $city_name = htmlspecialchars(get_city_name($bnsprof_city), ENT_QUOTES, 'UTF-8');
                    $updated_date = !empty($row_br->br_updated_date) 
                        ? date("d M, Y", strtotime($row_br->br_updated_date)) 
                        : 'N/A';
                    $preferred_location = $row_br->br_preferred_supplier_location ?? '';
                ?>
                <li>
                    <a style="font-size:15px; font-weight:700" 
                       href="buyleads-details.php?id=<?php echo rand(1000, 9999) . md5((string)$br_id); ?>">
                        <?php echo $br_pd_name; ?>
                    </a> 
                    <span class="vlogo g10 bo1 d2" onMouseOver="show('tp0');" onMouseOut="hide('tp0');">
                        مشترى حقيقى
                    </span>
                    <span id="tp0" class="off"></span>
                    
                    <p class="p1 lnh lsdc"><?php echo $br_requirement; ?></p>
                    
                    <?php if (!empty($preferred_location)): ?>
                        <?php if ($preferred_location == 'any'): ?>
                            <p class="p1">
                                <span class="c7">: الـبـلــد</span> 
                                <?php echo $country_name; ?>
                                &nbsp;&nbsp;
                                <?php if (!empty($country_flag)): ?>
                                <img src="images/country_flag/<?php echo $country_flag; ?>" 
                                     alt="" class="w4" align="top" height="15" width="23">
                                <?php endif; ?>
                            </p>
                            <p class="p1">
                                <span class="ltu flr"> Updated: <?php echo $updated_date; ?></span>
                                <span class="c7"></span> (محـلى وتصـديـر) : مكــان التجـارة 
                            </p>
                        <?php elseif ($preferred_location == 'abroad'): ?>
                            <p class="p1">
                                <span class="c7">: الـبـلــد</span> 
                                <?php echo $country_name; ?>
                                &nbsp;&nbsp;
                                <?php if (!empty($country_flag)): ?>
                                <img src="images/country_flag/<?php echo $country_flag; ?>" 
                                     alt="" class="w4" align="top" height="15" width="23">
                                <?php endif; ?>
                            </p>
                            <p class="p1">
                                <span class="ltu flr"> Updated: <?php echo $updated_date; ?></span>
                                <span class="c7"></span> (تصـدير فـقـط) : مكـان التجـارة 
                            </p>
                        <?php elseif ($preferred_location == 'domestic'): ?>
                            <p class="p1">
                                <span class="c7">Location:</span> 
                                <?php echo $country_name; ?>
                                &nbsp;&nbsp;
                                <?php if (!empty($country_flag)): ?>
                                <img src="images/country_flag/<?php echo $country_flag; ?>" 
                                     alt="" class="w4" align="top" height="15" width="23">
                                <?php endif; ?>
                            </p>
                            <p class="p1">
                                <span class="ltu flr"> Updated: <?php echo $updated_date; ?></span>
                                <span class="c7"></span> (مـحـلـى فـقـط) : مكـان التجـارة 
                            </p>
                        <?php elseif ($preferred_location == 'my_city' && $bnsprof_city > 0): ?>
                            <p class="p1">
                                <span class="c7">Location:</span> 
                                <?php echo $city_name; ?>
                            </p>
                            <p class="p1">
                                <span class="ltu flr"> Updated: <?php echo $updated_date; ?></span>
                                <span class="c7"></span> (كيلومتر 250) : مكـان التجـارة 
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="p1">
                            <span class="ltu flr"> Updated: <?php echo $updated_date; ?></span>
                            <span class="c7">Location:</span> 
                            <?php echo $country_name; ?>
                            &nbsp;&nbsp;
                            <?php if (!empty($country_flag)): ?>
                            <img src="images/country_flag/<?php echo $country_flag; ?>" 
                                 alt="" class="w4" align="top" height="15" width="23">
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                    
                    <p class="c3"></p>
                </li>
                <?php endwhile; ?>
            <?php endif; ?>
        </ul>
        
        <?php if ($count > 0): ?>
            <p class="cl"><br></p>
            <p align="center" style="margin-bottom:10px;">
                <?php
                // زر الصفحة الأولى
                if ($first_btn && $cur_page > 1) {
                    echo '<a href="javascript:showLead(\'1\',\'' . $pc_id . '\')"><img id="firstmail" src="images/firsten.gif"></a>';
                } elseif ($first_btn) {
                    echo '<img id="firstmail" src="images/first.gif">';
                }
                echo '&nbsp;';
                
                // زر الصفحة السابقة
                if ($previous_btn && $cur_page > 1) {
                    $pre = $cur_page - 1;
                    echo '<a href="javascript:showLead(\'' . $pre . '\',\'' . $pc_id . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                } elseif ($previous_btn) {
                    echo '<img id="prevmail" src="images/prevmail.gif">';
                }
                echo '&nbsp;';
                
                // زر الصفحة التالية
                if ($next_btn && $cur_page < $no_of_paginations) {
                    $nex = $cur_page + 1;
                    echo '<a href="javascript:showLead(\'' . $nex . '\',\'' . $pc_id . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                } elseif ($next_btn) {
                    echo '<img id="nextmail" src="images/nextmail.gif">';
                }
                echo '&nbsp;';
                
                // زر الصفحة الأخيرة
                if ($last_btn && $cur_page < $no_of_paginations) {
                    echo '<a href="javascript:showLead(\'' . $no_of_paginations . '\',\'' . $pc_id . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
                } elseif ($last_btn) {
                    echo '<img id="lastmail" src="images/last.gif">';
                }
                ?>
            </p>
        <?php else: ?>
            <p class="cl" style="text-align: center;">
                <img src="/images/search_icon_man.png" width="100px" height="100px"><br>
            </p>
            <p align="center" style="margin-bottom:10px; font-size:20px; font-weight:600;">
                No Leads under this category.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>