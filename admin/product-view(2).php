<?php
/**
 * File: product-view.php
 * Version: 3.0.0 (PHP 8.3)
 * Description: عرض المنتجات في لوحة التحكم - النسخة الكاملة
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
                    <li><a href="product-view.php">Manage Products</a></li>
                    <li class="active">Product View</li>
                </ul>
            </div>
            
            <div class="page-content">
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
                                    LIMIT 100";
                            $result = mysqli_query($con, $sql);
                            
                            while ($row = mysqli_fetch_assoc($result)):
                                // ===== معالجة الصورة (مع الشعار) =====
                                $imageHtml = '<div style="position: relative;">';
                                $img = $row['pd_image'] ?? '';
                                $imgArr = explode(',', $img);
                                $mainImg = !empty($imgArr[0]) ? $imgArr[0] : 'noimage.jpg';
                                
                                if (!empty($row['pd_imagelogo'])) {
                                    $logoArr = explode(',', $row['pd_imagelogo']);
                                    $imageHtml .= '<a href="#"><img src="../upload/myproduct/' . htmlspecialchars($logoArr[0]) . '" style="position: absolute; top: 48px; width: 30px; height: 29px;" /></a>';
                                }
                                $imageHtml .= '<a href="#"><img src="../upload/myproduct/' . htmlspecialchars($mainImg) . '" style="width: 80px; height: 78px;" /></a></div>';
                                
                                // ===== التصنيف (ثلاثة مستويات) =====
                                $categoryHtml = '';
                                if (!empty($row['pc2_name'])) {
                                    $categoryHtml .= htmlspecialchars($row['pc2_name']) . ' / ';
                                }
                                if (!empty($row['pc1_name'])) {
                                    $categoryHtml .= htmlspecialchars($row['pc1_name']) . ' / ';
                                }
                                $categoryHtml .= htmlspecialchars($row['category_name'] ?? '');
                                
                                // ===== السعر (من - إلى) =====
                                $priceHtml = '';
                                if (!empty($row['pd_fob_price'])) {
                                    $priceHtml = number_format((float)($row['pd_fob_price'] ?? 0), 2);
                                    if (!empty($row['pd_fob_price2'])) {
                                        $priceHtml .= ' - ' . number_format((float)($row['pd_fob_price2'] ?? 0), 2);
                                    }
                                }
                                
                                // ===== حالة المنتج =====
                                $status = (int)($row['pd_status'] ?? 0);
                                if ($status == 0) {
                                    $statusHtml = '<a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve"><img alt="Approve" src="images/active.jpg"></a>&nbsp;';
                                    $statusHtml .= '<a data-id="' . $row['pd_id'] . '" class="disapprove_product" title="Disapprove"><img alt="Disapprove" src="images/reject.png" width="19" height="19" border="0"></a>';
                                } elseif ($status == 1) {
                                    $statusHtml = '<font color="#009933" weight="800">Approved</font>&nbsp;</br><a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve">Re-send</a>';
                                } elseif ($status == 2) {
                                    $statusHtml = '<font color="#CC0000" weight="800">Rejected</font>&nbsp;</br><a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve">Re-send</a>';
                                }
                                
                                // ===== تاريخ انتهاء العضوية =====
                                $expiryHtml = '';
                                if (!empty($row['expiry_date'])) {
                                    $expiryDate = (int)$row['expiry_date'];
                                    if ($expiryDate == 253392431400) {
                                        $expiryHtml = 'Permanent';
                                    } else {
                                        $expiryHtml = date('d F Y', $expiryDate) . ' ' . (date('Y-m-d', $expiryDate) > date('Y-m-d') ? 'Active' : 'Inactive');
                                    }
                                }
                                
                                // ===== رابط الحذف =====
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
                                    <a data-id="saleoffer-<?php echo $row['pd_id']; ?>" id="saleoffer-<?php echo $row['pd_id']; ?>" class="add_sales_offer" title="Sale Offer">saleoffer</a> | 
                                    <a data-id="leader-<?php echo $row['pd_id']; ?>" id="leader-<?php echo $row['pd_id']; ?>" class="add_slider" title="Leader Products">leader products</a> | 
                                    <a data-id="loyal-<?php echo $row['pd_id']; ?>" id="loyal-<?php echo $row['pd_id']; ?>" class="add_slider" title="Loyal Service">loyal service</a>
                                 </td>
                              </tr>
                            <?php endwhile; ?>
                        </tbody>
                     </table>
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
    $('#product-table').DataTable({
        "pageLength": 10,
        "order": [[1, 'desc']],
        "language": {
            "search": "Search:",
            "lengthMenu": "Display _MENU_ records",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
    
    // تحديد الكل
    $('#selectAll').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
    });
});

function addToSlider(id) {
    alert('Add to slider: ' + id);
}
</script>

</body>
</html>
<?php ob_end_flush(); ?>