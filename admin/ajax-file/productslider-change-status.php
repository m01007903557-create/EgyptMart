<?php
/**
 * File: productslider-change-status.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة عناصر سلايدر المنتجات والخدمات (تفعيل/تعطيل)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/prodservice_slider_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../common.php';

/**
 * Class ProdServiceSliderUpdater
 */
class ProdServiceSliderUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'prodservice_slider';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/prodservice_slider_updates.log';
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
     * @param mixed $stat Status value
     * @param mixed $id Item ID
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
            throw new InvalidArgumentException('Invalid ID value');
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Get current status
     * 
     * @param int $id Item ID
     * @return int|null
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
     * Get item details
     * 
     * @param int $id Item ID
     * @return array|null
     */
    public function getItemDetails(int $id): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE adv_id = ?";
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
     * Update status
     * 
     * @param int $status New status
     * @param int $id Item ID
     * @return bool
     * @throws RuntimeException
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE {$this->tableName} SET adv_status = ? WHERE adv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Prepare failed: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $status, $id);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Execute failed: ' . $error);
        }
        
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected === 0) {
            throw new RuntimeException('Item not found');
        }
        
        return true;
    }
    
    /**
     * Log the update
     * 
     * @param int $id Item ID
     * @param int|null $oldStatus Old status
     * @param int $newStatus New status
     * @param array $itemDetails Item details
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus, array $itemDetails = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $title = $itemDetails['title'] ?? $itemDetails['adv_title'] ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] ProdService Slider Update | ID: %d | Title: %s | Old: %s | New: %d | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $title,
            $oldStatus !== null ? ($oldStatus === 1 ? 'Active' : 'Inactive') : 'Unknown',
            $newStatus,
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
}

// Main execution
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed', 405);
    }
    
    $updater = new ProdServiceSliderUpdater($con);
    
    if (!$updater->checkPermission()) {
        throw new RuntimeException('Unauthorized', 403);
    }
    
    if (!isset($_POST['stat']) || !isset($_POST['id'])) {
        throw new InvalidArgumentException('Missing required fields');
    }
    
    $validated = $updater->validateInput($_POST['stat'], $_POST['id']);
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    $itemDetails = $updater->getItemDetails($validated['id']) ?? [];
    
    $updater->updateStatus($validated['status'], $validated['id']);
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $itemDetails);
    
    // Return success response for AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'data' => [
            'id' => $validated['id'],
            'old_status' => $oldStatus,
            'new_status' => $validated['status']
        ]
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Validation error: " . $e->getMessage());
    header('Content-Type: application/json', true, 400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    
} catch (RuntimeException $e) {
    $code = $e->getCode() ?: 500;
    error_log("Runtime error: " . $e->getMessage());
    header('Content-Type: application/json', true, $code);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    
} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode(['success' => false, 'error' => 'System error occurred']);
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>