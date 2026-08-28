<?php
// company/product-details.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "includes/header.php";

// التحقق من وجود token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("معرف المنتج غير صحيح");
}

$token = substr($_GET['token'], 4);
$token = mysqli_real_escape_string($con, $token);

// جلب بيانات المنتج
$sql = "SELECT * FROM products WHERE md5(pd_id) = '{$token}' AND pd_status = '1'";
$pdresk = mysqli_query($con, $sql);

if (!$pdresk) {
    die("خطأ في استعلام المنتج: " . mysqli_error($con));
}

$pdrowk = mysqli_fetch_object($pdresk);
if (!$pdrowk) {
    die("المنتج غير موجود");
}

// دالة عرض طرق الدفع
function pmNames(string $methods, mysqli $con): string {
    if (empty($methods)) return '';
    
    if (strpos($methods, ',') !== false) {
        $methods = explode(",", $methods);
        $st = "";
        foreach ($methods as $method) {
            $method_id = (int)trim($method);
            $query = mysqli_query($con, "SELECT ph_title FROM payment_method WHERE ph_id = '{$method_id}'");
            if ($query) {
                $fetch = mysqli_fetch_object($query);
                if ($fetch) {
                    $st .= $fetch->ph_title . ", ";
                }
            }
        }
        return rtrim($st, ", ");
    } else {
        $method_id = (int)$methods;
        $query = mysqli_query($con, "SELECT ph_title FROM payment_method WHERE ph_id = '{$method_id}'");
        if ($query) {
            $fetch = mysqli_fetch_object($query);
            return $fetch->ph_title ?? '';
        }
        return '';
    }
}

// معالجة الصور
$image = !empty($pdrowk->pd_image) ? explode(',', $pdrowk->pd_image) : [];
$thumbnail = !empty($image[1]) ? $image[0] : ($pdrowk->pd_image ?? '');
?>
<!-- webcast -->
<style type="text/css">
.brand_name {
    position: relative;
    float: right;
    right: 6px;
}
span.brand_label {
    font-size: 16px;
    font-weight: 600;
}
span.brand_name_title {
    font-size: 16px;
    color: red;
}
</style>

