<?php
/**
 * File: compare_middle_content.php
 * Version: PHP 8.3
 * Description: محتوى صفحة مقارنة المنتجات - يعرض المنتجات المختارة للمقارنة في واجهة متحركة
 */

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// تأكد من وجود هذه الأسطر في بداية الملف
require_once __DIR__ . '/lib/function.php';


// باقي الكود القديم هنا...
// التحقق من وجود معرف المستخدم في الجلسة (اختياري)
$logged_in_user = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
?>

<!-- تضمين مكتبات Colorbox -->
<script src="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/js/jquery.colorbox.js"></script>
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/css/colorbox.css" type="text/css" rel="stylesheet">
<link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/css/overlay-v2.css" type="text/css" rel="stylesheet">

<style>
.zoomthis > img {
    width: 250px !important;
    height: 250px;
    object-fit: cover;
}
@media (min-width: 992px) {
    .compared-box {
        width: 22% !important;
        margin-left: 18px;
        margin-bottom: 20px;
        float: right;
    }
}
.zk {
    border: 1px solid #267abf;
    height: auto;
    width: 100px;
    position: absolute;
    bottom: 3px;
    left: 1px;
    background: rgba(255,255,255,0.8);
    padding: 2px;
}
.zk img {
    height: 45px;
    width: auto;
    max-width: 100%;
}
</style>

<script>
$(document).on('click', '.ajax', function() {
    $.colorbox({
        href: $(this).attr('href'),
        open: true,
        iframe: true,
        width: '750px',
        height: '600px'
    });
    return false;
});

$(document).ready(function() {
    $('#contactAllSupplier').click(function() {
        var allVals = [];
        var allSuppId = [];
        var checkBoxesCheck = 1;
        
        $.each($("input[name='suppliersChecks']:checked"), function() {
            var suppliersCheckbox = $(this).attr('rel');
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
                        window.location.href = "http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/sign-in.php";
                    } else {
                        createCookie("productids", '');
                        $("#thankYouBlock").show();
                        alert("شكراً، سوف يتصل بك المورد في أقرب فرصة");
                        window.location.href = "http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>";
                    }
                },
                error: function() {
                    alert("حدث خطأ في إرسال الاستفسار");
                }
            });
        }
    });
    
    // تحديد الكل
    $('#select_all').change(function() {
        var status = this.checked;
        $('.checkbox').each(function() {
            this.checked = status;
        });
    });
});

/**
 * حذف منتج من المقارنة
 * @param {number} id - معرف المنتج
 */
function delprod(id) {
    if (confirm("هل أنت متأكد من حذف هذا المنتج من المقارنة؟")) {
        var currentCookie = readCookie("productids") || '';
        var newCookie = currentCookie.replace(',' + id, '').replace(id + ',', '').replace(id, '');
        
        if (newCookie === '') {
            eraseCookie("productids");
        } else {
            createCookie("productids", newCookie);
        }
        
        $('#prod_block' + id).fadeOut(500, function() {
            $(this).remove();
            if ($('.compared-box').length === 0) {
                location.reload();
            }
        });
    }
}

/**
 * إنشاء كوكي
 * @param {string} name - اسم الكوكي
 * @param {string} value - قيمة الكوكي
 * @param {number} days - عدد أيام الصلاحية
 */
function createCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

/**
 * قراءة كوكي
 * @param {string} name - اسم الكوكي
 * @return {string|null} قيمة الكوكي أو null
 */
function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

/**
 * حذف كوكي
 * @param {string} name - اسم الكوكي
 */
function eraseCookie(name) {
    createCookie(name, "", -1);
}
</script>

<?php
// معالجة معرفات المنتجات من الكوكيز
$product_ids = isset($_COOKIE['productids']) ? $_COOKIE['productids'] : '';

// إذا كان الكوكي فارغاً، إعادة التوجيه إلى الصفحة الرئيسية
if (empty($product_ids)) {
    ?>
    <script>
    window.location.href = "http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>";
    </script>
    <?php
    exit();
}

// تنظيف سلسلة المعرفات
$compareidstr = str_replace("null,", "", $product_ids);
$compareidstr = trim($compareidstr, ',');

// تحويل إلى مصفوفة
$compareids = explode(",", $compareidstr);
$compareids = array_filter($compareids, function($value) {
    return !empty($value) && is_numeric($value);
});

$compareidscount = count($compareids);

