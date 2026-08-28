<?php
/**
 * File: changesliderContentStatus.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تحديث حالة سلايدر الهيدر (الصور المتحركة في أعلى الصفحة) - تفعيل/تعطيل
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/header_slider_errors.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../common.php';

/**
 * Class HeaderSliderStatusUpdater
 */
class HeaderSliderStatusUpdater {
    private mysqli $db;
    private array $allowedStatuses = [0, 1];
    private string $tableName = 'header_slider';
    private string $logFile;
    
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/header_slider_updates.log';
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
     * @param mixed $id Slider ID
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
            throw new InvalidArgumentException('رقم السلايدر غير صالح');
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Get current slider status
     * 
     * @param int $id Slider ID
     * @return int|null Current status
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT hs_status FROM {$this->tableName} WHERE hs_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $status = (int)$row['hs_status'];
            mysqli_stmt_close($stmt);
            return $status;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get slider details
     * 
     * @param int $id Slider ID
     * @return array|null Slider details
     */
    public function getSliderDetails(int $id): ?array {
        $sql = "SELECT * FROM {$this->tableName} WHERE hs_id = ?";
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
     * Update slider status
     * 
     * @param int $status New status
     * @param int $id Slider ID
     * @return bool Success status
     * @throws RuntimeException
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE {$this->tableName} SET hs_status = ? WHERE hs_id = ?";
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
            throw new RuntimeException('السلايدر غير موجود');
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
     * Get slider position description
     * 
     * @param array $details Slider details
     * @return string Position description
     */
    public function getPositionDescription(array $details): string {
        $position = $details['hs_position'] ?? 'main';
        $device = $details['hs_device'] ?? 'all';
        
        $positions = [
            'main' => 'السلايدر الرئيسي',
            'top' => 'أعلى الصفحة',
            'middle' => 'وسط الصفحة',
            'bottom' => 'أسفل الصفحة',
            'full' => 'عرض كامل'
        ];
        
        $devices = [
            'all' => 'جميع الأجهزة',
            'desktop' => 'سطح المكتب فقط',
            'mobile' => 'الجوال فقط',
            'tablet' => 'التابلت فقط'
        ];
        
        $positionName = $positions[$position] ?? "الموضع: $position";
        $deviceName = $devices[$device] ?? "الجهاز: $device";
        
        return "$positionName ($deviceName)";
    }
    
    /**
     * Log the status update
     * 
     * @param int $id Slider ID
     * @param int|null $oldStatus Old status
     * @param int $newStatus New status
     * @param array $details Slider details
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus, array $details = []): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $title = $details['hs_title'] ?? 'بدون عنوان';
        $image = $details['hs_image'] ?? 'لا توجد صورة';
        $link = $details['hs_link'] ?? 'لا يوجد رابط';
        $positionDesc = $this->getPositionDescription($details);
        $order = $details['hs_order'] ?? '0';
        
        $oldStatusText = $this->getStatusText($oldStatus ?? 0);
        $newStatusText = $this->getStatusText($newStatus);
        
        $logEntry = sprintf(
            "[%s] تحديث سلايدر الهيدر | ID: %d | العنوان: %s | الصورة: %s | الرابط: %s | الموضع: %s | الترتيب: %s | الحالة القديمة: %s (%d) | الحالة الجديدة: %s (%d) | المشرف: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $title,
            $image,
            $link,
            $positionDesc,
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
     * Check if slider exists
     * 
     * @param int $id Slider ID
     * @return bool
     */
    public function sliderExists(int $id): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE hs_id = ?";
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
     * Count active sliders by position
     * 
     * @param string $position Position name
     * @return int Number of active sliders
     */
    public function countActiveByPosition(string $position): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} 
                WHERE hs_position = ? AND hs_status = 1";
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
     * Get total active sliders count
     * 
     * @return int Total active sliders
     */
    public function getTotalActiveCount(): int {
        $sql = "SELECT COUNT(*) as count FROM {$this->tableName} WHERE hs_status = 1";
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_assoc($result);
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get next order number for position
     * 
     * @param string $position Position name
     * @return int Next order number
     */
    public function getNextOrder(string $position): int {
        $sql = "SELECT MAX(hs_order) as max_order FROM {$this->tableName} 
                WHERE hs_position = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 1;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $position);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ((int)($row['max_order'] ?? 0)) + 1;
    }
    
