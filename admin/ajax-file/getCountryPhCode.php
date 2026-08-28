<?php
/**
 * File: getCountryPhCode.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: جلب رمز الهاتف لبلد محدد (للإستخدام في نماذج التسجيل والإدخال)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/country_phone_errors.log');

session_start();
require_once "../../common.php";

/**
 * Class CountryPhoneCodeFetcher
 */
class CountryPhoneCodeFetcher {
    private mysqli $db;
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/country_phone_requests.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate country ID
     * 
     * @param mixed $id Raw country ID from POST
     * @return int Validated country ID
     * @throws InvalidArgumentException
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
     * Get country phone code
     * 
     * @param int $countryId Country ID
     * @return string|null Phone code or null if not found
     * @throws RuntimeException
     */
    public function getPhoneCode(int $countryId): ?string {
        $sql = "SELECT cn_ph FROM country WHERE cn_status = 1 AND cn_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $countryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_object($result)) {
            mysqli_stmt_close($stmt);
            return $row->cn_ph;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get full country details (for logging)
     * 
     * @param int $countryId Country ID
     * @return object|null Country details
     */
    public function getCountryDetails(int $countryId): ?object {
        $sql = "SELECT cn_name, cn_code, cn_ph FROM country WHERE cn_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $countryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_object($result)) {
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
     * @param string|null $phoneCode Phone code returned
     * @param object|null $countryDetails Country details
     */
    public function logRequest(int $countryId, ?string $phoneCode, ?object $countryDetails = null): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $countryName = $countryDetails->cn_name ?? 'Unknown';
        $countryCode = $countryDetails->cn_code ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] Phone Code Request | Country ID: %d | Country: %s | Code: %s | Phone: %s | User: %d | IP: %s | Agent: %s\n",
            date('Y-m-d H:i:s'),
            $countryId,
            $countryName,
            $countryCode,
            $phoneCode ?? 'NOT FOUND',
            $userId,
            $userIp,
            $userAgent
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Check if request is valid (POST method)
     * 
     * @return bool
     */
    public function isValidRequest(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Send response (plain text for AJAX)
     * 
     * @param string $phoneCode Phone code to return
     */
    public function sendResponse(string $phoneCode): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo $phoneCode;
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
        echo ''; // Return empty string on error
    }
}

// Main execution
try {
    // Create fetcher instance
    $fetcher = new CountryPhoneCodeFetcher($con);
    
    // Check request method
    if (!$fetcher->isValidRequest()) {
        $fetcher->sendError('Method not allowed', 405);
        exit;
    }
    
    // Validate country ID
    $countryId = $fetcher->validateCountryId($_POST['id'] ?? null);
    
    // Get country details for logging
    $countryDetails = $fetcher->getCountryDetails($countryId);
    
    // Get phone code
    $phoneCode = $fetcher->getPhoneCode($countryId);
    
    // Log the request
    $fetcher->logRequest($countryId, $phoneCode, $countryDetails);
    
    // Send response (empty string if not found)
    $fetcher->sendResponse($phoneCode ?? '');
    
} catch (InvalidArgumentException $e) {
    error_log("Country phone code validation error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CountryPhoneCodeFetcher($con);
    $fetcher->sendError('Invalid request');
    
} catch (RuntimeException $e) {
    error_log("Country phone code runtime error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CountryPhoneCodeFetcher($con);
    $fetcher->sendError('System error', 500);
    
} catch (Exception $e) {
    error_log("Country phone code unexpected error: " . $e->getMessage());
    $fetcher = $fetcher ?? new CountryPhoneCodeFetcher($con);
    $fetcher->sendError('System error', 500);
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>