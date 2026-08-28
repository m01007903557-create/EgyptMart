<?php
define('ACCESS_ALLOWED', true);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
} else {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
        die("Direct Access Not Allowed");
    }
}

// تضمين الملفات
require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";
require_once dirname(__DIR__) . "/includes/rfq_functions.php";

if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once  "../common.php";

// التحقق من تسجيل دخول المستخدم
check_admin_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}


class AdminLoginlist
{
    public $sqlList = "";
    public $start = 0;
    public $limit = 0;
    public $con;
  
    public function __construct()
    {
        global $con;
        $this->con = $con;
        $this->sqlList = "SELECT * FROM buy_requirement WHERE source_channel = 'website' ORDER BY br_posting_date DESC";
    }

  
    public function setsql($sql): void
    {
        $this->sqlList = $sql;
    }
 
    public function totalrecord(): int
    {
        $sql = "SELECT COUNT(*) as total FROM buy_requirement WHERE source_channel = 'website'";
        $result = mysqli_query($this->con, $sql);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }
    
    public function listview()
    {
        return mysqli_query($this->con, $this->sqlList);
    }
    
    public function numpage(int $rowPage): int
    {
        return (int)floor($this->totalrecord() / $rowPage);
    }
    
    public function deleterecord(int $adid): void
    {
        $adid = (int)$adid;
        
        if ($adid > 0) {
            $check_sql = "SELECT br_id FROM buy_requirement WHERE br_id = ?";
            $check_stmt = mysqli_prepare($this->con, $check_sql);
            mysqli_stmt_bind_param($check_stmt, 'i', $adid);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                $log_sql = "INSERT INTO rfq_activity_log (br_id, action, source, details, log_date) VALUES (?, 'deleted', 'admin', 'تم حذف طلب من المنصة (ID: '||?||')', NOW())";
                $log_stmt = mysqli_prepare($this->con, $log_sql);
                $details = "تم حذف طلب من المنصة (ID: $adid)";
                mysqli_stmt_bind_param($log_stmt, 'is', $adid, $details);
                mysqli_stmt_execute($log_stmt);
                mysqli_stmt_close($log_stmt);
                
                $sql = "DELETE FROM buy_requirement WHERE br_id = ?";
                $stmt = mysqli_prepare($this->con, $sql);
                mysqli_stmt_bind_param($stmt, 'i', $adid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            mysqli_stmt_close($check_stmt);
        }
    }
    
    public function approve_record(int $aid): void
    {
        $aid = (int)$aid;
        if ($aid > 0) {
            $sql = "UPDATE buy_requirement SET br_approval_status = '1' WHERE br_id = " . $aid;
            mysqli_query($this->con, $sql);
        }
    }
    
    public function disapprove_record(int $aid): void
    {
        $aid = (int)$aid;
        if ($aid > 0) {
            $sql = "UPDATE buy_requirement SET br_approval_status = '2' WHERE br_id = " . $aid;
            mysqli_query($this->con, $sql);
        }
    }
    

    public function deletelink(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&ad-id=" . $id;
        } else {
            $dellink = $_SERVER['QUERY_STRING'] . "&action=del&ad-id=" . $id;
        }
        return $dellink;
    }
    
    public function approve(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $plink = "?action=appr&id=" . $id;
        } else {
            $plink = "buyreq-view.php?" . $_SERVER['QUERY_STRING'] . "&action=appr&id=" . $id;
        }
        return $plink;
    }
    
    public function disapprove(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $plink = "?action=disappr&id=" . $id;
        } else {
            $plink = "buyreq-view.php?" . $_SERVER['QUERY_STRING'] . "&action=disappr&id=" . $id;
        }
        return $plink;
    }
}

// تهيئة كائن Pagination
$pagination = new Pagination();
$page = $pagination->getCurrentPage();

$al = new AdminLoginlist();

