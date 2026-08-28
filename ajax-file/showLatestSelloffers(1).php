<?php
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

// بناء شرط الموقع
$sql_so_ck = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_so_ck = " AND (
        (so_preferred_buyer_location='domestic' AND so_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id)) 
        OR 
        (so_preferred_buyer_location='any' AND so_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id))
    )";
} else {
    $location_geo_country = $location_geo_country ?? '';
    $sql_so_ck = " AND (
        (so_preferred_buyer_location='any')
        OR
        (so_preferred_buyer_location='abroad' AND so_usr_id NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
}

global $con;

// استعلام جلب البيانات
$sql_so = "SELECT so.*, u.*, bp.* 
           FROM sale_offer so
           INNER JOIN user u ON so.so_usr_id = u.usr_id
           INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
           WHERE so.so_approval_status = '1' 
           $sql_so_ck
           AND so.so_status = '1' 
           AND DATE_ADD(so.so_approval_date, INTERVAL so.so_validity DAY) >= NOW()
           ORDER BY so.so_approval_date DESC 
           LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql_so);

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    mysqli_stmt_bind_param($stmt, 'ii', $start, $per_page);
} else {
    $loc_country = $location_geo_country;
    mysqli_stmt_bind_param($stmt, 'sii', $loc_country, $start, $per_page);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// حساب إجمالي السجلات
$query_pag_num = "SELECT COUNT(*) as count 
                  FROM sale_offer so
                  INNER JOIN user u ON so.so_usr_id = u.usr_id
                  INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                  WHERE so.so_approval_status = '1' 
                  $sql_so_ck
                  AND so.so_status = '1' 
                  AND DATE_ADD(so.so_approval_date, INTERVAL so.so_validity DAY) >= NOW()";

$stmt_count = mysqli_prepare($con, $query_pag_num);

if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    mysqli_stmt_execute($stmt_count);
} else {
    mysqli_stmt_bind_param($stmt_count, 's', $location_geo_country);
    mysqli_stmt_execute($stmt_count);
}

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

<div class="bx a1 p2">
    <div class="bc1 f3" style="padding-left:5px;">
        <p class="flr fz2 ttc">إجمالى العدد المنشور - <span class="c6 bo"><?php echo $count; ?></span></p>
        آخر عروض البيع الخاصة المنشورة
    </div>
    
    <p class="flr p1 p2 p3 bg bpr"><!--<a href="#" class="tdn" target="_BLANK">FAQ - Sell Offers</a>--></p>
    
    <ul class="c3 lst">
        <?php while ($row_so = mysqli_fetch_object($result)): ?>
        <li>
     

// تحديد مسار الصورة الصحيح
<?php
// تحديد مسار الصورة الصحيح من الكائن $row_so
$image_src = 'upload/sale_offer/no-image.png';

if (!empty($row_so->so_pic)) {
    $image_path = $row_so->so_pic;
    
    // حالة 1: المسار يبدأ بـ http (رابط كامل)
    if (filter_var($image_path, FILTER_VALIDATE_URL)) {
        $image_src = $image_path;
    }
    // حالة 2: المسار يبدأ بـ upload/image_gallery/ (صورة من الجاليري)
    elseif (strpos($image_path, 'upload/image_gallery/') === 0) {
        $image_src = $image_path;
    }
    // حالة 3: المسار يبدأ بـ upload/sale_offer/ (صورة من الديسكتوب)
    elseif (strpos($image_path, 'upload/sale_offer/') === 0) {
        $image_src = $image_path;
    }
    // حالة 4: مجرد اسم ملف
    elseif (file_exists('upload/sale_offer/' . $image_path)) {
        $image_src = 'upload/sale_offer/' . $image_path;
    }
    // حالة 5: مجرد اسم ملف في مجلد الجاليري
    elseif (file_exists('upload/image_gallery/' . $image_path)) {
        $image_src = 'upload/image_gallery/' . $image_path;
    }
}
?>

