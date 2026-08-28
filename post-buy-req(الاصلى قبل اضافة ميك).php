<?php
declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// تسجيل الصفحة الحالية
$_SESSION['last_page'] = "post-buy-req.php";

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

// التحقق من تسجيل الدخول
if ($uid == 0) {
    $_SESSION['postKeyword'] = $_POST['keywords'] ?? '';
    $_SESSION['textAreaVal'] = $_POST['textAreaField'] ?? '';
    header("Location: sign-in.php");
    exit;
}

global $con;

// =============================================
// استرجاع بيانات البحث
// =============================================
$postKeyword = '';
$textAreaVal = '';

if (isset($_POST['keywords'])) {
    $postKeyword = trim($_POST['keywords']);
    $textAreaVal = trim($_POST['textAreaField'] ?? '');
} elseif (!empty($_SESSION['postKeyword'])) {
    $postKeyword = $_SESSION['postKeyword'];
    $textAreaVal = $_SESSION['textAreaVal'] ?? '';
    // يمكن إلغاء التعليق لحذف بيانات الجلسة بعد الاستخدام
    // unset($_SESSION['postKeyword'], $_SESSION['textAreaVal']);
}

// =============================================
// معالجة التوصية
// =============================================
if (isset($_POST['recommendation']) && $_POST['recommendation'] == 1 && !empty($postKeyword)) {
    $cat_sql = "SELECT pc.pc_id 
                FROM products p
                INNER JOIN product_category_arabyos pc ON pc.pc_id = p.pd_subcat_id
                WHERE p.pd_title = ? AND pc.pc_status = '1'
                LIMIT 1";
    
    $stmt_cat = mysqli_prepare($con, $cat_sql);
    mysqli_stmt_bind_param($stmt_cat, 's', $postKeyword);
    mysqli_stmt_execute($stmt_cat);
    $cat_result = mysqli_stmt_get_result($stmt_cat);
    $cat_row = mysqli_fetch_assoc($cat_result);
    mysqli_stmt_close($stmt_cat);
    
    if ($cat_row) {
        $key_cat_id = (int)$cat_row['pc_id'];
        
        $insert_sql = "INSERT INTO selloffer_alert_category (sac_usr_id, sac_pc_id, sac_updated_date) 
                       VALUES (?, ?, NOW())";
        $stmt_insert = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'ii', $uid, $key_cat_id);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
    }
}

// =============================================
// استرجاع بيانات الجلسة
// =============================================
$main_cat = $_SESSION['main_cat'] ?? '';
$pc_id = $_SESSION['pc_id'] ?? '';
$br_pc_id = $_SESSION['br_pc_id'] ?? '';
$br_pd_name = $_SESSION['br_pd_name'] ?? '';
$br_requirement = $_SESSION['br_requirement'] ?? '';
$br_estimate_qty = $_SESSION['br_estimate_qty'] ?? '';
$br_estimate_qty_unit = $_SESSION['br_estimate_qty_unit'] ?? '';

unset($_SESSION['main_cat'], $_SESSION['pc_id'], $_SESSION['br_pc_id'], 
      $_SESSION['br_pd_name'], $_SESSION['br_requirement'], 
      $_SESSION['br_estimate_qty'], $_SESSION['br_estimate_qty_unit']);

// =============================================
// كلاس إضافة المنتج
// =============================================
class AddProduct
{
    public $msg;
    public $main_cat;
    public $pc_id;
    public $br_pc_id;
    public $br_u_id;
    public $br_pd_name;
    public $br_requirement;
    public $br_estimate_qty;
    public $br_estimate_qty_unit;
    public $br_preferred_supplier_location;
    private $con;

    public function __construct($main_cat, $pc_id, $br_pc_id, $br_u_id, $br_pd_name, 
                                $br_requirement, $br_estimate_qty, $br_estimate_qty_unit, 
                                $br_preferred_supplier_location, $con)
    {
        $this->main_cat = $main_cat;
        $this->pc_id = $pc_id;
        $this->br_pc_id = $br_pc_id;
        $this->br_u_id = (int)$br_u_id;
        $this->br_pd_name = $br_pd_name;
        $this->br_requirement = $br_requirement;
        $this->br_estimate_qty = $br_estimate_qty;
        $this->br_estimate_qty_unit = $br_estimate_qty_unit;
        $this->br_preferred_supplier_location = $br_preferred_supplier_location;
        $this->con = $con;

        $_SESSION['main_cat'] = $this->main_cat;
        $_SESSION['pc_id'] = $this->pc_id;
        $_SESSION['br_pc_id'] = $this->br_pc_id;
        $_SESSION['br_pd_name'] = $this->br_pd_name;
        $_SESSION['br_requirement'] = $this->br_requirement;
        $_SESSION['br_estimate_qty'] = $this->br_estimate_qty;
        $_SESSION['br_estimate_qty_unit'] = $this->br_estimate_qty_unit;
        $_SESSION['br_preferred_supplier_location'] = $this->br_preferred_supplier_location;
    }

