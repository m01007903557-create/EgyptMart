<?php
/**
 * File: tender-email.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إرسال إشعارات البريد الإلكتروني للمناقصات للمشتركين
 * Send tender email notifications to subscribers
 * 
 * Features:
 * - إرسال تنبيهات للمشتركين حسب التصنيف والموقع
 * - تصفية المستلمين حسب تفضيلات الموقع
 * - إنشاء رسائل بريد إلكتروني مخصصة
 * - حفظ الرسائل في قاعدة البيانات
 */

declare(strict_types=1);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/tender_notification_errors.log');

// Check if we're in a standalone mode or included from another script
$standalone = !isset($tender_id) || empty($tender_id);

if ($standalone) {
    ob_start();
    session_start();
    require_once 'common.php';
}

/**
 * Class TenderEmailNotifier
 * 
 * Handles sending tender email notifications to subscribers
 */
class TenderEmailNotifier {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var int Tender ID */
    private int $tenderId;
    
    /** @var string Log file path */
    private string $logFile;
    
    /** @var string Base URL */
    private string $baseUrl = 'https://egyptmart.online';
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param int $tenderId Tender ID
     */
    public function __construct(mysqli $database, int $tenderId) {
        $this->db = $database;
        $this->tenderId = $tenderId;
        $this->logFile = __DIR__ . '/../logs/tender_notifications.log';
        $this->ensureLogDirectory();
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
     * Get tender details with location info
     * 
     * @return array|null Tender details
     */
    public function getTenderDetails(): ?array {
        $sql = "SELECT u.*, t.*, c.cn_id, c.cn_name, s.st_name, ct.ct_name 
                FROM user u
                JOIN tender t ON u.usr_id = t.tnd_usr_id
                LEFT JOIN country c ON u.country = c.cn_id
                LEFT JOIN states s ON u.state_id = s.st_id
                LEFT JOIN city ct ON u.city_id = ct.ct_id
                WHERE t.tnd_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->tenderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row ?: null;
    }
    
    /**
     * Get category path
     * 
     * @param int $subcatId Subcategory ID
     * @return array Category path [main, category, subcategory]
     */
    public function getCategoryPath(int $subcatId): array {
        $sql = "SELECT m.pc_name as main, c.pc_name as category, s.pc_name as subcategory 
                FROM product_category s
                JOIN product_category c ON s.pc_parent_id = c.pc_id
                JOIN product_category m ON c.pc_parent_id = m.pc_id
                WHERE s.pc_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return ['', '', ''];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $subcatId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        
        mysqli_stmt_close($stmt);
        return ['main' => '', 'category' => '', 'subcategory' => ''];
    }
    
    /**
     * Get subscribers for this tender
     * 
     * @param array $tender Tender details
     * @return array List of subscribers
     */
    public function getSubscribers(array $tender): array {
        $subscribers = [];
        
        $sql = "SELECT u.*, bf.*, tac.tac_pc_id 
                FROM tender_alert_category tac
                JOIN user u ON tac.tac_usr_id = u.usr_id
                JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
                WHERE tac.tac_pc_id = ? AND u.status = '1'";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return $subscribers;
        }
        
        $categoryId = (int)($tender['tnd_pc_id'] ?? 0);
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
     * @param array $tender Tender data
     * @param int $countryId Country ID
     * @return bool True if qualifies
     */
    public function subscriberQualifies(array $subscriber, array $tender, int $countryId): bool {
        $prefLocation = $subscriber['usr_tnd_prefLocation'] ?? 'any';
        $subscriberCountry = (int)($subscriber['country'] ?? 0);
        
        switch ($prefLocation) {
            case 'any':
                return true;
            
            case 'abroad':
                return ($subscriberCountry != $countryId);
            
            case 'domestic':
                return ($subscriberCountry == $countryId);
            
            case 'my_city':
                // For 'my_city', also need to check city
                return ($subscriberCountry == $countryId);
            
            default:
                return false;
        }
    }
    
    /**
     * Generate email HTML content
     * 
     * @param array $tender Tender data
     * @param array $subscriber Subscriber data
     * @param string $tenderToken Tender token
     * @return string HTML content
     */
    public function generateEmailContent(array $tender, array $subscriber, string $tenderToken): string {
        $categoryPath = $this->getCategoryPath((int)($tender['tnd_pc_id'] ?? 0));
        $currentDate = date('M d, Y');
        $dueDate = !empty($tender['tnd_due_date']) ? date('d M, Y', strtotime($tender['tnd_due_date'])) : 'N/A';
        
        $recipientName = trim(
            ($subscriber['name_prefix'] ?? '') . ' ' . 
            ($subscriber['fname'] ?? '') . ' ' . 
            ($subscriber['lname'] ?? '')
        );
        
        $companyName = $subscriber['bnsprof_compname'] ?? '';
        $quantity = (float)($tender['tnd_qty'] ?? 0);
        $unit = measurement_unit((int)($tender['tnd_qty_mu_id'] ?? 0));
        $value = (float)($tender['tnd_value'] ?? 0);
        $currency = getCurrency((int)($tender['tnd_currency'] ?? 0));
        $projectPeriod = $tender['tnd_project_period'] ?? '';
        
        $html = '<div style="width:680px; height:auto; border:9px solid #92AED2; float:left; padding:10px; margin-top:10px; font-family:Arial, Helvetica, sans-serif;">';
        
        // Header
        $html .= '<div style="height:100px; width:100%; float:left;">';
        $html .= '<div style="height:100px; width:30%; float:left;">';
        $html .= '<img src="' . $this->baseUrl . '/images/logo.png" style="width:100%;" alt="EgyptMART">';
        $html .= '</div>';
        $html .= '<div style="height:100px; width:43%; float:left;">';
        $html .= '<h2 style="font-size:20px; color:#466da0; text-align:center; margin-top:0px; margin-bottom:0px;">Today\'s Latest<br>Tender Alert</h2>';
        $html .= '</div>';
        $html .= '<div style="min-height:100px; width:27%; float:right; padding-top:3px;">';
        $html .= '<span style="font-size:15px; float:right; padding-bottom:0px; clear:both; font-weight:bold; color:#000000;">Notification</span>';
        $html .= '<span style="float:right; font-size:13px; padding-top:0px; clear:both; color:#000000;">' . $currentDate . '</span>';
        $html .= '</div></div>';
        
        // Greeting
        $html .= '<div style="width:100%; float:left; color:#000000;">';
        $html .= '<p style="font-size:16px; color:#000000">';
        $html .= '<strong>Dear ' . htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8') . '</strong>,<br><br>';
        $html .= 'Latest tender relevant to your subscribed categories on EgyptMART are listed below:</p>';
        $html .= '</div>';
        
        // Tender details
        $html .= '<div style="height:auto; width:100%; float:left; margin-top:10px;">';
        $html .= '<div style="height:auto; width:100%; float:left;">';
        $html .= '<div style="width:100%">';
        $html .= '<div style="width:100%"><h3 style="font-size:18px;">' . htmlspecialchars($tender['tnd_heading'] ?? '', ENT_QUOTES, 'UTF-8') . '</h3></div>';
        
        if ($quantity > 0) {
            $html .= '<div style="width:100%; margin-top:10px;">';
            $html .= '<div style="display:inline-block; padding-left:0px; width:30%;">Quantity<span style="padding-left:20px;">:</span></div>';
            $html .= '<div style="color:#e9582c; display:inline-block;">' . number_format($quantity) . '</div>';
            $html .= '<div style="color:#000; display:inline-block;"> ' . htmlspecialchars($unit, ENT_QUOTES, 'UTF-8') . '</div>';
            $html .= '</div>';
        }
        
        if ($value > 0) {
            $html .= '<div style="width:100%; margin-top:10px;">';
            $html .= '<div style="padding-left:0px; display:inline-block; width:30%;">Price<span style="padding-left:20px;">:</span></div>';
            $html .= '<div style="color:#e9582c; font-weight:bold; font-size:15px; line-height:15px; display:inline-block;">' . number_format($value, 2) . '</div>';
            $html .= '<div style="color:#000; padding-left:5px; display:inline-block;"> ' . htmlspecialchars($currency, ENT_QUOTES, 'UTF-8') . '</div>';
            $html .= '</div>';
        }
        
        $html .= '<div style="width:100%; margin-top:10px;">';
        $html .= '<div style="padding-left:0px; display:inline-block; width:30%;">Due Date<span style="padding-left:20px;">:</span></div>';
        $html .= '<div style="color:#e9582c; font-weight:bold; font-size:15px; line-height:15px; display:inline-block;">' . $dueDate . '</div>';
        $html .= '</div>';
        
        if (!empty($projectPeriod)) {
            $html .= '<div style="width:100%; margin-top:10px;">';
            $html .= '<div style="padding-left:0px; display:inline-block; width:30%;">Project Period<span style="padding-left:20px;">:</span></div>';
            $html .= '<div style="color:#e9582c; font-weight:bold; font-size:15px; line-height:15px; display:inline-block;">' . htmlspecialchars($projectPeriod, ENT_QUOTES, 'UTF-8') . '</div>';
            $html .= '</div>';
        }
        
        $html .= '</div></div></div>';
        
        // Learn more link
        $html .= '<div style="width:100%; font-size:12px; font-weight:bold; text-align:center; padding-top:15px;">';
        $html .= '<a href="' . $this->baseUrl . '/tender-details.php?id=' . $tenderToken . '" style="text-decoration:none; color:#466da0;">Learn More >></a>';
        $html .= '</div>';
        
        // Footer
        $html .= '<div style="height:2px; width:100%; float:left; border-bottom:3px dotted #D8AED8; margin:15px 0;"></div>';
        
        $html .= '<div style="width:100%; float:left; text-align:center; padding:10px 0;">';
        $html .= '<a href="' . $this->baseUrl . '/dir.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">Product & Suppliers</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/sale-offers.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">Sale Offers</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/buyleads.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">Buy Requests</a> | ';
        $html .= '<a href="' . $this->baseUrl . '/tenders.php" style="color:#466da0; text-decoration:none; font-size:18px; font-weight:bold;">Tenders</a>';
        $html .= '</div>';
        
        $html .= '<div style="width:100%; padding-left:0px; float:left; color:#808080; text-align:center;">';
        $html .= '<p style="margin:10px 0px 2px;">You have received this mail by virtue of your opt-in subscription for tender alert on <span style="color:blue;">EgyptMART</span>.</p>';
        $html .= '<p style="color:#808080; margin:0px 0px 20px;">';
        $html .= '<a href="' . $this->baseUrl . '/manage-tender-alert.php" style="text-decoration:none; color:blue;">Click here</a> if you wish to modify your tender alert categories.</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate inbox message content
     * 
     * @param array $tender Tender data
     * @param array $subscriber Subscriber data
     * @param string $tenderToken Tender token
     * @return string Message content
     */
    public function generateInboxMessage(array $tender, array $subscriber, string $tenderToken): string {
        // Similar to email content but without the full HTML structure
        return $this->generateEmailContent($tender, $subscriber, $tenderToken);
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
        $sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_to_status, msg_from_status, msg_date) 
                VALUES (?, ?, ?, ?, '1', '0', NOW())";
        
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
     * Send email
     * 
     * @param string $to Recipient email
     * @param string $subject Subject
     * @param string $message HTML message
     * @param array $senderInfo Sender information
     * @return bool Success status
     */
    public function sendEmail(string $to, string $subject, string $message, array $senderInfo): bool {
        $fromName = $senderInfo['from_name'] ?? get_page_settings(4);
        $fromEmail = $senderInfo['from_email'] ?? get_adminemail();
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Log notification
     * 
     * @param int $subscriberId Subscriber ID
     * @param int $tenderId Tender ID
     * @param bool $success Whether email was sent successfully
     */
    public function logNotification(int $subscriberId, int $tenderId, bool $success): void {
        $status = $success ? 'SENT' : 'FAILED';
        
        $logEntry = sprintf(
            "[%s] Tender Notification %s | Subscriber: %d | Tender: %d\n",
            date('Y-m-d H:i:s'),
            $status,
            $subscriberId,
            $tenderId
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Process notifications for this tender
     * 
     * @return int Number of notifications sent
     */
    public function processNotifications(): int {
        $count = 0;
        
        // Get tender details
        $tender = $this->getTenderDetails();
        if (!$tender) {
            error_log("Tender not found: {$this->tenderId}");
            return 0;
        }
        
        // Check if tender is approved
        if (($tender['tnd_approval_status'] ?? 0) != 1) {
            error_log("Tender not approved: {$this->tenderId}");
            return 0;
        }
        
        // Get country ID
        $countryId = (int)($tender['cn_id'] ?? 0);
        
        // Generate tender token
        $tenderToken = rand(1, 9999) . md5((string)$this->tenderId);
        
        // Get subscribers
        $subscribers = $this->getSubscribers($tender);
        
        if (empty($subscribers)) {
            return 0;
        }
        
        // Prepare sender info
        $senderInfo = [
            'from_name' => get_page_settings(4),
            'from_email' => get_adminemail()
        ];
        
        $subject = "Latest Tender Alert From " . get_page_settings(4);
        
        foreach ($subscribers as $subscriber) {
            // Check if subscriber qualifies based on location
            if (!$this->subscriberQualifies($subscriber, $tender, $countryId)) {
                continue;
            }
            
            $recipientEmail = $subscriber['email'] ?? '';
            if (empty($recipientEmail)) {
                continue;
            }
            
            // Generate email content
            $emailContent = $this->generateEmailContent($tender, $subscriber, $tenderToken);
            
            // Generate inbox message
            $inboxMessage = $this->generateInboxMessage($tender, $subscriber, $tenderToken);
            
            // Send email
            $emailSent = $this->sendEmail($recipientEmail, $subject, $emailContent, $senderInfo);
            
            // Save to inbox
            $fromId = (int)($tender['tnd_usr_id'] ?? 0);
            $toId = (int)($subscriber['usr_id'] ?? 0);
            
            if ($fromId > 0 && $toId > 0) {
                $this->saveToInbox($fromId, $toId, $subject, $inboxMessage);
            }
            
            // Log the notification
            $this->logNotification($toId, $this->tenderId, $emailSent);
            
            if ($emailSent) {
                $count++;
            }
        }
        
        return $count;
    }
}

// Main execution
try {
    // Determine tender ID from various sources
    $tenderId = 0;
    
    if (isset($_GET['tnd_usr_id']) && !empty($_GET['tnd_usr_id'])) {
        $tenderId = (int)$_GET['tnd_usr_id'];
    } elseif (isset($_GET['admn_tnd_id']) && !empty($_GET['admn_tnd_id'])) {
        $tenderId = (int)$_GET['admn_tnd_id'];
    } elseif (isset($_POST['tnd_id']) && !empty($_POST['tnd_id'])) {
        $tenderId = (int)$_POST['tnd_id'];
    } elseif (isset($_GET['tnd_id']) && !empty($_GET['tnd_id'])) {
        $tenderId = (int)$_GET['tnd_id'];
    }
    
    if ($tenderId === 0) {
        throw new InvalidArgumentException('Tender ID is required');
    }
    
    // Initialize notifier
    $notifier = new TenderEmailNotifier($con, $tenderId);
    
    // Process notifications
    $sentCount = $notifier->processNotifications();
    
    // Log total sent
    error_log("Tender notifications sent: {$sentCount} for tender ID {$tenderId}");
    
    // Redirect based on calling context
    if (isset($_GET['tnd_usr_id']) || isset($_GET['tnd_id'])) {
        header("Location: admin/tender-view.php");
        exit;
    }
    
    if (isset($_GET['admn_tnd_id'])) {
        $token = md5((string)$tenderId);
        header("Location: admin/tender-edit.php?token=" . $token);
        exit;
    }
    
} catch (InvalidArgumentException $e) {
    error_log("Tender notification error: " . $e->getMessage());
    
    if ($standalone) {
        header("Location: admin/tender-view.php");
        exit;
    }
    
} catch (Exception $e) {
    error_log("Tender notification unexpected error: " . $e->getMessage());
    
    if ($standalone) {
        header("Location: admin/tender-view.php");
        exit;
    }
    
} finally {
    if ($standalone && isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>