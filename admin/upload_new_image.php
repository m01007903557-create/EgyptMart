<?php
/**
 * File: upload_new_image.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: رفع الصور من محرر TinyMCE
 * Upload images from TinyMCE editor
 * 
 * Features:
 * - رفع الصور المرفوعة من محرر TinyMCE
 * - التحقق من عدم وجود ملف مكرر
 * - إرجاع مسار الصورة المرفوعة
 * - معالجة الأخطاء الأساسية
 */

declare(strict_types=1);

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/tinymce_upload_errors.log');

/**
 * Class TinyMCEImageUploader
 * 
 * Handles image uploads from TinyMCE editor
 */
class TinyMCEImageUploader {
    
    /** @var string Target upload directory */
    private string $uploadDir = 'images/';
    
    /** @var array Allowed file extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png', 'webp', 'svg'];
    
    /** @var int Maximum file size (5MB) */
    private int $maxFileSize = 5242880; // 5MB
    
    /** @var string Log file path */
    private string $logFile;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->logFile = __DIR__ . '/../../logs/tinymce_uploads.log';
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
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
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
        
        // Verify image (skip for SVG)
        if ($extension !== 'svg') {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                throw new InvalidArgumentException('File is not a valid image');
            }
        }
        
        return [
            'name' => $file['name'],
            'extension' => $extension,
            'temp' => $file['tmp_name'],
            'original_name' => $file['name']
        ];
    }
    
    /**
     * Generate unique filename if file exists
     * 
     * @param string $filename Original filename
     * @return string Unique filename
     */
    public function getUniqueFilename(string $filename): string {
        $targetPath = $this->uploadDir . $filename;
        
        if (!file_exists($targetPath)) {
            return $filename;
        }
        
        $fileInfo = pathinfo($filename);
        $name = $fileInfo['filename'];
        $extension = $fileInfo['extension'] ?? '';
        
        $counter = 1;
        do {
            $newFilename = $name . '_' . $counter . '.' . $extension;
            $targetPath = $this->uploadDir . $newFilename;
            $counter++;
        } while (file_exists($targetPath));
        
        return $newFilename;
    }
    
    /**
     * Log the upload
     * 
     * @param string $filename Uploaded filename
     * @param bool $isDuplicate Whether file was duplicate
     */
    public function logUpload(string $filename, bool $isDuplicate = false): void {
        $userId = (int)($_SESSION['uid_indm'] ?? 0);
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $status = $isDuplicate ? 'DUPLICATE_SKIPPED' : 'UPLOADED';
        
        $logEntry = sprintf(
            "[%s] TinyMCE Image Upload | Status: %s | Filename: %s | User: %d | IP: %s\n",
            date('Y-m-d H:i:s'),
            $status,
            $filename,
            $userId,
            $userIp
        );
        
        error_log($logEntry, 3, $this->logFile);
    }
    
    /**
     * Handle the upload
     * 
     * @return void
     */
    public function handleUpload(): void {
        
        // Check if file was uploaded
        if (!isset($_FILES['upload'])) {
            echo "No file uploaded.";
            return;
        }
        
        try {
            // Validate file
            $fileInfo = $this->validateFile($_FILES['upload']);
            
            // Check if file already exists
            $targetPath = $this->uploadDir . $fileInfo['name'];
            
            if (file_exists($targetPath)) {
                echo $fileInfo['name'] . " already exists.";
                $this->logUpload($fileInfo['name'], true);
                return;
            }
            
            // Move uploaded file
            if (!move_uploaded_file($fileInfo['temp'], $targetPath)) {
                throw new RuntimeException('Failed to move uploaded file');
            }
            
            // Log the upload
            $this->logUpload($fileInfo['name']);
            
            // Return success message with file path
            echo "Stored in: " . $this->uploadDir . $fileInfo['name'];
            
        } catch (InvalidArgumentException $e) {
            // Handle validation errors
            error_log("TinyMCE upload validation error: " . $e->getMessage());
            echo "Error: " . $e->getMessage();
            
        } catch (RuntimeException $e) {
            // Handle runtime errors
            error_log("TinyMCE upload runtime error: " . $e->getMessage());
            echo "Error: " . $e->getMessage();
            
        } catch (Exception $e) {
            // Handle any other errors
            error_log("TinyMCE upload unexpected error: " . $e->getMessage());
            echo "An unexpected error occurred.";
        }
    }
}

// Main execution
try {
    $uploader = new TinyMCEImageUploader();
    $uploader->handleUpload();
} catch (Exception $e) {
    error_log("TinyMCE upload critical error: " . $e->getMessage());
    echo "System error occurred.";
}
?>