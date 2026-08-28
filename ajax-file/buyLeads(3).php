<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// السماح بالاستدعاء عبر POST و GET
$page = isset($_POST['page']) ? (int)$_POST['page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 0);
$id   = isset($_POST['id'])   ? (int)$_POST['id']   : (isset($_GET['id'])   ? (int)$_GET['id']   : 0);

if ($page == 0 || $id == 0) {
    echo "Invalid request parameters - page: $page, id: $id";
    exit;
}

include "../common.php";

// ============================================
// استعلام مبسط بدون شروط معقدة
// ============================================
$limit_start = ($page - 1) * 10;
$sql = "SELECT br.*, mu.mu_name, u.fname, u.lname, c.cn_name as country_name
        FROM buy_requirement br
        LEFT JOIN measurement_unit mu ON br.br_estimate_qty_unit = mu.mu_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN country c ON u.country = c.cn_id
        WHERE br.br_pc_id = $id
        AND br.br_approval_status = '1'
        AND br.br_display_status = '1'
        AND br.br_status = '1'
        ORDER BY br.br_id DESC
        LIMIT $limit_start, 10";

// تشخيص: عرض الاستعلام في مصدر الصفحة (يمكنك إزالته بعد الاختبار)
echo "<!-- SQL: " . htmlspecialchars($sql) . " -->";

$result = mysqli_query($con, $sql);

if (!$result) {
    echo "<div class='alert alert-danger'>خطأ في الاستعلام: " . mysqli_error($con) . "</div>";
    exit;
}

if (mysqli_num_rows($result) == 0) {
    // تشخيص: عرض عدد النتائج في مصدر الصفحة
    echo "<!-- عدد النتائج: 0 -->";
    echo '<div class="alert alert-info text-center">لا توجد طلبات شراء في هذا التصنيف حالياً</div>';
    
    // استعلام اختباري لمعرفة ما إذا كان هناك طلبات شراء أصلاً
    $test_sql = "SELECT COUNT(*) as total FROM buy_requirement WHERE br_approval_status = '1'";
    $test_result = mysqli_query($con, $test_sql);
    $test_row = mysqli_fetch_object($test_result);
    echo "<!-- إجمالي طلبات الشراء في قاعدة البيانات: " . $test_row->total . " -->";
    exit;
}

// عرض النتائج بشكل منسق
while ($row = mysqli_fetch_object($result)) {
    ?>
    <div class="buy-lead-item" style="border:1px solid #ddd; margin-bottom:15px; padding:15px; border-radius:5px; background:#fff;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
            <h3 style="margin:0 0 10px 0; color:#333;"><?php echo htmlspecialchars($row->br_pd_name ?? 'غير محدد'); ?></h3>
            <span style="background:#e95801; color:#fff; padding:3px 10px; border-radius:3px; font-size:12px;">
                طلب شراء
            </span>
        </div>
        <div style="color:#666; margin:10px 0;">
            <?php echo nl2br(htmlspecialchars(substr($row->br_requirement ?? '', 0, 200))); ?>
            <?php if (strlen($row->br_requirement ?? '') > 200) { ?>...<?php } ?>
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:15px; margin:10px 0; font-size:13px; color:#888;">
            <span><strong>الكمية:</strong> <?php echo htmlspecialchars($row->br_estimate_qty ?? ''); ?> <?php echo htmlspecialchars($row->mu_name ?? ''); ?></span>
            <span><strong>البلد:</strong> <?php echo htmlspecialchars($row->country_name ?? 'غير محدد'); ?></span>
            <span><strong>تاريخ النشر:</strong> <?php echo date('Y-m-d', strtotime($row->br_posting_date ?? 'now')); ?></span>
        </div>
        <div style="margin-top:10px;">
            <a href="buyleads-details.php?id=<?php echo $row->br_id; ?>" class="btn-details" style="background:#e95801; color:#fff; padding:6px 15px; text-decoration:none; border-radius:3px; display:inline-block;">
                تفاصيل الطلب
            </a>
        </div>
    </div>
    <?php
}

// إضافة روابط التصفح (pagination)
$count_sql = "SELECT COUNT(*) as total FROM buy_requirement 
              WHERE br_pc_id = $id 
              AND br_approval_status = '1' 
              AND br_display_status = '1' 
              AND br_status = '1'";
$count_result = mysqli_query($con, $count_sql);
$count_row = mysqli_fetch_object($count_result);
$total = $count_row->total;
$total_pages = ceil($total / 10);

if ($total_pages > 1) {
    echo '<div style="text-align:center; margin-top:20px; padding:10px; direction:ltr;">';
    echo '<span style="display:inline-block;">الصفحات: </span>';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $page) ? 'active' : '';
        echo '<a href="#" onclick="showLeadMain(' . $i . ', ' . $id . '); return false;" 
              style="display:inline-block; margin:0 3px; padding:5px 10px; background:' . ($active ? '#e95801' : '#f0f0f0') . '; color:' . ($active ? '#fff' : '#333') . '; text-decoration:none; border-radius:3px;">
              ' . $i . '</a> ';
    }
    echo '</div>';
}
?>