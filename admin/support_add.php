<?php
/**
 * File: support_add.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة تصنيف دعم جديد
 * Add new support category
 * 
 * Features:
 * - إضافة تصنيف جديد للدعم الفني
 * - التحقق من صحة المدخلات
 * - منع التكرار
 * - رسائل نجاح/خطأ
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
check_admin_Login();

// Generate random number (kept for compatibility)
$randNo = rand(10000, 55555);

/**
 * Class SupportCategoryAdder
 * 
 * Handles support category addition operations
 */
class SupportCategoryAdder {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Category name */
    private string $fc_name = '';
    
    /**
     * Constructor
     * 
     * @param string $fc_name Category name
     */
    public function __construct(string $fc_name) {
        $this->fc_name = $fc_name;
        
        // Store in session for form persistence
        $_SESSION['fc_name'] = $this->fc_name;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        // Validate category name
        if (empty($this->fc_name)) {
            $this->msg = '<font color="#CC0000">Please enter category name</font>';
            return false;
        }
        
        if (strlen($this->fc_name) < 2) {
            $this->msg = '<font color="#CC0000">Category name must be at least 2 characters</font>';
            return false;
        }
        
        if (strlen($this->fc_name) > 100) {
            $this->msg = '<font color="#CC0000">Category name must not exceed 100 characters</font>';
            return false;
        }
        
        // Check for duplicate category name
        if ($this->isDuplicateName()) {
            $this->msg = '<font color="#CC0000">A category with this name already exists</font>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if category name already exists
     * 
     * @return bool True if duplicate exists
     */
    private function isDuplicateName(): bool {
        global $con;
        
        $sql = "SELECT COUNT(*) as count FROM faq_categories WHERE fc_name = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->fc_name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Add support category
     */
    public function add(): void {
        global $con;
        
        $sql = "INSERT INTO faq_categories SET fc_name = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">Database error</font>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->fc_name);
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear session variable
            unset($_SESSION['fc_name']);
            
            $this->msg = '<font color="#009900">Category added successfully</font>';
        } else {
            $this->msg = '<font color="#CC0000">Failed to add category</font>';
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
            $this->$name = $value;
        }
    }
}

// Handle session messages and form persistence
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$fc_name = $_SESSION['fc_name'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd'])) {
    
    $categoryAdder = new SupportCategoryAdder(
        trim($_POST['fc_name'] ?? '')
    );
    
    // Store in session for form persistence
    $_SESSION['fc_name'] = trim($_POST['fc_name'] ?? '');
    
    if ($categoryAdder->validate()) {
        $categoryAdder->add();
    }
    
    $_SESSION['msg'] = $categoryAdder->msg;
    header("Location: support_add.php");
    exit;
}
?>

<?php include "includes/admin-top.php" ?>

<!-- jQuery -->
<script src="../js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<script type="text/javascript">
function validateForm() {
    var fc_name = document.getElementById('fc_name');
    var message = "";
    var valid = true;
    
    if (fc_name.value.trim() === '') {
        message = 'Please enter category name';
        fc_name.focus();
        valid = false;
    }
    
    if (!valid) {
        document.getElementById('message').style.color = "red";
        document.getElementById('message').innerHTML = message;
    }
    
    return valid;
}
</script>

<!-- Styles -->
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<style>
    .required {
        color: #F00;
    }
    #message {
        margin: 10px 0;
        padding: 10px;
        border-radius: 3px;
    }
    #message font[color="#009900"] {
        color: #009900;
        font-weight: bold;
    }
    #message font[color="#CC0000"] {
        color: #CC0000;
        font-weight: bold;
    }
    .delete-btn {
        background: #d15b47;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        margin-bottom: 10px;
    }
    .delete-btn:hover {
        background: #b74635;
    }
    .x2-button {
        background: #2c3e50;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 3px;
        cursor: pointer;
    }
    .x2-button:hover {
        background: #1a2632;
    }
    .formItem {
        margin-bottom: 15px;
    }
    .formItem label {
        display: inline-block;
        vertical-align: top;
        font-weight: bold;
    }
    .formInputBox {
        display: inline-block;
    }
    .reg_txtfld {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-family: Arial, sans-serif;
    }
    .help-text {
        font-size: 11px;
        color: #777;
        margin: 3px 0 0 0;
    }
    .has-error input {
        border-color: #d15b47 !important;
    }
    .has-success input {
        border-color: #82af6f !important;
    }
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&rsaquo;&nbsp;&nbsp;Manage Support&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Add Category</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" onsubmit="return validateForm();">
                
                <em style="display:block;margin:5px;">Fields with <span class="required">*</span> are required.</em>
                
                <input type="button" class="delete-btn" onClick="window.location ='support-category-list.php'" value="View All Categories">
                
                <div id="message"><?php echo $msg; ?></div>
                <br />

                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            
                                            <!-- Category Name -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Category Name: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input name="fc_name" id="fc_name" class="reg_txtfld" type="text" 
                                                           value="<?php echo htmlspecialchars($fc_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                                           placeholder="Enter category name"/>
                                                    <p class="help-text">e.g., Technical Support, Billing, General Inquiry</p>
                                                </div>
                                            </div>
                                            
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row buttons">
                    <input type="submit" name="btnAdd" id="btnAdd" value="Add Category" class="x2-button" style="margin-right:10px;margin-top:5px;">
                </div>
            </form>    
            <br clear="all"/>
        </div>
    </div>
</div>
<br clear="all" />   	

<?php include "includes/footer.php" ?>

<!-- Additional scripts for real-time validation -->
<script type="text/javascript">
    jQuery(function($) {
        // Real-time validation
        $('#fc_name').on('keyup', function() {
            if ($(this).val().trim() === '') {
                $(this).closest('.formItem').removeClass('has-success').addClass('has-error');
            } else {
                $(this).closest('.formItem').removeClass('has-error').addClass('has-success');
            }
        });
    });
</script>

</body>
</html>

<?php ob_end_flush(); ?>