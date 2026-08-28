
<?php
// بدء التخزين المؤقت للمخرجات
ob_start();

// بدء الجلسة وإعداد التخزين المؤقت
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_cache_limiter(false);

// تضمين الملفات الأساسية
require_once 'common.php';

// تهيئة متغير معرف المستخدم
$uid = '';

// التحقق من وجود المستخدم في الجلسة
if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    $uid = (int)$_SESSION['uid_indm'];
}

// التحقق من وجود المستخدم في الكوكيز (إذا لم يكن في الجلسة)
if (empty($uid) && isset($_COOKIE['cook_usr_id']) && !empty($_COOKIE['cook_usr_id'])) {
    $uid = (int)$_COOKIE['cook_usr_id'];
}

// الحصول على معلومات موقع المستخدم (للإصدارات السابقة)
$location = [];
$location = getLocationInfoByIp();

// إذا لم يكن هناك مستخدم مسجل دخوله، توجيه إلى صفحة تسجيل الدخول
if (empty($uid)) {
    header("Location: sign-in.php");
    exit();
}

// ==============================================
// دوال مساعدة للبحث (محسنة لـ PHP 8.3)
// ==============================================

/**
 * توليد سلسلة بحث للمنتجات
 * @param string $keywords الكلمات المفتاحية للبحث
 * @return string سلسلة البحث المعدة
 */
function generateProdSearchString(string $keywords): string {
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", trim($keywords));
    
    foreach ($key_array as $v) {
        $v = mysqli_real_escape_string($GLOBALS['con'], $v);
        if ($i > 0) {
            $keywords_string .= " OR pd_title LIKE ";
        }
        $keywords_string .= "'%" . $v . "%'";
        $i++;
    }
    return $keywords_string;
}

/**
 * توليد سلسلة بحث للموردين (باسم الشركة)
 * @param string $keywords الكلمات المفتاحية للبحث
 * @return string سلسلة البحث المعدة
 */
function generateSupplierSearchString(string $keywords): string {
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", trim($keywords));
    
    foreach ($key_array as $v) {
        $v = mysqli_real_escape_string($GLOBALS['con'], $v);
        if ($i > 0) {
            $keywords_string .= " OR bnsprof_compname LIKE ";
        }
        $keywords_string .= "'%" . $v . "%'";
        $i++;
    }
    return $keywords_string;
}

/**
 * توليد سلسلة بحث للموردين (باسم المسؤول)
 * @param string $keywords الكلمات المفتاحية للبحث
 * @return string سلسلة البحث المعدة
 */
function generateSupplierNameSearchString(string $keywords): string {
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", trim($keywords));
    
    foreach ($key_array as $v) {
        $v = mysqli_real_escape_string($GLOBALS['con'], $v);
        if ($i > 0) {
            $keywords_string .= " OR (user.fname LIKE '%" . $v . "%' OR user.lname LIKE '%" . $v . "%')";
        } else {
            $keywords_string .= " (user.fname LIKE '%" . $v . "%' OR user.lname LIKE '%" . $v . "%')";
        }
        $i++;
    }
    return $keywords_string;
}

/**
 * توليد سلسلة بحث لطلبات الشراء
 * @param string $keywords الكلمات المفتاحية للبحث
 * @return string سلسلة البحث المعدة
 */
function generateBuyleadSearchString(string $keywords): string {
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", trim($keywords));
    
    foreach ($key_array as $v) {
        $v = mysqli_real_escape_string($GLOBALS['con'], $v);
        if ($i > 0) {
            $keywords_string .= " OR br_pd_name LIKE '%" . $v . "%' OR br_requirement LIKE '%" . $v . "%'";
        } else {
            $keywords_string .= "br_pd_name LIKE '%" . $v . "%' OR br_requirement LIKE '%" . $v . "%'";
        }
        $i++;
    }
    return $keywords_string;
}

/**
 * توليد سلسلة بحث للمناقصات
 * @param string $keywords الكلمات المفتاحية للبحث
 * @return string سلسلة البحث المعدة
 */
