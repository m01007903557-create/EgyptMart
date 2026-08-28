<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "../common.php";

// معالجة معاملات URL
$con_id = isset($_GET['conty']) ? (int)$_GET['conty'] : 0;
$geo_id = isset($_GET['geo']) ? mysqli_real_escape_string($con, $_GET['geo']) : '';

// تحديد شروط البحث حسب الموقع
$sql_pd_ck = "";

if ($con_id > 0 && isset($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = '{$loc_id}'))
        OR
        (pd_preferred_buyer_location = 'any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = '{$loc_id}'))
        OR
        (pd_preferred_buyer_location = 'my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city IN (SELECT ct_id FROM city WHERE ct_cn_id = '{$loc_id}')))
    )";
} elseif (!empty($geo_id)) {
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country = (SELECT cn_id FROM country WHERE cn_code = '{$geo_id}')))
    )";
}

// جلب معاملات URL
$c = isset($_GET['c']) ? mysqli_real_escape_string($con, $_GET['c']) : '';
$bnsprof_id = isset($_GET['id']) ? substr($_GET['id'], 4) : '';
$product_id = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
$vform = isset($_GET['vform']) ? (int)$_GET['vform'] : 0;

// جلب بيانات المنتج
$p_sql = "SELECT * FROM products WHERE pd_id = '{$product_id}'";
$p_res = mysqli_query($con, $p_sql);
$p_row = mysqli_fetch_object($p_res);

// جلب بيانات الشركة
$sql = "SELECT * FROM business_profile, user 
        WHERE bnsprof_uid = usr_id AND md5(bnsprof_id) = '{$bnsprof_id}'";
$res = mysqli_query($con, $sql);
$row = mysqli_fetch_object($res);

// جلب بيانات المستخدم الحالي
$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

$sql_own = "SELECT * FROM user, business_profile 
            WHERE usr_id = '{$uid}' AND bnsprof_uid = usr_id LIMIT 1";
$res_own = mysqli_query($con, $sql_own);
$row_own = mysqli_fetch_object($res_own);

// تحديد رابط الشركة
$company = empty($row->bnsprof_comp_url) ? 'company' : $row->bnsprof_comp_url;
$_SESSION['last_page'] = "company/products.php?c=" . urlencode($c);

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: ../sign-in.php");
    exit;
}

// جلب بيانات المستخدمين والشركات
$userArrayRow_Result = [];
$view_product = "SELECT `user`.*, `business_profile`.*, city.* 
                 FROM city, `business_profile`, `user` 
                 WHERE `business_profile`.`bnsprof_uid` = user.usr_id 
                 AND city.ct_id = `business_profile`.bnsprof_city";
$userArray = mysqli_query($con, $view_product);

while ($userArrayRow = mysqli_fetch_array($userArray, MYSQLI_ASSOC)) {
    $userArrayRow_Result[(int)$userArrayRow['usr_id']] = $userArrayRow;
}

// جلب المنتج التالي والسابق
$run_pre = null;
$run_nxt = null;
$data = null;
$data1 = null;

// المنتج التالي (أقل id)
if (!empty($_GET['keywords'])) {
    $keywords = mysqli_real_escape_string($con, $_GET['keywords']);
    
    $nxt_id = "SELECT * FROM products, measurement_unit, country 
               WHERE mu_id = pd_unit 
               AND (pd_title LIKE '%{$keywords}%')
               AND pd_currency = cn_id
               {$sql_pd_ck}
               AND pd_status = '1'
               AND pd_image != ''
               AND pd_id < {$product_id}
               ORDER BY products.pd_id DESC 
               LIMIT 1";
} else {
    $nxt_id = "SELECT * FROM products, measurement_unit, country 
               WHERE mu_id = pd_unit 
               AND pd_currency = cn_id
               {$sql_pd_ck}
               AND products.pd_status = '1'
               AND products.pd_image != ''";
    
    if ($vform == 1) {
        $nxt_id .= " AND products.pd_uid = " . (int)($p_row->pd_uid ?? 0);
    }
    
    $nxt_id .= " AND products.pd_id < {$product_id} 
                 ORDER BY products.pd_id DESC 
                 LIMIT 1";
}

$run_querynxt = mysqli_query($con, $nxt_id);
if ($run_querynxt && mysqli_num_rows($run_querynxt) > 0) {
    $run_nxt = mysqli_fetch_object($run_querynxt);
    if ($run_nxt) {
        $data = $userArrayRow_Result[(int)($run_nxt->pd_uid ?? 0)] ?? null;
    }
}

