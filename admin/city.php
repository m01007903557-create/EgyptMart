<?php
/**
 * File: admin/city.php
 * Version: PHP 8.3
 * Description: إدارة المدن في لوحة التحكم
 * 
 * تسمح هذه الصفحة للمشرف بعرض وإضافة وتعديل وحذف المدن
 * لكل بلد من خلال واجهة تفاعلية مع AJAX
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

// جلب قائمة البلدان
$sql_cn = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name";
$res_cn = mysqli_query($con, $sql_cn);
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
                        <a href="setting-view.php">Manage Settings</a>
                    </li>
                    <li class="active">City Management</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Settings
                        <small>
                            <i class="icon-double-angle-right"></i>
                            City Management
                        </small>
                    </h1>
                </div>
                
                <script type="text/javascript">
                /**
                 * عرض مدن بلد معين
                 * @param {number} cid - معرف البلد
                 */
                function ShowCity(cid) {
                    if (cid == 0) {
                        $('#city_list').html('');
                        return;
                    }
                    $.get("city_show.php", {cid: cid}, function(data) {
                        $('#city_list').html(data);
                    }).fail(function() {
                        alert("حدث خطأ في تحميل المدن");
                    });
                }

                /**
                 * حذف مدينة
                 * @param {number} hid - معرف المدينة
                 */
                function DelCity(hid) {
                    var conf = confirm("Are you sure you want to delete this City?");
                    if (conf == true) {
                        var cun = $('select#cun').val();
                        $.get("del_city.php", {hid: hid}, function(data) {
                            ShowCity(cun);
                        }).fail(function() {
                            alert("حدث خطأ في حذف المدينة");
                        });
                    }
                }

                /**
                 * إضافة مدينة جديدة
                 */
                function addCity() {
                    var city_add = $('input#city_add').val().trim();
                    var state_inp = $('#city_state').val();
                    var cun = $('select#cun').val();

                    if (cun == 0 || cun == null) {
                        alert("Please select a country first");
                        return;
                    }
                    
                    if (city_add == "") {
                        alert("Please enter City");
                    } else if (state_inp == "") {
                        alert("Please select state");
                    } else {
                        $.get("city_add.php", {
                            city_add: city_add,
                            cun: cun,
                            state_inp: state_inp
                        }, function(data) {
                            ShowCity(cun);
                            $('#city_add').val('');
                        }).fail(function() {
                            alert("حدث خطأ في إضافة المدينة");
                        });
                    }
                }

                /**
                 * إلغاء إضافة مدينة
                 */
                function CanCity() {
                    $('#save_link').show("fast");
                    $('#save_add').hide("fast");
                    $('#input_add').hide("fast");
                    $('#cancel_add').hide("fast");
                }

                /**
                 * إظهار نموذج إضافة مدينة
                 */
                function ShowaddCity() {
                    $('#save_link').hide("fast");
                    $('#save_add').show("fast");
                    $('#input_add').show("fast");
                    $('#cancel_add').show("fast");
                }

                /**
                 * إظهار نموذج تعديل مدينة
                 * @param {number} hid - معرف المدينة
                 */
                function ShowEditCity(hid) {
                    $('#display_' + hid).hide("fast");
                    $('#edit_' + hid).hide("fast");
                    $('#save_' + hid).show("fast");
                    $('#input_state_' + hid).show("fast");
                    $('#input_' + hid).show("fast");
                }

                /**
                 * تعديل مدينة
                 * @param {number} hid - معرف المدينة
                 */
                function EditCity(hid) {
                    var city_inp = $('input#city_' + hid).val().trim();
                    var state_inp = $('#state_' + hid).val();
                    var metro_inp = $('#metro_' + hid).val();

                    if (city_inp == "") {
                        alert("Please enter City");
                    } else if (state_inp == "") {
                        alert("Please select state");
                    } else {
                        $.get("city_edit.php", {
                            hid: hid,
                            city_inp: city_inp,
                            state_inp: state_inp,
                            metro_inp: metro_inp
                        }, function(data) {
                            $('#display_' + hid).html(data);
                            $('#display_' + hid).show("fast");
                            $('#edit_' + hid).show("fast");
                            $('#save_' + hid).hide("fast");
                            $('#input_' + hid).hide("fast");
                            $('#input_state_' + hid).hide("fast");
                        }).fail(function() {
                            alert("حدث خطأ في تعديل المدينة");
                        });
                    }
                }
                </script>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form name="test_view" id="test_view" method="post">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover" style="width: auto;">
                                            <tr>
                                                <td>
                                                    <?php
                                                    $sql_cn = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name";
                                                    $res_cn = mysqli_query($con, $sql_cn);
                                                    ?>
                                                    <select name="cun" id="cun" class="chosen-select form-control" style="width:300px;" onchange="ShowCity(this.value)">
                                                        <option value="0">-- Select Country --</option>
                                                        <?php while ($rec_cn = mysqli_fetch_object($res_cn)): ?>
                                                            <option value="<?php echo (int)$rec_cn->cn_id; ?>">
                                                                <?php echo htmlspecialchars($rec_cn->cn_name); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>

                                        <div id="city_list"></div>
                                    </div>
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