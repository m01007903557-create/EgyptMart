<?php
/**
 * File: admin/advhome-edit.php
 * Version: PHP 8.3
 * Description: تعديل إعلان الصفحة الرئيسية
 * 
 * تسمح هذه الصفحة للمشرف بتعديل إعلان الصفحة الرئيسية الموجود،
 * مع إمكانية تغيير البلدان والرابط والصورة والموضع
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
require_once "../common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس تعديل إعلان الصفحة الرئيسية
 */
class editAdvertisement
{
    public $msg;
    public $adv_id;
    public $adv_img;
    public $adv_link;
    public $adv_imagewidth;
    public $adv_imageheight;
    public $adv_country;
    public $adv_position;
    public $con;
    
    /**
     * Constructor
     * @param int $adv_id معرف الإعلان
     */
    public function __construct($adv_id)
    {
        global $con;
        $this->con = $con;
        $this->adv_id = (int)$adv_id;
    }
    
    /**
     * جلب تفاصيل الإعلان
     * @return object|null بيانات الإعلان
     */
    public function detailsObj()
    {
        $sql = "SELECT * FROM advertisementhome WHERE adv_id = " . $this->adv_id;
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
        // يمكن إضافة تحققات إضافية هنا إذا لزم الأمر
        return $valid;
    }
    
    /**
     * تحديث الإعلان في قاعدة البيانات
     */
    public function update(): void
    {
        // التحقق من رفع صورة جديدة
        if (!empty($_FILES["adv_img"]["name"])) {
            $this->updateWithImage();
        } else {
            $this->updateWithoutImage();
        }
    }
    
    /**
     * تحديث الإعلان مع تغيير الصورة
     */
    private function updateWithImage(): void
    {
        if ($_FILES["adv_img"]["error"] > 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Upload Error: ' . $_FILES["adv_img"]["error"] . '</div>';
            error_log("خطأ في رفع ملف الإعلان: " . $_FILES["adv_img"]["error"]);
            return;
        }

        // جلب معلومات الصورة القديمة
        $sqlImg = "SELECT * FROM advertisementhome WHERE adv_id = " . $this->adv_id;
        $resImg = mysqli_query($this->con, $sqlImg);
        $rowImg = mysqli_fetch_object($resImg);

        // حذف الصورة القديمة إذا كانت موجودة
        if (!empty($rowImg->adv_img)) {
            $oldImagePath = "../upload/advertisementhome/" . $rowImg->adv_img;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // معالجة الصورة الجديدة
        try {
            $imgSImage = new SimpleImage();
            $imgSImage->load($_FILES['adv_img']['tmp_name']);
            $imgSImage->resize($this->adv_imagewidth, $this->adv_imageheight);

            // إنشاء اسم فريد للصورة
            $file_extension = pathinfo($_FILES['adv_img']['name'], PATHINFO_EXTENSION);
            $safe_filename = preg_replace('/[^a-zA-Z0-9.]/', '_', $_FILES['adv_img']['name']);
            $this->adv_img = $this->adv_imagewidth . rand(1000, 9999) . $this->adv_imageheight . '_' . $safe_filename;

            // حفظ الصورة
            $upload_path = "../upload/advertisementhome/" . $this->adv_img;
            $imgSImage->save($upload_path);

            if (!file_exists($upload_path)) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to save image</div>';
                return;
            }

            // تحديث قاعدة البيانات مع الصورة الجديدة
            $sql = "UPDATE advertisementhome
                    SET
                        adv_img = '" . mysqli_real_escape_string($this->con, $this->adv_img) . "',
                        adv_link = '" . mysqli_real_escape_string($this->con, $this->adv_link) . "',
                        adv_imagewidth = " . (int)$this->adv_imagewidth . ",
                        adv_imageheight = " . (int)$this->adv_imageheight . ",
                        adv_country = '" . mysqli_real_escape_string($this->con, $this->adv_country) . "',
                        adv_position = '" . mysqli_real_escape_string($this->con, $this->adv_position) . "'
                    WHERE adv_id = " . $this->adv_id;

            $result = mysqli_query($this->con, $sql);

            if (!$result) {
                error_log("خطأ في تحديث الإعلان: " . mysqli_error($this->con));
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error: ' . mysqli_error($this->con) . '</div>';
                return;
            }

            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Advertisement updated successfully</div>';

        } catch (Exception $e) {
            error_log("استثناء في معالجة الصورة: " . $e->getMessage());
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Image processing error: ' . $e->getMessage() . '</div>';
        }
    }
    
