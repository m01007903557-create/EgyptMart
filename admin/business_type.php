<?php
/**
 * File: admin/business_types.php
 * Version: PHP 8.3
 * Description: إدارة أنواع الأعمال (Business Types) في لوحة التحكم
 * 
 * تسمح هذه الصفحة للمشرف بعرض وإضافة وتعديل وحذف أنواع الأعمال
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من تسجيل دخول المستخدم
check_admin_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * دالة للتحقق مما إذا كانت السلسلة فارغة بعد إزالة المسافات
 * @param string $str النص المراد فحصه
 * @return bool true إذا كان النص فارغاً
 */
function checkEmpty($str): bool {
    $check_empty = preg_replace('/\s+/', '', $str);
    return $check_empty == "";
}

// معالجة حذف نوع عمل
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $sql = "DELETE FROM business_type WHERE bsntyp_id = " . $id;
        mysqli_query($con, $sql);
    }
    header("location: business_types.php");
    exit();
}

// معالجة إضافة نوع عمل جديد
if (isset($_POST['save_mes'])) {
    $name = trim($_POST['business_type'] ?? '');
    if (!empty($name)) {
        $name_escaped = mysqli_real_escape_string($con, $name);
        $sql = "INSERT INTO business_type (bsntyp_title, bsntyp_status) VALUES('{$name_escaped}', '1')";
        mysqli_query($con, $sql);
    }
    header("location: business_types.php");
    exit();
}

// معالجة تحديث نوع عمل
if (isset($_POST['update_mes'])) {
    $name = trim($_POST['business_type'] ?? '');
    $id = (int)($_POST['business_type_id'] ?? 0);
    
    if (!empty($name) && $id > 0) {
        $name_escaped = mysqli_real_escape_string($con, $name);
        $sql = "UPDATE business_type SET bsntyp_title = '{$name_escaped}' WHERE bsntyp_id = " . $id;
        mysqli_query($con, $sql);
    }
    header("location: business_types.php");
    exit();
}

// جلب قائمة أنواع الأعمال
$recObj = mysqli_query($con, "SELECT * FROM business_type ORDER BY bsntyp_id ASC");
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
                        <a href="welcome.php">Home</a>
                    </li>
                    <li class="active">Manage Business Types</li>
                </ul><!-- .breadcrumb -->
            </div>
            
            <div class="page-content">
                <div class="row">
                    
                    <!-- نموذج الإضافة/التعديل -->
                    <div class="col-xs-12">
                        <div class="page-header">
                            <h1>Manage Business Types</h1>
                        </div>
                        
                        <form method="post">
                            <div class="input-group" style="width: 100%; margin-bottom: 20px;">
                                <?php if (isset($_GET['edit'])): ?>
                                    <?php 
                                    $id = (int)$_GET['edit'];
                                    $query = mysqli_query($con, "SELECT * FROM business_type WHERE bsntyp_id = " . $id);
                                    $fetch = $query ? mysqli_fetch_array($query) : null;
                                    
                                    if ($fetch):
                                    ?>
                                        <input type="hidden" name="business_type_id" value="<?php echo (int)$fetch['bsntyp_id']; ?>">
                                        <input type="text" class="form-control" style="width: 300px; display: inline-block;" 
                                               placeholder="Input business type Name" 
                                               name="business_type" 
                                               value="<?php echo htmlspecialchars($fetch['bsntyp_title'] ?? ''); ?>" 
                                               required>
                                        <input type="submit" class="btn btn-primary" value="Update" name="update_mes">
                                        <a href="business_types.php" class="btn btn-default">Cancel</a>
                                    <?php else: ?>
                                        <p class="alert alert-danger">Business type not found.</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <input type="text" class="form-control" style="width: 300px; display: inline-block;" 
                                           placeholder="Input business type Name" 
                                           name="business_type" 
                                           required>
                                    <input type="submit" class="btn btn-primary" value="Add New" name="save_mes">
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <br>
                    
                    <!-- جدول عرض أنواع الأعمال -->
                    <div class="col-xs-12">
                        <div class="table-responsive">
                            <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><strong>ID</strong></th>
                                        <th><strong>Name</strong></th>
                                        <th style="text-align:center"><strong>Actions</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $j = 0;
                                    $count = $recObj ? mysqli_num_rows($recObj) : 0;
                                    
                                    if ($count > 0):
                                        while ($row = mysqli_fetch_object($recObj)):
                                    ?>
                                            <tr>
                                                <td><?php echo (int)$row->bsntyp_id; ?></td>
                                                <td><?php echo htmlspecialchars($row->bsntyp_title ?? ''); ?></td>
                                                <td align="center">
                                                    <a href="?edit=<?php echo (int)$row->bsntyp_id; ?>" class="btn btn-success btn-sm">
                                                        <i class="icon-edit"></i> Edit
                                                    </a>
                                                    <a href="?delete=<?php echo (int)$row->bsntyp_id; ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       onclick="return confirm('Are you sure you want to delete this business type?')">
                                                        <i class="icon-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php 
                                            $j++;
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="3" align="center" style="padding: 20px; color: #F00;">
                                                No business types found.
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

<!-- JavaScript Libraries -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>

<!-- DataTables -->
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

<!-- ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    // تهيئة DataTable
    $('#sample-table-2').dataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Arabic.json"
        }
    });
    
    $('table th input:checkbox').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox').each(function() {
            this.checked = that.checked;
            $(this).closest('tr').toggleClass('selected');
        });
    });
    
    $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
    
    function tooltip_placement(context, source) {
        var $source = $(source);
        var $parent = $source.closest('table');
        var off1 = $parent.offset();
        var w1 = $parent.width();
        var off2 = $source.offset();
        var w2 = $source.width();
        
        if (parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) return 'right';
        return 'left';
    }
});
</script>

</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>