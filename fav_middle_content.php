<?php
/**
 * File: fav_middle_content.php

 * Version: PHP 8.3
 * Description: محتوى المنتجات المفضلة - يعرض المنتجات التي أضافها المستخدم إلى قائمة المفضلة في واجهة متحركة
 */

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

require_once __DIR__ . '/lib/function.php';

// باقي الكود القديم هنا...
// التحقق من وجود معرف المستخدم في الجلسة
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    return; // الخروج إذا لم يكن هناك مستخدم مسجل
}

$uid = (int)$_SESSION['uid_indm'];
?>

<!-- تضمين مكتبات Colorbox -->
<script src="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/js/jquery.colorbox.js"></script>
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/css/colorbox.css" type="text/css" rel="stylesheet">

<div id="thankYouBlock"> </div>

<script>
$(document).ready(function() {
    $('body').on('click', '.ajax', function() {
        $.colorbox({
            href: $(this).attr('href'),
            open: true,
            width: '750px',
            onClosed: function() {
                window.location.reload();
            }
        });
        return false;
    });
});
</script>

<?php
// جلب المنتجات المفضلة للمستخدم
$favourites_sql = "SELECT * FROM favourites_table WHERE user_id = {$uid}";
$favourites = mysqli_query($con, $favourites_sql);

$compareidstr = array();
while ($row = mysqli_fetch_object($favourites)) {
    $compareidstr[] = $row->user_id . '-' . $row->pro_id;
}

$tempallids = array();
$i = 0;
$x = 0;

foreach ($compareidstr as $tempid) {
    $datacheck = explode("-", $tempid);
    if (isset($datacheck[0]) && (int)$datacheck[0] == $uid) {
        if (!empty($tempid) && $tempid != 'null') {
            $i++;
            if (isset($datacheck[1])) {
                $tempallids[(int)$datacheck[1]] = (int)$datacheck[1];
            }
            $x++;
        }
    }
}

$compareids = $tempallids;
$compareidstr = !empty($tempallids) ? implode(",", $tempallids) : '0';
$compareidscount = count($compareids) > 0 ? count($compareids) : 0;

// جلب أنواع الأعمال
$view_product = "SELECT * FROM `business_type` WHERE 1";
$userArray = mysqli_query($con, $view_product);
$userArrayRow_Type = array();

while ($userArrayRow = mysqli_fetch_array($userArray, MYSQLI_ASSOC)) {
    $userArrayRow_Type[$userArrayRow['bsntyp_id']] = $userArrayRow['bsntyp_title'];
}
?>