    /**
     * تحديث الإعلان بدون تغيير الصورة
     */
    private function updateWithoutImage(): void
    {
        $sql = "UPDATE advertisementhome
                SET
                    adv_link = '" . mysqli_real_escape_string($this->con, $this->adv_link) . "',
                    adv_country = '" . mysqli_real_escape_string($this->con, $this->adv_country) . "',
                    adv_position = '" . mysqli_real_escape_string($this->con, $this->adv_position) . "'
                WHERE adv_id = " . $this->adv_id;

        $result = mysqli_query($this->con, $sql);

        if (!$result) {
            error_log("خطأ في تحديث الإعلان: " . mysqli_error($this->con));
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error: ' . mysqli_error($this->con) . '</div>';
            return;
        }

        $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Advertisement updated successfully</div>';
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// التحقق من وجود معرف الإعلان
if (!isset($_GET['aid']) || empty($_GET['aid'])) {
    header("location: advhome-view.php");
    exit();
}

$aid = (int)$_GET['aid'];

if ($aid <= 0) {
    header("location: advhome-view.php");
    exit();
}

// إنشاء كائن التعديل
$ob = new editAdvertisement($aid);
$row = $ob->detailsObj();

if (!$row) {
    header("location: advhome-view.php");
    exit();
}

// معالجة إرسال النموذج
if (isset($_POST['btnUpdate'])) {
    $ob->adv_imagewidth = (int)$_POST['adv_imagewidth'];
    $ob->adv_imageheight = (int)$_POST['adv_imageheight'];
    $ob->adv_img = trim($_FILES['adv_img']['name'] ?? '');
    $ob->adv_link = trim($_POST['adv_link'] ?? '');
    $ob->adv_position = trim($_POST['adv_position'] ?? '');
    $ob->adv_country = isset($_POST['adv_country']) && is_array($_POST['adv_country']) ? implode(",", $_POST['adv_country']) : '';
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    $_SESSION['msg'] = $ob->msg;
    header("location: advhome-edit.php?aid=" . $ob->adv_id);
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
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="advhome-view.php">Advertisement Management</a>
                    </li>
                    <li class="active">Advertisement Edit</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Advertisement Management
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Advertisement Edit
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                            <div id="msg" class="<?php echo strpos($msg, 'success') ? 'alert alert-success' : 'alert alert-danger'; ?>">
                                <?php echo $msg; ?>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Image Width & Height</label>
                                <div class="col-sm-9">
                                    <input type="hidden" name="adv_imagewidth" value="<?php echo (int)$row->adv_imagewidth; ?>" />
                                    <input type="hidden" name="adv_imageheight" value="<?php echo (int)$row->adv_imageheight; ?>" />
                                    <strong><?php echo (int)$row->adv_imagewidth . " x " . (int)$row->adv_imageheight; ?></strong>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Country:</label>
                                <div class="col-sm-8">
                                    <?php
                                    $sqlcnty = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_id DESC";
                                    $rscontry = mysqli_query($con, $sqlcnty);
                                    
                                    $adv_country = !empty($row->adv_country) ? explode(",", $row->adv_country) : [];
                                    ?>
                                    <select id="adv_country" name="adv_country[]" multiple="multiple" class="chosen-select" required>
                                        <?php while ($row_cntry = mysqli_fetch_object($rscontry)): 
                                            $selected = in_array($row_cntry->cn_id, $adv_country) ? 'selected="selected"' : '';
                                        ?>
                                            <option value="<?php echo (int)$row_cntry->cn_id; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($row_cntry->cn_name); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <span class="help-block">Hold Ctrl to select multiple countries</span>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Link</label>
                                <div class="col-sm-9">
                                    <input name="adv_link" id="adv_link" class="col-xs-10 col-sm-5" type="text" style="width:440px;" value="<?php echo htmlspecialchars($row->adv_link ?? ''); ?>" required />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Current Image</label>
                                <div class="col-sm-9">
                                    <?php if (!empty($row->adv_img)): ?>
                                        <img src="../upload/advertisementhome/<?php echo htmlspecialchars($row->adv_img); ?>" alt="Advertisement" style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; padding: 5px;" />
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Banner Position</label>
                                <div class="col-sm-9">
                                    <select name="adv_position" id="adv_position" class="chosen-select">
                                        <option value="top" <?php echo ($row->adv_position == 'top') ? 'selected="selected"' : ''; ?>>Top</option>
                                        <option value="middle" <?php echo ($row->adv_position == 'middle') ? 'selected="selected"' : ''; ?>>Middle</option>
                                        <option value="left" <?php echo ($row->adv_position == 'left') ? 'selected="selected"' : ''; ?>>Left</option>
                                        <option value="bottom" <?php echo ($row->adv_position == 'bottom') ? 'selected="selected"' : ''; ?>>Bottom</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Upload New Image (Optional)</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="adv_img" id="id-input-file-2" type="file" accept="image/*">
                                    </div>
                                    <span class="help-block">Leave empty to keep current image. Allowed formats: JPG, PNG, GIF</span>
                                </div>
                            </div>
                                        
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate">
                                        <i class="icon-ok bigger-110"></i>Update
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i>Reset
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

</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>