<div id="body">
    <ul class="cb">
        <li id="wideColumn">
            <div id="breadcrumb">
                <ul>
                    <li><a href="<?php echo '/company/index.php?c=' . urlencode($c ?? ''); ?>">Home</a><b>»</b></li>
                    <li><a href="<?php echo '/company/products.php?c=' . urlencode($c ?? ''); ?>">Product</a><b>»</b></li>
                    <li><?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?></li>
                </ul>
            </div>
            <br>

            <div id="h1"><h1><?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?></h1></div><br>  
            
            <div class="ac" style="position: relative;">
                <?php if (!empty($pdrowk->pd_image)): ?>
                    <div class="zoom-box" style="display: inline-block;">
                        <img src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($thumbnail); ?>" 
                             title="<?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?>" 
                             style="max-height:363px; max-width:450px">
                    </div>
                    
                    <?php if (!empty($pdrowk->pd_imagelogo)): 
                        $limg = explode(',', $pdrowk->pd_imagelogo);
                    ?>
                        <div class="zk" style="border: 1px solid #267abf; height: auto; width: 135px; position: absolute; bottom: 6px; left: 113px;">
                            <img style='width: auto; height: auto; max-width: 100%;' 
                                 src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($limg[0] ?? ''); ?>">
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <img src="https://egyptmart.shop/upload/myproduct/noimage.jpg" 
                         title="<?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?>" 
                         alt="<?php echo htmlspecialchars($pdrowk->pd_title ?? ''); ?>" class="bdr">
                    
                    <?php if (!empty($pdrowk->pd_imagelogo)): 
                        $limg = explode(',', $pdrowk->pd_imagelogo);
                    ?>
                        <div class="zk" style="border: 1px solid #267abf; height: 105px; width: 105px; position: absolute; bottom: 6px; left: 113px;">
                            <img style='width: 105px; height: 105px;' 
                                 src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($limg[0] ?? ''); ?>">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- webcast -->
            <?php if (!empty($pdrowk->brand_name)): ?>
                <div class="brand_name">
                    <span class="brand_label">Brand Name:</span>
                    <span class="brand_name_title"><?php echo htmlspecialchars($pdrowk->brand_name); ?></span>
                </div>
            <?php endif; ?>
            
            <br>
            
            <section id="proDet" class="box1 cb">
                <div class="p10px fo">
                    <p class="taj pt10px"><?php echo nl2br(htmlspecialchars($pdrowk->pd_desc ?? '')); ?></p><br>
                </div>
            </section><br>

            <section id="career" class="box1">
                <div class="h2"><h2>تفاصيل جديدة</h2></div>
                <nav class="proSpe">
                    <?php
                    $currencyrow = null;
                    if (!empty($pdrowk->pd_currency)) {
                        $currencysql = mysqli_query($con, "SELECT * FROM country WHERE cn_id = '" . (int)$pdrowk->pd_currency . "'");
                        $currencyrow = mysqli_fetch_object($currencysql);
                    }
                    
                    $unitrow = null;
                    if (!empty($pdrowk->pd_unit)) {
                        $unitsql = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_id = '" . (int)$pdrowk->pd_unit . "'");
                        $unitrow = mysqli_fetch_object($unitsql);
                    }
                    ?>
                    
                    <div style="width:655px; overflow-x:scroll;">
                        <table style="width:100%" border="1" cellpadding="1" cellspacing="1">	
                            <tbody>
                                <?php if (!empty($pdrowk->pd_code)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>كود الصنف</center></th>
                                        <td width="%"><?php echo htmlspecialchars($pdrowk->pd_code); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdrowk->pd_fob_price)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>الـسعـر</center></th>
                                        <td width="%">
                                            <?php 
                                            echo (float)$pdrowk->pd_fob_price . ' ~ ' . (float)$pdrowk->pd_fob_price2; 
                                            echo ' (' . htmlspecialchars($currencyrow->cn_currency ?? '') . ')';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdrowk->pd_stocks)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>المخزون</center></th>
                                        <td width="%">
                                            <?php echo (int)$pdrowk->pd_stocks; ?> <?php echo htmlspecialchars($unitrow->mu_name ?? ''); ?>(s)
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdrowk->pd_pod)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>ميناء التسليم</center></th>
                                        <td width="%"><?php echo htmlspecialchars($pdrowk->pd_pod); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdrowk->pd_pn_capct)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>طاقة الإنتاج</center></th>
                                        <td width="%"><?php echo htmlspecialchars($pdrowk->pd_pn_capct); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdrowk->pd_dlv_time)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>وقـت التسـليم</center></th>
                                        <td width="%"><?php echo htmlspecialchars($pdrowk->pd_dlv_time); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdrowk->pd_pck_dets)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>وصف التغليف</center></th>
                                        <td width="%"><?php echo nl2br(htmlspecialchars($pdrowk->pd_pck_dets)); ?></td>
                                    </tr>
                                <?php endif; ?>
                                
                                <tr>
                                    <th scope="row" width="%"><center>أهمية المنتج</center></th>
                                    <td width="%">
                                        <?php echo ((int)($pdrowk->pd_hot ?? 0) == 0) ? 'Default' : 'Hot'; ?>
                                    </td>
                                </tr>
                                
                                <?php if (!empty($pdrowk->pd_payment)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>شروط الدفع</center></th>
                                        <td width="%">
                                            <?php echo htmlspecialchars(pmNames($pdrowk->pd_payment, $con)); ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pdrowk->pd_pdf_attach)): ?>
                                    <tr>
                                        <th scope="row" width="%"><center>PDF File</center></th>
                                        <td width="%">
                                            <a href="https://egyptmart.shop/upload/productdoc/<?php echo htmlspecialchars($pdrowk->pd_pdf_attach); ?>" target="_blank">
                                                <img src="/images/pdf_icon.png" style="width: 28px; height: 28px; vertical-align: middle;"> PDF
                                            </a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>			
                    </div>
                </nav>
            </section><br><br>
            
            <script src="js/jquery.colorbox.js"></script>
            <link href="css/colorbox.css" type="text/css" rel="stylesheet">
            <script src="js/jquery-1.9.1.min.js"></script>
            <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.3/jquery.colorbox.js"></script>
            
            <script>
            $(document).ready(function() {
                $("#btn_ajax<?php echo (int)($pdrowk->pd_id ?? 0); ?>").colorbox({width: "62%", height: "89%"});
            });
            </script>
            
            <p class="jSea">
                <a href="quotationRequest.php?id=<?php echo rand(1000, 9999) . md5($row->bnsprof_id ?? ''); ?>&pid=<?php echo (int)($pdrowk->pd_id ?? 0); ?>&vform=1" 
                   rel="product-send-inquiry" 
                   class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px" 
                   id="btn_ajax<?php echo (int)($pdrowk->pd_id ?? 0); ?>">
                   تواصل مع الشركة
                </a>
            </p><br><br>
        </li>
        
        <li id="thinColumn">
            <?php include "includes/right.php"; ?>
        </li>
    </ul>
