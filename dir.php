<?php
/**
 * File: dir.php

 * Version: PHP 8.3
 * Description: القائمة الجانبية اليسرى للصفحة الرئيسية - تعرض التصنيفات والمنتجات حسب بلد المستخدم
 */

// بدء المخزن المؤقت والجلسة
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}



if (!function_exists('getLocationInfoByIp1')) {
    function getLocationInfoByIp1(): array {
        $client = $_SERVER['HTTP_CLIENT_IP'] ?? '';
        $forward = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        $remote = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        
        $result = ['country' => '', 'city' => ''];
        
        $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if ($ip_data && isset($ip_data->geoplugin_countryName) && $ip_data->geoplugin_countryName != null) {
            $result['country'] = $ip_data->geoplugin_countryCode ?? '';
            $result['city'] = $ip_data->geoplugin_city ?? '';
        }
        
        return $result;
    }
}




// ==============================================
// شروط التصفية حسب بلد المستخدم
// ==============================================

// تحديد معرف البلد من الكوكيز
$loc_id = isset($_COOKIE['loc_id']) ? (int)$_COOKIE['loc_id'] : 0;

// الحصول على معلومات الموقع الجغرافي (للاستخدام الاحتياطي)
$location_geo_country = '';
$location = getLocationInfoByIp1();
if (isset($location['countryCode']) && !empty($location['countryCode'])) {
    $location_geo_country = $location['countryCode'];
}

// بناء شروط SQL للمنتجات حسب بلد المستخدم
if ($loc_id > 0) {
    // مستخدم لديه بلد محدد في الكوكيز
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = {$loc_id})) 
        OR 
        (pd_preferred_buyer_location = 'any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = {$loc_id}))
        OR
        (pd_preferred_buyer_location = 'my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile bf JOIN city c ON bf.bnsprof_city = c.ct_id WHERE c.ct_cn_id = {$loc_id}))
    )";

    $sql_br_ck = " AND (
        (br_preferred_supplier_location = 'domestic' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country = {$loc_id})) 
        OR 
        (br_preferred_supplier_location = 'any' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country = {$loc_id}))
        OR
        (br_preferred_supplier_location = 'my_city' AND br_u_id IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city = (SELECT ct_id FROM city WHERE ct_cn_id = {$loc_id})))
    )";

    $sql_so_ck = " AND (
        (so_preferred_buyer_location = 'domestic' AND so_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = {$loc_id})) 
        OR 
        (so_preferred_buyer_location = 'any' AND so_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country = {$loc_id}))
        OR
        (so_preferred_buyer_location = 'my_city' AND so_usr_id IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city = (SELECT ct_id FROM city WHERE ct_cn_id = {$loc_id})))
    )";
} else {
    // مستخدم بدون بلد محدد (Global)
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country = (SELECT cn_id FROM country WHERE cn_code = '{$location_geo_country}')))
    )";

    $sql_br_ck = " AND (
        (br_preferred_supplier_location = 'any')
        OR
        (br_preferred_supplier_location = 'abroad' AND br_u_id NOT IN (SELECT DISTINCT usr_id FROM user WHERE country = (SELECT cn_id FROM country WHERE cn_code = '{$location_geo_country}')))
    )";

    $sql_so_ck = " AND (
        (so_preferred_buyer_location = 'any')
        OR
        (so_preferred_buyer_location = 'abroad' AND so_usr_id NOT IN (SELECT DISTINCT usr_id FROM user WHERE country = (SELECT cn_id FROM country WHERE cn_code = '{$location_geo_country}')))
    )";
}

// ==============================================
// جلب التصنيفات الفرعية للمنتجات
// ==============================================

$SubCategoryArray = "";
$html = "";

// جلب معرفات التصنيفات الفرعية للمنتجات المتاحة
$sql_cmt_cnt1 = "SELECT DISTINCT pd_subcat_id  
                 FROM products, measurement_unit, country, business_profile, plan_member_id, smembership_plan
                 WHERE mu_id = pd_unit 
                   AND pd_currency = cn_id 
                   {$sql_pd_ck} 
                   AND business_profile.bnsprof_uid = products.pd_uid 
                   AND plan_member_id.b_id = business_profile.bnsprof_id 
                   AND pd_status = '1'  
                   AND smembership_plan.mp_id = plan_member_id.p_id 
                   AND pd_image != '' 
                   AND plan_member_id.expiry_date > " . time() . "   
                 ORDER BY FIELD(p_id, '5', '4', '3', '15')";

