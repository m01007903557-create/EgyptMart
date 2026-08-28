<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من تسجيل دخول الأدمن (نفس الطريقة الأصلية)
check_admin_login();

global $con;

// ... باقي الكود ...

// معالجة تحديث حالة الاستفسار
if (isset($_POST['update_status']) && isset($_POST['enquiry_id'])) {
    $enquiry_id = (int)$_POST['enquiry_id'];
    $new_status = $_POST['status'];
    $sql_update = "UPDATE buy_enquiries SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql_update);
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $enquiry_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// معالجة نشر الطلب (Publish to Buy Leads)
if (isset($_POST['publish_lead']) && isset($_POST['enquiry_id'])) {
    $enquiry_id = (int)$_POST['enquiry_id'];
    
    // جلب بيانات الاستفسار
    $sql_get = "SELECT be.*, u.fname, u.lname, u.email, u.mobile1, bp.bnsprof_compname 
                FROM buy_enquiries be
                LEFT JOIN user u ON be.buyer_id = u.usr_id
                LEFT JOIN business_profile bp ON be.buyer_id = bp.bnsprof_uid
                WHERE be.id = ?";
    $stmt = mysqli_prepare($con, $sql_get);
    mysqli_stmt_bind_param($stmt, 'i', $enquiry_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $enquiry = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt);
    
    if ($enquiry) {
        // إخفاء بيانات الاتصال الشخصية
        $hidden_contact = "بيانات الاتصال متاحة للأدمن فقط - الرجاء تسجيل الدخول للمنصة";
        
        // حفظ في جدول buy_leads (أنشئه إذا لم يكن موجوداً)
        $sql_insert = "INSERT INTO buy_leads (product_name, quantity_from, quantity_to, unit, description, buyer_company, buyer_city, status, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
        $stmt2 = mysqli_prepare($con, $sql_insert);
        mysqli_stmt_bind_param($stmt2, 'siissss', 
            $enquiry->product_name, 
            $enquiry->quantity_from, 
            $enquiry->quantity_to, 
            $enquiry->product_unit,
            substr($enquiry->message, 0, 500),
            $enquiry->bnsprof_compname ?: $enquiry->fname . ' ' . $enquiry->lname,
            '' // city - يمكن إضافته لاحقاً
        );
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
        
        // تحديث حالة الاستفسار
        $sql_update = "UPDATE buy_enquiries SET status = 'published' WHERE id = ?";
        $stmt3 = mysqli_prepare($con, $sql_update);
        mysqli_stmt_bind_param($stmt3, 'i', $enquiry_id);
        mysqli_stmt_execute($stmt3);
        mysqli_stmt_close($stmt3);
    }
}

// جلب قائمة الاستفسارات
$enquiries = [];
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$sql = "SELECT be.*, 
               u.fname, u.lname, u.email, u.mobile1,
               bp.bnsprof_compname as buyer_company
        FROM buy_enquiries be
        LEFT JOIN user u ON be.buyer_id = u.usr_id
        LEFT JOIN business_profile bp ON be.buyer_id = bp.bnsprof_uid
        WHERE be.status = ?
        ORDER BY be.enquiry_date DESC";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $status_filter);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_object($result)) {
    $enquiries[] = $row;
}
mysqli_stmt_close($stmt);

