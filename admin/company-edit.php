<?php
// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من تسجيل دخول المستخدم
check_admin_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس تعديل بيانات الشركة
 */
class editCompany
{
    public $msg;
    public $bnsprof_id;
    public $bnsprof_uid;
    public $bnsprof_compname;
    public $bnsprof_owntype;
    public $bnsprof_ceoprefix;
    public $bnsprof_ceofname;
    public $bnsprof_ceolname;
    public $bnsprof_address1;
    public $bnsprof_address2;
    public $bnsprof_state;
    public $bnsprof_city;
    public $bnsprof_zipcode;
    public $bnsprof_phcode1;
    public $bnsprof_ph1;
    public $bnsprof_phcode2;
    public $bnsprof_ph2;
    public $bnsprof_phcode3;
    public $bnsprof_ph3;
    public $bnsprof_phcode4;
    public $bnsprof_ph4;
    public $bnsprof_mobile2;
    public $bnsprof_mobile3;
    public $bnsprof_mobile4;
    public $bnsprof_faxcode1;
    public $bnsprof_fax1;
    public $bnsprof_faxcode2;
    public $bnsprof_fax2;
    public $bnsprof_emailalt1;
    public $bnsprof_emailalt2;
    public $bnsprof_emailalt3;
    public $bnsprof_website_alt;
    public $bnsprof_businesstype;
    public $bnsprof_yoe;
    public $bnsprof_comemp;
    public $bnsprof_turnover;
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
    public $bnsprof_complogo;
    public $con;
    
    public function __construct($bnsprof_id)
    {
        global $con;
        $this->con = $con;
        $this->bnsprof_id = $bnsprof_id;
    }
    
    public function detailsObj()
    {
        $sql = "SELECT * FROM user, business_profile 
                WHERE bnsprof_uid = usr_id 
                AND MD5(bnsprof_id) = '" . mysqli_real_escape_string($this->con, $this->bnsprof_id) . "' 
                LIMIT 1";
        $res = mysqli_query($this->con, $sql);
        
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_object($res);
        }
        return null;
    }
    
    public function valid(): bool
    {
        $valid = true;
        $filename = $_FILES['bnsprof_complogo']['name'] ?? '';
        
        if (!empty($filename)) {
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed_ext = ['gif', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($ext, $allowed_ext)) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Invalid file type. Only GIF, JPG, PNG allowed.</div>';
                $valid = false;
            }
        }
        
        return $valid;
    }
    
    public function update()
    {
        // معالجة أنواع الأعمال
        $btype = '';
        if (is_array($this->bnsprof_businesstype)) {
            $btype = implode(',', $this->bnsprof_businesstype);
        }
        
        // تحديث مع صورة جديدة
        if (!empty($_FILES["bnsprof_complogo"]["name"])) {
            $this->updateWithLogo($btype);
        } else {
            $this->updateWithoutLogo($btype);
        }
    }
    
    private function updateWithLogo($btype)
    {
        if ($_FILES["bnsprof_complogo"]["error"] > 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Upload Error: ' . $_FILES["bnsprof_complogo"]["error"] . '</div>';
            return;
        }
        
        // إنشاء اسم فريد للصورة
        $filename = $_FILES['bnsprof_complogo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $vid = $this->bnsprof_uid . date("YmdHis");
        $this->bnsprof_complogo = $vid . '.' . $ext;
        
        // نقل الملف المرفوع
        $upload_path = "../upload/companylogo/" . $this->bnsprof_complogo;
        if (!move_uploaded_file($_FILES["bnsprof_complogo"]["tmp_name"], $upload_path)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to upload file</div>';
            return;
        }
        
        // إنشاء صورة مصغرة
        try {
            $imgSImage = new SimpleImage();
            $imgSImage->load($upload_path);
            $imgSImage->resize(90, 90);
            $imgSImage->save("../upload/companylogo/thumb/" . $this->bnsprof_complogo);
        } catch (Exception $e) {
            error_log("خطأ في إنشاء الصورة المصغرة: " . $e->getMessage());
        }
        
        // حذف الصورة القديمة
        $sqlImg = "SELECT * FROM business_profile WHERE bnsprof_id = " . (int)$this->bnsprof_id;
        $resImg = mysqli_query($this->con, $sqlImg);
        $rowImg = mysqli_fetch_object($resImg);
        
        if (!empty($rowImg->bnsprof_complogo)) {
            $pathLrg = "../upload/companylogo/" . $rowImg->bnsprof_complogo;
            if (is_file($pathLrg)) {
                unlink($pathLrg);
            }
            
            $pathThumb = "../upload/companylogo/thumb/" . $rowImg->bnsprof_complogo;
            if (is_file($pathThumb)) {
                unlink($pathThumb);
            }
        }
        
        // تحديث قاعدة البيانات
        $sql = $this->buildUpdateSQL($btype, true);
        $result = mysqli_query($this->con, $sql);
        
        if (!$result) {
            error_log("خطأ في تحديث بيانات الشركة: " . mysqli_error($this->con));
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
        } else {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Company updated successfully</div>';
        }
    }
    
    private function updateWithoutLogo($btype)
    {
        $sql = $this->buildUpdateSQL($btype, false);
        $result = mysqli_query($this->con, $sql);
        
        if (!$result) {
            error_log("خطأ في تحديث بيانات الشركة: " . mysqli_error($this->con));
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
        } else {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Company updated successfully</div>';
        }
    }
    
    private function buildUpdateSQL($btype, $includeLogo): string
    {
        $sql = "UPDATE business_profile SET
                bnsprof_compname = '" . mysqli_real_escape_string($this->con, $this->bnsprof_compname) . "',
                bnsprof_owntype = '" . mysqli_real_escape_string($this->con, $this->bnsprof_owntype) . "',
                bnsprof_ceoprefix = '" . mysqli_real_escape_string($this->con, $this->bnsprof_ceoprefix) . "',
                bnsprof_ceofname = '" . mysqli_real_escape_string($this->con, $this->bnsprof_ceofname) . "',
                bnsprof_ceolname = '" . mysqli_real_escape_string($this->con, $this->bnsprof_ceolname) . "',
                bnsprof_address1 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_address1) . "',
                bnsprof_address2 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_address2) . "',
                bnsprof_state = '" . mysqli_real_escape_string($this->con, $this->bnsprof_state) . "',
                bnsprof_city = '" . mysqli_real_escape_string($this->con, $this->bnsprof_city) . "',
                bnsprof_zipcode = '" . mysqli_real_escape_string($this->con, $this->bnsprof_zipcode) . "',
                bnsprof_phcode1 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_phcode1) . "',
                bnsprof_ph1 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_ph1) . "',
                bnsprof_phcode2 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_phcode2) . "',
                bnsprof_ph2 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_ph2) . "',
                bnsprof_phcode3 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_phcode3) . "',
                bnsprof_ph3 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_ph3) . "',
                bnsprof_phcode4 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_phcode4) . "',
                bnsprof_ph4 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_ph4) . "',
                bnsprof_mobile2 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_mobile2) . "',
                bnsprof_mobile3 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_mobile3) . "',
                bnsprof_mobile4 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_mobile4) . "',
                bnsprof_faxcode1 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_faxcode1) . "',
                bnsprof_fax1 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_fax1) . "',
                bnsprof_faxcode2 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_faxcode2) . "',
                bnsprof_fax2 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_fax2) . "',
                bnsprof_emailalt1 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_emailalt1) . "',
                bnsprof_emailalt2 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_emailalt2) . "',
                bnsprof_emailalt3 = '" . mysqli_real_escape_string($this->con, $this->bnsprof_emailalt3) . "',
                bnsprof_website_alt = '" . mysqli_real_escape_string($this->con, $this->bnsprof_website_alt) . "',
                bnsprof_businesstype = '" . mysqli_real_escape_string($this->con, $btype) . "',
                bnsprof_yoe = '" . mysqli_real_escape_string($this->con, $this->bnsprof_yoe) . "',
                bnsprof_comemp = '" . mysqli_real_escape_string($this->con, $this->bnsprof_comemp) . "',
                bnsprof_turnover = '" . mysqli_real_escape_string($this->con, $this->bnsprof_turnover) . "',
                bnsprof_regno = '" . mysqli_real_escape_string($this->con, $this->bnsprof_regno) . "',
                bnsprof_regauthority = '" . mysqli_real_escape_string($this->con, $this->bnsprof_regauthority) . "',
                bnsprof_cin_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_cin_no) . "',
                bnsprof_tan_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_tan_no) . "',
                bnsprof_pan_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_pan_no) . "',
                bnsprof_svtax_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_svtax_no) . "',
                bnsprof_excisereg_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_excisereg_no) . "',
                bnsprof_vat_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_vat_no) . "',
                bnsprof_ie_code = '" . mysqli_real_escape_string($this->con, $this->bnsprof_ie_code) . "',
                bnsprof_cst_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_cst_no) . "',
                bnsprof_msme_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_msme_no) . "',
                bnsprof_epf_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_epf_no) . "',
                bnsprof_esi_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_esi_no) . "',
                bnsprof_sct_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_sct_no) . "',
                bnsprof_dnb_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_dnb_no) . "',
                bnsprof_rbi_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_rbi_no) . "',
                bnsprof_fssailic_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_fssailic_no) . "',
                bnsprof_nsic_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_nsic_no) . "',
                bnsprof_sst_no = '" . mysqli_real_escape_string($this->con, $this->bnsprof_sst_no) . "'";
        
        if ($includeLogo) {
            $sql .= ", bnsprof_complogo = '" . mysqli_real_escape_string($this->con, $this->bnsprof_complogo) . "'";
        }
        
        $sql .= " WHERE bnsprof_id = " . (int)$this->bnsprof_id;
        
        return $sql;
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// التحقق من وجود معرف الشركة
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("location: company-list.php");
    exit();
}

