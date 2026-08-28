<?php
ini_set('default_socket_timeout', 300);
$totalpage = 50; // webxtor


if (!function_exists('_mq')) {
    function _mq(string $sql) {
        global $con;
        $result = $con->query($sql);
        if ($result === false) {
       
            return false;
        }
        return $result;
    }
}

/**
 * Execute a query; die with the error message on failure.
 * Mirrors  mysql_query($sql) or die(mysql_error()).
 */
if (!function_exists('_mqd')) {
    function _mqd(string $sql): mysqli_result|bool {
        global $con;
        $result = $con->query($sql);
        if ($result === false) {
            die(htmlspecialchars($con->error));
        }
        return $result;
    }
}

/** Mirrors mysql_num_rows() – safe for false/null inputs. */
if (!function_exists('_nr')) {
    function _nr(mysqli_result|bool|null $res): int {
        if (!$res instanceof mysqli_result) return 0;
        return $res->num_rows;
    }
}

/** Mirrors mysql_fetch_object(). */
if (!function_exists('_fo')) {
    function _fo(mysqli_result|bool|null $res): object|false|null {
        if (!$res instanceof mysqli_result) return false;
        return $res->fetch_object();
    }
}

/** Mirrors mysql_fetch_array($res, MYSQL_ASSOC). */
if (!function_exists('_fa')) {
    function _fa(mysqli_result|bool|null $res): array|false|null {
        if (!$res instanceof mysqli_result) return false;
        return $res->fetch_assoc();
    }
}

/** Mirrors mysql_fetch_array($res) – returns numeric+assoc mixed array. */
if (!function_exists('_fab')) {
    function _fab(mysqli_result|bool|null $res): array|false|null {
        if (!$res instanceof mysqli_result) return false;
        return $res->fetch_array(MYSQLI_BOTH);
    }
}

/** Mirrors mysql_real_escape_string(). */
if (!function_exists('_esc')) {
    function _esc(string $s): string {
        global $con;
        return $con->real_escape_string($s);
    }
}

/**
 * Safe currency-symbol helper.
 * The original used  new NumberFormatter(…)  which (a) requires the intl
 * extension and (b) called header() inside output – both crash on PHP 8.3.
 * We return the raw ISO code as a fallback when intl is absent.
 */
if (!function_exists('_currency_symbol')) {
    function _currency_symbol(string $isoCode): string {
        if (!$isoCode) return '';
        if (extension_loaded('intl')) {
            $fmt = new NumberFormatter('en-US@currency=' . $isoCode, NumberFormatter::CURRENCY);
            return (string) $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
        }
        return $isoCode . ' '; // graceful fallback
    }
}

/* =========================================================
 * highlight() helper (unchanged logic, kept here for
 * self-containment; safe to remove if defined elsewhere).
 * ========================================================= */
if (!function_exists('highlight')) {
    function highlight(string $content, string $word): string {
        $replace = '<span style="color: #f26a22;">' . $word . '</span>';
        $word    = str_replace('+', ' ', $word);
        $pattern = preg_quote($word, '/');
        return preg_replace("/($pattern)/i", '<span style="color: #f26a22;">$1</span>', $content);
    }
}

/* =========================================================
 *  CSS / JS  (100 % identical to original)
 * ========================================================= */
?>
<script src="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/js/jquery.colorbox.js"></script>
<link href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/css/colorbox.css" type="text/css" rel="stylesheet">
<style>
    #mask {
        position:absolute;
        left:0;
        top:0;
        z-index:9000;
        background-color:#000;
        display:none;
    }
    #boxes .window {
        position:absolute;
        left:0;
        top:0;
        width:440px;
        height:200px;
        display:none;
        z-index:9999;
        padding:20px;
        border-radius: 15px;
        text-align: center;
    }
    #boxes #dialog {
        width:450px;
        height:auto;
        padding:10px;
        background-color:#ffffff;
    }
    .maintext{
        text-align: center;
        text-decoration: none;
    }
    .modal.in .modal-dialog {
        transform: translate3d(0px, 0px, 0px);
    }
    .modal-lg{ width:38% !important;}
    h3 { font-weight:bold !important; margin-bottom:0px !important; margin-top:0px !important; font-size:20px !important;}
    .btn-warning {
        background-color: #f26a22;
    }
    .email-close {
        background: #FF0000 none repeat scroll 0 0;
        border: 2px solid #FFF;
        border-radius: 50%;
        box-shadow: 0 0 4px 1px rgb(0, 0, 0);
        cursor: pointer;
        font-size: 18px;
        height: 30px;
        position: absolute;
        right: -4px;
        text-align: center;
        top: 44px;
        width: 30px;
        z-index: 999;
        color:#FFFFFF;
        font-weight:bold;
    }
    .zoomthis:hover img {
        -moz-transform: scale(1.2);
        -webkit-transform: scale(1.2);
        transform: scale(1.2);
    }
    .zoomthis{
        overflow: hidden;
    }
    .zoomthis:hover ~ .zk {
        display : none;
    }
    .zoomthis:hover ~ .ribbon {
        display : none;
    }
    @media (max-width: 1300px) and (min-width: 1200px){
        .height44 td {padding-left: 3px !important;padding-right: 3px !important;padding-top: 0px !important;padding-bottom: 0 !important;position: relative;top: -8px;}
        .webcast-new-table .grid_mobile img {
            vertical-align: bottom;
        }
        .webcast-new-table .grid_mobile .txt-black {
            margin-left: -6px;
        }
    }
</style>
<script type="text/javascript">
    function openInNewTab(url) {
        var win = window.open(url, '_blank');
    }
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
            while (c.charAt(0) == ' ')
                c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0)
                return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
    function showfavorite(userid, product_id) {
        $.ajax({
            type: 'POST',
            url: "favourite_add.php",
            data: {"pro_id": product_id},
            success: function(response) {
                if (response == true) {
                    alert('Added to favorite');
                }
            }
        });
    }
    function isInArray(value, array) {
        var strarray = array.split(',');
        var isValue = false;
        for (var i = 0; i < strarray.length; i++) {
            if (strarray[i] == value) {
                isValue = true;
            } else {
                isValue = false;
            }
        }
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
                if (strarray.length > 10) {
                    alert("You can add only 10 products to compare list.");
                    window.location.href = "https://arab-mart.com/compare.php";
                } else {
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
        if (existingids == '') {
            createCookie("productids", product_id);
            window.location = "compare.php";
        } else {
            window.location = "compare.php";
        }
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
                $('.email-close').click(function () {
                    $('#mask').hide();
                    $('.window').hide();
                    limit = 150;
                });
            }
        }
        if (keyrcType == 'Products') {
            var idleInterval = setInterval(timerIncrement, 1000);
        }
        $(this).mousemove(function (e) { idleTime = 0; });
        $(this).keypress(function (e) { idleTime = 0; });
        $(this).click(function () { idleTime = 0; });
        $('.window .close').click(function (e) {
            e.preventDefault();
            $('#mask').hide();
            $('.window').hide();
        });
        $('#mask').click(function () {
            $(this).hide();
            $('.window').hide();
        });
        $(document).on('click', '#table-input1', function () {
            $("#sideAdTable1").hide();
            $("body").click(function () {
                var tabvin = document.getElementById("table-input1").value;
                if (tabvin == '') {
                    $("#sideAdTable1").show();
                }
            });
        });
        $(document).on('click', '#getInstaQuote', function () {
            $("#sideAdTable1").hide();
        });
    });
</script>
<input type="hidden" value="<?= htmlspecialchars((string)($_GET['keywords'] ?? '')) ?>" id="serachWallkeyword">
<?php

/* =========================================================
 *  VARIABLE SETUP  (unchanged logic)
 * ========================================================= */
$uid = $_SESSION['uid_indm'] ?? '';

// Location-based SQL conditions (unchanged SQL strings)
if (isset($_COOKIE['loc_id'])) {
    $loc = _esc($_COOKIE['loc_id']);
    $sql_pd_ck  = " and (
    (pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='{$loc}'))
    or
    (pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='{$loc}'))
    or
    (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='{$loc}'))))";

    $sql_br_ck  = " and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='{$loc}'))
    or
    (br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='{$loc}'))
    or
    (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='{$loc}'))))";

    $sql_so_ck  = " and (
    (so_preferred_buyer_location='domestic' and so_usr_id in(select distinct usr_id from user where country='{$loc}'))
    or
    (so_preferred_buyer_location='any' and so_usr_id in(select distinct usr_id from user where country='{$loc}'))
    or
    (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in(select ct_id from city where ct_cn_id='{$loc}'))))";

    $sql_tnd_ck = " and ((tnd_preferred_location='domestic' and tnd_usr_id in(select distinct usr_id from user where country='{$loc}'))
    or
    (tnd_preferred_location='any' and tnd_usr_id in(select distinct usr_id from user where country='{$loc}'))
    or
    (tnd_preferred_location='my_city' and tnd_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='{$loc}'))))";

    $sql_auc_ck = " and ((auc_preferred_location='domestic' and auc_usr_id in(select distinct usr_id from user where country='{$loc}'))
    or
    (auc_preferred_location='any' and auc_usr_id in(select distinct usr_id from user where country='{$loc}'))
    or
    (auc_preferred_location='my_city' and auc_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='{$loc}'))))";
} else {
    $geo0        = isset($location_geo_country[0]) ? _esc($location_geo_country[0]) : '';
    $sql_pd_ck  = " and (
    (pd_preferred_buyer_location='any')
    or
    (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='{$geo0}')))
    )";
    $sql_br_ck  = " and (
    (br_preferred_supplier_location='any')
    or
    (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='{$geo0}')))
    )";
    $sql_so_ck  = " and (
    (so_preferred_buyer_location='any')
    or
    (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='{$geo0}')))
    )";
    $sql_tnd_ck = " and (
    (tnd_preferred_location='any')
    or
    (tnd_preferred_location='abroad' and tnd_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='{$geo0}')))
    )";
    $sql_auc_ck = " and (
    (auc_preferred_location='any')
    or
    (auc_preferred_location='abroad' and auc_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='{$geo0}')))
    )";
}

$pageno  = isset($_GET['pageno']) ? (int)$_GET['pageno'] : 1;
$rctyp   = addslashes(trim((string)($_GET['rctyp'] ?? '')));

// Strip surrounding quotes from keywords (original logic preserved)
$rawKw = (string)($_GET['keywords'] ?? '');
if (substr($rawKw, 0, 1) === '"') {
    $keywords = substr(substr(trim($rawKw), 1), 0, strlen(substr(trim($rawKw), 1)) - 1);
} else {
    $keywords = trim($rawKw);
}

/* =========================================================
 *  QUERY BUILDING  (identical SQL, only execution changed)
 * ========================================================= */

