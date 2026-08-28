<?php
// company/loadProductByCategory.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
session_start();
include "../common.php";

// التحقق من وجود بيانات POST
if (!isset($_POST['page']) || !isset($_POST['id'])) {
    exit;
}

$page = (int)$_POST['page'];
$pc_id = mysqli_real_escape_string($con, $_POST['id']);

$cur_page = $page;
$page_index = $page - 1;
$per_page = 40;
$start = $page_index * $per_page;

// شروط البحث حسب الموقع
if (isset($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id='{$loc_id}')))
    )";
} else {
    $location_geo_country = isset($location_geo_country) ? $location_geo_country : '';
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='any')
        OR
        (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country=(SELECT cn_id FROM country WHERE cn_code='{$location_geo_country}')))
    )";
}

// جلب معلومات التصنيف
$sql_pcat = "SELECT m.pc_id, m.pc_name, c.pc_id as sub_id, c.pc_sort_name 
             FROM product_category m, product_category c 
             WHERE m.pc_id = c.pc_parent_id AND md5(c.pc_id) = '{$pc_id}'";
$res_pcat = mysqli_query($con, $sql_pcat);
$row_pcat = mysqli_fetch_array($res_pcat, MYSQLI_ASSOC);

// جلب معرفات التصنيفات الفرعية
$pc_id_arr = [];
$sql_check1 = "SELECT pc_id FROM product_category WHERE md5(pc_parent_id) = '{$pc_id}'";
$res_check1 = mysqli_query($con, $sql_check1);

while ($data = mysqli_fetch_array($res_check1, MYSQLI_ASSOC)) {
    $pc_id_arr[] = (int)$data['pc_id'];
}

$ids = !empty($pc_id_arr) ? "'" . implode("','", $pc_id_arr) . "'" : "''";

// استعلام المنتجات
$sql_prd = "SELECT p.*, m.mu_name, c.cn_currency, c.cn_id as country, b.bnsprof_id, b.bnsprof_city, b.bnsprof_state, 
                   sp.mst_name
            FROM products p
            LEFT JOIN measurement_unit m ON m.mu_id = p.pd_unit
            LEFT JOIN country c ON c.cn_id = p.pd_currency
            LEFT JOIN business_profile b ON b.bnsprof_uid = p.pd_uid
            LEFT JOIN plan_member_id pm ON pm.b_id = b.bnsprof_id
            LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id
            WHERE p.pd_status = '1'
            AND p.pd_image != ''
            AND pm.expiry_date > UNIX_TIMESTAMP()
            AND p.pd_subcat_id IN ({$ids})
            {$sql_pd_ck}
            ORDER BY FIELD(pm.p_id, '5','4','3','15')
            LIMIT {$start}, {$per_page}";

$recObj = mysqli_query($con, $sql_prd);

// إجمالي عدد المنتجات
$query_pag_num = "SELECT COUNT(*) AS count 
                  FROM products p
                  LEFT JOIN business_profile b ON b.bnsprof_uid = p.pd_uid
                  LEFT JOIN plan_member_id pm ON pm.b_id = b.bnsprof_id
                  WHERE p.pd_status = '1'
                  AND p.pd_image != ''
                  AND pm.expiry_date > UNIX_TIMESTAMP()
                  AND p.pd_subcat_id IN ({$ids})
                  {$sql_pd_ck}";
$result_pag_num = mysqli_query($con, $query_pag_num);
$row = mysqli_fetch_array($result_pag_num, MYSQLI_ASSOC);
$count = (int)($row['count'] ?? 0);
$no_of_paginations = ceil($count / $per_page);
$pagi_string = "Page " . ($page) . " of " . $no_of_paginations;

// حساب أرقام الصفحات للتنقل
if ($cur_page >= 7) {
    $start_loop = $cur_page - 3;
    if ($no_of_paginations > $cur_page + 3) {
        $end_loop = $cur_page + 3;
    } elseif ($cur_page <= $no_of_paginations && $cur_page > $no_of_paginations - 6) {
        $start_loop = $no_of_paginations - 6;
        $end_loop = $no_of_paginations;
    } else {
        $end_loop = $no_of_paginations;
    }
} else {
    $start_loop = 1;
    if ($no_of_paginations > 7) {
        $end_loop = 7;
    } else {
        $end_loop = $no_of_paginations;
    }
}

