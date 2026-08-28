<?php
session_start();
require_once "../lib/connect.php";

// ✅ اختبار الاتصال بقاعدة البيانات
$test_sql = "SELECT COUNT(*) as total FROM offers";
$test_res = mysqli_query($con, $test_sql);
$test_row = mysqli_fetch_assoc($test_res);
echo "<!-- عدد العروض في قاعدة البيانات: " . $test_row['total'] . " -->";



if (empty($_SESSION['ad_id_indm'])) {
    echo "غير مصرح بالدخول";
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

$where = "";
if ($status_filter != 'all') {
    $where = "WHERE o.status = '$status_filter'";
}
if (!empty($search)) {
    $where .= (empty($where) ? "WHERE" : " AND") . " (br.br_id LIKE '%$search%' OR br.br_pd_name LIKE '%$search%')";
}

$sql = "SELECT o.*, 
               br.br_id as rfq_id,
               br.br_pd_name as product_name,
               br.br_estimate_qty,
               br.br_estimate_qty_unit,
               br.br_requirement,
               br.br_posting_date,
               p.pd_image,
               u.fname as buyer_fname, u.lname as buyer_lname, u.mobile1 as buyer_phone,
               c.cn_name as country_name
        FROM offers o
        LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON o.buyer_id = u.usr_id
        LEFT JOIN country c ON u.country = c.cn_id
        WHERE 1=1 $where
        ORDER BY o.created_at DESC";
        
        
  
 $result = mysqli_query($con, $sql);
$total = mysqli_num_rows($result);

?>

<?php
// إحصائيات العروض
$stats_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'notified' THEN 1 ELSE 0 END) as notified,
                    SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
              FROM offers";
$stats_res = mysqli_query($con, $stats_sql);
$stats = mysqli_fetch_assoc($stats_res);
?>

<div class="row" style="margin-bottom:20px;">
    <div class="col-md-2">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?php echo $stats['total']; ?></h3>
                <p>إجمالي العروض</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?php echo $stats['pending']; ?></h3>
                <p>قيد الانتظار</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-blue">
            <div class="inner">
                <h3><?php echo $stats['notified']; ?></h3>
                <p>تم الإشعار</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?php echo $stats['accepted']; ?></h3>
                <p>تم القبول</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?php echo $stats['rejected']; ?></h3>
                <p>مرفوض</p>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><?php echo round(($stats['accepted'] / max($stats['total'], 1)) * 100); ?>%</h3>
                <p>نسبة القبول</p>
            </div>
        </div>
    </div>
</div>



