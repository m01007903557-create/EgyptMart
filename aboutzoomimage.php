<?php
/**
 * File: aboutzoomimage.php

 * Version: PHP 8.3
 * Description: عرض صورة "من نحن" بحجم كبير
 * 
 * هذا الملف يعرض صورة من صفحة "من نحن" بحجم كبير (600x500)
 * يتم استدعاؤه عند النقر على صورة مصغرة لعرضها بحجم أكبر
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود معرف المستخدم في الجلسة (اختياري - حسب متطلبات الأمان)
$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

// تضمين الملفات الأساسية
include 'common.php';

// تعيين نوع المحتوى إلى HTML
header('Content-Type: text/html; charset=UTF-8');

// التحقق من وجود التوكن في طلب GET
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('معرف الصورة مطلوب');
}

// تنظيف المدخلات
$token = substr(trim($_GET['token']), 4);
$token = mysqli_real_escape_string($con, $token);

// التحقق من صحة القيمة
if (empty($token)) {
    die('معرف الصورة غير صالح');
}

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// جلب بيانات الصورة من قاعدة البيانات
$sqlchk = "SELECT * FROM about_us WHERE MD5(abtus_id) = '{$token}' LIMIT 1";
$result = mysqli_query($con, $sqlchk);

if (!$result) {
    die('خطأ في جلب البيانات: ' . mysqli_error($con));
}

if (mysqli_num_rows($result) == 0) {
    die('الصورة غير موجودة');
}

$rowchk = mysqli_fetch_object($result);

// التحقق من وجود الصورة في المسار
$image_path = "upload/myprofile/" . $rowchk->abtus_image;
if (empty($rowchk->abtus_image) || !file_exists($image_path)) {
    $image_path = "images/no-image.png"; // صورة افتراضية
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الصورة - <?php echo htmlspecialchars($rowchk->abtus_title ?? 'صورة من نحن'); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .image-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            padding: 20px;
            max-width: 100%;
            text-align: center;
        }
        
        .image-title {
            margin-bottom: 15px;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .image-wrapper {
            overflow: auto;
            max-width: 100%;
            text-align: center;
        }
        
        .image-wrapper img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .image-info {
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
        
        .close-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 20px;
            background-color: #006bb1;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .close-btn:hover {
            background-color: #004d8c;
        }
        
        @media print {
            .close-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="image-container">
        <?php if (!empty($rowchk->abtus_title)): ?>
            <div class="image-title">
                <?php echo htmlspecialchars($rowchk->abtus_title); ?>
            </div>
        <?php endif; ?>
        
        <div class="image-wrapper">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="center">
                        <img src="<?php echo htmlspecialchars($image_path); ?>" 
                             alt="<?php echo htmlspecialchars($rowchk->abtus_title ?? 'صورة من نحن'); ?>"
                             style="max-width: 100%; height: auto;"
                             onerror="this.src='images/no-image.png';">
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="image-info">
            <p>تاريخ الإضافة: <?php echo !empty($rowchk->abtus_date) ? date('d/m/Y', strtotime($rowchk->abtus_date)) : 'غير محدد'; ?></p>
        </div>
        
        <a href="javascript:window.close();" class="close-btn">إغلاق النافذة</a>
    </div>
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>