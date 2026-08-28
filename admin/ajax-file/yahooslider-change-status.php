<?php
/**
 * تحديث حالة شريط ياهو المتحرك (Yahoo Slider Status Update)
 * 
 * هذا الملف مسؤول عن تحديث حالة العناصر في شريط ياهو المتحرك
 * (تفعيل / إلغاء تفعيل)
 * 
 * @package YahooSlider
 * @subpackage Admin
 * @version 1.0.0
 * @author System Admin
 * @copyright 2025 EgyptMart
 * @link https://egyptmart.shop
 * 
 * PHP Version 8.3
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
 * Class YahooSliderUpdater
 * 
 * المسؤولة عن تحديث حالة عناصر شريط ياهو
 */
class YahooSliderUpdater {
    
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
     * مسار ملف السجلات
     * @var string
     */
    private string $logFile;
    
    /**
     * constructor
     * 
     * @param mysqli $database اتصال قاعدة البيانات
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/yahoo_slider_updates.log';
        
        // التأكد من وجود مجلد السجلات
        $this->ensureLogDirectoryExists();
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
     * التحقق من صحة المدخلات
     * 
     * @param mixed $stat قيمة الحالة المرسلة
     * @param mixed $id رقم العنصر المرسل
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
        
        // تنظيف وتحويل رقم العنصر
        $cleanId = filter_var(trim((string)$id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException(
                'رقم العنصر غير صالح. يجب أن يكون رقماً موجباً'
            );
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * الحصول على الحالة القديمة للعنصر (للتسجيل)
     * 
     * @param int $id رقم العنصر
     * @return int|null الحالة القديمة أو null إذا لم يوجد
     */
    public function getCurrentStatus(int $id): ?int {
        $sql = "SELECT adv_status FROM yahoo_slider WHERE adv_id = ?";
        
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
     * تحديث حالة العنصر في قاعدة البيانات
     * 
     * @param int $status الحالة الجديدة
     * @param int $id رقم العنصر
     * @return bool نجاح أو فشل العملية
     * @throws RuntimeException إذا فشلت عملية التحديث
     */
    public function updateStatus(int $status, int $id): bool {
        
        // استخدام prepared statement لمنع SQL Injection
        $sql = "UPDATE yahoo_slider SET adv_status = ? WHERE adv_id = ?";
        
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
        
        // إذا كان عدد الصفوف المتأثرة 0، هذا يعني أن العنصر غير موجود
        if ($affectedRows === 0) {
            throw new RuntimeException('العنصر غير موجود في قاعدة البيانات');
        }
        
        return true;
    }
    
    /**
     * تسجيل عملية التحديث
     * 
     * @param int $id رقم العنصر
     * @param int|null $oldStatus الحالة القديمة
     * @param int $newStatus الحالة الجديدة
     * @return void
     */
    public function logUpdate(int $id, ?int $oldStatus, int $newStatus): void {
        
        // معلومات المستخدم
        $userId = $_SESSION['uid_indm'] ?? 0;
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // بناء سجل التحديث
        $logEntry = sprintf(
            "[%s] تحديث شريط ياهو | ID: %d | حالة قديمة: %s | حالة جديدة: %d | مستخدم: %d | IP: %s | متصفح: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $oldStatus !== null ? (string)$oldStatus : 'غير معروفة',
            $newStatus,
            $userId,
            $userIp,
            $userAgent
        );
        
        // حفظ في ملف السجلات
        error_log($logEntry, 3, $this->logFile);
        
        // يمكن أيضاً حفظ في قاعدة البيانات للتتبع
        $this->saveAuditTrail($id, $oldStatus, $newStatus, $userId, $userIp);
    }
    
    /**
     * حفظ سجل التدقيق في قاعدة البيانات
     * 
     * @param int $itemId رقم العنصر
     * @param int|null $oldStatus الحالة القديمة
     * @param int $newStatus الحالة الجديدة
     * @param int $userId رقم المستخدم
     * @param string $userIp عنوان IP
     * @return void
     */
    private function saveAuditTrail(int $itemId, ?int $oldStatus, int $newStatus, int $userId, string $userIp): void {
        
        $sql = "INSERT INTO yahoo_slider_audit 
                (item_id, old_status, new_status, user_id, user_ip, action_date) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            error_log("فشل حفظ سجل التدقيق: " . mysqli_error($this->db));
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "iiiis", $itemId, $oldStatus, $newStatus, $userId, $userIp);
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
}

/**
 * Class ResponseHandler
 * 
 * معالجة الاستجابات المرسلة للواجهة
 */
class ResponseHandler {
    
    /**
     * إرسال استجابة نجاح
     * 
     * @param array $data بيانات إضافية
     * @return void
     */
    public static function success(array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'تم تحديث حالة العنصر بنجاح',
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * إرسال استجابة خطأ
     * 
     * @param string $message رسالة الخطأ
     * @param int $statusCode كود حالة HTTP
     * @return void
     */
    public static function error(string $message, int $statusCode = 400): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * التنفيذ الرئيسي
 */
try {
    
    // التحقق من أن الطلب هو POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ResponseHandler::error('طريقة الطلب غير مسموحة. استخدم POST فقط.', 405);
        exit;
    }
    
    // إنشاء كائن المحدّث
    $updater = new YahooSliderUpdater($con);
    
    // التحقق من الصلاحيات
    if (!$updater->checkPermission()) {
        ResponseHandler::error('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
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
    
    // تحديث الحالة
    $updated = $updater->updateStatus($validated['status'], $validated['id']);
    
    if (!$updated) {
        throw new RuntimeException('فشل تحديث الحالة');
    }
    
    // تسجيل العملية
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status']);
    
    // إرسال استجابة نجاح
    ResponseHandler::success([
        'item_id' => $validated['id'],
        'old_status' => $oldStatus,
        'new_status' => $validated['status']
    ]);
    
} catch (InvalidArgumentException $e) {
    // أخطاء التحقق من صحة البيانات
    error_log("خطأ في التحقق من صحة البيانات - ياهو سلايدر: " . $e->getMessage());
    ResponseHandler::error($e->getMessage(), 400);
    
} catch (RuntimeException $e) {
    // أخطاء وقت التشغيل
    error_log("خطأ في تشغيل - ياهو سلايدر: " . $e->getMessage());
    ResponseHandler::error('حدث خطأ أثناء تحديث العنصر', 500);
    
} catch (Exception $e) {
    // أخطاء غير متوقعة
    error_log("خطأ غير متوقع - ياهو سلايدر: " . $e->getMessage());
    ResponseHandler::error('حدث خطأ غير متوقع', 500);
    
} finally {
    // إغلاق اتصال قاعدة البيانات
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>