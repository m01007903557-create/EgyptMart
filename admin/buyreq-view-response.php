<?php
// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// تشغيل عرض الأخطاء (للتصحيح فقط)
ini_set("display_errors", 1);

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    $json_data = array(
        "draw"            => 0,
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => [],
        "error"           => "Database connection error"
    );
    echo json_encode($json_data);
    exit();
}

// تهيئة المتغيرات
$params = $_REQUEST;
$columns = array(
    1 => 'br_posting_date',
    2 => 'br_pd_name', 
    3 => 'br_requirement',
    4 => 'bnsprof_compname',
    5 => 'cn_name',
    6 => 'mst_name',
    7 => 'expiry_date',
);

$where = "";
$sqlTot = "";
$sqlRec = "";

// التحقق من وجود قيمة بحث
if (!empty($params['search']['value'])) {
    $search_value = mysqli_real_escape_string($con, $params['search']['value']);
    $where .= " WHERE ";
    $where .= " (br_pd_name LIKE '%" . $search_value . "%' ";
    $where .= " OR br_requirement LIKE '%" . $search_value . "%' ";
    $where .= " OR bnsprof_compname LIKE '%" . $search_value . "%' ";
    $where .= " OR cn_name LIKE '%" . $search_value . "%' ";
    $where .= " OR br_updated_date LIKE '%" . $search_value . "%' ";
    $where .= " OR sp.mst_name LIKE '%" . $search_value . "%' )";
}

// استعلام جلب البيانات
$sql_base = "SELECT br.br_id, br.br_u_id, br.br_pic, br.br_posting_date, 
                    br.br_requirement, br.br_pd_name, br.br_approval_status,
                    c.cn_name, bf.bnsprof_compname, sp.mst_name
             FROM buy_requirement br
             JOIN measurement_unit mu ON br.br_estimate_qty_unit = mu.mu_id
             JOIN user u ON u.usr_id = br.br_u_id
             LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id
             LEFT JOIN country c ON c.cn_id = u.country
             LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id
             LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id
             LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";

// استعلام العدد الكلي
$sqlTot = "SELECT COUNT(*) as count 
           FROM buy_requirement br
           JOIN measurement_unit mu ON br.br_estimate_qty_unit = mu.mu_id
           JOIN user u ON u.usr_id = br.br_u_id
           LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id
           LEFT JOIN country c ON c.cn_id = u.country
           LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id
           LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id
           LEFT JOIN smembership_plan sp ON sp.mp_id = pm.p_id";

$sqlRec = $sql_base;

// إضافة شرط البحث إذا وجد
if (!empty($where)) {
    $sqlTot .= $where;
    $sqlRec .= $where;
}

// إعداد الترتيب
if ($params['order'][0]['column'] == 0) {
    $params['order'][0]['column'] = 1;
    $params['order'][0]['dir'] = 'desc';
}

// إضافة الترتيب والحدود
$order_column = isset($columns[$params['order'][0]['column']]) ? $columns[$params['order'][0]['column']] : 'br_id';
$order_dir = ($params['order'][0]['dir'] == 'asc') ? 'ASC' : 'DESC';
$start = (int)$params['start'];
$length = (int)$params['length'];

$sqlRec .= " ORDER BY " . $order_column . " " . $order_dir . " 
             LIMIT " . $start . ", " . $length;

// تنفيذ استعلام العدد الكلي
$queryTot = mysqli_query($con, $sqlTot);
$totalRecords = 0;

if ($queryTot && mysqli_num_rows($queryTot) > 0) {
    $queryTotObj = mysqli_fetch_object($queryTot);
    $totalRecords = (int)$queryTotObj->count;
}

// تنفيذ استعلام جلب البيانات
$queryRecords = mysqli_query($con, $sqlRec);

if (!$queryRecords) {
    error_log("خطأ في استعلام طلبات الشراء: " . mysqli_error($con));
    $json_data = array(
        "draw"            => intval($params['draw']),
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => [],
        "error"           => mysqli_error($con)
    );
    echo json_encode($json_data);
    exit();
}

// تجهيز البيانات
$data = array();

