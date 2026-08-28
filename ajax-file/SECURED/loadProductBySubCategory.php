<?php
/**
 * File: ajax/loadProductBySubCategory.php
 * Description: تحميل وعرض منتجات التصنيف الفرعي
 */

ob_start();
require_once __DIR__ . '/../common.php';

// ✅ جلب معرف التصنيف الفرعي
$pc_id = isset($_POST['id']) ? $_POST['id'] : '';

if (empty($pc_id)) {
    echo '<div class="alert alert-danger">معرف التصنيف غير صالح</div>';
    exit;
}

// ✅ جلب رقم الصفحة
$page = isset($_POST['page']) && is_numeric($_POST['page']) ? (int)$_POST['page'] : 1;
$cur_page = $page;
$page -= 1;
$per_page = 20;
$start = $page * $per_page;

global $con;

// ✅ فلترة البلد
$sql_pd_ck = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = '$loc_id'))
        OR 
        (pd_preferred_buyer_location = 'any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = '$loc_id'))
        OR
        (pd_preferred_buyer_location = 'my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = '$loc_id')))
    )";
} else {
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country = (SELECT cn_id FROM country WHERE cn_code = '" . $location_geo_country . "')))
    )";
}

// ✅ استعلام جلب المنتجات
$sql_prd = "SELECT p.*, mu.*, c.*, bp.*, pm.* 
            FROM products p
            INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
            INNER JOIN country c ON c.cn_id = p.pd_currency
            INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
            INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
            WHERE p.pd_status = '1' 
            AND p.pd_image != '' 
            AND pm.expiry_date > " . time() . "
            $sql_pd_ck
            AND MD5(p.pd_subcat_id) = '$pc_id'
            ORDER BY FIELD(pm.p_id,'5','4','3','15')
            LIMIT $start, $per_page";

$result = mysqli_query($con, $sql_prd);

// ✅ جلب العدد الإجمالي للمنتجات
$sql_count = "SELECT COUNT(*) as total 
              FROM products p
              INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
              INNER JOIN country c ON c.cn_id = p.pd_currency
              INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
              INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
              WHERE p.pd_status = '1' 
              AND p.pd_image != '' 
              AND pm.expiry_date > " . time() . "
              $sql_pd_ck
              AND MD5(p.pd_subcat_id) = '$pc_id'";

$count_res = mysqli_query($con, $sql_count);
$count_row = mysqli_fetch_assoc($count_res);
$total_records = (int)($count_row['total'] ?? 0);
$total_pages = ceil($total_records / $per_page);

// ✅ بناء HTML للمنتجات
$html = '';

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $product_title = htmlspecialchars($row['pd_title'] ?? '', ENT_QUOTES, 'UTF-8');
        $product_image = !empty($row['pd_image']) ? $row['pd_image'] : 'noimage.jpg';
        $product_price = isset($row['pd_fob_price']) ? number_format((float)$row['pd_fob_price'], 2) : '0.00';
        $product_currency = isset($row['cn_currency']) ? htmlspecialchars($row['cn_currency']) : 'USD';
        $product_id = (int)($row['pd_id'] ?? 0);
        $bnsprof_id = $row['bnsprof_id'] ?? 0;
        $subcat_id = $row['pd_subcat_id'] ?? 0;
        
        // ✅ بناء الرابط على طريقة النسخة 5.3
        $rand1 = rand(1000, 9999);
        $rand2 = rand(10000, 99999);
        $c_param = $rand1 . md5($bnsprof_id);
        $sc_param = $rand2 . $subcat_id;
        $link = 'company/products.php?c=' . $c_param . '&sc=' . $sc_param . '#' . $product_id;
        
        $html .= '<div class="product-item col-md-3 col-sm-4 col-xs-6">';
        $html .= '<div class="product-thumb">';
        $html .= '<a href="' . $link . '" target="_blank">';
        $html .= '<img src="upload/myproduct/' . $product_image . '" alt="' . $product_title . '" class="img-responsive" onerror="this.src=\'upload/myproduct/noimage.jpg\'">';
        $html .= '</a>';
        $html .= '<div class="product-title">';
        $html .= '<h4><a href="' . $link . '" target="_blank">' . $product_title . '</a></h4>';
        $html .= '</div>';
        $html .= '<div class="product-price">';
        $html .= '<span class="price">' . $product_price . ' ' . $product_currency . '</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
} else {
    $html = '<div class="col-md-12"><p class="text-center">لا توجد منتجات في هذا التصنيف</p></div>';
}

// ✅ بناء التصفح (Pagination)
$pagination = '';
if ($total_pages > 1) {
    $pagination .= '<ul class="pagination">';
    if ($cur_page > 1) {
        $pagination .= '<li><a href="#" onclick="loadProductBySubCategory(' . ($cur_page - 1) . ', \'' . $pc_id . '\')">« السابق</a></li>';
    }
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $cur_page) ? 'active' : '';
        $pagination .= '<li class="' . $active . '"><a href="#" onclick="loadProductBySubCategory(' . $i . ', \'' . $pc_id . '\')">' . $i . '</a></li>';
    }
    if ($cur_page < $total_pages) {
        $pagination .= '<li><a href="#" onclick="loadProductBySubCategory(' . ($cur_page + 1) . ', \'' . $pc_id . '\')">التالي »</a></li>';
    }
    $pagination .= '</ul>';
}