$html .= "<!-- {$sql_cmt_cnt1} -->";
$res_dd_mnu = mysqli_query($con, $sql_cmt_cnt1);
$pc_id_arr = array();

while ($row_dd_mnu = mysqli_fetch_object($res_dd_mnu)) {
    if (!empty($row_dd_mnu->pd_subcat_id)) {
        $pc_id_arr[] = (int)$row_dd_mnu->pd_subcat_id;
    }
}

if (count($pc_id_arr) == 0) {
    $html .= '<li class="ptag text-danger" style="padding: 5px;">! حالياً لا يوجد أى منتجات لهذا البلد للعرض</li>';
} else {
    // تحويل المصفوفة إلى سلسلة نصية للاستعلامات
    $SubCategoryArray = "'" . implode("','", $pc_id_arr) . "'";

    // ==============================================
    // جلب التصنيفات الرئيسية (الآباء)
    // ==============================================
    
    $ParentCategoryArray = "";
    $pc_id_arrNew = array();
    
    $sql_dd_cmnuParent = "SELECT DISTINCT pc_parent_id 
                          FROM product_category 
                          WHERE pc_id IN ({$SubCategoryArray}) 
                            AND pc_status = 1";
    
    $res_dd_cmnuParent = mysqli_query($con, $sql_dd_cmnuParent);
    while ($row_dd_cmnuParent = mysqli_fetch_object($res_dd_cmnuParent)) {
        if (!empty($row_dd_cmnuParent->pc_parent_id)) {
            $pc_id_arrNew[] = (int)$row_dd_cmnuParent->pc_parent_id;
        }
    }
    
    if (!empty($pc_id_arrNew)) {
        $ParentCategoryArray = "'" . implode("','", $pc_id_arrNew) . "'";
    }

    // ==============================================
    // جلب التصنيفات الرئيسية (الأجداد)
    // ==============================================
    
    $MasterCategoryArray = "";
    $pc_id_arrNewNew = array();
    
    if (!empty($ParentCategoryArray)) {
        $sql_dd_cmnuMaster = "SELECT DISTINCT pc_parent_id 
                              FROM product_category 
                              WHERE pc_id IN ({$ParentCategoryArray}) 
                                AND pc_status = 1";
        
        $res_dd_cmnuMaster = mysqli_query($con, $sql_dd_cmnuMaster);
        while ($row_dd_cmnuMaster = mysqli_fetch_object($res_dd_cmnuMaster)) {
            if (!empty($row_dd_cmnuMaster->pc_parent_id)) {
                $pc_id_arrNewNew[] = (int)$row_dd_cmnuMaster->pc_parent_id;
            }
        }
        
        if (!empty($pc_id_arrNewNew)) {
            $MasterCategoryArray = "'" . implode("','", $pc_id_arrNewNew) . "'";
        }
    }

    // ==============================================
    // جلب وعرض التصنيفات الرئيسية النهائية
    // ==============================================
    
    if (!empty($MasterCategoryArray)) {
        $sql_dd_cmnuAgainMaster = "SELECT DISTINCT pc_id, pc_image, pc_name 
                                   FROM product_category 
                                   WHERE pc_id IN ({$MasterCategoryArray}) 
                                     AND pc_status = 1 
                                   ORDER BY pc_order ASC";
        
        $res_dd_cmnuAgainMaster = mysqli_query($con, $sql_dd_cmnuAgainMaster);
        $total_count = mysqli_num_rows($res_dd_cmnuAgainMaster);
        
        $c = 1;
        while ($row_dd_cmnuAgainMaster = mysqli_fetch_object($res_dd_cmnuAgainMaster)) {
            $row_mcat = $row_dd_cmnuAgainMaster;
            ?>
            <style>
                .lnk li { height: auto; }
            </style>
            <div class="bx1 mb1 lnk" id="main_cat_<?php echo (int)$row_mcat->pc_id; ?>">
                <p class="p4 lh1">
                    <table width="98%">
                        <tr>
                            <td width="12%">
                                <img alt="" class="bg10a" src="upload/category/<?php echo htmlspecialchars($row_mcat->pc_image ?? ''); ?>" align="left" height="75" width="75">
                            </td>
                            <td style="padding-left:4%; background:#088100;" width="88%">
                                <h2 style="font-weight: 700;">
                                    <a href="category.php?token=<?php echo rand(1000, 9999) . md5((string)$row_mcat->pc_id); ?>" style="text-decoration:none; color: white;">
                                        <?php echo htmlspecialchars($row_mcat->pc_name ?? ''); ?>
                                    </a>
                                </h2>
                            </td>
                        </tr>
                    </table>
                </p>

                <ul class="sdu">
                    <?php
                    // جلب التصنيفات الفرعية المباشرة
                    if (!empty($ParentCategoryArray)) {
                        $sql_dd_cmnuAgainParent = "SELECT pc_id, pc_name 
                                                   FROM product_category 
                                                   WHERE pc_parent_id = " . (int)$row_mcat->pc_id . " 
                                                     AND pc_id IN ({$ParentCategoryArray}) 
                                                     AND pc_status = 1 
                                                   ORDER BY pc_order ASC";
                        
                        $res_dd_cmnuAgainParent = mysqli_query($con, $sql_dd_cmnuAgainParent);
                        
                        while ($row_dd_cmnuAgainParent = mysqli_fetch_object($res_dd_cmnuAgainParent)) {
                            $cat_row = $row_dd_cmnuAgainParent;
                            ?>
                            <li>
                                <a class="txt-blue" href="products.php?c=<?php echo md5((string)$cat_row->pc_id); ?>">
                                    <?php echo htmlspecialchars(ucwords($cat_row->pc_name ?? '')); ?>
                                </a>
                                <br>
                                <span>
                                    <?php
                                    // جلب التصنيفات الفرعية الأصغر
                                    $sql_dd_cmnuAgainParent2 = "SELECT pc_id, pc_name 
                                                                FROM product_category 
                                                                WHERE pc_parent_id = " . (int)$cat_row->pc_id . " 
                                                                  AND pc_id IN ({$SubCategoryArray}) 
                                                                  AND pc_status = 1 
                                                                ORDER BY pc_order ASC 
                                                                LIMIT 10";
                                    
                                    $res_dd_cmnuAgainParent2 = mysqli_query($con, $sql_dd_cmnuAgainParent2);
                                    $rowcount2 = mysqli_num_rows($res_dd_cmnuAgainParent2);
                                    $hide_i = 1;
                                    
                                    while ($row_dd_cmnuAgainParent2 = mysqli_fetch_object($res_dd_cmnuAgainParent2)) {
                                        $scat_row = $row_dd_cmnuAgainParent2;
                                        
                                        if ($hide_i == 4) {
                                            echo '</span><span style="display:none" id="id' . (int)$cat_row->pc_id . '">';
                                        }
                                        
                                        if ($hide_i > 3) {
                                            ?>
                                            <a href="products.php?sc=<?php echo md5((string)$scat_row->pc_id); ?>">
                                                <?php echo htmlspecialchars(ucwords($scat_row->pc_name ?? '')); ?>
                                            </a>
                                            <br/>
                                        <?php } else { ?>
                                            <a href="products.php?sc=<?php echo md5((string)$scat_row->pc_id); ?>">
                                                <?php echo htmlspecialchars(ucwords($scat_row->pc_name ?? '')); ?>
                                            </a>
                                            <br/>
                                        <?php }
                                        $hide_i++;
                                    } ?>
                                </span>
                                
                                <?php if ($rowcount2 > 3): ?>
                                    <span onclick="myFunction('<?php echo (int)$cat_row->pc_id; ?>', this)" 
                                          style="padding:3px; cursor:pointer; font-size:12px">
                                        <i class="fa fa-plus"></i>&nbsp;.. المزيد
                                    </span>
                                <?php endif; ?>
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
                <p class="c3"></p>
            </div>
            <?php
            $c++;
        }
    }
    
    $html .= ob_get_contents();
}

// تنظيف المخزن المؤقت
ob_end_clean();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <link type="text/css" rel="stylesheet" href="css/home-page-2.css">
    <link type="text/css" rel="stylesheet" href="css/main-v2.css">
    <link href="css/bl_form_temp1.css" rel="stylesheet" type="text/css">
    
    <!--JSCSS-->
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
</head>
<body data-twttr-rendered="true" class="search-show-box directory-template">
    <div class="q_hm1">
        
        <?php include "includes/header_new.php"; ?>
        
        <!--New Header End -->

        <!--Left Start::-->
        <div class="inner_wrapper">
            <div class="lft fl">
                <div class="bc">
                    <p class="cor f2"></p>
                </div>

                <?php 
                // تحديد ترتيب عرض الفئات
                if (get_page_settings('25') == 'manual') {
                    $sql_order = " ORDER BY pc_order, pc_name";
                } else {
                    $sql_order = " ORDER BY pc_name";
                }
                
                // عرض محتوى التصنيفات الديناميكي
                echo $html;
                ?>

                <p class="c3 mb1">
                    <img src="images/n_zero.gif" alt="" height="1" width="1">
                </p>

                <br class="c3">
                
                <!--Buy Lead Form Code Start-->
                <div align="left">
                    <br>
                    <span id="form_redirect_path"></span>
                    <span id="q_lead_enrichment"></span>
                    <span id="q_lead_conversion"></span>
                    <span id="q_lead_impressionload"></span>
                </div>
                <!--Buy Lead Form Code Ends-->
            </div>
            <!--Left End::-->
            
            <!--ryt Start::-->
            <div class="ryt fr">
                <div class="tbx">
                    <div class="wlc" id="wl">
                        <?php if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])): 
                            $uid = (int)$_SESSION['uid_indm'];
                        ?>
                            <div id="nlogin">
                                <p class="bp13 m12">
                                    <span class="cr6"><?php echo htmlspecialchars(user_info($uid, 'name_prefix') . " " . user_info($uid, 'fname')); ?>: مرحـبا</span>
                                    <br>
                                    <span>
                                        <b class="bo1">إذهب الى &nbsp;</b>
                                        <a href="my-dashboard.php">لوحة مفاتيح المنصة</a>
                                    </span>
                                </p>
                            </div>
                        <?php else: ?>
                            <p class="bp13 m12">
                                <?php echo htmlspecialchars(getWebSiteName() ?? ''); ?> مرحبا بك فى
                                <br>
                                <span>
                                    <b class="bo1">مستخدم جديد ؟ &nbsp; </b>
                                    <a href="create_account.php">إنضم الآن</a>
                                </span>
                            </p>
                        <?php endif; ?>
                        
                        <script type="text/javascript">
                            function buy() {
                                $("#fb1").addClass("bb").css("top", "2px");
                                $("#fs1").removeClass("bb").css("top", "1px");
                                $("#fb2").removeClass("off").addClass("ct1");
                                $("#fs2").removeClass("ct1").addClass("off");
                            }
                            function sell() {
                                $("#fb1").removeClass("bb").css("top", "1px");
                                $("#fs1").addClass("bb").css("top", "2px");
                                $("#fb2").removeClass("ct1").addClass("off");
                                $("#fs2").removeClass("off").addClass("ct1");
                            }
                        </script>

                        <p class="spb">
                            <img src="images/n_zero.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1">
                        </p>
                        
                        <div class="p1 p2">
                            <p class="fb1 bb" id="fb1" onMouseOver="buy()" style="position:relative; top:2px; z-index:1000;">للمشتريين</p>
                            <p class="fs1" id="fs1" onMouseOver="sell()" style="position:relative; top:1px; z-index:1000; left:-18px;">للموردين</p>
                        </div>
                        
                        <p class="c3"></p>
                        
                        <div class="bc7 mr1 bl6" id="mdiv">
                            <div id="fb2" class="ct1">
                                <p class="one"><a href="post-buy-req.php">أنشر طلبات شراء لشركتك</a></p>
                                <p class="p12">تلقى إستجابات من <br>موردين متحقق من وجودهم الفعلى</p>
                                <p class="two">إبحث عن أى منتج تهتم به</p>
                                <div class="p12">
                                    إرسل إستفسارات مباشرة الى المورد الذى تختاره
                                    <script>
                                        function validsearch_r() {
                                            var keywords = document.getElementById('keywords_r');
                                            if (keywords.value == '' || keywords.value == null) {
                                                alert("من فضلك أدخل كلمة صحيحة للبحث");
                                                return false;
                                            }
                                        }
                                    </script>
                                    <form method="GET" name="searchForm2" action="search.php" onSubmit="return validsearch_r();">
                                        <input type="hidden" name="rctyp" id="rctyp" value="Products"/>
                                        <table cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input size="24" class="m1 bl6" style="width:130px;" id="keywords_r" name="keywords" type="text">
                                                    </td>
                                                    <td>
                                                        &nbsp; 
                                                        <input value="إبحــث" class="m1 fz1 ff1 m5 r-block-search" style="width:55px; margin-left: -25px;" type="submit">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                                <p class="thre" style="margin-top:-3px!important;">
                                    <a href="manage-selloffer-alert.php">تلقى أشعارات عروض بيع خاصة</a>
                                </p>
                                <p class="p12">تلقى عروض بيع وطلبات شراء طبقا لأصناف إهتماماتك</p>
                            </div>

                            <div class="off" id="fs2">
                                <p class="one"><a href="create-free-website.php">إنشىء صفحات أعمالك التجارية مع أهم شركات عارضه</a></p>
                                <p class="p12 m31">إعلن عن منتجاتك وسط آلاف الموردين العارضين على هذه المنصة</p>
                                <p class="two"><a href="post-sell-offer.php">إعلانات مجانية عن أنشطتك التجارية</a></p>
                                <p class="p12">روج لمنتجاتك وعروضك<br>الى مشتريين فى الداخل والخارج</p>
                                <p class="thre"><a href="manage-buylead-alert.php">تلقى إشعارات طلبات شراء من الداخل والخارج</a></p>
                                <p class="p12">تلقى طلبات شراء من عدة مشتريين طبفا للأصناف التى تسجلها على المنصة</p>
                            </div>
                        </div>
                        <p class="c1 p33"></p>
                    </div>
                </div>
                <p id="flx" class="on"></p>

                <?php
                // عرض أحدث عروض البيع
                $sql_l_so = "SELECT * FROM sale_offer, user, business_profile 
                             WHERE so_usr_id = usr_id 
                               AND usr_id = bnsprof_uid 
                               AND so_approval_status = '1' 
                               {$sql_so_ck} 
                               AND so_status = '1' 
                               AND DATE_ADD(so_approval_date, INTERVAL so_validity DAY) >= NOW() 
                             ORDER BY so_approval_date DESC 
                             LIMIT 5";
                
                $res_l_so = mysqli_query($con, $sql_l_so);
                
                if (mysqli_num_rows($res_l_so) > 0):
                ?>
                <div class="mb1 c3">
                    <p class="bxt"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>
                    <div class="bbx led" style="border:1px solid #DDD; border-radius:5px; width:248px;">
                        <p class="bg bxh">أحدث عروض البيع</p>
                        <ul id="sell_offers" class="ovr">
                            <?php while ($row_l_so = mysqli_fetch_object($res_l_so)): ?>
                                <li style="background-color:#FAF4FF">
                                    <a style="background-position: 0px -1545px;" class="c_flg" rel="f_in" 
                                       href="saleoffer-details.php?id=<?php echo rand(1000, 9999) . md5((string)$row_l_so->so_id); ?>">
                                        <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_l_so->country ?? 0)); ?>" 
                                             style="margin-left:-15px; width:18px;" 
                                             alt="<?php echo htmlspecialchars(get_country_name($row_l_so->country ?? 0)); ?>" 
                                             title="<?php echo htmlspecialchars(get_country_name($row_l_so->country ?? 0)); ?>">
                                        &nbsp;&nbsp;
                                        <?php echo htmlspecialchars(ucwords(stripslashes($row_l_so->so_service ?? ''))); ?>
                                    </a>
                                    <span>
                                        Trade Scope:
                                        <?php
                                        if ($row_l_so->so_preferred_buyer_location == 'any') {
                                            echo "(Foreign & Domestic)";
                                        } else if ($row_l_so->so_preferred_buyer_location == 'abroad') {
                                            echo "(Foreign Only)";
                                        } else if ($row_l_so->so_preferred_buyer_location == 'domestic') {
                                            echo "(Domestic Only)";
                                        } else if ($row_l_so->so_preferred_buyer_location == 'my_city' && !empty($row_l_so->bnsprof_city)) {
                                            echo htmlspecialchars(get_city_name($row_l_so->bnsprof_city)) . "(250 KM)";
                                        }
                                        ?>
                                    </span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <p class="c3 rm tr">
                            <a href="sale-offers.php">شاهد كل عروض البيع</a>
                        </p>
                    </div>
                    <p class="bxb"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>
                </div>
                <?php endif; ?>

                <?php
                // عرض أحدث طلبات الشراء
                $sql_l_bl = "SELECT * FROM buy_requirement, user, business_profile 
                             WHERE br_u_id = usr_id 
                               AND usr_id = bnsprof_uid 
                               AND br_approval_status = '1' 
                               AND br_display_status = '1' 
                               {$sql_br_ck} 
                               AND br_status = '1' 
                             ORDER BY br_updated_date DESC 
                             LIMIT 5";
                
                $res_l_bl = mysqli_query($con, $sql_l_bl);
                
                if (mysqli_num_rows($res_l_bl) > 0):
                ?>
                <div class="mb1 c3">
                    <p class="bxt"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>
                    <div class="bbx led" style="border:1px solid #DDD; border-radius:5px; width:248px;">
                        <p class="bg bxh">Latest Buy Leads</p>
                        <ul id="buy_leads" class="ovr">
                            <?php while ($row_l_bl = mysqli_fetch_object($res_l_bl)): ?>
                                <li style="background-color:#FAF4FF">
                                    <a style="background-position: 0px -1545px;" class="c_flg" rel="f_in" 
                                       href="buyleads-details.php?id=<?php echo rand(1000, 9999) . md5((string)$row_l_bl->br_id); ?>">
                                        <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($row_l_bl->country ?? 0)); ?>" 
                                             style="margin-left:-15px; width:18px;" 
                                             alt="<?php echo htmlspecialchars(get_country_name($row_l_bl->country ?? 0)); ?>" 
                                             title="<?php echo htmlspecialchars(get_country_name($row_l_bl->country ?? 0)); ?>">
                                        &nbsp;&nbsp;
                                        <?php echo htmlspecialchars($row_l_bl->br_pd_name ?? ''); ?>
                                    </a>
                                    <span>
                                        <?php if (!empty($row_l_bl->br_preferred_supplier_location)): ?>
                                            Trade Scope:
                                            <?php
                                            if ($row_l_bl->br_preferred_supplier_location == 'any') {
                                                echo "(Foreign & Domestic)";
                                            } else if ($row_l_bl->br_preferred_supplier_location == 'abroad') {
                                                echo "(Foreign Only)";
                                            } else if ($row_l_bl->br_preferred_supplier_location == 'domestic') {
                                                echo "(Domestic Only)";
                                            } else if ($row_l_bl->br_preferred_supplier_location == 'my_city' && !empty($row_l_bl->bnsprof_city)) {
                                                echo htmlspecialchars(get_city_name($row_l_bl->bnsprof_city)) . "(250 KM)";
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <p class="c3 rm tr">
                            <a id="vbl" href="buyleads.php">شاهد كل طلبات الشراء</a>
                        </p>
                    </div>
                    <p class="bxb"><img src="images/n_zero.gif" alt="" height="1" width="1"></p>
                </div>
                <?php endif; ?>

                <div id="m4t4">
                    <?php
                    // عرض إعلان عشوائي
                    $sql_adv = "SELECT * FROM advertisement 
                                WHERE adv_imagewidth = '250' 
                                  AND adv_imageheight = '250' 
                                  AND adv_status = '1' 
                                ORDER BY RAND() 
                                LIMIT 1";
                    
                    $res_adv = mysqli_query($con, $sql_adv);
                    
                    if (mysqli_num_rows($res_adv) > 0) {
                        $row_adv = mysqli_fetch_object($res_adv);
                        $adv_link = htmlspecialchars($row_adv->adv_link ?? '');
                        $adv_img = htmlspecialchars($row_adv->adv_img ?? '');
                        ?>
                        <a href="//<?php echo $adv_link; ?>" target="_blank">
                            <img src="upload/advertisement/<?php echo $adv_img; ?>" width="250" height="250" alt="Advertisement">
                        </a>
                    <?php } else { ?>
                        <img alt="" src="upload/advertisement/250-250-advertisement.png" border="0" height="250" hspace="0" vspace="0" width="250">
                    <?php } ?>
                </div>
                <br>

                <p class="c3"></p>
                <div style="margin:10px auto 0px; width:160px;">
                    <div style="float:left; margin-left;15px;"></div>
                </div>
            </div>
        </div>
        <br>
        <!--ryt End::-->
        <br class="c3">
        <br class="c3">
        
        <!--Footer Start::-->
        <br class="c3">
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <script>
    function myFunction(id, obj) {
        var dots = document.getElementById("id" + id);
        
        if (dots.style.display === "none") {
            dots.style.display = "inline";
            obj.innerHTML = '<i class="fa fa-minus"></i>&nbsp;.. شاهد أقـل ';
        } else {
            dots.style.display = "none";
            obj.innerHTML = '<i class="fa fa-plus"></i>&nbsp;.. المزيـد';
        }
    }
    </script>
    
    <style type="text/css">
        .lnk li a.txt-blue span {
            font: 700 14px arial !important;
            text-decoration: none;
            line-height: 21px;
        }
    </style>
</body>
</html>