while ($row = mysqli_fetch_object($queryRecords)) {
    
    // إنشاء روابط الإجراءات
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    
    $plink = empty($query_string) 
        ? "?action=appr&id=" . $row->br_id
        : "buyreq-view.php?" . $query_string . "&action=appr&id=" . $row->br_id;
    
    $dlink = empty($query_string)
        ? "?action=disappr&id=" . $row->br_id
        : "buyreq-view.php?" . $query_string . "&action=disappr&id=" . $row->br_id;
    
    $dellink = empty($query_string)
        ? "?action=del&ad-id=" . $row->br_id
        : $query_string . "&action=del&ad-id=" . $row->br_id;
    
    // رابط الموافقة المختصر (لإعادة الإرسال)
    $ballink = empty($query_string)
        ? "?action=appr&id=" . $row->br_id
        : "?action=appr&id=" . $row->br_id;
    
    // ✅ تحديد مسار الصورة الصحيح (يدعم الجاليري والديسكتوب)
    $img = '<img src="../upload/buy_requirement/thumb/no-image.png" border="0" hspace="0" vspace="0" style="max-width: 60px; max-height: 60px;" />';
    
    if (!empty($row->br_pic)) {
        $image_path = $row->br_pic;
        
        // صورة من الجاليري
        if (strpos($image_path, 'upload/image_gallery/') !== false) {
            $img = '<img src="../' . $image_path . '" border="0" hspace="0" vspace="0" style="max-width: 60px; max-height: 60px; object-fit:cover;" />';
        }
        // صورة من رفع الديسكتوب (مسار كامل)
        elseif (strpos($image_path, 'upload/buy_requirement/') !== false) {
            $img = '<img src="../' . $image_path . '" border="0" hspace="0" vspace="0" style="max-width: 60px; max-height: 60px; object-fit:cover;" />';
        }
        // مجرد اسم ملف
        else {
            $img = '<img src="../upload/buy_requirement/thumb/' . htmlspecialchars($image_path) . '" border="0" hspace="0" vspace="0" style="max-width: 60px; max-height: 60px; object-fit:cover;" />';
        }
    }
    
    // تحديد نص الحالة
    $status_html = '';
    if ($row->br_approval_status == '0') {
        $status_html = '<a href="' . $plink . '" title="Approve" onclick="return confirm(\'Are you sure to approve this request?\')">
                            <img alt="Approve" src="images/active.jpg">
                        </a>&nbsp;
                        <a href="' . $dlink . '" title="Disapprove" onclick="return confirm(\'Are you sure to disapprove this request?\')">
                            <img alt="Disapprove" src="images/reject.png" width="19" height="19" border="0">
                        </a>';
    } elseif ($row->br_approval_status == '1') {
        $status_html = '<span style="color:#009933; font-weight:800;">Approved</span>';
        $status_html .= '<br><a href="' . $ballink . '" class="approve_product" title="Approve" onclick="return confirm(\'Are you sure to resend approval?\')">Re-send</a>';
    } elseif ($row->br_approval_status == '2') {
        $status_html = '<span style="color:#CC0000; font-weight:800;">Rejected</span>';
    }
    
    // تنسيق التاريخ
    $formatted_date = '';
    if (!empty($row->br_posting_date)) {
        try {
            $date_obj = date_create($row->br_posting_date);
            $formatted_date = $date_obj ? date_format($date_obj, "jS F Y") : $row->br_posting_date;
        } catch (Exception $e) {
            $formatted_date = $row->br_posting_date;
        }
    }
    
    // إنشاء صف البيانات
    $res = array();
    $res[0] = '<input name="cb[]" class="ace" type="checkbox" value="' . (int)$row->br_id . '" /><span class="lbl"></span>';
    $res[1] = htmlspecialchars($formatted_date);
    $res[2] = $img;  // ✅ الصورة هنا
    $res[3] = htmlspecialchars(ucwords(stripslashes($row->br_pd_name ?? '')));
    $res[4] = htmlspecialchars(ucwords(stripslashes($row->br_requirement ?? '')));
    $res[5] = htmlspecialchars(ucwords(stripslashes($row->bnsprof_compname ?? '')));
    $res[6] = htmlspecialchars(ucwords(stripslashes($row->cn_name ?? '')));
    $res[7] = htmlspecialchars(ucwords(stripslashes($row->mst_name ?? '')));
    $res[8] = $status_html;
    $res[9] = '<a href="buyreq-details.php?token=' . rand(1000, 9000) . md5((string)$row->br_id) . '" title="View Details">
                   <img src="images/details.png" alt="Details" />
               </a>
               <a href="buyreq-edit.php?token=' . md5((string)$row->br_id) . '" title="Edit">
                   <img src="images/edit.jpg" alt="Edit" />
               </a>';
    
    $data[] = $res;
}

// إنشاء مصفوفة النتائج النهائية
$json_data = array(
    "draw"            => intval($params['draw']),
    "recordsTotal"    => $totalRecords,
    "recordsFiltered" => $totalRecords,
    "data"            => $data
);

// إرسال البيانات بصيغة JSON
echo json_encode($json_data);

// إنهاء المخزن المؤقت
ob_end_flush();
?>