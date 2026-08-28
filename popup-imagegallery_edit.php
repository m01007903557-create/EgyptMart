<?php
/**
 * File Name: popup-imagegallery_edit.php
 * PHP Version: 8.3
 * Description: معرض صور المزايدات - نسخة مطورة ومتوافقة مع PHP 8.3
 */

declare(strict_types=1);

ob_start();
session_start();

require_once "common.php";

// التحقق من وجود المستخدم في الجلسة
if (empty($_SESSION['uid_indm'] ?? null)) {
    header('Location: sign-in.php');
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
if (!isset($con) || !$con) {
    die('Database connection error');
}

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
    $photos[] = [
        'id' => (int)$row['ph_id'],
        'fileName' => htmlspecialchars($row['ph_fileName'] ?? '', ENT_QUOTES, 'UTF-8')
    ];
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>معرض الصور - المزايدات</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <!-- JavaScript Files -->
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
            border: 3px solid #F44336;
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
        
        .normal:hover {
            border-color: #999;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
        
        .tickmark {
            display: none;
            position: relative;
            bottom: 25px;
            left: 45px;
            color: #4CAF50;
            font-size: 20px;
            font-weight: bold;
            text-shadow: 0 0 2px white;
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
            transition: all 0.3s ease;
        }
        
        #textbox span:hover {
            background-color: #f0f0f0;
            transform: scale(1.02);
        }
        
        #textbox span img {
            height: 100px;
            width: 105px;
            object-fit: cover;
            border-radius: 3px;
            cursor: pointer;
        }
        
        #textbox span a {
            text-decoration: none;
            cursor: pointer;
        }
        
        .saps {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 30px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 5px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        
        .saps:hover {
            background-color: #45a049;
        }
        
        .saps.mtt {
            margin-top: 15px;
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .no-images {
            text-align: center;
            padding: 50px;
            color: #666;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="bg_border_new" style="height:625px; width:910px;" id="dvh1">
    <div style="background-color:#FFFFFF; height:620px;" id="dvh2">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#E6E6E6">
                    <div class="myta">اختر صورة للمزايدة</div>
                </td>
                <td style="padding-right:7px;" align="RIGHT" bgcolor="#E6E6E6">&nbsp;</td>
            </tr>
        </table>
        
        <img src="images/zero.gif" height="10" width="1"><br>
        
        <div style="height: 450px;">
            <form style="margin:0px;" name="test" action="" onsubmit="return false;">
                <div>
                    <img src="images/zero.gif" height="14" width="1"><br>
                    
                    <div id="browse_cat" style="display: block;">
                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                            <tr>
                                <td width="19"><img src="images/zero.gif" height="1" width="19"></td>
                                <td bgcolor="#f8fcff">
                                    <table align="left" border="0" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="font-family:arial; font-size:12px; padding-left:3px; text-align:left;" width="100%">
                                                <span id="grp1" style="text-align: center; position:static;">
                                                    <div id="textbox">
                                                        <?php if (empty($photos)): ?>
                                                            <div class="no-images">
                                                                لا توجد صور في المعرض. الرجاء رفع صور جديدة.
                                                            </div>
                                                        <?php else: ?>
                                                            <?php foreach ($photos as $photo): ?>
                                                                <span>
                                                                    <a onclick="usePhotoToUpload('<?php echo $photo['id']; ?>');" style="cursor:pointer;">
                                                                        <img src="upload/image_gallery/<?php echo $photo['fileName']; ?>" 
                                                                             height="100" 
                                                                             width="105px" 
                                                                             alt="صورة المزايدة"
                                                                             loading="lazy" />
                                                                        <span class="tickmark">✓</span>
                                                                    </a>
                                                                </span>
                                                            <?php endforeach; ?>
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
                                    </table>
                                    <img src="images/zero.gif" height="8" width="1">
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <button class="saps mtt" onclick="usePhoto();">موافق</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * مدير اختيار الصور - متوافق مع PHP 8.3
 */
const ImageSelector = (function() {
    let selectedImage = null;
    
    return {
        /**
         * إضافة الصور إلى المدير وتفعيل خاصية الاختيار
         */
        addImages: function(container) {
            if (!container) return;
            
            const spans = container.getElementsByTagName("span");
            
            for (let i = 0; i < spans.length; i++) {
                const span = spans[i];
                const img = span.getElementsByTagName("img")[0];
                const tick = span.getElementsByClassName("tickmark")[0];
                
                if (img) {
                    // إزالة أي مستمع قديم
                    img.onclick = null;
                    
                    // إضافة مستمع جديد
                    img.onclick = (function(imgElement, tickElement, spanElement) {
                        return function() {
                            ImageSelector.toggleImage(this, tickElement, spanElement);
                        };
                    })(img, tick, span);
                }
            }
        },
        
        /**
         * تبديل حالة اختيار الصورة
         */
        toggleImage: function(imgElement, tickElement, spanElement) {
            if (!imgElement || !tickElement || !spanElement) return;
            
            if (imgElement.className === "highlighted") {
                // إلغاء الاختيار
                imgElement.className = "normal";
                if (tickElement) tickElement.style.display = "none";
                if (spanElement) spanElement.style.border = "none";
            } else {
                // إزالة الاختيار من جميع الصور أولاً
                this.clearAllSelections();
                
                // تحديد الصورة الجديدة
                imgElement.className = "highlighted";
                if (tickElement) tickElement.style.display = "inline";
                if (spanElement) spanElement.style.border = "3px solid #F44336";
                
                selectedImage = imgElement;
            }
        },
        
        /**
         * إزالة الاختيار من جميع الصور
         */
        clearAllSelections: function() {
            const allImages = document.querySelectorAll('#textbox img');
            const allTicks = document.querySelectorAll('#textbox .tickmark');
            const allSpans = document.querySelectorAll('#textbox span');
            
            allImages.forEach(img => img.className = "normal");
            allTicks.forEach(tick => tick.style.display = "none");
            allSpans.forEach(span => span.style.border = "none");
            
            selectedImage = null;
        },
        
        /**
         * الحصول على الصورة المحددة
         */
        getSelectedImage: function() {
            return selectedImage;
        }
    };
})();

// تهيئة المدير عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('textbox');
    if (container) {
        ImageSelector.addImages(container);
    }
});

/**
 * استخدام الصورة المحددة للمزايدة
 */
function usePhotoToUpload(photoId) {
    if (!photoId) return;
    
    // يمكن إضافة منطق إضافي هنا
    console.log('تم اختيار الصورة رقم: ' + photoId);
}

/**
 * تأكيد اختيار الصورة
 */
function usePhoto() {
    const selectedImage = ImageSelector.getSelectedImage();
    
    if (selectedImage) {
        // استخراج معرف الصورة من onclick event
        const parent = selectedImage.closest('span');
        if (parent) {
            const anchor = parent.querySelector('a');
            if (anchor && anchor.onclick) {
                // تنفيذ دالة الاختيار الأصلية
                anchor.onclick();
            }
        }
        
        // إغلاق النافذة إذا كانت مفتوحة في colorbox
        if (typeof parent !== 'undefined' && $.colorbox) {
            $.colorbox.close();
        }
    } else {
        alert('الرجاء اختيار صورة أولاً');
    }
}

// دوال التبويبات (محفوظة للتوافق)
function searchcat() {
    $("#sc").removeClass("tabclose").addClass("tabopen");
    $("#bc").removeClass("tabopen").addClass("tabclose");
    
    $("#browse_cat").css("display","none");
    $("#search_cat").css("display","block");
}

function beowswcat() {
    $("#bc").removeClass("tabclose").addClass("tabopen");
    $("#sc").removeClass("tabopen").addClass("tabclose");
    
    $("#search_cat").css("display","none");
    $("#browse_cat").css("display","block");
}

// دوال إضافية (محفوظة للتوافق مع الكود القديم)
function catajaxFunction(id) {
    $("#display_mcat").css("display","block");
    
    setTimeout(function () {
        type="sell";
        $.post("ajax-file/subcategoryCheckBox.php", {id: id, type: type}, function(data) {  
            $("#scat").html(data);
            $("#loading_scat").css("display","none");
            $("#scat").css("display","block");
        });
    }, 1000);
}

function scatAddDel(id) {
    if($('#scat_' + id).attr('checked')) {
        $.post("ajax-file/addTempSellofferAlertCat.php", {id: id}, function(data) {
            showList();
        });
    } else {
        $.post("ajax-file/delTempSellofferAlertCat.php", {id: id}, function(data) {
            showList();
        });
    }
}

function showList() {
    $.post("ajax-file/showTempSellofferAlertCat.php", {}, function(data) {
        $("#div1").html(data);
    });
}

function remove(id) {
    $.post("ajax-file/delTempSellofferAlertCat.php", {id: id}, function(data) {
        showList();
    });
}
</script>
</body>
</html>
<?php
ob_end_flush();
?>