<?php
/**
 * File: statutory-details.php

 * Description: إدارة البيانات القانونية للشركة (أرقام التسجيل، الضرائب، إلخ)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

global $con;

// =============================================
// كلاس إدارة البيانات القانونية
// =============================================
class EditBusinessDetails
{
    public $bnsprof_regno;
    public $bnsprof_regauthority;
    public $bnsprof_cin_no;
    public $bnsprof_tan_no;
    public $bnsprof_pan_no;
    public $bnsprof_svtax_no;
    public $bnsprof_excisereg_no;
    public $bnsprof_vat_no;
    public $bnsprof_ie_code;
    public $bnsprof_cst_no;
    public $bnsprof_msme_no;
    public $bnsprof_epf_no;
    public $bnsprof_esi_no;
    public $bnsprof_sct_no;
    public $bnsprof_dnb_no;
    public $bnsprof_rbi_no;
    public $bnsprof_fssailic_no;
    public $bnsprof_nsic_no;
    public $bnsprof_sst_no;
    public $user_id;
    public $msg;

    public function __construct()
    {
    }

    public function validate(): bool
    {
        if (!empty($this->bnsprof_regauthority) && empty($this->bnsprof_regno)) {
            $this->msg = '<font color="#CC0000">Registration Number is compulsory with Registration Authority.</font>';
            return false;
        } elseif (!empty($this->bnsprof_regno) && empty($this->bnsprof_regauthority)) {
            $this->msg = '<font color="#CC0000">Registration Authority is compulsory with Registration Number.</font>';
            return false;
        }
        return true;
    }

    public function update(): void
    {
        global $con;
        
        // التحقق من وجود سجل للشركة
        $check_sql = "SELECT bnsprof_id FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
        $stmt_check = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'i', $this->user_id);
        mysqli_stmt_execute($stmt_check);
        $check_result = mysqli_stmt_get_result($stmt_check);
        $exists = mysqli_num_rows($check_result) > 0;
        mysqli_stmt_close($stmt_check);

        if ($exists) {
            // تحديث السجل الموجود
            $sql = "UPDATE business_profile SET
                    bnsprof_regno = ?,
                    bnsprof_regauthority = ?,
                    bnsprof_cin_no = ?,
                    bnsprof_tan_no = ?,
                    bnsprof_pan_no = ?,
                    bnsprof_svtax_no = ?,
                    bnsprof_excisereg_no = ?,
                    bnsprof_vat_no = ?,
                    bnsprof_ie_code = ?,
                    bnsprof_cst_no = ?,
                    bnsprof_msme_no = ?,
                    bnsprof_epf_no = ?,
                    bnsprof_esi_no = ?,
                    bnsprof_sct_no = ?,
                    bnsprof_dnb_no = ?,
                    bnsprof_rbi_no = ?,
                    bnsprof_fssailic_no = ?,
                    bnsprof_nsic_no = ?,
                    bnsprof_sst_no = ?
                    WHERE bnsprof_uid = ?";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, 'sssssssssssssssssssi', 
                $this->bnsprof_regno,
                $this->bnsprof_regauthority,
                $this->bnsprof_cin_no,
                $this->bnsprof_tan_no,
                $this->bnsprof_pan_no,
                $this->bnsprof_svtax_no,
                $this->bnsprof_excisereg_no,
                $this->bnsprof_vat_no,
                $this->bnsprof_ie_code,
                $this->bnsprof_cst_no,
                $this->bnsprof_msme_no,
                $this->bnsprof_epf_no,
                $this->bnsprof_esi_no,
                $this->bnsprof_sct_no,
                $this->bnsprof_dnb_no,
                $this->bnsprof_rbi_no,
                $this->bnsprof_fssailic_no,
                $this->bnsprof_nsic_no,
                $this->bnsprof_sst_no,
                $this->user_id
            );
        } else {
            // إدراج سجل جديد
            $sql = "INSERT INTO business_profile SET
                    bnsprof_uid = ?,
                    bnsprof_regno = ?,
                    bnsprof_regauthority = ?,
                    bnsprof_cin_no = ?,
                    bnsprof_tan_no = ?,
                    bnsprof_pan_no = ?,
                    bnsprof_svtax_no = ?,
                    bnsprof_excisereg_no = ?,
                    bnsprof_vat_no = ?,
                    bnsprof_ie_code = ?,
                    bnsprof_cst_no = ?,
                    bnsprof_msme_no = ?,
                    bnsprof_epf_no = ?,
                    bnsprof_esi_no = ?,
                    bnsprof_sct_no = ?,
                    bnsprof_dnb_no = ?,
                    bnsprof_rbi_no = ?,
                    bnsprof_fssailic_no = ?,
                    bnsprof_nsic_no = ?,
                    bnsprof_sst_no = ?,
                    bnsprof_creation_date = NOW()";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, 'isssssssssssssssssss', 
                $this->user_id,
                $this->bnsprof_regno,
                $this->bnsprof_regauthority,
                $this->bnsprof_cin_no,
                $this->bnsprof_tan_no,
                $this->bnsprof_pan_no,
                $this->bnsprof_svtax_no,
                $this->bnsprof_excisereg_no,
                $this->bnsprof_vat_no,
                $this->bnsprof_ie_code,
                $this->bnsprof_cst_no,
                $this->bnsprof_msme_no,
                $this->bnsprof_epf_no,
                $this->bnsprof_esi_no,
                $this->bnsprof_sct_no,
                $this->bnsprof_dnb_no,
                $this->bnsprof_rbi_no,
                $this->bnsprof_fssailic_no,
                $this->bnsprof_nsic_no,
                $this->bnsprof_sst_no
            );
        }

        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="save bnr mt12" id="savemsg"><strong>Statutory Details saved successfully!</strong></div>';
        } else {
            error_log("Business Details Update Error: " . mysqli_error($con) . " | User ID: " . $this->user_id);
            $this->msg = '<div class="error bnr mt12" style="color:red;"><strong>Failed to save details. Please try again.</strong></div>';
        }
        mysqli_stmt_close($stmt);
    }
}

// =============================================
// معالجة تحديث البيانات
// =============================================
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

if (isset($_POST['btnUpdate'])) {
    $editor = new EditBusinessDetails();
    
    $editor->bnsprof_regno = trim($_POST['bnsprof_regno'] ?? '');
    $editor->bnsprof_regauthority = trim($_POST['bnsprof_regauthority'] ?? '');
    $editor->bnsprof_cin_no = trim($_POST['bnsprof_cin_no'] ?? '');
    $editor->bnsprof_tan_no = trim($_POST['bnsprof_tan_no'] ?? '');
    $editor->bnsprof_pan_no = trim($_POST['bnsprof_pan_no'] ?? '');
    $editor->bnsprof_svtax_no = trim($_POST['bnsprof_svtax_no'] ?? '');
    $editor->bnsprof_excisereg_no = trim($_POST['bnsprof_excisereg_no'] ?? '');
    $editor->bnsprof_vat_no = trim($_POST['bnsprof_vat_no'] ?? '');
    $editor->bnsprof_ie_code = trim($_POST['bnsprof_ie_code'] ?? '');
    $editor->bnsprof_cst_no = trim($_POST['bnsprof_cst_no'] ?? '');
    $editor->bnsprof_msme_no = trim($_POST['bnsprof_msme_no'] ?? '');
    $editor->bnsprof_epf_no = trim($_POST['bnsprof_epf_no'] ?? '');
    $editor->bnsprof_esi_no = trim($_POST['bnsprof_esi_no'] ?? '');
    $editor->bnsprof_sct_no = trim($_POST['bnsprof_sct_no'] ?? '');
    $editor->bnsprof_dnb_no = trim($_POST['bnsprof_dnb_no'] ?? '');
    $editor->bnsprof_rbi_no = trim($_POST['bnsprof_rbi_no'] ?? '');
    $editor->bnsprof_fssailic_no = trim($_POST['bnsprof_fssailic_no'] ?? '');
    $editor->bnsprof_nsic_no = trim($_POST['bnsprof_nsic_no'] ?? '');
    $editor->bnsprof_sst_no = trim($_POST['bnsprof_sst_no'] ?? '');
    $editor->user_id = (int)$_POST['userd'];

    if ($editor->user_id != $uid) {
        die("Invalid user ID");
    }

    if ($editor->validate()) {
        $editor->update();
        $_SESSION['msg'] = $editor->msg;
        header("Location: myproduct-buy.php");
        exit;
    } else {
        $_SESSION['msg'] = $editor->msg;
        header("Location: business-details.php");
        exit;
    }
}

// جلب البيانات الحالية
$row = null;
$sql = "SELECT * FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_object($result);
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/b-v-7.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
    
    <style>
        .label { color: #000 !important; }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
    
    <script>
    function chkalldetails() {
        var bnsprof_regno = document.getElementById('bnsprof_regno');
        var bnsprof_regauthority = document.getElementById('bnsprof_regauthority');
        var message = "";
        var valid = true;
        
        if (bnsprof_regauthority.value != '' && bnsprof_regno.value == '') {
            message = 'Registration Number is compulsory with Registration Authority.';
            bnsprof_regno.focus();
            valid = false;
        } else if (bnsprof_regno.value != "" && bnsprof_regauthority.value == '') {
            message = "Registration Authority is compulsory with Registration Number.";
            bnsprof_regauthority.focus();
            valid = false;
        }
        
        if (!valid) {
            document.getElementById('updatemessage').style.color = "red";
            document.getElementById('updatemessage').innerHTML = message;
        }
        
        return valid;
    }
    
    $(function() {
        var url = 'http://arabyos.com/server/php/';
        
        $('#fileupload').fileupload({
            url: url,
            maxNumberOfFiles: 1,
            dataType: 'json',
            done: function(e, data) {
                $.each(data.result.files, function(index, file) {
                    $('#business_documents').val(file.name);
                    $('#business_doc').attr('src', file.thumbnailUrl);
                    list_photo();
                });
            }
        });
        
        $('#file_upload').fileupload({
            url: url,
            maxNumberOfFiles: 1,
            dataType: 'json',
            done: function(e, data) {
                $.each(data.result.files, function(index, file) {
                    $.post("companylogo-update.php", {
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
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" width="1" height="1"></div>
        
        <div class="inner_wrapper">
            <!-- Menu -->
            <?php include __DIR__ . '/includes/header_menu.php'; ?>
            
            <!-- القائمة الجانبية اليسرى -->
            <?php include __DIR__ . '/includes/left_menu.php'; ?>
            
            <!-- المحتوى الرئيسي -->
            <div class="w56b f1 p2b p14 blr">
                <div>
                    <h1 style="font-size:22px; font-weight:bold; direction:rtl; text-align:right;">
                        إملأ البيانات القانونية للشركة
                    </h1>
                </div>
                
                <?php include __DIR__ . '/includes/business-panel.php'; ?>
                
                <div id="re_link" class="utab">
                    <span style="font-size:20px;" class="f1" 
                          title="Provide your statutory details to build better trust among your prospective buyers.">
                        أكتب بيانات الشركة القانونية لتحقيق قدر كبير من ثقة عملائك المحتملين
                    </span>
                </div>
                
                <div class="tbox" id="div_succ" style="display:none;">
                    <strong style="color:#000;">Saved Successfully!</strong>
                </div>
                
                <div style="text-align:left; width:489px;" class="" id="updatemessage">
                    <?php echo $msg; ?>
                </div>
                
                <div class="mt5">
                    <!-- نموذج تحديث البيانات -->
                    <form action="" name="form1" class="f12" method="post" onsubmit="return chkalldetails();">
                        <div class="frm_a clb" style="background-color:#FAF4FF">
                            <table align="left" border="0" cellpadding="4" cellspacing="0" width="100%">
                                <tbody>
                                    <!-- رقم التسجيل التجاري -->
                                    <tr>
                                        <td class="label" width="160" title="Registration No.">رقم التسجيل التجارى</td>
                                        <td>
                                            <div id="a17" class="tbp cona" style="display:none">
                                                <div class="t1a" align="left">Company Registration Number</div>
                                            </div>
                                            <input name="bnsprof_regno" id="bnsprof_regno" class="a_f rf" 
                                                   maxlength="30" type="text" 
                                                   value="<?php echo htmlspecialchars($row->bnsprof_regno ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <span id="reg_nu" class="em" style="display:none"></span>
                                        </td>
                                    </tr>
                                    
                                    <!-- جهة التسجيل -->
                                    <tr>
                                        <td class="label" title="Registration Authority">جهة التسجيل التجارى</td>
                                        <td>
                                            <div id="a18" class="tbp cona" style="display:none">
                                                <div class="t1a" align="left">Registration Authority</div>
                                            </div>
                                            <input name="bnsprof_regauthority" id="bnsprof_regauthority" 
                                                   value="<?php echo htmlspecialchars($row->bnsprof_regauthority ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                   class="a_f rf" maxlength="100" type="text">
                                            <span id="reg_w" class="em" style="display:none"></span>
                                        </td>
                                    </tr>
                                    
                                    <!-- رقم البطاقة الضريبية -->
                                    <tr>
                                        <td class="label" title="Service Tax No.">رقم البطاقة الضريبية</td>
                                        <td>
                                            <div id="a22" class="tbp cona" style="display:none">
                                                <div class="t1a" align="left">Service Tax Number</div>
                                            </div>
                                            <input id="bnsprof_svtax_no" name="bnsprof_svtax_no" class="a_f rf" 
                                                   maxlength="15" type="text" 
                                                   value="<?php echo htmlspecialchars($row->bnsprof_svtax_no ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </td>
                                    </tr>
                                    
                                    <!-- معرف المستخدم (مخفي) -->
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td align="left">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td width="118px;">
                                                            <input name="userd" id="userd" value="<?php echo $uid; ?>" type="hidden">
                                                            <input name="btnUpdate" id="btnUpdate" value="إحفظ التغييرات" 
                                                                   class="saps mt5" type="submit">
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="clb">&nbsp;</div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="c3">&nbsp;</div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>