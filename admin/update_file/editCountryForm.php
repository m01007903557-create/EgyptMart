<!--
 * File: admin/includes/editCountryForm.php

 * Version: PHP 8.3 Compatible
 * Description: نافذة منبثقة (مودال) لتعديل بيانات بلد موجود
 * 
 * تحتوي هذه النافذة على نموذج تعديل بيانات البلد:
 * - اسم البلد
 * - رمز العملة
 * - رمز الهاتف
 * - صورة العلم (اختياري)
-->

<div id="modal-form-edit" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="Edit_Country" name="Edit_Country" action="" method="post" enctype="multipart/form-data">
                
                <!-- رأس النافذة -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="blue bigger">تعديل بيانات البلد</h4>
                    <p class="text-muted">الرجاء تعديل الحقول المطلوبة</p>
                </div>

                <!-- محتوى النافذة -->
                <div class="modal-body overflow-visible">
                    <div class="row">
                        
                        <!-- الجانب الأيمن: رفع الصورة -->
                        <div class="col-xs-12 col-sm-5">
                            <div class="space"></div>
                            
                            <!-- معرف البلد (مخفي) -->
                            <input type="hidden" id="cn_id" name="cn_id" value="" />
                            
                            <!-- العلم الحالي -->
                            <div id="current-flag-container" style="margin-bottom: 15px; text-align: center;">
                                <label>العلم الحالي:</label>
                                <div>
                                    <img id="current-flag" src="#" alt="العلم الحالي" style="max-width: 100px; max-height: 60px; border: 1px solid #ddd; padding: 3px; display: none;" />
                                </div>
                            </div>
                            
                            <label for="cn_flag">تغيير العلم (اختياري)</label>
                            <div class="form-group">
                                <div class="ace-file-input" style="width:100%;">
                                    <input type="file" id="cn_flag" name="cn_flag" accept="image/png">
                                </div>
                                <span class="help-block">مسموح فقط بصيغة PNG، حجم 30x20 بكسل. اتركه فارغاً للاحتفاظ بالعلم الحالي.</span>
                            </div>
                            
                            <!-- معاينة الصورة الجديدة -->
                            <div id="flag-preview" style="margin-top: 10px; display: none;">
                                <label>معاينة العلم الجديد:</label>
                                <img id="preview-img" src="#" alt="معاينة العلم" style="max-width: 100px; max-height: 60px; border: 1px solid #ddd; padding: 3px;" />
                            </div>
                        </div>

                        <!-- الجانب الأيسر: حقول الإدخال -->
                        <div class="col-xs-12 col-sm-7">
                            
                            <!-- اسم البلد -->
                            <div class="form-group">
                                <label for="cn_name">اسم البلد <span style="color:#F00;">*</span></label>
                                <div>
                                    <input id="cn_name" name="cn_name" class="form-control" type="text" 
                                           placeholder="أدخل اسم البلد" value="" maxlength="100" required />
                                </div>
                            </div>

                            <div class="space-4"></div>

                            <!-- رمز العملة -->
                            <div class="form-group">
                                <label for="cn_currency">رمز العملة <span style="color:#F00;">*</span></label>
                                <div>
                                    <input id="cn_currency" name="cn_currency" class="form-control" type="text" 
                                           placeholder="مثال: USD, EUR, EGP" value="" maxlength="10" required />
                                </div>
                                <span class="help-block">اختصار العملة المكون من 3 أحرف</span>
                            </div>

                            <div class="space-4"></div>

                            <!-- رمز الهاتف -->
                            <div class="form-group">
                                <label for="cn_ph">رمز الهاتف <span style="color:#F00;">*</span></label>
                                <div>
                                    <input id="cn_ph" name="cn_ph" class="form-control" type="text" 
                                           placeholder="مثال: +20, +966, +1" value="" maxlength="10" required />
                                </div>
                                <span class="help-block">رمز الاتصال الدولي مع علامة +</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- رسائل الخطأ -->
                    <div id="country-edit-error-msg" style="margin-top: 15px; display: none;" class="alert alert-danger"></div>
                </div>

                <!-- أزرار التحكم -->
                <div class="modal-footer">
                    <button class="btn btn-sm btn-default" data-dismiss="modal">
                        <i class="icon-remove"></i>
                        إلغاء
                    </button>

                    <button class="btn btn-sm btn-primary" type="button" onclick="editValidCountry();">
                        <i class="icon-ok"></i>
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * تعبئة بيانات البلد في النموذج للتعديل
 * @param {number} id - معرف البلد
 * @param {string} name - اسم البلد
 * @param {string} currency - رمز العملة
 * @param {string} phone - رمز الهاتف
 * @param {string} flag - اسم ملف العلم
 */
function populateEditForm(id, name, currency, phone, flag) {
    $('#cn_id').val(id);
    $('#cn_name').val(name);
    $('#cn_currency').val(currency);
    $('#cn_ph').val(phone);
    
    // عرض العلم الحالي إذا وجد
    if (flag) {
        $('#current-flag').attr('src', '../images/country_flag/' + flag).show();
    } else {
        $('#current-flag').hide();
    }
    
    // إخفاء معاينة الصورة الجديدة
    $('#flag-preview').hide();
    $('#cn_flag').val('');
}

