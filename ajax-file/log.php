<?php
/**
 * File: admin/cmd_exec.php .....log.php

 * Description: واجهة لتنفيذ أوامر النظام (للإدارة فقط - يجب حمايتها بشدة)
 * WARNING: هذا الملف خطير جداً ويجب حمايته بكلمة مرور قوية أو إزالته تماماً
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

// ==================== أمان إضافي - يجب تفعيله ====================
// 1. التحقق من عنوان IP المسموح به
$allowed_ips = ['127.0.0.1', '::1']; // أضف عناوين IP المسموح بها فقط
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Access denied');
}

// 2. التحقق من كلمة مرور بسيطة (يمكنك تغييرها)
session_start();
if (!isset($_SESSION['cmd_auth']) && (!isset($_POST['password']) || $_POST['password'] !== 'Admin@123')) {
    ?>
    <form method="post" action="">
        <input type="password" name="password" placeholder="Enter password" required>
        <input type="submit" value="Login">
    </form>
    <?php
    exit;
} else {
    $_SESSION['cmd_auth'] = true;
}

// 3. تسجيل جميع المحاولات
function log_command_execution(string $command, array $output, string $status = 'SUCCESS'): void {
    $log_file = __DIR__ . '/../logs/cmd_exec.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_entry = sprintf(
        "[%s] IP: %s | User: %s | Status: %s | Command: %s | Output: %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        $_SESSION['uid_indm'] ?? 'GUEST',
        $status,
        $command,
        implode("\n", $output)
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// قائمة بالأوامر المحظورة
$blocked_commands = [
    'rm -rf', 'rmdir', 'del /f', 'format', 'mkfs', 'dd if=', 
    ':(){', 'wget', 'curl', 'nc ', 'netcat', 'chmod 777',
    '> /dev/sda', '> /dev/hda', 'drop database', 'truncate',
    'systemctl stop', 'service stop', 'killall', 'pkill'
];

$a = '';
$res = [];
$error = '';

if (isset($_POST['entrcmd']) && $_POST['entrcmd'] !== '') {
    $a = trim($_POST['entrcmd']);
    
    // التحقق من الأوامر المحظورة
    foreach ($blocked_commands as $blocked) {
        if (stripos($a, $blocked) !== false) {
            $error = "Command contains blocked pattern: $blocked";
            log_command_execution($a, [], 'BLOCKED: ' . $blocked);
            break;
        }
    }
    
    // التحقق من طول الأمر
    if (empty($error) && strlen($a) > 500) {
        $error = "Command too long (max 500 characters)";
        log_command_execution($a, [], 'BLOCKED: Too long');
    }
    
    // تنفيذ الأمر إذا كان آمناً
    if (empty($error)) {
        // تنفيذ الأمر مع الحد من المخاطر
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        $process = proc_open($a, $descriptors, $pipes);
        
        if (is_resource($process)) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            $return_value = proc_close($process);
            
            $res = array_filter(explode("\n", $stdout));
            if (!empty($stderr)) {
                $error = $stderr;
            }
            
            log_command_execution($a, $res, $error ? 'ERROR' : 'SUCCESS');
        } else {
            $error = "Failed to execute command";
            log_command_execution($a, [], 'FAILED');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Command Executor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: 70%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .output {
            background-color: #000;
            color: #0f0;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            overflow-x: auto;
            margin-top: 20px;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .examples {
            margin-top: 20px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .examples code {
            display: block;
            padding: 5px;
            background-color: #fff;
            border: 1px solid #ddd;
            margin: 5px 0;
            cursor: pointer;
        }
        .examples code:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>System Command Executor</h1>
        
        <div class="warning">
            <strong>⚠️ تحذير:</strong> هذه الواجهة مخصصة للإدارة فقط. استخدامها بطريقة خاطئة قد يؤدي إلى تدمير النظام.
        </div>

        <form method="post" action="">
            <input type="text" name="entrcmd" value="<?php echo htmlspecialchars($a, ENT_QUOTES, 'UTF-8'); ?>" 
                   placeholder="Enter command..." required>
            <input type="submit" name="excmd" value="Run Command">
        </form>

        <div class="examples">
            <strong>أمثلة على أوامر آمنة:</strong>
            <code onclick="document.getElementsByName('entrcmd')[0].value='ls -la'">ls -la</code>
            <code onclick="document.getElementsByName('entrcmd')[0].value='pwd'">pwd</code>
            <code onclick="document.getElementsByName('entrcmd')[0].value='php -v'">php -v</code>
            <code onclick="document.getElementsByName('entrcmd')[0].value='whoami'">whoami</code>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($res)): ?>
            <div class="output">
                <strong>Output:</strong>
                <pre><?php echo htmlspecialchars(implode("\n", $res), ENT_QUOTES, 'UTF-8'); ?></pre>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // تأكيد قبل تنفيذ الأمر
        document.querySelector('form').addEventListener('submit', function(e) {
            var cmd = document.getElementsByName('entrcmd')[0].value;
            if (!confirm('Are you sure you want to execute:\n\n' + cmd + '\n\nThis action may be dangerous!')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>