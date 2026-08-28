<?php
/**
 * index_rightsidebar_back.php - القائمة الجانبية اليمنى
 * تم ترقيته لـ PHP 8.3
 */

// منع الوصول المباشر
if (!defined('ACCESS_ALLOWED') && basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    exit('Direct access not allowed');
}


global $con, $location_geo_country, $pc_id;
?>



<style>

<?php
// تعريف $sql_pd_ck (مثل النسخة الأصلية 5.2)
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id='{$loc_id}')))
    )";
} else {
    $country_code = isset($location_geo_country[0]) ? mysqli_real_escape_string($con, $location_geo_country[0]) : '';
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='any')
        OR
        (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country=(SELECT cn_id FROM country WHERE cn_code='{$country_code}')))
    )";
}
?>

    .text-right button.btn.btn-default.btn-xs {
        height: auto !important;
        width: auto !important;
    }
    button.btn.btn-sm.btn-default.border-radius-0.txt-bold.bold-xs.btn-white.text-capitalize {
        height: auto !important;
        width: auto !important;
    }
    .right-section-search-buylead {
        position: absolute;
        right: 0;
    }
    #right-image{max-width: 238px !important;}
</style>

<?php
// =============================================
// 1. تعريف شروط الدولة (باستخدام mysqli)
// =============================================
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id='{$loc_id}')))
    )";
    
    $sql_br_ck = " AND (
        (br_preferred_supplier_location='domestic' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (br_preferred_supplier_location='any' AND br_u_id IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (br_preferred_supplier_location='my_city' AND br_u_id IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id='{$loc_id}')))
    )";
    
    $sql_tnd_ck = " AND (
        (tnd_preferred_location='domestic' AND tnd_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (tnd_preferred_location='any' AND tnd_usr_id IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (tnd_preferred_location='my_city' AND tnd_usr_id IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id='{$loc_id}')))
    )";
} else {
    $country_code = isset($location_geo_country[0]) ? mysqli_real_escape_string($con, $location_geo_country[0]) : '';
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='any')
        OR
        (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country=(SELECT cn_id FROM country WHERE cn_code='{$country_code}')))
    )";
    
    $sql_br_ck = " AND (
        (br_preferred_supplier_location='any')
        OR
        (br_preferred_supplier_location='abroad' AND br_u_id NOT IN (SELECT DISTINCT usr_id FROM user WHERE country=(SELECT cn_id FROM country WHERE cn_code='{$country_code}')))
    )";
    
    $sql_tnd_ck = " AND (
        (tnd_preferred_location='any')
        OR
        (tnd_preferred_location='abroad' AND tnd_usr_id NOT IN (SELECT DISTINCT usr_id FROM user WHERE country=(SELECT cn_id FROM country WHERE cn_code='{$country_code}')))
    )";
}

