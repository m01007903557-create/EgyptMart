<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

 function generateSearchString(string $keywords, mysqli $con): string {
    // هذا الافتراض أن الدالة تؤدي نفس وظيفة generateTenderSearchString
    // يمكنك استخدام نفس الكود الموجود داخل generateTenderSearchString هنا
    // أو إذا كنت تعرف أن generateTenderSearchString تعمل بشكل صحيح، يمكنك استدعاءها:
    return generateTenderSearchString($keywords, $con);
} 
        
/*function generateTenderSearchString(string $keywords, mysqli $con): string {
    $keywords = trim($keywords);
    if (empty($keywords)) return '';
    
    $key_array = array_filter(explode(' ', $keywords));
    $conditions = [];
    foreach ($key_array as $term) {
        $escaped_term = mysqli_real_escape_string($con, $term);
        $conditions[] = "(tnd_heading LIKE '%{$escaped_term}%' OR tnd_details LIKE '%{$escaped_term}%')";
    }
    return implode(' OR ', $conditions);
}
session_cache_limiter('private_no_expire');
require_once 'common.php';

// تحديد موقع المستخدم الجغرافي
$location_geo_country = [];

// محاولة جلب الموقع من IP
if (function_exists('getLocationInfoByIp1')) {
    $location_info = getLocationInfoByIp1();
    if (is_array($location_info) && !empty($location_info)) {
        $location_geo_country = $location_info;
    }
}

// إذا لم يتم العثور على موقع، استخدم قيمة افتراضية (مثلاً مصر)
if (empty($location_geo_country) || !is_array($location_geo_country)) {
    // استعلام لجلب id مصر من قاعدة البيانات
    $country_query = mysqli_query($link, "SELECT cn_id FROM country WHERE cn_code = 'EG' OR cn_name = 'Egypt' LIMIT 1");
    if ($country_query && mysqli_num_rows($country_query) > 0) {
        $country_row = mysqli_fetch_assoc($country_query);
        $location_geo_country = [$country_row['cn_id']];
    } else {
        // قيمة افتراضية إذا فشل الاستعلام
        $location_geo_country = [65]; // 65 هو id محتمل لمصر
    }
}
*/


$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

// دوال البحث (مطابقة لنسخة index_md_search.php)
/*function generateProdSearchString(string $keywords, mysqli $con): string {
    $keywords = trim($keywords);
    if (empty($keywords)) return '';
    
    $key_array = array_filter(explode(' ', $keywords));
    $conditions = [];
    foreach ($key_array as $term) {
        $conditions[] = "pd_title LIKE '%" . mysqli_real_escape_string($con, $term) . "%'";
    }
    return implode(' AND ', $conditions);
}*/

/*function generateProdSearchString_pro_sup(string $keywords, mysqli $con): string {
    $keywords = trim($keywords);
    if (empty($keywords)) return '';
    
    $key_array = array_filter(explode(' ', $keywords));
    $conditions = [];
    foreach ($key_array as $term) {
        $conditions[] = "bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $term) . "%'";
    }
    return implode(' AND ', $conditions);
}*/

/*function generateSupplierSearchString(string $keywords, mysqli $con): string {
    $keywords = trim($keywords);
    if (empty($keywords)) return '';
    
    $key_array = array_filter(explode(' ', $keywords));
    $conditions = [];
    foreach ($key_array as $term) {
        $conditions[] = "bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $term) . "%'";
    }
    return implode(' OR ', $conditions);
}*/

/*function generateBuyleadSearchString(string $keywords, mysqli $con): string {
    $keywords = trim($keywords);
    if (empty($keywords)) return '';
    
    $key_array = array_filter(explode(' ', $keywords));
    $conditions = [];
    foreach ($key_array as $term) {
        $escaped_term = mysqli_real_escape_string($con, $term);
        $conditions[] = "(br_pd_name LIKE '%{$escaped_term}%' OR br_requirement LIKE '%{$escaped_term}%')";
    }
    return implode(' OR ', $conditions);
}*/



/*function generateAuctionSearchString(string $keywords, mysqli $con): string {
    $keywords = trim($keywords);
    if (empty($keywords)) return '';
    
    $key_array = array_filter(explode(' ', $keywords));
    $conditions = [];
    foreach ($key_array as $term) {
        $escaped_term = mysqli_real_escape_string($con, $term);
        $conditions[] = "(auc_heading LIKE '%{$escaped_term}%' OR auc_details LIKE '%{$escaped_term}%')";
    }
    return implode(' OR ', $conditions);
}*/

/*function highlight(string $content, string $word): string {
    $word = str_replace(['+', '%20'], ' ', $word);
    if (empty($word) || empty($content)) return $content;
    
    $pattern = '/' . preg_quote($word, '/') . '/i';
    $replacement = '<span style="color: #f26a22;">$0</span>';
    return preg_replace($pattern, $replacement, $content);
}*/

