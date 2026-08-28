<?php
/**
 * اسم الملفtenders.php
 * الوصف: عرض المناقصات حسب التصنيف مع التقسيم (Pagination) وشروط الموقع
 * الإصدار: 2.0.0
 * تاريخ التحديث: 2024-01-25
 * متطلبات PHP: 8.3
 */

// بدء تشغيل المخزن المؤقت وجلسة العمل
ob_start();
session_start();

// تضمين ملف الإعدادات المشتركة
require_once "../common.php";

// التحقق من أن الطلب هو POST
if (!isset($_POST['page']) || !isset($_POST['id'])) {
    exit('طلب غير صالح');
}

// تنظيف المدخلات
$page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$pc_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// إذا كانت المدخلات غير صالحة، نعرض قيمة افتراضية
$page = ($page !== false && $page !== null) ? $page : 1;
$pc_id = ($pc_id !== false && $pc_id !== null) ? $pc_id : 0;

// إعدادات التقسيم
$per_page = 20;
$cur_page = $page;
$page_for_query = $page - 1;
$start = $page_for_query * $per_page;

/**
 * دالة تنفيذ الاستعلام بشكل آمن مع prepared statements
 */
function executeSecureQuery($con, $sql, $params, $types, $error_message) {
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        error_log($error_message . " - خطأ في التحضير: " . mysqli_error($con));
        return false;
    }
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log($error_message . " - خطأ في التنفيذ: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * دالة بناء شروط الموقع للمناقصات
 */
function buildLocationCondition(&$params, &$types) {
    global $location_geo_country;
    
    $condition = "";
    
    if (isset($_COOKIE['loc_id'])) {
        $loc_id = intval($_COOKIE['loc_id']);
        
        $condition = " AND (
            (tnd_preferred_location='domestic' AND tnd_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = ?)) 
            OR 
            (tnd_preferred_location='any' AND tnd_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = ?))
            OR
            (tnd_preferred_location='my_city' AND tnd_usr_id IN (
                SELECT DISTINCT bnsprof_uid 
                FROM business_profile 
                WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = ?)
            ))
        )";
        
        // إضافة المعاملات ثلاث مرات
        $params[] = $loc_id;
        $params[] = $loc_id;
        $params[] = $loc_id;
        $types .= "iii";
        
    } else {
        // تحديد رمز الدولة من المتغير العام
        $country_code = '';
        if (isset($location_geo_country)) {
            if (is_array($location_geo_country) && !empty($location_geo_country)) {
                $country_code = $location_geo_country[0];
            } else {
                $country_code = $location_geo_country;
            }
        }
        
        if (!empty($country_code)) {
            $condition = " AND (
                (tnd_preferred_location='any')
                OR
                (tnd_preferred_location='abroad' AND tnd_usr_id NOT IN (
                    SELECT DISTINCT usr_id 
                    FROM user 
                    WHERE country IN (SELECT cn_id FROM country WHERE cn_code = ?)
                ))
            )";
            
            $params[] = $country_code;
            $types .= "s";
        } else {
            // إذا لم يكن هناك دولة محددة، نظهر فقط المناقصات العامة
            $condition = " AND (tnd_preferred_location='any')";
        }
    }
    
    return $condition;
}

/**
 * دالة الحصول على المناقصات حسب التصنيف
 */
function getTendersByCategory($con, $pc_id, $start, $per_page) {
    $params = [];
    $types = "";
    
    // بناء شروط الموقع
    $location_condition = buildLocationCondition($params, $types);
    
    // بناء الاستعلام الأساسي
    $sql = "SELECT t.*, pc.*, u.*, bp.* 
            FROM tender t
            JOIN product_category pc ON t.tnd_pc_id = pc.pc_id
            JOIN user u ON t.tnd_usr_id = u.usr_id
            JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
            WHERE t.tnd_approval_status = '1' 
            AND TO_DAYS(t.tnd_due_date) >= TO_DAYS(NOW()) 
            AND t.tnd_status = '1'";
    
    if ($pc_id > 0) {
        $sql .= " AND pc.pc_parent_id IN (SELECT DISTINCT pc_id FROM product_category WHERE pc_parent_id = ?)";
        $params[] = $pc_id;
        $types .= "i";
    }
    
    $sql .= " {$location_condition} ORDER BY t.tnd_updated_date DESC LIMIT ?, ?";
    
    // إضافة معاملات LIMIT
    $params[] = $start;
    $params[] = $per_page;
    $types .= "ii";
    
    return executeSecureQuery(
        $con, 
        $sql, 
        $params, 
        $types, 
        "خطأ في جلب المناقصات حسب التصنيف"
    );
}

