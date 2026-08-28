<?php
/**
 * File: admin/country.php
 * Version: PHP 8.3
 * Description: إدارة البلدان والعملات ورموز الهاتف
 * 
 * تسمح هذه الصفحة للمشرف بعرض وإضافة وتعديل وحذف البلدان
 * مع إمكانية رفع أعلام البلدان بصيغة PNG
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";
check_admin_login();



// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إضافة بلد جديد
 */
class addCountry
{
    public $msg;
    public $cn_name;
    public $cn_code;
    public $cn_currency;
    public $cn_ph;
    public $cn_flag;
    public $con;
    
    /**
     * Constructor
     */
    public function __construct($cn_name, $cn_code, $cn_currency, $cn_ph, $cn_flag)
    {
        global $con;
        $this->con = $con;
        $this->cn_name = $cn_name;
        $this->cn_code = $cn_code;
        $this->cn_currency = $cn_currency;
        $this->cn_ph = $cn_ph;
        $this->cn_flag = $cn_flag;
    }
    
    /**
     * إضافة البلد إلى قاعدة البيانات
     */
    public function add(): void
    {
        if (empty($_FILES["cn_flag"]["name"])) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> No file uploaded</div>';
            return;
        }

        if ($_FILES["cn_flag"]["error"] > 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Upload Error: ' . $_FILES["cn_flag"]["error"] . '</div>';
            error_log("خطأ في رفع ملف علم البلد: " . $_FILES["cn_flag"]["error"]);
            return;
        }

        // التحقق من امتداد الملف
        $file_info = pathinfo($_FILES['cn_flag']['name']);
        $file_extension = strtolower($file_info['extension'] ?? '');
        
        if ($file_extension != 'png') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Only PNG files are allowed</div>';
            return;
        }

        // معالجة الصورة وتغيير حجمها
        try {
            $imgSImage = new SimpleImage();
            $imgSImage->load($_FILES['cn_flag']['tmp_name']);
            $imgSImage->resize(30, 20); // تغيير الحجم إلى 30x20 بكسل

            // إنشاء اسم فريد للصورة
            $safe_name = preg_replace('/[^a-zA-Z0-9]/', '_', $this->cn_name) . '_' . 
                         preg_replace('/[^a-zA-Z0-9]/', '_', $this->cn_currency) . '_' .
                         uniqid() . '.png';
            
            $this->cn_flag = $safe_name;

            // حفظ الصورة
            $upload_path = "../images/country_flag/" . $this->cn_flag;
            $imgSImage->save($upload_path);

            if (!file_exists($upload_path)) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to save image</div>';
                return;
            }

            // إدراج البيانات في قاعدة البيانات
            $sql = "INSERT INTO country
                    SET
                        cn_name = '" . mysqli_real_escape_string($this->con, $this->cn_name) . "',
                        cn_code = '" . mysqli_real_escape_string($this->con, strtoupper($this->cn_code)) . "',
                        cn_currency = '" . mysqli_real_escape_string($this->con, strtoupper($this->cn_currency)) . "',
                        cn_ph = '" . mysqli_real_escape_string($this->con, $this->cn_ph) . "',
                        cn_flag = '" . mysqli_real_escape_string($this->con, $this->cn_flag) . "',
                        cn_status = 1";

            $result = mysqli_query($this->con, $sql);

            if (!$result) {
                error_log("خطأ في إدراج البلد: " . mysqli_error($this->con));
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error: ' . mysqli_error($this->con) . '</div>';
                return;
            }

            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Country added successfully.</div>';

        } catch (Exception $e) {
            error_log("استثناء في معالجة الصورة: " . $e->getMessage());
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Image processing error: ' . $e->getMessage() . '</div>';
        }
    }
}

// معالجة رسائل الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);