// دالة تنفيذ استعلام مع Prepared Statement
/*function executeQuery(mysqli $con, string $sql, array $params = []): ?mysqli_result {
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($con));
        return null;
    }// دالة تنفيذ استعلام مع Prepared Statement
/*function executeQuery(mysqli $con, string $sql, array $params = []): ?mysqli_result {
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        error_log("Prepare failed: " . mysqli_error($con));
        return null;
    }
    
    if (!empty($params)) {
        $types = '';
        $bindParams = [];
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
            $bindParams[] = $param;
        }
        mysqli_stmt_bind_param($stmt, $types, ...$bindParams);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return null;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}*/

// التأكد من وجود الدالة قبل استخدامها
if (!function_exists('getLocationInfoByIp1')) {
    require_once __DIR__ . '/common.php';
}
$location = getLocationInfoByIp1();

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/ctstyle.css?t=<?php echo rand(); ?>" rel="stylesheet" type="text/css">
    
    <script src="js/jquery-1.11.1.min.js"></script>
    
    <style>
        .prdt-sup-ctrl span{ font-size: 11px!important;}
        .text-right button.btn.btn-default.btn-xs { padding: 1px 4px!important;}
        
        #search_result .big-img-box .zoomthis img,
        .seach-page-inn .big-img-box .zoomthis img {
            height: auto !important;
            max-height:253px;
        }
        .box .zoomthis {
            position: relative;
            top: 50%;
            left: 50%;
            width: 100%;
            transform: translate(-50%,-50%);
        }
        .inner-search-right-img {
            position: absolute;
            bottom: 5px;
            right: 5px;
        }
        .wrapper-product-searchright { position: relative; }
        .seach-page-inn .box { height: 253px; }
        #search_result figure.box { width: 100%; }
        .txt-dark-gray a { color: #302670 !important; }
        .ar-box-1 .sub-box+img+b { color: gray !important; }
        
        @media screen and (max-width: 1024px) and (min-width: 990px) {
            .ar-box-1 .box-1 .box { float: none !important; }
        }
        
        /* ALS Slider Styles */
        .als-container {
            position: relative;
            width: 100%;
            margin: 0px auto;
            z-index: 0;
        }
        .als-viewport {
            position: relative;
            overflow: hidden;
            margin: 0px auto;
        }
        .als-wrapper {
            position: relative;
            list-style: none;
        }
        .als-item {
            position: relative;
            display: block;
            text-align: center;
            cursor: pointer;
            float: left;
        }
        .als-prev, .als-next {
            position: absolute;
            cursor: pointer;
            clear: both;
        }
        
        #product_slider { margin: 2px auto; }
        #product_slider .als-item {
            margin: 0px 5px;
            padding: 4px 0px;
            min-height: 140px;
            min-width: 122px;
            text-align: center;
        }
        #product_slider .als-item img {
            display: block;
            margin: 0 auto;
            vertical-align: middle;
        }
        #product_slider .als-prev, #product_slider .als-next { top: 60px; }
        #product_slider .als-prev { left: 20px; }
        #product_slider .als-next { right: 20px; }
        
        #saleoffer_slider { margin: 2px auto; }
        #saleoffer_slider .als-item {
            margin: 0px 5px;
            padding: 4px 0px;
            min-height: 140px;
            min-width: 120px;
            text-align: center;
        }
        #saleoffer_slider .als-item img {
            display: block;
            margin: 0 auto;
            vertical-align: middle;
        }
        #saleoffer_slider .als-prev, #saleoffer_slider .als-next { top: 60px; }
        #saleoffer_slider .als-prev { left: 20px; }
        #saleoffer_slider .als-next { right: 20px; }
        
        .goog-te-gadget-simple .goog-te-menu-value { font-size: 10px; }
        .bbc { width: 1260px; margin: 0 auto; }
        
        @media(max-width:1024px) {
            .right-section-search-buylead { position: relative !important; }
            .photo { position: static !important; }
            .search-show-box-buyleads.products-categories-listing #res { width: calc(100% - 0) !important; }
            #right-image { max-width: 215px !important; }
        }
        
        @media (min-width:768px) and (max-width:1024px) {
            .hm1.bbc.search-wrap { width: calc(100% - 233px) !important; }
        }
        
        #mask { width: 100% !important; }
        
        .small-box table tr td img {
            height: auto !important;
            width: auto !important;
            max-width: 100%;
            padding: 5px;
            border: 1px solid #8a8a8a;
        }
        
        .box-under-twoimage .padding-0 { width: 50%; }
        .box-under-twoimage > div {
            display: flex;
            justify-items: center;
            align-items: baseline;
            border: 1px solid #8a8a8a;
        }
        
        .wrapper-product-searchright { padding: 5px; }
        .small-box { width: 100%; display: inline-block; }
        
        .box-under-twoimage img.photo {
            max-width: 100%;
            max-height: 77px;
            width: auto;
            margin: 0 auto;
            display: table;
        }
        
        @media(max-width:1023px) {
            html #search_result .box-3 {
                width: auto !important;
                float: right;
                margin-top: 20px;
            }
            .wrapper div.lft.ser-mid .row .ar-box-1 { padding-bottom: 10px !important; }
        }
        
        @media(max-width:480px) {
            div.lft.ser-mid .row .ar-box-1 + .small-box { width: 100% !important; }
            div.lft.ser-mid .row .box-3 .ar-box-1 { max-width: 100% !important; }
            .ar-mid-box .box-3 .small-box {
                display: block !important;
                margin-bottom: 15px;
            }
            .ar-mid-box .box-3 .hidden-xs.small-box { display: none !important; }
            button.btn.btn-sm.btn-warning.border-radius-0.btn-enquiry { margin-top: 45px !important; }
        }
    </style>
    
    <script>
    function loadProductByCategory(page, id) {
        $.post("ajax-file/loadProductByCategory.php", {page: page, id: id}, function(data) {
            $('#res').html(data);
        });
    }
    
    function loadProductBySubCategory(page, id) {
        $.post("ajax-file/loadProductBySubCategory.php", {page: page, id: id}, function(data) {
            $('#res').html(data);
        });
    }
    
    function refineProductBySubCategory(page, id) {
        $.post("ajax-file/refineProductBySubCategory.php", {page: page, id: id}, function(data) {
            $('#final_result').html(data);
        });
    }
    
    $(window).load(function() {
        $(".loader").hide();
        $(".yahoo_loader_image").hide();
    });
    
    $(document).ready(function() {
        <?php if(isset($_GET['c'])): ?>
            loadProductByCategory(1, '<?php echo htmlspecialchars($_GET['c']); ?>');
        <?php endif; ?>
        
        <?php if(isset($_GET['sc'])): ?>
            loadProductBySubCategory(1, '<?php echo htmlspecialchars($_GET['sc']); ?>');
        <?php endif; ?>
        
        if($.trim($("#product_slider").html()) != '&nbsp;') {
            $("#product_slider").als({
                visible_items: 4,
                scrolling_items: 1,
                orientation: "horizontal",
                circular: "yes",
                autoscroll: "yes",
                interval: 4000
            });
        }
       
        if($.trim($("#saleoffer_slider").html()) != '&nbsp;') {
            $("#saleoffer_slider").als({
                visible_items: 4,
                scrolling_items: 1,
                orientation: "horizontal",
                circular: "yes",
                autoscroll: "yes",
                interval: 4500
            });
        }
    });
    </script>
    <script src="js/jquery.als-1.6.js"></script>
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/ctstyle.css" rel="stylesheet" type="text/css">
    <link href="css/im-style-v1.css" rel="stylesheet" type="text/css">
