<?php
/**
 * File: showCategory.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: جلب التصنيفات الفرعية لعرضها في قائمة منسدلة
 * Get subcategories for dropdown display
 * 
 * Features:
 * - جلب التصنيفات الفرعية بناءً على معرف التصنيف الرئيسي
 * - إرجاع خيارات HTML للتحديد
 * - دعم AJAX للتحديث الديناميكي
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/subcategories_errors.log');

// Include database connection
require_once "../common.php";

/**
 * Class SubcategoryDropdown
 * 
 * Handles fetching subcategories for dropdown display
 */
class SubcategoryDropdown {
    
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
        $this->logFile = __DIR__ . '/../../logs/subcategories_requests.log';
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
     * Validate and sanitize parent category ID
     * 
     * @param mixed $q Raw category ID from GET
     * @return int Validated category ID
     * @throws InvalidArgumentException If validation fails
     */
    public function validateCategoryId($q): int {
        if (!isset($q)) {
            throw new InvalidArgumentException('Category ID is required');
        }
        
        $cleanId = filter_var(trim((string)$q), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId < 0) {
            throw new InvalidArgumentException('Invalid category ID');
        }
        
        return $cleanId;
    }
    
    /**
     * Fetch subcategories for parent category
     * 
     * @param int $parentId Parent category ID
     * @return array List of subcategories
     * @throws RuntimeException If database query fails
     */
    public function fetchSubcategories(int $parentId): array {
        if ($parentId <= 0) {
            return [];
        }
        
        $sql = "SELECT pc_id, pc_name 
                FROM product_category 
                WHERE pc_parent_id = ? 
                ORDER BY pc_name";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to execute query: ' . mysqli_error($this->db));
        }
        
        $subcategories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = [
                'id' => (int)$row['pc_id'],
                'name' => stripslashes($row['pc_name'])
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $subcategories;
    }
    
    /**
     * Generate HTML dropdown options
     * 
     * @param array $subcategories List of subcategories
     * @param bool $includeDefault Whether to include default option
     * @return string HTML options
     */
    public function generateOptions(array $subcategories, bool $includeDefault = true): string {
        $options = [];
        
        if ($includeDefault) {
            $options[] = '<option value="0">- Select Category -</option>';
        }
        
        if (empty($subcategories)) {
            if (!$includeDefault) {
                $options[] = '<option value="">- No subcategories available -</option>';
            }
            return implode("\n", $options);
        }
        
        foreach ($subcategories as $subcat) {
            $options[] = sprintf(
                '<option value="%d">%s</option>',
                $subcat['id'],
                htmlspecialchars($subcat['name'], ENT_QUOTES, 'UTF-8')
            );
        }
        
        return implode("\n", $options);
    }
    
    /**
     * Check if parent category has subcategories
     * 
     * @param int $parentId Parent category ID
     * @return bool True if has subcategories
     */
    public function hasSubcategories(int $parentId): bool {
        $sql = "SELECT COUNT(*) as count FROM product_category WHERE pc_parent_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $parentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Get category name by ID
     * 
     * @param int $categoryId Category ID
     * @return string|null Category name
     */
    public function getCategoryName(int $categoryId): ?string {
        $sql = "SELECT pc_name FROM product_category WHERE pc_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['pc_name'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Log the request
     * 
     * @param int $parentId Parent category ID
     * @param int $resultCount Number of results
     */
    public function logRequest(int $parentId, int $resultCount): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $categoryName = $this->getCategoryName($parentId) ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] Subcategory Dropdown Request | Parent ID: %d | Parent: %s | Results: %d | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $parentId,
            $categoryName,
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
            echo '<option value="0">- Invalid request -</option>';
        } else {
            echo '<option value="0">- System error -</option>';
        }
        
        error_log("Subcategory dropdown error: " . $message);
    }
}

// Main execution
try {
    // Initialize dropdown handler
    $dropdown = new SubcategoryDropdown($con);
    
    // Validate input
    $parentId = $dropdown->validateCategoryId($_GET['q'] ?? null);
    
    // If ID is 0 or negative, return default option
    if ($parentId <= 0) {
        $dropdown->sendResponse('<option value="0">- Select Category -</option>');
        exit;
    }
    
    // Fetch subcategories
    $subcategories = $dropdown->fetchSubcategories($parentId);
    
    // Log the request
    $dropdown->logRequest($parentId, count($subcategories));
    
    // Generate and send response
    $response = $dropdown->generateOptions($subcategories, true);
    $dropdown->sendResponse($response);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Subcategory dropdown validation error: " . $e->getMessage());
    $dropdown = $dropdown ?? new SubcategoryDropdown($con);
    $dropdown->sendError('Invalid request');
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Subcategory dropdown runtime error: " . $e->getMessage());
    $dropdown = $dropdown ?? new SubcategoryDropdown($con);
    $dropdown->sendError('Database error', 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Subcategory dropdown unexpected error: " . $e->getMessage());
    $dropdown = $dropdown ?? new SubcategoryDropdown($con);
    $dropdown->sendError('Unexpected error', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>