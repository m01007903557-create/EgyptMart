<?php
/**
 * File: product_view_disapproval.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إلغاء تنشيط المنتج (تعيين الحالة إلى 2)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/product_deactivate_errors.log');

session_start();
require_once "../../common.php";

/**
 * Class ProductDeactivator
 */
class ProductDeactivator {
    private mysqli $db;
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/product_deactivations.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate product ID
     * 
     * @param mixed $id Raw product ID from GET
     * @return int Validated product ID
     * @throws InvalidArgumentException
     */
    public function validateProductId($id): int {
        if (!isset($id)) {
            throw new InvalidArgumentException('Product ID is required');
        }
        
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('Invalid product ID');
        }
        
        return $cleanId;
    }
    
    /**
     * Get product details before deactivation
     * 
     * @param int $productId Product ID
     * @return array|null Product details
     */
    public function getProductDetails(int $productId): ?array {
        $sql = "SELECT pd_name, pd_code, pd_company, pd_status FROM products WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $productId);
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
     * Deactivate product (set status to 2)
     * 
     * @param int $productId Product ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function deactivateProduct(int $productId): bool {
        $sql = "UPDATE products SET pd_status = 2 WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $productId);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to execute update: ' . $error);
        }
        
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected === 0) {
            throw new RuntimeException('Product not found or already deactivated');
        }
        
        return true;
    }
    
    /**
     * Log the deactivation
     * 
     * @param int $productId Product ID
     * @param array $productDetails Product details
     * @param int $oldStatus Previous status
     */
    public function logDeactivation(int $productId, array $productDetails, int $oldStatus): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $productName = $productDetails['pd_name'] ?? 'Unknown';
        $productCode = $productDetails['pd_code'] ?? 'Unknown';
        $company = $productDetails['pd_company'] ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] PRODUCT DEACTIVATED | ID: %d | Name: %s | Code: %s | Company: %s | Old Status: %d | New Status: 2 | User: %d | IP: %s | Agent: %s\n",
            date('Y-m-d H:i:s'),
            $productId,
            $productName,
            $productCode,
            $company,
            $oldStatus,
            $userId,
            $userIp,
            $userAgent
        );
        
        error_log($logEntry, 3, $this->logFile);
        
        // Also log to main error log for redundancy
        error_log("Product deactivated: ID $productId by user $userId");
    }
    
    /**
     * Check if user has permission
     * 
     * @return bool
     */
    public function checkPermission(): bool {
        return isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] > 0;
    }
    
    /**
     * Send JSON response
     * 
     * @param bool $success Success status
     * @param string $message Response message
     * @param array $data Additional data
     */
    public function sendResponse(bool $success, string $message, array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
    }
}

// Main execution
try {
    // Check permission
    $deactivator = new ProductDeactivator($con);
    
    if (!$deactivator->checkPermission()) {
        $deactivator->sendResponse(false, 'Unauthorized access');
        exit;
    }
    
    // Validate product ID
    $productId = $deactivator->validateProductId($_GET['id'] ?? null);
    
    // Get product details before deactivation
    $productDetails = $deactivator->getProductDetails($productId);
    
    if (!$productDetails) {
        throw new RuntimeException('Product not found');
    }
    
    $oldStatus = (int)($productDetails['pd_status'] ?? 0);
    
    // Check if already deactivated
    if ($oldStatus === 2) {
        $deactivator->sendResponse(true, 'Product already deactivated', [
            'product_id' => $productId,
            'status' => 2
        ]);
        exit;
    }
    
    // Deactivate product
    $deactivated = $deactivator->deactivateProduct($productId);
    
    // Log the deactivation
    $deactivator->logDeactivation($productId, $productDetails, $oldStatus);
    
    // Send success response
    $deactivator->sendResponse(true, 'Product deactivated successfully', [
        'product_id' => $productId,
        'old_status' => $oldStatus,
        'new_status' => 2,
        'product_name' => $productDetails['pd_name'] ?? null
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Product deactivation validation error: " . $e->getMessage());
    $deactivator = $deactivator ?? new ProductDeactivator($con);
    $deactivator->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Product deactivation runtime error: " . $e->getMessage());
    $deactivator = $deactivator ?? new ProductDeactivator($con);
    $deactivator->sendResponse(false, 'Failed to deactivate product: ' . $e->getMessage());
    
} catch (Exception $e) {
    error_log("Product deactivation unexpected error: " . $e->getMessage());
    $deactivator = $deactivator ?? new ProductDeactivator($con);
    $deactivator->sendResponse(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>