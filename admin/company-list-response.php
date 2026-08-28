<?php
/**
 * File: admin/company-list-response.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: معالجة طلبات AJAX (DataTables) لعرض قائمة الشركات.
 *              يدعم البحث والترتيب والترقيم والتحقق من البريد الإلكتروني.
 */

// تمكين عرض الأخطاء (يُفضل تعطيله في الإنتاج)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء المخزن المؤقت والجلسة
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية (باستخدام مسار مطلق)
require_once dirname(__DIR__) . '/common.php';
require_once dirname(__DIR__) . '/lib/pagination.php';

// التحقق من اتصال قاعدة البيانات
global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    // تسجيل الخطأ في سجل الأخطاء
    error_log("company-list-response.php: فشل الاتصال بقاعدة البيانات.");
    // إرجاع استجابة JSON مع رسالة خطأ
    $json_data = [
        "draw"            => isset($_REQUEST['draw']) ? (int)$_REQUEST['draw'] : 0,
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => [],
        "error"           => "فشل الاتصال بقاعدة البيانات. الرجاء التحقق من السجل."
    ];
    echo json_encode($json_data);
    exit;
}

// ----- معالجة طلب التحقق من البريد الإلكتروني (AJAX منفصل) -----
if (isset($_POST['verifyId'])) {
    // ... (الكود الخاص بإرسال البريد الإلكتروني للتحقق - يبقى كما هو) ...
    // تأكد من أن الدوال المساعدة مثل get_page_settings و sendSMTPMail تعمل بشكل صحيح
    $userId = (int)$_POST['verifyId'];
    if ($userId > 0) {
        $_SESSION['uid_indm'] = $userId;
        $token = rand(1000, 9999) . md5((string)$userId);
        $email_link = "http://" . $_SERVER['SERVER_NAME'] . "/verifyUser.php?token=" . $token;
        $to = stripslashes(user_info($userId, 'email') ?? '');

        $subject = "تحقق من حسابك على إيجبت مارت";
        $from_name = get_page_settings(4);
        $from_email = get_adminemail();

        $message1 = '';
        $email_template = dirname(__DIR__) . "/email/emailVerification.php";
        if (file_exists($email_template)) {
            ob_start();
            include $email_template;
            $message1 = ob_get_clean();
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $from_name <$from_email>\r\n";

        if (!empty($to) && sendSMTPMail($to, $subject, $message1, $headers)) {
            echo 'Sent!';
            exit;
        }
    }
    echo 'Error!';
    exit;
}

// ----- معالجة طلب DataTable الرئيسي -----
try {
    // الحصول على معاملات DataTable
    $draw = isset($_REQUEST['draw']) ? (int)$_REQUEST['draw'] : 0;
    $start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
    $length = isset($_REQUEST['length']) ? (int)$_REQUEST['length'] : 10;
    $searchValue = isset($_REQUEST['search']['value']) ? $_REQUEST['search']['value'] : '';

    // تحديد عمود الترتيب واتجاهه
    $orderColumnIndex = isset($_REQUEST['order'][0]['column']) ? (int)$_REQUEST['order'][0]['column'] : 1;
    $orderDir = isset($_REQUEST['order'][0]['dir']) && $_REQUEST['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';

    // خريطة الأعمدة (تأكد من تطابقها مع أسماء الحقول في قاعدة البيانات)
    $columnsMap = [
        1 => 'usr_id',
        2 => 'bnsprof_creation_date',
        3 => 'bnsprof_compname',
        4 => 'website',
        5 => 'lname', // سيتم التعامل معه لاحقًا
        6 => 'mobile1',
        7 => 'email',
        8 => 'cn_name',
        9 => 'state_name',
        10 => 'ct_name',
        11 => 'mst_name',
        12 => 'expiry_date',
        13 => 'usr_emailVerify',
    ];
    $orderColumn = $columnsMap[$orderColumnIndex] ?? 'usr_id';

    // بناء جملة WHERE للبحث
    $searchWhere = '';
    if (!empty($searchValue)) {
        $escapedSearch = mysqli_real_escape_string($con, $searchValue);
        $searchWhere = " AND (";
        $searchWhere .= " u.fname LIKE '%$escapedSearch%'";
        $searchWhere .= " OR u.lname LIKE '%$escapedSearch%'";
        $searchWhere .= " OR u.email LIKE '%$escapedSearch%'";
        $searchWhere .= " OR bf.bnsprof_compname LIKE '%$escapedSearch%'";
        $searchWhere .= " OR c.cn_name LIKE '%$escapedSearch%'";
        $searchWhere .= " OR s.state_name LIKE '%$escapedSearch%'";
        $searchWhere .= " OR ct.ct_name LIKE '%$escapedSearch%'";
        $searchWhere .= " OR mp.mst_name LIKE '%$escapedSearch%'";
        $searchWhere .= " )";

        // البحث بالكلمات المفتاحية الخاصة (Active, Inactive, etc.)
        $searchLower = strtolower($searchValue);
        $currentTimestamp = time();
        if ($searchLower === 'active') {
            $searchWhere = " AND pm.expiry_date > $currentTimestamp";
        } elseif ($searchLower === 'inactive') {
            $searchWhere = " AND (pm.expiry_date IS NULL OR pm.expiry_date <= $currentTimestamp)";
        } elseif ($searchLower === 'verified') {
            $searchWhere = " AND u.usr_emailVerify = '1'";
        } elseif ($searchLower === 'not verified') {
            $searchWhere = " AND u.usr_emailVerify = '0'";
        }
    }

    // الاستعلام الأساسي (للبحث والترتيب)
    $baseQuery = "FROM business_profile bf
                  JOIN user u ON u.usr_id = bf.bnsprof_uid
                  LEFT JOIN country c ON c.cn_id = u.country
                  LEFT JOIN states s ON s.state_id = bf.bnsprof_state
                  LEFT JOIN city ct ON ct.ct_id = bf.bnsprof_city
                  LEFT JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id
                  LEFT JOIN smembership_plan mp ON mp.mp_id = u.usr_mp_id
                  WHERE u.status = '1'";

    // استعلام لحساب العدد الإجمالي للسجلات (بدون فلتر)
    $countTotalSql = "SELECT COUNT(*) as total " . $baseQuery;
    $resultTotal = mysqli_query($con, $countTotalSql);
    if (!$resultTotal) {
        throw new Exception("خطأ في حساب العدد الإجمالي: " . mysqli_error($con));
    }
    $rowTotal = mysqli_fetch_assoc($resultTotal);
    $recordsTotal = (int)$rowTotal['total'];

    // استعلام لحساب السجلات بعد تطبيق فلتر البحث
    $countFilteredSql = "SELECT COUNT(*) as total " . $baseQuery . $searchWhere;
    $resultFiltered = mysqli_query($con, $countFilteredSql);
    if (!$resultFiltered) {
        throw new Exception("خطأ في حساب السجلات المفلترة: " . mysqli_error($con));
    }
    $rowFiltered = mysqli_fetch_assoc($resultFiltered);
    $recordsFiltered = (int)$rowFiltered['total'];

    // استعلام لجلب البيانات مع الترتيب والحد
    // نختار جميع الأعمدة المطلوبة لعرضها في الجدول
    $selectColumns = "
        u.*, bf.*, c.cn_name, s.state_name, ct.ct_name, mp.mst_name, pm.expiry_date
    ";
    $dataSql = "SELECT " . $selectColumns . " " . $baseQuery . $searchWhere;
    // تطبيق الترتيب
    $dataSql .= " ORDER BY " . $orderColumn . " " . $orderDir;
    // تطبيق الحد (Pagination)
    $dataSql .= " LIMIT " . $start . ", " . $length;

    $resultData = mysqli_query($con, $dataSql);
    if (!$resultData) {
        throw new Exception("خطأ في جلب البيانات: " . mysqli_error($con));
    }

    // تجهيز مصفوفة البيانات بالصيغة المطلوبة لعرضها في الجدول
    $data = [];
    $currentTimestamp = time();
    while ($row = mysqli_fetch_assoc($resultData)) {
        // حساب حالة العضوية
        $membershipStatus = '';
        if (!empty($row['expiry_date'])) {
            if ($row['expiry_date'] == '9999-09-09') {
                $membershipStatus = 'Permanent';
            } else {
                $expiryTimestamp = strtotime($row['expiry_date']);
                $expiryFormatted = date("d F Y", $expiryTimestamp);
                $isActive = ($expiryTimestamp > $currentTimestamp) ? 'Active' : 'Inactive';
                $membershipStatus = $expiryFormatted . ' ' . $isActive;
            }
        }

        // حساب حالة التحقق من البريد الإلكتروني
        $emailStatus = '';
        if ($row['usr_emailVerify'] == '0') {
            $emailStatus = '<font color=red>Email not verified</font>' .
                           '<div id="verify-link-' . (int)$row['usr_id'] . '">' .
                           '<a href="javascript:void(0);" onclick="verifyNow(' . (int)$row['usr_id'] . ', this)" style="color: #F00">Verify Now</a>' .
                           '</div>';
        } elseif ($row['usr_emailVerify'] == '1') {
            $emailStatus = '<font color=green>Email verified</font>';
        }

        // إنشاء صف البيانات بترتيب الأعمدة
        $res = [];
        $res[0] = '<input name="cb[]" class="ace" type="checkbox" value="' . (int)$row['usr_id'] . '" /><span class="lbl"></span>';
        $res[1] = !empty($row['bnsprof_creation_date']) ? date('d/M/Y', strtotime($row['bnsprof_creation_date'])) : '';
        $res[2] = htmlspecialchars(ucwords($row['bnsprof_compname'] ?? ''));
        $website = htmlspecialchars($row['website'] ?? '');
        $res[3] = '<a href="' . $website . '" target="_blank">' . str_replace(".", "<br>.", $website) . '</a>';
        $fullName = htmlspecialchars(ucwords(trim(($row['name_prefix'] ?? '') . ' ' . ($row['lname'] ?? '') . ' ' . ($row['fname'] ?? ''))));
        $res[4] = '<a href="user-details.php?token=' . rand(1000, 9999) . md5((string)$row['usr_id']) . '" target="_blank">' .
                  str_replace(".", '<br>.', $fullName) . '</a>';
        $res[5] = '+' . htmlspecialchars($row['country_ph_code'] ?? '') . ' ' . htmlspecialchars($row['mobile1'] ?? '');
        $res[6] = str_replace("@", "<br>@", htmlspecialchars($row['email'] ?? ''));
        $res[7] = htmlspecialchars($row['cn_name'] ?? '');
        $res[8] = htmlspecialchars($row['state_name'] ?? '');
        $res[9] = htmlspecialchars($row['ct_name'] ?? '');
        $res[10] = htmlspecialchars($row['mst_name'] ?? '');
        $res[11] = $membershipStatus;
        $res[12] = $emailStatus;
        $res[13] = '<a href="company-details.php?token=' . md5((string)$row['bnsprof_id']) . '">' .
                   '<img src="images/details.png" alt="Details" /></a>';
        $res[14] = ''; // عمود فارغ للإجراءات المستقبلية

        $data[] = $res;
    }

    // إعداد الاستجابة النهائية
    $response = [
        "draw"            => $draw,
        "recordsTotal"    => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "data"            => $data
    ];

    echo json_encode($response);

} catch (Exception $e) {
    // تسجيل الخطأ وإرجاعه كاستجابة JSON
    error_log("company-list-response.php: " . $e->getMessage());
    $response = [
        "draw"            => $draw ?? 0,
        "recordsTotal"    => 0,
        "recordsFiltered" => 0,
        "data"            => [],
        "error"           => "حدث خطأ في معالجة الطلب: " . $e->getMessage()
    ];
    echo json_encode($response);
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>