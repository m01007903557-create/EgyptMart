<?php
/**
 * File: membership_expiry_notification_week2.php

 * Version: 2.0.0
 * Description: مهمة مجدولة لإرسال تذكير قبل 22 يوم من انتهاء صلاحية الخطط (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

// تشغيل عرض الأخطاء (للتطوير فقط)
ini_set("display_errors", "1");
error_reporting(E_ALL);

// تضمين الملفات المطلوبة
require_once __DIR__ . "/../common.php";

// منع تنفيذ السكريبت عبر المتصفح مباشرة
if (php_sapi_name() !== 'cli' && !isset($_SERVER['HTTP_USER_AGENT'])) {
    die('Access denied');
}

/**
 * Class PlanExpiryReminderWeek2 - معالجة تذكير انتهاء الخطط (22 يوم)
 */
class PlanExpiryReminderWeek2 {
    private mysqli $db;
    private string $siteName;
    private string $adminEmail;
    private int $adminUserId;
    
    /**
     * المُنشئ
     */
    public function __construct() {
        global $con;
        $this->db = $con;
        $this->siteName = get_page_settings(4) ?: 'الموقع';
        $this->adminEmail = get_adminemail() ?: 'admin@example.com';
        $this->adminUserId = getAdminUserId() ?: 1;
    }
    
    /**
     * تنفيذ المعالجة
     */
    public function process(): void {
        // جلب الخطط التي تحتاج تذكير
        $plans = $this->getPlansForReminder();
        
        if (empty($plans)) {
            $this->logMessage("لا توجد خطط تحتاج تذكير اليوم");
            return;
        }
        
        $processed = 0;
        $targetDate = date('Y-m-d', strtotime("+22 days"));
        
        foreach ($plans as $plan) {
            try {
                // التحقق مما إذا كانت تنتهي بعد 22 يوم
                $planExpiryDate = date('Y-m-d', (int)$plan->expiry_date);
                
                if ($planExpiryDate === $targetDate) {
                    $this->processExpiryReminder($plan);
                    $processed++;
                }
            } catch (Exception $e) {
                $this->logMessage("خطأ في معالجة الخطة ID {$plan->pm_id}: " . $e->getMessage());
            }
        }
        
        $this->logMessage("تمت معالجة $processed تذكير/تذكيرات");
    }
    
    /**
     * جلب الخطط التي تحتاج تذكير
     */
    private function getPlansForReminder(): array {
        $sql = "SELECT pm.*, u.*, sp.*, bf.bnsprof_uid 
                FROM plan_member_id pm 
                JOIN business_profile bf ON pm.b_id = bf.bnsprof_id 
                JOIN user u ON u.usr_id = bf.bnsprof_uid 
                JOIN smembership_plan sp ON sp.mp_id = pm.p_id 
                WHERE pm.notification_email_week2_status = 0";
        
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            throw new RuntimeException("فشل الاستعلام: " . mysqli_error($this->db));
        }
        
        $plans = [];
        while ($row = mysqli_fetch_object($result)) {
            $plans[] = $row;
        }
        
