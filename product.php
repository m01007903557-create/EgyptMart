<?php
// products.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
session_start();
include 'common.php';
set_time_limit(600);

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
$pc_id = isset($_GET['c']) ? mysqli_real_escape_string($con, $_GET['c']) : '';

// شروط البحث حسب الموقع
if (isset($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country='{$loc_id}'))
        OR
        (pd_preferred_buyer_location='my_city' AND pd_uid IN (SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city=(SELECT ct_id FROM city WHERE ct_cn_id='{$loc_id}')))
    )";
} else {
    $country_code = isset($location_geo_country) ? mysqli_real_escape_string($con, $location_geo_country) : '';
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location='any')
        OR
        (pd_preferred_buyer_location='abroad' AND pd_uid NOT IN (SELECT DISTINCT usr_id FROM user WHERE country=(SELECT cn_id FROM country WHERE cn_code='{$country_code}')))
    )";
}

// يمكن تعطيل الشرط إذا لزم الأمر
$sql_pd_ck = "";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <style type="text/css">
    @media (width: 1280px) {
        .footer .footer-searchsec {
            max-width: 860px !important;
        }
        .footer .footer-searchsec-right {
            margin-left: 8px !important;
        }
    }
    </style>
    
    <script src="js/jquery.js"></script>
    
    <script type="text/javascript">
    var is_sub = false;
    var main_cat = '<?php echo htmlspecialchars(addslashes($_GET['c'] ?? '')); ?>';
    
    function loadProductByCategory(page, id, flag = 0) {
        is_sub = flag;
        $("#header_load").show();
        $("#current_cat").val(id);
        
        let value = '';
        $("input[name=mst_type]").each(function() {
            if ($(this).prop('checked')) {
                value += $(this).val() + ',';
            }
        });
        let members = value.slice(0, -1);
        let min_order = $("#min_order").val();
        
        $.post("ajax-file/loadProductByCategory.php", {
            page: page,
            id: id,
            mst_type: members,
            min_order: min_order,
            is_sub: flag
        }, function(data) {
            $('#res_row').html(data);
            $("#header_load").hide();
        });
    }
    
    function loadProductBySubCategory(page, id) {
        $("#header_load").show();
        $("#current_cat").val(id);
        
        let value = '';
        $("input[name=mst_type]").each(function() {
            if ($(this).prop('checked')) {
                value += $(this).val() + ',';
            }
        });
        let members = value.slice(0, -1);
        let min_order = $("#min_order").val();
        
        $.post("ajax-file/loadProductBySubCategory.php", {
            page: page,
            id: id,
            mst_type: members,
            min_order: min_order
        }, function(data) {
            $('#res_row').html(data);
            $("#header_load").hide();
        });
    }
    
    function refineProductBySubCategory(page, id, flag = false) {
        if (flag === false) {
            flag = is_sub;
        } else if (flag == -1) {
            is_sub = false;
            flag = false;
        } else {
            is_sub = flag;
        }
        
        $("#header_load_sub").show();
        $("#current_cat").val(id);
        
        let value = '';
        $("input[name=mst_type]").each(function() {
            if ($(this).prop('checked')) {
                value += $(this).val() + ',';
            }
        });
        let members = value.slice(0, -1);
        
        value = '';
        $("input[name=country_sel]").each(function() {
            if ($(this).prop('checked')) {
                value += $(this).val() + ',';
            }
        });
        let countries = value.slice(0, -1);
        
        value = '';
        $("input[name=state_sel]").each(function() {
            if ($(this).prop('checked')) {
                value += $(this).val() + ',';
            }
        });
        let states = value.slice(0, -1);
        
        let min_order = $("#min_order").val();
        let city = $("input[name=scity]").val();
        
        $("#product_slider").html("");
        
        $.post("ajax-file/refineProductBySubCategory_new.php", {
            page: page,
            id: id,
            mst_type: members,
            country: countries,
            state: states,
            city: city,
            min_order: min_order,
            is_sub: flag
        }, function(data) {
            $('#product_slider').html(data);
            
            $.post("ajax-file/loadLeftCats.php", {
                page: page,
                id: id,
                mst_type: members,
                country: countries,
                state: states,
                city: city,
                min_order: min_order,
                is_sub: flag
            }, function(data) {
                $('#leftCats').html(data);
                $("#header_load_sub").hide();
            });
        });
    }
    
    $(document).ready(function() {
        <?php if (isset($_GET['c'])): ?>
            loadProductByCategory(1, '<?php echo htmlspecialchars($_GET['c']); ?>');
        <?php endif; ?>
        
        <?php if (isset($_GET['sc'])): ?>
            loadProductBySubCategory(1, '<?php echo htmlspecialchars($_GET['sc']); ?>');
        <?php endif; ?>
        
        $(document).on('click', "#minorder", function() {
            $('div#product_slider').css("opacity", "0.5");
            $('#header_load').css({"display": "block", "margin-top": "100px"});
            
            let minorder = $('#min_order').val();
            let id = '<?php echo htmlspecialchars($_GET['c'] ?? ''); ?>';
            
            $.ajax({
                url: "ajax-file/minporder.php",
                method: "POST",
                dataType: "html",
                data: {minorder: minorder, id: id},
                success: function(result) {
                    $("#res").html(result);
                    $('#header_load').css("display", "none");
                }
            });
        });
        
        $(document).on('click', "#showcnt", function() {
            $('.countries').toggle();
            $("#showcnt>i").toggleClass('fa-sort-desc fa-sort-asc');
        });
        
        $(document).on('click', ".cnt_state", function() {
            let id = $(this).attr('id');
            let cid = $("#current_cat").val();
            
            $('.state_section').css({"display": "block"});
            $('.countries_inner').css({"display": "none"});
            
            $.ajax({
                url: "ajax-file/slectedstate.php",
                method: "POST",
                dataType: "html",
                data: {id: id, cid: cid, is_sub: is_sub},
                success: function(result) {
                    $(".state_section").html(result);
                    $("input[name=country_sel][value=" + id + "]").prop('checked', false);
                }
            });
        });
        
        $(document).on('click', ".close_state", function() {
            $('.state_section').css({"display": "none"});
            $('.countries_inner').css({"display": "block"});
        });
    });
    
    function toggle_menu() {
        if ($('#cssmenu').hasClass('menu-active')) {
            $("#downarrow").css('display', 'inline');
            $("#uparrow").css('display', 'none');
        } else {
            $("#uparrow").css('display', 'inline');
            $("#downarrow").css('display', 'none');
        }
        $("#cssmenu").toggleClass("menu-active");
    }
    
    function filter_member() {
        refineProductBySubCategory(1, $("#current_cat").val());
    }
    </script>
    
    <script src="js/jquery.als-1.6.js"></script>
    <link href="css/im-style-v1.css" rel="stylesheet" type="text/css">
    
    <style>
    /*************************************
     * generic styling for ALS elements
     ************************************/
    .als-container {
        position: relative;
        width: 100%;
        margin: 0px auto;
        z-index: 0;
    }
    
    .als-viewport {
        position: relative;
        overflow: hidden;
        margin: 0px auto;
    }
    
    .als-wrapper {
        position: relative;
        list-style: none;
    }
    
    .als-item {
        position: relative;
        display: block;
        text-align: center;
        cursor: pointer;
        transition: transform .2s;
        float: left;
        width: 18.5%;
    }
    
    .als-item:hover {
        box-shadow: 0 0 10px;
        transform: scale(1.05);
    }
    
    .als-prev, .als-next {
        position: absolute;
        cursor: pointer;
        clear: both;
    }
    
    .als-item a div > span {
        font-size: 15px !important;
    }
    
    .utext:hover {
        text-decoration: underline;
        color: #d81921 !important;
    }
    
    p.cnt_supplier {
        padding: 5px 0;
        background: #2acf00;
        color: #fff;
        border-radius: 3px;
    }
    
    p.cnt-phone {
        padding: 7px 5px;
        background: #eee;
        color: #000;
        border-radius: 3px;
    }
    
    span.cnt-phone-inner img {
        width: 22px !important;
        float: left;
        margin-top: -2px !important;
    }
    
    /*************************************
     * specific styling for #demo3
     ************************************/
    #product_slider {
        margin: 2px auto;
    }
    
    #product_slider .als-item {
        padding: 4px 0px;
        min-height: 150px;
        text-align: justify;
    }
    
    #product_slider .als-item img {
        width: 100%;
        margin: 0 auto;
        vertical-align: middle;
        max-height: 95%;
        max-width: 95%;
        width: auto;
    }
    
    #product_slider .als-prev, #product_slider .als-next {
        top: 60px;
    }
    
    #product_slider .als-prev {
        left: 20px;
    }
    
    #product_slider .als-next {
        right: 20px;
    }
    
    #saleoffer_slider {
        margin: 2px auto;
    }
    
    #saleoffer_slider .als-item {
        margin: 0px 5px;
        padding: 4px 0px;
        min-height: 140px;
        min-width: 120px;
        text-align: center;
    }
    
    #saleoffer_slider .als-item img {
        display: block;
        margin: 0 auto;
        vertical-align: middle;
    }
    
    #saleoffer_slider .als-prev, #saleoffer_slider .als-next {
        top: 60px;
    }
    
    #saleoffer_slider .als-prev {
        left: 20px;
    }
    
    #saleoffer_slider .als-next {
        right: 20px;
    }
    
    #res_row > #cssmenu.hidden {
        display: none !important;
    }
    
    .left-side-bar-sale-offer h4 {
        font-size: 15px;
        font-weight: 600;
    }
    
    .post-product-btn {
        font-size: 13px !important;
    }
    
    .page-header-col2-intro-texts .post-product-btn small {
        font-size: 8px !important;
    }
    
    div#res a {
        color: #237abf;
    }
    
    .search-show-box-buyleads.products-categories-listing #res {
        width: calc(100% - 210px) !important;
        padding-left: 0;
    }
    
    div.countries {
        margin-top: 2px;
        background-color: #fff;
        padding: 10px;
    }
    
    #showcnt {
        cursor: pointer;
        box-shadow: 0 0 1px;
        padding: 2px;
    }
    
    .cnt-phone a {
        font-size: 14px;
        color: black;
        font-weight: bold;
        display: inline-block;
    }
    
    p.cnt-phone {
        padding-right: 2px;
    }
    
    .cnt_supplier {
        text-align: center;
    }
    
    .cnt_supplier span {
        color: white;
        font-size: 13px;
    }
    
    #img-div {
        text-align: center;
    }
    
    .utext {
        height: 30px;
    }
    
    .togle_style:hover {
        color: #FF751A !important;
    }
    
    #getcitydata {
        float: right;
        border: 1px solid #ddd;
        padding: 0px !important;
        width: 100% !important;
        text-align: right;
        margin-right: -43px;
        margin-top: 6px;
    }
    
    #scity {
        width: 87%;
        float: left;
        border: none;
        height: 19px;
        padding-top: 14px;
        padding-bottom: 9px;
    }
    
    .scity_btn {
        font-size: 13px;
        margin-top: 2px;
        margin-right: 3px;
        padding: 1px;
    }
    
    .main-warpp #topbar ul {
        min-width: 160px !important;
    }
    
    .checkbox-inline + .checkbox-inline {
        margin-top: 0;
        margin-left: 58px !important;
    }
    
    .min_quan {
        margin-top: 8px !important;
        float: left !important;
        width: auto !important;
        margin-left: -25px !important;
        padding-left: 0 !important;
    }
    
    .cnt-phone a {
        font-weight: 800;
        font-size: 13px;
    }
    
    .span_red {
        color: red;
        font-size: 14px !important;
        font-weight: bold;
    }
    
    .utext {
        color: #2b2b2b;
        font-size: 14px !important;
        padding: 0px;
        text-align: center;
        height: auto;
        margin-bottom: 6px;
    }
    
    .als-item {
        border: 1px solid #ccc;
        margin-top: 1%;
        margin-left: 0%;
        margin-right: 1%;
        padding: 4px !important;
        margin-bottom: 1%;
        border-radius: 4px;
        float: left;
        height: auto;
        background-color: #fff;
        height: 350px;
    }
    
    @media only screen and (min-width: 750px) and (max-width: 1024px) {
        .search-show-box-buyleads, #final_result {
            width: 100% !important;
            padding-left: 0;
            padding-right: 0;
        }
        .checkbox-inline {
            margin-left: 30px !important;
        }
    }
    
    @media only screen and (max-width: 768px) {
        .search-show-box-buyleads, #final_result {
            width: 100% !important;
            padding-left: 0;
            padding-right: 0;
        }
        .als-item {
            border: 1px solid #ccc;
            margin-top: 1%;
            margin-left: 0%;
            margin-right: 1%;
            padding: 4px !important;
            margin-bottom: 1%;
            border-radius: 4px;
            float: left;
            height: auto;
            background-color: rgba(251, 251, 251, 0.96);
            height: 350px;
        }
        .cnt_supplier_inner {
            font-size: 13px !important;
            font-weight: bold;
            padding: 0px !important;
        }
        .utext {
            color: #2b2b2b;
            font-size: 13px !important;
            padding: 0px;
            text-align: center;
            height: auto;
            margin-bottom: 6px;
        }
        .span_red {
            color: red !important;
            font-size: 13px !important;
            font-weight: bold;
        }
        .min_quan {
            margin-top: 8px !important;
            float: left !important;
            width: auto !important;
            margin-left: 25px !important;
            padding-left: 0 !important;
        }
        #getcitydata {
            float: left;
            border: 1px solid #ddd;
            padding: 5px;
            width: 159px !important;
            text-align: right;
        }
        .scity_btn {
            font-size: 16px !important;
            margin-top: 2px !important;
            margin-right: 3px !important;
            padding: 6px !important;
        }
        .fa_search {
            font-size: 12px !important;
            padding: 1px !important;
            position: absolute !important;
            right: -22px !important;
            top: 10px !important;
        }
        .checkbox-inline {
            margin-left: 22px !important;
            padding-bottom: 8px !important;
        }
        .checkbox-inline + .checkbox-inline {
            margin-top: 0;
            margin-left: 22px !important;
        }
        .mycol {
            margin-left: -20px !important;
        }
        span.cnt-phone-inner img {
            width: 16px !important;
            float: left;
            margin-top: 0px !important;
        }
        .cnt-phone a {
            font-weight: 800;
            font-size: 10px;
        }
        .als-viewport {
            position: relative;
            overflow: hidden;
        }
        .min_order {
            margin-left: -10px !important;
        }
        .min_order1 {
            margin-left: 0px !important;
        }
        .min_btn {
            margin-top: -7px !important;
        }
        .togle_style {
            font-size: 18px !important;
        }
    }
    
    #product_slider .als-item {
        margin: 4px !important;
    }
    
    .prc-right-side, #final_result {
        padding-left: 0px !important;
    }
    </style>
    
    
