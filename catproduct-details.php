<?php
/**
 * File: popup-product-details.php
 * Description: عرض تفاصيل المنتج في نافذة منبثقة (Popup)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من وجود التوكن
if (!isset($_GET['token'])) {
    die("Invalid request");
}

$token = substr($_GET['token'], 4);

global $con;

// جلب بيانات المنتج
$sql = "SELECT pd_id, pd_image, pd_title, pd_desc, pd_code, pd_date 
        FROM products 
        WHERE MD5(pd_id) = ? AND pd_status = '1' 
        LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pdrowk = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$pdrowk) {
    die("Product not found");
}

// تنظيف البيانات للعرض
$pd_image = htmlspecialchars($pdrowk->pd_image ?? '', ENT_QUOTES, 'UTF-8');
$pd_title = htmlspecialchars($pdrowk->pd_title ?? '', ENT_QUOTES, 'UTF-8');
$pd_desc = htmlspecialchars($pdrowk->pd_desc ?? '', ENT_QUOTES, 'UTF-8');
$pd_code = htmlspecialchars($pdrowk->pd_code ?? '', ENT_QUOTES, 'UTF-8');
$pd_date = !empty($pdrowk->pd_date) ? date('d M, Y', strtotime($pdrowk->pd_date)) : 'N/A';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المنتج</title>
    <style>
        * { margin: 0; }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .tst-back {
            background-color: #d1d2d4;
            padding: 0 2px 5px 2px;
            border: 1px solid #d1d2d4;
            width: 700px;
            margin: 0 auto;
        }
        
        .tst-bg-img {
            background-color: #fff;
            height: 33px;
            border-bottom: 1px solid #D1D2D4;
            color: #58585A;
            text-align: left;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            font-weight: 600;
            background-image: url(/gifs/cntr-bg.png);
        }
        
        .tst-block {
            margin: 0;
            background-color: #e9e9e9;
            background-image: url(/gifs/triangle1.png);
            background-position: 190px 1px;
            *background-position: 213px 1px;
            background-repeat: no-repeat;
            height: 19px;
            padding: 7px 10px;
            width: 193px;
            *width: 216px;
            text-shadow: 0 1px 1px #FFFFFF;
        }
        
        .tp-block {
            padding: 1px 0px;
            font-family: Arial;
            text-align: left;
            background-color: #f9f9f9;
            margin-bottom: 3px;
            margin: 0;
        }
        
        .dtl-ul {
            margin: 0;
            display: table;
            font-family: Verdana, Geneva, sans-serif;
            font-size: 12px;
            padding: 0 0 5px 0;
            text-align: left;
        }
        
        .dtl-li-1 {
            color: #000;
            display: table-cell;
            float: left;
            list-style: none outside none;
            padding: 2px 0px 2px 10px;
            width: 170px;
            text-shadow: 0 1px 0 #FFFFFF;
        }
        
        .dtl-li-2 {
            color: #000;
            float: left;
            font-weight: bold;
            list-style: none outside none;
            margin: 0;
            padding: 2px;
        }
        
        .dtl-li-3 {
            border-left: 1px solid #CCCCCC;
            color: #222;
            list-style: none outside none;
            margin-left: 190px;
            padding-left: 15px;
            padding-top: 2px;
            padding-bottom: 2px;
            width: 410px;
            word-wrap: break-word;
            text-shadow: 0 1px 0 #FFFFFF;
        }
        
        .dtl-li-4 {
            list-style: none;
            width: 529px;
            height: 1px;
            clear: both;
            margin-bottom: 3px;
            *float: left;
        }
        
        .samp-blck {
            background-color: #e7e8e9;
            height: 33px;
            border-bottom: 1px solid #D1D2D4;
            color: #58585A;
            text-align: left;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            font-weight: 600;
            background-image: url(/gifs/cntr-bg.png);
            background-repeat: repeat-x;
        }
        
        .ps-buy {
            padding: 1px 1px 0 0;
            margin: 0;
            text-align: right;
            float: right;
        }
    </style>
</head>
<body>
    <div class="tst-back">
        <div class="tst-bg-img">
            <p class="tst-block">تفاصيل المنتج</p>
        </div>
        
        <div class="tp-block">
            <ul class="dtl-ul">
                <li class="dtl-li-1">Image</li>
                <li class="dtl-li-3">
                    <img src="upload/myproduct/<?php echo $pd_image; ?>" width="100" alt="<?php echo $pd_title; ?>">
                </li>
                <li class="dtl-li-4">
                    <img alt="" src="images/div-1.png" align="left" width="100%" height="1">
                </li>
                
                <li class="dtl-li-1">Title</li>
                <li class="dtl-li-3"><?php echo $pd_title; ?></li>
                <li class="dtl-li-4">
                    <img alt="" src="images/div-1.png" align="left" width="100%" height="1">
                </li>
                
                <li class="dtl-li-1">Description</li>
                <li class="dtl-li-3"><?php echo nl2br($pd_desc); ?></li>
                <li class="dtl-li-4">
                    <img alt="" src="images/div-1.png" align="left" width="100%" height="1">
                </li>
                
                <li class="dtl-li-1">Item Code</li>
                <li class="dtl-li-3"><?php echo $pd_code; ?></li>
                <li class="dtl-li-4">
                    <img alt="" src="images/div-1.png" align="left" width="100%" height="1">
                </li>
                
                <li class="dtl-li-1">Updated date</li>
                <li class="dtl-li-3"><?php echo $pd_date; ?></li>
            </ul>
        </div>
        
        <p style="clear:both; margin:1px;"></p>
        
        <div class="samp-blck">
            <div class="ps-buy" align="right"></div>
        </div>
    </div>
</body>
</html>