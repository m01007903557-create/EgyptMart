<?php
/**
 * File: product-view.php
 * Version: 3.0.0 (PHP 8.3)
 * Description: عرض المنتجات في لوحة التحكم
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// حساب إجمالي عدد المنتجات
$totalProducts = 0;
$countResult = mysqli_query($con, "SELECT COUNT(*) as total FROM products");
if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalProducts = (int)$countRow['total'];
}
?>

<?php include "includes/admin-top.php"; ?>

<style>
/* إزالة المسافات الفارغة */
.main-container {
    padding-top: 0 !important;
    margin-top: 0 !important;
}
.main-content {
    padding-top: 0 !important;
}
.breadcrumbs {
    margin-bottom: 0 !important;
    padding: 5px 10px !important;
}
.page-content {
    padding-top: 0 !important;
}
.page-header {
    margin-top: 0 !important;
    padding-top: 0 !important;
    margin-bottom: 5px !important;
}
.dataTables_length, .dataTables_filter {
    margin-top: 0 !important;
    margin-bottom: 5px !important;
}
.dataTables_wrapper {
    padding-top: 0 !important;
}

<style>
/* إزالة المسافات الفارغة */
.main-container {
    padding-top: 0 !important;
    margin-top: 0 !important;
}
.main-content {
    padding-top: 0 !important;
}
.breadcrumbs {
    margin-bottom: 0 !important;
    padding: 5px 10px !important;
}
.page-content {
    padding-top: 0 !important;
}
.page-header {
    margin-top: 0 !important;
    padding-top: 0 !important;
    margin-bottom: 5px !important;
}
.dataTables_length, .dataTables_filter {
    margin-top: 0 !important;
    margin-bottom: 5px !important;
}
.dataTables_wrapper {
    padding-top: 0 !important;
}

/* إصلاح عرض الجدول */
.table-responsive {
    overflow-x: auto !important;
    width: 100% !important;
    -webkit-overflow-scrolling: touch;
}
.table {
    min-width: 1000px;
    width: 100%;
    margin-bottom: 10px;
}
.table th, .table td {
    white-space: nowrap;
    padding: 8px 6px;
    vertical-align: middle;
}
.table th:first-child, .table td:first-child {
    width: 40px;
    text-align: center;
}
.table th:last-child, .table td:last-child {
    width: 120px;
}
.table img {
    max-width: 60px;
    max-height: 60px;
    object-fit: cover;
}

/* تنسيق DataTable */
.dataTables_filter {
    float: right;
    margin-bottom: 10px;
}
.dataTables_length {
    float: left;
    margin-bottom: 10px;
}
.dataTables_info {
    float: left;
    margin-top: 10px;
}
.dataTables_paginate {
    float: right;
    margin-top: 10px;
}

/* ====== الكود الجديد: جعل النص ينكسر داخل الخلايا ====== */
.table td {
    white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
    max-width: 180px;
}

/* تحديد عرض أضيق للصور والأزرار */
<style>
/* تحديد عرض الأعمدة */
.table td:first-child, .table th:first-child {
    width: 10px;
    text-align: center;
}
.table td:nth-child(2), .table th:nth-child(2) { max-width: 50PX; }   /* Date */
.table td:nth-child(3), .table th:nth-child(3) { max-width: 120px; }   /* Image */
.table td:nth-child(4), .table th:nth-child(4) { max-width: 130px; }  /* Title */
.table td:nth-child(5), .table th:nth-child(5) { max-width: 120px; }  /* Category */
.table td:nth-child(6), .table th:nth-child(6) { max-width: 70px; }  /* Price */
.table td:nth-child(7), .table th:nth-child(7) { max-width: 130px; }  /* Posted by */
.table td:nth-child(8), .table th:nth-child(8) { max-width: 100px; }  /* Country */
.table td:nth-child(9), .table th:nth-child(9) { max-width: 80PX; }  /* Membership type */
.table td:nth-child(10), .table th:nth-child(10) { max-width: 80PX; } /* Membership Expired On */
.table td:nth-child(11), .table th:nth-child(11) { max-width: 30px; }  /* Details */
.table td:nth-child(12), .table th:nth-child(12) { max-width: 80px; }  /* Status */
.table td:nth-child(13), .table th:nth-child(13) { max-width: 80px; }  /* Action */
.table td:nth-child(14), .table th:nth-child(14) { max-width: 80px; } /* Add to Slider */

