<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

include "../common.php";


// التحقق من تسجيل دخول الأدمن
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: ../sign-in.php");
    exit;
}


$admin_id = (int)$_SESSION['uid_indm'];

// جلب معامل URL
$enquiry_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg_id = isset($_GET['msg_id']) ? (int)$_GET['msg_id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ==============================================
// معالجة تحديث حالة الاستفسار
// ==============================================
if ($action == 'update_status' && $enquiry_id > 0) {
    $new_status = isset($_POST['status']) ? $_POST['status'] : '';
    $admin_notes = isset($_POST['admin_notes']) ? mysqli_real_escape_string($con, $_POST['admin_notes']) : '';
    
    if (in_array($new_status, ['pending', 'approved', 'replied', 'completed'])) {
        $sql_update = "UPDATE buy_enquiries SET status = ?, admin_notes = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($con, $sql_update);
        mysqli_stmt_bind_param($stmt_update, 'ssi', $new_status, $admin_notes, $enquiry_id);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
        
        header("Location: message-view.php?id=" . $enquiry_id);
        exit;
    }
}

// ==============================================
// جلب بيانات الاستفسار من جدول buy_enquiries
// ==============================================
$enquiry = null;
$buyer_data = null;
$supplier_data = null;

if ($enquiry_id > 0) {
    $sql_enquiry = "SELECT be.*, 
                           u_buyer.fname as buyer_fname, u_buyer.lname as buyer_lname, u_buyer.email as buyer_email, u_buyer.mobile1 as buyer_phone, u_buyer.country_ph_code as buyer_country_code,
                           bp_buyer.bnsprof_compname as buyer_company, bp_buyer.bnsprof_address1 as buyer_address, bp_buyer.bnsprof_city as buyer_city,
                           u_supplier.fname as supplier_fname, u_supplier.lname as supplier_lname, u_supplier.email as supplier_email, u_supplier.mobile1 as supplier_phone, u_supplier.country_ph_code as supplier_country_code,
                           bp_supplier.bnsprof_compname as supplier_company
                    FROM buy_enquiries be
                    LEFT JOIN user u_buyer ON be.buyer_id = u_buyer.usr_id
                    LEFT JOIN business_profile bp_buyer ON be.buyer_id = bp_buyer.bnsprof_uid
                    LEFT JOIN user u_supplier ON be.supplier_id = u_supplier.usr_id
                    LEFT JOIN business_profile bp_supplier ON be.supplier_id = bp_supplier.bnsprof_uid
                    WHERE be.id = ?";
    
    $stmt_enquiry = mysqli_prepare($con, $sql_enquiry);
    mysqli_stmt_bind_param($stmt_enquiry, 'i', $enquiry_id);
    mysqli_stmt_execute($stmt_enquiry);
    $result_enquiry = mysqli_stmt_get_result($stmt_enquiry);
    $enquiry = mysqli_fetch_object($result_enquiry);
    mysqli_stmt_close($stmt_enquiry);
}

// جلب الرسائل المرتبطة بالاستفسار (من جدول message)
$messages = [];
if ($enquiry_id > 0) {
    $sql_msgs = "SELECT m.*, 
                        u_from.fname as from_fname, u_from.lname as from_lname,
                        u_to.fname as to_fname, u_to.lname as to_lname
                 FROM message m
                 LEFT JOIN user u_from ON m.msg_from = u_from.usr_id
                 LEFT JOIN user u_to ON m.msg_to = u_to.usr_id
                 WHERE (m.msg_from = ? OR m.msg_to = ?)
                 ORDER BY m.msg_date DESC";
    
    $stmt_msgs = mysqli_prepare($con, $sql_msgs);
    mysqli_stmt_bind_param($stmt_msgs, 'ii', $enquiry->buyer_id, $enquiry->buyer_id);
    mysqli_stmt_execute($stmt_msgs);
    $result_msgs = mysqli_stmt_get_result($stmt_msgs);
    
    while ($row = mysqli_fetch_object($result_msgs)) {
        $messages[] = $row;
    }
    mysqli_stmt_close($stmt_msgs);
}

// جلب قائمة جميع الاستفسارات (للsidebar)
$all_enquiries = [];
$sql_all = "SELECT be.id, be.product_name, be.enquiry_date, be.status,
                   bp.bnsprof_compname as company_name
            FROM buy_enquiries be
            LEFT JOIN business_profile bp ON be.buyer_id = bp.bnsprof_uid
            ORDER BY be.enquiry_date DESC
            LIMIT 50";
$result_all = mysqli_query($con, $sql_all);
while ($row = mysqli_fetch_object($result_all)) {
    $all_enquiries[] = $row;
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إدارة الاستفسارات - EgyptMART Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Cairo', sans-serif;
        }
        body {
            background: #f5f7fb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #466da0, #2c4c7a);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin: 0 0 5px;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
        }
        .dashboard {
            display: flex;
            gap: 25px;
        }
        .sidebar {
            width: 300px;
            flex-shrink: 0;
        }
        .main-content {
            flex: 1;
        }
        .enquiries-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .enquiries-list h3 {
            background: #f8f9fc;
            padding: 15px 20px;
            margin: 0;
            border-bottom: 1px solid #e0e4e8;
            font-size: 16px;
            color: #466da0;
        }
        .enquiry-item {
            padding: 12px 20px;
            border-bottom: 1px solid #edf0f4;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            color: #333;
        }
        .enquiry-item:hover {
            background: #f0f4fa;
        }
        .enquiry-item.active {
            background: #e8f0fe;
            border-right: 4px solid #466da0;
        }
        .enquiry-item .product {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .enquiry-item .date {
            font-size: 11px;
            color: #888;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            margin-top: 5px;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-replied { background: #cce5ff; color: #004085; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            overflow: hidden;
        }
        .card-header {
            background: #f8f9fc;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e4e8;
            font-weight: 600;
            color: #466da0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .card-body {
            padding: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .info-box {
            background: #f9fafc;
            border-radius: 10px;
            padding: 15px;
            border: 1px solid #eef2f6;
        }
        .info-box h4 {
            margin: 0 0 10px;
            color: #466da0;
            font-size: 14px;
            border-right: 3px solid #466da0;
            padding-right: 10px;
        }
        .info-box p {
            margin: 8px 0;
            font-size: 14px;
            word-break: break-word;
        }
        .info-box strong {
            color: #555;
            width: 100px;
            display: inline-block;
        }
        .quantity-badge {
            background: #e8f0fe;
            padding: 10px 15px;
            border-radius: 8px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #466da0;
        }
        .message-thread {
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
            background: #f9fafc;
            border-radius: 10px;
        }
        .message-item {
            background: white;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .message-item .sender {
            font-weight: 600;
            color: #466da0;
            margin-bottom: 5px;
        }
        .message-item .date {
            font-size: 11px;
            color: #aaa;
            float: left;
        }
        .message-item .content {
            margin-top: 8px;
            color: #444;
            line-height: 1.5;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #466da0;
            color: white;
        }
        .btn-primary:hover {
            background: #2c4c7a;
        }
        .btn-success {
            background: #25D366;
            color: white;
        }
        .btn-success:hover {
            background: #1da15a;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid #466da0;
            color: #466da0;
        }
        .form-select, .form-textarea {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-textarea {
            width: 100%;
            resize: vertical;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .whatsapp-link {
            background: #25D366;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        .whatsapp-link:hover {
            background: #1da15a;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #466da0;
            text-decoration: none;
            margin-bottom: 15px;
        }
        hr {
            margin: 20px 0;
            border-color: #eef2f6;
        }
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 إدارة الاستفسارات</h1>
        <p>عرض وإدارة استفسارات الشراء من المشترين</p>
    </div>
    
    <a href="dashboard.php" class="back-link">← العودة إلى لوحة التحكم</a>
    
    <div class="dashboard">
        <!-- Sidebar: قائمة الاستفسارات -->
        <div class="sidebar">
            <div class="enquiries-list">
                <h3>📌 أحدث الاستفسارات</h3>
                <?php if (count($all_enquiries) > 0): ?>
                    <?php foreach ($all_enquiries as $item): ?>
                        <a href="message-view.php?id=<?php echo $item->id; ?>" 
                           class="enquiry-item <?php echo ($enquiry_id == $item->id) ? 'active' : ''; ?>">
                            <div class="product">📦 <?php echo htmlspecialchars(substr($item->product_name, 0, 40)); ?></div>
                            <div class="date"><?php echo date('Y-m-d H:i', strtotime($item->enquiry_date)); ?></div>
                            <span class="status-badge status-<?php echo $item->status; ?>">
                                <?php 
                                    $status_text = ['pending'=>'قيد الانتظار', 'approved'=>'تمت الموافقة', 'replied'=>'تم الرد', 'completed'=>'مكتمل'];
                                    echo $status_text[$item->status] ?? $item->status;
                                ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: #888;">لا توجد استفسارات بعد</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main Content: تفاصيل الاستفسار -->
        <div class="main-content">
            <?php if ($enquiry): ?>
                <!-- بطاقة معلومات الاستفسار -->
                <div class="card">
                    <div class="card-header">
                        <span>📄 تفاصيل الاستفسار #<?php echo $enquiry->id; ?></span>
                        <span class="status-badge status-<?php echo $enquiry->status; ?>">
                            <?php 
                                $status_text = ['pending'=>'قيد الانتظار', 'approved'=>'تمت الموافقة', 'replied'=>'تم الرد', 'completed'=>'مكتمل'];
                                echo $status_text[$enquiry->status] ?? $enquiry->status;
                            ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <!-- معلومات المشتري -->
                            <div class="info-box">
                                <h4>👤 معلومات المشتري</h4>
                                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($enquiry->buyer_fname . ' ' . $enquiry->buyer_lname); ?></p>
                                <p><strong>الشركة:</strong> <?php echo htmlspecialchars($enquiry->buyer_company ?? 'غير محدد'); ?></p>
                                <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($enquiry->buyer_email); ?></p>
                                <p><strong>الجوال:</strong> +<?php echo htmlspecialchars($enquiry->buyer_country_code); ?> <?php echo htmlspecialchars($enquiry->buyer_phone); ?></p>
                                <?php if ($enquiry->buyer_phone): ?>
                                    <a href="https://wa.me/<?php echo $enquiry->buyer_country_code . ltrim($enquiry->buyer_phone, '0'); ?>?text=مرحباً، وصلنا استفساركم بخصوص <?php echo urlencode($enquiry->product_name); ?>" 
                                       target="_blank" class="whatsapp-link" style="margin-top: 10px; display: inline-block;">
                                        <i class="fab fa-whatsapp"></i> تواصل مع المشتري عبر واتساب
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- معلومات المورد -->
                            <div class="info-box">
                                <h4>🏢 معلومات المورد</h4>
                                <p><strong>الاسم:</strong> <?php echo htmlspecialchars($enquiry->supplier_fname . ' ' . $enquiry->supplier_lname); ?></p>
                                <p><strong>الشركة:</strong> <?php echo htmlspecialchars($enquiry->supplier_company ?? 'غير محدد'); ?></p>
                                <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($enquiry->supplier_email); ?></p>
                                <p><strong>الجوال:</strong> +<?php echo htmlspecialchars($enquiry->supplier_country_code); ?> <?php echo htmlspecialchars($enquiry->supplier_phone); ?></p>
                                <?php if ($enquiry->supplier_phone): ?>
                                    <a href="https://wa.me/<?php echo $enquiry->supplier_country_code . ltrim($enquiry->supplier_phone, '0'); ?>?text=مرحباً، لديك استفسار جديد من <?php echo urlencode($enquiry->buyer_company ?: $enquiry->buyer_fname); ?>" 
                                       target="_blank" class="whatsapp-link" style="margin-top: 10px; display: inline-block;">
                                        <i class="fab fa-whatsapp"></i> تواصل مع المورد عبر واتساب
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <!-- معلومات المنتج والكمية -->
                            <div class="info-box">
                                <h4>📦 معلومات المنتج</h4>
                                <p><strong>المنتج:</strong> <?php echo htmlspecialchars($enquiry->product_name); ?></p>
                                <p><strong>الوحدة:</strong> <?php echo htmlspecialchars($enquiry->product_unit); ?></p>
                                <div class="quantity-badge">
                                    📊 الكمية المطلوبة: من <?php echo (int)$enquiry->quantity_from; ?> إلى <?php echo (int)$enquiry->quantity_to; ?> <?php echo htmlspecialchars($enquiry->product_unit); ?>
                                </div>
                            </div>
                            
                            <!-- الرسالة -->
                            <div class="info-box" style="grid-column: span 2;">
                                <h4>💬 نص الاستفسار</h4>
                                <p style="background: #fff; padding: 12px; border-radius: 8px; border-right: 3px solid #466da0;">
                                    <?php echo nl2br(htmlspecialchars($enquiry->message)); ?>
                                </p>
                                <?php if ($enquiry->admin_notes): ?>
                                    <hr>
                                    <h4>📝 ملاحظات الأدمن</h4>
                                    <p style="background: #fff3cd; padding: 10px; border-radius: 8px;">
                                        <?php echo nl2br(htmlspecialchars($enquiry->admin_notes)); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- أزرار الإجراءات -->
                        <div class="action-buttons">
                            <form method="POST" action="message-view.php?id=<?php echo $enquiry_id; ?>&action=update_status" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <input type="hidden" name="status" id="status_input" value="<?php echo $enquiry->status; ?>">
                                <select name="status" class="form-select" onchange="document.getElementById('status_input').value=this.value">
                                    <option value="pending" <?php echo $enquiry->status == 'pending' ? 'selected' : ''; ?>>⏳ قيد الانتظار</option>
                                    <option value="approved" <?php echo $enquiry->status == 'approved' ? 'selected' : ''; ?>>✅ تمت الموافقة</option>
                                    <option value="replied" <?php echo $enquiry->status == 'replied' ? 'selected' : ''; ?>>💬 تم الرد</option>
                                    <option value="completed" <?php echo $enquiry->status == 'completed' ? 'selected' : ''; ?>>✔️ مكتمل</option>
                                </select>
                                <textarea name="admin_notes" class="form-textarea" placeholder="إضافة ملاحظات (اختياري)" style="width: 250px; height: 38px;"><?php echo htmlspecialchars($enquiry->admin_notes ?? ''); ?></textarea>
                                <button type="submit" class="btn btn-primary">💾 تحديث الحالة</button>
                            </form>
                            
                            <a href="reply-enquiry.php?id=<?php echo $enquiry_id; ?>" class="btn btn-primary">✏️ الرد على الاستفسار</a>
                            
                            <?php if ($enquiry->supplier_phone): ?>
                                <?php 
                                    $whatsapp_message = "🛒 *استفسار شراء جديد - رقم #{$enquiry->id}* 🛒\n\n";
                                    $whatsapp_message .= "👤 *المشتري:* " . ($enquiry->buyer_company ?: $enquiry->buyer_fname . ' ' . $enquiry->buyer_lname) . "\n";
                                    $whatsapp_message .= "📦 *المنتج:* " . $enquiry->product_name . "\n";
                                    $whatsapp_message .= "📊 *الكمية:* من {$enquiry->quantity_from} إلى {$enquiry->quantity_to} {$enquiry->product_unit}\n";
                                    $whatsapp_message .= "💬 *الرسالة:* " . substr($enquiry->message, 0, 200) . (strlen($enquiry->message) > 200 ? "..." : "") . "\n\n";
                                    $whatsapp_message .= "🔗 *للمراجعة:* https://egyptmart.shop/admin/message-view.php?id={$enquiry_id}";
                                    $encoded_msg = urlencode($whatsapp_message);
                                    $whatsapp_url = "https://wa.me/" . $enquiry->supplier_country_code . ltrim($enquiry->supplier_phone, '0') . "?text=" . $encoded_msg;
                                ?>
                                <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="btn btn-success">
                                    <i class="fab fa-whatsapp"></i> 📱 إرسال الاستفسار للمورد عبر واتساب
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- تاريخ المحادثة (الرسائل المتبادلة) -->
                <?php if (count($messages) > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <span>💬 تاريخ المحادثة</span>
                    </div>
                    <div class="card-body">
                        <div class="message-thread">
                            <?php foreach ($messages as $msg): ?>
                                <div class="message-item">
                                    <div class="sender">
                                        <?php 
                                            $sender_name = '';
                                            if ($msg->msg_from == $enquiry->buyer_id) {
                                                $sender_name = 'المشتري: ' . ($enquiry->buyer_fname . ' ' . $enquiry->buyer_lname);
                                            } elseif ($msg->msg_from == $enquiry->supplier_id) {
                                                $sender_name = 'المورد: ' . ($enquiry->supplier_fname . ' ' . $enquiry->supplier_lname);
                                            } else {
                                                $sender_name = 'الإدارة: ' . ($msg->from_fname . ' ' . $msg->from_lname);
                                            }
                                        ?>
                                        👤 <?php echo htmlspecialchars($sender_name); ?>
                                        <span class="date"><?php echo date('Y-m-d H:i', strtotime($msg->msg_date)); ?></span>
                                    </div>
                                    <div class="content">
                                        <?php echo nl2br(htmlspecialchars(substr($msg->msg_message, 0, 500))); ?>
                                        <?php if (strlen($msg->msg_message) > 500): ?>
                                            <a href="#" onclick="$(this).prev().show(); $(this).hide(); return false;">... عرض المزيد</a>
                                            <span style="display:none;"><?php echo nl2br(htmlspecialchars(substr($msg->msg_message, 500))); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="card">
                    <div class="card-body" style="text-align: center; padding: 50px;">
                        <p style="color: #888; font-size: 16px;">📭 اختر استفساراً من القائمة الجانبية لعرض تفاصيله</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- إضافة FontAwesome للأيقونات -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>