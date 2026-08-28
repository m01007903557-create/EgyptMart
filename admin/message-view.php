<?php
/**
 * File: message-view.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: عرض وإدارة الرسائل الداخلية في النظام
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * دالة الحصول على معلومات المستخدم
 */
function getUserInfoField($user_id, $field) {
    global $con;
    
    $user_id = (int)$user_id;
    if ($user_id <= 0) return '';
    
    $allowed_fields = ['name_prefix', 'fname', 'lname', 'email', 'phone'];
    if (!in_array($field, $allowed_fields)) return '';
    
    $sql = "SELECT $field FROM user WHERE usr_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $row[$field] ?? '';
}

/**
 * دالة الحصول على اسم المستخدم الكامل
 */
function getUserFullName($user_id) {
    $prefix = getUserInfoField($user_id, 'name_prefix');
    $fname = getUserInfoField($user_id, 'fname');
    $lname = getUserInfoField($user_id, 'lname');
    return trim($prefix . ' ' . $fname . ' ' . $lname);
}

/**
 * كلاس إدارة الرسائل
 */
class listMessage {
    public string $sqlList = "";
    public int $start = 0;
    public int $limit = 20;
    public array $errors = [];
    
    public function setsql(string $sql): void {
        $this->sqlList = $sql;
    }
    
    public function totalrecord(): int {
        global $con;
        $result = mysqli_query($con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    public function listview() {
        global $con;
        $sql = $this->sqlList . " LIMIT {$this->start}, {$this->limit}";
        return mysqli_query($con, $sql);
    }
    
    public function deleterecord(int $adid): bool {
        global $con;
        $sql = "DELETE FROM message WHERE msg_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) return false;
        
        mysqli_stmt_bind_param($stmt, "i", $adid);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    public function deletelink(int $id): string {
        $dellink = $_SERVER['QUERY_STRING'] == "" 
            ? "?action=del&fid=" . $id
            : "message-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&fid=" . $id;
        return $dellink;
    }
}

// تهيئة Pagination
$pagination = new Pagination();
$page = $pagination->getCurrentPage();

$al = new listMessage();

/******************** حذف سجل *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $al->deleterecord((int)$_GET['fid']);
    header("Location: message-view.php");
    exit;
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $pagination->getLimit(20);
$al->setsql("SELECT * FROM message ORDER BY msg_date DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($page, $limit, $totalitems);

$recObj = $al->listview();
$count = $recObj ? mysqli_num_rows($recObj) : 0;

$showitems = ($al->start + 1) . " - ";
if (($al->start + $limit) < $totalitems) {
    $showitems .= ($al->start + $limit);
} else {
    $showitems .= $totalitems;
}
$showitems .= " of " . $totalitems . " items";

// حذف متعدد
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $al->deleterecord((int)$id);
    }
    header("Location: message-view.php");
    exit;
}
?>

<?php include "includes/admin-top.php"; ?>

<style>
.table-header { margin-bottom: 10px; padding: 8px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; }
.dataTables_filter { float: right; margin-bottom: 10px; }
.dataTables_length { float: left; margin-bottom: 10px; }
.dataTables_info { float: left; margin-top: 10px; }
.dataTables_paginate { float: right; margin-top: 10px; }
.table td, .table th { vertical-align: middle; }
td a { color: #333; text-decoration: none; }
td a:hover { color: #4CAF50; text-decoration: underline; }
.pagination { margin: 0; }
</style>

<div class="main-container" id="main-container">
    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php"; ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li><i class="icon-home home-icon"></i><a href="welcome.php">Home</a></li>
                    <li class="active">Messages</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('Are you sure?')">
                                    <i class="icon-trash bigger-120"></i> Delete Selected
                                </button>
                                <span style="float: right;"><?php echo $showitems; ?></span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="message-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                         <th class="center"><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></th>
                                         <th>Date</th>
                                         <th>From</th>
                                         <th>To</th>
                                         <th>Subject</th>
                                         <th>Action</th>
                                    </thead>
                                    <tbody>
                                        <?php if ($count > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($recObj)): ?>
                                            <tr id="row_<?php echo $row->msg_id; ?>">
                                                <td class="center">
                                                    <input name="cb[]" class="ace" type="checkbox" value="<?php echo $row->msg_id; ?>">
                                                    <span class="lbl"></span>
                                                 </td>
                                                <td class="center"><?php echo date('d M Y H:i', strtotime($row->msg_date)); ?></td>
                                                <td class="center">
                                                    <strong><?php echo htmlspecialchars(getUserFullName((int)$row->msg_from)); ?></strong>
                                                </td>
                                                <td class="center">
                                                    <strong><?php echo htmlspecialchars(getUserFullName((int)$row->msg_to)); ?></strong>
                                                </td>
                                                <td class="center">
                                                    <a href="message-edit.php?fid=<?php echo $row->msg_id; ?>">
                                                        <?php echo htmlspecialchars($row->msg_subject); ?>
                                                    </a>
                                                </td>
                                                <td class="center">
                                                    <a href="message-edit.php?fid=<?php echo $row->msg_id; ?>" class="btn btn-xs btn-info">View</a>
                                                    <a href="<?php echo $al->deletelink((int)$row->msg_id); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this message?')">Delete</a>
                                                </td>
                                             </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                             <tr>
                                                <td colspan="6" class="text-center">No messages found</td>
                                             </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination - عرض محدود من الأرقام -->
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">Showing <?php echo $showitems; ?></div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    $totalPages = ceil($totalitems / $limit);
                                    if ($totalPages > 1):
                                        // عرض 5 أرقام فقط حول الصفحة الحالية
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $page + 2);
                                    ?>
                                    <div class="dataTables_paginate paging_bootstrap">
                                        <ul class="pagination">
                                            <!-- Previous button -->
                                            <li class="<?php echo ($page <= 1) ? 'disabled' : 'prev'; ?>">
                                                <a href="<?php echo ($page > 1) ? '?page=' . ($page - 1) : '#'; ?>">
                                                    <i class="icon-double-angle-left"></i>
                                                </a>
                                            </li>
                                            
                                            <!-- First page with dots -->
                                            <?php if ($startPage > 1): ?>
                                                <li><a href="?page=1">1</a></li>
                                                <?php if ($startPage > 2): ?>
                                                    <li class="disabled"><a href="#">...</a></li>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <!-- Page numbers -->
                                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <!-- Last page with dots -->
                                            <?php if ($endPage < $totalPages): ?>
                                                <?php if ($endPage < $totalPages - 1): ?>
                                                    <li class="disabled"><a href="#">...</a></li>
                                                <?php endif; ?>
                                                <li><a href="?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a></li>
                                            <?php endif; ?>
                                            
                                            <!-- Next button -->
                                            <li class="<?php echo ($page >= $totalPages) ? 'disabled' : 'next'; ?>">
                                                <a href="<?php echo ($page < $totalPages) ? '?page=' . ($page + 1) : '#'; ?>">
                                                    <i class="icon-double-angle-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

<script>
$(document).ready(function() {
    $('#message-table').DataTable({
        "pageLength": 20,
        "language": {
            "search": "Search:",
            "lengthMenu": "Display _MENU_ records",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries"
        }
    });
    
    $('#selectAll').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
    });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>