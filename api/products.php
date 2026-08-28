<?php
// api/products.php
require_once __DIR__ . '/includes/auth.php';

authenticate();

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

if ($method === 'POST' && end($segments) === 'extract-from-images') {
    $input = json_decode(file_get_contents('php://input'), true);
    $supplier_id = (int)($input['supplier_id'] ?? 0);
    $images = $input['images'] ?? [];
    
    if ($supplier_id <= 0 || empty($images)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Supplier ID and images are required']);
        exit;
    }
    
    // =====================================================
    // 🔮 هنا سيتم استدعاء خدمة الذكاء الاصطناعي لاستخراج البيانات
    // حاليًا نفترض بيانات وهمية للاختبار
    // =====================================================
    
    $extracted_products = [
        [
            'name' => 'منتج تجريبي 1',
            'description' => 'وصف المنتج التجريبي المستخرج من الصور',
            'price' => 1500,
            'category' => 'مواد بناء'
        ]
    ];
    
    echo json_encode([
        'status' => 'success',
        'products' => $extracted_products
    ]);
    exit;
}

if ($method === 'POST' && end($segments) === 'create-draft') {
    $input = json_decode(file_get_contents('php://input'), true);
    $supplier_id = (int)($input['supplier_id'] ?? 0);
    $products = $input['products'] ?? [];
    
    if ($supplier_id <= 0 || empty($products)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Supplier ID and products are required']);
        exit;
    }
    
    $product_ids = [];
    
    foreach ($products as $product) {
        $name = mysqli_real_escape_string($con, $product['name'] ?? '');
        $description = mysqli_real_escape_string($con, $product['description'] ?? '');
        $price = (float)($product['price'] ?? 0);
        $category = mysqli_real_escape_string($con, $product['category'] ?? '');
        $images = json_encode($product['images'] ?? []);
        
        $sql = "INSERT INTO products (supplier_id, product_name, description, price, category, images, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'draft', NOW())";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'issdss', $supplier_id, $name, $description, $price, $category, $images);
        
        if (mysqli_stmt_execute($stmt)) {
            $product_ids[] = mysqli_insert_id($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    echo json_encode([
        'status' => 'success',
        'product_ids' => $product_ids,
        'status' => 'draft'
    ]);
    exit;
}
?>