<?php
/**
 * File Name: post-auction.php (PHP 8.3)
 * PHP Version: 8.3
 * Description: صفحة نشر المزايدات - نسخة مطورة ومتوافقة مع PHP 8.3
 * 
 * التغييرات الرئيسية:
 * - استبدال mysql_* القديم بـ MySQLi مع prepared statements
 * - إضافة type declarations للخصائص والدوال
 * - استخدام strict typing
 * - تحسين أمان الجلسات والمدخلات
 * - إضافة معالجة آمنة للملفات المرفوعة
 * - تحسين التحقق من صحة البيانات
 * - إضافة DocBlocks للتوثيق
 */

declare(strict_types=1);

require_once "common.php";

// إعدادات أمان الجلسة
//ini_set('session.use_strict_mode', '1');
//ini_set('session.cookie_httponly', '1');
//ini_set('session.cookie_secure', '1');
//ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['last_page'] = 'post-auction.php';

// التحقق من وجود المستخدم في الجلسة
if (empty($_SESSION['uid_indm'] ?? null)) {
    header('Location: sign-in.php');
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// إنشاء اتصال قاعدة بيانات باستخدام MySQLi
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    error_log("فشل الاتصال بقاعدة البيانات: " . $db->connect_error);
    die("عذراً، حدث خطأ في الاتصال بقاعدة البيانات. الرجاء المحاولة لاحقاً.");
}

$db->set_charset('utf8mb4');

/**
 * Class AddAuction
 * كلاس إدارة وإضافة المزايدات الجديدة
 * 
 * @package Auction
 * @version 1.0.0
 */
class AddAuction
{
    private string $msg = '';
    private int $auc_usr_id;
    private string $main_cat = '';
    private int $pc_id = 0;
    private int $auc_pc_id = 0;
    private string $auc_heading = '';
    private float $auc_value = 0.0;
    private int $auc_currency = 0;
    private string $auc_prequalification_criteria = '';
    private string $auc_details = '';
    private string $auc_preferred_location = '';
    private string $so_validity = '';
    private ?string $so_pic = null;
    private mysqli $db;
    private array $badWords = [];

    /**
     * @param mysqli $db اتصال قاعدة البيانات
     * @param int $auc_usr_id معرف المستخدم
     * @param string $main_cat التصنيف العام
     * @param int $pc_id التصنيف الرئيسي
     * @param int $auc_pc_id التصنيف الفرعي
     * @param string $auc_heading عنوان المزايدة
     * @param float $auc_value قيمة المزايدة
     * @param int $auc_currency عملة المزايدة
     * @param string $auc_prequalification_criteria معايير التأهل
     * @param string $auc_details تفاصيل المزايدة
     * @param string $auc_preferred_location الموقع المفضل
     * @param string $so_validity صلاحية العرض
     */
    public function __construct(
        mysqli $db,
        int $auc_usr_id,
        string $main_cat,
        int $pc_id,
        int $auc_pc_id,
        string $auc_heading,
        float $auc_value,
        int $auc_currency,
        string $auc_prequalification_criteria,
        string $auc_details,
        string $auc_preferred_location,
        string $so_validity
    ) {
        $this->db = $db;
        $this->auc_usr_id = $auc_usr_id;
        $this->main_cat = $this->sanitizeInput($main_cat);
        $this->pc_id = $pc_id;
        $this->auc_pc_id = $auc_pc_id;
        $this->auc_heading = $this->sanitizeInput($auc_heading);
        $this->auc_value = $auc_value;
        $this->auc_currency = $auc_currency;
        $this->auc_prequalification_criteria = $this->sanitizeInput($auc_prequalification_criteria);
        $this->auc_details = $this->sanitizeInput($auc_details);
        $this->auc_preferred_location = $this->sanitizeInput($auc_preferred_location);
        $this->so_validity = $this->sanitizeInput($so_validity);

        // تخزين في الجلسة للاحتفاظ بالبيانات في حالة وجود أخطاء
        $_SESSION['main_cat'] = $this->main_cat;
        $_SESSION['pc_id'] = $this->pc_id;
        $_SESSION['auc_pc_id'] = $this->auc_pc_id;
        $_SESSION['auc_heading'] = $this->auc_heading;
        $_SESSION['auc_value'] = $this->auc_value;
        $_SESSION['auc_currency'] = $this->auc_currency;
        $_SESSION['auc_prequalification_criteria'] = $this->auc_prequalification_criteria;
        $_SESSION['auc_details'] = $this->auc_details;
        $_SESSION['auc_preferred_location'] = $this->auc_preferred_location;
        $_SESSION['so_validity'] = $this->so_validity;

        $this->loadBadWords();
    }