/* جعل النص ينكسر داخل الخلايا */
.table td {
    white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
}

/* جعل النص ينكسر في رأس الجدول */
.table th {
    white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-word !important;
    line-height: 1.3;
    padding: 8px 4px;
}
</style>
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
                    <li><a href="product-view.php">Manage Products</a></li>
                    <li class="active">Product View</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="table-header" style="margin-bottom: 15px;">
                    <button type="submit" name="btnDelete" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?')">
                        <i class="icon-trash bigger-120"></i>
                    </button>
                    <p style="display: inline-block;float: right; margin: 0;">
                        Go to Page No : <input type="number" name="page_no" id="page_no" class="page_no" min="1" style="width: 60px;" />
                    </p>
                </div>
                
                <div class="table-responsive">
                    <table id="product-table" class="table table-striped table-bordered table-hover">
                        <thead>
                             <th class="center"><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></th>
                             <th><strong>Date</strong></th>
                             <th><strong>Image</strong></th>
                             <th><strong>Title</strong></th>
                             <th><strong>Category</strong></th>
                             <th><strong>Price</strong></th>
                             <th><strong>Posted by</strong></th>
                             <th><strong>Country</strong></th>
                             <th><strong>Membership type</strong></th>
                             <th><strong>Membership Expired On</strong></th>
                             <th><strong>Details</strong></th>
                             <th><strong>Status</strong></th>
                             <th><strong>Action</strong></th>
                             <th><strong>Add to Slider</strong></th>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.pd_id, p.pd_date, p.pd_image, p.pd_imagelogo, p.pd_title, p.pd_fob_price, p.pd_fob_price2, p.pd_status,
                                           pc.pc_name as category_name, pc1.pc_name as pc1_name, pc2.pc_name as pc2_name,
                                           bf.bnsprof_compname as company_name,
                                           c.cn_name as country_name,
                                           sp.mst_name as membership_type,
                                           pm.expiry_date
                                    FROM products p
                                    LEFT JOIN product_category_arabyos pc ON p.pd_subcat_id = pc.pc_id
                                    LEFT JOIN product_category_arabyos pc1 ON pc1.pc_id = pc.pc_parent_id
                                    LEFT JOIN product_category_arabyos pc2 ON pc2.pc_id = pc1.pc_parent_id
                                    LEFT JOIN user u ON p.pd_uid = u.usr_id
                                    LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
                                    LEFT JOIN country c ON u.country = c.cn_id
                                    LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id
                                    LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id
                                    ORDER BY p.pd_id DESC
                                    LIMIT 500";
                            $result = mysqli_query($con, $sql);
                            
                            while ($row = mysqli_fetch_assoc($result)):
                                // معالجة الصورة مع الشعار
                                $imageHtml = '<div style="position: relative;">';
                                $img = $row['pd_image'] ?? '';
                                $imgArr = explode(',', $img);
                                $mainImg = !empty($imgArr[0]) ? $imgArr[0] : 'noimage.jpg';
                                
                                if (!empty($row['pd_imagelogo'])) {
                                    $logoArr = explode(',', $row['pd_imagelogo']);
                                    $imageHtml .= '<a href="#"><img src="../upload/myproduct/' . htmlspecialchars($logoArr[0]) . '" style="position: absolute; top: 48px; width: 30px; height: 29px;" /></a>';
                                }
                                $imageHtml .= '<a href="#"><img src="../upload/myproduct/' . htmlspecialchars($mainImg) . '" style="width: 80px; height: 78px;" /></a></div>';
                                
                                // التصنيف (ثلاثة مستويات)
                                $categoryHtml = '';
                                if (!empty($row['pc2_name'])) {
                                    $categoryHtml .= htmlspecialchars($row['pc2_name']) . ' / ';
                                }
                                if (!empty($row['pc1_name'])) {
                                    $categoryHtml .= htmlspecialchars($row['pc1_name']) . ' / ';
                                }
                                $categoryHtml .= htmlspecialchars($row['category_name'] ?? '');
                                
                                // السعر (من - إلى)
                                $priceHtml = number_format((float)($row['pd_fob_price'] ?? 0), 2);
                                if (!empty($row['pd_fob_price2'])) {
                                    $priceHtml .= ' - ' . number_format((float)($row['pd_fob_price2'] ?? 0), 2);
                                }
                                
                                // حالة المنتج
                                $status = (int)($row['pd_status'] ?? 0);
                                if ($status == 0) {
                                    $statusHtml = '<a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve"><img alt="Approve" src="images/active.jpg"></a>&nbsp;';
                                    $statusHtml .= '<a data-id="' . $row['pd_id'] . '" class="disapprove_product" title="Disapprove"><img alt="Disapprove" src="images/reject.png" width="19" height="19" border="0"></a>';
                                } elseif ($status == 1) {
                                    $statusHtml = '<font color="#009933" weight="800">Approved</font>&nbsp;</br><a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve">Re-send</a>';
                                } elseif ($status == 2) {
                                    $statusHtml = '<font color="#CC0000" weight="800">Rejected</font>&nbsp;</br><a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve">Re-send</a>';
                                }
                                
                                // تاريخ انتهاء العضوية
                                $expiryHtml = '';
                                if (!empty($row['expiry_date'])) {
                                    $expiryDate = (int)$row['expiry_date'];
                                    if ($expiryDate == 253392431400) {
                                        $expiryHtml = 'Permanent';
                                    } else {
                                        $expiryHtml = date('d F Y', $expiryDate) . ' ' . (date('Y-m-d', $expiryDate) > date('Y-m-d') ? 'Active' : 'Inactive');
                                    }
                                }
                                
                                // رابط الحذف
                                $dellink = $_SERVER['QUERY_STRING'] == "" 
                                    ? "?action=del&ad-id=" . $row['pd_id']
                                    : $_SERVER['QUERY_STRING'] . "&action=del&ad-id=" . $row['pd_id'];
                            ?>
                              <tr id="row_<?php echo $row['pd_id']; ?>">
                                 <td class="center"><input name="cb[]" class="ace" type="checkbox" value="<?php echo $row['pd_id']; ?>" /><span class="lbl"></span></td>
                                   <td><?php echo date('d M, y', strtotime($row['pd_date'] ?? 'now')); ?></td>
                                   <td><?php echo $imageHtml; ?></td>
                                   <td><?php echo htmlspecialchars(ucwords(stripslashes($row['pd_title'] ?? ''))); ?></td>
                                   <td><?php echo $categoryHtml; ?></td>
                                   <td><?php echo $priceHtml; ?></td>
                                   <td><?php echo htmlspecialchars($row['company_name'] ?? ''); ?></td>
                                   <td><?php echo htmlspecialchars($row['country_name'] ?? ''); ?></td>
                                   <td><?php echo htmlspecialchars($row['membership_type'] ?? 'Junior'); ?></td>
                                   <td><?php echo $expiryHtml; ?></td>
                                   <td><a href="product-details.php?token=<?php echo rand(1000,9999) . md5((string)$row['pd_id']); ?>"><img src="images/details.png" /></a></td>
                                   <td><?php echo $statusHtml; ?></td>
                                   <td>
                                    <a href="product-edit.php?fid=<?php echo $row['pd_id']; ?>" title="Edit"><img src="images/edit.jpg"/></a>
                                    <a href="<?php echo $dellink; ?>" onclick="return confirm('Are you sure to Delete the Product?')" title="Delete"><img src="images/delete.jpg"/></a>
                                   </td>
                                   <td>
                                    <a href="javascript:void(0)" onclick="addToSlider('saleoffer', <?php echo $row['pd_id']; ?>)" class="btn btn-xs btn-link">saleoffer</a> | 
                                    <a href="javascript:void(0)" onclick="addToSlider('leader', <?php echo $row['pd_id']; ?>)" class="btn btn-xs btn-link">leader products</a> | 
                                    <a href="javascript:void(0)" onclick="addToSlider('loyal', <?php echo $row['pd_id']; ?>)" class="btn btn-xs btn-link">loyal service</a>
                                   </td>
                               </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- عداد المنتجات أسفل الجدول -->
                <div class="row" style="margin-top: 15px;">
                    <div class="col-xs-6">
                        <div class="dataTables_info">
                            Showing 1 to <?php echo min(500, $totalProducts); ?> of <?php echo number_format($totalProducts); ?> entries
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#product-table').DataTable({
        "pageLength": 100,
        "lengthMenu": [[10, 25, 50, 100, 200, 300, 400, 500, -1], [10, 25, 50, 100, 200, 300, 400, 500, "All"]],
        "order": [[1, 'desc']],
        "language": {
            "search": "Search:",
            "lengthMenu": "Display _MENU_ records",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
    
    // Go to page functionality
    $("#page_no").on('keyup', function(e) {
        if (e.keyCode == 13) {
            var page = parseInt($(this).val()) - 1;
            if (!isNaN(page) && page >= 0 && page < table.page.info().pages) {
                table.page(page).draw('page');
            }
        }
    });
    
    // Select all functionality
    $('#selectAll').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
    });
});

