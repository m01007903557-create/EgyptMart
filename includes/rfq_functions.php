<?php
// =============================================
// الثوابت (Constants)
// =============================================
define('SOURCE_WEBSITE', 'website');
define('SOURCE_WHATSAPP_PLATFORM', 'whatsapp_platform');
define('SOURCE_WHATSAPP_BUSINESS', 'whatsapp_business');
define('SOURCE_FACEBOOK', 'facebook');
define('SOURCE_INSTAGRAM', 'instagram');
define('SOURCE_TELEGRAM', 'telegram');
define('SOURCE_API', 'api');

define('RFQ_STATUS_NEW', 'new');
define('RFQ_STATUS_CONTACTED', 'contacted');
define('RFQ_STATUS_QUOTED', 'quoted');
define('RFQ_STATUS_WON', 'won');
define('RFQ_STATUS_LOST', 'lost');
define('RFQ_STATUS_CLOSED', 'closed');

// =============================================
// الدالة الأساسية لحفظ طلبات الشراء
// =============================================
function saveRFQ(array $data): array {
    global $con;
    
    try {
        // تنظيف البيانات
        $br_u_id = (int)($data['br_u_id'] ?? 0);
        
        // إذا كان br_u_id = 0 أو غير موجود، استخدم guest ID
        if ($br_u_id == 0) {
            $br_u_id = getGuestUserId(); // أنشئ هذه الدالة
        }
        
        $source_channel = trim($data['source_channel'] ?? SOURCE_WEBSITE);
        $source_platform = isset($data['source_platform']) ? trim($data['source_platform']) : null;
        $source_detail = isset($data['source_detail']) ? trim($data['source_detail']) : null;
        $external_id = isset($data['external_id']) ? trim($data['external_id']) : null;
        
        // التحقق من التكرار (يمنع إدخال نفس الطلب مرتين)
        if (!empty($external_id)) {
            $check_sql = "SELECT br_id FROM buy_requirement WHERE external_id = ? LIMIT 1";
            $check_stmt = mysqli_prepare($con, $check_sql);
            mysqli_stmt_bind_param($check_stmt, 's', $external_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                $row = mysqli_fetch_assoc($check_result);
                mysqli_stmt_close($check_stmt);
                return [
                    'status' => 'duplicate',
                    'br_id' => (int)$row['br_id'],
                    'msg' => 'تم إرسال هذا الطلب مسبقاً'
                ];
            }
            mysqli_stmt_close($check_stmt);
        }
        
        // البيانات الأساسية
        $br_pc_id = (int)($data['br_pc_id'] ?? 0);
        $br_pd_name = trim($data['br_pd_name'] ?? '');
        $br_requirement = trim($data['br_requirement'] ?? '');
        $br_estimate_qty = (float)($data['br_estimate_qty'] ?? 0);
        $br_estimate_qty_unit = (int)($data['br_estimate_qty_unit'] ?? 0);
        $br_preferred_supplier_location = trim($data['br_preferred_supplier_location'] ?? 'any');
        $br_pic = trim($data['br_pic'] ?? '');
        $raw_payload = json_encode($data['raw_payload'] ?? $data, JSON_UNESCAPED_UNICODE);
        
        // التحقق من البيانات المطلوبة
        if (empty($br_requirement) && empty($br_pd_name)) {
            throw new Exception('يجب إدخال المنتج أو تفاصيل الطلب');
        }
        
        // إدراج الطلب
        $sql = "INSERT INTO buy_requirement 
                (br_u_id, br_pc_id, br_pd_name, br_requirement, br_estimate_qty, 
                 br_estimate_qty_unit, br_preferred_supplier_location, br_pic,
                 source_channel, source_platform, source_detail, external_id, 
                 raw_payload, br_status, br_posting_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'iissdississss', 
            $br_u_id, $br_pc_id, $br_pd_name, $br_requirement, $br_estimate_qty, 
            $br_estimate_qty_unit, $br_preferred_supplier_location, $br_pic,
            $source_channel, $source_platform, $source_detail, $external_id, 
            $raw_payload, RFQ_STATUS_NEW
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('فشل الحفظ: ' . mysqli_error($con));
        }
        
        $br_id = mysqli_insert_id($con);
        mysqli_stmt_close($stmt);
        
        // تسجيل النشاط
        logRFQActivity($br_id, 'created', $source_channel);
        
        // إرسال إشعار واتساب (إذا كان مطلوباً)
        $whatsapp_sent = false;
        if (!empty($data['send_whatsapp']) && !empty($source_detail)) {
            $whatsapp_sent = sendWhatsAppNotification(
                $source_detail,
                $br_requirement,
                $data['product_code'] ?? '',
                $br_id
            );
        }
        
        return [
            'status' => 'success',
            'br_id' => $br_id,
            'msg' => 'تم حفظ طلب الشراء بنجاح',
            'whatsapp_sent' => $whatsapp_sent
        ];
        
    } catch (Exception $e) {
        error_log("saveRFQ Error: " . $e->getMessage() . " | Data: " . print_r($data, true));
        return [
            'status' => 'error',
            'msg' => $e->getMessage()
        ];
    }
}

