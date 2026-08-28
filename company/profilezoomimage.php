<?php
// company/profile-image.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
session_start();
include '../common.php';

// التحقق من وجود token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("معرف الصورة غير صحيح");
}

$abtus_id = substr($_GET['token'], 4);
$abtus_id = mysqli_real_escape_string($con, $abtus_id);

// جلب بيانات الصورة
$sql = "SELECT a.*, ph.* 
        FROM about_us a, profile_heading ph 
        WHERE a.abtus_ph_id = ph.ph_id 
        AND md5(a.abtus_id) = '{$abtus_id}' 
        LIMIT 1";

$res = mysqli_query($con, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    die("الصورة غير موجودة");
}

$row = mysqli_fetch_object($res);
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض صورة الملف الشخصي</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
        }
        img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <table width="100%" height="100%">
        <tr>
            <td style="text-align:center; vertical-align:middle;">
                <?php if (!empty($row->abtus_image)): ?>
                    <img src="https://egyptmart.shop/upload/myprofile/<?php echo htmlspecialchars($row->abtus_image); ?>" 
                         alt="صورة الملف الشخصي"
                         style="max-width:100%; max-height:90vh;">
                <?php else: ?>
                    <img src="https://egyptmart.shop/images/noimage.jpg" 
                         alt="لا توجد صورة"
                         style="max-width:100%; max-height:90vh;">
                <?php endif; ?>
            </td>
        </tr>
    </table>
</body>
</html>
