<?php
/**
 * File: search_adv.php

 * Description: صفحة الاتصال بنا - البحث المتقدم عن المنتجات والشركات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "contact_us.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

global $con;

// جلب وحدات القياس
$units = [];
$sql_mu = "SELECT mu_id, mu_name FROM measurement_unit WHERE mu_status = '1' ORDER BY mu_name ASC";
$result_mu = mysqli_query($con, $sql_mu);
while ($row = mysqli_fetch_assoc($result_mu)) {
    $units[] = $row;
}

// جلب الإعلان
$adv_sql = "SELECT adv_link, adv_img FROM advertisement 
            WHERE adv_imagewidth = '728' AND adv_imageheight = '90' 
            AND adv_status = '1' 
            ORDER BY RAND() 
            LIMIT 1";
$adv_result = mysqli_query($con, $adv_sql);
$adv_row = mysqli_fetch_assoc($adv_result);
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link href="css/main-mp-v1.css" rel="stylesheet" type="text/css">
    
    <style>
        .fl { float: left; }
        .tr, .bx1 { text-align: right; }
        .wx1-d { width: 77%; padding: 10px; margin-right: 10px; }
        INPUT, LABEL { padding: 0; margin: 0; }
        h1 { margin: 0 0 15px; padding: 0 0 10px; font-size: 20px; border-bottom: 1px solid #f1f1f1; }
        .bx1, .bx2 { margin-bottom: 15px; }
        .bx1 { width: 180px; font: 400 13px arial; }
        .bx2 { width: 70%; }
        .txtb_ad {
            border: 1px solid #C488FF;
            padding: 7px;
            color: #000;
            width: 270px;
        }
        .txtb_ad:focus {
            outline: none;
            box-shadow: 0 0 6px #D9B3FF;
            -webkit-box-shadow: 0 0 6px #D9B3FF;
            -moz-box-shadow: 0 0 6px #D9B3FF;
        }
        .vm { vertical-align: middle; }
        .wx2-d {
            width: 22%;
            background: #FBF4FF;
            border: 1px solid #ededed;
            margin-top: 45px;
            padding: 10px 15px 0px !important;
        }
        .wx2-d ul { padding: 0; margin: 0 0 0 15px; }
        .wx2-d li {
            font: 400 13px arial;
            list-style-type: circle;
            color: #000;
            padding-bottom: 15px;
        }
        .btn1 {
            background: #DF0000;
            border: 1px solid #DF0000;
            font: 700 16px arial;
            padding: 5px 24px;
            text-decoration: none;
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #DF0000), color-stop(1, #DF0000));
            background: -moz-linear-gradient(center top, #DF0000 5%, #DF0000 100%);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#DF0000');
            color: #000;
            cursor: pointer;
        }
        .btn1:focus, .btn1:hover {
            -moz-box-shadow: 0 0 5px #C2C2C2;
            -webkit-box-shadow: 0 0 5px #C2C2C2;
            box-shadow: 0 0 5px #C2C2C2;
        }
        .ps11 { padding: 0 10px; }
        .cl1 { color: #767676; }
        .stp {
            background: url('../images/search-tips.jpg') no-repeat;
            padding: 3px 0 8px 23px;
            color: #c30000;
        }
        .fz4 { font-size: 14px; }
        .lft1 p, .li1 { line-height: 23px; }
        .p9 { padding-right: 8px; }
        .p3 { padding-top: 5px; }
        .c3 { clear: both; }
        #eto_ofr_ftr_frm h1 { display: none; }
        #eto_ofr_ftr_frm .sh { display: none; }
        .form_area { border: 0 solid #eee !important; }
        #eto_ofr_ftr_frm input, #eto_ofr_ftr_frm textarea, #eto_ofr_ftr_frm select {
            border: 1px solid #aed2f2 !important;
        }
        #eto_ofr_ftr_frm textarea { height: 80px; }
        .lsyp {
            font: 700 12px arial;
            margin-left: 2px;
            padding: 6px 0px 6px 0;
            background: #fff;
        }
        .lsyp b { color: #000099; font-size: 16px; }
        .lsyp strong { color: #990000; font-size: 22px; }
        #q_send_req_button .ssbt { width: 280px; }
        #q_contact_dtl1 table td { vertical-align: top !important; }
        .form_area { float: right; }
        .inx { background: #f8f7ff; padding: 10px; }
        #q_send_req_button .sbtn { border: 0 !important; }
        .p33 { padding-top: 2px; }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    
    <script>
    function pagevalidsearch() {
        var keywords = document.getElementById('pagekeywords');
        var rctyp = document.getElementById('rctyp');
        var adv_quantity = document.getElementById('adv_quantity');
        var adv_qty_list = document.getElementById('adv_qty_list');
        
        if (keywords.value == '' || keywords.value == null) {
            alert("Please enter a valid text to search.");
            return false;
        } else if (rctyp.value != 'Suppliers' && adv_quantity.value != '' && isNaN(adv_quantity.value)) {
            alert("Please enter a valid Quantity.");
            adv_quantity.value = '';
            adv_quantity.focus();
            return false;
        } else if (adv_quantity.value != '' && adv_qty_list.value == '') {
            alert("Please select Measurement Unit.");
            adv_qty_list.focus();
            return false;
        }
        return true;
    }
    
    function quot_on() {
        if (document.getElementById('pagekeywords').value.length > 0) {
            document.getElementById('em1').className = 'tr1 cl1 off';
            document.getElementById('em2').className = 'tr1 on';
        } else {
            document.getElementById('em1').className = 'tr1 cl1 on';
            document.getElementById('em2').className = 'tr1 off';
            document.getElementById('exmch').checked = false;
        }
    }
    
    function intext() {
        document.getElementById('pagekeywords').value = 
            document.getElementById('pagekeywords').value.replace(/"/g, '');
        var x1 = '"';
        var x2 = '"';
        var val = document.getElementById('pagekeywords').value;
        
        if (document.getElementById('exmch').checked == true) {
            document.getElementById('pagekeywords').value = x1 + val + x2;
        }
        if (document.getElementById('exmch').checked == false) {
            document.getElementById('pagekeywords').value = 
                document.getElementById('pagekeywords').value.replace(/"/g, '');
        }
    }
    
    function searchType(rctyp) {
        if (rctyp == 'Suppliers' || rctyp == 'Products' || rctyp == 'tender' || rctyp == 'auction') {
            $("#est_qty").hide();
            $("#est_qty_unit").hide();
        } else {
            $("#est_qty").show();
            $("#est_qty_unit").show();
        }
    }
    
    // تنفيذ quot_on عند تحميل الصفحة
    $(document).ready(function() {
        quot_on();
    });
    </script>
</head>
<body class="search-show-box">
    <div class="hm1 bbc">
        <!-- Header -->
        <?php include __DIR__ . '/includes/header_new.php'; ?>
        
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
        
            <!-- نموذج البحث -->
            <div class="wx1-d fl">
                <h1>البحث المتقدم عن المنتجات والشركات</h1>
                
                <form name="searchForm1" action="search.php" onSubmit="return pagevalidsearch();" method="get" style="display:block" id="aa1">
                    <!-- كلمة البحث -->
                    <p class="bx1 fl p9 p3" title="Enter Keywords:">أدخل كلمة البحث</p>
                    <p class="bx2 fl li1">
                        <input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" 
                               id="pagekeywords" name="keywords" onKeyUp="quot_on();" 
                               class="txtb_ad ui-autocomplete-input" style="width:100%; max-width:600px;"/>
                        <br>
                        <label class="tr1 off" id="em2">
                            <input name="exmatch" class="vm" onClick="intext();" id="exmch" type="checkbox" 
                                   title="Exact Match">
                            ضع الكلمة بالضبط دون زيادة ؟
                        </label>
                        <label class="tr1 cl1 on" id="em1">
                            <input disabled="disabled" name="" class="vm" onClick="" id="exmch_disabled" 
                                   type="checkbox" title="Exact Match">
                            ضع الكلمة بالضبط دون زيادة ؟
                        </label>
                    </p>
                    
                    <!-- نوع البحث -->
                    <p class="bx1 fl p9 p3" title="Looking For:">إختار تصنيف البحث</p>
                    <p class="bx2 fl li1">
                        <select style="width:100%; max-width:300px;" name="rctyp" id="rctyp" 
                                class="txtb_ad" onChange="searchType(this.value);">
                            <option value="Products">منتجات</option>
                            <option value="Suppliers">مورديين</option>
                            <option value="buy_lead">طلبات شراء</option>
                            <option value="tender">مناقصات</option>
                            <option value="auction">مزايدات</option>
                        </select>
                    </p>
                    
                    <!-- الكمية التقديرية (تظهر فقط لطلبات الشراء) -->
                    <p class="bx1 fl p9 p3" id="est_qty" style="display:none">Estimated Quantity:</p>
                    <p class="bx2 fl li1" id="est_qty_unit" style="display:none">
                        <input name="adv_quantity" id="adv_quantity" class="txtb_ad" 
                               style="width:180px; margin-right:7px">
                        <select style="width:130px" name="adv_qty_list" id="adv_qty_list" class="txtb_ad">
                            <option value="">--Select Unit--</option>
                            <?php foreach ($units as $unit): ?>
                            <option style="color:#000;" value="<?php echo (int)$unit['mu_id']; ?>">
                                <?php echo htmlspecialchars($unit['mu_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                    
                    <p><br /></p>
                    <p style="padding-left:190px">
                        <br><br>
                        <input value="" type="hidden">
                        <input value="إبحث" class="btn1" id="btnSearch1" title="Search" name="search" type="submit">
                    </p>
                </form>
            </div>
            
            <!-- نصائح البحث -->
            <div class="wx2-d fl">
                <p class="fz5 bo stp">مفاتيح البحث</p>
                <ul>
                    <li>To search exact phrase in search result, use double quotes around the text, 
                        e.g. <u>"organic milk"</u> by selecting the Exact Match option.</li>
                    <li>For better results, find only one product / service at a time</li>
                    <li>Avoid using very long search text.</li>
                </ul>
            </div>
        </div><!-- inner_wrapper end -->
        
        <p class="c3"><br></p>
        
        <!-- الإعلان السفلي -->
        <div style="text-align:center; margin-bottom:10px;">
            <?php if ($adv_row): ?>
                <a href="//<?php echo htmlspecialchars($adv_row['adv_link'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                    <img src="upload/advertisement/<?php echo htmlspecialchars($adv_row['adv_img'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                         width="728" height="90" id="advertisement_banner_img" alt="Advertisement">
                </a>
            <?php else: ?>
                <img src="upload/advertisement/239-186-advertisement.png" width="728" height="90" 
                     id="advertisement_banner_img" alt="Advertisement">
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>