if ($rctyp === 'Products') {

    if ($keywords === '') { $keywords = 'all'; }

    /* -- alert-category tracking (PHP 8.3 safe) -- */
    $key = str_replace('+', ' ', $_GET['keywords'] ?? '');
    if (!empty($_SESSION['uid_indm'])) {
        $sql_key    = "select * from products join product_category_arabyos on product_category_arabyos.pc_id=products.pd_subcat_id join `business_profile` on business_profile.bnsprof_uid = products.pd_uid where (pd_title like '%{$key}%' or `bnsprof_compname` LIKE '%{$key}%') and pc_status='1'";
        $query_key  = _mq($sql_key);
        $row_key    = _fo($query_key);
        $key_cat_id = '';
        if (_nr($query_key) > 0) {
            $key_cat_id = $row_key->pc_id;
        } else {
            $kwClean = _esc(str_replace(['+', '%20'], [' ', ' '], $_GET['keywords'] ?? ''));
            $sql_sq  = "SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%{$kwClean}%' AND pc.pc_parent_id!='0' and pc.pc_status='1'";
            $res_sq  = _mq($sql_sq);
            $fr      = _fo($res_sq);
            if (_nr($res_sq) > 0) {
                $sub_id = $fr->pc_id;
                $res_sq1 = _mq("SELECT * FROM product_category_arabyos WHERE pc_parent_id='{$sub_id}'");
                $fr1     = _fo($res_sq1);
                $key_cat_id = (_nr($res_sq1) > 0) ? $fr1->pc_id : $fr->pc_id;
            }
        }
        if ($key_cat_id !== '') {
            $esc_uid = _esc($_SESSION['uid_indm']);
            $esc_kid = _esc($key_cat_id);
            $r2 = _mq("select * from selloffer_alert_category where sac_pc_id='{$esc_kid}' AND sac_usr_id='{$esc_uid}'");
            if (_nr($r2) == 0) {
                _mq("insert into selloffer_alert_category set sac_usr_id='{$esc_uid}', sac_pc_id='{$esc_kid}', sac_updated_date=now()");
            }
        }
    }

    /* -- pagination / count queries -- */
    $Col = 'products.pd_uid,business_profile.bnsprof_state,measurement_unit_arabyos.*';

    $stateid = '';
    if (count((array)($_POST['state_id'] ?? [])) > 1) {
        foreach ($_POST['state_id'] as $v) { $stateid .= _esc($v) . ','; }
        $stateid = rtrim($stateid, ',');
    } else {
        $stateid = isset($_POST['state_id'][0]) ? _esc($_POST['state_id'][0]) : '';
    }

    if (isset($_GET['exmatch'])) {
        $kwEsc = _esc($keywords);
        $sqltk = isset($_POST['state_id'])
            ? "select {$Col} from products,measurement_unit_arabyos,business_profile where bnsprof_uid=pd_uid and ((pd_title LIKE '%{$kwEsc}%') or (bnsprof_compname LIKE '%{$kwEsc}%')) {$sql_pd_ck} and bnsprof_state in ({$stateid}) and pd_status='1' GROUP BY pd_id order by pd_title asc limit 0,6"
            : "select {$Col} from products,measurement_unit_arabyos,business_profile where bnsprof_uid=pd_uid and ((pd_title LIKE '%{$kwEsc}%') or (bnsprof_compname LIKE '%{$kwEsc}%')){$sql_pd_ck} and pd_status='1' GROUP BY pd_id order by pd_title asc limit 0,6";
    } else {
        $keywords_string     = generateProdSearchString($keywords);
        $keywords_string_pro_sup = generateProdSearchString_pro_sup($keywords);
        $sqltk = isset($_POST['state_id'])
            ? "select {$Col} from products,measurement_unit_arabyos,business_profile where bnsprof_uid=pd_uid and ((pd_title LIKE {$keywords_string}) or (bnsprof_compname LIKE {$keywords_string_pro_sup})) {$sql_pd_ck} and pd_status='1' and bnsprof_state in ({$stateid}) GROUP BY pd_id order by pd_title asc limit 0,6"
            : "select {$Col} from measurement_unit_arabyos,products,business_profile where bnsprof_uid=pd_uid and ((pd_title LIKE {$keywords_string}) or (bnsprof_compname LIKE {$keywords_string_pro_sup})) {$sql_pd_ck} and pd_status='1' GROUP BY pd_id order by pd_title asc limit 0,6";
    }

    $restk     = _mq($sqltk);
    $totitems  = _nr($restk);
    $limits    = 6;
    $total_pages  = (int)ceil($totitems / $limits);
    $start_limit  = $limits * ($pageno - 1);

    if (isset($_GET['exmatch'])) {
        $kwEsc = _esc($keywords);
        $sqlk = isset($_POST['state_id'])
            ? "select {$Col} from products,measurement_unit_arabyos,business_profile where bnsprof_uid=pd_uid and (pd_title LIKE '%{$kwEsc}%' or bnsprof_compname LIKE '%{$kwEsc}%') {$sql_pd_ck} and pd_status='1' and bnsprof_state in ({$stateid}) GROUP BY pd_id order by pd_title asc limit {$start_limit},{$limits}"
            : "select {$Col} from products,measurement_unit_arabyos,business_profile where bnsprof_uid=pd_uid and (pd_title LIKE '%{$kwEsc}%' or bnsprof_compname LIKE '%{$kwEsc}%') {$sql_pd_ck} and pd_status='1' GROUP BY pd_id order by pd_title asc limit {$start_limit},{$limits}";
    } else {
        $keywords_string = generateProdSearchString($keywords);
        $sqlk = isset($_POST['state_id'])
            ? "select {$Col} from products,measurement_unit_arabyos,business_profile where bnsprof_uid=pd_uid and (pd_title LIKE {$keywords_string} or bnsprof_compname LIKE '%{$keywords_string}%') {$sql_pd_ck} and pd_status='1' and bnsprof_state in ({$stateid}) GROUP BY pd_id order by pd_title asc limit {$start_limit},{$limits}"
            : "select {$Col} from products,measurement_unit_arabyos,business_profile where bnsprof_uid=pd_uid and (pd_title LIKE {$keywords_string} or bnsprof_compname LIKE '%{$keywords_string}%') {$sql_pd_ck} and pd_status='1' GROUP BY pd_id order by pd_title asc limit {$start_limit},{$limits}";
    }
    $resk = _mq($sqlk);

} elseif ($rctyp === 'Suppliers') {

    if (isset($_COOKIE['loc_id'])) {
        $loc    = _esc($_COOKIE['loc_id']);
        $kwEsc  = _esc($keywords);
        if (isset($_GET['exmatch'])) {
            $sqltk = "SELECT * FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id  WHERE (bnsprof_compname LIKE '%{$kwEsc}%') AND ((pd_preferred_buyer_location='domestic' AND user.country='{$loc}') OR (pd_preferred_buyer_location='any' AND user.country='{$loc}') OR (pd_preferred_buyer_location='my_city' AND user.country='{$loc}'))  AND pm.expiry_date > " . time() . "   and pd_status='1' GROUP BY products.pd_id ORDER BY business_profile.bnsprof_compname DESC";
        } else {
            $keywords_string = generateSupplierSearchString($keywords);
            $sqltk = "SELECT * FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id WHERE (bnsprof_compname LIKE '%{$kwEsc}%') AND ((pd_preferred_buyer_location='domestic' AND user.country='{$loc}') OR (pd_preferred_buyer_location='any' AND user.country='{$loc}') OR (pd_preferred_buyer_location='my_city' AND user.country='{$loc}'))  AND pm.expiry_date > " . time() . "   and pd_status='1' GROUP BY products.pd_id ORDER BY business_profile.bnsprof_compname DESC";
        }
        $supptotalpage = 50;
        $suppstartpage = 0;
        if (($_GET['page'] ?? 0) > 1) {
            $supplimit   = (($_GET['page'] - 1)) * $supptotalpage;
            $suppsetLimit = " LIMIT {$supplimit},{$supptotalpage}";
        } else {
            $supplimit   = $suppstartpage;
            $suppsetLimit = " LIMIT {$supplimit},{$supptotalpage}";
        }
        $restk    = _mq($sqltk);
        $totitems = _nr($restk);
        $limits   = 6;
        $total_pages  = (int)ceil($totitems / $limits);
        $start_limit  = $limits * ($pageno - 1);

        $keywords_string = generateSupplierSearchString($keywords);
        if (isset($_GET['exmatch'])) {
            $sqlk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id  WHERE (business_profile.bnsprof_compname LIKE {$keywords_string}) AND ((products.pd_preferred_buyer_location='domestic' AND user.country='{$loc}') OR (products.pd_preferred_buyer_location='any' AND user.country='{$loc}') OR (products.pd_preferred_buyer_location='my_city' AND user.country='{$loc}'))  AND pm.expiry_date > " . time() . "  and pd_status='1' GROUP BY products.pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC {$suppsetLimit}";
        } else {
            $sqlk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id WHERE (business_profile.bnsprof_compname LIKE {$keywords_string}) AND ((products.pd_preferred_buyer_location='domestic' AND user.country='{$loc}') OR (products.pd_preferred_buyer_location='any' AND user.country='{$loc}') OR (products.pd_preferred_buyer_location='my_city' AND user.country='{$loc}'))  AND pm.expiry_date > " . time() . "  and pd_status='1' GROUP BY products.pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC {$suppsetLimit}";
        }
        $resk = _mq($sqlk);

    } else {
        // No cookie
        $kwEsc  = _esc($keywords);
        $supptotalpage = 50;
        $suppstartpage = 0;
        if (($_GET['page'] ?? 0) > 1) {
            $supplimit    = (($_GET['page'] - 1)) * $supptotalpage;
            $suppsetLimit = " LIMIT {$supplimit},{$supptotalpage}";
        } else {
            $supplimit    = $suppstartpage;
            $suppsetLimit = " LIMIT {$supplimit},{$supptotalpage}";
        }
        $keywords_string = generateSupplierSearchString($keywords);
        $countryid       = $_POST['country_id'] ?? '';
        $cntryval1 = ''; $cntryval2 = '';

        if (isset($_GET['exmatch'])) {
            $sqltk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id WHERE (business_profile.bnsprof_compname LIKE {$keywords_string}) AND ((products.pd_preferred_buyer_location='domestic') OR (products.pd_preferred_buyer_location='any') OR (products.pd_preferred_buyer_location='my_city')) AND pm.expiry_date > " . time() . " and pd_status='1' GROUP BY products.pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC";
        } elseif (isset($_POST['country_id'])) {
            $p = 1;
            foreach ($countryid as $v) {
                $ve = _esc($v);
                $cntryval2 .= ($p === 1) ? " and (country.cn_name = '{$ve}'" : " or country.cn_name = '{$ve}'";
                $p++;
            }
            $sqltk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id WHERE (business_profile.bnsprof_compname LIKE {$keywords_string}) {$cntryval2}) AND pm.expiry_date > " . time() . " and pd_status='1' GROUP BY products.pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC";
        } else {
            $sqltk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id WHERE (business_profile.bnsprof_compname LIKE {$keywords_string}) AND ((products.pd_preferred_buyer_location='domestic') OR (products.pd_preferred_buyer_location='any') OR (products.pd_preferred_buyer_location='my_city')) AND pm.expiry_date > " . time() . " and pd_status='1' GROUP BY products.pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC";
        }

        $restk    = _mq($sqltk);
        $totitems = _nr($restk);
        $limits   = 6;
        $total_pages  = (int)ceil($totitems / $limits);
        $start_limit  = $limits * ($pageno - 1);

        if (isset($_GET['exmatch'])) {
            $sqlk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id WHERE (bnsprof_compname LIKE {$keywords_string}) AND ((pd_preferred_buyer_location='domestic') OR (pd_preferred_buyer_location='any') OR (pd_preferred_buyer_location='my_city')) AND pm.expiry_date > " . time() . " and pd_status='1' GROUP BY pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC {$suppsetLimit}";
        } elseif (isset($_POST['country_id'])) {
            $p = 1;
            foreach ($countryid as $v) {
                $ve = _esc($v);
                $cntryval1 .= ($p === 1) ? " and (country.cn_name = '{$ve}'" : " or country.cn_name = '{$ve}'";
                $p++;
            }
            $sqlk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id WHERE (bnsprof_compname LIKE {$keywords_string}) {$cntryval1}) AND pm.expiry_date > " . time() . " and pd_status='1' GROUP BY pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC {$suppsetLimit}";
        } else {
            $sqlk = "SELECT *, MATCH (bnsprof_compname) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance FROM products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid INNER JOIN user on user.usr_id = products.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON business_profile.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id =business_profile.bnsprof_id WHERE (bnsprof_compname LIKE {$keywords_string}) AND ((pd_preferred_buyer_location='domestic') OR (pd_preferred_buyer_location='any') OR (pd_preferred_buyer_location='my_city')) AND pm.expiry_date > " . time() . " and pd_status='1' GROUP BY pd_id ORDER BY title_relevance DESC, business_profile.bnsprof_compname ASC {$suppsetLimit}";
        }
        $resk = _mq($sqlk);
    }

} elseif ($rctyp === 'buy_lead') {

    /* -- alert-category tracking -- */
    if (isset($_GET['keywords']) && ($GET['rctyp'] ?? '') === 'buy_lead') {
        $kwClean = _esc(str_replace('+', ' ', $_GET['keywords'] ?? ''));
        $sql_key = "select * from buy_requirement join product_category_arabyos on product_category_arabyos.pc_id=buy_requirement.br_pc_id where (br_pd_name like '%{$kwClean}%' OR pc_name like '%{$kwClean}%') and pc_status='1' and pc_parent_id!='0'";
        $qk      = _mq($sql_key); $rk = _fo($qk);
        $key_cat_id = (_nr($qk) > 0) ? $rk->pc_id : '';
        if ($key_cat_id === '') {
            $kwClean2 = _esc(str_replace(['+', '%20'], [' ', ' '], $_GET['keywords'] ?? ''));
            $res_sq   = _mq("SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%{$kwClean2}%' AND pc.pc_parent_id!='0' and pc.pc_status='1'");
            $fr       = _fo($res_sq);
            if (_nr($res_sq) > 0) {
                $sub_id  = _esc($fr->pc_id);
                $res_sq1 = _mq("SELECT * FROM product_category_arabyos WHERE pc_parent_id='{$sub_id}' and pc_status='1'");
                $fr1     = _fo($res_sq1);
                $key_cat_id = (_nr($res_sq1) > 0) ? $fr1->pc_id : $fr->pc_id;
            }
        }
        if ($key_cat_id !== '' && $uid !== '') {
            $esc_uid = _esc($uid);
            $esc_kid = _esc($key_cat_id);
            $rr = _mq("SELECT * FROM buylead_alert_category WHERE bac_pc_id={$esc_kid} AND bac_usr_id={$esc_uid}");
            if (_nr($rr) == 0) {
                _mq("insert into buylead_alert_category SET bac_usr_id={$esc_uid}, bac_pc_id={$esc_kid}, bac_updated_date=now()");
            }
        }
    }

    $sql_extra = '';
    $bbidd = $_GET['bbidd'] ?? '';
    if ($bbidd !== '') {
        $res_bbidd = _mq("SELECT * FROM buy_requirement WHERE br_pc_id='" . _esc($bbidd) . "'");
        $pc_id_arrNewNewAA = [];
        while ($rr = _fo($res_bbidd)) { $pc_id_arrNewNewAA[] = $rr->br_id; }
        $MasterCategoryArrayAA = implode("','", $pc_id_arrNewNewAA);
        $iCategoryMatchNee     = " and br_id IN ('{$MasterCategoryArrayAA}')";
        $kwEsc = _esc($keywords);
        $sqlk  = "select * , MATCH (br_pd_name) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance from buy_requirement,measurement_unit_arabyos where br_estimate_qty_unit=mu_id and br_approval_status = '1' and br_display_status = '1' {$iCategoryMatchNee} and br_status = '1' {$sql_br_ck} {$sql_extra} order by title_relevance desc";
    } else {
        if (isset($_GET['adv_quantity']) && $_GET['adv_quantity'] !== '' && (int)$_GET['adv_quantity'] !== 0 && isset($_GET['adv_qty_list']) && $_GET['adv_qty_list'] !== '') {
            $aq = _esc(trim($_GET['adv_quantity']));
            $al = _esc(trim($_GET['adv_qty_list']));
            $sql_extra = " and br_estimate_qty='{$aq}' and br_estimate_qty_unit='{$al}'";
        }
        if (isset($_GET['exmatch'])) {
            $kwEsc = _esc($keywords);
            $sqltk = "select * from buy_requirement,measurement_unit_arabyos where br_estimate_qty_unit=mu_id and (br_pd_name LIKE '%{$kwEsc}%' or br_requirement LIKE '%{$kwEsc}%') and br_approval_status = '1' and br_display_status = '1' {$sql_br_ck} {$sql_extra} order by br_pd_name asc";
        } else {
            $keywords_string = generateBuyleadSearchString($keywords);
            $sqltk = "select * from buy_requirement,measurement_unit_arabyos where br_estimate_qty_unit=mu_id and ({$keywords_string}) and br_approval_status = '1' and br_display_status = '1' {$sql_br_ck} {$sql_extra} order by br_pd_name asc";
        }
        $restk    = _mq($sqltk);
        $totitems = _nr($restk);
        $limits   = 6;
        $total_pages = (int)ceil($totitems / $limits);
        $start_limit = $limits * ($pageno - 1);

        if (isset($_GET['exmatch'])) {
            $kwEsc = _esc($keywords);
            $sqlk  = "select * , MATCH (br_pd_name) AGAINST ('{$kwEsc}' IN BOOLEAN MODE) AS title_relevance from buy_requirement,measurement_unit_arabyos where br_estimate_qty_unit=mu_id and (br_pd_name LIKE '{$kwEsc}' or br_requirement LIKE '{$kwEsc}') and br_approval_status = '1' and br_status = '1' and br_display_status = '1' {$sql_br_ck} {$sql_extra} order by title_relevance desc limit {$start_limit},{$limits}";
        } else {
            $kwEsc = _esc($keywords);
            $sqlk  = "select * from buy_requirement br JOIN measurement_unit_arabyos mu ON br.br_estimate_qty_unit=mu.mu_id JOIN user u ON u.usr_id = br.br_u_id LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id LEFT JOIN country c ON c.cn_id = u.country LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id WHERE br_pd_name LIKE '%{$kwEsc}%' and br_display_status = '1' and br_status='1' order by br_pd_name asc";
        }
    }
    $resk = _mq($sqlk);

} elseif ($rctyp === 'tender') {

    /* -- alert-category tracking -- */
    if (isset($_GET['keywords'])) {
        $kwT    = _esc(str_replace('+', ' ', $_GET['keywords'] ?? ''));
        $qkt    = _mq("select * from tender join product_category_arabyos on product_category_arabyos.pc_id=tender.tnd_pc_id where tnd_heading like '%{$kwT}%' and pc_status='1'");
        $rkt    = _fo($qkt);
        $key_cat_id = ($rkt) ? $rkt->pc_id : '';

        if ($key_cat_id !== '' && !empty($_SESSION['uid_indm'])) {
            $esc_uid = _esc($_SESSION['uid_indm']); $esc_kid = _esc($key_cat_id);
            $rcheck = _mq("SELECT * FROM tender_alert_category WHERE tac_pc_id={$esc_kid} AND tac_usr_id={$esc_uid}");
            if (_nr($rcheck) == 0) {
                _mq("insert into tender_alert_category SET tac_usr_id={$esc_uid}, tac_pc_id={$esc_kid}, tac_updated_date=now()");
            }
            $rcheck1 = _mq("SELECT * FROM auction_alert_category WHERE aac_pc_id={$esc_kid} AND aac_usr_id={$esc_uid}");
            if (_nr($rcheck1) == 0) {
                _mq("insert into auction_alert_category SET aac_usr_id={$esc_uid}, aac_pc_id={$esc_kid}, aac_updated_date=now()");
            }
        } else {
            // Try auction category
            $qka2 = _mq("select * from auction join product_category_arabyos on product_category_arabyos.pc_id=auction.auc_pc_id where auc_heading like '%{$kwT}%' and pc_status='1'");
            $rka2 = _fo($qka2);
            if (_nr($qka2) > 0) {
                $key_cat_id = $rka2->pc_id;
            } else {
                $kwClean2 = _esc(str_replace(['+', '%20'], [' ', ' '], $_GET['keywords'] ?? ''));
                $res_sq   = _mq("SELECT pc.* FROM product_category_arabyos pc LEFT OUTER JOIN product_category_arabyos spc ON pc.pc_id = spc.pc_parent_id WHERE pc.pc_name like '%{$kwClean2}%' AND pc.pc_parent_id!='0' and pc.pc_status='1'");
                $fr       = _fo($res_sq);
                if (_nr($res_sq) > 0) {
                    $sub_id  = _esc($fr->pc_id);
                    $res_sq1 = _mq("SELECT * FROM product_category_arabyos WHERE pc_parent_id='{$sub_id}' and pc_status='1'");
                    $fr1     = _fo($res_sq1);
                    $key_cat_id = (_nr($res_sq1) > 0) ? $fr1->pc_id : $fr->pc_id;
                }
            }
            if ($key_cat_id !== '' && !empty($uid)) {
                $esc_uid = _esc($uid); $esc_kid = _esc($key_cat_id);
                $rca = _mq("SELECT * FROM auction_alert_category WHERE aac_pc_id={$esc_kid} AND aac_usr_id={$esc_uid}");
                if (_nr($rca) == 0) {
                    _mq("insert into auction_alert_category SET aac_usr_id={$esc_uid}, aac_pc_id={$esc_kid}, aac_updated_date=now()");
                }
                $rct = _mq("SELECT * FROM tender_alert_category WHERE tac_pc_id={$esc_kid} AND tac_usr_id={$esc_uid}");
                if (_nr($rct) == 0) {
                    _mq("insert into tender_alert_category SET tac_usr_id={$esc_uid}, tac_pc_id={$esc_kid}, tac_updated_date=now()");
                }
            }
        }
    }

    $sql_extra = '';
    if (isset($_COOKIE['loc_id'])) {
        $loc = _esc($_COOKIE['loc_id']);
        $auctionCondition = " AND ((auc_preferred_location='domestic' AND auc_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='{$loc}')) OR (auc_preferred_location='any' AND auc_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='{$loc}')) OR (auc_preferred_location='my_city' AND auc_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='{$loc}'))))";
        $tenderCondition  = " AND ((tnd_preferred_location='domestic' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='{$loc}')) OR (tnd_preferred_location='any' AND tnd_usr_id in(SELECT DISTINCT usr_id FROM user WHERE country='{$loc}')) OR (tnd_preferred_location='my_city' AND tnd_usr_id in(SELECT DISTINCT bnsprof_uid FROM business_profile WHERE bnsprof_city in (SELECT ct_id FROM city WHERE ct_cn_id='{$loc}'))))";
    } else {
        $auctionCondition = " AND ((auc_preferred_location='domestic') OR (auc_preferred_location='any') OR (auc_preferred_location='my_city'))";
        $tenderCondition  = " AND ((tnd_preferred_location='domestic') OR (tnd_preferred_location='any') OR (tnd_preferred_location='my_city'))";
    }

    $ttidd = $_GET['ttidd'] ?? '';
    $ccidd = $_GET['ccidd'] ?? '';
    if ($ttidd !== '') {
        $res_tt = _mq("SELECT * FROM tender WHERE tnd_pc_id='" . _esc($ttidd) . "'");
        $arr_tt = [];
        while ($rr = _fo($res_tt)) { $arr_tt[] = $rr->tnd_id; }
        $MCArr       = implode("','", $arr_tt);
        $iCatMatch   = " and tnd_id IN ('{$MCArr}')";
        $tendsqlk    = "select * from tender,product_category_arabyos,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_approval_status='1' and TO_DAYS(tnd_due_date)>=TO_DAYS(now()) and tnd_status='1' {$iCatMatch} order by tnd_id";
        $aucsqlk     = "select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_approval_status='1' and TO_DAYS(auc_due_date)>=TO_DAYS(now()) and auc_status='1' order by auc_id";
    } elseif ($ccidd !== '') {
        $res_cc = _mq("SELECT * FROM auction WHERE auc_pc_id='" . _esc($ccidd) . "'");
        $arr_cc = [];
        while ($rr = _fo($res_cc)) { $arr_cc[] = $rr->auc_id; }
        $MCArr   = implode("','", $arr_cc);
        $iCatMatch = " and auc_id IN ('{$MCArr}')";
        $aucsqlk   = "select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_approval_status='1' and TO_DAYS(auc_due_date)>=TO_DAYS(now()) and auc_status='1' {$iCatMatch} order by auc_id";
        $tendsqlk  = "select * from tender,product_category_arabyos,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_approval_status='1' and TO_DAYS(tnd_due_date)>=TO_DAYS(now()) and tnd_status='1' order by tnd_id";
    } else {
        $today = date('Y-m-d');
        if (isset($_GET['exmatch'])) {
            $kwEsc = _esc($keywords);
            $tendsqltk = "SELECT * FROM tender, product_category_arabyos, user, business_profile WHERE tnd_pc_id=pc_id AND tnd_usr_id=usr_id AND usr_id=bnsprof_uid AND tnd_approval_status='1' AND TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) and tnd_due_date>='{$today}' AND tnd_status='1' AND tnd_heading='%{$kwEsc}%' {$tenderCondition} ORDER BY tnd_heading ASC";
            $aucsqltk  = "SELECT * FROM auction, product_category_arabyos, user, business_profile WHERE auc_pc_id=pc_id AND auc_usr_id=usr_id AND usr_id=bnsprof_uid AND auc_approval_status='1' AND TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) and auc_due_date>='{$today}' AND auc_status='1' AND auc_heading='%{$kwEsc}%' {$auctionCondition} ORDER BY auc_id DESC";
        } else {
            $tender_keywords_string  = generateTenderSearchString($keywords);
            $auction_keywords_string = generateAuctionSearchString($keywords);
            $tendsqltk = "SELECT * FROM tender,product_category_arabyos,user,business_profile WHERE tnd_pc_id=pc_id AND tnd_usr_id=usr_id AND usr_id=bnsprof_uid AND ({$tender_keywords_string}) AND tnd_approval_status='1' AND TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) and tnd_due_date>='{$today}' AND tnd_approval_status='1'{$tenderCondition}";
            $aucsqltk  = "SELECT * FROM auction,product_category_arabyos,user,business_profile WHERE auc_pc_id=pc_id AND auc_usr_id=usr_id AND usr_id=bnsprof_uid AND ({$auction_keywords_string}) and auc_due_date>='{$today}' AND auc_approval_status='1' AND TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) AND auc_approval_status='1'{$auctionCondition}";
        }
        $tend_restk  = _mq($tendsqltk);
        $auction_restk = _mq($aucsqltk);
        $totitems    = _nr($tend_restk);
        $limits      = 6;
        $total_pages = (int)ceil($totitems / $limits);
        $start_limit = $limits * ($pageno - 1);

        if (isset($_GET['exmatch'])) {
            $kwEsc = _esc($keywords);
            $tendsqlk = "SELECT * FROM tender,product_category_arabyos,user,business_profile WHERE tnd_pc_id=pc_id AND tnd_usr_id=usr_id AND usr_id=bnsprof_uid AND tnd_heading LIKE '{$kwEsc}' OR tnd_details LIKE '{$kwEsc}') and tnd_due_date>='{$today}' AND tnd_approval_status='1' AND TO_DAYS(tnd_docSaleEnd_date)>=TO_DAYS(now()) {$tenderCondition} ORDER BY tnd_heading ASC";
            $aucsqlk  = "SELECT * FROM auction,product_category_arabyos,user,business_profile WHERE auc_pc_id=pc_id AND auc_usr_id=usr_id AND usr_id=bnsprof_uid AND (auc_heading LIKE '{$kwEsc}' or auc_details LIKE '{$kwEsc}') and auc_due_date>='{$today}' AND auc_approval_status='1' AND TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) {$auctionCondition} ORDER BY auc_id DESC";
        } else {
            $tender_keywords_string  = generateTenderSearchString($keywords);
            $auction_keywords_string = generateAuctionSearchString($keywords);
            $tendsqlk = "SELECT * FROM tender,product_category_arabyos,user,business_profile WHERE tnd_pc_id=pc_id AND tnd_usr_id=usr_id AND usr_id=bnsprof_uid AND ({$tender_keywords_string}) and tnd_due_date>='{$today}' AND tnd_approval_status='1'{$tenderCondition} ORDER BY tnd_heading ASC";
            $aucsqlk  = "SELECT * FROM auction,product_category_arabyos,user,business_profile WHERE auc_pc_id=pc_id AND auc_usr_id=usr_id AND usr_id=bnsprof_uid AND ({$auction_keywords_string}) and auc_due_date>='{$today}' AND auc_approval_status='1'{$auctionCondition} ORDER BY auc_id DESC";
        }
    }
    $tender_resk  = _mq($tendsqlk);
    $auction_resk = _mq($aucsqlk);

} elseif ($rctyp === 'auction') {

    /* -- alert-category tracking -- */
    if (isset($_GET['keywords']) && !empty($uid)) {
        $kwT  = _esc(str_replace('+', ' ', $_GET['keywords'] ?? ''));
        $qka  = _mq("select * from auction join product_category_arabyos on product_category_arabyos.pc_id=auction.auc_pc_id where auc_heading = '{$kwT}' and pc_status='1'");
        $rka  = _fo($qka);
        $key_cat_id = ($rka) ? $rka->pc_id : '';
        if ($key_cat_id !== '') {
            $esc_uid = _esc($uid); $esc_kid = _esc($key_cat_id);
            $rr = _mq("SELECT * FROM auction_alert_category WHERE aac_pc_id={$esc_kid} AND aac_usr_id={$esc_uid}");
            if (_nr($rr) == 0) {
                _mq("insert into auction_alert_category SET aac_usr_id={$esc_uid}, aac_pc_id={$esc_kid}, aac_updated_date=now()");
            }
        }
    }

    $sql_extra = '';
    if (isset($_GET['exmatch'])) {
        $kwEsc = _esc($keywords);
        $sqltk = "select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_approval_status='1' and TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) {$sql_auc_ck} and auc_status='1' and auc_heading='%{$kwEsc}%' order by auc_id desc";
    } else {
        $keywords_string = generateAuctionSearchString($keywords);
        $sqltk = "select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and ({$keywords_string}) and auc_approval_status='1' and TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) {$sql_auc_ck} {$sql_extra} order by auc_id desc";
    }
    $restk    = _mq($sqltk);
    $totitems = _nr($restk);
    $limits   = 6;
    $total_pages = (int)ceil($totitems / $limits);
    $start_limit = $limits * ($pageno - 1);
    if (isset($_GET['exmatch'])) {
        $kwEsc = _esc($keywords);
        $sqlk  = "select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_heading LIKE '{$kwEsc}' or auc_details LIKE '{$kwEsc}' and auc_approval_status='1' and TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) {$sql_auc_ck} {$sql_extra} order by auc_id desc limit {$start_limit},{$limits}";
    } else {
        $keywords_string = generateAuctionSearchString($keywords);
        $sqlk  = "select * from auction,product_category_arabyos,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and ({$keywords_string}) and auc_approval_status='1' and TO_DAYS(auc_docSaleEnd_date)>=TO_DAYS(now()) {$sql_auc_ck} {$sql_extra} order by auc_id desc limit {$start_limit},{$limits}";
    }
    $resk = _mq($sqlk);
}

