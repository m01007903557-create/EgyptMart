<?php
/**
 * File: upload-companylogo.php

 * Description: واجهة رفع شعار الشركة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

// التحقق من تسجيل الدخول
if ($uid == 0) {
    die("Unauthorized");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع شعار الشركة</title>
    
    <link rel="stylesheet" type="text/css" href="css/jquery.fileupload.css">
    <link rel="stylesheet" type="text/css" href="css/jf-1.css">
    
    <style>
        .newAddimage {
            display: block;
            background: -moz-linear-gradient(center top , #FFFFFF, #F0F0F0) repeat scroll 0 0 transparent;
            background: -webkit-linear-gradient(top, #ffffff, #f0f0f0);
            background: -ms-linear-gradient(top, #ffffff, #f0f0f0);
            background: -o-linear-gradient(top, #ffffff, #f0f0f0);
            background-color: #f0f0f0;
            background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#ffffff), to(#f0f0f0));
            color: #444444;
            cursor: pointer;
            position: absolute;
            width: 125px;
            padding-bottom: 1px;
            margin-left: 0px;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f0f0f0');
        }
        .file_button {
            position: relative;
            width: 125px;
            height: 123px;
            overflow: hidden;
        }
        .file_button .hiddenMask {
            position: absolute;
            top: -5px;
            right: -5px;
            z-index: 2;
            filter: alpha(opacity=0);
            opacity: 0;
            font-size: 106px !important;
            cursor: pointer;
        }
        .file_button .fadeButton {
            position: absolute;
            top: 2px;
            left: 0;
            z-index: 1;
        }
        #list_photo {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 10px;
        }
        .photo-item {
            position: relative;
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            margin: 5px;
        }
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-item .delete-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 18px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }
        .photo-item .delete-btn:hover {
            background: darkred;
        }
        .upload_div {
            padding: 10px;
            border: 1px dashed #ccc;
            border-radius: 5px;
            background: #f9f9f9;
            margin-top: 10px;
        }
        .file_input {
            display: inline-block;
            padding: 5px 10px;
            background: #4CAF50;
            color: white;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .file_input:hover {
            background: #45a049;
        }
        .clear { clear: both; }
    </style>
    
    <script src="js/jquery-1.7.min.js" type="text/javascript"></script>
    <script src="js/jquery.ui.widget.js" type="text/javascript"></script>
    <script src="js/jquery.fileupload.js" type="text/javascript"></script>
    
    <script>
    function list_photo() {
        $.get("companylogo-list.php", {'uid': <?php echo $uid; ?>}, function(data) {
            $('#list_photo').html(data);
        });
    }

    function DelTempImage(imid) {
        if (confirm("Remove logo?")) {
            $.get("del_companylogo.php", {imid: imid}, function(data) {
                list_photo();
            });
        }
    }

    function showImgButt() {
        $("#add_image1").show();
    }
    
    function hideImgButt() {
        $("#add_image1").hide();
    }

    $(function() {
        // عرض الصور عند تحميل الصفحة
        list_photo();
        
        // إعداد رفع الملفات
        $('#file_upload').fileupload({
            url: 'upload-company-logo.php',
            dataType: 'json',
            formData: {'uid': <?php echo $uid; ?>},
            done: function(e, data) {
                $.each(data.result.files, function(index, file) {
                    // إعادة تحميل قائمة الصور بعد الرفع
                    list_photo();
                });
            }
        });
    });
    </script>
</head>
<body>
    <div class="file_button" style="margin:0px;" id="addbut">
        <div id="queue">
            <div align="left" id="list_photo" class="line clearfix"></div>
        </div>
        
        <div class="upload_div">
            <img style="float:left; margin-right:10px;" src="<?php echo BASE_URL; ?>/images/comp-logo-90.gif" id="preview_image" alt="Logo Preview">
            <input id="file_upload" type="file" name="files" style="cursor:pointer;" />
            <span class="file_input">Add image</span>
        </div>
    </div>
</body>
</html>