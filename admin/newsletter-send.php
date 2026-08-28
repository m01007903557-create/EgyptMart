<?php
/**
 * File: newsletter-send.php

 * Version: 2.0.0
 * Description: إرسال نشرات بريدية جديدة مع إمكانية استهداف فئات وبلدان وشركات محددة
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة
session_start();

// تضمين الملفات المطلوبة
require_once "../common.php";
require_once "../lib/pagination.php";

// التحقق من تسجيل الدخول
check_admin_login();

// استرجاع قيم الجلسة
$nc_subject = $_SESSION['nc_subject'] ?? '';
unset($_SESSION['nc_subject']);

$nc_content = $_SESSION['nc_content'] ?? '';
unset($_SESSION['nc_content']);

// جلب بيانات الدول
$country_array = [];
$sql_country = "SELECT * FROM country ORDER BY cn_name ASC";
$res_country = mysqli_query($con, $sql_country);
if ($res_country) {
    while ($row_country = mysqli_fetch_assoc($res_country)) {
        $country_array[] = [
            'id' => $row_country['cn_id'],
            'name' => $row_country['cn_name']
        ];
    }
}

// جلب بيانات الشركات
$company_array = [];
$sql_company = "SELECT * FROM business_profile WHERE bnsprof_compname != '' ORDER BY bnsprof_compname ASC";
$res_company = mysqli_query($con, $sql_company);
if ($res_company) {
    while ($row_company = mysqli_fetch_assoc($res_company)) {
        $company_array[] = [
            'id' => $row_company['bnsprof_uid'],
            'name' => $row_company['bnsprof_compname']
        ];
    }
}



// =============================================
// جلب بيانات الشركات مع أرقام الهواتف (للواتساب اليدوي)
// =============================================
$company_array_with_phones = [];
$sql_company_phones = "SELECT bp.bnsprof_uid, bp.bnsprof_compname, u.mobile1 
                       FROM business_profile bp 
                       LEFT JOIN user u ON bp.bnsprof_uid = u.usr_id 
                       WHERE bp.bnsprof_compname != '' AND u.mobile1 IS NOT NULL
                       ORDER BY bp.bnsprof_compname ASC";
$res_company_phones = mysqli_query($con, $sql_company_phones);
if ($res_company_phones) {
    while ($row = mysqli_fetch_assoc($res_company_phones)) {
        $phone = preg_replace('/[^0-9]/', '', $row['mobile1'] ?? '');
        if (!empty($phone) && substr($phone, 0, 2) != '20') {
            $phone = '20' . ltrim($phone, '0');
        }
        $company_array_with_phones[] = [
            'id' => $row['bnsprof_uid'],
            'name' => $row['bnsprof_compname'],
            'phone' => $phone
        ];
    }
}







// جلب بيانات التصنيفات الرئيسية
$cat_array = [];
$sql_cat = "SELECT * FROM product_category WHERE pc_parent_id = '0' ORDER BY pc_id ASC";
$res_cat = mysqli_query($con, $sql_cat);
if ($res_cat) {
    while ($row_cat = mysqli_fetch_assoc($res_cat)) {
        $cat_array[] = [
            'id' => $row_cat['pc_id'],
            'name' => $row_cat['pc_name']
        ];
    }
}

/**
 * Class AddPlan - إضافة وإرسال النشرات البريدية
 */
class AddPlan {
    private ?string $msg = null;
    private ?string $nc_subject;
    private ?string $nc_content;
    private mysqli $db;
    
