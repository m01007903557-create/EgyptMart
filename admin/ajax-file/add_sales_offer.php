<?php
/**
 * File: add_sales_offer.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إنشاء عرض بيع من منتج محدد ونسخ الصور إلى مجلد عروض البيع
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/sale_offer_errors.log');

session_start();
require_once "../../common.php";

/**
 * Class SaleOfferCreator
 */
class SaleOfferCreator {
    private mysqli $db;
    private string $logFile;
    private array $paths;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/sale_offer_creation.log';
        $this->paths = [
            'source' => __DIR__ . '/../../upload/myproduct/',
            'dest' => __DIR__ . '/../../upload/sale_offer/',
            'dest_thumb' => __DIR__ . '/../../upload/sale_offer/thumb/'
        ];
        $this->ensureDirectories();
        $this->ensureLogDirectory();
    }
    
    private function ensureDirectories(): void {
        foreach ([$this->paths['dest'], $this->paths['dest_thumb']] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
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
     * @param string|null $idParam Raw ID parameter (format: prefix-productid)
     * @return int Validated product ID
     * @throws InvalidArgumentException
     */
    public function parseProductId(?string $idParam): int {
        if (empty($idParam)) {
            throw new InvalidArgumentException('ID parameter is required');
        }
        
        $parts = explode('-', $idParam);
        
        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Invalid ID format. Expected: prefix-productid');
        }
        
        $productId = filter_var(trim($parts[1]), FILTER_VALIDATE_INT);
        
        if ($productId === false || $productId <= 0) {
            throw new InvalidArgumentException('Invalid product ID');
        }
        
        return $productId;
    }
    
    /**
     * Get product details
     * 
     * @param int $productId Product ID
     * @return object|null Product details
     * @throws RuntimeException
     */
    public function getProductDetails(int $productId): ?object {
        $sql = "SELECT * FROM products WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare product query');
        }
        
        mysqli_stmt_bind_param($stmt, "i", $productId);
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
     * Copy product image to sale offer folders
     * 
     * @param string $imageName Image filename
     * @return bool Success status
     */
    public function copyImage(string $imageName): bool {
        if (empty($imageName)) {
            return false;
        }
        
        $sourcePath = $this->paths['source'] . $imageName;
        $destPath = $this->paths['dest'] . $imageName;
        $destThumbPath = $this->paths['dest_thumb'] . $imageName;
        
        if (!file_exists($sourcePath)) {
            error_log("Source image not found: $sourcePath");
            return false;
        }
        
        $copied1 = copy($sourcePath, $destPath);
        $copied2 = copy($sourcePath, $destThumbPath);
        
        return $copied1 && $copied2;
    }
    
    /**
     * Create sale offer in database
     * 
     * @param object $product Product details
     * @param string $preferredLocation Preferred buyer location
     * @param int $validity Validity days
     * @return bool Success status
     * @throws RuntimeException
     */
    public function createSaleOffer(object $product, string $preferredLocation = 'any', int $validity = 90): bool {
        $sql = "INSERT INTO sale_offer SET
                so_usr_id = ?,
                so_pc_id = ?,
                so_service = ?,
                so_description = ?,
                so_preferred_buyer_location = ?,
                so_pic = ?,
                so_validity = ?,
                so_posting_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare insert statement');
        }
        
        mysqli_stmt_bind_param(
            $stmt, 
            "iissssi",
            $product->pd_uid,
            $product->pd_subcat_id,
            $product->pd_title,
            $product->pd_desc,
            $preferredLocation,
            $product->pd_image,
            $validity
        );
        
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to create sale offer: ' . $error);
        }
        
        $insertId = mysqli_stmt_insert_id($stmt);
        mysqli_stmt_close($stmt);
        
        return $insertId > 0;
    }
    
    /**
     * Update product sale offer slider status
     * 
     * @param int $productId Product ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateProductSliderStatus(int $productId): bool {
        $sql = "UPDATE products SET pd_so_slider = 1 WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare update statement');
        }
        
        mysqli_stmt_bind_param($stmt, "i", $productId);
        $executed = mysqli_stmt_execute($stmt);
        
        mysqli_stmt_close($stmt);
        
        return $executed;
    }
    
    /**
     * Log the sale offer creation
     * 
     * @param int $productId Product ID
     * @param object $product Product details
     * @param bool $imageCopied Whether image was copied
     */
    public function logCreation(int $productId, object $product, bool $imageCopied): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] SALE OFFER CREATED | Product ID: %d | Title: %s | User ID: %d | Image: %s | Image Copied: %s | Admin: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $productId,
            $product->pd_title ?? 'Unknown',
            $product->pd_uid ?? 0,
            $product->pd_image ?? 'No Image',
            $imageCopied ? 'Yes' : 'No',
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Check if product already has a sale offer
     * 
     * @param int $productId Product ID
     * @return bool
     */
    public function hasSaleOffer(int $productId): bool {
        $sql = "SELECT COUNT(*) as count FROM sale_offer so 
                LEFT JOIN products p ON p.pd_title = so.so_service 
                WHERE p.pd_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
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
     * Send JSON response and redirect
     * 
     * @param bool $success Success status
     * @param string $message Response message
     * @param array $data Additional data
     */
    public function sendResponse(bool $success, string $message, array $data = []): void {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'message' => $message,
                'data' => $data
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Redirect for non-AJAX requests
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'products.php';
        $param = $success ? 'success' : 'error';
        header("Location: $redirectUrl?$param=" . urlencode($message));
        exit;
    }
}

// Main execution
try {
    // Check permission
    $creator = new SaleOfferCreator($con);
    
    if (!$creator->checkPermission()) {
        $creator->sendResponse(false, 'Unauthorized access');
        exit;
    }
    
    // Parse product ID
    $productId = $creator->parseProductId($_GET['id'] ?? null);
    
    // Check if product already has sale offer
    if ($creator->hasSaleOffer($productId)) {
        $creator->sendResponse(false, 'Product already has a sale offer', [
            'product_id' => $productId
        ]);
        exit;
    }
    
    // Get product details
    $product = $creator->getProductDetails($productId);
    
    if (!$product) {
        throw new RuntimeException('Product not found');
    }
    
    // Copy product image
    $imageCopied = $creator->copyImage((string)($product->pd_image ?? ''));
    
    // Create sale offer
    $preferredLocation = $product->so_preferred_buyer_location ?? 'any';
    $validity = (int)($product->so_validity ?? 90);
    
    $created = $creator->createSaleOffer($product, $preferredLocation, $validity);
    
    if (!$created) {
        throw new RuntimeException('Failed to create sale offer');
    }
    
    // Update product slider status
    $updated = $creator->updateProductSliderStatus($productId);
    
    // Log the creation
    $creator->logCreation($productId, $product, $imageCopied);
    
    // Send success response
    $creator->sendResponse(true, 'Sale offer created successfully', [
        'product_id' => $productId,
        'product_title' => $product->pd_title,
        'image_copied' => $imageCopied
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Sale offer creation validation error: " . $e->getMessage());
    $creator = $creator ?? new SaleOfferCreator($con);
    $creator->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Sale offer creation runtime error: " . $e->getMessage());
    $creator = $creator ?? new SaleOfferCreator($con);
    $creator->sendResponse(false, 'Failed to create sale offer: ' . $e->getMessage());
    
} catch (Exception $e) {
    error_log("Sale offer creation unexpected error: " . $e->getMessage());
    $creator = $creator ?? new SaleOfferCreator($con);
    $creator->sendResponse(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>