<?php
// لا حاجة لـ session_start() أو require_once هنا، لأنها موجودة بالفعل في الملف المستدعي

// ============================================================
// التحقق من وجود المتغيرات الأساسية القادمة من الملف المستدعي
// ============================================================
if (!isset($con)) {
    die('<div class="alert alert-danger">خطأ فني: اتصال قاعدة البيانات غير موجود.</div>');
}

if (!isset($chat_code) || empty($chat_code)) {
    die('<div class="alert alert-danger">خطأ فني: كود الشات لم يتم تمريره إلى ملف المحتوى.</div>');
}

// تنظيف كود الشات من أي مسافات أو أحرف إضافية
$chat_code = mysqli_real_escape_string($con, $chat_code);

// للتصحيح: عرض قيمة الكود في مصدر الصفحة (يمكنك إزالة هذا السطر لاحقاً)
echo "<!-- جاري البحث عن الشات بالكود: " . htmlspecialchars($chat_code) . " -->";

// ============================================================
// استعلام SQL لجلب بيانات الشات
// ============================================================
$sql = "SELECT c.*, 
               p.pd_title as product_name,
               sup.bnsprof_comp_url as supplier_name,
               sup.bnsprof_uid as supplier_id,
               u.usr_id as buyer_id
        FROM chat_rooms c
        LEFT JOIN buy_requirement br ON c.rfq_id = br.br_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN business_profile sup ON c.supplier_id = sup.bnsprof_uid
        LEFT JOIN user u ON c.buyer_id = u.usr_id
        WHERE c.chat_code = '$chat_code'";

$res = mysqli_query($con, $sql);

if (!$res) {
    echo '<div class="alert alert-danger">خطأ في استعلام قاعدة البيانات: ' . mysqli_error($con) . '</div>';
    return;
}

$chat = mysqli_fetch_assoc($res);

if (!$chat) {
    echo '<div class="alert alert-danger">';
    echo '<strong>الشات غير موجود في قاعدة البيانات.</strong><br>';
    echo 'الكود المطلوب: ' . htmlspecialchars($chat_code) . '<br>';
    echo 'تأكد من أن هذا الكود موجود في جدول `chat_rooms`.';
    echo '</div>';
    return;
}

// إذا وصلنا إلى هنا، فهذا يعني أنه تم العثور على الشات بنجاح
// ... ضع هنا باقي كود عرض الشات (HTML والجافاسكريبت) ...
echo "<!-- تم العثور على الشات: " . htmlspecialchars($chat['chat_code']) . " -->";
?>

<!-- هنا يبدأ عرض واجهة الشات -->
<div class="chat-container">
    <div class="chat-header">
        <i class="fa fa-whatsapp"></i> محادثة - <?php echo htmlspecialchars($chat['chat_code']); ?>
    </div>
    <!-- باقي عناصر الشات (messages, input, buttons, JS) -->
</div>
<?php
// انتهاء ملف chat-content.php
?>