<?php 
ini_set('default_socket_timeout', 300);
$totalpage = 50;
?>
<?php 
function highlight($content, $word) {
    $replace = '<span style="color: #f26a22;">' . $word . '</span>';
    $word = str_replace("+", " ", $word);
    $pattern = preg_quote($word);
    $content = preg_replace("/($pattern)/i", '<span style="color: #f26a22;">$1</span>', $content);
    return $content;
}
?>
<script src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/js/jquery.colorbox.js"></script>
<link href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/css/colorbox.css" type="text/css" rel="stylesheet">
<style>
    #mask { position:absolute; left:0; top:0; z-index:9000; background-color:#000; display:none; }
    #boxes .window { position:absolute; left:0; top:0; width:440px; height:200px; display:none; z-index:9999; padding:20px; border-radius:15px; text-align:center; }
    #boxes #dialog { width:450px; height:auto; padding:10px; background-color:#ffffff; }
    .maintext { text-align:center; text-decoration:none; }
    .modal.in .modal-dialog { transform:translate3d(0px,0px,0px); }
    .modal-lg { width:38% !important; }
    h3 { font-weight:bold !important; margin-bottom:0px !important; margin-top:0px !important; font-size:20px !important; }
    .btn-warning { background-color: #f26a22; }
    .email-close { background:#FF0000 none repeat scroll 0 0; border:2px solid #FFF; border-radius:50%; box-shadow:0 0 4px 1px rgb(0,0,0); cursor:pointer; font-size:18px; height:30px; position:absolute; right:-4px; text-align:center; top:44px; width:30px; z-index:999; color:#FFFFFF; font-weight:bold; }
    .zoomthis:hover img { -moz-transform:scale(1.2); -webkit-transform:scale(1.2); transform:scale(1.2); }
    .zoomthis { overflow:hidden; }
    .zoomthis:hover ~ .zk { display:none; }
    .zoomthis:hover ~ .ribbon { display:none; }
    @media (max-width:1300px) and (min-width:1200px) {
        .height44 td { padding-left:3px !important; padding-right:3px !important; padding-top:0px !important; padding-bottom:0 !important; position:relative; top:-8px; }
        .webcast-new-table .grid_mobile img { vertical-align:bottom; }
        .webcast-new-table .grid_mobile .txt-black { margin-left:-6px; }
    }
    .style_prevu_kit{
        border: 1px solid #999;
    background: #fff;
    margin: 5px;
    width: calc(33.333% - 10px);
    height: 450px !important;
    }
    @media (min-width: 992px) {
    .col-md-4 {
        width: 30.33333333%;
    }
}
@media (min-width: 1024px) {
    .active-grid-option .compared-box.compared-box1 {
        width: 30.3% !important;
        float: left;
        padding: 0 5px;
    }
}


.box-under-twoimage > div {
    display: inline-block;
    width: 48%;
    vertical-align: top;
}

.small-box-td1,
.small-box-td2 {
    display: inline-block !important;
    width: 48% !important;
    vertical-align: top;
}

.small-box-td1 img,
.small-box-td2 img {
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
    max-height: 80px !important;
    display: block;
}
</style>
<script type="text/javascript">
    function openInNewTab(url) { var win = window.open(url, '_blank'); }
    function createCookie(name, value, days) {
        var date = new Date();
        date.setTime(date.getTime() + (60 * 30 * 1000));
        var expires = "; expires=" + date.toGMTString();
        document.cookie = name + "=" + value + expires + "; path=/";
    }
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
    function showfavorite(userid, product_id) {
        $.ajax({ type:'POST', url:"favourite_add.php", data:{"pro_id":product_id}, success:function(response){ if(response==true){ alert('Added to favorite'); } } });
    }
    function isInArray(value, array) {
        var strarray = array.split(',');
        var isValue = false;
        for (var i = 0; i < strarray.length; i++) { if (strarray[i]==value) { isValue = true; } else { isValue = false; } }
        return isValue;
    }
    function addcompare(product_id) {
        var existingids = readCookie("productids");
        if (existingids) {
            if (isInArray(product_id, existingids)) {
                var prod = $("#div-" + product_id).next('.box-2').find(".txt-blue").children('a').text();
                alert(prod + " already added to compare list.");
            } else {
                var strarray = existingids.split(',');
                if (strarray.length > 10) { alert("You can add only 10 products to compare list."); window.location.href = "https://<?php echo $_SERVER['HTTP_HOST']; ?>/compare.php"; }
                else {
                    createCookie("productids", existingids + "," + product_id);
                    var prod = $("#div-" + product_id).next('.box-2').find(".txt-blue").children('a').text();
                    alert(prod + " added to compare list.");
                    $('.side_compare_list').show();
                    $(".comp-list").append("<h5 class='text-center'>" + prod + "</h5>");
                    return false;
                }
            }
        } else {
            createCookie("productids", existingids + "," + product_id);
            var prod = $("#div-" + product_id).next('.box-2').find(".txt-blue").children('a').text();
            alert(prod + " added to compare list.");
            $('.side_compare_list').show();
            $(".comp-list").append("<h5 class='text-center'>" + prod + "</h5>");
            return false;
        }
    }
    function showcompare(product_id) {
        var existingids = readCookie("productids");
        if (existingids == '') { createCookie("productids", product_id); window.location = "compare.php"; } else { window.location = "compare.php"; }
    }
</script>
<script>
    $(document).ready(function () {
        var id = '#dialog';
        var limit = 150;
        var idleTime = 0;
        var maskHeight = $(document).height();
        var maskWidth = $(window).width();
        var keyrcType = $("#keyrcType").val();
        function timerIncrement() {
            idleTime = idleTime + 1;
            if (idleTime > limit) {
                $('#mask').css({'width': maskWidth, 'height': maskHeight});
                $('#mask').fadeIn(500);
                $('#mask').fadeTo("slow", 0.5);
                var winH = $(window).height();
                var winW = $(window).width();
                $(id).css('top', winH / 2 - $(id).height() / 2);
                $(id).css('left', winW / 2 - $(id).width() / 2);
                $(id).fadeIn(2000);
                idleTime = 0;
                $('.email-close').click(function () { $('#mask').hide(); $('.window').hide(); limit = 150; });
            }
        }
        if (keyrcType == 'Products') { var idleInterval = setInterval(timerIncrement, 1000); }
        $(this).mousemove(function (e) { idleTime = 0; });
        $(this).keypress(function (e) { idleTime = 0; });
        $(this).click(function () { idleTime = 0; });
        $('.window .close').click(function (e) { e.preventDefault(); $('#mask').hide(); $('.window').hide(); });
        $('#mask').click(function () { $(this).hide(); $('.window').hide(); });
        $(document).on('click', '#table-input1', function () {
            $("#sideAdTable1").hide();
            $("body").click(function () { var tabvin = document.getElementById("table-input1").value; if (tabvin == '') { $("#sideAdTable1").show(); } });
        });
        $(document).on('click', '#getInstaQuote', function () { $("#sideAdTable1").hide(); });
    });
</script>
<input type="hidden" value="<?php echo htmlspecialchars($_GET['keywords'] ?? ''); ?>" id="serachWallkeyword">
<?php
$uid = isset($_SESSION['uid_indm']) ? $_SESSION['uid_indm'] : '';
$loc_id = isset($_COOKIE['loc_id']) ? (int)$_COOKIE['loc_id'] : 0;

if (isset($_COOKIE['loc_id'])) {
    $sql_pd_ck = " and ((pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='" . $loc_id . "')) or (pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='" . $loc_id . "')) or (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='" . $loc_id . "'))))";
    $sql_br_ck = " and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='" . $loc_id . "')) or (br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='" . $loc_id . "')) or (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='" . $loc_id . "'))))";
    $sql_auc_ck = " and ((auc_preferred_location='domestic' and auc_usr_id in(select distinct usr_id from user where country='" . $loc_id . "')) or (auc_preferred_location='any' and auc_usr_id in(select distinct usr_id from user where country='" . $loc_id . "')) or (auc_preferred_location='my_city' and auc_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='" . $loc_id . "'))))";
    $auctionCondition = " AND ((auc_preferred_location='domestic' AND auc_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='" . $loc_id . "')) OR (auc_preferred_location='any' AND auc_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='" . $loc_id . "')) OR (auc_preferred_location='my_city' AND auc_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='" . $loc_id . "'))))";
    $tenderCondition = " AND ((tnd_preferred_location='domestic' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='" . $loc_id . "')) OR (tnd_preferred_location='any' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='" . $loc_id . "')) OR (tnd_preferred_location='my_city' AND tnd_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='" . $loc_id . "'))))";
} else {
    $sql_pd_ck = " and ((pd_preferred_buyer_location='any') or (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country[0] ?? 243) . "'))))";
    $sql_br_ck = " and ((br_preferred_supplier_location='any') or (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country[0] ?? 243) . "'))))";
    $sql_auc_ck = " and ((auc_preferred_location='any') or (auc_preferred_location='abroad' and auc_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country[0] ?? 243) . "'))))";
    $auctionCondition = " AND ((auc_preferred_location='domestic') OR (auc_preferred_location='any') OR (auc_preferred_location='my_city'))";
    $tenderCondition = " AND ((tnd_preferred_location='domestic') OR (tnd_preferred_location='any') OR (tnd_preferred_location='my_city'))";
}



$pageno = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
$rctyp = addslashes(trim($_GET['rctyp'] ?? ''));
$raw_keywords = $_GET['keywords'] ?? '';
if (substr($raw_keywords, 0, 1) == '"') {
    $keywords = substr(substr(trim($raw_keywords), 1), 0, strlen(substr(trim($raw_keywords), 1)) - 1);
} else {
    $keywords = trim($raw_keywords);
}

// =========================================================
// Products section
// =========================================================
if ($rctyp == "Products") {
    if ($keywords == '') $keywords = 'all';

    $key = str_replace("+", " ", $raw_keywords);
    if (!empty($_SESSION['uid_indm'])) {
        $sql_key = "select * from products join product_category on product_category.pc_id=products.pd_subcat_id join business_profile on business_profile.bnsprof_uid = products.pd_uid where (pd_title like '%" . mysqli_real_escape_string($con, $key) . "%' or bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $key) . "%') and pc_status='1'";
        $query_key = mysqli_query($con, $sql_key);
        $row_key = mysqli_fetch_object($query_key);
        $key_cat_id = '';
        if (mysqli_num_rows($query_key) > 0) {
            $key_cat_id = $row_key->pc_id;
        } else {
            $sql_second_query = mysqli_query($con, "SELECT pc.* FROM product_category pc LEFT OUTER JOIN product_category spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%" . mysqli_real_escape_string($con, str_replace(["+","%20"], [" "," "], $raw_keywords)) . "%' AND pc.pc_parent_id!='0' and pc.pc_status='1'");
            $fetch_records = mysqli_fetch_object($sql_second_query);
            if (mysqli_num_rows($sql_second_query) > 0) {
                $sub_cat_id_get = $fetch_records->pc_id;
                $sql_second_query1 = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id='" . $sub_cat_id_get . "'");
                $fetch_records1 = mysqli_fetch_object($sql_second_query1);
                $key_cat_id = (mysqli_num_rows($sql_second_query1) > 0) ? $fetch_records1->pc_id : $fetch_records->pc_id;
            }
        }
        if ($key_cat_id != '') {
            $sql2 = "select * from selloffer_alert_category where sac_pc_id='" . $key_cat_id . "' AND sac_usr_id='" . $_SESSION['uid_indm'] . "'";
            $res2 = mysqli_query($con, $sql2);
            if ($res2->num_rows == 0) {
                mysqli_query($con, "insert into selloffer_alert_category set sac_usr_id='" . $_SESSION['uid_indm'] . "', sac_pc_id='" . $key_cat_id . "', sac_updated_date=now()");
            }
        }
    }

    $Col = 'products.*, business_profile.bnsprof_state, measurement_unit.*';


    // if (isset($_POST['state_id']) && is_array($_POST['state_id']) && count($_POST['state_id']) > 1) {
    //     $stateid = '';
    //     foreach ($_POST['state_id'] as $k => $v) { $stateid .= $v . ','; }
    //     $stateid = rtrim($stateid, ',');
    // } else {
    //     $stateid = isset($_POST['state_id'][0]) ? $_POST['state_id'][0] : '';
    // }


    $stateid = '';

if (!empty($_POST['state_id']) && is_array($_POST['state_id'])) {
    $stateid = implode(',', array_map('intval', $_POST['state_id']));
}


    $keywords_string = generateProdSearchString($keywords);
    $keywords_string_pro_sup = generateProdSearchString_pro_sup($keywords);

    if (isset($_GET['exmatch'])) {
        $sqltk = "select " . $Col . " from products,measurement_unit,business_profile where bnsprof_uid=pd_uid and ((pd_title LIKE '%" . mysqli_real_escape_string($con, $keywords) . "%') or (bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $keywords) . "%')) " . $sql_pd_ck . " and pd_status='1' GROUP BY pd_id order by pd_title asc limit 0,500";
    } else {
        if (isset($_POST['state_id'])) {
            $sqltk = "select " . $Col . " from products,measurement_unit,business_profile where bnsprof_uid=pd_uid and ((pd_title LIKE " . $keywords_string . ") or (bnsprof_compname LIKE " . $keywords_string_pro_sup . ")) " . $sql_pd_ck . " and pd_status='1' and bnsprof_state in (" . $stateid . ") GROUP BY pd_id order by pd_title asc limit 0,500";
        } else {
            $sqltk = "select " . $Col . " from measurement_unit,products,business_profile where bnsprof_uid=pd_uid and ((pd_title LIKE " . $keywords_string . ") or (bnsprof_compname LIKE " . $keywords_string_pro_sup . ")) " . $sql_pd_ck . " and pd_status='1' GROUP BY pd_id order by pd_title asc limit 0,500";
        }
    }

    $restk = mysqli_query($con, $sqltk);
    $totitems = mysqli_num_rows($restk);
    $limits = 50;
    $total_pages = ceil($totitems / $limits);
    $start_limit = $limits * ($pageno - 1);

    $state_condition = '';
if (!empty($stateid)) {
    $state_condition = " and bnsprof_state in (" . $stateid . ")";
}

    if (isset($_GET['exmatch'])) {
        $sqlk = "select " . $Col . " from products,measurement_unit,business_profile where bnsprof_uid=pd_uid and (pd_title LIKE '%" . mysqli_real_escape_string($con, $keywords) . "%' or bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $keywords) . "%') " . $sql_pd_ck . " and pd_status='1' " . $state_condition . "  GROUP BY pd_id order by pd_title asc limit " . $start_limit . "," . $limits;
    } else {
        $keywords_string = generateProdSearchString($keywords);
        $sqlk = "select " . $Col . " from products,measurement_unit,business_profile where bnsprof_uid=pd_uid and (pd_title LIKE " . $keywords_string . " or bnsprof_compname LIKE " . $keywords_string . ") " . $sql_pd_ck . " and pd_status='1' " . $state_condition . " GROUP BY pd_id order by pd_title asc limit " . $start_limit . "," . $limits;
    }
    $resk = mysqli_query($con, $sqlk);

// =========================================================
// Suppliers section
// =========================================================
} else if ($rctyp == "Suppliers") {
    $keywords_string = generateSupplierSearchString($keywords);
    $supptotalpage = 50;
    $suppstartpage = 0;
    if (($_GET['page'] ?? 1) > 1) {
        $supplimit = (($_GET['page'] ?? 1) - 1) * $supptotalpage;
        $suppsetLimit = " LIMIT " . $supplimit . "," . $supptotalpage;
    } else {
        $suppsetLimit = " LIMIT 0," . $supptotalpage;
    }

    $loc_condition = isset($_COOKIE['loc_id']) ? "AND ((products.pd_preferred_buyer_location = 'domestic' AND user.country = '" . $loc_id . "') OR (products.pd_preferred_buyer_location = 'any' AND user.country = '" . $loc_id . "') OR (products.pd_preferred_buyer_location = 'my_city' AND user.country = '" . $loc_id . "'))" : "AND ((products.pd_preferred_buyer_location = 'domestic') OR (products.pd_preferred_buyer_location = 'any') OR (products.pd_preferred_buyer_location = 'my_city'))";

    $sqltk = "SELECT * FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id = business_profile.bnsprof_id WHERE (bnsprof_compname LIKE '%" . mysqli_real_escape_string($con, $keywords) . "%') " . $loc_condition . " AND pm.expiry_date > " . time() . " and pd_status='1' GROUP BY products.pd_id ORDER BY business_profile.bnsprof_compname DESC";

    $restk = mysqli_query($con, $sqltk);
    $totitems = mysqli_num_rows($restk);
    $limits = 6;
    $total_pages = ceil($totitems / $limits);
    $start_limit = $limits * ($pageno - 1);

    $sqlk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('" . mysqli_real_escape_string($con, $keywords) . "' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id = business_profile.bnsprof_id WHERE (business_profile.bnsprof_compname LIKE " . $keywords_string . ") " . $loc_condition . " AND pm.expiry_date > " . time() . " and pd_status='1' GROUP BY products.pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC" . $suppsetLimit;
    $resk = mysqli_query($con, $sqlk);

// =========================================================
// Buy Lead section
// =========================================================
} else if ($rctyp == "buy_lead") {
    if (!empty($raw_keywords)) {
        $sql_key = "select * from buy_requirement join product_category on product_category.pc_id=buy_requirement.br_pc_id where (br_pd_name like '%" . mysqli_real_escape_string($con, str_replace("+", " ", $raw_keywords)) . "%' OR pc_name like '%" . mysqli_real_escape_string($con, str_replace("+", " ", $raw_keywords)) . "%') and pc_status='1' and pc_parent_id!='0'";
        $query_key = mysqli_query($con, $sql_key);
        $row_key = mysqli_fetch_object($query_key);
        $key_cat_id = (mysqli_num_rows($query_key) > 0) ? $row_key->pc_id : '';
        if ($key_cat_id != '' && !empty($uid)) {
            $r = mysqli_query($con, "SELECT * FROM buylead_alert_category WHERE bac_pc_id=" . $key_cat_id . " AND bac_usr_id=" . $uid);
            if (mysqli_num_rows($r) == 0) {
                mysqli_query($con, "insert into buylead_alert_category SET bac_usr_id=" . $uid . ", bac_pc_id=" . $key_cat_id . ", bac_updated_date=now()");
            }
        }
    }

    $sql_extra = "";
    if (isset($_GET['adv_quantity']) && $_GET['adv_quantity'] != '' && $_GET['adv_quantity'] != '0' && isset($_GET['adv_qty_list']) && $_GET['adv_qty_list'] != '') {
        $sql_extra = " and br_estimate_qty='" . trim($_GET['adv_quantity']) . "' and br_estimate_qty_unit='" . trim($_GET['adv_qty_list']) . "'";
    }

    $keywords_string = generateBuyleadSearchString($keywords);
    $sqltk = "select * from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id and (" . $keywords_string . ") and br_approval_status = '1' and br_display_status = '1' " . $sql_br_ck . " " . $sql_extra . " order by br_pd_name asc";
    $restk = mysqli_query($con, $sqltk);
    $totitems = mysqli_num_rows($restk);
    $limits = 6;
    $total_pages = ceil($totitems / $limits);
    $start_limit = $limits * ($pageno - 1);

    $sqlk = "select * from buy_requirement br JOIN measurement_unit mu ON br.br_estimate_qty_unit=mu.mu_id JOIN user u ON u.usr_id = br.br_u_id LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id LEFT JOIN country c ON c.cn_id = u.country LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id WHERE br_pd_name LIKE '%" . mysqli_real_escape_string($con, $keywords) . "%' and br_display_status = '1' and br_status='1' order by br_pd_name asc";
    $resk = mysqli_query($con, $sqlk);

// =========================================================
// Tender section
// =========================================================
} else if ($rctyp == "tender") {
    $tender_keywords_string = generateTenderSearchString($keywords);
    $auction_keywords_string = generateAuctionSearchString($keywords);

    $tendsqltk = "SELECT * FROM tender,product_category,user,business_profile WHERE tnd_pc_id=pc_id AND tnd_usr_id=usr_id AND usr_id=bnsprof_uid AND (" . $tender_keywords_string . ") AND tnd_approval_status='1' AND TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) and tnd_due_date>='" . date('Y-m-d') . "' AND tnd_approval_status = '1'" . $tenderCondition;
    $aucsqltk = "SELECT * FROM auction,product_category,user,business_profile WHERE auc_pc_id=pc_id AND auc_usr_id=usr_id AND usr_id=bnsprof_uid AND (" . $auction_keywords_string . ") and auc_due_date>='" . date('Y-m-d') . "' AND auc_approval_status = '1' AND TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) AND auc_approval_status = '1'" . $auctionCondition;

    $tend_restk = mysqli_query($con, $tendsqltk);
    $auction_restk = mysqli_query($con, $aucsqltk);
    $totitems = mysqli_num_rows($tend_restk);
    $limits = 6;
    $total_pages = ceil($totitems / $limits);
    $start_limit = $limits * ($pageno - 1);

    $tendsqlk = "SELECT * FROM tender,product_category,user,business_profile WHERE tnd_pc_id=pc_id AND tnd_usr_id=usr_id AND usr_id=bnsprof_uid AND (" . $tender_keywords_string . ") and tnd_due_date>='" . date('Y-m-d') . "' AND tnd_approval_status = '1'" . $tenderCondition . " ORDER BY tnd_heading ASC";
    $aucsqlk = "SELECT * FROM auction,product_category,user,business_profile WHERE auc_pc_id=pc_id AND auc_usr_id=usr_id AND usr_id=bnsprof_uid AND (" . $auction_keywords_string . ") and auc_due_date>='" . date('Y-m-d') . "' AND auc_approval_status = '1'" . $auctionCondition . " ORDER BY auc_id DESC";

    $tender_resk = mysqli_query($con, $tendsqlk);
    $auction_resk = mysqli_query($con, $aucsqlk);

// =========================================================
// Auction section
// =========================================================
} else if ($rctyp == "auction") {
    $keywords_string = generateAuctionSearchString($keywords);
    $sql_extra = "";
    $sqltk = "select * from auction,product_category,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and (" . $keywords_string . ") and auc_approval_status = '1' and TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) " . $sql_auc_ck . " order by auc_id desc";
    $restk = mysqli_query($con, $sqltk);
    $totitems = mysqli_num_rows($restk);
    $limits = 6;
    $total_pages = ceil($totitems / $limits);
    $start_limit = $limits * ($pageno - 1);
    $sqlk = "select * from auction,product_category,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and (" . $keywords_string . ") and auc_approval_status = '1' and TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) " . $sql_auc_ck . " " . $sql_extra . " order by auc_id desc limit " . $start_limit . "," . $limits;
    $resk = mysqli_query($con, $sqlk);
}
?>

<?php
// =========================================================
// DISPLAY: Non-Products (Suppliers, Buy Leads, Tenders, Auctions)
// =========================================================
if (($rctyp != "Products") && (isset($_GET['rctyp']))) {
    ?>
    <div class="wl-list" id="m">
        <?php
        $resk_count = ($rctyp == 'tender') ? mysqli_num_rows($tender_resk) : (isset($resk) ? mysqli_num_rows($resk) : 0);
        if ($resk_count > 0) { ?>
            <p class="flt_wd" style="float:right; color:#666666; font-family:Tahoma; font-size:13px; padding:0 0 3px 3px;">
                <?php
                if ($rctyp == 'buy_lead') echo $totitems . " Buy Leads";
                else if ($rctyp == 'tender') echo $totitems . " Tenders";
                else if ($rctyp == 'auction') echo $totitems . " Auctions";
                else echo $totitems . " Suppliers";
                ?> available
            </p>
        <?php } ?>

        <?php
        // ---- Suppliers ----
        if ($rctyp == "Suppliers") {
            $suppTotalRow = mysqli_num_rows($resk);
            $suppRowCount = 1;
            if (mysqli_num_rows($resk) > 0) {
                while ($rowk = mysqli_fetch_object($resk)) {
                    $fevrow_icon = 0;
                   $bp_sql = "SELECT business_profile.*, 
                
               city.ct_name, 
               city.ct_state,
               country.cn_name,
               country.cn_flag,
               country.cn_code,
               country.cn_ph,
               states.state_name,
               user.country as user_country_id,
               user.country as country
           FROM business_profile 
           LEFT JOIN city ON city.ct_id = business_profile.bnsprof_city 
           LEFT JOIN user ON user.usr_id = business_profile.bnsprof_uid
           LEFT JOIN country ON country.cn_id = user.country
           LEFT JOIN states ON states.state_id = business_profile.bnsprof_state
           WHERE business_profile.bnsprof_uid = " . (int)$row['pd_uid'] . " 
           LIMIT 1";
$bp_result = mysqli_query($con, $bp_sql);
$data = ($bp_result && mysqli_num_rows($bp_result) > 0) ? mysqli_fetch_assoc($bp_result) : null;
   
if (!isset($userArrayRow_Type) || empty($userArrayRow_Type)) {
    $userArrayRow_Type = [];
    $bt_result = mysqli_query($con, "SELECT bsntyp_id, bsntyp_title FROM business_type WHERE bsntyp_status = '1'");
    if ($bt_result) {
        while ($bt_row = mysqli_fetch_assoc($bt_result)) {
            $userArrayRow_Type[$bt_row['bsntyp_id']] = $bt_row['bsntyp_title'];
        }
    }
}
                    if ($data) {
                        $sql_icon = "select smembership_plan.mst_icon as sponsericon, plan_member_id.*, smembership_icon_plan.mst_icon as producticon, smembership_icon_plan.mst_name as pplan from smembership_plan,plan_member_id,smembership_icon_plan where smembership_icon_plan.mp_id=plan_member_id.p_id and smembership_plan.mp_id=plan_member_id.p_id and plan_member_id.b_id=" . $rowk->bnsprof_id;
                        $get_icon = mysqli_query($con, $sql_icon);
                        if (mysqli_num_rows($get_icon)) { $fevrow_icon = mysqli_fetch_array($get_icon, MYSQLI_ASSOC); }
                        $get_icon2 = mysqli_query($con, "select icon_id, p_id from plan_member_id where b_id=" . $data['bnsprof_id']);
                        $icon2 = mysqli_fetch_array($get_icon2);
                        $get_icon1 = mysqli_query($con, "select * from smembership_icon_plan where mp_id=" . ($icon2['icon_id'] ?? 0));
                        $icon1 = mysqli_fetch_array($get_icon1);
                        $get_icon3 = mysqli_query($con, "select * from smembership_plan where mp_id=" . ($icon2['p_id'] ?? 0));
                    }
                    $get_unit = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_id='" . $rowk->pd_unit . "'");
                    $row_unit = mysqli_num_rows($get_unit) ? mysqli_fetch_array($get_unit, MYSQLI_ASSOC) : [];
                    $rand = rand(1000, 9999);
                    $pimg1 = explode(',', $rowk->pd_image);
                    $zoom_image_val = '/upload/myproduct/' . $pimg1[0];
                    ?>
                    <div class="row ar-mid-box only-sup">
                        <div class="col-lg-12 ar-box-1 margin-top-10">
                            <div class="row">
                                <div class="col-xs-6 col-lg-3 big-img-box box-1">
                                    <header>
                                        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
                                            <a href="javascript:void(0)" class="product_fav_btn" onclick="return showfavorite(<?php echo $_SESSION['uid_indm']; ?>,<?php echo $rowk->pd_id; ?>)"><i class="fa fa-star star" style="color:<?php echo (isset($myfev) && in_array($rowk->pd_id, $myfev)) ? '#E48F23' : '#808080'; ?>"></i> Favorite</a>
                                        <?php } else { ?>
                                            <a href="sign-in.php" class="product_fav_btn"><i class="fa fa-star star"></i> Favorite</a>
                                        <?php } ?>
                                        <i class="fa fa-plus star"></i>
                                        <a href="javascript:void(0)" class="ar-star product_compare" onclick="return showcompare(<?php echo $rowk->pd_id; ?>)" data-prod_id="<?php echo $rowk->pd_id; ?>"> Compare</a>
                                    </header>
                                    <figure class="box">
                                        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
                                            <a href="javascript:void(0)" class="product_fav_btn" onclick="return showfavorite(<?php echo $_SESSION['uid_indm']; ?>,<?php echo $rowk->pd_id; ?>)"><i class="fa fa-star star"></i></a>
                                        <?php } else { ?>
                                            <a href="sign-in.php" class="product_fav_btn"><i class="fa fa-star star"></i></a>
                                        <?php } ?>
                                        <div class="zoomthis">
                                            <?php if ($fevrow_icon) { ?>
                                                <div class="ribbon"><img src="./admin/images/<?php echo $fevrow_icon['sponsericon']; ?>"/></div>
                                            <?php } elseif (isset($get_icon3) && $get_icon3 && mysqli_num_rows($get_icon3) > 0) {
                                                $fevrow_icon3 = mysqli_fetch_array($get_icon3, MYSQLI_ASSOC); ?>
                                                <div class="ribbon"><img src="./admin/images/<?php echo $fevrow_icon3['mst_icon']; ?>"/></div>
                                            <?php } ?>
                                            <?php echo $pimg1[0] != '' ? "<img src='/upload/myproduct/" . $pimg1[0] . "'>" : "<img src='/images/noimage.jpg'>"; ?>
                                        </div>
                                        <?php if (!empty($rowk->pd_imagelogo)) {
                                            $limg1 = explode(',', $rowk->pd_imagelogo);
                                            $zoom_image_val = '/upload/myproduct/' . $limg1[0]; ?>
                                            <div class="zk" style="border:1px solid #267abf;height:auto;width:100px;position:absolute;bottom:1px;left:1px;">
                                                <img style="width:auto;height:50px;max-width:100%;" src="/upload/myproduct/<?php echo $limg1[0]; ?>"/>
                                            </div>
                                        <?php } ?>
                                    </figure>
                                    <center><a onclick="zoom_image(this)" data-img="<?php echo $zoom_image_val; ?>" style="padding:10px;"><i class="fa fa-search-plus"></i> Zoom</a></center>
                                </div>

                                <div class="col-xs-6 col-lg-5 box-2" style="width:100%">
                                    <ul>
                                        <li class="margin-bottom-10">
                                            <h4 class="txt-blue">
                                                <a class="txt-blue" target="_blank" href="company/product-details.php?token=<?php echo rand(1000,9999).md5($rowk->pd_id); ?>&c=<?php echo rand(1000,9999).md5($rowk->bnsprof_id); ?>">
                                                    <?php echo ucwords($rowk->pd_title); ?>
                                                </a>
                                            </h4>
                                        </li>
                                        <li><?php echo htmlentities(substr($rowk->pd_desc, 0, 132)); ?></li>
                                        <li class="text-right">
                                            <a href="company/products.php?c=<?php echo rand(1000,9999).md5($rowk->bnsprof_id); ?>&sc=<?php echo rand(10000,99999).$rowk->pd_subcat_id; ?>#<?php echo $rowk->pd_id; ?>">+ More</a>
                                        </li>
                                        <li>Min Order &nbsp;<big class="txt-bold txt-red"><?php echo $rowk->pd_min_order_qty; ?></big>&nbsp; <?php echo $row_unit['mu_name'] ?? ''; ?></li>
                                        <?php
                                        $symbol = '$';
                                        $style_none = ((int)$rowk->pd_fob_price == 0) ? 'hide' : 'show';
                                        ?>
                                        <li class="<?php echo $style_none; ?>">Price &nbsp;
                                            <big class="txt-bold txt-red"><a style="color:#d22027" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/product-add.php"><?php echo $symbol . $rowk->pd_fob_price . '~' . $symbol . $rowk->pd_fob_price2; ?></a></big>
                                            <?php if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') { ?>
                                                <a style="float:right;" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sign-in.php"><button type="button" class="btn border-radius-0 btn-enquiry" style="font-weight:bold;">(Get Latest Price)</button></a>
                                            <?php } else { ?>
                                                <a class="ajax" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/quotationRequest_supplier.php?id=<?php echo $rand.md5($rowk->bnsprof_id); ?>&pid=<?php echo $rowk->pd_id; ?>&keywords=<?php echo urlencode($raw_keywords); ?>&search=1">
                                                    <button type="button" class="btn border-radius-0 btn-enquiry" style="font-weight:bold;">(Get Latest Price)</button>
                                                </a>
                                            <?php } ?>
                                        </li>
                                        <li class="margin-top-5">
                                            <table class="table"><tr>
                                                <td style="padding-left:0px;"><a href="/company/index.php?c=<?php echo rand(1000,9999).md5($rowk->bnsprof_id); ?>" class="txt-blue txt-bold"><img src="images/users.png" width="25px"/> About Us</a></td>
                                                <td><a href="/company/products.php?c=<?php echo rand(1000,9999).md5($rowk->bnsprof_id); ?>&flaag=whsuccess" class="txt-blue txt-bold"><img src="images/icon.png" width="20px"/> View Products</a></td>
                                                <td><a onclick="open_chat()"><i class="fa fa-comments"></i></a></td>
                                            </tr></table>
                                        </li>
                                       
                                       
                                       
                                        <li>
    <table class="table enquiry-tb margin-bottom-0">
        <tr class="bg-gray">
            <?php
            $is_logged_in = isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '';
            $product_id = $rowk['pd_id'] ?? 0;
            $product_title = $rowk['pd_title'] ?? 'Product';
            ?>
            <td class="padding-0 col-sm-6" style="vertical-align:middle;">
                <?php if ($is_logged_in) { ?>
                    <big>&nbsp;
                        <a href="javascript:void(0)" onclick="openWaRfq(<?php echo $product_id; ?>, '<?php echo addslashes($product_title); ?>')" style="color:#25D366; text-decoration:none; font-weight:bold;">
                            <img src="images/whatsapp-icon.png" width="25px" style="vertical-align:middle;"> 
                            طلب سعر واتساب
                        </a>
                    </big>
                <?php } else { ?>
                    <big>&nbsp;
                        <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sign-in.php#loginform" style="color:#25D366; text-decoration:none; font-weight:bold;">
                            <img src="images/whatsapp-icon.png" width="25px" style="vertical-align:middle;"> 
                            سجل دخول لطلب السعر
                        </a>
                    </big>
                <?php } ?>
            </td>
            <td class="text-right padding-0 col-sm-6">
                <!-- Keep your existing Send Enquiry button here -->
                <?php if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') { ?>
                    <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sign-in.php"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                <?php } else { ?>
                    <a class="ajax" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/quotationRequest_supplier.php?id=<?php echo $rand.md5($rowk->bnsprof_id); ?>&pid=<?php echo $rowk->pd_id; ?>&keywords=<?php echo urlencode($raw_keywords); ?>&search=1">
                        <button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button>
                    </a>
                <?php } ?>
            </td>
        </tr>
    </table>
</li>
                                
                                    </ul>
                                </div>

                                <div class="col-lg-4 box-3">
                                    <div class="ar-box-1 ar-box padding-5 margin-bottom-5 bg-gray" style="overflow-x:hidden;">
                                        <header class="sub-box">
                                            <?php if ($fevrow_icon) { ?><a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/membership_plans.php"><img src="./admin/images/<?php echo $fevrow_icon['producticon']; ?>" width="25px" height="25px"/></a><?php }
                                            elseif (isset($get_icon1) && $get_icon1 && mysqli_num_rows($get_icon1) > 0) { ?><a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/membership_plans.php"><img src="./admin/images/<?php echo $icon1['mst_icon']; ?>" width="25px" height="25px"/></a><?php } ?>
                                            <b class="txt-dark-gray">
                                                <a href="/company/profile.php?c=<?php echo rand(1000,9999).md5($rowk->bnsprof_id); ?>" class="titleLim" target="_blank"><?php echo ucfirst(substr($rowk->bnsprof_compname ?? '', 0, 20)); ?>...</a>
                                            </b>
                                        </header>
                                        <?php
                                        $countryId = $rowk->cn_id ?? 0;
                                        $getCountryName = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM country where cn_id='" . (int)$countryId . "'"));
                                        $getStateName = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM states where state_id='" . (int)($rowk->ct_state ?? 0) . "'"));
                                        ?>
                                        <img src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/images/country_flag/<?php echo $getCountryName['cn_flag'] ?? ''; ?>" style="width:21.6px;height:21.6px;"/>
                                        <b class="txt-bold" style="color:#302670;margin-left:10px;"><?php
                                        $address = '';
                                        if (!empty($getCountryName['cn_name'])) $address .= $getCountryName['cn_name'] . '-';
                                        if (!empty($getStateName['state_name'])) $address .= $getStateName['state_name'] . '-';
                                        if (!empty($rowk->ct_name)) $address .= $rowk->ct_name;
                                        echo $address ?: 'Not available';
                                        ?></b>
                                        <table class="table margin-top-5">
                                            <tr>
                                                <td class="txt-light-gray padding-0">Business Type:</td>
                                                <td class="padding-0 txt-bold"><?php
                                                $bnsprof_businesstype = $data['bnsprof_businesstype'] ?? '';
                                                $dataC = explode(",", $bnsprof_businesstype);
                                                if ($bnsprof_businesstype != '') {
                                                    $bus_type = ''; $i = 1;
                                                    foreach ($dataC as $r) {
                                                        if ($i <= 2) { $bus_type .= ($userArrayRow_Type[$r] ?? ''); if ($i < count($dataC)) $bus_type .= ", "; $i++; }
                                                    }
                                                    echo $bus_type . '...';
                                                } else { echo 'Not available'; }
                                                ?></td>
                                            </tr>
                                            <tr>
                                                <td class="txt-light-gray padding-0">Member Since:</td>
                                                <td class="padding-0 txt-bold"><?php echo date("Y", strtotime($rowk->bnsprof_creation_date ?? '')); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="txt-light-gray" colspan="2">
                                                    <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                                        <a href="<?php echo $rowk->bnsprof_website_alt ?? ''; ?>"><?php echo $rowk->bnsprof_website_alt ?? ''; ?></a>
                                                    <?php } else { ?>
                                                        <a href="/sign-in.php#loginform">Show Website</a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <?php
                    if ($suppRowCount == $suppTotalRow && $totitems > $totalpage) {
                        $pages = (($_GET['page'] ?? 1) >= 1) ? (($_GET['page'] ?? 1) + 1) : 2;
                        echo '<div class="col-lg-12 text-center" style="padding:30px;"><a href="https://' . $_SERVER['HTTP_HOST'] . '/search.php?rctyp=' . urlencode($rctyp) . '&keywords=' . urlencode($raw_keywords) . '&page=' . $pages . '"><button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px;font-weight:bolder;">Display More Results</button></a></div>';
                    }
                    $suppRowCount++;
                }
            } else { ?>
                <p class="error-cty bo">Sorry, your search for <span style="font-weight:bold;color:#C30000;"><?php echo htmlspecialchars($keywords); ?></span> did not match any Supplier.</p>
            <?php }

        // ---- Buy Leads ----
        } elseif ($rctyp == "buy_lead") {
            if (mysqli_num_rows($resk) > 0) {
                while ($rowk = mysqli_fetch_object($resk)) { ?>
                    <div class="m2 n-4 p_34">
                        <div class="a1" style="width:100%;padding-left:8px;padding-bottom:20px;">
                            <div>
                                <p class="as g5 w2 z1 p4">Updated: <?php echo date('d M, Y', strtotime($rowk->br_updated_date ?? '')); ?></p>
                                <a href="buyleads-details.php?id=<?php echo rand(1000,9999).md5($rowk->br_id); ?>" class="fs bo clst"><font size="5px"><?php echo htmlspecialchars($rowk->br_pd_name); ?></font></a>
                                <?php if ($rowk->br_approval_status == '1') { ?><span class="vlogoBN"><span class="vlogo g9 bo d1">Verified</span></span><?php } ?>
                            </div>
                            <?php $cleanRequirement = strip_tags(preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $rowk->br_requirement)); ?>
                            <p class="l1 p_33 pt1 wb"><?php echo htmlspecialchars(substr($cleanRequirement, 0, 100)); ?><?php if (strlen($cleanRequirement) > 100) { ?><a class="g9" href="buyleads-details.php?id=<?php echo rand(1000,9999).md5($rowk->br_id); ?>">more...</a><?php } ?></p>
                            <p class="g9 k7"><b class="x1">Location:</b> <?php echo get_city_name((int)user_info($rowk->br_u_id, 'bnsprof_city')); ?></p>
                            <div class="rn2 m2 z1 vu bo w1 d1 g9" style="background:url(images/c_button.png) no-repeat;height:30px;position:relative;top:-30px;" onClick="javascript:location.href='buyleads-details.php?id=<?php echo rand(1000,9999).md5($rowk->br_id); ?>';">&nbsp;Contact Now</div>
                        </div>
                        <p class="m2" style="background:#CCC;"><img alt="Buy Enquiry" src="images/zero.gif"></p>
                    </div>
                <?php }
            } else { ?>
                <p>Sorry, your search for <b><?php echo htmlspecialchars($keywords); ?></b> did not match any Buy Leads.</p>
            <?php }

        // ---- Tenders ----
        } elseif ($rctyp == "tender") {
            if (mysqli_num_rows($tender_resk) > 0) {
                while ($tendRowk = mysqli_fetch_object($tender_resk)) { ?>
                    <div class="m2 n-4 p_34">
                        <div class="a1" style="width:100%;padding-left:8px;padding-bottom:20px;">
                            <div>
                                <p class="as g5 w2 z1 p4">Updated: <?php echo date('d M, Y', strtotime($tendRowk->tnd_updated_date ?? '')); ?></p>
                                <a href="tender-details.php?id=<?php echo rand(1000,9999).md5($tendRowk->tnd_id); ?>" class="fs bo clst"><font size="5px"><?php echo htmlspecialchars($tendRowk->tnd_heading); ?></font></a>
                            </div>
                            <p class="l1 p_33 pt1 wb"><?php echo substr($tendRowk->tnd_details, 0, 100); ?><?php if (strlen($tendRowk->tnd_details) > 100) { ?><a class="g9" href="tender-details.php?id=<?php echo rand(1000,9999).md5($tendRowk->tnd_id); ?>">more...</a><?php } ?></p>
                            <p class="g9 k7"><b class="x1">Location:</b> <?php echo get_city_name((int)user_info($tendRowk->tnd_usr_id, 'bnsprof_city')); ?></p>
                            <div class="rn2 m2 z1 vu bo w1 d1 g9" style="background:url(images/c_button.png) no-repeat;height:30px;position:relative;top:-30px;" onClick="openInNewTab('tender-details.php?id=<?php echo rand(1000,9999).md5($tendRowk->tnd_id); ?>');">
                                <button class="btn btn-md btn-warning border-radius-0 btn-enquiry" type="button" style="height:33px;margin-left:-17px;margin-top:-9px;width:110px;font-weight:bold;">Contact Now</button>
                            </div>
                        </div>
                        <p class="m2" style="background:#CCC;"><img alt="Tender" src="images/zero.gif"></p>
                    </div>
                <?php } }
            if (mysqli_num_rows($auction_resk) > 0) {
                while ($auctionRowk = mysqli_fetch_object($auction_resk)) { ?>
                    <div class="m2 n-4 p_34">
                        <div class="a1" style="width:100%;padding-left:8px;padding-bottom:20px;">
                            <div>
                                <p class="as g5 w2 z1 p4">Updated: <?php echo date('d M, Y', strtotime($auctionRowk->auc_updated_date ?? '')); ?></p>
                                <a href="auction-details.php?id=<?php echo rand(1000,9999).md5($auctionRowk->auc_id); ?>" class="fs bo clst"><font size="5px"><?php echo htmlspecialchars($auctionRowk->auc_heading); ?></font></a>
                            </div>
                            <p class="l1 p_33 pt1 wb"><?php echo substr($auctionRowk->auc_details, 0, 100); ?><?php if (strlen($auctionRowk->auc_details) > 100) { ?><a class="g9" href="auction-details.php?id=<?php echo rand(1000,9999).md5($auctionRowk->auc_id); ?>">more...</a><?php } ?></p>
                            <p class="g9 k7"><b class="x1">Location:</b> <?php echo get_city_name((int)user_info($auctionRowk->auc_usr_id, 'bnsprof_city')); ?></p>
                            <div class="rn2 m2 z1 vu bo w1 d1 g9" style="background:url(images/c_button.png) no-repeat;height:30px;position:relative;top:-30px;" onClick="openInNewTab('auction-details.php?id=<?php echo rand(1000,9999).md5($auctionRowk->auc_id); ?>');">
                                <button class="btn btn-md btn-warning border-radius-0 btn-enquiry" type="button" style="height:33px;margin-left:-17px;margin-top:-9px;width:110px;font-weight:bold;">Contact Now</button>
                            </div>
                        </div>
                        <p class="m2" style="background:#CCC;"><img alt="Auction" src="images/zero.gif"></p>
                    </div>
                <?php } }
            if (mysqli_num_rows($tender_resk) == 0 && mysqli_num_rows($auction_resk) == 0) { ?>
                <p>Sorry, your search for <b><?php echo htmlspecialchars($keywords); ?></b> did not match any Tender or Auction.</p>
            <?php }

        // ---- Auctions ----
        } elseif ($rctyp == "auction") {
            if (mysqli_num_rows($resk) > 0) {
                while ($rowk = mysqli_fetch_object($resk)) { ?>
                    <div class="m2 n-4 p_34">
                        <div class="a1" style="width:100%;padding-left:8px;padding-bottom:20px;">
                            <div>
                                <p class="as g5 w2 z1 p4">Updated: <?php echo date('d M, Y', strtotime($rowk->auc_updated_date ?? '')); ?></p>
                                <a href="auction-details.php?id=<?php echo rand(1000,9999).md5($rowk->auc_id); ?>" class="fs bo clst"><font size="5px"><?php echo htmlspecialchars($rowk->auc_heading); ?></font></a>
                            </div>
                            <p class="l1 p_33 pt1 wb"><?php echo substr($rowk->auc_details, 0, 100); ?><?php if (strlen($rowk->auc_details) > 100) { ?><a class="g9" href="auction-details.php?id=<?php echo rand(1000,9999).md5($rowk->auc_id); ?>">more...</a><?php } ?></p>
                            <p class="g9 k7"><b class="x1">Location:</b> <?php echo get_city_name(user_info((int) ($rowk->auc_usr_id ?? 0), 'bnsprof_city')); ?></p>
                            <div class="rn2 m2 z1 vu bo w1 d1 g9" style="background:url(images/c_button.png) no-repeat;height:30px;position:relative;top:-30px;" onClick="javascript:location.href='auction-details.php?id=<?php echo rand(1000,9999).md5($rowk->auc_id); ?>';">&nbsp;Contact Now</div>
                        </div>
                        <p class="m2" style="background:#CCC;"><img alt="Auction" src="images/zero.gif"></p>
                    </div>
                <?php } }
        } ?>
    </div>

<?php
// =========================================================
// DISPLAY: Products (List View + Grid View)
// =========================================================
} else {
    $prod_col = 'products.*';
    $bus_col = 'business_profile.*';
    $usr_col = 'user.usr_id,user.email,user.fname,user.website,user.country,user.image,user.country_ph_code,user.profileImage';

    $is_grid = (isset($_GET['grid']) && $_GET['grid'] == 'active');

    // Build product SQL
    $newkw = generateProdSearchString($keywords);
    $bnewkw = generateProdSearchString_pro_sup($keywords);
    $min_qty = '';
    $minQty = isset($_POST['min_qty']) ? (int)$_POST['min_qty'] : 0;
    if ($minQty > 0) { $min_qty = " AND products.pd_min_order_qty <= " . $minQty; }

    $totalpage = 50;
    $startpage = 0;
    if (isset($_GET['page']) && (int)$_GET['page'] > 1) {
        $limit = ((int)$_GET['page'] - 1) * $totalpage;
        $setLimit = " LIMIT " . $limit . "," . $totalpage;
    } else {
        $setLimit = " LIMIT 0," . $totalpage;
    }


    $state_condition = '';
if (!empty($stateid)) {
    $state_condition = " and business_profile.bnsprof_state in (" . $stateid . ")";
}

$cntryval = '';
$countryid = isset($_POST['country_id']) ? $_POST['country_id'] : '';
if (!empty($countryid) && is_array($countryid)) {
    $p = 1;
    foreach ($countryid as $key => $value) {
        if ($p == 1) {
            $cntryval .= " and (country.cn_name = '" . mysqli_real_escape_string($con, $value) . "'";
        } else {
            $cntryval .= " or country.cn_name = '" . mysqli_real_escape_string($con, $value) . "'";
        }
        $p++;
    }
    if (!empty($cntryval)) {
        $cntryval .= ")"; // close the opening bracket
    }
}

// $sql_prd = "select measurement_unit.*,country.*," . $bus_col . "," . $prod_col . ", MATCH (pd_title) AGAINST ('" . mysqli_real_escape_string($con, $keywords) . "' IN BOOLEAN MODE) AS title_relevance 
//     from products,measurement_unit,country,business_profile,plan_member_id 
//     where bnsprof_uid = pd_uid 
//     and b_id = bnsprof_id 
//     and mu_id=pd_unit 
//     and ((pd_title LIKE " . $newkw . ") OR (bnsprof_compname LIKE " . $bnewkw . ")) 
//     and pd_currency=cn_id 
//     " . $sql_pd_ck . " 
//     and pd_status='1' 
//     and pd_image!='' 
//     " . $min_qty . "
//     " . $state_condition . "
//     AND plan_member_id.expiry_date > " . time() . " 
//     GROUP BY pd_id 
//     ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc" . $setLimit;

if (!empty($countryid) && is_array($countryid)) {
    // COUNTRY FILTER ACTIVE - same logic as old code ________33
    $sql_prd = "select measurement_unit.*,country.*," . $bus_col . "," . $prod_col . "," . $usr_col . ", 
        MATCH (pd_title) AGAINST ('" . mysqli_real_escape_string($con, $keywords) . "' IN BOOLEAN MODE) AS title_relevance  
        from products,measurement_unit,country,business_profile,user,plan_member_id 
        where user.usr_id = bnsprof_uid 
        and bnsprof_uid=pd_uid 
        and b_id = bnsprof_id  
        and mu_id=pd_unit 
        and ((pd_title LIKE " . $newkw . ") OR (bnsprof_compname LIKE " . $bnewkw . ")) 
        " . $cntryval . " 
        and pd_currency=cn_id 
        and pd_status='1' 
        and pd_image!='' 
        " . $min_qty . "
        " . $state_condition . "
        AND plan_member_id.expiry_date > " . time() . " 
        GROUP BY pd_id 
        ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc" . $setLimit;

} elseif (!empty($stateid)) {
    // STATE FILTER ACTIVE - same logic as old code ________44
    $sql_prd = "select measurement_unit.*,country.*," . $bus_col . "," . $prod_col . ", 
        MATCH (pd_title) AGAINST ('" . mysqli_real_escape_string($con, $keywords) . "' IN BOOLEAN MODE) AS title_relevance  
        from products,measurement_unit,country,business_profile,plan_member_id 
        where bnsprof_uid=pd_uid 
        and mu_id=pd_unit 
        and b_id = bnsprof_id  
        and ((pd_title LIKE " . $newkw . ") OR (bnsprof_compname LIKE " . $bnewkw . ")) 
        and pd_currency=cn_id 
        " . $sql_pd_ck . " 
        and pd_status='1' 
        and pd_image!='' 
        " . $min_qty . "
        " . $state_condition . "
        AND plan_member_id.expiry_date > " . time() . " 
        GROUP BY pd_id 
        ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc" . $setLimit;

} else {
    // NO FILTER - default query ________55
    $sql_prd = "select measurement_unit.*,country.*," . $bus_col . "," . $prod_col . ", 
        MATCH (pd_title) AGAINST ('" . mysqli_real_escape_string($con, $keywords) . "' IN BOOLEAN MODE) AS title_relevance  
        from products,measurement_unit,country,business_profile,plan_member_id 
        where bnsprof_uid = pd_uid 
        and b_id = bnsprof_id 
        and mu_id=pd_unit 
        and ((pd_title LIKE " . $newkw . ") OR (bnsprof_compname LIKE " . $bnewkw . ")) 
        and pd_currency=cn_id 
        " . $sql_pd_ck . " 
        and pd_status='1' 
        and pd_image!='' 
        " . $min_qty . "
        AND plan_member_id.expiry_date > " . time() . " 
        GROUP BY pd_id 
        ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc" . $setLimit;
}


if (isset($_POST['srchbustype'])) {

    $bsn_type = isset($_POST['bsn_type']) ? $_POST['bsn_type'] : [];
    $keyword = "";
    $scity = "";
    $sql_pd_country = "";

    // ✅ BUSINESS TYPE FILTER (FIXED)
    if (!empty($bsn_type) && is_array($bsn_type)) {

        $conditions = [];

        foreach ($bsn_type as $value) {
            $value = mysqli_real_escape_string($con, $value);

            $conditions[] = "(
                bnsprof_businesstype = '$value'
                OR bnsprof_businesstype LIKE '$value,%'
                OR bnsprof_businesstype LIKE '%,$value'
                OR bnsprof_businesstype LIKE '%,$value,%'
            )";
        }

        if (!empty($conditions)) {
            $keyword = " AND (" . implode(" OR ", $conditions) . ") ";
        }
    }

    // ✅ CITY FILTER
    if (isset($_POST['scity']) && strlen($_POST['scity']) > 0) {

        $scity_val = mysqli_real_escape_string($con, $_POST['scity']);

        if ($loc_id > 0) {
            $scity = "bnsprof_city IN(
                SELECT ct_id FROM city 
                WHERE ct_name LIKE '%$scity_val%' 
                AND ct_cn_id = $loc_id
            )";
        } else {
            $scity = "bnsprof_city IN(
                SELECT ct_id FROM city 
                WHERE ct_name LIKE '%$scity_val%'
            )";
        }
    }

    // ✅ COUNTRY LOGIC
    if ($loc_id > 0) {

        $checkCountry = " AND c.cn_id = '$loc_id'";

        if (!empty($scity)) {
            $sql_pd_country = " AND (
                $scity 
                AND pd_uid IN(
                    SELECT usr_id FROM user WHERE country = '$loc_id'
                )
            )";
        }

    } else {

        $checkCountry = "";

        if (!empty($scity)) {
            $sql_pd_country = " AND ($scity)";
        }
    }

    // ✅ MEMBER TYPE FILTER
    $keywordmem = "";
    if (isset($_POST['mst_type']) && is_array($_POST['mst_type'])) {

        $memConditions = [];

        foreach ($_POST['mst_type'] as $value) {
            $value = (int)$value;
            $memConditions[] = "plan_member_id.p_id = '$value'";
        }

        if (!empty($memConditions)) {
            $keywordmem = " AND (" . implode(" OR ", $memConditions) . ")";
        }
    }

    // ✅ FINAL QUERY (JOIN BASED - FIXED)
    $sql_prd = "
        SELECT 
            measurement_unit.*, 
            c.*, 
            $bus_col, 
            $prod_col,
            MATCH (pd_title) AGAINST ('" . mysqli_real_escape_string($con, $keywords) . "' IN BOOLEAN MODE) AS title_relevance

        FROM products
        INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid
        INNER JOIN measurement_unit ON measurement_unit.mu_id = products.pd_unit
        INNER JOIN country c ON c.cn_id = products.pd_currency
        INNER JOIN plan_member_id ON plan_member_id.b_id = business_profile.bnsprof_id

        WHERE 
            products.pd_status = '1'
            AND products.pd_image != ''
            AND plan_member_id.expiry_date > " . time() . "

            AND (
                products.pd_title LIKE $newkw 
                OR business_profile.bnsprof_compname LIKE $bnewkw
            )

            $sql_pd_country
            $checkCountry
            $keyword
            $keywordmem

        GROUP BY products.pd_id

        ORDER BY 
            title_relevance DESC,
            FIELD(plan_member_id.p_id,'5','4','3','15','0'),
            products.pd_title ASC

        LIMIT 0,20
    ";

}

    // $sql_prd = "select measurement_unit.*,country.*," . $bus_col . "," . $prod_col . ", MATCH (pd_title) AGAINST ('" . mysqli_real_escape_string($con, $keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE " . $newkw . ") OR (bnsprof_compname LIKE " . $bnewkw . ")) and pd_currency=cn_id " . $sql_pd_ck . " and pd_status='1' and pd_image!='' " . $min_qty . " AND plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc" . $setLimit;

    // Currency-country fix
    $sql_prd = preg_replace('#\bcountry,#msi', 'country c,', $sql_prd);
    $sql_prd = preg_replace('#^(.+?\b)where(.+)$#msi', '$1, city, country c2 where c2.cn_id=pd_currency AND bnsprof_city=ct_id AND $2', $sql_prd);
    $sql_prd = preg_replace('#pd_currency\s*=\s*cn_id#msi', 'cn_id=ct_cn_id', $sql_prd);
    $sql_prd = preg_replace('#([^.a-z_-])cn_(id|code)#msi', '$1c.cn_$2', $sql_prd);
    $sql_prd = str_replace("select c.cn_id from country where c.cn_code", "select cn_id from country where cn_code", $sql_prd);
    $sql_prd = preg_replace('#\bcountry\.#msi', 'c.', $sql_prd);

    // Total count
    $sql_prd_total = str_replace($setLimit, '', $sql_prd);
    $run_query_tot = mysqli_query($con, $sql_prd_total);
    $gettot_product = $run_query_tot ? mysqli_num_rows($run_query_tot) : 0;

    $run_query = mysqli_query($con, $sql_prd);
    $getSearchCount = $run_query ? mysqli_num_rows($run_query) : 0;
    if ($getSearchCount == 0 && isset($_GET['search_mode']) && $_GET['search_mode'] === 'scenario') {
        $fallbackKeyword = trim((string)($_GET['ai_product'] ?? ''));
        if ($fallbackKeyword === '') {
            $fallbackKeyword = trim((string)($keywords ?? ''));
        }
        $fallbackTerms = function_exists('ai_search_terms') ? ai_search_terms($fallbackKeyword) : preg_split('/\s+/u', $fallbackKeyword, -1, PREG_SPLIT_NO_EMPTY);
        $fallbackTerms = array_slice(array_values(array_unique(array_filter((array)$fallbackTerms))), 0, 6);
        $fallbackWhereParts = array();
        foreach ($fallbackTerms as $fallbackTerm) {
            $fallbackTerm = trim((string)$fallbackTerm);
            if ($fallbackTerm !== '') {
                $escapedTerm = mysqli_real_escape_string($con, $fallbackTerm);
                $fallbackWhereParts[] = "(products.pd_title LIKE '%{$escapedTerm}%' OR business_profile.bnsprof_compname LIKE '%{$escapedTerm}%')";
            }
        }
        $fallbackWhere = !empty($fallbackWhereParts) ? '(' . implode(' OR ', $fallbackWhereParts) . ')' : '1=1';
        $fallbackMatch = mysqli_real_escape_string($con, $fallbackKeyword !== '' ? $fallbackKeyword : ($keywords ?? ''));
        $sql_prd = "
            SELECT 
                measurement_unit.*, 
                c.*, 
                $bus_col, 
                $prod_col,
                MATCH (pd_title) AGAINST ('" . $fallbackMatch . "' IN BOOLEAN MODE) AS title_relevance
            FROM products
            INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid
            INNER JOIN measurement_unit ON measurement_unit.mu_id = products.pd_unit
            INNER JOIN country c ON c.cn_id = products.pd_currency
            INNER JOIN plan_member_id ON plan_member_id.b_id = business_profile.bnsprof_id
            WHERE products.pd_status = '1'
                AND products.pd_image != ''
                AND plan_member_id.expiry_date > " . time() . "
                AND " . $fallbackWhere . "
            GROUP BY products.pd_id
            ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), products.pd_title ASC
            LIMIT 0,20
        ";
        $sql_prd_total = str_replace($setLimit, '', $sql_prd);
        $run_query_tot = mysqli_query($con, $sql_prd_total);
        $gettot_product = $run_query_tot ? mysqli_num_rows($run_query_tot) : 0;
        $run_query = mysqli_query($con, $sql_prd);
        $getSearchCount = $run_query ? mysqli_num_rows($run_query) : 0;
        if ($getSearchCount == 0) {
            $sql_prd = "
                SELECT 
                    measurement_unit.*, 
                    c.*, 
                    $bus_col, 
                    $prod_col,
                    0 AS title_relevance
                FROM products
                INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid
                INNER JOIN measurement_unit ON measurement_unit.mu_id = products.pd_unit
                INNER JOIN country c ON c.cn_id = products.pd_currency
                INNER JOIN plan_member_id ON plan_member_id.b_id = business_profile.bnsprof_id
                WHERE products.pd_status = '1'
                    AND products.pd_image != ''
                    AND plan_member_id.expiry_date > " . time() . "
                GROUP BY products.pd_id
                ORDER BY FIELD(plan_member_id.p_id,'5','4','3','15','0'), products.pd_id DESC
                LIMIT 0,20
            ";
            $sql_prd_total = str_replace($setLimit, '', $sql_prd);
            $run_query_tot = mysqli_query($con, $sql_prd_total);
            $gettot_product = $run_query_tot ? mysqli_num_rows($run_query_tot) : 0;
            $run_query = mysqli_query($con, $sql_prd);
            $getSearchCount = $run_query ? mysqli_num_rows($run_query) : 0;
        }
    }

    if (!$is_grid) {
        // =====================
        // LIST VIEW
        // =====================
        ?>
        <div id="search_result" class="list-grid-active" style="position:relative;">
        <?php
        if ($getSearchCount > 0) {
            $myfev = [];
            $countRec = 1;
            $catTopBanner = "";
            $catBottomBanner = "";

            while ($row = mysqli_fetch_array($run_query, MYSQLI_ASSOC)) {
                $fevrow_icon = 0;
$bp_sql = "SELECT business_profile.*, 

               city.ct_name, 
               city.ct_state,
               country.cn_name,
               country.cn_flag,
               country.cn_code,
               country.cn_ph,
               states.state_name,
               user.country as user_country_id,
               user.country as country
           FROM business_profile 
           LEFT JOIN city ON city.ct_id = business_profile.bnsprof_city 
           LEFT JOIN user ON user.usr_id = business_profile.bnsprof_uid
           LEFT JOIN country ON country.cn_id = user.country
           LEFT JOIN states ON states.state_id = business_profile.bnsprof_state
           WHERE business_profile.bnsprof_uid = " . (int)$row['pd_uid'] . " 
           LIMIT 1";
$bp_result = mysqli_query($con, $bp_sql);
$data = ($bp_result && mysqli_num_rows($bp_result) > 0) ? mysqli_fetch_assoc($bp_result) : null;
   
if (!isset($userArrayRow_Type) || empty($userArrayRow_Type)) {
    $userArrayRow_Type = [];
    $bt_result = mysqli_query($con, "SELECT bsntyp_id, bsntyp_title FROM business_type WHERE bsntyp_status = '1'");
    if ($bt_result) {
        while ($bt_row = mysqli_fetch_assoc($bt_result)) {
            $userArrayRow_Type[$bt_row['bsntyp_id']] = $bt_row['bsntyp_title'];
        }
    }
}
   //print_r($data);

///echo "nulllll";
               // $data = isset($userArrayRow_Result[$row['pd_uid']]) ? $userArrayRow_Result[$row['pd_uid']] : null;
              //  print_r($userArrayRow_Result);

                if ($data) {
                    $get_icon = mysqli_query($con, "select smembership_plan.mst_icon as sponsericon, plan_member_id.*, smembership_icon_plan.mst_icon as producticon, smembership_icon_plan.mst_name as pplan from smembership_plan,plan_member_id,smembership_icon_plan where smembership_icon_plan.mp_id=plan_member_id.p_id and smembership_plan.mp_id=plan_member_id.p_id and plan_member_id.b_id=" . $data['bnsprof_id']);
                    if (mysqli_num_rows($get_icon)) { $fevrow_icon = mysqli_fetch_array($get_icon, MYSQLI_ASSOC); }
                    $get_icon2 = mysqli_query($con, "select icon_id, p_id from plan_member_id where b_id=" . $data['bnsprof_id']);
                    $icon2 = mysqli_fetch_array($get_icon2);
                    $get_icon1 = mysqli_query($con, "select * from smembership_icon_plan where mp_id=" . ($icon2['icon_id'] ?? 0));
                    $icon1 = mysqli_fetch_array($get_icon1);
                    $get_icon3 = mysqli_query($con, "select * from smembership_plan where mp_id=" . ($icon2['p_id'] ?? 0));
                }
                $pimg = explode(',', $row['pd_image']);
                $limg = explode(',', $row['pd_imagelogo'] ?? '');
                $zoom_image_val = '/upload/myproduct/' . $pimg[0];
                $rand = rand(1000, 9999);
                $symbol = '$';
                $geo_loc = $location_geo_country[0] ?? 243;
                $countryyyy = $_COOKIE['loc_id'] ?? '';
                ?>

                <div class="row ar-mid-box" style="width:100%">
                    <div class="col-lg-12 col-sm-11 col-md-11 ar-box-1 margin-top-10">
                        <div class="row">
                            <div class="col-xs-6 col-lg-3 big-img-box box-1" id="div-<?php echo $row['pd_id']; ?>">
                                <header>
                                    <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
                                        <a href="javascript:void(0)" class="product_fav_btn" onclick="return showfavorite(<?php echo $_SESSION['uid_indm']; ?>,<?php echo $row['pd_id']; ?>)"><i class="fa fa-star star" style="color:<?php echo (in_array($row['pd_id'], $myfev)) ? '#E48F23' : '#808080'; ?>"></i> Favorite</a>
                                    <?php } else { ?>
                                        <a href="sign-in.php" class="product_fav_btn"><i class="fa fa-star star"></i> Favorite</a>
                                    <?php } ?>
                                    <a href="javascript:void(0)" class="ar-star product_compare" onClick="return addcompare(<?php echo $row['pd_id']; ?>)" data-prod_id="<?php echo $row['pd_id']; ?>"><i class="fa fa-plus star"></i> Compare</a>
                                </header>
                                <figure class="box">
                                    <?php if ($fevrow_icon) { ?>
                                        <div class="ribbon"><img src="./admin/images/<?php echo $fevrow_icon['sponsericon']; ?>"/></div>
                                    <?php } elseif (isset($get_icon3) && $get_icon3 && mysqli_num_rows($get_icon3) > 0) {
                                        $fevrow_icon3 = mysqli_fetch_array($get_icon3, MYSQLI_ASSOC); ?>
                                        <div class="ribbon"><img src="./admin/images/<?php echo $fevrow_icon3['mst_icon']; ?>"/></div>
                                    <?php } ?>
                                    <div class="zoomthis">
                                        <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/product-details.php?token=<?php echo rand(1000,9999).md5($row['pd_id']); ?>&c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>" target="_blank">
                                            <img src="/upload/myproduct/<?php echo $pimg[0]; ?>" onerror="this.src='/images/noimage.jpg'"/>
                                        </a>
                                    </div>
                                    <?php if (!empty($row['pd_imagelogo'])) { ?>
                                        <div class="zk" style="border:1px solid #267abf;height:auto;width:100px;position:absolute;top:172px;left:5px;">
                                            <img style="width:auto;height:100px;max-width:100%;" src="/upload/myproduct/<?php echo $limg[0]; ?>"/>
                                        </div>
                                    <?php } ?>
                                </figure>
                                <center><a onclick="zoom_image(this)" data-img="<?php echo $zoom_image_val; ?>" style="padding:10px;"><i class="fa fa-search-plus"></i></a></center>
                            </div>

                            <div class="col-xs-6 col-lg-5 box-2">
                                <ul>
                                    <li class="margin-bottom-0">
                                        <h4 class="txt-blue">
                                            <a class="txt-blue" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/product-details.php?token=<?php echo rand(1000,9999).md5($row['pd_id']); ?>&c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>" target="_blank">
                                                <?php echo highlight($row['pd_title'], urlencode($raw_keywords)); ?>
                                            </a>
                                        </h4>
                                    </li>
                                    <li><?php echo substr($row['pd_desc'], 0, 132); ?></li>
                                    <li class="text-right">
                                        <?php if (!empty($row['brand_name'])) { ?><span style="float:left;"><strong>Brand:</strong> <?php echo $row['brand_name']; ?></span><?php } ?>
                                        <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/product-details.php?token=<?php echo rand(1000,9999).md5($row['pd_id']); ?>&c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>" target="_blank">+ More</a>
                                    </li>
                                    <li>Min Order &nbsp;<big class="txt-bold txt-red"><?php echo $row['pd_min_order_qty']; ?></big>&nbsp; <?php echo measurement_unit($row['pd_unit']); ?></li>
                                    <?php $style_none = ((int)$row['pd_fob_price'] == 0) ? 'hide' : 'show'; ?>
                                    <li class="<?php echo $style_none; ?>">&nbsp;
                                        <big class="txt-bold txt-red"><a style="color:black" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/product-add.php"><?php echo $symbol . $row['pd_fob_price'] . ' ~ ' . $symbol . $row['pd_fob_price2']; ?></a></big>
                                        <?php if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') { ?>
                                            <a style="float:right;" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sign-in.php">(Get Latest Price)</a>
                                        <?php } else { ?>
                                            <a class="ajax" style="float:right;" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/quotationRequest_supplier.php?id=<?php echo $rand.md5($data['bnsprof_id'] ?? 0); ?>&pid=<?php echo $row['pd_id']; ?>&keywords=<?php echo urlencode($raw_keywords); ?>&geo=<?php echo $geo_loc; ?>&conty=<?php echo $countryyyy; ?>&search=1">(Get Latest Price)</a>
                                        <?php } ?>
                                    </li>
                                    <li class="margin-top-5">
                                        <table class="table"><tr>
                                            <td style="padding-left:0px;"><a href="/company/index.php?c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>" target="_blank" class="txt-blue txt-bold hidden-xs"><img src="images/users.png" width="25px"/> About Us</a></td>
                                            <td class="hidden-xs"><a href="/company/products.php?c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>&flaag=whsuccess" target="_blank" class="txt-blue txt-bold hidden-xs"><img src="images/icon.png" width="20px"/> View Products</a></td>
                                            <?php if ($row['pd_pdf_attach']) { ?><td><a href="//<?php echo $_SERVER['HTTP_HOST']; ?>/upload/productdoc/<?php echo $row['pd_pdf_attach']; ?>" target="_blank"><img src="/images/pdf_icon.png" style="width:28px;height:28px;"> PDF</a></td><?php } ?>
                                            <td><a onclick="open_chat()" class="hidden-xs"><i class="fa fa-comments"></i></a></td>
                                        </tr></table>
                                    </li>
                                    
                                   
                                   <li>
    <table class="table enquiry-tb margin-bottom-0">
        <tr class="bg-gray">
            <?php
            $is_logged_in = isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '';
            $product_id = $row['pd_id'] ?? 0;
            $product_title = $row['pd_title'] ?? 'Product';
            ?>
            <td class="padding-0 col-sm-6" style="vertical-align:middle;">
                <?php if ($is_logged_in) { ?>
                    <big>&nbsp;
                        <a href="javascript:void(0)" onclick="openWaRfq(<?php echo $product_id; ?>, '<?php echo addslashes($product_title); ?>')" style="color:#25D366; text-decoration:none; font-weight:bold;">
                            <img src="images/whatsapp-icon.png" width="25px" style="vertical-align:middle;"> 
                            طلب سعر واتساب
                        </a>
                    </big>
                <?php } else { ?>
                    <big>&nbsp;
                        <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sign-in.php#loginform" style="color:#25D366; text-decoration:none; font-weight:bold;">
                            <img src="images/whatsapp-icon.png" width="25px" style="vertical-align:middle;"> 
                            سجل دخول لطلب السعر
                        </a>
                    </big>
                <?php } ?>
            </td>
            <td class="text-right padding-0 col-sm-6">
                <?php if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') { ?>
                    <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sign-in.php"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                <?php } else { ?>
                    <a class="ajax" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/quotationRequest.php?id=<?php echo $rand.md5($data['bnsprof_id'] ?? 0); ?>&pid=<?php echo $row['pd_id']; ?>&keywords=<?php echo urlencode($raw_keywords); ?>&geo=<?php echo $geo_loc; ?>&conty=<?php echo $countryyyy; ?>&search=1">
                        <button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button>
                    </a>
                <?php } ?>
            </td>
        </tr>
    </table>
