<?php
/**
 * File: upload-image.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: رفع صور الميزات البرمجية (للاستخدام مع Uploadify)
 * Upload feature images (for use with Uploadify)
 * 
 * Features:
 * - رفع الصور للميزات البرمجية
 * - التحقق من صحة نوع الملف
 * - إعادة تسمية الصورة باستخدام التاريخ
 * - حفظ مسار الصورة في قاعدة البيانات
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/upload_errors.log');

// Include database connection
require_once "../common.php";

/**
 * Class FeatureImageUploader
 * 
 * Handles feature image upload operations
 */
class FeatureImageUploader {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Target upload folder */
    private string $targetFolder = '../upload/feature';
    
    /** @var array Allowed file extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];
    
    /** @var int Maximum file size (5MB) */
    private int $maxFileSize = 5242880;
    
    /** @var string Log file path */
    private string $logFile;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->logFile = __DIR__ . '/../../logs/feature_uploads.log';
        $this->ensureLogDirectory();
        $this->ensureUploadDirectory();
    }
    
    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Ensure upload directory exists
     */
    private function ensureUploadDirectory(): void {
        if (!is_dir($this->targetFolder)) {
            mkdir($this->targetFolder, 0755, true);
        }
    }
    
    /**
     * Validate feature ID
     * 
     * @param mixed $pid Raw feature ID from POST
     * @return int Validated feature ID
     * @throws InvalidArgumentException If validation fails
     */
    public function validateFeatureId($pid): int {
        if (!isset($pid)) {
            throw new InvalidArgumentException('Feature ID is required');
        }
        
        $cleanId = filter_var(trim((string)$pid), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId < 0) {
            throw new InvalidArgumentException('Invalid feature ID');
        }
        
        return $cleanId;
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file Uploaded file data
     * @return array{name: string, extension: string, temp: string} Validated file info
     * @throws InvalidArgumentException If validation fails
     */
    public function validateFile(array $file): array {
        
        // Check if file was uploaded
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException('No file uploaded');
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
            throw new InvalidArgumentException($errorMsg);
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            throw new InvalidArgumentException('File size must be less than 5MB');
        }
        
        // Get file info
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');
        
        // Check file extension
        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new InvalidArgumentException('Invalid file type. Allowed: ' . implode(', ', $this->allowedExtensions));
        }
        
        // Verify image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new InvalidArgumentException('File is not a valid image');
        }
        
        return [
            'name' => $file['name'],
            'extension' => $extension,
            'temp' => $file['tmp_name']
        ];
    }
    
    /**
     * Generate unique filename
     * 
     * @param int $featureId Feature ID
     * @param string $extension File extension
     * @return string Unique filename
     */
    public function generateFilename(int $featureId, string $extension): string {
        $timestamp = date('YmdHis');
        return $featureId . $timestamp . '.' . $extension;
    }
    
    /**
     * Check if feature exists
     * 
     * @param int $featureId Feature ID
     * @return bool True if exists
     */
    public function featureExists(int $featureId): bool {
        $sql = "SELECT COUNT(*) as count FROM features WHERE f_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $featureId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Save image record to database
     * 
     * @param int $featureId Feature ID
     * @param string $filename Image filename
     * @return bool Success status
     * @throws RuntimeException If database operation fails
     */
    public function saveImageRecord(int $featureId, string $filename): bool {
        
        $sql = "INSERT INTO feature_images SET
                fi_f_id = ?,
                fi_image = ?,
                fi_updated_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "is", $featureId, $filename);
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to insert image record: ' . $error);
        }
        
        $insertId = mysqli_stmt_insert_id($stmt);
        mysqli_stmt_close($stmt);
        
        return $insertId > 0;
    }
    
    /**
     * Log the upload
     * 
     * @param int $featureId Feature ID
     * @param string $filename Image filename
     */
    public function logUpload(int $featureId, string $filename): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $logEntry = sprintf(
            "[%s] Feature Image Upload | Feature ID: %d | Filename: %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $featureId,
            $filename,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Send success response
     * 
     * @param string $filename Uploaded filename
     */
    public function sendSuccess(string $filename): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo '1'; // Uploadify expects '1' for success
        error_log("Feature image uploaded successfully: " . $filename);
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     */
    public function sendError(string $message): void {
        header('Content-Type: text/plain; charset=utf-8');
        echo '0'; // Uploadify expects '0' for error
        error_log("Feature image upload error: " . $message);
    }
}

// Main execution
try {
    // Check if file was uploaded
    if (empty($_FILES)) {
        throw new InvalidArgumentException('No file uploaded');
    }
    
    // Initialize uploader
    $uploader = new FeatureImageUploader($con);
    
    // Validate feature ID
    $featureId = $uploader->validateFeatureId($_POST['pid'] ?? null);
    
    // Check if feature exists (optional - uncomment if needed)
    // if (!$uploader->featureExists($featureId)) {
    //     throw new InvalidArgumentException('Feature not found');
    // }
    
    // Validate uploaded file
    $fileInfo = $uploader->validateFile($_FILES['Filedata']);
    
    // Generate unique filename
    $filename = $uploader->generateFilename($featureId, $fileInfo['extension']);
    
    // Set target file path
    $targetFile = rtrim($uploader->targetFolder, '/') . '/' . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($fileInfo['temp'], $targetFile)) {
        throw new RuntimeException('Failed to move uploaded file');
    }
    
    // Save image record to database
    $saved = $uploader->saveImageRecord($featureId, $filename);
    
    if (!$saved) {
        // Delete the file if database insert failed
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
        throw new RuntimeException('Failed to save image record to database');
    }
    
    // Log the upload
    $uploader->logUpload($featureId, $filename);
    
    // Send success response
    $uploader->sendSuccess($filename);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Feature upload validation error: " . $e->getMessage());
    $uploader = $uploader ?? new FeatureImageUploader($con);
    $uploader->sendError($e->getMessage());
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Feature upload runtime error: " . $e->getMessage());
    $uploader = $uploader ?? new FeatureImageUploader($con);
    $uploader->sendError($e->getMessage());
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Feature upload unexpected error: " . $e->getMessage());
    $uploader = $uploader ?? new FeatureImageUploader($con);
    $uploader->sendError('An unexpected error occurred');
    
} finally {
    // Close database connection
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>