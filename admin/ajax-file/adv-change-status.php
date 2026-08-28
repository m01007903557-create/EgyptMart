<?php
/**
 * File: adv-change-status.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة الإعلانات (تفعيل/تعطيل)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/advertisement_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../common.php';

/**
 * Class AdvertisementStatusUpdater
 */
class AdvertisementStatusUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'advertisement';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/advertisement_updates.log';
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
     * @param mixed $id Advertisement ID
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
            throw new InvalidArgumentException('Invalid advertisement ID');
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Get current advertisement status
     * 
     * @param int $id Advertisement ID
     * @return int|null Current status
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT adv_status FROM {$this->tableName} WHERE adv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $status = (int)$row['adv_status'];
            mysqli_stmt_close($stmt);
            return $status;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get advertisement details
     * 
     * @param int $id Advertisement ID
     * @return array|null Advertisement details
     */
    public function getAdvertisementDetails(int $id): ?array {
        $sql = "SELECT a.*, ac.at_name as category_name 
                FROM {$this->tableName} a
                LEFT JOIN adv_category ac ON a.adv_cat_id = ac.at_id
                WHERE a.adv_id = ?";
        
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
     * Update advertisement status
     * 
     * @param int $status New status
     * @param int $id Advertisement ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE {$this->tableName} SET adv_status = ? WHERE adv_id = ?";
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
            throw new RuntimeException('Advertisement not found');
        }
        
        return true;
    }
    
    /**
     * Log the status update
     * 
     * @param int $id Advertisement ID
     * @param int|null $oldStatus Old status
     * @param int $newStatus New status
     * @param array $details Advertisement details
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus, array $details = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $title = $details['adv_title'] ?? 'Unknown';
        $category = $details['category_name'] ?? 'Unknown';
        $position = $details['adv_position'] ?? 'Unknown';
        
        $oldStatusText = $oldStatus !== null ? ($oldStatus === 1 ? 'Active' : 'Inactive') : 'Unknown';
        $newStatusText = $newStatus === 1 ? 'Active' : 'Inactive';
        
        $logEntry = sprintf(
            "[%s] ADVERTISEMENT STATUS UPDATE | ID: %d | Title: %s | Category: %s | Position: %s | Old: %s (%d) | New: %s (%d) | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $title,
            $category,
            $position,
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
     * Check if advertisement exists
     * 
     * @param int $id Advertisement ID
     * @return bool
     */
    public function advertisementExists(int $id): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE adv_id = ?";
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
     * Check user permission
     * 
     * @return bool
     */
    public function checkPermission(): bool {
        return isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] > 0;
    }
    
    /**
     * Get status statistics for advertisement
     * 
     * @param int $id Advertisement ID
     * @return array Statistics
     */
    public function getStatusStats(int $id): array {
        // This would require an audit table - simplified version
        return [
            'advertisement_id' => $id,
            'current_status' => $this->getCurrentStatus($id)
        ];
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
    $updater = new AdvertisementStatusUpdater($con);
    
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
    
    // Check if advertisement exists
    if (!$updater->advertisementExists($validated['id'])) {
        $updater->sendResponse(false, 'Advertisement not found');
        exit;
    }
    
    // Get current status and details
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    $details = $updater->getAdvertisementDetails($validated['id']) ?? [];
    
    // Check if status is already the same
    if ($oldStatus === $validated['status']) {
        $statusText = $validated['status'] === 1 ? 'active' : 'inactive';
        $updater->sendResponse(true, "Advertisement already {$statusText}", [
            'advertisement_id' => $validated['id'],
            'status' => $validated['status']
        ]);
        exit;
    }
    
    // Update status
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $details);
    
    // Get updated stats
    $stats = $updater->getStatusStats($validated['id']);
    
    // Send success response
    $statusText = $validated['status'] === 1 ? 'activated' : 'deactivated';
    $updater->sendResponse(true, "Advertisement {$statusText} successfully", [
        'advertisement_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'title' => $details['adv_title'] ?? null,
        'stats' => $stats
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Advertisement status validation error: " . $e->getMessage());
    $updater = $updater ?? new AdvertisementStatusUpdater($con);
    $updater->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Advertisement status runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    
    $updater = $updater ?? new AdvertisementStatusUpdater($con);
    $updater->sendResponse(false, 'Failed to update advertisement status');
    
} catch (Exception $e) {
    error_log("Advertisement status unexpected error: " . $e->getMessage());
    $updater = $updater ?? new AdvertisementStatusUpdater($con);
    $updater->sendResponse(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>