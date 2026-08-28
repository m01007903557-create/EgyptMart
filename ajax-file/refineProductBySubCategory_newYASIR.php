<?php
/**
 * File: ajax/refineProductBySubCategory_new.php

 * Description: تحميل وعرض المنتجات حسب التصنيف الفرعي مع التصفية والتصفح (Pagination)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود رقم الصفحة ومعرف التصنيف
if (!isset($_POST['page']) || !is_numeric($_POST['page']) || !isset($_POST['id'])) {
    http_response_code(400);
    die("Invalid request parameters");
}

$page = (int)$_POST['page'];
$pc_id = mysqli_real_escape_string($GLOBALS['con'], $_POST['id']);
$is_sub = isset($_POST['is_sub']) && $_POST['is_sub'] === "true";

// معاملات التصفية
$mst_type = isset($_POST['mst_type']) ? mysqli_real_escape_string($GLOBALS['con'], $_POST['mst_type']) : '';
$min_order = isset($_POST['min_order']) ? (int)$_POST['min_order'] : 0;
$country_filter = isset($_POST['country']) ? mysqli_real_escape_string($GLOBALS['con'], $_POST['country']) : '';
$city_filter = isset($_POST['city']) ? mysqli_real_escape_string($GLOBALS['con'], $_POST['city']) : '';
$state_filter = isset($_POST['state']) ? mysqli_real_escape_string($GLOBALS['con'], $_POST['state']) : '';

// إعدادات التصفح
$cur_page = $page;
$page -= 1;
$per_page = 40;
$previous_btn = true;
$next_btn = true;
$first_btn = true;
$last_btn = true;
$start = $page * $per_page;

global $con;

if (!function_exists('em_product_image_src')) {
    function em_product_image_src($image, $thumb = false) {
        $clean = ltrim(trim((string)$image), '/');
        if ($clean === '') {
            return 'upload/myproduct/noimage.jpg';
        }
        $base = basename($clean);
        $candidates = $thumb
            ? array('upload/myproduct/thumb/' . $base, 'upload/myproduct/' . $base, 'upload/image_gallery/' . $base)
            : array('upload/myproduct/' . $base, 'upload/myproduct/thumb/' . $base, 'upload/image_gallery/' . $base);
        if (strpos($clean, '/') !== false) {
            array_unshift($candidates, $clean);
        }
        foreach ($candidates as $path) {
            if (is_file(__DIR__ . '/../' . $path)) {
                return $path;
            }
        }
        return $thumb ? 'upload/myproduct/thumb/' . $base : 'upload/myproduct/' . $base;
    }
}

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

// بناء شرط التصنيف الفرعي
$sql_sub_cat = "";
if ($is_sub) {
    $sql_sub_cat = " AND MD5(pd_subcat_id) = ?";
} else {
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
    
    if (!empty($pc_id_arr)) {
        $ids = implode("','", $pc_id_arr);
        $sql_sub_cat = " AND pd_subcat_id IN ('$ids')";
    }
}

// بناء شروط التصفية الإضافية
$member_condition = !empty($mst_type) ? " AND p_id IN ($mst_type)" : "";
$minorder_condition = ($min_order > 0) ? " AND pd_min_order_qty = $min_order" : "";
$country_condition = !empty($country_filter) ? " AND cn_id IN ($country_filter)" : "";

// شرط المدينة
$sql_pd_city = "";
if (!empty($city_filter)) {
    $city_escaped = mysqli_real_escape_string($con, $city_filter);
    $sql_pd_city = " AND bnsprof_city IN (SELECT ct_id FROM city WHERE ct_name LIKE '%$city_escaped%')";
}

// شرط الولاية
$sql_pd_state = "";
if (!empty($state_filter)) {
    $state_escaped = mysqli_real_escape_string($con, $state_filter);
    $sql_pd_state = " AND bnsprof_state IN ($state_escaped)";
    
    if (!empty($country_condition)) {
        $sql_pd_state = str_replace(' AND bnsprof_state', ' OR bnsprof_state', $sql_pd_state);
        $country_condition = str_replace(' AND cn_id', ' AND (cn_id', $country_condition) . $sql_pd_state . ')';
        $sql_pd_state = '';
    }
}

// استعلام جلب المنتجات
$sql_prd = "SELECT p.*, mu.*, c.*, bp.*, pm.*, sp.*, ct.*, c2.cn_currency 
            FROM products p
            INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
            INNER JOIN country c ON c.cn_id = p.pd_currency
            INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
            INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
            INNER JOIN smembership_plan sp ON sp.mp_id = pm.p_id
            INNER JOIN city ct ON ct.ct_id = bp.bnsprof_city
            INNER JOIN country c2 ON c2.cn_id = ct.ct_cn_id
            WHERE p.pd_status = '1' 
            AND p.pd_image != '' 
            AND pm.expiry_date > " . time() . "
            $sql_pd_ck
            $sql_sub_cat
            $member_condition
            $minorder_condition
            $country_condition
            $sql_pd_city
            $sql_pd_state
            ORDER BY FIELD(pm.p_id,'5','4','3','15')
            LIMIT ?, ?";

// إعداد وتنفيذ الاستعلام
if (isset($_COOKIE['loc_id']) && empty($_COOKIE['loc_id']) && !$is_sub) {
    // حالة خاصة مع معاملات متعددة
    // سيتم التعامل معها حسب الحاجة
}

$stmt = mysqli_prepare($con, $sql_prd);

if ($is_sub) {
    mysqli_stmt_bind_param($stmt, 'sii', $pc_id, $start, $per_page);
} else {
    mysqli_stmt_bind_param($stmt, 'ii', $start, $per_page);
}

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
                  INNER JOIN city ct ON ct.ct_id = bp.bnsprof_city
                  WHERE p.pd_status = '1' 
                  AND p.pd_image != '' 
                  AND pm.expiry_date > " . time() . "
                  $sql_pd_ck
                  $sql_sub_cat
                  $member_condition
                  $minorder_condition
                  $country_condition
                  $sql_pd_city
                  $sql_pd_state";

$stmt_count = mysqli_prepare($con, $query_pag_num);

if ($is_sub) {
    mysqli_stmt_bind_param($stmt_count, 's', $pc_id);
}

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

<style>
    .bt {
        background-position: 0 -240px;
        margin: 18px 0 30px 0;
        clear: left;
        height: 0px !Important;
        background-color: transparent;
        color: transparent;
    }
    .pagination ul {
        display: inline-block;
        padding: 0;
        margin: 0;
    }
    .pagination li {
        display: inline;
    }
    .pagination li a, li span {
        color: black;
        text-decoration: none;
    }
    .pagination li.active a {
        background-color: blue;
        color: white;
    }
    .pagination li a:hover:not(.active) {
        background-color: #ddd;
    }
</style>

<div class="als-container" id="product_slider" style="border-radius:5px;">
    <div class="als-viewport" align="center">
        <ul class="als-wrapper" style="width: 101.1%">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row_prd = mysqli_fetch_object($result)): 
                    $product_id = (int)$row_prd->pd_id;
                    $product_title = htmlspecialchars($row_prd->pd_title ?? '', ENT_QUOTES, 'UTF-8');
                    $product_title_short = htmlspecialchars(substr($product_title, 0, 30), ENT_QUOTES, 'UTF-8');
                    $product_image = htmlspecialchars($row_prd->pd_image ?? '', ENT_QUOTES, 'UTF-8');
                    $product_image_src = htmlspecialchars(em_product_image_src($row_prd->pd_image ?? ''), ENT_QUOTES, 'UTF-8');
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
                        
                        $location_name = $cn_city . "&nbsp;-&nbsp;" . $cn_state;
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
                <li class="als-item" style="border:1px solid #ccc; margin-top:1%; margin-left:0%; margin-right:1%; padding:4px !important; margin-bottom:1%; border-radius:4px; float:left; height:auto; background-color:rgba(251, 251, 251, 0.96);">
                    <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>&sc=<?php echo rand(10000, 99999) . $pd_subcat_id; ?>#<?php echo $product_id; ?>" style="text-decoration:none; color:#000" target="_blank">
                        <div style="height:150px;" id="img-div">
                            <span class="img-helper" style="display: inline-block; height: 100%; vertical-align: middle;"></span>
                            <img src="<?php echo $product_image_src; ?>" 
                                 alt="<?php echo $product_title_short; ?>"
                                 title="<?php echo $product_title; ?>" />
                        </div>
                        <div style="height:0%; margin-top:3%; padding-top:5%;">
                            <div class="utext" style="color:#005ce6; font-size:16px !important;">
                                <b><?php echo $product_title_short; ?></b>
                            </div>
                            <br/>
                        </div>
                        <div style="text-align:center">
                            <span style="color:red; font-size:13px !important; font-weight:bold;"><?php echo $location_name; ?></span>
                            <hr/>
                            <hr/>
                            <div style="height:10%; margin-top:5%; font-size:12px;">
                                MOQ : <span style="color:red; font-weight:600; font-size:15px !important;"><?php echo $pd_min_order_qty; ?>&nbsp;</span><?php echo $mu_name; ?>
                            </div>
                            <div style="height:10%; margin-top:1%; font-size:12px;">
                                <?php echo $cn_currency; ?>&nbsp;
                                <span style="color:red; font-weight:600; font-size:15px !important;"><?php echo $pd_fob_price; ?></span>/<?php echo $mu_name; ?>
                            </div>
                            <div style="font-weight:bold; font-size:12px; padding:4px 0;"><?php echo $mst_name; ?></div>
                        </div>
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
                                        window.location.href = "https://www.egyptmart.shop/sign-in.php";
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
            <script>
                function goToPage() {
                    refineProductBySubCategory($('#goToPageNum').val(), '<?php echo $pc_id; ?>');
                }
            </script>
            <nav>
                <div class="text-center">
                    <ul class="pagination">
                        <?php
                        $numrows = $count;
                        $rowsperpage = $per_page;
                        $totalpages = ceil($numrows / $rowsperpage);
                        
                        if ($totalpages > 1) {
                            $currentpage = $cur_page;
                            if ($currentpage < 1) $currentpage = 1;
                            if ($currentpage > $totalpages) $currentpage = $totalpages;
                            
                            $range = 3;
                            $link = "javascript:refineProductBySubCategory('{P}','$pc_id')";
                            
                            // الصفحة السابقة
                            if ($currentpage > 1) {
                                $prevpage = $currentpage - 1;
                                echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', $prevpage, $link) . '"><</a></li>';
                                
                                if ($currentpage - $range >= 2) {
                                    echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', 1, $link) . '">1</a></li>';
                                    if ($currentpage - $range > 2) {
                                        echo '<li class="disabled"><span>...</span></li>';
                                    }
                                }
                            }
                            
                            // أرقام الصفحات
                            for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
                                if (($x > 0) && ($x <= $totalpages)) {
                                    if ($x == $currentpage) {
                                        echo '<li class="page-item active"><a class="page-link" href="' . str_replace('{P}', $x, $link) . '">' . $x . '</a></li>';
                                    } else {
                                        echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', $x, $link) . '">' . $x . '</a></li>';
                                    }
                                }
                            }
                            
                            // الصفحة التالية
                            if ($x <= $totalpages) {
                                if ($x < $totalpages) {
                                    echo '<li class="disabled"><span>...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', $totalpages, $link) . '">' . $totalpages . '</a></li>';
                            }
                            
                            if ($currentpage != $totalpages) {
                                $nextpage = $currentpage + 1;
                                echo '<li class="page-item"><a class="page-link" href="' . str_replace('{P}', $nextpage, $link) . '">></a></li>';
                            }
                        }
                        ?>
                        <li class="page-item">
                            <span style="color:black; background:none; border:0;">
                                Go to page <input id="goToPageNum" type="text" value="" style="width:50px"/>
                                <button class="btn btn-xs btn-default border-radius-0" onclick="goToPage()" style="padding:0 5px 0 5px">Go</button>
                            </span>
                        </li>
                    </ul>
                </div>
            </nav>
        </p>
    <?php endif; ?>
</div>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>
