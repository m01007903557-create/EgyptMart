<?php
/**
 * File: admin/cus_description_faq.php
 * Version: PHP 8.3
 * Description: عرض تفاصيل سؤال مخصص في الأسئلة الشائعة
 * 
 * تعرض هذه الصفحة تفاصيل سؤال مخصص من قسم المساعدة
 * بما في ذلك الفئة والعنوان والموضوع والمحتوى
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// التحقق من وجود معرف السؤال
if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
    die('معرف السؤال مطلوب');
}

// تنظيف المدخلات
$id = (int)$_REQUEST['id'];

if ($id <= 0) {
    die('معرف السؤال غير صالح');
}

// جلب تفاصيل السؤال
$sql = mysqli_query($con, "SELECT * FROM custom_faq WHERE cf_id = " . $id);

if (!$sql || mysqli_num_rows($sql) == 0) {
    die('السؤال غير موجود');
}

$row = mysqli_fetch_array($sql);

// جلب معلومات الفئة
$faqcatsql = mysqli_query($con, "SELECT * FROM faq_categories WHERE fc_id = " . (int)$row['cf_fc_id']);

if (!$faqcatsql) {
    $faqcatrow = ['fc_name' => ''];
} else {
    $faqcatrow = mysqli_fetch_array($faqcatsql);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل السؤال - لوحة التحكم</title>
    <link rel="stylesheet" type="text/css" href="style/screen.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/main.css" media="screen, projection">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .control_Panel {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        #content h2 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .x2-layout {
            width: 100%;
            height: auto;
        }
        .formSection {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        .formSectionRow td {
            padding: 10px;
        }
        .formItem {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .formItem label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        .formItem h2 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }
        .formInputBox {
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 20px;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 15px;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
        .content-box {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="control_Panel">
        <div id="content-container">
            <div id="content">
                <h2>تفاصيل السؤال</h2>
                
                <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                    <div class="x2-layout">
                        <div class="formSection showSection">
                            <div class="tableWrapper">
                                <table style="width:100%;">
                                    <tbody>
                                        <tr class="formSectionRow">
                                            <td>
                                                <!-- الفئة -->
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label><strong>الفئة:</strong></label>
                                                    <div class="formInputBox">
                                                        <?php echo htmlspecialchars($faqcatrow['fc_name'] ?? ''); ?>
                                                    </div>
                                                </div>

                                                <!-- العنوان -->
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label><strong>العنوان:</strong></label>
                                                    <div class="formInputBox">
                                                        <?php echo htmlspecialchars($row['cf_heading'] ?? ''); ?>
                                                    </div>
                                                </div>

                                                <!-- الموضوع -->
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label><strong>الموضوع:</strong></label>
                                                    <div class="formInputBox">
                                                        <?php echo htmlspecialchars($row['cf_support'] ?? ''); ?>
                                                    </div>
                                                </div>

                                                <!-- رابط الموضوع -->
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label><strong>رابط الموضوع:</strong></label>
                                                    <div class="formInputBox">
                                                        <?php if (!empty($row['cf_support_link'])): ?>
                                                            <a href="<?php echo htmlspecialchars($row['cf_support_link']); ?>" target="_blank">
                                                                <?php echo htmlspecialchars($row['cf_support_link']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">لا يوجد رابط</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- المحتوى -->
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label><strong>المحتوى:</strong></label>
                                                    <div class="formInputBox content-box">
                                                        <?php echo nl2br(htmlspecialchars($row['cf_content'] ?? '')); ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- زر العودة -->
                    <div style="text-align: center; margin-top: 20px;">
                        <button type="button" class="back-btn" onclick="window.location.href='custom_faq-list.php'">
                            العودة إلى القائمة
                        </button>
                    </div>
                </form>
                
                <br clear="all" />
            </div>
        </div>
    </div>
    <br clear="all" />
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>