/* =========================================================
 *  HTML OUTPUT  (100 % identical to original)
 * ========================================================= */

if (($rctyp !== 'Products') && isset($_GET['rctyp'])) {
    ?>
    <div class="wl-list" id="m">
        <?php if (_nr($resk) > 0) { ?>
            <p class="flt_wd" style="float:right; color: #666666;font-family: Tahoma;font-size: 13px;padding: 0 0 3px 3px;">
                <?php
                if ($_GET['rctyp'] === 'buy_lead')       echo $totitems . ' Buy Leads';
                elseif ($_GET['rctyp'] === 'Products')   echo $totitems . ' ' . $_GET['rctyp'];
                elseif ($_GET['rctyp'] === 'tender')     echo $totitems . ' Tenders';
                elseif ($_GET['rctyp'] === 'auction')    echo $totitems . ' Auctions';
                else                                     echo $totitems . ' Suppliers';
                ?> available
            </p>
        <?php } ?>
        </h1></div> <p style="clear:both"></p>
    <?php

    /* ---------- SUPPLIERS ---------- */
    if ($rctyp === 'Suppliers') {
        $suppTotalRow = _nr($resk);
        $suppRowCount = 1;
        if (_nr($resk) > 0) {
            while ($rowk = _fo($resk)) {
                $fevrow_icon = 0;
                $data = $userArrayRow_Result[$rowk->pd_uid] ?? null;
                if ($data) {
                    $get_icon = _mqd("select smembership_plan.mst_icon as sponsericon , plan_member_id.* , smembership_icon_plan.mst_icon as producticon,smembership_icon_plan.mst_name as pplan from smembership_plan,plan_member_id , smembership_icon_plan where smembership_icon_plan.mp_id =plan_member_id.p_id and smembership_plan.mp_id =plan_member_id.p_id  and plan_member_id.b_id = " . (int)$data['bnsprof_id']);
                    if (_nr($get_icon)) { $fevrow_icon = _fab($get_icon); }
                    $get_icon2 = _mqd("select icon_id, p_id from plan_member_id where b_id = " . (int)$data['bnsprof_id']);
                    $icon2     = _fab($get_icon2);
                    $get_icon1 = _mqd("select * from smembership_icon_plan where mp_id = " . (int)($icon2['icon_id'] ?? 0));
                    $icon1     = _fab($get_icon1);
                    $get_icon3 = _mqd("select * from smembership_plan where mp_id = " . (int)($icon2['p_id'] ?? 0));
                }
                $munit   = _mqd("SELECT * FROM `measurement_unit_arabyos` WHERE mu_id='" . _esc($rowk->pd_unit ?? '') . "'");
                $row_unit = _nr($munit) ? _fab($munit) : [];
                ?>
                <div class="row ar-mid-box only-sup">
                    <div class="col-lg-12 ar-box-1  margin-top-10 ">
                        <div class="row">
                            <div class="col-xs-6 col-lg-3 big-img-box box-1">
                                <header><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
                                    <?php if ($_GET['rctyp'] !== 'Suppliers') { ?>
                                    <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                        <a href="javascript:void(0)" class="product_fav_btn" data="<?= $rowk->pd_id ?>" onclick="return showfavorite(<?= (int)$_SESSION['uid_indm'] ?>,<?= (int)$rowk->pd_id ?>)" class="ar-star"><i class="fa fa-star star" style="color:<?= (in_array($rowk->pd_id, $myfev ?? [])) ? '#E48F23' : '#808080' ?>"></i> Favorite</a>
                                    <?php } else { ?>
                                        <a href="sign-in.php" class="product_fav_btn" data="<?= $rowk->pd_id ?>" class="ar-star"><i class="fa fa-star star"></i> Favorite</a>
                                    <?php } ?>
                                    <i class="fa fa-plus star"></i><a href="javascript:void(0)" class="ar-star product_compare" data-prod_img="<?= 'upload/myproduct/' . htmlspecialchars($rowk->pd_image ?? '') ?>" onclick="return showcompare(<?= (int)$rowk->pd_id ?>)" data-prod_id="<?= (int)$rowk->pd_id ?>" title="<?= htmlspecialchars($rowk->pd_title ?? '') ?>"> Compare</a><?php } ?>
                                </header>
                                <figure class="box">
                                    <?php if ($_GET['rctyp'] === 'Suppliers') { ?>
                                    <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                        <a href="javascript:void(0)" class="product_fav_btn" data="<?= $rowk->pd_id ?>" onclick="return showfavorite(<?= (int)$_SESSION['uid_indm'] ?>,<?= (int)$rowk->pd_id ?>)" class="ar-star"><i class="fa fa-star star" style="color:<?= (in_array($rowk->pd_id, $myfev ?? [])) ? '#E48F23' : '#808080' ?>"></i></a>
                                    <?php } else { ?>
                                        <a href="sign-in.php" class="product_fav_btn" data="<?= $rowk->pd_id ?>" class="ar-star"><i class="fa fa-star star"></i></a>
                                    <?php } } ?>
                                    <?php
                                    $pimg1 = explode(',', $rowk->pd_image ?? '');
                                    $zoom_image_val = 'upload/myproduct/' . $pimg1[0];
                                    ?>
                                    <div class="zoomthis">
                                        <?php if ($fevrow_icon) { ?>
                                            <div class="ribbon"><img src="./admin/images/<?= htmlspecialchars($fevrow_icon['sponsericon'] ?? '') ?>"/></div>
                                        <?php } elseif (_nr($get_icon3)) {
                                            $fevrow_icon3 = _fab($get_icon3); ?>
                                            <div class="ribbon"><img src="./admin/images/<?= htmlspecialchars($fevrow_icon3['mst_icon'] ?? '') ?>"/></div>
                                        <?php } ?>
                                        <?php if ($pimg1[0] !== '') { echo "<img src='upload/myproduct/" . htmlspecialchars($pimg1[0]) . "'>"; } else { echo "<img src='/images/noimage.jpg'>"; } ?>
                                    </div>
                                    <?php if (!empty($rowk->pd_imagelogo)) {
                                        $limg1 = explode(',', $rowk->pd_imagelogo); ?>
                                        <div class="zk" style=" border: 1px solid #267abf;height: auto; width: 100px;position: absolute;bottom: 1px;left: 1px;">
                                            <?= "<img style='width: auto; height: 50px;max-width:100%;' src='upload/myproduct/" . htmlspecialchars($limg1[0]) . "'>" ?>
                                        </div>
                                    <?php } ?>
                                </figure>
                                <center>
                                    <a onclick="zoom_image(this)" data-img="<?= htmlspecialchars($zoom_image_val) ?>" style="padding: 10px;"><i class="fa fa-search-plus"></i> Zoom </a>
                                </center>
                            </div>
                            <?php $rand = rand(1000, 9999); ?>
                            <div class="col-xs-6 col-lg-5 box-2" style="width:100%">
                                <ul>
                                    <li class="margin-bottom-10">
                                        <h4 class="txt-blue">
                                            <a class="txt-blue" target="_blank" <?php if (user_info($rowk->bnsprof_uid, 'bnsprof_compname') !== '') { ?>href="company/product-details.php?token=<?= $rand . md5($rowk->pd_id) ?>&c=<?= $rand . md5($rowk->bnsprof_id) ?>"<?php } ?>><?= ucwords($rowk->pd_title ?? '') ?></a>
                                        </h4>
                                    </li>
                                    <li><?= htmlentities(substr($rowk->pd_desc ?? '', 0, 132)) ?></li>
                                    <li class="text-right" <?php if (!empty($rowk->brand_name)) echo 'style="display: flow-root;"' ?>>
                                        <?php if (!empty($rowk->brand_name)) { ?><span style="float: left;"><strong>Brand:</strong> <?= htmlspecialchars($rowk->brand_name) ?></span><?php } ?>
                                        <a <?php if (user_info($rowk->bnsprof_uid, 'bnsprof_compname') !== '') { ?>href="company/products.php?c=<?= $rand . md5($rowk->bnsprof_id) ?>&sc=<?= rand(10000,99999) . $rowk->pd_subcat_id ?>#<?= (int)$rowk->pd_id ?>"<?php } ?>>+  More</a>
                                    </li>
                                    <li class="text-right" style="display: none;">
                                        <a <?php if (user_info($rowk->bnsprof_uid, 'bnsprof_compname') !== '') { ?>href="company/products.php?c=<?= $rand . md5($rowk->bnsprof_id) ?>&sc=<?= rand(10000,99999) . $rowk->pd_subcat_id ?>#<?= (int)$rowk->pd_id ?>"<?php } ?>>+  More</a>
                                    </li>
                                    <li> Min Order &nbsp;<big class="txt-bold txt-red"><?= (int)$rowk->pd_min_order_qty ?></big>&nbsp; <?= htmlspecialchars($row_unit['mu_name'] ?? '') ?> </li>
                                    <?php
                                    /* FIX: header() must NOT be called inside output; currency symbol fetched without header() */
                                    $symbol = _currency_symbol((string)($rowk->pd_currency ?? ''));
                                    $style_none = ((int)($rowk->pd_fob_price ?? 0) == 0) ? 'hide' : 'show';
                                    ?>
                                    <li class="<?= $style_none ?>">Price  &nbsp; <big class="txt-bold txt-red"><a style="color: #d22027" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/product-add.php"><?= $symbol . ($rowk->pd_fob_price ?? '') . '~' . $symbol . ($rowk->pd_fob_price2 ?? '') ?></a></big> &nbsp;
                                    <?php if (empty($_SESSION['uid_indm'])) { ?>
                                        <a style="float: right;position: relative;right: 20px;top: -8px;" data-enquiry="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/sign-in.php"><button type="button" class="btn border-radius-0 btn-enquiry" style="font-weight:bold;">(Get Latest Price)</button></a>
                                    <?php } else { if (($_GET['grid'] ?? '') === 'active') { ?>
                                        <a style="float: right;position: relative;right: 20px;" class="ajax" data-price="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest.php?id=<?= $rand . md5($rowk->bnsprof_id) ?>&pid=<?= (int)$rowk->pd_id ?>&keywords=<?= urlencode($_GET['keywords'] ?? '') ?>&geo=<?= htmlspecialchars($geo_loc ?? '') ?>&conty=<?= htmlspecialchars($countryyyy ?? '') ?>&search=1" class="txt-bold txt-black pull-right inquiry_but" id="btn_ajax_send<?= (int)$rowk->pd_id ?>" rel="product-send-inquiry"><button type="button" class="btn border-radius-0 btn-enquiry" style="font-weight:bold;">(Get Latest Price)</button></a>
                                    <?php } else { ?>
                                        <a style="font-weight:bold; color: black;float: right;position: relative;right: 20px;top: -8px;" class="ajax" data-price="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest_supplier.php?id=<?= $rand . md5($rowk->bnsprof_id) ?>&pid=<?= (int)$rowk->pd_id ?>&keywords=<?= urlencode($_GET['keywords'] ?? '') ?>&geo=<?= htmlspecialchars($geo_loc ?? '') ?>&conty=<?= htmlspecialchars($countryyyy ?? '') ?>&search=1" class="txt-bold txt-black pull-right inquiry_but" id="btn_ajax_send<?= (int)$rowk->pd_id ?>" rel="product-send-inquiry"><button type="button" class="btn border-radius-0 btn-enquiry" style="font-weight:bold; color: black;">(Get Latest Price)</button></a>
                                    <?php } } ?>
                                    </li>
                                    <?php if (!empty($rowk->pd_pn_capct)) { ?>
                                    <li><a class="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/product-details.php?token=<?= $rand . md5($rowk->pd_id) ?>&c=<?= $rand . md5($rowk->bnsprof_id) ?>" target="_blank">Logistics</a></li>
                                    <?php } ?>
                                    <li class="margin-top-5">
                                        <table class="table"><tr>
                                            <?php $compName = explode(' ', $data['bnsprof_compname'] ?? ''); $compnamers = implode('', $compName); ?>
                                            <td style="padding-left:0px;"><a href="/company/index.php?c=<?= $rand . md5($rowk->bnsprof_id) ?>" class="txt-blue txt-bold"><img src="images/users.png" width="25px" target="_blank"> About Us</a></td>
                                            <td class=""><a href="/company/products.php?c=<?= $rand . md5($rowk->bnsprof_id) ?>&flaag=whsuccess" class="txt-blue txt-bold"><img src="images/icon.png" width="20px"/> View Products</a></td>
                                            <?php if (!empty($rowk->pd_pdf_attach)) { ?><td><a href="//arab-mart.com/upload/productdoc/<?= htmlspecialchars($rowk->pd_pdf_attach) ?>" target="_blank"><img src="/images/pdf_icon.png" style="width: 28px;height: 28px;"> PDF</a></td><?php } ?>
                                            <td><a onclick="open_chat()"><i class="fa fa-comments"></i> Chat</a></td>
                                        </tr></table>
                                    </li>
                                    <li>
                                        <table class="table enquiry-tb margin-bottom-0">
                                            <tr class="bg-gray">
                                                <?php $Countryphone = _fab(_mqd("SELECT * FROM `country` where cn_id = " . (int)($data['country'] ?? 0))); ?>
                                                <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                                    <td class="padding-0 col-sm-6" style="vertical-align: middle;"><big class=""> &nbsp; <a href="https://wa.me/+<?= user_info($data['bnsprof_uid'], 'country_ph_code') ?><?= user_info($data['bnsprof_uid'], 'mobile1') ?>" class="txt-black txt-lg"><img src="images/mobile.png" width="25px"/> <b><?= htmlspecialchars($Countryphone['cn_ph'] ?? '') ?>-<?= user_info($data['bnsprof_uid'], 'mobile1') ?></b></a></big></td>
                                                <?php } else { ?>
                                                    <td class="padding-0 col-sm-6" style="vertical-align: middle;"><big class=""> &nbsp;<a href="https://arab-mart.com/sign-in.php#loginform">Show Number</a><img src="images/mobile.png" width="25px"/></big></td>
                                                <?php } ?>
                                                <td class="text-right padding-0 col-sm-6">
                                                    <?php if (empty($_SESSION['uid_indm'])) { ?>
                                                        <a data-enquiry="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/sign-in.php"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                                                    <?php } else { if (($_GET['grid'] ?? '') === 'active') { ?>
                                                        <a class="ajax" data-price="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest.php?id=<?= $rand . md5($rowk->bnsprof_id) ?>&pid=<?= (int)$rowk->pd_id ?>&keywords=<?= urlencode($_GET['keywords'] ?? '') ?>&geo=<?= htmlspecialchars($geo_loc ?? '') ?>&conty=<?= htmlspecialchars($countryyyy ?? '') ?>&search=1" class="txt-bold txt-black pull-right inquiry_but" id="btn_ajax_send<?= (int)$rowk->pd_id ?>" rel="product-send-inquiry"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                                                    <?php } else { ?>
                                                        <a class="ajax" data-price="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest_supplier.php?id=<?= $rand . md5($rowk->bnsprof_id) ?>&pid=<?= (int)$rowk->pd_id ?>&keywords=<?= urlencode($_GET['keywords'] ?? '') ?>&geo=<?= htmlspecialchars($geo_loc ?? '') ?>&conty=<?= htmlspecialchars($countryyyy ?? '') ?>&search=1" class="txt-bold txt-black pull-right inquiry_but" id="btn_ajax_send<?= (int)$rowk->pd_id ?>" rel="product-send-inquiry"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                                                    <?php } } ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-4 box-3">
                                <div class="ar-box-1 ar-box padding-5 margin-bottom-5 bg-gray" style="overflow-x: hidden;">
                                    <header class="sub-box">
                                        <?php if ($fevrow_icon) { ?><a href="https://www.arab-mart.com/membership_plans.php"><img src="https://www.arab-mart.com/admin/images/<?= htmlspecialchars($fevrow_icon['producticon'] ?? '') ?>" title="<?= htmlspecialchars($fevrow_icon['pplan'] ?? '') ?>" width="25px" height="25px"/></a><?php } elseif (_nr($get_icon1)) { ?><a href="https://www.arab-mart.com/membership_plans.php"><img src="./admin/images/<?= htmlspecialchars($icon1['mst_icon'] ?? '') ?>" width="25px" height="25px" title="<?= htmlspecialchars($icon1['mst_name'] ?? '') ?>"/></a><?php } ?>
                                        <b class="txt-dark-gray"><a href="/company/profile.php?c=<?= $rand . md5($rowk->bnsprof_id) ?>" class="titleLim" target="_blank" title="<?= ucfirst($rowk->bnsprof_compname ?? '') ?>"><?= ucfirst(substr($rowk->bnsprof_compname ?? '', 0, 20) . '...') ?></a></b>
                                    </header>
                                    <img src="https://www.arab-mart.com/images/country_flag/<?= htmlspecialchars($rowk->cn_flag ?? '') ?>" alt="<?= htmlspecialchars($rowk->cn_flag ?? '') ?>" style="width:21.6px;height:21.6px;"/>
                                    <b class="txt-bold" style="color:#302670; margin-left:10px;"><?php
                                        $countryId     = $rowk->cn_id ?? 0;
                                        $getCountryName = _fab(_mqd("SELECT * FROM `country` where cn_id='" . (int)$countryId . "'"));
                                        $stateId       = $rowk->ct_state ?? 0;
                                        $getStateName  = _fab(_mqd("SELECT * FROM `states` where state_id='" . (int)$stateId . "'"));
                                        $address = '';
                                        if (!empty($getCountryName['cn_name'])) $address .= $getCountryName['cn_name'] . '-';
                                        if (!empty($getStateName['state_name'])) $address .= $getStateName['state_name'] . '-';
                                        if (!empty($data['ct_name'])) $address .= $rowk->ct_name ?? '';
                                        echo $address !== '' ? htmlspecialchars($address) : 'Not available';
                                    ?></b>
                                    <table class="table margin-top-5">
                                        <tr>
                                            <td class="txt-light-gray padding-0"> Business Type : </td>
                                            <?php
                                            $bnsprof_businesstype = $data['bnsprof_businesstype'] ?? '';
                                            $dataC = explode(',', $bnsprof_businesstype);
                                            $bus_type = ''; $bus_type1 = '';
                                            if ($bnsprof_businesstype !== '') {
                                                $i = 1; $j = 1;
                                                foreach ($dataC as $r) {
                                                    if ($i <= 2) { $bus_type .= ($userArrayRow_Type[$r] ?? '') . ($i < count($dataC) ? ', ' : ''); $i++; }
                                                    $bus_type1 .= ($userArrayRow_Type[$r] ?? '') . ($j < count($dataC) ? ', ' : ''); $j++;
                                                }
                                                $bus_type .= '...';
                                            } else { $bus_type = 'Not available'; }
                                            ?>
                                            <td class="padding-0 txt-bold" title="<?= htmlspecialchars($bus_type1) ?>"><?= htmlspecialchars($bus_type) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-light-gray padding-0"><a style="font-size:12px;font-weight:100;color:#8a8a8a" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/arab-mart.com/product-add.php">Trade Location:</a></td>
                                            <td class="padding-0 txt-bold"><a style="font-size:12px;color:#242424" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/arab-mart.com/product-add.php"><?php
                                                $ploc = $rowk->pd_preferred_buyer_location ?? '';
                                                if ($ploc === 'abroad')   echo 'Abroad Only';
                                                elseif ($ploc === 'any')  echo 'Abroad + Domestic';
                                                elseif ($ploc === 'domestic') echo 'Domestic Only';
                                                elseif ($ploc === 'my_city')  echo 'My City Only';
                                            ?></a></td>
                                        </tr>
                                        <tr><td class="padding-0 txt-bold"> N/A </td></tr>
                                        <tr>
                                            <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                                <td class="txt-light-gray" colspan="2"><a href="<?= htmlspecialchars($rowk->bnsprof_website_alt ?? '') ?>"><?= htmlspecialchars($rowk->bnsprof_website_alt ?? '') ?></a></td>
                                            <?php } else { ?>
                                                <td class="txt-light-gray" colspan="2"><a href="/sign-in.php#loginform">Show Website</a></td>
                                            <?php } ?>
                                            <td class="padding-0"></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="small-box hidden-xs">
                                    <table class="table margin-bottom-0"><tr>
                                        <?php
                                        $rqury   = 'select pd_id,pd_title, pd_image from products where pd_image!="" and pd_uid = ' . (int)$rowk->pd_uid . ' and pd_status != 0 and pd_id !=' . (int)($row['pd_id'] ?? 0) . ' ORDER by pd_id DESC limit 2';
                                        $rresult = _mq($rqury);
                                        $rcoun   = 1; $releted_pro = '';
                                        while ($rrow = _fab($rresult)) {
                                            $cls = ($rcoun == 1) ? 'small-box-td1' : 'text-right small-box-td2';
                                            $pad = ($rcoun == 1) ? 'padding-right:3px;' : 'padding-left:3px;';
                                            $releted_pro .= "<td class='padding-0 {$cls}'><div class='thumb' style='{$pad} width:106px;'></div><a class='image-thumb1' href='search.php?keyword_type=" . htmlspecialchars($_GET['keyword_type'] ?? '') . "&keywords=" . htmlspecialchars($rrow['pd_title']) . "&rctyp=Products'><img data-toggle='tooltip' data-placement='top' title='" . htmlspecialchars($rrow['pd_title']) . "' class='photo' src='upload/myproduct/" . htmlspecialchars($rrow['pd_image']) . "' data-large_photo='upload/myproduct/" . htmlspecialchars($rrow['pd_image']) . "'/></a></td>";
                                            $rcoun++;
                                        }
                                        echo $releted_pro;
                                        ?>
                                    </tr></table>
                                </div>
                            </div>
                            <div class="clearfix"> </div>
                        </div>
                    </div>
                </div>
                <?php
                if ($suppRowCount === $suppTotalRow) {
                    $pages = max(2, (int)($_GET['page'] ?? 1) + 1);
                    if ($totitems > $totalpage) {
                        echo '<div class="col-lg-12 text-center" style="padding:30px;"><a href="https://arab-mart.com/search.php?rctyp=' . urlencode($_GET['rctyp'] ?? '') . '&keywords=' . urlencode($_GET['keywords'] ?? '') . '&page=' . $pages . '"><button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px; font-weight:bolder;">Display More Products / Services </button></a></div>';
                    }
                }
                $suppRowCount++;
            }
        } else {
            ?>
            <div style="width: 72%; font-size: 17px; font-weight: bold; color: #000; padding: 0px 0px 5px 0px;">"<?= htmlspecialchars($_GET['keywords'] ?? '') ?>"</div>
            <p style="clear:both"></p><p class="error-cty bo">Sorry, your search for <span style="font-weight: bold;color: #C30000;"><?= htmlspecialchars($_GET['keywords'] ?? '') ?></span> did not match any Supplier.</p><br><table align="left" border="0" cellpadding="0" cellspacing="0" width="663px"><tbody><tr><td valign="TOP" width="480"><div class="sug"><b style="font-size: 13px; padding-bottom: 5px; color: rgb(28, 28, 28);  display:block;">Suggestions:</b><ul><li>Check spellings of your search words </li><li>Try a different set of search words </li><li>Search only one Supplier at a time </li><li>Do not use very long search phrase </li><li>Use two or three words for best search results </li><li>Do not use special characters in your search </li><li>Do not use search words that are very specific (e.g., 20x25 mm tone tiles) </li><li>Select any other country to find your product.</li></ul></div></td></tr></tbody></table>
            </div>
            <?php
        }
    /* ---------- BUY LEADS ---------- */
    } elseif ($rctyp === 'buy_lead') {
        if (_nr($resk) > 0) {
            ?>
            <link rel="stylesheet" href="css/trade-7.2.css"/>
            <span style="display: none;"><?php var_dump($sqlk); ?></span>
            <?php
            while ($rowk = _fo($resk)) { ?>
                <div class="m2 n-4 p_34" onMouseOut="removeColor(this, 2, 'B');">
                    <div class="a1" style="width:100%;padding-left:8px;padding-bottom:20px;">
                        <div>
                            <p class="as g5 w2 z1 p4">Updated: <?= date('d M, Y', strtotime($rowk->br_updated_date ?? 'now')) ?></p>
                            <p class="b-u a1" style="overflow:visible;width:38px;height:22px;">&nbsp;</p>
                            &nbsp;
                            <a href="buyleads-details.php?id=<?= rand(1000,9999) . md5($rowk->br_id) ?>" class="fs bo clst"><font size="5px"><?= htmlspecialchars($rowk->br_pd_name ?? '') ?></font></a>
                            &nbsp;&nbsp;
                            <?php if (($rowk->br_approval_status ?? '') === '1') { ?><span class="vlogoBN"><span class="vlogo g9 bo d1">Verified</span></span><?php } ?>
                            <p class="m2"></p>
                        </div>
                        <p class="l1 p_33 pt1 wb"><?= htmlspecialchars(substr($rowk->br_requirement ?? '', 0, 100)) ?><?php if (strlen($rowk->br_requirement ?? '') > 100) { ?><a class="g9" href="buyleads-details.php?id=<?= rand(1000,9999) . md5($rowk->br_id) ?>">more...</a><?php } ?></p>
                        <div id="div_2" class="g9 lstb w1 nnn w3 w4">
                            <div class="pb1 bo g9 k7"><p class="m2"></p></div>
                            <p class="g9 k7"><b class="x1">Location:</b> <?= get_city_name(user_info($rowk->br_u_id, 'bnsprof_city')) ?> (<?= ucfirst(city_to_country(user_info($rowk->br_u_id, 'bnsprof_city'))) ?>)</p>
                        </div>
                        <div id="div6369351846" class="rn2 m2 z1 vu bo w1 d1 g9" style="background:url(images/c_button.png) no-repeat; height:30px; position:relative; top:-30px;" onClick="javascript:location.href = 'buyleads-details.php?id=<?= rand(1000,9999) . md5($rowk->br_id) ?>';">&nbsp;Contact Now</div>
                    </div>
                    <p class="m2" style="background: #CCC;"><img alt="Buy Enquiry" src="images/zero.gif"></p>
                    <?php if (isset($_COOKIE['loc_id'])) { ?><!--</div>--><?php } else { ?></div><?php } ?>
            <?php }
            ?>
            <br />
            <div class="pagination">
                <?php if ($pageno > 1) { ?><a href="" style="width:65px;">« Prev</a><?php }
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($pageno == $i) { ?><span id="pageno"><?= $i ?></span><?php } else { ?><a href=""><?= $i ?></a><?php }
                }
                if ($pageno < $total_pages) { ?><a style="width:65px;" href="">Next »</a><?php } ?>
            </div>
        <?php } else { ?>
            <table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%"><tr style="width:100%"><td valign="TOP" style="width:100%"><div class="sor">Sorry, your search for <b class="cb1"><?= htmlspecialchars($_GET['keywords'] ?? '') ?></b> did not match any Buy Leads.</div><div class="sug"><b>Suggestions:</b><ul><li>Check spellings of your search words </li><li>Try a different set of search words </li><li>Do not use very long search phrase </li><li>Use two or three words for best search results </li><li>Do not use special characters in your search </li><li>Do not use search words that are very specific (e.g., 20x25 mm tone &nbsp;&nbsp;tiles) </li><li>Select any other country to find your product.</li></ul></div><div style="clear: both;"><br><br></div></td></tr></table>
        <?php } if (isset($_COOKIE['loc_id'])) { ?><!--</div>--><?php } ?>

    /* ---------- TENDERS ---------- */
    <?php } elseif ($rctyp === 'tender') {
        if (_nr($tender_resk) > 0) {
            ?><link rel="stylesheet" href="css/trade-7.2.css"/><?php
            while ($tendRowk = _fo($tender_resk)) { ?>
                <div class="m2 n-4 p_34" onMouseOut="removeColor(this, 2, 'B');">
                    <div class="a1" style="width:100%;padding-left:8px;padding-bottom:20px;">
                        <div>
                            <p class="as g5 w2 z1 p4">Updated: <?= date('d M, Y', strtotime($tendRowk->tnd_updated_date ?? 'now')) ?></p>
                            <p class="b-u a1" style="overflow:visible;width:38px;height:22px;">&nbsp;</p>
                            &nbsp;
                            <a href="tender-details.php?id=<?= rand(1000,9999) . md5($tendRowk->tnd_id) ?>" class="fs bo clst"><font size="5px"><?= htmlspecialchars($tendRowk->tnd_heading ?? '') ?></font></a>
                            &nbsp;&nbsp;
                            <p class="m2"></p>
                        </div>
                        <p class="l1 p_33 pt1 wb"><?= htmlspecialchars(substr($tendRowk->tnd_details ?? '', 0, 100)) ?><?php if (strlen($tendRowk->tnd_details ?? '') > 100) { ?><a class="g9" href="tender-details.php?id=<?= rand(1000,9999) . md5($tendRowk->tnd_id) ?>">more...</a><?php } ?></p>
                        <div id="div_2" class="g9 lstb w1 nnn w3 w4"><div class="pb1 bo g9 k7"><p class="m2"></p></div><p class="g9 k7"><b class="x1">Location:</b> <?= get_city_name(user_info($tendRowk->tnd_usr_id, 'bnsprof_city')) ?></p></div>
                        <div id="div6369351846" class="rn2 m2 z1 vu bo w1 d1 g9" style="background:url(images/c_button.png) no-repeat; height:30px; position:relative; top:-30px;" onClick="openInNewTab('tender-details.php?id=<?= rand(1000,9999) . md5($tendRowk->tnd_id) ?>');"><button class="btn btn-md btn-warning border-radius-0 btn-enquiry" type="button" style="height: 33px; margin-left: -17px; margin-top: -9px; width: 110px; font-weight:bold; padding-left:10px;">Contact Now</button></div>
                    </div>
                    <p class="m2" style="background: #CCC;"><img alt="Buy Enquiry" src="images/zero.gif"></p>
                    <?php if (isset($_COOKIE['loc_id'])) { ?></div><?php } else { ?></div><?php } ?>
            <?php }
        }
        if (_nr($auction_resk) > 0) {
            ?><link rel="stylesheet" href="css/trade-7.2.css"/><?php
            while ($auctionRowk = _fo($auction_resk)) { ?>
                <div class="m2 n-4 p_34" onMouseOut="removeColor(this, 2, 'B');">
                    <div class="a1" style="width:100%;padding-left:8px;padding-bottom:20px;">
                        <div>
                            <p class="as g5 w2 z1 p4">Updated: <?= date('d M, Y', strtotime($auctionRowk->auc_updated_date ?? 'now')) ?></p>
                            <p class="b-u a1" style="overflow:visible;width:38px;height:22px;">&nbsp;</p>
                            &nbsp;
                            <a href="auction-details.php?id=<?= rand(1000,9999) . md5($auctionRowk->auc_id) ?>" class="fs bo clst"><font size="5px"><?= htmlspecialchars($auctionRowk->auc_heading ?? '') ?></font></a>
                            &nbsp;&nbsp;
                            <p class="m2"></p>
                        </div>
                        <p class="l1 p_33 pt1 wb"><?= htmlspecialchars(substr($auctionRowk->auc_details ?? '', 0, 100)) ?><?php if (strlen($auctionRowk->auc_details ?? '') > 100) { ?><a class="g9" href="auction-details.php?id=<?= rand(1000,9999) . md5($auctionRowk->auc_id) ?>">more...</a><?php } ?></p>
                        <div id="div_2" class="g9 lstb w1 nnn w3 w4"><div class="pb1 bo g9 k7"><p class="m2"></p></div><p class="g9 k7"><b class="x1">Location:</b> <?= get_city_name(user_info($auctionRowk->auc_usr_id, 'bnsprof_city')) ?></p></div>
                        <div id="div6369351846" class="rn2 m2 z1 vu bo w1 d1 g9" style="background:url(images/c_button.png) no-repeat; height:30px; position:relative; top:-30px;" onClick="openInNewTab('auction-details.php?id=<?= rand(1000,9999) . md5($auctionRowk->auc_id) ?>');"><button class="btn btn-md btn-warning border-radius-0 btn-enquiry" type="button" style="height: 33px; margin-left: -17px; margin-top: -9px; width: 110px; font-weight:bold; padding-left:10px;">Contact Now</button></div>
                    </div>
                    <p class="m2" style="background: #CCC;"><img alt="Buy Enquiry" src="images/zero.gif"></p>
                </div>
            <?php }
        }
        if (_nr($tender_resk) == 0 && _nr($auction_resk) == 0) { ?>
            <br /><table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%"><tr style="width:100%"><td valign="TOP" style="width:100%"><div class="sor">Sorry, your search for <b class="cb1"><?= htmlspecialchars($_GET['keywords'] ?? '') ?></b> did not match any Tender &amp; Auction.</div><div class="sug"><b>Suggestions:</b><ul><li>Check spellings of your search words </li><li>Try a different set of search words </li><li>Do not use very long search phrase </li><li>Use two or three words for best search results </li><li>Do not use special characters in your search </li><li>Do not use search words that are very specific (e.g., 20x25 mm tone &nbsp;&nbsp;tiles) </li><li>Select any other country to find your product.</li></ul></div><div style="clear: both;"><br><br></div></td></tr></table>
        <?php }
        if (isset($_COOKIE['loc_id'])) { ?><!--</div>--><?php } ?>

    /* ---------- AUCTIONS ---------- */
    <?php } elseif ($rctyp === 'auction') {
        if (_nr($resk) > 0) {
            ?><link rel="stylesheet" href="css/trade-7.2.css"/><?php
            while ($rowk = _fo($resk)) { ?>
                <div class="m2 n-4 p_34" onMouseOut="removeColor(this, 2, 'B');">
                    <div class="a1" style="width:100%;padding-left:8px;padding-bottom:20px;">
                        <div>
                            <p class="as g5 w2 z1 p4">Updated: <?= date('d M, Y', strtotime($rowk->auc_updated_date ?? 'now')) ?></p>
                            <p class="b-u a1" style="overflow:visible;width:38px;height:22px;">&nbsp;</p>
                            &nbsp;
                            <a href="auction-details.php?id=<?= rand(1000,9999) . md5($rowk->auc_id) ?>" class="fs bo clst"><font size="5px"><?= htmlspecialchars($rowk->auc_heading ?? '') ?></font></a>
                            &nbsp;&nbsp;
                            <p class="m2"></p>
                        </div>
                        <p class="l1 p_33 pt1 wb"><?= htmlspecialchars(substr($rowk->auc_details ?? '', 0, 100)) ?><?php if (strlen($rowk->auc_details ?? '') > 100) { ?><a class="g9" href="auction-details.php?id=<?= rand(1000,9999) . md5($rowk->auc_id) ?>">more...</a><?php } ?></p>
                        <div id="div_2" class="g9 lstb w1 nnn w3 w4"><div class="pb1 bo g9 k7"><p class="m2"></p></div><p class="g9 k7"><b class="x1">Location:</b> <?= get_city_name(user_info($rowk->auc_usr_id, 'bnsprof_city')) ?> (<?= ucfirst(city_to_country(user_info($rowk->auc_usr_id, 'bnsprof_city'))) ?>)</p></div>
                        <div id="div6369351846" class="rn2 m2 z1 vu bo w1 d1 g9" style="background:url(images/c_button.png) no-repeat; height:30px; position:relative; top:-30px;" onClick="javascript:location.href = 'auction-details.php?id=<?= rand(1000,9999) . md5($rowk->auc_id) ?>';">&nbsp;Contact Now</div>
                    </div>
                    <p class="m2" style="background: #CCC;"><img alt="Buy Enquiry" src="images/zero.gif"></p>
                </div>
            <?php }
            ?>
            <br />
            <div class="pagination">
                <?php if ($pageno > 1) { ?><a href="" style="width:65px;">« Prev</a><?php }
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($pageno == $i) { ?><span id="pageno"><?= $i ?></span><?php } else { ?><a href=""><?= $i ?></a><?php }
                }
                if ($pageno < $total_pages) { ?><a style="width:65px;" href="">Next »</a><?php } ?>
            </div>
        <?php } else { ?>
            <table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%"><tr style="width:100%"><td valign="TOP" style="width:100%"><div class="sor">Sorry, your search for <b class="cb1"><?= htmlspecialchars($_GET['keywords'] ?? '') ?></b> did not match any Auction.</div><div class="sug"><b>Suggestions:</b><ul><li>Check spellings of your search words </li><li>Try a different set of search words </li><li>Do not use very long search phrase </li><li>Use two or three words for best search results </li><li>Do not use special characters in your search </li><li>Do not use search words that are very specific (e.g., 20x25 mm tone &nbsp;&nbsp;tiles) </li><li>Select any other country to find your product.</li></ul></div><div style="clear: both;"><br><br></div></td></tr></table>
        <?php } ?>
    <?php } ?>

    <br>
    <div id="autosuggest" style="width: 393px;position: absolute; left: 245px; top: 10407px;cursor: pointer;max-height: 202px;overflow-y: auto;overflow-x: hidden;z-index:1000 !important; display: none;"><ul><li><!-- Suggestion --></li></ul></div><br>
    </div>