    /**
     * المُنشئ
     */
    public function __construct(?string $nc_subject, ?string $nc_content, ?mysqli $databaseConnection = null) {
        global $con;
        
        $this->nc_subject = $nc_subject;
        $this->nc_content = $nc_content;
        $this->db = $databaseConnection ?? $con;
        
        $_SESSION['nc_subject'] = $this->nc_subject;
        $_SESSION['nc_content'] = $this->nc_content;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        $this->msg = null;
        
        if (empty($this->nc_subject)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال عنوان النشرة</div>';
            return false;
        }
        
        if (empty($this->nc_content)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال محتوى النشرة</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * معالجة الصور المضمنة في المحتوى
     */
    private function processInlineImages(): string {
        $content = $this->nc_content;
        $dir = dirname(__FILE__) . '/../images/reply/';
        
        // التأكد من وجود المجلد
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // البحث عن صور base64
        preg_match_all('/src="data:image\/([^;]+);base64,([^"]+)"/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $imageType = $match[1];
            $base64Data = $match[2];
            $fullMatch = $match[0];
            
            $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;
            $filename = uniqid() . '.' . $extension;
            $filepath = $dir . $filename;
            
            // فك تشفير وحفظ الصورة
            $imageData = base64_decode(str_replace(' ', '+', $base64Data));
            file_put_contents($filepath, $imageData);
            
            // استبدال الرابط في المحتوى
            $newSrc = 'http://egyptmart.online/images/reply/' . $filename;
            $content = str_replace($fullMatch, 'src="' . $newSrc . '"', $content);
        }
        
        return $content;
    }
    
    /**
     * بناء شرط التصنيفات
     */
    private function buildCategoryCondition(string $categoryassigned): string {
        // الحصول على التصنيفات الرئيسية
        $sql12 = "SELECT CONCAT( GROUP_CONCAT(p1.pc_id), ',', GROUP_CONCAT(DISTINCT p2.pc_id) , ',', GROUP_CONCAT(DISTINCT p3.pc_id) ) as Grandparentname
                  FROM product_category p1
                  LEFT JOIN product_category p2 ON p1.pc_parent_id = p2.pc_id
                  LEFT JOIN product_category p3 ON p2.pc_parent_id = p3.pc_id 
                  WHERE p3.pc_id IN ($categoryassigned)";
        
        $res_main_category = mysqli_query($this->db, $sql12);
        $allCategories = $categoryassigned;
        
        if ($res_main_category) {
            while ($row_cat1 = mysqli_fetch_object($res_main_category)) {
                if (!empty($row_cat1->Grandparentname)) {
                    $allCategories = $row_cat1->Grandparentname;
                }
            }
        }
        
        return " AND (
            usr_id IN (SELECT DISTINCT sac_usr_id FROM selloffer_alert_category WHERE sac_pc_id IN ($allCategories) AND sac_status = 1)
            OR usr_id IN (SELECT DISTINCT so_usr_id FROM sale_offer WHERE so_pc_id IN ($allCategories) AND so_status = 1)
            OR usr_id IN (SELECT DISTINCT tac_usr_id FROM tender_alert_category WHERE tac_pc_id IN ($allCategories) AND tac_status = 1)
            OR usr_id IN (SELECT DISTINCT aac_usr_id FROM auction_alert_category WHERE aac_pc_id IN ($allCategories) AND aac_status = 1)
            OR usr_id IN (SELECT DISTINCT bac_usr_id FROM buylead_alert_category WHERE bac_pc_id IN ($allCategories) AND bac_status = 1)
        )";
    }
    
    /**
     * إضافة وإرسال النشرة
     */
    public function add(): bool {
        if (!$this->valid()) {
            return false;
        }
        
        // معالجة التصنيفات المحددة
        $categoryassigned = isset($_POST['categoryassigned']) && is_array($_POST['categoryassigned']) 
            ? implode(",", array_map('intval', $_POST['categoryassigned'])) 
            : '';
        
        // معالجة الدول المحددة
        $country = isset($_POST['country']) && is_array($_POST['country']) 
            ? implode(",", array_map('intval', $_POST['country'])) 
            : '';
        
        // معالجة الشركات المحددة
        $companies = isset($_POST['companies']) && is_array($_POST['companies']) 
            ? implode(",", array_map('intval', $_POST['companies'])) 
            : '';
        
        // معالجة الصور المضمنة
        $processedContent = $this->processInlineImages();
        
        // حفظ في قاعدة البيانات
        $sql = "INSERT INTO newsletter_content
                SET nc_subject = ?,
                    nc_content = ?,
                    nc_category = ?,
                    nc_country = ?,
                    nc_companies = ?,
                    nc_updated_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في قاعدة البيانات</div>';
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sssss", 
            $this->nc_subject, 
            $processedContent, 
            $categoryassigned, 
            $country, 
            $companies
        );
        
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if (!$success) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> فشل حفظ النشرة</div>';
            return false;
        }
        
        // إرسال البريد الإلكتروني للمستخدمين المستهدفين
        $this->sendEmails($country, $companies, $categoryassigned);
        
