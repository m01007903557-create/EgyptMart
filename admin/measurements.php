<?php
/**
 * ملف إدارة وحدات القياس (Measurement Units)
 * 
 * @filename    measurements.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن إدارة وحدات القياس (إضافة، تعديل، حذف، عرض)
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء تشغيل المخزن المؤقت والجلسة
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// التحقق من صلاحيات المستخدم
check_admin_login();

/**
 * دالة التحقق من النص الفارغ
 * @param string $str
 * @return bool
 */
function checkEmpty($str) {
    $check_empty = preg_replace('/\s+/', '', $str);
    return $check_empty === "";
}

/**
 * دالة تنظيف المدخلات
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}

/**
 * دالة التحقق من وجود وحدة قياس مكررة
 * @param string $name
 * @param int $exclude_id
 * @return bool
 */
function isDuplicateMeasurement($con, $name, $exclude_id = 0) {
    $sql = "SELECT mu_id FROM measurement_unit WHERE mu_name = ? AND mu_id != ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $name, $exclude_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $count = mysqli_stmt_num_rows($stmt);
    mysqli_stmt_close($stmt);
    
    return $count > 0;
}

// معالجة الحذف
if (isset($_GET['delete'])) {
    $id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    
    if ($id && $id > 0) {
        // التحقق من عدم استخدام وحدة القياس في منتجات
        $check_sql = "SELECT COUNT(*) as count FROM products WHERE product_measurement_unit = ?";
        $check_stmt = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $check_row = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($check_stmt);
        
        if ($check_row['count'] == 0) {
            $delete_sql = "DELETE FROM measurement_unit WHERE mu_id = ?";
            $delete_stmt = mysqli_prepare($con, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, "i", $id);
            mysqli_stmt_execute($delete_stmt);
            mysqli_stmt_close($delete_stmt);
            
            $_SESSION['success_msg'] = "تم حذف وحدة القياس بنجاح";
        } else {
            $_SESSION['error_msg'] = "لا يمكن حذف وحدة القياس لأنها مستخدمة في منتجات";
        }
    }
    
    header("Location: measurements.php");
    exit();
}

// معالجة إضافة وحدة قياس جديدة
if (isset($_POST['save_mes'])) {
    $name = sanitizeInput($_POST['measurement'] ?? '');
    
    if (!checkEmpty($name)) {
        if (!isDuplicateMeasurement($con, $name)) {
            $insert_sql = "INSERT INTO measurement_unit (mu_name, mu_status, mu_created_date) VALUES (?, '1', NOW())";
            $insert_stmt = mysqli_prepare($con, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "s", $name);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $_SESSION['success_msg'] = "تم إضافة وحدة القياس بنجاح";
            } else {
                $_SESSION['error_msg'] = "حدث خطأ أثناء إضافة وحدة القياس";
            }
            mysqli_stmt_close($insert_stmt);
        } else {
            $_SESSION['error_msg'] = "وحدة القياس موجودة مسبقاً";
        }
    } else {
        $_SESSION['error_msg'] = "الرجاء إدخال اسم وحدة القياس";
    }
    
    header("Location: measurements.php");
    exit();
}

// معالجة تحديث وحدة قياس
if (isset($_POST['update_mes'])) {
    $name = sanitizeInput($_POST['measurement'] ?? '');
    $id = filter_input(INPUT_POST, 'measurement_id', FILTER_VALIDATE_INT);
    
    if (!checkEmpty($name) && $id && $id > 0) {
        if (!isDuplicateMeasurement($con, $name, $id)) {
            $update_sql = "UPDATE measurement_unit SET mu_name = ?, mu_updated_date = NOW() WHERE mu_id = ?";
            $update_stmt = mysqli_prepare($con, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "si", $name, $id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $_SESSION['success_msg'] = "تم تحديث وحدة القياس بنجاح";
            } else {
                $_SESSION['error_msg'] = "حدث خطأ أثناء تحديث وحدة القياس";
            }
            mysqli_stmt_close($update_stmt);
        } else {
            $_SESSION['error_msg'] = "وحدة القياس موجودة مسبقاً";
        }
    } else {
        $_SESSION['error_msg'] = "بيانات غير صحيحة";
    }
    
    header("Location: measurements.php");
    exit();
}

// جلب جميع وحدات القياس
$recObj = mysqli_query($con, "SELECT * FROM measurement_unit ORDER BY mu_id ASC");

// عرض رسائل النجاح/الخطأ
$success_msg = '';
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