function generateTenderSearchString(string $keywords): string {
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", trim($keywords));
    
    foreach ($key_array as $v) {
        $v = mysqli_real_escape_string($GLOBALS['con'], $v);
        if ($i > 0) {
            $keywords_string .= " OR tnd_heading LIKE '%" . $v . "%' OR tnd_details LIKE '%" . $v . "%'";
        } else {
            $keywords_string .= "tnd_heading LIKE '%" . $v . "%' OR tnd_details LIKE '%" . $v . "%'";
        }
        $i++;
    }
    return $keywords_string;
}

/**
 * توليد سلسلة بحث للمزادات
 * @param string $keywords الكلمات المفتاحية للبحث
 * @return string سلسلة البحث المعدة
 */
function generateAuctionSearchString(string $keywords): string {
    $i = 0;
    $keywords_string = "";
    $key_array = explode(" ", trim($keywords));
    
    foreach ($key_array as $v) {
        $v = mysqli_real_escape_string($GLOBALS['con'], $v);
        if ($i > 0) {
            $keywords_string .= " OR auc_heading LIKE '%" . $v . "%' OR auc_details LIKE '%" . $v . "%'";
        } else {
            $keywords_string .= "auc_heading LIKE '%" . $v . "%' OR auc_details LIKE '%" . $v . "%'";
        }
        $i++;
    }
    return $keywords_string;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>المفضلة | <?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="zomImage/js/jquery-photo-enlarger/css/jquery-photo-enlarger.css" rel="stylesheet" type="text/css">
<link href="css/slidebars.css" rel="stylesheet" type="text/css">
<link href="css/ctstyle.css?t=<?php echo rand(); ?>" rel="stylesheet" type="text/css">
<link href="css/im-style-v1.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
<script language="javascript" type="text/javascript" src="js/jquery-1.11.1.min.js"></script>

<script type="text/javascript">
/**
 * تحميل المنتجات حسب الفئة
 * @param {number} page - رقم الصفحة
 * @param {number} id - معرف الفئة
 */
function loadProductByCategory(page, id) {
    $.post("ajax-file/loadProductByCategory.php", {page: page, id: id}, function(data) {
        $('#res').html(data);
    }).fail(function() {
        alert("حدث خطأ في تحميل المنتجات");
    });
}

/**
 * تحميل المنتجات حسب الفئة الفرعية
 * @param {number} page - رقم الصفحة
 * @param {number} id - معرف الفئة الفرعية
 */
function loadProductBySubCategory(page, id) {
    $.post("ajax-file/loadProductBySubCategory.php", {page: page, id: id}, function(data) {
        $('#res').html(data);
    }).fail(function() {
        alert("حدث خطأ في تحميل المنتجات");
    });
}

/**
 * تحسين نتائج المنتجات حسب الفئة الفرعية
 * @param {number} page - رقم الصفحة
 * @param {number} id - معرف الفئة الفرعية
 */
function refineProductBySubCategory(page, id) {
    $.post("ajax-file/refineProductBySubCategory.php", {page: page, id: id}, function(data) {
        $('#final_result').html(data);
    }).fail(function() {
        alert("حدث خطأ في تحسين النتائج");
    });
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
        
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

/**
 * حذف المنتجات المحددة من المفضلة
 */
function delprod() {
    $('.checkbox').each(function() {
        if (this.checked) {
            var pro_id = $(this).attr('id');
            $.ajax({
                type: 'POST',
                url: "delete_fav_pro.php",
                data: {"pro_id": pro_id},
                success: function(response) {
                    if (response == true || response == "true" || response == 1) {
                        $('#prod_block' + pro_id).hide();
                    }
                },
                error: function() {
                    alert("حدث خطأ في حذف المنتج");
                }
            });
        }
    });
}

/**
 * حذف منتج معين من المفضلة
 * @param {string} user - معرف المستخدم
 * @param {number} id - معرف المنتج
 */
function delprodfav(user, id) {
    $.ajax({
        type: 'POST',
        url: "delete_fav_pro.php",
        data: {"pro_id": id},
        success: function(response) {
            if (response == true || response == "true" || response == 1) {
                $('#prod_block' + id).hide();
            }
        },
        error: function() {
            alert("حدث خطأ في حذف المنتج");
        }
    });
}

$(document).ready(function() {
    // تحميل الشرائح إذا كانت الصفحة تحتوي على عناصر محددة
    if ($("#product_slider").length > 0) {
        $("#product_slider").als({
            visible_items: 4,
            scrolling_items: 1,
            orientation: "horizontal",
            circular: "yes",
            autoscroll: "yes",
            interval: 4000
        });
    }
    
    if ($("#saleoffer_slider").length > 0) {
        $("#saleoffer_slider").als({
            visible_items: 4,
            scrolling_items: 1,
            orientation: "horizontal",
            circular: "yes",
            autoscroll: "yes",
            interval: 4500
        });
    }
    
    // معالجة تغيير حالة "تحديد الكل"
    $("#select_all").change(function() {
        var status = this.checked;
        $('.checkbox').each(function() {
            this.checked = status;
        });
    });
    
    // تحميل المحتوى بناءً على معلمات URL
    <?php if (isset($_GET['c']) && !empty($_GET['c'])): ?>
        loadProductByCategory(1, '<?php echo (int)$_GET['c']; ?>');
    <?php endif; ?>
    
    <?php if (isset($_GET['sc']) && !empty($_GET['sc'])): ?>
        loadProductBySubCategory(1, '<?php echo (int)$_GET['sc']; ?>');
    <?php endif; ?>
});
</script>

<script type="text/javascript" src="js/jquery.als-1.6.js"></script>

<style>
/*************************************
 * أنماط ALS للشرائح
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
    float: left;
}
.als-prev, .als-next {
    position: absolute;
    cursor: pointer;
    clear: both;
}

/*************************************
 * أنماط خاصة بـ #product_slider
 ************************************/
#product_slider {
    margin: 2px auto;
}
#product_slider .als-item {
    margin: 0px 5px;
    padding: 4px 0px;
    min-height: 140px;
    min-width: 122px;
    text-align: center;
}
#product_slider .als-item img {
    display: block;
    margin: 0 auto;
    vertical-align: middle;
    max-width: 100%;
    height: auto;
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

