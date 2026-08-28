<?php
/**
 * File: maincat-view.php
 * Version: 2.0.0
 * Description: عرض وإدارة التصنيفات الرئيسية مع إمكانية الترتيب والحذف (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات المطلوبة
require_once "../common.php";
require_once "../lib/pagination.php";

// التحقق من تسجيل الدخول
check_admin_login();

/**
 * Class ProductList - إدارة قائمة التصنيفات الرئيسية
 * متوافق مع PHP 8.3
 */
class ProductList {
    private string $sqlList = '';
    private int $start = 0;
    private int $limit = 0;
    private ?mysqli $dbConnection = null;
    
    /**
     * المُنشئ
     */
    public function __construct(?mysqli $databaseConnection = null) {
        global $con;
        $this->dbConnection = $databaseConnection ?? $con;
    }
    
    /**
     * تعيين استعلام SQL
     */
    public function setsql(string $sql): self {
        $this->sqlList = $sql;
        return $this;
    }
    
    /**
     * تعيين نقطة البداية
     */
    public function setStart(int $start): self {
        $this->start = max(0, $start);
        return $this;
    }
    
    /**
     * تعيين عدد العناصر في الصفحة
     */
    public function setLimit(int $limit): self {
        $this->limit = max(1, min(100, $limit));
        return $this;
    }
    
    /**
     * الحصول على إجمالي عدد السجلات
     */
    public function totalrecord(): int {
        if (empty($this->sqlList)) {
            return 0;
        }
        
        $countSql = preg_replace('/ORDER\s+BY\s+.*?(?=\s+LIMIT|\s*$)/i', '', $this->sqlList);
        $result = mysqli_query($this->dbConnection, $countSql);
        
        if (!$result) {
            error_log('خطأ في استعلام العدد: ' . mysqli_error($this->dbConnection));
            return 0;
        }
        
        return mysqli_num_rows($result);
    }
    
    /**
     * الحصول على عرض القائمة
     */
    public function listview() {
        if (empty($this->sqlList)) {
            return false;
        }
        
        $sql = $this->sqlList . " LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($this->dbConnection));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $this->limit, $this->start);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * حساب عدد الصفحات
     */
    public function numpage(int $rowPage): int {
        $total = $this->totalrecord();
        return $rowPage > 0 ? (int)floor($total / $rowPage) : 0;
    }
    
    /**
     * حذف التصنيف وجميع التصنيفات الفرعية
     */
    public function deleterecord($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        // حذف الصور قبل حذف السجلات
        $this->deleteCategoryImages($cleanId);
        
        // حذف التصنيف والتصنيفات الفرعية
        $sql = "DELETE FROM product_category WHERE pc_id = ? OR pc_parent_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $cleanId, $cleanId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $success;
    }
    
    /**
     * حذف صور التصنيف
     */
    private function deleteCategoryImages(int $categoryId): void {
        $sql = "SELECT pc_image, pc_banner FROM product_category WHERE pc_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($row) {
            // حذف صورة التصنيف
            if (!empty($row['pc_image'])) {
                $imagePath = __DIR__ . '/../upload/category/' . $row['pc_image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // حذف صورة البانر
            if (!empty($row['pc_banner'])) {
                $bannerPath = __DIR__ . '/../upload/bannerimage/' . $row['pc_banner'];
                if (file_exists($bannerPath)) {
                    unlink($bannerPath);
                }
            }
        }
    }
    
    /**
     * إنشاء رابط الحذف
     */
    public function deletelink($id): string {
        $cleanId = (int)$id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id=" . $cleanId;
        }
        
        return "maincat-view.php?" . htmlspecialchars($queryString) . "&action=del&ad-id=" . $cleanId;
    }
    
    /**
     * تحديث ترتيب التصنيف
     */
    public function updateOrder(int $id, int $order): bool {
        $sql = "UPDATE product_category SET pc_order = ? WHERE pc_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $order, $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $success;
    }
}

/**
 * Class Pagination - إدارة الترقيم
 */
class Pagination {
    private int $defaultLimit = 10;
    private int $maxLimit = 100;
    
