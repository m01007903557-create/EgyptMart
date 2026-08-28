<?php
/**
 * اسم الملفcomp_quotationRequest.php

 * الوصف: إدارة الملف التجاري للشركة - إضافة وتحديث بيانات الشركة
 * الإصدار: 2.0.0
 * تاريخ التحديث: 2024-01-25
 * متطلبات PHP: 8.3
 */

// بدء تشغيل المخزن المؤقت وجلسة العمل
ob_start();
session_start();

// تضمين ملف الإعدادات المشتركة
require_once 'common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header('Location: sign-in.php');
    exit();
}

$uid = intval($_SESSION['uid_indm']);

/**
 * كلاس إدارة الملف التجاري للشركة
 */
class Editcat
{
    private $company_name;
    private $website_alt;
    private $bnsprof_businesstype;
    private $bnsprof_yoe;
    private $bnsprof_comemp;
    private $bnsprof_turnover;
    private $bnsprof_owntype;
    private $userd;
    private $msg;
    private $con; // اتصال قاعدة البيانات

    /**
     * constructor
     */
    public function __construct($db_connection)
    {
        $this->con = $db_connection;
        $this->msg = '';
    }

    /**
     * تعيين قيم الخصائص
     */
    public function setProperties($data, $user_id)
    {
        $this->company_name = isset($data['company_name']) ? trim($data['company_name']) : '';
        $this->website_alt = isset($data['website_alt']) ? trim($data['website_alt']) : '';
        $this->bnsprof_businesstype = isset($data['bnsprof_businesstype']) ? $data['bnsprof_businesstype'] : [];
        $this->bnsprof_yoe = isset($data['bnsprof_yoe']) ? trim($data['bnsprof_yoe']) : '';
        $this->bnsprof_comemp = isset($data['bnsprof_comemp']) ? trim($data['bnsprof_comemp']) : '';
        $this->bnsprof_turnover = isset($data['bnsprof_turnover']) ? trim($data['bnsprof_turnover']) : '';
        $this->bnsprof_owntype = isset($data['bnsprof_owntype']) ? trim($data['bnsprof_owntype']) : '';
        $this->userd = intval($user_id);
    }

    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool
    {
        // التحقق من صحة رابط الموقع
        if (!empty($this->website_alt) && !$this->validateUrl($this->website_alt)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال رابط صحيح للموقع</font>';
            return false;
        }

        // التحقق من أن سنة التأسيس رقمية
        if (!empty($this->bnsprof_yoe) && !is_numeric($this->bnsprof_yoe)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال قيمة رقمية لسنة التأسيس</font>';
            return false;
        }

        // التحقق من أن السنة في النطاق المنطقي (1900-2025)
        if (!empty($this->bnsprof_yoe)) {
            $year = intval($this->bnsprof_yoe);
            if ($year < 1900 || $year > 2025) {
                $this->msg = '<font color="#CC0000">الرجاء إدخال سنة صحيحة بين 1900 و 2025</font>';
                return false;
            }
        }

        return true;
    }

    /**
     * التحقق من صحة URL
     */
    private function validateUrl($url): bool
    {
        // إضافة http:// إذا لم تكن موجودة
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }
        
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * تحديث أو إضافة الملف التجاري
     */
    public function update(): void
    {
        // معالجة أنواع النشاط التجاري
        $btype = $this->processBusinessTypes();
        
        // التحقق من وجود سجل سابق
        $exists = $this->checkIfExists();
        
        if ($exists) {
            $this->updateExisting($btype);
        } else {
            $this->insertNew($btype);
        }
    }

    /**
     * معالجة أنواع النشاط التجاري
     */
    private function processBusinessTypes(): string
    {
        if (empty($this->bnsprof_businesstype) || !is_array($this->bnsprof_businesstype)) {
            return '';
        }

        // تنظيف المدخلات - تحويل إلى أرقام صحيحة
        $types = array_map('intval', $this->bnsprof_businesstype);
        
        // إزالة القيم المكررة والصفرية
        $types = array_filter($types, function($val) {
            return $val > 0;
        });
        
        return implode(',', array_unique($types));
    }

