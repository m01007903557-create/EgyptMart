<?php
// ✅ الحفاظ على التعريف القديم
define('ACCESS_ALLOWED', true);

// ✅ بدء الجلسة إذا لم تبدأ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ السماح بطلبات AJAX من نفس الموقع
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // هذا طلب AJAX، استمر دون التحقق الإضافي
} else {
    // طلب عادي، تأكد من الإحالة
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
        die("Direct Access Not Allowed");
    }
}

// تضمين الملفات
require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

// تعريف ملف القائمة الجانبية
$file = "whatsapp_rfq";

// بدء المخزن المؤقت
ob_start();

// تضمين الملفات الأساسية
require_once "../common.php";

// التحقق من تسجيل دخول المستخدم
check_admin_login();

// ✅ تحديد اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// =============================================
// ✅ دوال خاصة بصفحة واتساب مع فلتر source_channel
// =============================================

/**
 * جلب قائمة طلبات واتساب مع فلتر source_channel
 */
function get_whatsapp_rfq_list($start, $limit, $search = '', $status = 'all') {
    global $con;
    
    // ✅ استخدم الأعمدة الموجودة فعلاً
    $sql = "SELECT br.*, u.fname, u.lname, u.email, u.mobile1, u.country,
                   c.cn_name
            FROM buy_requirement br
            LEFT JOIN user u ON u.usr_id = br.br_u_id
            LEFT JOIN country c ON c.cn_id = u.country
            WHERE br.source_channel = 'whatsapp_platform'";
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($con, $search);
        $sql .= " AND (br.br_id LIKE '%$search%' 
                       OR br.br_pd_name LIKE '%$search%' 
                       OR br.br_requirement LIKE '%$search%'
                       OR u.fname LIKE '%$search%'
                       OR u.lname LIKE '%$search%'
                       OR u.mobile1 LIKE '%$search%')";
    }
    
    if ($status != 'all') {
        $status = mysqli_real_escape_string($con, $status);
        $sql .= " AND br.br_status = '$status'";
    }
    
    $sql .= " ORDER BY br.br_posting_date DESC LIMIT $start, $limit";
    return mysqli_query($con, $sql);
}

/**
 * جلب عدد طلبات واتساب الإجمالي مع الفلتر
 */
function get_whatsapp_rfq_count($search = '', $status = 'all') {
    global $con;
    
    $sql = "SELECT COUNT(*) as total 
            FROM buy_requirement br
            LEFT JOIN user u ON u.usr_id = br.br_u_id
            WHERE br.source_channel = 'whatsapp_platform'";
    
    if (!empty($search)) {
        $search = mysqli_real_escape_string($con, $search);
        $sql .= " AND (br.br_id LIKE '%$search%' 
                       OR br.br_pd_name LIKE '%$search%' 
                       OR br.br_requirement LIKE '%$search%'
                       OR u.fname LIKE '%$search%'
                       OR u.lname LIKE '%$search%'
                       OR u.mobile1 LIKE '%$search%')";
    }
    
    if ($status != 'all') {
        $status = mysqli_real_escape_string($con, $status);
        $sql .= " AND br.br_status = '$status'";
    }
    
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    return (int)($row['total'] ?? 0);
}

/**
 * حذف طلب واتساب
 */
function delete_whatsapp_rfq($id) {
    global $con;
    $id = (int)$id;
    if ($id > 0) {
        $sql = "DELETE FROM buy_requirement WHERE br_id = $id AND source_channel = 'whatsapp_platform'";
        return mysqli_query($con, $sql);
    }
    return false;
}

/**
 * نشر طلب واتساب إلى المنصة (تغيير الحالة)
 */
function publish_to_public_rfq($id) {
    global $con;
    $id = (int)$id;
    if ($id > 0) {
        $sql = "UPDATE buy_requirement SET br_status = '1', br_approval_status = '1' 
                WHERE br_id = $id AND source_channel = 'whatsapp_platform'";
        return mysqli_query($con, $sql);
    }
    return false;
}

/**
 * إرسال إشعار واتساب للمورد
 */