</head>

<body id="search_result_page"> 
<div id="fb-root"></div>
<script>
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>

<?php
// تحديد ترتيب التصنيفات
$sql_order = (get_page_settings('25') == 'manual') ? " ORDER BY pc_order, pc_name" : " ORDER BY pc_name";

// تضمين الهيدر
include "./includes/header_new.php";
?>

<div class="seach-page-inn">
    <div class="hm1 bbc search-wrap">
        
        
               <div id="res" class="lft fl">
            <link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
            <?php include_once 'index_leftsidebar.php'; ?>
        </div>
        
        
        
        <div class="lft fl ser-mid">
            <?php
            // تحديد المحتوى المركزي حسب نوع البحث
            if (isset($_GET['rctyp']) && $_GET['rctyp'] == 'Suppliers') {
               
   // تضمين ملف نتائج الموردين
                // include_once 'index_md_search.php';
            } else {
                include_once 'index_middle_content.php';
            }
            ?>
            
</div>
            ?>
            
            <div class="als-container" id="product_slider" style="height:250px;">&nbsp;</div>
            <div class="als-container" id="saleoffer_slider" style="height:250px;">&nbsp;</div>
        </div>
        
        <?php if (isset($_GET['rctyp']) && $_GET['rctyp'] == 'Suppliers') echo '</div>'; ?>
        
        <?php include_once 'index_rightsidebar.php'; ?>
    </div>
</div>

<p class="cb"><br></p>

<?php include "includes/footer.php"; ?>

<link rel="stylesheet" href="css/jquery.jqZoom.css?v=4.4" type="text/css"/>
<script src="js/jquery.jqZoom.js?v=4.1"></script>
<script>
jQuery(document).ready(function($) {
    $(".zoom-box img").jqZoom({
        selectorWidth: 30,
        selectorHeight: 30,
        viewerWidth: 400,
        viewerHeight: 300
    });
});
</script>
</body>
</html>