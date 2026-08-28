<?php
/**
 * File: csv-attendance-summary.php
 * Version: 2.0.0
 * Description: تصدير تقرير ملخص الحضور إلى ملف CSV (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// تضمين الملفات المطلوبة
require_once "../common.php";

/**
 * Class AttendanceExporter - تصدير بيانات الحضور
 */
class AttendanceExporter {
    private mysqli $db;
    private array $filters;
    private array $headers;
    
    /**
     * المُنشئ
     */
    public function __construct(mysqli $database, array $postData) {
        $this->db = $database;
        $this->filters = $this->sanitizeFilters($postData);
        $this->headers = [
            'First Name',
            'Last Name',
            'Designation',
            'Department',
            'Duration (HH:MM:SS)'
        ];
    }
    
    /**
     * تنظيف وتجهيز الفلاتر
     */
    private function sanitizeFilters(array $postData): array {
        return [
            'emp_name' => $this->sanitizeString($postData['emp_name'] ?? ''),
            'jt_id' => $this->sanitizeInt($postData['jt_id'] ?? null),
            'dept_id' => $this->sanitizeInt($postData['dept_id'] ?? null),
            'es_id' => $this->sanitizeInt($postData['es_id'] ?? null),
            'ea_fromdate' => $this->sanitizeDate($postData['ea_fromdate'] ?? ''),
            'ea_todate' => $this->sanitizeDate($postData['ea_todate'] ?? '')
        ];
    }
    
    /**
     * تنظيف نص
     */
    private function sanitizeString(?string $value): string {
        return $value ? trim($value) : '';
    }
    
    /**
     * تنظيف رقم صحيح
     */
    private function sanitizeInt($value): ?int {
        if ($value === null || $value === '') {
            return null;
        }
        $int = filter_var($value, FILTER_VALIDATE_INT);
        return $int !== false ? $int : null;
    }
    
    /**
     * تنظيف تاريخ
     */
    private function sanitizeDate(?string $date): string {
        if (!$date) {
            return '';
        }
        // التحقق من صيغة التاريخ YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        return '';
    }
    
    /**
     * بناء شروط الاستعلام
     */
    private function buildWhereClause(): string {
        $conditions = [];
        
        // فلتر اسم الموظف
        if ($this->filters['emp_name'] !== '' && $this->filters['emp_name'] !== 'All') {
            $nameParts = explode(' ', $this->filters['emp_name'], 2);
            $firstName = $this->db->real_escape_string($nameParts[0]);
            $conditions[] = "emp_firstName LIKE '%{$firstName}%'";
        }
        
        // فلتر المسمى الوظيفي
        if ($this->filters['jt_id'] !== null && $this->filters['jt_id'] > 0) {
            $conditions[] = "ej_jt_id = " . (int)$this->filters['jt_id'];
        }
        
        // فلتر القسم
        if ($this->filters['dept_id'] !== null && $this->filters['dept_id'] > 0) {
            $conditions[] = "ej_dept_id = " . (int)$this->filters['dept_id'];
        }
        
        // فلتر حالة الموظف
        if ($this->filters['es_id'] !== null && $this->filters['es_id'] > 0) {
            $conditions[] = "ej_es_id = " . (int)$this->filters['es_id'];
        }
        
        // فلتر التاريخ
        if ($this->filters['ea_fromdate'] !== '' && $this->filters['ea_todate'] !== '') {
            $fromDate = $this->db->real_escape_string($this->filters['ea_fromdate']);
            $toDate = $this->db->real_escape_string($this->filters['ea_todate']);
            $conditions[] = "ea_date BETWEEN '{$fromDate}' AND '{$toDate}'";
        }
        
        return !empty($conditions) ? ' AND ' . implode(' AND ', $conditions) : '';
    }
    