if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['ad-id'])) {
    $adid = (int)$_GET['ad-id'];
    if ($adid > 0) {
        $al->deleterecord($adid);
    }
    header("location: buyreq-view.php");
    exit();
}

/******************** الموافقة على طلب شراء *********************/
if (isset($_GET['action']) && $_GET['action'] == "appr" && isset($_GET['id'])) {
    $pid = (int)$_GET['id'];
    $al->approve_record($pid);
    
    if ($pid > 0) {
        // جلب معلومات المستخدم والشركة
        $get_user = "SELECT u.*, bf.* 
                     FROM user u 
                     LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid 
                     WHERE u.usr_id = (SELECT br_u_id FROM buy_requirement WHERE br_id = " . $pid . ")";
        $res_user = mysqli_query($con, $get_user);
        
        if ($res_user && mysqli_num_rows($res_user) > 0) {
            $suser = mysqli_fetch_object($res_user);
            
            $contact_details = '<strong>' . htmlspecialchars($suser->bnsprof_compname ?? '') . '</strong><br/>' .
                               htmlspecialchars($suser->bnsprof_address1 ?? '') . '<br/>' .
                               'Mobile/Cell Phone: ' . htmlspecialchars($suser->mobile1 ?? '') . '<br/>' .
                               'Email: ' . htmlspecialchars($suser->email ?? '');
            
            $suname = ($suser->name_prefix ?? '') . ' ' . 
                      ($suser->fname ?? '') . ' ' . 
                      ($suser->lname ?? '');
            
            $to = $suser->email ?? '';
            
            // جلب معلومات طلب الشراء
            $get_product = "SELECT * FROM buy_requirement WHERE br_id = " . $pid;
            $res_product = mysqli_query($con, $get_product);
            
            if ($res_product && mysqli_num_rows($res_product) > 0) {
                $sproduct = mysqli_fetch_object($res_product);
                
                $product_img = 'https://www.egyptmart.shop/upload/buy_requirement/thumb/no-image.png';
                if (!empty($sproduct->br_pic)) {
                    $product_img = 'https://www.egyptmart.shop/upload/buy_requirement/thumb/' . $sproduct->br_pic;
                }
                
                $product_title = $sproduct->br_pd_name ?? '';
                $product_moq = $sproduct->br_estimate_qty ?? '';
                
                // جلب وحدة القياس
                $unitsql = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_status = '1' AND mu_id = " . (int)($sproduct->br_estimate_qty_unit ?? 0));
                $product_type = '';
                if ($unitsql && mysqli_num_rows($unitsql) > 0) {
                    $unitrow = mysqli_fetch_object($unitsql);
                    $product_type = $unitrow->mu_name ?? '';
                }
                
                $product_price = ($sproduct->br_apprx_order_value ?? '') . ' ' . ($sproduct->br_apprx_order_currency ?? '');
                
                // إعداد البريد الإلكتروني
                $subject = "الموافقة على نشر طلب شرائك";
                $from_name = get_page_settings(4);
                $from_email = get_adminemail();
                $usr_email = $to;
                
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: $from_name <$from_email>\r\n";
                
                // تضمين قالب البريد الإلكتروني
                $message2 = '';
                if (file_exists("email/admin_buylead_approve.php")) {
                    //include "email/admin_buylead_approve.php";
                }
                
                // إرسال البريد الإلكتروني
                if (!empty($to)) {
                    mail($to, $subject, $message2, $headers);
                }
                
                // التوجيه إلى صفحة التأكيد
                header('Location: ../buylead-alert-mail.php?br_id=' . $pid);
                exit();
            }
        }
    }
    
    header("location: buyreq-view.php");
    exit();
}
/*************************************************/

