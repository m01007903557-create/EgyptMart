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

// تضمين دوال WhatsApp RFQ
include "includes/whatsapp_rfq_functions.php";

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

// معالجة الإجراءات
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
        <th>الوصف</th>
        <th>المشتري</th>
        <th>الكمية</th>
        <th>الدولة / المحافظة</th>
        <th>نوع العضوية</th>
        <th>الحالة</th>
        <th width="150">إجراءات</th>
    </tr>
</thead>
                                    <tbody>
    <?php if(mysqli_num_rows($results) > 0): ?>
    <?php while($row = mysqli_fetch_assoc($results)): ?>
    <tr>
        <td><input type="checkbox" name="br_ids[]" value="<?php echo $row['br_id']; ?>"></td>
        <td><?php echo $row['br_id']; ?></td>
        <td><?php echo date('Y-m-d', strtotime($row['br_posting_date'])); ?></td>
        <td>
            <?php 
            $img = !empty($row['pd_image']) ? explode(',', $row['pd_image'])[0] : 'noimage.jpg';
            echo '<img src="../upload/myproduct/' . $img . '" width="50" height="50" style="object-fit:cover;">';
            ?>
        </td>
        <td><?php echo htmlspecialchars($row['pd_title'] ?? $row['br_pd_name']); ?></td>
        <td><?php echo mb_substr(htmlspecialchars($row['br_requirement']), 0, 50); ?>...</td>
        <td><?php echo htmlspecialchars(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '')); ?></td>
        <td><?php echo $row['br_estimate_qty'] . ' ' . $row['br_estimate_qty_unit']; ?></td>
        <td>
            <?php 
            $country = $row['country_name'] ?? 'غير محدد';
            $city = $row['supplier_city_name'] ?? 'غير محدد';
            echo htmlspecialchars($country . ' - ' . $city); 
            ?>
        </td>
        <td>
            <?php
            // نوع العضوية
            echo '<span class="label label-default">عضوية عادية</span>';
            ?>
        </td>
        <td>
            <select class="form-control status-select" data-id="<?php echo $row['br_id']; ?>" style="width:130px;">
                <option value="pending" <?php echo $row['wa_status']=='pending'?'selected':''; ?>>قيد الانتظار</option>
                <option value="sent_to_supplier" <?php echo $row['wa_status']=='sent_to_supplier'?'selected':''; ?>>تم الإرسال</option>
                <option value="waiting_response" <?php echo $row['wa_status']=='waiting_response'?'selected':''; ?>>انتظار رد</option>
                <option value="closed" <?php echo $row['wa_status']=='closed'?'selected':''; ?>>مغلق</option>
                <option value="cancelled" <?php echo $row['wa_status']=='cancelled'?'selected':''; ?>>ملغي</option>
            </select>
            <?php if(($row['wa_sent_count'] ?? 0) > 0): ?>
            <small class="text-muted">أرسل <?php echo $row['wa_sent_count']; ?> مرة</small>
            <?php endif; ?>
        </td>
        <td>
            
            <div>
    <button class="btn btn-info btn-sm view-rfq" data-id="<?php echo $row['br_id']; ?>" title="عرض"><i class="fa fa-eye"></i></button>
    <button class="btn btn-success btn-sm send-supplier" data-id="<?php echo $row['br_id']; ?>" title="إرسال للمورد"><i class="fa fa-paper-plane"></i></button>
    <button class="btn btn-warning btn-sm similar-suppliers" data-id="<?php echo $row['br_id']; ?>" title="موردين مشابهين"><i class="fa fa-users"></i></button>
    <button class="btn btn-danger btn-sm" onclick="deleteRfq(<?php echo $row['br_id']; ?>)" title="حذف"><i class="fa fa-trash"></i></button>
