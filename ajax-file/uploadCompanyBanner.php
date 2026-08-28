<?php
/**
 * File: public_html/ajax/uploadCompanyBanner.php
 * Description: رفع صور بانر الشركات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 * Last Updated: 2024
 * 
 * @category Ajax
 * @package EgyptMart
 * @author Your Name
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://egyptmart.shop
 */

declare(strict_types=1);

// تضمين ملف common.php (النسخة المحدثة)
require_once __DIR__ . '/../common.php';

// تضمين مكتبة SimpleImage (تأكد من وجودها وتحديثها)
require_once __DIR__ . '/../lib/SimpleImage.php';

// التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    die(json_encode(['error' => 'Method not allowed']));
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400); // Bad Request
    die(json_encode(['error' => 'Invalid or missing business profile ID']));
}

$cb_bnsprof_id = (int)$_POST['id']; // تحويل إلى integer

// التحقق من وجود الملف
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die(json_encode(['error' => 'No file uploaded']));
}

// إعدادات رفع الملفات
$targetFolder = __DIR__ . '/../upload/company_banner/';
$maxFileSize = 10 * 1024 * 1024; // 10 ميجابايت كحد أقصى (مناسب للبانر)
$allowedFileTypes = ['jpg', 'jpeg', 'gif', 'png'];
$allowedMimeTypes = [
    'image/jpeg',
    'image/pjpeg',
    'image/gif',
    'image/png',
    'image/x-png'
];

try {
    // التأكد من وجود المجلد الهدف
    if (!is_dir($targetFolder)) {
        if (!mkdir($targetFolder, 0755, true)) {
            throw new RuntimeException('Failed to create upload directory');
        }
    }

    // التحقق من صلاحيات الكتابة
    if (!is_writable($targetFolder)) {
        throw new RuntimeException('Upload directory is not writable');
    }

    $file = $_FILES['Filedata'];
    
    // التحقق من أخطاء الرفع
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $errorMessage = $uploadErrors[$file['error']] ?? 'Unknown upload error';
        throw new RuntimeException($errorMessage);
    }

    // التحقق من حجم الملف
    if ($file['size'] > $maxFileSize) {
        throw new RuntimeException(sprintf(
            'File size exceeds limit of %d MB',
            $maxFileSize / 1024 / 1024
        ));
    }

    // التحقق من نوع الملف باستخدام MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        throw new RuntimeException('Invalid file type based on MIME');
    }

    // الحصول على امتداد الملف والتحقق منه
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');
    
    if (!in_array($extension, $allowedFileTypes, true)) {
        throw new RuntimeException('Invalid file extension');
    }

    // إنشاء اسم فريد وآمن للملف
    $timestamp = time();
    $randomString = bin2hex(random_bytes(6)); // 12 حرف عشوائي آمن
    $newFileName = sprintf(
        'cb-%d-%d-%s.%s',
        $cb_bnsprof_id,
        $timestamp,
        $randomString,
        $extension
    );

    // تنظيف اسم الملف من أي أحرف غير آمنة (احتياطي)
    $newFileName = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $newFileName);
    
    $targetPath = $targetFolder . $newFileName;

    // معالجة الصورة باستخدام SimpleImage
    try {
        $imgSImage = new SimpleImage();
        
        // تحميل الصورة
        if (!$imgSImage->load($file['tmp_name'])) {
            throw new RuntimeException('Failed to load image');
        }
        
        // يمكنك إضافة تعديلات على الصورة هنا إذا أردت
        // مثال: تغيير الحجم
        // $imgSImage->resize(1200, 300); // عرض 1200px, ارتفاع 300px
        
        // حفظ الصورة
        if (!$imgSImage->save($targetPath)) {
            throw new RuntimeException('Failed to save image');
        }
        
    } catch (Exception $e) {
        throw new RuntimeException('Image processing error: ' . $e->getMessage());
    }

    // تغيير صلاحيات الملف
    chmod($targetPath, 0644);

    // حفظ المعلومات في قاعدة البيانات
    $sql = "INSERT INTO company_banner 
            (cb_bnsprof_id, cb_image, cb_updated_date) 
            VALUES (?, ?, NOW())";
    
    global $con;
    if (!$con || !($con instanceof mysqli)) {
        throw new RuntimeException('Database connection not available');
    }

    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        // إذا فشل الإدراج في قاعدة البيانات، نحذف الملف المرفوع
        unlink($targetPath);
        throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, 'is', $cb_bnsprof_id, $newFileName);
    
    if (!mysqli_stmt_execute($stmt)) {
        // إذا فشل الإدراج في قاعدة البيانات، نحذف الملف المرفوع
        unlink($targetPath);
        throw new RuntimeException('Failed to save to database: ' . mysqli_stmt_error($stmt));
    }

    $insertId = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    // إرجاع استجابة نجاح
    $response = [
        'success' => true,
        'message' => 'Company banner uploaded successfully',
        'data' => [
            'id' => $insertId,
            'filename' => $newFileName,
            'url' => BASE_URL . '/upload/company_banner/' . $newFileName,
            'size' => $file['size'],
            'type' => $mimeType,
            'business_id' => $cb_bnsprof_id
        ]
    ];

    // تسجيل النشاط (اختياري)
    if (function_exists('logUserActivity')) {
        logUserActivity('upload_company_banner', 
            "Business profile $cb_bnsprof_id uploaded banner: $newFileName");
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;

} catch (RuntimeException $e) {
    // تسجيل الخطأ
    error_log("Upload Company Banner Error: " . $e->getMessage() . 
              " | Business ID: $cb_bnsprof_id");
    
    // إرجاع استجابة خطأ
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
} catch (Exception $e) {
    // لأي أخطاء غير متوقعة
    error_log("Unexpected Upload Company Banner Error: " . $e->getMessage() . 
              " | Business ID: $cb_bnsprof_id");
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred'
    ]);
    exit;
}
?>