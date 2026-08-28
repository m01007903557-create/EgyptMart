<?php
// =============================================
// admin/social_requests.php
// عرض طلبات التواصل الاجتماعي
// =============================================

require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../includes/rfq_functions.php';

// التحقق من صلاحيات المدير
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

global $con;

// =============================================
// معالجة الفلترة والبحث
// =============================================
$source_filter = $_GET['source'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// بناء الاستعلام
$where_conditions = ["source_channel IN ('facebook', 'instagram', 'telegram', 'whatsapp_business', 'social_facebook', 'social_instagram', 'social_telegram', 'social_whatsapp')"];

if ($source_filter !== 'all') {
    $where_conditions[] = "source_channel = '" . mysqli_real_escape_string($con, $source_filter) . "'";
}

if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($con, $search);
    $where_conditions[] = "(br_pd_name LIKE '%$search_escaped%' 
                           OR br_requirement LIKE '%$search_escaped%' 
                           OR source_detail LIKE '%$search_escaped%')";
}

$where_clause = implode(' AND ', $where_conditions);

// =============================================
// جلب البيانات
// =============================================
$sql = "SELECT br.*, u.user_name, u.user_email 
        FROM buy_requirement br
        LEFT JOIN users u ON u.user_id = br.br_u_id
        WHERE $where_clause
        ORDER BY br.br_posting_date DESC 
        LIMIT $limit OFFSET $offset";

$result = mysqli_query($con, $sql);

// =============================================
// جلب الإحصائيات
// =============================================
$stats_sql = "SELECT 
    source_channel,
    COUNT(*) as total,
    SUM(CASE WHEN br_status = 'new' THEN 1 ELSE 0 END) as new_count,
    SUM(CASE WHEN br_status IN ('contacted', 'quoted') THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN br_status IN ('won', 'closed') THEN 1 ELSE 0 END) as completed
    FROM buy_requirement 
    WHERE source_channel IN ('facebook', 'instagram', 'telegram', 'whatsapp_business', 'social_facebook', 'social_instagram', 'social_telegram', 'social_whatsapp')
    GROUP BY source_channel";

$stats_result = mysqli_query($con, $stats_sql);
$stats = [];
while ($row = mysqli_fetch_assoc($stats_result)) {
    $stats[$row['source_channel']] = $row;
}

