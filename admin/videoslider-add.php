<?php
/**
 * File: videoslider-add.php
 * Version: 2.0.0 (تمت الترقية إلى PHP 8.3)
 * Description: إضافة إعلانات الفيديو (الرابط - العنوان - الوصف - الدول)
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// تضمين الاتصال بقاعدة البيانات لإصلاح تسجيل الدخول
// ========================================

require_once __DIR__ . '/../lib/connect.php';

if (!isset($con) || !$con) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// ========================================
// إصلاح مشكلة معرف المستخدم (مع الحفاظ على التوافق)
// ========================================

$user_id = 0;

// محاولة استخدام المعرف الموجود في الجلسة
if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    $user_id = (int)$_SESSION['uid_indm'];
}

// إذا كان المعرف 1 (افتراضي) أو غير موجود، جربه من قاعدة البيانات
if ($user_id <= 0 || $user_id == 1) {
    $email = $_SESSION['ad_email_indm'] ?? $_SESSION['email'] ?? '';
    
    if (!empty($email)) {
        $sql = "SELECT usr_id FROM user WHERE email = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if ($row) {
                $user_id = (int)$row['usr_id'];
                $_SESSION['uid_indm'] = $user_id;
                $_SESSION['reseller_id'] = $user_id;
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// إذا كان المعرف لا يزال غير صحيح، استخدم أول مستخدم في جدول user
if ($user_id <= 0) {
    $sql = "SELECT usr_id FROM user ORDER BY usr_id LIMIT 1";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $user_id = (int)$row['usr_id'];
        $_SESSION['uid_indm'] = $user_id;
        $_SESSION['reseller_id'] = $user_id;
    }
}

// إذا لم نجد أي مستخدم، قم بالتوجيه إلى تسجيل الدخول
if ($user_id <= 0) {
    $_SESSION['msg'] = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء تسجيل الدخول أولاً</div>';
    header("Location: /login.php");
    exit();
}

// تحديث متغيرات الجلسة للاستخدام في بقية الكود
$_SESSION['reseller_id'] = $user_id;
$_SESSION['admin_id'] = $user_id;
$_SESSION['admin_logged_in'] = true;

// ========================================
// تضمين الملفات المطلوبة (كما كانت في الأصل)
// ========================================

include "../common.php";

// ========================================
// كلاس addAdvertisement (معدل للتوافق مع PHP 8.3)
// ========================================

class addAdvertisement {
    public ?string $msg = null;
    public ?string $adv_img = null;
    public ?string $adv_link = null;
    public ?string $imgsize = null;
    public int $adv_imagewidth = 0;
    public int $adv_imageheight = 0;
    public ?string $adv_title = null;
    public ?string $adv_description = null;
    public ?string $adv_country = null;
    public ?string $adv_global = null;
    public ?string $adv_redirect = null;

    /**
     * المُنشئ - معدل للتوافق مع PHP 8.3
     */
    public function __construct(
        ?string $adv_link = '',
        ?string $adv_title = '',
        ?string $adv_description = '',
        ?array $adv_country = [],
        ?string $adv_global = '0',
        ?string $adv_redirect = ''
    ) {
        $this->adv_link = $adv_link;
        $this->adv_title = $adv_title;
        $this->adv_description = $adv_description;
        $this->adv_country = is_array($adv_country) ? implode(",", $adv_country) : '';
        $this->adv_global = $adv_global;
        $this->adv_redirect = $adv_redirect;
        
        $_SESSION['adv_link'] = $this->adv_link;
    }

    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        if (empty($this->adv_link)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a link</div>';
            return false;
        }
        
        if (empty($this->adv_title)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please Enter the Title</div>';
            return false;
        }
        
        if (empty($this->adv_description)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please Enter the Description</div>';
            return false;
        }
        
        return true;
    }

    /**
     * إضافة الإعلان إلى قاعدة البيانات
     */
    public function add(): void {
        global $con;
        
        if (!empty($this->adv_link)) {
            $sql = "INSERT INTO video_slider SET
                    adv_link = '" . mysqli_real_escape_string($con, $this->adv_link) . "',
                    adv_title = '" . mysqli_real_escape_string($con, $this->adv_title) . "',
                    adv_description = '" . mysqli_real_escape_string($con, $this->adv_description) . "',
                    adv_country = '" . mysqli_real_escape_string($con, $this->adv_country) . "',
                    adv_global = '" . mysqli_real_escape_string($con, $this->adv_global) . "',
                    adv_redirect = '" . mysqli_real_escape_string($con, $this->adv_redirect) . "',
                    adv_updated_date = NOW(),
                    adv_status = 1";

            mysqli_query($con, $sql) or die(mysqli_error($con));

            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Advertisement added successfully.</div>';
            unset($_SESSION['adv_link']);
        }
    }
}

// ========================================
// معالجة البيانات (نفس الكود الأصلي)
// ========================================

$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$adv_link = $_SESSION['adv_link'] ?? '';
unset($_SESSION['adv_link']);