</div>

<?php include "includes/footer.php"; ?>

<link rel="stylesheet" href="../css/jquery.jqZoom.css?v=4.02" type="text/css"/>

<script>
(function($) {
    var SPACING = 15;

    $.fn.jqZoom = function(options) {
        $(this).each(function(i, dom) {
            var me = $(dom);
            _initZoom(me, options.selectorWidth, options.selectorHeight);
            var imgUrl = options && options.zoomImgUrl ? options.zoomImgUrl : me.attr("src");
            _initViewer(me, imgUrl, options.viewerWidth, options.viewerHeight);
        });
    };
    
    var _initZoom = function(target, sWidth, sHeight) {
        var $zoom = $("<div />").addClass("zoom-selector").width(sWidth).height(sHeight);
        target.after($zoom);
        target.closest(".zoom-box").on({
            mousemove: function(e) {
                var mouseX = e.pageX - $(this).offset().left;
                var mouseY = e.pageY - $(this).offset().top;
                var halfSWidth = sWidth / 2, halfSHeight = sHeight / 2;
                var realX, realY;
                
                if (mouseX < halfSWidth) {
                    realX = 0;
                } else if (mouseX + halfSWidth > target.width()) {
                    realX = target.width() - sWidth;
                } else {
                    realX = mouseX - halfSWidth;
                }
                
                if (mouseY < halfSHeight) {
                    realY = 0;
                } else if (mouseY + halfSHeight > target.height()) {
                    realY = target.height() - sHeight;
                } else {
                    realY = mouseY - halfSHeight;
                }
                
                $zoom.css({
                    left: realX,
                    top: realY
                });
                
                var viewerX = realX * ($(this).find(".viewer-box>img").width() - $(this).find(".viewer-box").width()) / (target.width() - sWidth);
                var viewerY = realY * ($(this).find(".viewer-box>img").height() - $(this).find(".viewer-box").height()) / (target.height() - sHeight);
                
                $(this).find(".viewer-box>img").css({
                    left: -viewerX,
                    top: -viewerY
                });
            },
            mouseenter: function() {
                $zoom.css("display", "block");
                $(this).find(".viewer-box").css("display", "block");
                $(this).find(".zoom-text").css("display", "block");
            },
            mouseleave: function() {
                $zoom.css("display", "none");
                $(this).find(".viewer-box").css("display", "none");
                $(this).find(".zoom-text").css("display", "none");
            }
        });
    };
    
    var _initViewer = function(target, imgUrl, vWidth, vHeight) {
        var $viewer = $("<div />").addClass("viewer-box").width(vWidth).height(vHeight);
        var $TextViewer = $("<div />").addClass("zoom-text").width(vWidth).height(Number(vHeight) / 5);
        var $zoomBox = target.closest(".zoom-box");
        
        $viewer.css({
            left: target.width() + SPACING,
            top: 0
        });
        
        $TextViewer.css({
            left: target.width() + SPACING,
            top: vHeight
        });
        
        _setOriginalSize(target, function(oWidth, oHeight) {
            var $img = $("<img src='" + imgUrl + "' />").width(oWidth).height(oHeight);
            $viewer.append($img);
            $TextViewer.text(target.attr('title'));
            target.after($viewer);
            target.after($TextViewer);
        });
    };
    
    var _setOriginalSize = function(target, callback) {
        var newImg = new Image();
        newImg.src = target.attr("src") + "?date=" + new Date().getTime();
        $(newImg).on("load", function() {
            var width = Number(newImg.width) + 500;
            var height = Number(newImg.height) + 500;
            callback(width, height);
        });
    };
})(jQuery);

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