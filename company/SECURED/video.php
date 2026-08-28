<?php
// company/video.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "includes/header.php";

// التحقق من وجود معرف الشركة
if (!isset($row->usr_id) || empty($row->usr_id)) {
    die("معرف الشركة غير موجود");
}

$usr_id = (int)$row->usr_id;

// جلب بيانات الملف التجاري
$sql_bf = "SELECT * FROM business_profile WHERE bnsprof_uid = '{$usr_id}'";
$res_bf = mysqli_query($con, $sql_bf);
$row_bf = mysqli_fetch_object($res_bf);

if (!$row_bf) {
    die("لم يتم العثور على بيانات الشركة");
}

$bnsprof_id = (int)$row_bf->bnsprof_id;
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>فيديوهات الشركة</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .video-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .video-item {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
        }
        .video-delete-btn {
            color: #ff0000;
            cursor: pointer;
            margin-right: 10px;
            font-size: 14px;
        }
        .video-delete-btn:hover {
            text-decoration: underline;
        }
        .upload-area {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #fff;
        }
        .upload-area input[type="text"] {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .upload-area button {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-save {
            background: #28a745;
            color: white;
        }
        .btn-cancel {
            background: #dc3545;
            color: white;
        }
        .btn-add {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
    </style>
    <script>
    $(document).ready(function() {
        // عرض قائمة الفيديوهات عند تحميل الصفحة
        showVideoList(<?php echo $bnsprof_id; ?>, 1);
    });

    function showVideoList(bnsprofId, page) {
        $.post("../ajax-file/showCompanyVideoList.php", {
            cv_bnsprof_id: bnsprofId,
            page: page
        }, function(data) {
            $("#video_disp").html(data);
        }).fail(function() {
            $("#video_disp").html('<div class="error">حدث خطأ في تحميل الفيديوهات</div>');
        });
    }

    function openUploadArea() {
        $("#upload_area").show();
        $("#add_new_video").hide();
    }

    function closeUploadArea() {
        $("#upload_area").hide();
        $("#add_new_video").show();
        $("#cv_video_link").val(''); // تنظيف الحقل
    }

    function saveVideo() {
        var bnsprofId = $("#cv_bnsprof_id").val();
        var videoLink = $("#cv_video_link").val().trim();

        if (!videoLink) {
            alert('الرجاء إدخال رابط فيديو يوتيوب صحيح');
            return;
        }

        // التحقق من أن الرابط من يوتيوب
        var youtubeRegex = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\//i;
        if (!youtubeRegex.test(videoLink)) {
            alert('الرجاء إدخال رابط يوتيوب صحيح');
            return;
        }

        $("#cv_video_link").prop('disabled', true);
        $(".btn-save").prop('disabled', true).text('جاري الحفظ...');

        $.post("ajax-file/addCompanyVideo.php", {
            cv_bnsprof_id: bnsprofId,
            vlink: videoLink
        }, function(data) {
            $("#cv_video_link").val('').prop('disabled', false);
            $(".btn-save").prop('disabled', false).text('حفظ');
            closeUploadArea();
            $("#video_disp").html('<div class="loading"><img src="images/animated_loading.gif" alt="تحميل..." /></div>');
            alert('تم إضافة الفيديو بنجاح');
            showVideoList(bnsprofId, 1);
        }).fail(function() {
            alert('حدث خطأ أثناء إضافة الفيديو');
            $("#cv_video_link").prop('disabled', false);
            $(".btn-save").prop('disabled', false).text('حفظ');
        });
    }

    function delVideo(videoId, bnsprofId) {
        if (!confirm("هل أنت متأكد من حذف هذا الفيديو؟")) {
            return;
        }

        $.post("ajax-file/delCompanyVideo.php", {
            cv_id: videoId
        }, function(data) {
            showVideoList(bnsprofId, 1);
        }).fail(function() {
            alert('حدث خطأ أثناء حذف الفيديو');
        });
    }
    </script>
</head>

<body>
    <div id="body">
        <ul class="cb">
            <li id="wideColumn">
                <section class="box1">
                    <div class="h2">
                        <h2>فيديوهات الصناعة والمنتجات والشركة</h2>
                    </div>
                    
                    <!-- منطقة إضافة فيديو جديد -->
                    <div id="add_new_video" style="text-align: center; margin-bottom: 20px;">
                        <button class="btn-add" onclick="openUploadArea()">
                            إضافة فيديو جديد
                        </button>
                    </div>

                    <!-- منطقة رفع الفيديو -->
                    <div id="upload_area" class="upload-area" style="display: none;">
                        <h3>إضافة فيديو جديد</h3>
                        <input type="hidden" id="cv_bnsprof_id" value="<?php echo $bnsprof_id; ?>">
                        
                        <label for="cv_video_link">رابط فيديو يوتيوب:</label><br>
                        <input type="text" id="cv_video_link" name="cv_video_link" 
                               placeholder="https://www.youtube.com/watch?v=..." dir="ltr">
                        <br>
                        
                        <button class="btn-save" onclick="saveVideo()">حفظ</button>
                        <button class="btn-cancel" onclick="closeUploadArea()">إلغاء</button>
                        
                        <p style="color: #666; font-size: 12px; margin-top: 10px;">
                            * يرجى إدخال رابط فيديو يوتيوب صحيح
                        </p>
                    </div>

                    <!-- مكان عرض الفيديوهات -->
                    <nav class="comPro">
                        <div id="video_disp" style="text-align:center; padding-top:10px; min-height: 200px;">
                            <div class="loading">
                                <img src="images/animated_loading.gif" alt="جاري التحميل..." />
                            </div>
                        </div>
                    </nav>
                </section>
            </li>
            
            <li id="thinColumn">
                <?php include "includes/right.php"; ?>
            </li>
        </ul>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>