    /**
     * Check if position has slider limit
     * 
     * @param string $position Position name
     * @param int $limit Maximum allowed sliders
     * @return bool True if can add more
     */
    public function canActivateInPosition(string $position, int $limit = 10): bool {
        $activeCount = $this->countActiveByPosition($position);
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
    $updater = new HeaderSliderStatusUpdater($con);
    
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
    
    // Check if slider exists
    if (!$updater->sliderExists($validated['id'])) {
        $updater->sendResponse(false, 'السلايدر غير موجود');
        exit;
    }
    
    // Get current status and details
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    $details = $updater->getSliderDetails($validated['id']) ?? [];
    
    // Check if status is already the same
    if ($oldStatus === $validated['status']) {
        $statusText = $updater->getStatusText($validated['status']);
        $updater->sendResponse(true, "السلايدر بالفعل {$statusText}", [
            'slider_id' => $validated['id'],
            'status' => $validated['status']
        ]);
        exit;
    }
    
    // Get position for additional info
    $position = $details['hs_position'] ?? 'main';
    
    // If activating, check position limits
    if ($validated['status'] === 1) {
        $positionActiveCount = $updater->countActiveByPosition($position);
        
        // Position-specific limits
        $positionLimits = [
            'main' => 5,    // Main slider: max 5 slides
            'top' => 3,      // Top position: max 3 slides
            'middle' => 4,   // Middle position: max 4 slides
            'bottom' => 3,   // Bottom position: max 3 slides
            'full' => 2      // Full width: max 2 slides
        ];
        
        if (isset($positionLimits[$position]) && $positionActiveCount >= $positionLimits[$position]) {
            $updater->sendResponse(false, "لا يمكن التفعيل: تم الوصول للحد الأقصى لهذا الموضع", [
                'position' => $position,
                'limit' => $positionLimits[$position],
                'current' => $positionActiveCount
            ]);
            exit;
        }
    }
    
    // Update status
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status'], $details);
    
    // Get updated counts
    $positionActiveCount = $updater->countActiveByPosition($position);
    $totalActiveCount = $updater->getTotalActiveCount();
    $nextOrder = $updater->getNextOrder($position);
    
    // Send success response
    $statusText = $updater->getStatusText($validated['status']);
    $updater->sendResponse(true, "تم {$statusText} السلايدر بنجاح", [
        'slider_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'title' => $details['hs_title'] ?? null,
        'image' => $details['hs_image'] ?? null,
        'position' => $position,
        'position_active_count' => $positionActiveCount,
        'total_active_sliders' => $totalActiveCount,
        'next_order' => $nextOrder
    ]);
    
} catch (InvalidArgumentException $e) {
    error_log("Header slider validation error: " . $e->getMessage());
    $updater = $updater ?? new HeaderSliderStatusUpdater($con);
    $updater->sendResponse(false, $e->getMessage());
    
} catch (RuntimeException $e) {
    error_log("Header slider runtime error: " . $e->getMessage());
    
    if ($e->getCode() === 405) {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'طريقة الطلب غير مسموحة']);
        exit;
    }
    
    $updater = $updater ?? new HeaderSliderStatusUpdater($con);
    $updater->sendResponse(false, 'فشل تحديث حالة السلايدر');
    
} catch (Exception $e) {
    error_log("Header slider unexpected error: " . $e->getMessage());
    $updater = $updater ?? new HeaderSliderStatusUpdater($con);
    $updater->sendResponse(false, 'حدث خطأ في النظام');
    
} finally {
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>