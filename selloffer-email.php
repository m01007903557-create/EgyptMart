<?php
/**
 * File: selloffer-email.php
 * Version: 2.0.0
 * PHP Version: 8.3
 * 
 * Description: إرسال إشعارات عروض البيع للمستخدمين المهتمين بناءً على التصنيفات والموقع
 * Send sale offer notifications to interested users based on categories and location
 * 
 * Features:
 * - إرسال تنبيهات للمشتركين حسب التصنيف والموقع
 * - تصفية المستلمين حسب تفضيلات الموقع
 * - إنشاء رسائل بريد إلكتروني مخصصة بالعربية
 * - حفظ الرسائل في صندوق الوارد
 * - دعم HTML المتقدم للبريد الإلكتروني
 */

declare(strict_types=1);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/selloffer_notification_errors.log');

/**
 * Class SaleOfferEmailNotifier
 * 
 * Handles sending sale offer email notifications to subscribers
 */
class SaleOfferEmailNotifier {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var int Sale offer ID */
    private int $offerId;
    
    /** @var string Log file path */
    private string $logFile;
    
    /** @var string Base URL */
    private string $baseUrl = 'https://egyptmart.online';
    
    /** @var array Sender information */
    private array $senderInfo;
    
    /** @var int Count of sent notifications */
    private int $sentCount = 0;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param int $offerId Sale offer ID
     */
    public function __construct(mysqli $database, int $offerId) {
        $this->db = $database;
        $this->offerId = $offerId;
        $this->logFile = __DIR__ . '/../logs/selloffer_notifications.log';
        $this->ensureLogDirectory();
        $this->initializeSenderInfo();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Initialize sender information
     */
    private function initializeSenderInfo(): void {
        $this->senderInfo = [
            'from_name' => get_page_settings(4) ?: 'EgyptMART',
            'from_email' => get_adminemail() ?: 'noreply@egyptmart.online'
        ];
    }
    
    /**
     * Get sale offer details with country info
     * 
     * @return array|null Sale offer details
     */
    public function getOfferDetails(): ?array {
        $sql = "SELECT u.*, so.*, c.cn_id, c.cn_name 
                FROM user u
                JOIN sale_offer so ON u.usr_id = so.so_usr_id
                LEFT JOIN country c ON u.country = c.cn_id
                WHERE so.so_id = ? 
                LIMIT 1";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->offerId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row ?: null;
    }
    
    /**
     * Get subscribers for this sale offer
     * 
     * @param int $categoryId Category ID
     * @return array List of subscribers
     */
    public function getSubscribers(int $categoryId): array {
        $subscribers = [];
        
        $sql = "SELECT sac.*, u.*, bp.* 
                FROM selloffer_alert_category sac
                JOIN user u ON sac.sac_usr_id = u.usr_id
                JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                WHERE sac.sac_pc_id = ? AND u.status = '1'";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return $subscribers;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $subscribers[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        
        return $subscribers;
    }
    
    /**
     * Check if subscriber qualifies based on location preference
     * 
     * @param array $subscriber Subscriber data
     * @param int $countryId Country ID of the offer poster
     * @return bool True if qualifies
     */
    public function subscriberQualifies(array $subscriber, int $countryId): bool {
        $prefLocation = $subscriber['usr_so_prefLocation'] ?? 'any';
        $subscriberCountry = (int)($subscriber['country'] ?? 0);
        
        switch ($prefLocation) {
            case 'any':
                return true;
            
            case 'abroad':
                return ($subscriberCountry != $countryId);
            
            case 'domestic':
                return ($subscriberCountry == $countryId);
            
            case 'my_city':
                // For 'my_city', also need to check city (simplified)
                return ($subscriberCountry == $countryId);
            
            default:
                return false;
        }
    }
    
    /**
     * Generate email HTML content in Arabic
     * 
     * @param array $offer Sale offer data
     * @param array $subscriber Subscriber data
     * @param string $offerToken Offer token
     * @return string HTML content
     */
    public function generateEmailContent(array $offer, array $subscriber, string $offerToken): string {
        $currentDate = date('M d, Y');
        $recipientName = trim(
            ($subscriber['name_prefix'] ?? '') . ' ' . 
            ($subscriber['fname'] ?? '') . ' ' . 
            ($subscriber['lname'] ?? '')
        );
        
        $imageUrl = $this->baseUrl . '/upload/sale_offer/' . ($offer['so_pic'] ?? '');
        if (empty($offer['so_pic'])) {
            $imageUrl = $this->baseUrl . '/upload/myproduct/noimage.jpg';
        }
        
        $serviceName = htmlspecialchars($offer['so_service'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $html = '<div style="width:550px; height:auto; border:9px solid #92AED2; float:left; padding:10px; margin-top:10px; font-family:Arial, Helvetica, sans-serif; direction:rtl;">';
        
        // Header
        $html .= '<div style="height:100px; width:100%; float:left;">';
        $html .= '<div style="height:100px; width:30%; float:left;">';
        $html .= '<img src="' . $this->baseUrl . '/images/Mlogo.png" style="width:100%;" alt="EgyptMART">';
        $html .= '</div>';
        $html .= '<div style="height:100px; width:43%; float:left;">';
        $html .= '<h2 style="font-size:20px; color:#466da0; text-align:center; margin-top:0px; margin-bottom:0px;">أحدث عرض بيع<br></h2>';
        $html .= '</div>';
        $html .= '<div style="min-height:100px; width:27%; float:right; padding-top:3px;">';
        $html .= '<span style="font-size:15px; float:right; padding-bottom:0px; clear:both; font-weight:bold; color:#000000;"> عرض خاص</span>';
        $html .= '<span style="float:right; font-size:13px; padding-top:0px; clear:both; color:#000000;">' . $currentDate . '</span>';
        $html .= '</div></div>';
        
        // Greeting
        $html .= '<div style="width:100%; float:left; color:#000000;">';
        $html .= '<p style="font-size:16px; text-align:right; color:#000000">';
        $html .= '<strong>' . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . ': الســادة</strong><br><br>';
        $html .= ': آخر عرض بيع خاص طبقا لإهتمامات شرائك</p>';
        $html .= '</div>';
        
        // Offer details
        $html .= '<div style="height:auto; width:100%; float:left; margin-top:10px;">';
        $html .= '<div style="height:auto; width:100%; float:left;">';
        $html .= '<div style="width:25%; float:left; padding-top:9px;">';
        $html .= '<img src="' . $imageUrl . '" style="height:116px; width:100%;" alt="Product">';
        $html .= '</div>';
        $html .= '<div style="width:66%; float:right;">';
        $html .= '<div style="width:100%"><h3 style="font-size:18px;">' . $serviceName . '</h3></div>';
        $html .= '</div></div>';
        
        // Learn more link
        $html .= '<div style="width:100%; font-size:12px; font-weight:bold; text-align:center; padding-top:15px; clear:both;">';
        $html .= '<a href="' . $this->baseUrl . '/saleoffer-details.php?id=' . $offerToken . '" ';
        $html .= 'style="text-decoration:none; color:#466da0;">+ المزيد</a>';
        $html .= '</div>';
        
        // Footer
        $html .= '<div style="height:2px; width:100%; float:left; border-bottom:3px dotted #D8AED8; margin:15px 0;"></div>';
        
        $html .= '<div style="width:100%; float:left; text-align:center; padding:10px 0;">';
        $html .= '<a href="' . $this->baseUrl . '/dir.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">منتجات وخدمات</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/sale-offers.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">عروض بيع خاصة</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/buyleads.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">طلبات شراء</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/tenders.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">مناقصات ومزايدات</a>';
        $html .= '</div>';
        
        $html .= '<div style="width:100%; padding-left:0px; float:left; color:#808080; text-align:center;">';
        $html .= '<p style="margin:10px 0px 2px">You have received this mail virtue of your opt-in subscription for product alert on ';
        $html .= '<span style="color:blue;">EgyptMART</span>.</p>';
        $html .= '<p style="color:#808080; margin:0px 0px 20px;">';
        $html .= '<a href="' . $this->baseUrl . '/manage-selloffer-alert.php" style="text-decoration:none; color:blue;">إضغط هنا</a> ';
        $html .= 'عند رغبتك تعديل إشعارات عروض البيع التى تهمك</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate inbox message content in Arabic
     * 
     * @param array $offer Sale offer data
     * @param array $subscriber Subscriber data
     * @param string $offerToken Offer token
     * @return string Message content
     */
    public function generateInboxMessage(array $offer, array $subscriber, string $offerToken): string {
        $currentDate = date('M d, Y');
        $recipientName = trim(
            ($subscriber['name_prefix'] ?? '') . ' ' . 
            ($subscriber['fname'] ?? '') . ' ' . 
            ($subscriber['lname'] ?? '')
        );
        
        $imageUrl = $this->baseUrl . '/upload/sale_offer/' . ($offer['so_pic'] ?? '');
        if (empty($offer['so_pic'])) {
            $imageUrl = $this->baseUrl . '/upload/myproduct/noimage.jpg';
        }
        
        $serviceName = htmlspecialchars($offer['so_service'] ?? '', ENT_QUOTES, 'UTF-8');
        
        $html = '<div style="width:550px; height:auto; border:9px solid #92AED2; float:left; padding:10px; margin-top:10px; font-family:Arial, Helvetica, sans-serif; direction:rtl;">';
        
        // Header
        $html .= '<div style="height:100px; width:100%; float:left;">';
        $html .= '<div style="height:100px; width:30%; float:left;">';
        $html .= '<img src="' . $this->baseUrl . '/images/logo.png" style="width:100%;" alt="EgyptMART">';
        $html .= '</div>';
        $html .= '<div style="height:100px; width:43%; float:left;">';
        $html .= '<h2 style="font-size:20px; color:#466da0; text-align:center; margin-top:0px; margin-bottom:0px;">آخـر إشعــار<br> عـرض بيـع</h2>';
        $html .= '</div>';
        $html .= '<div style="min-height:100px; width:27%; float:right; padding-top:3px;">';
        $html .= '<span style="font-size:15px; float:right; padding-bottom:0px; clear:both; font-weight:bold; color:#000000;"> إشعــار جـــديـد</span>';
        $html .= '<span style="float:right; font-size:13px; padding-top:0px; clear:both; color:#000000;">' . $currentDate . '</span>';
        $html .= '</div></div>';
        
        // Greeting
        $html .= '<div style="width:100%; float:right; color:#000000;">';
        $html .= '<p style="font-size:16px; text-align:right; color:#000000">';
        $html .= '<strong>' . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . '</strong>: الســادة</p>';
        $html .= '<p style="font-size:16px; text-align:right; color:#000000"><strong> : آخر عروض لبيع لدينا طبقا لإهتمامك</strong></p>';
        $html .= '</div>';
        
        // Offer details
        $html .= '<div style="height:auto; width:100%; float:left; margin-top:10px;">';
        $html .= '<div style="height:auto; width:100%; float:left;">';
        $html .= '<div style="width:25%; float:left; padding-top:9px;">';
        $html .= '<img src="' . $imageUrl . '" style="height:116px; width:100%;" alt="Product">';
        $html .= '</div>';
        $html .= '<div style="width:66%; float:right;">';
        $html .= '<div style="width:100%"><h3 style="font-size:18px;">' . $serviceName . '</h3></div>';
        $html .= '</div></div>';
        
        // View link
        $html .= '<div style="width:100%; font-size:12px; font-weight:bold; text-align:center; padding-top:15px; clear:both;">';
        $html .= '<a href="' . $this->baseUrl . '/saleoffer-details.php?id=' . $offerToken . '" ';
        $html .= 'style="text-decoration:none; color:#466da0;"> << شاهد عرض البيع</a>';
        $html .= '</div>';
        
        // Footer
        $html .= '<div style="height:2px; width:100%; float:left; border-bottom:3px dotted #D8AED8; margin:15px 0;"></div>';
        
        $html .= '<div style="width:100%; float:left; text-align:center; padding:10px 0;">';
        $html .= '<a href="' . $this->baseUrl . '/dir.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">إعرض منتجات وخدمات</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/sale-offers.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">أنشر عروض بيع خاصة</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/buyleads.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">إنشر طلبات تسعير</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/tenders.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">أنشر مناقصات مجانا</a>';
        $html .= '</div>';
        
        $html .= '<div style="width:100%; padding-left:0px; float:left; color:#808080; text-align:center;">';
        $html .= '<p style="margin:10px 0px 2px">You have received this mail virtue of your opt-in subscription for product alert on ';
        $html .= '<span style="color:blue;">EgyptMART</span>.</p>';
        $html .= '<p style="color:#808080; margin:0px 0px 20px;">';
        $html .= '<a href="' . $this->baseUrl . '/manage-selloffer-alert.php" style="text-decoration:none; color:blue;">إضعط هنا</a> ';
        $html .= 'عند رغبتك تغيير إشعارات عروض البيع الخاصة بإهتمامك</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Send email
     * 
     * @param string $to Recipient email
     * @param string $subject Subject
     * @param string $message HTML message
     * @return bool Success status
     */
    public function sendEmail(string $to, string $subject, string $message): bool {
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=UTF-8\n";
        $headers .= "From: {$this->senderInfo['from_name']} <{$this->senderInfo['from_email']}>\n";
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Save message to inbox
     * 
     * @param int $fromId Sender ID
     * @param int $toId Recipient ID
     * @param string $subject Subject
     * @param string $message Message content
     * @return bool Success status
     */
    public function saveToInbox(int $fromId, int $toId, string $subject, string $message): bool {
        $sql = "INSERT INTO message SET
                msg_from = ?,
                msg_to = ?,
                msg_subject = ?,
                msg_message = ?,
                msg_to_status = '1',
                msg_from_status = '0',
                msg_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "iiss", $fromId, $toId, $subject, $message);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Log notification
     * 
     * @param int $subscriberId Subscriber ID
     * @param bool $success Whether email was sent successfully
     */
    public function logNotification(int $subscriberId, bool $success): void {
        $status = $success ? 'SENT' : 'FAILED';
        
        $logEntry = sprintf(
            "[%s] Sale Offer Notification %s | Subscriber: %d | Offer: %d\n",
            date('Y-m-d H:i:s'),
            $status,
            $subscriberId,
            $this->offerId
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Process notifications for this sale offer
     * 
     * @return int Number of notifications sent
     */
    public function processNotifications(): int {
        // Get offer details
        $offer = $this->getOfferDetails();
        if (!$offer) {
            error_log("Sale offer not found: {$this->offerId}");
            return 0;
        }
        
        // Check if offer is approved
        if (($offer['so_approval_status'] ?? 0) != 1) {
            error_log("Sale offer not approved: {$this->this->offerId}");
            return 0;
        }
        
        // Get country ID
        $countryId = (int)($offer['cn_id'] ?? 0);
        
        // Get category ID
        $categoryId = (int)($offer['so_pc_id'] ?? 0);
        if ($categoryId <= 0) {
            error_log("Invalid category ID for offer: {$this->offerId}");
            return 0;
        }
        
        // Generate offer token
        $offerToken = rand(1, 9999) . md5((string)$this->offerId);
        
        // Get subscribers
        $subscribers = $this->getSubscribers($categoryId);
        
        if (empty($subscribers)) {
            return 0;
        }
        
        $subject = "أحدث عرض بيع خاص يهمك";
        
        foreach ($subscribers as $subscriber) {
            // Check if subscriber qualifies based on location
            if (!$this->subscriberQualifies($subscriber, $countryId)) {
                continue;
            }
            
            $recipientEmail = $subscriber['email'] ?? '';
            if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            
            // Generate email content
            $emailContent = $this->generateEmailContent($offer, $subscriber, $offerToken);
            
            // Generate inbox message
            $inboxMessage = $this->generateInboxMessage($offer, $subscriber, $offerToken);
            
            // Send email
            $emailSent = $this->sendEmail($recipientEmail, $subject, $emailContent);
            
            // Save to inbox
            $fromId = (int)($offer['so_usr_id'] ?? 0);
            $toId = (int)($subscriber['usr_id'] ?? 0);
            
            if ($fromId > 0 && $toId > 0) {
                $this->saveToInbox($fromId, $toId, $subject, $inboxMessage);
            }
            
            // Log the notification
            $this->logNotification($toId, $emailSent);
            
            if ($emailSent) {
                $this->sentCount++;
            }
        }
        
        return $this->sentCount;
    }
    
    /**
     * Get sent count
     * 
     * @return int Number of sent notifications
     */
    public function getSentCount(): int {
        return $this->sentCount;
    }
    
    /**
     * Redirect based on calling context
     */
    public function redirect(): void {
        if (isset($_GET['so_id'])) {
            header("Location: admin/selloffer-view.php");
            exit;
        }
        
        if (isset($_GET['admn_so_id'])) {
            $token = md5((string)$_GET['admn_so_id']);
            header("Location: admin/selloffer-edit.php?token=" . $token);
            exit;
        }
    }
}

// Main execution
try {
    // Determine if we're running standalone or included
    $standalone = !isset($so_id) || $so_id === 0;
    
    if ($standalone) {
        ob_start();
        session_start();
        
        require_once __DIR__ . '/common.php';
    }
    
    // Get offer ID from various sources
    $offerId = 0;
    
    if (isset($_POST['so_id']) && is_numeric($_POST['so_id'])) {
        $offerId = (int)$_POST['so_id'];
    } elseif (isset($_GET['so_id']) && is_numeric($_GET['so_id'])) {
        $offerId = (int)$_GET['so_id'];
    } elseif (isset($_GET['admn_so_id']) && is_numeric($_GET['admn_so_id'])) {
        $offerId = (int)$_GET['admn_so_id'];
    } elseif (isset($so_id) && $so_id > 0) {
        $offerId = $so_id;
    }
    
    if ($offerId <= 0) {
        throw new InvalidArgumentException('Invalid sale offer ID');
    }
    
    // Initialize notifier
    $notifier = new SaleOfferEmailNotifier($con, $offerId);
    
    // Process notifications
    $sentCount = $notifier->processNotifications();
    
    // Log total sent
    error_log("Sale offer notifications sent: {$sentCount} for offer ID {$offerId}");
    
    // Redirect or display result
    if ($standalone) {
        $notifier->redirect();
    } else {
        echo "Notifications sent to {$sentCount} recipients.";
    }
    
} catch (InvalidArgumentException $e) {
    error_log("Sale offer notification error: " . $e->getMessage());
    
    if ($standalone) {
        die("Invalid offer ID");
    } else {
        echo "Error: " . $e->getMessage();
    }
    
} catch (Exception $e) {
    error_log("Sale offer notification unexpected error: " . $e->getMessage());
    
    if ($standalone) {
        die("An unexpected error occurred");
    } else {
        echo "An unexpected error occurred";
    }
    
} finally {
    if ($standalone && isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>