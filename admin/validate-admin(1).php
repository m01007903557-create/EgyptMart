<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// تسجيل محاولة الدخول
error_log("=== محاولة تسجيل دخول جديدة ===");
error_log("POST data: " . print_r($_POST, true));

// ... باقي الكود ...
/**
 * File: validate-admin.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: معالجة تسجيل دخول المشرفين
 * Admin login processing
 * 
 * Features:
 * - التحقق من صحة بيانات تسجيل الدخول
 * - تشفير كلمة المرور باستخدام MD5 (مع إمكانية الترقية)
 * - تسجيل محاولات الدخول الناجحة
 * - إنشاء جلسة للمشرف
 * - معالجة الأخطاء وإعادة التوجيه
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/admin_login_errors.log');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once "../common.php";

/**
 * Class AdminLoginProcessor
 * 
 * Handles admin login processing
 */
class AdminLoginProcessor {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Database name */
    private string $dbName;
    
    /** @var string Log file path */
    private string $logFile;
    
    /** @var int Error flag */
    private int $errorFlag = 0;
    
    /** @var string Error message */
    private string $errorMessage = '';
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param string $dbName Database name
     */
    public function __construct(mysqli $database, string $dbName) {
        $this->db = $database;
        $this->dbName = $dbName;
        $this->logFile = __DIR__ . '/../../logs/admin_login_attempts.log';
        $this->ensureLogDirectory();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate and sanitize input
     * 
     * @param string|null $username Username
     * @param string|null $password Password
     * @return array{username: string, password: string, encrypted: string} Validated data
     * @throws InvalidArgumentException If validation fails
     */
    public function validateInput(?string $username, ?string $password): array {
        
        // Check if login form was submitted
        if (!isset($_POST['login'])) {
            throw new InvalidArgumentException('Login form not submitted');
        }
        
        // Validate username
        if (empty($username)) {
            $this->errorFlag = 1;
            throw new InvalidArgumentException('Please enter username');
        }
        
        $cleanUsername = trim($username);
        if (strlen($cleanUsername) < 3) {
            $this->errorFlag = 1;
            throw new InvalidArgumentException('Username must be at least 3 characters');
        }
        
        if (strlen($cleanUsername) > 50) {
            $this->errorFlag = 1;
            throw new InvalidArgumentException('Username must not exceed 50 characters');
        }
        
        // Validate password
        if (empty($password)) {
            $this->errorFlag = 1;
            throw new InvalidArgumentException('Please enter password');
        }
        
        $cleanPassword = trim($password);
        
        return [
            'username' => $cleanUsername,
            'password' => $cleanPassword,
            'encrypted' => md5($cleanPassword) // Note: MD5 is considered weak, consider upgrading
        ];
    }
    
    /**
     * Authenticate admin user
     * 
     * @param string $username Username
     * @param string $encryptedPassword Encrypted password
     * @return array|null User data or null if not found
     */
    public function authenticate(string $username, string $encryptedPassword): ?array {
        $sql = "SELECT * FROM admin_user 
                WHERE username = ? AND password = ? AND status = '1' 
                LIMIT 1";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log("Login prepare failed: " . mysqli_error($this->db));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "ss", $username, $encryptedPassword);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) != 1) {
            mysqli_stmt_close($stmt);
            return null;
        }
        
        $userData = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $userData;
    }
    
    /**
     * Log login attempt
     * 
     * @param int $adminId Admin ID
     * @param string $ipAddress IP address
     * @param bool $success Whether login was successful
     */
    public function logLoginAttempt(int $adminId, string $ipAddress, bool $success = true): void {
        if ($success) {
            $this->logSuccessfulLogin($adminId, $ipAddress);
        } else {
            $this->logFailedAttempt($adminId, $ipAddress);
        }
    }
    