<div class="col-lg-12 compared-container">
    <header style="margin-bottom:30px; border-bottom:1px solid #000; padding-bottom:5px;">
        <h5>منتجاتى المختارة المفضلة (<?php echo $x ?: 0; ?>)</h5>
    </header>
    
    <?php if ($compareidscount > 0): 
        $slideblocks = ceil($compareidscount / 4);
        $count = 1;
        
        // جلب بيانات المنتجات المفضلة
        $qry = "SELECT products.*, 
                       business_profile.*, 
                       user.*, 
                       country.*, 
                       city.* 
                FROM products 
                INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid 
                INNER JOIN user ON user.usr_id = products.pd_uid 
                INNER JOIN country ON user.country = country.cn_id 
                INNER JOIN city ON business_profile.bnsprof_city = city.ct_id 
                WHERE products.pd_id IN ({$compareidstr})";
        
        $resq = mysqli_query($con, $qry);
        $temp_prod_data = array();
        
        while ($rowq = mysqli_fetch_object($resq)) {
            $temp_prod_data[$rowq->pd_id] = $rowq;
        }
        ?>
        
        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox">
                <div class="item active">
                    <?php foreach ($compareids as $k => $v): 
                        if (!isset($temp_prod_data[$v])) continue;
                        $product = $temp_prod_data[$v];
                    ?>
                        <div class="col-md-3 col-sm-6 col-xs-12 compared-box" id="prod_block<?php echo (int)$v; ?>">
                            <div class="text-right">
                                <a href="javascript:void(0);" onclick="delprodfav(<?php echo $uid; ?>, <?php echo (int)$v; ?>)" class="closeCls">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                            
                            <header style="padding:5px;" class="titleLim">
                                <a href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/company/product-details.php?token=<?php echo rand(1000, 9999) . md5((string)$v); ?>&c=<?php echo rand(1000, 9999) . md5($product->bnsprof_id ?? ''); ?>" target="_blank" class="h4" style="font-weight:bold;" title="<?php echo htmlspecialchars($product->pd_title ?? ''); ?>">
                                    <?php echo htmlspecialchars($product->pd_title ?? ''); ?>
                                </a>
                            </header>
                            
                            <figure class="img-box">
                                <?php if (!empty($product->bnsprof_id)): 
                                    // جلب أيقونات العضوية
                                    $sql_icon1 = "SELECT icon_id, p_id FROM plan_member_id WHERE b_id = " . (int)$product->bnsprof_id;
                                    $get_icon1 = mysqli_query($con, $sql_icon1);
                                    $fevrow_icon1 = $get_icon1 ? mysqli_fetch_array($get_icon1, MYSQLI_ASSOC) : null;
                                    
                                    $sql_icon2 = "SELECT * FROM smembership_icon_plan WHERE mp_id = " . (int)($fevrow_icon1['icon_id'] ?? 0);
                                    $get_icon2 = mysqli_query($con, $sql_icon2);
                                    
                                    $sql_icon3 = "SELECT * FROM smembership_plan WHERE mp_id = " . (int)($fevrow_icon1['p_id'] ?? 0);
                                    $get_icon3 = mysqli_query($con, $sql_icon3);
                                    
                                    $sql_icon = "SELECT smembership_plan.mst_icon as sponsericon, 
                                                        plan_member_id.*, 
                                                        smembership_icon_plan.mst_icon as producticon
                                                 FROM smembership_plan, plan_member_id, smembership_icon_plan 
                                                 WHERE smembership_icon_plan.mp_id = plan_member_id.p_id 
                                                   AND smembership_plan.mp_id = plan_member_id.p_id  
                                                   AND plan_member_id.b_id = " . (int)$product->bnsprof_id;
                                    $get_icon = mysqli_query($con, $sql_icon);
                                    
                                    if (mysqli_num_rows($get_icon) > 0):
                                        $fevrow_icon = mysqli_fetch_array($get_icon, MYSQLI_ASSOC);
                                        ?>
                                        <div class="ribbon">
                                            <img src="./admin/images/<?php echo htmlspecialchars($fevrow_icon['sponsericon'] ?? ''); ?>" alt="Sponsor Icon"/>
                                        </div>
                                    <?php elseif (mysqli_num_rows($get_icon3) > 0):
                                        $fevrow_icon3 = mysqli_fetch_array($get_icon3, MYSQLI_ASSOC);
                                        ?>
                                        <div class="ribbon">
                                            <img src="./admin/images/<?php echo htmlspecialchars($fevrow_icon3['mst_icon'] ?? ''); ?>" alt="Membership Icon"/>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="zoomthis">
                                    <img src="upload/myproduct/<?php echo htmlspecialchars(empty($product->pd_large_image) ? ($product->pd_image ?? '') : $product->pd_large_image); ?>" 
                                         class="zoomthis" 
                                         alt="<?php echo htmlspecialchars($product->pd_title ?? ''); ?>" 
                                         title="<?php echo htmlspecialchars($product->pd_title ?? ''); ?>"/>
                                </div>
                            </figure>
                            
                            <section>
                                <table>
                                    <tr>
                                        <td>
                                            <img src="<?php 
                                                if (!empty($product->bnsprof_id) && !empty($fevrow_icon['producticon'])) {
                                                    echo 'admin/images/' . htmlspecialchars($fevrow_icon['producticon']);
                                                } elseif (mysqli_num_rows($get_icon2) > 0) {
                                                    $fevrow_icon2 = mysqli_fetch_array($get_icon2, MYSQLI_ASSOC);
                                                    echo 'admin/images/' . htmlspecialchars($fevrow_icon2['mst_icon'] ?? '');
                                                } else {
                                                    echo 'admin/images/1543744425PROMO-icaon.png';
                                                }
                                            ?>" alt="Company Icon"/>
                                        </td>
                                        <td>
                                            <span style="text-overflow:ellipsis; overflow:hidden;" class="titleLim">
                                                <a href="https://egyptmart.shop/company/profile.php?c=<?php echo rand(1000, 9999) . md5($product->bnsprof_id ?? ''); ?>" target="_blank" class="h5" style="font-weight:bold;" title="<?php echo htmlspecialchars($product->bnsprof_compname ?? ''); ?>">
                                                    <?php echo htmlspecialchars(ucfirst(substr($product->bnsprof_compname ?? '', 0, 20)) . '...'); ?>
                                                </a>
                                            </span>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><img src="images/country_flag/<?php echo htmlspecialchars($product->cn_flag ?? ''); ?>" alt="Flag"/></td>
                                        <td><a href="javascript:void(0)" class="h5"><?php echo htmlspecialchars($product->cn_name ?? ''); ?></a></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="h5">
                                                <?php
                                                $bnsprof_businesstype = $product->bnsprof_businesstype ?? '';
                                                if (!empty($bnsprof_businesstype)) {
                                                    $dataC = explode(",", $bnsprof_businesstype);
                                                    $i = 1;
                                                    foreach ($dataC as $r) {
                                                        $r = trim($r);
                                                        if (!empty($r) && isset($userArrayRow_Type[$r])) {
                                                            echo htmlspecialchars($userArrayRow_Type[$r]);
                                                            if ($i < count($dataC)) {
                                                                echo ", ";
                                                            }
                                                        }
                                                        $i++;
                                                    }
                                                } else {
                                                    echo "غير مدون";
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="txt-bold txt-red" style="font-size:16px;">
                                                <?php echo htmlspecialchars($product->pd_fob_price ?? ''); ?> - <?php echo htmlspecialchars($product->pd_fob_price2 ?? ''); ?>
                                            </span> 
                                            <?php 
                                            $d = getCurrency($product->pd_currency ?? '');
                                            $locale = 'en-US';
                                            $currency = $d;
                                            $fmt = new NumberFormatter($locale . "@currency=$currency", NumberFormatter::CURRENCY);
                                            $symbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
                                            header("Content-Type: text/html; charset=UTF-8;");
                                            echo htmlspecialchars($symbol);
                                            ?>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="txt-bold txt-red" style="font-size:16px;">
                                                <?php echo htmlspecialchars($product->pd_min_order_qty ?? ''); ?>
                                            </span> 
                                            <?php echo htmlspecialchars(measurement_unit($product->pd_unit ?? '')); ?> (أقل طلب)
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><img src="images/mobile.png" alt="Phone"/></td>
                                        <td>
                                            <a href="javascript:void(0)" class="txt-black h4" style="font-weight:bold;">
                                                <?php 
                                                $country_data = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `country` WHERE cn_id = " . (int)($product->country ?? 0)));
                                                echo htmlspecialchars($product->cn_ph ?? '') . '-' . htmlspecialchars($product->mobile1 ?? '');
                                                ?>
                                            </a>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="checkbox" id="<?php echo (int)$v; ?>"  
                                                   rel="prod_block<?php echo (int)$v; ?>" 
                                                   name="suppliersChecks" 
                                                   class="checkbox" 
                                                   rev="<?php echo (int)($product->bnsprof_uid ?? 0); ?>"/>
                                        </td>
                                        <td>
                                            <a id="btn_ajax_send<?php echo (int)$product->pd_id; ?>" 
                                               data-enquiry=""  
                                               class="ajax" 
                                               href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/company/fav_quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($product->bnsprof_id ?? ''); ?>&pid=<?php echo (int)$product->pd_id; ?>&geo=<?php echo htmlspecialchars($product->cn_code ?? ''); ?>&conty=<?php echo (int)($product->cn_id ?? 0); ?>&search=1">
                                                <button type="button" class="btn btn-sm btn-enquiry" style="width:100%; font-weight:bold;" onclick="delprod('fav')">
                                                    إرسل إستفسارك
                                                </button>
                                            </a>
                                        </td>
                                        <td style="display: none;">Chat<img src="images/chat.png" style="width:20px; height:20px; margin-left:5px;"/></td>
                                    </tr>
                                </table>
                            </section>
                        </div>
                        
                        <?php if ($count > 1 && ($count) % 4 == 0): ?>
                            <?php if ($count < $compareidscount): ?>
                                </div><div class="item">
                            <?php else: ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php $count++; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Controls -->
            <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev" style="text-align:left;">
                <span class="slider-left" aria-hidden="false"> <i class="fa fa-chevron-left"></i> </span>
                <span class="sr-only">السابق</span>
            </a>
            <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next" style="text-align:right;">
                <span class="slider-right" aria-hidden="false"> <i class="fa fa-chevron-right"></i> </span>
                <span class="sr-only">التالى</span>
            </a>
        </div>
        
    <?php else: ?>
        <div style="text-align:center">لم يتم إختيار منتجات</div>
    <?php endif; ?>
    
    <!--Slider Close-->
    <div class="row">
        <div class="container">
            <div class="row" style="background-color:#c5e4f8; padding:5px;">
                <div class="col-md-3" style="padding-top:7px;">
                    <span class="h4">منتجاتى المختارة المفضلة</span>
                </div>
                <div class="col-md-2" style="padding-top:7px;">
                    <label>
                        <input style="vertical-align:sub;" type="checkbox" id="select_all" /> إرسل للجميع
                    </label>
                </div>
                <div class="col-md-7">
                    <button class="btn btn-sm border-radius-0 btn-default" onclick="delprod()">
                        <span class="h5">الغاء</span>
                    </button>
                    <button class="btn btn-sm border-radius-0 btn-warning" id="contactAllSupplier">
                        <span class="h5">إرسل إستفسار للجميع</span>
                    </button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

<div class="clearfix"></div>

<script type="text/javascript">
    $('#contactAllSupplier').click(function() {
        var allVals = [];
        var allSuppId = [];
        var checkBoxesCheck = 1;
        
        $.each($("input[name='suppliersChecks']:checked"), function() {
            var suppliersCheckbox = $(this).attr('id');
            var suppliersId = $(this).attr('rev');
            allVals.push(suppliersCheckbox);
            allSuppId.push(suppliersId);
        });
        
        if (allVals.length === 0) {
            alert('الرجاء اختيار منتج واحد على الأقل');
            return;
        }
        
        if (checkBoxesCheck == 1) {
            $.ajax({
                type: 'POST',
                url: 'company/sendmultienqueryform.php?action=sendEnquery&productId=' + allVals + '&suppId=' + allSuppId,
                data: allVals,
                dataType: "html",
                success: function(resultData) {
                    if (resultData < 1) {
                        window.location.href = "http://egyptmart.shop/sign-in.php";
                    } else {
                        createCookie("productids", '');
                        $("#thankYouBlock").html('<span style="color:green;font-size:18px;text-align:center;display: block;"> Your Inquiry has been sent successfully! </span>');
                        $("#thankYouBlock").show();
                    }
                },
                error: function() {
                    alert("حدث خطأ في إرسال الاستفسار");
                }
            });
        }
    });
</script>