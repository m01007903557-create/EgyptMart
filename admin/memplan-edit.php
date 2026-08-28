<?php
/**
 * File: memplan-edit.php
 * Version: 2.0.0
 * Description: تعديل خطط العضوية (الاسم - الرصيد - السعر) (تمت الترقية إلى PHP 8.3)
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

// التحقق من تسجيل الدخول
check_admin_login();

/**
 * Class EditProduct - تعديل بيانات خطة العضوية
 */
class EditProduct {
    private ?string $msg = null;
    private int $mp_id;
    private ?string $mp_name;
    private ?string $mp_credits;
    private ?string $mp_amount;
    private mysqli $db;
    
    // الثوابت
    private const CREDITS_MULTIPLE = 20;
    
    /**
     * المُنشئ
     */
    public function __construct(int $mp_id, ?mysqli $databaseConnection = null) {
        global $con;
        $this->mp_id = $mp_id;
        $this->db = $databaseConnection ?? $con;
    }
    
    /**
     * جلب تفاصيل الخطة
     */
    public function detailsObj(): ?object {
        $sql = "SELECT * FROM membership_plan WHERE mp_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($this->db));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->mp_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        
        mysqli_stmt_close($stmt);
        
        return $row ?: null;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        $this->msg = null;
        
        if (empty($this->mp_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال اسم الخطة</div>';
            return false;
        }
        
        if (empty($this->mp_credits) || trim($this->mp_credits) === '' || $this->mp_credits == '0') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال عدد الرصيد</div>';
            return false;
        }
        
        if (!is_numeric($this->mp_credits)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال رقم صحيح للرصيد</div>';
            return false;
        }
        
        $credits = (int)$this->mp_credits;
        if ($credits % self::CREDITS_MULTIPLE !== 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> يجب أن يكون الرصيد من مضاعفات 20</div>';
            return false;
        }
        
        if (empty($this->mp_amount) || trim($this->mp_amount) === '') {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال سعر الخطة</div>';
            return false;
        }
        
        if (!is_numeric($this->mp_amount)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال رقم صحيح للسعر</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * تحديث الخطة
     */
    public function update(): bool {
        if (!$this->valid()) {
            return false;
        }
        
        $sql = "UPDATE membership_plan 
                SET mp_name = ?,
                    mp_credits = ?,
                    mp_amount = ?,
                    mp_updated_date = NOW()
                WHERE mp_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في قاعدة البيانات</div>';
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sidi", 
            $this->mp_name,
            $this->mp_credits,
            $this->mp_amount,
            $this->mp_id
        );
        
        $success = mysqli_stmt_execute($stmt);
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        
        if ($success) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> تم تحديث الخطة بنجاح</div>';
            return true;
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> فشل التحديث: ' . $error . '</div>';
            return false;
        }
    }
    
    /**
     * تعيين اسم الخطة
     */
    public function setMpName(?string $mp_name): self {
        $this->mp_name = $mp_name ? trim($mp_name) : null;
        return $this;
    }
    
    /**
     * تعيين الرصيد
     */
    public function setMpCredits(?string $mp_credits): self {
        $this->mp_credits = $mp_credits ? trim($mp_credits) : null;
        return $this;
    }
    
    /**
     * تعيين السعر
     */
    public function setMpAmount(?string $mp_amount): self {
        $this->mp_amount = $mp_amount ? trim($mp_amount) : null;
        return $this;
    }
    
    /**
     * الحصول على الرسالة
     */
    public function getMessage(): ?string {
        return $this->msg;
    }
}

// التحقق من وجود المعرف
$fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
if (!$fid || $fid <= 0) {
    header("Location: memplan-view.php");
    exit();
}

// إنشاء الكائن وجلب التفاصيل
$ob = new EditProduct($fid);
$row = $ob->detailsObj();

if (!$row) {
    $_SESSION['msg'] = '<div class="alert alert-danger"><i class="icon-remove"></i> الخطة غير موجودة</div>';
    header("Location: memplan-view.php");
    exit();
}

