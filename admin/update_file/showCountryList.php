<?php
/**
 * File: admin/showCountryList.php
 * Version: PHP 8.3
 * Description: عرض قائمة البلدان مع خيارات التعديل والحذف
 * 
 * هذا الملف يعرض جميع البلدان في جدول مع إمكانية تعديل وحذف كل بلد،
 * بالإضافة إلى نافذة منبثقة لتعديل بيانات البلد ورفع علم جديد
 */

include "../common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    echo '<div class="alert alert-danger">خطأ في الاتصال بقاعدة البيانات</div>';
    exit();
}

// جلب قائمة البلدان
$sql_cn = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name";
$res_cn = mysqli_query($con, $sql_cn);

if (!$res_cn) {
    echo '<div class="alert alert-danger">خطأ في جلب البيانات: ' . mysqli_error($con) . '</div>';
    exit();
}
?>
<link rel="stylesheet" href="assets/css/ace.min.css" />
<script type="text/javascript" src="../js/jquery-1.2.1.min.js"></script>
<script src="../uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="../uploadifive/uploadifive.css">

<table id="sample-table-2" class="table table-striped table-bordered table-hover">
<?php
$j = 1;
while ($rec_cn = mysqli_fetch_object($res_cn)):
    
    // تنظيف البيانات للعرض
    $cn_name = htmlspecialchars(ucwords($rec_cn->cn_name ?? ''));
    $cn_currency = htmlspecialchars($rec_cn->cn_currency ?? '');
    $cn_ph = htmlspecialchars($rec_cn->cn_ph ?? '');
    $cn_flag = htmlspecialchars($rec_cn->cn_flag ?? '');
    $cn_id = (int)$rec_cn->cn_id;
    
    // بداية صف جديد كل 3 عناصر
    if ($j == 1):
?>
    <tr>
<?php endif; ?>

        <td width="590px;">
            <table>
                <tr>
                    <td style="width: 86%; border:0px;">
                        <!-- عرض البيانات -->
                        <span id="display_<?php echo $cn_id; ?>">
                            <img src="../images/country_flag/<?php echo $cn_flag; ?>" 
                                 alt="<?php echo $cn_name; ?>" 
                                 align="top" height="16" width="23"/>
                            &nbsp;
                            <?php echo $cn_name; ?> - <?php echo $cn_currency; ?> - <?php echo $cn_ph; ?>
                        </span>
                        
                        <!-- حقول التعديل (مخفية) -->
                        <span id="input_<?php echo $cn_id; ?>" style="display:none;">
                            <input type="text" style="width:100px;" 
                                   name="country_<?php echo $cn_id; ?>" 
                                   id="country_<?php echo $cn_id; ?>" 
                                   value="<?php echo $cn_name; ?>" 
                                   title="Country Name"/>
                            <input type="text" style="width:50px;" 
                                   name="currency_<?php echo $cn_id; ?>" 
                                   id="currency_<?php echo $cn_id; ?>" 
                                   value="<?php echo $cn_currency; ?>" 
                                   title="Currency"/>
                            <input type="text" style="width:50px;" 
                                   name="phone_<?php echo $cn_id; ?>" 
                                   id="phone_<?php echo $cn_id; ?>" 
                                   value="<?php echo $cn_ph; ?>" 
                                   title="Phone Code"/>
                        </span>
                    </td>
                    
                    <!-- أزرار التحكم -->
                    <td style="width: 12%; border:0px;">
                        <span id="edit_<?php echo $cn_id; ?>">
                            <a id="id-btn-job<?php echo $cn_id; ?>" 
                               role="button" 
                               class="editCun ajax badge badge-info" 
                               title="Edit">
                                <i class="icon-edit"></i>
                            </a>
                        </span>
                        <span id="save_<?php echo $cn_id; ?>" style="display:none;">
                            <a href="javascript:EditCountry(<?php echo $cn_id; ?>)" 
                               class="ajax badge badge-success" 
                               title="Update">
                                <i class="icon-check"></i>
                            </a>
                        </span>
                    </td>
                    <td style="width: 4%; border:0px;">
                        <span id="del_<?php echo $cn_id; ?>">
                            <a href="javascript:DelCountry(<?php echo $cn_id; ?>)" 
                               class="badge badge-danger" 
                               title="Delete">
                                <i class="icon-trash"></i>
                            </a>
                        </span>
                        <span id="cancel_<?php echo $cn_id; ?>" style="display:none;">
                            <a href="javascript:CancelEditCountry(<?php echo $cn_id; ?>)" 
                               class="ajax badge badge-danger" 
                               title="Cancel">
                                <i class="icon-remove"></i>
                            </a>
                        </span>
                    </td>
                </tr>
            </table>
        </td>

<?php 
    $j++;
    if ($j == 3):
        $j = 1;
?>
    </tr>
