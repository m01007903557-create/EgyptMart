<?php
/**
 * File: product-edit.php
 * Description: Edit Product Details (Basic Info, Images, Additional Details)
 * Version: 2.0.0 (PHP 8.3 Compatible) - LTR Layout
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// Register current page
$_SESSION['last_page'] = "product-edit.php";

// Check login
$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
if ($uid == 0) {
    header("Location: sign-in.php");
    exit;
}

// Check token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: product-list.php");
    exit;
}

$token = substr($_GET['token'], 4);

global $con;

// =============================================
// Reset push to top status
// =============================================
$reset_push_sql = "UPDATE products SET pd_pushed_top = '0' WHERE MD5(pd_id) = ?";
$stmt_reset = mysqli_prepare($con, $reset_push_sql);
mysqli_stmt_bind_param($stmt_reset, 's', $token);
mysqli_stmt_execute($stmt_reset);
mysqli_stmt_close($stmt_reset);

// =============================================
// Fetch product data
// =============================================
$product_sql = "SELECT p.*, pc.pc_parent_id 
                FROM products p
                INNER JOIN product_category pc ON p.pd_subcat_id = pc.pc_id
                WHERE MD5(p.pd_id) = ? 
                AND p.pd_uid = ?
                LIMIT 1";

$stmt_product = mysqli_prepare($con, $product_sql);
mysqli_stmt_bind_param($stmt_product, 'si', $token, $uid);
mysqli_stmt_execute($stmt_product);
$product_result = mysqli_stmt_get_result($stmt_product);
$product_row = mysqli_fetch_object($product_result);
mysqli_stmt_close($stmt_product);

if (!$product_row) {
    header("Location: product-list.php");
    exit;
}

// Fetch main category
$main_cat_sql = "SELECT pc_parent_id FROM product_category WHERE pc_id = ? AND pc_status = '1' LIMIT 1";
$stmt_main_cat = mysqli_prepare($con, $main_cat_sql);
mysqli_stmt_bind_param($stmt_main_cat, 'i', $product_row->pc_parent_id);
mysqli_stmt_execute($stmt_main_cat);
$main_cat_result = mysqli_stmt_get_result($stmt_main_cat);
$main_cat_row = mysqli_fetch_object($main_cat_result);
$main_cat_id = $main_cat_row ? (int)$main_cat_row->pc_parent_id : 0;
mysqli_stmt_close($stmt_main_cat);

// Fetch main categories
$main_categories = [];
$main_cat_sql = "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = 0 AND pc_status = '1' ORDER BY pc_order, pc_name ASC";
$main_cat_result = mysqli_query($con, $main_cat_sql);
while ($row = mysqli_fetch_assoc($main_cat_result)) {
    $main_categories[] = $row;
}

// Fetch sub categories (level 1)
$sub_categories = [];
if ($main_cat_id > 0) {
    $sub_cat_sql = "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = ? AND pc_status = '1' ORDER BY pc_order, pc_name ASC";
    $stmt_sub = mysqli_prepare($con, $sub_cat_sql);
    mysqli_stmt_bind_param($stmt_sub, 'i', $main_cat_id);
    mysqli_stmt_execute($stmt_sub);
    $sub_result = mysqli_stmt_get_result($stmt_sub);
    while ($row = mysqli_fetch_assoc($sub_result)) {
        $sub_categories[] = $row;
    }
    mysqli_stmt_close($stmt_sub);
}

// Fetch sub sub categories (level 2)
$sub_sub_categories = [];
if ($product_row->pc_parent_id > 0) {
    $sub_sub_sql = "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = ? AND pc_status = '1' ORDER BY pc_order, pc_name ASC";
    $stmt_sub_sub = mysqli_prepare($con, $sub_sub_sql);
    mysqli_stmt_bind_param($stmt_sub_sub, 'i', $product_row->pc_parent_id);
    mysqli_stmt_execute($stmt_sub_sub);
    $sub_sub_result = mysqli_stmt_get_result($stmt_sub_sub);
    while ($row = mysqli_fetch_assoc($sub_sub_result)) {
        $sub_sub_categories[] = $row;
    }
    mysqli_stmt_close($stmt_sub_sub);
}

// Fetch measurement units
$units = [];
$unit_sql = "SELECT mu_id, mu_name FROM measurement_unit WHERE mu_status = '1' ORDER BY mu_name ASC";
$unit_result = mysqli_query($con, $unit_sql);
while ($row = mysqli_fetch_assoc($unit_result)) {
    $units[] = $row;
}

// Fetch currencies
$currencies = [];
$curr_sql = "SELECT cn_id, cn_currency FROM country WHERE cn_status = '1' ORDER BY cn_currency ASC";
$curr_result = mysqli_query($con, $curr_sql);
while ($row = mysqli_fetch_assoc($curr_result)) {
    $currencies[] = $row;
}

// Fetch payment methods
$payment_methods = [];
$payment_sql = "SELECT ph_id, ph_title FROM payment_method ORDER BY ph_title ASC";
$payment_result = mysqli_query($con, $payment_sql);
while ($row = mysqli_fetch_assoc($payment_result)) {
    $payment_methods[] = $row;
}

// Handle update message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// =============================================
// Edit Product Class
// =============================================
class EditProduct
{
    private $con;
    public $cid;
    public $mcat_id;
    public $cat_id;
    public $pd_subcat_id;
    public $pd_title;
    public $pd_code;
    public $pd_min_order_qty;
    public $pd_unit;
    public $pd_fob_price;
    public $pd_fob_price2;
    public $pd_currency;
    public $pd_preferred_buyer_location;
    public $pd_desc;
    public $msg;

    public function __construct($cid, $con)
    {
        $this->cid = $cid;
        $this->con = $con;
    }

    public function getProductDetails()
    {
        $sql = "SELECT p.*, pc.pc_parent_id 
                FROM products p
                INNER JOIN product_category pc ON p.pd_subcat_id = pc.pc_id
                WHERE MD5(p.pd_id) = ? 
                LIMIT 1";
        
        $stmt = mysqli_prepare($this->con, $sql);
        mysqli_stmt_bind_param($stmt, 's', $this->cid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }

    public function validate(): bool
    {
        // Fetch bad words
        $bad_words = [];
        $bad_sql = "SELECT bd_word FROM bad_word";
        $bad_result = mysqli_query($this->con, $bad_sql);
        while ($row = mysqli_fetch_assoc($bad_result)) {
            $bad_words[] = strtoupper($row['bd_word']);
        }

        $title = strtoupper($this->pd_title);
        $desc = strtoupper($this->pd_desc);

        if ($this->mcat_id == '') {
            $this->msg = '<font color="#CC0000">Please select main category</font>';
            return false;
        } elseif ($this->cat_id == '') {
            $this->msg = '<font color="#CC0000">Please select category</font>';
            return false;
        } elseif ($this->pd_subcat_id == '') {
            $this->msg = '<font color="#CC0000">Please select sub-category</font>';
            return false;
        } elseif ($this->pd_title == '') {
            $this->msg = '<font color="#CC0000">Please enter product title</font>';
            return false;
        } elseif (!empty($this->pd_title)) {
            foreach ($bad_words as $word) {
                if (str_contains($title, $word)) {
                    $this->msg = "<font color='#CC0000'>You can't post words like '" . htmlspecialchars($word) . "' in Product Name.</font>";
                    return false;
                }
            }
        } elseif ($this->pd_min_order_qty == '') {
            $this->msg = '<font color="#CC0000">Please enter Minimum Order Quantity.</font>';
            return false;
        } elseif ($this->pd_unit == '') {
            $this->msg = '<font color="#CC0000">Please choose Measurement Unit for Minimum Order Quantity.</font>';
            return false;
        } elseif ($this->pd_currency == '') {
            $this->msg = '<font color="#CC0000">Please choose Currency.</font>';
            return false;
        } elseif (strlen($this->pd_desc) > 4000) {
            $this->msg = '<font color="#CC0000">Product Description cannot have more than 4000 characters.</font>';
            return false;
        } elseif (!empty($this->pd_desc)) {
            foreach ($bad_words as $word) {
                if (str_contains($desc, $word)) {
                    $this->msg = "<font color='#CC0000'>You can't post words like '" . htmlspecialchars($word) . "' in Product Description.</font>";
                    return false;
                }
            }
        }

        return true;
    }

    public function update(): void
    {
        $product_id = substr($this->cid, 4);
        
        $sql = "UPDATE products SET
                pd_subcat_id = ?,
                pd_title = ?,
                pd_code = ?,
                pd_min_order_qty = ?,
                pd_unit = ?,
                pd_fob_price = ?,
                pd_fob_price2 = ?,
                pd_currency = ?,
                pd_preferred_buyer_location = ?,
                pd_desc = ?,
                pd_date = NOW()
                WHERE MD5(pd_id) = ?";

        $stmt = mysqli_prepare($this->con, $sql);
        mysqli_stmt_bind_param($stmt, 'issiiississ', 
            $this->pd_subcat_id,
            $this->pd_title,
            $this->pd_code,
            $this->pd_min_order_qty,
            $this->pd_unit,
            $this->pd_fob_price,
            $this->pd_fob_price2,
            $this->pd_currency,
            $this->pd_preferred_buyer_location,
            $this->pd_desc,
            $product_id
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $this->msg = '<font color="green">! Product updated successfully and notification sent to all interested buyers!</font>';
    }
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editor = new EditProduct($_GET['token'], $con);
    
    $editor->mcat_id = (int)trim($_POST['mcat_id'] ?? 0);
    $editor->cat_id = (int)trim($_POST['cat_id'] ?? 0);
    $editor->pd_subcat_id = (int)trim($_POST['pd_subcat_id'] ?? 0);
    $editor->pd_title = trim($_POST['pd_title'] ?? '');
    $editor->pd_code = trim($_POST['pd_code'] ?? '');
    $editor->pd_desc = trim($_POST['pd_desc'] ?? '');
    $editor->pd_min_order_qty = (float)trim($_POST['pd_min_order_qty'] ?? 0);
    $editor->pd_unit = (int)trim($_POST['pd_unit'] ?? 0);
    $editor->pd_fob_price = (float)trim($_POST['pd_fob_price'] ?? 0);
    $editor->pd_fob_price2 = (float)trim($_POST['pd_fob_price2'] ?? 0);
    $editor->pd_currency = (int)trim($_POST['pd_currency'] ?? 0);
    $editor->pd_preferred_buyer_location = trim($_POST['pd_preferred_buyer_location'] ?? 'any');

    if ($editor->validate()) {
        $editor->update();
        $_SESSION['msg'] = $editor->msg;
        header("Location: product-email.php?token=" . urlencode($_GET['token']));
        exit;
    } else {
        $_SESSION['msg'] = $editor->msg;
        header("Location: product-edit.php?token=" . urlencode($_GET['token']));
        exit;
    }
}

// Split images
$product_images = explode(',', $product_row->pd_image ?? '');
$product_logo_images = explode(',', $product_row->pd_imagelogo ?? '');
$first_image = $product_images[0] ?? 'add-image.gif';
$first_logo = $product_logo_images[0] ?? 'add-image.gif';
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
    <link rel="stylesheet" href="css/colorbox.css" />
    <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
    
    <style>
        /* Additional styles */
        .tab-sel { background-color: #4CAF50; color: white; padding: 10px; cursor: pointer; }
        .tab-sel2 { background-color: #ddd; padding: 10px; cursor: pointer; }
        .tab_p, .tab_p1 { padding: 10px; display: inline-block; }
        .bulb { background-color: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        #img_disp, #img_disp_logo { border: 1px solid #ccc; margin: 10px 0; position: relative; }
        #img_disp { width: 125px; height: 125px; }
        #img_disp_logo { width: 43px; height: 46px; position: absolute; bottom: 4px; left: 5px; }
        .a_f { padding: 5px; border: 1px solid #ccc; border-radius: 3px; }
        .pf1 { width: 280px; margin-bottom: 10px; }
        .saps { background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        .saps:hover { background-color: #45a049; }
        .text-left { text-align: left; }
        .float-left { float: left; }
        .margin-left-40 { margin-left: 40px; }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
    
    <script>
    var imageBasket = [];
    var imageBasketlogo = [];

    function show_photo(id) {
        $.get("ajax-file/showProductImage.php", {id: id}, function(data) {
            $("#img_disp").html('<img src="' + data + '" alt="" style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
        });
    }

    function showTempPhotoLogo(id) {
        $.get("ajax-file/showProductImagelogo.php", {id: id}, function(data) {
            $("#img_disp_logo").html('<img src="' + data + '" alt="" style="width:43px;height:46px;margin:-27px 0px 0px -3px;"/>');
        });
    }

    function usePhotoToUpload(id) {
        if (jQuery.inArray(id, imageBasket) != -1) {
            imageBasket = $.grep(imageBasket, function(value) {
                return value != id;
            });
        } else {
            imageBasket.push(id);
        }
    }

    function usePhotoToUploadlogo(id) {
        if (jQuery.inArray(id, imageBasketlogo) != -1) {
            imageBasketlogo = $.grep(imageBasketlogo, function(value) {
                return value != id;
            });
        } else {
            imageBasketlogo.push(id);
        }
    }

    function usePhoto() {
        var imgArr = imageBasket;
        var tbl = 'products_edit';
        var typ = 'product';
        var pd_id = document.getElementById('pd_id').value;
        
        $.post("ajax-file/addNewImgFrmGallery.php", {
            imgArr: imgArr,
            pd_id: pd_id,
            tbl: tbl,
            typ: typ
        }, function(data) {
            jQuery('#cboxOverlay').remove();
            jQuery('#colorbox').remove();
            $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
            
            setTimeout(function() {
                show_photo(pd_id);
            }, 500);
        });
    }

    function usePhotoForLogo() {
        var imgArr = imageBasketlogo;
        var tbl = 'products_edit';
        var typ1 = 'logo';
        var pd_id = document.getElementById('pd_id').value;
        
        $.post("ajax-file/addNewImgFrmGallery.php", {
            imgArr: imgArr,
            pd_id: pd_id,
            tbl: tbl,
            typ: typ1
        }, function(data) {
            console.log(data);
            jQuery('#cboxOverlay').remove();
            jQuery('#colorbox').remove();
            $("#img_disp_logo").html('<img src="images/loader.gif" alt="Uploading...." style="width:43px;height:46px;margin:-27px 0px 0px -3px;"/>');
            
            setTimeout(function() {
                showTempPhotoLogo(pd_id);
            }, 500);
        });
    }

    function additem() {
        var mcat_id = document.getElementById('mcat_id');
        var cat_id = document.getElementById('cat_id');
        var pd_subcat_id = document.getElementById('pd_subcat_id');
        var pd_title = document.getElementById('pd_title');
        var pd_min_order_qty = document.getElementById('pd_min_order_qty');
        var pd_unit = document.getElementById('pd_unit');
        var pd_currency = document.getElementById('pd_currency');
        var pd_desc = document.getElementById('pd_desc');
        
        var message = "";
        var valid = true;
        
        if (mcat_id.value == '') {
            message = "Please select main category";
            mcat_id.focus();
            valid = false;
        } else if (cat_id.value == '') {
            message = "Please select category";
            cat_id.focus();
            valid = false;
        } else if (pd_subcat_id.value == '') {
            message = "Please select sub-category";
            pd_subcat_id.focus();
            valid = false;
        } else if (pd_title.value == '') {
            message = "Please enter product title";
            pd_title.focus();
            valid = false;
        } else if (pd_min_order_qty.value == '' || pd_min_order_qty.value == '0') {
            message = "Please enter valid Minimum Order Quantity";
            pd_min_order_qty.value = '';
            pd_min_order_qty.focus();
            valid = false;
        } else if (isNaN(pd_min_order_qty.value)) {
            message = "Minimum Order Quantity must be a number";
            pd_min_order_qty.value = '';
            pd_min_order_qty.focus();
            valid = false;
        } else if (pd_unit.value == '') {
            message = "Please select Measurement Unit";
            pd_unit.focus();
            valid = false;
        } else if (pd_currency.value == '') {
            message = "Please select Currency";
            pd_currency.focus();
            valid = false;
        } else if (pd_desc.value.length > 4000) {
            message = "Product Description cannot exceed 4000 characters";
            pd_desc.focus();
            valid = false;
        }
        
        if (!valid) {
            document.getElementById('updatemessage').style.color = "red";
            document.getElementById('updatemessage').innerHTML = message;
        }
        
        return valid;
    }

    function showCategory() {
        var pc_id = document.getElementById('mcat_id').value;
        $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
            $('#cat_id').html(data);
            showsubcat();
        });
    }

    function showsubcat() {
        var pc_id = $('select#cat_id option:selected').val();
        $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
            $('#pd_subcat_id').html(data);
        });
    }

    function mecount() {
        var cnt = $("#pd_desc").val().length;
        $("#cn").html(cnt);
    }

    function packdetcount() {
        var cnt = $("#pd_pck_dets").val().length;
        $("#pckdet").html(cnt);
    }

    function showadditional() {
        $("[id^=t3]").attr('class', 'tab-sel2 f1');
        $("#t1").attr('class', 'tab_p f1');
        $("#bafrm").hide('fast');
        $("#addef").show('fast');
        $("#adpre").hide();
        $("#adfrm").show();
    }

    function showmedit() {
        $("[id^=t1]").attr('class', 'tab-sel f1 fw');
        $("#t3").attr('class', 'tab_p1 f1');
        $("#bafrm").show('fast');
        $("#addef").hide('fast');
    }

    function additionaldet_update(pid) {
        var pd_hot = $('[name=pd_hot]:checked').val();
        var pd_payment = $("input[name=pd_payment]:checked").map(function() {
            return this.value;
        }).get().join(",");
        var pd_pod = $("#pd_pod").val();
        var pd_pn_capct = $("#pd_pn_capct").val();
        var pd_dlv_time = $("#pd_dlv_time").val();
        var pd_pck_dets = $("#pd_pck_dets").val();
        var brand_name = $("#pd_brand_name").val();
        
        $.get("ajax-file/additionaldet-edit.php", {
            pid: pid,
            pd_hot: pd_hot,
            pd_payment: pd_payment,
            pd_pod: pd_pod,
            pd_pn_capct: pd_pn_capct,
            pd_dlv_time: pd_dlv_time,
            pd_pck_dets: pd_pck_dets,
            pd_brand: brand_name
        }, function(data) {
            var e = data.split("||");
            if (e[1] == 0) {
                alert(e[0]);
            } else {
                $("#adfrm").hide();
                $("#adpre").show();
            }
        });
    }

    function cancel_update() {
        $("#adfrm").hide();
        $("#adpre").show();
    }

    $(document).ready(function() {
        mecount();
        packdetcount();
        
        $('.ajax').live('click', function() {
            if ($("#colorbox").css("display") == "block") {
                jQuery('#cboxOverlay').remove();
                jQuery('#colorbox').remove();
            }
            $.colorbox({href: $(this).attr('href'), open: true});
            return false;
        });
        
        $('.ajaxa').on('click', function() {
            $.colorbox({href: $(this).attr('href'), open: true});
            return false;
        });
        
        $(".inline").colorbox({inline: true, width: "50%"});
        
        jQuery('#product_upload').uploadifive({
            'auto': true,
            'formData': {'id': '<?php echo (int)$product_row->pd_id; ?>'},
            'queueID': 'queue',
            'debug': false,
            'method': 'post',
            'uploadScript': 'ajax-file/editProdImg.php',
            'onAddQueueItem': function(file) {
                $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
            },
            'onUploadComplete': function(file, data) {
                show_photo(<?php echo (int)$product_row->pd_id; ?>);
            }
        });
        
        jQuery('#productlogo_upload').uploadifive({
            'auto': true,
            'formData': {'id': '<?php echo (int)$product_row->pd_id; ?>'},
            'queueID': 'queue',
            'debug': false,
            'method': 'post',
            'uploadScript': 'ajax-file/editProdlogo.php',
            'onAddQueueItem': function(file) {
                $("#img_disp_logo").html('<img src="images/loader.gif" alt="Uploading...." style="width:43px;height:46px;margin-top:-29px;margin-left:-3px;"/>');
            },
            'onUploadComplete': function(file, data) {
                showTempPhotoLogo(<?php echo (int)$product_row->pd_id; ?>);
            }
        });
    });
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
            
            <!-- Left Sidebar -->
            <?php include __DIR__ . '/includes/left_menu.php'; ?>
            
            <!-- Main Content -->
            <div class="w56b f1 p2b p14 blr">
                <h1 class="text-left">تحديث المنتج أو الخدمة</h1>
                
                <div class="mt5">
                    <div class="ap1">
                        <a href="product-list.php" class="f1 ap2" title="Go Back to Manage Products">&lt;&lt; الرجوع لقائمة المنتجات</a>
                        <p class="f1 loading" style="display:none;" id="loading">
                            <img src="images/my2-loading.gif" class="loading_m2">&nbsp;Loading...&nbsp;
                        </p>
                        <p class="f2 mt12" id="page_str"></p>
                        <div class="c3"></div>
                    </div>
                    
                    <div class="apfc1">
                        <div>&nbsp;</div>
                        
                        <div class="tab_2">
                            <span id="t1" class="tab-sel tab_p f1" style="background-position:0px -26px;">
                                <a onclick="showmedit();" title="Edit Product">تحديث المنتج</a>
                            </span>
                            <span id="t3" class="tab-sel2 f1 fw" style="height:25px; width:164px;">
                                <a onclick="showadditional();" title="Additional Details">تفاصيل إضافية</a>
                            </span>
                            <div class="c3"></div>
                        </div>
                        
                        <div class="tab_brd" style="background-color:#FAF4FF" 
                             title="Writing your product details correctly makes it easier to appear in search engines">
                            
                            <!-- Basic Edit Form -->
                            <div style="display:block;" class="mse2 p-irt" id="bafrm">
                                <p class="bulb">
                                    كتابة منتجك بوضوح وتحميل صورة جيدة يساعد على ظهور منتجك بإستمرار فى كل مكان
                                </p>
                                <div class="c3"></div>
                                
                                <div class="pia f1">
                                    <div style="position:absolute" id="tempwarning_add"></div>
                                    <p id="img_form" style="margin-top:2px;"></p>
                                    
                                    <!-- Main Product Image -->
                                    <div id="img_disp" class="cssie1 mover">
                                        <img src="upload/myproduct/<?php echo htmlspecialchars($first_image, ENT_QUOTES, 'UTF-8'); ?>" 
                                             style="width:125px;height:125px;margin-top:1px;margin-left:1px;" 
                                             title="ADD product Image">
                                    </div>
                                    
                                    <div>
                                        <div id="drop" style="padding-left:10px; float:left">
                                            <input type="file" id="product_upload" name="product_upload" title="ADD Product Image">
                                        </div>
                                        <div id="queue"></div>
                                    </div>
                                    
                                    <div>
                                        <a class="ajax" href="show_productedit_img.php?pid=<?php echo (int)$product_row->pd_id; ?>" 
                                           style="text-decoration:none;" title="Select Image from Gallery">
                                            صورة من الجاليرى
                                        </a>
                                    </div>
                                    
                                    <p id="img_form" style="margin-top:40px;"></p>
                                    
                                    <!-- Product Logo -->
                                    <div id="imglogo_disps" class="cssie1 mover" style="position:relative; border:1px solid #ccc;">
                                        <img src="upload/myproduct/logo_upload2.jpg" 
                                             title="ADD Logo,Brand,Discount,Sign" 
                                             style="width:121px;height:125px;margin-top:1px;margin-left:1px;">
                                        
                                        <div id="img_disp_logo" class="cssie1" 
                                             style="width:43px; height:28px; left:5px; bottom:4px; position:absolute;">
                                            <img src="upload/myproduct/<?php echo htmlspecialchars($first_logo, ENT_QUOTES, 'UTF-8'); ?>" 
                                                 title="Add Brand Logo" 
                                                 style="width:43px; height:46px; margin:-27px 0px 0px -3px; 
                                                        <?php echo empty($first_logo) ? 'display:none;' : ''; ?>">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div id="drop" style="font-width:200">
                                            <input type="file" id="productlogo_upload" name="productlogo_upload" style="font-width:200">
                                        </div>
                                        <div id="queue"></div>
                                    </div>
                                    
                                    <div>
                                        <a class="ajaxa" href="show_product_edit_logo.php?pid=<?php echo (int)$product_row->pd_id; ?>" 
                                           style="text-decoration:none;" 
                                           title="ADD side image as Logo,Brand,Discount,Sign Image">
                                            تحميل صورة لوجو أو خصم
                                        </a>
                                    </div>
                                    
                                    <div id="remove_image" class="dn mt5">
                                        <a href="javascript:remove_small_image('add');">
                                            <img src="images/remove.gif" align="absmiddle" width="44" height="10">
                                        </a>
                                    </div>
                                </div>
                                
                                <form action="" method="POST" enctype="multipart/form-data" onsubmit="return additem();">
                                    <div class="fside f1" style="width:82%;">
                                        <div id="updatemessage"><?php echo $msg; ?></div>
                                        <div style="clear:both"></div>
                                        
                                        <!-- Product ID -->
                                        <input type="hidden" id="pd_id" name="pd_id" value="<?php echo (int)$product_row->pd_id; ?>">
                                        
                                        <!-- Main Category -->
                                        <div class="fs1 f1" style="width:100%" title="Main Category">
                                            <p><span style="line-height:12px;">*</span> Select Main Category</p>
                                            <select name="mcat_id" id="mcat_id" onChange="showCategory(this.value)" class="a_f pf1" style="width:280px;">
                                                <option value="">-- Select Main Category --</option>
                                                <?php foreach ($main_categories as $cat): ?>
                                                <option value="<?php echo (int)$cat['pc_id']; ?>" 
                                                        <?php echo ($cat['pc_id'] == $main_cat_id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select><br>
                                            <span></span>
                                        </div>
                                        
                                        <!-- Category -->
                                        <div class="fs1 f1" title="Category">
                                            <p><span style="line-height:12px;">*</span> Select Category</p>
                                            <select name="cat_id" id="cat_id" onChange="showsubcat()" class="a_f pf1" style="width:280px;">
                                                <option value="">-- Select Category --</option>
                                                <?php foreach ($sub_categories as $cat): ?>
                                                <option value="<?php echo (int)$cat['pc_id']; ?>" 
                                                        <?php echo ($cat['pc_id'] == $product_row->pc_parent_id) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select><br>
                                            <span></span>
                                        </div>
                                        
                                        <!-- Sub Category -->
                                        <div id="sbcat">
                                            <div class="fs1 f1" title="Sub Category">
                                                <p><span style="line-height:12px;">*</span> Select Sub Category</p>
                                                <select name="pd_subcat_id" id="pd_subcat_id" class="a_f pf1" style="width:280px;">
                                                    <option value="">-- Select Sub Category --</option>
                                                    <?php foreach ($sub_sub_categories as $cat): ?>
                                                    <option value="<?php echo (int)$cat['pc_id']; ?>" 
                                                            <?php echo ($cat['pc_id'] == $product_row->pd_subcat_id) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select><br>
                                                <span></span>
                                            </div>
                                        </div>
                                        
                                        <!-- Product Title -->
                                        <div class="fs1 f1" title="Product Heading">
                                            <p><span>*</span> Enter Product Title in Arabic or English depending on where you sell</p>
                                            <input value="<?php echo htmlspecialchars($product_row->pd_title ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                   name="pd_title" id="pd_title" maxlength="60" class="a_f pf1" type="text">
                                            <span></span>
                                        </div>
                                        
                                        <!-- Product Code -->
                                        <div class="fs2 f1" title="Product Item Code">
                                            <p>Enter Product Code (Optional)</p>
                                            <input value="<?php echo htmlspecialchars($product_row->pd_code ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                   name="pd_code" id="pd_code" maxlength="60" class="a_f pf1" type="text">
                                            <span></span>
                                        </div>
                                        
                                        <!-- Minimum Order Quantity -->
                                        <div class="fs1 f1" title="Minimum Order Quantity">
                                            <p><span>*</span> Enter Minimum Order Quantity</p>
                                            <input name="pd_min_order_qty" id="pd_min_order_qty" maxlength="60" class="a_f pf1" 
                                                   type="text" value="<?php echo htmlspecialchars((string)$product_row->pd_min_order_qty, ENT_QUOTES, 'UTF-8'); ?>">
                                            <span></span>
                                        </div>
                                        
                                        <!-- Measurement Unit -->
                                        <div class="fs2 f1" title="Measurement Unit">
                                            <p>Select Measurement Unit</p>
                                            <select size="1" name="pd_unit" id="pd_unit" class="a_f pf1">
                                                <option value="">- Select Unit Type -</option>
                                                <?php foreach ($units as $unit): ?>
                                                <option value="<?php echo (int)$unit['mu_id']; ?>" 
                                                        <?php echo ($unit['mu_id'] == $product_row->pd_unit) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($unit['mu_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span></span>
                                        </div>
                                        
                                        <!-- FOB Price -->
                                        <div class="fs1 f1" title="FOB / Wholesale Price">
                                            <p>Domestic or Export (From : To) Enter Product Price</p>
                                            <span title="From">From :</span>
                                            <input name="pd_fob_price" id="pd_fob_price" maxlength="60" class="a_f pf1" 
                                                   style="width:45%;" type="text" 
                                                   value="<?php echo htmlspecialchars((string)$product_row->pd_fob_price, ENT_QUOTES, 'UTF-8'); ?>">
                                            <span title="To">To :</span>
                                            <input name="pd_fob_price2" id="pd_fob_price2" maxlength="60" class="a_f pf1" 
                                                   style="width:42%;" type="text" 
                                                   value="<?php echo htmlspecialchars((string)$product_row->pd_fob_price2, ENT_QUOTES, 'UTF-8'); ?>">
                                            <br>
                                            <span class="margin-left-40"></span>
                                        </div>
                                        
                                        <!-- Currency -->
                                        <div class="fs2 f1" style="margin-top:4px;" title="Select Selling Currency">
                                            <p>&nbsp;</p>
                                            <select size="1" name="pd_currency" id="pd_currency" class="a_f pf1">
                                                <option value="">- Select Currency -</option>
                                                <?php foreach ($currencies as $curr): 
                                                    $selected = ($curr['cn_id'] == $product_row->pd_currency || 
                                                                 $curr['cn_id'] == user_info($uid, 'country')) ? 'selected' : '';
                                                ?>
                                                <option value="<?php echo (int)$curr['cn_id']; ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($curr['cn_currency'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span></span>
                                        </div>
                                        
                                        <div style="clear:both; line-height:21px;">&nbsp;</div>
                                        
                                        <!-- Location Preferences -->
                                        <div class="fs1 f1" style="margin-top:5px;" title="Location Preferences">
                                            <p><span style="line-height:12px;">*</span> إختار المكان الذى تبيع فيه المنتج أو الخدمة</p>
                                            
                                            <div style="vertical-align:middle">
                                                <input type="radio" id="pd_preferred_buyer_location_1" name="pd_preferred_buyer_location" value="abroad" 
                                                       <?php echo ($product_row->pd_preferred_buyer_location == 'abroad') ? 'checked' : ''; ?>>
                                                <label style="top:0px;" title="Abroad Only">- للتصدير فقط</label>
                                            </div>
                                            <div>
                                                <input type="radio" id="pd_preferred_buyer_location_2" name="pd_preferred_buyer_location" value="any" 
                                                       <?php echo ($product_row->pd_preferred_buyer_location == 'any') ? 'checked' : ''; ?>>
                                                <label style="top:0px;" title="Abroad + Domestic">- للتصدير والسوق المحلى</label>
                                            </div>
                                            <div>
                                                <input type="radio" id="pd_preferred_buyer_location_3" name="pd_preferred_buyer_location" value="domestic" 
                                                       <?php echo ($product_row->pd_preferred_buyer_location == 'domestic') ? 'checked' : ''; ?>>
                                                <label style="top:0px;" title="Domestic Only">- للسوق المحلى فقط</label>
                                            </div>
                                            <div>
                                                <input type="radio" id="pd_preferred_buyer_location_4" name="pd_preferred_buyer_location" value="my_city" 
                                                       <?php echo ($product_row->pd_preferred_buyer_location == 'my_city') ? 'checked' : ''; ?>>
                                                <label style="top:0px;" title="My City Only">- داخل مدينتى فقط</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="edtbut">
                                        <span id="save_basic">
                                            <input name="btnUpdate" class="saps mt12" 
                                                   title="Save Product Changes" 
                                                   value="إحفظ التغييرات" type="submit">
                                        </span>
                                        <span style="display:none;" id="basaving">
                                            <img alt="" src="edit%20product_files/loading.gif" border="0" width="16" height="11">
                                        </span>
                                    </div>
                                    
                                    <div id="editor_loading" style="display:none; padding:230px; height:0px;">
                                        <img src="images/indicator.gif">&nbsp;Loading editor...
                                    </div>
                                    
                                    <div class="c3 fs3" id="editor" style="">
                                        <p>وصف المنتج</p>
                                        <textarea class="a_f" rows="15" id="pd_desc" name="pd_desc" onKeyUp="mecount();"><?php 
                                            echo htmlspecialchars($product_row->pd_desc ?? '', ENT_QUOTES, 'UTF-8'); 
                                        ?></textarea>
                                        <div class="max">
                                            <font id="Charcount" color="#ff8000">
                                                <span id="cn" style="color:#ff8000">0</span> character (maximum of 4000)
                                            </font> character(s).
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Additional Details Section -->
                            <div id="addef" class="p-irt" style="display:none;">
                                <p class="bulb">
                                    Additional product details help buyers understand the product and respond quickly.
                                </p>
                                <p class="b-img arp1 pdb1 f1" href="JavaScript:;" title="Additional Details">
                                   تفاصيل إضافية عن المنتج
                                </p>
                                <div class="c3"></div>
                                
                                <!-- Additional Details Preview -->
                                <div id="adpre" class="pad f1 mr" style="display:none;">
                                    <ul>
                                        <li class="f1 tc" title="Product Status">حالة المنتج</li>
                                        <li class="f1 pct" id="view_hotnew">
                                            <?php echo ($product_row->pd_hot == '1') ? "Hot" : "-"; ?>
                                        </li>
                                        <li class="f1 tc" title="Product Brand Name">إسم البراند</li>
                                        <li class="f1 pct pbi" id="view_brand_name">
                                            <?php echo !empty($product_row->brand_name) ? htmlspecialchars($product_row->brand_name, ENT_QUOTES, 'UTF-8') : "-"; ?>
                                        </li>
                                        <li class="f1 tc" title="Payment Terms">Payment Terms</li>
                                        <li class="f1 pct pbi" id="view_payment_terms">-</li>
                                        <li class="f1 tc">FOB / Wholesale</li>
                                        <li class="f1 pct" id="view_fob_price">
                                            <?php if (!empty($product_row->pd_fob_price) && !empty($product_row->pd_currency)): ?>
                                                <?php echo htmlspecialchars((string)$product_row->pd_fob_price, ENT_QUOTES, 'UTF-8'); ?>&nbsp;
                                                <?php echo htmlspecialchars(get_product_detail($product_row->pd_id, 'pd_currency'), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </li>
                                        <li class="f1 tc">Minimum Order Quantity</li>
                                        <li class="f1 pct" id="view_moq">
                                            <?php if (!empty($product_row->pd_min_order_qty) && !empty($product_row->pd_unit)): ?>
                                                <?php echo htmlspecialchars((string)$product_row->pd_min_order_qty, ENT_QUOTES, 'UTF-8'); ?>&nbsp;
                                                <?php echo htmlspecialchars(get_product_detail($product_row->pd_id, 'pd_unit'), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </li>
                                        <li class="f1 tc" title="Port or Place of Dispatch">ميناء أو جهة التسليم</li>
                                        <li class="f1 pct" id="view_port_of_dispatch">
                                            <?php echo !empty($product_row->pd_pod) ? htmlspecialchars($product_row->pd_pod, ENT_QUOTES, 'UTF-8') : "-"; ?>
                                        </li>
                                        <li class="f1 tc" title="Production Capacity">Production Capacity</li>
                                        <li class="f1 pct" id="view_prod_cap">
                                            <?php echo !empty($product_row->pd_pn_capct) ? htmlspecialchars($product_row->pd_pn_capct, ENT_QUOTES, 'UTF-8') : "-"; ?>
                                        </li>
                                        <li class="f1 tc" title="Delivery Time">وقت التسليم</li>
                                        <li class="f1 pct" id="view_delivery_time">
                                            <?php echo !empty($product_row->pd_dlv_time) ? htmlspecialchars($product_row->pd_dlv_time, ENT_QUOTES, 'UTF-8') : "-"; ?>
                                        </li>
                                        <li class="f1 tc" title="Packing Details">تفاصيل التغليف</li>
                                        <li class="f1 pct pns7" id="view_packaging_det">
                                            <?php echo !empty($product_row->pd_pck_dets) ? htmlspecialchars($product_row->pd_pck_dets, ENT_QUOTES, 'UTF-8') : "-"; ?>
                                        </li>
                                        <li class="f1 c3 mt12">
                                            <a id="adedit" class="saps f1 f11 fw" onclick="showadditional();" style="cursor:pointer;">
                                                Edit
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                
                                <!-- Additional Details Form -->
                                <div style="display:block;" id="adfrm">
                                    <form method="post" name="additional_details">
                                        <div class="pad f1 mr">
                                            <ul>
                                                <li class="f1 tc adtp" title="Product Display Status">حالة المنتج</li>
                                                <li class="f1 pct tc1">
                                                    <input class="rad" name="pd_hot" value="1" id="pd_hot" type="radio" 
                                                           <?php echo ($product_row->pd_hot == '1') ? 'checked' : ''; ?>>
                                                    من المنتجات الهامة
                                                    <input name="pd_hot" value="0" id="pd_hot" type="radio" 
                                                           <?php echo ($product_row->pd_hot == '0') ? 'checked' : ''; ?>>
                                                    من المنتجات المختلفة
                                                </li>
                                                
                                                <li class="f1 tc adtp" title="Product Brand Name">إسم البراند</li>
                                                <li class="f1 pct pbi tc1">
                                                    <input name="pd_brand_name" id="pd_brand_name" maxlength="255" 
                                                           value="<?php echo htmlspecialchars($product_row->brand_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           class="a_f adt form-control" type="text">
                                                </li>
                                                
                                                <li class="f1 tc adtp" title="Payment Terms">طريقة الدفع</li>
                                                <li class="f1 pct pbi tc1">
                                                    <span class="d-blcok" id="payOptionButton" data-toggle="collapse" data-target="#payOptions" 
                                                          style="cursor: pointer;">
                                                        إختار الطريقة
                                                        <span class="payOptionButtonArrowDown" style="width:max-content; transform:rotate(90deg); display:inline-block;">&#10151;</span>
                                                    </span>
                                                    
                                                    <div class="collapse fade" id="payOptions" style="padding:4px 14px;">
                                                        <?php 
                                                        $selected_payments = explode(',', $product_row->pd_payment ?? '');
                                                        foreach ($payment_methods as $method): 
                                                        ?>
                                                        <input class="cb1" name="pd_payment" value="<?php echo (int)$method['ph_id']; ?>" 
                                                               type="checkbox" <?php echo in_array($method['ph_id'], $selected_payments) ? 'checked' : ''; ?>>
                                                        <?php echo htmlspecialchars($method['ph_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?><br>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </li>
                                                
                                                <li class="f1 tc adtp" title="Port or Place of Dispatch">ميناء أو جهة التسليم</li>
                                                <li class="f1 pct">
                                                    <input class="a_f adt form-control" name="pd_pod" id="pd_pod" maxlength="100" 
                                                           value="<?php echo htmlspecialchars($product_row->pd_pod ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           type="text">
                                                </li>
                                                
                                                <li class="f1 tc adtp" title="Production Capacity">القدرة الانتاجية</li>
                                                <li class="f1 pct">
                                                    <input name="pd_pn_capct" id="pd_pn_capct" maxlength="100" 
                                                           value="<?php echo htmlspecialchars($product_row->pd_pn_capct ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           class="a_f adt form-control" type="text">
                                                </li>
                                                
                                                <li class="f1 tc adtp" title="Delivery Time">وقت التسليم</li>
                                                <li class="f1 pct">
                                                    <input class="a_f adt form-control" name="pd_dlv_time" id="pd_dlv_time" maxlength="100" 
                                                           value="<?php echo htmlspecialchars($product_row->pd_dlv_time ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           type="text">
                                                </li>
                                                
                                                <li class="f1 tc adtp" title="Packing Details">تفاصيل التغليف</li>
                                                <li class="f1 pct">
                                                    <textarea rows="5" class="a_f form-control" name="pd_pck_dets" id="pd_pck_dets" onkeyup="packdetcount();"><?php 
                                                        echo htmlspecialchars($product_row->pd_pck_dets ?? '', ENT_QUOTES, 'UTF-8'); 
                                                    ?></textarea>
                                                    <font id="Charcount1" color="#ff8000">
                                                        <span id="pckdet">20</span> character (maximum of 2000)
                                                    </font> character(s).
                                                </li>
                                                
                                                <li class="f1 tc adtp" title="Attach PDF Brochure">إرفاق بروشور المنتج</li>
                                                <li class="f1 pct1 mt5">
                                                    <iframe src="upload-prd-doc.php?pid=<?php echo (int)$product_row->pd_id; ?>" 
                                                            border="0" framespacing="0" allowtransparency="true" scrolling="no" 
                                                            width="269" frameborder="0" height="30"></iframe>
                                                    <span class="f2" id="indecator_gif0" style="left:37px; position:relative; top:-32px;"></span>
                                                    ( PDF فقط ملف من نوع )
                                                </li>
                                                
                                                <li>
                                                    <input name="updateaddi" class="saps awt mt12 m5" 
                                                           title="Save Additional Details" value="إحفظ التغييرات" 
                                                           type="button" onclick="additionaldet_update(<?php echo (int)$product_row->pd_id; ?>)">
                                                    <span style="display:none;" id="adsaving">
                                                        <img alt="" src="editproduct-step1_files/loading.gif" border="0" width="16" height="11">
                                                    </span>
                                                    <input name="cancleaddi" class="saps mt12 ml8" value="Cancel" 
                                                           id="adcls" type="button" onclick="showmedit();">
                                                </li>
                                            </ul>
                                        </div>
                                    </form>
                                    <div class="c3"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="back-n fw">
                            <a href="product-list.php" title="Go Back to Manage Products">&lt;&lt; العودة الى قامة المنتجات</a>
                            <br><br>
                        </div>
                        
                        <div class="c3"></div>
                    </div>
                    
                    <input id="for" value="" type="hidden">
                </div>
            </div>
            
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
            <div class="c3">&nbsp;</div>
        </div>
        
        <br><br><br>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// Close database connection
// mysqli_close($con);
?>