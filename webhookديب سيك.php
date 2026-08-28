<?php
/**
 * webhook.php - ملف استقبال رسائل واتساب من UltraMsg API
 * هذا الملف هو "الريسبشن" الرئيسي الذي تستقبل عليه المنصة جميع رسائل العملاء
 */

// ============================================
// 1. إعدادات اتصال قاعدة البيانات (قم بتعديلها حسب إعداداتك)
// ============================================
$db_host = 'DB_HOST';     // مثال: localhost
$db_user = 'DB_USER';     // مثال: root
$db_pass = 'DB_PASS';     // مثال: 123456
$db_name = 'DB_NAME';     // مثال: whatsapp_bot

// إعدادات UltraMsg API
$ultramsg_instance_id = 'YOUR_INSTANCE_ID';      // معرف الإنستانس حقك من UltraMsg
$ultramsg_token = 'YOUR_ULTRAMSG_TOKEN';        // التوكن الخاص بـ UltraMsg
$ultramsg_api_url = "https://api.ultramsg.com/{$ultramsg_instance_id}/messages/chat";

// ============================================
// 2. الاتصال بقاعدة البيانات
// ============================================
try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Connection Failed: " . $e->getMessage());
    echo "Database Error";
    exit;
}

// ============================================
// 3. استقبال البيانات من UltraMsg
// ============================================
$input_data = file_get_contents('php://input');
if (empty($input_data)) {
    error_log("No input data received");
    echo "No Data";
    exit;
}

$webhook_data = json_decode($input_data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Invalid JSON received: " . $input_data);
    echo "Invalid JSON";
    exit;
}

// التأكد من نوع الحدث (event_type)
$event_type = $webhook_data['event_type'] ?? '';
if ($event_type !== 'message') {
    // إذا كان الحدث ليس رسالة، نخرج بدون معالجة (مثل حالة التسليم)
    echo "OK";
    exit;
}

// استخراج بيانات الرسالة
$from = $webhook_data['data']['from'] ?? '';        // رقم العميل (مرسل الرسالة)
$to = $webhook_data['data']['to'] ?? '';            // رقم واتساب المورد (المستقبل)
$customer_message = $webhook_data['data']['body'] ?? ''; // نص الرسالة

if (empty($from) || empty($to) || empty($customer_message)) {
    error_log("Missing required fields: from={$from}, to={$to}, message={$customer_message}");
    echo "Missing Fields";
    exit;
}

// تنظيف الأرقام (إزالة أي رموز غير أرقام)
$from = preg_replace('/[^0-9]/', '', $from);
$to = preg_replace('/[^0-9]/', '', $to);

// ============================================
// 4. البحث عن المورد باستخدام رقم الواتساب المستقبل
// ============================================
$supplier_query = "SELECT id, supplier_name, contact_name, contact_whatsapp 
                   FROM suppliers 
                   WHERE whatsapp_number = :to_number";
$supplier_stmt = $pdo->prepare($supplier_query);
$supplier_stmt->execute([':to_number' => $to]);
$supplier = $supplier_stmt->fetch();

// لو ملقاش المورد
if (!$supplier) {
    $error_message = "عذراً، رقم المورد غير مسجل عندنا";
    sendWhatsAppMessage($from, $error_message, $ultramsg_api_url, $ultramsg_token);
    error_log("Supplier not found for number: {$to}");
    echo "OK";
    exit;
}

$supplier_id = $supplier['id'];
$supplier_name = $supplier['supplier_name'];
$contact_name = $supplier['contact_name'];
$contact_whatsapp = $supplier['contact_whatsapp'];

// ============================================
// 5. جلب قائمة منتجات المورد من قاعدة البيانات
// ============================================
$products_query = "SELECT id, product_name, price, min_quantity, description 
                   FROM products 
                   WHERE supplier_id = :supplier_id 
                   ORDER BY id ASC";
$products_stmt = $pdo->prepare($products_query);
$products_stmt->execute([':supplier_id' => $supplier_id]);
$products = $products_stmt->fetchAll();

// بناء قائمة المنتجات النصية
$products_list = "";
foreach ($products as $product) {
    $products_list .= "{$product['id']}. {$product['product_name']} - السعر {$product['price']}ج - أقل كمية {$product['min_quantity']} - الوصف {$product['description']}\n";
}

// إذا لم يكن هناك منتجات مسجلة للمورد
if (empty($products_list)) {
    $products_list = "لا توجد منتجات مسجلة حالياً.";
}

// ============================================
// 6. استدعاء ملف ai_prompt.php لمعالجة الرسالة
// ============================================
// تحضير المتغيرات المطلوبة لملف ai_prompt.php
$supplier_name = $supplier_name;
$contact_name = $contact_name;
$products_list = $products_list;
$customer_message = $customer_message;

// تضمين ملف الذكاء الاصطناعي (المسار: includes/ai_prompt.php)
$ai_prompt_path = __DIR__ . '/includes/ai_prompt.php';
if (!file_exists($ai_prompt_path)) {
    error_log("ai_prompt.php not found at: {$ai_prompt_path}");
    $fallback_reply = "شكراً لتواصلك مع {$supplier_name}. سيتم الرد عليك قريباً.";
    sendWhatsAppMessage($from, $fallback_reply, $ultramsg_api_url, $ultramsg_token);
    echo "OK";
    exit;
}

