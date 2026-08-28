<?php
require_once 'common.php';
// تعريف المتغيرات الافتراضية لتجنب تحذيرات PHP 8.3
$suppsetLimit = ""; 
$cntryval1 = "";
$sql_pd_ck = "";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// حساب الـ Limit بناءً على رقم الصفحة
$limit_start = ($page - 1) * 50;
$suppsetLimit = " LIMIT $limit_start, 50 ";

// التحقق من الاتصال بقاعدة البيانات
if (!isset($con) && isset($GLOBALS['con'])) {
    $con = $GLOBALS['con'];
}

$uid = '';
if (isset($_SESSION['uid_indm'])) {
    $uid = (int)$_SESSION['uid_indm'];
}

$location = array();
if (function_exists('getLocationInfoByIp')) {
    $location = getLocationInfoByIp();
}

// =============================================
// دوال البحث المحسنة لـ PHP 8.3
// =============================================

function generateProdSearchString($keywords) {
    global $con;
    $keywords = trim($keywords);
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", $keywords);
    foreach ($key_array as $v) {
        if ($i > 0) { $keywords_string .= " AND pd_title LIKE "; }
        $keywords_string .= "'%" . mysqli_real_escape_string($con, $v) . "%'";
        $i++;
    }
    return $keywords_string;
}

function generateProdSearchString_pro_sup($keywords) {
    global $con;
    $keywords = trim($keywords);
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", $keywords);
    foreach ($key_array as $v) {
        if ($i > 0) { $keywords_string .= " AND bnsprof_compname LIKE "; }
        $keywords_string .= "'%" . mysqli_real_escape_string($con, $v) . "%'";
        $i++;
    }
    return $keywords_string;
}

function generateSupplierSearchString($keywords) {
    global $con;
    $keywords = trim($keywords);
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", $keywords);
    foreach ($key_array as $v) {
        if ($i > 0) { $keywords_string .= " OR bnsprof_compname LIKE "; }
        $keywords_string .= "'%" . mysqli_real_escape_string($con, $v) . "%'";
        $i++;
    }
    return $keywords_string;
}

function generateBuyleadSearchString($keywords) {
    global $con;
    $keywords = trim($keywords);
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", $keywords);
    foreach ($key_array as $v) {
        $safe_v = mysqli_real_escape_string($con, $v);
        if ($i > 0) {
            $keywords_string .= " OR br_pd_name LIKE '%" . $safe_v . "%' OR br_requirement LIKE '%" . $safe_v . "%'";
        } else {
            $keywords_string .= "br_pd_name LIKE '%" . $safe_v . "%' OR br_requirement LIKE '%" . $safe_v . "%'";
        }
        $i++;
    }
    return $keywords_string;
}

// ... يمكنك إضافة بقية الدوال (Tender, Auction) بنفس نمط mysqli_real_escape_string($con, $v)
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/ctstyle.css?t=<?php echo rand(); ?>" rel="stylesheet" type="text/css">
<link href="css/im-style-v1.css" rel="stylesheet" type="text/css">
<script src="js/jquery-1.11.1.min.js"></script>

<script type="text/javascript">
function loadProductByCategory(page,id) { $.post("ajax-file/loadProductByCategory.php",{page:page,id:id}, function(data){ $('#res').html(data); }); }
function loadProductBySubCategory(page,id) { $.post("ajax-file/loadProductBySubCategory.php",{page:page,id:id}, function(data){ $('#res').html(data); }); }
</script>

