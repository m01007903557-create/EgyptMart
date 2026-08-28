<?php
/**
 * File: changesoftversionStatus.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة إصدارات البرامج في الصفحة الرئيسية (تفعيل/تعطيل)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/software_version_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../common.php';

/**
 * Class SoftwareVersionStatusUpdater
 */
class SoftwareVersionStatusUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'index_software_version';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/software_version_updates.log';
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
     * @param mixed $id Version ID
     * @return array{status: int, id: int}
     * @throws InvalidArgumentException
     */
    public function validateInput($stat, $id): array {
        if (!isset($stat) || !isset($id)) {
            throw new InvalidArgumentException('جميع الحقول مطلوبة');
        }
        
        $cleanStatus = filter_var(trim((string)$stat), FILTER_VALIDATE_INT);
        if ($cleanStatus === false) {
            throw new InvalidArgumentException('قيمة الحالة غير صالحة');
        }
        
        if (!in_array($cleanStatus, $this->allowedStatuses, true)) {
            throw new InvalidArgumentException('الحالة يجب أن تكون 0 أو 1 فقط');
        }
        
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException('رقم الإصدار غير صالح');
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Get current version status
     * 
     * @param int $id Version ID
     * @return int|null Current status
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT isv_status FROM {$this->tableName} WHERE isv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $status = (int)$row['isv_status'];
            mysqli_stmt_close($stmt);
            return $status;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get version details
     * 
     * @param int $id Version ID
     * @return array|null Version details
     */
    public function getVersionDetails(int $id): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE isv_id = ?";
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
     * Update version status
     * 
     * @param int $status New status
     * @param int $id Version ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE {$this->tableName} SET isv_status = ? WHERE isv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('فشل تحضير الاستعلام: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $status, $id);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('فشل تنفيذ التحديث: ' . $error);
        }
        
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected === 0) {
            throw new RuntimeException('الإصدار غير موجود');
        }
        
        return true;
    }
    
    /**
     * Get status text in Arabic
     * 
     * @param int $status Status value
     * @return string Status text in Arabic
     */
    public function getStatusText(int $status): string {
        return $status === 1 ? 'نشط' : 'غير نشط';
    }
    
    /**
     * Get version type description
     * 
     * @param array $details Version details
     * @return string Type description
     */
    public function getVersionTypeDescription(array $details): string {
        $type = $details['isv_type'] ?? 'stable';
        $platform = $details['isv_platform'] ?? 'all';
        
        $types = [
            'stable' => 'إصدار مستقر',
            'beta' => 'نسخة تجريبية',
            'alpha' => 'نسخة اختبارية',
            'legacy' => 'إصدار قديم',
            'lts' => 'دعم طويل المدى'
        ];
        
        $platforms = [
            'all' => 'جميع المنصات',
            'windows' => 'ويندوز',
            'mac' => 'ماك',
            'linux' => 'لينكس',
            'web' => 'ويب',
            'mobile' => 'جوال'
        ];
        
        $typeName = $types[$type] ?? "النوع: $type";
        $platformName = $platforms[$platform] ?? "المنصة: $platform";
        
        return "$typeName - $platformName";
    }
    
    /**
     * Log the status update
     * 
     * @param int $id Version ID
     * @param int|null $oldStatus Old status
     * @param int $newStatus New status
     * @param array $details Version details
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus, array $details = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $versionName = $details['isv_name'] ?? 'بدون اسم';
        $versionNumber = $details['isv_version'] ?? '0.0.0';
        $versionType = $this->getVersionTypeDescription($details);
        $releaseDate = $details['isv_release_date'] ?? 'غير محدد';
        $downloadUrl = $details['isv_download_url'] ?? 'لا يوجد';
        $changelog = isset($details['isv_changelog']) ? substr($details['isv_changelog'], 0, 50) . '...' : 'لا يوجد';
        
        $oldStatusText = $this->getStatusText($oldStatus ?? 0);
        $newStatusText = $this->getStatusText($newStatus);
        
        $logEntry = sprintf(
            "[%s] تحديث إصدار برنامج | ID: %d | الاسم: %s | الإصدار: %s | النوع: %s | تاريخ الإصدار: %s | التغييرات: %s | الرابط: %s | الحالة القديمة: %s (%d) | الحالة الجديدة: %s (%d) | المشرف: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $versionName,
            $versionNumber,
            $versionType,
            $releaseDate,
            $changelog,
            $downloadUrl,
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
     * Check if version exists
     * 
     * @param int $id Version ID
     * @return bool
     */
    public function versionExists(int $id): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE isv_id = ?";
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
     * Count active versions by type
     * 
     * @param string $type Version type
     * @return int Number of active versions
     */
    public function countActiveByType(string $type): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE isv_type = ? AND isv_status = 1";
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
     * Count active versions by platform
     * 
     * @param string $platform Platform name
     * @return int Number of active versions
     */
    public function countActiveByPlatform(string $platform): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE isv_platform = ? AND isv_status = 1";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $platform);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get total active versions count
     * 
     * @return int Total active versions
     */
    public function getTotalActiveCount(): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE isv_status = 1";
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_assoc($result);
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get latest active version
     * 
     * @return array|null Latest version details
     */
    public function getLatestActiveVersion(): ?array {
        $sql = "SELECT * FROM {$this->tableName} 
                WHERE isv_status = 1 
                ORDER BY isv_release_date DESC, isv_version DESC 
                LIMIT 1";
        
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            return null;
        }
        
        return mysqli_fetch_assoc($result) ?: null;
    }
    
    /**
     * Check if version number already exists
     * 
     * @param string $version Version number
     * @param int $excludeId Exclude this ID
     * @return bool True if exists
     */
    public function versionNumberExists(string $version, int $excludeId = 0): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE isv_version = ? AND isv_id != ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $version, $excludeId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Check if can activate (only one stable version per platform)
     * 
     * @param string $type Version type
     * @param string $platform Platform
     * @param int $excludeId Exclude this ID
     * @return bool True if can activate
     */
    public function canActivateStable(string $type, string $platform, int $excludeId = 0): bool {
        if ($type !== 'stable') {
            return true;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE isv_type = 'stable' 
                AND isv_platform = ? 
                AND isv_status = 1 
                AND isv_id != ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return true; // Allow if can't check
        }
        
        mysqli_stmt_bind_param($stmt, "si", $platform, $excludeId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) === 0; // Only one stable version per platform
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
     * Send JSON response in Arabic
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
        throw new RuntimeException('طريقة الطلب غير مسموحة', 405);
    }
    
    // Initialize updater
    $updater = new SoftwareVersionStatusUpdater($con);
    
    // Check permission
    if (!$updater->checkPermission()) {
        $updater->sendResponse(false, 'ليس لديك صلاحية للقيام بهذا الإجراء');
        exit;
    }
    
    // Validate required fields
    if (!isset($_POST['stat']) || !isset($_POST['id'])) {
        $updater->sendResponse(false, 'البيانات المطلوبة غير مكتملة');
        exit;
    }
    
    // Validate input
    $validated = $updater->validateInput($_POST['stat'], $_POST['id']);
    
    // Check if version exists
    if (!$updater->versionExists($validated['id'])) {
        $updater->sendResponse(false, 'الإصدار غير موجود');
        exit;
    }
    
    // Get current status and details
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    $details = $updater->getVersionDetails($validated['id']) ?? [];
    
    // Check if status is already the same
    if ($oldStatus === $validated['status']) {
        $statusText = $updater->getStatusText($validated['status']);
        $updater->sendResponse(true, "الإصدار بالفعل {$statusText}", [
            'version_id' => $validated['id'],
            'status' => $validated['status']
        ]);
        exit;
    }
    
    // Get version info
    $versionType = $details['isv_type'] ?? 'stable';
    $platform = $details['isv_platform'] ?? 'all';
    $versionNumber = $details['isv_version'] ?? '0.0.0';
    
    // If activating stable version, check if another stable exists for same platform
    if ($validated['status'] === 1) {
        if (!$updater->canActivateStable($versionType, $platform, $validated['id'])) {
            $updater->sendResponse(false, "لا يمكن التفعيل: يوجد إصدار مستقر نشط بالفعل لهذه المنصة", [
                'platform' => $platform,
                'version' => $versionNumber
            ]);
            exit;
        }
    }
    
    // Update status
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $details);
    
    // Get updated counts
    $typeActiveCount = $updater->countActiveByType($versionType);
    $platformActiveCount = $updater->countActiveByPlatform($platform);
    $totalActiveCount = $updater->getTotalActiveCount();
    $latestVersion = $updater->getLatestActiveVersion();
    
    // Send success response
    $statusText = $updater->getStatusText($validated['status']);
    $updater->sendResponse(true, "تم {$statusText} الإصدار بنجاح", [
        'version_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'name' => $details['isv_name'] ?? null,
        'version' => $versionNumber,
        'type' => $versionType,
        'platform' => $platform,
        'type_active_count' => $typeActiveCount,
        'platform_active_count' => $platformActiveCount,
        'total_active_versions' => $totalActiveCount,
        'latest_version' => $latestVersion
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Software version validation error: " . $e->getMessage());
    $updater = $updater ?? new SoftwareVersionStatusUpdater($con);
    $updater->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Software version runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'طريقة الطلب غير مسموحة']);
        exit;
    }
    
    $updater = $updater ?? new SoftwareVersionStatusUpdater($con);
    $updater->sendResponse(false, 'فشل تحديث حالة الإصدار');
    
} catch (Exception $e) {
    error_log("Software version unexpected error: " . $e->getMessage());
    $updater = $updater ?? new SoftwareVersionStatusUpdater($con);
    $updater->sendResponse(false, 'حدث خطأ في النظام');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>