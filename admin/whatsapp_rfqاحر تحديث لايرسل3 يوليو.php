<?php
// ✅ الحفاظ على التعريف القديم
define('ACCESS_ALLOWED', true);

// ✅ بدء الجلسة إذا لم تبدأ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ استقبال الطلبات من saleoffer_whatsapp_handler.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['source']) && $_POST['source'] == 'saleoffer') {
    header('Content-Type: application/json');
    $br_id = isset($_POST['br_id']) ? (int)$_POST['br_id'] : 0;
    if ($br_id > 0) {
        $update_sql = "UPDATE buy_requirement SET 
            source_platform = 'saleoffer',
            wa_status = 'pending'
        WHERE br_id = $br_id";
        mysqli_query($con, $update_sql);
    }
    echo json_encode(['success' => true]);
    exit;
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

// تضمين دوال WhatsApp RFQ
include "includes/wa_functions_simple.php";

// Pagination variables
$limit = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// جلب البيانات
$total = get_whatsapp_rfq_count($search, $status);
$total_pages = ceil($total / $limit);
$results = get_whatsapp_rfq_list($start, $limit, $search, $status);

// ✅ معالجة البيانات
$results_display = array();

if ($results && mysqli_num_rows($results) > 0) {
    while ($row = mysqli_fetch_assoc($results)) {
        // ✅ قيم افتراضية
        $row['product_image'] = 'noimage.jpg';
        $row['supplier_name'] = 'غير محدد';
        $row['image_path'] = '../upload/myproduct/';
        
        // ✅ جلب الصورة واسم المورد حسب المصدر
        $product_id = (int)($row['br_pc_id'] ?? 0);
        $source = $row['source_platform'] ?? '';
        
       
       // ✅ ============================================================
// ✅ معالجة saleoffer (عروض البيع) - تصحيح اسم المورد
// ✅ ============================================================
if ($source == 'saleoffer' && $product_id > 0) {
    // ✅ جلب الصورة من sale_offer
    $offer_sql = "SELECT so_pic, so_usr_id FROM sale_offer WHERE so_id = $product_id LIMIT 1";
    $offer_res = mysqli_query($con, $offer_sql);
    
    if ($offer_res && mysqli_num_rows($offer_res) > 0) {
        $offer_data = mysqli_fetch_assoc($offer_res);
        if (!empty($offer_data['so_pic'])) {
            $row['product_image'] = $offer_data['so_pic'];
            $row['image_path'] = '../upload/sale_offer/';
        }
        
        // ✅ جلب اسم المورد من so_usr_id (وليس من br_u_id)
        $supplier_id = (int)($offer_data['so_usr_id'] ?? 0);
        if ($supplier_id > 0) {
            $user_sql = "SELECT fname, lname, bp.bnsprof_compname 
                         FROM user u 
                         LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                         WHERE u.usr_id = $supplier_id LIMIT 1";
            $user_res = mysqli_query($con, $user_sql);
            if ($user_res && mysqli_num_rows($user_res) > 0) {
                $user_data = mysqli_fetch_assoc($user_res);
                // ✅ استخدام اسم الشركة إذا وجد، وإلا استخدام الاسم الشخصي
                if (!empty($user_data['bnsprof_compname'])) {
                    $row['supplier_name'] = $user_data['bnsprof_compname'];
                } else {
                    $row['supplier_name'] = trim(($user_data['fname'] ?? '') . ' ' . ($user_data['lname'] ?? ''));
                }
                if (empty($row['supplier_name'])) {
                    $row['supplier_name'] = 'غير محدد';
                }
            }
        }
    }
}
        // ✅ ============================================================
        // ✅ معالجة الطلبات العادية (من products)
        // ✅ ============================================================
        else if ($product_id > 0) {
            $prod_sql = "SELECT p.pd_image, bp.bnsprof_compname 
                         FROM products p 
                         LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid 
                         WHERE p.pd_id = $product_id LIMIT 1";
            $prod_res = mysqli_query($con, $prod_sql);
            if ($prod_res && mysqli_num_rows($prod_res) > 0) {
                $prod_data = mysqli_fetch_assoc($prod_res);
                if (!empty($prod_data['pd_image'])) {
                    $row['product_image'] = explode(',', $prod_data['pd_image'])[0];
                }
                $row['supplier_name'] = $prod_data['bnsprof_compname'] ?? 'غير محدد';
            }
        }
        
        $results_display[] = $row;
    }
}

