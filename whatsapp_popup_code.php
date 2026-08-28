<!-- WhatsApp RFQ Popup -->
<div id="waModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; width:400px; max-width:90%; margin:100px auto; padding:25px; border-radius:10px; direction:rtl;">
        <span onclick="closeWaModal()" style="float:left; cursor:pointer; font-size:20px;">&times;</span>
        <h3 style="color:#25D366; margin-top:0;">طلب سعر عبر واتساب</h3>
        <form id="waForm">
            <input type="hidden" id="wa_pid">
            <input type="hidden" id="wa_pname">
            <div style="margin-bottom:15px;">
                <label>الكمية التقريبية (من)</label>
                <input type="number" id="wa_qty_from" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
            </div>
            <div style="margin-bottom:15px;">
                <label>إلى</label>
                <input type="number" id="wa_qty_to" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
            </div>
            <div style="margin-bottom:15px;">
                <label>التفاصيل</label>
                <textarea id="wa_details" rows="4" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></textarea>
            </div>
            <button type="submit" style="background:#25D366; color:#fff; border:none; padding:12px; width:100%; border-radius:5px; font-size:16px; cursor:pointer;">إرسال الطلب</button>
        </form>
    </div>
</div>

<script>
function openWaRfq(pid, pname) {
    console.log("Product ID:", pid);
    console.log("Product Name:", pname);
    if (!pid || pid == 0) {
        alert('خطأ: لم يتم العثور على معرف المنتج');
        return;
    }
    document.getElementById('wa_pid').value = pid;
    document.getElementById('wa_pname').value = pname;
    document.getElementById('waModal').style.display = 'block';
}

function closeWaModal() {
    document.getElementById('waModal').style.display = 'none';
}

document.getElementById('waForm').onsubmit = async function(e) {
    e.preventDefault();
    
    var pid = document.getElementById('wa_pid').value;
    if (!pid || pid == 0) {
        alert('خطأ: معرف المنتج غير صالح');
        return;
    }
    
    let btn = this.querySelector('button');
    btn.disabled = true;
    btn.innerText = 'جاري الإرسال...';
    
    let formData = new FormData();
    formData.append('product_id', pid);
    formData.append('product_name', document.getElementById('wa_pname').value);
    formData.append('qty_from', document.getElementById('wa_qty_from').value);
    formData.append('qty_to', document.getElementById('wa_qty_to').value);
    formData.append('requirement_details', document.getElementById('wa_details').value);
    
    try {
        let res = await fetch('/whatsapp_rfq_handler.php', {method:'POST', body:formData});
        let data = await res.json();
        if(data.success) {
            alert('✅ Your RFQ has been noted, suppliers will contact you soon.');
            window.open(data.whatsapp_url, '_blank');
            closeWaModal(); // هذه الدالة موجودة الآن
            document.getElementById('waForm').reset();
        } else {
            alert('❌ ' + data.error);
            btn.disabled = false;
            btn.innerText = 'إرسال الطلب';
        }
    } catch(error) {
        alert('خطأ في الاتصال: ' + error.message);
        btn.disabled = false;
        btn.innerText = 'إرسال الطلب';
    }
};
</script>