    /**
     * التحقق من وجود سجل سابق
     */
    private function checkIfExists(): bool
    {
        $sql = "SELECT bnsprof_id FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
        $stmt = mysqli_prepare($this->con, $sql);
        
        if (!$stmt) {
            error_log("خطأ في التحقق من وجود السجل: " . mysqli_error($this->con));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->userd);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);
        
        return $exists;
    }

    /**
     * تحديث سجل موجود
     */
    private function updateExisting($btype): void
    {
        $sql = "UPDATE business_profile SET 
                bnsprof_businesstype = ?,
                bnsprof_website_alt = ?,
                bnsprof_compname = ?,
                bnsprof_yoe = ?,
                bnsprof_comemp = ?,
                bnsprof_turnover = ?,
                bnsprof_owntype = ?
                WHERE bnsprof_uid = ?";

        $stmt = mysqli_prepare($this->con, $sql);
        
        if (!$stmt) {
            error_log("خطأ في تحضير استعلام التحديث: " . mysqli_error($this->con));
            $this->msg = '<div class="save bnr mt12" id="savemsg" style="color:red;"><strong>حدث خطأ في حفظ البيانات</strong></div>';
            return;
        }

        mysqli_stmt_bind_param(
            $stmt, 
            "sssssssi", 
            $btype, 
            $this->website_alt, 
            $this->company_name, 
            $this->bnsprof_yoe,
            $this->bnsprof_comemp, 
            $this->bnsprof_turnover, 
            $this->bnsprof_owntype, 
            $this->userd
        );

        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="save bnr mt12" id="savemsg"><strong>تم حفظ الملف التجاري بنجاح!</strong></div>';
        } else {
            error_log("خطأ في تنفيذ التحديث: " . mysqli_stmt_error($stmt));
            $this->msg = '<div class="save bnr mt12" id="savemsg" style="color:red;"><strong>حدث خطأ في حفظ البيانات</strong></div>';
        }

        mysqli_stmt_close($stmt);
    }

    /**
     * إضافة سجل جديد
     */
    private function insertNew($btype): void
    {
        $sql = "INSERT INTO business_profile SET
                bnsprof_uid = ?,
                bnsprof_businesstype = ?,
                bnsprof_website_alt = ?,
                bnsprof_compname = ?,
                bnsprof_yoe = ?,
                bnsprof_comemp = ?,
                bnsprof_turnover = ?,
                bnsprof_owntype = ?,
                bnsprof_creation_date = NOW()";

        $stmt = mysqli_prepare($this->con, $sql);
        
        if (!$stmt) {
            error_log("خطأ في تحضير استعلام الإدراج: " . mysqli_error($this->con));
            $this->msg = '<div class="save bnr mt12" id="savemsg" style="color:red;"><strong>حدث خطأ في حفظ البيانات</strong></div>';
            return;
        }

        mysqli_stmt_bind_param(
            $stmt, 
            "isssssss", 
            $this->userd, 
            $btype, 
            $this->website_alt, 
            $this->company_name, 
            $this->bnsprof_yoe,
            $this->bnsprof_comemp, 
            $this->bnsprof_turnover, 
            $this->bnsprof_owntype
        );

        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="save bnr mt12" id="savemsg"><strong>تم إنشاء الملف التجاري بنجاح!</strong></div>';
        } else {
            error_log("خطأ في تنفيذ الإدراج: " . mysqli_stmt_error($stmt));
            $this->msg = '<div class="save bnr mt12" id="savemsg" style="color:red;"><strong>حدث خطأ في حفظ البيانات</strong></div>';
        }

        mysqli_stmt_close($stmt);
    }

    /**
     * الحصول على رسالة النتيجة
     */
    public function getMessage(): string
    {
        return $this->msg;
    }
}

