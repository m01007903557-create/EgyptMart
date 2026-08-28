<?php
/**
 * File: service_page_edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل محتوى صفحة الخدمات
 * Edit service page content
 * 
 * Features:
 * - تعديل عنوان صفحة الخدمات
 * - محرر نصوص متقدم TinyMCE
 * - رابط سريع لقائمة الخدمات
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
 * Class ServicePageEditor
 * 
 * Handles service page content editing operations
 */
class ServicePageEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Content ID */
    private int $spc_id;
    
    /** @var string Page heading */
    private string $spc_heading = '';
    
    /**
     * Constructor
     * 
     * @param int $spc_id Content ID
     */
    public function __construct(int $spc_id) {
        $this->spc_id = $spc_id;
    }
    
    /**
     * Get page content details
     * 
     * @return object|null Page content details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM servicepage_content WHERE spc_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->spc_id);
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
        
        // Validate heading
        if (empty($this->spc_heading)) {
            $this->msg = '<font color="#CC0000">Please enter Heading</font>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Update page content
     */
    public function update(): void {
        global $con;
        
        $sql = "UPDATE servicepage_content SET
                spc_heading = ?,
                spc_updated_date = NOW()
                WHERE spc_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">Database error</font>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $this->spc_heading, $this->spc_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<font color="#009900">Record updated successfully</font>';
        } else {
            $this->msg = '<font color="#CC0000">Failed to update record</font>';
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

// Handle session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get content ID
$contentId = isset($_GET['sid']) ? (int)$_GET['sid'] : 0;
if ($contentId === 0) {
    header("Location: service_page_view.php");
    exit;
}

// Initialize editor
$editor = new ServicePageEditor($contentId);
$row = $editor->getDetails();

if (!$row) {
    header("Location: service_page_view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editor->spc_heading = trim($_POST['spc_heading'] ?? '');
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: service_page_edit.php?sid=" . $contentId);
    exit;
}
?>

<?php include "includes/admin-top.php" ?>

<!-- jQuery -->
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- Styles -->
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
        // General options
        mode : "textareas",
        theme : "advanced",
        
        plugins : "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Content CSS
        content_css : "css/content.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "lists/template_list.js",
        external_link_list_url : "lists/link_list.js",
        external_image_list_url : "lists/image_list.js",
        media_external_list_url : "lists/media_list.js",

        // Style formats
        style_formats : [
            {title : 'Bold text', inline : 'b'},
            {title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
            {title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
            {title : 'Example 1', inline : 'span', classes : 'example1'},
            {title : 'Example 2', inline : 'span', classes : 'example2'},
            {title : 'Table styles'},
            {title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
        ],         
        forced_root_block : false,
        force_p_newlines : false,
        remove_linebreaks : false,
        force_br_newlines : true,
        remove_trailing_nbsp : false,
        verify_html : false           
    });
</script>

<!-- Custom Styles -->
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
    .x2-button {
        background: #2c3e50;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 14px;
    }
    .x2-button:hover {
        background: #1a2632;
    }
    .x2-button.back-btn {
        background: #95a5a6;
    }
    .x2-button.back-btn:hover {
        background: #7f8c8d;
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
    .quick-link {
        margin-top: 20px;
        padding: 10px;
        background: #f8f9fa;
        border: 1px solid #e0e5ec;
        border-radius: 3px;
    }
    .quick-link a {
        color: #2c3e50;
        text-decoration: none;
        font-weight: bold;
    }
    .quick-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&nbsp;Manage Webpage&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Service Page Edit</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                
                <em style="display:block;margin:5px;">Fields with <span class="required">*</span> are required.</em>
                
                <div class="row buttons">
                    <input type="button" onclick="location.href='service_page_view.php'" 
                           value="Back to List" class="x2-button back-btn" style="margin-right:10px;margin-top:5px;">
                </div>
                
                <div id="message"><?php echo $msg; ?></div>
                <br />

                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            
                                            <!-- Heading with TinyMCE -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Heading: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:387px;">
                                                    <textarea name="spc_heading" id="spc_heading" class="reg_txtfld" style="height:300px"><?php echo htmlspecialchars($row->spc_heading ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                    <p class="help-text">Use the editor to format your heading text</p>
                                                </div>
                                            </div>
                                            
                                            <!-- Quick Link to Services List -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;"></label>
                                                <div class="formInputBox" style="width:387px;">
                                                    <div class="quick-link">
                                                        <a href="service_list.php">View & Upload Content List</a>
                                                        <p style="margin:5px 0 0 0; font-size:11px; color:#777;">
                                                            Manage individual service items and upload content
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row buttons" style="margin-top:20px;">
                    <input type="submit" name="btnUpdate" id="btnUpdate" value="Update" class="x2-button" style="margin-right:10px;">
                    <input type="reset" value="Reset" class="x2-button back-btn">
                </div>
            </form>    
            <br clear="all"/>
        </div>
    </div>
</div>
<br clear="all" />   	

<?php include "includes/footer.php" ?>

</body>
</html>

<?php ob_end_flush(); ?>