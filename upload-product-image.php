<?php
/**
 * File: upload-product-image.php

 * Description: نافذة رفع الصور المؤقتة للمنتج
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
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع الصور المؤقتة للمنتج</title>
    
    <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
    
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
        .clear { clear: both; }
    </style>
    
    <script src="js/jquery-1.7.min.js" type="text/javascript"></script>
    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
    
    <script>
    function list_photo() {
        $.get("product_temphoto.php", {'uid': <?php echo $uid; ?>}, function(data) {
            $('#list_photo').html(data);
        });
    }

    function DelTempImage(imid) {
        if (confirm("هل أنت متأكد من حذف هذه الصورة؟")) {
            $.get("del_product_tmpimage.php", {imid: imid}, function(data) {
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
        $('#file_upload').uploadifive({
            'auto': true,
            'formData': {'uid': '<?php echo $uid; ?>'},
            'queueID': 'queue',
            'debug': false,
            'method': 'post',
            'buttonClass': 'input_textFiled2',
            'buttonText': '<div style="padding-right:35px"><strong style="font-size:14px;font-family:arial black;cursor:pointer;">+</strong> Add</div>',
            'uploadScript': 'product-tempimage.php',
            'onUploadComplete': function(file, data) {
                list_photo();
            }
        });
        
        // عرض الصور عند تحميل الصفحة
        list_photo();
    });
    </script>
</head>
<body leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
    <form name="iform" style="margin:0px;" action="" method="post" enctype="multipart/form-data">
        <div class="file_button" style="margin:0px;" id="addbut">
            <div id="queue">
                <div align="left" id="list_photo" class="line clearfix"></div>
            </div>

            <div id="buttons" onMouseOver="showImgButt();" onMouseOut="hideImgButt();">
                <img style="cursor:pointer" src="images/add-image.gif" width="125" height="125" alt="Add Image">
                
                <div id="file-wrapper" class="title" title="upload images from your computer" style="">
                    <div style="display:block;" class="newAddimage cpr" id="old_img_f" align="center"></div>
                    <div id="add_image1" style="border:1px solid rgb(176,176,176); left:22px; padding:3px 7px; position:absolute; font-size:12px; text-align:center; top:85px; width:65px; height:20px; display:none;" class="newAddimage cpr">
                        <input type="file" name="file_upload" multiple="multiple" id="file_upload" style="">
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </form>
</body>
</html>