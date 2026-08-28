<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
require_once "../common.php";
require_once "../lib/pagination.php";

// التحقق من تسجيل دخول المستخدم
check_admin_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}





// عدد الاستفسارات الجديدة
$sql_pending = "SELECT COUNT(*) as count FROM buy_enquiries WHERE status = 'pending'";
$result_pending = mysqli_query($con, $sql_pending);
$stats['pending'] = mysqli_fetch_object($result_pending)->count ?? 0;

// عدد المنتجات
$sql_products = "SELECT COUNT(*) as count FROM products WHERE pd_status = '1'";
$result_products = mysqli_query($con, $sql_products);
$stats['products'] = mysqli_fetch_object($result_products)->count ?? 0;

// عدد المستخدمين
$sql_users = "SELECT COUNT(*) as count FROM user WHERE usr_status = '1'";
$result_users = mysqli_query($con, $sql_users);
$stats['users'] = mysqli_fetch_object($result_users)->count ?? 0;
?>
<!DOCTYPE html>
<html dir="ltr" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - EgyptMART Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #466da0, #2c4c7a);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header h1 { margin: 0 0 8px; font-size: 28px; }
        .header p { margin: 0; opacity: 0.9; }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #466da0;
            margin-bottom: 10px;
        }
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        .stat-card.pending .number { color: #ff9800; }
        .stat-card.products .number { color: #4caf50; }
        .stat-card.users .number { color: #2196f3; }
        
        /* Menu Cards */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .menu-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: block;
            text-align: center;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .menu-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .menu-card h3 {
            margin-bottom: 10px;
            color: #466da0;
        }
        .menu-card p {
            color: #666;
            font-size: 13px;
        }
        
        /* Logout Button */
        .logout-area {
            margin-top: 30px;
            text-align: center;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            transition: background 0.3s;
        }
        .logout-btn:hover { background: #c82333; }
        
        @media (max-width: 600px) {
            .header h1 { font-size: 22px; }
            .stat-card .number { font-size: 28px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🏠 لوحة تحكم المسؤول</h1>
        <p>مرحباً بك في لوحة إدارة منصة EgyptMART</p>
    </div>
    
    <!-- إحصائيات سريعة -->
    <div class="stats-grid">
        <div class="stat-card pending">
            <div class="number"><?php echo $stats['pending']; ?></div>
            <div class="label">📋 استفسارات جديدة (قيد الانتظار)</div>
        </div>
        <div class="stat-card products">
            <div class="number"><?php echo $stats['products']; ?></div>
            <div class="label">📦 منتجات نشطة</div>
        </div>
        <div class="stat-card users">
            <div class="number"><?php echo $stats['users']; ?></div>
            <div class="label">👥 مستخدمين نشطين</div>
        </div>
    </div>
    
    <!-- قائمة المهام -->
    <div class="menu-grid">
        <a href="message-view.php" class="menu-card">
            <div class="icon">📋</div>
            <h3>إدارة الاستفسارات</h3>
            <p>عرض والرد على استفسارات الشراء من العملاء</p>
        </a>
        
        <a href="../products-list.php" class="menu-card">
            <div class="icon">📦</div>
            <h3>إدارة المنتجات</h3>
            <p>مراجعة وتعديل قائمة المنتجات</p>
        </a>
        
        <a href="../all-users.php" class="menu-card">
            <div class="icon">👥</div>
            <h3>إدارة المستخدمين</h3>
            <p>عرض وإدارة حسابات العملاء والموردين</p>
        </a>
    </div>
    
    <div class="logout-area">
        <a href="../sign-out.php" class="logout-btn">🚪 تسجيل الخروج</a>
    </div>
</div>
</body>
</html>