    public function valid(): bool
    {
        // جلب الكلمات الممنوعة
        $letters = [];
        $bad_sql = "SELECT bd_word FROM bad_word";
        $bad_result = mysqli_query($this->con, $bad_sql);
        while ($row = mysqli_fetch_assoc($bad_result)) {
            $letters[] = strtoupper($row['bd_word']);
        }

        $br_name = strtoupper($this->br_pd_name);
        $requirement = strtoupper($this->br_requirement);

        if ($this->br_pc_id == "") {
            $this->msg = '<font color="#FF0000">رجاء إدخال التصنيف الفرعى</font>';
            return false;
        } elseif ($this->br_pd_name == "") {
            $this->msg = '<font color="#FF0000">رجاء إدخال عنوان المنتج / الخدمة التى تريد بيعها</font>';
            return false;
        } elseif (!empty($this->br_pd_name)) {
            foreach ($letters as $val) {
                if (str_contains($br_name, $val)) {
                    $this->msg = "<font color='#FF0000'>You can't post words like '" . htmlspecialchars($val) . "' in Product / Service Name.</font>";
                    return false;
                }
            }
        } elseif ($this->br_requirement == "") {
            $this->msg = '<font color="#FF0000">رجاء كتابة تفاصيل طلب الشراء</font>';
            return false;
        } elseif (!empty($this->br_requirement)) {
            foreach ($letters as $val) {
                if (str_contains($requirement, $val)) {
                    $this->msg = "<font color='#CC0000'>You can't post words like '" . htmlspecialchars($val) . "' in Requirement.</font>";
                    return false;
                }
            }
        } elseif ($this->br_estimate_qty == "") {
            $this->msg = '<font color="#FF0000">رجاء إدخال الكمية</font>';
            return false;
        } elseif (!is_numeric($this->br_estimate_qty)) {
            $this->msg = '<font color="#FF0000">رجاء إدخال كمية صالحة</font>';
            return false;
        } elseif ($this->br_estimate_qty_unit == "") {
            $this->msg = '<font color="#FF0000">رجاء إدخال وحدة قياس المنتج</font>';
            return false;
        }

        return true;
    }

    public function add(): void
    {
        $imgFile = "";
if (isset($_POST['selected_gallery_image']) && !empty($_POST['selected_gallery_image'])) {
    $imgFile = $_POST['selected_gallery_image'];
    
    // ✅ تصحيح المسار للموقع العربي
    // إذا كان المسار يبدأ بـ "upload/" والمجلد الحالي مختلف
    if (strpos($_SERVER['HTTP_HOST'], 'egyptmart') !== false) {
        // الموقع العربي - قد يحتاج إلى تعديل المسار
        // تأكد من أن المسار صحيح من الجذر
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $imgFile)) {
            // حاول مسارات بديلة
            $paths_to_try = [
                $_SERVER['DOCUMENT_ROOT'] . '/public_html/' . $imgFile,
                $_SERVER['DOCUMENT_ROOT'] . '/../' . $imgFile,
            ];
            foreach ($paths_to_try as $path) {
                if (file_exists($path)) {
                    $imgFile = str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', $path);
                    break;
                }
            }
        }
    }
    