// إحصائيات سريعة
$stats = [];
$sql_stats = "SELECT status, COUNT(*) as count FROM buy_enquiries GROUP BY status";
$result_stats = mysqli_query($con, $sql_stats);
while ($row = mysqli_fetch_object($result_stats)) {
    $stats[$row->status] = $row->count;
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة استفسارات الشراء - EgyptMART Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #f5f7fb;
            padding: 15px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #466da0, #2c4c7a);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
        }
        .header h1 i {
            margin-left: 8px;
        }
        .back-link {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
        }
        
        /* Stats Bar */
        .stats-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-item {
            background: white;
            border-radius: 8px;
            padding: 10px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.2s;
        }
        .stat-item.active {
            background: #466da0;
            color: white;
        }
        .stat-item.active .stat-number,
        .stat-item.active .stat-label {
            color: white;
        }
        .stat-icon {
            font-size: 20px;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #466da0;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
        }
        
        /* Table */
        .enquiries-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            padding: 12px 10px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fc;
            font-weight: 600;
            color: #466da0;
        }
        tr:hover {
            background: #f8f9fc;
        }
        
        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-published { background: #cce5ff; color: #004085; }
        .badge-completed { background: #d1ecf1; color: #0c5460; }
        
        /* Buttons */
        .btn-icon {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .btn-icon:hover {
            background: #eef2f6;
        }
        .btn-whatsapp { color: #25D366; }
        .btn-publish { color: #466da0; }
        .btn-view { color: #17a2b8; }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
        
        /* Form */
        select, textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            margin-top: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: 600;
            font-size: 13px;
            display: block;
        }
        
        /* Product Details */
        .product-detail {
            background: #f5f7fb;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .product-detail p {
            margin: 5px 0;
            font-size: 13px;
        }
        
        @media (max-width: 768px) {
            th, td { padding: 8px 6px; font-size: 11px; }
            .stat-label { display: none; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-shopping-cart"></i> إدارة استفسارات الشراء</h1>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-right"></i> العودة للوحة التحكم</a>
    </div>
    
    <!-- شريط الإحصائيات -->
    <div class="stats-bar">
        <a href="?status=pending" class="stat-item <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
            <i class="fas fa-clock stat-icon"></i>
            <div>
                <div class="stat-number"><?php echo $stats['pending'] ?? 0; ?></div>
                <div class="stat-label">جديدة</div>
            </div>
        </a>
        <a href="?status=approved" class="stat-item <?php echo $status_filter == 'approved' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle stat-icon"></i>
            <div>
                <div class="stat-number"><?php echo $stats['approved'] ?? 0; ?></div>
                <div class="stat-label">تمت الموافقة</div>
            </div>
        </a>
        <a href="?status=published" class="stat-item <?php echo $status_filter == 'published' ? 'active' : ''; ?>">
            <i class="fas fa-globe stat-icon"></i>
            <div>
                <div class="stat-number"><?php echo $stats['published'] ?? 0; ?></div>
                <div class="stat-label">منشورة</div>
            </div>
        </a>
        <a href="?status=completed" class="stat-item <?php echo $status_filter == 'completed' ? 'active' : ''; ?>">
            <i class="fas fa-check-double stat-icon"></i>
            <div>
                <div class="stat-number"><?php echo $stats['completed'] ?? 0; ?></div>
                <div class="stat-label">مكتملة</div>
            </div>
        </a>
    </div>
    
    <!-- جدول الاستفسارات -->
    <div class="enquiries-table">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>المشتري</th>
                    <th>الكمية</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($enquiries) > 0): ?>
                    <?php foreach ($enquiries as $enq): ?>
                        <tr>
                            <td><?php echo $enq->id; ?></td>
                            <td><?php echo htmlspecialchars(mb_substr($enq->product_name, 0, 30)); ?></td>
                            <td>
                                <?php echo htmlspecialchars($enq->buyer_company ?: $enq->fname . ' ' . $enq->lname); ?>
                                <br><small style="color:#888;"><?php echo htmlspecialchars($enq->mobile1); ?></small>
                            </td>
                            <td><?php echo $enq->quantity_from . ' - ' . $enq->quantity_to . ' ' . htmlspecialchars($enq->product_unit); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($enq->enquiry_date)); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $enq->status; ?>">
                                    <?php 
                                        $status_names = ['pending'=>'قيد الانتظار', 'approved'=>'موافق', 'published'=>'منشور', 'completed'=>'مكتمل'];
                                        echo $status_names[$enq->status] ?? $enq->status;
                                    ?>
                                </span>
                            </td>
                            <td>
                                <!-- زر عرض التفاصيل -->
                                <button class="btn-icon btn-view" onclick="showDetails(<?php echo htmlspecialchars(json_encode($enq)); ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- زر واتساب للمورد -->
                                <?php if ($enq->mobile1): ?>
                                <a href="https://wa.me/<?php echo ltrim($enq->mobile1, '0'); ?>?text=مرحباً، لديك استفسار جديد من <?php echo urlencode($enq->buyer_company ?: $enq->fname); ?> بخصوص <?php echo urlencode($enq->product_name); ?>" 
                                   target="_blank" class="btn-icon btn-whatsapp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <?php endif; ?>
                                
                                <!-- زر نشر الطلب -->
                                <?php if ($enq->status == 'pending' || $enq->status == 'approved'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('سيتم نشر هذا الطلب لجميع الموردين. هل أنت متأكد؟');">
                                    <input type="hidden" name="enquiry_id" value="<?php echo $enq->id; ?>">
                                    <button type="submit" name="publish_lead" class="btn-icon btn-publish" title="نشر الطلب للموردين">
                                        <i class="fas fa-share-alt"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #888;">
                            <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                            لا توجد استفسارات بهذه الحالة
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- مودال عرض التفاصيل -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-file-alt"></i> تفاصيل الاستفسار</h3>
            <button class="close-modal" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="modalBody">
            <!-- يتم تعبئته بواسطة JavaScript -->
        </div>
        <div class="modal-footer">
            <button class="close-modal" onclick="closeModal()">إغلاق</button>
        </div>
    </div>
</div>

<script>
function showDetails(enq) {
    const modal = document.getElementById('detailsModal');
    const modalBody = document.getElementById('modalBody');
    
    modalBody.innerHTML = `
        <div class="product-detail">
            <p><strong><i class="fas fa-box"></i> المنتج:</strong> ${enq.product_name}</p>
            <p><strong><i class="fas fa-balance-scale"></i> الوحدة:</strong> ${enq.product_unit}</p>
            <p><strong><i class="fas fa-sort-amount-down"></i> الكمية المطلوبة:</strong> ${enq.quantity_from} - ${enq.quantity_to} ${enq.product_unit}</p>
        </div>
        
        <div class="product-detail">
            <p><strong><i class="fas fa-user"></i> بيانات المشتري:</strong></p>
            <p>${enq.buyer_company || enq.fname + ' ' + enq.lname}</p>
            <p><i class="fas fa-phone"></i> ${enq.mobile1 || 'غير متوفر'}</p>
            <p><i class="fas fa-envelope"></i> ${enq.email || 'غير متوفر'}</p>
        </div>
        
        <div class="product-detail">
            <p><strong><i class="fas fa-comment"></i> نص الاستفسار:</strong></p>
            <p style="white-space: pre-wrap;">${enq.message}</p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="enquiry_id" value="${enq.id}">
            <div class="form-group">
                <label><i class="fas fa-tag"></i> تحديث الحالة:</label>
                <select name="status">
                    <option value="pending" ${enq.status == 'pending' ? 'selected' : ''}>⏳ قيد الانتظار</option>
                    <option value="approved" ${enq.status == 'approved' ? 'selected' : ''}>✅ تمت الموافقة</option>
                    <option value="published" ${enq.status == 'published' ? 'selected' : ''}>🌐 تم النشر</option>
                    <option value="completed" ${enq.status == 'completed' ? 'selected' : ''}>✔️ مكتمل</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn-publish" style="background:#466da0; color:white; padding:8px 15px; border:none; border-radius:6px; cursor:pointer; width:100%;">
                <i class="fas fa-save"></i> حفظ التغييرات
            </button>
        </form>
    `;
    
    modal.classList.add('active');
}

function closeModal() {
    document.getElementById('detailsModal').classList.remove('active');
}

// إغلاق المودال بالضغط على Esc
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
</body>
</html>