// Functions for slider buttons
function addToSlider(type, id) {
    alert('Add to ' + type + ': ' + id);
}
</script>

<style>
.dataTables_filter {
    float: right;
    margin-bottom: 10px;
}
.dataTables_length {
    float: left;
    margin-bottom: 10px;
}
.dataTables_info {
    float: left;
    margin-top: 10px;
}
.dataTables_paginate {
    float: right;
    margin-top: 10px;
}
</style>
</script>
<style>
/* تقليل المسافات الفارغة */
.main-container {
    padding-top: 0 !important;
    margin-top: 0 !important;
}
.main-content {
    padding-top: 5px !important;
}
.breadcrumbs {
    margin-bottom: 5px !important;
    padding: 5px 10px !important;
}
.page-content {
    padding-top: 5px !important;
}
.table-header {
    margin-bottom: 10px !important;
}
.navbar {
    margin-bottom: 0 !important;
}

/* تقليل المسافات في DataTable */
.dataTables_length, .dataTables_filter {
    margin-bottom: 5px !important;
}
.dataTables_info, .dataTables_paginate {
    margin-top: 5px !important;
    padding-top: 5px !important;
}
.page-header {
    margin-top: 0 !important;
    padding-top: 5px !important;
    margin-bottom: 10px !important;
}
</style>
<style>
/* إزالة المسافات الزائدة */
.breadcrumbs + div {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
.row {
    margin-top: 0 !important;
}
.col-xs-12 {
    padding-top: 0 !important;
}
</style>
<style>
/* إصلاح عرض الجدول */
.table-responsive {
    overflow-x: auto !important;
    width: 100% !important;
    -webkit-overflow-scrolling: touch;
}
.table {
    min-width: 1100px;
    width: 100%;
    margin-bottom: 10px;
}
/* محاذاة الجدول إلى اليسار */
.table {
    direction: ltr !important;
} 
    /* تقليل عرض table-header */
.table-header {
    padding: 5px 10px !important;
    margin-bottom: 10px !important;
    min-height: 35px;
}
.table-header button {
    padding: 3px 8px !important;
    font-size: 11px !important;
}
.table-header p {
    font-size: 12px !important;
}

.table th, .table td {
    white-space: nowrap;
    padding: 6px 4px;
    font-size: 12px;
}
.table th:first-child, .table td:first-child {
    width: 40px;
}
.table th:last-child, .table td:last-child {
    width: 100px;
}
.table img {
    max-width: 50px;
    max-height: 50px;
}
</style>
</body>
</html>
<?php ob_end_flush(); ?>