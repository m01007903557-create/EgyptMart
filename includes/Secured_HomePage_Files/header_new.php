<?php
// includes/header_new.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>


<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="../fonts/font-awesome.css" rel="stylesheet" type="text/css"/>
<link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>

<link href="css/style.css?t=<?php echo rand(); ?>" rel="stylesheet" type="text/css"/>
<link href="css/style123.css?t=<?php echo rand(); ?>" type="text/css" rel="stylesheet" />
<link href="css/responsive1.css" rel="stylesheet" type="text/css"/>
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="css/bootstrap.buyleads-new.min.css" rel="stylesheet" type="text/css"/>
<link href="css/main-style.css?r=<?php echo time(); ?>" rel="stylesheet" type="text/css"/>
<link href="css/im-style-v1.css" rel="stylesheet" type="text/css"/>
<link href="css/new_responsive.css" rel="stylesheet" type="text/css"/>
<link href="../css/main.css" rel="stylesheet" type="text/css"/>

<!-- Start of wrapper -->
<script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="css/slick.css">
<link rel="stylesheet" type="text/css" href="css/slick-theme.css">

<div class="wrapper">
    <script type="text/javascript">
    function showmymenu() {
        $("#mn1").show();
    }
    function hidemymenu() {
        $("#mn1").hide();
    }
    function showLocMenu() {
        $("#changeLocation").show();
    }
    function hideLocMenu() {
        $("#changeLocation").hide();
    }
    function showbuymenu() {
        $("#buymnu").show();
    }
    function hidebuymenu() {
        $("#buymnu").hide();
    }
    function showsellmenu() {
        $("#sellmnu").show();
    }
    function hidesellmenu() {
        $("#sellmnu").hide();
    }
    function showsrchm() {
        $("#smnu").show();
    }
    function hidesrchm() {
        $("#smnu").hide();
    }
    function OutboundLink(type) {
        let a1 = $("#a1");
        if (type == 'buy_lead') {
            a1.html("Buy Leads");
        } else if (type == 'tender') {
            a1.html("Tender");
        } else if (type == 'auction') {
            a1.html("Auction");
        } else {
            a1.html(type);
        }
        $("#rctyp").val(type);
        $("#smnu").hide();
    }

    function validsearch() {
        $('#search-box11').val($.trim($('#search-box11').val()));
        var keywords = document.getElementById('keywords');
        if (!keywords.value || keywords.value.trim() === '') {
            alert("Please enter a valid text to search.");
            return false;
        }
        return true;
    }

    function gotFocus() {
        var keywords = $("input#keywords").val();
        if (keywords === 'Enter product / service to search' || 
            keywords === 'Enter Buy Lead to search' || 
            keywords === 'Enter Supplier to search' ||
            keywords === 'Enter Tender to search') {
            $("input#keywords").val('');
        }
    }

    function lostFocus() {
        var type = $("#keyword_type").val();
        var keywords = $("input#keywords").val();
        var searchBox = $("input#keywords");
        
        if (type === 'Products' && (!keywords || keywords === 'Enter Buy Lead to search' || keywords === 'Enter Supplier to search' || keywords === 'Enter Tender to search')) {
            searchBox.val('Search Product');
        } else if (type === 'Buy Leads' && (!keywords || keywords === 'Enter product / service to search' || keywords === 'Enter Supplier to search')) {
            searchBox.val('Enter Buy Lead to search');
        } else if (type === 'Suppliers' && (!keywords || keywords === 'Enter product / service to search' || keywords === 'Enter Buy Lead to search')) {
            searchBox.val('Enter Supplier to search');
        } else if (type === 'Tender' && (!keywords || keywords === 'Enter product / service to search' || keywords === 'Enter Buy Lead to search' || keywords === 'Enter Tender to search')) {
            searchBox.val('Enter Tender to search');
        }
    }

    function enter_search() {
        $('.loading-text').removeClass('hide').addClass('show');
    }

    function setCountryLocation(id) {
        $.post("setCountryLocation.php", {loc_id: id}, function(data) {
            if (data != 0) {
                location.reload();
            }
        });
    }

    function unsetCountryLocation() {
        $.post("unsetCountryLocation.php", function(data) {
            location.reload();
        });
    }

    $(".center").slick({ 
        dots: true,
        infinite: true,
        centerMode: true,
        slidesToShow: 5,
        slidesToScroll: 3
    });
    </script>

    <style type="text/css">
    @media (width: 1280px) {
        .footer-searchsec {
            max-width: 840px !important;
        }
        .footer-searchsec-left {
            width: calc(100% - 37%);
        }
    }
    span.loading-text {
        position: absolute;
        top: 0;
        right: 55px;
        color: red;
        font-size: 20px;
    }
    .zoomin1 img { 
        height: 78px; 
        width: 219px; 
        -webkit-transition: all 0.5s ease; 
        -moz-transition: all 0.5s ease; 
        -ms-transition: all 0.5s ease; 
        transition: all 0.5s ease; 
    }
    .zoomin1 img:hover { 
        width: 229px; 
        height: 88px; 
    }
    .zoomin2 img { 
        height: 66px; 
        width: 200px; 
        -webkit-transition: all 0.5s ease; 
        -moz-transition: all 0.5s ease; 
        -ms-transition: all 0.5s ease; 
        transition: all 0.5s ease; 
        margin: 15px 15px;
    }
    .zoomin2 img:hover { 
        width: 210px; 
        height:77px; 
    }
    .zoomin3 img { 
        height: 41px; 
        width: 235px; 
        -webkit-transition: all 0.5s ease; 
        -moz-transition: all 0.5s ease; 
        -ms-transition: all 0.5s ease; 
        transition: all 0.5s ease; 
    }
    .zoomin3 img:hover { 
        width: 245px; 
        height:50px; 
    }
    .page-header-col1-row2-col2-form-select1 { 
        float: left; 
        width: 100px;
        font-size: 18px;
    }
    #search_result .big-img-box .zoomthis img {
        height: 166px;
    }
    #search_result figure.box {
        min-height: 253px;
    }
    .bg-gray .txt-black b {
        font-weight: 900;
        font-size: 13px;
    }
    .footer-searchsec-right-btn {
        font-size: 17px !important;
    }
    .seach-page-inn .small-box .table.margin-bottom-0 a .photo {
        position: static!important;
    }
    #search_result .big-img-box .ribbon img {
        height: 90px;
    }
    #post_buy_req .modal-dialog {
        margin: 20% auto;
    }
    .search-show-box-buyleads #res {
        width: 66.6%!important;
    }
    .testimonialbg {
        min-height: auto; 
        padding-bottom: 30px;
    }
    @media(min-width:981px) and (max-width:1024px){
        #search_result figure.box { 
            min-height: 200px;
        }
        .seach-page-inn .box {
            height: 199px;
        }
        .box-2 ul li big.txt-red + a {
            color: #000;
        }
        #search_result .big-img-box .zoomthis img, 
        .seach-page-inn .big-img-box .zoomthis img {
            width:auto;
            max-height: 150px!important;
        }
        .seach-page-inn .box { 
            height: 150px;
        }
        #search_result figure.box { 
            min-height: 150px;
        }
        .box-1 .zoomthis img {
            width: 100%!important;
        }
        .box .zoomthis {
            text-align: center;
        }
        .side_compare_list {
            display: block!important;
        }
        #right-image {
            max-width: 215px !important;
        }
        .hm1.bbc.search-wrap {
            width: calc(100% - 233px) !important;
        }
        div.ryt.ser-right {
            width:220px!important;
        }
    }
    @media(min-width:769px) and (max-width:980px){
        div#cssmenu {
            width:25% !important;
        }
    }
    @media(min-width:768px) and (max-width:800px) {
        #search_result .big-img-box .zoomthis img, 
        .seach-page-inn .big-img-box .zoomthis img {
            max-height: 200px;
        }
        .bg-gray .txt-black b {
            font-size: 16px;
        }
        div.lft.ser-mid .row .box-3 .ar-box-1 {
            max-width: calc(100% - 150px);
            padding: 10px !important;
            margin-bottom: 0 !important;
        }
        div.lft.ser-mid .row .ar-box-1 + .small-box {
            width: 150px;
            padding: 0;
        }
    }
    @media(min-width:767px) and (max-width:800px){
        .footer-searchsec .footer-searchsec-left {
            width: calc(100% - 36%) !important;
        }
        .ar-box-1 .small-box table tr td img {
            height: 85px !important;
            width: 85px !important;
        }
        html #search_result .box-3 {
            width:100% !important;
            display: flex;
        }
        .box-under-twoimage > div {
            display: block;
        }
        .box-under-twoimage .padding-0 {
            width:100%;
        }
        .table.enquiry-tb .bg-gray .padding-0 big {
            white-space: nowrap;
        }
        .membership_plans .upgrader {
            width: 75% !important;
        }
    }
    @media(max-width:640px){
        div.lft.ser-mid .row .box-3 .ar-box-1 {
            max-width: calc(100% - 150px);
            padding: 10px !important;
            margin-bottom: 0 !important;
        }
        div.lft.ser-mid .row .ar-box-1 + .small-box {
            width: 150px;
            padding: 0;
        }
        html #search_result .box-3 {
            width:100%!important;
        }
    }
    @media(max-width:600px){
        #search_result .big-img-box .ribbon img {
            height: 48px;
        }
        .table.enquiry-tb.margin-bottom-0 .btn-enquiry {
            margin-top:0 !important;
        }
        .table.enquiry-tb.margin-bottom-0 a[data-enquiry] { 
            margin-left: 62px;
        }
        div.lft.ser-mid .row .box-3 .ar-box-1 {
            max-width: 235px;
        }
        .ar-mid-box table tr td a[href*="company"] img {
            width: 20px !important;
        }
        .bg-gray .txt-black b {
            font-size: 15px;
        }
    }
    @media(max-width:480px){
        .seach-page-inn .clearfix {
            display: none;
        }
        .ryt.fl.ser-right.right-section-search-buylead {
            display: none;
        }
        button.btn.btn-sm.btn-warning.border-radius-0.btn-enquiry { 
            margin-top: 20px;
        }
        .testimonialbg {
            min-height: 250px;
        }
        #search_result figure.box { 
            min-height: 150px;
        }
        .seach-page-inn .box {
            height: 150px;
        }
        div.lft.ser-mid .row .ar-box-1 + .small-box {
            padding:0;
            margin-top:20px;
        }
        .table.enquiry-tb.margin-bottom-0 a[data-enquiry] {
            margin-left: 150px;
            margin-top: 15px; 
            float: right;
        }
        .ar-box-1 .box-2 {
            width: 60% !important;
            padding-left: 15px !important; 
            padding-top: 15px !important;
        }
        #search_result .big-img-box .zoomthis img, 
        .seach-page-inn .big-img-box .zoomthis img {
            max-height: 150px;
        }
        .ar-mid-box .table.enquiry-tb tbody tr td {
            width:100%!important;
        }
    }
    @media(max-width:414px){
        .table.enquiry-tb.margin-bottom-0 a[data-enquiry] {
            margin-left: 110px;
        }
        .padding-0.small-box-td1 .wrapper-product-searchright { 
            border-right: 1px solid #ccc;
        }
        .box-under-twoimage img.photo {
            width:auto;
        }
        .wrapper-product-searchright {
            text-align: center;
        }
    }
    @media(max-width:380px){
        .table.enquiry-tb.margin-bottom-0 a[data-enquiry] {
            margin-left: 85px;
        }
    }
    #changeLocation {
        top: 50px !important;
        width: 170px !important;
        left: -15px !important;
    }
    </style>

    <div class="main-warpp">
        <!-- Top Blue Bar-->
        <?php include "includes/inner_top_bar.php"; ?>
        <!-- End of topbar // -->
        
        <div class="maincontainertop">
            <!-- page-header start -->
            <header class="page-header site-main-header">
                <div class="page2-header2-col2">
                    <div class="page2-header2-col1-row1-col3">
                        <!-- page-header-col1-row1-col3 start -->
                        <div id="google_translate_element" style="margin: 0px auto 10px;"></div>
                        <script type="text/javascript">
                        function googleTranslateElementInit() {
                            new google.translate.TranslateElement({
                                pageLanguage: 'en',
                                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                            }, 'google_translate_element');
                        }
                        </script>
                        <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
                    </div>
                    
                    <div class="page-header-col2-intro">
                        <div class="page-header-col2-intro-texts">
                            <a href="product-sel-cat.php?select=bs" class="post-product-btn post-product-btn-inner" title="Post Business Services">
                                إنشر خدماتك التجارية
                                <small>وتلقـى إستفســـارات شــراء لها</small>
                            </a>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                
                <div class="page-header-col11">
                    <div class="col-md-9 page2-header2-col1-row1">
                        <div class="page2-header2-col1-row1-col1">
                            <div class="page2-header2-col1-row1-col1_row2">
                                <div class="page-header-col1-row1-col1_row2_pic" id="cnlocation">
                                    <?php if (isset($_COOKIE['loc_id'])): 
                                        $loc_id = (int)$_COOKIE['loc_id'];
                                    ?>
                                        <span style="font-weight:700; color: darkcyan;"><?php echo htmlspecialchars(get_country_name($loc_id) ?? ''); ?></span>&nbsp;
                                        <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag($loc_id) ?? ''); ?>"
                                             alt="<?php echo htmlspecialchars(get_country_name($loc_id) ?? ''); ?>" 
                                             class="w4" align="top" height="16" width="23" 
                                             title="<?php echo htmlspecialchars(get_country_name($loc_id) ?? ''); ?>"/>
                                    <?php else: ?>
                                        <b>Global</b> &nbsp; 
                                        <img src="images/country_flag/Global$download.png" alt="Global" 
                                             style="height:25px !important;width:25px!important;" 
                                             class="w4" align="top" height="25" width="25"/>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="page-header-col1-row1-col1-row2-form">
                                    <div onmouseover="showLocMenu();" onmouseout="hideLocMenu()">
                                        <a class="un" style="border-left:none;font-size: 10px;">
                                            <span style="color: black" title="Change Country">إختار</span> بلـد التجارة
                                            &nbsp;<span class="arw"><b>&or;</b></span>
                                        </a>
                                        
                                        <div class="sub_menu" style="display:none;width: 170px !important;left: -15px !important;top: 50px !important;" id="changeLocation">
                                            <ul>
                                                <li style="width:100%;">
                                                    <?php
                                                    $activeCountries = getActiveCountryList();
                                                    $sql_cnLoc = "SELECT * FROM country WHERE cn_id IN(" . $activeCountries . ")";
                                                    $res_cnLoc = mysqli_query($con, $sql_cnLoc);
                                                    ?>
                                                    <table style="width:100%;padding:1px;">
                                                        <tr>
                                                            <td align="center">
                                                                <a title="Global" style="cursor:pointer;" onclick="unsetCountryLocation();">
                                                                    <img src="images/country_flag/Global$download.png" alt="Global" 
                                                                         style="height:25px !important;width:25px!important;" 
                                                                         class="w4" align="top" height="16" width="16"/>
                                                                </a>
                                                            </td>
                                                            <?php
                                                            $cn = 1;
                                                            while ($row_cnLoc = mysqli_fetch_object($res_cnLoc)):
                                                                if ($cn % 3 == 0):
                                                                    $cn = 0;
                                                            ?>
                                                        </tr>
                                                        <tr>
                                                            <?php endif; ?>
                                                            <td align="center">
                                                                <a title="<?php echo htmlspecialchars($row_cnLoc->cn_name); ?>" 
                                                                   style="cursor:pointer;"
                                                                   onclick="setCountryLocation(<?php echo (int)$row_cnLoc->cn_id; ?>);">
                                                                    <img src="images/country_flag/<?php echo htmlspecialchars(get_country_flag((int)$row_cnLoc->cn_id) ?? ''); ?>"
                                                                         alt="<?php echo htmlspecialchars($row_cnLoc->cn_name); ?>" 
                                                                         class="w4" align="top" height="25" width="30"/>
                                                                </a>
                                                            </td>
                                                            <?php
                                                            $cn++;
                                                            endwhile;
                                                            while ($cn <= 3):
                                                            ?>
                                                            <td>&nbsp;</td>
                                                            <?php
                                                            $cn++;
                                                            endwhile;
                                                            ?>
                                                        </tr>
                                                    </table>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="page2-header2-col1-row1-col2">
                            <?php
                            $toplogo = GettingSite_Setting('unit-logo');
                            $toplogo2show = !empty($toplogo) ? "sitelogo/" . $toplogo : "images/left-header-logo.png";
                            ?>
                            <a href="/" title="سوق مصر على الإنترنت - أول منصة الكترونية لمبيعات الجملة / التصدير / الخدمات التجارية .. لأهم 10,000 شركة ومصنع فى مصر والمنطقة العربية">
                                <img src="<?php echo htmlspecialchars($toplogo2show); ?>" alt="" style="max-width:190px; max-height:85px;"/>
                            </a>
                        </div>
                    </div>
                    
                    <div class="page2-header2-col1-row2">
                        <div class="page2-header2-col1-row2-col2">
                            <div class="toplinksbar">
                                <ul></ul>
                            </div>
                            
                            <script>
                            $(document).ready(function() {
                                // Set selected option based on current rctyp
                                let rctyp = '<?php echo isset($_GET['rctyp']) ? htmlspecialchars($_GET['rctyp']) : 'Products'; ?>';
                                $(".page-header-col1-row2-col2-form-select1 option[value='" + rctyp + "']").attr('selected', 'selected');
                                
                                // Update placeholder based on selected type
                                function updatePlaceholder() {
                                    let tabVal = $("#rctyp").val();
                                    let placeholder = '';
                                    
                                    switch(tabVal) {
                                        case 'Products':
                                            placeholder = "إبحـث عن منتجات وخدمات بالأسعار التجارية >> ";
                                            break;
                                        case 'Suppliers':
                                            placeholder = "إبحــث عن مــوردين وخدمات تجارية من المنبـع أو بإسم المنتج >>";
                                            break;
                                        case 'buy_lead':
                                            placeholder = "إبحــث عن طلبات شــراء من المنبـع >>";
                                            break;
                                        case 'tender':
                                            placeholder = "إبحـث عن مناقصات / مزايدات لأعمالك التجارية ";
                                            break;
                                    }
                                    $("#search-box11").attr("placeholder", placeholder);
                                }
                                
                                // Initial placeholder
                                updatePlaceholder();
                                
                                // Update on change
                                $('#rctyp').change(function() {
                                    updatePlaceholder();
                                });
                                
                                // Autocomplete on keyup
                                $("#search-box11").keyup(function() {
                                    let getDrpDwnVal = $("#rctyp option:selected").val();
                                    let fileName = '';
                                    
                                    switch(getDrpDwnVal) {
                                        case 'Suppliers':
                                            fileName = "readsuppliers.php";
                                            break;
                                        case 'Products':
                                            fileName = "readproducts.php";
                                            break;
                                        case 'buy_lead':
                                            fileName = "read_leads.php";
                                            break;
                                        default:
                                            fileName = "read_tenders.php";
                                    }
                                    
                                    $.ajax({
                                        type: "POST",
                                        url: fileName,
                                        data: {keyword: $(this).val()},
                                        beforeSend: function() {
                                            $("#search-box11").css("background", "#FFF url(LoaderIcon.gif) no-repeat 165px");
                                        },
                                        success: function(data) {
                                            $("#suggesstion-box").show().html(data);
                                            $("#search-box11").css("background", "#FFF");
                                        }
                                    });
                                });
                            });
                            
                            function selectCountry(val) {
                                $("#search-box11").val(val);
                                $("#suggesstion-box").hide();
                            }
                            </script>
                            
                            <div class="top_search">
                                <form autocomplete="off" name="searchForm" action="search.php" onsubmit="return validsearch()" method="GET" id="hdr_frm">
                                    <div class="topsearch_bar">
                                        <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select1">
                                            <option value="Products">مـنـتـجــات</option>
                                            <option value="Suppliers">الـمـوردون</option>
                                            <option value="buy_lead">طلبات شراء</option>
                                            <option value="tender">مـنـاقصات</option>
                                        </select>
                                    </div>
                                    <div class="topsearch_placeholder">
                                        <input type="text" id="search-box11" name="keywords" 
                                               placeholder="Source Product / Services >> Find Suppliers" 
                                               onfocus="gotFocus();" onblur="lostFocus()" 
                                               value="<?php echo isset($_GET['keywords']) ? htmlspecialchars($_GET['keywords']) : ''; ?>"  
                                               class="topsearch_placeholder_cont"/>
                                    </div>
                                    <span class="loading-text hide"><img src="/assets/img/Spinner-200px.gif" style="width: 48px;height: 48px;"></span>
                                    
                                    <div id="suggesstion-box"></div>
                                    <div class="topsearch_searchbtn">
                                        <input type="submit" id="btnSearch" value="" class="topsearch-searchbtn"/>
                                    </div>
                                </form>
                                <div class="clear"></div>
                            </div>
                            
                            <div class="page-header-col1-row2-col2-links">
                                <p><span><a href="search_adv.php" title="Advanced Search">بحـــث متقــدم</a></span></p>
                            </div>
                        </div>
                        
                        <div class="page2-header2-col1-row2-col4">
                            <a href="post-buy-req.php" target="_blank" class="footer-searchsec-right-btn head-post-buy-req-btn" title="Post Buy Requirements">
                                سجـل طلبات شـراء 
                            </a>
                        </div>
                        <div class="clear"></div>
                    </div>
                    
                    <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css"/>
                    <script type="text/javascript" src="js/jquery.autocomplete2.js"></script>
                    <script type="text/javascript">
                    $(document).ready(function() {
                        lostFocus();
                        $('#keywords').keydown(function() {
                            var type = $("#keyword_type").val();
                            $("#keywords").autocomplete("autocomplete.php", {
                                selectFirst: true,
                                extraParams: {type: type},
                                width: 407
                            }).result(function(event, data, formatted) {
                                $("input#keywords").val(data);
                            });
                        });
                    });
                    </script>
                    
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </header>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        $('#btnSearch').click(function(event) {
            $('.loading-text').removeClass('hide').addClass('show');
        });
    });
    </script>
</div>