// =============================================
// دالة الحصول على ID مستخدم الضيف
// =============================================
function getGuestUserId(): int {
    global $con;
    
    // حاول جلب مستخدم الضيف
    $sql = "SELECT user_id FROM users WHERE user_type = 'guest' AND user_status = '1' LIMIT 1";
    $result = mysqli_query($con, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return (int)$row['user_id'];
    }
    
    // إنشاء مستخدم ضيف جديد
    $insert_sql = "INSERT INTO users (user_name, user_type, user_status, user_reg_date) 
                   VALUES ('Guest Social', 'guest', '1', NOW())";
    mysqli_query($con, $insert_sql);
    return (int)mysqli_insert_id($con);
}

// =============================================
// دالة تسجيل النشاط
// =============================================
function logRFQActivity(int $br_id, string $action, string $source): void {
    global $con;
    
    $sql = "INSERT INTO rfq_activity_log (br_id, action, source, log_date) 
            VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'iss', $br_id, $action, $source);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// =============================================
// دالة إرسال إشعار واتساب
// =============================================
function sendWhatsAppNotification(string $to, string $message, string $product_code = '', int $br_id = 0): bool {
    $whatsapp_config = [
        'api_url' => 'https://api.ultramsg.com/instanceXXXX/messages/chat',
        'token' => 'your_token_here'
    ];
    
    $full_message = "📋 طلب شراء جديد\n";
    if (!empty($product_code)) {
        $full_message .= "📦 كود المنتج: $product_code\n";
    }
    $full_message .= "📝 التفاصيل: $message\n";
    $full_message .= "🆔 رقم الطلب: $br_id\n";
    $full_message .= "🕐 التاريخ: " . date('Y-m-d H:i:s');
    
    $data = [
        'token' => $whatsapp_config['token'],
        'to' => $to,
        'body' => $full_message
    ];
    
    $ch = curl_init($whatsapp_config['api_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // تسجيل محاولة الإرسال في الجدول
    logWhatsAppAttempt($br_id, $to, $http_code == 200, $response);
    
    return $http_code == 200;
}

// =============================================
// دالة تسجيل محاولات واتساب
// =============================================
function logWhatsAppAttempt(int $br_id, string $to, bool $success, string $response): void {
    global $con;
    
    $sql = "INSERT INTO whatsapp_log (br_id, phone, success, response, log_date) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($con, $sql);
    $success_int = $success ? 1 : 0;
    mysqli_stmt_bind_param($stmt, 'isis', $br_id, $to, $success_int, $response);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// =============================================
// دالة جلب الطلبات حسب المصدر
// =============================================
function getRFQBySource(string $source_channel, int $limit = 100, int $offset = 0): mysqli_result {
    global $con;
    
    $sql = "SELECT * FROM buy_requirement 
            WHERE source_channel = ? 
            ORDER BY br_posting_date DESC 
            LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'sii', $source_channel, $limit, $offset);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// =============================================
// دالة إحصائيات الطلبات
// =============================================
function getRFQStats(): array {
    global $con;
    
    $sql = "SELECT 
                source_channel,
                COUNT(*) as total,
                SUM(CASE WHEN br_status = 'new' THEN 1 ELSE 0 END) as new_requests,
                SUM(CASE WHEN br_status IN ('contacted', 'quoted') THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN br_status IN ('won', 'closed') THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN br_status = 'lost' THEN 1 ELSE 0 END) as lost,
                DATE(br_posting_date) as date
            FROM buy_requirement 
            WHERE br_posting_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY source_channel, DATE(br_posting_date)
            ORDER BY source_channel, date DESC";
    
    $result = mysqli_query($con, $sql);
    $stats = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stats[$row['source_channel']][] = $row;
    }
    return $stats;
}
?>