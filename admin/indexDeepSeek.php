<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>تسجيل الدخول - لوحة التحكم</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; }
        .login-box { width: 300px; margin: 100px auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: #4CAF50; color: white; padding: 10px; width: 100%; border: none; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>تسجيل الدخول</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="validate-admin.php">
            <input type="text" name="username" placeholder="اسم المستخدم" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit">دخول</button>
        </form>
    </div>
</body>
</html>