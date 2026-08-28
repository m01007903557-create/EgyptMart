<?php
/**
 * اسم الملف: purchased-auctions.php
 * الوصف: عرض المزادات المشتراة للمستخدم مع التقسيم (Pagination)
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
 * دالة الحصول على المزادات المشتراة
 */
function getPurchasedAuctions($con, $user_id, $start, $per_page) {
    $sql = "SELECT a.*, pa.* 
            FROM auction a
            JOIN purchased_auction pa ON pa.pauc_auc_id = a.auc_id
            WHERE a.auc_status = '1' 
            AND pa.pauc_usr_id = ?
            ORDER BY pa.pauc_purchase_date DESC
            LIMIT ?, ?";
    
    return executeSecureQuery(
        $con, 
        $sql, 
        [$user_id, $start, $per_page], 
        "iii", 
        "خطأ في جلب المزادات المشتراة"
    );
}

/**
 * دالة حساب العدد الإجمالي للمزادات المشتراة
 */
function getTotalPurchasedCount($con, $user_id) {
    $sql = "SELECT COUNT(*) as count 
            FROM auction a
            JOIN purchased_auction pa ON pa.pauc_auc_id = a.auc_id
            WHERE a.auc_status = '1' 
            AND pa.pauc_usr_id = ?";
    
    $result = executeSecureQuery(
        $con, 
        $sql, 
        [$user_id], 
        "i", 
        "خطأ في حساب عدد المزادات المشتراة"
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

// تنفيذ الاستعلامات
$recObj = getPurchasedAuctions($con, $user_id, $start, $per_page);
$count = getTotalPurchasedCount($con, $user_id);
$no_of_paginations = ceil($count / $per_page);
$pagi_string = "الصفحة " . $page . " من " . $no_of_paginations;

// حساب نطاق الصفحات للعرض
$pagination_range = calculatePaginationRange($cur_page, $no_of_paginations);
$start_loop = $pagination_range['start'];
$end_loop = $pagination_range['end'];

?>

<?php if ($count > 0 && $recObj && mysqli_num_rows($recObj) > 0): ?>
<!-- بداية عرض المزادات المشتراة -->
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
                                                                <div class="f1_m2 rf_m2 p9_m2">
                                                                    <?php echo htmlspecialchars($pagi_string); ?>&nbsp;&nbsp;
                                                                    
                                                                    <?php
                                                                    // زر الصفحة الأولى
                                                                    if ($cur_page > 1) {
                                                                        echo '<a href="javascript:purchasedAuctions(\'1\')">';
                                                                        echo '<img id="firstmail" src="images/firsten.gif" alt="الأولى"></a>';
                                                                    } else {
                                                                        echo '<img id="firstmail" src="images/first.gif" alt="الأولى">';
                                                                    }
                                                                    ?>&nbsp;
                                                                    
                                                                    <?php
                                                                    // زر الصفحة السابقة
                                                                    if ($cur_page > 1) {
                                                                        $pre = $cur_page - 1;
                                                                        echo '<a href="javascript:purchasedAuctions(\'' . $pre . '\')">';
                                                                        echo '<img id="prevmail" src="images/prven.gif" alt="السابقة"></a>';
                                                                    } else {
                                                                        echo '<img id="prevmail" src="images/prevmail.gif" alt="السابقة">';
                                                                    }
                                                                    ?>&nbsp;
                                                                    
                                                                    <?php
                                                                    // زر الصفحة التالية
                                                                    if ($cur_page < $no_of_paginations) {
                                                                        $nex = $cur_page + 1;
                                                                        echo '<a href="javascript:purchasedAuctions(\'' . $nex . '\')">';
                                                                        echo '<img id="nextmail" src="images/nxten.gif" alt="التالية"></a>';
                                                                    } else {
                                                                        echo '<img id="nextmail" src="images/nextmail.gif" alt="التالية">';
                                                                    }
                                                                    ?>&nbsp;
                                                                    
                                                                    <?php
                                                                    // زر الصفحة الأخيرة
                                                                    if ($cur_page < $no_of_paginations) {
                                                                        echo '<a href="javascript:purchasedAuctions(\'' . $no_of_paginations . '\')">';
                                                                        echo '<img id="lastmail" src="images/lastenv.gif" alt="الأخيرة"></a>';
                                                                    } else {
                                                                        echo '<img id="lastmail" src="images/last.gif" alt="الأخيرة">';
                                                                    }
                                                                    ?>
                                                                    &nbsp;
                                                                </div>
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
                    
                    <!-- جدول عنوان المزادات -->
                    <table class="pbl_bg_topBuy" id="toptitle" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td class="pbl_top_mBuy" height="24">تفاصيل المزاد</td>
                                <td class="pbl_top_mBuy" height="24" width="208">اختر الإجراء</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- قائمة المزادات -->
<div id="bllistinmain">
    <div id="Listing1">
        <table class="selectsp1" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                
                <!-- تضمين ملفات JavaScript اللازمة -->
                <script src="js/jquery.colorbox.js"></script>
                <link href="css/colorbox.css" type="text/css" rel="stylesheet">
                <script>
                    $(document).ready(function(){
                        $(".ajax").colorbox({ width:"61%"});
                    });
                </script>

                <?php while($row = mysqli_fetch_object($recObj)): ?>
                <tr>
                    <td valign="TOP">
                        <div class="mp5 blhd" style="border-right: 1px solid #E7EAEE;">
                            <p>
                                <a onclick="detailPurAuctions(<?php echo intval($row->pauc_id); ?>)" style="cursor:pointer;">
                                    <?php echo htmlspecialchars($row->auc_heading); ?>
                                </a>
                                <?php if($row->auc_approval_status == '0'): ?>
                                    <img src="images/waiting_ico1.png" 
                                         title="&lt;b&gt;المزاد قيد المراجعة من قبل النظام&lt;/b&gt;" 
                                         id="imgWaiting" name="imgWaiting" class="imgWaiting" align="absmiddle"
                                         alt="قيد المراجعة">
                                <?php endif; ?>
                            </p>
                            <div style="padding:0 0 5px 0;font-size:11px;color:#727272;">
                                <b>تاريخ الشراء:</b> 
                                <?php echo date("d M, Y", strtotime($row->pauc_purchase_date)); ?>
                            </div>
                            <div class="blvd">
                                <a onclick="detailPurAuctions(<?php echo intval($row->pauc_id); ?>)" 
                                   style="cursor:pointer;">
                                    عرض التفاصيل الكاملة
                                </a>
                            </div>
                            <div class="blvd"></div>
                        </div>
                    </td>
                    <td class="mp5" valign="TOP" width="208">
                        <div class="bulletImage fleft">
                            <a id="apprvDel1" class="apprvDel" style="cursor:pointer;" 
                               onClick="delAuction(<?php echo intval($row->auc_id); ?>,'op');"></a>
                        </div>
                        <div class="bulletImage fleft">
                            <a class="ajax" rel="nofollow" 
                               href="sendAuctionEnquiry-form.php?id=<?php echo rand(1000,9999) . md5($row->auc_usr_id); ?>">
                                إرسل إستفسار
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>

<!-- رسالة عدم وجود نتائج -->
<div style="clear:both;">
    <div class="pbl-sr" style="background-position:0px -35px; min-height:90px;color:#F00;" align="center">
        <br>
        <b style="line-height:50px;">لا توجد مزادات مشتراة حتى الآن</b>
        <br><br>
    </div>
    <div id="switchDiv" style="display:none;"></div>
</div>

<?php endif; ?>

<?php
// إنهاء المخزن المؤقت وإرسال المحتوى
ob_end_flush();
?>