    /**
     * جلب بيانات الحضور
     */
    public function fetchAttendanceData(): array {
        $whereClause = $this->buildWhereClause();
        
        $sql = "SELECT 
                    emp_firstName,
                    emp_lastName,
                    jt_title,
                    dept_name,
                    SEC_TO_TIME(SUM(TIME_TO_SEC(ea_outTime) - TIME_TO_SEC(ea_inTime))) as worked
                FROM employee
                JOIN employee_attendance ON emp_id = ea_emp_id
                JOIN employee_job ON emp_id = ej_emp_id
                JOIN job_title ON ej_jt_id = jt_id
                JOIN department ON ej_dept_id = dept_id
                WHERE 1=1 {$whereClause}
                GROUP BY emp_id
                ORDER BY emp_firstName, emp_lastName";
        
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            error_log('خطأ في استعلام الحضور: ' . mysqli_error($this->db));
            return [];
        }
        
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                'First Name' => $row['emp_firstName'] ?? '',
                'Last Name' => $row['emp_lastName'] ?? '',
                'Designation' => $row['jt_title'] ?? '',
                'Department' => $row['dept_name'] ?? '',
                'Duration' => $this->formatDuration($row['worked'] ?? '00:00:00')
            ];
        }
        
        return $data;
    }
    
    /**
     * تنسيق المدة
     */
    private function formatDuration(string $duration): string {
        if ($duration === '00:00:00' || empty($duration)) {
            return '0:00:00';
        }
        
        $parts = explode(':', $duration);
        if (count($parts) === 3) {
            $hours = (int)$parts[0];
            $minutes = (int)$parts[1];
            $seconds = (int)$parts[2];
            
            // إزالة الأصفار البادئة للساعات
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return $duration;
    }
    
    /**
     * إنشاء ملف CSV وتنزيله
     */
    public function exportToCSV(): void {
        // جلب البيانات
        $data = $this->fetchAttendanceData();
        
        // إنشاء ملف مؤقت
        $filename = tempnam(sys_get_temp_dir(), 'attendance_');
        
        if ($filename === false) {
            die('فشل إنشاء الملف المؤقت');
        }
        
        $file = fopen($filename, 'w');
        
        if ($file === false) {
            die('فشل فتح الملف للكتابة');
        }
        
        // كتابة الترويسة
        fputcsv($file, $this->headers);
        
        // كتابة البيانات
        if (!empty($data)) {
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        } else {
            // كتابة صف فارغ إذا لم توجد بيانات
            fputcsv($file, ['لا توجد بيانات', '', '', '', '']);
        }
        
        fclose($file);
        
        // إعداد headers للتحميل
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="attendance-summary-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // إرسال الملف
        readfile($filename);
        
        // حذف الملف المؤقت
        unlink($filename);
    }
    
    /**
     * الحصول على ملخص الفلاتر المستخدمة
     */
    public function getFilterSummary(): string {
        $summary = [];
        
        if ($this->filters['emp_name'] !== '' && $this->filters['emp_name'] !== 'All') {
            $summary[] = "الموظف: {$this->filters['emp_name']}";
        }
        
        if ($this->filters['ea_fromdate'] !== '' && $this->filters['ea_todate'] !== '') {
            $summary[] = "من: {$this->filters['ea_fromdate']} إلى: {$this->filters['ea_todate']}";
        }
        
        return implode(' | ', $summary);
    }
}

// التحقق من الطلب (يمكن إلغاء التعليق للتحقق)
// if (!isset($_POST['viewcsv']) && !isset($_GET['export'])) {
//     header("Location: attendance-summary.php");
//     exit();
// }

// إنشاء المُصدر وتنفيذ التصدير
try {
    $exporter = new AttendanceExporter($con, $_POST);
    
    // تسجيل عملية التصدير (اختياري)
    error_log('تصدير تقرير الحضور - ' . $exporter->getFilterSummary());
    
    $exporter->exportToCSV();
    
} catch (Exception $e) {
    error_log('خطأ في تصدير الحضور: ' . $e->getMessage());
    
    // عرض رسالة خطأ للمستخدم
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html>
    <head><title>خطأ</title></head>
    <body style="text-align:center; padding:50px; font-family:Arial;">
        <h2 style="color:#d00;">حدث خطأ أثناء تصدير الملف</h2>
        <p>يرجى المحاولة مرة أخرى أو الاتصال بالدعم الفني.</p>
        <p><a href="attendance-summary.php">عودة إلى صفحة التقارير</a></p>
    </body>
    </html>';
}

ob_end_flush();
?>