function send_whatsapp_notification($br_id) {
    global $con;
    $br_id = (int)$br_id;
    
    if ($br_id <= 0) return false;
    
    // جلب بيانات الطلب والمورد
    $sql = "SELECT br.*, u.fname, u.lname, u.mobile1, u.email
            FROM buy_requirement br
            LEFT JOIN user u ON u.usr_id = br.br_u_id
            WHERE br.br_id = $br_id AND br.source_channel = 'whatsapp_platform'";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    
    if (!$row) return false;
    
    // ✅ هنا يمكنك إضافة كود إرسال واتساب الفعلي
    // مثال باستخدام Ultramsg API
    $message = "📋 طلب شراء جديد\n";
    $message .= "🆔 رقم الطلب: " . $row['br_id'] . "\n";
    $message .= "📦 المنتج: " . $row['br_pd_name'] . "\n";
    $message .= "📝 التفاصيل: " . substr($row['br_requirement'], 0, 100) . "...\n";
    $message .= "👤 المشتري: " . ($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '') . "\n";
    $message .= "📱 الجوال: " . ($row['mobile1'] ?? 'غير متوفر') . "\n";
    $message .= "🕐 التاريخ: " . date('Y-m-d H:i:s');
    
    // ✅ تحديث حالة الإرسال في قاعدة البيانات
    $update_sql = "UPDATE buy_requirement 
                   SET whatsapp_sent = 1, 
                       whatsapp_sent_date = NOW(),
                       wa_status = 'sent_to_supplier'
                   WHERE br_id = $br_id";
    mysqli_query($con, $update_sql);
    
    return true;
}

// =============================================
// معالجة المتغيرات وعرض الصفحة
// =============================================
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// جلب البيانات
$total = get_whatsapp_rfq_count($search, $status);
$total_pages = ceil($total / $limit);
$results = get_whatsapp_rfq_list($start, $limit, $search, $status);

// =============================================
// إحصائيات واتساب
// =============================================
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN wa_supplier_read = 1 THEN 1 ELSE 0 END) as read_count,
    SUM(CASE WHEN wa_accepted = 1 THEN 1 ELSE 0 END) as accepted_count,
    SUM(CASE WHEN br_status = '0' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN br_status = '1' THEN 1 ELSE 0 END) as sent_count
    FROM buy_requirement 
    WHERE source_channel = 'whatsapp_platform'";
$stats_result = mysqli_query($con, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

// =============================================
// معالجة الإجراءات (حذف، نشر، إرسال واتساب)
// =============================================
$message = '';

// معالجة الإجراءات الجماعية
if (isset($_POST['action']) && isset($_POST['br_ids']) && is_array($_POST['br_ids'])) {
    $br_ids = $_POST['br_ids'];
    $action = $_POST['action'];
    
    foreach ($br_ids as $id) {
        $id = (int)$id;
        if ($action == 'delete') {
            delete_whatsapp_rfq($id);
            $message = '<div class="alert alert-success">✅ تم حذف الطلبات المحددة</div>';
        } elseif ($action == 'publish') {
            publish_to_public_rfq($id);
            $message = '<div class="alert alert-success">✅ تم نشر الطلبات المحددة</div>';
        } elseif ($action == 'send_whatsapp') {
            send_whatsapp_notification($id);
            $message = '<div class="alert alert-success">✅ تم إرسال إشعارات واتساب للموردين</div>';
        }
    }
}

// معالجة الإجراءات الفردية (من رابط GET)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        delete_whatsapp_rfq($id);
        $message = '<div class="alert alert-success">✅ تم حذف الطلب</div>';
    } elseif ($action == 'publish') {
        publish_to_public_rfq($id);
        $message = '<div class="alert alert-success">✅ تم نشر الطلب</div>';
    } elseif ($action == 'send_whatsapp') {
        send_whatsapp_notification($id);
        $message = '<div class="alert alert-success">✅ تم إرسال إشعار واتساب للمورد</div>';
    }
}

// =============================================
// تعريف الحالات للعرض
// =============================================
$status_display = [
    '0' => 'قيد الانتظار',
    '1' => 'تم الإرسال',
    '2' => 'في انتظار الرد',
    '3' => 'مغلق',
    '4' => 'ملغي'
];

$status_class = [
    '0' => 'warning',
    '1' => 'info',
    '2' => 'primary',
    '3' => 'success',
    '4' => 'danger'
];
?>