// معالجة إضافة بلد جديد
if (isset($_POST['cn_name']) && isset($_POST['cn_code']) && isset($_POST['cn_currency']) && isset($_POST['cn_ph'])) {
    $adn = new addCountry(
        trim($_POST['cn_name']),
        trim($_POST['cn_code']),
        trim($_POST['cn_currency']),
        trim($_POST['cn_ph']),
        $_FILES['cn_flag']['name'] ?? ''
    );
    
    $adn->add();
    
    $_SESSION['msg'] = $adn->msg;
    header("Location: country.php");
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
                        <a href="setting-view.php">Manage Settings</a>
                    </li>
                    <li class="active">Country List</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Settings
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Country List
                        </small>
                    </h1>
                </div>
                
                <script type="text/javascript">
                /**
                 * حذف بلد
                 * @param {number} hid - معرف البلد
                 */
                function DelCountry(hid) {
                    var conf = confirm("Are you sure you want to delete this country?");
                    if (conf == true) {
                        $.get("del_country.php", {hid: hid}, function(data) {
                            showCountryList();
                        }).fail(function() {
                            alert("حدث خطأ في حذف البلد");
                        });
                    }
                }
                
                /**
                 * التحقق من صحة بيانات البلد الجديد
                 */
                function validCountry() {
                    var cn_name = document.getElementById('cn_name');
                    var cn_code = document.getElementById('cn_code');
                    var cn_currency = document.getElementById('cn_currency');
                    var cn_ph = document.getElementById('cn_ph');
                    var cn_flag = document.getElementById('cn_flag');
                    var fileName = cn_flag.value;
                    var ext = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
                    
                    if (cn_name.value.trim() == '') {
                        alert("Please enter Country Name.");
                        cn_name.focus();
                        return false;
                    } else if (cn_code.value.trim() == '') {
                        alert("Please enter Country Code.");
                        cn_code.focus();
                        return false;
                    } else if (cn_currency.value.trim() == '') {
                        alert("Please enter Currency Code.");
                        cn_currency.focus();
                        return false;
                    } else if (!isNaN(cn_currency.value.trim())) {
                        alert("Currency Code cannot be a number. Please enter valid Code.");
                        cn_currency.focus();
                        return false;
                    } else if (cn_ph.value.trim() == '') {
                        alert("Please enter Phone Code.");
                        cn_ph.focus();
                        return false;
                    } else if (isNaN(cn_ph.value.trim())) {
                        alert("Please enter valid Phone Code (numbers only).");
                        cn_ph.focus();
                        return false;
                    } else if (cn_flag.value == '') {
                        alert("Please upload Country Flag.");
                        return false;
                    } else if (ext != "png") {
                        alert("Please upload a PNG file.");
                        return false;
                    } else {
                        // التحقق من عدم وجود تكرار
                        $.post("checkNewCountry.php", {
                            cn_name: cn_name.value.trim(),
                            cn_code: cn_code.value.trim(),
                            cn_currency: cn_currency.value.trim(),
                            cn_ph: cn_ph.value.trim()
                        }, function(data) {
                            if (data == 1) {
                                alert("Records already exist. Please try with different data.");
                            } else {
                                $("#Add_New_Country").submit();
                            }
                        }).fail(function() {
                            alert("حدث خطأ في التحقق من البيانات");
                        });
                    }
                }
                
                /**
                 * تحديث بيانات بلد
                 * @param {number} id - معرف البلد
                 */
                function updCountry(id) {
                    var cn_id = id;
                    var cn_name = document.getElementById('cn_name_' + id);
                    var cn_code = document.getElementById('cn_code_' + id);
                    var cn_currency = document.getElementById('cn_currency_' + id);
                    var cn_ph = document.getElementById('cn_ph_' + id);
                    
                    if (cn_name.value.trim() == '') {
                        alert("Please enter Country Name.");
                        cn_name.focus();
                        return false;
                    } else if (cn_code.value.trim() == '') {
                        alert("Please enter Country Code.");
                        cn_code.focus();
                        return false;
                    } else if (!isNaN(cn_code.value.trim())) {
                        alert("Country Code cannot be a number. Please enter valid Code.");
                        cn_code.focus();
                        return false;
                    } else if (cn_currency.value.trim() == '') {
                        alert("Please enter Currency Code.");
                        cn_currency.focus();
                        return false;
                    } else if (!isNaN(cn_currency.value.trim())) {
                        alert("Currency Code cannot be a number. Please enter valid Code.");
                        cn_currency.focus();
                        return false;
                    } else if (cn_ph.value.trim() == '') {
                        alert("Please enter Phone Code.");
                        cn_ph.focus();
                        return false;
                    } else if (isNaN(cn_ph.value.trim())) {
                        alert("Please enter valid Phone Code (numbers only).");
                        cn_ph.focus();
                        return false;
                    } else {
                        // التحقق من عدم وجود تكرار
                        $.post("checkOldCountry.php", {
                            cn_id: cn_id,
                            cn_name: cn_name.value.trim(),
                            cn_code: cn_code.value.trim(),
                            cn_currency: cn_currency.value.trim(),
                            cn_ph: cn_ph.value.trim()
                        }, function(data) {
                            if (data == 1) {
                                alert("Records already exist. Please try with different data.");
                            } else {
                                $.post("updCountry.php", {
                                    cn_id: cn_id,
                                    cn_name: cn_name.value.trim(),
                                    cn_code: cn_code.value.trim(),
                                    cn_currency: cn_currency.value.trim(),
                                    cn_ph: cn_ph.value.trim()
                                }, function(data) {
                                    alert('Record updated successfully.');
                                    $("#job_form" + cn_id).fadeOut(200);
                                    $(".background_overlay").fadeOut(200);
                                    showCountryList();
                                }).fail(function() {
                                    alert("حدث خطأ في تحديث البيانات");
                                });
                            }
                        }).fail(function() {
                            alert("حدث خطأ في التحقق من البيانات");
                        });
                    }
                }
                
                /**
                 * عرض صورة البلد
                 * @param {number} id - معرف البلد
                 */
                function showCountryImg(id) {
                    $.get("showCountryImage.php", {id: id}, function(data) {
                        $("#img_disp_" + id).html('<img src="' + data + '" alt="" height="18" width="26"/>');
                    });
                }
                
                /**
                 * عرض قائمة البلدان
                 */
                function showCountryList() {
                    $("#countryList").html('<div align="center"><img src="images/loader_anim.gif" align="middle"/></div>');
                    $.get("showCountryList.php", function(data) {
                        $("#countryList").html(data);
                    }).fail(function() {
                        $("#countryList").html('<div class="alert alert-danger">حدث خطأ في تحميل القائمة</div>');
                    });
                }
                
                /**
                 * إلغاء إضافة بلد جديد
                 */
                function CanCountry() {
                    $('#save_link').show("fast");
                    $('#save_add').hide("fast");
                    $('#input_add').hide("fast");
                    $('#cancel_add').hide("fast");
                }
                
                /**
                 * إظهار نموذج إضافة بلد جديد
                 */
                function ShowaddCountry() {
                    $('#save_link').hide("fast");
                    $('#save_add').show("fast");
                    $('#input_add').show("fast");
                    $('#cancel_add').show("fast");
                }
                
                /**
                 * إظهار نموذج تعديل بلد
                 * @param {number} hid - معرف البلد
                 */
                function ShowEditCountry(hid) {
                    $('#display_' + hid).hide();
                    $('#edit_' + hid).hide();
                    $('#del_' + hid).hide();
                    
                    $('#input_' + hid).show();
                    $('#save_' + hid).show();
                    $('#cancel_' + hid).show();
                }
                
                /**
                 * إلغاء تعديل بلد
                 * @param {number} hid - معرف البلد
                 */
                function CancelEditCountry(hid) {
                    $('#display_' + hid).show();
                    $('#edit_' + hid).show();
                    $('#del_' + hid).show();
                    
                    $('#input_' + hid).hide();
                    $('#save_' + hid).hide();
                    $('#cancel_' + hid).hide();
                }
                </script>

                <div class="row">
                    <div class="col-xs-12">
                        <div id="msg" class="<?php echo strpos($msg, 'success') ? 'alert alert-success' : 'alert alert-danger'; ?>">
                            <?php echo $msg; ?>
                        </div>

                        <div class="table-responsive">
                            <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                <tr>
                                    <td style="border:0px;" align="center">
                                        <a href="#modal-form" role="button" data-toggle="modal" class="btn btn-xs btn-success">
                                            <i class="icon-plus-sign"></i><b>ADD COUNTRY</b>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <div id="countryList">
                                <div align="center"><img src="images/loader_anim.gif" align="middle"/></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- نافذة إضافة بلد جديد -->
                <div id="modal-form" class="modal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form id="Add_New_Country" name="Add_New_Country" action="" method="post" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="blue bigger">Please fill the following fields</h4>
                                </div>

                                <div class="modal-body overflow-visible">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-5">
                                            <div class="space"></div>
                                            <label>Country Flag (PNG only):</label>
                                            <input type="file" id="cn_flag" name="cn_flag" accept="image/png" required />
                                        </div>

                                        <div class="col-xs-12 col-sm-7">
                                            <div class="form-group">
                                                <label for="cn_name">Country Name <span style="color:#F00;">*</span></label>
                                                <div>
                                                    <input id="cn_name" name="cn_name" class="input-large form-control" type="text" placeholder="e.g. Egypt" value="" required />
                                                </div>
                                            </div>

                                            <div class="space-4"></div>

                                            <div class="form-group">
                                                <label for="cn_code">Country Code <span style="color:#F00;">*</span></label>
                                                <div>
                                                    <input id="cn_code" name="cn_code" class="input-large form-control" type="text" placeholder="e.g. EG" maxlength="2" value="" required />
                                                </div>
                                                <span class="help-block">Two-letter country code (ISO 3166-1 alpha-2)</span>
                                            </div>

                                            <div class="space-4"></div>

                                            <div class="form-group">
                                                <label for="cn_currency">Currency Code <span style="color:#F00;">*</span></label>
                                                <div>
                                                    <input id="cn_currency" name="cn_currency" class="input-medium form-control" type="text" placeholder="e.g. EGP" maxlength="3" value="" required />
                                                </div>
                                                <span class="help-block">Three-letter currency code (ISO 4217)</span>
                                            </div>

                                            <div class="space-4"></div>

                                            <div class="form-group">
                                                <label for="cn_ph">Phone Code <span style="color:#F00;">*</span></label>
                                                <div>
                                                    <input id="cn_ph" name="cn_ph" class="input-medium form-control" type="text" placeholder="e.g. +20" value="" required />
                                                </div>
                                                <span class="help-block">International dialing code with + sign</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-sm btn-default" data-dismiss="modal">
                                        <i class="icon-remove"></i> Cancel
                                    </button>
                                    <button class="btn btn-sm btn-primary" type="button" onClick="validCountry();">
                                        <i class="icon-ok"></i> Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <br clear="all" />
            </div>
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
    
    // تحميل قائمة البلدان
    showCountryList();
    
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

    $('#id-input-file-1 , #id-input-file-2, #cn_flag').ace_file_input({
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

    $('#modal-form-edit input[type=file]').ace_file_input({
        style:'well',
        btn_choose:'Drop files here or click to choose',
        btn_change:null,
        no_icon:'icon-cloud-upload',
        droppable:true,
        thumbnail:'large'
    });

    $('#modal-form-edit').on('shown.bs.modal', function () {
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