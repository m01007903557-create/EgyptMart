<?php
/**
 * File: changeIndexContentStatus.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة محتوى الصفحة الرئيسية (تفعيل/تعطيل)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/index_content_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../common.php';

/**
 * Class IndexContentStatusUpdater
 */
class IndexContentStatusUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'index_content';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/index_content_updates.log';
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
        $sql = "SELECT ic_status FROM {$this->tableName} WHERE ic_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $status = (int)$row['ic_status'];
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
        $sql = "SELECT * FROM {$this->tableName} WHERE ic_id = ?";
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
        $sql = "UPDATE {$this->tableName} SET ic_status = ? WHERE ic_id = ?";
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
     * Get content section description
     * 
     * @param array $details Content details
     * @return string Section description
     */
    public function getSectionDescription(array $details): string {
        $section = $details['ic_section'] ?? 'general';
        $type = $details['ic_type'] ?? 'text';
        
        $sections = [
            'hero' => 'Hero Section',
            'about' => 'About Us',
            'services' => 'Services',
            'features' => 'Features',
            'testimonials' => 'Testimonials',
            'contact' => 'Contact',
            'footer' => 'Footer',
            'general' => 'General Content'
        ];
        
        $types = [
            'text' => 'Text',
            'image' => 'Image',
            'video' => 'Video',
            'slider' => 'Slider',
            'banner' => 'Banner',
            'icon' => 'Icon'
        ];
        
        $sectionName = $sections[$section] ?? "Section: $section";
        $typeName = $types[$type] ?? "Type: $type";
        
        return "$sectionName ($typeName)";
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
        
        $title = $details['ic_title'] ?? 'Untitled';
        $sectionDesc = $this->getSectionDescription($details);
        $contentPreview = isset($details['ic_content']) ? substr(strip_tags($details['ic_content']), 0, 50) . '...' : 'No content';
        $order = $details['ic_order'] ?? '0';
        
        $oldStatusText = $this->getStatusText($oldStatus ?? 0);
        $newStatusText = $this->getStatusText($newStatus);
        
        $logEntry = sprintf(
            "[%s] INDEX CONTENT STATUS UPDATE | ID: %d | Title: %s | Section: %s | Content: %s | Order: %s | Old: %s (%d) | New: %s (%d) | Admin: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $title,
            $sectionDesc,
            $contentPreview,
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
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE ic_id = ?";
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
                WHERE ic_section = ? AND ic_status = 1";
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
     * Count active content by type
     * 
     * @param string $type Content type
     * @return int Number of active content items
     */
    public function countActiveByType(string $type): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE ic_type = ? AND ic_status = 1";
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
     * Get total active content count
     * 
     * @return int Total active content items
     */
    public function getTotalActiveCount(): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE ic_status = 1";
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_assoc($result);
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Check if section has content limit
     * 
     * @param string $section Section name
     * @param int $limit Maximum allowed items
     * @return bool True if can add more
     */
    public function canActivateInSection(string $section, int $limit = 10): bool {
        $activeCount = $this->countActiveBySection($section);
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
    $updater = new IndexContentStatusUpdater($con);
    
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
    $section = $details['ic_section'] ?? 'general';
    $type = $details['ic_type'] ?? 'text';
    
    // If activating, check section limits
    if ($validated['status'] === 1) {
        $sectionActiveCount = $updater->countActiveBySection($section);
        $typeActiveCount = $updater->countActiveByType($type);
        
        // You can add section-specific limits here
        $sectionLimits = [
            'hero' => 1,      // Only one hero section
            'about' => 1,      // Only one about section
            'testimonials' => 10 // Max 10 testimonials
        ];
        
        if (isset($sectionLimits[$section]) && $sectionActiveCount >= $sectionLimits[$section]) {
            $updater->sendResponse(false, "Cannot activate: Maximum limit reached for section '$section'", [
                'section' => $section,
                'limit' => $sectionLimits[$section],
                'current' => $sectionActiveCount
            ]);
            exit;
        }
    }
    
    // Update status
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $details);
    
    // Get updated counts
    $sectionActiveCount = $updater->countActiveBySection($section);
    $typeActiveCount = $updater->countActiveByType($type);
    $totalActiveCount = $updater->getTotalActiveCount();
    
    // Send success response
    $statusText = $updater->getStatusText($validated['status']);
    $updater->sendResponse(true, "Index content {$statusText} successfully", [
        'content_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'title' => $details['ic_title'] ?? null,
        'section' => $section,
        'type' => $type,
        'section_active_count' => $sectionActiveCount,
        'type_active_count' => $typeActiveCount,
        'total_active_content' => $totalActiveCount
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Index content validation error: " . $e->getMessage());
    $updater = $updater ?? new IndexContentStatusUpdater($con);
    $updater->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Index content runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    
    $updater = $updater ?? new IndexContentStatusUpdater($con);
    $updater->sendResponse(false, 'Failed to update content status');
    
} catch (Exception $e) {
    error_log("Index content unexpected error: " . $e->getMessage());
    $updater = $updater ?? new IndexContentStatusUpdater($con);
    $updater->sendResponse(false, 'System error occurred');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>