/*************************************
 * أنماط خاصة بـ #saleoffer_slider
 ************************************/
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
    max-width: 100%;
    height: auto;
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

.goog-te-gadget-simple .goog-te-menu-value {
    font-size: 10px;
}

.closeCls {
    color: black;
}
</style>
</head>

<body>
<div id="fb-root"></div>
<script>
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>

<?php
// تحديد ترتيب عرض الفئات
if (get_page_settings('25') == 'manual') {
    $sql_order = " ORDER BY pc_order, pc_name";
} else {
    $sql_order = " ORDER BY pc_name";
}

// تضمين رأس الصفحة
if (file_exists("./includes/header_new.php")) {
    include("./includes/header_new.php");
}
?>

<p class="bt cb">
    <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" width="1" height="1">
</p>

<div class="hm1 bbc">
    <!-- القائمة الجانبية اليسرى - معطلة حالياً -->
    <?php //include_once 'index_leftsidebar.php'; ?>

    <div class="comparemainblock">
        <?php 
        // عرض محتوى المفضلة إذا كان المستخدم مسجلاً دخوله
        if (!empty($uid)) {
            if (file_exists('fav_middle_content.php')) {
                include_once 'fav_middle_content.php';
            } else {
                echo '<div class="alert alert-warning">ملف المحتوى غير موجود</div>';
            }
        } else {
            // تم توجيه المستخدم في بداية الملف، ولكن هذا للاحتياط
            header('Location: sign-in.php');
            exit();
        }
        ?>
    </div>

    <!-- القائمة الجانبية اليمنى - معطلة حالياً -->
    <?php //include_once 'index_rightsidebar.php'; ?>

    <br>
    <!-- تعليقات فيسبوك وتويتر - معطلة حالياً -->
</div>

<p class="cb"><br></p>

<?php
// تضمين تذييل الصفحة
if (file_exists('includes/footer.php')) {
    include 'includes/footer.php';
}
?>
<script>
        function trimLeft(c) {
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            return c;
        }
    </script>
    
</body>
</html>

<?php
// ===== 6. إرسال المخرجات =====
ob_end_flush();
?>