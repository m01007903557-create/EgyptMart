<?php
/**
 * File: admin/buyreq-details.php
 * Version: PHP 8.3
 * Description: عرض تفاصيل طلب الشراء في لوحة التحكم
 * 
 * تعرض هذه الصفحة جميع تفاصيل طلب الشراء المحدد بما في ذلك
 * المنتج، الكمية، الموقع، وغيرها من المعلومات
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// التحقق من وجود التوكن
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("location: buyreq-view.php");
    exit();
}

// تنظيف المدخلات
$token = substr(trim($_GET['token']), 4);
$token = mysqli_real_escape_string($con, $token);

if (empty($token)) {
    header("location: buyreq-view.php");
    exit();
}

// جلب بيانات طلب الشراء
$sql = "SELECT * FROM buy_requirement, measurement_unit 
        WHERE br_estimate_qty_unit = mu_id 
        AND MD5(br_id) = '{$token}' 
        LIMIT 1";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    header("location: buyreq-view.php");
    exit();
}

$row = mysqli_fetch_object($res);

// جلب بيانات الشركة
$bsql = "SELECT bu.* FROM buy_requirement r 
         JOIN business_profile bu ON r.br_u_id = bu.bnsprof_uid 
         WHERE MD5(r.br_id) = '{$token}' 
         LIMIT 1";
$bres = mysqli_query($con, $bsql);
$brow = $bres ? mysqli_fetch_object($bres) : null;
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
                        <a href="buyreq-view.php">Manage Buy Requirement</a>
                    </li>
                    <li class="active">Buy Requirement Details</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Buy Requirement
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Buy Requirement Details
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">

                            <!-- المنتج / الخدمة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Products / Services:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row->br_pd_name ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- الصورة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Image:</label>
                                <div class="col-sm-9">
                                    <?php 
                                    $image = (!empty($row->br_pic)) ? $row->br_pic : 'no-image.png';
                                    $image_path = "../upload/buy_requirement/thumb/" . $image;
                                    ?>
                                    <img src="<?php echo $image_path; ?>" border="0" hspace="0" vspace="0" alt="Buy Requirement" style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; padding: 3px;" />
                                </div>
                            </div>
                            
                            <!-- التفاصيل -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Details:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo nl2br(htmlspecialchars(stripslashes($row->br_requirement ?? ''))); ?></label>
                                </div>
                            </div>

                            <!-- الكمية التقديرية -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Estimated Quantity:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        $qty = !empty($row->br_estimate_qty) ? htmlspecialchars($row->br_estimate_qty) : '';
                                        $unit = !empty($row->mu_name) ? htmlspecialchars($row->mu_name) : '';
                                        echo $qty . " " . $unit;
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- قيمة الطلب التقريبية -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Approximate Order Value:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        $currency = !empty($row->br_apprx_order_currency) ? htmlspecialchars($row->br_apprx_order_currency) : '';
                                        $value = !empty($row->br_apprx_order_value) ? htmlspecialchars($row->br_apprx_order_value) : '';
                                        echo $currency . " " . $value;
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- استخدام المنتج -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Product Application/ Usage:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo nl2br(htmlspecialchars(stripslashes($row->br_description ?? ''))); ?></label>
                                </div>
                            </div>

                            <!-- الموقع الإلكتروني -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Website:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php if (!empty($row->br_website)): ?>
                                            <a href="<?php echo htmlspecialchars($row->br_website); ?>" target="_blank"><?php echo htmlspecialchars($row->br_website); ?></a>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- عروض الأسعار المطلوبة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Need Quotations:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row->br_need_quote_for ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- موقع المورد المفضل -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Preferred Supplier Location:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php
                                        $location_text = '';
                                        if ($row->br_preferred_supplier_location == 'abroad') {
                                            $location_text = "Abroad Only";
                                        } else if ($row->br_preferred_supplier_location == 'any') {
                                            $location_text = "Anywhere";
                                        } else if ($row->br_preferred_supplier_location == 'domestic') {
                                            $location_text = "Domestic Only";
                                        } else if ($row->br_preferred_supplier_location == 'my_city') {
                                            $location_text = "My City Only";
                                        }
                                        echo htmlspecialchars($location_text);
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- سبب الحاجة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Why need this:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo nl2br(htmlspecialchars(stripslashes($row->br_need_for ?? ''))); ?></label>
                                </div>
                            </div>
                            
                            <!-- تكرار الطلب -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Requirement Frequency:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row->br_requirement_frequency ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- الجهة الناشرة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Posted By:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($brow->bnsprof_compname ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- زر العودة -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="button" onclick="window.location.href='buyreq-view.php'">
                                        <i class="icon-reply bigger-110"></i>Back to List
                                    </button>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                    <br clear="all" />
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