<?php
/**
 * File: admin/admin-contact-reply.php
 * Version: PHP 8.3
 * Description: صفحة الرد على استفسارات الاتصال في لوحة التحكم
 * 
 * تسمح هذه الصفحة للمشرف بالرد على استفسارات المستخدمين
 * وإرسال الرد عبر البريد الإلكتروني وحفظه في نظام الرسائل
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
require_once "../common.php";
require_once "../lib/pagination.php";


// التحقق من تسجيل دخول المستخدم
check_admin_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة الردود على استفسارات الاتصال
 */
class ContactUsDetails
{
    public $cu_id;
    public $reply_subject;
    public $reply_content;
    public $mem_name;
    public $msg;
    public $con;
    
    /**
     * Constructor
     * @param int $cu_id معرف الاستفسار
     */
    public function __construct($cu_id)
    {
        global $con;
        $this->con = $con;
        $this->cu_id = (int)$cu_id;
    }
    
    /**
     * جلب تفاصيل الاستفسار
     * @return object|null بيانات الاستفسار
     */
    public function detailsObj()
    {
        $sql = "SELECT * FROM contact_us WHERE cu_id = " . $this->cu_id;
        $res = mysqli_query($this->con, $sql);
        
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_object($res);
        }
        return null;
    }
    
    /**
     * التحقق من صحة بيانات الرد
     * @return bool true إذا كانت البيانات صحيحة
     */
    public function valid(): bool
    {
        $valid = true;
        
        if (empty($this->reply_subject)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> من فضلك أدخل موضوع</div>';
            $valid = false;
        } else if (empty($this->reply_content)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter message</div>';
            $valid = false;
        }
        
        return $valid;
    }
    
    /**
     * إدراج الرد وإرسال البريد الإلكتروني
     */
    public function insertMsg(): void
    {
        $obj = $this->detailsObj();
        
        if (!$obj) {
            $this->msg = '<div class="alert alert-danger">الاستفسار غير موجود</div>';
            return;
        }
        
        // تحديث حالة الرد
        $sql1 = "UPDATE contact_us SET replied = 1 WHERE cu_id = " . $this->cu_id;
        mysqli_query($this->con, $sql1);
        
        // معالجة الصور في المحتوى (إذا وجدت)
        $this->reply_content = $this->processImages($this->reply_content);
        
        // إدراج الرسالة في قاعدة البيانات
        $sql = "INSERT INTO message 
                SET 
                    msg_from = " . (int)getAdminUserId() . ",
                    msg_to = '" . mysqli_real_escape_string($this->con, $obj->cu_user_id) . "',
                    msg_subject = '" . mysqli_real_escape_string($this->con, $this->reply_subject) . "',
                    msg_message = '" . mysqli_real_escape_string($this->con, $this->reply_content) . "',
                    msg_entity = 'contact',
                    msg_entity_id = " . $this->cu_id . ",
                    msg_date = NOW()";
        
        $result = mysqli_query($this->con, $sql);
        
        if (!$result) {
            error_log("خطأ في إدراج الرسالة: " . mysqli_error($this->con));
            $this->msg = '<div class="alert alert-danger">فشل في إرسال الرد</div>';
            return;
        }
        
        $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Replied message sent successfully</div>';
        $this->mem_name = trim(($obj->cu_fname ?? '') . ' ' . ($obj->cu_lname ?? ''));
        
        // إرسال البريد الإلكتروني
        $this->sendEmail($obj);
    }
    
    /**
     * معالجة الصور في المحتوى (حفظ الصور من base64 إلى ملفات)
     * @param string $content المحتوى
     * @return string المحتوى بعد معالجة الصور
     */
    private function processImages(string $content): string
    {
        $doc = new DOMDocument();
        
        // تجاهل أخطاء تحليل HTML
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $content);
        libxml_clear_errors();
        
        $imageTags = $doc->getElementsByTagName('img');
        
        foreach ($imageTags as $tag) {
            $src = $tag->getAttribute('src');
            $dir = dirname(__FILE__) . '/../images/reply/';
            
            // التأكد من وجود المجلد
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $filename = '';
            
            if (strpos($src, 'data:image/png;base64,') === 0) {
                $src = str_replace('data:image/png;base64,', '', $src);
                $src = str_replace(' ', '+', $src);
                $data = base64_decode($src);
                $filename = uniqid() . '.png';
            } else if (strpos($src, 'data:image/jpeg;base64,') === 0 || strpos($src, 'data:image/jpg;base64,') === 0) {
                $src = str_replace('data:image/jpeg;base64,', '', $src);
                $src = str_replace('data:image/jpg;base64,', '', $src);
                $src = str_replace(' ', '+', $src);
                $data = base64_decode($src);
                $filename = uniqid() . '.jpg';
            }
            
            if (!empty($filename)) {
                $file = $dir . $filename;
                file_put_contents($file, $data);
                
                $new_src = 'http://egyptmart.shop/images/reply/' . $filename;
                $content = str_replace($src, $new_src, $content);
            }
        }
        
        return $content;
    }
    
    /**
     * إرسال البريد الإلكتروني للمستخدم
     * @param object $obj بيانات الاستفسار
     */
    private function sendEmail($obj): void
    {
        $to = $obj->cu_email ?? '';
        
        if (empty($to)) {
            error_log("البريد الإلكتروني للمستخدم غير موجود");
            return;
        }
        
        $subject = get_page_settings(4) . " رد أدمن المنصة على استفسارك";
        $from_name = get_page_settings(4);
        $from_email = get_adminemail();
        $is_contact = 1;
        
        // إنشاء تفاصيل الاستفسار
        $enq_details = '<p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Name: ' . htmlspecialchars(trim(($obj->cu_fname ?? '') . ' ' . ($obj->cu_lname ?? ''))) . '</p>
                        <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Contact Number: ' . htmlspecialchars($obj->cu_contactnumber ?? '') . '</p>
                        <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Email: ' . htmlspecialchars($obj->cu_email ?? '') . '</p>
                        <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Country/State: ' . htmlspecialchars(($obj->cu_country ?? '') . '-' . ($obj->cu_state ?? '')) . '</p>
                        <p style="color:#000;font-size: 14px; line-height:22px; margin-top:0px; margin-bottom:0px; font-family: HelveticaNeue, sans-serif;" align="left">
                        Comments: ' . htmlspecialchars($obj->cu_comments ?? '') . '</p>';
        
        // تضمين قالب البريد الإلكتروني
        $message1 = '';
        if (file_exists("email/admin-reply.php")) {
            include "email/admin-reply.php";
        } else {
            $message1 = "شكراً لتواصلك معنا. هذا رد على استفسارك:\n\n" . $this->reply_content;
        }
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $from_name <$from_email>\r\n";
        $headers .= "Reply-To: $from_email\r\n";
        
        if (!mail($to, $subject, $message1, $headers)) {
            error_log("فشل في إرسال البريد الإلكتروني إلى: $to");
        }
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// التحقق من وجود معرف الاستفسار
if (!isset($_GET['fid']) || empty($_GET['fid'])) {
    header("location: contact-view.php");
    exit();
}

$fid = (int)$_GET['fid'];

if ($fid <= 0) {
    header("location: contact-view.php");
    exit();
}

// إنشاء كائن الردود
$ob = new ContactUsDetails($fid);
$row = $ob->detailsObj();

if (!$row) {
    header("location: contact-view.php");
    exit();
}

// معالجة إرسال النموذج
if (isset($_POST['btnReplyBack'])) {
    header("location: contact-details.php?fid=" . $fid);
    exit();
} else if (isset($_POST['btnReply'])) {
    header("location: admin-contact-reply.php?fid=" . $fid);
    exit();
} else if (isset($_POST['btnReplySubmit'])) {
    $ob->reply_subject = trim($_POST['reply_subject'] ?? '');
    $ob->reply_content = trim($_POST['reply_content'] ?? '');
    
    if ($ob->valid()) {
        $ob->insertMsg();
    }
    
    $_SESSION['msg'] = $ob->msg;
    header("location: admin-contact-reply.php?fid=" . $fid);
    exit();
}

// متغير للرد (للاستخدام في النموذج)
$reply_content = isset($_POST['reply_content']) ? stripslashes($_POST['reply_content']) : '';
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
                        <a href="contact-view.php">Manage Contact Us</a>
                    </li>
                    <li class="active">تفاصيل الإتصال</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Contact Us
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Contact Details
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Name:</label>
                            <div class="col-sm-9">
                                <label style="padding-top:4px;">
                                    <?php echo htmlspecialchars(ucfirst($row->cu_fname ?? '') . ' ' . ucfirst($row->cu_lname ?? '')); ?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Email:</label>
                            <div class="col-sm-9">
                                <label style="padding-top:4px;">
                                    <a href="mailto:<?php echo htmlspecialchars($row->cu_email ?? ''); ?>">
                                        <?php echo htmlspecialchars($row->cu_email ?? ''); ?>
                                    </a>
                                </label>
                            </div>
                        </div>
                        
                        <?php if (!empty($row->cu_contactnumber)): ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Contact Number:</label>
                            <div class="col-sm-9">
                                <label style="padding-top:4px;"><?php echo htmlspecialchars($row->cu_contactnumber); ?></label>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($row->cu_country)): ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country/State:</label>
                            <div class="col-sm-9">
                                <label style="padding-top:4px;">
                                    <?php echo htmlspecialchars(($row->cu_country ?? '') . '-' . ($row->cu_state ?? '')); ?>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label no-padding-right" for="form-field-2">الرسالة</label>
                            <div class="col-sm-8">
                                <label style="padding-top:4px;"><?php echo nl2br(htmlspecialchars($row->cu_comments ?? '')); ?></label>
                            </div>
                        </div>
                        
                        <h2 class="col-xs-12">Reply to this Membership Request</h2>
                        
                        <form class="form-horizontal" name="mem_reply" id="mem_reply" method="post" enctype="multipart/form-data" onsubmit="return filling();">
                            <div id="msg" class="col-xs-12"><?php echo $msg; ?></div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Reply Subject:</label>
                                <div class="col-sm-9">
                                    <input name="reply_subject" id="reply_subject" class="form-control" type="text" 
                                           value="<?php echo isset($_POST['reply_subject']) ? htmlspecialchars($_POST['reply_subject']) : 'Reply from Admin for your Contact'; ?>"/>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">يمكنك الرد على الرساله من هنا</label>
                                <div class="col-sm-9">
                                    <textarea name="reply_content" id="reply_content" cols="50" rows="10" class="form-control"><?php echo htmlspecialchars($reply_content); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnReplySubmit" id="btnReplySubmit">
                                        <i class="icon-ok icon-only"></i>&nbsp;Submit
                                    </button>
                                    <button class="btn btn-info" type="submit" name="btnReplyBack" id="btnReplyBack">
                                        <i class="icon-reply icon-only"></i>&nbsp;Back
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <br clear="all" />
        </div>
        
        <?php include "includes/footer.php" ?>
    </div>