// =============================================
// عرض الصفحة
// =============================================
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طلبات التواصل الاجتماعي</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .stat-box { 
            display: inline-block; 
            padding: 15px; 
            margin: 10px; 
            background: #f5f5f5; 
            border-radius: 8px; 
            min-width: 150px;
            border: 1px solid #ddd;
        }
        .stat-number { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .stat-label { font-size: 14px; color: #7f8c8d; }
        .stat-new { color: #e74c3c; }
        .stat-progress { color: #f39c12; }
        .stat-done { color: #27ae60; }
        .filter-bar { margin: 15px 0; padding: 15px; background: #f9f9f9; border-radius: 8px; }
        .filter-bar a { margin: 0 10px; padding: 5px 15px; text-decoration: none; background: #fff; border: 1px solid #ddd; border-radius: 4px; }
        .filter-bar a.active { background: #3498db; color: #fff; border-color: #3498db; }
        .source-badge { 
            display: inline-block; 
            padding: 3px 10px; 
            border-radius: 12px; 
            font-size: 12px; 
            font-weight: bold;
            color: #fff;
        }
        .source-facebook { background: #1877f2; }
        .source-instagram { background: #e4405f; }
        .source-telegram { background: #0088cc; }
        .source-whatsapp { background: #25d366; }
        .source-social_facebook { background: #1877f2; }
        .source-social_instagram { background: #e4405f; }
        .source-social_telegram { background: #0088cc; }
        .source-social_whatsapp { background: #25d366; }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
        }
        .status-new { background: #e74c3c; color: #fff; }
        .status-contacted { background: #f39c12; color: #fff; }
        .status-quoted { background: #3498db; color: #fff; }
        .status-won { background: #27ae60; color: #fff; }
        .status-lost { background: #95a5a6; color: #fff; }
        .status-closed { background: #2c3e50; color: #fff; }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <?php include __DIR__ . '/../includes/admin-header.php'; ?>
        
        <div class="admin-content">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../includes/admin-left-con.php'; ?>
            
            <div class="main-content">
                <h1>📱 طلبات التواصل الاجتماعي</h1>
                
                <!-- ============================================= -->
                <!-- إحصائيات سريعة -->
                <!-- ============================================= -->
                <div class="stats-row">
                    <?php foreach ($stats as $source => $data): ?>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $data['total']; ?></div>
                            <div class="stat-label">
                                <?php echo ucfirst(str_replace(['social_', '_'], ['', ' '], $source)); ?>
                            </div>
                            <div style="font-size:12px; margin-top:5px;">
                                <span class="stat-new">جديد: <?php echo $data['new_count']; ?></span> |
                                <span class="stat-progress">قيد: <?php echo $data['in_progress']; ?></span> |
                                <span class="stat-done">مكتمل: <?php echo $data['completed']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- ============================================= -->
                <!-- شريط الفلترة -->
                <!-- ============================================= -->
                <div class="filter-bar">
                    <a href="?source=all" class="<?php echo $source_filter == 'all' ? 'active' : ''; ?>">الكل</a>
                    <a href="?source=facebook" class="<?php echo $source_filter == 'facebook' ? 'active' : ''; ?>">فيسبوك</a>
                    <a href="?source=instagram" class="<?php echo $source_filter == 'instagram' ? 'active' : ''; ?>">إنستجرام</a>
                    <a href="?source=telegram" class="<?php echo $source_filter == 'telegram' ? 'active' : ''; ?>">تليجرام</a>
                    <a href="?source=whatsapp_business" class="<?php echo $source_filter == 'whatsapp_business' ? 'active' : ''; ?>">واتساب أعمال</a>
                    
                    <form method="GET" style="display:inline-block; float:left;">
                        <input type="hidden" name="source" value="<?php echo $source_filter; ?>">
                        <input type="text" name="search" placeholder="بحث..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">🔍</button>
                    </form>
                </div>
                
                <!-- ============================================= -->
                <!-- جدول الطلبات -->
                <!-- ============================================= -->
                <table class="admin-table" border="1" cellpadding="10" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المصدر</th>
                            <th>المنتج</th>
                            <th>التفاصيل</th>
                            <th>رقم العميل</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php $counter = $offset + 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td>
                                        <span class="source-badge source-<?php echo $row['source_channel']; ?>">
                                            <?php 
                                            $source_display = [
                                                'facebook' => 'فيسبوك',
                                                'instagram' => 'إنستجرام',
                                                'telegram' => 'تليجرام',
                                                'whatsapp_business' => 'واتساب أعمال',
                                                'social_facebook' => 'فيسبوك',
                                                'social_instagram' => 'إنستجرام',
                                                'social_telegram' => 'تليجرام',
                                                'social_whatsapp' => 'واتساب أعمال'
                                            ];
                                            echo $source_display[$row['source_channel']] ?? $row['source_channel'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['br_pd_name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['br_requirement'] ?? '', 0, 50)); ?>...</td>
                                    <td><?php echo htmlspecialchars($row['source_detail'] ?? ''); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $row['br_status']; ?>">
                                            <?php 
                                            $status_display = [
                                                'new' => 'جديد',
                                                'contacted' => 'تم التواصل',
                                                'quoted' => 'تم التسعير',
                                                'won' => 'تم الفوز',
                                                'lost' => 'ملغي',
                                                'closed' => 'مغلق'
                                            ];
                                            echo $status_display[$row['br_status']] ?? $row['br_status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($row['br_posting_date'])); ?></td>
                                    <td>
                                        <a href="social_request_view.php?id=<?php echo $row['br_id']; ?>">عرض</a>
                                        <a href="social_request_edit.php?id=<?php echo $row['br_id']; ?>">تعديل</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:30px; color:#999;">
                                    لا توجد طلبات من وسائل التواصل الاجتماعي
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- ============================================= -->
                <!-- التنقل بين الصفحات (Pagination) -->
                <!-- ============================================= -->
                <?php
                $count_sql = "SELECT COUNT(*) as total FROM buy_requirement WHERE $where_clause";
                $count_result = mysqli_query($con, $count_sql);
                $count_row = mysqli_fetch_assoc($count_result);
                $total_records = $count_row['total'];
                $total_pages = ceil($total_records / $limit);
                ?>
                
                <?php if ($total_pages > 1): ?>
                <div class="pagination" style="margin-top:20px; text-align:center;">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?source=<?php echo $source_filter; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                           style="padding:5px 10px; margin:2px; background:<?php echo $i == $page ? '#3498db' : '#f5f5f5'; ?>; 
                                  color:<?php echo $i == $page ? '#fff' : '#333'; ?>; text-decoration:none; border-radius:4px;">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>