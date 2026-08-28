<?php
// api/products/create-draft.php
require_once __DIR__ . '/includes/auth.php';
authenticate();

$input = json_decode(file_get_contents('php://input'), true);
$supplier_id = (int)($input['supplier_id'] ?? 0);
$batch_id = $input['batch_id'] ?? '';
$products = $input['products'] ?? [];
$uploaded_images = $input['uploaded_images'] ?? [];

if ($supplier_id <= 0 || empty($batch_id) || empty($products)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$product_ids = [];

foreach ($products as $product) {
    $name = mysqli_real_escape_string($con, $product['name'] ?? '');
    $description = mysqli_real_escape_string($con, $product['description'] ?? '');
    $price = (float)($product['price'] ?? 0);
    $category = mysqli_real_escape_string($con, $product['category'] ?? '');
    $image_indexes = $product['image_indexes'] ?? [];
    
    // ربط الصور الصحيحة
    $product_images = [];
    foreach ($image_indexes as $index) {
        if (isset($uploaded_images[$index])) {
            $product_images[] = $uploaded_images[$index];
        }
    }
    $images_json = json_encode($product_images);
    
    // إنشاء Slug
    $base_slug = strtolower(str_replace(' ', '-', $name));
    $slug = $base_slug;
    $counter = 1;
    while (slug_exists($slug)) {
        $slug = $base_slug . '-' . $counter++;
    }
    
    $sql = "INSERT INTO products (
        supplier_id, batch_id, product_name, description, price, category, 
        images, slug, processing_status, status, created_at
    ) VALUES (
        ?, ?, ?, ?, ?, ?, 
        ?, ?, 'draft', 'draft', NOW()
    )";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'isssdsss', 
        $supplier_id, $batch_id, $name, $description, $price, $category,
        $images_json, $slug
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $product_ids[] = mysqli_insert_id($con);
    }
    mysqli_stmt_close($stmt);
}

echo json_encode([
    'status' => 'success',
    'product_ids' => $product_ids,
    'batch_id' => $batch_id,
    'processing_status' => 'draft',
    'message' => 'Products saved as draft'
]);
?>