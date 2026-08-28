<?php
/**
 * File: showCountryImage.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: الحصول على مسار علم الدولة بناءً على معرف الدولة
 * Get country flag path based on country ID
 * 
 * Features:
 * - جلب مسار علم الدولة من قاعدة البيانات
 * - إرجاع المسار الكامل للصورة
 * - دعم AJAX لعرض الأعلام ديناميكياً
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/country_flag_errors.log');

// Include database connection
require_once "../common.php";

/**
 * Class CountryFlagFetcher
 * 
 * Handles fetching country flag paths
 */
class CountryFlagFetcher {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Base path for flag images */
    private string $basePath = '../images/country_flag/';
    
    /** @var string Default flag image */
    private string $defaultFlag = 'default.png';
    
    /** @var string Log file path */
    private string $logFile;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/country_flag_requests.log';
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
     * Validate and sanitize country ID
     * 
     * @param mixed $id Raw country ID from GET
     * @return int Validated country ID
     * @throws InvalidArgumentException If validation fails
     */
    public function validateCountryId($id): int {
        if (!isset($id)) {
            throw new InvalidArgumentException('Country ID is required');
        }
        
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('Invalid country ID');
        }
        
        return $cleanId;
    }
    
    /**
     * Get country flag filename
     * 
     * @param int $countryId Country ID
     * @return string|null Flag filename or null if not found
     * @throws RuntimeException If database query fails
     */
    public function getFlagFilename(int $countryId): ?string {
        $sql = "SELECT cn_flag FROM country WHERE cn_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $countryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to execute query: ' . mysqli_error($this->db));
        }
        
        $flagFilename = null;
        if ($row = mysqli_fetch_assoc($result)) {
            $flagFilename = $row['cn_flag'];
        }
        
        mysqli_stmt_close($stmt);
        
        return $flagFilename;
    }
    
    /**
     * Get full flag path
     * 
     * @param int $countryId Country ID
     * @return string Full path to flag image
     */
    public function getFlagPath(int $countryId): string {
        try {
            $flagFilename = $this->getFlagFilename($countryId);
            
            if ($flagFilename && file_exists($this->basePath . $flagFilename)) {
                return $this->basePath . $flagFilename;
            }
            
            // Return default flag if country flag not found
            return $this->basePath . $this->defaultFlag;
            
        } catch (Exception $e) {
            error_log("Error getting flag for country {$countryId}: " . $e->getMessage());
            return $this->basePath . $this->defaultFlag;
        }
    }
    
    /**
     * Check if flag file exists
     * 
     * @param string $filename Flag filename
     * @return bool True if file exists
     */
    public function flagExists(string $filename): bool {
        return !empty($filename) && file_exists($this->basePath . $filename);
    }
    
    /**
     * Get country details
     * 
     * @param int $countryId Country ID
     * @return array|null Country details
     */
    public function getCountryDetails(int $countryId): ?array {
        $sql = "SELECT cn_name, cn_code, cn_currency, cn_flag FROM country WHERE cn_id = ?";
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
     * Log the request
     * 
     * @param int $countryId Country ID
     * @param string $flagPath Flag path returned
     */
    public function logRequest(int $countryId, string $flagPath): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $countryDetails = $this->getCountryDetails($countryId);
        $countryName = $countryDetails['cn_name'] ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] Country Flag Request | ID: %d | Country: %s | Flag: %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $countryId,
            $countryName,
            basename($flagPath),
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send response
     * 
     * @param string $path Flag path
     */
    public function sendResponse(string $path): void {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        echo $path;
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
        
        if ($statusCode === 400) {
            echo $this->basePath . $this->defaultFlag;
        } else {
            echo $this->basePath . $this->defaultFlag;
        }
        
        error_log("Country flag error: " . $message);
    }
}

// Main execution
try {
    // Initialize fetcher
    $fetcher = new CountryFlagFetcher($con);
    
    // Validate input
    $countryId = $fetcher->validateCountryId($_GET['id'] ?? null);
    
    // Get flag path
    $flagPath = $fetcher->getFlagPath($countryId);
    
    // Log the request
    $fetcher->logRequest($countryId, $flagPath);
    
    // Send response
    $fetcher->sendResponse($flagPath);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Country flag validation error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CountryFlagFetcher($con);
    $fetcher->sendError('Invalid request');
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Country flag runtime error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CountryFlagFetcher($con);
    $fetcher->sendError('Database error', 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Country flag unexpected error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CountryFlagFetcher($con);
    $fetcher->sendError('Unexpected error', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>