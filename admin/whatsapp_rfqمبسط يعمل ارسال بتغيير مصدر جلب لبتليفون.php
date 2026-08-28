<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../lib/connect.php";

if (empty($_SESSION['ad_id_indm'])) {
    echo "غير مصرح";
    exit;
}

// استعلام معدل: يجلب رقم المورد من جدول user
$sql = "SELECT br.*, 
               u.fname, u.lname, u.mobile1, u.email,
               p.pd_title, p.pd_uid as supplier_id,
               su.mobile1 as supplier_phone  -- ✅ جلب رقم المورد من جدول user
        FROM buy_requirement br
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user su ON p.pd_uid = su.usr_id  -- ✅ ربط بجدول user للمورد
        ORDER BY br.br_id DESC
        LIMIT 20";
$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>طلبات الشراء</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2>طلبات الشراء</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>RFQ ID</th>
                <th>المنتج</th>
                <th>المشتري</th>
                <th>رقم المورد</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php
            // جلب رقم المورد (من su.mobile1)
            $supplierPhone = $row['supplier_phone'] ?? '';
            if (!empty($supplierPhone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $supplierPhone);
                if (substr($cleanPhone, 0, 2) != '20') {
                    $cleanPhone = '20' . ltrim($cleanPhone, '0');
                }
            } else {
                $cleanPhone = '20123456789'; // رقم افتراضي للاختبار
            }
            
            // بناء الرسالة
            $msg = "📦 طلب شراء جديد #" . $row['br_id'] . "\n\n";
            $msg .= "المنتج: " . ($row['pd_title'] ?? 'غير محدد') . "\n";
            $msg .= "الكمية: " . ($row['br_estimate_qty'] ?? 0) . ' ' . ($row['br_estimate_qty_unit'] ?? '') . "\n";
            $msg .= "المشتري: " . ($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '') . "\n";
            $msg .= "هاتف المشتري: " . ($row['mobile1'] ?? '') . "\n\n";
            $msg .= "للتقديم: https://egyptmart.shop/supplier/whatsapp_rfq_view.php?id=" . $row['br_id'];
            
            $wa_url = "https://wa.me/" . $cleanPhone . "?text=" . rawurlencode($msg);
            ?>
            <tr>
                <td><?php echo $row['br_id']; ?></td>
                <td><?php echo htmlspecialchars($row['pd_title'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                <td><?php echo $cleanPhone; ?></td>
                <td>
                    <a href="<?php echo $wa_url; ?>" target="_blank" class="btn btn-success btn-sm">
                        <i class="fa fa-whatsapp"></i> واتساب
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>