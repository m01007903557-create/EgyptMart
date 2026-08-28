<?php
/**
 * File: companylogo-list.php
 * Version: PHP 8.3
 * Description: عرض شعار الشركة مع خيار الحذف
 * 
 * هذا الملف يعرض شعار الشركة من مجلد server/php/files/thumbnail/
 * ويوفر زر حذف يظهر عند تمرير الماوس على الصورة
 */

include "common.php";

// التحقق من وجود معرف المستخدم في طلب GET
if (!isset($_GET['uid']) || empty($_GET['uid'])) {
    die('معرف المستخدم مطلوب');
}

// تنظيف المدخلات
$user_id = (int)$_GET['uid'];

// التحقق من صحة القيمة
if ($user_id <= 0) {
    die('معرف المستخدم غير صالح');
}

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// جلب معلومات شعار الشركة
$sql = "SELECT * FROM business_profile WHERE bnsprof_uid = {$user_id} LIMIT 1";
$recObj = mysqli_query($con, $sql);

if (!$recObj) {
    die('خطأ في جلب البيانات: ' . mysqli_error($con));
}

$rowk = mysqli_fetch_object($recObj);
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شعار الشركة</title>
    <style>
        .file_button, .brw {
            width: 115px;
            height: 115px;
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
            text-align: center;
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
            width: 50px;
        }
        
        .mover {
            cursor: pointer;
            display: inline-block;
            position: relative;
        }
        
        .mover:hover {
            -ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=135, Color='#EAEAEA')";
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
        
        .no-logo {
            padding: 20px;
            background: #f8f8f8;
            border: 1px dashed #ccc;
            border-radius: 5px;
            color: #666;
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #cm-ed1 {
            position: absolute;
            top: 5px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            background: rgba(255,255,255,0.9);
            padding: 5px;
            border-radius: 3px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        #companyimages_1 {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        #companyimages_1 img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
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
            if (confirm("هل أنت متأكد من حذف شعار الشركة؟")) {
                // إرسال طلب AJAX لحذف الشعار
                $.ajax({
                    type: 'POST',
                    url: 'ajax-file/delete-company-logo.php',
                    data: {uid: id},
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert("تم حذف الشعار بنجاح");
                            // إخفاء الصورة بعد الحذف
                            $(".image-container").fadeOut();
                        } else {
                            alert("فشل في حذف الشعار: " + (response.message || "خطأ غير معروف"));
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("حدث خطأ في الاتصال بالخادم: " + error);
                    }
                });
            }
        }
    </script>
</head>
<body>
    <?php if (!empty($rowk->bnsprof_complogo)): ?>
        <div class="image-container">
            <div class="mover file_button" style="box-shadow: none; margin-top:5px; margin-bottom:5px; height:130px; width:100px;" align="center" onMouseOver="showrmvButt();" onMouseOut="hidermvButt();">
                
                <div id="cm-ed1" class="" style="display:none;" align="center">
                    <div id="tanuj" style="width:60px; padding-left:20px; float:left; clear:both;" align="left">
                        <a onclick="DelTempImage(<?php echo (int)$rowk->bnsprof_uid; ?>)" class="delete-link" title="حذف الشعار">
                            <img src="images/remove.gif" border="0" width="44" height="10" alt="حذف">
                        </a>
                    </div>
                </div>
                
                <div id="companyimages_1" style="width:100px; height:100px;">
                    <img src="server/php/files/thumbnail/<?php echo htmlspecialchars($rowk->bnsprof_complogo); ?>" 
                         alt="شعار الشركة"
                         onerror="this.src='images/no-logo.png';"
                         title="شعار الشركة"/>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="no-logo">
            لا يوجد شعار
        </div>
    <?php endif; ?>
</body>
</html>