<?php
/**
 * اسم الملف: index.php
 * الوصف: الصفحة الرئيسية للموقع - تعرض المنتجات، العروض، المناقصات، المزادات
 * الإصدار: 3.0.0
 * تاريخ التحديث: 2024-01-25
 * متطلبات PHP: 8.3
 */

// إخفاء الأخطاء في الإنتاج - للتطوير فقط
if (!defined('ENVIRONMENT') || ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
}

// بدء تشغيل المخزن المؤقت وجلسة العمل
ob_start();
session_start();

// تضمين الملفات الأساسية
require_once 'common.php';

// التحقق من وجود اتصال قاعدة البيانات
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في اتصال قاعدة البيانات');
}

// الحصول على معرف المستخدم الحالي
$uid = $_SESSION['uid_indm'] ?? 0;
$globalcntid = 241;

// تحديد الدولة من الكوكيز
$cn_id = 0;
$cn_name = "Global";

if (isset($_COOKIE['loc_id'])) {
    $cn_id = (int)$_COOKIE['loc_id'];
    $country_info = fetchOne(
        "SELECT cn_name FROM country WHERE cn_id = ?",
        'i',
        [$cn_id]
    );
    $cn_name = $country_info['cn_name'] ?? "Global";
}

// بناء شرط الدولة للاستعلامات
function buildCountryCondition($cn_id, $globalcntid, $field = 'adv_country'): string {
    if ($cn_id > 0) {
        return " AND ($field LIKE '%,$cn_id,%' OR $field LIKE '%,$cn_id' OR $field LIKE '$cn_id,%' OR $field = '$cn_id')";
    } else {
        return " AND ($field LIKE '%,$globalcntid,%' OR $field LIKE '%,$globalcntid' OR $field LIKE '$globalcntid,%' OR $field = '$globalcntid')";
    }
}

$strconutnry = buildCountryCondition($cn_id, $globalcntid);

/**
 * دالة بناء شروط الموقع للمنتجات وطلبات الشراء
 */
function buildLocationConditions($cn_id, $location_geo_country) {
    $conditions = [
        'product' => '',
        'sale_offer' => '',
        'buy_requirement' => ''
    ];
    
    if ($cn_id > 0) {
        // شروط للمنتجات
        $conditions['product'] = " AND (
            (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT usr_id FROM user WHERE country = ?))
            OR (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT usr_id FROM user WHERE country = ?))
            OR (pd_preferred_buyer_location='my_city' AND pd_uid IN (
                SELECT bnsprof_uid FROM business_profile 
                WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = ? LIMIT 1)
            ))
        )";
        
        // شروط لعروض البيع
        $conditions['sale_offer'] = " AND (
            (so_preferred_buyer_location='domestic' AND so_usr_id IN (SELECT usr_id FROM user WHERE country = ?))
            OR (so_preferred_buyer_location='any' AND so_usr_id IN (SELECT usr_id FROM user WHERE country = ?))
            OR (so_preferred_buyer_location='my_city' AND so_usr_id IN (
                SELECT bnsprof_uid FROM business_profile 
                WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = ? LIMIT 1)
            ))
        )";
        
        // شروط لطلبات الشراء
        $conditions['buy_requirement'] = " AND (
            (br_preferred_supplier_location='domestic' AND br_u_id IN (SELECT usr_id FROM user WHERE country = ?))
            OR (br_preferred_supplier_location='any' AND br_u_id IN (SELECT usr_id FROM user WHERE country = ?))
            OR (br_preferred_supplier_location='my_city' AND br_u_id IN (
                SELECT bnsprof_uid FROM business_profile 
                WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = ? LIMIT 1)
            ))
        )";
    } else {
        $country_code = '';
        if (isset($location_geo_country)) {
            $country_code = is_array($location_geo_country) ? ($location_geo_country[0] ?? '') : $location_geo_country;
        }
        
        if (!empty($country_code)) {
            $conditions['product'] = " AND (
                (pd_preferred_buyer_location='any')
                OR (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (
                    SELECT usr_id FROM user 
                    WHERE country IN (SELECT cn_id FROM country WHERE cn_code = ?)
                ))
            )";
            
            $conditions['sale_offer'] = " AND (
                (so_preferred_buyer_location='any')
                OR (so_preferred_buyer_location='abroad' AND so_usr_id NOT IN (
                    SELECT usr_id FROM user 
                    WHERE country IN (SELECT cn_id FROM country WHERE cn_code = ?)
                ))
            )";
            
            $conditions['buy_requirement'] = " AND (
                (br_preferred_supplier_location='any')
                OR (br_preferred_supplier_location='abroad' AND br_u_id NOT IN (
                    SELECT usr_id FROM user 
                    WHERE country IN (SELECT cn_id FROM country WHERE cn_code = ?)
                ))
            )";
        }
    }
    
    return $conditions;
}