/**
 * معاينة الصورة قبل الرفع
 */
document.getElementById('cn_flag').addEventListener('change', function(e) {
    var preview = document.getElementById('flag-preview');
    var img = document.getElementById('preview-img');
    var file = e.target.files[0];
    
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        img.src = '#';
    }
});

/**
 * التحقق من صحة البيانات قبل الإرسال (للتعديل)
 */
function editValidCountry() {
    var cn_id = document.getElementById('cn_id').value;
    var cn_name = document.getElementById('cn_name').value.trim();
    var cn_currency = document.getElementById('cn_currency').value.trim();
    var cn_ph = document.getElementById('cn_ph').value.trim();
    var cn_flag = document.getElementById('cn_flag').files[0];
    var errorMsg = document.getElementById('country-edit-error-msg');
    
    // إخفاء رسالة الخطأ السابقة
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    
    // التحقق من وجود معرف
    if (!cn_id || cn_id == '') {
        showEditError('معرف البلد غير صالح');
        return false;
    }
    
    // التحقق من الحقول الإجبارية
    if (cn_name == '') {
        showEditError('الرجاء إدخال اسم البلد');
        return false;
    }
    
    if (cn_currency == '') {
        showEditError('الرجاء إدخال رمز العملة');
        return false;
    }
    
    if (cn_ph == '') {
        showEditError('الرجاء إدخال رمز الهاتف');
        return false;
    }
    
    // التحقق من صيغة رمز العملة (2-3 أحرف)
    if (cn_currency.length < 2 || cn_currency.length > 3) {
        showEditError('رمز العملة يجب أن يكون 2-3 أحرف (مثال: USD, EUR, EGP)');
        return false;
    }
    
    // التحقق من الصورة إذا تم اختيار واحدة
    if (cn_flag) {
        if (cn_flag.type != 'image/png') {
            showEditError('صيغة الصورة غير صحيحة. يرجى اختيار صورة بصيغة PNG فقط');
            return false;
        }
        
        if (cn_flag.size > 2 * 1024 * 1024) {
            showEditError('حجم الصورة كبير جداً. الحد الأقصى 2 ميجابايت');
            return false;
        }
    }
    
    // التحقق من عدم وجود تكرار (باستثناء البلد الحالي)
    checkEditDuplicate(cn_id, cn_name, cn_currency, cn_ph);
}

/**
 * عرض رسالة خطأ في نافذة التعديل
 */
function showEditError(message) {
    var errorMsg = document.getElementById('country-edit-error-msg');
    errorMsg.innerHTML = '<i class="icon-remove"></i> ' + message;
    errorMsg.style.display = 'block';
    
    // تمرير إلى أعلى النافذة لعرض الخطأ
    var modalBody = document.querySelector('#modal-form-edit .modal-body');
    if (modalBody) {
        modalBody.scrollTop = 0;
    }
}

/**
 * التحقق من عدم وجود بلد مكرر (أثناء التعديل)
 */
function checkEditDuplicate(id, name, currency, phone) {
    $.post("ajax-file/check-country-duplicate-edit.php", {
        cn_id: id,
        cn_name: name,
        cn_currency: currency,
        cn_ph: phone
    }, function(data) {
        if (data == 1) {
            showEditError('هذه البيانات موجودة مسبقاً في بلد آخر (نفس الاسم، العملة، أو رمز الهاتف)');
        } else {
            // إرسال النموذج
            submitEditForm();
        }
    }).fail(function() {
        showEditError('حدث خطأ في الاتصال. الرجاء المحاولة مرة أخرى.');
    });
}

/**
 * إرسال نموذج التعديل عبر AJAX
 */
function submitEditForm() {
    var formData = new FormData(document.getElementById('Edit_Country'));
    
    $.ajax({
        url: 'country-edit.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            // تعطيل زر الحفظ وإظهار مؤشر التحميل
            $('#modal-form-edit .btn-primary').prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> جاري الحفظ...');
        },
        success: function(response) {
            if (response == 1) {
                $('#modal-form-edit').modal('hide');
                // إعادة تحميل قائمة البلدان
                if (typeof loadCountryList === 'function') {
                    loadCountryList();
                } else {
                    location.reload();
                }
            } else {
                showEditError('فشل في تحديث بيانات البلد. الرجاء المحاولة مرة أخرى.');
                $('#modal-form-edit .btn-primary').prop('disabled', false).html('<i class="icon-ok"></i> حفظ التغييرات');
            }
        },
        error: function() {
            showEditError('حدث خطأ في الاتصال. الرجاء المحاولة مرة أخرى.');
            $('#modal-form-edit .btn-primary').prop('disabled', false).html('<i class="icon-ok"></i> حفظ التغييرات');
        }
    });
}

// إعادة تعيين النموذج عند إغلاق النافذة
$('#modal-form-edit').on('hidden.bs.modal', function() {
    document.getElementById('Edit_Country').reset();
    document.getElementById('flag-preview').style.display = 'none';
    document.getElementById('preview-img').src = '#';
    document.getElementById('current-flag').hide();
    document.getElementById('country-edit-error-msg').style.display = 'none';
});
</script>