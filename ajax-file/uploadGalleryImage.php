<?php
/**
 * File: public_html/ajax/uploadGalleryImage.php
 * Description: رفع الصور إلى معرض الصور
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

// التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    die(json_encode(['error' => 'Method not allowed']));
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400); // Bad Request
    die(json_encode(['error' => 'Invalid or missing user ID']));
}

$ph_u_id = (int)$_POST['id']; // تحويل إلى integer

// التحقق من وجود الملف
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die(json_encode(['error' => 'No file uploaded']));
}

// إعدادات رفع الملفات
$targetFolder = __DIR__ . '/../upload/image_gallery/';
$maxFileSize = 5 * 1024 * 1024; // 5 ميجابايت كحد أقصى
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
    $randomString = bin2hex(random_bytes(8)); // 16 حرف عشوائي آمن
    $newFilename = sprintf(
        'ph-%d-%s-%s.%s',
        $ph_u_id,
        $timestamp,
        $randomString,
        $extension
    );

    // تنظيف اسم الملف من أي أحرف غير آمنة (احتياطي)
    $newFilename = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $newFilename);
    
    $targetPath = $targetFolder . $newFilename;

    // نقل الملف إلى المجلد الهدف
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded file');
    }

    // تغيير صلاحيات الملف
    chmod($targetPath, 0644);

    // إنشاء صورة مصغرة (اختياري - يمكنك تفعيله إذا أردت)
    /*
    createThumbnail($targetPath, $targetFolder . 'thumb_' . $newFilename, 200, 200);
    */

    // حفظ المعلومات في قاعدة البيانات
    $sql = "INSERT INTO photo (ph_u_id, ph_fileName, ph_updated_date) VALUES (?, ?, NOW())";
    
    global $con;
    if (!$con || !($con instanceof mysqli)) {
        throw new RuntimeException('Database connection not available');
    }

    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($con));
    }

    mysqli_stmt_bind_param($stmt, 'is', $ph_u_id, $newFilename);
    
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
        'message' => 'File uploaded successfully',
        'data' => [
            'id' => $insertId,
            'filename' => $newFilename,
            'url' => BASE_URL . '/upload/image_gallery/' . $newFilename,
            'size' => $file['size'],
            'type' => $mimeType
        ]
    ];

    // تسجيل النشاط (اختياري)
    if (function_exists('logUserActivity')) {
        logUserActivity('upload_gallery_image', "User $ph_u_id uploaded image: $newFilename");
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;

} catch (RuntimeException $e) {
    // تسجيل الخطأ
    error_log("Upload Gallery Error: " . $e->getMessage() . " | User ID: $ph_u_id");
    
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
    error_log("Unexpected Upload Gallery Error: " . $e->getMessage() . " | User ID: $ph_u_id");
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred'
    ]);
    exit;
}

/**
 * دالة مساعدة لإنشاء صورة مصغرة (اختياري)
 * 
 * @param string $sourcePath المسار الكامل للصورة الأصلية
 * @param string $destPath المسار الكامل للصورة المصغرة
 * @param int $width العرض المطلوب
 * @param int $height الارتفاع المطلوب
 * @return bool نجاح أو فشل العملية
 */
/*
function createThumbnail(string $sourcePath, string $destPath, int $width, int $height): bool {
    try {
        list($srcWidth, $srcHeight, $type) = getimagesize($sourcePath);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $srcImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $srcImage = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        $thumbImage = imagecreatetruecolor($width, $height);
        
        // الحفاظ على الشفافية للصور PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $width, $height, $transparent);
        }
        
        imagecopyresampled(
            $thumbImage, $srcImage,
            0, 0, 0, 0,
            $width, $height,
            $srcWidth, $srcHeight
        );
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbImage, $destPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbImage, $destPath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbImage, $destPath);
                break;
        }
        
        imagedestroy($srcImage);
        imagedestroy($thumbImage);
        
        return true;
    } catch (Exception $e) {
        error_log("Thumbnail creation error: " . $e->getMessage());
        return false;
    }
}
*/
?>