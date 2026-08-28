<?php
// api/suppliers.php
require_once __DIR__ . '/includes/auth.php';

authenticate();

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

if ($method === 'GET' && end($segments) === 'check-phone') {
    $input = json_decode(file_get_contents('php://input'), true);
    $phone = $input['phone'] ?? '';
    
    if (empty($phone)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Phone number is required']);
        exit;
    }
    
    $sql = "SELECT usr_id, usr_name, status FROM user WHERE mobile1 = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $phone);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($row) {
        echo json_encode([
            'exists' => true,
            'user_id' => (int)$row['usr_id'],
            'status' => $row['status']
        ]);
    } else {
        echo json_encode([
            'exists' => false,
            'user_id' => null,
            'status' => null
        ]);
    }
    exit;
}

if ($method === 'POST' && end($segments) === 'register') {
    $input = json_decode(file_get_contents('php://input'), true);
    $phone = $input['phone'] ?? '';
    $source_channel = $input['source_channel'] ?? 'whatsapp';
    
    if (empty($phone)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Phone number is required']);
        exit;
    }
    
    $user_name = "مورد واتساب - " . substr($phone, -4);
    $status = 'pending_products';
    
    $sql = "INSERT INTO user (mobile1, user_name, status, source_channel, register_date) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'ssss', $phone, $user_name, $status, $source_channel);
    
    if (mysqli_stmt_execute($stmt)) {
        $supplier_id = mysqli_insert_id($con);
        mysqli_stmt_close($stmt);
        
        // إنشاء Mini Site
        $brand_name = "شركة_" . $supplier_id;
        $slug = strtolower($brand_name);
        
        $mini_sql = "INSERT INTO mini_sites (supplier_id, brand_name, slug, status, created_at) 
                     VALUES (?, ?, ?, 'draft', NOW())";
        $mini_stmt = mysqli_prepare($con, $mini_sql);
        mysqli_stmt_bind_param($mini_stmt, 'iss', $supplier_id, $brand_name, $slug);
        mysqli_stmt_execute($mini_stmt);
        mysqli_stmt_close($mini_stmt);
        
        echo json_encode([
            'status' => 'success',
            'supplier_id' => $supplier_id,
            'mini_site_url' => "https://egyptmart.shop/" . $slug,
            'status' => 'pending_products'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
    }
    exit;
}
?>