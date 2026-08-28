<?php
/**
 * File: software_feature_edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل الميزات البرمجية مع إمكانية إدارة الصور
 * Edit software features with image management capability
 * 
 * Features:
 * - تعديل الميزة الرئيسية أو الفرعية
 * - محرر نصوص متقدم TinyMCE
 * - إدارة الصور (رفع/حذف)
 * - معاينة الصور المرفوعة
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
 * Class SoftwareFeatureEditor
 * 
 * Handles software feature editing operations
 */
class SoftwareFeatureEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Feature ID (MD5) */
    private string $f_id;
    
    /** @var string Feature heading */
    private string $f_heading = '';
    
    /** @var string Feature content */
    private string $f_content = '';
    
    /** @var string Main feature flag (1 or 0) */
    private string $f_main_feature = '';
    
    /** @var string Has images flag (1 or 0) */
    private string $f_image = '';
    
    /**
     * Constructor
     * 
     * @param string $f_id MD5 hashed feature ID
     */
    public function __construct(string $f_id) {
        $this->f_id = $f_id;
    }
    
    /**
     * Get feature details
     * 
     * @return object|null Feature details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM features WHERE md5(f_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->f_id);
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
        return true; // Validation can be extended as needed
    }
    
    /**
     * Update feature
     */
    public function update(): void {
        global $con;
        
        // Convert image flag
        $fimage = ($this->f_image == '1') ? '1' : '0';
        
        $sql = "UPDATE features SET
                f_heading = ?,
                f_content = ?,
                f_main_feature = ?,
                f_image = ?,
                f_updated_date = NOW()
                WHERE md5(f_id) = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">Database error</font>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $this->f_heading,
            $this->f_content,
            $this->f_main_feature,
            $fimage,
            $this->f_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<font color="#009900">Feature updated successfully</font>';
        } else {
            $this->msg = '<font color="#CC0000">Failed to update feature</font>';
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

// Get token
$fullToken = $_GET['token'] ?? '';
if (empty($fullToken)) {
    header("Location: software_feature_list.php");
    exit;
}

$token = substr($fullToken, 4); // Remove first 4 characters (random number)

// Initialize editor
$editor = new SoftwareFeatureEditor($token);
$row = $editor->getDetails();

if (!$row) {
    header("Location: software_feature_list.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnAdd'])) {
    $editor->f_main_feature = trim($_POST['f_main_feature'] ?? '0');
    $editor->f_heading = trim($_POST['f_heading'] ?? '');
    $editor->f_content = trim($_POST['f_content'] ?? '');
    $editor->f_image = trim($_POST['f_image'] ?? '0');
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: software_feature_edit.php?token=" . $fullToken);
    exit;
}
?>

<?php include "includes/admin-top.php" ?>

<!-- jQuery -->
<script src="../js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- Uploadifive -->
<script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
        mode : "textareas",
        theme : "advanced",
        
        plugins : "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",

        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        content_css : "css/content.css",

        template_external_list_url : "lists/template_list.js",
        external_link_list_url : "lists/link_list.js",
        external_image_list_url : "lists/image_list.js",
        media_external_list_url : "lists/media_list.js",

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

<script>
function showUploader() {
    if ($('#f_image').is(':checked')) {
        $("#uploadImageDiv").css("display", "block");
    } else {
        $("#uploadImageDiv").css("display", "none");
    }
}

function mylist_file() {
    $.get("list_temp_photo.php", {'pid' : <?php echo (int)$row->f_id; ?>}, function(data) {
        $('#list_photo').html(data);
    });
}

function DelTempImage_rc(pi) {
    $.get("del_temp_photo.php", {'pi' : pi}, function(data) {
        mylist_file();
    });
}

jQuery(function() {
    jQuery('#file_upload').uploadifive({
        'auto'     : true,
        'formData' : {'pid' : <?php echo (int)$row->f_id; ?>},
        'queueID'  : 'queue',
        'debug'    : false,
        'method'   : 'post',
        'uploadScript' : 'upload-image.php',
        'buttonClass'     : 'butt',
        'buttonText'      : 'Upload Images',
        'onUploadComplete' : function(file, data) {
            mylist_file();
        }
    });
});
</script>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&rsaquo;&nbsp;&nbsp;Manage Software Features&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Edit Feature</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                
                <em style="display:block;margin:5px;">Fields with <span class="required">*</span> are required.</em>
                
                <input type="button" class="delete-btn" onClick="window.location ='software_feature_list.php'" value="Feature List">
                
                <div id="message"><?php echo $msg; ?></div>
                <br />

                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            
                                            <!-- Main Feature Radio -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">This is our Main Feature:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input type="radio" name="f_main_feature" value="1" <?php echo ((int)($row->f_main_feature ?? 0) == 1) ? 'checked' : ''; ?>>&nbsp;Yes&nbsp;&nbsp;
                                                    <input type="radio" value="0" name="f_main_feature" <?php echo ((int)($row->f_main_feature ?? 0) == 0) ? 'checked' : ''; ?>>&nbsp;No
                                                </div>
                                            </div>
                                            
                                            <!-- Heading with TinyMCE -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Heading: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <textarea name="f_heading" id="f_heading" class="reg_txtfld" style="height:300px"><?php echo htmlspecialchars($row->f_heading ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <!-- Content with TinyMCE -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Content: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <textarea name="f_content" id="f_content" class="reg_txtfld" style="height:300px"><?php echo htmlspecialchars($row->f_content ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <!-- Has Images Checkbox -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">This Feature has Images:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input type="checkbox" name="f_image" id="f_image" onclick="showUploader();" value="1" <?php echo ((int)($row->f_image ?? 0) == 1) ? 'checked' : ''; ?>/>
                                                    <span class="help-text">Check if you want to manage images for this feature</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Image Upload Section -->
                                            <div id="uploadImageDiv" style="<?php echo ((int)($row->f_image ?? 0) == 1) ? 'display:block' : 'display:none'; ?>">
                                                
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;">Upload Images:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <input type="file" name="file_upload" multiple="multiple" id="file_upload" style="cursor:pointer"/>
                                                        <p class="help-text">You can upload multiple images</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;"></label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <div id="queue">
                                                            <div align="left" id="list_photo" class="line clearfix">
                                                                <script>mylist_file();</script>
                                                            </div>
                                                        </div>
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
                
                <div class="row buttons">
                    <input type="submit" name="btnAdd" id="btnAdd" value="Update Feature" class="x2-button" style="margin-right:10px;margin-top:5px;">
                </div>
            </form>    
            <br clear="all"/>
        </div>
    </div>
</div>
<br clear="all" />   	

<?php include "includes/footer.php" ?>

<style>
    .required {
        color: #F00;
    }
    .help-text {
        font-size: 11px;
        color: #777;
        margin: 3px 0 0 0;
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
    #list_photo img {
        max-width: 100px;
        max-height: 100px;
        margin: 5px;
        border: 1px solid #ddd;
        padding: 3px;
        border-radius: 3px;
    }
    .butt {
        background: #5cb85c;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        margin: 5px 0;
    }
    .butt:hover {
        background: #4cae4c;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>