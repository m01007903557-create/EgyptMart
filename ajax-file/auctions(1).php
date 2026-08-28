<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * اسم الملف: auctions.php
 * الوصف: ملف عرض المزادات مع التقسيم (Pagination) - متوافق مع PHP 8.3
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

// تحديد شروط الموقع
$sql_auc_ck = buildLocationCondition();

// بناء استعلام عرض المزادات
$sql_bl = buildAuctionQuery($pc_id, $start, $per_page, $sql_auc_ck);

// تنفيذ الاستعلام
$recObj = executeQuery($con, $sql_bl, "خطأ في استعلام المزادات");

// حساب العدد الإجمالي للمزادات
$count = getTotalCount($con, $pc_id, $sql_auc_ck);
$no_of_paginations = ceil($count / $per_page);

// حساب أرقام الصفحات للعرض
$pagination_range = calculatePaginationRange($cur_page, $no_of_paginations);
$start_loop = $pagination_range['start'];
$end_loop = $pagination_range['end'];

/**
 * دالة بناء شروط الموقع
 * @return string شروط SQL للموقع
 */
function buildLocationCondition(): string {
    if (isset($_COOKIE['loc_id'])) {
        $loc_id = intval($_COOKIE['loc_id']);
        return " AND (
            (auc_preferred_location='domestic' AND auc_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = ?)) 
            OR (auc_preferred_location='any' AND auc_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = ?))
            OR (auc_preferred_location='my_city' AND auc_usr_id IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = ?)))
        )";
    } else {
        global $location_geo_country;
        $country = is_array($location_geo_country) ? $location_geo_country[0] : $location_geo_country;
        return " AND (
            (auc_preferred_location='any')
            OR (auc_preferred_location='abroad' AND auc_usr_id NOT IN (SELECT DISTINCT usr_id FROM user WHERE country IN (SELECT cn_id FROM country WHERE cn_code = ?)))
        )";
    }
}

/**
 * دالة بناء استعلام المزادات
 */
function buildAuctionQuery($pc_id, $start, $per_page, $sql_auc_ck) {
    global $con;
    
    $base_query = "SELECT a.*, pc.*, u.*, bp.* 
                   FROM auction a
                   JOIN product_category pc ON a.auc_pc_id = pc.pc_id
                   JOIN user u ON a.auc_usr_id = u.usr_id
                   JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                   WHERE a.auc_approval_status = '1' 
                   AND TO_DAYS(a.auc_due_date) >= TO_DAYS(NOW()) 
                   AND a.auc_status = '1'";
    
    if ($pc_id > 0) {
        $base_query .= " AND pc.pc_parent_id IN (SELECT DISTINCT pc_id FROM product_category WHERE pc_parent_id = ?)";
    }
    
    $base_query .= " " . $sql_auc_ck . " ORDER BY a.auc_updated_date DESC LIMIT ?, ?";
    
    return $base_query;
}

/**
 * دالة تنفيذ الاستعلام بشكل آمن
 */
