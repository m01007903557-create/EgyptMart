<?php
/**
 * File: admin/category-edit.php
 * Version: PHP 8.3
 * Description: تعديل تصنيف في لوحة التحكم
 * 
 * تسمح هذه الصفحة للمشرف بتعديل التصنيفات (الفئات) الموجودة
 * مع إمكانية تغيير الاسم والاسم المختصر والفئة الرئيسية
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
 * كلاس تعديل التصنيف
 */
class editproduct
{
    public $msg;
    public $scat_id;
    public $scat_name;
    public $scat_cat_id;
    public $scat_sort_name;
    public $con;
    
    /**
     * Constructor
     * @param string $scat_id معرف التصنيف (MD5)
     */
    public function __construct($scat_id)
    {
        global $con;
        $this->con = $con;
        $this->scat_id = $scat_id;
    }
    
    /**
     * جلب تفاصيل التصنيف
     * @return object|null بيانات التصنيف
     */
    public function detailsObj()
    {
        $sql = "SELECT * FROM product_category WHERE MD5(pc_id) = '" . mysqli_real_escape_string($this->con, $this->scat_id) . "'";
        $res = mysqli_query($this->con, $sql);
        
        if ($res && mysqli_num_rows($res) > 0) {
            return mysqli_fetch_object($res);
        }
        return null;
    }
    
    /**
     * التحقق من صحة البيانات
     * @return bool true إذا كانت البيانات صحيحة
     */
    public function valid(): bool
    {
        $valid = true;
        
        if (empty($this->scat_cat_id)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose Main Category.</div>';
            $valid = false;
        } else if (empty($this->scat_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Category name.</div>';
            $valid = false;
        } else if (empty($this->scat_sort_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Category sort name.</div>';
            $valid = false;
        } else if (strlen($this->scat_sort_name) > 40) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Category sort name within 40 characters.</div>';
            $valid = false;
        }
        
        return $valid;
    }
    
    /**
     * تحديث التصنيف
     */
    public function update(): void
    {
        $sql = "UPDATE product_category 
                SET
                    pc_name = '" . mysqli_real_escape_string($this->con, $this->scat_name) . "',
                    pc_sort_name = '" . mysqli_real_escape_string($this->con, $this->scat_sort_name) . "',
                    pc_parent_id = '" . mysqli_real_escape_string($this->con, $this->scat_cat_id) . "'
                WHERE MD5(pc_id) = '" . mysqli_real_escape_string($this->con, $this->scat_id) . "'";
        
        $result = mysqli_query($this->con, $sql);
        
        if (!$result) {
            error_log("خطأ في تحديث التصنيف: " . mysqli_error($this->con));
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error: ' . mysqli_error($this->con) . '</div>';
            return;
        }
        
        $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Category updated successfully.</div>';
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// التحقق من وجود التوكن
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("location: category-view.php");
    exit();
}

$token = substr(trim($_GET['token']), 4);

if (empty($token)) {
    header("location: category-view.php");
    exit();
}

// إنشاء كائن التعديل
$ob = new editproduct($token);
$row = $ob->detailsObj();

if (!$row) {
    header("location: category-view.php");
    exit();
}

// معالجة إرسال النموذج
if (isset($_POST['btnUpdate'])) {
    $ob->scat_name = trim($_POST['scat_name'] ?? '');
    $ob->scat_sort_name = trim($_POST['scat_sort_name'] ?? '');
    $ob->scat_cat_id = trim($_POST['scat_cat_id'] ?? '');
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    $_SESSION['msg'] = $ob->msg;
    header("location: category-edit.php?token=" . $_GET['token']);
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
        /**
         * التحقق من صحة النموذج قبل الإرسال
         * @return {boolean} true إذا كانت البيانات صحيحة
         */
        function myvalid() {
            var scat_name = document.getElementById('scat_name');
            var scat_sort_name = document.getElementById('scat_sort_name');
            var scat_cat_id = document.getElementById('scat_cat_id');
            var message = "";
            var valid = true;
            
            if (scat_cat_id.value == '') {
                message = 'Please choose Main Category';
                scat_cat_id.focus();
                valid = false;
            } else if (scat_name.value.trim() == '') {
                message = 'Please enter Category name';
                scat_name.focus();
                valid = false;
            } else if (scat_sort_name.value.trim() == '') {
                message = 'Please enter Category Sort name';
                scat_sort_name.focus();
                valid = false;
            } else if (scat_sort_name.value.length > 40) {
                message = 'Please enter Category Sort name within 40 characters';
                scat_sort_name.focus();
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
                        <a href="category-view.php">Manage Category</a>
                    </li>
                    <li class="active">Category Edit</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Category
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Category Edit
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
                            <em style="display:block; margin:5px;">
                                Fields with <span style="color:#F00">*</span> are required.
                            </em>

                            <div id="msg" class="<?php echo strpos($msg, 'success') ? 'alert alert-success' : 'alert alert-danger'; ?>">
                                <?php echo $msg; ?>
                            </div>

                            <!-- Main Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">
                                    Main Category <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="scat_cat_id" id="scat_cat_id" class="chosen-select form-control" style="width:auto;">
                                        <option value="">-- Select Main Category --</option>
                                        <?php 
                                        $catsql = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id = '0' AND pc_status = '1'");
                                        while ($catrow = mysqli_fetch_object($catsql)) {
                                            $selected = ($catrow->pc_id == ($row->pc_parent_id ?? 0)) ? 'selected="selected"' : '';
                                            echo '<option value="' . (int)$catrow->pc_id . '" ' . $selected . '>' . 
                                                 htmlspecialchars($catrow->pc_name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Category Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">
                                    Category Name <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="scat_name" id="scat_name" class="col-xs-10 col-sm-5 form-control" 
                                           type="text" style="width:300px;" 
                                           value="<?php echo htmlspecialchars($row->pc_name ?? ''); ?>" />
                                </div>
                            </div>

                            <!-- Sort Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">
                                    Sort Name <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="scat_sort_name" id="scat_sort_name" class="col-xs-10 col-sm-5 form-control" 
                                           type="text" style="width:300px;" 
                                           value="<?php echo htmlspecialchars($row->pc_sort_name ?? ''); ?>" />
                                    <span class="help-block">Maximum 40 characters</span>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i> Reset
                                    </button>
                                    <button class="btn btn-danger" type="button" onclick="window.location.href='category-view.php'">
                                        <i class="icon-reply bigger-110"></i> Back to List
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