// المنتج السابق (أكبر id)
if (!empty($_GET['keywords'])) {
    $keywords = mysqli_real_escape_string($con, $_GET['keywords']);
    
    $pre_id = "SELECT * FROM products, measurement_unit, country 
               WHERE mu_id = pd_unit 
               AND (pd_title LIKE '%{$keywords}%')
               AND pd_currency = cn_id
               {$sql_pd_ck}
               AND pd_status = '1'
               AND pd_image != ''
               AND pd_id > {$product_id}
               ORDER BY products.pd_id DESC 
               LIMIT 1";
} else {
    $pre_id = "SELECT * FROM products, measurement_unit, country 
               WHERE mu_id = pd_unit 
               AND pd_currency = cn_id
               {$sql_pd_ck}
               AND products.pd_status = '1'
               AND products.pd_image != ''";
    
    if ($vform == 1) {
        $pre_id .= " AND products.pd_uid = " . (int)($p_row->pd_uid ?? 0);
    }
    
    $pre_id .= " AND products.pd_id > {$product_id} 
                 LIMIT 1";
}

$run_querypre = mysqli_query($con, $pre_id);
if ($run_querypre && mysqli_num_rows($run_querypre) > 0) {
    $run_pre = mysqli_fetch_object($run_querypre);
    if ($run_pre) {
        $data1 = $userArrayRow_Result[(int)($run_pre->pd_uid ?? 0)] ?? null;
    }
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <link type="text/css" rel="stylesheet" href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/css/main-v2.css">
    <link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/css/dir-style-8.css" type="text/css" rel="stylesheet">
    <link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/css/overlay-v2.css" type="text/css" rel="stylesheet">
    <link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/css/bl_form_temp5.css" rel="stylesheet" type="text/css">
    
    <script src="js/jquery-1.2.1.min.js"></script>
    <script src="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/js/jquery.colorbox.js"></script>
    <link href="https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST'] ?? ''); ?>/css/colorbox.css" type="text/css" rel="stylesheet">
    
    <?php if (isset($_GET['search']) && $_GET['search'] == 1): ?>
        <script>
        $(document).ready(function() {
            $('body').on('click', '.ajax', function(event) {
                parent.$.colorbox({
                    href: $(this).attr('href'),
                    open: true,
                    iframe: true,
                    width: '750px',
                    height: '650px'
                });
                parent.$.colorbox.close();
                return false;
            });
        });
        </script>
    <?php endif; ?>
    
    <script>
    $(document).ready(function() {
        $(document).on('keyup', '#msg_message', function(e) {
            var maxLength = 2000;
            var text = $(this).val();
            var length = text.length;
            
            if (length > maxLength) {
                $(this).val(text.substring(0, maxLength));
            }
            
            var remaining = maxLength - $(this).val().length;
            $("#charCount").text(remaining);
        });
    });
    
    function sendEnquiry() {
        // التحقق من الحقول الإجبارية الجديدة
        var quantity_from = $('#quantity_from').val();
        var quantity_to = $('#quantity_to').val();
        var msg_message = $('#msg_message').val();
        var msg = "";
        var valid = true;
        
        // التحقق من الكمية (من)
        if (!quantity_from || quantity_from < 1) {
            msg = "الرجاء إدخال الكمية التقريبية المطلوبة (من)";
            $('#quantity_from').focus();
            valid = false;
        }
        // التحقق من الكمية (إلى)
        else if (!quantity_to || quantity_to < 1) {
            msg = "الرجاء إدخال الكمية التقريبية المطلوبة (إلى)";
            $('#quantity_to').focus();
            valid = false;
        }
        // التحقق من أن "من" أقل من أو تساوي "إلى"
        else if (parseInt(quantity_from) > parseInt(quantity_to)) {
            msg = "الكمية 'من' يجب أن تكون أقل من أو تساوي الكمية 'إلى'";
            $('#quantity_to').focus();
            valid = false;
        }
        // التحقق من الرسالة
        else if (!msg_message || msg_message.trim() === '') {
            msg = "من فضلك أوصف طلبك بتفصيل مناسب";
            valid = false;
        } else if (msg_message.length < 50) {
            msg = "! إكتب 50 حرف على الأقل لوصف طلبك";
            $('#msg_message').focus();
            valid = false;
        }
        
        if (!valid) {
            alert(msg);
        } else {
            $('#msg_message').attr('readonly', 'readonly');
            $('#quantity_from').attr('readonly', 'readonly');
            $('#quantity_to').attr('readonly', 'readonly');
            $('#b_sub').css("display", "none");
            $('#loadings').css("display", "block");
            
            console.log("جاري الإرسال إلى: ../ajax-file/sendQuotationMessage.php");
            console.log("البيانات:", {
                quantity_from: quantity_from,
                quantity_to: quantity_to,
                msg_pro_unit: $('#prd_unit').val(),
                msg_pro_moq: $('#prd_moq').val(),
                msg_pro_name: $('#prd_name').val(),
                msg_from: $('#msg_from').val(),
                msg_img: $('#prd_image').val(),
                msg_to: $('#msg_to').val(),
                msg_subject: $('#msg_subject').val(),
                msg_message: msg_message
            });
            
            $.post("../ajax-file/sendQuotationMessage.php", {
                quantity_from: quantity_from,
                quantity_to: quantity_to,
                msg_pro_unit: $('#prd_unit').val(),
                msg_pro_moq: $('#prd_moq').val(),
                msg_pro_name: $('#prd_name').val(),
                msg_from: $('#msg_from').val(),
                msg_img: $('#prd_image').val(),
                msg_to: $('#msg_to').val(),
                msg_subject: $('#msg_subject').val(),
                msg_message: msg_message
            }, function(data) {
                if (data == 1) {
                    setTimeout(function() {
                        $("#loadings").css("display", "none");
                        $("#succ_results").css("display", "block");
                        // إعادة تعيين الحقول
                        $('#msg_message').val('').removeAttr('readonly');
                        $('#quantity_from').val('').removeAttr('readonly');
                        $('#quantity_to').val('').removeAttr('readonly');
                        $('#charCount').text('2000');
                    }, 500);
                } else {
                    setTimeout(function() {
                        $("#loadings").css("display", "none");
                        $("#err_results").css("display", "block");
                        $('#msg_message').removeAttr('readonly');
                        $('#quantity_from').removeAttr('readonly');
                        $('#quantity_to').removeAttr('readonly');
                    }, 500);
                }
            }).fail(function() {
                setTimeout(function() {
                    $("#loadings").css("display", "none");
                    $("#err_results").css("display", "block");
                    $('#msg_message').removeAttr('readonly');
                    $('#quantity_from').removeAttr('readonly');
                    $('#quantity_to').removeAttr('readonly');
                }, 500);
            });
        }
    }
    </script>
    
    <style>
    .snd-enq {
        box-shadow: 0pt 1px 5px rgb(170, 170, 170);
        font-family: Arial, Helvetica, sans-serif;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        color: #fff;
        border: 1px solid rgb(24, 143, 205);
        border-radius: 6px;
        padding: 5px 20px;
        cursor: pointer;
    }
    .quantity-group {
        margin-bottom: 15px;
        padding: 10px;
        background: #f9f9f9;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .quantity-group label {
        font-weight: bold;
        color: #466da0;
        margin-bottom: 8px;
        display: block;
    }
    .quantity-row {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    .quantity-item {
        flex: 1;
    }
    .quantity-item input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .quantity-sep {
        font-size: 18px;
        font-weight: bold;
        color: #466da0;
    }
    .help-text {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    .required-star {
        color: red;
    }
    </style>
</head>

<body>
    <div class="ov-base">
        <div class="neff2-nw">
            <p style="width: 635px;">
                إرسل إستفسار إلى : 
                <span class="co-name">&nbsp;<?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?></span>
            </p>
        </div>
        
        <div class="neff2-nw">
            <p style="width: 635px;">
                الإستفسار عن : 
                <span class="co-name"><?php echo htmlspecialchars(get_product_detail($product_id, 'pd_title')); ?></span>
            </p>
        </div>
        
        <div class="bo k9 err-msg" id="errmsg" style="display: none;"></div>
        
        <form name="dataform" class="mp0-nw" method="post" action="">
            <input type="hidden" id="prd_image" name="prd_image" value="<?php echo htmlspecialchars($p_row->pd_image ?? ''); ?>">
            <input type="hidden" id="prd_moq" name="prd_moq" value="<?php echo (int)($p_row->pd_min_order_qty ?? 0); ?>">
            <input type="hidden" id="prd_unit" name="prd_unit" value="<?php echo (int)($p_row->pd_unit ?? 0); ?>">
            <input type="hidden" id="prd_name" name="prd_name" value="<?php echo htmlspecialchars(get_product_detail($product_id, 'pd_title')); ?>">
            <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $uid; ?>">
            <input type="hidden" id="msg_to" name="msg_to" value="<?php echo (int)($row->usr_id ?? 0); ?>">
            <input type="hidden" id="msg_subject" name="msg_subject" value="Product Enquiry">
            
            <br>
            
            <div style="width: 100%; text-align: center;">
                <!-- السهم الأيسر (المنتج السابق) -->
                <?php if ($run_pre): ?>
                    <?php
                    $prev_url_id = $vform == 1 ? $_GET['id'] : (rand(1000, 9999) . md5((string)($data1['bnsprof_id'] ?? '')));
                    $prev_href = "https://" . ($_SERVER['HTTP_HOST'] ?? '') . "/company/quotationRequest.php?id={$prev_url_id}&pid=" . (int)($run_pre->pd_id ?? 0) . "&geo={$geo_id}&conty={$con_id}" . ($vform == 1 ? '&vform=1' : '');
                    ?>
                    <a data-prev="" class="ajax" href="<?php echo $prev_href; ?>" <?php echo ($vform == 1) ? 'style="display:none;"' : ''; ?>>
                        <img src="https://egyptmart.shop/company/images/2.png" style="height: 61px; float: left; margin-top: 44px; cursor: pointer;" />
                    </a>
                <?php endif; ?>

                <img height="163" width="250" 
                     src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($p_row->pd_image ?? ''); ?>" 
                     alt="Product Image">
                
                <!-- السهم الأيمن (المنتج التالي) -->
                <?php if ($run_nxt): ?>
                    <?php
                    $next_url_id = $vform == 1 ? $_GET['id'] : (rand(1000, 9999) . md5((string)($data['bnsprof_id'] ?? '')));
                    $next_href = "https://" . ($_SERVER['HTTP_HOST'] ?? '') . "/company/quotationRequest.php?id={$next_url_id}&pid=" . (int)($run_nxt->pd_id ?? 0) . "&geo={$geo_id}&conty={$con_id}" . ($vform == 1 ? '&vform=1' : '');
                    ?>
                    <a data-next="" class="ajax" href="<?php echo $next_href; ?>" <?php echo ($vform == 1) ? 'style="display:none;"' : ''; ?>>
                        <img src="https://egyptmart.shop/company/images/play.png" style="height: 61px; float: right; margin-top: 44px; cursor: pointer;" />
                    </a>
                <?php endif; ?>
            </div>
            
            <br>
            
            <!-- ========== حقل الكمية الجديد (إجباري) ========== -->
            <div class="quantity-group">
                <label>الكمية التقريبية المطلوبة <span class="required-star">*</span></label>
                <div class="quantity-row">
                    <div class="quantity-item">
                        <input type="number" name="quantity_from" id="quantity_from" 
                               class="form-control" placeholder="من" min="1" 
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
                    </div>
                    <div class="quantity-sep">إلى</div>
                    <div class="quantity-item">
                        <input type="number" name="quantity_to" id="quantity_to" 
                               class="form-control" placeholder="إلى" min="1" 
                               style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;" required>
                    </div>
                </div>
                <div class="help-text">أدخل النطاق التقريبي للكمية التي ترغب في شرائها (مثال: 100 إلى 500)</div>
            </div>
            
            <div class="enn1-nw nef4-nw">
                <textarea id="msg_message" name="msg_message" style="resize: none;" class="nef10-nw" tabindex="1"></textarea>
                <div class="nef9-nw nef12-nw"></div>
                <div class="nef9-nw nef12-nw" style="text-align:right; width:99%;" id="Description-status">
                    Remaining Characters:&nbsp;<b><strong id="charCount">2000</strong></b>
                    <div class="m2"></div>
                </div>
            </div>
            
            <fieldset style="height: 108px; border: 1px solid rgb(134, 182, 217); margin-top: 2px; width: 178px; width: 170px;">
                <legend style="font-size: 13px; color:#017BBC; text-align: center; width:89%; margin-bottom:2px;">
                    <strong>أكتب تفاصيل طلبك</strong>
                </legend>
                <div class="f1-nw" style="color:#055985;">
                    <ul>
                        <li class="li-1">متطلبات المنتج</li>
                        <li class="li-1">التفاصيل المطلوبة</li>
                        <li class="li-1">التعبئة والتسليم</li>
                        <li class="li-1">تفاصيل شركتك الخ</li>
                    </ul>
                </div>
            </fieldset>
            
            <div class="clr-nw" style="margin-bottom:2px"></div>
            
            <div>
                <div class="w12" style="font-size:14px; padding: 5px; border-bottom: 1px solid rgb(134, 182, 217); margin: 5px; width:658px; color: rgb(15, 84, 135); background-color: rgb(241, 241, 241); float:left; font-weight:700" align="LEFT">
                    <b>: تفاصيل الإتصال الخاصة بك</b>
                </div>
                
                <div class="text" style="padding-top: 5px; border: 4px double rgb(134, 182, 217); padding-bottom: 10px; padding-left: 10px; background-color: rgb(241, 241, 241);" align="LEFT">
                    <div style="clear:both"></div>
                    
                    <div id="yourcontactinfo">
                        <div class="text" style="padding-top:5px;" align="LEFT">
                            <?php 
                            echo htmlspecialchars(
                                trim(($row_own->name_prefix ?? '') . ' ' . ($row_own->fname ?? '') . ' ' . ($row_own->lname ?? ''))
                            ); 
                            ?>
                            <br>
                            <?php echo htmlspecialchars($row_own->bnsprof_compname ?? ''); ?>
                            <br>
                            
                            <?php if (!empty($row_own->bnsprof_address1)): ?>
                                <?php echo htmlspecialchars($row_own->bnsprof_address1); ?><br>
                            <?php endif; ?>
                            
                            <?php if (!empty($row_own->bnsprof_address2)): ?>
                                <?php echo htmlspecialchars($row_own->bnsprof_address2); ?><br>
                            <?php endif; ?>
                            
                            <?php
                            $address_parts = [];
                            
                            if (!empty($row_own->bnsprof_city) && $row_own->bnsprof_city != '0') {
                                $address_parts[] = get_city_name((int)$row_own->bnsprof_city);
                            }
                            if (!empty($row_own->bnsprof_state) && $row_own->bnsprof_state != '0') {
                                $address_parts[] = get_state_name((int)$row_own->bnsprof_state);
                            }
                            if (!empty($row_own->country) && $row_own->country != '0') {
                                $address_parts[] = get_country_name((int)$row_own->country);
                            }
                            
                            echo htmlspecialchars(implode(', ', array_filter($address_parts)));
                            ?>
                            <br>
                            
                            Email: <?php echo htmlspecialchars($row_own->email ?? ''); ?>
                            
                            <?php if (!empty($row_own->mobile1) && $row_own->mobile1 != '0'): ?>
                                <br>
                                Mobile / Cell Phone: +(<?php echo htmlspecialchars($row_own->country_ph_code ?? ''); ?>)-<?php echo htmlspecialchars($row_own->mobile1); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div style="font-size: 12px; margin-left: 0px; padding: 0px 0pt 10px 15px;" align="LEFT"><br></div>
            </div>
            
            <div class="clr-nw"></div>
            
            <div id="nu_frm">
                <div class="nef4-nw" align="center">
                    <div style="display: block;" id="b_sub">
                        <input name="submit_member" id="button" value="إرسل إستفسارك" 
                               class="snd-enq" type="button" onclick="sendEnquiry();"/>
                    </div>
                    
                    <div id="loadings" style="display:none; padding-left:5px; color:#1045B0; padding-top:16px;" class="g9 bo off">
                        <img class="loading" src="../images/loading-small.gif" alt="loading" height="16" width="16">
                        <b>... رجاء الانتظار</b>
                    </div>
                    
                    <div id="succ_results" style="display:none; padding-left:5px; color:#009700; padding-top:16px;" class="g9 bo off">
                        ... تم إرسال رسالتك بنجاح
                    </div>
                    
                    <div id="err_results" style="display:none; padding-left:5px; color:#F00; padding-top:16px;" class="g9 bo off">
                        .. حدث خطأ بالإرسال .. رجاء المحاولة لاحقا
                    </div>
                </div>
            </div>
        </form>
        
        <div class="clr-nw"></div>
    </div>
</body>
</html>