function executeQuery($con, $sql, $error_message) {
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        error_log($error_message . ": " . mysqli_error($con));
        return false;
    }
    
    // ربط المتغيرات حسب الحالة
      // حساب عدد علامات ? في الاستعلام
        $param_count = substr_count($sql, '?');
    error_log("DEBUG auctions.php: Number of ? = " . $param_count);
    error_log("DEBUG auctions.php: SQL = " . $sql);
    
    if (isset($_COOKIE['loc_id'])) {
        $loc_id = intval($_COOKIE['loc_id']);
        if (strpos($sql, 'LIMIT') !== false) {
            // استعلام مع LIMIT - يتوقع 5 parameters
            if ($param_count == 5) {
                mysqli_stmt_bind_param($stmt, "iiiii", $loc_id, $loc_id, $loc_id, $start, $per_page);
            } elseif ($param_count == 4) {
                mysqli_stmt_bind_param($stmt, "iiii", $loc_id, $loc_id, $loc_id, $start);
            } elseif ($param_count == 3) {
                mysqli_stmt_bind_param($stmt, "iii", $loc_id, $loc_id, $loc_id);
            } else {
                error_log("ERROR: Unexpected param count for LIMIT query: " . $param_count);
                mysqli_stmt_bind_param($stmt, "i", $loc_id);
            }
        } else {
            // استعلام بدون LIMIT - يتوقع 3 parameters
            if ($param_count == 3) {
                mysqli_stmt_bind_param($stmt, "iii", $loc_id, $loc_id, $loc_id);
            } elseif ($param_count == 2) {
                mysqli_stmt_bind_param($stmt, "ii", $loc_id, $loc_id);
            } else {
                error_log("ERROR: Unexpected param count for non-LIMIT query: " . $param_count);
                mysqli_stmt_bind_param($stmt, "i", $loc_id);
            }
        }
    } else {
        global $location_geo_country;
        $country = is_array($location_geo_country) ? $location_geo_country[0] : $location_geo_country;
        if (strpos($sql, 'LIMIT') !== false) {
            // استعلام مع LIMIT - يتوقع 3 parameters (country, start, per_page)
            if ($param_count == 3) {
                mysqli_stmt_bind_param($stmt, "sii", $country, $start, $per_page);
            } elseif ($param_count == 2) {
                mysqli_stmt_bind_param($stmt, "si", $country, $start);
            } elseif ($param_count == 4) {
                // إذا كان هناك 4 parameters، نستخدم قيمة افتراضية
                $extra = 0;
                mysqli_stmt_bind_param($stmt, "siii", $country, $start, $per_page, $extra);
            } else {
                error_log("ERROR: Unexpected param count for LIMIT query (no cookie): " . $param_count);
                mysqli_stmt_bind_param($stmt, "s", $country);
            }
        } else {
            // استعلام بدون LIMIT - يتوقع 1 parameter
            if ($param_count == 1) {
                mysqli_stmt_bind_param($stmt, "s", $country);
            } else {
                error_log("ERROR: Unexpected param count for non-LIMIT query (no cookie): " . $param_count);
$param_count = substr_count($sql, '?');
if ($param_count == 4) {
    $extra = 0;
    mysqli_stmt_bind_param($stmt, "siii", $country, $start, $per_page, $extra);
} elseif ($param_count == 3) {
    mysqli_stmt_bind_param($stmt, "sii", $country, $start, $per_page);
} elseif ($param_count == 2) {
    mysqli_stmt_bind_param($stmt, "si", $country, $start);
} else {
    $param_count = substr_count($sql, '?');
if ($param_count == 4) {
    $extra = 0;
    mysqli_stmt_bind_param($stmt, "siii", $country, $start, $per_page, $extra);
} elseif ($param_count == 3) {
    mysqli_stmt_bind_param($stmt, "sii", $country, $start, $per_page);
} elseif ($param_count == 2) {
    mysqli_stmt_bind_param($stmt, "si", $country, $start);
} else {
    mysqli_stmt_bind_param($stmt, "s", $country);
}
}            }
        }
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * دالة حساب العدد الإجمالي للمزادات
 */
function getTotalCount($con, $pc_id, $sql_auc_ck) {
    $count_query = "SELECT COUNT(*) as count 
                    FROM auction a
                    JOIN product_category pc ON a.auc_pc_id = pc.pc_id
                    JOIN user u ON a.auc_usr_id = u.usr_id
                    JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                    WHERE a.auc_approval_status = '1' 
                    AND TO_DAYS(a.auc_due_date) >= TO_DAYS(NOW()) 
                    AND a.auc_status = '1'";
    
    if ($pc_id > 0) {
        $count_query .= " AND pc.pc_parent_id IN (SELECT DISTINCT pc_id FROM product_category WHERE pc_parent_id = ?)";
    }
    
    $count_query .= " " . $sql_auc_ck;
    
    $result = executeQuery($con, $count_query, "خطأ في حساب عدد المزادات");
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return intval($row['count']);
    }
    
    return 0;
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
 * دالة عرض المزاد
 */
function displayAuction($row_auc) {
    ?>
    <li>
        <a style="font-size:15px;font-weight:700" 
           href="auction-details.php?id=<?php echo rand(1000,9999) . md5($row_auc->auc_id); ?>">
            <?php echo htmlspecialchars(ucwords($row_auc->auc_heading)); ?>
        </a>
        
        <p class="p1 lnh lsdc"><?php echo htmlspecialchars($row_auc->auc_details); ?></p>
        
        <?php displayLocationInfo($row_auc); ?>
        
        <p class="c3"></p>
    </li>
    <?php
}

/**
 * دالة عرض معلومات الموقع
 */
function displayLocationInfo($row_auc) {
    $updated_date = date("d M,Y", strtotime($row_auc->auc_updated_date));
    
    if (!empty($row_auc->auc_preferred_location)) {
        switch($row_auc->auc_preferred_location) {
            case 'any':
                ?>
                <p class="p1">
                    <span class="c7">Location:</span> 
                    <?php echo htmlspecialchars(get_country_name($row_auc->country)); ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_auc->country)); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                </p>
                <p class="p1">
                    <span class="ltu flr">Updated: <?php echo $updated_date; ?></span>
                    <span class="c7">Trade Scope:</span> (Foreign & Domestic)
                </p>
                <?php
                break;
                
            case 'abroad':
                ?>
                <p class="p1">
                    <span class="c7">Location:</span> 
                    <?php echo htmlspecialchars(get_country_name($row_auc->country)); ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_auc->country)); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                </p>
                <p class="p1">
                    <span class="ltu flr">Updated: <?php echo $updated_date; ?></span>
                    <span class="c7">Trade Scope:</span> (Foreign Only)
                </p>
                <?php
                break;
                
            case 'domestic':
                ?>
                <p class="p1">
                    <span class="c7">Location:</span> 
                    <?php echo htmlspecialchars(get_country_name($row_auc->country)); ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_auc->country)); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                </p>
                <p class="p1">
                    <span class="ltu flr">Updated: <?php echo $updated_date; ?></span>
                    <span class="c7">Trade Scope:</span> (Domestic Only)
                </p>
                <?php
                break;
                
            case 'my_city':
                if (!empty($row_auc->bnsprof_city) && $row_auc->bnsprof_city != '0') {
                    ?>
                    <p class="p1">
                        <span class="c7">Location:</span> 
                        <?php echo htmlspecialchars(get_city_name($row_auc->bnsprof_city)); ?>
                    </p>
                    <p class="p1">
                        <span class="ltu flr">Updated: <?php echo $updated_date; ?></span>
                        <span class="c7">Trade Scope:</span> (250 KM)
                    </p>
                    <?php
                }
                break;
        }
    } else {
        ?>
        <p class="p1">
            <span class="ltu flr">Updated: <?php echo $updated_date; ?></span>
            <span class="c7">Location:</span> 
            <?php echo htmlspecialchars(get_country_name($row_auc->country)); ?>
            <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_auc->country)); ?>" 
                 alt="" class="w4" align="top" height="15" width="23">
        </p>
        <?php
    }
}