        $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> تم إرسال النشرة بنجاح</div>';
        unset($_SESSION['nc_subject'], $_SESSION['nc_content']);
        
        return true;
    }
    
    /**
     * إرسال البريد الإلكتروني
     */
    private function sendEmails(string $country, string $companies, string $categoryassigned): void {
        // بناء شرط الاستعلام
        $and = "";
        
        if (!empty($country)) {
            $and .= " AND country IN ($country)";
        }
        
        if (!empty($companies)) {
            $and .= " AND usr_id IN ($companies)";
        }
        
        if (!empty($categoryassigned)) {
            $and .= $this->buildCategoryCondition($categoryassigned);
        }
        
        // جلب المستخدمين المستهدفين
        $sql_usr = "SELECT * FROM user WHERE status='1' $and";
        $res_usr = mysqli_query($this->db, $sql_usr);
        
        if (!$res_usr) {
            error_log('خطأ في جلب المستخدمين: ' . mysqli_error($this->db));
            return;
        }
        
        $from_name = get_page_settings(4) ?: 'الموقع';
        $from_email = get_adminemail() ?: 'admin@example.com';
        
        while ($row_usr = mysqli_fetch_assoc($res_usr)) {
            // تضمين قالب البريد الإلكتروني
            $message1 = '';
            $messageFilePath = __DIR__ . "/email/newsletter-send.php";
            
            if (file_exists($messageFilePath)) {
                ob_start();
                include $messageFilePath;
                $message1 = ob_get_clean();
            }
            
            if (!empty($row_usr['email'])) {
                sendSMTPMail($row_usr['email'], $this->nc_subject ?? '', $message1);
            }
        }
    }
    
    /**
     * الحصول على الرسالة
     */
    public function getMessage(): ?string {
        return $this->msg;
    }
}

// معالجة النموذج
if (isset($_POST['btnAdd'])) {
    $adn = new AddPlan(
        trim($_POST['nc_subject'] ?? ''),
        trim($_POST['nc_content'] ?? '')
    );
    
    if ($adn->valid()) {
        $adn->add();
    }
    
    $_SESSION['msg'] = $adn->getMessage();
    header("Location: newsletter-view.php");
    exit();
}



