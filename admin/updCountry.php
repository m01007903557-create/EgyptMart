<?php
/**
 * File: updCountry.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث معلومات الدولة (الاسم، الكود، العملة، رمز الهاتف)
 * Update country information (name, code, currency, phone code)
 * 
 * Features:
 * - تحديث جميع معلومات الدولة
 * - تحويل الكود والعملة إلى أحرف كبيرة
 * - التحقق من صحة المدخلات
 * - معالجة الأخطاء وتسجيلها
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/country_errors.log');

// Include database connection
require_once "../common.php";

/**
 * Class CountryUpdater
 * 
 * Handles country information updates
 */
class CountryUpdater {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Log file path */
    private string $logFile;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/country_updates.log';
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
     * Validate and sanitize input data
     * 
     * @param mixed $cn_id Country ID
     * @param mixed $cn_name Country name
     * @param mixed $cn_code Country code
     * @param mixed $cn_currency Currency code
     * @param mixed $cn_phone Phone code
     * @return array{id: int, name: string, code: string, currency: string, phone: string} Validated data
     * @throws InvalidArgumentException If validation fails
     */
    public function validateInput(
        $cn_id,
        $cn_name,
        $cn_code,
        $cn_currency,
        $cn_phone
    ): array {
        
        // Validate country ID
        $cleanId = filter_var(trim((string)$cn_id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('Invalid country ID');
        }
        
        // Validate country name
        $cleanName = trim((string)$cn_name);
        if (empty($cleanName)) {
            throw new InvalidArgumentException('Country name cannot be empty');
        }
        
        if (strlen($cleanName) < 2) {
            throw new InvalidArgumentException('Country name must be at least 2 characters');
        }
        
        if (strlen($cleanName) > 100) {
            throw new InvalidArgumentException('Country name must not exceed 100 characters');
        }
        
        // Validate country code
        $cleanCode = trim((string)$cn_code);
        if (empty($cleanCode)) {
            throw new InvalidArgumentException('Country code cannot be empty');
        }
        
        if (strlen($cleanCode) < 2 || strlen($cleanCode) > 3) {
            throw new InvalidArgumentException('Country code must be 2-3 characters');
        }
        
        if (!ctype_alpha($cleanCode)) {
            throw new InvalidArgumentException('Country code must contain only letters');
        }
        
        // Validate currency code
        $cleanCurrency = trim((string)$cn_currency);
        if (empty($cleanCurrency)) {
            throw new InvalidArgumentException('Currency code cannot be empty');
        }
        
        if (strlen($cleanCurrency) < 2 || strlen($cleanCurrency) > 4) {
            throw new InvalidArgumentException('Currency code must be 2-4 characters');
        }
        
        // Validate phone code
        $cleanPhone = trim((string)$cn_phone);
        if (empty($cleanPhone)) {
            throw new InvalidArgumentException('Phone code cannot be empty');
        }
        
        // Phone code should start with + and contain numbers
        $phonePattern = '/^\+?[0-9]{1,4}$/';
        if (!preg_match($phonePattern, $cleanPhone)) {
            throw new InvalidArgumentException('Phone code must be like +20 or 20 (1-4 digits)');
        }
        
        // Ensure + prefix for consistency
        if ($cleanPhone[0] !== '+') {
            $cleanPhone = '+' . $cleanPhone;
        }
        
        return [
            'id' => $cleanId,
            'name' => $cleanName,
            'code' => strtoupper($cleanCode),
            'currency' => strtoupper($cleanCurrency),
            'phone' => $cleanPhone
        ];
    }
    
    /**
     * Check if country exists
     * 
     * @param int $countryId Country ID
     * @return bool True if exists
     */
    public function countryExists(int $countryId): bool {
        $sql = "SELECT COUNT(*) as count FROM country WHERE cn_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $countryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Check for duplicate country code (excluding current)
     * 
     * @param int $countryId Country ID
     * @param string $countryCode Country code
     * @return bool True if duplicate exists
     */
    public function isDuplicateCode(int $countryId, string $countryCode): bool {
        $sql = "SELECT COUNT(*) as count FROM country WHERE cn_code = ? AND cn_id != ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $countryCode, $countryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Get country details before update (for logging)
     * 
     * @param int $countryId Country ID
     * @return array|null Country details
     */
    public function getCountryDetails(int $countryId): ?array {
        $sql = "SELECT cn_name, cn_code, cn_currency, cn_ph FROM country WHERE cn_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $countryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Update country information
     * 
     * @param int $countryId Country ID
     * @param string $name Country name
     * @param string $code Country code
     * @param string $currency Currency code
     * @param string $phone Phone code
     * @return bool Success status
     * @throws RuntimeException If database operation fails
     */
    public function updateCountry(
        int $countryId,
        string $name,
        string $code,
        string $currency,
        string $phone
    ): bool {
        
        // Check if country exists
        if (!$this->countryExists($countryId)) {
            throw new RuntimeException('Country not found');
        }
        
        // Check for duplicate country code
        if ($this->isDuplicateCode($countryId, $code)) {
            throw new RuntimeException('Country code already exists for another country');
        }
        
        $sql = "UPDATE country SET
                cn_name = ?,
                cn_code = ?,
                cn_currency = ?,
                cn_ph = ?
                WHERE cn_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $code, $currency, $phone, $countryId);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to update country: ' . $error);
        }
        
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $affectedRows >= 0;
    }
    
    /**
     * Log the update
     * 
     * @param int $countryId Country ID
     * @param array $oldDetails Old country details
     * @param array $newDetails New country details
     */
    public function logUpdate(int $countryId, array $oldDetails, array $newDetails): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] Country Updated | ID: %d | Name: %s -> %s | Code: %s -> %s | Currency: %s -> %s | Phone: %s -> %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $countryId,
            $oldDetails['cn_name'] ?? 'Unknown',
            $newDetails['name'],
            $oldDetails['cn_code'] ?? 'Unknown',
            $newDetails['code'],
            $oldDetails['cn_currency'] ?? 'Unknown',
            $newDetails['currency'],
            $oldDetails['cn_ph'] ?? 'Unknown',
            $newDetails['phone'],
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send response
     * 
     * @param array $data Response data
     */
    public function sendResponse(array $data = []): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Country updated successfully',
            'data' => $data
        ]);
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    public function sendError(string $message, int $statusCode = 400): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        
        error_log("Country update error: " . $message);
    }
}

