<?php
/**
 * File: membership-requirement.php
 * Version: 2.0.0
 * Description: عرض تفاصيل طلب العضوية مع إمكانية الرد (تمت الترقية إلى PHP 8.3)
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
check_user_login();

/**
 * Class MembershipReqDetails - عرض تفاصيل طلب العضوية
 */
class MembershipReqDetails {
    private int $mp_req_id;
    private mysqli $db;
    
    /**
     * المُنشئ
     */
    public function __construct(int $mp_req_id, ?mysqli $databaseConnection = null) {
        global $con;
        $this->mp_req_id = $mp_req_id;
        $this->db = $databaseConnection ?? $con;
        
        // تعيين ترميز UTF-8
        $this->setUtf8Encoding();
    }
    
    /**
     * تعيين ترميز UTF-8
     */
    private function setUtf8Encoding(): void {
        mysqli_query($this->db, "SET NAMES 'utf8'");
        mysqli_query($this->db, "SET CHARACTER SET utf8");
    }
    
    /**
     * تحديث حالة الفتح وجلب التفاصيل
     */
    public function detailsObj(): ?object {
        // تحديث حالة الفتح
        $updateSql = "UPDATE membership_requirements SET opened = 1 WHERE mp_req_id = ?";
        $updateStmt = mysqli_prepare($this->db, $updateSql);
        
        if ($updateStmt) {
            mysqli_stmt_bind_param($updateStmt, "i", $this->mp_req_id);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);
        }
        
        // جلب التفاصيل
        $selectSql = "SELECT * FROM membership_requirements WHERE mp_req_id = ?";
        $selectStmt = mysqli_prepare($this->db, $selectSql);
        
        if (!$selectStmt) {
            error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($this->db));
            return null;
        }
        
        mysqli_stmt_bind_param($selectStmt, "i", $this->mp_req_id);
        mysqli_stmt_execute($selectStmt);
        $result = mysqli_stmt_get_result($selectStmt);
        $row = mysqli_fetch_object($result);
        
        mysqli_stmt_close($selectStmt);
        
        return $row ?: null;
    }
    
    /**
     * تنسيق اسم العضوية
     */
    public function formatMembershipPlans(?string $mp_id): string {
        if (empty($mp_id)) {
            return 'غير محدد';
        }
        
        if ($mp_id === 'Advertisement Request') {
            return 'طلبات الإعلانات';
        }
        
        $planIds = array_map('intval', explode(',', $mp_id));
        $plans = [];
        
        foreach ($planIds as $planId) {
            $sql = "SELECT mst_name FROM smembership_icon_plan WHERE mp_id = ?";
            $stmt = mysqli_prepare($this->db, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $planId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                
                if ($row) {
                    $planName = $row['mst_name'];
                    // تحويل "VERIFIED member" إلى "Junior Member"
                    if ($planName === "VERIFIED member") {
                        $planName = "Junior Member";
                    }
                    $plans[] = $planName;
                }
                
                mysqli_stmt_close($stmt);
            }
        }
        
        return !empty($plans) ? implode('، ', $plans) : 'غير محدد';
    }
    
    /**
     * تنظيف النص من الترميزات
     */
    public function cleanText(?string $text): string {
        if (empty($text)) {
            return '';
        }
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }
}

// التحقق من وجود المعرف
$fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
if (!$fid || $fid <= 0) {
    header("Location: membership-requirements-view.php");
    exit();
}

// إنشاء الكائن وجلب التفاصيل
$ob = new MembershipReqDetails($fid);
$row = $ob->detailsObj();

// التحقق من وجود البيانات
if (!$row) {
    $_SESSION['error_msg'] = 'الطلب غير موجود';
    header("Location: membership-requirements-view.php");
    exit();
}

// معالجة الأزرار
if (isset($_POST['btnBack'])) {
    header("Location: membership-requirements-view.php");
    exit();
} elseif (isset($_POST['btnReply'])) {
    header("Location: admin-reply.php?fid=" . $fid);
    exit();
}

// تنسيق خطط العضوية
$plans = $ob->formatMembershipPlans($row->mp_id ?? '');
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
                        <a href="membership-requirements-view.php">طلبات العضوية</a>
                    </li>
                    <li class="active">تفاصيل الطلب</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        طلبات العضوية
                        <small>
                            <i class="icon-double-angle-right"></i>
                            تفاصيل الطلب
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" name="fd_view" id="fd_view" method="post">
                            
                            <!-- الاسم -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">الاسم:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php echo htmlspecialchars(ucfirst($row->name ?? '')); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- اسم الشركة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">اسم الشركة:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php echo htmlspecialchars(ucfirst($row->company_name ?? '')); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- البريد الإلكتروني -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">البريد الإلكتروني:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <a href="mailto:<?php echo htmlspecialchars($row->email ?? ''); ?>">
                                            <?php echo htmlspecialchars($row->email ?? ''); ?>
                                        </a>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- رقم الجوال -->
                            <?php if (!empty($row->mobile)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">رقم الجوال:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php echo htmlspecialchars($row->mobile); ?>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- الدولة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">الدولة:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php echo htmlspecialchars($row->country ?? ''); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- المدينة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">المدينة:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php echo htmlspecialchars($row->city ?? ''); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- العنوان -->
                            <?php if (!empty($row->address)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">العنوان:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php echo htmlspecialchars($row->address); ?>
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- المتطلبات -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">المتطلبات:</label>
                                <div class="col-sm-8">
                                    <div style="padding-top:4px; font-weight:normal; border:1px solid #ddd; padding:10px; border-radius:4px; background:#f9f9f9;">
                                        <?php echo nl2br(htmlspecialchars($ob->cleanText($row->requirement ?? ''))); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- خطط العضوية المختارة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">خطط العضوية المختارة:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php echo htmlspecialchars($plans); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- تاريخ الإرسال -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">تاريخ الإرسال:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php 
                                        if (!empty($row->submitted_date)) {
                                            echo date('Y-m-d H:i:s', strtotime($row->submitted_date));
                                        } else {
                                            echo 'غير محدد';
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- حالة الفتح -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">حالة المشاهدة:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px; font-weight:normal;">
                                        <?php if (!empty($row->opened)): ?>
                                            <span class="label label-success">تمت المشاهدة</span>
                                        <?php else: ?>
                                            <span class="label label-warning">جديد</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- الأزرار -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnBack" id="btnBack">
                                        <i class="icon-reply icon-only"></i>&nbsp;رجوع
                                    </button>
                                    <button class="btn btn-success" type="submit" name="btnReply" id="btnReply">
                                        <i class="icon-envelope icon-only"></i>&nbsp;رد
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
    
    // تفعيل auto-resize للنصوص
    $('textarea[class*=autosize]').autosize({append: "\n"});
    
    // تفعيل datepicker
    $('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });
});
</script>

</body>
</html>