<?php } else {
    /* ================================================================
     *  PRODUCTS  (list view + grid view)
     * ================================================================ */

    $prod_col = 'products.*';
    $bus_col  = 'business_profile.*';
    $usr_col  = 'user.usr_id,user.email,user.fname,user.website,user.country,user.image,user.country_ph_code,user.profileImage';

    if (($_GET['grid'] ?? '') !== 'active') {
        ?>
        <div id="search_result" class="list-grid-active" style="postion:relative;">
        <?php

        /* ---- Build $sql_prd ---- */
        $idd = $_GET['idd'] ?? '';
        if ($idd !== '') {
            $sql_prd = "select * from products,measurement_unit_arabyos,country, business_profile, plan_member_id, smembership_plan where mu_id=pd_unit and pd_currency=cn_id {$sql_pd_ck} and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id = business_profile.bnsprof_id and pd_status='1' ANd smembership_plan.mp_id = plan_member_id.p_id and pd_image!='' and plan_member_id.expiry_date > " . time() . " and pd_subcat_id = '" . _esc($idd) . "' ORDER BY FIELD(p_id,'5','4','3','15','0')";
        } else {
            $minQty    = $_POST['min_qty'] ?? 0;
            $min_qty   = ((int)$minQty > 0) ? " AND products.pd_min_order_qty <= " . (int)$minQty : '';
            $stateid   = '';
            if (count((array)($_POST['state_id'] ?? [])) > 1) {
                foreach ($_POST['state_id'] as $v) { $stateid .= _esc($v) . ','; }
                $stateid = rtrim($stateid, ',');
            } else {
                $stateid = isset($_POST['state_id'][0]) ? _esc($_POST['state_id'][0]) : '';
            }
            $countryid = $_POST['country_id'] ?? '';
            $cntryval  = '';

            if (isset($_POST['srchbustype'])) {
                $bsn_type  = $_POST['bsn_type'] ?? [];
                $keyword   = ''; $scity = ''; $sql_pd_country = ''; $keywordmem = '';
                $i = 1;
                foreach ($bsn_type as $v) {
                    $ve = _esc($v);
                    $keyword .= ($i == 1) ? "(bnsprof_businesstype='{$ve}' or bnsprof_businesstype like '{$ve},%' or bnsprof_businesstype like '%,{$ve}' or bnsprof_businesstype like '%,{$ve},%'" : " or bnsprof_businesstype='{$ve}' or bnsprof_businesstype like '{$ve},%' or bnsprof_businesstype like '%,{$ve}' OR bnsprof_businesstype like '%,{$ve},%'";
                    $i++;
                }
                if (strlen($keyword) > 0) $keyword = " and ({$keyword}) ";

                if (isset($_POST['mst_type'])) {
                    $k = 1;
                    foreach ($_POST['mst_type'] as $v) { $ve = _esc($v); $keywordmem .= ($k == 1) ? "( p_id = '{$ve}'" : " or p_id = '{$ve}'"; $k++; }
                    if (isset($_COOKIE['loc_id'])) {
                        $loc2 = _esc($_COOKIE['loc_id']);
                        $checkCountry = " AND cn_id = '{$loc2}'";
                        if (isset($_POST['scity']) && strlen($_POST['scity']) > 0) {
                            $sc = _esc($_POST['scity']);
                            $scity = "(bnsprof_city IN(SELECT ct_id from city where ct_name like '%{$sc}%' and ct_cn_id={$loc2})) and";
                        }
                        if (strlen($scity) > 0) $sql_pd_country = " and ( {$scity} ( pd_uid in(select distinct usr_id from user where country='{$loc2}')))";
                    } else {
                        $checkCountry = '';
                        if (isset($_POST['scity']) && strlen($_POST['scity']) > 0) { $sc = _esc($_POST['scity']); $scity = "(bnsprof_city IN(SELECT ct_id from city where ct_name like '%{$sc}%')) "; }
                        if (strlen($scity) > 0) $sql_pd_country = " and ( {$scity})";
                    }
                    $newkw = generateProdSearchString($keywords);
                    $bnewkw = generateProdSearchString_pro_sup($keywords);
                    $sql_prd = "select measurement_unit_arabyos.*,country.*,{$bus_col},{$prod_col}, MATCH (pd_title) AGAINST ('" . _esc($keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit_arabyos,business_profile,country, plan_member_id where bnsprof_uid=pd_uid and mu_id=pd_unit and ((pd_title LIKE {$newkw}) OR (bnsprof_compname LIKE {$bnewkw})) and pd_currency=cn_id {$sql_pd_country} and pd_status='1' and pd_image!=''{$checkCountry} and bnsprof_id=b_id and {$keywordmem}) AND plan_member_id.expiry_date > " . time() . "{$keyword} GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc limit 0,20";
                } else {
                    if (isset($_COOKIE['loc_id'])) {
                        $loc2 = _esc($_COOKIE['loc_id']); $checkCountry = " AND cn_id = '{$loc2}'";
                        if (isset($_POST['scity']) && strlen($_POST['scity']) > 0) { $sc = _esc($_POST['scity']); $scity = "(bnsprof_city IN(SELECT ct_id from city where ct_name like '%{$sc}%' and ct_cn_id={$loc2})) and"; }
                        if (strlen($scity) > 0) $sql_pd_country = " and ( {$scity} ( pd_uid in(select distinct usr_id from user where country='{$loc2}')))";
                    } else {
                        $checkCountry = '';
                        if (isset($_POST['scity']) && strlen($_POST['scity']) > 0) { $sc = _esc($_POST['scity']); $scity = "(bnsprof_city IN(SELECT ct_id from city where ct_name like '%{$sc}%') ) "; }
                        if (strlen($scity) > 0) $sql_pd_country = " and ( {$scity})";
                    }
                    $newkw = generateProdSearchString($keywords); $bnewkw = generateProdSearchString_pro_sup($keywords);
                    $sql_prd = "select measurement_unit_arabyos.*,country.*,{$bus_col},{$prod_col}, MATCH (pd_title) AGAINST ('" . _esc($keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit_arabyos,country,business_profile,plan_member_id where bnsprof_uid=pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE {$newkw}) OR (bnsprof_compname LIKE {$bnewkw})) and pd_currency=cn_id {$sql_pd_country} AND pd_status='1'{$checkCountry} AND pd_image!='' {$keyword} AND plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc limit 0,20";
                }
            } else {
                if ($keywords !== '') {
                    if (isset($_POST['country_id'])) {
                        $p = 1;
                        foreach ($countryid as $v) { $ve = _esc($v); $cntryval .= ($p == 1) ? " and (country.cn_name = '{$ve}'" : " or country.cn_name = '{$ve}'"; $p++; }
                        $newkw = generateProdSearchString($keywords); $bnewkw = generateProdSearchString_pro_sup($keywords);
                        $sql_prd = "select measurement_unit_arabyos.*,country.*,{$bus_col},{$prod_col},{$usr_col}, MATCH (pd_title) AGAINST ('" . _esc($keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit_arabyos,country,business_profile,user,plan_member_id where user.usr_id = bnsprof_uid and bnsprof_uid=pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE {$newkw}) OR (bnsprof_compname LIKE {$bnewkw})) {$cntryval}) and pd_currency=cn_id and pd_status='1' and pd_image!=''{$min_qty} AND plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc limit 0,20";
                    } elseif (isset($_POST['state_id'])) {
                        $newkw = generateProdSearchString($keywords); $bnewkw = generateProdSearchString_pro_sup($keywords);
                        $sql_prd = "select measurement_unit_arabyos.*,country.*,{$bus_col},{$prod_col}, MATCH (pd_title) AGAINST ('" . _esc($keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit_arabyos,country,business_profile,plan_member_id where bnsprof_uid=pd_uid and mu_id=pd_unit and b_id = bnsprof_id and ((pd_title LIKE {$newkw}) OR (bnsprof_compname LIKE {$bnewkw})) and pd_currency=cn_id {$sql_pd_ck} and pd_status='1' and pd_image!=''{$min_qty} and bnsprof_state IN ({$stateid}) AND plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc limit 0,20";
                    } else {
                        $totalpage = 50; $startpage = 0;
                        if (($_GET['page'] ?? 0) > 1) { $limit = (($_GET['page'] - 1)) * $totalpage; $setLimit = " LIMIT {$limit},{$totalpage}"; }
                        else { $limit = $startpage; $setLimit = " LIMIT {$limit},{$totalpage}"; }
                        $newkw = generateProdSearchString($keywords); $bnewkw = generateProdSearchString_pro_sup($keywords);
                        $sql_prd = "select measurement_unit_arabyos.*,country.*,{$bus_col},{$prod_col}, MATCH (pd_title) AGAINST ('" . _esc($keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit_arabyos,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE {$newkw}) OR (bnsprof_compname LIKE {$bnewkw})) and pd_currency=cn_id {$sql_pd_ck} and pd_status='1' and pd_image!=''{$min_qty} AND plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc" . ($setLimit ?? '');
                    }
                } else {
                    if (isset($_POST['country_id'])) {
                        $p = 1;
                        foreach ($countryid as $v) { $ve = _esc($v); $cntryval .= ($p == 1) ? " and (country.cn_name = '{$ve}'" : " or country.cn_name = '{$ve}'"; $p++; }
                        $newkw = generateProdSearchString($keywords); $bnewkw = generateProdSearchString_pro_sup($keywords);
                        $sql_prd = "select measurement_unit_arabyos.*,country.*,{$bus_col},{$prod_col},{$usr_col}, MATCH (pd_title) AGAINST ('" . _esc($keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit_arabyos,country,business_profile,user,plan_member_id where user.usr_id = bnsprof_uid and bnsprof_uid=pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE {$newkw}) OR (bnsprof_compname LIKE {$bnewkw})) {$cntryval}) and pd_currency=cn_id and pd_status='1' and pd_image!=''{$min_qty} AND plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_title asc limit 0,20";
                    } elseif (isset($_POST['state_id'])) {
                        $sql_prd = "select measurement_unit_arabyos.*,country.*,{$bus_col},{$prod_col}, MATCH (pd_title) AGAINST ('" . _esc($keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit_arabyos,country,business_profile,plan_member_id where bnsprof_uid=pd_uid and mu_id=pd_unit and b_id = bnsprof_id and pd_currency=cn_id {$sql_pd_ck} and pd_status='1' and pd_image!=''{$min_qty} and bnsprof_state IN ({$stateid}) AND plan_member_id.expiry_date > " . time() . " GROUP BY pd_id ORDER BY title_relevance DESC, FIELD(plan_member_id.p_id,'5','4','3','15','0'), pd_id desc limit 0,20";
                    } else {
                        $sql_prd = "select measurement_unit_arabyos.*,country.*,{$prod_col}, MATCH (pd_title) AGAINST ('" . _esc($keywords) . "' IN BOOLEAN MODE) AS title_relevance from products,measurement_unit_arabyos,country where mu_id=pd_unit and pd_currency=cn_id {$sql_pd_ck} and pd_status='1' and pd_image!=''{$min_qty} GROUP BY pd_id order by title_relevance DESC, pd_title asc limit 0,20";
                    }
                }
            }
        }

        /* ---- Total count query (webxtor currency-country fix preserved) ---- */
        $kwE = _esc($keywords);
        $nkw = generateProdSearchString($keywords); $bnkw = generateProdSearchString_pro_sup($keywords);
        $sql_prd_total = "select *, MATCH (pd_title) AGAINST ('{$kwE}' IN BOOLEAN MODE) AS title_relevance from products as prod,measurement_unit_arabyos,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE {$nkw}) OR (bnsprof_compname LIKE {$bnkw})) and pd_currency=cn_id {$sql_pd_ck} and pd_status='1' and pd_image!='' AND plan_member_id.expiry_date > " . time() . " ORDER BY title_relevance DESC, FIELD(p_id,'5','4','3','15','0'), pd_title asc";
        // Apply the webxtor currency-country regex fixes
        foreach (['sql_prd_total', 'sql_prd'] as $_var) {
            $$_var = preg_replace('#\bcountry,#msi', 'country c,', $$_var);
            $$_var = preg_replace('#^(.+?\b)where(.+)$#msi', '$1, city, country c2 where c2.cn_id=pd_currency AND bnsprof_city=ct_id AND $2', $$_var);
            $$_var = preg_replace('#pd_currency\s*=\s*cn_id#msi', 'cn_id=ct_cn_id', $$_var);
            $$_var = preg_replace('#([^.a-z_-])cn_(id|code)#msi', '$1c.cn_$2', $$_var);
            $$_var = str_replace('select c.cn_id from country where c.cn_code', 'select cn_id from country where cn_code', $$_var);
            $$_var = preg_replace('#\bcountry\.#msi', 'c.', $$_var);
        }

        $run_query_tot  = _mqd($sql_prd_total);
        $gettot_product = _nr($run_query_tot);

        $run_query      = _mqd($sql_prd);
        $getSearchCount = _nr($run_query);

        if ($getSearchCount > 0) {
            $myfev    = [];
            $related  = [];
            $countRec = 1;
            $catTopBanner = $catBottomBanner = '';
            $i = 0;

            while ($row = _fa($run_query)) {
                $related[]   = $row['pd_subcat_id'];
                $fevrow_icon = 0;
                $data = $userArrayRow_Result[$row['pd_uid']] ?? null;

                if ($data) {
                    $get_icon = _mqd("select smembership_plan.mst_icon as sponsericon , plan_member_id.* , smembership_icon_plan.mst_icon as producticon,smembership_icon_plan.mst_name as pplan from smembership_plan,plan_member_id , smembership_icon_plan where smembership_icon_plan.mp_id =plan_member_id.p_id and smembership_plan.mp_id =plan_member_id.p_id and plan_member_id.b_id = " . (int)$data['bnsprof_id']);
                    if (_nr($get_icon)) { $fevrow_icon = _fab($get_icon); }
                    $get_icon2 = _mqd("select icon_id, p_id from plan_member_id where b_id = " . (int)$data['bnsprof_id']);
                    $icon2     = _fab($get_icon2);
                    $get_icon1 = _mqd("select * from smembership_icon_plan where mp_id = " . (int)($icon2['icon_id'] ?? 0));
                    $icon1     = _fab($get_icon1);
                    $get_icon3 = _mqd("select * from smembership_plan where mp_id = " . (int)($icon2['p_id'] ?? 0));
                }
                ?>

                <div class="row ar-mid-box" style="width: 100%">
                    <div class="col-lg-12 col-sm-11 col-md-11 ar-box-1  margin-top-10 ">
                        <div class="row">
                            <div class="col-xs-6 col-lg-3 big-img-box box-1" id="div-<?= (int)$row['pd_id'] ?>">
                                <header>
                                    <?php if (($_GET['rctyp'] ?? '') !== 'Suppliers') { ?>
                                    <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                        <a href="javascript:void(0)" class="product_fav_btn" data="<?= (int)$row['pd_id'] ?>" onclick="return showfavorite(<?= (int)$_SESSION['uid_indm'] ?>,<?= (int)$row['pd_id'] ?>)" class="ar-star"><i class="fa fa-star star" style="color:<?= (in_array($row['pd_id'], $myfev)) ? '#E48F23' : '#808080' ?>"></i> Favorite</a>
                                    <?php } else { ?>
                                        <a href="sign-in.php" class="product_fav_btn" data="<?= (int)$row['pd_id'] ?>" class="ar-star"><i class="fa fa-star star"></i> Favorite</a>
                                    <?php } ?>
                                    <a href="javascript:void(0)" class="ar-star product_compare" data-prod_img="<?= 'https://www.arab-mart.com/upload/myproduct/' . htmlspecialchars($row['pd_image'] ?? '') ?>" onClick="return addcompare(<?= (int)$row['pd_id'] ?>)" data-prod_id="<?= (int)$row['pd_id'] ?>" title="<?= htmlspecialchars($row['pd_title'] ?? '') ?>"><i class="fa fa-plus star"></i> Compare</a><?php } ?>
                                </header>
                                <figure class="box">
                                    <?php if ($fevrow_icon) { ?>
                                        <div class="ribbon"><img src="./admin/images/<?= htmlspecialchars($fevrow_icon['sponsericon'] ?? '') ?>"/></div>
                                    <?php } elseif (_nr($get_icon3)) { $fevrow_icon3 = _fab($get_icon3); ?>
                                        <div class="ribbon"><img src="./admin/images/<?= htmlspecialchars($fevrow_icon3['mst_icon'] ?? '') ?>"/></div>
                                    <?php } ?>
                                    <?php $pimg = explode(',', $row['pd_image'] ?? ''); $limg = explode(',', $row['pd_imagelogo'] ?? ''); $zoom_image_val = 'upload/myproduct/' . $pimg[0]; ?>
                                    <div class="zoomthis"><a class="txt-blue" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/product-details.php?token=<?= rand(1000,9999) . md5($row['pd_id']) ?>&c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank"><?= "<img src='upload/myproduct/" . htmlspecialchars($pimg[0]) . "'>" ?></a></div>
                                    <?php if (!empty($row['pd_imagelogo'])) { ?><div class="zk" style="border: 1px solid #267abf;height: auto; width: 100px;position: absolute;top: 172px; left: 5px;"><?= "<img style='width: auto; height: 100px;max-width:100%;' src='upload/myproduct/" . htmlspecialchars($limg[0]) . "'>" ?></div><?php } ?>
                                </figure>
                                <center><a onclick="zoom_image(this)" data-img="<?= htmlspecialchars($zoom_image_val) ?>" style="padding: 10px;"><i class="fa fa-search-plus" Title="شاهد - الصورة - بحجم كبير"></i></a></center>
                            </div>
                            <div class="col-xs-6 col-lg-5 box-2">
                                <ul>
                                    <li class="margin-bottom-0">
                                        <h4 class="txt-blue"><a class="txt-blue" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/product-details.php?token=<?= rand(1000,9999) . md5($row['pd_id']) ?>&c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank"><?= highlight($row['pd_title'] ?? '', urlencode($_GET['keywords'] ?? '')) ?></a></h4>
                                    </li>
                                    <li><?= htmlspecialchars(substr($row['pd_desc'] ?? '', 0, 132)) ?></li>
                                    <li class="text-right web2" <?php if (!empty($row['brand_name'])) echo 'style="display: flow-root;"' ?>>
                                        <?php if (!empty($row['brand_name'])) { ?><span style="float: left;"><strong>Brand:</strong> <?= htmlspecialchars($row['brand_name']) ?></span><?php } ?>
                                        <a href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/product-details.php?token=<?= rand(1000,9999) . md5($row['pd_id']) ?>&c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank">+  More</a>
                                    </li>
                                    <li> Min Order &nbsp;<big class="txt-bold txt-red"><?= (int)$row['pd_min_order_qty'] ?></big>&nbsp; <?= measurement_unit($row['pd_unit']) ?> </li>
                                    <?php
                                    /* FIX: removed header() call; currency resolved without sending headers */
                                    $symbol = _currency_symbol((string)($row['pd_currency'] ?? ''));
                                    $style_none = ((int)($row['pd_fob_price'] ?? 0) == 0) ? 'hide' : 'show';
                                    ?>
                                    <li class="<?= $style_none ?>">Price  &nbsp; <big class="txt-bold txt-red"><a style="color: black;" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/product-add.php"><?= $symbol . ($row['pd_fob_price'] ?? '') . '<span style="color:black"> ~ </span>' . $symbol . ($row['pd_fob_price2'] ?? '') ?></a></big>
                                    <?php if (empty($_SESSION['uid_indm'])) { ?>
                                        <a style="float: right;position: relative;right: 20px;" class="hidden-xs" data-price="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/arab-mart.com/sign-in.php">(Get Letest Price)</a>
                                    <?php } else {
                                        $geo_loc   = $location_geo_country[0] ?? '';
                                        $countryyyy = $_COOKIE['loc_id'] ?? '';
                                        if ($keywords !== '') { ?>
                                            <a style="float: right;position: relative;right: 20px;" class="ajax" data-enquiry="" id="btn_ajax_send<?= (int)$row['pd_id'] ?>" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest_supplier.php?id=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>&pid=<?= (int)$row['pd_id'] ?>&keywords=<?= urlencode($_GET['keywords'] ?? '') ?>&geo=<?= htmlspecialchars($geo_loc) ?>&conty=<?= htmlspecialchars($countryyyy) ?>&search=1">(Get Letest Price)</a>
                                        <?php } else { ?>
                                            <a data-enquiry="" class="ajax" id="btn_ajax_send<?= (int)$row['pd_id'] ?>" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest_supplier.php?id=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>&pid=<?= (int)$row['pd_id'] ?>&keywords=<?= urlencode($_GET['keywords'] ?? '') ?>&geo=<?= htmlspecialchars($geo_loc) ?>&conty=<?= htmlspecialchars($countryyyy) ?>&search=1">(Get Letest Price)</a>
                                        <?php } } ?>
                                    </li>
                                    <?php if (!empty($row['pd_pn_capct'])) { ?><li><a href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/product-details.php?token=<?= rand(1000,9999) . md5($row['pd_id']) ?>&c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank">Logistics</a></li><?php } ?>
                                    <li class="margin-top-5">
                                        <table class="table"><tr>
                                            <?php $compName = explode(' ', $data['bnsprof_compname'] ?? ''); $compnamers = implode('', $compName); ?>
                                            <td style="padding-left:0px;"><a href="/company/index.php?c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank" class="txt-blue txt-bold hidden-xs"><img src="images/users.png" width="25px"/> About Us</a></td>
                                            <td class="hidden-xs"><a href="/company/products.php?c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>&flaag=whsuccess" target="_blank" class="txt-blue txt-bold hidden-xs"><img src="images/icon.png" width="20px"/> View Products</a></td>
                                            <?php if (!empty($row['pd_pdf_attach'])) { ?><td><a href="//arab-mart.com/upload/productdoc/<?= htmlspecialchars($row['pd_pdf_attach']) ?>" target="_blank"><img src="/images/pdf_icon.png" style="width: 28px;height: 28px;"> PDF</a></td><?php } ?>
                                            <td><a onclick="open_chat()" class="hidden-xs"><i class="fa fa-comments"></i> Chat</a></td>
                                        </tr></table>
                                    </li>
                                    <li>
                                        <table class="table enquiry-tb margin-bottom-0"><tr class="bg-gray">
                                            <?php $Countryphone = _fab(_mqd("SELECT * FROM `country` where cn_id = " . (int)($data['country'] ?? 0))); ?>
                                            <td class="padding-0 col-sm-6" style="vertical-align: middle;"><big class="">&nbsp;
                                                <a href="https://wa.me/+<?= user_info($data['bnsprof_uid'] ?? 0, 'country_ph_code') ?><?= user_info($data['bnsprof_uid'] ?? 0, 'mobile1') ?>" class="txt-black txt-lg" style="vertical-align: middle;"><img src="images/mobile.png" width="25px"/></a>
                                                <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                                    <a href="tel:+<?= user_info($data['bnsprof_uid'] ?? 0, 'country_ph_code') ?>"> &nbsp;<b><?= htmlspecialchars($Countryphone['cn_ph'] ?? '') ?>-<?= user_info($data['bnsprof_uid'] ?? 0, 'mobile1') ?></b></a>
                                                <?php } else { ?><a href="/sign-in.php#loginform">Show number</a><?php } ?>
                                            </big></td>
                                            <td class="text-right padding-0 col-sm-6">
                                                <?php if (empty($_SESSION['uid_indm'])) { ?>
                                                    <a data-enquiry="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/sign-in.php"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                                                <?php } else {
                                                    $geo_loc   = $location_geo_country[0] ?? '';
                                                    $countryyyy = $_COOKIE['loc_id'] ?? '';
                                                    if (($_GET['grid'] ?? '') === 'active') { ?>
                                                        <a data-enquiry="" id="btn_ajax_send<?= (int)$row['pd_id'] ?>" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/?&keywords=<?= urlencode($_GET['keywords'] ?? '') ?>&grid=active"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                                                    <?php } else { if ($keywords !== '') { ?>
                                                        <a data-enquiry="" class="ajax" id="btn_ajax_send<?= (int)$row['pd_id'] ?>" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest.php?id=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>&pid=<?= (int)$row['pd_id'] ?>&keywords=<?= urlencode($_GET['keywords'] ?? '') ?>&geo=<?= htmlspecialchars($geo_loc) ?>&conty=<?= htmlspecialchars($countryyyy) ?>&search=1"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                                                    <?php } else { ?>
                                                        <a data-enquiry="" class="ajax" id="btn_ajax_send<?= (int)$row['pd_id'] ?>" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest.php?id=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>&pid=<?= (int)$row['pd_id'] ?>&geo=<?= htmlspecialchars($geo_loc) ?>&conty=<?= htmlspecialchars($countryyyy) ?>&search=1"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                                                    <?php } } } ?>
                                            </td>
                                        </tr></table>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-4 box-3">
                                <div class="ar-box-1 ar-box padding-5 margin-bottom-5 bg-gray" style="overflow-x: hidden;">
                                    <header class="sub-box">
                                        <?php if ($fevrow_icon) { ?><a href="https://www.arab-mart.com/membership_plans.php"><img src="./admin/images/<?= htmlspecialchars($fevrow_icon['producticon'] ?? '') ?>" width="25px" height="25px" title="<?= htmlspecialchars($fevrow_icon['pplan'] ?? '') ?>"/></a><?php } elseif (_nr($get_icon1)) { ?><a href="https://www.arab-mart.com/membership_plans.php"><img src="./admin/images/<?= htmlspecialchars($icon1['mst_icon'] ?? '') ?>" width="25px" height="25px" title="<?= htmlspecialchars($icon1['mst_name'] ?? '') ?>"/></a><?php } ?>
                                        <b class="txt-dark-gray"><a href="/company/profile.php?c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank" style="white-space:nowrap;" title="<?= ucfirst($data['bnsprof_compname'] ?? '') ?>"><?= ucfirst(substr($data['bnsprof_compname'] ?? '', 0, 20) . '...') ?></a></b>
                                    </header>
                                    <?php
                                    $countryId     = $data['country'] ?? 0;
                                    $getCountryName = _fab(_mqd("SELECT * FROM `country` where cn_id='" . (int)$countryId . "'"));
                                    $stateId       = $data['ct_state'] ?? 0;
                                    $getStateName  = _fab(_mqd("SELECT * FROM `states` where state_id='" . (int)$stateId . "'"));
                                    if (!empty($getCountryName['cn_flag'])) {
                                        echo "<img src='images/country_flag/" . htmlspecialchars($getCountryName['cn_flag']) . "' alt='" . htmlspecialchars($getCountryName['cn_flag']) . "' style='width:21.6px;height:21.6px;'/>";
                                    } else {
                                        $getCountryflag = _fab(_mqd("SELECT * FROM `country` where cn_id='" . (int)($_COOKIE['loc_id'] ?? 0) . "'"));
                                        echo "<img src='images/country_flag/" . htmlspecialchars($getCountryflag['cn_flag'] ?? '') . "' alt='" . htmlspecialchars($getCountryflag['cn_flag'] ?? '') . "' style='width:21.6px;height:21.6px;'/>";
                                    }
                                    ?>
                                    <b class="txt-bold" style="color:#302670; margin-left:10px;"><?php
                                        $address = '';
                                        if (!empty($getCountryName['cn_name'])) $address .= $getCountryName['cn_name'] . '-';
                                        if (!empty($getStateName['state_name'])) $address .= $getStateName['state_name'] . '-';
                                        if (!empty($data['ct_name'])) $address .= $data['ct_name'];
                                        echo $address !== '' ? htmlspecialchars($address) : 'Not available';
                                    ?></b>
                                    <table class="table margin-top-5">
                                        <tr>
                                            <?php
                                            $bnsprof_businesstype = $data['bnsprof_businesstype'] ?? '';
                                            $dataC = explode(',', $bnsprof_businesstype);
                                            $busn_type = ''; $busn_type1 = '';
                                            if ($bnsprof_businesstype !== '') {
                                                $i = 1; $j = 1;
                                                foreach ($dataC as $r) {
                                                    if ($i <= 2) { $busn_type .= ($userArrayRow_Type[$r] ?? '') . ($i < count($dataC) ? ', ' : ''); $i++; }
                                                    $busn_type1 .= ($userArrayRow_Type[$r] ?? '') . ($j < count($dataC) ? ', ' : ''); $j++;
                                                }
                                                $busn_type .= '...';
                                            } else { $busn_type = 'Not available'; }
                                            ?>
                                            <td class="txt-light-gray padding-0"> Business Type : </td>
                                            <td class="padding-0 txt-bold" title="<?= htmlspecialchars($busn_type1) ?>"><?= htmlspecialchars($busn_type) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-light-gray padding-0"><a style="font-size:12px;font-weight:100;color:#8a8a8a" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/arab-mart.com/product-add.php" target="_blank">Trade Location :</a></td>
                                            <td class="padding-0 txt-bold"><a style="font-size:12px;color:#242424" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/arab-mart.com/product-add.php"><?php
                                                $ploc = $row['pd_preferred_buyer_location'] ?? '';
                                                if ($ploc === 'abroad') echo 'Abroad Only';
                                                elseif ($ploc === 'any') echo 'Abroad + Domestic';
                                                elseif ($ploc === 'domestic') echo 'Domestic Only';
                                                elseif ($ploc === 'my_city') echo 'My City Only';
                                            ?></a></td>
                                        </tr>
                                        <tr></tr>
                                        <tr>
                                            <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                                <td class="txt-light-gray" colspan="2" style="padding-left: 0;"><a href="<?= htmlspecialchars($data['bnsprof_website_alt'] ?? '') ?>" target="_blank"><?= htmlspecialchars($data['bnsprof_website_alt'] ?? '') ?></a></td>
                                            <?php } else { ?>
                                                <td class="txt-light-gray" colspan="2" style="padding-left: 0;"><a href="/sign-in.php#loginform">Show website</a></td>
                                            <?php } ?>
                                            <td class="padding-0"></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="small-box hidden-xs">
                                    <div class="box-under-twoimage"><div>
                                        <?php
                                        $rqury   = 'select pd_id,pd_title,pd_imagelogo, pd_image,pd_subcat_id from products where pd_image!="" and pd_uid = ' . (int)$row['pd_uid'] . ' and pd_status = "1" and pd_id !=' . (int)$row['pd_id'] . ' ORDER by pd_id DESC limit 2';
                                        $rresult = _mq($rqury);
                                        $rcoun   = 1; $releted_pro = '';
                                        while ($rrow = _fa($rresult)) {
                                            $imgarr = explode(',', $rrow['pd_image']);
                                            if ($rcoun == 1) {
                                                $releted_pro .= '<div class="padding-0 small-box-td1"><div class="thumb" style="padding-right:3px;"></div><div class="wrapper-product-searchright"><a class="thumb-images" href="search.php?keyword_type=' . htmlspecialchars($_GET['keyword_type'] ?? '') . '&keywords=' . htmlspecialchars($rrow['pd_title']) . '&rctyp=Products"><img data-toggle="tooltip" data-placement="top" title="' . htmlspecialchars($rrow['pd_title']) . '" class="photo" src="upload/myproduct/' . htmlspecialchars($imgarr[0]) . '" data-large_photo="upload/myproduct/' . htmlspecialchars($imgarr[0]) . '"/></a>';
                                                if (!empty($rrow['pd_imagelogo'])) { $logoarr = explode(',', $rrow['pd_imagelogo']); $releted_pro .= '<a class="inner-search-right-img" href="search.php?keyword_type=' . htmlspecialchars($_GET['keyword_type'] ?? '') . '&keywords=' . htmlspecialchars($rrow['pd_title']) . '&rctyp=Products" style="width: auto;height: auto;"><img style="width: 35px;max-width: 100%;" class="photo" src="upload/myproduct/' . htmlspecialchars($logoarr[0]) . '" data-large_photo="upload/myproduct/' . htmlspecialchars($logoarr[0]) . '"/></a>'; }
                                                $releted_pro .= '</div></div>';
                                            } else {
                                                $releted_pro .= '<div class="padding-0 text-right small-box-td2"><div class="thumb" style="padding-left:3px;"></div><div class="wrapper-product-searchright"><a class="thumb-images" href="search.php?keyword_type=' . htmlspecialchars($_GET['keyword_type'] ?? '') . '&keywords=' . htmlspecialchars($rrow['pd_title']) . '&rctyp=Products"><img data-toggle="tooltip" data-placement="top" title="' . htmlspecialchars($rrow['pd_title']) . '" class="photo" src="upload/myproduct/' . htmlspecialchars($imgarr[0]) . '" data-large_photo="upload/myproduct/' . htmlspecialchars($imgarr[0]) . '"/></a>';
                                                if (!empty($rrow['pd_imagelogo'])) { $logoarr = explode(',', $rrow['pd_imagelogo']); $releted_pro .= '<a class="inner-search-right-img" href="search.php?keyword_type=' . htmlspecialchars($_GET['keyword_type'] ?? '') . '&keywords=' . htmlspecialchars($rrow['pd_title']) . '&rctyp=Products" style="width: auto;height: auto;"><img style="width: 35px;max-width: 100%;" class="photo" src="upload/myproduct/' . htmlspecialchars($logoarr[0]) . '" data-large_photo="upload/myproduct/' . htmlspecialchars($logoarr[0]) . '"/></a>'; }
                                                $releted_pro .= '</div></div>';
                                            }
                                            $rcoun++;
                                        }
                                        echo $releted_pro;
                                        ?>
                                    </div></div>
                                </div>
                            </div>
                            <div class="clearfix"> </div>
                        </div>
                    </div>
                </div>
                <?php
                /* Banner + "Display More" button logic (identical to original) */
                if (empty($catTopBanner)) {
                    $catTopBanner    = isset($_COOKIE['loc_id']) ? categoryAdsBanner($_COOKIE['loc_id'], $row['pd_subcat_id'], '', 'top') : categoryAdsBanner('', $row['pd_subcat_id'], '', 'top');
                    $checkTopCatBan  = explode('~~', $catTopBanner);
                }
                if (empty($catBottomBanner)) {
                    $catBottomBanner   = isset($_COOKIE['loc_id']) ? categoryAdsBanner($_COOKIE['loc_id'], $row['pd_subcat_id'], '', 'bottom') : categoryAdsBanner('', $row['pd_subcat_id'], '', 'bottom');
                    $checkBottomCatBan = explode('~~', $catBottomBanner);
                }
                if ($getSearchCount > 4) {
                    if ($catTopBanner !== '' && ($checkTopCatBan[0] ?? '') === 'top' && $countRec == 4) {
                        echo '<div class="row text-center" style="margin-top:20px; margin-bottom:20px;"><div class="advertise-div">' . $checkTopCatBan[1] . '<div class="clearfix"> </div></div></div>';
                    }
                    if ($catBottomBanner !== '' && ($checkBottomCatBan[0] ?? '') === 'bottom' && $countRec == $getSearchCount) {
                        echo '<div class="row text-center" style="margin-top:20px; margin-bottom:20px;"><div class="advertise-div">' . $checkBottomCatBan[1] . '<div class="clearfix"> </div></div></div>';
                    }
                    if ($countRec == $getSearchCount) {
                        $pages = max(2, (int)($_GET['page'] ?? 1) + 1);
                        $gettot_product1 = $gettot_product - ((int)($_GET['page'] ?? 1) * 30);
                        if ($gettot_product1 > $totalpage) {
                            echo '<div class="col-lg-12 text-center" style="padding:30px;"><a href="https://arab-mart.com/search.php?rctyp=' . urlencode($_GET['rctyp'] ?? '') . '&keywords=' . urlencode($_GET['keywords'] ?? '') . '&page=' . $pages . '"><button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px; font-weight:bolder;"> Display More Products / Services </button></a></div>';
                        }
                    }
                } else {
                    if ($catTopBanner !== '' && $countRec == $getSearchCount) {
                        echo '<div class="row text-center" style="margin-top:20px; margin-bottom:20px;"><div class="advertise-div">' . $checkTopCatBan[1] . '<div class="clearfix"> </div></div></div>';
                    }
                    if ($catBottomBanner !== '' && $countRec == $getSearchCount) {
                        echo '<div class="row text-center" style="margin-top:20px; margin-bottom:20px;"><div class="advertise-div">' . $checkBottomCatBan[1] . '<div class="clearfix"> </div></div></div>';
                        $pages = max(1, (int)($_GET['page'] ?? 1) + 1);
                        if ($countRec > $getSearchCount) {
                            echo '<div class="col-lg-12 text-center" style="padding:30px;"><a href="https://arab-mart.com/search.php?rctyp=' . urlencode($_GET['rctyp'] ?? '') . '&keywords=' . urlencode($_GET['keywords'] ?? '') . '&page=' . $pages . '"><button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px; font-weight:bolder;">Display More Products / Services </button></a></div>';
                        }
                    }
                }
                $countRec++;
            } // end while
        } else {
            if (isset($_POST['scity']) && $_POST['scity'] !== '') { ?>
                <table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%"><tr style="width:100%"><td valign="TOP" style="width:100%"><div class="sor">Sorry, your search for <b class="cb1"><?= htmlspecialchars($_POST['scity']) ?></b> did not match any Supplier.</div><div class="sug"><b>Suggestions:</b><ul><li>Check spellings of your search words </li><li>Try a different set of search words </li></ul></div><div style="clear: both;"><br><br></div></td></tr></table>
            <?php } elseif (isset($_POST['bsn_type'])) { ?>
                <table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%"><tr style="width:100%"><td valign="TOP" style="width:100%"><div class="sor">Sorry, your search for <b class="cb1">Business Type</b> did not match any Supplier.</div><div class="sug"><b>Suggestions:</b><ul><li>Try a different business type of search </li><li>Try to check more than one business types</li></ul></div><div style="clear: both;"><br><br></div></td></tr></table>
            <?php } else { ?>
                <table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%"><tr style="width:100%"><td valign="TOP" style="width:100%"><div class="sor">Sorry, your search for <b class="cb1"><?= htmlspecialchars($_GET['keywords'] ?? '') ?></b> did not match any Product.</div><div class="sug"><b>Suggestions:</b><ul><li>Check spellings of your search words </li><li>Try a different set of search words </li><li>Do not use very long search phrase </li><li>Use two or three words for best search results </li><li>Do not use special characters in your search </li><li>Do not use search words that are very specific (e.g., 20x25 mm tone &nbsp;&nbsp;tiles) </li><li>Select any other country to find your product.</li></ul></div><div style="clear: both;"><br><br></div></td></tr></table>
            <?php }
        }
        ?>
        </div>
        </div>

    <?php } else { /* ---- GRID VIEW ---- */ ?>
        <div class="row fond active-grid-option">
        <?php
        $kwE = _esc($keywords);
        $nkw = generateProdSearchString($keywords); $bnkw = generateProdSearchString_pro_sup($keywords);
        $sql_prd_total2 = "select *, MATCH (pd_title) AGAINST ('{$kwE}' IN BOOLEAN MODE) AS title_relevance from products as prod,measurement_unit_arabyos,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE {$nkw}) OR (bnsprof_compname LIKE {$bnkw})) and pd_currency=cn_id {$sql_pd_ck} and pd_status='1' and pd_image!='' AND plan_member_id.expiry_date > " . time() . " ORDER BY title_relevance DESC, FIELD(p_id,'5','4','3','15','0'), pd_title asc";
        $totalgridpage = 50; $startgridpage = 0;
        if (($_GET['page'] ?? 0) > 1) { $gridlimit = (($_GET['page'] - 1)) * $totalgridpage; $gridsetLimit = " LIMIT {$gridlimit},{$totalgridpage}"; }
        else { $gridlimit = $startgridpage; $gridsetLimit = " LIMIT {$gridlimit},{$totalgridpage}"; }
        $sql_prd_grid = "select *, MATCH (pd_title) AGAINST ('{$kwE}' IN BOOLEAN MODE) AS title_relevance from products as prod,measurement_unit_arabyos,country,business_profile,plan_member_id where bnsprof_uid = pd_uid and b_id = bnsprof_id and mu_id=pd_unit and ((pd_title LIKE {$nkw}) OR (bnsprof_compname LIKE {$bnkw})) and pd_currency=cn_id {$sql_pd_ck} and pd_status='1' and pd_image!='' AND plan_member_id.expiry_date > " . time() . " ORDER BY title_relevance DESC, FIELD(p_id,'5','4','3','15','0'), pd_title asc";
        // Apply webxtor fixes
        foreach (['sql_prd_total2', 'sql_prd_grid'] as $_var) {
            $$_var = preg_replace('#\bcountry,#msi', 'country c,', $$_var);
            $$_var = preg_replace('#^(.+?\b)where(.+)$#msi', '$1, city, country c2 where c2.cn_id=pd_currency AND bnsprof_city=ct_id AND $2', $$_var);
            $$_var = preg_replace('#pd_currency\s*=\s*cn_id#msi', 'cn_id=ct_cn_id', $$_var);
            $$_var = preg_replace('#([^.a-z_-])cn_(id|code)#msi', '$1c.cn_$2', $$_var);
            $$_var = str_replace('select c.cn_id from country where c.cn_code', 'select cn_id from country where cn_code', $$_var);
            $$_var = preg_replace('#\bcountry\.#msi', 'c.', $$_var);
        }
        $run_query_tot2  = _mqd($sql_prd_total2);
        $gettot_product2 = _nr($run_query_tot2);
        $run_query_g     = _mqd($sql_prd_grid);
        $getSearchCount  = _nr($run_query_g);

        if ($getSearchCount > 0) {
            $myfev       = [];
            $gridRecCount = 1;
            $sql_prd_grid .= $gridsetLimit;
            $run_query_g   = _mqd($sql_prd_grid);
            while ($row = _fa($run_query_g)) {
                $fevrow_icon = 0;
                $data = $userArrayRow_Result[$row['pd_uid']] ?? null;
                if ($data) {
                    $get_icon = _mqd("select smembership_plan.mst_icon as sponsericon , plan_member_id.* , smembership_icon_plan.mst_icon as producticon,smembership_icon_plan.mst_name as pplan from smembership_plan,plan_member_id , smembership_icon_plan where smembership_icon_plan.mp_id =plan_member_id.p_id and smembership_plan.mp_id =plan_member_id.p_id and plan_member_id.b_id = " . (int)$data['bnsprof_id']);
                    if (_nr($get_icon)) { $fevrow_icon = _fab($get_icon); }
                    $get_icon2 = _mqd("select icon_id, p_id from plan_member_id where b_id = " . (int)$data['bnsprof_id']);
                    $icon2     = _fab($get_icon2);
                    $get_icon1 = _mqd("select * from smembership_icon_plan where mp_id = " . (int)($icon2['icon_id'] ?? 0));
                    $icon1     = _fab($get_icon1);
                    $get_icon3 = _mqd("select * from smembership_plan where mp_id = " . (int)($icon2['p_id'] ?? 0));
                }
                ?>
                <div class="col-md-4 compared-box compared-box1 style_prevu_kit">
                    <div class="text-right" id="div-<?= (int)$row['pd_id'] ?>"></div>
                    <header style="padding: 15px 0;width: 100% !important;" class="titleLim box-2">
                        <span class="txt-blue">
                            <a href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/product-details.php?token=<?= rand(1000,9999) . md5($row['pd_id']) ?>&c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank" class="h4" style="font-weight:bold;"><?= highlight($row['pd_title'] ?? '', urlencode($_GET['keywords'] ?? '')) ?></a>
                        </span>
                    </header>
                    <figure class="img-box">
                        <div class="ara-links">
                            <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                <a href="javascript:void(0)" class="product_fav_btn" data="<?= (int)$row['pd_id'] ?>" onclick="showfavorite(<?= (int)$_SESSION['uid_indm'] ?>,<?= (int)$row['pd_id'] ?>); addfavorite(<?= (int)$row['pd_id'] ?>)" data-prod_id="<?= (int)$row['pd_id'] ?>" title="<?= htmlspecialchars($row['pd_title'] ?? '') ?>"><i class="fa fa-star" aria-hidden="true"></i> Favorite</a>
                            <?php } else { ?>
                                <a href="sign-in.php" class="product_fav_btn" title="<?= htmlspecialchars($row['pd_title'] ?? '') ?>"><i class="fa fa-star" aria-hidden="true"></i> Favorite</a>
                            <?php } ?>
                            <a href="javascript:void(0)" onClick="return addcompare(<?= (int)$row['pd_id'] ?>)" data-prod_id="<?= (int)$row['pd_id'] ?>" title="<?= htmlspecialchars($row['pd_title'] ?? '') ?>"><i class="fa fa-plus"></i> Compare</a>
                        </div>
                        <?php $pimg2 = explode(',', $row['pd_image'] ?? ''); ?>
                        <div class="zoomthis">
                            <?php if ($fevrow_icon) { ?><div class="ribbon"><img src="./admin/images/<?= htmlspecialchars($fevrow_icon['sponsericon'] ?? '') ?>"/></div><?php } elseif (_nr($get_icon3)) { $fevrow_icon3 = _fab($get_icon3); ?><div class="ribbon"><img src="./admin/images/<?= htmlspecialchars($fevrow_icon3['mst_icon'] ?? '') ?>"/></div><?php } ?>
                            <a href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/product-details.php?token=<?= rand(1000,9999) . md5($row['pd_id']) ?>&c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank" class="h4" style="font-weight:bold;"><span style="display: inline-block;height: auto; vertical-align: middle;"></span><?= "<img src='upload/myproduct/" . htmlspecialchars($pimg2[0]) . "' class='zoomthis' style='' alt='upload/myproduct/" . htmlspecialchars($pimg2[0]) . "'>" ?></a>
                        </div>
                        <?php if (!empty($row['pd_imagelogo'])) { $limg2 = explode(',', $row['pd_imagelogo']); ?><div class="zk" style="border: 1px solid #267abf;height: auto; width:100px;position: absolute;top: 121px;left: 1px;"><?= "<img style='width: auto; height: 50px;max-width:100%;' src='upload/myproduct/" . htmlspecialchars($limg2[0]) . "'>" ?></div><?php } ?>
                    </figure>
                    <section>
                        <table style="text-align: left;">
                            <?php if (!empty($data['bnsprof_compname'])) { ?>
                                <tr>
                                    <td><?php if ($fevrow_icon) { ?><a href="https://www.arab-mart.com/membership_plans.php"><img src="https://www.arab-mart.com/admin/images/<?= htmlspecialchars($fevrow_icon['producticon'] ?? '') ?>" title="<?= htmlspecialchars($fevrow_icon['pplan'] ?? '') ?>" width="25px" height="25px"/></a><?php } elseif (_nr($get_icon1)) { ?><a href="https://www.arab-mart.com/membership_plans.php"><img src="./admin/images/<?= htmlspecialchars($icon1['mst_icon'] ?? '') ?>" width="25px" height="25px" title="<?= htmlspecialchars($icon1['mst_name'] ?? '') ?>"/></a><?php } ?></td>
                                    <td colspan="1" style="float:left; white-space:nowrap;"><a href="company/profile.php?c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>" target="_blank" style="font-weight:bold;" title="<?= ucfirst($data['bnsprof_compname']) ?>"><?= ucfirst(substr($data['bnsprof_compname'], 0, 20) . '...') ?></a></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td><?php
                                    $countryId = $data['country'] ?? 0;
                                    $getCountryName = _fab(_mqd("SELECT * FROM `country` where cn_id='" . (int)$countryId . "'"));
                                    $stateId        = $data['ct_state'] ?? 0;
                                    $getStateName   = _fab(_mqd("SELECT * FROM `states` where state_id='" . (int)$stateId . "'"));
                                    if (!empty($getCountryName['cn_flag'])) {
                                        echo "<img src='images/country_flag/" . htmlspecialchars($getCountryName['cn_flag']) . "' alt='" . htmlspecialchars($getCountryName['cn_flag']) . "'/>";
                                    } else {
                                        $getCountryflag = _fab(_mqd("SELECT * FROM `country` where cn_id='" . (int)($_COOKIE['loc_id'] ?? 0) . "'"));
                                        echo "<img src='images/country_flag/" . htmlspecialchars($getCountryflag['cn_flag'] ?? '') . "' alt='" . htmlspecialchars($getCountryflag['cn_flag'] ?? '') . "'/>";
                                    }
                                ?></td>
                                <td><a href="javascript:void(0)" class="h5" style="font-weight:bold;"><?php
                                    $address = '';
                                    if (!empty($getCountryName['cn_name'])) $address .= $getCountryName['cn_name'] . '-';
                                    if (!empty($getStateName['state_name'])) $address .= $getStateName['state_name'] . '-';
                                    if (!empty($data['ct_name'])) $address .= $data['ct_name'];
                                    echo $address !== '' ? htmlspecialchars($address) : 'Not available';
                                ?></a></td>
                            </tr>
                            <tr class="height44">
                                <td colspan="2" style="color:#00F;"><?php
                                    $bnsprof_businesstype = $data['bnsprof_businesstype'] ?? '';
                                    $dataC = explode(',', $bnsprof_businesstype);
                                    if ($bnsprof_businesstype !== '') {
                                        $i = 1;
                                        foreach ($dataC as $r) { echo htmlspecialchars($userArrayRow_Type[$r] ?? ''); if ($i < count($dataC)) echo ', '; $i++; }
                                    } else { echo 'Not available'; }
                                ?></td>
                            </tr>
                            <tr><td><a href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/arab-mart.com/product-add.php"></a></td><td colspan="2"></td></tr>
                            <tr><td class="txt-light-gray" colspan="2" style="display: none;"><a href="<?= htmlspecialchars($data['bnsprof_website_alt'] ?? '') ?>"><?= htmlspecialchars($data['bnsprof_website_alt'] ?? '') ?></a></td><td class="padding-0"></td></tr>
                        </table>
                        <table class="webcast-new-table"><tbody>
                            <tr>
                                <td></td>
                                <td><big class="txt-bold txt-red"><?= (int)$row['pd_min_order_qty'] ?></big> <?= measurement_unit($row['pd_unit']) ?> (Min Order)</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><?php $symbol = _currency_symbol((string)($row['pd_currency'] ?? '')); ?>
                                    <big class="txt-bold txt-red"><a style="color: #d22027" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/arab-mart.com/product-add.php"><?= $symbol . ($row['pd_fob_price'] ?? '') . '~ ' . $symbol . ($row['pd_fob_price2'] ?? '') ?></a></big>
                                </td>
                            </tr>
                            <tr></tr>
                            <tr>
                                <?php $Countryphone2 = _fab(_mqd("SELECT * FROM `country` where cn_id = " . (int)($data['country'] ?? 0))); ?>
                                <td colspan="2" class="grid_mobile"><img src="images/mobile.png" width="25px"/> &nbsp;
                                    <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                        <a href="#" class="txt-black h4"><b><?= htmlspecialchars($Countryphone2['cn_ph'] ?? '') ?>-<?= user_info($data['bnsprof_uid'] ?? 0, 'mobile1') ?></b></a>
                                    <?php } else { ?><a class="txt-black h4" href="/sign-in.php#loginform"><b>Show number</b></a><?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td style="text-align: center;">
                                    <?php if (empty($_SESSION['uid_indm'])) { ?>
                                        <a data-enquiry="" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/sign-in.php"><button class="btn btn-sm btn-default btn-enquiry1 border-radius-0"><span style="font-weight:bold;">Send Enquiry</span></button></a>
                                    <?php } else {
                                        $geo_loc   = $location_geo_country[0] ?? '';
                                        $countryyyy = $_COOKIE['loc_id'] ?? '';
                                        if (($_GET['grid'] ?? '') === 'active') { ?>
                                            <a data-enquiry="" class="ajax" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest.php?id=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>&pid=<?= (int)$row['pd_id'] ?>&geo=<?= htmlspecialchars($geo_loc) ?>&conty=<?= htmlspecialchars($countryyyy) ?>&search=1" id="btn_ajax_send<?= (int)$row['pd_id'] ?>"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" style="font-weight:bold;">Send Enquiry</button></a>
                                        <?php } else { ?>
                                            <a data-enquiry="" class="ajax" href="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>/company/quotationRequest_supplier.php?id=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>&pid=<?= (int)$row['pd_id'] ?>&geo=<?= htmlspecialchars($geo_loc) ?>&conty=<?= htmlspecialchars($countryyyy) ?>&search=1"><button type="button" class="btn btn-sm btn-warning border-radius-0 btn-enquiry" id="btn_ajax_send<?= (int)$row['pd_id'] ?>" style="font-weight:bold;">Send Enquiry</button></a>
                                        <?php } } ?>
                                </td>
                            </tr>
                        </table>
                        <div class="chat-button"><a href="company/products.php?c=<?= rand(1000,9999) . md5($data['bnsprof_id'] ?? '') ?>&sc=<?= rand(10000,99999) . ($data['pd_subcat_id'] ?? '') ?>#<?= (int)$row['pd_id'] ?>"></a></div>
                    </section>
                </div>
                <?php
                if ($gridRecCount == $totalgridpage) {
                    $pages = max(2, (int)($_GET['page'] ?? 1) + 1);
                    echo '<div class="col-lg-12 text-center" style="padding:30px;"><a href="https://arab-mart.com/search.php?keywords=' . urlencode($_GET['keywords'] ?? '') . '&grid=' . urlencode($_GET['grid'] ?? '') . '&page=' . $pages . '"><button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px; font-weight:bolder;">Display More Products / Services </button></a></div>';
                }
                $gridRecCount++;
            }
        } else { ?>
            <table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%"><tr style="width:100%; text-align:left;"><td valign="TOP" style="width:100%"><div class="sor">Sorry, your search for <b class="cb1"><?= htmlspecialchars($_GET['keywords'] ?? '') ?></b> did not match any Product.</div><div class="sug"><b>Suggestions:</b><ul><li>Check spellings of your search words </li><li>Try a different set of search words </li><li>Do not use very long search phrase </li><li>Use two or three words for best search results </li><li>Do not use special characters in your search </li><li>Do not use search words that are very specific (e.g., 20x25 mm tone &nbsp;&nbsp;tiles) </li></ul></div><div style="clear: both;"><br><br></div></td></tr></table>
        <?php }
        ?>
        <div class="clearfix"></div>
        </div>
        </div>
    <?php }
} // end Products / non-Products outer if
?>

