<?php
/**
 * File: ownership_type.php
 * Version: 2.0.0
 * Description: إدارة أنواع الملكية (إضافة - تعديل - حذف - عرض)
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

// جلب بيانات أنواع الملكية
$recObj = mysqli_query($con, "SELECT * FROM ownership_type ORDER BY owntyp_id ASC");

/**
 * دالة التحقق من النص الفارغ
 */
function checkEmpty(?string $str): bool {
    if ($str === null || $str === '') {
        return true;
    }
    $check_empty = preg_replace('/\s+/', '', $str);
    return $check_empty === "";
}

// معالجة الحذف
if (isset($_GET['delete'])) {
    $id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    if ($id && $id > 0) {
        $sql = "DELETE FROM ownership_type WHERE owntyp_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم الحذف بنجاح</div>';
        }
    }
    header("Location: ownership_type.php");
    exit();
}

// معالجة الإضافة
if (isset($_POST['save_mes'])) {
    $name = trim($_POST['business_type'] ?? '');
    if (!checkEmpty($name)) {
        $sql = "INSERT INTO ownership_type(owntyp_title, owntyp_statu) VALUES(?, '1')";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $name);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تمت الإضافة بنجاح</div>';
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $_SESSION['msg'] = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال اسم نوع الملكية</div>';
    }
    header("Location: ownership_type.php");
    exit();
}

// معالجة التحديث
if (isset($_POST['update_mes'])) {
    $name = trim($_POST['business_type'] ?? '');
    $id = filter_input(INPUT_POST, 'business_type_id', FILTER_VALIDATE_INT);
    
    if (!checkEmpty($name) && $id && $id > 0) {
        $sql = "UPDATE ownership_type SET owntyp_title = ? WHERE owntyp_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $name, $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم التحديث بنجاح</div>';
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: ownership_type.php");
    exit();
}

// جلب بيانات التعديل إذا وجدت
$editData = null;
if (isset($_GET['edit'])) {
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId && $editId > 0) {
        $sql = "SELECT * FROM ownership_type WHERE owntyp_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $editId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $editData = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
        }
    }
}

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
                    <li class="active">إدارة أنواع الملكية</li>
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
                    <div class="col-xs-12">
                        <form method="post"> 
                            <div class="input-group" style="width: 100%; margin-bottom: 20px;">
                                <?php if ($editData): ?>
                                    <input type="hidden" name="business_type_id" value="<?php echo (int)$editData['owntyp_id']; ?>">
                                    <input type="text" 
                                           class="form-control" 
                                           placeholder="أدخل نوع الملكية" 
                                           name="business_type" 
                                           value="<?php echo htmlspecialchars($editData['owntyp_title']); ?>"
                                           required>
                                    <input type="submit" class="form-control btn btn-primary" value="تحديث" name="update_mes">
                                <?php else: ?>
                                    <input type="text" 
                                           class="form-control" 
                                           placeholder="أدخل نوع الملكية" 
                                           name="business_type"
                                           required>
                                    <input type="submit" class="form-control btn btn-primary" value="إضافة جديد" name="save_mes">
                                <?php endif ?>
                            </div>
                        </form>
                    </div>

                    <br>

                    <div class="col-xs-12">
                        <div class="table-responsive">
                            <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><strong>الرقم</strong></th>
                                        <th><strong>الاسم</strong></th>
                                        <th style="text-align:center"><strong>الحالة</strong></th>
                                        <th style="text-align:center"><strong>الإجراءات</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $j = 0;
                                    $count = mysqli_num_rows($recObj);
                                    if ($count > 0):
                                        while($row = mysqli_fetch_assoc($recObj)): 
                                    ?>
                                            <tr>
                                                <td><?php echo (int)$row['owntyp_id']; ?></td>
                                                <td><?php echo htmlspecialchars($row['owntyp_title']); ?></td>
                                                <td align="center">
                                                    <?php if (isset($row['owntyp_statu']) && $row['owntyp_statu'] == '1'): ?>
                                                        <span class="label label-success">فعال</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">غير فعال</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td align="center">
                                                    <a href="?edit=<?php echo (int)$row['owntyp_id']; ?>" 
                                                       class="btn btn-xs btn-success"
                                                       title="تعديل">
                                                        <i class="icon-pencil"></i> تعديل
                                                    </a>
                                                    <a href="?delete=<?php echo (int)$row['owntyp_id']; ?>" 
                                                       class="btn btn-xs btn-danger"
                                                       onclick="return confirm('هل أنت متأكد من الحذف؟')"
                                                       title="حذف">
                                                        <i class="icon-trash"></i> حذف
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                            $j++;
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="4" align="center">
                                                <div class="alert alert-info">
                                                    <i class="icon-info-sign"></i>
                                                    لا توجد أنواع ملكية لعرضها
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br clear="all" />
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<script type="text/javascript">
window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

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
    // تفعيل DataTable
    $('#sample-table-2').dataTable({
        language: {
            "emptyTable": "لا توجد نتائج",
            "search": "بحث:",
            "lengthMenu": "عرض _MENU_ عنصر في الصفحة",
            "info": "عرض _START_ إلى _END_ من _TOTAL_ عنصر",
            "infoEmpty": "عرض 0 إلى 0 من 0 عنصر",
            "infoFiltered": "(مرشح من _MAX_ عنصر)",
            "zeroRecords": "لا توجد نتائج",
            "oPaginate": {
                "sFirst": "الأول",
                "sPrevious": "السابق",
                "sNext": "التالي",
                "sLast": "الأخير"
            }
        }
    });
    
    // تفعيل خاصية تحديد الكل
    $('table th input:checkbox').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
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
        
        if(parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) return 'right';
        return 'left';
    }
    
    // إخفاء رسالة النجاح بعد 5 ثوان
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

</body>
</html>