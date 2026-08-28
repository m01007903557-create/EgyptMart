<?php
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// كود تشخيصي مؤقت
$debug_file = '/home/u397968200/domains/egyptmart.shop/public_html/logs/product_view_debug.log';
file_put_contents($debug_file, date('Y-m-d H:i:s') . " - product-view-response.php called\n", FILE_APPEND);
file_put_contents($debug_file, "POST/GET: " . print_r($_REQUEST, true) . "\n", FILE_APPEND);

// محاكاة استجابة بسيطة للاختبار
if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == '1') {
    $test_data = [
        "draw" => 1,
        "recordsTotal" => 5,
        "recordsFiltered" => 5,
        "data" => [
            ['1', 'Test Product 1', 'Active', 'Edit', 'Delete'],
            ['2', 'Test Product 2', 'Active', 'Edit', 'Delete'],
            ['3', 'Test Product 3', 'Inactive', 'Edit', 'Delete'],
        ]
    ];
    echo json_encode($test_data);
    exit;
}

// باقي الكود الأصلي...
/**
 * File: product-view-response.php
 * Version: 3.0.0 (PHP 8.3)
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

// معاملات DataTable
$params = $_REQUEST;
$draw = isset($params['draw']) ? (int)$params['draw'] : 0;
$start = isset($params['start']) ? (int)$params['start'] : 0;
$length = isset($params['length']) ? (int)$params['length'] : 10;
$searchValue = isset($params['search']['value']) ? trim($params['search']['value']) : '';

// أعمدة الجدول
$columns = [
    1 => 'p.pd_date',
    3 => 'p.pd_title',
    4 => 'pc.pc_name',
    5 => 'p.pd_fob_price',
    6 => 'bf.bnsprof_compname',
    7 => 'c.cn_name',
    8 => 'sp.mst_name',
    9 => 'pm.expiry_date'
];

// تحديد عمود الترتيب
$orderColumn = isset($params['order'][0]['column']) ? (int)$params['order'][0]['column'] : 1;
$orderDir = isset($params['order'][0]['dir']) && strtolower($params['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';

// بناء الاستعلام الأساسي
$sql = "SELECT p.pd_id, p.pd_so_slider, p.pd_pck_dets, p.pd_lp_slider, p.pd_uid, 
               p.pd_image, p.pd_date, p.pd_imagelogo, p.pd_title, p.pd_status, 
               p.pd_fob_price, p.pd_fob_price2, p.pd_currency,
               pc.pc_name, pc1.pc_name as pc1_name, pc2.pc_name as pc2_name,
               c.cn_name, bf.bnsprof_compname, 
               pm.expiry_date, sp.mst_name 
        FROM products p 
        JOIN product_category_arabyos pc ON p.pd_subcat_id = pc.pc_id 
        JOIN product_category_arabyos pc1 ON pc1.pc_id = pc.pc_parent_id 
        JOIN product_category_arabyos pc2 ON pc2.pc_id = pc1.pc_parent_id 
        JOIN user u ON u.usr_id = p.pd_uid 
        JOIN business_profile bf ON bf.bnsprof_uid = p.pd_uid 
        JOIN country c ON c.cn_id = u.country 
        LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
        LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id 
        WHERE 1=1";

// إضافة شروط البحث
$where = "";
if (!empty($searchValue)) {
    $escapedSearch = mysqli_real_escape_string($con, $searchValue);
    $where = " AND (p.pd_title LIKE '%$escapedSearch%' 
                   OR pc.pc_name LIKE '%$escapedSearch%' 
                   OR bf.bnsprof_compname LIKE '%$escapedSearch%' 
                   OR c.cn_name LIKE '%$escapedSearch%' 
                   OR p.pd_date LIKE '%$escapedSearch%' 
                   OR p.pd_fob_price LIKE '%$escapedSearch%' 
                   OR pc1.pc_name LIKE '%$escapedSearch%' 
                   OR pc2.pc_name LIKE '%$escapedSearch%' 
                   OR sp.mst_name LIKE '%$escapedSearch%')";
    
    $searchLower = strtolower($searchValue);
    if ($searchLower == 'active') {
        $where .= " OR pm.expiry_date > UNIX_TIMESTAMP()";
    } elseif ($searchLower == 'inactive') {
        $where .= " OR pm.expiry_date < UNIX_TIMESTAMP()";
    } elseif ($searchLower == 'permanent') {
        $where .= " OR pm.expiry_date = 253392431400";
    }
}

// حساب العدد الإجمالي
$countSql = "SELECT COUNT(*) as count " . substr($sql, strpos($sql, "FROM"));
$countResult = mysqli_query($con, $countSql);
$totalRecords = 0;
if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalRecords = (int)$countRow['count'];
}

// إضافة شرط البحث وترتيب
$sql .= $where;
$orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'p.pd_date';
$sql .= " ORDER BY $orderBy $orderDir LIMIT $start, $length";

// تنفيذ الاستعلام
$result = mysqli_query($con, $sql);
$data = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // معالجة الصور
        $imageHtml = '<div style="position: relative;">';
        $img = $row['pd_image'] ?? '';
        $imgArr = explode(',', $img);
        $mainImg = !empty($imgArr[0]) ? $imgArr[0] : 'noimage.jpg';
        
        if (!empty($row['pd_imagelogo'])) {
            $logoArr = explode(',', $row['pd_imagelogo']);
            $imageHtml .= '<a href="#"><img src="https://egyptmart.shop/upload/myproduct/' . htmlspecialchars($logoArr[0]) . '" style="position: absolute; top: 48px; width: 30px; height: 29px;" /></a>';
        }
        $imageHtml .= '<a href="#"><img src="https://egyptmart.shop/upload/myproduct/' . htmlspecialchars($mainImg) . '" style="width: 80px; height: 78px;" /></a></div>';
        
        // معالجة حالة المنتج
        $statusHtml = '';
        $pdStatus = (int)($row['pd_status'] ?? 0);
        if ($pdStatus == 0) {
            $statusHtml = '<a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve"><img alt="Approve" src="images/active.jpg"></a>&nbsp;';
            $statusHtml .= '<a data-id="' . $row['pd_id'] . '" class="disapprove_product" title="Disapprove"><img alt="Disapprove" src="images/reject.png" width="19" height="19" border="0"></a>';
        } elseif ($pdStatus == 1) {
            $statusHtml = '<font color="#009933" weight="800">Approved</font>&nbsp;</br><a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve">Re-send</a>';
        } elseif ($pdStatus == 2) {
            $statusHtml = '<font color="#CC0000" weight="800">Rejected</font>&nbsp;</br><a data-id="' . $row['pd_id'] . '" class="approve_product" title="Approve">Re-send</a>';
        }
        
        // معالجة أزرار السلايدر
        $saleoffer = '<a data-id="saleoffer-' . $row['pd_id'] . '" id="saleoffer-' . $row['pd_id'] . '" class="add_sales_offer" title="Sale Offer">saleoffer</a>';
        $leaderP = '<a data-id="leader-' . $row['pd_id'] . '" id="leader-' . $row['pd_id'] . '" class="add_slider" title="Leader Products">leader products</a>';
        $loyalSe = '<a data-id="loyal-' . $row['pd_id'] . '" id="loyal-' . $row['pd_id'] . '" class="add_slider" title="Loyal Service">loyal service</a>';
        
        if (isset($row['pd_so_slider']) && $row['pd_so_slider'] == 1) {
            $saleoffer = '<a data-id="#" id="#" class="remove_slider" title="Remove" style="color:Red">saleoffer</a>';
        }
        if (isset($row['pd_pck_dets']) && $row['pd_pck_dets'] == 1) {
            $leaderP = '<a data-id="leader-' . $row['pd_id'] . '" id="leader-' . $row['pd_id'] . '" class="remove_slider" title="Remove" style="color:Red">leader products</a>';
        }
        if (isset($row['pd_lp_slider']) && $row['pd_lp_slider'] == 1) {
            $loyalSe = '<a data-id="loyal-' . $row['pd_id'] . '" id="loyal-' . $row['pd_id'] . '" class="remove_slider" title="Remove" style="color:Red">loyal service</a>';
        }
        
        // مسار الحذف
        $dellink = $_SERVER['QUERY_STRING'] == "" 
            ? "?action=del&ad-id=" . $row['pd_id']
            : $_SERVER['QUERY_STRING'] . "&action=del&ad-id=" . $row['pd_id'];
        
        // معالجة تاريخ انتهاء العضوية
        $expiryHtml = '';
        if (!empty($row['expiry_date'])) {
            $expiryDate = (int)$row['expiry_date'];
            if ($expiryDate == 253392431400) {
                $expiryHtml = 'Permanent';
            } else {
                $expiryHtml = date("d F Y", $expiryDate) . ' ' . (date("Y-m-d", $expiryDate) > date("Y-m-d") ? 'Active' : 'Inactive');
            }
        }
        
        // بناء صف البيانات
        $data[] = [
            '<input name="cb[]" class="ace" type="checkbox" value="' . $row['pd_id'] . '" /><span class="lbl"></span>',
            date('d M, y', strtotime($row['pd_date'] ?? 'now')),
            $imageHtml,
            htmlspecialchars(ucwords(stripslashes($row['pd_title'] ?? ''))),
            htmlspecialchars(ucwords(stripslashes($row['pc2_name'] ?? '')) . ' / ' . 
                           ucwords(stripslashes($row['pc1_name'] ?? '')) . ' / ' . 
                           ucwords(stripslashes($row['pc_name'] ?? ''))),
            htmlspecialchars($row['pd_fob_price'] ?? '0'),
            htmlspecialchars($row['bnsprof_compname'] ?? ''),
            htmlspecialchars($row['cn_name'] ?? ''),
            htmlspecialchars($row['mst_name'] ?? ''),
            $expiryHtml,
            '<a href="product-details.php?token=' . rand(1000, 9999) . md5((string)$row['pd_id']) . '"><img src="images/details.png" /></a>',
            $statusHtml,
            '<a href="product-edit.php?fid=' . $row['pd_id'] . '" title="Edit"><img src="images/edit.jpg"/></a>
             <a href="' . $dellink . '" onclick="return confirm(\'Are you sure to Delete the Product?\')" title="Delete"><img src="images/delete.jpg"/></a>',
            "$saleoffer | $leaderP | $loyalSe"
        ];
    }
}

// إرجاع البيانات بصيغة JSON
$response = [
    "draw" => $draw,
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data" => $data
];

header('Content-Type: application/json');
echo json_encode($response);

ob_end_flush();
?>