    error_log("✅ الصورة النهائية: " . $imgFile);
}

        // جلب الصورة المؤقتة
        $temp_sql = "SELECT tbi_image FROM temp_buyrequirement_image WHERE tbi_usr_id = ? LIMIT 1";
        $stmt_temp = mysqli_prepare($this->con, $temp_sql);
        mysqli_stmt_bind_param($stmt_temp, 'i', $this->br_u_id);
        mysqli_stmt_execute($stmt_temp);
        $temp_result = mysqli_stmt_get_result($stmt_temp);
        
        if (mysqli_num_rows($temp_result) > 0) {
            $temp_row = mysqli_fetch_assoc($temp_result);
            $imgFile = $temp_row['tbi_image'] ?? '';
            mysqli_stmt_close($stmt_temp);
            
            // حذف الصورة المؤقتة
            $delete_sql = "DELETE FROM temp_buyrequirement_image WHERE tbi_usr_id = ?";
            $stmt_delete = mysqli_prepare($this->con, $delete_sql);
            mysqli_stmt_bind_param($stmt_delete, 'i', $this->br_u_id);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);
        }

        // إدراج طلب الشراء
        $insert_sql = "INSERT INTO buy_requirement
                       (br_pc_id, br_u_id, br_pd_name, br_requirement, br_estimate_qty, 
                        br_estimate_qty_unit, br_preferred_supplier_location, br_pic, 
                        br_status, br_posting_date, br_updated_date)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, '1', NOW(), NOW())";
        
        $stmt_insert = mysqli_prepare($this->con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'iissdiss', 
            $this->br_pc_id,
            $this->br_u_id,
            $this->br_pd_name,
            $this->br_requirement,
            $this->br_estimate_qty,
            $this->br_estimate_qty_unit,
            $this->br_preferred_supplier_location,
            $imgFile
        );
        
        mysqli_stmt_execute($stmt_insert);
        $br_id = mysqli_insert_id($this->con);
        mysqli_stmt_close($stmt_insert);
        
        $_SESSION['new_br_id'] = $br_id;

        // مسح بيانات الجلسة
        unset($_SESSION['main_cat'], $_SESSION['pc_id'], $_SESSION['br_pc_id'],
              $_SESSION['br_pd_name'], $_SESSION['br_requirement'],
              $_SESSION['br_estimate_qty'], $_SESSION['br_estimate_qty_unit'],
              $_SESSION['br_preferred_supplier_location']);

        $this->msg = '<font color="#009900">Buy Request posted successfully.</font>';
    }
}

// =============================================
// معالجة نموذج الإرسال
// =============================================
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

if (isset($_POST['submitBuyReqButt'])) {
    $typeofselection = $_POST['typeofselection'] ?? 0;
    $keywordsFilter1 = trim($_POST['keywordsFilter1'] ?? '');
    $valid = false;

    if ($typeofselection) {
        $valid = true;
        
        if (empty($keywordsFilter1)) {
            $_SESSION['msg'] = '<font color="#CC0000">Kindly enter Keyword.</font>';
            header("Location: post-buy-req.php");
            exit;
        }

        $searchedproducts = $_SESSION['searchedproducts'] ?? [];

        if (empty($searchedproducts) || !array_key_exists($keywordsFilter1, $searchedproducts)) {
            $_SESSION['msg'] = '<font color="#CC0000">No category found with given keywords</font>';
            header("Location: post-buy-req.php");
            exit;
        }

        $keywordsParts = explode(">>", $keywordsFilter1);
        $keywordsFilterLast = end($keywordsParts);
        $tnd_pc_id = $searchedproducts[$keywordsFilterLast] ?? 0;
        
        $_POST['br_pc_id'] = $tnd_pc_id;
        $_POST['pc_id'] = $searchedproducts[$keywordsParts[1] ?? ''] ?? 0;
        $_POST['main_cat'] = $searchedproducts[$keywordsParts[0] ?? ''] ?? 0;

        if (!$tnd_pc_id) {
            $_SESSION['msg'] = '<font color="#CC0000">No category found with given keywords</font>';
            header("Location: post-buy-req.php");
            exit;
        }
    }

    $adn = new AddProduct(
        trim($_POST['main_cat'] ?? ''),
        trim($_POST['pc_id'] ?? ''),
        trim($_POST['br_pc_id'] ?? ''),
        $uid,
        trim($_POST['br_pd_name'] ?? ''),
        trim($_POST['br_requirement'] ?? ''),
        trim($_POST['br_estimate_qty'] ?? ''),
        trim($_POST['br_estimate_qty_unit'] ?? ''),
        trim($_POST['br_preferred_supplier_location'] ?? ''),
        $con
    );

    // إضافة إلى تنبيهات البيع
    $key_cat_id = (int)($_POST['br_pc_id'] ?? 0);
    if ($key_cat_id > 0) {
        $check_sql = "SELECT sac_id FROM selloffer_alert_category WHERE sac_pc_id = ? AND sac_usr_id = ? LIMIT 1";
        $stmt_check = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'ii', $key_cat_id, $uid);
        mysqli_stmt_execute($stmt_check);
        $check_result = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($check_result) == 0) {
            $insert_alert_sql = "INSERT INTO selloffer_alert_category (sac_usr_id, sac_pc_id, sac_updated_date) VALUES (?, ?, NOW())";
            $stmt_alert = mysqli_prepare($con, $insert_alert_sql);
            mysqli_stmt_bind_param($stmt_alert, 'ii', $uid, $key_cat_id);
            mysqli_stmt_execute($stmt_alert);
            mysqli_stmt_close($stmt_alert);
        }
        mysqli_stmt_close($stmt_check);
    }

    if ($adn->valid() || $valid) {
        $adn->add();
        
        // التحقق مرة أخرى وإضافة إلى تنبيهات البيع
        $check_again_sql = "SELECT sac_id FROM selloffer_alert_category WHERE sac_pc_id = ? AND sac_usr_id = ? LIMIT 1";
        $stmt_check2 = mysqli_prepare($con, $check_again_sql);
        mysqli_stmt_bind_param($stmt_check2, 'ii', $key_cat_id, $uid);
        mysqli_stmt_execute($stmt_check2);
        $check_result2 = mysqli_stmt_get_result($stmt_check2);
        
        if (mysqli_num_rows($check_result2) == 0) {
            $insert_again_sql = "INSERT INTO selloffer_alert_category (sac_usr_id, sac_pc_id, sac_updated_date) VALUES (?, ?, NOW())";
            $stmt_again = mysqli_prepare($con, $insert_again_sql);
            mysqli_stmt_bind_param($stmt_again, 'ii', $uid, $key_cat_id);
            mysqli_stmt_execute($stmt_again);
            mysqli_stmt_close($stmt_again);
        }
        mysqli_stmt_close($stmt_check2);
        
        header("Location: post-buy-req-info.php");
        exit;
    } else {
        $_SESSION['msg'] = $adn->msg;
        header("Location: post-buy-req.php");
        exit;
    }
}

