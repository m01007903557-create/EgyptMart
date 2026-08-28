<?php
/**
 * File: states_add.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة ولاية/محافظة جديدة لدولة محددة
 * Add a new state/province to a specific country
 * 
 * Features:
 * - إضافة ولاية جديدة
 * - ربط الولاية بالدولة المحددة
 * - التحقق من صحة المدخلات
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
 * Class StateAdder
 * 
 * Handles adding new states to the database
 */
class StateAdder {
    
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
        $this->logFile = __DIR__ . '/../../logs/states_additions.log';
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
     * @param string|null $stateName State name
     * @param string|null $countryId Country ID
     * @return array{stateName: string, countryId: int} Validated data
     * @throws InvalidArgumentException If validation fails
     */
    public function validateInput(?string $stateName, ?string $countryId): array {
        // Check if parameters exist
        if (!isset($stateName) || !isset($countryId)) {
            throw new InvalidArgumentException('Missing required parameters');
        }
        
        // Validate state name
        $cleanState = trim($stateName);
        if (empty($cleanState)) {
            throw new InvalidArgumentException('State name cannot be empty');
        }
        
        if (strlen($cleanState) < 2) {
            throw new InvalidArgumentException('State name must be at least 2 characters');
        }
        
        if (strlen($cleanState) > 100) {
            throw new InvalidArgumentException('State name must not exceed 100 characters');
        }
        
        // Validate country ID
        $cleanCountry = filter_var(trim($countryId), FILTER_VALIDATE_INT);
        if ($cleanCountry === false || $cleanCountry <= 0) {
            throw new InvalidArgumentException('Invalid country ID');
        }
        
        return [
            'stateName' => $cleanState,
            'countryId' => $cleanCountry
        ];
    }
    
    /**
     * Check if country exists
     * 
     * @param int $countryId Country ID
     * @return bool True if exists
     */
    public function countryExists(int $countryId): bool {
        $sql = "SELECT COUNT(*) as count FROM country WHERE cn_id = ? AND cn_status = 1";
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
     * Check if state already exists for this country
     * 
     * @param int $countryId Country ID
     * @param string $stateName State name
     * @return bool True if exists
     */
    public function stateExists(int $countryId, string $stateName): bool {
        $sql = "SELECT COUNT(*) as count FROM states WHERE st_cn_id = ? AND st_name = ? AND st_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "is", $countryId, $stateName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Get country name
     * 
     * @param int $countryId Country ID
     * @return string|null Country name
     */
    public function getCountryName(int $countryId): ?string {
        $sql = "SELECT cn_name FROM country WHERE cn_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $countryId);
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
     * Add state to database
     * 
     * @param int $countryId Country ID
     * @param string $stateName State name
     * @return bool Success status
     * @throws RuntimeException If database operation fails
     */
    public function addState(int $countryId, string $stateName): bool {
        // Check if state already exists
        if ($this->stateExists($countryId, $stateName)) {
            throw new RuntimeException('State already exists for this country');
        }
        
        $sql = "INSERT INTO states SET st_cn_id = ?, st_name = ?, st_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "is", $countryId, $stateName);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to insert state: ' . $error);
        }
        
        $insertId = mysqli_stmt_insert_id($stmt);
        mysqli_stmt_close($stmt);
        
        return $insertId > 0;
    }
    
    /**
     * Log the addition
     * 
     * @param int $countryId Country ID
     * @param string $stateName State name
     */
    public function logAddition(int $countryId, string $stateName): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $countryName = $this->getCountryName($countryId) ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] State Added | Country ID: %d | Country: %s | State: %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $countryId,
            $countryName,
            $stateName,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send response
     * 
     * @param int $countryId Country ID (to refresh states list)
     */
    public function sendResponse(int $countryId): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo (string)$countryId;
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
        error_log("State addition error: " . $message);
    }
}

// Main execution
try {
    // Initialize state adder
    $stateAdder = new StateAdder($con);
    
    // Validate input
    $validated = $stateAdder->validateInput($_GET['states_add'] ?? null, $_GET['cun'] ?? null);
    
    // Check if country exists
    if (!$stateAdder->countryExists($validated['countryId'])) {
        throw new InvalidArgumentException('Country not found');
    }
    
    // Add state
    $added = $stateAdder->addState($validated['countryId'], $validated['stateName']);
    
    if (!$added) {
        throw new RuntimeException('Failed to add state');
    }
    
    // Log the addition
    $stateAdder->logAddition($validated['countryId'], $validated['stateName']);
    
    // Send success response (return country ID to refresh states list)
    $stateAdder->sendResponse($validated['countryId']);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("State addition validation error: " . $e->getMessage());
    $stateAdder = $stateAdder ?? new StateAdder($con);
    $stateAdder->sendError($e->getMessage());
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("State addition runtime error: " . $e->getMessage());
    $stateAdder = $stateAdder ?? new StateAdder($con);
    $stateAdder->sendError($e->getMessage(), 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("State addition unexpected error: " . $e->getMessage());
    $stateAdder = $stateAdder ?? new StateAdder($con);
    $stateAdder->sendError('An unexpected error occurred', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>