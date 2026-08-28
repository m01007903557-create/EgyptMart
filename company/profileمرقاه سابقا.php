<?php
// company/profile.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "includes/header.php";

// جلب محتوى الصفحة الرئيسية للشركة
$sql_wc = "SELECT * FROM website_content WHERE wc_usr_id = '" . (int)($row->usr_id ?? 0) . "'";
$res_wc = mysqli_query($con, $sql_wc);
$row_wc = mysqli_fetch_object($res_wc);

// جلب معلومات إضافية عن الشركة
$bfact = '';
$exta = $row->bnsprof_yoe ?? '';
$noe = '';
$lesta = '';
$tern = '';
$regno = $row->bnsprof_regno ?? '';
$serTax = $row->bnsprof_svtax_no ?? '';

$user_turnover_id = (int)user_info($uid ?? 0, 'bnsprof_turnover');
$user_owntype_id = (int)user_info($uid ?? 0, 'bnsprof_owntype');

// جلب معلومات حجم الأعمال
$turnoversql = mysqli_query($con, "SELECT revturnover_title FROM revenue_turnover 
                                   WHERE revturnover_status = '1' AND revturnover_id = {$user_turnover_id}");
if ($turnoversql) {
    $turnoverow = mysqli_fetch_object($turnoversql);
    $tern = $turnoverow->revturnover_title ?? '';
}

// جلب معلومات الملكية
$owntypesql = mysqli_query($con, "SELECT * FROM ownership_type 
                                  WHERE owntyp_status = '1' AND owntyp_id = {$user_owntype_id}");
if ($owntypesql) {
    $owntyperow = mysqli_fetch_object($owntypesql);
    $lesta = $owntyperow->owntyp_title ?? '';
}

// جلب بيانات الشركة
$sql = "SELECT * FROM business_profile, user, ownership_type, revenue_turnover 
        WHERE bnsprof_uid = usr_id 
        AND bnsprof_owntype = owntyp_id 
        AND bnsprof_turnover = revturnover_id 
        AND md5(bnsprof_id) = '" . mysqli_real_escape_string($con, $id ?? '') . "'";
$res = mysqli_query($con, $sql);
$row = mysqli_fetch_object($res);

// جلب أنواع الأعمال
$arr_bfact = [];
$bnsprof_businesstype = isset($row->bnsprof_businesstype) ? explode(",", $row->bnsprof_businesstype) : [];

if (!empty($bnsprof_businesstype)) {
    $sql_btype = "SELECT * FROM business_type_arabyos 
                  WHERE bsntyp_id IN (" . implode(',', array_map('intval', $bnsprof_businesstype)) . ")";
    $res_btype = mysqli_query($con, $sql_btype);
    
    while ($row_btype = mysqli_fetch_object($res_btype)) {
        $arr_bfact[] = $row_btype->bsntyp_title;
    }
}

// جلب معلومات عدد الموظفين
$noempsql = mysqli_query($con, "SELECT * FROM employee_range 
                                WHERE emprange_status = '1' AND emprange_id = '" . (int)($row->bnsprof_comemp ?? 0) . "'");
$noemprow = mysqli_fetch_object($noempsql);
$noe = $noemprow->emprange_type ?? '';
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <style>
    .info_table_right img {
        border: 1px solid #bac3fc;
        max-width: 100%;
        height: auto;
    }
    </style>
</head>
<body>
    <div id="body">
        <ul class="cb">
            <li id="wideColumn">
                <?php if (!empty($row_wc->wc_homepage_key_desc)): ?>
                    <section class="box1">
                        <div class="h2"><h2>وصف الشركة</h2></div>
                        <nav class="comPro">
                            <p><?php echo nl2br(htmlspecialchars($row_wc->wc_homepage_key_desc)); ?></p>
                        </nav>
                    </section>
                <?php endif; ?>
                <br>

                <?php if (!empty($arr_bfact) || !empty($exta) || !empty($noe) || !empty($lesta) || !empty($tern) || !empty($regno) || !empty($serTax)): ?>
                    <section class="box1">
                        <div class="h2"><h2>معلومات الشركة</h2></div>
                        <nav class="comFact">
                            <?php if (!empty($arr_bfact)): ?>
                                <p>
                                    <span>نوع النشاط التجاري</span>
                                    <span><?php echo htmlspecialchars(implode("، ", $arr_bfact)); ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($exta)): ?>
                                <p>
                                    <span>سنة التأسيس</span>
                                    <span><?php echo htmlspecialchars($exta); ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($noe)): ?>
                                <p>
                                    <span>عدد الموظفين</span>
                                    <span><?php echo htmlspecialchars($noe); ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($lesta)): ?>
                                <p>
                                    <span>الوضع القانوني</span>
                                    <span><?php echo htmlspecialchars($lesta); ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($tern)): ?>
                                <p>
                                    <span>حجم الأعمال</span>
                                    <span><?php echo htmlspecialchars($tern); ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($row->bnsprof_designation)): ?>
                                <?php
                                $sql_desig = mysqli_query($con, "SELECT * FROM designation 
                                                                 WHERE desig_id = '" . (int)$row->bnsprof_designation . "'");
                                if ($sql_desig && mysqli_num_rows($sql_desig) > 0):
                                    $row_desig = mysqli_fetch_object($sql_desig);
                                ?>
                                    <p>
                                        <span><?php echo htmlspecialchars($row_desig->desig_title ?? ''); ?></span>
                                        <span><?php echo htmlspecialchars(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? '')); ?></span>
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($regno)): ?>
                                <p>
                                    <span>رقم السجل التجاري</span>
                                    <span><?php echo htmlspecialchars($regno); ?></span>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($serTax)): ?>
                                <p>
                                    <span>رقم ضريبة الخدمات</span>
                                    <span><?php echo htmlspecialchars($serTax); ?></span>
                                </p>
                            <?php endif; ?>
                        </nav>
                    </section>
                <?php endif; ?>
                
                <br>
                
                <?php
                // جلب أقسام "عن الشركة"
                $abtsql = mysqli_query($con, "SELECT * FROM about_us, profile_heading_arabyos 
                                              WHERE abtus_ph_id = ph_id 
                                              AND abtus_wc_id = '" . (int)($row_wc->wc_id ?? 0) . "' 
                                              ORDER BY abtus_order");
                $totalabt = mysqli_num_rows($abtsql);
                
                if ($totalabt > 0):
                    while ($abtrow = mysqli_fetch_object($abtsql)):
                ?>
                        <section class="box1">
                            <div class="h2"><h2><?php echo htmlspecialchars($abtrow->ph_title ?? ''); ?></h2></div>
                            <div class="info_table">
                                <div class="info_table_left">
                                    <?php echo nl2br(htmlspecialchars($abtrow->abtus_desc ?? '')); ?>
                                </div>
                                <div class="info_table_right" style="border:none;">
                                    <?php if (!empty($abtrow->abtus_image)): ?>
                                        <img src="https://egyptmart.shop/upload/myprofile/<?php echo htmlspecialchars($abtrow->abtus_image); ?>" 
                                             id="img_small_form_1671511" 
                                             alt="<?php echo htmlspecialchars($abtrow->ph_title ?? ''); ?>">
                                    <?php else: ?>
                                        <img src="https://egyptmart.shop/images/noimage.jpg" 
                                             id="img_small_form_1671511" 
                                             alt="لا توجد صورة">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>
                        <br>
                <?php
                    endwhile;
                endif;
                ?>
            </li>
            
            <?php include "includes/right.php"; ?>
        </ul>
    </div>
    
    <?php include "includes/footer.php"; ?>
</body>
</html>