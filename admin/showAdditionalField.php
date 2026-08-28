<?php
/**
 * File: showAdditionalField.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: جلب الحقول الإضافية حسب الفئة الفرعية المحددة
 * Get additional fields based on selected subcategory
 * 
 * Features:
 * - جلب الحقول الإضافية المرتبطة بفئة فرعية معينة
 * - تصفية حسب نوع الحقل (select, radio, checkbox)
 * - إرجاع خيارات HTML للتحديد
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/additional_fields_errors.log');

// Include database connection
require_once "../common.php";

/**
 * Class AdditionalFieldFetcher
 * 
 * Handles fetching additional fields for subcategories
 */
class AdditionalFieldFetcher {
    
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
        $this->logFile = __DIR__ . '/../../logs/additional_fields_requests.log';
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
     * Validate and sanitize subcategory ID
     * 
     * @param mixed $scat Raw subcategory ID from GET
     * @return int Validated subcategory ID
     * @throws InvalidArgumentException If validation fails
     */
    public function validateSubcategoryId($scat): int {
        if (!isset($scat)) {
            throw new InvalidArgumentException('Subcategory ID is required');
        }
        
        $cleanId = filter_var(trim((string)$scat), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId < 0) {
            throw new InvalidArgumentException('Invalid subcategory ID');
        }
        
        return $cleanId;
    }
    
    /**
     * Fetch additional fields for subcategory
     * 
     * @param int $subcatId Subcategory ID
     * @return array List of additional fields
     * @throws RuntimeException If database query fails
     */
    public function fetchFields(int $subcatId): array {
        if ($subcatId <= 0) {
            return [];
        }
        
        $sql = "SELECT af_id, af_label 
                FROM additional_field 
                WHERE af_pc_id = ? 
                  AND af_type IN ('select', 'radio', 'checkbox') 
                ORDER BY af_label";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $subcatId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to execute query: ' . mysqli_error($this->db));
        }
        
        $fields = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $fields[] = [
                'id' => (int)$row['af_id'],
                'label' => $row['af_label']
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $fields;
    }
    
    /**
     * Generate HTML options
     * 
     * @param array $fields List of fields
     * @return string HTML options
     */
    public function generateOptions(array $fields): string {
        if (empty($fields)) {
            return "<option value=''>- No fields available -</option>";
        }
        
        $options = ["<option value=''>- Select additional field -</option>"];
        
        foreach ($fields as $field) {
            $options[] = sprintf(
                '<option value="%d">%s</option>',
                $field['id'],
                htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8')
            );
        }
        
        return implode("\n", $options);
    }
    
    /**
     * Log the request
     * 
     * @param int $subcatId Subcategory ID
     * @param int $resultCount Number of results
     */
    public function logRequest(int $subcatId, int $resultCount): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] Additional Fields Request | Subcategory ID: %d | Results: %d | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $subcatId,
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
        echo "<option value=''>- Error: " . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . " -</option>";
    }
}

// Main execution
try {
    // Initialize fetcher
    $fetcher = new AdditionalFieldFetcher($con);
    
    // Validate input
    $subcatId = $fetcher->validateSubcategoryId($_GET['scat'] ?? null);
    
    // Fetch fields (returns empty array for ID <= 0)
    $fields = $fetcher->fetchFields($subcatId);
    
    // Log the request
    $fetcher->logRequest($subcatId, count($fields));
    
    // Generate and send response
    $response = $fetcher->generateOptions($fields);
    $fetcher->sendResponse($response);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Additional fields validation error: " . $e->getMessage());
    $fetcher = $fetcher ?? new AdditionalFieldFetcher($con);
    $fetcher->sendError('Invalid request');
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Additional fields runtime error: " . $e->getMessage());
    $fetcher = $fetcher ?? new AdditionalFieldFetcher($con);
    $fetcher->sendError('System error', 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Additional fields unexpected error: " . $e->getMessage());
    $fetcher = $fetcher ?? new AdditionalFieldFetcher($con);
    $fetcher->sendError('Unexpected error', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>