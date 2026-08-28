<?php
/**
 * File: changeFeatureHeadingStatus.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة محتوى صفحة المميزات (تفعيل/تعطيل)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/feature_page_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../common.php';

/**
 * Class FeaturePageContentUpdater
 */
class FeaturePageContentUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'featurepage_content';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/feature_page_updates.log';
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
     * @param mixed $id Content ID
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
            throw new InvalidArgumentException('Invalid content ID');
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Get current content status
     * 
     * @param int $id Content ID
     * @return int|null Current status
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT fpc_status FROM {$this->tableName} WHERE fpc_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $status = (int)$row['fpc_status'];
            mysqli_stmt_close($stmt);
            return $status;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get content details
     * 
     * @param int $id Content ID
     * @return array|null Content details
     */
    public function getContentDetails(int $id): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE fpc_id = ?";
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
     * Update content status
     * 
     * @param int $status New status
     * @param int $id Content ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE {$this->tableName} SET fpc_status = ? WHERE fpc_id = ?";
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
            throw new RuntimeException('Content not found');
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
     * Get content type description
     * 
     * @param array $details Content details
     * @return string Type description
     */
    public function getContentTypeDescription(array $details): string {
        $type = $details['fpc_type'] ?? 'unknown';
        $section = $details['fpc_section'] ?? 'general';
        
        $types = [
            'text' => 'Text Content',
            'image' => 'Image',
            'video' => 'Video',
            'icon' => 'Icon',
            'feature' => 'Feature Box'
        ];
        
        $typeName = $types[$type] ?? "Type: $type";
        return "$typeName (Section: $section)";
    }
    
    /**
     * Log the status update
     * 
     * @param int $id Content ID
     * @param int|null $oldStatus Old status
     * @param int $newStatus New status
     * @param array $details Content details
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus, array $details = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $title = $details['fpc_title'] ?? 'Untitled';
        $typeDesc = $this->getContentTypeDescription($details);
        $section = $details['fpc_section'] ?? 'general';
        $order = $details['fpc_order'] ?? '0';
        
        $oldStatusText = $this->getStatusText($oldStatus ?? 0);
        $newStatusText = $this->getStatusText($newStatus);
        
        $logEntry = sprintf(
            "[%s] FEATURE PAGE CONTENT UPDATE | ID: %d | Title: %s | Type: %s | Section: %s | Order: %s | Old: %s (%d) | New: %s (%d) | Admin: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $title,
            $typeDesc,
            $section,
            $order,
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
     * Check if content exists
     * 
     * @param int $id Content ID
     * @return bool
     */
    public function contentExists(int $id): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE fpc_id = ?";
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
     * Count active content by section
     * 
     * @param string $section Section name
     * @return int Number of active content items
     */
    public function countActiveBySection(string $section): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE fpc_section = ? AND fpc_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $section);
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
}

// Main execution
try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed', 405);
    }
    
    // Initialize updater
    $updater = new FeaturePageContentUpdater($con);
    
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
    
    // Check if content exists
    if (!$updater->contentExists($validated['id'])) {
        $updater->sendResponse(false, 'Content not found');
        exit;
    }
    
    // Get current status and details
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    $details = $updater->getContentDetails($validated['id']) ?? [];
    
    // Check if status is already the same
    if ($oldStatus === $validated['status']) {
        $statusText = $updater->getStatusText($validated['status']);
        $updater->sendResponse(true, "Content already {$statusText}", [
            'content_id' => $validated['id'],
            'status' => $validated['status']
        ]);
        exit;
    }
    
    // Get section for additional info
    $section = $details['fpc_section'] ?? 'general';
    
    // If activating, check section limits (optional)
    if ($validated['status'] === 1) {
        $activeCount = $updater->countActiveBySection($section);
        // You can add section-specific limits here if needed
    }
    
    // Update status
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $details);
    
    // Get updated count for this section
    $sectionCount = $updater->countActiveBySection($section);
    
    // Send success response
    $statusText = $updater->getStatusText($validated['status']);
    $updater->sendResponse(true, "Feature page content {$statusText} successfully", [
        'content_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'title' => $details['fpc_title'] ?? null,
        'section' => $section,
        'section_active_count' => $sectionCount
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Feature page content validation error: " . $e->getMessage());
    $updater = $updater ?? new FeaturePageContentUpdater($con);
    $updater->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Feature page content runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    
    $updater = $updater ?? new FeaturePageContentUpdater($con);
    $updater->sendResponse(false, 'Failed to update content status');
    
} catch (Exception $e) {
    error_log("Feature page content unexpected error: " . $e->getMessage());
    $updater = $updater ?? new FeaturePageContentUpdater($con);
    $updater->sendResponse(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>