<img src="<?php echo $image_src; ?>" width="68" height="68" style="object-fit:cover;" 
     onerror="this.src='upload/sale_offer/no-image.png'"
     alt="<?php echo htmlspecialchars($row_so->so_service ?? ''); ?>" 
     align="left" height="68" width="68">
     
     
     
                 alt="<?php echo htmlspecialchars($row_so->so_service ?? ''); ?>" 
                 align="left" height="68" width="68">
            
            <a href="saleoffer-details.php?id=<?php echo rand(1000, 9999) . md5((string)$row_so->so_id); ?>" 
               class="bo" style="font-size:15px">
                <?php echo htmlspecialchars(ucwords($row_so->so_service ?? '')); ?>
            </a>
            
            <p class="p1 lnh"><?php echo htmlspecialchars($row_so->so_description ?? ''); ?></p>
            
            <p class="p1">
                <?php
                $country_name = get_country_name((int)($row_so->country ?? 0));
                $country_flag = get_country_flag((int)($row_so->country ?? 0));
                $city_name = get_city_name((int)($row_so->bnsprof_city ?? 0));
                
                if ($row_so->so_preferred_buyer_location == 'any') {
                    ?>
                    <span class="c7">البلــد:</span> <?php echo htmlspecialchars($country_name); ?>
                    &nbsp;&nbsp;
                    <?php if (!empty($country_flag)): ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars($country_flag); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                    <?php endif; ?>
                    <span style="float:right"><span class="c7">أماكن البيع :</span> (تصدير ومحلى)</span>
                    <?php
                } elseif ($row_so->so_preferred_buyer_location == 'abroad') {
                    ?>
                    <span class="c7">البلــد:</span> <?php echo htmlspecialchars($country_name); ?>
                    &nbsp;&nbsp;
                    <?php if (!empty($country_flag)): ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars($country_flag); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                    <?php endif; ?>
                    <span style="float:right"><span class="c7">أماكن البيع :</span> (تصدير فقط)</span>
                    <?php
                } elseif ($row_so->so_preferred_buyer_location == 'domestic') {
                    ?>
                    <span class="c7">البلــد:</span> <?php echo htmlspecialchars($country_name); ?>
                    &nbsp;&nbsp;
                    <?php if (!empty($country_flag)): ?>
                    <img src="images/country_flag/<?php echo htmlspecialchars($country_flag); ?>" 
                         alt="" class="w4" align="top" height="15" width="23">
                    <?php endif; ?>
                    <span style="float:right"><span class="c7">أماكن البيع :</span> (محلى فقط)</span>
                    <?php
                } elseif ($row_so->so_preferred_buyer_location == 'my_city' && !empty($row_so->bnsprof_city) && $row_so->bnsprof_city != '0') {
                    ?>
                    <span class="c7">البلــد :</span> <?php echo htmlspecialchars($city_name); ?>
                    <span style="float:right"><span class="c7">أماكن البيع :</span> (مسافة 250 كيلومتر)</span>
                    <?php
                }
                ?>
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
                echo '<a href="javascript:showSaleoffers(\'1\')"><img id="firstmail" src="images/firsten.gif"></a>';
            } elseif ($first_btn) {
                echo '<img id="firstmail" src="images/first.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة السابقة
            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                echo '<a href="javascript:showSaleoffers(\'' . $pre . '\')"><img id="prevmail" src="images/prven.gif"></a>';
            } elseif ($previous_btn) {
                echo '<img id="prevmail" src="images/prevmail.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة التالية
            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                echo '<a href="javascript:showSaleoffers(\'' . $nex . '\')"><img id="nextmail" src="images/nxten.gif"></a>';
            } elseif ($next_btn) {
                echo '<img id="nextmail" src="images/nextmail.gif">';
            }
            echo '&nbsp;';
            
            // زر الصفحة الأخيرة
            if ($last_btn && $cur_page < $no_of_paginations) {
                echo '<a href="javascript:showSaleoffers(\'' . $no_of_paginations . '\')"><img id="lastmail" src="images/lasten.gif"></a>';
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

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_count);
?>