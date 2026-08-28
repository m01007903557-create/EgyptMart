<?php
/**
 * File: states_edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث اسم ولاية/محافظة موجودة
 * Update an existing state/province name
 * 
 * Features:
 * - تحديث اسم الولاية
 * - التحقق من صحة المدخلات
 * - التحقق من عدم وجود تكرار
 * - معالجة الأخطاء وتسجيلها
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/states_errors.log');

// Include database connection
require_once "../common.php";

// Check if user is logged in (optional - uncomment if needed)
// check_user_login();

/**
 * Class StateEditor
 * 
 * Handles updating state information
 */
class StateEditor {
    
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
        $this->logFile = __DIR__ . '/../../logs/states_edits.log';
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
     * @param string|null $hid State ID
     * @param string|null $stateName New state name
     * @return array{id: int, name: string} Validated data
     * @throws InvalidArgumentException If validation fails
     */
    public function validateInput(?string $hid, ?string $stateName): array {
        // Check if parameters exist
        if (!isset($hid) || !isset($stateName)) {
            throw new InvalidArgumentException('Missing required parameters');
        }
        
        // Validate state ID
        $cleanId = filter_var(trim($hid), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('Invalid state ID');
        }
        
        // Validate state name
        $cleanName = trim($stateName);
        if (empty($cleanName)) {
            throw new InvalidArgumentException('State name cannot be empty');
        }
        
        if (strlen($cleanName) < 2) {
            throw new InvalidArgumentException('State name must be at least 2 characters');
        }
        
        if (strlen($cleanName) > 100) {
            throw new InvalidArgumentException('State name must not exceed 100 characters');
        }
        
        return [
            'id' => $cleanId,
            'name' => $cleanName
        ];
    }
    
    /**
     * Get current state details
     * 
     * @param int $stateId State ID
     * @return array|null State details
     */
    public function getStateDetails(int $stateId): ?array {
        $sql = "SELECT st_name, st_cn_id FROM states WHERE st_id = ? AND st_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $stateId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return [
                'name' => $row['st_name'],
                'country_id' => (int)$row['st_cn_id']
            ];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Check if state exists
     * 
     * @param int $stateId State ID
     * @return bool True if exists
     */
    public function stateExists(int $stateId): bool {
        $sql = "SELECT COUNT(*) as count FROM states WHERE st_id = ? AND st_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $stateId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Check if state name already exists in the same country (excluding current)
     * 
     * @param int $stateId State ID
     * @param int $countryId Country ID
     * @param string $newName New state name
     * @return bool True if duplicate exists
     */
    public function isDuplicate(int $stateId, int $countryId, string $newName): bool {
        $sql = "SELECT COUNT(*) as count FROM states 
                WHERE st_cn_id = ? AND st_name = ? AND st_id != ? AND st_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "isi", $countryId, $newName, $stateId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Update state name
     * 
     * @param int $stateId State ID
     * @param string $newName New state name
     * @return bool Success status
     * @throws RuntimeException If database operation fails
     */
    public function updateState(int $stateId, string $newName): bool {
        // Check if state exists
        if (!$this->stateExists($stateId)) {
            throw new RuntimeException('State not found');
        }
        
        // Get current state details for duplicate check
        $currentDetails = $this->getStateDetails($stateId);
        if (!$currentDetails) {
            throw new RuntimeException('Could not retrieve state details');
        }
        
        // Check for duplicate name in the same country
        if ($this->isDuplicate($stateId, $currentDetails['country_id'], $newName)) {
            throw new RuntimeException('A state with this name already exists in the same country');
        }
        
        $sql = "UPDATE states SET st_name = ? WHERE st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "si", $newName, $stateId);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to update state: ' . $error);
        }
        
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $affectedRows > 0;
    }
    
    /**
     * Get country name by state ID
     * 
     * @param int $stateId State ID
     * @return string|null Country name
     */
    public function getCountryName(int $stateId): ?string {
        $sql = "SELECT c.cn_name 
                FROM states s
                JOIN country c ON s.st_cn_id = c.cn_id
                WHERE s.st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $stateId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['cn_name'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Log the edit
     * 
     * @param int $stateId State ID
     * @param string $oldName Old state name
     * @param string $newName New state name
     */
    public function logEdit(int $stateId, string $oldName, string $newName): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $countryName = $this->getCountryName($stateId) ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] State Edited | State ID: %d | Country: %s | Old Name: %s | New Name: %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $stateId,
            $countryName,
            $oldName,
            $newName,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send response
     * 
     * @param string $newName New state name
     */
    public function sendResponse(string $newName): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo htmlspecialchars($newName, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    public function sendError(string $message, int $statusCode = 400): void {
        http_response_code($statusCode);
        header('Content-Type: text/plain; charset=utf-8');
        echo '0';
        error_log("State edit error: " . $message);
    }
}

// Main execution
try {
    // Initialize state editor
    $stateEditor = new StateEditor($con);
    
    // Get old name for logging
    $oldDetails = $stateEditor->getStateDetails((int)($_GET['hid'] ?? 0));
    
    // Validate input
    $validated = $stateEditor->validateInput($_GET['hid'] ?? null, $_GET['states_inp'] ?? null);
    
    // Update state
    $updated = $stateEditor->updateState($validated['id'], $validated['name']);
    
    if (!$updated) {
        throw new RuntimeException('No changes made to state');
    }
    
    // Log the edit if we have old name
    if ($oldDetails) {
        $stateEditor->logEdit($validated['id'], $oldDetails['name'], $validated['name']);
    }
    
    // Send success response (return new name)
    $stateEditor->sendResponse($validated['name']);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("State edit validation error: " . $e->getMessage());
    $stateEditor = $stateEditor ?? new StateEditor($con);
    $stateEditor->sendError($e->getMessage());
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("State edit runtime error: " . $e->getMessage());
    $stateEditor = $stateEditor ?? new StateEditor($con);
    $stateEditor->sendError($e->getMessage(), 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("State edit unexpected error: " . $e->getMessage());
    $stateEditor = $stateEditor ?? new StateEditor($con);
    $stateEditor->sendError('An unexpected error occurred', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>