</div>

<!-- JavaScript files -->
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

<!-- editor scripts -->
<script src="assets/js/markdown/markdown.min.js"></script>
<script src="assets/js/markdown/bootstrap-markdown.min.js"></script>
<script src="assets/js/jquery.hotkeys.min.js"></script>
<script src="assets/js/bootstrap-wysiwyg.min.js"></script>
<script src="assets/js/bootbox.min.js"></script>
<script src="ckeditor/ckeditor.js"></script>

<script type="text/javascript">
function filling() {
    return true;
}

jQuery(function($){

    function showErrorAlert(reason, detail) {
        var msg = '';
        if (reason === 'unsupported-file-type') { 
            msg = "Unsupported format " + detail; 
        } else {
            console.log("error uploading file", reason, detail);
        }
        $('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'+
          '<strong>File upload error</strong> '+msg+' </div>').prependTo('#alerts');
    }

    // Initialize CKEditor
    CKEDITOR.replace('reply_content', {
        extraPlugins: 'imageuploader'
    });

    $('#editor2').css({'height':'200px'}).ace_wysiwyg({
        toolbar_place: function(toolbar) {
            return $(this).closest('.widget-box').find('.widget-header').prepend(toolbar).children(0).addClass('inline');
        },
        toolbar: [
            'bold',
            {name:'italic' , title:'Change Title!', icon: 'icon-leaf'},
            'strikethrough',
            null,
            'insertunorderedlist',
            'insertorderedlist',
            null,
            'justifyleft',
            'justifycenter',
            'justifyright'
        ],
        speech_button: false
    });

    $('[data-toggle="buttons"] .btn').on('click', function(e){
        var target = $(this).find('input[type=radio]');
        var which = parseInt(target.val());
        var toolbar = $('#editor1').prev().get(0);
        if(which == 1 || which == 2 || which == 3) {
            toolbar.className = toolbar.className.replace(/wysiwyg\-style(1|2)/g , '');
            if(which == 1) $(toolbar).addClass('wysiwyg-style1');
            else if(which == 2) $(toolbar).addClass('wysiwyg-style2');
        }
    });

    // Add Image Resize Functionality to Chrome and Safari
    if (typeof jQuery.ui !== 'undefined' && /applewebkit/.test(navigator.userAgent.toLowerCase())) {

        var lastResizableImg = null;
        function destroyResizable() {
            if(lastResizableImg == null) return;
            lastResizableImg.resizable( "destroy" );
            lastResizableImg.removeData('resizable');
            lastResizableImg = null;
        }

        var enableImageResize = function() {
            $('.wysiwyg-editor')
            .on('mousedown', function(e) {
                var target = $(e.target);
                if( e.target instanceof HTMLImageElement ) {
                    if( !target.data('resizable') ) {
                        target.resizable({
                            aspectRatio: e.target.width / e.target.height,
                        });
                        target.data('resizable', true);

                        if( lastResizableImg != null ) {
                            lastResizableImg.resizable( "destroy" );
                            lastResizableImg.removeData('resizable');
                        }
                        lastResizableImg = target;
                    }
                }
            })
            .on('click', function(e) {
                if( lastResizableImg != null && !(e.target instanceof HTMLImageElement) ) {
                    destroyResizable();
                }
            })
            .on('keydown', function() {
                destroyResizable();
            });
        };

        enableImageResize();
    }
});

// Additional Ace Admin scripts
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

</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>