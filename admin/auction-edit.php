<?php
/**
 * File: admin/auction-edit.php
 * Version: PHP 8.3
 * Description: تعديل بيانات المزاد في لوحة التحكم
 * 
 * تسمح هذه الصفحة للمشرف بتعديل جميع بيانات المزاد المحدد
 * بما في ذلك التصنيفات والقيم والتواريخ والتفاصيل
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من تسجيل دخول المستخدم
check_user_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس تعديل المزاد
 */
class editAuction
{
    public $msg;
    public $auc_id;
    public $auc_heading;
    public $mcat_id;
    public $pc_id;
    public $auc_pc_id;
    public $auc_value;
    public $auc_currency;
    
    public $auc_notice_type;
    public $auc_qty;
    public $auc_qty_mu_id;
    public $auc_emd;
    public $auc_document_fees;
    public $auc_document_fees_currency;
    public $auc_project_period;
    public $auc_products;
    
    public $auc_publish_date;
    public $auc_docSaleStart_date;
    public $auc_docSaleEnd_date;
    public $auc_docSubmitBefore_date;
    public $auc_due_date;
    public $auc_prequalification_criteria;
    public $auc_details;
    public $auc_preferred_location;
    public $auc_country;
    public $con;

    public function __construct($auc_id)
    {
        global $con;
        $this->con = $con;
        $this->auc_id = $auc_id;
    }
    
    public function detailsObj()
    {
        $sql = "SELECT * FROM auction 
                LEFT JOIN product_category ON auc_pc_id = pc_id 
                WHERE MD5(auc_id) = '" . mysqli_real_escape_string($this->con, $this->auc_id) . "'
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
        
        if (empty($this->auc_heading)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Auction heading.</div>';
            $valid = false;
        } else if (empty($this->mcat_id)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Main category.</div>';
            $valid = false;
        } else if (empty($this->pc_id)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Category.</div>';
            $valid = false;
        } else if (empty($this->auc_pc_id)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Sub-Category.</div>';
            $valid = false;
        } else if (!empty($this->auc_value) && empty($this->auc_currency)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Currency.</div>';
            $valid = false;
        } else if (empty($this->auc_notice_type)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Notice Type.</div>';
            $valid = false;
        } else if (empty($this->auc_document_fees)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Document Fees.</div>';
            $valid = false;
        } else if (empty($this->auc_document_fees_currency)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select currency for Document Fees.</div>';
            $valid = false;
        } else if (empty($this->auc_publish_date) || $this->auc_publish_date == '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select auction publish date.</div>';
            $valid = false;
        } else if (empty($this->auc_docSaleStart_date) || $this->auc_docSaleStart_date == '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document sale start date.</div>';
            $valid = false;
        } else if (empty($this->auc_docSaleEnd_date) || $this->auc_docSaleEnd_date == '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document sale end date.</div>';
            $valid = false;
        } else if (empty($this->auc_docSubmitBefore_date) || $this->auc_docSubmitBefore_date == '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document submit before date.</div>';
            $valid = false;
        } else if (empty($this->auc_due_date) || $this->auc_due_date == '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select auction due date.</div>';
            $valid = false;
        } else if (empty($this->auc_prequalification_criteria)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Pre-qualification criteria.</div>';
            $valid = false;
        } else if (empty($this->auc_details)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter auction details.</div>';
            $valid = false;
        }
        
        return $valid;
    }
    
