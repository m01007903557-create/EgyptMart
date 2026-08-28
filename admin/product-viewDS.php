<?php
/**
 * File: product-view.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: صفحة عرض وإدارة المنتجات مع دعم DataTables و AJAX
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once "../common.php";
require_once "../lib/pagination.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class AdminProductList
 * 
 * Handles product listing operations
 */
class AdminProductList {
    
    /** @var string SQL query */
    private string $sqlList = '';
    
    /** @var int Start offset for pagination */
    public int $start = 0;
    
    /** @var int Limit per page */
    public int $limit = 10;
    
    /**
     * Set SQL query
     * 
     * @param string $sql SQL query
     */
    public function setSql(string $sql): void {
        $this->sqlList = $sql;
    }
    
    /**
     * Get total records count
     * 
     * @return int Total records
     */
    public function getTotalRecords(): int {
        global $con;
        
        $result = mysqli_query($con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();
$limit = $pagination->getLimit(10);

// Initialize product list
$productList = new AdminProductList();

// ... باقي كود الملف (من هنا يبدأ الكود الأصلي) ...

?>
<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <!-- باقي محتوى الصفحة -->
</div>



<!-- jQuery and DataTables initialization -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>

<!-- DataTables scripts -->
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

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
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="product-view.php">إدارة المنتجات</a>
                    </li>
                    <li class="active">قائمة المنتجات</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to delete the selected records?')">
                                    <i class="icon-trash bigger-120"></i> Delete
                                </button>
                                
                                <p style="display: inline-block; float: right;">
                                    Go to Page No : 
                                    <input type="number" name="page_no" id="page_no" class="page_no" min="1" style="width:60px;" />
                                </p>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center">
                                                <label>
                                                    <input type="checkbox" class="ace" id="selectAll">
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>
                                            <th><strong>Date</strong></th>
                                            <th><strong>Image</strong></th>
                                            <th><strong>Title</strong></th>
                                            <th><strong>Category</strong></th>
                                            <th><strong>Price</strong></th>
                                            <th style="text-align:center"><strong>Posted by</strong></th>
                                            <th><strong>Country</strong></th>
                                            <th style="text-align:center"><strong>Membership type</strong></th>
                                            <th style="text-align:center"><strong>Membership Expired On</strong></th>
                                            <th>&nbsp;</th>    
                                            <th style="text-align:center"><strong>Status</strong></th>
                                            <th style="text-align:center"><strong>Action</strong></th>
                                            <th style="text-align:center"><strong>add Slider to</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                <br clear="all"/>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<!-- Inline scripts for DataTables functionality -->
<script type="text/javascript">
    jQuery(function($) {
        // إلغاء تعليق هذا الجزء
        var oTable1 = $('#sample-table-2').dataTable({
            "aoColumns": [
                { "bSortable": false },  // checkbox
                null,                     // date
                null,                     // product name
                null,                     // company name
                null,                     // country
                null,                     // price
                null,                     // unit
                null,                     // membership type
                null,                     // membership expiry
                null,                     // status
                { "bSortable": false },  // add to slider
                { "bSortable": false }   // actions
            ]
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
</script>

// Page number input handler
        $("#page_no").on('keyup', function() {
            var pageVal = $(this).val();
            if (pageVal !== '') {
                oTable1.fnPageChange(parseInt(pageVal) - 1);
            } else {
                oTable1.fnPageChange(0);
            }
        });
        
        // Select all checkbox
        $('table th input:checkbox').on('click', function() {
            var that = this;
            $(this).closest('table').find('tr > td:first-child input:checkbox')
                .each(function() {
                    this.checked = that.checked;
                    $(this).closest('tr').toggleClass('selected');
                });
        });
        
        // Tooltip placement
        $('[data-rel="tooltip"]').tooltip({
            placement: function(context, source) {
                var $source = $(source);
                var $parent = $source.closest('table');
                var off1 = $parent.offset();
                var w1 = $parent.width();
                var off2 = $source.offset();
                var w2 = $source.width();
                
                if (parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) {
                    return 'right';
                }
                return 'left';
            }
        });
    });
    
    // Approve product
    $(document).on('click', '.approve_product', function() {
        var pr_id = $(this).data('id');
        var parent = $(this).parent('td');
        
        $.ajax({
            url: "ajax-file/product_view_approval.php?id=" + pr_id,
            type: "get",
            success: function(res) {
                if (res == true) {
                    $(parent).html('<span class="label label-success">Approved</span>');
                }
            }
        });
    });
    
    // Disapprove product
    $(document).on('click', '.disapprove_product', function() {
        var pr_id = $(this).data('id');
        var parent = $(this).parent('td');
        
        $.ajax({
            url: "ajax-file/product_view_disapproval.php?id=" + pr_id,
            type: "get",
            success: function(res) {
                if (res == true) {
                    $(parent).html('<span class="label label-danger">Rejected</span>');
                }
            }
        });
    });
    
    // Add to slider
    $(document).on('click', '.add_slider', function() {
        var pr_id = $(this).data('id');
        var parent = $(this).parent('td');
        
        $.ajax({
            url: "ajax-file/product_view_slider.php?id=" + pr_id,
            type: "get",
            success: function(res) {
                $("#" + pr_id).hide();
                if (res == true) {
                    $(parent).html('<span class="label label-success">Added to slider</span>');
                }
            }
        });
    });
    
    // Remove from slider
    $(document).on('click', '.remove_slider', function() {
        var pr_id = $(this).data('id');
        var parent = $(this).parent('td');
        
        $.ajax({
            url: "ajax-file/product_view_remove_slider.php?id=" + pr_id,
            type: "get",
            success: function(res) {
                $("#" + pr_id).hide();
                if (res == true) {
                    $(parent).html('<span class="label label-warning">Removed from slider</span>');
                }
            }
        });
    });
    
    // Add to sales offer
    $(document).on('click', '.add_sales_offer', function() {
        var pr_id = $(this).data('id');
        var parent = $(this).parent('td');
        
        $.ajax({
            url: "ajax-file/add_sales_offer.php?id=" + pr_id,
            type: "get",
            success: function(res) {
                $("#" + pr_id).hide();
                if (res == true) {
                    $(parent).html('<span class="label label-success">Added to sales offer</span>');
                }
            }
        });
    });
</script>

</body>
</html>

<?php ob_end_flush(); ?>