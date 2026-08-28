<?php
// api/get_products_for_automation.php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../lib/connect.php';

$secret = 'EhabFawzy@64';   // ← تأكد أنها نفس الكلمة في webhook.php

if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$supplier_id = $_GET['user_id'] ?? null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

try {
    $sql = "SELECT pd_id, pd_title, pd_fob_price, pd_currency, pd_desc, 
                   pd_min_order_qty, pd_unit, brand_name, pd_image, 
                   pd_status, is_new_post, pd_date
            FROM products 
            WHERE pd_status = 1 ";

    if ($supplier_id) {
        $sql .= " AND pd_uid = ? ";
    }

    $sql .= " ORDER BY pd_date DESC LIMIT ?";

    $stmt = mysqli_prepare($con, $sql);

    if ($supplier_id) {
        mysqli_stmt_bind_param($stmt, "ii", $supplier_id, $limit);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $limit);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'count' => count($products),
        'supplier_id' => $supplier_id,
        'products' => $products
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}