// معالجة النموذج
if (isset($_POST['btnUpdate'])) {
    $ob->setMpName($_POST['mp_name'] ?? '')
       ->setMpCredits($_POST['mp_credits'] ?? '')
       ->setMpAmount($_POST['mp_amount'] ?? '');
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    $_SESSION['msg'] = $ob->getMessage();
    header("Location: memplan-edit.php?fid=" . $fid);
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
            var mp_name = document.getElementById('mp_name');
            var mp_credits = document.getElementById('mp_credits');
            var mp_amount = document.getElementById('mp_amount');
            
            var message = "";
            var valid = true;
            
            if (!mp_name.value || mp_name.value.trim() === '') {
                message = 'الرجاء إدخال اسم الخطة';
                mp_name.focus();
                valid = false;
            }
            else if (!mp_credits.value || mp_credits.value.trim() === '') {
                message = 'الرجاء إدخال عدد الرصيد';
                mp_credits.focus();
                valid = false;
            }
            else if (isNaN(mp_credits.value)) {
                message = 'الرجاء إدخال رقم صحيح للرصيد';
                mp_credits.value = '';
                mp_credits.focus();
                valid = false;
            }
            else if (parseInt(mp_credits.value) % 20 !== 0) {
                message = 'يجب أن يكون الرصيد من مضاعفات 20';
                mp_credits.value = '';
                mp_credits.focus();
                valid = false;
            }
            else if (!mp_amount.value || mp_amount.value.trim() === '') {
                message = 'الرجاء إدخال سعر الخطة';
                mp_amount.focus();
                valid = false;
            }
            else if (isNaN(mp_amount.value)) {
                message = 'الرجاء إدخال رقم صحيح للسعر';
                mp_amount.value = '';
                mp_amount.focus();
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
                        <a href="memplan-view.php">إدارة خطط العضوية</a>
                    </li>
                    <li class="active">تعديل الخطة</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        إدارة خطط العضوية
                        <small>
                            <i class="icon-double-angle-right"></i>
                            تعديل الخطة
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
                                <label class="col-sm-3 control-label no-padding-right" for="mp_name">
                                    اسم الخطة <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="mp_name" 
                                           id="mp_name" 
                                           class="col-xs-10 col-sm-5" 
                                           type="text" 
                                           value="<?php echo htmlspecialchars($row->mp_name ?? ''); ?>"
                                           maxlength="100" />
                                    <input type="hidden" name="mp_id" id="mp_id" value="<?php echo (int)$row->mp_id; ?>" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="mp_credits">
                                    الرصيد <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="mp_credits" 
                                           id="mp_credits" 
                                           class="col-xs-10 col-sm-5" 
                                           type="number" 
                                           step="20"
                                           min="20"
                                           value="<?php echo (int)($row->mp_credits ?? 0); ?>" />
                                    <small class="text-muted">(يجب أن يكون من مضاعفات 20)</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="mp_amount">
                                    سعر الخطة <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="mp_amount" 
                                           id="mp_amount" 
                                           class="col-xs-10 col-sm-5" 
                                           type="number" 
                                           step="0.01"
                                           min="0"
                                           value="<?php echo htmlspecialchars((string)($row->mp_amount ?? '0')); ?>" />
                                </div>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate">
                                        <i class="icon-ok bigger-110"></i>
                                        تحديث
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i>
                                        إعادة تعيين
                                    </button>
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

<script type="text/javascript">
jQuery(function($) {
    // تفعيل اختيارات Chosen
    $(".chosen-select").chosen();
    
    // تفعيل tooltips
    $('[data-rel=tooltip]').tooltip({container:'body'});
    
    // تفعيل popovers
    $('[data-rel=popover]').popover({container:'body'});
    
    // إخفاء رسالة النجاح بعد 5 ثوان
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
});
</script>

</body>
</html>