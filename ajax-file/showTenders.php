<?php
/**
 * اسم الملفshowTenders.phphp
 * الوصف: عرض مناقصات المستخدم مع التقسيم (Pagination)
 * الإصدار: 2.0.0
 * تاريخ التحديث: 2024-01-25
 * متطلبات PHP: 8.3
 */

// بدء تشغيل المخزن المؤقت وجلسة العمل
ob_start();
session_start();

// تضمين ملف الإعدادات المشتركة
require_once "../common.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    exit('يجب تسجيل الدخول أولاً');
}

// التحقق من أن الطلب هو POST
if (!isset($_POST['page'])) {
    exit('طلب غير صالح');
}

// تنظيف المدخلات
$page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$page = ($page !== false && $page !== null) ? $page : 1;
$user_id = intval($_SESSION['uid_indm']);

// إعدادات التقسيم
$per_page = 10;
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
 * دالة الحصول على مناقصات المستخدم
 */
function getUserTenders($con, $user_id, $start, $per_page) {
    $sql = "SELECT t.*, u.*, bp.* 
            FROM tender t
            JOIN user u ON t.tnd_usr_id = u.usr_id
            JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
            WHERE t.tnd_usr_id = ? 
            AND t.tnd_status = '1'
            ORDER BY t.tnd_updated_date DESC
            LIMIT ?, ?";
    
    return executeSecureQuery(
        $con, 
        $sql, 
        [$user_id, $start, $per_page], 
        "iii", 
        "خطأ في جلب مناقصات المستخدم"
    );
}

/**
 * دالة حساب العدد الإجمالي لمناقصات المستخدم
 */