    public function update(): void
    {
        $sql = "UPDATE auction
                SET
                    auc_pc_id = " . (int)$this->auc_pc_id . ",
                    auc_heading = '" . mysqli_real_escape_string($this->con, $this->auc_heading) . "',
                    auc_value = '" . mysqli_real_escape_string($this->con, $this->auc_value) . "',
                    auc_currency = '" . mysqli_real_escape_string($this->con, $this->auc_currency) . "',
                    auc_notice_type = '" . mysqli_real_escape_string($this->con, $this->auc_notice_type) . "',
                    auc_qty = '" . mysqli_real_escape_string($this->con, $this->auc_qty) . "',
                    auc_qty_mu_id = " . (int)$this->auc_qty_mu_id . ",
                    auc_emd = '" . mysqli_real_escape_string($this->con, $this->auc_emd) . "',
                    auc_document_fees = '" . mysqli_real_escape_string($this->con, $this->auc_document_fees) . "',
                    auc_document_fees_currency = '" . mysqli_real_escape_string($this->con, $this->auc_document_fees_currency) . "',
                    auc_project_period = '" . mysqli_real_escape_string($this->con, $this->auc_project_period) . "',
                    auc_products = '" . mysqli_real_escape_string($this->con, $this->auc_products) . "',
                    auc_prequalification_criteria = '" . mysqli_real_escape_string($this->con, $this->auc_prequalification_criteria) . "',
                    auc_details = '" . mysqli_real_escape_string($this->con, $this->auc_details) . "',
                    auc_preferred_location = '" . mysqli_real_escape_string($this->con, $this->auc_preferred_location) . "',
                    auc_publish_date = '" . $this->auc_publish_date . "',
                    auc_docSaleStart_date = '" . $this->auc_docSaleStart_date . "',
                    auc_docSaleEnd_date = '" . $this->auc_docSaleEnd_date . "',
                    auc_docSubmitBefore_date = '" . $this->auc_docSubmitBefore_date . "',
                    auc_due_date = '" . $this->auc_due_date . "',
                    auc_updated_date = NOW()
                WHERE auc_id = " . (int)$this->auc_id;

        $result = mysqli_query($this->con, $sql);
        
        if (!$result) {
            error_log("خطأ في تحديث المزاد: " . mysqli_error($this->con));
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error: ' . mysqli_error($this->con) . '</div>';
            return;
        }
        
        $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Auction updated successfully.</div>';
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// التحقق من وجود التوكن
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("location: auction-view.php");
    exit();
}

$token = trim($_GET['token']);

// إنشاء كائن التعديل
$ob = new editAuction($token);
$row = $ob->detailsObj();

if (!$row) {
    header("location: auction-view.php");
    exit();
}

// معالجة إرسال النموذج
if (isset($_POST['btnUpdate'])) {
    $ob->auc_id = (int)$_POST['auc_id'];
    $ob->auc_heading = trim($_POST['auc_heading'] ?? '');
    $ob->mcat_id = (int)($_POST['mcat_id'] ?? 0);
    $ob->pc_id = (int)($_POST['pc_id'] ?? 0);
    $ob->auc_pc_id = (int)($_POST['auc_pc_id'] ?? 0);
    $ob->auc_value = trim($_POST['auc_value'] ?? '');
    $ob->auc_currency = (int)($_POST['auc_currency'] ?? 0);
    
    $ob->auc_notice_type = trim($_POST['auc_notice_type'] ?? '');
    $ob->auc_qty = trim($_POST['auc_qty'] ?? '');
    $ob->auc_qty_mu_id = (int)($_POST['auc_qty_mu_id'] ?? 0);
    $ob->auc_emd = trim($_POST['auc_emd'] ?? '');
    $ob->auc_document_fees = trim($_POST['auc_document_fees'] ?? '');
    $ob->auc_document_fees_currency = (int)($_POST['auc_document_fees_currency'] ?? 0);
    $ob->auc_project_period = trim($_POST['auc_project_period'] ?? '');
    $ob->auc_products = trim($_POST['auc_products'] ?? '');
    
    $ob->auc_publish_date = trim($_POST['auc_publish_date'] ?? '');
    $ob->auc_docSaleStart_date = trim($_POST['auc_docSaleStart_date'] ?? '');
    $ob->auc_docSaleEnd_date = trim($_POST['auc_docSaleEnd_date'] ?? '');
    $ob->auc_docSubmitBefore_date = trim($_POST['auc_docSubmitBefore_date'] ?? '');
    $ob->auc_due_date = trim($_POST['auc_due_date'] ?? '');
    
    $ob->auc_prequalification_criteria = trim($_POST['auc_prequalification_criteria'] ?? '');
    $ob->auc_details = trim($_POST['auc_details'] ?? '');
    $ob->auc_preferred_location = trim($_POST['auc_preferred_location'] ?? '');
    
    if ($ob->valid()) {
        $ob->update();
        $_SESSION['msg'] = $ob->msg;
        header('Location: auction-edit.php?token=' . $token);
        exit();
    } else {
        $_SESSION['msg'] = $ob->msg;
        header('Location: auction-edit.php?token=' . $token);
        exit();
    }
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
        function showCategory() {
            var pc_id = document.getElementById('mcat_id').value;
            if (pc_id) {
                $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
                    $('#pc_id').html(data);
                    showSubcat();
                });
            }
        }
        
        function showSubcat() {
            var pc_id = document.getElementById('pc_id').value;
            if (pc_id) {
                $.get("showSubcat.php", {q: pc_id}, function(data) {
                    $('#auc_pc_id').html(data);
                });
            }
        }
        
        function validForm() {
            var mcat_id = document.getElementById('mcat_id');
            var pc_id = document.getElementById('pc_id');
            var auc_pc_id = document.getElementById('auc_pc_id');
            var auc_heading = document.getElementById('auc_heading');
            var auc_value = document.getElementById('auc_value');
            var auc_currency = document.getElementById('auc_currency');
            
            var auc_notice_type = document.getElementById('auc_notice_type');
            var auc_qty = document.getElementById('auc_qty');
            var auc_qty_mu_id = document.getElementById('auc_qty_mu_id');
            var auc_emd = document.getElementById('auc_emd');
            var auc_document_fees = document.getElementById('auc_document_fees');
            var auc_document_fees_currency = document.getElementById('auc_document_fees_currency');
            var auc_project_period = document.getElementById('auc_project_period');
            var auc_products = document.getElementById('auc_products');
            
            var auc_prequalification_criteria = document.getElementById('auc_prequalification_criteria');
            var auc_details = document.getElementById('auc_details');
            
            var message = "";
            var valid = true;
            
            if (mcat_id.value == '') {
                message = "Kindly select Main Category.";
                mcat_id.focus();
                valid = false;
            } else if (pc_id.value == '') {
                message = "Kindly select Category.";
                pc_id.focus();
                valid = false;
            } else if (auc_pc_id.value == '') {
                message = "Kindly select Sub-Category.";
                auc_pc_id.focus();
                valid = false;
            } else if (auc_heading.value == '') {
                message = "Kindly enter Auction Heading.";
                auc_heading.focus();
                valid = false;
            } else if (auc_value.value != '' && isNaN(auc_value.value)) {
                message = "Kindly enter valid Auction value.";
                auc_value.focus();
                valid = false;
            } else if (auc_value.value != '' && auc_currency.value == '') {
                message = "Kindly select currency for Auction Value.";
                auc_currency.focus();
                valid = false;
            } else if (auc_notice_type.value == '') {
                message = "Kindly enter Notice Type.";
                auc_notice_type.focus();
                valid = false;
            } else if (auc_qty.value != '' && isNaN(auc_qty.value)) {
                message = "Kindly enter valid Quantity.";
                auc_qty.focus();
                valid = false;
            } else if (auc_qty.value != '' && auc_qty_mu_id.value == '') {
                message = "Kindly select Quantity Unit.";
                auc_qty_mu_id.focus();
                valid = false;
            } else if (auc_document_fees.value == '' || auc_document_fees.value == '0') {
                message = "Kindly enter Document Fees.";
                auc_document_fees.focus();
                valid = false;
            } else if (auc_document_fees.value != '' && isNaN(auc_document_fees.value)) {
                message = "Kindly enter valid Document Fees.";
                auc_document_fees.focus();
                valid = false;
            } else if (auc_document_fees_currency.value == '') {
                message = "Kindly select currency for Document Fees.";
                auc_document_fees_currency.focus();
                valid = false;
            } else if (auc_prequalification_criteria.value == '') {
                message = "Kindly describe Pre-qualification Criteria.";
                auc_prequalification_criteria.focus();
                valid = false;
            } else if (auc_details.value == '') {
                message = "Kindly describe Auction details.";
                auc_details.focus();
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
                        <a href="auction-view.php">Manage Auction</a>
                    </li>
                    <li class="active">Auction Edit</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Auction Edit
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return validForm();">
                            <input type="hidden" id="auc_id" name="auc_id" value="<?php echo (int)$row->auc_id; ?>" />

                            <div id="msg" class="<?php echo strpos($msg, 'success') ? 'alert alert-success' : 'alert alert-danger'; ?>">
                                <?php echo $msg; ?>
                            </div>
                            
                            <!-- عنوان المزاد -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Auction Heading:</label>
                                <div class="col-sm-9">
                                    <input name="auc_heading" id="auc_heading" class="col-xs-12 col-sm-6" type="text" value="<?php echo htmlspecialchars($row->auc_heading ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <?php
                            // جلب التصنيف الرئيسي الحالي
                            $mcat_sql = "SELECT pc_parent_id FROM product_category WHERE pc_id = " . (int)$row->pc_parent_id . " AND pc_status = '1' LIMIT 1";
                            $mcat_res = mysqli_query($con, $mcat_sql);
                            $mcat_row = $mcat_res ? mysqli_fetch_object($mcat_res) : null;
                            $current_mcat_id = $mcat_row ? (int)$mcat_row->pc_parent_id : 0;
                            ?>
                            
                            <!-- التصنيف الرئيسي -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Main Category:</label>
                                <div class="col-sm-9">
                                    <select id="mcat_id" name="mcat_id" onchange="showCategory();">
                                        <option value="">-- Select Main Category --</option>
                                        <?php
                                        $sql_mcat = "SELECT * FROM product_category WHERE pc_parent_id = '0' AND pc_status = '1'";
                                        $res_mcat = mysqli_query($con, $sql_mcat);
                                        while ($row_mcat = mysqli_fetch_object($res_mcat)):
                                            $selected = ($row_mcat->pc_id == $current_mcat_id) ? 'selected="selected"' : '';
                                        ?>
                                            <option value="<?php echo (int)$row_mcat->pc_id; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($row_mcat->pc_name); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- التصنيف الفرعي -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Category:</label>
                                <div class="col-sm-8">
                                    <select id="pc_id" name="pc_id" onchange="showSubcat();">
                                        <option value="">-- Select Category --</option>
                                        <?php
                                        if ($current_mcat_id > 0) {
                                            $sql_pc = "SELECT * FROM product_category WHERE pc_parent_id = " . $current_mcat_id . " AND pc_status = '1'";
                                            $res_pc = mysqli_query($con, $sql_pc);
                                            while ($row_pc = mysqli_fetch_object($res_pc)):
                                                $selected = ($row_pc->pc_id == $row->pc_parent_id) ? 'selected="selected"' : '';
                                        ?>
                                                <option value="<?php echo (int)$row_pc->pc_id; ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($row_pc->pc_name); ?>
                                                </option>
                                        <?php 
                                            endwhile;
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- التصنيف الفرعي الفرعي -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Sub-Category:</label>
                                <div class="col-sm-8">
                                    <select id="auc_pc_id" name="auc_pc_id">
                                        <option value="">-- Select Sub-Category --</option>
                                        <?php
                                        if ($row->pc_parent_id > 0) {
                                            $sql_spc = "SELECT * FROM product_category WHERE pc_parent_id = " . (int)$row->pc_parent_id . " AND pc_status = '1'";
                                            $res_spc = mysqli_query($con, $sql_spc);
                                            while ($row_spc = mysqli_fetch_object($res_spc)):
                                                $selected = ($row_spc->pc_id == $row->auc_pc_id) ? 'selected="selected"' : '';
                                        ?>
                                                <option value="<?php echo (int)$row_spc->pc_id; ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($row_spc->pc_name); ?>
                                                </option>
                                        <?php 
                                            endwhile;
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- قيمة المزاد والعملة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Auction Value:</label>
                                <div class="col-sm-9">
                                    <input name="auc_value" id="auc_value" class="col-xs-12 col-sm-4" type="text" value="<?php echo htmlspecialchars($row->auc_value ?? ''); ?>" />
                                    &nbsp;
                                    <select name="auc_currency" id="auc_currency">
                                        <option value="">-- Select Currency --</option>
                                        <?php
                                        $currencysql = mysqli_query($con, "SELECT * FROM country WHERE cn_status = '1'");
                                        while ($currencyrow = mysqli_fetch_object($currencysql)):
                                            $selected = ($currencyrow->cn_id == $row->auc_currency) ? 'selected="selected"' : '';
                                        ?>
                                            <option value="<?php echo (int)$currencyrow->cn_id; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($currencyrow->cn_currency); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- نوع الإشعار -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Notice Type:</label>
                                <div class="col-sm-9">
                                    <input name="auc_notice_type" id="auc_notice_type" class="col-xs-12 col-sm-6" type="text" value="<?php echo htmlspecialchars($row->auc_notice_type ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- الكمية والوحدة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Quantity:</label>
                                <div class="col-sm-9">
                                    <input name="auc_qty" id="auc_qty" class="col-xs-12 col-sm-4" type="text" value="<?php echo htmlspecialchars($row->auc_qty ?? ''); ?>" />
                                    &nbsp;
                                    <select name="auc_qty_mu_id" id="auc_qty_mu_id">
                                        <option value="">-- Select Unit --</option>
                                        <?php
                                        $res_mu = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_status = '1'");
                                        while ($row_mu = mysqli_fetch_object($res_mu)):
                                            $selected = ($row_mu->mu_id == $row->auc_qty_mu_id) ? 'selected="selected"' : '';
                                        ?>
                                            <option value="<?php echo (int)$row_mu->mu_id; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($row_mu->mu_name); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- EMD -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">EMD:</label>
                                <div class="col-sm-9">
                                    <input name="auc_emd" id="auc_emd" class="col-xs-12 col-sm-6" type="text" value="<?php echo htmlspecialchars($row->auc_emd ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- رسوم المستندات والعملة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Fees:</label>
                                <div class="col-sm-9">
                                    <input name="auc_document_fees" id="auc_document_fees" class="col-xs-12 col-sm-4" type="text" value="<?php echo htmlspecialchars($row->auc_document_fees ?? ''); ?>" />
                                    &nbsp;
                                    <select name="auc_document_fees_currency" id="auc_document_fees_currency">
                                        <option value="">-- Select Currency --</option>
                                        <?php
                                        $currencysql2 = mysqli_query($con, "SELECT * FROM country WHERE cn_status = '1'");
                                        while ($currencyrow2 = mysqli_fetch_object($currencysql2)):
                                            $selected = ($currencyrow2->cn_id == $row->auc_document_fees_currency) ? 'selected="selected"' : '';
                                        ?>
                                            <option value="<?php echo (int)$currencyrow2->cn_id; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($currencyrow2->cn_currency); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- مدة المشروع -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Project Period:</label>
                                <div class="col-sm-9">
                                    <input name="auc_project_period" id="auc_project_period" class="col-xs-12 col-sm-6" type="text" value="<?php echo htmlspecialchars($row->auc_project_period ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- المنتجات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Products:</label>
                                <div class="col-sm-9">
                                    <input name="auc_products" id="auc_products" class="col-xs-12 col-sm-7" type="text" value="<?php echo htmlspecialchars($row->auc_products ?? ''); ?>" />
                                </div>
                            </div>
                            
                            <!-- تاريخ النشر -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Auction Publish Date:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="auc_publish_date" name="auc_publish_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo !empty($row->auc_publish_date) ? date("Y-m-d", strtotime($row->auc_publish_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- بدء بيع المستندات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Sale Starts:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="auc_docSaleStart_date" name="auc_docSaleStart_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo !empty($row->auc_docSaleStart_date) ? date("Y-m-d", strtotime($row->auc_docSaleStart_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- نهاية بيع المستندات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Sale Ends:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="auc_docSaleEnd_date" name="auc_docSaleEnd_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo !empty($row->auc_docSaleEnd_date) ? date("Y-m-d", strtotime($row->auc_docSaleEnd_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- آخر موعد لتقديم المستندات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Submit Before:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="auc_docSubmitBefore_date" name="auc_docSubmitBefore_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo !empty($row->auc_docSubmitBefore_date) ? date("Y-m-d", strtotime($row->auc_docSubmitBefore_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- تاريخ الاستحقاق -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Auction Due Date:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="auc_due_date" name="auc_due_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo !empty($row->auc_due_date) ? date("Y-m-d", strtotime($row->auc_due_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- معايير التأهيل المسبق -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Pre-qualification Criteria:</label>
                                <div class="col-sm-8">
                                    <textarea id="auc_prequalification_criteria" name="auc_prequalification_criteria" class="col-sm-8" rows="4"><?php echo htmlspecialchars(stripslashes($row->auc_prequalification_criteria ?? '')); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- التفاصيل -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Details:</label>
                                <div class="col-sm-8">
                                    <textarea id="auc_details" name="auc_details" class="col-sm-8" rows="4"><?php echo htmlspecialchars(stripslashes($row->auc_details ?? '')); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- تفضيلات الموقع -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Location Preferences:</label>
                                <div class="col-sm-8">
                                    <div class="radio">
                                        <label>
                                            <input type="radio" name="auc_preferred_location" value="abroad" <?php echo ($row->auc_preferred_location == 'abroad') ? 'checked="checked"' : ''; ?> />
                                            Abroad Only
                                        </label>
                                        &nbsp;&nbsp;&nbsp;
                                        <label>
                                            <input type="radio" name="auc_preferred_location" value="any" <?php echo ($row->auc_preferred_location == 'any') ? 'checked="checked"' : ''; ?> />
                                            Abroad + Domestic
                                        </label>
                                        &nbsp;&nbsp;&nbsp;
                                        <label>
                                            <input type="radio" name="auc_preferred_location" value="domestic" <?php echo ($row->auc_preferred_location == 'domestic') ? 'checked="checked"' : ''; ?> />
                                            Domestic Only
                                        </label>
                                        &nbsp;&nbsp;&nbsp;
                                        <label>
                                            <input type="radio" name="auc_preferred_location" value="my_city" <?php echo ($row->auc_preferred_location == 'my_city') ? 'checked="checked"' : ''; ?> />
                                            My City Only
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- زر التحديث -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate">
                                        <i class="icon-ok bigger-110"></i>Update
                                    </button>
                                    <button class="btn" type="button" onclick="window.location.href='auction-view.php'">
                                        <i class="icon-reply bigger-110"></i>Cancel
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

    $('#id-input-file-1 , #id-input-file-2').ace_file_input({
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