$location_conditions = buildLocationConditions($cn_id, $location_geo_country ?? '');

// تحديد ترتيب التصنيفات
$sql_order = (get_page_settings('25') == 'manual') ? " ORDER BY pc_order, pc_name" : " ORDER BY pc_name";

?>
<!DOCTYPE HTML>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="<?php echo escapeHtml(getSiteTitle()); ?>">
    <meta name="keywords" content="<?php echo escapeHtml(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo escapeHtml(get_page_settings(3)); ?>">
    <title>مرحباً :: <?php echo escapeHtml(getSiteTitle()); ?></title>
    
    <!-- CSS Files -->
    <link href="css/bootstrap.css" rel='stylesheet' type='text/css'/>
    <link href="css/style.css" rel="stylesheet" type='text/css'/>
    <link href="css/responsive1.css" rel="stylesheet" type="text/css"/>
    <link href="fonts/font-awesome.css" rel="stylesheet" type="text/css"/>
    <link href="css/im-style-v1.css" rel="stylesheet" type="text/css"/>
    <link href="css/verticle-menu.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="css/theme.css"/>
    <link href="css/type.css" rel="stylesheet" type="text/css"/>
    
    <!-- JavaScript Files -->
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="js/jquery.accessible-news-slider.js"></script>
    <script src="js/responsiveslides.min.js"></script>
    
    <script>
        $(function () {
            $("#slider").responsiveSlides({
                auto: true,
                nav: false,
                speed: 500,
                namespace: "callbacks",
                pager: true
            });
        });
        
        jQuery(document).ready(function () {
            jQuery('#newsslider').accessNews({});
            jQuery('#newsslider2').accessNews({
                title: "BREAKING NEWS:",
                subtitle: "stories from the internet",
                speed: "slow",
                slideBy: 5,
                slideShowInterval: 100000,
                slideShowDelay: 100000
            });
        });
        
        function setCountryLocation(id) {
            $.post("setCountryLocation.php", {loc_id: id}, function (data) {
                if (data != 0) {
                    location.reload();
                }
            }).fail(function() {
                alert("حدث خطأ في تغيير الدولة");
            });
        }
        
        function unsetCountryLocation() {
            $.post("unsetCountryLocation.php", function (data) {
                location.reload();
            }).fail(function() {
                alert("حدث خطأ في إلغاء تحديد الدولة");
            });
        }
    </script>
    <!--[if IE]>
    <script src="js/html5.js"></script> 
    <![endif]-->
</head>

<body style="background-color: #EDF2F5">

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

