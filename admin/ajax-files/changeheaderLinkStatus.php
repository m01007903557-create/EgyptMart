<?php
/**
 * File: changeheaderLinkStatus.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة روابط الهيدر (القائمة العلوية) - تفعيل/تعطيل
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/header_link_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../common.php';

/**
 * Class HeaderLinkStatusUpdater
 */
class HeaderLinkStatusUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'header_link';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/header_link_updates.log';
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
     * @param mixed $id Header link ID
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
            throw new InvalidArgumentException('Invalid header link ID');
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Get current header link status
     * 
     * @param int $id Header link ID
     * @return int|null Current status
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT hl_status FROM {$this->tableName} WHERE hl_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $status = (int)$row['hl_status'];
            mysqli_stmt_close($stmt);
            return $status;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get header link details
     * 
     * @param int $id Header link ID
     * @return array|null Header link details
     */
    public function getLinkDetails(int $id): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE hl_id = ?";
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
     * Update header link status
     * 
     * @param int $status New status
     * @param int $id Header link ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE {$this->tableName} SET hl_status = ? WHERE hl_id = ?";
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
            throw new RuntimeException('Header link not found');
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
     * Get link position description
     * 
     * @param array $details Link details
     * @return string Position description
     */
    public function getPositionDescription(array $details): string {
        $position = $details['hl_position'] ?? 'main';
        $parent = $details['hl_parent'] ?? 0;
        
        $positions = [
            'main' => 'Main Menu',
            'top' => 'Top Bar',
            'bottom' => 'Bottom Bar',
            'sidebar' => 'Sidebar',
            'footer' => 'Footer',
            'user' => 'User Menu'
        ];
        
        $positionName = $positions[$position] ?? "Position: $position";
        
        if ($parent > 0) {
            return "$positionName (Submenu)";
        }
        
        return $positionName;
    }
    
    /**
     * Log the status update
     * 
     * @param int $id Header link ID
     * @param int|null $oldStatus Old status
     * @param int $newStatus New status
     * @param array $details Link details
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus, array $details = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $linkName = $details['hl_name'] ?? 'Unnamed Link';
        $linkUrl = $details['hl_url'] ?? '#';
        $linkPosition = $this->getPositionDescription($details);
        $linkOrder = $details['hl_order'] ?? '0';
        $linkTarget = isset($details['hl_target']) && $details['hl_target'] === '_blank' ? 'New Window' : 'Same Window';
        
        $oldStatusText = $this->getStatusText($oldStatus ?? 0);
        $newStatusText = $this->getStatusText($newStatus);
        
        $logEntry = sprintf(
            "[%s] HEADER LINK STATUS UPDATE | ID: %d | Name: %s | URL: %s | Position: %s | Order: %s | Target: %s | Old: %s (%d) | New: %s (%d) | Admin: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $linkName,
            $linkUrl,
            $linkPosition,
            $linkOrder,
            $linkTarget,
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
     * Check if header link exists
     * 
     * @param int $id Header link ID
     * @return bool
     */
    public function linkExists(int $id): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE hl_id = ?";
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
     * Count active links by position
     * 
     * @param string $position Position name
     * @return int Number of active links
     */
    public function countActiveByPosition(string $position): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE hl_position = ? AND hl_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $position);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get total active links count
     * 
     * @return int Total active links
     */
    public function getTotalActiveCount(): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE hl_status = 1";
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_assoc($result);
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get links hierarchy for logging
     * 
     * @param int $id Link ID
     * @return array Link hierarchy
     */
    public function getLinkHierarchy(int $id): array {
        $hierarchy = [];
        $currentId = $id;
        
        while ($currentId > 0) {
            $sql = "SELECT hl_id, hl_name, hl_parent FROM {$this->tableName} WHERE hl_id = ?";
            $stmt = mysqli_prepare($this->db, $sql);
            
            if (!$stmt) {
                break;
            }
            
            mysqli_stmt_bind_param($stmt, "i", $currentId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                array_unshift($hierarchy, [
                    'id' => (int)$row['hl_id'],
                    'name' => $row['hl_name']
                ]);
                $currentId = (int)$row['hl_parent'];
            } else {
                break;
            }
            
            mysqli_stmt_close($stmt);
        }
        
        return $hierarchy;
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
    $updater = new HeaderLinkStatusUpdater($con);
    
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
    
    // Check if link exists
    if (!$updater->linkExists($validated['id'])) {
        $updater->sendResponse(false, 'Header link not found');
        exit;
    }
    
    // Get current status and details
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    $details = $updater->getLinkDetails($validated['id']) ?? [];
    
    // Check if status is already the same
    if ($oldStatus === $validated['status']) {
        $statusText = $updater->getStatusText($validated['status']);
        $updater->sendResponse(true, "Header link already {$statusText}", [
            'link_id' => $validated['id'],
            'status' => $validated['status']
        ]);
        exit;
    }
    
    // Get position for additional info
    $position = $details['hl_position'] ?? 'main';
    
    // If activating, check limits (optional)
    if ($validated['status'] === 1) {
        $positionActiveCount = $updater->countActiveByPosition($position);
        // You can add position-specific limits here if needed
    }
    
    // Get link hierarchy
    $hierarchy = $updater->getLinkHierarchy($validated['id']);
    
    // Update status
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $details);
    
    // Get updated counts
    $positionActiveCount = $updater->countActiveByPosition($position);
    $totalActiveCount = $updater->getTotalActiveCount();
    
    // Send success response
    $statusText = $updater->getStatusText($validated['status']);
    $updater->sendResponse(true, "Header link {$statusText} successfully", [
        'link_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'name' => $details['hl_name'] ?? null,
        'url' => $details['hl_url'] ?? null,
        'position' => $position,
        'hierarchy' => $hierarchy,
        'position_active_count' => $positionActiveCount,
        'total_active_links' => $totalActiveCount
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Header link status validation error: " . $e->getMessage());
    $updater = $updater ?? new HeaderLinkStatusUpdater($con);
    $updater->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Header link status runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    
    $updater = $updater ?? new HeaderLinkStatusUpdater($con);
    $updater->sendResponse(false, 'Failed to update header link status');
    
} catch (Exception $e) {
    error_log("Header link status unexpected error: " . $e->getMessage());
    $updater = $updater ?? new HeaderLinkStatusUpdater($con);
    $updater->sendResponse(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>