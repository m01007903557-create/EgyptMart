<?php
/**
 * File: update_gateway.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث معلومات بوابات الدفع الخاصة بالبائعين
 * Update reseller payment gateway information
 * 
 * Features:
 * - معالجة بيانات بوابات الدفع المتعددة
 * - تحديث أو إضافة معلومات البطاقة لكل بوابة
 * - ربط المعلومات بالبائع الحالي
 * - معالجة البيانات المرسلة بتنسيق خاص
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/reseller_payment_errors.log');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once "../common.php";

/**
 * Class ResellerPaymentGatewayUpdater
 * 
 * Handles updating reseller payment gateway information
 */
class ResellerPaymentGatewayUpdater {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var int Reseller ID */
    private int $resellerId;
    
    /** @var string Log file path */
    private string $logFile;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param int $resellerId Reseller ID
     */
    public function __construct(mysqli $database, int $resellerId) {
        $this->db = $database;
        $this->resellerId = $resellerId;
        $this->logFile = __DIR__ . '/../../logs/reseller_payment_updates.log';
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
     * Validate reseller session
     * 
     * @return bool True if valid
     */
    public function validateSession(): bool {
        return $this->resellerId > 0;
    }
    
    /**
     * Parse and validate input data
     * 
     * @param string|null $total Raw input string
     * @return array Parsed gateway data
     * @throws InvalidArgumentException If input is invalid
     */
    public function parseInput(?string $total): array {
        if (empty($total)) {
            throw new InvalidArgumentException('No gateway data provided');
        }
        
        // Remove first 2 characters as per original code
        $cleanTotal = substr($total, 2);
        
        if (empty($cleanTotal)) {
            return [];
        }
        
        // Split records
        $records = explode("||", $cleanTotal);
        $gatewayData = [];
        
        foreach ($records as $record) {
            if (empty($record)) {
                continue;
            }
            
            $parts = explode(":", $record);
            if (count($parts) >= 2) {
                $gatewayData[] = [
                    'gateway_id' => (int)$parts[0],
                    'card_number' => $parts[1]
                ];
            }
        }
        
        return $gatewayData;
    }
    
    /**
     * Check if gateway record exists
     * 
     * @param int $gatewayId Gateway ID
     * @return object|null Existing record or null
     */
    public function getExistingRecord(int $gatewayId): ?object {
        $sql = "SELECT * FROM reseller_payment_gateway 
                WHERE resl_pg_resellerid = ? AND resl_pg_gateway = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $this->resellerId, $gatewayId);
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
     * Insert new gateway record
     * 
     * @param int $gatewayId Gateway ID
     * @param string $cardNumber Card number
     * @return bool Success status
     */
    public function insertGateway(int $gatewayId, string $cardNumber): bool {
        $sql = "INSERT INTO reseller_payment_gateway 
                SET resl_pg_resellerid = ?, resl_pg_cardno = ?, resl_pg_gateway = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "isi", $this->resellerId, $cardNumber, $gatewayId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Update existing gateway record
     * 
     * @param int $recordId Record ID
     * @param int $gatewayId Gateway ID
     * @param string $cardNumber Card number
     * @return bool Success status
     */
    public function updateGateway(int $recordId, int $gatewayId, string $cardNumber): bool {
        $sql = "UPDATE reseller_payment_gateway 
                SET resl_pg_resellerid = ?, resl_pg_cardno = ?, resl_pg_gateway = ? 
                WHERE resl_pg_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "isii", $this->resellerId, $cardNumber, $gatewayId, $recordId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Process all gateway data
     * 
     * @param array $gatewayData Parsed gateway data
     * @return array Processing results
     */
    public function processGateways(array $gatewayData): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'details' => []
        ];
        
        foreach ($gatewayData as $data) {
            $gatewayId = $data['gateway_id'];
            $cardNumber = $data['card_number'];
            
            if ($gatewayId <= 0) {
                $results['failed']++;
                $results['details'][] = [
                    'gateway_id' => $gatewayId,
                    'status' => 'failed',
                    'reason' => 'Invalid gateway ID'
                ];
                continue;
            }
            
            $existing = $this->getExistingRecord($gatewayId);
            
            if (!$existing) {
                // Insert new record
                if ($this->insertGateway($gatewayId, $cardNumber)) {
                    $results['success']++;
                    $results['inserted']++;
                    $results['details'][] = [
                        'gateway_id' => $gatewayId,
                        'status' => 'inserted'
                    ];
                    
                    $this->logOperation('insert', $gatewayId, $cardNumber);
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'gateway_id' => $gatewayId,
                        'status' => 'failed',
                        'reason' => 'Insert failed'
                    ];
                }
            } else {
                // Update existing record
                if ($this->updateGateway((int)$existing->resl_pg_id, $gatewayId, $cardNumber)) {
                    $results['success']++;
                    $results['updated']++;
                    $results['details'][] = [
                        'gateway_id' => $gatewayId,
                        'status' => 'updated',
                        'record_id' => (int)$existing->resl_pg_id
                    ];
                    
                    $this->logOperation('update', $gatewayId, $cardNumber, (int)$existing->resl_pg_id);
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'gateway_id' => $gatewayId,
                        'status' => 'failed',
                        'reason' => 'Update failed'
                    ];
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Log the operation
     * 
     * @param string $operation Operation type
     * @param int $gatewayId Gateway ID
     * @param string $cardNumber Card number (masked)
     * @param int|null $recordId Record ID
     */
    private function logOperation(string $operation, int $gatewayId, string $cardNumber, ?int $recordId = null): void {
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Mask card number for security
        $maskedCard = substr($cardNumber, 0, 4) . '****' . substr($cardNumber, -4);
        
        $logEntry = sprintf(
            "[%s] Gateway %s | Reseller ID: %d | Gateway ID: %d | Card: %s | Record ID: %s | IP: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($operation),
            $this->resellerId,
            $gatewayId,
            $maskedCard,
            $recordId ?? 'NEW',
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send response
     * 
     * @param array $results Processing results
     */
    public function sendResponse(array $results): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Gateway information updated successfully',
            'results' => $results
        ], JSON_PRETTY_PRINT);
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
        
        error_log("Reseller payment gateway error: " . $message);
    }
}

// Main execution
try {
    // Get reseller ID from session
    $resellerId = (int)($_SESSION['reseller_id'] ?? 0);
    
    // Initialize updater
    $updater = new ResellerPaymentGatewayUpdater($con, $resellerId);
    
    // Validate session
    if (!$updater->validateSession()) {
        throw new RuntimeException('Invalid reseller session', 403);
    }
    
    // Get and validate input
    $total = $_GET['total_gateway'] ?? '';
    $gatewayData = $updater->parseInput($total);
    
    if (empty($gatewayData)) {
        $updater->sendResponse([
            'success' => 0,
            'failed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'details' => [],
            'message' => 'No gateway data to process'
        ]);
        exit;
    }
    
    // Process gateways
    $results = $updater->processGateways($gatewayData);
    
    // Send response
    $updater->sendResponse($results);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Reseller payment validation error: " . $e->getMessage());
    $updater = $updater ?? new ResellerPaymentGatewayUpdater($con, 0);
    $updater->sendError($e->getMessage());
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Reseller payment runtime error: " . $e->getMessage());
    $updater = $updater ?? new ResellerPaymentGatewayUpdater($con, 0);
    $updater->sendError($e->getMessage(), $e->getCode() ?: 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Reseller payment unexpected error: " . $e->getMessage());
    $updater = $updater ?? new ResellerPaymentGatewayUpdater($con, 0);
    $updater->sendError('An unexpected error occurred', 500);
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>