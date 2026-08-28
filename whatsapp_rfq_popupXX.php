<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

$product_id = (int)$_GET['pid'];
$product_data = getProductDetails($product_id); // دوال موجودة عندك

if (!$product_data) exit('Product not found');
?>

<div id="whatsappRFQModal" class="whatsapp-modal">
    <div class="whatsapp-modal-content">
        <span class="whatsapp-close">&times;</span>
        <h3>طلب سعر عبر واتساب</h3>
        <form id="whatsappRFQForm">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['uid_indm'] ?? 0; ?>">
            
            <div class="form-group">
                <label>الكمية التقريبية (من)</label>
                <input type="number" name="qty_from" id="qty_from" required>
            </div>
            <div class="form-group">
                <label>إلى</label>
                <input type="number" name="qty_to" id="qty_to" required>
            </div>
            <div class="form-group">
                <label>التفاصيل</label>
                <textarea name="requirement_details" rows="4" required></textarea>
            </div>
            <button type="submit">إرسال الطلب بالواتساب</button>
        </form>
    </div>
</div>

<style>
.whatsapp-modal { display: none; position: fixed; z-index: 9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); }
.whatsapp-modal-content { background:#fff; margin:10% auto; padding:20px; width:400px; border-radius:10px; }
.whatsapp-close { float:right; cursor:pointer; font-size:28px; }
</style>


<script>
document.getElementById('whatsappRFQForm').onsubmit = async function(e) {
    e.preventDefault();
    
    let submitBtn = document.querySelector('#whatsappRFQForm button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerText = 'جاري الإرسال...';
    
    let formData = new FormData(this);
    let response = await fetch('save_whatsapp_rfq.php', {
        method: 'POST',
        body: formData
    });
    let result = await response.json();
    
    if (result.success) {
        // عرض رسالة المحاكاة
        alert(result.simulation_message);
        
        // فتح واتساب
        window.open(result.whatsapp_url, '_blank');
        
        // إغلاق البوب اب
        let modal = document.getElementById('whatsappRFQModal');
        if(modal) modal.style.display = 'none';
        document.querySelector('.whatsapp-modal')?.remove();
        
        // اختياري: إعادة تحميل الصفحة أو رسالة نجاح إضافية
        // location.reload();
    } else {
        alert('خطأ: ' + result.error);
        submitBtn.disabled = false;
        submitBtn.innerText = 'إرسال الطلب بالواتساب';
    }
};
</script>