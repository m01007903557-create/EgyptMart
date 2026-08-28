<?php
/**
 * File: states_show.php
 * Version: 3.2.0 (ديناميكي لجميع الدول)
 * Description: عرض الولايات بناءً على اختيار الدولة (AJAX)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود معرف الدولة
if (!isset($_GET['cid']) || !is_numeric($_GET['cid'])) {
    echo '<div class="alert alert-warning">Please select a valid country.</div>';
    exit;
}

$countryId = (int)$_GET['cid'];

// ========================================
// تمرير معرف الدولة إلى JavaScript
// ========================================
echo '<script>var CURRENT_COUNTRY_ID = ' . $countryId . ';</script>';

// ========================================
// استخدام الاتصال من ملف connect.php
// ========================================

require_once __DIR__ . '/../lib/connect.php';

if (!isset($con) || !$con) {
    error_log("states_show.php: Database connection not available");
    echo '<div class="alert alert-danger">Database connection failed.</div>';
    exit;
}

// ========================================
// استخدام الكلاس
// ========================================

require_once __DIR__ . '/CountryStatesManager.php';

$statesManager = new CountryStatesManager($con);

// التحقق من وجود الدولة
if (!$statesManager->countryExists($countryId)) {
    echo '<div class="alert alert-warning">Country not found or inactive.</div>';
    exit;
}

// جلب اسم الدولة لعرضه
$countryName = $statesManager->getCountryName($countryId);

// جلب الولايات
$states = $statesManager->getStatesByCountryId($countryId);
$num_rows = count($states);

// ========================================
// بناء HTML لعرض النتائج
// ========================================

$output = '';

// عنوان مع اسم الدولة
$output .= '<div class="country-header">';
$output .= '<h3>' . htmlspecialchars($countryName) . ' - States</h3>';
$output .= '</div>';

// قسم إضافة ولاية جديدة
$output .= '<div class="add-state-row text-center">';
$output .= '<h4>Add New State</h4>';
$output .= '<div class="row">';
$output .= '<div class="col-sm-8 col-sm-offset-2">';
$output .= '<div class="input-group">';
$output .= '<input type="text" id="states_add" class="form-control" placeholder="Enter state name">';
$output .= '<span class="input-group-btn">';
$output .= '<button class="btn btn-primary" onclick="addState()"><i class="icon-plus"></i> Add State</button>';
$output .= '<button class="btn btn-default" id="cancel_add" style="display:none;" onclick="CanState()">Cancel</button>';
$output .= '</span>';
$output .= '</div>';
$output .= '</div>';
$output .= '</div>';
$output .= '</div>';

if ($num_rows == 0) {
    $output .= '<div class="alert alert-info">No states found for this country.</div>';
} else {
    $output .= '<h4>States List (' . $num_rows . ')</h4>';
    $output .= '<div id="states_container">';
    
    foreach ($states as $row) {
        $stateId = (int)$row['state_id'];
        $stateName = htmlspecialchars($row['state_name'], ENT_QUOTES, 'UTF-8');
        
        $output .= '<div class="state-row" id="state_' . $stateId . '">';
        $output .= '<div class="row">';
        $output .= '<div class="col-xs-6">';
        $output .= '<span id="display_' . $stateId . '">' . $stateName . '</span>';
        $output .= '<span id="input_' . $stateId . '" style="display:none;">';
        $output .= '<input type="text" id="states_' . $stateId . '" class="form-control" value="' . $stateName . '" style="width:200px; display:inline-block;">';
        $output .= '</span>';
        $output .= '</div>';
        $output .= '<div class="col-xs-6 text-right action-icons">';
        
        $output .= '<span id="edit_' . $stateId . '">';
        $output .= '<button class="btn btn-xs btn-info" onclick="ShowEditState(' . $stateId . ')">';
        $output .= '<i class="icon-pencil"></i> Edit';
        $output .= '</button>';
        $output .= '</span>';
        
        $output .= '<span id="save_' . $stateId . '" style="display:none;">';
        $output .= '<button class="btn btn-xs btn-success" onclick="EditState(' . $stateId . ')">';
        $output .= '<i class="icon-save"></i> Save';
        $output .= '</button>';
        $output .= '</span>';
        
        $output .= '&nbsp;';
        
        $output .= '<button class="btn btn-xs btn-danger" onclick="DelState(' . $stateId . ')">';
        $output .= '<i class="icon-trash"></i> Delete';
        $output .= '</button>';
        
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
    }
    
    $output .= '</div>';
}

echo $output;

// ========================================
// JavaScript للتعامل مع العمليات
// ========================================
?>
<script>
// ========================================
// دوال JavaScript للتعامل مع الولايات
// ========================================

// معرف الدولة الحالي (يتم تمريره من PHP عبر المتغير CURRENT_COUNTRY_ID)

/**
 * إضافة ولاية جديدة
 */
