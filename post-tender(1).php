<?php
/**
 * file name : post-tender.php
 * PHP Version: 8.3
 * Description: صفحة نشر المناقصات والمزايدات - نسخة مطورة ومتوافقة مع PHP 8.3
 * التغييرات الرئيسية:
 * - استبدال mysql_* القديم بـ MySQLi أو PDO (تم استخدام MySQLi في هذا المثال)
 * - إضافة type declarations للدوال والخصائص
 * - استخدام prepared statements لمنع SQL injection
 * - تحسين التحقق من المدخلات والـ Session
 * - إضافة strict typing
 * - تحسين معالجة الأخطاء
 */

declare(strict_types=1);

require_once "common.php";

// تعيين نوع الجلسة والأمان
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // للـ HTTPS فقط

// التحقق من الجلسة بشكل آمن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['last_page'] = 'post-tender.php';

// التحقق من وجود المستخدم في الجلسة بطريقة آمنة
if (empty($_SESSION['uid_indm'] ?? null)) {
    header('Location: sign-in.php');
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

/**
 * كلاس إضافة المناقصات - متوافق مع PHP 8.3
 */
class AddTender
{
    private string $msg = '';
    private int $tnd_usr_id;
    private string $main_cat = '';
    private int $pc_id = 0;
    private int $tnd_pc_id = 0;
    private string $tnd_heading = '';
    private float $tnd_value = 0.0;
    private int $tnd_currency = 0;
    private string $tnd_prequalification_criteria = '';
    private string $tnd_details = '';
    private string $tnd_preferred_location = '';
    private string $so_validity = '';
    private ?string $so_pic = null;
    private mysqli $db;

    public function __construct(
        int $tnd_usr_id,
        string $main_cat,
        int $pc_id,
        int $tnd_pc_id,
        string $tnd_heading,
        float $tnd_value,
        int $tnd_currency,
        string $tnd_prequalification_criteria,
        string $tnd_details,
        string $tnd_preferred_location,
        string $so_validity,
        mysqli $db
    ) {
        $this->db = $db;
        $this->tnd_usr_id = $tnd_usr_id;
        $this->main_cat = $this->sanitizeInput($main_cat);
        $this->pc_id = $pc_id;
        $this->tnd_pc_id = $tnd_pc_id;
        $this->tnd_heading = $this->sanitizeInput($tnd_heading);
        $this->tnd_value = $tnd_value;
        $this->tnd_currency = $tnd_currency;
        $this->tnd_prequalification_criteria = $this->sanitizeInput($tnd_prequalification_criteria);
        $this->tnd_details = $this->sanitizeInput($tnd_details);
        $this->tnd_preferred_location = $this->sanitizeInput($tnd_preferred_location);
        $this->so_validity = $this->sanitizeInput($so_validity);

        // تخزين في الجلسة بعد التنظيف
        $_SESSION['main_cat'] = $this->main_cat;
        $_SESSION['pc_id'] = $this->pc_id;
        $_SESSION['tnd_pc_id'] = $this->tnd_pc_id;
        $_SESSION['tnd_heading'] = $this->tnd_heading;
        $_SESSION['tnd_value'] = $this->tnd_value;
        $_SESSION['tnd_currency'] = $this->tnd_currency;
        $_SESSION['tnd_prequalification_criteria'] = $this->tnd_prequalification_criteria;
        $_SESSION['tnd_details'] = $this->tnd_details;
        $_SESSION['tnd_preferred_location'] = $this->tnd_preferred_location;
        $_SESSION['so_validity'] = $this->so_validity;
    }

    /**
     * تنظيف المدخلات النصية
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * التحقق من الكلمات غير المسموح بها
     */
    private function checkBadWord(string $param): bool
    {
        $valid = true;
        
        // استخدام prepared statement
        $stmt = $this->db->prepare("SELECT bd_word FROM bad_word");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $word = strtoupper($row['bd_word'] ?? '');
                if (!empty($word) && str_contains(strtoupper($param), $word)) {
                    $valid = false;
                    break;
                }
            }
            $stmt->close();
        }
        
        return $valid;
    }

    /**
     * التحقق من صحة البيانات
     */
    public function isValid(): bool
    {
        $valid = true;

        if (empty($this->main_cat)) {
            $this->msg = '<font color="#FF0000">من فضلك إختار التصنيف العام</font>';
            $valid = false;
        } elseif ($this->pc_id === 0) {
            $this->msg = '<font color="#FF0000">من فضلك إختار التصنيف الرئيسى</font>';
            $valid = false;
        } elseif ($this->tnd_pc_id === 0) {
            $this->msg = '<font color="#FF0000">من فضلك إختار التصنيف الفرعى</font>';
            $valid = false;
        } elseif (empty($this->tnd_heading)) {
            $this->msg = '<font color="#FF0000">من فضلك إختارعنوان للمناقصة أو المزايدة </font>';
            $valid = false;
        } elseif (!$this->checkBadWord(strtoupper($this->tnd_heading))) {
            $this->msg = "<font color='#FF0000'>لايمكنك كتابة مثل هذا العنوان لإحتوائه على كلمات غير مناسبة</font>";
            $valid = false;
        } elseif ($this->tnd_value <= 0) {
            $this->msg = '<font color="#FF0000">من فضلك أدخل قيمة صحيحة للمناقصة أو المزايدة</font>';
            $valid = false;
        } elseif (empty($this->tnd_prequalification_criteria)) {
            $this->msg = '<font color="#FF0000">من فضلك أدخل معايير التأهل المناقصة أو المزايدة</font>';
            $valid = false;
        } elseif (!$this->checkBadWord(strtoupper($this->tnd_prequalification_criteria))) {
            $this->msg = "<font color='#FF0000'> لايمكنك إدخال معايير التأهل للمناقصة أو المزايدة لإحتوائها كلمات غير مناسبة</font>";
            $valid = false;
        } elseif (empty($this->tnd_details)) {
            $this->msg = '<font color="#FF0000">من فضلك أدخل وصف وتفاصيل المناقصة أو المزايدة</font>';
            $valid = false;
        }

        return $valid;
    }

    /**
     * معالجة رفع الصور
     */
    private function uploadImage(array $file): ?string
    {
        if (empty($file['name'])) {
            return null;
        }

        if ($file['error'] > 0) {
            $this->msg = "خطأ في رفع الملف: " . $file['error'];
            return null;
        }

        // التحقق من نوع الملف
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $this->msg = 'نوع الملف غير مسموح به. المسموح: JPG, PNG, GIF';
            return null;
        }

        // التحقق من حجم الملف (2MB كحد أقصى)
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->msg = 'حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت';
            return null;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFileName = 'so-' . random_int(0, 9999) . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
        $uploadPath = __DIR__ . '/upload/sale_offer/' . $newFileName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return $newFileName;
        }

        $this->msg = 'فشل في رفع الملف';
        return null;
    }

    /**
     * إضافة المناقصة إلى قاعدة البيانات
     */
    public function add(array $files): bool
    {
        if (!empty($files['so_pic']['name'] ?? null)) {
            $this->so_pic = $this->uploadImage($files['so_pic']);
            
            if ($this->so_pic === null) {
                return false;
            }

            $sql = "INSERT INTO tender SET
                    tnd_usr_id = ?,
                    tnd_pc_id = ?,
                    tnd_heading = ?,
                    tnd_value = ?,
                    tnd_currency = ?,
                    tnd_prequalification_criteria = ?,
                    tnd_details = ?,
                    tnd_preferred_location = ?,
                    so_validity = ?,
                    so_pic = ?,
                    so_approval_status = '0',
                    so_posting_date = NOW(),
                    so_updated_date = NOW()";

            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param(
                    'iisdisssss',
                    $this->tnd_usr_id,
                    $this->tnd_pc_id,
                    $this->tnd_heading,
                    $this->tnd_value,
                    $this->tnd_currency,
                    $this->tnd_prequalification_criteria,
                    $this->tnd_details,
                    $this->tnd_preferred_location,
                    $this->so_validity,
                    $this->so_pic
                );
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $sql = "INSERT INTO tender SET
                    tnd_usr_id = ?,
                    tnd_pc_id = ?,
                    tnd_heading = ?,
                    tnd_value = ?,
                    tnd_currency = ?,
                    tnd_prequalification_criteria = ?,
                    tnd_details = ?,
                    tnd_preferred_location = ?,
                    so_validity = ?,
                    tnd_updated_date = NOW()";

            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param(
                    'iisdissss',
                    $this->tnd_usr_id,
                    $this->tnd_pc_id,
                    $this->tnd_heading,
                    $this->tnd_value,
                    $this->tnd_currency,
                    $this->tnd_prequalification_criteria,
                    $this->tnd_details,
                    $this->tnd_preferred_location,
                    $this->so_validity
                );
                $stmt->execute();
                $stmt->close();
            }
        }

        if ($this->db->affected_rows > 0) {
            // مسح بيانات الجلسة
            unset(
                $_SESSION['main_cat'],
                $_SESSION['pc_id'],
                $_SESSION['tnd_pc_id'],
                $_SESSION['tnd_heading'],
                $_SESSION['tnd_value'],
                $_SESSION['tnd_currency'],
                $_SESSION['tnd_prequalification_criteria'],
                $_SESSION['tnd_preferred_location'],
                $_SESSION['so_validity']
            );
            
            $this->msg = '<font color="#009900">Tender posted successfully.</font>';
            return true;
        }

        $this->msg = '<font color="#FF0000">حدث خطأ أثناء حفظ البيانات</font>';
        return false;
    }

    public function getMessage(): string
    {
        return $this->msg;
    }
}

