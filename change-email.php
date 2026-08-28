<?php
/**
 * File: change-email.php

 * Version: PHP 8.3
 * Description: نافذة منبثقة لتغيير البريد الإلكتروني الرئيسي للمستخدم
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود معرف المستخدم في الجلسة
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    die('يجب تسجيل الدخول أولاً');
}

$uid = (int)$_SESSION['uid_indm'];

// تضمين الملفات الأساسية
include "common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// جلب رسالة من الجلسة إذا وجدت
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : '';
unset($_SESSION['msg']);
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغيير البريد الإلكتروني</title>
    <style>
        body {
            background: silver;
            font-family: 'Tahoma', Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        .simplemodal-container {
            height: 100%;
            width: 100%;
            left: 0px;
            top: 33px;
            text-align: center;
            position: fixed;
            z-index: 9999;
        }
        
        .simplemodal-wrap {
            height: 100%;
            outline: 0px none;
            width: 100%;
            overflow: auto;
        }
        
        .overlay {
            background: white;
            border: 2px solid #333;
            border-radius: 10px;
            padding: 20px;
            max-width: 600px;
            margin: 50px auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .modalCloseImg {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            width: 25px;
            height: 25px;
            background: url('images/close.png') no-repeat;
            background-size: contain;
        }
        
        h2 {
            color: #333;
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .mu11 {
            width: 100%;
            padding: 7px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .cmp {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        
        .cmp:hover {
            background-color: #45a049;
        }
        
        .r {
            color: red;
        }
        
        .ma {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        
        .sbox {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .ebox {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .ibox {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
        }
        
        .c3 {
            clear: both;
            height: 1px;
        }
        
        #fbloading {
            background-color: #fff1a8;
            font-weight: bold;
            line-height: 23px;
            width: 28%;
            margin: 10px auto;
            padding: 5px;
            border-radius: 4px;
        }
        
        .loading_m2 {
            vertical-align: middle;
        }
        
        table {
            width: 98%;
            border-collapse: collapse;
        }
        
        td {
            padding: 4px;
        }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    
    <script>
    /**
     * تغيير البريد الإلكتروني
     */
    function changeuemail() {
        var emailad = $('#emailad').val().trim();
        var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        
        // التحقق من صحة المدخلات
        if (emailad == "" || emailad == null) {
            alert("الرجاء إدخال البريد الإلكتروني");
            $('#emailad').focus();
            return;
        }
        
        if (!emailad.match(is_email)) {
            alert("الرجاء إدخال بريد إلكتروني صحيح");
            $('#emailad').focus();
            return;
        }
        
        // إظهار مؤشر التحميل
        $('#fbloading').show();
        
        // إرسال طلب AJAX
        $.ajax({
            type: "POST",
            url: "ajax-file/changeuemail.php",
            data: {emailad: emailad},
            success: function(data) {
                $('#fbloading').hide();
                
                // عرض رسالة النتيجة
                if (data.trim() == 'success') {
                    alert("تم تغيير البريد الإلكتروني بنجاح");
                } else if (data.trim() == 'exists') {
                    alert("البريد الإلكتروني مستخدم بالفعل");
                } else if (data.trim() == 'invalid') {
                    alert("البريد الإلكتروني غير صالح");
                } else {
                    alert(data);
                }
                
                // إعادة تحميل الصفحة بعد التأكيد
                if (confirm("هل تريد تحديث الصفحة الآن؟")) {
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                $('#fbloading').hide();
                alert("حدث خطأ في الاتصال بالخادم: " + error);
            }
        });
    }
    
    /**
     * إغلاق النافذة المنبثقة
     */
   // ✅ منع التكرار اللانهائي
var isClosing = false;

function closePopup() {
    if (isClosing) return;
    isClosing = true;
    
    try {
        // محاولة إغلاق النافذة المنبثقة (إذا كانت SimpleModal)
        if (typeof $.modal !== 'undefined') {
            $.modal.close();
        }
        // إغلاق النافذة إذا كانت منبثقة بـ window.open
        else if (window.opener && !window.opener.closed) {
            window.close();
        }
        // إخفاء العناصر المرئية
        else {
            $('.simplemodal-container, .simplemodal-overlay, #changeEmailModal').hide();
            $('body').css('overflow', 'auto');
        }
    } catch(e) {
        console.log("Error closing popup:", e);
    }
    
    setTimeout(function() {
        isClosing = false;
    }, 200);
}

$(document).ready(function() {
    // إغلاق النافذة عند النقر على زر الإغلاق (بأي كلاس شائع)
    $('.modalCloseImg, .close, .close-btn, .simplemodal-close').click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        closePopup();
    });
    
    // إغلاق النافذة عند الضغط على ESC
    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            e.preventDefault();
            closePopup();
        }
    });
});