/**
 * دالة حساب العدد الإجمالي للمناقصات حسب التصنيف
 */
function getTotalTendersCountByCategory($con, $pc_id) {
    $params = [];
    $types = "";
    
    // بناء شروط الموقع
    $location_condition = buildLocationCondition($params, $types);
    
    // بناء استعلام العد
    $sql = "SELECT COUNT(*) as count 
            FROM tender t
            JOIN product_category pc ON t.tnd_pc_id = pc.pc_id
            JOIN user u ON t.tnd_usr_id = u.usr_id
            JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
            WHERE t.tnd_approval_status = '1' 
            AND TO_DAYS(t.tnd_due_date) >= TO_DAYS(NOW()) 
            AND t.tnd_status = '1'";
    
    if ($pc_id > 0) {
        $sql .= " AND pc.pc_parent_id IN (SELECT DISTINCT pc_id FROM product_category WHERE pc_parent_id = ?)";
        $params[] = $pc_id;
        $types .= "i";
    }
    
    $sql .= " {$location_condition}";
    
    $result = executeSecureQuery(
        $con, 
        $sql, 
        $params, 
        $types, 
        "خطأ في حساب عدد المناقصات حسب التصنيف"
    );
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return intval($row['count']);
    }
    
    return 0;
}

/**
 * دالة الحصول على اسم التصنيف
 */
function getCategoryName($con, $pc_id) {
    if ($pc_id <= 0) return '';
    
    $stmt = mysqli_prepare($con, "SELECT pc_name FROM product_category WHERE pc_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $pc_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        return $row->pc_name ?? '';
    }
    
    return '';
}

/**
 * دالة حساب نطاق أرقام الصفحات للعرض
 */
function calculatePaginationRange($cur_page, $no_of_paginations) {
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
        $end_loop = min(7, $no_of_paginations);
    }
    
    return ['start' => $start_loop, 'end' => $end_loop];
}

/**
 * دالة عرض أزرار التقسيم
 */
function displayPaginationButtons($cur_page, $no_of_paginations, $pc_id) {
    ?>
    <p align="center" style="margin-bottom:10px;">
        <?php
        // زر الصفحة الأولى
        if ($cur_page > 1) {
            echo '<a href="javascript:showLead(\'1\',\'' . $pc_id . '\')">';
            echo '<img id="firstmail" src="images/firsten.gif" alt="الأولى"></a>';
        } else {
            echo '<img id="firstmail" src="images/first.gif" alt="الأولى">';
        }
        ?>&nbsp;<?php
        
        // زر الصفحة السابقة
        if ($cur_page > 1) {
            $pre = $cur_page - 1;
            echo '<a href="javascript:showLead(\'' . $pre . '\',\'' . $pc_id . '\')">';
            echo '<img id="prevmail" src="images/prven.gif" alt="السابقة"></a>';
        } else {
            echo '<img id="prevmail" src="images/prevmail.gif" alt="السابقة">';
        }
        ?>&nbsp;<?php
        
        // زر الصفحة التالية
        if ($cur_page < $no_of_paginations) {
            $nex = $cur_page + 1;
            echo '<a href="javascript:showLead(\'' . $nex . '\',\'' . $pc_id . '\')">';
            echo '<img id="nextmail" src="images/nxten.gif" alt="التالية"></a>';
        } else {
            echo '<img id="nextmail" src="images/nextmail.gif" alt="التالية">';
        }
        ?>&nbsp;<?php
        
        // زر الصفحة الأخيرة
        if ($cur_page < $no_of_paginations) {
            echo '<a href="javascript:showLead(\'' . $no_of_paginations . '\',\'' . $pc_id . '\')">';
            echo '<img id="lastmail" src="images/lastenv.gif" alt="الأخيرة"></a>';
        } else {
            echo '<img id="lastmail" src="images/last.gif" alt="الأخيرة">';
        }
        ?>
    </p>
    <?php
}

/**
 * دالة عرض معلومات الموقع للمناقصة
 */
