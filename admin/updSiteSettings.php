<?php
/**
 * File: updSiteSettings.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث قيمة إعدادات الموقع (AJAX)
 * Update site setting value (AJAX)
 * 
 * Features:
 * - تحديث قيمة إعداد محدد
 * - تحديث تاريخ التعديل
 * - التحقق من صحة المدخلات
 * - معالجة الأخطاء وتسجيلها
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/site_settings_errors.log');

// Include database connection
require_once "../common.php";

/**
 * Class SiteSettingUpdater
 * 
 * Handles updating site settings via AJAX
 */
class SiteSettingUpdater {
    
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
        $this->logFile = __DIR__ . '/../../logs/site_settings_updates.log';
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
     * @param mixed $id Raw setting ID
     * @param mixed $value Raw setting value
     * @return array{id: int, value: string} Validated data
     * @throws InvalidArgumentException If validation fails
     */
    public function validateInput($id, $value): array {
        
        // Validate setting ID
        if (!isset($id) || $id === '') {
            throw new InvalidArgumentException('Setting ID is required');
        }
        
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('Invalid setting ID');
        }
        
        // Validate value
        if (!isset($value)) {
            throw new InvalidArgumentException('Setting value is required');
        }
        
        // Convert to string and trim
        $cleanValue = trim((string)$value);
        
        // Value can be empty for some settings? Check if needed
        
        return [
            'id' => $cleanId,
            'value' => $cleanValue
        ];
    }
    
    /**
     * Check if setting exists
     * 
     * @param int $settingId Setting ID
     * @return bool True if exists
     */
    public function settingExists(int $settingId): bool {
        $sql = "SELECT COUNT(*) as count FROM site_settings WHERE st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $settingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Get setting details before update (for logging)
     * 
     * @param int $settingId Setting ID
     * @return array|null Setting details
     */
    public function getSettingDetails(int $settingId): ?array {
        $sql = "SELECT st_field, st_value FROM site_settings WHERE st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $settingId);
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
     * Update setting value
     * 
     * @param int $settingId Setting ID
     * @param string $value New value
     * @return bool Success status
     * @throws RuntimeException If database operation fails
     */
    public function updateSetting(int $settingId, string $value): bool {
        
        // Check if setting exists
        if (!$this->settingExists($settingId)) {
            throw new RuntimeException('Setting not found');
        }
        
        $sql = "UPDATE site_settings SET
                st_value = ?,
                st_updated_date = NOW()
                WHERE st_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "si", $value, $settingId);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to update setting: ' . $error);
        }
        
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $affectedRows >= 0;
    }
    
    /**
     * Log the update
     * 
     * @param int $settingId Setting ID
     * @param string $oldValue Old value
     * @param string $newValue New value
     */
    public function logUpdate(int $settingId, ?string $oldValue, string $newValue): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $details = $this->getSettingDetails($settingId);
        $fieldName = $details['st_field'] ?? "ID:{$settingId}";
        
        // Truncate long values for logging
        $oldDisplay = !empty($oldValue) ? (strlen($oldValue) > 50 ? substr($oldValue, 0, 50) . '...' : $oldValue) : 'EMPTY';
        $newDisplay = !empty($newValue) ? (strlen($newValue) > 50 ? substr($newValue, 0, 50) . '...' : $newValue) : 'EMPTY';
        
        $logEntry = sprintf(
            "[%s] Site Setting Updated | ID: %d | Field: %s | Old: %s | New: %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $settingId,
            $fieldName,
            $oldDisplay,
            $newDisplay,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send success response
     */
    public function sendResponse(): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Setting updated successfully'
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
        
        error_log("Site setting update error: " . $message);
    }
}

// Main execution
try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed. Use POST.', 405);
    }
    
    // Initialize updater
    $updater = new SiteSettingUpdater($con);
    
    // Validate input
    $validated = $updater->validateInput(
        $_POST['id'] ?? null,
        $_POST['val'] ?? null
    );
    
    // Get old value for logging
    $oldDetails = $updater->getSettingDetails($validated['id']);
    $oldValue = $oldDetails['st_value'] ?? null;
    
    // Update setting
    $updated = $updater->updateSetting($validated['id'], $validated['value']);
    
    if (!$updated) {
        throw new RuntimeException('No changes made to setting');
    }
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldValue, $validated['value']);
    
    // Send success response
    $updater->sendResponse();
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Site setting validation error: " . $e->getMessage());
    $updater = $updater ?? new SiteSettingUpdater($con);
    $updater->sendError($e->getMessage());
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Site setting runtime error: " . $e->getMessage());
    $updater = $updater ?? new SiteSettingUpdater($con);
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
    
    $updater->sendError($e->getMessage(), 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Site setting unexpected error: " . $e->getMessage());
    $updater = $updater ?? new SiteSettingUpdater($con);
    $updater->sendError('An unexpected error occurred', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>