<?php
/**
 * File:list_photo.php

 * Version: PHP 8.3
 * Description: عرض صورة مؤقتة لصفحة "من نحن" مع خيار الحذف
 */

include "common.php";

// التحقق من وجود معرف المستخدم
if (!isset($_GET['uid']) || empty($_GET['uid'])) {
    die('معرف المستخدم مطلوب');
}

$user_id = (int)$_GET['uid'];

// الحصول على اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// جلب بيانات الصورة المؤقتة
$sql = "SELECT * FROM temp_about_us WHERE tmabs_usrid = " . $user_id;
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
    <title>عرض صورة مؤقتة - من نحن</title>
    <style>
        .file_button, .brw {
            width: 125px;
            height: 125px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .file_button .hiddenMask {
            position: absolute;
            top: -5px;
            right: -5px;
            z-index: 2;
            filter: alpha(opacity=0);
            opacity: 0;
            font-size: 100px !important;
        }
        
        .file_button .fadeButton {
            position: absolute;
            top: 2px;
            left: 0;
            z-index: 1;
        }
        
        body {
            font-family: verdana, Arial, Helvetica;
            font-size: 12px;
            font-weight: bold;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .brw {
            border: none;
            cursor: pointer;
        }
        
        .l_ai {
            padding: 3px;
            border: 1px solid #dbdbdb;
            -webkit-border-radius: 2px;
            -moz-border-radius: 2px;
            border-radius: 2px;
            color: #222;
            font-size: 11px;
            text-decoration: none;
            font-weight: bold;
            background-color: #f1f1f1;
            background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#f1f1f1), to(#f6f6f6));
            background: -webkit-linear-gradient(top, #f1f1f1, #f6f6f6);
            background: -moz-linear-gradient(top, #f1f1f1, #f6f6f6);
            background: -ms-linear-gradient(top, #f1f1f1, #f6f6f6);
            background: -o-linear-gradient(top, #f1f1f1, #f6f6f6);
        }
        
        .l_ai:hover {
            border: 1px solid #c6c6c6;
            color: #222;
        }
        
        .edit {
            box-shadow: 0 0 1px 1px #e4e4e4;
            cursor: pointer;
            position: absolute;
            width: 72px;
            margin: 64px 0 0 6px;
            *margin: 62px 0 0 -36px;
            padding: 2px;
            border: 1px solid #b0b0b0;
            background: -webkit-linear-gradient(top, #ffffff, #f0f0f0);
            background: -moz-linear-gradient(top, #ffffff, #f0f0f0);
            z-index: 10;
        }
        
        .mover {
            border: 1px solid #DCDCDC;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .mover:hover {
            -ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=135, Color='#EAEAEA')";
            box-shadow: 0 0 3px 3px #EAEAEA;
            border: 1px solid #DCDCDC;
        }
        
        .image-container {
            display: inline-block;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .delete-link {
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .delete-link img {
            vertical-align: middle;
        }
        
        .no-image {
            padding: 20px;
            background: #f8f8f8;
            border: 1px dashed #ccc;
            border-radius: 5px;
            color: #666;
        }
        
        .temp-badge {
            background-color: #ff9800;
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 3px;
            margin-top: 5px;
            display: inline-block;
        }
    </style>
    
    <script src="js/jquery-1.7.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        function showrmvButt() {
            $("#cm-ed1").show();
        }
        
        function hidermvButt() {
            $("#cm-ed1").hide();
        }
        
        function DelTempImage(id) {
            if (confirm("هل أنت متأكد من حذف هذه الصورة المؤقتة؟")) {
                // إرسال طلب AJAX لحذف الصورة
                $.post("ajax-file/delete-temp-aboutus-image.php", {id: id}, function(data) {
                    if (data.success) {
                        alert("تم حذف الصورة المؤقتة بنجاح");
                        // إخفاء الصورة بعد الحذف
                        $(".image-container").fadeOut();
                    } else {
                        alert("فشل في حذف الصورة: " + (data.message || "خطأ غير معروف"));
                    }
                }, "json").fail(function(xhr, status, error) {
                    alert("حدث خطأ في الاتصال بالخادم: " + error);
                });
            }
        }
    </script>
</head>
<body>
    <div style="text-align: center;">
        <?php if ($timage_num > 0 && $rowk && !empty($rowk->tmabs_images)): ?>
            <div class="image-container">
                <div class="mover file_button" style="box-shadow: none; border: 1px solid #dcdee2; margin-top: 5px; margin-bottom: 5px; height: 125px; width: 125px;" align="center" onMouseOver="showrmvButt();" onMouseOut="hidermvButt();">
                    
                    <div id="cm-ed1" class="edit" style="display: none; margin-top: 88px; margin-left: 25px;" align="center">
                        <div id="tanuj" style="width: 100px; padding-left: 20px; float: left; clear: both;" align="left">
                            <a onclick="DelTempImage(<?php echo (int)$rowk->tmabs_id; ?>)" class="delete-link" title="حذف الصورة المؤقتة">
                                <img src="images/remove.gif" border="0" width="44" height="10" alt="حذف">
                            </a>
                        </div>
                    </div>
                    
                    <div id="companyimages_1" style="width: 125px; height: 125px; display: flex; align-items: center; justify-content: center;">
                        <img src="upload/myprofile/<?php echo htmlspecialchars($rowk->tmabs_images); ?>" 
                             style="max-width: 100%; max-height: 100%; width: auto; height: auto;" 
                             alt="صورة مؤقتة - من نحن"
                             onerror="this.src='images/no-image.png';">
                    </div>
                </div>
                <div class="temp-badge">صورة مؤقتة</div>
            </div>
        <?php else: ?>
            <div class="no-image">
                لا توجد صورة مؤقتة متاحة
            </div>
        <?php endif; ?>
    </div>
</body>
</html>