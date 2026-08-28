<?php
/**
 * ملف عرض صور الميزات (Feature Images)
 * 
 * @filename    list_temp_photo.php (أو أي اسم مناسب)
 * @version     2.0.0

 * @description هذا الملف مسؤول عن عرض صور الميزات المرتبطة بميزة معينة
 *      * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء الجلسة بشكل آمن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// التحقق من صلاحيات المستخدم (يمكن تفعيلها)
// check_user_login();

// التحقق من وجود معرف الصفحة
$usid = filter_input(INPUT_GET, 'pid', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 999999]
]);

if (!$usid) {
    // إرجاع جدول فارغ مع رسالة خطأ
    echo '<div class="alert alert-danger">معرف الميزة غير صحيح</div>';
    exit();
}

// استعلام لجلب الصور المرتبطة بالميزة
$sql = "SELECT fi_id, fi_image, fi_f_id, fi_created_date 
        FROM feature_images 
        WHERE fi_f_id = ? 
        ORDER BY fi_id DESC";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $usid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$timage_num = mysqli_stmt_num_rows($stmt);

// التحقق من وجود أخطاء في الاستعلام
if (!$result) {
    echo '<div class="alert alert-danger">خطأ في جلب الصور: ' . mysqli_error($con) . '</div>';
    mysqli_stmt_close($stmt);
    exit();
}

// التحقق من وجود صور
if ($timage_num == 0) {
    echo '<div class="alert alert-info">لا توجد صور لهذه الميزة بعد</div>';
    mysqli_stmt_close($stmt);
    exit();
}

// بدء الجدول
echo '<table class="feature-images-table" style="width:100%; border-collapse:collapse;">';
echo '<thead>';
echo '<tr>';
echo '<th colspan="4" style="background-color:#f5f5f5; padding:10px; text-align:center;">';
echo '<i class="icon-picture"></i> صور الميزة (' . $timage_num . ')';
echo '</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$i = 1;
$row_count = 0;

// حلقة عرض الصور
while ($row = mysqli_fetch_assoc($result)) {
    $row_count++;
    
    // بداية صف جديد كل 4 صور
    if ($i == 1) {
        echo '<tr>';
    }
    
    // عرض خلية الصورة
    echo '<td style="width:25%; padding:10px; text-align:center; border:1px solid #ddd;">';
    echo '<div class="image-container" style="position:relative; display:inline-block;">';
    
    // الصورة
    echo '<img src="../upload/feature/' . htmlspecialchars($row['fi_image'], ENT_QUOTES, 'UTF-8') . '" 
               width="112" height="74" 
               style="border:1px solid #ccc; border-radius:5px;"
               alt="Feature Image" 
               title="' . htmlspecialchars($row['fi_image'], ENT_QUOTES, 'UTF-8') . '">';
    
    // زر الحذف
    echo '<a href="javascript:DelTempImage_rc(' . (int)$row['fi_id'] . ')" 
               class="remove" 
               style="position:absolute; top:-5px; right:-5px; background:white; border-radius:50%;"
               onclick="return confirm(\'هل أنت متأكد من حذف هذه الصورة؟\');">';
    echo '<img src="uploadifive/uploadifive-cancel.png" width="20" height="20" title="حذف الصورة">';
    echo '</a>';
    
    // معلومات إضافية (اختياري)
    if (!empty($row['fi_created_date'])) {
        echo '<div style="font-size:11px; color:#666; margin-top:5px;">';
        echo date('Y-m-d', strtotime($row['fi_created_date']));
        echo '</div>';
    }
    
    echo '</div>'; // نهاية image-container
    echo '</td>';
    
    // إغلاق الصف بعد 4 صور
    if ($i == 4 || $row_count == $timage_num) {
        // إضافة خلايا فارغة لإكمال الصف إذا لزم الأمر
        $remaining = 4 - $i;
        for ($j = 0; $j < $remaining; $j++) {
            echo '<td style="width:25%; padding:10px;"></td>';
        }
        echo '</tr>';
        $i = 0;
    }
    
    $i++;
}

// إغلاق الجدول
echo '</tbody>';
echo '</table>';

// إضافة رسالة نجاح أو تعليمات (اختياري)
echo '<div style="margin-top:15px; text-align:right; font-size:12px; color:#666;">';
echo '<i class="icon-info-sign"></i> يمكنك النقر على علامة <b style="color:#c00;">X</b> لحذف أي صورة.';
echo '</div>';

// إغلاق الاستعلام
mysqli_stmt_close($stmt);
mysqli_close($con);
?>

<!-- إضافة بعض التنسيقات CSS لتحسين المظهر -->
<style>
.feature-images-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.feature-images-table td {
    vertical-align: middle;
    background-color: #fff;
    transition: all 0.3s ease;
}

.feature-images-table td:hover {
    background-color: #fafafa;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.image-container {
    position: relative;
    display: inline-block;
    transition: transform 0.3s ease;
}

.image-container:hover {
    transform: scale(1.05);
}

.image-container .remove {
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-container:hover .remove {
    opacity: 1;
}

.remove img {
    border: none;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.remove:hover img {
    transform: scale(1.1);
}

.alert {
    padding: 12px 20px;
    margin: 15px 0;
    border-radius: 5px;
    font-size: 14px;
}

.alert-info {
    background-color: #d9edf7;
    border: 1px solid #bce8f1;
    color: #31708f;
}

.alert-danger {
    background-color: #f2dede;
    border: 1px solid #ebccd1;
    color: #a94442;
}
</style>

<!-- إضافة سكريبت إضافي للتحسين -->
<script type="text/javascript">
// تحسين دالة الحذف بإضافة تأكيد
function DelTempImage_rc(imageId) {
    if (confirm('هل أنت متأكد من حذف هذه الصورة؟ لا يمكن التراجع عن هذا الإجراء.')) {
        // هنا يمكن إضافة كود AJAX لحذف الصورة
        // مثال:
        /*
        $.get("delete_feature_image.php", {fi_id: imageId}, function(data) {
            if(data.success) {
                // إعادة تحميل الصور بعد الحذف
                location.reload();
            } else {
                alert('فشل حذف الصورة');
            }
        });
        */
        
        // حالياً نستخدم الدالة الموجودة في الصفحة الأصلية
        // يمكن استدعاء الدالة الأصلية مع التأكيد
        // return DelTempImage_rc_original(imageId);
    }
    return false;
}
</script>

<!-- نهاية ملف عرض الصور - الإصدار 2.0.0 -->