// =============================================
// 2. التصنيفات الأخرى (Related Categories)
// =============================================
if (isset($pc_id) && $pc_id != "") {
?>
<style>
.rht_related_cat a {
    font-weight: 600;
}
</style>
<div class="rht_related_cat">
    <div class="">
        <header style="margin-top: 20px;"> <span class="h4"> تصنيفات أخرى هامة </span> </header>
        <section>
            <ul style="list-style: none;">
                <?php
                // استعلام التصنيف الحالي
                $sql_current = "SELECT * FROM product_category WHERE md5(pc_id) = ? AND pc_status = '1'";
                $stmt_current = mysqli_prepare($con, $sql_current);
                mysqli_stmt_bind_param($stmt_current, 's', $pc_id);
                mysqli_stmt_execute($stmt_current);
                $result_current = mysqli_stmt_get_result($stmt_current);
                $rows = mysqli_fetch_object($result_current);
                mysqli_stmt_close($stmt_current);
                
                if ($rows) {
                    // جلب التصنيفات الرئيسية ذات الصلة
                    $sql_main = "SELECT * FROM product_category WHERE pc_parent_id = ? AND pc_status = '1' ORDER BY pc_name ASC";
                    $stmt_main = mysqli_prepare($con, $sql_main);
                    mysqli_stmt_bind_param($stmt_main, 'i', $rows->pc_parent_id);
                    mysqli_stmt_execute($stmt_main);
                    $result_main = mysqli_stmt_get_result($stmt_main);
                    
                    $counter = 0;
                    while ($Results = mysqli_fetch_object($result_main)) {
                        // جلب التصنيفات الفرعية
                        $sql_sub = "SELECT pc_id FROM product_category WHERE pc_parent_id = ?";
                        $stmt_sub = mysqli_prepare($con, $sql_sub);
                        mysqli_stmt_bind_param($stmt_sub, 'i', $Results->pc_id);
                        mysqli_stmt_execute($stmt_sub);
                        $result_sub = mysqli_stmt_get_result($stmt_sub);
                        
                        $pc_id_arr = [];
                        while ($data = mysqli_fetch_assoc($result_sub)) {
                            $pc_id_arr[] = (int)$data['pc_id'];
                        }
                        mysqli_stmt_close($stmt_sub);
                        
                        $ids = !empty($pc_id_arr) ? implode("','", $pc_id_arr) : '0';
                        
                        // التحقق من وجود منتجات في هذا التصنيف
                        $sql_check = "SELECT COUNT(*) as cnt FROM products p
                                      INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
                                      INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                                      WHERE p.pd_status = '1' AND p.pd_image != ''
                                      AND pm.expiry_date > " . time() . "
                                      AND p.pd_subcat_id IN ('$ids')
                                      $sql_pd_ck";
                        $check_result = mysqli_query($con, $sql_check);
                        $check_row = mysqli_fetch_assoc($check_result);
                        
                        if ($check_row['cnt'] > 0) {
                            $counter++;
                            if ($counter == 8) {
                                echo '<div class="collapse" id="categoriesRight">';
                            }
                            echo '<li class="first00"><a href="products.php?c=' . md5($Results->pc_id) . '">' . htmlspecialchars($Results->pc_name) . '</a></li>';
                        }
                    }
                    mysqli_stmt_close($stmt_main);
                    
                    if ($counter > 7) {
                        echo '</div>';
                    }
                    if ($counter > 7) { ?>
                        <div class="text-right">
                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#categoriesRight" aria-expanded="false" aria-controls="collapseExample"> المـزيد +</button>
                        </div>
                    <?php }
                }
                ?>
            </ul>
        </section>
    </div>
</div>
<?php } ?>

<!-- common for slider -->
<link rel="stylesheet" type="text/css" href="css/slick.css">
<link rel="stylesheet" type="text/css" href="css/slick-theme.css">
<script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
<?php include "css/custom.css"; ?>
<style>
    .slick-product-image > img {
        min-height: auto!important;
        max-height: 180px!important;
        border: 1px solid #E9E9E9!important;
    }
    .slick-product-wrapper {
        max-width: none!important;
        width: 90%;
        display: inline-block;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .matterbox p {
        text-align: center;
    }
    .ihoves {
        text-align: center!important;
    }
    .top-arrow::before, .bottom-arrow::before {
        font-family: slick;
        font-size: 20px;
        line-height: 1;
        opacity: .75;
        color: #fff;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .bottom-arrow::before {
        content: '←';
    }
    .top-arrow::before {
        content: '→';
    }
    .arrow_sli {
        height: 100%;
        width: 100%;
        right: 0;
        background: rgb(0,0,0);
        z-index: 9;
    }
</style>

<?php
// =============================================
// 3. تعريف الشروط مرة أخرى (للسلايدر)
// =============================================
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id='{$loc_id}')))
    )";
} else {
    $country_code = isset($location_geo_country[0]) ? mysqli_real_escape_string($con, $location_geo_country[0]) : '';
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='any')
        OR
        (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country=(SELECT cn_id FROM country WHERE cn_code='{$country_code}')))
    )";
}