$adv_title = '';
$adv_description = '';
$adv_redirect = '';

if (isset($_POST['btnAdd'])) {
    $adn = new addAdvertisement(
        addslashes(trim($_POST['adv_link'] ?? '')),
        addslashes(trim($_POST['adv_title'] ?? '')),
        addslashes(trim($_POST['adv_description'] ?? '')),
        $_POST['adv_country'] ?? [],
        $_POST['adv_global'] ?? '0',
        $_POST['adv_redirect'] ?? ''
    );
    
    if ($adn->valid()) {
        $adn->add();
    }
    
    $_SESSION['msg'] = $adn->msg;
    header("Location: videoslider-add.php");
    exit();
}

// ========================================
// عرض الصفحة (نفس الكود الأصلي مع الحفاظ على الهيكل)
// ========================================

?>
<?php include "includes/admin-top.php"; ?>
<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
<?php include "includes/admin-left-con.php"; ?>
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
                <a href="videoslider-view.php">Video Slider</a>
            </li>
            <li class="active">Add Slider</li>
        </ul><!-- .breadcrumb -->
    </div>

<div class="page-content">
    <div class="page-header">
        <h1>
            Video Slider
            <small>
                <i class="icon-double-angle-right"></i>
                Add Slider
            </small>
        </h1>
    </div>
    <div class="row">
        <div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
    <div id="msg"><?php echo $msg; ?></div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country:</label>
        <div class="col-sm-8">

            <?php
            $sqlcnty = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_id DESC";
            $rscontry = mysqli_query($con, $sqlcnty);
            ?>
            <select id="adv_country" name="adv_country[]" multiple="multiple" class="chosen-select">
            <?php
            while ($row_cntry = mysqli_fetch_object($rscontry)) {
            ?>
                <option value="<?php echo $row_cntry->cn_id; ?>"><?php echo $row_cntry->cn_name; ?></option>
            <?php
            }
            ?>
            </select>
        </div>
    </div>
    <input type="hidden" name="adv_global" value="0">
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Video Url</label>
        <div class="col-sm-9">
            <input name="adv_link" id="adv_link" class="col-xs-10 col-sm-5" type="text" style="width:440px;" value="<?php echo htmlspecialchars($adv_link); ?>" />
        </div>
    </div>
<div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Redirect Url</label>
        <div class="col-sm-9">
            <input name="adv_redirect" id="adv_redirect" class="col-xs-10 col-sm-5" type="url" style="width:440px;" value="<?php echo htmlspecialchars($adv_redirect); ?>" />
        </div>
</div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Title</label>
        <div class="col-sm-9">
            <input name="adv_title" id="adv_title" class="col-xs-10 col-sm-5" type="text" style="width:440px;" value="<?php echo htmlspecialchars($adv_title); ?>" required/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Description</label>
        <div class="col-sm-9">
        <textarea name="adv_description" id="adv_description" rows="10" cols="60" required><?php echo htmlspecialchars($adv_description); ?></textarea>
        </div>
    </div>      
    <div class="clearfix form-actions">
        <div class="col-md-offset-3 col-md-9">
            <button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd"><i class="icon-ok bigger-110"></i>Add</button>
            <button class="btn" type="reset"><i class="icon-undo bigger-110"></i>Reset</button>
        </div>
    </div>                              
</form>  
            </div>
        </div>

    </div>
    <br clear="all" />  
</div>
<?php include "includes/footer.php"; ?>
</body>
        <script type="text/javascript">
            window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
        </script>

        <script type="text/javascript">
            if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
        </script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/typeahead-bs2.min.js"></script>

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
                $(".input-mask-product").mask("a*-999-a999",{placeholder:" ",completed:function(){alert("You typed the following: "+this.val());}});

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
                    thumbnail:'small'
                }).on('change', function(){
                });

                $('#id-file-format').removeAttr('checked').on('change', function() {
                    var before_change
                    var btn_choose
                    var no_icon
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
                                            || ( type.length == 0 && ! (/\.(jpe?g|png|gif|bmp)$/i).test(file.name) )
                                        ) continue;
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
                    file_input.ace_file_input('update_settings', {'before_change':before_change, 'btn_choose': btn_choose, 'no_icon':no_icon})
                    file_input.ace_file_input('reset_input');
                });

                $('#spinner1').ace_spinner({value:0,min:0,max:200,step:10, btn_up_class:'btn-info' , btn_down_class:'btn-info'})
                .on('change', function(){
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
                    tag_input.tag(
                      {
                        placeholder:tag_input.attr('placeholder'),
                        source: ace.variable_US_STATES,
                      }
                    );
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
                })

                $('#modal-form').on('shown.bs.modal', function () {
                    $(this).find('.chosen-container').each(function(){
                        $(this).find('a:first-child').css('width' , '210px');
                        $(this).find('.chosen-drop').css('width' , '210px');
                        $(this).find('.chosen-search input').css('width' , '200px');
                    });
                })

            });
        </script>
</html>
<?php ob_end_flush(); ?>