<style>
/* التنسيقات الأصلية لضمان الشكل */
.seach-page-inn { direction: ltr; text-align: left; }
.my-container { width:100% !important; border: 1px solid #c2e6fe; padding: 14px; background: #fff; margin-bottom:15px; display:inline-block; }
.cat_image_div { height: 100px; width: 100%; border: 1px solid #c3bdbd !important; overflow:hidden; }
.cat_image { width:100%; height:100%; object-fit: contain; }
.zoom-box:hover { transform: scale(1.05); transition: 0.3s; }
</style>
</head>
<body id="search_result_page">
<div id="fb-root"></div>

<?php 
    include("./includes/header_new.php");
?>

// =============================================
// تعريف المتغيرات الافتراضية (إصلاح تحذيرات PHP 8.3)
// =============================================
$suppsetLimit = ""; 
$cntryval1 = "";
$sql_pd_ck = "";
$newkw = isset($_GET['keywords']) ? trim($_GET['keywords']) : "";
$page = (isset($_GET['page']) && (int)$_GET['page'] > 0) ? (int)$_GET['page'] : 1;

// حساب الـ Limit للنتائج (مثلاً 50 نتيجة لكل صفحة)
$rowsPerPage = 50;
$offset = ($page - 1) * $rowsPerPage;
$suppsetLimit = " LIMIT $offset, $rowsPerPage ";

// =============================================

<div class="seach-page-inn">
<div class="hm1 bbc search-wrap">
    <div id="res" class="lft fl">
        <link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
        <?php include_once 'index_leftsidebar.php'; ?>
        
        <?php if(!isset($_GET['rctyp'])) echo "</div>"; ?>
    </div>
    
    <div class="lft fl ser-mid">
        
       
    <?php 
    // 1. تحديد نوع البحث المطلوب من الرابط
    $current_type = isset($_GET['rctyp']) ? $_GET['rctyp'] : 'Products';

    // 2. منطق العرض بناءً على النوع (إصلاح تداخل catcompany)
    if ($current_type == 'Suppliers' || $current_type == 'Companies') {
        // إذا كان ملف catcompany.php موجوداً، استدعه هنا
        if (file_exists('catcompany.php')) {
            include 'catcompany.php';
        } else {
            echo "جاري تحميل بيانات الموردين...";
        }
    } else {
        // العرض الافتراضي للمنتجات والموردين معاً
        if (file_exists('index_md_search.php')) {
            
       echo "<!-- سيتم تضمين index_md_search.php -->";
     
            include 'index_md_search.php';

echo "<!-- تم تنفيذ include index_md_search.php -->";
        
            
            
            
        } else {
            // إذا لم يكن هناك ملف خارجي، تأكد من وجود كود الـ SQL والـ Loop هنا
            echo "";
        }
    }
    ?>
</div>
        
        
        
        <?php if(isset($_GET['rctyp']) && $_GET['rctyp'] == 'Suppliers'){ ?>
        <link type="text/css" rel="stylesheet" href="css/main-v2.css">
        <link href="css/dir-style-8.css" type="text/css" rel="stylesheet">
        <link href="css/overlay-v2.css" type="text/css" rel="stylesheet">
        <link href="css/bl_form_temp5.css" rel="stylesheet" type="text/css">
        
        <script src="js/jquery.colorbox.js"></script>
        <link href="css/colorbox.css" type="text/css" rel="stylesheet">
        <script>
            $(document).on('click', '.ajax', function () {
                $.colorbox({
                    href: $(this).attr('href'),
                    open: true,
                    iframe: true,
                    width: '750px',
                    height: '600px'
                });
                return false;
            });
            $(document).ready(function(){
                $(".inline").colorbox({inline:true, width:"50%"});
                $("#click").click(function(){ 
                    $('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
                    return false;
                });
            });
        </script>
        
        <style>
        .zoom-box .viewer-box { z-index: 1000000;}
        .g9 { font-size: 13px; background: white; padding: 17px;}
        #supplierid img{border: 1px solid #000;}
        #supplierid td{ width:100px; max-width:100px; word-wrap:break-word;}
        
        @media only screen and (min-device-width : 320px) and (max-device-width : 480px) {
            .my-container{ width:100% !important; border: 1px solid #c2e6fe !important; padding: 14px !important; background: #fff !important;}
            .col-md-2{ padding-left: 7px!important; padding-right: 7px!important;}
        }
        
        @media only screen and (min-width : 1224px) {
            .my-container{ width:850px !important; border: 1px solid #c2e6fe !important; padding: 14px !important; background: #fff !important; margin-left: auto; margin-right: auto;}
            .middle-part { margin-left: auto; margin-right: auto;}
        }
        
        @media only screen and (min-width : 1824px) {
            .my-container{ width:1000px !important; border: 1px solid #c2e6fe !important; padding: 14px!important; background: #fff !important;}
        }
        
        .cat_image_div{ height: 100px; width: 100%; border: 1px solid #c3bdbd !important;}
        .three-pics1 .col-md-2 { padding: 0px 5px;}
        .cat_image{ width:100%; height:100%; object-fit: contain;}
        
        @media only screen and (max-width: 768px) {
            .cat_image{ width:100% !important; height:100% !important; object-fit: contain;}
            .cat_image_div{border: 1px solid #cac6c6; height: 80px !important; width:80px !important;}
            .three-pics1, .col-md-2{ width:100px !important; padding:0px !important}
        }
        
        .page-header-col2-intro-texts .post-product-btn { font-size: 14px !important;}
        .page-header-col2-intro-texts .post-product-btn small { font-size: 10px !important;}
        .ps-15{ padding:15px!important;}
        
        @media(max-width:1024px){
            #changeLocation { top: -5px !important;}
            .postRequirement .girl-img{left: -132px !important;z-index: 99999;}
            .page-header-col1-row1-col1_row{right: -24px;}
            .scontentbox h2 {white-space: nowrap;}
        }
        </style>

        <?php
        // =============================================
        // تعريف شروط الموقع لصفحة الموردين
        // =============================================
        $sql_pd_ck = "";
        if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
            $loc_id = (int)$_COOKIE['loc_id'];
            $sql_pd_ck = " AND (
                (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}')) 
                OR 
                (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
                OR
                (pd_preferred_buyer_location='my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile bf JOIN city c ON bf.bnsprof_city = c.ct_id WHERE c.ct_cn_id='{$loc_id}'))
            )";
        } else {
            $location_geo_country = isset($location_geo_country) ? mysqli_real_escape_string($con, $location_geo_country) : '';
            $sql_pd_ck = " AND (
                (pd_preferred_buyer_location='any')
                OR
                (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country=(SELECT cn_id FROM country WHERE cn_code='{$location_geo_country}')))
            )";
        }
        
        // إعدادات الصفحة
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $newkw = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
        $supptotalpage = 50;
        $suppstartpage = 0;
        
        $supplimit = ($page > 1) ? ($page - 1) * $supptotalpage : $suppstartpage;
        $suppsetLimit = " LIMIT " . (int)$supplimit . "," . (int)$supptotalpage;
        
        // معالجة تصفية الدول
        $cntryval1 = "";
        if (isset($_POST['country_id']) && is_array($_POST['country_id'])) {
            $country_names = array_map(function($val) use ($con) {
                return "'" . mysqli_real_escape_string($con, $val) . "'";
            }, $_POST['country_id']);
            
            if (!empty($country_names)) {
                $cntryval1 = " AND country.cn_name IN (" . implode(",", $country_names) . ")";
            }
        }
        ?>
        <?php } // إغلاق شرط Suppliers ?>
    </div>
</div>
</div>
<?php
// جاهز لاستقبال الجزء الثالث (استعلام قاعدة البيانات وعرض النتائج)
?>



<?php
// =============================================
// الجزء الثالث - معالجة البيانات وعرض النتائج
// =============================================

$newkw = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
$sql_comp = "";

// 1. بناء الاستعلام بناءً على التصفية المختارة
if (isset($_POST['country_id']) && is_array($_POST['country_id'])) {
    $sql_comp = "SELECT * FROM business_profile bf 
                 JOIN products p ON bf.bnsprof_uid = p.pd_uid 
                 JOIN product_category_arabyos pc ON p.pd_subcat_id = pc.pc_id 
                 INNER JOIN user ON user.usr_id = p.pd_uid 
                 INNER JOIN country ON user.country = country.cn_id 
                 INNER JOIN city ON bf.bnsprof_city = city.ct_id 
                 JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
                 WHERE ((bf.bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $newkw) . "%') 
                 OR (p.pd_title LIKE '%" . mysqli_real_escape_string($con, $newkw) . "%')) 
                 " . $cntryval1 . " 
                 AND pm.expiry_date > " . time() . " 
                 AND pc.pc_status = '1' AND p.pd_status = '1' 
                 GROUP BY p.pd_uid 
                 ORDER BY pm.icon_id ASC " . $suppsetLimit;

} elseif (isset($_POST['state_id']) && !empty($_POST['state_id'])) {
    $state_ids = array_map('intval', (array)$_POST['state_id']);
    $stateid_list = implode(',', $state_ids);
    
    $sql_comp = "SELECT * FROM business_profile bf 
                 JOIN products p ON bf.bnsprof_uid = p.pd_uid 
                 JOIN product_category_arabyos pc ON p.pd_subcat_id = pc.pc_id 
                 INNER JOIN user ON user.usr_id = p.pd_uid 
                 INNER JOIN country ON user.country = country.cn_id 
                 INNER JOIN city ON bf.bnsprof_city = city.ct_id 
                 JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
                 WHERE ((bf.bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $newkw) . "%') 
                 OR (p.pd_title LIKE '%" . mysqli_real_escape_string($con, $newkw) . "%'))  
                 AND bf.bnsprof_state IN (" . $stateid_list . ")  
                 AND pm.expiry_date > " . time() . " 
                 AND pc.pc_status = '1' AND p.pd_status = '1' 
                 GROUP BY p.pd_uid 
                 ORDER BY pm.icon_id ASC " . $suppsetLimit;
} else {
    $sql_comp = "SELECT * FROM business_profile bf 
                 JOIN products p ON bf.bnsprof_uid = p.pd_uid 
                 JOIN product_category_arabyos pc ON p.pd_subcat_id = pc.pc_id 
                 JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
                 WHERE ((bf.bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $newkw) . "%') 
                 OR (p.pd_title LIKE '%" . mysqli_real_escape_string($con, $newkw) . "%')) 
                 AND pm.expiry_date > " . time() . " 
                 AND pc.pc_status = '1' " . ($sql_pd_ck ?? "") . " 
                 AND p.pd_status = '1' 
                 GROUP BY p.pd_uid 
                 ORDER BY pm.icon_id ASC " . $suppsetLimit;
}

$res_comp = mysqli_query($con, $sql_comp);
$getSearchCount = ($res_comp) ? mysqli_num_rows($res_comp) : 0;

if ($getSearchCount > 0) {
    // جلب معلومات التصنيف للزر العلوي
    mysqli_data_seek($res_comp, 0); 
    $row_s_cat = mysqli_fetch_object($res_comp);
    $category_p_id = $row_s_cat->pd_subcat_id;
    
    $parent_sql = "SELECT pc_parent_id FROM `product_category_arabyos` WHERE pc_id = '" . (int)$category_p_id . "'";
    $parent_res = mysqli_query($con, $parent_sql);
    $parent_row = mysqli_fetch_object($parent_res);
    $final_cat_id = $parent_row ? $parent_row->pc_parent_id : 0;

    echo '<div class="middle-part" style="margin-left:23px; margin-bottom:15px;">';
    echo '<button class="btn btn-warning border-radius-0" style="font-weight:700;">موردون</button>';
    echo '<a href="products.php?c='.md5((string)$final_cat_id).'" class="btn btn-default border-radius-0" style="background:#F5F7FA; font-weight:700;" target="_blank">منتجات</a>';
    echo '</div>';

    mysqli_data_seek($res_comp, 0); // العودة لبداية النتائج للبحث في الـ loop
    while ($row_comp = mysqli_fetch_object($res_comp)) {
        // جلب الدولة
        $iCountrySql = mysqli_query($con, "SELECT cn_name FROM country WHERE cn_id = '" . (int)user_info($row_comp->bnsprof_uid, 'country') . "'");
        $countryObj = mysqli_fetch_object($iCountrySql);
        
        // جلب الأيقونة والخطة
        $planSql = mysqli_query($con, "SELECT * FROM plan_member_id pm JOIN smembership_icon_plan si ON pm.icon_id = si.mp_id WHERE pm.b_id = '" . (int)$row_comp->bnsprof_id . "'");
        $planInfo = mysqli_fetch_object($planSql);
    ?>
    <div class="my-container zoom-box">
        <div class="row">
            <div class="col-md-1" style="width:50px">
                <?php if ($planInfo && !empty($planInfo->mst_icon)): ?>
                    <img src="admin/images/<?php echo htmlspecialchars($planInfo->mst_icon); ?>" title="<?php echo htmlspecialchars($planInfo->mst_name); ?>" width="30">
                <?php endif; ?>
            </div>
            <div class="col-md-11">
                <a href="company/profile.php?c=<?php echo rand(1000, 9999) . md5((string)$row_comp->bnsprof_id); ?>" target="_blank">
                    <h4 style="font-weight:900; color:#333; margin:0;"><?php echo str_ireplace($newkw, "<span style='color:orange'>$newkw</span>", stripslashes($row_comp->bnsprof_compname)); ?></h4>
                </a>
                <small><?php echo htmlspecialchars($countryObj->cn_name ?? ''); ?>, <?php echo htmlspecialchars(get_city_name($row_comp->bnsprof_city)); ?></small>
            </div>
        </div>

        <div class="row" style="margin-top:15px;">
            <div class="col-md-4">
                <div class="cat_image_div">
                    <?php 
                    $img_path = '/upload/myproduct/' . $row_comp->pd_image;
                    if (!empty($row_comp->pd_image) && file_exists($_SERVER['DOCUMENT_ROOT'] . $img_path)): ?>
                        <img src="<?php echo $img_path; ?>" class="cat_image" alt="Product">
                    <?php else: ?>
                        <img src="/images/no-image.jpg" class="cat_image">
                    <?php endif; ?>
                </div>
                <div style="font-size:11px; padding-top:5px;"><?php echo mb_substr(stripslashes($row_comp->pd_title), 0, 40); ?></div>
            </div>

            <div class="col-md-8">
                <p style="font-size:13px; color:#666;">
                    <strong>Main Business:</strong> 
                    <?php 
                    $cat_list = [];
                    $sub_q = mysqli_query($con, "SELECT pc_name FROM products p JOIN product_category_arabyos pc ON p.pd_subcat_id = pc.pc_id WHERE p.pd_uid = '".(int)$row_comp->bnsprof_uid."' LIMIT 3");
                    while($sq = mysqli_fetch_assoc($sub_q)) $cat_list[] = $sq['pc_name'];
                    echo htmlspecialchars(implode(', ', $cat_list));
                    ?>
                </p>
                <div class="contact-actions" style="margin-top:10px;">
                    <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$row_comp->bnsprof_id); ?>" class="btn btn-sm btn-info">شاهد المنتجات</a>
                    <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != $row_comp->bnsprof_uid): ?>
                        <a href="sendenquiry-form.php?id=<?php echo rand(1000, 9999) . md5((string)$row_comp->bnsprof_id); ?>" class="btn btn-sm btn-warning ajax">إرسل إستفسارك</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php } // End While
} else { ?>
    <div class="alert alert-info">عذراً، لم نجد نتائج لـ "<?php echo htmlspecialchars($newkw); ?>". جرب كلمات بحث أخرى.</div>
<?php } 

// =============================================
// زر عرض المزيد (Load More)
// =============================================
if (!empty($newkw)) {
    // إعادة حساب العدد الإجمالي بدون Limit
    $total_res = mysqli_query($con, str_replace($suppsetLimit, "", $sql_comp));
    $total_count = mysqli_num_rows($total_res);
    
    if (($page * 50) < $total_count) {
        $next_page = $page + 1;
        echo '<div class="text-center" style="padding:20px;">
                <a href="suppliers_search.php?rctyp=Suppliers&keywords='.urlencode($newkw).'&page='.$next_page.'" class="btn btn-warning">عرض المزيد من النتائج</a>
              </div>';
    }
}
?>

<div class="als-container" id="product_slider" style="height:50px;">&nbsp;</div>
</div> <?php if (isset($_GET['rctyp']) && $_GET['rctyp'] == 'Suppliers') { echo '</div>'; } ?>

</body>
</html>

<?php include_once 'index_rightsidebar.php'; ?>
        <div class="clearfix"></div>
    </div> </div> <p class="cb"><br></p>

<?php if (isset($_GET['rctyp'])): ?>
    <script type="text/javascript">
        /* $(window).on("load", function() {
            <?php if ($_GET['rctyp'] == 'Products'): ?>
                // window.open('https://www.egyptmart.shop/manage-selloffer-alert.php', 'newwindow', 'width=700,height=400'); 
            <?php elseif ($_GET['rctyp'] == 'buy_lead'): ?>
                // window.open('https://www.egyptmart.shop/manage-buylead-alert.php', 'newwindow', 'width=700,height=400');
            <?php elseif ($_GET['rctyp'] == 'tender'): ?>
                // window.open('https://www.egyptmart.shop/manage-tender-alert.php', 'newwindow', 'width=700,height=400');
            <?php endif; ?>
        });
        */
    </script>
<?php else: ?>
    <script type="text/javascript">
        $(window).on("load", function() {
            if ($(window).width() > 768) {
                // منطق إضافي للشاشات الكبيرة إذا لزم الأمر
            }
        });
    </script>
<?php endif; ?>

<?php 
include "includes/footer.php"; ?>

<link rel="stylesheet" href="css/jquery.jqZoom.css?v=4.4" type="text/css"/>
<script src="js/jquery.jqZoom.js?v=4.1"></script>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        // تأكد من وجود العنصر قبل استدعاء الوظيفة لتجنب أخطاء الكونسول
        if ($(".zoom-box img").length > 0) {
            $(".zoom-box img").jqZoom({
                selectorWidth: 30,
                selectorHeight: 30,
                viewerWidth: 400,
                viewerHeight: 300
            });
        }
    });
</script>

</body>
</html>

