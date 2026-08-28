<?php
/**
 * File Name: myprofileorder.php

 * PHP Version: 8.3
 * Description: صفحة ترتيب عرض الموضوعات في صفحة "من نحن" - نسخة مطورة ومتوافقة مع PHP 8.3
 */

declare(strict_types=1);

require_once 'common.php';

// التحقق من الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// استبدال mysql_* بـ MySQLi
$sql_wc = "SELECT * FROM website_content WHERE wc_usr_id = ?";
$stmt_wc = mysqli_prepare($con, $sql_wc);
mysqli_stmt_bind_param($stmt_wc, 'i', $uid);
mysqli_stmt_execute($stmt_wc);
$result_wc = mysqli_stmt_get_result($stmt_wc);
$row_wc = mysqli_fetch_object($result_wc);
mysqli_stmt_close($stmt_wc);

if (!$row_wc) {
    // إذا لم يكن هناك محتوى موقع، قم بإنشاء سجل افتراضي
    $insert_wc = "INSERT INTO website_content (wc_usr_id, wc_status) VALUES (?, '1')";
    $stmt_insert = mysqli_prepare($con, $insert_wc);
    mysqli_stmt_bind_param($stmt_insert, 'i', $uid);
    mysqli_stmt_execute($stmt_insert);
    $wc_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt_insert);
    
    $row_wc = new stdClass();
    $row_wc->wc_id = $wc_id;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? 'ترتيب الموضوعات'); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/p.css" type="text/css" rel="stylesheet">
    <link href="css/ap_p.css" type="text/css" rel="stylesheet">
    
    <script src="js/jquery.js"></script>
    <script src="js/jquery-ui.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        #test-list {
            list-style: none;
        }
        #test-list li {
            display: block;
        }
        #test-list li, .fid {
            padding: 8px;
            background-color: #ffffff;
            margin-bottom: 8px;
            width: 97%;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            border: 1px solid #e6e6e6;
            background-color: #ffffff;
        }
        #test-list li:hover {
            background: #fef8ea;
            cursor: move;
        }
        #test-list li div.handle {
            cursor: move;
        }
        #test-list li h4, .fid h4 {
            font-size: 12px;
            color: #0C5097;
            margin: 0;
            padding: 0 20px 0 0;
        }
        .mt2 {
            margin-top: 2px;
        }
        .saps {
            border: 1px solid #216ce3;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-family: Arial, Helvetica, sans-serif;
            padding: 5px;
            text-align: center;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            background-color: #4787ed;
            background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#67a0ff), to(#4787ed));
            background: -webkit-linear-gradient(top, #67a0ff, #4787ed);
            background: -moz-linear-gradient(top, #67a0ff, #4787ed);
            background: -ms-linear-gradient(top, #67a0ff, #4787ed);
            background: -o-linear-gradient(top, #67a0ff, #4787ed);
            display: inline-block;
            cursor: pointer;
        }
        .saps:hover {
            border: 1px solid #216ce3;
            text-decoration: none;
            -webkit-box-shadow: 0 5px 5px rgba(0,0,0,0.2);
            -moz-box-shadow: 0 5px 5px rgba(0,0,0,0.2);
            box-shadow: 0 5px 5px rgba(0,0,0,0.2);
        }
        #response {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            display: none;
        }
        .response-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .response-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .handle img {
            float: right;
            margin-left: 5px;
        }
    </style>
</head>
<body>
<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
    <div class="bt">
        <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" width="1" height="1">
    </div>

    <?php include 'includes/header_menu.php'; ?>
    
    <!-- القائمة الجانبية -->
    <?php include 'includes/left_menu.php'; ?>
    
    <!-- المحتوى الرئيسي -->
    <div class="w56 f1 p2b p14 blr" style="width:80%">
        <div>
            <h1 class="f1">ترتيب عرض الموضوعات</h1>
        </div>
        
        <div class="mt5">
            <div style="float:right; width:300px;"></div>
            <div class="c3"></div>
            <div class="aml"><div class="c3"></div></div>
            
            <div class="utab">
                <p class="f1">حرك عناوين الموضوعات لترتيب عرضها بصفحة الشركة</p>
                <a class="f2 fw prf_s" href="myprofile.php">&nbsp;</a>
                <a href="myprofile.php#form_tst1" style="display: block;" class="f2 fw apr1" id="edit_add">
                    أضف موضوعات جديدة
                </a>
            </div>
        </div>
        
        <div class="mt5">
            <div class="c3">&nbsp;</div>
            
            <div id="الموضوعات">
                <div id="response"></div>
                
                <ul class="ui-sortable" id="test-list">
                    <?php
                    // استخدام MySQLi مع prepared statement
                    $sql_about = "SELECT a.abtus_id, a.abtus_ph_id, a.abtus_order, p.ph_title 
                                  FROM about_us a 
                                  JOIN profile_heading_arabyos p ON a.abtus_ph_id = p.ph_id 
                                  WHERE a.abtus_wc_id = ? 
                                  ORDER BY a.abtus_order";
                    
                    $stmt_about = mysqli_prepare($con, $sql_about);
                    mysqli_stmt_bind_param($stmt_about, 'i', $row_wc->wc_id);
                    mysqli_stmt_execute($stmt_about);
                    $result_about = mysqli_stmt_get_result($stmt_about);
                    
                    if (mysqli_num_rows($result_about) > 0):
                        while ($abtrow = mysqli_fetch_object($result_about)):
                    ?>
                    <li id="arrayorder_<?php echo (int)$abtrow->abtus_id; ?>">
                        <div class="handle">
                            <img src="images/arrow1.png" alt="تحريك" class="mt2" align="right" width="10" height="10">
                            <h4><?php echo htmlspecialchars(ucwords($abtrow->ph_title), ENT_QUOTES, 'UTF-8'); ?></h4>
                        </div>
                    </li>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <li style="text-align: center; padding: 20px; background: #f8f9fa;">
                        <p>لا توجد موضوعات مضافة بعد. <a href="myprofile.php#form_tst1">أضف موضوعات جديدة</a></p>
                    </li>
                    <?php
                    endif;
                    mysqli_stmt_close($stmt_about);
                    ?>
                </ul>
            </div>
        </div>
        
        <div class="c3">&nbsp;</div>
    </div>
    
    <!-- تذييل الصفحة -->
    <?php include 'includes/footer.php'; ?>
</div>

<script type="text/javascript">
/**
 * دوال JavaScript - متوافقة مع PHP 8.3
 */

$(document).ready(function() {
    // إخفاء رسالة الاستجابة في البداية
    $("#response").hide();
    
    // دالة لإخفاء الرسالة بعد فترة
    function slideout() {
        setTimeout(function() {
            $("#response").slideUp("slow", function() {});
        }, 3000);
    }
    
    // تفعيل خاصية السحب والترتيب
    $("#list ul, #test-list").sortable({
        opacity: 0.8,
        cursor: 'move',
        placeholder: 'ui-state-highlight',
        tolerance: 'pointer',
        update: function(event, ui) {
            // تجميع البيانات وإرسالها
            var order = $(this).sortable("serialize", {
                key: 'item[]',
                attribute: 'id',
                expression: /arrayorder_(\d+)/
            }) + '&update=update';
            
            // إرسال الطلب
            $.post("updateList.php", order, function(theResponse) {
                // عرض رسالة النجاح
                $("#response")
                    .removeClass('response-error response-success')
                    .addClass('response-success')
                    .html(theResponse || 'تم حفظ الترتيب بنجاح')
                    .slideDown('slow');
                
                slideout();
            }).fail(function(xhr, status, error) {
                // عرض رسالة الخطأ
                $("#response")
                    .removeClass('response-error response-success')
                    .addClass('response-error')
                    .html('حدث خطأ أثناء حفظ الترتيب: ' + error)
                    .slideDown('slow');
                
                slideout();
            });
        }
    }).disableSelection(); // منع تحديد النص أثناء السحب
    
    // إضافة تأثيرات بصرية
    $('#test-list li').hover(
        function() {
            $(this).css('background', '#fef8ea');
        },
        function() {
            $(this).css('background', '#ffffff');
        }
    );
    
    // إضافة مؤشر حركة لكل عنصر
    $('#test-list li .handle').css('cursor', 'move');
});

/**
 * تحديث ترتيب الموضوعات يدوياً
 */
function updateOrder() {
    var items = [];
    $('#test-list li').each(function(index) {
        var id = $(this).attr('id').replace('arrayorder_', '');
        items.push(id);
    });
    
    $.post("updateList.php", {
        items: items,
        update: 'update'
    }, function(response) {
        $("#response")
            .removeClass('response-error response-success')
            .addClass('response-success')
            .html('تم تحديث الترتيب بنجاح')
            .slideDown('slow');
        
        setTimeout(function() {
            $("#response").slideUp('slow');
        }, 3000);
    }).fail(function() {
        $("#response")
            .removeClass('response-error response-success')
            .addClass('response-error')
            .html('حدث خطأ أثناء تحديث الترتيب')
            .slideDown('slow');
        
        setTimeout(function() {
            $("#response").slideUp('slow');
        }, 3000);
    });
}
</script>
</body>
</html>