/**
 * دالة عرض أزرار التقسيم
 */
function displayPagination($cur_page, $no_of_paginations, $pc_id) {
    ?>
    <p align="center" style="margin-bottom:10px;">
        <?php
        // زر الأولى
        if ($cur_page > 1) {
            echo '<a href="javascript:showAuction(\'1\',\'' . $pc_id . '\')">';
            echo '<img id="firstmail" src="images/firsten.gif"></a>';
        } else {
            echo '<img id="firstmail" src="images/first.gif">';
        }
        echo '&nbsp;';
        
        // زر السابق
        if ($cur_page > 1) {
            $pre = $cur_page - 1;
            echo '<a href="javascript:showAuction(\'' . $pre . '\',\'' . $pc_id . '\')">';
            echo '<img id="prevmail" src="images/prven.gif"></a>';
        } else {
            echo '<img id="prevmail" src="images/prevmail.gif">';
        }
        echo '&nbsp;';
        
        // زر التالي
        if ($cur_page < $no_of_paginations) {
            $nex = $cur_page + 1;
            echo '<a href="javascript:showAuction(\'' . $nex . '\',\'' . $pc_id . '\')">';
            echo '<img id="nextmail" src="images/nxten.gif"></a>';
        } else {
            echo '<img id="nextmail" src="images/nextmail.gif">';
        }
        echo '&nbsp;';
        
        // زر الأخيرة
        if ($cur_page < $no_of_paginations) {
            echo '<a href="javascript:showAuction(\'' . $no_of_paginations . '\',\'' . $pc_id . '\')">';
            echo '<img id="lastmail" src="images/lastenv.gif"></a>';
        } else {
            echo '<img id="lastmail" src="images/last.gif">';
        }
        ?>
    </p>
    <?php
}

?>

<!-- عرض النتائج -->
<div class="xx fl on" id="aaa">
    <?php if ($pc_id > 0): ?>
        <p class="cnb" style="font-size:15px;font-weight:700">
            <b class="cnr" style="font-size:15px;"><?php echo htmlspecialchars(getCategoryName($con, $pc_id)); ?></b>
        </p>
    <?php endif; ?>
    
    <div class="sl1">
        <ul class="lst">
            <?php if ($recObj && mysqli_num_rows($recObj) > 0): ?>
                <?php while($row_auc = mysqli_fetch_object($recObj)): ?>
                    <?php displayAuction($row_auc); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- رسالة عدم وجود نتائج -->
            <?php endif; ?>
        </ul>
        
        <?php if ($count > 0): ?>
            <p class="cl"><br></p>
            <?php displayPagination($cur_page, $no_of_paginations, $pc_id); ?>
        <?php else: ?>
            <p class="cl" style="text-align: center;">
                <img src="/images/search_icon_man.png" width="100px" height="100px"><br>
            </p>
            <p align="center" style="margin-bottom:10px;font-size: 20px;font-weight: 600;">
                لا توجد مزادات في هذا التصنيف
            </p>
        <?php endif; ?>
    </div>
</div>

<?php
// إنهاء المخزن المؤقت وإرسال المحتوى
ob_end_flush();
?>