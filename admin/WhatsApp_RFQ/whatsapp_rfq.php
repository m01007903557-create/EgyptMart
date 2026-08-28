<?php
// ✅ الحفاظ على التعريف القديم
define('ACCESS_ALLOWED', true);

// ✅ بدء الجلسة إذا لم تبدأ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ ============================================================
// ✅ معالجة الطلبات الواردة من saleoffer_whatsapp_handler.php
// ✅ ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['source']) && $_POST['source'] == 'saleoffer') {
    header('Content-Type: application/json');
    
    $br_id = isset($_POST['br_id']) ? (int)$_POST['br_id'] : 0;
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $product_name = isset($_POST['product_name']) ? mysqli_real_escape_string($con, $_POST['product_name']) : '';
    $qty_from = isset($_POST['qty_from']) ? (int)$_POST['qty_from'] : 1;
    $qty_to = isset($_POST['qty_to']) ? (int)$_POST['qty_to'] : 1;
    $requirement = isset($_POST['requirement_details']) ? mysqli_real_escape_string($con, $_POST['requirement_details']) : '';
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    error_log("📥 SaleOffer RFQ received: br_id=$br_id, user_id=$user_id, product_id=$product_id");
    
    if ($br_id > 0) {
        $update_sql = "UPDATE buy_requirement SET 
            br_pc_id = '$product_id',
            br_estimate_qty = '$qty_to',
            br_estimate_qty_unit = 'piece',
            source_channel = 'whatsapp',
            source_platform = 'saleoffer',
            communication_type = 'whatsapp',
            whatsapp_sent = 1,
            whatsapp_sent_date = NOW(),
            wa_status = 'pending'
        WHERE br_id = $br_id";
        
        mysqli_query($con, $update_sql);
        error_log("✅ Updated buy_requirement: br_id=$br_id");
    }
    
    echo json_encode(['success' => true, 'message' => 'RFQ received and processed']);
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

// ✅ ============================================================
// ✅ معالجة خاصة لعروض البيع (saleoffer)
// ✅ ============================================================
$results_display = [];

if ($results && mysqli_num_rows($results) > 0) {
    while ($row = mysqli_fetch_assoc($results)) {
        $row['supplier_name'] = 'غير محدد';
        
        if (isset($row['source_platform']) && $row['source_platform'] == 'saleoffer') {
            $product_id_for_supplier = (int)($row['br_pc_id'] ?? 0);
            if ($product_id_for_supplier > 0) {
                $supplier_sql = "SELECT u.fname, u.lname, bp.bnsprof_compname 
                                 FROM sale_offer so
                                 LEFT JOIN user u ON so.usr_id = u.usr_id
                                 LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                                 WHERE so.so_id = $product_id_for_supplier 
                                 LIMIT 1";
                $supplier_res = mysqli_query($con, $supplier_sql);
                if ($supplier_res && mysqli_num_rows($supplier_res) > 0) {
                    $supplier_data = mysqli_fetch_assoc($supplier_res);
                    $row['supplier_name'] = $supplier_data['bnsprof_compname'] ?? 
                                           ($supplier_data['fname'] . ' ' . $supplier_data['lname']);
                }
            }
        } else {
            $product_id_for_supplier = (int)($row['br_pc_id'] ?? 0);
            if ($product_id_for_supplier > 0) {
                $sup_sql = "SELECT bp.bnsprof_compname 
                            FROM products p 
                            LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid 
                            WHERE p.pd_id = $product_id_for_supplier 
                            LIMIT 1";
                $sup_res = mysqli_query($con, $sup_sql);
                if ($sup_res && mysqli_num_rows($sup_res) > 0) {
                    $sup_row = mysqli_fetch_assoc($sup_res);
                    $row['supplier_name'] = $sup_row['bnsprof_compname'] ?? 'غير محدد';
                }
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
    <!-- ... باقي الكود كما هو ... -->
    
    <tbody>
    <?php if(count($results_display) > 0): ?>
    <?php foreach($results_display as $row): ?>
    <tr>
        <td><input type="checkbox" name="br_ids[]" value="<?php echo $row['br_id']; ?>"></td>
        <td><?php echo $row['br_id']; ?></td>
        <td><?php echo date('Y-m-d', strtotime($row['br_posting_date'])); ?></td>
        <td class="center">
            <?php 
            $img = !empty($row['pd_image']) ? explode(',', $row['pd_image'])[0] : 'noimage.jpg';
            echo '<img src="../upload/myproduct/' . $img . '" width="50" height="50" style="object-fit:cover;">';
            ?>
        </td>
        <td><?php echo htmlspecialchars($row['pd_title'] ?? $row['br_pd_name']); ?></td>
        <td><?php echo mb_substr(htmlspecialchars($row['br_requirement']), 0, 30) . '...'; ?></td>
        <td class="center"><?php echo htmlspecialchars($row['supplier_name'] ?? 'غير محدد'); ?></td>
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
                    <button class="btn btn-success btn-sm" onclick="sendAndWhatsApp(<?php echo $row['br_id']; ?>)" title="إرسال وواتساب">
                        <i class="fa fa-paper-plane"></i> <i class="fa fa-whatsapp"></i>
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