function getTotalUserTendersCount($con, $user_id) {
    $sql = "SELECT COUNT(*) as count 
            FROM tender t
            JOIN user u ON t.tnd_usr_id = u.usr_id
            JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
            WHERE t.tnd_usr_id = ? 
            AND t.tnd_status = '1'";
    
    $result = executeSecureQuery(
        $con, 
        $sql, 
        [$user_id], 
        "i", 
        "خطأ في حساب عدد مناقصات المستخدم"
    );
    
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
 * دالة عرض أزرار التقسيم
 */
function displayPaginationButtons($cur_page, $no_of_paginations) {
    ?>
    <div class="f1_m2 rf_m2 p9_m2">
        <?php 
        // نص الصفحة الحالية
        echo "الصفحة " . $cur_page . " من " . $no_of_paginations; 
        ?>&nbsp;&nbsp;
        
        <?php
        // زر الصفحة الأولى
        if ($cur_page > 1) {
            echo '<a href="javascript:showTenders(\'1\')">';
            echo '<img id="firstmail" src="images/firsten.gif" alt="الأولى"></a>';
        } else {
            echo '<img id="firstmail" src="images/first.gif" alt="الأولى">';
        }
        ?>&nbsp;
        
        <?php
        // زر الصفحة السابقة
        if ($cur_page > 1) {
            $pre = $cur_page - 1;
            echo '<a href="javascript:showTenders(\'' . $pre . '\')">';
            echo '<img id="prevmail" src="images/prven.gif" alt="السابقة"></a>';
        } else {
            echo '<img id="prevmail" src="images/prevmail.gif" alt="السابقة">';
        }
        ?>&nbsp;
        
        <?php
        // زر الصفحة التالية
        if ($cur_page < $no_of_paginations) {
            $nex = $cur_page + 1;
            echo '<a href="javascript:showTenders(\'' . $nex . '\')">';
            echo '<img id="nextmail" src="images/nxten.gif" alt="التالية"></a>';
        } else {
            echo '<img id="nextmail" src="images/nextmail.gif" alt="التالية">';
        }
        ?>&nbsp;
        
        <?php
        // زر الصفحة الأخيرة
        if ($cur_page < $no_of_paginations) {
            echo '<a href="javascript:showTenders(\'' . $no_of_paginations . '\')">';
            echo '<img id="lastmail" src="images/lastenv.gif" alt="الأخيرة"></a>';
        } else {
            echo '<img id="lastmail" src="images/last.gif" alt="الأخيرة">';
        }
        ?>
        &nbsp;
    </div>
    <?php
}

/**
 * دالة عرض صف المناقصة
 */
function displayTenderRow($row) {
    ?>
    <tr>
        <td valign="TOP">
            <div class="mp5 blhd" style="border-right: 1px solid #E7EAEE;">
                <p>
                    <a onclick="detailTender(<?php echo intval($row->tnd_id); ?>)" style="cursor:pointer;">
                        <?php echo htmlspecialchars($row->tnd_heading); ?>
                    </a>
                    <?php if(isset($row->tnd_approval_status) && $row->tnd_approval_status == '0'): ?>
                        <img src="images/waiting_ico1.png" 
                             title="&lt;b&gt;المناقصة قيد المراجعة من قبل النظام&lt;/b&gt;" 
                             id="imgWaiting" name="imgWaiting" class="imgWaiting" align="absmiddle"
                             alt="قيد المراجعة">
                    <?php endif; ?>
                </p>
                <div style="padding:0 0 5px 0;font-size:11px;color:#727272;">
                    <b>تاريخ النشر:</b> 
                    <?php echo date("d M, Y", strtotime($row->tnd_updated_date)); ?>
                </div>
                <?php 
                // عرض مختصر من التفاصيل مع حماية ضد الـ XSS
                $details = isset($row->tnd_detail) ? strip_tags($row->tnd_detail) : '';
                echo '<div style="margin-bottom:5px;">' . htmlspecialchars(substr($details, 0, 200)) . '...</div>';
                ?>
                <div class="blvd">
                    <a onclick="detailTender(<?php echo intval($row->tnd_id); ?>)" style="cursor:pointer;">
                        عرض التفاصيل الكاملة
                    </a>
                </div>
            </div>
        </td>
        <td class="mp5" valign="TOP" width="208">
            <div class="bulletImage">
                <a id="apprvDel1" class="apprvDel" style="cursor:pointer;" 
                   onClick="delTender(<?php echo intval($row->tnd_id); ?>);">
                    حذف
                </a>
            </div>
        </td>
    </tr>
    <?php
}

// تنفيذ الاستعلامات
$recObj = getUserTenders($con, $user_id, $start, $per_page);
$count = getTotalUserTendersCount($con, $user_id);
$no_of_paginations = ceil($count / $per_page);

// حساب نطاق الصفحات للعرض
$pagination_range = calculatePaginationRange($cur_page, $no_of_paginations);
$start_loop = $pagination_range['start'];
$end_loop = $pagination_range['end'];

?>

<?php if ($count > 0 && $recObj && mysqli_num_rows($recObj) > 0): ?>
<!-- بداية عرض المناقصات -->
<div class="pbl_top_borderBuy">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
            <tr>
                <td height="33" width="100%">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td height="33" width="100%">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td height="30">
                                                    <div class="pbl_liv" style="font-family:arial;color:#474747;font-size:12px">
                                                        <b></b>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="pbl_liv" align="RIGHT">
                                                        <b>
                                                            <span class="pagenavigation">
                                                                <?php displayPaginationButtons($cur_page, $no_of_paginations); ?>
                                                            </span>
                                                        </b>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- جدول عنوان المناقصات -->
                    <table class="pbl_bg_topBuy" id="toptitle" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td class="pbl_top_mBuy" height="24">تفاصيل المناقصة</td>
                                <td class="pbl_top_mBuy" height="24" width="208">اختر الإجراء</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- قائمة المناقصات -->
<div id="bllistinmain">
    <div id="Listing1">
        <table class="selectsp1" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <?php while($row = mysqli_fetch_object($recObj)): ?>
                    <?php displayTenderRow($row); ?>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>

<!-- رسالة عدم وجود نتائج -->
<div style="clear:both;">
    <div style="font-size:12px;color:#b60000;padding: 10px 0 10px 0;" align="center">
        لا توجد مناقصات لعرضها
    </div>
</div>

<?php endif; ?>

<?php
// إنهاء المخزن المؤقت وإرسال المحتوى
ob_end_flush();
?>