function addState() {
    var stateName = document.getElementById('states_add').value;
    
    if (!stateName.trim()) {
        alert('الرجاء إدخال اسم المحافظة');
        return;
    }
    
    // تعطيل الزر أثناء الإرسال
    var addBtn = document.querySelector('.btn-primary');
    addBtn.disabled = true;
    addBtn.innerHTML = 'جاري الإضافة...';
    
    // إرسال الطلب
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'states_add.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            // إعادة تمكين الزر
            addBtn.disabled = false;
            addBtn.innerHTML = 'Add State';
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // إعادة تحميل الصفحة لعرض القائمة المحدثة
                        location.reload();
                    } else {
                        alert('خطأ: ' + response.message);
                    }
                } catch(e) {
                    alert('خطأ في معالجة الاستجابة');
                    console.error(e);
                }
            } else {
                alert('خطأ في الاتصال بالخادم');
            }
        }
    };
    // ✅ استخدام CURRENT_COUNTRY_ID (يأتي من PHP)
    xhr.send('country_id=' + CURRENT_COUNTRY_ID + '&state_name=' + encodeURIComponent(stateName));
}

/**
 * إظهار نموذج التعديل
 */
function ShowEditState(stateId) {
    document.getElementById('display_' + stateId).style.display = 'none';
    document.getElementById('input_' + stateId).style.display = 'inline';
    document.getElementById('edit_' + stateId).style.display = 'none';
    document.getElementById('save_' + stateId).style.display = 'inline';
}

/**
 * تحديث ولاية
 */
function EditState(stateId) {
    var newName = document.getElementById('states_' + stateId).value;
    
    if (!newName.trim()) {
        alert('الرجاء إدخال اسم صحيح');
        return;
    }
    
    // تعطيل الزر أثناء الإرسال
    var saveBtn = document.querySelector('#save_' + stateId + ' button');
    saveBtn.disabled = true;
    saveBtn.innerHTML = 'جاري الحفظ...';
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'states_update.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save';
            
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('خطأ: ' + response.message);
                    }
                } catch(e) {
                    alert('خطأ في معالجة الاستجابة');
                    console.error(e);
                }
            } else {
                alert('خطأ في الاتصال بالخادم');
            }
        }
    };
    xhr.send('state_id=' + stateId + '&state_name=' + encodeURIComponent(newName));
}

/**
 * حذف ولاية
 */
function DelState(stateId) {
    if (!confirm('هل أنت متأكد من حذف هذه المحافظة؟')) {
        return;
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'states_delete.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // إزالة العنصر من الصفحة بدون إعادة تحميل
                    var stateRow = document.getElementById('state_' + stateId);
                    if (stateRow) {
                        stateRow.remove();
                        // تحديث عدد الولايات
                        updateStateCount();
                    }
                } else {
                    alert('خطأ: ' + response.message);
                }
            } catch(e) {
                alert('خطأ في معالجة الاستجابة');
                console.error(e);
            }
        }
    };
    xhr.send('state_id=' + stateId);
}

/**
 * إلغاء إضافة ولاية
 */
function CanState() {
    document.getElementById('states_add').value = '';
    document.getElementById('cancel_add').style.display = 'none';
}

/**
 * تحديث عدد الولايات المعروض
 */
function updateStateCount() {
    var container = document.getElementById('states_container');
    if (container) {
        var items = container.querySelectorAll('.state-row');
        var countLabel = document.querySelector('h4');
        if (countLabel && countLabel.textContent.includes('States List')) {
            countLabel.textContent = 'States List (' + items.length + ')';
        }
    }
}

// عند تحميل الصفحة، تأكد من أن زر الإلغاء مخفي
document.addEventListener('DOMContentLoaded', function() {
    var cancelBtn = document.getElementById('cancel_add');
    if (cancelBtn) {
        cancelBtn.style.display = 'none';
    }
});
</script>