// استخدام output buffering لالتقاط الرد من ai_prompt.php
ob_start();
include $ai_prompt_path;
$ai_reply = ob_get_clean();

// التأكد من وجود رد فعلي من الـ AI
if (empty($ai_reply)) {
    error_log("AI returned empty reply for supplier: {$supplier_name}");
    $ai_reply = "عذراً، حدث خطأ في معالجة طلبك. الرجاء المحاولة مرة أخرى.";
}

// ============================================
// 7. إرسال الرد إلى العميل
// ============================================
sendWhatsAppMessage($from, $ai_reply, $ultramsg_api_url, $ultramsg_token);

// ============================================
// 8. فحص وجود أمر شراء (ORDER_DATA)
// ============================================
if (strpos($ai_reply, 'ORDER_DATA:') !== false) {
    // استخراج جزء JSON من النص
    preg_match('/ORDER_DATA:\{.*\}/', $ai_reply, $matches);
    
    if (!empty($matches)) {
        $json_part = str_replace('ORDER_DATA:', '', $matches[0]);
        $order_data = json_decode($json_part, true);
        
        // التحقق من صحة JSON ووجود جميع الحقول المطلوبة
        if ($order_data && isset($order_data['name'], $order_data['phone'], $order_data['product'], $order_data['qty'], $order_data['address'])) {
            
            // تنظيف البيانات قبل الإدراج في قاعدة البيانات
            $customer_name = trim($order_data['name']);
            $customer_phone = preg_replace('/[^0-9]/', '', $order_data['phone']);
            $product_name = trim($order_data['product']);
            $quantity = intval($order_data['qty']);
            $address = trim($order_data['address']);
            
            // ============================================
            // 9. إدراج الطلب في جدول orders
            // ============================================
            $insert_order_query = "INSERT INTO orders (supplier_id, customer_phone, customer_name, product_name, quantity, address, status, created_at) 
                                   VALUES (:supplier_id, :customer_phone, :customer_name, :product_name, :quantity, :address, 'new', NOW())";
            $insert_stmt = $pdo->prepare($insert_order_query);
            
            try {
                $insert_stmt->execute([
                    ':supplier_id' => $supplier_id,
                    ':customer_phone' => $customer_phone,
                    ':customer_name' => $customer_name,
                    ':product_name' => $product_name,
                    ':quantity' => $quantity,
                    ':address' => $address
                ]);
                
                $order_id = $pdo->lastInsertId();
                error_log("New order created - ID: {$order_id}, Supplier: {$supplier_name}");
                
                // ============================================
                // 10. إرسال إشعار للمورد (على رقم contact_whatsapp)
                // ============================================
                $supplier_notification = "🛒 طلب جديد من {$customer_name}\n"
                                       . "📦 المنتج: {$product_name}\n"
                                       . "🔢 الكمية: {$quantity}\n"
                                       . "📍 العنوان: {$address}\n"
                                       . "📞 رقم العميل: {$customer_phone}\n"
                                       . "🆔 رقم الطلب: {$order_id}";
                
                if (!empty($contact_whatsapp)) {
                    sendWhatsAppMessage($contact_whatsapp, $supplier_notification, $ultramsg_api_url, $ultramsg_token);
                } else {
                    error_log("Contact whatsapp number is empty for supplier: {$supplier_name}");
                }
                
            } catch (PDOException $e) {
                error_log("Failed to insert order: " . $e->getMessage());
            }
        } else {
            error_log("Invalid ORDER_DATA JSON structure: " . $json_part);
        }
    }
}

// ============================================
// 11. إرجاع OK إلى UltraMsg
// ============================================
echo "OK";

// ============================================
// دالة مساعدة لإرسال رسائل واتساب عبر UltraMsg API
// ============================================
function sendWhatsAppMessage($to, $message, $api_url, $token) {
    // تنظيف رقم الهاتف (إزالة أي شيء غير أرقام)
    $to = preg_replace('/[^0-9]/', '', $to);
    
    // إضافة كود مصر إذا كان الرقم بدون كود دولي
    if (strlen($to) === 10) {
        $to = '20' . $to; // كود مصر هو 20
    } elseif (strlen($to) === 11 && substr($to, 0, 1) === '0') {
        $to = '20' . substr($to, 1);
    }
    
    // تجهيز البيانات للإرسال
    $post_data = [
        'token' => $token,
        'to' => $to,
        'body' => $message,
        'priority' => 1,  // أولوية عالية
        'referenceId' => ''  // اختياري
    ];
    
    // تهيئة cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    
    // تنفيذ الإرسال
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // تسجيل الأخطاء إن وجدت
    if ($curl_error) {
        error_log("UltraMsg cURL Error: " . $curl_error);
    } elseif ($http_code !== 200) {
        error_log("UltraMsg API Error (HTTP {$http_code}): " . $response);
    } else {
        // محاولة فك الرد من UltraMsg للتأكد من النجاح
        $response_data = json_decode($response, true);
        if (isset($response_data['error'])) {
            error_log("UltraMsg Error Response: " . print_r($response_data, true));
        }
    }
    
    return $response;
}
?>