<?php
/**
 * File: support_change.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة وتعديل أسئلة وأجوبة الدعم الفني
 * Add and edit support FAQs
 * 
 * Features:
 * - إضافة سؤال وجواب جديد
 * - تعديل سؤال وجواب موجود
 * - اختيار التصنيف المناسب
 * - عرض قائمة الأسئلة حسب التصنيف
 * - حذف الأسئلة
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
check_admin_Login();

// Get parameters
$cf_fc_id = $_GET['hid'] ?? '';
$pid = $_GET['pid'] ?? '';
$mode = $_GET['mode'] ?? '';

// If editing, fetch existing data
$row = [];
if ($mode === 'edit' && !empty($pid)) {
    $recObj_sql = "SELECT * FROM custom_faq WHERE cf_id = ?";
    $stmt = mysqli_prepare($con, $recObj_sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $pid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($row) {
            $cf_fc_id = $row['cf_fc_id'];
        }
    }
}

/**
 * Class CustomFAQManager
 * 
 * Handles custom FAQ management operations
 */
class CustomFAQManager {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int FAQ ID */
    private int $pid;
    
    /** @var string Mode (add/edit) */
    private string $mode;
    
    /** @var int Category ID */
    private int $cf_fc_id;
    
    /** @var string FAQ heading/question */
    private string $cf_heading;
    
    /** @var string FAQ content/answer */
    private string $cf_content;
    
