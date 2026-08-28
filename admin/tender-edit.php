<?php
/**
 * File: tender-edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل بيانات المناقصة
 * Edit tender information
 * 
 * Features:
 * - تعديل جميع بيانات المناقصة
 * - اختيار التصنيفات الرئيسية والفرعية
 * - تحديد التواريخ والمواعيد النهائية
 * - إدخال القيم المالية والعملات
 * - التحقق من صحة المدخلات
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class TenderEditor
 * 
 * Handles tender editing operations
 */
class TenderEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Tender ID */
    private int $tnd_id;
    
    /** @var string Tender heading */
    private string $tnd_heading = '';
    
    /** @var int Main category ID */
    private int $mcat_id = 0;
    
    /** @var int Category ID */
    private int $pc_id = 0;
    
    /** @var int Subcategory ID */
    private int $tnd_pc_id = 0;
    
    /** @var float Tender value */
    private float $tnd_value = 0.0;
    
    /** @var int Currency ID */
    private int $tnd_currency = 0;
    
    /** @var string Notice type */
    private string $tnd_notice_type = '';
    
    /** @var float Quantity */
    private float $tnd_qty = 0.0;
    
    /** @var int Quantity unit ID */
    private int $tnd_qty_mu_id = 0;
    
    /** @var string EMD */
    private string $tnd_emd = '';
    
    /** @var float Document fees */
    private float $tnd_document_fees = 0.0;
    
    /** @var int Document fees currency ID */
    private int $tnd_document_fees_currency = 0;
    
    /** @var string Project period */
    private string $tnd_project_period = '';
    
    /** @var string Products */
    private string $tnd_products = '';
    
    /** @var string Publish date */
    private string $tnd_publish_date = '';
    
    /** @var string Document sale start date */
    private string $tnd_docSaleStart_date = '';
    
    /** @var string Document sale end date */
    private string $tnd_docSaleEnd_date = '';
    
    /** @var string Document submit before date */
    private string $tnd_docSubmitBefore_date = '';
    
    /** @var string Due date */
    private string $tnd_due_date = '';
    
    /** @var string Pre-qualification criteria */
    private string $tnd_prequalification_criteria = '';
    
    /** @var string Tender details */
    private string $tnd_details = '';
    
    /** @var string Preferred location */
    private string $tnd_preferred_location = 'any';
    
    /**
     * Constructor
     * 
     * @param string $token MD5 token
     */
    public function __construct(string $token) {
        $this->tnd_id = $this->decodeToken($token);
    }
    
    /**
     * Decode MD5 token to ID
     * 
     * @param string $token MD5 token
     * @return int Tender ID
     */
    private function decodeToken(string $token): int {
        global $con;
        
        $sql = "SELECT tnd_id FROM tender WHERE md5(tnd_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return (int)$row['tnd_id'];
        }
        
        mysqli_stmt_close($stmt);
        return 0;
    }
    
    /**
     * Get tender details
     * 
     * @return object|null Tender details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT t.*, pc.pc_parent_id, pc.pc_name 
                FROM tender t
                JOIN product_category pc ON t.tnd_pc_id = pc.pc_id 
                WHERE t.tnd_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->tnd_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        if (empty($this->tnd_heading)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Tender heading.</div>';
            return false;
        }
        
        if ($this->mcat_id <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Main category.</div>';
            return false;
        }
        
        if ($this->pc_id <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Category.</div>';
            return false;
        }
        
        if ($this->tnd_pc_id <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Sub-Category.</div>';
            return false;
        }
        
        if ($this->tnd_value > 0 && $this->tnd_currency <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Currency for Tender Value.</div>';
            return false;
        }
        
        if (empty($this->tnd_notice_type)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Notice Type.</div>';
            return false;
        }
        
        if ($this->tnd_document_fees <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Document Fees.</div>';
            return false;
        }
        
        if ($this->tnd_document_fees_currency <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select currency for Document Fees.</div>';
            return false;
        }
        
        if (empty($this->tnd_publish_date) || $this->tnd_publish_date === '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select tender publish date.</div>';
            return false;
        }
        
        if (empty($this->tnd_docSaleStart_date) || $this->tnd_docSaleStart_date === '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document sale start date.</div>';
            return false;
        }
        
        if (empty($this->tnd_docSaleEnd_date) || $this->tnd_docSaleEnd_date === '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document sale end date.</div>';
            return false;
        }
        
        if (empty($this->tnd_docSubmitBefore_date) || $this->tnd_docSubmitBefore_date === '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document submit before date.</div>';
            return false;
        }
        
        if (empty($this->tnd_due_date) || $this->tnd_due_date === '0000-00-00') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select tender due date.</div>';
            return false;
        }
        
        if (empty($this->tnd_prequalification_criteria)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Pre-qualification criteria.</div>';
            return false;
        }
        
        if (empty($this->tnd_details)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter tender details.</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Update tender
     */
    public function update(): void {
        global $con;
        
        $sql = "UPDATE tender SET
                tnd_pc_id = ?,
                tnd_heading = ?,
                tnd_value = ?,
                tnd_currency = ?,
                tnd_notice_type = ?,
                tnd_qty = ?,
                tnd_qty_mu_id = ?,
                tnd_emd = ?,
                tnd_document_fees = ?,
                tnd_document_fees_currency = ?,
                tnd_project_period = ?,
                tnd_products = ?,
                tnd_prequalification_criteria = ?,
                tnd_details = ?,
                tnd_preferred_location = ?,
                tnd_publish_date = ?,
                tnd_docSaleStart_date = ?,
                tnd_docSaleEnd_date = ?,
                tnd_docSubmitBefore_date = ?,
                tnd_due_date = ?,
                tnd_updated_date = NOW()
                WHERE tnd_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "isdisisississsssssssi",
            $this->tnd_pc_id,
            $this->tnd_heading,
            $this->tnd_value,
            $this->tnd_currency,
            $this->tnd_notice_type,
            $this->tnd_qty,
            $this->tnd_qty_mu_id,
            $this->tnd_emd,
            $this->tnd_document_fees,
            $this->tnd_document_fees_currency,
            $this->tnd_project_period,
            $this->tnd_products,
            $this->tnd_prequalification_criteria,
            $this->tnd_details,
            $this->tnd_preferred_location,
            $this->tnd_publish_date,
            $this->tnd_docSaleStart_date,
            $this->tnd_docSaleEnd_date,
            $this->tnd_docSubmitBefore_date,
            $this->tnd_due_date,
            $this->tnd_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Tender updated successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to update tender</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Magic setter for properties
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     */
    public function __set(string $name, $value): void {
        if (property_exists($this, $name)) {
            if (in_array($name, ['tnd_value', 'tnd_qty', 'tnd_document_fees'])) {
                $this->$name = (float)$value;
            } elseif (in_array($name, ['tnd_id', 'mcat_id', 'pc_id', 'tnd_pc_id', 'tnd_currency', 'tnd_qty_mu_id', 'tnd_document_fees_currency'])) {
                $this->$name = (int)$value;
            } else {
                $this->$name = $value;
            }
        }
    }
}

