<?php
/**
 * File Name: mysql.php
 * PHP Version: 8.3
 * Description: صفحة تثبيت وإعداد اتصال قاعدة البيانات - نسخة مطورة ومتوافقة مع PHP 8.3
 * 
 * تقوم هذه الصفحة بإنشاء ملف التكوين config.php بناءً على بيانات الاتصال المدخلة
 * وتختبر الاتصال بقاعدة البيانات قبل إنشاء الملف
 */

declare(strict_types=1);

// تمكين عرض الأخطاء للتثبيت فقط (يمكن تعطيلها بعد التثبيت)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// تحديد مسار ملف التكوين
$configFile = __DIR__ . '/config.php';

// التحقق من وجود ملف التكوين مسبقاً
if (file_exists($configFile)) {
    $message = '<div style="color: #856404; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 12px; border-radius: 4px; margin: 20px auto; max-width: 600px;">
        <strong>تنبيه:</strong> ملف التكوين موجود مسبقاً. إذا أردت إعادة التثبيت، قم بحذف ملف config.php أولاً.
        </div>';
    $showForm = false;
} else {
    $showForm = true;
}

// معالجة إرسال النموذج
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    // تنظيف المدخلات
    $configType = $_POST['config'] ?? 'auto';
    $host = trim($_POST['host'] ?? '');
    $database = trim($_POST['database'] ?? '');
    $user = trim($_POST['user'] ?? '');
    $password = $_POST['pwd'] ?? '';
    $port = isset($_POST['port']) && $_POST['port'] !== '' ? (int)$_POST['port'] : 3306;

    // التحقق من المدخلات الأساسية
    $errors = [];
    
    if (empty($host)) {
        $errors[] = 'يرجى إدخال اسم المضيف (Host Name)';
    }
    
    if (empty($database)) {
        $errors[] = 'يرجى إدخال اسم قاعدة البيانات (Database Name)';
    }
    
    if (empty($user)) {
        $errors[] = 'يرجى إدخال اسم المستخدم (User)';
    }
    
    if ($port < 1 || $port > 65535) {
        $errors[] = 'رقم المنفذ يجب أن يكون بين 1 و 65535';
    }

    // إذا لم تكن هناك أخطاء، نحاول الاتصال بقاعدة البيانات
    if (empty($errors)) {
        // محاولة الاتصال باستخدام MySQLi
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            // إنشاء اتصال تجريبي
            $testConn = mysqli_connect($host, $user, $password, $database, $port);
            
            if ($testConn) {
                // التحقق من وجود الجداول الأساسية (اختياري)
                $result = mysqli_query($testConn, "SHOW TABLES");
                $tablesExist = mysqli_num_rows($result) > 0;
                
                mysqli_close($testConn);
                
                // إنشاء ملف التكوين
                $configContent = "<?php
/**
 * File Name: config.php
 * PHP Version: 8.3
 * Description: ملف تكوين اتصال قاعدة البيانات - تم إنشاؤه تلقائياً في " . date('Y-m-d H:i:s') . "
 */

declare(strict_types=1);

// إعدادات اتصال قاعدة البيانات
define('DB_HOST', '" . addslashes($host) . "');
define('DB_USER', '" . addslashes($user) . "');
define('DB_PASS', '" . addslashes($password) . "');
define('DB_NAME', '" . addslashes($database) . "');
define('DB_PORT', " . $port . ");
define('DB_CHARSET', 'utf8mb4');

// إعدادات الاتصال حسب النوع المختار
define('DB_TYPE', '" . addslashes($configType) . "');

// إنشاء اتصال قاعدة البيانات
function getDbConnection() {
    \$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if (\$db->connect_error) {
        die('فشل الاتصال بقاعدة البيانات: ' . \$db->connect_error);
    }
    
    \$db->set_charset(DB_CHARSET);
    return \$db;
}

// متغير عام للاتصال (للتطبيقات القديمة)
\$con = getDbConnection();
?>";

                if (file_put_contents($configFile, $configContent)) {
                    $success = true;
                    $message = '<div style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 12px; border-radius: 4px; margin: 20px auto; max-width: 600px;">
                        <strong>تم بنجاح!</strong> تم إنشاء ملف التكوين بنجاح واختبار الاتصال بقاعدة البيانات.
                        <br><br>
                        <a href="index.php" style="color: #155724; font-weight: bold;">انتقل إلى الصفحة الرئيسية</a>
                        </div>';
                    $showForm = false;
                } else {
                    $errors[] = 'فشل في إنشاء ملف التكوين. تأكد من صلاحيات الكتابة للمجلد الحالي.';
                }
            }
        } catch (mysqli_sql_exception $e) {
            $errors[] = 'فشل الاتصال بقاعدة البيانات: ' . $e->getMessage();
        }
    }

    // عرض الأخطاء إذا وجدت
    if (!empty($errors)) {
        $message = '<div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 12px; border-radius: 4px; margin: 20px auto; max-width: 600px;">
            <strong>خطأ:</strong> ' . implode('<br>', $errors) . '
            </div>';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت النظام - إعداد اتصال قاعدة البيانات</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }
        
        .installer-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        input[type="number"] {
            -moz-appearance: textfield;
        }
        
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .info-text {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .requirements {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        
        .requirements h3 {
            margin: 0 0 10px;
            color: #333;
            font-size: 14px;
        }
        
        .requirements ul {
            margin: 0;
            padding-right: 20px;
            color: #666;
        }
        
        .requirements li {
            margin-bottom: 5px;
        }
        
        .requirements .ok {
            color: #28a745;
        }
        
        .requirements .not-ok {
            color: #dc3545;
        }
        
        .message {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="installer-box">
            <div class="header">
                <h1>🔧 تثبيت النظام</h1>
                <p>إعداد اتصال قاعدة البيانات</p>
            </div>
            
            <div class="content">
                <?php echo $message; ?>
                
                <?php if (!$showForm): ?>
                    <div style="text-align: center;">
                        <a href="index.php" style="display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; padding: 12px 30px; border-radius: 6px; font-weight: 600;">انتقل إلى الصفحة الرئيسية</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($showForm): ?>
                <!-- التحقق من متطلبات النظام -->
                <div class="requirements">
                    <h3>📋 متطلبات النظام:</h3>
                    <ul>
                        <?php
                        $phpVersion = phpversion();
                        $phpOk = version_compare($phpVersion, '8.0.0', '>=');
                        $mysqliOk = extension_loaded('mysqli');
                        $pdoOk = extension_loaded('pdo_mysql');
                        ?>
                        <li class="<?php echo $phpOk ? 'ok' : 'not-ok'; ?>">
                            PHP الإصدار <?php echo $phpVersion; ?> <?php echo $phpOk ? '✓' : '✗'; ?> (مطلوب PHP 8.0 أو أحدث)
                        </li>
                        <li class="<?php echo $mysqliOk ? 'ok' : 'not-ok'; ?>">
                            MySQLi extension <?php echo $mysqliOk ? '✓' : '✗'; ?> (مطلوب)
                        </li>
                        <li class="<?php echo $pdoOk ? 'ok' : 'not-ok'; ?>">
                            PDO MySQL extension <?php echo $pdoOk ? '✓' : '✗'; ?> (اختياري - يوصى به)
                        </li>
                        <li class="<?php echo is_writable(__DIR__) ? 'ok' : 'not-ok'; ?>">
                            صلاحيات الكتابة للمجلد الحالي <?php echo is_writable(__DIR__) ? '✓' : '✗'; ?> (مطلوب لإنشاء ملف config.php)
                        </li>
                    </ul>
                </div>
                
                <form method="post" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="config">🔌 نوع الاتصال:</label>
                        <select name="config" id="config">
                            <option value="auto" selected>اختيار تلقائي</option>
                            <option value="mysqli">MySQLi (موصى به)</option>
                            <option value="mysql">MySQL (قديم)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="host">🌐 اسم المضيف:</label>
                        <input type="text" name="host" id="host" placeholder="مثال: localhost" autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="database">📁 اسم قاعدة البيانات:</label>
                        <input type="text" name="database" id="database" placeholder="أدخل اسم قاعدة البيانات">
                    </div>
                    
                    <div class="form-group">
                        <label for="user">👤 اسم المستخدم:</label>
                        <input type="text" name="user" id="user" placeholder="أدخل اسم المستخدم">
                    </div>
                    
                    <div class="form-group">
                        <label for="pwd">🔑 كلمة المرور:</label>
                        <input type="password" name="pwd" id="pwd" placeholder="أدخل كلمة المرور">
                    </div>
                    
                    <div class="form-group">
                        <label for="port">🔢 المنفذ (اختياري):</label>
                        <input type="number" name="port" id="port" min="1" max="65535" value="3306">
                    </div>
                    
                    <button type="submit" class="btn-submit">🔌 اختبار الاتصال والتثبيت</button>
                </form>
                
                <div class="info-text">
                    <p>سيتم إنشاء ملف config.php في المجلد الحالي. تأكد من صلاحيات الكتابة.</p>
                    <p>بعد التثبيت الناجح، يمكنك حذف ملف install.php للأمان.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    function validateForm() {
        var host = document.getElementById('host').value.trim();
        var database = document.getElementById('database').value.trim();
        var user = document.getElementById('user').value.trim();
        var port = document.getElementById('port').value;
        
        if (host === '') {
            alert('يرجى إدخال اسم المضيف (Host Name)');
            document.getElementById('host').focus();
            return false;
        }
        
        if (database === '') {
            alert('يرجى إدخال اسم قاعدة البيانات (Database Name)');
            document.getElementById('database').focus();
            return false;
        }
        
        if (user === '') {
            alert('يرجى إدخال اسم المستخدم (User)');
            document.getElementById('user').focus();
            return false;
        }
        
        if (port < 1 || port > 65535) {
            alert('رقم المنفذ يجب أن يكون بين 1 و 65535');
            document.getElementById('port').focus();
            return false;
        }
        
        return true;
    }
    
    // إضافة مستمع لأحداث الإدخال لتحسين تجربة المستخدم
    document.addEventListener('DOMContentLoaded', function() {
        var inputs = document.querySelectorAll('input, select');
        inputs.forEach(function(input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            });
        });
    });
    </script>
</body>
</html>