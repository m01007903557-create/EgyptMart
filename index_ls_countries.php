<?php
/**
 * This file handles the display of country and state filters for search results
 */

// ============================================
// 1. جلب المتغيرات من URL
// ============================================
$rctyp = $_GET['rctyp'] ?? '';
$keywords = $_GET['keywords'] ?? '';
$idd = $_GET['idd'] ?? '';

// $sql_pd_ck is defined in the calling search.php; provide fallback so this file is safe standalone
if (!isset($sql_pd_ck)) {
    $sql_pd_ck = '';
}

// ============================================
// 2. الاتصال بقاعدة البيانات
// ============================================
global $con, $link;
if (!isset($link) && isset($con)) {
    $link = $con;
}

// ============================================
// 3. دالة مساعدة
// ============================================
if (!function_exists('city_to_country')) {
    function city_to_country($city) {
        global $link;
        if (empty($city)) return '';
        $city = mysqli_real_escape_string($link, $city);
        $query = "SELECT cn_name FROM country 
                  WHERE cn_id IN (SELECT ct_cn_id FROM city WHERE ct_name = '$city')";
        $result = mysqli_query($link, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['cn_name'];
        }
        return '';
    }
}

// ============================================
// 4. عرض الدول العارضة (GLOBAL)
// ============================================
if (!isset($_COOKIE['loc_id'])) {
    $rctypval = $rctyp ?: '';
    $keywordsval = $keywords ?: '';
    ?>
    <section class="ar-flags">
        <form action="search.php?rctyp=<?php echo urlencode($rctypval); ?>&keywords=<?php echo urlencode($keywordsval); ?>" method="post">
            <?php
            // بناء الاستعلام حسب نوع البحث
            if ($rctyp === 'buy_lead') {
                $safe_keywords = '';
                if (!empty($keywords)) {
                    $escaped = mysqli_real_escape_string($link, $keywords);
                    $safe_keywords = "AND (br_pd_name LIKE '%{$escaped}%' OR br_description LIKE '%{$escaped}%')";
                }
                $view_country = "SELECT country, br_estimate_qty_unit, mu_id, bnsprof_uid, br_u_id, bnsprof_id, cn_id, br_display_status, br_approval_status 
                                 FROM buy_requirement, measurement_unit, country, business_profile, user  
                                 WHERE br_estimate_qty_unit = mu_id  
                                 AND business_profile.bnsprof_uid = buy_requirement.br_u_id  
                                 {$safe_keywords}  
                                 AND br_approval_status='1'  
                                 GROUP BY br_u_id";
            } 
            else if ($rctyp === 'tender') {
    $safe_keywords = mysqli_real_escape_string($link, $keywords);
    $tender_keywords_string = "tnd_heading LIKE '%{$safe_keywords}%' OR tnd_details LIKE '%{$safe_keywords}%' OR tnd_products LIKE '%{$safe_keywords}%'";
    
    if (isset($_COOKIE['loc_id'])) {
        $loc_id = (int)$_COOKIE['loc_id'];
        $tenderCondition = " AND ((tnd_preferred_location='domestic' AND tnd_usr_id IN(SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}')) 
                              OR (tnd_preferred_location='any' AND tnd_usr_id IN(SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}')) 
                              OR (tnd_preferred_location='my_city' AND tnd_usr_id IN(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id='{$loc_id}'))))";
    } else {
        $tenderCondition = " AND ((tnd_preferred_location='domestic') OR (tnd_preferred_location='any') OR (tnd_preferred_location='my_city'))";
    }
    
    $view_country = "SELECT * FROM tender, product_category, user, business_profile, plan_member_id  
                     WHERE tnd_pc_id = pc_id  
                     AND tnd_usr_id = usr_id  
                     AND usr_id = bnsprof_uid  
                     AND business_profile.bnsprof_uid = tender.tnd_usr_id  
                     AND plan_member_id.b_id = business_profile.bnsprof_id  
                     AND plan_member_id.expiry_date > " . time() . "  
                     AND (" . $tender_keywords_string . ")  
                     AND tnd_due_date >= '" . date('Y-m-d') . "'  
                     AND tnd_approval_status = '1' " . $tenderCondition . "  
                     GROUP BY tender.tnd_currency ORDER BY tnd_id DESC";
}
            else if ($rctyp === 'Suppliers') {
                $safe_keywords = mysqli_real_escape_string($link, $keywords);
                if (!empty($keywords)) {
                    $view_country = "SELECT DISTINCT c.cn_id, c.cn_name, c.cn_flag
                                     FROM products p
                                     INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                                     INNER JOIN user u ON u.usr_id = p.pd_uid
                                     INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                                     INNER JOIN country c ON c.cn_id = u.country
                                     WHERE p.pd_status = '1'
                                     AND c.cn_status = '1'
                                     AND u.status = '1'
                                     AND bp.bnsprof_status = '1'
                                     AND p.pd_image != ''
                                     AND pm.expiry_date > " . time() . "
                                     AND (p.pd_title LIKE '%{$safe_keywords}%'
                                          OR bp.bnsprof_compname LIKE '%{$safe_keywords}%')
                                     ORDER BY c.cn_name";
                } else {
                    $view_country = "SELECT DISTINCT c.cn_id, c.cn_name, c.cn_flag
                                     FROM products p
                                     INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                                     INNER JOIN user u ON u.usr_id = p.pd_uid
                                     INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                                     INNER JOIN country c ON c.cn_id = u.country
                                     WHERE p.pd_status = '1'
                                     AND c.cn_status = '1'
                                     AND u.status = '1'
                                     AND bp.bnsprof_status = '1'
                                     AND pm.expiry_date > " . time() . "
                                     AND p.pd_image != ''
                                     ORDER BY c.cn_name";
                }
            } 
            else {
                $safe_keywords = mysqli_real_escape_string($link, $keywords);
                if ($idd != "") {
                    $view_country = "SELECT * FROM products, measurement_unit, country, business_profile, plan_member_id
                                     WHERE mu_id = pd_unit
                                     AND pd_subcat_id = " . (int)$idd . "
                                     AND business_profile.bnsprof_uid = products.pd_uid
                                     AND plan_member_id.b_id = business_profile.bnsprof_id
                                     AND pd_currency = cn_id
                                     AND cn_status = '1'
                                     " . $sql_pd_ck . "
                                     AND pd_status = '1'
                                     AND plan_member_id.expiry_date > " . time() . "
                                     AND pd_image != ''
                                     GROUP BY pd_currency";
                } else {
                    $view_country = "SELECT DISTINCT products.pd_uid, business_profile.bnsprof_city
                                     FROM products
                                     INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid
                                     INNER JOIN plan_member_id ON plan_member_id.b_id = business_profile.bnsprof_id
                                     INNER JOIN measurement_unit ON measurement_unit.mu_id = products.pd_unit
                                     INNER JOIN country ON country.cn_id = products.pd_currency
                                     WHERE (products.pd_title LIKE '%{$safe_keywords}%'
                                     OR business_profile.bnsprof_compname LIKE '%{$safe_keywords}%')
                                     AND country.cn_status = '1'
                                     " . $sql_pd_ck . "
                                     AND plan_member_id.expiry_date > " . time() . "
                                     AND products.pd_status = '1'
                                     AND products.pd_image != ''";
                }
            }
            
            // تنفيذ الاستعلام
            $run_sql = mysqli_query($link, $view_country);
            if (!$run_sql) {
                echo "<!-- خطأ في استعلام البلاد: " . mysqli_error($link) . " -->";
            }
            
            // معالجة النتائج وعرضها
            $counter = 0;
            $country_id = [];
            
            if (isset($_POST['country_id'])) {
                $_SESSION['country_id'] = is_array($_POST['country_id']) ? $_POST['country_id'] : [$_POST['country_id']];
            }
            
            if (isset($_SESSION['country_id'])) {
                $country_id = $_SESSION['country_id'];
            }
            
            $count_country = ($run_sql) ? mysqli_num_rows($run_sql) : 0;
            
            if ($count_country > 0) {  
                ?>
                <header><span class="h4" style="font-weight:bold">البـلاد العارضـــة</span></header>
                <?php
            }
            
            // عرض النتائج
            $is_suppliers_with_search = ($rctyp === 'Suppliers' && !empty($keywords));
            
            if ($is_suppliers_with_search && $run_sql && mysqli_num_rows($run_sql) > 0) {
                // حالة Suppliers مع بحث: النتائج مباشرة
                while ($row_country = mysqli_fetch_assoc($run_sql)) {
                    $counter++;
                    ?>
                    <div class="checkbox" style="text-align:left;">
                        <label>
                            <input type="checkbox" class="search_filter" 
                                <?php echo (in_array($row_country['cn_name'], $country_id)) ? "checked" : ''; ?> 
                                name="country_id[]" 
                                value="<?php echo htmlspecialchars($row_country['cn_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <?php if (!empty($row_country['cn_flag'])): ?>
                                <img src="images/country_flag/<?php echo htmlspecialchars($row_country['cn_flag'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" height="22" width="22">
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($row_country['cn_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                    </div>
                    <?php
                }
            } else {
                // الحالات الأخرى
                $country_buy = [];
                if ($run_sql && mysqli_num_rows($run_sql) > 0) {
                    while ($row11 = mysqli_fetch_assoc($run_sql)) {
                        if ($rctyp === 'buy_lead') { 
                            $user_city = function_exists('user_info') ? user_info($row11['br_u_id'], 'bnsprof_city') : '';
                            $country_buy[] = ucfirst(city_to_country($user_city));
                        } else if ($rctyp === 'tender') { 
                            $user_city = function_exists('user_info') ? user_info($row11['tnd_usr_id'], 'bnsprof_city') : '';
                            $country_buy[] = ucfirst(city_to_country($user_city));
                        } else if ($rctyp === 'Suppliers') { 
                            if (isset($row11['cn_name']) && !empty($row11['cn_name'])) {
                                $country_buy[] = $row11['cn_name'];
                            } else {
                                $user_city = function_exists('user_info') ? user_info($row11['pd_uid'], 'bnsprof_city') : '';
                                $country_buy[] = ucfirst(city_to_country($user_city));
                            }
                        } else {
                            $city_id = isset($row11['bnsprof_city']) ? (int)$row11['bnsprof_city'] : 0;
                            $country_query = mysqli_query($link, "SELECT cn_name FROM country 
                                                                 WHERE cn_id IN (SELECT ct_cn_id FROM city WHERE ct_id = $city_id)");
                            if ($country_query && mysqli_num_rows($country_query) > 0) {
                                $country_row = mysqli_fetch_assoc($country_query);
                                $country_name = $country_row['cn_name'];
                            } else {
                                $country_name = '';
                            }
                            $country_buy[] = ucfirst($country_name);
                        }
                    }
                }
                
                if (count($country_buy) > 0) {
                    $country_buy = array_unique($country_buy);
                    foreach ($country_buy as $cb) {
                        $Couflag = mysqli_query($link, "SELECT * FROM `country` WHERE cn_name = '" . mysqli_real_escape_string($link, $cb) . "'");
                        while ($rowk = mysqli_fetch_assoc($Couflag)) {
                            $counter++;
                            ?>
                            <div class="checkbox" style="text-align:left;">
                                <label>
                                    <input type="checkbox" class="search_filter" <?php echo (in_array($rowk['cn_name'], $country_id)) ? "checked" : ''; ?> name="country_id[]" value="<?php echo htmlspecialchars($rowk['cn_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"> 
                                    <?php 
                                    if (!empty($rowk['cn_flag'])) {
                                        echo "<img src='images/country_flag/" . htmlspecialchars($rowk['cn_flag'] ?? '', ENT_QUOTES, 'UTF-8') . "' height='22' width='22'>"; 
                                    } 
                                    ?>
                                    <span><?php echo htmlspecialchars($rowk['cn_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                </label>
                            </div>
                            <?php
                        }
                    }
                }
            }
            
            if ($count_country >= 5) {
                ?>
                <div class="text-right"> <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#countries" aria-expanded="false"> + المزيد </a> </div>
            <?php } ?>
            
            <?php if ($rctyp !== 'tender' && $rctyp !== 'buy_lead') { ?>
                <div class="form-group text-center">
                    <input id="btnSearch" value="تأكيد" class="btn btn-sm btn-warning border-radius-0 confirmBtns" type="submit">
                    <a href="javascript: window.history.go(-1)" class="cancelBtns">إلغاء</a>
                </div>
            <?php } ?>
        </form>
    </section>
<?php 
} // نهاية if (!isset($_COOKIE['loc_id']))

// ============================================
// 5. عرض المحافظات (عند وجود loc_id)
// ============================================
if (isset($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $safe_keywords = mysqli_real_escape_string($link, $keywords);
    
    $sql = "SELECT DISTINCT s.*
            FROM states s
            INNER JOIN business_profile bp ON bp.bnsprof_state = s.state_id
            INNER JOIN products p ON p.pd_uid = bp.bnsprof_uid
            INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
            WHERE s.state_cn_id = $loc_id
            AND p.pd_status = '1'
            AND p.pd_image != ''
            AND pm.expiry_date > " . time() . "
            AND (p.pd_title LIKE '%{$safe_keywords}%' 
                 OR bp.bnsprof_compname LIKE '%{$safe_keywords}%')
            GROUP BY s.state_id
            ORDER BY s.state_name";

    $run_sql1 = mysqli_query($link, $sql);
    
    if (!$run_sql1) {
        echo "<!-- خطأ في استعلام المحافظات: " . mysqli_error($link) . " -->";
    }
    
    $count_states = ($run_sql1) ? mysqli_num_rows($run_sql1) : 0;
    
    if ($count_states > 0) {
        ?>
        <section class="ar-flags">
            <header><span class="h4" style="font-weight:bold">المحافظات العارضـة</span></header>
            <form action="search.php?rctyp=<?php echo urlencode($rctyp); ?>&keywords=<?php echo urlencode($keywords); ?>" method="post">
                <?php
                $counter = 0;
                $sql_country_flag = mysqli_query($link, "SELECT * FROM country WHERE cn_id=" . $loc_id);
                $row_flag = mysqli_fetch_assoc($sql_country_flag);
                
                while ($rowz = mysqli_fetch_assoc($run_sql1)) {
                    $counter++;
                    if ($counter == 5) {
                        echo '<div class="collapse" id="states">';
                    }
                    ?>
                    <div id="loading_div_status">
                        <label>
                            <input type="checkbox" class="search_filter" 
                                <?php echo (isset($_POST['state_id']) && in_array($rowz['state_id'], (array)$_POST['state_id'])) ? "checked" : ''; ?> 
                                name="state_id[]" 
                                value="<?php echo (int)$rowz['state_id']; ?>">
                            <?php if (!empty($row_flag['cn_flag'])) { ?>
                                <img src="images/country_flag/<?php echo htmlspecialchars($row_flag['cn_flag'] ?? ''); ?>" height="22" width="22"/>
                            <?php } ?>
                            <span><?php echo htmlspecialchars($rowz['state_name'] ?? ''); ?></span>
                        </label>
                    </div>
                    <?php
                    if ($count_states >= 5 && $counter == $count_states) {
                        echo '</div>';
                    }
                }
                
                if ($count_states >= 5) { ?>
                    <div class="text-right">
                        <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#states" aria-expanded="false">+ المزيد</a>
                    </div>
                <?php } ?>
                
                <?php if ($rctyp !== 'tender' && $rctyp !== 'buy_lead') { ?>
                    <div class="form-group text-center">
                        <input value="تأكيد" class="btn btn-sm btn-warning border-radius-0 confirmBtns" type="submit">
                        <a href="javascript: window.history.go(-1)" class="cancelBtns">إلغاء</a>
                    </div>
                <?php } ?>
            </form>
        </section>
        <?php
    } else {
        echo "<!-- لا توجد محافظات للبلد ID: $loc_id -->";
    }
}
?>
