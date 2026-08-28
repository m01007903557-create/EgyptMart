<?php
/**
 * File: upload-prd-doc.php

 * Description: نافذة رفع مستندات المنتج (PDF)
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

// التحقق من وجود معرف المنتج
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    die("Invalid product ID");
}

$pid = (int)$_GET['pid'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رفع مستندات المنتج</title>
    
    <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
    
    <style>
        .file_button {
            position: relative;
            width: 155px;
            height: 26px;
            overflow: hidden;
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
        #list_photo {
            width: 400px;
            padding-top: 5px;
        }
        .doc-item {
            border: 1px solid #ddd;
            padding: 5px;
            margin-bottom: 5px;
            background-color: #f9f9f9;
            border-radius: 3px;
        }
        .doc-item a {
            color: #0066cc;
            text-decoration: none;
        }
        .doc-item a:hover {
            text-decoration: underline;
        }
        .doc-item .delete-btn {
            color: red;
            cursor: pointer;
            margin-left: 10px;
            font-weight: bold;
        }
        .doc-item .delete-btn:hover {
            color: darkred;
        }
        .clear { clear: both; }
        #file-wrapper {
            margin: 10px 0;
        }
        .title {
            font-size: 12px;
        }
        .f2 {
            font-size: 11px;
            color: #666;
        }
    </style>
    
    <script src="js/jquery-1.7.min.js" type="text/javascript"></script>
    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
    
    <script>
    function list_photo() {
        $.get("product-doc-list.php", {'pid': <?php echo $pid; ?>}, function(data) {
            $('#list_photo').html(data);
        });
    }

    function DelTempdoc(id) {
        if (confirm("هل أنت متأكد من حذف هذا المستند؟")) {
            $.get("del_product_doc.php", {id: id}, function(data) {
                list_photo();
            });
        }
    }

    $(function() {
        $('#file_upload').uploadifive({
            'auto': true,
            'formData': {'pid': <?php echo $pid; ?>},
            'queueID': 'queue',
            'debug': false,
            'method': 'post',
            'buttonClass': 'input_textFiled2',
            'buttonText': 'Upload',
            'uploadScript': 'product-doc-add.php',
            'onUploadComplete': function(file, data) {
                alert(data);
                list_photo();
            }
        });
        
        // عرض المستندات عند تحميل الصفحة
        list_photo();
    });
    </script>
</head>
<body topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
    <form name="iform" style="margin:0px; margin-top:4px;" action="" method="post" enctype="multipart/form-data">
        <div id="queue">
            <div align="left" id="list_photo" class="line clearfix">
                <div class="eID" style="width:400px; padding-top:5px;"></div>
            </div>
        </div>
        
        <div id="buttons">
            <div id="file-wrapper" class="title" title="upload documents from your computer" style="z-index:0;">
                <span class="title" style="z-index:0;">
                    <input type="file" name="file_upload" multiple="multiple" id="file_upload">
                </span>
            </div>
            <div class="clear"></div>
        </div>
    </form>
    <span class="f2" id="indecator_gif0" style="left:37px; position:relative; top:-32px;">(.pdf type attachment only)</span>
</body>
</html>