// معالجة بيانات الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$company_name = $_SESSION['company_name'] ?? '';
$website_alt = $_SESSION['website_alt'] ?? '';
$bnsprof_yoe = $_SESSION['bnsprof_yoe'] ?? '';
$bnsprof_comemp = $_SESSION['bnsprof_comemp'] ?? '';
$bnsprof_turnover = $_SESSION['bnsprof_turnover'] ?? '';
$bnsprof_owntype = $_SESSION['bnsprof_owntype'] ?? '';

// معالجة تقديم النموذج
if (isset($_POST['btnUpdate'])) {
    
    $ecms = new Editcat($con);
    $ecms->setProperties($_POST, $uid);
    
    if ($ecms->valid()) {
        $ecms->update();
    }
    
    $_SESSION['msg'] = $ecms->getMessage();
    header("Location: statutory-details.php");
    exit();
}

// جلب بيانات الملف التجاري الحالي
function getBusinessProfile($con, $uid) {
    $sql = "SELECT * FROM business_profile WHERE bnsprof_uid = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        error_log("خطأ في جلب الملف التجاري: " . mysqli_error($con));
        return null;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt);
    
    return $row;
}

$row = getBusinessProfile($con, $uid);
$bstyp = $row ? explode(",", $row->bnsprof_businesstype ?? '') : [];

/**
 * دالة الحصول على خيارات من قاعدة البيانات
 */
function getOptions($con, $table, $id_field, $title_field, $status_field) {
    $options = [];
    $sql = "SELECT {$id_field}, {$title_field} FROM {$table} WHERE {$status_field} = '1' ORDER BY {$title_field}";
    $result = mysqli_query($con, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_object($result)) {
            $options[] = $row;
        }
    }
    
    return $options;
}