$error_msg = '';
if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}
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
                    <li class="active">إدارة وحدات القياس</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        <i class="icon-ruler"></i> وحدات القياس
                        <small>
                            <i class="icon-double-angle-right"></i>
                            إضافة وتعديل وحذف وحدات القياس
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <!-- رسائل النظام -->
                    <?php if ($success_msg): ?>
                        <div class="alert alert-success">
                            <i class="icon-ok"></i> <?php echo htmlspecialchars($success_msg); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger">
                            <i class="icon-remove"></i> <?php echo htmlspecialchars($error_msg); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- نموذج الإضافة/التعديل -->
                    <div class="col-xs-12">
                        <div class="widget-box">
                            <div class="widget-header">
                                <h4 class="widget-title">
                                    <?php echo isset($_GET['edit']) ? 'تعديل وحدة قياس' : 'إضافة وحدة قياس جديدة'; ?>
                                </h4>
                            </div>
                            
                            <div class="widget-body">
                                <div class="widget-main">
                                    <form method="post" class="form-inline">
                                        <?php if (isset($_GET['edit'])): ?>
                                            <?php 
                                                $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
                                                if ($edit_id) {
                                                    $query = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_id = '$edit_id'");
                                                    $fetch = mysqli_fetch_array($query);
                                                }
                                            ?>
                                            <?php if ($fetch): ?>
                                                <input type="hidden" name="measurement_id" value="<?php echo $fetch['mu_id']; ?>">
                                                <div class="input-group" style="width: 100%;">
                                                    <input type="text" class="form-control" 
                                                           placeholder="أدخل اسم وحدة القياس" 
                                                           name="measurement" 
                                                           value="<?php echo htmlspecialchars($fetch['mu_name']); ?>" 
                                                           required>
                                                    <span class="input-group-btn">
                                                        <button type="submit" class="btn btn-primary" name="update_mes">
                                                            <i class="icon-save"></i> تحديث
                                                        </button>
                                                        <a href="measurements.php" class="btn btn-default">
                                                            <i class="icon-remove"></i> إلغاء
                                                        </a>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="input-group" style="width: 100%;">
                                                <input type="text" class="form-control" 
                                                       placeholder="أدخل اسم وحدة القياس" 
                                                       name="measurement" 
                                                       required>
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-success" name="save_mes">
                                                        <i class="icon-plus"></i> إضافة
                                                    </button>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xs-12" style="margin-top: 20px;">
                        <div class="table-responsive">
                            <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="center">#</th>
                                        <th>اسم وحدة القياس</th>
                                        <th class="center">الحالة</th>
                                        <th class="center">العمليات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $count = mysqli_num_rows($recObj);
                                    if ($count > 0):
                                        $j = 1;
                                        while($row = mysqli_fetch_object($recObj)):
                                    ?>
                                        <tr>
                                            <td class="center"><?php echo $j; ?></td>
                                            <td><?php echo htmlspecialchars($row->mu_name); ?></td>
                                            <td class="center">
                                                <?php if ($row->mu_status == '1'): ?>
                                                    <span class="label label-success">نشط</span>
                                                <?php else: ?>
                                                    <span class="label label-default">غير نشط</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="center">
                                                <div class="btn-group">
                                                    <a href="?edit=<?php echo $row->mu_id; ?>" 
                                                       class="btn btn-xs btn-info"
                                                       title="تعديل">
                                                        <i class="icon-edit bigger-120"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $row->mu_id; ?>" 
                                                       class="btn btn-xs btn-danger"
                                                       onclick="return confirm('هل أنت متأكد من حذف وحدة القياس هذه؟')"
                                                       title="حذف">
                                                        <i class="icon-trash bigger-120"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                            $j++;
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="4" class="center">
                                                <div class="alert alert-info">
                                                    <i class="icon-info-sign"></i> لا توجد وحدات قياس مضافة بعد
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript files -->
<script src="assets/js/jquery-2.0.3.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    // تفعيل DataTable
    $('#sample-table-2').dataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
        },
        "order": [[0, "asc"]],
        "pageLength": 25
    });
    
    // تفعيل tooltips
    $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
    
    function tooltip_placement(context, source) {
        var $source = $(source);
        var $parent = $source.closest('table');
        var off1 = $parent.offset();
        var w1 = $parent.width();
        var off2 = $source.offset();
        var w2 = $source.width();
        
        if(parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) 
            return 'right';
        return 'left';
    }
});
</script>

<!-- نهاية ملف measurements.php - الإصدار 2.0.0 -->
</body>
</html>