/******************** رفض طلب شراء *********************/
if (isset($_GET['action']) && $_GET['action'] == "disappr" && isset($_GET['id'])) {
    $aid = (int)$_GET['id'];
    if ($aid > 0) {
        $al->disapprove_record($aid);
    }
    header("location: buyreq-view.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة (للعرض الأولي)
$al->limit = $pagination->getLimit(10);
$al->setsql("SELECT br.*, u.fname, u.lname, u.email 
             FROM buy_requirement br
             LEFT JOIN user u ON u.usr_id = br.br_u_id
             WHERE br.source_channel = 'website' 
             ORDER BY br.br_posting_date DESC");
             
$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "buyreq-view.php";
$pagestring = "?limit=" . $limit . "&page=";

$recObj = $al->listview();

// عرض عدد العناصر
$showitems = ($al->start + 1) . "-";
if (($al->start + $limit) < $totalitems) {
    $showitems .= ($al->start + $limit);
} else {
    $showitems .= $totalitems;
}
$showitems .= " من " . $al->totalrecord() . " عنصر";

/******************** حذف متعدد *********************/
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $al->deleterecord($id);
        }
    }
    header("location: buyreq-view.php");
    exit();
}
/*************************************************/
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
                    <li>
                        <a>Manage Buy Requirement</a>
                    </li>
                    <li class="active">View Buy Requirement</li>
                </ul><!-- .breadcrumb -->
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('هل أنت متأكد من حذف السجلات المحددة؟')">
                                    <i class="icon-trash bigger-120"></i>Delete
                                </button>
                                <p style="display: inline-block; float: right;">
                                    Go to Page No : <input type="number" name="page_no" id="page_no" class="page_no" min="1" style="width: 60px;" />
                                </p>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center"><label><input type="checkbox" class="ace" id="check_all"><span class="lbl"></span></label></th>
                                            <th style="text-align:center"><strong>Date</strong></th>
                                            <th><strong>Image</strong></th>
                                            <th><strong>Product/Service</strong></th>
                                            <th><strong>Requirement in Detail</strong></th>
                                            <th><strong>Posted By</strong></th>
                                            <th><strong>Country</strong></th>
                                            <th style="text-align:center"><strong>Membership type</strong></th>
                                            <th style="text-align:center;"><strong>Status</strong></th>
                                            <th style="text-align:center"><strong>Action</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- يتم تعبئة البيانات عبر AJAX -->
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
    
    // تهيئة DataTable مع تحميل البيانات عبر AJAX
    var oTable1 = $('#sample-table-2').dataTable({
        "ajax": {
            url: "buyreq-view-response.php",
            type: "post",
            error: function() {
                $("#sample-table-2").css("display", "none");
                $("#sample-table-2").after('<div class="alert alert-danger">فشل في تحميل البيانات</div>');
            }
        },
        "lengthMenu": [10, 25, 50, 100, 200, 500],
        "bProcessing": true,
        "serverSide": true,
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": false },
            null,
            null,
            null,
            null,
            null,
            null,
            { "bSortable": false },
            { "bSortable": false }
        ],
        "drawCallback": function(settings) {
            $("#overlay").hide();
        },
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Arabic.json"
        }
    });
    
    // التنقل بين الصفحات
    $("#page_no").on('keyup', function(e) {
        if (e.keyCode == 13) { // Enter key
            var page = parseInt($(this).val());
            if (page > 0 && !isNaN(page)) {
                oTable1.fnPageChange(page - 1);
            } else {
                oTable1.fnPageChange(0);
            }
        }
    });
    
    // تحديد الكل
    $('#check_all').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox').each(function() {
            this.checked = that.checked;
            $(this).closest('tr').toggleClass('selected');
        });
    });
    
    // أداة المساعدة
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
<script>
// تعطيل أي تحديث تلقائي للصفحة
(function() {
    var highestTimeoutId = setTimeout(";");
    for (var i = 0; i < highestTimeoutId; i++) {
        clearTimeout(i);
        clearInterval(i);
    }
    console.log("Auto-refresh disabled for buyreq-view.php");
})();
</script>
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>