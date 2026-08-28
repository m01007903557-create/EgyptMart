<?php
/**
 * File: supp-change-status.php

 * Version: 1.0.0
 * Last Updated: 2025-03-15
 * 
 * Description: تحديث حالة شعارات الموردين (تفعيل/تعطيل) في السلايدر
 * Purpose: This script handles updating the active status of supplier logos in the slider
 * 
 * @package SupplierLogo
 * @subpackage Admin
 * @category AJAX Handler
 * @author System Admin
 * @copyright 2025 EgyptMart
 * @license https://egyptmart.online/license Commercial
 * 
 * PHP Version 8.3
 * 
 * Changelog:
 * v1.0.0 - 2025-03-15
 * - ترقية الكود إلى PHP 8.3
 * - إضافة prepared statements للحماية من SQL Injection
 * - إضافة نظام تسجيل العمليات (Audit Trail)
 * - تحسين معالجة الأخطاء
 * - إضافة التحقق من الصلاحيات
 */

declare(strict_types=1);

// تمكين تسجيل الأخطاء للتطوير (قم بتعطيله في الإنتاج)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');

// بدء الجلسة إذا لم تكن قد بدأت بعد
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين ملف الاتصال بقاعدة البيانات
require_once '../../common.php';

/**
 * Class SupplierLogoUpdater
 * 
 * إدارة تحديث حالة شعارات الموردين
 * 
 * @package SupplierLogo
 * @author System Admin
 * @version 1.0.0
 */
class SupplierLogoUpdater {
    
    /**
     * اتصال قاعدة البيانات
     * @var mysqli
     */
    private mysqli $db;
    
    /**
     * قيم الحالة المسموح بها
     * @var array<int>
     */
    private array $allowedStatuses = [0, 1];
    
    /**
     * مسار ملف سجل التحديثات
     * @var string
     */
    private string $logFile;
    
    /**
     * اسم الجدول في قاعدة البيانات
     * @var string
     */
    private string $tableName = 'supplier_logo';
    
    /**
     * اسم جدول سجل التدقيق
     * @var string
     */
    private string $auditTable = 'supplier_logo_audit';
    
