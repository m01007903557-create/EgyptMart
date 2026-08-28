<?php
/**
 * File: sqltest.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: التحقق من قيمة max_heap_table_size في MySQL
 * Check MySQL max_heap_table_size value
 * 
 * Features:
 * - استعلام عن قيمة max_heap_table_size
 * - إرجاع النتيجة بصيغة JSON
 * - معالجة الأخطاء
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/mysql_errors.log');

// Include database connection
require_once 'common.php';

/**
 * Class MySQLConfigChecker
 * 
 * Handles MySQL configuration checks
 */
class MySQLConfigChecker {
    
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
        $this->logFile = __DIR__ . '/../../logs/mysql_config_checks.log';
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
     * Check database connection
     * 
     * @return bool True if connected
     */
    public function checkConnection(): bool {
        return $this->db !== null && !mysqli_connect_error();
    }
    
    /**
     * Get max_heap_table_size value
     * 
     * @return array Query result
     * @throws RuntimeException If query fails
     */
    public function getMaxHeapTableSize(): array {
        $sql = "SELECT @@max_heap_table_size";
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            throw new RuntimeException('Failed to execute query: ' . mysqli_error($this->db));
        }
        
        $rows = mysqli_fetch_all($result, MYSQLI_NUM);
        mysqli_free_result($result);
        
        return $rows;
    }
    
    /**
     * Format size for human readability
     * 
     * @param int $bytes Size in bytes
     * @return string Formatted size
     */
    public function formatSize(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Log the check
     * 
     * @param array $result Query result
     */
    public function logCheck(array $result): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $size = isset($result[0][0]) ? (int)$result[0][0] : 0;
        $formattedSize = $this->formatSize($size);
        
        $logEntry = sprintf(
            "[%s] MySQL Config Check | Variable: max_heap_table_size | Value: %d (%s) | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $size,
            $formattedSize,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send JSON response
     * 
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    public function sendResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    public function sendError(string $message, int $statusCode = 500): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        error_log("MySQL config check error: " . $message);
    }
}

// Main execution
try {
    // Initialize checker
    $checker = new MySQLConfigChecker($con);
    
    // Check connection
    if (!$checker->checkConnection()) {
        throw new RuntimeException('Database connection failed');
    }
    
    // Get max_heap_table_size value
    $result = $checker->getMaxHeapTableSize();
    
    // Log the check
    $checker->logCheck($result);
    
    // Prepare enhanced response
    $sizeInBytes = isset($result[0][0]) ? (int)$result[0][0] : 0;
    
    $response = [
        'success' => true,
        'data' => [
            'raw' => $result,
            'formatted' => [
                'bytes' => $sizeInBytes,
                'human_readable' => $checker->formatSize($sizeInBytes)
            ]
        ],
        'timestamp' => date('Y-m-d H:i:s'),
        'query' => 'SELECT @@max_heap_table_size'
    ];
    
    // Send response
    $checker->sendResponse($response);
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("MySQL config check runtime error: " . $e->getMessage());
    $checker = $checker ?? new MySQLConfigChecker($con ?? null);
    $checker->sendError($e->getMessage());
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("MySQL config check unexpected error: " . $e->getMessage());
    $checker = $checker ?? new MySQLConfigChecker($con ?? null);
    $checker->sendError('An unexpected error occurred');
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>