</head>

<body class="search-show-box-buyleads products-categories-listing">
    <input type="hidden" id="current_cat" value="">
    
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
    
    <?php include "includes/header_new.php"; ?>
    
    <?php
    $sql_order = (get_page_settings('25') == 'manual') ? " ORDER BY pc_order, pc_name" : " ORDER BY pc_name";
    ?>
    
    <div id="header_load" style="text-align: center;display: none">
        <img src="https://egyptmart.shop/images/loadinggif.gif" style="height: 100px"/>
    </div>
    
    <div id="res_row" class="maincontainertop clearfix custom_quick_fix"></div>
    
    <p class="cb"><br></p>
   
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>
$(document).ready(function() {
    if ($(window).width() <= 768) {
        $(".demobox").addClass("vertical-slider-mobile");
        $(".vertical-slider-mobile").slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 3000,
            dots: true,
            arrows: true,
            vertical: true,
            verticalSwiping: true,
            responsive: [
                { breakpoint: 768, settings: { vertical: true, slidesToShow: 1 } }
            ]
        });
    }
});
<?php include $_SERVER['DOCUMENT_ROOT'] . '/whatsapp_popup_code.php'; ?>


<!-- Popup واتساب -->
<div id="waModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; width:400px; max-width:90%; margin:100px auto; padding:25px; border-radius:10px; direction:rtl;">
        <span onclick="closeWaModal()" style="float:left; cursor:pointer; font-size:20px;">&times;</span>
        <h3 style="color:#25D366; margin-top:0;">طلب سعر عبر واتساب</h3>
        <form id="waForm">
            <input type="hidden" id="wa_pid">
            <input type="hidden" id="wa_pname">
            <div style="margin-bottom:15px;">
                <label>الكمية التقريبية (من)</label>
                <input type="number" id="wa_qty_from" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
            </div>
            <div style="margin-bottom:15px;">
                <label>إلى</label>
                <input type="number" id="wa_qty_to" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
            </div>
            <div style="margin-bottom:15px;">
                <label>التفاصيل</label>
                <textarea id="wa_details" rows="4" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></textarea>
            </div>
            <button type="submit" style="background:#25D366; color:#fff; border:none; padding:12px; width:100%; border-radius:5px; font-size:16px; cursor:pointer;">إرسال الطلب</button>
        </form>
    </div>
