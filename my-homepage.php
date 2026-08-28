<?php
// my-homepage.php - نسخة PHP 8.4 متوافقة مع الاختبار

// تمكين عرض الأخطاء للتطوير
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'common.php';

// التحقق من الجلسة
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header('Location: sign-in.php');
    exit;
}

$_SESSION['last_page'] = 'my-homepage.php';
$uid = (int)$_SESSION['uid_indm'];

// جلب بيانات المستخدم الحالية باستخدام Prepared Statement
$sql = "SELECT * FROM website_content WHERE wc_usr_id = ?";
$stmt = mysqli_prepare($con, $sql);

if (!$stmt) {
    die("خطأ في تحضير الاستعلام: " . mysqli_error($con));
}

mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

// كلاس إدارة محتوى الصفحة الرئيسية
class CreateFreeWebsite
{
    private string $msg = '';
    private int $wc_usr_id;
    private string $wc_homepage_key_desc = '';
    private string $wc_homepage_detail_desc = '';
    
    public function __construct(
        int $wc_usr_id, 
        string $wc_homepage_key_desc, 
        string $wc_homepage_detail_desc
    ) {
        $this->wc_usr_id = $wc_usr_id;
        $this->wc_homepage_key_desc = trim($wc_homepage_key_desc);
        $this->wc_homepage_detail_desc = trim($wc_homepage_detail_desc);
    }
    
    public function getMsg(): string
    {
        return $this->msg;
    }
    
    public function valid(): bool
    {
        $valid = true;
        
        if (empty($this->wc_homepage_key_desc)) {
            $this->msg = '<div class="alert alert-danger">الوصف الرئيسي للشركة لا يمكن أن يكون فارغاً.</div>';
            $valid = false;
        } elseif (strlen($this->wc_homepage_key_desc) < 100) {
            $this->msg = '<div class="alert alert-danger">الوصف الرئيسي للشركة يجب أن لا يقل عن 100 حرف.</div>';
            $valid = false;
        } elseif (empty($this->wc_homepage_detail_desc)) {
            $this->msg = '<div class="alert alert-danger">الوصف التفصيلي للشركة لا يمكن أن يكون فارغاً.</div>';
            $valid = false;
        } elseif (strlen($this->wc_homepage_detail_desc) < 200) {
            $this->msg = '<div class="alert alert-danger">الوصف التفصيلي للشركة يجب أن لا يقل عن 200 حرف.</div>';
            $valid = false;
        }
        
        return $valid;
    }
    
    public function add(mysqli $con): bool
    {
        // التحقق من وجود السجل أولاً
        $check_sql = "SELECT COUNT(*) as count FROM website_content WHERE wc_usr_id = ?";
        $check_stmt = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($check_stmt, 'i', $this->wc_usr_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $check_row = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($check_stmt);
        
        if ($check_row['count'] > 0) {
            // تحديث
            $sql = "UPDATE website_content SET 
                    wc_homepage_key_desc = ?, 
                    wc_homepage_detail_desc = ?, 
                    wc_updated_date = NOW() 
                    WHERE wc_usr_id = ?";
        } else {
            // إدراج جديد
            $sql = "INSERT INTO website_content 
                    (wc_usr_id, wc_homepage_key_desc, wc_homepage_detail_desc, wc_updated_date) 
                    VALUES (?, ?, ?, NOW())";
        }
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger">خطأ في تحضير الاستعلام.</div>';
            error_log("Prepare failed: " . mysqli_error($con));
            return false;
        }
        
        if ($check_row['count'] > 0) {
            mysqli_stmt_bind_param($stmt, 'ssi', 
                $this->wc_homepage_key_desc, 
                $this->wc_homepage_detail_desc, 
                $this->wc_usr_id
            );
        } else {
            mysqli_stmt_bind_param($stmt, 'iss', 
                $this->wc_usr_id, 
                $this->wc_homepage_key_desc, 
                $this->wc_homepage_detail_desc
            );
        }
        
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            $this->msg = '<div class="alert alert-danger">حدث خطأ أثناء الحفظ: ' . mysqli_error($con) . '</div>';
            error_log("Execute failed: " . mysqli_error($con));
        } else {
            $this->msg = '<div class="alert alert-success">تم حفظ البيانات بنجاح.</div>';
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }
}