// ✅ معالجة الإجراءات
if (isset($_POST['action']) && isset($_POST['br_ids'])) {
    $br_ids = $_POST['br_ids'];
    if ($_POST['action'] == 'delete') {
        foreach ($br_ids as $id) delete_whatsapp_rfq($id);
        echo '<div class="alert alert-success">تم حذف الطلبات المحددة</div>';
    }
    if ($_POST['action'] == 'publish') {
        foreach ($br_ids as $id) publish_to_public_rfq($id);
        echo '<div class="alert alert-success">تم نشر الطلبات المحددة</div>';
    }
}
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

                <div class="row">
                    <div class="col-xs-12">
                        <!-- شريط البحث والفلتر -->
                        <div class="well well-sm">
                            <form method="GET" class="form-inline">
                                <div class="form-group">
                                    <input type="text" name="search" class="form-control" placeholder="بحث: RFQ ID, منتج, تفاصيل" value="<?php echo htmlspecialchars($search); ?>" style="width:300px;">
                                </div>
                                <div class="form-group">
                                    <select name="status" class="form-control">
                                        <option value="all">جميع الحالات</option>
                                        <option value="pending" <?php echo $status=='pending'?'selected':''; ?>>قيد الانتظار</option>
                                        <option value="sent_to_supplier" <?php echo $status=='sent_to_supplier'?'selected':''; ?>>تم الإرسال للمورد</option>
                                        <option value="waiting_response" <?php echo $status=='waiting_response'?'selected':''; ?>>في انتظار الرد</option>
                                        <option value="closed" <?php echo $status=='closed'?'selected':''; ?>>مغلق</option>
                                        <option value="cancelled" <?php echo $status=='cancelled'?'selected':''; ?>>ملغي</option>
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
                                            <th width="150">إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
    <?php if(count($results_display) > 0): ?>
    <?php foreach($results_display as $row): ?>
    <tr>
        <td><input type="checkbox" name="br_ids[]" value="<?php echo $row['br_id']; ?>"></td>
        <td><?php echo $row['br_id']; ?></td>
        <td><?php echo date('Y-m-d', strtotime($row['br_posting_date'])); ?></td>
        
        <!-- ✅ عمود الصورة -->
        <td class="center">
            <?php 
            $img_path = $row['image_path'] ?? '../upload/myproduct/';
            $img_name = $row['product_image'] ?? 'noimage.jpg';
            echo '<img src="' . $img_path . $img_name . '" width="50" height="50" style="object-fit:cover; border:1px solid #ddd; border-radius:4px;" onerror="this.src=\'../upload/myproduct/noimage.jpg\'">';
            ?>
        </td>
        
        <td><?php echo htmlspecialchars($row['pd_title'] ?? $row['br_pd_name']); ?></td>
        <td><?php echo mb_substr(htmlspecialchars($row['br_requirement']), 0, 30) . '...'; ?></td>
        
        <!-- ✅ عمود المورد -->
        <td class="center">
            <?php echo htmlspecialchars($row['supplier_name'] ?? 'غير محدد'); ?>
        </td>
        
        <td><?php echo htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')); ?></td>
        <td><?php echo $row['br_estimate_qty'] . ' ' . $row['br_estimate_qty_unit']; ?></td>
        
        <td class="center">
            <?php 
            $country = $row['country_name'] ?? 'غير محدد';
            $city = $row['city_name'] ?? 'غير محدد';
            echo htmlspecialchars($country . ' - ' . $city); 
            ?>
        </td>
        
        <td><span class="label label-default">عضوية عادية</span></td>
        
        <td>
            <select class="form-control status-select" data-id="<?php echo $row['br_id']; ?>" style="width:130px;">
                <option value="pending" <?php echo $row['wa_status']=='pending'?'selected':''; ?>>قيد الانتظار</option>
                <option value="sent_to_supplier" <?php echo $row['wa_status']=='sent_to_supplier'?'selected':''; ?>>تم الإرسال</option>
                <option value="waiting_response" <?php echo $row['wa_status']=='waiting_response'?'selected':''; ?>>انتظار رد</option>
                <option value="closed" <?php echo $row['wa_status']=='closed'?'selected':''; ?>>مغلق</option>
                <option value="cancelled" <?php echo $row['wa_status']=='cancelled'?'selected':''; ?>>ملغي</option>
            </select>
        </td>
        
        <td>
            <div>
                <button class="btn btn-info btn-sm view-rfq" data-id="<?php echo $row['br_id']; ?>" title="عرض"><i class="fa fa-eye"></i></button>
                <?php
                $check_accepted = mysqli_query($con, "SELECT status FROM offers WHERE rfq_id = {$row['br_id']} AND status = 'accepted' LIMIT 1");
                $is_accepted = (mysqli_num_rows($check_accepted) > 0);
                ?>
                <?php if (!$is_accepted): ?>
                    <button class="btn btn-primary btn-sm send-supplier" data-id="<?php echo $row['br_id']; ?>" title="إرسال للمورد"><i class="fa fa-paper-plane"></i></button>
                    <!-- ✅ زر إرسال واتساب - مع دعم saleoffer -->
<button class="btn btn-success btn-sm" 
    onclick="sendAndWhatsApp(<?php echo $row['br_id']; ?>, '<?php echo $row['source_platform'] ?? ''; ?>')" 
    title="إرسال واتساب">
    <i class="fa fa-whatsapp"></i>
</button>
                
                <?php else: ?>
                    <button class="btn btn-default btn-sm" disabled><i class="fa fa-check"></i> تم القبول</button>
                <?php endif; ?>
                <button class="btn btn-warning btn-sm similar-suppliers" data-id="<?php echo $row['br_id']; ?>" title="موردين مشابهين"><i class="fa fa-users"></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteRfq(<?php echo $row['br_id']; ?>)" title="حذف"><i class="fa fa-trash"></i></button>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php else: ?>
    <tr>
        <td colspan="13" class="text-center">لا توجد طلبات WhatsApp RFQ</td>
    </tr>
    <?php endif; ?>
