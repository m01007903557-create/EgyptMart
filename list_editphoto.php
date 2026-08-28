<?php
/**
 * File: list_editphoto.php
 * Version: PHP 8.3
 * Description: عرض صورة "من نحن" مع خيار الحذف (نسخة مبسطة)
 */

ob_start();
session_start();
include "common.php";

// التحقق من وجود معرف الصورة
if (!isset($_GET['abtid']) || empty($_GET['abtid'])) {
    die('معرف الصورة مطلوب');
}

$abtus_id = (int)$_GET['abtid'];

// الحصول على اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// جلب بيانات الصورة
$sql = "SELECT * FROM about_us WHERE abtus_id = " . $abtus_id;
$recObj = mysqli_query($con, $sql);

if (!$recObj) {
    die('خطأ في الاستعلام: ' . mysqli_error($con));
}

$timage_num = mysqli_num_rows($recObj);
$rowk = mysqli_fetch_object($recObj);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض صورة من نحن</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            text-align: center;
        }
        .image-container {
            display: inline-block;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .image-wrapper {
            position: relative;
            display: inline-block;
        }
        .delete-link {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #d9534f;
            font-size: 12px;
            font-weight: bold;
            padding: 5px 10px;
            border: 1px solid #d9534f;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        .delete-link:hover {
            background-color: #d9534f;
            color: white;
        }
        .no-image {
            padding: 20px;
            background: #f8f8f8;
            border: 1px dashed #ccc;
            border-radius: 5px;
            color: #666;
        }
        .z2 {
            margin-top: 8px;
        }
    </style>
    <script type="text/javascript">
        function DelTempImage(id) {
            if (confirm("هل أنت متأكد من حذف هذه الصورة؟")) {
                // إرسال طلب AJAX لحذف الصورة
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "ajax-file/delete-aboutus-image.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    alert("تم حذف الصورة بنجاح");
                                    location.reload();
                                } else {
                                    alert("فشل في حذف الصورة: " + (response.message || "خطأ غير معروف"));
                                }
                            } catch(e) {
                                alert("تم حذف الصورة بنجاح");
                                location.reload();
                            }
                        } else {
                            alert("حدث خطأ في الاتصال بالخادم");
                        }
                    }
                };
                xhr.send("id=" + id);
            }
        }
    </script>
</head>
<body>
    <div class="image-container">
        <?php if ($timage_num > 0 && $rowk && !empty($rowk->abtus_image)): ?>
            <div class="image-wrapper">
                <img src="upload/myprofile/<?php echo htmlspecialchars($rowk->abtus_image); ?>" 
                     width="125" 
                     height="93" 
                     alt="صورة من نحن"
                     onerror="this.src='images/no-image.png';"
                     style="border: 1px solid #ddd; border-radius: 4px; padding: 3px; background: #fff;">
            </div>
            
            <div id="delete_smallimg_popup" class="z2">
                <a href="javascript:DelTempImage(<?php echo (int)$rowk->abtus_id; ?>)" 
                   class="delete-link" 
                   style="text-decoration:none; text-align: center;">
                    <font size="1px"><b>إزالة</b></font>
                </a>
            </div>
        <?php else: ?>
            <div class="image-wrapper">
                <img src="images/add-image.gif" 
                     width="125" 
                     height="93" 
                     alt="إضافة صورة"
                     style="border: 1px dashed #ccc; border-radius: 4px;">
            </div>
            <div style="margin-top: 8px; color: #666; font-size: 11px;">
                لا توجد صورة
            </div>
        <?php endif; ?>
    </div>
</body>
</html>