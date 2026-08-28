<?php
/**
 * File: product-list.php
 * Description: عرض قائمة منتجات المستخدم مع خيارات التعديل والحذف والتصفح
 * Version: 2.0.0 (PHP 8.3 Compatible) - LTR Layout
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "product-list.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: membership_plans.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

global $con;

// =============================================
// إعدادات التصفح (Pagination)
// =============================================
$pageno = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
if ($pageno < 1) $pageno = 1;

// حساب إجمالي المنتجات
$count_sql = "SELECT COUNT(*) as total FROM products WHERE pd_uid = ? AND pd_status = '1'";
$stmt_count = mysqli_prepare($con, $count_sql);
mysqli_stmt_bind_param($stmt_count, 'i', $uid);
mysqli_stmt_execute($stmt_count);
$count_result = mysqli_stmt_get_result($stmt_count);
$count_row = mysqli_fetch_assoc($count_result);
$total_products = (int)($count_row['total'] ?? 0);
mysqli_stmt_close($stmt_count);

// إذا لم يكن هناك منتجات، التوجيه إلى صفحة إضافة منتج
if ($total_products == 0) {
    header("Location: product-add.php");
    exit;
}

$per_page = 10;
$total_pages = (int)ceil($total_products / $per_page);
if ($pageno > $total_pages) $pageno = $total_pages;

$start_limit = $per_page * ($pageno - 1);

// جلب المنتجات للصفحة الحالية
$sql = "SELECT pd_id, pd_title, pd_desc, pd_image, pd_imagelogo, pd_date 
        FROM products 
        WHERE pd_uid = ? AND pd_status = '1' 
        ORDER BY pd_id DESC 
        LIMIT ?, ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iii', $uid, $start_limit, $per_page);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
mysqli_stmt_close($stmt);

// حساب عرض العناصر
$start_item = $start_limit + 1;
$end_item = min($start_limit + $per_page, $total_products);
$show_items = $start_item . "-" . $end_item . " of " . $total_products;
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link href="css/pro.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/colorbox.css" type="text/css" rel="stylesheet">
    
    <style>
        .vu { text-decoration: none; padding: 5px 10px; border: 1px solid #ccc; margin: 0 2px; }
        .vu:hover { background-color: #f0f0f0; }
        .b3 { padding: 5px 10px; border: 1px solid #ccc; margin: 0 2px; }
        .w1 { background-color: #4CAF50; color: white; }
        .mg { margin: 0 2px; }
        .pro { max-width: 100%; height: auto; }
        .abouteditdv { background-color: #f9f9f9; padding: 10px; border-radius: 5px; }
        .yn { color: #0066cc; text-decoration: underline; cursor: pointer; }
        .product-title { margin-left: 15px; }
        .desc-text { margin-right: 20px; }
        .action-buttons { width: 100px; margin-right: 20px; }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <script>
    $(document).ready(function() {
        $(".ajax").colorbox();
        $(".inline").colorbox({inline: true, width: "50%"});
    });
    
    function showdeloption(id) {
        $(".abouteditdv").hide();
        $(".abtListdv").show();
        $("#dcon" + id).slideDown('slow');
    }
    
    function hidedeloption(id) {
        $("#dcon" + id).slideUp('slow');
    }
    
    function delmprofile(id) {
        $.get("ajax-file/delproduct.php", {id: id}, function(data) {
            location.reload();
        });
    }
    
    function showdesc(id) {
        $("#base_desc_hd" + id).show();
        $("#less_sd" + id).show();
        $("#base_desc_sd" + id).hide();
        $("#less_hd" + id).hide();
    }
    
    function hidedesc(id) {
        $("#base_desc_hd" + id).hide();
        $("#less_sd" + id).hide();
        $("#base_desc_sd" + id).show();
        $("#less_hd" + id).show();
    }
    
    function prostatchange(id) {
        $.get("ajax-file/product-change.php", {id: id}, function(data) {
            $("#pstch" + id).html(data);
        });
    }
    
    function markhot(id) {
        $('#pstch' + id).html('<img src=images/indicator.gif border=0>');
        $.get("ajax-file/markhot-add.php", {id: id}, function(data) {
            prostatchange(id);
        });
    }
    
    function pushedtotop(id) {
        $.get("ajax-file/pushedtotop.php", {id: id}, function(data) {
            prostatchange(id);
        });
    }
    </script>
</head>
<body>
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" width="1" height="1"></div>
        
        <div class="inner_wrapper">
            <!-- Menu -->
            <?php include __DIR__ . '/includes/header_menu.php'; ?>
            
            <!-- القائمة الجانبية اليسرى -->
            <?php include __DIR__ . '/includes/left_menu.php'; ?>
            
            <!-- المحتوى الرئيسي -->
            <div class="w56b f1 p2b p14 blr" style="height:auto;">
                <h1 style="font-size:22px; font-weight:bold; text-align:center; padding-left:10px;">
                    تحديث المنتجات والخدمات
                </h1>
                
                <div class="mt5">
                    <!-- شريط التصفح العلوي -->
                    <div class="ap1">
                        <p class="f2 mt12" id="page_str">
                            <span class="cpr link1"><strong><?php echo $show_items; ?></strong></span>
                        </p>
                        
                        <div class="c3">
                            <?php if ($total_products > 0): ?>
                                <?php if ($pageno > 1): ?>
                                    <a href="product-list.php?pageno=<?php echo $pageno - 1; ?>" class="vu">Previous</a>
                                <?php endif; ?>
                                
                                <?php if ($total_products > $per_page): ?>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <?php if ($pageno == $i): ?>
                                            <span class="b3 w1 p2 w3 p4 mg"><?php echo $i; ?></span>
                                        <?php else: ?>
                                            <a href="product-list.php?pageno=<?php echo $i; ?>" class="b3 b2 w1 p2 w3 p4 vu mg"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                <?php endif; ?>
                                
                                <?php if ($pageno < $total_pages): ?>
                                    <a href="product-list.php?pageno=<?php echo $pageno + 1; ?>" class="vu">Next</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <input name="pcid" id="pcid" value="3115715" type="hidden">
                    
                    <!-- شريط الإجراءات -->
                    <p class="urh psrh2">
                        <span class="fr" style="padding:0; margin-left:0">
                            <span class="allp"><span class="cpr" style="display:none">&nbsp;</span></span>
                        </span>
                        <a class="f2 fw pro-colr apr" href="product-add.php" title="Add New Product">+ إضافة منتج جديد</a>
                        <a href="javascript:void(0);" class="f11 pl4 f1 cls-gry" id="pgcls_close" style="display:none">x&nbsp;close</a>
                        &nbsp;<span id="wait_img"></span>
                    </p>
                    
                    <!-- قائمة المنتجات -->
                    <div id="list_view">
                        <?php foreach ($products as $product): 
                            $pd_id = (int)$product['pd_id'];
                            $pd_title = htmlspecialchars($product['pd_title'] ?? '', ENT_QUOTES, 'UTF-8');
                            $pd_desc = htmlspecialchars($product['pd_desc'] ?? '', ENT_QUOTES, 'UTF-8');
                            $pd_date = !empty($product['pd_date']) ? date('d M, Y', strtotime($product['pd_date'])) : 'N/A';
                            
                            // معالجة الصور
                            $images = explode(',', $product['pd_image'] ?? '');
                            $first_image = !empty($images[0]) ? htmlspecialchars($images[0], ENT_QUOTES, 'UTF-8') : '';
                            
                            $logos = explode(',', $product['pd_imagelogo'] ?? '');
                            $first_logo = !empty($logos[0]) ? htmlspecialchars($logos[0], ENT_QUOTES, 'UTF-8') : '';
                            
                            $has_long_desc = strlen($product['pd_desc'] ?? '') > 296;
                            $short_desc = htmlspecialchars(substr($product['pd_desc'] ?? '', 0, 296), ENT_QUOTES, 'UTF-8');
                            
                            // التحقق من وجود الصور الفعلية
                            $image_path = "upload/myproduct/" . $first_image;
                            $full_image_path = __DIR__ . "/" . $image_path;
                            $image_exists = !empty($first_image) && file_exists($full_image_path) && is_file($full_image_path);
                            
                            $logo_path = "upload/myproduct/" . $first_logo;
                            $full_logo_path = __DIR__ . "/" . $logo_path;
                            $logo_exists = !empty($first_logo) && file_exists($full_logo_path) && is_file($full_logo_path);
                        ?>
                        <div class="plst mse wid abtListdv" id="pro_<?php echo $pd_id; ?>">
                            <ul>
                                <!-- صورة المنتج -->
                                <li class="f1 prt" style="width:125px; height:125px;">
                                    <div style="position:relative;">
                                        <?php if ($image_exists): ?>
                                            <a><img src="<?php echo $image_path; ?>" alt="<?php echo $pd_title; ?>" 
                                                    class="pro" border="0" style="width:70%; height:auto;"></a>
                                        <?php else: ?>
                                            <a><img src="images/noimage.jpg" class="pro" border="0" width="125" height="107" alt="No Image"></a>
                                        <?php endif; ?>
                                        
                                        <?php if ($logo_exists): ?>
                                            <a>
                                                <img src="<?php echo $logo_path; ?>" border="0" 
                                                     style="position:absolute; top:82px; left:0px; width:42px; height:41px;" 
                                                     alt="Logo">
                                            </a>
                                        <?php else: ?>
                                            <a>
                                                <img src="upload/myproduct/pic-logo.png" border="0" 
                                                     style="position:absolute; top:82px; left:0px; width:42px; height:41px;" 
                                                     alt="Default Logo">
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                
                                <!-- تفاصيل المنتج -->
                                <li class="f2" style="margin-top:20px;">
                                    <div class="f1 p-cont">
                                        <h1 class="f1 itm_colr product-title" id="itemname_<?php echo $pd_id; ?>">
                                            <?php echo $pd_title; ?>
                                        </h1>
                                        <div class="c3"></div>
                                        <span class="f1 msenew">Last modified on: <?php echo $pd_date; ?></span>
                                        
                                        <div id="base_desc_hd<?php echo $pd_id; ?>" 
                                             style="margin-left:20px; color:#222222; display:none;" 
                                             class="disc c3">
                                            <?php echo nl2br($pd_desc); ?>
                                        </div>
                                        
                                        <div id="base_desc_sd<?php echo $pd_id; ?>" 
                                             style="margin-left:20px; color:#222222;" 
                                             class="disc c3">
                                            <?php echo nl2br($short_desc); ?>
                                        </div>
                                        
                                        <?php if ($has_long_desc): ?>
                                            <a style="padding-left:20px; float:left; font-size:12.5px; text-align:center; 
                                                      text-decoration:underline; cursor:pointer;" 
                                               id="less_hd<?php echo $pd_id; ?>" 
                                               onClick="showdesc(<?php echo $pd_id; ?>);">
                                                View Complete Details
                                            </a>
                                        <?php endif; ?>
                                        
                                        <span id="less_sd<?php echo $pd_id; ?>" style="display:none;">
                                            <a style="padding-left:20px; float:left; font-size:12.5px; text-align:center; 
                                                      text-decoration:underline; cursor:pointer;" 
                                               onClick="hidedesc(<?php echo $pd_id; ?>);">
                                                Less
                                            </a>
                                        </span>
                                    </div>
                                    
                                    <!-- أزرار الإجراءات -->
                                    <div class="f2 action-buttons">
                                        <script>prostatchange(<?php echo $pd_id; ?>);</script>
                                        <div id="pstch<?php echo $pd_id; ?>"></div>
                                        
                                        <div>
                                            <span class="link1 cpr" style="*margin-bottom:5px;">   
                                                <a class="b-img edi f1" 
                                                   href="product-edit.php?token=<?php echo rand(1000, 9999) . md5((string)$pd_id); ?>" 
                                                   style="*float:none" title="Edit">
                                                    تحديث
                                                </a>
                                            </span>
                                            <a class="del b-img f1 c3" onclick="showdeloption(<?php echo $pd_id; ?>)" 
                                               style="*float:none; cursor:pointer;" title="Delete">
                                                إزالة
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                
                                <!-- زر التكبير -->
                                <?php if ($image_exists): ?>
                                <li class="wtmp wtmpie">
                                    <a href="productzoomimage.php?token=<?php echo rand(1000, 9999) . md5((string)$pd_id); ?>" 
                                       class="ajax" style="cursor:pointer;">
                                        <div class="f2 zoom2 mrgzoom"></div>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                            
                            <div id="actb_<?php echo $pd_id; ?>" class="c3 pddng"></div>
                            
                            <!-- تأكيد الحذف -->
                            <div class="info bnr mt12 c3 abouteditdv" id="dcon<?php echo $pd_id; ?>" 
                                 style="display:none; height:33px; margin-bottom:5px;">
                                <div style="width:125px;" class="f2">   
                                    <a onclick="delmprofile(<?php echo $pd_id; ?>);" 
                                       class="yn" id="yes_<?php echo $pd_id; ?>" 
                                       style="cursor:pointer;">Yes</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <a onclick="hidedeloption(<?php echo $pd_id; ?>)" 
                                       class="yn" id="no_<?php echo $pd_id; ?>" 
                                       style="cursor:pointer;">No</a>
                                </div>
                                Do you want to delete this product?
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
?>