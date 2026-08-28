<?php
/**
 * File: admin/active_gig-view.php
 * Version: PHP 8.3
 * Description: صفحة إدارة الخدمات النشطة في لوحة التحكم
 * 
 * تعرض هذه الصفحة قائمة الخدمات (Gigs) النشطة مع إمكانية الحذف والإدارة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
require_once "../common.php";
require_once "../lib/pagination.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة الخدمات (Gigs)
 */
class listGig
{
    public $sqlList = "";
    public $start = 0;
    public $limit = 0;
    
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
        global $con;
        $result = mysqli_query($con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    /**
     * جلب السجلات مع تطبيق الحدود
     * @return mysqli_result|bool نتيجة الاستعلام
     */
    public function listview()
    {
        global $con;
        $sql = $this->sqlList . " LIMIT " . (int)$this->start . "," . (int)$this->limit;
        return mysqli_query($con, $sql);
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
     * حذف سجل (تعطيله)
     * @param int $adid معرف السجل
     */
    public function deleterecord(int $adid): void
    {
        global $con;
        $adid = (int)$adid;
        if ($adid > 0) {
            $sql = "UPDATE gig SET g_status = '0' WHERE g_id = {$adid}";
            mysqli_query($con, $sql);
        }
    }
    
    /**
     * إنشاء رابط الحذف
     * @param int $id معرف السجل
     * @return string رابط الحذف
     */
    public function deletelink(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&fid=" . $id;
        } else {
            $dellink = "active_gig-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&fid=" . $id;
        }
        return $dellink;
    }
}

// تهيئة الكائنات
$p = new Pagination();
$page = $p->setpage();

$al = new listGig();

/******************** حذف سجل مفرد *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $fid = (int)$_GET['fid'];
    if ($fid > 0) {
        $al->deleterecord($fid);
    }
    header("location: active_gig-view.php");
    exit();
}
/*************************************************/

/******************** حذف متعدد *********************/
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $al->deleterecord((int)$id);
    }
    header("location: active_gig-view.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $p->setlimit(10);
$al->setsql("SELECT * FROM gig, subcategory, category 
             WHERE g_scat_id = scat_id 
               AND scat_cat_id = cat_id 
               AND g_status = '1' 
             ORDER BY g_id");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $p->setstart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "active_gig-view.php";
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
?>

<?php include "includes/admin-top.php" ?>

<script type="text/javascript">
/**
 * تغيير حالة الخدمة
 * @param {number} id - معرف الخدمة
 * @param {string} stat - الحالة الجديدة
 */
function changeStatus(id, stat) {
    $.post("change_gig_status.php", {id: id, stat: stat}, function(data) {
        alert(data);
    });
}
</script>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <form name="myform" id="myform" method="post">
                <h2>&rsaquo;&nbsp;&nbsp;إدارة الخدمات&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;قائمة الخدمات النشطة</h2>
                
                <div id="whatsNew-grid" class="grid-view">
                    <table>
                        <tr>
                            <td>
                                <input name="btnDelete" type="submit" value="حذف" class="delete-btn" 
                                       onclick="return confirm('هل أنت متأكد من حذف السجلات المحددة؟')" />
                            </td>
                            <td>
                                <input type="button" class="delete-btn" onclick="window.location ='gig-add.php'" value="إضافة خدمة">
                            </td>
                            <td><?php echo htmlspecialchars($showitems); ?></td>
                            <td align="right">
                                <div class="summary">
                                    <div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
                                        <select name="limit" id="limit" onchange="javascript:window.location.href='active_gig-view.php?page=<?php echo (int)$page; ?>&amp;limit='+this.value;">
                                            <?php for ($i = 10; $i <= 40; $i += 10): ?>
                                                <option value="<?php echo $i; ?>" <?php echo ($i == $limit) ? 'selected="selected"' : ''; ?>>
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    نتيجة لكل صفحة
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <table class="items">
                        <thead>
                            <tr>
                                <th class="checkbox" align="left" style="width:40px;">
                                    <input name="check_all" value="yes" id="check_all" type="checkbox" onclick="return checkedAll();">
                                </th>
                                <th class="usr-name" style="width:180px;"><strong>العنوان</strong></th>
                                <th class="usr-name" style="width:250px;"><strong>التصنيف</strong></th>
                                <th class="usr-name" style="width:400px;"><strong>الوصف</strong></th>
                                <th class="action"><strong>الإجراءات</strong></th>
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
                                        <input name="cb[]" type="checkbox" value="<?php echo (int)$row->g_id; ?>" />
                                    </td>
                                    <td class="usr-name" style="width:180px;">
                                        <?php echo htmlspecialchars($row->g_title ?? ''); ?>
                                    </td>
                                    <td class="usr-name" style="width:250px;">
                                        <?php 
                                        $scat_name = isset($row->scat_name) ? stripslashes($row->scat_name) : '';
                                        $cat_name = isset($row->cat_name) ? stripslashes($row->cat_name) : '';
                                        echo htmlspecialchars($scat_name) . " (" . htmlspecialchars($cat_name) . ")";
                                        ?>
                                    </td>
                                    <td class="usr-name" style="width:400px;">
                                        <?php echo htmlspecialchars($row->g_description ?? ''); ?>
                                    </td>
                                    <td class="action">
                                        <a href="gig-details.php?token=<?php echo md5((string)$row->g_id); ?>">تفاصيل</a>
                                    </td>
                                </tr>
                            <?php 
                                    $j++;
                                endwhile;
                            else:
                            ?>
                                <tr class="<?php echo ($j % 2 == 1) ? 'row-clr' : ''; ?>" align="center">
                                    <td colspan="5" align="center" style="color: #D00; padding: 20px;">
                                        لا توجد سجلات
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div class="pager">
                        <?php 
                        echo $p->getPaginationString(
                            $page, 
                            $totalitems, 
                            $limit, 
                            $adjacents, 
                            $targetpage, 
                            $pagestring
                        );
                        ?>
                    </div>
                </div>
                
                <br clear="all"/>
            </form>
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