<?php include "includes/admin-top.php"; ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' ,'fixed');}catch(e){}
    </script>
    
    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php"; ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed');}catch(e){}
                </script>
                
                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="whatsapp_rfq.php">WhatsApp RFQ</a>
                    </li>
                    <li class="active">جميع الطلبات</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        <i class="fa fa-whatsapp" style="color:#25D366;"></i>
                        طلبات WhatsApp RFQ
                        <small class="text-muted">نظام مستقل - غير مرتبط بالإيميل</small>
                    </h1>
                </div>
                
                <?php echo $message; ?>
                
                <!-- ✅ عرض الإحصائيات -->
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-md-2">
                        <div class="well well-sm text-center" style="background:#f5f5f5;">
                            <h3><?php echo $stats['total'] ?? 0; ?></h3>
                            <p>📊 إجمالي الطلبات</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="well well-sm text-center" style="background:#f5f5f5;">
                            <h3><?php echo $stats['pending_count'] ?? 0; ?></h3>
                            <p>⏳ قيد الانتظار</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="well well-sm text-center" style="background:#f5f5f5;">
                            <h3><?php echo $stats['sent_count'] ?? 0; ?></h3>
                            <p>📤 تم الإرسال</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="well well-sm text-center" style="background:#f5f5f5;">
                            <h3><?php echo $stats['read_count'] ?? 0; ?></h3>
                            <p>📖 تم القراءة</p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="well well-sm text-center" style="background:#f5f5f5;">
                            <h3><?php echo $stats['accepted_count'] ?? 0; ?></h3>
                            <p>✅ تم القبول</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <!-- شريط البحث والفلتر -->
                        <div class="well well-sm">
                            <form method="GET" class="form-inline">
                                <div class="form-group">
                                    <input type="text" name="search" class="form-control" placeholder="بحث: RFQ ID, منتج, تفاصيل, مشتري" value="<?php echo htmlspecialchars($search); ?>" style="width:300px;">
                                </div>
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="all">جميع الحالات</option>
                                        <option value="0" <?php echo $status=='0'?'selected':''; ?>>قيد الانتظار</option>
                                        <option value="1" <?php echo $status=='1'?'selected':''; ?>>تم الإرسال</option>
                                        <option value="2" <?php echo $status=='2'?'selected':''; ?>>في انتظار الرد</option>
                                        <option value="3" <?php echo $status=='3'?'selected':''; ?>>مغلق</option>
                                        <option value="4" <?php echo $status=='4'?'selected':''; ?>>ملغي</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">بحث</button>
                                <a href="whatsapp_rfq.php" class="btn btn-default">إعادة تعيين</a>
                            </form>
                        </div>
                        
                        <!-- نموذج الإجراءات الجماعية -->
                        <form method="POST" id="bulkForm">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="30"><input type="checkbox" id="checkAll"></th>
                                            <th>RFQ ID</th>
                                            <th>التاريخ</th>
                                            <th>الصورة</th>
                                            <th>عنوان المنتج</th>
                                            <th style="width: 5%;">الوصف</th>
                                            <th>المورد</th>
                                            <th>المشتري</th>
                                            <th>الكمية</th>
                                            <th>الدولة / المحافظة</th>
                                            <th>نوع العضوية</th>
                                            <th>الحالة</th>
                                            <th width="180">إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($results && mysqli_num_rows($results) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($results)): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="br_ids[]" value="<?php echo $row['br_id']; ?>">
                                            </td>
                                            <td>
                                                <a href="buyreq-view.php?view=<?php echo $row['br_id']; ?>">
                                                    <strong>#<?php echo $row['br_id']; ?></strong>
                                                </a>
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($row['br_posting_date'])); ?></td>
                                            <td>
                                                <?php if(!empty($row['br_pic'])): ?>
                                                <img src="../<?php echo $row['br_pic']; ?>" alt="Product" style="width:50px; height:50px; object-fit:cover; border-radius:4px;">
                                                <?php else: ?>
                                                <span class="text-muted">لا توجد</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['br_pd_name'] ?? ''); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars(substr($row['br_requirement'] ?? '', 0, 50)) . '...'; ?></td>
                                            <td>
                                                <?php 
                                                $supplier_sql = "SELECT u.fname, u.lname, u.mobile1 
                                                                FROM products p 
                                                                LEFT JOIN user u ON u.usr_id = p.pd_uid 
                                                                WHERE p.pd_id = " . (int)($row['br_pc_id'] ?? 0);
                                                $supplier_result = mysqli_query($con, $supplier_sql);
                                                if($supplier_row = mysqli_fetch_assoc($supplier_result)) {
                                                    echo htmlspecialchars(($supplier_row['fname'] ?? '') . ' ' . ($supplier_row['lname'] ?? ''));
                                                    echo '<br><small class="text-muted">' . htmlspecialchars($supplier_row['mobile1'] ?? '') . '</small>';
                                                } else {
                                                    echo '<span class="text-muted">غير محدد</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                echo htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
                                                if(!empty($row['mobile1'])) {
                                                    echo '<br><small class="text-muted">📱 ' . htmlspecialchars($row['mobile1']) . '</small>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo $row['br_estimate_qty'] ?? 0; ?>
                                                <?php 
                                                $unit_sql = "SELECT mu_name FROM measurement_unit WHERE mu_id = " . (int)($row['br_estimate_qty_unit'] ?? 0);
                                                $unit_result = mysqli_query($con, $unit_sql);
                                                if($unit_row = mysqli_fetch_assoc($unit_result)) {
                                                    echo ' ' . htmlspecialchars($unit_row['mu_name']);
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($row['cn_name'] ?? $row['country'] ?? 'غير محدد'); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $mp_sql = "SELECT mp_name FROM smembership_icon_plan WHERE mp_id = (SELECT usr_mp_id FROM user WHERE usr_id = " . (int)($row['br_u_id'] ?? 0) . ")";
                                                $mp_result = mysqli_query($con, $mp_sql);
                                                if($mp_row = mysqli_fetch_assoc($mp_result)) {
                                                    echo htmlspecialchars($mp_row['mp_name']);
                                                } else {
                                                    echo '<span class="text-muted">مجاني</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="label label-<?php echo $status_class[$row['br_status']] ?? 'default'; ?>">
                                                    <?php echo $status_display[$row['br_status']] ?? $row['br_status']; ?>
                                                </span>
                                                <?php if($row['wa_supplier_read'] == 1): ?>
                                                <span class="label label-success">✅ مقروء</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical" style="width:100%;">
                                                    <div class="btn-group" style="width:100%;">
                                                        <a href="?action=send_whatsapp&id=<?php echo $row['br_id']; ?>" 
                                                           class="btn btn-xs btn-success" style="width:48%;" 
                                                           onclick="return confirm('هل تريد إرسال إشعار واتساب للمورد؟')">
                                                            <i class="fa fa-whatsapp"></i> إرسال
                                                        </a>
                                                        <a href="?action=publish&id=<?php echo $row['br_id']; ?>" 
                                                           class="btn btn-xs btn-info" style="width:48%;"
                                                           onclick="return confirm('هل تريد نشر هذا الطلب على المنصة؟')">
                                                            <i class="fa fa-globe"></i> نشر
                                                        </a>
                                                    </div>
                                                    <div class="btn-group" style="width:100%; margin-top:3px;">
                                                        <a href="buyreq-view.php?view=<?php echo $row['br_id']; ?>" 
                                                           class="btn btn-xs btn-primary" style="width:48%;">
                                                            <i class="fa fa-eye"></i> عرض
                                                        </a>
                                                        <a href="?action=delete&id=<?php echo $row['br_id']; ?>" 
                                                           class="btn btn-xs btn-danger" style="width:48%;"
                                                           onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                                                            <i class="fa fa-trash"></i> حذف
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="13" style="text-align:center; padding:40px; color:#999;">
                                                <i class="fa fa-whatsapp" style="font-size:48px; display:block; margin-bottom:10px; color:#25D366;"></i>
                                                لا توجد طلبات واتساب
                                                <br><small class="text-muted">الطلبات القادمة من رقم واتساب المنصة ستظهر هنا</small>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- أدوات الإجراءات الجماعية -->
                            <div class="well well-sm" style="margin-top:10px;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select name="action" class="form-control" style="width:auto; display:inline-block;">
                                            <option value="">اختر إجراء</option>
                                            <option value="delete">🗑️ حذف المحدد</option>
                                            <option value="publish">🌐 نشر المحدد</option>
                                            <option value="send_whatsapp">📱 إرسال واتساب للمحدد</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary">تنفيذ</button>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <span class="text-muted">
                                            إجمالي الطلبات: <strong><?php echo $total; ?></strong>
                                            <span class="badge badge-important"><?php echo $stats['pending_count'] ?? 0; ?> قيد الانتظار</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                        <div class="pagination" style="text-align:center; margin-top:20px;">
                            <ul class="pagination">
                                <?php if($page > 1): ?>
                                <li><a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">&laquo;</a></li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="<?php echo $i == $page ? 'active' : ''; ?>">
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                <li><a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">&raquo;</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // ✅ تحديد/إلغاء تحديد جميع الصفوف
    document.getElementById('checkAll').addEventListener('change', function() {
        var checkboxes = document.getElementsByName('br_ids[]');
        for(var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
        }
    });
</script>

<?php include "includes/admin-footer.php"; ?>