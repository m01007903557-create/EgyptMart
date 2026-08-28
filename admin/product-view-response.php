<?php
/**
 * File: product-view-response.php
 * Version: 4.0.0 (PHP 8.3)
 * Description: معالجة طلبات AJAX لعرض قائمة المنتجات
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    $response = [
        "draw" => isset($_REQUEST['draw']) ? (int)$_REQUEST['draw'] : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Unauthorized"
    ];
    echo json_encode($response);
    exit;
}

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
    $searchValue = isset($_REQUEST['search']['value']) ? trim($_REQUEST['search']['value']) : '';

    // استعلام بسيط لجلب البيانات
    $sql = "SELECT p.pd_id, p.pd_date, p.pd_image, p.pd_title, p.pd_status,
                   p.pd_fob_price, p.pd_fob_price2,
                   bf.bnsprof_compname as company_name,
                   c.cn_name as country_name,
                   sp.mst_name as membership_type,
                   pm.expiry_date
            FROM products p 
            LEFT JOIN user u ON p.pd_uid = u.usr_id 
            LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid 
            LEFT JOIN country c ON u.country = c.cn_id 
            LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
            LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id 
            WHERE 1=1";

    // إضافة شرط البحث
    if (!empty($searchValue)) {
        $escapedSearch = mysqli_real_escape_string($con, $searchValue);
        $sql .= " AND (p.pd_title LIKE '%$escapedSearch%' 
                      OR bf.bnsprof_compname LIKE '%$escapedSearch%')";
    }

    // حساب العدد الإجمالي
    $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as t";
    $countResult = mysqli_query($con, $countSql);
    $totalRecords = 0;
    if ($countResult) {
        $countRow = mysqli_fetch_assoc($countResult);
        $totalRecords = (int)$countRow['total'];
    }

    // إضافة الترتيب والحد
    $sql .= " ORDER BY p.pd_id DESC LIMIT $start, $length";
    $result = mysqli_query($con, $sql);

    $data = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // معالجة الصورة
            $imageHtml = '<img src="../upload/myproduct/noimage.jpg" style="width: 50px; height: 50px; object-fit: cover;">';
            if (!empty($row['pd_image'])) {
                $imageHtml = '<img src="../upload/myproduct/' . htmlspecialchars($row['pd_image']) . '" style="width: 50px; height: 50px; object-fit: cover;">';
            }
            
            // معالجة حالة المنتج
            $status = (int)($row['pd_status'] ?? 0);
            if ($status == 1) {
                $statusHtml = '<span class="label label-success">Approved</span>';
            } elseif ($status == 2) {
                $statusHtml = '<span class="label label-danger">Rejected</span>';
            } else {
                $statusHtml = '<span class="label label-warning">Pending</span>';
            }
            
            // معالجة تاريخ انتهاء العضوية
            $expiryHtml = '';
            if (!empty($row['expiry_date'])) {
                $expiryDate = (int)$row['expiry_date'];
                if ($expiryDate == 253392431400) {
                    $expiryHtml = 'Permanent';
                } else {
                    $expiryHtml = date('d M Y', $expiryDate);
                }
            }
            
            $data[] = [
    '<input type="checkbox" name="cb[]" value="' . $row['pd_id'] . '">',  // 0
    date('d M Y', strtotime($row['pd_date'] ?? 'now')),                    // 1
    $imageHtml,                                                             // 2
    htmlspecialchars($row['pd_title'] ?? ''),                               // 3
    '',  // category - سيتم إضافته لاحقاً                                  // 4
    number_format((float)($row['pd_fob_price'] ?? 0), 2),                   // 5
    htmlspecialchars($row['company_name'] ?? ''),                           // 6
    htmlspecialchars($row['country_name'] ?? ''),                           // 7
    htmlspecialchars($row['membership_type'] ?? 'Junior'),                  // 8
    $expiryHtml,                                                            // 9
    '<a href="product-details.php?id=' . $row['pd_id'] . '"><img src="images/details.png" /></a>', // 10
    $statusHtml,                                                            // 11
    '<a href="product-edit.php?id=' . $row['pd_id'] . '"><img src="images/edit.jpg"/></a>
     <a href="?action=del&ad-id=' . $row['pd_id'] . '" onclick="return confirm(\'Delete?\')"><img src="images/delete.jpg"/></a>', // 12
    '<a href="#" onclick="addToSlider(' . $row['pd_id'] . ')">Add to Slider</a>' // 13
];
        }
    }

    $response = [
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $totalRecords,
        "data" => $data
    ];

    header('Content-Type: application/json');
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