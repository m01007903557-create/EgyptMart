<?php
/**
 * File: showCity.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: جلب المدن حسب معرف الولاية/المحافظة
 * Get cities by state/province ID
 * 
 * Features:
 * - جلب المدن النشطة المرتبطة بولاية معينة
 * - إرجاع خيارات HTML للقائمة المنسدلة
 * - دعم AJAX للتحديث الديناميكي
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/cities_errors.log');

// Include database connection
require_once "../common.php";

/**
 * Class CitiesByStateFetcher
 * 
 * Handles fetching cities for a given state/province
 */
class CitiesByStateFetcher {
    
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
        $this->logFile = __DIR__ . '/../../logs/cities_requests.log';
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
     * Validate and sanitize state ID
     * 
     * @param mixed $id Raw state ID from GET
     * @return int Validated state ID
     * @throws InvalidArgumentException If validation fails
     */
    public function validateStateId($id): int {
        if (!isset($id)) {
            throw new InvalidArgumentException('State ID is required');
        }
        
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId < 0) {
            throw new InvalidArgumentException('Invalid state ID');
        }
        
        return $cleanId;
    }
    
    /**
     * Fetch cities for state
     * 
     * @param int $stateId State ID
     * @return array List of cities
     * @throws RuntimeException If database query fails
     */
    public function fetchCities(int $stateId): array {
        if ($stateId <= 0) {
            return [];
        }
        
        $sql = "SELECT ct_id, ct_name 
                FROM city 
                WHERE ct_state = ? 
                  AND ct_status = '1' 
                ORDER BY ct_name";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $stateId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to execute query: ' . mysqli_error($this->db));
        }
        
        $cities = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $cities[] = [
                'id' => (int)$row['ct_id'],
                'name' => stripslashes($row['ct_name'])
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $cities;
    }
    
    /**
     * Generate HTML dropdown options
     * 
     * @param array $cities List of cities
     * @param bool $includeDefault Whether to include default option
     * @return string HTML options
     */
    public function generateOptions(array $cities, bool $includeDefault = true): string {
        $options = [];
        
        if ($includeDefault) {
            $options[] = '<option value="">--- Select City ---</option>';
        }
        
        if (empty($cities)) {
            if (!$includeDefault) {
                $options[] = '<option value="">--- No cities available ---</option>';
            }
            return implode("\n", $options);
        }
        
        foreach ($cities as $city) {
            $options[] = sprintf(
                '<option value="%d">%s</option>',
                $city['id'],
                htmlspecialchars($city['name'], ENT_QUOTES, 'UTF-8')
            );
        }
        
        return implode("\n", $options);
    }
    
    /**
     * Get state name by ID
     * 
     * @param int $stateId State ID
     * @return string|null State name
     */
    public function getStateName(int $stateId): ?string {
        $sql = "SELECT st_name FROM states WHERE st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $stateId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['st_name'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Check if state has cities
     * 
     * @param int $stateId State ID
     * @return bool True if has cities
     */
    public function hasCities(int $stateId): bool {
        $sql = "SELECT COUNT(*) as count FROM city WHERE ct_state = ? AND ct_status = '1'";
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
     * Log the request
     * 
     * @param int $stateId State ID
     * @param int $resultCount Number of results
     */
    public function logRequest(int $stateId, int $resultCount): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stateName = $this->getStateName($stateId) ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] Cities by State Request | State ID: %d | State: %s | Cities: %d | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $stateId,
            $stateName,
            $resultCount,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send response
     * 
     * @param string $response Response HTML
     */
    public function sendResponse(string $response): void {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');
        echo $response;
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    public function sendError(string $message, int $statusCode = 400): void {
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=utf-8');
        
        if ($statusCode === 400) {
            echo '<option value="">--- Invalid request ---</option>';
        } else {
            echo '<option value="">--- System error ---</option>';
        }
        
        error_log("Cities by state error: " . $message);
    }
}

// Main execution
try {
    // Initialize fetcher
    $fetcher = new CitiesByStateFetcher($con);
    
    // Validate input
    $stateId = $fetcher->validateStateId($_GET['id'] ?? null);
    
    // If ID is 0 or negative, return default option
    if ($stateId <= 0) {
        $fetcher->sendResponse('<option value="">--- Select City ---</option>');
        exit;
    }
    
    // Fetch cities
    $cities = $fetcher->fetchCities($stateId);
    
    // Log the request
    $fetcher->logRequest($stateId, count($cities));
    
    // Generate and send response
    $response = $fetcher->generateOptions($cities, true);
    $fetcher->sendResponse($response);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Cities by state validation error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CitiesByStateFetcher($con);
    $fetcher->sendError('Invalid request');
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Cities by state runtime error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CitiesByStateFetcher($con);
    $fetcher->sendError('Database error', 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Cities by state unexpected error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CitiesByStateFetcher($con);
    $fetcher->sendError('Unexpected error', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>