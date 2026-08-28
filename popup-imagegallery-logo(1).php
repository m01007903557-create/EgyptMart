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
    <title>معرض الصور - الشعار</title>
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
        }

        #textbox span img {
            height: 100px;
            width: 105px;
            object-fit: cover;
            border-radius: 3px;
            cursor: pointer;
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
<div class="bg_border_new" style="height:625px; width:910px;" id="dvh1">
    <div style="background-color:#FFFFFF; height:620px;" id="dvh2">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#E6E6E6">
                    <div class="myta">اختر صورة للشعار</div>
                </td>
            </tr>
        </table>

        <div style="height: 450px;">
            <div id="browse_cat" style="display: block;">
                <div id="textbox">
                    <?php if (empty($photos)): ?>
                        <div style="text-align:center; width:100%; padding:50px;">لا توجد صور في المعرض</div>
                    <?php else: ?>
                        <?php foreach ($photos as $photo): ?>
                            <span onclick="selectImage(this, <?php echo $photo['ph_id']; ?>, '<?php echo $photo['ph_fileName']; ?>')">
                                <img src="upload/image_gallery/<?php echo $photo['ph_fileName']; ?>" height="100" width="105px" alt="">
                                <span class="tickmark">✓</span>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <button class="saps" onclick="confirmSelection()">موافق</button>
        </div>
    </div>
</div>

<script>
var selectedImageId = null;
var selectedImageFile = null;

function selectImage(element, id, fileName) {
    $('#textbox span').removeClass('highlighted');
    $('.tickmark').hide();

    $(element).addClass('highlighted');
    $(element).find('.tickmark').show();

    selectedImageId = id;
    selectedImageFile = fileName;
}

function confirmSelection() {
    if (!selectedImageId) {
        alert('الرجاء اختيار صورة أولاً');
        return;
    }
    usePhotoToUploadlogo(selectedImageId);
    usePhotoForLogo();
}
</script>
</body>
</html>
<?php ob_end_flush(); ?>