// =============================================
// معالجة إرسال واتساب (زر btnAddWhatsApp)
// =============================================
if (isset($_POST['btnAddWhatsApp'])) {
    // 1. جلب البيانات من النموذج
    $nc_subject = trim($_POST['nc_subject'] ?? '');
    $nc_content = trim($_POST['nc_content'] ?? '');
    $categoryassigned = isset($_POST['categoryassigned']) && is_array($_POST['categoryassigned']) 
        ? implode(",", array_map('intval', $_POST['categoryassigned'])) : '';
    $country = isset($_POST['country']) && is_array($_POST['country']) 
        ? implode(",", array_map('intval', $_POST['country'])) : '';
    $companies = isset($_POST['companies']) && is_array($_POST['companies']) 
        ? implode(",", array_map('intval', $_POST['companies'])) : '';

    // 2. التحقق من صحة البيانات
    if (empty($nc_subject) || empty($nc_content)) {
        $_SESSION['msg'] = '<div class="alert alert-danger">الرجاء إدخال عنوان ومحتوى النشرة</div>';
        header("Location: newsletter-send.php");
        exit;
    }

    // 3. حفظ في قاعدة البيانات
    $sql = "INSERT INTO newsletter_content 
            SET nc_subject = ?, 
                nc_content = ?, 
                nc_category = ?, 
                nc_country = ?, 
                nc_companies = ?, 
                nc_channel = 'whatsapp',
                nc_updated_date = NOW()";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $nc_subject, $nc_content, $categoryassigned, $country, $companies);
    mysqli_stmt_execute($stmt);
    $newsletter_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

   
   
    // =============================================
    // ✅ 4. إرسال Webhook إلى Make.com
    // =============================================
    $webhook_url = 'https://hook.eu1.make.com/YOUR_WEBHOOK_ID'; // استبدل بالرابط الفعلي
    
    $payload = [
        'newsletter_id' => $newsletter_id,
        'subject' => $nc_subject,
        'message' => $nc_content,
        'channel' => 'whatsapp',
        'target' => [
            'countries' => !empty($country) ? explode(',', $country) : [],
            'categories' => !empty($categoryassigned) ? explode(',', $categoryassigned) : [],
            'companies' => !empty($companies) ? explode(',', $companies) : []
        ],
        'sender_id' => $_SESSION['ad_id_indm'] ?? 0
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 5. تسجيل النشاط
    $log_sql = "INSERT INTO activity_log (user_id, action, item_type, item_id, item_title, ip_address, created_at) 
                VALUES (?, 'whatsapp_newsletter', 'newsletter', ?, ?, ?, NOW())";
    $log_stmt = mysqli_prepare($con, $log_sql);
    $log_title = "إرسال نشرة واتساب: " . substr($nc_subject, 0, 50);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    mysqli_stmt_bind_param($log_stmt, 'iiss', $_SESSION['ad_id_indm'], $newsletter_id, $log_title, $ip);
    mysqli_stmt_execute($log_stmt);
    mysqli_stmt_close($log_stmt);

    // 6. عرض رسالة نجاح أو فشل
    if ($http_code == 200 || $http_code == 201) {
        $_SESSION['msg'] = '<div class="alert alert-success">✅ تم إرسال النشرة إلى قائمة انتظار واتساب</div>';
    } else {
        $_SESSION['msg'] = '<div class="alert alert-warning">⚠️ تم حفظ النشرة ولكن فشل الاتصال بـ Make.com</div>';
    }

    // 7. التوجيه إلى صفحة العرض
    header("Location: newsletter-view.php");
    exit();
}





// رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>



<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>

        <script type="text/javascript">
        function myvalid() {
            var nc_subject = document.getElementById('nc_subject');
            var nc_content = CKEDITOR.instances.nc_content.getData();

            var message = "";
            var valid = true;

            if (!nc_subject.value || nc_subject.value.trim() === '') {
                message = 'الرجاء إدخال عنوان النشرة';
                nc_subject.focus();
                valid = false;
            }
            else if (!nc_content || nc_content.trim() === '') {
                message = 'الرجاء إدخال محتوى النشرة';
                valid = false;
            }

            if (!valid) {
                document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> " + message;
                document.getElementById('msg').className = "alert alert-danger";
            }

            return valid;
        }
        </script>

        <?php include "includes/admin-left-con.php" ?>

        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">الرئيسية</a>
                    </li>
                    <li>
                        <a href="newsletter-view.php">إدارة النشرات</a>
                    </li>
                    <li class="active">إرسال نشرة جديدة</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        إدارة النشرات
                        <small>
                            <i class="icon-double-angle-right"></i>
                            إرسال نشرة جديدة
                        </small>
                    </h1>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" 
                              method="post" enctype="multipart/form-data" onsubmit="return myvalid();">

                            <em style="display:block;margin:5px;">
                                الحقول التي تحمل علامة <span style="color:#F00">*</span> مطلوبة
                            </em>

                            <div id="msg"><?php echo $msg; ?></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="nc_subject">
                                    العنوان <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="nc_subject" 
                                           id="nc_subject" 
                                           class="col-xs-10 col-sm-8" 
                                           type="text" 
                                           value="<?php echo htmlspecialchars($nc_subject); ?>"
                                           maxlength="255" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="categoryassigned">
                                    التصنيفات
                                </label>
                                <div class="col-sm-9">
                                    <select class="col-xs-10 col-sm-8 chosen-select" 
                                            name="categoryassigned[]" 
                                            multiple="multiple"
                                            data-placeholder="اختر التصنيفات...">
                                        <?php foreach ($cat_array as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="country">
                                    الدول
                                </label>
                                <div class="col-sm-9">
                                    <select class="col-xs-10 col-sm-8 chosen-select" 
                                            name="country[]" 
                                            multiple="multiple"
                                            data-placeholder="اختر الدول...">
                                        <?php foreach ($country_array as $country): ?>
                                            <option value="<?php echo $country['id']; ?>">
                                                <?php echo htmlspecialchars($country['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="companies">
                                    الشركات
                                </label>
                                <div class="col-sm-9">
                                    <select class="selectpicker col-xs-10 col-sm-8" 
                                            name="companies[]" 
                                            multiple
                                            data-live-search="true"
                                            data-width="100%">
                                        <?php foreach ($company_array as $company): ?>
                                            <option value="<?php echo $company['id']; ?>">
                                                <?php echo htmlspecialchars($company['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>




<!-- حقل اختيار الشركة لواتساب اليدوي -->
<div class="form-group">
    <label class="col-sm-3 control-label no-padding-right" for="wa_company">
        الشركة (لإرسال واتساب يدوي)
    </label>
    <div class="col-sm-9">
        <select class="selectpicker col-xs-10 col-sm-8" 
                name="wa_company" 
                id="wa_company"
                data-live-search="true"
                data-width="100%"
                data-size="5"
                data-none-selected-text="اختر شركة لإرسال واتساب...">
            <option value="">-- اختر شركة --</option>
            <?php foreach ($company_array_with_phones as $company): ?>
                <option value="<?php echo $company['id']; ?>" 
                        data-phone="<?php echo $company['phone']; ?>">
                    <?php echo htmlspecialchars($company['name']); ?>
                    <?php if (!empty($company['phone'])): ?>
                        (<?php echo htmlspecialchars($company['phone']); ?>)
                    <?php endif; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="help-block">اختر الشركة التي تريد إرسال رسالة واتساب لها</span>
    </div>
</div>






                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="nc_content">
                                    المحتوى <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <textarea name="nc_content" id="nc_content"><?php echo htmlspecialchars(stripslashes($nc_content)); ?></textarea>
                               
                               
                                
        <!-- زر الإرسال الأصلي (بريد إلكتروني) - لم نلمسه -->
        <button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd">
            <i class="icon-ok bigger-110"></i>
            إرسال
        </button>
        
        <!-- ✅ زر جديد: إرسال النشرة إلى واتساب (يدوي) -->
        <button type="button" class="btn btn-success" onclick="sendManualWhatsApp()">
            <i class="fa fa-whatsapp bigger-110"></i>
            إرسال واتساب
        </button>
        
        <button class="btn" type="reset">
            <i class="icon-undo bigger-110"></i>
            إعادة تعيين
        </button>
    </div>
</div>
                            </div>
                        </form>
                    </div>
                </div>

                <br clear="all" />
            </div>
        </div>
    </div>
</div>



<!-- ============================================= -->
<!-- Modal إرسال واتساب يدوي -->
<!-- ============================================= -->
<div id="waModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; width:450px; max-width:90%; margin:100px auto; padding:25px; border-radius:12px; direction:rtl; position:relative;">
        <span onclick="closeWaModal()" style="position:absolute; top:10px; left:15px; cursor:pointer; font-size:28px; font-weight:bold; color:#999;">&times;</span>
        <h3 style="color:#25D366; margin-bottom:15px;">
            <i class="fa fa-whatsapp"></i> إرسال رسالة واتساب
        </h3>
        
        <div style="margin-bottom:10px;">
            <label style="font-weight:bold; display:block; margin-bottom:5px;">نص الرسالة:</label>
            <textarea id="waMessageText" rows="8" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px; background:#f9f9f9;" readonly></textarea>
        </div>
        
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin:15px 0;">
            <button onclick="copyWaMessage()" class="btn btn-primary" style="padding:8px 20px;">
                <i class="fa fa-copy"></i> نسخ النص
            </button>
            <a id="waLinkBtn" href="#" target="_blank" class="btn btn-success" 
               style="background:#25D366; color:white; padding:8px 20px; border-radius:4px; text-decoration:none;">
               <i class="fa fa-whatsapp"></i> فتح واتساب
            </a>
        </div>
        
        <div style="background:#e8f4f8; padding:10px; border-radius:6px; margin-top:10px;">
            <p style="margin:5px 0; font-size:13px;">
                <strong>📱 على الجوال:</strong> اضغط "فتح واتساب" ← يفتح التطبيق مباشرة<br>
                <strong>💻 على الكمبيوتر:</strong> اضغط "نسخ النص" ثم الصقه في واتساب ويب
            </p>
        </div>
    </div>
</div>





<?php include "includes/footer.php" ?>

<script type="text/javascript">
window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!--[if IE]>
<script type="text/javascript">
window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>
<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="assets/js/chosen.jquery.min.js"></script>
<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
<script src="assets/js/date-time/moment.min.js"></script>
<script src="assets/js/date-time/daterangepicker.min.js"></script>
<script src="assets/js/bootstrap-colorpicker.min.js"></script>
<script src="assets/js/jquery.knob.min.js"></script>
<script src="assets/js/jquery.autosize.min.js"></script>
<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="assets/js/jquery.maskedinput.min.js"></script>
<script src="assets/js/bootstrap-tag.min.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>
<script src="ckeditor/ckeditor.js"></script>
<script src="assets/js/markdown/markdown.min.js"></script>
<script src="assets/js/markdown/bootstrap-markdown.min.js"></script>
<script src="assets/js/jquery.hotkeys.min.js"></script>
<script src="assets/js/bootstrap-wysiwyg.min.js"></script>
<script src="assets/js/bootbox.min.js"></script>

<!-- Bootstrap Select -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/js/bootstrap-select.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    // تفعيل Chosen
    $(".chosen-select").chosen({
        no_results_text: "لا توجد نتائج",
        placeholder_text_multiple: "اختر الخيارات...",
        width: "100%"
    });
    
    // تفعيل Bootstrap Select
    $('#companies').selectpicker({
        noneSelectedText: 'اختر الشركات...',
        noneResultsText: 'لا توجد نتائج',
        countSelectedText: '{0} شركة محددة',
        maxOptionsText: ['الحد الأقصى', 'الحد الأقصى'],
        selectAllText: 'اختر الكل',
        deselectAllText: 'إلغاء الكل',
        doneButtonText: 'تم',
        liveSearch: true,
        liveSearchPlaceholder: 'بحث...'
    });
    
    // تفعيل CKEditor
    CKEDITOR.replace('nc_content', {
        extraPlugins: 'imageuploader',
        language: 'ar',
        height: 400,
        toolbarGroups: [
            { name: 'document', groups: ['mode', 'document', 'doctools'] },
            { name: 'clipboard', groups: ['clipboard', 'undo'] },
            { name: 'editing', groups: ['find', 'selection', 'spellchecker'] },
            { name: 'forms' },
            '/',
            { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
            { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'] },
            { name: 'links' },
            { name: 'insert' },
            '/',
            { name: 'styles' },
            { name: 'colors' },
            { name: 'tools' },
            { name: 'others' }
        ]
    });
    
    // إخفاء رسالة النجاح بعد 5 ثوان
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
});

// =============================================
// دوال واتساب اليدوي (مثل admin/offers.php)
// =============================================
function sendManualWhatsApp() {
    var subject = document.getElementById('nc_subject').value;
    var content = CKEDITOR.instances.nc_content.getData();
    var companySelect = document.getElementById('wa_company');
    var selectedOption = companySelect.options[companySelect.selectedIndex];
    var companyName = selectedOption?.text || '';
    var companyPhone = selectedOption?.getAttribute('data-phone') || '';

    if (!companyPhone) {
        alert('❌ الرجاء اختيار شركة لديها رقم هاتف صحيح');
        return;
    }

    // تنظيف المحتوى من HTML
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = content;
    var plainText = tempDiv.textContent || tempDiv.innerText || '';

    // بناء الرسالة
    var message = '📢 *' + subject + '*\n\n';
    message += plainText;

    // فتح النافذة مع الرقم
    openWaModal(message, companyPhone);
}

function openWaModal(message, phone = '') {
    document.getElementById('waMessageText').value = message;
    var encodedMessage = encodeURIComponent(message);
    var waUrl = 'https://wa.me/';
    if (phone && phone != '') {
        waUrl += phone + '?text=' + encodedMessage;
    } else {
        waUrl += '?text=' + encodedMessage;
    }
    document.getElementById('waLinkBtn').href = waUrl;
    document.getElementById('waModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeWaModal() {
    document.getElementById('waModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function copyWaMessage() {
    var textarea = document.getElementById('waMessageText');
    textarea.select();
    textarea.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(textarea.value).then(function() {
        alert('✅ تم نسخ النص بنجاح!');
    }).catch(function() {
        document.execCommand('copy');
        alert('✅ تم نسخ النص بنجاح!');
    });
}

// إغلاق النافذة عند الضغط خارجها أو على Escape
document.addEventListener('click', function(event) {
    var modal = document.getElementById('waModal');
    if (event.target === modal) closeWaModal();
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeWaModal();
});


</script>

</body>
</html>