// Main execution
try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed. Use POST.', 405);
    }
    
    // Initialize updater
    $updater = new CountryUpdater($con);
    
    // Validate input
    $validated = $updater->validateInput(
        $_POST['cn_id'] ?? null,
        $_POST['cn_name'] ?? null,
        $_POST['cn_code'] ?? null,
        $_POST['cn_currency'] ?? null,
        $_POST['cn_ph'] ?? null
    );
    
    // Get old details for logging
    $oldDetails = $updater->getCountryDetails($validated['id']);
    
    // Update country
    $updated = $updater->updateCountry(
        $validated['id'],
        $validated['name'],
        $validated['code'],
        $validated['currency'],
        $validated['phone']
    );
    
    if (!$updated) {
        throw new RuntimeException('No changes made to country');
    }
    
    // Log the update
    if ($oldDetails) {
        $updater->logUpdate($validated['id'], $oldDetails, $validated);
    }
    
    // Send success response
    $updater->sendResponse([
        'country_id' => $validated['id'],
        'country_name' => $validated['name'],
        'country_code' => $validated['code'],
        'currency' => $validated['currency'],
        'phone_code' => $validated['phone']
    ]);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Country update validation error: " . $e->getMessage());
    $updater = $updater ?? new CountryUpdater($con);
    $updater->sendError($e->getMessage());
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Country update runtime error: " . $e->getMessage());
    $updater = $updater ?? new CountryUpdater($con);
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
    
    $updater->sendError($e->getMessage(), 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Country update unexpected error: " . $e->getMessage());
    $updater = $updater ?? new CountryUpdater($con);
    $updater->sendError('An unexpected error occurred', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>