<?php
/**
 * File: payment_methods.php
 * Version: 2.0.0
 * Description: إدارة طرق الدفع (إضافة - تعديل - حذف - عرض)
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
 * Class PaymentMethodsManager - إدارة طرق الدفع
 */
class PaymentMethodsManager {
    private mysqli $db;
    private string $table = 'payment_method';
    
    /**
     * المُنشئ
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * جلب جميع طرق الدفع
     */
    public function getAllMethods() {
        $sql = "SELECT * FROM {$this->table} ORDER BY ph_id ASC";
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * جلب طريقة دفع محددة
     */
    public function getMethodById(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE ph_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row ?: null;
    }
    
    /**
     * إضافة طريقة دفع جديدة
     */
    public function addMethod(string $name): bool {
        if ($this->isEmpty($name)) {
            return false;
        }
        
        $sql = "INSERT INTO {$this->table} (ph_title, ph_status) VALUES (?, 1)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $name);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * تحديث طريقة دفع
     */
    public function updateMethod(int $id, string $name): bool {
        if ($this->isEmpty($name) || $id <= 0) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET ph_title = ? WHERE ph_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $name, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * حذف طريقة دفع
     */
    public function deleteMethod(int $id): bool {
        if ($id <= 0) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE ph_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * التحقق من النص الفارغ
     */
    private function isEmpty(string $str): bool {
        return trim($str) === '';
    }
    
    /**
     * تنظيف النص للإخراج
     */
    public function sanitize(?string $str): string {
        return htmlspecialchars(trim($str ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// تهيئة المدير
$manager = new PaymentMethodsManager($con);

// معالجة الحذف
if (isset($_GET['delete'])) {
    $id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    if ($id !== false && $id > 0) {
        if ($manager->deleteMethod($id)) {
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم الحذف بنجاح</div>';
        }
    }
    header("Location: payment_methods.php");
    exit();
}

// معالجة الإضافة
if (isset($_POST['save_mes'])) {
    $name = trim($_POST['payment_method'] ?? '');
    if (!empty($name)) {
        if ($manager->addMethod($name)) {
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تمت الإضافة بنجاح</div>';
        }
    } else {
        $_SESSION['msg'] = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال اسم طريقة الدفع</div>';
    }
    header("Location: payment_methods.php");
    exit();
}

// معالجة التحديث
if (isset($_POST['update_mes'])) {
    $name = trim($_POST['payment_method'] ?? '');
    $id = filter_input(INPUT_POST, 'payment_method_id', FILTER_VALIDATE_INT);
    
    if (!empty($name) && $id !== false && $id > 0) {
        if ($manager->updateMethod($id, $name)) {
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم التحديث بنجاح</div>';
        }
    } else {
        $_SESSION['msg'] = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال اسم طريقة الدفع</div>';
    }
    header("Location: payment_methods.php");
    exit();
}

// جلب بيانات التعديل
$editData = null;
if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId !== false && $editId > 0) {
        $editData = $manager->getMethodById($editId);
        if (!$editData) {
            $_SESSION['msg'] = '<div class="alert alert-warning"><i class="icon-warning"></i> طريقة الدفع غير موجودة</div>';
            header("Location: payment_methods.php");
            exit();
        }
    }
}

// جلب جميع طرق الدفع
$records = $manager->getAllMethods();
$totalCount = $records ? mysqli_num_rows($records) : 0;

// رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>

<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' ,'fixed')}catch(e){}
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
                    <li class="active">طرق الدفع</li>
                </ul>
            </div>
            
            <div class="page-content">
                
                <?php if ($msg): ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <?php echo $msg; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- نموذج الإضافة/التعديل -->
                    <div class="col-xs-12">
                        <div class="page-header">
                            <h1>
                                طرق الدفع
                                <small>
                                    <i class="icon-double-angle-right"></i>
                                    <?php echo $editData ? 'تعديل' : 'إضافة جديدة'; ?>
                                </small>
                            </h1>
                        </div>
                        
                        <form method="post" class="form-horizontal" id="paymentForm">
                            <div class="form-group">
                                <label class="col-sm-2 control-label no-padding-right">
                                    اسم طريقة الدفع
                                </label>
                                <div class="col-sm-8">
                                    <?php if ($editData): ?>
                                        <input type="hidden" name="payment_method_id" 
                                               value="<?php echo (int)($editData['ph_id'] ?? 0); ?>">
                                    <?php endif; ?>
                                    
                                    <input type="text" class="form-control" 
                                           placeholder="أدخل اسم طريقة الدفع" 
                                           name="payment_method" 
                                           id="payment_method"
                                           value="<?php echo $editData ? $manager->sanitize($editData['ph_title'] ?? '') : ''; ?>" 
                                           required
                                           maxlength="100">
                                </div>
                                <div class="col-sm-2">
                                    <?php if ($editData): ?>
                                        <button type="submit" class="btn btn-primary" name="update_mes">
                                            <i class="icon-ok"></i> تحديث
                                        </button>
                                        <a href="payment_methods.php" class="btn btn-default">
                                            <i class="icon-remove"></i> إلغاء
                                        </a>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-success" name="save_mes">
                                            <i class="icon-plus"></i> إضافة
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <br>
                    
                    <!-- قائمة طرق الدفع -->
                    <div class="col-xs-12">
                        <div class="page-header">
                            <h1>
                                قائمة طرق الدفع
                                <small>
                                    <i class="icon-double-angle-right"></i>
                                    الإجمالي: <?php echo $totalCount; ?>
                                </small>
                            </h1>
                        </div>
                        
                        <div class="table-responsive">
                            <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="center">#</th>
                                        <th>الاسم</th>
                                        <th>الحالة</th>
                                        <th class="center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($records && $totalCount > 0): ?>
                                        <?php mysqli_data_seek($records, 0); ?>
                                        <?php while ($row = mysqli_fetch_assoc($records)): ?>
                                            <tr>
                                                <td class="center"><?php echo (int)($row['ph_id'] ?? 0); ?></td>
                                                <td>
                                                    <?php echo $manager->sanitize($row['ph_title'] ?? ''); ?>
                                                </td>
                                                <td>
                                                    <?php if (isset($row['ph_status']) && $row['ph_status'] == 1): ?>
                                                        <span class="label label-success">فعال</span>
                                                    <?php else: ?>
                                                        <span class="label label-default">غير فعال</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="center">
                                                    <div class="btn-group">
                                                        <a href="?edit=<?php echo (int)($row['ph_id'] ?? 0); ?>" 
                                                           class="btn btn-xs btn-info" title="تعديل">
                                                            <i class="icon-edit bigger-120"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo (int)($row['ph_id'] ?? 0); ?>" 
                                                           class="btn btn-xs btn-danger" title="حذف"
                                                           onclick="return confirm('هل أنت متأكد من حذف طريقة الدفع هذه؟');">
                                                            <i class="icon-trash bigger-120"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                لا توجد طرق دفع. 
                                                <a href="#" onclick="document.getElementById('payment_method').focus(); return false;">
                                                    أضف أول طريقة دفع
                                                </a>.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br clear="all"/>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript files -->
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
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
    jQuery(function($) {
        // تفعيل DataTable مع ترجمة
        var oTable = $('#sample-table-2').dataTable({
            "aoColumns": [
                { "bSortable": true },
                null,
                { "bSortable": false },
                { "bSortable": false }
            ],
            "oLanguage": {
                "sSearch": "بحث:",
                "sLengthMenu": "عرض _MENU_ عنصر في الصفحة",
                "sInfo": "عرض _START_ إلى _END_ من _TOTAL_ عنصر",
                "sInfoEmpty": "عرض 0 إلى 0 من 0 عنصر",
                "sInfoFiltered": "(مرشح من _MAX_ عنصر)",
                "sZeroRecords": "لا توجد نتائج",
                "oPaginate": {
                    "sFirst": "الأول",
                    "sPrevious": "السابق",
                    "sNext": "التالي",
                    "sLast": "الأخير"
                }
            }
        });
        
        // التركيز على حقل الإدخال
        <?php if (!$editData): ?>
            $('#payment_method').focus();
        <?php endif; ?>
        
        // التحقق الفوري
        $('#payment_method').on('keyup', function() {
            var value = $(this).val().trim();
            var formGroup = $(this).closest('.form-group');
            
            if (value === '') {
                formGroup.removeClass('has-success').addClass('has-error');
            } else {
                formGroup.removeClass('has-error').addClass('has-success');
            }
        });
        
        // التحقق عند الإرسال
        $('#paymentForm').on('submit', function(e) {
            var value = $('#payment_method').val().trim();
            if (value === '') {
                e.preventDefault();
                alert('الرجاء إدخال اسم طريقة الدفع');
                $('#payment_method').focus();
            }
        });
        
        // إخفاء رسائل النجاح بعد 5 ثوان
        setTimeout(function() {
            $('.alert-success').fadeOut('slow');
        }, 5000);
    });
</script>

<style>
    .btn-group {
        display: flex;
        gap: 5px;
        justify-content: center;
    }
    
    .btn-group .btn {
        border-radius: 3px !important;
        padding: 2px 8px;
    }
    
    .has-error input {
        border-color: #d15b47 !important;
    }
    
    .has-success input {
        border-color: #82af6f !important;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .page-header {
        margin-top: 0;
        padding-bottom: 9px;
        border-bottom: 1px solid #eee;
    }
    
    .label {
        font-size: 12px;
        padding: 3px 8px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>