    /**
     * Constructor
     * 
     * @param mysqli $database اتصال قاعدة البيانات
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/supplier_logo_updates.log';
        
        // التأكد من وجود مجلد السجلات
        $this->ensureLogDirectoryExists();
        
        // التأكد من وجود جدول التدقيق
        $this->ensureAuditTableExists();
    }
    
    /**
     * التأكد من وجود مجلد السجلات
     * 
     * @return void
     */
    private function ensureLogDirectoryExists(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * التأكد من وجود جدول التدقيق
     * 
     * @return void
     */
    private function ensureAuditTableExists(): void {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->auditTable} (
            audit_id INT AUTO_INCREMENT PRIMARY KEY,
            logo_id INT NOT NULL,
            old_status TINYINT(1),
            new_status TINYINT(1) NOT NULL,
            user_id INT NOT NULL,
            user_ip VARCHAR(45),
            user_agent TEXT,
            action_date DATETIME NOT NULL,
            INDEX idx_logo_id (logo_id),
            INDEX idx_action_date (action_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        mysqli_query($this->db, $sql);
    }
    
    /**
     * التحقق من صحة المدخلات
     * 
     * @param mixed $stat قيمة الحالة المرسلة
     * @param mixed $id رقم الشعار المرسل
     * @return array{status: int, id: int} البيانات المحققة
     * @throws InvalidArgumentException إذا كانت البيانات غير صالحة
     */
    public function validateInput($stat, $id): array {
        
        // التحقق من وجود القيم
        if (!isset($stat) || !isset($id)) {
            throw new InvalidArgumentException('جميع الحقول مطلوبة');
        }
        
        // تنظيف وتحويل الحالة
        $cleanStatus = filter_var(trim((string)$stat), FILTER_VALIDATE_INT);
        if ($cleanStatus === false) {
            throw new InvalidArgumentException('قيمة الحالة غير صالحة');
        }
        
        // التحقق من أن الحالة مسموح بها (0 أو 1)
        if (!in_array($cleanStatus, $this->allowedStatuses, true)) {
            throw new InvalidArgumentException(
                'الحالة يجب أن تكون 0 أو 1 فقط. القيمة المرسلة: ' . htmlspecialchars((string)$stat)
            );
        }
        
        // تنظيف وتحويل رقم الشعار
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException(
                'رقم الشعار غير صالح. يجب أن يكون رقماً موجباً'
            );
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * الحصول على الحالة القديمة للشعار
     * 
     * @param int $id رقم الشعار
     * @return int|null الحالة القديمة أو null إذا لم يوجد
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT adv_status FROM {$this->tableName} WHERE adv_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            error_log("فشل تحضير استعلام جلب الحالة: " . mysqli_error($this->db));
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
     * الحصول على معلومات الشعار
     * 
     * @param int $id رقم الشعار
     * @return array|null معلومات الشعار
     */
    public function getLogoInfo(int $id): ?array {
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
     * تحديث حالة الشعار في قاعدة البيانات
     * 
     * @param int $status الحالة الجديدة
     * @param int $id رقم الشعار
     * @return bool نجاح أو فشل العملية
     * @throws RuntimeException إذا فشلت عملية التحديث
     */
    public function updateStatus(int $status, int $id): bool {
        
        // استخدام prepared statement لمنع SQL Injection
        $sql = "UPDATE {$this->tableName} SET adv_status = ? WHERE adv_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            throw new RuntimeException(
                'خطأ في تحضير الاستعلام: ' . mysqli_error($this->db)
            );
        }
        
        // ربط المتغيرات (كلاهما أرقام صحيحة)
        mysqli_stmt_bind_param($stmt, "ii", $status, $id);
        
        // تنفيذ الاستعلام
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('فشل تنفيذ التحديث: ' . $error);
        }
        
        // التحقق من عدد الصفوف المتأثرة
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        
        mysqli_stmt_close($stmt);
        
        // إذا كان عدد الصفوف المتأثرة 0، هذا يعني أن الشعار غير موجود
        if ($affectedRows === 0) {
            throw new RuntimeException('الشعار غير موجود في قاعدة البيانات');
        }
        
        return true;
    }
    
    /**
     * تسجيل عملية التحديث
     * 
     * @param int $id رقم الشعار
     * @param int|null $oldStatus الحالة القديمة
     * @param int $newStatus الحالة الجديدة
     * @return void
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus): void {
        
        // معلومات المستخدم
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // معلومات الشعار
        $logoInfo = $this->getLogoInfo($id);
        $supplierName = $logoInfo['supplier_name'] ?? 'غير معروف';
        
        // بناء سجل التحديث (نسخة نصية)
        $logEntry = sprintf(
            "[%s] تحديث شعار مورد | ID: %d | المورد: %s | حالة قديمة: %s | حالة جديدة: %d | مستخدم: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $supplierName,
            $oldStatus !== null ? ($oldStatus === 1 ? 'نشط' : 'غير نشط') : 'غير معروفة',
            $newStatus,
            $userId,
            $userIp
        );
        
        // حفظ في ملف السجلات
        error_log($logEntry, 3, $this->logFile);
        
        // حفظ في جدول التدقيق
        $this->saveAuditTrail($id, $oldStatus, $newStatus, $userId, $userIp, $userAgent);
    }
    
    /**
     * حفظ سجل التدقيق في قاعدة البيانات
     * 
     * @param int $logoId رقم الشعار
     * @param int|null $oldStatus الحالة القديمة
     * @param int $newStatus الحالة الجديدة
     * @param int $userId رقم المستخدم
     * @param string $userIp عنوان IP
     * @param string $userAgent معلومات المتصفح
     * @return void
     */
    private function saveAuditTrail(int $logoId, ?int $oldStatus, int $newStatus, int $userId, string $userIp, string $userAgent): void {
        
        $sql = "INSERT INTO {$this->auditTable} 
                (logo_id, old_status, new_status, user_id, user_ip, user_agent, action_date) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            error_log("فشل حفظ سجل التدقيق: " . mysqli_error($this->db));
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "iiiisss", $logoId, $oldStatus, $newStatus, $userId, $userIp, $userAgent);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    /**
     * التحقق من صلاحية المستخدم
     * 
     * @return bool هل المستخدم مصرح له
     */
    public function checkPermission(): bool {
        
        // التحقق من تسجيل الدخول
        if (!isset($_SESSION['uid_indm'])) {
            error_log("محاولة وصول غير مصرح بها من IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            return false;
        }
        
        // يمكن إضافة فحص الصلاحيات هنا
        // مثلاً التحقق من أن المستخدم لديه صلاحية المشرف
        
        return true;
    }
    
    /**
     * الحصول على إحصائيات التحديثات
     * 
     * @param int $logoId رقم الشعار
     * @return array إحصائيات التحديثات
     */
    public function getUpdateStats(int $logoId): array {
        $stats = [
            'total_updates' => 0,
            'last_update' => null,
            'activated_count' => 0,
            'deactivated_count' => 0
        ];
        
        $sql = "SELECT 
                COUNT(*) as total,
                MAX(action_date) as last_update,
                SUM(CASE WHEN new_status = 1 THEN 1 ELSE 0 END) as activated,
                SUM(CASE WHEN new_status = 0 THEN 1 ELSE 0 END) as deactivated
                FROM {$this->auditTable}
                WHERE logo_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $logoId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $stats['total_updates'] = (int)$row['total'];
                $stats['last_update'] = $row['last_update'];
                $stats['activated_count'] = (int)$row['activated'];
                $stats['deactivated_count'] = (int)$row['deactivated'];
            }
            
            mysqli_stmt_close($stmt);
        }
        
        return $stats;
    }
}