// Get token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("Location: tender-view.php");
    exit;
}

// Initialize editor
$editor = new TenderEditor($token);
$row = $editor->getDetails();

if (!$row) {
    header("Location: tender-view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editor->tnd_id = (int)($_POST['tnd_id'] ?? 0);
    $editor->tnd_heading = trim($_POST['tnd_heading'] ?? '');
    $editor->mcat_id = (int)($_POST['mcat_id'] ?? 0);
    $editor->pc_id = (int)($_POST['pc_id'] ?? 0);
    $editor->tnd_pc_id = (int)($_POST['tnd_pc_id'] ?? 0);
    $editor->tnd_value = (float)($_POST['tnd_value'] ?? 0);
    $editor->tnd_currency = (int)($_POST['tnd_currency'] ?? 0);
    $editor->tnd_notice_type = trim($_POST['tnd_notice_type'] ?? '');
    $editor->tnd_qty = (float)($_POST['tnd_qty'] ?? 0);
    $editor->tnd_qty_mu_id = (int)($_POST['tnd_qty_mu_id'] ?? 0);
    $editor->tnd_emd = trim($_POST['tnd_emd'] ?? '');
    $editor->tnd_document_fees = (float)($_POST['tnd_document_fees'] ?? 0);
    $editor->tnd_document_fees_currency = (int)($_POST['tnd_document_fees_currency'] ?? 0);
    $editor->tnd_project_period = trim($_POST['tnd_project_period'] ?? '');
    $editor->tnd_products = trim($_POST['tnd_products'] ?? '');
    $editor->tnd_publish_date = trim($_POST['tnd_publish_date'] ?? '');
    $editor->tnd_docSaleStart_date = trim($_POST['tnd_docSaleStart_date'] ?? '');
    $editor->tnd_docSaleEnd_date = trim($_POST['tnd_docSaleEnd_date'] ?? '');
    $editor->tnd_docSubmitBefore_date = trim($_POST['tnd_docSubmitBefore_date'] ?? '');
    $editor->tnd_due_date = trim($_POST['tnd_due_date'] ?? '');
    $editor->tnd_prequalification_criteria = trim($_POST['tnd_prequalification_criteria'] ?? '');
    $editor->tnd_details = trim($_POST['tnd_details'] ?? '');
    $editor->tnd_preferred_location = trim($_POST['tnd_preferred_location'] ?? 'any');
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header('Location: ../tender-email.php?admn_tnd_id=' . $editor->tnd_id);
    exit;
}

// Get main categories for dropdown
$mainCategories = [];
$result = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id = 0 AND pc_status = 1 ORDER BY pc_name");
while ($cat = mysqli_fetch_object($result)) {
    $mainCategories[] = $cat;
}

// Get measurement units
$units = [];
$unitResult = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_status = 1 ORDER BY mu_name");
while ($unit = mysqli_fetch_object($unitResult)) {
    $units[] = $unit;
}

// Get countries for currency
$countries = [];
$countryResult = mysqli_query($con, "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name");
while ($country = mysqli_fetch_object($countryResult)) {
    $countries[] = $country;
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
                    }).fail(function() {
                        alert('Failed to load categories');
                    });
                }
            }
            
            function showSubcat() {
                var pc_id = document.getElementById('pc_id').value;
                if (pc_id) {
                    $.get("showSubcat.php", {q: pc_id}, function(data) {
                        $('#tnd_pc_id').html(data);
                    }).fail(function() {
                        alert('Failed to load subcategories');
                    });
                }
            }
            
            function validateForm() {
                var mcat_id = document.getElementById('mcat_id');
                var pc_id = document.getElementById('pc_id');
                var tnd_pc_id = document.getElementById('tnd_pc_id');
                var tnd_heading = document.getElementById('tnd_heading');
                var tnd_value = document.getElementById('tnd_value');
                var tnd_currency = document.getElementById('tnd_currency');
                var tnd_notice_type = document.getElementById('tnd_notice_type');
                var tnd_qty = document.getElementById('tnd_qty');
                var tnd_qty_mu_id = document.getElementById('tnd_qty_mu_id');
                var tnd_document_fees = document.getElementById('tnd_document_fees');
                var tnd_document_fees_currency = document.getElementById('tnd_document_fees_currency');
                var tnd_prequalification_criteria = document.getElementById('tnd_prequalification_criteria');
                var tnd_details = document.getElementById('tnd_details');
                
                var message = "";
                var valid = true;
                
                if (mcat_id.value === '') {
                    message = "Kindly select Main Category.";
                    mcat_id.focus();
                    valid = false;
                } else if (pc_id.value === '') {
                    message = "Kindly select Category.";
                    pc_id.focus();
                    valid = false;
                } else if (tnd_pc_id.value === '' || tnd_pc_id.value === '0') {
                    message = "Kindly select Sub-Category.";
                    tnd_pc_id.focus();
                    valid = false;
                } else if (tnd_heading.value.trim() === '') {
                    message = "Kindly enter Tender Heading.";
                    tnd_heading.focus();
                    valid = false;
                } else if (tnd_value.value !== '' && isNaN(tnd_value.value)) {
                    message = "Kindly enter valid Tender value.";
                    tnd_value.focus();
                    valid = false;
                } else if (tnd_value.value !== '' && tnd_value.value !== '0' && tnd_currency.value === '') {
                    message = "Kindly select currency for Tender Value.";
                    tnd_currency.focus();
                    valid = false;
                } else if (tnd_notice_type.value.trim() === '') {
                    message = "Kindly enter Notice Type.";
                    tnd_notice_type.focus();
                    valid = false;
                } else if (tnd_qty.value !== '' && isNaN(tnd_qty.value)) {
                    message = "Kindly enter valid Quantity.";
                    tnd_qty.focus();
                    valid = false;
                } else if (tnd_qty.value !== '' && tnd_qty.value !== '0' && tnd_qty_mu_id.value === '') {
                    message = "Kindly select Quantity Unit.";
                    tnd_qty_mu_id.focus();
                    valid = false;
                } else if (tnd_document_fees.value === '' || tnd_document_fees.value === '0') {
                    message = "Kindly enter Document Fees.";
                    tnd_document_fees.focus();
                    valid = false;
                } else if (tnd_document_fees.value !== '' && isNaN(tnd_document_fees.value)) {
                    message = "Kindly enter valid Document Fees.";
                    tnd_document_fees.focus();
                    valid = false;
                } else if (tnd_document_fees_currency.value === '') {
                    message = "Kindly select currency for Document Fees.";
                    tnd_document_fees_currency.focus();
                    valid = false;
                } else if (tnd_prequalification_criteria.value.trim() === '') {
                    message = "Kindly describe Pre-qualification Criteria.";
                    tnd_prequalification_criteria.focus();
                    valid = false;
                } else if (tnd_details.value.trim() === '') {
                    message = "Kindly describe Tender details.";
                    tnd_details.focus();
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
                        <a href="tender-view.php">Manage Tenders</a>
                    </li>
                    <li class="active">Edit Tender</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit Tender
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars($row->tnd_heading ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" onsubmit="return validateForm();">
                            
                            <input type="hidden" name="tnd_id" value="<?php echo (int)$row->tnd_id; ?>" />

                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Tender Heading -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Tender Heading:</label>
                                <div class="col-sm-9">
                                    <input name="tnd_heading" id="tnd_heading" class="col-xs-12 col-sm-6" type="text" 
                                           value="<?php echo htmlspecialchars($row->tnd_heading ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Main Category -->
                            <div class="form-group">
                                <?php
                                // Get main category ID from current tender
                                $mainCatSql = "SELECT pc_parent_id FROM product_category WHERE pc_id = ?";
                                $stmt = mysqli_prepare($con, $mainCatSql);
                                $mainCatId = 0;
                                if ($stmt) {
                                    mysqli_stmt_bind_param($stmt, "i", $row->pc_parent_id);
                                    mysqli_stmt_execute($stmt);
                                    $mainCatResult = mysqli_stmt_get_result($stmt);
                                    $mainCatRow = mysqli_fetch_object($mainCatResult);
                                    $mainCatId = $mainCatRow->pc_parent_id ?? 0;
                                    mysqli_stmt_close($stmt);
                                }
                                ?>
                                <label class="col-sm-3 control-label no-padding-right">Main Category:</label>
                                <div class="col-sm-9">
                                    <select id="mcat_id" name="mcat_id" onchange="showCategory();" class="chosen-select" style="width:300px;">
                                        <option value="">Select Main Category</option>
                                        <?php foreach ($mainCategories as $cat): ?>
                                            <option value="<?php echo (int)$cat->pc_id; ?>" 
                                                <?php echo ((int)$cat->pc_id === (int)$mainCatId) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($cat->pc_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Category:</label>
                                <div class="col-sm-8">
                                    <select id="pc_id" name="pc_id" onchange="showSubcat();" style="width:300px;">
                                        <option value="">Select Category</option>
                                        <?php
                                        $catSql = "SELECT * FROM product_category WHERE pc_parent_id != 0 AND pc_parent_id = ? ORDER BY pc_name";
                                        $stmt = mysqli_prepare($con, $catSql);
                                        if ($stmt) {
                                            mysqli_stmt_bind_param($stmt, "i", $mainCatId);
                                            mysqli_stmt_execute($stmt);
                                            $catResult = mysqli_stmt_get_result($stmt);
                                            while ($catRow = mysqli_fetch_object($catResult)) {
                                                $selected = ((int)$catRow->pc_id === (int)$row->pc_parent_id) ? 'selected="selected"' : '';
                                                echo '<option value="' . (int)$catRow->pc_id . '" ' . $selected . '>' . 
                                                     htmlspecialchars($catRow->pc_name, ENT_QUOTES, 'UTF-8') . '</option>';
                                            }
                                            mysqli_stmt_close($stmt);
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Sub-Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Sub-Category:</label>
                                <div class="col-sm-8">
                                    <select id="tnd_pc_id" name="tnd_pc_id" style="width:300px;">
                                        <option value="0">- Select Sub-Category -</option>
                                        <?php
                                        $subCatSql = "SELECT * FROM product_category WHERE pc_parent_id = ? ORDER BY pc_name";
                                        $stmt = mysqli_prepare($con, $subCatSql);
                                        if ($stmt) {
                                            mysqli_stmt_bind_param($stmt, "i", $row->pc_parent_id);
                                            mysqli_stmt_execute($stmt);
                                            $subCatResult = mysqli_stmt_get_result($stmt);
                                            while ($subCatRow = mysqli_fetch_object($subCatResult)) {
                                                $selected = ((int)$subCatRow->pc_id === (int)$row->tnd_pc_id) ? 'selected="selected"' : '';
                                                echo '<option value="' . (int)$subCatRow->pc_id . '" ' . $selected . '>' . 
                                                     htmlspecialchars($subCatRow->pc_name, ENT_QUOTES, 'UTF-8') . '</option>';
                                            }
                                            mysqli_stmt_close($stmt);
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Tender Value -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Tender Value:</label>
                                <div class="col-sm-9">
                                    <input name="tnd_value" id="tnd_value" class="col-xs-12 col-sm-4" type="number" step="0.01" 
                                           value="<?php echo (float)($row->tnd_value ?? 0); ?>" />
                                    &nbsp;
                                    <select name="tnd_currency" id="tnd_currency" style="width:150px;">
                                        <option value="">- Select Currency -</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>" 
                                                <?php echo ((int)($row->tnd_currency ?? 0) === (int)$country->cn_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($country->cn_currency, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Notice Type -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Notice Type:</label>
                                <div class="col-sm-9">
                                    <input name="tnd_notice_type" id="tnd_notice_type" class="col-xs-12 col-sm-6" type="text" 
                                           value="<?php echo htmlspecialchars($row->tnd_notice_type ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Quantity -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Quantity:</label>
                                <div class="col-sm-9">
                                    <input name="tnd_qty" id="tnd_qty" class="col-xs-12 col-sm-4" type="number" step="0.01" 
                                           value="<?php echo (float)($row->tnd_qty ?? 0); ?>" />
                                    &nbsp;
                                    <select name="tnd_qty_mu_id" id="tnd_qty_mu_id" style="width:150px;">
                                        <option value="">- Select Unit -</option>
                                        <?php foreach ($units as $unit): ?>
                                            <option value="<?php echo (int)$unit->mu_id; ?>" 
                                                <?php echo ((int)($row->tnd_qty_mu_id ?? 0) === (int)$unit->mu_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($unit->mu_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- EMD -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">EMD:</label>
                                <div class="col-sm-9">
                                    <input name="tnd_emd" id="tnd_emd" class="col-xs-12 col-sm-6" type="text" 
                                           value="<?php echo htmlspecialchars($row->tnd_emd ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Document Fees -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Document Fees:</label>
                                <div class="col-sm-9">
                                    <input name="tnd_document_fees" id="tnd_document_fees" class="col-xs-12 col-sm-4" type="number" step="0.01" 
                                           value="<?php echo (float)($row->tnd_document_fees ?? 0); ?>" />
                                    &nbsp;
                                    <select name="tnd_document_fees_currency" id="tnd_document_fees_currency" style="width:150px;">
                                        <option value="">- Select Currency -</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>" 
                                                <?php echo ((int)($row->tnd_document_fees_currency ?? 0) === (int)$country->cn_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($country->cn_currency, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Project Period -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Project Period:</label>
                                <div class="col-sm-9">
                                    <input name="tnd_project_period" id="tnd_project_period" class="col-xs-12 col-sm-6" type="text" 
                                           value="<?php echo htmlspecialchars($row->tnd_project_period ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Products -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Products:</label>
                                <div class="col-sm-9">
                                    <input name="tnd_products" id="tnd_products" class="col-xs-12 col-sm-7" type="text" 
                                           value="<?php echo htmlspecialchars($row->tnd_products ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Publish Date -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Publish Date:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="tnd_publish_date" name="tnd_publish_date" 
                                               data-date-format="yyyy-mm-dd" type="text" 
                                               value="<?php echo !empty($row->tnd_publish_date) ? date("Y-m-d", strtotime($row->tnd_publish_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Document Sale Start -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Document Sale Starts:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="tnd_docSaleStart_date" name="tnd_docSaleStart_date" 
                                               data-date-format="yyyy-mm-dd" type="text" 
                                               value="<?php echo !empty($row->tnd_docSaleStart_date) ? date("Y-m-d", strtotime($row->tnd_docSaleStart_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Document Sale End -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Document Sale Ends:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="tnd_docSaleEnd_date" name="tnd_docSaleEnd_date" 
                                               data-date-format="yyyy-mm-dd" type="text" 
                                               value="<?php echo !empty($row->tnd_docSaleEnd_date) ? date("Y-m-d", strtotime($row->tnd_docSaleEnd_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Document Submit Before -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Document Submit Before:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="tnd_docSubmitBefore_date" name="tnd_docSubmitBefore_date" 
                                               data-date-format="yyyy-mm-dd" type="text" 
                                               value="<?php echo !empty($row->tnd_docSubmitBefore_date) ? date("Y-m-d", strtotime($row->tnd_docSubmitBefore_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Due Date -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Due Date:</label>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <input class="form-control date-picker" id="tnd_due_date" name="tnd_due_date" 
                                               data-date-format="yyyy-mm-dd" type="text" 
                                               value="<?php echo !empty($row->tnd_due_date) ? date("Y-m-d", strtotime($row->tnd_due_date)) : ''; ?>" />
                                        <span class="input-group-addon">
                                            <i class="icon-calendar bigger-110"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pre-qualification Criteria -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Pre-qualification Criteria:</label>
                                <div class="col-sm-8">
                                    <textarea id="tnd_prequalification_criteria" name="tnd_prequalification_criteria" class="col-sm-8" rows="4"><?php 
                                        echo htmlspecialchars($row->tnd_prequalification_criteria ?? '', ENT_QUOTES, 'UTF-8'); 
                                    ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Details -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Details:</label>
                                <div class="col-sm-8">
                                    <textarea id="tnd_details" name="tnd_details" class="col-sm-8" rows="4"><?php 
                                        echo htmlspecialchars($row->tnd_details ?? '', ENT_QUOTES, 'UTF-8'); 
                                    ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Location Preferences -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Location Preferences:</label>
                                <div class="radio col-sm-8">
                                    <?php
                                    $locations = [
                                        'abroad' => 'Abroad Only',
                                        'any' => 'Abroad + Domestic',
                                        'domestic' => 'Domestic Only',
                                        'my_city' => 'My City Only'
                                    ];
                                    $currentLocation = $row->tnd_preferred_location ?? 'any';
                                    foreach ($locations as $value => $label):
                                    ?>
                                        <label style="margin-right:15px;">
                                            <input type="radio" name="tnd_preferred_location" class="ace" 
                                                   value="<?php echo $value; ?>" 
                                                   <?php echo ($currentLocation === $value) ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> <?php echo $label; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <a href="tender-view.php" class="btn btn-primary">
                                        <i class="icon-arrow-left bigger-110"></i> Back to List
                                    </a>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                    <br clear="all"/>
                </div>
            </div>
            <br clear="all" />
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript includes and initialization -->
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

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize chosen selects
        $(".chosen-select").chosen({width: '300px'});
        
        // Initialize date pickers
        $('.date-picker').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            todayHighlight: true
        }).next().on(ace.click_event, function() {
            $(this).prev().focus();
        });
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Initialize autosize for textareas
        $('textarea').autosize({append: "\n"});
    });
</script>

<style>
    .btn {
        margin-right: 5px;
    }
    .radio label {
        margin-right: 15px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>