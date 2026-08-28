<?php
/**
 * File: admin/selloffer-details.php
 * Version: PHP 8.3
 * Description: عرض تفاصيل عرض البيع في لوحة التحكم
 * 
 * تعرض هذه الصفحة جميع تفاصيل عرض البيع المحدد مع إمكانية
 * الموافقة أو رفض العرض
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
    header("location: selloffer-view.php");
    exit();
}

// تنظيف المدخلات
$token = substr(trim($_GET['token']), 4);
$token = mysqli_real_escape_string($con, $token);

if (empty($token)) {
    header("location: selloffer-view.php");
    exit();
}

// جلب بيانات عرض البيع
$sql = "SELECT * FROM sale_offer WHERE MD5(so_id) = '{$token}' LIMIT 1";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    header("location: selloffer-view.php");
    exit();
}

$row = mysqli_fetch_object($res);

// معالجة الموافقة على العرض
if (isset($_POST['btnApprove'])) {
    $so_id = (int)$_POST['so_id'];
    
    if ($so_id > 0) {
        $sql = "UPDATE sale_offer SET
                so_approval_status = '1',
                so_approval_date = NOW()
                WHERE so_id = " . $so_id;
        
        $result = mysqli_query($con, $sql);
        
        if ($result) {
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer Approved successfully.</div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger"><i class="icon-remove"></i> Error approving sale offer.</div>';
            error_log("خطأ في الموافقة على عرض البيع: " . mysqli_error($con));
        }
    }
    
    header("Location: selloffer-details.php?token=" . rand(1000, 9999) . md5((string)$so_id));
    exit();
}

// معالجة رفض العرض
if (isset($_POST['btnDisApprove'])) {
    $so_id = (int)$_POST['so_id'];
    
    if ($so_id > 0) {
        $sql = "UPDATE sale_offer SET
                so_approval_status = '2',
                so_approval_date = NOW()
                WHERE so_id = " . $so_id;
        
        $result = mysqli_query($con, $sql);
        
        if ($result) {
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer Disapproved successfully.</div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger"><i class="icon-remove"></i> Error disapproving sale offer.</div>';
            error_log("خطأ في رفض عرض البيع: " . mysqli_error($con));
        }
    }
    
    header("Location: selloffer-details.php?token=" . rand(1000, 9999) . md5((string)$so_id));
    exit();
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// جلب بيانات التصنيفات
$sql_pc = "SELECT m.pc_name as main, c.pc_sort_name as category, s.pc_sort_name as subcategory
           FROM product_category m
           JOIN product_category c ON m.pc_id = c.pc_parent_id
           JOIN product_category s ON c.pc_id = s.pc_parent_id
           WHERE s.pc_id = " . (int)$row->so_pc_id . "
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
                        <a href="selloffer-view.php">Manage Sell Offer</a>
                    </li>
                    <li class="active">Sell Offer Details</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Sell Offer
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Sell Offer Details
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                            <input type="hidden" id="so_id" name="so_id" value="<?php echo (int)$row->so_id; ?>" />

                            <div id="msg" class="<?php echo strpos($msg, 'success') ? 'alert alert-success' : 'alert alert-danger'; ?>">
                                <?php echo $msg; ?>
                            </div>

                            <!-- الخدمة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Service:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars(stripslashes($row->so_service ?? '')); ?></label>
                                </div>
                            </div>
                            
                            <!-- الوصف -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Description:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;"><?php echo nl2br(htmlspecialchars(stripslashes($row->so_description ?? ''))); ?></label>
                                </div>
                            </div>
                            
                            <!-- التصنيف -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Category:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        echo htmlspecialchars($row_pc[0] ?? '') . " &raquo; " . 
                                             htmlspecialchars($row_pc[1] ?? '') . " &raquo; " . 
                                             htmlspecialchars($row_pc[2] ?? ''); 
                                        ?>
                                    </label>
                                </div>
                            </div>

                            <!-- تفضيلات الموقع -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Location Preferences:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php
                                        $locations = [];
                                        if ($row->so_domestic_display == '1') {
                                            $locations[] = "Domestic";
                                        }
                                        if ($row->so_global_display == '1') {
                                            $locations[] = "Global";
                                        }
                                        echo htmlspecialchars(implode(", ", $locations));
                                        ?>
                                    </label>
                                </div>
                            </div>

                            <!-- مدة الصلاحية -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Validity:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        if ($row->so_validity == '365') {
                                            echo "1 Year";
                                        } else if ($row->so_validity == '90') {
                                            echo "3 Months";
                                        } else {
                                            echo "1 Month";
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>

                            <!-- تاريخ النشر -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Posting Date:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php echo !empty($row->so_posting_date) ? date("d-M-Y", strtotime($row->so_posting_date)) : ''; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- الصورة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Picture:</label>
                                <div class="col-sm-8">
                                    <?php if (!empty($row->so_pic)): ?>
                                        <img src="../upload/sale_offer/<?php echo htmlspecialchars($row->so_pic); ?>" width="100px;" height="90px;" style="border: 1px solid #ddd; padding: 3px;" alt="Sale Offer" />
                                    <?php else: ?>
                                        <img src="../upload/sale_offer/no-image.png" width="100px;" height="90px;" style="border: 1px solid #ddd; padding: 3px;" alt="No Image" />
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- حالة الموافقة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Approval Status:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        if ($row->so_approval_status == '1') {
                                            echo '<span class="label label-success">Approved</span>';
                                        } elseif ($row->so_approval_status == '0') {
                                            echo '<span class="label label-warning">Pending Approval</span>';
                                        } elseif ($row->so_approval_status == '2') {
                                            echo '<span class="label label-danger">Disapproved</span>';
                                        }
                                        ?>
                                    </label>    
                                </div>
                            </div>

                            <!-- أزرار التحكم -->
                            <?php if ($row->so_approval_status == '0'): ?>
                                <div class="clearfix form-actions">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button class="btn btn-info" type="submit" name="btnApprove" id="btnApprove" onclick="return confirm('Are you sure you want to approve this sale offer?')">
                                            <i class="icon-ok bigger-110"></i>Approve
                                        </button>
                                        <button class="btn btn-danger" type="submit" name="btnDisApprove" id="btnDisApprove" onclick="return confirm('Are you sure you want to disapprove this sale offer?')">
                                            <i class="icon-ban-circle bigger-110"></i>Disapprove
                                        </button>
                                        <button class="btn btn-info" type="button" onclick="window.location.href='selloffer-view.php'">
                                            <i class="icon-reply bigger-110"></i>Back to List
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="clearfix form-actions">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button class="btn btn-info" type="button" onclick="window.location.href='selloffer-view.php'">
                                            <i class="icon-reply bigger-110"></i>Back to List
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
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