<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'common.php';
$_SESSION['prevent_temp_delete'] = true;

$_SESSION['last_page'] = "product-add.php";
if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') {
    header("Location:sign-in.php");
    exit;
}

if (function_exists('get_membership_expired') && get_membership_expired()) {
    header("Location: membership_plans.php");
    exit;
}


// ✅ كود تصحيح - سجل القيم المستلمة
error_log("===== product-add.php =====");
error_log("pd_subcat_id: " . ($_POST['pd_subcat_id'] ?? 'غير موجود'));
error_log("search_product_cat: " . ($_POST['search_product_cat'] ?? 'غير موجود'));

$uid = (int)$_SESSION['uid_indm'];

if (!isset($_POST['search_product_cat'])) {
    if (!isset($_POST['pd_subcat_id']) || $_POST['pd_subcat_id'] == '0' || $_POST['pd_subcat_id'] == '') {
        header("Location:product-sel-cat.php");
        exit;
    }
} else {
    // ...
}










$uid = (int)$_SESSION['uid_indm'];

if (!isset($_POST['search_product_cat'])) {
    if (!isset($_POST['pd_subcat_id']) || $_POST['pd_subcat_id'] == '0' || $_POST['pd_subcat_id'] == '') {
        header("Location:product-sel-cat.php");
        exit;
    }
} else {
    $searchedproducts = isset($_SESSION['searchedproducts']) ? $_SESSION['searchedproducts'] : array();
    if (!$searchedproducts && !array_key_exists($_POST['search_product_cat'], $searchedproducts)) {
        header("Location:product-sel-cat.php");
        exit();
    }
    $parts = explode(">>", $_POST['search_product_cat']);
    $_POST['search_product_cat'] = end($parts);
    $id = isset($searchedproducts[$_POST['search_product_cat']]) ? $searchedproducts[$_POST['search_product_cat']] : 0;
    if (!$id) {
        header("Location:product-sel-cat.php");
        exit();
    }
    $_POST['pd_subcat_id'] = $id;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="product-add-html">
    <head>
        <title><?php echo getSiteTitle(); ?></title>
        <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
        <meta name="title" content="<?php echo getSiteTitle(); ?>">
        <meta name="keywords" content="<?php echo get_page_settings(2); ?>">
        <meta name="description" content="<?php echo get_page_settings(3); ?>">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link href="css/pro.css?v0.2" type="text/css" rel="stylesheet">
        <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
        <link href="css/jf-1.css" type="text/css" rel="stylesheet">
        <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
        <script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
        <script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
        <script type="text/javascript">
        $(document).ready(function () {
            var url = 'http://egyptmart.shop/server/php/';
            jQuery('#file_upload').fileupload({
                url: url,
                maxNumberOfFiles: 1,
                dataType: 'json',
                done: function (e, data) {
                    jQuery.each(data.result.files, function (index, file) {
                        jQuery.post("companylogo-update.php", {'uid': '<?php echo $uid; ?>', 'file': file.name}, function (data) {
                            list_photo();
                        });
                    });
                }
            });
        });
        $(document).ready(function () {
            showTempPhoto(<?php echo $uid; ?>);
        });
        var imageBasket = [];
        var imageBasketlogo = [];
        function usePhotoToUpload(id) {
            if (jQuery.inArray(id, imageBasket) != -1) {
                imageBasket = $.grep(imageBasket, function (value) { return value != id; });
            } else {
                imageBasket.push(id);
            }
        }
        function usePhotoToUploadlogo(id) {
            if (jQuery.inArray(id, imageBasketlogo) != -1) {
                imageBasketlogo = $.grep(imageBasketlogo, function (value) { return value != id; });
            } else {
                imageBasketlogo.push(id);
            }
        }
        function usePhoto(id) {
            var imgArr = imageBasket;
            var tbl = 'temp_product_image';
            var typ = 'product';
            var usr = document.getElementById('uid').value;
            $.post("ajax-file/addNewImgFrmGallery.php", {imgArr: imgArr, usr: usr, tbl: tbl, typ: typ}, function (data) {
                console.log(data);
                jQuery('#cboxOverlay').remove();
                jQuery('#colorbox').remove();
                $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
                showTempPhoto(usr);
            });
        }
        function usePhotoForLogo(id) {
            var imgArr = imageBasketlogo;
            var tbl = 'temp_product_image';
            var typ1 = 'logo';
            var usr = document.getElementById('uid').value;
            $.post("ajax-file/addNewImgFrmGallery.php", {imgArr: imgArr, usr: usr, tbl: tbl, typ: typ1}, function (data) {
                console.log(data);
                jQuery('#cboxOverlay').remove();
                jQuery('#colorbox').remove();
                $("#imglogo_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width: 43px; height: 46px;"/>');
                showTempPhotoLogo(usr);
            });
        }
        function showTempPhoto(usr) {
            $.get("ajax-file/showTempProductImage.php", {usr: usr}, function (data) {
                $("#img_disp").html('');
                $("#img_disp").html('<img src="' + data + '" alt="" style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
            });
        }
        function showTempPhotoLogo(usr) {
            $.get("ajax-file/showTempProductLogo.php", {usr: usr}, function (data) {
                $("#imglogo_disp").html('');
                $("#imglogo_disp").html('<img src="' + data + '" alt="" style="width: 43px; height: 46px;"/>');
            });
        }
        function mecount() {
            if ($.trim($("#pd_desc").val()) != '') {
                var cnt = $("#pd_desc").val().length;
                $("#cn").html(cnt);
            }
        }
        mecount();
        function packdetcount() {
            var cnt = $("#pd_pck_dets").val().length;
            $("#pckdet").html(cnt);
        }
        function showCategory() {
            var pc_id = document.getElementById('mcat_id').value;
            $.post("ajax-file/showSubcat.php", {id: pc_id}, function (data) {
                $('#cat_id').html(data);
                showsubcat();
            });
        }
        function showsubcat() {
            var pc_id = $('select#cat_id option:selected').val();
            $.post("ajax-file/showSubcat.php", {id: pc_id}, function (data) {
                $('#pd_subcat_id').html(data);
            });
        }
        </script>
        <script type="text/javascript">
        function additem2() {
            var default_image = $("#img_disp img").attr("src");
            var pd_subcat_id = document.getElementById('pd_subcat_id');
            var pd_title = document.getElementById('pd_title');
            var pd_code = document.getElementById('pd_code');
            var pd_desc = document.getElementById('pd_desc');
            var uid = document.getElementById('uid');
            var pd_min_order_qty = document.getElementById('pd_min_order_qty');
            var pd_unit = document.getElementById('pd_unit');
            var pd_fob_price = document.getElementById('pd_fob_price');
            var pd_fob_price2 = document.getElementById('pd_fob_price2');
            var pd_currency = document.getElementById('pd_currency');
            var pd_preferred_buyer_location = $('input:radio[name=pd_preferred_buyer_location]:checked').val();
            var pd_status = $('input:radio[name=pd_hot]:checked').val();
            var pd_brand_name = $("#pd_brand_name").val();
            var pd_payment = $("input[name=pd_payment]:checked").map(function () { return this.value; }).get().join(",");
            var pd_pod = $("#pd_pod").val();
            var pd_pn_capct = $("#pd_pn_capct").val();
            var pd_dlv_time = $("#pd_dlv_time").val();
            var pd_pck_dets = $("#pd_pck_dets").val();
            var message = "";
            var valid = true;
            if (pd_title.value == '') {
                message = "من فضلك أدخل إسم وعنوان المنتج ";
                pd_title.focus();
                valid = false;
            } else if (pd_min_order_qty.value == '' || pd_min_order_qty.value == null || pd_min_order_qty.value == '0' || pd_min_order_qty.value == '00') {
                message = "من فضلك أدخل أقل كمية لبيع المنتج ";
                pd_min_order_qty.value = '';
                pd_min_order_qty.focus();
                valid = false;
            } else if (isNaN(pd_min_order_qty.value)) {
                message = "من فضلك أدخل قيمة صالحة لأقل كمية لبيع المنتج";
                pd_min_order_qty.value = '';
                pd_min_order_qty.focus();
                valid = false;
            } else if (pd_unit.value == '') {
                message = "من فضلك أدخل وحدة قياس لأقل كمية لبيع المنتج";
                pd_unit.focus();
                valid = false;
            } else if (default_image == 'upload/myproduct/add-image.gif' || default_image == ' upload/myproduct/add-image.gif') {
                message = "من فضلك إختار وحمل صورة المنتج الرئيسية";
                $("#img_disp").focus();
                valid = false;
            } else if (default_image == 'images/loader.gif' || default_image == ' images/loader.gif') {
                message = "من فضلك إنتظر لحين تحميل صورة المنتج";
                $("#img_disp").focus();
                valid = false;
            } else if (pd_currency.value == '') {
                message = "من فضلك إختار عملة بيع المنتج ";
                pd_currency.focus();
                valid = false;
            } else if (pd_desc.value.length > 4000) {
                message = "وصف المنتج لايزيد عن 4000 حرف من فضلك مراجعة عدد الحروف مرة أخرى";
                pd_desc.focus();
                valid = false;
            } else {
                $.post("ajax-file/save-sellofer.php", {cat_id: pd_subcat_id.value}, function (data) {
                    console.log(data);
                });
                $.post("ajax-file/productAdd.php", {
                    pd_subcat_id: pd_subcat_id.value,
                    pd_title: pd_title.value,
                    pd_status: pd_status,
                    pd_code: pd_code.value,
                    pd_desc: pd_desc.value,
                    uid: uid.value,
                    pd_min_order_qty: pd_min_order_qty.value,
                    pd_unit: pd_unit.value,
                    pd_fob_price: pd_fob_price.value,
                    pd_fob_price2: pd_fob_price2.value,
                    pd_currency: pd_currency.value,
                    pd_preferred_buyer_location: pd_preferred_buyer_location,
                    pd_brand: pd_brand_name,
                    pd_payment: pd_payment,
                    pd_pod: pd_pod,
                    pd_pn_capct: pd_pn_capct,
                    pd_dlv_time: pd_dlv_time,
                    pd_pck_dets: pd_pck_dets
                }, function (data) {
                    data = data.trim();
                    dt = data.split("|");
                    if (dt[0] == '0') {
                        document.getElementById('updatemessage').style.display = "block";
                        document.getElementById('updatemessage').style.color = "red";
                        document.getElementById('updatemessage').innerHTML = dt[1];
                    } else {
                        alert(dt[1]);
                        showTempPhoto(uid.value);
                        window.location.reload();
                    }
                });
            }
            if (!valid) {
                document.getElementById('updatemessage').style.color = "red";
                document.getElementById('updatemessage').innerHTML = message;
            }
            return valid;
        }
        </script>
        <script>
        function showAddImage() { $('#drop').show(); }
        function hideAddImage() { $('#drop').hide(); }
        function showadditional() {
            $("[id^=t3]").attr('class', 'tab-sel2 f1 fw').css({height: '25px', width: '164px'});
            $("#t1").attr('class', 'tab-sel tab_p f1').css('background-position', '-0 -26px');
            $("#bafrm").hide('fast');
            $("#addef").show('fast');
            $("#adfrm").show();
        }
        function showmedit() {
            $("[id^=t1]").attr('class', 'tab-sel f1 fw').css('background-position', '0px -50px');
            $("#t3").attr('class', 'tab_p1 f1');
            $("#bafrm").show('fast');
            $("#addef").hide('fast');
        }
        </script>
        <style>
        .from-f1-fs1 {
            max-width: 33.3%;
            float: left;
            vertical-align: middle;
            margin-top: 10px !important;
        }
        .web-active {
            height: 25px !important;
            width: 164px !important;
            background-position: 0 -50px !important;
            padding-top: 6px !important;
            background-image: url(../images/tab_arrow.png) !important;
        }
        .web-deactive {
            background-position: -0 -26px;
            height: 25px !important;
            width: 164px !important;
            padding-top: 6px;
            background-image: url(../images/tab_arrow.png) !important;
        }
        </style>
    </head>
    <body class="add-product">
        <input type="hidden" value='' id='tempPhoto'/>
        <input type="hidden" value='' id='tempLogo'/>
        <div class="hm1 bbc" id="res-mob1">
            <?php include "includes/header_new.php"; ?>
            <br>
            <div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>
            <?php include 'includes/header_menu.php'; ?>
            <?php include 'includes/left_menu.php'; ?>
            <div class="w56b f1 p2b p14 blr">
                <div class="mt5">
                    <h1 style="margin-top:-5px !important;padding-bottom:5px;" title="Post New Products"> إعرض منتج جديد </h1>
                    <div class="tab_2">
                        <span id="t1" class="tab-sel f1 fw"><a onclick="showmedit();" title="Post Product">إعرض منتج </a></span>
                        <span id="t3" class="tab_p1 f1"><a onclick="showadditional();" title="Additional Details">تفاصيل إضافية للمنتج</a></span>
                        <div class="c3"></div>
                    </div>
                    <p class="urh"><span class="f1" style="font-size:14px;"></span>&nbsp;&nbsp;<strong style="color:#444444"></strong></p>
                    <div class="apfc" id="bafrm" style="border: 1px solid #ffffff;">
                        <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
                        <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
                        <script type="text/javascript">
                        jQuery(function () {
                            jQuery('#product_upload').uploadifive({
                                'auto': true,
                                'formData': {'usr': '<?php echo $uid; ?>'},
                                'queueID': 'queue',
                                'debug': true,
                                'method': 'post',
                                'uploadScript': 'ajax-file/addTempProductImg.php',
                                'onAddQueueItem': function (file) {
                                    $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width:125px;height:125px;margin-top:1px;margin-left:1px;"/>');
                                },
                                'onUploadComplete': function (file, data) {
                                    showTempPhoto(<?php echo $uid; ?>);
                                }
                            });
                        });
                        </script>
                        <div class="pia f1">
                            <div style="position:absolute" id="tempwarning_add"></div>
                            <p id="img_form" style="margin-top: 2px;"></p>
                            <div id="img_disp" class="cssie1 mover">
                                <img src="upload/myproduct/add-image.gif" title="حمل هنا - الصورة الرئيسية للمنتج - من جهاز الكمبيوتر - أو من جاليرى الموقع" style="width:125px;height:125px;margin-top:1px;margin-left:1px;" />
                            </div>
                            <div>
                                <div id="drop" style="position: absolute;background: rgba(0,0,0,0.5);color: #fff;width: 125px;margin-top: -30px;cursor: pointer;display:block;">
                                    <input type="file" id="product_upload" name="product_upload" style="width:125px;" title="ADD Product Image"/>
                                    <span class="file_input">Upload Image</span>
                                </div>
                                <div id="queue"></div>
                            </div>
                            <div class="add_color_page">
                                <link rel="stylesheet" href="css/colorbox.css" />
                                <script src="js/jquery.colorbox.js"></script>
                                <script>
                                $(document).ready(function () {
                                    $('.ajax').on('click', function () {
                                        $.colorbox({href: $(this).attr('href'), open: true});
                                        return false;
                                    });
                                    $(".inline").colorbox({inline: true, width: "50%"});
                                });
                                </script>
                                <a class="ajax add_color_page_a" href="popup-imagegallery.php" style="text-decoration:none;" title="Add Image From The Site Gallery"> حمل من صور الجاليرى</a>
                            </div>
                            <div id="remove_image" class="dn mt5">
                                <a href="javascript:remove_small_image('add');"><img src="images/remove.gif" align="absmiddle" width="44" height="10"></a>
                            </div>

                            <!-- Logo upload section -->
                            <div style="position:absolute" id="tempwarning_logo"></div>
                            <p id="img_form_logo" style="margin-top: 50px;"></p>
                            <div id="imglogo_disps" class="cssie1 mover" style="position: relative;">
                                <img src="upload/myproduct/logo_upload.jpg" title="يمكنك إضافة صورة صغيرة - إختيارى - تظهر على أسفل يسار الصورة الأصلية - مثل لوجو أو علامة أو عرض خاص" style="width:125px;height:125px;margin-top:1px;margin-left:1px;">
                                <div id="imglogo_disp" class="cssie1 mover" style="width: 43px; height: 46px; left: 4px; bottom: 30px; position: absolute;"></div>
                            </div>
                            <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
                            <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
                            <script type="text/javascript">
                            jQuery(function () {
                                jQuery('#productlogo_upload').uploadifive({
                                    'auto': true,
                                    'formData': {'usr': '<?php echo $uid; ?>'},
                                    'queueID': 'queue_logo',
                                    'debug': true,
                                    'method': 'post',
                                    'uploadScript': 'ajax-file/addTempProductImglogo.php',
                                    'onAddQueueItem': function (file) {
                                        $("#imglogo_disp").html('<img src="images/loader.gif" alt="Uploading...." style="width: 43px; height: 46px;"/>');
                                    },
                                    'onUploadComplete': function (file, data) {
                                        showTempPhotoLogo(<?php echo $uid; ?>);
                                    }
                                });
                            });
                            </script>
                            <div>
                                <div id="drop_logo" style="position: absolute;background: rgba(0,0,0,0.5);color: #fff;width: 125px;margin-top: -30px;cursor: pointer;display:block;">
                                    <input type="file" id="productlogo_upload" name="productlogo_upload" style="font-width:200" title="ADD Logo,Brand,Discount,Sign"/>
                                </div>
                                <div id="queue_logo"></div>
                            </div>
                            <div class="add_color_page">
                                <link rel="stylesheet" href="css/colorbox.css" />
                                <script src="js/jquery.colorbox.js"></script>
                                <script>
                                $(document).ready(function () {
                                    $('.ajaxa').on('click', function () {
                                        $.colorbox({href: $(this).attr('href'), open: true});
                                        return false;
                                    });
                                    $(".inline").colorbox({inline: true, width: "50%"});
                                });
                                </script>
                                <a class="ajaxa add_color_page_a" href="popup-imagegallery-logo.php" style="text-decoration:none;" title="Add Image From The Site Gallery">حمل من صور الجاليرى</a>
                            </div>
                            <div id="remove_logo_image" class="dn mt5">
                                <a href="javascript:remove_small_image('logo');"><img src="images/remove.gif" align="absmiddle" width="44" height="10"></a>
                            </div>
                        </div>

                        <div class="right-side-addproduct">
                            <form action="" method="POST" id="add_new" name="add_new" enctype="multipart/form-data">
                                <div class="fside f1">
                                    <div id="updatemessage" style="margin-bottom:5px;"><?php echo isset($msg) ? $msg : ''; ?></div>
                                    <input type="hidden" id="pd_subcat_id" name="pd_subcat_id" value="<?php echo isset($_POST['pd_subcat_id']) ? htmlspecialchars($_POST['pd_subcat_id']) : ''; ?>" />
                                    <div class="fs1 f1" style="width: 50% !important;" title="أكتب عنوان المنتج باللغة العربية أو الإنجليزية حسب مكان البيع">
                                        <p><span style="line-height: 12px;">*</span> عنوان المنتج   </p>
                                        <input name="pd_title" id="pd_title" maxlength="60" class="a_f pf1" type="text" value="<?php echo isset($pd_title) ? htmlspecialchars($pd_title) : ''; ?>">
                                        <span></span>
                                    </div>
                                    <div class="fs2 f1" style="width: 50% !important;" title="Product Item Code">
                                        <p> أكتب كود المنتج </p>
                                        <input name="pd_code" id="pd_code" maxlength="60" class="a_f pf1" onblur="check_duplicate_item_code()" type="text" value="<?php echo isset($pd_code) ? htmlspecialchars($pd_code) : ''; ?>">
                                        <span></span>
                                        <input type="hidden" name="uid" id="uid" value="<?php echo $uid; ?>" />
                                    </div>
                                    <div style="clear: both;"></div>
                                    <div class="fs2 f1" style="width: 50% !important;" title="Minimum Quantity">
                                        <p><span style="line-height: 12px;">*</span> أكتب أقل كمية لبيع المنتج</p>
                                        <input name="pd_min_order_qty" id="pd_min_order_qty" maxlength="60" class="a_f pf1" type="text" value="<?php echo isset($pd_min_order_qty) ? htmlspecialchars($pd_min_order_qty) : ''; ?>">
                                        <span></span>
                                    </div>
                                    <div class="fs2 f1" style="width: 50% !important;">
                                        <p>&nbsp;</p>
                                        <select size="1" name="pd_unit" id="pd_unit" class="a_f s_u" title="إختار وحدة قياس المنتج">
                                            <option value="">-إختار وحدة القياس-</option>
                                            <?php
                                            $unitsql = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_status='1'");
                                            while ($unitrow = mysqli_fetch_object($unitsql)) { ?>
                                                <option value="<?php echo $unitrow->mu_id; ?>" <?php if (isset($pd_unit) && $pd_unit == $unitrow->mu_id) echo 'selected="selected"'; ?>><?php echo $unitrow->mu_name; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="fs1 f1">
                                        <div class="form-fs1-f1" title="FOB / WholeSale Price">
                                            <p><span style="line-height: 12px;"></span> : أكتب سعر بيع المنتج محلى أو تصدير  </p>
                                            <div class="from-f1-fs1" title="From"><span>من :</span> <input name="pd_fob_price" id="pd_fob_price" maxlength="60" class="a_f pf1" type="text" value="<?php echo isset($pd_fob_price) ? htmlspecialchars($pd_fob_price) : ''; ?>"></div>
                                        </div>
                                        <div class="fs1 f1" style="width: 33.3% !important;">
                                            <div class="to-f1-fs2" title="To"> <span> الى :</span> <input name="pd_fob_price2" id="pd_fob_price2" maxlength="60" class="a_f pf1" type="text" value="<?php echo isset($pd_fob_price2) ? htmlspecialchars($pd_fob_price2) : ''; ?>"></div>
                                            <span></span>
                                        </div>
                                        <div class="fs2 f1" style="width: 33.3% !important;" title="إختار عملة البيع - حسب البلد - التى تبيع لها المنتج">
                                            <p>&nbsp;</p>
                                            <select size="1" name="pd_currency" id="pd_currency" class="a_f s_u">
                                                <option value="">-إختار عملة البيع -</option>
                                                <?php
                                                $currencysql = mysqli_query($con, "SELECT * FROM country WHERE cn_status='1'");
                                                while ($currencyrow = mysqli_fetch_object($currencysql)) { ?>
                                                    <option value="<?php echo $currencyrow->cn_id; ?>" <?php
                                                        if (function_exists('user_info') && user_info($uid, 'country') == $currencyrow->cn_id) echo 'selected="selected"';
                                                        elseif (isset($pd_currency) && $pd_currency == $currencyrow->cn_id) echo 'selected="selected"';
                                                    ?>><?php echo $currencyrow->cn_currency; ?></option>
                                                <?php } ?>
                                            </select>
                                            <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                        </div>
                                    </div>
                                    <div class="fs1 f1" style="padding-top:4px; width: 50% !important; float: right;" title="Location Preferences">
                                        <p><span style="line-height: 12px;">*</span>إختار مكان بيع هذا المنتج</p>
                                        <div style="vertical-align:middle">
                                            <label style="top:0px;" title="Abroad Only">- هذا المنتج للتصدير فقط خارج بلدى</label> <input type="radio" id="pd_preferred_buyer_location_1" name="pd_preferred_buyer_location" value="abroad" />
                                        </div>
                                        <div>
                                            <label style="top:0px;" title="Abroad + Domestic">- المنتج للتصدير أو للبيع فى بلدى</label> <input type="radio" id="pd_preferred_buyer_location_2" name="pd_preferred_buyer_location" value="any" checked="checked"/>
                                        </div>
                                        <div>
                                            <label style="top:0px;" title="Domestic Only"> - هذا المنتج للبيع داخل بلدى فقط </label> <input type="radio" id="pd_preferred_buyer_location_3" name="pd_preferred_buyer_location" value="domestic"/>
                                        </div>
                                        <div>
                                            <label style="top:0px;" title="My City Only"> - المنتج للبيع داخل مدينتى فقط</label> <input type="radio" id="pd_preferred_buyer_location_4" name="pd_preferred_buyer_location" value="my_city"/>
                                        </div>
                                    </div>
                                    <div class="fs1 f1" style="padding-top:4px; width: 50% !important; float: left;" title="Product Display Status">
                                        <div>
                                            <p><span style="line-height: 12px;">*</span>حالة عرض المنتج  </p>
                                            <label>العرض فى صفحة المنتجات الهامة </label> <input class="rad" name="pd_hot" value="1" type="radio" checked="checked">
                                            <label>العرض فى صفحة المنتجات العادية</label> <input name="pd_hot" value="0" id="pd_hot" type="radio">
                                        </div>
                                    </div>
                                    <div style="clear:both;line-height: 21px;*line-height: 17px;">&nbsp;</div>
                                    <div class="fs1">&nbsp;</div>
                                    <div style="margin:5px 0"></div>
                                    <div id="editor_loading" style="display: none; padding: 230px; height: 0px;">
                                        <img src="images/indicator.gif">&nbsp;Loading editor...
                                    </div>
                                    <div id="div_save">
                                        <input name="btnSubmit" id="btnSubmit" class="c3 f2 saps mt12 mtt" style="margin-top:-35px;margin-right:-1px; -webkit-margin-before:-33px;*margin-top:-35px;margin-top:-33px\9;color:#fff;" title="Add Product" value="أضف المنتج " type="button" onclick="additem2();" />
                                    </div>
                                    <div class="c3 fs3" id="editor" style="">
                                        <p>وصف وتفاصيل المنتج</p>
                                        <textarea class="a_f" rows="15" id="pd_desc" name="pd_desc" onKeyUp="mecount();"><?php echo isset($pd_desc) ? htmlspecialchars($pd_desc) : ''; ?></textarea>
                                        <div class="max"><span id="cn" style="color:#ff8000">0</span><font id="Charcount" color="#ff8000"> character (maximum of 4000) </font> character(s).</div>
                                        <br>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="c3"></div>
                    </div>

                    <!-- Additional Details Tab Content -->
                    <div id="addef" class="p-irt" style="overflow: hidden; display: none; border: 1px solid #ffffff; background-color: #FAF4FF;">
                        <p class="bulb" title="Additional product details help your product listing gains more visibility to interested buyers">تسجيل تفاصيل إضافية لمنتجك تساعد على كسب رؤية أكبر تجذب المشتريين</p>
                        <p class="b-img arp1 pdb1 f1" href="JavaScript:;" title="Additional Details">تفاصيل المنتج الإضافية </p>
                        <div class="c3"></div>
                        <div id="abc"></div>
                        <div style="display:block;" id="adfrm">
                            <form method="post" name="additional_details">
                                <div class="pad f1 mr">
                                    <ul>
                                        <li class="f1 tc adtp" title="Product Brand Name">إسم ماركة المنتج المعروفة</li>
                                        <li class="f1 pct pbi tc1">
                                            <input name="pd_brand_name" id="pd_brand_name" maxlength="255" value="" class="a_f adt" type="text">
                                        </li>
                                        <li class="f1 tc adtp" title="Payment Terms">طريقـة الدفـع </li>
                                        <li class="f1 pct pbi tc1">
                                            <?php
                                            $paymentres = mysqli_query($con, "SELECT * FROM payment_method");
                                            ?>
                                            <span class="d-blcok" id="payOptionButton" data-toggle="collapse" data-target="#payOptions" style="cursor: pointer;">
                                                Select Options
                                                <span class="payOptionButtonArrowDown" style="width: max-content; transform: rotate(90deg); display: inline-block;">&#10151;</span>
                                            </span>
                                            <div class="collapse fade" id="payOptions" style="padding: 4px 14px">
                                            <?php while ($paymentrow = mysqli_fetch_object($paymentres)) {
                                                echo '<input class="cb1" name="pd_payment" value="' . $paymentrow->ph_id . '" type="checkbox" /> ' . htmlspecialchars($paymentrow->ph_title) . "<br>";
                                            } ?>
                                            </div>
                                        </li>
                                        <input name="in_pc_item_payment_terms" id="in_pc_item_payment_terms" value="L/C (Letter of Credit),D/A,T/T (Bank Transfer),Other" type="hidden">
                                        <li class="f1 tc adtp" title="Port of Dispatch">مكان أو ميناء التسليم</li>
                                        <li class="f1 pct">
                                            <input class="a_f adt" name="pd_pod" id="pd_pod" maxlength="100" value="" type="text">
                                        </li>
                                        <li class="f1 tc adtp" title="Production Capacity"> معدل الانتاج</li>
                                        <li class="f1 pct">
                                            <input name="pd_pn_capct" id="pd_pn_capct" maxlength="100" value="" class="a_f adt" type="text">
                                        </li>
                                        <li class="f1 tc adtp" title="Delivery Time">موعد  التسليم </li>
                                        <li class="f1 pct">
                                            <input class="a_f adt" name="pd_dlv_time" id="pd_dlv_time" maxlength="100" value="" type="text">
                                        </li>
                                        <li class="f1 tc adtp" title="Packing Details">طريقة التعبئة والتغليف </li>
                                        <li class="f1 pct">
                                            <textarea rows="5" class="a_f" name="pd_pck_dets" id="pd_pck_dets" onkeyup="packdetcount();"></textarea>
                                            <font id="Charcount1" color="#ff8000"><span id="pckdet">20</span> character (maximum of 2000)</font> character(s).
                                        </li>
                                        <li class="f1 tc adtp"> </li>
                                        <li>
                                            <input name="updateaddi" class="saps awt mt12 m5" title="Save Details" value="إحفظ التغييرات" type="button" onclick="additem2();">
                                            <span style="display:none;" id="adsaving"><img alt="" src="editproduct-step1_files/loading.gif" border="0" width="16" height="11"></span>
                                            <input name="cancleaddi" class="saps mt12 ml8" value="الغــاء" id="adcls" type="button" onclick="showmedit();">
                                        </li>
                                    </ul>
                                </div>
                            </form>
                            <div class="c3"></div>
                        </div>
                    </div>
                    <!-- Additional Details End -->

                </div>
            </div>
            <div class="c3">&nbsp;</div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </body>
</html>
