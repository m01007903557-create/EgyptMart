<?php
/**
 * File: admin/change-pass.php

 * Version: PHP 8.3
 * Description: تغيير كلمة مرور المشرف في لوحة التحكم
 * 
 * تسمح هذه الصفحة للمشرف بتغيير كلمة مروره مع التحقق من صحة
 * كلمة المرور الحالية وتطابق الجديدة مع التأكيد
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
check_admin_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس تغيير كلمة المرور للمشرف
 */
class changepassword
{
    public $current_pass = "";
    public $new_pass = "";
    public $con_password = "";
    public $msg = "";
    public $con;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $con;
        $this->con = $con;
        $this->current_pass = $_POST['current_pass'] ?? '';
        $this->new_pass = $_POST['new_pass'] ?? '';
        $this->con_password = $_POST['con_password'] ?? '';
    }
    
    /**
     * التحقق من صحة البيانات
     * @return bool true إذا كانت البيانات صحيحة
     */
    public function validpass(): bool
    {
        $valid = false;
        
        // التحقق من وجود كلمة المرور الحالية
        if (empty($this->current_pass)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> من فضلك أدخل كلمة المرور الحالية</div>';
            $valid = false;
        }
        // التحقق من صحة كلمة المرور الحالية
        else {
            $current_pass_md5 = md5($this->current_pass);
            $sql = "SELECT * FROM admin_user 
                    WHERE username = '" . mysqli_real_escape_string($this->con, $_SESSION['ad_username_indm'] ?? '') . "' 
                    AND password = '" . mysqli_real_escape_string($this->con, $current_pass_md5) . "'";
            $res = mysqli_query($this->con, $sql);
            
            if (!$res || mysqli_num_rows($res) == 0) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> كلمة المرور الحالية غير صحيحة</div>';
                $valid = false;
            }
            // التحقق من وجود كلمة المرور الجديدة
            else if (empty($this->new_pass)) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> من فضلك أدخل كلمة مرور جديدة</div>';
                $valid = false;
            }
            // التحقق من وجود تأكيد كلمة المرور
            else if (empty($this->con_password)) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> من فضلك أكد دخول كلمة المرور الجديدة مرة أخرى</div>';
                $valid = false;
            }
            // التحقق من تطابق كلمة المرور الجديدة مع التأكيد
            else if ($this->new_pass !== $this->con_password) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> كلمة المرور الجديدة وتأكيدها لا يتطابقان</div>';
                $valid = false;
            }
            // التحقق من قوة كلمة المرور (اختياري)
            else if (strlen($this->new_pass) < 6) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> كلمة المرور يجب أن تكون على الأقل 6 أحرف</div>';
                $valid = false;
            }
            else {
                $valid = true;
            }
        }
        
        return $valid;
    }
    
    /**
     * تحديث كلمة المرور في قاعدة البيانات
     */
    public function updatepass(): void
    {
        if ($this->new_pass === $this->con_password && isset($_SESSION['ad_username_indm'])) {
            $new_pass_md5 = md5($this->new_pass);
            $username = mysqli_real_escape_string($this->con, $_SESSION['ad_username_indm']);
            
            $sql = "UPDATE admin_user 
                    SET password = '" . mysqli_real_escape_string($this->con, $new_pass_md5) . "' 
                    WHERE username = '" . $username . "'";
            
            $res = mysqli_query($this->con, $sql);
            
            if (!$res) {
                error_log("خطأ في تحديث كلمة المرور: " . mysqli_error($this->con));
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> حدث خطأ في تحديث كلمة المرور</div>';
            } else {
                $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> تم تغيير كلمة المرور بنجاح</div>';
            }
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> كلمة المرور الجديدة وتأكيدها لا يتطابقان</div>';
        }
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// معالجة إرسال النموذج
if (isset($_POST['btnSubmit'])) {
    $cp = new changepassword();
    
    if ($cp->validpass()) {
        $cp->updatepass();
    }
    
    $_SESSION['msg'] = $cp->msg;
    header("location: " . $_SERVER['PHP_SELF']);
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
        
        <?php include "includes/admin-left-con.php" ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">الرئيسية</a>
                    </li>
                    <li>
                        <a href="#">إدارة الأدمن</a>
                    </li>
                    <li class="active">تغيير كلمة المرور</li>
                </ul><!-- .breadcrumb -->
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Admin Management
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Change Password
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <form class="form-horizontal" action="" method="post">
                            
                            <div id="msg" class="<?php echo strpos($msg, 'success') ? 'alert alert-success' : 'alert alert-danger'; ?>">
                                <?php echo $msg; ?>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">
                                    كلمة المرور الحالية <span style="color:#F00;">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="current_pass" id="current_pass" class="col-xs-10 col-sm-5 form-control" type="password" style="width:300px;" required />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">
                                    كلمة المرور الجديدة <span style="color:#F00;">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="new_pass" id="new_pass" class="col-xs-10 col-sm-5 form-control" type="password" style="width:300px;" required />
                                    <span class="help-block">يجب أن تكون على الأقل 6 أحرف</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">
                                    تأكيد كلمة المرور <span style="color:#F00;">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="con_password" id="con_password" class="col-xs-10 col-sm-5 form-control" type="password" style="width:300px;" required />
                                </div>
                            </div>
                            
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnSubmit">
                                        <i class="icon-ok bigger-110"></i>تغيير كلمة المرور
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i>إعادة تعيين
                                    </button>
                                    <button class="btn btn-danger" type="button" onclick="window.location='welcome.php'">
                                        <i class="icon-reply icon-only"></i>&nbsp;رجوع
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