// جلب التصنيفات الرئيسية
$main_categories = [];
$sql_mpc = "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = 0 AND pc_status = '1' ORDER BY pc_order, pc_name ASC";
$result_mpc = mysqli_query($con, $sql_mpc);
while ($row = mysqli_fetch_assoc($result_mpc)) {
    $main_categories[] = $row;
}

// جلب وحدات القياس
$units = [];
$sql_mu = "SELECT mu_id, mu_name FROM measurement_unit WHERE mu_status = '1' ORDER BY mu_name ASC";
$result_mu = mysqli_query($con, $sql_mu);
while ($row = mysqli_fetch_assoc($result_mu)) {
    $units[] = $row;
}

// جلب شهادة عميل عشوائية
$testimonial = null;
$testi_sql = "SELECT * FROM testimonials WHERE testi_type = 'buyer' AND testi_status = '1' ORDER BY RAND() LIMIT 1";
$testi_result = mysqli_query($con, $testi_sql);
if (mysqli_num_rows($testi_result) > 0) {
    $testimonial = mysqli_fetch_assoc($testi_result);
}

// تحديد التصنيف المحدد إذا كان هناك كلمة مفتاحية
$key_cat_id = 0;
$key_cat_name = '';
if (!empty($postKeyword)) {
    $key_sql = "SELECT pc.pc_id, pc.pc_name 
                FROM products p
                INNER JOIN product_category_arabyos pc ON pc.pc_id = p.pd_subcat_id
                WHERE p.pd_title = ? AND pc.pc_status = '1'
                LIMIT 1";
    $stmt_key = mysqli_prepare($con, $key_sql);
    mysqli_stmt_bind_param($stmt_key, 's', $postKeyword);
    mysqli_stmt_execute($stmt_key);
    $key_result = mysqli_stmt_get_result($stmt_key);
    $key_row = mysqli_fetch_assoc($key_result);
    if ($key_row) {
        $key_cat_id = (int)$key_row['pc_id'];
        $key_cat_name = htmlspecialchars($key_row['pc_name'] ?? '', ENT_QUOTES, 'UTF-8');
    }
    mysqli_stmt_close($stmt_key);
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
    
    <link href="css/eto-post-buy.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="css/colorbox.css" />
    <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
    
    <style>
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
        #blform input, select, textarea {
            height: 40px !important;
            vertical-align: middle;
        }
        .label {
            line-height: 3;
        }
        input[type='radio'] {
            transform: scale(2);
        }
        .ass_sub_radio label {
            padding: 10px;
            font-size: 15px;
        }
        #drop {
            background: #fff;
            cursor: pointer;
            padding: 10px;
            margin-left: 10px;
            margin-right: 10px;
            color: #207bc2;
        }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
    
    <script>
    function showCategory() {
        var pc_id = document.getElementById('main_cat').value;
        $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
            $('#pc_id').html(data);
            showsubcat();
        });
    }
    
    function showSubcat(id) {
        $.post("ajax-file/showSubcat.php", {id: id}, function(data) {
            $('#br_pc_id').html(data);
        });
    }
    
    $(document).ready(function() {
        setTimeout(function() {
            $(".mybs").attr("selected", "true");
            $(".mybs").change();
        }, 1000);
        
        $('input[type="text"]').focus(function() {
            $(this).addClass("blfs");
        });
        
        $('#br_requirement').focus(function() {
            $(this).addClass("blfs");
        });
        
        $('select').focus(function() {
            $(this).addClass("blfs");
        });
        
        $('input[type="text"]').blur(function() {
            $(this).removeClass("blfs");
        });
        
        $('#br_requirement').blur(function() {
            $(this).removeClass("blfs");
            var length = $(this).val().length;
            if (length < 50) {
                $('#err_desc').css('display', 'block');
            }
        });
        
        $('select').blur(function() {
            $(this).removeClass("blfs");
        });
        
        $(document).on('keyup', '#br_requirement', function(e) {
            var msgSpan = $(this).parents('li').find('#Charcount');
            var length = $(this).val().length;
            var msg = 4000 - length;
            msgSpan.empty().html(msg);
        });
        
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
    
    function validRequest() {
        var main_cat = document.getElementById('main_cat');
        var pc_id = document.getElementById('pc_id');
        var br_pc_id = document.getElementById('br_pc_id');
        var typeofselection = document.getElementById('typeofselection');
        var keywordsFilter1 = document.getElementById('keywordsFilter1');
        var br_pd_name = document.getElementById('br_pd_name');
        var br_requirement = document.getElementById('br_requirement');
        var br_estimate_qty = document.getElementById('br_estimate_qty');
        var br_estimate_qty_unit = document.getElementById('br_estimate_qty_unit');
        
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
            } else if (br_pc_id.value == '') {
                message = "Kindly select Sub-Category.";
                br_pc_id.focus();
                valid = false;
            }
        } else if (typeofselectionvalue && keywordsFilter1.value == '') {
            message = "Kindly enter valid Search for category";
            keywordsFilter1.focus();
            valid = false;
        }
        
        if (valid) {
            if (br_pd_name.value == '') {
                message = "Kindly enter Products / Services you are looking for.";
                br_pd_name.focus();
                valid = false;
            } else if (!isNaN(br_pd_name.value)) {
                message = "Kindly enter valid Products / Services you are looking for.";
                br_pd_name.focus();
                valid = false;
            } else if (br_requirement.value == "" || br_requirement.value == null) {
                message = "Kindly describe your Buying Requirements in detail.";
                br_requirement.focus();
                valid = false;
            } else if (br_requirement.value.length < 50) {
                message = "Your Buy Requirement description should not be less than 50 characters.";
                br_requirement.focus();
                valid = false;
            } else if (br_estimate_qty.value == '') {
                message = "Kindly enter Estimated Quantity.";
                br_estimate_qty.focus();
                valid = false;
            } else if (isNaN(br_estimate_qty.value)) {
                message = "Kindly enter valid Estimated Quantity.";
                br_estimate_qty.value = '';
                br_estimate_qty.focus();
                valid = false;
            } else if (br_estimate_qty_unit.value == '') {
                message = "Kindly select Estimated Quantity Unit.";
                br_estimate_qty_unit.focus();
                valid = false;
            }
        }
        
        if (!valid) {
            alert(message);
        }
        
        return valid;
    }
    
    function showTempPhoto(usr) {
        $.get("ajax-file/showTempBuyRequirementImage.php", {usr: usr}, function(data) {
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
    
    function usePhoto(id) 
    {
        var tbl = 'temp_buyrequirement_image';
        var usr = document.getElementById('br_u_id').value;
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
            'uploadScript': 'ajax-file/addTempBuyReqImg.php',
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
<body class="search-show-box post-buy-req">
    <div class="q_hm1" id="res-mob1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
      
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" height="1" width="1"></div>
        
        		<div class="inner_wrapper">	

        <!-- Menu -->
        <?php include __DIR__ . "/includes/header_menu.php"; ?>
        
        <p class="cb"></p>
        
        <div id="blform">
            <div style="margin:0px !important" class="hd fs5 c3 fw" id="fmHd" title="Tell us your Buy Requirement, Get Multiple Quotes">
                سجل هنا طلبات تسعير وتلقى أفضل تسعيرات
                <div class="eto-bg bp1 fmHd_a"></div>
            </div>
            
            <form name="postForm" method="post" action="" onsubmit="return validRequest();">
                <input type="hidden" id="br_u_id" name="br_u_id" value="<?php echo $uid; ?>">
                <input type="hidden" value="0" id="typeofselection" name="typeofselection">
                
                <div id="error_msg" style="display:<?php echo !empty($msg) ? 'block' : 'none'; ?>; position:relative; padding:10px 15px; margin:5px; text-align:left; font-family:arial; font-size:12px;" class="mt20">
                    <?php echo $msg; ?>
                </div>
                
                <div class="frm fl" style="background:#fff; width: calc(100% - 355px); padding:10px;">
                    <div id="req">
                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                            <tbody>
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td class="tabclose" onclick="searchcat()" id="scs" width="152" title="Search Categories">إبحث تلقائى</td>
                                                    <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
                                                    <td class="tabopen" onclick="beowswcat()" id="bcs" width="155" title="Browse Categories">إختار التصنيف</td>
                                                    <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <ul style="margin-top:12px;">
                            <!-- قسم البحث -->
                            <li class="scc" style="display:none;">
                                <p class="label">
                                    <label for="keywordsFilter1" style="width:100%;" title="Enter Keyword For category">أدخل كلمة البحث ودعه يبحث تلقائى</label>
                                </p>
                                <p class="wdh">
                                    <input role="textbox" class="txt ui-placeholder-input ui-autocomplete-input" 
                                           name="keywordsFilter1" id="keywordsFilter1" style="float:left; width:100%;" 
                                           class="add_post_buy_input" type="text" maxlength="60" size="33">
                                </p>
                            </li>
                            
                            <?php if (empty($postKeyword)): ?>
                            <!-- التصنيفات اليدوية -->
                            <li class="bcc">
                                <p class="label">
                                    <label for="buytitle" style="width:170px;" title="Main Category">إختار التصنيف العام</label>
                                </p>
                                <p class="wdh">
                                    <select id="main_cat" name="main_cat" class="ui-placeholder-input add_post_buy_input" 
                                            onchange="showCategory()" style="width:100%" 
                                            title="إختار - التصنيف العام - الذى يندرج تحته منتجك أو خدمتك">
                                        <option value="">-- إختار التصنيف العام --</option>
                                        <?php foreach ($main_categories as $cat): ?>
                                        <option value="<?php echo (int)$cat['pc_id']; ?>" <?php echo (isset($cat['pc_name']) && $cat['pc_name'] == 'Business Services') ? 'class="mybs"' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                            </li>
                            
                            <li class="bcc">
                                <p class="label">
                                    <label for="buytitle" style="width:170px;" title="Category">إختار التصنيف الرئيسى</label>
                                </p>
                                <p class="wdh">
                                    <select id="pc_id" name="pc_id" class="ui-placeholder-input" onchange="showSubcat(this.value)" 
                                            title="إختار - التصنيف - الذى يندرج تحته منتجك أو خدمتك">
                                        <option value="">-- إختار التصنيف الرئيسى --</option>
                                    </select>
                                    
                                    <select name="br_pc_id" id="br_pc_id" class="ui-placeholder-input" style="width:52%" title="إختار التصنيف الفرعى">
                                        <option value="">-- إختار التصنيف الفرعى --</option>
                                    </select>
                                </p>
                            </li>
                            <?php else: ?>
                            <!-- التصنيف من البحث -->
                            <input type="hidden" name="keywords" value="<?php echo htmlspecialchars($postKeyword, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="specs" value="<?php echo htmlspecialchars($textAreaVal, ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <li>
                                <p class="label">
                                    <label for="buytitle" style="width:170px;" title="Category">إختار التصنيف</label>
                                </p>
                                <p class="wdh">
                                    <select name="br_pc_id" id="br_pc_id" class="ui-placeholder-input">
                                        <option value="<?php echo $key_cat_id; ?>"><?php echo $key_cat_name; ?></option>
                                    </select>
                                </p>
                            </li>
                            <?php endif; ?>
                            
                            <!-- عنوان المنتج -->
                            <li>
                                <p class="label">
                                    <label for="buytitle" style="width:170px;" title="Product / Service">أكتب عنوان للمنتج أو الخدمة</label>
                                </p>
                                <p class="wdh">
                                    <input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" 
                                           class="ui-placeholder-input" placeholder=".. إدخل المنتج أو الخدمة التى تريد شرائها" 
                                           name="br_pd_name" id="br_pd_name" maxlength="100" style="width:100%;" 
                                           type="text" value="<?php echo htmlspecialchars($br_pd_name, ENT_QUOTES, 'UTF-8'); ?>">
                                </p>
                            </li>
                            
                            <!-- تفاصيل الطلب -->
                            <li style="margin-bottom:3px !important">
                                <p class="label fl">
                                    <label for="br_requirement" style="width:170px;" title="Requirement in detail">أكتب تفاصيل طلب شراء المنتج أو الخدمة</label>
                                </p>
                                <p class="fl wdh">
                                    <textarea class="ttp2" name="br_requirement" id="br_requirement" 
                                              maxlength="4000" style="width:100%; height:300px !important; resize:none"><?php 
                                        echo htmlspecialchars($textAreaVal ?: $br_requirement, ENT_QUOTES, 'UTF-8'); 
                                    ?></textarea>
                                    <span class="fr cb c5">
                                        <font id="Charcount" class="c4">4000</font> حروف الكتابة لاتقل عن
                                    </span>
                                    <span id="err_desc" style="display:none" class="em_tips fl mb10">Minimum 50 Characters.</span>
                                </p>
                            </li>
                            
                            <!-- الكمية التقديرية -->
                            <li>
                                <p class="label">
                                    <label for="qty" title="Estimated Quantity">الكمية التقديرية</label>
                                </p>
                                <p class="wdh">
                                    <input name="br_estimate_qty" id="br_estimate_qty" maxlength="200" 
                                           style="width:25%;" type="text" value="<?php echo htmlspecialchars($br_estimate_qty, ENT_QUOTES, 'UTF-8'); ?>">
                                    
                                    <select style="width:25%; margin-left:8px" name="br_estimate_qty_unit" id="br_estimate_qty_unit">
                                        <option selected="selected" value="">-- إختار وحدة القياس --</option>
                                        <?php foreach ($units as $unit): ?>
                                        <option style="color:#000;" value="<?php echo (int)$unit['mu_id']; ?>" 
                                                <?php echo ($br_estimate_qty_unit == $unit['mu_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($unit['mu_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                                <div class="cb mb10"></div>
                            </li>
                            
                            <!-- تفضيلات الموقع -->
                            <li>
                                <p class="label">
                                    <label for="buytitle" style="width:170px;" title="Location Preferences">حدد أماكن بيع المنتج</label>
                                </p>
                                <p class="wdh">
                                    <div style="vertical-align:bottom" class="ass_sub_radio">
                                        <span class="ass-radio-loaction">
                                            <input type="radio" id="br_preferred_supplier_location_1" name="br_preferred_supplier_location" value="abroad">
                                            <label style="top:0px;" title="Abroad Only">طلب الشراء من خارج بلدى</label>
                                        </span>
                                        <span class="ass-radio-loaction">
                                            <input type="radio" id="br_preferred_supplier_location_2" name="br_preferred_supplier_location" value="any" checked="checked">
                                            <label style="top:0px;" title="Abroad + Domestic">طلب الشراء من داخل بلدى وخارج بلدى</label>
                                        </span>
                                        <span class="ass-radio-loaction">
                                            <input type="radio" id="br_preferred_supplier_location_3" name="br_preferred_supplier_location" value="domestic">
                                            <label style="top:0px;" title="Domestic Only">طلب الشراء من داخل بلدى</label>
                                        </span>
                                        <span class="ass-radio-loaction">
                                            <input type="radio" id="br_preferred_supplier_location_4" name="br_preferred_supplier_location" value="my_city">
                                            <label style="top:0px;" title="My City Only">طلب الشراء من داخل مدينتى</label>
                                        </span>
                                    </div>
                                    <br>
                                </p>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- رفع الصور -->
                    
                   
                    <div style="display:block" id="contact_dtl">
                        <ul>
                            <li>
                                <p class="label">
                                    <label for="country_name" title="Upload Image">حمل صورة المنتج</label>
                                </p>
                                <p class="wdh">
                                    <table>
                                        <tr>
                                            <td>
                                                <div style="padding-left:5px; padding-top:0px;" id="img_disp">
                                                    <img src="upload/buy_requirement/no-image.png" id="6390059595_1" 
                                                         border="0" height="100" hspace="0" vspace="0" width="120" alt="Product Image">
                                                </div>
                                            </td>
                                            <td>
                                                <div id="drop" style="padding-left:10px; float:right">
                                                    <input type="file" id="file_upload" name="file_upload" style="border:none;">
                                                </div>
                                                <div id="queue"></div>
                                            </td>
                                            <td>
                                                <a class="ajax add_color_page" href="popup-imagegallery.php" 
                                                   style="font-weight:bold; text-decoration:none; color:#0000ff;" 
                                                   title="Select from Image Gallery">اختر من معرض الصور</a>
                                            </td>
                                        </tr>
                                    </table>
                      
                                </p>
                            </li>
                        </ul>
                    </div>
                    <p class="cb"></p>
                    
                    <div id="submitdiv">
                        <input name="frmsubmitbutton" value="login" type="hidden">
                        <input name="submitBuyReqButt" id="login" value="" type="SUBMIT" style="height:63px !important;">
                    </div>
                    
                    <div class="fl shd eto-bg">
                        <p class="sh1 eto-bg"></p>
                    </div>
                </div>
                
                <!-- الشريط الجانبي -->
                <div class="bnf fl mt10">
                    <div class="fl bnft fs1 mt10 c2">
                        <h4 class="fs4 c3 fw" title="Benefits for Buyers">فوائد نشر طلبات شراء</h4>
                        <p class="eto-bg hdbg mb10"></p>
                        <ul>
                            <li class="eto-bg bp2 ff">
                                <strong class="c5 fs3 fwn" title="Save Time">وفر وقت البحث عن موردين</strong><br>
                            </li>
                            <li class="eto-bg bp3 ff">
                                <strong class="c5 fs3 fwn" title="Select Less Responses">تلقى تسعيرات من موردين متحقق منهم</strong><br>
                            </li>
                            <li class="eto-bg bp4 ff">
                                <strong class="c5 fs3 fwn" title="Evaluate">قارن بين التسعيرات وإختار أقل سعر</strong><br>
                            </li>
                        </ul>
                    </div>
                    
                    <?php if ($testimonial): ?>
                    <div class="fl bnft fs1 c2" style="position:relative">
                        <p class="eto-bg hdbg mb10"></p>
                        <div id="slideshow">
                            <div class="xx1 lh" style="display:block;">
                                <div class="fl" style="padding:0px 5px">
                                    <img style='border-radius:30px; -webkit-box-shadow:2px 24px 14px -15px rgba(50,50,50,0.9); 
                                                -moz-box-shadow:2px 24px 14px -15px rgba(50,50,50,0.9); 
                                                box-shadow:2px 24px 14px -15px rgba(50,50,50,0.9);' 
                                         src="upload/testimonial_img/<?php echo htmlspecialchars($testimonial['testi_image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                         width="55" height="60" alt="<?php echo htmlspecialchars($testimonial['testi_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <strong class="fs2"><?php echo htmlspecialchars($testimonial['testi_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <?php echo htmlspecialchars(get_country_name((int)($testimonial['testi_cn_id'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                <p class="mt10 fs6">
                                    <em><?php echo htmlspecialchars(substr($testimonial['testi_details'] ?? '', 0, 120), ENT_QUOTES, 'UTF-8'); ?></em>
                                </p>
                                <?php if (strlen($testimonial['testi_details'] ?? '') > 120): ?>
                                <p class="c3 pa1 rm tr">
                                    <a class="fw c3" href="testimonial.php" target="_blank">Read More...</a>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div style="clear:both;"></div>
            </form>
            <p class="cb"></p>
        </div>
        
        <div class="cb"></div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// يمكن إلغاء التعليق لحذف بيانات الجلسة بعد الاستخدام
// unset($_SESSION['postKeyword'], $_SESSION['textAreaVal']);
?>