<!DOCTYPE html>
<html>
<head>
    <title>عروض الأسعار</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; padding: 20px; direction: rtl; }
        .table th, .table td { vertical-align: middle !important; text-align: center; }
        .label { padding: 3px 6px; border-radius: 4px; font-size: 11px; }
        .label-warning { background: #f0ad4e; color: #fff; }
        .label-info { background: #5bc0de; color: #fff; }
        .label-success { background: #5cb85c; color: #fff; }
        .label-default { background: #777; color: #fff; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-tag"></i> عروض الأسعار</h3>
        </div>
        <div class="panel-body">
            <!-- فلتر الحالة -->
            <div class="row" style="margin-bottom:15px;">
                <div class="col-md-6">
                    <div class="btn-group">
                        <a href="?status=all" class="btn btn-sm <?php echo $status_filter == 'all' ? 'btn-primary' : 'btn-default'; ?>">الكل</a>
                        <a href="?status=pending" class="btn btn-sm <?php echo $status_filter == 'pending' ? 'btn-primary' : 'btn-default'; ?>">قيد الانتظار</a>
                        <a href="?status=notified" class="btn btn-sm <?php echo $status_filter == 'notified' ? 'btn-primary' : 'btn-default'; ?>">تم الإشعار</a>
                        <a href="?status=accepted" class="btn btn-sm <?php echo $status_filter == 'accepted' ? 'btn-primary' : 'btn-default'; ?>">تم القبول</a>
                    </div>
                </div>
                <div class="col-md-6 text-left">
                    <form method="GET" class="form-inline">
                        <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="بحث: RFQ ID, منتج" value="<?php echo htmlspecialchars($search); ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="submit"><i class="fa fa-search"></i> بحث</button>
                            </span>
                        </div>
                        <a href="offers.php?status=<?php echo $status_filter; ?>" class="btn btn-default btn-sm">إعادة تعيين</a>
                    </form>
                </div>
            </div>
            
            <?php if ($total > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" id="checkAll"></th>
                            <th>RFQ ID</th>
                            <th>التاريخ</th>
                            <th width="50">الصورة</th>
                            <th>عنوان المنتج</th>
                            <th width="150">الوصف</th>
                            <th>المورد</th>
                            <th>المشتري</th>
                            <th>الكمية</th>
                            <th>الدولة</th>
                            <th>نوع العضوية</th>
                            <th>الحالة</th>
                            <th width="150">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $img = !empty($row['pd_image']) ? explode(',', $row['pd_image'])[0] : 'noimage.jpg';
                            $supplier_name = $row['supplier_company'] ?? ($row['supplier_fname'] . ' ' . $row['supplier_lname']);
                            $buyer_name = $row['buyer_fname'] . ' ' . $row['buyer_lname'];
                            $status_badge = '';
                            if ($row['status'] == 'pending') $status_badge = '<span class="label label-warning">قيد الانتظار</span>';
                            elseif ($row['status'] == 'notified') $status_badge = '<span class="label label-info">تم الإشعار</span>';
                            elseif ($row['status'] == 'accepted') $status_badge = '<span class="label label-success">تم القبول</span>';
                            else $status_badge = '<span class="label label-default">' . $row['status'] . '</span>';
                        ?>
                        <tr>
                            <td><input type="checkbox" name="offer_ids[]" value="<?php echo $row['id']; ?>"></td>
                            <td><?php echo $row['rfq_id']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($row['br_posting_date'])); ?></td>
                            <td><img src="../upload/myproduct/<?php echo $img; ?>" width="50" height="50" style="object-fit:cover;"></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars(mb_substr($row['br_requirement'], 0, 50)); ?>...<\/td>
                            <td><?php echo htmlspecialchars($supplier_name); ?></td>
                            <td><?php echo htmlspecialchars($buyer_name); ?></td>
                            <td><?php echo $row['br_estimate_qty'] . ' ' . $row['br_estimate_qty_unit']; ?></td>
                            <td><?php echo htmlspecialchars($row['country_name'] ?? 'غير محدد'); ?></td>
                            <td><span class="label label-default">عضوية عادية</span></td>
                            <td><?php echo $status_badge; ?></td>
                            <td>
                                <div class="btn-group btn-group-xs">
                                    <button class="btn btn-info view-offer" 
                                            data-price="<?php echo $row['price']; ?>" 
                                            data-currency="<?php echo $row['currency']; ?>" 
                                            data-delivery="<?php echo $row['delivery_days']; ?>" 
                                            data-notes="<?php echo htmlspecialchars($row['notes']); ?>">
                                        <i class="fa fa-dollar"></i> السعر
                                    </button>
                                    <?php if ($row['status'] == 'pending'): ?>
                                    <button class="btn btn-success send-notification" 
                                            data-offer-id="<?php echo $row['id']; ?>"
                                            data-buyer-phone="<?php echo (string)$row['buyer_phone']; ?>"
                                            data-buyer-name="<?php echo htmlspecialchars($buyer_name); ?>"
                                            data-supplier-name="<?php echo htmlspecialchars($supplier_name); ?>"
                                            data-price="<?php echo $row['price']; ?>"
                                            data-currency="<?php echo $row['currency']; ?>"
                                            data-delivery="<?php echo $row['delivery_days']; ?>"
                                            data-rfq-id="<?php echo $row['rfq_id']; ?>">
                                        <i class="fa fa-whatsapp"></i> إرسال
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-default btn-sm" disabled>تم</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info text-center">لا توجد عروض أسعار</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal عرض تفاصيل السعر -->
<div class="modal fade" id="priceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-dollar"></i> تفاصيل عرض السعر</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th width="40%">السعر</th><td id="modal_price">-</td></tr>
                    <tr><th>مدة التوصيل</th><td id="modal_delivery">-</td></tr>
                    <tr><th>ملاحظات المورد</th><td id="modal_notes">-</td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal إشعار واتساب -->
<div id="waModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; width:450px; max-width:90%; margin:100px auto; padding:25px; border-radius:12px; direction:rtl;">
        <span onclick="closeWaModal()" style="float:left; cursor:pointer; font-size:24px;">&times;</span>
        <h3 style="color:#25D366;"><i class="fa fa-whatsapp"></i> إرسال إشعار للمشتري</h3>
        <textarea id="waMessageText" rows="6" style="width:100%; padding:8px; margin:10px 0; border:1px solid #ddd; border-radius:6px;" readonly></textarea>
        <div style="margin:10px 0;">
            <button onclick="copyWaMessage()" class="btn btn-primary btn-sm"><i class="fa fa-copy"></i> نسخ النص</button>
            <a id="waLinkBtn" href="#" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-whatsapp"></i> فتح واتساب</a>
        </div>
        <div class="alert alert-warning" style="margin-top:10px; padding:8px;">
            <small>⚠️ ملاحظة: بعد فتح واتساب، قم بلصق الرسالة (Ctrl+V) ثم أرسلها.</small>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// استخدام onclick مباشرة بدلاً من jQuery
$('.send-notification').click(function(e) {
    e.preventDefault();
    
    let offerId = $(this).data('offer-id');
    let buyerPhone = String($(this).data('buyer-phone') || '');
    let buyerName = $(this).data('buyer-name') || '';
    let supplierName = $(this).data('supplier-name') || '';
    let price = $(this).data('price') || 0;
    let currency = $(this).data('currency') || 'EGP';
    let delivery = $(this).data('delivery') || 0;
    let rfqId = $(this).data('rfq-id') || 0;
    
    let cleanPhone = buyerPhone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) {
        cleanPhone = '20' + cleanPhone.substring(1);
    }
    
    let message = `📦 *عرض سعر جديد لطلبك RFQ #${rfqId}*\n\n`;
    message += `*المورد:* ${supplierName}\n`;
    message += `*السعر المقترح:* ${price} ${currency}\n`;
    message += `*مدة التوصيل:* ${delivery} يوم\n\n`;
    message += `للاطلاع على التفاصيل والرد على المورد:\n`;
    message += `https://egyptmart.shop/my-enquiries.php?rfq_id=${rfqId}\n\n`;
    message += `يمكنك التواصل مع المورد مباشرة عبر الرد على هذه الرسالة.`;
    
    $('#waMessageText').val(message);
    $('#waLinkBtn').attr('href', 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(message));
    $('#waModal').show();
    
    // تحديث حالة العرض في الخلفية (بدون إعادة تحميل الصفحة)
    fetch('/admin/ajax-file/send_offer_notification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId
    }).then(response => response.json()).then(data => {
        if (data.success) {
            // تغيير الزر إلى "تم" بدون إعادة تحميل
            $('.send-notification[data-offer-id="' + offerId + '"]')
                .html('<i class="fa fa-check"></i> تم')
                .removeClass('btn-success')
                .addClass('btn-default')
                .prop('disabled', true);
        }
    }).catch(error => console.error('Error:', error));
});

function copyWaMessage() {
    let textarea = document.getElementById('waMessageText');
    textarea.select();
    document.execCommand('copy');
    alert('✓ تم نسخ الرسالة');
}

function closeWaModal() {
    document.getElementById('waModal').style.display = 'none';
}
</script>
</body>
</html>