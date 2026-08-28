<?php
declare(strict_types=1);
ob_start();
session_start();

require_once "common.php";

if (empty($_SESSION['uid_indm'] ?? null)) {
    header('Location: sign-in.php');
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// جلب صور المستخدم
$sql_ph = "SELECT ph_id, ph_fileName FROM photo 
           WHERE ph_status = '1' AND ph_u_id = ? 
           ORDER BY ph_id DESC";

$stmt = mysqli_prepare($con, $sql_ph);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$photos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $photos[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>معرض الصور - المزايدات</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <script src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <style>
        .bg_border_new {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .myta {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            font-weight: bold;
            color: #333;
            padding: 8px 12px;
        }
        
        .highlighted {
            border: 3px solid #F44336 !important;
            border-radius: 3px;
            box-shadow: 0 0 5px rgba(244, 67, 54, 0.3);
        }
        
        .highlighted .tickmark {
            display: inline-block !important;
            color: #4CAF50;
            font-size: 20px;
            font-weight: bold;
            position: relative;
            top: -5px;
            left: 5px;
        }
        
        .normal {
            border: 1px solid #ddd;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .tickmark {
            display: none;
            position: relative;
            bottom: 25px;
            left: 45px;
            color: #4CAF50;
            font-size: 20px;
            font-weight: bold;
        }
        
        #textbox {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 15px;
            min-height: 300px;
            max-height: 400px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        
        #textbox span {
            float: left;
            height: 110px;
            width: 110px;
            padding: 5px;
            margin: 2px;
            background-color: #fafafa;
            border-radius: 4px;
            position: relative;
            cursor: pointer;
        }
        
        #textbox span img {
            height: 100px;
            width: 105px;
            object-fit: cover;
            border-radius: 3px;
        }
        
        .saps {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 30px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 5px;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .saps:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="bg_border_new" style="height:525px; width:710px;" id="dvh1">
    <div style="background-color:#FFFFFF; height:520px;" id="dvh2">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td bgcolor="#E6E6E6"><div class="myta">اختر صورة للمنتج</div></td>
                </tr>
            </tbody>
         </table>
        
        <img src="images/zero.gif" height="10" width="1"><br>
        <div style="height: 380px;">
            <form style="margin:0px;" name="test" action="" onsubmit="return false">
                <div>
                    <img src="images/zero.gif" height="14" width="1"><br>
                    
                    <div id="browse_cat" style="display: block;"> 
                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                            <tbody>
                                <tr>
                                    <td width="19"><img src="images/zero.gif" height="1" width="19"></td>
                                    <td bgcolor="#f8fcff"> 
                                        <table align="left" border="0" cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td style="font-family:arial; font-size:12px; padding-left:3px;text-align:left" width="100%">
                                                        <span id="grp1" style="text-align: center;position:static">
                                                            <div id="textbox">
                                                                <?php if (empty($photos)): ?>
                                                                    <div style="text-align:center; width:100%; padding:50px;">⚠️ لا توجد صور في المعرض</div>
                                                                <?php else: ?>
                                                                    <?php foreach ($photos as $photo): ?>
                                                                        <span onclick="selectImage(this, <?php echo $photo['ph_id']; ?>, '<?php echo $photo['ph_fileName']; ?>')">
                                                                            <img id="<?php echo $photo['ph_id']; ?>" src="upload/image_gallery/<?php echo $photo['ph_fileName']; ?>" height="100" width="105px" />
                                                                            <span class="tickmark">✓</span>
                                                                        </span>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <button class="saps" onclick="usePhoto();">موافق</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
var selectedImageId = null;
var selectedImageFile = null;

function selectImage(element, id, fileName) {
    // إزالة التحديد من جميع الصور
    $('#textbox span').removeClass('highlighted');
    $('.tickmark').hide();
    
    // تحديد الصورة الحالية
    $(element).addClass('highlighted');
    $(element).find('.tickmark').show();
    
    selectedImageId = id;
    selectedImageFile = fileName;
    
    console.log("تم اختيار الصورة رقم: " + selectedImageId);
}

function usePhotoToUpload(id) {
    console.log("بدء رفع الصورة: " + id);
    
    // ✅ تحديد نوع الصورة والجدول المناسب من الصفحة الأم
    var tbl = 'temp_buyrequirement_image';
    var typ = 'product'; // افتراضي
    if (window.parent.document.getElementById('selected_gallery_image')) {
        tbl = 'temp_product_image';
        typ = 'product';
    }
    
    // التحقق من نوع الصورة المطلوبة
    if (window.parent.document.getElementById('logo_image_path')) {
        // إذا كان هناك حقل للوجو، فهذه صورة لوجو
        typ = 'logo';
        tbl = 'temp_product_image';
    }
    
    var br_id = 0;
    if (window.parent.document.getElementById('br_id')) {
        br_id = window.parent.document.getElementById('br_id').value;
    }
    
    $.ajax({
        url: 'ajax-file/addNewImgFrmGallery.php',
        method: 'POST',
        data: {
            id: id,
            br_id: br_id,
            tbl: tbl,
            typ: typ
        },
        dataType: 'json',
        success: function(response) {
            console.log("استجابة الخادم:", response);
            if (response.success) {
                // ✅ تخزين في الحقل المخفي المناسب
                if (typ == 'logo') {
                    if (window.parent.document.getElementById('logo_image_path')) {
                        window.parent.document.getElementById('logo_image_path').value = response.image_path;
                    }
                    if (window.parent.document.getElementById('imglogo_disp')) {
                        window.parent.document.getElementById('imglogo_disp').innerHTML = '<img src="' + response.image_path + '" style="width:43px;height:46px;"/>';
                    }
                } else {
                    if (window.parent.document.getElementById('selected_gallery_image')) {
                        window.parent.document.getElementById('selected_gallery_image').value = response.image_file || response.image_path;
                    }
                    if (window.parent.document.getElementById('img_disp')) {
                        window.parent.document.getElementById('img_disp').innerHTML = '<img src="' + response.image_path + '" height="100" width="100"/>';
                    }
                }
                
                alert('تم حفظ الصورة بنجاح');
                
                // إغلاق النافذة
                setTimeout(function() {
                    if (parent.$.colorbox) parent.$.colorbox.close();
                    if (window.parent.$.colorbox) window.parent.$.colorbox.close();
                    $('#cboxClose').click();
                }, 500);
            } else {
                alert('خطأ: ' + (response.error || 'حدث خطأ'));
            }
        },
        error: function(xhr) {
            console.error("خطأ:", xhr);
            alert('حدث خطأ في الاتصال بالخادم');
        }
    });
}

function usePhoto() {
    if (!selectedImageId) {
        alert('الرجاء اختيار صورة أولاً');
        return false;
    }
    usePhotoToUpload(selectedImageId);
    return true;
}
</script>
</body>
</html>
<?php ob_end_flush(); ?>