        return $plans;
    }
    
    /**
     * معالجة تذكير انتهاء الخطة
     */
    private function processExpiryReminder(object $plan): void {
        // جلب تفاصيل الفواتير
        $billingDetail = $this->getBillingDetails((int)$plan->usr_id);
        
        // إرسال البريد الإلكتروني
        $this->sendReminderEmail($plan, $billingDetail);
        
        // تحديث حالة الإشعار
        $this->updateNotificationStatus((int)$plan->b_id);
        
        // إرسال رسالة داخلية
        $this->sendInternalMessage($plan);
        
        $this->logMessage("تم إرسال تذكير للمستخدم: {$plan->email} - الخطة: {$plan->mst_name}");
    }
    
    /**
     * جلب تفاصيل الفواتير
     */
    private function getBillingDetails(int $userId): ?object {
        $sql = "SELECT * FROM billing_history 
                WHERE bh_type = 5 AND bh_usr_id = ? 
                ORDER BY bh_updated_date DESC LIMIT 1";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $detail = mysqli_fetch_object($result);
        
        mysqli_stmt_close($stmt);
        
        return $detail ?: null;
    }
    
    /**
     * إرسال بريد تذكير
     */
    private function sendReminderEmail(object $plan, ?object $billingDetail): bool {
        $to = $plan->email;
        $fullname = trim(($plan->name_prefix ?? '') . ' ' . ($plan->fname ?? '') . ' ' . ($plan->lname ?? ''));
        $expiry_date = date("Y-m-d", (int)$plan->expiry_date);
        $start_date = date("Y-m-d", (int)$plan->start_date);
        $plan_name = $plan->mst_name ?? 'غير محدد';
        
        $subject = "تذكير: خطة العضوية الخاصة بك ستنتهي قريباً - " . $this->siteName;
        
        // تضمين قالب البريد الإلكتروني
        $message1 = '';
        $messageFilePath = __DIR__ . "/email/plan_expiry_notification.php";
        
        if (file_exists($messageFilePath)) {
            ob_start();
            include $messageFilePath;
            $message1 = ob_get_clean();
        } else {
            $message1 = $this->getDefaultEmailTemplate($plan, $fullname, $expiry_date);
        }
        
        // رأسيات البريد الإلكتروني
        $headers = [
            "MIME-Version: 1.0",
            "Content-type: text/html; charset=utf-8",
            "From: {$this->siteName} <{$this->adminEmail}>",
            "Reply-To: {$this->adminEmail}",
            "X-Mailer: PHP/" . phpversion()
        ];
        
        return mail($to, $subject, $message1, implode("\r\n", $headers));
    }
    
    /**
     * قالب البريد الإلكتروني الافتراضي
     */
    private function getDefaultEmailTemplate(object $plan, string $fullname, string $expiry_date): string {
        return "<html>
                <body>
                    <h3>عزيزي/عزيزتي {$fullname}</h3>
                    <p>نود تذكيرك بأن خطة العضوية الخاصة بك <strong>{$plan->mst_name}</strong> ستنتهي في {$expiry_date}.</p>
                    <p>يرجى تجديد عضويتك قبل هذا التاريخ للاستمرار في الاستفادة من خدماتنا.</p>
                    <p>مع تحيات،<br>{$this->siteName}</p>
                </body>
                </html>";
    }
    
    /**
     * تحديث حالة الإشعار
     */
    private function updateNotificationStatus(int $bId): bool {
        $sql = "UPDATE plan_member_id SET notification_email_week2_status = 1 WHERE b_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $bId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $success;
    }
    
    /**
     * إرسال رسالة داخلية
     */
    private function sendInternalMessage(object $plan): bool {
        $expiry_date = date("Y-m-d", (int)$plan->expiry_date);
        $plan_name = str_replace(["Plan", "plan"], "", $plan->mst_name ?? '');
        $message = "خطة العضوية {$plan_name} الخاصة بك ستنتهي في {$expiry_date}. يرجى تجديد عضويتك.";
        $subject = "تذكير بانتهاء خطة العضوية - {$this->siteName}";
        
        $sql = "INSERT INTO message 
                SET msg_from = ?,
                    msg_to = ?,
                    msg_subject = ?,
                    msg_message = ?,
                    msg_entity = 'membership_plan',
                    msg_entity_id = ?,
                    msg_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "iissi", 
            $this->adminUserId,
            $plan->usr_id,
            $subject,
            $message,
            $plan->b_id
        );
        
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $success;
    }
    
    /**
     * تسجيل رسالة
     */
    private function logMessage(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        // تسجيل في ملف
        $logFile = __DIR__ . '/../logs/plan_reminder_week2_' . date('Y-m-d') . '.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // طباعة على الشاشة إذا كان تنفيذ عبر CLI
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
}

// تنفيذ المعالجة
try {
    $cron = new PlanExpiryReminderWeek2();
    $cron->process();
} catch (Exception $e) {
    $errorMessage = "خطأ عام: " . $e->getMessage();
    error_log($errorMessage);
    
    if (php_sapi_name() === 'cli') {
        echo $errorMessage . PHP_EOL;
    }
}

?>