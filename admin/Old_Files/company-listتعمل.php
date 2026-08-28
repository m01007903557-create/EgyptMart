<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../lib/pagination.php";
require_once "../common.php";

// التحقق من تسجيل دخول المستخدم
check_admin_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة قائمة الشركات
 */
class AdminLoginlist
{
    public $sqlList = "";
    public $start = 0;
    public $limit = 0;
    public $con;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $con;
        $this->con = $con;
    }
    
    /**
     * تعيين استعلام SQL
     * @param string $sql الاستعلام
     */
    public function setsql($sql): void
    {
        $this->sqlList = $sql;
    }
    
    /**
     * حساب إجمالي عدد السجلات
     * @return int عدد السجلات
     */
    public function totalrecord(): int
    {
        $result = mysqli_query($this->con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    /**
     * جلب السجلات
     * @return mysqli_result|bool نتيجة الاستعلام
     */
    public function listview()
    {
        return mysqli_query($this->con, $this->sqlList);
    }
    
    /**
     * حساب عدد الصفحات
     * @param int $rowPage عدد السجلات في الصفحة
     * @return int عدد الصفحات
     */
    public function numpage(int $rowPage): int
    {
        return (int)floor($this->totalrecord() / $rowPage);
    }
    
    /**
     * حذف شركة (من جدولي user و business_profile)
     * @param int $adid معرف المستخدم
     */
    public function deleterecord(int $adid): void
    {
        $adid = (int)$adid;
        if ($adid > 0) {
            // حذف من business_profile أولاً (بسبب علاقة المفاتيح الخارجية)
            $sql1 = "DELETE FROM business_profile WHERE bnsprof_uid = " . $adid;
            mysqli_query($this->con, $sql1);
            
            // حذف من user
            $sql2 = "DELETE FROM user WHERE usr_id = " . $adid;
            mysqli_query($this->con, $sql2);
        }
    }
    
    /**
     * إنشاء رابط الحذف
     * @param int $id معرف المستخدم
     * @return string رابط الحذف
     */
    public function deletelink(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&ad-id=" . $id;
        } else {
            $dellink = $_SERVER['QUERY_STRING'] . "&action=del&ad-id=" . $id;
        }
        return $dellink;
    }
}

// تهيئة كائن Pagination
$pagination = new Pagination();

// الحصول على الصفحة الحالية من URL
$page = $pagination->getCurrentPage();

// الحصول على عدد العناصر في الصفحة
$limit = $pagination->getLimit(20);

// تهيئة كائن القائمة
$al = new AdminLoginlist();