// معالجة الرسائل
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// معالجة تقديم النموذج
if (isset($_POST['btnSubmit'])) {
    
    // التحقق من صلاحية العضوية
    $user_membership = (int)getUserInfo($uid, 'usr_mp_id');
    if ($user_membership < 3) {
        echo '<script>
            alert("يجب عليك الاشتراك في العضوية المميزة لإنشاء صفحة بائع");
            window.location.href = "membership_plans.php";
        </script>';
        exit;
    }
    
    // إنشاء كائن وتحديث البيانات
    $adn = new CreateFreeWebsite(
        (int)$_POST['wc_usr_id'],
        $_POST['wc_homepage_key_desc'] ?? '',
        $_POST['wc_homepage_detail_desc'] ?? ''
    );
    
    if ($adn->valid()) {
        $adn->add($con);
    }
    
    $_SESSION['msg'] = $adn->getMsg();
    header('Location: my-homepage.php');
    exit;
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    
    <!-- CSS -->
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/b-v-5.css" type="text/css" rel="stylesheet">
    <!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /><![endif]-->
    
    <!-- JavaScript -->
    <script src="js/jquery.js"></script>
    <script src="js/jquery.ui.widget.js"></script>
    <script src="js/jquery.fileupload.js"></script>
    
    <style>
        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .max {
            color: #fa5901;
            font-size: 11px;
            margin-top: 5px;
        }
        .frm_a {
            width: 98%;
            border: 1px solid #e0f0fd;
            padding: 10px;
        }
    </style>
    
    <script>
    $(document).ready(function() {
        mecount();
        
        // تهيئة رفع الملفات
        var url = 'http://arabyos.com/server/php/';
        $('#file_upload').fileupload({
            url: url,
            maxNumberOfFiles: 1,
            dataType: 'json',
            done: function(e, data) {
                $.each(data.result.files, function(index, file) {
                    $.post("companylogo-update.php", {
                        'uid': '<?php echo $uid; ?>',
                        'file': file.name
                    }, function(data) {
                        if (typeof list_photo === 'function') {
                            list_photo();
                        }
                    });
                });
            }
        });
    });
    
    function mecount() {
        var cnt = $('#wc_homepage_key_desc').val().length;
        var cnt2 = $('#wc_homepage_detail_desc').val().length;
        var ncnt = Math.max(0, 250 - cnt);
        var ncnt2 = Math.max(0, 4000 - cnt2);
        
        $('#cn').text(ncnt);
        $('#cn2').text(ncnt2);
    }
    
    function validWebsite() {
        var keyDesc = $('#wc_homepage_key_desc').val();
        var detailDesc = $('#wc_homepage_detail_desc').val();
        var message = '';
        var valid = true;
        
        if (!keyDesc || keyDesc.trim() === '') {
            message = 'الوصف الرئيسي للشركة لا يمكن أن يكون فارغاً.';
            $('#wc_homepage_key_desc').focus();
            valid = false;
        } else if (keyDesc.length < 100) {
            message = 'الوصف الرئيسي للشركة يجب أن لا يقل عن 100 حرف.';
            $('#wc_homepage_key_desc').focus();
            valid = false;
        } else if (!detailDesc || detailDesc.trim() === '') {
            message = 'الوصف التفصيلي للشركة لا يمكن أن يكون فارغاً.';
            $('#wc_homepage_detail_desc').focus();
            valid = false;
        } else if (detailDesc.length < 200) {
            message = 'الوصف التفصيلي للشركة يجب أن لا يقل عن 200 حرف.';
            $('#wc_homepage_detail_desc').focus();
            valid = false;
        }
        
        if (!valid) {
            alert(message);
        }
        return valid;
    }
    </script>
</head>
<body>
    <div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
        <div class="bt">
            <img src="images/z.gif" height="1" width="1" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>">
        </div>
        <div class="inner_wrapper">
            <?php include "includes/header_menu.php"; ?>
            <?php include "includes/left_menu.php"; ?>
            
            <div class="w56 f1 p2b p14 blr" style="width:80%;height:100%;">
                <style>
                    .max { color: #fa5901; font-size: 11px; margin-top: 5px; }
                    .s_u { width: 144px; }
                    .frm_a { width: 98%; border: 1px solid #e0f0fd; padding: 10px; }
                </style>
                
                <div>
                    <h1 style="font-size: 22px; font-weight: bold; margin-bottom: 15px;">
                        إملأ بيانات الصفحة الرئيسية لصفحة أعمالك على المنصة
                    </h1>
                </div>
                
                <?php if (!empty($msg)): ?>
                    <div><?php echo $msg; ?></div>
                <?php endif; ?>
                
                <p style="border-top: 3px solid #589CE3; margin-top: 8px;"></p>
                
                <div id="re_link" style="background-color: #F0E1FF; border-bottom: 1px solid #D2D2D2; color: #444444; font-size: 14px; padding: 10px;">
                    <span style="font-size: 14px;" title="Add Home Page details to your Online Catalog">
                        بيانات الصفحة الرئيسية عن المورد
                    </span>
                </div>
                
                <div class="clb px"></div>
                <div class="clb"></div>
                
                <div class="mt5">
                    <form method="POST" name="ModReg" onsubmit="return validWebsite();">
                        <input type="hidden" name="wc_usr_id" value="<?php echo $uid; ?>">
                        
                        <div class="frm_a clb" style="background-color:#FAF4FF;">
                            <table border="0" cellspacing="0" cellpadding="4" width="100%">
                                <tr>
                                    <td class="f1" valign="top" style="color: #222222;" title="Key Description of your Company">
                                        <b>الوصف الرئيسي لشركتك</b>
                                        <span class="f11" style="color: #707070;">
                                            (يظهر في صفحتك وفي نتائج بحث <?php echo htmlspecialchars(get_page_settings(4) ?? ''); ?>)
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <textarea id="wc_homepage_key_desc" 
                                                  name="wc_homepage_key_desc" 
                                                  style="width:100%; min-height:150px;" 
                                                  onkeyup="mecount();" 
                                                  maxlength="250"><?php echo htmlspecialchars($row->wc_homepage_key_desc ?? ''); ?></textarea>
                                        <div class="max">
                                            <span id="cn" style="color: #ff8000;">250</span> حرف متبقي. الحد الأقصى 250 حرف.
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr><td>&nbsp;</td></tr>
                                
                                <tr>
                                    <td class="f1" valign="top" style="font-weight: bold; color: #222222;" title="Detailed Description of your Website Home Page">
                                        <b>الوصف التفصيلي لشركتك</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <textarea id="wc_homepage_detail_desc" 
                                                  name="wc_homepage_detail_desc" 
                                                  style="width:100%; min-height:250px;" 
                                                  onkeyup="mecount();" 
                                                  maxlength="4000"><?php echo htmlspecialchars($row->wc_homepage_detail_desc ?? ''); ?></textarea>
                                        <div class="max">
                                            <span id="cn2" style="color: #ff8000;">4000</span> حرف متبقي. الحد الأقصى 4000 حرف.
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td style="text-align: left;">
                                        <button type="submit" name="btnSubmit" class="btn btn-primary" style="padding: 8px 20px;">
                                            حفظ التغييرات
                                        </button>
                                        <span id="pf_save" style="display:none; margin-left:15px;">
                                            <img src="images/loading.gif" width="16" height="11" alt="">
                                        </span>
                                    </td>
                                </tr>
                            </table>
                            <div class="clb">&nbsp;</div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="c3">&nbsp;</div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>