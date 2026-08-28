<?php
// admin/rfq_dashboard.php
require_once __DIR__ . '/../includes/rfq_functions.php';

// التحقق من صلاحيات المدير
if (!isAdmin()) {
    header("Location: login.php");
    exit;
}

$stats = getRFQStats();
?>
<!DOCTYPE html>
<html>
<head>
    <title>لوحة تحكم طلبات الشراء</title>
    <style>
        .stat-box { display: inline-block; padding: 15px; margin: 10px; background: #f5f5f5; border-radius: 8px; min-width: 150px; }
        .stat-number { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .stat-label { font-size: 14px; color: #7f8c8d; }
    </style>
</head>
<body>
    <h1>📊 إحصائيات طلبات الشراء</h1>
    
    <?php foreach ($stats as $source => $data): ?>
        <div class="stat-box">
            <div class="stat-number"><?php echo array_sum(array_column($data, 'total')); ?></div>
            <div class="stat-label"><?php echo ucfirst(str_replace('_', ' ', $source)); ?></div>
        </div>
    <?php endforeach; ?>
    
    <h2>📈 تفاصيل الطلبات حسب المصدر</h2>
    <table border="1" cellpadding="10">
        <tr>
            <th>المصدر</th>
            <th>إجمالي</th>
            <th>جديد</th>
            <th>قيد المعالجة</th>
            <th>مكتمل</th>
            <th>ملغي</th>
        </tr>
        <?php foreach ($stats as $source => $days): ?>
            <?php 
            $total = array_sum(array_column($days, 'total'));
            $new = array_sum(array_column($days, 'new_requests'));
            $in_progress = array_sum(array_column($days, 'in_progress'));
            $completed = array_sum(array_column($days, 'completed'));
            $lost = array_sum(array_column($days, 'lost'));
            ?>
            <tr>
                <td><?php echo ucfirst(str_replace('_', ' ', $source)); ?></td>
                <td><?php echo $total; ?></td>
                <td><?php echo $new; ?></td>
                <td><?php echo $in_progress; ?></td>
                <td><?php echo $completed; ?></td>
                <td><?php echo $lost; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>