</tbody>
</table>
</div>

<div class="row">
    <div class="col-md-6">
        <select name="action" class="form-control" style="width:200px; display:inline-block;">
            <option value="">-- إجراءات جماعية --</option>
            <option value="delete">حذف المحدد</option>
            <option value="publish">نشر في الصفحة العامة</option>
        </select>
        <button type="submit" class="btn btn-primary">تنفيذ</button>
    </div>
    <div class="col-md-6 text-right">
        إجمالي الطلبات: <strong><?php echo $total; ?></strong>
    </div>
</div>
</form>

<!-- Pagination -->
<div class="text-center" style="margin-top:20px;">
    <ul class="pagination" style="display:inline-flex; flex-wrap:wrap; gap:5px; justify-content:center; list-style:none; padding:0;">
        <?php if($page > 1): ?>
        <li style="display:inline-block;"><a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="btn btn-default btn-sm">« السابق</a></li>
        <?php endif; ?>
        
        <?php 
        $start_page = max(1, $page - 3);
        $end_page = min($total_pages, $page + 3);
        
        if($start_page > 1): ?>
        <li style="display:inline-block;"><a href="?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="btn btn-default btn-sm">1</a></li>
        <?php if($start_page > 2): ?>
        <li style="display:inline-block;"><span class="btn btn-default btn-sm disabled">…</span></li>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php for($i=$start_page; $i<=$end_page; $i++): ?>
        <li style="display:inline-block;">
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" 
               class="btn <?php echo $i==$page?'btn-primary':'btn-default'; ?> btn-sm">
                <?php echo $i; ?>
            </a>
        </li>
        <?php endfor; ?>
        
        <?php if($end_page < $total_pages): ?>
        <?php if($end_page < $total_pages - 1): ?>
        <li style="display:inline-block;"><span class="btn btn-default btn-sm disabled">…</span></li>
        <?php endif; ?>
        <li style="display:inline-block;"><a href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="btn btn-default btn-sm"><?php echo $total_pages; ?></a></li>
        <?php endif; ?>
        
        <?php if($page < $total_pages): ?>
        <li style="display:inline-block;"><a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" class="btn btn-default btn-sm">التالي »</a></li>
        <?php endif; ?>
    </ul>
    
    <div class="form-inline" style="margin-top:10px;">
        <label>انتقل إلى صفحة:</label>
        <input type="number" id="goToPage" min="1" max="<?php echo $total_pages; ?>" class="form-control" style="width:70px; display:inline-block;">
        <button class="btn btn-default" onclick="window.location='?page='+document.getElementById('goToPage').value+'&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>'">Go</button>
    </div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- Modal عرض التفاصيل -->
<div class="modal fade" id="rfqModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">تفاصيل طلب RFQ <span id="modalRfqId"></span></h4>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal الموردين المشابهين -->
<div class="modal fade" id="similarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">إرسال إلى موردين مشابهين</h4>
            </div>
            <div class="modal-body" id="similarBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" id="sendToSimilarBtn">إرسال للمحددين</button>
            </div>
        </div>
    </div>
</div>

<script>
function sendAndWhatsApp(rfqId, sourcePlatform) {
    if (!confirm('هل أنت متأكد من إرسال هذا الطلب للمورد وفتح واتساب؟')) return;

    // ✅ تحديد المسار حسب المصدر
    let url = '/admin/send_to_supplier_handler.php';
    if (sourcePlatform === 'saleoffer') {
        url = '/admin/send_saleoffer_wa_handler.php';
    }

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ تم إرسال الطلب للمورد');
            if (data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank');
            }
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ خطأ في الاتصال');
    });
}

function deleteRfq(rfqId) {
    if (!confirm('هل أنت متأكد من حذف هذا الطلب؟')) return;
    
    fetch('/admin/delete_rfq_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'rfq_id=' + rfqId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ تم حذف الطلب');
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ خطأ في الاتصال');
    });
}

$(document).ready(function() {
    $('.send-supplier').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (!id) {
            alert('خطأ: لا يوجد معرف للمنتج');
            return;
        }
        if (!confirm('سيتم فتح واتساب للمورد مع رسالة جاهزة. هل تريد المتابعة؟')) return;
        var btn = $(this);
        var originalHtml = btn.html();
        btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
        fetch('/admin/send_wa_final_working.php?rfq_id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.whatsapp_url) {
                    window.open(data.whatsapp_url, '_blank');
                    alert('✓ تم فتح واتساب. قم بإرسال الرسالة.');
                } else {
                    alert('❌ حدث خطأ: ' + (data.error || 'لم يتم إنشاء الرابط'));
                }
                btn.html(originalHtml).prop('disabled', false);
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('❌ حدث خطأ في الاتصال بالخادم');
                btn.html(originalHtml).prop('disabled', false);
            });
    });
});
</script>

<?php include "includes/footer.php"; ?>