/**
 * Class ResponseHandler
 * 
 * معالجة الاستجابات المرسلة للواجهة
 * 
 * @package SupplierLogo
 * @author System Admin
 * @version 1.0.0
 */
class ResponseHandler {
    
    /**
     * إرسال استجابة نجاح
     * 
     * @param array $data بيانات إضافية
     * @param string $message رسالة النجاح
     * @return void
     */
    public static function success(array $data = [], string $message = 'تم تحديث حالة الشعار بنجاح'): void {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * إرسال استجابة خطأ
     * 
     * @param string $message رسالة الخطأ
     * @param int $statusCode كود حالة HTTP
     * @param array $details تفاصيل إضافية
     * @return void
     */
    public static function error(string $message, int $statusCode = 400, array $details = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'error' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($details)) {
            $response['details'] = $details;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * إرسال استجابة غير مصرح بها
     * 
     * @return void
     */
    public static function unauthorized(): void {
        self::error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
    }
    
    /**
     * إرسال استجابة طريقة غير مسموحة
     * 
     * @return void
     */
    public static function methodNotAllowed(): void {
        self::error('طريقة الطلب غير مسموحة. استخدم POST فقط.', 405);
    }
}

/**
 * التنفيذ الرئيسي
 */
try {
    
    // التحقق من أن الطلب هو POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ResponseHandler::methodNotAllowed();
        exit;
    }
    
    // إنشاء كائن المحدّث
    $updater = new SupplierLogoUpdater($con);
    
    // التحقق من الصلاحيات
    if (!$updater->checkPermission()) {
        ResponseHandler::unauthorized();
        exit;
    }
    
    // التحقق من وجود البيانات المطلوبة
    if (!isset($_POST['stat']) || !isset($_POST['id'])) {
        ResponseHandler::error('البيانات المطلوبة غير مكتملة. stat و id مطلوبان');
        exit;
    }
    
    // التحقق من صحة المدخلات
    $validated = $updater->validateInput($_POST['stat'], $_POST['id']);
    
    // الحصول على الحالة القديمة للتسجيل
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    
    // الحصول على معلومات الشعار
    $logoInfo = $updater->getLogoInfo($validated['id']);
    
    // تحديث الحالة
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    if (!$updated) {
        throw new RuntimeException('فشل تحديث حالة الشعار');
    }
    
    // تسجيل العملية
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status']);
    
    // الحصول على إحصائيات التحديثات
    $stats = $updater->getUpdateStats($validated['id']);
    
    // إرسال استجابة نجاح
    ResponseHandler::success([
        'logo_id' => $validated['id'],
        'supplier_name' => $logoInfo['supplier_name'] ?? null,
        'old_status' => $oldStatus,
        'new_status' => $validated['status'],
        'status_text' => $validated['status'] === 1 ? 'نشط' : 'غير نشط',
        'statistics' => $stats
    ]);
    
} catch (InvalidArgumentException $e) {
    // أخطاء التحقق من صحة البيانات
    error_log("خطأ في التحقق من صحة البيانات - شعار المورد: " . $e->getMessage());
    ResponseHandler::error($e->getMessage(), 400);
    
} catch (RuntimeException $e) {
    // أخطاء وقت التشغيل
    error_log("خطأ في تشغيل - شعار المورد: " . $e->getMessage());
    ResponseHandler::error('حدث خطأ أثناء تحديث الشعار: ' . $e->getMessage(), 500);
    
} catch (Exception $e) {
    // أخطاء غير متوقعة
    error_log("خطأ غير متوقع - شعار المورد: " . $e->getMessage());
    ResponseHandler::error('حدث خطأ غير متوقع في النظام', 500);
    
} finally {
    // إغلاق اتصال قاعدة البيانات
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>