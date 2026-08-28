<?php
/**
 * File: admin/company-details.php
 * Version: PHP 8.3
 * Description: عرض تفاصيل الشركة في لوحة التحكم
 * 
 * تعرض هذه الصفحة جميع تفاصيل الشركة المحددة بما في ذلك
 * المعلومات الأساسية، جهات الاتصال، الأرقام التعريفية، والشعار
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
check_user_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// التحقق من وجود التوكن
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("location: company-list.php");
    exit();
}

// تنظيف المدخلات
$token = trim($_GET['token']);
$token = mysqli_real_escape_string($con, $token);

// جلب بيانات الشركة
$sql = "SELECT * FROM user, business_profile 
        WHERE bnsprof_uid = usr_id 
          AND MD5(bnsprof_id) = '{$token}' 
        LIMIT 1";
$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    header("location: company-list.php");
    exit();
}

$row = mysqli_fetch_array($res);

// معالجة أزرار التحكم
if (isset($_POST['btnBack'])) {
    header("location: company-list.php");
    exit();
}

if (isset($_POST['btnEdit'])) {
    $bnsprof_id = (int)$_POST['bnsprof_id'];
    if ($bnsprof_id > 0) {
        header("location: company-edit.php?id=" . md5((string)$bnsprof_id));
        exit();
    }
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
                        <a href="company-list.php">Manage Company</a>
                    </li>
                    <li class="active">Company Details</li>
                </ul><!-- .breadcrumb -->
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Company
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php if (!empty($row['bnsprof_compname'])): ?>
                                Details of <strong><?php echo htmlspecialchars(ucfirst($row['bnsprof_compname'])); ?></strong>
                            <?php else: ?>
                                Company Details
                            <?php endif; ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                            
                            <input type="hidden" id="bnsprof_id" name="bnsprof_id" value="<?php echo (int)$row['bnsprof_id']; ?>" />
                            
                            <!-- Company Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Company Name:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row['bnsprof_compname'] ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- Business Ownership Type -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Ownership Type:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $bo_query = mysqli_query($con, "SELECT owntyp_title FROM ownership_type 
                                                                     WHERE owntyp_id = " . (int)($row['bnsprof_owntype'] ?? 0) . " 
                                                                       AND owntyp_status = '1'");
                                    $bo_row = $bo_query ? mysqli_fetch_object($bo_query) : null;
                                    ?>
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($bo_row->owntyp_title ?? ''); ?></label>
                                </div>
                            </div>

                            <!-- CEO -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">CEO:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars(ucfirst(
                                            trim(($row['bnsprof_ceoprefix'] ?? '') . ' ' . 
                                                 ($row['bnsprof_ceofname'] ?? '') . ' ' . 
                                                 ($row['bnsprof_ceolname'] ?? ''))
                                        )); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Username -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Username:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars(ucwords(
                                            trim(($row['name_prefix'] ?? '') . ' ' . 
                                                 ($row['lname'] ?? '') . ' ' . 
                                                 ($row['fname'] ?? ''))
                                        )); ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Address:</label>
                                <div class="col-sm-9">
                                    <?php 
                                    $address_lines = [];
                                    if (!empty($row['bnsprof_address1'])) {
                                        $address_lines[] = htmlspecialchars($row['bnsprof_address1']);
                                    }
                                    if (!empty($row['bnsprof_address2'])) {
                                        $address_lines[] = htmlspecialchars($row['bnsprof_address2']);
                                    }
                                    $city_name = get_city_name((int)($row['bnsprof_city'] ?? 0));
                                    if (!empty($city_name)) {
                                        $address_lines[] = htmlspecialchars($city_name);
                                    }
                                    $state_name = get_state_name((int)($row['bnsprof_state'] ?? 0));
                                    if (!empty($state_name)) {
                                        $address_lines[] = htmlspecialchars($state_name);
                                    }
                                    if (!empty($row['bnsprof_zipcode'])) {
                                        $address_lines[] = htmlspecialchars($row['bnsprof_zipcode']);
                                    }
                                    
                                    echo implode('<br />', $address_lines);
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Phone Numbers -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Phone Number:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $phones = [];
                                    if (!empty($row['bnsprof_ph1'])) {
                                        $phones[] = htmlspecialchars(trim(($row['bnsprof_phcode1'] ?? '') . ' ' . $row['bnsprof_ph1']));
                                    }
                                    if (!empty($row['bnsprof_ph2'])) {
                                        $phones[] = htmlspecialchars(trim(($row['bnsprof_phcode2'] ?? '') . ' ' . $row['bnsprof_ph2']));
                                    }
                                    if (!empty($row['bnsprof_ph3'])) {
                                        $phones[] = htmlspecialchars(trim(($row['bnsprof_phcode3'] ?? '') . ' ' . $row['bnsprof_ph3']));
                                    }
                                    if (!empty($row['bnsprof_ph4'])) {
                                        $phones[] = htmlspecialchars(trim(($row['bnsprof_phcode4'] ?? '') . ' ' . $row['bnsprof_ph4']));
                                    }
                                    echo implode('<br />', $phones);
                                    ?>
                                </div>
                            </div>

                            <!-- Fax Numbers -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Fax Number:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $faxes = [];
                                    if (!empty($row['bnsprof_fax1'])) {
                                        $faxes[] = htmlspecialchars(trim(($row['bnsprof_faxcode1'] ?? '') . ' ' . $row['bnsprof_fax1']));
                                    }
                                    if (!empty($row['bnsprof_fax2'])) {
                                        $faxes[] = htmlspecialchars(trim(($row['bnsprof_faxcode2'] ?? '') . ' ' . $row['bnsprof_fax2']));
                                    }
                                    echo implode('<br />', $faxes);
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Email Addresses -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Email:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $emails = [];
                                    if (!empty($row['bnsprof_emailalt1'])) {
                                        $emails[] = htmlspecialchars($row['bnsprof_emailalt1']);
                                    }
                                    if (!empty($row['bnsprof_emailalt2'])) {
                                        $emails[] = htmlspecialchars($row['bnsprof_emailalt2']);
                                    }
                                    if (!empty($row['bnsprof_emailalt3'])) {
                                        $emails[] = htmlspecialchars($row['bnsprof_emailalt3']);
                                    }
                                    echo implode('<br />', $emails);
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Website -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Website:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php if (!empty($row['bnsprof_website_alt'])): ?>
                                            <a href="<?php echo htmlspecialchars($row['bnsprof_website_alt']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($row['bnsprof_website_alt']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Business Type -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Business Type:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $bt = explode(',', $row['bnsprof_businesstype'] ?? '');
                                    $bt_names = [];
                                    $c = 0;
                                    foreach ($bt as $btval) {
                                        $btval = (int)$btval;
                                        if ($btval > 0) {
                                            $bt_query = mysqli_query($con, "SELECT bsntyp_title FROM business_type 
                                                                             WHERE bsntyp_id = " . $btval . " 
                                                                               AND bsntyp_status = '1'");
                                            $bt_row = $bt_query ? mysqli_fetch_object($bt_query) : null;
                                            if ($bt_row && !empty($bt_row->bsntyp_title)) {
                                                $bt_names[] = htmlspecialchars($bt_row->bsntyp_title);
                                            }
                                        }
                                    }
                                    echo implode(', ', $bt_names);
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Year of Establishment -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Year of Establishment:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($row['bnsprof_yoe'] ?? ''); ?></label>
                                </div>
                            </div>
                            
                            <!-- Number of Employees -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">No of Employees:</label>
                                <div class="col-sm-9">
                                    <?php
                                    if (!empty($row['bnsprof_comemp']) && $row['bnsprof_comemp'] > 0) {
                                        $noemp_query = mysqli_query($con, "SELECT * FROM employee_range 
                                                                            WHERE emprange_id = " . (int)$row['bnsprof_comemp'] . " 
                                                                              AND emprange_status = '1'");
                                        $noemp = $noemp_query ? mysqli_fetch_array($noemp_query) : null;
                                        if ($noemp) {
                                            echo '<label style="padding-top:4px;">' . htmlspecialchars($noemp['emprange_type'] ?? '') . '</label>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Revenue Sales Turnover -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Revenue Sales Turnover:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $rs_query = mysqli_query($con, "SELECT revturnover_title FROM revenue_turnover 
                                                                     WHERE revturnover_id = " . (int)($row['bnsprof_turnover'] ?? 0) . " 
                                                                       AND revturnover_status = '1'");
                                    $rs_row = $rs_query ? mysqli_fetch_object($rs_query) : null;
                                    ?>
                                    <label style="padding-top:4px;"><?php echo htmlspecialchars($rs_row->revturnover_title ?? ''); ?></label>
                                </div>
                            </div>

                            <!-- Registration Information -->
                            <?php
                            $reg_fields = [
                                'bnsprof_regno' => 'Registration No.',
                                'bnsprof_regauthority' => 'Registration Authority',
                                'bnsprof_cin_no' => 'CIN No.',
                                'bnsprof_tan_no' => 'TAN No.',
                                'bnsprof_pan_no' => 'PAN No.',
                                'bnsprof_svtax_no' => 'Service Tax No.',
                                'bnsprof_excisereg_no' => 'Excise Reg. No.',
                                'bnsprof_vat_no' => 'TIN No. / VAT No.',
                                'bnsprof_ie_code' => 'TDGFT/IE Code',
                                'bnsprof_cst_no' => 'CST No.',
                                'bnsprof_msme_no' => 'SSI No. / MSME No.',
                                'bnsprof_epf_no' => 'EPF No.',
                                'bnsprof_esi_no' => 'ESI No.',
                                'bnsprof_sct_no' => 'SCT No.',
                                'bnsprof_dnb_no' => 'DNB No.',
                                'bnsprof_rbi_no' => 'RBI No.',
                                'bnsprof_fssailic_no' => 'FSSAI-LICENSE No.',
                                'bnsprof_nsic_no' => 'N.S.I.C No.',
                                'bnsprof_sst_no' => 'S.S.T No.'
                            ];
                            
                            foreach ($reg_fields as $field => $label):
                                if (!empty($row[$field])):
                            ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="form-field-2"><?php echo $label; ?>:</label>
                                    <div class="col-sm-9">
                                        <label style="padding-top:4px;"><?php echo htmlspecialchars($row[$field]); ?></label>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>

                            <!-- Company Logo -->
                            <?php if (!empty($row['bnsprof_complogo'])): ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right" for="form-field-2">Company Logo:</label>
                                    <div class="col-sm-9">
                                        <img src="../upload/companylogo/<?php echo htmlspecialchars($row['bnsprof_complogo']); ?>" 
                                             width="200px" height="232px" style="border:1px solid #ddd; padding:3px;" alt="Company Logo" />
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnBack" id="btnBack">
                                        <i class="icon-reply"></i>&nbsp;Back
                                    </button>
                                    <button class="btn btn-yellow" type="submit" name="btnEdit" id="btnEdit">
                                        <i class="icon-edit"></i>&nbsp;Edit
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