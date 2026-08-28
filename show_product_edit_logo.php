<?php
/**
 * File: show_product_edit_logo.php

 * Description: عرض صور شعار المنتج للتعديل مع إمكانية الحذف
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    die("Unauthorized");
}

// التحقق من وجود معرف المنتج
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    http_response_code(400);
    die("Invalid product ID");
}

$product_id = (int)$_GET['pid'];

global $con;

// جلب صور المنتج
$sql = "SELECT pd_imagelogo FROM products WHERE pd_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Product not found");
}

// تقسيم الصور
$images = explode(',', $row['pd_imagelogo'] ?? '');
$image_count = count($images);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل صور الشعار</title>
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .bg_border_new {
            height: 625px;
            width: 910px;
            margin: 0 auto;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .bg_border_new #dvh2 {
            height: 620px;
            overflow-y: auto;
        }
        .myta {
            font-size: 16px;
            font-weight: bold;
            padding: 10px;
            background-color: #E6E6E6;
        }
        #textbox {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding: 10px;
        }
        #imgrow {
            float: left;
            height: 100px;
            width: 107px;
            padding: 1px;
            position: relative;
            border: 1px solid #ddd;
            margin: 5px;
            background-color: #fff;
        }
        .delimg {
            width: 16px;
            height: 16px;
            position: absolute;
            top: -8px;
            right: -8px;
            cursor: pointer;
            z-index: 10;
        }
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ajax {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-weight: bold;
        }
        .ajax:hover {
            background-color: #45a049;
        }
        .no-images {
            text-align: center;
            padding: 50px;
            color: #999;
            font-size: 16px;
        }
    </style>
    
    <script>
    function delproimg(imgname, id, cnt) {
        if (confirm("هل أنت متأكد من حذف هذه الصورة؟")) {
            $.post("ajax-file/updateproductlogo.php", {
                id: id,
                imgname: imgname
            }, function(data) {
                $("#imgrow" + cnt).remove();
            });
        }
    }
    </script>
</head>
<body>
    <div class="bg_border_new" id="dvh1">
        <div style="background-color:#FFFFFF; height:620px" id="dvh2">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td bgcolor="#E6E6E6"><div class="myta">Browse Image</div></td>
                        <td style="padding-right:7px;" align="RIGHT" bgcolor="#E6E6E6">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
            
            <img src="images/zero.gif" height="10" width="1"><br>
            
            <div style="height:450px;">
                <form style="margin:0px;" name="test" action="" onsubmit="return false">
                    <div>
                        <img src="images/zero.gif" height="14" width="1"><br>
                        
                        <div id="browse_cat" style="display:block;">
                            <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                                <tbody>
                                    <tr>
                                        <td width="19"><img src="images/zero.gif" height="1" width="19"></td>
                                        <td bgcolor="#f8fcff">
                                            <table align="left" border="0" cellpadding="0" cellspacing="0">
                                                <tbody>
                                                    <tr>
                                                        <td style="font-family:arial; font-size:12px; padding-left:3px; text-align:left;" width="100%">
                                                            <span id="grp1" style="text-align:center; position:static;">
                                                                <div id="textbox">
                                                                    <?php if ($image_count > 0 && !empty($images[0])): ?>
                                                                        <?php 
                                                                        $counter = 1;
                                                                        foreach ($images as $img): 
                                                                            if (empty($img)) continue;
                                                                            $img_clean = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
                                                                            $image_path = "upload/myproduct/" . $img_clean;
                                                                            $full_path = __DIR__ . "/" . $image_path;
                                                                            $image_exists = file_exists($full_path) && is_file($full_path);
                                                                        ?>
                                                                        <div id="imgrow<?php echo $counter; ?>" style="float:left; height:100px; width:107px; padding:1px; position:relative;">
                                                                            <span>
                                                                                <a onclick="delproimg('<?php echo $img_clean; ?>', '<?php echo $product_id; ?>', '<?php echo $counter; ?>');">
                                                                                    <img src="img/crossimg.png" class="delimg" style="width:16px; height:16px; position:absolute; top:-8px; right:-8px; cursor:pointer;" alt="Delete">
                                                                                </a>
                                                                            </span>
                                                                            <a style="cursor:pointer;">
                                                                                <img src="<?php echo $image_path; ?>" height="100" width="105px;" class="product-image" alt="Product Logo">
                                                                            </a>
                                                                        </div>
                                                                        <?php 
                                                                        $counter++;
                                                                        endforeach; 
                                                                    else: ?>
                                                                        <div class="no-images">No images found</div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td width="100%"><img src="images/zero.gif" height="1" width="5"></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="font-family:arial; font-size:12px; padding-left:3px; background:none;" width="100%">
                                                            <br>
                                                            <div style="height:170px"></div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <img src="images/zero.gif" height="8" width="1">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <a class="ajax" href="popup-imagegallery-logo_edit.php" style="text-decoration:none;">
                            Select from Image Gallery
                        </a>
                        
                        <script>
                        function delproimg(imgname, id, cnt) {
                            if (confirm("هل أنت متأكد من حذف هذه الصورة؟")) {
                                $.post("ajax-file/updateproductlogo.php", {
                                    id: id,
                                    imgname: imgname
                                }, function(data) {
                                    $("#imgrow" + cnt).remove();
                                });
                            }
                        }
                        </script>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>