    /**
     * Constructor
     * 
     * @param int $pid FAQ ID
     * @param string $mode Mode
     * @param int $cf_fc_id Category ID
     * @param string $cf_heading Question
     * @param string $cf_content Answer
     */
    public function __construct(
        int $pid,
        string $mode,
        int $cf_fc_id,
        string $cf_heading,
        string $cf_content
    ) {
        $this->pid = $pid;
        $this->mode = $mode;
        $this->cf_fc_id = $cf_fc_id;
        $this->cf_heading = $cf_heading;
        $this->cf_content = $cf_content;
        
        // Store in session for form persistence
        $_SESSION['cf_heading'] = $this->cf_heading;
        $_SESSION['cf_content'] = $this->cf_content;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        if ($this->cf_fc_id <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select a Category.</div>';
            return false;
        }
        
        if (empty($this->cf_heading)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a question/heading.</div>';
            return false;
        }
        
        if (empty($this->cf_content)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter an answer/content.</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Add new FAQ
     */
    public function add(): void {
        global $con;
        
        $sql = "INSERT INTO custom_faq SET
                cf_fc_id = ?,
                cf_heading = ?,
                cf_content = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "iss", $this->cf_fc_id, $this->cf_heading, $this->cf_content);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> FAQ added successfully.</div>';
            unset($_SESSION['cf_heading']);
            unset($_SESSION['cf_content']);
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to add FAQ</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Edit existing FAQ
     */
    public function edit(): void {
        global $con;
        
        $sql = "UPDATE custom_faq SET
                cf_fc_id = ?,
                cf_heading = ?,
                cf_content = ?
                WHERE cf_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "issi", $this->cf_fc_id, $this->cf_heading, $this->cf_content, $this->pid);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> FAQ updated successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to update FAQ</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get all FAQ categories
     * 
     * @return array List of categories
     */
    public static function getCategories(): array {
        global $con;
        
        $categories = [];
        $sql = "SELECT fc_id, fc_name FROM faq_categories ORDER BY fc_name";
        $result = mysqli_query($con, $sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = [
                'id' => (int)$row['fc_id'],
                'name' => $row['fc_name']
            ];
        }
        
        return $categories;
    }
    
    /**
     * Magic setter for properties
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     */
    public function __set(string $name, $value): void {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
}

// Handle session messages
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get persisted form values
$cf_heading = $_SESSION['cf_heading'] ?? ($row['cf_heading'] ?? '');
$cf_content = $_SESSION['cf_content'] ?? ($row['cf_content'] ?? '');

// Handle form submission for Add
if (isset($_POST['btnAdd'])) {
    
    $faqManager = new CustomFAQManager(
        (int)($_POST['pid'] ?? 0),
        $_POST['mode'] ?? '',
        (int)($_POST['cf_fc_id'] ?? 0),
        trim($_POST['cf_heading'] ?? ''),
        trim($_POST['cf_content'] ?? '')
    );
    
    if ($faqManager->validate()) {
        $faqManager->add();
    }
    
    $_SESSION['msg'] = $faqManager->msg;
    header("Location: support_change.php?hid=" . urlencode((string)($_POST['cf_fc_id'] ?? '')));
    exit;
}

// Handle form submission for Update
if (isset($_POST['btnUpdate'])) {
    
    $faqManager = new CustomFAQManager(
        (int)($_POST['pid'] ?? 0),
        $_POST['mode'] ?? '',
        (int)($_POST['cf_fc_id'] ?? 0),
        trim($_POST['cf_heading'] ?? ''),
        trim($_POST['cf_content'] ?? '')
    );
    
    if ($faqManager->validate()) {
        $faqManager->edit();
    }
    
    $_SESSION['msg'] = $faqManager->msg;
    header("Location: support_change.php?pid=" . urlencode($_POST['pid'] ?? '') . "&mode=" . urlencode($_POST['mode'] ?? ''));
    exit;
}

// Get all categories for dropdown
$categories = CustomFAQManager::getCategories();
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
            function GetPageList() {
                var hid = $("#cf_fc_id").val();
                if (hid && hid !== '') {
                    $('#cus_pages').html('<img src="images/loader.gif" border="0">');
                    $.get("cus_support.php", {hid: hid}, function(data) {
                        $('#cus_pages').html(data);
                    }).fail(function() {
                        $('#cus_pages').html('<div class="alert alert-danger">Failed to load FAQs</div>');
                    });
                } else {
                    $('#cus_pages').html('');
                }
            }
            
            function DelPageList(hid) {
                if (confirm('Are you sure you want to delete this FAQ?')) {
                    $.get("del_cus_faq.php", {hid: hid}, function(data) {
                        GetPageList();
                    }).fail(function() {
                        alert('Failed to delete FAQ');
                    });
                }
            }
            
            // Initialize on page load
            $(document).ready(function() {
                GetPageList();
            });
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
                    <li class="active">Manage FAQs</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage FAQs
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo $mode === 'edit' ? 'Edit FAQ' : 'Add New FAQ'; ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <!-- Main Form -->
                        <form class="form-horizontal" action="" method="post">
                            
                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Category Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Support Category</label>
                                <div class="col-sm-9">
                                    <select name="cf_fc_id" id="cf_fc_id" class="chosen-select" onchange="GetPageList()" style="width:300px;">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo (int)$cat['id']; ?>" 
                                                <?php echo ((int)$cf_fc_id === (int)$cat['id']) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Question/Heading -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Question/Heading</label>
                                <div class="col-sm-9">
                                    <input name="cf_heading" id="cf_heading" class="col-xs-10 col-sm-7" type="text" 
                                           value="<?php echo htmlspecialchars($cf_heading, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Enter the question"/>
                                </div>
                            </div>
                            
                            <!-- Answer/Content -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Answer/Content</label>
                                <div class="col-sm-9">
                                    <textarea name="cf_content" id="cf_content" class="col-xs-10 col-sm-7" rows="8" 
                                              placeholder="Enter the answer"><?php echo htmlspecialchars($cf_content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid, ENT_QUOTES, 'UTF-8'); ?>"/>
                                    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode, ENT_QUOTES, 'UTF-8'); ?>"/>
                                    
                                    <?php if ($mode === 'edit'): ?>
                                        <button class="btn btn-info" type="submit" name="btnUpdate">
                                            <i class="icon-ok bigger-110"></i> Update
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-info" type="submit" name="btnAdd">
                                            <i class="icon-ok bigger-110"></i> Add
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i> Reset
                                    </button>
                                    
                                    <a href="support-category-list.php" class="btn btn-primary">
                                        <i class="icon-folder-open bigger-110"></i> Manage Categories
                                    </a>
                                </div>
                            </div>
                        </form>
                        
                        <!-- FAQ List Container -->
                        <div id="cus_pages" class="mt-20"></div>
                        
                    </div>
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
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Auto-resize textarea
        $('#cf_content').autosize({append: "\n"});
        
        // Real-time validation
        $('#cf_heading, #cf_content').on('keyup', function() {
            if ($(this).val().trim() === '') {
                $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
            } else {
                $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
            }
        });
    });
</script>

<style>
    .mt-20 {
        margin-top: 20px;
    }
    .has-error input, .has-error textarea {
        border-color: #d15b47 !important;
    }
    .has-success input, .has-success textarea {
        border-color: #82af6f !important;
    }
    .btn {
        margin-right: 5px;
    }
    .alert {
        margin-bottom: 20px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>