</li>
                               
                               
                               
                                </ul>
                            </div>

                            <div class="col-lg-4 box-3">
                                <div class="ar-box-1 ar-box padding-5 margin-bottom-5 bg-gray" style="overflow-x:hidden;">
                                    <header class="sub-box">
                                        <?php if ($fevrow_icon) { ?><a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/membership_plans.php"><img src="./admin/images/<?php echo $fevrow_icon['producticon']; ?>" width="25px" height="25px" title="<?php echo $fevrow_icon['pplan']; ?>"/></a><?php }
                                        elseif (isset($get_icon1) && $get_icon1 && mysqli_num_rows($get_icon1) > 0) { ?><a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/membership_plans.php"><img src="./admin/images/<?php echo $icon1['mst_icon']; ?>" width="25px" height="25px"/></a><?php } ?>
                                        <b class="txt-dark-gray">
                                            <a href="/company/profile.php?c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>" target="_blank" style="white-space:nowrap;">
                                                <?php echo ucfirst(substr($data['bnsprof_compname'] ?? '', 0, 20)); ?>...
                                            </a>
                                        </b>
                                    </header>
                                    <?php
                                    $countryId = $data['country'] ?? 0;



                                    $getCountryName = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM country where cn_id='" . (int)$countryId . "'"));
                                    $getStateName = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM states where state_id='" . (int)($data['ct_state'] ?? 0) . "'"));
                                    if (!empty($getCountryName['cn_flag'])) { ?>
                                        <img src="images/country_flag/<?php echo $getCountryName['cn_flag']; ?>" style="width:21.6px;height:21.6px;"/>
                                    <?php } ?>
                                    <b class="txt-bold" style="color:#302670;margin-left:10px;"><?php
                                    $address = '';
                                    if (!empty($getCountryName['cn_name'])) $address .= $getCountryName['cn_name'] . '-';
                                    if (!empty($getStateName['state_name'])) $address .= $getStateName['state_name'] . '-';
                                    if (!empty($data['ct_name'])) $address .= $data['ct_name'];
                                    echo $address ?: 'Not available';
                                    ?></b>
                                    <table class="table margin-top-5">
                                        <tr>
                                            <td class="txt-light-gray padding-0">Business Type:</td>
                                            <td class="padding-0 txt-bold"><?php
                                            $bnsprof_businesstype = $data['bnsprof_businesstype'] ?? '';
                                            $dataC = explode(",", $bnsprof_businesstype);
                                            if ($bnsprof_businesstype != '') {
                                                $busn_type = ''; $i = 1;
                                                foreach ($dataC as $r) {
                                                    if ($i <= 2) { $busn_type .= ($userArrayRow_Type[$r] ?? ''); if ($i < count($dataC)) $busn_type .= ", "; $i++; }
                                                }
                                                echo $busn_type . '...';
                                            } else { echo 'Not available'; }
                                            ?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-light-gray padding-0">Trade Location:</td>
                                            <td class="padding-0 txt-bold"><?php
                                            if ($row['pd_preferred_buyer_location'] == 'abroad') echo "Abroad Only";
                                            else if ($row['pd_preferred_buyer_location'] == 'any') echo "Abroad + Domestic";
                                            else if ($row['pd_preferred_buyer_location'] == 'domestic') echo "Domestic Only";
                                            else if ($row['pd_preferred_buyer_location'] == 'my_city') echo "My City Only";
                                            ?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-light-gray padding-0" width="95">Member Since:</td>
                                            <td class="padding-0 txt-bold"><?php echo date("Y", strtotime($data['bnsprof_creation_date'] ?? '')); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-light-gray" colspan="2" style="padding-left:0;">
                                                <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                                    <a href="<?php echo $data['bnsprof_website_alt'] ?? ''; ?>" target="_blank"><?php echo $data['bnsprof_website_alt'] ?? ''; ?></a>
                                                <?php } else { ?>
                                                    <a href="/sign-in.php#loginform">Show Website</a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="small-box hidden-xs">
                                    <div class="box-under-twoimage">
                                        <?php
                                        $rresult = mysqli_query($con, 'select pd_id,pd_title,pd_imagelogo,pd_image,pd_subcat_id from products where pd_image!="" and pd_uid=' . $row['pd_uid'] . ' and pd_status="1" and pd_id!=' . $row['pd_id'] . ' ORDER by pd_id DESC limit 2');
                                        $rcoun = 1; $releted_pro = '';
                                        while ($rrow = mysqli_fetch_array($rresult, MYSQLI_ASSOC)) {
                                            $imgarr = explode(',', $rrow['pd_image']);
                                            $cls = ($rcoun == 1) ? 'small-box-td1' : 'small-box-td2';
                                            $releted_pro .= '<div class="padding-0 ' . $cls . '"><div class="wrapper-product-searchright"><a class="thumb-images" href="search.php?keywords=' . urlencode($rrow['pd_title']) . '&rctyp=Products"><img title="' . htmlspecialchars($rrow['pd_title']) . '" class="photo" src="/upload/myproduct/' . htmlspecialchars($imgarr[0]) . '"/></a></div></div>';
                                            $rcoun++;
                                        }
                                        echo $releted_pro;
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>

                <?php
                if (empty($catTopBanner)) {
                    $catTopBanner = categoryAdsBanner($loc_id ?: '', $row['pd_subcat_id'], "", "top");
                    $checkTopCatBan = explode('~~', $catTopBanner);
                }
                if (empty($catBottomBanner)) {
                    $catBottomBanner = categoryAdsBanner($loc_id ?: '', $row['pd_subcat_id'], "", "bottom");
                    $checkBottomCatBan = explode('~~', $catBottomBanner);
                }

                if ($countRec == $getSearchCount) {
                    $pages = (isset($_GET['page']) ? (int)$_GET['page'] : 1) + 1;
                    $gettot_product1 = $gettot_product - ((isset($_GET['page']) ? (int)$_GET['page'] : 1) * 30);
                    if ($gettot_product1 > $totalpage) {
                        echo '<div class="col-lg-12 text-center" style="padding:30px;"><a href="https://' . $_SERVER['HTTP_HOST'] . '/search.php?rctyp=' . urlencode($rctyp) . '&keywords=' . urlencode($raw_keywords) . '&page=' . $pages . '"><button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px;font-weight:bolder;">Display More Products / Services</button></a></div>';
                    }
                }
                $countRec++;
            }
        } else { ?>
            <table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%">
                <tr style="width:100%;text-align:left;">
                    <td valign="TOP" style="width:100%">
                        <div class="sor">Sorry, your search for <b class="cb1"><?php echo htmlspecialchars($keywords); ?></b> did not match any Product.</div>
                        <div class="sug"><b>Suggestions:</b><ul><li>Check spellings of your search words</li><li>Try a different set of search words</li><li>Use two or three words for best search results</li></ul></div>
                    </td>
                </tr>
            </table>
        <?php } ?>
        </div>
        </div>

    <?php } else {
        // =====================
        // GRID VIEW
        // =====================
        ?>
        <div class="row fond active-grid-option">
        <?php
        // Grid SQL with limit
        $totalgridpage = 50;
        if (isset($_GET['page']) && (int)$_GET['page'] > 1) {
            $gridlimit = ((int)$_GET['page'] - 1) * $totalgridpage;
            $gridsetLimit = " LIMIT " . $gridlimit . "," . $totalgridpage;
        } else {
            $gridsetLimit = " LIMIT 0," . $totalgridpage;
        }

        $sql_prd_grid = "select *, MATCH (pd_title) AGAINST ('" . mysqli_real_escape_string($con, $keywords) . "' IN BOOLEAN MODE) AS title_relevance from products as prod,measurement_unit,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE " . $newkw . ") OR (bnsprof_compname LIKE " . $bnewkw . ")) and pd_currency=cn_id " . $sql_pd_ck . " and pd_status='1' and pd_image!='' AND plan_member_id.expiry_date > " . time() . " ORDER BY title_relevance DESC, FIELD(p_id,'5','4','3','15','0'), pd_title asc";

        // Currency-country fix
        $sql_prd_grid = preg_replace('#\bcountry,#msi', 'country c,', $sql_prd_grid);
        $sql_prd_grid = preg_replace('#^(.+?\b)where(.+)$#msi', '$1, city, country c2 where c2.cn_id=pd_currency AND bnsprof_city=ct_id AND $2', $sql_prd_grid);
        $sql_prd_grid = preg_replace('#pd_currency\s*=\s*cn_id#msi', 'cn_id=ct_cn_id', $sql_prd_grid);
        $sql_prd_grid = preg_replace('#([^.a-z_-])cn_(id|code)#msi', '$1c.cn_$2', $sql_prd_grid);
        $sql_prd_grid = str_replace("select c.cn_id from country where c.cn_code", "select cn_id from country where cn_code", $sql_prd_grid);
        $sql_prd_grid = preg_replace('#\bcountry\.#msi', 'c.', $sql_prd_grid);

        $run_query_grid_count = mysqli_query($con, $sql_prd_grid);
        $getSearchCount = $run_query_grid_count ? mysqli_num_rows($run_query_grid_count) : 0;

        if ($getSearchCount > 0) {
            $sql_prd_grid .= $gridsetLimit;
            $run_query = mysqli_query($con, $sql_prd_grid);
            $gridRecCount = 1;
            $myfev = [];
            $geo_loc = $location_geo_country[0] ?? 243;
            $countryyyy = $_COOKIE['loc_id'] ?? '';

            while ($row = mysqli_fetch_array($run_query, MYSQLI_ASSOC)) {
                $fevrow_icon = 0;
               $bp_sql = "SELECT business_profile.*, 
                
               city.ct_name, 
               city.ct_state,
               country.cn_name,
               country.cn_flag,
               country.cn_code,
               country.cn_ph,
               states.state_name,
               user.country as user_country_id,
               user.country as country
           FROM business_profile 
           LEFT JOIN city ON city.ct_id = business_profile.bnsprof_city 
           LEFT JOIN user ON user.usr_id = business_profile.bnsprof_uid
           LEFT JOIN country ON country.cn_id = user.country
           LEFT JOIN states ON states.state_id = business_profile.bnsprof_state
           WHERE business_profile.bnsprof_uid = " . (int)$row['pd_uid'] . " 
           LIMIT 1";
$bp_result = mysqli_query($con, $bp_sql);
$data = ($bp_result && mysqli_num_rows($bp_result) > 0) ? mysqli_fetch_assoc($bp_result) : null;
   
if (!isset($userArrayRow_Type) || empty($userArrayRow_Type)) {
    $userArrayRow_Type = [];
    $bt_result = mysqli_query($con, "SELECT bsntyp_id, bsntyp_title FROM business_type WHERE bsntyp_status = '1'");
    if ($bt_result) {
        while ($bt_row = mysqli_fetch_assoc($bt_result)) {
            $userArrayRow_Type[$bt_row['bsntyp_id']] = $bt_row['bsntyp_title'];
        }
    }
}
                if ($data) {
                    $get_icon = mysqli_query($con, "select smembership_plan.mst_icon as sponsericon, plan_member_id.*, smembership_icon_plan.mst_icon as producticon, smembership_icon_plan.mst_name as pplan from smembership_plan,plan_member_id,smembership_icon_plan where smembership_icon_plan.mp_id=plan_member_id.p_id and smembership_plan.mp_id=plan_member_id.p_id and plan_member_id.b_id=" . $data['bnsprof_id']);
                    if (mysqli_num_rows($get_icon)) { $fevrow_icon = mysqli_fetch_array($get_icon, MYSQLI_ASSOC); }
                    $get_icon2 = mysqli_query($con, "select icon_id, p_id from plan_member_id where b_id=" . $data['bnsprof_id']);
                    $icon2 = mysqli_fetch_array($get_icon2);
                    $get_icon1 = mysqli_query($con, "select * from smembership_icon_plan where mp_id=" . ($icon2['icon_id'] ?? 0));
                    $icon1 = mysqli_fetch_array($get_icon1);
                    $get_icon3 = mysqli_query($con, "select * from smembership_plan where mp_id=" . ($icon2['p_id'] ?? 0));
                }
                $pimg2 = explode(',', $row['pd_image']);
                $symbol = '$';
                $rand = rand(1000, 9999);
                ?>

                <div class="col-md-4 compared-box compared-box1 style_prevu_kit">
                    <div class="text-right" id="div-<?php echo $row['pd_id']; ?>"></div>
                    <header style="padding:15px 0;width:100% !important;" class="titleLim box-2">
                        <span class="txt-blue">
                            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/product-details.php?token=<?php echo rand(1000,9999).md5($row['pd_id']); ?>&c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>" target="_blank" class="h4" style="font-weight:bold;">
                                <?php echo highlight($row['pd_title'], urlencode($raw_keywords)); ?>
                            </a>
                        </span>
                    </header>

                    <figure class="img-box">
                        <div class="ara-links">
                            <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
                                <a href="javascript:void(0)" class="product_fav_btn" onclick="showfavorite(<?php echo $_SESSION['uid_indm']; ?>,<?php echo $row['pd_id']; ?>)" data-prod_id="<?php echo $row['pd_id']; ?>"><i class="fa fa-star"></i> Favorite</a>
                            <?php } else { ?>
                                <a href="sign-in.php" class="product_fav_btn"><i class="fa fa-star"></i> Favorite</a>
                            <?php } ?>
                            <a href="javascript:void(0)" onClick="return addcompare(<?php echo $row['pd_id']; ?>)" data-prod_id="<?php echo $row['pd_id']; ?>"><i class="fa fa-plus"></i> Compare</a>
                        </div>

                        <div class="zoomthis">
                            <?php if ($fevrow_icon) { ?>
                                <div class="ribbon"><img src="./admin/images/<?php echo $fevrow_icon['sponsericon']; ?>"/></div>
                            <?php } elseif (isset($get_icon3) && $get_icon3 && mysqli_num_rows($get_icon3) > 0) {
                                $fevrow_icon3 = mysqli_fetch_array($get_icon3, MYSQLI_ASSOC); ?>
                                <div class="ribbon"><img src="./admin/images/<?php echo $fevrow_icon3['mst_icon']; ?>"/></div>
                            <?php } ?>
                            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/product-details.php?token=<?php echo rand(1000,9999).md5($row['pd_id']); ?>&c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>" target="_blank">
                                <img src="/upload/myproduct/<?php echo $pimg2[0]; ?>" onerror="this.src='/images/noimage.jpg'" style="max-width:100%;"/>
                            </a>
                        </div>
                        <?php if (!empty($row['pd_imagelogo'])) {
                            $limg2 = explode(',', $row['pd_imagelogo']); ?>
                            <div class="zk" style="border:1px solid #267abf;height:auto;width:100px;position:absolute;top:121px;left:1px;">
                                <img style="width:auto;height:50px;max-width:100%;" src="/upload/myproduct/<?php echo $limg2[0]; ?>"/>
                            </div>
                        <?php } ?>
                    </figure>

                    <section>
                        <table style="text-align:left;width:100%;">
                            <?php if (!empty($data['bnsprof_compname'])) { ?>
                            <tr>
                                <td>
                                    <?php if ($fevrow_icon) { ?>
                                        <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/membership_plans.php"><img src="./admin/images/<?php echo $fevrow_icon['producticon']; ?>" width="25px" height="25px" title="<?php echo $fevrow_icon['pplan']; ?>"/></a>
                                    <?php } elseif (isset($get_icon1) && $get_icon1 && mysqli_num_rows($get_icon1) > 0) { ?>
                                        <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/membership_plans.php"><img src="./admin/images/<?php echo $icon1['mst_icon']; ?>" width="25px" height="25px"/></a>
                                    <?php } ?>
                                </td>
                                <td colspan="1" style="float:left;white-space:nowrap;">
                                    <a href="/company/profile.php?c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>" target="_blank" style="font-weight:bold;">
                                        <?php echo ucfirst(substr($data['bnsprof_compname'] ?? '', 0, 20)); ?>...
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td>
                                    <?php
                                    $countryId = $data['country'] ?? 0;
                                    $getCountryName = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM country where cn_id='" . (int)$countryId . "'"));
                                    $getStateName = mysqli_fetch_array(mysqli_query($con, "SELECT * FROM states where state_id='" . (int)($data['ct_state'] ?? 0) . "'"));
                                    if (!empty($getCountryName['cn_flag'])) { ?>
                                        <img src="images/country_flag/<?php echo $getCountryName['cn_flag']; ?>" alt="flag" style="width:21.6px;height:21.6px;"/>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php
                                    $address = '';
                                    if (!empty($getCountryName['cn_name'])) $address .= $getCountryName['cn_name'] . '-';
                                    if (!empty($getStateName['state_name'])) $address .= $getStateName['state_name'] . '-';
                                    if (!empty($data['ct_name'])) $address .= $data['ct_name'];
                                    echo $address ?: 'Not available';
                                    ?>
                                </td>
                            </tr>
                            <tr class="height44">
                                <td colspan="2" style="color:#00F;"><?php
                                $bnsprof_businesstype = $data['bnsprof_businesstype'] ?? '';
                                $dataC = explode(",", $bnsprof_businesstype);
                                if ($bnsprof_businesstype != '') {
                                    $i = 1;
                                    foreach ($dataC as $r) {
                                        echo ($userArrayRow_Type[$r] ?? '');
                                        if ($i < count($dataC)) echo ", ";
                                        $i++;
                                    }
                                } else { echo 'Not available'; }
                                ?></td>
                            </tr>
                        </table>

                        <table class="webcast-new-table">
                            <tr>
                                <td></td>
                                <td><big class="txt-bold txt-red"><?php echo $row['pd_min_order_qty']; ?></big> <?php echo measurement_unit($row['pd_unit']); ?> (Min Order)</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><big class="txt-bold txt-red"><?php echo $symbol . $row['pd_fob_price'] . ' ~ ' . $symbol . $row['pd_fob_price2']; ?></big></td>
                            </tr>
                           
                           
                                                    <td colspan="2" class="grid_mobile">
    <img src="images/whatsapp-icon.png" width="25px"/> &nbsp;
    <?php if (!empty($_SESSION['uid_indm'])) { 
        $grid_product_id = $data['pc_id'] ?? $row['pd_id'] ?? 0;
        $grid_product_title = $data['pd_title'] ?? $row['pd_title'] ?? 'Product';
    ?>
        <a href="javascript:void(0)" onclick="openWaRfq(<?php echo $grid_product_id; ?>, '<?php echo addslashes($grid_product_title); ?>')" class="txt-black h4" style="color:#25D366; text-decoration:none; font-weight:bold;">
            طلب سعر واتساب
        </a>
    <?php } else { ?>
        <a href="/sign-in.php#loginform" class="txt-black h4" style="color:#25D366; text-decoration:none; font-weight:bold;">
            سجل دخول لطلب السعر
        </a>
    <?php } ?>
</tr>
                           
                           
                           
                            <tr>
                                <td></td>
                                <td style="text-align:center;">
                                    <?php if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') { ?>
                                        <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sign-in.php">
                                            <button class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button>
                                        </a>
                                    <?php } else { ?>
                                        <a class="ajax" href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/quotationRequest.php?id=<?php echo $rand.md5($data['bnsprof_id'] ?? 0); ?>&pid=<?php echo $row['pd_id']; ?>&keywords=<?php echo urlencode($raw_keywords); ?>&geo=<?php echo $geo_loc; ?>&conty=<?php echo $countryyyy; ?>&search=1">
                                            <button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        </table>

                        <div class="chat-button">
                            <a href="/company/products.php?c=<?php echo rand(1000,9999).md5($data['bnsprof_id'] ?? 0); ?>&sc=<?php echo rand(10000,99999).($data['pd_subcat_id'] ?? 0); ?>#<?php echo $row['pd_id']; ?>"></a>
                        </div>
                    </section>
                </div>

                <?php
                if ($gridRecCount == $totalgridpage) {
                    $pages = (isset($_GET['page']) ? (int)$_GET['page'] : 1) + 1;
                    echo '<div class="col-lg-12 text-center" style="padding:30px;"><a href="https://' . $_SERVER['HTTP_HOST'] . '/search.php?keywords=' . urlencode($raw_keywords) . '&grid=active&page=' . $pages . '"><button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px;font-weight:bolder;">Display More Products / Services</button></a></div>';
                }
                $gridRecCount++;
            }
        } else { ?>
            <table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%">
                <tr style="width:100%;text-align:left;">
                    <td valign="TOP" style="width:100%">
                        <div class="sor">Sorry, your search for <b class="cb1"><?php echo htmlspecialchars($keywords); ?></b> did not match any Product.</div>
                        <div class="sug"><b>Suggestions:</b><ul><li>Check spellings of your search words</li><li>Try different search words</li></ul></div>
                    </td>
                </tr>
            </table>
        <?php } ?>
        <div class="clearfix"></div>
        </div>
        </div>
    <?php }
} ?>