// ترتيب التصنيفات
$sql_order = (get_page_settings('25') == 'manual') ? " ORDER BY pc_order, pc_name" : " ORDER BY pc_name";
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
    .bt {
        background-position: 0 -240px;
        margin: 18px 0 30px 0;
        clear: left;
        height: 0px !important;
        background-color: transparent;
        color: transparent;
    }
    </style>
</head>
<body>
    <div class="col-md-3 col-sm-3 col-xs-12 col-lg-2 prc-left-side">
        <h3 style="font-size: 20px; cursor: pointer;" onclick="toggle_menu();">
            <span class="fa fa-list"></span>&nbsp; أسواقى التجارية
        </h3>
        <div style="padding-left:10px; padding-right:10px;" class="left-side-bar-sale-offer">
            <link rel="stylesheet" href="../css/menu_styles.css" type="text/css" />
            
            <div id='cssmenu' class="" style="float:left; width:200px; display: none">
                <ul>
                    <?php
                    $sql_dd_mnu = "SELECT pc_id, pc_name, pc_image 
                                   FROM product_category 
                                   WHERE pc_parent_id = '0' AND pc_status = '1' {$sql_order}";
                    $res_dd_mnu = mysqli_query($con, $sql_dd_mnu);
                    
                    while ($row_dd_mnu = mysqli_fetch_object($res_dd_mnu)):
                    ?>
                        <li class='has-sub'>
                            <a href="category.php?token=<?php echo rand(10, 9999) . md5((string)$row_dd_mnu->pc_id); ?>">
                                <span><?php echo htmlspecialchars($row_dd_mnu->pc_name); ?></span>
                            </a>
                            <ul>
                                <?php
                                $sql_dd_cmnu = "SELECT pc_id, pc_sort_name 
                                                FROM product_category 
                                                WHERE pc_parent_id = '" . (int)$row_dd_mnu->pc_id . "' 
                                                AND pc_status = '1' {$sql_order}";
                                $res_dd_cmnu = mysqli_query($con, $sql_dd_cmnu);
                                
                                while ($row_dd_cmnu = mysqli_fetch_object($res_dd_cmnu)):
                                ?>
                                    <li>
                                        <a href="products.php?c=<?php echo md5((string)$row_dd_cmnu->pc_id); ?>">
                                            <span><?php echo htmlspecialchars(ucwords($row_dd_cmnu->pc_sort_name ?? '')); ?></span>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </li>
                    <?php endwhile; ?>
                    
                    <li><a href="dir.php">شاهد كل التصنيفات</a></li>
                </ul>
            </div>

            <?php
            $sql_subcat = "SELECT p.pc_id, p.pc_sort_name,
                                  (SELECT COUNT(*) 
                                   FROM products pr
                                   LEFT JOIN business_profile b ON b.bnsprof_uid = pr.pd_uid
                                   LEFT JOIN plan_member_id pm ON pm.b_id = b.bnsprof_id
                                   WHERE pr.pd_status = '1'
                                   AND pr.pd_image != ''
                                   AND pm.expiry_date > UNIX_TIMESTAMP()
                                   AND pr.pd_subcat_id = p.pc_id
                                   {$sql_pd_ck}) as tot_prod
                           FROM product_category p
                           WHERE md5(p.pc_parent_id) = '{$pc_id}'";
            
            $res_subcat = mysqli_query($con, $sql_subcat);
            
            while ($row_subcat = mysqli_fetch_object($res_subcat)):
                if ($row_subcat->tot_prod > 0):
            ?>
                    <div class="item-list">
                        <a style="cursor:pointer;" 
                           onclick="refineProductBySubCategory(1, '<?php echo md5((string)$row_subcat->pc_id); ?>');">
                            <?php echo htmlspecialchars(ucwords($row_subcat->pc_sort_name ?? '')) . " (" . (int)$row_subcat->tot_prod . ")"; ?>
                        </a>
                    </div>
            <?php 
                endif;
            endwhile; 
            ?>
        </div>
    </div>

    <div class="col-md-9 col-sm-9 col-lg-10 col-xs-12 prc-right-side">
        <div class="col-md-9 col-sm-9 col-xs-12 col-lg-9" id="final_result">
            <div class="middle-part">
                <button type="button" class="btn btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize" disabled>
                    منتجات
                </button>
                <button type="button" class="btn btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize">
                    <a href="https://egyptmart.shop/catcompany.php" style="color:#000;">موردون</a>
                </button>
            </div>
            
            <div class="middle-control-part row">
                <div class="col-md-2 col-xs-12">
                    <div class="dropdown sup-country-list">
                        <span class="dropdown-toggle" id="menu1" data-toggle="dropdown">البلاد العارضة</span>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                            <?php
                            $keywords = isset($keywords) ? $keywords : '';
                            
                            if (isset($_COOKIE['loc_id'])) {
                                $loc_id = (int)$_COOKIE['loc_id'];
                                $view_country = "SELECT DISTINCT pd_currency 
                                                FROM products p
                                                LEFT JOIN user u ON u.usr_id = p.pd_uid
                                                LEFT JOIN business_profile b ON b.bnsprof_uid = p.pd_uid
                                                LEFT JOIN plan_member_id pm ON pm.b_id = b.bnsprof_id
                                                WHERE (b.bnsprof_compname LIKE '%{$keywords}%')
                                                AND ((pd_preferred_buyer_location = 'domestic' AND u.country = '{$loc_id}')
                                                     OR (pd_preferred_buyer_location = 'any' AND u.country = '{$loc_id}')
                                                     OR (pd_preferred_buyer_location = 'my_city' AND u.country = '{$loc_id}'))
                                                AND pm.expiry_date > UNIX_TIMESTAMP()
                                                AND p.pd_status = '1'
                                                AND p.pd_image != ''
                                                GROUP BY pd_currency";
                            } else {
                                $view_country = "SELECT DISTINCT pd_currency 
                                                FROM products p
                                                LEFT JOIN user u ON u.usr_id = p.pd_uid
                                                LEFT JOIN business_profile b ON b.bnsprof_uid = p.pd_uid
                                                LEFT JOIN plan_member_id pm ON pm.b_id = b.bnsprof_id
                                                WHERE (b.bnsprof_compname LIKE '%{$keywords}%')
                                                AND ((pd_preferred_buyer_location = 'domestic')
                                                     OR (pd_preferred_buyer_location = 'any')
                                                     OR (pd_preferred_buyer_location = 'my_city'))
                                                AND pm.expiry_date > UNIX_TIMESTAMP()
                                                AND p.pd_status = '1'
                                                AND p.pd_image != ''
                                                GROUP BY pd_currency";
                            }
                            
                            $run_sql = mysqli_query($con, $view_country);
                            $country_buy = [];
                            
                            while ($row11 = mysqli_fetch_array($run_sql, MYSQLI_ASSOC)) {
                                // يمكن إضافة منطق جلب الدول هنا
                            }
                            
                            $country_buy = array_unique($country_buy);
                            
                            if (count($country_buy) > 0) {
                                foreach ($country_buy as $cb) {
                                    $Couflag = mysqli_query($con, "SELECT * FROM country WHERE cn_name = '" . mysqli_real_escape_string($con, $cb) . "'");
                                    while ($rowk = mysqli_fetch_array($Couflag, MYSQLI_ASSOC)) {
                            ?>
                                        <li>
                                            <img src='images/country_flag/<?php echo htmlspecialchars($rowk['cn_flag'] ?? ''); ?>' height='22' width='22'>
                                            <span><?php echo htmlspecialchars($rowk['cn_name'] ?? ''); ?></span>
                                        </li>
                            <?php
                                    }
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-10 col-xs-12">
                    <?php
                    $membership_type = isset($_SESSION['membership_type']) ? $_SESSION['membership_type'] : [];
                    
                    $mtqury = "SELECT sp.mp_id, sip.mst_icon, sp.mst_name 
                               FROM smembership_plan sp 
                               JOIN smembership_icon_plan sip ON sp.mp_id = sip.mp_id 
                               WHERE sp.mp_status != '0'";
                    $mtresult = mysqli_query($con, $mtqury);
                    
                    while ($mtrow = mysqli_fetch_array($mtresult, MYSQLI_ASSOC)):
                    ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="search_filter" 
                                   <?php echo (in_array($mtrow['mp_id'], $_POST['mst_type'] ?? [])) ? "checked" : ''; ?> 
                                   name="mst_type[]" value="<?php echo (int)$mtrow['mp_id']; ?>">
                            <img src="admin/images/<?php echo htmlspecialchars($mtrow['mst_icon'] ?? ''); ?>" 
                                 width="20px" height="20px;" style="margin-right:5px;"/>
                            <span class="txt-gray text-uppercase"><?php echo htmlspecialchars($mtrow['mst_name'] ?? ''); ?></span>
                        </label>
                    <?php endwhile; ?>
                    
                    <button class="btn btn-xs btn-default border-radius-0" style="padding:0 5px 0 5px">OK</button>
                    
                    <label for="min_order" class="checkbox-inline">
                        أدنى طلب : <input id="min_order" style="width: 50px"/>
                    </label>
                    
                    <button class="btn btn-xs btn-default border-radius-0" style="padding:0 5px 0 5px">OK</button>
                </div>
            </div>

            <div class="als-container" id="product_slider" style="border-radius:5px;">
                <div class="als-viewport" align="center">
                    <ul class="als-wrapper">
                        <?php if (mysqli_num_rows($recObj) > 0): ?>
                            <?php while ($row_prd = mysqli_fetch_object($recObj)):
                                $newtext = substr($row_prd->pd_title ?? '', 0, 12);
                                
                                // جلب معلومات المدينة والولاية
                                $cn_name = '';
                                $city_name = '';
                                $state_name = '';
                                $country_name = '';
                                
                                if (isset($_COOKIE['loc_id'])) {
                                    if (!empty($row_prd->bnsprof_city)) {
                                        $row_cityname = mysqli_fetch_object(mysqli_query($con, "SELECT ct_name FROM city WHERE ct_id = '" . (int)$row_prd->bnsprof_city . "' LIMIT 1"));
                                        $city_name = $row_cityname->ct_name ?? '';
                                    }
                                    if (!empty($row_prd->bnsprof_state)) {
                                        $row_statename = mysqli_fetch_object(mysqli_query($con, "SELECT state_name FROM states WHERE state_id = '" . (int)$row_prd->bnsprof_state . "' LIMIT 1"));
                                        $state_name = $row_statename->state_name ?? '';
                                    }
                                    
                                    if (!empty($city_name) || !empty($state_name)) {
                                        $cn_name = trim($city_name . " - " . $state_name, " -");
                                    }
                                } else {
                                    if (!empty($row_prd->bnsprof_city)) {
                                        $row_cityname = mysqli_fetch_object(mysqli_query($con, "SELECT ct_cn_id FROM city WHERE ct_id = '" . (int)$row_prd->bnsprof_city . "' LIMIT 1"));
                                        if ($row_cityname && !empty($row_cityname->ct_cn_id)) {
                                            $row_countryname = mysqli_fetch_object(mysqli_query($con, "SELECT cn_name FROM country WHERE cn_id = '" . (int)$row_cityname->ct_cn_id . "' LIMIT 1"));
                                            $country_name = $row_countryname->cn_name ?? '';
                                        }
                                    }
                                    $cn_name = $country_name;
                                }
                                
                                // جلب معلومات الاتصال
                                $row_contact = mysqli_fetch_object(mysqli_query($con, "SELECT mobile1, country_ph_code FROM user WHERE usr_id = '" . (int)$row_prd->pd_uid . "' LIMIT 1"));
                            ?>
                                <li class="als-item" style="border:1px solid #ccc; margin-top:1%; margin-left:1%; padding:4px !important; margin-bottom:1%; border-radius:4px; float:left; height:auto; background-color:rgba(251,251,251,0.96);">
                                    <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)($row_prd->bnsprof_id ?? '')); ?>&sc=<?php echo rand(10000, 99999) . (int)($row_prd->pd_subcat_id ?? 0); ?>#<?php echo (int)($row_prd->pd_id ?? 0); ?>" 
                                       style="text-decoration:none; color:#000" target="_blank">
                                        <img src="https://egyptmart.shop/upload/myproduct/thumb/<?php echo htmlspecialchars($row_prd->pd_image ?? ''); ?>" 
                                             alt="<?php echo htmlspecialchars(ucwords(substr($row_prd->pd_title ?? '', 0, 28))); ?>" 
                                             title="<?php echo htmlspecialchars(ucwords($row_prd->pd_title ?? '')); ?>" />
                                        
                                        <div style="height:0%; margin-top:3%; padding-top:5%;">
                                            <span class="utext" style="color:blue; font-size:17px !important;">
                                                <b><?php echo htmlspecialchars($newtext); ?></b>
                                            </span><br />
                                            <span style="color:red; font-size:13px !important; font-weight:bold;"><?php echo htmlspecialchars($cn_name); ?></span>
                                        </div>
                                        
                                        <hr />
                                        <hr />
                                        
                                        <div style="height:10%; margin-top:5%; font-size:13px;">
                                            أدنى طلب : <span style="color:red; font-weight:600; font-size:15px !important;"><?php echo (int)($row_prd->pd_min_order_qty ?? 0); ?></span>&nbsp;<?php echo htmlspecialchars($row_prd->mu_name ?? ''); ?>
                                        </div>
                                        
                                        <div style="height:10%; margin-top:1%; font-size:13px;">
                                            <?php echo htmlspecialchars($row_prd->cn_currency ?? ''); ?>&nbsp;
                                            <span style="color:red; font-weight:600; font-size:15px !important;"><?php echo (float)($row_prd->pd_fob_price ?? 0); ?></span>/<?php echo htmlspecialchars($row_prd->mu_name ?? ''); ?>
                                        </div>
                                        
                                        <div style="font-weight:bold; font-size:13px; padding:4px 0;">
                                            <?php echo htmlspecialchars($row_prd->mst_name ?? ''); ?>
                                        </div>
                                    </a>
                                    
                                    <p class="cnt-phone">
                                        <span class="cnt-phone-inner">
                                            <img src="images/mobile.png" width="25px">&nbsp;&nbsp;
                                            <a href="tel:+<?php echo htmlspecialchars($row_contact->country_ph_code ?? ''); ?>-<?php echo htmlspecialchars($row_contact->mobile1 ?? ''); ?>">
                                                <?php echo htmlspecialchars($row_contact->country_ph_code ?? ''); ?>-<?php echo htmlspecialchars($row_contact->mobile1 ?? ''); ?>
                                            </a>
                                        </span>
                                    </p>

                                    <p class="cnt_supplier">
                                        <span class="cnt_supplier_inner">
                                            <i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;&nbsp;تواصل مع المورد
                                        </span>
                                    </p>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="als-item" style="border:1px solid #484891; margin-top:1%; margin-left:1.5%; border-radius:4px; width:97%; height:20px; color:#F00;">
                                لايوجد تصنيفات مسجله بهذا التصنيف
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <?php if ($count > 0): ?>
                    <p class="cl"><br></p>
                    <p align="center" style="margin-bottom:10px;">
                        <?php
                        // زر الصفحة الأولى
                        if ($first_btn && $cur_page > 1) { ?>
                            <a href="javascript:loadProductByCategory('1','<?php echo $pc_id; ?>')">
                                <img id="firstmail" src="images/firsten.gif">
                            </a>
                        <?php } elseif ($first_btn) { ?>
                            <img id="firstmail" src="images/first.gif">
                        <?php } ?>&nbsp;
                        
                        <?php
                        // زر السابق
                        if ($previous_btn && $cur_page > 1) {
                            $pre = $cur_page - 1; ?>
                            <a href="javascript:loadProductByCategory('<?php echo $pre; ?>','<?php echo $pc_id; ?>')">
                                <img id="prevmail" src="images/prven.gif">
                            </a>
                        <?php } elseif ($previous_btn) { ?>
                            <img id="prevmail" src="images/prevmail.gif">
                        <?php } ?>&nbsp;
                        
                        <?php
                        // زر التالي
                        if ($next_btn && $cur_page < $no_of_paginations) {
                            $nex = $cur_page + 1; ?>
                            <a href="javascript:loadProductByCategory('<?php echo $nex; ?>','<?php echo $pc_id; ?>')">
                                <img id="nextmail" src="images/nxten.gif">
                            </a>
                        <?php } elseif ($next_btn) { ?>
                            <img id="nextmail" src="images/nextmail.gif">
                        <?php } ?>&nbsp;
                        
                        <?php
                        // زر الصفحة الأخيرة
                        if ($last_btn && $cur_page < $no_of_paginations) { ?>
                            <a href="javascript:loadProductByCategory('<?php echo $no_of_paginations; ?>','<?php echo $pc_id; ?>')">
                                <img id="lastmail" src="images/lastenv.gif">
                            </a>
                        <?php } elseif ($last_btn) { ?>
                            <img id="lastmail" src="images/last.gif">
                        <?php } ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-3 col-sm-12 col-xs-12 col-lg-3">
            <?php include_once '../index_rightsidebar_back.php'; ?>
        </div>
    </div>
</body>
</html>