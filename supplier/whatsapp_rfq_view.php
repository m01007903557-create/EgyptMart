<?php
$token = $_GET['token'] ?? '';
$id = $_GET['id'] ?? 0;

if(empty($token) || $id == 0) {
    die('رابط غير صالح');
}

include "../lib/connect.php";

$sql = "SELECT br.*, u.fname, u.lname, u.mobile1, u.email, u.usr_business_name,
               p.pd_title, p.pd_image, p.pd_desc
        FROM buy_requirement br
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        WHERE br.br_id = '$id' AND br.wa_magic_token = '$token' AND br.wa_token_expiry > NOW()";
$res = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($res);

if(!$rfq) {
    die('الرابط منتهي الصلاحية أو غير صالح');
}

// تحديث حالة القراءة
mysqli_query($con, "UPDATE buy_requirement SET wa_supplier_read = 1 WHERE br_id = '$id'");
?>
<!DOCTYPE html>
<html dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلب شراء - RFQ #<?php echo $id; ?></title>
    <style>
        body { font-family: Arial; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 10px; padding: 25px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #25D366; border-bottom: 2px solid #25D366; padding-bottom: 10px; }
        .product-img { text-align: center; margin: 20px 0; }
        .product-img img { max-width: 200px; border-radius: 10px; }
        .info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .label { font-weight: bold; color: #555; width: 150px; display: inline-block; }
        .footer { margin-top: 30px; text-align: center; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
        .whatsapp-btn { display: inline-block; background: #25D366; color: white; padding: 10px 25px; border-radius: 50px; text-decoration: none; margin-top: 20px; }
        .buyer-info { background: #e8f5e9; padding: 15px; border-radius: 8px; margin: 15px 0; border-right: 4px solid #25D366; }
    </style>
</head>
<body>
<div class="container">
    <h1>📦 طلب شراء جديد - RFQ #<?php echo $id; ?></h1>
    
    <div class="product-img">
        <?php 
        $img = !empty($rfq['pd_image']) ? explode(',', $rfq['pd_image'])[0] : 'noimage.jpg';
        echo '<img src="../upload/myproduct/' . $img . '">';
        ?>
    </div>
    
    <div class="info">
        <div><span class="label">المنتج:</span> <?php echo htmlspecialchars($rfq['pd_title']); ?></div>
        <div><span class="label">الكمية المطلوبة:</span> <?php echo $rfq['br_estimate_qty'] . ' ' . $rfq['br_estimate_qty_unit']; ?></div>
        <div><span class="label">التفاصيل:</span><br><?php echo nl2br(htmlspecialchars($rfq['br_requirement'])); ?></div>
        <div><span class="label">تاريخ الطلب:</span> <?php echo date('Y-m-d', strtotime($rfq['br_posting_date'])); ?></div>
    </div>
    
    <div class="buyer-info">
        <strong>📞 بيانات المشتري (للتواصل المباشر):</strong><br><br>
        <div>🏢 اسم الشركة: <?php echo htmlspecialchars($rfq['bnsprof_comp_url'] ?? $rfq['fname'] . ' ' . $rfq['lname']); ?></div>>
        <div>📱 الجوال: <?php echo $rfq['mobile1']; ?></div>
        <div>📧 الإيميل: <?php echo $rfq['email']; ?></div>
    </div>
    
    <div class="footer">
        <p>✨ يرجى التواصل مع المشتري لكسب الثقة والترقي في المنصة ✨</p>
        <a href="https://wa.me/<?php echo $rfq['mobile1']; ?>?text=مرحباً، لدينا عرض سعر لمنتج <?php echo urlencode($rfq['pd_title']); ?> - RFQ #<?php echo $id; ?>" class="whatsapp-btn" target="_blank">
            📱 تواصل عبر واتساب
        </a>
    </div>
<!-- بعد عرض بيانات المشتري (buyer-info) -->
<?php 
// التحقق إذا كان المورد قد أرسل عرض سعر بالفعل
$check_sql = "SELECT * FROM whatsapp_quotations WHERE rfq_id = $id AND supplier_id = $supplier_id";
$check_res = mysqli_query($con, $check_sql);
$quotation_sent = mysqli_num_rows($check_res) > 0;
?>

<?php if (!$quotation_sent && $rfq['wa_status'] != 'accepted'): ?>
<div class="quotation-form" style="margin-top:30px; padding:20px; background:#f9f9f9; border-radius:10px;">
    <h3>📝 تقديم عرض سعر</h3>
    <form id="quotationForm">
        <input type="hidden" name="rfq_id" value="<?php echo $id; ?>">
        <div class="form-group">
            <label>السعر (USD)</label>
            <input type="number" name="price" step="0.01" required class="form-control">
        </div>
        <div class="form-group">
            <label>أقل كمية (MOQ)</label>
            <input type="number" name="moq" class="form-control">
        </div>
        <div class="form-group">
            <label>مدة التوصيل</label>
            <input type="text" name="delivery_time" placeholder="مثال: 15 يوم" class="form-control">
        </div>
        <div class="form-group">
            <label>شروط الدفع</label>
            <input type="text" name="payment_terms" placeholder="مثال: 30% مقدماً" class="form-control">
        </div>
        <div class="form-group">
            <label>رسالة للمشتري</label>
            <textarea name="message" rows="3" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">إرسال عرض السعر</button>
    </form>
</div>
<?php endif; ?>
</div>
</body>
</html>