function closePopup() {
    // ✅ إغلاق النافذة إذا كانت منبثقة
    if (window.opener && !window.opener.closed) {
        window.close();
    } 
    // ✅ إذا كانت النافذة الحالية هي الصفحة نفسها (وليست منبثقة)
    else {
        // العودة إلى الصفحة السابقة (my-contactdetails.php)
        window.location.href = 'my-contactdetails.php';
    }
}

    </script>
</head>
<body>
    <div class="simplemodal-container" id="simplemodal-container">
        <a class="modalCloseImg simplemodal-close" title="إغلاق"></a>
        
        <div class="simplemodal-wrap" tabindex="-1">
            <div id="Change_Email" class="overlay simplemodal-data">
                
                <div>
                    <h2>تغيير ميل الدخول الرئيسى</h2>
                </div>
                
                <div>&nbsp;</div>
                
                <!-- رسائل التنبيه -->
                <div class="ma sbox c bnr fw lh" id="div_succ1" style="display:none;" align="left">
                    تم تغيير البريد الإلكتروني بنجاح
                </div>
                
                <div class="ma ibox c bnr fw lh" id="div_caut1" style="background-position: 4px -457px; display:none;" align="left">
                    تحذير: البريد الإلكتروني مستخدم بالفعل
                </div>
                
                <div class="ma ebox c bnr fw lh" id="div_error1" style="background-position:-581px -422px; display:none;" align="left">
                    البريد الإلكتروني الجديد مستخدم بالفعل بواسطة مستخدم آخر. يمكنك استخدامه كبريد بديل.
                </div>
                
                <!-- عرض رسالة من الجلسة -->
                <?php if (!empty($msg)): ?>
                    <div class="ma sbox c bnr fw lh" align="left">
                        <?php echo htmlspecialchars($msg); ?>
                    </div>
                <?php endif; ?>
                
                <div align="center">
                    <div id="fbloading" style="display:none;">
                        <img src="http://my.imimg.com/gifs/my2-loading.gif" class="loading_m2" alt="جاري التحميل">
                        &nbsp;جاري التحميل...
                    </div>
                </div>
                
                <div class="c3">&nbsp;</div>
                
                <div class="c3">
                    <table border="0" cellpadding="4" cellspacing="0" width="98%">
                        <tbody>
                            <tr>
                                <td width="25%">&nbsp;</td>
                                <td width="75%">
                                    <div id="messages" class="ma sbox" style="<?php echo empty($msg) ? 'display:none;' : ''; ?>">
                                        <?php echo htmlspecialchars($msg); ?>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td width="50%" style="text-align:right;">
                                    <span class="r">*</span>&nbsp;ضع إيميل جديد هنا:
                                </td>
                                <td width="50%" style="text-align:left;">
                                    <input name="emailad" 
                                           id="emailad" 
                                           value="<?php echo htmlspecialchars(user_info($uid, 'email') ?? ''); ?>" 
                                           class="mu11" 
                                           style="padding:7px;" 
                                           maxlength="100" 
                                           type="text"
                                           placeholder="أدخل بريدك الإلكتروني الجديد">
                                </td>
                            </tr>
                            
                            <tr>
                                <td colspan="2">
                                    <div id="saalt" name="saalt" style="display:none">
                                        <input name="saveasalt" id="saveasalt" checked="checked" type="checkbox">
                                        حفظ البريد الإلكتروني الحالي كبريد بديل
                                    </div>
                                </td>
                            </tr>
                            
                            <tr>
                                <td>&nbsp;</td>
                                <td>
                                    <input id="changeButton" 
                                           name="changeButton" 
                                           value="غير الميل الرئيسي لدخول حسابي" 
                                           class="cmp" 
                                           type="button" 
                                           onclick="changeuemail();">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>