    /**
     * تنظيف المدخلات النصية من الأحرف الخاصة
     * 
     * @param string $input النص المراد تنظيفه
     * @return string النص بعد التنظيف
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * تحميل قائمة الكلمات الممنوعة من قاعدة البيانات
     */
    private function loadBadWords(): void
    {
        $stmt = $this->db->prepare("SELECT bd_word FROM bad_word WHERE bd_word IS NOT NULL");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['bd_word'])) {
                    $this->badWords[] = strtoupper($row['bd_word']);
                }
            }
            $stmt->close();
        }
    }

    /**
     * التحقق من وجود كلمات ممنوعة في النص
     * 
     * @param string $param النص المراد فحصه
     * @return bool true إذا كان النص نظيفاً، false إذا وجدت كلمات ممنوعة
     */
    private function checkBadWord(string $param): bool
    {
        if (empty($this->badWords)) {
            return true;
        }

        $upperParam = strtoupper($param);
        foreach ($this->badWords as $word) {
            if (str_contains($upperParam, $word)) {
                return false;
            }
        }
        return true;
    }

    /**
     * التحقق من صحة جميع البيانات المدخلة
     * 
     * @return bool true إذا كانت البيانات صحيحة، false إذا وجد خطأ
     */
    public function isValid(): bool
    {
        if (empty($this->main_cat)) {
            $this->msg = '<font color="#FF0000">من فضلك اختار تصنيف عام</font>';
            return false;
        }

        if ($this->pc_id === 0) {
            $this->msg = '<font color="#FF0000">من فضلك اختار تصنيف رئيسى</font>';
            return false;
        }

        if ($this->auc_pc_id === 0) {
            $this->msg = '<font color="#FF0000">من فضلك اختار تصنيف فرعى</font>';
            return false;
        }

        if (empty($this->auc_heading)) {
            $this->msg = '<font color="#FF0000">من فضل أدخل عنوان للمزايدة</font>';
            return false;
        }

        if (!$this->checkBadWord(strtoupper($this->auc_heading))) {
            $this->msg = '<font color="#FF0000">لايمكنك إدخال كلمات مسيئة</font>';
            return false;
        }

        if ($this->auc_value <= 0) {
            $this->msg = '<font color="#FF0000">من فضلك أدخل قيمة صحيحة للمزايدة</font>';
            return false;
        }

        if (empty($this->auc_prequalification_criteria)) {
            $this->msg = '<font color="#FF0000">من فضلك أدخل مؤهلات التقدم للمزايدة</font>';
            return false;
        }

        if (!$this->checkBadWord(strtoupper($this->auc_prequalification_criteria))) {
            $this->msg = '<font color="#FF0000">لا يمكن إدخال كلمات مسيئة في مؤهلات التقدم</font>';
            return false;
        }

        if (empty($this->auc_details)) {
            $this->msg = '<font color="#FF0000">من فضلك أدخل تفاصيل المزايدة</font>';
            return false;
        }

        return true;
    }

    /**
     * رفع ومعالجة الصور المرفوعة
     * 
     * @param array $file بيانات الملف المرفوع من $_FILES
     * @return string|null اسم الملف الجديد أو null في حالة الفشل
     */
    private function uploadImage(array $file): ?string
    {
        if (empty($file['name'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'حجم الملف كبير جداً',
                UPLOAD_ERR_FORM_SIZE => 'حجم الملف كبير جداً',
                UPLOAD_ERR_PARTIAL => 'تم رفع جزء من الملف فقط',
                UPLOAD_ERR_NO_FILE => 'لم يتم رفع أي ملف',
                UPLOAD_ERR_NO_TMP_DIR => 'مجلد مؤقت غير موجود',
                UPLOAD_ERR_CANT_WRITE => 'فشل في كتابة الملف',
                UPLOAD_ERR_EXTENSION => 'امتداد الملف غير مسموح'
            ];
            $this->msg = '<font color="#FF0000">' . ($errorMessages[$file['error']] ?? 'خطأ غير معروف في رفع الملف') . '</font>';
            return null;
        }

        // التحقق من نوع الملف
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $this->msg = '<font color="#FF0000">نوع الملف غير مسموح به. المسموح: JPG, PNG, GIF, WEBP</font>';
            return null;
        }

        // التحقق من حجم الملف (5MB كحد أقصى)
        $maxSize = 5 * 1024 * 1024; // 5 ميجابايت
        if ($file['size'] > $maxSize) {
            $this->msg = '<font color="#FF0000">حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت</font>';
            return null;
        }

        // إنشاء اسم ملف آمن وفريد
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = sprintf(
            'auc-%s-%s.%s',
            date('Ymd'),
            bin2hex(random_bytes(8)),
            $extension
        );

        $uploadDir = __DIR__ . '/upload/auction/';
        
        // إنشاء المجلد إذا لم يكن موجوداً
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            $this->msg = '<font color="#FF0000">فشل في إنشاء مجلد الرفع</font>';
            return null;
        }

        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $newFileName;
        }

        $this->msg = '<font color="#FF0000">فشل في رفع الملف. الرجاء المحاولة مرة أخرى</font>';
        return null;
    }

    /**
     * إضافة المزايدة إلى قاعدة البيانات
     * 
     * @param array $files بيانات الملفات المرفوعة من $_FILES
     * @return bool true إذا تمت الإضافة بنجاح، false في حالة الفشل
     */
    public function add(array $files): bool
    {
        if (!empty($files['so_pic']['name'] ?? null)) {
            $this->so_pic = $this->uploadImage($files['so_pic']);
            
            if ($this->so_pic === null) {
                return false;
            }

            $sql = "INSERT INTO auction SET
                    auc_usr_id = ?,
                    auc_pc_id = ?,
                    auc_heading = ?,
                    auc_value = ?,
                    auc_currency = ?,
                    auc_prequalification_criteria = ?,
                    auc_details = ?,
                    auc_preferred_location = ?,
                    so_validity = ?,
                    so_pic = ?,
                    so_approval_status = '0',
                    so_posting_date = NOW(),
                    so_updated_date = NOW()";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("خطأ في تحضير الاستعلام: " . $this->db->error);
                $this->msg = '<font color="#FF0000">حدث خطأ في النظام</font>';
                return false;
            }

            $stmt->bind_param(
                'iisdisssss',
                $this->auc_usr_id,
                $this->auc_pc_id,
                $this->auc_heading,
                $this->auc_value,
                $this->auc_currency,
                $this->auc_prequalification_criteria,
                $this->auc_details,
                $this->auc_preferred_location,
                $this->so_validity,
                $this->so_pic
            );
        } else {
            $sql = "INSERT INTO auction SET
                    auc_usr_id = ?,
                    auc_pc_id = ?,
                    auc_heading = ?,
                    auc_value = ?,
                    auc_currency = ?,
                    auc_prequalification_criteria = ?,
                    auc_details = ?,
                    auc_preferred_location = ?,
                    so_validity = ?,
                    auc_updated_date = NOW()";

            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("خطأ في تحضير الاستعلام: " . $this->db->error);
                $this->msg = '<font color="#FF0000">حدث خطأ في النظام</font>';
                return false;
            }

            $stmt->bind_param(
                'iisdissss',
                $this->auc_usr_id,
                $this->auc_pc_id,
                $this->auc_heading,
                $this->auc_value,
                $this->auc_currency,
                $this->auc_prequalification_criteria,
                $this->auc_details,
                $this->auc_preferred_location,
                $this->so_validity
            );
        }

        if ($stmt->execute()) {
            $this->clearSessionData();
            $this->msg = '<font color="#009900">تم نشر المزايدة بنجاح</font>';
            $stmt->close();
            return true;
        }

        error_log("خطأ في تنفيذ الاستعلام: " . $stmt->error);
        $this->msg = '<font color="#FF0000">حدث خطأ أثناء حفظ البيانات</font>';
        $stmt->close();
        return false;
    }

    /**
     * مسح بيانات الجلسة بعد النشر الناجح
     */
    private function clearSessionData(): void
    {
        $sessionKeys = [
            'main_cat', 'pc_id', 'auc_pc_id', 'auc_heading', 
            'auc_value', 'auc_currency', 'auc_prequalification_criteria', 
            'auc_details', 'auc_preferred_location', 'so_validity'
        ];

        foreach ($sessionKeys as $key) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * الحصول على رسالة الخطأ/النجاح
     * 
     * @return string الرسالة
     */
    public function getMessage(): string
    {
        return $this->msg;
    }
}

