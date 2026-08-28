// admin/index.php أو admin/dashboard.php
// أضف هذا الكود في المكان المناسب

require_once __DIR__ . '/../includes/rfq_functions.php';
$stats = getRFQStats();

// عرض إحصائيات سريعة
echo '<div class="dashboard-widget">';
echo '<h3>📊 طلبات الشراء</h3>';
foreach ($stats as $source => $data) {
    $total = array_sum(array_column($data, 'total'));
    echo "<p>$source: $total طلب</p>";
}
echo '</div>';

<!-- عروض الأسعار المعلقة -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-tag"></i> عروض أسعار معلقة</h3>
    </div>
    <div class="panel-body">
        <?php
        $offers_sql = "SELECT o.*, 
                              br.br_pd_name as product_name,
                              u.fname as buyer_fname, u.lname as buyer_lname, u.mobile1 as buyer_phone,
                              sup.fname as supplier_fname, sup.lname as supplier_lname
                       FROM offers o
                       LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
                       LEFT JOIN user u ON o.buyer_id = u.usr_id
                       LEFT JOIN user sup ON o.supplier_id = sup.usr_id
                       WHERE o.status = 'pending'
                       ORDER BY o.created_at DESC";
        $offers_res = mysqli_query($con, $offers_sql);
        
        if (mysqli_num_rows($offers_res) > 0):
        ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>RFQ ID</th>
                    <th>المنتج</th>
                    <th>المشتري</th>
                    <th>المورد</th>
                    <th>السعر</th>
                    <th>مدة التوصيل</th>
                    <th>التاريخ</th>
                    <th>إجراء</th>
                </tr>
            </thead>
            <tbody>
                <?php while($offer = mysqli_fetch_assoc($offers_res)): ?>
                <tr>
                    <td><?php echo $offer['rfq_id']; ?></td>
                    <td><?php echo htmlspecialchars($offer['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($offer['buyer_fname'] . ' ' . $offer['buyer_lname']); ?></td>
                    <td><?php echo htmlspecialchars($offer['supplier_fname'] . ' ' . $offer['supplier_lname']); ?></td>
                    <td><?php echo $offer['price'] . ' ' . $offer['currency']; ?></td>
                    <td><?php echo $offer['delivery_days'] . ' يوم'; ?></td>
                    <td><?php echo date('Y-m-d', strtotime($offer['created_at'])); ?></tr>
                    <td>
                        <button class="btn btn-success btn-sm send-offer-notification" 
                                data-offer-id="<?php echo $offer['id']; ?>"
                                data-buyer-phone="<?php echo $offer['buyer_phone']; ?>"
                                data-buyer-name="<?php echo htmlspecialchars($offer['buyer_fname'] . ' ' . $offer['buyer_lname']); ?>"
                                data-supplier-name="<?php echo htmlspecialchars($offer['supplier_fname'] . ' ' . $offer['supplier_lname']); ?>"
                                data-price="<?php echo $offer['price']; ?>"
                                data-currency="<?php echo $offer['currency']; ?>"
                                data-delivery="<?php echo $offer['delivery_days']; ?>"
                                data-rfq-id="<?php echo $offer['rfq_id']; ?>">
                            <i class="fa fa-whatsapp"></i> إرسال إشعار
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted">لا توجد عروض أسعار معلقة</p>
        <?php endif; ?>
    </div>
</div>



<!-- Modal إشعار واتساب -->
<div id="waNotificationModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; width:450px; max-width:90%; margin:100px auto; padding:25px; border-radius:12px; direction:rtl;">
        <span onclick="closeWaModal()" style="float:left; cursor:pointer; font-size:24px;">&times;</span>
        <h3 style="color:#25D366;"><i class="fa fa-whatsapp"></i> إرسال إشعار للمشتري</h3>
        
        <label>نص الرسالة:</label>
        <textarea id="waMessageText" rows="6" style="width:100%; padding:8px; margin:10px 0; border:1px solid #ddd; border-radius:6px;" readonly></textarea>
        
        <button onclick="copyWaMessage()" style="background:#2196F3; color:white; border:none; padding:8px 15px; border-radius:5px; margin-left:10px;">
            <i class="fa fa-copy"></i> نسخ النص
        </button>
        <a id="waLinkBtn" href="#" target="_blank" style="background:#25D366; color:white; text-decoration:none; padding:8px 15px; border-radius:5px; display:inline-block;">
            <i class="fa fa-whatsapp"></i> فتح واتساب
        </a>
        
        <div style="background:#FFF3CD; padding:10px; margin-top:15px; border-radius:6px;">
            <small>⚠️ ملاحظة: بعد فتح واتساب، قم بلصق الرسالة (Ctrl+V) ثم أرسلها.</small>
        </div>
    </div>
</div>

<script>
let currentWaUrl = '';

function sendOfferNotification(offerId, buyerPhone, buyerName, supplierName, price, currency, deliveryDays, rfqId) {
    // تنظيف رقم الجوال (إزالة أي أحرف غير رقمية)
    let cleanPhone = buyerPhone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) {
        cleanPhone = '20' + cleanPhone.substring(1);
    }
    
    // بناء رسالة واتساب
    let message = `📦 *عرض سعر جديد لطلبك RFQ #${rfqId}*\n\n`;
    message += `*المورد:* ${supplierName}\n`;
    message += `*السعر المقترح:* ${price} ${currency}\n`;
    message += `*مدة التوصيل:* ${deliveryDays} يوم\n\n`;
    message += `للاطلاع على التفاصيل والرد على المورد، يرجى تسجيل الدخول إلى حسابك:\n`;
    message += `https://egyptmart.shop/my-enquiries.php?rfq_id=${rfqId}\n\n`;
    message += `يمكنك التواصل مع المورد مباشرة عبر الرد على هذه الرسالة.`;
    
    document.getElementById('waMessageText').value = message;
    currentWaUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
    document.getElementById('waLinkBtn').href = currentWaUrl;
    document.getElementById('waNotificationModal').style.display = 'block';
    
    // تحديث حالة العرض في قاعدة البيانات
fetch('/admin/ajax-file/send_offer_notification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'offer_id=' + offerId
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('خطأ في تحديث حالة العرض:', data.error);
        }
    })
    .catch(error => console.error('Fetch error:', error));
}

function copyWaMessage() {
    let textarea = document.getElementById('waMessageText');
    textarea.select();
    document.execCommand('copy');
    alert('✓ تم نسخ الرسالة');
}

function closeWaModal() {
    document.getElementById('waNotificationModal').style.display = 'none';
}

// ربط الأزرار (ضع هذا داخل $(document).ready أو DOMContentLoaded)
document.querySelectorAll('.send-offer-notification').forEach(btn => {
    btn.addEventListener('click', function() {
        sendOfferNotification(
            this.dataset.offerId,
            this.dataset.buyerPhone,
            this.dataset.buyerName,
            this.dataset.supplierName,
            this.dataset.price,
            this.dataset.currency,
            this.dataset.delivery,
            this.dataset.rfqId
        );
    });
});
</script>