<?php
/**
 * File: ajax/minporder.php

 * Description: تحميل وعرض المنتجات حسب التصنيف مع فلترة حسب أقل كمية (Min Order)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرّف التصنيف
if (!isset($_POST['id'])) {
    http_response_code(400);
    die("Invalid category ID");
}

$pc_id = mysqli_real_escape_string($GLOBALS['con'], $_POST['id']);
$minorder = isset($_POST['minorder']) ? (float)$_POST['minorder'] : 0;
$mst_type = $_POST['mst_type'] ?? [];

// إعدادات التصفح (الصفحة الأولى دائماً)
$page = 1;
$cur_page = $page;
$page -= 1;
$per_page = 40;
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

global $con;

// بناء شرط الموقع
$sql_pd_ck = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id)) 
        OR 
        (pd_preferred_buyer_location = 'any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id))
        OR
        (pd_preferred_buyer_location = 'my_city' AND pd_uid IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile 
            WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = $loc_id)
        ))
    )";
} else {
    $location_geo_country = $location_geo_country ?? '';
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
}

// جلب معلومات التصنيف
$sql_pcat = "SELECT m.pc_id, m.pc_name, c.pc_id, c.pc_sort_name 
             FROM product_category m
             INNER JOIN product_category c ON m.pc_id = c.pc_parent_id
             WHERE MD5(c.pc_id) = ? 
             LIMIT 1";

$stmt_pcat = mysqli_prepare($con, $sql_pcat);
mysqli_stmt_bind_param($stmt_pcat, 's', $pc_id);
mysqli_stmt_execute($stmt_pcat);
$result_pcat = mysqli_stmt_get_result($stmt_pcat);
$row_pcat = mysqli_fetch_array($result_pcat);
mysqli_stmt_close($stmt_pcat);

// جلب جميع التصنيفات الفرعية تحت هذا التصنيف
$sql_check1 = "SELECT pc_id FROM product_category WHERE MD5(pc_parent_id) = ?";
$stmt_check = mysqli_prepare($con, $sql_check1);
mysqli_stmt_bind_param($stmt_check, 's', $pc_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

$pc_id_arr = [];
while ($data = mysqli_fetch_assoc($result_check)) {
    $pc_id_arr[] = (int)$data['pc_id'];
}
mysqli_stmt_close($stmt_check);

$ids = !empty($pc_id_arr) ? implode("','", $pc_id_arr) : '0';

// بناء شرط نوع العضوية
$member_condition = "";
if (!empty($mst_type) && is_array($mst_type)) {
    $member_ids = implode(',', array_map('intval', $mst_type));
    $member_condition = " AND sp.mp_id IN ($member_ids)";
}

// استعلام جلب المنتجات
$sql_prd = "SELECT p.*, mu.*, c.*, bp.*, pm.*, sp.* 
            FROM products p
            INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
            INNER JOIN country c ON c.cn_id = p.pd_currency
            INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
            INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
            INNER JOIN smembership_plan sp ON sp.mp_id = pm.p_id
            WHERE p.pd_min_order_qty <= ? 
            AND p.pd_currency = c.cn_id
            $sql_pd_ck
            AND bp.bnsprof_uid = p.pd_uid
            AND p.pd_status = '1'
            AND p.pd_image != ''
            AND pm.expiry_date > " . time() . "
            AND p.pd_subcat_id IN ('$ids')
            $member_condition
            ORDER BY FIELD(sp.mp_id,'5','4','3','15')
            LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_prd);
mysqli_stmt_bind_param($stmt, 'dii', $minorder, $start, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM products p
                  INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
                  INNER JOIN country c ON c.cn_id = p.pd_currency
                  INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                  INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                  INNER JOIN smembership_plan sp ON sp.mp_id = pm.p_id
                  WHERE p.pd_min_order_qty <= ? 
                  AND p.pd_currency = c.cn_id
                  $sql_pd_ck
                  AND bp.bnsprof_uid = p.pd_uid
                  AND p.pd_status = '1'
                  AND p.pd_image != ''
                  AND pm.expiry_date > " . time() . "
                  AND p.pd_subcat_id IN ('$ids')
                  $member_condition";

$stmt_count = mysqli_prepare($con, $query_pag_num);
mysqli_stmt_bind_param($stmt_count, 'd', $minorder);
mysqli_stmt_execute($stmt_count);
$result_count = mysqli_stmt_get_result($stmt_count);
$row = mysqli_fetch_assoc($result_count);
$count = (int)($row['count'] ?? 0);

$no_of_paginations = (int)ceil($count / $per_page);
$pagi_string = "Page " . ($cur_page) . " of " . $no_of_paginations;

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

<div class="col-md-12 col-sm-12 col-lg-12 col-xs-12 prc-right-side">
    <div class="col-md-12 col-sm-12 col-xs-12 col-lg-12" id="final_result">
        <div class="middle-part">
            <button type="button" class="btn btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize" 
                    style="border-top:2px solid #ff7519 !important; border:0px;">Products</button>
            <button type="button" class="btn btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize" disabled>
                <a href="https://egyptmart.online/catcompany.php" style="color:#000;">Suppliers</a>
            </button>
        </div>
        
        <div class="als-container" id="product_slider" style="border-radius:5px;">
            <div class="countries">
                <div class="countries_inner">
                    <?php
                    $country_sql = "SELECT DISTINCT u.country, c.cn_name 
                                   FROM country c
                                   INNER JOIN user u ON u.country = c.cn_id
                                   WHERE c.cn_status = 1
                                   ORDER BY c.cn_name ASC
                                   LIMIT 50";
                    $country_result = mysqli_query($con, $country_sql);
                    while ($data1 = mysqli_fetch_assoc($country_result)):
                        $country_id = (int)$data1['country'];
                        $country_name = htmlspecialchars($data1['cn_name'] ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                    <span class="outer_c">
                        <input type="checkbox" value="<?php echo $country_id; ?>">
                        <span><?php echo $country_name; ?></span>
                        <i class="fa fa-angle-down cnt_state" style="font-size: 15px; margin-left: 5px; cursor: pointer;"></i>
                    </span>
                    <?php endwhile; ?>
                </div>
                <div class="state_section"></div>
            </div>

            <div class="als-viewport" align="center">
                <ul class="als-wrapper">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row_prd = mysqli_fetch_object($result)): 
                            $product_id = (int)$row_prd->pd_id;
                            $product_title = htmlspecialchars($row_prd->pd_title ?? '', ENT_QUOTES, 'UTF-8');
                            $product_title_short = htmlspecialchars(substr($product_title, 0, 12), ENT_QUOTES, 'UTF-8');
                            $product_image = htmlspecialchars($row_prd->pd_image ?? '', ENT_QUOTES, 'UTF-8');
                            $bnsprof_id = (int)($row_prd->bnsprof_id ?? 0);
                            $pd_subcat_id = (int)($row_prd->pd_subcat_id ?? 0);
                            $pd_uid = (int)($row_prd->pd_uid ?? 0);
                            $bnsprof_city = (int)($row_prd->bnsprof_city ?? 0);
                            $bnsprof_state = (int)($row_prd->bnsprof_state ?? 0);
                            $pd_min_order_qty = (int)($row_prd->pd_min_order_qty ?? 0);
                            $mu_name = htmlspecialchars($row_prd->mu_name ?? '', ENT_QUOTES, 'UTF-8');
                            $cn_currency = htmlspecialchars($row_prd->cn_currency ?? '', ENT_QUOTES, 'UTF-8');
                            $pd_fob_price = htmlspecialchars($row_prd->pd_fob_price ?? '', ENT_QUOTES, 'UTF-8');
                            $mst_name = htmlspecialchars($row_prd->mst_name ?? '', ENT_QUOTES, 'UTF-8');
                            
                            // جلب معلومات الموقع
                            $location_name = '';
                            if (isset($_COOKIE['loc_id'])) {
                                $city_query = "SELECT ct_name FROM city WHERE ct_id = ? LIMIT 1";
                                $stmt_city = mysqli_prepare($con, $city_query);
                                mysqli_stmt_bind_param($stmt_city, 'i', $bnsprof_city);
                                mysqli_stmt_execute($stmt_city);
                                $city_result = mysqli_stmt_get_result($stmt_city);
                                $city_row = mysqli_fetch_object($city_result);
                                $cn_city = $city_row ? htmlspecialchars($city_row->ct_name ?? '', ENT_QUOTES, 'UTF-8') : '';
                                mysqli_stmt_close($stmt_city);
                                
                                $state_query = "SELECT state_name FROM states WHERE state_id = ? LIMIT 1";
                                $stmt_state = mysqli_prepare($con, $state_query);
                                mysqli_stmt_bind_param($stmt_state, 'i', $bnsprof_state);
                                mysqli_stmt_execute($stmt_state);
                                $state_result = mysqli_stmt_get_result($stmt_state);
                                $state_row = mysqli_fetch_object($state_result);
                                $cn_state = $state_row ? htmlspecialchars($state_row->state_name ?? '', ENT_QUOTES, 'UTF-8') : '';
                                mysqli_stmt_close($stmt_state);
                                
                                $location_name = $cn_city . " - " . $cn_state;
                            } else {
                                $city_query = "SELECT ct_cn_id FROM city WHERE ct_id = ? LIMIT 1";
                                $stmt_city = mysqli_prepare($con, $city_query);
                                mysqli_stmt_bind_param($stmt_city, 'i', $bnsprof_city);
                                mysqli_stmt_execute($stmt_city);
                                $city_result = mysqli_stmt_get_result($stmt_city);
                                $city_row = mysqli_fetch_object($city_result);
                                $ct_cn_id = $city_row ? (int)$city_row->ct_cn_id : 0;
                                mysqli_stmt_close($stmt_city);
                                
                                $country_query = "SELECT cn_name FROM country WHERE cn_id = ? LIMIT 1";
                                $stmt_country = mysqli_prepare($con, $country_query);
                                mysqli_stmt_bind_param($stmt_country, 'i', $ct_cn_id);
                                mysqli_stmt_execute($stmt_country);
                                $country_result = mysqli_stmt_get_result($stmt_country);
                                $country_row = mysqli_fetch_object($country_result);
                                $location_name = $country_row ? htmlspecialchars($country_row->cn_name ?? '', ENT_QUOTES, 'UTF-8') : '';
                                mysqli_stmt_close($stmt_country);
                            }
                            
                            // جلب معلومات الاتصال
                            $contact_query = "SELECT mobile1, country_ph_code FROM user WHERE usr_id = ? LIMIT 1";
                            $stmt_contact = mysqli_prepare($con, $contact_query);
                            mysqli_stmt_bind_param($stmt_contact, 'i', $pd_uid);
                            mysqli_stmt_execute($stmt_contact);
                            $contact_result = mysqli_stmt_get_result($stmt_contact);
                            $row_contact = mysqli_fetch_object($contact_result);
                            mysqli_stmt_close($stmt_contact);
                            
                            $mobile = $row_contact ? htmlspecialchars($row_contact->mobile1 ?? '', ENT_QUOTES, 'UTF-8') : '';
                            $phone_code = $row_contact ? htmlspecialchars($row_contact->country_ph_code ?? '', ENT_QUOTES, 'UTF-8') : '';
                        ?>
                        <li class="als-item" style="border:1px solid #ccc; margin-top:1%; margin-left:1%; padding:4px !important; margin-bottom:1%; border-radius:4px; float:left; height:auto; background-color:rgba(251, 251, 251, 0.96);">
                            <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>&sc=<?php echo rand(10000, 99999) . $pd_subcat_id; ?>#<?php echo $product_id; ?>" 
                               style="text-decoration:none; color:#000" target="_blank">
                                <img src="upload/myproduct/thumb/<?php echo $product_image; ?>" 
                                     alt="<?php echo htmlspecialchars(ucwords(substr($product_title, 0, 28)), ENT_QUOTES, 'UTF-8'); ?>" 
                                     title="<?php echo $product_title; ?>" />
                                <div style="height:0%; margin-top:3%; padding-top:5%;">
                                    <span class="utext" style="color:#005ce6; font-size:16px !important;">
                                        <b><?php echo $product_title_short; ?></b>
                                    </span><br />
                                    <span style="color:red; font-size:13px !important; font-weight:bold;"><?php echo $location_name; ?></span>
                                </div>
                                <hr />
                                <hr />
                                <div style="height:10%; margin-top:5%; font-size:13px;">
                                    MOQ : <span style="color:red; font-weight:600; font-size:15px !important;"><?php echo $pd_min_order_qty; ?>&nbsp;</span><?php echo $mu_name; ?>
                                </div>
                                <div style="height:10%; margin-top:1%; font-size:13px;">
                                    <?php echo $cn_currency; ?>&nbsp;
                                    <span style="color:red; font-weight:600; font-size:15px !important;"><?php echo $pd_fob_price; ?></span>/<?php echo $mu_name; ?>
                                </div>
                                <div style="font-weight:bold; font-size:13px; padding:4px 0;"><?php echo $mst_name; ?></div>
                            </a>
                            
                            <p class="cnt-phone">
                                <span class="cnt-phone-inner">
                                    <img src="images/mobile.png" width="25px">&nbsp;&nbsp;
                                    <a href="tel:+<?php echo $phone_code; ?>-<?php echo $mobile; ?>"><?php echo $phone_code; ?>-<?php echo $mobile; ?></a>
                                </span>
                            </p>

                            <p class="cnt_supplier">
                                <span class="cnt_supplier_inner">
                                    <i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;&nbsp;
                                    <a href="ajax-file/quotationRequest.php?id=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>&pid=<?php echo $product_id; ?>&c=<?php echo $pd_uid; ?>&vform=1" 
                                       id="btn_ajax<?php echo $product_id; ?>" 
                                       rel="product-send-inquiry" class="inquiry_but" style="color:#fff;">Contact Supplier</a>
                                </span>
                            </p>
                            
                            <div class="link pt10px">	
                                <script>
                                    $(document).ready(function() {
                                        var uid_ind = '<?php echo $_SESSION['uid_indm'] ?? ''; ?>';
                                        $("#btn_ajax<?php echo $product_id; ?>").click(function(event) {
                                            if (uid_ind == '') {
                                                window.location.href = "https://www.egyptmart.online/sign-in.php";
                                            } else {
                                                event.preventDefault();
                                                $("#btn_ajax<?php echo $product_id; ?>").colorbox({
                                                    width: "62%",
                                                    height: "89%"
                                                });
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="als-item" style="border:1px solid #484891; margin-top:1%; margin-left:1.5%; border-radius:4px; width:97%; height:20px; color:#F00">
                            No products listed for this category.
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <?php if ($count > 0): ?>
                <p class="cl"><br></p>
                <p align="center" style="margin-bottom:10px;">
                    <?php
                    // زر الصفحة الأولى
                    if ($first_btn && $cur_page > 1) {
                        echo '<a href="javascript:loadProductByCategory(\'1\',\'' . $pc_id . '\')"><img id="firstmail" src="images/firsten.gif"></a>';
                    } elseif ($first_btn) {
                        echo '<img id="firstmail" src="images/first.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة السابقة
                    if ($previous_btn && $cur_page > 1) {
                        $pre = $cur_page - 1;
                        echo '<a href="javascript:loadProductByCategory(\'' . $pre . '\',\'' . $pc_id . '\')"><img id="prevmail" src="images/prven.gif"></a>';
                    } elseif ($previous_btn) {
                        echo '<img id="prevmail" src="images/prevmail.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة التالية
                    if ($next_btn && $cur_page < $no_of_paginations) {
                        $nex = $cur_page + 1;
                        echo '<a href="javascript:loadProductByCategory(\'' . $nex . '\',\'' . $pc_id . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
                    } elseif ($next_btn) {
                        echo '<img id="nextmail" src="images/nextmail.gif">';
                    }
                    echo '&nbsp;';
                    
                    // زر الصفحة الأخيرة
                    if ($last_btn && $cur_page < $no_of_paginations) {
                        echo '<a href="javascript:loadProductByCategory(\'' . $no_of_paginations . '\',\'' . $pc_id . '\')"><img id="lastmail" src="images/lastenv.gif"></a>';
                    } elseif ($last_btn) {
                        echo '<img id="lastmail" src="images/last.gif">';
                    }
                    ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>