// =============================================
// 4. السلايدر (شركات رائدة) - النسخة المصححة
// =============================================
if (isset($pc_id) && $pc_id != "") {
    // جلب التصنيفات ذات الصلة
    $sql_current = "SELECT * FROM product_category WHERE md5(pc_id) = ? AND pc_status = '1'";
    $stmt_current = mysqli_prepare($con, $sql_current);
    mysqli_stmt_bind_param($stmt_current, 's', $pc_id);
    mysqli_stmt_execute($stmt_current);
    $result_current = mysqli_stmt_get_result($stmt_current);
    $rows = mysqli_fetch_object($result_current);
    mysqli_stmt_close($stmt_current);
    
    if ($rows && isset($rows->pc_parent_id)) {
        // جلب التصنيفات الرئيسية ذات الصلة
        $sql_main = "SELECT pc_id FROM product_category WHERE pc_parent_id = ? AND pc_status = '1'";
        $stmt_main = mysqli_prepare($con, $sql_main);
        mysqli_stmt_bind_param($stmt_main, 'i', $rows->pc_parent_id);
        mysqli_stmt_execute($stmt_main);
        $result_main = mysqli_stmt_get_result($stmt_main);
        
        $rel_id = [];
        while ($Results = mysqli_fetch_object($result_main)) {
            if ($Results && isset($Results->pc_id)) {
                $rel_id[] = $Results->pc_id;
            }
        }
        mysqli_stmt_close($stmt_main);
        
        if (!empty($rel_id)) {
            $rel_ids = implode(',', $rel_id);
            
            // جلب التصنيفات الفرعية
            $sql_sub = "SELECT DISTINCT pc_id FROM product_category WHERE pc_parent_id IN ($rel_ids) GROUP BY pc_parent_id";
            $result_sub = mysqli_query($con, $sql_sub);
            
            $rel_id = [];
            if ($result_sub) {
                while ($Results = mysqli_fetch_object($result_sub)) {
                    if ($Results && isset($Results->pc_id)) {
                        $rel_id[] = $Results->pc_id;
                    }
                }
            }
            
            $rel_ids = !empty($rel_id) ? implode(',', $rel_id) : '0';
            
            // تصحيح: عرض عدد المنتجات في السلايدر
echo "<!-- debug: عدد المنتجات في السلايدر قبل الاستعلام: 0 -->";
            $sql_prd = "SELECT DISTINCT p.*, mu.*, c.*, u.*, bp.bnsprof_id 
            FROM products p
            INNER JOIN measurement_unit mu ON mu.mu_id = p.pd_unit
            INNER JOIN country c ON c.cn_id = p.pd_currency
            INNER JOIN user u ON u.usr_id = p.pd_uid
            INNER JOIN business_profile bp ON bp.bnsprof_uid = p.pd_uid
            WHERE p.pd_status = '1' 
            AND p.pd_image != ''
            AND p.pd_subcat_id IN ($rel_ids)
            ORDER BY RAND() 
            LIMIT 30";
            
            $result_prd = mysqli_query($con, $sql_prd);
            

            if ($result_prd && mysqli_num_rows($result_prd) > 2) {
                ?>
                <p style="font-size: 17px; text-align: center; margin-top: 20px;margin-bottom: 10px;"><b>شركــات رائــدة</b></p>
                <div class="demobox">
                    <div class="wrapper-container">
                        <div class="white_bg">
                            <div class="welcome_desc">
                                <div class="course_demo">
                                    <ul id="EgyptMART-relatedCat">
                                        <?php
                                        $indx = 0;
                                        while ($rowleading = mysqli_fetch_object($result_prd)) {
                                            if ($indx >= 30) break;
                                            if (!$rowleading || !isset($rowleading->pd_uid)) continue;
                                            
                                            // الحصول على business_profile
                                            $sql_bprof = "SELECT bnsprof_id FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
                                            $stmt_bprof = mysqli_prepare($con, $sql_bprof);
                                            $bprof_id = '';
                                            if ($stmt_bprof) {
                                                mysqli_stmt_bind_param($stmt_bprof, 'i', $rowleading->pd_uid);
                                                mysqli_stmt_execute($stmt_bprof);
                                                $res_bprof = mysqli_stmt_get_result($stmt_bprof);
                                                $row_bprof = mysqli_fetch_object($res_bprof);
                                                if ($row_bprof && isset($row_bprof->bnsprof_id)) {
                                                    $bprof_id = $row_bprof->bnsprof_id;
                                                }
                                                mysqli_stmt_close($stmt_bprof);
                                            }
                                            
                                            $product_link = 'company/products.php?c=' . rand(1000, 9999) . md5($bprof_id) . '&sc=' . rand(10, 99999) . ($rowleading->pd_subcat_id ?? '') . '#' . ($rowleading->pd_id ?? '');
                                            ?>
                                            <div class="main-slick-wrapper-item">
                                                <a class="slick-product-wrapper" href="<?php echo $product_link; ?>" target="_blank">
                                                    <div class="demobox">
                                                        <div class="slick-product-image">
                                                            <?php 
                                                            $pd_image = $rowleading->pd_image ?? '';


                                                  
                                                            if ($pd_image && file_exists($_SERVER['DOCUMENT_ROOT'] . '/upload/myproduct/' . $pd_image)): 
                                                            ?>
                                                            <img alt="" src="upload/myproduct/<?php echo htmlspecialchars($pd_image); ?>" class="black" style="margin: auto;border: 1px solid #E9E9E9!important;" title="<?php echo htmlspecialchars(ucwords($rowleading->pd_title ?? '')); ?>">
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="matterbox">
                                                            <div class="icon-pic-with-heading">
                                                                <div class="ihover-wrapper">
                                                                    <h3 class="ihoves">
                                                                        <?php 
                                                                        $pd_title = $rowleading->pd_title ?? '';
                                                                        echo htmlspecialchars(ucwords(substr($pd_title, 0, 15))); 
                                                                        if (strlen($pd_title) > 15) { echo '...'; } 
                                                                        ?>
                                                                    </h3>
                                                                    <div class="auction_hover">
                                                                        <p><?php echo htmlspecialchars(ucwords($pd_title)); ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="rightmatter">
                                                                <p><span class="nam"><?php echo htmlspecialchars(get_country_name((int)($rowleading->country ?? 0))); ?></span></p>
                                                                <p>MOQ: <span class="nam"><?php echo htmlspecialchars($rowleading->pd_min_order_qty ?? ''); ?><?php echo htmlspecialchars($rowleading->mu_name ?? ''); ?></span></p>
                                                                <p><?php echo htmlspecialchars($rowleading->cn_currency ?? ''); ?><span style="font-size:11px!important" class="nam"><?php echo htmlspecialchars($rowleading->pd_fob_price ?? ''); ?>/</span><?php echo htmlspecialchars($rowleading->mu_name ?? ''); ?></p>
                                                                <div class="clear"></div>
                                                            </div>
                                                            <div class="clear"></div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <?php
                                            $indx++;
                                        }
                                        ?>
                                    </ul>
                                    <script>
                                        $(window).load(function() {
                                            if (typeof $.fn.flexisel !== 'undefined') {
                                                $("#flexiselDemo4").flexisel({
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
                                            }
                                        });
                                    </script>
                                </div>
                            </div>
                            <div class="clear" style="height:1px"></div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
    }
}
?>








<script>
    $('#EgyptMART-product,#EgyptMART-relatedCat').slick({
        nextArrow: '<div class="arrow_sli"><img src="/assets/img/botom.png" class="top-arrow" aria-label="Previous" style="width:30px;display: block;margin: auto;border: none;background: rgb(34,122,191);padding: 5px;"></div>',
        prevArrow: '<div class="arrow_sli"><img src="/assets/img/top.png" class="bottom-arrow" aria-label="Next" style="width:30px;display: block;margin: auto;border: none;background: rgb(34,122,191);padding: 5px;"></div>',
        centerMode: true,
        centerPadding: '10px',
        slidesToShow: 5,
        autoplay: true,
        vertical: true,
        responsive: [
            { breakpoint: 1024, settings: { centerMode: true, centerPadding: '10px', slidesToShow: 5 } },
            { breakpoint: 768, settings: { centerMode: true, centerPadding: '10px', slidesToShow: 5 } },
            { breakpoint: 480, settings: { centerMode: true, centerPadding: '10px', slidesToShow: 5 } }
        ]
    });
</script>

<!-- Trade Offers Start -->
<script type="text/javascript">
    function buy_show() {
        $("#bs1").removeClass("cp c4");
        $("#ss1").addClass("cp c4");
        $("#bs2").removeClass("off").addClass("on mt2");
        $("#ss2").removeClass("on mt2").addClass("off");
    }
    function sell_show() {
        $("#bs1").addClass("cp c4");
        $("#ss1").removeClass("cp c4");
        $("#bs2").removeClass("on mt2").addClass("off");
        $("#ss2").removeClass("off").addClass("on mt2");
    }
    <script>
    $(document).ready(function() {
        setTimeout(function() {
            $('.slick-product-image img').each(function() {
                console.log('صورة:', $(this).attr('src'));
                $(this).css({'display': 'block', 'visibility': 'visible', 'opacity': '1'});
            });
        }, 1000);
    });
</script>
    
</script>