<?php
// company/productzoomimage.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
include '../common.php';

// التحقق من وجود token
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("معرف الصورة غير صحيح");
}

$token = substr($_GET['token'], 4);
$token = mysqli_real_escape_string($con, $token);

// جلب بيانات المنتج
$sqlchk = "SELECT * FROM products WHERE md5(pd_id) = '{$token}'";
$result = mysqli_query($con, $sqlchk);

if (!$result || mysqli_num_rows($result) == 0) {
    die("المنتج غير موجود");
}

$rowchk = mysqli_fetch_object($result);

// معالجة الصورة (إذا كانت هناك عدة صور)
$image = !empty($rowchk->pd_image) ? explode(',', $rowchk->pd_image) : [];
$thumbnail = !empty($image[1]) ? $image[0] : ($rowchk->pd_image ?? '');
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض الصورة</title>
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
                <?php if (!empty($thumbnail)): ?>
                    <img src="https://egyptmart.shop/upload/myproduct/<?php echo htmlspecialchars($thumbnail); ?>" 
                         alt="صورة المنتج"
                         style="max-width:100%; max-height:90vh;">
                <?php else: ?>
                    <img src="https://egyptmart.shop/upload/myproduct/noimage.jpg" 
                         alt="لا توجد صورة"
                         style="max-width:100%; max-height:90vh;">
                <?php endif; ?>
            </td>
        </tr>
    </table>
</body>
</html>