<?php
/**
 * File: admin/auction-details.php
 * Version: PHP 8.3
 * Description: عرض تفاصيل المزاد في لوحة التحكم
 * 
 * تعرض هذه الصفحة جميع تفاصيل المزاد المحدد بما في ذلك
 * التصنيفات والتواريخ والقيم والمعلومات الإضافية
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
    header("location: auction-view.php");
    exit();
}

// تنظيف المدخلات
$token = substr(trim($_GET['token']), 4);
$token = mysqli_real_escape_string($con, $token);

if (empty($token)) {
    header("location: auction-view.php");
    exit();
}

// جلب بيانات المزاد
$sql = "SELECT * FROM auction WHERE MD5(auc_id) = '{$token}' LIMIT 1";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    header("location: auction-view.php");
    exit();
}

$row = mysqli_fetch_object($res);

// جلب بيانات الشركة
$bsql = "SELECT bu.* FROM auction a 
         JOIN business_profile bu ON a.auc_usr_id = bu.bnsprof_uid 
         WHERE MD5(a.auc_id) = '{$token}' LIMIT 1";
$bres = mysqli_query($con, $bsql);
$brow = $bres ? mysqli_fetch_object($bres) : null;

// جلب بيانات التصنيفات
$sql_pc = "SELECT m.pc_name as main, c.pc_name as category, s.pc_name as subcategory 
           FROM product_category m
           JOIN product_category c ON m.pc_id = c.pc_parent_id
           JOIN product_category s ON c.pc_id = s.pc_parent_id
           WHERE s.pc_id = " . (int)$row->auc_pc_id . "
           LIMIT 1";
$res_pc = mysqli_query($con, $sql_pc);
$row_pc = $res_pc ? mysqli_fetch_array($res_pc) : ['', '', ''];
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
                        <a href="auction-view.php">Manage Auction</a>
                    </li>
                    <li class="active">Auction Details</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Auction Details
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                            
                            <!-- العنوان -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Heading:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row->auc_heading ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- قيمة المزاد -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Auction Value:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        if (!empty($row->auc_value) && $row->auc_value != '0.00') {
                                            echo htmlspecialchars($row->auc_value) . "&nbsp;" . htmlspecialchars(getCurrency($row->auc_currency ?? ''));
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>

                            <!-- التصنيف الرئيسي -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Main Category:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row_pc[0] ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- التصنيف الفرعي -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Category:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row_pc[1] ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- التصنيف الفرعي الفرعي -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Sub-Category:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row_pc[2] ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- نوع الإشعار -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Notice Type:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row->auc_notice_type ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- الكمية -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Quantity:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        if (!empty($row->auc_qty) && $row->auc_qty != '0.00') {
                                            echo htmlspecialchars($row->auc_qty) . " " . htmlspecialchars(measurement_unit($row->auc_qty_mu_id ?? 0));
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- EMD -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">EMD:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row->auc_emd ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- رسوم المستندات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Fees:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        if (!empty($row->auc_document_fees) && $row->auc_document_fees != '0.00') {
                                            echo htmlspecialchars($row->auc_document_fees) . "&nbsp;" . htmlspecialchars(getCurrency($row->auc_document_fees_currency ?? ''));
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- مدة المشروع -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Project Period:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row->auc_project_period ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- المنتجات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Products:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row->auc_products ?? ''); ?></label>
                                </div>
                            </div>

                            <!-- تاريخ النشر -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Publish Date:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php echo !empty($row->auc_publish_date) ? date("d-M-Y", strtotime($row->auc_publish_date)) : ''; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- بدء بيع المستندات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Sale Start:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php echo !empty($row->auc_docSaleStart_date) ? date("d-M-Y", strtotime($row->auc_docSaleStart_date)) : ''; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- نهاية بيع المستندات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Sale End:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php echo !empty($row->auc_docSaleEnd_date) ? date("d-M-Y", strtotime($row->auc_docSaleEnd_date)) : ''; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- آخر موعد لتقديم المستندات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Submit Before:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php echo !empty($row->auc_docSubmitBefore_date) ? date("d-M-Y", strtotime($row->auc_docSubmitBefore_date)) : ''; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- تاريخ الاستحقاق -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Due Date:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php echo !empty($row->auc_due_date) ? date("d-M-Y", strtotime($row->auc_due_date)) : ''; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- موقع المورد المفضل -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Preferred Supplier Location:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php
                                        $location_text = '';
                                        if ($row->auc_preferred_location == 'abroad') {
                                            $location_text = "Abroad Only";
                                        } else if ($row->auc_preferred_location == 'any') {
                                            $location_text = "Anywhere";
                                        } else if ($row->auc_preferred_location == 'domestic') {
                                            $location_text = "Domestic Only";
                                        } else if ($row->auc_preferred_location == 'my_city') {
                                            $location_text = "My City Only";
                                        }
                                        echo htmlspecialchars($location_text);
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- معايير التأهيل المسبق -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Pre-qualification Criteria:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo nl2br(htmlspecialchars(stripslashes($row->auc_prequalification_criteria ?? ''))); ?></label>
                                </div>
                            </div>
                            
                            <!-- وصف تفصيلي -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Detail Description:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo nl2br(htmlspecialchars(stripslashes($row->auc_details ?? ''))); ?></label>
                                </div>
                            </div>
                            
                            <!-- الجهة الناشرة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Posted By:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($brow->bnsprof_compname ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <?php
                            // جلب الحقول الإضافية
                            $sql_af = "SELECT DISTINCT aav.*, af.* 
                                      FROM auction_additional_value aav
                                      JOIN additional_field af ON aav.aav_af_id = af.af_id
                                      WHERE aav.aav_auc_id = " . (int)$row->auc_id;
                            $res_af = mysqli_query($con, $sql_af);
                            
                            if ($res_af && mysqli_num_rows($res_af) > 0):
                                while ($row_af = mysqli_fetch_object($res_af)):
                            ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="form-field-2">
                                        <?php echo htmlspecialchars(stripslashes($row_af->af_label ?? '')); ?>:
                                    </label>
                                    <div class="col-sm-8">
                                        <label style="padding-top:4px;">
                                            <?php
                                            $sql_aav = "SELECT * FROM auction_additional_value 
                                                       WHERE aav_af_id = " . (int)$row_af->aav_af_id . " 
                                                       AND aav_auc_id = " . (int)$row->auc_id;
                                            $res_aav = mysqli_query($con, $sql_aav);
                                            $i = 0;
                                            if ($res_aav) {
                                                while ($row_aav = mysqli_fetch_object($res_aav)) {
                                                    if ($i > 0) {
                                                        echo "<br/>";
                                                    }
                                                    echo htmlspecialchars(stripslashes($row_aav->aav_value ?? ''));
                                                    $i++;
                                                }
                                            }
                                            ?>
                                        </label>
                                    </div>
                                </div>
                            <?php 
                                endwhile;
                            endif; 
                            ?>
                            
                            <!-- زر العودة -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="button" onclick="window.location.href='auction-view.php'">
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