<!-- Modal (unchanged) -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Modal title</h4>
            </div>
            <div class="modal-body">hii</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<?php
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$chrome    = (strpos($userAgent, 'Chrome') !== false);
if ($chrome) {
    $style = !empty($_SESSION['uid_indm']) ? 'style="left:-144px;z-index: 99999;"' : 'style="left:-109px;z-index: 99999;"';
} else {
    $style = !empty($_SESSION['uid_indm']) ? 'style="left:-149px;z-index: 99999;"' : 'style="left:-116px;z-index: 99999;"';
}
?>
<input type="hidden" name="keyrcType" id="keyrcType" value="<?= htmlspecialchars($_GET['rctyp'] ?? '') ?>"/>
<input type="hidden" name="checkLoginUser" id="checkLoginUser" value="<?= htmlspecialchars($_SESSION['uid_indm'] ?? '') ?>"/>
<div id="boxes">
    <div style="left: 551.5px; width:10%; display: none; background-color: transparent;" id="dialog" class="window">
        <form action="/post-buy-req.php" method="post" id="post_buy_req" name="post_buy_req">
            <input name="keywords" value="<?= htmlspecialchars(trim($_GET['keywords'] ?? '')) ?>" type="hidden">
            <div class="modal postRequirement" tabindex="-1" aria-labelledby="myLargeModalLabel" style="display:block !important;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" style="background-color:transparent !important; height:0px !important; border:0px !important;">
                        <div class="email-close">x</div>
                        <div class="col-lg-12 popup-box" style="float:none;">
                            <img class="girl-img" <?= $style ?> src="images/girl1.png"/>
                            <div class="col-lg-12 popup-sub-box">
                                <header>
                                    <h3 style="color:#fff;">Submit Buy Requirement For</h3>
                                    <h3 style="color:#f58238;">"<?= htmlspecialchars(trim($_GET['keywords'] ?? '')) ?>"</h3>
                                </header>
                                <section class="col-lg-12">
                                    <div class="col-lg-12" style="padding:0px; border:1px solid #a094c7; position:relative;">
                                        <textarea required style="width:100%; max-width:100%; min-height:150px; max-height:150px; border:none; background-color:transparent; position:relative; z-index:5; font-size:12px;" id="table-input1" name="textAreaField"></textarea>
                                        <table id="sideAdTable1" style="width:100%; position:absolute; top:0px;">
                                            <tr><td><i class="fa fa-exclamation-triangle" style="color:#ba2025; font-size:18px;"></i></td><td class="h4" style="font-weight: bold; font-size: 15px;"> Enter Product/Service Specifications </td></tr>
                                            <tr><td></td><td style="font-size: 13px;">- Application of Product</td></tr>
                                            <tr><td></td><td style="font-size: 13px;">- Product Features</td></tr>
                                            <tr><td></td><td style="font-size: 13px;">- Material - Product Packaging</td></tr>
                                            <tr><td></td><td style="font-size: 13px;">- Any Special Requirement</td></tr>
                                        </table>
                                    </div>
                                    <div class="col-lg-12 margin-top-10 margin-bottom-10" style="padding: 0px; text-align: left;white-space: nowrap;z-index:9999;">
                                        <input name="recommendation" type="checkbox" value="1">
                                        <b style="padding:3px 5px; font-size:15px; font-weight:normal;">Send this category updates to my inbox.</b>
                                    </div>
                                    <div class="col-lg-12 margin-top-10 margin-bottom-10">
                                        <button class="btn btn-lg btn-warning" style="padding:3px 5px; font-size:17px;" id="getInstaQuote"><b class="txt-bold">Get Instant Quote Now</b><br><small>For many verified Suppliers </small></button>
                                    </div>
                                    <?php if (!empty($_SESSION['uid_indm'])) { ?>
                                        <div><ul>
                                            <li style="font-size:17px;">Your Contact Information</li>
                                            <li><?= htmlspecialchars(user_info($_SESSION['uid_indm'], 'fname')) ?>&nbsp;<?= htmlspecialchars(user_info($_SESSION['uid_indm'], 'lanme')) ?></li>
                                            <li><?= ucfirst(city_to_country(user_info($_SESSION['uid_indm'], 'bnsprof_city'))) ?> - <?= get_city_name(user_info($_SESSION['uid_indm'], 'bnsprof_city')) ?></li>
                                            <li>+<?= user_info($_SESSION['uid_indm'], 'country_ph_code') ?> <?= user_info($_SESSION['uid_indm'], 'mobile1') ?></li>
                                            <li><?= htmlspecialchars(user_info($_SESSION['uid_indm'], 'email')) ?></li>
                                        </ul></div>
                                    <?php } ?>
                                </section>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div style="width: 1478px; font-size: 32pt; color:white; height: 602px; display: none; opacity: 0.5;" id="mask"></div>
</div>

<style type="text/css">
    .zk:hover .zk { display: none; }
    .box .product_fav_btn {
        position: absolute; right: 10px; top: 5px;
        float: right; display: inline-block; color: gray;
        font-size: 16px; z-index: 99;
    }
</style>

<script>
    $(document).ready(function () {
        $('input[name=mst_type\\[\\]]').change(function () {
            var check = $(this).is(':checked');
            if (check) {
                $(".sor").html('Sorry, your search for business type did not match');
                $(".sug").html("<b>Suggestions:</b><ul><li>Check other business type to filter</li><li>Check one by one type</li></ul>");
            }
        });
        $(document).on('click', '.ajax', function () {
            $.colorbox({ href: $(this).attr('href'), open: true, iframe: true, width: '750px', height: '600px' });
            return false;
        });
    });
    jQuery(document).ready(function ($) {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
