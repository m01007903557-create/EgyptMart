<?php
/**
 * File: admin/product-view-response.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: معالجة طلبات AJAX لعرض قائمة المنتجات
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../common.php";
require_once "../lib/pagination.php";

// التحقق من تسجيل دخول المستخدم
check_user_login();

global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    $response = [
        "draw" => isset($_REQUEST['draw']) ? (int)$_REQUEST['draw'] : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Database connection failed"
    ];
    echo json_encode($response);
    exit;
}

try {
    // معاملات DataTable
    $draw = isset($_REQUEST['draw']) ? (int)$_REQUEST['draw'] : 0;
    $start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
    $length = isset($_REQUEST['length']) ? (int)$_REQUEST['length'] : 10;
    $searchValue = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';

    // استعلام الأساس
    $baseQuery = "FROM products p 
                  LEFT JOIN measurement_unit mu ON p.pd_unit = mu.mu_id 
                  LEFT JOIN country c ON p.pd_currency = c.cn_id 
                  LEFT JOIN user u ON p.pd_uid = u.usr_id 
                  LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                  WHERE 1=1";

    // البحث
    $searchWhere = '';
    if (!empty($searchValue)) {
        $escapedSearch = mysqli_real_escape_string($con, $searchValue);
        $searchWhere = " AND (p.pd_title LIKE '%$escapedSearch%' 
                              OR p.pd_description LIKE '%$escapedSearch%' 
                              OR u.email LIKE '%$escapedSearch%')";
    }

    // العدد الإجمالي
    $countSql = "SELECT COUNT(*) as total " . $baseQuery;
    $countResult = mysqli_query($con, $countSql);
    $totalRecords = $countResult ? (int)mysqli_fetch_assoc($countResult)['total'] : 0;

    // العدد بعد الفلتر
    $filteredSql = "SELECT COUNT(*) as total " . $baseQuery . $searchWhere;
    $filteredResult = mysqli_query($con, $filteredSql);
    $recordsFiltered = $filteredResult ? (int)mysqli_fetch_assoc($filteredResult)['total'] : 0;

    // جلب البيانات
    $dataSql = "SELECT p.*, mu.mu_name, c.cn_currency, c.cn_name, 
                       u.email, u.fname, u.lname, bp.bnsprof_compname
                " . $baseQuery . $searchWhere . " 
                ORDER BY p.pd_id DESC 
                LIMIT $start, $length";

    $dataResult = mysqli_query($con, $dataSql);
    $data = [];

    if ($dataResult) {
        while ($row = mysqli_fetch_assoc($dataResult)) {
            $data[] = [
                (int)$row['pd_id'],
                htmlspecialchars($row['pd_title'] ?? ''),
                htmlspecialchars($row['bnsprof_compname'] ?? ''),
                htmlspecialchars($row['cn_name'] ?? ''),
                number_format((float)($row['pd_fob_price'] ?? 0), 2),
                htmlspecialchars($row['mu_name'] ?? ''),
                '<a href="product-edit.php?id=' . $row['pd_id'] . '" class="btn btn-xs btn-info">Edit</a>',
                '<a href="product-delete.php?id=' . $row['pd_id'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</a>'
            ];
        }
    }

    $response = [
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $recordsFiltered,
        "data" => $data
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("product-view-response.php: " . $e->getMessage());
    $response = [
        "draw" => $draw ?? 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => $e->getMessage()
    ];
    echo json_encode($response);
}

ob_end_flush();
?>