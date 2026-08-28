<?php
/**
 * File: changeServiceStatus.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة الخدمات (تفعيل/تعطيل) في النظام
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/services_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../common.php';

/**
 * Class ServicesStatusUpdater
 */
class ServicesStatusUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'services';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/services_updates.log';
        $this->ensureLogDirectory();
    }
    
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Validate input data
     * 
     * @param mixed $stat Status value (0 or 1)
     * @param mixed $id Service ID
     * @return array{status: int, id: int}
     * @throws InvalidArgumentException
     */
    public function validateInput($stat, $id): array {
        if (!isset($stat) || !isset($id)) {
            throw new InvalidArgumentException('All fields are required');
        }
        
        $cleanStatus = filter_var(trim((string)$stat), FILTER_VALIDATE_INT);
        if ($cleanStatus === false) {
            throw new InvalidArgumentException('Invalid status value');
        }
        
        if (!in_array($cleanStatus, $this->allowedStatuses, true)) {
            throw new InvalidArgumentException('Status must be 0 or 1');
        }
        
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('Invalid service ID');
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Get current service status
     * 
     * @param int $id Service ID
     * @return int|null Current status
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT ser_status FROM {$this->tableName} WHERE ser_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $status = (int)$row['ser_status'];
            mysqli_stmt_close($stmt);
            return $status;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get service details
     * 
     * @param int $id Service ID
     * @return array|null Service details
     */
    public function getServiceDetails(int $id): ?array {
        $sql = "SELECT s.*, c.cat_name as category_name 
                FROM {$this->tableName} s
                LEFT JOIN category c ON s.ser_category = c.cat_id
                WHERE s.ser_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
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
     * Update service status
     * 
     * @param int $status New status
     * @param int $id Service ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE {$this->tableName} SET ser_status = ? WHERE ser_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $status, $id);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to execute update: ' . $error);
        }
        
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected === 0) {
            throw new RuntimeException('Service not found');
        }
        
        return true;
    }
    
    /**
     * Get status text
     * 
     * @param int $status Status value
     * @return string Status text
     */
    public function getStatusText(int $status): string {
        return $status === 1 ? 'Active' : 'Inactive';
    }
    
    /**
     * Get service type description
     * 
     * @param array $details Service details
     * @return string Type description
     */
    public function getServiceTypeDescription(array $details): string {
        $type = $details['ser_type'] ?? 'standard';
        $category = $details['category_name'] ?? 'Uncategorized';
        
        $types = [
            'standard' => 'Standard Service',
            'premium' => 'Premium Service',
            'featured' => 'Featured Service',
            'popular' => 'Popular Service',
            'new' => 'New Service'
        ];
        
        $typeName = $types[$type] ?? "Type: $type";
        return "$typeName (Category: $category)";
    }
    
    /**
     * Log the status update
     * 
     * @param int $id Service ID
     * @param int|null $oldStatus Old status
     * @param int $newStatus New status
     * @param array $details Service details
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus, array $details = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $serviceName = $details['ser_name'] ?? 'Unnamed Service';
        $serviceDesc = isset($details['ser_description']) ? substr(strip_tags($details['ser_description']), 0, 50) . '...' : 'No description';
        $serviceType = $this->getServiceTypeDescription($details);
        $servicePrice = isset($details['ser_price']) ? '$' . number_format((float)$details['ser_price'], 2) : 'No price';
        $serviceDuration = $details['ser_duration'] ?? 'No duration';
        $serviceOrder = $details['ser_order'] ?? '0';
        
        $oldStatusText = $this->getStatusText($oldStatus ?? 0);
        $newStatusText = $this->getStatusText($newStatus);
        
        $logEntry = sprintf(
            "[%s] SERVICE STATUS UPDATE | ID: %d | Name: %s | Type: %s | Price: %s | Duration: %s | Description: %s | Order: %s | Old: %s (%d) | New: %s (%d) | Admin: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $serviceName,
            $serviceType,
            $servicePrice,
            $serviceDuration,
            $serviceDesc,
            $serviceOrder,
            $oldStatusText,
            $oldStatus ?? 0,
            $newStatusText,
            $newStatus,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Check if service exists
     * 
     * @param int $id Service ID
     * @return bool
     */
    public function serviceExists(int $id): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE ser_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Count active services by category
     * 
     * @param int $categoryId Category ID
     * @return int Number of active services
     */
    public function countActiveByCategory(int $categoryId): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE ser_category = ? AND ser_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Count active services by type
     * 
     * @param string $type Service type
     * @return int Number of active services
     */
    public function countActiveByType(string $type): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE ser_type = ? AND ser_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get total active services count
     * 
     * @return int Total active services
     */
    public function getTotalActiveCount(): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE ser_status = 1";
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_assoc($result);
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get services by provider
     * 
     * @param int $providerId Provider user ID
     * @return array List of services
     */
    public function getServicesByProvider(int $providerId): array {
        $sql = "SELECT ser_id, ser_name, ser_status FROM {$this->tableName} 
                WHERE ser_provider_id = ? ORDER BY ser_order";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $providerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $services = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $services[] = [
                'id' => (int)$row['ser_id'],
                'name' => $row['ser_name'],
                'status' => (int)$row['ser_status']
            ];
        }
        
        mysqli_stmt_close($stmt);
        return $services;
    }
    
    /**
     * Check if category has service limit
     * 
     * @param int $categoryId Category ID
     * @param int $limit Maximum allowed services
     * @return bool True if can add more
     */
    public function canActivateInCategory(int $categoryId, int $limit = 50): bool {
        $activeCount = $this->countActiveByCategory($categoryId);
        return $activeCount < $limit;
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
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed', 405);
    }
    
    // Initialize updater
    $updater = new ServicesStatusUpdater($con);
    
    // Check permission
    if (!$updater->checkPermission()) {
        $updater->sendResponse(false, 'Unauthorized access');
        exit;
    }
    
    // Validate required fields
    if (!isset($_POST['stat']) || !isset($_POST['id'])) {
        $updater->sendResponse(false, 'Missing required fields');
        exit;
    }
    
    // Validate input
    $validated = $updater->validateInput($_POST['stat'], $_POST['id']);
    
    // Check if service exists
    if (!$updater->serviceExists($validated['id'])) {
        $updater->sendResponse(false, 'Service not found');
        exit;
    }
    
    // Get current status and details
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    $details = $updater->getServiceDetails($validated['id']) ?? [];
    
    // Check if status is already the same
    if ($oldStatus === $validated['status']) {
        $statusText = $updater->getStatusText($validated['status']);
        $updater->sendResponse(true, "Service already {$statusText}", [
            'service_id' => $validated['id'],
            'status' => $validated['status']
        ]);
        exit;
    }
    
    // Get additional info
    $categoryId = (int)($details['ser_category'] ?? 0);
    $type = $details['ser_type'] ?? 'standard';
    $providerId = (int)($details['ser_provider_id'] ?? 0);
    
    // If activating, check category limits
    if ($validated['status'] === 1 && $categoryId > 0) {
        $categoryActiveCount = $updater->countActiveByCategory($categoryId);
        $typeActiveCount = $updater->countActiveByType($type);
        
        // Category-specific limits
        $categoryLimits = [
            1 => 100,  // Category ID 1: max 100 services
            2 => 50,   // Category ID 2: max 50 services
            3 => 30    // Category ID 3: max 30 services
        ];
        
        if (isset($categoryLimits[$categoryId]) && $categoryActiveCount >= $categoryLimits[$categoryId]) {
            $updater->sendResponse(false, "Cannot activate: Maximum limit reached for this category", [
                'category_id' => $categoryId,
                'limit' => $categoryLimits[$categoryId],
                'current' => $categoryActiveCount
            ]);
            exit;
        }
    }
    
    // Update status
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $details);
    
    // Get updated counts
    $categoryActiveCount = $categoryId > 0 ? $updater->countActiveByCategory($categoryId) : 0;
    $typeActiveCount = $updater->countActiveByType($type);
    $providerServices = $providerId > 0 ? $updater->getServicesByProvider($providerId) : [];
    $totalActiveCount = $updater->getTotalActiveCount();
    
    // Send success response
    $statusText = $updater->getStatusText($validated['status']);
    $updater->sendResponse(true, "Service {$statusText} successfully", [
        'service_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'name' => $details['ser_name'] ?? null,
        'category_id' => $categoryId,
        'category_name' => $details['category_name'] ?? null,
        'type' => $type,
        'provider_id' => $providerId,
        'category_active_count' => $categoryActiveCount,
        'type_active_count' => $typeActiveCount,
        'provider_services_count' => count($providerServices),
        'total_active_services' => $totalActiveCount
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Services status validation error: " . $e->getMessage());
    $updater = $updater ?? new ServicesStatusUpdater($con);
    $updater->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Services status runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    
    $updater = $updater ?? new ServicesStatusUpdater($con);
    $updater->sendResponse(false, 'Failed to update service status');
    
} catch (Exception $e) {
    error_log("Services status unexpected error: " . $e->getMessage());
    $updater = $updater ?? new ServicesStatusUpdater($con);
    $updater->sendResponse(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>