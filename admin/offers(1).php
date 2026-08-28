<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once "../lib/connect.php";

if (empty($_SESSION['ad_id_indm'])) {
    header("Location: index.php");
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

// استعلام جلب العروض
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
              br.br_pc_id,
              p.pd_image,
              u.fname as buyer_fname, u.lname as buyer_lname, u.mobile1 as buyer_phone,
              c.cn_name as country_name,
              sup.fname as supplier_fname, sup.lname as supplier_lname,
              bp.bnsprof_compname as supplier_company,
              bp.bnsprof_city as supplier_city_id,
              bp.bnsprof_state as supplier_state_id
       FROM offers o
       LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
       LEFT JOIN products p ON br.br_pc_id = p.pd_id
       LEFT JOIN user u ON o.buyer_id = u.usr_id
       LEFT JOIN country c ON u.country = c.cn_id
       LEFT JOIN user sup ON o.supplier_id = sup.usr_id
       LEFT JOIN business_profile bp ON sup.usr_id = bp.bnsprof_uid
       $where
       ORDER BY o.created_at DESC";
       
$result = mysqli_query($con, $sql);
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>عروض الأسعار - لوحة التحكم</title>
    <?php include dirname(__DIR__) . '/admin/includes/header.php'; ?>
    <style>
        .table > thead > tr > th, .table > tbody > tr > td {
            vertical-align: middle !important;
            text-align: center;
        }
        .btn-group-xs > .btn {
            padding: 2px 5px;
            font-size: 11px;
        }
        .label {
            font-size: 11px;
            padding: 3px 6px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    
    <?php include dirname(__DIR__) . '/admin/includes/admin-top.php'; ?>
    <?php include dirname(__DIR__) . '/admin/includes/admin-left-con.php'; ?>
    
    

    <div class="content-wrapper">
        <section class="content-header">
            <h1><i class="fa fa-tag"></i> عروض الأسعار</h1>
        </section>
        
        <section class="content">
            <div class="box">
                <div class="box-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="btn-group">
                                <a href="?status=all" class="btn btn-sm <?php echo $status_filter == 'all' ? 'btn-primary' : 'btn-default'; ?>">الكل</a>
                                <a href="?status=pending" class="btn btn-sm <?php echo $status_filter == 'pending' ? 'btn-primary' : 'btn-default'; ?>">
                                    قيد الانتظار <span class="badge">جديد</span>
                                </a>
                                <a href="?status=notified" class="btn btn-sm <?php echo $status_filter == 'notified' ? 'btn-primary' : 'btn-default'; ?>">تم الإشعار</a>
                                <a href="?status=accepted" class="btn btn-sm <?php echo $status_filter == 'accepted' ? 'btn-primary' : 'btn-default'; ?>">تم القبول</a>
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <form method="GET" class="form-inline">
                                <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="بحث: RFQ ID, منتج" value="<?php echo htmlspecialchars($search); ?>">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                    </span>
                                </div>
                                <a href="offers.php?status=<?php echo $status_filter; ?>" class="btn btn-default btn-sm">إعادة تعيين</a>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="box-body table-responsive">
                    <?php if ($total > 0): ?>
                    <table class="table table-bordered table-striped table-hover">
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
                                <th>الدولة / المحافظة</th>
                                <th>نوع العضوية</th>
                                <th>الحالة</th>
                                <th width="120">إجراءات</th>
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
                                <td class="text-center"><input type="checkbox" name="offer_ids[]" value="<?php echo $row['id']; ?>"></td>
                                <td class="text-center"><?php echo $row['rfq_id']; ?></td>
                                <td class="text-center"><?php echo date('Y-m-d', strtotime($row['br_posting_date'])); ?></td>
                                <td class="text-center">
                                    <img src="../upload/myproduct/<?php echo $img; ?>" width="40" height="40" style="object-fit:cover;">
                                </td>
                                <td class="text-center"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td class="text-center"><?php echo mb_substr(htmlspecialchars($row['br_requirement']), 0, 50); ?>...</td>
                                <td class="text-center"><?php echo htmlspecialchars($supplier_name); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($buyer_name); ?></td>
                                <td class="text-center"><?php echo $row['br_estimate_qty'] . ' ' . $row['br_estimate_qty_unit']; ?></td>
                                <td class="text-center">
                                    <td class="text-center">
    <?php 
    $country = $row['country_name'] ?? 'غير محدد';
    $city_id = $row['supplier_city_id'] ?? 0;
    $state_id = $row['supplier_state_id'] ?? 0;
    $city_name = ($city_id > 0) ? get_city_name($city_id) : '';
    $state_name = ($state_id > 0) ? get_state_name($state_id) : '';
    echo htmlspecialchars($country . ' - ' . ($city_name ?: $state_name));
    ?>
</td>
                                <td class="text-center">
                                    <span class="label label-default">عضوية عادية</span>
                                </td>
                                <td class="text-center"><?php echo $status_badge; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-xs">
                                        <button class="btn btn-info view-offer" data-offer-id="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>" data-currency="<?php echo $row['currency']; ?>" data-delivery="<?php echo $row['delivery_days']; ?>" data-notes="<?php echo htmlspecialchars($row['notes']); ?>" title="عرض تفاصيل السعر">
                                            <i class="fa fa-dollar"></i> السعر
                                        </button>
                                        <?php if ($row['status'] == 'pending'): ?>
                                        <button class="btn btn-success send-notification" 
                                                data-offer-id="<?php echo $row['id']; ?>"
                                                data-buyer-phone="<?php echo $row['buyer_phone']; ?>"
                                                data-buyer-name="<?php echo htmlspecialchars($buyer_name); ?>"
                                                data-supplier-name="<?php echo htmlspecialchars($supplier_name); ?>"
                                                data-price="<?php echo $row['price']; ?>"
                                                data-currency="<?php echo $row['currency']; ?>"
                                                data-delivery="<?php echo $row['delivery_days']; ?>"
                                                data-rfq-id="<?php echo $row['rfq_id']; ?>"
                                                title="إرسال إشعار واتساب للمشتري">
                                            <i class="fa fa-whatsapp"></i> إرسال
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-default btn-sm" disabled title="تم الإشعار مسبقاً">
                                            <i class="fa fa-check"></i> تم
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                 </td>
                             </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info text-center">لا توجد عروض أسعار</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
    
    <?php include "includes/footer.php"; ?>
</div>

<!-- Modal عرض تفاصيل السعر -->
<div id="priceModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-dollar"></i> تفاصيل عرض السعر</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr><th width="40%">السعر</th><td id="modal_price"></td></tr>
                    <tr><th>مدة التوصيل</th><td id="modal_delivery"></td></tr>
                    <tr><th>ملاحظات المورد</th><td id="modal_notes"></td></tr>
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
        
        <label>نص الرسالة:</label>
        <textarea id="waMessageText" rows="6" style="width:100%; padding:8px; margin:10px 0; border:1px solid #ddd; border-radius:6px;" readonly></textarea>
        
        <div style="margin:10px 0;">
            <button onclick="copyWaMessage()" class="btn btn-primary btn-sm">
                <i class="fa fa-copy"></i> نسخ النص
            </button>
            <a id="waLinkBtn" href="#" target="_blank" class="btn btn-success btn-sm">
                <i class="fa fa-whatsapp"></i> فتح واتساب
            </a>
        </div>
        
        <div class="alert alert-warning" style="margin-top:10px; padding:8px;">
            <small>⚠️ ملاحظة: بعد فتح واتساب، قم بلصق الرسالة (Ctrl+V) ثم أرسلها.</small>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#checkAll').click(function() {
        $('input[name="offer_ids[]"]').prop('checked', this.checked);
    });
    
    $('.view-offer').click(function() {
        $('#modal_price').text($(this).data('price') + ' ' + $(this).data('currency'));
        $('#modal_delivery').text($(this).data('delivery') + ' يوم');
        $('#modal_notes').text($(this).data('notes') || 'لا توجد ملاحظات');
        $('#priceModal').modal('show');
    });
    
    $('.send-notification').click(function() {
        let offerId = $(this).data('offer-id');
        let buyerPhone = $(this).data('buyer-phone');
        let buyerName = $(this).data('buyer-name');
        let supplierName = $(this).data('supplier-name');
        let price = $(this).data('price');
        let currency = $(this).data('currency');
        let delivery = $(this).data('delivery');
        let rfqId = $(this).data('rfq-id');
        
        let cleanPhone = buyerPhone.replace(/\D/g, '');
        if (cleanPhone.startsWith('0')) {
            cleanPhone = '20' + cleanPhone.substring(1);
        }
        
        let message = `📦 *عرض سعر جديد لطلبك RFQ #${rfqId}*\n\n`;
        message += `*المورد:* ${supplierName}\n`;
        message += `*السعر المقترح:* ${price} ${currency}\n`;
        message += `*مدة التوصيل:* ${delivery} يوم\n\n`;
        message += `للاطلاع على التفاصيل والرد على المورد، يرجى تسجيل الدخول إلى حسابك:\n`;
        message += `https://egyptmart.shop/my-enquiries.php?rfq_id=${rfqId}\n\n`;
        message += `يمكنك التواصل مع المورد مباشرة عبر الرد على هذه الرسالة.`;
        
        $('#waMessageText').val(message);
        $('#waLinkBtn').attr('href', 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(message));
        $('#waModal').show();
        
        fetch('/admin/ajax-file/send_offer_notification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'offer_id=' + offerId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
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