</div>
            
            
          
        </td>
    </tr>
    <?php endwhile; ?>
    <?php else: ?>
    <tr>
        <td colspan="12" class="text-center">لا توجد طلبات WhatsApp RFQ</td>
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
                        <div class="text-center">
                            <ul class="pagination">
                                <?php if($page > 1): ?>
                                <li><a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">&laquo; السابق</a></li>
                                <?php endif; ?>
                                <?php for($i=1; $i<=$total_pages; $i++): ?>
                                <li class="<?php echo $i==$page?'active':''; ?>"><a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                <?php if($page < $total_pages): ?>
                                <li><a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>">التالي &raquo;</a></li>
                                <?php endif; ?>
                            </ul>
                            <div class="form-inline">
                                <label>انتقل إلى صفحة:</label>
                                <input type="number" id="goToPage" min="1" max="<?php echo $total_pages; ?>" class="form-control" style="width:70px;">
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
                <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                <button type="button" class="close" data-dismiss="modal">&times;</button>
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
$(document).ready(function() {
    $('#checkAll').click(function() {
        $('input[name="br_ids[]"]').prop('checked', this.checked);
    });
    
    $('.view-rfq').click(function() {
        var id = $(this).data('id');
        $.get('ajax-files/whatsapp_rfq_ajax.php', {action: 'get_details', id: id}, function(data) {
            var d = JSON.parse(data);
            $('#modalRfqId').text(d.br_id);
            var html = '<table class="table table-bordered">';
            html += '<tr><th width="30%">المنتج:</th><td>' + (d.pd_title || d.br_pd_name) + '</td></tr>';
            html += '<tr><th>المشتري:</th><td>' + (d.fname || '') + ' ' + (d.lname || '') + '</td></tr>';
            html += '<tr><th>الجوال:</th><td>' + (d.mobile1 || '') + '</td></tr>';
            html += '<tr><th>الإيميل:</th><td>' + (d.email || '') + '</td></tr>';
            html += '<tr><th>الكمية:</th><td>' + (d.br_estimate_qty || 0) + ' ' + (d.br_estimate_qty_unit || '') + '</td></tr>';
            html += '<tr><th>التفاصيل:</th><td>' + (d.br_requirement || '') + '</td></tr>';
            html += '<tr><th>ملاحظات الأدمن:</th><td><textarea id="adminNotes" class="form-control" rows="3">' + (d.wa_admin_notes || '') + '</textarea><br><button class="btn btn-sm btn-primary" onclick="saveNotes('+d.br_id+')">حفظ</button></td></tr>';
            html += '<tr><th>سجل الإرسال:</th><td><pre>' + (d.wa_send_log || 'لا يوجد سجل') + '</pre></td></tr>';
            html += '</table>';
            $('#modalBody').html(html);
            $('#rfqModal').modal('show');
        });
    });
    
    $('.status-select').change(function() {
        var id = $(this).data('id');
        var status = $(this).val();
        $.post('ajax-files/whatsapp_rfq_ajax.php', {action: 'update_status', id: id, status: status}, function(r) {
            if(r == 'ok') location.reload();
        });
    });
    
    $('.send-supplier').click(function() {
        var id = $(this).data('id');
        if(confirm('إرسال إشعار للمورد (إيميل + واتساب + لوحة التحكم)؟')) {
            $.post('ajax-files/whatsapp_rfq_ajax.php', {action: 'send_to_supplier', id: id}, function(r) {
                alert(r);
                location.reload();
            });
        }
    });
    
    $('.similar-suppliers').click(function() {
        var id = $(this).data('id');
        $.get('ajax-files/whatsapp_rfq_ajax.php', {action: 'get_similar', id: id}, function(data) {
            $('#similarBody').html(data);
            $('#similarModal').data('rfq_id', id);
            $('#similarModal').modal('show');
        });
    });
    
    $('#sendToSimilarBtn').click(function() {
        var ids = [];
        $('.sim-supp:checked').each(function() { ids.push($(this).val()); });
        var rfq_id = $('#similarModal').data('rfq_id');
        if(ids.length == 0) { alert('اختر موردين على الأقل'); return; }
        $.post('ajax-files/whatsapp_rfq_ajax.php', {action: 'send_to_similar', rfq_id: rfq_id, supplier_ids: ids}, function(r) {
            alert(r);
            $('#similarModal').modal('hide');
            location.reload();
        });
    });
});

function deleteRfq(id) {
    if(confirm('هل أنت متأكد من حذف هذا الطلب؟')) {
        $.post('ajax-files/whatsapp_rfq_ajax.php', {action: 'delete', id: id}, function(r) {
            location.reload();
        });
    }
}

function saveNotes(id) {
    var notes = $('#adminNotes').val();
    $.post('ajax-files/whatsapp_rfq_ajax.php', {action: 'save_notes', id: id, notes: notes}, function(r) {
        alert('تم حفظ الملاحظات');
        $('#rfqModal').modal('hide');
    });
}
</script>

<?php include "includes/footer.php"; ?>