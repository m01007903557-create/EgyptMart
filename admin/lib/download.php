<?php
/**
 * File: download.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: ملف تنزيل المرفقات والملفات مع دعم الاستئناف وتعدد المهام
 * File download script with resume support and multipart download
 * 
 * Original Developer: www.webinfopedia.com
 * Last Updated: 2025-03-15
 * 
 * Features:
 * - دownload resuming (HTTP Range)
 * - MIME type detection
 * - Memory efficient chunked reading
 * - Security validations
 * 
 * @package FileManagement
 * @subpackage Download
 * @license GPL
 * @link http://www.webinfopedia.com
 */

declare(strict_types=1);

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../logs/download_errors.log');

// Set timezone
date_default_timezone_set('Africa/Cairo');

// Prevent direct access
//if (!defined('IN_EGYPTMART') && !isset($_REQUEST['filename'])) {
    //exit('Direct access not allowed');
//}

/**
 * Output file for download with resume support
 * 
 * @param string $file Path to file
 * @param string $name Download filename
 * @param string $mime_type MIME type (optional)
 * @throws RuntimeException If file cannot be accessed
 */
function outputFile(string $file, string $name, string $mime_type = ''): void {
    
    // Security: Prevent directory traversal
    $file = realpath($file);
    $name = basename($name); // Remove any path information
    
    if ($file === false) {
        throw new RuntimeException('File not found or inaccessible!');
    }
    
    // Check file permissions
    if (!is_readable($file)) {
        throw new RuntimeException('File not found or inaccessible!');
    }
    
    $size = filesize($file);
    $name = rawurldecode($name);
    
    // Known MIME types
    $known_mime_types = [
        "pdf" => "application/pdf",
        "txt" => "text/plain",
        "html" => "text/html",
        "htm" => "text/html",
        "exe" => "application/octet-stream",
        "zip" => "application/zip",
        "doc" => "application/msword",
        "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "xls" => "application/vnd.ms-excel",
        "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "ppt" => "application/vnd.ms-powerpoint",
        "pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "gif" => "image/gif",
        "png" => "image/png",
        "jpeg" => "image/jpeg",
        "jpg" => "image/jpeg",
        "php" => "text/plain",
        "css" => "text/css",
        "js" => "application/javascript",
        "json" => "application/json",
        "xml" => "application/xml",
        "csv" => "text/csv",
        "mp3" => "audio/mpeg",
        "mp4" => "video/mp4",
        "avi" => "video/x-msvideo",
        "mov" => "video/quicktime"
    ];
    
    // Determine MIME type if not provided
    if ($mime_type === '') {
        $file_extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime_type = $known_mime_types[$file_extension] ?? "application/octet-stream";
    }
    
    // Turn off output buffering to decrease CPU usage
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Required for IE, otherwise Content-Disposition may be ignored
    if (ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }
    
    // Set download headers
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    
    // Non-cacheable headers
    header('Cache-Control: private, no-cache, must-revalidate');
    header('Pragma: private');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    // Handle range requests (download resuming)
    $isPartial = false;
    $rangeStart = 0;
    $rangeEnd = $size - 1;
    $contentLength = $size;
    
    if (isset($_SERVER['HTTP_RANGE'])) {
        // Parse range header
        if (preg_match('/bytes=\h*(\d+)-(\d*)/i', $_SERVER['HTTP_RANGE'], $matches)) {
            $rangeStart = intval($matches[1]);
            if (!empty($matches[2])) {
                $rangeEnd = intval($matches[2]);
                if ($rangeEnd > $size - 1) {
                    $rangeEnd = $size - 1;
                }
            }
            
            $contentLength = $rangeEnd - $rangeStart + 1;
            
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes {$rangeStart}-{$rangeEnd}/{$size}");
            $isPartial = true;
        }
    }
    
    header("Content-Length: " . $contentLength);
    
    // Output file in chunks
    $chunkSize = 1024 * 1024; // 1MB chunks
    $bytesSent = 0;
    
    $handle = fopen($file, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Cannot open file for reading');
    }
    
    try {
        // Seek to position if partial download
        if ($isPartial && $rangeStart > 0) {
            fseek($handle, $rangeStart);
        }
        
        // Read and output file in chunks
        while (!feof($handle) && !connection_aborted() && $bytesSent < $contentLength) {
            $remainingBytes = $contentLength - $bytesSent;
            $readSize = min($chunkSize, $remainingBytes);
            
            $buffer = fread($handle, $readSize);
            if ($buffer === false) {
                throw new RuntimeException('Error reading file');
            }
            
            echo $buffer;
            flush();
            
            $bytesSent += strlen($buffer);
        }
        
    } finally {
        fclose($handle);
    }
}

// Main execution
try {
    // Validate input
    if (!isset($_REQUEST['filename']) || empty($_REQUEST['filename'])) {
        throw new RuntimeException('Filename not specified');
    }
    
    $filename = $_REQUEST['filename'];
    
    // Security: Prevent directory traversal
    $filename = basename($filename);
    if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        throw new RuntimeException('Invalid filename');
    }
    
    // Build file path
    $file_path = __DIR__ . '/../../upload/productdoc/' . $filename;
    
    // Validate file exists
    if (!file_exists($file_path)) {
        throw new RuntimeException('File not found: ' . $filename);
    }
    
    // Set time limit for large files
    set_time_limit(0);
    
    // Call download function
    outputFile($file_path, $filename, '');
    
} catch (RuntimeException $e) {
    // Log error
    error_log("Download error: " . $e->getMessage());
    
    // Clear output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Show user-friendly error
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: text/plain; charset=utf-8');
    die('File not found or inaccessible. Please contact support.');
    
} catch (Exception $e) {
    error_log("Unexpected download error: " . $e->getMessage());
    
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('HTTP/1.1 500 Internal Server Error');
    die('An error occurred while processing your request.');
}
?>