/******************** حذف سجل مفرد *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['ad-id'])) {
    $adid = (int)$_GET['ad-id'];
    if ($adid > 0) {
        $al->deleterecord($adid);
    }
    header("location: company-list.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $limit;
$sql = "SELECT * FROM country, business_profile bf 
        JOIN user u ON u.usr_id = bf.bnsprof_uid 
        LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
        WHERE country = cn_id 
          AND bnsprof_uid = usr_id 
          AND status = '1' 
        ORDER BY usr_id DESC";

$al->setsql($sql);

// حساب إجمالي السجلات
$totalitems = $al->totalrecord();

// حساب نقطة البداية للاستعلام
$start = $pagination->getStart($page, $limit, $totalitems);

// إضافة LIMIT إلى الاستعلام الأصلي
$sqlWithLimit = $sql . " LIMIT {$start}, {$limit}";
$al->setsql($sqlWithLimit);

// جلب السجلات
$recObj = $al->listview();

// عرض عدد العناصر
$showitems = ($start + 1) . "-";
if (($start + $limit) < $totalitems) {
    $showitems .= ($start + $limit);
} else {
    $showitems .= $totalitems;
}
$showitems .= " من " . $totalitems . " عنصر";

// إعدادات Pagination للعرض
$adjacents = 1;
$targetpage = "company-list.php";
$pagestring = "?limit=" . $limit . "&page=";

/******************** حذف متعدد *********************/
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $al->deleterecord($id);
        }
    }
    header("location: company-list.php");
    exit();
}
/*************************************************/
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

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="company-list.php">Manage Company Profile</a>
                    </li>
                    <li class="active">View Company</li>
                </ul><!-- .breadcrumb -->
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                &nbsp;<button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('هل أنت متأكد من حذف السجلات المحددة؟')">
                                    <i class="icon-trash bigger-120"></i>Delete
                                </button>

                                <p style="display: inline-block; float: right;">
                                    Go to Page No : <input type="number" name="page_no" id="page_no" class="page_no" min="1" style="width: 60px;" />
                                </p>

                                <p style="display: inline-block; float: left; margin-left: 20px;">
                                    <input type="text" id="mySearchText" placeholder="Search..." style="width: 200px; padding: 5px;" 
                                           onkeypress="if (event.keyCode === 13) { event.preventDefault(); return false; }">
                                    <button class="btn btn-xs btn-primary" id="mySearchButton">
                                        <i class="icon-search"></i> Search
                                    </button>
                                </p>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center"><label><input type="checkbox" class="ace" id="check_all"><span class="lbl"></span></label></th>
                                            <th><strong>Date</strong></th>
                                            <th><strong>Name</strong></th>
                                            <th><strong>Web Address</strong></th>
                                            <th><strong>User</strong></th>
                                            <th><strong>Mobile Number</strong></th>
                                            <th><strong>Email</strong></th>
                                            <th><strong>Country</strong></th>
                                            <th><strong>State</strong></th>
                                            <th><strong>City</strong></th>
                                            <th style="text-align:center"><strong>Membership type</strong></th>
                                            <th style="text-align:center"><strong>Membership Expired On</strong></th>
                                            <th style="text-align:center"><strong>Status</strong></th>
                                            <th><strong>&nbsp;</strong></th>
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
                
                <!-- Pagination Info -->
                <div class="row">
                    <div class="col-xs-6">
                        <div class="dataTables_info">
                            عرض <?php echo $showitems; ?>
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <?php
                        // إنشاء كلاس Pagination مساعد للعرض
                        class PaginationHelper {
                            public static function getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring) {
                                $totalpages = ceil($totalitems / $limit);
                                if ($totalpages <= 1) return '';
                                
                                $html = '<div class="dataTables_paginate paging_bootstrap">';
                                $html .= '<ul class="pagination">';
                                
                                // Previous button
                                if ($page > 1) {
                                    $html .= '<li class="prev"><a href="' . $targetpage . $pagestring . ($page - 1) . '"><i class="icon-double-angle-left"></i></a></li>';
                                } else {
                                    $html .= '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>';
                                }
                                
                                // Page numbers
                                $start = ($page - $adjacents > 0) ? $page - $adjacents : 1;
                                $end = ($page + $adjacents <= $totalpages) ? $page + $adjacents : $totalpages;
                                
                                for ($i = $start; $i <= $end; $i++) {
                                    $activeClass = ($i == $page) ? 'active' : '';
                                    $html .= '<li class="' . $activeClass . '"><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                                }
                                
                                // Next button
                                if ($page < $totalpages) {
                                    $html .= '<li class="next"><a href="' . $targetpage . $pagestring . ($page + 1) . '"><i class="icon-double-angle-right"></i></a></li>';
                                } else {
                                    $html .= '<li class="next disabled"><a href="#"><i class="icon-double-angle-right"></i></a></li>';
                                }
                                
                                $html .= '</ul>';
                                $html .= '</div>';
                                return $html;
                            }
                        }
                        
                        echo PaginationHelper::getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring);
                        ?>
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
/**
 * دالة التحقق من البريد الإلكتروني
 * @param {number} userId - معرف المستخدم
 * @param {HTMLElement} element - العنصر الذي تم النقر عليه
 */
function verifyNow(userId, element) {
    $.post('company-list-response.php', {verifyId: userId}, function(response) {
        if (response == 'Sent!' || response.includes('Sent')) {
            $('#sample-table-2').find('#verify-link-' + userId).html('<span style="color:green">Email sent!</span>');
        } else {
            alert("Error sending verification email");
        }
    }).fail(function() {
        alert("Error sending verification email");
    });
}

jQuery(function($) {
    
    // تهيئة DataTable مع تحميل البيانات عبر AJAX
    var oTable1 = $('#sample-table-2').on('search.dt', function(event) {
        event.preventDefault();
        console.log('Search');
        return false;
    }).dataTable({
        "ajax": {
            url: "company-list-response.php",
            type: "post",
            error: function() {
                $("#sample-table-2").css("display", "none");
                $("#sample-table-2").after('<div class="alert alert-danger">فشل في تحميل البيانات</div>');
            }
        },
        "lengthMenu": [10, 25, 50, 100, 200, 500],
        "bProcessing": true,
        "bFilter": false,
        "ordering": false,
        "searching": true,
        "dom": 'rlitp', // r - processing, l - length, i - information, t - table, p - pagination
        "serverSide": true,
        "aoColumns": [
            { "bSortable": false },
            null,
            null,
            null,
            null,
            null,
            null,
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
    
    // البحث المخصص
    $('#mySearchButton').on('click', function(event) {
        event.preventDefault();
        oTable1.fnFilter($('#mySearchText').val());
        return false;
    });
    
    // البحث بالضغط على Enter
    $('#mySearchText').on('keypress', function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            oTable1.fnFilter($(this).val());
            return false;
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

</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>