    public function setpage(): int {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        return max(1, $page);
    }
    
    public function setlimit(int $default = 10): int {
        $this->defaultLimit = $default;
        
        if (!isset($_GET['limit'])) {
            return $this->defaultLimit;
        }
        
        $limit = (int)$_GET['limit'];
        
        if ($limit < 1) return 1;
        if ($limit > $this->maxLimit) return $this->maxLimit;
        
        return $limit;
    }
    
    public function setstart(int $page, int $limit, int $total): int {
        $start = ($page - 1) * $limit;
        
        if ($start >= $total && $total > 0) {
            $start = max(0, $total - $limit);
        }
        
        return max(0, $start);
    }
}

// تهيئة الكلاسات
$p = new Pagination();
$page = $p->setpage();
$al = new ProductList();

// معالجة حذف سجل
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $adId = filter_input(INPUT_GET, 'ad-id', FILTER_VALIDATE_INT);
    if ($adId !== false && $adId > 0) {
        $al->deleterecord($adId);
        $_SESSION['success_msg'] = 'تم حذف التصنيف بنجاح';
    }
    header("Location: maincat-view.php");
    exit();
}

// معالجة تحديث الترتيب عبر AJAX
if (isset($_POST['action']) && $_POST['action'] === 'update_order') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $order = filter_input(INPUT_POST, 'order', FILTER_VALIDATE_INT);
    
    if ($id && $order !== false) {
        $success = $al->updateOrder($id, $order);
        echo $success ? 'success' : 'error';
    } else {
        echo 'invalid';
    }
    exit();
}

// معالجة الحذف المتعدد
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    $deleted = 0;
    foreach ($_POST['cb'] as $id) {
        $cleanId = filter_var($id, FILTER_VALIDATE_INT);
        if ($cleanId !== false && $cleanId > 0) {
            if ($al->deleterecord($cleanId)) {
                $deleted++;
            }
        }
    }
    
    if ($deleted > 0) {
        $_SESSION['success_msg'] = "تم حذف $deleted تصنيف/تصنيفات بنجاح";
    }
    
    header("Location: maincat-view.php");
    exit();
}

// إعدادات الترقيم
$limit = $p->setlimit(10);
$al->setLimit($limit);
$al->setsql("SELECT * FROM product_category WHERE pc_parent_id = '0' ORDER BY pc_order ASC, pc_id ASC");
$totalitems = $al->totalrecord();
$al->setStart($p->setstart($page, $limit, $totalitems));
$recObj = $al->listview();

// حساب نص عرض العناصر
$startItem = $totalitems > 0 ? $al->start + 1 : 0;
$endItem = min($al->start + $limit, $totalitems);
$showitems = $totalitems > 0 ? "$startItem - $endItem من $totalitems عنصر" : "0 عناصر";