// معالجة الرسالة المخزنة في الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// معالجة النموذج عند الإرسال
if (isset($_POST['submitAuction']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // تنظيف وتأمين المدخلات
        $auc_usr_id = filter_input(INPUT_POST, 'auc_usr_id', FILTER_VALIDATE_INT);
        $main_cat = trim($_POST['main_cat'] ?? '');
        $pc_id = filter_input(INPUT_POST, 'pc_id', FILTER_VALIDATE_INT) ?: 0;
        $auc_pc_id = filter_input(INPUT_POST, 'auc_pc_id', FILTER_VALIDATE_INT) ?: 0;
        $auc_heading = trim($_POST['auc_heading'] ?? '');
        $auc_value = filter_input(INPUT_POST, 'auc_value', FILTER_VALIDATE_FLOAT) ?: 0.0;
        $auc_currency = filter_input(INPUT_POST, 'auc_currency', FILTER_VALIDATE_INT) ?: 0;
        $auc_prequalification_criteria = trim($_POST['auc_prequalification_criteria'] ?? '');
        $auc_details = trim($_POST['auc_details'] ?? '');
        $auc_preferred_location = trim($_POST['auc_preferred_location'] ?? 'any');
        $so_validity = trim($_POST['so_validity'] ?? '');

        // التحقق من صحة معرف المستخدم
        if ($auc_usr_id !== $uid) {
            header('Location: sign-in.php');
            exit;
        }

        $auction = new AddAuction(
            $db,
            $auc_usr_id,
            $main_cat,
            $pc_id,
            $auc_pc_id,
            $auc_heading,
            $auc_value,
            $auc_currency,
            $auc_prequalification_criteria,
            $auc_details,
            $auc_preferred_location,
            $so_validity
        );

        if ($auction->isValid()) {
            if ($auction->add($_FILES)) {
                $_SESSION['msg'] = $auction->getMessage();
                header('Location: post-auction-res.php');
                exit;
            } else {
                $_SESSION['msg'] = $auction->getMessage();
                header('Location: post-auction.php');
                exit;
            }
        } else {
            $_SESSION['msg'] = $auction->getMessage();
            header('Location: post-auction.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("خطأ في معالجة المزايدة: " . $e->getMessage());
        $_SESSION['msg'] = '<font color="#FF0000">حدث خطأ غير متوقع</font>';
        header('Location: post-auction.php');
        exit;
    }
}

// استرجاع قيم التصنيفات من الجلسة
$main_cat = $_SESSION['main_cat'] ?? '';
$pc_id = $_SESSION['pc_id'] ?? 0;
$auc_pc_id = $_SESSION['auc_pc_id'] ?? 0;
$auc_heading = $_SESSION['auc_heading'] ?? '';
$auc_value = $_SESSION['auc_value'] ?? '';
$auc_currency = $_SESSION['auc_currency'] ?? '';
$auc_prequalification_criteria = $_SESSION['auc_prequalification_criteria'] ?? '';
$auc_details = $_SESSION['auc_details'] ?? '';
$auc_preferred_location = $_SESSION['auc_preferred_location'] ?? 'any';
$so_validity = $_SESSION['so_validity'] ?? '';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? 'نشر مزايدة جديدة'); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    
    <!-- CSS Files -->
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/eto-post-sell.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1.css" type="text/css" rel="stylesheet">
    <link href="css/c.css" type="text/css" rel="stylesheet">
    <link href="css/jquery.css" type="text/css" rel="stylesheet">
    <link href="css/ui.css" rel="stylesheet">
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
    <link href="css/dir-new.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css">
    <link rel="stylesheet" type="text/css" media="screen" href="datepicker/datePicker.css">
    <link rel="stylesheet" type="text/css" media="screen" href="datepicker/demo.css">

    <!-- JavaScript Files -->
    <script src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.autocomplete.js"></script>
    <script src="datepicker/date.js"></script>
    <script src="datepicker/jquery.datePicker.js"></script>
</head>
<body>
<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>

    <div class="bt">
        <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" width="1" height="1">
    </div>

    <?php include "includes/header_menu.php"; ?>

    <!-- القائمة الجانبية -->
    <div class="f1 w61n tb lh ml m2" id="lnav" style="display: block; text-align:center;">
        <ul class="nln1" style="margin: 0px; padding: 0px;">
            <li><h2>العروض التجارية</h2></li>
            <li style="border-bottom: medium none;"><h3>طلبات الشراء</h3></li>
            <li class="lp"><a href="post-buy-req.php">»&nbsp;أنشر طلب شراء</a></li>
            <li class="lp"><a href="manage-buy-requirement.php">»&nbsp;إدارة طلبات الشراء</a></li>

            <li style="border-bottom: medium none;"><h3>عروض البيع</h3></li>
            <li class="lp"><a href="post-sell-offer.php">»&nbsp;أنشر عرض بيع جديد</a></li>
            <li class="lp"><a href="manage-sell-offer.php">»&nbsp;إدارة عروض البيع</a></li>

            <li style="border-bottom: medium none;"><h3>المزايدات</h3></li>
            <li class="lp"><a href="post-auction.php">»&nbsp;أنشر مزايدة جديدة</a></li>
            <li class="lp"><a href="manage-auctions.php">»&nbsp;إدارة المزايدات</a></li>

            <li style="border-bottom: medium none; margin-top: 40px;"><h2>ربما أيضا تريد</h2></li>
            <li class="np"><a href="buyleads.php">آخر طلبات الشراء</a></li>
            <li class="np"><a href="sale-offers.php">آخر عروض البيع</a></li>
            <li class="np"><a href="auctions.php">آخر المزايدات</a></li>
            <li class="np"><a href="manage-purchased-buyleads.php">طلبات الشراء المشتراة</a></li>
            <li class="np"><a href="manage-buylead-alert.php">إدارة إشعارات طلبات الشراء</a></li>
        </ul>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="w57 b1_m2 f1 wd797" id="ldiv">
        <input type="hidden" value="0" id="typeofselection">
        
        <div id="div2" style="display:block;">
            <div><img src="images/zero.gif" width="1" height="19"></div>
            
            <table width="100%" align="center">
                <tr>
                    <td>
                        <div align="left">
                            <div class="tw2l fl" id="formmain" style="margin-left:8px;background-color:#FAF4FF">
                                <div class="" id="lgn1">
                                    <p class="c-1 g2 fs bo1">
                                        أنشر مزايدة مجانا
                                        <span class="p6 q4 tm1 cbc fsz1">
                                            <i class="co">*</i> معلومات مطلوبة
                                        </span>
                                    </p>
                                </div>
                                <br><br>
                                
                                <div>
                                    <script type="text/javascript">
                                    function searchcat() {
                                        $("#scs").removeClass("tabclose").addClass("tabopen");
                                        $("#bcs").removeClass("tabopen").addClass("tabclose");
                                        $('#typeofselection').val(1);
                                        $(".bcc").css("display", "none");
                                        $(".scc").removeAttr('style');
                                    }
                                    
                                    function beowswcat() {
                                        $("#bcs").removeClass("tabclose").addClass("tabopen");
                                        $("#scs").removeClass("tabopen").addClass("tabclose");
                                        $('#typeofselection').val(0);
                                        $(".scc").css("display", "none");
                                        $(".bcc").removeAttr('style');
                                    }
                                    </script>

                                    <form method="post" name="postForm1" action="" onsubmit="return validAuction();" enctype="multipart/form-data">
                                        <div id="error_msg" style="color: #FF0000; margin-bottom: 10px;">
                                            <?php echo $msg; ?>
                                        </div>

                                        <input type="hidden" id="auc_usr_id" name="auc_usr_id" value="<?php echo (int)$_SESSION['uid_indm']; ?>">

                                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                                            <tr>
                                                <td>
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td class="tabclose" onclick="searchcat()" id="scs" width="152">بحث التصنيفات</td>
                                                            <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
                                                            <td class="tabopen" onclick="beowswcat()" id="bcs" width="155">تصفح التصنيفات</td>
                                                            <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <table class="frm mt5" width="100%">
                                            <!-- صف البحث عن التصنيفات -->
                                            <tr class="scc" id="r0" style="display: none;">
                                                <td valign="middle" width="30%">
                                                    <p class="pd15">
                                                        <b style="font-size:13px;"><font color="#E95801">Enter product keywords to find a category</font></b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input class="txt ui-placeholder-input ui-autocomplete-input" 
                                                           name="keywordsFilter1" 
                                                           id="keywordsFilter1" 
                                                           style="width: 450px; float: left; height:30px; border: 1px solid #ff8a8a;" 
                                                           type="text" 
                                                           maxlength="60" 
                                                           size="33">
                                                </td>
                                            </tr>

                                            <!-- التصنيف العام -->
                                            <tr id="r0" style="height: 48px;" class="bcc">
                                                <td valign="middle" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>التصنيف العام:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <select class="bd4 hw6 mr3 htb" id="main_cat" name="main_cat" style="height:30px;" onchange="showCategory()">
                                                        <option value="">--إختار تصنيف عام--</option>
                                                        <?php
                                                        $stmt = $db->prepare("SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = '0' AND pc_status = '1'");
                                                        if ($stmt) {
                                                            $stmt->execute();
                                                            $result = $stmt->get_result();
                                                            while ($row = $result->fetch_assoc()) {
                                                                $selected = ((string)$row['pc_id'] === (string)$main_cat) ? 'selected="selected"' : '';
                                                                echo '<option value="' . (int)$row['pc_id'] . '" ' . $selected . '>' . 
                                                                     htmlspecialchars($row['pc_name']) . '</option>';
                                                            }
                                                            $stmt->close();
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>

                                            <!-- التصنيف الرئيسي والفرعي -->
                                            <tr id="r1" style="height: 48px;" class="bcc">
                                                <td valign="middle" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>التصنيف الرئيسى:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <select class="bd4 hw6 mr3 htb" id="pc_id" name="pc_id" style="height:30px;" onchange="showSubcat()">
                                                        <option value="">--إختار تصنيف رئيسى--</option>
                                                        <?php
                                                        if (!empty($main_cat)) {
                                                            $stmt = $db->prepare("SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = ? AND pc_status = '1'");
                                                            if ($stmt) {
                                                                $stmt->bind_param('i', $main_cat);
                                                                $stmt->execute();
                                                                $result = $stmt->get_result();
                                                                while ($row = $result->fetch_assoc()) {
                                                                    $selected = ($row['pc_id'] == $pc_id) ? 'selected="selected"' : '';
                                                                    echo '<option value="' . (int)$row['pc_id'] . '" ' . $selected . '>' . 
                                                                         htmlspecialchars($row['pc_name']) . '</option>';
                                                                }
                                                                $stmt->close();
                                                            }
                                                        }
                                                        ?>
                                                    </select>

                                                    <select class="bd4 hw6 mr3 htb" id="auc_pc_id" name="auc_pc_id" style="height:30px;" onchange="showAuctionAdditionalFields();">
                                                        <option value="">--إختار تصنيف فرعى--</option>
                                                        <?php
                                                        if (!empty($pc_id)) {
                                                            $stmt = $db->prepare("SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = ? AND pc_status = '1'");
                                                            if ($stmt) {
                                                                $stmt->bind_param('i', $pc_id);
                                                                $stmt->execute();
                                                                $result = $stmt->get_result();
                                                                while ($row = $result->fetch_assoc()) {
                                                                    $selected = ($row['pc_id'] == $auc_pc_id) ? 'selected="selected"' : '';
                                                                    echo '<option value="' . (int)$row['pc_id'] . '" ' . $selected . '>' . 
                                                                         htmlspecialchars($row['pc_name']) . '</option>';
                                                                }
                                                                $stmt->close();
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>

                                            <!-- عنوان المزايدة -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>عنوان المزايدة:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_heading" 
                                                           id="auc_heading" 
                                                           style="width:450px;" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           maxlength="90" 
                                                           value="<?php echo htmlspecialchars($auc_heading); ?>">
                                                </td>
                                            </tr>

                                            <!-- قيمة المزايدة والعملة -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <b>قيمة المزايدة:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_value" 
                                                           id="auc_value" 
                                                           style="width:280px;" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           maxlength="90" 
                                                           value="<?php echo htmlspecialchars((string)$auc_value); ?>">
                                                    <select name="auc_currency" id="auc_currency" class="a_f s_u">
                                                        <option value="">-إختار العملة-</option>
                                                        <?php
                                                        $stmt = $db->prepare("SELECT cn_id, cn_currency FROM country WHERE cn_status = '1'");
                                                        if ($stmt) {
                                                            $stmt->execute();
                                                            $result = $stmt->get_result();
                                                            $userCountry = user_info($uid, 'country');
                                                            while ($row = $result->fetch_assoc()) {
                                                                $selected = ($row['cn_id'] == ($auc_currency ?: $userCountry)) ? 'selected="selected"' : '';
                                                                echo '<option value="' . (int)$row['cn_id'] . '" ' . $selected . '>' . 
                                                                     htmlspecialchars($row['cn_currency']) . '</option>';
                                                            }
                                                            $stmt->close();
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>

                                            <!-- نوع العطاء -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>نوع العطاء :</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_notice_type" 
                                                           id="auc_notice_type" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           style="width:200px;height:18px;">
                                                </td>
                                            </tr>

                                            <!-- الكمية -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <b>الكمية:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_qty" 
                                                           id="auc_qty" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           style="width:75px;height:18px;">
                                                    <select name="auc_qty_mu_id" id="auc_qty_mu_id" class="a_f s_u">
                                                        <option value="">-وحدة القياس-</option>
                                                        <?php
                                                        $stmt = $db->prepare("SELECT mu_id, mu_name FROM measurement_unit WHERE mu_status = '1'");
                                                        if ($stmt) {
                                                            $stmt->execute();
                                                            $result = $stmt->get_result();
                                                            while ($row = $result->fetch_assoc()) {
                                                                echo '<option value="' . (int)$row['mu_id'] . '">' . 
                                                                     htmlspecialchars($row['mu_name']) . '</option>';
                                                            }
                                                            $stmt->close();
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>

                                            <!-- قيمة التأمين -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <b>قيمة التأمين:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_emd" 
                                                           id="auc_emd" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           style="width:200px;height:18px;">
                                                </td>
                                            </tr>

                                            <!-- رسوم الأوراق -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>رسوم الأوراق:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_document_fees" 
                                                           id="auc_document_fees" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           style="width:200px;height:18px;">
                                                    <select name="auc_document_fees_currency" id="auc_document_fees_currency" class="a_f s_u">
                                                        <option value="">-إختر العملة-</option>
                                                        <?php
                                                        $stmt = $db->prepare("SELECT cn_id, cn_currency FROM country WHERE cn_status = '1'");
                                                        if ($stmt) {
                                                            $stmt->execute();
                                                            $result = $stmt->get_result();
                                                            $userCountry = user_info($uid, 'country');
                                                            while ($row = $result->fetch_assoc()) {
                                                                $selected = ($row['cn_id'] == $userCountry) ? 'selected="selected"' : '';
                                                                echo '<option value="' . (int)$row['cn_id'] . '" ' . $selected . '>' . 
                                                                     htmlspecialchars($row['cn_currency']) . '</option>';
                                                            }
                                                            $stmt->close();
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>

                                            <!-- مدة المشروع -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <b>مدة مشروع المزايدة:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_project_period" 
                                                           id="auc_project_period" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           style="width:200px;height:18px;">
                                                </td>
                                            </tr>

                                            <!-- المنتجات/الخدمات -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <b>منتجات أو خدمات المزايدة :</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_products" 
                                                           id="auc_products" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           style="width:375px;height:18px;">
                                                </td>
                                            </tr>

                                            <!-- التواريخ -->
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>تاريخ نشر المزايدة:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_publish_date" 
                                                           id="auc_publish_date" 
                                                           class="date-pick dp-applied bd4 hw6 mr3 htb" 
                                                           style="width:75px;height:18px;" 
                                                           readonly="readonly">
                                                </td>
                                            </tr>

                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>تاريخ بيع الأوراق:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_docSaleStart_date" 
                                                           id="auc_docSaleStart_date" 
                                                           class="date-pick dp-applied bd4 hw6 mr3 htb" 
                                                           style="width:75px;height:18px;" 
                                                           readonly="readonly">
                                                </td>
                                            </tr>

                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>تاريخ انتهاء بيع الأوراق:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_docSaleEnd_date" 
                                                           id="auc_docSaleEnd_date" 
                                                           class="date-pick dp-applied bd4 hw6 mr3 htb" 
                                                           style="width:75px;height:18px;" 
                                                           readonly="readonly">
                                                </td>
                                            </tr>

                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>تقديم العطاء قبل تاريخ:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_docSubmitBefore_date" 
                                                           id="auc_docSubmitBefore_date" 
                                                           class="date-pick dp-applied bd4 hw6 mr3 htb" 
                                                           style="width:75px;height:18px;" 
                                                           readonly="readonly">
                                                </td>
                                            </tr>

                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>تاريخ المزايدة:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="auc_due_date" 
                                                           id="auc_due_date" 
                                                           class="date-pick dp-applied bd4 hw6 mr3 htb" 
                                                           style="width:75px;height:18px;" 
                                                           readonly="readonly">
                                                </td>
                                            </tr>

                                            <!-- معايير التأهل -->
                                            <tr id="r3">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>مؤهلات التقدم للمزايدة:</b>
                                                        <br>
                                                        <font class="co1" id="Charcount" color="#ff8000">2000</font>
                                                        <b class="fwn cbc">الكلمات المتبقية</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <div id="lgn6" style="width: 360px; height: 105px;">
                                                        <textarea name="auc_prequalification_criteria" 
                                                                  id="auc_prequalification_criteria" 
                                                                  style="max-width: 4500px; width:450px; height:95px; max-height:95px; display: block;" 
                                                                  rows="5" 
                                                                  cols="30"><?php echo htmlspecialchars($auc_prequalification_criteria); ?></textarea>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- تفاصيل المزايدة -->
                                            <tr id="r3">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15">
                                                        <i>*</i><b>تفاصيل المزايدة:</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <div id="lgn6" style="width: 360px; height: 105px;">
                                                        <textarea name="auc_details" 
                                                                  id="auc_details" 
                                                                  style="max-width: 4500px; width:450px; height:95px; max-height:95px; display: block;" 
                                                                  rows="5" 
                                                                  cols="30"><?php echo htmlspecialchars($auc_details); ?></textarea>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- الموقع المفضل -->
                                            <tr id="r4">
                                                <td valign="TOP" width="30%">
                                                    <p class="pd15"><b>مكان نشر المزايدة:</b></p>
                                                </td>
                                                <td valign="TOP">
                                                    <div style="vertical-align:bottom">
                                                        <input type="radio" id="auc_preferred_location_1" name="auc_preferred_location" value="abroad" <?php echo ($auc_preferred_location === 'abroad') ? 'checked="checked"' : ''; ?>>
                                                        <label style="top:0px;">خارج بلدى</label>
                                                        &nbsp;&nbsp;
                                                        <input type="radio" id="auc_preferred_location_2" name="auc_preferred_location" value="any" <?php echo ($auc_preferred_location === 'any') ? 'checked="checked"' : ''; ?>>
                                                        <label style="top:0px;">خارج وداخل بلدى</label>
                                                        &nbsp;&nbsp;
                                                        <input type="radio" id="auc_preferred_location_3" name="auc_preferred_location" value="domestic" <?php echo ($auc_preferred_location === 'domestic') ? 'checked="checked"' : ''; ?>>
                                                        <label style="top:0px;">فى بلدى فقط</label>
                                                        &nbsp;&nbsp;
                                                        <input type="radio" id="auc_preferred_location_4" name="auc_preferred_location" value="my_city" <?php echo ($auc_preferred_location === 'my_city') ? 'checked="checked"' : ''; ?>>
                                                        <label style="top:0px;">فى مدينتى</label>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                                        </table>

                                        <!-- الحقول الإضافية -->
                                        <table id="additional" class="frm mt5" width="100%"></table>
                                        <input type="hidden" id="additional_field_ids">
                                        <input type="hidden" id="additional_field_types">

                                        <!-- زر الإرسال -->
                                        <div class="a2 pt pb" id="loginsubmit" style="display: block;">
                                            <input type="hidden" name="frmsubmitbutton" value="login">
                                            <input type="hidden" name="submitAuction" value="1">
                                            <input type="button" 
                                                   id="login_frm1" 
                                                   class="cr bo1 fsz1" 
                                                   style="height: 32px; width: 170px; cursor: pointer;" 
                                                   value="إرسل المزايدة للنشر" 
                                                   onclick="validAuction();">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div style="clear:both;">
        <br><br>&nbsp;&nbsp;
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script type="text/javascript">
$(document).ready(function() {
    // تهيئة البحث التلقائي
    $('#keywordsFilter1').keyup(function() {
        var type11 = 'Products';
        $(this).autocomplete("autocomplete.php", {
            selectFirst: true,
            extraParams: {type: type11},
            width: 407
        }).result(function(event, data, formatted) {
            $("#keywordsFilter1").val(data);
        });
    });

    // تهيئة منتقي التاريخ
    $('#auc_publish_date, #auc_docSaleStart_date, #auc_docSaleEnd_date, #auc_docSubmitBefore_date, #auc_due_date')
        .datePicker()
        .val(new Date().asString())
        .trigger('change');

    // تهيئة عداد الكلمات المتبقية
    $('#auc_prequalification_criteria, #auc_details').keyup(function() {
        var remaining = 2000 - $(this).val().length;
        if (remaining < 0) remaining = 0;
        $('#Charcount').text(remaining);
    });
});

function showCategory() {
    var pc_id = $('#main_cat').val();
    $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
        $('#pc_id').html(data);
        showSubcat();
    });
}

function showSubcat() {
    var id = $('#pc_id').val();
    $.post("ajax-file/showSubcat.php", {id: id}, function(data) {
        $('#auc_pc_id').html(data);
    });
}

function showAuctionAdditionalFields() {
    var id = $('#auc_pc_id').val();
    $.post("showAuctionAdditionalFields.php", {id: id}, function(data) {
        data = data.trim();
        var dt = data.split("|");
        $('#additional').html(dt[0]);
        $('#additional_field_ids').val(dt[1]);
        $('#additional_field_types').val(dt[2]);
    });
}

function validAuction() {
    var typeofselection = $('#typeofselection').val();
    var keywordsFilter1 = $('#keywordsFilter1').val();
    var main_cat = $('#main_cat').val();
    var pc_id = $('#pc_id').val();
    var auc_pc_id = $('#auc_pc_id').val();
    var auc_heading = $('#auc_heading').val();
    var auc_value = $('#auc_value').val();
    var auc_currency = $('#auc_currency').val();
    var auc_notice_type = $('#auc_notice_type').val();
    var auc_qty = $('#auc_qty').val();
    var auc_qty_mu_id = $('#auc_qty_mu_id').val();
    var auc_emd = $('#auc_emd').val();
    var auc_document_fees = $('#auc_document_fees').val();
    var auc_document_fees_currency = $('#auc_document_fees_currency').val();
    var auc_project_period = $('#auc_project_period').val();
    var auc_products = $('#auc_products').val();
    var auc_publish_date = $('#auc_publish_date').val();
    var auc_docSaleStart_date = $('#auc_docSaleStart_date').val();
    var auc_docSaleEnd_date = $('#auc_docSaleEnd_date').val();
    var auc_docSubmitBefore_date = $('#auc_docSubmitBefore_date').val();
    var auc_due_date = $('#auc_due_date').val();
    var auc_prequalification_criteria = $('#auc_prequalification_criteria').val();
    var auc_details = $('#auc_details').val();
    var auc_preferred_location = $('input:radio[name=auc_preferred_location]:checked').val();

    // جمع الحقول الإضافية
    var fld_ids = ($('#additional_field_ids').val() || '').split(",");
    var fld_types = ($('#additional_field_types').val() || '').split(",");
    var af_id = "";
    var afv_val = "";
    var j = -1;

    for (var i = 0; i < fld_ids.length; i++) {
        if (fld_ids[i] === '') continue;

        if (fld_types[i] == "checkbox") {
            var chkval = $('input[name="chk-' + fld_ids[i] + '[]"]:checked').map(function() {
                return $(this).val();
            }).get().join("-");
            
            if (chkval !== '') {
                j++;
                if (j > 0) {
                    af_id += "|";
                    afv_val += "|";
                }
                af_id += fld_ids[i];
                afv_val += chkval;
            }
        } else if (fld_types[i] == "radio") {
            var radioval = $('input[name="radio-' + fld_ids[i] + '"]:checked').val();
            if (radioval) {
                j++;
                if (j > 0) {
                    af_id += "|";
                    afv_val += "|";
                }
                af_id += fld_ids[i];
                afv_val += radioval;
            }
        } else if (fld_types[i] == "select" && $('#' + fld_ids[i]).val()) {
            j++;
            if (j > 0) {
                af_id += "|";
                afv_val += "|";
            }
            af_id += fld_ids[i];
            afv_val += $('#' + fld_ids[i]).val();
        } else if ((fld_types[i] == "text" || fld_types[i] == "textarea") && $('#' + fld_ids[i]).val()) {
            j++;
            if (j > 0) {
                af_id += "|";
                afv_val += "|";
            }
            af_id += fld_ids[i];
            afv_val += $('#' + fld_ids[i]).val();
        }
    }

    var message = "";
    var valid = true;

    // التحقق من الحقول المطلوبة
    if (typeofselection == 0) {
        if (!main_cat) {
            message = "من فضلك أدخل التصنيف العام";
            $('#main_cat').focus();
            valid = false;
        } else if (!pc_id) {
            message = "من فضلك أدخل التصنيف الرئيسى";
            $('#pc_id').focus();
            valid = false;
        } else if (!auc_pc_id) {
            message = "من فضلك أدخل التصنيف الفرعى";
            $('#auc_pc_id').focus();
            valid = false;
        }
    } else if (typeofselection == 1 && !keywordsFilter1) {
        message = "رجاء ادخال كلمة بحث صحيحة";
        $('#keywordsFilter1').focus();
        valid = false;
    }

    if (valid && !auc_heading) {
        message = "من فضلك أدخل عنوان المزايدة";
        $('#auc_heading').focus();
        valid = false;
    } else if (valid && auc_value && isNaN(auc_value)) {
        message = "من فضلك أدخل قيمة صحيحة للمزايدة";
        $('#auc_value').focus();
        valid = false;
    } else if (valid && auc_value && !auc_currency) {
        message = "من فضلك أدخل عملة المزايدة";
        $('#auc_currency').focus();
        valid = false;
    } else if (valid && !auc_notice_type) {
        message = "من فضلك أدخل نوع العطاء مزايدة أو مناقصة";
        $('#auc_notice_type').focus();
        valid = false;
    } else if (valid && auc_qty && isNaN(auc_qty)) {
        message = "من فضلك أدخل كمية صحيحة";
        $('#auc_qty').focus();
        valid = false;
    } else if (valid && auc_qty && !auc_qty_mu_id) {
        message = "من فضلك أدخل وحدة القياس";
        $('#auc_qty_mu_id').focus();
        valid = false;
    } else if (valid && (!auc_document_fees || auc_document_fees == '0')) {
        message = "من فضلك أدخل رسوم الأوراق";
        $('#auc_document_fees').focus();
        valid = false;
    } else if (valid && auc_document_fees && isNaN(auc_document_fees)) {
        message = "من فضلك أدخل رسوم صحيحة";
        $('#auc_document_fees').focus();
        valid = false;
    } else if (valid && !auc_document_fees_currency) {
        message = "من فضلك أدخل نوع عملة الرسوم";
        $('#auc_document_fees_currency').focus();
        valid = false;
    } else if (valid && !auc_prequalification_criteria) {
        message = "من فضلك أوصف مؤهلات التقدم للمزايدة";
        $('#auc_prequalification_criteria').focus();
        valid = false;
    } else if (valid && !auc_details) {
        message = "من فضلك أوصف تفاصيل المزايدة";
        $('#auc_details').focus();
        valid = false;
    }

    if (valid) {
        $.get("addNewAuction.php", {
            keywordsFilter: keywordsFilter1,
            typeofselection: typeofselection,
            auc_usr_id: $('#auc_usr_id').val(),
            main_cat: main_cat,
            pc_id: pc_id,
            auc_pc_id: auc_pc_id,
            auc_heading: auc_heading,
            auc_value: auc_value,
            auc_notice_type: auc_notice_type,
            auc_qty: auc_qty,
            auc_qty_mu_id: auc_qty_mu_id,
            auc_emd: auc_emd,
            auc_document_fees: auc_document_fees,
            auc_document_fees_currency: auc_document_fees_currency,
            auc_project_period: auc_project_period,
            auc_products: auc_products,
            auc_publish_date: auc_publish_date,
            auc_docSaleStart_date: auc_docSaleStart_date,
            auc_docSaleEnd_date: auc_docSaleEnd_date,
            auc_docSubmitBefore_date: auc_docSubmitBefore_date,
            auc_due_date: auc_due_date,
            auc_currency: auc_currency,
            auc_prequalification_criteria: auc_prequalification_criteria,
            auc_details: auc_details,
            auc_preferred_location: auc_preferred_location,
            af_id: af_id,
            afv_val: afv_val
        }, function(data) {
            data = data.trim();
            var dt = data.split("|");
            if (dt[0] == '0') {
                alert(dt[1]);
            } else {
                alert(dt[1]);
                window.location = "post-auction.php";
            }
        });
    } else {
        alert(message);
    }

    return valid;
}
</script>
</body>
</html>
<?php
// إغلاق اتصال قاعدة البيانات
if (isset($db) && $db instanceof mysqli) {
    $db->close();
}
?>