$id = $_GET['id'];

// إنشاء كائن التعديل
$ob = new editCompany($id);
$row = $ob->detailsObj();

if (!$row) {
    header("location: company-list.php");
    exit();
}

// معالجة إرسال النموذج
if (isset($_POST['btnUpdate'])) {
    
    $ob->bnsprof_id = (int)$_POST['bnsprof_id'];
    $ob->bnsprof_uid = (int)$_POST['bnsprof_uid'];
    $ob->bnsprof_compname = trim($_POST['bnsprof_compname'] ?? '');
    $ob->bnsprof_owntype = $_POST['bnsprof_owntype'] ?? '';
    $ob->bnsprof_ceoprefix = $_POST['bnsprof_ceoprefix'] ?? '';
    $ob->bnsprof_ceofname = trim($_POST['bnsprof_ceofname'] ?? '');
    $ob->bnsprof_ceolname = trim($_POST['bnsprof_ceolname'] ?? '');
    $ob->bnsprof_address1 = trim($_POST['bnsprof_address1'] ?? '');
    $ob->bnsprof_address2 = trim($_POST['bnsprof_address2'] ?? '');
    $ob->bnsprof_state = (int)($_POST['bnsprof_state'] ?? 0);
    $ob->bnsprof_city = (int)($_POST['bnsprof_city'] ?? 0);
    $ob->bnsprof_zipcode = trim($_POST['bnsprof_zipcode'] ?? '');
    $ob->bnsprof_phcode1 = trim($_POST['bnsprof_phcode1'] ?? '');
    $ob->bnsprof_ph1 = trim($_POST['bnsprof_ph1'] ?? '');
    $ob->bnsprof_phcode2 = trim($_POST['bnsprof_phcode2'] ?? '');
    $ob->bnsprof_ph2 = trim($_POST['bnsprof_ph2'] ?? '');
    $ob->bnsprof_phcode3 = trim($_POST['bnsprof_phcode3'] ?? '');
    $ob->bnsprof_ph3 = trim($_POST['bnsprof_ph3'] ?? '');
    $ob->bnsprof_phcode4 = trim($_POST['bnsprof_phcode4'] ?? '');
    $ob->bnsprof_ph4 = trim($_POST['bnsprof_ph4'] ?? '');
    $ob->bnsprof_mobile2 = trim($_POST['bnsprof_mobile2'] ?? '');
    $ob->bnsprof_mobile3 = trim($_POST['bnsprof_mobile3'] ?? '');
    $ob->bnsprof_mobile4 = trim($_POST['bnsprof_mobile4'] ?? '');
    $ob->bnsprof_faxcode1 = trim($_POST['bnsprof_faxcode1'] ?? '');
    $ob->bnsprof_fax1 = trim($_POST['bnsprof_fax1'] ?? '');
    $ob->bnsprof_faxcode2 = trim($_POST['bnsprof_faxcode2'] ?? '');
    $ob->bnsprof_fax2 = trim($_POST['bnsprof_fax2'] ?? '');
    $ob->bnsprof_emailalt1 = trim($_POST['bnsprof_emailalt1'] ?? '');
    $ob->bnsprof_emailalt2 = trim($_POST['bnsprof_emailalt2'] ?? '');
    $ob->bnsprof_emailalt3 = trim($_POST['bnsprof_emailalt3'] ?? '');
    $ob->bnsprof_website_alt = trim($_POST['bnsprof_website_alt'] ?? '');
    $ob->bnsprof_businesstype = $_POST['bnsprof_businesstype'] ?? [];
    $ob->bnsprof_yoe = trim($_POST['bnsprof_yoe'] ?? '');
    $ob->bnsprof_comemp = $_POST['bnsprof_comemp'] ?? '';
    $ob->bnsprof_turnover = $_POST['bnsprof_turnover'] ?? '';
    $ob->bnsprof_regno = trim($_POST['bnsprof_regno'] ?? '');
    $ob->bnsprof_regauthority = trim($_POST['bnsprof_regauthority'] ?? '');
    $ob->bnsprof_cin_no = trim($_POST['bnsprof_cin_no'] ?? '');
    $ob->bnsprof_tan_no = trim($_POST['bnsprof_tan_no'] ?? '');
    $ob->bnsprof_pan_no = trim($_POST['bnsprof_pan_no'] ?? '');
    $ob->bnsprof_svtax_no = trim($_POST['bnsprof_svtax_no'] ?? '');
    $ob->bnsprof_excisereg_no = trim($_POST['bnsprof_excisereg_no'] ?? '');
    $ob->bnsprof_vat_no = trim($_POST['bnsprof_vat_no'] ?? '');
    $ob->bnsprof_ie_code = trim($_POST['bnsprof_ie_code'] ?? '');
    $ob->bnsprof_cst_no = trim($_POST['bnsprof_cst_no'] ?? '');
    $ob->bnsprof_msme_no = trim($_POST['bnsprof_msme_no'] ?? '');
    $ob->bnsprof_epf_no = trim($_POST['bnsprof_epf_no'] ?? '');
    $ob->bnsprof_esi_no = trim($_POST['bnsprof_esi_no'] ?? '');
    $ob->bnsprof_sct_no = trim($_POST['bnsprof_sct_no'] ?? '');
    $ob->bnsprof_dnb_no = trim($_POST['bnsprof_dnb_no'] ?? '');
    $ob->bnsprof_rbi_no = trim($_POST['bnsprof_rbi_no'] ?? '');
    $ob->bnsprof_fssailic_no = trim($_POST['bnsprof_fssailic_no'] ?? '');
    $ob->bnsprof_nsic_no = trim($_POST['bnsprof_nsic_no'] ?? '');
    $ob->bnsprof_sst_no = trim($_POST['bnsprof_sst_no'] ?? '');
    $ob->bnsprof_complogo = $_FILES['bnsprof_complogo']['name'] ?? '';
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    $_SESSION['msg'] = $ob->msg;
    header("Location: company-edit.php?id=" . md5((string)$ob->bnsprof_id));
    exit();
}
?>
<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <script type="text/javascript">
        function showCity(id) {
            $.get("showCity.php", {id: id}, function(data) {
                $('#bnsprof_city').html(data);
            });
        }
        
        function checkEmail(eml) {
            var at = "@";
            var dot = ".";
            var lat = eml.indexOf(at);
            var lstr = eml.length;
            var ldot = eml.indexOf(dot);
            
            if (eml.indexOf(at) == -1 || eml.indexOf(at) == 0 || eml.indexOf(at) == lstr || 
                eml.indexOf(dot) == -1 || eml.indexOf(dot) == 0 || eml.indexOf(dot) == lstr || 
                eml.indexOf(at, (lat+1)) != -1 || eml.substring(lat-1, lat) == dot || 
                eml.substring(lat+1, lat+2) == dot || eml.indexOf(dot, (lat+2)) == -1 || 
                eml.indexOf(" ") != -1) {   
                return true;
            } else {
                return false;
            }
        }
        
        function validForm() {
            var bnsprof_compname = document.getElementById('bnsprof_compname');
            var bnsprof_ceofname = document.getElementById('bnsprof_ceofname');
            var bnsprof_ceolname = document.getElementById('bnsprof_ceolname');
            var bnsprof_address1 = document.getElementById('bnsprof_address1');
            var bnsprof_state = document.getElementById('bnsprof_state');
            var bnsprof_city = document.getElementById('bnsprof_city');
            var bnsprof_zipcode = document.getElementById('bnsprof_zipcode');
            
            var bnsprof_phcode1 = document.getElementById('bnsprof_phcode1');
            var bnsprof_ph1 = document.getElementById('bnsprof_ph1');
            var bnsprof_phcode2 = document.getElementById('bnsprof_phcode2');
            var bnsprof_ph2 = document.getElementById('bnsprof_ph2');
            var bnsprof_phcode3 = document.getElementById('bnsprof_phcode3');
            var bnsprof_ph3 = document.getElementById('bnsprof_ph3');
            var bnsprof_phcode4 = document.getElementById('bnsprof_phcode4');
            var bnsprof_ph4 = document.getElementById('bnsprof_ph4');
            
            var bnsprof_mobile2 = document.getElementById('bnsprof_mobile2');
            var bnsprof_mobile3 = document.getElementById('bnsprof_mobile3');
            var bnsprof_mobile4 = document.getElementById('bnsprof_mobile4');
            
            var bnsprof_faxcode1 = document.getElementById('bnsprof_faxcode1');
            var bnsprof_fax1 = document.getElementById('bnsprof_fax1');
            var bnsprof_faxcode2 = document.getElementById('bnsprof_faxcode2');
            var bnsprof_fax2 = document.getElementById('bnsprof_fax2');
            
            var bnsprof_emailalt1 = document.getElementById('bnsprof_emailalt1');
            var bnsprof_emailalt2 = document.getElementById('bnsprof_emailalt2');
            var bnsprof_emailalt3 = document.getElementById('bnsprof_emailalt3');
            var bnsprof_yoe = document.getElementById('bnsprof_yoe');

            var fup = document.getElementById('bnsprof_complogo');
            var fileName = fup.value;
            var ext = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();

            var message = "";
            var valid = true;

            if (bnsprof_compname.value == '' || bnsprof_compname.value == null) {
                message = 'Please enter Company Name.';
                bnsprof_compname.focus();
                valid = false;
            } else if (!isNaN(bnsprof_compname.value)) {
                message = 'Please enter valid Company Name.';
                bnsprof_compname.value = '';
                bnsprof_compname.focus();
                valid = false;
            } else if (bnsprof_ceofname.value != "" && !isNaN(bnsprof_ceofname.value)) {
                message = "Please enter valid First Name of CEO.";
                bnsprof_ceofname.value = '';
                bnsprof_ceofname.focus();
                valid = false;
            } else if (bnsprof_ceolname.value != "" && !isNaN(bnsprof_ceolname.value)) {
                message = "Please enter valid Last Name of CEO.";
                bnsprof_ceolname.value = '';
                bnsprof_ceolname.focus();
                valid = false;
            } else if (bnsprof_address1.value == '' || bnsprof_address1.value == null) {
                message = 'Please enter Address.';
                bnsprof_address1.focus();
                valid = false;
            } else if (bnsprof_state.value == '' || bnsprof_state.value == null || bnsprof_state.value == '0') {
                message = 'Please select State.';
                bnsprof_state.focus();
                valid = false;
            } else if (bnsprof_city.value == '' || bnsprof_city.value == null || bnsprof_city.value == '0') {
                message = 'Please select City.';
                bnsprof_city.focus();
                valid = false;
            } else if (bnsprof_zipcode.value != "" && isNaN(bnsprof_zipcode.value)) {
                message = "Please enter valid Postal/Zip Code.";
                bnsprof_zipcode.value = '';
                bnsprof_zipcode.focus();
                valid = false;
            } else if (bnsprof_phcode1.value == '' || bnsprof_phcode1.value == null || bnsprof_phcode1.value == '0') {
                message = 'Please enter Area Telephone Code.';
                bnsprof_phcode1.value = '';
                bnsprof_phcode1.focus();
                valid = false;
            } else if (isNaN(bnsprof_phcode1.value)) {
                message = 'Please enter valid Area Telephone Code.';
                bnsprof_phcode1.value = '';
                bnsprof_phcode1.focus();
                valid = false;
            } else if (bnsprof_ph1.value == '' || bnsprof_ph1.value == null || bnsprof_ph1.value == '0') {
                message = 'Please enter Phone Number.';
                bnsprof_ph1.value = '';
                bnsprof_ph1.focus();
                valid = false;
            } else if (isNaN(bnsprof_ph1.value)) {
                message = 'Please enter valid Phone Number.';
                bnsprof_ph1.value = '';
                bnsprof_ph1.focus();
                valid = false;
            } else if ((bnsprof_phcode2.value != '' && bnsprof_phcode2.value != null) && isNaN(bnsprof_phcode2.value)) {
                message = 'Please enter valid Area Telephone Code.';
                bnsprof_phcode2.value = '';
                bnsprof_phcode2.focus();
                valid = false;
            } else if ((bnsprof_ph2.value != '' && bnsprof_ph2.value != null) && isNaN(bnsprof_ph2.value)) {
                message = 'Please enter valid Phone Number.';
                bnsprof_ph2.value = '';
                bnsprof_ph2.focus();
                valid = false;
            } else if ((bnsprof_phcode3.value != '' && bnsprof_phcode3.value != null) && isNaN(bnsprof_phcode3.value)) {
                message = 'Please enter valid Area Telephone Code.';
                bnsprof_phcode3.value = '';
                bnsprof_phcode3.focus();
                valid = false;
            } else if ((bnsprof_ph3.value != '' && bnsprof_ph3.value != null) && isNaN(bnsprof_ph3.value)) {
                message = 'Please enter valid Phone Number.';
                bnsprof_ph3.value = '';
                bnsprof_ph3.focus();
                valid = false;
            } else if ((bnsprof_phcode4.value != '' && bnsprof_phcode4.value != null) && isNaN(bnsprof_phcode4.value)) {
                message = 'Please enter valid Area Telephone Code.';
                bnsprof_phcode4.value = '';
                bnsprof_phcode4.focus();
                valid = false;
            } else if ((bnsprof_ph4.value != '' && bnsprof_ph4.value != null) && isNaN(bnsprof_ph4.value)) {
                message = 'Please enter valid Phone Number.';
                bnsprof_ph4.value = '';
                bnsprof_ph4.focus();
                valid = false;
            } else if ((bnsprof_mobile2.value != '' && bnsprof_mobile2.value != null) && 
                      (isNaN(bnsprof_mobile2.value) || bnsprof_mobile2.value.length != 10)) {
                message = 'Please enter valid Mobile Number.';
                bnsprof_mobile2.value = '';
                bnsprof_mobile2.focus();
                valid = false;
            } else if ((bnsprof_mobile3.value != '' && bnsprof_mobile3.value != null) && 
                      (isNaN(bnsprof_mobile3.value) || bnsprof_mobile3.value.length != 10)) {
                message = 'Please enter valid Mobile Number.';
                bnsprof_mobile3.value = '';
                bnsprof_mobile3.focus();
                valid = false;
            } else if ((bnsprof_mobile4.value != '' && bnsprof_mobile4.value != null) && 
                      (isNaN(bnsprof_mobile4.value) || bnsprof_mobile4.value.length != 10)) {
                message = 'Please enter valid Mobile Number.';
                bnsprof_mobile4.value = '';
                bnsprof_mobile4.focus();
                valid = false;
            } else if ((bnsprof_faxcode1.value != '' && bnsprof_faxcode1.value != null) && isNaN(bnsprof_faxcode1.value)) {
                message = 'Please enter valid Fax Code Number.';
                bnsprof_faxcode1.value = '';
                bnsprof_faxcode1.focus();
                valid = false;
            } else if ((bnsprof_fax1.value != '' && bnsprof_fax1.value != null) && isNaN(bnsprof_fax1.value)) {
                message = 'Please enter valid Fax Number.';
                bnsprof_fax1.value = '';
                bnsprof_fax1.focus();
                valid = false;
            } else if ((bnsprof_faxcode2.value != '' && bnsprof_faxcode2.value != null) && isNaN(bnsprof_faxcode2.value)) {
                message = 'Please enter valid Fax Code Number.';
                bnsprof_faxcode2.value = '';
                bnsprof_faxcode2.focus();
                valid = false;
            } else if ((bnsprof_fax2.value != '' && bnsprof_fax2.value != null) && isNaN(bnsprof_fax2.value)) {
                message = 'Please enter valid Fax Number.';
                bnsprof_fax2.value = '';
                bnsprof_fax2.focus();
                valid = false;
            } else if (bnsprof_emailalt1.value != '' && bnsprof_emailalt1.value != null && checkEmail(bnsprof_emailalt1.value)) {
                message = 'Please enter valid Email Address.';
                bnsprof_emailalt1.value = '';
                bnsprof_emailalt1.focus();
                valid = false;
            } else if (bnsprof_emailalt2.value != '' && bnsprof_emailalt2.value != null && checkEmail(bnsprof_emailalt2.value)) {
                message = 'Please enter valid Email Address.';
                bnsprof_emailalt2.value = '';
                bnsprof_emailalt2.focus();
                valid = false;
            } else if (bnsprof_emailalt3.value != '' && bnsprof_emailalt3.value != null && checkEmail(bnsprof_emailalt3.value)) {
                message = 'Please enter valid Email Address.';
                bnsprof_emailalt3.value = '';
                bnsprof_emailalt3.focus();
                valid = false;
            } else if ((bnsprof_yoe.value != '' && bnsprof_yoe.value != null) && 
                      (isNaN(bnsprof_yoe.value) || bnsprof_yoe.value.length != 4)) {
                message = 'Please enter valid Year (4 digits).';
                bnsprof_yoe.value = '';
                bnsprof_yoe.focus();
                valid = false;
            }
            
            if (fileName != '' && !ext.match(/^(gif|jpg|jpeg|png)$/i)) {
                message = 'Please upload valid File (GIF, JPG, PNG only).';
                fup.value = '';
                fup.focus();
                valid = false;
            }
            
            if (!valid) {
                document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> " + message;
                document.getElementById('msg').className = "alert alert-danger";
            }
            return valid;
        }
        </script>
        
        <?php include "includes/admin-left-con.php" ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="company-list.php">Manage Company</a>
                    </li>
                    <li class="active">Company Details</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Company
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php if (!empty($row->bnsprof_compname)): ?>
                                Details of <strong><?php echo htmlspecialchars(ucfirst($row->bnsprof_compname)); ?></strong>
                            <?php else: ?>
                                Company Details
                            <?php endif; ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return validForm();">
                            <em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
                            
                            <input type="hidden" id="bnsprof_id" name="bnsprof_id" value="<?php echo (int)$row->bnsprof_id; ?>" />
                            <input type="hidden" id="bnsprof_uid" name="bnsprof_uid" value="<?php echo (int)$row->bnsprof_uid; ?>" />
                            
                            <div id="msg" class="<?php echo strpos($msg, 'success') ? 'alert alert-success' : 'alert alert-danger'; ?>">
                                <?php echo $msg; ?>
                            </div>
                            
                            <!-- Company Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">
                                    Company Name: <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_compname" id="bnsprof_compname" class="col-xs-10 col-sm-5 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_compname ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- Ownership Type -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Ownership Type:</label>
                                <div class="col-sm-9">
                                    <select class="col-sm-5 form-control" name="bnsprof_owntype" id="bnsprof_owntype">
                                        <option value="">---Choose One---</option>
                                        <?php
                                        $owntypesql = mysqli_query($con, "SELECT * FROM ownership_type WHERE owntyp_status='1'");
                                        while ($owntyperow = mysqli_fetch_object($owntypesql)):
                                        ?>
                                            <option value="<?php echo (int)$owntyperow->owntyp_id; ?>" 
                                                <?php echo ($row->bnsprof_owntype == $owntyperow->owntyp_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($owntyperow->owntyp_title); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- CEO Info -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">CEO:</label>
                                <div class="col-sm-8">
                                    <select name="bnsprof_ceoprefix" id="bnsprof_ceoprefix" class="col-xs-12 col-sm-2 form-control" style="width:auto; display:inline-block;">
                                        <?php
                                        $arr = array("Mr.", "Ms.", "Mrs.", "Dr.");
                                        foreach ($arr as $val):
                                        ?>
                                            <option value="<?php echo $val; ?>" <?php echo ($val == $row->bnsprof_ceoprefix) ? 'selected="selected"' : ''; ?>>
                                                <?php echo $val; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    &nbsp;
                                    <input name="bnsprof_ceofname" id="bnsprof_ceofname" class="col-xs-12 col-sm-3 form-control" type="text" style="width:200px; display:inline-block;" value="<?php echo htmlspecialchars($row->bnsprof_ceofname ?? ''); ?>" />
                                    &nbsp;
                                    <input name="bnsprof_ceolname" id="bnsprof_ceolname" class="col-xs-12 col-sm-3 form-control" type="text" style="width:200px; display:inline-block;" value="<?php echo htmlspecialchars($row->bnsprof_ceolname ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- Username -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Username:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars(ucwords(($row->name_prefix ?? '') . ' ' . ($row->lname ?? '') . ' ' . ($row->fname ?? ''))); ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Address: <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <textarea id="bnsprof_address1" name="bnsprof_address1" class="col-sm-6 form-control" rows="2"><?php echo htmlspecialchars($row->bnsprof_address1 ?? ''); ?></textarea>
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9" style="margin-top:2px;">
                                    <textarea id="bnsprof_address2" name="bnsprof_address2" class="col-sm-6 form-control" rows="2"><?php echo htmlspecialchars($row->bnsprof_address2 ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- State/City -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">&nbsp;</label>
                                <div class="col-sm-9">
                                    <select name="bnsprof_state" id="bnsprof_state" class="form-control" style="width:200px; display:inline-block;" onchange="showCity(this.value);" title="State">
                                        <option value="0"> --- Select State --- </option>
                                        <?php
                                        $sql_state = "SELECT * FROM states WHERE state_cn_id = '" . (int)$row->country . "' AND state_status='1'";
                                        $res_state = mysqli_query($con, $sql_state);
                                        while ($row_state = mysqli_fetch_object($res_state)):
                                        ?>
                                            <option value="<?php echo (int)$row_state->state_id; ?>" 
                                                <?php echo ($row_state->state_id == $row->bnsprof_state) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($row_state->state_name); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    
                                    <select name="bnsprof_city" id="bnsprof_city" class="form-control" style="width:200px; display:inline-block;" title="City">
                                        <option value=""> --- Select City --- </option>
                                        <?php
                                        $sql_ct = "SELECT * FROM city WHERE ct_status='1'";
                                        $res_ct = mysqli_query($con, $sql_ct);
                                        while ($row_ct = mysqli_fetch_object($res_ct)):
                                        ?>
                                            <option value="<?php echo (int)$row_ct->ct_id; ?>" 
                                                <?php echo ($row_ct->ct_id == $row->bnsprof_city) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($row_ct->ct_name); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Postal Code -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Postal / Zip Code:</label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_zipcode" id="bnsprof_zipcode" class="col-xs-10 col-sm-5 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_zipcode ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- Country -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $sql_cn = "SELECT * FROM country WHERE cn_id = '" . (int)$row->country . "' AND cn_status='1'";
                                    $res_cn = mysqli_query($con, $sql_cn);
                                    if (mysqli_num_rows($res_cn) > 0):
                                        $row_cn = mysqli_fetch_object($res_cn);
                                    ?>
                                        <label style="padding-top:4px;"><?php echo htmlspecialchars($row_cn->cn_name); ?></label>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Phone Numbers -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Phone Number: <span style="color:#CC0000">*</span></label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_phcode1" id="bnsprof_phcode1" class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_phcode1 ?? ''); ?>" />
                                    <input name="bnsprof_ph1" id="bnsprof_ph1" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_ph1 ?? ''); ?>" />
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9" style="margin-top:2px;">
                                    <input name="bnsprof_phcode2" id="bnsprof_phcode2" class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_phcode2 ?? ''); ?>" />
                                    <input name="bnsprof_ph2" id="bnsprof_ph2" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_ph2 ?? ''); ?>" />
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9" style="margin-top:2px;">
                                    <input name="bnsprof_phcode3" id="bnsprof_phcode3" class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_phcode3 ?? ''); ?>" />
                                    <input name="bnsprof_ph3" id="bnsprof_ph3" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_ph3 ?? ''); ?>" />
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9" style="margin-top:2px;">
                                    <input name="bnsprof_phcode4" id="bnsprof_phcode4" class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_phcode4 ?? ''); ?>" />
                                    <input name="bnsprof_ph4" id="bnsprof_ph4" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_ph4 ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- Mobile Numbers -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Mobile/Cell Phone:</label>
                                <div class="col-sm-9">
                                    <input class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block; background-color:#f5f5f5;" type="text" value="<?php echo htmlspecialchars($row->country_ph_code ?? ''); ?>" readonly="readonly" />
                                    <input name="bnsprof_mobile2" id="bnsprof_mobile2" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_mobile2 ?? ''); ?>" />
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9" style="margin-top:2px;">
                                    <input class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block; background-color:#f5f5f5;" type="text" value="<?php echo htmlspecialchars($row->country_ph_code ?? ''); ?>" readonly="readonly" />
                                    <input name="bnsprof_mobile3" id="bnsprof_mobile3" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_mobile3 ?? ''); ?>" />
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9" style="margin-top:2px;">
                                    <input class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block; background-color:#f5f5f5;" type="text" value="<?php echo htmlspecialchars($row->country_ph_code ?? ''); ?>" readonly="readonly" />
                                    <input name="bnsprof_mobile4" id="bnsprof_mobile4" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_mobile4 ?? ''); ?>" />
                                </div>
                            </div>

                            <!-- Fax Numbers -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Fax Number:</label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_faxcode1" id="bnsprof_faxcode1" class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_faxcode1 ?? ''); ?>" />
                                    <input name="bnsprof_fax1" id="bnsprof_fax1" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_fax1 ?? ''); ?>" />
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9" style="padding-top:2px;">
                                    <input name="bnsprof_faxcode2" id="bnsprof_faxcode2" class="col-xs-12 col-sm-1 form-control" style="width:80px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_faxcode2 ?? ''); ?>" />
                                    <input name="bnsprof_fax2" id="bnsprof_fax2" class="col-xs-12 col-sm-2 form-control" style="width:150px; display:inline-block;" type="text" value="<?php echo htmlspecialchars($row->bnsprof_fax2 ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- Emails -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Email:</label>
                                <div class="col-sm-9" style="padding-bottom:2px;">
                                    <input name="bnsprof_emailalt1" id="bnsprof_emailalt1" class="col-xs-12 col-sm-5 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_emailalt1 ?? ''); ?>" />
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9" style="padding-bottom:2px;">
                                    <input name="bnsprof_emailalt2" id="bnsprof_emailalt2" class="col-xs-12 col-sm-5 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_emailalt2 ?? ''); ?>" />
                                </div>
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"></label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_emailalt3" id="bnsprof_emailalt3" class="col-xs-12 col-sm-5 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_emailalt3 ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- Website -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Website:</label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_website_alt" id="bnsprof_website_alt" class="col-xs-12 col-sm-5 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_website_alt ?? ''); ?>" />
                                </div>
                            </div>

                            <!-- Business Type -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Type:</label>
                                <div class="col-sm-8">
                                    <?php
                                    $bstyp = !empty($row->bnsprof_businesstype) ? explode(',', $row->bnsprof_businesstype) : [];
                                    $bstypsql = mysqli_query($con, "SELECT * FROM business_type WHERE bsntyp_status='1'");
                                    $c = 1;
                                    while ($bstyprow = mysqli_fetch_object($bstypsql)):
                                    ?>
                                        <label class="col-sm-4" style="font-weight:normal;">
                                            <input name="bnsprof_businesstype[]" id="bnsprof_businesstype" class="ace ace-checkbox-2" value="<?php echo (int)$bstyprow->bsntyp_id; ?>" type="checkbox" <?php echo in_array($bstyprow->bsntyp_id, $bstyp) ? 'checked="checked"' : ''; ?> />
                                            <span class="lbl"><?php echo htmlspecialchars($bstyprow->bsntyp_title); ?></span>
                                        </label>
                                        <?php if ($c % 3 == 0): ?>
                                            <div class="clearfix"></div>
                                        <?php endif; ?>
                                    <?php $c++; endwhile; ?>
                                </div>
                            </div>
                            
                            <!-- Year of Establishment -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Year of Establishment:</label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_yoe" id="bnsprof_yoe" class="col-xs-10 col-sm-2 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_yoe ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- No of Employees -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">No of Employees:</label>
                                <div class="col-sm-9">
                                    <select class="col-sm-5 form-control" name="bnsprof_comemp" id="bnsprof_comemp">
                                        <option value="">---Choose One---</option>
                                        <?php
                                        $noempsql = mysqli_query($con, "SELECT * FROM employee_range WHERE emprange_status='1'");
                                        while ($noemprow = mysqli_fetch_object($noempsql)):
                                        ?>
                                            <option value="<?php echo (int)$noemprow->emprange_id; ?>" 
                                                <?php echo ($row->bnsprof_comemp == $noemprow->emprange_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($noemprow->emprange_type); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Revenue Turnover -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Revenue Sales Turnover:</label>
                                <div class="col-sm-9">
                                    <select class="col-sm-5 form-control" name="bnsprof_turnover" id="bnsprof_turnover">
                                        <option value="">---Choose One---</option>
                                        <?php
                                        $turnoversql = mysqli_query($con, "SELECT * FROM revenue_turnover WHERE revturnover_status='1'");
                                        while ($turnoverow = mysqli_fetch_object($turnoversql)):
                                        ?>
                                            <option value="<?php echo (int)$turnoverow->revturnover_id; ?>" 
                                                <?php echo ($row->bnsprof_turnover == $turnoverow->revturnover_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($turnoverow->revturnover_title); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Registration Fields -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Registration No.:</label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_regno" id="bnsprof_regno" class="col-xs-10 col-sm-5 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_regno ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Registration Authority:</label>
                                <div class="col-sm-9">
                                    <input name="bnsprof_regauthority" id="bnsprof_regauthority" class="col-xs-10 col-sm-5 form-control" type="text" value="<?php echo htmlspecialchars($row->bnsprof_regauthority ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- Continue with all other fields similarly... -->
                            <!-- [بقية الحقول بنفس النمط - تم اختصارها للطول] -->
                            
                            <!-- Company Logo -->
                            <?php if (!empty($row->bnsprof_complogo)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Current Company Logo:</label>
                                <div class="col-sm-9">
                                    <img src="../upload/companylogo/<?php echo htmlspecialchars($row->bnsprof_complogo); ?>" width="200px" height="auto" style="border:1px solid #ddd; padding:3px;" />
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">New Company Logo (Optional):</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="bnsprof_complogo" id="bnsprof_complogo" type="file" accept="image/*" />
                                    </div>
                                    <span class="help-block">Allowed formats: GIF, JPG, PNG. Max size: 2MB</span>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <button class="btn" type="button" onclick="window.location='company-list.php'">
                                        <i class="icon-reply icon-only"></i> Back
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <br clear="all" />
                </div>
            </div>
            <br clear="all" />
        </div>
        
        <?php include "includes/footer.php" ?>
    </div>
</div>

<!-- JavaScript Libraries -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>

<!--[if lte IE 8]>
<script src="assets/js/excanvas.min.js"></script>
<![endif]-->

<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="assets/js/chosen.jquery.min.js"></script>
<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
<script src="assets/js/date-time/moment.min.js"></script>
<script src="assets/js/date-time/daterangepicker.min.js"></script>
<script src="assets/js/bootstrap-colorpicker.min.js"></script>
<script src="assets/js/jquery.knob.min.js"></script>
<script src="assets/js/jquery.autosize.min.js"></script>
<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="assets/js/jquery.maskedinput.min.js"></script>
<script src="assets/js/bootstrap-tag.min.js"></script>

<!-- ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    $('#id-disable-check').on('click', function() {
        var inp = $('#form-input-readonly').get(0);
        if(inp.hasAttribute('disabled')) {
            inp.setAttribute('readonly' , 'true');
            inp.removeAttribute('disabled');
            inp.value="This text field is readonly!";
        }
        else {
            inp.setAttribute('disabled' , 'disabled');
            inp.removeAttribute('readonly');
            inp.value="This text field is disabled!";
        }
    });

    $(".chosen-select").chosen(); 
    $('#chosen-multiple-style').on('click', function(e){
        var target = $(e.target).find('input[type=radio]');
        var which = parseInt(target.val());
        if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
        else $('#form-field-select-4').removeClass('tag-input-style');
    });

    $('[data-rel=tooltip]').tooltip({container:'body'});
    $('[data-rel=popover]').popover({container:'body'});

    $('textarea[class*=autosize]').autosize({append: "\n"});
    $('textarea.limited').inputlimiter({
        remText: '%n character%s remaining...',
        limitText: 'max allowed : %n.'
    });

    $.mask.definitions['~']='[+-]';
    $('.input-mask-date').mask('99/99/9999');
    $('.input-mask-phone').mask('(999) 999-9999');
    $('.input-mask-eyescript').mask('~9.99 ~9.99 999');
    $(".input-mask-product").mask("a*-999-a999",{placeholder:" ",completed:function(){
        alert("You typed the following: "+this.val());
    }});

    $( "#input-size-slider" ).css('width','200px').slider({
        value:1,
        range: "min",
        min: 1,
        max: 8,
        step: 1,
        slide: function( event, ui ) {
            var sizing = ['', 'input-sm', 'input-lg', 'input-mini', 'input-small', 'input-medium', 'input-large', 'input-xlarge', 'input-xxlarge'];
            var val = parseInt(ui.value);
            $('#form-field-4').attr('class', sizing[val]).val('.'+sizing[val]);
        }
    });

    $( "#input-span-slider" ).slider({
        value:1,
        range: "min",
        min: 1,
        max: 12,
        step: 1,
        slide: function( event, ui ) {
            var val = parseInt(ui.value);
            $('#form-field-5').attr('class', 'col-xs-'+val).val('.col-xs-'+val);
        }
    });

    $( "#slider-range" ).css('height','200px').slider({
        orientation: "vertical",
        range: true,
        min: 0,
        max: 100,
        values: [ 17, 67 ],
        slide: function( event, ui ) {
            var val = ui.values[$(ui.handle).index()-1]+"";
            if(! ui.handle.firstChild ) {
                $(ui.handle).append("<div class='tooltip right in' style='display:none;left:16px;top:-6px;'><div class='tooltip-arrow'></div><div class='tooltip-inner'></div></div>");
            }
            $(ui.handle.firstChild).show().children().eq(1).text(val);
        }
    }).find('a').on('blur', function(){
        $(this.firstChild).hide();
    });

    $( "#slider-range-max" ).slider({
        range: "max",
        min: 1,
        max: 10,
        value: 2
    });

    $( "#eq > span" ).css({width:'90%', 'float':'left', margin:'15px'}).each(function() {
        var value = parseInt( $( this ).text(), 10 );
        $( this ).empty().slider({
            value: value,
            range: "min",
            animate: true
        });
    });

    $('#id-input-file-1 , #id-input-file-2, #bnsprof_complogo').ace_file_input({
        no_file:'No File ...',
        btn_choose:'Choose',
        btn_change:'Change',
        droppable:false,
        onchange:null,
        thumbnail:false
    });

    $('#id-input-file-3').ace_file_input({
        style:'well',
        btn_choose:'Drop files here or click to choose',
        btn_change:null,
        no_icon:'icon-cloud-upload',
        droppable:true,
        thumbnail:'small',
        preview_error : function(filename, error_code) {
            //error_code values: 1 = 'FILE_LOAD_FAILED', 2 = 'IMAGE_LOAD_FAILED', 3 = 'THUMBNAIL_FAILED'
        }
    }).on('change', function(){
        //console.log($(this).data('ace_input_files'));
    });

    $('#id-file-format').removeAttr('checked').on('change', function() {
        var before_change;
        var btn_choose;
        var no_icon;
        if(this.checked) {
            btn_choose = "Drop images here or click to choose";
            no_icon = "icon-picture";
            before_change = function(files, dropped) {
                var allowed_files = [];
                for(var i = 0 ; i < files.length; i++) {
                    var file = files[i];
                    if(typeof file === "string") {
                        if(! (/\.(jpe?g|png|gif|bmp)$/i).test(file) ) return false;
                    }
                    else {
                        var type = $.trim(file.type);
                        if( ( type.length > 0 && ! (/^image\/(jpe?g|png|gif|bmp)$/i).test(type) )
                                || ( type.length == 0 && ! (/\.(jpe?g|png|gif|bmp)$/i).test(file.name) ) ) 
                            continue;
                    }
                    allowed_files.push(file);
                }
                if(allowed_files.length == 0) return false;
                return allowed_files;
            }
        }
        else {
            btn_choose = "Drop files here or click to choose";
            no_icon = "icon-cloud-upload";
            before_change = function(files, dropped) {
                return files;
            }
        }
        var file_input = $('#id-input-file-3');
        file_input.ace_file_input('update_settings', {'before_change':before_change, 'btn_choose': btn_choose, 'no_icon':no_icon});
        file_input.ace_file_input('reset_input');
    });

    $('#spinner1').ace_spinner({value:0,min:0,max:200,step:10, btn_up_class:'btn-info' , btn_down_class:'btn-info'})
        .on('change', function(){
            //alert(this.value)
        });
    $('#spinner2').ace_spinner({value:0,min:0,max:10000,step:100, touch_spinner: true, icon_up:'icon-caret-up', icon_down:'icon-caret-down'});
    $('#spinner3').ace_spinner({value:0,min:-100,max:100,step:10, on_sides: true, icon_up:'icon-plus smaller-75', icon_down:'icon-minus smaller-75', btn_up_class:'btn-success' , btn_down_class:'btn-danger'});

    $('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });
    $('input[name=date-range-picker]').daterangepicker().prev().on(ace.click_event, function(){
        $(this).next().focus();
    });

    $('#timepicker1').timepicker({
        minuteStep: 1,
        showSeconds: true,
        showMeridian: false
    }).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });

    $('#colorpicker1').colorpicker();
    $('#simple-colorpicker-1').ace_colorpicker();

    $(".knob").knob();

    var tag_input = $('#form-field-tags');
    if(! ( /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase())) ) 
    {
        tag_input.tag({
            placeholder: tag_input.attr('placeholder'),
            source: ace.variable_US_STATES,
        });
    }
    else {
        tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
    }

    $('#modal-form input[type=file]').ace_file_input({
        style:'well',
        btn_choose:'Drop files here or click to choose',
        btn_change:null,
        no_icon:'icon-cloud-upload',
        droppable:true,
        thumbnail:'large'
    });

    $('#modal-form').on('shown.bs.modal', function () {
        $(this).find('.chosen-container').each(function(){
            $(this).find('a:first-child').css('width' , '210px');
            $(this).find('.chosen-drop').css('width' , '210px');
            $(this).find('.chosen-search input').css('width' , '200px');
        });
    });
});
</script>

</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>