// إذا كانت المصفوفة فارغة، إعادة التوجيه
if ($compareidscount == 0) {
    ?>
    <script>
    window.location.href = "http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>";
    </script>
    <?php
    exit();
}

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
        <h5>منتجـات المقـارنة (<?php echo $compareidscount; ?>)</h5>
    </header>
    
    <?php if ($compareidscount > 0): 
        // تنظيف المعرفات مرة أخرى للاستعلام
        $clean_ids = array_map('intval', $compareids);
        $compareidstr = implode(",", $clean_ids);
        
        // جلب بيانات المنتجات
        $qry = "SELECT products.*, 
                       business_profile.*, 
                       user.*, 
                       c.* 
                FROM products 
                INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid 
                INNER JOIN user ON user.usr_id = products.pd_uid 
                INNER JOIN country AS c ON user.country = c.cn_id  
                WHERE products.pd_id IN ({$compareidstr})";
        
        $resq = mysqli_query($con, $qry);
        $temp_prod_data = array();
        
        while ($rowq = mysqli_fetch_object($resq)) {
            $temp_prod_data[$rowq->pd_id] = $rowq;
        }
        
        $count = 1;
        ?>
        
        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox">
                <div class="item active">
                    <?php foreach ($compareids as $k => $v): 
                        if (empty($v) || !isset($temp_prod_data[$v])) continue;
                        $product = $temp_prod_data[$v];
                    ?>
                        <div class="col-md-3 compared-box" id="prod_block<?php echo (int)$v; ?>">
                            <div class="text-right">
                                <a onclick="delprod(<?php echo (int)$v; ?>)" class="closeCls" style="cursor:pointer;">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                            
                            <header style="padding:5px;" class="titleLim">
                                <a class="h4" style="font-weight:bold;" 
                                   href="http://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/company/product-details.php?token=<?php echo rand(1000, 9999) . md5((string)$v); ?>&c=<?php echo rand(1000, 9999) . md5($product->bnsprof_id ?? ''); ?>" 
                                   target="_blank" 
                                   title="<?php echo htmlspecialchars($product->pd_title ?? ''); ?>">
                                    <?php echo htmlspecialchars($product->pd_title ?? ''); ?>
                                </a>
                            </header>
                            
                            <figure class="img-box" style="position:relative;">
                                <?php if (!empty($product->bnsprof_id)): 
                                    // جلب أيقونات العضوية
                                    $sql_icon1 = "SELECT icon_id, p_id FROM plan_member_id WHERE b_id = " . (int)$product->bnsprof_id . " LIMIT 1";
                                    $get_icon1 = mysqli_query($con, $sql_icon1);
                                    $fevrow_icon1 = $get_icon1 ? mysqli_fetch_array($get_icon1, MYSQLI_ASSOC) : null;
                                    
                                    $sql_icon2 = "SELECT * FROM smembership_icon_plan WHERE mp_id = " . (int)($fevrow_icon1['icon_id'] ?? 0);
                                    $get_icon2 = mysqli_query($con, $sql_icon2);
                                    $fevrow_icon2 = $get_icon2 ? mysqli_fetch_array($get_icon2, MYSQLI_ASSOC) : null;
                                    
                                    $sql_icon3 = "SELECT * FROM smembership_plan WHERE mp_id = " . (int)($fevrow_icon1['p_id'] ?? 0);
                                    $get_icon3 = mysqli_query($con, $sql_icon3);
                                    
                                    $sql_icon = "SELECT smembership_plan.mst_icon as sponsericon, 
                                                        plan_member_id.*, 
                                                        smembership_icon_plan.mst_icon as producticon
                                                 FROM smembership_plan, plan_member_id, smembership_icon_plan 
                                                 WHERE smembership_icon_plan.mp_id = plan_member_id.p_id 
                                                   AND smembership_plan.mp_id = plan_member_id.p_id  
                                                   AND plan_member_id.b_id = " . (int)$product->bnsprof_id . " 
                                                 LIMIT 1";
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
                                
                                <?php 
                                $pimg1 = explode(',', $product->pd_image ?? '');
                                if (!empty($pimg1[0])): 
                                ?>
                                    <div class="zoomthis">
                                        <img src="/upload/myproduct/<?php echo htmlspecialchars($pimg1[0]); ?>" 
                                             alt="<?php echo htmlspecialchars($product->pd_title ?? ''); ?>" 
                                             title="<?php echo htmlspecialchars($product->pd_title ?? ''); ?>"/>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($product->pd_imagelogo)): 
                                    $limg1 = explode(',', $product->pd_imagelogo);
                                    if (!empty($limg1[0])):
                                ?>
                                    <div class="zk">
                                        <img src="/upload/myproduct/<?php echo htmlspecialchars($limg1[0]); ?>" alt="Logo"/>
                                    </div>
                                <?php endif; endif; ?>
                            </figure>
                            
                            <section>
                                <table style="width:100%;">
                                    <tr>
                                        <td style="width:30px;">
                                            <img src="<?php 
                                                if (!empty($fevrow_icon['producticon'])) {
                                                    echo 'admin/images/' . htmlspecialchars($fevrow_icon['producticon']);
                                                } elseif (!empty($fevrow_icon2['mst_icon'])) {
                                                    echo 'admin/images/' . htmlspecialchars($fevrow_icon2['mst_icon']);
                                                } else {
                                                    echo 'images/4.png';
                                                }
                                            ?>" alt="Company Icon" style="max-width:30px;"/>
                                        </td>
                                        <td>
                                            <span style="text-overflow:ellipsis; overflow:hidden;" class="titleLim">
                                                <a href="http://egyptmart.online/company/profile.php?c=<?php echo rand(1000, 9999) . md5($product->bnsprof_id ?? ''); ?>" 
                                                   target="_blank" 
                                                   class="h5" 
                                                   style="font-weight:bold;" 
                                                   title="<?php echo htmlspecialchars($product->bnsprof_compname ?? ''); ?>">
                                                    <?php echo htmlspecialchars(ucfirst(substr($product->bnsprof_compname ?? '', 0, 20)) . '...'); ?>
                                                </a>
                                            </span>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td><img src="images/country_flag/<?php echo htmlspecialchars($product->cn_flag ?? ''); ?>" alt="Flag"/></td>
                                        <td><span class="h5"><?php echo htmlspecialchars($product->cn_name ?? ''); ?></span></td>
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
                                                    echo "Not available";
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="txt-bold txt-red" style="font-size:16px;">
                                                <?php echo htmlspecialchars($product->pd_fob_price ?? ''); ?> - <?php echo htmlspecialchars($product->pd_fob_price2 ?? ''); ?>
                                            </span>
                                            <?php
                                            if (!empty($product->pd_currency)) {
                                                $d = getCurrency($product->pd_currency);
                                                $locale = 'en-US';
                                                $currency = $d;
                                                $fmt = new NumberFormatter($locale . "@currency=$currency", NumberFormatter::CURRENCY);
                                                echo htmlspecialchars($fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL));
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="txt-bold txt-red" style="font-size:16px;">
                                                <?php echo htmlspecialchars($product->pd_min_order_qty ?? ''); ?>
                                            </span>
                                            <?php echo htmlspecialchars(measurement_unit($product->pd_unit ?? '')); ?> (أقل كمية)
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td><img src="images/mobile.png" alt="Phone"/></td>
                                        <td>
                                            <a href="javascript:void(0)" class="txt-black h4" style="font-weight:bold;">
                                                <?php 
                                                $country_data = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM `country` WHERE cn_id = " . (int)($product->country ?? 0)));
                                                echo htmlspecialchars($product->cn_ph ?? '') . '-' . htmlspecialchars(user_info($product->bnsprof_uid ?? 0, 'mobile1'));
                                                ?>
                                            </a>
                                        </td>
                                    </tr>
                                    
                                    <tr>
                                        <td>
                                            <input type="checkbox" 
                                                   value="<?php echo (int)$v; ?>" 
                                                   class="checkbox" 
                                                   name="suppliersChecks" 
                                                   id="suppliers<?php echo (int)$v; ?>" 
                                                   rel="<?php echo (int)$v; ?>" 
                                                   rev="<?php echo (int)($product->bnsprof_uid ?? 0); ?>"/>
                                        </td>
                                        <td>
                                            <a id="btn_ajax_send<?php echo (int)$product->pd_id; ?>" 
                                               data-enquiry="" 
                                               class="ajax" 
                                               href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/company/comp_quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($product->bnsprof_id ?? ''); ?>&pid=<?php echo (int)$product->pd_id; ?>&geo=<?php echo htmlspecialchars($product->cn_code ?? ''); ?>&conty=<?php echo (int)($product->cn_id ?? 0); ?>&search=1">
                                                <button type="button" class="btn btn-sm btn-enquiry" style="width:100%; font-weight:bold;">
                                                    إرسل إستفسارك
                                                </button>
                                            </a>
                                        </td>
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
        <div style="text-align:center; padding:50px; font-size:18px; color:#666;">
            لم يتم اختيار منتجات للمقارنة
        </div>
    <?php endif; ?>
    
    <!--Slider Close-->
    <div class="row">
        <div class="container">
            <div class="row" style="background-color:#fff; padding:5px; margin-top:20px;">
                <div class="col-md-2" style="padding-top:7px;">
                    <span class="h4">إرسل للجميع</span>
                </div>
                <div class="col-md-2" style="padding-top:7px;">
                    <label>
                        <input type="hidden" name="loggedInUser" id="loggedInUser" value="<?php echo $logged_in_user; ?>" />
                        <input type="checkbox" style="vertical-align:sub;" id="select_all"/> إختار الجميع
                    </label>
                </div>
                <div class="col-md-8">
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
<div id="thankYouBlock" style="display:none; text-align:center; padding:20px; color:green; font-size:18px;">
    شكراً لك! تم إرسال استفسارك بنجاح.
</div>