<input type="hidden" name="keyrcType" id="keyrcType" value="<?php echo htmlspecialchars($_GET['rctyp'] ?? ''); ?>"/>
<input type="hidden" name="checkLoginUser" id="checkLoginUser" value="<?php echo htmlspecialchars($_SESSION['uid_indm'] ?? ''); ?>"/>

<script>
    $(document).ready(function () {
        $('input[name=mst_type\\[\\]]').change(function () {
            var check = $(this).is(':checked');
            if (check) {
                $(".sor").html('Sorry, your search for business type did not match');
                $(".sug").html("<b>Suggestions:</b><ul><li>Check other business type to filter</li><li>Check one by one type</li></ul>");
            }
        });
    });
    $(document).on('click', '.ajax', function () {
        $.colorbox({ href: $(this).attr('href'), open: true, iframe: true, width: '750px', height: '600px' });
        return false;
    });
</script>
<script>
    jQuery(document).ready(function ($) {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>    

<!-- WhatsApp RFQ System -->
<div id="waModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; width:400px; margin:100px auto; padding:25px; border-radius:10px;">
        <span onclick="document.getElementById('waModal').style.display='none'" style="float:left; cursor:pointer;">✖</span>
        <h3 style="color:#25D366;">طلب سعر واتساب</h3>
        <form id="waForm">
            <input type="hidden" id="wa_pid">
            <input type="hidden" id="wa_pname">
            <p><label>الكمية من</label> <input type="number" id="wa_qty_from" required style="width:100%; padding:8px;"></p>
            <p><label>إلى</label> <input type="number" id="wa_qty_to" required style="width:100%; padding:8px;"></p>
            <p><label>التفاصيل</label> <textarea id="wa_details" rows="4" style="width:100%; padding:8px;"></textarea></p>
            <button type="submit" style="background:#25D366; color:#fff; border:none; padding:10px 20px; width:100%; border-radius:5px;">إرسال</button>
        </form>
    </div>
</div>

<script>
function openWaRfq(pid, pname) {
    document.getElementById('wa_pid').value = pid;
    document.getElementById('wa_pname').value = pname;
    document.getElementById('waModal').style.display = 'block';
}
document.getElementById('waForm').onsubmit = async function(e) {
    e.preventDefault();
    let form = new FormData();
    form.append('product_id', document.getElementById('wa_pid').value);
    form.append('product_name', document.getElementById('wa_pname').value);
    form.append('qty_from', document.getElementById('wa_qty_from').value);
    form.append('qty_to', document.getElementById('wa_qty_to').value);
    form.append('requirement_details', document.getElementById('wa_details').value);
    
    let res = await fetch('/whatsapp_rfq_handler.php', {method:'POST', body:form});
    let data = await res.json();
    if(data.success) {
        alert('✅ Your RFQ has been noted, suppliers will contact you soon.');
        window.open(data.whatsapp_url, '_blank');
        document.getElementById('waModal').style.display = 'none';
        document.getElementById('waForm').reset();
    } else {
        alert('❌ ' + data.error);
    }
};
</script>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/whatsapp_popup_code.php'; ?>