<?php endif; ?>

    <!-- نافذة تعديل البلد (Popup) -->
    <div id="job_form<?php echo $cn_id; ?>" class="backLayer" style="left: 25%; top: 5%; display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="Edit_Country_<?php echo $cn_id; ?>" name="Add_New_Country" action="" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <button type="button" class="close" id="clse_job<?php echo $cn_id; ?>">&times;</button>
                        <h4 class="blue bigger">تعديل بيانات البلد</h4>
                    </div>

                    <div class="modal-body overflow-visible">
                        <div class="row">
                            <div class="col-xs-12 col-sm-5">
                                <div class="space"></div>
                                <input type="hidden" id="cn_id" name="cn_id" value="<?php echo $cn_id; ?>"/>

                                <script type="text/javascript">
                                jQuery(function(){
                                    jQuery('#file_upload<?php echo $cn_id; ?>').uploadifive({
                                        'auto': true,
                                        'formData': {'cn_id': '<?php echo $cn_id; ?>'},
                                        'queueID': 'queue',
                                        'debug': false,
                                        'method': 'post',
                                        'uploadScript': 'editCountryImg.php',
                                        'onUploadComplete': function(file, data) {
                                            showCountryImg(<?php echo $cn_id; ?>);
                                        },
                                        'onError': function(errorType, error) {
                                            alert('حدث خطأ في رفع الملف: ' + error);
                                        }
                                    });
                                });
                                </script>
                                
                                <div>
                                    <div id="img_disp_<?php echo $cn_id; ?>">
                                        <img src="../images/country_flag/<?php echo $cn_flag; ?>" 
                                             alt="<?php echo $cn_name; ?>" 
                                             align="top" height="18" width="26"/>
                                    </div>
                                    <div id="drop" style="padding-left:10px; float:right">
                                        <input type="file" id="file_upload<?php echo $cn_id; ?>" name="file_upload" accept=".png"/>
                                        <span class="help-block">Only PNG files allowed</span>
                                    </div>
                                    <div id="queue"></div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-sm-7">
                                <div class="form-group">
                                    <label for="form-field-username">Country Name <span style="color:#CC0000">*</span></label>
                                    <div>
                                        <input id="cn_name_<?php echo $cn_id; ?>" 
                                               name="cn_name_<?php echo $cn_id; ?>" 
                                               class="input-large form-control" 
                                               type="text" 
                                               placeholder="Country Name" 
                                               value="<?php echo $cn_name; ?>" />
                                    </div>
                                </div>

                                <div class="space-4"></div>

                                <div class="form-group">
                                    <label for="form-field-username">Currency Code <span style="color:#CC0000">*</span></label>
                                    <div>
                                        <input id="cn_currency_<?php echo $cn_id; ?>" 
                                               name="cn_currency_<?php echo $cn_id; ?>" 
                                               class="input-medium form-control" 
                                               type="text" 
                                               placeholder="Currency Code" 
                                               value="<?php echo $cn_currency; ?>" />
                                    </div>
                                </div>

                                <div class="space-4"></div>

                                <div class="form-group">
                                    <label for="form-field-first">Phone Code <span style="color:#CC0000">*</span></label>
                                    <div>
                                        <input id="cn_ph_<?php echo $cn_id; ?>" 
                                               name="cn_ph_<?php echo $cn_id; ?>" 
                                               class="input-medium form-control" 
                                               type="text" 
                                               placeholder="Phone Code" 
                                               value="<?php echo $cn_ph; ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-sm btn-primary" type="button" onclick="updCountry(<?php echo $cn_id; ?>);">
                            <i class="icon-ok"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php endwhile; ?>

<!-- إغلاق الصف الأخير إذا لزم الأمر -->
<?php if ($j > 1): ?>
    </tr>
<?php endif; ?>

</table>

<!-- طبقة التعتيم الخلفية -->
<div class="background_overlay" style="display: none;"></div>

<style>
.backLayer {
    position: fixed;
    background: white;
    z-index: 9999999999999;
    border-radius: 5px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
}
.background_overlay {
    position: fixed;
    left: 0px;
    top: 0px;
    width: 100%;
    height: 100%;
    z-index: 999999;
    background: black;
    opacity: 0.4;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    
    // فتح نافذة التعديل لكل بلد
    $("[id^='id-btn-job']").click(function() {
        var id = this.id.replace('id-btn-job', '');
        $("#job_form" + id).fadeIn(200);
        $(".background_overlay").fadeIn(200);
        positionCookiePopup(id);
    });
    
    // إغلاق نافذة التعديل
    $("[id^='clse_job'], .background_overlay").click(function() {
        var id = this.id.replace('clse_job', '');
        if (isNaN(parseInt(id))) {
            // إغلاق جميع النوافذ
            $("[id^='job_form']").fadeOut(200);
        } else {
            $("#job_form" + id).fadeOut(200);
        }
        $(".background_overlay").fadeOut(200);
    });
    
    // إغلاق بالضغط على ESC
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            $("[id^='job_form']").fadeOut(200);
            $(".background_overlay").fadeOut(200);
        }
    });
});

/**
 * تحديد موقع النافذة المنبثقة
 * @param {number} id - معرف البلد
 */
function positionCookiePopup(id) {
    var popup = $("#job_form" + id);
    if (!popup.is(':visible')) {
        return;
    }
    popup.css({
        left: ($(window).width() - popup.width()) / 2,
        top: ($(window).height() - popup.height()) / 5,
        position: 'fixed'
    });
}

// إعادة تحديد الموقع عند تغيير حجم النافذة
$(window).bind('resize', function() {
    $("[id^='job_form']:visible").each(function() {
        var id = this.id.replace('job_form', '');
        positionCookiePopup(id);
    });
});
</script>