    /**
     * Log successful login
     * 
     * @param int $adminId Admin ID
     * @param string $ipAddress IP address
     */
    private function logSuccessfulLogin(int $adminId, string $ipAddress): void {
        $sql = "INSERT INTO admin_login_details 
                SET id = ?, last_login_time = NOW(), user_ip = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "is", $adminId, $ipAddress);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Log to file
        $logEntry = sprintf(
            "[%s] Successful login | Admin ID: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $adminId,
            $ipAddress
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Log failed login attempt
     * 
     * @param int $adminId Admin ID (0 if unknown)
     * @param string $ipAddress IP address
     */
    private function logFailedAttempt(int $adminId, string $ipAddress): void {
        $logEntry = sprintf(
            "[%s] Failed login attempt | Username: %s | IP: %s\n",
            date('Y-m-d H:i:s'),
            $_POST['username'] ?? 'Unknown',
            $ipAddress
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Create admin session
     * 
     * @param string $username Username
     * @param array $userData User data
     */
    public function createSession(string $username, array $userData): void {
        $_SESSION['ad_username_indm'] = $username;
        $_SESSION['ad_email_indm'] = $userData['email'] ?? '';
        $_SESSION['ad_id_indm'] = (int)($userData['id'] ?? 0);
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    /**
     * Get client IP address
     * 
     * @return string IP address
     */
    public function getClientIp(): string {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Check for proxy headers
        $proxyHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];
        
        foreach ($proxyHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ipAddress = $_SERVER[$header];
                break;
            }
        }
        
        return $ipAddress;
    }
    
    /**
     * Set error message in session
     * 
     * @param string $message Error message
     */
    public function setSessionError(string $message): void {
        $_SESSION['err_msg'] = $message;
    }
    
    /**
     * Redirect to login page
     */
    public function redirectToLogin(): void {
        header("Location: index.php");
        exit;
    }
    
    /**
     * Redirect to welcome page
     */
    public function redirectToWelcome(): void {
        header("Location: welcome.php");
        
      // قبل إعادة التوجيه، تأكد من أن الجلسة تم تعيينها
error_log("تم تعيين الجلسة: ad_id_indm = " . ($_SESSION['ad_id_indm'] ?? 'غير موجود'));
error_log("تم تعيين الجلسة: admin_logged_in = " . ($_SESSION['admin_logged_in'] ?? 'غير موجود')
);  
        exit;
    }
    
    /**
     * Get error flag
     * 
     * @return int Error flag
     */
    public function getErrorFlag(): int {
        return $this->errorFlag;
    }
    
    /**
     * Get error message
     * 
     * @return string Error message
     */
    public function getErrorMessage(): string {
        return $this->errorMessage;
    }
}

// Main execution
try {
    // Initialize login processor
    $loginProcessor = new AdminLoginProcessor($con, $db_name ?? '');
    
    // Get input data
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    
    // Validate input
    $validated = $loginProcessor->validateInput($username, $password);
    
    // Authenticate user
    $userData = $loginProcessor->authenticate($validated['username'], $validated['encrypted']);
    
    if (!$userData) {
        // Log failed attempt
        $loginProcessor->logLoginAttempt(0, $loginProcessor->getClientIp(), false);
        
        // Set error message
        $loginProcessor->setSessionError("Username or Password Incorrect");
        $loginProcessor->redirectToLogin();
    }
    
    // Log successful login
    $adminId = (int)($userData['id'] ?? 0);
    $loginProcessor->logLoginAttempt($adminId, $loginProcessor->getClientIp(), true);
    
    // Create session
    $loginProcessor->createSession($validated['username'], $userData);
    
    // Redirect to welcome page
    $loginProcessor->redirectToWelcome();
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Login validation error: " . $e->getMessage());
    
    $loginProcessor = $loginProcessor ?? new AdminLoginProcessor($con, $db_name ?? '');
    $loginProcessor->setSessionError($e->getMessage());
    $loginProcessor->redirectToLogin();
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Login unexpected error: " . $e->getMessage());
    
    $loginProcessor = $loginProcessor ?? new AdminLoginProcessor($con, $db_name ?? '');
    $loginProcessor->setSessionError("An unexpected error occurred");
    $loginProcessor->redirectToLogin();
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>