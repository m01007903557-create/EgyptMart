<?php
/**
 * File: post-sell-offer.php
 * Description: نموذج نشر عرض بيع جديد مع إمكانية البحث عن التصنيفات ورفع الصور
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "post-sell-offer.php";

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

// التحقق من تسجيل الدخول
if ($uid == 0) {
    // يمكن تخزين عنوان الصفحة للعودة إليها بعد تسجيل الدخول
    // $_SESSION['request_url'] = $_SERVER['REQUEST_URI'];
    header("Location: sign-in.php");
    exit;
}

global $con;

// =============================================
// استرجاع بيانات الجلسة
// =============================================
$pc_id = $_SESSION['pc_id'] ?? '';
$so_pc_id = $_SESSION['so_pc_id'] ?? '';
$so_service = $_SESSION['so_service'] ?? '';
$so_description = $_SESSION['so_description'] ?? '';
$so_validity = $_SESSION['so_validity'] ?? '';

unset($_SESSION['pc_id'], $_SESSION['so_pc_id'], $_SESSION['so_service'], 
      $_SESSION['so_description'], $_SESSION['so_validity']);

// =============================================
// كلاس إضافة عرض البيع
// =============================================
class AddSaleOffer
{
    public $msg;
    public $so_usr_id;
    public $main_cat;
    public $pc_id;
    public $so_pc_id;
    public $so_service;
    public $so_description;
    public $so_preferred_buyer_location;
    public $so_validity;
    private $con;

    public function __construct($so_usr_id, $main_cat, $pc_id, $so_pc_id, $so_service, 
                                $so_description, $so_preferred_buyer_location, $so_validity, $con)
    {
        $this->so_usr_id = (int)$so_usr_id;
        $this->main_cat = $main_cat;
        $this->pc_id = $pc_id;
        $this->so_pc_id = $so_pc_id;
        $this->so_service = $so_service;
        $this->so_description = $so_description;
        $this->so_preferred_buyer_location = $so_preferred_buyer_location;
        $this->so_validity = $so_validity;
        $this->con = $con;

        $_SESSION['main_cat'] = $this->main_cat;
        $_SESSION['pc_id'] = $this->pc_id;
        $_SESSION['so_pc_id'] = $this->so_pc_id;
        $_SESSION['so_service'] = $this->so_service;
        $_SESSION['so_description'] = $this->so_description;
        $_SESSION['so_preferred_buyer_location'] = $this->so_preferred_buyer_location;
        $_SESSION['so_validity'] = $this->so_validity;
    }

    public function checkBadWord(string $text): bool
    {
        $bad_sql = "SELECT bd_word FROM bad_word";
        $bad_result = mysqli_query($this->con, $bad_sql);
        
        while ($row = mysqli_fetch_assoc($bad_result)) {
            $word = strtoupper($row['bd_word']);
            if (str_contains($text, $word)) {
                return false;
            }
        }
        return true;
    }

    public function valid(): bool
    {
        if ($this->main_cat == "") {
            $this->msg = '<font color="#FF0000">Kindly select Main Category.</font>';
            return false;
        } elseif ($this->pc_id == "") {
            $this->msg = '<font color="#FF0000">Kindly select Category.</font>';
            return false;
        } elseif ($this->so_pc_id == "") {
            $this->msg = '<font color="#FF0000">Kindly select Sub-Category.</font>';
            return false;
        } elseif ($this->so_service == "") {
            $this->msg = '<font color="#FF0000">Kindly enter Products / Services you want to Sell.</font>';
            return false;
        } elseif (!$this->checkBadWord(strtoupper($this->so_service))) {
            $this->msg = "<font color='#FF0000'>You can't post this Product / Service Name. It contains some Bad words.</font>";
            return false;
        } elseif ($this->so_description == "") {
            $this->msg = '<font color="#FF0000">Kindly describe your Products / Services in detail.</font>';
            return false;
        } elseif (!$this->checkBadWord(strtoupper($this->so_description))) {
            $this->msg = "<font color='#FF0000'>You can't post this Product / Services in detail. It contains some Bad words.</font>";
            return false;
        }
        return true;
    }

    public function add(): void
    {
        $imgFile = "";
        
        // التحقق من وجود صورة مرفوعة
        if (isset($_FILES["so_pic"]) && $_FILES["so_pic"]["error"] == UPLOAD_ERR_OK) {
            $file_name = $_FILES['so_pic']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, ['jpg', 'jpeg', 'gif', 'png'])) {
                $this->so_pic = 'so-' . rand(0, 9999) . '_' . time() . '.' . $file_ext;
                $target_path = "upload/sale_offer/" . $this->so_pic;
                
                if (move_uploaded_file($_FILES["so_pic"]["tmp_name"], $target_path)) {
                    $imgFile = $this->so_pic;
                    
                    // إنشاء صورة مصغرة إذا كانت المكتبة موجودة
                    if (class_exists('SimpleImage')) {
                        try {
                            $thumb_path = "upload/sale_offer/thumb/" . $this->so_pic;
                            $img = new SimpleImage();
                            $img->load($target_path);
                            $img->resize(100, 80);
                            $img->save($thumb_path);
                        } catch (Exception $e) {
                            error_log("Thumbnail creation error: " . $e->getMessage());
                        }
                    }
                }
            }
        } else {
            // استخدام الصورة المؤقتة
            $temp_sql = "SELECT tsi_image FROM temp_selloffer_image WHERE tsi_usr_id = ? LIMIT 1";
            $stmt_temp = mysqli_prepare($this->con, $temp_sql);
            mysqli_stmt_bind_param($stmt_temp, 'i', $this->so_usr_id);
            mysqli_stmt_execute($stmt_temp);
            $temp_result = mysqli_stmt_get_result($stmt_temp);
            
            if (mysqli_num_rows($temp_result) > 0) {
                $temp_row = mysqli_fetch_assoc($temp_result);
                $imgFile = $temp_row['tsi_image'] ?? '';
                
                // حذف الصورة المؤقتة
                $delete_sql = "DELETE FROM temp_selloffer_image WHERE tsi_usr_id = ?";
                $stmt_delete = mysqli_prepare($this->con, $delete_sql);
                mysqli_stmt_bind_param($stmt_delete, 'i', $this->so_usr_id);
                mysqli_stmt_execute($stmt_delete);
                mysqli_stmt_close($stmt_delete);
            }
            mysqli_stmt_close($stmt_temp);
        }

        // إدراج عرض البيع
        $insert_sql = "INSERT INTO sale_offer
                       (so_usr_id, so_pc_id, so_service, so_description, so_preferred_buyer_location, 
                        so_validity, so_pic, so_approval_status, so_posting_date, so_updated_date)
                       VALUES (?, ?, ?, ?, ?, ?, ?, '0', NOW(), NOW())";

        $stmt_insert = mysqli_prepare($this->con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'iisssss', 
            $this->so_usr_id,
            $this->so_pc_id,
            $this->so_service,
            $this->so_description,
            $this->so_preferred_buyer_location,
            $this->so_validity,
            $imgFile
        );

        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);

        // مسح بيانات الجلسة
        unset($_SESSION['main_cat'], $_SESSION['pc_id'], $_SESSION['so_pc_id'],
              $_SESSION['so_service'], $_SESSION['so_description'],
              $_SESSION['so_preferred_buyer_location'], $_SESSION['so_validity']);

        $this->msg = '<font color="#009900">Sale Offer posted successfully.</font>';
    }
}

// =============================================
// معالجة نموذج الإرسال
// =============================================
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

if (isset($_POST['submitSaleOffrButt'])) {
    $typeofselection = $_POST['typeofselection'] ?? 0;
    $keywordsFilter1 = trim($_POST['keywordsFilter1'] ?? '');
    $valid = false;

    if ($typeofselection) {
        $valid = true;
        
        if (empty($keywordsFilter1)) {
            $_SESSION['msg'] = '<font color="#CC0000">Kindly enter Keyword.</font>';
            header("Location: post-sell-offer.php");
            exit;
        }

        $searchedproducts = $_SESSION['searchedproducts'] ?? [];

        if (empty($searchedproducts) || !array_key_exists($keywordsFilter1, $searchedproducts)) {
            $_SESSION['msg'] = '<font color="#CC0000">No category found with given keywords</font>';
            header("Location: post-sell-offer.php");
            exit;
        }

        $keywordsParts = explode(">>", $keywordsFilter1);
        $keywordsFilterLast = end($keywordsParts);
        $tnd_pc_id = $searchedproducts[$keywordsFilterLast] ?? 0;
        
        $_POST['so_pc_id'] = $tnd_pc_id;
        $_POST['pc_id'] = $searchedproducts[$keywordsParts[1] ?? ''] ?? 0;
        $_POST['main_cat'] = $searchedproducts[$keywordsParts[0] ?? ''] ?? 0;

        if (!$tnd_pc_id) {
            $_SESSION['msg'] = '<font color="#CC0000">No category found with given keywords</font>';
            header("Location: post-sell-offer.php");
            exit;
        }
    }

    $adn = new AddSaleOffer(
        $uid,
        trim($_POST['main_cat'] ?? ''),
        trim($_POST['pc_id'] ?? ''),
        trim($_POST['so_pc_id'] ?? ''),
        trim($_POST['so_service'] ?? ''),
        trim($_POST['so_description'] ?? ''),
        trim($_POST['so_preferred_buyer_location'] ?? ''),
        trim($_POST['so_validity'] ?? '30'),
        $con
    );

    // إضافة إلى تنبيهات الشراء
    $key_cat_id = (int)($_POST['so_pc_id'] ?? 0);
    if ($key_cat_id > 0) {
        $check_sql = "SELECT bac_id FROM buylead_alert_category WHERE bac_pc_id = ? AND bac_usr_id = ? LIMIT 1";
        $stmt_check = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'ii', $key_cat_id, $uid);
        mysqli_stmt_execute($stmt_check);
        $check_result = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($check_result) == 0) {
            $insert_alert_sql = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) VALUES (?, ?, NOW())";
            $stmt_alert = mysqli_prepare($con, $insert_alert_sql);
            mysqli_stmt_bind_param($stmt_alert, 'ii', $uid, $key_cat_id);
            mysqli_stmt_execute($stmt_alert);
            mysqli_stmt_close($stmt_alert);
        }
        mysqli_stmt_close($stmt_check);
    }

    if ($adn->valid() || $valid) {
        $adn->add();
        
        // التحقق مرة أخرى وإضافة إلى تنبيهات الشراء
        $check_again_sql = "SELECT bac_id FROM buylead_alert_category WHERE bac_pc_id = ? AND bac_usr_id = ? LIMIT 1";
        $stmt_check2 = mysqli_prepare($con, $check_again_sql);
        mysqli_stmt_bind_param($stmt_check2, 'ii', $key_cat_id, $uid);
        mysqli_stmt_execute($stmt_check2);
        $check_result2 = mysqli_stmt_get_result($stmt_check2);
        
        if (mysqli_num_rows($check_result2) == 0) {
            $insert_again_sql = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) VALUES (?, ?, NOW())";
            $stmt_again = mysqli_prepare($con, $insert_again_sql);
            mysqli_stmt_bind_param($stmt_again, 'ii', $uid, $key_cat_id);
            mysqli_stmt_execute($stmt_again);
            mysqli_stmt_close($stmt_again);
        }
        mysqli_stmt_close($stmt_check2);
        
        header("Location: post-sell-offer-res.php");
        exit;
    } else {
        $_SESSION['msg'] = $adn->msg;
        header("Location: post-sell-offer.php");
        exit;
    }
}

// جلب التصنيفات الرئيسية
$main_categories = [];
$sql_pc = "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = 0 AND pc_status = '1' ORDER BY pc_order, pc_name ASC";
$result_pc = mysqli_query($con, $sql_pc);
while ($row = mysqli_fetch_assoc($result_pc)) {
    $main_categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/eto-post-sell.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1.css" type="text/css" rel="stylesheet">
    <link href="css/c.css" type="text/css" rel="stylesheet">
    <link href="css/jquery.css" type="text/css" rel="stylesheet">
    <link href="css/ui.css" rel="stylesheet">
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/dir-new.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="css/colorbox.css" />
    <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
    
    <style>
        #login_frm1 {
            border: 1px solid #6F0000;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            padding: 5px;
            text-align: center;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            background-color: #DF0000;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#DF0000');
            background: -webkit-gradient(linear, left top, left bottom, from(#DF0000), to(#DF0000));
            background: -moz-linear-gradient(top, #DF0000, #DF0000);
            cursor: pointer;
            font-family: Arial, Helvetica, sans-serif;
        }
        .tabopen {
            border-collapse: collapse;
            border: 1px solid #6500CA;
            border-bottom: 0px;
            color: #9D0000;
            font-family: arial;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            padding-top: 4px;
            padding-bottom: 4px;
            background-color: #FAF4FF;
        }
        .tabclose {
            border-collapse: collapse;
            border: 1px solid #C2E6FF;
            background-color: #D2ECFF;
            color: #2161B8;
            font-family: arial;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            padding-top: 4px;
            padding-bottom: 4px;
            cursor: pointer;
        }
        .tabborder {
            border-collapse: collapse;
            border-bottom: 1px solid #6500CA;
        }
        .border_bottom {
            border-collapse: collapse;
            border: 1px solid #6500CA;
            border-top: 0px solid #6500CA;
        }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
    
    <script>
    $(document).ready(function() {
        showTempPhoto(<?php echo $uid; ?>);
        
        $('.ajax').on('click', function() {
            $.colorbox({
                href: $(this).attr('href'),
                open: true
            });
            return false;
        });
        
        $(".inline").colorbox({inline: true, width: "50%"});
        
        $('#keywordsFilter1').unbind().live('keyup', function() {
            var type11 = 'Products';
            $(this).autocomplete("autocomplete.php", {
                selectFirst: true,
                extraParams: {type: type11},
                width: 407
            }).result(function(event, data, formatted) {
                $("input#keywordsFilter1").val(data);
            });
        });
    });
    
    function showCategory() {
        var pc_id = document.getElementById('main_cat').value;
        $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
            $('#pc_id').html(data);
            showSubcat();
        });
    }
    
    function showSubcat() {
        var id = document.getElementById('pc_id').value;
        $.post("ajax-file/showSubcat.php", {id: id}, function(data) {
            $('#so_pc_id').html(data);
        });
    }
    
    function searchcat() {
        $("#scs").removeClass("tabclose").addClass("tabopen");
        $("#bcs").removeClass("tabopen").addClass("tabclose");
        $('#typeofselection').val(1);
        $(".bcc").css("display", "none");
        $(".scc").removeAttr('style');
    }
    
    function beowswcat() {
        $("#bcs").removeClass("tabclose").addClass("tabopen");
        $("#scs").removeClass("tabopen").addClass("tabclose");
        $('#typeofselection').val(0);
        $(".scc").css("display", "none");
        $(".bcc").removeAttr('style');
    }
    
    function validSaleOffer() {
        var typeofselection = document.getElementById('typeofselection');
        var keywordsFilter1 = document.getElementById('keywordsFilter1');
        var main_cat = document.getElementById('main_cat');
        var pc_id = document.getElementById('pc_id');
        var so_pc_id = document.getElementById('so_pc_id');
        var so_service = document.getElementById('so_service');
        var so_description = document.getElementById('so_description');
        
        var message = "";
        var valid = true;
        var typeofselectionvalue = parseInt(typeofselection.value);
        
        if (typeofselectionvalue == 0) {
            if (main_cat.value == '') {
                message = "Kindly select Main Category.";
                main_cat.focus();
                valid = false;
            } else if (pc_id.value == '') {
                message = "Kindly select Category.";
                pc_id.focus();
                valid = false;
            } else if (so_pc_id.value == '') {
                message = "Kindly select Sub-Category.";
                so_pc_id.focus();
                valid = false;
            }
        } else if (typeofselectionvalue && keywordsFilter1.value == '') {
            message = "Kindly enter valid Search for category";
            keywordsFilter1.focus();
            valid = false;
        }
        
        if (valid) {
            if (so_service.value == '') {
                message = "Kindly enter Products / Services you want to Sell.";
                so_service.focus();
                valid = false;
            } else if (so_description.value == '') {
                message = "Kindly describe your Products / Services in detail.";
                so_description.focus();
                valid = false;
            }
        }
        
        if (!valid) {
            alert(message);
        }
        
        return valid;
    }
    
    function showTempPhoto(usr) {
        $.get("ajax-file/showTempSaleofferImage.php", {usr: usr}, function(data) {
            $("#img_disp").html('<img src="' + data + '" alt="" height="100" width="125"/>');
        });
    }
    
    var imageBasket = [];
    
    function usePhotoToUpload(id) {
        if (jQuery.inArray(id, imageBasket) != -1) {
            imageBasket = $.grep(imageBasket, function(value) {
                return value != id;
            });
        } else {
            imageBasket.push(id);
        }
    }
    
    function usePhoto(id) {
        var tbl = 'temp_selloffer_image';
        var usr = document.getElementById('so_usr_id').value;
        
        if (imageBasket.length > 0) {
            id = imageBasket.pop();
        }
        
        $.post("ajax-file/addNewImgFrmGallery.php", {id: id, usr: usr, tbl: tbl}, function(data) {
            $('#cboxClose').click();
            $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="100" width="100"/>');
            
            setTimeout(function() {
                showTempPhoto(usr);
            }, 500);
        });
    }
    
    jQuery(function() {
        jQuery('#file_upload').uploadifive({
            'auto': true,
            'formData': {'usr': '<?php echo $uid; ?>'},
            'queueID': 'queue',
            'debug': false,
            'method': 'post',
            'uploadScript': 'ajax-file/addTempSOImg.php',
            'onAddQueueItem': function(file) {
                $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="125" width="125"/>');
            },
            'onUploadComplete': function(file, data) {
                showTempPhoto(<?php echo $uid; ?>);
            }
        });
    });
    </script>
</head>
<body class="search-show-box">
    <div class="hm1 bbc" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <br><br>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" width="1" height="1"></div>
        
        <div class="inner_wrapper">
            <!-- Menu -->
            <?php include __DIR__ . "/includes/header_menu.php"; ?>
            
            <!-- القائمة الجانبية اليسرى -->
            <div class="f1 w61n tb lh ml m2" id="lnav" style="display:block;">
                <ul class="nln1" style="margin:0px; padding:0px;" title="عروض البيع وطلبات الشراء">
                    <li style="border-bottom:medium none;" title="Hot Sell Offers">
                        <h3>إعلانات عروض البيع الخاصة</h3>
                    </li>
                    <li class="lp"><a href="post-sell-offer.php" title="Post a New Sell Offer">»&nbsp; أنشـر عروض بيـع جديـدة</a></li>
                    <li class="lp"><a href="manage-sell-offer.php" title="Manage Sell Offers">»&nbsp;إدارة عروض البيع</a></li>
                    
                    <li style="border-bottom:medium none;" title="Buy Lead Alerts">
                        <h3>إشعارات طلبات شراء الى بريدى</h3>
                    </li>
                    <li class="lp"><a href="manage-buylead-alert.php" title="Manage Buy Lead Alerts">»&nbsp;إدارة طلبات الشراء</a></li>
                </ul>
            </div>
            
            <!-- المحتوى الرئيسي -->
            <div class="w57 b1_m2 f1 wd797" id="ldiv">
                <div style="display:none;" id="hdbord" class=""></div>
                
                <table id="topstrip" style="text-align:left; display:none;" width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td class="sprite l_strip fl"></td>
                            <td class="sprite cntr_strip fl">
                                <table style="text-align:left;" width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td class="sprite icon1"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class="sprite r_strip" align="right"></td>
                        </tr>
                    </tbody>
                </table>
                
                <div id="div2" style="display:block;">
                    <div><img src="post-sell_offer_files/zero.gif" width="1" height="19"></div>
                    
                    <table width="100%" align="center">
                        <tbody>
                            <tr>
                                <td>
                                    <div align="left">
                                        <div class="tw2l fl" id="formmain" style="margin-left:8px; background-color:#FAF4FF">
                                            <div class="" id="lgn1" dir="rtl" style="text-align:right;">
                                                <p class="c-1 g2 fs bo1" title="Post Business Ads FREE" style="text-align:right;">
                                                    أنشر عروض بيع خاصة أو إعلانات للشركة
                                                    <span class="p6 q4 tm1 cbc fsz1"><i class="co"></i></span>
                                                </p>
                                                <p class="ts1 ptp"></p>
                                            </div>
                                            
                                            <div>
                                                <form method="post" name="postForm1" action="" onsubmit="return validSaleOffer();" enctype="multipart/form-data">
                                                    <div id="error_msg" style=""><?php echo $msg; ?></div>
                                                    
                                                    <input type="hidden" id="so_usr_id" name="so_usr_id" value="<?php echo $uid; ?>">
                                                    <input type="hidden" value="0" id="typeofselection" name="typeofselection">
                                                    
                                                    <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td class="tabclose" onclick="searchcat()" id="scs" width="152" title="Search Categories">حدد الأصناف تلقائيا</td>
                                                                                <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
                                                                                <td class="tabopen" onclick="beowswcat()" id="bcs" width="155" title="Browse Categories">تصفح وإختار الأصناف</td>
                                                                                <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    
                                                    <table class="frm mt5" width="100%">
                                                        <tbody>
                                                            <!-- قسم البحث -->
                                                            <tr class="scc" id="r0" style="display:none;">
                                                                <td valign="middle" width="30%">
                                                                    <p class="pd15">
                                                                        <b style="font-size:13px;"><font color="#E95801" title="Enter product keywords to find a category"></font></b>
                                                                    </p>
                                                                </td>
                                                                <td valign="TOP">
                                                                    <input role="textbox" class="txt ui-placeholder-input ui-autocomplete-input" 
                                                                           name="keywordsFilter1" id="keywordsFilter1" style="width:450px; float:left;" 
                                                                           type="text" maxlength="60" size="33">
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- التصنيف العام -->
                                                            <tr id="r0" style="height:48px;" class="bcc">
                                                                <td valign="middle" width="40%">
                                                                    <p class="pd15" title="Main Category:">
                                                                        <i>*</i><b>إختار التصنيف العام</b>
                                                                    </p>
                                                                </td>
                                                                <td valign="TOP">
                                                                    <select class="bd4 hw6 mr3 htb" id="main_cat" name="main_cat" 
                                                                            style="height:30px;" onchange="showCategory()" 
                                                                            title="إختار - التصنيف العام - الذى يندرج تحته منتجك أو خدمتك">
                                                                        <option value="">-- التصنيف العام --</option>
                                                                        <?php foreach ($main_categories as $cat): ?>
                                                                        <option value="<?php echo (int)$cat['pc_id']; ?>" 
                                                                                <?php echo ((int)$cat['pc_id'] == (int)$pc_id) ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($cat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                                        </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- التصنيف الرئيسي والفرعي -->
                                                            <tr id="r1" style="height:48px;" class="bcc">
                                                                <td valign="middle" width="40%">
                                                                    <p class="pd15" title="Category:">
                                                                        <i>*</i><b>إختار التصنيف الرئيسى</b>
                                                                    </p>
                                                                </td>
                                                                <td valign="TOP">
                                                                    <select class="bd4 hw6 mr3 htb" id="pc_id" name="pc_id" 
                                                                            style="height:30px;" onchange="showSubcat()" 
                                                                            title="إختار - التصنيف الرئيسى - الذى يندرج تحته منتجك أو خدمتك">
                                                                        <option value="">-- التصنيف الرئيسى --</option>
                                                                    </select>
                                                                    
                                                                    <select class="bd4 hw6 mr3 htb" id="so_pc_id" name="so_pc_id" 
                                                                            style="height:30px;" 
                                                                            title="إختار - التصنيف الفرعى - الذى يندرج تحته منتجك أو خدمتك">
                                                                        <option value="" title="Select Sub Category">-- إخـتار التصنيف الفرعى --</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- عنوان المنتج -->
                                                            <tr id="r2" style="height:48px;">
                                                                <td valign="TOP" width="40%">
                                                                    <p class="pd15" title="Products / Services you want to Sell:">
                                                                        <i>*</i><b>أكتب عنوان المنتج أو الخدمة التى تريد بيعها</b>
                                                                    </p>
                                                                    <img src="post-sell_offer_files/zero.gif" width="190" height="1">
                                                                </td>
                                                                <td valign="TOP">
                                                                    <input name="so_service" id="so_service" style="width:450px;" 
                                                                           class="bd4 hw6 mr3 htb" maxlength="90" 
                                                                           value="<?php echo htmlspecialchars($so_service, ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <div class="displayoff" id="hlp" style="line-height:14px; height:14px;"></div>
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- وصف المنتج -->
                                                            <tr id="r3">
                                                                <td valign="TOP" width="40%">
                                                                    <p class="pd15" title="Describe Your Products /Services in Detail:">
                                                                        <i>*</i><b>إوصف تفاصيل منتجك أو خدمتك بطريقة تساعد على جذب المشتريين</b>
                                                                        <br>
                                                                        <b class="q4"></b>
                                                                        <font class="co1" id="Charcount" color="#ff8000">2000</font>
                                                                        <b class="fwn cbc">Characters Remaining : عدد الحروف لايقل الوصف عن</b>
                                                                    </p>
                                                                </td>
                                                                <td onmouseover="document.getElementById('tt2').style.display='block';" 
                                                                    onmouseout="document.getElementById('tt2').style.display='none';" valign="TOP">
                                                                    <div id="lgn6" style="width:360px; height:105px;">
                                                                        <textarea aria-hidden="true" name="so_description" id="so_description" 
                                                                                  style="max-width:450px; width:450px; height:95px; max-height:95px; display:block;" 
                                                                                  rows="5" cols="30"><?php echo htmlspecialchars($so_description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            
                                                            <!-- تفضيلات الموقع -->
                                                            <tr id="r4">
                                                                <td valign="TOP" width="40%">
                                                                    <p class="pd15" title="Location Preferences:"><b>حدد أماكن بيع المنتج / الخدمة</b></p>
                                                                </td>
                                                                <td valign="TOP">
                                                                    <div style="vertical-align:bottom">
                                                                        <input type="radio" id="so_preferred_buyer_location_1" name="so_preferred_buyer_location" value="abroad">
                                                                        <label style="top:0px;" title="Abroad Only">هذا المنتج للتصدير فقط</label>&nbsp;&nbsp;
                                                                        
                                                                        <input type="radio" id="so_preferred_buyer_location_2" name="so_preferred_buyer_location" value="any" checked="checked">
                                                                        <label style="top:0px;" title="Abroad + Domestic">هذا المنتج للتصدير أو للبيع الداخلى</label>&nbsp;&nbsp;
                                                                        
                                                                        <input type="radio" id="so_preferred_buyer_location_3" name="so_preferred_buyer_location" value="domestic">
                                                                        <label style="top:0px;" title="Domestic Only">هذا المنتج للبيع داخل بلدى فقط</label>&nbsp;&nbsp;
                                                                        
                                                                        <input type="radio" id="so_preferred_buyer_location_4" name="so_preferred_buyer_location" value="my_city">
                                                                        <label style="top:0px;" title="My City Only">هذا المنتج للبيع داخل مدينتى فقط</label>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                                                            <tr><td class="j1"><i class="co fl" style="height:26px;"></i></td></tr>
                                                            
                                                            <tr><td colspan="2">&nbsp;</td></tr>
                                                            
                                                            <!-- رفع الصور -->
                                                            <tr id="r4">
                                                                <td class="pb1 pt2" valign="top">
                                                                    <b class="q4"></b><b>Product Picture:</b><br/>
                                                                    (Upload Images in .jpg, .jpeg, .png or .gif file format)
                                                                </td>
                                                                <td class="s pb" align="left">
                                                                    <table width="100%">
                                                                        <tr>
                                                                            <td>
                                                                                <div id="main" class="po-com1">
                                                                                    <div style="padding-left:18px; padding-top:5px;" id="img_disp">
                                                                                        <img src="https://egyptmart.shop/upload/sale_offer/no-image.png" 
                                                                                             id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125" 
                                                                                             alt="Product Image">
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <div id="drop" style="padding-left:10px; float:right">
                                                                                    <input type="file" id="file_upload" name="file_upload">
                                                                                </div>
                                                                                <div id="queue"></div>
                                                                            </td>
                                                                            <td>
                                                                                <a class="ajax" href="popup-imagegallery.php" style="text-decoration:none;">
                                                                                    Select from Image Gallery
                                                                                </a>
                                                                                
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    
                                                    <!-- مدة عرض الإعلان -->
                                                    <p id="add_dtl" style="padding:5px 13px; color:#0000ff; cursor:pointer; float:right" 
                                                       onclick="javascript:document.getElementById('dtl').style.display='block'; this.style.display='none';" 
                                                       title="Additional Information">
                                                        <b style="color:#0000ff;">+</b> حدد مدة عرض الإعلان
                                                    </p>
                                                    
                                                    <div style="display:none" id="dtl">
                                                        <table class="frm" width="100%">
                                                            <tbody>
                                                                <tr id="r20">
                                                                    <td>
                                                                        <b class="q4" title="Validity of your product:"></b>حدد مدة عرض الاعلان<b></b>
                                                                    </td>
                                                                    <td class="v">
                                                                        <input name="so_validity" value="30" type="radio" title="1 Month"> شهر واحد
                                                                        <input name="so_validity" value="90" type="radio"> ثلاثة شهور
                                                                        <input name="so_validity" value="365" checked="checked" type="radio"> سنة كاملة
                                                                        <span class="cc j1" style="display:block; margin-left:6px">(مدة عرض الاعلان التى اخترتها)</span>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                    <br>
                                                    
                                                    <!-- زر الإرسال -->
                                                    <div class="a2 pt pb" id="loginsubmit" style="display:block;">
                                                        <input name="frmsubmitbutton" value="login" type="hidden">
                                                        <input name="submitSaleOffrButt" id="login_frm1" class="cr bo1 fsz1" 
                                                               style="height:32px; width:170px;" title="Submit your Offer" 
                                                               value="أنشـر الإعــلان للبيــع" type="SUBMIT">
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p_rl" id="slempform" style="display:none; font-family:arial; font-weight:bold; padding:30px 0px 0px 0px; text-align:center; color:#FF6000; font-size:16px; height:200px;">
                    <nobr>You do not have privilege to access this section</nobr>
                </div>
                
                <div><br><br><br></div>
            </div>
            
            <div style="clear:both;"><br><br>&nbsp;&nbsp;</div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>