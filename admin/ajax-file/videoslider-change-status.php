<?php
/**
 * videoslider-change-status.php
 * 
 * This script handles updating the status (active/inactive) of video slider items
 * 
 * PHP Version 8.3
 * 
 * @package VideoSlider
 * @author System Admin
 * @copyright 2025
 */

declare(strict_types=1);

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start session if needed for authentication checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../../common.php';

/**
 * Class VideoSliderStatusUpdater
 * 
 * Handles video slider status update operations
 */
class VideoSliderStatusUpdater {
    private mysqli $db;
    private array $validStatuses = ['0', '1'];
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * Validate and sanitize input parameters
     * 
     * @param string $status Status value
     * @param string $id Item ID
     * @return array{status: string, id: int} Validated data
     * @throws InvalidArgumentException If validation fails
     */
    public function validateInput(string $status, string $id): array {
        // Sanitize status
        $cleanStatus = trim($status);
        if (!in_array($cleanStatus, $this->validStatuses, true)) {
            throw new InvalidArgumentException(
                'Invalid status value. Must be 0 or 1. Received: ' . htmlspecialchars($status)
            );
        }
        
        // Validate and sanitize ID
        $cleanId = filter_var(trim($id), FILTER_VALIDATE_INT);
        if ($cleanId === false || $cleanId <= 0) {
            throw new InvalidArgumentException(
                'Invalid ID. Must be a positive integer. Received: ' . htmlspecialchars($id)
            );
        }
        
        return [
            'status' => $cleanStatus,
            'id' => $cleanId
        ];
    }
    
    /**
     * Update video slider status in database
     * 
     * @param string $status New status (0 or 1)
     * @param int $id Video slider ID
     * @return bool True on success
     * @throws RuntimeException If database operation fails
     */
    public function updateStatus(string $status, int $id): bool {
        // Use prepared statement to prevent SQL injection
        $sql = "UPDATE video_slider SET adv_status = ? WHERE adv_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            throw new RuntimeException(
                'Failed to prepare statement: ' . mysqli_error($this->db)
            );
        }
        
        // Bind parameters (status as string, id as integer)
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        
        // Execute the statement
        $success = mysqli_stmt_execute($stmt);
        
        if (!$success) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new RuntimeException('Failed to execute update: ' . $error);
        }
        
        // Check if any rows were affected
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        
        mysqli_stmt_close($stmt);
        
        // Return true if update was successful (even if no rows changed)
        return $success && $affectedRows >= 0;
    }
    
    /**
     * Get current status before update (for logging)
     * 
     * @param int $id Video slider ID
     * @return string|null Current status or null if not found
     */
    public function getCurrentStatus(int $id): ?string {
        $stmt = mysqli_prepare($this->db, "SELECT adv_status FROM video_slider WHERE adv_id = ?");
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return (string)$row['adv_status'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Log the status update for audit purposes
     * 
     * @param int $id Video slider ID
     * @param string $oldStatus Previous status
     * @param string $newStatus New status
     * @return void
     */
    public function logUpdate(int $id, ?string $oldStatus, string $newStatus): void {
        $logEntry = sprintf(
            "[%s] Video Slider ID: %d | Old Status: %s | New Status: %s | User ID: %s | IP: %s\n",
            date('Y-m-d H:i:s'),
            $id,
            $oldStatus ?? 'UNKNOWN',
            $newStatus,
            $_SESSION['uid_indm'] ?? '0',
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
        );
        
        // Log to file
        $logFile = __DIR__ . '/../../logs/video_slider_updates.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        error_log($logEntry, 3, $logFile);
    }
    
    /**
     * Verify user has permission to update video slider
     * 
     * @return bool True if user has permission
     */
    public function checkPermission(): bool {
        // Implement your authentication logic here
        // Customize based on your needs
        
        // Check if user is logged in
        if (!isset($_SESSION['uid_indm'])) {
            return false;
        }
        
        // Optional: Add role-based checks
        // For example, check if user is admin or has specific permission
        
        return true;
    }
}

/**
 * Response handler for AJAX requests
 */
class AjaxResponse {
    /**
     * Send success response
     * 
     * @param array $data Additional data to include
     * @return void
     */
    public static function success(array $data = []): void {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => true,
            'message' => 'Video slider status updated successfully'
        ], $data));
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @return void
     */
    public static function error(string $message, int $statusCode = 400): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
    }
}

// Main execution
try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        AjaxResponse::error('Method not allowed. Only POST requests are accepted.', 405);
        exit;
    }
    
    // Initialize updater
    $updater = new VideoSliderStatusUpdater($con);
    
    // Check permissions (optional - uncomment if needed)
    // if (!$updater->checkPermission()) {
    //     AjaxResponse::error('Unauthorized access', 403);
    //     exit;
    // }
    
    // Validate required parameters
    if (!isset($_POST['stat']) || !isset($_POST['id'])) {
        AjaxResponse::error('Missing required parameters: stat and id are required');
        exit;
    }
    
    // Validate and sanitize inputs
    $validated = $updater->validateInput($_POST['stat'], $_POST['id']);
    
    // Get current status for logging (optional)
    $oldStatus = $updater->getCurrentStatus($validated['id']);
    
    // Perform the update
    $success = $updater->updateStatus($validated['status'], $validated['id']);
    
    if (!$success) {
        throw new RuntimeException('Failed to update video slider status');
    }
    
    // Log the update
    $updater->logUpdate($validated['id'], $oldStatus, $validated['status']);
    
    // Return success response
    AjaxResponse::success([
        'data' => [
            'id' => $validated['id'],
            'old_status' => $oldStatus,
            'new_status' => $validated['status']
        ]
    ]);
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    error_log("Video slider validation error: " . $e->getMessage());
    AjaxResponse::error($e->getMessage(), 400);
    
} catch (RuntimeException $e) {
    // Handle runtime errors
    error_log("Video slider runtime error: " . $e->getMessage());
    AjaxResponse::error('An error occurred while updating the video slider', 500);
    
} catch (Exception $e) {
    // Handle any other errors
    error_log("Video slider unexpected error: " . $e->getMessage());
    AjaxResponse::error('An unexpected error occurred', 500);
    
} finally {
    // Close database connection if it exists
    if (isset($con) && $con instanceof mysqli) {
        mysqli_close($con);
    }
}
?>