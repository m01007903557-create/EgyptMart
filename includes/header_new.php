<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>

<?php
// تحديد مسار المجلد الحالي للمشكلة
$current_file = $_SERVER['SCRIPT_FILENAME'];
$is_in_subfolder = (strpos($current_file, '/chat/') !== false);

// إذا كان الملف في مجلد chat، قم بتعديل المسارات
if ($is_in_subfolder) {
    $base_path = $_SERVER['DOCUMENT_ROOT'];
} else {
    $base_path = '';
}
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
<link rel="stylesheet" type="text/css" href="css/m-common.css">
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
        var keywords = document.getElementById('search-box11');
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

<script>
    $(document).ready(function() {
        $('#btnSearch').click(function(event) {
            $('.loading-text').removeClass('hide').addClass('show');
        });
    });
</script>
<!-- 
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
    .site-main-header {
        background: #fff;
        border-bottom: 1px solid #d8e2ec;
        box-sizing: border-box;
        padding: 8px 12px 10px;
    }
    .site-main-header .page-header-col11 {
        align-items: center;
        display: flex;
        gap: 14px;
        max-width: 1320px;
        margin: 0 auto;
        width: 100%;
    }
    .site-main-header .page2-header2-col1-row1 {
        align-items: center;
        display: flex;
        flex: 0 0 220px;
        gap: 8px;
        width: auto !important;
    }
    .site-main-header .page2-header2-col1-row1-col1 {
        flex: 0 0 76px;
    }
    .site-main-header .page2-header2-col1-row1-col2 {
        flex: 1 1 auto;
        text-align: left;
    }
    .site-main-header .page2-header2-col1-row1-col2 img {
        height: auto;
        max-height: 62px !important;
        max-width: 138px !important;
    }
    .site-main-header .page2-header2-col1-row2 {
        align-items: center;
        display: flex;
        flex: 1 1 auto;
        gap: 14px;
        min-width: 0;
        width: auto !important;
    }
    .site-main-header .page2-header2-col1-row2-col2 {
        flex: 1 1 auto;
        min-width: 0;
    }
    .site-main-header .top_search {
        border: 2px solid #2a58a8;
        border-radius: 5px;
        box-sizing: border-box;
        display: flex;
        margin: 0 !important;
        min-height: 52px;
        position: relative;
        width: 100%;
    }
    .site-main-header #hdr_frm {
        align-items: stretch;
        display: flex;
        width: 100%;
    }
    .site-main-header .topsearch_bar {
        flex: 0 0 118px;
        width: 118px !important;
    }
    .site-main-header .page-header-col1-row2-col2-form-select1 {
        border: 0;
        border-left: 1px solid #bbc9d7;
        box-sizing: border-box;
        height: 50px;
        width: 118px !important;
    }
    .site-main-header .topsearch_placeholder {
        flex: 1 1 auto;
        min-width: 0;
    }
    .site-main-header .topsearch_placeholder_cont {
        border: 0 !important;
        box-sizing: border-box;
        height: 50px !important;
        padding: 0 12px !important;
        text-align: right;
        width: 100% !important;
    }
    .site-main-header .topsearch_searchbtn {
        flex: 0 0 58px;
        margin: 0 !important;
        width: 58px !important;
    }
    .site-main-header .topsearch-searchbtn {
        height: 50px !important;
        width: 58px !important;
    }
    .site-main-header .page-header-col1-row2-col2-links {
        margin-top: 2px;
        text-align: right;
    }
    .site-main-header .page2-header2-col1-row2-col4 {
        flex: 0 0 220px;
        width: 220px !important;
    }
    .site-main-header .head-post-buy-req-btn {
        box-sizing: border-box;
        display: flex !important;
        align-items: center;
        justify-content: center;
        height: 54px;
        margin: 0 !important;
        width: 100% !important;
    }
    .site-main-header .page2-header2-col2 {
        align-items: center;
        display: flex;
        justify-content: flex-end;
        max-width: 1320px;
        margin: 0 auto 6px;
    }
    .site-main-header .page-header-col2-intro {
        margin: 0 !important;
    }
    .site-main-header .page-header-col2-intro-texts .post-product-btn {
        margin: 0 !important;
    }
    @media (max-width: 768px) {
        .site-main-header {
            padding: 6px 8px 8px;
        }
        .site-main-header .page2-header2-col2 {
            display: none !important;
        }
        .site-main-header .page-header-col11,
        .site-main-header .page2-header2-col1-row1,
        .site-main-header .page2-header2-col1-row2 {
            display: block;
        }
        .site-main-header .page2-header2-col1-row1,
        .site-main-header .page2-header2-col1-row2-col4 {
            margin-bottom: 6px;
            width: 100% !important;
        }
        .site-main-header .page2-header2-col1-row1-col1,
        .site-main-header .page2-header2-col1-row1-col2 {
            display: inline-block;
            vertical-align: middle;
        }
        .site-main-header .page2-header2-col1-row1-col1 {
            width: 120px !important;
        }
        .site-main-header .page2-header2-col1-row1-col2 img {
            max-height: 46px !important;
            max-width: 120px !important;
        }
        .site-main-header .page2-header2-col1-row2-col4 {
            margin-top: 6px;
        }
        .site-main-header .top_search {
            min-height: 46px;
        }
        .site-main-header .topsearch_bar,
        .site-main-header .page-header-col1-row2-col2-form-select1 {
            flex-basis: 94px;
            height: 44px;
            width: 94px !important;
        }
        .site-main-header .topsearch_placeholder_cont,
        .site-main-header .topsearch-searchbtn {
            height: 44px !important;
        }
        .site-main-header .topsearch_searchbtn,
        .site-main-header .topsearch-searchbtn {
            flex-basis: 48px;
            width: 48px !important;
        }
        .site-main-header .head-post-buy-req-btn {
            height: 46px !important;
        }
    }
</style> -->

<!-- 
New Styles for header added on 28 Aug 2026 Ends
-->
<style>
    #leftsection ul.dropdown a {
        display: block;
        width: 100%;
        height: 100%;
        color: #000;
        padding: 7px; 
    }

    li[itemscope] a  {
        color: #ff0000f7;
    }
</style>
<!-- 
    New Styles for header added on 28 Aug 2026 Ends
-->

<div class="wrapper">

    <div class="main-warpp">
        <!-- Top Blue Bar-->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/inner_top_bar.php'; ?>
        <!-- <nav class="mobile-quick-strip" aria-label="Quick links">
            <a href="index.php"><i class="fa fa-home"></i><span>Home</span></a>
            <a href="dir.php#main_cat"><i class="fa fa-th-large"></i><span>Categories</span></a>
            <a href="buyleads.php"><i class="fa fa-list-alt"></i><span>RFQs</span></a>
            <a href="search.php?rctyp=Suppliers"><i class="fa fa-industry"></i><span>Suppliers</span></a>
            <a href="sale-offers.php"><i class="fa fa-tags"></i><span>Offers</span></a>
        </nav> -->

        <!-- End of topbar // -->
        
        
    </div>

</div>