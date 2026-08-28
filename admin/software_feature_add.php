<?php
/**
 * File: software_feature_add.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة ميزة برمجية جديدة مع إمكانية رفع صور متعددة
 * Add new software feature with multiple image upload capability
 * 
 * Features:
 * - إضافة ميزة رئيسية أو فرعية
 * - محرر نصوص متقدم TinyMCE
 * - رفع صور متعددة للميزة
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

// Generate random number for image uploads
$randNo = rand(10000, 55555);

/**
 * Class SoftwareFeatureAdder
 * 
 * Handles software feature addition operations
 */
class SoftwareFeatureAdder {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Feature heading */
    private string $f_heading = '';
    
    /** @var string Feature content */
    private string $f_content = '';
    
    /** @var string Main feature flag (1 or 0) */
    private string $f_main_feature = '';
    
    /** @var string Has images flag (1 or 0) */
    private string $f_image = '';
    
    /** @var int Random number for image association */
    private int $randNo;
    
    /**
     * Constructor
     * 
     * @param string $f_heading Feature heading
     * @param string $f_content Feature content
     * @param string $f_main_feature Main feature flag
     * @param string $f_image Has images flag
     * @param int $randNo Random number
     */
    public function __construct(
        string $f_heading,
        string $f_content,
        string $f_main_feature,
        string $f_image,
        int $randNo
    ) {
        $this->f_heading = $f_heading;
        $this->f_content = $f_content;
        $this->f_main_feature = $f_main_feature;
        $this->f_image = $f_image;
        $this->randNo = $randNo;
        
        // Store in session for form persistence
        $_SESSION['f_heading'] = $this->f_heading;
        $_SESSION['f_content'] = $this->f_content;
        $_SESSION['f_main_feature'] = $this->f_main_feature;
        $_SESSION['f_image'] = $this->f_image;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        return true; // Validation will be implemented as needed
    }
    
    /**
     * Add new software feature
     */
    public function add(): void {
        global $con;
        
        // Convert image flag
        $fimage = ($this->f_image == '1') ? '1' : '0';
        
        // Insert feature
        $sql = "INSERT INTO features SET
                f_heading = ?,
                f_content = ?,
                f_main_feature = ?,
                f_image = ?,
                f_updated_date = NOW()";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">Database error</font>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $this->f_heading,
            $this->f_content,
            $this->f_main_feature,
            $fimage
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $lastId = mysqli_insert_id($con);
            mysqli_stmt_close($stmt);
            
            // Update associated images if any
            if ($this->f_image == '1') {
                $this->associateImages($lastId);
            }
            
            // Clear session variables
            unset($_SESSION['f_main_feature']);
            unset($_SESSION['f_heading']);
            unset($_SESSION['f_content']);
            unset($_SESSION['f_image']);
            
            $this->msg = '<font color="#009900">Feature added successfully</font>';
        } else {
            $this->msg = '<font color="#CC0000">Failed to add feature</font>';
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Associate temporary images with the new feature
     * 
     * @param int $featureId Feature ID
     */
    private function associateImages(int $featureId): void {
        global $con;
        
        $sql = "UPDATE feature_images SET fi_f_id = ? WHERE fi_f_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $featureId, $this->randNo);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
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

$f_main_feature = $_SESSION['f_main_feature'] ?? '';
$f_heading = $_SESSION['f_heading'] ?? '';
$f_content = $_SESSION['f_content'] ?? '';
$f_image = $_SESSION['f_image'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd'])) {
    
    $featureAdder = new SoftwareFeatureAdder(
        trim($_POST['f_heading'] ?? ''),
        trim($_POST['f_content'] ?? ''),
        trim($_POST['f_main_feature'] ?? '0'),
        trim($_POST['f_image'] ?? '0'),
        (int)($_POST['randNo'] ?? $randNo)
    );
    
    if ($featureAdder->validate()) {
        $featureAdder->add();
    }
    
    $_SESSION['msg'] = $featureAdder->msg;
    header("Location: software_feature_add.php");
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
    $.get("list_temp_photo.php", {'pid' : <?php echo $randNo; ?>}, function(data) {
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
        'formData' : {'pid' : <?php echo $randNo; ?>},
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
            <h2>&rsaquo;&nbsp;&nbsp;Manage Software Features&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Add Feature</h2>
            
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
                                                    <input type="radio" name="f_main_feature" value="1" <?php echo ($f_main_feature == '1') ? 'checked' : ''; ?>>&nbsp;Yes&nbsp;&nbsp;
                                                    <input type="radio" value="0" name="f_main_feature" <?php echo ($f_main_feature != '1') ? 'checked' : ''; ?>>&nbsp;No
                                                </div>
                                            </div>
                                            
                                            <!-- Heading with TinyMCE -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Heading: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <textarea name="f_heading" id="f_heading" class="reg_txtfld" style="height:300px"><?php echo htmlspecialchars($f_heading, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <!-- Content with TinyMCE -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Content: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <textarea name="f_content" id="f_content" class="reg_txtfld" style="height:300px"><?php echo htmlspecialchars($f_content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <!-- Has Images Checkbox -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">This Feature has Images:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input type="checkbox" name="f_image" id="f_image" onclick="showUploader();" value="1" <?php echo ($f_image == '1') ? 'checked' : ''; ?>/>
                                                    <span class="help-text">Check if you want to upload images for this feature</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Image Upload Section -->
                                            <div id="uploadImageDiv" style="<?php echo ($f_image == '1') ? 'display:block' : 'display:none'; ?>">
                                                
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;">Upload Images:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <input type="file" name="file_upload" multiple="multiple" id="file_upload" style="cursor:pointer"/>
                                                        <p class="help-text">You can upload multiple images</p>
                                                    </div>
                                                </div>
                                                
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;"></label>
                                                    <input type="hidden" name="randNo" id="randNo" value="<?php echo $randNo; ?>"/>
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
                    <input type="submit" name="btnAdd" id="btnAdd" value="Add Feature" class="x2-button" style="margin-right:10px;margin-top:5px;">
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