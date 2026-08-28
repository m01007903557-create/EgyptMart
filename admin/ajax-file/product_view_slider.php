<?php
/**
 * File: product_view_slider.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث ميزات المنتج (سلايدر العروض، اختيار القائد، سلايدر الولاء)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/product_feature_errors.log');

session_start();
ob_start();

require_once "../../common.php";

/**
 * Class ProductFeatureUpdater
 */
class ProductFeatureUpdater {
    private mysqli $db;
    private array $validFeatures = ['saleoffer', 'leader', 'loyal'];
    private array $featureColumns = [
        'saleoffer' => 'pd_so_slider',
        'leader' => 'pd_pck_dets',
        'loyal' => 'pd_lp_slider'
    ];
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/product_feature_updates.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Parse and validate input ID
     * 
     * @param string $idParam Raw ID parameter (format: feature-productid)
     * @return array{feature: string, productId: int}
     * @throws InvalidArgumentException
     */
    public function parseInput(string $idParam): array {
        if (empty($idParam)) {
            throw new InvalidArgumentException('ID parameter is required');
        }
        
        $parts = explode('-', $idParam);
        
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Invalid ID format. Expected: feature-productid');
        }
        
        $feature = trim($parts[0]);
        $productId = filter_var(trim($parts[1]), FILTER_VALIDATE_INT);
        
        if (!in_array($feature, $this->validFeatures, true)) {
            throw new InvalidArgumentException('Invalid feature type: ' . htmlspecialchars($feature));
        }
        
        if ($productId === false || $productId <= 0) {
            throw new InvalidArgumentException('Invalid product ID');
        }
        
        return [
            'feature' => $feature,
            'productId' => $productId
        ];
    }
    
    /**
     * Get column name for feature
     * 
     * @param string $feature Feature type
     * @return string Column name
     */
    public function getColumnName(string $feature): string {
        return $this->featureColumns[$feature] ?? 'pd_id';
    }
    
    /**
     * Get current feature value
     * 
     * @param int $productId Product ID
     * @param string $column Column name
     * @return int|null Current value
     */
    public function getCurrentValue(int $productId, string $column): ?int {
        $sql = "SELECT $column FROM products WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $value = (int)$row[$column];
            mysqli_stmt_close($stmt);
            return $value;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get product details
     * 
     * @param int $productId Product ID
     * @return array|null Product details
     */
    public function getProductDetails(int $productId): ?array {
        $sql = "SELECT pd_name, pd_code, pd_company FROM products WHERE pd_id = ?";
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
     * Update product feature
     * 
     * @param int $productId Product ID
     * @param string $column Column name
     * @param int $value Value to set (1)
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateFeature(int $productId, string $column, int $value = 1): bool {
        $sql = "UPDATE products SET $column = ? WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $value, $productId);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Execute failed: ' . $error);
        }
        
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected === 0) {
            throw new RuntimeException('Product not found or no changes made');
        }
        
        return true;
    }
    
    /**
     * Log the update
     * 
     * @param string $feature Feature type
     * @param int $productId Product ID
     * @param int|null $oldValue Old value
     * @param array $productDetails Product details
     */
    public function logUpdate(string $feature, int $productId, ?int $oldValue, array $productDetails = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $productName = $productDetails['pd_name'] ?? 'Unknown';
        $productCode = $productDetails['pd_code'] ?? 'Unknown';
        
        $featureNames = [
            'saleoffer' => 'Sale Offer Slider',
            'leader' => 'Leader Pick',
            'loyal' => 'Loyalty Slider'
        ];
        
        $featureName = $featureNames[$feature] ?? $feature;
        
        $logEntry = sprintf(
            "[%s] Product Feature Update | Feature: %s | Product ID: %d | Product: %s | Code: %s | Old: %d | New: 1 | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $featureName,
            $productId,
            $productName,
            $productCode,
            $oldValue ?? 0,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Check user permission
     * 
     * @return bool
     */
    public function checkPermission(): bool {
        return isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] > 0;
    }
    
    /**
     * Get feature display info
     * 
     * @param string $feature Feature type
     * @return array{name: string, message: string}
     */
    public function getFeatureInfo(string $feature): array {
        $info = [
            'saleoffer' => [
                'name' => 'Sale Offer Slider',
                'message' => 'Product added to sale offer slider successfully'
            ],
            'leader' => [
                'name' => 'Leader Pick',
                'message' => 'Product marked as leader pick successfully'
            ],
            'loyal' => [
                'name' => 'Loyalty Slider',
                'message' => 'Product added to loyalty slider successfully'
            ]
        ];
        
        return $info[$feature] ?? [
            'name' => $feature,
            'message' => 'Product updated successfully'
        ];
    }
}

// Main execution
try {
    // Check permission
    if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] <= 0) {
        throw new RuntimeException('Unauthorized access', 403);
    }
    
    // Check required parameter
    if (!isset($_GET['id'])) {
        throw new InvalidArgumentException('ID parameter is required');
    }
    
    // Initialize updater
    $updater = new ProductFeatureUpdater($con);
    
    // Parse input
    $parsed = $updater->parseInput($_GET['id']);
    $feature = $parsed['feature'];
    $productId = $parsed['productId'];
    
    // Get column name
    $column = $updater->getColumnName($feature);
    
    // Get current value and product details
    $oldValue = $updater->getCurrentValue($productId, $column);
    $productDetails = $updater->getProductDetails($productId) ?? [];
    
    // Update feature
    $updater->updateFeature($productId, $column, 1);
    
    // Log the update
    $updater->logUpdate($feature, $productId, $oldValue, $productDetails);
    
    // Get feature info
    $featureInfo = $updater->getFeatureInfo($feature);
    
    // Clear output buffer and redirect
    ob_clean();
    
    // Redirect back with success message
    header("Location: " . $_SERVER['HTTP_REFERER'] ?? 'products.php');
    exit;
    
} catch (InvalidArgumentException $e) {
    error_log("Product feature validation error: " . $e->getMessage());
    ob_clean();
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'products.php') . "?error=" . urlencode($e->getMessage()));
    exit;
    
} catch (RuntimeException $e) {
    error_log("Product feature runtime error: " . $e->getMessage());
    ob_clean();
    
    if ($e->getCode() === 403) {
        header("Location: ../../login.php");
        exit;
    }
    
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'products.php') . "?error=" . urlencode($e->getMessage()));
    exit;
    
} catch (Exception $e) {
    error_log("Product feature unexpected error: " . $e->getMessage());
    ob_clean();
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'products.php') . "?error=System error occurred");
    exit;
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
    ob_end_flush();
}
?>