<?php
/**
 * File: admin/contact_list.php
 * Version: PHP 8.3
 * Description: عرض وإدارة قائمة رسائل الاتصال في لوحة التحكم
 * 
 * تعرض هذه الصفحة قائمة رسائل الاتصال المرسلة من المستخدمين
 * مع إمكانية الحذف والترقيم والبحث
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة قائمة رسائل الاتصال
 */
class contactlist
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
     * جلب السجلات مع تطبيق الحدود
     * @return mysqli_result|bool نتيجة الاستعلام
     */
    public function listview()
    {
        $sql = $this->sqlList . " LIMIT " . (int)$this->start . ", " . (int)$this->limit;
        return mysqli_query($this->con, $sql);
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
     * حذف رسالة
     * @param int $adid معرف الرسالة
     */
    public function deleterecord(int $adid): void
    {
        $adid = (int)$adid;
        if ($adid > 0) {
            $sql = "DELETE FROM contact_us WHERE cu_id = " . $adid;
            mysqli_query($this->con, $sql);
        }
    }
    
    /**
     * إنشاء رابط الحذف
     * @param int $id معرف الرسالة
     * @return string رابط الحذف
     */
    public function deletelink(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&clid=" . $id;
        } else {
            $dellink = "contact_list.php?" . $_SERVER['QUERY_STRING'] . "&action=del&clid=" . $id;
        }
        return $dellink;
    }
}

// تهيئة كائن Pagination
$p = new Pagination();
$page = $p->setpage();

// تهيئة كائن القائمة
$al = new contactlist();

/******************** حذف سجل مفرد *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['clid'])) {
    $clid = (int)$_GET['clid'];
    if ($clid > 0) {
        $al->deleterecord($clid);
    }
    header("location: contact_list.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $p->setlimit(10);
$al->setsql("SELECT * FROM contact_us ORDER BY cu_updated_date DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $p->setstart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "contact_list.php";
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
    header("location: contact_list.php");
    exit();
}
/*************************************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrative Panel - Contact List</title>
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    
    <script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
    <script src="js/menu-collapsed.js" type="text/javascript"></script>
    
    <script type="text/javascript">
    /**
     * تحديد الكل أو إلغاء تحديده
     */
    function checkedAll() {
        var aa = document.getElementById('myform');
        var checkAllBox = document.getElementById('check_all');
        
        for (var i = 0; i < aa.elements.length; i++) {
            if (aa.elements[i].type == 'checkbox' && aa.elements[i].name == 'cb[]') {
                aa.elements[i].checked = checkAllBox.checked;
            }
        }
    }
    </script>
    
    <link href="style/pagination.css" type="text/css" rel="stylesheet"/>
    <link href="style/styles.css" type="text/css" rel="stylesheet">
</head>
<body>
    <div class="main">
        <?php include "includes/admin-top.php" ?>
        
        <div class="control_Panel">
            <?php include "includes/admin-left-con.php" ?>
            
            <div id="content-container">
                <div id="content">
                    <form name="myform" id="myform" method="post">
                        <h2>&rsaquo;&nbsp;&nbsp;Manage Contact&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Contact List</h2>
                        
                        <div id="whatsNew-grid" class="grid-view">
                            <table style="width:100%;">
                                <tr>
                                    <td style="width:80px;">
                                        <input name="btnDelete" type="submit" value="Delete" class="delete-btn" 
                                               onclick="return confirm('هل أنت متأكد من حذف السجلات المحددة؟')" />
                                    </td>
                                    <td><?php echo htmlspecialchars($showitems); ?></td>
                                    <td align="right">
                                        <div class="summary">
                                            <div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
                                                <select name="limit" id="limit" onchange="javascript:window.location.href='contact_list.php?page=<?php echo (int)$page; ?>&amp;limit='+this.value;">
                                                    <?php for ($i = 10; $i <= 40; $i += 10): ?>
                                                        <option value="<?php echo $i; ?>" <?php echo ($i == $limit) ? 'selected="selected"' : ''; ?>>
                                                            <?php echo $i; ?>
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            results per page.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
                            <table class="items">
                                <thead>
                                    <tr>
                                        <th class="checkbox" align="left" style="width:40px;">
                                            <input name="check_all" value="yes" id="check_all" type="checkbox" onclick="checkedAll();">
                                        </th>
                                        <th class="usr-name" style="width:140px;"><strong>Name</strong></th>
                                        <th class="usr-name" style="width:150px;"><strong>Email</strong></th>
                                        <th class="usr-name" style="width:90px;"><strong>Number</strong></th>
                                        <th class="usr-name" style="width:80px;"><strong>Detail</strong></th>
                                        <th class="usr-name" style="width:80px;"><strong>Date</strong></th>
                                        <th class="action" style="width:90px;"><strong>Action</strong></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php 
                                    $j = 1;
                                    if ($recObj && mysqli_num_rows($recObj) > 0):
                                        while ($row = mysqli_fetch_object($recObj)):
                                            $row_class = ($j % 2 == 1) ? 'row-clr' : '';
                                    ?>
                                            <tr class="<?php echo $row_class; ?>">
                                                <td class="checkbox">
                                                    <input name="cb[]" type="checkbox" value="<?php echo (int)$row->cu_id; ?>" />
                                                </td>
                                                <td class="usr-name" style="width:140px; text-align:center;">
                                                    <?php echo htmlspecialchars(ucwords($row->cu_name ?? '')); ?>
                                                </td>
                                                <td class="usr-name" style="width:110px; text-align:center;">
                                                    <?php echo htmlspecialchars($row->cu_email ?? ''); ?>
                                                </td>
                                                <td class="usr-name" style="width:90px; text-align:center;">
                                                    <?php echo htmlspecialchars($row->cu_contactnumber ?? ''); ?>
                                                </td>
                                                <td class="usr-name" style="width:80px; text-align:center">
                                                    <a class='ajax' href="contact_details.php?token=<?php echo rand(1000, 9999) . md5((string)$row->cu_id); ?>">
                                                        <strong>View</strong>
                                                    </a>
                                                </td>
                                                <td class="usr-name" style="width:80px; text-align:center">
                                                    <?php echo !empty($row->cu_updated_date) ? date('M d, Y', strtotime($row->cu_updated_date)) : ''; ?>
                                                </td>
                                                <td class="action" style="text-align:center;">
                                                    <a href="<?php echo $al->deletelink((int)$row->cu_id); ?>" 
                                                       title="Delete" 
                                                       onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')">
                                                        <img alt="delete" src="images/delete.jpg" border="0">
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php 
                                            $j++;
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="7" align="center" style="padding: 20px; color: #F00;">
                                                لا توجد رسائل اتصال
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            
                            <!-- Pagination -->
                            <div class="pager">
                                <?php echo $p->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring); ?>
                            </div>
                        </div>
                        
                        <br clear="all" />
                    </form>
                </div>
            </div>
        </div>
        
        <br clear="all" />
    </div>
    
    <?php include "includes/footer.php" ?>
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>