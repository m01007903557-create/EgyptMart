<?php
/**
 * File: admin/company-list-back.php
 * Version: PHP 8.3
 * Description: عرض وإدارة قائمة الشركات في لوحة التحكم مع دعم DataTables
 * 
 * تعرض هذه الصفحة قائمة الشركات مع إمكانية الحذف والبحث والترقيم،
 * وتستخدم DataTables للعرض الديناميكي مع AJAX
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
check_user_login();

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
$p = new Pagination();
$page = $p->setpage();

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

// إعدادات الصفحة (للعرض الأولي)
$al->limit = $p->setlimit(10);
$al->setsql("SELECT * FROM country, business_profile bf 
             JOIN user u ON u.usr_id = bf.bnsprof_uid 
             LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
             WHERE country = cn_id 
               AND bnsprof_uid = usr_id 
               AND status = '1' 
             ORDER BY usr_id DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $p->setstart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "company-list.php";
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
                                            <th><strong>Name</strong></th>
                                            <th><strong>Web Address</strong></th>
                                            <th><strong>User</strong></th>
                                            <th><strong>Mobile Number</strong></th>
                                            <th><strong>Email</strong></th>
                                            <th><strong>Country</strong></th>
                                            <th><strong>State</strong></th>
                                            <th><strong>City</strong></th>
                                            <th><strong>Membership Expired On</strong></th>
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
                
                <!-- Pagination (للعرض الأولي فقط، DataTables تدير الترقيم الخاص بها) -->
                <?php if ($totalitems > $limit): ?>
                <div class="pager">
                    <?php echo $p->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring); ?>
                </div>
                <?php endif; ?>
                
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
    $.post("company-list-response.php", {verifyId: userId}, function(data) {
        if (data == 'Sent!') {
            $(element).parent().html('<font color="green">Verification email sent</font>');
        } else {
            alert("Error sending verification email");
        }
    }).fail(function() {
        alert("Error sending verification email");
    });
}

jQuery(function($) {
    
    // تهيئة DataTable مع تحميل البيانات عبر AJAX
    var oTable1 = $('#sample-table-2').dataTable({
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

</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>