</div>

<script>
function openWaRfq(pid, pname) {
    console.log("Product ID:", pid);
    console.log("Product Name:", pname);
    if (!pid || pid == 0) {
        alert('خطأ: لم يتم العثور على معرف المنتج');
        return;
    }
    document.getElementById('wa_pid').value = pid;
    document.getElementById('wa_pname').value = pname;
    document.getElementById('waModal').style.display = 'block';
}

function closeWaModal() {
    document.getElementById('waModal').style.display = 'none';
}

document.getElementById('waForm').onsubmit = async function(e) {
    e.preventDefault();
    
    var pid = document.getElementById('wa_pid').value;
    if (!pid || pid == 0) {
        alert('خطأ: معرف المنتج غير صالح');
        return;
    }
    
    let btn = this.querySelector('button');
    btn.disabled = true;
    btn.innerText = 'جاري الإرسال...';
    
    let formData = new FormData();
    formData.append('product_id', pid);
    formData.append('product_name', document.getElementById('wa_pname').value);
    formData.append('qty_from', document.getElementById('wa_qty_from').value);
    formData.append('qty_to', document.getElementById('wa_qty_to').value);
    formData.append('requirement_details', document.getElementById('wa_details').value);
    
    try {
        let res = await fetch('/whatsapp_rfq_handler.php', {method:'POST', body:formData});
        let data = await res.json();
        if(data.success) {
            alert('✅ Your RFQ has been noted, suppliers will contact you soon.');
            window.open(data.whatsapp_url, '_blank');
            closeWaModal();
            document.getElementById('waForm').reset();
        } else {
            alert('❌ ' + data.error);
        }
    } catch(error) {
        alert('خطأ في الاتصال: ' + error.message);
    }
    btn.disabled = false;
    btn.innerText = 'إرسال الطلب';
};
</script>
</body>

</html>