<!-- Start of wrapper -->
<div class="wrapper">

    <?php include "includes/header.php"; ?>

    <!-- Start of middlesection -->
    <div class="middlesection">
        <div class="maincontainer">
            <div class="demobox">
                <!-- القسم الأيسر -->
                <div id="leftsection">
                    <h3 style="margin-left: 10PX">
                        <a href="dir.php#main_cat">
                            <i class="fa fa-list-ul" style="color:#FF4500 "></i>&nbsp;<span>كل</span> التصنيفات
                        </a>
                    </h3>

                    <div id="block_navigation">
                        <div id="pull" style="display: none;">
                            <a href="#"> <i class="icon-reorder"></i>القائمة</a>
                        </div>

                        <ul class="navigation ptag">
                            <?php
                            // جلب التصنيفات الرئيسية
                            $categories = fetchAll(
                                "SELECT pc_id, pc_name, pc_image 
                                 FROM product_category 
                                 WHERE pc_parent_id = 0 AND pc_status = '1' 
                                 $sql_order"
                            );
                            
                            foreach ($categories as $cat) {
                                $pc_id = (int)$cat['pc_id'];
                                $pc_name = escapeHtml($cat['pc_name']);
                                $cat_class = ($pc_name == "Business Services") ? 'style="color:red; font-family: Arial Black"' : '';
                            ?>
                            <li class="ptag">
                                <a href="dir.php#main_cat_ptag<?php echo $pc_id; ?>" <?php echo $cat_class; ?>>
                                    <?php echo $pc_name; ?>
                                    <span class="main_links_span"></span>
                                </a>
                                
                                <div class="typography_3_colm">
                                    <div class="colm_3_container">
                                        <?php
                                        // جلب التصنيفات الفرعية
                                        $subcategories = fetchAll(
                                            "SELECT pc_id, pc_sort_name 
                                             FROM product_category 
                                             WHERE pc_parent_id = ? AND pc_status = '1' 
                                             $sql_order",
                                            'i',
                                            [$pc_id]
                                        );
                                        
                                        $item_cnt = count($subcategories);
                                        
                                        if ($item_cnt > 0) {
                                            $subcat_chunks = array_chunk($subcategories, ceil($item_cnt / 3));
                                            foreach ($subcat_chunks as $chunk) {
                                        ?>
                                        <div class="colmn_3_fullwidth">
                                            <ol class="some_links ptaga">
                                                <?php foreach ($chunk as $subcat): ?>
                                                    <li>
                                                        <a class="ptaga" href="products.php?c=<?php echo md5((string)$subcat['pc_id']); ?>">
                                                            <?php echo escapeHtml(ucwords($subcat['pc_sort_name'] ?? '')); ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ol>
                                        </div>
                                        <?php 
                                            }
                                        } 
                                        ?>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                            <p><a href="dir.php#main_cat" target="_blank">عرض كل التصنيفات <span>&gt;&gt;</span></a></p>
                        </ul>
                    </div>

                    <div class="list-top">
                        <h1><a href="#"><img src="images/wholesaler.jpg" alt=""/></a></h1>
                        <h5>حدد</h5>
                        <a href="create-free-website.php" target="_">
                            <div class="showcase">عرض المنتجات</div>
                        </a>
                        <p>وزع في مدينتك</p>
                    </div>

                    <div class="map-tops">
                        <div class="map">
                            <a href="#"><img src="images/map.png" alt=""/></a>
                        </div>
                    </div>

                    <div class="list-top">
                        <div class="seniorbox">
                            <div class="siniorlistbox">
                                <div class="siconbox"><img src="images/left-icon.png" alt=""/></div>
                                <div class="scontentbox"><h2>مورد <span>متميز</span></h2></div>
                                <div class="clear"></div>
                            </div>

                            <ul>
                                <li>&gt; <a href="#">مواقع شركات متميزة</a></li>
                                <li>&gt; <a href="#">عرض المنتجات</a></li>
                                <li>&gt; <a href="#">ترتيب متقدم للمنتجات</a></li>
                                <li>&gt; <a href="#">وصول كامل لطلبات الشراء</a></li>
                                <li>&gt; <a href="#">إعلانات مجانية</a></li>
                                <li>&gt; <a href="#">فيديو الشركة</a></li>
                                <p><a href="membership_plans.php" style="fright">اعرف المزيد <span>&gt; &gt;</span></a></p>
                            </ul>

                            <h3><a href="membership_plans.php">ترقى الآن</a></h3>
                        </div>
                    </div>

                    <div class="mid-tops">
                        <?php
                        $banner = GetHomeBanner('left', $strconutnry);
                        if (!empty($banner)) {
                            echo '<div class="middle mid-center" style="padding:0;">' . $banner . '</div>';
                        }
                        ?>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>

                <!-- القسم الأوسط -->
                <div id="midcenter">
                    <!-- Start of slider -->
                    <div class="slider">
                        <div class="yahoo_slider">
                            <ul id="newsslider">
                                <?php
                                $slides = fetchAll(
                                    "SELECT * FROM yahoo_slider 
                                     WHERE adv_status = '1' AND adv_img != '' 
                                     $strconutnry 
                                     ORDER BY adv_updated_date DESC"
                                );
                                
                                foreach ($slides as $slide):
                                    $img_path = "upload/yahoo_slider/" . escapeHtml($slide['adv_img']);
                                    $adv_link = escapeHtml($slide['adv_link'] ?? '#');
                                    $adv_title = escapeHtml($slide['adv_title'] ?? '');
                                    $adv_desc = Show_shortcontent($slide['adv_description'] ?? '', 22);
                                ?>
                                <li>
                                    <a href="<?php echo $adv_link; ?>" target="_blank">
                                        <img src="<?php echo $img_path; ?>" alt="<?php echo $adv_title; ?>" />
                                    </a>
                                    <h3><a href="<?php echo $adv_link; ?>" target="_blank"><?php echo $adv_title; ?></a></h3>
                                    <p><?php echo $adv_desc; ?><br/><a href="<?php echo $adv_link; ?>"> &raquo; قراءة المزيد</a></p>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="video_slider">
                            <!-- فيديوهات الشركات -->
                            <div class="slider">
                                <ul class="rslides" id="slider">
                                    <?php
                                    $videos = fetchAll(
                                        "SELECT * FROM video_slider 
                                         WHERE adv_status = '1' $strconutnry"
                                    );
                                    
                                    foreach ($videos as $video):
                                        $video_link = $video['adv_link'] ?? '';
                                        $adv_redirect = escapeHtml($video['adv_redirect'] ?? '#');
                                        $bnsprof_compname = escapeHtml($video['adv_title'] ?? '');
                                        $bnsprof_address1 = escapeHtml($video['adv_description'] ?? '');
                                        
                                        // معالجة رابط يوتيوب
                                        $iframe2show = $video_link;
                                        if (strpos($video_link, 'youtube.com') !== false || strpos($video_link, 'youtu.be') !== false) {
                                            preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $video_link, $matches);
                                            $id = $matches[1] ?? '';
                                            if (!empty($id)) {
                                                $iframe2show = '<iframe width="100%" height="181"
                                                    src="https://www.youtube.com/embed/' . $id . '" 
                                                    frameborder="0" allowfullscreen></iframe>';
                                            }
                                        }
                                    ?>
                                    <li>
                                        <?php echo $iframe2show; ?>
                                        <div class="iframebox">
                                            <h2>
                                                <i class="fa fa-play"></i>
                                                <a href="<?php echo $adv_redirect; ?>" target="_blank">
                                                    <?php echo mb_substr($bnsprof_compname, 0, 22); ?>..
                                                </a>
                                            </h2>
                                            <p><?php echo mb_substr($bnsprof_address1, 0, 30); ?>..</p>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="verifiedbox_bottom">
                                <h4><a href="company-video.php">انشر <span>مجاناً</span> فيديو شركتك</a></h4>

                                <div class="verifiedbox_supplierbox">
                                    <h3>موردون موثوقون</h3>
                                    <p>موردون مختارون من حول العالم  
                                        <span class="fright">
                                            <a href="membership_plans.php">اعرف المزيد &gt; &gt;</a>
                                        </span>
                                    </p>

                                    <div class="clear"></div>
                                    <ul>
                                        <li>
                                            <a href="membership_plans.php" class="tooltip1">
                                                <img src="images/verified01.jpg" alt=""/>
                                                <span><i>مورد راعي</i></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="membership_plans.php" class="tooltip1">
                                                <img src="images/verified02.jpg" alt=""/>
                                                <span><i>مورد متميز</i></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="membership_plans.php" class="tooltip1">
                                                <img src="images/verified03.jpg" alt=""/>
                                                <span><i>مورد عادي</i></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End of slider // -->

                    <div class="countrybox">
                        <div class="countrubox_top">
                            <div class="countrubox_heading">
                                <div class="globalicon">
                                    الصفحة العالمية 
                                    <a href="#" onclick="unsetCountryLocation();">
                                        <img src="images/Untit.png" alt="Global"/>
                                    </a>
                                </div>
                                <h2>أفضل <span>الأسواق العربية</span></h2>
                            </div>

                            <div class="search">
                                <input type="text" id="search" name="search" class="textbox" placeholder="ابحث عن دولة"/>
                                <input type="submit" value="اشترك" id="submit" name="submit"/>
                                
                                <script type="text/javascript">
                                    $("#submit").click(function(){
                                        var cn = $("#search").val();
                                        $.ajax({
                                            type: "POST",
                                            url: "search_country.php",
                                            data: 'cname=' + cn,
                                            cache: false,
                                            success: function(data){
                                                $('#response').html(data);
                                            }
                                        });
                                    });
                                </script>
                                <div id="response"></div>
                            </div>
                            <div class="clear"></div>
                        </div>

                        <!-- قائمة الدول الآسيوية -->
                        <div class="cnt1">
                            <table>
                                <tr>
                                    <td><span><b>آسيا&nbsp;&nbsp;&nbsp;:</b></span></td>
                                    <td>
                                        <ul class="country">
                                            <li><a href="#" onclick="setCountryLocation(225);"><img src="images/uae.jpg" alt=""> الإمارات</a></li>
                                            <li><a href="#" onclick="setCountryLocation(187);"><img src="images/Saudi-Arabia.jpg" alt=""> السعودية</a></li>
                                            <li><a href="#" onclick="setCountryLocation(112);"><img src="images/Kuwait.jpg" alt=""> الكويت</a></li>
                                            <li><a href="#" onclick="setCountryLocation(173);"><img src="images/Qatar.jpg" alt=""> قطر</a></li>
                                            <li><a href="#" onclick="setCountryLocation(108);"><img src="images/jordan.jpg" alt=""> الأردن</a></li>
                                            <li><a href="#" onclick="setCountryLocation(116);"><img src="images/Lebanon.jpg" alt=""> لبنان</a></li>
                                            <li><a href="#" onclick="setCountryLocation(237);"><img src="images/yemen.jpg" alt=""> اليمن</a></li>
                                            <li><a href="#" onclick="setCountryLocation(101);"><img src="images/iraq.jpg" alt=""> العراق</a></li>
                                            <li><a href="#" onclick="setCountryLocation(208);"><img src="images/Syria.jpg" alt=""> سوريا</a></li>
                                            <li><a href="#" onclick="setCountryLocation(163);"><img src="images/Palestine.jpg" alt=""> فلسطين</a></li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- قائمة الدول الأفريقية -->
                        <div class="cnt1">
                            <table>
                                <tr>
                                    <td><span><b>أفريقيا:</b></span></td>
                                    <td>
                                        <ul class="country">
                                            <li><a href="#" onclick="setCountryLocation(63);"><img src="images/flag01.png" alt=""> مصر</a></li>
                                            <li><a href="#" onclick="setCountryLocation(202);"><img src="images/sudan.jpg" alt=""> السودان</a></li>
                                            <li><a href="#" onclick="setCountryLocation(119);"><img src="images/Libya.jpg" alt=""> ليبيا</a></li>
                                            <li><a href="#" onclick="setCountryLocation(142);"><img src="images/morroco.jpg" alt=""> المغرب</a></li>
                                            <li><a href="#" onclick="setCountryLocation(3);"><img src="images/flag02.png" alt=""> الجزائر</a></li>
                                            <li><a href="#" onclick="setCountryLocation(217);"><img src="images/Tunisia.jpg" alt=""> تونس</a></li>
                                            <li><a href="#" onclick="setCountryLocation(133);"><img src="images/Mauritania.jpg" alt=""> موريتانيا</a></li>
                                            <li><a href="#" onclick="setCountryLocation(58);"><img src="images/Djibouti.jpg" alt=""> جيبوتي</a></li>
                                            <li><a href="#" onclick="setCountryLocation(196);"><img src="images/Somalia.jpg" alt=""> الصومال</a></li>
                                            <li><a href="#" onclick="setCountryLocation(49);"><img src="images/Comoros.jpg" alt=""> جزر القمر</a></li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="space21"></div>

                    <!-- قسم المنتجات -->
                    <div class="countrubox_top2">
                        <div class="countrubox_heading">
                            <h2>عرض <a href="dir.php#main_cat"><span>المنتجات والموردين</span></a></h2>
                        </div>
                        <div class="list-rights">
                            <h2><a href="product-sel-cat.php"><span>اعرض</span> منتجاتك</a></h2>
                        </div>
                        <div class="clear"></div>
                    </div>

                    <div class="demobox">
                        <div class="col-md-12">
                            <div class="white_bg">
                                <div class="clear" style="height:5px;"></div>
                                <div class="welcome_desc">
                                    <div class="course_demo">
                                        <?php
                                        // جلب المنتجات
                                        $products = [];
                                        
                                        if ($cn_id > 0) {
                                            $products = fetchAll(
                                                "SELECT p.*, mu.*, c.*, 
                                                        (SELECT bnsprof_id FROM business_profile WHERE bnsprof_uid = p.pd_uid LIMIT 1) as bnsprof_id
                                                 FROM products p
                                                 JOIN measurement_unit mu ON p.pd_unit = mu.mu_id
                                                 JOIN country c ON p.pd_currency = c.cn_id
                                                 WHERE p.pd_status = '1' AND p.pd_image != '' 
                                                 " . ($location_conditions['product'] ?? '') . "
                                                 ORDER BY RAND() LIMIT 24",
                                                'iii',
                                                array_fill(0, substr_count($location_conditions['product'] ?? '', '?'), $cn_id)
                                            );
                                        } else {
                                            $products = fetchAll(
                                                "SELECT p.*, mu.*, c.*,
                                                        (SELECT bnsprof_id FROM business_profile WHERE bnsprof_uid = p.pd_uid LIMIT 1) as bnsprof_id
                                                 FROM products p
                                                 JOIN measurement_unit mu ON p.pd_unit = mu.mu_id
                                                 JOIN country c ON p.pd_currency = c.cn_id
                                                 WHERE p.pd_status = '1' AND p.pd_image != '' 
                                                 ORDER BY RAND() LIMIT 24"
                                            );
                                        }
                                        
                                        if (!empty($products)):
                                        ?>
                                        <ul id="flexiselDemo1">
                                            <?php foreach ($products as $product): 
                                                $bnsprof_id = $product['bnsprof_id'] ?? 0;
                                                
                                                // جلب أيقونة العضوية
                                                $icon_info = fetchOne(
                                                    "SELECT sip.mst_icon, sip.mst_name 
                                                     FROM smembership_icon_plan sip 
                                                     JOIN plan_member_id pm ON sip.mp_id = pm.p_id 
                                                     WHERE pm.b_id = ?",
                                                    'i',
                                                    [$bnsprof_id]
                                                );
                                                
                                                $title = 'Junior';
                                                if (!empty($icon_info)) {
                                                    $mst_name = strtolower($icon_info['mst_name'] ?? '');
                                                    if (str_contains($mst_name, 'senior') || str_contains($mst_name, 'senier')) {
                                                        $title = 'Senior';
                                                    } elseif (str_contains($mst_name, 'sponsor') || str_contains($mst_name, 'sponser')) {
                                                        $title = 'Sponsor';
                                                    }
                                                }
                                                
                                                $icon_img = $icon_info['mst_icon'] ?? 'slider-icon01.jpg';
                                                $product_link = "company/products.php?c=" . rand(1000, 9999) . md5((string)$bnsprof_id) . 
                                                                "&sc=" . rand(10000, 99999) . ($product['pd_subcat_id'] ?? '') . 
                                                                "#" . ($product['pd_id'] ?? '');
                                            ?>
                                            <li>
                                                <a href="<?php echo $product_link; ?>" style="text-decoration:none;color:#000" target="_blank">
                                                    <img src="upload/myproduct/thumb/<?php echo escapeHtml($product['pd_image'] ?? ''); ?>" 
                                                         alt="<?php echo escapeHtml(ucwords(mb_substr($product['pd_title'] ?? '', 0, 28))); ?>" 
                                                         title="<?php echo escapeHtml(ucwords($product['pd_title'] ?? '')); ?>"  
                                                         class="img-responsive"/>
                                                    
                                                    <div class="matterbox">
                                                        <div class="icon_pic">
                                                            <img src="admin/images/<?php echo escapeHtml($icon_img); ?>"  
                                                                 title="<?php echo $title; ?>" 
                                                                 style="width:18px; height:15px;"
                                                                 class="img-responsive" alt=""/>
                                                        </div>
                                                        <div class="rightmatter">
                                                            <h3><?php echo escapeHtml(ucwords(mb_substr($product['pd_title'] ?? '', 0, 28))); ?></h3>
                                                            <p>البلد: <span class="nam"><?php echo escapeHtml(get_country_name((int)($product['cn_id'] ?? 0))); ?></span><br></p>
                                                            <p>أقل كمية: <span class="nam"><?php echo (int)($product['pd_min_order_qty'] ?? 0); ?>&nbsp;<?php echo escapeHtml($product['mu_name'] ?? ''); ?></span><br></p>
                                                            <p>
                                                                <?php echo escapeHtml($product['cn_currency'] ?? '$'); ?>&nbsp; 
                                                                <span class="nam" style="font-size:15px !important;"> 
                                                                    <?php echo (float)($product['pd_fob_price'] ?? 0); ?>/
                                                                </span>
                                                                <?php echo escapeHtml($product['mu_name'] ?? ''); ?>
                                                            </p>
                                                            <div class="clear"></div>
                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                </a>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>

                                        <script type="text/javascript">
                                            $(window).load(function () {
                                                $("#flexiselDemo1").flexisel({
                                                    visibleItems: 4,
                                                    animationSpeed: 1000,
                                                    autoPlay: true,
                                                    autoPlaySpeed: 3000,
                                                    pauseOnHover: true,
                                                    enableResponsiveBreakpoints: true,
                                                    responsiveBreakpoints: {
                                                        portrait: { changePoint: 480, visibleItems: 1 },
                                                        landscape: { changePoint: 640, visibleItems: 2 },
                                                        tablet: { changePoint: 768, visibleItems: 2 }
                                                    }
                                                });
                                            });
                                        </script>
                                        <script type="text/javascript" src="js/jquery.flexisel.js"></script>
                                    </div>

                                    <div class="learnmores">
                                        <p><a href="dir.php#main_cat" target="_blank">عرض كل التصنيفات <span>&gt;&gt;</span></a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- باقي أقسام الصفحة (عروض البيع، إعلانات، خدمات الأعمال، موردون) -->
                    <!-- سيتم إكمالها في الجزء التالي -->
                    
                </div> <!-- نهاية midcenter -->

                <!-- القسم الأيمن -->
                <div id="rightsection">
                    <!-- سيتم إضافته في الجزء التالي -->
                </div>
            </div> <!-- نهاية demobox -->
        </div> <!-- نهاية maincontainer -->
    </div> <!-- نهاية middlesection -->
    
    <?php include 'includes/footer.php'; ?>
</div> <!-- نهاية wrapper -->

<?php
// إنهاء المخزن المؤقت وإرسال المحتوى
ob_end_flush();
?>