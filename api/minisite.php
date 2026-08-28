<?php
// api/minisite.php
require_once __DIR__ . '/includes/auth.php';

authenticate();

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

if ($method === 'POST' && end($segments) === 'update') {
    $input = json_decode(file_get_contents('php://input'), true);
    $supplier_id = (int)($input['supplier_id'] ?? 0);
    $products = $input['products'] ?? [];
    
    if ($supplier_id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Supplier ID is required']);
        exit;
    }
    
    // التحقق من وجود Mini Site
    $check_sql = "SELECT ms_id, slug FROM mini_sites WHERE supplier_id = ?";
    $check_stmt = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($check_stmt, 'i', $supplier_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $site = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);
    
    if ($site) {
        $slug = $site['slug'];
        // تحديث حالة Mini Site
        $update_sql = "UPDATE mini_sites SET status = 'active', updated_at = NOW() WHERE supplier_id = ?";
        $update_stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($update_stmt, 'i', $supplier_id);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
    } else {
        // إنشاء Mini Site جديد
        $brand_name = "شركة_" . $supplier_id;
        $slug = strtolower($brand_name);
        $insert_sql = "INSERT INTO mini_sites (supplier_id, brand_name, slug, status, created_at) 
                       VALUES (?, ?, ?, 'active', NOW())";
        $insert_stmt = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, 'iss', $supplier_id, $brand_name, $slug);
        mysqli_stmt_execute($insert_stmt);
        mysqli_stmt_close($insert_stmt);
    }
    
    echo json_encode([
        'status' => 'success',
        'mini_site_url' => "https://egyptmart.shop/" . ($site['slug'] ?? $slug),
        'status' => 'updated'
    ]);
    exit;
}
?>