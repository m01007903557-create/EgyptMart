<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/product_approve_errors.log');

session_start();
require_once "../../common.php";

/**
 * Class ProductApprover
 */
class ProductApprover {
    private mysqli $db;
    private string $logFile;
    private string $baseUrl = 'http://egyptmart.shop';
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/product_approvals.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate product ID from GET parameters
     * 
     * @param mixed $id Raw product ID
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
     * Get user details by product owner ID
     * 
     * @param int $userId User ID
     * @return object|null User details
     * @throws RuntimeException
     */
    public function getUserDetails(int $userId): ?object {
        $sql = "SELECT u.*, bf.* 
                FROM user u 
                LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid 
                WHERE u.usr_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare user query');
        }
        
        mysqli_stmt_bind_param($stmt, "i", $userId);
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
     * Get measurement unit name
     * 
     * @param int $unitId Unit ID
     * @return string Unit name
     */
    public function getMeasurementUnit(int $unitId): string {
        $sql = "SELECT mu_name FROM measurement_unit WHERE mu_status = 1 AND mu_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return '';
        }
        
        mysqli_stmt_bind_param($stmt, "i", $unitId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_object($result)) {
            mysqli_stmt_close($stmt);
            return $row->mu_name;
        }
        
        mysqli_stmt_close($stmt);
        return '';
    }
    
    /**
     * Update product status to approved (1)
     * 
     * @param int $productId Product ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function approveProduct(int $productId): bool {
        $sql = "UPDATE products SET pd_status = 1 WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare update statement');
        }
        
        mysqli_stmt_bind_param($stmt, "i", $productId);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to approve product: ' . $error);
        }
        
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $affected > 0;
    }
    
    /**
     * Generate product image HTML
     * 
     * @param object $product Product object
     * @return string Image HTML
     */
    public function getProductImage(object $product): string {
        $imagePath = !empty($product->pd_image) 
            ? $this->baseUrl . '/upload/myproduct/' . $product->pd_image
            : $this->baseUrl . '/upload/myproduct/noimage.jpg';
        
        // Handle multiple images (comma separated)
        $images = explode(',', $imagePath);
        $firstImage = $images[0];
        
        return '<img src="' . htmlspecialchars($firstImage) . '" width="100" alt="' . htmlspecialchars($product->pd_title) . '" />';
    }
    
    /**
     * Generate contact details HTML
     * 
     * @param object $user User object
     * @return string Contact details HTML
     */
    public function getContactDetails(object $user): string {
        $company = htmlspecialchars($user->bnsprof_compname ?? '');
        $address = htmlspecialchars($user->bnsprof_address1 ?? '');
        $mobile = htmlspecialchars($user->mobile1 ?? '');
        $email = htmlspecialchars($user->email ?? '');
        
        return "<strong>{$company}</strong><br/>{$address}<br/>Mobile/Cell Phone: {$mobile}<br/>Email: {$email}";
    }
    
    /**
     * Generate user full name
     * 
     * @param object $user User object
     * @return string Full name
     */
    public function getUserFullName(object $user): string {
        $prefix = $user->name_prefix ?? '';
        $fname = $user->fname ?? '';
        $lname = $user->lname ?? '';
        
        return trim("$prefix $fname $lname");
    }
    
    /**
     * Send approval email
     * 
     * @param object $user User object
     * @param object $product Product object
     * @return bool Success status
     */
    public function sendApprovalEmail(object $user, object $product): bool {
        $siteName = get_page_settings(4);
        $fromEmail = get_adminemail();
        $fromName = $siteName;
        
        $userName = $this->getUserFullName($user);
        $productImage = $this->getProductImage($product);
        $contactDetails = $this->getContactDetails($user);
        $measurementUnit = $this->getMeasurementUnit((int)($product->pd_unit ?? 0));
        
        $productPrice = ($product->pd_fob_price ?? '') . ' ~ ' . ($product->pd_fob_price2 ?? '');
        $productMoq = $product->pd_min_order_qty ?? '';
        
        // Generate unique contact ID
        $contactId = rand(1000, 9999) . md5((string)($user->bnsprof_id ?? ''));
        
        // Prepare email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            "From: $fromName <$fromEmail>",
            "Reply-To: $fromEmail"
        ];
        
        // Include email template
        ob_start();
        include "../email/admin_product_approve.php";
        $message = ob_get_clean();
        
        // Send email using SMTP
        return sendSMTPMail($user->email, "Your Product Has Been Approved - $siteName", $message, implode("\r\n", $headers));
    }
    
    /**
     * Log the approval
     * 
     * @param int $productId Product ID
     * @param object $product Product object
     * @param object $user User object
     */
    public function logApproval(int $productId, object $product, object $user): void {
        $adminId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] PRODUCT APPROVED | ID: %d | Title: %s | Owner: %s (ID: %d) | Admin: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $productId,
            $product->pd_title ?? 'Unknown',
            $user->bnsprof_compname ?? 'Unknown',
            $user->usr_id ?? 0,
            $adminId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Check if user has permission to approve products
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
    $approver = new ProductApprover($con);
    
    if (!$approver->checkPermission()) {
        $approver->sendResponse(false, 'Unauthorized access');
        exit;
    }
    
    // Validate product ID
    $productId = $approver->validateProductId($_GET['id'] ?? null);
    
    // Get product details
    $product = $approver->getProductDetails($productId);
    if (!$product) {
        throw new RuntimeException('Product not found');
    }
    
    // Get user details
    $user = $approver->getUserDetails((int)($product->pd_uid ?? 0));
    if (!$user) {
        throw new RuntimeException('Product owner not found');
    }
    
    // Check if already approved
    if (($product->pd_status ?? 0) == 1) {
        $approver->sendResponse(true, 'Product already approved', [
            'product_id' => $productId,
            'status' => 1
        ]);
        exit;
    }
    
    // Approve product
    $approved = $approver->approveProduct($productId);
    
    if (!$approved) {
        throw new RuntimeException('Failed to approve product');
    }
    
    // Send approval email
    $emailSent = $approver->sendApprovalEmail($user, $product);
    
    // Log the approval
    $approver->logApproval($productId, $product, $user);
    
    // Trigger additional notifications
    include_once('../../product-email-notification.php');
    
    // Send success response
    $approver->sendResponse(true, 'Product approved successfully', [
        'product_id' => $productId,
        'email_sent' => $emailSent,
        'product_title' => $product->pd_title ?? null,
        'owner_name' => $user->bnsprof_compname ?? null
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Product approval validation error: " . $e->getMessage());
    $approver = $approver ?? new ProductApprover($con);
    $approver->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Product approval runtime error: " . $e->getMessage());
    $approver = $approver ?? new ProductApprover($con);
    $approver->sendResponse(false, 'Failed to approve product: ' . $e->getMessage());
    
} catch (Exception $e) {
    error_log("Product approval unexpected error: " . $e->getMessage());
    $approver = $approver ?? new ProductApprover($con);
    $approver->sendResponse(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>