// إنشاء اتصال قاعدة بيانات باستخدام MySQLi
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $db->connect_error);
}

$db->set_charset('utf8');

// معالجة الرسائل المخزنة في الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// معالجة النموذج عند الإرسال
if (isset($_POST['submitTender']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // تنظيف وتأمين المدخلات
    $tnd_usr_id = (int)($_POST['tnd_usr_id'] ?? 0);
    $main_cat = trim($_POST['main_cat'] ?? '');
    $pc_id = (int)($_POST['pc_id'] ?? 0);
    $tnd_pc_id = (int)($_POST['tnd_pc_id'] ?? 0);
    $tnd_heading = trim($_POST['tnd_heading'] ?? '');
    $tnd_value = (float)($_POST['tnd_value'] ?? 0);
    $tnd_currency = (int)($_POST['tnd_currency'] ?? 0);
    $tnd_prequalification_criteria = trim($_POST['tnd_prequalification_criteria'] ?? '');
    $tnd_details = trim($_POST['tnd_details'] ?? '');
    $tnd_preferred_location = trim($_POST['tnd_preferred_location'] ?? '');
    $so_validity = trim($_POST['so_validity'] ?? '');

    // التحقق من صحة معرف المستخدم
    if ($tnd_usr_id !== $uid) {
        header('Location: sign-in.php');
        exit;
    }

    $adn = new AddTender(
        $tnd_usr_id,
        $main_cat,
        $pc_id,
        $tnd_pc_id,
        $tnd_heading,
        $tnd_value,
        $tnd_currency,
        $tnd_prequalification_criteria,
        $tnd_details,
        $tnd_preferred_location,
        $so_validity,
        $db
    );

    if ($adn->isValid()) {
        if ($adn->add($_FILES)) {
            $_SESSION['msg'] = $adn->getMessage();
            header('Location: post-tender-res.php');
            exit;
        } else {
            $_SESSION['msg'] = $adn->getMessage();
            header('Location: post-tender.php');
            exit;
        }
    } else {
        $_SESSION['msg'] = $adn->getMessage();
        header('Location: post-tender.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? 'موقع المناقصات'); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    
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
    <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
    <link rel="stylesheet" type="text/css" media="screen" href="datepicker/datePicker.css">
    <link rel="stylesheet" type="text/css" media="screen" href="datepicker/demo.css">

    <script src="js/jquery-1.2.1.min.js"></script>
    <script src="js/jquery.autocomplete.js"></script>
    <script src="datepicker/date.js"></script>
    <script src="datepicker/jquery.datePicker.js"></script>
</head>
<body>
<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
    <br><br>
    
    <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" width="1" height="1"></div>

    <?php include "includes/header_menu.php"; ?>
    
    <!--left navigation:start-->
    <div class="f1 w61n tb lh ml m2" id="lnav" style="display: block;">
        <ul class="nln1" style="margin: 0px; padding: 0px;" title="Trade Offers">
            <li><h2>عروض التجارة البيع والشراء</h2></li>
            <li style="border-bottom: medium none;" title="Buy Requirement"><h3>طلبات الشراء</h3></li>
            <li class="lp"><a href="post-buy-req.php" title="Post a New Buy Requirement">»&nbsp;أنشر طلب شراء</a></li>
            <li class="lp"><a href="manage-buy-requirement.php" title="Manage Buy Requirements">»&nbsp;إدارة طلبات شرائى </a></li>
            
            <li style="border-bottom: medium none;" title="Sell Offers "><h3>عروض البيع</h3></li>
            <li class="lp"><a href="post-sell-offer.php" title="Post a New Sell Offer">»&nbsp;أنشر عروض بيع خاصة</a></li>
            <li class="lp"><a href="manage-sell-offer.php" title="Manage Sell Offers">»&nbsp;إدارة عروض البيع الخاصة </a></li>
            
            <li style="border-bottom: medium none;" title="Tenders & Auctions"><h3>المناقصات والمزايدات</h3></li>
            <li class="lp"><a href="post-tender.php" title="Post a Tender or Auctions">»&nbsp;أنشر مناقصات أو مزايدات</a></li>
            <li class="lp"><a href="manage-tenders.php" title="Manage Tenders or Auctions">»&nbsp;إدارة مناقصاتى أو مزايداتى</a></li>
            
            <li style="border-bottom: medium none; margin-top: 40px;" title="You may also like to "><h2>ربما أيضا تريد </h2></li>
            <li class="np"><a href="buyleads.php" title="View Latest Buy Leads">شاهد أحدث طلبات الشراء</a></li>
            <li class="np"><a href="sale-offers.php" title="View Latest Sell Offers">شاهد أحدث عروض البيع الخاصة</a></li>
            <li class="np"><a href="tenders.php" title="View Latest Tenders">شاهد أحدث المناقصات والمزايدات</a></li>
            <li class="np"><a href="manage-purchased-buyleads.php" title="View Purchased Buy Leads">شاهد طلبات الشراء المشتراه</a></li>
            <li class="np"><a href="manage-buylead-alert.php" title="Manage Buy Lead Alerts">إدارة إشعارات طلبات الشراء</a></li>
        </ul>
    </div>
    <!--left navigation:ends-->
    
    <div class="w57 b1_m2 f1 wd797" id="ldiv">
        <input type="hidden" value="0" id="typeofselection" />
        
        <div id="div2" style="display:block;">
            <div><img src="images/zero.gif" width="1" height="19"></div>
            <table width="100%" align="center">
                <tr>
                    <td>
                        <div align="left">
                            <div class="tw2l fl" id="formmain" style="margin-left:8px;background-color:#FAF4FF">
                                <div class="" id="lgn1">
                                    <p class="c-1 g2 fs bo1">أنشر مناقصات أو مزايدات مجانا  
                                        <span class="p6 q4 tm1 cbc fsz1"><i class="co" title="Required Information">*</i></span>
                                    </p>
                                </div>
                                
                                <div>
                                    <?php if (!empty($msg)): ?>
                                        <div id="error_msg" style="color: #FF0000;">
                                            <?php echo $msg; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="post" name="postForm1" action="" onsubmit="return validTender();" enctype="multipart/form-data">
                                        <input type="hidden" id="tnd_usr_id" name="tnd_usr_id" value="<?php echo (int)$_SESSION['uid_indm']; ?>"/>
                                        
                                        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%">
                                            <tr>
                                                <td>
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td class="tabclose" onclick="searchcat()" id="scs" width="152" title="Search Categories">حدد الأصناف تلقائيا</td>
                                                            <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
                                                            <td class="tabopen" onclick="beowswcat()" id="bcs" width="155" title="Browse Categories">تصفح وإختار الأصناف</td>
                                                            <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <table class="frm mt5" width="100%">
                                            <tr class="scc" id="r0" style="display: none;">
                                                <td valign="middle" width="30%">
                                                    <p class="pd15"><b style="font-size:13px;"></b></p>
                                                </td>
                                                <td valign="TOP">
                                                    <input class="txt ui-placeholder-input ui-autocomplete-input" 
                                                           name="keywordsFilter1" 
                                                           id="keywordsFilter1" 
                                                           style="width: 450px;float: left; height:30px; border: 1px solid #ff8a8a;" 
                                                           type="text" 
                                                           maxlength="60" 
                                                           size="33">
                                                </td>
                                            </tr>
                                            
                                            <tr id="r0" style="height: 48px;" class="bcc">
                                                <td valign="middle" width="30%" title="Main Category:">
                                                    <p class="pd15">
                                                        <i>*</i><b>إختار التصنيف العام للمناقصة أو المزايدة </b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <select class="bd4 hw6 mr3 htb" id="main_cat" name="main_cat" style="height:30px;" onchange="showCategory()">
                                                        <option value="">-- إختار التصنيف العام --</option>
                                                        <?php
                                                        $stmt = $db->prepare("SELECT pc_id, pc_name FROM product_category_arabyos WHERE pc_parent_id = '0' AND pc_status = '1'");
                                                        if ($stmt) {
                                                            $stmt->execute();
                                                            $result = $stmt->get_result();
                                                            while ($row = $result->fetch_assoc()) {
                                                                echo '<option value="' . (int)$row['pc_id'] . '">' . 
                                                                     htmlspecialchars($row['pc_name']) . '</option>';
                                                            }
                                                            $stmt->close();
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            
                                            <tr id="r1" style="height: 48px;" class="bcc">
                                                <td valign="middle" width="30%" title="Category:">
                                                    <p class="pd15">
                                                        <i>*</i><b> إختار التصنيف الرئيسى </b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <select class="bd4 hw6 mr3 htb" id="pc_id" name="pc_id" style="height:30px;" onchange="showSubcat()">
                                                        <option value="">-- إختار التصنيف الرئيسى --</option>
                                                    </select>
                                                    
                                                    <select class="bd4 hw6 mr3 htb" id="tnd_pc_id" name="tnd_pc_id" style="height:30px;" onchange="showTenderAdditionalFields();">
                                                        <option value="">-- إختار التصنيف الفرعى --</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%" title="Tender Heading:">
                                                    <p class="pd15">
                                                        <i>*</i><b> أكتب العنوان الرئيسى للمناقصة أو المزايدة </b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="tnd_heading" 
                                                           id="tnd_heading" 
                                                           style="width:450px;" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           maxlength="90" 
                                                           value="<?php echo htmlspecialchars($_SESSION['tnd_heading'] ?? ''); ?>"/>
                                                </td>
                                            </tr>
                                            
                                            <tr id="r2" style="height: 48px;">
                                                <td valign="TOP" width="30%" title="Tender Value:">
                                                    <p class="pd15">
                                                        <b> أكتب قيمة المناقصة أو المزايدة </b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <input name="tnd_value" 
                                                           id="tnd_value" 
                                                           style="width:280px;" 
                                                           class="bd4 hw6 mr3 htb" 
                                                           maxlength="90" 
                                                           value="<?php echo htmlspecialchars($_SESSION['tnd_value'] ?? ''); ?>"/>
                                                    <select size="1" name="tnd_currency" id="tnd_currency" class="a_f s_u">
                                                        <option value="">- إختار نوع العملة -</option>
                                                        <?php
                                                        $stmt = $db->prepare("SELECT cn_id, cn_currency FROM country WHERE cn_status = '1'");
                                                        if ($stmt) {
                                                            $stmt->execute();
                                                            $result = $stmt->get_result();
                                                            $selectedCountry = user_info($uid, 'country') ?? $_SESSION['tnd_currency'] ?? '';
                                                            while ($row = $result->fetch_assoc()) {
                                                                $selected = ($row['cn_id'] == $selectedCountry) ? 'selected="selected"' : '';
                                                                echo '<option value="' . (int)$row['cn_id'] . '" ' . $selected . '>' . 
                                                                     htmlspecialchars($row['cn_currency']) . '</option>';
                                                            }
                                                            $stmt->close();
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            
                                            <!-- بقية الحقول مشابهة ولكن مع تحسينات الأمان -->
                                            
                                            <tr id="r3">
                                                <td valign="TOP" width="30%" title="Pre-qualification Criteria:">
                                                    <p class="pd15">
                                                        <i>*</i><b>شروط التأهـل</b>
                                                        <br />
                                                        <b class="q4"></b><font class="co1" id="Charcount" color="#ff8000">2000</font>
                                                        <b class="fwn cbc">الحروف المتبقية</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <div id="lgn6" style="width: 360px; height: 105px;">
                                                        <textarea name="tnd_prequalification_criteria" 
                                                                  id="tnd_prequalification_criteria" 
                                                                  style="max-width: 4500px;width:450px; height:95px; max-height:95px; display: block;" 
                                                                  rows="5" 
                                                                  cols="30"><?php echo htmlspecialchars($_SESSION['tnd_prequalification_criteria'] ?? ''); ?></textarea>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <tr id="r3">
                                                <td valign="TOP" width="30%" title="Details: ">
                                                    <p class="pd15">
                                                        <i>*</i><b> التفاصيـل</b>
                                                    </p>
                                                </td>
                                                <td valign="TOP">
                                                    <div id="lgn6" style="width: 360px; height: 105px;">
                                                        <textarea name="tnd_details" 
                                                                  id="tnd_details" 
                                                                  style="max-width: 4500px;width:450px; height:95px; max-height:95px; display: block;" 
                                                                  rows="5" 
                                                                  cols="30"><?php echo htmlspecialchars($_SESSION['tnd_details'] ?? ''); ?></textarea>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <tr id="r4">
                                                <td valign="TOP" width="30%" title="Location Preferences:">
                                                    <p class="pd15"><b> تحديد مكان المناقصة أو المزايدة </b></p>
                                                </td>
                                                <td valign="TOP">
                                                    <div style="vertical-align:bottom">
                                                        <input type="radio" id="tnd_preferred_location_1" name="tnd_preferred_location" value="abroad" />
                                                        <label style="top:0px;" title="Abroad Only">المناقصة أو المزايدة لخارج مصر فقط</label>
                                                        &nbsp;&nbsp;
                                                        <input type="radio" id="tnd_preferred_location_2" name="tnd_preferred_location" value="any" checked="checked"/>
                                                        <label style="top:0px;" title="Abroad + Domestic">المناقصة أو المزايدة للخارج ولمصر</label>
                                                        &nbsp;&nbsp;
                                                        <input type="radio" id="tnd_preferred_location_3" name="tnd_preferred_location" value="domestic"/>
                                                        <label style="top:0px;" title="Domestic Only">المناقصة أو المزايدة لمصر فقط</label>
                                                        &nbsp;&nbsp;
                                                        <input type="radio" id="tnd_preferred_location_4" name="tnd_preferred_location" value="my_city"/>
                                                        <label style="top:0px;" title="My City Only">المناقصة أو المزايدة لمدينتى فقط</label>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                                        </table>
                                        
                                        <table id="additional" class="frm mt5" width="100%"></table>
                                        <input type="hidden" id="additional_field_ids">
                                        <input type="hidden" id="additional_field_types">
                                        
                                        <div class="a2 pt pb" id="loginsubmit" style="display: block;">
                                            <input type="hidden" name="frmsubmitbutton" value="login">
                                            <input type="hidden" name="submitTender" value="1">
                                            <input type="button" 
                                                   id="login_frm1" 
                                                   class="cr bo1 fsz1" 
                                                   style="height: 32px; width: 170px;" 
                                                   value="أنشر المناقصة أو المزايدة" 
                                                   onclick="validTender();">
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
    lostFocus();
    $('#keywordsFilter1').unbind().live('keyup',function() {
        var type11='Products';
        $("#keywordsFilter1").autocomplete("autocomplete.php", {
            selectFirst: true,
            extraParams: {type: type11},
            width: 407
        }).result(function(event, data, formatted) {
            $("input#keywordsFilter1").val(data);
        });
    });
    
    // تهيئة منتقي التاريخ
    $('#tnd_publish_date, #tnd_docSaleStart_date, #tnd_docSaleEnd_date, #tnd_docSubmitBefore_date, #tnd_due_date')
        .datePicker()
        .val(new Date().asString())
        .trigger('change');
});

function searchcat() {
    $("#scs").removeClass("tabclose").addClass("tabopen");
    $("#bcs").removeClass("tabopen").addClass("tabclose");
    $('#typeofselection').val(1);
    $(".bcc").css("display","none");
    $(".scc").removeAttr('style');
}

function beowswcat() {
    $("#bcs").removeClass("tabclose").addClass("tabopen");
    $("#scs").removeClass("tabopen").addClass("tabclose");
    $('#typeofselection').val(0);
    $(".scc").css("display","none");
    $(".bcc").removeAttr('style');
}

function showCategory() {
    var pc_id = document.getElementById('main_cat').value;
    $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) { 
        $('#pc_id').html(data); 
        showSubcat(); 
    });
}

function showSubcat() {
    var id = document.getElementById('