// جلب الخيارات من قاعدة البيانات
$business_types = getOptions($con, 'business_type', 'bsntyp_id', 'bsntyp_title', 'bsntyp_status');
$employee_ranges = getOptions($con, 'employee_range', 'emprange_id', 'emprange_type', 'emprange_status');
$revenue_turnovers = getOptions($con, 'revenue_turnover', 'revturnover_id', 'revturnover_title', 'revturnover_status');
$ownership_types = getOptions($con, 'ownership_type', 'owntyp_id', 'owntyp_title', 'owntyp_status');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="rtl" lang="ar">
<head>
    <title><?php echo htmlspecialchars(getSiteTitle()); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle()); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/b-v-7.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/jquery.fileupload.css">
    
    <script language="javascript" type="text/javascript" src="js/jquery-1.7.min.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
    
    <script type="text/javascript">
    function validateForm() {
        var website_alt = document.getElementById('website_alt');
        var bnsprof_yoe = document.getElementById('bnsprof_yoe');
        var message = "";
        var valid = true;
        
        // التحقق من صحة رابط الموقع
        if (website_alt.value != '' && !website_alt.value.match(/^(https?:\/\/)?([a-z0-9-]+\.)+[a-z]{2,}(:[0-9]+)?(\/.*)?$/i)) {
            message = 'الرجاء إدخال رابط صحيح للموقع';
            website_alt.focus();
            valid = false;
        }
        // التحقق من أن سنة التأسيس رقمية
        else if (bnsprof_yoe.value != "" && isNaN(bnsprof_yoe.value)) {
            message = "الرجاء إدخال قيمة رقمية لسنة التأسيس";
            bnsprof_yoe.focus();
            valid = false;
        }
        // التحقق من نطاق السنة
        else if (bnsprof_yoe.value != "") {
            var year = parseInt(bnsprof_yoe.value);
            if (year < 1900 || year > 2025) {
                message = "الرجاء إدخال سنة صحيحة بين 1900 و 2025";
                bnsprof_yoe.focus();
                valid = false;
            }
        }
        
        if (!valid) {
            document.getElementById('updatemessage').style.color = "red";
            document.getElementById('updatemessage').innerHTML = message;
        }
        
        return valid;
    }

    function list_photo() {
        $.get("companylogo-list.php", {'uid': <?php echo $uid; ?>}, function(data) { 
            $('#list_photo').html(data); 
        });
    }

    function DelTempImage(imid) {
        if (confirm("هل أنت متأكد من حذف الشعار؟")) {
            $.get("del_companylogo.php", {imid: imid}, function(data) {
                list_photo();
            });
        }
    }

    function showImgButt() {
        $("#add_image1").show();
    }

    function hideImgButt() {
        $("#add_image1").hide();
    }

    jQuery(document).ready(function() {
        jQuery('#file_upload').fileupload({
            url: '/server/php/',
            maxNumberOfFiles: 1,
            dataType: 'json',
            done: function(e, data) {
                jQuery.each(data.result.files, function(index, file) {
                    jQuery.post("companylogo-update.php", {
                        'uid': '<?php echo $uid; ?>', 
                        'file': file.name 
                    }, function(data) {
                        list_photo();
                    });
                });
            }
        });
    });
    </script>
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
    <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName()); ?>" width="1" height="1"></div>
    
    <div class="inner_wrapper">
        <?php include 'includes/header_menu.php'; ?>
        
        <!-- القائمة الجانبية -->
        <?php include 'includes/left_menu.php'; ?>
        
        <div class="w56b f1 p2b p14 blr">
            <style type="text/css">
                .max{color:#fa5901; font-size:11px; margin-top:5px} 
                .s_u{width:144px} 
                .frm_a{width:95%; border:1px solid #e0f0fd; padding:10px}
                .label {color: #000 !important; direction: rtl; text-align: right;}
            </style>
            
            <div> 
                <h1 style="font-size: 22px; font-weight: bold; direction: rtl; text-align: right;" title="الملف التجاري">
                    إملأ بيانات بروفايل الشركة
                </h1>
            </div>
            
            <?php include 'includes/business-panel.php'; ?>
            
            <div id="re_link" class="utab" style="height:39px;">
                <span style="font-size: 14px;" class="f1" title="أكمل معلومات الشركة">
                    أكمل معلومات الشركة حتى تجتذب مشتريين حقيقيين
                </span>
            </div>

            <div class="clb px"></div>
            <div class="clb"></div>
            
            <div class="mt5">
                <div style="text-align:center;width:100%;padding:1%;" class="" id="updatemessage">
                    <?php echo $msg; ?>
                </div>
                
                <form style="margin:0px;" action="" method="POST" name="ModReg" onsubmit="return validateForm();">
                    <div class="frm_a clb" style="background-color:#FAF4FF">
                        <table align="center" border="0" cellpadding="4" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td class="label" style="text-align:right" width="135" title="اسم الشركة">
                                        الإسم التجارى للشركة
                                    </td>
                                    <td>
                                        <input maxlength="60" name="company_name" id="company_name" 
                                               value="<?php echo htmlspecialchars(user_info($uid, 'bnsprof_compname') ?? ''); ?>" 
                                               class="a_f rf" tabindex="1">
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label pt7" valign="top" title="شعار الشركة">
                                        لوجو الشركة
                                    </td>
                                    <td>
                                        <script type="text/javascript">list_photo();</script>
                                        <div id="queue">
                                            <div align="left" id="list_photo" class="line clearfix"></div>
                                        </div>
                                        <div class="upload_div" title="حمل شعار الشركة">
                                            <img style="float:left; margin-right:10px;" src="<?php echo BASE_URL; ?>/images/newaddlogo.jpg" alt="إضافة شعار">
                                            <input id="file_upload" type="file" name="files" style="cursor:pointer;">
                                            <span class="file_input" title="إضافة صورة">حمل لوجو الشركة</span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label" title="الموقع الإلكتروني">
                                        الموقع الإلكتروني للشركة
                                    </td>
                                    <td>
                                        <input class="a_f rf" maxlength="80" name="website_alt" id="website_alt" 
                                               value="<?php echo htmlspecialchars(user_info($uid, 'bnsprof_website_alt') ?? ''); ?>" 
                                               tabindex="3" dir="ltr" style="text-align:left;">
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label pt7" valign="top" title="نوع النشاط">
                                        نوع نشاط الشركة
                                    </td>
                                    <td>
                                        <table style="margin:0px;" cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                                <?php
                                                $count = 0;
                                                foreach ($business_types as $type) {
                                                    if ($count % 3 == 0) echo '<tr>';
                                                    ?>
                                                    <td class="fom3" style="text-align:right" valign="TOP" width="33%" height="20">
                                                        <input name="bnsprof_businesstype[]" 
                                                               value="<?php echo $type->bsntyp_id; ?>" 
                                                               tabindex="10" 
                                                               type="checkbox" 
                                                               <?php echo (in_array($type->bsntyp_id, $bstyp)) ? 'checked="checked"' : ''; ?>>
                                                        <?php echo htmlspecialchars($type->bsntyp_title); ?>
                                                    </td>
                                                    <?php
                                                    $count++;
                                                    if ($count % 3 == 0) echo '</tr>';
                                                }
                                                if ($count % 3 != 0) echo '</tr>';
                                                ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label" width="138" title="سنة التأسيس">
                                        سنة إنشاء الشركة
                                    </td>
                                    <td>
                                        <input maxlength="4" class="a_f rf" name="bnsprof_yoe" id="bnsprof_yoe" 
                                               value="<?php echo htmlspecialchars(user_info($uid, 'bnsprof_yoe') ?? ''); ?>"
                                               placeholder="مثال: 2020">
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label" title="عدد الموظفين">
                                        عدد الموظفين
                                    </td>
                                    <td>
                                        <select class="a_f em_p" size="1" name="bnsprof_comemp" id="bnsprof_comemp">
                                            <option value="">--- إختار ---</option>
                                            <?php foreach ($employee_ranges as $range): ?>
                                                <option value="<?php echo $range->emprange_id; ?>" 
                                                    <?php echo (user_info($uid, 'bnsprof_comemp') == $range->emprange_id) ? 'selected="selected"' : ''; ?>>
                                                    <?php echo htmlspecialchars($range->emprange_type); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label" title="العائد السنوي">
                                        العائد السنوي التقريبي
                                    </td>
                                    <td>
                                        <select class="a_f em_p" size="1" name="bnsprof_turnover" id="bnsprof_turnover">
                                            <option value="">--- إختار ---</option>
                                            <?php foreach ($revenue_turnovers as $turnover): ?>
                                                <option value="<?php echo $turnover->revturnover_id; ?>" 
                                                    <?php echo (user_info($uid, 'bnsprof_turnover') == $turnover->revturnover_id) ? 'selected="selected"' : ''; ?>>
                                                    <?php echo htmlspecialchars($turnover->revturnover_title); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="label" title="نوع الملكية">
                                        نوع ملكية الشركة
                                    </td>
                                    <td>
                                        <select class="a_f em_p" size="1" name="bnsprof_owntype" id="bnsprof_owntype">
                                            <option value="">--- إختار ---</option>
                                            <?php foreach ($ownership_types as $type): ?>
                                                <option value="<?php echo $type->owntyp_id; ?>" 
                                                    <?php echo (user_info($uid, 'bnsprof_owntype') == $type->owntyp_id) ? 'selected="selected"' : ''; ?>>
                                                    <?php echo htmlspecialchars($type->owntyp_title); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>&nbsp;</td>
                                    <td align="center">
                                        <input name="userd" id="userd" value="<?php echo $uid; ?>" tabindex="31" type="hidden">
                                        <input name="btnUpdate" id="btnUpdate" value="حفظ التغييرات" 
                                               class="saps mt5" tabindex="31" type="submit" style="padding:10px 30px;">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="clb">&nbsp;</div>
                    </div>
                </form>
            </div>

            <div><br></div>
            <div><br></div>
        </div>
        <div class="c3">&nbsp;</div>
    </div>
</div>

<!-- التذييل -->
<?php include 'includes/footer.php'; ?>

<?php
// إنهاء المخزن المؤقت وإرسال المحتوى
ob_end_flush();
?>