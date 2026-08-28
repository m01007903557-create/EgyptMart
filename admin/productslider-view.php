<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";  // أضف هذا السطر

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// ===== كود تغيير الحالة مع تشخيص =====
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'change_status') {
    $stat = isset($_POST['stat']) ? (int)$_POST['stat'] : -1;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // كتابة السجل
    $log_file = '/home/u397968200/domains/egyptmart.shop/public_html/logs/status_debug.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Called with stat=$stat, id=$id\n", FILE_APPEND);
    
    if ($id > 0 && ($stat == 0 || $stat == 1)) {
        global $con;
        $sql = "UPDATE prodservice_slider SET adv_status = $stat WHERE adv_id = $id";
        file_put_contents($log_file, "SQL: $sql\n", FILE_APPEND);
        
        if (mysqli_query($con, $sql)) {
            file_put_contents($log_file, "Success\n", FILE_APPEND);
            echo '1';
        } else {
            file_put_contents($log_file, "Error: " . mysqli_error($con) . "\n", FILE_APPEND);
            echo '0';
        }
    } else {
        file_put_contents($log_file, "Invalid parameters\n", FILE_APPEND);
        echo '0';
    }
    exit;
}

/**
 * Class sliderviewlist - إدارة سلايدر المنتجات
 */
class sliderviewlist {
    public string $sqlList = "";
    public int $start = 0;
    public int $limit = 20;
    
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
        return mysqli_query($con, $this->sqlList);
    }
    
    public function numpage(int $rowPage): int {
        return (int)floor($this->totalrecord() / $rowPage);
    }
    
    public function deleterecord(int $adid): void {
        global $con;
        
        // جلب اسم الصورة لحذفها
        $chquesql = "SELECT adv_img FROM prodservice_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $chquesql);
        mysqli_stmt_bind_param($stmt, "i", $adid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($row && !empty($row['adv_img'])) {
            $path = "../upload/product_slider/" . $row['adv_img'];
            if (is_file($path)) {
                unlink($path);
            }
        }
        
        // حذف من قاعدة البيانات
        $sql_del = "DELETE FROM prodservice_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql_del);
        mysqli_stmt_bind_param($stmt, "i", $adid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    public function deletelink(int $id): string {
        $dellink = $_SERVER['QUERY_STRING'] == "" 
            ? "?action=del&fid=" . $id
            : "productslider-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&fid=" . $id;
        return $dellink;
    }
}

// تهيئة Pagination
$pagination = new Pagination();
$page = $pagination->getCurrentPage();

$al = new sliderviewlist();

/******************** حذف سجل *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $al->deleterecord((int)$_GET['fid']);
    header("Location: productslider-view.php");
    exit;
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $pagination->getLimit(20);
$al->setsql("SELECT * FROM prodservice_slider WHERE adv_type = '1' ORDER BY adv_updated_date DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($page, $limit, $totalitems);

// إضافة LIMIT إلى الاستعلام
$query = "SELECT * FROM prodservice_slider WHERE adv_type = '1' ORDER BY adv_updated_date DESC LIMIT {$al->start}, {$limit}";
$al->setsql($query);
$recObj = $al->listview();

// حساب نطاق العرض
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
    header("Location: productslider-view.php");
    exit;
}

// مصفوفة رموز العملات
$currency_symbols = [
    'USD' => '$',
    'EUR' => '€',
    'GBP' => '£',
    'EGP' => 'E£',
    'SAR' => 'SR',
    'AED' => 'د.إ'
];
?>

<?php include "includes/admin-top.php"; ?>

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
                    <li><a href="productslider-view.php">Manage Product Slider</a></li>
                    <li class="active">View Slider</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('Are you sure to delete the record?')">
                                    <i class="icon-trash bigger-120"></i> Delete
                                </button>
                                <button type="button" class="btn btn-xs btn-success" onclick="window.location='productslider-add.php'">
                                    <i class="icon-pencil align-top bigger-120"></i> Add Slider
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                         <th class="center"><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></th>
                                         <th><strong>Image</strong></th>
                                         <th><strong>Info</strong></th>
                                         <th><strong>Width & Height</strong></th>
                                         <th><strong>Status</strong></th>
                                         <th><strong>Change Status</strong></th>
                                         <th><strong>Action</strong></th>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $count = $recObj ? mysqli_num_rows($recObj) : 0;
                                        if ($count > 0):
                                            while ($row = mysqli_fetch_object($recObj)):
                                                // معالجة الدول
                                                $country2show = '';
                                                if (!empty($row->adv_country)) {
                                                    $countrylist = explode(",", $row->adv_country);
                                                    $countryNames = [];
                                                    foreach ($countrylist as $snglecntry) {
                                                        $cid = (int)trim($snglecntry);
                                                        if ($cid > 0) {
                                                            $countryNames[] = get_country_name($cid);
                                                        }
                                                    }
                                                    $country2show = implode(", ", $countryNames);
                                                }
                                        ?>
                                        <tr id="row_<?php echo $row->adv_id; ?>">
                                            <td class="center">
                                                <input name="cb[]" class="ace" type="checkbox" value="<?php echo $row->adv_id; ?>">
                                                <span class="lbl"></span>
                                             </td>
                                            <td style="text-align:center;">
                                                <img src="https://egyptmart.shop/upload/product_slider/<?php echo htmlspecialchars($row->adv_img); ?>" width="200px;" height="150px;">
                                            </td>
                                            <td style="text-align:center">
                                                <strong>Link:</strong> <?php echo htmlspecialchars($row->adv_link); ?><br><br>
                                                <strong>Heading:</strong> <?php echo htmlspecialchars($row->adv_title); ?><br>
                                                <strong>MOQ data:</strong> <?php echo htmlspecialchars($row->adv_description); ?><br>
                                                <?php if (!empty($country2show)): ?>
                                                    <strong>Country:</strong> <?php echo htmlspecialchars($country2show); ?><br>
                                                <?php endif; ?>
                                                <strong>Fob price:</strong> <?php echo ($currency_symbols[$row->adv_currency] ?? '$') . htmlspecialchars($row->adv_price); ?><br>
                                                <strong>MOQ unit:</strong> <?php echo htmlspecialchars($row->adv_piece); ?><br>
                                                <strong>Unit type:</strong> <?php echo htmlspecialchars($row->unit_type); ?><br>
                                                <strong>Title:</strong> <?php echo htmlspecialchars($row->adv_title); ?><br>
                                                <strong>Membership Icon:</strong> <img src="../images/<?php echo htmlspecialchars($row->adv_icon); ?>" alt="">
                                            </td>
                                            <td style="text-align:center"><?php echo $row->adv_imagewidth . " x " . $row->adv_imageheight; ?></td>
                                            <td style="width:90px; text-align:center">
                                                <?php if ($row->adv_status == '1'): ?>
                                                    <font color="green">Active</font>
                                                <?php else: ?>
                                                    <font color="red">Inactive</font>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align:center;">
                                                <select id="status_<?php echo $row->adv_id; ?>" onchange="changeStatus(this.value, '<?php echo $row->adv_id; ?>')">
                                                    <option value="">Select</option>
                                                    <?php if ($row->adv_status == '1'): ?>
                                                        <option value="0">Deactivate</option>
                                                    <?php else: ?>
                                                        <option value="1">Activate</option>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                            <td align="center">
                                                <a href="productslider-edit.php?aid=<?php echo $row->adv_id; ?>" title="edit">
                                                    <img alt="edit" src="images/edit.jpg" border="0">
                                                </a>
                                                <a href="<?php echo $al->deletelink((int)$row->adv_id); ?>" title="delete" onclick="return confirm('Are you sure to delete the record?')">
                                                    <img alt="delete" src="images/delete.jpg" border="0">
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            endwhile;
                                        endif;
                                        ?>
                                    </tbody>
                                 </table>
                            </div>
                            
                            <!-- Pagination Info -->
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">
                                        Showing <?php echo $showitems; ?>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    $totalPages = ceil($totalitems / $limit);
                                    if ($totalPages > 1):
                                    ?>
                                    <div class="dataTables_paginate paging_bootstrap">
                                        <ul class="pagination">
                                            <?php if ($page > 1): ?>
                                                <li class="prev"><a href="?page=<?php echo ($page - 1); ?>"><i class="icon-double-angle-left"></i></a></li>
                                            <?php else: ?>
                                                <li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $totalPages): ?>
                                                <li class="next"><a href="?page=<?php echo ($page + 1); ?>"><i class="icon-double-angle-right"></i></a></li>
                                            <?php else: ?>
                                                <li class="next disabled"><a href="#"><i class="icon-double-angle-right"></i></a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
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

<?php include "includes/footer.php"; ?>

<script src="assets/js/jquery-2.0.3.min.js"></script>
<script src="assets/js/jquery.mobile.custom.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script type="text/javascript">
function changeStatus(stat, id) {
    if (stat != '') {
        $.post(window.location.href, {ajax_action: 'change_status', stat: stat, id: id}, function(data) {
            // إزالة أي مسافات أو أحرف إضافية
            var response = data.trim();
            console.log("Response (trimmed):", response);
            
            if (response === '1') {
                window.location.reload();
            } else {
                alert('Error: ' + response);
            }
        });
    }
}
</script>

</body>
</html>
<?php ob_end_flush(); ?>