function displayLocationInfo($row_tnd) {
    $updated_date = date("d M,Y", strtotime($row_tnd->tnd_updated_date));
    
    if (!empty($row_tnd->tnd_preferred_location)) {
        switch($row_tnd->tnd_preferred_location) {
            case 'any':
                ?>
                <p class="p1">
                    <span class="c7">الموقع:</span> 
                    <?php echo htmlspecialchars(get_country_name($row_tnd->country)); ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_tnd->country)); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                </p>
                <p class="p1">
                    <span class="ltu flr">آخر تحديث: <?php echo $updated_date; ?></span>
                    <span class="c7">النطاق التجاري:</span> (داخلي وخارجي)
                </p>
                <?php
                break;
                
            case 'abroad':
                ?>
                <p class="p1">
                    <span class="c7">الموقع:</span> 
                    <?php echo htmlspecialchars(get_country_name($row_tnd->country)); ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_tnd->country)); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                </p>
                <p class="p1">
                    <span class="ltu flr">آخر تحديث: <?php echo $updated_date; ?></span>
                    <span class="c7">النطاق التجاري:</span> (خارجي فقط)
                </p>
                <?php
                break;
                
            case 'domestic':
                ?>
                <p class="p1">
                    <span class="c7">الموقع:</span> 
                    <?php echo htmlspecialchars(get_country_name($row_tnd->country)); ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_tnd->country)); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                </p>
                <p class="p1">
                    <span class="ltu flr">آخر تحديث: <?php echo $updated_date; ?></span>
                    <span class="c7">النطاق التجاري:</span> (داخلي فقط)
                </p>
                <?php
                break;
                
            case 'my_city':
                if (!empty($row_tnd->bnsprof_city) && $row_tnd->bnsprof_city != '0') {
                    ?>
                    <p class="p1">
                        <span class="c7">الموقع:</span> 
                        <?php echo htmlspecialchars(get_city_name($row_tnd->bnsprof_city)); ?>
                    </p>
                    <p class="p1">
                        <span class="ltu flr">آخر تحديث: <?php echo $updated_date; ?></span>
                        <span class="c7">النطاق التجاري:</span> (250 كم)
                    </p>
                    <?php
                }
                break;
        }
    } else {
        ?>
        <p class="p1">
            <span class="ltu flr">آخر تحديث: <?php echo $updated_date; ?></span>
            <span class="c7">الموقع:</span> 
            <?php echo htmlspecialchars(get_country_name($row_tnd->country)); ?>
            <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_tnd->country)); ?>" 
                 alt="" class="w4" align="top" height="15" width="23">
        </p>
        <?php
    }
}

/**
 * دالة عرض صف المناقصة
 */
function displayTenderRow($row_tnd) {
    $tender_link = "tender-details.php?id=" . rand(1000,9999) . md5($row_tnd->tnd_id);
    ?>
    <li>
        <a style="font-size:15px;font-weight:700" 
           href="<?php echo htmlspecialchars($tender_link); ?>">
            <?php echo htmlspecialchars(ucwords($row_tnd->tnd_heading)); ?>
        </a>
        <span id="tp0" class="off"></span>
        
        <p class="p1 lnh lsdc"><?php echo htmlspecialchars($row_tnd->tnd_details); ?></p>
        
        <?php displayLocationInfo($row_tnd); ?>
        
        <p class="c3"></p>
    </li>
    <?php
}

// تنفيذ الاستعلامات
$recObj = getTendersByCategory($con, $pc_id, $start, $per_page);
$count = getTotalTendersCountByCategory($con, $pc_id);
$no_of_paginations = ceil($count / $per_page);

// حساب نطاق الصفحات للعرض
$pagination_range = calculatePaginationRange($cur_page, $no_of_paginations);
$start_loop = $pagination_range['start'];
$end_loop = $pagination_range['end'];

// الحصول على اسم التصنيف
$category_name = getCategoryName($con, $pc_id);

?>

<!-- عرض النتائج -->
<div class="xx fl on" id="aaa">
    
    <?php if (!empty($category_name)): ?>
        <p class="cnb" style="font-size:15px;font-weight:700">
            <b class="cnr" style="font-size:15px;"><?php echo htmlspecialchars($category_name); ?></b>
        </p>
    <?php endif; ?>
    
    <div class="sl1">
        <ul class="lst">
            <?php if ($recObj && mysqli_num_rows($recObj) > 0): ?>
                <?php while($row_tnd = mysqli_fetch_object($recObj)): ?>
                    <?php displayTenderRow($row_tnd); ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </ul>
        
        <?php if ($count > 0): ?>
            <p class="cl"><br></p>
            <?php displayPaginationButtons($cur_page, $no_of_paginations, $pc_id); ?>
        <?php else: ?>
            <p class="cl" style="text-align: center;">
                <img src="/images/search_icon_man.png" width="100px" height="100px" alt="لا توجد نتائج"><br>
            </p>
            <p align="center" style="margin-bottom:10px;font-size: 20px;font-weight: 600;">
                لا توجد مناقصات
            </p>
        <?php endif; ?>
    </div>
</div>

<?php
// إنهاء المخزن المؤقت وإرسال المحتوى
ob_end_flush();
?>