<?php
/**
 * File: change_gig_status.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة الخدمات المصغرة (Gigs) - تفعيل/تعطيل
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/gig_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../common.php";

/**
 * Class GigStatusUpdater
 */
class GigStatusUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'gig';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/gig_updates.log';
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
     * @param mixed $status Status value (0 or 1)
     * @param mixed $id Gig ID
     * @return array{status: int, id: int}
     * @throws InvalidArgumentException
     */
    public function validateInput($status, $id): array {
        if (!isset($status) || !isset($id)) {
            throw new InvalidArgumentException('All fields are required');
        }
        
        $cleanStatus = filter_var(trim((string)$status), FILTER_VALIDATE_INT);
        if ($cleanStatus === false) {
            throw new InvalidArgumentException('Invalid status value');
        }
        
        if (!in_array($cleanStatus, $this->allowedStatuses, true)) {
            throw new InvalidArgumentException('Status must be 0 or 1');
        }
        
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('Invalid gig ID');
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Get current gig status
     * 
     * @param int $id Gig ID
     * @return int|null Current status
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT g_status FROM {$this->tableName} WHERE g_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $status = (int)$row['g_status'];
            mysqli_stmt_close($stmt);
            return $status;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get gig details
     * 
     * @param int $id Gig ID
     * @return array|null Gig details
     */
    public function getGigDetails(int $id): ?array {
        $sql = "SELECT g.*, u.fname, u.lname, u.email 
                FROM {$this->tableName} g
                LEFT JOIN user u ON g.g_uid = u.usr_id
                WHERE g.g_id = ?";
        
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
     * Update gig status
     * 
     * @param int $status New status
     * @param int $id Gig ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE {$this->tableName} SET g_status = ? WHERE g_id = ?";
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
            throw new RuntimeException('Gig not found');
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
     * Log the status update
     * 
     * @param int $id Gig ID
     * @param int|null $oldStatus Old status
     * @param int $newStatus New status
     * @param array $details Gig details
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus, array $details = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $gigTitle = $details['g_title'] ?? 'Unknown';
        $gigPrice = $details['g_price'] ?? '0';
        $ownerName = trim(($details['fname'] ?? '') . ' ' . ($details['lname'] ?? ''));
        $ownerEmail = $details['email'] ?? 'Unknown';
        
        $oldStatusText = $this->getStatusText($oldStatus ?? 0);
        $newStatusText = $this->getStatusText($newStatus);
        
        $logEntry = sprintf(
            "[%s] GIG STATUS UPDATE | ID: %d | Title: %s | Price: %s | Owner: %s (%s) | Old: %s (%d) | New: %s (%d) | Admin: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $gigTitle,
            $gigPrice,
            $ownerName,
            $ownerEmail,
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
     * Check if gig exists
     * 
     * @param int $id Gig ID
     * @return bool
     */
    public function gigExists(int $id): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE g_id = ?";
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
     * Count active gigs by user
     * 
     * @param int $userId User ID
     * @return int Number of active gigs
     */
    public function countActiveGigsByUser(int $userId): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE g_uid = ? AND g_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
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
    
    /**
     * Handle redirect for non-AJAX requests
     * 
     * @param bool $success Success status
     * @param string $message Message
     */
    public function handleRedirect(bool $success, string $message): void {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->sendResponse($success, $message);
            exit;
        }
        
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'gigs.php';
        $param = $success ? 'success' : 'error';
        header("Location: $redirectUrl?$param=" . urlencode($message));
        exit;
    }
}

// Main execution
try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed', 405);
    }
    
    // Initialize updater
    $updater = new GigStatusUpdater($con);
    
    // Check permission
    if (!$updater->checkPermission()) {
        $updater->handleRedirect(false, 'Unauthorized access');
        exit;
    }
    
    // Validate required fields
    if (!isset($_POST['stat']) || !isset($_POST['id'])) {
        $updater->handleRedirect(false, 'Missing required fields');
        exit;
    }
    
    // Validate input
    $validated = $updater->validateInput($_POST['stat'], $_POST['id']);
    
    // Check if gig exists
    if (!$updater->gigExists($validated['id'])) {
        $updater->handleRedirect(false, 'Gig not found');
        exit;
    }
    
    // Get current status and details
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    $details = $updater->getGigDetails($validated['id']) ?? [];
    
    // Check if status is already the same
    if ($oldStatus === $validated['status']) {
        $statusText = $updater->getStatusText($validated['status']);
        $updater->handleRedirect(true, "Gig already {$statusText}", [
            'gig_id' => $validated['id'],
            'status' => $validated['status']
        ]);
        exit;
    }
    
    // If activating, check limits (optional)
    if ($validated['status'] === 1 && isset($details['g_uid'])) {
        $activeCount = $updater->countActiveGigsByUser((int)$details['g_uid']);
        // You can add user-specific limits here if needed
    }
    
    // Update status
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $details);
    
    // Get updated active count for this user
    $userActiveCount = isset($details['g_uid']) ? 
        $updater->countActiveGigsByUser((int)$details['g_uid']) : 0;
    
    // Send success response
    $statusText = $updater->getStatusText($validated['status']);
    $updater->handleRedirect(true, "Gig {$statusText} successfully", [
        'gig_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'title' => $details['g_title'] ?? null,
        'user_active_gigs' => $userActiveCount
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Gig status validation error: " . $e->getMessage());
    $updater = $updater ?? new GigStatusUpdater($con);
    $updater->handleRedirect(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Gig status runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo 'Method not allowed';
        exit;
    }
    
    $updater = $updater ?? new GigStatusUpdater($con);
    $updater->handleRedirect(false, 'Failed to update gig status');
    
} catch (Exception $e) {
    error_log("Gig status unexpected error: " . $e->getMessage());
    $updater = $updater ?? new GigStatusUpdater($con);
    $updater->handleRedirect(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>