<!--
 * File: admin/editCountryForm.php
 * Version: PHP 8.3 Compatible
 * Description: نافذة منبثقة لإضافة بلد جديد
 * 
 * هذا الملف يحتوي على قالب النافذة المنبثقة لإضافة بلد جديد
 * مع رفع العلم والتحقق من البيانات عبر JavaScript
-->

<div id="modal-form-add" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <!-- نموذج إضافة بلد جديد -->
            <form id="Add_New_Country" name="Add_New_Country" action="" method="post" enctype="multipart/form-data">
                
                <!-- رأس النافذة -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="blue bigger">إضافة بلد جديد</h4>
                </div>

                <!-- محتوى النافذة -->
                <div class="modal-body overflow-visible">
                    <div class="row">
                        
                        <!-- حقل رفع العلم -->
                        <div class="col-xs-12 col-sm-5">
                            <div class="space"></div>
                            <label for="cn_flag">Country Flag <span style="color:#CC0000">*</span>:</label>
                            <input type="file" id="cn_flag" name="cn_flag" accept=".png" required/>
                            <span class="help-block">Only PNG files allowed, 30x20 pixels</span>
                        </div>

                        <!-- حقول البيانات -->
                        <div class="col-xs-12 col-sm-7">
                            
                            <!-- اسم البلد -->
                            <div class="form-group">
                                <label for="cn_name">Country Name <span style="color:#CC0000">*</span>:</label>
                                <div>
                                    <input id="cn_name" name="cn_name" class="input-large form-control" type="text" placeholder="Enter country name" value="" required />
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <!-- رمز العملة -->
                            <div class="form-group">
                                <label for="cn_currency">Currency Code <span style="color:#CC0000">*</span>:</label>
                                <div>
                                    <input id="cn_currency" name="cn_currency" class="input-medium form-control" type="text" placeholder="e.g., USD, EUR, EGP" value="" required />
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <!-- رمز الهاتف -->
                            <div class="form-group">
                                <label for="cn_ph">Phone Code <span style="color:#CC0000">*</span>:</label>
                                <div>
                                    <input id="cn_ph" name="cn_ph" class="input-medium form-control" type="text" placeholder="e.g., +20, +966" value="" required />
                                </div>
                            </div>
                            
                            <!-- رمز البلد (اختياري) -->
                            <div class="space-4"></div>
                            <div class="form-group">
                                <label for="cn_code">Country Code (Optional):</label>
                                <div>
                                    <input id="cn_code" name="cn_code" class="input-medium form-control" type="text" placeholder="e.g., EG, SA, US" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- أزرار التحكم -->
                <div class="modal-footer">
                    <button class="btn btn-sm" data-dismiss="modal">
                        <i class="icon-remove"></i> إلغاء
                    </button>
                    <button class="btn btn-sm btn-primary" type="button" onclick="validAddCountry();">
                        <i class="icon-ok"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
/**
 * فتح نافذة إضافة بلد جديد
 */
function openAddCountryModal() {
    // تفريغ الحقول
    $('#cn_name').val('');
    $('#cn_currency').val('');
    $('#cn_ph').val('');
    $('#cn_code').val('');
    $('#cn_flag').val('');
    
    // فتح النافذة
    $('#modal-form-add').modal('show');
}

/**
 * التحقق من صحة بيانات الإضافة
 */
function validAddCountry() {
    var cn_name = document.getElementById('cn_name');
    var cn_currency = document.getElementById('cn_currency');
    var cn_ph = document.getElementById('cn_ph');
    var cn_code = document.getElementById('cn_code');
    var cn_flag = document.getElementById('cn_flag');
    
    var fileName = cn_flag.value;
    var ext = '';
    
    // التحقق من رفع العلم
    if (fileName == '') {
        alert("Please upload country flag.");
        cn_flag.focus();
        return false;
    }
    
    // التحقق من امتداد الملف
    ext = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
    if (ext != "png") {
        alert("Please upload a PNG file.");
        cn_flag.focus();
        return false;
    }
    
    // التحقق من البيانات الأساسية
    if (cn_name.value.trim() == '') {
        alert("Please enter Country Name.");
        cn_name.focus();
        return false;
    } else if (cn_currency.value.trim() == '') {
        alert("Please enter Currency Code.");
        cn_currency.focus();
        return false;
    } else if (cn_ph.value.trim() == '') {
        alert("Please enter Phone Code.");
        cn_ph.focus();
        return false;
    }
    
    // التحقق من عدم وجود تكرار
    $.post("ajax-file/checkNewCountry.php", {
        cn_name: cn_name.value,
        cn_code: cn_code.value,
        cn_currency: cn_currency.value,
        cn_ph: cn_ph.value
    }, function(data) {
        if (data == 1) {
            alert("Records already exist. Please try with different data.");
        } else {
            submitAddForm();
        }
    }).fail(function() {
        alert("Error checking duplicate data.");
    });
    
    return false;
}

/**
 * إرسال نموذج الإضافة
 */
function submitAddForm() {
    // استخدام FormData للتعامل مع رفع الملفات
    var formData = new FormData(document.getElementById('Add_New_Country'));
    
    $.ajax({
        url: "ajax-file/add-country.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(data) {
            if (data > 0) {
                alert("Country added successfully.");
                $('#modal-form-add').modal('hide');
                showCountryList(); // تحديث القائمة
            } else {
                alert("Error adding country.");
            }
        },
        error: function() {
            alert("Error adding country.");
        }
    });
}
</script>