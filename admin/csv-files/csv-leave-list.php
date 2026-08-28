<?php
/**
 * ملف: export-leave-csv.php
 * الإصدار: 2.0.0
 * توافق PHP: 8.3
 * 
 * الوصف: تصدير تقارير الإجازات إلى ملف CSV
 * Export leave reports to CSV file
 * 
 * المميزات:
 * - تصدير بيانات الإجازات بناءً على معايير البحث
 * - دعم تصفية حسب الموظف، القسم، الحالة
 * - نطاق زمني محدد (من تاريخ - إلى تاريخ)
 * - تنسيق CSV متوافق مع Excel
 * - معالجة آمنة للبيانات
 */

declare(strict_types=1);

// بدء تشغيل المخزن المؤقت
ob_start();

// التحقق من الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// التحقق من صلاحيات المستخدم (اختياري - يمكن تفعيله حسب الحاجة)
// check_user_login();

/**
 * دالة تنقية المدخلات النصية
 * 
 * @param string $input النص المدخل
 * @return string النص المنقى
 */
function sanitizeInput(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * دالة التحقق من صحة التاريخ
 * 
 * @param string $date التاريخ المدخل
 * @return bool صحيح إذا كان التاريخ صالحاً
 */
function isValidDate(string $date): bool {
    if (empty($date)) return false;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * دالة تنسيق الحالة للعرض
 * 
 * @param string $status رمز الحالة
 * @return string نص الحالة المنسق
 */
function formatStatus(string $status): string {
    $statusMap = [
        'pending approval' => 'قيد الانتظار',
        'approve' => 'معتمدة',
        'reject' => 'مرفوضة',
        'cancel' => 'ملغاة',
        'scheduled' => 'مجدولة',
        'taken' => 'تم أخذها'
    ];
    
    return $statusMap[$status] ?? $status;
}

// استلام وتنقية المدخلات
$emp_name = sanitizeInput($_POST['emp_name'] ?? 'All');
$fromdate = sanitizeInput($_POST['fromdate'] ?? '');
$todate = sanitizeInput($_POST['todate'] ?? '');
$dept_id = filter_input(INPUT_POST, 'dept_id', FILTER_VALIDATE_INT) ?: '';
$status_all = $_POST['status_all'] ?? '';
$status_rejected = $_POST['status_rejected'] ?? '';
$status_canceled = $_POST['status_canceled'] ?? '';
$status_pending = $_POST['status_pending'] ?? '';
$status_scheduled = $_POST['status_scheduled'] ?? '';
$status_taken = $_POST['status_taken'] ?? '';

// التحقق من صحة التواريخ
if (!isValidDate($fromdate) || !isValidDate($todate)) {
    die("خطأ: تواريخ غير صحيحة");
}

// بناء شروط الاستعلام
$conditions = [];
$params = [];
$types = "";

// شرط اسم الموظف
if ($emp_name !== 'All') {
    $nameParts = explode(' ', $emp_name);
    $firstName = $nameParts[0] ?? '';
    if (!empty($firstName)) {
        $conditions[] = "emp_firstName LIKE ?";
        $params[] = "%$firstName%";
        $types .= "s";
    }
}

// شرط القسم
if (!empty($dept_id)) {
    $conditions[] = "ej_dept_id = ?";
    $params[] = $dept_id;
    $types .= "i";
}

// شروط التاريخ
$conditions[] = "(la_from_date BETWEEN ? AND ? OR la_to_date BETWEEN ? AND ?)";
$params[] = $fromdate;
$params[] = $todate;
$params[] = $fromdate;
$params[] = $todate;
$types .= "ssss";

// بناء شرط الحالة
$statusConditions = [];

if (empty($status_all)) {
    if (!empty($status_rejected)) {
        $statusConditions[] = "la_status = 'reject'";
    }
    if (!empty($status_canceled)) {
        $statusConditions[] = "la_status = 'cancel'";
    }
    if (!empty($status_pending)) {
        $statusConditions[] = "la_status = 'pending approval'";
    }
    if (!empty($status_scheduled)) {
        $statusConditions[] = "(la_status = 'approve' AND NOW() <= la_from_date)";
    }
    if (!empty($status_taken)) {
        $statusConditions[] = "(la_status = 'approve' AND NOW() > la_from_date)";
    }
    
    if (!empty($statusConditions)) {
        $conditions[] = "(" . implode(" OR ", $statusConditions) . ")";
    }
}

// بناء جملة SQL
$sql = "SELECT 
            la_from_date as from_date,
            la_to_date as to_date,
            emp_firstName as first_name,
            emp_lastName as last_name,
            DATEDIFF(la_to_date, la_from_date) + 1 as duration,
            la_status as status
        FROM leave_assign
        JOIN employee ON emp_id = la_emp_id
        JOIN employee_job ON emp_id = ej_emp_id
        JOIN department ON ej_dept_id = dept_id";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY la_from_date DESC";

// تنفيذ الاستعلام
if (!empty($params)) {
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        die("خطأ في قاعدة البيانات: " . mysqli_error($con));
    }
} else {
    $result = mysqli_query($con, $sql);
}

if (!$result) {
    die("خطأ في جلب البيانات: " . mysqli_error($con));
}

// إنشاء ملف مؤقت
$filename = tempnam(sys_get_temp_dir(), "leave_export_");
$file = fopen($filename, "w");

// كتابة رؤوس الأعمدة (باللغتين)
$headers = [
    'من تاريخ',
    'إلى تاريخ',
    'الاسم الأول',
    'الاسم الأخير',
    'المدة (أيام)',
    'الحالة'
];
fputcsv($file, $headers);

// كتابة البيانات
$totalRows = 0;
$totalDays = 0;

while ($row = mysqli_fetch_assoc($result)) {
    // تنسيق الحالة
    $row['status'] = formatStatus($row['status']);
    
    // إعادة ترتيب الحقول
    $csvRow = [
        $row['from_date'],
        $row['to_date'],
        $row['first_name'],
        $row['last_name'],
        $row['duration'],
        $row['status']
    ];
    
    fputcsv($file, $csvRow);
    
    $totalRows++;
    $totalDays += (int)$row['duration'];
}

// إضافة سطر إجمالي
if ($totalRows > 0) {
    fputcsv($file, []); // سطر فارغ
    fputcsv($file, ['الإجمالي', '', '', '', $totalRows . ' إجازة', $totalDays . ' يوم']);
}

fclose($file);

// تسجيل عملية التصدير في سجل النشاطات
if (function_exists('logActivity')) {
    logActivity('export', 'leave_report', [
        'from_date' => $fromdate,
        'to_date' => $todate,
        'total_rows' => $totalRows,
        'total_days' => $totalDays
    ]);
}

// تعيين رؤوس HTTP للتحميل
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=تقرير_الإجازات_" . date('Y-m-d') . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

// إرسال الملف إلى المتصفح
readfile($filename);

// حذف الملف المؤقت
unlink($filename);

// إنهاء المخزن المؤقت
ob_end_flush();

/**
 * دالة تسجيل النشاطات (اختيارية)
 */
function logActivity(string $action, string $type, array $details = []): void {
    global $con;
    
    $userId = $_SESSION['user_id'] ?? 0;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $detailsJson = json_encode($details);
    
    $sql = "INSERT INTO activity_log (user_id, action, item_type, details, ip_address, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issss", $userId, $action, $type, $detailsJson, $ipAddress);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// إنشاء جدول سجل النشاطات إذا لم يكن موجوداً
$checkTable = mysqli_query($con, "SHOW TABLES LIKE 'activity_log'");
if (mysqli_num_rows($checkTable) == 0) {
    $createTable = "CREATE TABLE IF NOT EXISTS `activity_log` (
        `log_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `action` varchar(50) NOT NULL,
        `item_type` varchar(50) NOT NULL,
        `details` text DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`),
        KEY `user_id` (`user_id`),
        KEY `item_type` (`item_type`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $createTable);
}

// إغلاق اتصال قاعدة البيانات
mysqli_close($con);

?>