// رسائل النجاح
$successMsg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);
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

                <script type="text/javascript">
                // دالة تحديث الترتيب
                function updOrder(id) {
                    var pc_order = document.getElementById('inp_order_' + id);
                    
                    if (!pc_order.value || isNaN(pc_order.value) || parseInt(pc_order.value) < 0) {
                        alert('الرجاء إدخال رقم صحيح موجب');
                        return;
                    }
                    
                    // استخدام fetch بدلاً من jQuery
                    fetch('maincat-view.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=update_order&id=' + id + '&order=' + pc_order.value
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'success') {
                            alert('تم تحديث الترتيب بنجاح');
                        } else {
                            alert('فشل تحديث الترتيب');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('حدث خطأ في الاتصال');
                    });
                }

                // تحديد الكل / إلغاء التحديد
                function toggleCheckAll(source) {
                    var checkboxes = document.querySelectorAll('input[name="cb[]"]');
                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = source.checked;
                        checkbox.closest('tr')?.classList.toggle('selected', source.checked);
                    });
                }

                // تأكيد الحذف المتعدد
                function confirmBulkDelete() {
                    return confirm('هل أنت متأكد من حذف التصنيفات المحددة؟');
                }
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">الرئيسية</a>
                    </li>
                    <li>
                        <a href="maincat-view.php">إدارة التصنيفات</a>
                    </li>
                    <li class="active">عرض التصنيفات</li>
                </ul>
            </div>

            <div class="page-content">
                <?php if ($successMsg): ?>
                    <div class="alert alert-success">
                        <i class="icon-ok"></i>
                        <?php echo htmlspecialchars($successMsg); ?>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                <?php endif; ?>

                <form name="myform" id="myform" method="post" onsubmit="return confirmBulkDelete();">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button type="button" class="btn btn-xs btn-success" onClick="window.location='maincat-add.php'">
                                    <i class="icon-pencil align-top bigger-120"></i>
                                    إضافة تصنيف جديد
                                </button>
                                
                                <button type="submit" name="btnDelete" class="btn btn-xs btn-danger" style="margin-right:10px;">
                                    <i class="icon-trash align-top bigger-120"></i>
                                    حذف المحدد
                                </button>
                                
                                <span style="float:left; margin-top:5px;">
                                    <?php echo htmlspecialchars($showitems); ?>
                                </span>
                            </div>

                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center">
                                                <label>
                                                    <input type="checkbox" class="ace" onclick="toggleCheckAll(this)">
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>
                                            <th><strong>الصورة</strong></th>
                                            <th><strong>اسم التصنيف</strong></th>
                                            <th style="text-align:center"><strong>الترتيب</strong></th>
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
                                                <td class="center">
                                                    <label>
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row['pc_id']; ?>">
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['pc_image'])): ?>
                                                        <img src="../upload/category/<?php echo htmlspecialchars($row['pc_image']); ?>" 
                                                             width="70" height="70" 
                                                             onerror="this.src='images/no-image.jpg'"
                                                             alt="<?php echo htmlspecialchars($row['pc_name']); ?>">
                                                    <?php else: ?>
                                                        <img src="images/no-image.jpg" width="70" height="70" alt="لا توجد صورة">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['pc_name']); ?></td>
                                                <td style="text-align:center">
                                                    <input type="number" 
                                                           id="inp_order_<?php echo (int)$row['pc_id']; ?>" 
                                                           style="width:60px; text-align:center;" 
                                                           value="<?php echo (int)($row['pc_order'] ?? 0); ?>"
                                                           min="0">
                                                    <button type="button" 
                                                            onclick="updOrder('<?php echo (int)$row['pc_id']; ?>');" 
                                                            class="btn btn-xs btn-info">
                                                        <i class="icon-ok"></i>
                                                    </button>
                                                </td>
                                                <td align="center">
                                                    <?php 
                                                    // إنشاء رابط آمن للتعديل
                                                    $token = bin2hex(random_bytes(8));
                                                    $editHash = md5($row['pc_id'] . $token);
                                                    ?>
                                                    <a href="maincat-edit.php?token=<?php echo $editHash; ?>&id=<?php echo (int)$row['pc_id']; ?>" 
                                                       title="تعديل">
                                                        <img alt="تعديل" src="images/edit.jpg">
                                                    </a>
                                                    <a href="<?php echo htmlspecialchars($al->deletelink($row['pc_id'])); ?>" 
                                                       title="حذف" 
                                                       onclick="return confirm('هل أنت متأكد من حذف هذا التصنيف؟');">
                                                        <img alt="حذف" src="images/delete.jpg" border="0">
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="5" align="center">
                                                    <div class="alert alert-info">
                                                        <i class="icon-info-sign"></i>
                                                        لا توجد تصنيفات مضافة حالياً
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
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
    var oTable1 = $('#sample-table-2').dataTable({
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": false },
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
    
    // تفعيل خاصية تحديد الكل
    $('table th input:checkbox').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
    });
    
    // إخفاء رسائل النجاح بعد 5 ثوان
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
});
</script>

</body>
</html>