// ✅ إرجاع HTML كامل
echo '<div class="row">' . $html . '</div>';
echo '<div class="row"><div class="col-md-12 text-center">' . $pagination . '</div></div>';
?>

<div style="border:1px solid #F5ECFF; border-radius:5px; padding-left:10px;">
    <h4 style="color:#36006C">
        <a href="index.php" style="text-decoration:none; color:#36006C">Home</a>&nbsp;&raquo;&nbsp;
        <?php if (!empty($row_pcat[0])): ?>
        <a href="dir.php#main_cat_<?php echo $row_pcat[0]; ?>" style="text-decoration:none; color:#36006C">
            <?php echo htmlspecialchars(ucwords($row_pcat[1] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
        </a>&nbsp;&raquo;&nbsp;
        <?php endif; ?>
        <?php if (!empty($row_pcat[2])): ?>
        <a href="products.php?c=<?php echo md5((string)$row_pcat[2]); ?>" style="text-decoration:none; color:#36006C">
            <?php echo htmlspecialchars(ucwords($row_pcat[3] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
        </a>&nbsp;&raquo;&nbsp;
        <?php endif; ?>
        <?php echo htmlspecialchars(ucwords($row_pcat[4] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
    </h4>
</div>

<div class="als-container" id="product_slider" style="border:1px solid #F5ECFF; border-radius:5px;">
    <div class="als-viewport" align="center">
        <ul class="als-wrapper">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row_prd = mysqli_fetch_object($result)): 
                    $product_title = htmlspecialchars($row_prd->pd_title ?? '', ENT_QUOTES, 'UTF-8');
                    $product_title_short = htmlspecialchars(substr($product_title, 0, 12), ENT_QUOTES, 'UTF-8');
                    $product_image = htmlspecialchars($row_prd->pd_image ?? '', ENT_QUOTES, 'UTF-8');
                    $bnsprof_id = (int)($row_prd->bnsprof_id ?? 0);
                    $pd_subcat_id = (int)($row_prd->pd_subcat_id ?? 0);
                    $pd_id = (int)($row_prd->pd_id ?? 0);
                    $bnsprof_city = (int)($row_prd->bnsprof_city ?? 0);
                    $bnsprof_state = (int)($row_prd->bnsprof_state ?? 0);
                    $pd_min_order_qty = (int)($row_prd->pd_min_order_qty ?? 0);
                    $mu_name = htmlspecialchars($row_prd->mu_name ?? '', ENT_QUOTES, 'UTF-8');
                    $cn_currency = htmlspecialchars($row_prd->cn_currency ?? '', ENT_QUOTES, 'UTF-8');
                    $pd_fob_price = htmlspecialchars($row_prd->pd_fob_price ?? '', ENT_QUOTES, 'UTF-8');
                    
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
                ?>
                <li class="als-item" style="border:1px solid #ccc; margin-top:1%; margin-left:1%; margin-bottom:1%; border-radius:4px; float:left; height:190px; background-color:rgba(251, 251, 251, 0.96);">
                    <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>&sc=<?php echo rand(10000, 99999) . $pd_subcat_id; ?>#<?php echo $pd_id; ?>" 
                       style="text-decoration:none; color:#000" target="_blank">
                        <img src="upload/myproduct/thumb/<?php echo $product_image; ?>" 
                             alt="<?php echo htmlspecialchars(ucwords(substr($product_title, 0, 28)), ENT_QUOTES, 'UTF-8'); ?>" 
                             title="<?php echo $product_title; ?>" />
                        <div style="height:0%; margin-top:3%; padding-top:5%;">
                            <span style="color:blue;"><b><?php echo ucwords($product_title_short); ?>....</b></span><br />
                            <span style="color:red;"><?php echo $location_name; ?></span>
                        </div>
                        <hr />
                        <hr />
                        <div style="height:10%; margin-top:16%; font-size:11px;">
                            MOQ: <span style="color:red; font-weight:600; font-size:15px;"><?php echo $pd_min_order_qty; ?>&nbsp;</span><?php echo $mu_name; ?>
                        </div>
                        <div style="height:10%; margin-top:1%; font-size:11px;">
                            <?php echo $cn_currency; ?>&nbsp;
                            <span style="color:red; font-weight:600; font-size:15px;"><?php echo $pd_fob_price; ?></span>/<?php echo $mu_name; ?>
                        </div>
                    </a>
                </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="als-item" style="border:1px solid #484891; margin-top:1%; margin-left:1.5%; border-radius:4px; width:97%; height:20px; color:#F00">
                    No products listed for this category.
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php
// إغلاق الاتصال بقاعدة البيانات إذا كان mysqli مستخدماً
// mysqli_close($con);
?>